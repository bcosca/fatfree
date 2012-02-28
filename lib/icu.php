<?php

/**
	Language support module for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package ICU
		@version 2.0.9
**/

//! Language support tools
class ICU extends Base {

	static
		// Languages indexed by ISO 639-1 codes
		$languages=array(
			'aa'=>'Afar',
			'ab'=>'Abkhazian',
			'ae'=>'Avestan',
			'af'=>'Afrikaans',
			'ak'=>'Akan',
			'am'=>'Amharic',
			'an'=>'Aragonese',
			'ar'=>'Arabic',
			'as'=>'Assamese',
			'av'=>'Avaric',
			'ay'=>'Aymara',
			'az'=>'Azerbaijani',
			'ba'=>'Bashkir',
			'be'=>'Belarusian',
			'bg'=>'Bulgarian',
			'bh'=>'Bihari',
			'bi'=>'Bislama',
			'bm'=>'Bambara',
			'bn'=>'Bengali',
			'bo'=>'Tibetan',
			'br'=>'Breton',
			'bs'=>'Bosnian',
			'ca'=>'Catalan',
			'ce'=>'Chechen',
			'ch'=>'Chamorro',
			'co'=>'Corsican',
			'cr'=>'Cree',
			'cs'=>'Czech',
			'cu'=>'Church Slavic',
			'cv'=>'Chuvash',
			'cy'=>'Welsh',
			'da'=>'Danish',
			'de'=>'German',
			'dv'=>'Divehi',
			'dz'=>'Dzongkha',
			'ee'=>'Ewe',
			'el'=>'Greek',
			'en'=>'English',
			'eo'=>'Esperanto',
			'es'=>'Spanish',
			'et'=>'Estonian',
			'eu'=>'Basque',
			'fa'=>'Persian',
			'ff'=>'Fulah',
			'fi'=>'Finnish',
			'fj'=>'Fijian',
			'fo'=>'Faroese',
			'fr'=>'French',
			'fy'=>'Western Frisian',
			'ga'=>'Irish',
			'gd'=>'Scottish Gaelic',
			'gl'=>'Galician',
			'gn'=>'Guarani',
			'gu'=>'Gujarati',
			'gv'=>'Manx',
			'ha'=>'Hausa',
			'he'=>'Hebrew',
			'hi'=>'Hindi',
			'ho'=>'Hiri Motu',
			'hr'=>'Croatian',
			'ht'=>'Haitian',
			'hu'=>'Hungarian',
			'hy'=>'Armenian',
			'hz'=>'Herero',
			'ia'=>'Interlingua',
			'id'=>'Indonesian',
			'ie'=>'Interlingue',
			'ig'=>'Igbo',
			'ii'=>'Sichuan Yi',
			'ik'=>'Inupiaq',
			'io'=>'Ido',
			'is'=>'Icelandic',
			'it'=>'Italian',
			'iu'=>'Inuktitut',
			'ja'=>'Japanese',
			'jv'=>'Javanese',
			'ka'=>'Georgian',
			'kg'=>'Kongo',
			'ki'=>'Kikuyu',
			'kj'=>'Kwanyama',
			'kk'=>'Kazakh',
			'kl'=>'Kalaallisut',
			'km'=>'Khmer',
			'kn'=>'Kannada',
			'ko'=>'Korean',
			'kr'=>'Kanuri',
			'ks'=>'Kashmiri',
			'ku'=>'Kurdish',
			'kv'=>'Komi',
			'kw'=>'Cornish',
			'ky'=>'Kirghiz',
			'la'=>'Latin',
			'lb'=>'Luxembourgish',
			'lg'=>'Ganda',
			'li'=>'Limburgish',
			'ln'=>'Lingala',
			'lo'=>'Lao',
			'lt'=>'Lithuanian',
			'lu'=>'Luba-Katanga',
			'lv'=>'Latvian',
			'mg'=>'Malagasy',
			'mh'=>'Marshallese',
			'mi'=>'Maori',
			'mk'=>'Macedonian',
			'ml'=>'Malayalam',
			'mn'=>'Mongolian',
			'mr'=>'Marathi',
			'ms'=>'Malay',
			'mt'=>'Maltese',
			'my'=>'Burmese',
			'na'=>'Nauru',
			'nb'=>'Norwegian Bokmal',
			'nd'=>'North Ndebele',
			'ne'=>'Nepali',
			'ng'=>'Ndonga',
			'nl'=>'Dutch',
			'nn'=>'Norwegian Nynorsk',
			'no'=>'Norwegian',
			'nr'=>'South Ndebele',
			'nv'=>'Navajo',
			'ny'=>'Chichewa',
			'oc'=>'Occitan',
			'oj'=>'Ojibwa',
			'om'=>'Oromo',
			'or'=>'Oriya',
			'os'=>'Ossetian',
			'pa'=>'Panjabi',
			'pi'=>'Pali',
			'pl'=>'Polish',
			'ps'=>'Pashto',
			'pt'=>'Portuguese',
			'qu'=>'Quechua',
			'rm'=>'Raeto-Romance',
			'rn'=>'Kirundi',
			'ro'=>'Romanian',
			'ru'=>'Russian',
			'rw'=>'Kinyarwanda',
			'sa'=>'Sanskrit',
			'sc'=>'Sardinian',
			'sd'=>'Sindhi',
			'se'=>'Northern Sami',
			'sg'=>'Sango',
			'si'=>'Sinhala',
			'sk'=>'Slovak',
			'sl'=>'Slovenian',
			'sm'=>'Samoan',
			'sn'=>'Shona',
			'so'=>'Somali',
			'sq'=>'Albanian',
			'sr'=>'Serbian',
			'ss'=>'Swati',
			'st'=>'Southern Sotho',
			'su'=>'Sundanese',
			'sv'=>'Swedish',
			'sw'=>'Swahili',
			'ta'=>'Tamil',
			'te'=>'Telugu',
			'tg'=>'Tajik',
			'th'=>'Thai',
			'ti'=>'Tigrinya',
			'tk'=>'Turkmen',
			'tl'=>'Tagalog',
			'tn'=>'Tswana',
			'to'=>'Tonga',
			'tr'=>'Turkish',
			'ts'=>'Tsonga',
			'tt'=>'Tatar',
			'tw'=>'Twi',
			'ty'=>'Tahitian',
			'ug'=>'Uighur',
			'uk'=>'Ukrainian',
			'ur'=>'Urdu',
			'uz'=>'Uzbek',
			've'=>'Venda',
			'vi'=>'Vietnamese',
			'vo'=>'Volapuk',
			'wa'=>'Walloon',
			'wo'=>'Wolof',
			'xh'=>'Xhosa',
			'yi'=>'Yiddish',
			'yo'=>'Yoruba',
			'za'=>'Zhuang',
			'zh'=>'Chinese',
			'zu'=>'Zulu'
		);
	static
		//! Current locale
		$locale;

