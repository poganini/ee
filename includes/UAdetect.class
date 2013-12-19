<?php

class UAdetect
// version: 2.3 alpha 1
// date: 2008-05-18
{
	var $ua_string;
	var $type;
	var $name;
	var $ver_str;
	var $ver;
	var $engine_ver_str;
	var $engine_ver;
	var $os_type;
	var $os_name;
	var $stylable;



	function UAdetect($ua_string = NULL)
	{
// User agent string
		$this->ua_string = htmlspecialchars(CF::Seek4NonEmptyStr($ua_string, getenv("HTTP_USER_AGENT")));

// User agent type
		$types = array(
			"amaya" => true,
			"lynx" => true,
			"elinks" => true,
			"khtml" => "khtml",
			"opera" => true,
			"gecko" => true,
			"ie" => "msie",
			"mozilla_compatible" => "mozilla.*compatible",
			"old_netscape" => "mozilla"
			);

		foreach ($types as $type => $evidence)
		{
			$evidence = CF::Seek4NonEmptyStr($evidence, $type);

			if (preg_match("/$evidence/i", $this->ua_string))
			{
				$this->type = $type;
				break;
			}
		}

		if (is_null($this->type))
		{
			$this->type = "unknown";
		}

// User agent engine version

		if ($this->IsGecko())
		{
			if (strpos($this->ua_string, "rv:"))
			{
				$this->engine_ver_str = preg_replace("/(.*)rv:([\d\.]+)([^)]*)\).*/", "\\2\\3", $this->ua_string);
			}

			else
			{
				$this->engine_ver_str = preg_replace("/(.*) ([\d\.]+)\) Gecko(.*)/", "\\2", $this->ua_string);
			}

			$this->engine_ver = $this->VerStr2Float($this->engine_ver_str, true);

			if (preg_match("/\) Gecko\/\d+.*( \S+\/[\d\w\.]+[^\d\w\.]*)+.*$/i", $this->ua_string))
			{
				preg_replace(
					"/\) Gecko\/\d+.*( (\S+)\/([\d\w\.]+)[^\d\w\.]*)* (\S+)\/([\d\w\.]+)[^\d\w\.]*.*$/ie",
					"\$this->GeckoNameVerEval(\"\\4\", \"\\5\")",
					$this->ua_string
					);
			}

			else
			{
				$this->name = "Mozilla";
				$this->ver_str = $this->engine_ver_str;
				$this->ver = $this->engine_ver;
			}
		}

		elseif ($this->IsKHTML()) // beta feature!!!
		{
			if (preg_match("/safari/i", $this->ua_string))
			{
				$this->name = "Safari";

				if (preg_match("/\) Version\/[\d\.]+ Safari\/[\d\.]+$/i", $this->ua_string))
				{
					preg_replace("/\) Version\/([\d\.]+) Safari\/([\d\.]+)$/ie", "\$this->SafariVerEval(\"\\1\", \"\\2\")", $this->ua_string);
				}
			}

			elseif (preg_match("/konqueror/i", $this->ua_string))
			{
				$this->name = "Konqueror";
			}


			if (preg_match("/KHTML\/([\d\.]+)/i", $this->ua_string))
			{
				$this->ver_str = preg_replace("/.*KHTML\/([\d\.]+).*/i", "\\1" ,$this->ua_string);
				$this->ver = $this->VerStr2Float($this->ver_str);
			}
		}

		else
		{
			$array = array(
				"amaya" => true,
				"opera" => false,
				"konqueror" => false,
				"lynx" => true,
				"safari" => false,
				"ie" => ".*msie",
				"mozilla_compatible" => "mozilla",
				"old_netscape" => "mozilla",
				);

			$prec = isset($array[$this->type]) ? $array[$this->type] : ".*?";

			if ($prec === true)
			{
				$prec = $this->type;
			}

			elseif ($prec === false)
			{
				$prec = ".*$this->type";
			}

			if (preg_match("/^$prec(\/| )[\d\.]+([^\d\.](.*)$|$)/i", $this->ua_string))
			{

				$this->engine_ver_str = preg_replace("/^$prec(\/| )([\d\.]+)([^\d\.](.*)$|$)/i", "\\2", $this->ua_string);
				$this->engine_ver = $this->VerStr2Float($this->engine_ver_str);

				$array = array(// Specific browsers
					"ie" => array(
						array("Netscape\/([\d\.]+)", "Netscape", true, true), // Netscape via MSIE engine
						),

					"opera" => array(
						array("Opera Mini\/([\d\.]+)", "Opera Mini", true, true), // Opera mini
						),
				);

				$flag = false;

				if (isset($array[$this->type]))
				{
					foreach ($array[$this->type] as $key => $elem)
					{
						if (preg_match("/{$elem[0]}/i", $this->ua_string))
						{
							$this->name = $elem[1];
							$this->ver_str = preg_replace("/" . CF::Seek4NonEmptyStr($elem[2], ".*{$elem[0]}.*") . "/i", CF::Seek4NonEmptyStr($elem[3], "\\1"), $this->ua_string);
							$this->ver = $this->VerStr2Float($this->ver_str);

							$flag = true;

							break;
						}
					}
				}

				if (!$flag)
				{
					$this->ver_str = $this->engine_ver_str;
					$this->ver = $this->engine_ver;

					$array = array(
						"ie" => "Microsoft Internet Explorer",
						"mozilla_compatible" => "Unknown (Mozilla compatible)",
						"old_netscape" => "Netscape",
						"elinks" => "ELinks",
						);

					$this->name = isset($array[$this->type]) ? $array[$this->type] : ucfirst($this->type);
				}
			}

			else
			{
				$this->name = "Unknown";
			}
		}

		$array = array(
			"Windows NT 5\.1" => array("win", "Windows XP"),
			"WinNT 5\.1" => array("win", "Windows XP"),
			"Windows NT 5\.0" => array("win", "Windows 2000"),
			"WinNT 5\.0" => array("win", "Windows 2000"),
			"Windows NT" => array("win", "Windows NT"),
			"WinNT" => array("win", "Windows NT"),
			"Windows 98" => array("win", "Windows 95"),
			"Windows 95" => array("win", "Windows 95"),
			"Windows 3\.1" => array("win", "Windows 3.1"),
			"Windows" => array("win", "Windows"),
			"Linux" => array("linux", "Linux"),
			"SunOS" => array("sun", "SunOS"),
			"NetBSD" => array("bsd", "NetBSD"),
			"Mac" => array("mac", "Mac"),
			);

		$flag = false;

		foreach ($array as $key => $elem)
		{
			if (preg_match("/$key/i", $this->ua_string))
			{
				list($this->os_type, $this->os_name) = $elem;
				$flag = true;
				break;
			}
		}

		if (!$flag)
		{
			$this->os_type = false;
			$this->os_name = "Unknown";
		}

		$this->SetStylable();
	}



	function GeckoNameVerEval($name, $ver_str)
	{
		$this->name = $name;
		$this->ver_str = $ver_str;
		$this->ver = $this->VerStr2Float($ver_str);

		$specific_names = array(
			"firefox" => "Mozilla Firefox",
			"netscape6" => "Netscape",
			"navigator" => "Netscape",
			);

		$name2lower = strtolower($name);

		if (isset($specific_names[$name2lower]))
		{
			$this->name = $specific_names[$name2lower];
		}
	}



	function SafariVerEval($ver_str, $engine_ver_str)
	{
		$this->ver_str = $ver_str;
		$this->engine_ver_str = $this->VerStr2Float($engine_ver_str);
	}



	function VerStr2Float($ver_str, $is_gecko_engine_ver_str = false)
	{
		$parts = explode(".", $ver_str);
		$cnt = count($parts);

		if ($is_gecko_engine_ver_str && in_array($cnt, array(3, 4)) && ($parts[0] == 1) && in_array($parts[1], array(7, 8)))
		{
			return intval($parts[0]) + intval($parts[1])/10 + intval($parts[2])/1000 + (isset($parts[3]) ? intval($parts[3])/100000 : 0);
		}

		elseif ($is_gecko_engine_ver_str && (($cnt < 2) || ($cnt > 4)))
		{
			return false;
		}

		elseif ($cnt <= 2)
		{
			return floatval($ver_str);
		}

		$output = 0;

		foreach ($parts as $key => $val)
		{
			$output += intval($val) / (pow(10, $key + strlen($val) - 1));
		}

		return $output;
	}



	function FullName($add_engine_info = false, $add_os_info = false)
	{
		return "$this->name" . ((!$this->IsUnknown() && !$this->IsMozillaCompatible()) ? (" " . $this->ver_str . ($add_engine_info ? " (engine version: $this->engine_ver_str)" : "")) : "") . ($add_os_info ? ", OS: $this->os_name" : "");
	}



	function VerFits($min_ver = false, $max_ver = false, $ver = false)
	{
		return ($min_ver ? $this->engine_ver >= $min_ver : true) && ($max_ver ? $this->engine_ver < $max_ver : true);
	}



	function IsGecko($min_engine_ver = false, $max_engine_ver = false)
	{
		return ($this->type == "gecko") && $this->VerFits($min_engine_ver, $max_engine_ver, $this->engine_ver);
	}



	function IsOpera($min_ver = false, $max_ver = false)
	{
		return $this->type == "opera" && $this->VerFits($min_ver, $max_ver, $this->ver);
	}



	function IsIE($min_ver = false, $max_ver = false)
	{
		return $this->type == "ie" && $this->VerFits($min_ver, $max_ver, $this->ver);
	}



	function IsAmaya($min_ver = false, $max_ver = false)
	{
		return $this->type == "amaya" && $this->VerFits($min_ver, $max_ver, $this->ver);
	}


	function IsKHTML($min_engine_ver = false, $max_engine_ver = false)
	{
		return $this->type == "khtml" && $this->VerFits($min_engine_ver, $max_engine_ver, $this->engine_ver);
	}



	function IsSafari($min_ver = false, $max_ver = false)
	{
		return $this->name == "Safari" && $this->VerFits($min_ver, $max_ver, $this->ver);
	}



	function IsKonqueror($min_ver = false, $max_ver = false)
	{
		return $this->name == "Konqueror" && $this->VerFits($min_ver, $max_ver, $this->ver);
	}



	function IsMozillaCompatible($min_ver = false, $max_ver = false)
	{
		return $this->type == "mozilla_compatible" && $this->VerFits($min_ver, $max_ver, $this->ver);
	}



	function IsOldNetscape($min_ver = false, $max_ver = false)
	{
		return $this->type == "old_netscape" && $this->VerFits($min_ver, $max_ver, $this->ver);
	}



	function IsUnknown()
	{
		return $this->type == "unknown";
	}



// вспомогательные функции

	function IsOldGecko()
	{
		return $this->IsGecko(false, 1);
	}



	function IsNewGecko()
	{
		return $this->IsGecko(1.8);
	}



	function IsOldIE()
	{
		return $this->IsIE(false, 5);
	}



	function IsNewIE()
	{
		return $this->IsIE(6);
	}



	function IsOldOpera()
	{
		return $this->IsOpera(false, 7);
	}



	function IsNewOpera()
	{
		return $this->IsOpera(9);
	}



	function IsGood()
	{
		return $this->IsNewGecko() || $this->IsNewOpera();
	}



	function SetStylable($array = NULL, $full_replace = false)
	{
		if (!is_array($this->stylable))
		{
			$this->stylable = array();
		}

		if (!is_array($array))
		{
			$array = array(
				"ie" => 6,
				"gecko" => 1,
				"opera" => 7,
				"mozilla_compatible" => 4,
				"safari" => false,
				"konqueror" => false,
				"amaya" => false
				);
		}

		$this->stylable = $full_replace ? $array : array_merge($this->stylable, $array);

		foreach ($this->stylable as $key => $val)
		{
			if (is_null($val))
			{
				unset($this->stylable[$key]);
			}
		}
	}



	function IsStylable()
	{
		return isset($this->stylable[$this->type]) && $this->VerFits($this->stylable[$this->type]);
	}
}

?>