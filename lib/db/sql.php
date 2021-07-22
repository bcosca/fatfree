<?php

/*

	Copyright (c) 2009-2019 F3::Factory/Bong Cosca, All rights reserved.

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

//! PDO wrapper
class SQL {

	//@{ Error messages
	const
		E_PKey='Table %s does not have a primary key';
	//@}

	const
		PARAM_FLOAT='float';

	protected
		//! UUID
		$uuid,
		//! Raw PDO
		$pdo,
		//! Data source name
		$dsn,
		//! Database engine
		$engine,
		//! Database name
		$dbname,
		//! Transaction flag
		$trans=FALSE,
		//! Number of rows affected by query
		$rows=0,
		//! SQL log
		$log;

	/**
	*	Begin SQL transaction
	*	@return bool
	**/
	function begin() {
		$out=$this->pdo->begintransaction();
		$this->trans=TRUE;
		return $out;
	}

	/**
	*	Rollback SQL transaction
	*	@return bool
	**/
	function rollback() {
		$out=FALSE;
		if ($this->pdo->inTransaction())
			$out=$this->pdo->rollback();
		$this->trans=FALSE;
		return $out;
	}

	/**
	*	Commit SQL transaction
	*	@return bool
	**/
	function commit() {
		$out=FALSE;
		if ($this->pdo->inTransaction())
			$out=$this->pdo->commit();
		$this->trans=FALSE;
		return $out;
	}

	/**
	*	Return transaction flag
	*	@return bool
	**/
	function trans() {
		return $this->trans;
	}

	/**
	*	Map data type of argument to a PDO constant
	*	@return int
	*	@param $val scalar
	**/
	function type($val) {
		switch (gettype($val)) {
			case 'NULL':
				return \PDO::PARAM_NULL;
			case 'boolean':
				return \PDO::PARAM_BOOL;
			case 'integer':
				return \PDO::PARAM_INT;
			case 'resource':
				return \PDO::PARAM_LOB;
			case 'float':
				return self::PARAM_FLOAT;
			default:
				return \PDO::PARAM_STR;
		}
	}

	/**
	*	Cast value to PHP type
	*	@return mixed
	*	@param $type string
	*	@param $val mixed
	**/
	function value($type,$val) {
		switch ($type) {
			case self::PARAM_FLOAT:
				if (!is_string($val))
					$val=str_replace(',','.',$val);
				return $val;
			case \PDO::PARAM_NULL:
				return NULL;
			case \PDO::PARAM_INT:
				return (int)$val;
			case \PDO::PARAM_BOOL:
				return (bool)$val;
			case \PDO::PARAM_STR:
				return (string)$val;
			case \PDO::PARAM_LOB:
				return (binary)$val;
		}
	}

	/**
	*	Execute SQL statement(s)
	*	@return array|int|FALSE
	*	@param $cmds string|array
	*	@param $args string|array
	*	@param $ttl int|array
	*	@param $log bool
	*	@param $stamp bool
	**/
	function exec($cmds,$args=NULL,$ttl=0,$log=TRUE,$stamp=FALSE) {
		$tag='';
		if (is_array($ttl))
			list($ttl,$tag)=$ttl;
		$auto=FALSE;
		if (is_null($args))
			$args=[];
		elseif (is_scalar($args))
			$args=[1=>$args];
		if (is_array($cmds)) {
			if (count($args)<($count=count($cmds)))
				// Apply arguments to SQL commands
				$args=array_fill(0,$count,$args);
			if (!$this->trans) {
				$this->begin();
				$auto=TRUE;
			}
		}
		else {
			$count=1;
			$cmds=[$cmds];
			$args=[$args];
		}
		if ($this->log===FALSE)
			$log=FALSE;
		$fw=\Base::instance();
		$cache=\Cache::instance();
		$result=FALSE;
		for ($i=0;$i<$count;++$i) {
			$cmd=$cmds[$i];
			$arg=$args[$i];
			// ensure 1-based arguments
			if (array_key_exists(0,$arg)) {
				array_unshift($arg,'');
				unset($arg[0]);
			}
			if (!preg_replace('/(^\s+|[\s;]+$)/','',$cmd))
				continue;
			$now=microtime(TRUE);
			$keys=$vals=[];
			if ($fw->CACHE && $ttl && ($cached=$cache->exists(
				$hash=$fw->hash($this->dsn.$cmd.
				$fw->stringify($arg)).($tag?'.'.$tag:'').'.sql',$result)) &&
				$cached[0]+$ttl>microtime(TRUE)) {
				foreach ($arg as $key=>$val) {
					$vals[]=$fw->stringify(is_array($val)?$val[0]:$val);
					$keys[]='/'.preg_quote(is_numeric($key)?chr(0).'?':$key).
						'/';
				}
				if ($log)
					$this->log.=($stamp?(date('r').' '):'').'('.
						sprintf('%.1f',1e3*(microtime(TRUE)-$now)).'ms) '.
						'[CACHED] '.
						preg_replace($keys,$vals,
							str_replace('?',chr(0).'?',$cmd),1).PHP_EOL;
			}
			elseif (is_object($query=$this->pdo->prepare($cmd))) {
				foreach ($arg as $key=>$val) {
					if (is_array($val)) {
						// User-specified data type
						$query->bindvalue($key,$val[0],
							$val[1]==self::PARAM_FLOAT?\PDO::PARAM_STR:$val[1]);
						$vals[]=$fw->stringify($this->value($val[1],$val[0]));
					}
					else {
						// Convert to PDO data type
						$query->bindvalue($key,$val,
							($type=$this->type($val))==self::PARAM_FLOAT?
								\PDO::PARAM_STR:$type);
						$vals[]=$fw->stringify($this->value($type,$val));
					}
					$keys[]='/'.preg_quote(is_numeric($key)?chr(0).'?':$key).
						'/';
				}
				if ($log)
					$this->log.=($stamp?(date('r').' '):'').'(-0ms) '.
						preg_replace($keys,$vals,
							str_replace('?',chr(0).'?',$cmd),1).PHP_EOL;
				$query->execute();
				if ($log)
					$this->log=str_replace('(-0ms)',
						'('.sprintf('%.1f',1e3*(microtime(TRUE)-$now)).'ms)',
						$this->log);
				if (($error=$query->errorinfo()) && $error[0]!=\PDO::ERR_NONE) {
					// Statement-level error occurred
					if ($this->trans)
						$this->rollback();
					user_error('PDOStatement: '.$error[2],E_USER_ERROR);
				}
				if (preg_match('/(?:^[\s\(]*'.
					'(?:WITH|EXPLAIN|SELECT|PRAGMA|SHOW)|RETURNING)\b/is',$cmd) ||
					(preg_match('/^\s*(?:CALL|EXEC)\b/is',$cmd) &&
						$query->columnCount())) {
					$result=$query->fetchall(\PDO::FETCH_ASSOC);
					// Work around SQLite quote bug
					if (preg_match('/sqlite2?/',$this->engine))
						foreach ($result as $pos=>$rec) {
							unset($result[$pos]);
							$result[$pos]=[];
							foreach ($rec as $key=>$val)
								$result[$pos][trim($key,'\'"[]`')]=$val;
						}
					$this->rows=count($result);
					if ($fw->CACHE && $ttl)
						// Save to cache backend
						$cache->set($hash,$result,$ttl);
				}
				else
					$this->rows=$result=$query->rowcount();
				$query->closecursor();
				unset($query);
			}
			elseif (($error=$this->pdo->errorInfo()) && $error[0]!=\PDO::ERR_NONE) {
				// PDO-level error occurred
				if ($this->trans)
					$this->rollback();
				user_error('PDO: '.$error[2],E_USER_ERROR);
			}

		}
		if ($this->trans && $auto)
			$this->commit();
		return $result;
	}

	/**
	*	Return number of rows affected by last query
	*	@return int
	**/
	function count() {
		return $this->rows;
	}

	/**
	*	Return SQL profiler results (or disable logging)
	*	@return string
	*	@param $flag bool
	**/
	function log($flag=TRUE) {
		if ($flag)
			return $this->log;
		$this->log=FALSE;
	}

	/**
	*	Return TRUE if table exists
	*	@return bool
	*	@param $table string
	**/
	function exists($table) {
		$mode=$this->pdo->getAttribute(\PDO::ATTR_ERRMODE);
		$this->pdo->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_SILENT);
		$out=$this->pdo->
			query('SELECT 1 FROM '.$this->quotekey($table).' LIMIT 1');
		$this->pdo->setAttribute(\PDO::ATTR_ERRMODE,$mode);
		return is_object($out);
	}

	/**
	*	Retrieve schema of SQL table
	*	@return array|FALSE
	*	@param $table string
	*	@param $fields array|string
	*	@param $ttl int|array
	**/
	function schema($table,$fields=NULL,$ttl=0) {
		$fw=\Base::instance();
		$cache=\Cache::instance();
		if ($fw->CACHE && $ttl &&
			($cached=$cache->exists(
				$hash=$fw->hash($this->dsn.$table).'.schema',$result)) &&
			$cached[0]+$ttl>microtime(TRUE))
			return $result;
		if (strpos($table,'.'))
			list($schema,$table)=explode('.',$table);
		// Supported engines
		// format: engine_name => array of:
		//	0: query
		//	1: field name of column name
		//	2: field name of column type
		//	3: field name of default value
		//	4: field name of nullable value
		//	5: expected field value to be nullable
		//	6: field name of primary key flag
		//	7: expected field value to be a primary key
		//	8: field name of auto increment check (optional)
		//	9: expected field value to be an auto-incremented identifier
		$cmd=[
			'sqlite2?'=>[
				'SELECT * FROM pragma_table_info('.$this->quote($table).') JOIN ('.
					'SELECT sql FROM sqlite_master WHERE (type=\'table\' OR type=\'view\')  AND '.
					'name='.$this->quote($table).')',
				'name','type','dflt_value','notnull',0,'pk',TRUE,'sql',
					'/\W(%s)\W+[^,]+?AUTOINCREMENT\W/i'],
			'mysql'=>[
				'SHOW columns FROM `'.$this->dbname.'`.`'.$table.'`',
				'Field','Type','Default','Null','YES','Key','PRI','Extra','auto_increment'],
			'mssql|sqlsrv|sybase|dblib|pgsql|odbc'=>[
				'SELECT '.
					'C.COLUMN_NAME AS field,'.
					'C.DATA_TYPE AS type,'.
					'C.COLUMN_DEFAULT AS defval,'.
					'C.IS_NULLABLE AS nullable,'.
				($this->engine=='pgsql'
					?'COALESCE(POSITION(\'nextval\' IN C.COLUMN_DEFAULT),0) AS autoinc,'
					:'columnproperty(object_id(C.TABLE_NAME),C.COLUMN_NAME,\'IsIdentity\')'
						.' AS autoinc,').
					'T.CONSTRAINT_TYPE AS pkey '.
				'FROM INFORMATION_SCHEMA.COLUMNS AS C '.
				'LEFT OUTER JOIN '.
					'INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS K '.
					'ON '.
						'C.TABLE_NAME=K.TABLE_NAME AND '.
						'C.COLUMN_NAME=K.COLUMN_NAME AND '.
						'C.TABLE_SCHEMA=K.TABLE_SCHEMA '.
						($this->dbname?
							('AND C.TABLE_CATALOG=K.TABLE_CATALOG '):'').
				'LEFT OUTER JOIN '.
					'INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS T ON '.
						'K.TABLE_NAME=T.TABLE_NAME AND '.
						'K.CONSTRAINT_NAME=T.CONSTRAINT_NAME AND '.
						'K.TABLE_SCHEMA=T.TABLE_SCHEMA '.
						($this->dbname?
							('AND K.TABLE_CATALOG=T.TABLE_CATALOG '):'').
				'WHERE '.
					'C.TABLE_NAME='.$this->quote($table).
					($this->dbname?
						(' AND C.TABLE_CATALOG='.
							$this->quote($this->dbname)):''),
				'field','type','defval','nullable','YES','pkey','PRIMARY KEY','autoinc',1],
			'oci'=>[
				'SELECT c.column_name AS field, '.
					'c.data_type AS type, '.
					'c.data_default AS defval, '.
					'c.nullable AS nullable, '.
					'(SELECT t.constraint_type '.
						'FROM all_cons_columns acc '.
						'LEFT OUTER JOIN all_constraints t '.
						'ON acc.constraint_name=t.constraint_name '.
						'WHERE acc.table_name='.$this->quote($table).' '.
						'AND acc.column_name=c.column_name '.
						'AND constraint_type='.$this->quote('P').') AS pkey '.
				'FROM all_tab_cols c '.
				'WHERE c.table_name='.$this->quote($table),
				'FIELD','TYPE','DEFVAL','NULLABLE','Y','PKEY','P']
		];
		if (is_string($fields))
			$fields=\Base::instance()->split($fields);
		$conv=[
			'int\b|integer'=>\PDO::PARAM_INT,
			'bool'=>\PDO::PARAM_BOOL,
			'blob|bytea|image|binary'=>\PDO::PARAM_LOB,
			'float|real|double|decimal|numeric'=>self::PARAM_FLOAT,
			'.+'=>\PDO::PARAM_STR
		];
		foreach ($cmd as $key=>$val)
			if (preg_match('/'.$key.'/',$this->engine)) {
				$rows=[];
				foreach ($this->exec($val[0],NULL) as $row)
					if (!$fields || in_array($row[$val[1]],$fields)) {
						foreach ($conv as $regex=>$type)
							if (preg_match('/'.$regex.'/i',$row[$val[2]]))
								break;
						if (!isset($rows[$row[$val[1]]])) // handle duplicate rows in PgSQL
							$rows[$row[$val[1]]]=[
								'type'=>$row[$val[2]],
								'pdo_type'=>$type,
								'default'=>is_string($row[$val[3]])?
									preg_replace('/^\s*([\'"])(.*)\1\s*/','\2',
									$row[$val[3]]):$row[$val[3]],
								'nullable'=>$row[$val[4]]==$val[5],
								'pkey'=>$row[$val[6]]==$val[7],
								'auto_inc'=>isset($val[8]) && isset($row[$val[8]])
									? ($this->engine=='sqlite'?
										(bool) preg_match(sprintf($val[9],$row[$val[1]]),
											$row[$val[8]]):
										($row[$val[8]]==$val[9])
									) : NULL,
							];
					}
				if ($fw->CACHE && $ttl)
					// Save to cache backend
					$cache->set($hash,$rows,$ttl);
				return $rows;
			}
		user_error(sprintf(self::E_PKey,$table),E_USER_ERROR);
		return FALSE;
	}

	/**
	*	Quote string
	*	@return string
	*	@param $val mixed
	*	@param $type int
	**/
	function quote($val,$type=\PDO::PARAM_STR) {
		return $this->engine=='odbc'?
			(is_string($val)?
				\Base::instance()->stringify(str_replace('\'','\'\'',$val)):
				$val):
			$this->pdo->quote($val,$type);
	}

	/**
	*	Return UUID
	*	@return string
	**/
	function uuid() {
		return $this->uuid;
	}

	/**
	*	Return parent object
	*	@return \PDO
	**/
	function pdo() {
		return $this->pdo;
	}

	/**
	*	Return database engine
	*	@return string
	**/
	function driver() {
		return $this->engine;
	}

	/**
	*	Return server version
	*	@return string
	**/
	function version() {
		return $this->pdo->getattribute(\PDO::ATTR_SERVER_VERSION);
	}

	/**
	*	Return database name
	*	@return string
	**/
	function name() {
		return $this->dbname;
	}

	/**
	*	Return quoted identifier name
	*	@return string
	*	@param $key
	*	@param bool $split
	 **/
	function quotekey($key, $split=TRUE) {
		$delims=[
			'sqlite2?|mysql'=>'``',
			'pgsql|oci'=>'""',
			'mssql|sqlsrv|odbc|sybase|dblib'=>'[]'
		];
		$use='';
		foreach ($delims as $engine=>$delim)
			if (preg_match('/'.$engine.'/',$this->engine)) {
				$use=$delim;
				break;
			}
		return $use[0].($split ? implode($use[1].'.'.$use[0],explode('.',$key))
			: $key).$use[1];
	}

	/**
	*	Redirect call to PDO object
	*	@return mixed
	*	@param $func string
	*	@param $args array
	**/
	function __call($func,array $args) {
		return call_user_func_array([$this->pdo,$func],$args);
	}

	//! Prohibit cloning
	private function __clone() {
	}

	/**
	*	Instantiate class
	*	@param $dsn string
	*	@param $user string
	*	@param $pw string
	*	@param $options array
	**/
	function __construct($dsn,$user=NULL,$pw=NULL,array $options=NULL) {
		$fw=\Base::instance();
		$this->uuid=$fw->hash($this->dsn=$dsn);
		if (preg_match('/^.+?(?:dbname|database)=(.+?)(?=;|$)/is',$dsn,$parts))
			$this->dbname=str_replace('\\ ',' ',$parts[1]);
		if (!$options)
			$options=[];
		if (isset($parts[0]) && strstr($parts[0],':',TRUE)=='mysql')
			$options+=[\PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES '.
				strtolower(str_replace('-','',$fw->ENCODING)).';'];
		$this->pdo=new \PDO($dsn,$user,$pw,$options);
		$this->engine=$this->pdo->getattribute(\PDO::ATTR_DRIVER_NAME);
	}

}
