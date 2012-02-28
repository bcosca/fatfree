<?php

/**
	Unicode-aware string functions for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Unicode
		@version 2.0.9
**/

//! Unicode-aware string functions
class UTF extends Base {

	/*
		IMPORTANT: All string arguments of methods in this class must be
		encoded in UTF-8 to function properly
	*/

	/**
		Find position of first occurrence of a string (case-insensitive)
			@return mixed
			@param $stack string
			@param $needle string
			@param $ofs int
			@public
	**/
	static function stripos($stack,$needle,$ofs=0) {
		return self::strpos($stack,$needle,$ofs,TRUE);
	}

	/**
		Get string length
			@return int
			@param $str string
			@public
	**/
	static function strlen($str) {
		preg_match_all('/./us',$str,$matches);
		return count($matches[0]);
	}

	/**
		Find position of first occurrence of a string
			@return mixed
			@param $stack string
			@param $needle string
			@param $ofs int
			@param $case boolean
			@public
	**/
	static function strpos($stack,$needle,$ofs=0,$case=FALSE) {
		preg_match('/^(.*?)'.preg_quote($needle,'/').'/'.($case?'i':'').'us',
			self::substr($stack,$ofs),$match);
		return isset($match[1])?self::strlen($match[1]):FALSE;
	}

	/**
		Finds position of last occurrence of a string (case-insensitive)
			@return mixed
			@param $stack string
			@param $needle string
			@param $ofs int
			@public
	**/
	static function strripos($stack,$needle,$ofs=0) {
		return self::strrpos($stack,$needle,$ofs,TRUE);
	}

	/**
		Find position of last occurrence of a string
			@return mixed
			@param $stack string
			@param $needle string
			@param $ofs int
			@param $case boolean
			@public
	**/
	static function strrpos($stack,$needle,$ofs=0,$case=FALSE) {
		if (!$needle)
			return FALSE;
		$len=self::strlen($stack);
		$ptr=$ofs;
		while ($ptr<$len) {
			$sub=self::substr($stack,$ptr);
			if (!$sub || !preg_match('/^(.*?)'.
				preg_quote($needle,'/').'/'.($case?'i':'').'us',$sub,$match))
				break;
			$ofs=$ptr+self::strlen($match[1]);
			$ptr+=self::strlen($match[0]);
		}
		return $sub?$ofs:FALSE;
	}

	/**
		Returns part of haystack string from the first occurrence of
		needle to the end of haystack (case-insensitive)
			@return mixed
			@param $stack string
			@param $needle string
			@param $before boolean
			@public
	**/
	static function stristr($stack,$needle,$before=FALSE) {
		return strstr($stack,$needle,$before,TRUE);
	}

	/**
		Returns part of haystack string from the first occurrence of
		needle to the end of haystack
			@return mixed
			@param $stack string
			@param $needle string
			@param $before boolean
			@param $case boolean
			@public
	**/
	static function strstr($stack,$needle,$before=FALSE,$case=FALSE) {
		if (!$needle)
			return FALSE;
		preg_match('/^(.*?)'.preg_quote($needle,'/').'/'.($case?'i':'').'us',
			$stack,$match);
		return isset($match[1])?
			($before?$match[1]:self::substr($stack,self::strlen($match[1]))):
			FALSE;
	}

	/**
		Return part of a string
			@return mixed
			@param $str string
			@param $start int
			@param $len int
			@public
	**/
	static function substr($str,$start,$len=0) {
		if ($start<0) {
			$len=-$start;
			$start=self::strlen($str)+$start;
		}
		if (!$len)
			$len=self::strlen($str)-$start;
		return preg_match('/^.{'.$start.'}(.{0,'.$len.'})/us',$str,$match)?
			$match[1]:FALSE;
	}

	/**
		Count the number of substring occurrences
			@return int
			@param $stack string
			@param $needle string
			@public
	**/
	static function substr_count($stack,$needle) {
		preg_match_all('/'.preg_quote($needle,'/').'/us',$stack,
			$matches,PREG_SET_ORDER);
		return count($matches);
	}

}
