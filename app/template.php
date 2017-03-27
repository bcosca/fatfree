<?php

namespace App;

class Template extends Controller {

	function get($f3) {
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$tpl=\Preview::instance();
		$f3->set('foo','bar->baz');
		$test->expect(
			$tpl->render('templates/test1.htm')=='bar-&gt;baz',
			'Auto-escaping enabled'
		);
		$test->expect(
			$tpl->token($expr='@foo.bar')==($eval='$foo[\'bar\']'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo[bar]')==($eval='$foo[\'bar\']'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo[\'bar\']')==($eval='$foo[\'bar\']'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo["bar"]')==($eval='$foo["bar"]'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo.0')==($eval='$foo[0]'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo.@bar')==($eval='$foo.$bar'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo[@bar]')==($eval='$foo[$bar]'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo->bar.baz')==($eval='$foo->bar[\'baz\']'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo->bar[baz]')==($eval='$foo->bar[\'baz\']'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo->bar.@baz')==($eval='$foo->bar.$baz'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo->bar[@baz]')==($eval='$foo->bar[$baz]'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo->@baz')==($eval='$foo->$baz'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo::bar.baz')==($eval='$foo::bar[\'baz\']'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo::bar[baz]')==($eval='$foo::bar[\'baz\']'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo::bar.@baz')==($eval='$foo::bar.$baz'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo::@baz')==($eval='$foo::$baz'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo::bar[@baz]')==($eval='$foo::bar[$baz]'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo->bar[@qux.baz]')==
				($eval='$foo->bar[$qux[\'baz\']]'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo->bar[@qux.@baz]')==
				($eval='$foo->bar[$qux.$baz]'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo()')==($eval='$foo()'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo()->bar')==($eval='$foo()->bar'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo.zip()')==($eval='$foo[\'zip\']()'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo.zip(@bar)')==($eval='$foo[\'zip\']($bar)'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo.zip(@bar,@baz)')==
				($eval='$foo[\'zip\']($bar,$baz)'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo.zip(@bar,\'qux\')')==
				($eval='$foo[\'zip\']($bar,\'qux\')'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo->zip(@bar,\'qux\',123,[\'a\'=>\'hello\'])')==($eval='$foo->zip($bar,\'qux\',123,[\'a\'=>\'hello\'])'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo[google.com]')==
				($eval='$foo[\'google.com\']'),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo.\'hello, world\'')==
				($eval='$foo.\'hello, world\''),
			$expr.': '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo | esc')==
				($eval='$this->esc($foo)'),
			$expr.': '.$eval
		);
		$tpl=\Template::instance();
		$f3->set('foo','bar');
		$f3->set('cond',TRUE);
		$f3->set('file','templates/test1.htm');
		$test->expect(
			$tpl->render('templates/test2.htm')=='bar',
			'<include>'
		);
		$f3->set('foo','barré');
		$test->expect(
			($str=$tpl->render('templates/test2b.htm'))=='barrébarré',
			'double <include> doesn\'t double encode'
		);
		$f3->clear('cond');
		$f3->set('foo','baz');
		$test->expect(
			$tpl->render('templates/test3.htm')=='bazbaz',
			'<exclude> and {* comment *}'
		);
		$f3->clear('foo');
		$f3->set('div',
			[
				'coffee'=>['arabica','barako','liberica','kopiluwak'],
				'tea'=>['darjeeling','pekoe','samovar']
			]
		);
		$test->expect(
			preg_replace('/[\t\r\n]/','',
				$tpl->render('templates/test4.htm'))==
				'<div>'.
					'<p><span><b>coffee</b></span></p>'.
					'<p>'.
						'<span class="odd">arabica</span>'.
						'<span class="even">barako</span>'.
						'<span class="odd">liberica</span>'.
						'<span class="even">kopiluwak</span>'.
					'</p>'.
				'</div>'.
				'<div>'.
					'<p><span><b>tea</b></span></p>'.
					'<p>'.
						'<span class="odd">darjeeling</span>'.
						'<span class="even">pekoe</span>'.
						'<span class="odd">samovar</span>'.
					'</p>'.
				'</div>',
			'<repeat>'
		);
		$f3->clear('div');
		$f3->set('cond1',TRUE);
		$f3->set('cond2',TRUE);
		$test->expect(
			trim($tpl->render('templates/test5.htm'))==
				'c1:T,c2:T',
			'<check>, <true>, <true>'
		);
		$f3->set('cond1',TRUE);
		$f3->set('cond2',FALSE);
		$test->expect(
			trim($tpl->render('templates/test5.htm'))==
				'c1:T,c2:F',
			'<check>, <true>, <false>'
		);
		$f3->set('cond1',FALSE);
		$f3->set('cond2',TRUE);
		$test->expect(
			trim($tpl->render('templates/test5.htm'))==
				'c1:F,c2:T',
			'<check>, <false>, <true>'
		);
		$f3->set('cond1',FALSE);
		$f3->set('cond2',FALSE);
		$test->expect(
			trim($tpl->render('templates/test5.htm'))==
				'c1:F,c2:F',
			'<check>, <false>, <false>'
		);
		$f3->clear('cond1');
		$f3->clear('cond2');
		$f3->set('test',['string'=>'thin','int'=>123,'bool'=>FALSE]);
		$test->expect(
			preg_replace('/\s/','',$tpl->render('templates/test11.htm'))==
				'<em>thin</em>-1failed123124',
			'<switch>, <case>, <default>'
		);
		$f3->clear('test');
		$test->expect(
			preg_replace('/[\t\r\n]/','',
				$tpl->render('templates/test6.htm'))==
					'<div>'.
						'<p class="odd">1</p>'.
						'<p class="even">2</p>'.
						'<p class="odd">3</p>'.
					'</div>'.
					'Temporary variable preserved across includes',
			'<loop> with embedded <include>'
		);
		$test->expect(
			preg_replace('/[\t\r\n]/','',
				$tpl->render('templates/test8.htm'))==
					'<span>3</span>'.
					'<span>6</span>'.
					'<span>xyz</span>'.
					'<span>1</span>'.
					'<span>[1,3,5]</span>'.
					'<span>a</span>'.
					'<span>b</span>'.
					'<span>c</span>'.
					'email@address.com',
			'<set>'
		);
		$test->expect(
			preg_replace('/[\t\r\n]/','',
				$tpl->render('templates/test9.htm'))==
				'<script type="text/javascript">var a=\'{{a}}\';</script>',
			'<ignore>'
		);
		$tpl->extend('foo',
			function ($node) use ($f3) {
				return $f3->stringify($node);
			}
		);
		$result=$tpl->render('templates/test10.htm');
		$lines=array_map('trim',explode("\n",$result));
		$test->expect(
			$lines[0]==$f3->stringify(
				['@attrib'=>['bar'=>'123','baz'=>'abc']]),
			'Custom tag'
		);
		$test->expect(
			isset($lines[1]) && $lines[1]==$f3->stringify(
				['@attrib'=>['bar'=>'test2'],'test2']),
			'Custom tag with inner content'
		);
		$test->expect(
			isset($lines[2]) && $lines[2]==$f3->stringify(
				['@attrib'=>['bar'=>'test3','disabled'=>NULL],'test3']),
			'Custom tag with value-less attribute'
		);
		$test->expect(
			isset($lines[3]) && $lines[3]==$f3->stringify(
				['@attrib'=>['data-foo'=>'baz'],'test4']),
			'Custom tag with hyphenated attribute'
		);
		$test->expect(
			isset($lines[4]) && $lines[4]==$f3->stringify(
				['@attrib'=>['foo'=>'{{ @t1 }}'],'param with token']),
			'Custom tag with attribute containing token'
		);
		$test->expect(
			isset($lines[5]) && $lines[5]==$f3->stringify(
				['@attrib'=>['bar'=>'{{ @baz }}','baz'=>'abc'],'multiple params']),
			'Custom tag with mixed attributes'
		);
		$test->expect(
			isset($lines[6]) && $lines[6]==$f3->stringify(
				array('@attrib'=>array('bar'=>'baz','baz'=>'{{ @abc }}'),'multiple params switched')),
			'Custom tag with mixed attributes (switched)'
		);
		$test->expect(
			isset($lines[7]) && $lines[7]==$f3->stringify(
				array('@attrib'=>array('bar'=>'baz','class'=>'{{ @class | esc }}'),'token with format')),
			'Custom tag with attribute containing template engine formatting'
		);
		$test->expect(
			isset($lines[8]) && $lines[8]==$f3->stringify(
				array('@attrib'=>array('{{ @param }}'),'tag with inline token')),
			'Custom tag with inline token'
		);
		$test->expect(
			isset($lines[9]) && $lines[9]==$f3->stringify(
				array('@attrib'=>array('bar'=>'test10','{{ @param }}'),'param, inline token')),
			'Custom tag with attribute and inline token'
		);
		$test->expect(
			isset($lines[10]) && $lines[10]==$f3->stringify(
				array('@attrib'=>array('bar'=>'test11','rel'=>'foo','{{ @param }}'),
	  			'params, inline token and space')),
			'Custom tag with attributes, inline token, and ignored space'
		);
		$test->expect(
			isset($lines[11]) && $lines[11]==$f3->stringify(
				array('@attrib'=>array('bar'=>'test12','{{ @param }}','rel'=>'foo'),
				'param, token, param')),
			'Custom tag with attribute, inline token, and another attribute'
		);
		$test->expect(
			isset($lines[12]) && $lines[12]==$f3->stringify(
				array('@attrib'=>array('bar'=>'test13'),'simple tag')),
			'Custom tag (simple)'
		);
		$test->expect(
			isset($lines[13]) && $lines[13]==$f3->stringify(
				array('@attrib'=>array('bar'=>'test14'),'this {{ @token }} should NOT get resolved')),
			'Custom tag with inner HTML containing template token'
		);
		$test->expect(
			isset($lines[14]) && $lines[14]==$f3->stringify(
				array('@attrib'=>array('bar'=>'test15','baz'=>'abc'),'multi-line start tag')),
			'Custom tag spanning multiple lines'
		);
		$test->expect(
			isset($lines[15]) && $lines[15]==$f3->stringify(
				array('@attrib'=>array('value'=>'0'))),
			'Node attribute with 0 value'
		);
		$test->expect(
			isset($lines[16]) && $lines[16]==$f3->stringify(
				array('@attrib'=>array('bar'=>NULL,'baz'=>'1'))),
			'Node attribute with empty value'
		);
		$f3->set('foo','bar');
		$f3->set('file','templates/test14.htm');
		$test->expect(
			preg_replace('/\s*#\s*/','#',trim($tpl->render('templates/test13.htm')))=='bar#BAR,123,bar#quoted $string,unquoted string,bar#bar',
			'<include> with extended hive'
		);
		$f3->set('string','<test>');
		$obj=new \stdclass;
		$obj->content='<ok>';
		$f3->set('object',$obj);
		$f3->set('ENV.content',$obj->content);
		$test->expect(
			$f3->get('string')=='<test>' &&
			$f3->get('object')->content=='<ok>' &&
			$f3->get('ENV.content')=='<ok>' &&
			$tpl->render('templates/test12.htm')==
				'&lt;test&gt;&lt;ok&gt;&lt;ok&gt;' &&
			$f3->get('string')=='<test>' &&
			$f3->get('object')->content=='<ok>' &&
			$f3->get('ENV.content')=='<ok>',
			'Escaped values'
		);
		$test->expect(
			$tpl->resolve('<p>{{ @string }}</p>')=='<p>&lt;test&gt;</p>' &&
			$tpl->resolve('<p>{{ @ENV.content }}</p>')=='<p>&lt;ok&gt;</p>' &&
			$tpl->resolve('{* hello *}')=='',
			'resolve() template strings'
		);
		$tpl->filter('pick','\App\Helper::instance()->pick');
		$test->expect(
			$tpl->filter('pick')=='\App\Helper::instance()->pick',
			'Register new token filter'
		);
		$filters=$tpl->filter();
		$test->expect(
			in_array('esc',$filters) &&
			in_array('raw',$filters) &&
			in_array('alias',$filters) &&
			in_array('format',$filters) &&
			in_array('pick',$filters),
			'Get list of available filters'
		);
		$test->expect(
			$tpl->token($expr='@foo | pick')==
			($eval='\App\Helper::instance()->pick($foo)'),
			'Resolve custom filter: '.$expr.' - '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo, \'bar\' | pick')==
			($eval='\App\Helper::instance()->pick($foo, \'bar\')'),
			'Resolve filter with arguments: '.$expr.' - '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo, \'bar|baz\' | pick')==
			($eval='\App\Helper::instance()->pick($foo, \'bar|baz\')'),
			'Filter pipe test: '.$expr.' - '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo || @bar')==
			($eval='$foo || $bar'),
			'Double pipe OR condition: '.$expr.' - '.$eval
		);
		$test->expect(
			$tpl->token($expr='(@foo&&@bar)?@baz:@qux | esc')==
			($eval='$this->esc(($foo&&$bar)?$baz:$qux)'),
			'Ternary condition with filter: '.$expr.' - '.$eval
		);
		$test->expect(
			$tpl->token($expr='(@foo||@bar)?@baz:@qux | esc')==
			($eval='$this->esc(($foo||$bar)?$baz:$qux)'),
			'Double pipe OR condition with filter: '.$expr.' - '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo | pick, esc')==
			($eval='$this->esc(\App\Helper::instance()->pick($foo))'),
			'Multiple filter: '.$expr.' - '.$eval
		);
		$test->expect(
			$tpl->token($expr='@foo, @bar | pick, esc')==
			($eval='$this->esc(\App\Helper::instance()->pick($foo, $bar))'),
			'Multiple filter, multiple arguments: '.$expr.' - '.$eval
		);
		$test->expect(
			$tpl->render('templates/test15.html')=='applecherry',
			'Test custom filter'
		);
		$f3->set('div',
			array_fill(0,1000,array_combine(range('a','j'),range(0,9))));
		$now=microtime(TRUE);
		$test->expect(
			\View::instance()->render('benchmark.htm'),
			'Raw PHP template: '.
				round(1e3*(microtime(TRUE)-$now),2).' msecs'
		);
		$now=microtime(TRUE);
		$test->expect(
			\Template::instance()->render('templates/benchmark.htm'),
			'Use template engine: '.
				round(1e3*(microtime(TRUE)-$now),2).' msecs'
		);
		$test->expect(
			$f3->CACHE===FALSE,
			'Enable caching'
		);
		$cachedir=sprintf('tmp/cache/template_%s/',microtime(TRUE));
		$f3->CACHE='folder='.$cachedir;
		$file='templates/cache.htm';
		$test->expect(
			$tpl->render($file,null,['value'=>'nope'],0)==='nope',
			'Don\'t cache'
		);
		$test->expect(
			$tpl->render($file,null,['value'=>'cold'],2)==='cold',
			'Cache for two seconds'
		);
		$test->expect(
			$tpl->render($file,null,['value'=>'warm'],2)==='cold',
			'Load two second cached view'
		);
		sleep(3);
		$test->expect(
			$tpl->render($file,null,['value'=>'cold_again'],2)==='cold_again',
			'Replace outdated two second cached view'
		);
		$f3->CACHE=FALSE;
		foreach (glob($cachedir.'*') as $file) unlink($file);
		rmdir($cachedir);
		foreach (glob($f3->get('TEMP').
			$f3->hash($f3->get('ROOT').$f3->get('BASE')).'.*.php') as $file)
			unlink($file);
		$f3->set('results',$test->results());
	}

}
