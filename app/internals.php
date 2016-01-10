<?php

namespace App;

class Internals extends Controller {

	function get($f3) {
		$test=new \F3\Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$test->expect(
			PHP_VERSION,
			'PHP version '.PHP_VERSION
		);
		$test->expect(
			PHP_SAPI,
			'SAPI: '.PHP_SAPI
		);
		$test->expect(
			!@strpos(),
			'Intentional error'
		);
		$f3->foo='bar';
		$test->expect(
			$f3===\F3\Base::instance() && @\F3\Base::instance()->foo=='bar',
			'Same framework instance returned'
		);
		$test->expect(
			$f3->fixslashes('C:\xyz\abc.php')=='C:/xyz/abc.php',
			'Coerce directory separators'
		);
		$test->expect(
			$f3->split('a|bc;d,efg')==array('a','bc','d','efg'),
			'Split comma-, semi-colon, or pipe-separated string'
		);
		$test->expect(
			$f3->stringify(9)==='9' &&
			$f3->stringify(1.5)==='1.5' &&
			$f3->stringify(-7)==='-7' &&
			$f3->stringify(2e3)==='2000',
			'Convert number to exportable string'
		);
		$test->expect(
			$f3->stringify('hello, world')=='\'hello, world\'',
			'Convert string to exportable string'
		);
		$test->expect(
			$f3->stringify(array(1,'a',0.5))==
				'array(1,\'a\',0.5)' &&
			$f3->stringify(array('x'=>'hello','y'=>'world'))==
				'array(\'x\'=>\'hello\',\'y\'=>\'world\')',
			'Convert array to exportable string'
		);
		$obj=new \stdClass;
		$obj->hello='world';
		$test->expect(
			$f3->stringify($obj)==
				'stdClass::__set_state(array(\'hello\'=>\'world\'))',
			'Convert object to exportable string'
		);
		$test->expect(
			$f3->csv(array(1,'a',0.5))=='1,\'a\',0.5',
			'Flatten and convert array to CSV string'
		);
		$test->expect(
			$f3->snakecase('helloWorld')=='hello_world',
			'Snake-case'
		);
		$test->expect(
			$f3->camelcase('hello_world')=='helloWorld',
			'Camel-case'
		);
		$hash=array();
		$found=FALSE;
		for ($i=0;$i<10000;$i++)
			if (is_int(array_search(
				$f3->hash(str_shuffle(uniqid(NULL,TRUE))),$hash))) {
				$found=TRUE;
				break;
			}
		$test->expect(
			!$found,
			'No hash() collisions'
		);
		$_GET=array('foo'=>'ok<h1>foo</h1><p>bar<span>baz</span></p>');
		$f3->scrub($_GET);
		$test->expect(
			$f3->get('GET["foo"]')=='okfoobarbaz',
			'Scrub all HTML tags'
		);
		$_GET=array('foo'=>'ok<h1>foo</h1><p>bar<span>baz</span></p>');
		$f3->scrub($_GET,'p,span');
		$test->expect(
			$f3->get('GET["foo"]')=='okfoo<p>bar<span>baz</span></p>',
			'Scrub specific HTML tags'
		);
		$_GET=array('foo'=>'ok<h1>foo</h1><p>bar<span>baz</span></p>');
		$f3->scrub($_GET,'*');
		$test->expect(
			$f3->get('GET["foo"]')=='ok<h1>foo</h1><p>bar<span>baz</span></p>',
			'Pass-thru HTML tags'
		);
		$var='"hello world", a'.chr(8).
			'<$20 or €20> donation helps improve'.chr(0).' this software';
		$f3->scrub($var);
		$test->expect(
			$var=='"hello world", a donation helps improve this software',
			'Remove control characters'
		);
		$test->expect(
			$f3->encode('I\'ll "walk" the <b>dog</b> now™')==
				($out='I\'ll &quot;walk&quot; the &lt;b&gt;dog&lt;'.
				'/b&gt; now™'),
			'Encode HTML entities'
		);
		$test->expect(
			$f3->encode('I\'ll "walk" the <b>dog</b> now™')==
				($out='I\'ll &quot;walk&quot; the &lt;b&gt;dog&lt;'.
				'/b&gt; now™'),
			'Encode HTML entities'
		);
		$test->expect(
			$f3->decode($out)=='I\'ll "walk" the <b>dog</b> now™',
			'Decode HTML entities'
		);
		$obj=\F3\Matrix::instance();
		$test->expect(
			\F3\Registry::exists($class=get_class($obj)),
			'instance() saves object to framework registry'
		);
		$test->expect(
			$f3->constants($f3,'REQ_')==array('SYNC'=>\F3\Base::REQ_SYNC,'AJAX'=>\F3\Base::REQ_AJAX),
			'Fetch constants from a class (object)'
		);
		$test->expect(
			$f3->constants('\F3\ISO','CC_')==\F3\ISO::instance()->countries(),
			'Fetch constants from a class (string)'
		);
		$f3->set('results',$test->results());
	}

}
