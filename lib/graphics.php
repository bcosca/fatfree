<?php

/**
	Graphics plugin for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2012 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Graphics
		@version 2.0.12
**/

//! Graphics plugin
class Graphics extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_Image='Unsupported image format',
		TEXT_Color='Invalid color specified';
	//@}

	const
		// Background color
		GFX_BGColor=0xFFF,
		// Foreground transparency
		GFX_Transparency=0x020,
		// Identicon horizontal/vertical blocks
		GFX_IdBlocks=4,
		// Identicon pixels per block
		GFX_IdPixels=64,
		//! PNG compression level
		PNG_Compress=1;

	/**
		Convert RGB hex triad to array
			@return mixed
			@param $int integer
			@public
	**/
	static function rgb($int) {
		$hex=str_pad(dechex($int),$int<4096?3:6,'0',STR_PAD_LEFT);
		$len=strlen($hex);
		if ($len>6) {
			trigger_error(self::TEXT_Color);
			return FALSE;
		}
		$color=str_split($hex,$len/3);
		foreach ($color as &$hue)
			$hue=hexdec(str_repeat($hue,6/$len));
		return $color;
	}

	/**
		Generate CAPTCHA image
			@param $dimx integer
			@param $dimy integer
			@param $len integer
			@param $ttfs string
			@param $var string
			@param $die boolean
			@public
	**/
	static function captcha(
		$dimx=150,$dimy=50,$len=5,$ttfs='cube',$var='captcha',$die=TRUE) {
		$base=self::rgb(self::$vars['BGCOLOR']);
		$trans=self::$vars['FGTRANS'];
		// Specify Captcha seed
		$seed=substr(md5(uniqid()),0,$len);
		F3::set('SESSION.'.$var,$seed);
		// Font size
		$size=.9*min($dimx/$len,$dimy);
		// Load TrueType fonts
		$fonts=self::split($ttfs);
		$file=self::$vars['FONTS'].
			self::fixslashes($fonts[array_rand($fonts)]).'.ttf';
		$stats=&self::ref('STATS');
		if (!isset($stats['FILES']))
			$stats['FILES']=array('fonts'=>array());
		$stats['FILES']['fonts'][basename($file)]=filesize($file);
		$maxdeg=15;
		// Create blank image
		$captcha=imagecreatetruecolor($dimx,$dimy);
		list($r,$g,$b)=$base;
		$bg=imagecolorallocate($captcha,$r,$g,$b);
		imagefill($captcha,0,0,$bg);
		$width=0;
		// Insert each Captcha character
		for ($i=0;$i<$len;$i++) {
			// Random angle
			$angle=mt_rand(-$maxdeg,$maxdeg);
			// Get CAPTCHA character from session cookie
			$char=$seed[$i];
			$fg=imagecolorallocatealpha(
				$captcha,
				mt_rand(0,255-$trans),
				mt_rand(0,255-$trans),
				mt_rand(0,255-$trans),
				$trans
			);
			// Compute bounding box metrics
			$bbox=imagettfbbox($size,0,$file,$char);
			$w=max($bbox[2],$bbox[4])-min($bbox[0],$bbox[6]);
			$h=max($bbox[1],$bbox[3])-min($bbox[5],$bbox[7]);
			$sin=sin(deg2rad($angle));
			imagettftext(
				$captcha,$size,$angle,
				.9*$width+abs($h*$sin),
				$dimy-$h/2+abs($w*$sin),
				$fg,$file,$char
			);
			$width+=$w+abs($h*$sin);
			imagecolordeallocate($captcha,$fg);
		}
		// Make the background transparent
		imagecolortransparent($captcha,$bg);
		// Send output as PNG image
		if (PHP_SAPI!='cli' && !headers_sent()) {
			header(self::HTTP_Content.': image/png');
			header(self::HTTP_Powered.': '.self::TEXT_AppName.' '.
				'('.self::TEXT_AppURL.')');
		}
		imagepng($captcha,NULL,self::PNG_Compress,PNG_NO_FILTER);
		if ($die)
			die;
	}

	/**
		Invert colors of specified image
			@param $file string
			@param $die boolean
			@public
	**/
	static function invert($file,$die=TRUE) {
		preg_match('/\.(gif|jp[e]*g|png)$/i',$file,$ext);
		if ($ext) {
			$ext[1]=str_replace('jpg','jpeg',strtolower($ext[1]));
			$file=self::fixslashes(self::resolve($file));
			$img=imagecreatefromstring(self::getfile($file));
			imagefilter($img,IMG_FILTER_NEGATE);
			if (PHP_SAPI!='cli' && !headers_sent())
				header(self::HTTP_Content.': image/'.$ext[1]);
			// Send output in same graphics format as original
			eval('image'.$ext[1].'($img);');
		}
		else
			trigger_error(self::TEXT_Image);
		if ($die)
			die;
	}

	/**
		Apply grayscale filter on specified image
			@param $file string
			@param $die boolean
			@public
	**/
	static function grayscale($file,$die=TRUE) {
		preg_match('/\.(gif|jp[e]*g|png)$/i',$file,$ext);
		if ($ext) {
			$ext[1]=str_replace('jpg','jpeg',strtolower($ext[1]));
			$file=self::fixslashes(self::resolve($file));
			$img=imagecreatefromstring(self::getfile($file));
			imagefilter($img,IMG_FILTER_GRAYSCALE);
			if (PHP_SAPI!='cli' && !headers_sent())
				header(self::HTTP_Content.': image/'.$ext[1]);
			// Send output in same graphics format as original
			eval('image'.$ext[1].'($img);');
		}
		else
			trigger_error(self::TEXT_Image);
		if ($die)
			die;
	}

	/**
		Generate thumbnail image
			@param $file string
			@param $dimx integer
			@param $dimy integer
			@param $die boolean
			@public
	**/
	static function thumb($file,$dimx,$dimy,$die=TRUE) {
		preg_match('/\.(gif|jp[e]*g|png)$/i',$file,$ext);
		if ($ext) {
			$ext[1]=str_replace('jpg','jpeg',strtolower($ext[1]));
			$file=self::fixslashes(self::resolve($file));
			$img=imagecreatefromstring(self::getfile($file));
			// Get image dimensions
			$oldx=imagesx($img);
			$oldy=imagesy($img);
			// Adjust dimensions; retain aspect ratio
			$ratio=$oldx/$oldy;
			if ($dimx<=$oldx && $dimx/$ratio<=$dimy)
				// Adjust height
				$dimy=$dimx/$ratio;
			elseif ($dimy<=$oldy && $dimy*$ratio<=$dimx)
				// Adjust width
				$dimx=$dimy*$ratio;
			else {
				// Retain size if dimensions exceed original image
				$dimx=$oldx;
				$dimy=$oldy;
			}
			// Create blank image
			$tmp=imagecreatetruecolor($dimx,$dimy);
			list($r,$g,$b)=self::rgb(self::$vars['BGCOLOR']);
			$bg=imagecolorallocate($tmp,$r,$g,$b);
			imagefill($tmp,0,0,$bg);
			// Resize
			imagecopyresampled($tmp,$img,0,0,0,0,$dimx,$dimy,$oldx,$oldy);
			// Make the background transparent
			imagecolortransparent($tmp,$bg);
			if (PHP_SAPI!='cli' && !headers_sent()) {
				header(self::HTTP_Content.': image/'.$ext[1]);
				header(self::HTTP_Powered.': '.self::TEXT_AppName.' '.
					'('.self::TEXT_AppURL.')');
			}
			// Send output in same graphics format as original
			eval('image'.$ext[1].'($tmp);');
		}
		else
			trigger_error(self::TEXT_Image);
		if ($die)
			die;
	}

	/**
		Generate identicon from an MD5 hash value
			@param $hash string
			@param $size integer
			@param $die boolean
			@public
	**/
	static function identicon($hash,$size=NULL,$die=TRUE) {
		$hash=self::resolve($hash);
		$blox=self::$vars['IBLOCKS'];
		if (is_null($size))
			$size=self::$vars['IPIXELS'];
		// Rotatable shapes
		$dynamic=array(
			array(.5,1,1,0,1,1),
			array(.5,0,1,0,.5,1,0,1),
			array(.5,0,1,0,1,1,.5,1,1,.5),
			array(0,.5,.5,0,1,.5,.5,1,.5,.5),
			array(0,.5,1,0,1,1,0,1,1,.5),
			array(1,0,1,1,.5,1,1,.5,.5,.5),
			array(0,0,1,0,1,.5,0,0,.5,1,0,1),
			array(0,0,.5,0,1,.5,.5,1,0,1,.5,.5),
			array(.5,0,.5,.5,1,.5,1,1,.5,1,.5,.5,0,.5),
			array(0,0,1,0,.5,.5,1,.5,.5,1,.5,.5,0,1),
			array(0,.5,.5,1,1,.5,.5,0,1,0,1,1,0,1),
			array(.5,0,1,0,1,1,.5,1,1,.75,.5,.5,1,.25),
			array(0,.5,.5,0,.5,.5,1,0,1,.5,.5,1,.5,.5,0,1),
			array(0,0,1,0,1,1,0,1,1,.5,.5,.25,.5,.75,0,.5,.5,.25),
			array(0,.5,.5,.5,.5,0,1,0,.5,.5,1,.5,.5,1,.5,.5,0,1),
			array(0,0,1,0,.5,.5,.5,0,0,.5,1,.5,.5,1,.5,.5,0,1)
		);
		// Fixed shapes (for center sprite)
		$static=array(
			array(),
			array(0,0,1,0,1,1,0,1),
			array(.5,0,1,.5,.5,1,0,.5),
			array(0,0,1,0,1,1,0,1,0,.5,.5,1,1,.5,.5,0,0,.5),
			array(.25,0,.75,0,.5,.5,1,.25,1,.75,.5,.5,
				.75,1,.25,1,.5,.5,0,.75,0,.25,.5,.5),
			array(0,0,.5,.25,1,0,.75,.5,1,1,.5,.75,0,1,.25,.5),
			array(.33,.33,.67,.33,.67,.67,.33,.67),
			array(0,0,.33,0,.33,.33,.67,.33,.67,0,1,0,1,.33,.67,.33,
				.67,.67,1,.67,1,1,.67,1,.67,.67,.33,.67,.33,1,0,1,
				0,.67,.33,.67,.33,.33,0,.33)
		);
		// Parse MD5 hash
		list($bgR,$bgG,$bgB)=self::rgb(self::$vars['BGCOLOR']);
		list($fgR,$fgG,$fgB)=self::rgb(hexdec(substr($hash,0,6)));
		$shapeC=hexdec($hash[6]);
		$angleC=hexdec($hash[7]%4);
		$shapeX=hexdec($hash[8]);
		for ($i=0;$i<$blox-2;$i++) {
			$shapeS[$i]=hexdec($hash[9+$i*2]);
			$angleS[$i]=hexdec($hash[10+$i*2]%4);
		}
		// Start with NxN blank slate
		$identicon=imagecreatetruecolor($size*$blox,$size*$blox);
		imageantialias($identicon,TRUE);
		$bg=imagecolorallocate($identicon,$bgR,$bgG,$bgB);
		$fg=imagecolorallocate($identicon,$fgR,$fgG,$fgB);
		// Generate corner sprites
		$corner=imagecreatetruecolor($size,$size);
		imagefill($corner,0,0,$bg);
		$sprite=$dynamic[$shapeC];
		for ($i=0,$len=count($sprite);$i<$len;$i++)
			$sprite[$i]=$sprite[$i]*$size;
		imagefilledpolygon($corner,$sprite,$len/2,$fg);
		for ($i=0;$i<$angleC;$i++)
			$corner=imagerotate($corner,90,$bg);
		// Generate side sprites
		for ($i=0;$i<$blox-2;$i++) {
			$side[$i]=imagecreatetruecolor($size,$size);
			imagefill($side[$i],0,0,$bg);
			$sprite=$dynamic[$shapeS[$i]];
			for ($j=0,$len=count($sprite);$j<$len;$j++)
				$sprite[$j]=$sprite[$j]*$size;
			imagefilledpolygon($side[$i],$sprite,$len/2,$fg);
			for ($j=0;$j<$angleS[$i];$j++)
				$side[$i]=imagerotate($side[$i],90,$bg);
		}
		// Generate center sprites
		for ($i=0;$i<$blox-2;$i++) {
			$center[$i]=imagecreatetruecolor($size,$size);
			imagefill($center[$i],0,0,$bg);
			$sprite=$dynamic[$shapeX];
			if ($blox%2>0 && $i==$blox-3)
				// Odd center sprites
				$sprite=$static[$shapeX%8];
			$len=count($sprite);
			if ($len) {
				for ($j=0;$j<$len;$j++)
					$sprite[$j]=$sprite[$j]*$size;
				imagefilledpolygon($center[$i],$sprite,$len/2,$fg);
			}
			if ($i<($blox-3))
				for ($j=0;$j<$angleS[$i];$j++)
					$center[$i]=imagerotate($center[$i],90,$bg);
		}
		// Paste sprites
		for ($i=0;$i<4;$i++) {
			imagecopy($identicon,$corner,0,0,0,0,$size,$size);
			for ($j=0;$j<$blox-2;$j++) {
				imagecopy($identicon,$side[$j],
					$size*($j+1),0,0,0,$size,$size);
				for ($k=$j;$k<$blox-3-$j;$k++)
					imagecopy($identicon,$center[$k],
						$size*($k+1),$size*($j+1),0,0,$size,$size);
			}
			$identicon=imagerotate($identicon,90,$bg);
		}
		if ($blox%2>0)
			// Paste odd center sprite
			imagecopy($identicon,$center[$blox-3],
				$size*(floor($blox/2)),$size*(floor($blox/2)),0,0,
				$size,$size);
		// Resize
		$resized=imagecreatetruecolor($size,$size);
		imagecopyresampled($resized,$identicon,0,0,0,0,$size,$size,
			$size*$blox,$size*$blox);
		// Make the background transparent
		imagecolortransparent($resized,$bg);
		if (PHP_SAPI!='cli' && !headers_sent()) {
			header(self::HTTP_Content.': image/png');
			header(self::HTTP_Powered.': '.self::TEXT_AppName.' '.
				'('.self::TEXT_AppURL.')');
		}
		imagepng($resized,NULL,self::PNG_Compress,PNG_NO_FILTER);
		if ($die)
			die;
	}

	/**
		Generate a blank image for use as a placeholder
			@param $dimx integer
			@param $dimy integer
			@param $bg string
			@param $die boolean
			@public
	**/
	static function fakeImage($dimx,$dimy,$bg=0xEEE,$die=TRUE) {
		list($r,$g,$b)=self::rgb($bg);
		$img=imagecreatetruecolor($dimx,$dimy);
		$bg=imagecolorallocate($img,$r,$g,$b);
		imagefill($img,0,0,$bg);
		if (PHP_SAPI!='cli' && !headers_sent()) {
			header(self::HTTP_Content.': image/png');
			header(self::HTTP_Powered.': '.self::TEXT_AppName.' '.
				'('.self::TEXT_AppURL.')');
		}
		imagepng($img,NULL,self::PNG_Compress,PNG_NO_FILTER);
		if ($die)
			die;
	}

	/**
		Grab HTML page and render using WebKit engine;
		Crop image and generate thumbnail, if specified
			@param $url string
			@param $dimx integer
			@param $dimy integer
			@param $cropw integer
			@param $croph integer
			@param $die boolean
			@public
	**/
	static function screenshot(
		$url,$dimx=0,$dimy=0,$cropw=0,$croph=0,$die=TRUE) {
		$file=self::$vars['TEMP'].$_SERVER['SERVER_NAME'].
			'.scr.'.self::hash($url).'.jpg';
		// Map OS to folder location
		$exec=array(
			'Windows|WINNT'=>'windows',
			'Darwin'=>'osx',
			'Linux'=>'linux'
		);
		foreach ($exec as $os=>$dir)
			if (preg_match('/'.$os.'/i',PHP_OS)) {
				$win=($dir=='windows');
				// Suppress text output
				$null=$win?'nul':'null';
				exec(($win?'start /b ':'').
					self::$vars['EXTERNAL'].$dir.'/wkhtmltoimage '.
					($cropw?('--crop-w '.$cropw.' '):'').
					($croph?('--crop-h '.$croph.' '):'').
					$url.' '.$file.' >'.$null.' 2>'.$null);
				break;
			}
		if (is_file($file)) {
			if ($dimx && $dimy)
				self::thumb($file,$dimx,$dimy,FALSE);
			elseif (PHP_SAPI!='cli' && !headers_sent()) {
				header(self::HTTP_Content.': image/png');
				header(self::HTTP_Powered.': '.self::TEXT_AppName.' '.
					'('.self::TEXT_AppURL.')');
				echo self::getfile($file);
			}
			unlink($file);
		}
		if ($die)
			die;
	}

	/**
		Class initializer
			@public
	**/
	static function onload() {
		if (!extension_loaded('gd')) {
			// GD extension required
			trigger_error(sprintf(self::TEXT_PHPExt,'gd'));
		}
		if (!isset(self::$vars['EXTERNAL']))
			self::$vars['EXTERNAL']=self::$vars['ROOT'];
		if (!isset(self::$vars['FONTS']))
			self::$vars['FONTS']=self::$vars['ROOT'];
		if (!isset(self::$vars['BGCOLOR']))
			self::$vars['BGCOLOR']=self::GFX_BGColor;
		if (!isset(self::$vars['FGTRANS']))
			self::$vars['FGTRANS']=self::GFX_Transparency;
		if (!isset(self::$vars['IBLOCKS']))
			self::$vars['IBLOCKS']=self::GFX_IdBlocks;
		if (!isset(self::$vars['IPIXELS']))
			self::$vars['IPIXELS']=self::GFX_IdPixels;
	}

}
