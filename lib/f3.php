<?php

/*

	Copyright (c) 2009-2015 F3::Factory/Bong Cosca, All rights reserved.

	This file is part of the Fat-Free Framework (http://fatfreeframework.com).

	This is free software: you can redistribute it and/or modify it under the
	terms of the GNU General Public License as published by the Free Software
	Foundation, either version 3 of the License, or later.

	Please see the LICENSE file for more information.

*/

//! Legacy mode enabler
class F3 {

	static
		//! Framework instance
		$fw;

	/**
	*	Forward function calls to framework
	*	@return mixed
	*	@param $func callback
	*	@param $args array
	**/
	static function __callstatic($func,array $args) {
		if (!self::$fw)
			self::$fw=Base::instance();
		return call_user_func_array(array(self::$fw,$func),$args);
	}

}
