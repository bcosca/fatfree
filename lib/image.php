<?php

//! Image manipulation tools
class Image {

	//@{ Messages
	const
		E_Color='Invalid color specified: %s';
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
			trigger_error(sprintf(self::E_Color,'0x'.$hex));
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
			$width=imagesx($this->data),$height=imagesy($this->data));
		imagecopyresampled($tmp,
			$this->data,0,0,$width-1,0,$width,$height,-$width,$height);
		$this->data=$tmp;
		return $this->save();
	}

	/**
		Flip on vertical axis
		@return object
	**/
	function vflip() {
		$tmp=imagecreatetruecolor(
			$width=imagesx($this->data),$height=imagesy($this->data));
		imagecopyresampled($tmp,
			$this->data,0,0,0,$height-1,$width,$height,$width,-$height);
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
		Resize image (Maintain aspect ratio)
		@return object
		@param $width int
		@param $height int
	**/
	function resize($width,$height) {
		// Adjust dimensions; retain aspect ratio
		$ratio=($oldx=imagesx($this->data))/($oldy=imagesy($this->data));
		if ($width/$ratio<=$height)
			// Adjust height
			$height=$width/$ratio;
		else
			// Adjust width
			$width=$height*$ratio;
		// Create blank image
		$tmp=imagecreatetruecolor($width,$height);
		list($r,$g,$b)=$this->bg;
		$bg=imagecolorallocate($tmp,$r,$g,$b);
		imagefill($tmp,0,0,$bg);
		imagealphablending($tmp,FALSE);
		imagesavealpha($tmp,TRUE);
		// Resize
		imagecopyresampled($tmp,
			$this->data,0,0,0,0,$width,$height,$oldx,$oldy);
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
		$bg=imagecolorallocate($this->data,$r,$g,$b);
		$this->data=imagerotate($this->data,$angle,$bg);
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

	/**
		Send image to HTTP client
		@return NULL
		@param $format string
		@param $quality int
		@param $filters int
	**/
	function render($format='png',$quality=100,$filters=0) {
		if (PHP_SAPI!='cli') {
			header('Content-Type: image/'.$format);
			header('X-Powered-By: '.Base::instance()->get('PACKAGE'));
		}
		eval('image'.$format.'($this->data,NULL,'.$quality.
			($filters?(','.$filters):'').');');
	}

	/**
		Return image as a string
		@return string
		@param $format string
		@param $quality int
		@param $filters int
	**/
	function dump($format='png',$quality=0,$filters=0) {
		ob_start();
		eval('image'.$format.'($this->data,NULL'.
			($quality?(','.$quality.($filters?(','.$filters):'')):'').');');
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
		$fw->write($dir.'/'.$fw->hash($this->file).'-'.$this->count.'.png',
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
			$fw->hash($this->file).'-').$state.'.png')) {
			$this->data=imagecreatefromstring($fw->read($file));
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
				if (is_file($dir.$file))
					$this->data=imagecreatefromstring($fw->read($dir.$file));
			$this->save();
		}
	}

}
