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

//! SMTP plug-in
class SMTP extends Magic {

	//@{ Locale-specific error/exception messages
	const
		E_Header='%s: header is required',
		E_Blank='Message must not be blank',
		E_Attach='Attachment %s not found';
	//@}

	protected
		//! Message properties
		$headers,
		//! E-mail attachments
		$attachments,
		//! SMTP host
		$host,
		//! SMTP port
		$port,
		//! TLS/SSL
		$scheme,
		//! User ID
		$user,
		//! Password
		$pw,
		//! TLS/SSL stream context
		$context,
		//! TCP/IP socket
		$socket,
		//! Server-client conversation
		$log;

	/**
	*	Fix header
	*	@return string
	*	@param $key string
	**/
	protected function fixheader($key) {
		return str_replace(' ','-',
			ucwords(preg_replace('/[_-]/',' ',strtolower($key))));
	}

	/**
	*	Return TRUE if header exists
	*	@return bool
	*	@param $key
	**/
	function exists($key) {
		$key=$this->fixheader($key);
		return isset($this->headers[$key]);
	}

	/**
	*	Bind value to e-mail header
	*	@return string
	*	@param $key string
	*	@param $val string
	**/
	function set($key,$val) {
		$key=$this->fixheader($key);
		return $this->headers[$key]=$val;
	}

	/**
	*	Return value of e-mail header
	*	@return string|NULL
	*	@param $key string
	**/
	function &get($key) {
		$key=$this->fixheader($key);
		if (isset($this->headers[$key]))
			$val=&$this->headers[$key];
		else
			$val=NULL;
		return $val;
	}

	/**
	*	Remove header
	*	@return NULL
	*	@param $key string
	**/
	function clear($key) {
		$key=$this->fixheader($key);
		unset($this->headers[$key]);
	}

	/**
	*	Return client-server conversation history
	*	@return string
	**/
	function log() {
		return str_replace("\n",PHP_EOL,$this->log);
	}

	/**
	*	Send SMTP command and record server response
	*	@return string
	*	@param $cmd string
	*	@param $log bool|string
	*	@param $mock bool
	**/
	protected function dialog($cmd=NULL,$log=TRUE,$mock=FALSE) {
		$reply='';
		if ($mock) {
			$host=str_replace('ssl://','',$this->host);
			switch ($cmd) {
			case NULL:
				$reply='220 '.$host.' ESMTP ready'."\n";
				break;
			case 'DATA':
				$reply='354 Go ahead'."\n";
				break;
			case 'QUIT':
				$reply='221 '.$host.' closing connection'."\n";
				break;
			default:
				$reply='250 OK'."\n";
				break;
			}
		}
		else {
			$socket=&$this->socket;
			if ($cmd)
				fputs($socket,$cmd."\r\n");
			while (!feof($socket) && ($info=stream_get_meta_data($socket)) &&
				!$info['timed_out'] && $str=fgets($socket,4096)) {
				$reply.=$str;
				if (preg_match('/(?:^|\n)\d{3} .+?\r\n/s',$reply))
					break;
			}
		}
		if ($log) {
			if ($cmd)
				$this->log.=$cmd."\n";
			$this->log.=str_replace("\r",'',$reply);
		}
		return $reply;
	}

	/**
	*	Add e-mail attachment
	*	@return NULL
	*	@param $file string
	*	@param $alias string
	*	@param $cid string
	**/
	function attach($file,$alias=NULL,$cid=NULL) {
		if (!is_file($file))
			user_error(sprintf(self::E_Attach,$file),E_USER_ERROR);
		if ($alias)
			$file=[$alias,$file];
		$this->attachments[]=['filename'=>$file,'cid'=>$cid];
	}

