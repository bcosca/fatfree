<?php

//! Template engine
class Template {

	private
		//! Compiled template file
		$view,
		//! MIME type
		$mime,
		//! Escaped copy of framework hive
		$hive,
		//! Template tags
		$tags='set|include|exclude|loop|repeat|check|true|false';

	/**
		Convert token to variable
		@return string
		@param $str string
	**/
	function token($str) {
		if (preg_match('/{{(.+?)}}/',$str,$parts))
			$str=$parts[1];
		return preg_replace('/(?<!\w)@(\w+)/','$\1',$str);
	}

	/**
		Template -set- tag handler
		@return string
		@param $node array
	**/
	protected function _set(array $node) {
		$out='';
		foreach ($node['@attrib'] as $key=>$val)
			$out.='$'.$key.'='.
				(preg_match('/{{(.+?)}}/',$val,$parts)?
					$this->token($val):
					Base::instance()->stringify($val)).'; ';
		return '<?php '.$out.'?>';
	}

	/**
		Template -include- tag handler
		@return string
		@param $node array
	**/
	protected function _include(array $node) {
		$attrib=$node['@attrib'];
		return
			'<?php '.(isset($attrib['if'])?
				('if ('.$this->token($attrib['if']).') '):'').
				('echo $this->serve('.
					(preg_match('/{{(.+?)}}/',$attrib['href'],$parts)?
						$this->token($parts[1]):
						Base::instance()->stringify($attrib['href'])).','.
					'$this->mime,get_defined_vars()); ?>');
	}

	/**
		Template -exclude- tag handler
		@return string
		@param $node array
	**/
	protected function _exclude(array $node) {
		return '';
	}

	/**
		Template -loop- tag handler
		@return string
		@param $node array
	**/
	protected function _loop(array $node) {
		$attrib=$node['@attrib'];
		unset($node['@attrib']);
		return
			'<?php for ('.
				$this->token($attrib['from']).';'.
				$this->token($attrib['to']).';'.
				$this->token($attrib['step']).'): ?>'.
				$this->build($node).
			'<?php endfor; ?>';
	}

	/**
		Template -repeat- tag handler
		@return string
		@param $node array
	**/
	protected function _repeat(array $node) {
		$attrib=$node['@attrib'];
		unset($node['@attrib']);
		return
			'<?php foreach (('.
				$this->token($attrib['group']).'?:array()) as '.
				(isset($attrib['key'])?
					($this->token($attrib['key']).'=>'):'').
				$this->token($attrib['value']).'): ?>'.
				$this->build($node).
			'<?php endforeach; ?>';
	}

	/**
		Template -check- tag handler
		@return string
		@param $node array
	**/
	protected function _check(array $node) {
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
			'<?php if ('.$this->token($attrib['if']).'): ?>'.
				$this->build($node).
			'<?php endif; ?>';
	}

	/**
		Template -true- tag handler
		@return string
		@param $node array
	**/
	protected function _true(array $node) {
		return $this->build($node);
	}

	/**
		Template -false- tag handler
		@return string
		@param $node array
	**/
	function _false(array $node) {
		return '<?php else: ?>'.$this->build($node);
	}

	/**
		Assemble markup
		@return string
		@param $node array|string
	**/
	protected function build($node) {
		if (is_string($node)) {
			$self=$this;
			return preg_replace_callback(
				'/{{(.+?)}}/s',
				function($expr) use($self) {
					$str=trim($self->token($expr[1]));
					if (preg_match('/^(.+?)\s*\|\s*(raw|esc|format)$/',
						$str,$parts))
						$str='Base::instance()->'.$parts[2].'('.$parts[1].')';
					return '<?php echo '.$str.'; ?>';
				},
				$node
			);
		}
		$out='';
		foreach ($node as $key=>$val)
			$out.=is_int($key)?$this->build($val):$this->{'_'.$key}($val);
		return $out;
	}

	/**
		Create sandbox for template execution
		@return string
	**/
	protected function sandbox() {
		extract($this->hive);
		ob_start();
		require $this->view;
		return ob_get_clean();
	}

	/**
		Render template
		@return string
		@param $file string
		@param $mime string
		@param $hive array
	**/
	function serve($file,$mime='text/html',array $hive=NULL) {
		$fw=Base::instance();
		if (!is_dir($dir=$fw->get('TEMP').'views'))
			$fw->mkdir($dir);
		foreach ($fw->split($fw->get('UI')) as $path)
			if (is_file($view=$fw->fixslashes($path.$file))) {
				if (!is_file($this->view=($dir.'/'.
					$fw->hash($fw->get('ROOT')).'.'.
					$fw->hash($view).'.php')) ||
					filemtime($this->view)<filemtime($view)) {
					// Remove PHP code and comments
					$text=preg_replace('/<\?(?:php)?.+?\?>|{{\*.+?\*}}/is','',
						$fw->read($view));
					// Build tree structure
					for ($ptr=0,$len=strlen($text),$tree=array(),$node=&$tree,
						$stack=array(),$depth=0,$temp='';$ptr<$len;)
						if (preg_match('/^<(\/?)(?:F3:)?('.$this->tags.')\b'.
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
					$fw->write($this->view,$this->build($tree));
				}
				if (PHP_SAPI!='cli' && !headers_sent())
					header('Content-Type: '.($this->mime=$mime).'; '.
						'charset='.$fw->get('ENCODING'));
				if (!$hive)
					$hive=$fw->hive();
				$this->hive=$fw->get('ESCAPE')?$fw->esc($hive):$hive;
				return $this->sandbox();
			}
		trigger_error(sprintf(Base::E_Open,$file));
	}

	/**
		Return class instance
		@return object
	**/
	static function instance() {
		if (!Registry::exists($class=__CLASS__))
			Registry::set($class,$self=new $class);
		return Registry::get($class);
	}

	//! Prohibit cloning
	private function __clone() {
	}

	//! Prohibit instantiation
	private function __construct() {
	}

	//! Wrap-up
	function __destruct() {
		Registry::clear(__CLASS__);
	}

}
