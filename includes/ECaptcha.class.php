<?php

class ECaptcha
// version: 2.0 alpha 2
// date: 2008-05-16
{
	var $output;

    // В $params передаётся через точку с запятой: число символов от 4 до 32, второй параметр использовать только цифры
	function ECaptcha($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = NULL)
	{
		$parts = explode(";", $params);
		$length = intval($parts[0]);
		$numbers_only = isset($parts[1]) ? (bool)$parts[1] : false;

		if ($length < 5)
		{
			$length = 5;
		}
		
		elseif ($length > 32)
		{
			$length = 32;
		}


		$this->output["length"] = $length;


		if ($numbers_only)
		{
			$this->output["code"] = "";

			for ($i = 0; $i < $length; $i++)
			{
				$this->output["code"] .= rand(0, 9);
			}

			$_SESSION["code"] = $this->output["code"];
		}

		else
		{
			$this->output["code"] = $_SESSION["code"] = substr(md5(uniqid($_SERVER["SERVER_NAME"])), 0, $length);
		}
	}


	function Output()
	{
		return $this->output;
	}
}

?>