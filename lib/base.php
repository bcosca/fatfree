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

//! Base structure
final class Base {

	//@{ Framework details
	const
		PACKAGE='Fat-Free Framework',
		VERSION='3.0.2-Release';
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

	const
		//! Mapped PHP globals
		GLOBALS='GET|POST|COOKIE|REQUEST|SESSION|FILES|SERVER|ENV',
		//! HTTP verbs
		VERBS='GET|HEAD|POST|PUT|PATCH|DELETE|CONNECT',
		//! Default directory permissions
		MODE=0755,
		//! Fallback language
		FALLBACK='en',
		//! Syntax highlighting stylesheet
		CSS='code.css';

	//@{ Error messages
	const
		E_Pattern='Invalid routing pattern: %s',
		E_Fatal='Fatal error: %s',
		E_Open='Unable to open %s',
		E_Routes='No routes specified',
		E_Method='Invalid method %s';
	//@}

	private
		//! Globals
		$hive,
		//! Default settings
		$defaults,
		//! Language lookup sequence
		$languages,
		//! NULL reference
		$null=NULL;

	/**
		Sync PHP global with corresponding hive key
		@return array
		@param $key string
	**/
	function sync($key) {
		return $this->hive[$key]=&$GLOBALS['_'.$key];
	}

	/**
		Return the parts of specified hive key
		@return array
		@param $key string
	**/
	private function cut($key) {
		return preg_split('/\[\s*[\'"]?(.+?)[\'"]?\s*\]|(->)|\./',
			$key,NULL,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
	}

	/**
		Get hive key reference/contents; Add non-existent hive keys,
		array elements, and object properties by default
		@return mixed
		@param $key string
		@param $add bool
	**/
	function &ref($key,$add=TRUE) {
		$parts=$this->cut($key);
		if ($parts[0]=='SESSION') {
			@session_start();
			if (empty($parts[1]))
				session_regenerate_id(TRUE);
			$this->sync('SESSION');
		}
		if ($add)
			$var=&$this->hive;
		else
			$var=$this->hive;
		$obj=FALSE;
		foreach ($parts as $part)
			if ($part=='->')
				$obj=TRUE;
			elseif ($add) {
				if ($obj) {
					if (!is_object($var))
						$var=new stdclass;
					if (isset($var->$part))
						$var->$part=NULL;
					$var=&$var->$part;
					$obj=FALSE;
				}
				else
					$var=&$var[$part];
			}
			elseif ($obj && isset($var->$part)) {
				$var=$var->$part;
				$obj=FALSE;
			}
			elseif (is_array($var) && isset($var[$part]))
				$var=$var[$part];
			else
				return $this->null;
		return $var;
	}

	/**
		Return TRUE if hive key is not empty
		@return bool
		@param $key string
	**/
	function exists($key) {
		$ref=&$this->ref($key,FALSE);
		return isset($ref)?TRUE:Cache::instance()->exists($this->hash($key));
	}


	/**
		Bind value to hive key
		@return mixed
		@param $key string
		@param $val mixed
		@param $ttl int
	**/
	function set($key,$val,$ttl=0) {
		if (preg_match('/^(GET|POST|COOKIE)\b(.+)/',$key,$expr)) {
			$this->set('REQUEST'.$expr[2],$val);
			if ($expr[1]=='COOKIE') {
				$parts=$this->cut($key);
				call_user_func_array('setcookie',
					array($parts[1],$val)+$this->hive['JAR']);
			}
		}
		else switch ($key) {
			case 'CACHE':
				$val=Cache::instance()->load($val);
				break;
			case 'ENCODING':
				$val=ini_set('default_charset',$val);
				break;
			case 'JAR':
				call_user_func_array('session_set_cookie_params',$val);
				break;
			case 'LANGUAGE':
				$val=$this->language($val);
			case 'LOCALES':
				foreach ($this->languages as $language) {
					$base=$val.$language;
					if ((is_file($file=$base.'.php') ||
						is_file($file=strtolower($base).'.php')) &&
						is_array($dict=require($file)))
						$this->mset($dict,NULL,$ttl);
					elseif (is_file($file=$base.'.ini')) {
						preg_match_all(
							'/(?<=^|\n)'.
							'(?:;.*?)|(?:<\?php.+\?>?)|'.
							'(.+?)[[:blank:]]*=[[:blank:]]*'.
							'((?:\\\\[[:blank:]\r]*\n|[^\n])*)'.
							'(?=\n|$)/',
							file_get_contents($file),$matches,PREG_SET_ORDER);
						if ($matches)
							foreach ($matches as $match)
								if (isset($match[1]))
									$this->set($match[1],
										preg_replace(
											'/\\\\[[:blank:]\r]*\n/','',
											$match[2]));
					}
				}
				break;
			case 'TZ':
				date_default_timezone_set($val);
				break;
		}
		$ref=&$this->ref($key);
		$ref=$val;
		if ($ttl)
			// Persist the key-value pair
			Cache::instance()->set($this->hash($key),$val);
		return $ref;
	}

	/**
		Retrieve contents of hive key
		@return mixed
		@param $key string
		@param $args string|array
	**/
	function get($key,$args=NULL) {
		if (is_string($val=$this->ref($key,FALSE)) && $args)
			return $this->format($val,$args);
		if (is_null($val)) {
			// Attempt to retrieve from cache
			if (Cache::instance()->exists($this->hash($key),$data))
				return $data;
		}
		return $val;
	}

	/**
		Unset hive key
		@return NULL
		@param $key string
	**/
	function clear($key) {
		// Normalize array literal
		$cache=Cache::instance();
		$parts=$this->cut($key);
		if ($parts[0]=='CACHE')
			// Clear cache contents
			$cache->reset();
		elseif ($parts[0]=='SESSION') {
			@session_start();
			if (empty($parts[1])) {
				// End session
				session_unset();
				session_destroy();
				unset($_COOKIE[session_name()]);
				header_remove('Set-Cookie');
			}
			$this->sync('SESSION');
		}
		elseif (preg_match('/^(GET|POST|COOKIE)\b(.+)/',$key,$expr)) {
			$this->clear('REQUEST'.$expr[2]);
			if ($expr[1]=='COOKIE') {
				$parts=$this->cut($key);
				$jar=$this->hive['JAR'];
				$jar['expire']=strtotime('-1 year');
				call_user_func_array('setcookie',
					array($parts[1],'')+$jar);
			}
		}
		if (!isset($parts[1]) &&
			array_key_exists($parts[0],$this->defaults))
			// Reset global to default value
			$this->hive[$parts[0]]=$this->defaults[$parts[0]];
		else {
			$out='';
			$obj=FALSE;
			foreach ($parts as $part)
				if ($part=='->')
					$obj=TRUE;
				elseif ($obj) {
					$obj=FALSE;
					$out.='->'.$out;
				}
				else
					$out.='['.$this->stringify($part).']';
			// PHP can't unset a referenced array/object directly
			eval('unset($this->hive'.$out.');');
			if ($cache->exists($hash=$this->hash($key)))
				// Remove from cache
				$cache->clear($hash);
		}
	}

	/**
		Multi-variable assignment using associative array
		@return NULL
		@param $vars array
		@param $prefix string
		@param $ttl int
	**/
	function mset(array $vars,$prefix='',$ttl=0) {
		foreach ($vars as $key=>$val)
			$this->set($prefix.$key,$val,$ttl);
	}

	/**
		Publish hive contents
		@return array
	**/
	function hive() {
		return $this->hive;
	}

	/**
		Copy contents of hive variable to another
		@return mixed
		@param $src string
		@param $dst string
	**/
	function copy($src,$dst) {
		$ref=&$this->ref($dst);
		return $ref=$this->ref($src);
	}

	/**
		Concatenate string to hive string variable
		@return string
		@param $key string
		@param $val string
	**/
	function concat($key,$val) {
		$ref=&$this->ref($key);
		$ref.=$val;
		return $ref;
	}

	/**
		Swap keys and values of hive array variable
		@return array
		@param $key string
		@public
	**/
	function flip($key) {
		$ref=&$this->ref($key);
		return $ref=array_combine(array_values($ref),array_keys($ref));
	}

	/**
		Add element to the end of hive array variable
		@return mixed
		@param $key string
		@param $val mixed
	**/
	function push($key,$val) {
		$ref=&$this->ref($key);
		array_push($ref,$val);
		return $val;
	}

	/**
		Remove last element of hive array variable
		@return mixed
		@param $key string
	**/
	function pop($key) {
		$ref=&$this->ref($key);
		return array_pop($ref);
	}

	/**
		Add element to the beginning of hive array variable
		@return mixed
		@param $key string
		@param $val mixed
	**/
	function unshift($key,$val) {
		$ref=&$this->ref($key);
		array_unshift($ref,$val);
		return $val;
	}

	/**
		Remove first element of hive array variable
		@return mixed
		@param $key string
	**/
	function shift($key) {
		$ref=&$this->ref($key);
		return array_shift($ref);
	}

	/**
		Convert backslashes to slashes
		@return string
		@param $str string
	**/
	function fixslashes($str) {
		return $str?strtr($str,'\\','/'):$str;
	}

	/**
		Split comma-, semi-colon, or pipe-separated string
		@return array
		@param $str string
	**/
	function split($str) {
		return array_map('trim',
			preg_split('/[,;|]/',$str,0,PREG_SPLIT_NO_EMPTY));
	}

	/**
		Convert PHP expression/value to compressed exportable string
		@return string
		@param $arg mixed
	**/
	function stringify($arg) {
		switch (gettype($arg)) {
			case 'object':
				if (method_exists($arg,'__tostring'))
					return stripslashes($arg);
				$str='';
				if ($this->hive['DEBUG']>2)
					foreach ((array)$arg as $key=>$val)
						$str.=($str?',':'').$this->stringify(
							preg_replace('/[\x00].+?[\x00]/','',$key)).'=>'.
							$this->stringify($val);
				return addslashes(get_class($arg)).'::__set_state('.$str.')';
			case 'array':
				$str='';
				foreach ($arg as $key=>$val)
					$str.=($str?',':'').
						$this->stringify($key).'=>'.$this->stringify($val);
				return 'array('.$str.')';
			default:
				return var_export(
					is_string($arg)?addcslashes($arg,'\''):$arg,TRUE);
		}
	}

	/**
		Flatten array values and return as CSV string
		@return string
		@param $args array
	**/
	function csv(array $args) {
		return implode(',',array_map('stripcslashes',
			array_map(array($this,'stringify'),$args)));
	}

	/**
		Convert snakecase string to camelcase
		@return string
		@param $str string
	**/
	function camelcase($str) {
		return preg_replace_callback(
			'/_(\w)/',
			function($match) {
				return strtoupper($match[1]);
			},
			$str
		);
	}

	/**
		Convert camelcase string to snakecase
		@return string
		@param $str string
	**/
	function snakecase($str) {
		return strtolower(preg_replace('/[[:upper:]]/','_\0',$str));
	}

	/**
		Return -1 if specified number is negative, 0 if zero,
		or 1 if the number is positive
		@return int
		@param $num mixed
	**/
	function sign($num) {
		return $num?($num/abs($num)):0;
	}

	/**
		Generate Base36+CRC32 hash code
		@return string
		@param $str
	**/
	function hash($str) {
		return str_pad(base_convert(
			sprintf('%u',crc32($str)),10,36),7,'0',STR_PAD_LEFT);
	}

	/**
		Return Base64-encoded equivalent
		@return string
		@param $data string
		@param $mime string
	**/
	function base64($data,$mime) {
		return 'data:'.$mime.';base64,'.base64_encode($data);
	}

	/**
		Convert special characters to HTML entities
		@return string
		@param $str string
	**/
	function encode($str) {
		return @htmlentities($str,ENT_COMPAT,$this->hive['ENCODING'],FALSE);
	}

	/**
		Convert HTML entities back to characters
		@return string
		@param $str string
	**/
	function decode($str) {
		return html_entity_decode($str,ENT_COMPAT,$this->hive['ENCODING']);
	}

	/**
		Remove HTML tags (except those enumerated) and non-printable
		characters to mitigate XSS/code injection attacks
		@return mixed
		@param $var mixed
		@param $tags string
	**/
	function scrub(&$var,$tags=NULL) {
		if (is_string($var)) {
			if ($tags)
				$tags='<'.implode('><',$this->split($tags)).'>';
			$var=preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/','',
				($tags=='*')?$var:strip_tags($var,$tags));
		}
		elseif (is_array($var))
			foreach ($var as &$val) {
				$this->scrub($val,$tags);
				unset($val);
			}
		return $var;
	}

