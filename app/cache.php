<?php

namespace App;

class Cache extends Controller {

	function get() {
		$f3=\Base::instance();
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$backend=$f3->set('CACHE',FALSE);
		$test->expect(
			!$backend,
			'Cache engine disabled: '.$f3->stringify($backend)
		);
		$backend=$f3->set('CACHE','invalid');
		$test->expect(
			$backend!='invalid',
			'Invalid backend specified (fallback invoked)'
		);
		$test->expect(
			$backend,
			'Cache backend '.$f3->stringify($backend).' detected'
		);
		$cache=\Cache::instance();
		$cache->set($f3->hash('foo'),'bar');
		$test->expect(
			$f3->get('foo')=='bar',
			'Retrieve previously cached entry'
		);
		$ttl=1;
		$mark=microtime(TRUE);
		$cache->set('a',1,$ttl);
		$test->expect(
			$cache->get('a')===1,
			'Integer: '.sprintf('%.1f',(microtime(TRUE)-$mark)*1e3).'ms'
		);
		$mark=microtime(TRUE);
		$cache->set('b',2.34,$ttl);
		$test->expect(
			$cache->get('b')===2.34,
			'Float: '.sprintf('%.1f',(microtime(TRUE)-$mark)*1e3).'ms'
		);
		$mark=microtime(TRUE);
		$cache->set('c',TRUE,$ttl);
		$test->expect(
			$cache->get('c')===TRUE,
			'Boolean: '.sprintf('%.1f',(microtime(TRUE)-$mark)*1e3).'ms'
		);
		$mark=microtime(TRUE);
		$cache->set('d',array('hello','world'),$ttl);
		$test->expect(
			$cache->get('d')==array('hello','world'),
			'Array: '.sprintf('%.1f',(microtime(TRUE)-$mark)*1e3).'ms'
		);
		$mark=microtime(TRUE);
		$cache->set('foo','bar',$ttl);
		$test->expect(
			$cache->get('foo')=='bar',
			'String: '.
			sprintf('%.1f',(microtime(TRUE)-$mark)*1e3).'ms'
		);
		$cache->set('e',$class=new \stdClass,$ttl);
		$mark=microtime(TRUE);
		$cache->set('foo','baz',$ttl);
		$test->expect(
			$cache->get('foo')=='baz',
			'String replaced: '.
			sprintf('%.1f',(microtime(TRUE)-$mark)*1e3).'ms'
		);
		$mark=microtime(TRUE);
		$test->expect(
			is_object($cache->get('e')),
			'Object: '.
			sprintf('%.1f',(microtime(TRUE)-$mark)*1e3).'ms'
		);
		$test->expect(
			$cache->get('a')===1 &&
			$cache->get('b')===2.34 &&
			$cache->get('c')===TRUE &&
			$cache->get('d')==array('hello','world') &&
			$cache->get('foo')=='baz' &&
			is_object($cache->get('e')),
			'Cache integrity test'
		);
		$mark=microtime(TRUE);
		$cache->clear('a');
		$cache->clear('b');
		$cache->clear('c');
		$cache->clear('d');
		$cache->clear('e');
		$cache->clear('foo');
		$test->expect(
			!$cache->exists('a') &&
			!$cache->exists('b') &&
			!$cache->exists('c') &&
			!$cache->exists('d') &&
			!$cache->exists('e') &&
			!$cache->exists('foo'),
			'Cache cleared: average: '.
				sprintf('%.1f',(microtime(TRUE)-$mark)/6*1e3).'ms/item'
		);
		$f3->clear('ROUTES');
		$ttl=0.05;
		$mark=microtime(TRUE);
		$f3->route('GET /dummy',
			function() {
				echo 'All in a day\'s work';
			},
			$ttl
		);
		$hash=$f3->hash('GET '.$f3->get('BASE').'/dummy').'.url';
		$f3->mock('GET /dummy');
		$test->expect(
			$cache->exists($hash),
			'Route cached (duration: '.($ttl*1e3).'ms)'
		);
		$test->expect(
			$cache->exists($hash),
			'Cached route still fresh: '.
				sprintf('%.1f',(microtime(TRUE)-$mark)*1e3).'ms'
		);
		usleep(1.1e6*$ttl);
		$test->expect(
			!$cache->exists($hash),
			'Cached route expired: '.
				sprintf('%.1f',(microtime(TRUE)-$mark)*1e3).'ms'
		);
		$f3->mock('GET /dummy');
		$test->expect(
			$cache->exists($hash),
			'Cache refreshed: '.
				sprintf('%.1f',(microtime(TRUE)-$mark)*1e3).'ms'
		);
		$cache->clear($hash);
		$f3->set('results',$test->results());
	}

}
