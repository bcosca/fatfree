<?php

/*

	Copyright (c) 2009-2015 F3::Factory/Bong Cosca, All rights reserved.

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

namespace Web\Google;

//! Google Static Maps API v2 plug-in
class StaticMap {

	const
		//! API URL
		URL_Static='http://maps.googleapis.com/maps/api/staticmap';

	protected
		//! Query arguments
		$query=array();

	/**
	*	Specify API key-value pair via magic call
	*	@return object
	*	@param $func string
	*	@param $args array
	**/
	function __call($func,array $args) {
		$this->query[]=array($func,$args[0]);
		return $this;
	}

	/**
	*	Generate map
	*	@return string
	**/
	function dump() {
		$fw=\Base::instance();
		$web=\Web::instance();
		$out='';
		return ($req=$web->request(
			self::URL_Static.'?'.array_reduce(
				$this->query,
				function($out,$item) {
					return ($out.=($out?'&':'').
						urlencode($item[0]).'='.urlencode($item[1]));
				}
			))) && $req['body']?$req['body']:FALSE;
	}

}
