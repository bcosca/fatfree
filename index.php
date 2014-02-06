<?php

$f3=require('lib/base.php');

$f3->set('DEBUG',1);
/*if ((float)PCRE_VERSION<7.9)
	trigger_error('PCRE version is out of date');*/

$f3->config('config.ini');

$f3->route('GET /',  'UserController->index');
$f3->route('GET|POST /user/create',  'UserController->create');
$f3->route('GET|POST /user/update/@id', 'UserController->update');
$f3->route('GET /user/delete/@id', 'UserController->delete');

$f3->run();
