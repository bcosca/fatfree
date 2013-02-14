<?php

namespace App;

class Markdown extends Controller {

	function get($f3) {
		$test=new \Test(\Test::FLAG_Both);
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$md=\Markdown::instance();
		$cases=array(
			'Code blocks',
			'Blockquotes with code blocks',
			'Nested blockquotes',
			'Horizontal rules',
			'Ordered and unordered lists',
			'Hard-wrapped paragraphs with list-like lines',
			'Tight blocks',
			'Tabs',
			'Tidyness',
			'Links, shortcut references',
			'Links, reference style',
			'Links, inline style',
			'Images',
			'Inline HTML (Simple)',
			'Inline HTML (Advanced)',
			'Inline HTML comments',
			'Code spans',
			'Strong and em together',
			'Auto links',
			'Amps and angle encoding',
			'Backslash escapes',
			'Literal quotes in titles',
			'PHP-specific bugs'
		);
		foreach ($cases as $case) {
			$txt=$md->render('markdown/'.$case.'.txt');
			$test->expect(
				$ok=$txt==$f3->read($f3->get('UI').'markdown/'.$case.'.htm'),
				$case
			);
			if (!$ok) echo $txt;
		}
		$f3->set('results',$test->results());
	}

}
