<?php
if(INDEX_BTC != true){ exit; }

//Rien (liste unt + src)
if(!$_sub)
{
	$_tpl->set("btc_act",false);

	
	$unt_todo = UntTodo::get($_user['mid']);
	
	foreach($unt_todo as $id => $value) {
		if(!in_array($btc_type,$_ses->getConf("unt",$value['utdo_type'],"need_btc")))
			unset($unt_todo[$id]);
	}
	

	$src_todo = SrcTodo::get($_user['mid']);

	$_tpl->set("unt_todo",$unt_todo);
	$_tpl->set("src_todo",$src_todo);
	$_tpl->set("src_conf",$_ses->getConf("src"));

}
