<?php
//Verif
if(!defined("_INDEX_") || !$_ses->canDo(DROIT_ADM_COM)){ exit; }

$race = request('race','uint','get',1);
$_tpl->set("admin_tpl","modules/btc/admin.tpl");
$_tpl->set("admin_name","Compétences");
$_tpl->set('list_btc',Config::get($race, 'btc'));
$_tpl->set('race_sel',$race);

?>
