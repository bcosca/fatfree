<?php

namespace App;

class Lexicon extends Controller {

	function get($f3) {
		$f3->set('CACHE',TRUE);
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$f3->set('LOCALES','dict/');
		$test->expect(
			$language=$f3->get('FALLBACK'),
			'FALLBACK: '.$language
		);
		$test->expect(
			$language=$f3->get('LANGUAGE'),
			'LANGUAGE: '.$language.' auto-detected'
		);
		$template=\Template::instance();
		$f3->set('LANGUAGE','fr-FR',60);
		$test->expect(
			substr_count($f3->decode($template->render('templates/lexicon.htm')),
			'Les naïfs ægithales hâtifs pondant à Noël où il gèle sont sûrs d\'être déçus et de voir leurs drôles d\'œufs abîmés.'),
			'fr-FR'
		);
		$f3->set('LANGUAGE','en-US',60);
		$test->expect(
			substr_count($f3->decode($template->render('templates/lexicon.htm')),
			'The quick brown fox jumps over the lazy dog.'),
			'en-US'
		);
		$f3->set('LANGUAGE','es-CL',60);
		$test->expect(
			substr_count($f3->decode($template->render('templates/lexicon.htm')),
			'El pingüino Wenceslao hizo kilómetros bajo exhaustiva lluvia y frío, añoraba a su querido cachorro.'),
			'es-CL'
		);
		$f3->set('LANGUAGE','en',60);
		$test->expect(
			preg_match('/I love Fat-Free!/',
				$template->render('templates/lexicon.htm')),
			'en (fallback)'
		);
		$tqbf='The quick brown fox jumps over the lazy dog.';
		$f3->set('PREFIX',$prefix='prefix_');
		$f3->set('LANGUAGE','en');
		$test->expect(
			$f3->get($prefix.'tqbf')==$tqbf,
			"String PREFIX: \"$prefix\""
		);
		$f3->set('PREFIX',$prefix='prefix.');
		$f3->set('LANGUAGE','en');
		$test->expect(
			$f3->get($prefix.'tqbf')==$tqbf,
			"Array PREFIX: \"$prefix\""
		);
		$f3->set('PREFIX',$prefix='prefix.prefix_');
		$f3->set('LANGUAGE','en');
		$test->expect(
			$f3->get($prefix.'tqbf')==$tqbf,
			"Mixed PREFIX: \"$prefix\""
		);
		$f3->set('foo',
			'{0, plural, '.
				'zero {There\'s nothing on the table.}, '.
				'one {A book is on the table.}, '.
				'other {There are # books on the table.}'.
			'}'
		);
		$test->expect(
			$f3->get('foo',0)=='There\'s nothing on the table.',
			'Pluralization - zero'
		);
		$test->expect(
			$f3->get('foo',1)=='A book is on the table.',
			'Pluralization - one'
		);
		$test->expect(
			$f3->get('foo',2)=='There are 2 books on the table.',
			'Pluralization - two'
		);
		$test->expect(
			$f3->get('foo',9)=='There are 9 books on the table.',
			'Pluralization - other'
		);
		$f3->set('results',$test->results());
	}

}
