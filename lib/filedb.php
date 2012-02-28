<?php

/**
	Simple Flat-file ORM for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package FileDB
		@version 2.0.9
**/

//! Flat-file data access layer
class FileDB extends Base {

	//@{ Storage formats
	const
		FORMAT_Plain=0,
		FORMAT_Serialized=1,
		FORMAT_JSON=2,
		FORMAT_GZip=3;
	//@}

	//@{ Locale-specific error/exception messages
	const
		TEXT_Criteria='Invalid criteria: %s';
	//@}

	public
		//! Exposed properties
		$path,$result;
	private
		//! Storage settings
		$format,
		//! Journal identifier
		$journal;

	/**
		Begin transaction
			@public
	**/
	function begin() {
		$this->journal=base_convert(microtime(TRUE)*100,10,16);
	}

	/**
		Rollback transaction
			@public
	**/
	function rollback() {
		if ($glob=glob($this->path.'*.jnl.'.$this->journal))
			foreach ($glob as $temp) {
				self::mutex(
					function() use($temp) {
						unlink($temp);
					},
					$temp
				);
				break;
			}
		$this->journal=NULL;
	}

	/**
		Commit transaction
			@public
	**/
	function commit() {
		if ($glob=glob($this->path.'*.jnl.'.$this->journal))
			foreach ($glob as $temp) {
				$file=preg_replace('/\.jnl\.'.$this->journal.'$/','',$temp);
				self::mutex(
					function() use($temp,$file) {
						@rename($temp,$file);
					},
					$temp,$file
				);
				break;
			}
		$this->journal=NULL;
	}

	/**
		Retrieve contents of flat-file
			@return mixed
			@param $file string
			@public
	**/
	function read($file) {
		$file=$this->path.$file;
		if (!is_file($file))
			return array();
		$text=self::getfile($file);
		switch ($this->format) {
			case self::FORMAT_GZip:
				$text=gzinflate($text);
			case self::FORMAT_Plain:
				if (ini_get('allow_url_fopen') &&
					ini_get('allow_url_include'))
					// Stream wrap
					$file='data:text/plain,'.urlencode($text);
				else {
					$file=self::$vars['TEMP'].$_SERVER['SERVER_NAME'].'.'.
						'php.'.self::hash($file);
					self::putfile($file,$text);
				}
				$instance=new F3instance;
				$out=$instance->sandbox($file);
				break;
			case self::FORMAT_Serialized:
				$out=unserialize($text);
				break;
			case self::FORMAT_JSON:
				$out=json_decode($text,TRUE);
		}
		return $out;
	}

	/**
		Store PHP expression in flat-file
			@param $file string
			@param $expr mixed
			@public
	**/
	function write($file,$expr) {
		$file=$this->path.$file;
		$auto=FALSE;
		if (!$this->journal) {
			$auto=TRUE;
			$this->begin();
			if (is_file($file))
				copy($file,$file.'.jnl.'.$this->journal);
		}
		$file.='.jnl.'.$this->journal;
		if (!$expr)
			$expr=array();
		$out='<?php'."\n\n".'return '.self::stringify($expr).';'."\n";
		switch ($this->format) {
			case self::FORMAT_GZip:
				$out=gzdeflate($out);
				break;
			case self::FORMAT_Serialized:
				$out=serialize($expr);
				break;
			case self::FORMAT_JSON:
				$out=json_encode($expr);
		}
		if (self::putfile($file,$out)===FALSE)
			$this->rollback();
		elseif ($auto)
			$this->commit();
	}

