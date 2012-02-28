<?php

/**
	Template engine for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Template
		@version 2.0.9
**/

//! Template engine
class Template extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_Render='Template %s cannot be rendered';
	//@}

	/**
		Render template
			@return string
			@param $file string
			@param $mime string
			@param $globals boolean
			@public
	**/
	static function serve($file,$mime='text/html',$globals=TRUE) {
		$file=self::resolve($file);
		$found=FALSE;
		foreach (preg_split('/[\|;,]/',self::$vars['GUI'],0,
			PREG_SPLIT_NO_EMPTY) as $gui) {
			if (is_file($view=self::fixslashes($gui.$file))) {
				$found=TRUE;
				break;
			}
		}
		if (!$found) {
			trigger_error(sprintf(self::TEXT_Render,$file));
			return '';
		}
		if (PHP_SAPI!='cli' && !headers_sent())
			// Send HTTP header with appropriate character set
			header(self::HTTP_Content.': '.$mime.'; '.
				'charset='.self::$vars['ENCODING']);
		$hash='tpl.'.self::hash($view);
		$cached=Cache::cached($hash);
		if ($cached && filemtime($view)<$cached) {
			if (self::$vars['CACHE'])
				// Retrieve PHP-compiled template from cache
				$text=Cache::get($hash);
		}
		else {
			// Parse raw template
			$doc=new F3markup($mime,$globals);
			$text=$doc->load(self::getfile($view));
			if (self::$vars['CACHE'] && $doc::$cache)
				// Save PHP-compiled template to cache
				Cache::set($hash,$text);
		}
		// Render in a sandbox
		$instance=new F3instance;
		ob_start();
		if (ini_get('allow_url_fopen') && ini_get('allow_url_include'))
			// Stream wrap
			$instance->sandbox('data:text/plain,'.urlencode($text));
		else {
			// Save PHP-equivalent file in temporary folder
			if (!is_dir(self::$vars['TEMP']))
				self::mkdir(self::$vars['TEMP']);
			$temp=self::$vars['TEMP'].$_SERVER['SERVER_NAME'].'.'.$hash;
			if (!$cached || !is_file($temp) ||
				filemtime($temp)<Cache::cached($view)) {
				// Create semaphore
				$hash='sem.'.self::hash($view);
				while ($cached=Cache::cached($hash))
					// Locked by another process
					usleep(mt_rand(0,100));
				Cache::set($hash,TRUE);
				self::putfile($temp,$text);
				// Remove semaphore
				Cache::clear($hash);
			}
			$instance->sandbox($temp);
		}
		$out=ob_get_clean();
		unset($instance);
		return self::$vars['TIDY']?self::tidy($out):$out;
	}

}

