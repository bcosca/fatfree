<?php

namespace DB\SQL;

//! SQL data mapper
class Mapper extends \DB\Cursor {

	//@{ Error messages
	const
		E_Adhoc='Unable to process ad hoc field %s';
	//@}

	protected
		//! PDO wrapper
		$db,
		//! Database engine
		$engine,
		//! SQL table
		$table,
		//! Last insert ID
		$id,
		//! Defined fields
		$fields,
		//! Adhoc fields
		$adhoc=array();

	/**
		Return TRUE if field is defined
		@return bool
		@param $key string
	**/
	function exists($key) {
		return array_key_exists($key,$this->fields+$this->adhoc);
	}

	/**
		Assign value to field
		@return scalar
		@param $key string
		@param $val scalar
	**/
	function set($key,$val) {
		if (array_key_exists($key,$this->fields)) {
			if (!is_null($val))
				$val=$this->value($this->fields[$key]['pdo_type'],$val);
			if ($this->fields[$key]['value']!==$val ||
				$this->fields[$key]'default']!==$val)
				$this->fields[$key]['changed']=TRUE;
			return $this->fields[$key]['value']=$val;
		}
		// Parenthesize expression in case it's a subquery
		$this->adhoc[$key]=array('expr'=>'('.$val.')','value'=>NULL);
		return $val;
	}

	/**
		Retrieve value of field
		@return scalar
		@param $key string
	**/
	function get($key) {
		if ($key=='_id')
			return $this->id;
		elseif (array_key_exists($key,$this->fields))
			return $this->fields[$key]['value'];
		elseif (array_key_exists($key,$this->adhoc))
			return $this->adhoc[$key]['value'];
		trigger_error(sprintf(self::E_Field,$key));
	}

	/**
		Clear value of field
		@return NULL
		@param $key string
	**/
	function clear($key) {
		if (array_key_exists($key,$this->adhoc))
			unset($this->adhoc[$key]);
	}

	/**
		Get PHP type equivalent of PDO constant
		@return string
		@param $pdo string
	**/
	function type($pdo) {
		switch ($pdo) {
			case \PDO::PARAM_NULL:
				return 'unset';
			case \PDO::PARAM_INT:
				return 'int';
			case \PDO::PARAM_BOOL:
				return 'bool';
			case \PDO::PARAM_STR:
				return 'string';
		}
	}

	/**
		Cast value to PHP type
		@return scalar
		@param $type string
		@param $val scalar
	**/
	function value($type,$val) {
		switch ($type) {
			case \PDO::PARAM_NULL:
				return (unset)$val;
			case \PDO::PARAM_INT:
				return (int)$val;
			case \PDO::PARAM_BOOL:
				return (bool)$val;
			case \PDO::PARAM_STR:
				return (string)$val;
		}
	}

	/**
		Convert array to mapper object
		@return object
		@param $row array
	**/
	protected function factory($row) {
		$mapper=clone($this);
		$mapper->reset();
		foreach ($row as $key=>$val)
			$mapper->{array_key_exists($key,$this->fields)?'fields':'adhoc'}
				[$key]['value']=$val;
		return $mapper;
	}

	/**
		Return fields of mapper object as an associative array
		@return array
		@param $obj object
	**/
	function cast(Mapper $obj=NULL) {
		if (!$obj)
			$obj=$this;
		return array_map(
			function($row) {
				return $row['value' ];
			},
			$obj->fields+$obj->adhoc
		);
	}

	/**
		Build query string and execute
		@return array
		@param $fields string
		@param $filter string|array
		@param $options array
	**/
	function select($fields,$filter=NULL,array $options=NULL) {
		if (!$options)
			$options=array();
		$options+=array(
			'group'=>NULL,
			'order'=>NULL,
			'offset'=>0,
			'limit'=>0
		);
		$sql='SELECT '.$fields.' FROM '.$this->table;
		$args=array();
		if ($filter) {
			if (is_array($filter)) {
				$params=isset($filter[1]) && is_array($filter[1])?
					$filter[1]:
					array_slice($filter,1,NULL,TRUE);
				list($filter)=$filter;
				$args+=is_array($params)?$params:array(1=>$params);
			}
			$sql.=' WHERE '.$filter;
		}
		if ($options['group']) {
			if (is_array($options['group']))
				list($options['group'],$params)=$options['group'];
			$args+=is_array($params)?$params:array($params);
			$sql.=' GROUP BY '.$options['group'];
		}
		if ($options['order'])
			$sql.=' ORDER BY '.$options['order'];
		if ($options['offset'])
			$sql.=' OFFSET '.$options['offset'];
		if ($options['limit'])
			$sql.=' LIMIT '.$options['limit'];
		$result=$this->db->exec($sql.';',$args);
		$out=array();
		foreach ($result as &$row) {
			foreach ($row as $field=>&$val) {
				if (array_key_exists($field,$this->fields))
					$val=$this->value($this->fields[$field]['pdo_type'],$val);
				elseif (array_key_exists($field,$this->adhoc))
					$this->adhoc[$field]['value']=$val;
				unset($val);
			}
			$out[]=$this->factory($row);
			unset($row);
		}
		return $out;
	}

	/**
		Return records that match criteria
		@return array
		@param $filter string|array
		@param $options array
	**/
	function find($filter=NULL,array $options=NULL) {
		if (!$options)
			$options=array();
		$options+=array(
			'group'=>NULL,
			'order'=>NULL,
			'offset'=>0,
			'limit'=>0
		);
		$adhoc='';
		foreach ($this->adhoc as $key=>$field)
			$adhoc.=','.$field['expr'].' AS '.$key;
		return $this->select('*'.$adhoc,$filter,$options);
	}

	/**
		Count records that match criteria
		@return int
		@param $filter string|array
	**/
	function count($filter=NULL) {
		list($out)=$this->select('COUNT(*) AS rows',$filter);
		return $out->adhoc['rows']['value'];
	}

	/**
		Return record at specified offset using same criteria as
		previous load() call and make it active
		@return array
		@param $ofs int
	**/
	function skip($ofs=1) {
		if ($out=parent::skip($ofs)) {
			foreach ($this->fields as $key=>&$field) {
				$field['value']=$out->fields[$key]['value'];
				$field['changed']=FALSE;
				if ($field['pkey'])
					$field['previous']=$out->fields[$key]['value'];
				unset($field);
			}
			foreach ($this->adhoc as $key=>&$field) {
				$field['value']=$out->adhoc[$key]['value'];
				unset($field);
			}
		}
		return $out;
	}

	/**
		Insert new record
		@return array
	**/
	function insert() {
		$args=array();
		$ctr=0;
		$fields='';
		$values='';
		foreach ($this->fields as $key=>$field)
			if ($field['changed']) {
				$fields.=($ctr?',':'').
					($this->engine=='mysql'?('`'.$key.'`'):$key);
				$values.=($ctr?',':'').'?';
				$args[$ctr+1]=array($field['value'],$field['pdo_type']);
				$ctr++;
			}
		if ($fields)
			$this->db->exec(
				'INSERT INTO '.$this->table.' ('.$fields.') '.
				'VALUES ('.$values.');',$args
			);
		$out=array();
		$inc=array();
		foreach ($this->fields as $key=>$field) {
			$out+=array($key=>$field['value']);
			if ($field['pkey']) {
				$field['previous']=$field['value'];
				if ($field['pdo_type']==\PDO::PARAM_INT &&
					!$field['nullable'] && is_null($field['value']))
					$inc[]=$key;
			}
		}
		parent::reset();
		$ctr=count($inc);
		if ($ctr>1)
			return $out;
		if ($ctr) {
			// Reload to obtain default and auto-increment field values
			$seq=NULL;
			if ($this->engine=='pgsql') {
				$pkeys=array_keys($this->pkeys);
				$seq=$this->table.'_'.end($pkeys).'_seq';
			}
			return $this->load(
				array($inc[0].'=?',$this->value(
					$this->fields[$inc[0]]['pdo_type'],
					$this->id=$this->db->lastinsertid($seq))));
		}
	}

	/**
		Update current record
		@return array
	**/
	function update() {
		$args=array();
		$ctr=0;
		$pairs='';
		$filter='';
		foreach ($this->fields as $key=>$field)
			if ($field['changed']) {
				$pairs.=($pairs?',':'').
					($this->engine=='mysql'?('`'.$key.'`'):$key).'=?';
				$args[$ctr+1]=array($field['value'],$field['pdo_type']);
				$ctr++;
			}
		foreach ($this->fields as $key=>$field)
			if ($field['pkey']) {
				$filter.=($filter?' AND ':'').$key.'=?';
				$args[$ctr+1]=array($field['previous'],$field['pdo_type']);
				$ctr++;
			}
		if ($pairs) {
			$sql='UPDATE '.$this->table.' SET '.$pairs;
			if ($filter)
				$sql.=' WHERE '.$filter;
			return $this->db->exec($sql.';',$args);
		}
	}

	/**
		Delete current record
		@return int
		@param $filter string|array
	**/
	function erase($filter=NULL) {
		if ($filter) {
			if (is_array($filter)) {
				$params=isset($filter[1]) && is_array($filter[1])?
					$filter[1]:
					array_slice($filter,1,NULL,TRUE);
				list($filter)=$filter;
			}
			$args=is_array($params)?$params:array(1=>$params);
			return $this->db->
				exec('DELETE FROM '.$this->table.' WHERE '.$filter.';',$args);
		}
		$args=array();
		$ctr=0;
		$filter='';
		foreach ($this->fields as $key=>&$field) {
			if ($field['pkey']) {
				$filter.=($filter?' AND ':'').$key.'=?';
				$args[$ctr+1]=array($field['previous'],$field['pdo_type']);
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
		$this->skip(0);
		return $this->db->
			exec('DELETE FROM '.$this->table.' WHERE '.$filter.';',$args);
	}

	/**
		Reset cursor
		@return NULL
	**/
	function reset() {
		foreach ($this->fields as &$field) {
			$field['value']=NULL;
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
		Hydrate mapper object using hive array variable
		@return NULL
		@param $key string
	**/
	function copyfrom($key) {
		foreach (\Base::instance()->get($key) as $key=>$val)
			if (in_array($key,array_keys($this->fields))) {
				$field=&$this->fields[$key];
				if ($field['value']!==$val) {
					$field['value']=$val;
					$field['changed']=TRUE;
				}
				unset($field);
			}
	}

	/**
		Populate hive array variable with mapper fields
		@return NULL
		@param $key string
	**/
	function copyto($key) {
		$var=&\Base::instance()->ref($key);
		foreach ($this->fields as $key=>$field)
			$var[$key]=$field['value'];
	}

	/**
		Instantiate class
		@param $db object
		@param $table string
		@param $ttl int
	**/
	function __construct(\DB\SQL $db,$table,$ttl=60) {
		$this->db=$db;
		$this->engine=$db->driver();
		$this->table=$table;
		$this->fields=$db->schema($table,$ttl);
		$this->reset();
	}

}

//! Custom session handler
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
		$this->load(array('session_id=?',$id));
		return $this->dry()?FALSE:$this->get('data');
	}

	/**
		Write session data
		@return TRUE
		@param $id string
		@param $data string
	**/
	function write($id,$data) {
		$this->load(array('session_id=?',$id));
		$this->set('session_id',$id);
		$this->set('data',$data);
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
		$this->erase(array('session_id=?',$id));
		return TRUE;
	}

	/**
		Garbage collector
		@return TRUE
		@param $max int
	**/
	function cleanup($max) {
		$this->erase('stamp+'.$max.'<'.time());
		return TRUE;
	}

	/**
		Instantiate class
		@param $db object
		@param $table string
	**/
	function __construct(\DB\SQL $db,$table='sessions') {
		$db->exec(
			'CREATE TABLE IF NOT EXISTS '.
				(($name=$db->name())?($name.'.'):'').$table.' ('.
				'session_id VARCHAR(40),'.
				'data TEXT,'.
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
	}

	//! Wrap-up
	function __destruct() {
		session_commit();
	}

}
