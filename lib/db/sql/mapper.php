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

namespace DB\SQL;

//! SQL data mapper
class Mapper extends \DB\Cursor {

	protected
		//! PDO wrapper
		$db,
		//! Database engine
		$engine,
		//! SQL table
		$source,
		//! SQL table (quoted)
		$table,
		//! Alias for SQL table
		$as,
		//! Last insert ID
		$_id,
		//! Defined fields
		$fields,
		//! Adhoc fields
		$adhoc=[],
		//! Dynamic properties
		$props=[];

	/**
	*	Return database type
	*	@return string
	**/
	function dbtype() {
		return 'SQL';
	}

	/**
	*	Return mapped table
	*	@return string
	**/
	function table() {
		return $this->source;
	}

	/**
	*	Return TRUE if any/specified field value has changed
	*	@return bool
	*	@param $key string
	**/
	function changed($key=NULL) {
		if (isset($key))
			return $this->fields[$key]['changed'];
		foreach($this->fields as $key=>$field)
			if ($field['changed'])
				return TRUE;
		return FALSE;
	}

	/**
	*	Return TRUE if field is defined
	*	@return bool
	*	@param $key string
	**/
	function exists($key) {
		return array_key_exists($key,$this->fields+$this->adhoc);
	}

	/**
	*	Assign value to field
	*	@return scalar
	*	@param $key string
	*	@param $val scalar
	**/
	function set($key,$val) {
		if (array_key_exists($key,$this->fields)) {
			$val=is_null($val) && $this->fields[$key]['nullable']?
				NULL:$this->db->value($this->fields[$key]['pdo_type'],$val);
			if ($this->fields[$key]['initial']!==$val ||
				$this->fields[$key]['default']!==$val && is_null($val))
				$this->fields[$key]['changed']=TRUE;
			return $this->fields[$key]['value']=$val;
		}
		// Adjust result on existing expressions
		if (isset($this->adhoc[$key]))
			$this->adhoc[$key]['value']=$val;
		elseif (is_string($val))
			// Parenthesize expression in case it's a subquery
			$this->adhoc[$key]=['expr'=>'('.$val.')','value'=>NULL];
		else
			$this->props[$key]=$val;
		return $val;
	}

	/**
	*	Retrieve value of field
	*	@return scalar
	*	@param $key string
	**/
	function &get($key) {
		if ($key=='_id')
			return $this->_id;
		elseif (array_key_exists($key,$this->fields))
			return $this->fields[$key]['value'];
		elseif (array_key_exists($key,$this->adhoc))
			return $this->adhoc[$key]['value'];
		elseif (array_key_exists($key,$this->props))
			return $this->props[$key];
		user_error(sprintf(self::E_Field,$key),E_USER_ERROR);
	}

	/**
	*	Clear value of field
	*	@return NULL
	*	@param $key string
	**/
	function clear($key) {
		if (array_key_exists($key,$this->adhoc))
			unset($this->adhoc[$key]);
		else
			unset($this->props[$key]);
	}

	/**
	*	Invoke dynamic method
	*	@return mixed
	*	@param $func string
	*	@param $args array
	**/
	function __call($func,$args) {
		return call_user_func_array(
			(array_key_exists($func,$this->props)?
				$this->props[$func]:
				$this->$func),$args
		);
	}

	/**
	*	Convert array to mapper object
	*	@return static
	*	@param $row array
	**/
	function factory($row) {
		$mapper=clone($this);
		$mapper->reset();
		foreach ($row as $key=>$val) {
			if (array_key_exists($key,$this->fields))
				$var='fields';
			elseif (array_key_exists($key,$this->adhoc))
				$var='adhoc';
			else
				continue;
			$mapper->{$var}[$key]['value']=$val;
			$mapper->{$var}[$key]['initial']=$val;
			if ($var=='fields' && $mapper->{$var}[$key]['pkey'])
				$mapper->{$var}[$key]['previous']=$val;
		}
		$mapper->query=[clone($mapper)];
		if (isset($mapper->trigger['load']))
			\Base::instance()->call($mapper->trigger['load'],$mapper);
		return $mapper;
	}

