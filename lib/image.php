<?php

//! Image manipulation tools
class Image {

	//@{ Messages
	const
		E_Color='Invalid color specified: %s';
	//@}

	//@{ Positional cues
	const
		POS_Left=1,
		POS_Center=2,
		POS_Right=4,
		POS_Top=8,
		POS_Middle=16,
		POS_Bottom=32;
	//@}

	private
		//! Source filename
		$file,
		//! Image resource
		$data,
		//! Background color
		$bg=array(255,255,255),
		//! Filter count
		$count=0;

	/**
		Convert RGB hex triad to array
		@return array|FALSE
		@param $color int
	**/
	function rgb($color) {
		$hex=str_pad($hex=dechex($color),$color<4096?3:6,'0',STR_PAD_LEFT);
		if (($len=strlen($hex))>6)
			user_error(sprintf(self::E_Color,'0x'.$hex));
		$color=str_split($hex,$len/3);
		foreach ($color as &$hue)
			$hue=hexdec(str_repeat($hue,6/$len));
		return $color;
	}

	/**
		Invert image
		@return object
	**/
	function invert() {
		imagefilter($this->data,IMG_FILTER_NEGATE);
		return $this->save();
	}

	/**
		Adjust brightness (range:-255 to 255)
		@return object
		@param $level int
	**/
	function brightness($level) {
		imagefilter($this->data,IMG_FILTER_BRIGHTNESS,$level);
		return $this->save();
	}

	/**
		Adjust contrast (range:-100 to 100)
		@return object
		@param $level int
	**/
	function contrast($level) {
		imagefilter($this->data,IMG_FILTER_CONTRAST,$level);
		return $this->save();
	}

	/**
		Convert to grayscale
		@return object
	**/
	function grayscale() {
		imagefilter($this->data,IMG_FILTER_GRAYSCALE);
		return $this->save();
	}

	/**
		Adjust smoothness
		@return object
		@param $level int
	**/
	function smooth($level) {
		imagefilter($this->data,IMG_FILTER_SMOOTH,$level);
		return $this->save();
	}

	/**
		Emboss the image
		@return object
	**/
	function emboss() {
		imagefilter($this->data,IMG_FILTER_EMBOSS);
		return $this->save();
	}

	/**
		Apply sepia effect
		@return object
	**/
	function sepia() {
		imagefilter($this->data,IMG_FILTER_GRAYSCALE);
		imagefilter($this->data,IMG_FILTER_COLORIZE,90,60,45);
		return $this->save();
	}

	/**
		Pixelate the image
		@return object
		@param $size int
	**/
	function pixelate($size) {
		imagefilter($this->data,IMG_FILTER_PIXELATE,$size,TRUE);
		return $this->save();
	}

	/**
		Blur the image using Gaussian filter
		@return object
		@param $selective bool
	**/
	function blur($selective=FALSE) {
		imagefilter($this->data,
			$selective?IMG_FILTER_SELECTIVE_BLUR:IMG_FILTER_GAUSSIAN_BLUR);
		return $this->save();
	}

	/**
		Apply sketch effect
		@return object
	**/
	function sketch() {
		imagefilter($this->data,IMG_FILTER_MEAN_REMOVAL);
		return $this->save();
	}

	/**
		Flip on horizontal axis
		@return object
	**/
	function hflip() {
		$tmp=imagecreatetruecolor(
			$width=$this->width(),$height=$this->height());
		list($r,$g,$b)=$this->bg;
		$bg=imagecolorallocatealpha($tmp,$r,$g,$b,127);
		imagesavealpha($tmp,TRUE);
		imagefill($tmp,0,0,$bg);
		imagecopyresampled($tmp,$this->data,
			0,0,$width-1,0,$width,$height,-$width,$height);
		imagedestroy($this->data);
		$this->data=$tmp;
		return $this->save();
	}

	/**
		Flip on vertical axis
		@return object
	**/
	function vflip() {
		$tmp=imagecreatetruecolor(
			$width=$this->width(),$height=$this->height());
		list($r,$g,$b)=$this->bg;
		$bg=imagecolorallocatealpha($tmp,$r,$g,$b,127);
		imagesavealpha($tmp,TRUE);
		imagefill($tmp,0,0,$bg);
		imagecopyresampled($tmp,$this->data,
			0,0,0,$height-1,$width,$height,$width,-$height);
		imagedestroy($this->data);
		$this->data=$tmp;
		return $this->save();
	}

