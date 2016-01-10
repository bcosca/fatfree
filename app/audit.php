<?php

namespace App;

class Audit extends Controller {

	function get($f3) {
		$test=new \F3\Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$audit=new \F3\Audit;
		$test->expect(
			!$audit->url('http://www.example.com/space here.html') &&
			$audit->url('http://www.example.com/space%20here.html'),
			'URL'
		);
		$test->expect(
			!$audit->email('Abc.google.com',FALSE) &&
			!$audit->email('Abc.@google.com',FALSE) &&
			!$audit->email('Abc..123@google.com',FALSE) &&
			!$audit->email('A@b@c@google.com',FALSE) &&
			!$audit->email('a"b(c)d,e:f;g<h>i[j\k]l@google.com',FALSE) &&
			!$audit->email('just"not"right@google.com',FALSE) &&
			!$audit->email('this is"not\allowed@google.com',FALSE) &&
			!$audit->email('this\ still\"not\\allowed@google.com',FALSE) &&
			$audit->email('niceandsimple@google.com',FALSE) &&
			$audit->email('very.common@google.com',FALSE) &&
			$audit->email('a.little.lengthy.but.fine@google.com',FALSE) &&
			$audit->email('disposable.email.with+symbol@google.com',FALSE) &&
			$audit->email('user@[IPv6:2001:db8:1ff::a0b:dbd0]',FALSE) &&
			$audit->email('"very.unusual.@.unusual.com"@google.com',FALSE) &&
			$audit->email('!#$%&\'*+-/=?^_`{}|~@google.com',FALSE) &&
			$audit->email('""@google.com',FALSE),
			'E-mail address'
		);
		$test->expect(
			!$audit->email('Abc.google.com') &&
			!$audit->email('Abc.@google.com') &&
			!$audit->email('Abc..123@google.com') &&
			!$audit->email('A@b@c@google.com') &&
			!$audit->email('a"b(c)d,e:f;g<h>i[j\k]l@google.com') &&
			!$audit->email('just"not"right@google.com') &&
			!$audit->email('this is"not\allowed@google.com') &&
			!$audit->email('this\ still\"not\\allowed@google.com') &&
			$audit->email('niceandsimple@google.com') &&
			$audit->email('very.common@google.com') &&
			$audit->email('a.little.lengthy.but.fine@google.com') &&
			$audit->email('disposable.email.with+symbol@google.com') &&
			$audit->email('user@[IPv6:2001:db8:1ff::a0b:dbd0]',FALSE) &&
			$audit->email('"very.unusual.@.unusual.com"@google.com') &&
			$audit->email('!#$%&\'*+-/=?^_`{}|~@google.com') &&
			$audit->email('""@google.com'),
			'E-mail address (with domain verification)'
		);
		$test->expect(
			!$audit->ipv4('') &&
			!$audit->ipv4('...') &&
			!$audit->ipv4('hello, world') &&
			!$audit->ipv4('256.256.0.0') &&
			!$audit->ipv4('255.255.255.') &&
			!$audit->ipv4('.255.255.255') &&
			!$audit->ipv4('172.300.256.100') &&
			$audit->ipv4('30.88.29.1') &&
			$audit->ipv4('192.168.100.48'),
			'IPv4 address'
		);
		$test->expect(
			!$audit->ipv6('') &&
			!$audit->ipv6('FF01::101::2') &&
			!$audit->ipv6('::1.256.3.4') &&
			!$audit->ipv6('2001:DB8:0:0:8:800:200C:417A:221') &&
			!$audit->ipv6('FF02:0000:0000:0000:0000:0000:0000:0000:0001') &&
			$audit->ipv6('::') &&
			$audit->ipv6('::1') &&
			$audit->ipv6('2002::') &&
			$audit->ipv6('::ffff:192.0.2.128') &&
			$audit->ipv6('0:0:0:0:0:0:0:1') &&
			$audit->ipv6('2001:DB8:0:0:8:800:200C:417A'),
			'IPv6 address'
		);
		$test->expect(
			!$audit->isprivate('0.1.2.3') &&
			!$audit->isprivate('201.176.14.4') &&
			$audit->isprivate('fc00::') &&
			$audit->isprivate('10.10.10.10') &&
			$audit->isprivate('172.16.93.7') &&
			$audit->isprivate('192.168.3.5'),
			'Local IP range'
		);
		$test->expect(
			!$audit->isreserved('193.194.195.196') &&
			$audit->isreserved('::1') &&
			$audit->isreserved('127.0.0.1') &&
			$audit->isreserved('0.1.2.3') &&
			$audit->isreserved('169.254.1.2') &&
			$audit->isreserved('192.0.2.1') &&
			$audit->isreserved('224.225.226.227') &&
			$audit->isreserved('240.241.242.243'),
			'Reserved IP range'
		);
		$type='American Express';
		$test->expect(
			$audit->card('378282246310005')==$type &&
			$audit->card('371449635398431')==$type &&
			$audit->card('378734493671000')==$type,
			$type
		);
		$type='Diners Club';
		$test->expect(
			$audit->card('30569309025904')==$type &&
			$audit->card('38520000023237')==$type,
			$type
		);
		$type='Discover';
		$test->expect(
			$audit->card('6011111111111117')==$type &&
			$audit->card('6011000990139424')==$type,
			$type
		);
		$type='JCB';
		$test->expect(
			$audit->card('3530111333300000')==$type &&
			$audit->card('3566002020360505')==$type,
			$type
		);
		$type='MasterCard';
		$test->expect(
			$audit->card('5555555555554444')==$type &&
			$audit->card('5105105105105100')==$type,
			$type
		);
		$type='Visa';
		$test->expect(
			$audit->card('4222222222222')==$type &&
			$audit->card('4111111111111111')==$type &&
			$audit->card('4012888888881881')==$type,
			$type
		);
		$test->message('isdesktop: '.$f3->stringify($audit->isdesktop()));
		$test->message('ismobile: '.$f3->stringify($audit->ismobile()));
		$f3->set('results',$test->results());
	}

}
