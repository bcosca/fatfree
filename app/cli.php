<?php

namespace App;

class CLI extends Controller {

	/** @var string PHP CLI binary name **/
	protected $binary;

	function get($f3) {
		$test=new \Test;
		$test->expect(
			isset($this->binary),
			'PHP CLI binary detected'
		);
		if (isset($this->binary)) {
			$test->expect(
				$this->exec('/web?foo=bar')=='<h1>Web</h1>foo=bar',
				'Web-style argument (HTTP request)'
			);
			$test->expect(
				$this->exec('log show')=='show',
				'Console-style arguments'
			);
			$test->expect(
				$this->exec('debug uri')=='/debug/uri?' &&
				$this->exec('debug uri -a=1 --name=foo')=='/debug/uri?a=1&name=foo' &&
				$this->exec('debug get -a=1 --name=foo')=='a:1,name:foo',
				'Console-style options'
			);
			$test->expect(
				$this->exec('debug uri -a -b --force')=='/debug/uri?a=&b=&force=',
				'Console-style flags'
			);
			$test->expect(
				$this->exec('debug uri -abc=1 -d=2')=='/debug/uri?a=&b=&c=1&d=2',
				'Console-style combined flags'
			);
			$test->expect(
				$this->exec('debug -a=1 uri -b=2')=='/debug/uri?a=1&b=2',
				'The position of options doesn\'t matter'
			);
			$test->expect(
				$this->exec('')=='Home' &&
				$this->exec('--color=blue')=='Home is blue',
				'Default route'
			);
		}
		$f3->set('results',$test->results());
	}

	function exec($str) {
		exec($this->binary.' cli.php '.$str,$out,$ret);
		return $ret==0?$out[0]:FALSE;
	}

	function __construct() {
		// Detect PHP CLI binary in the path
		if (function_exists('exec'))
			foreach(array('php','php-cli') as $path) {
				exec($path.' -v 2>&1',$out,$ret);
				if ($ret==0 && preg_match('/cli/',@$out[0],$out)) {
					$this->binary=$path;
					break;
				}
			}
	}

}
