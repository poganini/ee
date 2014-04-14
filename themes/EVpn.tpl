<style>
p.module_message {
	color: #f00;
	font-weight: bold;
}

div.vpn_user_info {
	width: 65%; 
	padding-right: 30px;
}

div.vpn_account_info {
	display:none; 
	padding-left:10px;
}
</style>


<?php

/*
	 EVpn.tpl, версия 1.5, 11/25/13  
*/


$MODULE_OUTPUT = $MODULE_DATA["output"];

if (isset($MODULE_OUTPUT["mode"])) {

	$fieldsCyr = array('name'=>'ФИО:', 'office'=>'Юридический адрес:', 'whereis'=>'Физический адрес:', 'phone'=>'Телефон:',
			'fax'=>'Факс:', 'techphone'=>'Телефон тех.специалиста:', 'person'=>'Руководитель:', 'technic'=>'Тех. специалист:',
			 'email'=>'Электронная почта:', 'type'=> 'Тип клиента:', 'cdate'=> 'Дата создания:', 'operator'=>'Оператор:');
	
	$createAndEditFormPattern = '
		<div><strong>{TITLE}</strong><p></p><p><a href="'.$Engine->engine_uri.'"><strong>Список всех пользователей</strong></a></p></div><hr />
		<div class="vpn_user_info">
		<table class="nsau_table">
		<tr><td width="35%">ФИО или название организации</td><td width="65%"><input type="text" name="cinfo[name]" value="%[name]%" /></td></tr>
		<tr><td width="35%">Юридический адрес</td><td width="65%"><input type="text" name="cinfo[office]" value="%[office]%" /></td></tr>
		<tr><td width="35%">Физический адрес</td><td width="65%"><input type="text" name="cinfo[whereis]" value="%[whereis]%" /></td></tr>
		<tr><td width="35%">Телефон</td><td width="65%"><input type="text" name="cinfo[phone]" value="%[phone]%" /></td></tr>
		<tr><td width="35%">Телефон тех.специалиста</td><td width="65%"><input type="text" name="cinfo[techphone]" value="%[techphone]%" /></td></tr>
		<tr><td width="35%">Факс</td><td width="65%"><input type="text" name="cinfo[fax]" value="%[fax]%" /></td></tr>
		<tr><td width="35%">Руководитель</td><td width="65%"><input type="text" name="cinfo[person]" value="%[person]%" /></tr>
		<tr><td width="35%">Технический специалист</td><td width="65%"><input type="text" name="cinfo[technic]" value="%[technic]%" /></td></tr>
		<tr><td width="35%">Электронная почта</td><td width="65%"><input type="text" name="cinfo[email]" value="%[email]%" /></td></tr>
		<tr><td width="35%">Тип клиента</td><td width="65%"><select name="cinfo[type]" /><option value="1">физическое лицо</option><option value="2">юридическое лицо</option><option value="3">служебный</option></select></td></tr>
		<tr><td align="right" colspan=2><input type="submit" value="Сохранить" /></td></tr>
		</table></div>';
	
	if (!empty($MODULE_OUTPUT['messages']['bad'])) {	
		foreach($MODULE_OUTPUT['messages']['bad'] as $badMsg)
			echo '<p class="module_message">'.$badMsg.'</p>';
	}
	if (!empty($MODULE_OUTPUT['messages']['good'])) {	
		foreach($MODULE_OUTPUT['messages']['good'] as $goodMsg)
			echo '<p class="module_message">'.$goodMsg.'</p>';
	}
			
	if(!$MODULE_OUTPUT["exception"])
	switch ($MODULE_OUTPUT["mode"]) {

		case "list": ?>
			<h2>Пользователи ВПН</h2>
			<p>
				<b><a href="create">Создать пользователя</a></b></p><hr />
        <p>
			Сортировать по:
<?php 		if (isset($_GET["sortby"]) && $_GET["sortby"] == "login") { ?>
			логину | <a href="?sortby=name">владельцу</a> &nbsp;&nbsp;&nbsp;
      <?php if (isset($_GET["order"]) && $_GET["order"] == "DESC") { ?>по убыванию | <a href="?sortby=login&order=ASC">по возрастанию</a><?php } else { ?><a href="?sortby=login&order=DESC">по убыванию</a> | по возрастанию<?php } ?> 
<?php 		} else { ?>
			<a href="?sortby=login">логину</a> | владельцу &nbsp;&nbsp;&nbsp;
      <?php if (isset($_GET["order"]) && $_GET["order"] == "DESC") { ?>по убыванию | <a href="?sortby=name&order=ASC">по возрастанию</a><?php } else { ?><a href="?sortby=name&order=DESC">по убыванию</a> | по возрастанию<?php } ?>
<?php 		} ?>
		</p>
    
				<table>

        <?
				if (isset($MODULE_OUTPUT["clients"]) && !empty($MODULE_OUTPUT["clients"]))
					foreach ($MODULE_OUTPUT["clients"] as $client) {
						?>
            <tr>
            <td><a href="view/<?=$client["id"]?>"><?=$client["name"]?></a></td>
            <td><a href="editAcc?acc=<?=$client['login']?>&client_id=<?=$client["id"]?>"><?=$client['login']?></a></td>
            <td><a href="delete?client_id=<?=$client["id"]?>" title="удалить" onclick="if (!confirm('Вы уверены, что хотите удалить пользователя <?=$client["name"]?>?')) return false;"><img src="/themes/images/delete.png" /></a></td>
            </tr><?
				}
			?></table><?
		break;
		
		case "create": ?>
		<form name="createForm" action="<?=$Engine->engine_uri?>create" method="POST"> <?
			$createAndEditForm = str_replace('{TITLE}', 'Создание нового пользователя', $createAndEditFormPattern);
			$createAndEditForm = preg_replace("/%\[\S+\]%/", "", $createAndEditForm);
			echo $createAndEditForm; ?>
		</form>
<?		break;
		
		case "edit": ?> 
			<form name="editForm" action="<?=$Engine->engine_uri?>edit/<?=$MODULE_OUTPUT["clientInfo"]["id"]?>" method="POST"> <?
			if (isset($MODULE_OUTPUT["clientInfo"]) && !empty($MODULE_OUTPUT["clientInfo"])) {
				$createAndEditForm = str_replace('{TITLE}', 'Редактирование пользователя '.'<a href="'.$Engine->engine_uri.'view/'.$MODULE_OUTPUT["clientInfo"]["id"].'">'.$MODULE_OUTPUT["clientInfo"]["name"].'</a>' , $createAndEditFormPattern);
				foreach ($MODULE_OUTPUT["clientInfo"] as $field=>$value) {
					$createAndEditForm = str_replace("%[".$field."]%", $value, $createAndEditForm);
				}
				$createAndEditForm = str_replace("option value=\"".$MODULE_OUTPUT["clientInfo"]['type_id']."\"", "option value=\"".$MODULE_OUTPUT["clientInfo"]['type_id']."\" selected", $createAndEditForm);
				echo $createAndEditForm;
			}
			 ?>
		</form> <?
		break; 

		case "createAcc":
		?>
			<div>Пользователь <a href="<?=$Engine->engine_uri?>view/<?=$_REQUEST['client_id']?>"><?=$MODULE_OUTPUT['client_name']?></a><hr /></div>
			<form name="createAcc" method="post" action="<?=$Engine->engine_uri?>createAcc?client_id=<?=$_REQUEST['client_id']?>" onSubmit="return checkAllEmpty(this)">
			<table>
				<tr>
					<th colspan=2>Создание нового аккаунта</th>
				</tr>
				<!-- <tr>
					<td>IP-адрес</td>
					<td>
						<? foreach($MODULE_OUTPUT['ips'] as $ip) {
							if (!isset($selIp)) $selIp = ereg_replace("\.0$", "", long2ip($ip)); 
							if (isset($_REQUEST['net']) && $_REQUEST['net']==$ip) 
								$selIp = ereg_replace("\.0$", "", long2ip($ip));
						 	} 
						?>
						<?=$selIp?>.<?=(!isset($_REQUEST['ip4']) || ($_REQUEST['ip4']<1 || $_REQUEST['ip4']>254 || !ereg('^[0-9]{1,3}$',$_REQUEST['ip4']) || !in_array(($MODULE_OUTPUT['net_ip']+$_REQUEST['ip4']),$MODULE_OUTPUT['free_ips']))) ? $MODULE_OUTPUT['free_ips'][0]-$MODULE_OUTPUT['net_ip'] : $_REQUEST['ip4'] ?>
						<br />
						<select name="net" onChange="selectNet()">
						<? foreach($MODULE_OUTPUT['ips'] as $ip) { ?>
							<option value="<?=$ip.( (isset($_REQUEST['net']) && $_REQUEST['net']==$ip) ? '" selected' :'"')?>><?=ereg_replace("\.0$", "", long2ip($ip))?></option>
						<? } ?> 
						?>
						</select>
						<input type="text" name="ip4" value="<?=(!isset($_REQUEST['ip4']) || ($_REQUEST['ip4']<1 || $_REQUEST['ip4']>254 || !ereg('^[0-9]{1,3}$',$_REQUEST['ip4']) || !in_array(($MODULE_OUTPUT['net_ip']+$_REQUEST['ip4']),$MODULE_OUTPUT['free_ips']))) ? $MODULE_OUTPUT['free_ips'][0]-$MODULE_OUTPUT['net_ip'] : $_REQUEST['ip4'] ?>" size="2" maxlength="3" />
					</td>
				</tr> -->
				<tr>
					<td>Логин</td>
					<td>
						<input type="hidden" name="net" value="<?=isset($_REQUEST['net']) ? $_REQUEST['net'] : $MODULE_OUTPUT['ips'][0] ?>" />
						<input type="hidden" name="ip4" value="<?=(!isset($_REQUEST['ip4']) || ($_REQUEST['ip4']<1 || $_REQUEST['ip4']>254 || !ereg('^[0-9]{1,3}$',$_REQUEST['ip4']) || !in_array(($MODULE_OUTPUT['net_ip']+$_REQUEST['ip4']),$MODULE_OUTPUT['free_ips']))) ? $MODULE_OUTPUT['free_ips'][0]-$MODULE_OUTPUT['net_ip'] : $_REQUEST['ip4'] ?>" />
						<input type="text" name="login" value="<?= isset($_REQUEST["login"]) ? $_REQUEST["login"] : ''?>" />
					</td>
				</tr>
				<tr>
					<td>Пароль</td>
					<td>
						<input type="password" name="pw1">
					</td>
				</tr>
				<tr>
					<td>Подтверждение пароля</td>
					<td>
						<input type="password" name="pw2">
					</td>
				</tr>
				<tr>
					<td>Тариф</td>
					<td>
						<select name="tariff_id">
						<? foreach($MODULE_OUTPUT['tariff_plans'] as $plan) { ?>
							<option value="<?=$plan['id'].( (isset($_REQUEST["tariff_id"]) && $_REQUEST["tariff_id"] == $plan['id'] ) ? '" selected' : '"')?>><?=$plan['name']?></option>
						<? } ?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="right">
						<input type="hidden" name="client_id" value="<?=isset($_GET['client_id']) ? $_GET['client_id'] : ''?>" />
						<input type="submit" value="Создать" />
					</td>
				</tr>
			</table>
			</form>
		<?
		break;

		case "view":
		?>
		<div>
			<p><a href="<?=$Engine->engine_uri?>"><strong>Список всех пользователей</strong></a></p><br />
			<h2>Информация о пользователе</h2>
		</div>
		<hr />
		<div class="float-left vpn_user_info" >
			<form name="viewForm" action="<?=$Engine->engine_uri?>edit/<?=$MODULE_OUTPUT["clientInfo"]["id"]?>" method="POST">
			<table class="nsau_table"><?
			if (isset($MODULE_OUTPUT["clientInfo"]) && !empty($MODULE_OUTPUT["clientInfo"]))
				foreach ($MODULE_OUTPUT["clientInfo"] as $field=>$value) {
					if (isset($fieldsCyr[$field])) {?>
					<tr><td width="35%"><?=$fieldsCyr[$field]?></td><td width="65%" align='center'><?=$value ? $value : '-'?></td></tr>
			<?		}
				}
			?>
				<tr><td align="right" colspan=2><input type="submit" value="Изменить" /></td></tr>
			</table>
			</form>
		</div>
		<div class="float-left">
			<table>
				<tr>
					<th>ВПН-аккаунты пользователя</th>
				</tr>
				<? if (isset($MODULE_OUTPUT["clientInfo"]["accounts"]) && !empty($MODULE_OUTPUT["clientInfo"]["accounts"]))
					foreach($MODULE_OUTPUT["clientInfo"]["accounts"] as $account) {	?>
				<tr>
					<td><a id="acc_<?=$account["id"]?>" onClick="showAccInfo(this.id); return false; " href="#"><?=$account["login"]?></a>&nbsp;&nbsp;<a href="<?=$Engine->engine_uri?>editAcc?acc=<?=$account["login"]?>&client_id=<?=$MODULE_OUTPUT["clientInfo"]["id"]?>" title="редактировать" ><img src="/themes/images/edit.png" /></a>&nbsp;&nbsp;<a href="<?=$Engine->engine_uri?>delAcc?acc=<?=$account["login"]?>&client_id=<?=$MODULE_OUTPUT["clientInfo"]["id"]?>" title="удалить" onclick="if (!confirm('Вы уверены, что хотите удалить аккаунт <?=$account["login"]?>?')) return false;"><img src="/themes/images/delete.png" /></a>
						<div id="acc_<?=$account["id"]?>_info" class="vpn_account_info" >
							<table>
								<tr>
									<td colspan="2"><strong>Информация об аккаунте:</strong></td>
								</tr>
								<tr>
									<td>Логин:</td>
									<td><?=$account["login"]?></td>
								</tr>
								<tr>
									<td>IP-адрес:</td>
									<td><?=ereg_replace("\.0$", "", long2ip($account["ip_ex"]))?></td>
								</tr>
								<tr>
									<td>Тарифный план:</td>
									<td><?=$account["name"]?></td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
				<? } else { ?>
				<tr>
					<td align="center">-</td>
				</tr>
				<? } ?>
				<tr>
					<td align="center"><a href="<?=$Engine->engine_uri?>createAcc?client_id=<?=$MODULE_OUTPUT["clientInfo"]["id"]?>">Создать vpn-аккаунт</a></td>
				</tr>
			</table>
		</div>
<?php 
		break;
		
		case "editAcc": ?>
			<div>Пользователь <a href="<?=$Engine->engine_uri?>view/<?=$MODULE_OUTPUT['client_id']?>"><?=$MODULE_OUTPUT['client_name']?></a><hr /></div>
			<form name="editAcc" method="post" action="<?=$Engine->engine_uri?>editAcc?acc=<?=$MODULE_OUTPUT['accountInfo']['login']?>&client_id=<?=$_REQUEST['client_id']?>" onSubmit="return checkAllEmpty(this)">
			<table>
				<tr>
					<th colspan=2>Редактирование аккаунта "<?=$MODULE_OUTPUT['accountInfo']['login']?>"</th>
				</tr>
				<tr>
					<td>IP-адрес</td>
					<td>
						<?php if($Engine->OperationAllowed($MODULE_OUTPUT["module_id"], "vpn.ip.handle", /*-1*/$MODULE_OUTPUT['client_id'], $Auth->usergroup_id) /* || $Engine->OperationAllowed($MODULE_OUTPUT["module_id"], "vpn.ip.handle", -1, $Auth->usergroup_id) */) { ?>
						<input type="text" name="ip_ex" value="<?=$MODULE_OUTPUT['accountInfo']['ip_ex']?>" size="20" maxlength="20" />
						<?php } else { ?>
						<?=$MODULE_OUTPUT['accountInfo']['ip_ex']?>
						<input type="hidden" name="ip_ex" value="<?=$MODULE_OUTPUT['accountInfo']['ip_ex']?>" />
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td>Пароль</td>
					<td>
						<input type="hidden" name="login" value="<?=$MODULE_OUTPUT['accountInfo']['login']?>" />
						<input type="password" name="pw1">
					</td>
				</tr>
				<tr>
					<td>Подтверждение пароля</td>
					<td>
						<input type="password" name="pw2">
					</td>
				</tr>
				<tr>
					<td>Тариф</td>
					<td>
						<select name="tariff_id">
						<? foreach($MODULE_OUTPUT['tariff_plans'] as $plan) { ?>
							<option value="<?=$plan['id'].( (isset($MODULE_OUTPUT['accountInfo']['tariff_id']) && $MODULE_OUTPUT['accountInfo']['tariff_id'] == $plan['id'] ) ? '" selected' : '"')?>><?=$plan['name']?></option>
						<? } ?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="right">
						<input type="hidden" name="client_id" value="<?=isset($_GET['client_id']) ? $_GET['client_id'] : ''?>" />
						<input type="submit" value="Изменить" />
					</td>
				</tr>
			</table>
			</form>
		<?	
		break;
		
		case "delAcc": ?>
			<div>
				<a href="<?=$Engine->engine_uri?>view/<?=$MODULE_OUTPUT["client_id"]?>">Вернуться на страницу пользователя</a>
			</div>
		<?
		break;
	}

}

?>

<script>
	function showAccInfo(accHrefId) {
		document.getElementById(accHrefId+'_info').style.display = document.getElementById(accHrefId+'_info').style.display == 'block' ? 'none' : 'block';
	}
	
	function checkAllEmpty(form) {
		var fieldsToCheck = {'IP-адрес':'ip4', 'Логин':'login', 'Пароль':'pw1', 'Внешний IP': 'ip_ex'};
		for (var ind in fieldsToCheck) 
			if (form[fieldsToCheck[ind]] && !form[fieldsToCheck[ind]].value) {
				alert('Необходимо заполнить поле '+ind);
				return false;
			}
		for (var ind in form.login.value)
			if (form.login.value[ind].search(/[A-Za-z_0-9]/i) == -1 ) {
				alert('Поле Логин может содержать только цифры и латинские символы');
				return false;
			}
		if (form.pw1.value != form.pw2.value) {
			alert('Значения полей Пароль и Подтверждение пароля не совпадают');
			return false;
		} 
		if (form.pw1.value.length < <?=$MODULE_OUTPUT['pass_min_length']?>) {
			alert('Длина пароля должна составлять не менее <?=$MODULE_OUTPUT['pass_min_length']?> символов');
			return false;
		}
		return true;
	}

	function selectNet() {
		form = document.forms['createAcc'];
		sLogin = form.login.value;
		sNet = form.net.options[form.net.selectedIndex].value;
		sTariff = form.tariff_id.options[form.tariff_id.selectedIndex].value;
		sLocation = '<?=$Engine->engine_uri.$MODULE_OUTPUT['module_uri']?>?client_id=<?=$_GET["client_id"]?>&login='+sLogin+'&net='+sNet+'&tariff_id='+sTariff;
		location.replace(sLocation);
	}
</script>