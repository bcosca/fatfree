<?php

namespace App;

class Cache extends Controller {

	function get($f3) {
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
			$backend=$f3->get('CACHE'),
			'Cache backend '.$f3->stringify($backend).' detected'
		);
		$repeat=TRUE;
		while ($repeat) {
			$cache=\Cache::instance();
			$test->expect(
				$cache===\Cache::instance(),
				'Same cache engine instance returned'
			);
			$cache->set($f3->hash('foo').'.var','bar',0.05);
			$test->expect(
				$f3->get('foo')=='bar',
				'Retrieve previously cached entry'
			);
			$test->expect(
				is_array($inf=$f3->exists('foo',$val)) &&
				is_float($inf[0]) &&
				$inf[1]===0.05 &&
				$val=='bar'
				,
				'Retrieve cache entry details'
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
			$cache->set('d',['hello','world'],$ttl);
			$test->expect(
				$cache->get('d')==['hello','world'],
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
				$cache->get('d')==['hello','world'] &&
				$cache->get('foo')=='baz' &&
				is_object($cache->get('e')),
				'Cache integrity test'
			);
			$mark=microtime(TRUE);
			$cache->reset();
			sleep(1);
			/*
			$cache->clear('a');
			$cache->clear('b');
			$cache->clear('c');
			$cache->clear('d');
			$cache->clear('e');
			$cache->clear('foo');
			*/
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
				function($f3) {
					$f3->set('message','All in a day\'s work');
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
			$test->expect(
				!$f3->exists('foo',$val) && !$val,
				'Hive key expired: '.
					sprintf('%.1f',(microtime(TRUE)-$mark)*1e3).'ms'
			);
			$cache->clear($hash);
			$session=new \Session;
			$test->expect(
				$session->sid()===NULL,
				'Cache-based session instantiated but not started'
			);
			session_start();
			$test->expect(
				$sid=$session->sid(),
				'Cache-based session started: '.$sid
			);
			$f3->set('SESSION.foo','hello world');
			session_write_close();
			$test->expect(
				$session->sid()===NULL,
				'Cache-based session written and closed'
			);
			$_SESSION=[];
			$test->expect(
				$f3->get('SESSION.foo')=='hello world',
				'Session variable retrieved from database'
			);
			$test->expect(
				$ip=$session->ip(),
				'IP address: '.$ip
			);
			$test->expect(
				$stamp=$session->stamp(),
				'Timestamp: '.date('r',$stamp)
			);
			$test->expect(
				$agent=$session->agent(),
				'User agent: '.$agent
			);
			$test->expect(
				$csrf=$session->csrf(),
				'Anti-CSRF token: '.$csrf
			);
			$before=$after='';
			if (preg_match('/^Set-Cookie: '.session_name().'=(\w+)/m',
				implode(PHP_EOL,array_reverse(headers_list())),$m))
				$before=$m[1];
			$f3->clear('SESSION');
			if (preg_match('/^Set-Cookie: '.session_name().'=(\w+)/m',
				implode(PHP_EOL,array_reverse(headers_list())),$m))
				$after=$m[1];
			$test->expect(
				empty($_SESSION) && !$cache->exists($sid.'@') &&
				$before==$sid && $after=='deleted' &&
				empty($_COOKIE[session_name()]),
				'Session destroyed and cookie expired'
			);
			$backend=$f3->get('CACHE');
			$f3->clear('CACHE');
			if (extension_loaded('memcached') &&
				!preg_match('/memcached=/',$backend) &&
				!preg_match('/redis=/',$backend)) {
				$f3->set('CACHE','memcached=localhost');
				if (preg_match('/memcached=/',$backend=$f3->get('CACHE'))) {
					$test->expect(
						$backend,
						'Cache backend '.$f3->stringify($backend).' specified'
					);
					continue;
				}
			}
			if (extension_loaded('redis') &&
				!preg_match('/redis=/',$backend)) {
				$f3->set('CACHE','redis=localhost');
				if (preg_match('/redis=/',$backend=$f3->get('CACHE'))) {
					$test->expect(
						$backend,
						'Cache backend '.$f3->stringify($backend).' specified'
					);
					continue;
				}
			}

			$repeat=FALSE;
		}
		$f3->set('results',$test->results());
	}

}
