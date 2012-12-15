<?php

//! Unit testing tools
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
