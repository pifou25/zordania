<?php
/*
* can_src(), ini_src() ne peuvent être utilisés que dans le site, et pour quelqu'un de connecté
* avec le bon $_user['race']
*/

/* Verifie qu'on peut faire telle ou telle recherche */
function can_src($mid, $type, & $cache = array()) {
	$mid = protect($mid, "uint");
	$type = protect($type, "uint");
	$cache = protect($cache, "array");

	$bad_src = array('need_src' => array(), 'need_no_src' => array());
	$bad_btc = array();
	$bad_res = array();
	$done = false;
	
	if(!get_conf("src", $type))
		return array("do_not_exist" => true);

	/* Bâtiments */
	$need_btc = get_conf("src", $type, "need_btc");
	$cond_btc = $need_btc;

	if(!isset($cache['btc_done'])) {
		$have_btc = Btc::getNbDone($mid, $cond_btc);
	} else
		$have_btc = $cache['btc_done'];

	/* Recherches */
	$cond_src = array($type);

	$need_src = get_conf("src", $type, "need_src");
	$need_no_src = get_conf("src", $type, "need_no_src");
	$cond_src = array_merge($need_src,$need_no_src);

	if(!isset($cache['src'])) {
		$have_src = Src::get($mid, $cond_src);
		$have_src = index_array($have_src, "src_type");
	} else
		$have_src = $cache['src'];

	if(!isset($cache['src_todo'])) {
		$todo_src = SrcTodo::get($mid, array($type));
		$todo_src = index_array($todo_src, "src_type");
	} else
		$todo_src = $cache['src_todo'];

	/* Les recherches qu'on ne doit pas avoir */
	foreach($need_no_src as $src_type) {
		if(isset($have_src[$src_type]))
			$bad_src['need_no_src'][$src_type] = $src_type;
	}

	/* Les recherches qu'il faut avoir */
	foreach($need_src as $src_type) {
		if(!isset($have_src[$src_type]))
			$bad_src['need_src'][$src_type] = $src_type;
	}

	/* La recherche qu'on veut est elle déjà en cours ? */
	$todo = isset($todo_src[$type]);
	$done = (isset($todo_src[$type]) || isset($have_src[$type]));

	/* Ressources */
	$prix_res = get_conf("src", $type, "prix_res");
	$cond_res = array_keys($prix_res);

	if(!isset($cache['res'])) {
		$have_res = Res::get($mid, $cond_res);
	} else
		$have_res = $cache['res'];

	foreach($prix_res as $res_type => $nombre) {
		$diff =  $nombre - $have_res[$res_type];
		if($diff > 0)
			$bad_res[$res_type] =  $diff;
	}

	/* Verifications Bâtiments */
	foreach($need_btc as $btc_type) {
		if(!isset($have_btc[$btc_type]))
			$bad_btc[] = $btc_type;
	}

	return array('need_btc' => $bad_btc, 'need_src' => $bad_src['need_src'], 'need_no_src' => $bad_src['need_no_src'], 'todo' => $todo, 'done' => $done,  'prix_res' => $bad_res);
}
