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

namespace DB;

//! In-memory/flat-file DB wrapper
class Jig {

	//@{ Storage formats
	const
		FORMAT_JSON=0,
		FORMAT_Serialized=1;
	//@}

	protected
		//! UUID
		$uuid,
		//! Storage location
		$dir,
		//! Current storage format
		$format,
		//! Jig log
		$log,
		//! Memory-held data
		$data,
		//! lazy load/save files
		$lazy;

	/**
	*	Read data from memory/file
	*	@return array
	*	@param $file string
	**/
	function &read($file) {
		if (!$this->dir || !is_file($dst=$this->dir.$file)) {
			if (!isset($this->data[$file]))
				$this->data[$file]=[];
			return $this->data[$file];
		}
		if ($this->lazy && isset($this->data[$file]))
			return $this->data[$file];
		$fw=\Base::instance();
		$raw=$fw->read($dst);
		switch ($this->format) {
			case self::FORMAT_JSON:
				$data=json_decode($raw,TRUE);
				break;
			case self::FORMAT_Serialized:
				$data=$fw->unserialize($raw);
				break;
		}
		$this->data[$file] = $data;
		return $this->data[$file];
	}

	/**
	*	Write data to memory/file
	*	@return int
	*	@param $file string
	*	@param $data array
	**/
	function write($file,array $data=NULL) {
		if (!$this->dir || $this->lazy)
			return count($this->data[$file]=$data);
		$fw=\Base::instance();
		switch ($this->format) {
			case self::FORMAT_JSON:
				$out=json_encode($data,JSON_PRETTY_PRINT);
				break;
			case self::FORMAT_Serialized:
				$out=$fw->serialize($data);
				break;
		}
		return $fw->write($this->dir.$file,$out);
	}

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
	*	Return profiler results (or disable logging)
	*	@param $flag bool
	*	@return string
	**/
	function log($flag=TRUE) {
		if ($flag)
			return $this->log;
		$this->log=FALSE;
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
	*	Clean storage
	*	@return NULL
	**/
	function drop() {
		if ($this->lazy) // intentional
			$this->data=[];
		if (!$this->dir)
			$this->data=[];
		elseif ($glob=@glob($this->dir.'/*',GLOB_NOSORT))
			foreach ($glob as $file)
				@unlink($file);
	}

	//! Prohibit cloning
	private function __clone() {
	}

	/**
	*	Instantiate class
	*	@param $dir string
	*	@param $format int
	**/
	function __construct($dir=NULL,$format=self::FORMAT_JSON,$lazy=FALSE) {
		if ($dir && !is_dir($dir))
			mkdir($dir,\Base::MODE,TRUE);
		$this->uuid=\Base::instance()->hash($this->dir=$dir);
		$this->format=$format;
		$this->lazy=$lazy;
	}

	/**
	*	save file on destruction
	**/
	function __destruct() {
		if ($this->lazy) {
			$this->lazy = FALSE;
			foreach ($this->data?:[] as $file => $data)
				$this->write($file,$data);
		}
	}

}
