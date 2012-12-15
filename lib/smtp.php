<?php

//! SMTP plugin
class SMTP extends Magic {

	//@{ Locale-specific error/exception messages
	const
		E_Header='%s: header is required',
		E_Blank='Message must not be blank',
		E_Attach='Attachment %s not found';
	//@}

	private
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
		//! TCP/IP socket
		$socket,
		//! Server-client conversation
		$log;

	/**
		Fix header
		@return string
		@param $key string
	**/
	protected function fixheader($key) {
		return str_replace(' ','-',
			ucwords(preg_replace('/_-/',' ',strtolower($key))));
	}

	/**
		Return TRUE if header exists
		@return bool
		@param $key
	**/
	function exists($key) {
		$key=$this->fixheader($key);
		return isset($this->headers[$key]);
	}

	/**
		Bind value to e-mail header
		@return string
		@param $key string
		@param $val string
	**/
	function set($key,$val) {
		$key=$this->fixheader($key);
		return $this->headers[$key]=$val;
	}

	/**
		Return value of e-mail header
		@return string|NULL
		@param $key string
	**/
	function get($key) {
		$key=$this->fixheader($key);
		return isset($this->headers[$key])?$this->headers[$key]:NULL;
	}

	/**
		Remove header
		@return NULL
		@param $key string
	**/
	function clear($key) {
		$key=$this->fixheader($key);
		unset($this->headers[$key]);
	}

	/**
		Return client-server conversation history
		@return string
	**/
	function log() {
		return $this->log;
	}

	/**
		Send SMTP command and record server response
		@return NULL
		@param $cmd string
		@param $log bool
	**/
	protected function dialog($cmd=NULL,$log=FALSE) {
		$socket=&$this->socket;
		fputs($socket,$cmd."\r\n");
		$reply='';
		while (!feof($socket) && ($info=stream_get_meta_data($socket)) &&
			!$info['timed_out'] && $str=fgets($socket,4096)) {
			$reply.=$str;
			if (preg_match('/(?:^|\n)\d{3}\s.+?\r\n/s',$reply))
				break;
		}
		if ($log) {
			$this->log.=$cmd."\n";
			$this->log.=str_replace("\r",'',$reply);
		}
	}

	/**
		Add e-mail attachment
		@return NULL
		@param $file
	**/
	function attach($file) {
		if (!is_file($file))
			user_error(sprintf(self::E_Attach,$file));
		$this->attachments[]=$file;
	}

	/**
		Transmit message
		@return bool
		@param $message string
	**/
	function send($message) {
		if ($this->scheme=='ssl' && !extension_loaded('openssl'))
			return FALSE;
		$fw=Base::instance();
		// Connect to the server
		$socket=&$this->socket;
		$socket=@fsockopen($this->host,$this->port,$code,$text);
		if (!$socket) {
			user_error($text);
			return;
		}
		stream_set_blocking($socket,TRUE);
		stream_set_timeout($socket,ini_get('default_socket_timeout'));
		// Get server's initial response
		$this->log=str_replace("\r",'',fgets($socket,4096));
		// Indicate presence
		$this->dialog('EHLO '.$_SERVER['SERVER_NAME'],TRUE);
		if (strtoupper($this->scheme)=='tls') {
			$this->dialog('STARTTLS',TRUE);
			stream_socket_enable_crypto(
				$socket,TRUE,STREAM_CRYPTO_METHOD_TLS_CLIENT);
			$this->dialog('EHLO '.$_SERVER['SERVER_NAME'],TRUE);
		}
		if ($this->user && $this->pw) {
			// Authenticate
			$this->dialog('AUTH LOGIN',TRUE);
			$this->dialog(base64_encode($this->user),TRUE);
			$this->dialog(base64_encode($this->pw),TRUE);
		}
		// Required headers
		$reqd=array('From','To','Subject');
		// Retrieve headers
		$headers=$this->headers;
		foreach ($reqd as $id)
			if (empty($headers[$id]))
				user_error(sprintf(self::E_Header,$id));
		// Message should not be blank
		if (!$message)
			user_error(self::E_Blank);
		$eol="\r\n";
		$str='';
		// Stringify headers
		foreach ($headers as $key=>$val)
			if (!in_array($key,$reqd))
				$str.=$key.': '.$val.$eol;
		// Start message dialog
		$this->dialog('MAIL FROM: '.strstr($headers['From'],'<'),TRUE);
		foreach ($fw->split($headers['To'].
			(isset($headers['Cc'])?(';'.$headers['Cc']):'').
			(isset($headers['Bcc'])?(';'.$headers['Bcc']):'')) as $dst)
			$this->dialog('RCPT TO: '.strstr($dst,'<'),TRUE);
		$this->dialog('DATA',TRUE);
		if ($this->attachments) {
			// Replace Content-Type
			$hash=$fw->hash(mt_rand());
			$type=$headers['Content-Type'];
			$headers['Content-Type']='multipart/mixed; '.
				'boundary="'.$hash.'"';
			// Send mail headers
			$out='';
			foreach ($headers as $key=>$val)
				if ($key!='Bcc')
					$out.=$key.': '.$val.$eol;
			$out.=$eol;
			$out.='This is a multi-part message in MIME format'.$eol;
			$out.=$eol;
			$out.='--'.$hash.$eol;
			$out.='Content-Type: '.$type.$eol;
			$out.=$eol;
			$out.=$message.$eol;
			foreach ($this->attachments as $attachment) {
				$out.='--'.$hash.$eol;
				$out.='Content-Type: application/octet-stream'.$eol;
				$out.='Content-Transfer-Encoding: base64'.$eol;
				$out.='Content-Disposition: attachment; '.
					'filename="'.basename($attachment).'"'.$eol;
				$out.=$eol;
				$out.=chunk_split(
					base64_encode($fw->read($attachment))).$eol;
			}
			$out.=$eol;
			$out.='--'.$hash.'--'.$eol;
			$out.='.';
			$this->dialog($out,TRUE);
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
			$this->dialog($out,TRUE);
		}
		$this->dialog('QUIT',TRUE);
		if ($socket)
			fclose($socket);
		return TRUE;
	}

	/**
		Instantiate class
		@param $host string
		@param $port int
		@param $scheme string
		@param $user string
		@param $pw string
	**/
	function __construct($host,$port,$scheme,$user,$pw) {
		$this->headers=array(
			'MIME-Version'=>'1.0',
			'Content-Type'=>'text/plain; '.
				'charset='.Base::instance()->get('ENCODING'),
			'Content-Transfer-Encoding'=>'8bit'
		);
		$this->host=$host;
		if (strtoupper($this->scheme=strtolower($scheme))=='ssl')
			$this->host='ssl://'.$host;
		$this->port=$port;
		$this->user=$user;
		$this->pw=$pw;
	}

}
