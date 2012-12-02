<?php

namespace App;

class Lexicon extends Controller {

	function get() {
		$f3=\Base::instance();
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$test->expect(
			$language=$f3->get('LANGUAGE'),
			'LANGUAGE: '.$language.' auto-detected'
		);
		$template=\Template::instance();
		$f3->set('LANGUAGE','fr_FR');
		$test->expect(
			$template->render('templates/lexicon.htm'),
			'fr_FR'
		);
		$f3->set('LANGUAGE','en_US');
		$test->expect(
			$template->render('templates/lexicon.htm'),
			'en_US'
		);
		$f3->set('LANGUAGE','es_CL');
		$test->expect(
			$template->render('templates/lexicon.htm'),
			'es_AR'
		);
		$f3->set('LANGUAGE','en');
		$test->expect(
			$template->render('templates/lexicon.htm'),
			'en (fallback)'
		);
		$f3->set('results',$test->results());
	}

}
