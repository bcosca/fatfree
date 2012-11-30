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
		($ip===NULL && $_SERVER['REMOTE_ADDR'])?$ip=$_SERVER['REMOTE_ADDR']
			:$ip='127.0.0.1';
		if ($ip!='127.0.0.1' && function_exists('geoip_db_avail')
			&& geoip_db_avail(GEOIP_CITY_EDITION_REV1)
			&& geoip_db_avail(GEOIP_COUNTRY_EDITION))
			if ($out=@geoip_record_by_name($ip)) {
				$out['request']=$ip;
				$out['region_code']=$out['region'];
				$out['region_name']=geoip_region_name_by_code(
					$out['country_code'],$out['region']);
				unset($out['country_code3'],$out['postal_code'],$out['region']);
				return $out;
			}

		if ($req=\Web::instance()->request(
			'http://www.geoplugin.net/json.gp'
			.(($ip!='127.0.0.1')?('?ip='.$ip):''))) {
			if ($data=@json_decode($req['body'],TRUE)) {
				$out=array();
				foreach ($data as $key=>$val)
					if (!strpos($key,'currency') && $key!=='geoplugin_status'
						&& $key!=='geoplugin_region')
						$out[strtolower(preg_replace('/[[:upper:]]/','_\0',
							substr($key, 10)))]=$val;
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