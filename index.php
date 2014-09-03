<?php

$f3=require('lib/base.php');

$f3->set('DEBUG',2);
$f3->set('UI','ui/');

$f3->set('menu',
	array(
		'/'=>'Globals',
		'/internals'=>'Internals',
		'/hive'=>'Hive',
		'/lexicon'=>'Lexicon',
		'/autoload'=>'Autoloader',
		'/redir'=>'Router',
		'/cache'=>'Cache Engine',
		'/config'=>'Config',
		'/template'=>'Template',
		'/markdown'=>'Markdown',
		'/unicode'=>'Unicode',
		'/audit'=>'Audit',
		'/basket'=>'Basket',
		'/sql'=>'SQL',
		'/mongo'=>'MongoDB',
		'/jig'=>'Jig',
		'/auth'=>'Auth',
		'/log'=>'Log Engine',
		'/matrix'=>'Matrix',
		'/image'=>'Image',
		'/web'=>'Web',
		'/geo'=>'Geo',
		'/google'=>'Google',
		'/openid'=>'OpenID',
		'/pingback'=>'Pingback'
	)
);

$f3->map('/','App\Globals');
$f3->map('/@controller','App\@controller');

$f3->run();
