<?php

namespace App;

class Pingback2 {

	function get($f3) {
		header('X-Pingback: '.$f3->get('SCHEME').'://'.
			$_SERVER['SERVER_NAME'].($f3->get('BASE')?:'/').'pingback');
		echo \View::instance()->render('pingback.htm');
		die;
	}

}