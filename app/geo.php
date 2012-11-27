<?php

namespace App;

class Geo extends Controller {

	function get() {
		$f3=\Base::instance();
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$geo=new \Web\Geo;
		$test->expect(
			($info=$geo->tzinfo($tz=$f3->get('TZ'))) &&
				isset($info['offset']) && isset($info['country']) &&
				isset($info['latitude']) && isset($info['longitude']) &&
				isset($info['dst']),
			'Server timezone info: '.$tz
		);
		$test->expect(
			is_array($loc=$geo->location()),
			'Detect geolocation: '.(isset($loc['city'])?$loc['city']:'').
				(isset($loc['countryName'])?(', '.$loc['countryName']):'').
				(isset($loc['request'])?
					(' (IP address '.$loc['request'].')'):'')
		);
		$f3->set('results',$test->results());
	}

}
