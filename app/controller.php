<?php

namespace App;

class Controller {

	function beforeroute() {
		$f3=\Base::instance();
		$uri=$f3->get('URI');
		if ($uri=='/router')
			$uri='/redir';
		elseif (preg_match('/\/openid2\b/',$uri))
			$uri='/openid';
		$f3->set('active',$f3->get('menu["'.$uri.'"]'));
		$f3->set('QUIET',TRUE);
	}

	function afterroute() {
		$f3=\Base::instance();
		$f3->set('QUIET',FALSE);
		echo \View::instance()->render('layout.htm');
	}

}
