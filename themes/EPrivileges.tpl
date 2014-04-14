<?php
// version: 1.1
// date: 2014-01-15

foreach ($MODULE_DATA["output"]["messages"]["good"] as $data)
{
	echo "<p class=\"message\">$data</p>\n";
}

foreach ($MODULE_DATA["output"]["messages"]["bad"] as $data)
{
	echo "<p class=\"message red\">$data</p>\n";
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

if(!isset($MODULE_DATA["output"])) $MODULE_DATA["output"] = $MODULE_OUTPUT; 
if (ALLOW_AUTH)
{
	switch ($MODULE_DATA["output"]["mode"])
	{
		case "show_modules":
			?><h2>Выберите модуль:</h2>
			<a href="<?=$EE["unqueried_uri"]?>0/">Системные операции</a><br /><?
			foreach ($MODULE_DATA["output"]["modules"] as $module) {
				?><a href="<?=$EE["unqueried_uri"]?><?=$module["id"]?>/"><?=$module["comment"]?></a><br /><?
			}
			break;

		case "privileges_form":
			if($MODULE_DATA["output"]["usergroup_id"] == NULL) {
      if ($MODULE_DATA["output"]["module_name"] != "Системные операции") {
				?><h2>Операции модуля "<?=$MODULE_DATA["output"]["module_name"]?>"</h2><?
			}
			else {
				 ?><h2>Системные операции</h2><?
			}
			?>
	      <style>
	      .loading img { margin: 0px; }
	      </style>
	      <script>
	      jQuery(document).ready(function($) {
				Select_Object(<?=$MODULE_OUTPUT['current_module_id']?>, <?=$MODULE_OUTPUT['module_id']?>);
			});			
		</script>
			<form action="<?=$EE["unqueried_uri"]?>" method="post">
			
			<input type="hidden" name="<?=$NODE_ID?>[give_privileges][module_id]" value="<?=$MODULE_DATA["output"]["module_id"]?>">
			
			<p>Операция:<br />					
			
			<select name="<?=$NODE_ID?>[give_privileges][operation_name]" id="select_operation" size="1" onchange="">
			<option value="---"></option>			
			?>	
			<?
			foreach ($MODULE_DATA["output"]["operations"] as $operation) {
				?><option value="<?=$operation["operation_name"]?>">
				<?php
				if ($operation["comment"])
					echo $operation["comment"];
				else
					echo $operation["operation_name"];
				?>
				</option><?
			}
			?>
			</select><span class="red loading hidden" id="loading"></span>
			</p>
			
			<p>Группа пользователей:<br />
			<select name="<?=$NODE_ID?>[give_privileges][usergroup_id]" size="1">
			<option value="-1">Все пользователи</option>
			<option value="0">Незарегистрированные пользователи</option><?
			foreach ($MODULE_DATA["output"]["usergroups"] as $usergroup) {
				?><option value="<?=$usergroup["id"]?>"><?=$usergroup["comment"]?></option><?
			}
			?>
			</select></p>
			
			<p class="hidden">Элемент:<br />
			
			<select name="<?=$NODE_ID?>[give_privileges][table_name]" id="select_element" class="select_element" size="1">
      <option value="-1">Все элементы</option>
      <?
			foreach ($MODULE_DATA["output"]["name_elements"] as $element) {
			
			/*	//if(isset($_POST['$NODE_ID[give_privileges][operation_name]'])){
				?><option value="<?=$element["id"]?>"><?=$element["name"]?>
				
				</option><?
				//}  */
			}
			?>
			</select>
      <input type="text" id="element_search" class="element_search" size="10" title="Быстрый поиск" placeholder="Введите часть названия" style="display: none; width: 265px; " />
      <a href="javascript: void(0)" onclick="jQuery(this).parent().find('.element_search').show(); jQuery(this).parent().find('.select_element').hide(); jQuery(this).hide(); " class="action_edit" title="Поиск по фрагменгту названия" id="element_search_link" style="display: none; ">Искать</a>
      </p>
			
			<p>ID элемента (entry):<br />
			<input type="text" id="entry_id" name="<?=$NODE_ID?>[give_privileges][entry_id]" size="3" disabled>
			<input type="checkbox" name="<?=$NODE_ID?>[give_privileges][all_entries]" id="for_all_entries" onclick="if(!this.checked) document.getElementById('entry_id').removeAttribute('disabled'); else document.getElementById('entry_id').setAttribute('disabled', '1');" checked>
			Для всех элементов</p>
			
			<p>Действие:<br />
			<select name="<?=$NODE_ID?>[give_privileges][is_allowed]" size="1">
			<option value="1">Разрешить</option>
			<option value="0">Запретить</option>
			</select></p>
			
			<p><input type="submit" value="Готово"></p><hr><?php } ?>
			<h2>Выданные права</h2>
			<ul><?
			if (isset($MODULE_DATA["output"]["privileges"]))
			foreach ($MODULE_DATA["output"]["privileges"] as $privilege) {
				?><li><? if (!$privilege["is_allowed"]) { ?>Запретить<? } else { ?>Разрешить<? }
				if ($privilege["usergroup"] == "-1") { ?> всем пользователям<? }
				elseif (!$privilege["usergroup"]) { ?> незарегистрированным пользователям<? }
				else { ?> группе пользователей "<?=$privilege["usergroup"]?>" <? } ?>
				действие "<?=$privilege["operation"]?>"
				<? if ($privilege["entry_id"] == "-1") { ?> на всех элементах<? }
				else if($privilege["entry"]) { ?> на элементе "<?=$privilege["entry"]["name"]?>"<? }
				else { ?> на элементе <?=$privilege["entry_id"]?><? } ?>. 
				<a href="?delete_name=<?=$privilege["operation_code"]?>&delete_module=<?=$privilege["module_id"]?>&delete_usergroup=<?=$privilege["usergroup_id"]?>&delete_entry=<?=$privilege["entry_id"]?>&delete_allowed=<?=$privilege["is_allowed"]?>" onclick="if(!confirm('Вы действительно хотите удалить эту привилегию?')) return false;"><img src="/themes/images/delete.png"></a>
				<?
			} ?>
			</ul>
			<?
			
			break;
      
    case "ajax_get_elements":  
    ?>
    <select name="<?=$NODE_ID?>[give_privileges][table_name]" id="select_element" class="select_element" size="1">
      <?php /* ?><option value="-1">Все элементы</option><?php */ ?>
      <?
			foreach ($MODULE_DATA["output"]["elements"] as $element) {
			
				//if(isset($_POST['$NODE_ID[give_privileges][operation_name]'])){
				?><option value="<?=$element["id"]?>"><?=$element["name"]?>
				
				</option><?
				//}
			}
			?>
			</select>
    <?php 
    break;
			
		case "message":
		
			break;
	}
}

?>