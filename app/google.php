<?php

namespace App;

class Google extends Controller {

	function get($f3) {
		$test=new \F3\Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$f3=\F3\Base::instance();
		$map=new \F3\Web\Google\StaticMap;
		$test->expect(
			$img=$f3->base64(
				$map->center('Brooklyn Bridge, New York, NY')->zoom(13)->
				size('480x360')->sensor('false')->maptype('roadmap')->
				markers('color:blue|label:S|40.702127,-74.015794')->
				markers('color:green|label:G|40.711614,-74.012318')->
				markers('color:red|label:C|40.718217,-73.998284')->
				dump(),'image/png'
			),
			'Static map<br />'.
			'<img src="'.$img.'" title="Static Map" />'
		);
		$f3->set('ESCAPE',FALSE);
		$f3->set('results',$test->results());
	}

}
