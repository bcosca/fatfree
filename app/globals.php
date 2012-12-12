<?php

namespace App;

class Globals extends Controller {

	function get() {
		$f3=\Base::instance();
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$test->expect(
			$package=$f3->get('PACKAGE'),
			'PACKAGE: '.$package
		);
		$test->expect(
			$version=$f3->get('VERSION'),
			'VERSION: '.$version
		);
		$f3->clear('PACKAGE');
		$f3->clear('VERSION');
		$test->expect(
			$f3->get('PACKAGE')==$package && $f3->get('VERSION')==$version,
			'Clearing global variable resets to default value'
		);
		$test->expect(
			is_dir($root=$f3->get('ROOT')),
			'ROOT (document root): '.$f3->stringify($root)
		);
		$test->expect(
			is_string($base=$f3->get('BASE')),
			'BASE (path to index.php relative to ROOT): '.
				$f3->stringify($base)
		);
		$test->expect(
			$scheme=$f3->get('SCHEME'),
			'SCHEME (Web protocol): '.$f3->stringify($scheme)
		);
		$test->expect(
			($verb=$f3->get('VERB'))==$_SERVER['REQUEST_METHOD'],
			'VERB (request method): '.$f3->stringify($verb)
		);
		$test->expect(
			($uri=$f3->get('URI'))==$_SERVER['REQUEST_URI'],
			'URI (request URI): '.$f3->stringify($uri)
		);
		$test->expect(
			$pattern=$f3->get('PATTERN'),
			'PATTERN (matching route): '.$f3->stringify($pattern)
		);
		$test->expect(
			($charset=$f3->get('ENCODING'))=='UTF-8',
			'ENCODING (character set): '.$f3->stringify($charset)
		);
		$test->expect(
			($language=$f3->get('LANGUAGE')),
			'LANGUAGE: '.$f3->stringify($language)
		);
		$test->expect(
			$tz=$f3->get('TZ'),
			'TZ (time zone): '.$f3->stringify($tz)
		);
		$f3->set('TZ','America/New_York');
		$test->expect(
			($tz=$f3->get('TZ'))==date_default_timezone_get(),
			'Time zone adjusted: '.$f3->stringify($tz)
		);
		$test->expect(
			$serializer=$f3->get('SERIALIZER'),
			'SERIALIZER: '.$f3->stringify($serializer)
		);
		$f3->clear('SESSION');
		$test->expect(
			!session_id(),
			'No active session'
		);
		$f3->set('SESSION[hello]','world');
		$test->expect(
			session_id() && $_SESSION['hello']=='world',
			'Session auto-started by set()'
		);
		$f3->clear('SESSION');
		$test->expect(
			!session_id() && empty($_SESSION),
			'Session destroyed by clear()'
		);
		$result=$f3->get('SESSION[hello]');
		$test->expect(
			session_id() && !isset($_SESSION['hello']) && is_null($result),
			'Session restarted by get()'
		);
		$f3->clear('SESSION');
		$result=$f3->exists('SESSION[hello]');
		$test->expect(
			session_id() && !isset($_SESSION['hello']) && $result===FALSE,
			'No session variable instantiated by exists()'
		);
		$f3->clear('SESSION');
		$test->expect(
			!session_id() && empty($_SESSION),
			'Session cleared'
		);
		$ok=TRUE;
		$list='';
		foreach (explode('|',$f3::GLOBALS) as $global)
			if ($GLOBALS['_'.$global]!=$f3->get($global)) {
				$ok=FALSE;
				$list.=($list?',':'').$global;
			}
		$test->expect(
			$ok,
			'PHP globals same as hive globals'.
				($list?(': '.$list):'')
		);
		$ok=TRUE;
		$list='';
		foreach (explode('|',$f3::GLOBALS) as $global) {
			$f3->set($global.'.foo','bar');
			if ($GLOBALS['_'.$global]!==$f3->get($global)) {
				$ok=FALSE;
				$list.=($list?',':'').$global;
			}
		}
		$test->expect(
			$ok,
			'Altering hive globals affects PHP globals'.
				($list?(': '.$list):'')
		);
		$ok=TRUE;
		$list='';
		foreach (explode('|',$f3::GLOBALS) as $global) {
			$GLOBALS['_'.$global]['bar']='foo';
			if ($GLOBALS['_'.$global]!==$f3->get($global)) {
				$ok=FALSE;
				$list.=($list?',':'').$global;
			}
		}
		$test->expect(
			$ok,
			'Altering PHP globals affects hive globals'.
				($list?(': '.$list):'')
		);
		$f3->clear('SESSION');
		$f3->set('GET["bar"]','foo');
		$f3->set('POST.baz','qux');
		$test->expect(
			$f3->get('GET["bar"]')=='foo' && $_GET['bar']=='foo' &&
			$f3->get('REQUEST["bar"]')=='foo' && $_REQUEST['bar']=='foo' &&
			$f3->get('POST["baz"]')=='qux' && $_POST['baz']=='qux' &&
			$f3->get('REQUEST["baz"]')=='qux' && $_REQUEST['baz']=='qux',
			'PHP global variables in sync'
		);
		$f3->clear('GET["bar"]');
		$test->expect(
			!$f3->exists('GET["bar"]') && !isset($_GET['bar']) &&
			!$f3->exists('REQUEST["bar"]') && !isset($_REQUEST['bar']),
			'PHP global variables cleared'
		);
		$f3->set('results',$test->results());
	}

}
