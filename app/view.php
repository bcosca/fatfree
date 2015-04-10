<?php

namespace App;

class View extends Controller {

	function get($f3) {
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$view=\View::instance();
		$raw='<&>"\'ä';
		$escaped="&lt;&amp;&gt;&quot;'ä";
		$escapedTwice="&amp;lt;&amp;amp;&amp;gt;&amp;quot;'ä";
		$f3->set('test',$raw);
		$test->expect(
			$view->esc($raw)==$escaped,
			'Encoding of ' . $raw
		);
		$test->expect(
			$view->esc($view->esc($raw))==$escapedTwice,
			'Double encoding of ' . $raw
		);
		$test->expect(
			$view->raw($escaped)==$raw,
			'Decoding of ' . $escaped
		);
		$test->expect(
			$view->raw($view->raw($escapedTwice))==$raw,
			'Double decoding of ' . $escapedTwice
		);
		$test->expect(
			$view->render('view/test0.php')==$escaped.'-'.$escaped,
			'Included template'
		);
		$test->expect(
			$view->render('view/test1.php')==$escaped.'-'.$escaped,
			'Embedded view with implicit HIVE'
		);
		$test->expect(
			$view->render('view/test2.php')==$escaped.'-'.$escaped,
			'Embedded view with custom HIVE based on escaped HIVE'
		);
		$test->expect(
			$view->render('view/test3.php')==$escaped.'-'.$raw,
			'Embedded view with full custom HIVE'
		);
		$f3->set('results',$test->results());
	}

}
