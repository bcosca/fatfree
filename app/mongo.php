<?php

namespace App;

class Mongo extends Controller {

	function get($f3) {
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$test->expect(
			$loaded=extension_loaded('mongo'),
			'MongoDB extension enabled'
		);
		if ($loaded) {
			try {
				$db=new \DB\Mongo('mongodb://localhost:27017','test');
			}
			catch (\Exception $x) {
				$db=NULL;
			}
			$test->expect(
				$db,
				'DB wrapper initialized (Version '.\Mongo::VERSION.')'
			);
			if ($db) {
				$test->expect(
					$uuid=$db->uuid(),
					'UUID: '.$uuid
				);
				$movie=new \DB\Mongo\Mapper($db,'movies');
				$test->expect(
					$type=$movie->dbtype(),
					'DB type: '.$type
				);
				$test->expect(
					is_object($movie),
					'Mapper instantiated'
				);
				$movie->load(array('title'=>'The Hobbit'));
				$test->expect(
					$movie->dry(),
					'Mapper is dry'
				);
				$movie->set('title','Donnie Brasco');
				$movie->set('director','Mike Newell');
				$movie->set('year',1997);
				$movie->save();
				$movie->save(); // intentional
				$test->expect(
					$db->log(),
					'MongoDB profiler active'
				);
				$movie->load(array('title'=>'Donnie Brasco'));
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
				$movie->load();
				$test->expect(
					$movie->count()==2,
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
					!$movie->exists('title') &&
					!$movie->exists('director') &&
					!$movie->exists('year'),
					'Navigation beyond cursor limit'
				);
				$obj=$movie->findone(array('title'=>'Zodiac'));
				$class=get_class($obj);
				$test->expect(
					$class=='DB\Mongo\Mapper' &&
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
				$session=new \DB\Mongo\Session($db);
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
				$_SESSION=array();
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
					empty($_SESSION) && $session->count(array('session_id=?',$sid))==0 &&
					$before==$sid && $after=='deleted' && empty($_COOKIE[session_name()]),
					'Session destroyed and cookie expired'
				);
				$db->drop();
			}
		}
		$f3->set('results',$test->results());
	}

}
