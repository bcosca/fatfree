<?php

namespace App;

class Autoload extends Controller {

	function get($f3) {
		$test=new \F3\Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$test->message(
			'Namespace search path: '.
				$f3->get('PLUGINS').';'.$f3->get('AUTOLOAD')
		);
		$test->expect(
			class_exists('NS\C'),
			'NS\C: ns/c.php'
		);
		$test->expect(
			class_exists('NS\NS1\C'),
			'NS\NS1\C: ns/ns1/c.php'
		);
		$test->expect(
			class_exists('NS\NS2\C'),
			'NS\NS2\C: ns/ns2/c.php'
		);
		$test->expect(
			class_exists('NS\NS3\C'),
			'NS\NS3\C: ns/ns3/c.php'
		);
		$test->expect(
			class_exists('NS\NS3\NS4\C'),
			'NS\NS3\NS4\C: ns/ns3/ns4/c.php'
		);
		$test->expect(
			class_exists('NS\NS3\NS5\C'),
			'NS\NS3\NS5\C: ns/ns3/ns5/c.php'
		);
		$test->expect(
			class_exists('NS\NS6\NS7\C'),
			'NS\NS6\NS7\C: ns/ns6/ns7/c.php'
		);
		$test->expect(
			class_exists('\F3\Cache'),
			'Class in root namespace: lib/base.php'
		);
		$f3->set('results',$test->results());
	}

}
