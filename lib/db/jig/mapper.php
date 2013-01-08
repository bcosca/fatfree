<?php

/*
	Copyright (c) 2009-2012 F3::Factory/Bong Cosca, All rights reserved.

	This file is part of the Fat-Free Framework (http://fatfree.sf.net).

	THE SOFTWARE AND DOCUMENTATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF
	ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR
	PURPOSE.

	Please see the license.txt file for more information.
*/

namespace DB\Jig;

//! Flat-file DB mapper
class Mapper extends \DB\Cursor {

	protected
		//! Flat-file DB wrapper
		$db,
		//! Data file
		$file,
		//! Document identifier
		$id,
		//! Document contents
		$document=array();

	/**
		Return TRUE if field is defined
		@return bool
		@param $key string
	**/
	function exists($key) {
		return array_key_exists($key,$this->document);
	}

	/**
		Assign value to field
		@return scalar|FALSE
		@param $key string
		@param $val scalar
	**/
	function set($key,$val) {
		return ($key=='_id')?FALSE:($this->document[$key]=$val);
	}

	/**
		Retrieve value of field
		@return scalar|FALSE
		@param $key string
	**/
	function get($key) {
		if ($key=='_id')
			return $this->id;
		if (array_key_exists($key,$this->document))
			return $this->document[$key];
		user_error(sprintf(self::E_Field,$key));
		return FALSE;
	}

	/**
		Delete field
		@return NULL
		@param $key string
	**/
	function clear($key) {
		unset($this->document[$key]);
	}

	/**
		Convert array to mapper object
		@return object
		@param $id string
		@param $row array
	**/
	protected function factory($id,$row) {
		$mapper=clone($this);
		$mapper->reset();
		foreach ($row as $field=>$val) {
			$mapper->id=$id;
			$mapper->document[$field]=$val;
		}
		return $mapper;
	}

	/**
		Return fields of mapper object as an associative array
		@return array
		@param $obj object
	**/
	function cast($obj=NULL) {
		if (!$obj)
			$obj=$this;
		return $obj->document;
	}

	/**
		Convert tokens in string expression to variable names
		@return string
		@param $str string
	**/
	function token($str) {
		$self=$this;
		$str=preg_replace_callback(
			'/(?<!\w)@(\w(?:[\w\.\[\]])*)/',
			function($var) use($self) {
				// Convert from JS dot notation to PHP array notation
				return '$'.preg_replace_callback(
					'/(\.\w+)|\[((?:[^\[\]]*|(?R))*)\]/',
					function($expr) use($self) {
						$fw=Base::instance();
						return 
							'['.
							($expr[1]?
								$fw->stringify(substr($expr[1],1)):
								(preg_match('/^\w+/',
									$mix=$self->token($expr[2]))?
									$fw->stringify($mix):
									$mix)).
							']';
					},
					$var[1]
				);
			},
			$str
		);
		return trim($str);
	}

	/**
		Return records that match criteria
		@return array|FALSE
		@param $filter array
		@param $options array
		@param $log bool
	**/
	function find($filter=NULL,array $options=NULL,$log=TRUE) {
		if (!$options)
			$options=array();
		$options+=array(
			'order'=>NULL,
			'limit'=>0,
			'offset'=>0
		);
		$fw=\Base::instance();
		$db=$this->db;
		$now=microtime(TRUE);
		$data=$db->read($this->file);
		if ($filter) {
			if (!is_array($filter))
				return FALSE;
			// Prefix local variables to avoid conflict with user code
			$_self=$this;
			$_args=isset($filter[1]) && is_array($filter[1])?
				$filter[1]:
				array_slice($filter,1,NULL,TRUE);
			$_args=is_array($_args)?$_args:array(1=>$_args);
			$keys=$vals=array();
			list($_expr)=$filter;
			$data=array_filter($data,
				function($_row) use($_expr,$_args,$_self) {
					extract($_row);
					$_ctr=0;
					// Evaluate pseudo-SQL expression
					return eval('return '.
						preg_replace_callback(
							'/(\:\w+)|(\?)/',
							function($token) use($_args,&$_ctr) {
								// Parameterized query
								if ($token[1])
									// Named
									$key=$token[1];
								else {
									// Positional
									$_ctr++;
									$key=$_ctr;
								}
								// Add slashes to prevent code injection
								return \Base::instance()->stringify(
									is_string($_args[$key])?
										addcslashes($_args[$key],'\''):
										$_args[$key]);
							},
							$_self->token($_expr)
						).';'
					);
				}
			);
		}
		if (isset($options['order']))
			foreach (array_reverse($fw->split($options['order'])) as $col) {
				$parts=explode(' ',$col,2);
				$order=empty($parts[1])?SORT_ASC:constant($parts[1]);
				uasort(
					$data,
					function($val1,$val2) use($col,$order) {
						list($v1,$v2)=array($val1[$col],$val2[$col]);
						$out=is_numeric($v1) && is_numeric($v2)?
							Base::instance()->sign($v1-$v2):strcmp($v1,$v2);
						if ($order==SORT_DESC)
							$out=-$out;
						return $out;
					}
				);
			}
		$out=array();
		foreach (array_slice($data,
			$options['offset'],$options['limit']?:NULL,TRUE) as $id=>$doc)
			$out[]=$this->factory($id,$doc);
		if ($log) {
			if ($filter)
				foreach ($_args as $key=>$val) {
					$vals[]=$fw->stringify(is_array($val)?$val[0]:$val);
					$keys[]='/'.(is_numeric($key)?'\?':preg_quote($key)).'/';
				}
			$db->jot('('.sprintf('%.1f',1e3*(microtime(TRUE)-$now)).'ms) '.
				$this->file.' [find] '.
				($filter?preg_replace($keys,$vals,$filter[0],1):''));
		}
		return $out;
	}

