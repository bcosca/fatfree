<?php

namespace DB;

//! Simple cursor implementation
abstract class Cursor extends \Magic {

	//@{ Error messages
	const
		E_Field='Undefined field %s';
	//@}

	protected
		//! Query results
		$query=array(),
		//! Current position
		$ptr=0;

	/**
		Return fields of mapper object as an associative array
		@return array
		@param $obj object
	**/
	abstract function cast($obj=NULL);

	/**
		Return records (array of mapper objects) that match criteria
		@return array
		@param $filter string|array
		@param $options array
	**/
	abstract function find($filter=NULL,array $options=NULL);

	/**
		Insert new record
		@return array
	**/
	abstract function insert();

	/**
		Update current record
		@return array
	**/
	abstract function update();

	/**
		Return TRUE if current cursor position is not mapped to any record
		@return bool
	**/
	function dry() {
		return !(bool)$this->query;
	}

	/**
		Return first record (mapper object) that matches criteria
		@return object|FALSE
		@param $filter string|array
		@param $options array
	**/
	function findone($filter=NULL,array $options=NULL) {
		return ($data=$this->find($filter,$options))?$data[0]:FALSE;
	}

	/**
		Return records (array of associative arrays) that match criteria
		@return array
		@param $filter string|array
		@param $options array
	**/
	function afind($filter=NULL,array $options=NULL) {
		return array_map(array($this,'cast'),$this->find($filter,$options));
	}

	/**
		Return first record (associative array) that matches criteria
		@return array|FALSE
		@param $filter string|array
		@param $options array
	**/
	function afindone($filter=NULL,array $options=NULL) {
		return ($found=$this->findone($filter,$options))?
			$found->cast():FALSE;
	}

	/**
		Map to first record that matches criteria
		@return array|FALSE
		@param $filter string|array
		@param $options array
	**/
	function load($filter=NULL,array $options=NULL) {
		return ($this->query=$this->find($filter,$options)) &&
			$this->skip(0)?$this->query[$this->ptr=0]:FALSE;
	}

	//! Move pointer to first record in cursor
	function rewind() {
		$this->ptr=0;
	}

	/**
		Map to nth record relative to current cursor position
		@return mixed
		@param $ofs int
	**/
	function skip($ofs=1) {
		$ofs+=$this->ptr;
		return $ofs>-1 && $ofs<count($this->query)?
			$this->query[$this->ptr=$ofs]:FALSE;
	}

	/**
		Map next record
		@return mixed
	**/
	function next() {
		return $this->skip();
	}

	/**
		Map previous record
		@return mixed
	**/
	function prev() {
		return $this->skip(-1);
	}

	/**
		Save mapped record
		@return mixed
	**/
	function save() {
		return $this->query?$this->update():$this->insert();
	}

	/**
		Delete current record
		@return int|bool
	**/
	function erase() {
		$this->query=array_slice($this->query,0,$this->ptr,TRUE)+
			array_slice($this->query,$this->ptr,NULL,TRUE);
		$this->ptr=0;
	}

	//! Reset cursor
	function reset() {
		$this->query=array();
		$this->ptr=0;
	}

}
