<?php

/**
	PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Base
		@version 2.0.9
**/

//! Base structure
class Base {

	//@{ Framework details
	const
		TEXT_AppName='Fat-Free Framework',
		TEXT_Version='2.0.9',
		TEXT_AppURL='http://fatfree.sourceforge.net';
	//@}

	//@{ Locale-specific error/exception messages
	const
		TEXT_Illegal='%s is not a valid framework variable name',
		TEXT_Config='The configuration file %s was not found',
		TEXT_Section='%s is not a valid section',
		TEXT_MSet='Invalid multi-variable assignment',
		TEXT_NotArray='%s is not an array',
		TEXT_PHPExt='PHP extension %s is not enabled',
		TEXT_Apache='Apache mod_rewrite module is not enabled',
		TEXT_Object='%s cannot be used in object context',
		TEXT_Class='Undefined class %s',
		TEXT_Method='Undefined method %s',
		TEXT_NotFound='The URL %s was not found',
		TEXT_NotAllowed='%s request is not allowed for the URL %s',
		TEXT_NoRoutes='No routes specified',
		TEXT_HTTP='HTTP status code %s is invalid',
		TEXT_Render='Unable to render %s - file does not exist',
		TEXT_Form='The input handler for %s is invalid',
		TEXT_Static='%s must be a static method',
		TEXT_Fatal='Fatal error: %s',
		TEXT_Write='%s must have write permission on %s',
		TEXT_Tags='PHP short tags are not supported by this server';
	//@}

	//@{ HTTP status codes (RFC 2616)
	const
		HTTP_100='Continue',
		HTTP_101='Switching Protocols',
		HTTP_200='OK',
		HTTP_201='Created',
		HTTP_202='Accepted',
		HTTP_203='Non-Authorative Information',
		HTTP_204='No Content',
		HTTP_205='Reset Content',
		HTTP_206='Partial Content',
		HTTP_300='Multiple Choices',
		HTTP_301='Moved Permanently',
		HTTP_302='Found',
		HTTP_303='See Other',
		HTTP_304='Not Modified',
		HTTP_305='Use Proxy',
		HTTP_307='Temporary Redirect',
		HTTP_400='Bad Request',
		HTTP_401='Unauthorized',
		HTTP_402='Payment Required',
		HTTP_403='Forbidden',
		HTTP_404='Not Found',
		HTTP_405='Method Not Allowed',
		HTTP_406='Not Acceptable',
		HTTP_407='Proxy Authentication Required',
		HTTP_408='Request Timeout',
		HTTP_409='Conflict',
		HTTP_410='Gone',
		HTTP_411='Length Required',
		HTTP_412='Precondition Failed',
		HTTP_413='Request Entity Too Large',
		HTTP_414='Request-URI Too Long',
		HTTP_415='Unsupported Media Type',
		HTTP_416='Requested Range Not Satisfiable',
		HTTP_417='Expectation Failed',
		HTTP_500='Internal Server Error',
		HTTP_501='Not Implemented',
		HTTP_502='Bad Gateway',
		HTTP_503='Service Unavailable',
		HTTP_504='Gateway Timeout',
		HTTP_505='HTTP Version Not Supported';
	//@}

	//@{ HTTP headers (RFC 2616)
	const
		HTTP_AcceptEnc='Accept-Encoding',
		HTTP_Agent='User-Agent',
		HTTP_Allow='Allow',
		HTTP_Cache='Cache-Control',
		HTTP_Connect='Connection',
		HTTP_Content='Content-Type',
		HTTP_Disposition='Content-Disposition',
		HTTP_Encoding='Content-Encoding',
		HTTP_Expires='Expires',
		HTTP_Host='Host',
		HTTP_IfMod='If-Modified-Since',
		HTTP_Keep='Keep-Alive',
		HTTP_LastMod='Last-Modified',
		HTTP_Length='Content-Length',
		HTTP_Location='Location',
		HTTP_Partial='Accept-Ranges',
		HTTP_Powered='X-Powered-By',
		HTTP_Pragma='Pragma',
		HTTP_Referer='Referer',
		HTTP_Transfer='Content-Transfer-Encoding',
		HTTP_WebAuth='WWW-Authenticate';
	//@}

	const
		//! Framework-mapped PHP globals
		PHP_Globals='GET|POST|COOKIE|REQUEST|SESSION|FILES|SERVER|ENV',
		//! HTTP methods for RESTful interface
		HTTP_Methods='GET|HEAD|POST|PUT|DELETE|OPTIONS';

	//@{ Global variables and references to constants
	protected static
		$vars,
		$null=NULL,
		$true=TRUE,
		$false=FALSE;
	//@}

	private static
		//! Read-only framework variables
		$readonly='BASE|PROTOCOL|ROUTES|STATS|VERSION';

	/**
		Convert Windows double-backslashes to slashes; Also for
		referencing namespaced classes in subdirectories
			@return string
			@param $str string
			@public
	**/
	static function fixslashes($str) {
		return $str?strtr($str,'\\','/'):$str;
	}

	/**
		Convert PHP expression/value to compressed exportable string
			@return string
			@param $arg mixed
			@public
	**/
	static function stringify($arg) {
		switch (gettype($arg)) {
			case 'array':
				$str='';
				foreach ($arg as $key=>$val)
					$str.=($str?',':'').
						self::stringify($key).'=>'.self::stringify($val);
				return 'array('.$str.')';
			case 'object':
				return '\'object:'.get_class($arg).'\'';
			case 'string':
				return '"'.addcslashes($arg,'"').'"';
			case 'boolean':
				return $arg?'TRUE':'FALSE';
			case 'NULL':
				return 'NULL';
		}
		return (string)$arg;
	}

	/**
		Flatten array values and return as CSV string
			@return string
			@param $args mixed
			@public
	**/
	static function csv($args) {
		return implode(',',array_map('self::stringify',$args));
	}

	/**
		Split pipe-, semi-colon, comma-separated string
			@return array
			@param $str string
			@public
	**/
	static function split($str) {
		return array_map('trim',
			preg_split('/[|;,]/',$str,0,PREG_SPLIT_NO_EMPTY));
	}

	/**
		Generate Base36/CRC32 hash code
			@return string
			@param $str string
			@public
	**/
	static function hash($str) {
		return str_pad(base_convert(
			sprintf('%u',crc32($str)),10,36),7,'0',STR_PAD_LEFT);
	}

	/**
		Convert hexadecimal to binary-packed data
			@return string
			@param $hex string
			@public
	**/
	static function hexbin($hex) {
		return pack('H*',$hex);
	}

	/**
		Convert binary-packed data to hexadecimal
			@return string
			@param $bin string
			@public
	**/
	static function binhex($bin) {
		return implode('',unpack('H*',$bin));
	}

	/**
		Returns -1 if the specified number is negative, 0 if zero, or 1 if
		the number is positive
			@return int
			@param $num mixed
			@public
	**/
	static function sign($num) {
		return $num?$num/abs($num):0;
	}

	/**
		Convert engineering-notated string to bytes
			@return int
			@param $str string
			@public
	**/
	static function bytes($str) {
		$greek='KMGT';
		$exp=strpbrk($str,$greek);
		return pow(1024,strpos($greek,$exp)+1)*(int)$str;
	}

	/**
		Convert from JS dot notation to PHP array notation
			@return string
			@param $key string
			@public
	**/
	static function remix($key) {
		$out='';
		$obj=FALSE;
		foreach (preg_split('/\[\s*[\'"]?|[\'"]?\s*\]|\.|(->)/',
			$key,NULL,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE) as $fix) {
			if ($out) {
				if ($fix=='->') {
					$obj=TRUE;
					continue;
				}
				elseif ($obj) {
					$obj=FALSE;
					$fix='->'.$fix;
				}
				else
					$fix='['.var_export($fix,TRUE).']';
			}
			$out.=$fix;
		}
		return $out;
	}

	/**
		Return TRUE if specified string is a valid framework variable name
			@return bool
			@param $key string
			@public
	**/
	static function valid($key) {
		if (preg_match('/^(\w+(?:\[[^\]]+\]|\.\w+|\s*->\s*\w+)*)$/',$key))
			return TRUE;
		// Invalid variable name
		trigger_error(sprintf(self::TEXT_Illegal,var_export($key,TRUE)));
		return FALSE;
	}

