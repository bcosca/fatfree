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
		Return records that match criteria
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
		Return first record that matches criteria
		@return array|FALSE
		@param $filter string|array
		@param $options array
	**/
	function findone($filter=NULL,array $options=NULL) {
		return ($data=$this->find($filter,$options))?$data[0]:FALSE;
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

	/**
		Move pointer to first record in cursor
		@return NULL
	**/
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

	/**
		Reset cursor
		@return NULL
	**/
	function reset() {
		$this->query=array();
		$this->ptr=0;
	}

}
