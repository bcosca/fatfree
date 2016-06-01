<?php

$f3=require('lib/base.php');

$f3->route('GET /',function($f3){
	echo 'Home';
});

$f3->route('GET /web',function($f3){
	echo '<h1>Web</h1>'.$f3->QUERY;
});

$f3->run();