	/**
		Get framework variable reference/contents
			@return mixed
			@param $key string
			@param $set bool
			@public
	**/
	static function &ref($key,$set=TRUE) {
		// Traverse array
		$matches=preg_split(
			'/\[\s*[\'"]?|[\'"]?\s*\]|\.|(->)/',self::remix($key),
			NULL,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
		// Referencing a SESSION variable element auto-starts a session
		if ($matches[0]=='SESSION' && !session_id()) {
			// Use cookie jar setup
			call_user_func_array('session_set_cookie_params',
				self::$vars['JAR']);
			session_start();
			// Sync framework and PHP global
			self::$vars['SESSION']=&$_SESSION;
		}
		// Read-only framework variable?
		if ($set && !preg_match('/^('.self::$readonly.')\b/',$matches[0]))
			$var=&self::$vars;
		else
			$var=self::$vars;
		$obj=FALSE;
		foreach ($matches as $match)
			if ($match=='->')
				$obj=TRUE;
			else {
				if (preg_match('/@(\w+)/',$match,$token))
					// Token found
					$match=&self::ref($token[1]);
				if ($set) {
					// Create property/array element if not found
					if ($obj) {
						if (!is_object($var))
							$var=new stdClass;
						if (!isset($var->$match))
							$var->$match=NULL;
						$var=&$var->$match;
						$obj=FALSE;
					}
					else
						$var=&$var[$match];
				}
				elseif ($obj && isset($var->$match)) {
					// Object property found
					$var=$var->$match;
					$obj=FALSE;
				}
				elseif (is_array($var) && isset($var[$match]))
					// Array element found
					$var=$var[$match];
				else
					// Property/array element doesn't exist
					return self::$null;
			}
		if ($set && count($matches)>1 &&
			preg_match('/GET|POST|COOKIE/',$matches[0],$php)) {
			// Sync with REQUEST
			$req=&self::ref(preg_replace('/^'.$php[0].'\b/','REQUEST',$key));
			$req=$var;
		}
		return $var;
	}

	/**
		Copy contents of framework variable to another
			@param $src string
			@param $dst string
			@public
	**/
	static function copy($src,$dst) {
		$ref=&self::ref($dst);
		$ref=self::ref($src);
	}

	/**
		Concatenate string to framework string variable
			@param $var string
			@param $val string
			@public
	**/
	static function concat($var,$val) {
		$ref=&self::ref($var);
		$ref.=$val;
	}

	/**
		Format framework string variable
			@return string
			@public
	**/
	static function sprintf() {
		return call_user_func_array('sprintf',
			array_map('self::resolve',func_get_args()));
	}

	/**
		Add keyed element to the end of framework array variable
			@param $var string
			@param $key string
			@param $val mixed
			@public
	**/
	static function append($var,$key,$val) {
		$ref=&self::ref($var);
		$ref[self::resolve($key)]=$val;
	}

	/**
		Swap keys and values of framework array variable
			@param $var string
			@public
	**/
	static function flip($var) {
		$ref=&self::ref($var);
		$ref=array_combine(array_values($ref),array_keys($ref));
	}

	/**
		Merge one or more framework array variables
			@public
	**/
	static function merge() {
		$args=func_get_args();
		foreach ($args as &$arg) {
			if (is_string($arg))
				$arg=self::ref($arg);
			if (!is_array($arg))
				trigger_error(sprintf(self::TEXT_NotArray,
					self::stringify($arg)));
		}
		call_user_func_array('array_merge',$args);
	}

	/**
		Add element to the end of framework array variable
			@param $var string
			@param $val mixed
			@public
	**/
	static function push($var,$val) {
		$ref=&self::ref($var);
		if (!is_array($ref))
			$ref=array();
		array_push($ref,is_array($val)?
			array_map('self::resolve',$val):
			(is_string($val)?self::resolve($val):$val));
	}

	/**
		Remove last element of framework array variable and
		return the element
			@return mixed
			@param $var string
			@public
	**/
	static function pop($var) {
		$ref=&self::ref($var);
		if (is_array($ref))
			return array_pop($ref);
		trigger_error(sprintf(self::TEXT_NotArray,$var));
		return FALSE;
	}

	/**
		Add element to the beginning of framework array variable
			@param $var string
			@param $val mixed
			@public
	**/
	static function unshift($var,$val) {
		$ref=&self::ref($var);
		if (!is_array($ref))
			$ref=array();
		array_unshift($ref,is_array($val)?
			array_map('self::resolve',$val):
			(is_string($val)?self::resolve($val):$val));
	}

	/**
		Remove first element of framework array variable and
		return the element
			@return mixed
			@param $var string
			@public
	**/
	static function shift($var) {
		$ref=&self::ref($var);
		if (is_array($ref))
			return array_shift($ref);
		trigger_error(sprintf(self::TEXT_NotArray,$var));
		return FALSE;
	}

	/**
		Execute callback as a mutex operation
			@return mixed
			@public
	**/
	static function mutex() {
		$args=func_get_args();
		$func=array_shift($args);
		$handles=array();
		foreach ($args as $file) {
			$lock=$file.'.lock';
			while (TRUE) {
				usleep(mt_rand(0,100));
				if (is_resource($handle=@fopen($lock,'x'))) {
					$handles[$lock]=$handle;
					break;
				}
				if (is_file($lock) &&
					filemtime($lock)+self::$vars['MUTEX']<time())
					unlink($lock);
			}
		}
		$out=$func();
		foreach ($handles as $lock=>$handle) {
			fclose($handle);
			unlink($lock);
		}
		return $out;
	}

	/**
		Lock-aware file reader
			@param $file string
			@public
	**/
	static function getfile($file) {
		$out=FALSE;
		if (!function_exists('flock'))
			$out=self::mutex(
				function() use($file) {
					return file_get_contents($file);
				},
				$file
			);
		elseif ($handle=@fopen($file,'r')) {
			flock($handle,LOCK_EX);
			$size=filesize($file);
			$out=$size?fread($handle,$size):$out;
			flock($handle,LOCK_UN);
			fclose($handle);
		}
		return $out;
	}

	/**
		Lock-aware file writer
			@param $file string
			@param $data string
			@public
	**/
	static function putfile($file,$data) {
		if (!function_exists('flock'))
			$out=self::mutex(
				function() use($file,$data) {
					return file_put_contents($file,$data,LOCK_EX);
				},
				$file
			);
		else
			$out=file_put_contents($file,$data,LOCK_EX);
		return $out;
	}

	/**
		Evaluate template expressions in string
			@return string
			@param $val mixed
			@public
	**/
	static function resolve($val) {
		// Analyze string for correct framework expression syntax
		$self=__CLASS__;
		$str=preg_replace_callback(
			// Expression
			'/{{(.+?)}}/i',
			function($expr) use($self) {
				// Evaluate expression
				$out=preg_replace_callback(
					// Function
					'/(?<!@)\b(\w+)\s*\(([^\)]*)\)/',
					function($func) use($self) {
						return is_callable($ref=$self::ref($func[1],FALSE))?
							// Variable holds an anonymous function
							call_user_func_array($ref,str_getcsv($func[2])):
							// Check if empty array
							($func[1].$func[2]=='array'?'NULL':
								($func[1]=='array'?'\'Array\'':$func[0]));
					},
					preg_replace_callback(
						// Framework variable
						'/(?<!\w)@(\w+(?:\[[^\]]+\]|\.\w+)*'.
						'(?:\s*->\s*\w+)?)\s*(\(([^\)]*)\))?(?:\\\(.+))?/',
						function($var) use($self) {
							// Retrieve variable contents
							$val=$self::ref($var[1],FALSE);
							if (isset($var[2]) && is_callable($val))
								// Anonymous function
								$val=call_user_func_array(
									$val,str_getcsv($var[3]));
							if (isset($var[4]) && class_exists('ICU',FALSE))
								// ICU-formatted string
								$val=call_user_func_array('ICU::format',
									array($val,str_getcsv($var[4])));
							return $self::stringify($val);
						},
						$expr[1]
					)
				);
				return (!preg_match('/@|\bnew\s+/i',$out) &&
					($eval=eval('return (string)'.$out.';'))!==FALSE?
						$eval:$out);
			},
			$val
		);
		return $str;
	}

	/**
		Sniff headers for real IP address
			@return string
			@public
	**/
	static function realip() {
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			// Behind proxy
			return $_SERVER['HTTP_CLIENT_IP'];
		elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			// Use first IP address in list
			list($ip)=explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
			return $ip;
		}
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
		Return TRUE if IP address is local or within a private IPv4 range
			@return bool
			@param $addr string
			@public
	**/
	static function privateip($addr=NULL) {
		if (!$addr)
			$addr=self::realip();
		return preg_match('/^127\.0\.0\.\d{1,3}$/',$addr) ||
			!filter_var($addr,FILTER_VALIDATE_IP,
				FILTER_FLAG_IPV4|FILTER_FLAG_NO_PRIV_RANGE);
	}

	/**
		Clean and repair HTML
			@return string
			@param $html string
			@public
	**/
	static function tidy($html) {
		if (!extension_loaded('tidy'))
			return $html;
		$tidy=new Tidy;
		$tidy->parseString($html,self::$vars['TIDY'],
			str_replace('-','',self::$vars['ENCODING']));
		$tidy->cleanRepair();
		return (string)$tidy;
	}

	/**
		Create folder; Trigger error and return FALSE if script has no
		permission to create folder in the specified path
			@param $name string
			@param $perm int
			@public
	**/
	static function mkdir($name,$perm=0775) {
		$parent=dirname($name);
		if (!@is_writable($parent) && !chmod($parent,775)) {
			$uid=posix_getpwuid(posix_geteuid());
			trigger_error(sprintf(self::TEXT_Write,
				$uid['name'],realpath(dirname($name))));
			return FALSE;
		}
		// Create the folder
		mkdir($name,$perm);
	}

	/**
		Intercept calls to undefined methods
			@param $func string
			@param $args array
			@public
	**/
	function __call($func,array $args) {
		trigger_error(sprintf(self::TEXT_Method,get_called_class().'->'.
			$func.'('.self::csv($args).')'));
	}

	/**
		Intercept calls to undefined static methods
			@param $func string
			@param $args array
			@public
	**/
	static function __callStatic($func,array $args) {
		trigger_error(sprintf(self::TEXT_Method,get_called_class().'::'.
			$func.'('.self::csv($args).')'));
	}

	/**
		Return instance of child class
			@public
	**/
	static function instance() {
		return eval('return new '.get_called_class().
			'('.self::csv(func_get_args()).');');
	}

	/**
		Class constructor
			@public
	**/
	function __construct() {
		// Prohibit use of class as an object
		trigger_error(sprintf(self::TEXT_Object,get_called_class()));
	}

}

//! Main framework code
class F3 extends Base {

