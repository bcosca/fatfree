<?php

class Main extends F3instance {

	function hotlink() {
		$this->set('HOTLINK','/error');
		file_put_contents('f3hotlink.tmp',md5('f3hotlink'));
		$this->clear('ROUTES');
		$this->route('GET /',
			function() {
				echo 'This should never be executed';
			},0,5000,FALSE
		);
		$this->route('GET /error',array($this,'ehandler'));
		$this->mock('GET /');
		$_SERVER['HTTP_REFERER']='http://www.yahoo.com/search/';
		$this->run();
	}

	function ehandler() {
		$this->set('title','Error Handling');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$this->expect(
			file_exists('f3hotlink.tmp') &&
			file_get_contents('f3hotlink.tmp')==md5('f3hotlink'),
			'Hotlink test succeeded',
			'Hotlink test failed - you shouldn\'t reload this page'
		);
		@unlink('f3hotlink.tmp');

		$this->set('QUIET',TRUE);
		$this->status(69);
		$this->expect(
			!is_null($this->get('ERROR')) && $this->get('ERROR.code')===500,
			$this->get('ERROR.text'),
			'No error detected: '.var_export($this->get('ERROR'),TRUE)
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$this->set('QUIET',TRUE);
		$this->set('a*x',123);
		$this->expect(
			!is_null($this->get('ERROR')) && $this->get('ERROR.code')===500,
			$this->get('ERROR.text'),
			'No error detected: '.var_export($this->get('ERROR'),TRUE)
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$static='Akismet|AtomRSS|Auth|Data|Geo|Google|Graphics|ICU|Net|'.
			'Template|Twitter|UTF|Web|XML|Yahoo';
		foreach (explode('|',$static) as $class) {
			$this->set('QUIET',TRUE);
			new $class;
			$this->expect(
				!is_null($this->get('ERROR')) && $this->get('ERROR.code')===500,
				$this->get('ERROR.text'),
				$class.' class instantiated'
			);
			$this->set('QUIET',FALSE);
			$this->clear('ERROR');
		}

		$dynamic='DB|FileDB|Log|M2|Zip';
		foreach (array_merge(explode('|',$static),explode('|',$dynamic))
			as $class) {
			$this->set('QUIET',TRUE);
			$method=$this->hash(mt_rand(0,getrandmax()));
			$class::$method();
			$this->expect(
				!is_null($this->get('ERROR')) && $this->get('ERROR.code')===500,
				$this->get('ERROR.text'),
				'No error detected: '.var_export($this->get('ERROR'),TRUE)
			);
			if (in_array($class,explode('|',$dynamic))) {
				try {
					$z=new $class('abc');
					$z->$method();
					$this->expect(
						!is_null($this->get('ERROR')) && $this->get('ERROR.code')===500,
						$this->get('ERROR.text'),
						'No error detected: '.var_export($this->get('ERROR'),TRUE)
					);
					// Remove file created by FileDB|Log class (side-effect)
					@rmdir('abc');
					@unlink('abc');
				}
				catch(exception $x) {
					$this->expect(
						!is_null($this->get('ERROR')) && $this->get('ERROR.code')===500,
						$this->get('ERROR.text'),
						'No error detected: '.var_export($this->get('ERROR'),TRUE)
					);
				}
			}
			$this->set('QUIET',FALSE);
			$this->clear('ERROR');
		}

		echo $this->render('basic/results.htm');
	}

	function globals() {
		$this->set('title','Globals');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		foreach (explode('|',F3::PHP_Globals) as $php) {
			$this->set($php.'.x',3.14);
			$this->set($php.'.y',0.15);
			$this->expect(
				$this->get($php)==$GLOBALS['_'.$php],
				$php.' matches $_'.$php,
				$php.' does not match $_'.$php.': '.
					var_export($this->get($php),TRUE).' != '.
					var_export($GLOBALS['_'.$php],TRUE)
			);
		}

		$this->set('POST.xyz',567);
		$this->expect(
			$_POST['xyz']===567 &&
				$this->get('POST.xyz')===567 && $this->get('POST["xyz"]')===567,
			'F3-mirrored PHP variable also alters underlying variable',
			'Underlying variable unchanged: '.
				var_export($_POST['xyz'],TRUE)
		);

		$this->set('POST["xyz"]',567);
		$this->expect(
			$_POST['xyz']===567 &&
				$this->get('POST.xyz')===567 && $this->get('POST["xyz"]')===567,
			'$this->set() variant also alters underlying variable',
			'Underlying variable unchanged: '.
				var_export($_POST['xyz'],TRUE)
		);

		$this->set('POST.abc.def',999);
		$this->expect(
			$_POST['abc']['def']===999 &&
				$this->get('POST.abc.def')===999 &&
				$this->get('POST["abc"]["def"]')===999,
			'Multi-level array variable mirrored properly',
			'Variable mirroring issue: '.
				var_export($_POST['abc']['def'],TRUE)
		);

		$_POST['xyz']=789;
		$this->expect(
			$this->get('POST.xyz')===789 && $this->get('POST["xyz"]')===789,
			'Changing a PHP global also alters F3 equivalent',
			'No change in F3-mirrored PHP variable: '.
				var_export($this->get('POST.xyz'),TRUE)
		);

		$_POST['xyz']=234;
		$this->expect(
			$this->get('POST.xyz')===234 && $this->get('POST["xyz"]')===234,
			'PHP global in sync with F3 equivalent',
			'PHP global not in sync with F3 equivalent: '.
				var_export($this->get('POST.xyz'),TRUE)
		);

		$this->clear('POST');
		$this->expect(
			!isset($_POST) && !$this->exists('POST'),
			'Clearing F3 variable also clears PHP global',
			'PHP global not cleared: '.
				var_export($this->get('POST'),TRUE)
		);

		$this->expect(
			strlen(session_id()),
			'Session auto-started',
			'Session was not auto-started: '.var_export($_SESSION,TRUE)
		);

		$this->clear('SESSION.x');
		$this->expect(
			!$this->exists('SESSION.x') && !isset($_SESSION['x']),
			'Session variable cleared',
			'Session variable not cleared: '.var_export($_SESSION,TRUE)
		);

		$this->clear('SESSION');
		$this->expect(
			!session_id(),
			'Session destroyed',
			'Session was not destroyed: '.var_export($_SESSION,TRUE)
		);

		echo $this->render('basic/results.htm');
	}

	function f3vars() {
		$this->set('title','F3 Variables');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$base=$this->get('BASE');
		$this->set('BASE','abc/123');

		$this->expect(
			$this->get('BASE')==$base,
			'Altering a read-only framework variable has no effect',
			'Value of read-only framework variable changed: '.$this->get('BASE')
		);

		$this->expect(
			$this->get('title')=='F3 Variables',
			'String assigned to userland variable',
			'Incorrect value/data type: '.
				var_export($this->get('title'),TRUE).'/'.
				gettype($this->get('title'))
		);
		$this->expect(
			$this->get('title')=='F3 Variables' && is_string($this->get('title')),
			'String value preserved',
			'Incorrect value/data type: '.
				var_export($this->get('title'),TRUE).'/'.
				gettype($this->get('title'))
		);
		$this->expect(
			$this->get('title')==F3::get('title') && is_string(F3::get('title')),
			'f3::get() and $this->get() return the same value',
			'f3::get() and $this->get() behave differently! '.
				var_export($this->get('title'),TRUE).'/'.
				var_export(F3::get('title'),TRUE)
		);

		$this->set('i',123);
		$this->expect(
			$this->get('i')===123,
			'Integer assigned',
			'Incorrect value/data type: '.
				var_export($this->get('i'),TRUE).'/'.gettype($this->get('i'))
		);
		$this->expect(
			$this->get('i')===123,
			'Integer value preserved',
			'Incorrect value/data type: '.
				var_export($this->get('i'),TRUE).'/'.gettype($this->get('i'))
		);

		$this->set('f',345.6);
		$this->expect(
			$this->get('f')===345.6,
			'Float assigned',
			'Incorrect value/data type: '.
				var_export($this->get('f'),TRUE).'/'.gettype($this->get('f'))
		);
		$this->expect(
			$this->get('f')===345.6,
			'Float value preserved',
			'Incorrect value/data type: '.
				var_export($this->get('f'),TRUE).'/'.gettype($this->get('f'))
		);

		$this->set('e',1.23e-4);
		$this->expect(
			$this->get('e')===1.23e-4,
			'Negative exponential float assigned',
			'Incorrect value/data type: '.
				var_export($this->get('e'),TRUE).'/'.gettype($this->get('e'))
		);

		$this->set('e',1.23e+4);
		$this->expect(
			$this->get('e')===1.23e+4,
			'Positive exponential float value preserved',
			'Incorrect value/data type: '.
				var_export($this->get('e'),TRUE).'/'.gettype($this->get('e'))
		);

		$this->set('e',1.23e4);
		$this->expect(
			$this->get('e')===1.23e4,
			'Unsigned exponential float value preserved',
			'Incorrect value/data type: '.
				var_export($this->get('e'),TRUE).'/'.gettype($this->get('e'))
		);

		$this->set('b',TRUE);
		$this->expect(
			$this->get('b')===TRUE,
			'Boolean value preserved',
			'Incorrect value/data type: '.
				var_export($this->get('b'),TRUE).'/'.gettype($this->get('b'))
		);

		$this->set('a',array(1,'inner',3.5));
		$this->expect(
			is_array($this->get('a')) && $this->get('a')==array(1,'inner',3.5),
			'Array preserved',
			'Incorrect value/data type: '.
				var_export($this->get('a'),TRUE).'/'.gettype($this->get('a'))
		);

		$this->push('a','after');
		$this->expect(
			$this->get('a')==array(1,'inner',3.5,'after'),
			'Array push() works',
			'Array push() failed'
		);

		$this->expect(
			$this->pop('a')=='after' && $this->get('a')==array(1,'inner',3.5),
			'Array pop() works',
			'Array pop() failed'
		);

		$this->unshift('a','before');
		$this->expect(
			$this->get('a')==array('before',1,'inner',3.5),
			'Array unshift() works',
			'Array unshift() failed'
		);

		$this->expect(
			$this->shift('a')=='before' && $this->get('a')==array(1,'inner',3.5),
			'Array shift() works',
			'Array shift() failed'
		);

		$this->flip('a');
		$this->expect(
			$this->get('a')==array(1=>0,'inner'=>1,'3.5'=>2),
			'Array flip() works',
			'Array flip() failed: '.var_export($this->get('a'),TRUE)
		);

		$this->expect(
			is_null($this->get('hello')),
			'Non-existent variable returns NULL',
			'Non-existent variable failure: '.var_export($this->get('hello'),TRUE)
		);

		$this->set('obj',new Obj);
		$this->expect(
			$this->get('obj')==new Obj && is_object($this->get('obj')),
			'Object preserved',
			'Incorrect value/data type: '.
				var_export($this->get('obj'),TRUE).'/'.gettype($this->get('obj'))
		);

		$this->expect(
			$this->exists('i'),
			'Existence confirmed',
			'Variable does not exist: '.var_export($this->get('i'),TRUE)
		);

		$this->clear('i');
		$this->expect(
			!$this->exists('i'),
			'Clear confirmed',
			'Variable not cleared: '.var_export($this->exists('i'),TRUE)
		);

		$this->set('v[0]',123);
		$this->expect(
			$this->exists('v'),
			'Array instantiated when element is assigned a value',
			'Variable does not exist: '.var_export($this->get('v'),TRUE)
		);
		$this->expect(
			$this->get('v')==array(123),
			'Array constructed properly',
			'Array not constructed properly: '.var_export($this->get('v'),TRUE)
		);
		$this->set('v.1',456);
		$this->expect(
			$this->get('v')==array(123,456),
			'Value assigned using dot-notation',
			'Value not assigned: '.var_export($this->get('v'),TRUE)
		);

		$this->clear('v[1]');
		$this->expect(
			$this->get('v')==array(123),
			'Element cleared using regular notation',
			'Value not cleared: '.var_export($this->get('v'),TRUE)
		);

		$this->set('v.2',789);
		$this->clear('v.2');
		$this->expect(
			$this->get('v')==array(123),
			'Element cleared using dot-notation',
			'Value not cleared: '.var_export($this->get('v'),TRUE)
		);

		$this->clear('v');
		$this->expect(
			!$this->exists('v'),
			'Clear confirmed',
			'Array not cleared: '.var_export($this->get('v'),TRUE)
		);

		$this->set('a',369);
		$this->set('b','a');
		$this->set('{{@b}}',246);
		$this->expect(
			$this->get('a')===246,
			'Variable variable assigned',
			'Variable variable assignment error: '.var_export($this->get('a'),TRUE)
		);

		$this->set('a',357);
		$this->expect(
			$this->get('{{@b}}')===357,
			'Variable variable retrieved',
			'Variable variable retrieval error: '.var_export($this->get('{{@b}}'),TRUE)
		);

		$this->set('QUIET',TRUE);
		$this->expect(
			is_null($this->get('{{@b.1}}')) && !is_null($this->get('ERROR')),
			'Incorrect variable variable usage',
			'Variable variable usage error: '.var_export($this->get('{{@b.1}}'),TRUE)
		);
		$this->set('QUIET',FALSE);

		$this->set('a',array());
		$x=&$this->ref('a');
		$x[]=123;
		$x[]=234;
		$x[]=345;
		$this->set('QUIET',TRUE);
		$this->expect(
			$this->get('a')==array(123,234,345) && !is_null($this->get('ERROR')),
			'Variable references handled properly',
			'Variable references incorrect: '.var_export($this->get('a'),TRUE)
		);
		$this->set('QUIET',FALSE);

		$this->set('a',1);
		$this->set('b',2);
		$this->set('c',array('{{@a}}',array('{{@b}}')));
		$this->expect(
			$this->get('c')==array(1,array(2)),
			'Deeply-nested tokens in framework array variable replaced',
			'Deeply-nested tokens not replaced: '.var_export($this->get('c'),TRUE)
		);

		$this->set('str','hello');
		$this->concat('str',' world');
		$this->expect(
			$this->get('str')=='hello world',
			'String concatenation works',
			'String concatenation failed'
		);

		echo $this->render('basic/results.htm');
	}

	function matrix() {

		$this->set('title','Matrix');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$z=array(
			array('id'=>123,'name'=>'paul','sales'=>0.35),
			array('id'=>456,'name'=>'ringo','sales'=>0.13),
			array('id'=>345,'name'=>'george','sales'=>0.57),
			array('id'=>234,'name'=>'john','sales'=>0.79)
		);
		Matrix::sort($z,'name');
		$this->expect(
			array_values($z)==array(
				array('id'=>345,'name'=>'george','sales'=>0.57),
				array('id'=>234,'name'=>'john','sales'=>0.79),
				array('id'=>123,'name'=>'paul','sales'=>0.35),
				array('id'=>456,'name'=>'ringo','sales'=>0.13),
			),
			'Sorting a multi-dimensional array by string column works properly',
			'Incorrect array sort algorithm: '.var_export($z,TRUE)
		);

		Matrix::sort($z,'id');
		$this->expect(
			array_values($z)==array(
				array('id'=>123,'name'=>'paul','sales'=>0.35),
				array('id'=>234,'name'=>'john','sales'=>0.79),
				array('id'=>345,'name'=>'george','sales'=>0.57),
				array('id'=>456,'name'=>'ringo','sales'=>0.13)
			),
			'Sorting a multi-dimensional array by integer column works properly',
			'Incorrect array sort algorithm: '.var_export($z,TRUE)
		);

		Matrix::sort($z,'sales');
		$this->expect(
			array_values($z)==array(
				array('id'=>456,'name'=>'ringo','sales'=>0.13),
				array('id'=>123,'name'=>'paul','sales'=>0.35),
				array('id'=>345,'name'=>'george','sales'=>0.57),
				array('id'=>234,'name'=>'john','sales'=>0.79)
			),
			'Sorting a multi-dimensional array by float column works properly',
			'Incorrect array sort algorithm: '.var_export($z,TRUE)
		);

		echo $this->render('basic/results.htm');
	}

	function configure() {
		$this->set('title','Configuration');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$this->clear('ROUTES');

		include 'inc/config.inc.php';
		$this->config('inc/config.ini');

		$this->expect(
			$this->get('num')==123,
			'Integer variable found',
			'Missing integer variable'
		);

		$this->expect(
			$this->get('str')=='abc',
			'String variable found',
			'Missing string variable'
		);

		$this->expect(
			$this->get('hash')==array('x'=>1,'y'=>2,'z'=>3),
			'Hash variable found',
			'Missing hash variable'
		);

		$this->expect(
			$this->get('list')==array(7,8,9),
			'List variable found',
			'Missing list variable'
		);

		$this->expect(
			$this->get('mix')==array("this",123.45,FALSE),
			'Mixed array variable found',
			'Missing mixed array variable'
		);

		$this->set('QUIET',TRUE);
		$this->mock('GET /');
		$this->run();
		$this->expect(
			is_null($this->get('ERROR')),
			$this->get('SERVER.REQUEST_METHOD').' '.$this->get('PARAMS.0').' exists',
			'Routing/configuration error: '.$this->get('ERROR.text')
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$this->set('QUIET',TRUE);
		$this->mock('GET /404');
		$this->run();
		$this->expect(
			is_null($this->get('ERROR')),
			$this->get('SERVER.REQUEST_METHOD').' '.$this->get('PARAMS.0').' exists',
			'Routing/configuration error: '.$this->get('ERROR.text')
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$this->set('QUIET',TRUE);
		$this->mock('GET /inside/multi');
		$this->run();
		$this->expect(
			is_null($this->get('ERROR')),
			$this->get('SERVER.REQUEST_METHOD').' '.$this->get('PARAMS.0').' exists',
			'Routing/configuration error: '.$this->get('ERROR.text')
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$this->set('QUIET',TRUE);
		$this->mock('GET /noroute');
		$this->run();
		$this->expect(
			!is_null($this->get('ERROR')),
			$this->get('ERROR.text'),
			'Routing/configuration error: '.$this->get('ERROR.text')
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$this->set('QUIET',TRUE);
		$this->mock('GET /map');
		$this->run();
		$this->expect(
			is_null($this->get('ERROR')),
			$this->get('SERVER.REQUEST_METHOD').' '.$this->get('PARAMS.0').' exists',
			'Routing/configuration error: '.$this->get('ERROR.text')
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$this->set('QUIET',TRUE);
		$this->mock('POST /map');
		$this->run();
		$this->expect(
			is_null($this->get('ERROR')),
			$this->get('SERVER.REQUEST_METHOD').' '.$this->get('PARAMS.0').' exists',
			'Routing/configuration error: '.$this->get('ERROR.text')
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$this->set('QUIET',TRUE);
		$this->mock('DELETE /map');
		$this->run();
		$this->expect(
			!is_null($this->get('ERROR')) && $this->get('ERROR.code')===405,
			'DELETE /map triggered an HTTP 405',
			'Routing/configuration error: '.$this->get('ERROR.code')
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$this->set('QUIET',TRUE);
		$this->mock('PUT /map');
		$this->run();
		$this->expect(
			is_null($this->get('ERROR')),
			$this->get('SERVER.REQUEST_METHOD').' '.$this->get('PARAMS.0').' exists',
			'Routing/configuration error: '.$this->get('ERROR.text')
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		echo $this->render('basic/results.htm');
	}

	function redirect() {
		file_put_contents('f3routing.tmp',md5('f3routing'));
		$this->reroute('/routing');
	}

	function routing() {
		$this->set('title','Routing');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$this->expect(
			file_exists('f3routing.tmp') &&
			file_get_contents('f3routing.tmp')==md5('f3routing'),
			'Rerouting succeeded',
			'Rerouting did not work as expected - you shouldn\'t reload this page'
		);
		@unlink('f3routing.tmp');

		$this->set('QUIET',TRUE);
		$this->clear('ROUTES');
		$this->run();
		$this->expect(
			!is_null($this->get('ERROR')) && $this->get('ERROR.code')===500,
			'HTTP 500 expected - '.$this->get('ERROR.text'),
			'No error detected: '.var_export($this->get('ERROR'),TRUE)
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$this->set('QUIET',TRUE);
		$this->route('GRAB /','test');
		$this->mock('GET /');
		$this->run();
		$this->expect(
			!is_null($this->get('ERROR')) && $this->get('ERROR.code')===405,
			$this->get('ERROR.text'),
			'No HTTP 405 triggered: '.$this->get('ERROR.text')
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$this->clear('ROUTES');

		$this->set('IMPORTS','inc/');
		file_put_contents('inc/temp.php',
			'<?php '.
				'F3::set(\'temp\',\'inside\');'
		);
		$this->route('GET /','temp.php');
		$this->mock('GET /');
		$this->run();
		$this->expect(
			$this->get('temp')=='inside',
			'Import file loaded',
			'Import file failure'
		);
		@unlink('inc/temp.php');

		$this->clear('ROUTES');

		$this->set('QUIET',TRUE);
		$this->route('GET /',
			function() {}
		);
		$this->mock('GET /test/noroute');
		$this->run();
		$this->expect(
			!is_null($this->get('ERROR')) && $this->get('ERROR.code')===404,
			'HTTP 404 expected - non-existent route',
			'No HTTP 404 triggered'
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$this->clear('ROUTES');

		$this->set('QUIET',TRUE);
		$this->route('POST /','nonexistent');
		$this->mock('POST /');
		$this->run();
		$this->expect(
			!is_null($this->get('ERROR')) && $this->get('ERROR.code')===404,
			'HTTP 404 expected - non-existent function',
			'No HTTP 404 triggered: '.$this->get('ERROR.text')
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$this->set('QUIET',TRUE);
		$this->error(404);
		$this->expect(
			!is_null($this->get('ERROR')) && $this->get('ERROR.code')===404,
			'Programmatically-triggered HTTP 404',
			'No HTTP 404 triggered'
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$anon=TRUE;
		$this->set('anon',TRUE);

		$this->mock('GET /solo');
		$self=$this;
		$this->route('GET /solo',
			function() use($anon) {
				$anon=FALSE;
				F3::set('anon',FALSE);
			}
		);
		$this->run();

		$this->expect(
			$anon && $this->get('anon')===FALSE,
			'Routed to anonymous function',
			'Issue with routing to anonymous function'
		);

		$this->clear('ROUTES');
		$this->clear('ERROR');

		function dummy1() {
			F3::set('routed',1);
			F3::set('x','i');
		}
		function dummy2() {
			F3::set('routed',2);
			F3::set('y','am');
		}
		function dummy3() {
			F3::set('routed',3);
			F3::set('z','fine');
		}
		function dummy4() {
			F3::set('routed',4);
		}
		$this->route('GET /a','dummy1');
		$this->route('GET /a/b/c','dummy2');
		$this->route('GET|POST /a-b/c','dummy3');
		$this->route('POST /a/b','dummy4');

		$this->set('QUIET',TRUE);
		$this->mock('POST /a');
		$this->run();
		$this->expect(
			!is_null($this->get('ERROR')),
			'No handler for mock route - triggered error',
			'Route handling issue'
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$this->route('GET /hoohah','dummy1|dummy2|dummy3');
		$this->mock('GET /hoohah');
		$this->run();
		$this->expect(
			$this->get('x')=='i' && $this->get('y')=='am' && $this->get('z')=='fine',
			'Route handler containing chained functions executed',
			'Problem with chained functions'
		);

		$this->set('routed',0);
		$this->mock('GET /a');
		$this->run();
		$this->expect(
			$this->get('routed')===1,
			'Non slash-terminated URI routed properly',
			'Slash-terminated URI routing issue'
		);

		$this->set('routed',0);
		$this->mock('GET /a/');
		$this->run();
		$this->expect(
			$this->get('routed')===1,
			'Slash-terminated URI routed properly',
			'Slash-terminated URI routing issue'
		);

		$this->set('routed',0);
		$this->mock('GET /a/b/c');
		$this->run();
		$this->expect(
			$this->get('routed')===2,
			'Non slash-terminated URI (deep nesting) routed properly',
			'Slash-terminated URI (deep nesting) routing issue'
		);

		$this->set('routed',0);
		$this->mock('GET /a-b/c');
		$this->run();
		$this->expect(
			$this->get('routed')===3,
			'Slash-terminated URI (deep nesting) routed properly',
			'Slash-terminated URI (deep nesting) routing issue'
		);

		$this->set('routed',0);
		$this->mock('GET /a-b/c/');
		$this->run();
		$this->expect(
			$this->get('routed')===3,
			'Slash-terminated URI (with special characters) routed properly',
			'Slash-terminated URI (with special characters) routing issue'
		);

		$this->set('routed',0);
		$this->mock('GET /a-b/c?x=557&y=355');
		$this->run();
		$this->expect(
			$this->get('routed')===3,
			'URI (with special characters and GET variables) routed properly',
			'URI (with special characters and GET variables) routing issue'
		);

		$this->expect(
			$this->get('GET')==array('x'=>'557','y'=>'355'),
			'GET variables passed to framework-mirrored PHP variable',
			'Issue with GET variables in URI: '.var_export($this->get('GET'),TRUE)
		);

		$this->set('routed',0);
		$this->mock('GET /a-b/c/?x=557&y=355');
		$this->run();
		$this->expect(
			$this->get('GET')==array('x'=>'557','y'=>'355'),
			'GET variables in slash-terminated URI passed properly',
			'Issue with slash-terminated URI: '.var_export($this->get('GET'),TRUE)
		);

		$this->set('routed',0);
		$this->mock('POST /a-b/c?x=557&y=355');
		$this->run();
		$this->expect(
			$this->get('routed')===3,
			'Route with combined GET|POST executed correctly',
			'Handling of route with combined GET|POST is faulty'
		);

		$this->set('routed',0);
		$this->mock('DELETE /a-b/c?x=557&y=355');
		$this->set('QUIET',TRUE);
		$this->run();
		$this->set('QUIET',FALSE);
		$this->expect(
			!is_null($this->get('ERROR')) && $this->get('ERROR.code')==405,
			'No matching route for DELETE request method',
			'DELETE request method handled incorrectly'
		);

		$this->set('routed',0);
		$this->mock('POST /a/b?x=224&y=466');
		$this->run();
		$this->expect(
			$this->get('routed')===4,
			'POST route handler called',
			'Routing issue with POST method'
		);

		$this->set('routed',0);
		$this->mock('POST /a/b?x=224&y=466');
		$this->run();
		$this->expect(
			$this->get('POST')===array('x'=>'224','y'=>'466'),
			'POST variables passed to framework-mirrored PHP variable',
			'Issue with POST variables in URI: '.var_export($this->get('POST'),TRUE)
		);

		$this->clear('ROUTES');
		$this->clear('ERROR');

		$this->route('GET /a','dummy1');
		$this->route('GET /a/@token','dummy2');
		$this->route('GET /old-adage/a/@token1/@token2/@token3','dummy3');

		$this->set('routed',0);
		$this->mock('GET /a/x');
		$this->run();
		$this->expect(
			$this->get('routed')===2,
			'Framework treats root URI and tokenized URI differently',
			'Routing problem with tokens'
		);

		$this->set('QUIET',TRUE);
		$this->set('routed',0);
		$this->mock('GET /a/rose/is/a/rose');
		$this->run();
		$this->expect(
			!is_null($this->get('ERROR')) && $this->get('ERROR.code')===404,
			'Expected a 404 error - no route handler for specified URI',
			'HTTP 404 not triggered'
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$this->mock('GET /old-adage/a/bird/in/hand');
		$this->run();
		$this->expect(
			$this->get('routed')===3 &&
			$this->get('PARAMS.token1')==='bird' &&
			$this->get('PARAMS.token2')==='in' &&
			$this->get('PARAMS.token3')==='hand',
			'URI tokens handled properly',
			'Incorrect handling of URI tokens'
		);

		$this->mock('GET /old-adage/a/bird/in/hand/');
		$this->run();
		$this->expect(
			$this->get('routed')===3 &&
			$this->get('PARAMS.token1')==='bird' &&
			$this->get('PARAMS.token2')==='in' &&
			$this->get('PARAMS.token3')==='hand',
			'URI tokens handled correctly even with a trailing slash',
			'Incorrect handling of URI tokens'
		);

		$this->mock('GET /old-adage/a/fool-and/his-money-are/soon-parted/');
		$this->run();
		$this->expect(
			$this->get('routed')===3 &&
			$this->get('PARAMS.token1')==='fool-and' &&
			$this->get('PARAMS.token2')==='his-money-are' &&
			$this->get('PARAMS.token3')==='soon-parted',
			'URI tokens distributed correctly',
			'Incorrect distribution of URI tokens'
		);

		$this->mock('GET /old-adage/a/fool and/his money are/soon parted/');
		$this->run();
		$this->expect(
			$this->get('PARAMS.token1')==='fool and' &&
			$this->get('PARAMS.token2')==='his money are' &&
			$this->get('PARAMS.token3')==='soon parted',
			'URL-encoded data (containing spaces) in route handled properly',
			'Issue with URL-encoded data containing spaces'
		);

		$this->clear('ROUTES');

		@mkdir('inc/temp',0755);
		file_put_contents('inc/temp/ext.php',
			'<?php '.
				'class Ext {'.
					'function myfunc() {'.
						'F3::set(\'routed\',5);'.
					'}'.
				'}'
		);

		$this->route('GET /ext','Ext->myfunc');
		$this->mock('GET /ext');
		$this->run();
		$this->expect(
			$this->get('routed')===5,
			'Routed to autoload class',
			'Routing to autoload class failed'
		);

		@unlink('inc/temp/ext.php');

		@mkdir('inc/temp/ns',0755);
		file_put_contents('inc/temp/ns/deep.php',
			'<?php '.
				'namespace ns; '.
				'class Deep {'.
					'public function innerfunc() {'.
						'\\F3::set(\'routed\',6);'.
					'}'.
				'}'
		);
		@mkdir('inc/temp/ns/ns2',0755);
		file_put_contents('inc/temp/ns/ns2/deeper.php',
			'<?php '.
				'namespace ns\\ns2; '.
				'class Deeper {'.
					'function innermostfunc() {'.
						'\\F3::set(\'routed\',7);'.
					'}'.
				'}'
		);

		$this->route('GET /deep','ns\Deep->innerfunc');
		$this->mock('GET /deep');
		$this->run();
		$this->expect(
			$this->get('routed')===6,
			'Autoloaded level-1 namespaced class',
			'Routing to level-1 namespaced class failed'
		);

		$this->route('GET /deeper','ns\ns2\Deeper->innermostfunc');
		$this->mock('GET /deeper');
		$this->run();
		$this->expect(
			$this->get('routed')===7,
			'Autoloaded level-2 namespaced class',
			'Routing to level-2 namespaced autoload class failed'
		);

		dummy3();
		$this->expect(
			$this->get('routed')===3,
			'Direct call to route handler',
			'Unable to call route handler directly'
		);

		@unlink('inc/temp/ns/ns2/deeper.php');
		rmdir('inc/temp/ns/ns2');
		@unlink('inc/temp/ns/deep.php');
		rmdir('inc/temp/ns');
		rmdir('inc/temp');

		$this->clear('ROUTES');
		$min=1000; // 1 second minimum execution time
		$time=microtime(TRUE);
		$this->route('GET /throttle',
			function() {
				echo 'done!';
			},
			0,1000
		);

		$this->set('QUIET',TRUE);
		$this->mock('GET /throttle');
		$this->run();
		$this->set('QUIET',FALSE);
		$elapsed=microtime(TRUE)-$time;
		$this->expect(
			$this->get('RESPONSE')=='done!' && $elapsed*1000>=$min,
			'Throttle working properly: done in '.sprintf('%1.3f',$elapsed).' secs',
			'Throttle malfunctioning: completed in '.sprintf('%1.3f',$elapsed).' secs'
		);

		echo $this->render('basic/results.htm');
	}

	function ecache() {

		$this->set('title','Cache Engine');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$this->set('CACHE',TRUE);

		$this->expect(
			$this->get('CACHE'),
			'Cache back-end detected: \''.$this->get('CACHE').'\'',
			'Cache disabled'
		);

		$this->set('x',123,TRUE);
		$this->expect(
			$this->cached('x'),
			'Framework variable cached',
			'Variable not cached: '.var_export($this->cached('x'),TRUE)
		);

		$this->expect(
			$this->get('x'),
			'Value retrieved from cache',
			'Caching issue: '.var_export($this->get('x'),TRUE)
		);

		$this->clear('x');
		$this->expect(
			is_bool($this->cached('x')),
			'Variable removed from cache',
			'Caching issue: '.var_export($this->cached('x'),TRUE)
		);

		$this->clear('ROUTES');

		$ttl=3;
		$this->route('GET /caching',
			function() {
				echo 'here';
			},
			$ttl
		);

		$start=time();
		$i=0;
		while (TRUE) {
			$this->set('QUIET',TRUE);
			$this->mock('GET /caching');
			sleep(1);
			$this->run();
			$cached=Cache::cached('url.'.$this->hash('GET /caching'));
			if (is_bool($cached))
				break;
			$this->set('QUIET',FALSE);
			if (!isset($saved))
				$saved=$cached;
			if ($saved!=$cached)
				break;
			$time=time();
			$this->expect(TRUE,'Cache age @'.date('G:i:s',$time).': '.
				($time-$cached).' secs');
			$i++;
			if ($i==$ttl)
				break;
		}

		$this->expect(
			$i==$ttl,
			'Cache refreshed',
			'Cache TTL has expired'
		);

		echo $this->render('basic/results.htm');
	}

	function validator() {
		$this->set('title','User Input');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$this->route('POST /form',
			function() {
				F3::input('field1','nonexistent');
			}
		);
		$this->set('QUIET',TRUE);
		$this->mock('POST /form');
		$this->run();
		$this->expect(
			!is_null($this->get('ERROR')) && $this->get('ERROR.code')===500,
			'HTTP 500 expected - form field handler is invalid',
			'No HTTP 500 triggered'
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$this->route('POST /form',
			function() {
				F3::input('field',
					function($value) {
						F3::expect(
							$value=='alert(\'hello\');',
							'HTML tags removed (attempt to insert Javascript)',
							'HTML tags were not removed: '.$value
						);
					}
				);
			}
		);
		$this->mock('POST /form',array('field'=>'<script>alert(\'hello\');</script>'));
		$this->run();
		$this->clear('ROUTES');

		$this->expect(
			$_POST['field']=='alert(\'hello\');' &&
			$_POST['field']=='alert(\'hello\');',
			'Framework sanitizes underlying $_POST and $_POST variables',
			'Framework didn\'t sanitize $_POST/$_POST: '.$_POST['field']
		);

		$this->set('POST',array('field'=>'<p><b>hello</b> world</p>'));
		$this->input('field',
			function($value) {
				F3::expect(
					$value=='<p>hello world</p>',
					'HTML tags allowed but not converted to HTML entities'.
						'<br/>Note: application is responsible for '.
						'HTML decoding',
					'HTML tags not converted/blocked by framework: '.$value
				);
			},
			'p'
		);

		$this->set('POST',array('field'=>'Adam & Eve'));
		$this->input('field',
			function($value) {
				F3::expect(
					$value=='Adam & Eve',
					'Ampersand preserved',
					'Ampersand converted to HTML entity!'
				);
			}
		);

		$this->set('POST',array('field'=>'&copy;'));
		$this->input('field',
			function($value) {
				F3::expect(
					$value=='&copy;',
					'No duplicate encoding of HTML entity: '.$value,
					'Double-encoding of HTML entity: '.$value
				);
			}
		);

		$this->set('POST',array('field'=>'hello "world"'));
		$this->input('field',
			function($value) {
				F3::expect(
					$value=='hello "world"',
					'Double-quotes preserved: '.$value,
					'Double-quotes not handled properly: '.$value
				);
			}
		);

		$this->expect(
			Data::validEmail('!def!xyz%abc@example.com'),
			'Valid e-mail address: !def!xyz%abc@example.com',
			'Framework flagged !def!xyz%abc@example.com invalid!'
		);

		$this->expect(
			Data::validEmail('"Abc@def"@example.com'),
			'Valid e-mail address: "Abc@def"@example.com',
			'Framework flagged "Abc@def"@example.com invalid!'
		);

		$this->expect(
			!Data::validEmail('"Abc@def"@example.com',TRUE),
			'Invalid e-mail address: "Abc@def"@example.com (MX record verified)',
			'Framework flagged "Abc@def"@example.com valid!'
		);

		$this->expect(
			!Data::validEmail('Abc@def@example.com'),
			'Invalid e-mail address: Abc@def@example.com',
			'Framework flagged Abc@def@example.com valid!'
		);

		$this->expect(
			Data::validEmail('a@b.com'),
			'Valid e-mail address: a@b.com (MX record not verified)',
			'Framework flagged a@b.com invalid!'
		);

		$this->expect(
			!Data::validEmail('a@b.com',TRUE),
			'Invalid e-mail address: a@b.com (MX record verified)',
			'Framework flagged a@b.com valid!'
		);

		$this->expect(
			Data::validURL('http://www.google.com'),
			'Valid URL: http://www.google.com',
			'Framework flagged http://www.google.com invalid!'
		);

		$this->expect(
			Data::validURL('http://www.yahoo.com/'),
			'Valid URL: http://www.yahoo.com/',
			'Framework flagged http://www.yahoo.com/ invalid!'
		);

		$this->expect(
			Data::validURL(
				'http://www.google.com/search?ie=UTF-8&oe=UTF-8&sourceid=navclient'),
			'Valid URL: '.
				'http://www.google.com/search?ie=UTF-8&oe=UTF-8&sourceid=navclient',
			'Framework flagged '.
				'http://www.google.com/search?ie=UTF-8&oe=UTF-8&sourceid=navclient '.
				'invalid!'
		);

		$this->expect(
			Data::validURL('http://www.yahoo.com?http%3A%2F%2Fwww.yahoo.com'),
			'Valid URL: http://www.yahoo.com?http%3A%2F%2Fwww.yahoo.com',
			'Framework flagged '.
				'http://www.yahoo.com?http%3A%2F%2Fwww.yahoo.com invalid!'
		);

		echo $this->render('basic/results.htm');
	}

	function renderer() {
		$this->set('title','Output Rendering');

		$this->set('CACHE',TRUE);

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$out=$this->render('basic/layout.htm');
		$this->expect(
			$out==="",
			'Subtemplate not defined - none inserted',
			'Subtemplate insertion issue: '.$out
		);

		$this->set('sub','sub1.htm');
		$this->set('test','<i>italics</i>');
		$out=$this->render('basic/layout.htm');
		$this->expect(
			$out=="<i>italics</i>",
			'HTML Special characters retained: '.$out,
			'Problem with HTML insertion: '.$out
		);

		$this->set('sub','sub1.htm');
		$this->set('test','&copy;');
		$out=$this->render('basic/layout.htm');
		$this->expect(
			$out=="&copy;",
			'HTML entity inserted: '.$this->render('basic/layout.htm'),
			'Problem with HTML insertion: '.$out
		);

		$this->set('sub','sub1.htm');
		$this->set('test',chr(0));
		$out=$this->render('basic/layout.htm');
		$this->expect(
			$out==chr(0),
			'Control characters allowed: '.$this->render('basic/layout.htm'),
			'Control characters removed: '.$out
		);

		$this->set('sub','sub1.htm');
		$this->set('test','אני יכול לאכול זכוכית וזה לא מזיק לי.');
		$out=$this->render('basic/layout.htm');
		$this->expect(
			$this->render('basic/layout.htm')=="אני יכול לאכול זכוכית וזה לא מזיק לי.",
			'UTF-8 character set rendered correctly: '.$out,
			'UTF-8 issue: '.$out
		);

		$this->set('sub','sub1.htm');
		$this->set('test','I&nbsp;am&nbsp;here.');
		$out=$this->render('basic/layout.htm');
		$this->expect(
			$this->render('basic/layout.htm')=="I&nbsp;am&nbsp;here.",
			'HTML entities preserved: '.$out,
			'HTML entities converted: '.$out
		);

		$this->set('sub','sub2.htm');
		$this->set('src','/test/image');
		$this->set('alt',htmlspecialchars('this is "the" life'));
		$out=$this->render('basic/layout.htm');
		$this->expect(
			$out==
				'<img src="/test/image" alt="this is &quot;the&quot; life"/>',
			'Double-quotes inside HTML attributes converted to XML entities',
			'Problem with double-quotes inside HTML attributes: '.$out
		);

		$this->set('sub','sub3.htm');
		$out=$this->render('basic/layout.htm');
		$this->clear('div');
		$this->expect(
			$out=='',
			'Undefined array renders empty output',
			'Output not empty: '.$out
		);

		$this->set('sub','sub3.htm');
		$out=$this->render('basic/layout.htm');
		$this->set('div',NULL);
		$this->expect(
			$out=='',
			'NULL used as group attribute renders empty output',
			'Output not empty: '.$out
		);

		$this->set('sub','sub3.htm');
		$out=$this->render('basic/layout.htm');
		$this->set('div',array());
		$this->expect(
			$out=='',
			'Empty array used as group attribute renders empty output',
			'Output not empty: '.$out
		);

		$this->set('sub','sub3.htm');
		$this->set('div',
			array(
				'coffee'=>array('arabica','barako','liberica','kopiluwak'),
				'tea'=>array('darjeeling','pekoe','samovar')
			)
		);
		$out=$this->render('basic/layout.htm');
		$this->expect(
			preg_match(
				'#'.
				'<div>\s+'.
				'<p><span><b>coffee</b></span></p>\s+'.
				'<p>\s+'.
				'<span>arabica</span>\s+'.
				'<span>barako</span>\s+'.
				'<span>liberica</span>\s+'.
				'<span>kopiluwak</span>\s+'.
				'</p>\s+'.
				'</div>\s+'.
				'<div>\s+'.
				'<p><span><b>tea</b></span></p>\s+'.
				'<p>\s+'.
				'<span>darjeeling</span>\s+'.
				'<span>pekoe</span>\s+'.
				'<span>samovar</span>\s+'.
				'</p>\s+'.
				'</div>'.
				'#s',
				$out
			),
			'Subtemplate inserted; nested repeat directives rendered correctly',
			'Template rendering issue: '.var_export($out,TRUE)
		);

		$this->set('sub','sub4.htm');
		$out=$this->render('basic/layout.htm');
		$this->expect(
			preg_match(
				'#'.
				'<script type="text/javascript">\s*'.
				'function hello\(\) {\s*'.
				'alert\(\'Javascript works\'\);\s*'.
				'}\s*'.
				'</script>\s*'.
				'<script type="text/javascript">alert\(unescape\("%3Cscript src=\'" \+ gaJsHost \+ "google-analytics\.com/ga\.js\' type=\'text/javascript\'%3E%3C/script%3E"\)\);</script>\s'.
				'#s',
				$out
			),
			'Javascript preserved',
			'Javascript mangled: '.htmlentities($out)
		);

		$this->set('sub','sub5.htm');
		$this->set('cond1',FALSE);
		$this->set('cond3',FALSE);
		$out=trim($this->render('basic/layout.htm'));
		$this->expect(
			$out=='c1:F,c3:F',
			'Conditional directives evaluated correctly: FALSE, FALSE',
			'Incorrect evaluation of conditional directives: '.$out
		);
		$this->set('cond1',FALSE);
		$this->set('cond3',TRUE);
		$out=trim($this->render('basic/layout.htm'));
		$this->expect(
			$out=='c1:F,c3:T',
			'Conditional directives evaluated correctly: FALSE, TRUE',
			'Incorrect evaluation of conditional directives: '.$out
		);
		$this->set('cond1',TRUE);
		$this->set('cond2',FALSE);
		$out=trim($this->render('basic/layout.htm'));
		$this->expect(
			$out=='c1:T,c2:F',
			'Conditional directives evaluated correctly: TRUE, FALSE',
			'Incorrect evaluation of conditional directives: '.$out
		);
		$this->set('cond1',TRUE);
		$this->set('cond2',TRUE);
		$out=trim($this->render('basic/layout.htm'));
		$this->expect(
			$out=='c1:T,c2:T',
			'Conditional directives evaluated correctly: TRUE, TRUE',
			'Incorrect evaluation of conditional directives: '.$out
		);

		$pi=3.141592654;
		$money=63950.25;

		$this->set('sub','sub6.htm');
		$this->set('LANGUAGE','en');
		$out=$this->render('basic/layout.htm');
		// PHP 5.3.2 inserts a line feed at end of translation
		$this->expect(
			$out==
				"<h3>I love Fat-Free!</h3>\n".
				"<p>Today is ".ICU::format('{0,date}',array(time()))."</p>\n".
				"<p>The quick brown fox jumps over the lazy dog.</p>\n".
				"<p>".ICU::format('{0,number}',array($pi))."</p>\n".
				"<p>".ICU::format('{0,number,currency}',array($money))."</p>",
			'English locale (i18n)',
			'English locale mangled: '.$out
		);

		$this->set('sub','sub6.htm');
		$this->set('LANGUAGE','fr-FR');
		$out=$this->render('basic/layout.htm');
		// PHP 5.3.2 inserts a line feed at end of translation
		$this->expect(
			$out==
				"<h3>J'aime Fat-Free!</h3>\n".
				"<p>Aujourd'hui, c'est ".ICU::format('{0,date}',array(time()))."</p>\n".
				"<p>Les naïfs ægithales hâtifs pondant à Noël où il gèle sont sûrs d'être déçus et de voir leurs drôles d'œufs abîmés.</p>\n".
				"<p>".ICU::format('{0,number}',array($pi))."</p>\n".
				"<p>".ICU::format('{0,number,currency}',array($money))."</p>",
			'Translated properly to French',
			'French translation mangled: '.$out
		);

		$this->set('sub','sub6.htm');
		$this->set('LANGUAGE','es-AR');
		$out=$this->render('basic/layout.htm');
		// PHP 5.3.2 inserts a line feed at end of translation
		$this->expect(
			$out==
				"<h3>Me encanta Fat-Free!</h3>\n".
				"<p>Hoy es ".ICU::format('{0,date}',array(time()))."</p>\n".
				"<p>El pingüino Wenceslao hizo kilómetros bajo exhaustiva lluvia y frío, añoraba a su querido cachorro.</p>\n".
				"<p>".ICU::format('{0,number}',array($pi))."</p>\n".
				"<p>".ICU::format('{0,number,currency}',array($money))."</p>",
			'Translated properly to Spanish',
			'Spanish translation mangled: '.$out
		);

		$this->set('sub','sub6.htm');
		$this->set('LANGUAGE','de-DE');
		$out=$this->render('basic/layout.htm');
		// PHP 5.3.2 inserts a line feed at end of translation
		$this->expect(
			$out==
				"<h3>Ich liebe Fat-Free!</h3>\n".
				"<p>Heute ist ".ICU::format('{0,date}',array(time()))."</p>\n".
				"<p>Im finsteren Jagdschloß am offenen Felsquellwasser patzte der affig-flatterhafte kauzig-höfliche Bäcker über seinem versifften kniffligen Xylophon.</p>\n".
				"<p>".ICU::format('{0,number}',array($pi))."</p>\n".
				"<p>".ICU::format('{0,number,currency}',array($money))."</p>",
			'Translated properly to German',
			'German translation mangled: '.$out
		);

		$this->set('LANGUAGE','en');

		$this->set('benchmark',
			array_fill(1,100,
				array(
					'a'=>1,'b'=>2,'c'=>3,'d'=>4,'e'=>5,
					'f'=>6,'g'=>7,'h'=>8,'i'=>9,'j'=>10
				)
			)
		);
		$time=microtime(TRUE);
		$this->render('basic/benchmark.htm');
		$elapsed=round(microtime(TRUE)-$time,3);
		$this->expect(
			$elapsed<0.05,
			'Template containing '.(count($this->get('benchmark'))*10).
				'+ HTML elements/calculations rendered in '.$elapsed.' seconds',
			'Template rendering too slow on this server: '.$elapsed.' seconds'
		);

		echo $this->render('basic/results.htm');
	}

	function template() {

		$this->set('title','Template Engine');

		$this->set('CACHE',TRUE);

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$this->set('a',123);
		$this->set('b','{{@a}}');
		$this->expect(
			Template::resolve('{{@b}}')=='123',
			'Template token substituted; string value returned',
			'Token substition failed: '.Template::resolve('{{@b}}')
		);
		$this->expect(
			Template::resolve('{{@a}}')=='123',
			'Template engine confirms substitution',
			'Template engine failed: '.Template::resolve('{{@a}}')
		);

		$this->set('a',345);
		$this->expect(
			Template::resolve('{{@a}}')=='345',
			'Template engine confirms replacement',
			'Template engine failed: '.Template::resolve('{{@a}}')
		);

		$this->expect(
			Template::resolve('{{@a+1}}')=='346',
			'Mixed expression correct',
			'Mixed expression failed: '.Template::resolve('{{@a+1}}')
		);

		$this->expect(
			Template::resolve('{{@a + 1}}')=='346',
			'Mixed expression (with whitespaces) correct',
			'Mixed expression (with whitespaces) failed: '.Template::resolve('{{@a + 1}}')
		);

		$this->set('x','{{123}}');
		$this->expect(
			Template::resolve('{{@x}}')=='123',
			'Integer constant in template expression correct',
			'Template expression is wrong: '.Template::resolve('{{@x}}')
		);

		$this->set('i','hello');
		$this->set('j','there');

		$this->expect(
			Template::resolve('{{@i.@j}}')=='hellothere',
			'String concatenation works',
			'String concatenation problem: '.Template::resolve('{{@i.@j}}')
		);

		$this->expect(
			Template::resolve('{{@i. @j}}')=='hellothere',
			'String concatenation (with whitespaces) works',
			'String concatenation (with whitespaces) problem: '.
				Template::resolve('{{@i. @j}}')
		);

		$this->expect(
			Template::resolve('{{@i .@j}}')=='hellothere',
			'Variation in string concatenation (with whitespaces) works',
			'Variation in string concatenation (with whitespaces) problem: '.
				Template::resolve('{{@i .@j}}')
		);

		$this->expect(
			Template::resolve('{{  @i  .  @j  }}')=='hellothere',
			'Liberal amounts of whitespaces produces the correct result',
			'Liberal amounts of whitespaces produces strange result: '.
				Template::resolve('{{  @i  .  @j  }}')
		);

		$this->set('x','{{345+5}}');
		$this->expect(
			Template::resolve('{{@x}}')=='350',
			'Arithmetic expression in template expression evaluated',
			'Arithmetic expression is wrong: '.Template::resolve('{{@x}}')
		);

		$this->set('x','{{1+0.23e-4}}');
		$this->expect(
			Template::resolve('{{@x}}')=='1.000023',
			'Negative exponential float in template expression correct',
			'Negative exponential float is wrong: '.Template::resolve('{{@x}}')
		);

		$this->set('x','{{1+0.23e+4}}');
		$this->expect(
			Template::resolve('{{@x}}')=='2301',
			'Positive exponential float in template expression correct',
			'Positive exponential float is wrong: '.Template::resolve('{{@x}}')
		);

		$this->set('x','{{1+0.23e4}}');
		$this->expect(
			Template::resolve('{{@x}}')=='2301',
			'Unsigned exponential float in template expression correct',
			'Unsigned exponential float is wrong: '.Template::resolve('{{@x}}')
		);

		$this->set('x','{{456+7.5}}');
		$this->expect(
			Template::resolve('{{@x}}')=='463.5',
			'Integer + float in template expression correct',
			'Integer + float is wrong: '.Template::resolve('{{@x}}')
		);

		$this->set('x','{{(1+2)*3}}');
		$this->expect(
			Template::resolve('{{@x}}')=='9',
			'Parenthesized arithmetic expression evaluated',
			'Parenthesized expression is wrong: '.Template::resolve('{{@x}}')
		);

		$this->expect(
			Template::resolve('{{(@a+1)*2}}')=='692',
			'Variable + arithmetic expression evaluated',
			'Variable + arithmetic expression is wrong: '.
				Template::resolve('{{(@a+1)*2}}')
		);

		$this->set('x','{{(intval(1+2.25))*3}}');
		$this->expect(
			Template::resolve('{{@x}}')=='9',
			'Allowed function and nested parentheses evaluated',
			'Allowed function/parentheses failed: '.Template::resolve('{{@x}}')
		);

		$this->set('x','{{(round(234.567,1)+(-1)+1)*2}}');
		$this->expect(
			Template::resolve('{{@x}}')=='469.2',
			'Function with multiple arguments evaluated',
			'Function with multiple arguments failed: '.Template::resolve('{{@x}}')
		);

		$this->set('x',NULL);
		$this->expect(
			Template::resolve('{{@x}}')=='',
			'NULL converted to empty string',
			'NULL not converted to empty string: '.Template::resolve('{{@x}}')
		);

		$this->set('x','{{array()}}');
		$this->expect(
			Template::resolve('{{@x}}')=='',
			'Empty array converted to empty string',
			'Array conversion failed: '.Template::resolve('{{@x}}')
		);

		$this->set('x','{{array(1,2,3)}}');
		$this->expect(
			Template::resolve('{{@x}}')=='Array',
			'Array converted to string \'Array\'',
			'Array conversion failed: '.Template::resolve('{{@x}}')
		);

		$this->set('x','{{NULL}}');
		$this->expect(
			Template::resolve('{{@x}}')=='',
			'NULL value evaluated',
			'Incorrect NULL evaluation: '.Template::resolve('{{@x}}')
		);

		$this->set('x','{{null}}');
		$this->expect(
			Template::resolve('{{@x}}')=='',
			'NULL value evaluated (case-insensitive)',
			'Incorrect NULL evaluation: '.Template::resolve('{{@x}}')
		);

		$this->set('x','{{TRUE}}');
		$this->expect(
			Template::resolve('{{@x}}')=='1',
			'Boolean TRUE expression evaluated',
			'Incorrect boolean evaluation: '.Template::resolve('{{@x}}')
		);

		$this->set('x','{{FALSE}}');
		$this->expect(
			Template::resolve('{{@x}}')=='',
			'Boolean FALSE expression converted to empty string',
			'Incorrect boolean evaluation: '.Template::resolve('{{@x}}')
		);

		$this->set('x','{{0}}');
		$this->expect(
			Template::resolve('{{@x}}')=='0',
			'Zero remains as-is',
			'Incorrect evaluation of integer zero: '.Template::resolve('{{@x}}')
		);

		$this->set('x','{{a@b.com}}');
		$this->expect(
			Template::resolve('{{@x}}')=='\'a@b.com\'',
			'E-mail address preserved',
			'Incorrect interpretation of e-mail address: '.Template::resolve('{{@x}}')
		);

		$this->set('x','{{new CustomObj}}');
		$this->expect(
			Template::resolve('{{@x}}')=='\'new CustomObj\'',
			'Object instantiation using template engine prohibited',
			'Object instantiation issue: '.Template::resolve('{{@x}}')
		);

		$this->set('x',new CustomObj);
		$this->expect(
			Template::resolve('{{@x}}')=='CustomObj',
			'Object converted to string',
			'Object conversion failed: '.Template::resolve('{{@x}}')
		);

		$this->set('func',
			function($x) {
				return 123;
			}
		);
		$this->expect(
			Template::resolve('{{@func("hello")}}')==123,
			'Variable containing anonymous function interpreted correctly',
			'Template misunderstood variable containing anonymous function: '.
				Template::resolve('{{@func("hello")}}')
		);

		$z=new stdClass;
		$z->a=123;
		$z->b=345;
		$this->set('var',$z);
		$this->expect(
			Template::resolve('{{@var->a}}')==123 &&
			Template::resolve('{{@var->b}}')==345,
			'Variable containing an object interpreted correctly',
			'Template misunderstood variable containing an object/properties: '.
				Template::resolve('{{@var->a}}')
		);

		$z->c=function() {
			return 'foo';
		};
		$this->expect(
			Template::resolve('{{@var->c()}}')=='foo',
			'Variable containing an anonymous function rendered properly',
			'Variable containing an anonymous function evaluated wrong: '.
				Template::resolve('{{@var->c()}}')
		);

		$this->set('z.x','good idea');
		$this->expect(
			Template::resolve('{{@z.x}}')=='good idea',
			'Array element evaluated',
			'Array element failed: '.Template::resolve('{{@z.x}}')
		);

		$this->expect(
			Template::resolve('{{@z.y}}')=='',
			'Non-existent array element converted to empty string',
			'Non-existent element failed: '.Template::resolve('{{@z.y}}')
		);

		$this->set('q',' indeed');
		$this->expect(
			Template::resolve('{{@z.@q}}')=='Array indeed',
			'Concatenation of array and string produces expected result',
			'Illegal concatenation: '.Template::resolve('{{@z.@q}}')
		);

		$this->expect(
			Template::resolve('{{@z.x.@q}}')=='good idea indeed',
			'Concatenation of array element and string correct',
			'Incorrect concatenation: '.Template::resolve('{{@z.x.@q}}')
		);

		$this->set('my_plans',array('test'=>1,'plan'=>array('city_name'=>2)));
		$this->expect(
			Template::resolve('{{@my_plans[plan][city_name]}}')==2,
			'Got the right value of a deeply-nested array element',
			'Incorrect evaluation of a deeply-nested array element'
		);

		$this->expect(
			Template::resolve('{{@x.@q}}')=='CustomObj indeed',
			'Mixing object and string produces expected result',
			'Illegal concatenation: '.Template::resolve('{{@x.@q}}')
		);

		$out=Template::serve('template/layout.htm');
		$this->expect(
			$out==="",
			'Subtemplate not defined - none inserted',
			'Subtemplate insertion issue: '.$out
		);

		$this->set('sub','sub1.htm');
		$this->set('test','<i>italics</i>');
		$out=Template::serve('template/layout.htm');
		$this->expect(
			$out=="<i>italics</i>",
			'HTML special characters retained',
			'Problem with HTML insertion: '.$out
		);

		$this->set('sub','sub1.htm');
		$this->set('test','&copy;');
		$out=Template::serve('template/layout.htm');
		$this->expect(
			$out=="&copy;",
			'HTML entity inserted: '.Template::serve('template/layout.htm'),
			'Problem with HTML insertion: '.$out
		);

		$this->set('sub','sub1.htm');
		$this->set('test','אני יכול לאכול זכוכית וזה לא מזיק לי.');
		$out=Template::serve('template/layout.htm');
		$this->expect(
			Template::serve('template/layout.htm')=="אני יכול לאכול זכוכית וזה לא מזיק לי.",
			'UTF-8 character set rendered correctly: '.$out,
			'UTF-8 issue: '.$out
		);

		$this->set('sub','sub1.htm');
		$this->set('test','I&nbsp;am&nbsp;here.');
		$out=Template::serve('template/layout.htm');
		$this->expect(
			Template::serve('template/layout.htm')=="I&nbsp;am&nbsp;here.",
			'HTML entities preserved: '.$out,
			'HTML entities converted: '.$out
		);

		$this->set('sub','sub2.htm');
		$this->set('src','/test/image');
		$this->set('alt',htmlspecialchars('this is "the" life'));
		$out=Template::serve('template/layout.htm');
		$this->expect(
			$out==
				'<img src="/test/image" alt="this is &quot;the&quot; life"/>',
			'Double-quotes inside HTML attributes converted to XML entities',
			'Problem with double-quotes inside HTML attributes: '.$out
		);

		$this->set('sub','sub3.htm');
		$out=Template::serve('template/layout.htm');
		$this->clear('div');
		$this->expect(
			$out=='',
			'Undefined array renders empty output',
			'Output not empty: '.$out
		);

		$this->set('sub','sub3.htm');
		$out=Template::serve('template/layout.htm');
		$this->set('div',NULL);
		$this->expect(
			$out=='',
			'NULL used as group attribute renders empty output',
			'Output not empty: '.$out
		);

		$this->set('sub','sub3.htm');
		$out=Template::serve('template/layout.htm');
		$this->set('div',array());
		$this->expect(
			$out=='',
			'Empty array used as group attribute renders empty output',
			'Output not empty: '.$out
		);

		$this->set('sub','sub3.htm');
		$this->set('div',
			array(
				'coffee'=>array('arabica','barako','liberica','kopiluwak'),
				'tea'=>array('darjeeling','pekoe','samovar')
			)
		);
		$out=Template::serve('template/layout.htm');
		$this->expect(
			preg_match(
				'#'.
				'<div>\s+'.
				'<p><span><b>coffee</b></span></p>\s+'.
				'<p>\s+'.
				'<span>arabica</span>\s+'.
				'<span>barako</span>\s+'.
				'<span>liberica</span>\s+'.
				'<span>kopiluwak</span>\s+'.
				'</p>\s+'.
				'</div>\s+'.
				'<div>\s+'.
				'<p><span><b>tea</b></span></p>\s+'.
				'<p>\s+'.
				'<span>darjeeling</span>\s+'.
				'<span>pekoe</span>\s+'.
				'<span>samovar</span>\s+'.
				'</p>\s+'.
				'</div>'.
				'#s',
				$out
			),
			'Subtemplate inserted; nested repeat directives rendered correctly',
			'Template rendering issue: '.$out
		);

		$this->set('sub','sub4.htm');
		$this->set('group',array('world','me','others'));
		$out=Template::serve('template/layout.htm');
		$this->expect(
			preg_match(
				'#'.
				'<script type="text/javascript">\s*'.
				'function hello\(\) {\s*'.
				'alert\(\'Javascript works\'\);\s*'.
				'}\s*'.
				'</script>\s*'.
				'<script type="text/javascript">alert\(unescape\("%3Cscript src=\'" \+ gaJsHost \+ "google-analytics\.com/ga\.js\' type=\'text/javascript\'%3E%3C/script%3E"\)\);</script>\s'.
				'world,\s+me,\s+others,\s+'.
				'#s',
				$out
			),
			'Javascript preserved',
			'Javascript mangled: '.htmlentities($out)
		);

		$this->set('sub','sub5.htm');
		$this->set('cond1',FALSE);
		$this->set('cond3',FALSE);
		$out=trim(Template::serve('template/layout.htm'));
		$this->expect(
			$out=='c1:F,c3:F',
			'Conditional directives evaluated correctly: FALSE, FALSE',
			'Incorrect evaluation of conditional directives: '.$out
		);
		$this->set('cond1',FALSE);
		$this->set('cond3',TRUE);
		$out=trim(Template::serve('template/layout.htm'));
		$this->expect(
			$out=='c1:F,c3:T',
			'Conditional directives evaluated correctly: FALSE, TRUE',
			'Incorrect evaluation of conditional directives: '.$out
		);
		$this->set('cond1',TRUE);
		$this->set('cond2',FALSE);
		$out=trim(Template::serve('template/layout.htm'));
		$this->expect(
			$out=='c1:T,c2:F',
			'Conditional directives evaluated correctly: TRUE, FALSE',
			'Incorrect evaluation of conditional directives: '.$out
		);
		$this->set('cond1',TRUE);
		$this->set('cond2',TRUE);
		$out=trim(Template::serve('template/layout.htm'));
		$this->expect(
			$out=='c1:T,c2:T',
			'Conditional directives evaluated correctly: TRUE, TRUE',
			'Incorrect evaluation of conditional directives: '.$out
		);

		$this->set('pi_val',$pi=3.141592654);
		$money=63950.25;

		$this->set('sub','sub6.htm');
		$this->set('LANGUAGE','en');
		$out=trim(Template::serve('template/layout.htm'));
		// PHP 5.3.2 inserts a line feed at end of translation
		$this->expect(
			$out==
				"<h3>I love Fat-Free!</h3>\n".
				"<p>Today is ".ICU::format('{0,date}',array(time()))."</p>\n".
				"<p>The quick brown fox jumps over the lazy dog.</p>\n".
				"<p>".ICU::format('{0,number}',array($pi))."</p>\n".
				"<p>".ICU::format('{0,number,currency}',array($money))."</p>",
			'English locale (i18n)',
			'English locale mangled: '.$out
		);

		$this->set('sub','sub6.htm');
		$this->set('LANGUAGE','fr-FR');
		$out=trim(Template::serve('template/layout.htm'));
		// PHP 5.3.2 inserts a line feed at end of translation
		$this->expect(
			$out==
				"<h3>J'aime Fat-Free!</h3>\n".
				"<p>Aujourd'hui, c'est ".ICU::format('{0,date}',array(time()))."</p>\n".
				"<p>Les naïfs ægithales hâtifs pondant à Noël où il gèle sont sûrs d'être déçus et de voir leurs drôles d'œufs abîmés.</p>\n".
				"<p>".ICU::format('{0,number}',array($pi))."</p>\n".
				"<p>".ICU::format('{0,number,currency}',array($money))."</p>",
			'Translated properly to French',
			'French translation mangled: '.$out
		);

		$this->set('sub','sub6.htm');
		$this->set('LANGUAGE','es-AR');
		$out=trim(Template::serve('template/layout.htm'));
		// PHP 5.3.2 inserts a line feed at end of translation
		$this->expect(
			$out==
				"<h3>Me encanta Fat-Free!</h3>\n".
				"<p>Hoy es ".ICU::format('{0,date}',array(time()))."</p>\n".
				"<p>El pingüino Wenceslao hizo kilómetros bajo exhaustiva lluvia y frío, añoraba a su querido cachorro.</p>\n".
				"<p>".ICU::format('{0,number}',array($pi))."</p>\n".
				"<p>".ICU::format('{0,number,currency}',array($money))."</p>",
			'Translated properly to Spanish',
			'Spanish translation mangled: '.$out
		);

		$this->set('sub','sub6.htm');
		$this->set('LANGUAGE','de-DE');
		$out=trim(Template::serve('template/layout.htm'));
		// PHP 5.3.2 inserts a line feed at end of translation
		$this->expect(
			$out==
				"<h3>Ich liebe Fat-Free!</h3>\n".
				"<p>Heute ist ".ICU::format('{0,date}',array(time()))."</p>\n".
				"<p>Im finsteren Jagdschloß am offenen Felsquellwasser patzte der affig-flatterhafte kauzig-höfliche Bäcker über seinem versifften kniffligen Xylophon.</p>\n".
				"<p>".ICU::format('{0,number}',array($pi))."</p>\n".
				"<p>".ICU::format('{0,number,currency}',array($money))."</p>",
			'Translated properly to German',
			'German translation mangled: '.$out
		);

		$this->set('LANGUAGE','en');

		$this->set('sub','sub7.htm');
		$this->set('array',array('a'=>'apple','b'=>'blueberry','c'=>'cherry'));
		$this->set('element','b');
		$out=trim(Template::serve('template/layout.htm'));
		$this->expect(
			$out=='blueberry',
			'Array with variable element rendered correctly',
			'Array variable element failed to render: '.var_export($out,TRUE)
		);

		$this->set('sub','sub8.htm');
		$this->set('func',
			function($arg1,$arg2) {
				return 'hello, '.$arg1.' '.$arg2;
			}
		);
		$this->set('arg1','wise');
		$this->set('arg2','guy');
		$out=trim(Template::serve('template/layout.htm'));
		$this->expect(
			$out=='hello, wise guy',
			'Function with variable arguments rendered correctly',
			'Array with variable arguments failed to render: '.var_export($out,TRUE)
		);

		$this->set('benchmark',
			array_fill(1,100,
				array(
					'a'=>1,'b'=>2,'c'=>3,'d'=>4,'e'=>5,
					'f'=>6,'g'=>7,'h'=>8,'i'=>9,'j'=>10
				)
			)
		);
		$time=microtime(TRUE);
		Template::serve('template/benchmark.htm');
		$elapsed=round(microtime(TRUE)-$time,3);
		$this->expect(
			$elapsed<0.05,
			'Template containing '.(count($this->get('benchmark'))*10).
				'+ HTML elements/calculations rendered in '.$elapsed.' seconds',
			'Template rendering too slow on this server: '.$elapsed.' seconds'
		);

		echo $this->render('basic/results.htm');
	}

	function axon() {

		$this->set('title','SQL/Axon');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$this->set('DB',new DB('sqlite::memory:'));
		$this->expect(
			extension_loaded('pdo_sqlite'),
			'SQLite PDO available',
			'SQLite PDO is not active - unable to continue'
		);

		if (extension_loaded('pdo_sqlite')) {
			DB::sql(
				array(
					'DROP TABLE IF EXISTS products;',
					'CREATE TABLE products ('.
						'item INTEGER,'.
						'description VARCHAR(255),'.
						'quantity INTEGER,'.
						'PRIMARY KEY (item)'.
					');'
				)
			);
			$product=new Axon('products');
			$this->expect(
				is_object($product),
				'Axon created',
				'Unable to instantiate Axon'
			);
			unset($product);

			$product=Axon::instance('products');
			$this->expect(
				is_a($product,'Axon'),
				'Axon instance created',
				'Unable to instantiate Axon'
			);
			unset($product);

			$product=new axon('products');
			$this->expect(
				is_object($product),
				'Axon created (case-insensitive)',
				'Unable to instantiate Axon (case-insensitive)'
			);

			$this->expect(
				$product->dry(),
				'Axon in dry state',
				'Axon is in hydrated state'
			);

			$product->item=111;
			$product->description='Coca Cola';
			$product->quantity=3;

			$this->expect(
				!$product->dry(),
				'Axon hydrated manually',
				'Axon should be hydrated by now'
			);

			$product->save();
			$this->expect(
				!$product->dry(),
				'Axon expected to remain hydrated',
				'Axon should be dry'
			);
			// MySQL always reports an _id of 0 if primary key
			// is not an auto-increment field
			$this->expect(
				$product->_id,
				'Last insert ID available; SQLite returns '.
					$product->_id,
				'No last insert ID available'
			);

			$product->load(array('item=:item',array(':item'=>111)));
			$this->expect(
				$product->item==111 &&
				$product->description=='Coca Cola' &&
				$product->quantity==3,
				'Auto-hydration succeeded (SQLite converts numbers to strings)',
				'Auto-hydration failed'
			);

			$result=$product->findOne(array('item=:item',array(':item'=>111)));
			$this->expect(
				$result->item==111 &&
				$result->description=='Coca Cola' &&
				$result->quantity==3,
				'findOne returned the correct record',
				'findOne return value is incorrect'
			);

			$result=$product->find(array('item=:item',array(':item'=>111)));
			$this->expect(
				get_class($result[0])=='Axon' &&
				$result[0]->item==111 &&
				$result[0]->description=='Coca Cola' &&
				$result[0]->quantity==3,
				'find returned an array of Axon objects',
				'find return type is incorrect'
			);

			$product->quantity++;
			$product->save();
			$product->load(array('item=:item',array(':item'=>111)));
			$this->expect(
				$product->item==111 &&
				$product->description=='Coca Cola' &&
				$product->quantity==4,
				'Axon saved - database update succeeded',
				'Database update failed'
			);

			$product->copyTo('POST');
			$this->expect(
				$this->get('POST.item')==111 &&
				$this->get('POST.description')=='Coca Cola' &&
				$this->get('POST.quantity')==4,
				'Axon properties copied to framework variable',
				'Unable to copy Axon properties to framework variable'
			);

			$_POST['description']='Pepsi';
			$product->copyFrom('POST');
			$this->expect(
				$product->item==111 &&
				$product->description=='Pepsi' &&
				$product->quantity==4,
				'Axon properties populated by framework variable',
				'Unable to fill Axon properties with contents of framework variable'
			);

			$this->set('POST.item',999);
			$this->set('POST.description','Pepsi');
			$this->set('POST.quantity',11);
			$product->copyFrom('POST','item|quantity');
			$this->expect(
				$product->item==999 &&
				$product->description=='Pepsi' &&
				$product->quantity==11,
				'Axon properties populated by selected fields in framework variable',
				'Unable to fill Axon properties with contents of framework variable'
			);

			$product->reset();
			$this->expect(
				$product->dry(),
				'Axon reset completed',
				'Axon should be dry'
			);

			$product->item=222;
			$product->description='Mobile Phone';
			$product->quantity=9;

			$this->expect(
				!$product->dry(),
				'Axon rehydrated manually',
				'Axon should hydrated by now'
			);

			$product->save();
			$this->expect(
				!$product->dry(),
				'Axon expected to remain hydrated',
				'Axon should not be dry'
			);

			$product->load('item=111');
			$this->expect(
				$product->item==111 &&
				$product->description=='Coca Cola' &&
				$product->quantity==4,
				'First record still there',
				'First record is missing'
			);

			$product->load('item=222');
			$this->expect(
				$product->item==222 &&
				$product->description=='Mobile Phone' &&
				$product->quantity==9,
				'Second record found',
				'Second record is missing'
			);

			$product->def('total','SUM(quantity)');
			$this->expect(
				$product->isdef('total')===TRUE,
				'Virtual field created',
				'Problem creating virtual field'
			);

			$product->load();
			$this->expect(
				$product->total==13,
				'Computed value of aggregate value using a virtual field works',
				'Virtual field implementation faulty'
			);

			$product->undef('total');
			$this->expect(
				$product->isdef('total')===FALSE,
				'Virtual field destroyed',
				'Problem destroying virtual field'
			);

			$product->load('item=111');
			$product->erase();
			$product->load('item=111');
			$this->expect(
				$product->dry(),
				'First record deleted',
				'First record still exists'
			);

			$product->load('item=222');
			$this->expect(
				$product->item==222 &&
				$product->description=='Mobile Phone' &&
				$product->quantity==9,
				'Second record still there',
				'Second record is missing'
			);

			$product->reset();
			$product->item=111;
			$product->description='Lots of dough';
			$product->quantity=666;
			$product->save();
			$product->load('quantity>0');
			$this->expect(
				$product->found()==2,
				'New record added - multirecord criteria specified for loading',
				'New record was not added'
			);

			$product->skip(1);
			$this->expect(
				!$product->dry(),
				'One more record expected to be retrieved',
				'Axon is dry'
			);

			$this->expect(
				$product->item==222 &&
				$product->description=='Mobile Phone' &&
				$product->quantity==9,
				'Forward navigation',
				'Forward navigation failed'
			);

			$product->skip(-1);
			$this->expect(
				$product->item==111 &&
				$product->description=='Lots of dough' &&
				$product->quantity==666,
				'Backward navigation',
				'Backward navigation failed'
			);

			$product->skip(-1);
			$this->expect(
				$product->dry(),
				'Axon is dry when navigating before the start of the record set',
				'Navigation failure'
			);

			$this->set('QUIET',TRUE);
			$product->skip(-1);
			$this->expect(
				!is_null($this->get('ERROR')),
				'Navigating past dry state triggers an error',
				'Navigation error handling issue'
			);
			$this->set('QUIET',FALSE);
			$this->clear('ERROR');

			$product->load('quantity>0');
			$product->skip(2);
			$this->expect(
				$product->dry(),
				'Axon is dry when navigating beyond the end of the record set',
				'Navigation failure'
			);

			$this->set('QUIET',TRUE);
			$product->skip();
			$this->expect(
				!is_null($this->get('ERROR')),
				'Navigating past dry state triggers an error',
				'Navigation error handling issue'
			);
			$this->set('QUIET',FALSE);
			$this->clear('ERROR');

			$db=$this->get('DB');
			$result=$db->exec(
				'SELECT * FROM products WHERE item=:item',
				array(':item'=>111)
			);
			$this->expect(
				$result[0]['item']==111 &&
				$result[0]['description']=='Lots of dough' &&
				$result[0]['quantity']==666,
				'Late-binding of parameters to values in SQL statements',
				'Late-binding issue encountered'
			);

			$product->load('item=111');
			$product->description='quoted "string"';
			$product->save();
			$result=$product->findOne('item=111');
			$this->expect(
				$result->description=='quoted "string"',
				'Double-quoted strings are left untouched',
				'Double-quoted strings altered'
			);

		}

		echo $this->render('basic/results.htm');
	}

	function jig() {

		$this->set('title','Jig Mapper');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		@unlink('db/products');

		$this->set('DB',new FileDB('db/',FileDB::FORMAT_GZip));

		$product=new Jig('products');
		$this->expect(
			is_object($product),
			'Jig created',
			'Unable to instantiate Jig'
		);
		unset($product);

		$product=Jig::instance('products');
		$this->expect(
			is_a($product,'Jig'),
			'Jig instance created',
			'Unable to instantiate Jig'
		);
		unset($product);

		$product=new Jig('products');
		$this->expect(
			is_object($product),
			'Jig created (case-insensitive)',
			'Unable to instantiate Jig (case-insensitive)'
		);

		$this->expect(
			$product->dry(),
			'Jig in dry state',
			'Jig is in hydrated state'
		);

		$product->item=111;
		$product->description='Coca Cola';
		$product->quantity=3;

		$this->expect(
			!$product->dry(),
			'Jig hydrated manually',
			'Jig should be hydrated by now'
		);

		$product->save();
		$this->expect(
			!$product->dry(),
			'Jig expected to remain hydrated',
			'Jig should be dry'
		);

		$this->expect(
			$product->_id,
			'Last insert ID available: '.$product->_id,
			'No last insert ID available'
		);

		$product->load(array('item'=>111));
		$this->expect(
			$product->item==111 &&
			$product->description=='Coca Cola' &&
			$product->quantity==3,
			'Auto-hydration succeeded',
			'Auto-hydration failed'
		);

		$result=$product->findOne(array('item'=>111));
		$this->expect(
			$result->item==111 &&
			$result->description=='Coca Cola' &&
			$result->quantity==3,
			'findOne returned the correct record',
			'findOne return value is incorrect'
		);

		$result=$product->find(array('item'=>111));
		$this->expect(
			get_class($result[0])=='Jig' &&
			$result[0]->item==111 &&
			$result[0]->description=='Coca Cola' &&
			$result[0]->quantity==3,
			'find returned an array of Jig objects',
			'find return type is incorrect'
		);

		$product->quantity++;
		$product->save();
		$product->load(array('item'=>111));
		$this->expect(
			$product->item==111 &&
			$product->description=='Coca Cola' &&
			$product->quantity==4,
			'Jig saved - database update succeeded',
			'Database update failed'
		);

		$product->copyTo('POST');
		$this->expect(
			$this->get('POST.item')==111 &&
			$this->get('POST.description')=='Coca Cola' &&
			$this->get('POST.quantity')==4,
			'Jig properties copied to framework variable',
			'Unable to copy Jig properties to framework variable'
		);

		$_POST['description']='Pepsi';
		$product->copyFrom('POST');
		$this->expect(
			$product->item==111 &&
			$product->description=='Pepsi' &&
			$product->quantity==4,
			'Jig properties populated by framework variable',
			'Unable to fill Jig properties with contents of framework variable'
		);

		$this->set('POST.item',999);
		$this->set('POST.description','Pepsi');
		$this->set('POST.quantity',11);
		$product->copyFrom('POST','item|quantity');
		$this->expect(
			$product->item==999 &&
			$product->description=='Pepsi' &&
			$product->quantity==11,
			'Jig properties populated by selected fields in framework variable',
			'Unable to fill Jig properties with contents of framework variable'
		);

		$product->reset();
		$this->expect(
			$product->dry(),
			'Jig reset completed',
			'Jig should be dry'
		);

		$product->item=222;
		$product->description='Mobile Phone';
		$product->quantity=9;

		$this->expect(
			!$product->dry(),
			'Jig rehydrated manually',
			'Jig should hydrated by now'
		);

		$product->save();
		$this->expect(
			!$product->dry(),
			'Jig expected to remain hydrated',
			'Jig should not be dry'
		);

		$product->load(array('item'=>111));
		$this->expect(
			$product->item==111 &&
			$product->description=='Coca Cola' &&
			$product->quantity==4,
			'First record still there',
			'First record is missing'
		);

		$product->load(array('item'=>222));
		$this->expect(
			$product->item==222 &&
			$product->description=='Mobile Phone' &&
			$product->quantity==9,
			'Second record found',
			'Second record is missing'
		);

		$product->load(array('item'=>111));
		$product->erase();
		$product->load(array('item'=>111));
		$this->expect(
			$product->dry(),
			'First record deleted',
			'First record still exists'
		);

		$product->load(array('item'=>222));
		$this->expect(
			$product->item==222 &&
			$product->description=='Mobile Phone' &&
			$product->quantity==9,
			'Second record still there',
			'Second record is missing'
		);

		$product->reset();
		$product->item=111;
		$product->description='Lots of dough';
		$product->quantity=666;
		$product->save();
		$product->load(array('quantity'=>array('gt'=>0)));
		$this->expect(
			$product->found()==2,
			'New record added - multirecord criteria specified for loading',
			'New record was not added'
		);

		$product->skip(1);
		$this->expect(
			!$product->dry(),
			'One more record expected to be retrieved',
			'Jig is dry'
		);

		$this->expect(
			$product->item==111 &&
			$product->description=='Lots of dough' &&
			$product->quantity==666,
			'Backward navigation',
			'Backward navigation failed'
		);

		$product->skip(-1);
		$this->expect(
			$product->item==222 &&
			$product->description=='Mobile Phone' &&
			$product->quantity==9,
			'Forward navigation',
			'Forward navigation failed'
		);

		$product->skip(-1);
		$this->expect(
			$product->dry(),
			'Jig is dry when navigating before the start of the record set',
			'Navigation failure'
		);

		$this->set('QUIET',TRUE);
		$product->skip(-1);
		$this->expect(
			!is_null($this->get('ERROR')),
			'Navigating past dry state triggers an error',
			'Navigation error handling issue'
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$product->load(array('quantity'=>array('gt'=>0)));
		$product->skip(2);
		$this->expect(
			$product->dry(),
			'Jig is dry when navigating beyond the end of the record set',
			'Navigation failure'
		);

		$this->set('QUIET',TRUE);
		$product->skip();
		$this->expect(
			!is_null($this->get('ERROR')),
			'Navigating past dry state triggers an error',
			'Navigation error handling issue'
		);
		$this->set('QUIET',FALSE);
		$this->clear('ERROR');

		$product->load(array('item'=>111));
		$product->description='quoted "string"';
		$product->save();
		$result=$product->findOne(array('item'=>111));
		$this->expect(
			$result->description=='quoted "string"',
			'Double-quoted strings are left untouched',
			'Double-quoted strings altered'
		);

		$this->get('DB')->convert(FileDB::FORMAT_Serialized);

		echo $this->render('basic/results.htm');
	}

	function m2() {
		$this->set('title','M2 Mapper');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$this->expect(
			extension_loaded('mongo'),
			'MongoDB extension available',
			'MongoDB extension is not active - unable to continue'
		);

		if (extension_loaded('mongo')) {

			$this->set('QUIET',TRUE);
			try {
				$db=new MongoDB(new Mongo('mongodb://localhost:27017'),'DB');
			}
			catch (Exception $x) {}
			$this->expect(
				isset($db),
				'MongoDB connection established',
				'MongoDB server is not active - unable to continue'
			);
			$this->set('QUIET',FALSE);
			$this->clear('ERROR');

			if (isset($db)) {
				$db->drop();

				$product=new M2('products',$db);
				$this->expect(
					is_object($product) && get_class($product)=='M2',
					'M2 created',
					'Unable to instantiate M2'
				);
				unset($product);

				$product=new M2('products',$db);
				$this->expect(
					is_object($product) && get_class($product)=='M2',
					'M2 created (case-insensitive)',
					'Unable to instantiate M2 (case-insensitive)'
				);

				$this->expect(
					$product->dry(),
					'M2 in dry state',
					'M2 is in hydrated state'
				);

				$product->item=111;
				$product->description='Coca Cola';
				$product->quantity=3;

				$this->expect(
					!$product->dry(),
					'M2 hydrated manually',
					'M2 should be hydrated by now'
				);

				$product->save();
				$this->expect(
					!$product->dry(),
					'M2 expected to remain hydrated',
					'M2 should be dry'
				);

				$this->expect(
					isset($product->_id),
					'MongoID assigned: '.$product->_id,
					'No MongoID assigned'
				);

				$id=$product->_id;
				$product->load(array('item'=>111));
				$this->expect(
					is_object($product->_id) && $product->_id==$id,
					'M2 MongoID is identical to saved collection object',
					'M2 MongoID sync issue'
				);

				$result=$product->findOne(array('item'=>111));
				$this->expect(
					$result->item==111 &&
					$result->description=='Coca Cola' &&
					$result->quantity==3,
					'findOne returned the correct collection object',
					'findOne return value is incorrect'
				);

				$result=$product->find(array('item'=>111));
				$this->expect(
					get_class($result[0])=='M2' &&
					$result[0]->item==111 &&
					$result[0]->description=='Coca Cola' &&
					$result[0]->quantity==3,
					'find returned an array of M2 objects',
					'find return type is incorrect'
				);

				$this->expect(
					$product->item===111 &&
					$product->description=='Coca Cola' &&
					$product->quantity===3,
					'Auto-hydration succeeded',
					'Auto-hydration failed'
				);

				$product->quantity++;
				$product->save();
				$product->load(array('item'=>111));
				$this->set('product',$product->findone());

				$this->expect(
					(is_object($product->_id)) && $product->_id==$id,
					'M2 MongoID is intact after object update: {{@product->_id}}',
					'M2 MongoID sync issue'
				);

				$this->expect(
					$product->item==111 &&
					$product->description=='Coca Cola' &&
					$product->quantity==4,
					'M2 saved - database update succeeded',
					'Database update failed'
				);

				$product->copyTo('POST');
				$this->expect(
					$this->get('POST.item')==111 &&
					$this->get('POST.description')=='Coca Cola' &&
					$this->get('POST.quantity')==4,
					'M2 properties copied to framework variable',
					'Unable to copy M2 properties to framework variable'
				);

				$_POST['description']='Pepsi';
				$product->copyFrom('POST');
				$this->expect(
					$product->item==111 &&
					$product->description=='Pepsi' &&
					$product->quantity==4,
					'M2 properties populated by framework variable',
					'Unable to fill M2 properties with contents of framework variable'
				);

				$this->set('POST.item',999);
				$this->set('POST.description','Pepsi');
				$this->set('POST.quantity',11);
				$product->copyFrom('POST','item|quantity');
				$this->expect(
					$product->item==999 &&
					$product->description=='Pepsi' &&
					$product->quantity==11,
					'M2 properties populated by selected fields in framework variable',
					'Unable to fill M2 properties with contents of framework variable'
				);

				$product->reset();
				$this->expect(
					$product->dry(),
					'M2 reset completed',
					'M2 should be dry'
				);

				$product->item=222;
				$product->description='Mobile Phone';
				$product->quantity=9;

				$this->expect(
					!$product->dry(),
					'M2 rehydrated manually',
					'M2 should hydrated by now'
				);

				$product->save();
				$this->expect(
					isset($product->_id),
					'MongoID for new object assigned: '.$product->_id,
					'M2 MongoID sync issue'
				);

				$this->expect(
					!$product->dry(),
					'M2 expected to remain hydrated',
					'M2 should not be dry'
				);

				$product->load(array('item'=>111));
				$this->expect(
					(is_object($product->_id)) && $product->_id==$id,
					'MongoID of first object correct: '.$product->_id,
					'M2 MongoID sync issue'
				);

				$this->expect(
					$product->item==111 &&
					$product->description=='Coca Cola' &&
					$product->quantity==4,
					'First record still there',
					'First record is missing'
				);

				$product->load(array('item'=>222));
				$this->expect(
					$product->item==222 &&
					$product->description=='Mobile Phone' &&
					$product->quantity==9,
					'Second record found',
					'Second record is missing'
				);

				$product->load(array('item'=>111));
				$product->erase();
				$product->load(array('item'=>111));
				$this->expect(
					$product->dry(),
					'First record deleted',
					'First record still exists'
				);

				$product->load(array('item'=>222));
				$this->expect(
					$product->item==222 &&
					$product->description=='Mobile Phone' &&
					$product->quantity==9,
					'Second record still there',
					'Second record is missing'
				);

				$product->reset();
				$product->item=111;
				$product->description='Lots of dough';
				$product->quantity=666;
				$product->save();
				$product->load(array('quantity'=>array('$gt'=>0)),array('item'=>TRUE));
				$this->expect(
					$product->found()==2,
					'New record added - multirecord criteria specified for loading',
					'New record was not added'
				);

				$product->skip(1);
				$this->expect(
					!$product->dry(),
					'One more record expected to be retrieved',
					'M2 is dry'
				);

				$this->expect(
					$product->item==222 &&
					$product->description=='Mobile Phone' &&
					$product->quantity==9,
					'Forward navigation',
					'Forward navigation failed'
				);

				$product->skip(-1);
				$this->expect(
					$product->item==111 &&
					$product->description=='Lots of dough' &&
					$product->quantity==666,
					'Backward navigation',
					'Backward navigation failed'
				);

				$product->skip(-1);
				$this->expect(
					$product->dry(),
					'M2 is dry when navigating before the start of the record set',
					'Navigation failure'
				);

				$this->set('QUIET',TRUE);
				$product->skip(-1);
				$this->expect(
					!is_null($this->get('ERROR')),
					'Navigating past dry state triggers an error',
					'Navigation error handling issue'
				);
				$this->set('QUIET',FALSE);
				$this->clear('ERROR');

				$product->load(array('quantity'=>array('$gt'=>0)),array('item'=>TRUE));
				$product->skip(2);
				$this->expect(
					$product->dry(),
					'M2 is dry when navigating beyond the end of the record set',
					'Navigation failure'
				);

				$this->set('QUIET',TRUE);
				$product->skip();
				$this->expect(
					!is_null($this->get('ERROR')),
					'Navigating past dry state triggers an error',
					'Navigation error handling issue'
				);
				$this->set('QUIET',FALSE);
				$this->clear('ERROR');
			}
		}

		echo $this->render('basic/results.htm');
	}

	function Unicode() {
		$this->set('title','Unicode');

		$str='אני יכול לאכול זכוכית וזה לא מזיק לי';

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$this->expect(
			UTF::strlen($str)==36,
			'String length evaluated correctly',
			'String length is wrong: '.UTF::strlen($str)
		);

		$this->expect(
			UTF::substr($str,0,5)=='אני י',
			'Substring at offset 0 (length 5) is correct',
			'Substring at offset 0 (length 5) is wrong: '.
				UTF::substr($str,0,5)
		);

		$this->expect(
			UTF::substr($str,12)=='ול זכוכית וזה לא מזיק לי',
			'Substring at offset 12 (unspecified length) is correct',
			'Substring at offset 12 (unspecified length) is wrong: '.
				UTF::substr($str,12)
		);

		$this->expect(
			UTF::substr($str,3,4)==' יכו',
			'Substring at offset 3 (length 4) is correct',
			'Substring at offset 3 (length 4) is wrong: '.
				UTF::substr($str,3,4)
		);

		$this->expect(
			UTF::substr('',7)==FALSE,
			'Substring of empty string returns FALSE',
			'Substring of empty string returns non-boolean value: '.
				UTF::substr('',0)
		);

		$this->expect(
			UTF::substr($str,-5)=='יק לי',
			'Substring at negative offset is correct',
			'Substring at negative offset is wrong: '.
				UTF::substr($str,-5)
		);

		$this->expect(
			UTF::strpos($str,'ת וזה')==20,
			'String position detected accurately',
			'String position detected incorrectly'
		);

		$this->expect(
			UTF::strpos($str,'xyz')===FALSE,
			'Non-existent substring returns FALSE',
			'Non-existent substring returns non-boolean value: '.
				UTF::strpos($str,'xyz')
		);

		$this->expect(
			UTF::strrpos($str,'כ')==18,
			'String position (right-to-left) detected accurately',
			'String position (right-to-left) detected incorrectly'
		);

		$this->expect(
			UTF::strstr($str,'לא מ',TRUE)=='אני יכול לאכול זכוכית וזה ',
			'Substring search returns correct value',
			'Substring search failed: '.UTF::strstr($str,'לא מ',TRUE)
		);

		$this->expect(
			UTF::strstr($str,'לא מזי')=='לא מזיק לי',
			'Substring search returns correct latent value',
			'Substring search failed: '.UTF::strstr($str,'לא מ')
		);

		$this->expect(
			UTF::substr_count($str,'כו')==3,
			'Substring count is correct',
			'Substring count is incorrect'
		);

		echo $this->render('basic/results.htm');
	}

	function graphics() {
		$this->set('title','Graphics');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$this->expect(
			extension_loaded('gd'),
			'GD extension loaded',
			'GD extension not available - unable to continue'
		);

		if (extension_loaded('gd')) {

			$this->expect(
				TRUE,
				$this->render('captcha.htm')
			);

			$icons=array();
			for ($j=0;$j<9;$j++)
				$icons[]=array('md5'=>md5(mt_rand(1,getrandmax())),'size'=>24+($j*6));
			$this->set('icons',$icons);
			$this->expect(
				TRUE,
				$this->render('identicon.htm')
			);

			$this->expect(
				TRUE,
				$this->render('invert.htm')
			);

			$this->expect(
				TRUE,
				$this->render('thumb.htm')
			);

			$this->expect(
				TRUE,
				$this->render('screenshot.htm')
			);

		}

		echo $this->render('basic/results.htm');
	}

	function geo() {
		$this->set('title','Geo');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$this->expect(
			Geo::$countries,
			'Geo plugin returns '.count(Geo::$countries).' countries',
			'Geo plugin failed to retrieve countries'
		);

		$tz=Geo::timezones();
		$this->expect(
			count($tz),
			'Geo plugin returns an array of '.count($tz).' timezones',
			'Geo plugin failed to retrieve timezones'
		);

		while (TRUE) {
			$loc=array_rand($tz);
			if (is_int(strpos($loc,'/')) && is_bool(strpos($loc,'Etc')))
				break;
		}
		$this->expect(
			$tz[$loc],
			$loc.' timezone returns UTC'.
				($tz[$loc]['offset']?'+':'').$tz[$loc]['offset'].' offset',
			'UTC offset not available'
		);

		$this->set('QUIET',TRUE);
		$weather=Geo::weather($tz[$loc]['latitude'],$tz[$loc]['longitude']);
		$this->set('QUIET',FALSE);
		$this->expect(
			$weather,
			'GeoNames weather report: Temperature at '.
				$weather['stationName'].' is '.$weather['temperature'].' degrees',
			'Weather report for above location is not available'
		);
		$this->clear('ERROR');

		$this->set('QUIET',TRUE);
		$location=Geo::location();
		$this->set('QUIET',FALSE);
		$this->expect(
			$location,
			'Detected GeoLocation is '.
				$location['city'].', '.$location['countryName'],
			'GeoLocation is not available'
		);
		$this->clear('ERROR');

		echo $this->render('basic/results.htm');
	}

	function google() {
		$this->set('title','Google');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$this->expect(
			extension_loaded('sockets'),
			'Sockets extension available',
			'Sockets extension is not active - unable to continue'
		);

		if (extension_loaded('sockets')) {

			$this->set('QUIET',TRUE);
			$this->expect(
				Google::translate('I am hungry','en','es')=='Tengo hambre',
				'Text translated from English to Spanish by Google',
				'Google translation failure'
			);
			$this->set('QUIET',FALSE);

			$this->expect(
				TRUE,
				'Google map<br/><img src="/google/map" alt="Google Map"/>'
			);

			$search=Google::search('google');
			$this->set('QUIET',TRUE);
			$this->expect(
				is_array($search),
				'Google search generated the following results:<br/>'.
					implode('<br/>',$this->pick($search['results'],'url')),
				'Google search failure: '.var_export($search,TRUE)
			);
			$this->set('QUIET',FALSE);

			$geocode=Google::geocode('1600 Amphitheatre Pkwy, Mountain View, CA 94043, USA');
			$this->set('QUIET',TRUE);
			$this->expect(
				is_array($geocode),
				'Geocode API call success',
				'Geocode API call failure: '.var_export($geocode,TRUE)
			);
			$this->set('QUIET',FALSE);

		}

		echo $this->render('basic/results.htm');
	}

	function web() {
		$this->set('title','Web Tools');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$this->expect(
			extension_loaded('sockets'),
			'Sockets extension available',
			'Sockets extension is not active - unable to continue'
		);

		if (extension_loaded('sockets')) {

			$text='Ñõw is the tîme~for all good mên. to cóme! to the aid 0f-thëir_côuntry';
			$this->expect(
				Web::slug($text)=='now-is-the-time-for-all-good-men-to-come-to-the-aid-0f-their_country',
				'Framework generates correct URL-friendly version of string',
				'Incorrect URL-friendly string conversion: '.Web::slug($text)
			);

			$this->set('QUIET',TRUE);
			$text=Web::http('GET http://'.$_SERVER['HTTP_HOST'].'/minified/reset.css');
			$this->expect(
				$text=='html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,article,aside,canvas,details,embed,figure,figcaption,footer,header,hgroup,menu,nav,output,ruby,section,summary,time,mark,audio,video{margin:0;padding:0;border:0;font-size:100%;font:inherit;vertical-align:baseline;}article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section{display:block;}body{line-height:1;}ol,ul{list-style:none;}blockquote,q{quotes:none;}blockquote:before,blockquote:after,q:before,q:after{content:\'\';content:none;}table{border-collapse:collapse;border-spacing:0;}',
				'CSS minified '.round(100*(filesize('gui/reset.css')-strlen($text))/filesize('gui/reset.css'),1).'%: '.strlen($text).' bytes; '.
					'original size: '.filesize('gui/reset.css').' bytes',
				'CSS minification issue: '.var_export($text,true)
			);
			$this->set('QUIET',FALSE);

			$this->set('QUIET',TRUE);
			$text=Web::http('GET http://'.$_SERVER['HTTP_HOST'].'/minified/simple.css');
			$this->expect(
				$text=='div *{text-align:center;}#content{border:1px #000 solid;text-shadow:#ccc -1px -1px 0px;}',
				'CSS minified properly - necessary (and IE-problematic) spaces preserved',
				'CSS minified incorrectly: '.var_export($text,true)
			);
			$this->set('QUIET',FALSE);

			$this->set('QUIET',TRUE);
			$text=Web::http('GET http://'.$_SERVER['HTTP_HOST'].'/minified/cookie.js');
			$this->expect(
				$text=='function getCookie(c_name){if(document.cookie.length>0){c_start=document.cookie.indexOf(c_name+"=");if(c_start!=-1){c_start=c_start+c_name.length+1;c_end=document.cookie.indexOf(";",c_start);if(c_end==-1)c_end=document.cookie.length
return unescape(document.cookie.substring(c_start,c_end));}}return""}function setCookie(c_name,value,expiredays){var exdate=new Date();exdate.setDate(exdate.getDate()+expiredays);document.cookie=c_name+"="+escape(value)+((expiredays==null)?"":"; expires="+exdate.toUTCString());}function checkCookie(){username=getCookie(\'username\');if(username!=null&&username!=""){alert(\'Welcome again \'+username+\'!\');}else{username=prompt(\'Please enter your name:\',"");if(username!=null&&username!=""){setCookie(\'username\',username,365);}}}',
				'Javascript minified '.round(100*(filesize('gui/cookie.js')-strlen($text))/filesize('gui/cookie.js'),1). '%: '.strlen($text).' bytes; '.
					'original size: '.filesize('gui/cookie.js').' bytes',
				'Javascript minification issue: '.var_export($text,true)
			);
			$this->set('QUIET',FALSE);

			echo $this->render('basic/results.htm');
		}
	}

	function network() {
		$this->set('title','Network');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$this->expect(
			$this->privateip('127.0.0.59'),
			'Localhost identified correctly',
			'Localhost detection failed'
		);

		$this->expect(
			$this->privateip('192.168.0.5'),
			'Private Network (192.168.0.5) identified correctly',
			'Private Network (192.168.0.5) detection failed'
		);

		$this->expect(
			$this->privateip('172.16.3.2'),
			'Private Network (172.16.3.2) identified correctly',
			'Private Network (172.16.3.2) detection failed'
		);

		$this->expect(
			$this->privateip('10.10.10.10'),
			'Private Network (10.10.10.10) identified correctly',
			'Private Network (10.10.10.10) detection failed'
		);

		$this->expect(
			!$this->privateip('4.2.2.1'),
			'Public Network (4.2.2.1) identified correctly',
			'Public Network (4.2.2.1) detection failed'
		);

		$this->expect(
			!$this->privateip('169.254.222.111'),
			'Public Network (169.254.222.111) identified correctly',
			'Public Network (169.254.222.111) detection failed'
		);

		for ($i=0;$i<3;$i++) {
			$ping=Net::ping('www.yahoo.com',TRUE,1);
			$this->expect(
				!is_bool($ping),
				'Ping round-trip time to/from www.yahoo.com: '.
					round($ping['avg']).'ms',
				'No ping reply from www.yahoo.com'
			);
		}

		$this->set('QUIET',TRUE);
		$ping=Net::ping('www.yay.cc',TRUE,1);
		$this->expect(
			is_bool($ping),
			'www.yay.cc: No ping reply expected',
			'Huh? Ping reply from www.yay.cc'
		);
		$this->set('QUIET',FALSE);

		$this->expect(
			$result=Net::whois('sourceforge.net'),
			'Received response from whois server: <pre>'.$result.'</pre>',
			'No response from whois server'
		);

		/*
		$path=Net::traceroute('58.71.0.158',TRUE);
		$this->expect(
			is_array($path),
			'Traceroute initiated',
			'Traceroute failed'
		);
		if (is_array($path))
			foreach ($path as $node)
				if (is_bool($node))
					$this->expect(
						TRUE,
						'Traceroute hop skipped'
					);
				else
					$this->expect(
						TRUE,
						'Traceroute '.$node['host'].' '.
							'min: '.$node['min'].'ms '.
							'avg: '.$node['avg'].'ms '.
							'max: '.$node['max'].'ms '.
							'packets: '.$node['packets']
					);
		*/
		echo $this->render('basic/results.htm');
	}

	function misc() {
		$this->set('title','Miscellaneous');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$str=Code::snake('helloWorld');
		$this->expect(
			$str=='hello_world',
			'Snakecase conversion is correct: '.$str,
			'Snakecase conversion failed: '.$str
		);

		$str=Code::camel($str);
		$this->expect(
			$str=='helloWorld',
			'camelCase conversion is correct: '.$str,
			'camelCase conversion failed: '.$str
		);

		$old=trim(file_get_contents('inc/diff1.txt'));
		$new=trim(file_get_contents('inc/diff2.txt'));
		foreach (
			array(
				'character-based comparison'=>'',
				'space as delimiter'=>' ',
				'line-by-line comparison'=>"\n"
			) as $desc=>$delim) {
			$diff=Text::diff($old,$new,$delim);

			$build=Text::patch($old,$diff['patch'],$delim);
			$this->expect(
				$build==$new,
				'Patch worked ('.$desc.')',
				'Patch failed: <pre>'.$build.'</pre>'
			);

			$build=Text::patch($new,$diff['patch'],$delim,TRUE);
			$this->expect(
				$build==$old,
				'Reverse patch applied correctly',
				'Reverse patch failed: <pre>'.$build.'</pre>'
			);
		}

		echo $this->render('basic/results.htm');
	}

	function openid() {
		$this->set('title','OpenID');

		$openid=new OpenID;
		$openid->identity='https://www.google.com/accounts/o8/id';
		$openid->return_to=$this->get('PROTOCOL').'://'.
			$_SERVER['SERVER_NAME'].'/openid2';

		$this->expect(
			$openid->auth(),
			'* This should not be displayed *',
			'OpenID account failed authentication'
		);

		echo $this->render('basic/results.htm');
	}

	function openid2() {
		$this->set('title','OpenID');

		$this->expect(
			is_null($this->get('ERROR')),
			'No errors expected at this point',
			'ERROR variable is set: '.$this->get('ERROR.text')
		);

		$openid=new OpenID;
		$this->expect(
			$openid->verified(),
			'OpenID account verified: '.$openid->identity,
			'OpenID account failed verification: '.$openid->identity
		);

		echo $this->render('basic/results.htm');
	}

}
