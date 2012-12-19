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

//! Data validator
class Audit extends Prefab {

	/**
		Return TRUE if string is a valid URL
		@return bool
		@param $str string
	**/
	function url($str) {
		return is_string(filter_var($str,FILTER_VALIDATE_URL));
	}

	/**
		Return TRUE if string is a valid e-mail address;
		Check DNS MX records if specified
		@return bool
		@param $str string
		@param $mx boolean
	**/
	function email($str,$mx=TRUE) {
		$hosts=array();
		return is_string(filter_var($str,FILTER_VALIDATE_EMAIL)) &&
			(!$mx || getmxrr(substr($str,strrpos($str,'@')+1),$hosts));
	}

	/**
		Return TRUE if string is a valid IPV4 address
		@return bool
		@param $addr string
	**/
	function ipv4($addr) {
		return filter_var($addr,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4);
	}

	/**
		Return TRUE if string is a valid IPV6 address
		@return bool
		@param $addr string
	**/
	function ipv6($addr) {
		return (bool)filter_var($addr,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6);
	}

	/**
		Return TRUE if IP address is within private range
		@return bool
		@param $addr string
	**/
	function isprivate($addr) {
		return !(bool)filter_var($addr,FILTER_VALIDATE_IP,
			FILTER_FLAG_IPV4|FILTER_FLAG_IPV6|FILTER_FLAG_NO_PRIV_RANGE);
	}

	/**
		Return TRUE if IP address is within reserved range
		@return bool
		@param $addr string
	**/
	function isreserved($addr) {
		return !(bool)filter_var($addr,FILTER_VALIDATE_IP,
			FILTER_FLAG_IPV4|FILTER_FLAG_IPV6|FILTER_FLAG_NO_RES_RANGE);
	}

	/**
		Return TRUE if IP address is neither private nor reserved
		@return bool
		@param $addr string
	**/
	function ispublic($addr) {
		return (bool)filter_var($addr,FILTER_VALIDATE_IP,
			FILTER_FLAG_IPV4|FILTER_FLAG_IPV6|
			FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE);
	}

}
