<?php

/*
	Copyright (c) 2009-2014 F3::Factory/Bong Cosca, All rights reserved.

	This file is part of the Fat-Free Framework (http://fatfree.sf.net).

	THE SOFTWARE AND DOCUMENTATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF
	ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR
	PURPOSE.

	Please see the license.txt file for more information.
*/

namespace DB;

//! In memory version of Jig
class MemoryJig extends AbstractJig {

	protected
		$data;

	/**
	*	Instantiate class
	*	@param $dir string
	*	@param $format int
	**/
	function __construct($dir=null,$format=null) {
		$dir=$dir?$dir:rand()."";
		parent::__construct($dir,parent::FORMAT_Memory);
	}

	function read($file) {
		return $this->data[$file];
	}

	function write($file,array $data) {
		$this->data[$file]=$data;
	}

	function drop() {
		return $this->data=array();
	}
}
