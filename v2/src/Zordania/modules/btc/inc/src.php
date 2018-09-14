<?php
if(!defined("INDEX_BTC")){ exit; }
 
 if($_sub == "cancel_src")
{
	$_tpl->set("btc_act","cancel_src");
	$sid = request("sid", "uint", "get");
	
	if(!$sid)
		$_tpl->set("btc_no_sid",true);
	else {
		if(SrcTodo::del($_user['mid'], $sid)) {
			Res::mod($_user['mid'], $_ses->getConf("src", $sid, "prix_res"), 0.5);
			$_tpl->set("btc_ok",true);
		} else
		$_tpl->set("btc_ok",false);
	}
	
}
//Formulaire src + Liste src
elseif($_sub == "src")
{
	$_tpl->set("btc_act","src");
	
	$src_todo = SrcTodo::get($_user['mid']);
	
	$_tpl->set("src_todo",$src_todo);
	
	$conf_src = $_ses->getConf("src");
	foreach($conf_src as $type => $value) { 
		$src_tmp[$type]['bad'] = $mbr->can_src($type);
		$src_tmp[$type]['conf'] = $value;
	}

	$src_array = [];
	foreach($src_tmp as $sid => $array) {
		if($array['bad']['need_src'] || $array['bad']['need_no_src'] || $array['bad']['need_btc']) continue;
		$src_array[$sid] = $array;
	}

	unset($src_tmp);

	$_tpl->set("src_dispo", $src_array);
	$_tpl->set("res_utils", $mbr->res());
	$_tpl->set("src_conf", $conf_src);
}
//Nouvelle src
elseif($_sub == "add_src")
{
	$type = request("type", "uint", "post");
	
	$src_todo = SrcTodo::get($_user['mid']);
	$_tpl->set("btc_act","add_src");
	$_tpl->set("src_type", $type);
	if(!$type)
		$_tpl->set("btc_no_type",true);
	else if(count($src_todo) + 1 > TODO_MAX_SRC)
		$_tpl->set("btc_src_max",TODO_MAX_SRC);
	else if(isset($src_todo[$type]))
		$_tpl->set("src_pending", true);
	else
	{
		$array = $mbr->can_src($type);
		if(isset($array['do_not_exist']))
			$_tpl->set("btc_no_type",true);
		else {
			$ok = !($array['need_no_src'] || $array['prix_res'] || $array['need_src'] || $array['done'] || $array['need_btc']);
			$_tpl->set("src_infos", $array);
			$_tpl->set("btc_ok", $ok);
			if($ok) {
				SrcTodo::add($_user['mid'], $type);
				Res::mod($_user['mid'], $_ses->getConf("src", $type, "prix_res"), -1);
			}
		}
	}
}
?>
