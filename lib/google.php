<?php

/**
	Google plugin for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Google
		@version 2.0.9
**/

//! Collection of Google API adaptors
class Google extends Base {

	/**
		Language translator using Google AJAX Language API
			@return mixed
			@param $text string
			@param $from string
			@param $to string
			@public
	**/
	static function translate($text,$from,$to) {
		$result=json_decode(
			Web::http(
				'GET http://ajax.googleapis.com/'.
					'ajax/services/language/translate',
				http_build_query(
					array(
						'v'=>'1.0',
						'q'=>$text,
						'langpair'=>$from.'|'.$to
					)
				)
			),
			TRUE
		);
		if (is_null($result['responseData'])) {
			trigger_error($result['responseDetails']);
			return FALSE;
		}
		return $result['responseData']['translatedText'];
	}

	/**
		Call Geocoding API and return geographical coordinates of
		specified address
			@return array
			@param $addr string
	**/
	static function geocode($addr) {
		$result=json_decode(
			Web::http(
				'GET http://maps.googleapis.com/maps/api/geocode/json?',
				http_build_query(
					array(
						'address'=>$addr,
						'sensor'=>'false'
					)
				)
			),
			TRUE
		);
		if (is_null($result['results'])) {
			trigger_error($result['status']);
			return FALSE;
		}
		return $result['results'][0]['geometry'];
	}

	/**
		Generate static map using Google Maps API
			@param $center string
			@param $zoom integer
			@param $size string
			@param $type string
			@param $format string
			@param $language string
			@param $markers array
			@public
	**/
	static function
		staticmap(
			$center,
			$zoom=15,
			$size='400x400',
			$type='roadmap',
			$format='png',
			$language='en',
			array $markers=NULL) {
		echo Web::http(
			'GET http://maps.google.com/maps/api/staticmap',
			http_build_query(
				array_merge(
					array(
						'center'=>$center,
						'zoom'=>$zoom,
						'size'=>$size,
						'maptype'=>$type,
						'format'=>$format,
						'language'=>$language,
						'sensor'=>'true'
					),
					$markers?$markers:array()
				)
			)
		);
	}

	/**
		Web search using Google AJAX Search API
			@return mixed
			@param $text string
			@param $page integer
			@public
	**/
	static function search($text,$page=0) {
		$result=json_decode(
			Web::http(
				'GET http://ajax.googleapis.com/ajax/services/search/web',
				http_build_query(
					array(
						'v'=>'1.0',
						'q'=>$text,
						'rsz'=>'large',
						'start'=>8*$page
					)
				)
			),
			TRUE
		);
		if (is_null($result['responseData'])) {
			trigger_error($result['responseDetails']);
			return FALSE;
		}
		foreach ($result['responseData']['results'] as &$data)
			$data=array(
				'url'=>$data['unescapedUrl'],
				'title'=>$data['title'],
				'content'=>$data['content']
			);
		return array(
			'page'=>$result['responseData']['cursor']['currentPageIndex'],
			'results'=>$result['responseData']['results']
		);
	}

	/**
		Retrieve Atom/RSS feed using Google AJAX Feed API; If second
		argument is TRUE, XML string returned; Otherwise, a PHP array
			@return mixed
			@param $url string
			@param $isxml boolean
			@public
	**/
	static function feed($url,$isxml=TRUE) {
		$result=json_decode(
			Web::http(
				'GET http://ajax.googleapis.com/ajax/services/feed/load',
				http_build_query(
					array(
						'v'=>'1.0',
						'q'=>$url,
						'num'=>'-1',
						'output'=>$isxml?'xml':'json'
					)
				)
			),
			TRUE
		);
		if (is_null($result['responseData'])) {
			trigger_error($result['responseDetails']);
			return FALSE;
		}
		return $result['responseData'][$isxml?'xmlString':'feed'];
	}

}
