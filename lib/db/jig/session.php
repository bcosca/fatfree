<?php

namespace DB\Jig;

//! Custom Jig-managed session handler
class Session extends Mapper {

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
		$this->load(array('@session_id==?',$id));
		return $this->dry()?FALSE:$this->get('data');
	}

	/**
		Write session data
		@return TRUE
		@param $id string
		@param $data string
	**/
	function write($id,$data) {
		$fw=\Base::instance();
		$req=$fw->get('HEADERS');
		$this->load(array('@session_id==?',$id));
		$this->set('session_id',$id);
		$this->set('data',$data);
		$this->set('ip',$fw->get('IP'));
		$this->set('agent',isset($req['User-Agent'])?$req['User-Agent']:'');
		$this->set('stamp',time());
		$this->save();
		return TRUE;
	}

	/**
		Destroy session
		@return TRUE
		@param $id string
	**/
	function destroy($id) {
		$this->erase(array('@session_id==?',$id));
		return TRUE;
	}

	/**
		Garbage collector
		@return TRUE
		@param $max int
	**/
	function cleanup($max) {
		$this->erase(array('@stamp+?<?',$max,time()));
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
		$this->load(array('@session_id==?',$id));
		return $this->dry()?FALSE:$this->get('ip');
	}

	/**
		Return Unix timestamp associated with specified session ID
		@return string|FALSE
		@param $id string
	**/
	function stamp($id=NULL) {
		if (!$id)
			$id=session_id();
		$this->load(array('@session_id==?',$id));
		return $this->dry()?FALSE:$this->get('stamp');
	}

	/**
		Return HTTP user agent associated with specified session ID
		@return string|FALSE
		@param $id string
	**/
	function agent($id=NULL) {
		if (!$id)
			$id=session_id();
		$this->load(array('@session_id==?',$id));
		return $this->dry()?FALSE:$this->get('agent');
	}

	/**
		Instantiate class
		@param $db object
		@param $table string
	**/
	function __construct(\DB\Jig $db,$table='sessions') {
		parent::__construct($db,'sessions');
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
