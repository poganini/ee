<?php

$multiple_steps = false;


if (count($EE["footsteps"]) >= 1)
{
		$array = array();
		$array[] = array(
			"uri" => HTTP_ROOT,
			"title" => "Главная"/*SITE_SHORT_NAME*/,
			"descr" => "На главную страницу сайта",
			"is_folder" => true,
			"is_hidden" => false,
			"module_id" => 0,
			);
		$array = array_merge($array, $EE["footsteps"]);

	$num = count($array);
	$i = 0;
	$array2 = array();

	foreach ($array as $data)
	{
		if (!$data["is_hidden"])
		{
			$flag = (++$i == $num);
			$flag2 =  CF::URIis($data["uri"]);
			$elem = "";
//			$elem = "<span>";
//			$elem = $flag ? "</span>" : "";
			$elem .= $flag2 ? "<strong>" : "<a href=\"{$data["uri"]}\" title=\"{$data["descr"]}\">";
			$elem .= $data["title"];
			$elem .= $flag2 ? "</strong>" : "</a>";
//			$elem .= $flag ? "" : "";
			$elem .= $flag ? "" : " &nbsp;/&nbsp; ";
//			$elem .= "</span>";
			$elem .= $flag ? "" : " ";
			$array2[] = $elem;
		}

		else
		{
			break;
		}
	}


	if (count($array2) > 1)
	{
		$multiple_steps = true;
	}
}


if ($multiple_steps)
{
	echo "<div id=\"footsteps\">" . implode("", $array2) . "</div>";
}

?>