	/**
	*	Return fields of mapper object as an associative array
	*	@return array
	*	@param $obj object
	**/
	function cast($obj=NULL) {
		if (!$obj)
			$obj=$this;
		return array_map(
			function($row) {
				return $row['value'];
			},
			$obj->fields+$obj->adhoc
		);
	}

	/**
	*	Build query string and arguments
	*	@return array
	*	@param $fields string
	*	@param $filter string|array
	*	@param $options array
	**/
	function stringify($fields,$filter=NULL,array $options=NULL) {
		if (!$options)
			$options=[];
		$options+=[
			'group'=>NULL,
			'order'=>NULL,
			'limit'=>0,
			'offset'=>0,
			'comment'=>NULL
		];
		$db=$this->db;
		$sql='SELECT '.$fields.' FROM '.$this->table;
		if (isset($this->as))
			$sql.=' AS '.$this->db->quotekey($this->as);
		$args=[];
		if (is_array($filter)) {
			$args=isset($filter[1]) && is_array($filter[1])?
				$filter[1]:
				array_slice($filter,1,NULL,TRUE);
			$args=is_array($args)?$args:[1=>$args];
			list($filter)=$filter;
		}
		if ($filter)
			$sql.=' WHERE '.$filter;
		if ($options['group']) {
			$sql.=' GROUP BY '.implode(',',array_map(
				function($str) use($db) {
					return preg_replace_callback(
						'/\b(\w+[._\-\w]*)\h*(HAVING.+|$)/i',
						function($parts) use($db) {
							return $db->quotekey($parts[1]).
								(isset($parts[2])?(' '.$parts[2]):'');
						},
						$str
					);
				},
				explode(',',$options['group'])));
		}
		if ($options['order']) {
			$char=substr($db->quotekey(''),0,1);// quoting char
			$order=' ORDER BY '.(is_bool(strpos($options['order'],$char))?
				implode(',',array_map(function($str) use($db) {
					return preg_match('/^\h*(\w+[._\-\w]*)'.
						'(?:\h+((?:ASC|DESC)[\w\h]*))?\h*$/i',
						$str,$parts)?
						($db->quotekey($parts[1]).
						(isset($parts[2])?(' '.$parts[2]):'')):$str;
				},explode(',',$options['order']))):
				$options['order']);
		}
		// SQL Server fixes
		if (preg_match('/mssql|sqlsrv|odbc/', $this->engine) &&
			($options['limit'] || $options['offset'])) {
			// order by pkey when no ordering option was given
			if (!$options['order'])
				foreach ($this->fields as $key=>$field)
					if ($field['pkey']) {
						$order=' ORDER BY '.$db->quotekey($key);
						break;
					}
			$ofs=$options['offset']?(int)$options['offset']:0;
			$lmt=$options['limit']?(int)$options['limit']:0;
			if (strncmp($db->version(),'11',2)>=0) {
				// SQL Server >= 2012
				$sql.=$order.' OFFSET '.$ofs.' ROWS';
				if ($lmt)
					$sql.=' FETCH NEXT '.$lmt.' ROWS ONLY';
			}
			else {
				// SQL Server 2008
				$sql=preg_replace('/SELECT/',
					'SELECT '.
					($lmt>0?'TOP '.($ofs+$lmt):'').' ROW_NUMBER() '.
					'OVER ('.$order.') AS rnum,',$sql.$order,1);
				$sql='SELECT * FROM ('.$sql.') x WHERE rnum > '.($ofs);
			}
		}
		else {
			if (isset($order))
				$sql.=$order;
			if ($options['limit'])
				$sql.=' LIMIT '.(int)$options['limit'];
			if ($options['offset'])
				$sql.=' OFFSET '.(int)$options['offset'];
		}
		if ($options['comment'])
			$sql.="\n".' /* '.$options['comment'].' */';
		return [$sql,$args];
	}

	/**
	*	Build query string and execute
	*	@return static[]
	*	@param $fields string
	*	@param $filter string|array
	*	@param $options array
	*	@param $ttl int|array
	**/
	function select($fields,$filter=NULL,array $options=NULL,$ttl=0) {
		list($sql,$args)=$this->stringify($fields,$filter,$options);
		$result=$this->db->exec($sql,$args,$ttl);
		$out=[];
		foreach ($result as &$row) {
			foreach ($row as $field=>&$val) {
				if (array_key_exists($field,$this->fields)) {
					if (!is_null($val) || !$this->fields[$field]['nullable'])
						$val=$this->db->value(
							$this->fields[$field]['pdo_type'],$val);
				}
				unset($val);
			}
			$out[]=$this->factory($row);
			unset($row);
		}
		return $out;
	}

