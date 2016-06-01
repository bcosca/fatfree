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
				$this->exec('/web')=='<h1>Web</h1>',
				'Mock HTTP request'
			);
			$test->expect(
				$this->exec('/web?foo=bar')=='<h1>Web</h1>foo=bar',
				'Pass query string'
			);
			$test->expect(
				$this->exec('')=='Home',
				'Default argument'
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
