<?php

/**
	SMTP plugin for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2012 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package SMTP
		@version 2.0.10
**/

//! SMTP plugin
class SMTP extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_MailHeader='%s: header is required',
		TEXT_MailBlank='Message must not be blank',
		TEXT_MailAttach='Attachment %s not found';
	//@}

	const
		//! Carriage return/line feed sequence
		EOL="\r\n";

	//@{ SMTP headers
	const
		SMTP_Content='Content-Type',
		SMTP_Disposition='Content-Disposition',
		SMTP_Encoding='Content-Transfer-Encoding',
		SMTP_MIME='MIME-Type';
	//@}

	const
		// Notice to mail clients
		SMTP_Notice='This is a multi-part message in MIME format';

	private
		//! Message properties
		$headers,
		//! Connection parameters
		$socket,$server,$port,$enc,
		//! E-mail attachments
		$attachments;

	public
		//! Server-client conversation
		$log;

	/**
		Fix header
			@param $key
			@private
	**/
	private function fixheader($key) {
		return str_replace(' ','-',
			ucwords(str_replace('-',' ',self::resolve($key))));
	}

	/**
		Add e-mail attachment
			@param $file
			@public
	**/
	function attach($file) {
		if (!is_file($file)) {
			trigger_error(sprintf(self::TEXT_MailAttach,$file));
			return;
		}
		$this->attachments[]=$file;
	}

	/**
		Bind value to e-mail header
			@param $key string
			@param $val string
			@public
	**/
	function set($key,$val) {
		$key=$this->fixheader($key);
		$this->headers[$key]=self::resolve($val);
	}

	/**
		Return value of e-mail header
			@param $key string
			@public
	**/
	function get($key) {
		$key=$this->fixheader($key);
		return isset($this->headers[$key])?$this->headers[$key]:NULL;
	}

	/**
		Remove header
			@param $key
			@public
	**/
	function clear($key) {
		$key=$this->fixheader($key);
		unset($this->headers[$key]);
	}

	/**
		Send SMTP command and record server response
			@param $cmd string
			@param $log boolean
			@public
	**/
	function dialog($cmd=NULL,$log=TRUE) {
		$socket=&$this->socket;
		fputs($socket,$cmd.self::EOL);
		if ($log) {
			$reply='';
			while ($str=fgets($socket,512)) {
				$reply.=$str;
				if (preg_match('/\d{3}\s/',$str))
					break;
			}
			$this->log.=$cmd."\n";
			$this->log.=$reply;
		}
		else
			$this->log.=$cmd."\n";
	}

	/**
		Transmit message
			@param $message string
			@public
	**/
	function send($message) {
		// Required headers
		$reqd=array('From','To','Subject');
		// Retrieve headers
		$headers=$this->headers;
		foreach ($reqd as $id)
			if (!isset($headers[$id])) {
				trigger_error(sprintf(self::TEXT_MailHeader,$id));
				return;
			}
		// Message should not be blank
		$message=self::resolve($message);
		if (!$message) {
			trigger_error(self::TEXT_MailBlank);
			return;
		}
		$str='';
		// Stringify headers
		foreach ($headers as $key=>$val)
			if (!in_array($key,$reqd))
				$str.=$key.': '.$val."\r\n";
		// Start message dialog
		$this->dialog('MAIL FROM: '.strstr($headers['From'],'<'));
		$this->dialog('RCPT TO: '.$headers['To']);
		$this->dialog('DATA');
		if ($this->attachments) {
			// Replace Content-Type
			$hash=self::hash(mt_rand());
			$type=$headers[self::SMTP_Content];
			$headers[self::SMTP_Content]='multipart/mixed; '.
				'boundary="'.$hash.'"';
			// Send mail headers
			foreach ($headers as $key=>$val)
				$this->dialog($key.': '.$val,FALSE);
			$this->dialog(NULL,FALSE);
			$this->dialog(self::SMTP_Notice,FALSE);
			$this->dialog(NULL,FALSE);
			$this->dialog('--'.$hash,FALSE);
			$this->dialog(self::SMTP_Content.': '.$type,FALSE);
			$this->dialog(NULL,FALSE);
			$this->dialog($message,FALSE);
			$this->dialog('--'.$hash,FALSE);
			foreach ($this->attachments as $attachment) {
				$this->dialog(self::SMTP_Content.': '.
					'application/octet-stream',FALSE);
				$this->dialog(self::SMTP_Encoding.': base64',FALSE);
				$this->dialog(self::SMTP_Disposition.': '.
					'attachment; filename="'.basename($attachment).'"',FALSE);
				$this->dialog(NULL,FALSE);
				$this->dialog(chunk_split(base64_encode(
					self::getfile($attachment))),FALSE);
			}
			$this->dialog('--'.$hash,FALSE);
		}
		else {
			// Send mail headers
			foreach ($headers as $key=>$val)
				$this->dialog($key.': '.$val,FALSE);
			$this->dialog(NULL,FALSE);
			// Send message
			$this->dialog($message,FALSE);
		}
		$this->dialog('.');
	}

	/**
		Class constructor
			@param $server string
			@param $port int
			@param $enc string
			@param $user string
			@param $pw string
			@public
	**/
	function __construct(
		$server='localhost',$port=25,$enc=NULL,$user=NULL,$pw=NULL) {
		$this->headers=array(
			self::SMTP_MIME=>'1.0',
			self::SMTP_Content=>'text/plain; charset='.self::ref('ENCODING'),
			self::SMTP_Encoding=>'8bit'
		);
		if ($enc && $enc!='TLS')
			$server=strtolower($enc).'://'.$server;
		$this->server=$server;
		$this->port=$port;
		$this->enc=$enc;
		// Connect to the server
		$socket=&$this->socket;
		$socket=@fsockopen($server,$port,$errno,$errstr);
		if (!$socket) {
			trigger_error($errstr);
			return;
		}
		stream_set_blocking($socket,TRUE);
		stream_set_timeout($socket,ini_get('default_socket_timeout'));
		// Get server's initial response
		$this->log=fgets($socket,512);
		// Indicate presence
		$this->dialog('EHLO '.$_SERVER['SERVER_NAME']);
		if ($enc=='TLS') {
			$this->dialog('STARTTLS');
			stream_socket_enable_crypto(
				$socket,TRUE,STREAM_CRYPTO_METHOD_TLS_CLIENT);
			$this->dialog('EHLO '.$_SERVER['SERVER_NAME']);
		}
		if ($user) {
			// Authenticate
			$this->dialog('AUTH LOGIN');
			$this->dialog(base64_encode($user));
			$this->dialog(base64_encode($pw));
		}
	}

	/**
		Free up resources
			@public
	**/
	function __destruct() {
		$this->dialog('QUIT');
		fclose($this->socket);
	}

}
