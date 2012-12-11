<?php

namespace App;

class Internals extends Controller {

	function get() {
		$f3=\Base::instance();
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$test->expect(
			PHP_VERSION,
			'PHP version '.PHP_VERSION
		);
		$f3->foo='bar';
		$test->expect(
			$f3===\Base::instance() && @\Base::instance()->foo=='bar',
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
			$f3->stringify(array(1,'a',0.5))==
				'array(0=>1,1=>\'a\',2=>0.5)' &&
			$f3->stringify(array('x'=>'hello','y'=>'world'))==
				'array(\'x\'=>\'hello\',\'y\'=>\'world\')',
			'Convert array to exportable string'
		);
		$test->expect(
			$f3->stringify(new \stdClass)=='stdClass::__set_state()',
			'Convert object to exportable string'
		);
		$test->expect(
			$f3->csv(array(1,'a',0.5))=='1,\'a\',0.5',
			'Flatten and convert array to CSV string'
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
		$var='"hello world", a'.chr(8).
			'<$20 or €20> donation helps improve'.chr(0).' this software';
		$f3->scrub($var);
		$test->expect(
			$var=='"hello world", a donation helps improve this software',
			'Remove control characters'
		);
		$test->expect(
			$f3->encode('I\'ll "walk" the <b>dog</b> now™')==
				($out='I\'ll &quot;walk&quot; the &lt;b&gt;dog&lt;/b&gt; now&trade;'),
			'Encode HTML entities'
		);
		$test->expect(
			$f3->decode($out)=='I\'ll "walk" the <b>dog</b> now™',
			'Decode HTML entities'
		);
		$text=\Text::instance();
		$test->expect(
			\Registry::exists('Text'),
			'instance() saves object to framework registry'
		);
		unset($text);
		$test->expect(
			\Registry::exists('Text'),
			'Destruction of object removes instance from registry'
		);
		$f3->set('results',$test->results());
	}

}
