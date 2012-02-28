<?php

/**
	Authentication plugin for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Auth
		@version 2.0.9
**/

//! Plugin for various user authentication methods
class Auth extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_AuthSetup='Invalid AUTH variable configuration',
		TEXT_IMAPConnect='Unable to connect to IMAP server %s',
		TEXT_LDAPConnect='Unable to connect to LDAP server %s',
		TEXT_LDAPBind='LDAP bind failure';
	//@}

	/**
		Authenticate against SQL database;
			AUTH global array elements:
				db:<database-id> (default:'DB'),
				table:<table-name>,
				id:<userID-field>,
				pw:<password-field>
			@return mixed
			@param $id string
			@param $pw string
			@public
	**/
	static function sql($id,$pw) {
		$auth=&self::$vars['AUTH'];
		foreach (array('table','id','pw') as $param)
			if (!isset($auth[$param])) {
				trigger_error(self::TEXT_AuthSetup);
				return FALSE;
			}
		if (!isset($auth['db']))
			$auth['db']=self::ref('DB');
		$axon=new Axon($auth['table'],self::ref('AUTH.db'));
		$axon->load(
			array(
				self::ref('AUTH.id').'=:id AND '.
				self::ref('AUTH.pw').'=:pw',
				array(':id'=>$id,':pw'=>$pw)
			)
		);
		return $axon->dry()?FALSE:$axon;
	}

	/**
		Authenticate against NoSQL database (MongoDB);
			AUTH global array elements:
				db:<database-id> (default:'DB'),
				collection:<collection-name>,
				id:<userID-field>,
				pw:<password-field>
			@return mixed
			@param $id string
			@param $pw string
			@public
	**/
	static function nosql($id,$pw) {
		$auth=&self::$vars['AUTH'];
		foreach (array('collection','id','pw') as $param)
			if (!isset($auth[$param])) {
				trigger_error(self::TEXT_AuthSetup);
				return FALSE;
			}
		if (!isset($auth['db']))
			$auth['db']=self::ref('DB');
		$m2=new M2($auth['collection'],self::ref('AUTH.db'));
		$m2->load(
			array(
				self::ref('AUTH.id')=>$id,
				self::ref('AUTH.pw')=>$pw
			)
		);
		return $m2->dry()?FALSE:$m2;
	}

	/**
		Authenticate against Jig-mapped flat-file database;
			AUTH global array elements:
				db:<database-id> (default:'DB'),
				table:<table-name>,
				id:<userID-field>,
				pw:<password-field>
			@return mixed
			@param $id string
			@param $pw string
			@public
	**/
	static function jig($id,$pw) {
		$auth=&self::$vars['AUTH'];
		foreach (array('table','id','pw') as $param)
			if (!isset($auth[$param])) {
				trigger_error(self::TEXT_AuthSetup);
				return FALSE;
			}
		if (!isset($auth['db']))
			$auth['db']=self::ref('DB');
		$jig=new Jig($auth['table'],self::ref('AUTH.db'));
		$jig->load(
			array(
				self::ref('AUTH.id')=>$id,
				self::ref('AUTH.pw')=>$pw
			)
		);
		return $jig->dry()?FALSE:$jig;
	}

	/**
		Authenticate against IMAP server;
			AUTH global array elements:
				server:<IMAP-server>,
				port:<TCP-port> (default:143)
			@return boolean
			@param $id string
			@param $pw string
			@public
	**/
	static function imap($id,$pw) {
		// IMAP extension required
		if (!extension_loaded('imap')) {
			// Unable to continue
			trigger_error(sprintf(self::TEXT_PHPExt,'imap'));
			return;
		}
		$auth=self::$vars['AUTH'];
		if (!isset($auth['server'])) {
			trigger_error(self::TEXT_AuthSetup);
			return FALSE;
		}
		if (!isset($auth['port']))
			$auth['port']=143;
		$ic=@fsockopen($auth['server'],$auth['port']);
		if (!is_resource($ic)) {
			// Connection failed
			trigger_error(sprintf(self::TEXT_IMAPConnect,$auth['server']));
			return FALSE;
		}
		$ibox='{'.$auth['server'].':'.$auth['port'].'}INBOX';
		$mbox=@imap_open($ibox,$id,$pw);
		$ok=is_resource($mbox);
		if (!$ok) {
			$mbox=@imap_open($ibox,$id.'@'.$auth['server'],$pw);
			$ok=is_resource($mbox);
		}
		imap_close($mbox);
		return $ok;
	}

	/**
		Authenticate via LDAP;
			AUTH global array elements:
				dc:<domain-controller>,
				rdn:<connection-DN>,
				pw:<connection-password>
			@return boolean
			@param $id string
			@param $pw string
			@public
	**/
	static function ldap($id,$pw) {
		// LDAP extension required
		if (!extension_loaded('ldap')) {
			// Unable to continue
			trigger_error(sprintf(self::TEXT_PHPExt,'ldap'));
			return;
		}
		$auth=self::$vars['AUTH'];
		if (!isset($auth['dc'])) {
			trigger_error(self::TEXT_AuthSetup);
			return FALSE;
		}
		$dc=@ldap_connect($auth['dc']);
		if (!$dc) {
			// Connection failed
			trigger_error(sprintf(self::TEXT_LDAPConnect,$auth['dc']));
			return FALSE;
		}
		ldap_set_option($dc,LDAP_OPT_PROTOCOL_VERSION,3);
		ldap_set_option($dc,LDAP_OPT_REFERRALS,0);
		if (!@ldap_bind($dc,$auth['rdn'],$auth['pw'])) {
			// Bind failed
			trigger_error(self::TEXT_LDAPBind);
			return FALSE;
		}
		$result=ldap_search($dc,$auth['base_dn'],'uid='.$id);
		if (ldap_count_entries($dc,$result)!=1)
			// Didn't return a single record
			return FALSE;
		// Bind using credentials
		$info=ldap_get_entries($dc,$result);
		if (!@ldap_bind($dc,$info[0]['dn'],$pw))
			// Bind failed
			return FALSE;
		@ldap_unbind($dc);
		// Verify user ID
		return $info[0]['uid'][0]==$id;
	}

	/**
		Basic HTTP authentication
			@return boolean
			@param $auth mixed
			@param $realm string
			@public
	**/
	static function basic($auth,$realm=NULL) {
		if (is_null($realm))
			$realm=$_SERVER['REQUEST_URI'];
		if (isset($_SERVER['PHP_AUTH_USER']))
			return call_user_func(
				array('self',$auth),
				$_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']
			);
		if (PHP_SAPI!='cli')
			header(self::HTTP_WebAuth.': Basic realm="'.$realm.'"',TRUE,401);
		return FALSE;
	}

	/**
		Class initializer
			@public
	**/
	static function onload() {
		if (!isset(self::$vars['AUTH']))
			// Authentication setup options
			self::$vars['AUTH']=NULL;
	}

}