//! Markup loader/parser/builder
class F3markup extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_AttribMissing='Missing attribute: %s',
		TEXT_AttribInvalid='Invalid attribute: %s',
		TEXT_Global='Use of global variable %s is not allowed';
	//@}

	public
		//! MIME type
		$mime,
		//! Enable/disable PHP globals
		$globals=TRUE;
	static
		//! Enable/disable template caching
		$cache=TRUE;

	private
		//! Parsed markup string
		$tree=array(),
		//! Symbol table for repeat/loop blocks
		$syms=array();

	/**
		Convert template expression to PHP code
			@return string
			@param $str string
			@param $echo boolean
			@public
	**/
	function expr($str,$echo=FALSE) {
		$self=$this;
		$syms=&$this->syms;
		$regex='/(?!\w)@(\w+(?:\[[^\]]+\]|\.\w+(?![\w\(]))*'.
			'(?:\s*->\s*\w+)?)(\s*\([^\)]*\))?(?:\s*\\\(.+))?/';
		return preg_replace_callback(
			'/{{(.+?)}}/s',
			function($expr) use(&$syms,$self,$echo,$regex) {
				$out=preg_replace_callback($regex,
					function($var) use(&$syms,$self,$regex) {
						//Return stringified framework variable
						preg_match('/^(\w+)\b(.*)/',$var[1],$match);
						if (!$self->globals &&
							preg_match('/'.$self::PHP_Globals.'/',
							$match[1])) {
							trigger_error(
								sprintf($self::TEXT_Global,$match[1])
							);
							return FALSE;
						}
						$isfunc=isset($var[2]) && $var[2];
						if (in_array($match[1],$syms))
							return '$_'.$self::remix($var[1]).
								($isfunc?$var[2]:'');
						$str='F3::get('.var_export($var[1],TRUE).')';
						if ($isfunc) {
							preg_match_all($regex,$var[2],$parts,
								PREG_SET_ORDER);
							$args='';
							if ($parts) {
								foreach ($parts as $part)
									$args.=($args?',':'').
										$self->expr('{{'.$part[0].'}}');
								$args='('.$args.')';
							}
							else
								$args=$var[2];
							$str='call_user_func_array('.
								$str.',array'.$args.')';
						}
						elseif (isset($var[3]))
							$str=str_replace(')',',array('.
								$self->expr('{{'.$var[3].'}}').'))',$str);
						if (!$match[2] &&
							!preg_match('/('.$self::PHP_Globals.')\b/',
								$match[1])) {
							$syms[]=$match[1];
							$str='($_'.$match[1].'='.$str.')';
						}
						return $str;
					},
					$expr[1]
				);
				return $echo?('<?php echo '.$out.'; ?>'):$out;
			},
			$str
		);
	}

	/**
		Return TRUE if all mandatory attributes are present
			@return boolean
			@param $key string
			@param $tags array
			@param $attrs array
			@public
	**/
	function isdef($key,array $tags,array $attrs) {
		$ok=TRUE;
		foreach ($attrs as $attr)
			if (!isset($tags['@attrib'][$attr])) {
				$ok=FALSE;
				break;
			}
		if ($ok)
			return TRUE;
		$out='<'.$key;
		if (isset($tags['@attrib']))
			foreach ($tags['@attrib'] as $akey=>$aval)
				$out.=' '.$akey.'="'.$aval.'"';
		$out.='>';
		trigger_error(sprintf(self::TEXT_AttribMissing,$out));
		return FALSE;

	}

	/**
		Reassemble markup string and insert appropriate PHP code
			@return string
			@param $node mixed
			@public
	**/
	function build($node) {
		$out='';
		if (is_array($node)) {
			foreach ($node as $nkey=>$nval)
				if (is_int($nkey))
					$out.=$this->build($nval);
				else {
					$count=count($this->syms);
					switch ($nkey) {
						case 'include':
							// <include> directive
							if (!$this->isdef($nkey,$nval,array('href')))
								return;
							$hvar=$nval['@attrib']['href'];
							if (isset($nval['@attrib']['if'])) {
								$ival=$nval['@attrib']['if'];
								$cond=$this->expr($ival);
								// Syntax check
								if ($cond==$ival) {
									trigger_error(sprintf(
										self::TEXT_AttribInvalid,
										'if="'.addcslashes($ival,'"').'"'));
									return;
								}
							}
							$doc=new F3markup($this->mime,$this->globals);
							$file=self::resolve($hvar);
							if ($hvar!=$file)
								self::$cache=FALSE;
							foreach (self::split(self::$vars['GUI']) as $gui)
								if (is_file($view=$gui.$file)) {
									$text=$doc->load(
										self::getfile($view),$this->syms
									);
									$out.=isset($ival)?
										('<?php if ('.trim($cond).'): ?>'.
											$text.'<?php endif; ?>'):$text;
									break;
								}
							break;
						case 'loop':
							// <loop> directive
							if (!$this->isdef($nkey,$nval,
								array('counter','from','to')))
								return;
							$cvar=self::remix(
								preg_replace('/{{\s*@(.+?)\s*}}/','\1',
									$nval['@attrib']['counter']));
							foreach ($nval['@attrib'] as $akey=>$aval) {
								${$akey[0].'att'}=$aval;
								${$akey[0].'str'}=$this->expr($aval);
								// Syntax check
								if (${$akey[0].'str'}==$aval) {
									trigger_error(sprintf(
										self::TEXT_AttribInvalid,$akey.'="'.
											addcslashes($aval,'"').'"'));
									return;
								}
							}
							unset($nval['@attrib']);
							$this->syms[]=$cvar;
							$out.='<?php for ('.
								'$_'.$cvar.'='.$fstr.';'.
								'$_'.$cvar.'<='.$tstr.';'.
								'$_'.$cvar.'+='.
									// step attribute
									(isset($satt)?$sstr:'1').'): ?>'.
								$this->build($nval).
								'<?php endfor; ?>';
							break;
						case 'repeat':
							// <repeat> directive
							if (!$this->isdef($nkey,$nval,array('group')) &&
								(!$this->isdef($nkey,$nval,array('key')) ||
								!$this->isdef($nkey,$nval,array('value'))))
								return;
							$gval=$nval['@attrib']['group'];
							$gstr=trim($this->expr($gval));
							// Syntax check
							if ($gstr==$gval) {
								trigger_error(sprintf(
									self::TEXT_AttribInvalid,
									'group="'.addcslashes($gval,'"').'"'));
								return;
							}
							foreach ($nval['@attrib'] as $akey=>$aval) {
								${$akey[0].'var'}=self::remix(
									preg_replace('/{{\s*@(.+?)\s*}}/',
										'\1',$aval));
								// Syntax check
								if (${$akey[0].'var'}==$aval) {
									trigger_error(sprintf(
										self::TEXT_AttribInvalid,
										$akey.'='.
											'"'.addcslashes($aval,'"').'"'));
									return;
								}
							}
							unset($nval['@attrib']);
							if (isset($vvar))
								$this->syms[]=$vvar;
							else
								$vvar=self::hash($gvar);
							if (isset($kvar))
								$this->syms[]=$kvar;
							if (isset($cvar))
								$this->syms[]=$cvar;
							$out.='<?php '.
								(isset($cvar)?('$_'.$cvar.'=0; '):'').
								'if (is_array('.$gstr.')):'.
								'foreach (('.$gstr.'?:array()) as '.
								(isset($kvar)?('$_'.$kvar.'=>'):'').
									'$_'.$vvar.'):'.
								(isset($cvar)?('$_'.$cvar.'++; '):'').
								'?>'.
								$this->build($nval).
								'<?php '.
									'endforeach;'.
									'endif;'.
								'?>';
							break;
						case 'check':
							// <check> directive
							if (!$this->isdef($nkey,$nval,array('if')))
								return;
							$ival=$nval['@attrib']['if'];
							$cond=$this->expr($ival);
							// Syntax check
							if ($cond==$ival) {
								trigger_error(sprintf(
									self::TEXT_AttribInvalid,
									'if="'.addcslashes($ival,'"').'"'));
								return;
							}
							// Is <true> is defined ahead of <false>?
							foreach ($nval as $pos=>$blk)
								if (is_array($blk))
									foreach ($blk as $ckey=>$cval)
										if (preg_match('/(?:F3:)?'.
											'(?:true|false)/i',$ckey))
											${$ckey[0].'block'}=
												array($pos,$blk);
							if (isset($tblock) && isset($fblock) &&
								$tblock[0]>$fblock[0])
								// Swap <true> and <false> blocks
								// <false> is defined ahead of <true>
								list($nval[$tblock[0]],$nval[$fblock[0]])=
									array($fblock[1],$tblock[1]);
							$out.='<?php if ('.trim($cond).'): ?>'.
								$this->build($nval).
								'<?php endif; ?>';
							break;
						case 'true':
							// <true> block of <check> directive
							$out.=$this->build($nval);
							break;
						case 'false':
							// <false> block of <check> directive
							$out.='<?php else: ?>'.$this->build($nval);
							break;
					}
					// Reset scope
					$this->syms=array_slice($this->syms,0,$count);
				}
		}
		else
			$out.=preg_match('/<\?php/',$node)?$node:$this->expr($node,TRUE);
		return $out;
	}

	/**
		Load markup from string
			@return string
			@param $text string
			@param $syms array
			@public
	**/
	function load($text,array $syms=NULL) {
		if ($syms)
			$this->syms=$syms;
		// Remove PHP code and alternative exclude-tokens
		$text=preg_replace(
			'/<\?(?:php)?.+?\?>|{{\*.+?\*}}/is','',$text);
		// Define root node
		$node=&$this->tree;
		// Define stack and depth variables
		$stack=array();
		$depth=0;
		// Define string parser variables
		$len=strlen($text);
		$ptr=0;
		$temp='';
		while ($ptr<$len)
			if (preg_match('/^<(\/?)'.
				'(?:F3:)?(include|exclude|loop|repeat|check|true|false)\b'.
				'((?:\s+\w+s*=\s*(?:"(?:.+?)"|\'(?:.+?)\'))*)\s*(\/?)>/is',
				substr($text,$ptr),$match)) {
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
							if (is_array($item) && isset($item[$match[2]])) {
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
						preg_match_all('/\s+(\w+)\s*=\s*'.
							'(?:"(.+?)"|\'(.+?)\')/s',$match[3],$attr,
							PREG_SET_ORDER);
						foreach ($attr as $kv)
							$node['@attrib'][$kv[1]]=$kv[2]?:$kv[3];
					}
					if ($match[4] || $match[1]=='include')
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
			$node[]=$temp;
		unset($node);
		unset($stack);
		return $this->build($this->tree);
	}

	/**
		Override base constructor
			@param $mime string
			@param $globals boolean
			@public
	**/
	function __construct($mime,$globals) {
		$this->mime=$mime;
		$this->globals=$globals;
	}

}
