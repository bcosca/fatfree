<?php

namespace DB\Mongo;

//! MongoDB mapper
class Mapper extends \DB\Cursor {

	protected
		//! MongoDB wrapper
		$db,
		//! Mongo collection
		$collection,
		//! Mongo document
		$document=array();

	/**
		Return TRUE if field is defined
		@return bool
		@param $key string
	**/
	function exists($key) {
		return array_key_exists($key,$this->document);
	}

	/**
		Assign value to field
		@return scalar|FALSE
		@param $key string
		@param $val scalar
	**/
	function set($key,$val) {
		return $this->document[$key]=$val;
	}

	/**
		Retrieve value of field
		@return scalar|FALSE
		@param $key string
	**/
	function get($key) {
		if ($this->exists($key))
			return $this->document[$key];
		user_error(sprintf(self::E_Field,$key));
		return FALSE;
	}

	/**
		Delete field
		@return NULL
		@param $key string
	**/
	function clear($key) {
		unset($this->document[$key]);
	}

	/**
		Convert array to mapper object
		@return object
		@param $row array
	**/
	protected function factory($row) {
		$mapper=clone($this);
		$mapper->reset();
		foreach ($row as $key=>$val)
			$mapper->document[$key]=$val;
		return $mapper;
	}

	/**
		Return fields of mapper object as an associative array
		@return array
		@param $obj object
	**/
	function cast($obj=NULL) {
		if (!$obj)
			$obj=$this;
		return $obj->document;
	}

	/**
		Build query and execute
		@return array
		@param $fields string
		@param $filter array
		@param $options array
	**/
	function select($fields,$filter=NULL,array $options=NULL) {
		if (!$options)
			$options=array();
		$options+=array(
			'group'=>NULL,
			'order'=>NULL,
			'offset'=>0,
			'limit'=>0
		);
		if ($options['group']) {
			$this->db->selectcollection(
				$temp=$_SERVER['SERVER_NAME'].'.'.
					\Base::instance()->hash(uniqid()).'.tmp');
			$this->db->$temp->batchinsert(
				$this->collection->group(
					$options['group']['keys'],
					$options['group']['initial'],
					$options['group']['reduce'],
					array(
						'condition'=>array(
							$filter,
							$options['group']['finalize']
						)
					)
				),
				array('safe'=>TRUE)
			);
			$filter=array();
			$collection=$this->db->$temp;
		}
		else {
			$filter=$filter?:array();
			$collection=$this->collection;
		}
		$cursor=$collection->find($filter,$fields?:array());
		if ($options['order'])
			$cursor=$cursor->sort($options['order']);
		if ($options['offset'])
			$cursor=$cursor->skip($options['offset']);
		if ($options['limit'])
			$cursor=$cursor->limit($options['limit']);
		if ($options['group'])
			$this->db->$temp->drop();
		$result=iterator_to_array($cursor,FALSE);
		$out=array();
		foreach ($result as &$doc) {
			foreach ($doc as &$val)
				if (is_array($val))
					$val=json_decode(json_encode($val));
			$out[]=$this->factory($doc);
			unset($doc);
		}
		return $out;
	}

	/**
		Return records that match criteria
		@return array
		@param $filter array
		@param $options array
	**/
	function find($filter=NULL,array $options=NULL) {
		if (!$options)
			$options=array();
		$options+=array(
			'group'=>NULL,
			'order'=>NULL,
			'offset'=>0,
			'limit'=>0
		);
		return $this->select(NULL,$filter,$options);
	}

	/**
		Count records that match criteria
		@return int
		@param $filter array
	**/
	function count($filter=NULL) {
		return $this->collection->count($filter);
	}

	/**
		Return record at specified offset using criteria of previous
		load() call and make it active
		@return array
		@param $ofs int
	**/
	function skip($ofs=1) {
		$this->document=($out=parent::skip($ofs))?$out->document:array();
		return $out;
	}

	/**
		Insert new record
		@return array
	**/
	function insert() {
		$this->collection->insert($this->document);
		parent::reset();
		return $this->document;
	}

	/**
		Update current record
		@return array
	**/
	function update() {
		$this->collection->update(
			array('_id'=>$this->document['_id']),$this->document);
		return $this->document;
	}

	/**
		Delete current record
		@return bool
		@param $filter array
	**/
	function erase($filter=NULL) {
		if ($filter)
			return $this->collection->remove($filter);
		$result=$this->collection->
			remove(array('_id'=>$this->document['_id']));
		parent::erase();
		$this->skip(0);
		return $result;
	}

	//! Reset cursor
	function reset() {
		$this->document=array();
		parent::reset();
	}

	/**
		Hydrate mapper object using hive array variable
		@return NULL
		@param $key string
	**/
	function copyfrom($key) {
		foreach (\Base::instance()->get($key) as $key=>$val)
			$this->document[$key]=$val;
	}

	/**
		Populate hive array variable with mapper fields
		@return NULL
		@param $key string
	**/
	function copyto($key) {
		$var=&\Base::instance()->ref($key);
		foreach ($this->document as $key=>$field)
			$var[$key]=$field;
	}

	/**
		Instantiate class
		@return void
		@param $db object
		@param $collection string
	**/
	function __construct(\DB\Mongo $db,$collection) {
		$this->db=$db;
		$this->collection=$db->selectcollection($collection);
		$this->reset();
	}

}

//! Custom session handler
class Session extends Mapper {

	/**
		Open session
		@return TRUE
		@param $path string
		@param $name string
	**/
	function open($path,$name) {
		return TRUE;
	}

	/**
		Close session
		@return TRUE
	**/
	function close() {
		return TRUE;
	}

	/**
		Return session data in serialized format
		@return string|FALSE
		@param $id string
	**/
	function read($id) {
		$this->load(array('session_id'=>$id));
		return $this->dry()?FALSE:$this->get('data');
	}

	/**
		Write session data
		@return TRUE
		@param $id string
		@param $data string
	**/
	function write($id,$data) {
		$this->load(array('session_id'=>$id));
		$this->set('session_id',$id);
		$this->set('data',$data);
		$this->set('stamp',time());
		$this->save();
		return TRUE;
	}

	/**
		Destroy session
		@return TRUE
		@param $id string
	**/
	function destroy($id) {
		$this->erase(array('session_id'=>$id));
		return TRUE;
	}

	/**
		Garbage collector
		@return TRUE
		@param $max int
	**/
	function cleanup($max) {
		$this->erase(array('$where'=>'this.stamp+'.$max.'<'.time()));
		return TRUE;
	}

	/**
		Instantiate class
		@param $db object
		@param $table string
	**/
	function __construct(\DB\Mongo $db,$table='sessions') {
		parent::__construct($db,$table);
		session_set_save_handler(
			array($this,'open'),
			array($this,'close'),
			array($this,'read'),
			array($this,'write'),
			array($this,'destroy'),
			array($this,'cleanup')
		);
	}

	//! Wrap-up
	function __destruct() {
		session_commit();
	}

}
