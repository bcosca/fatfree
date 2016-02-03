<?php

namespace App;

class Helper extends \F3\Prefab {

	function pick($val,$match) {
		return preg_grep('/'.$match.'/',$val);
	}

}