<?php

/**
	Yahoo plugin for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2012 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Yahoo
		@version 2.0.10
**/

//! API wrapper for Yahoo! Web services
class Yahoo extends Base {

	/**
		Notify Yahoo! of changes to Web page by submitting the sitemap
		location or Web page URL
			@return boolean
			@param $url string
			@public
	**/
	static function ping($url) {
		$result=simplexml_load_string(
			Web::http(
				'GET http://search.yahooapis.com/'.
					'SiteExplorerService/V1/ping',
				http_build_query(array('sitemap'=>$url))
			)
		);
		if ($result->getName()!='Success') {
			trigger_error($result['Message']);
			return FALSE;
		}
		return TRUE;
	}

	/**
		Retrieve info about inbound links to a particular page
			@return mixed
			@param $appid string
			@param $path string
			@param $count int
			@param $start int
			@param $omit string
			@public
	**/
	static function inlinks($appid,$path,$count=100,$start=1,$omit='') {
		$result=simplexml_load_string(
			Web::http(
				'GET http://search.yahooapis.com/'.
					'SiteExplorerService/V1/inlinkData',
				http_build_query(
					array(
						'appid'=>$appid,
						'query'=>$path,
						'results'=>$count,
						'start'=>$start,
						'omit_inlinks'=>$omit,
						'output'=>'xml'
					)
				)
			)
		);
		if ($result->getName()!='ResultSet') {
			trigger_error($result['Message']);
			return FALSE;
		}
		$out=array();
		foreach ($result->attributes() as $key=>$val)
			$out[$key]=(int)$val;
		foreach ($result->Result as $item) {
			$out['Result'][]=
				array(
					'title'=>(string)$item->Title,
					'url'=>(string)$item->Url
				);
		}
		return $out;
	}

	/**
		Return online status of a Yahoo! ID
			@return boolean
			@param $id string
			@public
	**/
	static function online($id) {
		$result=Web::http(
			'GET http://opi.yahoo.com/online',
			http_build_query(
				array(
					'u'=>$id,
					'm'=>'a',
					't'=>1
				)
			)
		);
		return (boolean)(int)$result;
	}

}
