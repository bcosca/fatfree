<?php

namespace App;

class Image extends Controller {

	function get($f3) {
		$test=new \F3\Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$test->expect(
			$loaded=extension_loaded('gd'),
			'GD2 extension loaded'
		);
		if ($loaded) {
			$img=new \F3\Image;
			$test->expect(
				$src=$f3->base64(
					$img->captcha('fonts/thunder.ttf')->
					dump(),'image/png'),
				'CAPTCHA<br />'.
				'<img src="'.$src.'" title="CAPTCHA" />'
			);
			$test->expect(
				$src=$f3->base64(
					$img->captcha('fonts/thunder.ttf',32,4,NULL,'',
					0xFF0000,0xFFF000)->dump(),'image/png'),
					'Custom CAPTCHA<br />'.
				'<img src="'.$src.'" title="CAPTCHA" />'
			);
			$test->expect(
				$src=$f3->base64(
					$img->captcha('fonts/thunder.ttf',18,7,NULL,'',
					0xFFFFFF,0x33000000)->dump(),'image/png'),
					'Translucent CAPTCHA<br />'.
				'<img src="'.$src.'" title="CAPTCHA" />'
			);
			$test->expect(
				$src=$f3->base64(
					$img->identicon(md5(mt_rand()),48)->dump(),'image/png'),
				'Identicon<br />'.
				'<img src="'.$src.'" title="Identicon" />'
			);
			$f3->set('file','images/south-park.jpg');
			$img=new \F3\Image($f3->get('file'),TRUE);
			$test->expect(
				$orig=\F3\View::instance()->render('image.htm'),
				'Original image rendered from template<br />'.$orig
			);
			$test->expect(
				$src=$f3->base64($img->dump(),'image/png'),
				'Same image from base64-encoded data URI<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->hflip()->dump(),'image/png'),
				'Horizontal flip<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->undo()->dump(),'image/png'),
				'Undo<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->vflip()->dump(),'image/png'),
				'Vertical flip<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->undo()->invert()->sepia()->sketch()->
					restore()->dump(),'image/png'),
				'Restore<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->invert()->dump(),'image/png'),
				'Invert<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->undo()->grayscale()->dump(),'image/png'),
				'Grayscale<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->undo()->pixelate(10)->dump('png'),'image/png'),
				'Pixelate<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->undo()->sketch()->dump(),'image/png'),
				'Sketch<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->undo()->sepia()->dump(),'image/png'),
				'Sepia<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->undo()->blur()->dump(),'image/png'),
				'Blur<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->undo()->emboss()->dump(),'image/png'),
				'Emboss<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->restore()->crop(25,25,95,95)->dump(),'image/png'),
				'Crop<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->restore()->resize(120,90,FALSE)->
					dump(),'image/png'),
				'Resize (smaller)<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->restore()->resize(200,150,FALSE)->
					dump(),'image/png'),
				'Resize (larger)<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->restore()->resize(100,90)->dump(),'image/png'),
				'Resize/crop horizontal<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->restore()->resize(150,90)->dump(),'image/png'),
				'Resize/crop vertical<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->restore()->rotate(-90)->dump(),'image/png'),
				'Rotate clockwise<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->restore()->rotate(90)->dump(),'image/png'),
				'Rotate anti-clockwise<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$ovr=new \F3\Image('images/watermark.png');
			$ovr->resize(100,38)->rotate(90);
			$test->expect(
				$src=$f3->base64($img->restore()->
					overlay($ovr,\F3\Image::POS_Right|\F3\Image::POS_Middle)->
					dump(),'image/png'),
				'Overlay<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$ovr=new \F3\Image('images/watermark.png');
			$ovr->resize(100,38)->rotate(45);
			$test->expect(
				$src=$f3->base64($img->restore()->
					overlay($ovr,array(65,25),50)->
					dump(),'image/png'),
				'Overlay, 50% transparency, manually aligned<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->restore()->dump('gif'),'image/gif'),
				'Convert to GIF format<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->restore()->dump('jpeg'),'image/jpeg'),
				'Convert to JPEG format<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				$src=$f3->base64($img->restore()->dump('png',NULL,9,PNG_ALL_FILTERS),'image/png'),
				'Dump with additional arguments<br />'.
				'<img src="'.$src.'" '.
					'title="'.$img->width().'x'.$img->height().'" />'
			);
			$test->expect(
				is_resource($img->data()) && imagesx($img->data())===$img->width(),
				'Get image resource'
			);
			unset($img);
			$f3->set('ESCAPE',FALSE);
		}
		$f3->set('results',$test->results());
	}

}
