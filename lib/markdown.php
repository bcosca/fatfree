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

//! Markdown-to-HTML converter
class Markdown extends Prefab {

	protected
		//! Parsing rules
		$blocks,
		//! Special characters
		$special;

	/**
		Process blockquote
		@return string
		@param $str string
	**/
	protected function _blockquote($str) {
		$str=preg_replace('/(?<=^|\n)\h?>\h?(.*?(?:\n+|$))/','\1',$str);
		return strlen($str)?
			('<blockquote>'.$this->build($str).'</blockquote>'."\n\n"):'';
	}

	/**
		Process whitespace-prefixed code block
		@return string
		@param $str string
	**/
	protected function _pre($str) {
		$str=preg_replace('/(?<=^|\n)(?: {4}|\t)(.+?(?:\n+|$))/','\1',
			$this->esc($str));
		return strlen($str)?
			('<pre><code>'.
				$this->esc($this->snip($str)).
			'</code></pre>'."\n\n"):
			'';
	}

	/**
		Process fenced code block
		@return string
		@param $hint string
		@param $str string
	**/
	protected function _fence($hint,$str) {
		$str=$this->snip($str);
		$fw=Base::instance();
		$str=$fw->get('HIGHLIGHT') && preg_match('/^php\b/',$hint)?
			$fw->highlight($str):
			('<code>'.$this->esc($str).'</code>');
		return '<pre>'.$str.'</pre>'."\n\n";
	}

	/**
		Process horizontal rule
		@return string
	**/
	protected function _hr() {
		return '<hr />'."\n\n";
	}

	/**
		Process atx-style heading
		@return string
		@param $type string
		@param $str string
	**/
	protected function _atx($type,$str) {
		$level=strlen($type);
		return '<h'.$level.' id="'.Web::instance()->slug($str).'">'.
			$this->scan($str).'</h'.$level.'>'."\n\n";
	}

	/**
		Process setext-style heading
		@return string
		@param $str string
		@param $type string
	**/
	protected function _setext($str,$type) {
		$level=strpos('=-',$type)+1;
		return '<h'.$level.' id="'.Web::instance()->slug($str).'">'.
			$this->scan($str).'</h'.$level.'>'."\n\n";
	}

	/**
		Process ordered/unordered list
		@return string
		@param $str string
	**/
	protected function _li($str) {
		// Initialize list parser
		$len=strlen($str);
		$ptr=0;
		$dst='';
		$first=TRUE;
		$tight=TRUE;
		$type='ul';
		// Main loop
		while ($ptr<$len) {
			if (preg_match('/^\h*[*-](?:\h?[*-]){2,}(?:\n+|$)/',
				substr($str,$ptr),$match)) {
				$ptr+=strlen($match[0]);
				// Embedded horizontal rule
				return (strlen($dst)?
					('<'.$type.'>'."\n".$dst.'</'.$type.'>'."\n\n"):'').
					'<hr />'."\n\n".$this->build(substr($str,$ptr));
			}
			elseif (preg_match('/(?<=^|\n)([*+-]|\d+\.)\h'.
				'(.+?(?:\n+|$))((?:(?: {4}|\t)+.+?(?:\n+|$))*)/s',
				substr($str,$ptr),$match)) {
				// List
				$block=preg_match(
					'/^\h+([*+-]|\d+\.)\h?.+?(?:\n+|$)/',$match[3]);
				if ($first) {
					// First pass
					if (is_numeric($match[1]))
						$type='ol';
					if (preg_match('/\n{2,}$/',
						$match[2].($block?'':$match[3])))
						// Loose structure; Use paragraphs
						$tight=FALSE;
					$first=FALSE;
				}
				$ptr+=strlen($match[0]);
				// Strip leading whitespaces
				$match[3]=preg_replace('/(?<=^|\n)(?: {4}|\t)/','',$match[3]);
				$tmp=$this->snip($match[2].$match[3]);
				$dst.='<li>'.$this->scan(
					trim(
						$tight?
							($block?
								($match[2].
									$this->_li($this->snip($match[3]))):
								$tmp):
							$this->build($tmp)
					)
				).'</li>'."\n";
			}
		}
		return strlen($dst)?
			('<'.$type.'>'."\n".$dst.'</'.$type.'>'."\n\n"):'';
	}

	/**
		Ignore raw HTML
		@return string
		@param $str string
	**/
	protected function _raw($str) {
		//var_dump($str);
		return $str;
	}

