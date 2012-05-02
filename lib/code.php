<?php

/**
	Development tools for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2012 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Code
		@version 2.0.10
**/

//! Development tools
class Code extends Base {

	/**
		Highlight syntax in string
			@param $str string
			@param $php bool
			@public
	**/
	static function highlight($str,$php=TRUE) {
		return $php?
			highlight_string($str,TRUE):
			preg_replace('/&lt;\?php&nbsp;|\?&gt;/s','',
				highlight_string('<?php '.$str,TRUE));
	}

	/**
		Return HTML-friendly dump of PHP expression
			@return string
			@param $expr mixed
			@param $echo bool
			@param $highlight bool
			@public
	**/
	static function dump($expr,$echo=TRUE,$highlight=TRUE) {
		ob_start();
		var_dump($expr);
		$out=ob_get_clean();
		$out=$highlight?
			self::highlight($out,FALSE):('<code>'.$out.'</code>'."\n");
		if ($echo)
			echo $out;
		else
			return $out;
	}

	/**
		Convert snakecase string to camelcase
			@return string
			@param $str string
			@public
	**/
	static function camel($str) {
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
			@public
	**/
	static function snake($str) {
		return strtolower(preg_replace('/[[:upper:]]/','_\0',$str));
	}

}
