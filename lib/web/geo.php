<?php

namespace Web;

//! Geo plugin
class Geo {

	/**
		Return information about specified Unix time zone
		@return array
		@param $zone string
	**/
	function tzinfo($zone) {
		$ref=new \DateTimeZone($zone);
		$loc=$ref->getLocation();
		$trn=$ref->getTransitions($now=time(),$now);
		return array(
			'offset'=>$ref->
				getOffset(new \DateTime('now',new \DateTimeZone('GMT')))/3600,
			'country'=>$loc['country_code'],
			'latitude'=>$loc['latitude'],
			'longitude'=>$loc['longitude'],
			'dst'=>$trn[0]['isdst']
		);
	}

	/**
		Return geolocation data based on specified/auto-detected IP address
		@return array|FALSE
		@param $ip string
	**/
	function location($ip=NULL) {
		if ($req=\Web::instance()->request(
			'http://www.geoplugin.net/json.gp'.($ip?('?ip='.$ip):''))) {
			$out=array();
			if ($data=@json_decode($req['body'],TRUE)) {
				foreach ($data as $key=>$val)
					$out[str_replace('geoplugin_','',$key)]=$val;
				return $out;
			}
		}
		return FALSE;
	}

	/**
		Instantiate class
		@return $obj
	**/
	static function instance() {
		if (!\Registry::exists($class=__CLASS__))
			\Registry::set($class,$self=new $class);
		return \Registry::get($class);
	}

	//! Wrap-up
	function __destruct() {
		\Registry::clear(__CLASS__);
	}

}
