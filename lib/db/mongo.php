<?php

/*
	Copyright (c) 2009-2012 F3::Factory/Bong Cosca, All rights reserved.

	This file is part of the Fat-Free Framework (http://fatfree.sf.net).

	THE SOFTWARE AND DOCUMENTATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF
	ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR
	PURPOSE.

	Please see the license.txt file for more information.
*/

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