	/**
		Bind value to framework variable
			@param $key string
			@param $val mixed
			@param $persist bool
			@param $resolve bool
			@public
	**/
	static function set($key,$val,$persist=FALSE,$resolve=TRUE) {
		if (preg_match('/{{.+}}/',$key))
			// Variable variable
			$key=self::resolve($key);
		if (!self::valid($key))
			return;
		if (preg_match('/COOKIE\b/',$key) && !headers_sent()) {
			// Create/modify cookie
			$matches=preg_split(
				'/\[\s*[\'"]?|[\'"]?\s*\]|\./',self::remix($key),
				NULL,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
			array_shift($matches);
			if ($matches) {
				$var='';
				foreach ($matches as $match)
					if (!$var)
						$var=$match;
					else
						$var.='[\''.$match.'\']';
				$val=array($var=>$val);
			}
			if (is_array($val))
				foreach ($val as $var=>$sub) {
					$func=self::$vars['JAR'];
					array_unshift($func,$var,$sub);
					call_user_func_array('setcookie',$func);
				}
			return;
		}
		$var=&self::ref($key);
		if (is_string($val) && $resolve)
			$val=self::resolve($val);
		elseif (is_array($val)) {
			$var=array();
			// Recursive token substitution
			foreach ($val as $subk=>$subv)
				self::set($key.'['.var_export($subk,TRUE).']',
					$subv,FALSE);
			return;
		}
		$var=$val;
		if (preg_match('/LANGUAGE|LOCALES/',$key) && class_exists('ICU'))
			// Load appropriate dictionaries
			ICU::load();
		// Initialize cache if explicitly defined
		elseif ($key=='CACHE' && $val)
			Cache::prep();
		if ($persist) {
			$hash='var.'.self::hash(self::remix($key));
			Cache::set($hash,$val);
		}
	}

	/**
		Retrieve value of framework variable and apply locale rules
			@return mixed
			@param $key string
			@param $args mixed
			@public
	**/
	static function get($key,$args=NULL) {
		if (preg_match('/{{.+}}/',$key))
			// Variable variable
			$key=self::resolve($key);
		if (!self::valid($key))
			return self::$null;
		$val=self::ref($key,FALSE);
		if (is_string($val))
			return class_exists('ICU',FALSE) && $args?
				ICU::format($val,$args):$val;
		elseif (is_null($val)) {
			// Attempt to retrieve from cache
			$hash='var.'.self::hash(self::remix($key));
			if (Cache::cached($hash))
				$val=Cache::get($hash);
		}
		return $val;
	}

	/**
		Unset framework variable
			@param $key string
			@public
	**/
	static function clear($key) {
		if (preg_match('/{{.+}}/',$key))
			// Variable variable
			$key=self::resolve($key);
		if (!self::valid($key))
			return;
		if (preg_match('/COOKIE/',$key) && !headers_sent()) {
			$val=$_COOKIE;
			$matches=preg_split(
				'/\[\s*[\'"]?|[\'"]?\s*\]|\./',self::remix($key),
				NULL,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
			array_shift($matches);
			if ($matches) {
				// Expire specific cookie
				$var='';
				foreach ($matches as $match)
					if (!$var)
						$var=$match;
					else
						$var.='[\''.$match.'\']';
				$val=array($var,FALSE);
			}
			if (is_array($val))
				// Expire all cookies
				foreach (array_keys($val) as $var) {
					$func=self::$vars['JAR'];
					$func['expire']=strtotime('-1 year');
					array_unshift($func,$var,FALSE);
					call_user_func_array('setcookie',$func);
				}
			return;
		}
		// Clearing SESSION array ends the current session
		if ($key=='SESSION') {
			if (!session_id()) {
				call_user_func_array('session_set_cookie_params',
					self::$vars['JAR']);
				session_start();
			}
			// End the session
			session_unset();
			session_destroy();
		}
		preg_match('/^('.self::PHP_Globals.')(.*)$/',$key,$match);
		if (isset($match[1])) {
			$name=self::remix($key,FALSE);
			eval($match[2]?'unset($_'.$name.');':'$_'.$name.'=NULL;');
		}
		$name=preg_replace('/^(\w+)/','[\'\1\']',self::remix($key));
		// Assign NULL to framework variables; do not unset
		eval(ctype_upper(preg_replace('/^\w+/','\0',$key))?
			'self::$vars'.$name.'=NULL;':'unset(self::$vars'.$name.');');
		// Remove from cache
		$hash='var.'.self::hash(self::remix($key));
		if (Cache::cached($hash))
			Cache::clear($hash);
	}

	/**
		Return TRUE if framework variable has been assigned a value
			@return bool
			@param $key string
			@public
	**/
	static function exists($key) {
		if (preg_match('/{{.+}}/',$key))
			// Variable variable
			$key=self::resolve($key);
		if (!self::valid($key))
			return FALSE;
		$var=&self::ref($key,FALSE);
		return isset($var);
	}

	/**
		Multi-variable assignment using associative array
			@param $arg array
			@param $pfx string
			@public
	**/
	static function mset($arg,$pfx='') {
		if (!is_array($arg))
			// Invalid argument
			trigger_error(self::TEXT_MSet);
		else
			// Bind key-value pairs
			foreach ($arg as $key=>$val)
				self::set($pfx.$key,$val);
	}

	/**
		Determine if framework variable has been cached
			@return mixed
			@param $key string
			@public
	**/
	static function cached($key) {
		if (preg_match('/{{.+}}/',$key))
			// Variable variable
			$key=self::resolve($key);
		return self::valid($key)?
			Cache::cached('var.'.self::hash(self::remix($key))):
			FALSE;
	}

	/**
		Configure framework according to INI-style file settings;
		Cache auto-generated PHP code to speed up execution
			@param $file string
			@public
	**/
	static function config($file) {
		// Generate hash code for config file
		$hash='php.'.self::hash($file);
		$cached=Cache::cached($hash);
		if ($cached && filemtime($file)<$cached)
			// Retrieve from cache
			$save=Cache::get($hash);
		else {
			if (!is_file($file)) {
				// Configuration file not found
				trigger_error(sprintf(self::TEXT_Config,$file));
				return;
			}
			// Load the .ini file
			$cfg=array();
			$sec='';
			if ($ini=file($file))
				foreach ($ini as $line) {
					preg_match('/^\s*(?:(;)|\[(.+)\]|(.+?)\s*=\s*(.+))/',
						$line,$parts);
					if (isset($parts[1]) && $parts[1])
						// Comment
						continue;
					elseif (isset($parts[2]) && $parts[2])
						// Section
						$sec=strtolower($parts[2]);
					elseif (isset($parts[3]) && $parts[3]) {
						// Key-value pair
						$csv=array_map(
							function($val) {
								$val=trim($val);
								return is_numeric($val) || defined($val)?
									eval('return '.$val.';'):$val;
							},
							str_getcsv($parts[4])
						);
						$cfg[$sec=$sec?:'globals'][$parts[3]]=
							count($csv)>1?$csv:$csv[0];
					}
				}
			$plan=array('globals'=>'set','maps'=>'map','routes'=>'route');
			ob_start();
			foreach ($cfg as $sec=>$pairs)
				if (isset($plan[$sec]))
					foreach ($pairs as $key=>$val)
						echo 'self::'.$plan[$sec].'('.
							self::stringify($key).','.
							(is_array($val) && $sec!='globals'?
								self::csv($val):self::stringify($val)).');'.
							"\n";
			$save=ob_get_clean();
			// Compress and save to cache
			Cache::set($hash,$save);
		}
		// Execute cached PHP code
		eval($save);
		if (!is_null(self::$vars['ERROR']))
			// Remove from cache
			Cache::clear($hash);
	}

	/**
		Convert special characters to HTML entities using globally-
		defined character set
			@return string
			@param $str string
			@param $all bool
			@public
	**/
	static function htmlencode($str,$all=FALSE) {
		return call_user_func(
			$all?'htmlentities':'htmlspecialchars',
			$str,ENT_COMPAT,self::$vars['ENCODING'],TRUE);
	}

	/**
		Convert HTML entities back to their equivalent characters
			@return string
			@param $str string
			@param $all bool
			@public
	**/
	static function htmldecode($str,$all=FALSE) {
		return $all?
			html_entity_decode($str,ENT_COMPAT,self::$vars['ENCODING']):
			htmlspecialchars_decode($str,ENT_COMPAT);
	}

	/**
		Send HTTP status header; Return text equivalent of status code
			@return mixed
			@param $code int
			@public
	**/
	static function status($code) {
		if (!defined('self::HTTP_'.$code)) {
			// Invalid status code
			trigger_error(sprintf(self::TEXT_HTTP,$code));
			return FALSE;
		}
		// Get description
		$response=constant('self::HTTP_'.$code);
		// Send raw HTTP header
		if (PHP_SAPI!='cli' && !headers_sent())
			header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$response);
		return $response;
	}

	/**
		Retrieve HTTP headers
			@return array
			@public
	**/
	static function headers() {
		if (PHP_SAPI!='cli') {
			if (function_exists('getallheaders'))
				// Apache server
				return getallheaders();
			// Workaround
			$req=array();
			foreach ($_SERVER as $key=>$val)
				if (substr($key,0,5)=='HTTP_')
					$req[strtr(ucwords(strtolower(
						strtr(substr($key,5),'_',' '))),' ','-')]=$val;
			return $req;
		}
		return array();
	}

	/**
		Send HTTP header with expiration date (seconds from current time)
			@param $secs int
			@public
	**/
	static function expire($secs=0) {
		if (PHP_SAPI!='cli' && !headers_sent()) {
			$time=time();
			$req=self::headers();
			if (isset($req[self::HTTP_IfMod]) &&
				strtotime($req[self::HTTP_IfMod])+$secs>$time) {
				self::status(304);
				die;
			}
			header(self::HTTP_Powered.': '.self::TEXT_AppName.' '.
				'('.self::TEXT_AppURL.')');
			if ($secs) {
				header_remove(self::HTTP_Pragma);
				header(self::HTTP_Expires.': '.gmdate('r',$time+$secs));
				header(self::HTTP_Cache.': max-age='.$secs);
				header(self::HTTP_LastMod.': '.gmdate('r'));
			}
			else {
				header(self::HTTP_Pragma.': no-cache');
				header(self::HTTP_Cache.': no-cache, must-revalidate');
			}
		}
	}

	/**
		Reroute to specified URI
			@param $uri string
			@public
	**/
	static function reroute($uri) {
		$uri=self::resolve($uri);
		if (PHP_SAPI!='cli' && !headers_sent()) {
			// HTTP redirect
			self::status($_SERVER['REQUEST_METHOD']=='GET'?301:303);
			if (session_id())
				session_commit();
			header(self::HTTP_Location.': '.
				(preg_match('/^https?:\/\//',$uri)?
					$uri:(self::$vars['BASE'].$uri)));
			die;
		}
		self::mock('GET '.$uri);
		self::run();
	}

	/**
		Assign handler to route pattern
			@param $pattern string
			@param $funcs mixed
			@param $ttl int
			@param $throttle int
			@param $hotlink bool
			@public
	**/
	static function route($pattern,$funcs,$ttl=0,$throttle=0,$hotlink=TRUE) {
		list($methods,$uri)=
			preg_split('/\s+/',$pattern,2,PREG_SPLIT_NO_EMPTY);
		foreach (self::split($methods) as $method)
			// Use pattern and HTTP methods as route indexes
			self::$vars['ROUTES'][$uri][strtoupper($method)]=
				// Save handler, cache timeout and hotlink permission
				array($funcs,$ttl,$throttle,$hotlink);
	}

	/**
		Provide REST interface by mapping URL to object/class
			@param $url string
			@param $class mixed
			@param $ttl int
			@param $throttle int
			@param $hotlink bool
			@public
	**/
	static function map($url,$class,$ttl=0,$throttle=0,$hotlink=TRUE) {
		foreach (explode('|',self::HTTP_Methods) as $method)
			if (method_exists($class,$method)) {
				$ref=new ReflectionMethod($class,$method);
				self::route($method.' '.$url,$ref->isStatic()?
					array($class,$method):array(new $class,$method),$ttl,
					$throttle,$hotlink);
				unset($ref);
			}
	}

	/**
		Call route handler
			@return mixed
			@param $funcs string
			@param $listen bool
			@public
	**/
	static function call($funcs,$listen=FALSE) {
		$classes=array();
		$funcs=is_string($funcs)?self::split($funcs):array($funcs);
		foreach ($funcs as $func) {
			if (is_string($func)) {
				$func=self::resolve($func);
				if (preg_match('/(.+)\s*(->|::)\s*(.+)/s',$func,$match)) {
					if (!class_exists($match[1]) ||
						!method_exists($match[1],'__call') &&
						!method_exists($match[1],$match[3])) {
						self::error(404);
						return FALSE;
					}
					$func=array($match[2]=='->'?
						new $match[1]:$match[1],$match[3]);
				}
				elseif (!function_exists($func)) {
					if (preg_match('/\.php$/i',$func)) {
						foreach (self::split(self::$vars['IMPORTS'])
							as $path)
							if (is_file($file=$path.$func)) {
								$instance=new F3instance;
								return $instance->sandbox($file);
							}
					}
					self::error(404);
					return FALSE;
				}
			}
			if (!is_callable($func)) {
				self::error(404);
				return FALSE;
			}
			$oop=is_array($func) &&
				(is_object($func[0]) || is_string($func[0]));
			if ($listen && $oop &&
				method_exists($func[0],$before='beforeRoute') &&
				!in_array($func[0],$classes)) {
				// Execute beforeRoute() once per class
				if (call_user_func(array($func[0],$before))===FALSE)
					return FALSE;
				$classes[]=is_object($func[0])?get_class($func[0]):$func[0];
			}
			$out=call_user_func($func);
			if ($listen && $oop &&
				method_exists($func[0],$after='afterRoute') &&
				!in_array($func[0],$classes)) {
				// Execute afterRoute() once per class
				if (call_user_func(array($func[0],$after))===FALSE)
					return FALSE;
				$classes[]=is_object($func[0])?get_class($func[0]):$func[0];
			}
		}
		return $out;
	}

	/**
		Process routes based on incoming URI
			@public
	**/
	static function run() {
		// Validate user against spam blacklists
		if (self::$vars['DNSBL'] && !self::privateip($addr=self::realip()) &&
			(!self::$vars['EXEMPT'] ||
			!in_array($addr,self::split(self::$vars['EXEMPT'])))) {
			// Convert to reverse IP dotted quad
			$quad=implode('.',array_reverse(explode('.',$addr)));
			foreach (self::split(self::$vars['DNSBL']) as $list)
				// Check against DNS blacklist
				if (gethostbyname($quad.'.'.$list)!=$quad.'.'.$list) {
					if (self::$vars['SPAM'])
						// Spammer detected; Send to blackhole
						self::reroute(self::$vars['SPAM']);
					else {
						// Forbidden
						self::error(403);
						die;
					}
				}
		}
		// Process routes
		if (!isset(self::$vars['ROUTES']) || !self::$vars['ROUTES']) {
			trigger_error(self::TEXT_NoRoutes);
			return;
		}
		$found=FALSE;
		// Detailed routes get matched first
		krsort(self::$vars['ROUTES']);
		$time=time();
		$req=preg_replace('/^'.preg_quote(self::$vars['BASE'],'/').
			'\b(.+)/','\1',rawurldecode($_SERVER['REQUEST_URI']));
		foreach (self::$vars['ROUTES'] as $uri=>$route) {
			if (!preg_match('/^'.
				preg_replace(
					'/(?:{{)?@(\w+\b)(?:}})?/',
					// Valid URL characters (RFC 1738)
					'(?P<\1>[\w\-\.!~\*\'"(),\s]+)',
					// Wildcard character in URI
					str_replace('\*','(.*)',preg_quote($uri,'/'))
				).'\/?(?:\?.*)?$/iu',$req,$args))
				continue;
			$wild=is_int(strpos($uri,'/*'));
			// Inspect each defined route
			foreach ($route as $method=>$proc) {
				if (!preg_match('/'.$method.'/',$_SERVER['REQUEST_METHOD']))
					continue;
				$found=TRUE;
				list($funcs,$ttl,$throttle,$hotlink)=$proc;
				if (!$hotlink && isset(self::$vars['HOTLINK']) &&
					isset($_SERVER['HTTP_REFERER']) &&
					parse_url($_SERVER['HTTP_REFERER'],PHP_URL_HOST)!=
						$_SERVER['SERVER_NAME'])
					// Hot link detected; Redirect page
					self::reroute(self::$vars['HOTLINK']);
				if (!$wild)
					// Save named uri captures
					foreach (array_keys($args) as $key)
						// Remove non-zero indexed elements
						if (is_numeric($key) && $key)
							unset($args[$key]);
				self::$vars['PARAMS']=$args;
				// Default: Do not cache
				self::expire(0);
				if ($_SERVER['REQUEST_METHOD']=='GET' && $ttl) {
					$_SERVER['REQUEST_TTL']=$ttl;
					// Get HTTP request headers
					$req=self::headers();
					// Content divider
					$div=chr(0);
					// Get hash code for this Web page
					$hash='url.'.self::hash(
						$_SERVER['REQUEST_METHOD'].' '.
						$_SERVER['REQUEST_URI']
					);
					$cached=Cache::cached($hash);
					$uri='/^'.self::HTTP_Content.':.+/';
					if ($cached && $time-$cached<$ttl) {
						if (!isset($req[self::HTTP_IfMod]) ||
							$cached>strtotime($req[self::HTTP_IfMod])) {
							// Activate cache timer
							self::expire($cached+$ttl-$time);
							// Retrieve from cache
							$buffer=Cache::get($hash);
							$type=strstr($buffer,$div,TRUE);
							if (PHP_SAPI!='cli' && !headers_sent() &&
								preg_match($uri,$type,$match))
								// Cached MIME type
								header($match[0]);
							// Save response
							self::$vars['RESPONSE']=substr(
								strstr($buffer,$div),1);
						}
						else {
							// Client-side cache is still fresh
							self::status(304);
							die;
						}
					}
					else {
						// Activate cache timer
						self::expire($ttl);
						$type='';
						foreach (headers_list() as $hdr)
							if (preg_match($uri,$hdr)) {
								// Add Content-Type header to buffer
								$type=$hdr;
								break;
							}
						// Cache this page
						ob_start();
						self::call($funcs,TRUE);
						self::$vars['RESPONSE']=ob_get_clean();
						if (!self::$vars['ERROR'] &&
							self::$vars['RESPONSE'])
							// Compress and save to cache
							Cache::set($hash,
								$type.$div.self::$vars['RESPONSE']);
					}
				}
				else {
					// Capture output
					ob_start();
					self::$vars['REQBODY']=file_get_contents('php://input');
					self::call($funcs,TRUE);
					self::$vars['RESPONSE']=ob_get_clean();
				}
				$elapsed=time()-$time;
				$throttle=$throttle?:self::$vars['THROTTLE'];
				if ($throttle/1e3>$elapsed)
					// Delay output
					usleep(1e6*($throttle/1e3-$elapsed));
				if (strlen(self::$vars['RESPONSE']) && !self::$vars['QUIET'])
					// Display response
					echo self::$vars['RESPONSE'];
			}
			if ($found)
				// Hail the conquering hero
				return;
			// Method not allowed
			if (PHP_SAPI!='cli' && !headers_sent())
				header(self::HTTP_Allow.': '.
					implode(',',array_keys($route)));
			self::error(405);
			return;
		}
		// No such Web page
		self::error(404);
	}

	/**
		Transmit a file for downloading by HTTP client; If kilobytes per
		second is specified, output is throttled (bandwidth will not be
		controlled by default); Return TRUE if successful, FALSE otherwise;
		Support for partial downloads is indicated by third argument
			@param $file string
			@param $kbps int
			@param $partial
			@public
	**/
	static function send($file,$kbps=0,$partial=TRUE) {
		$file=self::resolve($file);
		if (!is_file($file)) {
			self::error(404);
			return FALSE;
		}
		if (PHP_SAPI!='cli' && !headers_sent()) {
			header(self::HTTP_Content.': application/octet-stream');
			header(self::HTTP_Partial.': '.($partial?'bytes':'none'));
			header(self::HTTP_Length.': '.filesize($file));
		}
		$ctr=1;
		$handle=fopen($file,'r');
		$time=microtime(TRUE);
		while (!feof($handle) && !connection_aborted()) {
			if ($kbps) {
				// Throttle bandwidth
				$ctr++;
				if (($ctr/$kbps)>$elapsed=microtime(TRUE)-$time)
					usleep(1e6*($ctr/$kbps-$elapsed));
			}
			// Send 1KiB and reset timer
			echo fread($handle,1024);
		}
		fclose($handle);
		die;
	}

	/**
		Remove HTML tags (except those enumerated) to protect against
		XSS/code injection attacks
			@return mixed
			@param $input string
			@param $tags string
			@public
	**/
	static function scrub($input,$tags=NULL) {
		if (is_array($input))
			foreach ($input as &$val)
				$val=self::scrub($val,$tags);
		if (is_string($input)) {
			$input=($tags=='*')?
				$input:strip_tags($input,is_string($tags)?
					('<'.implode('><',self::split($tags)).'>'):$tags);
		}
		return $input;
	}

	/**
		Call form field handler
			@param $fields string
			@param $funcs mixed
			@param $tags string
			@param $filter int
			@param $opt array
			@param $assign bool
			@public
	**/
	static function input($fields,$funcs=NULL,
		$tags=NULL,$filter=FILTER_UNSAFE_RAW,$opt=array(),$assign=TRUE) {
		$funcs=is_string($funcs)?self::split($funcs):array($funcs);
		foreach (self::split($fields) as $field) {
			$found=FALSE;
			// Sanitize relevant globals
			foreach (explode('|','GET|POST|REQUEST') as $var)
				if (self::exists($var.'.'.$field)) {
					$key=&self::ref($var.'.'.$field);
					$key=self::scrub($key,$tags);
					$val=filter_var($key,$filter,$opt);
					foreach ($funcs as $func)
						if ($func) {
							if (is_string($func) &&
								preg_match('/([\w\\\]+)\s*->\s*(\w+)/',
									$func,$match))
								// Convert class->method syntax
								$func=array(new $match[1],$match[2]);
							if (!is_callable($func)) {
								// Invalid handler
								trigger_error(
									sprintf(self::TEXT_Form,$field)
								);
								return;
							}
							if (!$found) {
								$out=call_user_func($func,$val,$field);
								if (!$assign)
									return $out;
								if ($out)
									$key=$out;
								$found=TRUE;
							}
							elseif ($assign && $out)
								$key=$val;
						}
				}
			if (!$found) {
				// Invalid handler
				trigger_error(sprintf(self::TEXT_Form,$field));
				return;
			}
		}
	}

	/**
		Render user interface
			@return string
			@param $file string
			@public
	**/
	static function render($file) {
		$file=self::resolve($file);
		foreach (self::split(self::$vars['GUI']) as $gui)
			if (is_file($view=self::fixslashes($gui.$file))) {
				$instance=new F3instance;
				$out=$instance->grab($view);
				return self::$vars['TIDY']?self::tidy($out):$out;
			}
		trigger_error(sprintf(self::TEXT_Render,$view));
	}

	/**
		Return runtime performance analytics
			@return array
			@public
	**/
	static function profile() {
		$stats=&self::$vars['STATS'];
		// Compute elapsed time
		$stats['TIME']['elapsed']=microtime(TRUE)-$stats['TIME']['start'];
		// Compute memory consumption
		$stats['MEMORY']['current']=memory_get_usage();
		$stats['MEMORY']['peak']=memory_get_peak_usage();
		return $stats;
	}

	/**
		Mock environment for command-line use and/or unit testing
			@param $pattern string
			@param $args array
			@public
	**/
	static function mock($pattern,array $args=NULL) {
		list($method,$uri)=explode(' ',$pattern,2);
		$method=strtoupper($method);
		$url=parse_url($uri);
		$query='';
		if ($args)
			$query.=http_build_query($args);
		$query.=isset($url['query'])?(($query?'&':'').$url['query']):'';
		if ($query) {
			parse_str($query,$GLOBALS['_'.$method]);
			parse_str($query,$GLOBALS['_REQUEST']);
		}
		$_SERVER['REQUEST_METHOD']=$method;
		$_SERVER['REQUEST_URI']=self::$vars['BASE'].$url['path'].
			($query?('?'.$query):'');
	}

	/**
		Perform test and append result to TEST global variable
			@return string
			@param $cond bool
			@param $pass string
			@param $fail string
			@public
	**/
	static function expect($cond,$pass=NULL,$fail=NULL) {
		if (is_string($cond))
			$cond=self::resolve($cond);
		$text=$cond?$pass:$fail;
		self::$vars['TEST'][]=array(
			'result'=>(int)(boolean)$cond,
			'text'=>is_string($text)?
				self::resolve($text):var_export($text,TRUE)
		);
		return $text;
	}

	/**
		Display default error page; Use custom page if found
			@param $code int
			@param $str string
			@param $trace array
			@param $fatal bool
			@public
	**/
	static function error($code,$str='',array $trace=NULL,$fatal=FALSE) {
		$prior=self::$vars['ERROR'];
		$out='';
		switch ($code) {
			case 404:
				$str=sprintf(self::TEXT_NotFound,$_SERVER['REQUEST_URI']);
				break;
			case 405:
				$str=sprintf(self::TEXT_NotAllowed,
					$_SERVER['REQUEST_METHOD'],$_SERVER['REQUEST_URI']);
				break;
			default:
				// Generate internal server error if code is zero
				if (!$code)
					$code=500;
				if (!self::$vars['DEBUG'])
					// Disable stack trace
					$trace=NULL;
				elseif ($code==500 && !$trace)
					$trace=debug_backtrace();
				if (is_array($trace)) {
					$line=0;
					$plugins=is_array(
						$plugins=glob(self::$vars['PLUGINS'].'*.php'))?
						array_map('self::fixslashes',$plugins):array();
					// Stringify the stack trace
					ob_start();
					foreach ($trace as $nexus) {
						// Remove stack trace noise
						if (self::$vars['DEBUG']<3 && !$fatal &&
							(!isset($nexus['file']) ||
							self::$vars['DEBUG']<2 &&
							(strrchr(basename($nexus['file']),'.')=='.tmp' ||
							in_array(self::fixslashes(
								$nexus['file']),$plugins)) ||
							isset($nexus['function']) &&
							preg_match('/^(call_user_func(?:_array)?|'.
								'trigger_error|{.+}|'.__FUNCTION__.'|__)/',
									$nexus['function'])))
							continue;
						echo '#'.$line.' '.
							(isset($nexus['line'])?
								(urldecode(self::fixslashes(
									$nexus['file'])).':'.
									$nexus['line'].' '):'').
							(isset($nexus['function'])?
								((isset($nexus['class'])?
									($nexus['class'].$nexus['type']):'').
										$nexus['function'].
								'('.(!preg_match('/{{.+}}/',
									$nexus['function']) &&
									isset($nexus['args'])?
									(self::csv($nexus['args'])):'').')'):'').
								"\n";
						$line++;
					}
					$out=ob_get_clean();
				}
		}
		// Save error details
		self::$vars['ERROR']=array(
			'code'=>$code,
			'title'=>self::status($code),
			'text'=>preg_replace('/\v/','',$str),
			'trace'=>self::$vars['DEBUG']?$out:''
		);
		$error=&self::$vars['ERROR'];
		if (self::$vars['DEBUG']<2 && self::$vars['QUIET'])
			return;
		// Write to server's error log (with complete stack trace)
		error_log($error['text']);
		foreach (explode("\n",$out) as $str)
			if ($str)
				error_log($str);
		if ($prior || self::$vars['QUIET'])
			return;
		foreach (array('title','text','trace') as $sub)
			// Convert to HTML entities for safety
			$error[$sub]=self::htmlencode(rawurldecode($error[$sub]));
		$error['trace']=nl2br($error['trace']);
		$func=self::$vars['ONERROR'];
		if ($func && !$fatal)
			self::call($func,TRUE);
		else
			echo '<html>'.
				'<head>'.
					'<title>'.$error['code'].' '.$error['title'].'</title>'.
				'</head>'.
				'<body>'.
					'<h1>'.$error['title'].'</h1>'.
					'<p><i>'.$error['text'].'</i></p>'.
					'<p>'.$error['trace'].'</p>'.
				'</body>'.
			'</html>';
		if (self::$vars['STRICT'])
			die;
	}

	/**
		Bootstrap code
			@public
	**/
	static function start() {
		// Prohibit multiple calls
		if (self::$vars)
			return;
		// Handle all exceptions/non-fatal errors
		error_reporting(E_ALL|E_STRICT);
		ini_set('display_errors',0);
		ini_set('register_globals',0);
		// Get PHP settings
		$ini=ini_get_all(NULL,FALSE);
		// Intercept errors and send output to browser
		set_error_handler(
			function($errno,$errstr) {
				if (error_reporting()) {
					// Error suppression (@) is not enabled
					$self=__CLASS__;
					$self::error(500,$errstr);
				}
			}
		);
		// Do the same for PHP exceptions
		set_exception_handler(
			function($ex) {
				if (!count($trace=$ex->getTrace())) {
					// Translate exception trace
					list($trace)=debug_backtrace();
					$arg=$trace['args'][0];
					$trace=array(
						array(
							'file'=>$arg->getFile(),
							'line'=>$arg->getLine(),
							'function'=>'{main}',
							'args'=>array()
						)
					);
				}
				$self=__CLASS__;
				$self::error(500,$ex->getMessage(),$trace);
				// PHP aborts at this point
			}
		);
		// Apache mod_rewrite enabled?
		if (function_exists('apache_get_modules') &&
			!in_array('mod_rewrite',apache_get_modules())) {
			trigger_error(self::TEXT_Apache);
			return;
		}
		// Fix Apache's VirtualDocumentRoot limitation
		$_SERVER['DOCUMENT_ROOT']=
			dirname(self::fixslashes($_SERVER['SCRIPT_FILENAME']));
		// Adjust HTTP request time precision
		$_SERVER['REQUEST_TIME']=microtime(TRUE);
		// Hydrate framework variables
		$base=self::fixslashes(
			preg_replace('/\/[^\/]+$/','',$_SERVER['SCRIPT_NAME']));
		$scheme=PHP_SAPI=='cli'?
			NULL:
			isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!='off' ||
			isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
			$_SERVER['HTTP_X_FORWARDED_PROTO']=='https'?'https':'http';
		$jar=array(
			'expire'=>0,
			'path'=>$base?:'/',
			'domain'=>'.'.$_SERVER['SERVER_NAME'],
			'secure'=>($scheme=='https'),
			'httponly'=>TRUE
		);
		self::$vars=array(
			// Autoload folders
			'AUTOLOAD'=>'./',
			// Web root folder
			'BASE'=>$base,
			// Cache backend to use (autodetect if true; disable if false)
			'CACHE'=>FALSE,
			// Stack trace verbosity:
			// 0-no stack trace, 1-noise removed, 2-normal, 3-verbose
			'DEBUG'=>1,
			// DNS black lists
			'DNSBL'=>NULL,
			// Document encoding
			'ENCODING'=>'utf-8',
			// Last error
			'ERROR'=>NULL,
			// Allow/prohibit framework class extension
			'EXTEND'=>TRUE,
			// IP addresses exempt from spam detection
			'EXEMPT'=>NULL,
			// User interface folders
			'GUI'=>'./',
			// URL for hotlink redirection
			'HOTLINK'=>NULL,
			// Include path for procedural code
			'IMPORTS'=>'./',
			// Default cookie settings
			'JAR'=>$jar,
			// Default language (auto-detect if null)
			'LANGUAGE'=>NULL,
			// Autoloaded classes
			'LOADED'=>NULL,
			// Dictionary folder
			'LOCALES'=>'./',
			// Maximum POST size
			'MAXSIZE'=>self::bytes($ini['post_max_size']),
			// Max mutex lock duration
			'MUTEX'=>60,
			// Custom error handler
			'ONERROR'=>NULL,
			// Plugins folder
			'PLUGINS'=>self::fixslashes(__DIR__).'/',
			// Scheme/protocol
			'PROTOCOL'=>$scheme,
			// Allow framework to proxy for plugins
			'PROXY'=>FALSE,
			// Stream handle for HTTP PUT method
			'PUT'=>NULL,
			// Output suppression switch
			'QUIET'=>FALSE,
			// Absolute path to document root folder
			'ROOT'=>$_SERVER['DOCUMENT_ROOT'].'/',
			// Framework routes
			'ROUTES'=>NULL,
			// URL for spam redirection
			'SPAM'=>NULL,
			// Stop script on error?
			'STRICT'=>TRUE,
			// Profiler statistics
			'STATS'=>array(
				'MEMORY'=>array('start'=>memory_get_usage()),
				'TIME'=>array('start'=>microtime(TRUE))
			),
			// Temporary folder
			'TEMP'=>'temp/',
			// Minimum script execution time
			'THROTTLE'=>0,
			// Tidy options
			'TIDY'=>array(),
			// Framework version
			'VERSION'=>self::TEXT_AppName.' '.self::TEXT_Version,
			// Default whois server
			'WHOIS'=>'whois.internic.net'
		);
		// Alias the GUI variable (2.0+)
		self::$vars['UI']=&self::$vars['GUI'];
		// Create convenience containers for PHP globals
		foreach (explode('|',self::PHP_Globals) as $var) {
			// Sync framework and PHP globals
			self::$vars[$var]=&$GLOBALS['_'.$var];
			if (isset($ini['magic_quotes_gpc']) &&
				$ini['magic_quotes_gpc'] && preg_match('/^[GPCR]/',$var))
				// Corrective action on PHP magic quotes
				array_walk_recursive(
					self::$vars[$var],
					function(&$val) {
						$val=stripslashes($val);
					}
				);
		}
		if (PHP_SAPI=='cli') {
			// Command line: Parse GET variables in URL, if any
			if (isset($_SERVER['argc']) && $_SERVER['argc']<2)
				array_push($_SERVER['argv'],'/');
			// Detect host name from environment
			$_SERVER['SERVER_NAME']=gethostname();
			// Convert URI to human-readable string
			self::mock('GET '.$_SERVER['argv'][1]);
		}
		// Initialize autoload stack and shutdown sequence
		spl_autoload_register(__CLASS__.'::autoload');
		register_shutdown_function(__CLASS__.'::stop');
	}

	/**
		Execute shutdown function
			@public
	**/
	static function stop() {
		chdir(self::$vars['ROOT']);
		$error=error_get_last();
		if ($error && !self::$vars['QUIET'] && in_array($error['type'],
			array(E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR)))
			// Intercept fatal error
			self::error(500,sprintf(self::TEXT_Fatal,$error['message']),
				array($error),TRUE);
		if (isset(self::$vars['UNLOAD']) &&
			is_callable(self::$vars['UNLOAD']))
			self::call(self::$vars['UNLOAD']);
	}

	/**
		onLoad event handler (static class initializer)
			@public
	**/
	static function loadstatic($class) {
		$loaded=&self::$vars['LOADED'];
		$lower=strtolower($class);
		if (!isset($loaded[$lower])) {
			$loaded[$lower]=
				array_map('strtolower',get_class_methods($class));
			if (in_array('onload',$loaded[$lower])) {
				// Execute onload method
				$method=new ReflectionMethod($class,'onload');
				if ($method->isStatic())
					call_user_func(array($class,'onload'));
				else
					trigger_error(sprintf(self::TEXT_Static,
						$class.'::onload'));
			}
		}
	}

	/**
		Intercept instantiation of objects in undefined classes
			@param $class string
			@public
	**/
	static function autoload($class) {
		foreach (self::split(
			self::$vars['PLUGINS'].';'.self::$vars['AUTOLOAD']) as $auto) {
			$ns='';
			$iter=ltrim($class,'\\');
			for (;;) {
				if ($glob=glob($auto.self::fixslashes($ns).'*')) {
					if ($grep=preg_grep('/^'.preg_quote($auto,'/').
						implode('[\/_]',explode('\\',$ns.$iter)).
						'(?:\.class)?\.php/i',$glob)) {
						$instance=new F3instance;
						$instance->sandbox(current($grep));
						// Verify that the class was loaded
						if (class_exists($class,FALSE)) {
							// Run onLoad event handler if defined
							self::loadstatic($class);
							return;
						}
						elseif (interface_exists($class,FALSE))
							return;
					}
					$parts=explode('\\',$iter,2);
					if (count($parts)>1) {
						$iter=$parts[1];
						if ($grep=preg_grep('/^'.
							preg_quote($auto.self::fixslashes($ns).
							$parts[0],'/').'$/i',$glob)) {
							$ns=str_replace('/','\\',preg_replace('/^'.
								preg_quote($auto,'/').'/','',
								current($grep))).'\\';
							continue;
						}
						$ns.=$parts[0].'\\';
					}
				}
				break;
			}
		}
		if (count(spl_autoload_functions())==1)
			// No other registered autoload functions exist
			trigger_error(sprintf(self::TEXT_Class,$class));
	}

	/**
		Intercept calls to undefined static methods and proxy for the
		called class if found in the plugins folder
			@return mixed
			@param $func string
			@param $args array
			@public
	**/
	static function __callStatic($func,array $args) {
		if (self::$vars['PROXY'] &&
			$glob=glob(self::fixslashes(
				self::$vars['PLUGINS'].'/*.php',GLOB_NOSORT)))
			foreach ($glob as $file) {
				$class=strstr(basename($file),'.php',TRUE);
				// Prevent recursive calls
				$found=FALSE;
				foreach (debug_backtrace() as $trace)
					if (isset($trace['class']) &&
						// Support namespaces
						preg_match('/\b'.preg_quote($trace['class']).'\b/i',
						strtolower($class)) &&
						preg_match('/'.$trace['function'].'/i',
						strtolower($func))) {
						$found=TRUE;
						break;
					}
				if ($found)
					continue;
				// Run onLoad event handler if defined
				self::loadstatic($class);
				if (in_array($func,self::$vars['LOADED'][$class]))
					// Proxy for plugin
					return call_user_func_array(array($class,$func),$args);
			}
		if (count(spl_autoload_functions())==1)
			// No other registered autoload functions exist
			trigger_error(sprintf(self::TEXT_Method,$func));
		return FALSE;
	}

}

//! Cache engine
class Cache extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_Backend='Cache back-end is invalid',
		TEXT_Store='Unable to save %s to cache',
		TEXT_Fetch='Unable to retrieve %s from cache',
		TEXT_Clear='Unable to clear %s from cache';
	//@}

