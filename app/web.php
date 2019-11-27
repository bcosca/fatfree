<?php

namespace App;

class Web extends Controller {

	function get($f3) {
		$test=new \Test;
		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);
		$web=\Web::instance();
		$test->expect(
			$web->slug($text='Ñõw is the tîme~for all good mên. to cóme! to the aid 0f-thëir_côuntry')==
				'now-is-the-time-for-all-good-men-to-come-to-the-aid-0f-their-country',
			'Convert to URL-friendly string'
		);
		$test->expect(
			$web->mime('test.html')=='text/html' &&
			$web->mime('xyz.htm')=='text/html' &&
			$web->mime('nude.jpeg')=='image/jpeg' &&
			$web->mime('sexy.jpg')=='image/jpeg',
			'Auto-detect MIME type using file extension'
		);
		$now=microtime(TRUE);
		$file=$f3->get('UI').'images/wallpaper.jpg';
		ob_start();
		$web->send($file,NULL,$kbps=256,FALSE,NULL,FALSE);
		$out=ob_get_clean();
		header_remove('Content-Type');
		$test->expect(
			($elapsed=microtime(TRUE)-$now)>
				($size=filesize($file)/1024)/$kbps,
			'Send '.round($size,1).'KB file '.
			'@'.$kbps.' KBps (MIME type auto-detected): '.
				round($elapsed,2).' secs'
		);
		$f3->set('UPLOADS',$f3->get('TEMP'));
		$f3->route('PUT /upload/@filename',
			function() use($web) { $web->receive(); }
		);
		$f3->mock('PUT /upload/'.basename($file),NULL,NULL,$f3->read($file));
		$test->expect(
			is_file($target=$f3->get('UPLOADS').basename($file)),
			'Upload file via PUT'
		);
		@unlink($target);
		$_SERVER['HTTP_ACCEPT']='application/xml;q=0.1, text/html; q=0.5, text/*; q=0.01 , application/json;q=0, text/html;level=2, text/html;level=1;q=0.5,application/xhtml+xml ; q=0.1';
		$test->expect(
			$web->acceptable()==[
				'text/html;level=2'=>1,
				'text/html;level=1'=>0.5,
				'text/html'=>0.5,
				'application/xml'=>0.1,
				'application/xhtml+xml'=>0.1,
				'text/*'=>0.01,
				'application/json'=>0
			] &&
			$web->acceptable(['text/html','text/html;level=1','text/html;level=2','text/html;level=3'])=='text/html;level=2' &&
			$web->acceptable('image/jpeg')===FALSE &&
			$web->acceptable('application/json')===FALSE &&
			$web->acceptable('text/javascript')=='text/javascript',
			'Acceptable MIME types'
		);
		$f3->clear('ROUTES');
		$f3->clear('BODY');
		$f3->set('CACHE',TRUE);
		$now=microtime(TRUE);
		$test->expect(
			$web->minify('css/simple.css',NULL,FALSE)==
				'html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,article,aside,canvas,details,embed,figure,figcaption,footer,header,hgroup,menu,nav,output,ruby,section,summary,time,mark,audio,video{margin:0;padding:0;border:0;font-size:100%;font:inherit;vertical-align:baseline}article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section{display:block}body{line-height:1}ol,ul{list-style:none}blockquote,q{quotes:none}blockquote:before,blockquote:after,q:before,q:after{content:\'\';content:none}table{border-collapse:collapse;border-spacing:0}div *{text-align:center}#content{border:1px #000 solid;text-shadow:#ccc -1px -1px 0px}tr:nth-child(odd) td{line-height:1.2em}h1[name] span{font-size:12pt}.sprite{background:url(./test.jpg) no-repeat}@media(min-width:768px) and (max-width:979px){body{background:green}}.widget>div :first-child{margin-top:0}.widget>div:first-child{margin-top:0}',
			'Minify CSS ('.round(1e3*(microtime(TRUE)-$now),1).' msecs)'
		);
		$now=microtime(TRUE);
		$test->expect(
			$web->minify('js/underscore.js',NULL,FALSE)==
				\View::instance()->render('js/underscore.min.js'),
			'Minify Javascript ('.round(1e3*(microtime(TRUE)-$now),1).' msecs)'
		);
		$now=microtime(TRUE);
		$test->expect(
			$web->minify('js/operators.js',NULL,FALSE)==
				'(this.id="ui-id-"+ ++a);var a=5;var b="test"+ ++a;',
			'Minify tricky JS ('.round(1e3*(microtime(TRUE)-$now),1).' msecs)'
		);
		$f3->UI = 'ui2/,ui/';
		$min=$web->minify('css/theme.css,css/simple.css',null,false);
		$test->expect(
			strpos($min,'html{height:100vh;')!==FALSE &&
			strpos($min,'html{height:100%;')===FALSE
		,'Minify from multiple UI paths');
		$f3->UI = 'ui/';

		foreach ($f3->split('curl,stream,socket') as $wrapper) {
			if (preg_match('/curl/i',$wrapper) &&
				extension_loaded('curl') ||
				preg_match('/stream/i',$wrapper) &&
				ini_get('allow_url_fopen') ||
				preg_match('/socket/i',$wrapper) &&
				function_exists('fsockopen')) {
				$web->engine($wrapper);
				$now=microtime(TRUE);
				$test->expect(
					$req=@$web->request($url='http://www.google.com/'),
					'HTTP request ('.$url.') using '.$req['engine'].' '.
					'('.round(1e3*(microtime(TRUE)-$now),1).' msecs)'
				);
				$now=microtime(TRUE);
				$test->expect(
					@$web->request('pingback2?page=pingback/client'),
					'HTTP request (local resource: '.
					round(1e3*(microtime(TRUE)-$now),1).' msecs)'
				);
				$now=microtime(TRUE);
				$test->expect(
					is_array($rss=$web->rss(
						$url='https://wordpress.org/news/feed/')),
					'RSS feed ('.$url.') '.
					round(1e3*(microtime(TRUE)-$now),1).' msecs'
				);
			}
		}
		$test->expect(
			$whois=$web->whois('sourceforge.net'),
			'WHOIS: '.nl2br($f3->stringify($whois))
		);
		$test->expect(
			$filler=nl2br(wordwrap($web->filler(3))),
			'Filler (standard):<br />'.$filler
		);
		$test->expect(
			$filler=nl2br(wordwrap($web->filler(5,20,FALSE))),
			'Filler (random):<br />'.$filler
		);
		$f3->clear('CACHE');
		$f3->set('ESCAPE',FALSE);
		header_remove();
		$f3->set('results',$test->results());
	}

}
