<?php

namespace App;

class OpenID extends Controller {

	function get($f3) {
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$openid=new \Web\OpenID;
		$openid->set('identity','https://www.google.com/accounts/o8/id');
		$openid->set('return_to',
			$f3->get('SCHEME').'://'.$f3->get('HOST').
			$f3->get('BASE').'/'.'openid2');
		// auth() should always redirect if successful; fail if displayed
		$test->expect(
			$openid->auth(
				NULL,
				array(
					'country'=>'http://axschema.org/contact/country/home',
					'email'=>'http://axschema.org/contact/email',
					'firstname'=>'http://axschema.org/namePerson/first',
					'lastname'=>'http://axschema.org/namePerson/last'
				),
				array('firstname,lastname')
			),
			'OpenID authentication'
		);
		$f3->set('results',$test->results());
	}

}
