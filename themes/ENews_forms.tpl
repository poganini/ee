<?php
if ($MODULE_OUTPUT["manage_access"] || $MODULE_OUTPUT["privileges"]["cat.create.items"]["create"]) {
	if (!function_exists("DrawCatsSelect")) {
		function DrawCatsSelect($elem_name, $data_array, $selected_item, $depth = 0) {
			if (!$depth) { ?>
	<p>
		<label class="block wide"><strong>���������</strong>:</label>
		<select name="<?=$elem_name?>" class="block">
<?php		}
			foreach ($data_array as $key => $data) {
				if (isset($data["allow"]) && $data["allow"]) {
					echo "		<option value=\"$key\"";
					if ($key == $selected_item) {
						echo " selected=\"selected\"";
					}
					echo ">" . str_repeat("&nbsp;", $depth * 4) . $data["title"] . "</option>\n";
					DrawCatsSelect($elem_name, $data["subitems"], $selected_item, $depth + 1);
				}
			}
			if (!$depth) { ?>
		</select>
	</p>
<?php		}
		}
	}

	if ((($MODULE_OUTPUT["mode"] == "full_list" || $MODULE_OUTPUT["mode"] == "new_full_list") && ($MODULE_OUTPUT["display_variant"] != "add_item")) || ($MODULE_OUTPUT["privileges"]["cat.create.items"]["create"] && (($MODULE_OUTPUT["mode"] == "full_list" || $MODULE_OUTPUT["mode"] == "new_full_list") && ($MODULE_OUTPUT["display_variant"] != "add_item")))) { ?>
	<p class="actions">
		[&nbsp;<a href="<?=$EE["unqueried_uri"]?>?node=<?=$NODE_ID?>&amp;action=add_item#form">�������� ��������</a>&nbsp;]
	</p>
<?php	}

	if ($MODULE_OUTPUT["display_variant"] == "add_item") {
		$data = $MODULE_OUTPUT["form_data"]; ?>
	<a name="form">&nbsp;</a>
	<h2>���������� ���������</h2>
<?php	if ($MODULE_OUTPUT["messages"]["bad"]) {
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
		} ?>
	<form action="<?=$EE["unqueried_uri"]?>" method="post" enctype="multipart/form-data" onsubmit="return checkForm(this);">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?=$MODULE_OUTPUT["input_file_max_size"]?>" />
<?php
		$array = array(
			"" => array(
				"title" => "&mdash; �������� ��������� &mdash;",
				"subitems" => array(),
				),
		) + $MODULE_OUTPUT["cats"]/*[$MODULE_OUTPUT["folder_cat_id"]]["subitems"]*/;
	
		if(isset($MODULE_OUTPUT["show_catselect"]) && $MODULE_OUTPUT["show_catselect"]) { ?>
		<input type="hidden" name="<?=$NODE_ID?>[add_item][cat_id]" value="<?=$data["cat_id"]?>" />
<?php	} else {
			DrawCatsSelect($NODE_ID . "[add_item][cat_id]", $array, $data["cat_id"]);
		} ?>
		<p>
			<label class="block wide"><strong>����� ������:</strong></label>
			<input type="text" name="<?=$NODE_ID?>[add_item][uripart]" value="<?=$data["uripart"]?>" class="block text" />
			<small>(����� ���� �&nbsp;�������� ������ ��������, ���������: ��������� �����, �����, �����, ���� �������������; ������, ��������� <strong>������ ��&nbsp;����, �����������</strong>!)</small>
		</p>
		<p>
			<label class="block wide"><strong>���� �&nbsp;����� ����������:</strong></label>
			<input type="text" class="date_time" name="<?=$NODE_ID?>[add_item][public][date]" value="<?=$data['public_time']?>" size="18" /><br />
			<small>(������ �������� ������������� ���������� �� ������� ����/�����)</small>
		</p> 
    <?php $show_event_params = (isset($MODULE_OUTPUT["show_event_input"]) && $MODULE_OUTPUT["show_event_input"]); ?>
<?php	/*if(isset($MODULE_OUTPUT["show_event_input"]) && $MODULE_OUTPUT["show_event_input"])*/ { ?>
    <?php if(!$show_event_params) { ?>
    <p>
    <label class="block wide"><strong>��������� �������:</strong></label>
    <input type="checkbox" id="show-event-params" value="1" />  
    </p>
    <?php } ?>
		<div id="event-params"<?php if(!$show_event_params) { ?> style="display: none; "<?php } ?>>
    <p>
			<label class="block wide"><strong>���� �&nbsp;����� ������ �������:<span class="obligatorily">*</span></strong></label>
			<input type="text" class="date_time" name="<?=$NODE_ID?>[add_item][start_event][date]" value="<?=$data['start_event']?>" size="18" /><br />
			<!-- <small>(������ �������� ������������� ���������� �� ������� ����/�����)</small> -->
		</p>
		<p>
			<label class="block wide"><strong>���� �&nbsp;����� ��������� �������:</strong></label>
			<input type="text" class="date_time" name="<?=$NODE_ID?>[add_item][end_event][date]" value="<?=$data['end_event']?>" size="18" /><br />
			<small>(������ �������� ������������� ���������� �� ������� ����(��� �� ���� ��������� ������, ���� �������) � ����� 18:00(���� �� �������))</small>
		</p>
    </div>
<?php	} ?>
		<p>
			<label class="block wide"><strong>�����������:</strong></label>
			<input type="file" name="<?=$NODE_ID?>_file" class="block" />
			<small>(��� ������ ������ ������ �������� ���� �������� ��&nbsp;����� <?php echo CF::FormatFilesize($MODULE_OUTPUT["input_file_max_size"]); ?>)</small>
		</p>
		<p>
			<label class="block wide"><strong>���������:</strong></label>
			<input type="text" name="<?=$NODE_ID?>[add_item][title]" value="<?=$data["title"]?>" class="block text" />
			<small>(��������� ���������, ��������� ����� �������, �� ����� 3-� ��������)</small>
		</p>
		<p>
			<label><strong>������� �����:</strong></label><br />
			<small>(������������ �&nbsp;�������)</small>
			<textarea name="<?=$NODE_ID?>[add_item][short_text]" cols="20" rows="8" class="common tiny_mce_news"><?=$data["short_text"]?></textarea>
		</p>
		<p>
			<label><strong>������ �����:</strong></label><br />
			<small>(������������ ��&nbsp;������� ������ ��������� ����������)</small>
			<textarea name="<?=$NODE_ID?>[add_item][full_text]" cols="20" rows="8" class="common tiny_mce_news"><?=$data["full_text"]?></textarea>
		</p>
		<?php if (isset($MODULE_OUTPUT["use_tags"]) && $MODULE_OUTPUT["use_tags"]) {?>
		<p>
			<label class="block wide"><strong>�����:</strong></label>
			<input type="text" name="<?=$NODE_ID?>[add_item][tags]" value="<?=$data["tags"]?>" class="block text" />
			<small>(����� ���������, ������������� ����� �������)</small>
		</p>
		<?php } ?>
		<p>
			<input type="submit" value="�������� ��������" />
			<input type="submit" name="<?=$NODE_ID?>[cancel]" value="������" />
		</p>
	</form>
	<script>jQuery(document).ready(function($) {InputDatetimepicker(); });</script>
<?php
	} elseif ($MODULE_OUTPUT["display_variant"] == "edit_item") {
		$data = $MODULE_OUTPUT["form_data"];
?>
	<a name="form">&nbsp;</a>
	<h2>��������� ���������</h2>
<?php
		if ($MODULE_OUTPUT["messages"]["bad"]) {
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
		} ?>
	<form action="<?=$EE["unqueried_uri"]?>" method="post" enctype="multipart/form-data" onsubmit="return checkForm(this);">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?=$MODULE_OUTPUT["input_file_max_size"]?>" />
		<input type="hidden" name="<?=$NODE_ID?>[save_item][id]" value="<?=$data["id"]?>" />
<?php

		$array = array(
			"" => array(
				"title" => "&mdash; �������� ��������� &mdash;",
				"subitems" => array(),
			),
		) + $MODULE_OUTPUT["cats"]/*[$MODULE_OUTPUT["folder_cat_id"]]["subitems"]*/;
		
		if(isset($MODULE_OUTPUT["show_catselect"]) && $MODULE_OUTPUT["show_catselect"]) { ?>
		<input type="hidden" name="<?=$NODE_ID?>[save_item][cat_id]" value="<?=$data["cat_id"]?>" />
<?php	} else {
			DrawCatsSelect($NODE_ID . "[save_item][cat_id]", $array, $data["cat_id"]);
		} ?>
		<p>
			<label class="block wide"><strong>����� ������:</strong></label>
			<input type="text" name="<?=$NODE_ID?>[save_item][uripart]" value="<?=$data["uripart"]?>" class="block text" />
			<small>(����� ���� �&nbsp;�������� ������ ��������, ���������: ��������� �����, �����, �����, ���� �������������; ������, ��������� <strong>������ ��&nbsp;����, �����������</strong>!)</small>
		</p>
		<p>
			<label class="block wide"><strong>���� �&nbsp;����� ����������:</strong></label>
			<input type="text" class="date_time" name="<?=$NODE_ID?>[save_item][public][date]" value="<?=$data['public_time']?>" size="18" />
		</p>
<?php $show_event_params = (isset($MODULE_OUTPUT["show_event_input"]) && $MODULE_OUTPUT["show_event_input"]); ?>
<?php	/*if(isset($MODULE_OUTPUT["show_event_input"]) && $MODULE_OUTPUT["show_event_input"])*/ { ?>
    <?php if(!$show_event_params) { ?>
    <p>
    <label class="block wide"><strong>��������� �������:</strong></label>
    <input type="checkbox" id="show-event-params" value="1" />  
    </p>
    <?php } ?>    
    <div id="event-params"<?php if(!$show_event_params) { ?> style="display: none; "<?php } ?>>
		<p>
			<label class="block wide"><strong>���� �&nbsp;����� ������ �������:</strong></label>
			<input type="text" class="date_time" name="<?=$NODE_ID?>[save_item][start_event][date]" value="<?=$data['start_event']?>" size="18" /><br />
			<small>(������ �������� ������������� ���������� �� ������� ����/�����)</small>
		</p>
		<p>
			<label class="block wide"><strong>���� �&nbsp;����� ��������� �������:</strong></label>
			<input type="text" class="date_time" name="<?=$NODE_ID?>[save_item][end_event][date]" value="<?=$data['end_event']?>" size="18" /><br />
			<small>(������ �������� ������������� ���������� �� ������� ����(��� �� ���� ��������� �������, ���� �������) � ����� 18:00(���� �� �������))</small>
		</p>
    </div>
<?php	} ?>
		<p>
			<label class="block wide"><strong>���������:</strong></label>
			<input type="text" name="<?=$NODE_ID?>[save_item][title]" value="<?=$data["title"]?>" class="block text" />
			<small>(��������� ���������, ��������� ����� �������, �� ����� 3-� ��������)</small>
		</p>
<?php if (!is_null($data["image_data"])) {?>		
		<p>
			<label class="block wide"><strong>������� �����������:</strong></label>
			<input type="checkbox" name="<?=$NODE_ID?>[save_item][del_image]" />
		</p>
<?php } ?>		
		<p>
			<label class="block wide"><strong>�����������:</strong></label>
			<input type="file" name="<?=$NODE_ID?>_file" class="block" />
			<small>(��� ������ ������ ������ �������� ���� �������� ��&nbsp;����� <?php echo CF::FormatFilesize($MODULE_OUTPUT["input_file_max_size"]); ?>)</small>
		</p>
		<p>
			<label><strong>������� �����:</strong></label><br />
			<small>(������������ �&nbsp;�������)</small>
			<textarea name="<?=$NODE_ID?>[save_item][short_text]" cols="20" rows="8" class="common tiny_mce_news"><?=$data["short_text"]?></textarea>
		</p>
		<p>
			<label><strong>������ �����:</strong></label><br />
			<small>(������������ ��&nbsp;������� ������ ��������� ����������)</small>
			<textarea name="<?=$NODE_ID?>[save_item][full_text]" cols="20" rows="8" class="common tiny_mce_news"><?=$data["full_text"]?></textarea>
		</p>
		<?php if (isset($MODULE_OUTPUT["use_tags"]) && $MODULE_OUTPUT["use_tags"]) {?>
		<p>
			<label class="block wide"><strong>�����:</strong></label>
			<input type="text" name="<?=$NODE_ID?>[save_item][tags]" value="<?=$data["tags"]?>" class="block text" />
			<small>(����� ���������, ������������� ����� �������)</small>
		</p>
		<?php } ?>
		<p>
			<input type="submit" value="��������� ��������" />
			<input type="reset" value="�����" />
			<input type="submit" name="<?=$NODE_ID?>[cancel]" value="������" />
		</p>
	</form>
	<script>jQuery(document).ready(function($) {InputDatetimepicker();});</script>
<?php
	}
	elseif ($MODULE_OUTPUT["display_variant"] == "edit_comment")
	{
			$data = $MODULE_OUTPUT["form_data"];
			
?>
	<a name="form"></a>
	<h2>�������������� �����������</h2>
	<form action="<?=$EE["unqueried_uri"]?>" method="post" enctype="multipart/form-data" class="manage-form">
	<p><label><strong>�����:</strong></label><br />
		<textarea name="<?=$NODE_ID?>[save_comment][text]" cols="20" rows="8" class="common tiny_mce_news"><?=$data["text"]?></textarea></p>
	<input type="hidden" name="<?=$NODE_ID?>[save_comment][id]" value="<?=$data["id"]?>">
	<p><input type="submit" value="��������� ��������" /></p>
	</form>
<?
	}
}
?>
