<?php

namespace App;

class Pingback2 {

	function get($f3) {
		if ($f3->exists('GET.page'))
			echo \View::instance()->render($f3->get('GET.page').'.htm');
		die;
	}

}
