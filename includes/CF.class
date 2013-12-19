<?php

class CF
// version: 3.10
// date: 2013-11-15
{
	static function MenuURIis($uri, $current_uri = NULL, $case_sensitive = false)
	// version: 1.0
	// date: 2011-03-16
	{
		if (is_null($current_uri))
		{
			$current_uri_parts = explode ("?", $_SERVER["REQUEST_URI"]);
			$current_uri = $current_uri_parts[0];
		}

		if (!$case_sensitive)
		{
			$uri = strtolower($uri);
			$current_uri = strtolower($current_uri);
		}
		
		$current_uri = @iconv("utf-8", "cp1251", urldecode($current_uri));
		
		$uri = @iconv("utf-8", "cp1251", urldecode($uri));

		return ($uri === $current_uri);
	}
		
	
	static function URIis($uri, $current_uri = NULL, $case_sensitive = false)
	// version: 1.1
	// date: 2010-11-16
	{
		if (is_null($current_uri))
		{
			$current_uri = $_SERVER["REQUEST_URI"];
		}

		if (!$case_sensitive)
		{
			$uri = strtolower($uri);
			$current_uri = strtolower($current_uri);
		}
		
		$current_uri = @iconv("utf-8", "cp1251", urldecode($current_uri));
		
		$uri = @iconv("utf-8", "cp1251", urldecode($uri));
		
		// ак то непон€тно работает
		//if (!strpos($current_uri, "?"))  /*дабы не было глюков с get'ом*/
			//$current_uri = @iconv("utf-8", "cp1251", urldecode($current_uri));
		
		//if (isset($_GET["mode"]) && $_GET["mode"]=="json")  /*дабы не было глюков с а€ксом*/
			//$uri = @iconv("utf-8", "cp1251", urldecode($uri));

		return ($uri === $current_uri);
	}



	static function URIin($uri, $current_uri = NULL, $case_sensitive = false)
	// version: 1.1
	// date: 2008-02-28
	{
		if (is_null($current_uri))
		{
			$current_uri = $_SERVER["REQUEST_URI"];
		}

		if (!$case_sensitive)
		{
			$uri = strtolower($uri);
			$current_uri = strtolower($current_uri);
		}
		
		$current_uri = @iconv("utf-8", "cp1251", urldecode($current_uri));
		
		$uri = @iconv("utf-8", "cp1251", urldecode($uri));

		return (strpos($current_uri, $uri) === 0);
	}



	static function BreakByLbr($input, $lbr = false)
	// version: 1.1
	// date: 2007-11-10
	{	
		if (!$lbr)
		{			
			$array = array("\r\n", "\r", "\n");		
			
			foreach ($array as $candidate)
			{
				if (substr_count($input, $candidate))
				{
					$lbr = $candidate;
					break;
				}
			}
			
			if (!$lbr)
			{
				$lbr = reset($array);
			}
		}	
		
		return explode($lbr, $input);
	}


	static function EntityDecode($input, $cp = NULL)
	// version: 1.1
	// date: 2007-11-02
	{
		return html_entity_decode($input, ENT_NOQUOTES, CF::Seek4NonEmptyStr($cp, "cp1251"));
	}


	static function EntityEncode($input, $cp = NULL)
	// version: 1.1
	// date: 2007-11-02
	{
		return htmlspecialchars($input, ENT_NOQUOTES, CF::Seek4NonEmptyStr($cp, "cp1251"));
	}


	static function FullURLdecode($input, $cp = NULL)
	// version: 1.2
	// date: 2007-11-02
	{
		return CF::EntityDecode(urldecode($input), $cp);
	}


	static function ValidateString($input, $valid_chars = NULL, $min_len = NULL, $max_len = NULL)
	// version: 1.2
	// date: 2007-11-02
	{
		if (!is_string($input))
		{
			return false;
		}

		$valid_chars = CF::Seek4NonEmptyStr($valid_chars, "a-zA-Z0-9_-");

		$output = !preg_match("/[^$valid_chars]/", $input);

		if (CF::IsNaturalNum($min_len))
		{
			$output = ($output && (strlen($input) >= $min_len));
		}

		if (CF::IsNaturalNum($max_len))
		{
			$output = ($output && (strlen($input) <= $max_len));
		}

		return $output;
	}


	static function ValidateEmail($input)
	// version: 1.1
	// date: 2008-06-25
	{
		return preg_match("/[a-z0-9_\.-]+@[a-z0-9_\.-]*[a-z0-9_-]+\.[a-z]{2,}/i", $input);
	}



	static function RefineText($input, $extended_tags_stripping = true, $extended_space_handling = true, $simplify_chars = true, $cp = NULL)
	// version: 1.2
	// date: 2008-04-11
	{
		if ($extended_space_handling)
		{
			$input = str_replace("</", " </", $input);                          // inserting spaces before closing double-tags
			$input = str_replace("/>", "/> ", $input);                          // inserting spaces after single-tags
			$input = str_replace(array("\r\n", "\n", "\r", "\t"), " ", $input); // converting newlines and tabs to spaces
			$input = str_replace("&nbsp;", " ", $input);                        // replacing non-break spaces to regular ones
		}

		if ($extended_tags_stripping)
		{
			preg_replace(array(			                                            // wiping off style, script and noscript tags
					"/<style[\S\s]*?>[\S\s]*?<\/style>/i",
					"/<script[\S\s]*?>[\S\s]*?<\/script>/i",
					"/<noscript[\S\s]*?>[\S\s]*?<\/noscript>/i",
					), "", $input);
		}

		$input = strip_tags($input);                                          // stripping remaining tags
		$input = CF::EntityDecode($input, $cp);                               // decoding all HTML entities known for given codepage

		if ($extended_space_handling)
		{
			while (is_int(strpos($input, "  ")))
			{
				$input = str_replace("  ", " ", $input);                          // putting off double spaces
			}
		}

		if ($simplify_chars)
		{
			$input = str_replace(array("Ђ", "ї", "Д", "У", "Ф"), "\"", $input); // simplifying double quotes
			$input = str_replace(array("С", "Т"), "'", $input);                 // simplifying single quotes
			$input = str_replace(array("Ч", "Ц"), "-", $input);                 // simplifying en- and em-dashes
			$input = str_replace(array("Е"), "...", $input);                    // simplifying dots
		}

		return $input;
	}



	static function Text2Str($input, $decode_entities = false, $replace_brs_with_spaces = false, $cp = NULL)
	// version: 1.4
	// date: 2007-11-02
	{
		if ($replace_brs_with_spaces)
		{
			$input = preg_replace("/<br[^>]*>/i", " ", $input);
		}

		$input = strip_tags(str_replace(array("\r\n", "\n", " \r\n", " \n"), " ", $input));

		if ($decode_entities)
		{
			$input = CF::EntityDecode($input, $cp);
		}

		return $input;
	}


	static function HSClite($text)
	{
	// version: 1.0
	// date: 2007-05-24
		$text = str_replace("\"", "&quot;", $text);
		$text = str_replace("<", "&lt;", $text);
		$text = str_replace(">", "&gt;", $text);
		return $text;
	}


	static function DisplayCharLen($input, $cp = NULL)
	// version: 1.21
	// date: 2008-01-28
	{
		return strlen(CF::Text2Str($input, true, false, $cp));
	}


	static function LimitStr($input, $max_size, $cp = NULL)
	// version: 1.3
	// date: 2008-01-28
	{
		$decoded_input = CF::Text2Str($input, true, true, $cp);
		return (strlen($decoded_input) > $max_size) ? (CF::EntityEncode(substr($decoded_input, 0, $max_size - 1), $cp) . "&hellip;") : CF::EntityEncode($decoded_input, $cp);
	}


	static function JsEscape($input)
	// version: 1.01
	// date: 2007-04-30
	{
		foreach (array("'", "€") as $char)
		{
			$input = str_replace($char, "\\$char", $input);
		}

		return $input;
	}


	static function Seek4Str($input, $replace = "")
	// version: 1.3
	// date: 2007-04-30
	{
		if (!is_string($input))
		{
			$input = $replace;
		}

		return $input;
	}


	static function IsNonEmptyStr($input)
	// version: 1.1
	// date: 2007-05-22
	{
		return is_string($input) && ($input !== "");
	}


	static function Seek4NonEmptyStr($input, $replace)
	// version: 1.4
	// date: 2007-11-02
	{
		if (!CF::IsNonEmptyStr($input))
		{
			$input = $replace;
		}

		return $input;
	}


	static function Seek4Content($input, $replace)
	// version: 1.2
	// date: 2007-11-02
	{
		if (!CF::IsNonEmptyStr($input) && !is_numeric($input))
		{
			$input = $replace;
		}

		return $input;
	}


	static function FindKey($key, $arr = NULL, $else_return = false)
	// version: 1.01
	// date: 2007-04-30
	{
		if (!is_array($arr))
		{
			$arr = $_POST;
		}

		return isset($arr[$key]) ? $arr[$key] : $else_return;
	}


	static function IsNonEmptyArr($input)
	// version: 1.01
	// date: 2006-10-05
	{
		return is_array($input) && count($input);
	}


	static function Seek4NonEmptyArr($input, $replace)
	// version: 1.1
	// date: 2007-11-02
	{
		if (!CF::IsNonEmptyArr($input))
		{
			$input = $replace;
		}

		return $input;
	}


	static function RemoveEmptyElems($arr, $remove_only_str = false)
	{
	// version: 1.02
	// date: 2007-04-30
		foreach ($arr as $key => $val)
		{
			if (($remove_only_str && ($val === "")) || (!$remove_only_str && !$val))
			{
				unset($arr[$key]);
			}
		}

		return $arr;
	}


	static function ImplodeNonEmpty($arr, $glue = " ")
	{
	// version: 1.2
	// date: 2007-11-02
		return implode($glue, CF::RemoveEmptyElems($arr));
	}


	static function IsRichArr($input, $remove_only_str = false)
	// version: 1.2
	// date: 2007-11-02
	{
		return is_array($input) && count(CF::RemoveEmptyElems($input, $remove_only_str));
	}


	static function OneDim($arr, $urlencode = true)
	// version: 1.3
	// date: 2007-11-02
	{
		$output = array();
		CF::OneDimArr($arr, $output, "", $urlencode);
		return $output;
	}

	static function OneDimArr($arr, &$output, $key_prefix, $urlencode)
	// version: 1.3
	// date: 2007-11-02
	{
		foreach ($arr as $key => $elem)
		{
			$key_full = $key_prefix ? "$key_prefix[$key]" : $key;

			if (is_array($elem))
			{
				CF::OneDimArr($elem, $output, $key_full, $urlencode);
			}

			else
			{
				$output[$urlencode ? rawurlencode($key_full) : $key_full] = $urlencode ? rawurlencode($elem) : $elem;
			}
		}
	}
	
	static function ArrMap($arr, $func)
	// version: 1.0
	// date: 2013-07-03
	{
		foreach($arr as $key => $elem){
			if(!is_array($elem)) $arr[$key] = $func($elem);
			else $arr[$key] = CF::ArrMap($elem, $func); 
		}
		return $arr; 
	}
	
	static function IsUtf8(&$data, $is_strict = true)
	// version: 1.0
	// date: 2013-07-18
	{
		if (is_array($data))
		{
			foreach ($data as $k => &$v) if (! is_utf8($v, $is_strict)) return false;
			return true;
		}
		elseif (is_string($data))
		{
			/*
			 –ег. выражени€ имеют внутренние ограничени€ на длину повторов шаблонов поиска *,  , {x,y}
			равное 65536, поэтому используем preg_replace() вместо preg_match()
			*/
			$result = $is_strict ?
			preg_replace('/(?>[\x09\x0A\x0D\x20-\x7E]           # ASCII
                                  | [\xC2-\xDF][\x80-\xBF]            # non-overlong 2-byte
                                  |  \xE0[\xA0-\xBF][\x80-\xBF]       # excluding overlongs
                                  | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
                                  |  \xED[\x80-\x9F][\x80-\xBF]       # excluding surrogates
                                  |  \xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
                                  | [\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
                                  |  \xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
                                 )*
                                /sx', '', $data) :
	                                #это рег. выражение провер€ет более широкий диапазон ASCII [\x00-\x7E]
			preg_replace('/.*/su', '', $data);
			if (function_exists('preg_last_error'))
	        {
						if (preg_last_error() === PREG_NO_ERROR) return strlen($result) === 0;
						if (preg_last_error() === PREG_BAD_UTF8_ERROR) return false;
				}
				elseif (is_string($result)) return strlen($result) === 0;
		
				#в этом месте произошла ошибка выполнени€ регул€рного выражени€
				#провер€ем еще одним, но более медленным способом:
				if (! function_exists('utf8_check')) include_once 'utf8_check.php';
				return utf8_check($data, $is_strict);
		}
		elseif (is_scalar($data) || is_null($data)) return true;  #~ null, integer, float, boolean
		#~ object or resource
		trigger_error('Scalar, null or array type expected, ' . gettype($data) . ' given ', E_USER_WARNING);
			return false;
	}
	
	static function Win2Utf($input)
	// version: 1.0
	// date: 2013-07-18
	{
		if(is_array($input)) {
			foreach($input as $k => &$v) {
				$v = CF::Win2Utf($v); 
			}
			return $input; 
		}
		if(CF::IsUtf8($input)){ return $input; }
		if(function_exists("iconv")) return iconv("cp1251","utf-8",$input);
		$t = "";
		$c209 = chr(209); $c208 = chr(208); $c129 = chr(129);
		for($i=0; $i<strlen($input); $i++)    {
			$c=ord($input[$i]);
			if ($c>=192 and $c<=239) $t.=$c208.chr($c-48);
			elseif ($c>239) $t.=$c209.chr($c-112);
			elseif ($c==184) $t.=$c209.chr(145);
			elseif ($c==168) $t.=$c208.$c129;
			else $t.=$input[$i];
		}
		return $t;
	}
	
	static function Utf2Win($input) 
	// version: 1.0
	// date: 2013-07-18
	{
	  if(function_exists("iconv")) return iconv("utf-8", "cp1251", $input);
	  //if(!preg_match("//u", $input)){ return $input; }
	  $str = "";
	  for($i = 0; $i < strlen($input);){
	    $ch = $input[$i];
	    $code = ord($ch);//printf("\u%04x", $code);
	    if($code >> 7 == 0) { $str .= $ch; $l = 1;}
	    if($code >> 5 == 0x06){
	      $l = 2;
	      $ch1 = $code & 0x1F;
	      $ch2 = (ord($input[$i+1]) & 0x3F);
	
	      $res = $ch2 + ($ch1 << 6);
	      if($res == 0x401)
	      {
	        $res = 0xA8;
	      }
	      elseif($res == 0x451)
	      {
	        $res = 0xB8;
	      }
	      else
	      {
	        $res -= 848;
	      }
	      $str .= chr($res);
	    }
	    $i += $l;
	  }
	  return $str;
	}

	static function IsOdd($num)
	// version: 1.0
	// date: 2007-??-??
	{
		return (bool)($num % 2);
	}


	static function IsNaturalNum($input)
	// version: 1.01
	// date: 2006-10-05
	{
		return is_int($input) && ($input >= 1);
	}


	static function Seek4NaturalNum($input, $replace)
	// version: 1.2
	// date: 2007-11-02
	{
		if (!CF::IsNaturalNum($input))
		{
			$input = $replace;
		}

		return $input;
	}


	static function IsNaturalNumeric($input)
	// version: 1.3
	// date: 2007-11-10
	{
		if (is_numeric($input))
		{
			$intval = intval($input);
			return (floatval($input) == $intval) && CF::IsNaturalNum($intval) && !is_int(strpos($input, "."));
		}

		else
		{
			return false;
		}
	}


	static function Seek4NaturalNumeric($input, $replace)
	// version: 1.3
	// date: 2007-11-02
	{
		if (!CF::IsNaturalNumeric($input))
		{
			$input = $replace;
		}

		return $input;
	}


	static function IsNonNegativeInt($input)
	// version: 1.01
	// date: 2006-10-05
	{
		return is_int($input) && ($input >= 0);
	}


	static function Seek4NonNegativeInt($input, $replace = 0)
	// version: 1.3
	// date: 2007-11-02
	{
		if (!CF::IsNonNegativeInt($input))
		{
			$input = $replace;
		}

		return $input;
	}


	static function IsNaturalOrZeroNumeric($input)
	// version: 1.2
	// date: 2007-11-02
	{
		if (is_numeric($input))
		{
			$intval = intval($input);
			return (floatval($input) == $intval) && ($intval >= 0);
		}

		else
		{
			return false;
		}
	}


	static function Seek4NaturalOrZeroNumeric($input, $replace = 0)
	// version: 1.1
	// date: 2007-11-02
	{
		if (!CF::IsNaturalOrZeroNumeric($input))
		{
			$input = $replace;
		}

		return $input;
	}




	static function IsIntNumeric($input)
	// version: 1.0
	// date: 2008-03-13
	{
		if (is_numeric($input))
		{
			$intval = intval($input);
			return (floatval($input) == $intval);
		}

		else
		{
			return false;
		}
	}


	static function Seek4IntNumeric($input, $replace = 0)
	// version: 1.0
	// date: 2008-03-13
	{
		if (!CF::IsIntNumeric($input))
		{
			$input = $replace;
		}

		return $input;
	}


	static function Seek4Bool($input, $replace = NULL)
	// version: 1.2
	// date: 2007-04-30
	{
		if (!is_bool($input))
		{
			$input = $replace;
		}

		return $input;
	}


	static function FormatFilesize($input, $array = NULL)
	// version: 1.1
	// date: 2007-11-11
	{
		if (!$array)
		{
			$array = array("байт", " Ѕ", "ћЅ");
		}

		if (is_file($input))
		{
			$input = filesize($input);
		}

		if ($input < 1024)
		{
			$unit = $array[0];
		}

		elseif (isset($array[1]) && (($input = $input / 1024) < 1024))
		{
			$unit = $array[1];
		}

		elseif (isset($array[2]) && (($input = $input / 1024) < 1024))
		{
			$unit = $array[2];
		}

		elseif (isset($array[3]) && (($input = $input / 1024) < 1024))
		{
			$unit = $array[3];
		}

		elseif (isset($array[4]) && (($input = $input / 1024) < 1024))
		{
			$unit = $array[4];
		}

		return str_replace(" ", "&nbsp;", number_format($input, ($input >= 10) ? 1 : 2, ",", " ") . " $unit");
	}


	static function Seek4Function($primary, $secondary)
	// version: 1.0
	// date: 2007-05-23
	{
		return function_exists($primary) ? $primary : (function_exists($secondary) ? $secondary : false);
	}


	static function ResizeImage($input_file, $output_file, $mode = NULL, $type = NULL, $input_width = NULL, $input_height = NULL, $output_width = NULL, $output_height = NULL, $allow_enlarge = false)
	// version: 1.7
	// date: 2007-11-02
	{
		if (!is_numeric($type) || !is_numeric($input_width) || !is_numeric($input_height))
		{
			if ($result = @getimagesize($input_file))
			{
				list($input_width, $input_height, $type) = $result;
			}

			else
			{
				return false;
			}
		}

		$types = array
		(
			1 => array("imagecreatefromgif", "imagegif"),
			2 => array("imagecreatefromjpeg", "imagejpeg"),
			3 => array("imagecreatefrompng", "imagepng"),
		);

		if (!isset($types[$type]))
		{
			return false;
		}

		if (!file_exists($input_file))
		{
			return false;
		}

		if (is_null($mode))
		{
			$mode = 0; // 0 - вписать, 1 - обрезать
		}


		switch ($mode)
		{
			case 0:
				$do_resize = ($allow_enlarge || ($input_width > $output_width) || ($input_height > $output_height));
				break;

			case 1:
				$do_resize = ($allow_enlarge || (($width_ratio = $input_width / $output_width) == ($height_ratio = $input_height / $output_height)));
				break;
		}


		if ($do_resize)
		{
			$input_image = $types[$type][0]($input_file);


			if ($mode == 0)
			{
				if ($allow_enlarge || ($input_width > $output_width) || ($input_height > $output_height))
				$resized_width = $input_width;
				$resized_height = $input_height;

				$ratio = max($input_width / $output_width, $input_height / $output_height);
				$output_width = $input_width / $ratio;
				$output_height = $input_height / $ratio;

				$input_x = $input_y = 0;
			}

			elseif ($width_ratio < $height_ratio)
			{
				$resized_width = $input_width;
				$resized_height = round($output_height * $width_ratio);
				$input_x = 0;
				$input_y = round(($input_height - $resized_height) / 2);
			}

			else
			{
				$resized_width = round($output_width * $height_ratio);
				$resized_height = $input_height;
				$input_x = round(($input_width - $resized_width) / 2);
				$input_y = 0;
			}


			if (!$function = CF::Seek4Function("imagecreatetruecolor", "imagecreate"))
			{
				return false;
			}

			$output_image = $function($output_width, $output_height);


			if (!$function = CF::Seek4Function("imagecopyresampled", "imagecopyresized"))
			{
				return false;
			}

			$function($output_image, $input_image, 0, 0, $input_x, $input_y, $output_width, $output_height, $resized_width, $resized_height);

			return $types[$type][1]($output_image, $output_file);
		}

		else
		{
			return copy($input_file, $output_file);
		}
	}


	static function Redirect($url = NULL, $referer = NULL)
	// version: 1.4
	// date: 2007-11-02
	{
		header("Keep-Alive: 666");
		header("Location: " . CF::Seek4NonEmptyStr($url, $_SERVER["REQUEST_URI"]), true, 303);
		
		exit();
	}


	static function Debug($input)
	// version: 1.0
	// date: 2007-??-??
	{
		echo "\n<pre>";
		print_r($input);
		echo "</pre>\n";
	}
}

?>