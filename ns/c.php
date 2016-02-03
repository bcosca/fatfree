<?php

namespace NS;

class C extends \F3\Prefab {

	// Emulate HTTP method so we can test map()
	function __call($name,$args) {
		$f3=$args[0];
		$f3->set('route',strtoupper($name));
		$f3->set('body',$f3->get('BODY'));
	}

}
