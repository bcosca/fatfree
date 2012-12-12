<?php

namespace App;

class SQL extends Controller {

	function get() {
		$f3=\Base::instance();
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
			//$db=new \DB\SQL('mysql:host=localhost');
			$engine=$db->driver();
			$test->expect(
				is_object($db),
				'DB wrapper initialized ('.$engine.' driver)'
			);
			if ($engine=='mysql') {
				$db->exec(
					array(
						'DROP DATABASE IF EXISTS test;',
						'CREATE DATABASE test DEFAULT CHARSET=utf8;'
					)
				);
				unset($db);
				$db=new \DB\SQL(
					'mysql:host=localhost;dbname=test');
			}
			$db->exec(
				array(
					'DROP TABLE IF EXISTS movies;',
					'CREATE TABLE movies ('.
						'title VARCHAR(255) NOT NULL PRIMARY KEY,'.
						'director VARCHAR(255),'.
						'year INTEGER'.
					');'
				)
			);
			$test->expect(
				$db->log(),
				'SQL log available'
			);
			$db->exec(
				'INSERT INTO movies (title,director,year) '.
				'VALUES ("Reservoir Dogs","Quentin Tarantino",1992);'
			);
			$db->begin();
			$db->exec(
				array (
					'INSERT INTO movies (title,director,year) '.
					'VALUES ("Fight Club","David Fincher",1999);',
					'DELETE FROM movies WHERE title="Reservoir Dogs";'
				)
			);
			$db->rollback();
			$test->expect(
				$db->exec('SELECT * FROM movies;')==
				array(
					array(
						'title'=>'Reservoir Dogs',
						'director'=>'Quentin Tarantino',
						'year'=>1992
					)
				),
				'Manual rollback'
			);
			$db->begin();
			$db->exec(
				array (
					'INSERT INTO movies (title,director,year) '.
					'VALUES ("Fight Club","David Fincher",1999);',
					'DELETE FROM movies WHERE title="Reservoir Dogs";'
				)
			);
			$db->commit();
			$test->expect(
				$db->exec('SELECT * FROM movies;')==
				array(
					array(
						'title'=>'Fight Club',
						'director'=>'David Fincher',
						'year'=>1999
					)
				),
				'Manual commit'
			);
			$db->exec(
				array (
					'INSERT INTO movies (title,director,year) '.
					'VALUES ("Donnie Brasco","Mike Newell",1997);',
					'DELETE FROM movies WHERE title="Fight Club";'
				)
			);
			$test->expect(
				$db->exec('SELECT * FROM movies;')==
				array(
					array(
						'title'=>'Donnie Brasco',
						'director'=>'Mike Newell',
						'year'=>1997
					)
				),
				'Auto-commit'
			);
			@$db->exec(
				'INSERT INTO movies (title,director,year) '.
				'VALUES ("Donnie Brasco","Mike Newell",1997);'
			);
			$test->expect(
				$db->exec('SELECT * FROM movies;')==
				array(
					array(
						'title'=>'Donnie Brasco',
						'director'=>'Mike Newell',
						'year'=>1997
					)
				),
				'Flag primary key violation'
			);
			$test->expect(
				$db->exec(
					'SELECT * FROM movies WHERE director=?;',
					'Mike Newell')==
				array(
					array(
						'title'=>'Donnie Brasco',
						'director'=>'Mike Newell',
						'year'=>1997
					)
				),
				'Parameterized query (positional)'
			);
			$test->expect(
				$db->exec('SELECT * FROM movies WHERE director=:name;',
					array(':name'=>'Mike Newell'))==
				array(
					array(
						'title'=>'Donnie Brasco',
						'director'=>'Mike Newell',
						'year'=>1997
					)
				),
				'Parameterized query (named)'
			);
			$test->expect(
				($schema=$db->schema('movies',60)) && count($schema)==3,
				'Schema retrieved'
			);
			$movie=new \DB\SQL\Mapper($db,'movies');
			$test->expect(
				is_object($movie),
				'Mapper instantiated'
			);
			$movie->load(array('title=?','Donnie Brasco'));
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
			$movie->load(
				array(
					'title=? AND director=?',
					'The River Murders',
					'Rich Cowan'
				)
			);
			$test->expect(
				$movie->get('title')=='The River Murders' &&
				$movie->get('director')=='Rich Cowan' &&
				$movie->get('year')==2011,
				'Parameterized query (positional)'
			);
			$movie->load(
				array(
					'title=? AND director=?',
					array(
						1=>'The River Murders',
						2=>'Rich Cowan'
					)
				)
			);
			$test->expect(
				$movie->get('title')=='The River Murders' &&
				$movie->get('director')=='Rich Cowan' &&
				$movie->get('year')==2011,
				'Parameterized query (alternative positional)'
			);
			$movie->load(
				array(
					'title=:title AND director=:director',
					':title'=>'The River Murders',
					':director'=>'Rich Cowan'
				)
			);
			$test->expect(
				$movie->get('title')=='The River Murders' &&
				$movie->get('director')=='Rich Cowan' &&
				$movie->get('year')==2011,
				'Parameterized query (named)'
			);
			$movie->load(
				array(
					'title=:title AND director=:director',
					array(
						':title'=>'The River Murders',
						':director'=>'Rich Cowan'
					)
				)
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
			$movie->skip();
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
			$movie->skip(-1);
			$test->expect(
				$movie->get('title')=='Donnie Brasco' &&
				$movie->get('director')=='Mike Newell' &&
				$movie->get('year')==1997,
				'Backward navigation'
			);
			$movie->skip();
			$test->expect(
				$movie->get('title')=='The River Murders' &&
				$movie->get('director')=='Rich Cowan' &&
				$movie->get('year')==2011,
				'Forward navigation'
			);
			$movie->set('title','Zodiac');
			$movie->set('director','David Fincher');
			$movie->set('year',2007);
			$movie->save();
			$movie->load();
			$movie->skip();
			$test->expect(
				$movie->get('title')=='Zodiac' &&
				$movie->get('director')=='David Fincher' &&
				$movie->get('year')==2007,
				'Record updated'
			);
			$movie->skip(-1);
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
				!$movie->skip(),
				'Navigation beyond cursor limit'
			);
			$obj=$movie->findone(array('title=?','Zodiac'));
			$class=get_class($obj);
			$test->expect(
				$class=='DB\SQL\Mapper' &&
				$obj->get('title')=='Zodiac' &&
				$obj->get('director')=='David Fincher' &&
				$obj->get('year')==2007,
				'Object returned by findone(): '.$class
			);
			$array=$movie->afindone(array('title=?','Zodiac'));
			$test->expect(
				$array['title']=='Zodiac' &&
				$array['director']=='David Fincher' &&
				$array['year']==2007,
				'Associative array returned by afindone()'
			);
			$db->exec(
				array(
					'DROP TABLE IF EXISTS tickets;',
					'CREATE TABLE tickets ('.
						'ticketno '.
						($engine=='mysql'?
							'INT AUTO_INCREMENT':
							'INTEGER').' NOT NULL PRIMARY KEY,'.
						'title VARCHAR(128) NOT NULL'.
					');'
				)
			);
			$ticket=new \DB\SQL\Mapper($db,'tickets');
			$ticket->set('title','The River Murders');
			$ticket->save();
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
			$ticket->set('adhoc','MIN(ticketno)');
			$test->expect(
				$ticket->exists('adhoc') && is_null($ticket->get('adhoc')),
				'Ad hoc field defined'
			);
			$ticket->load();
			$test->expect(
				($num=$ticket->get('adhoc'))==$first,
				'First auto-increment ID: '.$num
			);
			$ticket->set('adhoc','MAX(ticketno)');
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
				array('title'=>'admin\'; DELETE FROM tickets; SELECT \'1'));
			$ticket->copyfrom('GET');
			$ticket->save();
			$ticket->load(
				array('title=?','admin\'; DELETE FROM tickets; SELECT \'1'));
			$test->expect(
				!$ticket->dry(),
				'SQL injection-safe'
			);
			$db->exec('DROP TABLE IF EXISTS sessions;');
			$session=new \DB\SQL\Session($db);
			$test->expect(
				session_start(),
				'Database-managed session started'
			);
			$_SESSION['foo']='hello world';
			session_commit();
			$id=session_id();
			$test->expect(
				$ip=$session->ip($id),
				'IP address: '.$ip
			);
			$test->expect(
				$stamp=$session->stamp($id),
				'Timestamp: '.date('r',$stamp)
			);
			$test->expect(
				$agent=$session->agent($id),
				'User agent: '.$agent
			);
			session_unset();
			$_SESSION=array();
			$test->expect(
				!isset($_SESSION['foo']),
				'Session cleared'
			);
			session_commit();
			session_start();
			$test->expect(
				isset($_SESSION['foo']) && $_SESSION['foo']=='hello world',
				'Session variable retrieved from database'
			);
			session_unset();
			session_destroy();
			header_remove('Set-Cookie');
			unset($_COOKIE[session_name()]);
			$test->expect(
				!isset($_SESSION['foo']),
				'Session destroyed'
			);
		}
		$f3->set('results',$test->results());
	}

}