	/**
		Load appropriate dictionary files
			@public
	**/
	static function load() {
		if (!self::$vars['LANGUAGE']) {
			// Auto-detect
			if (extension_loaded('intl'))
				$def=isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?
					Locale::acceptFromHTTP($_SERVER['HTTP_ACCEPT_LANGUAGE']):
					Locale::getDefault();
			else {
				if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
					$def=preg_replace('/^(\w+-\w+)\b.*/','\1',
						$_SERVER['HTTP_ACCEPT_LANGUAGE']);
				else {
					$def=setlocale(LC_ALL,NULL);
					if (strtoupper(substr(PHP_OS,0,3))=='WIN')
						$def=key(preg_grep('/'.strstr($def,'_',TRUE).'/',
							self::$languages));
					elseif (!preg_match('/^\w{2}(?:_\w{2})?\b/',$def))
						// Environment points to invalid language
						$def='en';
				}
			}
			self::$vars['LANGUAGE']=$def;
		}
		$def=self::$vars['LANGUAGE'];
		$list=array($def);
		if (preg_match('/^\w+\b/',$def,$match)) {
			array_unshift($list,$match[0]);
			if (extension_loaded('intl'))
				Locale::setDefault($match[0]);
			else {
				self::$locale=setlocale(LC_ALL,NULL);
				setlocale(LC_ALL,self::$languages[$match[0]]);
			}
		}
		// Add English as fallback
		array_unshift($list,'en');
		foreach (array_unique($list) as $language) {
			$file=self::fixslashes(self::$vars['LOCALES']).$language.'.php';
			if (is_file($file) && ($trans=require_once $file) &&
				is_array($trans))
				// Combine dictionaries and assign key/value pairs
				F3::mset($trans);
		}
		if (!extension_loaded('intl') &&
			!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			setlocale(LC_ALL,NULL);
	}

	/**
		Return ICU-formatted string
			@return string
			@param $str string
			@param $args mixed
			@public
	**/
	static function format($str,$args) {
		// Format string according to locale rules
		if (extension_loaded('intl'))
			return msgfmt_format_message(Locale::getDefault(),$str,
				is_array($args)?$args:array($args));
		foreach ($args as &$arg)
			if (preg_match('/@\w+\b/',$arg))
				$arg=self::resolve('{{'.$arg.'}}');
		self::$locale=setlocale(LC_ALL,NULL);
		if (preg_match('/\w+\b/',self::$vars['LANGUAGE'],$match))
			setlocale(LC_ALL,self::$languages[$match[0]]);
		$info=localeconv();
		$out=preg_replace_callback(
			'/{(\d+)(?:,(\w+)(?:,(\w+))?)?}/',
			function($expr) use($args,$info) {
				extract($info);
				$arg=$args[$expr[1]];
				if (!isset($expr[2]))
					return $arg;
				if ($expr[2]=='number') {
					if (isset($expr[3]))
						switch ($expr[3]) {
							case 'integer':
								return number_format($arg,0,
									$decimal_point,$thousands_sep);
							case 'currency':
								return $currency_symbol.
									($p_sep_by_space?' ':'').
									number_format($arg,$frac_digits,
										$mon_decimal_point,
										$mon_thousands_sep);
						}
					else
						return sprintf('%f',$arg);
				}
				elseif ($expr[2]=='date')
					return strftime('%x',$arg);
				else
					return $arg;
			},
			$str
		);
		setlocale(LC_ALL,NULL);
		return $out;
	}

}
