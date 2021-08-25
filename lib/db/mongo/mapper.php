<?php

/*

	Copyright (c) 2009-2019 F3::Factory/Bong Cosca, All rights reserved.

	This file is part of the Fat-Free Framework (http://fatfreeframework.com).

	This is free software: you can redistribute it and/or modify it under the
	terms of the GNU General Public License as published by the Free Software
	Foundation, either version 3 of the License, or later.

	Fat-Free Framework is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
	General Public License for more details.

	You should have received a copy of the GNU General Public License along
	with Fat-Free Framework.  If not, see <http://www.gnu.org/licenses/>.

*/

namespace DB\Mongo;

//! MongoDB mapper
class Mapper extends \DB\Cursor {

	protected
		//! MongoDB wrapper
		$db,
		//! Legacy flag
		$legacy,
		//! Mongo collection
		$collection,
		//! Mongo document
		$document=[],
		//! Mongo cursor
		$cursor,
		//! Defined fields
		$fields;

	/**
	*	Return database type
	*	@return string
	**/
	function dbtype() {
		return 'Mongo';
	}

	/**
	*	Return TRUE if field is defined
	*	@return bool
	*	@param $key string
	**/
	function exists($key) {
		return array_key_exists($key,$this->document);
	}

	/**
	*	Assign value to field
	*	@return scalar|FALSE
	*	@param $key string
	*	@param $val scalar
	**/
	function set($key,$val) {
		return $this->document[$key]=$val;
	}

	/**
	*	Retrieve value of field
	*	@return scalar|FALSE
	*	@param $key string
	**/
	function &get($key) {
		if ($this->exists($key))
			return $this->document[$key];
		user_error(sprintf(self::E_Field,$key),E_USER_ERROR);
	}

	/**
	*	Delete field
	*	@return NULL
	*	@param $key string
	**/
	function clear($key) {
		unset($this->document[$key]);
	}

	/**
	*	Convert array to mapper object
	*	@return static
	*	@param $row array
	**/
	function factory($row) {
		$mapper=clone($this);
		$mapper->reset();
		foreach ($row as $key=>$val)
			$mapper->document[$key]=$val;
		$mapper->query=[clone($mapper)];
		if (isset($mapper->trigger['load']))
			\Base::instance()->call($mapper->trigger['load'],$mapper);
		return $mapper;
	}

	/**
	*	Return fields of mapper object as an associative array
	*	@return array
	*	@param $obj object
	**/
	function cast($obj=NULL) {
		if (!$obj)
			$obj=$this;
		return $obj->document;
	}

	/**
	*	Build query and execute
	*	@return static[]
	*	@param $fields string
	*	@param $filter array
	*	@param $options array
	*	@param $ttl int|array
	**/
	function select($fields=NULL,$filter=NULL,array $options=NULL,$ttl=0) {
		if (!$options)
			$options=[];
		$options+=[
			'group'=>NULL,
			'order'=>NULL,
			'limit'=>0,
			'offset'=>0,
			'collation'=>NULL,
		];
		$tag='';
		if (is_array($ttl))
			list($ttl,$tag)=$ttl;
		$fw=\Base::instance();
		$cache=\Cache::instance();
		if (!($cached=$cache->exists($hash=$fw->hash($this->db->dsn().
			$fw->stringify([$fields,$filter,$options])).($tag?'.'.$tag:'').'.mongo',
			$result)) || !$ttl || $cached[0]+$ttl<microtime(TRUE)) {
			if ($options['group']) {
				$grp=$this->collection->group(
					$options['group']['keys'],
					$options['group']['initial'],
					$options['group']['reduce'],
					[
						'condition'=>$filter,
						'finalize'=>$options['group']['finalize']
					]
				);
				$tmp=$this->db->selectcollection(
					$fw->HOST.'.'.$fw->BASE.'.'.
					uniqid(NULL,TRUE).'.tmp'
				);
				$tmp->batchinsert($grp['retval'],['w'=>1]);
				$filter=[];
				$collection=$tmp;
			}
			else {
				$filter=$filter?:[];
				$collection=$this->collection;
			}
			if ($this->legacy) {
				$this->cursor=$collection->find($filter,$fields?:[]);
				if ($options['order'])
					$this->cursor=$this->cursor->sort($options['order']);
				if ($options['limit'])
					$this->cursor=$this->cursor->limit($options['limit']);
				if ($options['offset'])
					$this->cursor=$this->cursor->skip($options['offset']);
				if ($options['collation'])
					$this->cursor=$this->cursor->collation($options['collation']);
				$result=[];
				while ($this->cursor->hasnext())
					$result[]=$this->cursor->getnext();
			}
			else {
				$this->cursor=$collection->find($filter,[
					'sort'=>$options['order'],
					'limit'=>$options['limit'],
					'skip'=>$options['offset'],
					'collation'=>$options['collation'],
				]);
				$result=$this->cursor->toarray();
			}
			if ($options['group'])
				$tmp->drop();
			if ($fw->CACHE && $ttl)
				// Save to cache backend
				$cache->set($hash,$result,$ttl);
		}
		$out=[];
		foreach ($result as $doc)
			$out[]=$this->factory($doc);
		return $out;
	}

