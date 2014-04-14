<?php
// version: 3.21
// date: 2014-04-01
//$MODULE_OUTPUT = $MODULE_DATA["output"];

$MODULE_MESSAGES = array(
    101 => "Студент успешно добавлен.",
    102 => "Уровень обучения специальности успешно добавлен.",
    103 => "Специальность успешно добавлена.",
    104 => "Уровень обучения специальности успешно удален.",
    105 => "Специальность с ее уровнями обучения успешно удалена.",
    106 => "Редактирование прошло успешно.",
    301 => "Студент не добавлен.",
    302 => "Студент не добавлен. Были введены 2 различных пароля.",
    303 => "Студент не добавлен. Необходимо ввести пароль.",
    304 => "Группа с таким номером уже существует.",
    305 => "Невозможно удалить данную группу. Она не пуста.",
    306 => "Ошибка. Были заполнены не все поля.",
    307 => "Уровень обучения специальности не добавлен.",
    308 => "Специальность не добавлена.",
    309 => "Уровень обучения специальности не удален.",
    310 => "Специальность с ее уровнями обучения не удалена.",
    311 => "Ошибка при добавлении уровня обучения специальности.",
    312 => "Ошибка при редактировании специальности.",   
    313 => "Ошибка! Не указано название дисциплины или ее кафедра.",   
    314 => "Ошибка! Не указан номер группы.",    
    315 => "Ошибка! Введите фамилию, имя или отчество.",   
	316 => "Человек не найден.",   
	317 => "Ошибка! Введите фамилию и имя.",
	318 => "Ошибка! Должна быть выбранна хотя бы одна кафедра.",
	319 => "Ошибка! Не указан логин или пароль, или пароли не совпадают.",
	320 => "Ошибка! Такая запись существует.",
	321 => "Расписание успешно добавлено.",
	322 => "Данная дисциплина используется в расписании, либо к ней есть методическое пособие.",
	323 => "Ошибка! Специальность не выбранна.",
	324 => "Ошибка! Должность не выбранна.",
	325 => "Ошибка! Статус не выбран.",
	326 => "Ошибка! Группа пользователей не выбранна.",
	327 => "Ошибка! Данный логин занят.",
	328 => "Ошибка! Укажите год поступления.",
	329 => "Ошибка! Укажите номер читательского билета.",
	330 => "Ошибка! Укажите свой e-mail.",
	331 => "Ошибка! Не верный формат e-mail.",
	332 => "Ошибка! Пользователь с данным e-mail уже существует.",
	333 => "Проверьте почтовый ящик и подтвердите заявку регистрацию.",
	334 => "Ошибка! Данная специальность уже существует",
	335 => "Ошибка! Не указаны группы куратора",
	336 => "Ошибка! Такая запись уже существует!"
);


if ($MODULE_OUTPUT["messages"]["good"])
{
    $array = array();

    foreach ($MODULE_OUTPUT["messages"]["good"] as $elem)
    {
        if (isset($MODULE_MESSAGES[$elem]))
        {
            $array[] = $MODULE_MESSAGES[$elem];
        }
    }

    if ($array)
    {
        foreach ($array as $elem)
        {
            echo "<p class=\"message\">".$elem."</p>\n";
        }
    }
}

if ($MODULE_OUTPUT["messages"]["bad"] && isset($MODULE_OUTPUT["display_variant"]) && !in_array($MODULE_OUTPUT["display_variant"], array("add_item", "edit_item")))
{
    $array = array();

    foreach ($MODULE_OUTPUT["messages"]["bad"] as $elem)
    {
        if (isset($MODULE_MESSAGES[$elem]))
        {
            $array[] = $MODULE_MESSAGES[$elem];
        }
    }

    if ($array)
    {
        foreach ($array as $elem)
        {
            echo "<p class=\"message red\">".$elem."</p>\n";
        }
    }
}




switch ($EE["language"])
{
    case "en":
        $LANGUAGE["news"] = array(
            "read_all_news" => "Read all news",
            "all_news" => "All news",
            "latest_news" => "News",
            "date_format" => "d/m/Y",
            "datetime_format" => "d/m/Y g:i:s A",
            "read_more" => "Read more",
            "more" => "more",
            "prev" => "prev",
            "previous_page" => "Previous page",
            "next" => "next",
            "next_page" => "Next page",
            "pages" => "Pages",
            "add_comment" => "Add comment",
            "comments" => "Comments",
            "no_comments" => "There are no comments for this item currently. Your comment could be the first one!",
            "name" => "Name",
            "location" => "Location",
            "email" => "E-mail",
            "your_comment" => "Your comment",
            "confirmation_code" => "Confirmation code",
            "confirmation_code_hint" => "Please write in a code displayed on the picture.",
            "obligatory_field" => "Obligatory field",
            "all_publications" => "all publications",
            "read_all_publications" => "Read all publications",
            );
        break;

    default:
        $LANGUAGE["news"] = array(
            "read_all_news" => "Читать все новости",
            "all_news" => "Все материалы",
            "latest_news" => "Последние материалы",
            "date_format" => "d.m.Y",
            "datetime_format" => "d.m.Y, H:i:s",
            "read_more" => "Читать подробнее",
            "more" => "подробнее",
            "prev" => "предыдущая",
            "previous_page" => "Предыдущая страница",
            "next" => "следующая",
            "next_page" => "Следующая страница",
            "pages" => "Страницы",
            "add_comment" => "Добавить комментарий",
            "comments" => "Комментарии",
            "no_comments" => "На данный момент к&nbsp;этому материалу нет ни&nbsp;одного комментария. Вы можете оставить первый!",
            "name" => "Имя",
            "location" => "Город",
            "email" => "E-mail",
            "your_comment" => "Ваш комментарий",
            "confirmation_code" => "Код подтверждения",
            "confirmation_code_hint" => "Пожалуйста, введите цифровой код, изображённый на картинке.",
            "obligatory_field" => "Поле, обязательное для заполнения",
            "all_publications" => "Все новости",
            "read_all_publications" => "Читать все новости",
            );
        break;
}

