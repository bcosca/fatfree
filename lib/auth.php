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

//! Authorization/authentication plug-in
class Auth {

	//@{ Error messages
	const
		E_LDAP='LDAP connection failure',
		E_SMTP='SMTP connection failure';
	//@}

	protected
		//! Auth storage
		$storage,
		//! Mapper object
		$mapper,
		//! Storage options
		$args,
		//! Custom compare function
		$func;

	/**
	*	Jig storage handler
	*	@return bool
	*	@param $id string
	*	@param $pw string
	*	@param $realm string
	**/
	protected function _jig($id,$pw,$realm) {
		$success = (bool)
			call_user_func_array(
				[$this->mapper,'load'],
				[
					array_merge(
						[
							'@'.$this->args['id'].'==?'.
							($this->func?'':' AND @'.$this->args['pw'].'==?').
							(isset($this->args['realm'])?
								(' AND @'.$this->args['realm'].'==?'):''),
							$id
						],
						($this->func?[]:[$pw]),
						(isset($this->args['realm'])?[$realm]:[])
					)
				]
			);
		if ($success && $this->func)
			$success = call_user_func($this->func,$pw,$this->mapper->get($this->args['pw']));
		return $success;
	}

	/**
	*	MongoDB storage handler
	*	@return bool
	*	@param $id string
	*	@param $pw string
	*	@param $realm string
	**/
	protected function _mongo($id,$pw,$realm) {
		$success = (bool)
			$this->mapper->load(
				[$this->args['id']=>$id]+
				($this->func?[]:[$this->args['pw']=>$pw])+
				(isset($this->args['realm'])?
					[$this->args['realm']=>$realm]:[])
			);
		if ($success && $this->func)
			$success = call_user_func($this->func,$pw,$this->mapper->get($this->args['pw']));
		return $success;
	}

	/**
	*	SQL storage handler
	*	@return bool
	*	@param $id string
	*	@param $pw string
	*	@param $realm string
	**/
	protected function _sql($id,$pw,$realm) {
		$success = (bool)
			call_user_func_array(
				[$this->mapper,'load'],
				[
					array_merge(
						[
							$this->args['id'].'=?'.
							($this->func?'':' AND '.$this->args['pw'].'=?').
							(isset($this->args['realm'])?
								(' AND '.$this->args['realm'].'=?'):''),
							$id
						],
						($this->func?[]:[$pw]),
						(isset($this->args['realm'])?[$realm]:[])
					)
				]
			);
		if ($success && $this->func)
			$success = call_user_func($this->func,$pw,$this->mapper->get($this->args['pw']));
		return $success;
	}

	/**
	*	LDAP storage handler
	*	@return bool
	*	@param $id string
	*	@param $pw string
	**/
	protected function _ldap($id,$pw) {
		$port=(int)($this->args['port']?:389);
		$filter=$this->args['filter']=$this->args['filter']?:"uid=".$id;
		$this->args['attr']=$this->args['attr']?:["uid"];
		array_walk($this->args['attr'],
		function($attr)use(&$filter,$id) {
			$filter=str_ireplace($attr."=*",$attr."=".$id,$filter);});
		$dc=@ldap_connect($this->args['dc'],$port);
		if ($dc &&
			ldap_set_option($dc,LDAP_OPT_PROTOCOL_VERSION,3) &&
			ldap_set_option($dc,LDAP_OPT_REFERRALS,0) &&
			ldap_bind($dc,$this->args['rdn'],$this->args['pw']) &&
			($result=ldap_search($dc,$this->args['base_dn'],
				$filter,$this->args['attr'])) &&
			ldap_count_entries($dc,$result) &&
			($info=ldap_get_entries($dc,$result)) &&
			$info['count']==1 &&
			@ldap_bind($dc,$info[0]['dn'],$pw) &&
			@ldap_close($dc)) {
			return in_array($id,(array_map(function($value){return $value[0];},
				array_intersect_key($info[0],
					array_flip($this->args['attr'])))),TRUE);
		}
		user_error(self::E_LDAP,E_USER_ERROR);
	}

	/**
	*	SMTP storage handler
	*	@return bool
	*	@param $id string
	*	@param $pw string
	**/
	protected function _smtp($id,$pw) {
		$socket=@fsockopen(
			(strtolower($this->args['scheme'])=='ssl'?
				'ssl://':'').$this->args['host'],
				$this->args['port']);
		$dialog=function($cmd=NULL) use($socket) {
			if (!is_null($cmd))
				fputs($socket,$cmd."\r\n");
			$reply='';
			while (!feof($socket) &&
				($info=stream_get_meta_data($socket)) &&
				!$info['timed_out'] && $str=fgets($socket,4096)) {
				$reply.=$str;
				if (preg_match('/(?:^|\n)\d{3} .+\r\n/s',
					$reply))
					break;
			}
			return $reply;
		};
		if ($socket) {
			stream_set_blocking($socket,TRUE);
			$dialog();
			$fw=Base::instance();
			$dialog('EHLO '.$fw->HOST);
			if (strtolower($this->args['scheme'])=='tls') {
				$dialog('STARTTLS');
				stream_socket_enable_crypto(
					$socket,TRUE,STREAM_CRYPTO_METHOD_TLS_CLIENT);
				$dialog('EHLO '.$fw->HOST);
			}
			// Authenticate
			$dialog('AUTH LOGIN');
			$dialog(base64_encode($id));
			$reply=$dialog(base64_encode($pw));
			$dialog('QUIT');
			fclose($socket);
			return (bool)preg_match('/^235 /',$reply);
		}
		user_error(self::E_SMTP,E_USER_ERROR);
	}

	/**
	*	Login auth mechanism
	*	@return bool
	*	@param $id string
	*	@param $pw string
	*	@param $realm string
	**/
	function login($id,$pw,$realm=NULL) {
		return $this->{'_'.$this->storage}($id,$pw,$realm);
	}

	/**
	*	HTTP basic auth mechanism
	*	@return bool
	*	@param $func callback
	**/
	function basic($func=NULL) {
		$fw=Base::instance();
		$realm=$fw->REALM;
		$hdr=NULL;
		if (isset($_SERVER['HTTP_AUTHORIZATION']))
			$hdr=$_SERVER['HTTP_AUTHORIZATION'];
		elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))
			$hdr=$_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
		if (!empty($hdr))
			list($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])=
				explode(':',base64_decode(substr($hdr,6)));
		if (isset($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']) &&
			$this->login(
				$_SERVER['PHP_AUTH_USER'],
				$func?
					$fw->call($func,$_SERVER['PHP_AUTH_PW']):
					$_SERVER['PHP_AUTH_PW'],
				$realm
			))
			return TRUE;
		if (PHP_SAPI!='cli')
			header('WWW-Authenticate: Basic realm="'.$realm.'"');
		$fw->status(401);
		return FALSE;
	}

	/**
	*	Instantiate class
	*	@return object
	*	@param $storage string|object
	*	@param $args array
	*	@param $func callback
	**/
	function __construct($storage,array $args=NULL,$func=NULL) {
		if (is_object($storage) && is_a($storage,'DB\Cursor')) {
			$this->storage=$storage->dbtype();
			$this->mapper=$storage;
			unset($ref);
		}
		else
			$this->storage=$storage;
		$this->args=$args;
		$this->func=$func;
	}

}