	static
		//! Level-1 cached object
		$buffer,
		//! Cache back-end
		$backend;

	/**
		Auto-detect extensions usable as cache back-ends; MemCache must be
		explicitly activated to work properly; Fall back to file system if
		none declared or detected
			@public
	**/
	static function detect() {
		$ref=array_merge(array_intersect(array('apc','xcache'),
			array_map('strtolower',get_loaded_extensions())),array());
		self::$vars['CACHE']=array_shift($ref)?:
			('folder='.self::$vars['ROOT'].'cache/');
	}

	/**
		Initialize cache backend
			@return bool
			@public
	**/
	static function prep() {
		if (!self::$vars['CACHE'])
			return TRUE;
		if (is_bool(self::$vars['CACHE']))
			// Auto-detect backend
			self::detect();
		if (preg_match(
			'/^(apc)|(memcache)=(.+)|(xcache)|(folder)=(.+\/)/i',
			self::$vars['CACHE'],$match)) {
			if (isset($match[5]) && $match[5]) {
				if (!is_dir($match[6]))
					self::mkdir($match[6]);
				// File system
				self::$backend=array('type'=>'folder','id'=>$match[6]);
			}
			else {
				$ext=strtolower($match[1]?:($match[2]?:$match[4]));
				if (!extension_loaded($ext)) {
					trigger_error(sprintf(self::TEXT_PHPExt,$ext));
					return FALSE;
				}
				if (isset($match[2]) && $match[2]) {
					// Open persistent MemCache connection(s)
					$mcache=NULL;
					foreach (self::split($match[3]) as $server) {
						// Hostname:port
						list($host,$port)=explode(':',$server);
						if (is_null($port))
							// Use default port
							$port=11211;
						// Connect to each server
						if (is_null($mcache))
							$mcache=memcache_pconnect($host,$port);
						else
							memcache_add_server($mcache,$host,$port);
					}
					// MemCache
					self::$backend=array('type'=>$ext,'id'=>$mcache);
				}
				else
					// APC and XCache
					self::$backend=array('type'=>$ext);
			}
			self::$buffer=NULL;
			return TRUE;
		}
		// Unknown back-end
		trigger_error(self::TEXT_Backend);
		return FALSE;
	}