	/**
		Encode characters to equivalent HTML entities
		@return string
		@param $arg mixed
	**/
	function esc($arg) {
		if (is_string($arg))
			return $this->encode($arg);
		if (is_array($arg))
			foreach ($arg as &$val) {
				$val=$this->esc($val);
				unset($val);
			}
		return $arg;
	}

	/**
		Decode HTML entities to equivalent characters
		@return string
		@param $arg mixed
	**/
	function raw($arg) {
		if (is_string($arg))
			return $this->decode($arg);
		if (is_array($arg))
			foreach ($arg as &$val) {
				$val=$this->raw($val);
				unset($val);
			}
		return $arg;
	}

	/**
		Return locale-aware formatted string
		@return string
	**/
	function format() {
		$args=func_get_args();
		$val=array_shift($args);
		$list=array();
		$windows=preg_match('/^win/i',PHP_OS);
		foreach ($this->languages as $language) {
			if ($windows) {
				$parts=explode('_',$language);
				$language=@constant('ISO::LC_'.$parts[0]);
				if (isset($parts[1]) &&
					$country=@constant('ISO::CC_'.$parts[1]))
					$language.='_'.$country;
			}
			$list[]=$language;
			$list[]=$language.'.'.$this->hive['ENCODING'];
		}
		setlocale(LC_ALL,$list);
		// Get formatting rules
		$conv=localeconv();
		if (!is_array($args))
			$args=array($args);
		$out=preg_replace_callback(
			'/{(?P<index>\d+)(?:,(?P<format>\w+)(?:,(?P<type>\w+))?)?}/',
			function($expr) use($args,$conv) {
				if (empty($args[$expr['index']]))
					return $expr[0];
				if (empty($expr['format']))
					return $args[$expr['index']];
				switch ($expr['format']) {
					case 'number':
						if (empty($expr['type']))
							return sprintf('%f',$args[$expr['index']]);
						switch ($expr['type']) {
							case 'integer':
								return
									number_format(
										$args[$expr['index']],0,'',
										$conv['thousands_sep']);
							case 'currency':
								return
									$conv['currency_symbol'].
									number_format(
										$args[$expr['index']],
										$conv['frac_digits'],
										$conv['decimal_point'],
										$conv['thousands_sep']);
							case 'percent':
								return
									number_format(
										$args[$expr['index']]*100,0,
										$conv['decimal_point'],
										$conv['thousands_sep']).'%';
						}
					case 'date':
						return strftime(empty($expr['type']) ||
							$expr['type']=='short'?'%x':'%A, %d %B %Y',
							$args[$expr['index']]);
					case 'time':
						return strftime('%X',$args[$expr['index']]);
					default:
						return $args[$expr['index']];
				}
			},
			$val
		);
		return $windows?
			iconv('Windows-1252',$this->hive['ENCODING'],$out):$out;
	}

	/**
		Assign/auto-detect language
		@return string
		@param $code string
	**/
	function language($code=NULL) {
		$this->languages=array(self::FALLBACK);
		// Use Accept-Language header, if available
		$headers=$this->hive['HEADERS'];
		if (!$code && isset($headers['Accept-Language']))
			$code=str_replace('-','_',$headers['Accept-Language']);
		// Validate string/header
		if (!preg_match('/^(\w{2})(?:_(\w{2}))?\b/',$code,$parts))
			return self::FALLBACK;
		if ($parts[1]!=self::FALLBACK)
			// Generic language
			array_unshift($this->languages,$parts[1]);
		if (isset($parts[2]))
			// Specific language
			array_unshift($this->languages,$parts[0]);
		return $parts[0];
	}

