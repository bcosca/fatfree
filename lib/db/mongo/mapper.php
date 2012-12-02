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
		if (array_key_exists($key,$this->document))
			return $this->document[$key];
		trigger_error(sprintf(self::E_Field,$key));
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
		foreach ($row as $field=>$val)
			$mapper->document[$field]=$val;
		return $mapper;
	}

	/**
		Return fields of mapper object as an associative array
		@return array
		@param $obj Mapper
	**/
	function cast(Mapper $obj=NULL) {
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
		parent::reset();
		return $result;
	}

	/**
		Reset cursor
		@return NULL
	**/
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
