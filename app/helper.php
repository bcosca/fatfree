<?php

namespace App;

class Helper extends \Prefab {

	function pick($val,$match) {
		return preg_grep('/'.$match.'/',$val);
	}

}
