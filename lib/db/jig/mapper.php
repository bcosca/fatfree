<?php

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
		trigger_error(sprintf(self::E_Field,$key));
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
	function cast(Mapper $obj=NULL) {
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
	**/
	function find($filter=NULL,array $options=NULL) {
		if (!$options)
			$options=array();
		$options+=array(
			'order'=>NULL,
			'offset'=>0,
			'limit'=>0
		);
		$fw=\Base::instance();
		$db=$this->db;
		$data=$db->read($this->file);
		if ($filter) {
			if (!is_array($filter))
				return FALSE;
			// Prefix variables to prevent conflict with user code
			$_self=$this;
			$_args=array();
			$params=isset($filter[1]) && is_array($filter[1])?
				$filter[1]:
				array_slice($filter,1,NULL,TRUE);
			list($filter)=$filter;
			$_args+=is_array($params)?$params:array(1=>$params);
			$_expr=$filter;
			$data=array_filter($data,
				function($_) use($_expr,$_args,$_self) {
					extract($_);
					$_ctr=0;
					// Evaluate user code
					return eval('return '.
						preg_replace_callback(
							'/(\:\w+)|(\?)/',
							function($token) use($_args,$_self,$_ctr) {
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
										addslashes($_args[$key]):
										$args[$key]);
							},
							$_self->token($_expr)
						).';'
					);
				}
			);
		}
		if (isset($options['order']))
			foreach (array_reverse($fw->split($options['order'])) as $col) {
				$parts=explode(' ',$col);
				$order=isset($parts[1])?constant($parts[1]):SORT_ASC;
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
		return $out;
	}

	/**
		Count records that match criteria
		@return int
		@param $filter array
	**/
	function count($filter=NULL) {
		$db=$this->db;
		return count($filter?$this->find($filter):$db->read($this->file));
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
		$fw=\Base::instance();
		$db=$this->db;
		while (($id=dechex(microtime(TRUE)*1000)) &&
			($data=$db->read($this->file)) && isset($data[$id]))
			usleep(mt_rand(0,100));
		$this->id=$id;
		$data[$id]=$this->document;
		$db->write($this->file,$data);
		parent::reset();
		return $this->document;
	}

	/**
		Update current record
		@return array
	**/
	function update() {
		$fw=\Base::instance();
		$db=$this->db;
		$data=$db->read($this->file);
		$data[$this->id]=$this->document;
		$db->write($this->file,$data);
		return $this->document;
	}

	/**
		Delete current record
		@return bool
		@param $filter array
	**/
	function erase($filter=NULL) {
		$fw=\Base::instance();
		$db=$this->db;
		$data=$db->read($this->file);
		if ($filter) {
			$data=$this->find($filter);
			foreach (array_reverse($data) as $id=>$doc)
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
		return TRUE;
	}

	/**
		Reset cursor
		@return NULL
	**/
	function reset() {
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
		$this->load(array('@session_id==?',$id));
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
	}

	//! Wrap-up
	function __destruct() {
		session_commit();
	}

}
