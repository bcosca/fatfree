<?php

namespace App;

class Config extends Controller {

	function get() {
		$f3=\Base::instance();
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$f3->config('config.php');
		$test->expect(
			$f3->get('test')=='',
			'Empty string'
		);
		$test->expect(
			$f3->get('num')===123,
			'Integer'
		);
		$test->expect(
			$f3->get('str1')=='abc defg h ijk',
			'Unquoted string literal'
		);
		$test->expect(
			$f3->get('str2')=='abc',
			'Quoted string literal'
		);
		$test->expect(
			$f3->get('multi')=='this is a string that spans several lines',
			'Multi-line string'
		);
		$test->expect(
			$f3->get('list')==array(7,8,9),
			'Ordinary array'
		);
		$test->expect(
			$f3->get('hash')==array('x'=>1,'y'=>2,'z'=>3),
			'Array with named keys'
		);
		$test->expect(
			$f3->get('mix')==array('this',123.45,FALSE),
			'Array with mixed elements'
		);
		$test->expect(
			is_null($f3->get('const')) &&
			$f3->get('os')==PHP_OS,
			'PHP constants'
		);
		$test->expect(
			$f3->get('long')==='12345678901234567890' &&
			$f3->get('huge')===12345678901234567890,
			'Data types preserved'
		);
		$routes=array_keys($f3->get('ROUTES'));
		$test->expect(
			in_array('/go',$routes) &&
			in_array('/404',$routes) &&
			in_array('/inside/@series',$routes) &&
			in_array('/cached',$routes),
			'Routes declared'
		);
		$test->expect(
			in_array('/map',$routes),
			'ReST map declared'
		);
		$f3->set('results',$test->results());
	}

}
