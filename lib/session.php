<?php

/*
	Copyright (c) 2009-2013 F3::Factory/Bong Cosca, All rights reserved.

	This file is part of the Fat-Free Framework (http://fatfree.sf.net).

	THE SOFTWARE AND DOCUMENTATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF
	ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR
	PURPOSE.

	Please see the license.txt file for more information.
*/

//! Cache-based session handler
class Session {

	/**
	*	Open session
	*	@return TRUE
	*	@param $path string
	*	@param $name string
	**/
	function open($path,$name) {
		return TRUE;
	}

	/**
	*	Close session
	*	@return TRUE
	**/
	function close() {
		return TRUE;
	}

	/**
	*	Return session data in serialized format
	*	@return string|FALSE
	*	@param $id string
	**/
	function read($id) {
		return Cache::instance()->exists($id.'.@',$data)?$data['data']:FALSE;
	}

	/**
	*	Write session data
	*	@return TRUE
	*	@param $id string
	*	@param $data string
	**/
	function write($id,$data) {
		$fw=Base::instance();
		$sent=headers_sent();
		$headers=$fw->get('HEADERS');
		$csrf=$fw->hash($fw->get('ROOT').$fw->get('BASE')).'.'.
			$fw->hash(mt_rand());
		$jar=$fw->get('JAR');
		Cache::instance()->set($id.'.@',
			array(
				'data'=>$data,
				'csrf'=>$sent?$this->csrf():$csrf,
				'ip'=>$fw->get('IP'),
				'agent'=>isset($headers['User-Agent'])?
					$headers['User-Agent']:'',
				'stamp'=>time()
			),
			$jar['expire']?($jar['expire']-time()):0
		);
		if (!$sent) {
			if ($_COOKIE['_'])
				setcookie('_','',strtotime('-1 year'));
			call_user_func_array('setcookie',array('_',$csrf)+$jar);
		}
		return TRUE;
	}

	/**
	*	Destroy session
	*	@return TRUE
	*	@param $id string
	**/
	function destroy($id) {
		Cache::instance()->clear($id.'.@');
		setcookie(session_name(),'',strtotime('-1 year'));
		unset($_COOKIE[session_name()]);
		header_remove('Set-Cookie');
		return TRUE;
	}

	/**
	*	Garbage collector
	*	@return TRUE
	*	@param $max int
	**/
	function cleanup($max) {
		Cache::instance()->reset('.@',$max);
		return TRUE;
	}

	/**
	*	Return anti-CSRF tokan associated with specified session ID
	*	@return string|FALSE
	*	@param $id string
	**/
	function csrf($id=NULL) {
		return Cache::instance()->exists(($id?:session_id()).'.@',$data)?
			$data['csrf']:FALSE;
	}

	/**
	*	Return IP address associated with specified session ID
	*	@return string|FALSE
	*	@param $id string
	**/
	function ip($id=NULL) {
		return Cache::instance()->exists(($id?:session_id()).'.@',$data)?
			$data['ip']:FALSE;
	}

	/**
	*	Return Unix timestamp associated with specified session ID
	*	@return string|FALSE
	*	@param $id string
	**/
	function stamp($id=NULL) {
		return Cache::instance()->exists(($id?:session_id()).'.@',$data)?
			$data['stamp']:FALSE;
	}

	/**
	*	Return HTTP user agent associated with specified session ID
	*	@return string|FALSE
	*	@param $id string
	**/
	function agent($id=NULL) {
		return Cache::instance()->exists(($id?:session_id()).'.@',$data)?
			$data['agent']:FALSE;
	}

	/**
	*	Instantiate class
	*	@return object
	**/
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
		@session_start();
		$fw=\Base::instance();
		$headers=$fw->get('HEADERS');
		if (($csrf=$this->csrf()) &&
			((!isset($_COOKIE['_']) || $_COOKIE['_']!=$csrf) ||
			($ip=$this->ip()) && $ip!=$fw->get('IP') ||
			($agent=$this->agent()) && !isset($headers['User-Agent']) ||
				$agent!=$headers['User-Agent'])) {
			$jar=$fw->get('JAR');
			$jar['expire']=strtotime('-1 year');
			call_user_func_array('setcookie',
				array_merge(array('_',''),$jar));
			unset($_COOKIE['_']);
			session_destroy();
			\Base::instance()->error(403);
		}
		$csrf=$fw->hash($fw->get('ROOT').$fw->get('BASE')).'.'.
			$fw->hash(mt_rand());
		$jar=$fw->get('JAR');
		if (Cache::instance()->exists($id=session_id().'.@',$data)) {
			$data['csrf']=$csrf;
			Cache::instance()->set($id.'.@',
				$data,
				$jar['expire']?($jar['expire']-time()):0
			);
			call_user_func_array('setcookie',array('_',$csrf)+$jar);
		}
	}

}
