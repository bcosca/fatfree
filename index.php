<?php

$app=require __DIR__.'/lib/base.php';

$app->set('AUTOLOAD','inc/;inc/temp/');
$app->set('CACHE',FALSE);
$app->set('DEBUG',3);
$app->set('EXTEND',TRUE);
$app->set('EXTERNAL','bin/');
$app->set('FONTS','fonts/');
$app->set('GUI','gui/');
$app->set('LOCALES','dict/');
$app->set('PROXY',TRUE);
$app->set('TEMP','temp/');

$app->set('timer',microtime(TRUE));
$app->set('menu',
	array(
		'/'=>'Error Handling',
		'globals'=>'Globals',
		'f3vars'=>'F3 Variables',
		'configure'=>'Configuration',
		'redirect'=>'Routing',
		'ecache'=>'Cache Engine',
		'validator'=>'User Input',
		'renderer'=>'Output Rendering',
		'template'=>'Template Engine',
		'axon'=>'SQL/Axon',
		'jig'=>'Jig Mapper',
		'm2'=>'M2 Mapper',
		'matrix'=>'Matrix',
		'unicode'=>'Unicode',
		'graphics'=>'Graphics',
		'geo'=>'Geo',
		'google'=>'Google',
		'web'=>'Web Tools',
		'network'=>'Network',
		'misc'=>'Miscellaneous',
		'openid'=>'OpenID'
	)
);

$app->route('GET /','Main->hotlink');
foreach ($app->get('menu') as $url=>$func)
	if ($url!='/')
		$app->route('GET /'.$url,'Main->'.$url);

$app->route('GET /error','Main->ehandler');
$app->route('GET /routing','Main->routing');
$app->route('GET /openid2','Main->openid2');
$app->route('GET /captcha',
	function() {
		Graphics::captcha(180,60,5);
	}
);
$app->route('GET /identicon/@id/@size',
	function() {
		Graphics::identicon(f3::get('PARAMS.id'),f3::get('PARAMS.size'));
	}
);
$app->route('GET /invert',
	function() {
		Graphics::invert('{{@GUI}}test.jpg');
	}
);
$app->route('GET /thumb',
	function() {
		Graphics::thumb('{{@GUI}}large.jpg',256,192);
	},60
);

$app->route('GET /screenshot',
	function() {
		Graphics::screenshot('http://www.yahoo.com',150,200);
	}
);
$app->route('GET /google/map',
	function() {
		Google::staticmap('Brooklyn Bridge',12,'256x256');
	}
);
$app->route('GET /minified/@script',
	function() use($app) {
		Web::minify($app->get('GUI'),array($app->get('PARAMS.script')));
	}
);

$app->run();

class Obj {
	public function hello() {
		echo 'hello';
	}
}

class CustomObj {
	public function hello() {
		echo 'hello';
	}
	public function __toString() {
		return 'CustomObj';
	}
}

function first() {
	global $flag;
	$flag=FALSE;
	F3::set('f3flag',FALSE);
}

function second() {
	F3::set('f3flag','xyz');
}
