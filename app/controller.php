<?php

namespace App;

class Controller {

	function beforeroute($f3) {
		$base=$f3->get('BASE');
		$uri=preg_replace('/^'.preg_quote($base,'/').'/','',$f3->get('URI'));
		if ($uri=='/router')
			$uri='/redir';
		elseif (preg_match('/\/openid2\b/',$uri))
			$uri='/openid';
		$f3->set('active',$f3->get('menu["'.$uri.'"]'));
	}

	function afterroute($f3) {
		// Collect errors.
		if ($results=$f3->get('results')) {
			$f3->set('errors', array_reduce($results, function($carry, $item){
				if (!$item['status']) $carry[]=$item;
				return $carry;
			}, array()));
		}

		echo \Preview::instance()->render('layout.htm');
	}

}

class Map {

	function get() {
	}

	function post() {
	}

}
