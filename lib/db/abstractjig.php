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

//! Simple abstract base for Jig DBs.
abstract class AbstractJig {

	//@{ Storage formats
	const
		FORMAT_JSON=0,
		FORMAT_Serialized=1,
		FORMAT_Memory=2;
	//@}

	protected
		//! UUID
		$uuid,
		//! Storage location
		$dir,
		//! Current storage format
		$format,
		//! Jig log
		$log;

	abstract function read($file);
	
	abstract function write($file, array $data);
	
	abstract function drop();
	
	/**
	*	Return directory
	*	@return string
	**/
	function dir() {
		return $this->dir;
	}

	/**
	*	Return UUID
	*	@return string
	**/
	function uuid() {
		return $this->uuid;
	}

	/**
	*	Return SQL profiler results
	*	@return string
	**/
	function log() {
		return $this->log;
	}

	/**
	*	Jot down log entry
	*	@return NULL
	*	@param $frame string
	**/
	function jot($frame) {
		if ($frame)
			$this->log.=date('r').' '.$frame.PHP_EOL;
	}

	/**
	*	Instantiate class
	*	@param $dir string
	*	@param $format int
	**/
	function __construct($dir,$format=self::FORMAT_JSON) {
		$this->uuid=\Base::instance()->hash($this->dir=$dir);
		$this->format=$format;
	}

}
