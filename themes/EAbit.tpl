<?php
// version: 1.8.5
// date: 2014-07-01
global $Engine, $Auth, $EE;

//$MODULE_OUTPUT = $MODULE_DATA["output"];

/*if ($MODULE_OUTPUT["messages"]["good"]) {
    $array = array();
    foreach ($MODULE_OUTPUT["messages"]["good"] as $elem) {
        if (isset($MODULE_MESSAGES[$elem])) {
            $array[] = $MODULE_MESSAGES[$elem];
        }
    }
    if ($array) {
        foreach ($array as $elem) {
            echo "<p class=\"message\">$elem</p>\n";
        }
    }
}
if ($MODULE_OUTPUT["messages"]["bad"] && isset($MODULE_OUTPUT["display_variant"]) && !in_array($MODULE_OUTPUT["display_variant"], array("add_item", "edit_item"))) {
    $array = array();
    foreach ($MODULE_OUTPUT["messages"]["bad"] as $elem) {
        if (isset($MODULE_MESSAGES[$elem])) {
            $array[] = $MODULE_MESSAGES[$elem];
        }
    }
    if ($array) {
        foreach ($array as $elem) {
            echo "<p class=\"message red\">$elem</p>\n";
        }
    }
}*/

if($MODULE_OUTPUT["mode"] == "ajax_abitfile") {
	//$content = ob_get_contents();
	//ob_end_clean(); 
	foreach ($MODULE_OUTPUT["messages"]["bad"] as &$elem){
		$elem = iconv("windows-1251", "utf-8", $elem); 
	}
	foreach ($MODULE_OUTPUT["messages"]["good"] as &$elem){
		$elem = iconv("windows-1251", "utf-8", $elem);
	}
	//echo(json_encode($MODULE_OUTPUT["messages"]));
	//exit; 
}

foreach ($MODULE_DATA["output"]["messages"]["good"] as $data) {
	echo "<p class=\"message\">$data</p>\n";
}

foreach ($MODULE_DATA["output"]["messages"]["bad"] as $data) {
	echo "<p class=\"message red\">$data</p>\n";
}

/*if(!isset($_GET["secrett"]))
	exit(1);*/

