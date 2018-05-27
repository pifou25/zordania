<?php
if(!defined("_INDEX_") || !can_d(DROIT_PLAY)){ exit; }
if(!can_d(DROIT_PLAY)) 
	$_tpl->set("need_to_be_loged",true); 
else {

require_once("lib/trn.lib.php");

$_tpl->set("module_tpl","modules/trn/trn.tpl");
$_tpl->set("trn_dispo",Trn::get($_user['mid']));

}
?>
