<?php

namespace App;

class SQL extends Controller {

	function get($f3) {
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$test->expect(
			$loaded=extension_loaded('pdo_sqlite'),
			'PDO extension enabled'
		);
		if ($loaded) {
			if (!is_dir('tmp/'))
				mkdir('tmp/',\Base::MODE,TRUE);
			$db=new \DB\SQL('sqlite:tmp/sqlite.db');
			$db->exec(
				[
					'PRAGMA temp_store=MEMORY;',
					'PRAGMA journal_mode=MEMORY;',
					'PRAGMA foreign_keys=ON;'
				]
			);
			//$db=new \DB\SQL('mysql:host=localhost');
			$engine=$db->driver();
			$test->expect(
				is_object($db),
				'DB wrapper initialized ('.$engine.' '.$db->version().')'
			);
			$test->expect(
				$uuid=$db->uuid(),
				'UUID: '.$uuid
			);
			if ($engine=='mysql') {
				$db->exec(
					[
						'DROP DATABASE IF EXISTS '.$db->quotekey('test').';',
						'CREATE DATABASE '.$db->quotekey('test').
							' DEFAULT CHARSET=utf8;'
					]
				);
				unset($db);
				$db=new \DB\SQL(
					'mysql:host=localhost;dbname=test');
			}
			$db->exec(
				[
					'DROP TABLE IF EXISTS '.$db->quotekey('movies').';',
					'CREATE TABLE '.$db->quotekey('movies').' ('.
						$db->quotekey('title').
							' VARCHAR(255) NOT NULL PRIMARY KEY,'.
						$db->quotekey('director').' VARCHAR(255),'.
						$db->quotekey('year').' INTEGER'.
					');'
				]
			);
			$test->expect(
				$db->log(),
				'SQL profiler active'
			);
			$db->exec(
				'INSERT INTO '.$db->quotekey('movies').' ('.
					$db->quotekey('title').','.
					$db->quotekey('director').','.
					$db->quotekey('year').
				') '.
				'VALUES (\'Reservoir Dogs\',\'Quentin Tarantino\',1992);'
			);
			$db->begin();
			$db->exec(
				[
					'INSERT INTO '.$db->quotekey('movies').' ('.
						$db->quotekey('title').','.
						$db->quotekey('director').','.
						$db->quotekey('year').
					') '.
					'VALUES (\'Fight Club\',\'David Fincher\',1999);',
					'DELETE FROM '.$db->quotekey('movies').' WHERE '.
						$db->quotekey('title').'=\'Reservoir Dogs\';'
				]
			);
			$db->rollback();
			$test->expect(
				$db->exec('SELECT * FROM '.$db->quotekey('movies').';')==
				[
					[
						'title'=>'Reservoir Dogs',
						'director'=>'Quentin Tarantino',
						'year'=>1992
					]
				],
				'Manual rollback'
			);
			$db->begin();
			$db->exec(
				[
					'INSERT INTO '.$db->quotekey('movies').' ('.
						$db->quotekey('title').','.
						$db->quotekey('director').','.
						$db->quotekey('year').
					') '.
					'VALUES (\'Fight Club\',\'David Fincher\',1999);',
					'DELETE FROM '.$db->quotekey('movies').' WHERE '.
						$db->quotekey('title').'=\'Reservoir Dogs\';'
				]
			);
			$db->commit();
			$test->expect(
				$db->exec('SELECT * FROM '.$db->quotekey('movies').';')==
				[
					[
						'title'=>'Fight Club',
						'director'=>'David Fincher',
						'year'=>1999
					]
				],
				'Manual commit'
			);
			$db->exec(
				[
					'INSERT INTO '.$db->quotekey('movies').' ('.
						$db->quotekey('title').','.
						$db->quotekey('director').','.
						$db->quotekey('year').
					') '.
					'VALUES (\'Donnie Brasco\',\'Mike Newell\',1997);',
					'DELETE FROM '.$db->quotekey('movies').' WHERE '.
						$db->quotekey('title').'=\'Fight Club\';'
				]
			);
			$test->expect(
				$db->exec('SELECT * FROM '.$db->quotekey('movies').';')==
				[
					[
						'title'=>'Donnie Brasco',
						'director'=>'Mike Newell',
						'year'=>1997
					]
				],
				'Auto-commit'
			);
			@$db->exec(
					'INSERT INTO '.$db->quotekey('movies').' ('.
						$db->quotekey('title').','.
						$db->quotekey('director').','.
						$db->quotekey('year').
					') '.
				'VALUES (\'Donnie Brasco\',\'Mike Newell\',1997);'
			);
			$test->expect(
				$db->exec('SELECT * FROM '.$db->quotekey('movies').';')==
				[
					[
						'title'=>'Donnie Brasco',
						'director'=>'Mike Newell',
						'year'=>1997
					]
				],
				'Flag primary key violation'
			);
			$test->expect(
				$db->exec(
					'SELECT * FROM '.$db->quotekey('movies').' WHERE '.
						$db->quotekey('director').'=?;',
						[1=>'Mike Newell'])==
				[
					[
						'title'=>'Donnie Brasco',
						'director'=>'Mike Newell',
						'year'=>1997
					]
				],
				'Parameterized query (positional)'
			);
			$test->expect(
				$db->exec('SELECT * FROM '.$db->quotekey('movies').' WHERE '.
					$db->quotekey('director').'=:name;',
					[':name'=>'Mike Newell'])==
				[
					[
						'title'=>'Donnie Brasco',
						'director'=>'Mike Newell',
						'year'=>1997
					]
				],
				'Parameterized query (named)'
			);
			$test->expect(
				($schema=$db->schema('movies',NULL,60)) && count($schema)==3,
				'Schema retrieved'
			);
			$movie=new \DB\SQL\Mapper($db,'movies');
			$test->expect(
				$type=$movie->dbtype(),
				'DB type: '.$type
			);
			$test->expect(
				is_object($movie),
				'Mapper instantiated'
			);
			$movie->load([$db->quotekey('title').'=?','The Hobbit']);
			$test->expect(
				$movie->dry(),
				'Mapper is dry'
			);
			$movie->load([$db->quotekey('title').'=?','Donnie Brasco']);
			$test->expect(
				$movie->count()==1 &&
				$movie->get('title')=='Donnie Brasco' &&
				$movie->get('director')=='Mike Newell' &&
				$movie->get('year')==1997,
				'Record loaded'
			);
			$test->expect(
				$movie->title=='Donnie Brasco' &&
				$movie->director=='Mike Newell' &&
				$movie->year==1997,
				'Magic properties'
			);
			$movie->reset();
			$test->expect(
				$movie->dry(),
				'Mapper reset'
			);
			$movie->set('title','The River Murders');
			$movie->set('director','Rich Cowan');
			$movie->set('year',2011);
			$movie->save();
			$movie->save(); // intentional
			$movie->load(
				[
					$db->quotekey('title').'=? AND '.
					$db->quotekey('director').'=?',
					'The River Murders',
					'Rich Cowan'
				]
			);
			$test->expect(
				$movie->get('title')=='The River Murders' &&
				$movie->get('director')=='Rich Cowan' &&
				$movie->get('year')==2011,
				'Parameterized query (positional)'
			);
			$movie->load(
				[
					$db->quotekey('title').'=? AND '.
					$db->quotekey('director').'=?',
					[
						1=>'The River Murders',
						2=>'Rich Cowan'
					]
				]
			);
			$test->expect(
				$movie->get('title')=='The River Murders' &&
				$movie->get('director')=='Rich Cowan' &&
				$movie->get('year')==2011,
				'Parameterized query (alternative positional)'
			);
			$movie->load(
				[
					$db->quotekey('title').'=:title AND '.
					$db->quotekey('director').'=:director',
					':title'=>'The River Murders',
					':director'=>'Rich Cowan'
				]
			);
			$test->expect(
				$movie->get('title')=='The River Murders' &&
				$movie->get('director')=='Rich Cowan' &&
				$movie->get('year')==2011,
				'Parameterized query (named)'
			);
			$movie->load(
				[
					$db->quotekey('title').'=:title AND '.
					$db->quotekey('director').'=:director',
					[
						':title'=>'The River Murders',
						':director'=>'Rich Cowan'
					]
				]
			);
			$test->expect(
				$movie->get('title')=='The River Murders' &&
				$movie->get('director')=='Rich Cowan' &&
				$movie->get('year')==2011,
				'Parameterized query (alternative named)'
			);
			$movie->load();
			$test->expect(
				$db->count()==2 && $movie->count()==2,
				'Record count: '.$movie->count()
			);
			$page=$movie->paginate(0,1);
			$test->expect(
				$page['subset'][0]->get('title')=='Donnie Brasco' &&
				$page['subset'][0]->get('director')=='Mike Newell' &&
				$page['subset'][0]->get('year')==1997,
				'Pagination: first page'
			);
			$page=$movie->paginate(1,1);
			$test->expect(
				$page['subset'][0]->get('title')=='The River Murders' &&
				$page['subset'][0]->get('director')=='Rich Cowan' &&
				$page['subset'][0]->get('year')==2011,
				'Pagination: last page'
			);
			$movie->next();
			$cast=$movie->cast();
			$test->expect(
				$cast['title']=='The River Murders' &&
				$cast['director']=='Rich Cowan' &&
				$cast['year']==2011,
				'Cast mapper to ordinary array'
			);
			$test->expect(
				$movie->get('title')=='The River Murders' &&
				$movie->get('director')=='Rich Cowan' &&
				$movie->get('year')==2011,
				'New record saved'
			);
			$movie->prev();
			$test->expect(
				!$movie->dry(),
				'Hydrated'
			);
			$test->expect(
				$movie->get('title')=='Donnie Brasco' &&
				$movie->get('director')=='Mike Newell' &&
				$movie->get('year')==1997,
				'Backward navigation'
			);
			$movie->next();
			$test->expect(
				$movie->get('title')=='The River Murders' &&
				$movie->get('director')=='Rich Cowan' &&
				$movie->get('year')==2011,
				'Forward navigation'
			);
			$movie->set('title','Zodiac');
			$test->expect(
				$movie->changed('title') && !$movie->changed('director') && $movie->changed(),
				'Changed field'
			);
			$movie->set('director','David Fincher');
			$movie->set('year',2007);
			$movie->save();
			$movie->save(); // intentional
			$movie->load();
			$movie->next();
			$test->expect(
				$movie->get('title')=='Zodiac' &&
				$movie->get('director')=='David Fincher' &&
				$movie->get('year')==2007,
				'Record updated'
			);
			$movie->prev();
			$movie->erase();
			$movie->load();
			$test->expect(
				$movie->count()==1 &&
				$movie->get('title')=='Zodiac' &&
				$movie->get('director')=='David Fincher' &&
				$movie->get('year')==2007,
				'Record erased'
			);
			$movie->copyto('GET');
			$test->expect(
				$_GET['title']=='Zodiac' &&
				$_GET['director']=='David Fincher' &&
				$_GET['year']==2007,
				'Copy fields to hive key'
			);
			$_GET['year']=2008;
			$movie->copyfrom('GET');
			$test->expect(
				$movie->get('title')=='Zodiac' &&
				$movie->get('director')=='David Fincher' &&
				$movie->get('year')==2008,
				'Hydrate mapper from hive key'
			);
			$test->expect(
				!$movie->next() && $movie->dry() &&
				!$movie->get('title') &&
				!$movie->get('director') &&
				!$movie->get('year'),
				'Navigation beyond cursor limit'
			);
			$obj=$movie->findone([$db->quotekey('title').'=?','Zodiac']);
			$class=get_class($obj);
			$test->expect(
				$class=='DB\SQL\Mapper' &&
				$obj->get('title')=='Zodiac' &&
				$obj->get('director')=='David Fincher' &&
				$obj->get('year')==2007,
				'Object returned by findone(): '.$class
			);
			$test->expect(
				$obj['title']=='Zodiac' &&
				$obj['director']=='David Fincher' &&
				$obj['year']==2007,
				'Associative array access'
			);
			$test->expect(
				$out=$movie->required('title') &&
					!$movie->required('director'),
				'Required: '.$out
			);
			$movie->erase('');
			$test->expect(
				$movie->count()==0,
				'All records erased'	
			);
			switch ($engine) {
				case 'mysql':
					$inc='INT NOT NULL AUTO_INCREMENT';
					break;
				case 'pgsql':
					$inc='SERIAL';
					break;
				default:
					$inc='INTEGER NOT NULL';
					break;
			}
			$db->exec(
				[
					'DROP TABLE IF EXISTS '.$db->quotekey('tickets').';',
					'CREATE TABLE tickets ('.
						$db->quotekey('ticketno').' '.$inc.' PRIMARY KEY,'.
						$db->quotekey('title').' VARCHAR(128) NOT NULL,'.
						'FOREIGN KEY ('.$db->quotekey('title').') '.
						'REFERENCES movies ('.$db->quotekey('title').')'.
					');'
				]
			);
			$ticket=new \DB\SQL\Mapper($db,'tickets');
			$ticket->set('title','The River Murders');
			$ticket->save();
			$ticket->save(); // intentional
			$test->expect(
				($num=$ticket->get('ticketno')) && is_int($num),
				'New mapper instantiated; auto-increment: '.($first=$num)
			);
			$test->expect(
				($id=$ticket->get('_id'))==$num,
				'Virtual _id field: '.$id
			);
			$ticket->reset();
			$ticket->set('title','Zodiac');
			$ticket->save();
			$test->expect(
				$ticket->count()==2 &&
				($num=$ticket->get('ticketno')) && is_int($num),
				'Record added; primary key: '.($latest=$num)
			);
			$test->expect(
				($id=$ticket->get('_id'))==$num,
				'Virtual _id field: '.$id
			);
			$ticket->set('adhoc','MIN('.$db->quotekey('ticketno').')');
			$test->expect(
				$ticket->exists('adhoc') && is_null($ticket->get('adhoc')),
				'Ad hoc field defined'
			);
			$ticket->load();
			$test->expect(
				($num=$ticket->get('adhoc'))==$first,
				'First auto-increment ID: '.$num
			);
			$ticket->clear('adhoc');
			$ticket->set('adhoc','MAX('.$db->quotekey('ticketno').')');
			$ticket->load();
			$test->expect(
				($num=$ticket->get('adhoc'))==$latest,
				'Latest auto-increment ID: '.$num
			);
			$ticket->clear('adhoc');
			$test->expect(
				!$ticket->exists('adhoc'),
				'Ad hoc field destroyed'
			);
			$f3->set('GET',
				[
					'title'=>'admin\'; '.
					'DELETE FROM '.$db->quotekey('tickets').'; '.
					'SELECT \'1'
				]
			);
			$ticket->copyfrom('GET');
			$ticket->save();
			$ticket->load(
				[
					$db->quotekey('title').'=?',
					'admin\'; '.
					'DELETE FROM '.$db->quotekey('tickets').'; '.
					'SELECT \'1'
				]
			);
			$test->expect(
				!$ticket->dry(),
				'SQL injection-safe'
			);
			if ($engine!='pgsql') { // PostgreSQL not supported (yet)
				$db->exec(
					'DROP TABLE IF EXISTS '.$db->quotekey('sessions').';'
				);
				$session=new \DB\SQL\Session($db);
				$test->expect(
					$session->sid()===NULL,
					'Database-managed session instantiated but not started'
				);
				session_start();
				$test->expect(
					$sid=$session->sid(),
					'Database-managed session started: '.$sid
				);
				$f3->set('SESSION.foo','hello world');
				session_write_close();
				$test->expect(
					$session->sid()===NULL,
					'Database-managed session written and closed'
				);
				$_SESSION=[];
				$test->expect(
					$f3->get('SESSION.foo')=='hello world',
					'Session variable retrieved from database'
				);
				$test->expect(
					$ip=$session->ip(),
					'IP address: '.$ip
				);
				$test->expect(
					$stamp=$session->stamp(),
					'Timestamp: '.date('r',$stamp)
				);
				$test->expect(
					$agent=$session->agent(),
					'User agent: '.$agent
				);
				$test->expect(
					$csrf=$session->csrf(),
					'Anti-CSRF token: '.$csrf
				);
				$before=$after='';
				if (preg_match('/^Set-Cookie: '.session_name().'=(\w+)/m',
					implode(PHP_EOL,array_reverse(headers_list())),$m))
					$before=$m[1];
				$f3->clear('SESSION');
				if (preg_match('/^Set-Cookie: '.session_name().'=(\w+)/m',
					implode(PHP_EOL,array_reverse(headers_list())),$m))
					$after=$m[1];
				$test->expect(
					empty($_SESSION) && $session->count(['session_id=?',$sid])==0 &&
					$before==$sid && $after=='deleted' && empty($_COOKIE[session_name()]),
					'Session destroyed and cookie expired'
				);
			}
		}
		$f3->set('results',$test->results());
	}

}
