<?php

namespace App;

class Basket extends Controller {

	function get($f3) {
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$basket=new \Basket;
		$test->expect(
			is_object($basket),
			'Cursor instantiated'
		);
		$basket->load('item','chicken wings');
		if ($resume=$basket->count())
			$test->message('Shopping resumed');
		else
			$test->message('Basket is empty');
		$basket->set('item','chicken wings');
		$basket->set('quantity',3);
		$basket->set('price',0.68);
		$basket->set('measure','pound');
		$basket->save();
		$test->expect(
			$basket->get('item')=='chicken wings' &&
			$basket->get('quantity')==3 &&
			$basket->get('price')==0.68 &&
			$basket->get('measure')=='pound',
			'Item saved ['.$basket->get('_id').']'
		);
		$basket->load('item','port wine');
		if (!$resume)
			$test->expect(
				$basket->dry(),
				'Current basket item is empty/undefined'
			);
		$basket->set('item','port wine');
		$basket->set('quantity',1);
		$basket->set('price',8.65);
		$basket->set('measure','bottle');
		$basket->save();
		$test->expect(
			$basket->get('item')=='port wine' &&
			$basket->get('quantity')==1 &&
			$basket->get('price')==8.65 &&
			$basket->get('measure')=='bottle',
			'Item added ['.$basket->get('_id').']'
		);
		$basket->load('item','chicken wings');
		$basket->set('quantity',2);
		$basket->save();
		$basket->load('item','chicken wings');
		$test->expect(
			$basket->get('item')=='chicken wings' &&
			$basket->get('quantity')==2 &&
			$basket->get('price')==0.68 &&
			$basket->get('measure')=='pound',
			'First item updated  ['.$basket->get('_id').']'
		);
		$basket->load('item','blue cheese');
		$basket->set('item','blue cheese');
		$basket->set('quantity',1);
		$basket->set('price',7.50);
		$basket->set('measure','12oz');
		$basket->save();
		$test->expect(
			$basket->get('item')=='blue cheese' &&
			$basket->get('quantity')==1 &&
			$basket->get('price')==7.50 &&
			$basket->get('measure')=='12oz',
			'Another item added ['.$basket->get('_id').']'
		);
		$basket->erase('item','port wine');
		$test->expect(
			$basket->get('_id'),
			'Current item survives'
		);
		$basket->copyto('foo');
		$test->expect(
			$f3->get('foo.item')=='blue cheese' &&
			$f3->get('foo.quantity')==1 &&
			$f3->get('foo.price')==7.50 &&
			$f3->get('foo.measure')=='12oz',
			'Current item copied to hive variable'
		);
		$test->expect(
			$basket->count()==2,
			'Count items in basket'
		);
		$test->expect(
			array_values($basket->checkout())==array(
				array(
					'item'=>'chicken wings',
					'quantity'=>2,
					'price'=>0.68,
					'measure'=>'pound'
				),
				array(
					'item'=>'blue cheese',
					'quantity'=>1,
					'price'=>7.5,
					'measure'=>'12oz'
				)
			),
			'Check out'
		);
		$basket->item='Chocolate cake';
		$test->expect(
			$basket->item=='Chocolate cake',
			'Magic access'
		);
		$basket->drop();
		$f3->set('results',$test->results());
	}

}