	/**
	*	Return records that match criteria
	*	@return static[]
	*	@param $filter array
	*	@param $options array
	*	@param $ttl int|array
	**/
	function find($filter=NULL,array $options=NULL,$ttl=0) {
		if (!$options)
			$options=[];
		$options+=[
			'group'=>NULL,
			'order'=>NULL,
			'limit'=>0,
			'offset'=>0,
			'collation'=>NULL,
		];
		return $this->select($this->fields,$filter,$options,$ttl);
	}

	/**
	*	Count records that match criteria
	*	@return int
	*	@param $filter array
	*	@param $options array
	*	@param $ttl int|array
	**/
	function count($filter=NULL,array $options=NULL,$ttl=0) {
		$fw=\Base::instance();
		$cache=\Cache::instance();
		$tag='';
		if (is_array($ttl))
			list($ttl,$tag)=$ttl;
		if (!($cached=$cache->exists($hash=$fw->hash($fw->stringify(
			[$filter])).($tag?'.'.$tag:'').'.mongo',$result)) || !$ttl ||
			$cached[0]+$ttl<microtime(TRUE)) {
			$result=$this->collection->count($filter?:[],$options);
			if ($fw->CACHE && $ttl)
				// Save to cache backend
				$cache->set($hash,$result,$ttl);
		}
		return $result;
	}

	/**
	*	Return record at specified offset using criteria of previous
	*	load() call and make it active
	*	@return array
	*	@param $ofs int
	**/
	function skip($ofs=1) {
		$this->document=($out=parent::skip($ofs))?$out->document:[];
		if ($this->document && isset($this->trigger['load']))
			\Base::instance()->call($this->trigger['load'],$this);
		return $out;
	}

	/**
	*	Insert new record
	*	@return array
	**/
	function insert() {
		if (isset($this->document['_id']))
			return $this->update();
		if (isset($this->trigger['beforeinsert']) &&
			\Base::instance()->call($this->trigger['beforeinsert'],
				[$this,['_id'=>$this->document['_id']]])===FALSE)
			return $this->document;
		if ($this->legacy) {
			$this->collection->insert($this->document);
			$pkey=['_id'=>$this->document['_id']];
		}
		else {
			$result=$this->collection->insertone($this->document);
			$pkey=['_id'=>$result->getinsertedid()];
		}
		if (isset($this->trigger['afterinsert']))
			\Base::instance()->call($this->trigger['afterinsert'],
				[$this,$pkey]);
		$this->load($pkey);
		return $this->document;
	}

	/**
	*	Update current record
	*	@return array
	**/
	function update() {
		$pkey=['_id'=>$this->document['_id']];
		if (isset($this->trigger['beforeupdate']) &&
			\Base::instance()->call($this->trigger['beforeupdate'],
				[$this,$pkey])===FALSE)
			return $this->document;
		$upsert=['upsert'=>TRUE];
		if ($this->legacy)
			$this->collection->update($pkey,$this->document,$upsert);
		else
			$this->collection->replaceone($pkey,$this->document,$upsert);
		if (isset($this->trigger['afterupdate']))
			\Base::instance()->call($this->trigger['afterupdate'],
				[$this,$pkey]);
		return $this->document;
	}

	/**
	*	Delete current record
	*	@return bool
	*	@param $filter array
	*	@param $quick bool
	**/
	function erase($filter=NULL,$quick=TRUE) {
		if ($filter) {
			if (!$quick) {
				foreach ($this->find($filter) as $mapper)
					if (!$mapper->erase())
						return FALSE;
				return TRUE;
			}
			return $this->legacy?
				$this->collection->remove($filter):
				$this->collection->deletemany($filter);
		}
		$pkey=['_id'=>$this->document['_id']];
		if (isset($this->trigger['beforeerase']) &&
			\Base::instance()->call($this->trigger['beforeerase'],
				[$this,$pkey])===FALSE)
			return FALSE;
		$result=$this->legacy?
			$this->collection->remove(['_id'=>$this->document['_id']]):
			$this->collection->deleteone(['_id'=>$this->document['_id']]);
		parent::erase();
		if (isset($this->trigger['aftererase']))
			\Base::instance()->call($this->trigger['aftererase'],
				[$this,$pkey]);
		return $result;
	}

