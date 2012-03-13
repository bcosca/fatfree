<?php

/**
	SQL database plugin for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package DB
		@version 2.0.9
**/

//! SQL data access layer
class DB extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_ExecFail='Unable to execute prepared statement: %s',
		TEXT_DBEngine='Database engine is not supported',
		TEXT_Schema='Schema for %s table is not available';
	//@}

	public
		//! Exposed data object properties
		$dbname,$backend,$pdo,$result;
	private
		//! Connection parameters
		$dsn,$user,$pw,$opt,
		//! Transaction tracker
		$trans=FALSE,
		//! Auto-commit mode
		$auto=TRUE,
		//! Number of rows affected by query
		$rows=0;

	/**
		Force PDO instantiation
			@public
	**/
	function instantiate() {
		$this->pdo=new PDO($this->dsn,$this->user,$this->pw,$this->opt);
	}

	/**
		Begin SQL transaction
			@param $auto boolean
			@public
	**/
	function begin($auto=FALSE) {
		if (!$this->pdo)
			self::instantiate();
		$this->pdo->beginTransaction();
		$this->trans=TRUE;
		$this->auto=$auto;
	}

	/**
		Rollback SQL transaction
			@public
	**/
	function rollback() {
		if (!$this->pdo)
			self::instantiate();
		$this->pdo->rollback();
		$this->trans=FALSE;
		$this->auto=TRUE;
	}

	/**
		Commit SQL transaction
			@public
	**/
	function commit() {
		if (!$this->pdo)
			self::instantiate();
		$this->pdo->commit();
		$this->trans=FALSE;
		$this->auto=TRUE;
	}

	/**
		Process SQL statement(s)
			@return array
			@param $cmds mixed
			@param $args array
			@param $ttl int
			@public
	**/
	function exec($cmds,array $args=NULL,$ttl=0) {
		if (!$this->pdo)
			self::instantiate();
		$stats=&self::ref('STATS');
		if (!isset($stats[$this->dsn]))
			$stats[$this->dsn]=array(
				'cache'=>array(),
				'queries'=>array()
			);
		$batch=is_array($cmds);
		if ($batch) {
			if (!$this->trans && $this->auto)
				$this->begin(TRUE);
			if (is_null($args)) {
				$args=array();
				for ($i=0;$i<count($cmds);$i++)
					$args[]=NULL;
			}
		}
		else {
			$cmds=array($cmds);
			$args=array($args);
		}
		for ($i=0,$len=count($cmds);$i<$len;$i++) {
			list($cmd,$arg)=array($cmds[$i],$args[$i]);
			$hash='sql.'.self::hash($cmd.var_export($arg,TRUE));
			$cached=Cache::cached($hash);
			if ($ttl && $cached && $_SERVER['REQUEST_TIME']-$cached<$ttl) {
				// Gather cached queries for profiler
				if (!isset($stats[$this->dsn]['cache'][$cmd]))
					$stats[$this->dsn]['cache'][$cmd]=0;
				$stats[$this->dsn]['cache'][$cmd]++;
				$this->result=Cache::get($hash);
			}
			else {
				if (is_null($arg))
					$query=$this->pdo->query($cmd);
				else {
					$query=$this->pdo->prepare($cmd);
					if (is_object($query)) {
						foreach ($arg as $key=>$value)
							if (!(is_array($value)?
								$query->bindvalue($key,$value[0],$value[1]):
								$query->bindvalue($key,$value,
									$this->type($value))))
								break;
						$query->execute();
					}
				}
				// Check SQLSTATE
				foreach (array($this->pdo,$query) as $obj)
					if ($obj->errorCode()!=PDO::ERR_NONE) {
						if ($this->trans && $this->auto)
							$this->rollback();
						$error=$obj->errorinfo();
						trigger_error($error[2]);
						return FALSE;
					}
				if (preg_match(
					'/^\s*(?:SELECT|PRAGMA|SHOW|EXPLAIN)\s/i',$cmd)) {
					$this->result=$query->fetchall(PDO::FETCH_ASSOC);
					$this->rows=$query->rowcount();
				}
				else
					$this->rows=$this->result=$query->rowCount();
				if ($ttl)
					Cache::set($hash,$this->result,$ttl);
				// Gather real queries for profiler
				if (!isset($stats[$this->dsn]['queries'][$cmd]))
					$stats[$this->dsn]['queries'][$cmd]=0;
				$stats[$this->dsn]['queries'][$cmd]++;
			}
		}
		if ($batch || $this->trans && $this->auto)
			$this->commit();
		return $this->result;
	}

	/**
		Return number of rows affected by latest query
			@return int
	**/
	function rows() {
		return $this->rows;
	}

	/**
		Return auto-detected PDO data type of specified value
			@return int
			@param $val mixed
			@public
	**/
	function type($val) {
		foreach (
			array(
				'null'=>'NULL',
				'bool'=>'BOOL',
				'string'=>'STR',
				'int'=>'INT',
				'float'=>'STR'
			) as $php=>$pdo)
			if (call_user_func('is_'.$php,$val))
				return constant('PDO::PARAM_'.$pdo);
		return PDO::PARAM_LOB;
	}

	/**
		Convenience method for direct SQL queries (static call)
			@return array
			@param $cmds mixed
			@param $args mixed
			@param $ttl int
			@param $db string
			@public
	**/
	static function sql($cmds,array $args=NULL,$ttl=0,$db='DB') {
		return self::$vars[$db]->exec($cmds,$args,$ttl);
	}

	/**
		Return schema of specified table
			@return array
			@param $table string
			@param $ttl int
			@public
	**/
	function schema($table,$ttl) {
		// Support these engines
		$cmd=array(
			'sqlite2?'=>array(
				'PRAGMA table_info('.$table.');',
				'name','pk',1,'type'),
			'mysql'=>array(
				'SHOW columns FROM `'.$this->dbname.'`.'.$table.';',
				'Field','Key','PRI','Type'),
			'mssql|sybase|dblib|pgsql|ibm|odbc'=>array(
				'SELECT c.column_name AS field,'.
				'c.data_type AS type,t.constraint_type AS pkey '.
				'FROM information_schema.columns AS c '.
				'LEFT OUTER JOIN '.
					'information_schema.key_column_usage AS k ON '.
						'c.table_name=k.table_name AND '.
						'c.column_name=k.column_name '.
						($this->dbname?
							('AND '.
							(preg_match('/^pgsql$/',$this->backend)?
								'c.table_catalog=k.table_catalog':
								'c.table_schema=k.table_schema').' '):'').
				'LEFT OUTER JOIN '.
					'information_schema.table_constraints AS t ON '.
						'k.table_name=t.table_name AND '.
						'k.constraint_name=t.constraint_name '.
						($this->dbname?
							('AND '.
							(preg_match('/pgsql/',$this->backend)?
								'k.table_catalog=t.table_catalog':
								'k.table_schema=t.table_schema').' '):'').
				'WHERE '.
					'c.table_name=\''.$table.'\''.
					($this->dbname?
						('AND '.
						(preg_match('/pgsql/',$this->backend)?
							'c.table_catalog':'c.table_schema').
							'=\''.$this->dbname.'\''):'').
				';',
				'field','pkey','PRIMARY KEY','type')
		);
		$match=FALSE;
		foreach ($cmd as $backend=>$val)
			if (preg_match('/'.$backend.'/',$this->backend)) {
				$match=TRUE;
				break;
			}
		if (!$match) {
			trigger_error(self::TEXT_DBEngine);
			return FALSE;
		}
		$result=$this->exec($val[0],NULL,$ttl);
		if (!$result) {
			trigger_error(sprintf(self::TEXT_Schema,$table));
			return FALSE;
		}
		return array(
			'result'=>$result,
			'field'=>$val[1],
			'pkname'=>$val[2],
			'pkval'=>$val[3],
			'type'=>$val[4]
		);
	}

	/**
		Custom session handler
			@param $table string
			@public
	**/
	function session($table='sessions') {
		$self=$this;
		session_set_save_handler(
			// Open
			function($path,$name) use($self,$table) {
				// Support these engines
				$cmd=array(
					'sqlite2?'=>
						'SELECT name FROM sqlite_master '.
						'WHERE type=\'table\' AND name=\''.$table.'\';',
					'mysql|mssql|sybase|dblib|pgsql'=>
						'SELECT table_name FROM information_schema.tables '.
						'WHERE '.
							(preg_match('/pgsql/',$self->backend)?
								'table_catalog':'table_schema').
								'=\''.$self->dbname.'\' AND '.
							'table_name=\''.$table.'\''
				);
				foreach ($cmd as $backend=>$val)
					if (preg_match('/'.$backend.'/',$self->backend))
						break;
				$result=$self->exec($val,NULL);
				if (!$result)
					// Create SQL table
					$self->exec(
						'CREATE TABLE '.
							(preg_match('/sqlite2?/',$self->backend)?
								'':($self->dbname.'.')).$table.' ('.
							'id VARCHAR(40),'.
							'data LONGTEXT,'.
							'stamp INTEGER'.
						');'
					);
				register_shutdown_function('session_commit');
				return TRUE;
			},
			// Close
			function() {
				return TRUE;
			},
			// Read
			function($id) use($table) {
				$axon=new Axon($table);
				$axon->load(array('id=:id',array(':id'=>$id)));
				return $axon->dry()?FALSE:$axon->data;
			},
			// Write
			function($id,$data) use($table) {
				$axon=new Axon($table);
				$axon->load(array('id=:id',array(':id'=>$id)));
				$axon->id=$id;
				$axon->data=$data;
				$axon->stamp=time();
				$axon->save();
				return TRUE;
			},
			// Delete
			function($id) use($table) {
				$axon=new Axon($table);
				$axon->erase(array('id=:id',array(':id'=>$id)));
				return TRUE;
			},
			// Cleanup
			function($max) use($table) {
				$axon=new Axon($table);
				$axon->erase('stamp+'.$max.'<'.time());
				return TRUE;
			}
		);
	}

	/**
		Class destructor
			@public
	**/
	function __destruct() {
		unset($this->pdo);
	}

	/**
		Class constructor
			@param $dsn string
			@param $user string
			@param $pw string
			@param $opt array
			@param $force boolean
			@public
	**/
	function __construct($dsn,$user=NULL,$pw=NULL,$opt=NULL,$force=FALSE) {
		if (!isset(self::$vars['MYSQL']))
			// Default MySQL character set
			self::$vars['MYSQL']='utf8';
		if (!$opt)
			// Append other default options
			$opt=array(PDO::ATTR_EMULATE_PREPARES=>FALSE)+(
				extension_loaded('pdo_mysql') &&
				preg_match('/^mysql:/',$dsn)?
					array(PDO::MYSQL_ATTR_INIT_COMMAND=>
						'SET NAMES '.self::$vars['MYSQL']):array()
			);
		list($this->dsn,$this->user,$this->pw,$this->opt)=
			array($this->resolve($dsn),$user,$pw,$opt);
		$this->backend=strstr($this->dsn,':',TRUE);
		preg_match('/dbname=([^;$]+)/',$this->dsn,$match);
		if ($match)
			$this->dbname=$match[1];
		if (!isset(self::$vars['DB']))
			self::$vars['DB']=$this;
		if ($force)
			$this->pdo=new PDO($this->dsn,$this->user,$this->pw,$this->opt);
	}

}

