<?php
// version: 2.8.10
// date: 2012-11-01
$MODULE_MESSAGES = array(
	101 => "Материал успешно добавлен",
	102 => "Материал успешно обновлён",
	103 => "Материал успешно скрыт",
	104 => "Материал успешно открыт",
	105 => "Материал успешно удалён",
	106 => "Комментарий успешно добавлен",
	107 => "Комментарий успешно обновлён",
	107 => "Комментарий успешно скрыт",
	107 => "Комментарий успешно открыт",
	107 => "Комментарий успешно удалён",
	150 => "Информация об&nbsp;изображении успешно обновлена",
	301 => "Не&nbsp;указана категория материала",
	302 => "Неверный формат части пути (допустимы только латинские буквы, цифры, дефис и&nbsp;знак подчёркивания; строка, состоящая целиком из&nbsp;цифр, недопустима!)",
	303 => "Не&nbsp;заполнено поле краткого текста материала",
	304 => "Не&nbsp;указана дата начала события",
	310 => "Материал не&nbsp;найден в&nbsp;базе данных",
	311 => "Не заполнено поле &laquo;Автор&raquo;",
	312 => "Неверный формат поля &laquo;E-mail&raquo;",
	313 => "Не заполнено поле &laquo;Текст комментария&raquo;",
	314 => "Код подтверждения устарел. Пожалуйста, укажите новый код подтверждения",
	315 => "Не указан код подтверждения",
	316 => "Указан неверный код подтверждения",
	317 => "Ещё не истёк защитный интервал между сообщениями. Пожалуйста, повторите через несколько секунд",
	318 => "Комментарий не&nbsp;найден в&nbsp;базе данных",
	401 => "Ошибка базы данных при добавлении материала. Обратитесь к&nbsp;администратору",
	402 => "Ошибка базы данных при обновлении материала. Обратитесь к&nbsp;администратору",
	403 => "Ошибка базы данных при скрытии материала. Обратитесь к&nbsp;администратору",
	404 => "Ошибка базы данных при открытии материала. Обратитесь к&nbsp;администратору",
	405 => "Ошибка базы данных при удалении материала. Обратитесь к&nbsp;администратору",
	406 => "Ошибка базы данных при добавлении комментария. Обратитесь к&nbsp;администратору",
	407 => "Ошибка базы данных при обновлении комментария. Обратитесь к&nbsp;администратору",
	408 => "Ошибка базы данных при скрытии комментария. Обратитесь к&nbsp;администратору",
	409 => "Ошибка базы данных при открытии комментария. Обратитесь к&nbsp;администратору",
	410 => "Ошибка базы данных при удалении комментария. Обратитесь к&nbsp;администратору",
	450 => "Ошибка базы данных при обновлении информации об&nbsp;изображении. Обратитесь к&nbsp;администратору",
	901 => "Не&nbsp;выбран файл изображения",
	902 => "Размер файла изображения превышает максимально допустимый",
	903 => "Не&nbsp;удалось обнаружить закачанный файл. Обратитесь к&nbsp;администратору",
	904 => "Непредвиденная ошибка. Обратитесь к&nbsp;администратору",
	905 => "Не удаётся определить высоту и/или ширину изображения. Обратитесь к&nbsp;администратору",
	906 => "Размеры изображения превышают максимально допустимые",
	907 => "Файл не-изображения без расширения",
	908 => "Неизвестное расширение файла не-изображения",
	909 => "Неизвестный формат файла изображения",
	910 => "Ошибка создания файла изображения. Обратитесь к&nbsp;администратору",
	911 => "Ошибка копирования файла не-изображения. Обратитесь к&nbsp;администратору",
	912 => "Поле &laquo;Заголовок&raquo; должно содержать не менее 3-х символов",
	913 => "Поле &laquo;Заголовок&raquo; слишком длинное",
//	1001 => "Не&nbsp;указана группа новостей",
//	1002 => "Не&nbsp;указана папка новостей в&nbsp;режиме анонса",
//	1003 => "Не&nbsp;указано число выводимых новостей в&nbsp;режиме анонса",
//	1100 => "Неизвестный режим модуля",
	);

$Month = array('января','февраля','марта','апреля','мая','июня', 'июля','августа','сентября','октября','ноября','декабря');


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
			echo "<p class=\"message\">$elem</p>\n";
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
			echo "<p class=\"message red\">$elem</p>\n";
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



