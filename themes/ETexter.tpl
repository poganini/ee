<?php
// version: 2.3
// date: 2012-05-10

$flag = true; // отображать ли сам текст
$flag2 = false; // отображать ли ссылку на редактирование

if(isset($MODULE_OUTPUT['mode'])) {
	switch($MODULE_OUTPUT['mode']) {
		case 'normal': {			
			if (isset($MODULE_OUTPUT["privileges"]["item.view"]) && $MODULE_OUTPUT["privileges"]["item.view"]) {
				if ($MODULE_OUTPUT["privileges"]["item.alter"]) {
					if (isset($_GET["node"], $_GET["action"]) && ($_GET["node"] == $NODE_ID) && ($_GET["action"] == "edit")) {
						$flag = false; ?>
			<a name="form"></a>
<?php					/*$messages = $MODULE_OUTPUT["messages"];
						foreach ($messages["good"] as $data) {
							echo "<p class=\"message\">$data</p>\n";
						}
						foreach ($messages["bad"] as $data) {
							echo "<p class=\"message red\">$data</p>\n";
						}*/ ?>
			<form action="<?=$EE["unqueried_uri"]?>" method="post">
				<h2>Редактирование текста</h2>
				<textarea name="<?=$NODE_ID?>[save][text]" cols="60" rows="15" class="wysiwyg"><?php echo htmlspecialchars($MODULE_OUTPUT["text"]); ?></textarea>
				<p><input type="submit" value="Сохранить изменения" /> <input type="reset" value="Сброс" /> <input type="submit" name="<?=$NODE_ID?>[save][cancel]" value="Отмена" /></p>
			</form>
<?php 				} else {
						$flag2 = true;
					}
				}
			}
			if (isset($_POST["node"], $_POST["action"]) && ($_POST["action"] == "edit#form")) {
				header("Content-type: text/html; charset=windows-1251");
				$flag = false;
				$flag2 = false; ?>
			<a name="form"></a>
<?php			$messages = $MODULE_OUTPUT["messages"];
				foreach ($messages["good"] as $data) {
					echo "<p class=\"message\">$data</p>\n";
				}
				foreach ($messages["bad"] as $data) {
					echo "<p class=\"message red\">$data</p>\n";
				} ?>
			<form action="<?=$_POST["uri"]?>" method="POST">
				<h2>Редактирование текста</h2>
				<textarea name="<?=$_POST["node"]?>[save][text]" cols="60" rows="15" class="wysiwyg"><?php echo htmlspecialchars($MODULE_OUTPUT["text"]); ?></textarea>
				<p><input type="submit" value="Сохранить изменения" /> <input type="reset" value="Сброс" /> <input type="submit" name="<?=$_POST["node"]?>[save][cancel]" value="Отмена" /></p>
			</form>
<?php		} 			
			
			if ($flag2) { ?>
			<div id="node<?=$NODE_ID?>">
<?php		}
			
			if ($flag) { ?>
				<?=$MODULE_OUTPUT["text"]?>
<?php		}
			
			if ($flag2) {
				$class = "";
				if(isset($MODULE_OUTPUT["mode_edit"])) {
					$class = $MODULE_OUTPUT["mode_edit"] == "modal_edit" ? $MODULE_OUTPUT["mode_edit"] : "";
				} ?>
				<p class="actions">[&nbsp;<a class="<?=$class?>" href="<?=$EE["unqueried_uri"]?>?node=<?=$NODE_ID?>&amp;action=edit#form&amp;mode=modal&amp;item_id=<?=$MODULE_OUTPUT["item_id"]?>" onmouseover="document.getElementById('node<?=$NODE_ID?>').style.backgroundColor = '#95c27c'" onmouseout="document.getElementById('node<?=$NODE_ID?>').style.backgroundColor = ''">Редактировать текст</a>&nbsp;]</p>
			</div>
<?php		}
		}
		break;
	}
}
?>