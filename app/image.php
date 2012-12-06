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
			'Original image rendered from template<br/>'.$orig
		);
		$test->expect(
			$src=$f3->base64($img->dump('jpeg'),'image/jpeg'),
			'Same image from base64-encoded data URI<br/>'.
			'<img src="'.$src.'" '.
				'title="'.$img->width().'x'.$img->height().'"/>'
		);
		$test->expect(
			$src=$f3->base64($img->hflip()->dump('jpeg'),'image/jpeg'),
			'Horizontal flip<br/>'.
			'<img src="'.$src.'" '.
				'title="'.$img->width().'x'.$img->height().'"/>'
		);
		$test->expect(
			$src=$f3->base64($img->undo()->dump('jpeg'),'image/jpeg'),
			'Undo<br/>'.
			'<img src="'.$src.'" '.
				'title="'.$img->width().'x'.$img->height().'"/>'
		);
		$test->expect(
			$src=$f3->base64($img->vflip()->dump('jpeg'),'image/jpeg'),
			'Vertical flip<br/>'.
			'<img src="'.$src.'" '.
				'title="'.$img->width().'x'.$img->height().'"/>'
		);
		$test->expect(
			$src=$f3->base64($img->undo()->invert()->sepia()->sketch()->
				restore()->dump('jpeg'),'image/jpeg'),
			'Restore<br/>'.
			'<img src="'.$src.'" '.
				'title="'.$img->width().'x'.$img->height().'"/>'
		);
		$test->expect(
			$src=$f3->base64($img->invert()->dump('png'),'image/png'),
			'Invert<br/>'.
			'<img src="'.$src.'" '.
				'title="'.$img->width().'x'.$img->height().'"/>'
		);
		$test->expect(
			$src=$f3->base64($img->undo()->grayscale()->dump('jpeg'),'image/jpeg'),
			'Grayscale<br/>'.
			'<img src="'.$src.'" '.
				'title="'.$img->width().'x'.$img->height().'"/>'
		);
		$test->expect(
			$src=$f3->base64($img->undo()->pixelate(10)->dump('gif'),'image/gif'),
			'Pixelate<br/>'.
			'<img src="'.$src.'" '.
				'title="'.$img->width().'x'.$img->height().'"/>'
		);
		$test->expect(
			$src=$f3->base64($img->undo()->sketch()->dump('png'),'image/png'),
			'Sketch<br/>'.
			'<img src="'.$src.'" '.
				'title="'.$img->width().'x'.$img->height().'"/>'
		);
		$test->expect(
			$src=$f3->base64($img->undo()->sepia()->dump('png'),'image/png'),
			'Sepia<br/>'.
			'<img src="'.$src.'" '.
				'title="'.$img->width().'x'.$img->height().'"/>'
		);
		$test->expect(
			$src=$f3->base64($img->undo()->blur()->dump('png'),'image/png'),
			'Blur<br/>'.
			'<img src="'.$src.'" '.
				'title="'.$img->width().'x'.$img->height().'"/>'
		);
		$test->expect(
			$src=$f3->base64($img->undo()->emboss()->dump('png'),'image/png'),
			'Emboss<br/>'.
			'<img src="'.$src.'" '.
				'title="'.$img->width().'x'.$img->height().'"/>'
		);
		$test->expect(
			$src=$f3->base64($img->restore()->resize(120,90)->dump('png'),'image/png'),
			'Resize (smaller)<br/>'.
			'<img src="'.$src.'" '.
				'title="'.$img->width().'x'.$img->height().'"/>'
		);
		$test->expect(
			$src=$f3->base64($img->restore()->resize(200,150)->dump('png'),'image/png'),
			'Resize (larger)<br/>'.
			'<img src="'.$src.'" '.
				'title="'.$img->width().'x'.$img->height().'"/>'
		);
		$f3->set('ESCAPE',FALSE);
		$f3->set('results',$test->results());
	}

}
