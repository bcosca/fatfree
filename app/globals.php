<?php

namespace App;

class Globals extends Controller {

	function get($f3) {
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
			$ip=$f3->get('IP'),
			'IP (Remote IP address): '.$f3->stringify($ip)
		);
		$test->expect(
			$realm=$f3->get('REALM'),
			'REALM (Full canonical URI): '.$f3->stringify($realm)
		);
		$test->expect(
			($verb=$f3->get('VERB'))==$_SERVER['REQUEST_METHOD'],
			'VERB (request method): '.$f3->stringify($verb)
		);
		$test->expect(
			$scheme=$f3->get('SCHEME'),
			'SCHEME (Web protocol): '.$f3->stringify($scheme)
		);
		$test->expect(
			$scheme=$f3->get('HOST'),
			'HOST (Web host/domain): '.$f3->stringify($scheme)
		);
		$test->expect(
			$port=$f3->get('PORT'),
			'PORT (HTTP port): '.$port
		);
		$test->expect(
			is_string($base=$f3->get('BASE')),
			'BASE (path to index.php relative to ROOT): '.
				$f3->stringify($base)
		);
		$test->expect(
			($uri=$f3->get('URI'))==$_SERVER['REQUEST_URI'],
			'URI (request URI): '.$f3->stringify($uri)
		);
		$test->expect(
			$agent=$f3->get('AGENT'),
			'AGENT (user agent): '.$f3->stringify($agent)
		);
		$test->expect(
			!($ajax=$f3->get('AJAX')),
			'AJAX: '.$f3->stringify($ajax)
		);
		$test->expect(
			$pattern=$f3->get('PATTERN'),
			'PATTERN (matching route): '.$f3->stringify($pattern)
		);
		$test->expect(
			($charset=$f3->get('ENCODING'))=='UTF-8',
			'ENCODING (character set): '.$f3->stringify($charset)
		);
		if (extension_loaded('mbstring'))
			$test->expect(
				($charset=mb_internal_encoding())=='UTF-8',
				'Multibyte encoding: '.$f3->stringify($charset)
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
			session_id() && empty($_SESSION['hello']) && is_null($result),
			'Session restarted by get()'
		);
		$f3->clear('SESSION');
		$result=$f3->exists('SESSION[hello]');
		$test->expect(
			session_id() && empty($_SESSION['hello']) && $result===FALSE,
			'No session variable instantiated by exists()'
		);
		$f3->set('SESSION.foo','bar');
		$f3->set('SESSION.baz','qux');
		$f3->clear('SESSION.foo');
		$result=$f3->exists('SESSION.foo');
		$test->expect(
			session_id() && empty($_SESSION['foo']) && $result===FALSE &&
			!empty($_SESSION['baz']),
			'Specific session variable created/erased'
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
		foreach (explode('|',$f3::GLOBALS) as $global)
			unset($GLOBALS['_'.$global]['foo'],$GLOBALS['_'.$global]['bar']);
		$f3->clear('SESSION');
		$f3->set('GET["bar"]','foo');
		$f3->set('POST.baz','qux');
		$test->expect(
			$f3->get('GET.bar')=='foo' && $_GET['bar']=='foo' &&
			$f3->get('REQUEST.bar')=='foo' && $_REQUEST['bar']=='foo' &&
			$f3->get('POST.baz')=='qux' && $_POST['baz']=='qux' &&
			$f3->get('REQUEST.baz')=='qux' && $_REQUEST['baz']=='qux',
			'PHP global variables in sync'
		);
		$f3->clear('GET["bar"]');
		$test->expect(
			!$f3->exists('GET["bar"]') && empty($_GET['bar']) &&
			!$f3->exists('REQUEST["bar"]') && empty($_REQUEST['bar']),
			'PHP global variables cleared'
		);
		$ok=TRUE;
		foreach ($f3->get('HEADERS') as $hdr=>$val)
			if ($_SERVER['HTTP_'.strtoupper(str_replace('-','_',$hdr))]!=
				$val)
				$ok=FALSE;
		$test->expect(
			$ok,
			'HTTP headers match HEADERS variable'
		);
		$ok=TRUE;
		$hdrs=array();
		foreach (array_keys($f3->get('HEADERS')) as $hdr) {
			$f3->set('HEADERS["'.$hdr.'"]','foo');
			$hdr=strtoupper(str_replace('-','_',$hdr));
			$hdrs[]=$hdr;
			if ($_SERVER['HTTP_'.$hdr]!='foo')
				$ok=FALSE;
		}
		$test->expect(
			$ok,
			'Altering HEADERS variable affects HTTP headers'
		);
		$ok=TRUE;
		foreach ($hdrs as $hdr) {
			$_SERVER['HTTP_'.$hdr]='bar';
			if ($f3->get('HEADERS["'.
				str_replace(' ','-',
					ucwords(str_replace('_',' ',strtolower($hdr)))).'"]')!=
				$_SERVER['HTTP_'.$hdr])
				$ok=FALSE;
		}
		$test->expect(
			$ok,
			'Altering HTTP headers affects HEADERS variable'
		);
		if ($f3->exists('COOKIE.baz')) {
			$test->message('HTTP cookie retrieved');
			$f3->clear('COOKIE.baz');
		}
		else
			$test->expect(
				$f3->set('COOKIE.baz','qux'),
				'HTTP cookie sent'
			);
		$f3->set('results',$test->results());
	}

}
