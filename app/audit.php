<?php

namespace App;

class Audit extends Controller {

	function get() {
		$f3=\Base::instance();
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$valid=new \Audit;
		$test->expect(
			!$valid->url('http://www.example.com/space here.html') &&
			$valid->url('http://www.example.com/space%20here.html'),
			'URL'
		);
		$test->expect(
			!$valid->email('Abc.google.com') &&
			!$valid->email('Abc.@google.com') &&
			!$valid->email('Abc..123@google.com') &&
			!$valid->email('A@b@c@google.com') &&
			!$valid->email('a"b(c)d,e:f;g<h>i[j\k]l@google.com') &&
			!$valid->email('just"not"right@google.com') &&
			!$valid->email('this is"not\allowed@google.com') &&
			!$valid->email('this\ still\"not\\allowed@google.com') &&
			$valid->email('niceandsimple@google.com') &&
			$valid->email('very.common@google.com') &&
			$valid->email('a.little.lengthy.but.fine@google.com') &&
			$valid->email('disposable.style.email.with+symbol@google.com') &&
			$valid->email('user@[IPv6:2001:db8:1ff::a0b:dbd0]',FALSE) &&
			$valid->email('"very.unusual.@.unusual.com"@google.com') &&
			$valid->email('!#$%&\'*+-/=?^_`{}|~@google.com') &&
			$valid->email('""@google.com'),
			'E-mail address'
		);
		$test->expect(
			!$valid->ipv4('') &&
			!$valid->ipv4('...') &&
			!$valid->ipv4('hello, world') &&
			!$valid->ipv4('256.256.0.0') &&
			!$valid->ipv4('255.255.255.') &&
			!$valid->ipv4('.255.255.255') &&
			!$valid->ipv4('172.300.256.100') &&
			$valid->ipv4('30.88.29.1') &&
			$valid->ipv4('192.168.100.48'),
			'IPv4 address'
		);
		$test->expect(
			!$valid->ipv6('') &&
			!$valid->ipv6('FF01::101::2') &&
			!$valid->ipv6('::1.256.3.4') &&
			!$valid->ipv6('2001:DB8:0:0:8:800:200C:417A:221') &&
			!$valid->ipv6('FF02:0000:0000:0000:0000:0000:0000:0000:0001') &&
			$valid->ipv6('::') &&
			$valid->ipv6('::1') &&
			$valid->ipv6('2002::') &&
			$valid->ipv6('::ffff:192.0.2.128') &&
			$valid->ipv6('0:0:0:0:0:0:0:1') &&
			$valid->ipv6('2001:DB8:0:0:8:800:200C:417A'),
			'IPv6 address'
		);
		$test->expect(
			!$valid->isprivate('0.1.2.3') &&
			!$valid->isprivate('201.176.14.4') &&
			$valid->isprivate('fc00::') &&
			$valid->isprivate('10.10.10.10') &&
			$valid->isprivate('172.16.93.7') &&
			$valid->isprivate('192.168.3.5'),
			'Local IP range'
		);
		$test->expect(
			!$valid->isreserved('193.194.195.196') &&
			$valid->isreserved('::1') &&
			$valid->isreserved('127.0.0.1') &&
			$valid->isreserved('0.1.2.3') &&
			$valid->isreserved('169.254.1.2') &&
			$valid->isreserved('192.0.2.1') &&
			$valid->isreserved('224.225.226.227') &&
			$valid->isreserved('240.241.242.243'),
			'Reserved IP range'
		);
		$f3->set('results',$test->results());
	}

}
