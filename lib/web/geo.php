<?php

/*

	Copyright (c) 2009-2017 F3::Factory/Bong Cosca, All rights reserved.

	This file is part of the Fat-Free Framework (http://fatfreeframework.com).

	This is free software: you can redistribute it and/or modify it under the
	terms of the GNU General Public License as published by the Free Software
	Foundation, either version 3 of the License, or later.

	Fat-Free Framework is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
	General Public License for more details.

	You should have received a copy of the GNU General Public License along
	with Fat-Free Framework.  If not, see <http://www.gnu.org/licenses/>.

*/

namespace Web;

//! Geo plug-in
class Geo extends \Prefab {

	/**
	*	Return information about specified Unix time zone
	*	@return array
	*	@param $zone string
	**/
	function tzinfo($zone) {
		$ref=new \DateTimeZone($zone);
		$loc=$ref->getLocation();
		$trn=$ref->getTransitions($now=time(),$now);
		$out=[
			'offset'=>$ref->
				getOffset(new \DateTime('now',new \DateTimeZone('UTC')))/3600,
			'country'=>$loc['country_code'],
			'latitude'=>$loc['latitude'],
			'longitude'=>$loc['longitude'],
			'dst'=>$trn[0]['isdst']
		];
		unset($ref);
		return $out;
	}

	/**
	*	Return geolocation data based on specified/auto-detected IP address
	*	@return array|FALSE
	*	@param $ip string
	**/
	function location($ip=NULL) {
		$fw=\Base::instance();
		$web=\Web::instance();
		if (!$ip)
			$ip=$fw->IP;
		$public=filter_var($ip,FILTER_VALIDATE_IP,
			FILTER_FLAG_IPV4|FILTER_FLAG_IPV6|
			FILTER_FLAG_NO_RES_RANGE|FILTER_FLAG_NO_PRIV_RANGE);
		if (function_exists('geoip_db_avail') &&
			geoip_db_avail(GEOIP_CITY_EDITION_REV1) &&
			$out=@geoip_record_by_name($ip)) {
			$out['request']=$ip;
			$out['region_code']=$out['region'];
			$out['region_name']=(!empty($out['country_code']) && !empty($out['region']))
				? geoip_region_name_by_code($out['country_code'],$out['region']) : '';
			unset($out['country_code3'],$out['region'],$out['postal_code']);
			return $out;
		}
		if (($req=$web->request('http://www.geoplugin.net/json.gp'.
			($public?('?ip='.$ip):''))) &&
			$data=json_decode($req['body'],TRUE)) {
			$out=[];
			foreach ($data as $key=>$val)
				if (!strpos($key,'currency') && $key!=='geoplugin_status'
					&& $key!=='geoplugin_region')
					$out[$fw->snakecase(substr($key, 10))]=$val;
			return $out;
		}
		return FALSE;
	}

	/**
	*	Return weather data based on specified latitude/longitude
	*	@return array|FALSE
	*	@param $latitude float
	*	@param $longitude float
	*	@param $key string
	**/
	function weather($latitude,$longitude,$key) {
		$fw=\Base::instance();
		$web=\Web::instance();
		$query=[
			'lat'=>$latitude,
			'lon'=>$longitude,
			'APPID'=>$key,
			'units'=>'metric'
		];
		return ($req=$web->request(
			'http://api.openweathermap.org/data/2.5/weather?'.
				http_build_query($query)))?
			json_decode($req['body'],TRUE):
			FALSE;
	}

}
