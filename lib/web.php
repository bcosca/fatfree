<?php

/**
	Web pack for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2012 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Expansion
		@version 2.0.12
**/

//! Web pack
class Web extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_Minify='Unable to minify %s';
	//@}

	const
		//! Carriage return/line feed sequence
		EOL="\r\n";

	/**
		Return a URL/filesystem-friendly version of string
			@return string
			@param $text string
			@param $maxlen integer
	**/
	static function slug($text,$maxlen=0) {
		$out=preg_replace('/([^\w]|-)+/','-',
			trim(strtr(str_replace('\'','',$text),
			self::$vars['DIACRITICS'])));
		return trim(strtolower($maxlen?substr($out,0,$maxlen):$out),'-');
	}

	/**
		Strip Javascript/CSS files of extraneous whitespaces and comments;
		Return combined output as a minified string
			@return string
			@param $base string
			@param $files array
			@param $echo bool
			@public
	**/
	static function minify($base,array $files,$echo=TRUE) {
		$mime=array(
			'js'=>'application/x-javascript',
			'css'=>'text/css'
		);
		$path=self::fixslashes($base);
		foreach ($files as $file)
			if (!is_file($path.$file) || is_int(strpos($file,'../')) ||
				!preg_match('/\.(js|css)$/',$file,$ext) || !$ext[1]) {
				trigger_error(sprintf(self::TEXT_Minify,$file));
				return $echo?NULL:FALSE;
			}
		$src='';
		foreach ($files as $file) {
			$stats=&self::ref('STATS');
			$stats['FILES']['minified']
				[basename($file)]=filesize($path.$file);
			// Rewrite relative URLs in CSS
			$src.=preg_replace_callback(
				'/\b(?=url)\(([\"\'])?(.+?)\1\)/s',
				function($url) use($path,$file) {
					// Ignore absolute URLs
					if (preg_match('/https?:/',$url[2]))
						return $url[0];
					$fdir=dirname($file);
					$rewrite=explode(
						'/',$path.($fdir!='.'?$fdir.'/':'').$url[2]
					);
					$i=0;
					while ($i<count($rewrite))
						// Analyze each URL segment
						if ($i && $rewrite[$i]=='..' &&
							$rewrite[$i-1]!='..') {
							// Simplify URL
							unset($rewrite[$i],$rewrite[$i-1]);
							$rewrite=array_values($rewrite);
							$i--;
						}
						else
							$i++;
					// Reconstruct simplified URL
					return
						'('.implode('/',array_merge($rewrite,array())).')';
				},
				// Retrieve CSS/Javascript file
				self::getfile($path.$file)
			);
		}
		$ptr=0;
		$dst='';
		while ($ptr<strlen($src)) {
			if ($src[$ptr]=='/') {
				if (substr($src,$ptr+1,2)=='*@') {
					// Conditional block
					$str=strstr(substr($src,$ptr+3),'@*/',TRUE);
					$dst.='/*@'.$str.$src[$ptr].'@*/';
					$ptr+=strlen($str)+6;
				}
				elseif ($src[$ptr+1]=='*') {
					// Multiline comment
					$str=strstr(substr($src,$ptr+2),'*/',TRUE);
					$ptr+=strlen($str)+4;
				}
				elseif ($src[$ptr+1]=='/') {
					// Single-line comment
					$str=strstr(substr($src,$ptr+2),"\n",TRUE);
					$ptr+=strlen($str)+2;
				}
				else {
					// Presume it's a regex pattern
					$regex=TRUE;
					// Backtrack and validate
					$ofs=$ptr;
					while ($ofs) {
						$ofs--;
						// Pattern should be preceded by a punctuation
						if (ctype_punct($src[$ofs])) {
							while ($ptr<strlen($src)) {
								$str=strstr(substr($src,$ptr+1),'/',TRUE);
								if (!strlen($str) && $src[$ptr-1]!='/' ||
									strpos($str,"\n")!==FALSE) {
									// Not a regex pattern
									$regex=FALSE;
									break;
								}
								$dst.='/'.$str;
								$ptr+=strlen($str)+1;
								if ($src[$ptr-1]!='\\' ||
									$src[$ptr-2]=='\\') {
										$dst.='/';
										$ptr++;
										break;
								}
							}
							break;
						}
						elseif ($src[$ofs]!="\t" && $src[$ofs]!=' ') {
							// Not a regex pattern
							$regex=FALSE;
							break;
						}
					}
					if (!$regex) {
						// Division operator
						$dst.=$src[$ptr];
						$ptr++;
					}
				}
			}
			elseif ($src[$ptr]=='\'' || $src[$ptr]=='"') {
				$match=$src[$ptr];
				// String literal
				while ($ptr<strlen($src)) {
					$str=strstr(substr($src,$ptr+1),$src[$ptr],TRUE);
					$dst.=$match.$str;
					$ptr+=strlen($str)+1;
					if ($src[$ptr-1]!='\\' || $src[$ptr-2]=='\\') {
						$dst.=$match;
						$ptr++;
						break;
					}
				}
			}
			elseif (ctype_space($src[$ptr])) {
				if ($ptr+1<strlen($src) && preg_match(
					'/[\w'.($ext[1]=='css'?'+*#\.\-\)\]':'').']{2}/',
					substr($dst,-1).$src[$ptr+1]))
					$dst.=' ';
				$ptr++;
			}
			else {
				$dst.=$src[$ptr];
				$ptr++;
			}
		}
		if ($echo) {
			if (PHP_SAPI!='cli' && !headers_sent())
				header(self::HTTP_Content.': '.$mime[$ext[1]].'; '.
					'charset='.self::$vars['ENCODING']);
			echo $dst;
			die;
		}
		return $dst;
	}

	/**
		Convert seconds to frequency (in words)
			@return integer
			@param $secs string
			@public
	**/
	static function frequency($secs) {
		$freq['hourly']=3600;
		$freq['daily']=86400;
		$freq['weekly']=604800;
		$freq['monthly']=2592000;
		foreach ($freq as $key=>$val)
			if ($secs<=$val)
				return $key;
		return 'yearly';
	}

	/**
		Send HTTP/S request to another host; Follow 30x redirects (default);
		Forward headers received (if specified) and return content
			@return mixed
			@param $pattern string
			@param $query string
			@param $reqhdrs array
			@param $follow bool
			@param $forward bool
			@public
	**/
	static function http(
		$pattern,$query='',$reqhdrs=array(),$follow=TRUE,$forward=FALSE) {
		self::$vars['HEADERS']=array();
		// Check if valid route pattern
		list($method,$route)=explode(' ',$pattern,2);
		$url=parse_url($route);
		if (!isset($url['path']))
			// Set to Web root
			$url['path']='/';
		if ($method!='GET') {
			if (isset($url['query']) && $url['query']) {
				// Non-GET method; Query is distinct from URI
				$query=$url['query'];
				$url['query']='';
			}
		}
		else {
			if ($query) {
				// GET method; Query is integral part of URI
				$url['query']=$query;
				$query='';
			}
		}
		// Set up host name and TCP port for socket connection
		if (isset($url['scheme']) && $url['scheme']=='https') {
			if (!isset($url['port']))
				$url['port']=443;
			$target='ssl://'.$url['host'];
		}
		else {
			if (!isset($url['port']))
				$url['port']=80;
			if (!isset($url['host']))
				$url['host']=$_SERVER['SERVER_NAME'];
			$target=$url['host'];
		}
		$socket=@fsockopen($target,$url['port'],$errno,$text);
		if (!$socket) {
			// Can't establish connection
			trigger_error($text);
			return FALSE;
		}
		if (isset($url['user']) && isset($url['pass']))
			$reqhdrs[]='Authorization: Basic '.
				base64_encode($url['user'].':'.$url['pass']);
		// Set connection timeout parameters
		stream_set_blocking($socket,TRUE);
		stream_set_timeout($socket,ini_get('default_socket_timeout'));
		// Send HTTP request
		fputs($socket,
			$method.' '.(isset($url['path'])?$url['path']:'').
				(isset($url['query']) && $url['query']?
					('?'.$url['query']):'').' '.
					'HTTP/1.0'.self::EOL.
				self::HTTP_Host.': '.$url['host'].self::EOL.
				self::HTTP_Agent.': Mozilla/5.0 '.
					'(compatible;'.PHP_OS.')'.self::EOL.
				($reqhdrs?
					(implode(self::EOL,$reqhdrs).self::EOL):'').
				($method!='GET'?(
					'Content-Type: '.
						'application/x-www-form-urlencoded'.self::EOL.
					'Content-Length: '.strlen($query).self::EOL):'').
				self::HTTP_AcceptEnc.': gzip'.self::EOL.
				self::HTTP_Connect.': close'.self::EOL.self::EOL.
			$query.self::EOL.self::EOL
		);
		$found=FALSE;
		$gzip=FALSE;
		$rcvhdrs='';
		$info=stream_get_meta_data($socket);
		// Get headers and response
		$response='';
		while (!feof($socket) && !$info['timed_out']) {
			$response.=fgets($socket,4096); // MDFK97
			$info=stream_get_meta_data($socket);
			if (!$found && is_int(strpos($response,self::EOL.self::EOL))) {
				$found=TRUE;
				$rcvhdrs=strstr($response,self::EOL.self::EOL,TRUE);
				ob_start();
				if ($follow &&
					preg_match('/HTTP\/1\.\d\s30\d/',$rcvhdrs)) {
					// Redirection
					preg_match('/'.self::HTTP_Location.
						':\s*(.+)/',$rcvhdrs,$loc);
					foreach ($reqhdrs as $key=>$hdr)
						if (preg_match('/Authorization:/',$hdr) &&
							$loc[1][0]!='/') {
							unset($reqhdrs[$key]);
							break;
						}
					return self::http($method.' '.trim($loc[1]),
						$query,$reqhdrs);
				}
				foreach (explode(self::EOL,$rcvhdrs) as $hdr) {
					self::$vars['HEADERS'][]=$hdr;
					if (PHP_SAPI!='cli' && $forward)
						// Forward HTTP header
						header($hdr);
					elseif (preg_match('/^'.
						self::HTTP_Encoding.':\s*.*gzip/',$hdr))
						// Uncompress content
						$gzip=TRUE;
				}
				ob_end_flush();
				// Split content from HTTP response headers
				$response=substr(strstr($response,self::EOL.self::EOL),4);
			}
		}
		fclose($socket);
		if ($info['timed_out'])
			return FALSE;
		if (PHP_SAPI!='cli') {
			if ($gzip)
				$response=gzinflate(substr($response,10));
		}
		// Return content
		return $response;
	}

	/**
		Parse each URL recursively and generate sitemap
			@param $url string
			@public
	**/
	static function sitemap($url=NULL) {
		if (is_null($url))
			$url=self::$vars['BASE'].'/';
		if ($url[0]=='#' || isset(self::$vars['SITEMAP'][$url]) &&
			is_bool(self::$vars['SITEMAP'][$url]['status']))
			// Skip
			return;
		$parse=parse_url($url);
		if (isset($parse['scheme']) &&
			!preg_match('/https?:/',$parse['scheme']))
			return;
		$response=self::http('GET '.self::$vars['PROTOCOL'].'://'.
			$_SERVER['SERVER_NAME'].$url);
		if (!$response) {
			// No HTTP response
			self::$vars['SITEMAP'][$url]['status']=FALSE;
			return;
		}
		foreach (self::$vars['HEADERS'] as $header)
			if (preg_match('/HTTP\/\d\.\d\s(\d+)/',$header,$match) &&
				$match[1]!=200) {
				self::$vars['SITEMAP'][$url]['status']=FALSE;
				return;
			}
		$doc=new DOMDocument('1.0',self::$vars['ENCODING']);
		// Suppress errors caused by invalid HTML structures
		libxml_use_internal_errors(TRUE);
		if ($doc->loadHTML($response)) {
			// Valid HTML; add to sitemap
			if (!self::$vars['SITEMAP'][$url]['level'])
				// Web root
				self::$vars['SITEMAP'][$url]['level']=0;
			self::$vars['SITEMAP'][$url]['status']=TRUE;
			self::$vars['SITEMAP'][$url]['mod']=time();
			self::$vars['SITEMAP'][$url]['freq']=0;
			// Cached page
			$hash='url.'.self::hash('GET '.$url);
			$cached=Cache::cached($hash);
			if ($cached) {
				self::$vars['SITEMAP'][$url]['mod']=$cached['time'];
				self::$vars['SITEMAP'][$url]['freq']=$_SERVER['REQUEST_TTL'];
			}
			// Parse all links
			$links=$doc->getElementsByTagName('a');
			foreach ($links as $link) {
				$ref=$link->getAttribute('href');
				preg_match('/^http[s]*:\/\/([^\/$]+)/',$ref,$host);
				if (!empty($host) && $host[1]!=$_SERVER['SERVER_NAME'] ||
					!$ref || ($rel=$link->getAttribute('rel')) &&
					preg_match('/nofollow/',$rel))
					// Don't crawl this link!
					continue;
				if (!isset(self::$vars['SITEMAP'][$ref]))
					self::$vars['SITEMAP'][$ref]=array(
						'level'=>self::$vars['SITEMAP'][$url]['level']+1,
						'status'=>NULL
					);
			}
			// Parse each link
			$map=array_keys(self::$vars['SITEMAP']);
			array_walk($map,'self::sitemap');
		}
		unset($doc);
		if (!self::$vars['SITEMAP'][$url]['level']) {
			// Finalize sitemap
			$depth=1;
			while ($ref=current(self::$vars['SITEMAP']))
				// Find deepest level while iterating
				if (!$ref['status'])
					// Remove remote URLs and pages with errors
					unset(self::$vars['SITEMAP']
						[key(self::$vars['SITEMAP'])]);
				else {
					$depth=max($depth,$ref['level']+1);
					next(self::$vars['SITEMAP']);
				}
			// Create XML document
			$xml=simplexml_load_string(
				'<?xml version="1.0" encoding="'.
					self::$vars['ENCODING'].'"?>'.
				'<urlset xmlns='.
					'"http://www.sitemaps.org/schemas/sitemap/0.9"'.
				'/>'
			);
			$host=self::$vars['PROTOCOL'].'://'.$_SERVER['SERVER_NAME'];
			foreach (self::$vars['SITEMAP'] as $key=>$ref) {
				// Add new URL
				$item=$xml->addChild('url');
				// Add URL elements
				$item->addChild('loc',$host.($key[0]=='/'?'':'/').$key);
				$item->addChild('lastmod',gmdate('c',$ref['mod']));
				$item->addChild('changefreq',
					self::frequency($ref['freq']));
				$item->addChild('priority',
					sprintf('%1.1f',1-$ref['level']/$depth));
			}
			// Send output
			if (PHP_SAPI!='cli' && !headers_sent())
				header(self::HTTP_Content.': application/xml; '.
					'charset='.self::$vars['ENCODING']);
			$xml=dom_import_simplexml($xml)->ownerDocument;
			$xml->formatOutput=TRUE;
			echo $xml->saveXML();
			die;
		}
	}

	/**
		Return TRUE if HTTP request origin is AJAX
			@return bool
			@public
	**/
	static function isajax() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			$_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest';
	}

	/**
		Class initializer
			@public
	**/
	static function onload() {
		if (!extension_loaded('sockets'))
			// Sockets extension required
			trigger_error(sprintf(self::TEXT_PHPExt,'sockets'));
		// Default translations
		$diacritics=array(
			'À'=>'A','Á'=>'A','Â'=>'A','Ã'=>'A','Å'=>'A','Ä'=>'A','Æ'=>'AE',
			'à'=>'a','á'=>'a','â'=>'a','ã'=>'a','å'=>'a','ä'=>'a','æ'=>'ae',
			'Þ'=>'B','þ'=>'b','Č'=>'C','Ć'=>'C','Ç'=>'C','č'=>'c','ć'=>'c',
			'ç'=>'c','Ď'=>'D','ð'=>'d','ď'=>'d','Đ'=>'Dj','đ'=>'dj','È'=>'E',
			'É'=>'E','Ê'=>'E','Ë'=>'E','è'=>'e','é'=>'e','ê'=>'e','ë'=>'e',
			'Ì'=>'I','Í'=>'I','Î'=>'I','Ï'=>'I','ì'=>'i','í'=>'i','î'=>'i',
			'ï'=>'i','Ľ'=>'L','ľ'=>'l','Ñ'=>'N','Ň'=>'N','ñ'=>'n','ň'=>'n',
			'Ò'=>'O','Ó'=>'O','Ô'=>'O','Õ'=>'O','Ø'=>'O','Ö'=>'O','Œ'=>'OE',
			'ò'=>'o','ó'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','œ'=>'oe','ø'=>'o',
			'Ŕ'=>'R','Ř'=>'R','ŕ'=>'r','ř'=>'r','Š'=>'S','š'=>'s','ß'=>'ss',
			'Ť'=>'T','ť'=>'t','Ù'=>'U','Ú'=>'U','Û'=>'U','Ü'=>'U','Ů'=>'U',
			'ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u','ů'=>'u','Ý'=>'Y','Ÿ'=>'Y',
			'ý'=>'y','ÿ'=>'y','Ž'=>'Z','ž'=>'z'
		);
		self::$vars['DIACRITICS']=isset(self::$vars['DIACRITICS'])?
			$diacritics+self::$vars['DIACRITICS']:$diacritics;
		// Site structure
		self::$vars['SITEMAP']=NULL;
	}

}
