<?php

// version: 1.11
// date: 2007-11-08


if (!function_exists("http_build_query"))
{
	function http_build_query($formdata, $numeric_prefix = NULL, $key_prefix = NULL)
	{
		$output = array();

		foreach ((array) $formdata as $key => $elem)
		{
			$tmp_key = urlencode(is_int($key) ? ($numeric_prefix . $key) : $key);

			if ($key_prefix)
			{
				$tmp_key = $key_prefix . "[" . $tmp_key . "]";
			}

			if (is_array($elem) || is_object($elem))
			{
				$output[] = http_build_query($elem, $numeric_prefix, $tmp_key);
			}

			else
			{
				$output[] = "$tmp_key=" . urlencode($elem);
			}
		}

		return implode(ini_get("arg_separator.output"), $output);
	}
}



if (!function_exists("str_split"))
{
	function str_split($str, $len = 1)
	{
		$parts_qty = ceil(strlen($str) / $len);
		$output = array();

		for ($i = 0; $i < $parts_qty; $i++)
		{
			$output[] = substr($str, $i * $len, $len);
		}

		return $output;
	}
}

?>