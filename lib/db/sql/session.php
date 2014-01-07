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

namespace DB\SQL;

//! SQL-managed session handler
class Session extends Mapper {

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
		$this->load(array('session_id=?',$id));
		return $this->dry()?FALSE:$this->get('data');
	}

	/**
	*	Write session data
	*	@return TRUE
	*	@param $id string
	*	@param $data string
	**/
	function write($id,$data) {
		$fw=\Base::instance();
		$sent=headers_sent();
		$headers=$fw->get('HEADERS');
		$this->load(array('session_id=?',$id));
		$csrf=$fw->hash($fw->get('ROOT').$fw->get('BASE')).'.'.
			$fw->hash(mt_rand());
		$this->set('session_id',$id);
		$this->set('data',$data);
		$this->set('csrf',$sent?$this->csrf():$csrf);
		$this->set('ip',$fw->get('IP'));
		$this->set('agent',
			isset($headers['User-Agent'])?$headers['User-Agent']:'');
		$this->set('stamp',time());
		$this->save();
		if (!$sent) {
			if (isset($_COOKIE['_']))
				setcookie('_','',strtotime('-1 year'));
			call_user_func_array('setcookie',
				array('_',$csrf)+$fw->get('JAR'));
		}
		return TRUE;
	}

	/**
	*	Destroy session
	*	@return TRUE
	*	@param $id string
	**/
	function destroy($id) {
		$this->erase(array('session_id=?',$id));
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
		$this->erase(array('stamp+?<?',$max,time()));
		return TRUE;
	}

	/**
	*	Return anti-CSRF tokan associated with specified session ID
	*	@return string|FALSE
	*	@param $id string
	**/
	function csrf($id=NULL) {
		$this->load(array('session_id=?',$id?:session_id()));
		return $this->dry()?FALSE:$this->get('csrf');
	}

	/**
	*	Return IP address associated with specified session ID
	*	@return string|FALSE
	*	@param $id string
	**/
	function ip($id=NULL) {
		$this->load(array('session_id=?',$id?:session_id()));
		return $this->dry()?FALSE:$this->get('ip');
	}

	/**
	*	Return Unix timestamp associated with specified session ID
	*	@return string|FALSE
	*	@param $id string
	**/
	function stamp($id=NULL) {
		$this->load(array('session_id=?',$id?:session_id()));
		return $this->dry()?FALSE:$this->get('stamp');
	}

	/**
	*	Return HTTP user agent associated with specified session ID
	*	@return string|FALSE
	*	@param $id string
	**/
	function agent($id=NULL) {
		$this->load(array('session_id=?',$id?:session_id()));
		return $this->dry()?FALSE:$this->get('agent');
	}

	/**
	*	Instantiate class
	*	@param $db object
	*	@param $table string
	**/
	function __construct(\DB\SQL $db,$table='sessions') {
		$db->exec(
			(preg_match('/mssql|sqlsrv|sybase/',$db->driver())?
				('IF NOT EXISTS (SELECT * FROM sysobjects WHERE '.
					'name='.$db->quote($table).' AND xtype=\'U\') '.
					'CREATE TABLE dbo.'):
				('CREATE TABLE IF NOT EXISTS '.
					(($name=$db->name())?($name.'.'):''))).
			$table.' ('.
				'session_id VARCHAR(40),'.
				'data TEXT,'.
				'csrf TEXT,'.
				'ip VARCHAR(40),'.
				'agent VARCHAR(255),'.
				'stamp INTEGER,'.
				'PRIMARY KEY(session_id)'.
			');'
		);
		parent::__construct($db,$table);
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
		if ($this->load(array('session_id=?',session_id()))) {
			$this->set('csrf',$csrf);
			$this->save();
			call_user_func_array('setcookie',
				array('_',$csrf)+$fw->get('JAR'));
		}
	}

}
