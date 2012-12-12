<?php

namespace Cache;

//! Custom cache-based session handler
class Session {

	/**
		Open session
		@return TRUE
		@param $path string
		@param $name string
	**/
	function open($path,$name) {
		return TRUE;
	}

	/**
		Close session
		@return TRUE
	**/
	function close() {
		return TRUE;
	}

	/**
		Return session data in serialized format
		@return string|FALSE
		@param $id string
	**/
	function read($id) {
		return \Cache::instance()->exists($id.'.@',$data)?
			$data[0]['data']:FALSE;
	}

	/**
		Write session data
		@return TRUE
		@param $id string
		@param $data string
	**/
	function write($id,$data) {
		$jar=session_get_cookie_params();
		\Cache::instance()->set($id.'.@',
			array(
				'data'=>$data,
				'ip'=>isset($_SERVER['HTTP_CLIENT_IP'])?
					// Behind proxy
					$_SERVER['HTTP_CLIENT_IP']:
					(isset($_SERVER['HTTP_X_FORWARDED_FOR'])?
						// Use first IP address in list
						current(explode(',',
							$_SERVER['HTTP_X_FORWARDED_FOR'])):
						$_SERVER['REMOTE_ADDR']),
				'agent'=>isset($_SERVER['HTTP_USER_AGENT'])?
					$_SERVER['HTTP_USER_AGENT']:'',
				'stamp'=>time()
			),
			$jar['lifetime']
		);
		return TRUE;
	}

	/**
		Destroy session
		@return TRUE
		@param $id string
	**/
	function destroy($id) {
		\Cache::instance()->clear($id.'.@');
		return TRUE;
	}

	/**
		Garbage collector
		@return TRUE
		@param $max int
	**/
	function cleanup($max) {
		\Cache::instance()->reset('.@',$max);
		return TRUE;
	}

	/**
		Return IP address associated with specified session ID
		@return string|FALSE
		@param $id string
	**/
	function ip($id=NULL) {
		if (!$id)
			$id=session_id();
		return \Cache::instance()->exists($id.'.@',$data)?
			$data[0]['ip']:FALSE;
	}

	/**
		Return Unix timestamp associated with specified session ID
		@return string|FALSE
		@param $id string
	**/
	function stamp($id=NULL) {
		if (!$id)
			$id=session_id();
		return \Cache::instance()->exists($id.'.@',$data)?
			$data[0]['stamp']:FALSE;
	}

	/**
		Return HTTP user agent associated with specified session ID
		@return string|FALSE
		@param $id string
	**/
	function agent($id=NULL) {
		if (!$id)
			$id=session_id();
		return \Cache::instance()->exists($id.'.@',$data)?
			$data[0]['agent']:FALSE;
	}

	//! Instantiate class
	function __construct() {
		session_set_save_handler(
			array($this,'open'),
			array($this,'close'),
			array($this,'read'),
			array($this,'write'),
			array($this,'destroy'),
			array($this,'cleanup')
		);
	}

	//! Wrap-up
	function __destruct() {
		session_commit();
	}

}