//! Axon ORM
class Axon extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_AxonConnect='Undefined database',
		TEXT_AxonEmpty='Axon is empty',
		TEXT_AxonArray='Must be an array of Axon objects',
		TEXT_AxonNotMapped='The field %s does not exist',
		TEXT_AxonCantUndef='Cannot undefine an Axon-mapped field',
		TEXT_AxonCantUnset='Cannot unset an Axon-mapped field',
		TEXT_AxonConflict='Name conflict with Axon-mapped field',
		TEXT_AxonInvalid='Invalid virtual field expression',
		TEXT_AxonReadOnly='Virtual fields are read-only';
	//@}

	//@{
	//! Axon properties
	public
		$_id;
	private
		$db,$table,$pkeys,$fields,$types,$adhoc,$mod,$empty,$cond,$seq,$ofs;
	//@}

	/**
		Axon factory
			@return object
			@param $row array
			@public
	**/
	function factory($row) {
		$self=get_class($this);
		$axon=new $self($this->table,$this->db);
		foreach ($row as $field=>$val) {
			if (array_key_exists($field,$this->fields)) {
				$axon->fields[$field]=$val;
				if ($this->pkeys &&
					array_key_exists($field,$this->pkeys))
					$axon->pkeys[$field]=$val;
			}
			else
				$axon->adhoc[$field]=array($this->adhoc[$field][0],$val);
			if ($axon->empty && $val)
				$axon->empty=FALSE;
		}
		return $axon;
	}

	/**
		Return current record contents as an array
			@return array
			@public
	**/
	function cast() {
		return $this->fields;
	}

	/**
		SQL select statement wrapper
			@return array
			@param $fields string
			@param $cond mixed
			@param $group string
			@param $seq string
			@param $limit int
			@param $ofs int
			@param $axon boolean
			@public
	**/
	function select(
		$fields=NULL,
		$cond=NULL,$group=NULL,$seq=NULL,$limit=0,$ofs=0,$axon=TRUE) {
		$rows=is_array($cond)?
			$this->db->exec(
				'SELECT '.($fields?:'*').' FROM '.$this->table.
					($cond?(' WHERE '.$cond[0]):'').
					($group?(' GROUP BY '.$group):'').
					($seq?(' ORDER BY '.$seq):'').
					($limit?(' LIMIT '.$limit):'').
					($ofs?(' OFFSET '.$ofs):'').';',
				$cond[1]
			):
			$this->db->exec(
				'SELECT '.($fields?:'*').' FROM '.$this->table.
					($cond?(' WHERE '.$cond):'').
					($group?(' GROUP BY '.$group):'').
					($seq?(' ORDER BY '.$seq):'').
					($limit?(' LIMIT '.$limit):'').
					($ofs?(' OFFSET '.$ofs):'').';'
			);
		if ($axon)
			// Convert array elements to Axon objects
			foreach ($rows as &$row)
				$row=$this->factory($row);
		return $rows;
	}

	/**
		SQL select statement wrapper;
		Returns an array of associative arrays
			@return array
			@param $fields string
			@param $cond mixed
			@param $group string
			@param $seq string
			@param $limit int
			@param $ofs int
			@public
	**/
	function aselect(
		$fields=NULL,
		$cond=NULL,$group=NULL,$seq=NULL,$limit=0,$ofs=0) {
		return $this->select($fields,$cond,$group,$seq,$limit,$ofs,FALSE);
	}

	/**
		Return all records that match criteria
			@return array
			@param $cond mixed
			@param $seq string
			@param $limit int
			@param $ofs int
			@param $axon boolean
			@public
	**/
	function find($cond=NULL,$seq=NULL,$limit=0,$ofs=0,$axon=TRUE) {
		$adhoc='';
		if ($this->adhoc)
			foreach ($this->adhoc as $field=>$val)
				$adhoc.=','.$val[0].' AS '.$field;
		return $this->select('*'.$adhoc,$cond,NULL,$seq,$limit,$ofs,$axon);
	}

	/**
		Return all records that match criteria as an array of
		associative arrays
			@return array
			@param $cond mixed
			@param $seq string
			@param $limit int
			@param $ofs int
			@public
	**/
	function afind($cond=NULL,$seq=NULL,$limit=0,$ofs=0) {
		return $this->find($cond,$seq,$limit,$ofs,FALSE);
	}

	/**
		Retrieve first record that matches criteria
			@return array
			@param $cond mixed
			@param $seq string
			@param $ofs int
			@public
	**/
	function findone($cond=NULL,$seq=NULL,$ofs=0) {
		list($result)=$this->find($cond,$seq,1,$ofs)?:array(NULL);
		return $result;
	}

	/**
		Return the array equivalent of the object matching criteria
			@return array
			@param $cond mixed
			@param $seq string
			@param $ofs int
			@public
	**/
	function afindone($cond=NULL,$seq=NULL,$ofs=0) {
		list($result)=$this->afind($cond,$seq,1,$ofs)?:array(NULL);
		return $result;
	}

	/**
		Count records that match condition
			@return int
			@param $cond mixed
			@public
	**/
	function found($cond=NULL) {
		$this->def('_found','COUNT(*)');
		list($result)=$this->find($cond);
		$found=$result->_found;
		$this->undef('_found');
		return $found;
	}

	/**
		Dehydrate Axon
			@public
	**/
	function reset() {
		foreach (array_keys($this->fields) as $field)
			$this->fields[$field]=NULL;
		if ($this->pkeys)
			foreach (array_keys($this->pkeys) as $pkey)
				$this->pkeys[$pkey]=NULL;
		if ($this->adhoc)
			foreach (array_keys($this->adhoc) as $adhoc)
				$this->adhoc[$adhoc][1]=NULL;
		$this->empty=TRUE;
		$this->mod=NULL;
		$this->cond=NULL;
		$this->seq=NULL;
		$this->ofs=0;
	}

	/**
		Hydrate Axon with first record that matches criteria
			@return mixed
			@param $cond mixed
			@param $seq string
			@param $ofs int
			@public
	**/
	function load($cond=NULL,$seq=NULL,$ofs=0) {
		if ($ofs>-1) {
			$this->ofs=0;
			if ($axon=$this->findone($cond,$seq,$ofs)) {
				if (method_exists($this,'beforeLoad') &&
					$this->beforeLoad()===FALSE)
					return;
				// Hydrate Axon
				foreach ($axon->fields as $field=>$val) {
					$this->fields[$field]=$val;
					if ($this->pkeys &&
						array_key_exists($field,$this->pkeys))
						$this->pkeys[$field]=$val;
				}
				if ($axon->adhoc)
					foreach ($axon->adhoc as $field=>$val)
						$this->adhoc[$field][1]=$val[1];
				list($this->empty,$this->cond,$this->seq,$this->ofs)=
					array(FALSE,$cond,$seq,$ofs);
				if (method_exists($this,'afterLoad'))
					$this->afterLoad();
				return $this;
			}
		}
		$this->reset();
		return FALSE;
	}

	/**
		Hydrate Axon with nth record relative to current position
			@return mixed
			@param $ofs int
			@public
	**/
	function skip($ofs=1) {
		if ($this->dry()) {
			trigger_error(self::TEXT_AxonEmpty);
			return FALSE;
		}
		return $this->load($this->cond,$this->seq,$this->ofs+$ofs);
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
		Insert record/update database
			@public
	**/
	function save() {
		if ($this->dry() ||
			method_exists($this,'beforeSave') &&
			$this->beforeSave()===FALSE)
			return;
		$new=TRUE;
		if ($this->pkeys)
			// If all primary keys are NULL, this is a new record
			foreach ($this->pkeys as $pkey)
				if (!is_null($pkey)) {
					$new=FALSE;
					break;
				}
		if ($new) {
			// Insert record
			$fields=$values='';
			$bind=array();
			foreach ($this->fields as $field=>$val)
				if (isset($this->mod[$field])) {
					$fields.=($fields?',':'').
						(preg_match('/^mysql$/',$this->backend)?
							('`'.$field.'`'):$field);
					$values.=($values?',':'').':'.$field;
					$bind[':'.$field]=array($val,$this->types[$field]);
				}
			if ($bind)
				$this->db->exec(
					'INSERT INTO '.$this->table.' ('.$fields.') '.
						'VALUES ('.$values.');',$bind);
			$this->_id=$this->db->pdo->lastinsertid();
		}
		elseif (!is_null($this->mod)) {
			// Update record
			$set=$cond='';
			foreach ($this->fields as $field=>$val)
				if (isset($this->mod[$field])) {
					$set.=($set?',':'').
						(preg_match('/^mysql$/',$this->backend)?
							('`'.$field.'`'):$field).'=:'.$field;
					$bind[':'.$field]=array($val,$this->types[$field]);
				}
			// Use primary keys to find record
			if ($this->pkeys)
				foreach ($this->pkeys as $pkey=>$val) {
					$cond.=($cond?' AND ':'').$pkey.'=:c_'.$pkey;
					$bind[':c_'.$pkey]=array($val,$this->types[$pkey]);
				}
			if ($set)
				$this->db->exec(
					'UPDATE '.$this->table.' SET '.$set.
						($cond?(' WHERE '.$cond):'').';',$bind);
		}
		if ($this->pkeys)
			// Update primary keys with new values
			foreach (array_keys($this->pkeys) as $pkey)
				$this->pkeys[$pkey]=$this->fields[$pkey];
		$this->empty=FALSE;
		if (method_exists($this,'afterSave'))
			$this->afterSave();
	}

	/**
		Delete record/s
			@param $cond mixed
			@param $force bool
			@public
	**/
	function erase($cond=NULL,$force=FALSE) {
		if (method_exists($this,'beforeErase') &&
			$this->beforeErase()===FALSE)
			return;
		if (!$cond)
			$cond=$this->cond;
		if ($cond) {
			if (!is_array($cond))
				$cond=array($cond,NULL);
			$this->db->exec(
				'DELETE FROM '.$this->table.' WHERE '.$cond[0],$cond[1]
			);
		}
		$this->reset();
		if (method_exists($this,'afterErase'))
			$this->afterErase();
	}

	/**
		Return TRUE if Axon is empty
			@return bool
			@public
	**/
	function dry() {
		return $this->empty;
	}

	/**
		Hydrate Axon with elements from array variable;
		Adhoc fields are not modified
			@param $name string
			@param $keys string
			@public
	**/
	function copyFrom($name,$keys=NULL) {
		$var=self::ref($name);
		$keys=is_null($keys)?array_keys($var):self::split($keys);
		foreach ($keys as $key)
			if (in_array($key,array_keys($var)) &&
				in_array($key,array_keys($this->fields))) {
				if ($this->fields[$key]!=$var[$key])
					$this->mod[$key]=TRUE;
				$this->fields[$key]=$var[$key];
			}
		$this->empty=FALSE;
	}

	/**
		Populate array variable with Axon properties
			@param $name string
			@param $keys string
			@public
	**/
	function copyTo($name,$keys=NULL) {
		if ($this->dry()) {
			trigger_error(self::TEXT_AxonEmpty);
			return FALSE;
		}
		$list=array_diff(preg_split('/[\|;,]/',$keys,0,
			PREG_SPLIT_NO_EMPTY),array(''));
		$keys=array_keys($this->fields);
		$adhoc=$this->adhoc?array_keys($this->adhoc):NULL;
		foreach ($adhoc?array_merge($keys,$adhoc):$keys as $key)
			if (empty($list) || in_array($key,$list)) {
				$var=&self::ref($name);
				if (in_array($key,array_keys($this->fields)))
					$var[$key]=$this->fields[$key];
				if ($this->adhoc &&
					in_array($key,array_keys($this->adhoc)))
					$var[$key]=$this->adhoc[$key];
			}
	}

	/**
		Synchronize Axon and SQL table structure
			@param $table string
			@param $db object
			@param $ttl int
			@public
	**/
	function sync($table,$db=NULL,$ttl=60) {
		if (!$db) {
			if (isset(self::$vars['DB']) && is_a(self::$vars['DB'],'DB'))
				$db=self::$vars['DB'];
			else {
				trigger_error(self::TEXT_AxonConnect);
				return;
			}
		}
		if (method_exists($this,'beforeSync') &&
			$this->beforeSync()===FALSE)
			return;
		// Initialize Axon
		list($this->db,$this->table)=array($db,$table);
		if ($schema=$db->schema($table,$ttl)) {
			// Populate properties
			foreach ($schema['result'] as $row) {
				$this->fields[$row[$schema['field']]]=NULL;
				if ($row[$schema['pkname']]==$schema['pkval'])
					// Save primary key
					$this->pkeys[$row[$schema['field']]]=NULL;
				$this->types[$row[$schema['field']]]=
					preg_match('/int|bool/i',$row[$schema['type']],$match)?
						constant('PDO::PARAM_'.strtoupper($match[0])):
						PDO::PARAM_STR;
			}
			$this->empty=TRUE;
		}
		if (method_exists($this,'afterSync'))
			$this->afterSync();
	}

	/**
		Create an adhoc field
			@param $field string
			@param $expr string
			@public
	**/
	function def($field,$expr) {
		if (array_key_exists($field,$this->fields)) {
			trigger_error(self::TEXT_AxonConflict);
			return;
		}
		$this->adhoc[$field]=array($expr,NULL);
	}

	/**
		Destroy an adhoc field
			@param $field string
			@public
	**/
	function undef($field) {
		if (array_key_exists($field,$this->fields) || !self::isdef($field)) {
			trigger_error(sprintf(self::TEXT_AxonCantUndef,$field));
			return;
		}
		unset($this->adhoc[$field]);
	}

	/**
		Return TRUE if adhoc field exists
			@param $field string
			@public
	**/
	function isdef($field) {
		return $this->adhoc && array_key_exists($field,$this->adhoc);
	}

	/**
		Return value of mapped field
			@return mixed
			@param $field string
			@public
	**/
	function &__get($field) {
		if (array_key_exists($field,$this->fields))
			return $this->fields[$field];
		if (self::isdef($field))
			return $this->adhoc[$field][1];
		return self::$false;
	}

	/**
		Assign value to mapped field
			@return bool
			@param $field string
			@param $val mixed
			@public
	**/
	function __set($field,$val) {
		if (array_key_exists($field,$this->fields)) {
			if ($this->fields[$field]!=$val && !isset($this->mod[$field]))
				$this->mod[$field]=TRUE;
			$this->fields[$field]=$val;
			if (!is_null($val))
				$this->empty=FALSE;
			return TRUE;
		}
		if (self::isdef($field))
			trigger_error(self::TEXT_AxonReadOnly);
		return FALSE;
	}

	/**
		Trigger error in case a field is unset
			@param $field string
			@public
	**/
	function __unset($field) {
		trigger_error(str_replace('@FIELD',$field,self::TEXT_AxonCantUnset));
	}

	/**
		Return TRUE if mapped field is set
			@return bool
			@param $field string
			@public
	**/
	function __isset($field) {
		return array_key_exists($field,$this->fields) ||
			$this->adhoc && array_key_exists($field,$this->adhoc);
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
