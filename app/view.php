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
		$test->expect(
			$view->render('view/hive_size.php',NULL,NULL)!=='0',
			'Default HIVE is not empty'
		);
		$test->expect(
			$view->render('view/hive_size.php',NULL,[])==='0',
			'Empty custom HIVE'
		);
		$test->expect(
			$view->render('view/hive_content.php',NULL,['fw'=>1,'hive'=>2,'implicit'=>3,'mime'=>4])
				=='a:4:{s:2:"fw";i:1;s:4:"hive";i:2;s:8:"implicit";i:3;s:4:"mime";i:4;}',
			'Variables $fw, $hive, $implicit and $mime are available'
		);
		$test->expect(
			$f3->CACHE===false,
			'Enable caching'
		);
		$cachedir=sprintf('tmp/cache/view_%s/',microtime(TRUE));
		$f3->CACHE='folder='.$cachedir;
		$file='view/cache.php';
		$test->expect(
			$view->render($file,null,['value'=>'nope'],0)==='nope',
			'Don\'t cache'
		);
		$test->expect(
			$view->render($file,null,['value'=>'cold'],2)==='cold',
			'Cache for two seconds'
		);
		$test->expect(
			$view->render($file,null,['value'=>'warm'],2)==='cold',
			'Load two second cached view'
		);
		sleep(3);
		$test->expect(
			$view->render($file,null,['value'=>'cold_again'],2)==='cold_again',
			'Replace outdated two second cached view'
		);
		$f3->CACHE=FALSE;
		foreach (glob($cachedir.'*') as $file) unlink($file);
		rmdir($cachedir);
		$f3->set('results',$test->results());
	}

}