switch($MODULE_OUTPUT["mode"])
{  
    case "faculties": { ?>
        <h1><a href="/about/department/">Подразделения НГАУ</a></h1>
        <ul>
<?php   foreach($MODULE_OUTPUT["faculties"] as $faculty) { ?>
            <li class="faculty">
<?php		if($faculty["link"]) { ?>
				<a href="<?=( ($faculty["link"][0] == '/' || substr($faculty["link"],0,7) == 'http://') ? '' : '/' ). $faculty["link"]?>" title="<?=$faculty["comment"]?>">
<?php		} ?>
					<?=$faculty["name"]?>
<?php		if($faculty["link"]) { ?>
				</a>
<?php		} ?>
            </li>
<?php 	} ?>
        </ul>
<?php 	if (!isset($MODULE_OUTPUT["hide_deps_href"])) {?>
		<a href="/about/department/">Все подразделения</a>
<?php 	}
	}
	break;
    
    case "speciality": {
		{
		/*echo "<pre>";
		print_r($MODULE_OUTPUT);
		echo "</pre>";*/
        /*if(isset($_GET["action"], $MODULE_OUTPUT['spec_info']) && $_GET["action"]=="add_subsection") { ?> 
            <h3>Добавить уровень обучения в специальность <?=$MODULE_OUTPUT['spec_info']["code"]?> - <?=$MODULE_OUTPUT['spec_info']["name"]?> :</h3> 
            <form method="post" action="/speciality/">
                <input type="hidden" name="action_sub" value="add_subsection" /> 
                <input type="hidden" name="code" value="<?=$MODULE_OUTPUT['spec_info']["code"]?>" />
                <input type="hidden" name="spec_id" value="<?=$MODULE_OUTPUT['spec_info']["id"]?>" />
                <br/>
                <table border="1">
					<tr>
						<th bgcolor="#BBBBBB">Год открытия</th>
						<th bgcolor="#BBBBBB">Уровень обучения</th>
						<th bgcolor="#BBBBBB">Срок обучения</th>
						<th bgcolor="#BBBBBB">Очная форма обучения</th>
						<th bgcolor="#BBBBBB">Заочная форма обучения</th>
						<th bgcolor="#BBBBBB">Ведётся набор</th>
					</tr>
                    <tr>
                        <td><input type="text" name="year_open_sub" /></td>
                        <td>
							<select name="type_sub" size="1">
								<option selected="selected">Тип уровня обучения</option>
								<option value="secondary">Среднее професиональное образование</option>
								<option value="higher">Высшее професиональное образование</option> 
								<option value="bachelor">Бакалавриат</option> 
								<option value="magistracy">Магистратура</option> 
							</select>
                        </td>
                        <td><input type="text" name="data_training" /></td>
                        <td align="center"><input type="checkbox" name="internal_tuition_sub" checked='checked' /></td>
                        <td align="center"><input type="checkbox" name="correspondence_tuition_sub" /></td>
						<td align="center"><input type="checkbox" name="has_vacancies" checked='checked' /></td>
                    </tr>
                </table>
                <br/>
                <input type="submit" value="Добавить" class="button" />
            </form>
<?php  	}*/
        /*if(isset($_GET["node"]) && isset($_GET["action"]) && $_GET["action"]=="add") { ?>
            <form method="post" action="/speciality/">
				<!--fieldset>
					<legend>Добавить направление:</legend>
				</fieldset-->
                <input type="hidden" name="action_op" value="add" />Код специальности:<br />
                <input type="text" name="code_add" /><br />Название специальности:<br />
                <input type="text" name="speciality_name" /><br/>Выберите направление:<br />
				<select name="direction" size="1">
					<option value="0" selected="selected">Направление специальности</option>
<?php		foreach($MODULE_OUTPUT["directory"] as $dir) { ?>
					<option value="<?=$dir["code"]?>"><?=$dir["name"]?></option>
<?php 		} ?>
                </select><br />
                Год открытия:<br />
                <input type="text" name="year_open" /><br/>Тип специальности:<br />
                <select name="type" size="1">
                    <option selected="selected">Тип специальности</option>
                    <option value="secondary">Среднее професиональное образование</option>
                    <option value="higher">Высшее професиональное образование</option> 
                    <option value="bachelor">Бакалавриат</option> 
                    <option value="magistracy">Магистратура</option> 
                </select>
                <br/>
                Заочная форма обучения:
                <input type="checkbox" value="1" name="internal_tuition" checked /><br/>Очная форма обучения:&nbsp;&nbsp;&nbsp; 
                <input type="checkbox" value="1" name="correspondence_tuition"/><br/>Ведётся набор:&nbsp;&nbsp;&nbsp; 
                <input type="checkbox" value="1" checked name="has_vacancies"/><br/>Описание:
                <textarea name="description"  style="width=300px; height=100px" ></textarea><br/>
                <input type="submit" value="Добавить" class="button" />
            </form>
<?php  	} */
		}
        if (!empty($MODULE_OUTPUT["speciality"])) {
        	foreach($MODULE_OUTPUT["faculty"] as $faculty) {
        		$faculty_ids[] = $faculty['id'];
        	}
            foreach($MODULE_OUTPUT["speciality"] as $special) {
                if(isset($_GET["node"]) && isset($_GET["action"])) {
                    {
					/*if($_GET["action"]=="edit") { ?>
            <h2><?=$special["code"]?>&nbsp;-&nbsp;<?=$special["name"]?></h2>   
            <form action="/speciality/<?=$special["code"]?>" method="post">
                Код специальности:<br />
                <input type="text" name="code" value="<?=$special["code"]?>" /><br />Название специальности:<br />
                <input type="text" name="speciality_name" style="width:484px" value="<?=$special["name"]?>" /><br/>Выберите факультет:<br />
                <select name="faculty" size="1">
                    <option selected="<?=(in_array($special["id_faculty"], $faculty_ids) ? '':'selected')?>">--------------------------------------------</option>
<?php  					$faculty_checked = false;
						foreach($MODULE_OUTPUT["faculty"] as $faculty) {
							if ($faculty["id"]==$special["id_faculty"]) { ?>
					<option value="<?=$faculty["id"]?>" selected="selected"><?=$faculty["name"]?></option> 
<?php 							$faculty_checked = true; 
							} else { ?>
                    <option value="<?=$faculty["id"]?>"><?=$faculty["name"]?></option>
<?php 						}
						} ?>
                </select><br />
                Выберите направление:<br />
                <select name="direction" size="1">
                    <!--<option value="0" selected="selected"><?=$special["direction"]?></option>-->
                    <option disabled>--------------------------------------------</option>
<?php 					foreach($MODULE_OUTPUT["direction"] as $dir) {
							if ($dir["code"]==$special["direction"]) { ?>
					<option value="<?=$dir["code"]?>" selected="selected"><?=$dir["name"]?></option> 
<?php 						} else { ?>
                    <option value="<?=$dir["code"]?>"><?=$dir["name"]?></option>
<?php 						}
						}
						$i=0; ?>
                </select><br /><br />
                <table border="1">
					<tr>
						<th bgcolor="#BBBBBB">№ п.п</th>
						<th bgcolor="#BBBBBB">Год открытия</th>
						<th bgcolor="#BBBBBB">Тип специальности</th>
						<th bgcolor="#BBBBBB">Очная<br />форма<br />обучения</th>
						<th bgcolor="#BBBBBB">Заочная<br />форма<br />обучения</th>
						<th bgcolor="#BBBBBB">Ведется<br />набор</th>
						<th bgcolor="#BBBBBB">Продолжительность</th>
						<th bgcolor="#BBBBBB">Уровень обучения</th>
					</tr>
<?php 					foreach($MODULE_OUTPUT["speciality_edit"] as $edit) {
							$i++; ?>
                    <tr>
                        <td align="center"><?=$i?></td>
                        <td>
							<input type="hidden" name="<?=$edit["type"]?>[st_type]" value="<?=$edit["type"]?>" />
							<input type="hidden" name="<?=$edit["type"]?>[st_year]" value="<?=$edit["year_open"]?>" /> 
							<input type="text" name="<?=$edit["type"]?>[year_open]" value="<?=$edit["year_open"]?>" />
                        </td>
                        <td>
							<select name="<?=$edit["type"]?>[type]" size="1">
<?php						switch ($edit["type"]) {
                                case secondary: ?>
								<option value="secondary" selected="selected">Среднее професcиональное образование</option>
                                <option disabled>---------------------------------------</option>
<?php 							break;
                                case higher: ?>
								<option value="higher" selected="selected">Высшее професcиональное образование</option>
                                <option disabled>---------------------------------------</option>
<?php 							break;
                                case bachelor: ?>
								<option value="bachelor" selected="selected">Бакалавриат</option>
                                <option disabled>---------------------------------------</option>
<?php 							break;
                                case magistracy: ?>
								<option value="magistracy" selected="selected">Магистратура</option>
                                <option disabled>---------------------------------------</option>
<?php 							break;
                            } ?>                            
								<option value="secondary">Среднее профеcсиональное образование</option>
								<option value="higher">Высшее професcиональное образование</option> 
								<option value="bachelor">Бакалавриат</option> 
								<option value="magistracy">Магистратура</option> 
							</select>
                        </td>
                        <td align="center"><input type="checkbox" name="<?=$edit["type"]?>[internal_tuition]" <?php if ($edit["internal_tuition"]==1){?>checked <?php }?> /></td>
                        <td align="center"><input type="checkbox" name="<?=$edit["type"]?>[correspondence_tuition]" <?php if ($edit["correspondence_tuition"]==1){?>checked <?php }?> /></td>
                        <td align="center"><input type="checkbox"  name="<?=$edit["type"]?>[has_vacancies]" <?php if ($edit["has_vacancies"]){?>checked <?php }?> /></td>
                        <td align="center"><input type="text" name="<?=$edit["type"]?>[data_training]" value="<?=$edit["data_training"]?>" /></td>
						<td align="center"><input type="text" name="<?=$edit["type"]?>[qualification]" value="<?=$edit["qualification"]?>" /></td>                       
                    </tr>
<?php 					} ?>
                </table><br />
				<b>Общее описание специальности:</b><br />
                <textarea name="description" cols="60" rows="15" class="wysiwyg"><?=$special["description"]?></textarea><br />
<?php 					foreach($MODULE_OUTPUT["speciality_edit"] as $edit) {
							if ($edit["type"] == "bachelor")
								$dir = "бакалавриат";
							elseif ($edit["type"] == "magistracy")
								$dir = "магистратура";
							elseif ($edit["type"] == "higher")
								$dir = "ВПО";
							elseif ($edit["type"] == "secondary")
								$dir = "СПО"; ?>
				<br />
				<fieldset>
					<legend><b>Направление <?=$dir?></b></legend>						
					Список экзаменов:<br />
<?php						if ($edit["type"] == "magistracy") echo "Собеседование по профилю выбранного направления подготовки";
							else
								foreach ($MODULE_OUTPUT["exams"] as $exam) { ?>
					<input type="checkbox" name="<?=$edit["type"]?>_exams[exam_<?=$exam["id"]?>]" <? if ($exam["need"]) { ?>checked<? } ?> /><?=$exam["name"]?><br />
<?php 							} ?>
					<textarea name="<?=$edit["type"]?>[description]" cols="60" rows="15" class="wysiwyg"><?=$edit["description"]?></textarea>
				</fieldset><br />
<?php 					} ?>
				<p>
					<input type="submit" value="Сохранить изменения" />
					<input type="reset" value="Сброс" />
					<input type="submit" name="<?=$_GET["node"]?>[save][cancel]" value="Отмена" />
				</p>
            </form>
<?php 				}*/
					}
				} else {
					if (empty($special["description"]) && empty($MODULE_OUTPUT["speciality_edit"])) { ?>
		<p>Информация о данной специальности отсутствует</p><br />
<?php         		} else { ?>
        <h2><?=$special["code"]?>&nbsp;-&nbsp;<?=$special["name"]?></h2>
<?php  				//if(!empty($MODULE_OUTPUT["type_klass"])) {
						/*echo "Квалификация: ".$MODULE_OUTPUT["type_klass"]." <br />";
						echo "Срок обучения: ".$MODULE_OUTPUT["data_training"]." <br />";*/
						/*if($MODULE_OUTPUT["type_klass"]=="mag")
							{?>Квалификация: Магистр <br> Срок обучения: 5 лет <br> <?php }
						elseif($MODULE_OUTPUT["type_klass"]=="bach")
							{?>Квалификация: Бакалавр <br> Срок обучения: 4 года <br> <?php }
						elseif($MODULE_OUTPUT["type_klass"]=="spo")
							{?>Квалификация: Специалист <br> Срок обучения: 2 года 10 месяцев <br> <?php }
						elseif($MODULE_OUTPUT["type_klass"]=="vpo")
							{?>Квалификация: Специалист <br> Срок обучения: 5 лет <br> <?php }*/ 
					//} else {
                      
						if (!empty($MODULE_OUTPUT["speciality_edit"])) { 
							/*foreach($MODULE_OUTPUT["speciality_edit"] as $key => $type_sp) {
								if ($type_sp["type"] == "bachelor") {
									$edits[] = $type_sp;
									unset($MODULE_OUTPUT["speciality_edit"][$key]);
								}
							}
							foreach($MODULE_OUTPUT["speciality_edit"] as $type_sp) {
								$edits[] = $type_sp;
							}*/
							foreach($MODULE_OUTPUT["speciality_edit"][$special['id']] as $type_sp) {
								$dir["secondary"] = "СПО";
								$dir["higher"] = "ВПО";
								$dir["magistracy"] = "Магистратура";
								$dir["bachelor"] = "Бакалавриат";
								/*if ($type_sp["has_vacancies"]) */{ ?>
		<fieldset>
			<legend><?=$dir[$type_sp["type"]]?><? if ($type_sp["qualification"] || $type_sp["data_training"]) { ?> - <? } ?><? if ($type_sp["qualification"]) { ?>уровень обучения <?=$type_sp["qualification"]?>, <? } ?><? if ($type_sp["data_training"]) { ?>срок обучения <?=$type_sp["data_training"]?><? } ?></legend>
<?php 								if (!empty($type_sp["description"])) { ?>
			<div><b>Дополнительная информация</b>:</div>
			<?=$type_sp["description"]?>
<?php								}
									if(isset($type_sp["exams"]) && count($type_sp["exams"])) { ?>
			<p><strong> Вступительные испытания</strong>:
<?php									if ($type_sp["type"] != "magistracy") {
											foreach($type_sp["exams"] as $key => $exam) { ?><?=(!$key ? $exam["name"] : ", ".$exam["name"])?><?php										}
										} else { ?>
				Собеседование по профилю выбранного направления подготовки
<?php									} ?>
			</p>
<?php								} ?>
		</fieldset>
<?php                       	/* if ($type_sp["type"]=="secondary" | $type_sp["type"]=="higher") { ?>
									Специалист (срок обучения 5 лет) <br><?php
								} elseif ($type_sp["type"]=="bachelor") { ?>
									Бакалавр (срок обучения 4 года) <br><?php 
								} elseif ($type_sp["type"]=="magistracy") { ?>
									Магистр (срок обучения 2 года) <br><?php
								}*/		
								}
							}
						}
                //}
						if ($special["description"]) { ?>
		<fieldset>
			<legend>О направлении:</legend>
			<?=$special["description"]?>
		</fieldset>
<?php 					}
					} ?>
		<br /><br />
<?php			}
                if ($MODULE_OUTPUT["privileges"]["specialities.handle"]==true /*&& 0*/) { ?>
		<a href="/office/speciality_directory/edit_speciality/<?=$special["id_edit"]?>/">[ Редактировать описание ]</a>
<?php  			}
            } ?>
		<br />
		<script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8"></script>
		<script>jQuery(document).ready(function($) {SpecialityChangeTitle();});</script>
		<div onmouseover="change_title(this, '<?=$special["name"]?>', false);" class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="none" data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,lj"></div> 
<?php   } else {          
			if(isset($_GET["node"]) && isset($_GET["action"]) && $_GET["action"]=="add") {
				1;
			} else { ?> 
        <form action="/speciality/" method="get">
			<p>
				Специальность:
				<input name="name_search" type="text" size="35" value="<?php echo isset($_GET["name"]) ? $_GET["name"] : "" ?>"/>
				<input type="submit" value="Найти" class="button" />
			</p>        
        </form>
<?php 			if(!empty($MODULE_OUTPUT["specialities"])) { ?>
        <ul>
<?php 				foreach($MODULE_OUTPUT["specialities"] as $special) { ?>
			<li><a href="/speciality/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["code"]?>&nbsp;-&nbsp;<?=$special["name"]?></a></li>
<?php 				} ?>
        </ul>
<?php 			} else {
					if(isset($_GET["name"])) { ?>
        <p>Не найдено специальностей с такими параметрами поиска.</p>
<?php 				} else { ?>
        <p>Введите в поле поиска данные о специальности.</p>
<?php            	}
				} 
            } 
        }
	}
    break;
    
    case "library_standard": {
		/*echo "<pre>";
		print_r($MODULE_OUTPUT["specialities"]);
		echo "</pre>";*/
        /*if($MODULE_OUTPUT["type"]=="higher") { ?>
		<h2 style="text-align: center;">Перечень специальностей высшего профессионального образования</h2>
<?php 	} elseif($MODULE_OUTPUT["type"]=="secondary") { ?>
		<h2 style="text-align: center;">Перечень специальностей среднего профессионального образования</h2>
<?php 	} elseif($MODULE_OUTPUT["type"]=="bachelor") { ?>
		<h2 style="text-align: center;">Направления подготовки бакалавров</h2>
<?php 	} elseif($MODULE_OUTPUT["type"]=="magistracy") { ?>
		<h2 style="text-align: center;">Направления подготовки магистров</h2>
<?php 	}*/
        if (isset($MODULE_OUTPUT["type_tuition"]) && $MODULE_OUTPUT["type_tuition"]=="correspondence_true") { ?>
        <p align="center">Показаны только специальности заочной формы обучения. <a href="?sort=all" >Показать все</a>.</p>
<?php   } ?>
        <table class="nsau_table" align="center">
			<thead>
				<tr>
					<td><a href="?sort=code" title="Сортировка по коду специальности"><strong>Код</strong></a></td>
					<th><a href="?sort=name" title="Сортировка по наименованию специальности"><strong>Наименование специальности</strong></a></th>
					<?php /* ?><th><a href="?sort=year" title="Сортировка по году открытия специальности">Год открытия</a></th>
          <th>Квалификация</th>
					<th>Очная</th>
					<th><a href="?sort=correspondence_tuition" title="Выбрать специальности заочной формы обучения">Заочная</a></th><?php */ ?>            
				</tr>				
			</thead>
			<tbody>
<?php 	/*$j=0;
        $dir=$MODULE_OUTPUT["specialities"];
        if ($MODULE_OUTPUT["specialities"][0]["has_vacancies"]) { ?>
			<tr style="text-align: center; font-weight:bolder; background-color: #f09695;">
				<td style="font-size:12pt; padding:5px;" colspan="6"><?=$dir[$j]["direction"]?>&nbsp;-&nbsp;<?=$dir[$j]["name_dir"]?> </td>
			</tr>
<?php 	}*/
		foreach($MODULE_OUTPUT["specialities"]  as $direction_id => $direction) {
			if($direction["has_vacancies"] == 1 && count($direction['spec'])) { ?>
				<tr class="tr_highlight">
					<th colspan="6"><?=$direction_id?>&nbsp;-&nbsp;<?=$direction["direction"]?></th>
				</tr>
<?php			foreach($direction['spec'] as $special) {
					if($special["has_vacancies"] == 1) { ?>
				<tr>
				    <td><a href="/file/<?=$special['file_id'];?>"><strong><?=$special["code"]?></strong></a><?php /* ?>
<?php 					if($MODULE_OUTPUT["type"]=="higher") { ?>
						<a href="/speciality/higher/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["code"]?></a>
<?php 					} elseif($MODULE_OUTPUT["type"]=="secondary") { ?>
						<a href="/speciality/secondary/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["code"]?></a>
<?php 					} elseif($MODULE_OUTPUT["type"]=="bachelor") { ?>
						<a href="/speciality/bachelor/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["code"]?></a>
<?php 					} elseif($MODULE_OUTPUT["type"]=="magistracy") { ?>
						<a href="/speciality/magistracy/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["code"]?></a>
<?php 					} ?><?php */ ?>
				    </td>
				    <td><a href="/file/<?=$special['file_id'];?>"><strong><?=$special["name"]?></strong></a><?php /* ?>
<?php 					if($MODULE_OUTPUT["type"]=="higher") { ?>
						<a href="/speciality/higher/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["name"]?></a>
<?php 					} elseif($MODULE_OUTPUT["type"]=="secondary") { ?>
						<a href="/speciality/secondary/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["name"]?></a>
<?php 					} elseif($MODULE_OUTPUT["type"]=="bachelor") { ?>
						<a href="/speciality/bachelor/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["name"]?></a>
<?php 					} elseif($MODULE_OUTPUT["type"]=="magistracy") { ?>
						<a href="/speciality/magistracy/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["name"]?></a>
<?php 					}?><?php */ ?>
						<br/>
<?php 					if ($MODULE_OUTPUT["privileges"]["specialities.handle"]==true /*&& 0*/) { ?>
						<a href="/office/speciality_directory/edit_speciality/<?=$special["id"]?>/" target="_blank"><font size="1pt">Редактировать</font></a>
<?php  					} ?>
				    </td>
				    <?php /* ?><td ><?=$special["year_open"]?></td>
            <td><?php if($special["old"]) { 
            switch ($special["type"]) {
					        case "secondary":
					            $type_sp="Среднее професcиональное образование";
					        break;
					        case "higher":
					            $type_sp="Высшее професcиональное образование";
					        break;
					        case "magistracy":
					            $type_sp="Магистратура";
					        break;
					        case "bachelor":
					            $type_sp="Бакалавриат";
					        break;
					    } 
            }
            else $type_sp = $special["qualification"];?>
              <?=$type_sp;?>
              </td>
<?php 					if($special["internal_tuition"]==1) { ?>
					<td class="td_highlight">+</td>
<?php 					} else{ ?>
					<td class="td_highlight">-</td>
<?php 					}
						if($special["correspondence_tuition"]==1) { ?>
					<td>+</td>
<?php 					} else{ ?>
					<td>-</td>
<?php 					} ?> <?php */ ?>                                              
				</tr>
<?php				}
				}
			} 
		}
       ?>				
			</tbody>
		</table>
<?php
	}
	break;
           
    case "specialities": {
		/*echo "<pre>";
		print_r($MODULE_OUTPUT["specialities"]);
		echo "</pre>";*/
        if($MODULE_OUTPUT["type"]=="higher") { ?>
		<h2 style="text-align: center;">Перечень специальностей высшего профессионального образования</h2>
<?php 	} elseif($MODULE_OUTPUT["type"]=="secondary") { ?>
		<h2 style="text-align: center;">Перечень специальностей среднего профессионального образования</h2>
<?php 	} elseif($MODULE_OUTPUT["type"]=="bachelor") { ?>
		<h2 style="text-align: center;">Направления подготовки бакалавров</h2>
<?php 	} elseif($MODULE_OUTPUT["type"]=="magistracy") { ?>
		<h2 style="text-align: center;">Направления подготовки магистров</h2>
<?php 	}
        if (isset($MODULE_OUTPUT["type_tuition"]) && $MODULE_OUTPUT["type_tuition"]=="correspondence_true") { ?>
        <p align="center">Показаны только специальности заочной формы обучения. <a href="?sort=all" >Показать все</a>.</p>
<?php   } ?>
        <table class="nsau_table" align="center">
			<thead>
				<tr>
					<th><a href="?sort=code" title="Сортировка по коду специальности">Код</a></th>
					<th><a href="?sort=name" title="Сортировка по наименованию специальности">Наименование специальности</a></th>
					<th><a href="?sort=year" title="Сортировка по году открытия специальности">Год открытия</a></th>
          <th>Квалификация</th>
					<th>Очная</th>
					<th><a href="?sort=correspondence_tuition" title="Выбрать специальности заочной формы обучения">Заочная</a></th>            
				</tr>				
			</thead>
			<tbody>
<?php 	/*$j=0;
        $dir=$MODULE_OUTPUT["specialities"];
        if ($MODULE_OUTPUT["specialities"][0]["has_vacancies"]) { ?>
			<tr style="text-align: center; font-weight:bolder; background-color: #f09695;">
				<td style="font-size:12pt; padding:5px;" colspan="6"><?=$dir[$j]["direction"]?>&nbsp;-&nbsp;<?=$dir[$j]["name_dir"]?> </td>
			</tr>
<?php 	}*/
		foreach($MODULE_OUTPUT["specialities"]  as $direction_id => $direction) {
			if($direction["has_vacancies"] == 1 && count($direction['spec'])) { ?>
				<tr class="tr_highlight">
					<th colspan="6"><?=$direction_id?>&nbsp;-&nbsp;<?=$direction["direction"]?></th>
				</tr>
<?php			foreach($direction['spec'] as $special) {
					if($special["has_vacancies"] == 1) { ?>
				<tr>
				    <th>
<?php 					if($MODULE_OUTPUT["type"]=="higher") { ?>
						<a href="/speciality/higher/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["code"]?></a>
<?php 					} elseif($MODULE_OUTPUT["type"]=="secondary") { ?>
						<a href="/speciality/secondary/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["code"]?></a>
<?php 					} elseif($MODULE_OUTPUT["type"]=="bachelor") { ?>
						<a href="/speciality/bachelor/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["code"]?></a>
<?php 					} elseif($MODULE_OUTPUT["type"]=="magistracy") { ?>
						<a href="/speciality/magistracy/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["code"]?></a>
<?php 					} ?>
				    </th>
				    <td>
<?php 					if($MODULE_OUTPUT["type"]=="higher") { ?>
						<a href="/speciality/higher/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["name"]?></a>
<?php 					} elseif($MODULE_OUTPUT["type"]=="secondary") { ?>
						<a href="/speciality/secondary/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["name"]?></a>
<?php 					} elseif($MODULE_OUTPUT["type"]=="bachelor") { ?>
						<a href="/speciality/bachelor/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["name"]?></a>
<?php 					} elseif($MODULE_OUTPUT["type"]=="magistracy") { ?>
						<a href="/speciality/magistracy/<?=$special["code"]?>/<?=$special["id"]?>/"><?=$special["name"]?></a>
<?php 					}?>
						<br/>
<?php 					if ($MODULE_OUTPUT["privileges"]["specialities.handle"]==true /*&& 0*/) { ?>
						<a href="/office/speciality_directory/edit_speciality/<?=$special["id"]?>/" target="_blank"><font size="1pt">Редактировать</font></a>
<?php  					} ?>
				    </td>
				    <td ><?=$special["year_open"]?></td>
            <td><?php if($special["old"]) { 
            switch ($special["type"]) {
					        case "secondary":
					            $type_sp="Среднее професcиональное образование";
					        break;
					        case "higher":
					            $type_sp="Высшее професcиональное образование";
					        break;
					        case "magistracy":
					            $type_sp="Магистратура";
					        break;
					        case "bachelor":
					            $type_sp="Бакалавриат";
					        break;
					    } 
            }
            else $type_sp = $special["qualification"];?>
              <?=$type_sp;?>
              </td>
<?php 					if($special["internal_tuition"]==1) { ?>
					<td class="td_highlight">+</td>
<?php 					} else{ ?>
					<td class="td_highlight">-</td>
<?php 					}
						if($special["correspondence_tuition"]==1) { ?>
					<td>+</td>
<?php 					} else{ ?>
					<td>-</td>
<?php 					} ?>                                               
				</tr>
<?php				}
				}
			} 
		}
        /*foreach($MODULE_OUTPUT["specialities"] as $special) {
            if($special["has_vacancies"]==1) { ?>
			<tr>
                <td style="padding:4px;" bordercolor="#000000">
<?php 			if($MODULE_OUTPUT["type"]=="higher") { ?>
					<a href="/speciality/higher/<?=$special["code"]?>/"><?=$special["code"]?></a>
<?php 			} elseif($MODULE_OUTPUT["type"]=="secondary") { ?>
					<a href="/speciality/secondary/<?=$special["code"]?>/"><?=$special["code"]?></a>
<?php 			} elseif($MODULE_OUTPUT["type"]=="bachelor") { ?>
					<a href="/speciality/bachelor/<?=$special["code"]?>/"><?=$special["code"]?></a>
<?php 			} elseif($MODULE_OUTPUT["type"]=="magistracy") { ?>
					<a href="/speciality/magistracy/<?=$special["code"]?>/"><?=$special["code"]?></a>
<?php 			} ?>
                </td>
                <td align="left">
<?php 			if($MODULE_OUTPUT["type"]=="higher") { ?>
					<a href="/speciality/higher/<?=$special["code"]?>/"><?=$special["name"]?></a>
<?php 			} elseif($MODULE_OUTPUT["type"]=="secondary") { ?>
					<a href="/speciality/secondary/<?=$special["code"]?>/"><?=$special["name"]?></a>
<?php 			} elseif($MODULE_OUTPUT["type"]=="bachelor") { ?>
					<a href="/speciality/bachelor/<?=$special["code"]?>/"><?=$special["name"]?></a>
<?php 			} elseif($MODULE_OUTPUT["type"]=="magistracy") { ?>
					<a href="/speciality/magistracy/<?=$special["code"]?>/"><?=$special["name"]?></a>
<?php 			}?>
					<br/>
<?php 			if ($MODULE_OUTPUT["privileges"]["specialities.handle"]==true) { ?>
					<a href="/speciality/<?=$special["code"]?>/?node=<?=$NODE_ID?>&amp;action=edit#form"><font size="1pt">Редактировать</font></a>
<?php  			} ?>
                </td>
                <td ><?=$special["year_open"]?></td>
<?php 			if($special["internal_tuition"]==1) { ?>
				<td bgcolor="#FF9F71">+</td>
<?php 			} else{ ?>
				<td bgcolor="#FF9F71">-</td>
<?php 			}
                if($special["correspondence_tuition"]==1) { ?>
				<td>+</td>
<?php 			} else{ ?>
				<td>-</td>
<?php 			} ?>                                               
            </tr>
<?php 		}
            $j+=1;
            if (!empty($dir[$j]["direction"]) && ($dir[$j]["direction"] != $dir[$j-1]["direction"])) { ?>
			<tr style="text-align: center; font-weight:bolder; color:#000000; background-color: #f09695">
				<td style="font-size:12pt; padding:5px;" colspan="6"><?=$dir[$j]["direction"]?>&nbsp;-&nbsp;<?=$dir[$j]["name_dir"]?> </td>
			</tr>
<?php 		}
		}*/ ?>				
			</tbody>
		</table>
<?php
	}
	break;
        
    case "speciality_directory": { ?>
		<div class="speciality_directory">
<?php	if(isset($MODULE_OUTPUT['sub_mode']) && $MODULE_OUTPUT['sub_mode'] == 'add_speciality') {
			$form_back = $MODULE_OUTPUT['form_back'];
			if(isset($MODULE_OUTPUT["messages"]["bad"])) {
				foreach($MODULE_OUTPUT["messages"]["bad"] as $mess) { ?>
			<p class="message red"><?=$mess?></p>
<?php			}
			} ?>
			<form method="post" action="<?=$EE["unqueried_uri"]?>">
				<fieldset>
					<legend>Добавить специальность:</legend>
					<div class="wide">
						<input type="hidden" name="action_op" value="add" />
						<div class="form-notes">
							<dl>
								<dt><label>Название специальности:</label></dt>
								<dd><input type="text" name="speciality_name" value="<?=$form_back['speciality_name']?>" /></dd>
							</dl>
						</div>
						<div class="form-notes">
							<dl>
								<dt><label>Код специальности:</label></dt>
								<dd><input type="text" name="code_add" value="<?=$form_back['code_add']?>" /></dd>
							</dl>
						</div>
						<div class="clear"></div>
					</div>
					<div class="wide">
						<div class="form-notes">
							<dl>
								<dt><label>Выберите направление:</label></dt>
								<dd>
									<select name="direction" size="1">
										<option value="0"<?=($form_back['direction'] == 0 ? " selected='selected'" : "")?>>Направление специальности</option>
<?php		foreach($MODULE_OUTPUT["directory"] as $dir) { ?>
										<option value="<?=$dir["code"]?>"<?=($form_back['direction'] == $dir["code"] ? " selected='selected'" : "")?>><?=$dir["code"]?> - <?=$dir["name"]?></option>
<?php 		} ?>
									</select>
								</dd>
							</dl>
						</div>
						<div class="clear"></div>
					</div>
					<div class="wide">
						<div class="form-notes">
							<dl>
								<dt><label>Год открытия:</label></dt>
								<dd><input type="text" name="year_open" value="<?=$form_back['year_open']?>" /></dd>
							</dl>
						</div>
						<div class="form-notes">
							<dl>
								<dt><label>Уровень подготовки специальности:</label></dt>
								<dd>
									<?php /* ?><select name="type" size="1">
										<option value="0"<?=($form_back['type'] == 0 ? " selected='selected'" : "")?>>Тип специальности</option>
										<option value="secondary"<?=($form_back['type'] == "secondary" ? " selected='selected'" : "")?>>51 - Среднее професиональное образование</option>
										<option value="higher"<?=($form_back['type'] == "higher" ? " selected='selected'" : "")?>>65 - Высшее професиональное образование</option> 
										<option value="bachelor"<?=($form_back['type'] == "bachelor" ? " selected='selected'" : "")?>>62 - Бакалавриат</option> 
										<option value="magistracy"<?=($form_back['type'] == "magistracy" ? " selected='selected'" : "")?>>68 - Магистратура</option> 
									</select><?php */ ?><input type="hidden" name="type" value="" />
									<select name="qualification" size="1" onchange="console.log(this.options[this.selectedIndex].getAttribute('data-old_type'));this.form.elements['type'].value = this.options[this.selectedIndex].getAttribute('data-old_type');">
										<option value="0"<?=($form_back['qualification'] == 0 ? " selected='selected'" : "")?> data-old_type="">Квалификация</option>
										<?php foreach($MODULE_OUTPUT["qualifications"] as $qual_id => $qualif) { ?>
										<option value="<?=$qual_id?>"<?=($form_back['qualification'] == $qual_id ? " selected='selected'" : "")?> data-old_type="<?=$qualif['old_type']?>" title="<?=$MODULE_OUTPUT["spec_type_name"][$qualif['old_type']]; ?>"><?=$qualif["name"]?></option>
										<?php } ?>
									</select>&nbsp;<a href="<?=$EE["engine_uri"]?>manage_qualifications/">Управление квалификациями</a>
								</dd>
							</dl>
						</div>
						<div class="clear"></div>
					</div>
					<div class="wide">
						<div class="form-notes">
							<dl>
								<dt><label>Заочная форма обучения: <input type="checkbox" value="1" name="internal_tuition"<?=($form_back['internal_tuition'] ? " checked='checked'" : "")?> /></label></dt>
								<dt><label>Очная форма обучения: <input type="checkbox" value="1" name="correspondence_tuition"<?=($form_back['correspondence_tuition'] ? " checked='checked'" : "")?> /></label></dt>
								<dt><label>Ведётся набор: <input type="checkbox" value="1" name="has_vacancies"<?=($form_back['has_vacancies'] ? " checked='checked'" : "")?> /></label></dt>
							</dl>
						</div>
						<div class="form-notes">
							<dl>
								<dd><label>Старая специальность</label></dd>
								<dt>
								<select name="old_spec" size="1" class="editable-select">
									<option value="0">--------------</option>
									<?php foreach ($MODULE_OUTPUT['old_specs'] as $dir_code => $dir) { ?>
									<optgroup label="<?=$dir["code"];?> - <?=$dir["title"];?>">
									<?php foreach ($dir["specs"] as $old_id => $old_spec) { ?>
									<option value="<?=$old_id; ?>"<?=($form_back['old_spec'] == $old_id ? " selected='selected'" : "")?>><?=$old_spec['code']?> - <?=$old_spec['name']?></option>
									<?php } ?>
									</optgroup>
									<?php } ?>
								</select>
								</dt>
							</dl>
						</div>
						<div class="clear"></div>
					</div>
					<div class="wide">
						<dl>
							<dt><label>Описание направления:</label></dt>
							<dd>
								<textarea name="description"  style="width=300px; height=100px" class="wysiwyg"><?=$form_back['description']?></textarea>
							</dd>
						</dl>
					</div>
					<div class="wide">
						<input type="submit" value="Добавить" class="button" />
					</div>
				</fieldset>
            </form>
<?php	} elseif(isset($MODULE_OUTPUT['sub_mode']) && $MODULE_OUTPUT['sub_mode'] == 'add_spec_type') {
			$form_back = $MODULE_OUTPUT['form_back'];
			if(isset($MODULE_OUTPUT["messages"]["bad"])) {
				foreach($MODULE_OUTPUT["messages"]["bad"] as $mess) { ?>
			<p class="message red"><?=$mess?></p>
<?php			}
			} ?>
            <form method="post" action="<?=$EE["unqueried_uri"]?>">
				<fieldset>
					<legend>Добавить уровень обучения в специальность <?=$MODULE_OUTPUT['spec_info']["code"]?> - <?=$MODULE_OUTPUT['spec_info']["name"]?> :</legend>
					<input type="hidden" name="action_sub" value="add_spec_type" /> 
					<input type="hidden" name="code" value="<?=$MODULE_OUTPUT['spec_info']["code"]?>" />
					<input type="hidden" name="spec_id" value="<?=$MODULE_OUTPUT['spec_info']["id"]?>" />
					<br/>
					<table border="1">
						<tr>
							<th bgcolor="#BBBBBB">Год открытия</th>
							<th bgcolor="#BBBBBB">Уровень обучения</th>
							<th bgcolor="#BBBBBB">Срок обучения</th>
							<th bgcolor="#BBBBBB">Очная форма обучения</th>
							<th bgcolor="#BBBBBB">Заочная форма обучения</th>
							<th bgcolor="#BBBBBB">Ведётся набор</th>
						</tr>
					    <tr>
					        <td><input type="text" name="year_open_sub" value="<?=$form_back['year_open_sub']?>" /></td>
					        <td>
								<?php if($special['old'] == 1) { ?><select name="type_sub" size="1">
									<option value="0"<?=($form_back['type_sub'] == "0" ? " selected='selected'" : "")?>>Тип уровня обучения</option>
									<option value="secondary"<?=($form_back['type_sub'] == "secondary" ? " selected='selected'" : "")?>>51 - Среднее професиональное образование</option>
									<option value="higher"<?=($form_back['type_sub'] == "higher" ? " selected='selected'" : "")?>>65 - Высшее професиональное образование</option> 
									<option value="bachelor"<?=($form_back['type_sub'] == "bachelor" ? " selected='selected'" : "")?>>62 - Бакалавриат</option> 
									<option value="magistracy"<?=($form_back['type_sub'] == "magistracy" ? " selected='selected'" : "")?>>68 - Магистратура</option> 
								</select><?php } else { ?>
                <input type="hidden" name="type_sub" value="" />
									<select name="qualification" size="1" onchange="console.log(this.options[this.selectedIndex].getAttribute('data-old_type'));this.form.elements['type_sub'].value = this.options[this.selectedIndex].getAttribute('data-old_type');">
										<option value="0"<?=($form_back['qualification'] == 0 ? " selected='selected'" : "")?> data-old_type="">Квалификация</option>
										<?php foreach($MODULE_OUTPUT["qualifications"] as $qual_id => $qualif) { ?>
										<option value="<?=$qual_id?>"<?=($form_back['qualification'] == $qual_id ? " selected='selected'" : "")?> data-old_type="<?=$qualif['old_type']?>"><?=$qualif["name"]?></option>
										<?php } ?>
									</select>
                <?php } ?>
					        </td>
					        <td><input type="text" name="data_training" value="<?=$form_back['data_training']?>" /></td>
					        <td align="center"><input type="checkbox" name="internal_tuition_sub"<?=($form_back['internal_tuition_sub'] ? " checked='checked'" : "")?> /></td>
					        <td align="center"><input type="checkbox" name="correspondence_tuition_sub"<?=($form_back['correspondence_tuition_sub'] ? " checked='checked'" : "")?> /></td>
							<td align="center"><input type="checkbox" name="has_vacancies"<?=($form_back['has_vacancies'] ? " checked='checked'" : "")?> /></td>
					    </tr>
					</table>
					<br/>
					<input type="submit" value="Добавить" class="button" />
				</fieldset>
            </form>
<?php } elseif(isset($MODULE_OUTPUT['sub_mode']) && $MODULE_OUTPUT['sub_mode'] == 'add_profile') {
			$form_back = $MODULE_OUTPUT['form_back'];
			if(isset($MODULE_OUTPUT["messages"]["bad"])) {
				foreach($MODULE_OUTPUT["messages"]["bad"] as $mess) { ?>
			<p class="message red"><?=$mess?></p>
<?php			}
			} ?>
            <form method="post" action="<?=$EE["unqueried_uri"]?>">
				<fieldset>
					<legend>Добавить профиль в специальность <?=$MODULE_OUTPUT['spec_info']["code"]?> - <?=$MODULE_OUTPUT['spec_info']["name"]?>:</legend>
					<input type="hidden" name="action_sub" value="add_profile" /> 
					<input type="hidden" name="code" value="<?=$MODULE_OUTPUT['spec_info']["code"]?>" />
					<input type="hidden" name="spec_id" value="<?=$MODULE_OUTPUT['spec_info']["id"]?>" />
					<div class="wide">
						<input type="hidden" name="action_op" value="add" />
						<div class="form-notes">
							<dl>
								<dt><label>Название профиля:</label></dt>
								<dd><input type="text" name="profile_name" style="width:484px" value="<?=$form_back['profile_name']?>" /></dd>
							</dl>
						</div>
						<div class="clear"></div>
					</div>
					<div class="wide">
						<input type="submit" value="Добавить" class="button" />
					</div>
				</fieldset>
			</form>
<?php	} elseif(isset($MODULE_OUTPUT['sub_mode']) && $MODULE_OUTPUT['sub_mode'] == 'edit_speciality' && isset($MODULE_OUTPUT['speciality']) && count($MODULE_OUTPUT['speciality'])) {
			foreach($MODULE_OUTPUT["faculty"] as $faculty) {
        		$faculty_ids[] = $faculty['id'];
        	};
			$special = $MODULE_OUTPUT["speciality"]; ?>
            <form action="<?=$EE["unqueried_uri"]?>" method="post">
				<fieldset>
					<legend><?=$special["code"]?>&nbsp;-&nbsp;<?=$special["name"]?></legend>
					<input type="hidden" name="spec_id" value="<?=$MODULE_OUTPUT['speciality']['id']?>" />
					Код специальности:<br />
					<input type="text" name="code" value="<?=$special["code"]?>" /><br />Название специальности:<br />
					<input type="text" name="speciality_name" style="width:484px" value="<?=$special["name"]?>" /><br/>Выберите факультет:<br />
					<select name="faculty" size="1">
					    <option selected="<?=(in_array($special["id_faculty"], $faculty_ids) ? '':'selected')?>">--------------------------------------------</option>
<?php  					$faculty_checked = false;
						foreach($MODULE_OUTPUT["faculty"] as $faculty) {
							if ($faculty["id"]==$special["id_faculty"]) { ?>
						<option value="<?=$faculty["id"]?>" selected="selected"><?=$faculty["name"]?></option> 
<?php 							$faculty_checked = true; 
							} else { ?>
					    <option value="<?=$faculty["id"]?>"><?=$faculty["name"]?></option>
<?php 						}
						} ?>
					</select><br />
					Выберите направление:<br />
					<select name="direction" size="1">
					    <!--<option value="0" selected="selected"><?=$special["direction"]?></option>-->
					    <option disabled>--------------------------------------------</option>
<?php 					foreach($MODULE_OUTPUT["direction"] as $dir) {
							if ($dir["code"]==$special["direction"]) { ?>
						<option value="<?=$dir["code"]?>" selected="selected"><?=$dir["code"]?> - <?=$dir["name"]?></option> 
<?php 						} else { ?>
					    <option value="<?=$dir["code"]?>"><?=$dir["code"]?> - <?=$dir["name"]?></option>
<?php 						}
						}
						$i=0; ?>
					</select><br />
          <?php if($special['old'] == 0) { ?>
          Старая специальность: <br />
          <select name="old_spec" size="1" class="editable-select">
									<option value="0">--------------</option>
									<?php foreach ($MODULE_OUTPUT['old_specs'] as $dir_code => $dir) { ?>
									<optgroup label="<?=$dir["code"];?> - <?=$dir["title"];?>">
									<?php foreach ($dir["specs"] as $old_id => $old_spec) { ?>
									<option value="<?=$old_id; ?>"<?=($special['old_id'] == $old_id ? " selected='selected'" : "")?>><?=$old_spec['code']?> - <?=$old_spec['name']?></option>
									<?php } ?>
									</optgroup>
									<?php } ?>
								</select><br />
                <?php } ?>
          <br />
					<table border="1">
						<tr>
							<th bgcolor="#BBBBBB">№ п.п</th>
							<th bgcolor="#BBBBBB">Год открытия</th>
							<th bgcolor="#BBBBBB"><?php if($special['old']) { ?>Уровень подготовки<?php } else { ?>Квалификация<?php } ?></th>
							<th bgcolor="#BBBBBB">Очная<br />форма<br />обучения</th>
							<th bgcolor="#BBBBBB">Заочная<br />форма<br />обучения</th>
							<th bgcolor="#BBBBBB">Ведется<br />набор</th>
							<th bgcolor="#BBBBBB">Продолжительность</th>
							<th bgcolor="#BBBBBB">Уровень обучения</th>
						</tr>
<?php 					foreach($MODULE_OUTPUT["speciality_edit"] as $edit) {
							$i++; ?>
					    <tr>
					        <td align="center"><?=$i?><?php if($special['old'] == 0) { ?><input type="hidden" name="qualification[<?=$edit["qual_id"]?>]" value="<?=$edit["qual_id"]?>" /><?php } ?></td>
					        <td>
								<input type="hidden" name="spec_type[<?=$special['old'] ? $edit["type"]: $edit['id']; ?>][st_type_id]" value="<?=$edit["id"]?>" />
								<input type="text" name="spec_type[<?=$special['old'] ? $edit["type"]: $edit['id']; ?>][year_open]" value="<?=$edit["year_open"]?>" />
					        </td>
					        <td>
								<?php if($special['old'] == 1) { ?><select name="spec_type[<?=$edit["type"]?>][type]" size="1">
<?php						switch ($edit["type"]) {
                                case secondary: ?>
									<option value="secondary" selected="selected">Среднее професcиональное образование</option>
					                <option disabled='disabled'>---------------------------------------</option>
<?php 							break;
                                case higher: ?>
									<option value="higher" selected="selected">Высшее професcиональное образование</option>
					                <option disabled>---------------------------------------</option>
<?php 							break;
                                case bachelor: ?>
									<option value="bachelor" selected="selected">Бакалавриат</option>
					                <option disabled>---------------------------------------</option>
<?php 							break;
                                case magistracy: ?>
									<option value="magistracy" selected="selected">Магистратура</option>
					                <option disabled>---------------------------------------</option>
<?php 							break;
                            } ?>                            
									<option value="secondary" disabled='disabled'>Среднее профеcсиональное образование</option>
									<option value="higher" disabled='disabled'>Высшее професcиональное образование</option> 
									<option value="bachelor" disabled='disabled'>Бакалавриат</option> 
									<option value="magistracy" disabled='disabled'>Магистратура</option> 
								</select><?php } else { ?>
								<select name="spec_type[<?=$edit['id']?>][qual_id]" size="1" onchange="console.log(this.options[this.selectedIndex].getAttribute('data-old_type'));document.getElementById('spec_type_<?=$edit['id'];?>').value = this.options[this.selectedIndex].getAttribute('data-old_type');">
										<option value="0"<?=($edit['qual_id'] == 0 ? " selected='selected'" : "")?> data-old_type="">Квалификация</option>
										<?php foreach($MODULE_OUTPUT["qualifications"] as $qual_id => $qualif) { ?>
										<option value="<?=$qual_id?>"<?=($edit['qual_id'] == $qual_id ? " selected='selected'" : "")?> data-old_type="<?=$qualif['old_type']?>"><?=$qualif["name"]?></option>
										<?php } ?>
									</select>
                  <input type="hidden" name="spec_type[<?=$edit['id']?>][type]" value="<?=$MODULE_OUTPUT["qualifications"][$edit['qual_id']]['old_type'];?>" id="spec_type_<?=$edit['id'];?>" />
								<?php } ?>
					        </td>
					        <td align="center"><input type="checkbox" name="spec_type[<?=$special['old'] ? $edit["type"]: $edit['id']; ?>][internal_tuition]" <?php if ($edit["internal_tuition"]==1){?>checked <?php }?> /></td>
					        <td align="center"><input type="checkbox" name="spec_type[<?=$special['old'] ? $edit["type"]: $edit['id']; ?>][correspondence_tuition]" <?php if ($edit["correspondence_tuition"]==1){?>checked <?php }?> /></td>
					        <td align="center"><input type="checkbox" name="spec_type[<?=$special['old'] ? $edit["type"]: $edit['id']; ?>][has_vacancies]" <?php if ($edit["has_vacancies"]){?>checked <?php }?> /></td>
					        <td align="center"><input type="text" name="spec_type[<?=$special['old'] ? $edit["type"]: $edit['id']; ?>][data_training]" value="<?=$edit["data_training"]?>" /></td>
							<td align="center"><input type="text" name="spec_type[<?=$special['old'] ? $edit["type"]: $edit['id']; ?>][qualification]" value="<?=$edit["qualification"]?>" /></td>                       
					    </tr>
<?php 					} ?>
					</table>
          <a href="<?=$EE["engine_uri"]?>add_spec_type/<?=$special['id'];?>/">Добавить <?php if($special['old'] == 0) { ?>квалификацию<?php } else { ?>уровень подготовки<?php } ?></a><br />  
          <br />
          
          <?php if($special['old'] == 0 && count($MODULE_OUTPUT["speciality_edit"]) > 0) { ?>   
          <fieldset>
          <legend>Связанные файлы</legend>
          <table border="1">
          <tr>
          <th>Квалификация</th>
          <?php foreach($MODULE_OUTPUT['file_types'] as $file_type) { ?>
          <th><?php echo $file_type; ?></th>
          <?php } ?>
          </tr>
          <?php foreach($MODULE_OUTPUT["speciality_edit"] as $edit) { ?>
            <tr>
              <td><?php echo $MODULE_OUTPUT["qualifications"][$edit['qual_id']]['name']; ?></td> 
              <?php foreach($MODULE_OUTPUT['file_types'] as $file_type) { ?>
              <td>
              <select name="spec_file[<?php echo $file_type; ?>][<?=$edit['id']?>]" style="max-width: 300px; ">
              <option value="0"></option>
              <?php 
              foreach($MODULE_OUTPUT["files"] as $file_id => $file) {
                ?><option value="<?php echo $file_id;?>"<?php if($file_id == $MODULE_OUTPUT["spec_files"][$file_type][$edit["qual_id"]]["file_id"]) { ?>  selected="selected"<?php } ?>><?=$file["name"]?>.<?=$file["filename"];?></option><?php 
              }
              ?>
              </select>
              </td>
              <?php } ?>
            </tr> 
          <?php } ?>
          </table>
          </fieldset>
          <?php } ?>
<?php 				if(count($MODULE_OUTPUT["profiles"])) {
						?>
						Профили: <br /> 
						<?php 	
						foreach($MODULE_OUTPUT["profiles"] as $profile) {
							?>
								<input type="hidden" name="profile[<?=$profile["id"]?>][id]" value="<?=$profile["id"]?>" />
								<input type="text" name="profile[<?=$profile["id"]?>][name]" style="width:484px"  value="<?=$profile["name"]?>" /> 
								<a title="Удалить" href="<?=$EE["engine_uri"]?>del_profile/<?=$profile["id"]?>/"><img src="/themes/images/delete.png" alt="Удалить" onclick="if(confirm('Вы действительно желаете удалить профиль?')){return true;}else{return false;}" /></a>
								<br />
							<?php
						}
						?>
						<br /><br />
						<?php 
					}
?>
					<b>Общее описание специальности:</b><br />
					<textarea name="description" cols="60" rows="15" class="wysiwyg"><?=$special["description"]?></textarea><br />
<?php 					foreach($MODULE_OUTPUT["speciality_edit"] as $edit) {
							if ($edit["type"] == "bachelor")
								$dir = "бакалавриат";
							elseif ($edit["type"] == "magistracy")
								$dir = "магистратура";
							elseif ($edit["type"] == "higher")
								$dir = "ВПО";
							elseif ($edit["type"] == "secondary")
								$dir = "СПО"; 
                if($special['old'] == 0) $dir = $MODULE_OUTPUT['qualifications'][$edit['qual_id']]['name'];
                ?>
					<br />
					<fieldset>
						<legend><b><?php if($special['old'] == 1) { ?>Направление<?php } ?> <?=$dir?></b></legend>						
						Список экзаменов:<br />
<?php						if ($edit["type"] == "magistracy") {
								echo "Собеседование по профилю выбранного направления подготовки";
							} else {
								foreach ($MODULE_OUTPUT["exams"] as $exam) { ?>
						<input type="checkbox" name="spec_type[<?=$special['old'] ? $edit["type"]: $edit['id']; ?>][exams][<?=$exam["id"]?>]"<?=(($exam["need"] && $exam['type'] == $edit['type']) ? " checked='checked'" : "")?> /><?=$exam["name"]?><br />
<?php 							}
							} ?>
						<textarea name="spec_type[<?=$special['old'] ? $edit["type"]: $edit['id']; ?>][description]" cols="60" rows="15" class="wysiwyg"><?=$edit["description"]?></textarea>
					</fieldset><br />
<?php 					} ?>
					<p>
						<input type="submit" value="Сохранить изменения" />
						<input type="reset" value="Сброс" />
						<input type="submit" name="<?=$_GET["node"]?>[save][cancel]" value="Отмена" />
					</p>
				</fieldset>
            </form>
            <a href="<?=$EE["engine_uri"]?>add_profile/<?=$MODULE_OUTPUT['speciality']['id']?>">Добавить профиль</a>
<?php	} elseif(!empty($MODULE_OUTPUT["speciality_directory"])) { ?>
			<div class="action_speciality"><a href="add_speciality/">[ Добавить направление ]</a></div>
			<ul>
<?php 		foreach($MODULE_OUTPUT["speciality_directory"] as $special) { ?>
			    <li>
					<div class="list_title inline-block<?=(!$MODULE_OUTPUT["privileges"]["specialities.handle"]==true && !$special["handle"] ? ' disabled' : '')?>">
						<?=$special["code"]?>&nbsp;-&nbsp;<?=$special["name"]?>
<?php 			if ($MODULE_OUTPUT["privileges"]["specialities.handle"]==true || $special["handle"]) { ?>
						<div>
							<a title="Удалить описание и все уровни обучения" href="del_speciality/<?=$special["id"]?>/" onclick="if(confirm('Вы действительно желаете удалить направление?')){return true;}else{return false;}" ><img src="/themes/images/delete.png" alt="Удалить описание и все уровни обучения" /></a>
							<a title="Редактировать" href="edit_speciality/<?=$special["id"]?>/"><img src="/themes/images/edit.png" alt="Редактировать" /></a>
							<a href="add_spec_type/<?=$special["id"]?>/" title="Добавить уровень обучения"><img src="/themes/images/edit-add.png" alt="Добавить уровень обучения" /></a>
						</div>
<?php 			} ?>
					</div>
<?php			if(isset($special["spec_type"])) { ?>					
			        <ul>
<?php 				foreach($special["spec_type"] as $speciality) {
					    switch ($speciality["type"]) {
					        case "secondary":
					            $type_sp="Среднее професcиональное образование";
					        break;
					        case "higher":
					            $type_sp="Высшее професcиональное образование";
					        break;
					        case "magistracy":
					            $type_sp="Магистратура";
					        break;
					        case "bachelor":
					            $type_sp="Бакалавриат";
					        break;
					    } 
					    
					    if($speciality["qualification"]) $type_sp = $speciality["qualification"];
					    ?>
			            <li>
							<div class="list_title inline-block<?=(!$special["handle"] ? ' disabled' : '')?>">
								<?=$speciality["year_open"]?>&nbsp;-&nbsp;<?=$type_sp?>&nbsp;<?=((0 && $speciality['has_vacancies'])?'(ведётся набор)':'')?>
<?php 					if ($special["handle"]) { ?>
								<div>
<?php						if($special["spec_type_count"] > 1) { ?>
									<a title="Удалить" href="del_spec_type/<?=$speciality["id"]?>/"><img src="/themes/images/delete.png" alt="Удалить" onclick="if(confirm('Вы действительно желаете удалить уровень обучения?')){return true;}else{return false;}" /></a>
<?php						} ?>									
									<a title="Учебный план" href="curriculum/<?=$speciality["id"]?>/"><img src="/themes/images/clipboard.png" alt="Учебный план" /></a>
								</div>
<?php 					} ?>
							</div>
						</li>
<?php				} ?>
					</ul>
<?php			} ?>
<?php 		} ?>
				</li>
			</ul>
<?php 	} elseif(isset($MODULE_OUTPUT['sub_mode']) && $MODULE_OUTPUT['sub_mode'] == 'manage_qualifications') { ?>
		<form method="post" action="<?=$EE["unqueried_uri"]?>">
		<table border="1">
		<?php foreach($MODULE_OUTPUT['qualifications'] as $qual_id => $qualif) { ?>
		<tr>
		<td><input type="text" name="name[<?=$qual_id;?>]" value="<?=$qualif['name']; ?>" size="40" /></td>
		<td><select name="type[<?=$qual_id; ?>]" size="1">
				<option value="0"<?=($qualif['old_type'] == "0" ? " selected='selected'" : "")?>>---------------</option>
				<option value="secondary"<?=($qualif['old_type'] == "secondary" ? " selected='selected'" : "")?>>51 - Среднее професиональное образование</option>
				<option value="higher"<?=($qualif['old_type'] == "higher" ? " selected='selected'" : "")?>>65 - Высшее професиональное образование</option> 
				<option value="bachelor"<?=($qualif['old_type'] == "bachelor" ? " selected='selected'" : "")?>>62 - Бакалавриат</option> 
				<option value="magistracy"<?=($qualif['old_type'] == "magistracy" ? " selected='selected'" : "")?>>68 - Магистратура</option> 
			</select></td>
		<td><input type="checkbox" name="del[<?=$qual_id;?>]" value="<?=$qual_id; ?>" /><input type="hidden" name="id[]" value="<?=$qual_id; ?>" /></td>
		</tr>
		<?php } ?>
		</table>
		<p><input type="submit" value="Сохранить" />
		<input type="hidden" name="action" value="save_qualifications" /></p>
		</form><br />
		<form method="post" action="<?=$EE["unqueried_uri"]?>">
			<fieldset>
			<legend>Добавление квалификации</legend>
			Название: <br />
			<input type="text" name="name" size="40" /><br />
			Старый уровень обучения: <br />
			<select name="type" size="1">
				<option value="0"<?=($form_back['type'] == "0" ? " selected='selected'" : "")?>>---------------</option>
				<option value="secondary"<?=($form_back['type'] == "secondary" ? " selected='selected'" : "")?>>51 - Среднее професиональное образование</option>
				<option value="higher"<?=($form_back['type'] == "higher" ? " selected='selected'" : "")?>>65 - Высшее професиональное образование</option> 
				<option value="bachelor"<?=($form_back['type'] == "bachelor" ? " selected='selected'" : "")?>>62 - Бакалавриат</option> 
				<option value="magistracy"<?=($form_back['type'] == "magistracy" ? " selected='selected'" : "")?>>68 - Магистратура</option> 
			</select><br />
			<input type="submit" value="Добавить" />
			<input type="hidden" name="action" value="add_qualification" />
			</fieldset>
		</form>
<?php 	} else { ?>
			<p>Специальностей не существует</p>
<?php 	} ?>
		</div>
<?php
	}
    break;

	case "curriculum": {
		if(isset($MODULE_OUTPUT['curriculum'])) {
			$curriculum = $MODULE_OUTPUT['curriculum'];
			if(isset($MODULE_OUTPUT['form_back']))
				$form_back = $MODULE_OUTPUT['form_back'];
			/*echo "<pre>";
			print_r($curriculum);
			echo "</pre>";*/
			if(isset($MODULE_OUTPUT['curriculum_allowed']) && $MODULE_OUTPUT['curriculum_allowed']) { ?>
		<script>jQuery(document).ready(function($) {Curriculum(<?=$MODULE_OUTPUT['module_id']?>);});</script>
<?php		}?>
		<div id="curriculum">
<?php		if(isset($MODULE_OUTPUT['curriculum_allowed']) && $MODULE_OUTPUT['curriculum_allowed']) { ?>
<?php			if(isset($MODULE_OUTPUT['mess']['curriculum']) && count($MODULE_OUTPUT['mess']['curriculum'])) { ?>			
			<div class="wide">
<?php				foreach($MODULE_OUTPUT['mess']['curriculum'] as $mess) { ?>
				<p class="message red"><?=$mess?></p>
<?php				} ?>
			</div>
<?php			} ?>
			<form action="<?=$EE["unqueried_uri"]?>" method="post">
				<fieldset>
					<legend>Добавить цикл дисциплин</legend>
					<div class="wide">
						<div class="form-notes">
							<dl>
								<dt><label>Шифр цикла:</label></dt>
								<dd><input type="text" maxlength="10" name="sgroup_code" value="<?=(isset($form_back) ? $form_back['add_sgroup']['sgroup_code'] : "")?>" /></dd>
							</dl>
							<div class="wide">
								<input type="submit" value="Добавить" />
							</div>
						</div>
						<div class="form-notes">
							<dl>
								<dt><label>Название цикла дисциплин:</label></dt>
								<dd><input class="input_long" type="text" name="sgroup_name" size="50" value="<?=(isset($form_back) ? $form_back['add_sgroup']['sgroup_name'] : "")?>" /></dd>
							</dl>
							<div class="wide">
								<input type="hidden" name="add_sgroup" value="1" />
							</div>
						</div>
						<div class="clear"></div>
					</div>
				</fieldset>
			</form>
			<form action="<?=$EE["unqueried_uri"]?>" method="post">
				<fieldset>
					<legend>Удалить цикл дисциплин</legend>
					<div class="wide">
						<div class="form-notes">
							<dl>
								<dt><label>Цикл дисциплин:</label></dt>
								<dd>
<?php			if($curriculum['sgroup_list']) { ?>
									<select size="1" name="edit_sgroup_id">
<?php				foreach($curriculum['sgroup_list'] as $subject_group) { ?>
										<option value="<?=$subject_group['id']?>" title="<?=$subject_group['code']?>"<?=(isset($form_back['add_subject']['sgroup_id']) && $form_back['add_subject']['sgroup_id'] == $subject_group['id'] ? " selected='selected'" : "")?>><?=$subject_group['name']?></option>
<?php				} ?>
									</select>
<?php			} else { ?>
									Цикл дисциплин отсутствует.
<?php			} ?>
								</dd>
							</dl>
							<div class="wide">
								<input type="hidden" name="action_del" value="1" />
								<input type="submit" value="Удалить" />
							</div>
						</div>
						<div class="clear"></div>
					</div>
				</fieldset>
			</form>
			<form action="<?=$EE["unqueried_uri"]?>" method="post">
				<fieldset>
					<legend>Добавить дисциплины в цикл дисциплин</legend>
					<div class="wide">
						<div class="form-notes">
							<dl>
								<dt><label>Выберите цикл дисциплин:</label></dt>
								<dd>
<?php			if($curriculum['sgroup_list']) { ?>
									<select class="select_sgroup" size="1" name="sgroup_id">
<?php				foreach($curriculum['sgroup_list'] as $subject_group) { ?>
										<option value="<?=$subject_group['id']?>" title="<?=$subject_group['code']?>"<?=(isset($form_back['add_subject']['sgroup_id']) && $form_back['add_subject']['sgroup_id'] == $subject_group['id'] ? " selected='selected'" : "")?>><?=$subject_group['name']?></option>
<?php				} ?>
									</select>
<?php			} else { ?>
									Цикл дисциплин отсутствует.
<?php			} ?>
								</dd>
							</dl>
							<div class="wide">
								<input type="hidden" name="add_subject" value="1" />
							</div>
						</div>
						<div class="form-notes">
							<div class="select_subject">
								<label>Выберите и укажите шифр дисциплины</label>
<?php			if(isset($curriculum['departments'])) { ?>
								<ul>
<?php				foreach($curriculum['departments'] as $dep_id => $dep) { ?>
									<li>
										<label><?=$dep['name']?></label>
										<dl>
<?php					foreach($dep['subjects'] as $subj_id => $subj) { ?>
											<dt class="subj_<?=$subj_id?>">
												<label title="<?=$subj?>">
													<input type="checkbox" value="<?=$subj_id?>" name="subject_id[<?=$subj_id?>]"<?=(isset($form_back['add_subject']['select_subject'][$subj_id]) ? " checked='checked'" : "")?> /><?=$subj?>
												</label>
											</dt>
											<dd>
												<label>Шифр дисциплины: <span></span><input class="subject_code" type="text" maxlength="10" name="subject_code[<?=$subj_id?>]" value="<?=(isset($form_back['add_subject']['select_subject'][$subj_id]) ? $form_back['add_subject']['select_subject'][$subj_id] : "")?>" /></label>
											</dd>
<?php					} ?>
										</dl>
									</li>
<?php				} ?>
								</ul>
<?php			} ?>
							</div>							
						</div>
						<div class="wide">
							<input type="submit" value="Добавить" />
						</div>
					</div>
				</fieldset>
			</form>
<?php		} ?>			
			<div class="wide">
				<div class="form-notes">
					<p>Направление подготовки, специальность:</p>
					<p>Факультет:</p>
				</div>
				<div class="form-notes">
					<p><?=$curriculum['scode']?>.<?=$curriculum['stcode']?> - <?=$curriculum['sname']?></p>
					<p><?=$curriculum['fname']?></p>
				</div>
				<div class="clear"></div>
			</div>
<?php		if(isset($curriculum['subject_group'])) {
				foreach($curriculum['subject_group'] as $group_id => $subject_group) { ?>
			<fieldset class="subject_group_item">
				<form class="form_legend edit_form del_sgroup" action="<?=$EE["unqueried_uri"]?>" method="post">
					Цикл: <span class="action_del"><?=$subject_group['sgroup_name']?></span>
					<input class="action_edit" type="text" maxlength="10" name="edit_sgroup_code" size="10" value="<?=$subject_group['sgroup_code']?>" title="Шифр" />
					<input class="action_edit" type="text" name="edit_sgroup_name" size="50" value="<?=$subject_group['sgroup_name']?>" title="Название цикла дисциплин" />
					<input type="hidden" name="edit_sgroup_id" value="<?=$group_id?>" />
					<button class="input_submit action_del" type="submit" value="" title="Удалить (если нет дисциплин)"></button>
					<button class="input_submit action_edit" type="submit" value="" title="Сохранить изменения"></button>
					<label class="inline-block action_del" title="Редактировать"><input type="radio" name="action_del" value="0" /></label>
					<label class="inline-block action_edit" title="Отмена редактирования"><input type="radio" name="action_del" value="1" checked='checked' /></label>
				</form>
				<div class="subject_list">
<?php				foreach($subject_group['subjects'] as $cur_id => $subjects) { ?>
					<div class="subject_row">						
						<form class="del_sgroup edit_form" action="<?=$EE["unqueried_uri"]?>" method="post">
							<div class="subject_action">
								<input type="hidden" name="edit_cur_id" value="<?=$cur_id?>" />
								<input type="hidden" name="edit_subject_id" value="<?=$subjects['subj_id']?>" />
								<button class="input_submit action_del" type="submit" value="" title="Удалить"></button>
								<button class="input_submit action_edit" type="submit" value="" title="Сохранить"></button>
								<label class="inline-block action_del" title="Редактировать"><input type="radio" name="action_del" value="0" /></label>
								<label class="inline-block action_edit" title="Отмена редактирования"><input type="radio" name="action_del" value="1" checked='checked' /></label>
							</div>
							<div class="subject_pos">
								<span class="action_del"><?=$subjects['pos']?></span>
								<input class="action_edit" type="text" maxlength="5" pattern="^[0-9]+$" name="edit_subject_pos" size="3" value="<?=$subjects['pos']?>" title="Позиция дисциплины (только цифры)" />
							</div>
							<div class="subject_code">
								<span class="action_del"><?=$subject_group['sgroup_code']?>.<?=$subjects['scode']?></span>
								<input class="action_edit" type="text" maxlength="10" name="edit_subject_code" size="10" value="<?=$subjects['scode']?>" title="Шифр дисциплины" />
							</div>
							<div class="subject_cn">
								<div class="subject_name">
									<span class="action_del"><?=$subjects['sname']?></span>
<?php			if(isset($curriculum['departments'])) { ?>
									<select class="action_edit" size="1" name="select_subject_id" title="Выберите дисциплину">
<?php				foreach($curriculum['departments'] as $dep_id => $dep) { ?>
										<optgroup label="<?=$dep['name']?>">
<?php					foreach($dep['subjects'] as $subj_id => $subj) {
							if(!isset($subject_group['subjects'][$subj_id]) || $subjects['subj_id'] == $subj_id) { ?>
											<option value="<?=$subj_id?>"<?=($subjects['subj_id'] == $subj_id ? " selected='selected'" : "")?>><?=$subj?></option>
<?php						}
						}?>
										</optgroup>
<?php				} ?>
									</select>
<?php			} ?>
								</div>
							</div>
							<div class="subject_dep"><?=$subjects['sdep']?></div>
						</form>
					</div>
<?php				} ?>
				</div>
			</fieldset>
<?php			}
			} else { ?>
			<fieldset><p>Учебный план не заполнен</p></fieldset>
<?php		} ?>
		</div>
<?php	}
	}
	break;
    
    case "search_spec": { ?>
        <h2>Люди</h2>
        <form action="/speciality/" method="get">
			<p>
				Специальность:
				<input name="name_search" type="text" size="35"/> <input type="submit" value="Найти" class="button" />
			</p>        
        </form>
<?php
	}
    break;
                  
    case "search_people": ?>
        <form id="search_people_form" action="/people/" method="get">
			<fieldset>
				<legend>Человек</legend>
				<p>
					<input name="person_name" type="text" size="20" /> <input type="submit" value="Найти" class="button" />
				</p>
			</fieldset>
        </form>
<?php   break;

	case "people_info": {
		/*echo "<pre>";
		print_r($MODULE_OUTPUT["man"]);
		echo "</pre>";*/
		if(isset($MODULE_OUTPUT["info_view"])) {
			if(!empty($MODULE_OUTPUT["man"])) {
				$man = $MODULE_OUTPUT["man"]; ?>
			<h4><strong>Общая информация</strong></h4>
			<p><?=$man["last_name"]?> <?=$man["name"]?> <?=$man["patronymic"]?><?=(!empty($man["prad"]) ? ", ".$man["prad"] : "")?></p>
<?php			if(isset($MODULE_OUTPUT["people_fac_dep"])) { ?>
			<dl>
				<dt>Место работы:</dt>
<?php				foreach($MODULE_OUTPUT["people_fac_dep"] as $fac) { ?>
				<dd>
					<dl>
						<dt><a href="<?=$fac['uri']?>"><?=$fac['name']?></a></dt>
<?php					foreach($fac['departments'] as $dep) { ?>
						<dd><a href="<?=$dep['uri']?>"><?=$dep['name']?></a></dd>
<?php					} ?>
					</dl>
				</dd>
<?php				} ?>
			</dl>
<?php			}
				if(isset($MODULE_OUTPUT["subjects"])) { ?>
				<dl>
					<dt>Преподаваемые дисциплины:</dt>
<?php				foreach($MODULE_OUTPUT["subjects"] as $subj) { ?>
					<dd><?=$subj?></dd>
<?php				} ?>
				</dl>
<?php			}
			}
		} else { ?>
		<div class="shadow_rbg">
			<div class="people_info">
<?php		if(!empty($MODULE_OUTPUT["man"])) {
				$man = $MODULE_OUTPUT["man"]; ?>
				<div class="photo">
<?php			if($man["photo"]) { ?>
					<a href="/images/people/<?=$man["original_photo"]?>" target="_blank" onclick="return hs.expand(this);">
						<img src="/images/people/<?=$man["photo"]?>" alt="" />
					</a>
<?php 			} else { ?>
					<img src="/images/people/no_photo.jpg" alt="" />
<?php 			} ?>
				</div>
				<div class="people_name"><span><?=$man["last_name"]?></span><br /><?=$man["name"]?> <?=$man["patronymic"]?></div>
				<?=$man["comment"]?>
<?php		} else { ?>
				<div class="photo">
					<img src="/images/people/no_photo.jpg" alt="" />
				</div>
<?php			if(isset($MODULE_OUTPUT['user_displayed_name'])) { ?>
				<div class="people_name"><?=$MODULE_OUTPUT['user_displayed_name']?></div>
<?php			} else { ?>
				<p>Информация отсутствует.</p>
<?php			}
			} ?>					
			</div>	
			<div class="shadow_tr">&nbsp;</div>
			<div class="shadow_b">
				<div class="shadow_br">&nbsp;</div>
				<div class="shadow_bl">&nbsp;</div>
			</div>
		</div>
<?php
		}
	}
	break;
       
    case "people": {
        if(!empty($MODULE_OUTPUT["man"])) { 
			if ($MODULE_OUTPUT["allow_peopledit"]) { ?>
		<script>jQuery(document).ready(function($) {AjaxEditPhoto(<?=$MODULE_OUTPUT["module_id"]?>);});</script>
<?php       }
			$man = $MODULE_OUTPUT["man"];?>
            <h1><?=$man["last_name"]?> <?=$man["name"]?> <?=$man["patronymic"]?></h1>
			<div id="test_photo"></div>
			<div class="people_photo float-left">
				<em class="hidden"></em>
<?php  		if($man["photo"]) { ?>
				<a class="big_photo" href="/images/people/<?=$man["original_photo"]?>" target="_blank" onclick="return hs.expand(this);">
					<img src="/images/people/<?=$man["photo"]?>" alt="" />
				</a>
<?php			if ($MODULE_OUTPUT["allow_peopledit"]) { ?>
				<a href="javascript:void(0);" class="edit_photo">Редактировать фото</a>
<?php			} ?>
<?php 		} else { ?>
				<img src="/images/people/no_photo.jpg" alt="" />
<?php 		} ?>
			</div>
			<?=($man["status"] ? "<p><strong>" . $man["status"] . "</strong></p>": "")?>
			<?=($man["prad"] ? "<p><strong>Должность, степень, звание:</strong> ".$man["prad"]."</p>" : "")?>
			<?=($man["post"] && 0 ? "<p><strong>Должность:</strong> ".$man["post"]."</p>" : "")?>
			<?  if ($man["status_id"] == 2) {?>
			<?="<p><strong>Стаж работы: </strong>".($man["exp_full"] ? (intval(date('Y'))- $man["exp_full"]).( ( (((intval(date('Y'))- $man["exp_full"])%10) < 5) && (((intval(date('Y'))- $man["exp_full"])%10) != 0) && ((intval(date('Y'))- $man["exp_full"]) > 14 || (intval(date('Y'))- $man["exp_full"]) < 5) ) ? ( ((intval(date('Y'))- $man["exp_full"])%10) > 1 ? " года": " год" ) : " лет") : " нет данных")."<br />"?>
			<?="<strong>&nbsp;в том числе педагогический: </strong>".($man["exp_teach"] ? (intval(date('Y'))- $man["exp_teach"]).( ( (((intval(date('Y'))- $man["exp_teach"])%10) < 5) && (((intval(date('Y'))- $man["exp_teach"])%10) != 0) && ((intval(date('Y'))- $man["exp_teach"]) > 14 || (intval(date('Y'))- $man["exp_teach"]) < 5) ) ? ( ((intval(date('Y'))- $man["exp_teach"])%10) > 1 ? " года": " год" ) : " лет") : " нет данных")."</p>"?>
			<? } ?>
				<? //="Стаж работы: ".($man["exp_full"] ? "<p><strong>Стаж работы:</strong> с ".$man["exp_full"]." года </p>" : ((intval(date('Y'))- $man["exp_full"] + 1) < 5 ? ((intval(date('Y'))- $man["exp_full"] + 1) > 1 ? " года": " год" ) : " лет")." нет данных")?>
			<?//=($man["exp_teach"] ? "<strong>Педагогический стаж:</strong> с ".$man["exp_teach"]." года </p>" : "")?>
<?php 		if($man["status"] == "Абитуриент" && !empty($MODULE_OUTPUT["abit_specs"])) { ?>
				<p>Специальности:
<?php			$i = 1;
					foreach ($MODULE_OUTPUT["abit_specs"] as $spec) {
						if (!empty($spec["code"])) {
							echo '<a href="/'.($spec["form"] == 'заочная' ? 'izop/abiturientam' : 'entrant').'/'.($spec["form"] == 'вечерняя' ? 'list-vpo-ev' : ($spec["form"] == 'спо' ? 'list-spo' : 'list' ) ).'/?type='.$spec["type_id"].'" title="Список поступающих на специальность">'.$spec["code"];
							if(!empty($spec["qualification"])) {
								echo ".";
								echo $spec["qualification"];
							}
							echo '</a>';
								$to_show = '';
							if (!empty($spec["name"]) && $spec["name"])
								$to_show[] = '<a href="/'.($spec["form"] == 'заочная' ? 'izop/abiturientam' : 'entrant').'/'.($spec["form"] == 'вечерняя' ? 'list-vpo-ev' : ($spec["form"] == 'спо' ? 'list-spo' : 'list' ) ).'/?type='.$spec["type_id"].'" title="Список поступающих на специальность">'.$spec["name"].'</a>';
							if (!empty($spec["ege"]) && $spec["ege"])
								$to_show[] = "баллов: ".$spec["ege"];
							if (count($to_show))
								echo " (".implode(', ', $to_show).")";
							if ($i < count($MODULE_OUTPUT["abit_specs"]))
								echo ", ";
						}
						$i++;
					} ?>
				</p>
<?php			}
	 			if (!empty($MODULE_OUTPUT["displayed_departments"])) { //$man["post"] == "Преподаватель" &&  ?> 
				<p>
<?php				$i = 1;
					foreach ($MODULE_OUTPUT["displayed_departments"] as $value) {
						if ($value["url"])
							echo "<a href=\"".$value["url"]."\">".$value["name"]."</a>";
						else
							echo $value["name"];
						if ($i < count($MODULE_OUTPUT["displayed_departments"]))
								echo ", ";
						$i++;
					} ?>
				</p>
<?php			} ?>
            <?php // echo $man["id_speciality"] ? "Специальность: ".$man["id_speciality"].", ".$man["speciality"]["name"] ."<br />": ""?>
            <?php echo ($man["year"] && $man["year"]!="0000" && $man["status"]=="Студент") ? "Год поступления: ".$man["year"] ."<br />" : ""?>
			<?php echo $man["group_name"] ? "Группа: <a href=\"/student/timetable/groups/".$man["id_group"] ."/\">".$man["group_name"]."</a><br />" : ""?>
			<?php echo $man["comment"] ? " ".$man["comment"] ." " : ""?>
			
			<? if ($MODULE_OUTPUT["allow_peopledit"]) { ?><p class="actions"><a href="/people/edit/<?=$man["id"]?>/">Редактировать</a> || <a href="/people/delete/<?=$man["id"]?>/">Удалить</a></p><? } ?>
			
            <?php
			if (isset($MODULE_DATA["output"]["files_subj_list"]) || isset($MODULE_DATA["output"]["files_folder_list"]) || isset($MODULE_DATA["output"]["files_nosubj_list"]) /* && sizeof($MODULE_DATA["output"]["files_subj_list"])*/) {
                $arrow = array("", "", "");
                $url_sort = array("?sort=time&sort_desc=false", "?sort=name&sort_desc=false", "?sort=subj&sort_desc=false");
                if(isset($_GET["sort"])) {
                    if($_GET["sort"] == "time") {
                        if($_GET["sort_desc"] == "false") {
                            $arrow = array("&darr;", "", "");
                            $url_sort = array("?sort=time&sort_desc=true", "?sort=name&sort_desc=false", "?sort=subj&sort_desc=false");
                        }
                        else {
                            $arrow = array("&uarr;", "", "");
                            $url_sort = array("?sort=time&sort_desc=false", "?sort=name&sort_desc=false", "?sort=subj&sort_desc=false");
                        }
                    }
                    if($_GET["sort"] == "name") {
                        if($_GET["sort_desc"] == "false") {
                            $arrow = array("", "&darr;", "");
                            $url_sort = array("?sort=time&sort_desc=false", "?sort=name&sort_desc=true", "?sort=subj&sort_desc=false");
                        }
                        else {
                            $arrow = array("", "&uarr;", "");
                            $url_sort = array("?sort=time&sort_desc=false", "?sort=name&sort_desc=false", "?sort=subj&sort_desc=false");
                        }
                    }
                    if($_GET["sort"] == "subj") {
                        if($_GET["sort_desc"] == "false") {
                            $arrow = array("", "", "&darr;");
                            $url_sort = array("?sort=time&sort_desc=false", "?sort=name&sort_desc=false", "?sort=subj&sort_desc=true");
                        }
                        else {
                            $arrow = array("", "", "&uarr;");
                            $url_sort = array("?sort=time&sort_desc=false", "?sort=name&sort_desc=false", "?sort=subj&sort_desc=false");
                        }
                    }
                } else {
                    $arrow = array("", "", "");
                }
			?>
			<br /><h2>Файлы пользователя</h2>
            <div class="sort_list">
                <ul>
                    <li>Сортировать:</li>
                    <li id="by_time"><a href="<?=$url_sort[0]?>">по дате</a><?=$arrow[0]?></li>
                    <li id="by_alf"><a href="<?=$url_sort[1]?>">по алфавиту</a><?=$arrow[1]?></li>
                </ul>
            </div>
			<?php
                if(isset($_GET["sort"]) && $_GET["sort"] != "subj") {
                    echo "<ul class=\"bayan\">";
                    foreach ($MODULE_DATA["output"]["files_subj_list"] as $file_id => $file) {
                    	echo "<li><a class=\"load_f\" href='/file/".$file_id."/' title='".$file["file_discr"]."'>".$file["file_name"]."</a>";
                    	if (isset($file["subj_list"])) {
                    		echo " (";
                    		$i = 0;
                    		foreach ($file["subj_list"] as $subj) {
                    			if ($i == sizeof($file["subj_list"])-1)
                    				echo "<a href=\"/subjects/".$subj["subj_id"]."/\">".$subj["subj_name"]."</a>";
                    			else
                    				echo "<a href=\"/subjects/".$subj["subj_id"]."/\">".$subj["subj_name"]."</a>".", ";
                    			$i++;
                    		}
                    		echo ")";
							echo "&nbsp;&nbsp;(скачано: ".$file["file_count"].")";
                    	}
                        echo "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<ul class=\"bayan\">";
                    if(isset($MODULE_DATA["output"]["files_subj_list"])) {
						foreach ($MODULE_DATA["output"]["files_subj_list"] as  $subj_id => $subj) {
							echo "<li><a href=\"/subjects/".$subj_id."/\">".$subj["subj_name"]."</a><ul>";
								foreach ($subj["file_list"][$subj_id] as $file_list) {
									echo "<li><a class=\"load_f\" href='/file/".$file_list["file_id"]."/' title='".$file_list["file_discr"]."'>".$file_list["file_name"]."</a>&nbsp;&nbsp;(скачано: ".$file_list["file_count"].")</li>";
								}
							echo "</ul></li>";
						}
					}
					if(isset($MODULE_DATA["output"]["files_folder_list"])) {
						foreach ($MODULE_DATA["output"]["files_folder_list"] as  $folder_id => $folder) {
							echo "<li>".$folder["fd_name"]."<ul>";
                    		foreach ($folder["file_list"][$folder_id] as $file_list) {
                    			echo "<li><a class=\"load_f\" href='/file/".$file_list["file_id"]."/' title='".$file_list["file_discr"]."'>".$file_list["file_name"]."</a>&nbsp;&nbsp;(скачано: ".$file_list["file_count"].")</li>";
                    		}
							echo "</ul></li>";
						}
					}
                    foreach ($MODULE_DATA["output"]["files_nosubj_list"] as $file_id => $file) {
                        echo "<li><a class=\"load_f\" href='/file/".$file_id."/' title='".$file["file_discr"]."'>".$file["file_name"]."</a>&nbsp;&nbsp;(скачано: ".$file["file_count"].")</li>";
                    }
                    echo "</ul>";
                }
			}
        }
        else
        {
            ?>
            <form action="/people/" method="get">
            <p>
            Человек:
            <input name="person_name" type="text" size="35" value="<?php echo isset($_GET["person_name"]) ? $_GET["person_name"] : "" ?>"/> <input type="submit" value="Найти" class="button"/>
            </p>
            </form>
            <?
            if(!empty($MODULE_OUTPUT["people"]))
            {
                ?>
                <ul>
                <?php
                foreach($MODULE_OUTPUT["people"] as $man)
                {
                    ?>
                    <li><a href="/people/<?=$man["id"]?>/"><?=$man["last_name"]?> <?=$man["name"]?> <?=$man["patronymic"]?></a></li>
                    <?php
                }
                ?>
                </ul>
                <?php
            }
            else
            {
                if(isset($_GET["person_name"]))
                {
                ?>
                <p>Не найдено людей с такими параметрами поиска.</p>
                <?php
                }
                else
                {
                ?>
                <p>Введите в поле поиска данные о человеке.</p>
                <?php              
                }
            };
			if ($MODULE_OUTPUT["allow_addman"]) { ?><p class="actions"><a href="/people/add/">Добавить человека</a></p><? }
			
        };
	}
    break;
		
	case "delete_people": {
		$name = $MODULE_DATA["output"]["person_name"];
		$people_id = $MODULE_DATA["output"]["people_id"];
		if (isset($MODULE_DATA["output"]["is_user"])) { ?>
		Для пользователя <?=$name?> существует учётная запись. Действия:<br />
		<a href="?confirm=1">Удалить, сохранив учётную запись</a><br />
		<a href="?confirm=2">Удалить вместе с учётной записью</a><br />
		<a href="/people/<?=$people_id?>/">Не удалять</a>
<?php	} else { ?>
		Вы уверены, что хотите удалить запись о человеке <?=$name?>?<br />
		<a href="?confirm=1">Да</a><br />
		<a href="/people/<?=$people_id?>/">Нет</a>
<?php	}
	}
	break;
		
	case "edit_people": {
		if ($MODULE_OUTPUT["allow_peopledit"]) { ?>
		<script>jQuery(document).ready(function($) {GlobalPeople();AjaxEditPhoto(<?=$MODULE_OUTPUT["module_id"]?>);});</script>
<?php	}
		if(isset($MODULE_OUTPUT["display_variant"]["add_item"])) {
			$display_variant = $MODULE_OUTPUT["display_variant"]["add_item"];
		}
		$submode = $MODULE_DATA["output"]["submode"];
		$man = $MODULE_DATA["output"]["editperson"]; ?>
		<form id="people_form" class="people_cat_<?=$man["people_cat"]?>" method="post" enctype="multipart/form-data" onsubmit="if(document.getElementById('add_pass').value != document.getElementById('repeatpass').value) {alert('Пароль подтверждён неверно'); return false;}">
<?php	if($man["people_cat"] == 2) { ?>
			<div class="form_title">Редактирование информации о преподавателе <?=$man["last_name"]?> <?=(isset($man["name"]) ? $man["name"] : "")?> <?=(isset($man["patronymic"]) ? $man["patronymic"] : "")?></div>
<?php	} else { ?>
			<div class="form_title">Редактировать информацию о пользователе</div>
<?php	} ?>
			<fieldset>
				<legend>Общая информация</legend>
<?php	if(0) { ?>
				<div class="wide">
<?php		if(!isset($submode)) { ?>
					<div class="form-notes">
						<dl>
							<dt><label>Категория пользователя:</label></dt>
							<dd>
								<select id="people_cat_select" name="people_cat" size="1">
									<option value="1"<?=((!isset($display_variant) || (isset($display_variant) && $display_variant["people_cat"] == 1)) ? " selected='selected'" : "")?>>Студент</option>
									<option value="2"<?=(isset($display_variant) && $display_variant["people_cat"] == 2 ? " selected='selected'" : "")?>>Преподаватель</option>
									<option value="3"<?=(isset($display_variant) && $display_variant["people_cat"] == 3 ? " selected='selected'" : "")?>>Служебная учётная запись</option>
								</select>
							</dd>
						</dl>
					</div>
<?php		} ?>					
					<div class="form-notes">
						<dl id="responsible">
							<dt><label>Ответственный:</label></dt>
							<dd>
								<input type="checkbox" name="responsible"<?=(isset($display_variant["responsible"]) ? " checked='checked'" : "")?> />
							</dd>
						</dl>
					</div>
				</div>
<?php	} else { ?>
				<input name="people_cat" type="hidden" value="<?=$man["people_cat"]?>" />
<?php	} ?>				
				<div class="wide">
					<div class="form-notes">
						<dl>
							<dt><label>Фамилия:</label></dt>
							<dd><input name="last_name" type="text" class="input_long" value="<?=$man["last_name"]?>" /></dd>
							<dt><label>Имя:</label></dt>
							<dd><input name="first_name" type="text" class="input_long" value="<?=$man["name"]?>" /></dd>
							<dt><label>Отчество:</label></dt>
							<dd><input name="patronymic" type="text" class="input_long" value="<?=$man["patronymic"]?>" /></dd>
						</dl>						
					</div>
					<div class="form-notes">
						<dl>
							<dt><label>Фотография:</label></dt>
							<dd>
								<div class="user_photo float-right">
<?php	if(isset($man["photo"]) && $man["photo"]) { ?>
									<em class="hidden"></em>
									<a href="/images/people/<?=$man["original_photo"]?>" target="_blank" title="Редактировать фотографию">
										<img src="/images/people/<?=$man["photo"]?>" alt="" />
									</a>
<?php	} else { ?>
									<img src="/images/people/no_photo.jpg" alt="" />
<?php	} ?>
								</div>
								<span id="photoinput"><input name="user_photo" type="file" id="user_photo" /></span>
								<input name="delete_photo" onclick="if(this.checked) document.getElementById('photoinput').className='hidden'; else document.getElementById('photoinput').className='';" type="checkbox"> Удалить фотографию
							</dd>
							<dt><label>Пол:</label></dt>
							<dd><input type="radio" name="male" value="1"<?=($man["male"] ? " checked=\"checked\"" : "")?> /> М/Ж <input type="radio" name="male" value="0"<?=($man["male"] ? "" : " checked=\"checked\"")?> /></dd>
<?php	if($man["people_cat"] != 2) { ?>							
							<dt><label>Статус:</label></dt>
							<dd>
								<select name="status" size="1">
									<!--option value="0"></option-->
<?php 		foreach ($MODULE_DATA["output"]["statuses"] as $st) {
				if($st["people_cat"] == $man["people_cat"]) { ?>
									<option value="<?=$st["id"]?>"<?=($man["status_id"]==$st["id"] ? " selected='selected'" : "")?> class="people_cat_<?=$st["people_cat"]?>"><?=$st["name"]?></option>
<?php 			}
			} ?>
								</select>
							</dd>
<?php	} ?>							
						</dl>
					</div>
				</div>
				<div class="wide">
					<dl>
						<dt><label>Комментарий:</label></dt>
						<dd><textarea name="comment" class="wysiwyg" cols="40" rows="7" wrap="virtual"><?=$man["comment"]?></textarea></dd>
					</dl>
				</div>
			</fieldset>
<?php	if($man["people_cat"] == 2 && (!isset($submode) || $submode == 2)) { ?>
			<fieldset id="teacher_subform">
				<legend>Данные преподавателя</legend>
				<div class="wide">
					<div class="form-notes">
						<dl>
							<dt><label>Должность, степень, звание:</label></dt>
							<dd><input type="text" name="prad" size="35" value="<?=(isset($man["prad"]) ? $man["prad"] : "")?>" /></dd>
							<dt><label>Должность:</label></dt>
							<dd>
								<select name="post" size="1" class="select_long">
									<option value="Заведующий"<?=($man['post'] == 'Заведующий' ? ' selected="selected"' : '')?>>Заведующий</option>
									<option value="Преподаватель"<?=($man['post'] == 'Преподаватель' ? ' selected="selected"' : '')?>>Преподаватель</option>
									<option value="Лаборант"<?=($man['post'] == 'Лаборант' ? ' selected="selected"' : '')?>>Лаборант, Старший Лаборант, Врач, Аспирант</option>
								</select>
							</dd>
						</dl>
					</div>					
					<div class="form-notes">
						<dl>
							<dt><label>Год начала общего стажа:</label></dt>
							<dd><input type="text" name="exp_full" value="<?=(isset($man["exp_full"]) ? $man["exp_full"] : "")?>" /></dd>
							<dt><label>Год начала педагогического стажа:</label></dt>
							<dd><input type="text" name="exp_teach" value="<?=(isset($man["exp_teach"]) ? $man["exp_teach"] : "")?>" /></dd>
						</dl>
					</div>
				</div>
				<div class="wide">
					<div class="form-notes">
<?php		if(!$MODULE_OUTPUT["current_department"]) { ?>
						<dl>
							<dt><label>Выберите кафедру:</label></dt>
							<dd>
								<ul id="department_select" class="select_long">
<?php     		foreach($MODULE_OUTPUT["faculties"] as $faculties) { ?>
									<li>
										<dl>
											<dt><?=$faculties["fac_name"]?></dt>
<?php				foreach($faculties["departments"] as $department) { ?>
											<dd id="depid_<?=$department["id"]?>"<?=(isset($man["department"][$department["id"]]) ? " class='hidden'" : "")?> title="<?=$department["name"]?>"><?=$department["name"]?></dd>
<?php				} ?>
										</dl>
									</li>
<?php			} ?>
								</ul>
							</dd>
						</dl>
<?php		} ?>
					</div>					
					<div class="form-notes">
<?php		if(!$MODULE_OUTPUT["current_department"]) { ?>
						<dl>
							<dt><label>Преподаватель будет добавлен на кафедры:</label></dt>
							<dd>
								<ul id="department_list">
<?php			foreach($man["department"] as $dep_id => $dep) { ?>
									<li><?=$dep?><input type="hidden" value="<?=$dep_id?>" name="department[<?=$dep_id?>]" /><em class="inline-block"></em></li>
<?php			} ?>			
								</ul>
							</dd>
						</dl>
<?php		} else { ?>
						<dl>
							<dt><label>Преподаватель будет добавлен на кафедры:</label></dt>
							<dd>
								<ul>
<?php			foreach($MODULE_OUTPUT["current_department"] as $dep_id => $dep) { ?>									
									<li><?=$dep?><input type="hidden" value="<?=$dep_id?>" name="department[<?=$dep_id?>]" /><em class="inline-block"></em></li>
<?php			} ?>									
								</ul>
							</dd>
						</dl>
<?php		} ?>
					</div>
				</div>
				<div class="wide">
					<div class="form-notes">
						<dl>
							<dt><label>Кураторская работа:</label><input name="curator_selector" id="curator_selector" type="checkbox" <?=($man["is_curator"] ? "checked='checked'" : "") ?> /></dt>
							<dd>
								<textarea name="curator_text" id="curator_text" cols="60" rows="15" class="wysiwyg"><?=(!is_null($man["curator_text"]) ? $man["curator_text"] : '')?></textarea>
							</dd>
						</dl>	
					</div>
					
					
									
					<div class="form-notes" id="curator_groups_cont" >
						<dl>
							<dt><label>Группы куратора:</label></dt>
							<dd>
								<select id="curator_groups" size="8" multiple="true" >
									<options>
										<?php	foreach($man["department"] as $dep_id => $dep) { ?>									
										<optgroup id="<?=$dep_id?>" label="<?=$dep?>">
											<?php if (isset($man["curator_groups"]) && !empty($man["curator_groups"][$dep_id]))
											 		foreach ($man["curator_groups"][$dep_id] as $group) { ?>
												<option value="<?=$group['id']?>"><?=$group['name']?></option> 
												<? }
												else { ?>
												<option></option>
											<?php } ?>
										</optgroup>
										<?php }	 ?>
									</options>
								</select>
								<div id="curator_groups_selector" > >> </div>
								<?php	if (isset($man["curator_groups"]))
									foreach($man["department"] as $dep_id => $dep) 
										foreach ($man["curator_groups"][$dep_id] as $group) { ?>
											<input type="hidden" name="curator_groups[<?=$dep_id?>][<?=$group['id']?>]" value="<?=$group['id']?>" />
								<?php }?>
							</dd>
						</dl>	
						<dl>
							<dt><label>Список групп:</label></dt>
							<dd>
								<div id="all_groups_selector" style="display: inline-block; vertical-align: top; font-weight:bold; cursor: pointer; text-decoration: underline;"> << </div>
								<select id="all_groups" size="16" multiple="true" name="curator_groups[]" >
									<options>
										<?php foreach ($man["all_groups"] as $group) { ?>
											<option value="<?=$group['id']?>"><?=$group['name']?></option> 
										<? } ?>
									</options>
								</select>
							</dd>						
						</dl>
					</div>
					
				</div>
				<?php if($Engine->OperationAllowed($MODULE_OUTPUT["module_id"], "teachers.retired.handle", -1, $Auth->usergroup_id)){ ?>
				<div class="wide">
					<div class="form-notes">
						<dl>
							<dt><input type="checkbox" name="retired" value="1" <?=($man["status_id"] == 9 ? "checked='checked'" : "") ?> /> Уволен</dt>
						</dl>
					</div>
				</div>
				<?php } else { ?>
				<input type="hidden" name="retired" value="<?=($man["status_id"] == 9 ? 1 : 0) ?>" />
				<?php } ?>	
			</fieldset>
<?php	}
		if($man["people_cat"] == 1 && (!isset($submode) || $submode == 1)) { ?>
			<fieldset id="student_subform">
				<legend>Данные студента</legend>
				<div class="wide">
					<div class="form-notes">
						<dl>
							<dt><label>Группа:</label></dt>
							<dd>
								<select name="group" size="1">
									<option value="0"></option>
<?php 		foreach ($MODULE_DATA["output"]["groups"] as $gr) { ?>
									<option value="<?=$gr["id"]?>"<?=($man["id_group"]==$gr["id"] ? " selected='selected'" : "")?>><?=$gr["name"]?></option>
<?php 		} ?>
								</select>
							</dd>
							<dt><label>Подгруппа:</label></dt>
							<dd>
								<input name="subgroup" type="radio" value="а"<?=(!isset($man["subgroup"]) ? " checked='checked'" : "")?> /> Нет
								<input name="subgroup" type="radio" value="а"<?=($man["subgroup"] == 'а' ? " checked='checked'" : "")?> /> а
								<input name="subgroup" type="radio" value="б"<?=($man["subgroup"] == 'б' ? " checked='checked'" : "")?> /> б
							</dd>
						</dl>
					</div>					
					<div class="form-notes">
						<dl>
							<!--dt><label>Специальность:</label></dt>
							<dd>
								<select name="speciality" size="1" class="select_long">
									<option value="0"></option>
<?php 		foreach ($MODULE_DATA["output"]["specialities"] as $spec) { ?>
									<option value="<?=$spec["id"]?>"<?=($MODULE_DATA["output"]["type_id"]==$spec["id"] ? " selected='selected'" : "")?>><?=$spec["type"]?></option>
<?php 		} ?>
								</select>
							</dd-->
							<dt><label>Год поступления:</label></dt>
							<dd><input name="st_year" type="text" value="<?=isset($man["year"]) ? $man["year"] : "" ?>" /></dd>
						</dl>
					</div>
				</div>
			</fieldset>
<?php	} ?>
			<fieldset>
				<legend>Данные учётной записи</legend>
<?php	$user_group = $MODULE_DATA["output"]["users"][$MODULE_DATA["output"]["cur_user"]]['usergroup_id'];
		/*if(!$MODULE_DATA["output"]["cur_user"]) { ?>				
				<div class="wide">
					<dl>
						<dt><label>Действия с учётной записью пользователя:</label></dt>
						<dd>
							<input type="radio" name="user" value="0" onclick="show_fields(this.value);"<?=($MODULE_DATA["output"]["cur_user"] ? "" : " checked='checked'")?> />Создать новую<br />
							<input type="radio" name="user" value="1" onclick="show_fields(this.value);"<?=($MODULE_DATA["output"]["cur_user"] ? " checked='checked'" : "")?> />Привязать к существующей<br />
							<input type="radio" name="user" value="2" onclick="show_fields(this.value);" />Не создавать
						</dd>
					</dl>
				</div>
				<div class="wide" id="olduser"<?=($MODULE_DATA["output"]["cur_user"] ? " style='display:block;'" : " style='display:none;'")?>>
					<dl>
						<dt><label>Выберите учётную запись:</label></dt>
						<dd>
							<select name="user_select" size="1">
								<option value="0"></option>
<?php		foreach ($MODULE_DATA["output"]["users"] as $user) {
				if($MODULE_DATA["output"]["cur_user"] == $user["id"]) ?>
								<option value="<?=$user["id"]?>"<?=($MODULE_DATA["output"]["cur_user"] == $user["id"] ? " selected='selected'" : "")?>><?=$user["displayed_name"]?></option>
<?php 		} ?>
							</select>
						</dd>
					</dl>
				</div>
<?php 	} else {*/ ?>
				<input type="hidden" name="user_select" value="<?=(isset($MODULE_DATA["output"]["cur_user"]) ? $MODULE_DATA["output"]["cur_user"] : 0)?>" />
				<input type="hidden" name="user" value="0" />
<?php	//} ?>				
				<div class="wide" id="newuser">
					<div class="form-notes">
						<dl>
							<dt><label>Логин:</label></dt>
							<dd><input name="username" type="text" value="<?=($MODULE_DATA["output"]["cur_user"] && isset($man["username"]) ? $man["username"] : "")?>" /></dd>
							<dt><label>E-mail:</label></dt>
							<dd><input name="email" type="text" value="<?=($MODULE_DATA["output"]["cur_user"] && isset($man["email"]) ? $man["email"] : "")?>" /></dd>
						</dl>
					</div>
					<div class="form-notes">
						<dl>
							<dt>
								<label>Пароль:<span id="genpass"></span></label>
								<span id="copypass"><a onclick="copypass('genpass','add_pass','repeatpass');">использовать этот пароль<span id="instead" class="instead"></span></a></span>
							</dt>
							<dd>
								<input name="password1" id="add_pass" type="password"  autocomplete="off" />
								<a onclick="passgen('genpass',3,1,1,'add_pass');">сгенерировать пароль</a>
							</dd>
							<dt><label>Повторите пароль:</label></dt>
							<dd><input name="password2" id="repeatpass" type="password" /></dd>
						</dl>
					</div>
					<div class="wide">
						<dl id="usergroup" >
							<dt><label>Группы пользователей:</label></dt>
							<dd>
								<select name="usergroup" size="1">
									<option value="0"></option>
<?php 	foreach ($MODULE_DATA["output"]["usergroups"] as $st) { ?>
									<option value="<?=$st["id"]?>"<?=($st["id"] == $user_group ? "selected='selected'" : "")?>><?=$st["comment"]?></option>
<?php 	} ?>
								</select>
							</dd>
						</dl>
					</div>
				</div>
				<div class="wide">
					<p>						
						<input type="hidden" value="edit_people" name="mode" />
						<input type="submit" value="Готово" class="button"/>
					</p>
				</div>
			</fieldset>		
		</form>
<?php
	}
	break;
	
	case "add_people": { 
		if ($MODULE_OUTPUT["allow_peopledit"]) { ?>
		<script>jQuery(document).ready(function($) {GlobalPeople();});</script>
<?php 	}
		if(isset($MODULE_OUTPUT["display_variant"]["add_item"])) {
			$display_variant = $MODULE_OUTPUT["display_variant"]["add_item"];
		}
		$submode = $MODULE_DATA["output"]["submode"];
		if(isset($MODULE_OUTPUT["is_exist"])) {
			$exist = $MODULE_OUTPUT["is_exist"];
            //foreach($MODULE_OUTPUT["is_exist"] as $exist) { ?>
                <form method="post" name="is_exist">
					<fieldset>
						<p>
<?php			if(isset($exist["people"]["dep_identic"])) {
					echo "Преподаватель&nbsp;";
					echo $exist["people"]["last_name"]."&nbsp;";
					if(isset($exist["people"]["name"])) echo $exist["people"]["name"]."&nbsp;";
					if(isset($exist["people"]["patronymic"])) echo $exist["people"]["patronymic"];
					if(isset($exist["people"]["dep_identic"])) echo ", приписаный к кафедрам:&nbsp;";
				} else { 
					echo "Пользователь&nbsp;";
					echo $exist["people"]["last_name"]."&nbsp;";
					if(isset($exist["people"]["name"])) echo $exist["people"]["name"]."&nbsp;";
					if(isset($exist["people"]["patronymic"])) echo $exist["people"]["patronymic"];
				}
				if(isset($exist["people"]["dep_identic"])) {
					$dep_str = null;
					foreach($exist["people"]["department"]["old"] as $id => $dep_name) {
						if(!is_null($dep_str)) {
							$dep_str .= ", ".$dep_name;
						} else {
							$dep_str = $dep_name;
						}
					}
					echo $dep_str;
				}
				echo "&nbsp;уже существует."; ?>
						</p>
						<p>
							<input type="hidden" value="<?=$exist["people_cat"]?>" name="people_cat" />
							<input type="hidden" value="<?=$exist["people"]["last_name"]?>" name="last_name" />
							<input type="hidden" value="<?=$exist["people"]["name"]?>" name="first_name" />
							<input type="hidden" value="<?=$exist["people"]["patronymic"]?>" name="patronymic" />
							<input type="hidden" value="<?=$exist["people"]["male"]?>" name="male" />
							<input type="hidden" value="<?=$exist["status"]?>" name="status" />
							<textarea class="hidden" name="comment"><?=$exist["comment"]?></textarea>
<?php			if($exist["people_cat"] == 1) { ?>							
							<input type="hidden" value="<?=$exist["group"]?>" name="group" />
							<input type="hidden" value="<?=$exist["subgroup"]?>" name="subgroup" />
							<input type="hidden" value="<?=$exist["speciality"]?>" name="speciality" />
							<input type="hidden" value="<?=$exist["year"]?>" name="year" />
<?php			} elseif($exist["people_cat"] == 2) {
					/*if(isset($exist["people"]["department"]["old"])) {
						$i = 0;
						foreach($exist["people"]["department"]["old"] as $id => $dep_name) { ?>
							<input type="hidden" value="<?=$id?>" name="department[<?=$i?>]" />
<?php						$i++;
						}
					}*/
					if(isset($exist["people"]["department"]["new"])) {
						foreach($exist["people"]["department"]["new"] as $i => $dep_id) { ?>
							<input type="hidden" value="<?=$dep_id?>" name="department[<?=$i?>]" />
<?php					}
					} ?>
							<input type="hidden" value="<?=$exist["exp_full"]?>" name="exp_full" />
							<input type="hidden" value="<?=$exist["exp_teach"]?>" name="exp_teach" />
							<input type="hidden" value="<?=$exist["prad"]?>" name="prad" />
							<input type="hidden" value="<?=$exist["post"]?>" name="post" />
<?php			} else { ?>
							
<?php			} ?>
							<input type="hidden" value="<?=$exist["user"]?>" name="user" />
<?php			if(isset($exist["people"]["usergroup"])) { ?>
							<input type="hidden" value="<?=$exist["usergroup"]?>" name="usergroup" />
<?php			} ?>
<?php			if(isset($exist["people"]["user_select"])) { ?>
							<input type="hidden" value="<?=$exist["user_select"]?>" name="user_select" />
<?php			} ?>
							<input type="hidden" value="<?=$exist["username"]?>" name="username" />
							<input type="hidden" value="<?=$exist["email"]?>" name="email" />
							<input type="hidden" value="<?=$exist["password1"]?>" name="password1" />
							<input type="hidden" value="<?=$exist["password2"]?>" name="password2" />
							<label>Вы уверены, что нужно добавить еще раз?</label>
							<input type="submit" value="Да" name="check" class="button" />
							<input type="submit" value="Нет" name="check" class="button" />
							<label>&nbsp;&nbsp;Или перейти на <a href="/people/<?=$exist["people"]["id"]?>/">редактирование</a>.</label>							
						</p>
					</fieldset>
                </form>
<?php 		//}
        } ?>
		<form id="people_form" class="people_cat_<?=(isset($submode) ? $submode : "1")?>" method="post" enctype="multipart/form-data" onsubmit="if(document.getElementById('add_pass').value != document.getElementById('repeatpass').value) {alert('Пароль подтверждён неверно'); return false;}">
<?php	if(!isset($submode) || $submode != 2) { ?>		
			<div class="form_title">Добавить человека</div>
<?php	} else { ?>
			<div class="form_title">Добавить преподавателя</div>
<?php	} ?>
			<fieldset>
				<legend>Общая информация</legend>
<?php	if(!isset($submode)) { ?>				
				<div class="wide">
					<div class="form-notes">
						<dl>
							<dt><label>Категория пользователя:</label></dt>
							<dd>
								<select id="people_cat_select" name="people_cat" size="1">
									<option value="1"<?=((!isset($display_variant) || (isset($display_variant) && $display_variant["people_cat"] == 1)) ? " selected='selected'" : "")?>>Студент</option>
									<option value="2"<?=(isset($display_variant) && $display_variant["people_cat"] == 2 ? " selected='selected'" : "")?>>Преподаватель</option>
									<option value="3"<?=(isset($display_variant) && $display_variant["people_cat"] == 3 ? " selected='selected'" : "")?>>Служебная учётная запись</option>
								</select>
							</dd>
						</dl>
					</div>
					<div class="form-notes">
						<dl id="responsible">
							<dt><label>Ответственный:</label></dt>
							<dd>
								<input type="checkbox" name="responsible"<?=(isset($display_variant["responsible"]) ? " checked='checked'" : "")?> />
							</dd>
						</dl>
					</div>
				</div>
<?php	} else { ?>
				<input type="hidden" name="people_cat" value="<?=$submode?>" />
<?php	} ?>				
				<div class="wide">
					<div class="form-notes">
						<dl>
							<dt><label>Фамилия:</label></dt>
							<dd><input name="last_name" type="text" class="input_long" value="<?=(isset($display_variant) ? $display_variant["last_name"] : "")?>" /></dd>
							<dt><label>Имя:</label></dt>
							<dd><input name="first_name" type="text" class="input_long" value="<?=(isset($display_variant) ? $display_variant["first_name"] : "")?>" /></dd>
							<dt><label>Отчество:</label></dt>
							<dd><input name="patronymic" type="text" class="input_long" value="<?=(isset($display_variant) ? $display_variant["patronymic"] : "")?>" /></dd>
						</dl>						
					</div>
					<div class="form-notes">
						<dl>
							<dt><label>Фотография:</label></dt>
							<dd>
								<div class="user_photo float-right"><img src="/images/people/no_photo.jpg" alt="" /></div>
								<span id="photoinput"><input name="user_photo" type="file" id="user_photo" /></span>
							</dd>
							<dt><label>Пол:</label></dt>
							<dd><input type="radio" name="male" value="1"<?=(((isset($display_variant) && $display_variant["male"] == 1) || !isset($display_variant)) ? " checked='checked'" : "")?> /> М/Ж <input type="radio" name="male" value="0"<?=(isset($display_variant) && $display_variant["male"] == 0 ? " checked='checked'" : "")?> /></dd>
							<dt class="add_status"><label>Статус:</label></dt>
							<dd class="add_status">
								<ul>
<?php 	$i = 0;
		foreach ($MODULE_DATA["output"]["statuses"] as $st) {			
			if(!isset($submode) || (isset($submode) && $submode == $st["people_cat"])) { ?>
									<li class="people_cat_<?=$st["people_cat"]?><?=(isset($display_variant) && $display_variant["status"] == $st["id"] ? " selected" : "")?>">
										<input type="radio" name="status" value="<?=$st["id"]?>"<?=(!isset($display_variant) && $i == 0 ? " checked='checked'" : "")?> /><?=$st["name"]?>
									</li>
<?php 			$i++;
			}
		} ?>
								</ul>
							</dd>
						</dl>
					</div>
				</div>
				<div class="wide">
					<dl>
						<dt><label>Комментарий:</label></dt>
						<dd><textarea name="comment" class="wysiwyg" cols="40" rows="7" wrap="virtual"><?=(isset($display_variant) ? $display_variant["comment"] : "")?></textarea></dd>
					</dl>
				</div>
			</fieldset>
<?php	if(!isset($submode) || $submode == 2) { ?>		
			<fieldset id="teacher_subform">
				<legend>Данные преподавателя</legend>
				<div class="wide">
					<div class="form-notes">
						<dl>
							<dt><label>Должность, степень, звание:</label></dt>
							<dd><input type="text" name="prad" size="35" value="<?=(isset($display_variant) ? $display_variant["prad"] : "")?>" /></dd>
							<dt><label>Должность:</label></dt>
							<dd>
								<select name="post" size="1" class="select_long">
									<option value="Заведующий"<?=(isset($display_variant) && $display_variant["post"] == "Заведующий" ? " selected='selected'" : "")?>>Заведующий</option>
									<option value="Преподаватель"<?=((!isset($display_variant) || (isset($display_variant) && $display_variant["post"] == "Преподаватель")) ? " selected='selected'" : "")?>>Преподаватель</option>
									<option value="Лаборант"<?=(isset($display_variant) && $display_variant["post"] == "Лаборант" ? " selected='selected'" : "")?>>Лаборант, Старший Лаборант, Врач, Аспирант</option>
								</select>
							</dd>
						</dl>
					</div>					
					<div class="form-notes">
						<dl>
							<dt><label>Год начала общего стажа:</label></dt>
							<dd><input type="text" name="exp_full" value="<?=(isset($display_variant) ? $display_variant["exp_full"] : "")?>" /></dd>
							<dt><label>Год начала педагогического стажа:</label></dt>
							<dd><input type="text" name="exp_teach" value="<?=(isset($display_variant) ? $display_variant["exp_teach"] : "")?>" /></dd>
						</dl>
					</div>
				</div>
				<div class="wide">
					<div class="form-notes">
<?php		if(!$MODULE_OUTPUT["current_department_id"]) { ?>
						<dl>
							<dt><label>Выберите кафедру:</label></dt>
							<dd>
								<ul id="department_select" class="select_long">
<?php     		foreach($MODULE_OUTPUT["faculties"] as $faculties) { ?>
									<li>
										<dl>
											<dt><?=$faculties["fac_name"]?></dt>
<?php				foreach($faculties["departments"] as $department) { ?>
											<dd id="depid_<?=$department["id"]?>"<?=(isset($display_variant["department"][$department["id"]]) ? " class='hidden'" : "")?> title="<?=$department["name"]?>"><?=$department["name"]?></dd>
<?php				} ?>
										</dl>
									</li>
<?php			} ?>
								</ul>
							</dd>
<?php		} ?>
						</dl>
					</div>					
					<div class="form-notes">
<?php		if(!$MODULE_OUTPUT["current_department_id"]) { ?>
						<dl>
							<dt><label>Преподаватель будет добавлен на кафедры:</label></dt>
							<dd>
								<ul id="department_list">
<?php			if(isset($display_variant)) {
					foreach($display_variant["department"] as $dep_id => $dep) { ?>									
									<li><?=$dep?><input type="hidden" value="<?=$dep_id?>" name="department[<?=$dep_id?>]" /><em class="inline-block"></em></li>
<?php				}
				} ?>
								</ul>
							</dd>
						</dl>
<?php		} else { ?>
						<dl>
							<dt><label>Преподаватель будет добавлен на кафедру <?=$MODULE_OUTPUT["current_department_name"]?></label></dt>
							<dd>
								<input type="hidden" name="department[0]" value="<?=$MODULE_OUTPUT["current_department_id"]?>">
							</dd>
						</dl>
<?php		} ?>
					</div>
				</div>
				<div class="wide">
					<div class="form-notes">
						<dl>
							<dt><label>Кураторская работа:</label><input name="curator_selector" id="curator_selector" type="checkbox" <?=$display_variant["is_curator"] ? 'checked="checked" ' : ''?> /></dt>
							<dd>
								<textarea name="curator_text" id="curator_text" cols="60" rows="15" class="wysiwyg"><?=$display_variant["curator_text"]?></textarea>
							</dd>
						</dl>	
					</div>
					<div class="form-notes" id="curator_groups_cont" >
						<dl>
							<dt><label>Группы куратора:</label></dt>
							<dd>
								<select id="curator_groups" size="8" multiple="true" >
									<options>
										<?php	
										if(isset($display_variant)) {
											foreach($display_variant["department"] as $dep_id => $dep) { ?>									
										<optgroup id="<?=$dep_id?>" label="<?=$dep?>">
											<?php if (!empty($display_variant["curator_groups"][$dep_id]))
											 		foreach ($display_variant["curator_groups"][$dep_id] as $group) { ?>
												<option value="<?=$group['id']?>"><?=$group['name']?></option> 
												<? }
												else { ?>
												<option></option>
											<?php } ?>
										</optgroup>
										<?php }	
										} ?>
									</options>
								</select>
								<div id="curator_groups_selector" > >> </div>
							</dd>
						</dl>	
						<dl>
							<dt><label>Список групп:</label></dt>
							<dd>
								<div id="all_groups_selector" style="display: inline-block; vertical-align: top; font-weight:bold; cursor: pointer; text-decoration: underline;"> << </div>
								<select id="all_groups" size="16" multiple="true" name="curator_groups[]" >
									<options>
										<?php foreach ($MODULE_OUTPUT["groups"] as $group) { ?>
											<option value="<?=$group['id']?>"><?=$group['name']?></option> 
										<? } ?>
									</options>
								</select>
							</dd>						
						</dl>
					</div>

				</div>
			</fieldset>
<?php	}
		if(!isset($submode) || $submode == 1) { ?>
			<fieldset id="student_subform">
				<legend>Данные студента</legend>
				<div class="wide">
					<div class="form-notes">
						<dl>
							<dt><label>Группа:</label></dt>
							<dd>								
								<select name="group" size="1">
									<option value="0"></option>
<?php 	foreach ($MODULE_DATA["output"]["groups"] as $gr) { ?>
									<option value="<?=$gr["id"]?>"<?=(isset($display_variant) && $display_variant["group"] == $gr["id"] ? " selected='selected'" : "")?>><?=$gr["name"]?></option>
<?php 	} ?>
								</select>
							</dd>
							<dt><label>Подгруппа:</label></dt>
							<dd>
								<input name="subgroup" type="radio" value="0"<?=((!isset($display_variant) || (isset($display_variant) && $display_variant["subgroup"] == 0)) ? " checked='checked'" : "")?> /> Нет
								<input name="subgroup" type="radio" value="а"<?=(isset($display_variant) && $display_variant["subgroup"] == "а" ? " checked='checked'" : "")?> /> а
								<input name="subgroup" type="radio" value="б"<?=(isset($display_variant) && $display_variant["subgroup"] == "б" ? " checked='checked'" : "")?> /> б
							</dd>
						</dl>
					</div>					
					<div class="form-notes">
						<dl>
							<!--dt><label>Специальность:</label></dt>
							<dd>
								<select name="speciality" size="1" class="select_long">
									<option value="0"></option>
<?php 	foreach ($MODULE_DATA["output"]["specialities"] as $spec) { ?>
									<option value="<?=$spec["id"]?>"<?=(isset($display_variant) && $display_variant["speciality"] == $spec["id"] ? " selected='selected'" : "")?>><?=$spec["type"]?></option>
<?php 	} ?>
								</select>
							</dd-->
							<dt><label>Год поступления:</label></dt>
							<dd><input name="st_year" type="text" value="<?=(isset($display_variant) ? $display_variant["st_year"] : "")?>" /></dd>
						</dl>
					</div>
				</div>
			</fieldset>
<?php	} ?>			
			<fieldset>
				<legend>Данные учётной записи</legend>
<?php	/*if(!isset($submode) || $submode != 2) { ?>	
				<div class="wide">
					<dl>
						<dt><label>Действия с учётной записью пользователя:</label></dt>
						<dd>
							<input type="radio" name="user" value="0" onclick="show_fields(this.value);"<?=((!isset($display_variant) || (isset($display_variant) && $display_variant["user"] == 0)) ? " checked='checked'" : "")?> />Создать новую<br />
							<input type="radio" name="user" value="1" onclick="show_fields(this.value);"<?=(isset($display_variant) && $display_variant["user"] == 1 ? " checked='checked'" : "")?> />Привязать к существующей<br />
							<input type="radio" name="user" value="2" onclick="show_fields(this.value);"<?=(isset($display_variant) && $display_variant["user"] == 2 ? " checked='checked'" : "")?> />Не создавать
						</dd>
					</dl>
				</div>
				<div class="wide" id="olduser">
					<dl>
						<dt><label>Выберите учётную запись:</label></dt>
						<dd>
							<select name="user_select" size="1">
								<option value="0"></option>
<?php 		foreach ($MODULE_DATA["output"]["users"] as $user) { ?>
								<option value="<?=$user["id"]?>"<?=(isset($display_variant) && $display_variant["user"] == 1 && $display_variant["user_select"] == $user["id"] ? " selected='selected'" : "")?>><?=$user["displayed_name"]?></option>
<?php 		} ?>
							</select>
						</dd>
					</dl>
				</div>
<?php	} else {*/ ?>
				<input type="hidden" name="user" value="0"/>
<?php	//} ?>
				<div class="wide" id="newuser">
					<div class="form-notes">
						<dl>
							<dt><label>Логин:</label></dt>
							<dd><input name="username" type="text" value="<?=(isset($display_variant) ? $display_variant["username"] : "")?>" /></dd>
							<dt><label>E-mail:</label></dt>
							<dd><input name="email" type="text" value="<?=(isset($display_variant) ? $display_variant["email"] : "")?>" /></dd>
						</dl>
					</div>
					<div class="form-notes">
						<dl>
							<dt>
								<label>Пароль:<span id="genpass"></span></label>
								<span id="copypass"><a onclick="copypass('genpass','add_pass','repeatpass');">использовать этот пароль<span id="instead" class="instead"></span></a></span>
							</dt>
							<dd>
								<input name="password1" id="add_pass" type="password"  autocomplete="off" />
								<a onclick="passgen('genpass',3,1,1,'add_pass');">сгенерировать пароль</a>
							</dd>
							<dt><label>Повторите пароль:</label></dt>
							<dd><input name="password2" id="repeatpass" type="password" /></dd>
						</dl>
					</div>
					<div class="wide">
						<dl id="usergroup" class="hidden">
							<dt><label>Группы пользователей:</label></dt>
							<dd>
								<select name="usergroup" size="1">
									<option value="0"></option>
<?php 	foreach ($MODULE_DATA["output"]["usergroups"] as $st) { ?>
									<option value="<?=$st["id"]?>"<?=(isset($display_variant) && $display_variant["usergroup"] == $st["id"] && ($display_variant["people_cat"] == 3 || ($display_variant["people_cat"] == 2 && isset($display_variant["responsible"]))) ? " selected='selected'" : "")?>><?=$st["comment"]?></option>
<?php 	} ?>
								</select>
							</dd>
						</dl>
					</div>
				</div>
				<div class="wide">
					<p>
						<input type="hidden" value="add_people" name="mode" />
						<input type="submit" value="Готово" class="button"/>
					</p>
				</div>
			</fieldset>		
		</form>
<?php
	}
	break;
		
	case "prof_choice": { ?>
		<script src="/scripts/rekom.js"></script>
		<div id="education" style="display:block;"><p>
		<h3>Ваше образование</h3> <span style="color:#555;"><small>укажите Ваш текущий уровень образования</small></span><br />
		<a class="dash" id="link1" onclick="show_choice(this, 'show_cont_sec');">Высшее</a> <br />
		<a class="dash" id="link2" onclick="show_choice(this, 'show_pre_level');">Средне-специальное</a> <br />
		<a class="dash" id="link3" onclick="show_choice(this, 'show_pre_level');">Среднее</a> <br />
		</p></div>
		<div id="pre_level" style="display:none;"><p>
		<h3>Желаемый уровень подготовки</h3>
		<span style="color:#555;"><small>какую степень Вы хотите иметь по окончании обучения?</small></span><br />
		<a class="dash" id="link4" onclick="show_choice(this, 'show_ege_int');">Бакалавр</a> <br />
		<a class="dash" id="link5" onclick="show_choice(this, 'show_ege_int');">Специалист</a> <br />
		</p></div>
		<div id="cont_sec" style="display:none;"><p>
		<h3>Вы хотите:</h3>
		<span style="color:#555;"><small>с какой ступени Вы хотите начать обучение?</small></span><br />
		<a class="dash" id="link6" onclick="show_choice(this, 'show_magistr');">Продолжить образование (магистратура)</a> <br />
		<a class="dash" id="link7" onclick="show_choice(this, 'show_pre_level_second');">Получить второе образование</a> <br />
		</p></div>
		<div id="pre_level_second" style="display:none;"><p>
		<h3>Желаемый уровень подготовки</h3>
		<span style="color:#555;"><small>какую степень Вы хотите иметь по окончании обучения?</small></span><br />
		<a class="dash" id="link8" onclick="show_choice(this, 'show_ege_int', 1);">Бакалавр</a> <br />
		<a class="dash" id="link9" onclick="show_choice(this, 'show_ege_int', 1);">Специалист</a> <br />
		</p></div>
		<div id="magistr" style="display:none;"><p>
		<h3>Направления магистратуры:</h3>
		<ul>
		<?
		foreach ($MODULE_OUTPUT["mag_spec"] as $mag) {
			?><li><a href="/speciality/mag/<?=$mag["code"]?>/"><?=$mag["name"]?></a><?
		}
		?>
		</ul>
		</p></div>
		<div id="ege_int" style="display:none;"><p>
		<h3>Выбор:</h3>
		<a class="dash" id="link10" onclick="show_choice(this, 'show_ege');">по результатам ЕГЭ</a> <br />
		<a class="dash" id="link11" onclick="show_choice(this, 'show_interests');">по интересам</a> <br />
		</div></p>
		<p><div id="ege" style="display:none;">
		<h3>Укажите Ваши баллы по ЕГЭ:</h3>
		<form action="rekom/" method="post">
		<table cols="2" border="0">
		<tr><td><input type="checkbox" name="ege_rus_on" id="ege_rus_on" checked onclick="return false;"> Русский язык:</td><td><input type="text" size="3" name="ege_rus" id="ege_rus"></td></tr>
		<tr><td><input type="checkbox" name="ege_math_on" id="ege_math_on" checked onclick="return false;"> Математика:</td><td><input type="text" size="3" name="ege_math" id="ege_math"></td></tr>
		<tr><td><input type="checkbox" name="ege_inf_on" id="ege_inf_on" onclick="if(this.checked) document.getElementById('ege_inf').removeAttribute('disabled'); else document.getElementById('ege_inf').setAttribute('disabled', 1);"> Информатика:</td><td><input type="text" size="3" name="ege_inf" id="ege_inf" disabled></td></tr>
		<tr><td><input type="checkbox" name="ege_hist_on" id="ege_hist_on" onclick="if(this.checked) document.getElementById('ege_hist').removeAttribute('disabled'); else document.getElementById('ege_hist').setAttribute('disabled', 1);"> История:</td><td><input type="text" size="3" name="ege_hist" id="ege_hist" disabled></td></tr>
		<tr><td><input type="checkbox" name="ege_phys_on" id="ege_phys_on" onclick="if(this.checked) document.getElementById('ege_phys').removeAttribute('disabled'); else document.getElementById('ege_phys').setAttribute('disabled', 1);"> Физика:</td><td><input type="text" size="3" name="ege_phys" id="ege_phys" disabled></td></tr>
		<tr><td><input type="checkbox" name="ege_biol_on" id="ege_biol_on" onclick="if(this.checked) document.getElementById('ege_biol').removeAttribute('disabled'); else document.getElementById('ege_biol').setAttribute('disabled', 1);"> Биология:</td><td><input type="text" size="3" name="ege_biol" id="ege_biol" disabled></td></tr>
		<!--<tr><td><input type="checkbox" name="ege_chem_on" id="ege_chem_on" onclick="if(this.checked) document.getElementById('ege_chem').removeAttribute('disabled'); else document.getElementById('ege_chem').setAttribute('disabled', 1);"> Химия:</td><td><input type="text" size="3" name="ege_chem" id="ege_chem" disabled></td></tr>
		<tr><td><input type="checkbox" name="ege_geo_on" id="ege_geo_on" onclick="if(this.checked) document.getElementById('ege_geo').removeAttribute('disabled'); else document.getElementById('ege_geo').setAttribute('disabled', 1);"> География:</td><td><input type="text" size="3" name="ege_geo" id="ege_geo" disabled></td></tr>-->
		<tr><td><input type="checkbox" name="ege_lang_on" id="ege_lang_on" onclick="if(this.checked) document.getElementById('ege_lang').removeAttribute('disabled'); else document.getElementById('ege_lang').setAttribute('disabled', 1);"> Иностранный язык:</td><td><input type="text" size="3" name="ege_lang" id="ege_lang" disabled></td></tr>
		<tr><td><input type="checkbox" name="ege_soc_on" id="ege_soc_on" onclick="if(this.checked) document.getElementById('ege_soc').removeAttribute('disabled'); else document.getElementById('ege_soc').setAttribute('disabled', 1);"> Обществознание:</td><td><input type="text" size="3" name="ege_soc" id="ege_soc" disabled></td></tr>
		</table><br />
		<input type="button" value="Отправить" onclick="document.getElementById('finalrek').style.display = 'block'; process();">
		
		<div id="finalrek" style="display:none;">
		<div id="divrekoms"></div>
		</div>
		</form>
		</p></div>
		<div id="interests" style="display:none;"><p>
		<h3>Укрупнённые группы:</h3>
		<ul>
		<?
		foreach ($MODULE_OUTPUT["schools"] as $sch) {
			?><li><a class="dash sch" onclick="show_choice(this, '<?=$sch["code"]?>')"><?=$sch["name"]?></a></li><?
		}
		?>
		</ul>
		</p></div>
		<div id="rekom">
		<?
		foreach ($MODULE_OUTPUT["high_spec"] as $key => $speclist) {
			?><div name="rek" id="<?=$key?>" style="display:none;"><h3>Направления:</h3>
		<? foreach ($speclist as $spec) { ?>
			<a href="/speciality/<?=$spec["code"]?>/"><?=$spec["name"]?></a><br />
		<? } ?></div><? }
		?></div><div id="flagdiv"></div><?
	}
	break;

    case "search_subjects": { ?>
		<div id="search_subjects">
		    <form action="/subjects/" method="get">
				<fieldset>
					<legend>Дисциплины</legend>
					<p>
						Дисциплина:
						<input name="subject_name" id="subject_name" placeholder="Пожалуйста, введите название дисциплины" title="Пожалуйста, введите название дисциплины" type="text" size="43" />
						<input type="submit" value="Найти" class="button" onclick="if(document.getElementById('subject_name').value=='') { document.getElementById('btc').appendChild(document.getElementById('subject_name').tooltip); return false; }"/> <a href="/library/ebooks/e-lib-sys-nsau/poisk-resursov-ebs/" target="_blank">Расширенная форма</a>
					</p>
				</fieldset>
			</form>
		</div>
<?php
	}
	break;
		
	case "rekom": {
		header('Content-Type: text/xml; charset=windows-1251');
		echo '<?xml version="1.0" encoding="cp1251" standalone="yes"?>';
		echo '<response>';
		foreach ($MODULE_OUTPUT["rekoms"] as $key => $rekom) {
			$rekom = explode(" - ", $rekom);
			echo '<rekom>&lt;a href="/speciality/'.$rekom[0].'/"&gt;'.$rekom[1].'&lt;/a&gt;</rekom>';
		}
		echo '</response>';
	}
	break;
		
    case "subjects": {
        if(!empty($MODULE_OUTPUT["subject"]))
        {            
        	$man = $MODULE_OUTPUT["subject"];
			?>
			<h2><?php echo $MODULE_OUTPUT["subject"]["name"] ?></h2><br>
			<?php
			if (isset($MODULE_OUTPUT["subject"]["files"]))
			{
				?><ul><?php
				foreach ($MODULE_OUTPUT["subject"]["files"] as $file)
				{
				?>
				<li><a href="/file/<?php echo $file["id"]; ?>/" title="<?php echo $file["descr"]; ?>"><?php echo (isset($file["descr"]) && $file["descr"] ) ? $file["descr"] : $file["name"]; ?></a> <?php if($file["user_id"] && 0) { ?><a href="/people/<?php echo $file["user_id"]; ?>/"><?php } ?><?php if (0) echo $file["user_name"]; ?><?php if($file["user_id"] && 0) { ?></a><?php } ?> 
				<?php if ($file["author"]) echo '&nbsp;Автор: <strong>' .$file['author']. '</strong>.'; if ($file["year"]) echo '&nbsp;Год издания: <strong>' .$file['year']. '</strong>';?>
				<?php if ($file["volume"]) echo '&nbsp;Объем: <strong>' .$file['volume']. '</strong> стр.'; if ($file["edition"]) echo '&nbsp;Тираж: <strong>' .$file['edition']. '</strong> экз.';?>
				<?php if ($file["place"]) echo '&nbsp;Место издания: <strong>' .$file['place'].'</strong>';?>
				<?php
				}
			?>
			</ul>
			<?php
			}
        }
        else
        {
            ?>
            <form action="/subjects/" method="get">        
            <p>
            Дисциплина:
            <input name="subject_name" type="text" size="35" value="<?php echo isset($_GET["name"]) ? $_GET["name"] : "" ?>"/> <input type="submit" value="Найти" class="button"/>
            </p>        
            </form>
            <?
            if(!empty($MODULE_OUTPUT["subjects"]))
            {
                ?>
                <ul>
                <?php
                foreach($MODULE_OUTPUT["subjects"] as $subj)
                {
                    ?>
                    <li><?php if($subj["num_files"] > 0) { ?><a href="/subjects/<?=$subj["id"]?>/"><?php } ?><?=$subj["name"]?> <?php if($subj["num_files"] > 0) { ?></a><?php } ?></li>
                    <?php
                }
                ?>
                </ul>
                <?php
            }
            else
            {
                if(isset($_GET["name"]))
                {
                ?>
                <p>Не найдено дисциплин с такими параметрами поиска.</p>
                <?php
                }
                else
                {
                ?>
                <p>Введите в поле поиска данные о дисциплине.</p>
                <?php              
                }
            };
        };
	}
    break;

    case "search_group": { ?>
		<div id="search_group">
			<form action="/student/timetable/groups/" method="get">
				<fieldset>
					<legend>Группы</legend>
					<p>
						Группа:
						<input name="group" placeholder="Пожалуйста, введите название группы" title="Пожалуйста, введите название группы" id="group_name" type="text" size="43" />
						<input type="submit" value="Найти" class="button" onclick="if(document.getElementById('group_name').value=='') { document.getElementById('btc').appendChild(document.getElementById('group_name').tooltip); Locate; return false; }"/>
					</p>
				</fildset>
			</form>
		</div> 
<?php
	}
	break;

    case "groups": { ?>
		<script>jQuery(document).ready(function($) {InputCalendar();});</script>
<?php   $days = array("понедельник", "вторник", "среда", "четверг", "пятница", "суббота");
        $timepars = array("09:00 - 10:20","10:30 - 11:50","12:30 - 13:50","14:00 - 15:20","15:30 - 16:50","17:00 - 18:20","18:30 - 19:50");
        $months = array("явнаря", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября","декабря");
        if(isset($MODULE_OUTPUT["edit_timetable"])) {
            $data = $MODULE_OUTPUT["edit_timetable"]; ?>
			<div class="EEngine EE_add_timetable">
				<form method="post">
					<fieldset>
						<legend>Редактирование записи:</legend>
						<div class="wide">
							<input type="hidden" name="id" value="<?=$data["id"]?>" />
							<div class="notes_column">
								<div class="form-notes title-notes">
									<label>День недели:</label>
								</div>
								<div class="center-notes">
									<div class="cn">
										<div class="form-notes">
											<select name="dow">
<?php       for($i=0; $i<6; $i++) {
                if($data["dow"] == $i+1) { ?>
												<option value="<?=$i+1?>" selected="selected"><?=$days[$i]?></option>
<?php			} else { ?>
												<option value="<?=$i+1?>"><?=$days[$i]?></option>
<?php			}
            } ?>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="notes_column">
								<div class="form-notes title-notes">
									<label>Номер пары:</label>
								</div>
								<div class="center-notes">
									<div class="cn">
										<div class="form-notes">
											<select name="num">
												<option value="1" selected="selected">1</option>
<?php 		for($i=1; $i<8; $i++) {
                if($data["num"] == $i) { ?>
												<option value="<?=$i?>" selected="selected"><?=$i?></option>
<?php 			} else { ?>
												<option value="<?=$i?>"><?=$i?></option>
<?php     		}
            } ?>
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="wide">
							<div class="notes_column">
								<div class="wide">
									<label>Четная/нечетная или каждую неделю:</label>
								</div>
								<div class="wide">
									<input type="radio" name="parity" <?=($data["parity"] == "both" ? 'checked="checked" ' : '')?>value="0" />Каждую неделю
								</div>
								<div class="wide">
									<input type="radio" name="parity" <?=($data["parity"] == "odd" ? 'checked="checked" ' : '')?>value="2" />Четная
								</div>
								<div class="wide">
									<input type="radio" name="parity" <?=($data["parity"] == "even" ? 'checked="checked" ' : '')?>value="1" />Нечетная
								</div>
							</div>
							<div class="notes_column">
								<div class="wide">
									<label>Номер подгруппы, если есть</label>
								</div>
								<div class="wide">
									<input type="radio" name="subgroup" <?=(empty($data["subgroup"]) ? 'checked="checked" ' : '')?>value="0" />Нет
								</div>
								<div class="wide">
									<input type="radio" name="subgroup" <?=(!empty($data["subgroup"]) && $data["subgroup"] == "а" ? 'checked="checked" ' : '')?>value="а" />а
								</div>
								<div class="wide">
									<input type="radio" name="subgroup" <?=(!empty($data["subgroup"]) && $data["subgroup"] == "б" ? 'checked="checked" ' : '')?>value="б" />б
								</div>
							</div>
						</div>
						<div class="wide">
							<div class="notes_column">
								<div class="wide">
									<label>Дисциплина:</label>
								</div>
								<div class="wide">
									<select name="subject">
<?php		foreach($MODULE_OUTPUT["dep_subjects"] as $dep_subj) { ?>
										<optgroup label="<?=$dep_subj['dep_name']?>">
<?php			foreach($dep_subj['subjects'] as $subject) { ?>
											<option value="<?=$subject["id"]?>"<?=($subject["id"] == $data["subject_id"] ? " selected='selected'" : "")?>><?=$subject["name"]?></option>
<?php			} ?>
										</optgroup>
<?php		} ?>
									</select>
								</div>
							</div>
							<div class="notes_column">
								<div class="wide">
									<label>Аудитория:</label>
								</div>
								<div class="wide">
									<select name="auditorium">
<?php 		$id=0;
            foreach($MODULE_OUTPUT["auditorium"] as $auditorium) {
                if(!$id) {
                    $id++; ?>
										<optgroup label="<?=$auditorium["building_name"]?>">
<?php       	}
                if($id != $auditorium["building_id"]) { ?>
										</optgroup>
										<optgroup label="<?=$auditorium["building_name"]?>">
<?php 				$id++;
                }
                if($data["auditorium_id"] == $auditorium["id"]) { ?>
											<option selected="selected" value="<?=$auditorium["id"]?>"><?=$auditorium["name"]?></option>
<?php
                } else { ?>
											<option value="<?=$auditorium["id"]?>"><?=$auditorium["name"]?></option>
<?php 			}
            } ?>
										</optgroup>
									</select>
								</div>
							</div>
						</div>
						<div class="wide">
							<div class="form-notes">
								<label>Тип занятия:</label>&nbsp;
								<input type="radio" name="lesson_view" <?=($data["lesson_view"] == "1" ? 'checked="checked" ' : '')?>value="1" />Лекция&nbsp;&nbsp;
								<input type="radio" name="lesson_view" <?=($data["lesson_view"] == "0" ? 'checked="checked" ' : '')?>value="0" />Практика
							</div>
						</div>
						<div class="wide">
							<input type="submit" value="Сохранить" class="button" /> 
						</div>
					</fieldset>
				</form>
			</div>
<?php    
        } elseif(!empty($MODULE_OUTPUT["group"])) {
            $group = $MODULE_OUTPUT["group"];
            $days = array("понедельник", "вторник", "среда", "четверг", "пятница", "суббота");
            $timepars = array("09:00 - 10:20","10:30 - 11:50","12:30 - 13:50","14:00 - 15:20","15:30 - 16:50","17:00 - 18:20","18:30 - 19:50");
            $months = array("явнаря", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября","декабря");
            $week = ($MODULE_OUTPUT["parity"] == "even") ? "Чётная" : "Нечётная";
            $now = $MODULE_OUTPUT["now"];
            $url_parity = "";
            $url_subgroup = "";
            if(isset($_GET["subgr"]))
                $url_subgroup = "subgr=".$_GET["subgr"]."&amp;";
            if(isset($_GET["parity"]))
                $url_parity = "parity=".$_GET["parity"]."&amp;"; ?>
				<div id="group_timetable" class="center_cont">
					<div class="cn">
						<div id="group_timetable_title">
							<h1><?=$group["faculty"]["name"]?></h1>
							<h2>
								Группа <?=$group["name"]?>								
<?php		if(!isset($_GET["subgr"]) || $_GET["subgr"] == "all") { ?>
								<a href='?<?=$url_parity?>subgr=a'>а</a><a href='?<?=$url_parity?>subgr=b'>б</a>
<?php       } else {
                if(isset($_GET["subgr"]) && $_GET["subgr"] == "a") { ?>
								<span>а</span><a href='?<?=$url_parity?>subgr=b'>б</a><a href='?<?=$url_parity?>subgr=all'>Все</a>
<?php           }
                if(isset($_GET["subgr"]) && $_GET["subgr"] == "b") { ?>
								<a href='?<?=$url_parity?>subgr=a'>а</a><span>б</span><a href='?<?=$url_parity?>subgr=all'>Все</a>
<?php	        }
            } ?>
							</h2>
						</div>				
						<h2>Расписание учебных занятий
<?php			if(!isset($_GET["parity"]) || $_GET["parity"] != "both") { ?>
							&nbsp;&nbsp;(<a href='http://nsau.edu.ru/student/timetable/'>Всё расписание</a>)
<?php			} else { ?>
							&nbsp;&nbsp;(<a href='?<?=$url_subgroup?>parity=<?=$MODULE_OUTPUT["parity"]?>'><?=$week?> неделя</a>)
<?php			} ?>					
						</h2>
<?php			$j = 0;
				$ij = 0;
                $timetable = $MODULE_OUTPUT["timetable"]; ?>						
						<!--div id="timetable_div">
							<div class="timetable_two_column timetable_head">
								<table>
									<thead>
										<tr>
<?php			/*if(count($timetable) > 1) { ?>
											<td>
												<div class="timetable_parity">
													Чётная
												</div>
											</td>
											<th></th>
											<td>
												<div class="timetable_parity">
													Нечётная
												</div>
											</td>
<?php			} else { ?>
											<td colspan="3">
												<div class="timetable_parity">
													<?=$week?>
												</div>
											</td>
<?php			} */?>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>
												<div class="lesson_num">
													№
												</div>
												<div class="subject">
													<div class="subject_cn">Дисциплина</div>
												</div>
												<div class="auditorium">
													Аудитория
												</div>
												<div class="subgroup">
													ПГ
												</div>
											</td>
											<th></th>
											<td>
												<div class="lesson_num">
													№
												</div>
												<div class="subject">
													<div class="subject_cn">Дисциплина</div>
												</div>
												<div class="auditorium">
													Аудитория
												</div>
												<div class="subgroup">
													ПГ
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
<?php			/*echo "<pre>";
				print_r($timetable);
				echo "</pre>";	*/			
				/*if(count($timetable) > 1)
					$ij = 6;
				else
					$ij = 3;
				for($i = 0; $i < $ij;) { */?>
							<div class="timetable_two_column">
								<table>
									<thead>
										<tr>
<?php				/*if(count($timetable) > 1) { ?>
											<td<?=(($i+1 == $now["wday"]) ? " class=\"current_day\"" : "")?>>
												<div class="week_day">
													<?=$days[$i]?>
												</div>
											</td>
											<th></th>
											<td<?=(($i+1 == $now["wday"]) ? " class=\"current_day\"" : "")?>>
												<div class="week_day">
													<?=$days[$i]?>
												</div>
											</td>	
<?php				} else { ?>
											<td<?=(($i+1 == $now["wday"]) ? " class=\"current_day\"" : "")?>>
												<div class="week_day">
													<?=$days[$i]?>
												</div>
											</td>
											<th></th>
											<td<?=(($i+4 == $now["wday"]) ? " class=\"current_day\"" : "")?>>
												<div class="week_day">
													<?=$days[$i+3]?>
												</div>
											</td>	
<?php				} */?>																	
										</tr>
									</thead>
									<tbody>
<?php				/*$k = 0;
					do { 
						$f = true; ?>
										<tr>
<?php					foreach($timetable as $week_list) {
							if(count($timetable) > 1) {
								if(isset($week_list[$i+1][$k+1])) { ?>
									<td<?=(($i+1 == $now["wday"]) ? " class=\"current_day\"" : "")?>>
										<div class="lesson_num">
											<?=($k+1)?>
										</div>
<?php								if(count($week_list[$i+1][$k+1]) > 1) { */?>
										<div class="timetable_row">
<?php								/*}
									foreach($week_list[$i+1][$k+1] as $lesson) {
										if(count($week_list[$i+1][$k+1]) > 1) { ?>
											<div class="timetable_row">
<?php									} ?>
												<div class="subject">
													<div class="subject_cn<?=($lesson['lesson_view'] == 1 ? " lecture" : "")?>">
<?php											if($MODULE_OUTPUT["edit_acces"]) { ?>
														<a href="delete/<?=$lesson["id"]?>/" onclick="return confirm('Вы уверены, что хотите удалить выбранный элемент?');">
															<img src="/themes/images/delete.png" alt="" />
														</a>
														<a href="edit/<?=$lesson["id"]?>/"><?=$lesson["subject"]?></a>
<?php    										} else { ?>
														<?=$lesson["subject"]?>
<?php											} ?>
													</div>
												</div>
												<div class="auditorium">
													<span title="<?=$lesson["building"]?>">
														<?=$lesson["label"]?>
													</span> - <?=$lesson["auditorium"]?>
												</div>
												<div class="subgroup">
													<?=$lesson["subgroup"]?>
												</div>
<?php									if(count($week_list[$i+1][$k+1]) > 1) { ?>
											</div>
<?php									} 
									}
									if(count($week_list[$i+1][$k+1]) > 1) { ?>
										</div>
<?php								} ?>
									</td>
<?php							} else { ?>
									<td<?=(($i+1 == $now["wday"]) ? " class=\"current_day\"" : "")?>>
										<div class="lesson_num">
											<?=($k+1)?>
										</div>
										<div class="subject"></div>
										<div class="auditorium"></div>
										<div class="subgroup"></div>
									</td>
<?php							}
								if($f) { ?>
									<th></th>
<?php							}
								$f = false;
							} else { ?>
<?php							if(isset($week_list[$i+1][$k+1])) { ?>
									<td<?=(($i+1 == $now["wday"]) ? " class=\"current_day\"" : "")?>>
										<div class="lesson_num">
											<?=($k+1)?>
										</div>
<?php								if(count($week_list[$i+1][$k+1]) > 1) { ?>
										<div class="timetable_row">
<?php								}
									foreach($week_list[$i+1][$k+1] as $lesson) {
										if(count($week_list[$i+1][$k+1]) > 1) { ?>
											<div class="timetable_row">
<?php									} ?>
												<div class="subject">
													<div class="subject_cn">
<?php											if($MODULE_OUTPUT["edit_acces"]) { ?>
														<a href="delete/<?=$lesson["id"]?>/" onclick="return confirm('Вы уверены, что хотите удалить выбранный элемент?');">
															<img src="/themes/images/delete.png" alt="" />
														</a>
														<a href="edit/<?=$lesson["id"]?>/"><?=$lesson["subject"]?></a>
<?php    										} else { ?>
														<?=$lesson["subject"]?>
<?php											} ?>
													</div>
												</div>
												<div class="auditorium">
													<span title="<?=$lesson["building"]?>">
														<?=$lesson["label"]?>
													</span> - <?=$lesson["auditorium"]?>
												</div>
												<div class="subgroup">
													<?=$lesson["subgroup"]?>
												</div>
<?php									if(count($week_list[$i+1][$k+1]) > 1) { ?>
											</div>
<?php									} 
									}
									if(count($week_list[$i+1][$k+1]) > 1) { ?>
										</div>
<?php								} ?>
									</td>
<?php							} else { ?>
									<td<?=(($i+1 == $now["wday"]) ? " class=\"current_day\"" : "")?>>
										<div class="lesson_num">
											<?=($k+1)?>
										</div>
										<div class="subject"></div>
										<div class="auditorium"></div>
										<div class="subgroup"></div>
									</td>
<?php							} ?>
									<th></th>
<?php							if(isset($week_list[$i+4][$k+1])) { ?>
									<td<?=(($i+4 == $now["wday"]) ? " class=\"current_day\"" : "")?>>
										<div class="lesson_num">
											<?=($k+1)?>
										</div>
<?php								if(count($week_list[$i+4][$k+1]) > 1) { ?>
										<div class="timetable_row">
<?php								}
									foreach($week_list[$i+4][$k+1] as $lesson) {
										if(count($week_list[$i+4][$k+1]) > 1) { ?>
											<div class="timetable_row">
<?php									} ?>
												<div class="subject">
													<div class="subject_cn">
<?php											if($MODULE_OUTPUT["edit_acces"]) { ?>
														<a href="delete/<?=$lesson["id"]?>/" onclick="return confirm('Вы уверены, что хотите удалить выбранный элемент?');">
															<img src="/themes/images/delete.png" alt="" />
														</a>
														<a href="edit/<?=$lesson["id"]?>/"><?=$lesson["subject"]?></a>
<?php    										} else { ?>
														<?=$lesson["subject"]?>
<?php											} ?>
													</div>
												</div>
												<div class="auditorium">
													<span title="<?=$lesson["building"]?>">
														<?=$lesson["label"]?>
													</span> - <?=$lesson["auditorium"]?>
												</div>
												<div class="subgroup">
													<?=$lesson["subgroup"]?>
												</div>
<?php									if(count($week_list[$i+4][$k+1]) > 1) { ?>
											</div>
<?php									} 
									}
									if(count($week_list[$i+4][$k+1]) > 1) { ?>
										</div>
<?php								}?>
									</td>
<?php							} else { ?>
									<td<?=(($i+4 == $now["wday"]) ? " class=\"current_day\"" : "")?>>
										<div class="lesson_num">
											<?=($k+1)?>
										</div>
										<div class="subject"></div>
										<div class="auditorium"></div>
										<div class="subgroup"></div>
									</td>
<?php							} ?>								
<?php						}*/
						/*} ?>
								</tr>
<?php					$k++;
					} while($k < 7);
					if(count($timetable) > 1) { ?>
										<tr class="teble_buttom_padding">
											<td<?=(($i+1 == $now["wday"]) ? " class=\"current_day\"" : "")?>></td>
											<th></th>
											<td<?=(($i+1 == $now["wday"]) ? " class=\"current_day\"" : "")?>></td>
										</tr>
<?php				} else { ?>
										<tr class="teble_buttom_padding">
											<td<?=(($i+1 == $now["wday"]) ? " class=\"current_day\"" : "")?>></td>
											<th></th>
											<td<?=(($i+4 == $now["wday"]) ? " class=\"current_day\"" : "")?>></td>
										</tr>
<?php				} ?>
										
									</tbody>
								</table>
							</div>
<?php				if(count($timetable) > 1) {
						$i++;	
					} else {
						$i = $i+1;
					}
				} */?>
							<div class="timetable_two_column">
								<table>
									<tfoot>
										<tr>
											<td></td>
											<th></th>
											<td></td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div-->
					
					
					
					
					
					
					</div>
				
				
				
				</div>
				<div class="right_cont">
					<div id="timetable_data">
						Сегодня&nbsp;<?=$now["mday"]?>&nbsp;<?=$months[$now["mon"]-1]?>&nbsp;<?=$now["year"]?>&nbsp;г.
						<?=$days[$now["wday"]-1]?>.&nbsp;Неделя&nbsp;<?=$week?>
					</div>
					
					
					
                    <div id="designation">
						<h3>Обозначение корпусов</h3>
						<ul>
<?php			foreach($MODULE_OUTPUT["buildings"] as $buildings) { ?>
							<li>
								<span><?=$buildings["label"]?></span>
								<p><?=$buildings["name"]?></p>
							</li>    
<?php			} ?>
						</ul>
                    </div>
				</div>
                
<?php		if($MODULE_OUTPUT["edit_acces"]) { ?>
				<div class="EEngine EE_add_timetable">
					<!--form method="post">
						<fieldset>
							<legend>Добавить запись в расписание:</legend>
							<div class="wide">
								<div class="notes_column">
									<div class="form-notes title-notes">
										<label>День недели:</label>
									</div>
									<div class="center-notes">
										<div class="cn">
											<div class="form-notes">
												<select name="dow">
<?php			/*for($i = 0; $i < 6; $i++) { ?>
													<option value="<?=$i+1?>"><?=$days[$i]?></option>
<?php			} */?>
												</select>
											</div>
										</div>
									</div>
								</div>
								<div class="notes_column">
									<div class="form-notes title-notes">
										<label>Номер пары:</label>
									</div>
									<div class="center-notes">
										<div class="cn">
											<div class="form-notes">
												<select name="num">
													<option value="1" selected="selected">1</option>
<?php          /* for($i=1; $i<8; $i++) { ?>
													<option value="<?=$i?>"><?=$i?></option>
<?php    		} */?>
												</select>
											</div>
										</div>
									</div>
								</div>								
							</div>
							<div class="wide">
								<div class="notes_column">
									<div class="wide">
										<label>Четная/нечетная или каждую неделю:</label>
									</div>
									<div class="wide">
										<input type="radio" name="parity" checked="checked" value="0" />Каждую неделю
									</div>
									<div class="wide">
										<input type="radio" name="parity" value="2" />Четная
									</div>
									<div class="wide">
										<input type="radio" name="parity" value="1" />Нечетная
									</div>
								</div>
								<div class="notes_column">
									<div class="wide">
										<label>Номер подгруппы, если есть</label>
									</div>
									<div class="wide">
										<input type="radio" name="subgroup" checked="checked" value="0" />Нет
									</div>
									<div class="wide">
										<input type="radio" name="subgroup" value="а" />а
									</div>
									<div class="wide">
										<input type="radio" name="subgroup" value="б" />б
									</div>
								</div>
								<div class="notes_column">
									<br />
									<div class="wide">
										<label>Даты действия</label>
									</div>
									C:
            						<input name='date_from' type="text" class="date_time"  value="<?=date('Y-m-d')?>" />
            						По:
            						<input name='date_to' type="text" class="date_time"  value="<?=date('Y-m-d')?>" />
            						<br />
            					</div>
							</div>
							<div class="wide">
								<div class="form-notes">
									<label>Тип занятия:</label>&nbsp;
									<input type="radio" name="lesson_view" value="1" />Лекция&nbsp;&nbsp;
									<input type="radio" name="lesson_view" checked="checked" value="0" />Практика
								</div>
							</div>
							<div class="wide">
								<div class="notes_column">
									<div class="wide">
										<label>Дисциплина:</label>
									</div>
									<div class="wide">
										<select name="subject">
<?php	/*	foreach($MODULE_OUTPUT["dep_subjects"] as $dep_subj) { ?>
										<optgroup label="<?=$dep_subj['dep_name']?>">
<?php			foreach($dep_subj['subjects'] as $subject) { ?>
											<option value="<?=$subject["id"]?>"><?=$subject["name"]?></option>
<?php			} ?>
										</optgroup>
<?php		}*/ ?>
										</select>
									</div>
								</div>
								<div class="notes_column">
									<div class="wide">
										<label>Аудитория:</label>
									</div>
									<div class="wide">
										<select name="auditorium">
<?php			/*$id=0;
				foreach($MODULE_OUTPUT["auditorium"] as $auditorium) {
					if(!$id) {
						$id++; ?>
											<optgroup label="<?=$auditorium["building_name"]?>">
<?php       		}
					if($id != $auditorium["building_id"]) { ?>
											</optgroup>
											<optgroup label="<?=$auditorium["building_name"]?>">
<?php 					$id++;
					} ?>
												<option value="<?=$auditorium["id"]?>"><?=$auditorium["name"]?></option>
<?php 			} */?>
											</optgroup>
										</select>
									</div>
								</div>
							</div>
							<div class="wide">
								<input type="submit" value="Добавить запись" class="button" />
							</div>
						</fieldset>					
					</form-->
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				</div>
<?php       } 
        } else { ?>
            <form action="/student/timetable/groups/" method="get">        
				<p>
					Группа:
					<input name="group" type="text" size="35" value="<?php echo isset($_GET["group"]) ? $_GET["group"] : "" ?>" />
					<input type="submit" value="Найти" class="button" />
				</p>        
            </form>
<?php       if(!empty($MODULE_OUTPUT["groups"])) { ?>
            <ul>
<?php			foreach($MODULE_OUTPUT["groups"] as $group) { ?>
                <li><a href="/student/timetable/groups/<?=$group["id"]?>/">Группа <?=$group["name"]?>, <?=$group["faculty"]?></a></li>
<?php          	} ?>
            </ul>
<?php       } else {
                if(isset($_GET["group"])) { ?>
            <p>Не найдено групп с такими параметрами поиска.</p>
<?php			} else { ?>
            <p>Введите в поле поиска название группы.</p>
<?php           }
            };
        };
	}
    break;
		
	case "students": {
		if (isset($MODULE_OUTPUT["students"]) && sizeof($MODULE_OUTPUT["students"])) { ?>
			<h2>Состав группы</h2>
			<ol>
<?php 		foreach($MODULE_OUTPUT["students"] as $student) { ?>
				<li><a href="/people/<?=$student["id"]?>/"><?=($student["last_name"]." ".$student["name"]." ".$student["patronymic"])?></a>
<?php		} ?>
			</ol>
<?php	}
	}
	break;
    
    case "timetable_groups": { 
        if(!isset($MODULE_OUTPUT["groups_mode"])) {
			$id = -1;
			foreach($MODULE_OUTPUT["timetable_groups"] as $group) {
			    if($group["id_faculty"] != $id) {
			        if($group["institute_name"]) { ?>
            <h2><?=$group["institute_name"]?>: <?=$group["faculty_name"]?></h2>
<?php				} else { ?>
            <h2><?=isset($group["faculty_name"]) ? $group["faculty_name"] : "Факультет не указан"?></h2>
<?php				}
			        $id = $group["id_faculty"];
			    } ?>
            <a href="edit/<?=$group["id"]?>/"><?=$group["name"]?></a> [<a href="delete/<?=$group["id"]?>/" onclick="return confirm('Вы уверены, что хотите удалить выбранный элемент?');"}><img src="/themes/images/delete.png" alt="Удалить" /></a>][<a href="/student/timetable/groups/<?=$group["id"]?>/">Перейти к расписанию группы</a>]<br />
<?php		} ?>
        <form method="post" name="add_group" id="add_group">
			<fieldset>
				<legend>Добавить группу</legend>
				<div class="wide">
					<div class="form-notes">
						<dl>
							<dt><label>Имя группы:</label></dt>
							<dd id="new_group_name"><input type="text" name="group_name" /></dd>
							<dt><label>Курс:</label></dt>
							<dd id="group_year">
								<select name="year" size="1">
									<option value="1" selected="selected">Первый курс</option>
									<option value="2">Второй курс</option>
									<option value="3">Третий курс</option>
									<option value="4">Четвёртый курс</option>
									<option value="5">Пятый курс</option>
									<option value="6">Шестой курс</option>
								</select>
							</dd>
						</dl>
					</div>
					<div class="form-notes">
						<dl>
							<dt><label>Направление подготовки:</label></dt>
							<dd id="group_faculties">
								<select style="max-width: 400px" name="faculties" size="1">
									<option value="0" selected="selected">Направление подготовки</option>
<?php		foreach($MODULE_OUTPUT["faculties"] as $faculty) { ?>
									<optgroup label="<?=$faculty["fac_name"]?>">
<?php			foreach($faculty["spec"] as $spec) { ?>
										<option value="<?=$spec["id_faculty"]?>|<?=$spec["code"]?>"><?=$spec["code"]?>-<?=$spec["name"]?></option>
<?php			} ?>						
									</optgroup>
<?php		} ?>
								</select>								
							</dd>
							<dt><label>Выберите уровень подготовки группы:</label></dt>
							<dd id="group_qualification">
								<select name="qualif">
<?php		$qualif = array(68=>"Магистратура", 65=>"Специалист", 62=>"Бакалавриат", 51=>"Среднее специальное");
				foreach($qualif as $qual_code => $qualif) {
					if($qual_code == 62) { ?>
									<option value="<?=$qual_code?>" selected="selected"><?=$qualif?></option>
<?php				} else { ?>
									<option value="<?=$qual_code?>"><?=$qualif?></option>
<?php				}
				} ?>
								</select>
							</dd>
							<dt><label>Выберите форму обучения:</label></dt>
							<dd id="form_education">
								<select name="form_edu">
<?php		$form_edu = array(1=>"Очная", 2=>"Заочная");
				foreach($form_edu as $form_code => $form_edu) {
				if($form_code == 1) { ?>
									<option value="<?=$form_code?>" selected="selected"><?=$form_edu?></option>
<?php				} else { ?>
									<option value="<?=$form_code?>"><?=$form_edu?></option>
<?php				}
				} ?>
								</select>
							</dd>
						</dl>					
					</div>
				</div>
				<div class="wide">
					<input type="submit" value="Добавить" class="button" />
				</div>
			</fieldset>
        </form>
<?php  	} else {
            switch($MODULE_OUTPUT["groups_mode"]) {
                case "edit": {
                    foreach($MODULE_OUTPUT["edit_group"] as $group) { ?>
                        <form name="edit_group" method="post" id="edit_group">
							<fieldset>
								<legend>Редактирование информации о группе <?=$group["name"]?> (<?=$group["faculty_name"]?>)</legend>
								<div class="wide">
									<div class="form-notes title-notes">
										<div id="group_name">
											<h3>Название группы:</h3>
											<input type="text" name="group_name" value="<?=$group["name"]?>" />
										</div>
									</div>
									<div class="form-notes">
										<div id="group_faculties">
											<h3>Специальность, к которому относится группа:</h3>
											<select name="faculty">
<?php			foreach($MODULE_OUTPUT["faculties"] as $faculty) { ?>
												<optgroup label="<?=$faculty["fac_name"]?>">
<?php				foreach($faculty["spec"] as $spec) {
						if($spec["code"] == $group["spec_code"]) { ?>
													<option value="<?=$spec["id_faculty"]?>|<?=$spec["code"]?>" selected="selected"><?=$spec["name"]?></option>
<?php					} else { ?>
													<option value="<?=$spec["id_faculty"]?>|<?=$spec["code"]?>"><?=$spec["name"]?></option>
<?php					}
					} ?>						
												</optgroup>					
<?php			} ?>
											</select>
										</div>
									</div>
								</div>
								<div class="wide">
									<div class="form-notes title-notes">
										<div id="group_year">
											<h3>Курс:</h3>
											<select name="year" size="1">
	<?php					$year_arry = array('Первый курс','Второй курс','Третий курс','Четвёртый курс','Пятый курс','Шестой курс');
									for($i = 1; $i <= 6; $i++) {
										if($i == $group["year"]) { ?>
												<option value="<?=$i?>" selected="selected"><?=$year_arry[$i-1]?></option>
	<?php						} else { ?>
												<option value="<?=$i?>"><?=$year_arry[$i-1]?></option>
	<?php						}
							} ?>
											</select>
										</div>
									</div>
									<div class="form-notes">
										<div id="group_qualification">
											<h3>Уровень подготовки группы:</h3>
											<select name="qualif">
			<?php					$qualif = array(68=>"Магистратура", 65=>"Специалист", 62=>"Бакалавриат", 51=>"Среднее специальное");
									foreach($qualif as $qual_code => $qualif) {
										if($qual_code == $group["qualification"]) { ?>
												<option value="<?=$qual_code?>" selected="selected"><?=$qualif?></option>
			<?php 						} else { ?>
												<option value="<?=$qual_code?>"><?=$qualif?></option>
			<?php 						}
									} ?>
											</select>
										</div>
										<div id="form_educat">
								<h3>Выберите форму обучения:</h3>
								<select name="form_edu">
<?php

	if($group["form_education"]==!NULL) {

$form_edu = array(  1=>"Очная", 2=>"Заочная");
				foreach($form_edu as $form_code => $form_edu) {
				if($form_code == $group["form_education"]) {?>
									<option value="<?=$form_code?>" selected="selected"><?=$form_edu?></option>
<?php				} else { ?>
									<option value="<?=$form_code?>"><?=$form_edu?></option>

		
				
				<? }
				}
	}
				
				if($group["form_education"]==NULL) {
				$form_edu = array( 1=>"Очная", 2=>"Заочная", NULL=>" ");
				foreach($form_edu as $form_code => $form_edu) {
					if($form_code == $group["form_education"]) {?>
									<option value="<?=$form_code?>" selected="selected"><?=$form_edu?></option>
<?php				} else { ?>
									<option value="<?=$form_code?>"><?=$form_edu?></option>
				
				<? }
				
					}
						}?>
								</select>
							</div>
								</div>	
									</div>
									
								<br/><br/>					
								<div class="wide">
									<input type="hidden" value="edit" name="mode" />
									<input type="hidden" value="<?=$group["id"]?>" name="id" />
									<input type="submit" value="Сохранить" class="button" />
								</div>
							</fieldset>
                        </form>
<?php 				}
                    break;
                }
            }
        }
        break;
    }
    case "timetable_faculties":
    {
        if(!isset($MODULE_OUTPUT["faculty_mode"]))
        {
            ?>
            <h2>Список факультетов:</h2>
            <ul>
            <?php
        foreach($MODULE_OUTPUT["timetable_faculties"] as $faculty)
        {
            ?>
            <li><a href="edit/<?=$faculty["id"]?>/" title="<?=$faculty["comment"]?>"><?=$faculty["name"]?></a> [<a href="delete/<?=$faculty["id"]?>/" onclick="return confirm('Вы уверены, что хотите удалить выбранный элемент?');"}>Удалить</a>]</li>
            <?php
            if(isset($faculty["subfaculties"]))
            {
                ?>
                <ul>
                <?php
                foreach($faculty["subfaculties"] as $subfaculty)
                {
                    ?>
                    <li><a href="edit/<?=$subfaculty["id"]?>/"><?=$subfaculty["name"]?></a> [<a href="delete/<?=$subfaculty["id"]?>/" onclick="return confirm('Вы уверены, что хотите удалить выбранный элемент?');"}>Удалить</a>]</li>
                    <?php
                }
                ?>
                </ul>
                <?php
            }    
        }
        ?>
        </ul>
        <br />
        <h2>Добавить факультет или институт:</h3>
        <form method="post" name="add_faculty">
        <h3>Название факультета или института:</h3>
        <input type="text" name="faculty_name" class="input_long" />
		<h3>Позиция в списке:</h3>
        <input type="text" name="pos" class="input" />
		<h3>Короткое название:</h3>
        <input type="text" name="faculty_shortname" />
		<h3>Год основания:</h3>
        <input type="text" name="foundation_year" />
		<h3>Описание:</h3>
        <input type="text" name="comment" class="input_long" />
        <h3>Выберите институт (если есть), к которому будет относиться данный факультет:</h3>
        <select name="pid" size="1">
        <option value="0" selected="selected">Выберите институт:</option>
        <?php
        foreach($MODULE_OUTPUT["timetable_faculties"] as $faculty)
        {
            ?>
            <option value="<?=$faculty["id"]?>"><?=$faculty["name"]?></option>
            <?php
        }
        ?>
        </select>
		<h3>Сайт:</h3>
        <input type="text" name="faculty_link" /> <br />
		<input type="checkbox" name="is_active" checked="checked"> Ныне действующий
        <br />
        <input type="submit" value="Добавить" class="button" />
        </form>
        <?php
        }
        else
        {
            switch($MODULE_OUTPUT["faculty_mode"])
            {
                case "edit":
                {
                    foreach($MODULE_OUTPUT["edit_faculty"] as $faculty)
                    {
                        ?>
                        <h2>Редактирование информации о факультете <?=$faculty["name"]?>:</h2>                        
                        <form name="edit_faculty" method="post">
                        <h3>Название факультета:</h3>
                        <input type="text" name="faculty_name" value="<?=$faculty["name"]?>" class="input_long" />
                        <h3>Факультет или институт, к которому относится данный факультет:</h3>
                        <select name="pid" size="1">
                        <?php
                        if(!$faculty["pid"])
                        {
                            ?>
                            <option value="0" selected="selected">Выберите факультет</option>
                            <?php    
                        }
                        foreach($MODULE_OUTPUT["faculties"] as $faculties)
                        {
                        
                            if($faculty["pid"] == $faculties["id"])
                            {?>
                                <option value="<?=$faculties["id"]?>" selected="selected"><?=$faculties["name"]?></option>
                                <?php
                            }
                            else
                            {   ?>
                                <option value="<?=$faculties["id"]?>"><?=$faculties["name"]?></option>
                                <?php
                            }
                        }
                        ?>
                        </select><br />
						<h3>Позиция в списке:</h3>
						<input type="text" name="pos" value="<?=$faculty["pos"]?>" />
						<h3>Сайт факультета:</h3>
                        <input type="text" name="faculty_link" value="<?=$faculty["link"]?>" />
						<h3>Год основания:</h3>
                        <input type="text" name="foundation_year" value="<?=$faculty["foundation_year"]?>" />
						<h3>Описание:</h3>
						<input type="text" name="comment" class="input_long" value="<?=$faculty["comment"]?>" /><br />
						<input type="checkbox" name="is_active"<? if ($faculty["is_active"]) { ?>checked="checked"<? } ?>> Ныне действующий
                        <input type="hidden" value="edit" name="mode" />
                        <input type="hidden" value="<?=$faculty["id"]?>" name="id" /><br />
                        <input type="submit" value="Сохранить" class="button" />
                        </form>
                        <?php
                    }
                    break;
                }
            }
        }
        break;
    }
    
    case "timetable_make":
    {
        ?>
        <h2>Управление расписанием</h2>
        <p>Вы находитесь в разделе "Управление расписанием".
        В данном разделе вы можете добавлять и редактировать информацию о факультетах, кафедрах, преподавателях, дисциплинах, аудиториях и группах.
        Так же вы можете составлять и редактировать графики учебного процесса и распределение дисциплин по кафедрам.
        Для этого выберите соответствующий раздел меню, находящегося справа.</p>
        <?php
        break;
    }
    
    case "pulpit_list": {
        if(isset($MODULE_OUTPUT["pulpit"])) { ?>
			<div class="shadow_rbg">
<?php       if(isset($MODULE_OUTPUT["faculty_name"])) { ?>
                <h2>Кафедры:</h2>
<?php       } ?>
				<ul class="shadow_cont">
<?php       foreach($MODULE_OUTPUT["pulpit"] as $pulpit) {
                if($pulpit["url"] == null) { ?>
					<li><?=$pulpit["name"]?></li>
<?php           } else { ?>
                    <li><a href="<?=$pulpit["url"]?>"><?=$pulpit["name"]?></a></li>
<?php			}
            } ?>
				</ul>
				<div class="shadow_tr">&nbsp;</div>
				<div class="shadow_b">
					<div class="shadow_br">&nbsp;</div>
					<div class="shadow_bl">&nbsp;</div>
				</div>
			</div>
<?php   }
        break;
    }
    
    case "timetable_departmens":
    {
        if(!isset($MODULE_OUTPUT["departments_mode"]))
        { 
        if(isset($MODULE_OUTPUT["timetable_departments"]))
        {
            $id = 0;
            ?>
            <h2>Список кафедр по факультетам:</h2>            
            <ul>
            <?php
            foreach($MODULE_OUTPUT["timetable_departments"] as $department)
            {
                if($department["faculty_id"] != $id)
                {
                    ?>
                    </ul><li><?=$department["faculty_name"]?><ul>
                    <?php
                    $id = $department["faculty_id"];
                }
                ?>
                <li> <a href="edit/<?=$department["id"]?>/" <?php if(!$department['is_active']) { ?>  class="inactive"<?php } ?>><?=$department["name"]?></a> [<a href="delete/<?=$department["id"]?>/" onclick="return confirm('Вы уверены, что хотите удалить выбранный элемент?');"}>Удалить</a>]</li>
                <?php
            }
            ?>
            </ul>
            <?php   
        }
        ?>
        <br />
        <h2>Добавить кафедру:</h2>
        <form method="post" name="add_department">
        <h3>Название кафедры:</h3>
        <input type="text" name="department_name" size="60" />
        <h3>Адрес кафедры:</h3>
        <input type="text" name="department_url" size="60" />
        <h3>Выберите институт или факультет, к которому относится кафедра:</h3>
        <select name="faculty_id" size="1">
        <option value="0" selected="selected">Выберите факультет:</option>
        <?php
        foreach($MODULE_OUTPUT["faculties"] as $faculty)
        {
            ?>
            <option value="<?=$faculty["id"]?>"><?=$faculty["name"]?></option>
            <?php
        }
        ?>
        </select>
        <br />
        <h3><input type="checkbox" name="is_active" value="1" /> Кафедра активна</h3>
        <input type="submit" value="Добавить" class="button" />
        </form>
        <?php
        }
        else
        {
            switch($MODULE_OUTPUT["departments_mode"])
            {
                case "edit":
                {
                    foreach($MODULE_OUTPUT["edit_department"] as $department)
                    {
                        ?>
                        <h2>Редактирование информации о кафедре <?=$department["name"]?> (<?=$department["faculty_name"]?>):</h2>
                        <form name="edit_department" method="post">
                        <h3>Название кафедры:</h3>
                        <input type="text" name="department_name" value="<?=$department["name"]?>" size="60" />
                        <h3>Адрес кафедры:</h3>
                        <input type="text" name="department_url" value="<?=$department["url"]?>" size="60" />
                        <h3>Факультет, к которому относится кафедра:</h3>
                        <select name="faculty">
                        <?php
                        foreach($MODULE_OUTPUT["faculties"] as $faculty)
                        {
                            if($faculty["id"] == $department["faculty_id"])
                            {?>
                                <option value="<?=$faculty["id"]?>" selected="selected"><?=$faculty["name"]?></option>
                                <?php
                            }
                            else
                            {?>
                                <option value="<?=$faculty["id"]?>"><?=$faculty["name"]?></option>
                                <?php
                            }
                        }
                        ?>
                        </select><br />
                        <h3><input type="checkbox" name="is_active" value="1" <?php echo $department["is_active"] ? " checked=\"checked\"" : ""; ?> /> Кафедра активна</h3>
                        <input type="hidden" value="edit" name="mode" />
                        <input type="hidden" value="<?=$department["id"]?>" name="id" />
                        <input type="submit" value="Сохранить" class="button" />
                        </form>
                        <?php
                    }
                    break;
                }
            }
        }
        break;
    }
	
	case "select_teachers": {
		if (count($MODULE_OUTPUT["select_teachers"])) { ?>
		<div class="select_teachers">
			<h2>Состав кафедры:</h2>
			<ul>
<?php		foreach($MODULE_OUTPUT["select_teachers"] as $teachers) { ?>
				<li>
					<a class="name-color" href="/people/<?=$teachers["id"]?>/"><strong><?=$teachers["last_name"]?></strong> <?=$teachers["name"]?> <?=$teachers["patronymic"]?></a><?=($teachers["prad"] ? ", ".strip_tags($teachers["prad"]) : "")?>
				</li>
<?php		} ?>
			</ul>
			<hr class="sub_hr" />
		</div>
<?php	}
	}
	break;
	
	case "dep_subjects": {
		
		
		if(isset($MODULE_OUTPUT["subjects_file"])) { 
			if(!isset($MODULE_OUTPUT["faculties_name"])) { ?>
			<h2>Методическая работа:</h2>
<?php		} else { ?>
			<h2>Библиотека:</h2>
<?php 		} 
		if(isset($_GET['list_type'], $MODULE_OUTPUT["spec_file"]) && $_GET['list_type'] == 'spec') { ?>
		<div><a href="?list_type=subj">По дисциплинам</a> &nbsp;&nbsp;&nbsp;<strong>По направлению подготовки</strong></div>
		<div class="subject_file_list">
			<ul>
<?php		foreach($MODULE_OUTPUT["spec_file"] as $spec_id => $spec) { ?>
				<li>
<?php			if(!isset($spec['files'])) { ?>
					<?=$spec['code']?> - <?=$spec['name']?>
<?php			} else { ?>
					<a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');" data-id="<?=$spec_id; ?>"><?=$spec['code']?> - <?=$spec['name']?></a>
					<ul>
<?php				foreach($spec['files'] as $file_id => $file_info) {
						$title = "";
						if(isset($file_info['descr'])) {
							$title .= "Описание: ".$file_info['descr']."";
						}
						if(isset($file_info['view'])) {
							if(!empty($title)) {
								$title .= "&nbsp;|&nbsp;&nbsp;";
							}
							$title .= "Вид файла: ".$file_info['view']."";
						}
						if(isset($file_info['education'])) {
							if(!empty($title)) {
								$title .= "&nbsp;|&nbsp;&nbsp;";
							}
							$title .= "форма обучения: ".$file_info['education']."";
						}
						if(empty($title)) {
							$title = "Информация отсутствует";
						} ?>
						<li>
							<a target="_blank" href="/file/<?=$file_id?>/" title="<?=$title?>"><?=$file_info['name']?></a>
							<span>Дисциплина: <?=$file_info['subj']?></span>
						</li>
<?php				} ?>
					</ul>
				</li>
<?php			} ?>
<?php		} ?>
			</ul>
			<hr class="sub_hr" />
		</div>
<?php		} else { ?>
		<div><strong>По дисциплинам</strong> &nbsp;&nbsp;&nbsp;<a href="?list_type=spec">По направлению подготовки</a></div>
		<div class="subject_file_list">
			<ul>
<?php		foreach($MODULE_OUTPUT["subjects_file"] as $subj_id => $subj) { ?>
				<li>
<?php			if(!isset($subj['files'])) { ?>
					<?=$subj['name']?>
<?php			} else { ?>
					<a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');" data-id="<?=$subj_id; ?>"><?=$subj['name']?></a>
					<ul>
<?php				foreach($subj['files'] as $file_id => $file_info) {
						$title = "";
						if(isset($file_info['descr'])) {
							$title .= "Описание: ".$file_info['descr']."";
						}
						if(isset($file_info['view'])) {
							if(!empty($title)) {
								$title .= "&nbsp;|&nbsp;&nbsp;";
							}
							$title .= "Вид файла: ".$file_info['view']."";
						}
						if(isset($file_info['education'])) {
							if(!empty($title)) {
								$title .= "&nbsp;|&nbsp;&nbsp;";
							}
							$title .= "форма обучения: ".$file_info['education']."";
						}
						if(empty($title)) {
							$title = "Информация отсутствует";
						} ?>
						<li>
							<a target="_blank" href="/file/<?=$file_id?>/" title="<?=$title?>"><?=$file_info['name']?></a>
<?php					if(isset($file_info['spec_code'],$file_info['spec_name'])) { ?>
							<span>Направление подготовки: <?=$file_info['spec_code']?> - <?=$file_info['spec_name']?></span>
<?php					} ?>
						</li>
<?php				} ?>
					</ul>
				</li>
<?php			} ?>
<?php		} ?>
			</ul>
			<hr class="sub_hr" />
		</div>
<?php		} ?>
<?php	}
		if (count($MODULE_OUTPUT["subjects"])) { ?>
		<div>
			<h2>Дисциплины кафедры:</h2>
			<div>
				<ul>
<?php		foreach($MODULE_OUTPUT["subjects"] as $subj) {
				if ($subj['has_files_attached']) { ?>
					<li><a href="<?=$subj["id"]?>/"><?=$subj["name"]?></a></li>
<?php 			} else { ?>
					<li><?=$subj["name"]?></li>
<?php 			}
			} ?>
				</ul>
			</div>
		</div>
<?php	}		
		if (count($MODULE_OUTPUT["specs"])) { ?>
		<div>
			<h2>Направления подготовки:</h2>
			<div>
				<ul>
<?php		foreach($MODULE_OUTPUT["specs"] as $spec) { ?>
					<li><a href="specs/<?=$spec["id"]?>/"><?=$spec["code"]." ".$spec["name"]?></a></li>
<?php		} ?>
				</ul>
			</div>
		</div>
<?php	}
	}
	break;
	
	case "dep_subj_views": {
		if (count($MODULE_OUTPUT["views"])) { ?>
		<h1><?=$MODULE_OUTPUT["department"]["name"]?></h1>
		<h2><?=(isset($MODULE_OUTPUT["subject"]["name"]) ? $MODULE_OUTPUT["subject"]["name"] : '').(isset($MODULE_OUTPUT["spec"]["id"]) ? $MODULE_OUTPUT["spec"]["code"].'-'.$MODULE_OUTPUT["spec"]["name"] : '')?></h2>
		<b>Учебно-методический комплекс дисциплин</b>
		<br />
		<ul>
<?php 		foreach($MODULE_OUTPUT["views"] as $view) {
				if ($view["file_id"]) { ?>
			<li><a href="/file/<?=$view["file_id"]?>/"><?=$view["file_descr"] ? $view["file_descr"] : $view["view_name"]?></a></li>
<?php 			}				
			} ?>
		</ul>
<?php 	}
	}
	break;
    case "izop_ekonom":{?>
			<h2>Специалитет</h2>
		<ul>
			
		<? foreach($MODULE_OUTPUT["specialities"] as $special1) {
			if($special1["code"]=='080109'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='080109')&&($code["spec_type"]=='65')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				<?}}?>
				
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		if($special1["code"]=='080502'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='080502')&&($code["spec_type"]=='65')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				
					<?}}?>	
			</ul>
				
		
				
				</li>
				
				
			<?
		
		}
			if($special1["code"]=='080105'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='080105')&&($code["spec_type"]=='65')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				
				<?} }?>
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		}?>
			
		</ul>
		
			
		
	<h2>Бакалавриат</h2>
		<ul>
			
		<? foreach($MODULE_OUTPUT["specialities"] as $special1) {
			if($special1["code"]=='080100'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='080100')&&($code["spec_type"]=='62')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				
					<? }}?>
			</ul>
				
			
				
				</li>
				
				
			<?
		
		}
			if($special1["code"]=='080200'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='080200')&&($code["spec_type"]=='62')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				
				<?} }?>
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		}?>
			
		</ul>
		
		
		
	<?
	}
	

			break;
		 case "izop_engineer":{?>
			<h2>Специалитет</h2>
		<ul>
			
		<? foreach($MODULE_OUTPUT["specialities"] as $special1) {
			if($special1["code"]=='110301'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='110301')&&($code["spec_type"]=='65')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
					<?} }?>
				
			</ul>
				
			
				
				</li>
				
				
			<?
		
		}
		if($special1["code"]=='110304'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='110304')&&($code["spec_type"]=='65')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				<?} }?>
				
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		
			if($special1["code"]=='110302'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='110302')&&($code["spec_type"]=='65')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				
			<?} }?>	
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		
		if($special1["code"]=='190601'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='190601')&&($code["spec_type"]=='65')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
			<? }}?>	
				
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		}?>
			
		</ul>
			
			
			
		
	<h2>Бакалавриат</h2>
		<ul>
			
		<? foreach($MODULE_OUTPUT["specialities"] as $special1) {
			if($special1["code"]=='110800'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='110800')&&($code["spec_type"]=='62')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				
				<?} }?>
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		
			if($special1["code"]=='190600'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
					<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='190600')&&($code["spec_type"]=='62')){
			?>
				
			
		<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				<?} }?>
				
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		}?>
			
		</ul>
		
		
		
		
		
		
		
	<?
	}
	

			break;
		
		
		 case "izop_bio":{?>
			<h2>Специалитет</h2>
		<ul>
			
		<? foreach($MODULE_OUTPUT["specialities"] as $special1) {
			if($special1["code"]=='200503'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='200503')&&($code["spec_type"]=='65')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				<?} }?>
				
			</ul>
				
				
				
				</li>
				
				
			<?
			}
			if($special1["code"]=='110400'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='110400')&&($code["spec_type"]=='65')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				
				<?} }?>
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		
		
			if($special1["code"]=='110305'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='110305')&&($code["spec_type"]=='65')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
			<?} }?>	
				
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		
		if($special1["code"]=='260501'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='260501')&&($code["spec_type"]=='65')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				<?} }?>
				
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		
		if($special1["code"]=='110201'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='110201')&&($code["spec_type"]=='65')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				
				<?} }?>
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		
		if($special1["code"]=='250203'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='250203')&&($code["spec_type"]=='65')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				<?} }?>
				
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		if($special1["code"]=='111201'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='111201')&&($code["spec_type"]=='65')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				<?} }?>
				
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		
		}
		?>
			
		</ul>
			
		
			
			
				
		
		
		
	<h2>Бакалавриат</h2>
		<ul>
			
		<? foreach($MODULE_OUTPUT["specialities"] as $special1) {
			if($special1["code"]=='110400'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='110400')&&($code["spec_type"]=='62')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				
				<?}
				}?>
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		
		if($special1["code"]=='250700'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='250700')&&($code["spec_type"]=='62')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				<?} }?>
				
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
			if($special1["code"]=='111900'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='111900')&&($code["spec_type"]=='62')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				<?} }?>
				
			</ul>
				
				
				
				</li>
				
				
			<?
		
			}
			
				if($special1["code"]=='250100'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='250100')&&($code["spec_type"]=='62')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				
				<?} }?>
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
			
				if($special1["code"]=='221700'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
				if(($code["education"]=='Заочная')&&($code["spec_code"]=='221700')&&($code["spec_type"]=='62')){
			?>
				
				
			<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
			<?	}
			}?>
				
			</ul>
				
				
				
				</li>
				
				
			<?
		
			}
			
				if($special1["code"]=='110900'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='110900')&&($code["spec_type"]=='62')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				<?} }?>
				
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		
			if($special1["code"]=='260800'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='260800')&&($code["spec_type"]=='62')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				<?} }?>
				
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		
		if($special1["code"]=='111100'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='111100')&&($code["spec_type"]=='62')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				
					<?} }?>
			</ul>
				
			
				
				</li>
				
				
			<?
		
		}
		
		}?>
			
		</ul>
		
				
	<?
	}
	break;
		
		case "izop_government":{?>
			<h2>Специалитет</h2>
		<ul>
			
		<? foreach($MODULE_OUTPUT["specialities"] as $special1) {
			if($special1["code"]=='080504'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='080504')&&($code["spec_type"]=='65')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])&&($code["descr"]!=NULL)){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				
				
			
				
				<?}
			}
			?>
				</ul>
				</li>
				
				
		<?
			
		
		}
		
		if($special1["code"]=='080505'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='080505')&&($code["spec_type"]=='65')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				<?} }?>
				
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
	}?>
			
		</ul>
		
		
			
			
		
	<h2>Бакалавриат</h2>
		<ul>
			
		<? foreach($MODULE_OUTPUT["specialities"] as $special1) {
			if($special1["code"]=='081100'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='081100')&&($code["spec_type"]=='62')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				<?} }?>
				
			</ul>
				
				
				
				</li>
				
				
			<?
		
		}
		
		
			if($special1["code"]=='080400'){?>
				
				<li><a href="javascript:void(0)" onclick="jQuery(this).parent().toggleClass('active');"><?=$special1["code"]?> - <?=$special1["name"]?></a>
				<ul>
				<? 
			foreach($MODULE_OUTPUT["code1"] as $code) {
				
			if(($code["education"]=='Заочная')&&($code["spec_code"]=='080400')&&($code["spec_type"]=='62')){
			?>
				
				
				<li>
					<? if(isset($code["descr"])){
				?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["descr"]?></a><span>  Дисциплина: <?=$code["p_n"]?></span>
				
			<?} else{		?>
				<a target="_blank" href="/file/<?=$code["file_id"]?>/" > <?=$code["name"]?></a>	<span>  Дисциплина: <?=$code["p_n"]?> </span> <? }?>
				
					
				</li>
				
						<?}
			}?>	
			</ul>
				
			
				
				</li>
				
				
			<?
		
		}
		}?>
			
		</ul>
				
		
	<?
	}
	

			break;
    case "timetable_teachers": {
        $rate = array(0.25, 0.5, 0.75, 1, 1.25, 1.5);
        if(!isset($MODULE_OUTPUT["teachers_mode"])) {
			if(isset($MODULE_OUTPUT["timetable_teachers"])) {
				$id = 0; ?>
		<a href="add/">Добавить преподавателя</a>
        <h2>Состав кафедр:</h2>
		<ul>
<?php			foreach ($MODULE_OUTPUT["timetable_teachers"] as $dep_id => $dep) { ?>
			<li><?=$dep["dep_name"]?>
				<ul>
<?php				foreach($dep["teachers"] as $teachers) {
						if (isset($MODULE_OUTPUT["allow_handle"][$dep_id]) && $MODULE_OUTPUT["allow_handle"][$dep_id]) { ?>
					<li>
						<a href="/people/<?=$teachers["id"]?>/"><?php if($teachers["status"] == 9) {?><span class="user_inactive"><?php } ?><?=$teachers["last_name"]?> <?=$teachers["name"]?> <?=$teachers["patronymic"]?><?php if($teachers["status"] == 9) {?></span><?php } ?></a>
						<a href="edit/<?=$teachers["id"]?>/<?=$teachers["department_id"]?>/"><img src="/themes/images/edit.png" /></a>
						<a href="delete/<?=$teachers["id"]?>/<?=$teachers["department_id"]?>/" onclick="return confirm('Вы уверены, что хотите удалить выбранный элемент?');"><img src="/themes/images/delete.png" /></a>
					</li>
<?php 					} else { ?>
					<li>
						<a href="/people/<?=$teachers["id"]?>/"><?=$teachers["last_name"]?> <?=$teachers["name"]?> <?=$teachers["patronymic"]?></a>
					</li>
<?php 					}
					} ?>
				</ul>
			</li>
<?php			} ?>
		</ul>
<?php 		} ?>

<?php 	if(isset($MODULE_OUTPUT["is_exist"])) {
			$exist = $MODULE_OUTPUT["is_exist"]["people"];
            //foreach($MODULE_OUTPUT["is_exist"] as $exist) { ?>
        <form method="post" name="is_exist">
			<fieldset>
				<p>
					Преподаватель <?=$exist["last_name"]?>&nbsp;<?=(isset($exist["name"]) ? $exist["name"] : "")?>&nbsp;<?=(isset($exist["patronymic"]) ? $exist["patronymic"] : "")?><?=(isset($exist["dep_identic"]) ? ", приписаный к кафедрам:" : "")?>
<?php			if(isset($exist["dep_identic"])) {
					foreach($exist["department"] as $id => $dep_name) { ?>
					<?=$dep_name?>
<?php				}
				} ?>
					уже существует. Вы уверены, что нужно добавить еще раз?
				</p>
				<p>
					<input type="hidden" value="<?=$exist["last_name"]?>" name="last_name" />
					<input type="hidden" value="<?=$exist["name"]?>" name="first_name" />
					<input type="hidden" value="<?=$exist["patronymic"]?>" name="patronymic" />
					<input type="hidden" value="<?=$exist["male"]?>" name="male" />
					<input type="hidden" value="<?=$MODULE_OUTPUT["is_exist"]["exp_full"]?>" name="exp_full" />
					<input type="hidden" value="<?=$MODULE_OUTPUT["is_exist"]["exp_teach"]?>" name="exp_teach" />
					<input type="hidden" value="<?=$MODULE_OUTPUT["is_exist"]["prad"]?>" name="prad" />
					<textarea class="hidden" name="comment"><?=$MODULE_OUTPUT["is_exist"]["comment"]?></textarea>
					<input type="hidden" value="<?=$MODULE_OUTPUT["is_exist"]["username"]?>" name="username" />
					<input type="hidden" value="<?=$MODULE_OUTPUT["is_exist"]["email"]?>" name="email" />
					<input type="hidden" value="<?=$MODULE_OUTPUT["is_exist"]["password1"]?>" name="password1" />
					<input type="hidden" value="<?=$MODULE_OUTPUT["is_exist"]["password2"]?>" name="password2" />
<?php			if(isset($MODULE_OUTPUT["is_exist"]["department"])) {
					$i = 0;
					foreach($MODULE_OUTPUT["is_exist"]["department"] as $id => $dep_name) { ?>
					<input type="hidden" value="<?=$dep_name?>" name="department[<?=$id?>]" />
<?php					$i++;
					}
				} ?>
					<input type="submit" value="Да" name="check" class="button" />
					<input type="submit" value="Нет" name="check" class="button" />
				</p>
			</fieldset>
        </form>
<?php 		//}
        }
		
		if (isset($MODULE_OUTPUT["allow_handle"]) || count($MODULE_OUTPUT["department"])) {
			if(isset($MODULE_OUTPUT["display_variant"]["add_item"]))
				$display_variant = $MODULE_OUTPUT["display_variant"]["add_item"]; ?>
		<form method="post" action="search_teacher/">
			<fieldset>
				<legend>Найти ранее добавленного преподавателя</legend>
				<div class="wide">
					<p>
						<input type="text" name="search_teacher" class="input_long">
						<input type="submit" class="button" value="Найти">
					</p>
				</div>
			</fieldset>
		</form>
<?php
			}
        } else {
            switch($MODULE_OUTPUT["teachers_mode"]) {
                case "search_teacher":  {
                    //CF::Debug($MODULE_OUTPUT["search_teachers"]);
                    ?>
        <h2>Список найденных преподавателей:</h2>
<?php
                    //Если смотрит зав.кафедры, то так:
					if(isset($MODULE_OUTPUT["auth_usergroup"]) && $MODULE_OUTPUT["auth_usergroup"] == 6) { ?>
		<h3>(отметьте тех, которых хотите добавить к своей кафедре)</h3>
        <form name="add_teachers" method="post">
            <ul>
<?php
                        $i=1;
                        foreach($MODULE_OUTPUT["search_teachers"] as $teacher) {
                            if($teacher["in_current_department"]) { ?>
                <li>[уже добавлен] <?=$teacher["last_name"]?> <?=$teacher["name"]?> <?=$teacher["patronymic"]?></li>
<?php     					} else { ?>
                <li><input type="hidden" name="teachers_id[<?=$i?>][id]" value="<?=$teacher["id"]?>"><input type="checkbox" name="teachers_id[<?=$i?>][check]"> <?=$teacher["last_name"]?> <?=$teacher["name"]?> <?=$teacher["patronymic"]?></li>
<?php 							$i++; 
                            }
                        } ?>
            </ul>
            <input type="hidden" name="department_id" value="<?=$MODULE_OUTPUT["current_department"]?>" />
            <input type="submit" value="Сохранить изменения" />
        </form>
<?php     			} else { ?>
        <h3>Выберите преподавателя:</h3>
        <ul>
<?php 					foreach($MODULE_OUTPUT["search_teachers"] as $teacher) { ?>
            <li><a href="<?=$MODULE_OUTPUT["uri"]?>edit/<?=$teacher["id"]?>/"><?=$teacher["last_name"]?> <?=$teacher["name"]?> <?=$teacher["patronymic"]?></a></li>
<?php     				} ?>
        </ul>
<?php 				}
                }
                break;
			
                case "edit": {
                    //CF::Debug($MODULE_OUTPUT);
                    foreach($MODULE_OUTPUT["edit_teacher"] as $teacher) { ?>
		<script>jQuery(document).ready(function($) {Send_Forms();});</script>
        <form name="edit_teacher" method="post" onsubmit="return sendform(this);" enctype="multipart/form-data">
			<fieldset>
				<legend> 
					Редактирование информации о преподавателе <?=$teacher["last_name"]?> <?=(isset($teacher["name"]) ? $teacher["name"] : "")?> <?=(isset($teacher["patronymic"]) ? $teacher["patronymic"] : "")?>
				</legend>
				<div class="wide">
					<div class="form-notes">
						<h3>Фамилия преподавателя:</h3>
						<p><input type="text" name="last_name" value="<?=$teacher["last_name"]?>" /></p>
						<h3>Имя преподавателя:</h3>
						<p><input type="text" name="first_name" value="<?=$teacher["name"]?>" /></p>
						<h3>Отчество преподавателя:</h3>
						<p><input type="text" name="patronymic" value="<?=$teacher["patronymic"]?>" /></p>
						<h3>Фотография:</h3>
						<p>
							<span id="photoinput"><input name="edit_photo" type="file"/></span>
							<input type="checkbox" name="delete_photo" onclick="if(this.checked) document.getElementById('photoinput').style.display='none'; else document.getElementById('photoinput').style.display='inline';" /> Удалить фотографию
						</p>
						<h3>Пол:</h3>
						<p>
							<input type="radio" name="male" value="1"<?=($teacher["male"] ? " checked=\"checked\"" : "")?> />М / Ж
							<input type="radio" name="male" value="0"<?=($teacher["male"] ? "" : " checked=\"checked\"")?> />
						</p>										
					</div>
					<div class="form-notes">
						<h3>Должность, степень, звание:</h3>
						<p><input type="text" name="prad" class="input_long" value="<?=$teacher["prad"]?>" /></p>
						<h3>Должность:</h3>
						<p>
							<select name="post" size="1" class="select_long">
								<option value="Заведующий"<?=($teacher['post'] == 'Заведующий' ? ' selected="selected"' : '')?>>Заведующий</option>
								<option value="Преподаватель"<?=($teacher['post'] == 'Преподаватель' ? ' selected="selected"' : '')?>>Преподаватель</option>
								<option value="Лаборант"<?=($teacher['post'] == 'Лаборант' ? ' selected="selected"' : '')?>>Лаборант, Старший Лаборант, Врач, Аспирант</option>												
							</select>
						</p>
						<h3>Год начала общего стажа:</h3>
						<p><input type="text" name="exp_full" value="<?=$teacher["exp_full"]?>" /></p>
						<h3>Год начала педагогического стажа:</h3>
						<p><input type="text" name="exp_teach" value="<?=$teacher["exp_teach"]?>" /></p>										
					</div>
				</div>
				<div class="wide">
					<h3>Дополнительная информация:</h3>
					<p><textarea name="comment" class="wysiwyg" cols="50" rows="10"><?=$teacher["comment"]?></textarea></p>									
				</div>
				<div class="wide">
<?php					if(!isset($MODULE_OUTPUT["current_department_id"])) { ?>
					<h3>Выберите кафедру:</h3>
					<p>
						<input type="hidden" name="department[0][old]" value="<?=(isset($teacher["department"][0]) ? $teacher["department"][0] : "0")?>" />
						<select name="department[0][new]" size="1">
<?php 						if(isset($teacher["department"][0])) { ?>
							<option value="0">Кафедра не выбрана</option>
<?php 						} else { ?>
							<option value="0" selected="selected">Выберите кафедру:</option>
<?php     					}
							foreach($MODULE_OUTPUT["faculties"] as $faculties) { ?>
							<optgroup label="<?=$faculties["fac_name"]?>">
<?php							foreach($faculties["departments"] as $department) { ?>
								<option value="<?=$department["id"]?>"<?=((isset($teacher["department"][0]) && $teacher["department"][0] == $department["id"]) ? " selected=\"selected\"" : "")?>><?=$department["name"]?></option>
<?php							} ?>
							</optgroup>
<?php						} ?>
						</select>
					</p>
					<h3>Выберите вторую кафедру(если есть. В этом случае выбранные кафедры должны быть различными):</h3>
					<p>
						<input type="hidden" name="department[1][old]" value="<?=(isset($teacher["department"][1]) ? $teacher["department"][1] : "0")?>" />
						<select name="department[1][new]" size="1">
<?php 						if(isset($teacher["department"][1])) { ?>
							<option value="0">Кафедра не выбрана</option>
<?php 						} else { ?>
							<option value="0" selected="selected">Выберите кафедру:</option>
<?php     					}
							foreach($MODULE_OUTPUT["faculties"] as $faculties) { ?>
							<optgroup label="<?=$faculties["fac_name"]?>">
<?php							foreach($faculties["departments"] as $department) { ?>
								<option value="<?=$department["id"]?>"<?=((isset($teacher["department"][1]) && $teacher["department"][1] == $department["id"]) ? " selected=\"selected\"" : "")?>><?=$department["name"]?></option>
<?php							} ?>
							</optgroup>
<?php						} ?>
						</select>
					</p>
<?php 					} else { ?>
					<p>
						<input type="hidden" name="department[0]" value="<?=$MODULE_OUTPUT["current_department_id"]?>">
					</p>
<?php 					} ?>
					</div>
					<div class="wide">
						<div class="form-notes">
							<h3>Имя пользователя для доступа в систему:</h3>
							<p>
								<input type="hidden" name="isset_user" value="<?=(isset($teacher["username"]) ? 1 : 0)?>" autocomplete="off" />
								<input type="text" name="username" value="<?=(isset($teacher["username"]) ? $teacher["username"] : "")?>" autocomplete="off" />
							</p>
							<h3>Электронная почта (E-mail):</h3>
							<p><input type="text" name="email" value="<?=(isset($teacher["email"]) ? $teacher["email"] : "")?>" autocomplete="off" /></p>
						</div>
						<div class="form-notes">
<?php 					//if(empty($teacher["password"])) { ?>
							<h3>
								Пароль:
								<span id="genpass"></span>
								<span id="copypass">
									<a onclick="copypass('genpass','add_pass','repeatpass');">
										использовать этот пароль<span id="instead" class="instead"></span>
									</a>
								</span>
							</h3>
							<p>
								<input id="add_pass" name="password1" value="<?=(isset($teacher["password"])?$teacher["password"]:'')?>" type="password" autocomplete="off" />
								<a onclick="passgen('genpass',3,1,1,'add_pass');">сгенерировать пароль</a>
							</p>
							<h3>Повторите пароль:</h3>
							<p><input id="repeatpass" type="password" value="<?=(isset($teacher["password"])?$teacher["password"]:'')?>" name="password2" autocomplete="off" /></p>
<?php 					//} ?>
						</div>
					</div>
					<div class="wide">
						<p>
							<input type="hidden" value="edit" name="mode" />
							<input type="hidden" value="<?=$teacher["id"]?>" name="id" />
							<input type="submit" value="Сохранить" class="button" />
						</p>
					</div>
				</fieldset>
            </form>
<?php 				}
                }
                break;
			}
        }
        break;
    }
	
	case "ajax_delete_subj": {
		if(isset($MODULE_OUTPUT["ajax_delete_subj"])) {
			header('Content-Type: text/xml,  charset=windows-1251');
			$dom = new DOMDocument("1.0", "windows-1251");
			$del_subj = $dom->createElement('del_subj');
			$dom->appendChild($del_subj);				
			$status = $dom->createTextNode($MODULE_OUTPUT['ajax_delete_subj']);
			$del_subj->appendChild($status);
			if($MODULE_OUTPUT['error_msg']) {
				$error_msg  = $dom->createElement("error_msg");
				$msg = $dom->createTextNode(CF::Win2Utf($MODULE_OUTPUT['error_msg']));
				$msg = $dom->createCDATASection(CF::Win2Utf($MODULE_OUTPUT['error_msg']));
				$error_msg->appendChild($msg); 
				$del_subj->appendChild($error_msg);
			}
			$xmlString = $dom->saveXML();	
			echo $xmlString;
		}
	}
	break;
    
    case "timetable_subjects": {
        if(!isset($MODULE_OUTPUT["subjects_mode"])) {
            /*if(isset($MODULE_OUTPUT["subjects"])) {
                $id = 0; ?>
        <h2>Список дисциплин по кафедрам:</h2>                
         <ul>
<?php 			foreach($MODULE_OUTPUT["subjects"] as $subject) {
                    if($subject["department_id"] != $id) { ?>
        </ul>
        <li><?=$subject["department_name"]?><ul>
<?php 					$id = $subject["department_id"];
                    } ?>
        <li> <a href="edit/<?=$subject["id"]?>/"><?=$subject["name"]?></a> [<a href="delete/<?=$subject["id"]?>/" onclick="return confirm('Вы уверены, что хотите удалить выбранный элемент?');"}>Удалить</a>]</li>
<?php 			} ?>
        </ul>
<?php 		}*/ ?>
		<script>jQuery(document).ready(function($) {TimetableSubjects(<?=$MODULE_OUTPUT['module_id']?>);});</script>
<?php       if(isset($MODULE_OUTPUT["fac_dep_subj"])) {
                foreach($MODULE_OUTPUT["fac_dep_subj"] as $fac) {
                    if (!empty($fac["dep_list"])) { ?>
		<h2><?=$fac["fac_name"]?></h2>
<?php                   }
                    foreach($fac["dep_list"] as $dep) { ?>
        <h3><?=$dep["dep_name"]?></h3>
		<ul class='subj_list'>
<?php                   foreach($dep["subj_list"] as $subj) { ?>
            <li id='subj_item_<?=$subj["subj_id"]?>'>
				<a href='edit/<?=$subj["subj_id"]?>/'><?=$subj["subj_name"]?></a> [<a href='delete/<?=$subj["subj_id"]?>/' class='delet_subj'>Удалить</a>]
			</li>
<?php					} ?>
        </ul>
<?php				}
                }
            }
            if(isset($MODULE_OUTPUT["nodep_subj"])) { ?>
        <h2>Дисциплины, не привязанные к кафедре</h2>
        <ul class='subj_list'>
<?php           foreach($MODULE_OUTPUT["nodep_subj"] as $nodep_subj) { ?>
            <li id='subj_item_<?=$nodep_subj["subj_id"]?>'>
				<a href='edit/<?=$nodep_subj["subj_id"]?>/'><?=$nodep_subj["subj_name"]?></a> [<a href='delete/<?=$nodep_subj["subj_id"]?>/' class='delet_subj'>Удалить</a>]
			</li>
<?php           } ?>
        </ul>
<?php       } ?>
        <p><?=(isset($MODULE_OUTPUT["error"])) ? "<br /><h3>".$MODULE_MESSAGES[$MODULE_OUTPUT["error"]]."</h3>": ""?></p>
        <h2>Добавить дисциплину:</h2>
        <form  method="post" name="add_subject">
            <h3>Название дисциплины:</h3>
            <input type="text" name="subject_name" class="input_long" />
            <h3>Выберите кафедру, к которой относится дисциплина:</h3>
            <select name="department_id" size="1">
				<option value="0" selected="selected">Выберите кафедру:</option>
<?php  		/*foreach($MODULE_OUTPUT["departments"] as $department) {
                ?>
                <option value="<?=$department["id"]?>"><?=$department["name"]?></option>
                <?php
            }*/
            $id=0;
			foreach($MODULE_OUTPUT["departments"] as $department) {
				if($department["faculty_id"]!=$id) {
					if($id) { ?>
					</optgroup>
<?php 				} ?>
					<optgroup label="<?=$department["faculty_name"]?>">
<?php 				$id=$department["faculty_id"];
				} ?>
				<option value="<?=$department["id"]?>"><?=$department["name"]?></option>
<?php 		} ?>
            </select><br />
            <input type="submit" value="Добавить" class="button" />
        </form>
<?php 	} else {
            switch($MODULE_OUTPUT["subjects_mode"]) {
                case "edit": {
                    foreach($MODULE_OUTPUT["edit_subject"] as $subject) { ?>
		<p><?=(isset($MODULE_OUTPUT["error"])) ? "<br /><h3 class='message'>".$MODULE_MESSAGES[$MODULE_OUTPUT["error"]]."</h3>": ""?></p>
        <h2>Редактирование информации о дисциплине <?=$subject["name"]?>:</h2>
        <form name="edit_subject" method="post">
            <h3>Название дисциплины:</h3>
            <input type="text" name="subject_name" value="<?=$subject["name"]?>" />
            <h3>Кафедра, к которому относится дисциплина:</h3>
            <select name="department">
<?php 					foreach($MODULE_OUTPUT["departments"] as $department) {
                            if($department["id"] == $subject["department_id"]) { ?>
				<option value="<?=$department["id"]?>" selected="selected"><?=$department["name"]?></option>
<?php 						} else { ?>
                <option value="<?=$department["id"]?>"><?=$department["name"]?></option>
<?php 						}
                        } ?>
            </select><br />
            <input type="hidden" value="edit" name="mode" />
            <input type="hidden" value="<?=$subject["id"]?>" name="id" />
            <input type="submit" value="Сохранить" class="button" />
        </form>
<?php 				}
                }
                break;
            }
        }
    }
    break;
	
    case "timetable_auditorium": { ?>
		<script>jQuery(document).ready(function($) {TimetableAuditorium();});</script>
		<!--script type="text/javascript" src="<?=HTTP_COMMON_SCRIPTS?>jquery.editable-select.js"></script-->
<?php 	$type = array("Открытый", "Закрытый", "Закрытый специальный");
        $using = array("Используется", "Ремонт", "ЧП");
        if(!isset($MODULE_OUTPUT["auditorium_mode"])) {
            if(isset($MODULE_OUTPUT["auditorium"])) {
                $id = 0; ?>
        <h2>Список аудиторий по корпусам:</h2>
		<ul>
<?php 			foreach($MODULE_OUTPUT["auditorium"] as $build) { ?>
			<li>
				<?=$build['bild_name']?>
<?php				if(isset($build['bild_audit'])) { ?>
				<ul>
<?php					foreach($build['bild_audit'] as $audit) { ?>
					<li>
						<a href="edit/<?=$audit["id"]?>/"<?php if($audit["multimedia"]) {?> title="Установлено мультимедийное оборудование"<?php } ?>><?=$build["bild_label"]?> - <?=$audit["name"]?></a> Вместительноть: <?=$audit["roominess"]?>;&nbsp;
<?php 						for($i=0; $i<3; $i++) {
								if($i == $audit["type"]) { ?>
                        Тип аудитории: <?=$type[$i]?>;
<?php 							}
							} ?>
						Состояние: <?=$using[$audit["is_using"]]?>;&nbsp;
						<?php if($audit["multimedia"]) {?>Мультимедиа: Есть;<?php } ?>
						[<a href="delete/<?=$audit["id"]?>/" onclick="return confirm('Вы уверены, что хотите удалить выбранный элемент?');"}>Удалить</a>]
					</li>
<?php					} ?>
				</ul>
<?php				} ?>				
			</li>
<?php			} ?>
			
		</ul>
<?php 		} ?>
        <h2>Добавить аудиторию:</h2>
        <form onsubmit="$(this).find('input.editable-select').attr('name', '');" method="post" name="add_auditorium">
			<h3>Номер аудитории:</h3>
			<input type="text" name="auditorium_name" />
			<h3>Выберите корпус, в котором расположена данная аудитория:</h3>
			<select name="building_id" size="1">
				<option value="0" selected="selected">Выберите корпус:</option>
<?php 		foreach($MODULE_OUTPUT["buildings"] as $building) { ?>
				<option value="<?=$building["id"]?>"><?=$building["name"]?></option>
<?php 		} ?>
			</select>
			<h3>Введите вместительность аудитории:</h3>
			<input type="text" name="roominess" />
			<h3>Кафедра, к которой приписана аудитория (если есть):</h3>
			<select name="department_id" size="1" class="editable-select">
				<option value="0" selected="selected">Выберите кафедру:</option>
<?php 		foreach($MODULE_OUTPUT["departments"] as $department) { ?>
				<option value="<?=$department["id"]?>"><?=$department["name"]?></option>
<?php 		} ?>
			</select>
			<h3>Выберите тип аудитории:</h3>
			<select name="type" size="1">
<?php 		for($i=0; $i<3; $i++) { ?>
				<option value="<?=$i?>"><?=$type[$i]?></option>
<?php 		} ?>
			</select>
			<h3>Выберите состояние аудитории:</h3>
			<select name="is_using" size="1">
<?php 		for($i=0; $i<3; $i++) { ?>
				<option value="<?=$i?>"><?=$using[$i]?></option>
<?php 		} ?>
			</select><br />
            <input type="checkbox" name="multimedia" value="1" /> Использует мультимедийное оборудование<br />
			<input  type="submit" value="Добавить" class="button" />
        </form>
<?php 	} else {
            switch($MODULE_OUTPUT["auditorium_mode"]) {
                case "edit": {
                    foreach($MODULE_OUTPUT["edit_auditorium"] as $auditorium) { ?>
        <h2>Редактирование информации об аудитории <?=$auditorium["name"]?>:</h2>
        <form name="edit_auditorium" method="post">
            <h3>Номер аудитории:</h3>
            <input type="text" name="auditorium_name" value="<?=$auditorium["name"]?>" />
            <h3>Корпус, в котором расположена аудитория:</h3>
            <select name="building">
<?php 					foreach($MODULE_OUTPUT["buildings"] as $building) {
                            if($building["id"] == $auditorium["building_id"]) { ?>
                <option value="<?=$building["id"]?>" selected="selected"><?=$building["name"]?></option>
<?php 						} else { ?>
                <option value="<?=$building["id"]?>"><?=$building["name"]?></option>
<?php 						}
                        } ?>
            </select>
            <h3>Введите вместительность аудитории:</h3>
            <input type="text" name="roominess" value="<?=$auditorium["roominess"]?>" />
            <h3>Кафедра, к которой приписана аудитория (если есть):</h3>
            <select name="department_id" class="editable-select">
<?php 					if(!$auditorium["department_id"]) { ?>
				<option selected="selected" value="0">Выберите кафедру</option>
<?php 					} else { ?>
                <option value="0">Нет кафедры</option>
<?php     				}
                        foreach($MODULE_OUTPUT["departments"] as $department) {
                            if($department["id"] == $auditorium["department_id"]) { ?>
                <option selected="selected" value="<?=$department["id"]?>"><?=$department["name"]?></option>
<?php 						} else { ?>
                <option value="<?=$department["id"]?>"><?=$department["name"]?></option>
<?php 						}
                        } ?>
            </select>
            <h3>Тип аудитории:</h3>
            <select name="type" size="1">
<?php 					for($i=0; $i<3; $i++) {
                            if($i == $auditorium["type"]) { ?>
                <option value="<?=$i?>" selected="selected"><?=$type[$i]?></option>
<?php 						} else { ?>
                <option value="<?=$i?>"><?=$type[$i]?></option>
<?php     					}
                        } ?>
            </select>
            <h3>Состояние аудитории:</h3>
            <select name="is_using" size="1">
<?php 					for($i=0; $i<3; $i++) {
                            if($i == $auditorium["is_using"]) { ?>
                <option value="<?=$i?>" selected="selected"><?=$using[$i]?></option>
<?php 						} else { ?>
                <option value="<?=$i?>"><?=$using[$i]?></option>
<?php     					}
                        } ?>
            </select><br />
            <input type="checkbox" name="multimedia" value="1" <?=($auditorium["multimedia"] ? " checked=\"checked\"" : "") ?>/> Использует мультимедийное оборудование<br />
            <input type="hidden" value="edit" name="mode" />
            <input type="hidden" value="<?=$auditorium["id"]?>" name="id" />
            <input type="submit" value="Сохранить" class="button" />
        </form>
<?php  				}
                }
                break;
            }
        }
    }   
    break;
    
    case "timetable_show": { ?>
        <h1>Расписание</h1>
		<div class="faculties_groups_block">
<?php   foreach($MODULE_OUTPUT["faculties"] as $faculty) { ?>
			<div class="faculties_groups">
			    <h2><span><?=$faculty["name"]?></span><em></em></h2>
			    <div class="table_groups">
					<table>
						<thead>
							<tr>
								<th>Курс</th>
								<th colspan="6">Группы</th>
							</tr>
						</thead>
						<tbody>
<?php		for($i = 1; $i < 7; $i++) { ?>
							<tr>
								<th><?=$i?></th>
<?php			$td_num = 0;
				if(isset($faculty["groups"]) && isset($faculty["groups"][$i])) {					
                    foreach($faculty["groups"][$i] as $group) {
						$td_num++; ?>
							    <td><a href="groups/<?=$group["id"]?>/"><?=$group["name"]?></a></td>
<?php				}
                }
				for(;$td_num < 6; $td_num++) { ?>
								<td></td>
<?php			} ?>
							</tr>   
<?php       } ?>
						</tbody>
					</table>
				</div>
			</div>
<?php   } ?>
		</div>
<?php   break;
    }
    
    case "groups_list": { ?>
		<script>jQuery(document).ready(function($) {Send_Forms();});</script>
<?php   if(isset($MODULE_OUTPUT["faculty_list"])) { ?>
        <h2>Добавление группы:</h2>
        <form name="new_group" method="post">
			<h3>Введите номер группы:</h3>
			<input type="text" name="group_name" />
			<h3>Выберите факультет</h3>
			<select name="faculty">
<?php		foreach($MODULE_OUTPUT["faculty_list"] as $faculty) { ?>
				<option value="<?=$faculty["id"]?>"><?=$faculty["name"]?></option>
<?php		} ?>
			</select><br />
			<h3>Курс</h3>
			<select name="year">
				<option value="1">Первый курс</option>
				<option value="2">Второй курс</option>
				<option value="3">Третий курс</option>
				<option value="4">Четвёртый курс</option>
				<option value="5">Пятый курс</option>
				<option value="6">Шестой курс</option>
			</select><br />
			<input type="submit" value="Добавить группу" />
        </form>
<?php	}
        if(isset($MODULE_OUTPUT["faculties"])) { ?>
		<a href="add_group/">Добавить группу.</a>
		<div class="faculties_groups_block">
<?php   foreach($MODULE_OUTPUT["faculties"] as $faculty) { ?>
			<div class="faculties_groups">
				<h2><span><?=$faculty["name"]?></span><em></em></h2>
				<div class="table_groups">
					<table>
						<thead>
							<tr>
								<th>Курс</th>
								<th colspan="6">Группы</th>
							</tr>
						</thead>
						<tbody>
<?php		for($i = 1; $i < 7; $i++) { ?>
							<tr>
								<th><?=$i?></th>
<?php			$td_num = 0;
				if(isset($faculty["groups"]) && isset($faculty["groups"][$i])) {					
                    foreach($faculty["groups"][$i] as $group) {
						$td_num++; ?>
								<td><a href="<?=$group["id"]?>/"><?=$group["name"]?></a></td>
<?php				}
                }
				for(;$td_num < 6; $td_num++) { ?>
								<td></td>
<?php			} ?>
							</tr>   
<?php       } ?>
						</tbody>
					</table>
				</div>
			</div>
<?php   } ?>
		</div>
<?php	}
        if(isset($MODULE_OUTPUT["students_list"]) || isset($MODULE_OUTPUT["students_list_mode"])) { ?>
        <a href="edit_group/">Редактировать группу.</a><br />
        <a href="delete_group/" onclick="return confirm('Вы уверены, что хотите удалить выбранный элемент?');">Удалить группу(это возможно сделать только после отчисления всех студентов или перевода их в другую групу).</a>
        <h2>Список студентов
<?php		if(isset($MODULE_OUTPUT["group_name"])) {
                echo ", числящихся в группе ";
                echo $MODULE_OUTPUT["group_name"];
            }
            if(isset($MODULE_OUTPUT["faculty_name"])) {
                echo "(".$MODULE_OUTPUT["faculty_name"].")";
            } ?>
            :
		</h2>
<?php 		if(isset($MODULE_OUTPUT["students_list"])) {
				foreach($MODULE_OUTPUT["students_list"] as $student) { ?>
        <a href="<?=$student["id"]?>/"><?=$student["last_name"]?> <?=$student["name"]?> <?=$student["patronymic"]?></a><br />
<?php  				$id_speciality = $student["id_speciality"];
				}
            } ?>
        <h2>Добавление студента в группу:</h2>
        <form name="add_student" method="post" onsubmit="return sendform(this);">
			<h3>Фамилия:</h3>
			<input type="text" name="last_name" />
			<h3>Имя:</h3>
			<input type="text" name="first_name" />
			<h3>Отчество:</h3>
			<input type="text" name="patronymic" />
			<input type="hidden" name="group_id" value="<?=$MODULE_OUTPUT["group_id"]?>" />
			<h3>Пол:</h3>
			<input type="radio" value="1" name="male" />М / Ж<input type="radio" value="0" name="male" />
			<h3>Специальность:</h3>
			<select name="id_speciality">
<?php 		if(isset($MODULE_OUTPUT["specialities"])) {
                if(!isset($id_speciality)) { ?>
                <option value="0" selected="selected" disabled="disabled">Выберите специальность</option>
<?php 			}
                foreach($MODULE_OUTPUT["specialities"] as $speciality) {
                    if(isset($id_speciality)) {
                        if($id_speciality == $speciality["code"]) { ?>
                <option value="<?=$id_speciality?>" selected="selected"><?=$speciality["name"]?> (<?=$speciality["code"]?>)</option>
<?php 					} else { ?>
                <option value="<?=$speciality["code"]?>"><?=$speciality["name"]?> (<?=$speciality["code"]?>)</option>
<?php 					}
                    } else { ?>
                <option value="<?=$speciality["code"]?>"><?=$speciality["name"]?> (<?=$speciality["code"]?>)</option>
<?php 				}
                }
            } ?>
			</select>
			<h3>Год поступления:</h3>
			<input type="text" name="year" />
			<h3>Подгруппа (если есть):</h3>
			<input type="radio" value="а" name="subgroup" />а / б<input type="radio" value="б" name="subgroup" />
			<h3>Имя пользователя для доступа в систему:</h3>
			<input type="text" name="username" />
			<h3>Электронная почта (E-mail):</h3>
			<input type="text" name="email" />
			<h3>Пароль:</h3>
			<input type="password" name="password1" />
			<h3>Повторите пароль:</h3>
			<input type="password" name="password2" /><br />
			<input type="submit" value="Добавить студента в группу" class="button" /> 
        </fomr>
<?php   }
        if(isset($MODULE_OUTPUT["edit_group"])) {
            foreach($MODULE_OUTPUT["edit_group"] as $group) { ?>
			<h2>Редактирование информации о группе <?=$group["name"]?> (<?=$group["faculty_name"]?>):</h2>
            <form name="edit_group" method="post">
                <h3>Название группы:</h3>
                <input type="text" name="group_name" value="<?=$group["name"]?>" />
                <h3>Факультет, к которому относится группа:</h3>
                <select name="faculty">
<?php 			foreach($MODULE_OUTPUT["faculties_edit_group"] as $faculty) {
                    if($faculty["id"] == $group["id_faculty"]) { ?>
                    <option value="<?=$faculty["id"]?>" selected="selected"><?=$faculty["name"]?></option>
<?php  				} else { ?>
                    <option value="<?=$faculty["id"]?>"><?=$faculty["name"]?></option>
<?php 				}
                } ?>
                </select>
                <input type="hidden" value="edit" name="mode" />
                <input type="hidden" value="<?=$group["id"]?>" name="id" />
                <h3>Курс</h3>
                <input type="text" name="year" value="<?=$group["year"]?>" /><br />
                <input type="submit" value="Сохранить" class="button" />
            </form>
<?php 		}
        }
        if(isset($MODULE_OUTPUT["edit_student"])) {
            $student = $MODULE_OUTPUT["edit_student"]; ?>
        <h2>Редактирование информации о студенте:</h2>
        <a href="expulsion/" onclick="return confirm('Вы уверены, что хотите отчислить данного студента?');">Отчислить студента.</a><br />
        <a href="marks/">Просмотр оценок</a>
        <input type="hidden" name="id_student" value="<?=$student["id"]?>" />
        <form name="edit_student" method="post" onsubmit="return sendform(this);">
            <h3>Фамилия:</h3>
            <input type="text" name="last_name" value="<?=$student["last_name"]?>" />
            <h3>Имя:</h3>
            <input type="text" name="first_name" value="<?=$student["name"]?>" />
            <h3>Отчество:</h3>
            <input type="text" name="patronymic" value="<?=$student["patronymic"]?>" />
            <h3>Пол:</h3>
<?php 		if(isset($student["male"])) {
                if($student["male"]) { ?>
            <input type="radio" value="1" name="male" checked="checked" />М / Ж<input type="radio" value="0" name="male" />
<?php 			} else { ?>
            <input type="radio" value="1" name="male" />М / Ж<input type="radio" value="0" name="male" checked="checked" />
<?php 			}
            } else { ?>
            <input type="radio" value="1" name="male" />М / Ж<input type="radio" value="0" name="male" />
<?php 		} ?>
            <h3>Специальность:</h3>
            <select name="id_speciality">
<?php 		if(isset($MODULE_OUTPUT["specialities"])) {           
                foreach($MODULE_OUTPUT["specialities"] as $speciality) {
                    //if(isset($student["id_speciality"]))
                    {
                        //if($student["id_speciality"] == $speciality["id_speciality"])
                    if($student["id_speciality"] == $speciality["code"]) { ?>
                <option value="<?=$speciality["code"]?>" selected="selected"><?=$speciality["name"]?> (<?=$speciality["code"]?>)</option>          
<?php 				} else { ?>
                <option value="<?=$speciality["code"]?>"><?=$speciality["name"]?> (<?=$speciality["code"]?>)</option>
<?php 				}
                    }
                    /*else { ?>
                <option value="<?=$student["id_speciality"]?>"><?=$speciality["name"]?> (<?=$speciality["code"]?>)</option>
<?php 				}*/
                }
            } ?>
            </select>
            <h3>Год поступления:</h3>
            <input type="text" name="year" value="<?=$student["year"]?>" />
            <h3>Группа:</h3>
            <select name="group">
<?php 		foreach($MODULE_OUTPUT["faculties_list"] as $faculty) { ?>
                <optgroup label="<?=$faculty["name"]?>">
<?php 			foreach($faculty["groups"] as $group) {
                    if($student["id_group"] == $group["id"]) { ?>
                    <option value="<?=$group["id"]?>" selected="selected"><?=$group["name"]?></option>          
<?php 				}  else { ?>
                    <option value="<?=$group["id"]?>"><?=$group["name"]?></option>
<?php 				}    
                } ?>
                </optgroup>
<?php 		} ?>
            </select>
            <h3>Подгруппа (если есть):</h3>
<?php 		if(isset($student["male"])) {
                if($student["subgroup"] == "а") { ?>
            <input type="radio" value="а" name="subgroup" checked="checked" />а / б<input type="radio" value="б" name="subgroup" />
<?php 			} else { ?>
            <input type="radio" value="а" name="subgroup" />а / б<input type="radio" value="б" name="subgroup" checked="checked" />
<?php 			}
            } else { ?>
            <input type="radio" value="а" name="subgroup" />а / б<input type="radio" value="б" name="subgroup" />
<?php 		} ?>
            <h3>Имя пользователя для доступа в систему:</h3>
<?php 		if(isset($student["username"])) { ?>
            <input type="text" name="username" value="<?=$student["username"]?>" />
<?php 		} else { ?>
            <input type="text" name="username" value="" />
<?php     	} ?>
            <br />
            <h3>Электронная почта (E-mail):</h3>
<?php 		if(isset($student["email"])) { ?>
            <input type="text" name="email" value="<?=$student["email"]?>" />
<?php 		} else { ?>
            <input type="text" name="email" value="" />
<?php 		} ?>
            <br />
<?php 		if(/*empty($student["password"])*/1) { ?>
            <h3>Пароль:</h3>
            <input type="password" name="password1" />
            <h3>Повторите пароль:</h3>
            <input type="password" name="password2" />
<?php 		} ?>
            <br />
            <input type="submit" value="Сохранить изменения" class="button" /> 
        </fomr>
<?php 	}
        if(isset($MODULE_OUTPUT["marks"])) {
            $marks = $MODULE_OUTPUT["marks"];
            $months = array("января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря");
            $year = date("Y");
            $month = date("n");
            $day = date("j"); ?>
        <h2><?=$MODULE_OUTPUT["student_last_name"]?> <?=$MODULE_OUTPUT["student_name"]?> <?=$MODULE_OUTPUT["student_patronymic"]?></h2>
<?php 		if(!empty($MODULE_OUTPUT["marks"])) { ?>
        <h2>Просмотр оценок студента</h2>
        <table class="timetable">
            <tr class="table_top">
				<th>Название дисциплины</th>
				<th>Оценка</th>
				<th>Дата</th>
				<th>Действие</th>
			</tr>
<?php 			$i=1;
				foreach($MODULE_OUTPUT["marks"] as $mark) {
					if($mark["term"] == $i) { ?>
            <tr class="week_day">
				<th colspan="4">Семестр <?=$i?></th>
			</tr>
<?php 					$i++;
					} ?>
            <tr>
				<td><?=$mark["subject"]?></td>
				<td><?=$mark["mark"]?></td>
				<td><?=$mark["date"]?></td>
				<td><a href="edit/<?=$mark["id"]?>/">Редактировать</a> / <a href="delete/<?=$mark["id"]?>/" onclick="return confirm('Вы уверены, что хотите удалить выбранный элемент?');">Удалить</a></td>
			</tr>
<?php 			} ?>
        </table>
<?php 		} ?>
        <h2>Добавить оценку</h2>
        <form method="post">
            <input type="hidden" name="student_id" value="<?=$MODULE_OUTPUT["student_id"]?>" />
            <h3>Название дисциплины:</h3>
            <select name="subject">
				<option value="0">Выберите дисциплину</option>
<?php 		foreach($MODULE_OUTPUT["subjects"] as $subject) { ?>
                <option value="<?=$subject["id"]?>"><?=$subject["name"]?></option>
<?php 		} ?>
            </select>
            <h3>Оценка (или отметка о зачете)</h3>
            <select name="mark">
				<option selected="selected" disabled="disabled" value="0">Выберите оценку</option>
<?php 		for($k=2; $k<6; $k++) { ?>
                <option value="<?=$k?>"><?=$k?></option>
<?php 		} ?>
				<option value="Зачтено">Зачтено</option>
				<option value="Не зачтено">Не зачтено</option>
            </select><br />
            <h3>Дата</h3>
            <select name="day">
<?php 		for($i=1; $i<=31; $i++) {
                if($i != $day) { ?>
                <option value="<?=$i?>"><?=$i?></option>
<?php 			} else { ?>
                <option value="<?=$i?>" selected="selected"><?=$i?></option>
<?php 			}
            } ?>
            </select>
            <select name="month">
<?php 		for($i=0; $i<12; $i++) {
                if($i+1 != $month) { ?>
                <option value="<?=$i+1?>"><?=$months[$i]?></option>
<?php 			} else { ?>
                <option value="<?=$i+1?>" selected="selected"><?=$months[$i]?></option>
<?php     		}
            } ?>
            </select>
            <select name="year">
<?php 		for($i=$year-5; $i<$year; $i++) { ?>
                <option value="<?=$i?>"><?=$i?></option>
<?php 		} ?>
				<option selected="selected"><?=$year?></option>
            </select><br />
            <h3>Семестр</h3>
            <select name="term">
				<option selected="selected" disabled="disabled">Выберите семестр</option>
<?php 		for($k=1; $k<13; $k++) { ?>
                <option value="<?=$k?>"><?=$k?></option>
<?php 		} ?>
            </select><br />
            <input type="submit" value="Добавить запись" class="button" />
        </form>
<?php 	}
        if(isset($MODULE_OUTPUT["edit_mark"])) {
            $mark = $MODULE_OUTPUT["edit_mark"];
            $months = array("января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря");
            $year = date("Y"); ?>
        <h2>Редактирование записи:</h2>
        <form method="post" name="edit_mark">
			<input type="hidden" name="mark_id" value="<?=$mark["id"]?>" />
            <h3>Название дисциплины:</h3>
            <select name="subject">
<?php 		foreach($MODULE_OUTPUT["subjects"] as $subject) {
                if($subject["id"] == $mark["subject_id"]) { ?>
                <option value="<?=$subject["id"]?>" selected="selected"><?=$subject["name"]?></option>
<?php 			} else { ?>
                <option value="<?=$subject["id"]?>"><?=$subject["name"]?></option>
<?php 			}
            } ?>
            </select>
            <h3>Оценка (или отметка о зачете)</h3>
            <select name="mark">
<?php 		for($k=2; $k<6; $k++) {
                if($mark["mark"]==$k) { ?>
                <option value="<?=$k?>" selected="selected"><?=$k?></option>
<?php 			} else { ?>
                <option value="<?=$k?>"><?=$k?></option>
<?php 			}
            }
            if($mark["mark"]=="Зачтено") { ?>
                <option value="Зачтено" selected="selected">Зачтено</option>
                <option value="Не зачтено">Не зачтено</option>
<?php 		} else {
				if($mark["mark"]=="Не зачтено") { ?>
                <option value="Зачтено">Зачтено</option>
                <option value="Не зачтено" selected="selected">Не зачтено</option>
<?php 			} else { ?>
                <option value="Зачтено">Зачтено</option>
                <option value="Не зачтено">Не зачтено</option>
<?php 			}
            } ?>
            </select>
            <h3>Дата:</h3>
            <select name="day">
<?php 		for($i=1; $i<=31; $i++) {
                if($i != $mark["day"]) { ?>
                <option value="<?=$i?>"><?=$i?></option>
<?php 			} else { ?>
                <option value="<?=$i?>" selected="selected"><?=$i?></option>
<?php 			}
            } ?>
            </select>
            <select name="month">
<?php 		for($i=0; $i<12; $i++) {
                if($i+1 != $mark["month"]) { ?>
                <option value="<?=$i+1?>"><?=$months[$i]?></option>
<?php 			} else { ?>
                <option value="<?=$i+1?>" selected="selected"><?=$months[$i]?></option>
<?php     		}
            } ?>
            </select>
            <select name="year">
<?php 		for($i=$year-10; $i<=$year; $i++) {
                if($i != $mark["year"]) { ?>
                <option value="<?=$i?>"><?=$i?></option>
<?php 			} else { ?>
                <option value="<?=$i?>" selected="selected"><?=$i?></option>
<?php     		}
            } ?>
            </select><br />
            <h3>Семестр</h3>
            <select name="term">
<?php 		for($k=1; $k<13; $k++) {
                if($mark["term"]==$k) { ?>
                <option value="<?=$k?>" selected="selected"><?=$k?></option>    
<?php 			} else { ?>
                <option value="<?=$k?>"><?=$k?></option>
<?php 			}
            } ?>
            </select> <br />
            <input type="submit" value="Сохранить данные" class="button" />
        </form>
<?php 	}
    }
    break;
    
    case "timetable_subjects_by_departments": {
		//if($Engine->OperationAllowed($MODULE_OUTPUT['module_id'], "nsau.js.access", -1, $Auth->usergroup_id)) { ?>
		<script>jQuery(document).ready(function($) {TimetableSubjectsByDepartments();InputCalendar();});</script>
<?php   //}
		if(isset($MODULE_OUTPUT["departments_list"])) { ?>
		<ul>
<?php		foreach($MODULE_OUTPUT["departments_list"] as $faculty) { ?>
			<li>
				<?=$faculty['fname']?>
<?php			if(count($faculty['dep_list'])) { ?>
				<ul>
<?php				foreach($faculty['dep_list'] as $dep_id => $department) { ?>
					<li><a href="show/<?=$dep_id?>/"><?=$department?></a></li>
<?php				} ?>
				</ul>
<?php			} ?>
			</li>	
<?php		} ?>
		</ul>
<?php	}
        $rate = array('0.25', '0.5', '0.75', '1.0', '1.25', '1.5');
        if(isset($MODULE_OUTPUT["show_mode"]) && $MODULE_OUTPUT["show_mode"]=="show") {
            if(isset($MODULE_OUTPUT["subjects_by_departments_list"]))  {
                $department = str_replace("Кафедра", "Кафедры", $MODULE_OUTPUT["department_name"]); ?>
        <h1>Распределение дисциплин между ППС <?=$department?></h1>
        <table class="timetable">
            <tr class="table_top">
				<th rowspan="4"><img src="/themes/images/delete.png"></th>
				<th rowspan="4">Дисциплина</th>
				<th rowspan="4">Ф-т</th>
				<th rowspan="4">К<br />у<br/>р<br/>с</th>
                <th colspan="2">Ф.И.О. преподавателя</th>
				<th colspan="2">В каких</th>
				<th rowspan="4">№ ауд.</th>
                <th colspan="5">Дополнительные сведения о преподавателе</th>
				<th rowspan="4">Примечания</th>
				<th rowspan="4">С<br />т<br />а<br />в<br />к<br />а</th>
			</tr>
            <tr class="table_top">
				<th rowspan="3">лекции</th>
				<th rowspan="3">практики</th>
				<th rowspan="3">группах</th>
                <th rowspan="3">подгруппах</th>
				<th colspan="4">Участие в работе(день, пара)</th>
                <th rowspan="3">Ш<br />т<br />а<br />т<br />н<br />ы<br />й</th>
			</tr>  
            <tr class="table_top">
                <th colspan="3">планерок</th>
                <th rowspan="2">ученого совета НГАУ</th>
            </tr>
            <tr class="table_top">
				<th><span title="ректорской">р</span></th>
				<th><span title="проректорской">п</span></th>
				<th><span title="деканатской">д</span></th>
			</tr>
<?php 			$i=0;
                foreach($MODULE_OUTPUT["subjects_by_departments_list"] as $data) {
                    $i++; ?>
            <tr>
                <td><a href="delete/<?=$data["id"]?>/" onclick="return confirm('Вы уверены, что хотите удалить выбранный элемент?');"><img src="/themes/images/delete.png"></a></td>
				<td><a href="edit/<?=$data["id"]?>/"><?=$data["subject"]?></a></td>
				<td><span title="<?=$data["faculty"]?>"><?=$data["faculty_short"]?></span></td>
				<td><?=$data["year"]?></td>
<?php 				if(!$data["type"]) { ?>
                <td><?=$data["last_name"]?> <?=$data["name"]?> <?=$data["patronymic"]?></td>
				<td>&nbsp;</td>
<?php 				} else {
                        if($data["type"]==1) { ?>
                <td>&nbsp;</td>
				<td><?=$data["last_name"]?> <?=$data["name"]?> <?=$data["patronymic"]?></td>
<?php 					} else { ?>
                <td><?=$data["last_name"]?> <?=$data["name"]?> <?=$data["patronymic"]?></td>
				<td><?=$data["last_name"]?> <?=$data["name"]?> <?=$data["patronymic"]?></td>
<?php     				}
                    } ?>
                <td><?=$data["groups"]?></td>
				<td><?=$data["subgroups"]?></td>
				<td><?=$data["auditorium"]?></td>
                <td><?=($data["rectors"] ? "+" : "&nbsp;")?></td>
                <td><?=($data["prorectors"] ? "+" : "&nbsp;")?></td>
                <td><?=($data["decanats"] ? "+" : "&nbsp;")?></td>
                <td><?=($data["academic_senate"] ? "+" : "&nbsp;")?></td>
                <td><?=($data["state"] ? "+" : "&nbsp;")?></td>
                <td><?=$data["comments"]?></td>
                <td><?=$data["rate"]?></td>
            </tr>
<?php 			} ?>
        </table>
<?php 		}
            if(!isset($MODULE_OUTPUT["subjects_list"])) { ?>
        <h3>К выбранной кафедре не приписано дисциплин. Сначала добавьте дисциплину.</h3>
<?php 		}
            if(isset($MODULE_OUTPUT["subjects_list"])) { ?>
        <h2>Добавить запись в распределение</h2>
        <form method="post" onsubmit="return sendform_sbd(this);">
            <h3>Выберите дисциплину:</h3>
            <select name="subject">
				<option value="0" selected="selected" disabled="disabled">Выберите дисциплину</option>
<?php 			foreach($MODULE_OUTPUT["subjects_list"] as $subject) { ?>
                <option value="<?=$subject["id"]?>"><?=$subject["name"]?></option>
<?php 			} ?>
            </select>
            <h3>Выберите факультет</h3>
            <select name="faculty">
				<option value="0" selected="selected" disabled="disabled">Выберите факультет</option>
<?php 			foreach($MODULE_OUTPUT["faculty_list"] as $faculty) { ?>
                <option value="<?=$faculty["id"]?>"><?=$faculty["name"]?></option>
<?php 			} ?>
            </select>
            <h3>Выберите курс:</h3>
            <select name="year">
<?php 			for($i=1; $i<=6; $i++) { ?>
                <option value="<?=$i?>">Курс <?=$i?></option>
<?php 			} ?>
            </select>
            <h3>Выберите преподавателя:</h3>
            <select name="teacher_id">
				<option value="0" selected="selected" disabled="disabled">Выберите преподавателя</option>
<?php 			foreach($MODULE_OUTPUT["teachers_list"] as $teacher) { ?>
                <option value="<?=$teacher["id"]?>"><?=$teacher["last_name"]?> <?=$teacher["name"]?> <?=$teacher["patronymic"]?></option>
<?php 			} ?>
            </select>
            <h3>Лекции и (или) практики:</h3>
            <input type="radio" name="type" value="2" checked="checked" />Лекции и практики<br />
            <input type="radio" name="type" value="0" />Лекции / Практики <input type="radio" name="type" value="1" />
            <h3>В группах (группы вводятся через точку с запятой):</h3>
            <input type="text" name="groups" />
            <h3>В подгруппах (группы вводятся через точку с запятой):</h3>
            <input type="text" name="subgroups" />
            <h3>Номера аудиторий (вводятся через точку с запятой):</h3>
            <input type="text" name="auditorium" />
            <h3>C:</h3>
            <input name='date_from' type="text" class="date_time"  value="2012-01-01" />
            <h3>По:</h3>
            <input name='date_to' type="text" class="date_time"  value="2012-12-31" />
            <h3>Участие в работе планерок:</h3> 
            <input type="checkbox" name="rectors" /> ректорской<br />
            <input type="checkbox" name="prorectors" /> проректорской<br />
            <input type="checkbox" name="decanats" /> деканатской<br />
            <input type="checkbox" name="academic_senate" />Участие в работе ученого совета НГАУ<br />
            <h3>Является ли штатным преподавателем:</h3>
            <input type="radio" name="state" value="0" />Нет / Да <input type="radio" name="state" value="1" checked="checked"/>
            <h3>Размер занимаемой ставки</h3>
            <select name="rate">
				<option selected="selected" value="0" disabled="disabled">Выберите размер занимаемой ставки</option>
<?php 			foreach($rate as $r) { ?>
                <option value="<?=$r?>"><?=$r?></option>
<?php  			} ?>
            </select>
            <h3>Добавить примечания:</h3>
            <input type="text" name="comments" /><br />
            <input type="submit" value="Добавить" class="button" />
        </form>
<?php 		}
        }
        if(isset($MODULE_OUTPUT["show_mode"]) && $MODULE_OUTPUT["show_mode"]=="edit") {
            $data = $MODULE_OUTPUT["edit_row"]; ?>
        <h2>Редактирование записи</h2>
        <form method="post" onsubmit="return sendform_sbd();">
            <input type="hidden" name="id" value="<?=$data["id"]?>">
            <h3>Выберите дисциплину:</h3>
            <select name="subject">
				<option value="0" selected="selected" disabled="disabled">Выберите дисциплину</option>
<?php 		foreach($MODULE_OUTPUT["subjects_list"] as $subject) {
                if($subject["id"] == $data["subject_id"]) { ?>
                <option selected="selected" value="<?=$subject["id"]?>"><?=$subject["name"]?></option>
<?php 			} else { ?>
                <option value="<?=$subject["id"]?>"><?=$subject["name"]?></option>
<?php 			}
            } ?>
            </select>
            <h3>Выберите факультет</h3>
            <select name="faculty">
				<option value="0" selected="selected" disabled="disabled">Выберите факультет</option>
<?php 		foreach($MODULE_OUTPUT["faculty_list"] as $faculty) {
                if($faculty["id"] == $data["faculty_id"]) { ?>
                <option selected="selected" value="<?=$faculty["id"]?>"><?=$faculty["name"]?></option>
<?php 			} else { ?>
                 <option value="<?=$faculty["id"]?>"><?=$faculty["name"]?></option>
<?php 			}
            } ?>
            </select>
            <h3>Выберите курс:</h3>
            <select name="year">
<?php 		for($i=1; $i<=6; $i++) {
                if($i == $data["year"]) { ?>
                <option selected="selected" value="<?=$i?>">Курс <?=$i?></option>
<?php 			} else { ?>
                <option value="<?=$i?>">Курс <?=$i?></option>
<?php 			}
            } ?>
            </select>
            <h3>Выберите преподавателя:</h3>
            <select name="teacher_id">
				<option value="0" selected="selected">Выберите преподавателя</option>
<?php 		foreach($MODULE_OUTPUT["teachers_list"] as $teacher) {
                if($teacher["id"] == $data["teacher_id"]) { ?>
                <option selected="selected" value="<?=$teacher["id"]?>"><?=$teacher["last_name"]?> <?=$teacher["name"]?> <?=$teacher["patronymic"]?></option>
<?php 			} else { ?>
                <option value="<?=$teacher["id"]?>"><?=$teacher["last_name"]?> <?=$teacher["name"]?> <?=$teacher["patronymic"]?></option>
<?php 			}
            } ?>
            </select>
            <h3>Лекции и (или) практики:</h3>
<?php 		if(!$data["type"]) { ?>
            <input type="radio" name="type" value="2" />Лекции и практики<br />
            <input type="radio" name="type" value="0" checked="checked" />Лекции / Практики <input type="radio" name="type" value="1" />
<?php 		} else {
                if($data["type"] == 1) { ?>
            <input type="radio" name="type" value="2" />Лекции и практики<br />
            <input type="radio" name="type" value="0" />Лекции / Практики <input type="radio" name="type" value="1" checked="checked" />
<?php 			} else { ?>
            <input type="radio" name="type" value="2" checked="checked" />Лекции и практики<br />
            <input type="radio" name="type" value="0" />Лекции / Практики <input type="radio" name="type" value="1" />
<?php 			}
            } ?>
            <h3>В группах (группы вводятся через точку с запятой):</h3>
            <input type="text" name="groups" value="<?=$data["groups"]?>" />
            <h3>В подгруппах (группы вводятся через точку с запятой):</h3>
            <input type="text" name="subgroups" value="<?=$data["subgroups"]?>" />
            <h3>Номера аудиторий (вводятся через точку с запятой):</h3>
            <input type="text" name="auditorium" value="<?=$data["auditorium"]?>" />
            <h3>C:</h3>
            <input name='date_from' type="text" class="date_time"  value="<?=$data["date_from"]?>" />
            <h3>По:</h3>
            <input name='date_to' type="text" class="date_time"  value="<?=$data["date_to"]?>" />
            <h3>Участие в работе планерок:</h3>
            <input type="checkbox" name="rectors" <?=($data["participation_rectors"] ? 'checked="checked"' : '')?> /> ректорской<br />
            <input type="checkbox" name="prorectors" <?=($data["participation_prorectors"] ? 'checked="checked"' : '')?> /> проректорской<br />
            <input type="checkbox" name="decanats" <?=($data["participation_decanats"] ? 'checked="checked"' : '')?> /> деканатской<br />
            <input type="checkbox" name="academic_senate" <?=($data["participation_academic_senate"] ? 'checked="checked"' : '')?> />Участие в работе ученого совета НГАУ<br />
            <h3>Является ли штатным преподавателем:</h3>
<?php 		if($data["state"]) { ?>
            <input type="radio" name="state" value="0" />Нет / Да <input type="radio" name="state" value="1" checked="checked" />
<?php 		} else { ?>
            <input type="radio" name="state" value="0" checked="checked" />Нет / Да <input type="radio" name="state" value="1" />
<?php     	} ?>
            <h3>Размер занимаемой ставки</h3>
            <select name="rate">
<?php 		foreach($rate as $r) {
                if($r == $data["rate"]) { ?>
                <option selected="selected" value="<?=$r?>"><?=$r?></option>
<?php     		} else { ?>
                <option value="<?=$r?>"><?=$r?></option>
<?php 			}
            } ?>
            </select>
            <h3>Примечания:</h3>
            <input type="text" name="comments" value="<?=$data["comments"]?>" /><br />
            <input type="submit" value="Сохранить" class="button" />
        </form>
<?php   }
    }
    break;

    case "timetable_graphics": {
		//if($Engine->OperationAllowed($MODULE_OUTPUT['module_id'], "nsau.js.access", -1, $Auth->usergroup_id)) { ?>
		<script>jQuery(document).ready(function($) {SendformGraphics();});</script>
<?php   //}
		$month = array("Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь");
        $months = array("Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря");
        if(isset($MODULE_OUTPUT["calendar"])) { ?>
        <a href="<?=$MODULE_OUTPUT["calendar"]["back_link"]?>">Вернуться к просмотру графика</a>
        <h2>Редактирование календаря</h2>
        <form method="post">
            <table class="graphics_table">
				<tr class="table_top">
					<th rowspan="2">№ недели</th>
					<th colspan="3">Дата начала недели</th>
					<th colspan="3">Дата конца недели</th>
				</tr>
				<tr class="table_top">
					<th>День</th>
					<th>Месяц</th>
					<th>Год</th>
					<th>День</th>
					<th>Месяц</th>
					<th>Год</th>
				</tr>
<?php 		$year = date("Y");
            foreach($MODULE_OUTPUT["calendar_data"] as $data) {
                $date = explode("-",$data["begin_date"]); ?>
                <tr>
					<td><?=$data["num_week"]?></td>
					<td>
						<input type="hidden" name="begin_date[<?=$data["num_week"]?>][num_week]" value="<?=$data["num_week"]?>"/>
						<select name="begin_date[<?=$data["num_week"]?>][day]">
<?php 			for($i=1; $i<=31; $i++) {
                    if($i != $date[2]) { ?>
							<option value="<?=$i?>"><?=$i?></option>
<?php 				} else { ?>
							<option value="<?=$i?>" selected="selected"><?=$i?></option>
<?php 				}
                } ?>
						</select>
					</td>
					<td>
						<input type="hidden" name="begin_date[<?=$data["num_week"]?>][num_week]" value="<?=$data["num_week"]?>"/>
						<select name="begin_date[<?=$data["num_week"]?>][month]">
<?php 			for($i=0; $i<12; $i++) {
                    if($i+1 != $date[1]) { ?>
							<option value="<?=$i+1?>"><?=$month[$i]?></option>
<?php 				} else { ?>
							<option value="<?=$i+1?>" selected="selected"><?=$month[$i]?></option>
<?php     			}
                } ?>
						</select>
					</td>
					<td>
						<input type="hidden" name="begin_date[<?=$data["num_week"]?>][num_week]" value="<?=$data["num_week"]?>"/>
						<select name="begin_date[<?=$data["num_week"]?>][year]">
<?php 			for($i=$year-2; $i<$year+3; $i++) {
                    if($i != $date[0]) { ?>
							<option value="<?=$i?>"><?=$i?></option>
<?php 				} else { ?>
							<option selected="selected" value="<?=$i?>"><?=$i?></option>
<?php     			}
                } ?>
						</select>
					</td>
<?php 			$date_end = explode("-",$data["end_date"]); ?>
					<td>
						<input type="hidden" name="end_date[<?=$data["num_week"]?>][num_week]" value="<?=$data["num_week"]?>"/>
						<select name="end_date[<?=$data["num_week"]?>][day]">
<?php  			for($i=1; $i<=31; $i++) {
                    if($i != $date_end[2]) { ?>
							<option value="<?=$i?>"><?=$i?></option>
<?php 				} else { ?>
							<option value="<?=$i?>" selected="selected"><?=$i?></option>
<?php 				}
                } ?>
						</select>
					</td>
					<td>
						<input type="hidden" name="end_date[<?=$data["num_week"]?>][num_week]" value="<?=$data["num_week"]?>"/>
						<select name="end_date[<?=$data["num_week"]?>][month]">
<?php 			for($i=0; $i<12; $i++) {
                    if($i+1 != $date_end[1]) { ?>
							<option value="<?=$i+1?>"><?=$month[$i]?></option>
<?php 				} else { ?>
							<option value="<?=$i+1?>" selected="selected"><?=$month[$i]?></option>
<?php     			}
                } ?>
						</select>
					</td>
					<td>
						<input type="hidden" name="end_date[<?=$data["num_week"]?>][num_week]" value="<?=$data["num_week"]?>"/>
						<select name="end_date[<?=$data["num_week"]?>][year]">
<?php 			for($i=$year-2; $i<$year+3; $i++) {
                    if($i != $date_end[0]) { ?>
							<option value="<?=$i?>"><?=$i?></option>
<?php 				} else { ?>
							<option selected="selected" value="<?=$i?>"><?=$i?></option>
<?php     			}
                } ?>
						</select>
					</td>
                </tr>
<?php 		} ?>
            </table>
            <input type="submit" value="Сохранить" class="button" />
        </form>
<?php 	}
        if(isset($MODULE_OUTPUT["graphics_list"])) { ?>
        <h1>Список графиков:</h1>
<?php 		$i=-1;
            foreach($MODULE_OUTPUT["graphics_list"] as $graphic) {
                if($i==0 && $graphic["type"]) { ?>
        <h3>Графики для заочников</h3>
<?php 				$i++;
                }
                if($i<0) { ?>
        <h3>Графики для очников</h3>
<?php 				$i++;
                } ?>
        <p><a href="<?=$graphic["id"]?>/"><?=$graphic["name"]?></a> [<a href="edit/<?=$graphic["id"]?>/">Редактировать</a>][<a href="delete/<?=$graphic["id"]?>/" onclick="return confirm('Вы уверены, что хотите удалить выбранный элемент?');">Удалить</a>]</p>
<?php 		} ?>
        <h2>Добавить график</h2>
        <form method="post">
            <h3>Название графика:</h3>
            <input name="graphic_name" size="50" type="text" /><br />
            <h3>Очники или заочники:</h3>
            <input type="radio" name="type" value="0" checked="checked" /> Очники / Заочники <input type="radio" name="type" value="1" />
            <h3>Дата начала сессии:</h3>
            <select name="begin_day">
				<option value="0" selected="selected">День</option>
<?php 		for($i=1; $i<=31; $i++) { ?>
                <option value="<?=$i?>"><?=$i?></option>
<?php 		} ?>
            </select>
            <select name="begin_month">
				<option value="0" selected="selected">Месяц</option>
<?php 		for($i=0; $i<=11; $i++) { ?>
                <option value="<?=$i?>"><?=$months[$i]?></option>
<?php 		} ?>
            </select>
            <select name="begin_year">
				<option value="0" selected="selected">Год</option>
<?php 		$year=date("Y");
            for($i=$year-5; $i<=$year+5; $i++) { ?>
                <option value="<?=$i?>"><?=$i?></option>
<?php 		} ?>
            </select><br />
            <h3>Дата окончания сессии:</h3>
            <select name="end_day">
				<option value="0" selected="selected">День</option>
<?php 		for($i=1; $i<=31; $i++) { ?>
                <option value="<?=$i?>"><?=$i?></option>
<?php 		} ?>
            </select>
            <select name="end_month">
				<option value="0" selected="selected">Месяц</option>
<?php 		for($i=0; $i<=11; $i++) { ?>
                <option value="<?=$i+1?>"><?=$months[$i]?></option>
<?php 		} ?>
            </select>
            <select name="end_year">
				<option value="0" selected="selected">Год</option>
<?php 		$year=date("Y");
            for($i=$year-5; $i<=$year+5; $i++) { ?>
                <option value="<?=$i?>"><?=$i?></option>
<?php 		} ?>
            </select> <br />
            <input value="Добавить" type="submit" class="button"/>
        </form>
<?php 	}
        if(isset($MODULE_OUTPUT["graphics_list_data"])) {
            $data = $MODULE_OUTPUT["graphics_list_data"]; ?>
        <h2>Редактирование иинформации о графике:</h2>
        <form method="post">
            <h3>Назваение графика:</h3>
            <input name="graphic_name" type="text" size="50" value="<?=$data["name"]?>" /> <br />
            <h3>Очники или заочники:</h3>
<?php 		if($data["type"]) { ?>
            <input type="radio" name="type" value="0" /> Очники / Заочники <input type="radio" name="type" value="1" checked="checked" />    
<?php 		} else { ?>
            <input type="radio" name="type" value="0" checked="checked" /> Очники / Заочники <input type="radio" name="type" value="1" />
<?php 		}
            $begin_date = explode("-",$data["begin_session"]);
            $end_date = explode("-",$data["end_session"]); ?>
            <h3>Дата начала сессии:</h3>
            <select name="begin_day">
				<option value="0" selected="selected">День</option>
<?php 		for($i=1; $i<=31; $i++) {
                if($begin_date[2]==$i) { ?>
                <option value="<?=$i?>" selected="selected"><?=$i?></option>
<?php     		} else { ?>
                <option value="<?=$i?>"><?=$i?></option>
<?php 			}
            } ?>
            </select>
            <select name="begin_month">
				<option value="0" selected="selected">Месяц</option>
<?php 		for($i=0; $i<=11; $i++) {
                if($begin_date[1] == $i+1) { ?>
                <option value="<?=$i+1?>" selected="selected"><?=$months[$i]?></option>
<?php     		} else { ?>
                <option value="<?=$i+1?>"><?=$months[$i]?></option>
<?php 			}
            } ?>
            </select>
            <select name="begin_year">
				<option value="0" selected="selected">Год</option>
<?php 		$year=date("Y");
            for($i=$year-5; $i<=$year+5; $i++) {
                if($begin_date[0] == $i) { ?>
                <option value="<?=$i?>" selected="selected"><?=$i?></option>
<?php     		} else { ?>
                <option value="<?=$i?>"><?=$i?></option>
<?php 			}
            } ?>
            </select> <br />
            <h3>Дата окончания сессии:</h3>
            <select name="end_day">
				<option value="0" selected="selected">День</option>
<?php 		for($i=1; $i<=31; $i++) {
                if($end_date[2] == $i) { ?>
                <option value="<?=$i?>" selected="selected"><?=$i?></option>
<?php     		} else { ?>
                <option value="<?=$i?>"><?=$i?></option>
<?php 			}
            } ?>
            </select>
            <select name="end_month">
				<option value="0" selected="selected">Месяц</option>
<?php 		for($i=0; $i<=11; $i++) {
                if($end_date[1] == $i+1) { ?>
                <option value="<?=$i+1?>" selected="selected"><?=$months[$i]?></option>
<?php     		} else { ?>
                <option value="<?=$i+1?>"><?=$months[$i]?></option>
<?php 			}
            } ?>
            </select>
            <select name="end_year">
				<option value="0" selected="selected">Год</option>
<?php 		$year=date("Y");
            for($i=$year-5; $i<=$year+5; $i++) {
                if($end_date[0] == $i) { ?>
                <option value="<?=$i?>" selected="selected"><?=$i?></option>
<?php     		} else { ?>
                <option value="<?=$i?>"><?=$i?></option>
<?php 			}
            } ?>
            </select> <br />
            <input type="submit" value="Сохранить изменения" class="button">
        </form>
<?php 	}
        if(isset($MODULE_OUTPUT["graphics_data"])) { ?>
        <h2>Просмотр графика <?=$MODULE_OUTPUT["g_name"]?></h2>
        <a href="calendar/">Календарь</a>
<?php       if(!empty($MODULE_OUTPUT["begin_session"]) && !empty($MODULE_OUTPUT["end_session"])) {
                $begin_date = explode("-",$MODULE_OUTPUT["begin_session"]);
                $end_date = explode("-",$MODULE_OUTPUT["end_session"]); ?>
        <h4><a href="<?=$MODULE_OUTPUT["uri"]?>edit/<?=$MODULE_OUTPUT["g_id"]?>/">Сессия: с <?=$begin_date[2]?>/<?=$begin_date[1]?><?=$begin_date[0]?> по <?=$end_date[2]?>/<?=$end_date[1]?>/<?=$end_date[0]?></a></h4>
<?php 		}
            if(!$MODULE_OUTPUT["g_type"]) { ?>
        <form name="graphics" method="post">
            <table class="graphics_table">
				<tr class="table_top">
					<th>Кафедра</th>
					<th>Ф-тет</th>
					<th>Дисциплина</th>
					<th><img src="/gen_ru_img.php?text=№№ групп&width=100"></th>
					<th><img src="/gen_ru_img.php?text=Кол-во студентов&width=200"></th>
					<th>Вид занятия</th>
					<th class="groups"><img src="/gen_ru_img.php?text=Кол-во гр.,подгр.&width=200"></th>
					<th>В<br />с<br />е<br />г<br />о<br /><br />ч<br />а<br />с<br />о<br />в</th>
<?php 			foreach($MODULE_OUTPUT["dates"] as $week) {
					if($week["num_week"]>17) {
						break;
					}
					$date1 = explode("-",$week["begin_date"]);
					$date2 = explode("-",$week["end_date"]);
					$m1 = $date1[1] + 0;
					$m2 = $date2[1] +0;
					$text = $date1[2]." - ".$date2[2]; ?>
					<th><span title="<?=$month[$m2-1]?>"><img src="/gen_img.php?text=<?=$text?>"></span><br /><?=$week["num_week"]?><br /><br /><?=($week["num_week"]+1)%2+1?></th>
<?php 			} ?>
					<th>З<br />а<br />ч<br />е<br />т<br />о<br />в</th>
					<th>Э<br />к<br />з<br />а<br />м<br />е<br />н<br />о<br />в</th>
				</tr>
<?php 			foreach($MODULE_OUTPUT["graphics_data"] as $data) {
					$department = str_replace("Кафедра ", "", $data["department_name"]); ?>
                <tr>
					<td rowspan="2"><a href="edit/<?=$data["id_graphic"]?>/"><?=$department?></a></td>
					<td rowspan="2"><span title="<?=$data["faculty_name"]?>"><?=$data["faculty_short_name"]?></span></td>
					<td rowspan="2"><?=$data["subject_name"]?><br /><span title="Удалить"><a href="delete/<?=$data["id_graphic"]?>/" onclick="return confirm('Вы уверены, что хотите удалить выбранный элемент?');"><img src="/themes/images/delete.png"></a></span></td>
					<td rowspan="2">
<?php 				foreach($data["groups"] as $group) { ?>
						<?=$group["group_name"]?>;&nbsp;
<?php				} ?>
					</td>
					<td rowspan="2"><?=$data["sum_students"]?></td>
					<td colspan="2">Лек</td>
					<td><?=$data["lecture_hours"]?><input type="hidden" name="<?=$data["id_graphic"]?>[name]" value="<?=$data["id_graphic"]?>"></td>
<?php 				$i=1;
					if(!empty($data["lect"])) {
						foreach($data["lect"] as $lect) {
							while($lect["week_num"]!=$i && $i<18) { ?>
                    <td><input type="text" size="1" style="width: 1em;" name="<?=$data["id_graphic"]?>[l][<?=$i?>]" /></td>
<?php 							$i++; 
							}
							if($lect["week_num"]==$i) { ?>
                    <td><input type="text" size="1" style="width: 1em;" name="<?=$data["id_graphic"]?>[l][<?=$i?>]" value="<?=$lect["hours_per_week"]?>" /></td>
<?php 						}
							$i++;        
						}
					}
					while($i<18) { ?>
                    <td><input type="text" size="1" style="width: 1em;" name="<?=$data["id_graphic"]?>[l][<?=$i?>]" /></td>
<?php 					$i++;    
					}
					if($data["attestation_type"]) { ?>
					<td rowspan="2">+</td><td rowspan="2">&nbsp;</td>
<?php 				} else { ?>
					<td rowspan="2">&nbsp;</td><td rowspan="2">+</td>
<?php 				} ?>
                </tr>
                <tr>
					<td><?=(!$data["type"] ? "Групп" : "Подгр")?></td>
					<td><?=$data["kol_groups"]?></td>
					<td><?=$data["practice_hours"]?></td>
<?php 				$i=1;
					foreach($data["pr"] as $lect) {
						while($lect["week_num"]!=$i && $i<18) { ?>
                    <td><input type="text" size="1" style="width: 1em;" name="<?=$data["id_graphic"]?>[p][<?=$i?>]" /></td>
<?php 						$i++; 
						}
						if($lect["week_num"]==$i) { ?>
                    <td><input type="text" size="1" style="width: 1em;" name="<?=$data["id_graphic"]?>[p][<?=$i?>]" value="<?=$lect["hours_per_week"]?>" /></td>
<?php 					}
						$i++;        
					}
					while($i<18) { ?>
                    <td><input type="text" size="1" style="width: 1em;" name="<?=$data["id_graphic"]?>[p][<?=$i?>]" /></td>
<?php 					$i++;    
					} ?>
                </tr>
<?php 			} ?>
            </table>
            <input type="submit" value="Сохранить изменения" class="button" />
        </form>
<?php 		} else { ?>
        <form name="graphics" method="post">
            <table class="graphics_table">
                <tr class="table_top">
					<th>Кафедра</th>
					<th>Дисциплина</th>
					<th><img src="/gen_ru_img.php?text=№№ групп&width=100"></th>
					<th><img src="/gen_ru_img.php?text=Кол-во студентов&width=200"></th>
					<th>Вид занятия</th>
					<th class="groups"><img src="/gen_ru_img.php?text=Кол-во гр.,подгр.&width=200"></th>
					<th>В<br />с<br />е<br />г<br />о<br /><br />ч<br />а<br />с<br />о<br />в</th>
					<th>УЛ</th>
					<th>УП</th>    
<?php 			foreach($MODULE_OUTPUT["dates"] as $week) {
                    if($week["num_week"]>17) {
                        break;
                    }
                    $date1 = explode("-",$week["begin_date"]);
                    $date2 = explode("-",$week["end_date"]);
                    $m1 = $date1[1] + 0;
                    $m2 = $date2[1] + 0;
                    $text = $date1[2]." - ".$date2[2]; ?>
                    <th><span title="<?=$month[$m2-1]?>"><img src="/gen_img.php?text=<?=$text?>"></span><br /><?=$week["num_week"]?><br /><br /><?=($week["num_week"]+1)%2+1?></th>
<?php 			} ?>
					<th>К<br />о<br />н<br />т<br />р.</th>
					<th>К<br />у<br />р<br />с.</th>
					<th>З<br />а<br />ч<br />е<br />т<br />о<br />в</th>
					<th>Э<br />к<br />з<br />а<br />м<br />е<br />н<br />о<br />в</th>
                </tr>
<?php 			foreach($MODULE_OUTPUT["graphics_data"] as $data) {
					$department = str_replace("Кафедра ", "", $data["department_name"]); ?>
                <tr>
					<td rowspan="2"><a href="edit/<?=$data["id_graphic"]?>/"><?=$department?></a></td>
					<td rowspan="2"><?=$data["subject_name"]?><br /><span title="Удалить"><a href="delete/<?=$data["id_graphic"]?>/" onclick="return confirm('Вы уверены, что хотите удалить выбранный элемент?');"><img src="/themes/images/delete.png"></a></span></td>
					<td rowspan="2">
<?php 				foreach($data["groups"] as $group) { ?>
						<?=$group["group_name"]?>;&nbsp;
<?php				} ?>
					</td>
					<td rowspan="2"><?=$data["sum_students"]?></td>
					<td colspan="2">Лек</td><td><?=$data["lecture_hours"]?></td>
					<td rowspan="2"><?=$data["ust_lecture"]?></td>
					<td rowspan="2"><?=$data["ust_practice"]?><input type="hidden" name="<?=$data["id_graphic"]?>[name]" value="<?=$data["id_graphic"]?>"></td>
<?php 				$i=1;
					if(!empty($data["lect"])) {
						foreach($data["lect"] as $lect) {
							while($lect["week_num"]!=$i && $i<18) { ?>
                    <td><input type="text" size="1" style="width: 1em;" name="<?=$data["id_graphic"]?>[l][<?=$i?>]" /></td>
<?php 							$i++; 
							}
							if($lect["week_num"]==$i) { ?>
                    <td><input type="text" size="1" style="width: 1em;" name="<?=$data["id_graphic"]?>[l][<?=$i?>]" value="<?=$lect["hours_per_week"]?>" /></td>
<?php 						}
							$i++;        
						}
					}
					while($i<18) { ?>
                    <td><input type="text" size="1" style="width: 1em;" name="<?=$data["id_graphic"]?>[l][<?=$i?>]" /></td>
<?php 					$i++;    
					}
					if($data["Contr"]) { ?>
                    <td rowspan="2">+</td>
<?php 				} else { ?>
                    <td rowspan="2">&nbsp;</td>
<?php     			}
					if($data["Curse"]) { ?>
                    <td rowspan="2">+</td>
<?php 				} else { ?>
                    <td rowspan="2">&nbsp;</td>
<?php     			}
					if($data["attestation_type"]) { ?>
					<td rowspan="2">+</td><td rowspan="2">&nbsp;</td>
<?php 				} else { ?>
					<td rowspan="2">&nbsp;</td><td rowspan="2">+</td>
<?php 				} ?>
                </tr>
                <tr>
					<td><?=(!$data["type"] ? "Групп" : "Подгр")?></td>
					<td><?=$data["kol_groups"]?></td>
					<td><?=$data["practice_hours"]?></td>
<?php 				$i=1;
					foreach($data["pr"] as $lect) {
						while($lect["week_num"]!=$i && $i<18) { ?>
                    <td><input type="text" size="1" style="width: 1em;" name="<?=$data["id_graphic"]?>[p][<?=$i?>]" /></td>
<?php 						$i++; 
						}
						if($lect["week_num"]==$i) { ?>
                    <td><input type="text" size="1" style="width: 1em;" name="<?=$data["id_graphic"]?>[p][<?=$i?>]" value="<?=$lect["hours_per_week"]?>" /></td>
<?php 					}
						$i++;        
					}
					while($i<18) { ?>
                    <td><input type="text" size="1" style="width: 1em;" name="<?=$data["id_graphic"]?>[p][<?=$i?>]" /></td>
<?php 					$i++;    
					} ?>
                </tr>
<?php 			} ?>
            </table>
            <input type="submit" value="Сохранить изменения" class="button"/>
        </form>
<?php 		} ?>
        <h2>Добавить новую запись в график</h2>
        <form method="post" name="new_graphic" onsubmit="return sendform_graphics(this);">
			<h3>Выберите дисциплину</h3>
			<select name="subject_id">
				<option value="0" selected="selected" disabled="disabled">Выберите дисциплину</option>
<?php 		$j=0;
            foreach($MODULE_OUTPUT["subjects"] as $subject) {
                if(!$j) { ?>
				<optgroup label="<?=$subject["department_name"]?> (<?=$subject["faculty_short_name"]?>)">
<?php 				$j = $subject["department_id"];
                } else {
                    if($j != $subject["department_id"]) {
                        $j = $subject["department_id"]; ?>
				</optgroup>
				<optgroup label="<?=$subject["department_name"]?> (<?=$subject["faculty_short_name"]?>)">
<?php 				}
                } ?>
					<option value="<?=$subject["subject_id"]?>"><?=$subject["subject_name"]?></option>
<?php 		} ?>
				</optgroup>
			</select>
			<h3>Введите номера групп (после каждой группы ставится точку с запятой):</h3>
            <input name="groups" />
            <h3>Тип занятия:</h3>
            <input type="radio" value="0" checked="checked" name="type" /> Групповое / Подгрупповое
            <input type="radio" value="1" name="type" />
            <h3>Количество групп (или подгрупп, если занятия подгрупповые)</h3>
            <input type="text" name="kol_groups" value="1" />
            <h3>Количество часов за семестр:</h3>
            <input type="text" name="lecture_hours" value="0" /> лекционных<br />
            <input type="text" name="practice_hours" value="0" /> практических занятий
<?php 		if($MODULE_OUTPUT["g_type"]) { ?>
            <h3>Количество установочных занятий:</h3>
            <input type="text" name="ust_lecture" value="0" /> Установочные лекции (УЛ)<br />
            <input type="text" name="ust_practice" value="0" /> Установочные практики (УП)
            <h3>Отметить, если есть:</h3>
            <input type="checkbox" name="Contr" /> Контрольные <br />
            <input type="checkbox" name="Curse" /> Курсовые <br />
<?php 		} ?>
            <h3>Выберите тип аттестации:</h3>
            <input type="radio" value="0" checked="checked" name="attestation_type" /> Экзамен / Зачет
            <input type="radio" value="1" name="attestation_type" /> <br />
            <input type="submit" value="Добавить запись" class="button" />
        </form>
<?php 	}
        if(isset($MODULE_OUTPUT["edit_graphic"])) {
            $data = $MODULE_OUTPUT["edit_graphic"]; ?>
        <h2>Редактирование графика <?=$data["g_name"]?></h2>
        <form method="post" onsubmit="return sendform_graphics(this);">
            <h3>Дисциплина:</h3>
            <select name="subject_id">
<?php 		$j=0;
            foreach($MODULE_OUTPUT["subjects"] as $subject) {
                if(!$j) { ?>
                <optgroup label="<?=$subject["department_name"]?> (<?=$subject["faculty_short_name"]?>)">
<?php 				$j = $subject["department_id"];
                } else {
                    if($j != $subject["department_id"]) {
                        $j = $subject["department_id"]; ?>
                </optgroup>
                <optgroup label="<?=$subject["department_name"]?> (<?=$subject["faculty_short_name"]?>)">
<?php 				}
                }
                if($data["subject_id"] == $subject["subject_id"]) { ?>
                    <option selected="selected" value="<?=$subject["subject_id"]?>"><?=$subject["subject_name"]?></option>
<?php     		} else { ?>
                    <option value="<?=$subject["subject_id"]?>"><?=$subject["subject_name"]?></option>
<?php 			}
            } ?>
				</optgroup>
            </select>
            <h3>Номера групп:</h3>
<?php 		$groups_list=null;
            foreach($data["groups"] as $group) {
                $groups_list = $groups_list.$group["group_name"].";";
            } ?>
            <input type="text" name="groups" value="<?=$groups_list?>" />
            <h3>Тип занятия:</h3>
<?php 		if($data["type"]) { ?>
            <input type="radio" name="type" value="0" /> Групповое / Подгрупповое <input type="radio" name="type" value="1" checked="checked" />
<?php 		} else { ?>
            <input type="radio" name="type" value="0" checked="checked" /> Групповое / Подгрупповое <input type="radio" name="type" value="1" />
<?php 		} ?>
            <h3>Количество групп (или подгрупп, если занятия подгрупповые):</h3>
            <input type="text" name="kol_groups" value="<?=$data["kol_groups"]?>" />
            <h3>Количество часов за семестр:</h3>
            <input type="text" name="lecture_hours" value="<?=$data["lecture_hours"]?>" /> лекционных<br />
            <input type="text" name="practice_hours" value="<?=$data["practice_hours"]?>" />практических
<?php 		if($data["g_type"]) { ?>
            <h3>Количество установочных занятий:</h3>
            <input type="text" name="ust_lecture" value="<?=$data["ust_lecture"]?>" /> Установочные лекции (УЛ)<br />
            <input type="text" name="ust_practice" value="<?=$data["ust_practice"]?>" /> Установочные практики (УП)
            <h3>Отметить, если есть:</h3>
<?php 			if($data["Contr"]) { ?>
            <input type="checkbox" name="Contr" checked="checked" /> Контрольные <br />
<?php 			} else { ?>
            <input type="checkbox" name="Contr" /> Контрольные <br />
<?php 			}
				if($data["Curse"]) { ?>
            <input type="checkbox" name="Curse" checked="checked" /> Курсовые <br />
<?php 			} else { ?>
            <input type="checkbox" name="Curse" /> Курсовые <br />
            <input type="checkbox" name="Curse" /> Курсовые <br />
<?php 			} ?>
<?php 		} ?>
            <h3>Выберите тип аттестации:</h3>
<?php 		if($data["attestation_type"]) { ?>
            <input type="radio" name="attestation_type" value="0" /> Экзамен / Зачет <input type="radio" name="attestation_type" value="1" checked="checked" />
<?php 		} else { ?>
            <input type="radio" name="attestation_type" value="0" checked="checked" /> Экзамен / Зачет <input type="radio" name="attestation_type" value="1" />
<?php 		} ?>
            <br />
            <input type="submit" value="Сохранить изменения" class="button" />
        </form>
<?php 	}
    }
    break;

    case "faculties_files": {
        if(isset($MODULE_OUTPUT["faculties_files"])) {
            $fac = $MODULE_OUTPUT["faculties_files"];
            //echo "<h1>".$MODULE_OUTPUT["faculties_name"]."</h1>";
            foreach($fac as $data) {
                echo "<h2>".$data["faculties_name"]."</h2>";
                foreach($data["subject_list"] as $subject_list) {
                    echo "<h3>".$subject_list["subject_name"]."</h3>";
                    echo "<ul>";
                    foreach($subject_list["file_list"] as $file_list) {
                        if(isset($file_list["file_descr"]) && $file_list["file_descr"] != "")
                            echo "<li><a href=\"/file/".$file_list["file_id"]."/\">".$file_list["file_descr"]."</a></li>";
                        else
                            echo "<li><a href=\"/file/".$file_list["file_id"]."/\">".$file_list["file_name"]."</a></li>";
                    }
                    echo "</ul>";
                }                
            }
        }
        break;
    }
    case "department_files": {
        if(isset($MODULE_OUTPUT["department_files"])) {
            $data = $MODULE_OUTPUT["department_files"];
            echo "<h1>".$MODULE_OUTPUT["department_name"]."</h1>";
            foreach($data as $subject_list) {
                echo "<h2>".$subject_list["subject_name"]."</h2>";
                echo "<ul>";
                foreach($subject_list["file_list"] as $file_list) {
                    if(isset($file_list["file_descr"]) && $file_list["file_descr"] != "")
                        echo "<li><a href=\"/file/".$file_list["file_id"]."/\">".$file_list["file_descr"]."</a></li>";
                    else
                        echo "<li><a href=\"/file/".$file_list["file_id"]."/\">".$file_list["file_name"]."</a></li>";
                }
                echo "</ul>";
            }
        }
        break;
    }
    
    case "faculty_specialities": {
        if(isset($MODULE_OUTPUT["faculty_spec"])) {
            echo "<h2>Перечень специальностей</h2>";
            foreach($MODULE_OUTPUT["faculty_spec"] as $type => $list) {
                if($type == "secondary")
                    $type = "Среднее профессиональное образование";
                elseif($type == "magistracy")
                    $type = "Магистратура";
                elseif($type == "bachelor")
                    $type = "Бакалавриат";
                elseif($type == "higher")
                    $type = "Высшее профессиональное образование";
                echo "<h4>".$type."</h4>";
                echo "<ul>";
                foreach($list as $item) {
                    echo "<li><a href=\"/speciality/".$item["code"]."/\">".$item["name"]."</a></li>";
                }
                echo "</ul>";
            }
        }
    }
    break;

	case "student_registr": {
		if($MODULE_OUTPUT['allowed']) {
			if(isset($MODULE_OUTPUT["display_variant"]["add_item"])) {
				$display_variant = $MODULE_OUTPUT["display_variant"]["add_item"];
			} ?>
		<div class="student_registr_form">
<?php		if(isset($MODULE_OUTPUT['active_request'])) {
				if($MODULE_OUTPUT['active_request'] == 1) { ?>
			<p class="gray-radius-block"><strong>
				В ближайшее время Ваш запрос будет обработан модератором, и на Ваш почтовый ящик придет соответствующее письмо.</strong>
			</p>
<?php			} else { ?>
			<p class="gray-radius-block">
				<strong>Ваш e-mail уже подтверждён.</strong>
			</p>
<?php			}
			} else { ?>
			<script src="/scripts/rekom.js"></script>
			<h2><span class="bayan"><a id="link12"  onclick="show_choice(this, 'show_form_ed');">Если вы студент</a></span></h2>
			
			<div id="f_education" style="display:none;"><p>
		<h3>Выберите форму обучения:</h3>
		<br />
		<a  id="link13" onclick="show_choice(this, 'show_form_ed_och');">Очная</a> <br />
		<a  id="link14"onclick="show_choice(this, 'show_form_ed_zoch');">Заочная</a> <br />
		</p></div>
			
			<form name="form_ed_och" id="form_ed_och" class="bayan_cont<?=(isset($display_variant) ? "" : " hidden")?>" action="<?=$EE["unqueried_uri"]?>" method="post">
				<div class="form_title">Регистрация студентов <strong>ОЧНОЙ</strong> формы обучения</div>
				<p>После отправки формы проверьте почтовый ящик и подтвердите заявку на регистрацию.</p>
				<fieldset>
					<legend>Общая информация</legend>
					<input type="hidden" value="1" name="people_cat" />
					<input type="hidden" value="8" name="status" />
					<div class="wide">
						<div class="form-notes">
							<dl>
								<dt><label>Фамилия<span class="obligatorily">*</span>:</label></dt>
								<dd><input type="text" value="<?=(isset($display_variant) ? $display_variant["last_name"] : "")?>" class="input_long" name="last_name"></dd>
								<dt><label>Имя<span class="obligatorily">*</span>:</label></dt>
								<dd><input type="text" value="<?=(isset($display_variant) ? $display_variant["first_name"] : "")?>" class="input_long" name="first_name"></dd>
								<dt><label>Отчество:</label></dt>
								<dd><input type="text" value="<?=(isset($display_variant) ? $display_variant["patronymic"] : "")?>" class="input_long" name="patronymic"></dd>
								<dt><label>Номер читательского билета<span class="obligatorily">*</span>:</label></dt>
								<dd><input type="text" value="<?=(isset($display_variant) ? $display_variant["library_card"] : "")?>" class="input_long" name="library_card"></dd>
							</dl>						
						</div>
						<div class="form-notes">
							<dl>
								<dt><label>Пол:</label></dt>
								<dd>
									<select name="male" size="1">
										<option value="1"<?=(((isset($display_variant) && $display_variant["male"] == 1) || !isset($display_variant)) ? " selected='selected'" : "")?>>Мужской</option>
										<option value="0"<?=(isset($display_variant) && $display_variant["male"] == 0 ? " selected='selected'" : "")?>>Женский</option>
									</select>
								</dd>
								<dt><label>Группа<span class="obligatorily">*</span>:</label></dt>
								<dd>
	<?php	if(isset($MODULE_OUTPUT['groups'])) {
		?>
									<select name="group" size="1">
										<option></option>
	<?php 		foreach ($MODULE_DATA["output"]["groups"] as $gr) {
		
		if($gr["form_education"]==1){
		?>
										<option value="<?=$gr["id"]?>"<?=(isset($display_variant) && $display_variant["group"] == $gr["id"] ? " selected='selected'" : "")?>><?=$gr["name"]?></option>
	<?php 		} } ?>
									</select>
	<?php	} ?>
									&nbsp;&nbsp;<small><a href="/support/">Моей группы нет в списке.</a></small>
								</dd>
								<dt><label>Подгруппа:</label></dt>
								<dd>
									<input name="subgroup" type="radio" value="0"<?=((!isset($display_variant) || (isset($display_variant) && $display_variant["subgroup"] == 0)) ? " checked='checked'" : "")?> /> Нет
									<input name="subgroup" type="radio" value="а"<?=(isset($display_variant) && $display_variant["subgroup"] == "а" ? " checked='checked'" : "")?> /> а
									<input name="subgroup" type="radio" value="б"<?=(isset($display_variant) && $display_variant["subgroup"] == "б" ? " checked='checked'" : "")?> /> б
								</dd>
								<dt><label>Год поступления:</label></dt>
								<dd><input name="st_year" type="text" value="<?=(isset($display_variant) ? $display_variant["st_year"] : "")?>" /></dd>
							</dl>
						</div>
					</div>
				</fieldset>
				<fieldset>
					<legend>Данные учётной записи</legend>
					<div class="wide" id="newuser">
						<div class="form-notes">
							<dl>
								<dt><label>Логин<span class="obligatorily">*</span>:</label></dt>
								<dd><input name="username" type="text" value="<?=(isset($display_variant) ? $display_variant["username"] : "")?>" /></dd>
								<dt><label>E-mail<span class="obligatorily">*</span>:</label></dt>
								<dd><input name="email" type="text" value="<?=(isset($display_variant) ? $display_variant["email"] : "")?>" /></dd>
							</dl>
						</div>
						<div class="form-notes">
							<dl>
								<dt>
									<label>Пароль<span class="obligatorily">*</span>:<span id="genpass"></span></label>
									<span id="copypass"><a onclick="copypass('genpass','add_pass','repeatpass');">использовать этот пароль<span id="instead" class="instead"></span></a></span>
								</dt>
								<dd>
									<input name="password1" id="add_pass" type="password"  autocomplete="off" />
									<a onclick="passgen('genpass',3,1,1,'add_pass');">сгенерировать пароль</a>
								</dd>
								<dt><label>Повторите пароль<span class="obligatorily">*</span>:</label></dt>
								<dd><input name="password2" id="repeatpass" type="password" /></dd>
							</dl>
						</div>
					</div>
					<div class="wide">
						<dl class="usergroup">
							<dt><label>Группы пользователей:</label></dt>
							<dd>
								<select name="usergroup" size="1">
									<option value="0"></option>
<?php 	foreach ($MODULE_DATA["output"]["usergroups"] as $st) { ?>
									<option value="<?=$st["id"]?>"<?=(isset($display_variant) && $display_variant["usergroup"] == $st["id"] && ($display_variant["people_cat"] == 3 || ($display_variant["people_cat"] == 2 && isset($display_variant["responsible"]))) ? " selected='selected'" : "")?>><?=$st["comment"]?></option>
<?php 	} ?>
								</select>
							</dd>
						</dl>
					</div>
					<div class="wide">
						<input type="hidden" value="add_student" name="mode" />
						<input type="submit" value="Готово" class="button"/>
					</div>
				</fieldset>
			</form>
		
		
		<form name="form_ed_zoch" id="form_ed_zoch" class="bayan_cont<?=(isset($display_variant) ? "" : " hidden")?>" action="<?=$EE["unqueried_uri"]?>" method="post">
				<div class="form_title">Регистрация студентов <strong>ЗАОЧНОЙ</strong> формы обучения</div>
				<p>После отправки формы проверьте почтовый ящик и подтвердите заявку на регистрацию.</p>
				<fieldset>
					<legend>Общая информация</legend>
					<input type="hidden" value="1" name="people_cat" />
					<input type="hidden" value="8" name="status" />
					<div class="wide">
						<div class="form-notes">
							<dl>
								<dt><label>Фамилия<span class="obligatorily">*</span>:</label></dt>
								<dd><input type="text" value="<?=(isset($display_variant) ? $display_variant["last_name"] : "")?>" class="input_long" name="last_name"></dd>
								<dt><label>Имя<span class="obligatorily">*</span>:</label></dt>
								<dd><input type="text" value="<?=(isset($display_variant) ? $display_variant["first_name"] : "")?>" class="input_long" name="first_name"></dd>
								<dt><label>Отчество:</label></dt>
								<dd><input type="text" value="<?=(isset($display_variant) ? $display_variant["patronymic"] : "")?>" class="input_long" name="patronymic"></dd>
								<dt><label>Номер зачётной книжки (шифр)<span class="obligatorily">*</span>:</label></dt>
								<dd><input type="text" value="<?=(isset($display_variant) ? $display_variant["library_card"] : "")?>" class="input_long" name="library_card">
								<br/>
								<span style="color:#555;"><small>Например, А29024</small></span><br /></dd>
							</dl>						
						</div>
						<div class="form-notes">
							<dl>
								<dt><label>Пол:</label></dt>
								<dd>
									<select name="male" size="1">
										<option value="1"<?=(((isset($display_variant) && $display_variant["male"] == 1) || !isset($display_variant)) ? " selected='selected'" : "")?>>Мужской</option>
										<option value="0"<?=(isset($display_variant) && $display_variant["male"] == 0 ? " selected='selected'" : "")?>>Женский</option>
									</select>
								</dd>
								<dt><label>Группа<span class="obligatorily">*</span>:</label></dt>
								<dd>
	<?php	if(isset($MODULE_OUTPUT['groups'])) { ?>
									<select name="group" size="1">
										<option></option>
	<?php 		foreach ($MODULE_DATA["output"]["groups"] as $gr) {
		if($gr["form_education"]==2){
		?>
										<option value="<?=$gr["id"]?>"<?=(isset($display_variant) && $display_variant["group"] == $gr["id"] ? " selected='selected'" : "")?>><?=$gr["name"]?></option>
	<?php 		} } ?>
									</select>
	<?php	} ?>
									&nbsp;&nbsp;<small><a href="/support/">Моей группы нет в списке.</a></small>
								</dd>
								<dt><label>Подгруппа:</label></dt>
								<dd>
									<input name="subgroup" type="radio" value="0"<?=((!isset($display_variant) || (isset($display_variant) && $display_variant["subgroup"] == 0)) ? " checked='checked'" : "")?> /> Нет
									<input name="subgroup" type="radio" value="а"<?=(isset($display_variant) && $display_variant["subgroup"] == "а" ? " checked='checked'" : "")?> /> а
									<input name="subgroup" type="radio" value="б"<?=(isset($display_variant) && $display_variant["subgroup"] == "б" ? " checked='checked'" : "")?> /> б
								</dd>
								<dt><label>Год поступления:</label></dt>
								<dd><input name="st_year" type="text" value="<?=(isset($display_variant) ? $display_variant["st_year"] : "")?>" /></dd>
							</dl>
						</div>
					</div>
				</fieldset>
				<fieldset>
					<legend>Данные учётной записи</legend>
					<div class="wide" id="newuser">
						<div class="form-notes">
							<dl>
								<dt><label>Логин<span class="obligatorily">*</span>:</label></dt>
								<dd><input name="username" type="text" value="<?=(isset($display_variant) ? $display_variant["username"] : "")?>" /></dd>
								<dt><label>E-mail<span class="obligatorily">*</span>:</label></dt>
								<dd><input name="email" type="text" value="<?=(isset($display_variant) ? $display_variant["email"] : "")?>" /></dd>
							</dl>
						</div>
						<div class="form-notes">
							<dl>
								<dt>
									<label>Пароль<span class="obligatorily">*</span>:<span id="genpass"></span></label>
									<span id="copypass"><a onclick="copypass('genpass','add_pass','repeatpass');">использовать этот пароль<span id="instead" class="instead"></span></a></span>
								</dt>
								<dd>
									<input name="password1" id="add_pass" type="password"  autocomplete="off" />
									<a onclick="passgen('genpass',3,1,1,'add_pass');">сгенерировать пароль</a>
								</dd>
								<dt><label>Повторите пароль<span class="obligatorily">*</span>:</label></dt>
								<dd><input name="password2" id="repeatpass" type="password" /></dd>
							</dl>
						</div>
					</div>
					<div class="wide">
						<dl class="usergroup">
							<dt><label>Группы пользователей:</label></dt>
							<dd>
								<select name="usergroup" size="1">
									<option value="0"></option>
<?php 	foreach ($MODULE_DATA["output"]["usergroups"] as $st) { ?>
									<option value="<?=$st["id"]?>"<?=(isset($display_variant) && $display_variant["usergroup"] == $st["id"] && ($display_variant["people_cat"] == 3 || ($display_variant["people_cat"] == 2 && isset($display_variant["responsible"]))) ? " selected='selected'" : "")?>><?=$st["comment"]?></option>
<?php 	} ?>
								</select>
							</dd>
						</dl>
					</div>
					<div class="wide">
						<input type="hidden" value="add_student" name="mode" />
						<input type="submit" value="Готово" class="button"/>
					</div>
				</fieldset>
			</form>

			<h2><span class="bayan" id="">Если вы преподаватель или сотрудник НГАУ</span></h2>
			<div class="bayan_cont hidden">
				<p>Обратитесь к <a title="Ответственные лица по подразделениям" href="/directory/responsible/">ответственному лицу</a> по вашему подразделению либо в <a href="/support/">поддержку портала</a>.<strong></strong></p>
				<h2><strong></strong>Что даёт регистрация?</h2>
				<p>Зарегистрированные пользователи могут:</p>
				<ul>
				<li>Опубликовать информацию о себе;</li>
				<li>Размещать учебные материалы и документы;</li>
				<li>Работать с электронной почтой на портале;</li>
				<li>... и многое другое.</li>
				</ul>
				<p>Зарегистрированные пользователи имеют возможность получить доступ к закрытой информации (приказы, документация и др.)</p>
			</div>
<?php } ?>
			<script>
				jQuery(document).ready(function($) {
					//$('.student_registr_form .bayan_cont').hide();
					$('.student_registr_form .bayan').click(function() {
						$(this).parent().next('.bayan_cont').toggle();
					});
				});
			</script>
		</div>
		
<?php
		}
	}
	break;

	case 'student_moderation': {
		if(isset($MODULE_OUTPUT['student_list']) && count($MODULE_OUTPUT['student_list'])) {
			/*echo "<pre>";
			print_r($MODULE_OUTPUT['student_list']);
			echo "</pre>";*/ ?>
			<div class="student_moderation">
				<table>
					<thead>
						<tr>
							<td class="student_name">Ф.И.О.</td>
							<td class="student_group">группа</td>
							<td class="student_year">год поступления</td>
							<td class="student_email">e-mail</td>
							<td class="student_library_card">№ чит. билета</td>
							<td class="student_date">Дата подачи заявки</td>
							<td class="student_item_form" colspan="2">зарегистрировать / запретить</td>
						</tr>
					</thead>
					<tbody>
<?php		foreach($MODULE_OUTPUT['student_list'] as $id => $students) { ?>
						<tr>
							<td class="student_name"><?=$students['last_name']?><br /><?=$students['name']?><br /><?=$students['patronymic']?></td>
							<td class="student_group"><?=$students['group']?> <?=$students['subgroup']?></td>
							<td class="student_year"><?=$students['year']?></td>
							<td class="student_email"><?=$students['email']?></td>
							<td class="student_library_card"><?=$students['library_card']?></td>
							<td class="student_date"><?=$students['create_time']?></td>
							<td class="student_item_form student_register">
								<form action="<?=$EE["unqueried_uri"]?>" method="post">
									<input type="hidden" value="<?=$id?>" name="student_temp_id" />
									<input type="hidden" value="<?=$students['user_id']?>" name="user_id" />
									<input type="submit" value="" title="Зарегестрировать" name="register" onclick="return confirm('Вы уверены в том, что хотите зарегестрировать данного студента?');" />
								</form>
							</td>
							<td class="student_item_form student_delete">
								<form action="<?=$EE["unqueried_uri"]?>" method="post">
									<input type="hidden" value="<?=$id?>" name="student_temp_id" />
									<input type="hidden" value="<?=$students['user_id']?>" name="user_id" />
									<input type="submit" value="" title="Запретить" name="delete" onclick="return confirm('Вы уверены в том, что хотите отказать данному студенту в регистрации?');" />
								</form>
							</td>
						</tr>
<?php		} ?>
					</tbody>
				</table>
			</div>
<?php	}
	}
	break;

	case "department_curators":
		if (isset($MODULE_OUTPUT["curators_info"])) { ?>
		<p>Кураторы студенческих групп:</p>
    <ul>
<?php	
		foreach($MODULE_OUTPUT["curators_info"] as $info) {	?>
				<li><?=implode(',', $info["groups"])?> гр. - <a href="<?=$info["href"]?>"><span class="name-color"><?=$info["name"]?></span></a></li>
			
<?php		}
    ?> 
    </ul>	
    <?php 	
	}
  ?>
  <?php foreach($MODULE_OUTPUT["curators_info"] as $info) {	
  if($info["text"]){
  ?>
  <p style="text-align:: center;"><strong><?=$info["name"] ?></strong></p>
  <hr />
  <?=$info["text"]?>
  <?php 
    }
  } ?>
  <?php 
	break;
}

?>


