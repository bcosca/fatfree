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
				$db->drop();
				$movie=new \DB\Mongo\Mapper($db,'movies');
				$test->expect(
					is_object($movie),
					'Mapper instantiated'
				);
				$movie->set('title','Donnie Brasco');
				$movie->set('director','Mike Newell');
				$movie->set('year',1997);
				$movie->save();
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
					!$movie->next(),
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
				$array=$movie->afindone(array('title'=>'Zodiac'));
				$test->expect(
					$array['title']=='Zodiac' &&
					$array['director']=='David Fincher' &&
					$array['year']==2007,
					'Associative array returned by afindone()'
				);
				$session=new \DB\Mongo\Session($db);
				$test->expect(
					session_start(),
					'Database-managed session started'
				);
				$_SESSION['foo']='hello world';
				session_commit();
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
				session_unset();
				$_SESSION=array();
				$test->expect(
					empty($_SESSION['foo']),
					'Session cleared'
				);
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
					empty($_SESSION['foo']),
					'Session destroyed'
				);
			}
		}
		$f3->set('results',$test->results());
	}

}
