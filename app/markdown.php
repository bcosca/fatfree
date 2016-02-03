<?php

namespace App;

class Markdown extends Controller {

	function get($f3) {
		$test=new \F3\Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$md=\F3\Markdown::instance();
		$cases=array(
			'Code Blocks',
			'Blockquotes with code blocks',
			'Nested blockquotes',
			'Horizontal rules',
			'Ordered and unordered lists',
			'Code block in a list item',
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
			'Code Spans',
			'Strong and em together',
			'Auto links',
			'Amps and angle encoding',
			'Backslash escapes',
			'Literal quotes in titles',
			'PHP-specific bugs',
			'Tricky combinations'
		);
		foreach ($cases as $case) {
			$txt=$f3->read($f3->get('UI').'markdown/'.$case.'.txt');
			$test->expect(
				$md->convert($txt)==$f3->read($f3->get('UI').
					'markdown/'.$case.'.htm'),
				$case
			);
		}
		$f3->set('results',$test->results());
	}

}
