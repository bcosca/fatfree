<?php

namespace App;

class Pingback2 {

	function get($f3) {
		die(\F3\View::instance()->render($f3->get('GET.page').'.htm'));
	}

}
