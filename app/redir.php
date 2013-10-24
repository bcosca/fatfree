<?php

namespace App;

class Redir {

	function get($f3) {
		$tmp=$f3->get('TEMP');
		if (!is_dir($tmp))
			mkdir($tmp);
		$f3->write($tmp.'redir',microtime(TRUE));
		$f3->reroute('/router');
	}

}