	/**
		Store data in framework cache; Return TRUE/FALSE on success/failure
			@return bool
			@param $name string
			@param $data mixed
			@public
	**/
	static function set($name,$data) {
		if (!self::$vars['CACHE'])
			return TRUE;
		if (is_null(self::$backend)) {
			// Auto-detect back-end
			self::detect();
			if (!self::prep())
				return FALSE;
		}
		$key=$_SERVER['SERVER_NAME'].'.'.$name;
		// Serialize data for storage
		$time=time();
		// Add timestamp
		$val=gzdeflate(serialize(array($time,$data)));
		// Instruct back-end to store data
		switch (self::$backend['type']) {
			case 'apc':
				$ok=apc_store($key,$val);
				break;
			case 'memcache':
				$ok=memcache_set(self::$backend['id'],$key,$val);
				break;
			case 'xcache':
				$ok=xcache_set($key,$val);
				break;
			case 'folder':
				$ok=self::putfile(self::$backend['id'].$key,$val);
				break;
		}
		if (is_bool($ok) && !$ok) {
			trigger_error(sprintf(self::TEXT_Store,$name));
			return FALSE;
		}
		// Free up space for level-1 cache
		while (count(self::$buffer) && strlen(serialize($data))+
			strlen(serialize(array_slice(self::$buffer,1)))>
			ini_get('memory_limit')-memory_get_peak_usage())
				self::$buffer=array_slice(self::$buffer,1);
		self::$buffer[$name]=array('data'=>$data,'time'=>$time);
		return TRUE;
	}

