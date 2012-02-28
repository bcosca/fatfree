<?php

/**
	ZIP archive utility for the Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Zip
		@version 2.0.9
**/

//! Utility class for ZIP archives
class Zip extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_Required='A ZIP archive must be specified',
		TEXT_NotValid='File %s is not a valid ZIP archive',
		TEXT_UnMethod='Unsupported compression method';
	//@}

	//@{ ZIP header signatures
	const
		LFHDR_Sig='504B0304',
		CDHDR_Sig='504B0102',
		CDEND_Sig='504B0506';
	//@}

	const
		//! Read-granularity of ZIP archive
		BLOCK_Size=4096;

	private
		//! ZIP file name
		$file,
		//! Central directory container
		$cdir,
		//! Central directory relative offset
		$cofs;

	/**
		Return central directory structure
			@return array
			@public
	**/
	function dir() {
		return $this->cdir;
	}

	/**
		Return content of specified file from ZIP archive; FALSE if
		compression method is not supported
			@return mixed
			@param $path string
			@public
	**/
	function get($path) {
		if (!$path || $path[strlen($path)-1]=='/')
			return FALSE;
		$chdr=$this->cdir[$path];
		// Find local file header
		$zip=fopen($this->file,'rb');
		fseek($zip,implode('',unpack('V',substr($chdr,42,4))));
		// Read local file header
		$fhdr=fread($zip,30+strlen($path));
		$comp=self::binhex(substr($fhdr,8,2));
		if ($comp!='0800' && $comp!='0000') {
			trigger_error(self::TEXT_UnMethod);
			return FALSE;
		}
		if ($len=implode(unpack('v',substr($fhdr,28,2))))
			// Append extra field
			$fhdr.=fread($zip,$len);
		$len=unpack('V',substr($fhdr,22,4));
		$data='';
		if ($len)
			$data=fread($zip,implode('',$len));
		fclose($zip);
		return hexdec($comp) && $data?gzinflate($data):$data;

	}

	/**
		Add or replace file in ZIP archive using specified content;
		Create folder if content is NULL or unspecified
			@param $path string
			@param $data string
			@param $time integer
			@public
	**/
	function set($path,$data=NULL,$time=0) {
		$this->parse('set',$path,$data,$time);
	}

	/**
		Delete file from ZIP archive
			@param $path string
			@public
	**/
	function clear($path) {
		$this->parse('clear',$path);
	}

	/**
		Parse ZIP archive
			@param $path string
			@param $func mixed
			@public
	**/
	private function parse($action,$path,$data=NULL,$time=0) {
		if (!$time)
			$time=time();
		$tfn=self::$vars['TEMP'].$_SERVER['SERVER_NAME'].'.zip.'.
			self::hash($path);
		$tmp=fopen($tfn,'wb+');
		if (is_file($this->file)) {
			$zip=fopen($this->file,'rb');
			// Copy data from ZIP archive to temporary file
			foreach ($this->cdir as $name=>$chdr)
				if ($name!=$path) {
					// Find local file header
					fseek($zip,implode('',
						unpack('V',substr($chdr,42,4))));
					$fhdr=fread($zip,30+strlen($name));
					$len=implode(unpack('v',substr($fhdr,28,2)));
					if ($len)
						// Append extra field
						$fhdr.=fread($zip,$len);
					// Update relative offset
					$this->cdir[$name]=substr_replace(
						$this->cdir[$name],pack('V',ftell($tmp)),42,4);
					// Copy header and compressed content
					$len=implode('',unpack('V',substr($fhdr,18,4)));
					fwrite($tmp,$fhdr.($len?fread($zip,$len):''));
				}
			fclose($zip);
		}
		switch ($action) {
			case 'set':
				$path=self::fixslashes($path).
					(is_null($data) && $path[strlen($path)]!='/'?'/':'');
				$chdr=&$this->cdir[$path];
				// Blank headers
				$fhdr=str_repeat(chr(0),30).$path;
				$chdr=str_repeat(chr(0),46).$path;
				// Signatures
				$fhdr=substr_replace(
					$fhdr,self::hexbin(self::LFHDR_Sig),0,4);
				$chdr=substr_replace(
					$chdr,self::hexbin(self::CDHDR_Sig),0,4);
				// Version needed to extract
				$ver=self::hexbin(is_null($data)?'0A00':'1400');
				$fhdr=substr_replace($fhdr,$ver,4,2);
				$chdr=substr_replace($chdr,$ver,6,2);
				// Last modification time
				$mod=pack('V',self::unix2dostime($time));
				$fhdr=substr_replace($fhdr,$mod,10,4);
				$chdr=substr_replace($chdr,$mod,12,4);
				// File name length
				$len=pack('v',strlen($path));
				$fhdr=substr_replace($fhdr,$len,26,2);
				$chdr=substr_replace($chdr,$len,28,2);
				// File header relative offset
				$chdr=substr_replace(
					$chdr,pack('V',ftell($tmp)),42,4);
				if (!is_null($data)) {
					// Compress data/Fix CRC bug
					$comp=gzdeflate($data);
					// Compression method
					$def=self::hexbin('0800');
					$fhdr=substr_replace($fhdr,$def,8,2);
					$chdr=substr_replace($chdr,$def,10,2);
					// CRC32
					$crc=pack('V',crc32($data));
					$fhdr=substr_replace($fhdr,$crc,14,4);
					$chdr=substr_replace($chdr,$crc,16,4);
					// Compressed size
					$size=pack('V',strlen($comp));
					$fhdr=substr_replace($fhdr,$size,18,4);
					$chdr=substr_replace($chdr,$size,20,4);
					// Uncompressed size
					$size=pack('V',strlen($data));
					$fhdr=substr_replace($fhdr,$size,22,4);
					$chdr=substr_replace($chdr,$size,24,4);
					// Copy header and compressed content
					fwrite($tmp,$fhdr.$comp);
				}
				break;
			case 'clear':
				$path=self::fixslashes($path);
				unset($this->cdir[$path]);
				break;
		}
		// Central directory relative offset
		$this->cofs=ftell($tmp);
		foreach ($this->cdir as $raw)
			// Copy central directory file headers
			fwrite($tmp,$raw);
		// Blank end of central directory record
		$cend=str_repeat(chr(0),22);
		// Signature
		$cend=substr_replace($cend,self::hexbin(self::CDEND_Sig),0,4);
		// Total number of central directory records
		$total=pack('v',count($this->cdir));
		$cend=substr_replace($cend,$total,8,2);
		$cend=substr_replace($cend,$total,10,2);
		// Size of central directory
		$cend=substr_replace(
			$cend,pack('V',strlen(implode('',$this->cdir))),12,4);
		// Relative offset of central directory
		$cend=substr_replace($cend,pack('V',$this->cofs),16,4);
		fwrite($tmp,$cend);
		fclose($tmp);
		if (is_file($this->file))
			// Delete old ZIP archive
			unlink($this->file);
		rename($tfn,$this->file);
	}

	/**
		Convert 4-byte DOS time to Un*x timestamp
			@return integer
			@param $time integer
			@public
	**/
	static function dos2unixtime($time) {
		$date=$time>>16;
		return mktime(
			($time & 0xF800)>>11,
			($time & 0x07E0)>>5,
			($time & 0x001F)<<1,
			($date & 0x01E0)>>5,
			($date & 0x001F),
			(($date & 0xFE00)>>9)+1980
		);
	}

	/**
		Convert Un*x timestamp to 4-byte DOS time
			@return integer
			@param $time integer
			@public
	**/
	static function unix2dostime($time=0) {
		$time=$time?getdate($time):getdate();
		if ($time['year']<1980)
			$time=array_combine(
				array('hours','minutes','seconds','mon','mday','year'),
				array(0,0,0,1,1,1980)
			);
		return
			($time['hours']<<11) |
			($time['minutes']<<5) |
			($time['seconds']>>1) |
			($time['mon']<<21) |
			($time['mday']<<16) |
			(($time['year']-1980)<<25);
	}

	/**
		Class constructor
			@param $path string
			@public
	**/
	function __construct($path=NULL) {
		if (is_null($path)) {
			trigger_error(self::TEXT_Required);
			return;
		}
		$path=self::resolve($path);
		$this->file=$path;
		$this->cdir=array();
		if (!is_file($path))
			return;
		// Parse file contents
		$zip=fopen($path,'rb');
		$found=FALSE;
		$cdir='';
		while (!feof($zip)) {
			$cdir.=fread($zip,self::BLOCK_Size);
			if (is_bool($found)) {
				$found=strstr($cdir,self::hexbin(self::CDHDR_Sig));
				if (is_string($found)) {
					// Start of central directory record
					$cdir=$found;
					$this->cofs=ftell($zip)-strlen($found);
				}
				elseif (strlen($cdir)>self::BLOCK_Size)
					// Conserve memory
					$cdir=substr($cdir,self::BLOCK_Size);
			}
		}
		fclose($zip);
		if (is_bool(strstr($cdir,self::hexbin(self::CDEND_Sig)))) {
			// Invalid ZIP archive
			trigger_error(sprintf(self::TEXT_NotValid,$path));
			return;
		}
		// Save central directory record
		foreach (array_slice(explode(self::hexbin(self::CDHDR_Sig),
			strstr($cdir,self::hexbin(self::CDEND_Sig),TRUE)),1)
			as $raw)
				// Extract name and use as array key
				$this->cdir[substr(
					$raw,42,implode('',unpack('v',substr($raw,24,2)))
				)]=self::hexbin(self::CDHDR_Sig).$raw;
	}

}
