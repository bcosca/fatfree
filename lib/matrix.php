<?php

/**
	Generic array utilities for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Matrix
		@version 2.0.9
**/

//! Generic array utilities
class Matrix extends Base {

	/**
		Retrieve values from a specified column of a multi-dimensional
		array variable
			@return array
			@param $var array
			@param $col mixed
			@public
	**/
	static function pick(array $var,$col) {
		return array_map(
			function($row) use($col) {
				return $row[$col];
			},
			$var
		);
	}

	/**
		Rotate a two-dimensional array variable
			@return array
			@param $var array
			@public
	**/
	static function transpose(array $var) {
		$result=array();
		foreach ($var as $keyx=>$cols)
			foreach ($cols as $keyy=>$valy)
				$result[$keyy][$keyx]=$valy;
		return $result;
	}

	/**
		Sort a multi-dimensional array variable on a specified column
			@return array
			@param $var array
			@param $col mixed
			@param $order integer
			@public
	**/
	static function sort(array &$var,$col,$order=SORT_ASC) {
		uasort(
			$var,
			function($val1,$val2) use($col,$order) {
				$self=__CLASS__;
				list($v1,$v2)=array($val1[$col],$val2[$col]);
				$out=is_numeric($v1) && is_numeric($v2)?
					$self::sign($v1-$v2):strcmp($v1,$v2);
				if ($order==SORT_DESC)
					$out=-$out;
				return $out;
			}
		);
	}

	/**
		Change the key of a two-dimensional array element
			@param $var array
			@param $old string
			@param $new string
			@public
	**/
	static function changekey(array &$var,$old,$new) {
		$keys=array_keys($var);
		$vals=array_values($var);
		$keys[array_search($old,$keys)]=$new;
		$var=array_combine($keys,$vals);
	}

}