	/**
		Retrieve value from framework cache
			@return mixed
			@param $name string
			@param $quiet bool
			@public
	**/
	static function get($name,$quiet=FALSE) {
		if (!self::$vars['CACHE'])
			return FALSE;
		if (is_null(self::$backend)) {
			// Auto-detect back-end
			self::detect();
			if (!self::prep())
				return FALSE;
		}
		$stats=&self::$vars['STATS'];
		if (!isset($stats['CACHE']))
			$stats['CACHE']=array(
				'level-1'=>array('hits'=>0,'misses'=>0),
				'backend'=>array('hits'=>0,'misses'=>0)
			);
		// Check level-1 cache first
		if (isset(self::$buffer) && isset(self::$buffer[$name])) {
			$stats['CACHE']['level-1']['hits']++;
			return self::$buffer[$name]['data'];
		}
		else
			$stats['CACHE']['level-1']['misses']++;
		$key=$_SERVER['SERVER_NAME'].'.'.$name;
		// Instruct back-end to fetch data
		switch (self::$backend['type']) {
			case 'apc':
				$val=apc_fetch($key);
				break;
			case 'memcache':
				$val=memcache_get(self::$backend['id'],$key);
				break;
			case 'xcache':
				$val=xcache_get($key);
				break;
			case 'folder':
				$val=is_file(self::$backend['id'].$key)?
					self::getfile(self::$backend['id'].$key):FALSE;
				break;
		}
		if (is_bool($val)) {
			$stats['CACHE']['backend']['misses']++;
			// No error display if specified
			if (!$quiet)
				trigger_error(sprintf(self::TEXT_Fetch,$name));
			self::$buffer[$name]=NULL;
			return FALSE;
		}
		// Unserialize timestamp and data
		list($time,$data)=unserialize(gzinflate($val));
		$stats['CACHE']['backend']['hits']++;
		// Free up space for level-1 cache
		while (count(self::$buffer) && strlen(serialize($data))+
			strlen(serialize(array_slice(self::$buffer,1)))>
			ini_get('memory_limit')-memory_get_peak_usage())
				self::$buffer=array_slice(self::$buffer,1);
		self::$buffer[$name]=array('data'=>$data,'time'=>$time);
		return $data;
	}

