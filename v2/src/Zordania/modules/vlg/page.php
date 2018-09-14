<?php
if(!defined("_INDEX_")){ exit; }
if(!$_ses->canDo(DROIT_PLAY)){
	$_tpl->set("need_to_be_loged",true); 
}else{

if($_user['race'] == 2 || $_user['race'] == 3 || $_user['race'] == 5){
	// la forteresse est une img de fond specifique
	$forteresse = $_ses->getConf('race_cfg', 'btc_nb');
}else{
	// la forteresse est un batiment normal
	$forteresse = false;
}
$btc_array = Btc::getNbDone($_user['mid']);
// img de fond du village
$imgvlg = 'vlg0.jpg';
if($forteresse !== false){
	foreach($btc_array as $type => $value){
		if($type == $forteresse){
			$imgvlg = 'vlgf0.jpg';
			break;
		}
	}
}
	
$_tpl->set("module_tpl","modules/vlg/vlg.tpl");

$_tpl->set("btc_max", $_ses->getConf("race_cfg", "btc_nb"));
$_tpl->set("btc_conf", $_ses->getConf("btc"));
$_tpl->set("src_conf", $_ses->getConf("src"));
$_tpl->set("src_array", Src::get($_user['mid']));
$_tpl->set("btc_array", $btc_array);
$_tpl->set("forteresse", $forteresse);
$_tpl->set("imgvlg", $imgvlg);

}
