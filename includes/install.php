<?php


$destFolder = "scripts";
$destFile = "ee.zip";

$zipPathes = array();
$files = scandir(".");

if (!empty($files))
	foreach($files as $fileName) {
	//$ext = substr(strrchr($fileName, '.'), 1);
	if ($fileName == $destFile) 
		$zipPathes[] = $fileName;
	} 

if (!empty($zipPathes))
	foreach($zipPathes as $zipPath) {
		$zip = new ZipArchive();
		$zip->open($zipPath);
		$zip->extractTo($destFolder);
	}


?>