	/**
		Delete variable from framework cache
			@return bool
			@param $name string
			@param $quiet bool
			@public
	**/
	static function clear($name,$quiet=FALSE) {
		if (!self::$vars['CACHE'])
			return TRUE;
		if (is_null(self::$backend)) {
			// Auto-detect back-end
			self::detect();
			if (!self::prep())
				return FALSE;
		}
		$key=$_SERVER['SERVER_NAME'].'.'.$name;
		// Instruct back-end to clear data
		switch (self::$backend['type']) {
			case 'apc':
				$ok=!apc_exists($key) || apc_delete($key);
				break;
			case 'memcache':
				$ok=memcache_delete(self::$backend['id'],$key);
				break;
			case 'xcache':
				$ok=!xcache_isset($key) || xcache_unset($key);
				break;
			case 'folder':
				$ok=!is_file(self::$backend['id'].$key) ||
					@unlink(self::$backend['id'].$key);
				break;
		}
		if (is_bool($ok) && !$ok) {
			if (!$quiet)
				trigger_error(sprintf(self::TEXT_Clear,$name));
			return FALSE;
		}
		// Check level-1 cache first
		if (isset(self::$buffer) && isset(self::$buffer[$name]))
			unset(self::$buffer[$name]);
		return TRUE;
	}

