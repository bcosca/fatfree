<?php

/*
	Copyright (c) 2009-2012 F3::Factory/Bong Cosca, All rights reserved.

	This file is part of the Fat-Free Framework (http://fatfree.sf.net).

	THE SOFTWARE AND DOCUMENTATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF
	ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR
	PURPOSE.

	Please see the license.txt file for more information.
*/

//! Wrapper for various HTTP utilities
class Web extends Prefab {

	/**
		Detect MIME type using file extension
		@return string
		@param $file string
	**/
	function mime($file) {
		if (preg_match('/\w+$/',$file,$ext)) {
			$map=array(
				'au'=>'audio/basic',
				'avi'=>'video/avi',
				'bmp'=>'image/bmp',
				'bz2'=>'application/x-bzip2',
				'css'=>'text/css',
				'dtd'=>'application/xml-dtd',
				'doc'=>'application/msword',
				'gif'=>'image/gif',
				'gz'=>'application/x-gzip',
				'hqx'=>'application/mac-binhex40',
				'html?'=>'text/html',
				'jar'=>'application/java-archive',
				'jpe?g'=>'image/jpeg',
				'js'=>'application/x-javascript',
				'midi'=>'audio/x-midi',
				'mp3'=>'audio/mpeg',
				'mpe?g'=>'video/mpeg',
				'ogg'=>'audio/vorbis',
				'pdf'=>'application/pdf',
				'png'=>'image/png',
				'ppt'=>'application/vnd.ms-powerpoint',
				'ps'=>'application/postscript',
				'qt'=>'video/quicktime',
				'ram?'=>'audio/x-pn-realaudio',
				'rdf'=>'application/rdf',
				'rtf'=>'application/rtf',
				'sgml?'=>'text/sgml',
				'sit'=>'application/x-stuffit',
				'svg'=>'image/svg+xml',
				'swf'=>'application/x-shockwave-flash',
				'tgz'=>'application/x-tar',
				'tiff'=>'image/tiff',
				'txt'=>'text/plain',
				'wav'=>'audio/wav',
				'xls'=>'application/vnd.ms-excel',
				'xml'=>'application/xml',
				'zip'=>'application/zip'
			);
			foreach ($map as $key=>$val)
				if (preg_match('/'.$key.'/',$ext[0]))
					return $val;
		}
		return 'application/octet-stream';
	}

	/**
		Transmit file to HTTP client; Return file size if successful,
		FALSE otherwise
		@return int|FALSE
		@param $file string
		@param $mime string
		@param $kbps int
	**/
	function send($file,$mime=NULL,$kbps=0) {
		if (!is_file($file))
			return FALSE;
		if (PHP_SAPI!='cli') {
			header('Content-Type: '.$mime?:$this->mime($file));
			if ($mime=='application/octet-stream')
				header('Content-Disposition: attachment; '.
					'filename='.basename($file));
			header('Accept-Ranges: bytes');
			header('Content-Length: '.$size=filesize($file));
			header('X-Powered-By: '.Base::instance()->get('PACKAGE'));
		}
		$ctr=0;
		$handle=fopen($file,'rb');
		$start=microtime(TRUE);
		while (!feof($handle) &&
			($info=stream_get_meta_data($handle)) &&
			!$info['timed_out'] && !connection_aborted()) {
			if ($kbps) {
				// Throttle output
				$ctr++;
				if ($ctr/$kbps>$elapsed=microtime(TRUE)-$start)
					usleep(1e6*($ctr/$kbps-$elapsed));
			}
			// Send 1KiB and reset timer
			echo fread($handle,1024);
		}
		fclose($handle);
		return $size;
	}

