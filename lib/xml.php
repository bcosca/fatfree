<?php

/**
	XML data conversion tools for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Network
		@version 2.0.9
**/

//! XML data conversion tools
class XML extends Base {

	static
		//! XML translation table
		$xmltab=array();

	/**
		Return XML translation table
			@return array
			@param $latin boolean
			@private
	**/
	private static function table($latin=FALSE) {
		if (!isset(self::$xmltab[(int)$latin])) {
			$xl8=get_html_translation_table(HTML_ENTITIES,ENT_COMPAT);
			foreach ($xl8 as $key=>$val)
				$tab[$latin?$val:$key]='&#'.ord($key).';';
			self::$xmltab[(int)$latin]=$tab;
		}
		return self::$xmltab[(int)$latin];
	}

	/**
		Convert plain text to XML entities
			@return string
			@param $str string
			@param $latin boolean
			@public
	**/
	static function encode($str,$latin=FALSE) {
		return strtr($str,self::table($latin));
	}

	/**
		Convert XML entities to plain text
			@return string
			@param $str string
			@param $latin boolean
			@public
	**/
	static function decode($str,$latin=FALSE) {
		return strtr($str,array_flip(self::table($latin)));
	}

}