if (isset($MODULE_OUTPUT['requests_info'])) {
	$requests_info = $MODULE_OUTPUT['requests_info'];
	$updateDate = $MODULE_OUTPUT["date"]; ?>
	<h2 style="text-align: center;">Информация о количестве поданных заявлений в разрезе <?=($MODULE_OUTPUT["request_mode"] == "spo"? "специальностей" :"направлений (специальностей)")?> на <?=$updateDate?>г.</h2>
	<table style="border-collapse: separate;" border="1" cellspacing="1" cellpadding="1" width="620" align="center">
		<tbody>
			<tr>
				<td style="width: 50px;" align="center" valign="middle" bgcolor="#f9dfc5">
					<div>Код</div>
				</td>
				<td align="center" valign="middle" bgcolor="#f9dfc5"><?=($MODULE_OUTPUT["request_mode"] == "spo" ? "Специальность" : "Направление (специальность)")?></td>
				<?php if ($MODULE_OUTPUT["request_mode"] != "vpo-ev") { ?>
				<td bgcolor="#f9dfc5">
					<p style="text-align: center;">Кол-во бюджетных мест</p>
				</td>
				<?php } ?>
				<td bgcolor="#f9dfc5">
					<p style="text-align: center;">Кол-во коммерческих мест</p>
				</td>
				<td style="width: 20px;" align="center" valign="middle" bgcolor="#f9dfc5">
					<div>Кол-во поданных заявлений</div>
				</td>
				<td style="background-color: #f9dfc5;">&nbsp;Конкурс</td>
			</tr>
<?php
	foreach ($requests_info as $faculty_name=>$faculty_info) {
		if (($MODULE_OUTPUT["request_mode"] != "spo" &&  $MODULE_OUTPUT["request_mode"] != "spo-ex")|| $faculty_name == "sum") { ?>
			<tr style="background-color: #ccff33;">
				<td style="text-align: center;" colspan="2"><strong><?=($faculty_name!='sum'? $faculty_name :'Итого'.($MODULE_OUTPUT["request_mode"] == "spo" ? "" : " по университету"))?></strong></td>
				<?php if ($MODULE_OUTPUT["request_mode"] != "vpo-ev") { ?>
				<td style="text-align: center;"><strong><?=$faculty_info['budget']?></strong><br /></td>
				<?php } ?>
				<td style="text-align: center;"><strong><?=$faculty_info['commerce']?></strong><br /></td>
				<td><div style="text-align: center;"><strong><?=$faculty_info['req_count']?></strong></div></td>
				<td style="text-align: center;"><?=($faculty_info['contest']?:'');?></td>
			</tr>
<?php 	}
		foreach ($faculty_info['specs'] as $spec_info) { ?>
			<tr style="text-align:center">
				<td>
					<div><a href="/entrant/list/?type=<?=$spec_info->id?>"><?=$spec_info->code?></a></div>
				</td>
				<td style="text-align: left;" height="20"><a href="<?=$MODULE_OUTPUT['abit_list_base_href'].$spec_info->id?>"><?=trim($spec_info->type)?></a></td>
				<?php if ($MODULE_OUTPUT["request_mode"] != "vpo-ev") { ?>
				<td style="text-align: center;"><?=$spec_info->budget?></td>
				<?php } ?>
				<td style="text-align: center;"><?=$spec_info->commerce?></td>
				<td style="text-align: center;"><?=$spec_info->req_count?></td>
				<td style="text-align: center;"><?=($spec_info->contest?:'')?></td>
			</tr>
<?php	}
	} ?>
		</tbody>
	</table>
<?php
} else if (isset($MODULE_OUTPUT['edit_info'])) { ?>
	<div style="margin: 0 auto; padding-top: 20px; width: 625px">
		<form action="<?=$MODULE_OUTPUT['uri']?>" method="post" onsubmit="javascript: return do_submit()">
			<input type="hidden" id="post_data" name="data_str" value="">
			<table id='types_info' style="border-collapse: separate;" border="1" cellspacing="1" cellpadding="1" width="620" align="center">
				<tbody>
					<tr>
						<td align="center" valign="middle" bgcolor="#f9dfc5">Код</td>
						<td align="center" valign="middle" bgcolor="#f9dfc5">Направление (специальность)</td>
						<td bgcolor="#f9dfc5">
							<p style="text-align: center;">Кол-во бюджетных мест</p>
						</td>
						<td bgcolor="#f9dfc5">
							<p style="text-align: center;">Кол-во бюджетных мест (заочная форма)</p>
						</td>
						<td bgcolor="#f9dfc5">
							<p style="text-align: center;">Кол-во коммерческих мест</p>
						</td>
						<td bgcolor="#f9dfc5">
							<p style="text-align: center;">Кол-во целевых мест</p>
						</td>
					</tr>
<?php
	$faculty_ind = 0;
	foreach ($MODULE_OUTPUT['edit_info'] as $faculty_name=>$faculty_info) {
		$faculty_ind++; ?>
					<tr style="background-color: #ccff33;">
						<td colspan="2"><strong><?=($faculty_name!='sum'? $faculty_name :'Итого по университету')?></strong></td>
						<td style="text-align: center; font-weight: bold;" id="<?=($faculty_name!='sum'?$faculty_ind:'sum').'-budget'?>"><?=$faculty_info['budget']?></td>
						<td style="text-align: center; font-weight: bold;" id="<?=($faculty_name!='sum'?$faculty_ind:'sum').'-budget_ext'?>"><?php if($faculty_info['has_ext']) { ?><?=$faculty_info['budget_ext']?><?php } else { ?>&nbsp;<?php } ?></td>
						<td style="text-align: center; font-weight: bold;" id="<?=($faculty_name!='sum'?$faculty_ind:'sum').'-commerce'?>"><?=$faculty_info['commerce']?></td>
						<td style="text-align: center; font-weight: bold;" id="<?=($faculty_name!='sum'?$faculty_ind:'sum').'-purpose'?>"><?=$faculty_info['purpose']?></td>
					</tr>
<?php
		foreach ($faculty_info['specs'] as $spec_info) { ?>
					<tr style="text-align:center">
						<td style="text-align: left;" height="20"><a href="/office/speciality_directory/edit_speciality/<?=$spec_info->spec_id; ?>/"><?=trim($spec_info->code)?></a></td>
						<td style="text-align: left;" height="20"><a href="/office/speciality_directory/edit_speciality/<?=$spec_info->spec_id; ?>/"><?=trim($spec_info->type)?></a></td>
						<td style="text-align: center;"><?=$MODULE_OUTPUT["auth"]?'<input class="info_cell" type="text" id="'.$faculty_ind.'-budget-'.$spec_info->id.'" style="text-align:center; width:40px; border: 1px solid;" value="'.$spec_info->budget.'"/>':$spec_info->budget?></td>
						<td style="text-align: center;"><?php if($spec_info->has_ext) { ?><?=$MODULE_OUTPUT["auth"]?'<input class="info_cell" type="text" id="'.$faculty_ind.'-budget_ext-'.$spec_info->id.'" style="text-align:center; width:40px; border: 1px solid;" value="'.$spec_info->budget_ext.'"/>':$spec_info->budget_ext?><?php } else { ?><?php echo '<input type="hidden" id="'.$faculty_ind.'-budget_ext-'.$spec_info->id.'" style="text-align:center; width:40px; border: 1px solid;" value="'.$spec_info->budget_ext.'"/>';?><?php } ?></td>
						<td style="text-align: center;"><?=$MODULE_OUTPUT["auth"]?'<input class="info_cell" type="text" id="'.$faculty_ind.'-commerce-'.$spec_info->id.'" style="text-align:center; width:40px; border: 1px solid;" value="'.$spec_info->commerce.'"/>':$spec_info->commerce?></td>
						<td style="text-align: center;"><?=$MODULE_OUTPUT["auth"]?'<input class="info_cell" type="text" id="'.$faculty_ind.'-purpose-'.$spec_info->id.'" style="text-align:center; width:40px; border: 1px solid;" value="'.$spec_info->purpose.'"/>':$spec_info->purpose?></td>
					</tr>
<?php 	}
	} ?>
				</tbody>
			</table>
      
<?php
	if ($MODULE_OUTPUT["auth"]) { ?>
			<div style="margin-top:10px; text-align: right;"><input type="submit"  value="Сохранить" /></div>
			<script>jQuery(document).ready(function($) {setKeyboardHandlers();});</script>
<?php
	} ?>
		</form>
	</div>
  <div style="margin: 0 auto; padding-top: 20px; width: 625px">
  <?php 
  foreach ($MODULE_OUTPUT["messages"]["bad"] as &$elem){
		$elem = iconv("windows-1251", "utf-8", $elem); 
	}
	foreach ($MODULE_OUTPUT["messages"]["good"] as &$elem){
		$elem = iconv("windows-1251", "utf-8", $elem);
	}
  ?>
	<h2>Добавить направление</h2>
		<form action="<?=$EE["unqueried_uri"]?>" method="post">
		<table border="1" cellpadding="1" cellspacing="1">
			<tr>
				<td rowspan="2" bgcolor="#f9dfc5" align="center">Код</td>
				<td rowspan="2" bgcolor="#f9dfc5" align="center">Направление (специальность)</td>
        <td rowspan="2" bgcolor="#f9dfc5" align="center">Квалификация</td>
				<td bgcolor="#f9dfc5" colspan="2" align="center">Количество бюджетных мест</td>
				<td rowspan="2" bgcolor="#f9dfc5" align="center">Количество коммерческих мест</td>
				<td rowspan="2" bgcolor="#f9dfc5" align="center">Порядок сортировки</td>
				<?php /* ?><td rowspan="2" bgcolor="#f9dfc5" align="center">Профиль направления</td><?php */ ?>
			</tr>
			<tr>
				<td bgcolor="#f9dfc5" align="center">&nbsp;</td>
				<td bgcolor="#f9dfc5" align="center">В т.ч. целевых</td>
			</tr>
			<tr>
					<td><input type="text" name="code" value="" size="6" /></td>
					<td><input type="text" name="type" value="" /></td> 
          <?php  ?><td><select name="qualification" style="width: 300px; ">
          <option value="0">Не указана</option>
          <?php foreach($MODULE_OUTPUT["qualifications"] as $qualification) { ?>
          <option value="<?php echo $qualification->id; ?>"><?php echo $qualification->name; ?></option>
          <?php } ?>
          </select></td><?php  ?>
					<td><input type="text" name="budget" size="6" value="" /><input type="hidden" name="id" value="" /></td>
					<td><input type="text" name="purpose" size="6" value="" /></td>
					<td><input type="text" name="commerce" size="6" value="" /></td>
					<td><input type="text" name="sortorder" size="2" value="" /></td>
					<?php /* ?><td>
						<select name="parent" style="width: 10ex;">
						<option value="0">--</option>
						<?php
						foreach($MODULE_OUTPUT["types"] as $type) {
							if($type['pid'] == 0) {
							?>
							<option value="<?=$type['id']?>"><?=$type["type"]?></option>
							<?php 
							}
	} 
	?>
						</select>
					</td><?php */ ?>
				</tr>
		</table>
			<!--<h3>Название направления</h3>
			<input type="text" name="type" />
			<h3>Код</h3>
			<input type="text" name="code" />  -->
			<input class="button" type="submit" value="Добавить" />
			<input type="hidden" name="mode" value="add" />
		</form>
	</div>
<?php
} elseif (isset($MODULE_OUTPUT["has_info"]) && $MODULE_OUTPUT["has_info"]) { ?>
	<div id="daystogo">
<?php if (isset($MODULE_OUTPUT["show_mode"]) && $MODULE_OUTPUT["show_mode"] == 'text') { ?>
		<div class="text" >
<?php } ?>
			<span class="title"><?=$MODULE_OUTPUT["notice"]?></span>
<?php if (isset($MODULE_OUTPUT["show_mode"]) && $MODULE_OUTPUT["show_mode"] == 'text') { ?>
		</div>
<?php } else { ?>
		<div class="counter">

			<div class="numb"><?=$MODULE_OUTPUT["days"]?></div>
			<span class="days"><?=$MODULE_OUTPUT["word"]?></span>
		</div>
<?php } ?>
	</div>
<?php
} elseif (isset($MODULE_OUTPUT["abitfile"])) { ?>
  <script>/*jQuery(document).ready(function($) { AjaxAbitForm(<?=$MODULE_OUTPUT['module_id']?>, '<?=$MODULE_OUTPUT['abitprogressfile']?>'); });*/</script>
	<div class="message red" id="abitfile_status"></div>
	<h1>Данные об абитуриентах</h1>
	<form <? if (count($MODULE_DATA["output"]["messages"]["good"])) { ?>style="display: none;"<? } ?> action="/office/abit/" method="post" name="abitform" id="abitform" enctype="multipart/form-data">
		<p>Путь к csv-файлу:<br><input type="file" name="abit" size="40"></p>
		<?php if($MODULE_OUTPUT["handle_vpo"]) { ?>
		<input name="type" value="vpo" type="radio" checked="checked" />Файл содержит данные абитурентов только ВПО(дневное)<br />
		<input name="type" value="vpo_ev" type="radio"  />Файл содержит данные абитурентов только ВПО(вечернее)<br />
		<?php } ?>
		<?php if($MODULE_OUTPUT["handle_spo"]) { ?>
		<input name="type" value="spo" type="radio" />Файл содержит данные абитурентов только СПО<br />
		<input name="type" value="spo-ex" type="radio" />Файл содержит данные абитурентов только СПО (заочная форма)<br />
		<?php } ?>
		<?php if($MODULE_OUTPUT["handle_izop"]) { ?>
		<input name="type" value="izop" type="radio" />Файл содержит данные абитурентов только ИЗОП<br />
		<?php } ?>
		<input id="abitfile_submit" type="submit" size="20" value="Готово" />
	</form>
<?php
} elseif (isset($MODULE_OUTPUT["mode"]) && $MODULE_OUTPUT["mode"] != "daystogo") { ?>
	<select onchange="location.href=this.options[this.selectedIndex].value">
		<option value="">Укажите направление</option>
<?php
	foreach($MODULE_OUTPUT["types"] as $type) {
		if(isset($_GET["type"]) && ($type["id"] == $_GET["type"])) {
			$selected = " selected=\"selected\"";
		} else {
			$selected = "";
		}

		$parts = explode(".", $type["code"]);
		if (isset($parts[1]) && $parts[1]=="51") {
			$type["type"] .= " (СПО)";
		} ?>
		<option value="?type=<?=$type["id"]?>"<?=$selected?>><?=$type["code"]?> - <?=$type["type"]?></option>
<?php
	} ?>
	</select>
<?php
	if(isset($_GET["type"])) { ?>
		<p><strong>Данные на <?=$MODULE_OUTPUT['date']?>г.</strong><br/></p>
		<!--p style="text-align:center"><strong>Пофамильный список лиц, рекомендуемых к зачислению</strong></p>-->

		<!--<p>Полный пофамильный перечень лиц, зачисление которых рассматривается приёмной комиссией университета в число студентов 1 курса.</p>-->
		<!-- <p><i>Примечание: бюджетные места абитуриентов, рекомендованных к зачислению и не сдавших оригинал аттестата (диплома) до 04.08.2010 г. распределяются
		нижестоящим по ранжированному списку лицам, сдавшим оригиналы аттестатов (дипломов).</i></p> -->
		<h3 style="text-align: center;"><?=$MODULE_OUTPUT["type"]["code"]?> <?=$MODULE_OUTPUT["type"]["type"]?></h3>
		<p align="center">
			<?=(isset($MODULE_OUTPUT['isEvening']) && $MODULE_OUTPUT['isEvening']) ? '' : '<strong>Общее количество бюджетных мест: '.$MODULE_OUTPUT["type"]["budget"]?>
<?php 	if(0 && $MODULE_OUTPUT["type"]["purpose"] > 0) { ?>	
			(в&nbsp;том числе целевой приём: <?=$MODULE_OUTPUT["type"]["purpose"]?>)
<?php 	} ?>
			</strong>
		</p>
    <?php foreach($MODULE_OUTPUT["people"] as $ent => $people) { ?>
    <?php if(count($MODULE_OUTPUT["people"]) > 1) { ?>
    <h2><?php echo $ent; ?></h2>
    <?php } ?>
		<table style="margin-left: 2em;">
<?php 	$category_prev = "";
		$fio_prev = "";
		$i = 1;
		//die(print_r($MODULE_OUTPUT["people"]));
		foreach($MODULE_OUTPUT["people"][$ent] as $man) {
			if (!($category = $man["descr"])) {
				$category = "по общему конкурсу";
			}
		
			$fio = $man["last_name"]." ".$man["name"]." ".$man["patronymic"];
			if ($fio != $fio_prev) {
				if(!empty($man["notice"])) {
					if($man["position"] == 4) {
						if($man["notice"] == "Сироты и инвалиды") {
							$category = "вне конкурса (дети-сироты, инвалиды)";
							$notice = "";
						} else {
							$notice = " (".($man["notice"] == 'Спецкласс' ? '<a href="http://nsau.edu.ru/entrant/11klass/">'.$man["notice"].'</a>' : $man["notice"]).")";
						}
					} else {
						if($man["acceptance"] == "Зачислен") {
							$notice = "";
						} else {
							$notice = " (".($man["notice"] == 'Спецкласс' ? '<a href="http://nsau.edu.ru/entrant/11klass/">'.$man["notice"].'</a>' : $man["notice"]).")";
						}
					}				
				} else {
					$notice = "";
				}
				if($category_prev != $category) { ?>
			<tr><td colspan="<?php if(!$MODULE_OUTPUT["is_magistracy"] && !$MODULE_OUTPUT["is_spo"]  && $ent != "Собеседование") { ?>6<?php } else { ?>3<?php } ?>"><strong><?=$category?></strong></td></tr>
<?php 			} ?>
			<tr>
				<td><?=$i.".&nbsp;"?>
<?php 			if(/*$man["acceptance"] == "Рекомендован" || $man["acceptance"] == "Зачислен")*/ ($i<=$MODULE_OUTPUT["type"]["budget"] || $man["dogovor"]=="заключен") && $man["qualification"]!="51" && $man["form"]!= "вечерняя") { ?>
					<?php /*?><strong><?php */ ?>
<?php			} ?>
						<a href="/people/<?=$man["id"]?>/" <?=((/*$man["acceptance"] == "Рекомендован"*/0) ? " title='Рекомендован к зачислению'": '')?>>
							<?=$man["last_name"]?> <?=$man["name"]?> <?=$man["patronymic"]?>
						</a>
<?php 			if(/*$man["acceptance"] == "Рекомендован" || $man["acceptance"] == "Зачислен")*/ ($i<=$MODULE_OUTPUT["type"]["budget"] || $man["dogovor"]=="заключен") && $man["qualification"]!="51" && $man["form"]!= "вечерняя") { ?>
					<?php /*?></strong><?php */ ?>
<?php 			} ?>      
				</td> 
        <?php if(!$MODULE_OUTPUT["is_magistracy"] && !$MODULE_OUTPUT["is_spo"] && $ent != "Собеседование") { ?>
        <td><?php if($man["exam_rus"]) { ?><?=$man["exam_rus"]?><?php } else { ?>0<?php } ?></td>
        <td><?php if($man["exam_prof"]) { ?><?=$man["exam_prof"]?><?php } else { ?>0<?php } ?></td>
        <td><?php if($man["exam_prof"]) { ?><?=$man["exam_extra"]?><?php } else { ?>0<?php } ?></td>
        <?php } ?>
				<td title="Общий балл"><strong><?=$man["ege"]?></strong></td>
				<td>
<?php 			if(/*$man["acceptance"] != "Зачислен"*/1) { ?>
					<?=$man["documents"]?><?=$notice?>
<?php 			}
				if(($man["acceptance"] == 1 || $man["acceptance"] == 2 || $man["acceptance"] == 3)/* && $man["position"]==1*/) { ?>
					<span style="font-weight: bold; color: #0c0;">Зачислен</span>
<?php			} elseif ($man["acceptance"] == 4) { ?>
					<span style="font-weight: bold; color: #0c0;">Рекомендован к зачислению</span>
<?php			} ?>
				<?php if ($man["position"] == 1 && $man["client"]) { ?>
					<span>(заказчик - <?=$man["client"]?>)</span>
				<? } ?>
				</td>        
        <?php /* ?><td><?php if($man["qual_name"]) { ?><?=ucfirst($man["qual_name"])?><?php } else { ?>&nbsp;<?php } ?></td><?php */ ?>
			</tr>
<?php 			$i++;
				$category_prev = $category;
				$fio_prev = $fio;
			}
		} ?>
		</table>
    <?php } ?>
    
    <!--По собеседованию
    <table style="margin-left: 2em;">
<?php 	$category_prev = "";
		$fio_prev = "";
		$i = 1;
		//die(print_r($MODULE_OUTPUT["people"]));
		foreach($MODULE_OUTPUT["people"]["Собеседование"] as $man) {
			if (!($category = $man["descr"])) {
				$category = "по общему конкурсу";
			}
		
			$fio = $man["last_name"]." ".$man["name"]." ".$man["patronymic"];
			if ($fio != $fio_prev) {
				if(!empty($man["notice"])) {
					if($man["position"] == 4) {
						if($man["notice"] == "Сироты и инвалиды") {
							$category = "вне конкурса (дети-сироты, инвалиды)";
							$notice = "";
						} else {
							$notice = " (".($man["notice"] == 'Спецкласс' ? '<a href="http://nsau.edu.ru/entrant/11klass/">'.$man["notice"].'</a>' : $man["notice"]).")";
						}
					} else {
						if($man["acceptance"] == "Зачислен") {
							$notice = "";
						} else {
							$notice = " (".($man["notice"] == 'Спецкласс' ? '<a href="http://nsau.edu.ru/entrant/11klass/">'.$man["notice"].'</a>' : $man["notice"]).")";
						}
					}				
				} else {
					$notice = "";
				}
				if($category_prev != $category) { ?>
			<tr><td colspan="<?php if(!$MODULE_OUTPUT["is_magistracy"] && !$MODULE_OUTPUT["is_spo"]) { ?>7<?php } else { ?>4<?php } ?>"><strong><?=$category?></strong></td></tr>
<?php 			} ?>
			<tr>
				<td><?=$i.".&nbsp;"?>
<?php 			if(/*$man["acceptance"] == "Рекомендован" || $man["acceptance"] == "Зачислен")*/ ($i<=$MODULE_OUTPUT["type"]["budget"] || $man["dogovor"]=="заключен") && $man["qualification"]!="51" && $man["form"]!= "вечерняя") { ?>
					<?php /*?><strong><?php */ ?>
<?php			} ?>
						<a href="/people/<?=$man["id"]?>/" <?=((/*$man["acceptance"] == "Рекомендован"*/0) ? " title='Рекомендован к зачислению'": '')?>>
							<?=$man["last_name"]?> <?=$man["name"]?> <?=$man["patronymic"]?>
						</a>
<?php 			if(/*$man["acceptance"] == "Рекомендован" || $man["acceptance"] == "Зачислен")*/ ($i<=$MODULE_OUTPUT["type"]["budget"] || $man["dogovor"]=="заключен") && $man["qualification"]!="51" && $man["form"]!= "вечерняя") { ?>
					<?php /*?></strong><?php */ ?>
<?php 			} ?>      
				</td> 
        <?php if(!$MODULE_OUTPUT["is_magistracy"] && !$MODULE_OUTPUT["is_spo"]) { ?>
        <?php /* ?><td><?php if($man["exam_rus"]) { ?><?=$man["exam_rus"]?><?php } else { ?>0<?php } ?></td>
        <td><?php if($man["exam_prof"]) { ?><?=$man["exam_prof"]?><?php } else { ?>0<?php } ?></td>
        <td><?php if($man["exam_prof"]) { ?><?=$man["exam_extra"]?><?php } else { ?>0<?php } ?></td><?php */ ?>
        <?php } ?>
				<td title="Общий балл"><strong><?=$man["ege"]?></strong></td>
				<td>
<?php 			if(/*$man["acceptance"] != "Зачислен"*/1) { ?>
					<?=$man["documents"]?><?=$notice?>
<?php 			}
				if(($man["acceptance"] == 1 || $man["acceptance"] == 2 || $man["acceptance"] == 3)/* && $man["position"]==1*/) { ?>
					<span style="font-weight: bold; color: #0c0;">Зачислен</span>
<?php			} elseif ($man["acceptance"] == 4) { ?>
					<span style="font-weight: bold; color: #0c0;">Рекомендован к зачислению</span>
<?php			} ?>
				<?php if ($man["position"] == 1 && $man["client"]) { ?>
					<span>(заказчик - <?=$man["client"]?>)</span>
				<? } ?>
				</td>        
        <td><?php if($man["qual_name"]) { ?><?=ucfirst($man["qual_name"])?><?php } else { ?>&nbsp;<?php } ?></td>
			</tr>
<?php 			$i++;
				$category_prev = $category;
				$fio_prev = $fio;
			}
		} ?>
		</table>-->
    
    <?php if(!empty($MODULE_OUTPUT["exams"])) { ?>
    <br />Экзамены: 
    <?php 
    $i = 1; 
    $count = count($MODULE_OUTPUT["exams"]);
    foreach($MODULE_OUTPUT["exams"] as $exam) {
    ?>
    <?php echo $exam["name"]; ?><?php if($i < $count) { ?>, <?php } ?>
    <?php 
    $i++;
    }?>
    <?php } ?>
<?php
	} else { // if
		1;
	} // else
} ?>
