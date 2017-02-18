<?php

namespace App;

class WS extends Controller {

	function get($f3) {
		$test=new \Test;
		$f3->set('JS',\Preview::instance()->render('ws.htm'));
		$f3->set('results',$test->results());
	}

}
