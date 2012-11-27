<?php

namespace DB;

//! MongoDB wrapper
class Mongo extends \MongoDB {

	/**
		Instantiate class
		@param $dsn string
		@param $dbname string
		@param $options array
	**/
	function __construct($dsn,$dbname,array $options=NULL) {
		parent::__construct(new \Mongo($dsn,$options?:array()),$dbname);
	}

}
