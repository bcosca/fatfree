<?php

namespace App;

class Pingback extends Controller {

	function get($f3) {
		// Client
		$test=new \F3\Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$test->expect(
			$loaded=extension_loaded('xmlrpc'),
			'XML-RPC extension loaded'
		);
		if ($loaded) {
			$pingback=new \F3\Web\Pingback;
			$source=$f3->get('BASE').'/pingback2?page=pingback/client';
			$test->expect(
				$f3->read($f3->get('UI').'pingback/server.htm'),
				'Read permalink contents'
			);
			$pingback->inspect($source);
			$test->expect(
				is_file($file=$f3->get('TEMP').$f3->hash($source).'.htm'),
				'Reply from pingback server'
			);
			$test->expect(
				$pingback->log(),
				'Transaction log available'
			);
			$test->expect(
				@file_get_contents($file)==
					\F3\View::instance()->render('pingback/client.htm'),
				'Read source contents'
			);
			@unlink($file);
			$pingback->inspect('http://example.com/');
			$test->expect(
				!is_file($file),
				'External source'
			);
			$source=$f3->get('BASE').'/pingback2?page=pingback/invalid';
			$pingback->inspect($source);
			$test->expect(
				!is_file($file=$f3->get('TEMP').$f3->hash($source).'.htm'),
				'Non-existent permalink'
			);
		}
		$f3->set('results',$test->results());
	}

	function post($f3) {
		\F3\Web\Pingback::instance()->listen(
			function($source,$text) use($f3) {
				$f3->write($f3->get('TEMP').$f3->hash($source).'.htm',$text);
			}
		);
	}

}
