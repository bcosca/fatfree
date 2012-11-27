<?php

namespace App;

class Text extends Controller {

	function get() {
		$f3=\Base::instance();
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$txt=new \Text;
		$test->expect(
			$txt->snakecase('helloWorld')=='hello_world',
			'Snake-case'
		);
		$test->expect(
			$txt->camelcase('hello_world')=='helloWorld',
			'Camel-case'
		);
		$old=trim($f3->read('diff/diff1.txt'));
		$new=trim($f3->read('diff/diff2.txt'));
		$diff=$txt->diff($old,$new,'');
		$test->expect(
			is_array($diff) && isset($diff['patch']) &&
			$txt->patch($old,$diff['patch'],'')==$new &&
			$txt->patch($new,$diff['patch'],'',TRUE)==$old,
			'Character-based diff/patch'
		);
		$diff=$txt->diff($old,$new,"\n");
		$test->expect(
			is_array($diff) && isset($diff['patch']) &&
			$txt->patch($old,$diff['patch'],"\n")==$new &&
			$txt->patch($new,$diff['patch'],"\n",TRUE)==$old,
			'Line-by-line diff/patch'
		);
		$diff=$txt->diff($old,$new," ");
		$test->expect(
			is_array($diff) && isset($diff['patch']) &&
			$txt->patch($old,$diff['patch']," ")==$new &&
			$txt->patch($new,$diff['patch']," ",TRUE)==$old,
			'Space-delimited diff/patch'
		);
		$f3->set('results',$test->results());
	}

}