	/**
		Process paragraph
		@return string
		@param $str string
	**/
	protected function _p($str) {
		$str=trim($str);
		if (strlen($str)) {
			if (preg_match('/(.+?\n)([>#].+)/',$str,$parts))
				return $this->_p($parts[1]).$this->build($parts[2]);
			$self=$this;
			$str=preg_replace_callback(
				'/([^<\[]+)?(<.+?>|\[.+?\]\s*\(.+?\))([^>\]]+)?|(.+)/s',
				function($expr) use($self) {
					$tmp='';
					if (isset($expr[4]))
						$tmp.=$self->esc($expr[4]);
					else {
						if (isset($expr[1]))
							$tmp.=$self->esc($expr[1]);
						$tmp.=$expr[2];
						if (isset($expr[3]))
							$tmp.=$self->esc($expr[3]);
					}
					return $tmp;
				},
				$str
			);
			return '<p>'.$this->scan($str).'</p>'."\n\n";
		}
		return '';
	}

	/**
		Process mixed strong/em span
		@return string
		@param $str string
	**/
	protected function _mixed($str) {
		return preg_replace('/(?<!\\\\)([*_]{3})([^\n`]+)(?!\\\\)\1/',
			'<strong><em>\2</em></strong>',$str);
	}

	/**
		Process strong span
		@return string
		@param $str string
	**/
	protected function _strong($str) {
		return preg_replace('/(?<!\\\\)([*_]{2})([^\n`]+)(?!\\\\)\1/',
			'<strong>\2</strong>',$str);
	}

	/**
		Reduce em span
		@return string
		@param $str string
	**/
	protected function _em($str) {
		return preg_replace('/(?<!\\\\)([*_])([^\n`]+)(?!\\\\)\1/',
			'<em>\2</em>',$str);
	}

	/**
		Process image span
		@return string
		@param $str string
	**/
	protected function _img($str) {
		$self=$this;
		return preg_replace_callback(
			'/!(?:\[(.+?)\])?\h*\(<?(.*?)>?(?:\h*"(.*?)"\h*)?\)/',
			function($expr) use($self) {
				return '<img src="'.$expr[2].'"'.
					(empty($expr[1])?
						'':
						(' alt="'.$self->esc($expr[1]).'"')).
					(empty($expr[3])?
						'':
						(' title="'.$self->esc($expr[3]).'"')).' />';
			},
			$str
		);
	}

	/**
		Process anchor span
		@return string
		@param $str string
	**/
	protected function _a($str) {
		$self=$this;
		return preg_replace_callback(
			'/(?<!\\\\)\[(.+?)(?!\\\\)\]\h*\(<?(.*?)>?(?:\h*"(.*?)"\h*)?\)/',
			function($expr) use($self) {
				return '<a href="'.$self->esc($expr[2]).'"'.
					(empty($expr[3])?
						'':
						(' title="'.$self->esc($expr[3]).'"')).
					'>'.$expr[1].'</a>';
			},
			$str
		);
	}

	/**
		Auto-convert links
		@return string
		@param $str string
	**/
	protected function _auto($str) {
		$self=$this;
		return preg_replace_callback(
			'/`.*?<(.+?)>.*?`|<(.+?)>/',
			function($expr) use($self) {
				if (empty($expr[1]) && parse_url($expr[2],PHP_URL_SCHEME)) {
					$expr[2]=$self->esc($expr[2]);
					return '<a href="'.$expr[2].'">'.$expr[2].'</a>';
				}
				return $expr[0];
			},
			$str
		);
	}

	/**
		Process code span
		@return string
		@param $str string
	**/
	protected function _code($str) {
		$self=$this;
		return preg_replace_callback(
			'/(".*?`.+?`.*?")|`` (.+?) ``|(?<!\\\\)`(.+?)(?!\\\\)`/',
			function($expr) use($self) {
				return empty($expr[1])?
					('<code>'.
						$self->esc(empty($expr[2])?$expr[3]:$expr[2]).
					'</code>'):
					$expr[1];
			},
			$str
		);
	}

	/**
		Convert characters to HTML entities
		@return string
		@param $str string
	**/
	function esc($str) {
		if (!$this->special)
			$this->special=array(
				'...'=>'&hellip;',
				'(tm)'=>'&trade;',
				'(r)'=>'&reg;',
				'(c)'=>'&copy;'
			);
		foreach ($this->special as $key=>$val)
			$str=preg_replace('/'.preg_quote($key,'/').'/i',$val,$str);
		return htmlspecialchars($str,ENT_COMPAT,
			Base::instance()->get('ENCODING'),FALSE);
	}

	/**
		Reduce multiple line feeds
		@return string
		@param $str string
	**/
	protected function snip($str) {
		return preg_replace('/(?:(?<=\n)\n+)|\n+$/',"\n",$str);
	}

	/**
		Scan line for convertible spans
		@return string
		@param $str string
	**/
	protected function scan($str) {
		$inline=array('img','a','mixed','strong','em','auto','code');
		foreach ($inline as $func)
			$str=$this->{'_'.$func}($str);
		return $str;
	}