	/**
		Return FALSE if specified variable is not in cache;
		otherwise, return Un*x timestamp
			@return mixed
			@param $name string
			@public
	**/
	static function cached($name) {
		return self::get($name,TRUE)?self::$buffer[$name]['time']:FALSE;
	}

}

//! F3 object mode
class F3instance {

	const
		TEXT_Conflict='%s conflicts with framework method name';

	/**
		Get framework variable reference; Workaround for PHP's
		call_user_func() reference limitation
			@return mixed
			@param $key string
			@param $set bool
			@public
	**/
	function &ref($key,$set=TRUE) {
		return F3::ref($key,$set);
	}

	/*
		Run PHP code in sandbox
			@param $file string
			@public
	*/
	function sandbox($file) {
		return require $file;
	}

	/**
		Grab file contents
			@return mixed
			@param $file string
			@public
	**/
	function grab($file) {
		$file=F3::resolve($file);
		if (!ini_get('short_open_tag')) {
			$text=preg_replace_callback(
				'/<\?(?:\s|\s*(=))(.+?)\?>/s',
				function($tag) {
					return '<?php '.($tag[1]?'echo ':'').trim($tag[2]).' ?>';
				},
				$orig=self::getfile($file)
			);
			if (ini_get('allow_url_fopen') && ini_get('allow_url_include'))
				// Stream wrap
				$file='data:text/plain,'.urlencode($text);
			elseif ($text!=$orig) {
				// Save re-tagged file in temporary folder
				if (!is_dir($ref=F3::ref('TEMP')))
					F3::mkdir($ref);
				$temp=$ref.$_SERVER['SERVER_NAME'].'.tpl.'.F3::hash($file);
				if (!is_file($temp))
					self::mutex(
						function() use($temp,$text) {
							file_put_contents($temp,$text);
						}
					);
				$file=$temp;
			}
		}
		ob_start();
		// Render
		$this->sandbox($file);
		return ob_get_clean();
	}

	/**
		Proxy for framework methods
			@return mixed
			@param $func string
			@param $args array
			@public
	**/
	function __call($func,array $args) {
		return call_user_func_array('F3::'.$func,$args);
	}

	/**
		Class constructor
			@param $boot bool
			@public
	**/
	function __construct($boot=FALSE) {
		if ($boot)
			F3::start();
		// Allow application to override framework methods?
		if (F3::ref('EXTEND'))
			// User assumes risk
			return;
		// Get all framework methods not defined in this class
		$def=array_diff(get_class_methods('F3'),get_class_methods(__CLASS__));
		// Check for conflicts
		$class=new ReflectionClass($this);
		foreach ($class->getMethods() as $func)
			if (in_array($func->name,$def))
				trigger_error(sprintf(self::TEXT_Conflict,
					get_called_class().'->'.$func->name));
	}

}

// Bootstrap
return new F3instance(TRUE);
