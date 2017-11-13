<?php
if(!defined("_INDEX_")){ exit; }
if(!can_d(DROIT_PLAY)){
	$_tpl->set("need_to_be_loged",true); 
}else{

require_once("lib/btc.lib.php");
require_once("lib/src.lib.php");

// TODO
$_tpl->set("module_tpl","modules/vlg/vlg.tpl");

$btc_array = get_nb_btc_done($_user['mid']);
$_tpl->set("btc_max", get_conf("race_cfg", "btc_nb"));
$_tpl->set("btc_conf", get_conf("btc"));
$_tpl->set("src_conf", get_conf("src"));
$_tpl->set("src_array", get_src_done($_user['mid']));
$_tpl->set("btc_array", $btc_array);


}
?>
