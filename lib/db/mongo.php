<?php

/*

	Copyright (c) 2009-2017 F3::Factory/Bong Cosca, All rights reserved.

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

//! MongoDB wrapper
class Mongo {

	//@{
	const
		E_Profiler='MongoDB profiler is disabled';
	//@}

	protected
		//! UUID
		$uuid,
		//! Data source name
		$dsn,
		//! MongoDB object
		$db,
		//! Legacy flag
		$legacy,
		//! MongoDB log
		$log;

	/**
	*	Return data source name
	*	@return string
	**/
	function dsn() {
		return $this->dsn;
	}

	/**
	*	Return UUID
	*	@return string
	**/
	function uuid() {
		return $this->uuid;
	}

	/**
	*	Return MongoDB profiler results (or disable logging)
	*	@param $flag bool
	*	@return string
	**/
	function log($flag=TRUE) {
		if ($flag) {
			$cursor=$this->db->selectcollection('system.profile')->find();
			foreach (iterator_to_array($cursor) as $frame)
				if (!preg_match('/\.system\..+$/',$frame['ns']))
					$this->log.=date('r',$this->legacy() ?
						$frame['ts']->sec : (round((string)$frame['ts'])/1000)).
						' ('.sprintf('%.1f',$frame['millis']).'ms) '.
						$frame['ns'].' ['.$frame['op'].'] '.
						(empty($frame['query'])?
							'':json_encode($frame['query'])).
						(empty($frame['command'])?
							'':json_encode($frame['command'])).
						PHP_EOL;
		} else {
			$this->log=FALSE;
			if ($this->legacy)
				$this->db->setprofilinglevel(-1);
			else
				$this->db->command(['profile'=>-1]);
		}
		return $this->log;
	}

	/**
	*	Intercept native call to re-enable profiler
	*	@return int
	**/
	function drop() {
		$out=$this->db->drop();
		if ($this->log!==FALSE) {
			if ($this->legacy)
				$this->db->setprofilinglevel(2);
			else
				$this->db->command(['profile'=>2]);
		}
		return $out;
	}

	/**
	*	Redirect call to MongoDB object
	*	@return mixed
	*	@param $func string
	*	@param $args array
	**/
	function __call($func,array $args) {
		return call_user_func_array([$this->db,$func],$args);
	}

	/**
	*	Return TRUE if legacy driver is loaded
	*	@return bool
	**/
	function legacy() {
		return $this->legacy;
	}

	//! Prohibit cloning
	private function __clone() {
	}

	/**
	*	Instantiate class
	*	@param $dsn string
	*	@param $dbname string
	*	@param $options array
	**/
	function __construct($dsn,$dbname,array $options=NULL) {
		$this->uuid=\Base::instance()->hash($this->dsn=$dsn);
		if ($this->legacy=class_exists('\MongoClient')) {
			$this->db=new \MongoDB(new \MongoClient($dsn,$options?:[]),$dbname);
			$this->db->setprofilinglevel(2);
		}
		else {
			$this->db=(new \MongoDB\Client($dsn,$options?:[]))->$dbname;
			$this->db->command(['profile'=>2]);
		}
	}

}
