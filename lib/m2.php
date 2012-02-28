<?php

/**
	MongoDB Mapper for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package M2
		@version 2.0.9
**/

//! MongoDB Mapper
class M2 extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_M2Connect='Undefined database',
		TEXT_M2Empty='M2 is empty',
		TEXT_M2Collection='Collection %s does not exist';
	//@}

	//@{
	//! M2 properties
	private
		$db,$collection,$object,$cond,$seq,$ofs;
	//@}

	/**
		M2 factory
			@return object
			@param $doc array
			@public
	**/
	function factory($doc) {
		$self=get_class($this);
		$m2=new $self($this->collection,$this->db);
		foreach ($doc as $key=>$val)
			$m2->object[$key]=is_array($val)?
				json_decode(json_encode($val)):$val;
		return $m2;
	}

	/**
		Retrieve from cache; or save query results to cache if not
		previously executed
			@param $query array
			@param $ttl int
			@private
	**/
	private function cache(array $query,$ttl) {
		$cmd=json_encode($query,TRUE);
		$hash='mdb.'.self::hash($cmd);
		$cached=Cache::cached($hash);
		$db=(string)$this->db;
		$stats=&self::ref('STATS');
		if ($ttl && $cached && $_SERVER['REQUEST_TIME']-$cached<$ttl) {
			// Gather cached queries for profiler
			if (!isset($stats[$db]['cache'][$cmd]))
				$stats[$db]['cache'][$cmd]=0;
			$stats[$db]['cache'][$cmd]++;
			// Retrieve from cache
			return Cache::get($hash);
		}
		else {
			$result=$this->exec($query);
			if ($ttl)
				Cache::set($hash,$result,$ttl);
			// Gather real queries for profiler
			if (!isset($stats[$db]['queries'][$cmd]))
				$stats[$db]['queries'][$cmd]=0;
			$stats[$db]['queries'][$cmd]++;
			return $result;
		}
	}

	/**
		Execute MongoDB query
			@return mixed
			@param $query array
			@private
	**/
	private function exec(array $query) {
		$cmd=json_encode($query,TRUE);
		$hash='mdb.'.self::hash($cmd);
		// Except for save method, collection must exist
		$list=$this->db->listCollections();
		foreach ($list as &$coll)
			$coll=$coll->getName();
		if ($query['method']!='save' && !in_array($this->collection,$list)) {
			trigger_error(sprintf(self::TEXT_M2Collection,$this->collection));
			return;
		}
		if (isset($query['map'])) {
			// Create temporary collection
			$ref=$this->db->selectCollection($hash);
			$ref->batchInsert(iterator_to_array($out,FALSE));
			$map=$query['map'];
			$func='function() {}';
			// Map-reduce
			$tmp=$this->db->command(
				array(
					'mapreduce'=>$ref->getName(),
					'map'=>isset($map['map'])?
						$map['map']:$func,
					'reduce'=>isset($map['reduce'])?
						$map['reduce']:$func,
					'finalize'=>isset($map['finalize'])?
						$map['finalize']:$func
				)
			);
			if (!$tmp['ok']) {
				trigger_error($tmp['errmsg']);
				return FALSE;
			}
			$ref->remove();
			// Aggregate the result
			foreach (iterator_to_array($this->db->
				selectCollection($tmp['result'])->find(),FALSE) as $agg)
				$ref->insert($agg['_id']);
			$out=$ref->find();
			$ref->drop();
		}
		elseif (preg_match('/find/',$query['method'])) {
			// find and findOne methods allow selection of fields
			$out=call_user_func(
				array(
					$this->db->selectCollection($this->collection),
					$query['method']
				),
				isset($query['cond'])?$query['cond']:array(),
				isset($query['fields'])?$query['fields']:array()
			);
			if ($query['method']=='find') {
				if (isset($query['seq']))
					// Sort results
					$out=$out->sort($query['seq']);
				if (isset($query['ofs']))
					// Skip to record ofs
					$out=$out->skip($query['ofs']);
				if (isset($query['limit']) && $query['limit'])
					// Limit number of results
					$out=$out->limit($query['limit']);
				// Convert cursor to PHP array
				$out=iterator_to_array($out,FALSE);
				if ($query['m2'])
					foreach ($out as &$obj)
						$obj=$this->factory($obj);
			}
		}
		else
			$out=preg_match('/count|remove/',$query['method'])?
				// count() and remove() methods can specify cond
				call_user_func(
					array(
						$this->db->selectCollection($this->collection),
						$query['method']
					),
					isset($query['cond'])?$query['cond']:array()
				):
				// All other queries
				call_user_func(
					array(
						$this->db->selectCollection($this->collection),
						$query['method']
					),
					$this->object
				);
		return $out;
	}

	/**
		Return current object contents as an array
			@return array
			@public
	**/
	function cast() {
		return $this->object;
	}

	/**
		Similar to M2->find method but provides more fine-grained control
		over specific fields and map-reduced results
			@return array
			@param $fields array
			@param $cond mixed
			@param $map mixed
			@param $seq mixed
			@param $limit mixed
			@param $ofs mixed
			@param $ttl int
			@param $m2 bool
			@public
	**/
	function lookup(
		array $fields,
		$cond=NULL,
		$map=NULL,
		$seq=NULL,
		$limit=0,
		$ofs=0,
		$ttl=0,
		$m2=TRUE) {
		$query=array(
			'method'=>'find',
			'fields'=>$fields,
			'cond'=>$cond,
			'map'=>$map,
			'seq'=>$seq,
			'limit'=>$limit,
			'ofs'=>$ofs,
			'm2'=>$m2
		);
		return $ttl?$this->cache($query,$ttl):$this->exec($query);
	}

	/**
		Alias of the lookup method
			@public
	**/
	function select() {
		// PHP doesn't allow direct use as function argument
		$args=func_get_args();
		return call_user_func_array(array($this,'lookup'),$args);
	}

	/**
		Return an array of collection objects matching cond
			@return array
			@param $cond mixed
			@param $seq mixed
			@param $limit mixed
			@param $ofs mixed
			@param $ttl int
			@param $m2 bool
			@public
	**/
	function find($cond=NULL,$seq=NULL,$limit=NULL,$ofs=0,$ttl=0,$m2=TRUE) {
		$query=array(
			'method'=>'find',
			'cond'=>$cond,
			'seq'=>$seq,
			'limit'=>$limit,
			'ofs'=>$ofs,
			'm2'=>$m2
		);
		return $ttl?$this->cache($query,$ttl):$this->exec($query);
	}

	/**
		Return an array of associative arrays matching cond
			@return array
			@param $cond mixed
			@param $seq mixed
			@param $limit mixed
			@param $ofs int
			@param $ttl int
			@public
	**/
	function afind($cond=NULL,$seq=NULL,$limit=NULL,$ofs=0,$ttl=0) {
		return $this->find($cond,$seq,$limit,$ofs,$ttl,FALSE);
	}

	/**
		Return the first object that matches the specified cond
			@return array
			@param $cond mixed
			@param $seq mixed
			@param $ofs int
			@param $ttl int
			@public
	**/
	function findone($cond=NULL,$seq=NULL,$ofs=0,$ttl=0) {
		list($result)=$this->find($cond,$seq,1,$ofs,$ttl)?:array(NULL);
		return $result;
	}

	/**
		Return the array equivalent of the object matching criteria
			@return array
			@param $cond mixed
			@param $seq mixed
			@param $ofs int
			@public
	**/
	function afindone($cond=NULL,$seq=NULL,$ofs=0) {
		list($result)=$this->afind($cond,$seq,1,$ofs)?:array(NULL);
		return $result;
	}

	/**
		Count objects that match condition
			@return int
			@param $cond mixed
			@public
	**/
	function found($cond=NULL) {
		$result=$this->exec(
			array(
				'method'=>'count',
				'cond'=>$cond
			)
		);
		return $result;
	}

	/**
		Hydrate M2 with elements from framework array variable, keys of
		which will be identical to field names in collection object
			@param $name string
			@public
	**/
	function copyFrom($name) {
		if (is_array($ref=self::ref($name)))
			foreach ($ref as $key=>$val)
				$this->object[$key]=$val;
	}

	/**
		Populate framework array variable with M2 properties, keys of
		which will have names identical to fields in collection object
			@param $name string
			@param $fields string
			@public
	**/
	function copyTo($name,$fields=NULL) {
		if ($this->dry()) {
			trigger_error(self::TEXT_M2Empty);
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
		Dehydrate M2
			@public
	**/
	function reset() {
		// Dehydrate
		$this->object=NULL;
		$this->cond=NULL;
		$this->seq=NULL;
		$this->ofs=NULL;
	}

	/**
		Retrieve first collection object that satisfies cond
			@return mixed
			@param $cond mixed
			@param $seq mixed
			@param $ofs int
			@public
	**/
	function load($cond=NULL,$seq=NULL,$ofs=0) {
		if ($ofs>-1) {
			$this->ofs=0;
			if ($m2=$this->findOne($cond,$seq,$ofs)) {
				if (method_exists($this,'beforeLoad') &&
					$this->beforeLoad()===FALSE)
					return;
				// Hydrate M2
				foreach ($m2->object as $key=>$val)
					$this->object[$key]=$val;
				list($this->cond,$this->seq,$this->ofs)=
					array($cond,$seq,$ofs);
				if (method_exists($this,'afterLoad'))
					$this->afterLoad();
				return $this;
			}
		}
		$this->reset();
		return FALSE;
	}

	/**
		Retrieve N-th object relative to current using the same cond
		that hydrated M2
			@return mixed
			@param $count int
			@public
	**/
	function skip($count=1) {
		if ($this->dry()) {
			trigger_error(self::TEXT_M2Empty);
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
		Insert/update collection object
			@public
	**/
	function save() {
		if ($this->dry() ||
			method_exists($this,'beforeSave') &&
			$this->beforeSave()===FALSE)
			return;
		// Let the MongoDB driver decide how to persist the
		// collection object in the database
		$obj=$this->object;
		$this->exec(array('method'=>'save'));
		if (!isset($obj['_id']))
			// Reload to retrieve MongoID of inserted object
			$this->object=
				$this->exec(array('method'=>'findOne','cond'=>$obj));
		if (method_exists($this,'afterSave'))
			$this->afterSave();
	}

	/**
		Delete collection object and reset M2
			@public
	**/
	function erase() {
		if (method_exists($this,'beforeErase') &&
			$this->beforeErase()===FALSE)
			return;
		$this->exec(array('method'=>'remove','cond'=>$this->cond));
		$this->reset();
		if (method_exists($this,'afterErase'))
			$this->afterErase();
	}

	/**
		Return TRUE if M2 is NULL
			@return bool
			@public
	**/
	function dry() {
		return is_null($this->object);
	}

	/**
		Synchronize M2 and MongoDB collection
			@param $coll string
			@param $db object
			@public
	**/
	function sync($coll,$db=NULL) {
		if (!extension_loaded('mongo')) {
			// MongoDB extension not activated
			trigger_error(sprintf(self::TEXT_PHPExt,'mongo'));
			return;
		}
		if (!$db) {
			if (isset(self::$vars['DB']) && is_a(self::$vars['DB'],'MongoDB'))
				$db=self::$vars['DB'];
			else {
				trigger_error(self::TEXT_M2Connect);
				return;
			}
		}
		if (method_exists($this,'beforeSync') &&
			$this->beforeSync()===FALSE)
			return;
		// Initialize M2
		list($this->db,$this->collection)=array($db,$coll);
		if (method_exists($this,'afterSync'))
			$this->afterSync();
	}

	/**
		Return value of M2-mapped field
			@return bool
			@param $name string
			@public
	**/
	function __get($name) {
		return $this->object[$name];
	}

	/**
		Assign value to M2-mapped field
			@return bool
			@param $name string
			@param $val mixed
			@public
	**/
	function __set($name,$val) {
		$this->object[$name]=$val;
	}

	/**
		Clear value of M2-mapped field
			@return bool
			@param $name string
			@public
	**/
	function __unset($name) {
		unset($this->object[$name]);
	}

	/**
		Return TRUE if M2-mapped field exists
			@return bool
			@param $name string
			@public
	**/
	function __isset($name) {
		return array_key_exists($name,$this->object);
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