	/**
		Return string representation of PHP value
		@return string
		@param $arg mixed
	**/
	function serialize($arg) {
		switch (strtolower($this->hive['SERIALIZER'])) {
			case 'igbinary':
				return igbinary_serialize($arg);
			case 'json':
				return json_encode($arg);
			default:
				return serialize($arg);
		}
	}

	/**
		Return PHP value derived from string
		@return string
		@param $arg mixed
	**/
	function unserialize($arg) {
		switch (strtolower($this->hive['SERIALIZER'])) {
			case 'igbinary':
				return igbinary_unserialize($arg);
			case 'json':
				return json_decode($arg);
			default:
				return unserialize($arg);
		}
	}

	/**
		Send HTTP/1.1 status header; Return text equivalent of status code
		@return string
		@param $code int
	**/
	function status($code) {
		if (PHP_SAPI!='cli')
			header('HTTP/1.1 '.$code);
		return @constant('self::HTTP_'.$code);
	}

	/**
		Send cache metadata to HTTP client
		@return NULL
		@param $secs int
	**/
	function expire($secs=0) {
		if (PHP_SAPI!='cli') {
			header('X-Powered-By: '.$this->hive['PACKAGE']);
			if ($secs) {
				$time=microtime(TRUE);
				header_remove('Pragma');
				header('Expires: '.gmdate('r',$time+$secs));
				header('Cache-Control: max-age='.$secs);
				header('Last-Modified: '.gmdate('r'));
				$headers=$this->hive['HEADERS'];
				if (isset($headers['If-Modified-Since']) &&
					strtotime($headers['If-Modified-Since'])+$secs>$time) {
					$this->status(304);
					die;
				}
			}
			else
				header('Cache-Control: no-cache, no-store, must-revalidate');
		}
	}

	/**
		Log error; Execute ONERROR handler if defined, else display
		default error page
		@return NULL
		@param $code int
		@param $text string
		@param $trace array
	**/
	function error($code,$text='',array $trace=NULL) {
		$prior=$this->hive['ERROR'];
		$header=$this->status($code);
		$req=$this->hive['VERB'].' '.$this->hive['URI'];
		error_log($text=$text?:('HTTP '.$code.' '.$header.' ('.$req.')'));
		$out='';
		$eol="\n";
		if (!$trace)
			$trace=array_slice(debug_backtrace(FALSE),1);
		$css=$this->hive['HIGHLIGHT'] && is_file($file=__DIR__.'/'.self::CSS);
		// Analyze stack trace
		foreach ($trace as $frame) {
			$line='';
			if (isset($frame['file']) && ($frame['file']!=__FILE__ ||
				$this->hive['DEBUG']>1) && (empty($frame['class']) ||
				$frame['class']!='Magic') && (empty($frame['function']) ||
				!preg_match('/^(?:(?:trigger|user)_error|'.
					'__call|call_user_func)/',$frame['function']))) {
				$addr=$this->fixslashes($frame['file']).':'.$frame['line'];
				if (isset($frame['class']))
					$line.=$frame['class'].$frame['type'];
				if (isset($frame['function'])) {
					$line.=$frame['function'];
					if (!preg_match('/{.+}/',$frame['function'])) {
						$line.='(';
						if (!empty($frame['args']))
							$line.=$this->csv($frame['args']);
						$line.=')';
					}
				}
				error_log('- '.$addr.' '.$line);
				$out.='&bull; '.
					($css?$this->highlight($addr):$addr).' '.
					($css?$this->highlight($line):$line).$eol;
			}
		}
		$this->hive['ERROR']=array(
			'code'=>$code,
			'text'=>$text,
			'trace'=>$trace
		);
		if ($this->hive['ONERROR'])
			// Execute custom error handler
			$this->call($this->hive['ONERROR'],array($this));
		elseif (!$prior && PHP_SAPI!='cli' && !$this->hive['QUIET'])
			echo
				'<!DOCTYPE html>'.
				'<html>'.$eol.
				'<head>'.
					'<title>'.$code.' '.$header.'</title>'.
					($css?('<style>'.file_get_contents($file).'</style>'):'').
				'</head>'.$eol.
				'<body>'.$eol.
					'<h1>'.$header.'</h1>'.$eol.
					'<p>'.
						$this->encode($text?:$req).'</p>'.$eol.
					($out && $this->hive['DEBUG']?
						('<p>'.$eol.nl2br($out).'</p>'.$eol):'').
				'</body>'.$eol.
				'</html>';
	}

	/**
		Mock environment
		@return NULL
		@param $pattern string
		@param $args array
		@param $headers array
		@param $body string
	**/
	function mock($pattern,array $args=NULL,array $headers=NULL,$body=NULL) {
		list($verb,$url)=explode(' ',$pattern,2);
		$verb=strtoupper($verb);
		$url=parse_url($url);
		$query='';
		if ($args)
			$query.=http_build_query($args);
		$query.=isset($url['query'])?(($query?'&':'').$url['query']):'';
		if ($query && preg_match('/GET|POST/',$verb)) {
			parse_str($query,$GLOBALS['_'.$verb]);
			parse_str($query,$GLOBALS['_REQUEST']);
		}
		foreach ($headers?:array() as $key=>$val)
			$_SERVER['HTTP_'.str_replace('-','_',strtoupper($key))]=$val;
		$this->hive['VERB']=$verb;
		$this->hive['URI']=$this->hive['BASE'].$url['path'];
		if (preg_match('/GET|HEAD/',$verb) && $query)
			$this->hive['URI'].='?'.$query;
		else
			$this->hive['BODY']=$body?:$query;
		$this->run();
	}

	/**
		Bind handler to route pattern
		@return NULL
		@param $pattern string
		@param $handler callback
		@param $ttl int
		@param $kbps int
	**/
	function route($pattern,$handler,$ttl=0,$kbps=0) {
		$parts=preg_split('/\s+/',$pattern,2,PREG_SPLIT_NO_EMPTY);
		if (count($parts)<2)
			user_error(sprintf(self::E_Pattern,$pattern));
		list($verbs,$url)=$parts;
		foreach ($this->split($verbs) as $verb) {
			if (!preg_match('/'.self::VERBS.'/',$verb))
				$this->error(501,$verb.' '.$this->hive['URI']);
			$this->hive['ROUTES'][$url]
				[strtoupper($verb)]=array($handler,$ttl,$kbps);
		}
	}

	/**
		Reroute to specified URI
		@return NULL
		@param $uri string
	**/
	function reroute($uri) {
		if (PHP_SAPI!='cli') {
			@session_commit();
			header('Location: '.(preg_match('/^https?:\/\//',$uri)?
				$uri:($this->hive['BASE'].$uri)));
			$this->status($this->hive['VERB']=='GET'?301:303);
			die;
		}
		$this->mock('GET '.$uri);
	}

	/**
		Provide ReST interface by mapping HTTP verb to class method
		@param $url string
		@param $class string
		@param $ttl int
		@param $kbps int
	**/
	function map($url,$class,$ttl=0,$kbps=0) {
		foreach (explode('|',self::VERBS) as $method)
			$this->route($method.' '.
				$url,$class.'->'.strtolower($method),$ttl,$kbps);
	}

