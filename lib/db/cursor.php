<?php

/*
	Copyright (c) 2009-2012 F3::Factory/Bong Cosca, All rights reserved.

	This file is part of the Fat-Free Framework (http://fatfree.sf.net).

	THE SOFTWARE AND DOCUMENTATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF
	ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR
	PURPOSE.

	Please see the license.txt file for more information.
*/

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
		Return array containing subset of records matching criteria,
		number of subsets available, and actual subset position
		@return array
		@param $pos int
		@param $size int
		@param $filter string|array
		@param $options array
	**/
	function paginate($pos=0,$size=10,$filter=NULL,array $options=NULL) {
		return array(
			'subset'=>$this->find($filter,
				array_merge(
					$options?:array(),
					array('limit'=>$size,'offset'=>$pos*$size)
				)
			),
			'count'=>($count=ceil($this->count($filter,$options)/$size)),
			'pos'=>($pos && $pos<$count?$pos:NULL)
		);
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
		@return mixed
	**/
	function first() {
		return $this->query[$this->ptr=0];
	}

	/**
		Move pointer to last record in cursor
		@return mixed
	**/
	function last() {
		return $this->query[$this->ptr=($ctr=count($this->query))?$ctr-1:0];
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

	/**
		Reset cursor
		@return NULL
	**/
	function reset() {
		$this->query=array();
		$this->ptr=0;
	}

}
