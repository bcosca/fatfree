<?php

namespace App;

class Unicode extends Controller {

	function get($f3) {
		$test=new \F3\Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$utf=new \F3\UTF;
		$test->expect(
			$len=$utf->strlen('⠊⠀⠉⠁⠝⠀⠑⠁⠞⠀⠛⠇⠁⠎⠎⠀⠁⠝⠙⠀⠊⠞')==22,
			'strlen'
		);
		$test->expect(
			$utf->substr('Я можу їсти скло',0,6)=='Я можу',
			'substr (at zero offset)'
		);
		$test->expect(
			$utf->substr('أنا قادر على أكل الزجاج و هذا لا يؤلمني',0,8)=='أنا قادر',
			'substr (at zero offset RTL-language)'
		);
		$test->expect(
			$utf->substr('나는 유리를 먹을 수 있어요. 그래도',3,3)=='유리를',
			'substr (non-zero offset)'
		);
		$test->expect(
			$utf->substr('',7)===FALSE,
			'substr (empty string)'
		);
		$test->expect(
			$utf->substr('איך קען עסן גלאָז און עס טוט מיר נישט װײ',-7)==
				'נישט װײ',
			'substr (negative offset)'
		);
		$test->expect(
			$utf->substr('El pingüino Wenceslao hizo kilómetros',-10,4)=='kiló',
			'substr (negative offset and specified length)'
		);
		$test->expect(
			$utf->strpos('Góa ē-tàng chia̍h po-lê','h')==12,
			'strpos'
		);
		$test->expect(
			$utf->strpos('123 456 789 123 4','123',7)==12,
			'strpos with offset'
		);
		$str='ᛋᚳᛖᚪᛚ᛫ᚦᛖᚪᚻ᛫ᛗᚪᚾᚾᚪ᛫ᚷᛖᚻᚹᛦᛚᚳ᛫ᛗᛁᚳᛚᚢᚾ᛫ᚻᛦᛏ᛫ᛞᚫᛚᚪᚾ';
		$test->expect(
			$utf->strstr($str,'ᛁᚳᛚᚢᚾ',TRUE)=='ᛋᚳᛖᚪᛚ᛫ᚦᛖᚪᚻ᛫ᛗᚪᚾᚾᚪ᛫ᚷᛖᚻᚹᛦᛚᚳ᛫ᛗ',
			'strstr (before needle)'
		);
		$test->expect(
			$utf->strstr($str,'ᛁᚳᛚᚢᚾ')=='ᛁᚳᛚᚢᚾ᛫ᚻᛦᛏ᛫ᛞᚫᛚᚪᚾ',
			'strstr (after needle)'
		);
		$test->expect(
			$utf->substr_count(
				'Можам да јадам стакло, а не ме штета.','д')==2,
			'substr_count'
		);
		$str="\xe2\x80\x83\x20#string#\xc2\xa0\xe1\x9a\x80";
		$test->expect(
			$utf->ltrim($str)=="#string#\xc2\xa0\xe1\x9a\x80",
			'ltrim'
		);
		$test->expect(
			$utf->rtrim($str)=="\xe2\x80\x83\x20#string#",
			'rtrim'
		);
		$test->expect(
			$utf->trim($str)=='#string#',
			'trim'
		);
		$f3->set('results',$test->results());
	}

}
