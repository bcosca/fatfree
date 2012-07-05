<?php

/**
	Data validation plugin for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2012 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Data
		@version 2.0.12
**/

//! Data validators
class Data extends Base {

	/**
		Return TRUE if string is a valid e-mail address with option to check
		if DNS MX records exist for the domain
			@return boolean
			@param $text string
			@param $mx boolean
			@public
	**/
	static function validEmail($text,$mx=FALSE) {
		return is_string(filter_var($text,FILTER_VALIDATE_EMAIL)) &&
			(!$mx ||
				extension_loaded('sockets') &&
				@fsockopen(substr($text,
					strpos($text,'@')+1),25,$errno,$errstr,5) ||
				getmxrr(substr($text,strrpos($text,'@')+1),$hosts));
	}

	/**
		Return TRUE if string is a valid URL
			@return boolean
			@param $text string
			@public
	**/
	static function validURL($text) {
		return is_string(filter_var($text,FILTER_VALIDATE_URL));
	}

	/**
		Return TRUE if string and generated CAPTCHA image are identical
			@return boolean
			@param $text string
			@public
	**/
	static function validCaptcha($text) {
		$result=FALSE;
		if (isset($_SESSION['captcha'])) {
			$result=($text==$_SESSION['captcha']);
			unset($_SESSION['captcha']);
		}
		return $result;
	}

}
