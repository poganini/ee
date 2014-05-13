<?php
// version: 3.11
// date: 2014-05-08
global $Engine, $Auth, $EE;

//$MODULE_OUTPUT = $MODULE_DATA["output"];
//if(isset($MODULE_OUTPUT["message"]))
//    echo "<p>".$MODULE_OUTPUT["message"]."</p>";
			//echo "<pre>";
			//print_r($MODULE_OUTPUT);
			//echo "</pre>";
	
	if (isset($MODULE_OUTPUT["deleted"]))
		echo "<p>Удалено разделов: ".$MODULE_OUTPUT["deleted"]."</p>";

	switch($MODULE_OUTPUT["mode"]) {
		case "ajax_pos_folder": {
			if(isset($MODULE_OUTPUT["ajax_pos_folder"])) {
				$item = $MODULE_OUTPUT["ajax_pos_folder"];
				header('Content-Type: text/xml,  charset=windows-1251');
				$dom = new DOMDocument();
				$sort_folder = $dom->createElement('sort_folder');
				$dom->appendChild($sort_folder);
				
				$from_elem = $dom->createElement('from_id');
				$from_id = $dom->createTextNode($item['from_id']);
				$from_elem->appendChild($from_id);
				
				$to_elem = $dom->createElement('to_id');
				$to_id = $dom->createTextNode($item['to_id']);
				$to_elem->appendChild($to_id);
				
				$root_elem = $dom->createElement('pid');
				$pid = $dom->createTextNode($item['pid']);
				$root_elem->appendChild($pid);
						
				$sort_folder->appendChild($from_elem);
				$sort_folder->appendChild($to_elem);
				$sort_folder->appendChild($root_elem);
					
				$xmlString = $dom->saveXML();	
				echo $xmlString;
			}
		}
		break;
	
		case "ajax_hide_folder": {
			if(isset($MODULE_OUTPUT["ajax_hide_folder"])) {
				header('Content-Type: text/xml,  charset=windows-1251');				
				$item = $MODULE_OUTPUT["ajax_hide_folder"];
				$fname = "";
				$descr = iconv('windows-1251','utf-8',$item["descr"]);
				$title = iconv('windows-1251','utf-8',$item["title"]);
				$uri = iconv('windows-1251','utf-8',$item["uri"]);
				if($item["is_active"]) { 
					$fname = "<a class='foldet_name' href='".$uri."' title='".$descr."'>".$title."</a>";          
				} else {
					if($item["manage_props"]) {
						$fname = "<a class='foldet_name' href='".$uri."' title='".$descr.iconv('windows-1251','utf-8','. Сейчас ссылка не работает, поскольку раздел скрыт!')."'>".$title."</a>";
					}
				}
				
				$dom = new DOMDocument();
				$hide_folder = $dom->createElement('hide_folder');
				
				$folder = $dom->createElement('folder');
				$folder_name = $dom->createTextNode($fname);
				$folder->appendChild($folder_name);
				
				$status = $dom->createElement('status');
				$is_active = $dom->createTextNode($item['is_active']);
				$status->appendChild($is_active);
						
				$hide_folder->appendChild($folder);
				$hide_folder->appendChild($status);
				$dom->appendChild($hide_folder);
					
				$xmlString = $dom->saveXML();
				echo $xmlString;
			}
		}
		break;
	
		case "ajax_add_folder":
		case "ajax_get_folder": { ;
			if(isset($MODULE_OUTPUT["ajax_add_folder"]) || isset($MODULE_OUTPUT["ajax_get_folder"])) {
				header("Content-type: text/html; charset=windows-1251");
				$array = array();
				if(isset($MODULE_OUTPUT["ajax_get_folder"])) {
					$array = $MODULE_OUTPUT["ajax_get_folder"];
					$displayed = 0;
				} elseif(isset($MODULE_OUTPUT["ajax_add_folder"])) {
					$array[$MODULE_OUTPUT["creat_folder_id"]] = $MODULE_OUTPUT["ajax_add_folder"][$MODULE_OUTPUT["creat_folder_id"]];
					//$item = $array[$MODULE_OUTPUT["creat_folder_id"]];
					//$id = $MODULE_OUTPUT["creat_folder_id"];
					$displayed = 1;//count($array) - 1;
				}				
				if($MODULE_OUTPUT["mode"] == 'ajax_get_folder' || ($MODULE_OUTPUT["mode"] == 'ajax_add_folder' && count($MODULE_OUTPUT["ajax_add_folder"]) == 1)) { ?>
		<ul style="display: none;">
<?php			}
				foreach($array as $id => $item) {
					if ($item["is_active"] || $item["manage_props"]) {
						$li_class = '';
						$li_class .= $item["is_active"] ? '' : 'inactive ';
						$li_class .= !empty($item["subitems"]) || $item["have_subfolder"] ? 'no_marker ' : '';
						$li_class .= count($array) > 1 ? '' : 'one_child ';
						$li_class .= $displayed ? '' : 'first_child ';
						$li_class .= $displayed < count($array) - 1 ? '' : 'last_child '; ?>
			<li id='folder_<?=$id?>'<?=(!empty($li_class) ? " class='".$li_class."'" : "")?>><span class="slide_botton inline-block" title='Свернуть'></span><div class="folder_title">				
<?php					if($item["is_active"]) { ?>
					<a class="foldet_name" href='<?=$item["uri"]?>' title='<?=$item["descr"]?>'><?=$item["title"]?></a>
<?php					} else {
							if($item["manage_props"]) { ?>
					<a class="foldet_name" href='<?=$item["uri"]?>' title='<?=$item["descr"]?>. Сейчас ссылка не работает, поскольку раздел скрыт!'><?=$item["title"]?></a>
<?php						}
						} ?>
					<small>
<?php					if($item["manage_props"]) {//Смена позиции
							$chain_array = $item["chain"];
							$chain = "";
						
							foreach ($chain_array as $key => $value) {
								if (!$key) $chain .= "?";
								$chain .= "pid".$key."=".$value;
								if ($key != count($chain_array)-1) $chain .= "&";
							} ?>
						<span class="pos_move">[
<?php						//if ($displayed) { ?>
							<a class="posup" href='posup/<?=$id?>/<?=$chain?>' title='Переместить на одну позицию вверх'><big>&uarr;</big></a>
<?php						//}
							//if ($displayed < count($array) - 1) { ?>
							<a class="posdown" href='posdown/<?=$id?>/<?=$chain?>' title='Переместить на одну позицию вниз'><big>&darr;</big></a>
<?php						//}
								$displayed++; ?>
						]</span><br />
<?php					}				
						if($item["manage_props"] && $item["manage_folder.delete"] && $id) {
							//if($item["is_active"]) { ?>
						<span  class="hide_folder">[<a href='hide/<?=$id?>/<?=$chain?>' title='Не показывать данный раздел посетителям'>скрыть</a>]</span>        
<?php						//} else { ?>
						<span  class="show_folder">[<a href='show/<?=$id?>/<?$chain?>' title='Показать данный раздел посетителям'>показать</a>]</span>
<?php						//}
						}   
						if($item["manage_subFolders"]  && $id) { ?>
						[<a title='Создать подраздел для раздела &laquo;<?=$item["title"]?>&raquo;' onClick='return add(<?=$id?>)'>создать подраздел</a>]&nbsp;
<?php					}
				//else 
				//	echo "[<span style=\"color: grey;\"\'>создать подраздел</span>]&nbsp;";
						if($item["manage_props"]) { ?>
						[<a href='edit/<?=$id?>/' title='Редактировать свойства раздела &laquo;<?=$item["title"]?>&raquo;'>редактировать</a>]&nbsp;
<?php					}				
						if($item["manage_folder.delete"] && $id) { ?>
						[<a href='delete/<?=$id?>/<?=$chain?>' title='Удалить' onClick='return del(<?=$id?>,<?=$item["pid"]?>)'>удалить</a>]
<?php					}
				//else
				//	echo "[<span style=\"color: grey;\">удалить</span>]"; ?>
					</small><em class='load'>&nbsp;</em>
				</div>
<?php					if($id) { ?>
				<div class='folder_add' id='add<?=$id?>'></div>
<?php					} ?>
			</li>
<?php				}
				}
				if($MODULE_OUTPUT["mode"] == 'ajax_get_folder' || ($MODULE_OUTPUT["mode"] == 'ajax_add_folder' && count($MODULE_OUTPUT["ajax_add_folder"]) == 1)) { ?>
		</ul>
<?php			}
			}
		}
		break;
	
		case "ajax_del_folder": {
			if(isset($MODULE_OUTPUT["message"]))
				echo "<p>".$MODULE_OUTPUT["message"]."</p>";
		}
		break;
	
		case "edit": { ?>
    <h2>Редактирование раздела</h2>
    <form action="<?=$EE["unqueried_uri"]?>" method="post" onsubmit="if((document.getElementById('uri_part').value.indexOf('/') + 1) || (document.getElementById('uri_part').value.indexOf('\\') + 1)) { alert('Слэш не должен присутствовать в адресе.'); return false; }">
		<p>
			Название:<br />
			<input name="<?=$NODE_ID?>[save][title]" type="text" class="input_long" value="<?=$MODULE_OUTPUT["folder"]["title"]?>" />
		</p>
		<p>
			Описание:<br />
			<input name="<?=$NODE_ID?>[save][descr]" type="text" class="input_long" value="<?=$MODULE_OUTPUT["folder"]["descr"]?>" />
		</p>
		<p>
			Часть адреса:<br />
			<input name="<?=$NODE_ID?>[save][uri_part]" type="text" id="uri_part" class="input_long" value="<?=$MODULE_OUTPUT["folder"]["uri_part"]?>" /> <a href="<?=$MODULE_OUTPUT["folder"]["uri"]?>" target="_blank">Перейти</a>
		</p>
		<p>
			Родительский раздел:<br />
			<select name="<?=$NODE_ID?>[save][pid]" size="1">
<?php 		OutPutFoldersToSelect($MODULE_OUTPUT["folders"], 0, $MODULE_OUTPUT["folder"]["pid"]); ?>
			</select>
		</p>
		<p>
			Наследовать блоки на дочерние разделы:<br />
<?php 		foreach ($MODULE_OUTPUT["nodegroups"] as $nodegroup) { ?>
			<input name="<?=$NODE_ID?>[save][nodegroups_inheritance][<?=$nodegroup["id"]?>]" type="checkbox"<?=($nodegroup["inheritance"] ? " checked='checked'" : "")?> /> <?=$nodegroup["comment"]?><br />
<?php 		} ?>
		</p>
		<p>
			Главный шаблон:<br />
			<input name="<?=$NODE_ID?>[save][main_template]" type="text" value="<?=$MODULE_OUTPUT["folder"]["main_template"]?>" />
		</p>
		<p>
			Описание для meta-тегов:<br />
			<input name="<?=$NODE_ID?>[save][meta_descr]" type="text" class="input_long" value="<?=$MODULE_OUTPUT["folder"]["meta_descr"]?>" />
		</p>
		<p>
			Ключевые слова для meta-тегов:<br />
			<input name="<?=$NODE_ID?>[save][keywords]" type="text" class="input_long" value="<?=$MODULE_OUTPUT["folder"]["keywords"]?>" />
		</p>
		<p>
			<input type="submit" value="Сохранить" />
		</p>
    </form>
    <hr />
<?php 		if ($MODULE_OUTPUT["nodes_allowed"]) { ?>
    <h2>Привязка модулей</h2>
    <form action="<?=$EE["unqueried_uri"]?>" method="post">
<?php 			if(!empty($MODULE_OUTPUT["nodes"])) { ?>
        <table cellspacing="3">
			<tr bgcolor="#BBBBBB">
				<td><b>Модуль</b></td>
				<td><b>Блок</b></td>
				<td><b>Позиция</b></td>
				<td><b>Параметры</b></td>
				<td align="center"><img src="/themes/images/ok.png" title="Активно" alt="" /></td>
				<td align="center"><img src="/themes/images/delete.png" title="Удалить" alt="" /></td>
				<td align="center"><b>Перенести в папку:</b></td>
				<td align="center">
					<img src="/themes/images/parse.png" title="Дальнейший обработчик">
					<input type="radio" name="<?=$NODE_ID?>[edit_parser][parser_node_id]" value="0" title="Без обработки"<?=(!$MODULE_OUTPUT["folder"]["parser_node_id"] ?	" checked='checked'" : "")?> />
				</td>
			</tr>
<?php				$i=0;
					foreach($MODULE_OUTPUT["nodes"] as $node) { ?>
            <tr<?php if ($i%2) { ?> bgcolor="#CCCCCC"<?php } ?>>
				<td><?=$MODULE_OUTPUT["modules"][$node["module_id"]]["comment"]?></td>
				<td>
					<select name="<?=$node["id"]?>[edit_node][nodegroup_id]">
<?php 					foreach($MODULE_OUTPUT["nodegroups"] as $id => $nodegroup) { ?>
						<option value="<?=$id?>"<?=($node["nodegroup_id"]==$id ? " selected='selected'" : "")?>><?=$nodegroup["comment"]?></option>
<?php 					} ?>
					</select>
				</td>
				<td><input type="text" name="<?=$node["id"]?>[edit_node][pos]" size="7" value="<?=$node["pos"]?>" /></td>
				<td><input type="text" name="<?=$node["id"]?>[edit_node][params]" value="<?=$node["params"]?>" /></td>
				<td><input type="checkbox" name="<?=$node["id"]?>[edit_node][is_active]"<?=($node["is_active"] ? " checked='checked'" : "")?>></td>
				<td><input type="checkbox" name="<?=$node["id"]?>[edit_node][delete]"></td>
				<td>
					<select name="<?=$node["id"]?>[edit_node][folder_id]" size="1" style="width: 8em;">
						<option value="<?=$MODULE_OUTPUT["folder"]["id"]?>">&nbsp;</option>
<?php   				//foreach ($MODULE_OUTPUT["folders"] as $folder) { ?>
						<option value="<?=$folder["id"]?>"><?=$folder["title"]?></option>
<?php 					//	}
						OutPutFoldersToSelect($MODULE_OUTPUT["folders"], 0); ?>
					</select>
				</td>
				<td><input type="radio" name="<?=$NODE_ID?>[edit_parser][parser_node_id]" value="<?=$node["id"]?>"<?=($MODULE_OUTPUT["folder"]["parser_node_id"] == $node["id"] ? " checked='checked'" : "")?> /></td>
			</tr>
<?php 					$i++;
					} ?>
        </table>
		<input type="hidden" name="edit_nodes" />
		<input type="submit" value="Сохранить изменения" />
<?php 			} ?>
	</form>
	<form action="<?=$EE["unqueried_uri"]?>" method="post">
		<p>Блок:</p>
		<select name="<?=$NODE_ID?>[add_node][nodegroup_id]">
<?php 			foreach($MODULE_OUTPUT["nodegroups"] as $id => $nodegroup) { ?>
			<option value="<?=$id?>"><?=$nodegroup["comment"]?></option>
<?php 			} ?>
		</select>
		<p>
			Модуль:
			<select name="<?=$NODE_ID?>[add_node][module_id]">
<?php 			foreach($MODULE_OUTPUT["modules"] as $id => $module) { ?>
				<option value="<?=$id?>"><?=$module["comment"]?></option>
<?php 			} ?>
			</select>
		</p>
		<p>
			Позиция:<br />
			<input name="<?=$NODE_ID?>[add_node][pos]" type="text" size="2" /> 
		</p>
		<p>    
			Параметры:<br />
			<input name="<?=$NODE_ID?>[add_node][params]" type="text" /> 
		</p>
		<p>
			<input type="submit" value="Привязать" />
		</p>
    </form>
<?php  		}
		}
		break;
	
		case "none": {
			
		}	
		break;
	
		default: {
			if(!empty($MODULE_OUTPUT["folders"])) {
				if (!$MODULE_OUTPUT["pid"]) { ?>
	<h2>Разделы сайта</h2>
<?php			}
				if(isset($MODULE_OUTPUT["action"])) echo $MODULE_OUTPUT["action"];
				/*echo "<pre>";
				echo print_r($MODULE_OUTPUT["folders"]);
				echo "</pre>";*/ ?>	
	<div class="folders_list">
<?php			OutPutFolders($MODULE_OUTPUT["folders"], $MODULE_OUTPUT["open"]); ?>
	</div>
<?php			if($MODULE_OUTPUT["manage_subFolders"]) { ?>
	<div>
		<i>[<a href="javascript:void(0)" onClick='return add(<?=(isset($MODULE_OUTPUT["pid"])?$MODULE_OUTPUT["pid"]:0)?>);'>добавить раздел</a>]</i>
		<div class='folder_add' id='add<?=(isset($MODULE_OUTPUT["pid"])?$MODULE_OUTPUT["pid"]:0)?>'></div>
	</div>

	<!--div>
		<i>[<a href="javascript:void(0)" onClick='return add(<?=(isset($MODULE_OUTPUT["pid"])?$MODULE_OUTPUT["pid"]:0)?>);'>добавить раздел</a>]</i>
		<div class='folder_add' id='add<?=(isset($MODULE_OUTPUT["pid"])?$MODULE_OUTPUT["pid"]:0)?>'></div>
	</div-->
<?php			}
				if ($Engine->OperationAllowed(1, "module.items.handle", 0, $Auth->usergroup_id)) {
					$moduleItemsHandle = 1;
				} else {
					$moduleItemsHandle = 0;
				} ?>
	<script>jQuery(document).ready(function($) {DefaultStructure(<?=$MODULE_OUTPUT['module_id']?>,<?=$MODULE_OUTPUT['ajax_allowed']?>,'<?=$EE["unqueried_uri"]?>',<?=$NODE_ID?>,<?=$moduleItemsHandle?>);});</script>
<?php		}
		}
	}

	function OutPutFolders($array, $open = 0) {
		$displayed = 0;
		$pid = null;
	
		foreach($array as $id => $item) {
			$pid = $item["pid"];
			continue;
		} ?>
		<ul<?=($open == 1 ? " class='open_folder'" : "")?>>
<?php	foreach($array as $id => $item) {
			if ($item["is_active"] || $item["manage_props"]) {
			$li_class = '';
			$li_class .= $item["is_active"] ? '' : 'inactive ';
			$li_class .= (!empty($item["subitems"]) || $item["have_subfolder"]) && $id ? 'no_marker ' : '';
			$li_class .= count($array) > 1 ? '' : 'one_child ';
			$li_class .= $displayed ? '' : 'first_child ';
			$li_class .= $displayed < count($array) - 1 ? '' : 'last_child '; ?>
			<li id='folder_<?=$id?>'<?=(!empty($li_class) ? " class='".$li_class."'" : "")?>><span class="slide_botton inline-block" title='Свернуть'></span><div class="folder_title">				
<?php			if($item["is_active"]) { ?>
					<a class="foldet_name" href='<?=$item["uri"]?>' title='<?=$item["descr"]?>'><?=$item["title"]?></a>           
<?php			} else {
					if($item["manage_props"]) { ?>
					<a class="foldet_name" href='<?=$item["uri"]?>' title='<?=$item["descr"]?>. Сейчас ссылка не работает, поскольку раздел скрыт!'><?=$item["title"]?></a>
<?php				}
				} ?>
					<small>
<?php			if($item["manage_props"]) {//Смена позиции
					$chain_array = $item["chain"];
					$chain = "";
						
					foreach ($chain_array as $key => $value) {
						if (!$key) $chain .= "?";
						$chain .= "pid".$key."=".$value;
						if ($key != count($chain_array)-1) $chain .= "&";
					} ?>
						<span class="pos_move">[
<?php						//if ($displayed) { ?>
							<a class="posup" href='posup/<?=$id?>/<?=$chain?>' title='Переместить на одну позицию вверх'><big>&uarr;</big></a>
<?php						//}
							//if ($displayed < count($array) - 1) { ?>
							<a class="posdown" href='posdown/<?=$id?>/<?=$chain?>' title='Переместить на одну позицию вниз'><big>&darr;</big></a>
<?php						//}
						$displayed++; ?>
						]</span><br />
<?php			}				
				if($item["manage_props"] && $id) {
					//if($item["is_active"]) { ?>
						<span  class="hide_folder">[<a href='hide/<?=$id?>/<?=$chain?>' title='Не показывать данный раздел посетителям'>скрыть</a>]</span>        
<?php				//} else { ?>
						<span  class="show_folder">[<a href='show/<?=$id?>/<?$chain?>' title='Показать данный раздел посетителям'>показать</a>]</span>
<?php				//}
				}   
				if($item["manage_subFolders"]  && $id) { ?>
						[<a title='Создать подраздел для раздела &laquo;<?=$item["title"]?>&raquo;' onClick='return add(<?=$id?>)'>создать подраздел</a>]&nbsp;
<?php			}
				//else 
				//	echo "[<span style=\"color: grey;\"\'>создать подраздел</span>]&nbsp;";
				if($item["manage_props"]) { ?>
						[<a href='edit/<?=$id?>/' title='Редактировать свойства раздела &laquo;<?=$item["title"]?>&raquo;'>редактировать</a>]&nbsp;
<?php			}				
				if($item["manage_folder.delete"] && $id) { ?>
						[<a href='delete/<?=$id?>/<?=$chain?>' title='Удалить' onClick='return del(<?=$id?>,<?=$item["pid"]?>)'>удалить</a>]
<?php			}
				//else
				//	echo "[<span style=\"color: grey;\">удалить</span>]"; ?>
					</small><em class='load'>&nbsp;</em>
				</div>
<?php			if($id) { ?>
				<div class='folder_add' id='add<?=$id?>'></div>
<?php			}
				if(!empty($item["subitems"])) {
					OutPutFolders($item["subitems"], $open);
				} ?>
			</li>
<?php		}
		} ?>
		</ul>
	
<?php	/*if($pid && !$open)
			$ul_style = " style='display: none;'";
		else
			$ul_style = "";
	
		echo "<ul id='ul_".$pid."'".$ul_style." class='sortable_li'>\n";
	
		foreach($array as $id => $item) {
			if ($item["is_active"] || $item["manage_props"]) {
				echo "<li id='folder_".$id."'>";
				if(!empty($item["subitems"]) && !$open) {
					echo "<a href='javascript:show_folders(".$id.", 1);' id='a_".$id."'><big><span id='span_".$id."' title='Развернуть'>+</span></big></a> ";
				}
				elseif(!empty($item["subitems"]) && $open) {
					echo "<a href='javascript:show_folders(".$id.", 0);' id='a_".$id."'><big><span id='span_".$id."' title='Свернуть'>-</span></big></a> ";
				}
				
				if($item["is_active"]) {
					echo "<a href='".$item["uri"]."' title='".$item["descr"]."'>".$item["title"]."</a>";            
				}
				else {
					if($item["manage_props"]) {
						echo "<a href='".$item["uri"]."' title='".$item["descr"].". Сейчас ссылка не работает, поскольку раздел скрыт!' style='color: grey;'>".$item["title"]."</a>";
					}
				}
				echo "<small>";
					
				if($item["manage_props"]) {
					if (count($array) > 1)
						echo "&nbsp;[&nbsp;";
						
					$chain_array = $item["chain"];
					$chain = "";
						
					foreach ($chain_array as $key => $value) {
						if (!$key) $chain .= "?";
						$chain .= "pid".$key."=".$value;
						if ($key != count($chain_array)-1) $chain .= "&";
					}
					
					if ($displayed)
						echo "<a href='posup/".$id."/".$chain."' title='Переместить на одну позицию вверх'><big>&uarr;</big></a>&nbsp;";
					if ($displayed < count($array) - 1)
						echo "<a href='posdown/".$id."/".$chain."' title='Переместить на одну позицию вниз'><big>&darr;</big></a>&nbsp;";
					$displayed++;
						
					if (count($array) > 1)
						echo "]";
						
					echo "<br />";
				}
				
				if($item["manage_props"] && $id) {
					if($item["is_active"]) {
						echo "[<a href='hide/".$id."/".$chain."' title='Не показывать данный раздел посетителям'>скрыть</a>]&nbsp;";            
					}
					else {            
						echo "[<a href='show/".$id."/".$chain."' title='Показать данный раздел посетителям'>показать</a>] ";
					}
				}   
				if($item["manage_subFolders"]  && $id) {
					echo "[<a title='Создать подраздел для раздела &laquo;".$item["title"]."&raquo;' onClick='return add(".$id.")' style='cursor: pointer;'>создать подраздел</a>]&nbsp;";
				}
				//else 
				//	echo "[<span style=\"color: grey;\"\'>создать подраздел</span>]&nbsp;";
				
				if($item["manage_props"]) {
					echo "[<a href='edit/".$id."/' title='Редактировать свойства раздела &laquo;".$item["title"]."&raquo;'>редактировать</a>]&nbsp;";
				}
				
				if($item["manage_folder.delete"] && $id) {
					echo "[<a href='delete/".$id."/".$chain."' title='Удалить' onClick='return del(".$id.")'>удалить</a>] ";
				}
				//else
				//	echo "[<span style=\"color: grey;\">удалить</span>]";
				echo "</small><em class='load'>&nbsp;</em>\n";
				if($id) {
					echo "<div class='folder_add' id='add".$id."'></div>";
				}
				if(!empty($item["subitems"])) {
					OutPutFolders($item["subitems"], $open);
				}
				echo "</li>\n";
			}
		}
		echo "</ul><script language='javascript'>open_folders();</script>\n";*/
	}

	function OutPutFoldersToSelect($array, $depth, $selected = NULL) {
		$displayed = 0;
		//die(print_r($array));
		$level = "";
		for ($i = $depth; $i > 0; $i--)
			$level .= '-';
	
		foreach($array as $id => $item) {		
			if (isset($selected) && $id == $selected) {
				$select_flag = " selected='selected'";
			}
			else {
				$select_flag = " ";
			}	
			if($item["is_active"]) {
			    echo "<option value='".$item["id"]."'".$select_flag.">".$level." ".$item["title"]."</option>";
			}
			else {
			    echo "<option value='".$item["id"]."'".$select_flag.">".$level." ".$item["title"]." (не активен)</option>";
			}		
			if(!empty($item["subitems"])) {
				OutPutFoldersToSelect($item["subitems"], $depth+1, $selected);
			}
		}
	}
?>
