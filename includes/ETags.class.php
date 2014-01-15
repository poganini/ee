<?php

class ETags
// version: 1.0 betta pribetta 1
// date: 2008-12-01
{
	var $output;
	var $module_id;
	var $node_id;
	var $module_uri;
	var $db_prefix;
	var $mode;
	
	// В params передаётся режим работы tags, tag или cloud
	// В global_params префикс таблиц БД 
	
	function ETags($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = NULL)
	{
		global $DB, $Engine;
		$this->module_id = $module_id;
		$this->module_uri = $module_uri;
		$this->node_id = $node_id;
		$parts = explode(";", $params);
        $this->output["mode"] = $this->mode = $parts[0];
        $this->output = array();
		
		switch($this->mode)
		{               
		    case "tags":
                $this->output["mode"] = $this->mode;
                $this->Tags($parts[1],$parts[2]);
                break;
            case "tag":
				$this->output["mode"] = $this->mode;
				$this->ShowPosts($this->module_uri);
                $this->Cloud();
				break;
			case "cloud":
				$this->output["mode"] = $this->mode;
				$this->Cloud();
				break;
			default:
				die("ETags module error #1100 at node $this->node_id: unknown mode &mdash; $this->mode.");
				break;			
		}		
	}
    
    
    
    function Tags($mode, $num)//$mode - модуль, по которому выбираются теги, $num - число элементов, которые ищутся по тегам
    {
        global $DB;
        $this->output["tags"]["firms"]=array();
        if(isset($_SESSION["tags"]))
        {
            $tags = $_SESSION["tags"];
            foreach ($tags as $tag)
            {
                $tag = trim($tag);
                $DB->SetTable("tags_tags");
                $DB->AddField("id");
                $DB->AddCondFS("tag", "=", $tag);
                $res = $DB->Select(1);
                if($row = $DB->FetchObject($res))
                {
                    $DB->SetTable("tags_posts");
                    $DB->AddCondFS("tag_id", "=", $row->id);
                    $DB->AddCondFS("module_id", "=", $mode);
                    $res1 = $DB->Select();
                    while($row1 = $DB->FetchObject($res1))
                    {
                        $DB->SetTable("firms_items");
                        $DB->AddCondFS("id", "=", $row1->entry_id);
                        $res2 = $DB->Select(1);
                        if($row3 = $DB->FetchObject($res2))
                        {
                            $this->output["tags"]["firms"][$row3->id]=$row3;   
                        }   
                    }                                
                }

            }
            unset($_SESSION['tags']);
            session_unregister('tags');
        }
    }
	
	function ShowPosts($tag)
	{
		global $DB;
		$this->output["tag"] = $this->utf8_to_win1251(urldecode($tag));
		$res = $DB->Exec('SELECT *
			FROM tags_tags t, tags_posts p
			WHERE t.id = p.tag_id
			AND t.tag = "'.$this->output["tag"].'"
			GROUP BY p.entry_id DESC');
		$this->output["posts"] = array();
		while ($row = $DB->FetchObject($res))
		{
			$this->output["posts"][$row->id] = array(
			"link_suffix" => $row->link_suffix,
			"cache_title" => $row->cache_title,
			"cache_short" => $row->cache_short
			);
		}		
	}
	
	function Cloud()
	{
		global $DB;
		$this->output["tags"] = array();
		$res = $DB->Exec("SELECT tags_tags.tag, COUNT(tags_posts.tag_id) AS posts_count
				FROM tags_posts LEFT JOIN tags_tags ON tags_posts.tag_id=tags_tags.id
				GROUP BY tags_tags.id");
		if($row = $DB->FetchObject($res))
		{
			$this->output["tags"][$row->tag] = $row->posts_count;
			$max = $min = $row->posts_count;
		}
		while($row = $DB->FetchObject($res))
		{
			$this->output["tags"][$row->tag] = $row->posts_count;
			if($row->posts_count > $max)
				$max = $row->posts_count;
			if($row->posts_count < $min)
				$min = $row->posts_count;
		}
		$this->output["max"] = $max;
		$this->output["min"] = $min;		
	}	

	function utf8_to_win1251($str)
	{
		$chars = array(
		# upper case letters
		'208144' => chr(192), '208145' => chr(193), '208146' => chr(194),
		'208147' => chr(195), '208148' => chr(196), '208149' => chr(197),
		'208129' => chr(168), '208150' => chr(198), '208151' => chr(199),
		'208152' => chr(200), '208153' => chr(201), '208154' => chr(202),
		'208155' => chr(203), '208156' => chr(204), '208157' => chr(205),
		'208158' => chr(206), '208159' => chr(207), '208160' => chr(208),
		'208161' => chr(209), '208162' => chr(210), '208163' => chr(211),
		'208164' => chr(212), '208165' => chr(213), '208166' => chr(214),
		'208167' => chr(215), '208168' => chr(216), '208169' => chr(217),
		'208170' => chr(218), '208171' => chr(219), '208172' => chr(220),
		'208173' => chr(221), '208174' => chr(222), '208175' => chr(223),
		# lower case letters
		'208176' => chr(224), '208177' => chr(225), '208178' => chr(226),
		'208179' => chr(227), '208180' => chr(228), '208181' => chr(229),
		'209145' => chr(184), '208182' => chr(230), '208183' => chr(231),
		'208184' => chr(232), '208185' => chr(233), '208186' => chr(234),
		'208187' => chr(235), '208188' => chr(236), '208189' => chr(237),
		'208190' => chr(238), '208191' => chr(239), '209128' => chr(240),
		'209129' => chr(241), '209130' => chr(242), '209131' => chr(243),  
		'209132' => chr(244), '209133' => chr(245), '209134' => chr(246),
		'209135' => chr(247), '209136' => chr(248), '209137' => chr(249),
		'209138' => chr(250), '209139' => chr(251), '209140' => chr(252),
		'209141' => chr(253), '209142' => chr(254), '209143' => chr(255)
		);
	  $len = strlen($str);
	  $temp = '';

	  for($i=0;$i<$len;$i++) {
	    $chcode = ord($str[$i]);
	    while($i<$len-1 && $chcode!=208 && $chcode!=209) { # skip not utf8 chars
	      $temp.=$str[$i];
	      $chcode = ord($str[++$i]);
	    }
	    if($i<$len-1) {
	      $key = (string) $chcode.ord($str[++$i]);
	      if(isset($chars[$key])) { # if after 208 or 209 correct char (exist as key in $chars)
		$temp.= $chars[$key];
	      } else $temp.=$str[$i];
	    } else $temp.=$str[$i];
	  }
	  return($temp);
	}
	
	function Output()
	{
		return $this->output;
	}
}
?>