<?php

namespace App;

class Lexicon extends Controller {

	function get($f3) {
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$f3->set('LOCALES','dict/');
		$test->expect(
			$language=$f3->get('LANGUAGE'),
			'LANGUAGE: '.$language.' auto-detected'
		);
		$template=\Template::instance();
		$f3->set('LANGUAGE','fr_FR');
		$test->expect(
			substr_count($f3->decode($template->render('templates/lexicon.htm')),
			'Les naïfs ægithales hâtifs pondant à Noël où il gèle sont sûrs d\'être déçus et de voir leurs drôles d\'œufs abîmés.'),
			'fr_FR'
		);
		$f3->set('LANGUAGE','en_US');
		$test->expect(
			substr_count($f3->decode($template->render('templates/lexicon.htm')),
			'The quick brown fox jumps over the lazy dog.'),
			'en_US'
		);
		$f3->set('LANGUAGE','es_CL');
		$test->expect(
			substr_count($f3->decode($template->render('templates/lexicon.htm')),
			'El pingüino Wenceslao hizo kilómetros bajo exhaustiva lluvia y frío, añoraba a su querido cachorro.'),
			'es_CL'
		);
		$f3->set('LANGUAGE','en');
		$test->expect(
			preg_match('/I love Fat-Free!/',
				$template->render('templates/lexicon.htm')),
			'en (fallback)'
		);
		$f3->set('results',$test->results());
	}

}