	/**
		Receive file(s) from HTTP client; Return file size if successful,
		FALSE otherwise
		@return int|FALSE
		@param $func callback
		@param $overwrite bool
		@param $slug bool
	**/
	function receive($func=NULL,$overwrite=FALSE,$slug=TRUE) {
		$fw=Base::instance();
		$dir=$fw->get('UPLOADS');
		if (!is_dir($dir))
			mkdir($dir,Base::MODE,TRUE);
		if ($fw->get('VERB')=='PUT') {
			$fw->write($dir.basename($fw->get('URI')),$fw->get('BODY'));
			return TRUE;
		}
		if ($fw->get('VERB')=='POST')
			foreach ($_FILES as $item) {
				if (is_array($item['name'])) {
					// Transpose array
					$out=array();
					foreach ($item as $keyx=>$cols)
						foreach ($cols as $keyy=>$valy)
							$out[$keyy][$keyx]=$valy;
					$item=$out;
				}
				else
					$item=array($item);
				foreach ($item as $file) {
					if (empty($file['name']))
						return FALSE;
					$base=basename($file['name']);
					if ($slug) {
						preg_match('/(.+?)(\.\w+)?$/',$base,$parts);
						$dst=$dir.$this->slug($parts[1]).$parts[2];
					}
					else
						$dst=$dir.$base;
					if ($file['error'] ||
						$file['type']!=$this->mime($file['name']) ||
						$overwrite && file_exists($dst) ||
						$func && !$fw->call($func,array($file)) ||
						!move_uploaded_file($file['tmp_name'],$dst))
						return FALSE;
				}
				return TRUE;
			}
		return FALSE;
	}

	/**
		Return upload progress in bytes, FALSE on failure
		@return int|FALSE
		@param $id string
	**/
	function progress($id) {
		// ID returned by session.upload_progress.name
		return ini_get('session.upload_progress.enabled')?
			$_SESSION[$id]['bytes_processed']:FALSE;
	}

