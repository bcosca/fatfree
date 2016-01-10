<?php

namespace App;

class Geo extends Controller {

	function get($f3) {
		$test=new \F3\Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$geo=new \F3\Web\Geo;
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
				(isset($loc['region_name'])?(', '.$loc['region_name']):'').
				(isset($loc['country_name'])?(', '.$loc['country_name']):'').
				(isset($loc['request'])?
					(' (IP address '.$loc['request'].')'):'')
		);
		$test->expect(
			is_array($w=$geo->weather($loc['latitude'],$loc['longitude'])),
			'Weather: '.
				(isset($w['name'])?$w['name']:'').
				(isset($w['main']['temp'])?
					(', temperature: '.$w['main']['temp'].'°C'):'').
				(isset($w['wind']['speed'])?
					(', wind speed: '.((float)$w['wind']['speed']).' knots'):'')
		);
		$f3->set('results',$test->results());
	}

}
