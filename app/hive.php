<?php

namespace App;

class Hive extends Controller {

	function get() {
		$f3=\Base::instance();
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$f3->set('i',123);
		$test->expect(
			$f3->get('i')===123,
			'Value assigned and retrieved'
		);
		$f3->clear('i');
		$test->expect(
			!$f3->exists('i'),
			'Value cleared and verified'
		);
		$f3->set('f',3.14);
		$test->expect(
			$f3->get('f')===3.14,
			'Float value'
		);
		$f3->set('b',TRUE);
		$test->expect(
			$f3->get('b')===TRUE,
			'Boolean value'
		);
		$f3->set('e',6.78e-3);
		$test->expect(
			$f3->get('e')===6.78e-3,
			'Scientific notation'
		);
		$f3->set('array',array('w'=>FALSE,'x'=>'abc','y'=>123,'z'=>4.56));
		$test->expect(
			$f3->get('array')===
				array('w'=>FALSE,'x'=>'abc','y'=>123,'z'=>4.56),
			'Array value'
		);
		$test->expect(
			$f3->get('array[w]')===FALSE &&
			$f3->get('array[\'w\']')===FALSE &&
			$f3->get('array["w"]')===FALSE,
			'Boolean element'
		);
		$test->expect(
			$f3->get('array[x]')=='abc' &&
			$f3->get('array[\'x\']')=='abc' &&
			$f3->get('array["x"]')=='abc',
			'String element'
		);
		$test->expect(
			$f3->get('array[y]')===123 &&
			$f3->get('array[\'y\']')===123 &&
			$f3->get('array["y"]')===123,
			'Integer element'
		);
		$test->expect(
			$f3->get('array[z]')===4.56 &&
			$f3->get('array[\'z\']')===4.56 &&
			$f3->get('array["z"]')===4.56,
			'Float element'
		);
		$f3->set('array.x','tuv');
		$f3->set('array',array('w'=>FALSE,'x'=>'qrs','y'=>123,'z'=>4.56));
		$test->expect(
			$f3->get('array')===
				array('w'=>FALSE,'x'=>'qrs','y'=>123,'z'=>4.56),
			'Array altered'
		);
		$f3->set('array',array('a'=>array('b'=>array('c'=>'hello'))));
		$test->expect(
			$f3->get('array')===array('a'=>array('b'=>array('c'=>'hello'))),
			'Value replaced; now a multidimensional array'
		);
		$test->expect(
			$f3->get('array[a]')===array('b'=>array('c'=>'hello')) &&
			$f3->get('array[a][\'b\']')===array('c'=>'hello') &&
			$f3->get('array[a][b][c]')==='hello' &&
			$f3->get('array["a"]')===array('b'=>array('c'=>'hello')) &&
			$f3->get('array["a"]["b"]')===array('c'=>'hello') &&
			$f3->get('array["a"][\'b\']["c"]')==='hello' &&
			$f3->get('array["a"]["b"]["c"]')==='hello',
			'Array access; array literal, and mixed'
		);
		$f3->set('a',function() {
			return 'hello, world';
		});
		$test->expect(
			get_class($func=$f3->get('a'))=='Closure' &&
			$func()==='hello, world',
			'Closure assigned'
		);
		$f3->set('a',new \stdClass);
		$f3->get('a')->hello='world';
		$test->expect(
			is_object($f3->get('a')) && $f3->get('a')->hello=='world' &&
			$f3->get('a->hello')=='world',
			'Value replaced; now an object'
		);
		$test->expect(
			$f3->exists('a') && $f3->exists('a->hello'),
			'Existence confirmed'
		);
		$test->expect(
			!$f3->exists('j'),
			'Non-existent variable'
		);
		$f3->set('x','open sesame!');
		$x=&$f3->ref('x');
		$test->expect(
			$x=='open sesame!' && $f3->get('x')=='open sesame!',
			'Reference to variable'
		);
		$x=123;
		$test->expect(
			$x===123 && $f3->get('x')===123,
			'Indirect assignment'
		);
		$test->expect(
			$f3->exists('tqbf'),
			'Retrieve from dictionary'
		);
		$f3->set('x','foo');
		$f3->copy('x','y');
		$test->expect(
			$f3->get('x')===$f3->get('y'),
			'Copy variable'
		);
		$f3->concat('y',' bar');
		$test->expect(
			$f3->get('y')=='foo bar',
			'String concatenation'
		);
		$f3->set('z',array(1,2,3));
		$f3->push('z',4);
		$test->expect(
			$f3->get('z')==array(1,2,3,4),
			'Array push'
		);
		$test->expect(
			$f3->pop('z')==4 &&
			$f3->get('z')==array(1,2,3),
			'Array pop'
		);
		$f3->unshift('z',0);
		$test->expect(
			$f3->get('z')==array(0,1,2,3),
			'Array shift'
		);
		$test->expect(
			$f3->shift('z')==0 &&
			$f3->get('z')==array(1,2,3),
			'Array unshift'
		);
		$f3->set('q',array('a'=>2,'b'=>5,'c'=>7));
		$f3->flip('q');
		$test->expect(
			$f3->get('q')==array(2=>'a',5=>'b',7=>'c'),
			'Array flip'
		);
		$f3->set('results',$test->results());
	}

}
