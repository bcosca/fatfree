<?php

namespace DB;

//! Flat-file DB wrapper
class Jig {

	//@{ Storage formats
	const
		FORMAT_JSON=0,
		FORMAT_Serialized=1;
	//@}

	protected
		//! Storage location
		$dir,
		//! Current storage format
		$format;

	/**
		Read data from file
		@return array
		@param $file
	**/
	function read($file) {
		$fw=\Base::instance();
		if (!is_file($dst=$this->dir.$file))
			return array();
		$raw=$fw->read($dst);
		switch ($this->format) {
			case self::FORMAT_JSON:
				$data=json_decode($raw,TRUE);
				break;
			case self::FORMAT_Serialized:
				$data=$fw->unserialize($raw);
				break;
		}
		return $data;
	}

	/**
		Write data to file
		@return int
		@param $file string
		@param $data array
	**/
	function write($file,array $data=NULL) {
		$fw=\Base::instance();
		switch ($this->format) {
			case self::FORMAT_JSON:
				$out=json_encode($data);
				break;
			case self::FORMAT_Serialized:
				$out=$fw->serialize($data);
				break;
		}
		return $fw->write($this->dir.$file,$out);
	}

	//! Clean storage
	function drop() {
		$fw=\Base::instance();
		foreach (glob($this->dir.'/*',GLOB_NOSORT) as $file)
			@$fw->unlink($file);
	}

	/**
		Instantiate class
		@param $dir string
		@param $format int
	**/
	function __construct($dir,$format=self::FORMAT_JSON) {
		if (!is_dir($dir))
			mkdir($dir,\Base::MODE,TRUE);
		$this->dir=$dir;
		$this->format=$format;
	}

}