	/**
	*	Return records that match criteria
	*	@return static[]
	*	@param $filter string|array
	*	@param $options array
	*	@param $ttl int|array
	**/
	function find($filter=NULL,array $options=NULL,$ttl=0) {
		if (!$options)
			$options=[];
		$options+=[
			'group'=>NULL,
			'order'=>NULL,
			'limit'=>0,
			'offset'=>0
		];
		$adhoc='';
		foreach ($this->adhoc as $key=>$field)
			$adhoc.=','.$field['expr'].' AS '.$this->db->quotekey($key);
		return $this->select(
			($options['group'] && !preg_match('/mysql|sqlite/',$this->engine)?
				$options['group']:
				implode(',',array_map([$this->db,'quotekey'],
					array_keys($this->fields)))).$adhoc,$filter,$options,$ttl);
	}

	/**
	*	Count records that match criteria
	*	@return int
	*	@param $filter string|array
	*	@param $options array
	*	@param $ttl int|array
	**/
	function count($filter=NULL,array $options=NULL,$ttl=0) {
		if (!($subquery_mode=($options && !empty($options['group']))))
			$this->adhoc['_rows']=['expr'=>'COUNT(*)','value'=>NULL];
		$adhoc=[];
		foreach ($this->adhoc as $key=>$field)
			// Add all adhoc fields
			// (make them available for grouping, sorting, having)
			$adhoc[]=$field['expr'].' AS '.$this->db->quotekey($key);
		$fields=implode(',',$adhoc);
		if ($subquery_mode) {
			if (empty($fields))
				// Select at least one field, ideally the grouping fields
				// or sqlsrv fails
				$fields=preg_replace('/HAVING.+$/i','',$options['group']);
			if (preg_match('/mssql|dblib|sqlsrv/',$this->engine))
				$fields='TOP 100 PERCENT '.$fields;
		}
		list($sql,$args)=$this->stringify($fields,$filter,$options);
		if ($subquery_mode)
			$sql='SELECT COUNT(*) AS '.$this->db->quotekey('_rows').' '.
				'FROM ('.$sql.') AS '.$this->db->quotekey('_temp');
		$result=$this->db->exec($sql,$args,$ttl);
		unset($this->adhoc['_rows']);
		return (int)$result[0]['_rows'];
	}
	/**
	*	Return record at specified offset using same criteria as
	*	previous load() call and make it active
	*	@return static
	*	@param $ofs int
	**/
	function skip($ofs=1) {
		$out=parent::skip($ofs);
		$dry=$this->dry();
		foreach ($this->fields as $key=>&$field) {
			$field['value']=$dry?NULL:$out->fields[$key]['value'];
			$field['initial']=$field['value'];
			$field['changed']=FALSE;
			if ($field['pkey'])
				$field['previous']=$dry?NULL:$out->fields[$key]['value'];
			unset($field);
		}
		foreach ($this->adhoc as $key=>&$field) {
			$field['value']=$dry?NULL:$out->adhoc[$key]['value'];
			unset($field);
		}
		if (!$dry && isset($this->trigger['load']))
			\Base::instance()->call($this->trigger['load'],$this);
		return $out;
	}

