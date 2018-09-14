<?php
//Verif
if(!defined("_INDEX_")){ exit; }

if(!$_ses->canDo(DROIT_SITE))
	$_tpl->set("need_to_be_loged",true);
else if(!$_ses->canDo(DROIT_ADM))
	$_tpl->set("cant_view_this",true);
else {

$module = request("module", "string", "get");

$_tpl->set("module_tpl","modules/admin/admin.tpl");

if(!empty($module) && file_exists(MOD_DIR . "$module/admin.php"))
	require_once(MOD_DIR . "$module/admin.php");
else {
	$handle = opendir(MOD_DIR);
	$pages = array();
	while ($file = readdir($handle)) {
		if ($file != "." && $file != ".." && file_exists(MOD_DIR . "$file/admin.php"))
			$pages[]=$file;
	}
	closedir($handle);
	$_tpl->set("admin_array",$pages);
}
}
