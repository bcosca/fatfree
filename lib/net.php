<?php

/**
	Network utilities for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2012 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Network
		@version 2.0.10
**/

//! Network utilities
class Net extends Base {

	/**
		Send ICMP echo request to specified host; Return array containing
		minimum/average/maximum round-trip time (in millisecs) and number of
		packets received, or FALSE if host is unreachable
			@return mixed
			@param $addr string
			@param $dns boolean
			@param $count integer
			@param $wait integer
			@param $ttl integer
			@public
	**/
	static function ping($addr,$dns=FALSE,$count=3,$wait=3,$ttl=30) {
		// ICMP transmit socket
		$tsocket=socket_create(AF_INET,SOCK_RAW,1);
		// Set TTL
		socket_set_option($tsocket,0,PHP_OS!='Linux'?4:2,$ttl);
		// ICMP receive socket
		$rsocket=socket_create(AF_INET,SOCK_RAW,1);
		// Bind to all network interfaces
		socket_bind($rsocket,0,0);
		// Initialize counters
		list($rtt,$rcv,$min,$max)=array(0,0,0,0);
		for ($i=0;$i<$count;$i++) {
			// Send ICMP header and payload
			$data=uniqid();
			$payload=self::hexbin('0800000000000000').$data;
			// Recalculate ICMP checksum
			if (strlen($payload)%2)
				$payload.=self::hexbin('00');
			$bits=unpack('n*',$payload);
			$sum=array_sum($bits);
			while ($sum>>16)
				$sum=($sum>>16)+($sum&0xFFFF);
			$payload=self::hexbin('0800').pack('n*',~$sum).
				self::hexbin('00000000').$data;
			// Transmit ICMP packet
			@socket_sendto($tsocket,$payload,strlen($payload),0,$addr,0);
			// Start timer
			$time=microtime(TRUE);
			$rset=array($rsocket);
			$tset=NULL;
			$xset=NULL;
			// Wait for incoming ICMP packet
			socket_select($rset,$tset,$xset,$wait);
			if ($rset &&
				@socket_recvfrom($rsocket,$reply,255,0,$host,$port)) {
				$elapsed=1e3*(microtime(TRUE)-$time);
				// Socket didn't timeout; Record round-trip time
				$rtt+=$elapsed;
				if ($elapsed>$max)
					$max=$elapsed;
				if (!($min>0) || $elapsed<$min)
					$min=$elapsed;
				// Count packets received
				$rcv++;
				if ($host)
					$addr=$host;
			}
		}
		socket_close($tsocket);
		socket_close($rsocket);
		return $rcv?
			array(
				'host'=>$dns?gethostbyaddr($addr):$addr,
				'min'=>(int)round($min),
				'max'=>(int)round($max),
				'avg'=>(int)round($rtt/$rcv),
				'packets'=>$rcv
			):
			FALSE;
	}

	/**
		Return the path taken by packets to a specified network destination
			@return array
			@param $addr string
			@param $dns boolean
			@param $wait integer
			@param $hops integer
			@public
	**/
	static function traceroute($addr,$dns=FALSE,$wait=3,$hops=30) {
		$route=array();
		for ($i=0;$i<$hops;$i++) {
			set_time_limit(ini_get('default_socket_timeout'));
			$result=self::ping($addr,$dns,3,$wait,$i+1);
			$route[]=$result;
			if (gethostbyname($result['host'])==gethostbyname($addr))
				break;
		}
		return $route;
	}

	/**
		Retrieve information from whois server
			@return string
			@param $addr
			@public
	**/
	static function whois($addr) {
		$socket=@fsockopen(self::$vars['WHOIS'],43,$errno,$errstr);
		if (!$socket) {
			// Can't establish connection
			trigger_error($errstr);
			return FALSE;
		}
		// Set connection timeout parameters
		stream_set_blocking($socket,TRUE);
		stream_set_timeout($socket,ini_get('default_socket_timeout'));
		// Send request
		fputs($socket,$addr."\r\n");
		$info=stream_get_meta_data($socket);
		// Get response
		$response='';
		while (!feof($socket) && !$info['timed_out']) {
			$response.=fgets($socket,4096); // MDFK97
			$info=stream_get_meta_data($socket);
		}
		fclose($socket);
		if ($info['timed_out']) {
			trigger_error(self::TEXT_Timeout);
			return FALSE;
		}
		return $response;
	}

	/**
		Class initializer
			@public
	**/
	static function onload() {
		if (!extension_loaded('sockets'))
			// Sockets extension required
			trigger_error(sprintf(self::TEXT_PHPExt,'sockets'));
	}

}
