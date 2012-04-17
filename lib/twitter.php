<?php

/**
	Twitter plugin for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2012 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Twitter
		@version 2.0.10
**/

//! Collection of Twitter API adaptors
class Twitter extends Base {

	/**
		Search via Twitter API
			@return array
			@param $query string
			@param $page integer
			@param $since string
			@param $type string
			@param $language string
			@public
	**/
	static function search(
		$query,$page=1,$since=NULL,$type='mixed',$language='en') {
		if (is_null($since))
			$since=gmdate('Y-m-d',time());
		$response=json_decode(
			Web::http(
				'GET http://search.twitter.com/search.json',
				http_build_query(
					array(
						'q'=>$query,
						'lang'=>$language,
						'page'=>$page,
						'rpp'=>10,
						'since'=>$since,
						'result_type'=>$type
					)
				)
			),
			TRUE
		);
		if ($response['error']) {
			trigger_error($response['error']);
			return FALSE;
		}
		return $response;
	}

	/**
		Get user information via Twitter API
			@return array
			@param $userid string
			@public
	**/
	static function show($userid) {
		$response=json_decode(
			Web::http(
				'GET http://api.twitter.com/1/users/show/'.
					$userid.'.json'
			),
			TRUE
		);
		if ($response['error']) {
			trigger_error($response['error']);
			return FALSE;
		}
		return $response;
	}

	/**
		Get information about user's friends via Twitter API
			@return array
			@param $userid string
			@public
	**/
	static function friends($userid) {
		$response=json_decode(
			Web::http(
				'GET http://api.twitter.com/1/statuses/friends/'.
					$userid.'.json'
			),
			TRUE
		);
		if ($response['error']) {
			trigger_error($response['error']);
			return FALSE;
		}
		return $response;
	}

	/**
		Get information about user's followers via Twitter API
			@return array
			@param $userid string
			@public
	**/
	static function followers($userid) {
		$response=json_decode(
			Web::http(
				'GET http://api.twitter.com/1/statuses/followers/'.
					$userid.'.json'
			),
			TRUE
		);
		if ($response['error']) {
			trigger_error($response['error']);
			return FALSE;
		}
		return $response;
	}

}