	/**
		Match routes against incoming URI
		@return NULL
	**/
	function run() {
		if ($this->hive['DNSBL'] && !in_array($this->hive['IP'],
			is_array($this->hive['EXEMPT'])?
				$this->hive['EXEMPT']:
				$this->split($this->hive['EXEMPT']))) {
			// Reverse IPv4 dotted quad
			$rev=implode('.',array_reverse(explode('.',$this->hive['IP'])));
			foreach (is_array($this->hive['DNSBL'])?
				$this->hive['DNSBL']:
				$this->split($this->hive['DNSBL']) as $server)
				// DNSBL lookup
				if (gethostbyname($host=$rev.'.'.$server)!=$host)
					// Spammer detected
					$this->error(403);
		}
		if (!$this->hive['ROUTES'])
			// No routes defined
			user_error(self::E_Routes);
		// Match specific routes first
		krsort($this->hive['ROUTES']);
		// Convert to BASE-relative URL
		$req=preg_replace(
			'/^'.preg_quote($this->hive['BASE'],'/').'\b(.+)/','\1',
			rawurldecode($this->hive['URI'])
		);
		$allowed=array();
		$case=$this->hive['CASELESS']?'i':'';
		foreach ($this->hive['ROUTES'] as $url=>$route) {
			if (!preg_match('/^'.
				preg_replace('/@(\w+\b)/','(?P<\1>.+?)',
				str_replace('\*','(.*)',preg_quote($url,'/'))).
				'\/?(?:\?.*)?$/'.$case.'um',$req,$args))
				// Process next route
				continue;
			if (isset($route[$this->hive['VERB']])) {
				if ($this->hive['VERB']=='GET' &&
					strlen($path=parse_url($req,PHP_URL_PATH))>1 &&
					substr($path,-1)=='/') {
					// Trailing slash in URL; Redirect
					$query=parse_url($req,PHP_URL_QUERY);
					$this->reroute(substr($path,0,-1).
						($query?('?'.$query):''));
				}
				list($handler,$ttl,$kbps)=$route[$this->hive['VERB']];
				if (is_bool(strpos($url,'/*')))
					foreach (array_keys($args) as $key)
						if (is_numeric($key) && $key)
							unset($args[$key]);
				if (is_string($handler))
					// Replace route pattern tokens in handler if any
					$handler=preg_replace_callback('/@(\w+\b)/',
						function($id) use($args) {
							return isset($args[$id[1]])?$args[$id[1]]:$id[0];
						},
						$handler
					);
				// Capture values of route pattern tokens
				$this->hive['PARAMS']=$args;
				// Save matching route
				$this->hive['PATTERN']=$url;
				// Process request
				$now=microtime(TRUE);
				if (preg_match('/GET|HEAD/',$this->hive['VERB']) &&
					isset($ttl)) {
					// Only GET and HEAD requests are cacheable
					$headers=$this->hive['HEADERS'];
					$cache=Cache::instance();
					$cached=$cache->exists(
						$hash=$this->hash($this->hive['VERB'].' '.
							$this->hive['URI']).'.url',$data);
					if ($cached && $cached+$ttl>$now) {
						if (empty($headers['If-Modified-Since']) ||
							floor($cached)>
								strtotime($headers['If-Modified-Since'])) {
							// Retrieve from cache backend
							list($headers,$body)=$data;
							if (PHP_SAPI!='cli')
								array_walk($headers,'header');
							// Override headers
							$this->expire($cached+$ttl-$now);
						}
						else {
							// HTTP client-cached page is fresh
							$this->status(304);
							die;
						}
					}
					else {
						// Expire HTTP client-cached page
						$this->expire($ttl);
						// Call route handler
						ob_start();
						$this->call($handler,array($this,$args),
							'beforeroute,afterroute');
						$body=ob_get_clean();
						if (!error_get_last())
							// Save to cache backend
							$cache->set($hash,
								array(headers_list(),$body),$ttl);
					}
				}
				else {
					$this->expire(0);
					// Call route handler
					ob_start();
					$this->call($handler,array($this,$args),
						'beforeroute,afterroute');
					$body=ob_get_clean();
				}
				if ($this->hive['RESPONSE']=$body) {
					$ctr=0;
					foreach (str_split($body,1024) as $part) {
						if ($kbps) {
							// Throttle output
							$ctr++;
							if ($ctr/$kbps>$elapsed=microtime(TRUE)-$now)
								usleep(1e6*($ctr/$kbps-$elapsed));
						}
						if (!$this->hive['QUIET'])
							echo $part;
					}
				}
				return;
			}
			$allowed=array_keys($route);
			break;
		}
		if (!$allowed)
			// URL doesn't match any route
			$this->error(404);
		elseif (PHP_SAPI!='cli') {
			// Unhandled HTTP method
			header('Allow: '.implode(',',$allowed));
			if ($this->hive['VERB']!='OPTIONS')
				$this->error(405);
		}
	}

	/**
		Execute callback/hooks (supports 'class->method' format)
		@return mixed|FALSE
		@param $func callback
		@param $args array
		@param $hooks string
	**/
	function call($func,array $args=NULL,$hooks='') {
		// Execute function; abort if callback/hook returns FALSE
		if (is_string($func) &&
			preg_match('/(.+)\s*(->|::)\s*(.+)/s',$func,$parts)) {
			// Convert string to executable PHP callback
			if (!class_exists($parts[1]))
				$this->error(404);
			if ($parts[2]=='->')
				$parts[1]=is_subclass_of($parts[1],'Prefab')?
					call_user_func($parts[1].'::instance'):
					new $parts[1];
			$func=array($parts[1],$parts[3]);
		}
		if (!is_callable($func) && $hooks=='beforeroute,afterroute')
			// No route handler
			$this->error(404);
		$oo=FALSE;
		if (is_array($func)) {
			$hooks=$this->split($hooks);
			$oo=TRUE;
		}
		// Execute pre-route hook if any
		if ($oo && $hooks && in_array($hook='beforeroute',$hooks) &&
			method_exists($func[0],$hook) &&
			call_user_func_array(array($func[0],$hook),$args)===FALSE)
			return FALSE;
		// Execute callback
		$out=call_user_func_array($func,$args?:array());
		if ($out===FALSE)
			return FALSE;
		// Execute post-route hook if any
		if ($oo && $hooks && in_array($hook='afterroute',$hooks) &&
			method_exists($func[0],$hook) &&
			call_user_func_array(array($func[0],$hook),$args)===FALSE)
			return FALSE;
		return $out;
	}

	/**
		Execute specified callbacks in succession
		@return array
		@param $funcs array|string
		@param $args array
	**/
	function chain($funcs,array $args=NULL) {
		$out=array();
		foreach (is_array($funcs)?$funcs:$this->split($funcs) as $func)
			$out[]=$this->call($func,$args);
		return $out;
	}

	/**
		Configure framework according to .ini-style file settings
		@return NULL
		@param $file string
	**/
	function config($file) {
		preg_match_all(
			'/(?<=^|\n)'.
			'(?:;.*?)|(?:<\?php.+\?>?)|'.
			'(?:\[(.+?)\])|'.
			'(.+?)[[:blank:]]*=[[:blank:]]*'.
			'((?:\\\\[[:blank:]\r]*\n|.+?)*)'.
			'(?=\r?\n|$)/',
			file_get_contents($file),$matches,PREG_SET_ORDER);
		if ($matches) {
			$sec='globals';
			foreach ($matches as $match)
				if (isset($match[1])) {
					if ($match[1])
						$sec=$match[1];
					elseif (in_array($sec,array('routes','maps')))
						call_user_func_array(
							array($this,trim($sec,'s')),
							array_merge(
								array($match[2]),
								str_getcsv($match[3])));
					else {
						$args=array_map(
							function($val) {
								$quote=(isset($val[0]) && $val[0]=="\x00");
								$val=trim($val);
								if (!$quote && is_numeric($val))
									return $val+0;
								if (preg_match('/^\w+$/i',$val) &&
									defined($val))
									return constant($val);
								return preg_replace(
									'/\\\\[[:blank:]\r]*\n/','',$val);
							},
							str_getcsv(
								// Mark quoted strings with 0x00 whitespace
								preg_replace('/"(.+?)"/',"\x00\\1",$match[3]))
						);
						call_user_func_array(array($this,'set'),
							array_merge(
								array($match[2]),
								count($args)>1?array($args):$args));
					}
				}
		}
	}

