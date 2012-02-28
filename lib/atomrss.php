<?php

/**
	Atom/RSS feed reader for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package AtomRSS
		@version 2.0.9
**/

//! Atom/RSS feed reader
class AtomRSS extends Base {

	/**
		Retrieve RSS/Atom feed and return as an array
			@return mixed
			@param $url string
			@param $count int
			@param $tags string
			@public
	**/
	static function read($url,$count=10,$tags='b;i;u;a') {
		$data=Web::http('GET '.$url);
		if (!$data)
			return FALSE;
		$xml=simplexml_load_string(
			$data,'SimpleXMLElement',LIBXML_NOCDATA|LIBXML_ERR_FATAL
		);
		if (!is_object($xml))
			return FALSE;
		$result=array();
		if (isset($xml->channel)) {
			$result['source']=(string)$xml->channel->title;
			foreach ($xml->channel->item as $item)
				$result['feed'][]=array(
					'title'=>(string)$item->title,
					'link'=>(string)$item->link,
					'text'=>strip_tags($item->description,
						'<'.implode('><',self::split($tags)).'>')
				);
		}
		elseif (isset($xml->entry)) {
			$result['source']=(string)$xml->author->name;
			foreach ($xml->entry as $item)
				$result['feed'][]=array(
					'title'=>(string)$item->title,
					'link'=>(string)$item->link['href'],
					'text'=>strip_tags($item->summary,
						'<'.implode('><',self::split($tags)).'>')
				);
		}
		else
			return FALSE;
		return array_slice($result,0,$count);
	}

}
