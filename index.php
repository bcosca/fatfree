<?php

$f3=require('lib/base.php');

$f3->set('DEBUG',2);
$f3->set('UI','ui/');

$f3->set('menu',
	array(
		'/'=>'Internals',
		'/globals'=>'Globals',
		'/hive'=>'Hive',
		'/lexicon'=>'Lexicon',
		'/autoload'=>'Autoloader',
		'/redir'=>'Router',
		'/cache'=>'Cache Engine',
		'/config'=>'Config',
		'/template'=>'Template',
		'/unicode'=>'Unicode',
		'/audit'=>'Audit',
		'/sql'=>'SQL',
		'/mongo'=>'MongoDB',
		'/jig'=>'Jig',
		'/log'=>'Log Engine',
		'/matrix'=>'Matrix',
		'/image'=>'Image',
		'/web'=>'Web',
		'/geo'=>'Geo',
		'/openid'=>'OpenID',
		'/pingback'=>'Pingback'
	)
);

$f3->map('/','App\Internals');
$f3->map('/@controller','App\@controller');

$f3->run();