	/**
		Obtain exclusive locks on specified files and invoke callback;
		Release locks after callback execution
		@return mixed
		@param $files string|array
		@param $func callback
		@param $args array
	**/
	function mutex($files,$func,array $args=NULL) {
		$handles=array();
		if (!is_dir($tmp=$this->hive['TEMP']))
			mkdir($tmp,self::MODE,TRUE);
		// Max lock duration
		$max=ini_get('max_execution_time');
		foreach (is_array($files)?$files:$this->split($files) as $file) {
			// Use filesystem lock
			if (is_file($lock=$tmp.'/'.
				$this->hash($this->hive['ROOT'].$this->hive['BASE']).'.'.
				$this->hash($file).'.lock') &&
				filemtime($lock)+$max<microtime(TRUE))
				// Stale lock
				unlink($lock);
			while (!$handle=@fopen($lock,'x'))
				usleep(mt_rand(0,100));
			$handles[$lock]=$handle;
		}
		// Allow class->method format
		$out=$this->call($func,$args?:array());
		foreach ($handles as $lock=>$handle) {
			fclose($handle);
			unlink($lock);
		}
		return $out;
	}

	/**
		Exclusive file read
		@return string
		@param $file string
	**/
	function read($file) {
		return $this->mutex($file,'file_get_contents',array($file));
	}

	/**
		Exclusive file write
		@return int
		@param $file string
		@param $data mixed
		@param $append bool
	**/
	function write($file,$data,$append=FALSE) {
		return $this->mutex($file,
			'file_put_contents',
			array($file,$data,LOCK_EX|($append?FILE_APPEND:0)));
	}

	/**
		Exclusive file rename
		@return bool
		@param $from string
		@param $to string
	**/
	function rename($from,$to) {
		return $this->mutex($from,'rename',array($from,$to));
	}

	/**
		Exclusive file delete
		@return bool
		@param $file string
	**/
	function unlink($file) {
		return $this->mutex($file,'unlink',array($file));
	}

	/**
		Apply syntax highlighting
		@return string
		@param $text string
	**/
	function highlight($text) {
		$out='';
		$pre=FALSE;
		if (!preg_match('/<\?php/',$text)) {
			$text='<?php '.$text;
			$pre=TRUE;
		}
		$ref=new ReflectionExtension('tokenizer');
		foreach (token_get_all($text) as $token)
			if ($pre)
				$pre=FALSE;
			else
				$out.='<span class="php'.
					(is_array($token)?
						(' '.substr(strtolower(token_name($token[0])),2).'">'.
							$this->encode($token[1]).''):
						('">'.$this->encode($token))).
					'</span>';
		return $out?:$text;
	}

	/**
		Dump expression with syntax highlighting
		@return NULL
		@param $expr mixed
	**/
	function dump($expr) {
		echo $this->highlight($this->stringify($expr));
	}

	/**
		Namespace-aware class autoloader
		@return NULL
		@param $class string
	**/
	protected function autoload($class) {
		$class=$this->fixslashes(ltrim($class,'\\'));
		foreach ($this->split($this->hive['PLUGINS'].';'.
			$this->hive['AUTOLOAD']) as $auto)
			if (is_file($file=$auto.$class.'.php') ||
				is_file($file=$auto.strtolower($class).'.php')) {
				require($file);
				return;
			}
	}

	/**
		Execute framework/application shutdown sequence
		@return NULL
	**/
	function unload() {
		if (($error=error_get_last()) &&
			in_array($error['type'],
				array(E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR)))
			// Fatal error detected
			$this->error(500,sprintf(self::E_Fatal,$error['message']),
				array($error));
		if (isset($this->hive['UNLOAD']))
			$this->UNLOAD();
	}

	/**
		Return class instance
		@return object
	**/
	static function instance() {
		if (!Registry::exists($class=__CLASS__))
			Registry::set($class,new $class);
		return Registry::get($class);
	}

	//! Prohibit cloning
	private function __clone() {
	}

	//! Bootstrap
	private function __construct() {
		// Managed directives
		ini_set('default_charset',$charset='UTF-8');
		ini_set('display_errors',0);
		// Deprecated directives
		ini_set('magic_quotes_gpc',0);
		ini_set('register_globals',0);
		// Intercept errors/exceptions; PHP5.3-compatible
		error_reporting(E_ALL|E_STRICT);
		$fw=$this;
		set_exception_handler(
			function($obj) use($fw) {
				$fw->error(500,$obj->getmessage(),$obj->gettrace());
			}
		);
		set_error_handler(
			function($code,$text) use($fw) {
				if (error_reporting())
					throw new ErrorException($text);
			}
		);
		if (!isset($_SERVER['SERVER_NAME']))
			$_SERVER['SERVER_NAME']=gethostname();
		if (PHP_SAPI=='cli') {
			// Emulate HTTP request
			if (isset($_SERVER['argc']) && $_SERVER['argc']<2) {
				$_SERVER['argc']++;
				$_SERVER['argv'][1]='/';
			}
			$_SERVER['REQUEST_METHOD']='GET';
			$_SERVER['REQUEST_URI']=$_SERVER['argv'][1];
		}
		$headers=getallheaders();
		if (isset($headers['X-HTTP-Method-Override']))
			$_SERVER['REQUEST_METHOD']=$headers['X-HTTP-Method-Override'];
		$scheme=isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ||
			isset($headers['X-Forwarded-Proto']) &&
			$headers['X-Forwarded-Proto']=='https'?'https':'http';
		$base=implode('/',array_map('urlencode',
			explode('/',$this->fixslashes(
			preg_replace('/\/[^\/]+$/','',$_SERVER['SCRIPT_NAME'])))));
		call_user_func_array('session_set_cookie_params',
			$jar=array(
				'expire'=>0,
				'path'=>$base?:'/',
				'domain'=>is_int(strpos($_SERVER['SERVER_NAME'],'.')) &&
					!filter_var($_SERVER['SERVER_NAME'],FILTER_VALIDATE_IP)?
					$_SERVER['SERVER_NAME']:'',
				'secure'=>($scheme=='https'),
				'httponly'=>TRUE
			)
		);
		// Default configuration
		$this->hive=array(
			'AJAX'=>isset($headers['X-Requested-With']) &&
				$headers['X-Requested-With']=='XMLHttpRequest',
			'AUTOLOAD'=>'./',
			'BASE'=>$base,
			'BODY'=>file_get_contents('php://input'),
			'CACHE'=>FALSE,
			'CASELESS'=>TRUE,
			'DEBUG'=>0,
			'DIACRITICS'=>array(),
			'DNSBL'=>'',
			'ENCODING'=>$charset,
			'ERROR'=>NULL,
			'ESCAPE'=>TRUE,
			'EXEMPT'=>NULL,
			'HEADERS'=>$headers,
			'HIGHLIGHT'=>TRUE,
			'HOST'=>$_SERVER['SERVER_NAME'],
			'IP'=>isset($headers['Client-IP'])?
				$headers['Client-IP']:
				(isset($headers['X-Forwarded-For'])?
					current(explode(',',$headers['X-Forwarded-For'])):
					(isset($_SERVER['REMOTE_ADDR'])?
						$_SERVER['REMOTE_ADDR']:'')),
			'JAR'=>$jar,
			'LANGUAGE'=>$this->language(),
			'LOCALES'=>'./',
			'LOGS'=>'./',
			'ONERROR'=>NULL,
			'PACKAGE'=>self::PACKAGE,
			'PARAMS'=>array(),
			'PATTERN'=>NULL,
			'PLUGINS'=>$this->fixslashes(__DIR__).'/',
			'PORT'=>isset($_SERVER['SERVER_PORT'])?
				$_SERVER['SERVER_PORT']:NULL,
			'QUIET'=>FALSE,
			'REALM'=>$scheme.'://'.
				$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'],
			'RESPONSE'=>'',
			'ROOT'=>$_SERVER['DOCUMENT_ROOT'],
			'ROUTES'=>array(),
			'SCHEME'=>$scheme,
			'SERIALIZER'=>extension_loaded($ext='igbinary')?$ext:'php',
			'TEMP'=>'tmp/',
			'TIME'=>microtime(TRUE),
			'TZ'=>date_default_timezone_get(),
			'UI'=>'./',
			'UNLOAD'=>NULL,
			'UPLOADS'=>'./',
			'URI'=>&$_SERVER['REQUEST_URI'],
			'VERB'=>&$_SERVER['REQUEST_METHOD'],
			'VERSION'=>self::VERSION
		);
		if (ini_get('auto_globals_jit'))
			// Override setting
			$GLOBALS+=array('_ENV'=>$_ENV,'_REQUEST'=>$_REQUEST);
		// Sync PHP globals with corresponding hive keys
		$this->defaults=$this->hive;
		foreach (explode('|',self::GLOBALS) as $global) {
			$sync=$this->sync($global);
			$this->defaults+=array(
				$global=>preg_match('/SERVER|ENV/',$global)?$sync:array()
			);
		}
		// Register framework autoloader
		spl_autoload_register(array($this,'autoload'));
		// Register shutdown handler
		register_shutdown_function(array($this,'unload'));
	}

