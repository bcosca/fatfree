<?php

/**
*	Execute the following command from a terminal to run the WebSocket server
*	as a background service
*
*	php -q /path/to/rfc6455.php [ >> /path/to/rfc6455.log ] & disown
**/

/**
*	Simple console logger
*	@return NULL
*	@param $line string
**/
function trace($line) {
	echo "\r".date('H:i:s').' ['.memory_get_usage(TRUE).'] '.$line.PHP_EOL;
}

if (PHP_SAPI!='cli') {
	// Prohibit direct HTTP access
	header('HTTP/1.1 404 Not Found');
	die;
}

chdir(__DIR__);
ini_set('default_socket_timeout',3);

$fw=require('lib/base.php');

error_reporting(
	(E_ALL|E_STRICT)&~(E_NOTICE|E_USER_NOTICE|E_WARNING|E_USER_WARNING)
);

$fw->DEBUG=2;
$fw->ONERROR=function($fw) {
	trace($fw->get('ERROR.text'));
	foreach (explode("\n",trim($fw->get('ERROR.trace'))) as $line)
		trace($line);
};

// Instantiate the server
$ws=new CLI\WS('tcp://0.0.0.0:9000');

$ws->
	on('start',function($server) use($fw) {
		trace('WebSocket server started');
		$fw->write('tmp/ws.log','start'.PHP_EOL);
	})->
	on('error',function($server) use($fw) {
		if ($err=socket_last_error()) {
			trace(socket_strerror($err));
			socket_clear_error();
		}
		if ($err=error_get_last())
			trace($err['message']);
	})->
	on('stop',function($server) use($fw) {
		trace('Shutting down');
	})->
	on('connect',function($agent) use($fw) {
		trace(
			'(0x00'.$agent->uri().') '.$agent->id().' connected '.
			'<'.(count($agent->server()->agents())+1).'>'
		);
		$fw->write('tmp/ws.log','connect'.PHP_EOL,TRUE);
	})->
	on('disconnect',function($agent) use($fw) {
		trace('(0x08'.$agent->uri().') '.$agent->id().' disconnected');
		if ($err=socket_last_error()) {
			trace(socket_strerror($err));
			socket_clear_error();
		}
		$fw->write('tmp/ws.log','disconnect'.PHP_EOL,TRUE);
	})->
	on('idle',function($agent) use($fw) {
		$fw->write('tmp/ws.log','idle'.PHP_EOL,TRUE);
	})->
	on('receive',function($agent,$op,$data) use($fw) {
		switch($op) {
		case CLI\WS::Pong:
			$text='pong';
			break;
		case CLI\WS::Text:
			$data=trim($data);
		case CLI\WS::Binary:
			$text='data';
			break;
		}
		trace(
			'(0x'.str_pad(dechex($op),2,'0',STR_PAD_LEFT).
			$agent->uri().') '.$agent->id().' '.$text.' received'
		);
		if ($op==CLI\WS::Text && $data) {
			$in=json_decode($data,TRUE);
			$agent->send(CLI\WS::Text,json_encode($in));
			$fw->write('tmp/ws.log','receive'.PHP_EOL,TRUE);
		}
	})->
	on('send',function($agent,$op,$data) use($fw) {
		switch($op) {
		case CLI\WS::Ping:
			$text='ping';
			break;
		case CLI\WS::Text:
			$data=trim($data);
		case CLI\WS::Binary:
			$text='data';
			break;
		}
		trace(
			'(0x'.str_pad(dechex($op),2,'0',STR_PAD_LEFT).
			$agent->uri().') '.$agent->id().' '.$text.' sent'
		);
		if ($op==CLI\WS::Text) {
			$out=json_decode($data,TRUE);
			$fw->write('tmp/ws.log','send'.PHP_EOL,TRUE);
		}
	})->
	run();
