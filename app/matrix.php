<?php

namespace App;

class Matrix extends Controller {

	function get($f3) {
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$array=array(
			array('id'=>123,'name'=>'paul','sales'=>0.35),
			array('id'=>456,'name'=>'ringo','sales'=>0.13),
			array('id'=>345,'name'=>'george','sales'=>0.57),
			array('id'=>234,'name'=>'john','sales'=>0.79)
		);
		$matrix=\Matrix::instance();
		$matrix->sort($array,'name');
		$test->expect(
			$array==array(
				array('id'=>345,'name'=>'george','sales'=>0.57),
				array('id'=>234,'name'=>'john','sales'=>0.79),
				array('id'=>123,'name'=>'paul','sales'=>0.35),
				array('id'=>456,'name'=>'ringo','sales'=>0.13),
			),
			'Sort multi-dimensional array by specified column'
		);
		$matrix->sort($array,'sales');
		$test->expect(
			$array==array(
				array('id'=>456,'name'=>'ringo','sales'=>0.13),
				array('id'=>123,'name'=>'paul','sales'=>0.35),
				array('id'=>345,'name'=>'george','sales'=>0.57),
				array('id'=>234,'name'=>'john','sales'=>0.79)
			),
			'Sort multi-dimensional array on another column'
		);
		$test->expect(
			$matrix->pick($array,'name')==
				array('ringo','paul','george','john'),
			'Retrieve specified column'
		);
		$matrix->transpose($array);
		$test->expect(
			$array==array(
				'id'=>array(456,123,345,234),
				'name'=>array('ringo','paul','george','john'),
				'sales'=>array(0.13,0.35,0.57,0.79)
			),
			'Transpose matrix'
		);
		$matrix->changekey($array,'sales','percent');
		$test->expect(
			$array==array(
				'id'=>array(456,123,345,234),
				'name'=>array('ringo','paul','george','john'),
				'percent'=>array(0.13,0.35,0.57,0.79)
			),
			'Change row key'
		);
		$test->expect(
			$matrix->calendar('2001-09-11')==array(
				array(6=>1),
				array(0=>2,1=>3,2=>4,3=>5,4=>6,5=>7,6=>8),
				array(0=>9,1=>10,2=>11,3=>12,4=>13,5=>14,6=>15),
				array(0=>16,1=>17,2=>18,3=>19,4=>20,5=>21,6=>22),
				array(0=>23,1=>24,2=>25,3=>26,4=>27,5=>28,6=>29),
				array(0=>30)
			),
			'Generate calendar (Sunday, first day of week)'
		);
		$f3->set('results',$test->results());
	}

}
