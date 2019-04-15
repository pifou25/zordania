<?php

if(!defined("INDEX_BTC")){ exit; }


if($_sub == "cancel_unt")
{
	$uid = request("uid", "uint", "get");
	$nb = request("nb", "uint", "post");
	
	$_tpl->set("btc_act","cancel_unt");
	$_tpl->set("btc_uid", $uid);
	
	if(!$uid)
		$_tpl->set("btc_no_uid",true);
	elseif(!$nb)
		$_tpl->set("btc_no_nb",true);
	else {
		$infos = UntTodo::get($_user['mid'], array('uid' => $uid));
		
		if($infos && $infos[0]['utdo_nb'] >= $nb) {
			$_tpl->set("btc_ok",true);
			$_tpl->set("unt_canceled",$infos);

			$type = $infos[0]['utdo_type'];
			UntTodo::del($_user['mid'], $uid, $nb);
			/* on récupère les unités mais que 50% des ressources: */
			Unt::editVlg($_user['mid'], $_ses->getConf("unt", $type, "prix_unt"), 1 * $nb);
			Res::mod($_user['mid'], $_ses->getConf("unt", $type, "prix_res"), 0.5 * $nb);
			Mbr::edit($_user['mid'], array('population' => Leg::countUnt($_user['mid'])));
		} else
			$_tpl->set("btc_ok",false);
	}

} else if($_sub == "unt") {
	$_tpl->set("btc_act","unt");
	
	$unt_todo = $mbr->unt_todo();// toutes les unités 'todo'
	
	foreach($unt_todo as $id => $value) {// filtrer les unités du batiment concerné
		if(!in_array($btc_type,$mbr->getConf("unt",$value['utdo_type'],"in_btc")))
			unset($unt_todo[$id]);
	}

	$_tpl->set("unt_todo",$unt_todo);
	
	$unt_tmp = array();
    // conf de toutes les unités
	$conf_unt = $mbr->getConf('unt');
    // si l'unité n'est pas dispo dans ce bat on filtre
    $conf_unt = array_filter( $conf_unt, function($val) use ($btc_type){
        return in_array($btc_type, $val['in_btc']);
    });

	foreach($conf_unt as $type => $value) {
        // vérifie qu'on peut faire 1 unité du $type, indique ce qui manque
		$unt_tmp[$type]['bad'] = $mbr->can_unt($type, 1);
		$unt_tmp[$type]['conf'] = $value;
	}
	
	$unt_array = array();
    $unt_done = $mbr->nb_unt();
	$vlg = $mbr->unt_leg(Leg::ETAT_VLG);
	foreach($unt_tmp as $uid => $array) {
		// on ne garde que les unités dont on a tous les batiments et recherches
		if($array['bad']['need_src'] || $array['bad']['need_btc']) 
			continue;
		// regrouper par group (cf la conf)
		$group = isset($array['conf']['group']) ? $array['conf']['group'] : 0;
		$unt_array[$group][$uid] = $array;
		// nb unt dispo et total
		$unt_array[$group][$uid]['tot'] = $mbr->nb_unt($uid);
		$unt_array[$group][$uid]['vlg'] = isset($vlg[$uid]) ? $vlg[$uid] : 0;
	}
	
	unset($unt_tmp);
	
	$_tpl->set("unt_dispo", $unt_array);// unités possibles à former
	$_tpl->set("res_utils", $mbr->res());
	$_tpl->set("unt_utils", $mbr->nb_unt_done());
	
	$_tpl->set("unt_conf", $conf_unt);
	$_tpl->set("btc_pop_used", $_user['population']);
	$_tpl->set("btc_pop_max", $_user['place']);
	$_debugvars['unt_array'] = $unt_array;

}
//Nouvelle unt (sauf de type heros)
elseif($_sub == "add_unt")
{
	//définir la limite de population
	if ($_user['race'] == 7) define('TOTAL_MAX_UNT', TOTAL_MAX_UNT_2); //si gobelin
	else define('TOTAL_MAX_UNT', TOTAL_MAX_UNT_1); // les autres


$_tpl->set('TOTAL_MAX_UNT', TOTAL_MAX_UNT);
	$type = request("type", "uint", "post");
	$role = $_ses->getConf("unt", $type, "role");
	$nb = request("nb", "uint", "post");

	if($type) { /* calcul pop y compris formation en cours */
		$unt_todo = $mbr->unt_todo();
		$unt_todo_nb = 0;
		foreach($unt_todo as $value)
			$unt_todo_nb += $value['utdo_nb'];

		$prix_nb = 0;
		$prix_unt = (array) $_ses->getConf("unt", $type, "prix_unt");
		foreach($prix_unt as $key => $value)
			$prix_nb += $value * $nb;

		$unt_nb = $_user['population'] + $nb + $unt_todo_nb - $prix_nb;
	}

	$_tpl->set("btc_act","add_unt");
	if(!$type)
		$_tpl->set("btc_no_type",true);
	else if($role == TYPE_UNT_HEROS)
		$_tpl->set("type_no_heros",true);
	else if(!$nb)
		$_tpl->set("btc_no_nb",true);
	else if($unt_todo_nb + $nb > TODO_MAX_UNT)
		$_tpl->set("btc_unt_todo_max",TODO_MAX_UNT);
	else if($unt_nb > TOTAL_MAX_UNT || $unt_nb > $_user['place'])
		$_tpl->set("btc_unt_total_max",TOTAL_MAX_UNT);
	else {
			$array = $mbr->can_unt( $type, $nb);

			if(isset($array['do_not_exist']))
				$_tpl->set("btc_no_type",true);
			else {
				$ok = !($array['need_src'] || $array['need_btc'] || $array['limit_unt'] || $array['prix_res'] || $array['prix_unt']);
				$_tpl->set("unt_type", $type);
				$_tpl->set("unt_nb", $nb);
				$_tpl->set("unt_infos", $array);
				$_tpl->set("btc_ok", $ok);
				if($ok) {
					Unt::editVlg($_user['mid'], $_ses->getConf("unt", $type, "prix_unt"), -1 * $nb);
					Res::mod($_user['mid'], $_ses->getConf("unt", $type, "prix_res"), -1 * $nb);
					UntTodo::add($_user['mid'], array($type =>$nb));
					Mbr::edit($_user['mid'], array('population' => Leg::countUnt($_user['mid'])));
				}
			}
	}
}

