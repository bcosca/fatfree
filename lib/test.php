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

//! Unit test kit
class Test {

	private
		//! Test results
		$data=array();

	/**
		Return test results
		@return array
	**/
	function results() {
		return $this->data;
	}

	/**
		Evaluate condition and save test result
		@return NULL
		@param $cond bool
		@param $text string
	**/
	function expect($cond,$text=NULL) {
		$out=(bool)$cond;
		foreach (debug_backtrace() as $frame)
			if (isset($frame['file'])) {
				$this->data[]=array(
					'status'=>$out,
					'text'=>$text,
					'source'=>Base::instance()->
						fixslashes($frame['file']).':'.$frame['line']
				);
				break;
			}
	}

}
