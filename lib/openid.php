<?php

/**
	OpenID plugin for single sign-on and authentication

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package OpenID
		@version 2.0.9
**/

//! OpenID plugin
class OpenID extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_EndPoint='Unable to find OpenID provider';
	//@}

	var
		//! HTTP request parameters
		$args=array();

	/**
		Return TRUE if OpenID verification was successful
			@public
	**/
	function verified() {
		foreach ($_REQUEST as $key=>$val)
			if (preg_match('/^openid_(.+)/',$key,$match))
				$this->$match[1]=$val;
		if (isset($this->provider))
			$op=$this->provider;
		elseif (isset($this->server))
			$op=$this->server;
		else {
			trigger_error(self::TEXT_EndPoint);
			return FALSE;
		}
		$this->mode='check_authentication';
		$var=array();
		foreach ($this->args as $key=>$val)
			$var['openid.'.$key]=$val;
		return preg_match('/is_valid:true/i',
			Web::http('POST '.$op.'?'.http_build_query($var)));
	}

	/**
		Initiate OpenID authentication sequence
			@return bool
			@public
	**/
	function auth() {
		$root=self::$vars['PROTOCOL'].'://'.$_SERVER['SERVER_NAME'];
		if (!isset($this->trust_root))
			$this->trust_root=$root.(self::$vars['BASE']?:'/');
		if (!isset($this->return_to))
			$this->return_to=$root.$_SERVER['REQUEST_URI'];
		$this->mode='checkid_setup';
		if (isset($this->provider)) {
			// OpenID 2.0
			$op=$this->provider;
			if (!isset($this->claimed_id))
				$this->claimed_id=$this->identity;
		}
		elseif (isset($this->server))
			// OpenID 1.1
			$op=$this->server;
		else
			return FALSE;
		$var=array();
		foreach ($this->args as $key=>$val)
			$var['openid.'.$key]=$val;
		$fw=new F3instance;
		$fw->reroute($op.'?'.http_build_query($var));
	}

	/**
		Bind value to OpenID request parameter
			@param $key string
			@param $val string
			@public
	**/
	function __set($key,$val) {
		if ($key=='identity') {
			// Normalize
			if (!preg_match('/https?:\/\//i',$val))
				$val='http://'.$val;
			$url=parse_url($val);
			// Remove fragment; reconnect parts
			$val=$url['scheme'].'://'.
				(isset($url['user'])?
					($url['user'].
					(isset($url['pass'])?
						(':'.$url['pass']):'').'@'):'').
				strtolower($url['host']).
				(isset($url['path'])?$url['path']:'/').
				(isset($url['query'])?('?'.$url['query']):'');
			$this->args['identity']=$val;
			// HTML-based discovery of OpenID provider
			$text=Web::http('GET '.$val);
			$type=array_values(
				preg_grep('/Content-Type:/',self::$vars['HEADERS'])
			);
			if ($type &&
				preg_match('/application\/xrds\+xml|text\/xml/',$type[0]) &&
				($sxml=simplexml_load_string($text)) &&
				($xrds=json_decode(json_encode($sxml),TRUE)) &&
				isset($xrds['XRD'])) {
				// XRDS document
				$svc=$xrds['XRD']['Service'];
				if (isset($svc[0]))
					$svc=$svc[0];
				if (preg_grep('/http:\/\/specs\.openid\.net\/auth\/2.0\/'.
						'(?:server|signon)/',$svc['Type'])) {
					$this->args['provider']=$svc['URI'];
					if (isset($svc['LocalID']))
						$this->args['localidentity']=$svc['LocalID'];
					elseif (isset($svc['CanonicalID']))
						$this->args['localidentity']=$svc['CanonicalID'];
				}
				$this->args['server']=$svc['URI'];
				if (isset($svc['Delegate']))
					$this->args['delegate']=$svc['Delegate'];
			}
			else {
				$len=strlen($text);
				$ptr=0;
				// Parse document
				while ($ptr<$len)
					if (preg_match(
						'/^<link\b((?:\s+\w+s*=\s*(?:"(?:.+?)"|'.
						'\'(?:.+?)\'))*)\s*\/?>/is',
						substr($text,$ptr),$match)) {
						if ($match[1]) {
							// Process attributes
							preg_match_all('/\s+(rel|href)\s*=\s*'.
								'(?:"(.+?)"|\'(.+?)\')/s',$match[1],$attr,
								PREG_SET_ORDER);
							$node=array();
							foreach ($attr as $kv)
								$node[$kv[1]]=isset($kv[2])?$kv[2]:$kv[3];
							if (isset($node['rel']) &&
								preg_match('/openid2?\.(\w+)/',
									$node['rel'],$var) &&
								isset($node['href']))
								$this->args[$var[1]]=$node['href'];

						}
						$ptr+=strlen($match[0]);
					}
					else
						$ptr++;
			}
			// Get OpenID provider's endpoint URL
			if (isset($this->args['provider'])) {
				// OpenID 2.0
				$this->args['ns']='http://specs.openid.net/auth/2.0';
				if (isset($this->args['localidentity']))
					$this->args['identity']=$this->args['localidentity'];
				if (isset($this->args['trust_root']))
					$this->args['realm']=$this->args['trust_root'];
			}
			elseif (isset($this->args['server'])) {
				// OpenID 1.1
				if (isset($this->args['delegate']))
					$this->args['identity']=$this->args['delegate'];
			}
		}
		else
			$this->args[$key]=self::resolve($val);
	}

	/**
		Return value of OpenID request parameter
			@return mixed
			@param $key string
			@public
	**/
	function __get($key) {
		return isset($this->args[$key])?$this->args[$key]:NULL;
	}

	/**
		Return TRUE if OpenID request parameter exists
			@return bool
			@param $key string
			@public
	**/
	function __isset($key) {
		return isset($this->args[$key]);
	}

	/**
		Remove OpenID request parameter
			@param $key
			@public
	**/
	function __unset($key) {
		unset($this->args[$key]);
	}

	/**
		Override base constructor
			@public
	**/
	function __construct() {
	}

}