	/**
		Count records that match criteria
		@return int
		@param $filter array
	**/
	function count($filter=NULL) {
		$now=microtime(TRUE);
		$out=count($this->find($filter,NULL,FALSE));
		$this->db->jot('('.sprintf('%.1f',1e3*(microtime(TRUE)-$now)).'ms) '.
			$this->file.' [count] '.($filter?json_encode($filter):''));
		return $out;
	}

	/**
		Return record at specified offset using criteria of previous
		load() call and make it active
		@return array
		@param $ofs int
	**/
	function skip($ofs=1) {
		$this->document=($out=parent::skip($ofs))?$out->document:array();
		$this->id=$out?$out->id:NULL;
		return $out;
	}

	/**
		Insert new record
		@return array
	**/
	function insert() {
		$db=$this->db;
		$now=microtime(TRUE);
		while (($id=uniqid()) &&
			($data=$db->read($this->file)) && isset($data[$id]) &&
			!connection_aborted())
			usleep(mt_rand(0,100));
		$this->id=$id;
		$data[$id]=$this->document;
		$db->write($this->file,$data);
		parent::reset();
		$db->jot('('.sprintf('%.1f',1e3*(microtime(TRUE)-$now)).'ms) '.
			$this->file.' [insert] '.
			json_encode(array('_id'=>$this->id)+$this->document));
		return $this->document;
	}

	/**
		Update current record
		@return array
	**/
	function update() {
		$db=$this->db;
		$now=microtime(TRUE);
		$data=$db->read($this->file);
		$data[$this->id]=$this->document;
		$db->write($this->file,$data);
		$db->jot('('.sprintf('%.1f',1e3*(microtime(TRUE)-$now)).'ms) '.
			$this->file.' [update] '.
			json_encode(array('_id'=>$this->id)+$this->document));
		return $this->document;
	}

	/**
		Delete current record
		@return bool
		@param $filter array
	**/
	function erase($filter=NULL) {
		$db=$this->db;
		$now=microtime(TRUE);
		$data=$db->read($this->file);
		if ($filter) {
			$data=$this->find($filter,NULL,FALSE);
			foreach (array_keys(array_reverse($data)) as $id)
				unset($data[$id]);
		}
		elseif (isset($this->id)) {
			unset($data[$this->id]);
			parent::erase();
			$this->skip(0);
		}
		else
			return FALSE;
		$db->write($this->file,$data);
		if ($filter) {
			$_args=isset($filter[1]) && is_array($filter[1])?
				$filter[1]:
				array_slice($filter,1,NULL,TRUE);
			$_args=is_array($_args)?$_args:array(1=>$_args);
			foreach ($_args as $key=>$val) {
				$vals[]=\Base::instance()->
					stringify(is_array($val)?$val[0]:$val);
				$keys[]='/'.(is_numeric($key)?'\?':preg_quote($key)).'/';
			}
		}
		$db->jot('('.sprintf('%.1f',1e3*(microtime(TRUE)-$now)).'ms) '.
			$this->file.' [erase] '.
			($filter?preg_replace($keys,$vals,$filter[0],1):''));
		return TRUE;
	}

	/**
		Reset cursor
		@return NULL
	**/
	function reset() {
		$this->id=NULL;
		$this->document=array();
		parent::reset();
	}

	/**
		Hydrate mapper object using hive array variable
		@return NULL
		@param $key string
	**/
	function copyfrom($key) {
		foreach (\Base::instance()->get($key) as $key=>$val)
			$this->document[$key]=$val;
	}

	/**
		Populate hive array variable with mapper fields
		@return NULL
		@param $key string
	**/
	function copyto($key) {
		$var=&\Base::instance()->ref($key);
		foreach ($this->document as $key=>$field)
			$var[$key]=$field;
	}

	/**
		Instantiate class
		@return void
		@param $db object
		@param $file string
	**/
	function __construct(\DB\Jig $db,$file) {
		$this->db=$db;
		$this->file=$file;
		$this->reset();
	}

}
