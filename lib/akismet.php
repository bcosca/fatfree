<?php

/**
	Akismet plugin for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Akismet
		@version 2.0.9
**/

//! Akismet API adaptor
class Akismet extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_VerifyFail='Akismet verification failed';
	//@}

	static
		//! Akismet key verification flag
		$verified=FALSE,
		//! Verification key (NULL if unverified/invalid)
		$key=NULL;

	/**
		Akismet key verification
			@return boolean
			@param $key string
			@public
	**/
	static function verify($key) {
		$response=Web::http(
			'GET http://rest.akismet.com/1.1/verify-key',
			http_build_query(
				array(
					'key'=>$key,
					'blog'=>'http://'.$_SERVER['SERVER_NAME']
				)
			)
		);
		if (preg_match('/invalid/i',$response)) {
			trigger_error(self::TEXT_VerifyFail);
			self::$key=NULL;
		}
		else
			self::$key=$key;
		return self::$key;
	}

	/**
		Forward content for spam checking
			@return boolean
			@param $text string
			@param $author string
			@param $email string
			@param $url string
			@public
	**/
	static function check($text,$author,$email,$url) {
		$response=Web::http(
			'GET http://'.self::$key.'.rest.akismet.com/1.1/comment-check',
			http_build_query(
				array(
					'comment_content'=>$text,
					'comment_author'=>$author,
					'comment_author_email'=>$email,
					'comment_author_url'=>$url,
					'user_agent'=>$_SERVER['HTTP_USER_AGENT'],
					'referrer'=>$_SERVER['HTTP_REFERER'],
					'blog'=>'http://'.$_SERVER['SERVER_NAME'],
					'permalink'=>'http://'.$_SERVER['SERVER_NAME'].'/'.
						$_SERVER['REQUEST_URI']
				)
			)
		);
		return (boolean)$response;
	}

	/**
		Report content that was not marked as spam
			@return boolean
			@param $text string
			@param $author string
			@param $email string
			@param $url string
			@public
	**/
	static function spam($text,$author,$email,$url) {
		$response=Web::http(
			'GET http://'.self::$key.'.rest.akismet.com/1.1/submit-spam',
			http_build_query(
				array(
					'comment_content'=>$text,
					'comment_author'=>$author,
					'comment_author_email'=>$email,
					'comment_author_url'=>$url,
					'user_agent'=>$_SERVER['HTTP_USER_AGENT'],
					'referrer'=>$_SERVER['HTTP_REFERER'],
					'blog'=>'http://'.$_SERVER['SERVER_NAME'],
					'permalink'=>'http://'.$_SERVER['SERVER_NAME'].'/'.
						$_SERVER['REQUEST_URI']
				)
			)
		);
		return (boolean)$response;
	}

	/**
		Report content as a false positive
			@return boolean
			@param $text string
			@param $author string
			@param $email string
			@param $url string
			@public
	**/
	static function ham($text,$author,$email,$url) {
		$response=Web::http(
			'GET http://'.self::$key.'.rest.akismet.com/1.1/submit-ham',
			http_build_query(
				array(
					'comment_content'=>$text,
					'comment_author'=>$author,
					'comment_author_email'=>$email,
					'comment_author_url'=>$url,
					'user_agent'=>$_SERVER['HTTP_USER_AGENT'],
					'referrer'=>$_SERVER['HTTP_REFERER'],
					'blog'=>'http://'.$_SERVER['SERVER_NAME'],
					'permalink'=>'http://'.$_SERVER['SERVER_NAME'].'/'.
						$_SERVER['REQUEST_URI']
				)
			)
		);
		return (boolean)$response;
	}

}