	/**
		Assemble blocks
		@return string
		@param $str string
	**/
	protected function build($str) {
		if (!$this->blocks) {
			// Regexes for capturing entire blocks
			$this->blocks=array(
				'blockquote'=>'/^(?:\h?>\h?.*?(?:\n+|$))+/',
				'pre'=>'/^(?:(?: {4}|\t).+?(?:\n+|$))+/',
				'fence'=>'/^`{3}\h*(\w+)?[^\n]*\n+(.+?)`{3}[^\n]*(?:\n+|$)/s',
				'hr'=>'/^\h*[*_-](?:\h?[\*_-]){2,}\h*(?:\n+|$)/',
				'atx'=>'/^\h*(#{1,6})\h?(.+?)\h*(?:#.*)?(?:\n+|$)/',
				'setext'=>'/^\h*(.+?)\h*\n([=-])+\h*(?:\n+|$)/',
				'li'=>'/^(?:(?:[*+-]|\d+\.)\h.+?(?:\n+|$)'.
					'(?:(?: {4}|\t)+.+?(?:\n+|$))*)+/s',
				'raw'=>'/^((?:<!--.+?-->|<\?.+?\?>|<%.+?%>|'.
					'<(\w+).*?(?:\/>|>(?:(?>[^><]+)|(?R))*<\/\2>))'.
					'\h*(?:\n{2,}|\n?$))/s',
				'p'=>'/^(.+?(?:\n{2,}|\n?$))/s'
			);
		}
		$self=$this;
		// Treat lines with nothing but whitespaces as empty lines
		$str=preg_replace('/\n\h+(?=\n)/',"\n",$str);
		// Initialize block parser
		$len=strlen($str);
		$ptr=0;
		$dst='';
		// Main loop
		while ($ptr<$len) {
			if (preg_match('/^ {0,3}\[([^\[\]]+)\]:\s*<?(.*?)>?\s*'.
				'(?:"([^\n]*)")?(?:\n+|$)/s',substr($str,$ptr),$match)) {
				// Reference-style link; Backtrack
				$ptr+=strlen($match[0]);
				$tmp='';
				// Catch line breaks in title attribute
				$ref=preg_replace('/\h/','\s',preg_quote($match[1],'/'));
				while ($dst!=$tmp) {
					$dst=preg_replace_callback(
						'/(?<!\\\\)\[('.$ref.')(?!\\\\)\]\s*\[\]|'.
						'(!?)(?:\[([^\[\]]+)\]\s*)?'.
						'(?<!\\\\)\[('.$ref.')(?!\\\\)\]/',
						function($expr) use($match,$self) {
							return (empty($expr[2]))?
								// Anchor
								('<a href="'.$self->esc($match[2]).'"'.
								(empty($match[3])?
									'':
									(' title="'.
										$self->esc($match[3]).'"')).'>'.
								// Link
								(empty($expr[3])?
									(empty($expr[1])?
										$expr[4]:
										$expr[1]):
									$expr[3]).
								'</a>'):
								// Image
								('<img src="'.$match[2].'"'.
								(empty($expr[2])?
									'':
									(' alt="'.
										$self->esc($expr[3]).'"')).
								(empty($match[3])?
									'':
									(' title="'.
										$self->esc($match[3]).'"')).
								' />');
						},
						$tmp=$dst
					);
				}
			}
			else
				foreach ($this->blocks as $func=>$regex)
					if (preg_match($regex,substr($str,$ptr),$match)) {
						//echo '{'.$func.','.$ptr.':'.strlen($match[0]).'}->';
						//var_dump($match[0]);
						$ptr+=strlen($match[0]);
						$dst.=call_user_func_array(
							array($this,'_'.$func),
							count($match)>1?array_slice($match,1):$match
						);
						break;
					}
		}
		return $dst;
	}

	/**
		Render HTML equivalent of markdown
		@return string
		@param $file string
	**/
	function render($file) {
		$fw=Base::instance();
		if (!is_dir($tmp=$fw->get('TEMP')))
			mkdir($tmp,Base::MODE,TRUE);
		foreach ($fw->split($fw->get('UI')) as $dir)
			if (is_file($abs=$fw->fixslashes($dir.$file))) {
				$str=preg_replace_callback(
					'/(<code>.+?<\/code>|<[^>\n]+>|\([^\n\)]+\)|"[^"\n]+")|'.
					'\\\\(.)/s',
					function($expr) {
						// Process escaped characters
						return empty($expr[1])?$expr[2]:$expr[1];
					},
					$this->build($fw->read($abs,TRUE))
				);
				return $this->snip($str);
			}
		user_error(sprintf(Base::E_Open,$file));
	}

}
