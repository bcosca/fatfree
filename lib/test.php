<?php

/*

	Copyright (c) 2009-2015 F3::Factory/Bong Cosca, All rights reserved.

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

//! Unit test kit
class Test {

	//@{ Reporting level
	const
		FLAG_False=0,
		FLAG_True=1,
		FLAG_Both=2;
	//@}

	protected
		//! Test results
		$data=array(),
		//! Success indicator
		$passed=TRUE;

	/**
	*	Return test results
	*	@return array
	**/
	function results() {
		return $this->data;
	}

	/**
	*	Return FALSE if at least one test case fails
	*	@return bool
	**/
	function passed() {
		return $this->passed;
	}

	/**
	*	Evaluate condition and save test result
	*	@return object
	*	@param $cond bool
	*	@param $text string
	**/
	function expect($cond,$text=NULL) {
		$out=(bool)$cond;
		if ($this->level==$out || $this->level==self::FLAG_Both) {
			$data=array('status'=>$out,'text'=>$text,'source'=>NULL);
			foreach (debug_backtrace() as $frame)
				if (isset($frame['file'])) {
					$data['source']=Base::instance()->
						fixslashes($frame['file']).':'.$frame['line'];
					break;
				}
			$this->data[]=$data;
		}
		if (!$out && $this->passed)
			$this->passed=FALSE;
		return $this;
	}

	/**
	*	Append message to test results
	*	@return NULL
	*	@param $text string
	**/
	function message($text) {
		$this->expect(TRUE,$text);
	}

	/**
	*	Class constructor
	*	@return NULL
	*	@param $level int
	**/
	function __construct($level=self::FLAG_Both) {
		$this->level=$level;
	}

}
