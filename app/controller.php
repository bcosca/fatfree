<?php

namespace App;

class Controller {

	function beforeroute() {
		$f3=\Base::instance();
		$base=$f3->get('BASE');
		$uri=preg_replace('/^'.preg_quote($base,'/').'\b/','',
			$f3->get('URI'));
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
		header_remove();
		echo \View::instance()->render('layout.htm');
	}

}