	/**
		Convert database to another format
			@return bool
			@param $fmt int
			@public
	**/
	function convert($fmt) {
		$glob=glob($this->path.'*');
		if ($glob) {
			foreach ($glob as $file) {
				$file=str_replace($this->path,'',$file);
				$out=$this->read($file);
				switch ($fmt) {
					case self::FORMAT_GZip:
						$out=gzdeflate($out);
						break;
					case self::FORMAT_Serialized:
						$out=serialize($out);
						break;
					case self::FORMAT_JSON;
						$out=json_encode($out);
				}
				$this->format=$fmt;
				$this->write($file,$out);
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
		Custom session handler
			@param $table string
			@public
	**/
	function session($table='sessions') {
		session_set_save_handler(
			// Open
			function($path,$name) {
				register_shutdown_function('session_commit');
				return TRUE;
			},
			// Close
			function() {
				return TRUE;
			},
			// Read
			function($id) use($table) {
				$jig=new Jig($table);
				$jig->load(array('id'=>$id));
				return $jig->dry()?FALSE:$jig->data;
			},
			// Write
			function($id,$data) use($table) {
				$jig=new Jig($table);
				$jig->load(array('id'=>$id));
				$jig->id=$id;
				$jig->data=$data;
				$jig->stamp=time();
				$jig->save();
				return TRUE;
			},
			// Delete
			function($id) use($table) {
				$jig=new Jig($table);
				$jig->erase(array('id'=>$id));
				return TRUE;
			},
			// Cleanup
			function($max) use($table) {
				$jig=new Jig($table);
				$jig->erase(
					array(
						'_PHP_'=>
							array(
								'stamp'=>
									function($stamp) use($max) {
										return $stamp+$max<time();
									}
							)
					)
				);
				return TRUE;
			}
		);
	}

	/**
		Class constructor
			@param $path string
			@param $fmt int
			@public
	**/
	function __construct($path=NULL,$fmt=self::FORMAT_Plain) {
		$path=self::fixslashes(realpath(self::resolve($path)).'/');
		if (!is_dir($path))
			self::mkdir($path);
		list($this->path,$this->format)=array($path,$fmt);
		if (!isset(self::$vars['DB']))
			self::$vars['DB']=$this;
	}

}

//! Flat-file ORM
class Jig extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_JigConnect='Undefined database',
		TEXT_JigEmpty='Jig is empty',
		TEXT_JigTable='Table %s does not exist',
		TEXT_JigField='Field %s does not exist';
	//@}

	//@{
	//! Jig properties
	public
		$_id;
	private
		$db,$table,$object,$mod,$cond,$seq,$ofs;
	//@}

	/**
		Jig factory
			@return object
			@param $obj array
			@public
	**/
	function factory($obj) {
		$self=get_class($this);
		$jig=new $self($this->table,$this->db);
		$jig->_id=$obj['_id'];
		unset($obj['_id']);
		foreach ($obj as $key=>$val)
			$jig->object[$key]=$val;
		return $jig;
	}

	/**
		Evaluate query criteria
			@return boolean
			@param $expr array
			@param $obj array
			@private
	**/
	private function check(array $expr,array $obj) {
		if (is_null($expr))
			return TRUE;
		if (is_array($expr)) {
			$result=TRUE;
			foreach ($expr as $field=>$cond) {
				if ($field=='_OR_') {
					if (!is_array($cond)) {
						trigger_error(
							sprintf(
								self::TEXT_Criteria,
								$this->stringify($cond)
							)
						);
						return FALSE;
					}
					foreach ($cond as $val)
						// Short circuit
						if ($this->check($val,$obj))
							return TRUE;
					return FALSE;
				}
				elseif ($field=='_PHP_') {
					list($key,$val)=array(key($cond),current($cond));
					if (!is_array($cond) || !is_callable($val)) {
						trigger_error(
							sprintf(
								self::TEXT_Callback,
								$this->stringify($val)
							)
						);
						return FALSE;
					}
					return isset($obj[$key])?
						call_user_func($val,$obj[$key]):TRUE;
				}
				elseif (!isset($obj[$field]))
					$result=FALSE;
				elseif (is_array($cond)) {
					$map=array(
						'='=>'==',
						'eq'=>'==',
						'gt'=>'>',
						'lt'=>'<',
						'gte'=>'>=',
						'lte'=>'<=',
						'<>'=>'!=',
						'ne'=>'!='
					);
					foreach ($cond as $op=>$val)
						$result=($op=='_OR_' || $op=='_PHP_')?
							$this->check($val,$obj):
							($op=='regex'?
								preg_match('/'.$val.'/s',$obj[$field]):
								eval(
									'return $obj[$field]'.
									(isset($map[$op])?$map[$op]:$op).
									'(is_string($val)?'.
										'self::resolve($val):$val);'));
				}
				else
					$result=($obj[$field]==(is_string($cond)?
						self::resolve($cond):$cond));
				if (!$result)
					break;
			}
			return $result;
		}
		return (bool)$expr;
	}

	/**
		Return current object contents as an array
			@return array
			@public
	**/
	function cast() {
		return array_merge(array('_id'=>$this->_id),$this->object);
	}

	/**
		Return an array of objects matching criteria
			@return array
			@param $cond array
			@param $seq array
			@param $limit mixed
			@param $ofs int
			@param $jig boolean
			@public
	**/
	function find(
		array $cond=NULL,array $seq=NULL,$limit=0,$ofs=0,$jig=TRUE) {
		$table=$this->db->read($this->table);
		$result=array();
		if ($table) {
			if (is_array($seq))
				foreach (array_reverse($seq,TRUE) as $key=>$sort)
					Matrix::sort($table,$key,$sort);
			foreach ($table as $key=>$obj) {
				$obj['_id']=$key;
				if (is_null($cond) || $this->check($cond,$obj))
					$result[]=$jig?$this->factory($obj):$obj;
			}
			$result=array_slice($result,$ofs,$limit?:NULL);
		}
		$this->db->result=$result;
		return $result;
	}

	/**
		Return an array of associative arrays matching criteria
			@return array
			@param $cond array
			@param $seq array
			@param $limit mixed
			@param $ofs int
			@public
	**/
	function afind(array $cond=NULL,array $seq=NULL,$limit=0,$ofs=0) {
		return $this->find($cond,$seq,$limit,$ofs,FALSE);
	}

	/**
		Return the first object that matches the specified criteria
			@return array
			@param $cond array
			@param $seq array
			@param $ofs int
			@public
	**/
	function findone(array $cond=NULL,array $seq=NULL,$ofs=0) {
		list($result)=$this->find($cond,$seq,1,$ofs)?:array(NULL);
		return $result;
	}

	/**
		Return the array equivalent of the object matching criteria
			@return array
			@param $cond array
			@param $seq array
			@param $ofs int
			@public
	**/
	function afindone(array $cond=NULL,array $seq=NULL,$ofs=0) {
		list($result)=$this->afind($cond,$seq,1,$ofs)?:array(NULL);
		return $result;
	}

	/**
		Count objects that match condition
			@return int
			@param $cond array
			@public
	**/
	function found(array $cond=NULL) {
		return count($this->find($cond));
	}

	/**
		Hydrate Jig with elements from framework array variable, keys of
		which will be identical to object properties
			@param $name string
			@public
	**/
	function copyFrom($name) {
		if (is_array($ref=self::ref($name))) {
			foreach ($ref as $key=>$val)
				$this->object[$key]=$val;
			$this->mod=TRUE;
		}
	}

	/**
		Populate framework array variable with Jig properties, keys of
		which will have names identical to object properties
			@param $name string
			@param $fields string
			@public
	**/
	function copyTo($name,$fields=NULL) {
		if ($this->dry()) {
			trigger_error(self::TEXT_JigEmpty);
			return FALSE;
		}
		if (is_string($fields))
			$list=preg_split('/[\|;,]/',$fields,0,PREG_SPLIT_NO_EMPTY);
		foreach (array_keys($this->object) as $field)
			if (!isset($list) || in_array($field,$list)) {
				$var=&self::ref($name);
				$var[$field]=$this->object[$field];
			}
	}

	/**
		Dehydrate Jig
			@public
	**/
	function reset() {
		// Dehydrate
		$this->_id=NULL;
		$this->object=NULL;
		$this->mod=NULL;
		$this->cond=NULL;
		$this->seq=NULL;
		$this->ofs=0;
	}

	/**
		Retrieve first object that satisfies criteria
			@return mixed
			@param $cond array
			@param $seq array
			@param $ofs int
			@public
	**/
	function load(array $cond=NULL,array $seq=NULL,$ofs=0) {
		if ($ofs>-1) {
			$this->ofs=0;
			if ($jig=$this->findone($cond,$seq,$ofs)) {
				if (method_exists($this,'beforeLoad') &&
					$this->beforeLoad()===FALSE)
					return;
				// Hydrate Jig
				$this->_id=$jig->_id;
				foreach ($jig->object as $key=>$val)
					$this->object[$key]=$val;
				list($this->cond,$this->seq,$this->ofs)=
					array($cond,$seq,$ofs);
				if (method_exists($this,'afterLoad'))
					$this->afterLoad();
				$this->mod=NULL;
				return $this;
			}
		}
		$this->reset();
		return FALSE;
	}

	/**
		Retrieve N-th object relative to current using the same criteria
		that hydrated Jig
			@return mixed
			@param $count int
			@public
	**/
	function skip($count=1) {
		if ($this->dry()) {
			trigger_error(self::TEXT_JigEmpty);
			return FALSE;
		}
		return $this->load($this->cond,$this->seq,$this->ofs+$count);
	}

	/**
		Return next record
			@return array
			@public
	**/
	function next() {
		return $this->skip();
	}

	/**
		Return previous record
			@return array
			@public
	**/
	function prev() {
		return $this->skip(-1);
	}

	/**
		Insert/update object
			@public
	**/
	function save() {
		if ($this->dry() ||
			method_exists($this,'beforeSave') &&
			$this->beforeSave()===FALSE)
			return;
		if ($this->mod) {
			// Object modified
			$table=$this->db->read($this->table);
			$obj=$this->object;
			if (!is_null($this->_id))
				// Update
				$id=$this->_id;
			else {
				// Insert with concurrency control
				while (($id=base_convert(microtime(TRUE)*100,10,16)) &&
					isset($table[$id])) {
					usleep(mt_rand(0,100));
					// Reload table
					$table=$this->db->read($this->table);
				}
				$this->_id=$id;
			}
			$table[$id]=$obj;
			// Save to file
			$this->db->write($this->table,$table);
		}
		if (method_exists($this,'afterSave'))
			$this->afterSave();
	}

	/**
		Delete object/s and reset Jig
			@param $cond array
			@param $force boolean
			@public
	**/
	function erase(array $cond=NULL,$force=FALSE) {
		if (method_exists($this,'beforeErase') &&
			$this->beforeErase()===FALSE)
			return;
		if (!$cond)
			$cond=$this->cond;
		if ($force || $cond) {
			$table=$this->db->read($this->table);
			foreach ($this->find($cond) as $found)
				unset($table[$found->_id]);
			// Save to file
			$this->db->write($this->table,$table);
		}
		$this->reset();
		if (method_exists($this,'afterErase'))
			$this->afterErase();
	}

	/**
		Return TRUE if Jig is NULL
			@return boolean
			@public
	**/
	function dry() {
		return is_null($this->object);
	}

	/**
		Synchronize Jig and underlying file
			@param $table string
			@param $db object
			@public
	**/
	function sync($table,$db=NULL) {
		if (!$db) {
			if (isset(self::$vars['DB']) &&
				is_a(self::$vars['DB'],'FileDB'))
				$db=self::$vars['DB'];
			else {
				trigger_error(self::TEXT_JigConnect);
				return;
			}
		}
		if (method_exists($this,'beforeSync') &&
			$this->beforeSync()===FALSE)
			return;
		// Initialize Jig
		list($this->db,$this->table)=array($db,$table);
		if (method_exists($this,'afterSync'))
			$this->afterSync();
	}

	/**
		Return value of Jig-mapped property
			@return boolean
			@param $name string
			@public
	**/
	function &__get($name) {
		return $this->object[$name];
	}

	/**
		Assign value to Jig-mapped property
			@return boolean
			@param $name string
			@param $val mixed
			@public
	**/
	function __set($name,$val) {
		if (!isset($this->object[$name]) || $this->object[$name]!=$val)
			$this->mod=TRUE;
		$this->object[$name]=$val;
	}

	/**
		Clear value of Jig-mapped property
			@return boolean
			@param $name string
			@public
	**/
	function __unset($name) {
		unset($this->object[$name]);
		$this->mod=TRUE;
	}

	/**
		Return TRUE if Jig-mapped property exists
			@return boolean
			@param $name string
			@public
	**/
	function __isset($name) {
		return array_key_exists($name,$this->object);
	}

	/**
		Display class name if conversion to string is attempted
			@public
	**/
	function __toString() {
		return get_class($this);
	}

	/**
		Class constructor
			@public
	**/
	function __construct() {
		// Execute mandatory sync method
		call_user_func_array(array($this,'sync'),func_get_args());
	}

}
