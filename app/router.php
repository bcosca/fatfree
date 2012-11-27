<?php

namespace App;

class Router extends Controller {

	function callee() {
		\Base::instance()->set('called',TRUE);
	}

	function get() {
		$f3=\Base::instance();
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$test->expect(
			$result=is_file($file=$f3->get('TEMP').'redir') &&
			$val=$f3->read($file),
			'Rerouted to this page'.($result?(': '.
				sprintf('%.1f',(microtime(TRUE)-$val)*1e3).'ms'):'')
		);
		if (is_file($file))
			$f3->unlink($file);
		$f3->set('ROUTES',array());
		$f3->route('GET /',
			function() use($f3) {
				$f3->set('bar','foo');
			}
		);
		$f3->mock('GET /');
		$test->expect(
			$f3->get('bar')=='foo',
			'Routed to anonymous/lambda function'
		);
		$f3->set('ROUTES',array());
		$f3->route('GET /',__NAMESPACE__.'\please');
			function please() {
				\Base::instance()->set('send','money');
			}
		$f3->mock('GET /');
		$test->expect(
			$f3->get('send')=='money',
			'Routed to regular namespaced function'
		);
		$f3->set('ROUTES',array());
		$f3->set('QUIET',FALSE);
		$f3->map('/dummy','NS\C');
		$ok=TRUE;
		$list='';
		foreach (explode('|',\Base::VERBS) as $verb) {
			$f3->mock($verb.' /dummy',array('a'=>'hello'));
			if ($f3->get('route')!=$verb ||
				preg_match('/GET|HEAD/',strtoupper($verb)) &&
				$f3->get('body') && !parse_url($f3->get('URI'),PHP_URL_QUERY))
				$ok=FALSE;
			else
				$list.=($list?', ':'').$verb;
		}
		$f3->set('QUIET',TRUE);
		$test->expect(
			$ok,
			'Methods supported'.($list?(': '.$list):'')
		);
		$f3->set('BODY','');
		$f3->mock('PUT /dummy');
		$test->expect(
			$f3->exists('BODY'),
			'Request body available'
		);
		$f3->set('ERROR',NULL);
		$f3->set('ROUTES',array());
		$f3->route('GET /food/@id',
			function() use($f3) {
				$f3->set('id',$f3->get('PARAMS')['id']);
			}
		);
		$f3->mock('GET /food/fish');
		$test->expect(
			$f3->get('id')=='fish',
			'Parameter in route captured'
		);
		$f3->mock('GET /food/bread');
		$test->expect(
			$f3->get('id')=='bread',
			'Different parameter in route'
		);
		$f3->route('GET /food/@id/@quantity',
			function() use($f3) {
				$f3->set('id',$f3->get('PARAMS')['id']);
				$f3->set('quantity',$f3->get('PARAMS')['quantity']);
			}
		);
		$f3->mock('GET /food/beef/789');
		$test->expect(
			$f3->get('id')=='beef' && $f3->get('quantity')==789,
			'Multiple parameters'
		);
		$f3->mock('GET /food/nuts?a=1&b=3&c=5');
		$test->expect(
			$_GET==array('a'=>1,'b'=>3,'c'=>5),
			'Query string mocked'
		);
		$f3->mock('GET /food/chicken/999?d=246&e=357',array('f'=>468));
		$test->expect(
			$_GET==array('d'=>246,'e'=>357,'f'=>468),
			'Query string and mock arguments merged'
		);
		$test->expect(
			$f3->get('id')=='chicken' && $f3->get('quantity')==999,
			'Route parameters captured along with query'
		);
		$f3->mock('GET /food/%C3%B6%C3%A4%C3%BC/123');
		$test->expect(
			$f3->get('id')=='öäü' && $f3->get('quantity')==123,
			'Unicode characters in URL (PCRE version: '.PCRE_VERSION.')'
		);
		$f3->set('ROUTES',array());
		$mark=microtime(TRUE);
		$f3->route('GET /nothrottle',
			function() {
				echo 'Perfect wealth becomes me';
			}
		);
		$f3->mock('GET /nothrottle');
		$test->expect(
			$elapsed=microtime(TRUE)-$mark,
			'Page rendering baseline: '.
				sprintf('%.1f',$elapsed*1e3).'ms'
		);
		$f3->set('ROUTES',array());
		$mark=microtime(TRUE);
		$f3->route('GET /throttled',
			function() {
				echo 'Perfect wealth becomes me';
			},
			0, // don't cache
			$throttle=16 // 8Kbps
		);
		$f3->mock('GET /throttled');
		$test->expect(
			$elapsed=microtime(TRUE)-$mark,
			'Same page throttled @'.$throttle.'Kbps '.
				'(~'.(1000/$throttle).'ms): '.
				sprintf('%.1f',$elapsed*1e3).'ms'
		);
		$f3->clear('called');
		$f3->call('App\Router->callee');
		$test->expect(
			$f3->get('called'),
			'Call method (NS\Class->method)'
		);
		$f3->clear('called');
		$obj=new Router;
		$f3->call(array($obj,'callee'));
		$test->expect(
			$f3->get('called'),
			'Call method (PHP array format)'
		);
		$f3->clear('called');
		$f3->call('App\callee');
		$test->expect(
			$f3->get('called'),
			'Call PHP function'
		);
		$f3->clear('called');
		$f3->call(function() {
			\Base::instance()->set('called',TRUE);
		});
		$test->expect(
			$f3->get('called'),
			'Call lambda function'
		);
		$f3->set('results',$test->results());
	}

}

function callee() {
	\Base::instance()->set('called',TRUE);
}