	/**
		Assign background color
		@return object
		@param $color int
	**/
	function background($color) {
		$this->bg=$color;
	}

	/**
		Resize image (Maintain aspect ratio); Crop relative to center
		if flag is enabled
		@return object
		@param $width int
		@param $height int
		@param $crop bool
	**/
	function resize($width,$height,$crop=TRUE) {
		// Adjust dimensions; retain aspect ratio
		$ratio=($origw=imagesx($this->data))/($origh=imagesy($this->data));
		if (!$crop)
			if ($width/$ratio<=$height)
				$height=$width/$ratio;
			else
				$width=$height*$ratio;
		// Create blank image
		$tmp=imagecreatetruecolor($width,$height);
		list($r,$g,$b)=$this->bg;
		$bg=imagecolorallocatealpha($tmp,$r,$g,$b,127);
		imagesavealpha($tmp,TRUE);
		imagefill($tmp,0,0,$bg);
		// Resize
		if ($crop) {
			if ($width/$ratio<=$height) {
				$cropw=$origh*$width/$height;
				imagecopyresampled($tmp,$this->data,
					0,0,($origw-$cropw)/2,0,$width,$height,$cropw,$origh);
			}
			else {
				$croph=$origw*$height/$width;
				imagecopyresampled($tmp,$this->data,
					0,0,0,($origh-$croph)/2,$width,$height,$origw,$croph);
			}
		}
		else
			imagecopyresampled($tmp,$this->data,
				0,0,0,0,$width,$height,$origw,$origh);
		imagedestroy($this->data);
		$this->data=$tmp;
		return $this->save();
	}

	/**
		Rotate image
		@return object
		@param $angle int
	**/
	function rotate($angle) {
		list($r,$g,$b)=$this->bg;
		$bg=imagecolorallocatealpha($this->data,$r,$g,$b,127);
		$this->data=imagerotate($this->data,$angle,$bg);
		imagesavealpha($this->data,TRUE);
		return $this->save();
	}

	/**
		Apply an image overlay
		@return object
		@param $img object
		@param $align int
	**/
	function overlay(Image $img,$align=NULL) {
		if (is_null($align))
			$align=self::POS_Right|self::POS_Bottom;
		$ovr=imagecreatefromstring($img->dump());
		imagesavealpha($ovr,TRUE);
		$imgw=$this->width();
		$imgh=$this->height();
		$ovrw=imagesx($ovr);
		$ovrh=imagesy($ovr);
		if ($align & self::POS_Left)
			$posx=0;
		if ($align & self::POS_Center)
			$posx=($imgw-$ovrw)/2;
		if ($align & self::POS_Right)
			$posx=$imgw-$ovrw;
		if ($align & self::POS_Top)
			$posy=0;
		if ($align & self::POS_Middle)
			$posy=($imgh-$ovrh)/2;
		if ($align & self::POS_Bottom)
			$posy=$imgh-$ovrh;
		if (empty($posx))
			$posx=0;
		if (empty($posy))
			$posy=0;
		imagecopy($this->data,$ovr,$posx,$posy,0,0,$ovrw,$ovrh);
		return $this->save();
	}

