<?php

//! Legacy mode
class F3 {

	static
		$fw;

	static function __callstatic($func,$args) {
		if (!self::$fw)
			$fw=Base::instance();
		return call_user_func_array(array($fw,$func),$args);
	}

}
