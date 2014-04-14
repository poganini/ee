<?php

foreach ($MODULE_DATA["output"]["messages"]["good"] as $data)
{
	echo "<p class=\"message\">$data</p>\n";
}

foreach ($MODULE_DATA["output"]["messages"]["bad"] as $data)
{
	echo "<p class=\"message red\">$data</p>\n";
}

if ((isset($_GET["desc"]) && $_GET["desc"]) || !isset($_GET["desc"])) $desc = $saldesc = 0;
else $desc = $saldesc = 1;

if (!isset($_GET["sortby"]) || (isset($_GET["sortby"]) && $_GET["sortby"] != "salary"))
	$saldesc = 1;

	switch ($MODULE_DATA["output"]["mode"])
	{
		case "vacancies_list":
			echo "<h2>Вакансии</h2>";
			
			?><fieldset><legend>Критерии поиска</legend>
      <div class="form-notes" style="width: 49%; ">
      <form method="post" action="/vacancies/">
			<p>Район:<br />
			<select size="1" name="<?=$NODE_ID?>[filter][district]"><option value="0">Не важно</option>
			<?php
				foreach ($MODULE_DATA["output"]["districts"] as $dis) {
					?><option value="<?=$dis["district"]?>"<? if(isset($_SESSION["filter_district"]) && $_SESSION["filter_district"]==$dis["district"]) { ?> selected<? } ?>><?=$dis["district"]?></option><?php
				}
			?>
			</select></p>
			
			<p>Зарплата от <input type="text" name="<?=$NODE_ID?>[filter][salary_from]" size="3"<? if(isset($_SESSION["filter_salary_from"])) echo ' value="'.$_SESSION["filter_salary_from"].'"'; ?>> р. до <input type="text" name="<?=$NODE_ID?>[filter][salary_to]" size="3"<? if(isset($_SESSION["filter_salary_to"])) echo ' value="'.$_SESSION["filter_salary_to"].'"'; ?>> р.</p>
			
			<p>Жильё:<br />
			<select size="1" name="<?=$NODE_ID?>[filter][lodging]"><option value="0">Не важно</option>
			<?php
				foreach ($MODULE_DATA["output"]["lodgings"] as $lod) {
					?><option value="<?=$lod["id"]?>"<? if(isset($_SESSION["filter_lodging"]) && $_SESSION["filter_lodging"]==$lod["id"]) { ?> selected<? } ?>><?=$lod["lodging"]?></option><?php
				}
			?>
			</select></p>
      
      <p>Название:<br />
			<select size="1" name="<?=$NODE_ID?>[filter][vacancy]"><option value="">Не важно</option>
			<?php
				foreach ($MODULE_DATA["output"]["titles"] as $title) {
					?><option value="<?=$title['vacancy']?>"<? if(isset($_SESSION["filter_vacancy"]) && $_SESSION["filter_vacancy"]==$title['vacancy']) { ?> selected<? } ?>><?=$title['vacancy']?></option><?php
				}
			?>
			</select></p>
      
      <p>Специальность:<br />
			<select size="1" name="<?=$NODE_ID?>[filter][speciality_id]"><option value="">Не важно</option>
			<?php
				foreach ($MODULE_DATA["output"]["specialities"] as $spec) {
					?><option value="<?=$spec['code']?>"<? if(isset($_SESSION["filter_speciality_id"]) && $_SESSION["filter_speciality_id"]==$spec['code']) { ?> selected<? } ?>><?=$spec['name']?></option><?php
				}
			?>
			</select></p>
      
      <p>Факультет:<br />
			<select size="1" name="<?=$NODE_ID?>[filter][faculty_id]"><option value="">Не важно</option>
			<?php
				foreach ($MODULE_DATA["output"]["faculties"] as $fac) {
					?><option value="<?=$fac['id']?>"<? if(isset($_SESSION["filter_faculty_id"]) && $_SESSION["filter_faculty_id"]==$fac['id']) { ?> selected<? } ?>><?=$fac['name']?></option><?php
				}
			?>
			</select></p>
			
			<p><input type="submit" value="Посмотреть"></p>
			
			</form>
      </div>
      <div class="form-notes" style="width: 49%; text-align: right; padding-right: 1%; padding-left: 0%; ">
      <a href="/about/department/practice_placement/">Отдел практик и трудоустройства</a>
      </div>
      </fieldset>
			
			<?
			
			if ($MODULE_DATA["output"]["show_manage"])
				echo "<a href=\"add/\"><b>Добавить вакансию</b></a><br>";
			echo "<table cellpadding=\"0\" cellspacing=\"3\" border=\"0\">";
			echo "<tr bgcolor=\"#BBBBBB\"><td><b><a href=\"?sortby=vacancy&desc=".$desc."\">Вакансия</a></b></td><td><b><a href=\"?sortby=salary&desc=".$saldesc."\">Зарплата</a></b></td><td><b><a href=\"?sortby=district&desc=".$desc."\">Район</a></b></td><td><b><a href=\"?sortby=lodging_id&desc=".$desc."\">Жильё</a></b></td><td><b><a href=\"?sortby=pub_date&desc=".$desc."\">Опубликовано</a></b></td></tr>";
			$i = 0;
			$manage = "";
			foreach ($MODULE_DATA["output"]["vacancies_list"] as $vac) {
				if ($i%2)
					$bgcolor = " bgcolor=\"#EEEEEE\"";
				else
					$bgcolor = "";
				if ($MODULE_DATA["output"]["show_manage"])
					$manage = "<br /><a href=\"edit/".$vac["id"]."/\">Редактировать</a> <a href=\"delete/".$vac["id"]."/\" onclick=\"if (!confirm('Удалить вакансию?')) return false;\">Удалить</a>";
					
				if (!$vac["salary"]) $vac["salary"] = "По договорённости";
				echo "<tr".$bgcolor."><td><a href=\"".$vac["id"]."/\">".$vac["vacancy"]."</a><br><small><a href=\"?{$NODE_ID}[filter][company]=".urlencode($vac["company"])."\" title=\"Показать все вакансии этой организации\" style=\"color: #000000; \">".$vac["company"]."</a>".$manage."</td><td>".$vac["salary"]."</small></td><td>".$vac["district"]."</td><td>".$vac["lodging"]."</td><td>".$vac["vac_date"]."</td></tr>";
				$i++;
			}
			echo "</table>";
			
		$pager_output = $MODULE_DATA["output"]["pager_output"];
		
		if (count($pager_output["pages_data"]) > 1) // если есть что выводить, и страниц больше одной
        {
            echo "<div class=\"pager\">\n";
            
            if($pager_output["prev_page"])
            {
                echo "<a href=\"{$pager_output["pages_data"][$pager_output["prev_page"]]["link"]}\" title=\"Предыдущая страница\"";
                echo " id=\"prev_page_link\" class=\"prev-next-link\"><span class=\"arrow\">&larr;&nbsp;</span>";
                echo $pager_output["prev_page"] ? "</a>" : "</span>";
                echo " ";
            }

            foreach ($pager_output["pages_data"] as $page => $data)
            {
                if (is_int($page))
                {
                    echo ($data["is_current"] ? "<strong>" : "<a href=\"{$data["link"]}\">") . $page . ($data["is_current"] ? "</strong>" : "</a>") . " ";
                }

                else
                {
                    echo "&hellip; ";
                }
            }

            if ($pager_output["next_page"])
            {
                echo "<a href=\"{$pager_output["pages_data"][$pager_output["next_page"]]["link"]}\" title=\"Следующая страница\"";
                echo " id=\"next_page_link\" class=\"prev-next-link\">" /*. $LANGUAGE["news"]["next"]*/ . "<span class=\"arrow\">&nbsp;&rarr;</span>";
                echo "</a>";
            }
            echo "    </p>\n";
            echo "</div>\n";
			
		}
			
		break;
		
		case "vacancy_edit":
			?> 
      
			<h2>Редактирование вакансии</h2>
			<form action="<?=$EE["unqueried_uri"]?>" method="post">
			<p>Район:<br>
			<input type="text" name="<?=$NODE_ID?>[edit_vacancy][district]" value="<?=$MODULE_DATA["output"]["vacancy"]["district"]?>">
			<p>Вакансия:<br>
			<input type="text" name="<?=$NODE_ID?>[edit_vacancy][vacancy]" value="<?=$MODULE_DATA["output"]["vacancy"]["vacancy"]?>">
			<p>Компания:<br>
			<input type="text" name="<?=$NODE_ID?>[edit_vacancy][company]" value="<?=$MODULE_DATA["output"]["vacancy"]["company"]?>">
			<p>Образование:<br>
			<select size="1" name="<?=$NODE_ID?>[edit_vacancy][education_id]"><option value="0"></option>
			<?php
				foreach ($MODULE_DATA["output"]["educations"] as $edu) {
					?><option value="<?=$edu["id"]?>"<?php if($MODULE_DATA["output"]["vacancy"]["education_id"] == $edu["id"]) { ?> selected<?php } ?>><?=$edu["education"]?></option><?php
				}
			?>
			</select>
			<p>Особые требования:<br>
			<input type="text" name="<?=$NODE_ID?>[edit_vacancy][requirements]" value="<?=$MODULE_DATA["output"]["vacancy"]["requirements"]?>">
			<p>Оплата:<br>
			<input type="text" name="<?=$NODE_ID?>[edit_vacancy][salary]" value="<?=$MODULE_DATA["output"]["vacancy"]["salary"]?>">
			<p>Жильё:<br>
			<select size="1" name="<?=$NODE_ID?>[edit_vacancy][lodging_id]"><option value="0"></option>
			<?php
				foreach ($MODULE_DATA["output"]["lodgings"] as $lod) {
					?><option value="<?=$lod["id"]?>"<?php if($MODULE_DATA["output"]["vacancy"]["lodging_id"] == $lod["id"]) { ?> selected<?php } ?>><?=$lod["lodging"]?></option><?php
				}
			?>
			</select>
			<p>Контакты:<br>
			<input type="text" name="<?=$NODE_ID?>[edit_vacancy][contacts]" value="<?=$MODULE_DATA["output"]["vacancy"]["contacts"]?>">
			<p>Факультет:<br>
			<select size="1" name="<?=$NODE_ID?>[edit_vacancy][faculty_id]"><option value="0"></option>
			<?php
				foreach ($MODULE_DATA["output"]["faculties"] as $fac) {
					?><option value="<?=$fac["id"]?>"<?php if($MODULE_DATA["output"]["vacancy"]["faculty_id"] == $fac["id"]) { ?> selected<?php } ?>><?=$fac["name"]?></option><?php
				}
			?>
			</select>
			<p>Специальность:<br>
			<select size="1" name="<?=$NODE_ID?>[edit_vacancy][speciality_id]"><option value="0"></option>
			<?php
				foreach ($MODULE_DATA["output"]["specialities"] as $spec) {
					?><option value="<?=$spec["code"]?>"<?php if($MODULE_DATA["output"]["vacancy"]["speciality_id"] == $spec["code"]) { ?> selected<?php } ?>><?=$spec["name"]?></option><?php
				}
			?>
			</select>
      <p>Дата публикации:<br>
			<input type="text" class="date_time" name="<?=$NODE_ID?>[edit_vacancy][pub_date]" value="<?=$MODULE_DATA["output"]["vacancy"]["pub_date"]?>">
			<p><input type="submit" value="Готово"></p>
			</form>
			<?php
		break;
		
		case "vacancy_add":
			?>
			<h2>Добавление вакансии</h2>
			<form action="<?=$EE["unqueried_uri"]?>" method="post">
			<p>Район:<br>
			<input type="text" name="<?=$NODE_ID?>[add_vacancy][district]">
			<p>Вакансия:<br>
			<input type="text" name="<?=$NODE_ID?>[add_vacancy][vacancy]">
			<p>Компания:<br>
			<input type="text" name="<?=$NODE_ID?>[add_vacancy][company]">
			<p>Образование:<br>
			<select size="1" name="<?=$NODE_ID?>[add_vacancy][education_id]"><option value="0"></option>
			<?php
				foreach ($MODULE_DATA["output"]["educations"] as $edu) {
					?><option value="<?=$edu["id"]?>"><?=$edu["education"]?></option><?php
				}
			?>
			</select>
			<p>Особые требования:<br>
			<input type="text" name="<?=$NODE_ID?>[add_vacancy][requirements]">
			<p>Оплата:<br>
			<input type="text" name="<?=$NODE_ID?>[add_vacancy][salary]" value="0">
			<p>Жильё:<br>
			<select size="1" name="<?=$NODE_ID?>[add_vacancy][lodging_id]"><option value="0"></option>
			<?php
				foreach ($MODULE_DATA["output"]["lodgings"] as $lod) {
					?><option value="<?=$lod["id"]?>"><?=$lod["lodging"]?></option><?php
				}
			?>
			</select>
			<p>Контакты:<br>
			<input type="text" name="<?=$NODE_ID?>[add_vacancy][contacts]">
			<p>Факультет:<br>
			<select size="1" name="<?=$NODE_ID?>[add_vacancy][faculty_id]"><option value="0"></option>
			<?php
				foreach ($MODULE_DATA["output"]["faculties"] as $fac) {
					?><option value="<?=$fac["id"]?>"><?=$fac["name"]?></option><?php
				}
			?>
			</select>
			<p>Специальность:<br>
			<select size="1" name="<?=$NODE_ID?>[add_vacancy][speciality_id]"><option value="0"></option>
			<?php
				foreach ($MODULE_DATA["output"]["specialities"] as $spec) {
					?><option value="<?=$spec["code"]?>"><?=$spec["name"]?></option><?php
				}
			?>
			</select> 
      <p>Дата публикации:<br>
			<input type="text" class="date_time" name="<?=$NODE_ID?>[add_vacancy][pub_date]" value="<?=date('Y-m-d')?>">
			<p><input type="submit" value="Готово"></p>
			</form>
			<?php				
		break;
		
		case "vacancy_view":
			$vac = $MODULE_DATA["output"]["vacancy"];
      if (!$vac["salary"]) $vac["salary"] = "По договорённости";
			?>
			<h2><?=$vac["vacancy"]?></h2>
			<b>Компания:</b> <?=$vac["company"]?><br>
			<b>Район:</b> <?=$vac["district"]?><br>
			<b>Оплата труда:</b> <?=$vac["salary"]?><br>
			<b>Образование:</b> <?=$vac["education"]?><br>
			<b>Особые требования:</b> <?=$vac["requirements"]?><br>
			<b>Жильё:</b> <?=$vac["lodging"]?><br>
			<b>Контакты:</b> <?=$vac["contacts"]?><br>
			<b>Факультет:</b> <?=$vac["faculty"]?><br>
			<b>Специальность:</b> <?=$vac["speciality"]?><br>
			<?php  
      if ($MODULE_DATA["output"]["show_manage"]) {
      ?>
      <br />
      <a href="<?=$EE['engine_uri']?>edit/<?=$vac["id"]?>/">Редактировать</a> <a href="<?=$EE['engine_uri']?>delete/<?=$vac["id"];?>/" onclick="if (!confirm('Удалить вакансию?')) return false;">Удалить</a>
      <?php }
		break;
	}