	/**
		Wrap-up
		@return NULL
	**/
	function __destruct() {
		Registry::clear(__CLASS__);
	}

}

//! Cache engine
final class Cache {

	private
		//! Cache DSN
		$dsn,
		//! Prefix for cache entries
		$prefix,
		//! MemCache object
		$ref;

	/**
		Return timestamp of cache entry or FALSE if not found
		@return float|FALSE
		@param $key string
		@param $val mixed
	**/
	function exists($key,&$val=NULL) {
		$fw=Base::instance();
		if (!$this->dsn)
			return FALSE;
		$ndx=$this->prefix.'.'.$key;
		$parts=explode('=',$this->dsn);
		switch ($parts[0]) {
			case 'apc':
				$raw=apc_fetch($ndx);
				break;
			case 'memcache':
				$raw=memcache_get($this->ref,$ndx);
				break;
			case 'wincache':
				$raw=wincache_ucache_get($ndx);
				break;
			case 'xcache':
				$raw=xcache_get($ndx);
				break;
			case 'folder':
				if (is_file($file=$parts[1].$ndx))
					$raw=$fw->read($file);
				break;
		}
		if (isset($raw)) {
			list($val,$time,$ttl)=$fw->unserialize($raw);
			if (!$ttl || $time+$ttl>microtime(TRUE))
				return $time;
			$this->clear($key);
		}
		return FALSE;
	}

	/**
		Store value in cache
		@return mixed|FALSE
		@param $key string
		@param $val mixed
		@param $ttl int
	**/
	function set($key,$val,$ttl=0) {
		$fw=Base::instance();
		if (!$this->dsn)
			return TRUE;
		$ndx=$this->prefix.'.'.$key;
		$data=$fw->serialize(array($val,microtime(TRUE),$ttl));
		$parts=explode('=',$this->dsn);
		switch ($parts[0]) {
			case 'apc':
				return apc_store($ndx,$data,$ttl);
			case 'memcache':
				return memcache_set($this->ref,$ndx,$data,0,$ttl);
			case 'wincache':
				return wincache_ucache_set($ndx,$data,$ttl);
			case 'xcache':
				return xcache_set($ndx,$data,$ttl);
			case 'folder':
				return $fw->write($parts[1].$ndx,$data);
		}
		return FALSE;
	}

	/**
		Retrieve value of cache entry
		@return mixed|FALSE
		@param $key string
	**/
	function get($key) {
		return $this->dsn && $this->exists($key,$data)?$data:FALSE;
	}

	/**
		Delete cache entry
		@return bool
		@param $key string
	**/
	function clear($key) {
		$fw=Base::instance();
		if (!$this->dsn)
			return;
		$ndx=$this->prefix.'.'.$key;
		$parts=explode('=',$this->dsn);
		switch ($parts[0]) {
			case 'apc':
				return apc_delete($ndx);
			case 'memcache':
				return memcache_delete($this->ref,$ndx);
			case 'wincache':
				return wincache_ucache_delete($ndx);
			case 'xcache':
				return xcache_unset($ndx);
			case 'folder':
				return is_file($file=$parts[1].$ndx) && $fw->unlink($file);
		}
		return FALSE;
	}

	/**
		Clear contents of cache backend
		@return bool
		@param $suffix string
		@param $lifetime int
	**/
	function reset($suffix=NULL,$lifetime=0) {
		if (!$this->dsn)
			return TRUE;
		$regex='/'.preg_quote($this->prefix.'.','/').'.+?'.
			preg_quote($suffix,'/').'/';
		$parts=explode('=',$this->dsn);
		switch ($parts[0]) {
			case 'apc':
				$info=apc_cache_info('user');
				foreach ($info['cache_list'] as $item)
					if (preg_match($regex,$item['info']) &&
						$item['mtime']+$lifetime<time())
						apc_delete($item['info']);
				return TRUE;
			case 'memcache':
				foreach (memcache_get_extended_stats(
					$this->ref,'slabs') as $slabs)
					foreach (array_keys($slabs) as $id)
						foreach (memcache_get_extended_stats(
							$this->ref,'cachedump',(int)$id) as $data)
							if (is_array($data))
								foreach ($data as $key=>$val)
									if (preg_match($regex,$key) &&
										$val[1]+$lifetime<time())
										memcache_delete($this->ref,$key);
				return TRUE;
			case 'wincache':
				$info=wincache_ucache_info();
				foreach ($info['ucache_entries'] as $item)
					if (preg_match($regex,$item['key_name']) &&
						$item['use_time']+$lifetime<time())
					apc_delete($item['key_name']);
				return TRUE;
			case 'xcache':
				return TRUE; // Not supported
			case 'folder':
				foreach (glob($parts[1].'*') as $file)
					if (preg_match($regex,basename($file)) &&
						filemtime($file)+$lifetime<time())
						Base::instance()->unlink($file);
				return TRUE;
		}
		return FALSE;
	}

	/**
		Load/auto-detect cache backend
		@return string
		@param $dsn bool|string
	**/
	function load($dsn) {
		if ($dsn) {
			$fw=Base::instance();
			if (preg_match('/memcache=(.+)/',$dsn,$parts) &&
				extension_loaded('memcache'))
				foreach ($fw->split($parts[1]) as $server) {
					$port=11211;
					$parts=explode(':',$server,2);
					if (count($parts)>1)
						list($host,$port)=$parts;
					else
						$host=$parts[0];
					if (empty($this->ref))
						$this->ref=@memcache_connect($host,$port)?:NULL;
					else
						memcache_add_server($this->ref,$host,$port);
				}
			if (empty($this->ref) && !preg_match('/folder=/',$dsn))
				$dsn=($grep=preg_grep('/^(apc|wincache|xcache)/',
					array_map('strtolower',get_loaded_extensions())))?
						// Auto-detect
						current($grep):
						// Use filesystem as fallback
						('folder='.$fw->get('TEMP').'cache/');
			if (preg_match('/folder=(.+)/',$dsn,$parts) && !is_dir($parts[1]))
				mkdir($parts[1],Base::MODE,TRUE);
		}
		return $this->dsn=$dsn;
	}

	/**
		Return class instance
		@return object
	**/
	static function instance() {
		if (!Registry::exists($class=__CLASS__))
			Registry::set($class,new $class);
		return Registry::get($class);
	}

	//! Prohibit cloning
	private function __clone() {
	}

	//! Prohibit instantiation
	private function __construct() {
		$fw=Base::instance();
		$this->prefix=$fw->hash($fw->get('ROOT').$fw->get('BASE'));
	}

	/**
		Wrap-up
		@return NULL
	**/
	function __destruct() {
		Registry::clear(__CLASS__);
	}

}

//! Prefab for classes with constructors and static factory methods
abstract class Prefab {

	/**
		Return class instance
		@return object
	**/
	static function instance() {
		if (!Registry::exists($class=get_called_class()))
			Registry::set($class,new $class);
		return Registry::get($class);
	}

	/**
		Wrap-up
		@return NULL
	**/
	function __destruct() {
		Registry::clear(get_called_class());
	}

}

//! View handler
class View extends Prefab {

	protected
		//! Template file
		$view,
		//! Local hive
		$hive;

	/**
		Create sandbox for template execution
		@return string
	**/
	protected function sandbox() {
		extract($this->hive);
		ob_start();
		require($this->view);
		return ob_get_clean();
	}