	/**
	*	Insert new record
	*	@return static
	**/
	function insert() {
		$args=[];
		$actr=0;
		$nctr=0;
		$fields='';
		$values='';
		$filter='';
		$pkeys=[];
		$nkeys=[];
		$ckeys=[];
		$inc=NULL;
		foreach ($this->fields as $key=>$field)
			if ($field['pkey'])
				$pkeys[$key]=$field['previous'];
		if (isset($this->trigger['beforeinsert']) &&
			\Base::instance()->call($this->trigger['beforeinsert'],
				[$this,$pkeys])===FALSE)
			return $this;
		foreach ($this->fields as $key=>&$field) {
			if ($field['pkey']) {
				$field['previous']=$field['value'];
				if (!$inc && $field['pdo_type']==\PDO::PARAM_INT &&
					empty($field['value']) && !$field['nullable'])
					$inc=$key;
				$filter.=($filter?' AND ':'').$this->db->quotekey($key).'=?';
				$nkeys[$nctr+1]=[$field['value'],$field['pdo_type']];
				$nctr++;
			}
			if ($field['changed'] && $key!=$inc) {
				$fields.=($actr?',':'').$this->db->quotekey($key);
				$values.=($actr?',':'').'?';
				$args[$actr+1]=[$field['value'],$field['pdo_type']];
				$actr++;
				$ckeys[]=$key;
			}
		}
		if ($fields) {
			$add=$aik='';
			if ($this->engine=='pgsql' && !empty($pkeys)) {
				$names=array_keys($pkeys);
				$aik=end($names);
				$add=' RETURNING '.$this->db->quotekey($aik);
			}
			$lID=$this->db->exec(
				(preg_match('/mssql|dblib|sqlsrv/',$this->engine) &&
				array_intersect(array_keys($pkeys),$ckeys)?
					'SET IDENTITY_INSERT '.$this->table.' ON;':'').
				'INSERT INTO '.$this->table.' ('.$fields.') '.
				'VALUES ('.$values.')'.$add,$args
			);
			if ($this->engine=='pgsql' && $lID && $aik)
				$this->_id=$lID[0][$aik];
			elseif ($this->engine!='oci')
				$this->_id=$this->db->lastinsertid();
			// Reload to obtain default and auto-increment field values
			if ($reload=(($inc && $this->_id) || $filter))
				$this->load($inc?
					[$inc.'=?',$this->db->value(
						$this->fields[$inc]['pdo_type'],$this->_id)]:
					[$filter,$nkeys]);
			if (isset($this->trigger['afterinsert']))
				\Base::instance()->call($this->trigger['afterinsert'],
					[$this,$pkeys]);
			// reset changed flag after calling afterinsert
			if (!$reload)
				foreach ($this->fields as $key=>&$field) {
					$field['changed']=FALSE;
					$field['initial']=$field['value'];
					unset($field);
				}
		}
		return $this;
	}

	/**
	*	Update current record
	*	@return static
	**/
	function update() {
		$args=[];
		$ctr=0;
		$pairs='';
		$filter='';
		$pkeys=[];
		foreach ($this->fields as $key=>$field)
			if ($field['pkey'])
				$pkeys[$key]=$field['previous'];
		if (isset($this->trigger['beforeupdate']) &&
			\Base::instance()->call($this->trigger['beforeupdate'],
				[$this,$pkeys])===FALSE)
			return $this;
		foreach ($this->fields as $key=>$field)
			if ($field['changed']) {
				$pairs.=($pairs?',':'').$this->db->quotekey($key).'=?';
				$args[++$ctr]=[$field['value'],$field['pdo_type']];
			}
		foreach ($this->fields as $key=>$field)
			if ($field['pkey']) {
				$filter.=($filter?' AND ':' WHERE ').
					$this->db->quotekey($key).'=?';
				$args[++$ctr]=[$field['previous'],$field['pdo_type']];
			}
		if ($pairs) {
			$sql='UPDATE '.$this->table.' SET '.$pairs.$filter;
			$this->db->exec($sql,$args);
		}
		if (isset($this->trigger['afterupdate']))
			\Base::instance()->call($this->trigger['afterupdate'],
				[$this,$pkeys]);
		// reset changed flag after calling afterupdate
		foreach ($this->fields as $key=>&$field) {
				$field['changed']=FALSE;
				$field['initial']=$field['value'];
				unset($field);
			}
		return $this;
	}

