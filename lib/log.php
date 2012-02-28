<?php

/**
	Custom Log for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Log
		@version 2.0.9
**/

//! Custom log plugin
class Log extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_LogOpen='Unable to open log file',
		TEXT_LogLock='Unable to gain exclusive access to log file';
	//@}

	const
		//! Seconds before framework gives up trying to lock resource
		LOG_Timeout=30,
		//! Maximum log file size
		LOG_Size='2M';

	//@{
	//! Log file properties
	private
		$filename,$handle;
	//@}

	/**
		Return TRUE if log file is locked before timer expires
			@return boolean
			@private
	**/
	private function ready() {
		$time=microtime(TRUE);
		while (!flock($this->handle,LOCK_EX)) {
			if ((microtime(TRUE)-$time)>self::LOG_Timeout)
				// Give up
				return FALSE;
			usleep(mt_rand(0,100));
		}
		return TRUE;
	}

	/**
		Write specified text to log file
			@param $text string
			@public
	**/
	function write($text) {
		if (!$this->ready()) {
			// Lock attempt failed
			trigger_error(self::TEXT_LogLock);
			return;
		}
		clearstatcache();
		if (filesize($this->filename)>self::bytes(self::LOG_Size)) {
			// Perform log rotation sequence
			if (is_file($this->filename.'.1'))
				copy($this->filename.'.1',$this->filename.'.2');
			copy($this->filename,$this->filename.'.1');
			ftruncate($this->handle,0);
		}
		// Prepend text with timestamp, source IP, file name and
		// line number for tracking origin
		$trace=debug_backtrace(FALSE);
		fwrite(
			$this->handle,
			date('r').' ['.$_SERVER['REMOTE_ADDR'].'] '.
				self::fixslashes($trace[0]['file']).':'.
				$trace[0]['line'].' '.
				preg_replace('/\s+/',' ',$text)."\n"
		);
		flock($this->handle,LOCK_UN);
	}

	/**
		Logger constructor
			@param $file string
			@public
	**/
	function __construct($file) {
		$this->filename=$this->ref('LOGS').$file;
		$this->handle=fopen($file,'a+');
		if (!is_resource($this->handle)) {
			// Unable to open file
			trigger_error(self::TEXT_LogOpen);
			return;
		}
	}

	/**
		Logger destructor
			@public
	**/
	function __destruct() {
		if (is_resource($this->handle))
			fclose($this->handle);
	}

}