	/**
		Render template
		@return string
		@param $file string
		@param $mime string
		@param $hive array
	**/
	function render($file,$mime='text/html',array $hive=NULL) {
		$fw=Base::instance();
		foreach ($fw->split($fw->get('UI')) as $dir)
			if (is_file($this->view=$fw->fixslashes($dir.$file))) {
				if (isset($_COOKIE[session_name()]))
					@session_start();
				$fw->sync('SESSION');
				if (!$hive)
					$hive=$fw->hive();
				$this->hive=$fw->get('ESCAPE')?$hive=$fw->esc($hive):$hive;
				if (PHP_SAPI!='cli')
					header('Content-Type: '.$mime.'; '.
						'charset='.$fw->get('ENCODING'));
				return $this->sandbox();
			}
		user_error(sprintf(Base::E_Open,$file));
	}

}

//! ISO language/country codes
class ISO extends Prefab {

	//@{ ISO 3166-1 country codes
	const
		CC_af='Afghanistan',
		CC_ax='Åland Islands',
		CC_al='Albania',
		CC_dz='Algeria',
		CC_as='American Samoa',
		CC_ad='Andorra',
		CC_ao='Angola',
		CC_ai='Anguilla',
		CC_aq='Antarctica',
		CC_ag='Antigua and Barbuda',
		CC_ar='Argentina',
		CC_am='Armenia',
		CC_aw='Aruba',
		CC_au='Australia',
		CC_at='Austria',
		CC_az='Azerbaijan',
		CC_bs='Bahamas',
		CC_bh='Bahrain',
		CC_bd='Bangladesh',
		CC_bb='Barbados',
		CC_by='Belarus',
		CC_be='Belgium',
		CC_bz='Belize',
		CC_bj='Benin',
		CC_bm='Bermuda',
		CC_bt='Bhutan',
		CC_bo='Bolivia',
		CC_ba='Bosnia and Herzegovina',
		CC_bw='Botswana',
		CC_bv='Bouvet Island',
		CC_br='Brazil',
		CC_io='British Indian Ocean Territory',
		CC_bn='Brunei Darussalam',
		CC_bg='Bulgaria',
		CC_bf='Burkina Faso',
		CC_bi='Burundi',
		CC_kh='Cambodia',
		CC_cm='Cameroon',
		CC_ca='Canada',
		CC_cv='Cape Verde',
		CC_ky='Cayman Islands',
		CC_cf='Central African Republic',
		CC_td='Chad',
		CC_cl='Chile',
		CC_cn='China',
		CC_cx='Christmas Island',
		CC_cc='Cocos (Keeling) Islands',
		CC_co='Colombia',
		CC_km='Comoros',
		CC_cg='Congo',
		CC_cd='Congo, The Democratic Republic of',
		CC_ck='Cook Islands',
		CC_cr='Costa Rica',
		CC_ci='Côte D\'ivoire',
		CC_hr='Croatia',
		CC_cu='Cuba',
		CC_cw='Curaçao',
		CC_cy='Cyprus',
		CC_cz='Czech Republic',
		CC_dk='Denmark',
		CC_dj='Djibouti',
		CC_dm='Dominica',
		CC_do='Dominican Republic',
		CC_ec='Ecuador',
		CC_eg='Egypt',
		CC_sv='El Salvador',
		CC_gq='Equatorial Guinea',
		CC_er='Eritrea',
		CC_ee='Estonia',
		CC_et='Ethiopia',
		CC_fk='Falkland Islands (Malvinas)',
		CC_fo='Faroe Islands',
		CC_fj='Fiji',
		CC_fi='Finland',
		CC_fr='France',
		CC_gf='French Guiana',
		CC_pf='French Polynesia',
		CC_tf='French Southern Territories',
		CC_ga='Gabon',
		CC_gm='Gambia',
		CC_ge='Georgia',
		CC_de='Germany',
		CC_gh='Ghana',
		CC_gi='Gibraltar',
		CC_gr='Greece',
		CC_gl='Greenland',
		CC_gd='Grenada',
		CC_gp='Guadeloupe',
		CC_gu='Guam',
		CC_gt='Guatemala',
		CC_gg='Guernsey',
		CC_gn='Guinea',
		CC_gw='Guinea-Bissau',
		CC_gy='Guyana',
		CC_ht='Haiti',
		CC_hm='Heard Island and Mcdonald Islands',
		CC_va='Holy See (Vatican City State)',
		CC_hn='Honduras',
		CC_hk='Hong Kong',
		CC_hu='Hungary',
		CC_is='Iceland',
		CC_in='India',
		CC_id='Indonesia',
		CC_ir='Iran, Islamic Republic of',
		CC_iq='Iraq',
		CC_ie='Ireland',
		CC_im='Isle of Man ',
		CC_il='Israel',
		CC_it='Italy',
		CC_jm='Jamaica',
		CC_jp='Japan',
		CC_je='Jersey ',
		CC_jo='Jordan',
		CC_kz='Kazakhstan',
		CC_ke='Kenya',
		CC_ki='Kiribati',
		CC_kp='Korea, Democratic People\'s Republic of',
		CC_kr='Korea, Republic of',
		CC_kw='Kuwait',
		CC_kg='Kyrgyzstan',
		CC_la='Lao People\'s Democratic Republic',
		CC_lv='Latvia',
		CC_lb='Lebanon',
		CC_ls='Lesotho',
		CC_lr='Liberia',
		CC_ly='Libyan Arab Jamahiriya',
		CC_li='Liechtenstein',
		CC_lt='Lithuania',
		CC_lu='Luxembourg',
		CC_mo='Macao',
		CC_mk='Macedonia, The Former Yugoslav Republic of',
		CC_mg='Madagascar',
		CC_mw='Malawi',
		CC_my='Malaysia',
		CC_mv='Maldives',
		CC_ml='Mali',
		CC_mt='Malta',
		CC_mh='Marshall Islands',
		CC_mq='Martinique',
		CC_mr='Mauritania',
		CC_mu='Mauritius',
		CC_yt='Mayotte',
		CC_mx='Mexico',
		CC_fm='Micronesia, Federated States of',
		CC_md='Moldova, Republic of',
		CC_mc='Monaco',
		CC_mn='Mongolia',
		CC_ms='Montserrat',
		CC_ma='Morocco',
		CC_mz='Mozambique',
		CC_mm='Myanmar',
		CC_na='Namibia',
		CC_nr='Nauru',
		CC_np='Nepal',
		CC_nl='Netherlands',
		CC_an='Netherlands Antilles',
		CC_nc='New Caledonia',
		CC_nz='New Zealand',
		CC_ni='Nicaragua',
		CC_ne='Niger',
		CC_ng='Nigeria',
		CC_nu='Niue',
		CC_nf='Norfolk Island',
		CC_mp='Northern Mariana Islands',
		CC_no='Norway',
		CC_om='Oman',
		CC_pk='Pakistan',
		CC_pw='Palau',
		CC_ps='Palestinian Territory, Occupied',
		CC_pa='Panama',
		CC_pg='Papua New Guinea',
		CC_py='Paraguay',
		CC_pe='Peru',
		CC_ph='Philippines',
		CC_pn='Pitcairn',
		CC_pl='Poland',
		CC_pt='Portugal',
		CC_pr='Puerto Rico',
		CC_qa='Qatar',
		CC_re='Réunion',
		CC_ro='Romania',
		CC_ru='Russian Federation',
		CC_rw='Rwanda',
		CC_sh='Saint Helena',
		CC_kn='Saint Kitts and Nevis',
		CC_lc='Saint Lucia',
		CC_pm='Saint Pierre and Miquelon',
		CC_vc='Saint Vincent and The Grenadines',
		CC_ws='Samoa',
		CC_sm='San Marino',
		CC_st='Sao Tome and Principe',
		CC_sa='Saudi Arabia',
		CC_sn='Senegal',
		CC_cs='Serbia and Montenegro',
		CC_sc='Seychelles',
		CC_sl='Sierra Leone',
		CC_sg='Singapore',
		CC_sk='Slovakia',
		CC_si='Slovenia',
		CC_sb='Solomon Islands',
		CC_so='Somalia',
		CC_za='South Africa',
		CC_gs='South Georgia and The South Sandwich Islands',
		CC_es='Spain',
		CC_lk='Sri Lanka',
		CC_sd='Sudan',
		CC_sr='Suriname',
		CC_sj='Svalbard and Jan Mayen',
		CC_sz='Swaziland',
		CC_se='Sweden',
		CC_ch='Switzerland',
		CC_sy='Syrian Arab Republic',
		CC_tw='Taiwan, Province of China',
		CC_tj='Tajikistan',
		CC_tz='Tanzania, United Republic of',
		CC_th='Thailand',
		CC_tl='Timor-Leste',
		CC_tg='Togo',
		CC_tk='Tokelau',
		CC_to='Tonga',
		CC_tt='Trinidad and Tobago',
		CC_tn='Tunisia',
		CC_tr='Turkey',
		CC_tm='Turkmenistan',
		CC_tc='Turks and Caicos Islands',
		CC_tv='Tuvalu',
		CC_ug='Uganda',
		CC_ua='Ukraine',
		CC_ae='United Arab Emirates',
		CC_gb='United Kingdom',
		CC_us='United States',
		CC_um='United States Minor Outlying Islands',
		CC_uy='Uruguay',
		CC_uz='Uzbekistan',
		CC_vu='Vanuatu',
		CC_ve='Venezuela',
		CC_vn='Viet Nam',
		CC_vg='Virgin Islands, British',
		CC_vi='Virgin Islands, U.S.',
		CC_wf='Wallis and Futuna',
		CC_eh='Western Sahara',
		CC_ye='Yemen',
		CC_zm='Zambia',
		CC_zw='Zimbabwe';
	//@}

