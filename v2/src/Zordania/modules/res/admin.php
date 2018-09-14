<?php
//Verif
if(!defined("_INDEX_") || !$_ses->canDo(DROIT_ADM_COM)){ exit; }

$_tpl->set("admin_tpl","modules/res/admin.tpl");
$_tpl->set("admin_name","Ressources");

$res_array = array();
/* decroiser le tableau : $res_array[res type][race] = conf */
foreach ($_races as $i => $value)
	if($i != 0) {
		$tmp = Config::get($i, 'res');
		foreach($tmp as $res => $val)
			 $res_array[$res][$i] = $val;
	}

$_tpl->set('res_array',$res_array);

?>