	/**
	*	Delete current record
	*	@return int
	*	@param $quick bool
	*	@param $filter string|array
	**/
	function erase($filter=NULL,$quick=TRUE) {
		if (isset($filter)) {
			if (!$quick) {
				$out=0;
				foreach ($this->find($filter) as $mapper)
					$out+=$mapper->erase();
				return $out;
			}
			$args=[];
			if (is_array($filter)) {
				$args=isset($filter[1]) && is_array($filter[1])?
					$filter[1]:
					array_slice($filter,1,NULL,TRUE);
				$args=is_array($args)?$args:[1=>$args];
				list($filter)=$filter;
			}
			return $this->db->
				exec('DELETE FROM '.$this->table.
				($filter?' WHERE '.$filter:'').';',$args);
		}
		$args=[];
		$ctr=0;
		$filter='';
		$pkeys=[];
		foreach ($this->fields as $key=>&$field) {
			if ($field['pkey']) {
				$filter.=($filter?' AND ':'').$this->db->quotekey($key).'=?';
				$args[$ctr+1]=[$field['previous'],$field['pdo_type']];
				$pkeys[$key]=$field['previous'];
				$ctr++;
			}
			$field['value']=NULL;
			$field['changed']=(bool)$field['default'];
			if ($field['pkey'])
				$field['previous']=NULL;
			unset($field);
		}
		foreach ($this->adhoc as &$field) {
			$field['value']=NULL;
			unset($field);
		}
		parent::erase();
		if (isset($this->trigger['beforeerase']) &&
			\Base::instance()->call($this->trigger['beforeerase'],
				[$this,$pkeys])===FALSE)
			return 0;
		$out=$this->db->
			exec('DELETE FROM '.$this->table.' WHERE '.$filter.';',$args);
		if (isset($this->trigger['aftererase']))
			\Base::instance()->call($this->trigger['aftererase'],
				[$this,$pkeys]);
		return $out;
	}

	/**
	*	Reset cursor
	*	@return NULL
	**/
	function reset() {
		foreach ($this->fields as &$field) {
			$field['value']=NULL;
			$field['initial']=NULL;
			$field['changed']=FALSE;
			if ($field['pkey'])
				$field['previous']=NULL;
			unset($field);
		}
		foreach ($this->adhoc as &$field) {
			$field['value']=NULL;
			unset($field);
		}
		parent::reset();
	}

	/**
	*	Hydrate mapper object using hive array variable
	*	@return NULL
	*	@param $var array|string
	*	@param $func callback
	**/
	function copyfrom($var,$func=NULL) {
		if (is_string($var))
			$var=\Base::instance()->$var;
		if ($func)
			$var=call_user_func($func,$var);
		foreach ($var as $key=>$val)
			if (in_array($key,array_keys($this->fields)))
				$this->set($key,$val);
	}

	/**
	*	Populate hive array variable with mapper fields
	*	@return NULL
	*	@param $key string
	**/
	function copyto($key) {
		$var=&\Base::instance()->ref($key);
		foreach ($this->fields+$this->adhoc as $key=>$field)
			$var[$key]=$field['value'];
	}

	/**
	*	Return schema and, if the first argument is provided, update it
	*	@return array
	*	@param $fields NULL|array
	**/
	function schema($fields=null) {
		if ($fields)
			$this->fields = $fields;
		return $this->fields;
	}

	/**
	*	Return field names
	*	@return array
	*	@param $adhoc bool
	**/
	function fields($adhoc=TRUE) {
		return array_keys($this->fields+($adhoc?$this->adhoc:[]));
	}

	/**
	*	Return TRUE if field is not nullable
	*	@return bool
	*	@param $field string
	**/
	function required($field) {
		return isset($this->fields[$field]) &&
			!$this->fields[$field]['nullable'];
	}

	/**
	*	Retrieve external iterator for fields
	*	@return object
	**/
	function getiterator() {
		return new \ArrayIterator($this->cast());
	}

	/**
	*	Assign alias for table
	*	@param $alias string
	**/
	function alias($alias) {
		$this->as=$alias;
		return $this;
	}

	/**
	*	Instantiate class
	*	@param $db \DB\SQL
	*	@param $table string
	*	@param $fields array|string
	*	@param $ttl int|array
	**/
	function __construct(\DB\SQL $db,$table,$fields=NULL,$ttl=60) {
		$this->db=$db;
		$this->engine=$db->driver();
		if ($this->engine=='oci')
			$table=strtoupper($table);
		$this->source=$table;
		$this->table=$this->db->quotekey($table);
		$this->fields=$db->schema($table,$fields,$ttl);
		$this->reset();
	}

}