	//@{ ISO 639-1 language codes (Windows-compatibility subset)
	const
		LC_af='Afrikaans',
		LC_am='Amharic',
		LC_ar='Arabic',
		LC_as='Assamese',
		LC_ba='Bashkir',
		LC_be='Belarusian',
		LC_bg='Bulgarian',
		LC_bn='Bengali',
		LC_bo='Tibetan',
		LC_br='Breton',
		LC_ca='Catalan',
		LC_co='Corsican',
		LC_cs='Czech',
		LC_cy='Welsh',
		LC_da='Danish',
		LC_de='German',
		LC_dv='Divehi',
		LC_el='Greek',
		LC_en='English',
		LC_es='Spanish',
		LC_et='Estonian',
		LC_eu='Basque',
		LC_fa='Persian',
		LC_fi='Finnish',
		LC_fo='Faroese',
		LC_fr='French',
		LC_gd='Scottish Gaelic',
		LC_gl='Galician',
		LC_gu='Gujarati',
		LC_he='Hebrew',
		LC_hi='Hindi',
		LC_hr='Croatian',
		LC_hu='Hungarian',
		LC_hy='Armenian',
		LC_id='Indonesian',
		LC_ig='Igbo',
		LC_is='Icelandic',
		LC_it='Italian',
		LC_ja='Japanese',
		LC_ka='Georgian',
		LC_kk='Kazakh',
		LC_km='Khmer',
		LC_kn='Kannada',
		LC_ko='Korean',
		LC_lb='Luxembourgish',
		LC_lo='Lao',
		LC_lt='Lithuanian',
		LC_lv='Latvian',
		LC_mi='Maori',
		LC_ml='Malayalam',
		LC_mr='Marathi',
		LC_ms='Malay',
		LC_mt='Maltese',
		LC_ne='Nepali',
		LC_nl='Dutch',
		LC_no='Norwegian',
		LC_oc='Occitan',
		LC_or='Oriya',
		LC_pl='Polish',
		LC_ps='Pashto',
		LC_pt='Portuguese',
		LC_qu='Quechua',
		LC_ro='Romanian',
		LC_ru='Russian',
		LC_rw='Kinyarwanda',
		LC_sa='Sanskrit',
		LC_si='Sinhala',
		LC_sk='Slovak',
		LC_sl='Slovenian',
		LC_sq='Albanian',
		LC_sv='Swedish',
		LC_ta='Tamil',
		LC_te='Telugu',
		LC_th='Thai',
		LC_tk='Turkmen',
		LC_tr='Turkish',
		LC_tt='Tatar',
		LC_uk='Ukrainian',
		LC_ur='Urdu',
		LC_vi='Vietnamese',
		LC_wo='Wolof',
		LC_yo='Yoruba',
		LC_zh='Chinese';
	//@}

	/**
		Return list of languages indexed by ISO 639-1 language code
		@return array
	**/
	function languages() {
		$ref=new ReflectionClass($this);
		$out=array();
		foreach (preg_grep('/LC_/',array_keys($ref->getconstants()))
			as $key=>$val)
			$out[$key=substr(strstr($val,'_'),1)]=constant('self::LC_'.$key);
		return $out;
	}

	/**
		Return list of countries indexed by ISO 3166-1 country code
		@return array
	**/
	function countries() {
		$ref=new ReflectionClass($this);
		$out=array();
		foreach (preg_grep('/CC_/',array_keys($ref->getconstants()))
			as $key=>$val)
			$out[$key=substr(strstr($val,'_'),1)]=constant('self::CC_'.$key);
		return $out;
	}

}

//! Container for singular object instances
final class Registry {

	private static
		//! Object catalog
		$table;

	/**
		Return TRUE if object exists in catalog
		@return bool
		@param $key string
	**/
	static function exists($key) {
		return isset(self::$table[$key]);
	}

	/**
		Add object to catalog
		@return object
		@param $key string
		@param $obj object
	**/
	static function set($key,$obj) {
		return self::$table[$key]=$obj;
	}

	/**
		Retrieve object from catalog
		@return object
		@param $key string
	**/
	static function get($key) {
		return self::$table[$key];
	}

	/**
		Remove object from catalog
		@return NULL
		@param $key string
	**/
	static function clear($key) {
		unset(self::$table[$key]);
	}

	//! Prohibit cloning
	private function __clone() {
	}

	//! Prohibit instantiation
	private function __construct() {
	}

}

//! PHP magic wrapper
abstract class Magic {

	/**
		Return TRUE if key is not empty
		@return bool
		@param $key string
	**/
	abstract function exists($key);

	/**
		Bind value to key
		@return mixed
		@param $key string
		@param $val mixed
	**/
	abstract function set($key,$val);

	/**
		Retrieve contents of key
		@return mixed
		@param $key string
	**/
	abstract function get($key);

	/**
		Unset key
		@return NULL
		@param $key string
	**/
	abstract function clear($key);

	/**
		Return TRUE if property has public visibility
		@return bool
		@param $Key string
	**/
	private function visible($key) {
		if (property_exists($this,$key)) {
			$ref=new \ReflectionProperty(get_class($this),$key);
			return $ref->ispublic();
		}
		return FALSE;
	}

	/**
		Convenience method for checking property value
		@return mixed
		@param $key string
	**/
	function __isset($key) {
		return $this->visible($key)?isset($this->$key):$this->exists($key);
	}

	/**
		Convenience method for assigning property value
		@return mixed
		@param $key string
		@param $val scalar
	**/
	function __set($key,$val) {
		return $this->visible($key)?($this->key=$val):$this->set($key,$val);
	}

	/**
		Convenience method for retrieving property value
		@return mixed
		@param $key string
	**/
	function __get($key) {
		return $this->visible($key)?$this->$key:$this->get($key);
	}

	/**
		Convenience method for checking property value
		@return mixed
		@param $key string
	**/
	function __unset($key) {
		if ($this->visible($key))
			unset($this->$key);
		else
			$this->clear($key);
	}

}

if (!function_exists('getallheaders')) {

	/**
		Fetch HTTP request headers
		@return array
	**/
	function getallheaders() {
		if (PHP_SAPI=='cli')
			return FALSE;
		$headers=array();
		foreach ($_SERVER as $key=>$val)
			if (substr($key,0,5)=='HTTP_')
				$headers[strtr(ucwords(strtolower(
					strtr(substr($key,5),'_',' '))),' ','-')]=$val;
		return $headers;
	}

}

return Base::instance();
