<?php

$f3=require('lib/base.php');

$f3->route('GET /',function($f3){
	echo 'Home';
	if ($f3->exists('GET.color',$color))
		echo ' is '.$color;
});

$f3->route('GET /web',function($f3){
	echo '<h1>Web</h1>'.$f3->QUERY;
});

$f3->route('GET /log/@cmd',function($f3,$params){
	echo $params['cmd'];
});

$f3->route('GET /debug/@cmd',function($f3,$params){
	if ($params['cmd']=='uri')
		echo $_SERVER['REQUEST_URI'];
	if ($params['cmd']=='get') {
		$str='';
		foreach($f3->get('GET') as $k=>$v)
			$str.=($str?',':'')."$k:$v";
		echo $str;
	}
});

$f3->run();
