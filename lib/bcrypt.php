<?php

/**
*	Copyright (c) 2009-2019 F3::Factory/Bong Cosca, All rights reserved.
*
*	This file is part of the Fat-Free Framework (http://fatfreeframework.com).
*
*	This is free software: you can redistribute it and/or modify it under the
*	terms of the GNU General Public License as published by the Free Software
*	Foundation, either version 3 of the License, or later.
*
*	Fat-Free Framework is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
*	General Public License for more details.
*
*	You should have received a copy of the GNU General Public License along
*	with Fat-Free Framework.  If not, see <http://www.gnu.org/licenses/>.
*
**/

/**
*	Lightweight password hashing library (PHP 5.5+ only)
*	@deprecated Use http://php.net/manual/en/ref.password.php instead
**/
class Bcrypt extends Prefab {

	//@{ Error messages
	const
		E_CostArg='Invalid cost parameter',
		E_SaltArg='Salt must be at least 22 alphanumeric characters';
	//@}

	//! Default cost
	const
		COST=10;

	/**
	*	Generate bcrypt hash of string
	*	@return string|FALSE
	*	@param $pw string
	*	@param $salt string
	*	@param $cost int
	**/
	function hash($pw,$salt=NULL,$cost=self::COST) {
		if ($cost<4 || $cost>31)
			user_error(self::E_CostArg,E_USER_ERROR);
		$len=22;
		if ($salt) {
			if (!preg_match('/^[[:alnum:]\.\/]{'.$len.',}$/',$salt))
				user_error(self::E_SaltArg,E_USER_ERROR);
		}
		else {
			$raw=16;
			$iv='';
			if (!$iv && extension_loaded('openssl'))
				$iv=openssl_random_pseudo_bytes($raw);
			if (!$iv)
				for ($i=0;$i<$raw;++$i)
					$iv.=chr(mt_rand(0,255));
			$salt=str_replace('+','.',base64_encode($iv));
		}
		$salt=substr($salt,0,$len);
		$hash=crypt($pw,sprintf('$2y$%02d$',$cost).$salt);
		return strlen($hash)>13?$hash:FALSE;
	}

	/**
	*	Check if password is still strong enough
	*	@return bool
	*	@param $hash string
	*	@param $cost int
	**/
	function needs_rehash($hash,$cost=self::COST) {
		list($pwcost)=sscanf($hash,"$2y$%d$");
		return $pwcost<$cost;
	}

	/**
	*	Verify password against hash using timing attack resistant approach
	*	@return bool
	*	@param $pw string
	*	@param $hash string
	**/
	function verify($pw,$hash) {
		$val=crypt($pw,$hash);
		$len=strlen($val);
		if ($len!=strlen($hash) || $len<14)
			return FALSE;
		$out=0;
		for ($i=0;$i<$len;++$i)
			$out|=(ord($val[$i])^ord($hash[$i]));
		return $out===0;
	}

}
