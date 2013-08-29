<?php

namespace App;

class OpenID2 extends Controller {

	function get($f3) {
		$test=new \Test;
		$f3->set('results',$test->results());
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$openid=new \Web\OpenID;
		$test->expect(
			$openid->verified(),
			'OpenID '.$openid->get('identity').' verified'
		);
		$test->expect(
			$openid->response(),
			'OpenID attributes in response'
		);
		$f3->set('results',$test->results());
	}

}

