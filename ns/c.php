<?php

namespace NS;

class C {

	// Emulate HTTP method so we can test map()
	function __call($name,$args) {
		$f3=\Base::instance();
		$f3->set('route',strtoupper($name));
		$f3->set('body',$f3->get('BODY'));
	}

}
