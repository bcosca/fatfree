<?php

namespace App;

class Redir {

	function get($f3) {
		$f3->write($file=$f3->get('TEMP').'redir',microtime(TRUE));
		$f3->reroute('/router');
	}

}
