<?php

namespace App;

class Hive extends Controller {

	function get($f3) {
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$f3->set('i',123);
		$test->expect(
			$f3->get('i')===123 && $f3['i']===123 && $f3->i===123,
			'Value assigned and retrieved'
		);
		$f3->clear('i');
		$test->expect(
			!$f3->exists('i') && empty($f3['i']) && empty($f3->i),
			'Value cleared and verified'
		);
		$f3->set('f',3.14);
		$test->expect(
			$f3->get('f')===3.14 && $f3['f']===$f3->f &&
			$f3->f===$f3->get('f'),
			'Float value'
		);
		$f3->set('b',TRUE);
		$test->expect(
			$f3->get('b')===TRUE && $f3['b']===$f3->b &&
			$f3->b===$f3->get('b'),
			'Boolean value'
		);
		$f3->set('e',6.78e-3);
		$test->expect(
			$f3->get('e')===6.78e-3 && $f3['e']===$f3->e &&
			$f3->e===$f3->get('e'),
			'Scientific notation'
		);
		$f3->set('array',array('w'=>FALSE,'x'=>'abc','y'=>123,'z'=>4.56));
		$test->expect(
			$f3->get('array')===
				array('w'=>FALSE,'x'=>'abc','y'=>123,'z'=>4.56) &&
			$f3['array']===$f3->array && $f3->array===$f3->get('array'),
			'Array value'
		);
		$test->expect(
			$f3->get('array.w')===FALSE &&
			$f3->get('array[w]')===FALSE &&
			$f3->get('array[\'w\']')===FALSE &&
			$f3->get('array["w"]')===FALSE &&
			$f3['array']['w']===FALSE &&
			$f3->array['w']===FALSE,
			'Boolean element'
		);
		$test->expect(
			$f3->get('array.x')=='abc' &&
			$f3->get('array[x]')=='abc' &&
			$f3->get('array[\'x\']')=='abc' &&
			$f3->get('array["x"]')=='abc' &&
			$f3['array']['x']=='abc' &&
			$f3->array['x']=='abc',
			'String element'
		);
		$test->expect(
			$f3->get('array.y')===123 &&
			$f3->get('array[y]')===123 &&
			$f3->get('array[\'y\']')===123 &&
			$f3->get('array["y"]')===123 &&
			$f3['array']['y']===123 &&
			$f3->array['y']===123,
			'Integer element'
		);
		$test->expect(
			$f3->get('array.z')===4.56 &&
			$f3->get('array[z]')===4.56 &&
			$f3->get('array[\'z\']')===4.56 &&
			$f3->get('array["z"]')===4.56 &&
			$f3['array']['z']===4.56 &&
			$f3->array['z']===4.56,
			'Float element'
		);
		$f3->set('array',array('w'=>FALSE,'x'=>'qrs','y'=>123,'z'=>4.56));
		$test->expect(
			$f3->get('array')===
				array('w'=>FALSE,'x'=>'qrs','y'=>123,'z'=>4.56) &&
			$f3['array']===$f3->array &&
			$f3->array===$f3->get('array'),
			'Array altered'
		);
		$f3['array']=array('a'=>array('b'=>array('c'=>'hello')));
		$test->expect(
			$f3->get('array')===array('a'=>array('b'=>array('c'=>'hello'))) &&
			$f3['array']===$f3->array &&
			$f3->array===$f3->get('array'),
			'Value replaced; now a multidimensional array'
		);
		$test->expect(
			$f3->get('array.a')===array('b'=>array('c'=>'hello')) &&
			$f3->get('array[a]')===array('b'=>array('c'=>'hello')) &&
			$f3->get('array.a[b]')===array('c'=>'hello') &&
			$f3->get('array[a].b')===array('c'=>'hello') &&
			$f3->get('array[a][\'b\']')===array('c'=>'hello') &&
			$f3->get('array.a.b.c')==='hello' &&
			$f3->get('array[a][b][c]')==='hello' &&
			$f3->get('array["a"]')===array('b'=>array('c'=>'hello')) &&
			$f3->get('array["a"]["b"]')===array('c'=>'hello') &&
			$f3->get('array["a"][\'b\']["c"]')==='hello' &&
			$f3->get('array["a"]["b"]["c"]')==='hello' &&
			$f3['array']['a']===$f3->array['a'] &&
			$f3['array']['a']['b']===$f3->array['a']['b'] &&
			$f3['array']['a']['b']['c']===$f3->array['a']['b']['c'] &&
			$f3->array['a']===$f3->get('array.a') &&
			$f3->array['a']['b']===$f3->get('array.a.b') &&
			$f3->array['a']['b']['c']===$f3->get('array.a.b.c'),
			'Array access; array literal, and mixed'
		);
		$f3->set('a',function() {
			return 'hello, world';
		});
		$test->expect(
			get_class($func=$f3->get('a'))=='Closure' &&
			get_class($f3['a'])=='Closure' &&
			get_class($f3->a)=='Closure' &&
			$func()==='hello, world' &&
			$f3['a']()==='hello, world' &&
			$f3->a()==='hello, world',
			'Closure assigned'
		);
		$f3->set('a',new \stdClass);
		$f3->a->hello='world';
		$test->expect(
			is_object($f3->get('a')) && is_object($f3->a) &&
			$f3->get('a')->hello=='world' &&
			$f3->get('a->hello')=='world' &&
			$f3['a']->hello=='world' &&
			$f3->a->hello=='world',
			'Value replaced; now an object'
		);
		$test->expect(
			$f3->exists('a') && $f3->exists('a->hello',$hello) &&
				$hello=='world' && isset($f3['a']) && isset($f3->a) &&
				isset($f3['a']->hello) && isset($f3->a->hello),
			'Existence confirmed'
		);
		$f3->set('a->z.x','foo');
		$test->expect(
			is_array($f3->get('a->z')) && $f3->get('a->z.x')=='foo' &&
			is_array($f3['a']->z) && $f3['a']->z['x']=='foo' &&
			is_array($f3->a->z) && $f3->a->z['x']=='foo',
			'Object property containing array'
		);
		$f3->set('a->z.qux',function() {
			return 'hello';
		});
		$test->expect(
			is_callable($f3->get('a->z.qux')) &&
			is_callable($f3['a']->z['qux']) &&
			is_callable($f3->a->z['qux']),
			'Object property containing lambda function'
		);
		$f3->set('i.j','bar');
		$test->expect(
			is_array($f3->get('i')) && $f3->get('i.j')=='bar' &&
			is_array($f3['i']) && $f3['i']['j']=='bar' &&
			is_array($f3->i) && $f3->i['j']=='bar',
			'Multilevel array'
		);
		$f3->clear('i');
		$f3->set('i.j.k','foo');
		$test->expect(
			is_array($f3->get('i')) &&
			is_array($f3['i']) && is_array($f3->i) &&
			is_array($f3->get('i.j')) &&
			is_array($f3['i']['j']) && is_array($f3->i['j']) &&
			$f3->get('i.j.k')=='foo' &&
			$f3['i']['j']['k']=='foo' && $f3->i['j']['k']=='foo',
			'Modified array'
		);
		$test->expect(
			is_null($f3->get('l.m.n')) &&
			is_null($f3['l']['m']['n']) && is_null($f3->l['m']['n']) &&
			!is_array($f3->get('l')) &&
			!is_array($f3['l']) && !is_array($f3->l) &&
			!is_array($f3->get('l.m')) &&
			!is_array($f3['l']['m']) && !is_array($f3->l['m']) &&
			is_null($f3->get('l.m.n')) &&
			is_null($f3['l']['m']['n']) && is_null($f3->l['m']['n']),
			'Non-existent array'
		);
		$f3->set('domains',
			array(
				'google.com'=>'Google',
				'yahoo.com'=>'Yahoo'
			)
		);
		$test->expect(
			$f3->get('domains')==
			array(
				'google.com'=>'Google',
				'yahoo.com'=>'Yahoo'
			) && $f3->domains==$f3->get('domains') &&
			$f3->get('domains[google.com]')=='Google' &&
			$f3['domains']['google.com']=='Google' &&
			$f3->domains['google.com']=='Google' &&
			$f3->get('domains[yahoo.com]')=='Yahoo' &&
			$f3['domains']['yahoo.com']=='Yahoo' &&
			$f3->domains['yahoo.com']=='Yahoo',
			'Array keys containing dot symbol'
		);
		$test->expect(
			!$f3->exists('j') && empty($f3->j),
			'Non-existent variable'
		);
		$f3->set('x','open sesame!');
		$x=&$f3->ref('x');
		$test->expect(
			$x=='open sesame!' && $f3->get('x')=='open sesame!' &&
			$f3['x']=='open sesame!' && $f3->x=='open sesame!',
			'Reference to variable'
		);
		$x=123;
		$test->expect(
			$x===123 && $f3->get('x')===123 && $f3['x']===123 && $f3->x===123,
			'Indirect assignment'
		);
		$f3->set('LOCALES','dict/');
		$test->expect(
			$f3->exists('tqbf') && isset($f3->tqbf),
			'Retrieve from dictionary'
		);
		$f3->set('x','foo');
		$f3->copy('x','y');
		$test->expect(
			$f3->get('x')===$f3->get('y') &&
			$f3['x']===$f3['y'] && $f3->x===$f3->y,
			'Copy variable'
		);
		$f3->concat('y',' bar');
		$test->expect(
			$f3->get('y')=='foo bar' &&
			$f3['y']=='foo bar' && $f3->y=='foo bar',
			'String concatenation'
		);
		$f3->clear('z');
		$f3->push('z',1);
		$test->expect(
			$f3->get('z')==array(1) &&
			$f3->push('z',2)==2 &&
			$f3->get('z')==array(1,2),
			'Array push create'
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
