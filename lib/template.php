<?php

/**
	Template engine for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2012 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Template
		@version 2.1.0
**/

//! Template engine
class Template extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_Render='Template %s cannot be rendered';
	//@}

	private static
		//! Compiled template file
		$view,
		//! Local variables
		$hive,
		//! MIME format
		$mime,
		//! Template tags
		$tags='set|include|exclude|loop|repeat|check|true|false';

	/**
		Convert token to variable
			@return string
			@param $str string
	**/
	static function token($str) {
		return preg_replace('/(?<!\w)@(\w+)/','$\1',$str);
	}

	/**
		Template -set- tag handler
			@return string
			@param $node array
	**/
	static function _set(array $node) {
		$out='';
		foreach ($node['@attrib'] as $key=>$val)
			$out.='$'.$key.'='.
				(preg_match('/(?<!\w)@(\w+)/',$val,$parts)?
					self::token($val):
					self::stringify($val)).'; ';
		return '<?php '.$out.'?>';
	}

	/**
		Template -include- tag handler
			@return string
			@param $node array
	**/
	static function _include(array $node) {
		$attrib=$node['@attrib'];
		return
			'<?php '.(isset($attrib['if'])?
				('if ('.self::token($attrib['if']).') '):'').
				('echo self::serve('.
					(preg_match('/(?<!\w)@(\w+)/',
						$attrib['href'],$parts)?
						self::token($attrib['href']):
						self::stringify($attrib['href'])).','.
					'self::$mime,get_defined_vars()); ?>');
	}

	/**
		Template -exclude- tag handler
			@return string
			@param $node array
	**/
	static function _exclude(array $node) {
		return '';
	}

	/**
		Template -loop- tag handler
			@return string
			@param $node array
	**/
	static function _loop(array $node) {
		$attrib=$node['@attrib'];
		unset($node['@attrib']);
		return
			'<?php for ('.
				self::token($attrib['from']).';'.
				self::token($attrib['to']).';'.
				self::token($attrib['step']).'): ?>'.
				self::build($node).
			'<?php endfor; ?>';
	}

	/**
		Template -repeat- tag handler
			@return string
			@param $node array
	**/
	static function _repeat(array $node) {
		$attrib=$node['@attrib'];
		unset($node['@attrib']);
		return
			'<?php foreach (('.
				self::token($attrib['group']).'?:array()) as '.
				(isset($attrib['key'])?
					(self::token($attrib['key']).'=>'):'').
				self::token($attrib['value']).'): ?>'.
				self::build($node).
			'<?php endforeach; ?>';
	}

	/**
		Template -check- tag handler
			@return string
			@param $node array
	**/
	static function _check(array $node) {
		$attrib=$node['@attrib'];
		unset($node['@attrib']);
		// Grab <true> and <false> blocks
		foreach ($node as $pos=>$block)
			if (isset($block['true']))
				$true=array($pos,$block);
			elseif (isset($block['false']))
				$false=array($pos,$block);
		if (isset($true) && isset($false) && $true[0]>$false[0])
			// Reverse <true> and <false> blocks
			list($node[$true[0]],$node[$false[0]])=array($false[1],$true[1]);
		return
			'<?php if ('.self::token($attrib['if']).'): ?>'.
				self::build($node).
			'<?php endif; ?>';
	}

	/**
		Template -true- tag handler
			@return string
			@param $node array
	**/
	static function _true(array $node) {
		return self::build($node);
	}

	/**
		Template -false- tag handler
			@return string
			@param $node array
	**/
	static function _false(array $node) {
		return '<?php else: ?>'.self::build($node);
	}

	/**
		Assemble markup
			@return string
			@param $node array|string
	**/
	static function build($node) {
		if (is_string($node)) {
			$self=__CLASS__;
			return preg_replace_callback(
				'/{{(.+?)}}/s',
				function($expr) use($self) {
					return '<?php echo '.trim($self::token($expr[1])).'; ?>';
				},
				$node
			);
		}
		$out='';
		foreach ($node as $key=>$val)
			$out.=is_int($key)?
				self::build($val):
				call_user_func(array(__CLASS__,'_'.$key),$val);
		return $out;
	}

	/**
		Create sandbox for template execution
		@return string
	**/
	static function sandbox() {
		extract(self::$hive);
		ob_start();
		require self::$view;
		return ob_get_clean();
	}

	/**
		Render template
			@return string
			@param $file string
			@param $mime string
			@param $hive array
	**/
	static function serve($file,$mime='text/html',array $hive=NULL) {
		if (!$hive)
			$hive=self::$vars;
		self::$hive=$hive;
		foreach (F3::split(self::$vars['GUI']) as $path)
			if (is_file($view=self::fixslashes($path.$file))) {
				if (!is_dir($dir=self::$vars['TEMP']))
					self::mkdir($dir);
				if (!is_file(self::$view=($dir.'/'.
					$_SERVER['SERVER_NAME'].'.tpl.'.self::hash($view))) ||
					filemtime(self::$view)<filemtime($view)) {
					// Remove PHP code and comments
					$text=preg_replace('/<\?(?:php)?.+?\?>|{{\*.+?\*}}/is','',
						self::getfile($view));
					// Build tree structure
					for ($ptr=0,$len=strlen($text),$tree=array(),$node=&$tree,
						$stack=array(),$depth=0,$temp='';$ptr<$len;)
						if (preg_match('/^<(\/?)(?:F3:)?('.self::$tags.')\b'.
							'((?:\s+\w+s*=\s*(?:"(?:.+?)"|\'(?:.+?)\'))*)\s*'.
							'(\/?)>/is',substr($text,$ptr),$match)) {
							if (strlen($temp))
								$node[]=$temp;
							// Element node
							if ($match[1]) {
								// Find matching start tag
								$save=$depth;
								$found=FALSE;
								while ($depth>0) {
									$depth--;
									foreach ($stack[$depth] as $item)
										if (is_array($item) &&
											isset($item[$match[2]])) {
											// Start tag found
											$found=TRUE;
											break 2;
										}
								}
								if (!$found)
									// Unbalanced tag
									$depth=$save;
								$node=&$stack[$depth];
							}
							else {
								// Start tag
								$stack[$depth]=&$node;
								$node=&$node[][$match[2]];
								if ($match[3]) {
									// Process attributes
									preg_match_all(
										'/\s+(\w+)\s*='.
										'\s*(?:"(.+?)"|\'(.+?)\')/s',
										$match[3],$attr,PREG_SET_ORDER);
									foreach ($attr as $kv)
										$node['@attrib'][$kv[1]]=
											$kv[2]?:$kv[3];
								}
								if ($match[4])
									// Empty tag
									$node=&$stack[$depth];
								else
									$depth++;
							}
							$temp='';
							$ptr+=strlen($match[0]);
						}
						else {
							// Text node
							$temp.=$text[$ptr];
							$ptr++;
						}
					if (strlen($temp))
						// Append trailing text
						$node[]=$temp;
					// Break references
					unset($node);
					unset($stack);
					self::putfile(self::$view,self::build($tree));
				}
				if (PHP_SAPI!='cli' && !headers_sent())
					header('Content-Type: '.(self::$mime=$mime).'; '.
						'charset='.self::$vars['ENCODING']);
				ob_start();
				echo self::sandbox();
				return ob_get_clean();
			}
		trigger_error(sprintf(self::TEXT_Render,$file));
	}

}
