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

//! Flat-file DB wrapper
class Jig extends AbstractJig {

	/**
	*	Read data from file
	*	@return array
	*	@param $file string
	**/
	function read($file) {
		$fw=\Base::instance();
		if (!is_file($dst=$this->dir.$file))
			return array();
		$raw=$fw->read($dst);
		switch ($this->format) {
			case parent::FORMAT_JSON:
				$data=json_decode($raw,TRUE);
				break;
			case parent::FORMAT_Serialized:
				$data=$fw->unserialize($raw);
				break;
		}
		return $data;
	}

	/**
	*	Write data to file
	*	@return int
	*	@param $file string
	*	@param $data array
	**/
	function write($file,array $data=NULL) {
		$fw=\Base::instance();
		switch ($this->format) {
			case parent::FORMAT_JSON:
				$out=json_encode($data,@constant('JSON_PRETTY_PRINT'));
				break;
			case parent::FORMAT_Serialized:
				$out=$fw->serialize($data);
				break;
		}
		return $fw->write($this->dir.$file,$out);
	}

	/**
	*	Clean storage
	*	@return NULL
	**/
	function drop() {
		if ($glob=@glob($this->dir.'/*',GLOB_NOSORT))
			foreach ($glob as $file)
				@unlink($file);
	}

	/**
	*	Instantiate class
	*	@param $dir string
	*	@param $format int
	**/
	function __construct($dir,$format=parent::FORMAT_JSON) {
		parent::__construct($dir,$format);
		if (!is_dir($dir))
			mkdir($dir,\Base::MODE,TRUE);
	}

}