	/**
	*	Transmit message
	*	@return bool
	*	@param $message string
	*	@param $log bool|string
	*	@param $mock bool
	**/
	function send($message,$log=TRUE,$mock=FALSE) {
		if ($this->scheme=='ssl' && !extension_loaded('openssl'))
			return FALSE;
		// Message should not be blank
		if (!$message)
			user_error(self::E_Blank,E_USER_ERROR);
		$fw=Base::instance();
		// Retrieve headers
		$headers=$this->headers;
		// Connect to the server
		if (!$mock) {
			$socket=&$this->socket;
			$socket=@stream_socket_client($this->host.':'.$this->port,
				$errno,$errstr,ini_get('default_socket_timeout'),
				STREAM_CLIENT_CONNECT,$this->context);
			if (!$socket) {
				$fw->error(500,$errstr);
				return FALSE;
			}
			stream_set_blocking($socket,TRUE);
		}
		// Get server's initial response
		$this->dialog(NULL,TRUE,$mock);
		// Announce presence
		$reply=$this->dialog('EHLO '.$fw->HOST,$log,$mock);
		if (strtolower($this->scheme)=='tls') {
			$this->dialog('STARTTLS',$log,$mock);
			if (!$mock)
				stream_socket_enable_crypto(
					$socket,TRUE,STREAM_CRYPTO_METHOD_TLS_CLIENT);
			$reply=$this->dialog('EHLO '.$fw->HOST,$log,$mock);
		}
		$message=wordwrap($message,998);
		if (preg_match('/8BITMIME/',$reply))
			$headers['Content-Transfer-Encoding']='8bit';
		else {
			$headers['Content-Transfer-Encoding']='quoted-printable';
			$message=preg_replace('/^\.(.+)/m',
				'..$1',quoted_printable_encode($message));
		}
		if ($this->user && $this->pw && preg_match('/AUTH/',$reply)) {
			// Authenticate
			$this->dialog('AUTH LOGIN',$log,$mock);
			$this->dialog(base64_encode($this->user),$log,$mock);
			$reply=$this->dialog(base64_encode($this->pw),$log,$mock);
			if (!preg_match('/^235\s.*/',$reply)) {
				$this->dialog('QUIT',$log,$mock);
				if (!$mock && $socket)
					fclose($socket);
				return FALSE;
			}
		}
		if (empty($headers['Message-Id']))
			$headers['Message-Id']='<'.uniqid('',TRUE).'@'.$this->host.'>';
		if (empty($headers['Date']))
			$headers['Date']=date('r');
		// Required headers
		$reqd=['From','To','Subject'];
		foreach ($reqd as $id)
			if (empty($headers[$id]))
				user_error(sprintf(self::E_Header,$id),E_USER_ERROR);
		$eol="\r\n";
		// Stringify headers
		foreach ($headers as $key=>&$val) {
			if (in_array($key,['From','To','Cc','Bcc'])) {
				$email='';
				preg_match_all('/(?:".+?" )?(?:<.+?>|[^ ,]+)/',
					$val,$matches,PREG_SET_ORDER);
				foreach ($matches as $raw)
					$email.=($email?', ':'').
						(preg_match('/<.+?>/',$raw[0])?
							$raw[0]:
							('<'.$raw[0].'>'));
				$val=$email;
			}
			unset($val);
		}
		// Start message dialog
		$this->dialog('MAIL FROM: '.strstr($headers['From'],'<'),$log,$mock);
		foreach ($fw->split($headers['To'].
			(isset($headers['Cc'])?(';'.$headers['Cc']):'').
			(isset($headers['Bcc'])?(';'.$headers['Bcc']):'')) as $dst) {
			$this->dialog('RCPT TO: '.strstr($dst,'<'),$log,$mock);
		}
		$this->dialog('DATA',$log,$mock);
		if ($this->attachments) {
			// Replace Content-Type
			$type=$headers['Content-Type'];
			unset($headers['Content-Type']);
			$enc=$headers['Content-Transfer-Encoding'];
			unset($headers['Content-Transfer-Encoding']);
			$hash=uniqid(NULL,TRUE);
			// Send mail headers
			$out='Content-Type: multipart/mixed; boundary="'.$hash.'"'.$eol;
			foreach ($headers as $key=>$val)
				if ($key!='Bcc')
					$out.=$key.': '.$val.$eol;
			$out.=$eol;
			$out.='This is a multi-part message in MIME format'.$eol;
			$out.=$eol;
			$out.='--'.$hash.$eol;
			$out.='Content-Type: '.$type.$eol;
			$out.='Content-Transfer-Encoding: '.$enc.$eol;
			$out.=$eol;
			$out.=$message.$eol;
			foreach ($this->attachments as $attachment) {
				if (is_array($attachment['filename']))
					list($alias,$file)=$attachment['filename'];
				else
					$alias=basename($file=$attachment['filename']);
				$out.='--'.$hash.$eol;
				$out.='Content-Type: application/octet-stream'.$eol;
				$out.='Content-Transfer-Encoding: base64'.$eol;
				if ($attachment['cid'])
					$out.='Content-Id: '.$attachment['cid'].$eol;
				$out.='Content-Disposition: attachment; '.
					'filename="'.$alias.'"'.$eol;
				$out.=$eol;
				$out.=chunk_split(base64_encode(
					file_get_contents($file))).$eol;
			}
			$out.=$eol;
			$out.='--'.$hash.'--'.$eol;
			$out.='.';
			$this->dialog($out,preg_match('/verbose/i',$log),$mock);
		}
		else {
			// Send mail headers
			$out='';
			foreach ($headers as $key=>$val)
				if ($key!='Bcc')
					$out.=$key.': '.$val.$eol;
			$out.=$eol;
			$out.=$message.$eol;
			$out.='.';
			// Send message
			$this->dialog($out,preg_match('/verbose/i',$log),$mock);
		}
		$this->dialog('QUIT',$log,$mock);
		if (!$mock && $socket)
			fclose($socket);
		return TRUE;
	}

	/**
	*	Instantiate class
	*	@param $host string
	*	@param $port int
	*	@param $scheme string
	*	@param $user string
	*	@param $pw string
	*	@param $ctx resource
	**/
	function __construct(
		$host='localhost',$port=25,$scheme=NULL,$user=NULL,$pw=NULL,$ctx=NULL) {
		$this->headers=[
			'MIME-Version'=>'1.0',
			'Content-Type'=>'text/plain; '.
				'charset='.Base::instance()->ENCODING
		];
		$this->host=strtolower((($this->scheme=strtolower($scheme))=='ssl'?
			'ssl':'tcp').'://'.$host);
		$this->port=$port;
		$this->user=$user;
		$this->pw=$pw;
		$this->context=stream_context_create($ctx);
	}

}
