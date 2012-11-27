<?php

//! Data validator
class Audit {

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
		return filter_var($addr,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6);
	}

	/**
		Return TRUE if IP address is within private range
		@return bool
		@param $addr string
	**/
	function local($addr) {
		return !filter_var($addr,FILTER_VALIDATE_IP,
			FILTER_FLAG_IPV4|FILTER_FLAG_IPV6|FILTER_FLAG_NO_PRIV_RANGE);
	}

	/**
		Return TRUE if IP address is within reserved range
		@return bool
		@param $addr string
	**/
	function reserved($addr) {
		return !filter_var($addr,FILTER_VALIDATE_IP,
			FILTER_FLAG_IPV4|FILTER_FLAG_IPV6|FILTER_FLAG_NO_RES_RANGE);
	}

}
