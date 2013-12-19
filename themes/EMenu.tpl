<?php
// version: 3.2
// date: 2012-05-12

//$MODULE_OUTPUT = $MODULE_DATA["output"];

	if(!function_exists("OutPutGroups")) {
		function OutPutGroups($array) {
		//die(print_r($array[3])); ?>
			<ul>
<?php		foreach($array as $id => $item) {
				if ($array[$id]["manage_groups"]) { ?>
				<li>
					<?=$item["descr"]?> <a href="edit_menugroup/<?=$id?>/"><img src="/themes/images/edit.png" alt="Редактировать группу меню" /></a><br />
					[<a href="add/<?=$id?>/0/" title="Создать пункт группы &laquo;<?=$item["descr"]?>&raquo;">создать пункт</a>]
				</li>
<?php				if(!empty($item["subitems"])) {
						OutPutItems($item["subitems"]);
					}
				}
			} ?>
			</ul>
<?php	}
	}

	if(!function_exists("OutPutItems")) {
		function OutPutItems($array) {
			$displayed = 0; ?>
			<ul>
<?php		foreach($array as $id => $item) { ?>
				<li>
<?php			if (!$uri = $item["direct_link"]) {
					$uri = $item["uri"];
				}
				if($item["is_active"]) { ?>
					<a href="<?=$uri?>" title="<?=$item["descr"]?>"><?=$item["title"]?></a>
<?php			} else {
					if($item["manage_items"]) { ?>
					<a href="<?=$uri?>" title="<?=$item["descr"]?>" style="color: grey;"><?=$item["title"]?></a>
<?php				}
				}
				if($item["manage_items"]) {
					if (count($array) > 1) { ?>
					&nbsp;[&nbsp;
<?php				}
					if ($displayed) { ?>
					<a href="posup/<?=$id?>/" title="Переместить на одну позицию вверх">&uarr;</a>&nbsp;
<?php				}
					if ($displayed < count($array) - 1) { ?>
					<a href="posdown/<?=$id?>/" title="Переместить на одну позицию вниз">&darr;</a>&nbsp;
<?php				}
					$displayed++;
					if (count($array) > 1) { ?>
					]
<?php				} ?>
					<br />
					<small>
<?php				if($item["is_active"]) { ?>
						[<a href="hide/<?=$id?>/" title="Не показывать данный пункт посетителям">скрыть</a>]&nbsp;
<?php				} else { ?>
						[<a href="show/<?=$id?>/" title="Показать данный пункт посетителям">показать</a>]
<?php				} ?>
						[<a href="add/<?=$item["group_id"]?>/<?=$id?>/" title="Создать подпункт для пункта &laquo;<?=$item["title"]?>&raquo;">создать подпункт</a>]&nbsp;
						[<a href="edit/<?=$id?>/" title="Редактировать свойства пункта &laquo;<?=$item["title"]?>&raquo;">редактировать</a>]&nbsp;
<?php				if($id) { ?>
						[<a href="delete/<?=$id?>/" title="Удалить" onClick="return del(<?=$id?>);">удалить</a>]
<?php				} ?>
					</small>
<?php			} ?>
					<div class="item_add" id="add<?=$id?>"></div>
				</li>
<?php			if(!empty($item["subitems"])) {
					OutPutItems($item["subitems"]);
				}
			} ?>
			</ul>
<?php	}
	}
	
	if(!function_exists("OutPutFoldersOptions"))
	{
/*		function OutPutFoldersOptions($array, $selected_id = NULL)
		{
			foreach($array as $id => $item)
			{
				$sel_attr = "";
				if ($id == $selected_id) { $sel_attr = " selected"; }
				echo "<option value=\"".$id."\"".$sel_attr.">".$item["descr"]."</option>";
				if(!empty($item["subitems"]))
					OutPutFoldersOptions($item["subitems"], $selected_id);
			}
		}*/
		
		function OutPutFoldersOptions($array, $selected_id  = NULL, $depth = 0)
		{
			//die(print_r($array));
			$displayed = 0;
			$level = "";
			for ($i = $depth; $i > 0; $i--)
				$level .= '-';
			
			foreach($array as $id => $item)
			{		
				if ($id == $selected_id)
					$selected = " selected";
				else
					$selected = "";
			
				if($item["is_active"])
					echo "<option value=\"".$id."\"".$selected.">".$level." ".$item["title"]."</option>";
				else
					echo "<option value=\"".$id."\"".$selected.">".$level." ".$item["title"]." (не активен)</option>";
				
			if(!empty($item["subitems"]))
				OutPutFoldersOptions($item["subitems"], $selected_id, $depth+1);
			}
		}
	}

	switch ($MODULE_OUTPUT["mode"]) {
		case "manage": {
			if(!empty($MODULE_OUTPUT["groups"])) {				
				echo "<h2>Группы меню</h2>";
				OutPutGroups($MODULE_OUTPUT["groups"]);
				if($MODULE_OUTPUT["manage_groups"])  //добавление группы меню - на будущее // теперь - настоящее
				{        
					echo "<i>[<a style=\"cursor: pointer;\" href=\"add_group\" onClick=\"return add(0)\">добавить меню</a>]</i>\n";
					echo "<div class=\"group_add\" id=\"add0\"></div>\n";	
				};
			}
			
			if (isset($MODULE_OUTPUT["group_add"]) && $MODULE_OUTPUT["group_add"]) {
				echo "<h2>Добавление группы меню</h2><br />";
				echo "<form action=\"".$EE["unqueried_uri"]."\" method=\"post\" enctype=\"multipart/form-data\">
				<strong>Название:</strong><br /><input name=\"descr\" type=\"text\"><br /><br />
				<input type=\"Submit\" value=\"Добавить группу\">
                </form>";
			}
			
			if(!empty($MODULE_OUTPUT["folders"]) && !isset($MODULE_OUTPUT["menu_item"])) {
				echo "<script>jQuery(document).ready(function($) {ManageMenu(".$MODULE_OUTPUT['module_id'].");});</script>";
				echo "<h2>Добавление пункта меню</h2><br />";
				
				echo "<form action=\"".$EE["unqueried_uri"]."\" method=\"post\" enctype=\"multipart/form-data\">
				<input name=\"pid\" type=\"hidden\" value=\"".$MODULE_OUTPUT["pid"]."\">
				<input name=\"group_id\" type=\"hidden\" value=\"".$MODULE_OUTPUT["group_id"]."\">
                <strong>Соответствующая страница:</strong><br />
				<select name=\"folder_id\" size=\"1\" class=\"menu_list\" />";
				
				
				OutPutFoldersOptions($MODULE_OUTPUT["folders"]);
				
				echo "</select>
				<input type=\"text\" size=\"10\" title=\"Быстрый поиск\" placeholder=\"Введите часть названия\" class=\"menu_search\" style=\"display: none; width: 265px; \" />
				<a href=\"javascript: void(0)\" onclick=\"jQuery(this).parent().find('.menu_search').show(); jQuery(this).parent().find('.menu_list').hide(); jQuery(this).hide(); \" class=\"menu_list\" title=\"Поиск по фрагменгту названия\">Искать</a> 
				<br /><br />
				<div id=\"linkshow\"><a style=\"cursor: pointer\" onclick=\"document.getElementById('addfields').style.display='block'; document.getElementById('linkhide').style.display='block'; document.getElementById('linkshow').style.display='none';\">Показать дополнительные поля</a></div>
				<div id=\"linkhide\" style=\"display: none;\"><a style=\"cursor: pointer\" onclick=\"document.getElementById('addfields').style.display='none'; document.getElementById('linkshow').style.display='block'; document.getElementById('linkhide').style.display='none';\">Скрыть дополнительные поля</a></div>
				<br />
				<fieldset id=\"addfields\" style=\"display:none; width: 500px;\">
				<legend>Необязательно для заполнения</legend>
				<strong>Название:</strong><br /><input name=\"title\" type=\"text\"><br />
                <strong>Описание (всплывающая подсказка):</strong><br /><input name=\"descr\" type=\"text\"><br />
				<strong>Прямая ссылка:</strong><br /><input name=\"direct_link\" type=\"text\"><br />
				<strong>Иконка:</strong><br /><input name=\"attach\" type=\"file\"><br />
				</fieldset>
				<input type=\"Submit\" value=\"Добавить пункт\">
                </form>";				
			}
			
			if (isset($MODULE_OUTPUT["menu_item"])) { ?>
			<h2>Редактирование пункта меню</h2>
			<form action="<?=$EE["unqueried_uri"]?>" method="post" enctype="multipart/form-data">
				<p>
					<strong>Соответствующая страница:</strong><br />
					<select name="<?=$NODE_ID?>[save][folder_id]" size=1>
<?php 			OutPutFoldersOptions($MODULE_OUTPUT["folders"], $MODULE_OUTPUT["menu_item"]["folder_id"]); ?>
					</select>
				</p>
				<div id="linkshow"><a style="cursor: pointer" onclick="document.getElementById('addfields').style.display='block'; document.getElementById('linkhide').style.display='block'; document.getElementById('linkshow').style.display='none';">Показать дополнительные поля</a></div>
				<div id="linkhide" style="display: none;"><a style="cursor: pointer" onclick="document.getElementById('addfields').style.display='none'; document.getElementById('linkshow').style.display='block'; document.getElementById('linkhide').style.display='none';">Скрыть дополнительные поля</a></div>
				<br />
				<fieldset id="addfields" style="display:none; width: 500px;">
					<legend>Необязательно для заполнения</legend>
					<p>
						Название:<br />
						<input name="<?=$NODE_ID?>[save][title]" type="text" value="<?=$MODULE_OUTPUT["menu_item"]["title"]?>">
					</p>
					<p>
						Описание (всплывающая подсказка):<br />
						<input name="<?=$NODE_ID?>[save][descr]" type="text" value="<?=$MODULE_OUTPUT["menu_item"]["descr"]?>">
					</p>
					<p>
						Прямая ссылка:<br />
						<input name="<?=$NODE_ID?>[save][direct_link]" type="text" value="<?=$MODULE_OUTPUT["menu_item"]["direct_link"]?>">
					</p>
					<p>
						<strong>Иконка (оставьте это поле пустым, если не хотите менять иконку):</strong><br /><input name="attach" type="file"><br />
						<input type="checkbox" name="<?=$NODE_ID?>[save][icon_delete]"> Удалить иконку<br />
					</p>
				</fieldset>
				<p>
					<input type="submit" value="Сохранить">
				</p>
			</form>
<?php 		}
		}
		break;
		
		case "edit": { ?>
			<h2>Редактирование пункта меню</h2>
			<form action="<?=$EE["unqueried_uri"]?>" method="post" enctype=\"multipart/form-data\">
				<p>
					<strong>Соответствующая страница:</strong><br />
					<select name='<?=$NODE_ID?>[save][folder_id]' size=1>
<?php 		OutPutFoldersOptions($MODULE_OUTPUT["folders"], $MODULE_OUTPUT["menu_item"]["folder_id"]); ?>
					</select>
				</p>
				<fieldset name="Дополнительно">
					<p>
						Название:<br />
						<input name="<?=$NODE_ID?>[save][title]" type="text" value="<?=$MODULE_OUTPUT["menu_item"]["title"]?>">
					</p>
					<p>
						Описание (всплывающая подсказка):<br />
						<input name="<?=$NODE_ID?>[save][descr]" type="text" value="<?=$MODULE_OUTPUT["menu_item"]["descr"]?>">
					</p>
					<p>
						Прямая ссылка:<br />
						<input name="<?=$NODE_ID?>[save][direct_link]" type="text" value="<?=$MODULE_OUTPUT["menu_item"]["direct_link"]?>">
					</p>
				</fieldset>
				<p>
					<input type="submit" value="Сохранить">
				</p>
			</form>
<?php	}
		break;
        
		case "edit_menugroup": { ?>
			<h2>Редактирование группы меню</h2>
			<form action="<?=$EE["unqueried_uri"]?>" method="post">
				<p>
					Название:<br />
					<input name="<?=$NODE_ID?>[save][descr]" type="text" class="input_long" value="<?=$MODULE_OUTPUT["menu_group"]["descr"]?>" />
				</p>
				<p>
					<input type="submit" value="Сохранить" />
				</p>
			</form>
<?php	}
		break;
        
        case "submenu": {
			if(is_array($MODULE_OUTPUT["items"]) && count($MODULE_OUTPUT["items"])) { ?>			
<?php       	if (!function_exists('print_array')) {
					function print_array($ar) {
					    static $count; 
					    $count = (isset($count)) ? ++$count : 0;
					    if (!is_array($ar)) {
					        return; 
					    }
						$class = '';
						if(!$count) {
							$class = ' class="main"';
						} ?>
							<ul<?=$class?>>
<?php       		    foreach($ar as $data) { ?>
								<li>
									<a href="<?=$data["uri"]?>"<?=($data["uri_is_current"] ? " class='current'" : "" )?> title="<?=$data["descr"]?>">
										<span><?=$data["title"]?></span><em>&nbsp;</em>
									</a>
<?php						if(isset($data["submenu"])) { 
								print_array($data["submenu"]);
							} ?>									
								</li>      
<?php           	    } ?>
							</ul>
<?php           		$count--;
					}
				}
				print_array($MODULE_OUTPUT["items"]); ?>			    
<?php       }
        }
		break;
        
        case "main_menu": {
			if(count($MODULE_OUTPUT["items"]) <= 2) { ?>
				<ul>
<?php		foreach ($MODULE_OUTPUT["items"][0]["subitems"] as $data) { /* Вывод пунктов типичного меню */ ?>
					<li>
						<a href="<?=$data["uri"]?>" <?=($data["uri_is_current"] ? "class=\"active\"" : "")?> title="<?=$data["descr"]?>">
<?php			if(!empty($data["options"]["icon"])) { /* Иконка */ ?>
							<img src="/files/icons/<?=$data["options"]["icon"]?>" alt="" /> 
<?php			} ?>
							<?=$data["title"]?>
						<span></span><span></span>
						</a>
					</li>
<?php		} ?>
				</ul>
<?php		} else {
				$i = 1; ?>
				<ul id="main_submenu">
<?php			foreach ($MODULE_OUTPUT["items"] as $data) { /* Вывод пунктов типичного меню */ ?>
					<li>
						<a href="<?=$data["uri"]?>" <?=($data["uri_is_current"] ? "class='current'" : "")?> title="<?=htmlspecialchars($data["descr"])?>"><?=$data["title"]?><span><span></span></span></a>
<?php				if ($data["subitems"]) { ?>
							<em>&nbsp;</em>
<?php				} elseif ($i < count($MODULE_OUTPUT["items"])) { ?>
							<i>&nbsp;</i>
<?php				} 
					if ($data["subitems"]) { ?>
						<div class="sum_menu">
							<ul>
<?php					foreach ($data["subitems"] as $data2) { ?>
								<li>
									<a href="<?=$data2["uri"]?>" <?=($data2["uri_is_current"] ? "class='current'" : "")?> title="<?=$data2["descr"]?>">
										<?=$data2["title"]?>
									</a>
								</li>
<?php					} ?>
							</ul>
							<div class="clear">&nbsp;</div>
						</div>
<?php				} ?>
					</li>
<?php				$i++;
				} ?>
				</ul>				
<?php		}
		}
		break;
		
		case 'img_menu': {
			if(isset($MODULE_OUTPUT["items"])) {
				$module_id = $MODULE_OUTPUT['module_id'];
				$ajax_upload = $MODULE_OUTPUT['ajax_upload'];
				$group_id = $MODULE_OUTPUT['group_id'];
				$pid = $MODULE_OUTPUT['pid'];
				$count = $MODULE_OUTPUT['count']; ?>
			<script>jQuery(document).ready(function($) {MenuImage(<?=$module_id?>,<?=$ajax_upload?>,<?=$group_id?>,<?=$pid?>,<?=$count?>);});</script>
			<div class="img_menu">
<?php			if(count($MODULE_OUTPUT["items"]) > 1) { ?>
				<a href="javascript:void(0)" class="prev"><span><span><span></span></span></span></a>
				<a href="javascript:void(0)" class="next"><span><span><span></span></span></span></a>
<?php			} ?>
				<ul>
<?php			$first = true;
				foreach($MODULE_OUTPUT["items"] as $data) {
					if(!empty($data["options"]["icon"])) { ?>
					<li<?=($first ? " class='current'" : "")?>>
						<div class="img_block"><img src="/images/icons/<?=$data["options"]["icon"]?>" alt="" /></div>
						<div class="title_block">
							<div class="title_block_bg"></div>
							<div class="title_cont">
								<div class="title"><?=$data["title"];?></div>
								<div class="title_link"><a href="<?=$data["uri"]?>" title="Перейти на страницу &laquo;<?=$data["title"];?>&raquo;" onclick="return !window.open(this.href);"><?=(isset($data["descr"]) ? $data["descr"] : $data["title"])?></a></div>
							</div>
						</div>
					</li>
<?php					$first = false;
					}
				} ?>
				</ul>
			</div>
<?php		}
		}
		break;
	
		case 'ajax_get_menu_item': {
			if(isset($MODULE_OUTPUT["items"])) {
				header("Content-type: text/html; charset=windows-1251");
				foreach($MODULE_OUTPUT["items"] as $data) {
					if(!empty($data["options"]["icon"])) { ?>
					<li>
						<div class="img_block"><img src="/images/icons/<?=$data["options"]["icon"]?>" alt="" /></div>
						<div class="title_block">
							<div class="title_block_bg"></div>
							<div class="title_cont">
								<div class="title"><?=$data["title"];?></div>
								<div class="title_link"><a href="<?=$data["uri"]?>" title="Перейти на страницу &laquo;<?=$data["title"];?>&raquo;" onclick="return !window.open(this.href);"><?=(isset($data["descr"]) ? $data["descr"] : $data["title"])?></a></div>
							</div>
						</div>
					</li>
<?php				}
				}
			}
		}
		break;
		
		
		case 'nripk_menu': {
			foreach ($MODULE_OUTPUT["items"] as $ind=>$data) { ?>
				<div class="art-block">
			    	<div class="art-block-body">
						<div class="art-blockheader">
			    			<div class="l"></div>
			    			<div class="r"></div>
			     			<div class="t"><?=$data["title"];?></div>
						</div>
						<div class="art-blockcontent">
						    <div class="art-blockcontent-tl"></div>
						    <div class="art-blockcontent-tr"></div>
						    <div class="art-blockcontent-bl"></div>
						    <div class="art-blockcontent-br"></div>
						    <div class="art-blockcontent-tc"></div>
						    <div class="art-blockcontent-bc"></div>
						    <div class="art-blockcontent-cl"></div>
						    <div class="art-blockcontent-cr"></div>
						    <div class="art-blockcontent-cc"></div>
						    <div class="art-blockcontent-body">
								<ul class="menu">
<?php 			if (isset($data['subitems']) && !empty($data['subitems'])) { 
					foreach ($data["subitems"] as $data2) { ?>
									<li<?=($data2["uri_is_current"] ? " class='current'" : "")?>>
										<a href="<?=$data2["uri"]?>" title="<?=$data2["descr"]?>"><?=$data2["title"]?></a>
<?php								if ($data2["subitems"]) { ?>
										<ul>
<?php									foreach ($data2["subitems"] as $data3) { ?>
											<li<?=($data3["uri_is_current"] ? " class='current'" : "")?>>
												<a href="<?=$data3["uri"]?>" title="<?=$data3["descr"]?>">
													<?=$data3["title"]?>
												</a>
											</li>
<?php									} ?>
										</ul>
<?php								} ?>
									</li>
<?php				}
 				}  ?>
								</ul>
								<div class="cleared"></div>
							 </div>
						</div>
						<div class="cleared"></div>
					</div>
				</div>
<?php 		}
		}
		break;
		
  
		default: { ?>
			<ul>
<?php		foreach ($MODULE_OUTPUT["items"] as $data) { /* Вывод пунктов типичного меню */ ?>
				<li<?=($data["uri_is_current"] ? " class='current'" : "")?>>
					<a <?=($data["uri"] ? 'href="'.$data["uri"].'"' : 'style="text-decoration:none"')?> title="<?=$data["descr"]?>">
<?php			if(!empty($data["options"]["icon"])) { ?>
						<img src="/files/icons/<?=$data["options"]["icon"]?>" alt="" />
<?php			} ?>
						<span><span><?=$data["title"];?></span></span>
					</a>
<?php			if ($data["subitems"]) { ?>
					<ul>
<?php				foreach ($data["subitems"] as $data2) { ?>
						<li<?=($data2["uri_is_current"] ? " class='current'" : "")?>>
							<a href="<?=$data2["uri"]?>" title="<?=$data2["descr"]?>"><?=$data2["title"]?></a>
<?php					if ($data2["subitems"]) { ?>
							<ul>
<?php						foreach ($data2["subitems"] as $data3) { ?>
								<li<?=($data3["uri_is_current"] ? " class='current'" : "")?>>
									<a href="<?=$data3["uri"]?>" title="<?=$data3["descr"]?>">
										<?=$data3["title"]?>
									</a>
								</li>
<?php						} ?>
							</ul>
<?php					} ?>
						</li>
<?php				} ?>
					</ul>
<?php			} ?>
				</li>
<?php		} ?>
			</ul>
<?php		break;
		}
	}
	
?>