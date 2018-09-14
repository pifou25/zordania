<?php
if(!defined("_INDEX_") || !$_ses->canDo(DROIT_PLAY)){ exit; }
if(!$_ses->canDo(DROIT_PLAY)) 
	$_tpl->set("need_to_be_loged",true); 
else {

$_tpl->set("module_tpl","modules/res/res.tpl");
$_tpl->set("res_dispo",Res::get($_user['mid']));
$_tpl->set("trn_dispo",Trn::get($_user['mid']));

}
?>
