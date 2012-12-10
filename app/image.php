<?php

namespace App;

class Image extends Controller {

	function get() {
		$f3=\Base::instance();
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$f3->set('file','images/south-park.jpg');
		$img=new \Image($f3->get('file'));
		$test->expect(
			$orig=\View::instance()->render('image.htm'),
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
			$src=$f3->base64($img->restore()->resize(120,90)->dump(),'image/png'),
			'Resize (smaller)<br />'.
			'<img src="'.$src.'" '.
				'title="'.$img->width().'x'.$img->height().'" />'
		);
		$test->expect(
			$src=$f3->base64($img->restore()->resize(200,150)->dump(),'image/png'),
			'Resize (larger)<br />'.
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
		$f3->set('ESCAPE',FALSE);
		$f3->set('results',$test->results());
	}

}