	/**
		Generate identicon
			@return object
			@param $str string
			@param $size int
			@param $blocks int
	**/
	function identicon($str,$size=64,$blocks=4) {
		$sprites=array(
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
		$this->data=imagecreatetruecolor($size,$size);
		list($r,$g,$b)=$this->rgb(mt_rand(0x333,0xCCC));
		$fg=imagecolorallocate($this->data,$r,$g,$b);
		list($r,$g,$b)=$this->bg;
		$bg=imagecolorallocatealpha($this->data,$r,$g,$b,127);
		imagefill($this->data,0,0,$bg);
		$hash=md5($str);
		$ctr=count($sprites);
		$dim=$blocks*(int)($size/$blocks)*2/$blocks;
		for ($j=0,$y=ceil($blocks/2);$j<$y;$j++)
			for ($i=$j,$x=$blocks-1-$j;$i<$x;$i++) {
				$sprite=imagecreatetruecolor($dim,$dim);
				imagefill($sprite,0,0,$bg);
				if ($block=$sprites[
					hexdec($hash[($j*$blocks+$i)*2])%$ctr]) {
					for ($k=0,$pts=count($block);$k<$pts;$k++)
						$block[$k]*=$dim;
					imagefilledpolygon($sprite,$block,$pts/2,$fg);
				}
				$sprite=imagerotate($sprite,
					90*(hexdec($hash[($j*$blocks+$i)*2+1])%4),$bg);
				for ($k=0;$k<4;$k++) {
					imagecopyresampled($this->data,$sprite,
						$i*$dim/2,$j*$dim/2,0,0,$dim/2,$dim/2,$dim,$dim);
					$this->data=imagerotate($this->data,90,$bg);
				}
				imagedestroy($sprite);
			}
		imagesavealpha($this->data,TRUE);
		return $this->save();
	}

	/**
		Return image width
		@return int
	**/
	function width() {
		return imagesx($this->data);
	}

	/**
		Return image height
		@return int
	**/
	function height() {
		return imagesy($this->data);
	}

	//! Send image to HTTP client
	function render() {
		$args=func_get_args();
		$format=$args?array_shift($args):'png';
		if (PHP_SAPI!='cli') {
			header('Content-Type: image/'.$format);
			header('X-Powered-By: '.Base::instance()->get('PACKAGE'));
		}
		call_user_func_array('image'.$format,array($this->data)+$args);
	}

	/**
		Return image as a string
		@return string
	**/
	function dump() {
		$args=func_get_args();
		$format=$args?array_shift($args):'png';
		ob_start();
		call_user_func_array('image'.$format,array($this->data)+$args);
		return ob_get_clean();
	}

	/**
		Save current state
		@return object
	**/
	function save() {
		$fw=Base::instance();
		if (!is_dir($dir=$fw->get('TEMP')))
			mkdir($dir,Base::MODE,TRUE);
		$this->count++;
		$fw->write($dir.'/'.$fw->hash($fw->get('ROOT').$fw->get('BASE')).'.'.
			$fw->hash($this->file).'-'.$this->count.'.png',
			$this->dump());
		return $this;
	}

	/**
		Revert to specified state
		@return object
		@param $state int
	**/
	function restore($state=1) {
		$fw=Base::instance();
		if (is_file($file=($path=$fw->get('TEMP').
			$fw->hash($fw->get('ROOT').$fw->get('BASE')).'.'.
			$fw->hash($this->file).'-').$state.'.png')) {
			if (is_resource($this->data))
				imagedestroy($this->data);
			$this->data=imagecreatefromstring($fw->read($file));
			imagesavealpha($this->data,TRUE);
			foreach (glob($path.'*.png',GLOB_NOSORT) as $match)
				if (preg_match('/-(\d+)\.png/',$match,$parts) &&
					$parts[1]>$state)
					$fw->unlink($match);
			$this->count=$state;
		}
		return $this;
	}

	/**
		Undo most recently applied filter
		@return object
	**/
	function undo() {
		if ($this->count)
			$this->count--;
		return $this->restore($this->count);
	}

	/**
		Instantiate image
		@param $file string
	**/
	function __construct($file=NULL) {
		if ($file) {
			$fw=Base::instance();
			// Create image from file
			$this->file=$file;
			foreach ($fw->split($fw->get('UI')) as $dir)
				if (is_file($dir.$file)) {
					$this->data=imagecreatefromstring($fw->read($dir.$file));
					imagesavealpha($this->data,TRUE);
				}
			$this->save();
		}
	}

	//! Wrap-up
	function __destruct() {
		if (is_resource($this->data)) {
			imagedestroy($this->data);
			$fw=Base::instance();
			$path=$fw->get('TEMP').
				$fw->hash($fw->get('ROOT').$fw->get('BASE')).'.'.
				$fw->hash($this->file);
			foreach (glob($path.'*.png',GLOB_NOSORT) as $match)
				if (preg_match('/-(\d+)\.png/',$match))
					$fw->unlink($match);
		}
	}

}
