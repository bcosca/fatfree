<?php

namespace DB;

//! Flat-file DB wrapper
class Jig {

	//@{ Storage formats
	const
		FORMAT_Serialized=0,
		FORMAT_JSON=1;
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
		if (!is_file($this->dir.$file))
			return array();
		$raw=$fw->read($this->dir.$file);
		switch ($this->format) {
			case self::FORMAT_Serialized:
				$data=$fw->unserialize($raw);
				break;
			case self::FORMAT_JSON:
				$data=json_decode($raw,TRUE);
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
			case self::FORMAT_Serialized:
				$out=$fw->serialize($data);
				break;
			case self::FORMAT_JSON:
				$out=json_encode($data);
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
	function __construct($dir,$format=self::FORMAT_Serialized) {
		if (!is_dir($dir))
			\Base::instance()->mkdir($dir);
		$this->dir=$dir;
		$this->format=$format;
	}

}