	/**
		Submit HTTP request; Use HTTP context options (described in
		http://www.php.net/manual/en/context.http.php) if specified;
		Cache the page as instructed by remote server
		@return array|FALSE
		@param $url string
		@param $options array
	**/
	function request($url,array $options=NULL) {
		if (!is_array($options))
			$options=array();
		$fw=Base::instance();
		$parts=parse_url($url);
		if (empty($parts['scheme'])) {
			// Local URL
			$url=$fw->get('SCHEME').'://'.
				$fw->get('HOST').
				($url[0]!='/'?($fw->get('BASE')?:'/'):'').$url;
			$parts=parse_url($url);
		}
		elseif (!preg_match('/https?/',$parts['scheme']))
			return FALSE;
		if (isset($options['header']) && is_string($options['header']))
			$options['header']=array($options['header']);
		$options+=array(
			'method'=>'GET',
			'header'=>array(
				'Host: '.$parts['host'],
				'User-Agent: Mozilla/5.0 (compatible; '.php_uname('s').')',
				'Connection: close',
			),
			'follow_location'=>TRUE,
			'max_redirects'=>20,
			'ignore_errors'=>TRUE
		);
		if ($options['method']!='GET')
			$options['header']+=
				array('Content-Type: application/x-www-form-urlencoded');
		$eol="\r\n";
		if ($fw->get('CACHE') &&
			preg_match('/GET|HEAD/',$options['method'])) {
			$cache=Cache::instance();
			if ($cache->exists(
				$hash=$fw->hash($options['method'].' '.$url).'.url',$data)) {
				if (preg_match('/Last-Modified:\s(.+?)'.preg_quote($eol).'/',
					implode($eol,$data['headers']),$mod))
					$options['header']+=array('If-Modified-Since: '.$mod[1]);
			}
		}
		if (extension_loaded('curl')) {
			// Use cURL extension
			$curl=curl_init($url);
			curl_setopt($curl,CURLOPT_FOLLOWLOCATION,
				$options['follow_location']);
			curl_setopt($curl,CURLOPT_MAXREDIRS,
				$options['max_redirects']);
			curl_setopt($curl,CURLOPT_CUSTOMREQUEST,$options['method']);
			if (isset($options['header']))
				curl_setopt($curl,CURLOPT_HTTPHEADER,$options['header']);
			if (isset($options['user_agent']))
				curl_setopt($curl,CURLOPT_USERAGENT,$options['user_agent']);
			if (isset($options['content']))
				curl_setopt($curl,CURLOPT_POSTFIELDS,$options['content']);
			curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,
				isset($options['timeout'])?
					$options['timeout']:
					ini_get('default_socket_timeout'));
			$headers=array();
			curl_setopt($curl,CURLOPT_HEADERFUNCTION,
				function($curl,$line) use(&$headers) {
					if ($trim=trim($line))
						$headers[]=$trim;
					return strlen($line);
				}
			);
			curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
			ob_start();
			$out=curl_exec($curl);
			curl_close($curl);
			$result=array(
				'body'=>ob_get_clean(),
				'headers'=>$headers,
				'engine'=>'cURL',
				'cached'=>FALSE
			);
		}
		elseif ($parts['scheme']=='https' && !extension_loaded('openssl'))
			// short-circuit
			return FALSE;
		elseif (ini_get('allow_url_fopen')) {
			// Use stream wrapper
			$options['header']=implode($eol,$options['header']);
			$out=@file_get_contents($url,FALSE,
				stream_context_create(array('http'=>$options)));
			$result=array(
				'body'=>$out,
				'headers'=>$out?$http_response_header:array(),
				'engine'=>'stream-wrapper',
				'cached'=>FALSE
			);
		}
		else {
			// Use low-level TCP/IP socket
			$headers=array();
			$body='';
			for ($i=0;$i<$options['max_redirects'];$i++) {
				if (isset($parts['user'],$parts['pass']))
					$options['header']+=array(
						'Authorization: Basic '.
							base64_encode($parts['user'].':'.$parts['pass'])
					);
				if (isset($parts['scheme']) && $parts['scheme']=='https') {
					$parts['host']='ssl://'.$parts['host'];
					if (empty($parts['port']))
						$parts['port']=443;
				}
				elseif (empty($parts['port']))
					$parts['port']=80;
				if (empty($parts['path']))
					$parts['path']='/';
				if (empty($parts['query']))
					$parts['query']='';
				$socket=@fsockopen($parts['host'],$parts['port'],$code,$text);
				if (!$socket)
					return FALSE;
				stream_set_blocking($socket,1);
				fputs($socket,$options['method'].' '.$parts['path'].
					($parts['query']?('?'.$parts['query']):'').' '.
					'HTTP/1.1'.$eol
				);
				fputs($socket,
					'Content-Length: '.strlen($parts['query']).$eol.
					'Accept-Encoding: gzip'.$eol
				);
				if (isset($options['header']))
					fputs($socket,implode($eol,$options['header']).$eol);
				if (isset($options['user_agent']))
					fputs($socket,'User-Agent: '.$options['user_agent'].$eol);
				fputs($socket,$eol);
				if (isset($options['content']))
					fputs($socket,$options['content'].$eol.$eol);
				// Get response
				$content='';
				while (!feof($socket) &&
					($info=stream_get_meta_data($socket)) &&
					!$info['timed_out'] && $str=fgets($socket,4096))
					$content.=$str;
				fclose($socket);
				$html=explode($eol.$eol,$content);
				$headers=array_merge($headers,explode($eol,$html[0]));
				$body=$html[1];
				if (preg_match('/Content-Encoding:\s.*?gzip.*?'.
					preg_quote($eol).'/',$html[0]))
					$body=gzinflate(substr($body,10));
				if (!$options['follow_location'] ||
					!preg_match('/Location:\s(.+?)'.preg_quote($eol).'/',
					$html[0],$loc))
					break;
				$url=$loc[1];
				$parts=parse_url($url);
			}
			$result=array(
				'body'=>$body,
				'headers'=>$headers,
				'engine'=>'sockets',
				'cached'=>FALSE
			);
		}
		if (isset($cache)) {
			if (preg_match('/HTTP\/1\.\d 304/',
				implode($eol,$result['headers']))) {
				$result=$cache->get($hash);
				$result['cached']=TRUE;
			}
			elseif (preg_match('/Cache-Control:\smax-age=(.+?)'.
				preg_quote($eol).'/',implode($eol,$result['headers']),$exp))
				$cache->set($hash,$result,$exp[1]);
		}
		return $result;
	}

	/**
		Strip Javascript/CSS files of extraneous whitespaces and comments;
		Return combined output as a minified string
		@return string
		@param $files string|array
		@param $mime string
	**/
	function minify($files,$mime=NULL) {
		$fw=Base::instance();
		if (is_string($files))
			$files=$fw->split($files);
		if (!$mime)
			$mime=$this->mime($files[0]);
		preg_match('/\w+$/',$files[0],$ext);
		if (!is_dir($tmp=$fw->get('TEMP')))
			$fw->mkdir($tmp);
		$dst='';
		foreach ($fw->split($fw->get('UI')) as $dir)
			foreach ($files as $file)
				if (is_file($min=$fw->fixslashes($dir.$file))) {
					if (!is_file($save=($tmp.'/'.
						$fw->hash($fw->get('ROOT').$fw->get('BASE')).'.'.
						$fw->hash($min).'.'.$ext[0])) ||
						filemtime($save)<filemtime($min)) {
						$src=$fw->read($min);
						for ($ptr=0,$len=strlen($src);$ptr<$len;) {
							if ($src[$ptr]=='/') {
								if (substr($src,$ptr+1,2)=='*@') {
									// Conditional block
									$str=strstr(
										substr($src,$ptr+3),'@*/',TRUE);
									$dst.='/*@'.$str.$src[$ptr].'@*/';
									$ptr+=strlen($str)+6;
								}
								elseif ($src[$ptr+1]=='*') {
									// Multiline comment
									$str=strstr(
										substr($src,$ptr+2),'*/',TRUE);
									$ptr+=strlen($str)+4;
								}
								elseif ($src[$ptr+1]=='/') {
									// Single-line comment
									$str=strstr(
										substr($src,$ptr+2),"\n",TRUE);
									$ptr+=strlen($str)+2;
								}
								else {
									// Presume it's a regex pattern
									$regex=TRUE;
									// Backtrack and validate
									$ofs=$ptr;
									while ($ofs) {
										$ofs--;
										// Pattern should be preceded by open
										// parenthesis,colon (object property)
										// or operator
										if (preg_match(
											'/(return|[(:=!+\-*&|])$/',
											substr($src,0,$ofs+1))) {
											$dst.='/';
											$ptr++;
											while ($ptr<$len) {
												$dst.=$src[$ptr];
												$ptr++;
												if ($src[$ptr-1]=='\\') {
													$dst.=$src[$ptr];
													$ptr++;
												}
												elseif ($src[$ptr-1]=='/')
													break;
											}
											break;
										}
										elseif (!ctype_space($src[$ofs])) {
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
								continue;
							}
							if (in_array($src[$ptr],array('\'','"'))) {
								$match=$src[$ptr];
								$dst.=$match;
								$ptr++;
								// String literal
								while ($ptr<$len) {
									$dst.=$src[$ptr];
									$ptr++;
									if ($src[$ptr-1]=='\\') {
										$dst.=$src[$ptr];
										$ptr++;
									}
									elseif ($src[$ptr-1]==$match)
										break;
								}
								continue;
							}
							if (ctype_space($src[$ptr])) {
								if ($ptr+1<strlen($src) &&
									preg_match('/([\w'.($ext[0]=='css'?
										'#\.+\-*()\[\]':'\$').']){2}/',
										substr($dst,-1).$src[$ptr+1]))
									$dst.=' ';
								$ptr++;
								continue;
							}
							$dst.=$src[$ptr];
							$ptr++;
						}
						$fw->write($save,$dst);
					}
					else
						$dst=$fw->read($save);
				}
		if (PHP_SAPI!='cli')
			header('Content-Type: '.$mime.'; charset='.$fw->get('ENCODING'));
		return $dst;
	}

	/**
		Retrieve RSS/Atom feed and return as an array
		@return array|FALSE
		@param $url string
		@param $max int
		@param $tags string
	**/
	function rss($url,$max=10,$tags=NULL) {
		if (!$data=$this->request($url))
			return FALSE;
		// Suppress errors caused by invalid XML structures
		libxml_use_internal_errors(TRUE);
		$xml=simplexml_load_string($data['body'],
			NULL,LIBXML_NOBLANKS|LIBXML_NOERROR);
		if (!is_object($xml))
			return FALSE;
		$out=array();
		if (isset($xml->channel)) {
			$out['source']=(string)$xml->channel->title;
			for ($i=0;$i<$max;$i++) {
				$item=$xml->channel->item[$i];
				$out['feed'][]=array(
					'title'=>(string)$item->title,
					'link'=>(string)$item->link,
					'text'=>(string)$item->description
				);
			}
		}
		elseif (isset($xml->entry)) {
			$out['source']=(string)$xml->author->name;
			for ($i=0;$i<$max;$i++) {
				$item=$xml->entry[$i];
				$out['feed'][]=array(
					'title'=>(string)$item->title,
					'link'=>(string)$item->link['href'],
					'text'=>(string)$item->summary
				);
			}
		}
		else
			return FALSE;
		Base::instance()->scrub($out,$tags);
		return $out;
	}

	/**
		Return a URL/filesystem-friendly version of string
		@return string
		@param $text string
	**/
	function slug($text) {
		return trim(strtolower(preg_replace('/([^\pL\pN])+/u','-',
			trim(strtr(str_replace('\'','',$text),
			Base::instance()->get('DIACRITICS')+
			array(
				'À'=>'A','Á'=>'A','Â'=>'A','Ã'=>'A','Å'=>'A','Ä'=>'A',
				'Æ'=>'AE','à'=>'a','á'=>'a','â'=>'a','ã'=>'a','å'=>'a',
				'ä'=>'a','æ'=>'ae','Þ'=>'B','þ'=>'b','Č'=>'C','Ć'=>'C',
				'Ç'=>'C','č'=>'c','ć'=>'c','ç'=>'c','Ď'=>'D','ð'=>'d',
				'ď'=>'d','Đ'=>'Dj','đ'=>'dj','È'=>'E','É'=>'E','Ê'=>'E',
				'Ë'=>'E','Ě'=>'e','ě'=>'e','è'=>'e','é'=>'e','ê'=>'e',
				'ë'=>'e','Ì'=>'I','Í'=>'I','Î'=>'I','Ï'=>'I','ì'=>'i',
				'í'=>'i','î'=>'i','ï'=>'i','Ľ'=>'L','ľ'=>'l','Ñ'=>'N',
				'Ň'=>'N','ñ'=>'n','ň'=>'n','Ò'=>'O','Ó'=>'O','Ô'=>'O',
				'Õ'=>'O','Ø'=>'O','Ö'=>'O','Œ'=>'OE','ò'=>'o','ó'=>'o',
				'ô'=>'o','õ'=>'o','ö'=>'o','œ'=>'oe','ø'=>'o','Ŕ'=>'R',
				'Ř'=>'R','ŕ'=>'r','ř'=>'r','Š'=>'S','š'=>'s','ß'=>'ss',
				'Ť'=>'T','ť'=>'t','Ù'=>'U','Ú'=>'U','Û'=>'U','Ü'=>'U',
				'Ů'=>'U','ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u','ů'=>'u',
				'Ý'=>'Y','Ÿ'=>'Y','ý'=>'y','ÿ'=>'y','Ž'=>'Z','ž'=>'z'
			))))),'-');
	}

}
