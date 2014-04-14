<?php
// version: 2.11.25
// date: 2014-03-04
global $Engine, $Auth, $EE;

$MODULE_MESSAGES = array("delete_constraint"=>"������ ������� �� ����� ���� ������, ��� ��� ������� ����������� � ���� ����� �������������.");

foreach ($MODULE_DATA["output"]["messages"]["good"] as $data) {
	echo "<p class=\"message\">$data</p>\n";
}
if(isset($MODULE_OUTPUT["messages"]["bad"]) && $MODULE_OUTPUT["mode"] != 'ajax_add_file') {
	foreach ($MODULE_OUTPUT["messages"]["bad"] as $data) {
		echo "<p class=\"message red\">$data</p>\n";
	}
}

if (($MODULE_DATA["output"]["mode"]=="link_to_file" || $MODULE_DATA["output"]["mode"]=="get_file") && !$MODULE_DATA["output"]["allow_download"]){
	echo "<b>������������ ���� ��� ���������� ������</b>";
}	
elseif (( $MODULE_DATA["output"]["mode"]!="files_search" && $MODULE_DATA["output"]["mode"]!="link_to_file" && $MODULE_DATA["output"]["mode"]!="get_file" && $MODULE_DATA["output"]["mode"]!="files_list" && $MODULE_DATA["output"]["mode"]!="attach_file" && $MODULE_DATA["output"]["mode"]!="download" && $MODULE_DATA["output"]["mode"]!="stats" && $MODULE_OUTPUT["mode"]!="els_spec_list") && $MODULE_DATA["output"]["mode"]!="els" /* && $MODULE_DATA["output"]["mode"]!="els_spec_list" */&& !isset($_SESSION["user_id"]) || (($MODULE_DATA["output"]["mode"]=="els" || $MODULE_DATA["output"]["mode"]=="els_spec_list") && substr($Auth->ip, 0, 7) != '192.168'   && !$MODULE_DATA["output"]["allow_download"])) {
//	echo $MODULE_DATA["output"]["mode"];

	echo "<b>������ � ������� �������� ������ �������������� �������������</b>";
}
else {
	switch ($MODULE_OUTPUT["mode"])	{
		case "ajax_add_file": {
			if(isset($MODULE_OUTPUT['files_list'])) {
				header("Content-type: text/html; charset=windows-1251");
				foreach ($MODULE_OUTPUT["files_list"] as $file) { ?>
					<div class="file_list_row" id="file_row_<?=$file["id"]?>">
						<div class="left_column"><a class="file_name" href="/file/<?=$file["id"]?>/" title="<?=$file["descr"]?>"><?=$file["name"]?></a></div>
						<div class="center_column">
							<div class="center_cont">
								<div class="file_item_info">
									<input type="text" id="url_<?=$file["id"]?>" size="40" value="http://<?=$_SERVER["SERVER_NAME"]?>/file/<?=$file["id"]?>/" readonly />
									<img class="pointer" title="�������������" id="editbutton_<?=$file["id"]?>" src="/themes/images/edit.png" alt="" />
									<a class="delet_file inline-block" title="�������" id="deletebutton_<?=$file["id"]?>" href="<?=$EE["unqueried_uri"]?>?delete_file=<?=$file["id"]?>"><img src="/themes/images/delete.png" alt="" /></a>
<?php				if($file["download_count"] == 0) { ?>
									(�������: <?=$file["download_count"]?>)
<?php				} else { ?>
									(�������: <a href="/office/download_people_list/?file_id=<?=$file["id"]?>" title="������ ��������� ����"><?=$file["download_count"]?></a>)
<?php				}
					if(isset($file["attach_file_list"])) { ?>
									<a href="javascript:void(0)" class="show_attach_file">�������� ��������</a>
<?php				} ?>								
								</div>
							</div>
						</div>
<?php			    if (isset($file["attach_file_list"]) || isset($file["folder"])) { ?>
						<div class="attach_file_list hidden">
							<ul>
<?php					if(isset($file["attach_file_list"])) {
							foreach ($file["attach_file_list"] as $id => $attach) {
	$attach_status = '';
	$attach_status_title = '';
	if(is_null($attach["approved"])) {
		$attach_status = 'no_view';
		$attach_status_title = '��� �� �����������';
	} elseif($attach["approved"] == -1) {
		$attach_status = 'no_access';
		$attach_status_title = '���������';
	} else {
		$attach_status = 'access';
		$attach_status_title = '���������';
								} ?>
								<li class="<?=$attach_status?> attach_row" id="attach_row_<?=$id ?>">
									<div class="attach_status status_default" title="<?=$attach_status_title?>"></div>
									<div class="attach_center">
										<div class="attach_cont">
											<span>
<?php							if(!isset($attach["subject_name"])) { ?>
												�� ������
<?php							} else { ?>
												<a href="/subjects/<?=$attach["subject_id"]?>/"><?=$attach["subject_name"]?></a>
<?php							} ?>
												[<small><a href="<?=$EE["unqueried_uri"]?>?del_attach=<?=$id?>" class="delet_attach">�������</a></small>]
											</span>
<?php							if(!isset($attach["spec_name"])) { ?>
											�� ������
<?php							} else { ?>
											<a href="/speciality/<?=$attach["spec_code"]?>/<?=$attach["spec_id"]?>/"><?=$attach["spec_name"]?></a>
<?php							} ?>
											<p>
<?php 										if(isset($attach["profile"])) { ?>
												�������: <?=$attach["profile"]?><br />
<?php 										} ?>
												��� �������: <?=(isset($attach["spec_view"]) ? $attach["spec_view"] : "��� ������� �� ������")?><br />
												����� ��������: <?=(isset($attach["spec_education"]) ? $attach["spec_education"] : "����� �������� �� �������")?>
<?php 										if(!empty($attach["semester"])) { ?><br />
												�������: <?=$attach["semester"]?>
<?php 										} ?>
											</p>
										</div>
									</div>
									<div class="attach_comment">
<?php							if(isset($attach['comment']) && !empty($attach['comment']) && $attach["approved"] == -1) { ?>
										<blockquote><?=$attach['comment']?></blockquote>
<?php							} ?>
									</div>
								</li>
<?php						}
						} ?>
<?php			    	if (isset($file["folder"])) { ?>
								<li>�����: 
<?php				    	$i = 0;
							foreach ($file["folder"] as $folder) { ?>
								<?=$folder["name"]?> [<small><a href="<?=$EE["unqueried_uri"]?>?del_folder=<?=$folder["id"]?>&amp;del_file=<?=$file["id"]?>">�������</a></small>]
								<?=($i != sizeof($file["folder"])-1 ? "," : "")?>
<?php							$i++;
							} ?>
								</li>
<?php					}  ?>
							</ul>
						</div>
<?php				}  ?>
						<form id="edit_<?=$file["id"]?>" data-id="<?=$file["id"]?>" class="edit_file_form hidden" action="<?=$EE["unqueried_uri"]?>" method="post" enctype="multipart/form-data" accept-charset="utf-8">
							<div class="wide">
								<input type="hidden" class="upfile_token" id="upfile_id_<?=$file['id']?>" name="<?php echo ini_get("session.upload_progress.name"); ?>"  value="<?=$MODULE_DATA["output"]["upfile_id"].$file['id']?>" />
								<input type="hidden" name="editfile" value="<?=$file["id"]?>" />
								<div class="form-notes">
									<dl>
										<dt>������������ ������ �����: <?=$MODULE_OUTPUT["maxsize"]?> 
										<span class="progress-bar"></span></dt>
										<dt><label>����:</label></dt>
										<dd><input type="file" name="upload_file" id="upload_file_<?=$file["id"]?>" size="40" /></dd>
										<dt><label>��������:</label></dt>
										<dd><input type="text" name="upload_descr_<?=$file["id"]?>"  id="upload_descr_<?=$file["id"]?>" wrap="virtual" size="50" value="<?=$file['descr']?>" /></dd>
										<dt><label>�����:</label></dt>
										<dd><input type="text" name="upload_author_<?=$file["id"]?>" wrap="virtual" size="50" value="<?=$file['author']?>" /></dd>
										<dt><label>�����:</label></dt>
										<dd><input type="text" name="upload_volume_<?=$file["id"]?>" wrap="virtual" size="50" value="<?=$file['volume']?>" /></dd>
										<dt><label>�����:</label></dt>
										<dd><input type="text" name="upload_edition_<?=$file["id"]?>" wrap="virtual" size="50" value="<?=$file['edition']?>" /></dd>
										<dt><label>����� �������:</label></dt>
										<dd><input type="text" name="upload_place_<?=$file["id"]?>" wrap="virtual" size="50" value="<?=$file['place']?>" /></dd>
									</dl>
								</div>
								<div class="form-notes">
									<dl>
										<dt>&nbsp;</dt>
										<dd><input type="submit" value="OK" /><input type="button" value="������" /></dd>
										<dt>&nbsp;</dt>
										<dd><input type="checkbox" name="allow_download_edit_<?=$file["id"]?>" <?=($file["is_allowed"] ? "checked='checked'" : "")?> /> ��������� ���������� ���� �������������</dd>
										<dt><input type="checkbox" name="is_html_<?=$file["id"]?>" <?=($file["is_html"]?"checked":"")?> /> <span title="���� ��� ������ ��������� - ��� ���� �������������� � ������� C����� � ������� html. ��� �������������� ����� ���������� �������������� � ������ ������ zip. ����� �������, html-�������� ����� �������� ��� ��������� �� �������." style="font-size:16px; font-weight: bold; color: #9C0605; cursor: pointer;">?</span> ��������� ��� ������-���������</dt>
										<dd>(���� ������ ���� � ������� zip)</dd>
										<dt><label>��� �������:</label></dt>
										<dd>
											<select name="upload_year_<?=$file["id"]?>" wrap="virtual" size="1" style="width:100px">
<?php 				for ($year = 1960; $year <= date('Y'); $year++) { ?>
												<option<?=($year == intval($file['year']) ? " selected='selected'" : '')?> value="<?=$year?>"><?=$year?></option>
<?php				} ?>
											</select>
										</dd>
<?php		if(isset($MODULE_OUTPUT["folder_list"])) { ?>
										<dt><label>�����:</label></dt>
										<dd>
											<select name="sel_folder_<?=$file["id"]?>" size="1">
												<option value="0"></option>
<?php			foreach ($MODULE_OUTPUT["folder_list"] as $folder) { ?>
												<option value="<?=$folder["id"]?>" <?=($folder["id"] == $file["folder"][0]["id"] ? "selected" : "")?>><?=$folder["name"]?></option>
<?php			} ?>
											</select>
										</dd>
<?php		}
			if(isset($MODULE_OUTPUT["teacher_transfer"])) { ?>
										<dt><label>�������� �������������:</label></dt>
										<dd>
											<select name="sel_teacher_<?=$file["id"]?>" size="1" class="select_long">
												<option value="0"></option>
<?php			foreach ($MODULE_OUTPUT["teacher_transfer"] as $dep) { ?>
												<optgroup label="<?=$dep['dname']?>">
<?php				foreach ($dep["teacher"] as $userid => $teacher) { ?>
													<option value="<?=$userid?>" <?=($userid == $file["user_id"] ? "selected" : "")?>><?=$teacher?></option>
<?php				} ?>
												</optgroup>
<?php			} ?>
											</select>
										</dd>
<?php		} ?>
									</dl>
								</div>
							</div>
							<div class="progress-bar" style="color: #f00" id="progress-bar-<?=$file["id"]?>"></div>
						</form>
					</div>
<?php		    }
			}
			if(isset($MODULE_OUTPUT["messages"]["bad"])) {
				header("Content-type: text/html; charset=windows-1251");
				foreach ($MODULE_OUTPUT["messages"]["bad"] as $data) {
					echo "<p class=\"message red\">$data</p>\n";
				}
			}
		}
		break;
		case "ajax_del_file": {
			if(isset($MODULE_OUTPUT['ajax_del_file'])) {
				header('Content-Type: text/xml,  charset=windows-1251');
				$dom = new DOMDocument();
				$del_status = $dom->createElement('del_status');
				$dom->appendChild($del_status);				
				$status = $dom->createTextNode($MODULE_OUTPUT['ajax_del_file']);
				$del_status->appendChild($status);
				$xmlString = $dom->saveXML();	
				echo $xmlString; 
			}
		}
		break;
		case "ajax_del_attach": {
			if(isset($MODULE_OUTPUT['ajax_del_attach'])) {
				header('Content-Type: text/xml,  charset=windows-1251');
				$dom = new DOMDocument();
				$del_status = $dom->createElement('del_status');
				$dom->appendChild($del_status);
				$status = $dom->createTextNode($MODULE_OUTPUT['ajax_del_attach']);
				$del_status->appendChild($status);
				$xmlString = $dom->saveXML();
				echo $xmlString;
			}
		}
		break; 
		case "ajax_update_file_select": {
			if(isset($MODULE_OUTPUT['files_list'])) {
				header("Content-type: text/html; charset=windows-1251"); ?>
									<select name="sel_file" size="1">
										<option></option>
<?php		foreach ($MODULE_OUTPUT["files_list"] as $file) { ?>
										<option value="<?=$file["id"]?>"><?=$file["name"]?></option>
<?php		} ?>
									</select>
<?php		}
		}
		break; 
		case "ajax_teachers": {
			header("Content-type: text/html; charset=windows-1251");
			$file = $MODULE_OUTPUT["file"]; 
			$user_id = $MODULE_OUTPUT["user_id"];
			?>
			<select name="sel_teacher_<?=$file["id"]?>" size="1" class="select_long">
			<option value="0"></option>
<?php		foreach ($MODULE_OUTPUT["teacher_transfer"] as $dep) { ?>
			<optgroup label="<?=$dep['dname']?>">
			<?php						foreach ($dep["teacher"] as $userid => $teacher) { ?>
			<option value="<?=$userid?>" <?=($userid == $file["user_id"] ? "selected" : "")?>><?=$teacher?></option>
			<?php						} ?>
			</optgroup>
			<?php } ?>
			</select>
			<?php 
		}
		case "ajax_search_subj": {
			if(isset($MODULE_OUTPUT['subjects_list'])) {
				header("Content-type: text/html; charset=windows-1251"); ?>
				<select name="sel_subject" size="1" id="subject_list">
											<option></option>
	<?php			if (isset($MODULE_OUTPUT["subjects_list"])) {
						foreach ($MODULE_OUTPUT["subjects_list"] as $depart) { ?>
											<optgroup label="<?=$depart["name"]?>">
	<?php					foreach ($depart["subj"] as $subject) { ?>
												<option value="<?=$subject["sid"]?>"><?=$subject["sname"]?></option>
	<?php					} ?>
											</optgroup>
	<?php				}
					} ?>
										</select>
				<?php 
			}
		}
		break;
		case "get_upload_progress": {
			if(isset($MODULE_OUTPUT['get_upload_progress'])) {
				header('Content-Type: text/xml,  charset=windows-1251');
				$dom = new DOMDocument();
				$upload_progress = $dom->createElement('upload_progress');
				$dom->appendChild($upload_progress);
				
				$process = $dom->createElement('process');
				$process_num = $dom->createTextNode($MODULE_OUTPUT['get_upload_progress']['process_uploaded']);
				$process->appendChild($process_num);
				
				$process_time = $dom->createElement('time');
				$time_num = $dom->createTextNode($MODULE_OUTPUT['get_upload_progress']['est_sec']);
				$process_time->appendChild($time_num);
				
				$upload_progress->appendChild($process);
				$upload_progress->appendChild($process_time);
					
				$xmlString = $dom->saveXML();	
				echo $xmlString;
			}
		}
		break;
	
		case "abitfile": { ?>
		<h1>������ �� ������������</h1>
		<form<?=(count($MODULE_DATA["output"]["messages"]["good"]) ? "style='display: none;'" : "")?> action="/office/abit/" method="post" name="abitform" id="abitform" enctype="multipart/form-data">
			<p>���� � csv-�����:<br><input type="file" name="abit" size="40" /></p>
			<input name="spo" type="checkbox" /> ���<br />
			<input type="submit" size="20" value="������" />
		</form>
<?php	}
		break;
		
		case "umkd": {
			if (isset($MODULE_DATA["output"]["umkd_list"]) && !empty($MODULE_DATA["output"]["umkd_list"])) { ?>
			<fieldset>
			    <legend>�������� ����:</legend>
			    <table>
			    	<tr>
			    		<td >
				    		<select id="umkd_list" size="<?=count($MODULE_DATA["output"]["umkd_list"])?>">
				    		<? foreach($MODULE_DATA["output"]["umkd_list"] as $ind=>$umkd) {?>
				    			<option <?=(!$ind?"selected":"")?> id="<?=$umkd['id']?>"><?=$umkd['view_name']?></option>
				    		<? } ?>
				    		</select>
				    		<div style="display:none; width:100%" id="umkd_edit"><form name="umkd_edit_form" method="post" action=""><input name="umkd_id" id="umkd_edit_id" type="hidden" value="" /> <input style="width:80%" name="umkd_text" id="umkd_edit_text" type="text" value="" /><input type="submit" value="OK" /></form></div>
				    	</td>
				    	<td  valign="top">
				    		- <span id='umkd_add_caller' style="cursor:pointer" onclick="document.getElementById('umkd_del_caller').style.textDecoration = 'none';document.getElementById('umkd_edit_caller').style.textDecoration = 'none'; this.style.textDecoration ='underline'; document.getElementById('umkd_edit').style.display='block'; document.getElementById('umkd_edit_text').value = '';"> <strong>�������� �������</strong></span> 
				    		<br>- <span id='umkd_edit_caller' style="cursor:pointer" onclick="document.getElementById('umkd_del_caller').style.textDecoration = 'none'; document.getElementById('umkd_add_caller').style.textDecoration = 'none'; this.style.textDecoration ='underline'; document.getElementById('umkd_edit').style.display='block'; document.getElementById('umkd_edit_text').value = document.getElementById('umkd_list').options[document.getElementById('umkd_list').selectedIndex].value; document.getElementById('umkd_edit_id').value = document.getElementById('umkd_list').options[document.getElementById('umkd_list').selectedIndex].id;"><strong>������������� �������</strong></span> 
				    		<br>- <span id='umkd_del_caller' style="cursor:pointer" onclick="document.getElementById('umkd_add_caller').style.textDecoration = 'none';document.getElementById('umkd_edit_caller').style.textDecoration = 'none'; this.style.textDecoration ='underline'; if (confirm('�� �������, ��� ������ ������� ������ �������?')) {document.getElementById('umkd_edit_id').value = document.getElementById('umkd_list').options[document.getElementById('umkd_list').selectedIndex].id; document.forms['umkd_edit_form'].submit();} "><strong>������� �������</strong></span>
				    	</td>
				    </tr>
				   </table>
			</fieldset>
<?php 		}
		}
		break;
		
		case "files_list": { ?>
		<script>jQuery(document).ready(function($) {ShowAttachFile();FilesList(<?=$MODULE_OUTPUT['module_id']?>,<?=$MODULE_OUTPUT['upload_max_filesize']?>,'<?=$MODULE_OUTPUT['maxsize']?>',<?=(!isset($MODULE_OUTPUT['msoffice_upload']) || !$MODULE_OUTPUT['msoffice_upload'] ? 0 : 1)?>);});</script>
		<div id="file_list_LK" class="files_list">
			<fieldset>
			    <legend>���� �����:</legend>
				<div class="file_list_cont">
<?php		if (isset($MODULE_DATA["output"]["files_list"])) {
				foreach ($MODULE_DATA["output"]["files_list"] as $file) { ?>
					<div class="file_list_row" id="file_row_<?=$file["id"]?>">
						<div class="left_column"><a class="file_name" href="/file/<?=$file["id"]?>/"><?=$file["name"]?></a></div>
						<div class="center_column">
							<div class="center_cont">
								<div class="file_item_info">
									<input type="text" id="url_<?=$file["id"]?>" size="40" value="http://<?=$_SERVER["SERVER_NAME"]?>/file/<?=$file["id"]?>/" readonly />
									<img class="pointer" title="�������������" id="editbutton_<?=$file["id"]?>" src="/themes/images/edit.png" alt="" />
									<a class="delet_file inline-block" title="�������" id="deletebutton_<?=$file["id"]?>" href="<?=$EE["unqueried_uri"]?>?delete_file=<?=$file["id"]?>"><img src="/themes/images/delete.png" alt="" /></a>
<?php				if($file["download_count"] == 0) { ?>
									(�������: <?=$file["download_count"]?>)
<?php				} else { ?>
									(�������: <a href="/office/download_people_list/?file_id=<?=$file["id"]?>" title="������ ��������� ����"><?=$file["download_count"]?></a>)
<?php				}
					if(isset($file["attach_file_list"])) { ?>
									<a href="javascript:void(0)" class="show_attach_file" id="show_attach_file_<?=$file["id"]?>">�������� ��������</a>
<?php				} ?>
								</div>
							</div>
						</div>
<?php			    if (isset($file["attach_file_list"]) || isset($file["folder"])) { ?>
						<div class="attach_file_list hidden" id="attach_file_list_<?=$file["id"]?>">
							<ul>
<?php					if(isset($file["attach_file_list"])) {
							foreach ($file["attach_file_list"] as $id => $attach) {
								$attach_status = '';
								$attach_status_title = '';
								if(is_null($attach["approved"])) {
									$attach_status = 'no_view';
									$attach_status_title = '��� �� �����������';
								} elseif($attach["approved"] == -1) {
									$attach_status = 'no_access';
									$attach_status_title = '���������';
								} else {
									$attach_status = 'access';
									$attach_status_title = '���������';
								} ?>
								<li class="<?=$attach_status?> attach_row" id="attach_row_<?=$id ?>">
									<div class="attach_status status_default" title="<?=$attach_status_title?>"></div>
									<div class="attach_center">
										<div class="attach_cont">
											<span>
<?php							if(!isset($attach["subject_name"])) { ?>
												�� ������
<?php							} else { ?>
												<a href="/subjects/<?=$attach["subject_id"]?>/"><?=$attach["subject_name"]?></a>
<?php							} ?>
												[<small><a href="<?=$EE["unqueried_uri"]?>?del_attach=<?=$id?>" class="delet_attach">�������</a></small>]
											</span>
<?php							if(!isset($attach["spec_name"])) { ?>
											�� ������
<?php							} else { ?>
											<a href="/speciality/<?=$attach["spec_code"]?>/<?=$attach["spec_id"]?>/"><?=$attach["spec_name"]?></a>
<?php							} ?>
<?php             /*if($MODULE_OUTPUT['attach_dates']['add_attach'][$id]){ ?>, <?=$MODULE_OUTPUT['attach_dates']['add_attach'][$id];?><?php } */ ?>
											<p>
<?php 										if(isset($attach["profile"])) { ?>
												�������: <?=$attach["profile"]?><br />
<?php 										} ?>
												��� �������: <?=(isset($attach["spec_view"]) ? $attach["spec_view"] : "��� ������� �� ������")?><br />
												����� ��������: <?=(isset($attach["spec_education"]) ? $attach["spec_education"] : "����� �������� �� �������")?>
<?php 										if(!empty($attach["semester"])) { ?><br />
												�������: <?=$attach["semester"]?>
<?php 										} ?>
											</p>
										</div>
									</div>
									<div class="attach_comment">
<?php							if(isset($attach['comment']) && !empty($attach['comment']) && $attach["approved"] == -1) { ?>
										<blockquote><?=$attach['comment']?></blockquote>
<?php							} ?>
									</div>
								</li>
<?php						}
						} ?>
<?php			    	if (isset($file["folder"])) { ?>
								<li>�����: 
<?php				    	$i = 0;
							foreach ($file["folder"] as $folder) { ?>
								<?=$folder["name"]?> [<small><a href="<?=$EE["unqueried_uri"]?>?del_folder=<?=$folder["id"]?>&amp;del_file=<?=$file["id"]?>">�������</a></small>]
								<?=($i != sizeof($file["folder"])-1 ? "," : "")?>
<?php							$i++;
							} ?>
								</li>
<?php					}  ?>
							</ul>
						</div>
<?php				}  ?>
						<form id="edit_<?=$file["id"]?>" data-id="<?=$file["id"]?>" class="edit_file_form hidden" action="<?=$EE["unqueried_uri"]?>" method="post" enctype="multipart/form-data" accept-charset="utf-8">
							<div class="wide">
								<input type="hidden" class="upfile_token" id="upfile_id_<?=$file['id']?>" name="<?php echo ini_get("session.upload_progress.name"); ?>"  value="<?=$MODULE_DATA["output"]["upfile_id"].$file['id']?>" />
								<input type="hidden" name="editfile" value="<?=$file["id"]?>" />
								<div class="form-notes">
									<dl>
										<dt>������������ ������ �����: <?=$MODULE_OUTPUT["maxsize"]?> 
										<span class="progress-bar"></span></dt>
										<dt><label>����:</label></dt>
										<dd><input type="file" name="upload_file" id="upload_file_<?=$file["id"]?>" size="40" /></dd>
										<dt><label>��������:</label></dt>
										<dd><input type="text" name="upload_descr_<?=$file["id"]?>"  id="upload_descr_<?=$file["id"]?>" wrap="virtual" size="50" value="<?=$file['descr']?>" /></dd>
										<dt><label>�����:</label></dt>
										<dd><input type="text" name="upload_author_<?=$file["id"]?>" wrap="virtual" size="50" value="<?=$file['author']?>" /></dd>
										<dt><label>�����:</label></dt>
										<dd><input type="text" name="upload_volume_<?=$file["id"]?>" wrap="virtual" size="50" value="<?=$file['volume']?>" /></dd>
										<dt><label>�����:</label></dt>
										<dd><input type="text" name="upload_edition_<?=$file["id"]?>" wrap="virtual" size="50" value="<?=$file['edition']?>" /></dd>
										<dt><label>����� �������:</label></dt>
										<dd><input type="text" name="upload_place_<?=$file["id"]?>" wrap="virtual" size="50" value="<?=$file['place']?>" /></dd>
									</dl>
								</div>
								<div class="form-notes">
									<dl>
										<dt>&nbsp;</dt>
										<dd><input type="submit" value="OK" /><input type="button" value="������" /></dd>
										<dt>&nbsp;</dt>
										<dd><input type="checkbox" name="allow_download_edit_<?=$file["id"]?>" <?=($file["is_allowed"] ? "checked='checked'" : "")?> /> ��������� ���������� ���� �������������</dd>
										<dt><input type="checkbox" name="is_html_<?=$file["id"]?>" <?=($file["is_html"]?"checked":"")?> /> <span title="���� ��� ������ ��������� - ��� ���� �������������� � ������� C����� � ������� html. ��� �������������� ����� ���������� �������������� � ������ ������ zip. ����� �������, html-�������� ����� �������� ��� ��������� �� �������." style="font-size:16px; font-weight: bold; color: #9C0605; cursor: pointer;">?</span> ��������� ��� ������-���������</dt>
										<dd>(���� ������ ���� � ������� zip)</dd>
										<dt><label>��� �������:</label></dt>
										<dd>
											<select name="upload_year_<?=$file["id"]?>" wrap="virtual" size="1" style="width:100px">
<?php 				for ($year = 1960; $year <= date('Y'); $year++) { ?>
												<option<?=($year == intval($file['year']) ? " selected='selected'" : '')?> value="<?=$year?>"><?=$year?></option>
<?php				} ?>
											</select>
										</dd>
<?php				if(isset($MODULE_DATA["output"]["folder_list"])) { ?>
										<dt><label>�����:</label></dt>
										<dd>
											<select name="sel_folder_<?=$file["id"]?>" size="1">
												<option value="0"></option>
<?php					foreach ($MODULE_DATA["output"]["folder_list"] as $folder) { ?>
												<option value="<?=$folder["id"]?>" <?=($folder["id"] == $file["folder"][0]["id"] ? "selected" : "")?>><?=$folder["name"]?></option>
<?php					} ?>
											</select>
										</dd>
<?php				}
					if(isset($MODULE_DATA["output"]["teacher_transfer"])) { ?>
										<dt><label>�������� �������������:</label></dt>
										<dd>
											<select name="sel_teacher_<?=$file["id"]?>" size="1" class="select_long">
												<option value="0"></option>
<?php					/*foreach ($MODULE_DATA["output"]["teacher_transfer"] as $dep) { ?>
												<optgroup label="<?=$dep['dname']?>">
<?php						foreach ($dep["teacher"] as $userid => $teacher) { ?>
													<option value="<?=$userid?>" <?=($userid == $file["user_id"] ? "selected" : "")?>><?=$teacher?></option>
<?php						} ?>
												</optgroup>
<?php					}*/ ?>
											</select>
										</dd>
<?php				} ?>
									</dl>
								</div>
							</div>
							<div class="progress-bar" style="color: #f00" id="progress-bar-<?=$file["id"]?>"></div>
						</form>
					</div>
<?php		    }
			} else { ?>
					����� �����������
<?php		} ?>
				</div>
            </fieldset>
		</div>	
<?php //		echo '<a href="#" id="demo-attach">Attach a file</a><ul id="demo-list"></ul><a href="#" id="demo-attach-2" style="display: none;">Attach another file</a>';
		//print_r($EE);
			if ($MODULE_DATA["output"]["allow_upload"] && isset($_SESSION["user_id"])) { ?>
		<div id="add_file_LK" style="display: none; ">
			<fieldset>
			    <legend>�������� ����</legend>
				������������ ������ �����: <?=$MODULE_DATA["output"]["maxsize"]?>
				<span class="progress-bar"></span><br><span class="progress-bar1"></span>
				<form action="<?=$EE["unqueried_uri"]?>" method="post" name="fileform" id="fileform" enctype="multipart/form-data">
					<div class="wide">
						<div class="form-notes title-notes">
							<div><label>����:<span class="obligatorily">*</span></label></div>
							<div><label>������ ��������:<span class="obligatorily">*</span></label></div>
							<div><label>�����:</label></div>
							<div><label><nobr>��� �������:</nobr></label></div>
							<div><label><nobr>�����,���:</nobr></label></div>
							<div><label><nobr>�����,���:</nobr></label></div>
							<div><label><nobr>����� ���-�:</nobr></label></div>
						</div>
						<div class="center-notes">
							<div class="cn">
								<div class="form-notes">
									<input type="hidden" name="MAX_FILE_SIZE" value="157286400">
									<input type="hidden" class="upfile_token" id="upfile_id" name="<?php echo ini_get("session.upload_progress.name"); ?>"  value="<?=$MODULE_DATA["output"]["upfile_id"];?>" />
									<div><input type="file" name="upload_file" id="upload_file" size="37" /></div>
									<div><input type="text" name="upload_descr" id="upload_descr" wrap="virtual" size="48" /></div>
									<div><input name="upload_author" type="text" value="" size="48" /></div>
									<div>
										<select name="upload_year" size="1" style="width:100px">
<?php 			for($year = date('Y'); $year >= 1960; $year--) { ?>
											<option><?=$year;?></option>
<?php 			} ?>
										</select>
									</div>
									<div><input name="upload_volume" type="text" value=""/></div>
									<div><input name="upload_edition" type="text" value=""/></div>
									<div>
										<div class="datalis">
											<input name="upload_place" type="text" value="" autocomplete="off" />
											<ul>
												<li onclick="this.parentNode.parentNode.getElementsByTagName('input')[0].value = this.innerHTML;">�����������, ����</li>
												<li onclick="this.parentNode.parentNode.getElementsByTagName('input')[0].value = this.innerHTML;">������, �����</li>
											</ul>
										</div>								
									</div>
								</div>
								<div class="form-notes">
									<div><input type="checkbox" name="allow_download" /> ��������� ���������� ���� �������������</div>
									<div><input type="checkbox" name="is_html" /> <span title="���� ��� ������ ��������� - ��� ���� �������������� � ������� C����� � ������� html. ��� �������������� ����� ���������� �������������� � ������ ������ zip. ����� �������, html-�������� ����� �������� ��� ��������� �� �������." style="font-size:16px; font-weight: bold; color: #9C0605; cursor: pointer;">?</span> ��������� ��� ������-��������� (���� ������ ���� � ������� zip)</div>
								</div>
							</div>
						</div>
					</div>
					<div class="wide">
						<input type="submit" size="20" value="������" />
					</div>
					<iframe style="display:none" id="upload-frame" name="upload-frame"></iframe>
				</form>
			</fieldset>
		</div>
    <style> 
    #multifile_form_tpl { display: none; }
    /*#multifile_forms { width: 49%; }*/
    #selected_files { 
      cursor: default; 
      border: 1px inset #F0F0F0;
      height: 96px;
      line-height: 16px;
       /* ��� Mozilla FireFox */ -moz-user-select: none;
       /* ��� Safari, Chrome */ -khtml-user-select: none;
       /* ����� �������� */ user-select: none; 
    }
    #selected_files li em {
      background: url("/themes/images/delete.png") no-repeat scroll 0 0 rgba(0, 0, 0, 0);
      cursor: pointer;
      display: inline-block;
      height: 16px;
      margin: 0 0 0 5px;
      vertical-align: bottom;
      width: 16px;
    }
    #selected_files li { list-style-type: none; padding-left: 20px; }
    #selected_files li.selected {
      background: none repeat scroll 0 0 #3399FF;
      color: #FFFFFF;
    }
    #multifile_forms .title-notes { width: 145px; margin-right: -145px; }
    #multifile_forms .center-notes .cn { margin-left: 145px; }
    </style>
    <div class="wide" id="multifile_LK">
      <fieldset>
        <legend>�������� ���������� ������</legend>
        <div class="form-notes" style="width: 49%; ">&nbsp;
          <dl>
            <dd>�������� ����: <span id="multifile_progress_LK" class="progress-bar"></span></dd>
            <dt><span style="position: relative; overflow: hidden;">
            <a href="javascript: void(0);" style="top: 0px; left: 0px;">������� ����(�)</a><input type="file" multiple="multiple" id="multifile_select_LK" class="multifile_input" style="position: absolute; z-index: 99; top: 0px; left: 0px; opacity: 0; cursor: pointer; " /></span></dt>
            <dd><input type="button" size="20" value="���������" id="multifile_upload_selected" title="��������� ��������� �����" /></dd>
          </dl>
        </div>
        <div class="form-notes">
        <dl>
          <dd>������� ��������� �����: </dd>
          <dt>
            <ul id="selected_files" class="select_long"></ul>
          </dt>
        </dl>
        </div>
        <!--<div class="wide">
						<input type="submit" size="20" value="������" />
					</div>-->
        <div class="wide" id="multifile_forms"></div>
      </fieldset>
    </div>
        
    <?php /* ?><div class="wide" id="multifile_LK">
      <fieldset>
        <legend>�������� ���������� ������</legend>
        <div class="form-notes" id="multifile_forms">&nbsp;
        </div>
        <div class="form-notes">
        <dl>
          <dd>�������� ����: </dd>
          <dt><span style="position: relative; overflow: hidden;">
          <a href="javascript: void(0);" style="top: 0px; left: 0px;">��������� ����</a><input type="file" class="multifile_input" style="position: absolute; z-index: 99; top: 0px; left: 0px; opacity: 0; cursor: pointer; " /></span></dt>
          <dd>������� ��������� �����: </dd>
          <dt>
            <ul id="selected_files" class="select_long"></ul>
          </dt>
        </dl>
        </div>
        <div class="wide"></div>
      </fieldset>
    </div><?php */ ?> 
    <div id="multifile_form_tpl">
      <?php /* ?><form action="<?=$EE["unqueried_uri"]?>" method="post" enctype="multipart/form-data">
      <dl>
        <dd><label>����:<span class="obligatorily">*</span></label></dd>
        <dt class="file_field"></dt>
        <dd><label>������ ��������:<span class="obligatorily">*</span></label></dd>
        <dt><input type="text" name="upload_descr" id="upload_descr_{id}" wrap="virtual" size="48" /></dt>
        <dd><label>�����:</label></dd>
        <dt><input type="text" name="upload_author" id="upload_author_{id}" wrap="virtual" size="48" /></dt>
      </dl>
      </form><?*/ ?>
      ������������ ������ �����: <?=$MODULE_DATA["output"]["maxsize"]?>
				<span class="progress-bar"></span><br><span class="progress-bar{id}"></span>
				<form action="<?=$EE["unqueried_uri"]?>" method="post" name="fileform_{id}" id="fileform_{id}" enctype="multipart/form-data" data-id="{id}">
					<div class="wide">
						<div class="form-notes title-notes">
							<!--<div><label>����:<span class="obligatorily">*</span></label></div> -->
							<div><label>������ ��������:<span class="obligatorily">*</span></label></div>
							<div><label>�����:</label></div>
							<div><label><nobr>��� �������:</nobr></label></div>
							<div><label><nobr>�����,���:</nobr></label></div>
							<div><label><nobr>�����,���:</nobr></label></div>
							<div><label><nobr>����� ���-�:</nobr></label></div>
						</div>
						<div class="center-notes">
							<div class="cn">
								<div class="form-notes">
									<input type="hidden" name="MAX_FILE_SIZE" value="157286400">
									<input type="hidden" class="upfile_token" id="upfile_id_{id}" name="<?php echo ini_get("session.upload_progress.name"); ?>"  value="<?=$MODULE_DATA["output"]["upfile_id"];?>" />
									<div class="file_field"></div>
									<div><input type="text" name="upload_descr" id="upload_descr_{id}" wrap="virtual" size="48" /></div>
									<div><input name="upload_author" type="text" value="" size="48" /></div>
									<div>
										<select name="upload_year" size="1" style="width:100px">
<?php 			for($year = date('Y'); $year >= 1960; $year--) { ?>
											<option><?=$year;?></option>
<?php 			} ?>
										</select>
									</div>
									<div><input name="upload_volume" type="text" value=""/></div>
									<div><input name="upload_edition" type="text" value=""/></div>
									<div>
										<div class="datalis">
											<input name="upload_place" type="text" value="" autocomplete="off" />
											<ul>
												<li onclick="this.parentNode.parentNode.getElementsByTagName('input')[0].value = this.innerHTML;">�����������, ����</li>
												<li onclick="this.parentNode.parentNode.getElementsByTagName('input')[0].value = this.innerHTML;">������, �����</li>
											</ul>
										</div>								
									</div>
								</div>
								<div class="form-notes">
									<div><input type="checkbox" name="allow_download" /> ��������� ���������� ���� �������������</div>
									<div><input type="checkbox" name="is_html" /> <span title="���� ��� ������ ��������� - ��� ���� �������������� � ������� C����� � ������� html. ��� �������������� ����� ���������� �������������� � ������ ������ zip. ����� �������, html-�������� ����� �������� ��� ��������� �� �������." style="font-size:16px; font-weight: bold; color: #9C0605; cursor: pointer;">?</span> ��������� ��� ������-��������� (���� ������ ���� � ������� zip)</div>
								</div>
							</div>
						</div>
					</div>
					<div class="wide">
						<input type="submit" size="20" value="������" />
					</div>
					<iframe style="display:none" id="upload-frame-{id}" name="upload-frame"></iframe>
				</form>
    </div>
<?php		}
		}
		break;
			
		case "attach_file": {
			if ($MODULE_DATA["output"]["allow_attach"]) { ?>
		<script>jQuery(document).ready(function($) {FileAttach(); ajaxSearchSubj(<?=$MODULE_OUTPUT['module_id']?>); });</script>
		<div id="file_and_subj_LK"<?=(!isset($MODULE_DATA["output"]["files_list"]) && !count($MODULE_DATA["output"]["files_list"]) ? " class='hidden'" : "")?>>
			<fieldset>
			    <legend>��������� ���� � ����������</legend>
				<form id="privyazka" name="privyazka" action="<?=$EE["unqueried_uri"]?>" method="post">
					<div class="wide">
						<div class="form-notes">
							<dl>
								<dt><label>����:</label></dt>
								<dd>
									<select name="sel_file" size="1" class="sel_file">
										<option></option>
<?php			foreach ($MODULE_DATA["output"]["files_list"] as $file) { ?>
										<option value="<?=$file["id"]?>"><?=$file["name"]?></option>
<?php			} ?>
									</select>
								</dd>
								<dt><label>����������: </label></dt>
								<dd>
									<select name="sel_subject" size="1" id="subject_list" class="editable-select">
										<option></option>
<?php			if (isset($MODULE_DATA["output"]["subjects_list"])) {
					foreach ($MODULE_DATA["output"]["subjects_list"] as $depart) { ?>
										<optgroup label="<?=$depart["name"]?>">
<?php					foreach ($depart["subj"] as $subject) { ?>
											<option value="<?=$subject["sid"]?>"><?=$subject["sname"]?></option>
<?php					} ?>
										</optgroup>
<?php				}
				} ?>
									</select> 
									<input type="text" id="subject_search" size="10" title="������� �����" placeholder="������� ����� ��������" style="display: none; width: 265px; " /> <a href="javascript: void(0)" onclick="jQuery('#subject_search').show(); jQuery('#subject_list').hide(); jQuery(this).hide(); " title="����� �� ���������� ��������">������</a>
								</dd>
								<dt><label>��� �������:</label></dt>
								<dd>
									<select name="sel_view" size="1">
										<option></option>
<?php			if (isset($MODULE_DATA["output"]["files_view"])) {
					foreach ($MODULE_DATA["output"]["files_view"] as $view) { ?>
										<option value="<?=$view["id"]?>"><?=$view["view_name"]?></option>
<?php				}
				} ?>
									</select>
								</dd>
							</dl>
							<div class="wide">
								<div>
									<input type="hidden" name="referenc_file" value="1" />
									<input type="submit" value="���������" />
								</div>
							</div>
						</div>
						<div class="form-notes">
							<div class="sel_subject_list">
								<label>�����������, ������� ����������, ����� ��������:</label>
<?php			if(isset($MODULE_OUTPUT["spec_list"])) { ?>
								<dl>
<?php				foreach($MODULE_OUTPUT["spec_list"] as $spec_id => $spec) { ?>				
									<dt>
										<label title="<?=$spec['name']?>">
											<input type="checkbox" name="spec_item[<?=$spec_id?>]" value="<?=$spec['code']?>" /><?=$spec['code']?> - <?=$spec['name']?>
										</label>
									</dt>
									<dd>
										<select name="sel_spec_type[<?=$spec_id?>]" size="1" title="������� ����������">
<?php					if (isset($spec["type"])) {
							$first = true;
							foreach ($spec["type"] as $type_id => $type) { ?>
											<option value="<?=$type_id?>"<?=($first ? " selected='selected'" : "")?>><?php if($spec['old'] == 1) { ?><?=$type['code']?> - <?=$type['name']?><?php } else { ?><?=$type['name'];?><?php } ?></option>
<?php							$first = false;
							}
						} ?>
										</select>
										<select class="education" name="sel_education[<?=$spec_id?>]" size="1" title="����� ��������">
											<option value="�����" selected="selected">�����</option>
											<option value="�������">�������</option>
											<option value="����-�������">����-�������</option>
										</select>
										<div class="semester">
											�������:
											<select name="sel_semester[<?=$spec_id?>]">
												<option value="">-</option>
												<option value="1">1</option>
												<option value="2">2</option>
												<option value="3">3</option>
												<option value="4">4</option>
												<option value="5">5</option>
												<option value="6">6</option>
												<option value="7">7</option>
												<option value="8">8</option>
												<option value="9">9</option>
												<option value="10">10</option>
												<option value="11">11</option>
												<option value="12">12</option>
											</select>
										</div>
										<?php 
										if(!empty($spec['profile'])) {
										?>
										<div class="profile">
											�������: 
											<select name="sel_profile[<?=$spec_id?>]">
											<?php 
											foreach($spec['profile'] as $profile_id => $profile) {
												?>
												<option value="<?=$profile_id ?>"><?=$profile['name'] ?></option>
												<?php
											}
											?>
											</select>
										</div>
										<?php 
										}
										?>
									</dd>
<?php				} ?>
								</dl>
<?php			} ?>
							</div>
						</div>
					</div>
<?php /*				
					<div class="wide">
						<div class="form-notes title-notes">
							<div><label>�������-�:</label></div>
							<div><label>������� ����������:</label></div>
							<div><label>����� ��������:</label></div>
							<div><label>����������:</label></div>
							<div><label>��� �������:</label></div>
						</div>
						<div class="center-notes">
							<div class="cn">
								<div class="form-notes">
									<div>
										<select name="sel_spec" size="1">
											<option></option>
<?php		if (isset($MODULE_DATA["output"]["specs_list"]))
				foreach ($MODULE_DATA["output"]["specs_list"] as $spec) { ?>
											<option value="<?=$spec["code"]?>"><?=$spec["name"]?> - <?=$spec["code"]?></option>
<?php			} ?>
										</select>
									</div>
									<div>
										<select name="sel_spec_type" size="1">
											<option value=""></option>
<?php		if (isset($MODULE_DATA["output"]["specs_types_list"]))
				foreach ($MODULE_DATA["output"]["specs_types_list"] as $spec_type_id => $spec_type) { ?>
											<option value="<?=$spec_type_id?>"><?=$spec_type?></option>
<?php			} ?>
										</select>
									</div>
									<div>
										<select name="sel_education" size="1">
											<option value="�����" selected="selected">�����</option>
											<option value="�������">�������</option>
											<option value="����-�������">����-�������</option>
										</select>
									</div>
									<div>
										<select name="sel_subject" size="1">
											<option></option>
<?php		if (isset($MODULE_DATA["output"]["subjects_list"]))
				foreach ($MODULE_DATA["output"]["subjects_list"] as $depart) { ?>
											<optgroup label="<?=$depart["name"]?>">
<?php				foreach ($depart["subj"] as $subject) { ?>
												<option value="<?=$subject["sid"]?>"><?=$subject["sname"]?></option>
<?php				} ?>
											</optgroup>
<?php			} ?>
										</select>
									</div>
									<div>
										<select name="sel_view" size="1">
											<option></option>
<?php		if (isset($MODULE_DATA["output"]["files_view"]))
				foreach ($MODULE_DATA["output"]["files_view"] as $view) { ?>
											<option value="<?=$view["id"]?>"><?=$view["view_name"]?></option>
<?php			} ?>
										</select>
									</div>
								</div>
								<div class="form-notes">
									<div><label>����:</label></div>
									<div>
										<select name="sel_file" size="1">
											<option></option>
<?php		foreach ($MODULE_DATA["output"]["files_list"] as $file) { ?>
											<option value="<?=$file["id"]?>"><?=$file["name"]?></option>
<?php		} ?>
										</select>
									</div>							
								</div>
							</div>
						</div>
					</div>
	  */?>					
				</form>
			</fieldset>
		</div>
<?php			//echo '</select></p><p><input type="submit" value="���������"></p></form>';
			}
		}
		break;
	
		case "files_search": {
			$form_back = $MODULE_OUTPUT['form_back'];
			if(isset($MODULE_OUTPUT['allow_files_moderation']) && $MODULE_OUTPUT['allow_files_moderation']) {
				$allow_moderation = 1;
			} else {
				$allow_moderation = 0;
			} ?>
		<div class="files_list no-scroll<?=($allow_moderation ? " files_moderation" : "")?>">
			<fieldset class="file_search_form">
			    <legend>����� ������:</legend>
				<form action="<?=$EE["unqueried_uri"]?>" method="post">
					<dl class="form-notes">
						<dt>��������:</dt>
						<dd><input type="text" name="file_name" value="<?=(isset($form_back['file_name']) ? $form_back['file_name'] : '')?>" /></dd>						
						<dt>������� ����������: </dt>
						<dd>
						<select name="file_type">
						<option value="">---</option>
							<?php foreach($MODULE_OUTPUT["spec_type_name"] as $type => $type_name) { ?>
								<option value="<?=$type?>"<?=(isset($form_back['file_type']) && $form_back['file_type'] == $type ? ' selected="selected"' : '')?>><?=$type_name?></option>
							<?php } ?>
						</select>
						</dd>
						<dt>����������� ����������:</dt>
						<dd>
							<select name="file_spec" class="editable-select">
								<option value="">---</option>
<?php			if(isset($MODULE_OUTPUT["spec_list"])) {
					foreach($MODULE_OUTPUT["spec_list"] as $spec_id => $spec) { ?>
								<option value="<?=$spec_id?>"<?=(isset($form_back['file_spec']) && $form_back['file_spec'] == $spec_id ? ' selected="selected"' : '')?>><?=$spec['code']?> - <?=$spec['name']?></option>
<?php				}
				} ?>
							</select>
						</dd>
						<dt>��� �������:</dt>
						<dd>
							<select name="file_view">
								<option value="">---</option>
                <option value="-1"<?=(isset($form_back['file_view']) && $form_back['file_view'] == -1 ? ' selected="selected"' : '')?>>(�� ������)</ option>
<?php			if (isset($MODULE_OUTPUT["files_view"])) {
					foreach ($MODULE_OUTPUT["files_view"] as $view) { ?>
								<option value="<?=$view["id"]?>"<?=(isset($form_back['file_view']) && $form_back['file_view'] == $view["id"] ? ' selected="selected"' : '')?>><?=$view["view_name"]?></option>
<?php				}
				} ?>
							</select>
						</dd>
						<dt>�������: </dt>
						<dd>
							<select name="file_user" class="editable-select">
									<option value="">---</option>
	<?php			if (isset($MODULE_OUTPUT["users"])) {
						foreach ($MODULE_OUTPUT["users"] as $user) { ?>
									<option value="<?=$user["id"]?>"<?=(isset($form_back['file_user']) && $form_back['file_user'] == $user["id"] ? ' selected="selected"' : '')?>><?=$user["user_name"]?></option>
	<?php				}
					} ?>
								</select>
						</dd>
					</dl>
					<dl class="form-notes">
						<dt>�����:</dt>
						<dd><input type="text" name="file_author" value="<?=(isset($form_back['file_author']) ? $form_back['file_author'] : '')?>" /></dd>
						<dt>����������:</dt>
						<dd>
							<select name="file_subject" id="subject_list" class="subject_list">
								<option value="">---</option>
<?php		if (isset($MODULE_OUTPUT["subjects_list"])) {
				foreach ($MODULE_OUTPUT["subjects_list"] as $depart) { ?>
								<optgroup label="<?=$depart["name"]?>">
<?php				foreach ($depart["subj"] as $subject) { ?>
									<option value="<?=$subject["sid"]?>"<?=(isset($form_back['file_subject']) && $form_back['file_subject'] == $subject["sid"] ? ' selected="selected"' : '')?>><?=$subject["sname"]?></option>
<?php				} ?>
								</optgroup>
<?php			}
			} ?>
							</select>
							<input type="text" id="subject_search" class="subject_search" size="10" title="������� �����" placeholder="������� ����� ��������" style="display: none; width: 265px; " /> 
							<a href="javascript: void(0)" onclick="jQuery(this).parent().find('.subject_search').show(); jQuery(this).parent().find('.subject_list').hide(); jQuery(this).hide(); " class="action_edit" title="����� �� ���������� ��������">������</a>
						</dd>
						<dt>��� �������:</dt>
						<dd>
							<select name="file_year">
								<option value="">---</option>
<?php			if (isset($MODULE_OUTPUT["years"])) {
					foreach($MODULE_OUTPUT["years"] as $year) { ?>
								<option value="<?=$year?>"<?=(isset($form_back['file_year']) && $form_back['file_year'] == $year ? ' selected="selected"' : '')?>><?=$year?></option>
<?php 				}
				} ?>
							</select>
						</dd>						
						<dt>����� ��������:</dt>
						<dd>
							<select name="file_education" size="1" title="����� ��������">
								<option value="">---</option>
                <option value="-"<?=(isset($form_back['file_education']) && ($form_back['file_education'] == '-') ? ' selected="selected"' : '')?>>(�� �������)</option>
								<option value="�����"<?=(isset($form_back['file_education']) && ($form_back['file_education'] == '�����') ? ' selected="selected"' : '')?>>�����</option>
								<option value="�������"<?=(isset($form_back['file_education']) && ($form_back['file_education'] == '�������') ? ' selected="selected"' : '')?>>�������</option>
								<option value="����-�������"<?=(isset($form_back['file_education']) && ($form_back['file_education'] == '����-�������') ? ' selected="selected"' : '')?>>����-�������</option>
							</select>
						</dd>
						<dt>������</dt>
						<dd>
						<input type="radio" name="file_status" value=""<?=(!isset($form_back['file_status']) || empty($form_back['file_status']) ? ' checked="checked"' : '')?> /> �� ��������� 
						<input type="radio" name="file_status" value="1"<?=(isset($form_back['file_status']) && $form_back['file_status'] == 1 ? ' checked="checked"' : '')?> /> �������� 
						<input type="radio" name="file_status" value="-1"<?=(isset($form_back['file_status']) && $form_back['file_status'] == -1 ? ' checked="checked"' : '')?> /> �� ��������
						</dd>
						<dd><input type="submit" value="������" /></dd>
					</dl>
				</form>
			</fieldset>
			<fieldset>
			    <legend>�����:</legend>
				<div class="file_list_cont">
<?php		if (isset($MODULE_DATA["output"]["files_list"])) {  
				$dates = $MODULE_DATA["output"]["files_dates"];
				foreach ($MODULE_DATA["output"]["files_list"] as $file) { ?>
					<div class="file_list_row" id="file_row_<?=$file["id"]?>">
						<div class="left_column"><a class="file_name" href="/file/<?=$file["id"]?>/"><?=$file["descr"]?></a></div>
						<div class="center_column">
							<div class="center_cont">
								<div class="file_item_info">
									<div class="info_cont">
<?php				if(isset($file["people"]) || isset($file["author"]) || isset($file["year"])) { ?>
										<dl>
<?php					if(isset($file["people"])) { ?>
											<dt>�������:</dt>
											<dd><a href="/people/<?=$file["people"]["people_id"]?>/"><?=$file["people"]["people_name"]?></a><?=isset($dates["create"][$file["id"]]) ? ", ".$dates["create"][$file["id"]]."&nbsp;" : ""?></dd>
<?php					} else { ?>
                      <dt>�������:</dt>
											<dd><?=$file["user_name"]?><?=isset($dates["create"][$file["id"]]) ? ", ".$dates["create"][$file["id"]]."&nbsp;" : ""?></dd>
<?php         }
						if(isset($file["author"])) { ?>
											<dt>�����:</dt>
											<dd><?=$file["author"]?>&nbsp;</dd>
<?php					}
						if(isset($file["year"])) { ?>
											<dt>��� �������:</dt>
											<dd><?=$file["year"]?>&nbsp;</dd>
<?php					} ?>
										</dl>
									
<?php				} ?>
									</div>
<?php				if(isset($file["attach_file_list"])) { ?>
									<div class="show_attach_button"><a href="javascript:void(0)" class="show_attach_file">�������� ��������</a></div>
<?php				} ?>
								</div>
							</div>
						</div>
<?php			    if (isset($file["attach_file_list"])) { ?>
						<div class="attach_file_list hidden">
							<ul class="file_attaches">
<?php					
					foreach ($file["attach_file_list"] as $id => $attach) {
							$attach_status = '';
							$attach_status_title = '';
							//if($allow_moderation) {
								if(is_null($attach["approved"])) {
									$attach_status = 'no_view';
									$attach_status_title = '��� �� �����������';
								} elseif($attach["approved"] == -1) {
									$attach_status = 'no_access';
									$attach_status_title = '���������';
								} else {
									$attach_status = 'access';
									$attach_status_title = '���������';
								}
								//} ?> 
								<li class="<?=$attach_status?> attach_row" id="file-attach-<?=$attach["id"]?>">
<?php							//if($allow_moderation) {?>
									<div class="attach_status status_default" title="<?=$attach_status_title?>"></div>
<?php							//} ?>	
									<div class="attach_center" style="margin-right: -99px; ">
										<div class="attach_cont">
											<span>
<?php						if(!isset($attach["subject_name"])) { ?>
												�� ������
<?php						} else { ?>
												<a href="/subjects/<?=$attach["subject_id"]?>/"><?=$attach["subject_name"]?></a> 
<?php						} ?>
[<small><a href="<?=$EE["unqueried_uri"]?>?del_attach=<?=$id?>" class="delet_attach">�������</a></small>]
											</span>
<?php						if(!isset($attach["spec_name"])) { ?>
											�� ������
<?php						} else { ?>
											<a href="/speciality/<?=$attach["spec_code"]?>/<?=$attach["spec_id"]?>/"><?=$attach["spec_name"]?></a>
<?php						} ?>
<?php             /*if($MODULE_OUTPUT['attach_dates']['add_attach'][$id]){ ?> <?=$MODULE_OUTPUT['attach_dates']['add_attach'][$id]?><?php } */ ?>
											<p>
<?php 										if(isset($attach["profile"])) { ?>
												�������: <?=$attach["profile"]?><br />
<?php 										} ?>
												��� �������: <?=(isset($attach["spec_view"]) ? $attach["spec_view"] : "��� ������� �� ������")?><br />
												����� ��������: <?=(isset($attach["spec_education"]) ? $attach["spec_education"] : "����� �������� �� �������")?>
												<?php if(!empty($attach["semester"])) { ?><br />�������: <?=$attach["semester"] ?><?php } ?>
												<?php if (isset($dates["attach"][$id])) { ?><br />���������: <?=$dates["attach"][$id] ?><?php if (isset($dates["update"][$id])) { ?>, ���������: <?=$dates["update"][$id] ?> <?php } ?><?php } ?>
                        <?php if (isset($dates["approve"][$id]) && ($attach["approved"] == 1)) { ?><br />��������: <?=$dates["approve"][$id] ?> <?php } ?>
                        <?php if (isset($dates["decline"][$id]) && ($attach["approved"] == -1)) { ?><br />���������: <?=$dates["decline"][$id] ?> <?php } ?>
												<?php /*if (isset($dates["update"][$id])) { ?><br />�����������: <?=$dates["update"][$id] ?> <?php }*/ ?>
											</p>
										</div>
									</div>
<?php						if($allow_moderation) {?>

									<div class="moderation_delete">
									<a href="/office/metodicheskie-posobiya/?del_attach=<?=$attach["id"]?>" style="height: 100%; width: 100%; background: url('/themes/images/status_cancel.png') no-repeat scroll 0 0 transparent; display: block; " title="������� ��������" class="delet_attach"></a>
									</div>
									<div class="moderation_control">
										<a href="javascript:void()" title="������������ ��������"></a>
									</div>
<?php						} ?>
									<div class="attach_comment">
<?php						if($allow_moderation && isset($attach['comment']) && !empty($attach['comment']) && $attach["approved"] == -1) { ?>
										<blockquote><?=$attach['comment']?></blockquote>
<?php						} ?>
									</div>
<?php						if($allow_moderation) {?>
									<div class="moderation_status_form">
										<form action="<?=$EE["unqueried_uri"]?>" method="post">
											<input type="hidden" name="file_attach_id" value="<?=$id?>" />
											<dl>
												<dt>��������:</dt>
												<dd>
													<label><input type="radio" name="file_access" value="1"<?=(!isset($attach["approved"]) || is_null($attach["approved"]) || $attach["approved"] == 1 ? " checked='checked'" : "")?> />���������</label>
													<label><input type="radio" name="file_access" value="-1"<?=(isset($attach["approved"]) && $attach["approved"] == -1 ? " checked='checked'" : "")?> />���������</label>
												</dd>
												<dt>�����������</dt>
												<dd><textarea name="file_attach_comment"></textarea></dd>
											</dl>
											<input type="submit" class="moderation_form_button" value="Ok" />
										</form>
									</div>
<?php						} ?>									
								</li>
<?php					} ?>
							</ul>
						</div>
<?php				}  ?>
					</div>
<?php		    }
			} else { ?>
					����� �����������
<?php		} ?>
				</div>
            </fieldset>
            <?php 
             
//����� �������
$pager_output = $MODULE_OUTPUT["pager_output"]; 
if (count($pager_output["pages_data"]) > 1) { // ���� ���� ��� ��������, � ������� ������ �����        
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
            echo "����� �������: " . $MODULE_OUTPUT["count"]."\n";
            echo "</div>\n";

            //$CODE_SUBSTITUTES["EXTRA_HEAD_CONTENT"] .= "    <script type=\"text/javascript\" src=\"{$EE["http_scripts"]}pager.js\"></script>\n";
        }
        /* ����� ������� */ 
            ?>
<?php		if($allow_moderation) { ?>
			<script>jQuery(document).ready(function($) {ShowAttachFile();ajaxFilesModeration(<?=$MODULE_OUTPUT['module_id']?>);});</script>
<?php		} else { ?>
			<script>jQuery(document).ready(function($) {ShowAttachFile();});</script>
<?php		} ?>

		<script>jQuery(document).ready(function($) {FileAttach(); ajaxSearchSubj(<?=$MODULE_OUTPUT['module_id']?>); });</script>
		<div id="file_and_subj_LK"<?=(!isset($MODULE_DATA["output"]["files_list"]) && !count($MODULE_DATA["output"]["files_list"]) ? " class='hidden'" : "")?>>
			<fieldset>
			    <legend>��������� ���� � ����������</legend>
				<form id="privyazka" name="privyazka" action="<?=$EE["unqueried_uri"]?><?=(!empty($_GET) ? "?" . http_build_query($_GET) : "")?>" method="post">
					<div class="wide">
						<div class="form-notes">
							<dl>
								<dt><label>����:</label></dt>
								<dd>
									<select name="sel_file" size="1" class="sel_file">
										<option></option>
<?php			foreach ($MODULE_DATA["output"]["files_list"] as $file) { ?>
										<option value="<?=$file["id"]?>"><?=$file["descr"]?><?=($file["author"] ? " (".$file["author"].($file["year"] ? ", ".$file["year"] : "").") " : "")?></option>
<?php			} ?>
									</select>
								</dd>
								<dt><label>����������: <input type="text" id="subject_search" size="10" title="������� �����" style="display: none; " /></label></dt>
								<dd>
									<select name="sel_subject" size="1" id="subject_list" class="subject_list">
										<option></option>
<?php			if (isset($MODULE_DATA["output"]["subjects_list"])) {
					foreach ($MODULE_DATA["output"]["subjects_list"] as $depart) { ?>
										<optgroup label="<?=$depart["name"]?>">
<?php					foreach ($depart["subj"] as $subject) { ?>
											<option value="<?=$subject["sid"]?>"<?=(isset($form_back['file_subject']) && $form_back['file_subject'] == $subject["sid"] ? ' selected="selected"' : '')?>><?=$subject["sname"]?></option>
<?php					} ?>
										</optgroup>
<?php				}
				} ?>
									</select>
									<input type="text" class="subject_search" size="10" title="������� �����" placeholder="������� ����� ��������" style="display: none; width: 265px; " />  
									<a href="javascript: void(0)" onclick="jQuery(this).parent().find('.subject_search').show(); jQuery(this).parent().find('.subject_list').hide(); jQuery(this).hide(); " class="action_edit" title="����� �� ���������� ��������">������</a>
								</dd>
								<dt><label>��� �������:</label></dt>
								<dd>
									<select name="sel_view" size="1">
										<option></option>
<?php			if (isset($MODULE_DATA["output"]["files_view"])) {
					foreach ($MODULE_DATA["output"]["files_view"] as $view) { ?>
										<option value="<?=$view["id"]?>"<?=(isset($form_back['file_view']) && $form_back['file_view'] == $view["id"] ? ' selected="selected"' : '')?>><?=$view["view_name"]?></option>
<?php				}
				} ?>
									</select>
								</dd>
							</dl>
							<div class="wide">
								<div>
									<input type="hidden" name="referenc_file" value="1" />
									<input type="submit" value="���������" />
								</div>
							</div>
						</div>
						<div class="form-notes">
							<div class="sel_subject_list">
								<label>�����������, ������� ����������, ����� ��������:</label>
<?php			if(isset($MODULE_OUTPUT["spec_list"])) { ?>
								<dl>
<?php				foreach($MODULE_OUTPUT["spec_list"] as $spec_id => $spec) { ?>				
									<dt>
										<label title="<?=$spec['name']?>">
											<input type="checkbox" name="spec_item[<?=$spec_id?>]" value="<?=$spec['code']?>" /><?=$spec['code']?> - <?=$spec['name']?>
										</label>
									</dt>
									<dd>
										<select name="sel_spec_type[<?=$spec_id?>]" size="1" title="������� ����������">
<?php					if (isset($spec["type"])) {print_r($spec); 
							$first = true;
							foreach ($spec["type"] as $type_id => $type) { ?>
											<option value="<?=$type_id?>"<?=($first ? " selected='selected'" : "")?>><?=$type['code']?> - <?=$type['name']?></option>
<?php							$first = false;
							}
						} ?>
										</select>
										<select class="education" name="sel_education[<?=$spec_id?>]" size="1" title="����� ��������">
											<option value="�����" selected="selected">�����</option>
											<option value="�������">�������</option>
											<option value="����-�������">����-�������</option>
										</select>
										<div class="semester">
											�������:
											<select name="sel_semester[<?=$spec_id?>]">
												<option value="">-</option>
												<option value="1">1</option>
												<option value="2">2</option>
												<option value="3">3</option>
												<option value="4">4</option>
												<option value="5">5</option>
												<option value="6">6</option>
												<option value="7">7</option>
												<option value="8">8</option>
												<option value="9">9</option>
												<option value="10">10</option>
												<option value="11">11</option>
												<option value="12">12</option>
											</select>
										</div>
										<?php 
										if(!empty($spec['profile'])) {
										?>
										<div class="profile">
											�������: 
											<select name="sel_profile[<?=$spec_id?>]">
											<?php 
											foreach($spec['profile'] as $profile_id => $profile) {
												?>
												<option value="<?=$profile_id ?>"><?=$profile['name'] ?></option>
												<?php
											}
											?>
											</select>
										</div>
										<?php 
										}
										?>
									</dd>
<?php				} ?>
								</dl>
<?php			} ?>
							</div>
						</div>
					</div>
<?php /*				
					<div class="wide">
						<div class="form-notes title-notes">
							<div><label>�������-�:</label></div>
							<div><label>������� ����������:</label></div>
							<div><label>����� ��������:</label></div>
							<div><label>����������:</label></div>
							<div><label>��� �������:</label></div>
						</div>
						<div class="center-notes">
							<div class="cn">
								<div class="form-notes">
									<div>
										<select name="sel_spec" size="1">
											<option></option>
<?php		if (isset($MODULE_DATA["output"]["specs_list"]))
				foreach ($MODULE_DATA["output"]["specs_list"] as $spec) { ?>
											<option value="<?=$spec["code"]?>"><?=$spec["name"]?> - <?=$spec["code"]?></option>
<?php			} ?>
										</select>
									</div>
									<div>
										<select name="sel_spec_type" size="1">
											<option value=""></option>
<?php		if (isset($MODULE_DATA["output"]["specs_types_list"]))
				foreach ($MODULE_DATA["output"]["specs_types_list"] as $spec_type_id => $spec_type) { ?>
											<option value="<?=$spec_type_id?>"><?=$spec_type?></option>
<?php			} ?>
										</select>
									</div>
									<div>
										<select name="sel_education" size="1">
											<option value="�����" selected="selected">�����</option>
											<option value="�������">�������</option>
											<option value="����-�������">����-�������</option>
										</select>
									</div>
									<div>
										<select name="sel_subject" size="1">
											<option></option>
<?php		if (isset($MODULE_DATA["output"]["subjects_list"]))
				foreach ($MODULE_DATA["output"]["subjects_list"] as $depart) { ?>
											<optgroup label="<?=$depart["name"]?>">
<?php				foreach ($depart["subj"] as $subject) { ?>
												<option value="<?=$subject["sid"]?>"><?=$subject["sname"]?></option>
<?php				} ?>
											</optgroup>
<?php			} ?>
										</select>
									</div>
									<div>
										<select name="sel_view" size="1">
											<option></option>
<?php		if (isset($MODULE_DATA["output"]["files_view"]))
				foreach ($MODULE_DATA["output"]["files_view"] as $view) { ?>
											<option value="<?=$view["id"]?>"><?=$view["view_name"]?></option>
<?php			} ?>
										</select>
									</div>
								</div>
								<div class="form-notes">
									<div><label>����:</label></div>
									<div>
										<select name="sel_file" size="1">
											<option></option>
<?php		foreach ($MODULE_DATA["output"]["files_list"] as $file) { ?>
											<option value="<?=$file["id"]?>"><?=$file["name"]?></option>
<?php		} ?>
										</select>
									</div>							
								</div>
							</div>
						</div>
					</div>
	  */?>
	  <?php foreach($form_back as $key => $item) {
	  	?>
	  	<input type="hidden" name="<?php echo $key?>" value="<?php echo $item; ?>" />
	  	<?php 
	  }
	  ?>					
				</form>
			</fieldset>
		</div>

		</div>	
<?php //		echo '<a href="#" id="demo-attach">Attach a file</a><ul id="demo-list"></ul><a href="#" id="demo-attach-2" style="display: none;">Attach another file</a>';
		//print_r($EE);
		}
		break;
	
		case "ajax_files_moderation": {
			if(isset($MODULE_OUTPUT['ajax_files_moderation'])) {
				header('Content-Type: text/xml,  charset=windows-1251');
				$dom = new DOMDocument();
				$attach_status = $dom->createElement('attach_status');
				$dom->appendChild($attach_status);				
				$status = $dom->createTextNode($MODULE_OUTPUT['ajax_files_moderation']);
				$attach_status->appendChild($status);
				$xmlString = $dom->saveXML();	
				echo $xmlString; 
			}
		}
		break;
		
		case "create_folder": {
			if ($MODULE_DATA["output"]["allow_attach"]) { ?>
		<div id="create_folder_LK">
			<fieldset>
			    <legend>�������� �����</legend>
				<form id="creat_folder" name="creat_folder" action="<?=$EE["unqueried_uri"]?>" method="post">
					<div class="wide">
						<div class="form-notes center-notes">
							<div><label>��� �����:</label></div>
							<div>
								<input type="text" name="add[folder_name]" value="" />
								<input type="submit" value="�������" />
							</div>
						</div>
						<div class="clear"></div>
					</div>
				</form>
<?php			if(isset($MODULE_DATA["output"]["folder_list"])) { ?>
				<form id="delete_folder" name="delete_folder" action="<?=$EE["unqueried_uri"]?>" method="post">
					<div class="wide">
						<div class="form-notes">
							<div><label>��� �����:</label></div>
							<div>
								<select name="delete[folder_id]" size="1">
									<option></option>
<?php				foreach ($MODULE_DATA["output"]["folder_list"] as $folder) { ?>
									<option value="<?=$folder["id"]?>"><?=$folder["name"]?></option>
<?php				} ?>
								</select>
								<input type="submit" value="�������" />
							</div>							
						</div>
						<div class="clear"></div>
					</div>
				</form>
<?php			} ?>
			</fieldset>
		</div>
<?php		}
		}
		break;
		
		case "els": { ?>
			<div id="els">
				<h2>����� ����������</h2>
				<fieldset>
				    <form id="file_search" name="file_search" action="<?=$EE["unqueried_uri"]?>" method="post">
						<input type="hidden" id="show_publisher" value="<?=(isset($MODULE_DATA["output"]["show_publisher"]) && $MODULE_DATA["output"]["show_publisher"]) ? "1" : ""?>" />
						<dl class="form-notes">
							<dt>
								<label>��� �������</label>
							</dt>
							<dd  class="b25">
								<select name="file_umkd" size="1">
									<option value="">---</option>
									<? foreach($MODULE_DATA["output"]["umkd"] as $umkd) { ?>
									<option value="<?=$umkd['id']?>"><?=$umkd['view_name']?></option>
									<? } ?>
								</select>
							</dd>
							<dd class="b20">
								<input name="file_title" placeholder="��������" title="��������" value="��������" onfocus="if (this.value=='��������') this.value='';" onblur="if (this.value=='') this.value='��������';" type="text" />
							</dd>
							<dd class="b20">
								<input name="file_author" placeholder="�����" title="�����"  value="�����" onfocus="if (this.value=='�����') this.value='';" onblur="if (this.value=='') this.value='�����';" type="text" />
							</dd>
						</dl>
						<dl class="form-notes">
							<dt>
								<label>������� ����������</label>
							</dt>
							<dd class="b10">
								<select name="file_type" size="1">
									<option value="">---</option>
									<? foreach($MODULE_DATA["output"]["spec_type_name"] as $type => $type_name) { ?>
									<option value="<?=$type?>"><?=$type_name?></option>
								<? } ?>
								</select>
							</dd>
              <dt>
								<label>����������� ����������</label>
							</dt>
							<dd class="b10">
								<select name="file_spec" size="1">
									<option value="">---</option>
									<? foreach($MODULE_DATA["output"]["spec"] as $spec) { ?>
									<option value="<?=$spec['id']?>"><?=$spec['code'].' - '.$spec['name']?></option>
								<? } ?>
								</select>
							</dd>
							<dt>
								<label>��� �������:</label>
							</dt>
							<dd class="b10">
								<select name="file_year" size="1">
									<option value="">---</option>
									<? foreach($MODULE_DATA["output"]["years"] as $year) { ?>
									<option value="<?=$year?>"><?=$year?></option>
								<? } ?>
								</select>
							</dd>
							<dt>
								<label>����������:</label>
							</dt>
							<dd class="b10">
								<select name="file_subj" size="1">
									<option value="">---</option>
									<? foreach($MODULE_DATA["output"]["subjects"] as $subj) { ?>
									<option value="<?=$subj["id"]?>"><?=$subj["name"]?></option>
								<? } ?>
								</select>
							</dd>
							<dt>
								<label>�����:</label>
							</dt>
							<dd class="b10">
								<select name="file_education" size="1">
									<option value="">---</option>
									<option value="�����">�����</option>
											<option value="�������">�������</option>
											<option value="����-�������">����-�������</option>
								</select>
							</dd>
							<dd class="b10">
								<!-- <input type="reset" value="��������" /> --><a href="#" onclick="return false"><img id="submit_search"  src="/themes/images/empty-button.png" /></a>
							</dd>
						</dl>
					</form>
				</fieldset>	
				<div id="search_res">
				</div>
				<div class="pager_cont" id="pager_cont">
<?php		include 'Pager.tpl'; ?>
				</div>
				<script>
					jQuery(document).ready(function($) {
						Els(<?=$MODULE_OUTPUT['module_id']?>,<?=$MODULE_OUTPUT['feedback_module_id']?>);
					});
				</script>
			</div>
<?php 	}	
		break;
		
		case "els_spec_list": {
			?>
			<select name="file_spec" size="1">
									<option value="">---</option>
									<? foreach($MODULE_OUTPUT["spec"] as $spec) { ?>
									<option value="<?=$spec['id']?>"<?php if($MODULE_OUTPUT["file_spec"] == $spec["id"]) {?> selected="selected"<?php } ?>><?=$spec['code'].' - '.$spec['name']?></option>
								<? } ?>
								</select>
			<?php 
		}
		break; 
		
		case "stats": {
			?>
			<p>���������� � ����������: <?=$MODULE_OUTPUT["documents"]; ?></p>
			<p>������������������ �������������: <?=$MODULE_OUTPUT["users"]; ?></p>
			<p id="stat_downloads" style="display: none; ">��������� ���������� �� <?=date('Y')-1;?> ���: <span></span></p>
			<p id="stat_approved" style="display: none; ">����� ���������� �� <?=date('Y')-1;?> ���: <span></span></p>
			<script>
					jQuery(document).ready(function($) {
						Stats(<?=$MODULE_OUTPUT['module_id']?>);
					});
				</script>
			<?php 
		}
		break;
					
		case "get_file": {
			
		}
		break;
			
		case "download": {
			header('Content-Disposition: attachment; filename="'.basename($MODULE_OUTPUT["str_to"]).'"');
			header('Content-type: application/force-download');
			//echo $MODULE_OUTPUT["file"];
			readfile($MODULE_OUTPUT["file"]); 
		}
		break;
			
		case "link_to_file": {
			if (!$MODULE_DATA["output"]["file"]["is_html"])
				echo '<br /><b>'.$MODULE_DATA["output"]["file"]["name"].'.'.$MODULE_DATA["output"]["file"]["filename"].'</b> ';
			else
				echo '<br /><b>'.$MODULE_DATA["output"]["file"]["name"].'</b> ';
			
			if (isset($MODULE_DATA["output"]["user_author"]))
				echo ' (��������� ������������ '.($MODULE_DATA["output"]["user_author"]["href"] ? '<a href="'.$MODULE_DATA["output"]["user_author"]["href"].'" >'.$MODULE_DATA["output"]["user_author"]["name"].'</a>' : $MODULE_DATA["output"]["user_author"]["name"]).') ';
			
			if ($MODULE_DATA["output"]["allow_edit"])
				echo '<img title="�������������" id="editbutton" style="margin-top: 3px;" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" onclick="document.getElementById(\'edit\').style.display=\'inline\'; document.getElementById(\'editbutton\').style.display=\'none\'"; src="/themes/images/edit.png"></a>
				<form action="'.$EE["unqueried_uri"].'" id="edit" method="post" enctype="multipart/form-data" style="display:none;">
					<div>
					<input type="file" name="upload_file" size="40">
					<input type="hidden" name="editfile" value="'.$MODULE_DATA["output"]["file"]["id"].'">
					<input type="hidden" name="editfrom" value="linktofile">
					<input type="submit" value="OK">
					<input type="button" value="������" onclick="document.getElementById(\'edit\').style.display=\'none\'; document.getElementById(\'editbutton\').style.display=\'inline\';">
					<p><input type="checkbox" name="allow_download" '.($MODULE_DATA["output"]["file"]["is_allowed"]?"checked":"").' > ��������� ���������� ���� �������������</p>
					<p><input type="checkbox" name="is_html"'.($MODULE_DATA["output"]["file"]["is_html"]?"checked":"").' > <span title="���� ��� ������ ��������� - ��� ���� �������������� � ������� C����� � ������� html. ��� �������������� ����� ���������� �������������� � ������ ������ zip. ����� �������, html-�������� ����� �������� ��� ��������� �� �������." style="font-size:16px; font-weight: bold; color: #9C0605; cursor: pointer;">?</span>��������� ��� ������-��������� (���� ������ ���� � ������� zip)</p>
					<p>��������:<br /><input type="text" name="upload_descr_'.$MODULE_DATA["output"]["file"]["id"].'" wrap="virtual" size="50" value="'.$MODULE_DATA["output"]["file"]["descr"].'"/></p></div></form>';
			
			
				if (!$MODULE_DATA["output"]["file"]["is_html"])
					echo ' <br />'.$MODULE_DATA["output"]["file"]["descr"].'<br />'.$MODULE_DATA["output"]["filesize"].'<br /><br /><a href="/file/'.$MODULE_DATA["output"]["file_id"].'/?get='.md5(time()).'" onclick="window.open(this.href, \'_blank\'); return false;"><b>�������</b></a> / <a href="/file/'.$MODULE_DATA["output"]["file_id"].'/?get='.md5(time()).'"><b>�������</b></a>';
				else
					echo ' <br />'.$MODULE_DATA["output"]["file"]["descr"].'<br /><br /><a href="/htmldocs/'.$MODULE_DATA["output"]["file_id"].'/?get='.md5(time()).'" target="_blank"><b>�������</b></a>';
		}
		break;

		case "download_people_list": {
			if(isset($MODULE_DATA["output"]["people_list"])) {
				echo "���� &laquo;".$MODULE_DATA["output"]["file_name"]."&raquo; �������:<ul>";
				foreach ($MODULE_DATA["output"]["people_list"] as $people) {
					echo "<li>";
					if($people["username"] == "" || $people["username"] == null)
						echo $people["ip"];
					else
						echo $people["username"];
					echo "</li>";
				}
				echo "</ul>";
			}
		}
		break;
		
		case "reports": { ?>
			<div id="files_info_cont" >&nbsp;</div>
			<script>jQuery(document).ready(function($) {FilesReports(<?=$MODULE_OUTPUT['module_id']?>);});</script>
<?php		switch ($MODULE_DATA["output"]["submode"])	{
				case 'departments': { ?>
			<div>
				<br />
				�������� �������: 
				<select style="width:300px" id='departments_list'>
					<option></option>
<?php 				foreach ($MODULE_DATA['output']['faculties'] as $faculty) { ?>
					<optgroup label="<?=$faculty["name"]?>">
<?php 					if ($MODULE_DATA['output']['departments'] && !empty($MODULE_DATA['output']['departments'])) {
							foreach ($MODULE_DATA['output']['departments'] as $department) {
								if ($department['faculty_id'] == $faculty['id']) { ?>
						<option<?=(isset($_GET['dep_id']) && $_GET['dep_id'] == $department["id"] ? ' selected="selected"' : '')?> class="<?=$department['faculty_id']?>" id="<?=$department["id"]?>"><?=$department["name"]?></option>
<?php							}
							}
						} ?>
					</optgroup>
<?php 				} ?>
				</select>
				<br />
				�������� �������������� �� ��������� �����������:
				<input id="show_summary_checker" type="checkbox" <?=(isset($_GET["summary"]) && $_GET["summary"]) ? "" : "checked=checked" ?> /> 
			</div>
<?php  				if (isset($MODULE_DATA['output']['files_count'])) { ?>
			<br />
			<div>
<?//CF::Debug($MODULE_DATA['output']['files_count']);?>
				<div style="text-align:center"><h3><b>�������������� �������</b></h3></div>
				<br />
				<table border=1 style="border-collapse:collapse" >
					<tr bgcolor="#d0d0d0">
						<td align="center" style="border:1px solid #5f5f5f;">�������� ����������</td>
<?php 					if (isset($MODULE_DATA['output']["show_summary"])) {
?>
						<td title="����� �������" style="padding:3px;border:1px solid #5f5f5f;">����� �������</td>
<?php 					} else
							foreach($MODULE_DATA['output']['faculties'] as $faculty) { ?>
						<td title="<?=$faculty['name']?>" style="padding:3px;border:1px solid #5f5f5f;"><?=$faculty['short_name']?></td>
<?php 						} ?>						
					</tr>
<?php 					foreach($MODULE_DATA['output']['subjects'] as $subject) { ?>
					<tr bgcolor="#f5f5f5">
						<td style="padding:3px;border:1px solid #c0c0c0;"><?=$subject['name']?></td>
<?php 					if (isset($MODULE_DATA['output']["show_summary"])) { ?>
						<td style="padding:2px; border:1px solid #c0c0c0;" align="center">
<?php							if(key_exists($subject['name'], $MODULE_DATA['output']['files_count'])) { ?>
							<a onclick="show_files_by_subject_and_faculty(this.id);return false;" href="#" id="<?=$subject['id']?>">
								<b><?=$MODULE_DATA['output']['files_count'][$subject['name']]?></b>
							</a>
<?php							} else { ?>
							�
<?php							} ?>
						</td>
<?php 					}
						else
	 						foreach($MODULE_DATA['output']['faculties'] as $faculty) { ?>
						<td style="padding:2px; border:1px solid #c0c0c0;" align="center">
<?php							if(key_exists($subject['name'], $MODULE_DATA['output']['files_count'][$faculty['name']])) { ?>
							<a onclick="show_files_by_subject_and_faculty(this.id);return false;" href="#" id="<?=$subject['id']?>-<?=$faculty['id']?>">
								<b><?=$MODULE_DATA['output']['files_count'][$faculty['name']][$subject['name']]?></b>
							</a>
<?php							} else { ?>
							�
<?php							} ?>
						</td>
<?php 						} ?>
					</tr>
<?php 					} ?>
				</table>
			</div>
<?php 				} else if (isset($_GET['dep_id'])) { ?>
			<div>
				<br />
				<h2>�� ������ ������� ����������� ���������� ��� ������������ ����������.</h2>
			</div>
<?php 				}
				}
				break;
				
				case 'specialities': { ?>
			<div style="display:none">
				<br />
				�������� �����������: 
				<select style="width:300px" id='specialities_list' onchange="if (window.location.href.indexOf('spec_id=') == -1) window.location.href = window.location.href+'?spec_id='+this.options[this.selectedIndex].id; else window.location.href = window.location.href.replace( new RegExp('spec_id=[0-9\.]+') ,'spec_id='+this.options[this.selectedIndex].id)">
					<option></option>
<?php 				if (isset($MODULE_DATA['output']['specialities']) && $MODULE_DATA['output']['specialities']) {
						foreach ($MODULE_DATA['output']['specialities'] as $spec) { 
							if (isset($_GET['spec_id']) && $_GET['spec_id'] ==  $spec["id"]) {
								$spec_name = $spec['name'];
								$spec_code = $spec['code'];
								$spec_type = $spec["type"];
							} ?>
					<option <?=(isset($_GET['spec_id']) && $_GET['spec_id'] == $spec["id"] ? 'selected' : '')?> id="<?=$spec["id"]?>"><?=$spec["name"]?> - <?=$spec["code"].'.'.$spec["type"]?></option>;
<?php 					}
					} ?>
				</select>
			</div>
			<br />  
<?php  				if (!$MODULE_DATA['output']['files_count']) { ?>
			<style>
				#spec-reports-table tr:hover {
					background-color: #c0c0c0;
					cursor: pointer;
				}
				#spec-reports-table td.center {
					text-align: center;
				}
			</style>					
			<div>
				<table id="spec-reports-table" border=1>
					<tr>
						<th>���</th>
						<th>������������ �����������</th>
						<th>��� ������������</th>
						<th>�������������� �����������</th>
					</tr>
<?php 					foreach ($MODULE_DATA['output']['specialities'] as $spec) { ?>
					<tr id="<?=$spec['id']?>" onclick="if (window.location.href.indexOf('spec_id=') == -1) window.location.href = window.location.href+'?spec_id='+this.id; else window.location.href = window.location.href.replace( new RegExp('spec_id=[0-9\.]+') ,'spec_id='+this.id)">
						<td><?=$spec['code']?></td>
						<td><?=$spec['name']?></td>
						<td class="center"><?=$spec['type']?></td>
						<td class="center"><?=$spec['total']?></td>
					</tr>
<?php 					} ?>
				</table>
			</div>
<?php  			 	} else if (isset($MODULE_DATA['output']['files_count'])) { 
						foreach ($MODULE_DATA['output']['specialities'] as $spec) {
							if (isset($_GET['spec_id']) && $_GET['spec_id'] ==  $spec["id"]) {
								$spec_name = $spec['name']; 
								$spec_code = $spec['code']; 
								$spec_type = $spec["type"];
								break;
							}
						} ?>
			<br />
			<div>
<?// CF::Debug($MODULE_DATA['output']['files_count']);?>
				<div style="text-align:center"><h3><b>�������������� �������� ��������������� ��������� �� ����������� ���������� <?=(isset($spec_code) ? $spec_code.'.'.$spec_type : '').(isset($spec_name)?'-'.$spec_name:'')?></b></h3></div>
				<br />
				<table border=1 style="border-collapse:collapse" >
					<tr bgcolor="#d0d0d0">
						<td align="center" style="border:1px solid #5f5f5f;">�������</td>
						<td align="center" style="border:1px solid #5f5f5f;">������������ ����������</td>
<?php 					foreach($MODULE_DATA['output']['umkd'] as $umkd) { ?>
						<td align="center" style="padding:3px;border:1px solid #5f5f5f;"><?=$umkd['view_name']?></td>
<?php 					} ?>						
					</tr>
<?php 					foreach($MODULE_DATA['output']['subjects'] as $subject) { ?>
					<tr bgcolor="#f5f5f5">
						<td style="padding:5px;border:1px solid #c0c0c0;"><?=$subject['dname']?></td>
						<td style="padding:5px;border:1px solid #c0c0c0;"><?=$subject['name']?></td>
<?php 						foreach($MODULE_DATA['output']['umkd'] as $umkd) { ?>
						<td style="padding:2px; border:1px solid #c0c0c0;" align="center"><?=(key_exists($subject['name'], $MODULE_DATA['output']['files_count'][$umkd['view_name']])? '<a onclick="show_files_by_subject_and_umkd(this.id);return false;" href="" id="'.$subject['id'].'-'.$umkd['id'].'"><b>'.$MODULE_DATA['output']['files_count'][$umkd['view_name']][$subject['name']].'</a></b>' : '�' )?></td>
<?php 						} ?>
					</tr>
<?php 					} ?>
				</table>
			</div>
<?php 				} else if (isset($_GET['spec_id'])) { ?>
			<div>
				<br />
				<h2>�� ������� ����������� ����������� ���������� ��� ������������ ����������.</h2>
			</div>
<?php 				}
				}
				break;
			}
		}
		break;
	}
}

/*	elseif ($MODULE_DATA["output"]["mode"]=="get_file" || $MODULE_DATA["output"]["mode"]=="link_to_file")
		echo "<b>������ � ������� �������� ������ �������������� �������������</b>";
<script>
 <? if (isset($MODULE_DATA["output"]["messages"]["info"]) && !empty($MODULE_DATA["output"]["messages"]["info"])) 
 	foreach ($MODULE_DATA["output"]["messages"]["info"] as $msg) {
 		if (isset($MODULE_MESSAGES[$msg])) { ?>
 	 		alert("<?=$MODULE_MESSAGES[$msg]?>");	
 		<?}
 } ?>
</script>*/
?>