	/**
	* Run an aggregation pipeline on the collection
	*
	* @see Aggregate::__construct() for supported options
	* @see https://docs.mongodb.com/php-library/current/reference/method/MongoDBCollection-aggregate/
	*
	* @param array $aggregation The aggregation pipeline
	* @param array $options  Command options
	* @return array
	*/
	public function aggregate(aggregation $aggregation, array $options=[]) {
		$result=$this->collection->aggregate($aggregation->getPipeline(), $options);

		return $result->toarray();
	}

	/**
	* Update multiple documents in one shot
	*
	* @see UpdateMany::__construct() for supported options
	* @see https://docs.mongodb.com/manual/reference/method/db.collection.updateMany/index.html
	* @see https://docs.mongodb.com/php-library/v1.7/reference/write-result-classes/#phpclass.MongoDB\UpdateResult
	*
	* @param array $filter  Query by which to filter documents
	* @param array $update  Update to apply to the matched documents
	* @param array $options Command options
	*
	* @return UpdateResult
	*/
	public function updateMany(array $filter, array $update, array $options=[]) {
		return $this->collection->updateMany($filter, $update, $options);
	}

	/**
	* Inserts multiple documents.
	*
	* @see InsertMany::__construct() for supported options
	* @see https://docs.mongodb.com/php-library/v1.7/reference/method/MongoDBCollection-insertMany/
	* @see https://docs.mongodb.com/php-library/v1.7/reference/write-result-classes/#phpclass.MongoDB\InsertManyResult
	*
	* @param array[]|object[] $documents The documents to insert
	* @param array            $options   Command options
	* @return InsertManyResult
	* @throws InvalidArgumentException for parameter/option parsing errors
	* @throws DriverRuntimeException for other driver errors (e.g. connection errors)
	*/
	public function insertMany(array $documents, array $options=[]) {
		return $this->collection->insertMany($documents, $options);
	}

	/**
	* Finds documents and can modify at the same time
	*
	* @see findOneAndUpdate::__construct() for supported options
	* @see https://docs.mongodb.com/php-library/v1.7/reference/method/MongoDBCollection-findOneAndUpdate/
	*
	* @param array $filter
	* @param array $update
	* @param array $options
	* @return array or null
	*/
	public function findOneAndUpdate(array $filter, array $update=[], array $options=[]) {
		return $this->collection->findOneAndUpdate($filter, $update, $options);
	}

	/**
	*	Reset cursor
	*	@return NULL
	**/
	function reset() {
		$this->document=[];
		parent::reset();
	}

	/**
	*	Hydrate mapper object using hive array variable
	*	@return NULL
	*	@param $var array|string
	*	@param $func callback
	**/
	function copyfrom($var,$func=NULL) {
		if (is_string($var))
			$var=\Base::instance()->$var;
		if ($func)
			$var=call_user_func($func,$var);
		foreach ($var as $key=>$val)
			$this->set($key,$val);
	}

	/**
	*	Populate hive array variable with mapper fields
	*	@return NULL
	*	@param $key string
	**/
	function copyto($key) {
		$var=&\Base::instance()->ref($key);
		foreach ($this->document as $key=>$field)
			$var[$key]=$field;
	}

	/**
	*	Return field names
	*	@return array
	**/
	function fields() {
		return array_keys($this->document);
	}

	/**
	*	Return the cursor from last query
	*	@return object|NULL
	**/
	function cursor() {
		return $this->cursor;
	}

	/**
	*	Retrieve external iterator for fields
	*	@return object
	**/
	function getiterator() {
		return new \ArrayIterator($this->cast());
	}

	/**
	*	Instantiate class
	*	@return void
	*	@param $db object
	*	@param $collection string
	*	@param $fields array
	**/
	function __construct(\DB\Mongo $db,$collection,$fields=NULL) {
		$this->db=$db;
		$this->legacy=$db->legacy();
		$this->collection=$db->selectcollection($collection);
		$this->fields=$fields;
		$this->reset();
	}

}
