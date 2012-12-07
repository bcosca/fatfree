<?php

namespace App;

class Jig extends Controller {

	function get() {
		$f3=\Base::instance();
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$db=new \DB\Jig('tmp/jig/',\DB\Jig::FORMAT_JSON);
		$db->drop();
		$test->expect(
			is_object($db),
			'DB wrapper initialized'
		);
		$movie=new \DB\Jig\Mapper($db,'movies');
		$test->expect(
			is_object($movie),
			'Mapper instantiated'
		);
		$movie->set('title','Donnie Brasco');
		$movie->set('director','Mike Newell');
		$movie->set('year',1997);
		$movie->save();
		$movie->load(array('@title==?','Donnie Brasco'));
		$test->expect(
			$movie->count()==1 &&
			$movie->get('title')=='Donnie Brasco' &&
			$movie->get('director')=='Mike Newell' &&
			$movie->get('year')==1997,
			'Record loaded'
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
				'@title==? && @director==?',
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
				'@title==? AND @director==?',
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
				'@title==:title && @director==:director',
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
				'@title==:title && @director==:director',
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
			$movie->count()==2,
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
		$obj=$movie->findone(array('@title==?','Zodiac'));
		$class=get_class($obj);
		$test->expect(
			$class=='DB\Jig\Mapper' &&
			$obj->get('title')=='Zodiac' &&
			$obj->get('director')=='David Fincher' &&
			$obj->get('year')==2007,
			'Object returned by findone: '.$class
		);
		$session=new \DB\Jig\Session($db);
		$test->expect(
			session_start(),
			'Database-managed session started'
		);
		$_SESSION['foo']='hello world';
		session_commit();
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
		unset($_COOKIE[session_name()]);
		$test->expect(
			!isset($_SESSION['foo']),
			'Session destroyed'
		);
		$f3->set('results',$test->results());
	}

}
