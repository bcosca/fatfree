<?php

//! Cache-based session handler
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
		return Cache::instance()->exists($id.'.@',$data)?$data['data']:FALSE;
	}

	/**
		Write session data
		@return TRUE
		@param $id string
		@param $data string
	**/
	function write($id,$data) {
		$fw=Base::instance();
		$req=$fw->get('HEADERS');
		$jar=session_get_cookie_params();
		Cache::instance()->set($id.'.@',
			array(
				'data'=>$data,
				'ip'=>$fw->get('IP'),
				'agent'=>isset($req['User-Agent'])?$req['User-Agent']:'',
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
		Cache::instance()->clear($id.'.@');
		return TRUE;
	}

	/**
		Garbage collector
		@return TRUE
		@param $max int
	**/
	function cleanup($max) {
		Cache::instance()->reset('.@',$max);
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
		return Cache::instance()->exists($id.'.@',$data)?
			$data['ip']:FALSE;
	}

	/**
		Return Unix timestamp associated with specified session ID
		@return string|FALSE
		@param $id string
	**/
	function stamp($id=NULL) {
		if (!$id)
			$id=session_id();
		return Cache::instance()->exists($id.'.@',$data)?
			$data['stamp']:FALSE;
	}

	/**
		Return HTTP user agent associated with specified session ID
		@return string|FALSE
		@param $id string
	**/
	function agent($id=NULL) {
		if (!$id)
			$id=session_id();
		return Cache::instance()->exists($id.'.@',$data)?
			$data['agent']:FALSE;
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
		register_shutdown_function('session_commit');
	}

}
