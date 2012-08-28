<?php

/**
	Template engine for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2012 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Template
		@version 2.0.13
**/

//! Template engine
class Template extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_Render='Template %s cannot be rendered';
	//@}

	/**
		Render template
			@return string
			@param $file string
			@param $mime string
			@param $globals boolean
			@param $syms array
			@public
	**/
	static function serve($file,
		$mime='text/html',$globals=TRUE,$syms=array()) {
		$file=self::resolve($file);
		$found=FALSE;
		foreach (preg_split('/[\|;,]/',self::$vars['GUI'],0,
			PREG_SPLIT_NO_EMPTY) as $gui) {
			if (is_file($view=self::fixslashes($gui.$file))) {
				$found=TRUE;
				break;
			}
		}
		if (!$found) {
			trigger_error(sprintf(self::TEXT_Render,$file));
			return '';
		}
		if (PHP_SAPI!='cli' && !headers_sent())
			// Send HTTP header with appropriate character set
			header(self::HTTP_Content.': '.$mime.'; '.
				'charset='.self::$vars['ENCODING']);
		$hash='tpl.'.self::hash($view);
		$cached=Cache::cached($hash);
		if ($cached && filemtime($view)<$cached) {
			if (self::$vars['CACHE'])
				// Retrieve PHP-compiled template from cache
				$text=Cache::get($hash);
		}
		else {
			// Parse raw template
			$doc=new F3markup($mime,$globals);
			$text=$doc->load(self::getfile($view),$syms);
			if (self::$vars['CACHE'] && $doc::$cache)
				// Save PHP-compiled template to cache
				Cache::set($hash,$text);
		}
		// Render in a sandbox
		$instance=new F3instance;
		ob_start();
		if (ini_get('allow_url_fopen') && ini_get('allow_url_include'))
			// Stream wrap
			$instance->sandbox('data:text/plain,'.urlencode($text),$syms);
		else {
			// Save PHP-equivalent file in temporary folder
			if (!is_dir(self::$vars['TEMP']))
				self::mkdir(self::$vars['TEMP']);
			$temp=self::$vars['TEMP'].$_SERVER['SERVER_NAME'].'.'.$hash;
			if (!$cached || !is_file($temp) ||
				filemtime($temp)<Cache::cached($view)) {
				// Create semaphore
				$hash='sem.'.self::hash($view);
				while ($cached=Cache::cached($hash))
					// Locked by another process
					usleep(mt_rand(0,100));
				Cache::set($hash,TRUE);
				self::putfile($temp,$text);
				// Remove semaphore
				Cache::clear($hash);
			}
			$instance->sandbox($temp,$syms);
		}
		$out=ob_get_clean();
		unset($instance);
		return self::$vars['TIDY']?self::tidy($out):$out;
	}

}