switch ($MODULE_OUTPUT["mode"]) {	
	case "announce":
    //CF::Debug($MODULE_OUTPUT);
	if ($MODULE_OUTPUT["news"])	{
		$sub_mode = $MODULE_OUTPUT["sub_mode"];
		if($sub_mode != "image" && $sub_mode != "image_short" && $sub_mode != "image_title" && $sub_mode != "short") { ?>
<div id="newscat<?=$MODULE_OUTPUT["cat_id"]?>" class="news_block">
	<div class="news_block_title">
		<a href="<?=$MODULE_OUTPUT["folder_uri"]?>" title="<?=$LANGUAGE["news"]["read_all_news"]?>"><?=$MODULE_OUTPUT["folder_title"]?></a>
<?php 		if(isset($MODULE_OUTPUT["prev_skip"]) && 0) { ?>
		<a style="cursor:pointer;" onclick="process(<?=$MODULE_OUTPUT["prev_skip"]?>, <?=$MODULE_OUTPUT["cat_id"]?>, <?=$MODULE_OUTPUT["news_folder_id"]?>, <?=$MODULE_OUTPUT["display_limit"]?>);">
			<img title="Предыдущие записи" alt="Предыдущие записи" src="/themes/images/news_previous.png" />
		</a>
<?php 		}
			if (isset($MODULE_OUTPUT["next_skip"]) && 0) { ?>
		<a style="cursor:pointer;" onclick="process(<?=$MODULE_OUTPUT["next_skip"]?>, <?=$MODULE_OUTPUT["cat_id"]?>, <?=$MODULE_OUTPUT["news_folder_id"]?>, <?=$MODULE_OUTPUT["display_limit"]?>);">
			<img title="Следующие записи" alt="Следующие записи" src="/themes/images/news_next.png" />
		</a>
<?php		} ?>
	</div>
	<ul>
<?php		foreach ($MODULE_OUTPUT["news"] as $data) {
				if ($data["has_full_text"]) {
					$link_begin_t = "<a href=\"". $data["link"] . "\" title=\"" . $LANGUAGE["news"]["read_more"] . "\">";
					$link_end = "</a>";
				} else {
					$link_begin_h = $link_begin_t = $link_end = "";
				} ?>
		<li>
<?php			if ($data["output_files"]) { ?>
			<div class="news_img"><?=$link_begin_t?><img src="<?=$data["output_files"][2]?>" alt="<?=$data["title"]?>" /><?=$link_end?></div>
<?php			} else {?>
	
	<div class="news_img"><img src="/images/default.jpg" alt="" /></div>
	
	
<?}




?>


			<div class="news_cont">
				<div class="news_data"><time><?=date($LANGUAGE["news"]["date_format"], strtotime( ( isset($data["start_event"]) && $data["start_event"] != "0000-00-00 00:00:00") ? $data["start_event"] : $data["time"]))?></time></div>
				<div class="news_title"><?=$link_begin_t?><?=$data["title"]?><?=$link_end?></div>
				<div class="news_text">
					<p><?=($data["has_full_text"] ? strip_tags($data["short_text"], "<a>, <br>, <span>, <strong>") : strip_tags($data["short_text"], "<a>, <br>, <span>, <strong>"))?></p>
				</div>
			</div>
		</li>
<?php 		} ?>
	</ul>
</div>
<?php	} else { ?>
	<div id="newscat<?=$MODULE_OUTPUT["cat_id"]?>" class="newscat">
		<h1>
			<a href="<?=$MODULE_OUTPUT["folder_uri"]?>" title="<?=$LANGUAGE["news"]["read_all_news"]?>"><?=$MODULE_OUTPUT["folder_title"]?></a>
<?php 		if(isset($MODULE_OUTPUT["prev_skip"]) && $sub_mode == "none") { ?>
			<a style="cursor:pointer;" onclick="process(<?=$MODULE_OUTPUT["prev_skip"]?>, <?=$MODULE_OUTPUT["cat_id"]?>, <?=$MODULE_OUTPUT["news_folder_id"]?>, <?=$MODULE_OUTPUT["display_limit"]?>);">
				<img title="Предыдущие записи" alt="Предыдущие записи" src="/themes/images/news_previous.png">
			</a>
<?php 		}
			if (isset($MODULE_OUTPUT["next_skip"]) && $sub_mode == "none") { ?>
			<a style="cursor:pointer;" onclick="process(<?=$MODULE_OUTPUT["next_skip"]?>, <?=$MODULE_OUTPUT["cat_id"]?>, <?=$MODULE_OUTPUT["news_folder_id"]?>, <?=$MODULE_OUTPUT["display_limit"]?>);">
				<img title="Следующие записи" alt="Следующие записи" src="/themes/images/news_next.png">
			</a>
<?php 		} ?>
		</h1>
		<div id="announse_list">
			<?if ($sub_mode == "image") {?> <a href="#" class="left_arrow">&nbsp;</a> <?}?>
			<div class="slider_block">
				<div class="loading_bg"></div>
				<ul class="no_script">
<?php		foreach ($MODULE_OUTPUT["news"] as $data) {
				if ($data["has_full_text"] || $sub_mode == "image_short" || $sub_mode == "title" ||  $sub_mode == "short") {
					$link_begin_t = "<a href=\"". $data["link"] . "\" title=\"" . $LANGUAGE["news"]["read_more"] . "\">";
					$link_end = "</a>";
				} else {
					$link_begin_h = $link_begin_t = $link_end = "";
				} ?>	
					<li>
						<div class="announce_short_text_block">
							<em class="open">&nbsp;</em>
<?php 			if ($sub_mode != "image_title") { ?>
							<div class="announce_title">
<?php 				if ($data["output_files"] && $sub_mode != "short") { ?>
								<?=$link_begin_t?><img src="<?=$data["output_files"][2]?>" alt="<?=$data["title"]?>" /><?=$link_end?>
<?php		}  else {?>
	
	<div class="news_img"><img src="/images/default.jpg" alt="" /></div>
	
	
<?}?>
								<h5><?=date($LANGUAGE["news"]["date_format"], strtotime( ( isset($MODULE_OUTPUT["show_event_input"]) && $MODULE_OUTPUT["show_event_input"]) ? $data["start_event"] : $data["time"]))?></h5>					
								<p>
									<span><?=(($MODULE_OUTPUT['sub_mode'] == 'image_short' || $MODULE_OUTPUT['sub_mode'] == 'short') ?$link_begin_t:'')?><?=$data["title"]?><?=(($MODULE_OUTPUT['sub_mode'] == 'image_short' || $MODULE_OUTPUT['sub_mode'] == 'short')?$link_end:'')?></span><em>&nbsp;</em>
								</p>
							</div>
<?php			} ?>
<?php			if ($MODULE_OUTPUT['sub_mode'] != 'image_short' && $MODULE_OUTPUT['sub_mode'] != 'short') { ?>
							<div class="short_text">
								<div>
									<h3>
										<?=$link_begin_t?><?=$data["title"]?><?=$link_end?>
									</h3>
									<?=$data["short_text"]?>
								</div>
							</div>
							<em class="close" title="Закрыть краткий текст">&nbsp;</em>
<?php 			} ?>
						</div>
					</li>					
<?php 		} ?>
					<li class="last_li">&nbsp;</li>
				</ul>
			</div>
			<?if ($sub_mode == "image") {?><a href="#" class="right_arrow">&nbsp;</a><?}?>
		</div>	
		<script>jQuery(document).ready(function($) {AnnounseSlider(<?=$MODULE_OUTPUT['module_id']?>,'<?=$MODULE_OUTPUT['parems']?>');});</script>
	</div>
<?php	}
	}
	break;

	case "announse_ajax": {
		if(isset($MODULE_OUTPUT["news"]))	{
			foreach ($MODULE_OUTPUT["news"] as $data) {
				if ($data["has_full_text"] || $sub_mode == "title") {
					$link_begin_t = "<a href=\"". $data["link"] . "\" title=\"" . $LANGUAGE["news"]["read_more"] . "\">";
					$link_end = "</a>";
				} else {
					$link_begin_h = $link_begin_t = $link_end = "";
				} ?>	
						<li>
							<div class="announce_short_text_block">
								<em class="open">&nbsp;</em>
								<div class="announce_title">
<?php 			if ($data["output_files"]) { ?>
									<?=$link_begin_t?><img src="<?=$data["output_files"][2]?>" alt="<?=$data["title"]?>" /><?=$link_end?>
<?php			} ?>
									<h5><?=date($LANGUAGE["news"]["date_format"], strtotime( ( isset($MODULE_OUTPUT["show_event_input"]) && $MODULE_OUTPUT["show_event_input"]) ? $data["start_event"] : $data["time"]))?></h5>					
									<p>
										<span><?=$data["title"]?></span><em>&nbsp;</em>
									</p>
								</div>
								<div class="short_text">
									<div>
										<h3>
											<?=$link_begin_t?><?=$data["title"]?><?=$link_end?>
										</h3>
										<?=$data["short_text"]?>
									</div>
								</div>
								<em class="close" title="Закрыть краткий текст">&nbsp;</em>
							</div>
						</li>
<?php 		}
		}
	}
	break;
	
	case "calendar": {		 
		if (isset($MODULE_OUTPUT['year'])) { 
			$year = intval($MODULE_OUTPUT['year']);
			$month = intval($MODULE_OUTPUT['month']);
		} else { 
			$month =  intval(date('m'));
			$year = date('Y');
		} 
		$prev_month = ( $month > 1 ? $month - 1 : 12);
		$next_month = ( $month < 12 ? $month + 1 : 1);
		$monthes = array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'); ?>
			<table id="calendar" width="70%" border="0" cellspacing="0" cellpadding="0">
				<thead> 
					<tr>  	
						<th colspan="1"><a onclick="changeDate(-1, <?=$prev_month?>); return false;" href="">&laquo;</a></th>
						<th colspan="5"><a id="monthNewsHref" href="<?=$MODULE_OUTPUT['href_base']?>?date=<?=$year.'-'.($month<10 ? '0' : '').$month?>"><span id='curMonth'><?=$monthes[$month-1]?></span> <span id='curYear'><?=$year?></span></a></th>
						<th colspan="1"><a onclick="changeDate(1, <?=$next_month?>); return false;" href="">&raquo;</a></th>
					</tr>
					<tr>
						<th>П</th>
						<th>В</th>
						<th>С</th>
						<th>Ч</th>
						<th>П</th>
						<th>С</th>
						<th>В</th>
					</tr>
				</thead>
				<tbody>
<?php			$skip = date("w", mktime(0,0,0,$month,1,$year))-1; 
				$daysInMonth = date("t", mktime(0,0,0,$month,1,$year));
				$day = 1;
				for($i = 0; $i < 6; $i++) { ?>
					<tr>
<?php				for($j = 0; $j < 7; $j++) {
						if(($skip > 0)or($day > $daysInMonth)) { ?>
						<td class="none">&nbsp;</td>
<?php 						$skip--;
						} else	{
							if ((date(j)==$day)&&(date(m)==$month)&&(date(Y)==$year)){ ?>
						<td class="today"><?=$day?></td>
<?php 						} else { ?> 
						<td class="day">
<?php							if (!empty($MODULE_OUTPUT['newsDates']) && $day == current($MODULE_OUTPUT['newsDates'])) { ?>
							<a href="<?=$MODULE_OUTPUT['href_base']?>?date=<?=$year.'-'.($month<10 ? '0'.$month : $month).'-'.($day<10 ? '0'.$day : $day)?>" ><?=$day?></a>
<?php 								next($MODULE_OUTPUT['newsDates']);
								} else { ?>
							<?=$day?>
<?php 							} ?>
						</td> 
<?php						}
						$day++; 
						}
						
					} ?>
					</tr>
<?php			} ?> 
				</tbody>
			</table>
			<script>jQuery(document).ready(function($) {InitCalendar(<?=$MODULE_OUTPUT['module_id']?>,<?=$year?>,<?=$month?>,'<?=$MODULE_OUTPUT['href_base']?>');});</script>
<?php 
	}
	break;
	
	case "comments": {		
		if ($MODULE_OUTPUT["comments"] && !empty($MODULE_OUTPUT["comments"])) { // !!! сделать, чтобы не показывалось при отключенных комментариях		
		//	CF::Debug($MODULE_OUTPUT["comments"]);
			echo "<br />\n<div class=\"news_block\"><h2>Последние комментарии:</h2></div><br />\n";
			foreach ($MODULE_OUTPUT["comments"] as $data) {
				echo "<div" . ($data["is_active"] ? "" : " class=\"inactive\"") . ">";
				echo "<div><small><strong>" . $data["author_name"] . "</strong>" . (CF::IsNonEmptyStr($data["author_from"]) ? " ({$data["author_from"]})" : "") . ", " . date($LANGUAGE["news"]["datetime_format"], strtotime($data["time"])) . "</small>:</div>\n";
				echo "<div>{$data["text"]}</div>\n";
				echo "</div>\n";
				echo "<br />\n";
			}
		}
	}
	break;

	case "header_news": { ?>
		<div id="daystogo">
			<div class="text" >
				<span class="title"><?=isset( $MODULE_OUTPUT['news_text']) ? $MODULE_OUTPUT['news_text'] : '' ?></span>
			</div>
		</div>
	<? }
	break;
	
	case "new_full_list":
	case "full_list": { 
		if (!function_exists("FlattenNewsCats")) { // !!! нужно выводить плоский список модулем		
			function FlattenNewsCats($input) {
				$output = array();

				if (is_array($input)) {
					foreach ($input as $key => $elem) {
						$output[$key] = $elem;

						if ($elem["subitems"]) {
							$output += FlattenNewsCats($elem["subitems"]);
						}
						unset($output[$key]["subitems"]);
					}
				}
				return $output;
			}
		}

		$array = FlattenNewsCats($MODULE_OUTPUT["cats"]); ?>
<h1><?=/*$MODULE_OUTPUT["cats"]*/$array[$MODULE_OUTPUT["root_cat_id"]]["title"]?></h1>
<?php
		/*if (isset($MODULE_OUTPUT["cats"][$MODULE_OUTPUT["cat_id"]]["subitems"]) && $MODULE_OUTPUT["cats"][$MODULE_OUTPUT["cat_id"]["subitems"]])
		{
			echo "<ul>\n";

			foreach ($MODULE_OUTPUT["cats"][$MODULE_OUTPUT["cat_id"]]["subitems"] as $data)
			{
				echo "	<li><a href=\"" . $EE["unqueried_uri"] . $data["uripart"] . "/\" title=\"" . $data["descr"] . "\">" . $data["title"] . "</a></li>\n";
			}

			echo "</ul>\n";
		}*/

		$pager_output = $MODULE_OUTPUT["pager_output"];

		foreach ($MODULE_OUTPUT["news"] as $data) {
			if ($data["has_full_text"]) {
				$link_begin_h = "<a href=\"". $data["link"] . "\" title=\"" . $LANGUAGE["news"]["read_more"] . "\">";
				$link_begin_t = "<a href=\"". $data["link"] . "\" class=\"nd\" title=\"" . $LANGUAGE["news"]["read_more"] . "\">";
				$link_end = "</a>";
			} else {
				$link_begin_h = $link_begin_t = $link_end = "";
			} ?>
			<div class="short_news shadow_rbg<?=($data["is_active"] ? "" : " inactive")?>">
				<p class="news_date"><?=date($LANGUAGE["news"]["date_format"], strtotime( $data["time"]))?>&nbsp;<?=$link_begin_h?><?=$data["title"]?><?=$link_end?></p>
				<!-- <p class="news_date"><?=date($LANGUAGE["news"]["date_format"], strtotime( ( isset($MODULE_OUTPUT["show_event_input"]) && $MODULE_OUTPUT["show_event_input"]) ? $data["start_event"] : $data["time"]))?>&nbsp;<?=$link_begin_h?><?=$data["title"]?><?=$link_end?></p>-->
				<div class="news_text">
<?php		if ($data["output_files"]) { ?>
					<?=$link_begin_h?><img src="<?=$data["output_files"][2]?>" alt="<?=$data["title"]?>" class="float-left" /><?=$link_end?>
<?php		}  else {?>
	
	<div class="news_img"><img src="/images/default.jpg" alt="" /></div>
	
	
<?}?>
				<?=$link_begin_t?><?=(CF::RefineText($data["short_text"]))?><?=$link_end?>
				</div>
				
				<div class="pusher"></div>
					
<?php


if ($MODULE_OUTPUT["privileges"]["cat.items.handle"] || ($MODULE_OUTPUT["privileges"]["cat.create.items"]["create"] && $MODULE_OUTPUT["privileges"]["cat.create.items"]["user_id"] == $data["create_user_id"])) { ?>
<p><small>Просмотров: <?=$data["num_views"]?><?php if (isset($MODULE_OUTPUT["display_comments"]) && $MODULE_OUTPUT["display_comments"]) { ?>; комментариев: <?=$data["num_comments"]?>.<?php }?></small></p>
<p class="actions">
	[&nbsp;<a href="<?=$EE["unqueried_uri"]?>?node=<?=$NODE_ID?>&amp;action=edit_item&amp;id=<?=$data["id"]?>#form">Редактировать</a>&nbsp;]
	[&nbsp;<a href="<?=$EE["unqueried_uri"]?>?node=<?=$NODE_ID?>&amp;action=<?php echo $data["is_active"] ? "hide" : "unhide"; ?>_item&amp;id=<?=$data["id"]?>"><?php echo $data["is_active"] ? "Скрыть" : "Открыть"; ?></a>&nbsp;]
<?php  			if ($MODULE_OUTPUT["privileges"]["cat.items.handle"]) { ?>
	[&nbsp;<a href="<?=$EE["unqueried_uri"]?>?node=<?=$NODE_ID?>&amp;action=delete_item&amp;id=<?=$data["id"]?>" onclick="return confirm('Вы действительно хотите удалить этот материал?')">Удалить</a>&nbsp;]
<?php  			} ?>
</p>

<?php		} ?>
				<div class="shadow_tr">&nbsp;</div>
				<div class="shadow_b">
					<div class="shadow_br">&nbsp;</div>
					<div class="shadow_bl">&nbsp;</div>
				</div>
			</div>
<?php	}
		if (count($pager_output["pages_data"]) > 1) { // если есть что выводить, и страниц больше одной        
            echo "<div class=\"pager\"><p>\n";
            //echo "    <p><strong>" . $LANGUAGE["news"]["pages"] . ":</strong> ";
            if($pager_output["prev_page"]) {
                echo "<a href=\"{$pager_output["pages_data"][$pager_output["prev_page"]]["link"]}\" title=\"" . $LANGUAGE["news"]["previous_page"] . "\"";
                echo " id=\"prev_page_link\" class=\"prev-next-link\"><span class=\"arrow\">&larr;&nbsp;</span>";
                echo $pager_output["prev_page"] ? "</a>" : "</span>";
                echo " ";
            }
            
            //echo "</p>\n";
            //echo "    <p>";

            foreach ($pager_output["pages_data"] as $page => $data) {
                if (is_int($page)) {
                    echo ($data["is_current"] ? "<strong>" : "<a href=\"{$data["link"]}\">") . $page . ($data["is_current"] ? "</strong>" : "</a>") . " ";
                }
                else {
                    echo "&hellip; ";
                }
            }

            if ($pager_output["next_page"]) {
                echo "<a href=\"{$pager_output["pages_data"][$pager_output["next_page"]]["link"]}\" title=\"" . $LANGUAGE["news"]["next_page"] . "\"";
                echo " id=\"next_page_link\" class=\"prev-next-link\">" /*. $LANGUAGE["news"]["next"]*/ . "<span class=\"arrow\">&nbsp;&rarr;</span>";
                echo "</a>";
            }
            echo "    </p>\n";
            echo "</div>\n";

            //$CODE_SUBSTITUTES["EXTRA_HEAD_CONTENT"] .= "    <script type=\"text/javascript\" src=\"{$EE["http_scripts"]}pager.js\"></script>\n";
        }        
		include "ENews_forms.tpl";
	}

	break;
	
	case "vvr_full_list": {
		if (!function_exists("FlattenNewsCats")) { // !!! нужно выводить плоский список модулем		
			function FlattenNewsCats($input) {
				$output = array();

				if (is_array($input)) {
					foreach ($input as $key => $elem) {
						$output[$key] = $elem;

						if ($elem["subitems"]) {
							$output += FlattenNewsCats($elem["subitems"]);
						}

						unset($output[$key]["subitems"]);
					}
				}
				return $output;
			}
		}
		$array = FlattenNewsCats($MODULE_OUTPUT["cats"]); ?>
<h1><?=/*$MODULE_OUTPUT["cats"]*/$array[$MODULE_OUTPUT["root_cat_id"]]["title"]?></h1>
<?php
		/*if (isset($MODULE_OUTPUT["cats"][$MODULE_OUTPUT["cat_id"]]["subitems"]) && $MODULE_OUTPUT["cats"][$MODULE_OUTPUT["cat_id"]["subitems"]])
		{
			echo "<ul>\n";

			foreach ($MODULE_OUTPUT["cats"][$MODULE_OUTPUT["cat_id"]]["subitems"] as $data)
			{
				echo "	<li><a href=\"" . $EE["unqueried_uri"] . $data["uripart"] . "/\" title=\"" . $data["descr"] . "\">" . $data["title"] . "</a></li>\n";
			}

			echo "</ul>\n";
		}*/

		$pager_output = $MODULE_OUTPUT["pager_output"]; ?>
		<table class="blog" cellpadding="0" cellspacing="0">
<?php	foreach ($MODULE_OUTPUT["news"] as $data) {
			if ($data["has_full_text"]) {
				$link_begin_h = "<a href=\"". $data["link"] . "\" title=\"" . $LANGUAGE["news"]["read_more"] . "\">";
				$link_begin_t = "<a href=\"". $data["link"] . "\" class=\"nd\" title=\"" . $LANGUAGE["news"]["read_more"] . "\">";
				$link_end = "</a>";
			} else {
				$link_begin_h = $link_begin_t = $link_end = "";
			} ?>				
			<tr>
				<td valign="top" width="100%">
					<table class="contentpaneopen">
						<tr>
							<td class="contentheading" width="100%"><?=$link_begin_h?><?=$data["title"]?><?=$link_end?></td>
						</tr>
					</table>
					<table class="contentpaneopen">
						<tr>
							<td valign="top" colspan="2" class="createdate"><?=date($LANGUAGE["news"]["date_format"], strtotime( ( isset($MODULE_OUTPUT["show_event_input"]) && $MODULE_OUTPUT["show_event_input"]) ? $data["time"] : $data["time"]))?></td>
						</tr>
						<tr>
							<td valign="top" colspan="2"><?=$link_begin_t?><?=(CF::RefineText($data["short_text"]))?><?=$link_end?></td>
						</tr>
					</table>
					<span class="article_seperator">&nbsp;</span>
				</td>
			</tr>					
<?php
echo 'qq'.isset($MODULE_OUTPUT["display_comments"]);
if ($MODULE_OUTPUT["privileges"]["cat.items.handle"] || ($MODULE_OUTPUT["privileges"]["cat.create.items"]["create"] && $MODULE_OUTPUT["privileges"]["cat.create.items"]["user_id"] == $data["create_user_id"])) { ?>
<p><small>Просмотров: <?=$data["num_views"]?><?php if (isset($MODULE_OUTPUT["display_comments"]) && $MODULE_OUTPUT["display_comments"]) { ?>; комментариев: <?=$data["num_comments"]?>.<?php }?></small></p>
<p class="actions">
	[&nbsp;<a href="<?=$EE["unqueried_uri"]?>?node=<?=$NODE_ID?>&amp;action=edit_item&amp;id=<?=$data["id"]?>#form">Редактировать</a>&nbsp;]
	[&nbsp;<a href="<?=$EE["unqueried_uri"]?>?node=<?=$NODE_ID?>&amp;action=<?php echo $data["is_active"] ? "hide" : "unhide"; ?>_item&amp;id=<?=$data["id"]?>"><?php echo $data["is_active"] ? "Скрыть" : "Открыть"; ?></a>&nbsp;]
	[&nbsp;<a href="<?=$EE["unqueried_uri"]?>?node=<?=$NODE_ID?>&amp;action=delete_item&amp;id=<?=$data["id"]?>" onclick="return confirm('Вы действительно хотите удалить этот материал?')">Удалить</a>&nbsp;]
</p>
<?php 		} 
		} ?>
		</table>
<?php 	if (count($pager_output["pages_data"]) > 1) { // если есть что выводить, и страниц больше одной        
            echo "<div class=\"pager\"><p>\n";
            //echo "    <p><strong>" . $LANGUAGE["news"]["pages"] . ":</strong> ";
            if($pager_output["prev_page"]) {
                echo "<a href=\"{$pager_output["pages_data"][$pager_output["prev_page"]]["link"]}\" title=\"" . $LANGUAGE["news"]["previous_page"] . "\"";
                echo " id=\"prev_page_link\" class=\"prev-next-link\"><span class=\"arrow\">&larr;&nbsp;</span>";
                echo $pager_output["prev_page"] ? "</a>" : "</span>";
                echo " ";
            }            
            //echo "</p>\n";
            //echo "    <p>";

            foreach ($pager_output["pages_data"] as $page => $data) {
                if (is_int($page)) {
                    echo ($data["is_current"] ? "<strong>" : "<a href=\"{$data["link"]}\">") . $page . ($data["is_current"] ? "</strong>" : "</a>") . " ";
                }

                else {
                    echo "&hellip; ";
                }
            }

            if ($pager_output["next_page"]) {
                echo "<a href=\"{$pager_output["pages_data"][$pager_output["next_page"]]["link"]}\" title=\"" . $LANGUAGE["news"]["next_page"] . "\"";
                echo " id=\"next_page_link\" class=\"prev-next-link\">" /*. $LANGUAGE["news"]["next"]*/ . "<span class=\"arrow\">&nbsp;&rarr;</span>";
                echo "</a>";
            }
            echo "    </p>\n";
            echo "</div>\n";
            //$CODE_SUBSTITUTES["EXTRA_HEAD_CONTENT"] .= "    <script type=\"text/javascript\" src=\"{$EE["http_scripts"]}pager.js\"></script>\n";
        }        
		include "ENews_forms.tpl";
	}
	break;

	case "detail": {
		$array = array();

		foreach ($MODULE_OUTPUT["messages"]["bad"] as $elem) {
			if (isset($MODULE_MESSAGES[$elem])) {
				$array[] = $MODULE_MESSAGES[$elem];
			}
		}

		foreach ($array as $elem) {
			echo "<p>$elem</p>\n";
		} ?>
			<div id="detail_text">
				<h1>
					<small class="date"><?=date($LANGUAGE["news"]["date_format"], strtotime( ( isset($MODULE_OUTPUT["show_event_input"]) && $MODULE_OUTPUT["show_event_input"]) ? $MODULE_OUTPUT["item"]["start_event"] : $MODULE_OUTPUT["item"]["time"]))?></small>
					<?=$MODULE_OUTPUT["item"]["title"]?>
				</h1>
<?php	if ($MODULE_OUTPUT["item"]["output_files"]) { ?>
				<img src="<?=$MODULE_OUTPUT["item"]["output_files"][1]?>" alt="<?=$MODULE_OUTPUT["item"]["title"]?>" class="float-left" />

<?php		}  else {?>
	
	<div class="news_img"><img src="/images/default.jpg" alt="" /></div>
	
	
<?}?>
				<?=$MODULE_OUTPUT["item"]["text"]?>
			</div>
<?php if (isset($MODULE_OUTPUT["show_news_share"]) && $MODULE_OUTPUT["show_news_share"])  { ?>			
			<script type="text/javascript" src="http://userapi.com/js/api/openapi.js?43"></script>
			<script type="text/javascript" defer="defer">VK.init({apiId: 2683971, onlyWidgets: true});</script>
			<script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8" defer="defer"></script>
			<div id="yandex_icons"  class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="none" data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,lj"></div>
<?php } ?>
<?php	

if ($MODULE_OUTPUT["privileges"]["cat.items.handle"]) { ?>
<p><small>Просмотров: <?=$MODULE_OUTPUT["item"]["num_views"]?><?php if (isset($MODULE_OUTPUT["display_comments"]) && $MODULE_OUTPUT["display_commants"]) { ?>; комментариев: <?=$MODULE_OUTPUT["item"]["num_comments"]?>.<?php }?></small></p>
			<p class="actions">
				[&nbsp;<a href="<?=$EE["unqueried_uri"]?>?node=<?=$NODE_ID?>&amp;action=edit_item&amp;id=<?=$MODULE_OUTPUT["item"]["id"]?>#form">Редактировать</a>&nbsp;]
			</p>
<?php   }
		if(!empty($MODULE_OUTPUT["item"]["tags"])) { ?>
        	Метки:
<?php      	foreach($MODULE_OUTPUT["item"]["tags"] as $tag) { ?>
				<a href="/tag/<?=$tag?>"><?=$tag?></a>
<?php		}
		} ?>
		<div class="pusher">&nbsp;</div>
		<p>
			<a href="<?=$MODULE_OUTPUT["folder_uri"]?>" title="<?=$LANGUAGE["news"]["read_all_publications"]?>">
				<?=$LANGUAGE["news"]["all_publications"]?>
			</a>
		</p>
		<div id="vk_comments"></div>
		<script type="text/javascript" defer="defer">VK.Widgets.Comments("vk_comments", {limit: 10, width: "500", attach: false});</script>
<?php 	/*?>
		<p><a href="#comment_form"><strong><?=$LANGUAGE["news"]["add_comment"]?></strong></a></p>
		<h3><?=$LANGUAGE["news"]["comments"]?></h3>
		<?php  */
?>
<?php 
		if ($MODULE_OUTPUT["comments"]) { // !!! сделать, чтобы не показывалось при отключенных комментариях
			//	CF::Debug($MODULE_OUTPUT["comments"]);
			foreach ($MODULE_OUTPUT["comments"] as $data) { ?>
		<div<?=($data["is_active"] ? "" : " class=\"inactive\"")?>>
			<div>
				<small>
					<strong><?=$data["author_name"]?></strong>
					<?=(CF::IsNonEmptyStr($data["author_from"]) ? " (".$data["author_from"].")" : "")?>,&nbsp;
					<?=date($LANGUAGE["news"]["datetime_format"], strtotime($data["time"]))?>
				</small>:
			</div>
			<div><?=$data["text"]?></div>
<?php			if ($MODULE_OUTPUT["privileges"]["cat.comments.handle"]) { ?>
			<p class="actions">
				[&nbsp;<a href="<?=$EE["unqueried_uri"]?>?node=<?=$NODE_ID?>&amp;action=edit_comment&amp;id=<?=$data["id"]?>#form">Редактировать</a>&nbsp;]
				[&nbsp;<a href="<?=$EE["unqueried_uri"]?>?node=<?=$NODE_ID?>&amp;action=<?php echo $data["is_active"] ? "hide" : "unhide"; ?>_comment&amp;id=<?=$data["id"]?>"><?php echo $data["is_active"] ? "Скрыть" : "Открыть"; ?></a>&nbsp;]
				[&nbsp;<a href="<?=$EE["unqueried_uri"]?>?node=<?=$NODE_ID?>&amp;action=delete_comment&amp;id=<?=$data["id"]?>" onclick="return confirm('Вы действительно хотите удалить этот комментарий?')">Удалить</a>&nbsp;]
			</p>
<?php			} ?>
		</div>
<?php
			}
		} else if ($MODULE_OUTPUT["display_comments"]) { ?>
		<p><?=$LANGUAGE["news"]["no_comments"]?></p>
<?php 	}
		if ($MODULE_OUTPUT["display_comments"]) { ?>
		<a name="comment_form"></a>
		<form action="<?=$EE["unqueried_uri"]?>" method="post">
			<input type="hidden" name="<?=$NODE_ID?>[add_comment][item_id]" value="<?=$MODULE_OUTPUT["item"]["id"]?>" />
			<p>
				<label class="block">
					<strong><?=$LANGUAGE["news"]["name"]?><span class="hint" title="<?=$LANGUAGE["news"]["obligatory_field"]?>">*</span></strong>:
				</label>
				<input name="<?=$NODE_ID?>[add_comment][author_name]" maxlength="255" value="<?=$MODULE_OUTPUT["comment_form_data"]["author_name"]?>" />
			</p>
			<p style="display:none;">
				<label class="block">
					<strong><?=$LANGUAGE["news"]["location"]?></strong>:
				</label>
				<input name="<?=$NODE_ID?>[add_comment][author_from]" maxlength="255" value="<?=$MODULE_OUTPUT["comment_form_data"]["author_from"]?>" />
			</p>
			<p>
				<label class="block">
					<strong><?=$LANGUAGE["news"]["email"]?></strong>:
				</label>
				<input name="<?=$NODE_ID?>[add_comment][author_email]" maxlength="255" value="<?=$MODULE_OUTPUT["comment_form_data"]["author_email"]?>" />
			</p>
			<p>
				<label>
					<strong><?=$LANGUAGE["news"]["your_comment"]?><span class="hint" title="<?=$LANGUAGE["news"]["obligatory_field"]?>">*</span></strong>:
				</label><br />
				<textarea name="<?=$NODE_ID?>[add_comment][text]" cols="30" rows="8" class="common"><?=$MODULE_OUTPUT["comment_form_data"]["text"]?></textarea>
			</p>
			<p>
				<label>
					<strong><?=$LANGUAGE["news"]["confirmation_code"]?><span class="hint" title="<?=$LANGUAGE["news"]["obligatory_field"]?>">*</span>:</strong>
				</label><br />
				<small><?=$LANGUAGE["news"]["confirmation_code_hint"]?></small><br />
				<input name="<?=$NODE_ID?>[add_comment][code]" maxlength="4" value="" class="code block" />
				<img src="<?=$MODULE_OUTPUT["comment_form_data"]["code"]?>" alt="" class="block" /> 
				<input type="submit" value="<?=$LANGUAGE["news"]["add_comment"]?>" class="block after-code" />
			</p>
			<div class="pusher">&nbsp;</div>
		</form>
<?php 	}
		include "ENews_forms.tpl";
		//echo "</div>\n";
		$param = isset($EE['footsteps'][count($EE['footsteps'])-1]['title']) ? $EE['footsteps'][count($EE['footsteps'])-1]['title'] : "";
		echo '<script>jQuery(document).ready(function($) {if($("#yandex_icons").length){YandexActivate("'.$param.'");};});</script>';
		//echo '<script>if (document.getElementById("yandex_icons")) set_handlers();</script>';
	}
	break;		
		
	case "subscribe": { ?>
		<div id="subscribe_form">
			<form action="<?=$EE["unqueried_uri"]?>" method="post">
				<fieldset>
					<legend>Подписаться на новости</legend>
					<?=$MODULE_OUTPUT["messages"]["confirmation_result"]?>
					<p>
						<input type="text" size="20" name="<?=$NODE_ID?>[subscribe][email]" value="ваш e-mail" onclick="this.value='';" onblur="if(!this.value) this.value='ваш e-mail';" />
						<input type="submit" value="Готово" class="button" />
					</p>
				</fieldset>
			</form>	
		</div>
<?php	}
	break;

	case "RSS": {
		if ($MODULE_OUTPUT["news"]) {
	        header("content-type: application/rss+xml");
	        echo "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\n<rss version=\"2.0\">\n<channel>\n";
	        echo "<title>".SITE_NAME."</title>\n<link>http://".APPLIED_DOMAIN."</link>\n\n";
	        foreach ($MODULE_OUTPUT["news"] as $data) {
				$title=$data["title"];
				$link=$data["link"];
				$shortText=$data["short_text"];
				$full_text=CF::RefineText($data["full_text"]);
				$time=$data["time"];
	
				$short_text = CF::RefineText($shortText);
				echo "<item>\n
				    <title>$title</title>\n
				    <link>http://".APPLIED_DOMAIN."$link</link>\n 
				    <description>".((isset($MODULE_OUTPUT["full_text_mode"]) && $MODULE_OUTPUT["full_text_mode"]) ? $full_text : $short_text)."</description>\n
				    <pubDate>$time</pubDate>\n
				    </item>\n\n"; 
			}   
	        echo "</channel>\n
	                </rss>";  
	    }
	}
    break;
    
     
    case "RSS2": {
		if ($MODULE_OUTPUT["news"]) {
	        header("content-type: application/rss+xml");
	        
	        echo "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\n<rss xmlns:yandex=\"http://news.yandex.ru\" xmlns:media=\"http://search.yahoo.com/mrss/\" version=\"2.0\">\n<channel>\n";
	        echo "<title>".SITE_NAME."</title>\n<link>http://".APPLIED_DOMAIN."</link>\n";
	        echo "<description>Лента новостей Новосибирского государственного аграрного университета</description>\n";
	        echo "<image>\n";
	        echo "<url>http://nsau.edu.ru/images/logo_new.png</url>\n";
	        echo "<title>Новости НГАУ</title>\n";
	        echo "<link>http://nsau.edu.ru</link>\n";
	        echo "</image>\n\n";
	        
	        foreach ($MODULE_OUTPUT["news"] as $data) {
				$title=$data["title"];
				$link=$data["link"];
				$shortText=$data["short_text"];
				$full_text=CF::RefineText($data["full_text"]);
				$time=$data["time"];
	
				$short_text = CF::RefineText($shortText);
				echo "<item>\n 
				    <title>".$title."</title>\n
				    <link>http://".APPLIED_DOMAIN."$link</link>\n 
				    <description>".((isset($MODULE_OUTPUT["full_text_mode"]) && $MODULE_OUTPUT["full_text_mode"]) ? $full_text : $short_text)."</description>\n
				    <pubDate>$time</pubDate>\n
				    <yandex:full-text>$full_text</yandex:full-text>\n
				    </item>\n\n"; 
			}   
	        echo "</channel>\n
	                </rss>";  
	    }
	}
    break;
    

	case "online_webcast": {
		if(isset($MODULE_OUTPUT["online_webcast"]) && $MODULE_OUTPUT["online_webcast"]) { ?>
		<div class="online_ebcast">			
			<p class="gray-radius-block video_link float-right"><a class="modal_video" title="Прямая видеотрансляция из зала учёного совета" href="javascript:void(0)">Видеотрансляция</a></p>
			<script>jQuery(document).ready(function($) {VideoSteam();});</script>
		</div>
<?php	}
	}
	break;

	case "events_news": { 

?>
		<div class="events_news shadow_rbg">
			<div class="center_column">
				<div class="center_cont">
					<div class="title">Событие</div>
					<div class="evetns_cont">
<?php 	
	if(isset($MODULE_OUTPUT['news_events'])) {  
			$output = array();
			$output_new = array();
			$today = $MODULE_OUTPUT['news_events'][date('Y-m-d')];
			$link = '';
			if($today[0]['link']) {
				$link = "<a href='".$today[0]['link']."'>".$today[0]['title']."</a>";
			} else {
				$link = $today[0]['title'];
			} ?>
						<div class="event current">
							<h4><?=$link?></h4>
							<?=$today[0]['short_text']?>
						</div>	
<?php	} ?>
					</div>
					<div class="pager">
						
					</div>
				</div>
			</div>
			<div class="right_column">
				<div id="datepicker"></div>
				<div class="month_events" style="display: none;">
					<div class="title">События месяца</div>
					<div class="month_events_list">
<?php 	if(isset($MODULE_OUTPUT['news_events'])) { ?>
						<ul>
<?php		foreach($MODULE_OUTPUT['news_events'] as $date => $events) { 
				foreach($events as $event) { ?>
							<li><span><?=(date("j",strtotime($date))." ".$Month[date("n",strtotime($date))-1]." ".date("Y",strtotime($date))." г.")?></span> - <?=($event['title'])?></li>
<?php			}
			} ?>
						</ul>
<?php	} ?>
					</div>
				</div>
			</div>
			<div class="shadow_tr">&nbsp;</div>
			<div class="shadow_b">
				<div class="shadow_br">&nbsp;</div>
				<div class="shadow_bl">&nbsp;</div>
			</div>
			<script>
				jQuery(document).ready(function($) {
					var news_cat = new Array();
					var folder_id = new Array();
<?php	foreach($MODULE_OUTPUT['news_cat'] as $i => $news_cat) { ?>
					news_cat[<?=$i?>] = <?=((int)$news_cat)?>;
<?php	}
		foreach($MODULE_OUTPUT['folder_id'] as $i => $folder_id) { ?>
					folder_id[<?=$i?>] = <?=((int)$folder_id)?>;
<?php	} ?>
					EventNews(<?=$MODULE_OUTPUT['module_id']?>,news_cat,folder_id);
				});
			</script>
		</div>
<?php	}
	break;

	case "ajax_get_event": {
		$current_event_date = null;
		if(isset($MODULE_OUTPUT['news_events']['current_event_date'])) {
			$current_event_date = $MODULE_OUTPUT['news_events']['current_event_date'];
		}
		if(isset($MODULE_OUTPUT['news_events'])) {
			$month_events = '';
			$output = array();
			foreach($MODULE_OUTPUT['news_events'] as $row_date => $news_events) {
				$html = '';
				$pager = '';
				foreach($news_events as $row_num => $new_event) {
					$link = '';
					if($new_event['link']) {
						$link = "<a href='".$new_event['link']."'>".$new_event['title']."</a>";
					} else {
						$link = $new_event['title'];
					}
					//$output[$row_date][$row_num]['html'] = <<<END
					$num = $row_num+1;
					if(!$row_num) {
					$pager .= <<<END
						<li class="current"><a href="#event_{$num}"></a></li>
END;
					} else {
					$pager .= <<<END
						<li><a href="#event_{$num}"></a></li>
END;
					}
					if(!$row_num) {
					$html .= <<<END
				<div class="event current" id="event_{$num}">
					<h4>{$link}</h4>
					{$new_event['short_text']}
				</div>	
END;
					} else {
					$html .= <<<END
				<div class="event" id="event_{$num}">
					<h4>{$link}</h4>
					{$new_event['short_text']}
				</div>	
END;
					}
					$month_events_date = date("j",strtotime($row_date))." ".$Month[date("n",strtotime($row_date))-1]." ".date("Y",strtotime($row_date))." г.";
					$month_events_date = iconv("windows-1251", "UTF-8", $month_events_date);
					$month_events .= <<<END
					<li><span>{$month_events_date}</span> - {$new_event['title']}</li>
END;
				}
				if(count($news_events) > 1) {
					$pager = <<<END
					<ul>
						{$pager}
					</ul>
END;
				} else {
					$pager = '';
				}
				if(is_null($current_event_date)) {
					$current_event_date = $row_date;
				}
				$output[$row_date] = array(
					"date" => $row_date,
					"html" => $html,
					"pager" => $pager,
				);
			}
			if(count($month_events)) {
				$month_events = <<<END
					<ul>
						{$month_events}
					</ul>
END;
			} else {
				$month_events = '';
			}
			echo json_encode(array("output"=>$output,"month_events"=>$month_events,"current_event_date"=>$current_event_date, 'defaultDate'=>$MODULE_OUTPUT['news_events']['defaultDate']));
		}
	}
	break;

	default: {
		
	}
}
?>