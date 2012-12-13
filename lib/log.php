<?php

//! Custom logger
class Log {

	protected
		//! File name
		$file;

	/**
		Write specified text to log file
		@return string
		@param $text string
		@param $format string
	**/
	function write($text,$format='r') {
		$fw=Base::instance();
		$fw->write(
			$this->file,
			date($format).
				(isset($_SERVER['REMOTE_ADDR'])?
					(' ['.$_SERVER['REMOTE_ADDR'].']'):'').' '.
			trim($text)."\n",
			TRUE
		);
	}

	//! Erase log
	function erase() {
		Base::instance()->unlink($this->file);
	}

	/**
		Instantiate class
		@param $file string
	**/
	function __construct($file) {
		$fw=Base::instance();
		if (!is_dir($dir=$fw->get('LOGS')))
			mkdir($dir,Base::MODE,TRUE);
		$this->file=$dir.$file;
	}

}
