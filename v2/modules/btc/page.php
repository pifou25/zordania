<?php
//Verifications
if(!defined("_INDEX_")) exit;
if(!can_d(DROIT_PLAY))
	$_tpl->set("need_to_be_loged",true); 
else {
    
$mbr = new member($_user['mid']);

require_once("lib/src.lib.php");
require_once("lib/unt.lib.php");
require_once("lib/res.lib.php");
require_once("lib/member.lib.php");

// construction des batiments
if($_act == 'btc')
{
	$_tpl->set("module_tpl","modules/btc/btc.tpl");

	/* Nombre de travailleurs */
	$_tpl->set("btc_trav", $mbr->nb_unt_done(1));
	$_tpl->set("btc_act",false);

	// tous les batiments constuits - sauf les BTC_ETAT_TODO
	//$cache['btc_done'] = $mbr->nb_btc(); // Btc::getNbDone($_user['mid']);
	//$cache['btc_todo'] = $mbr->nb_btc( array(), array(BTC_ETAT_TODO));

	if($_sub == 'btc') {// construire un nouveau bâtiment $type
		$_tpl->set("btc_act","btc");
		$type = request("type", "uint", "get");
		
		if(!$type)
			$_tpl->set("btc_no_type",true);
		else if(count($mbr->btc(array(), array(BTC_ETAT_TODO))) >= TODO_MAX_BTC)
			$_tpl->set("another_btc", true);
		else {
			$array = $mbr->can_btc($type); // can_btc($_user['mid'], $type, $cache);
			
			if(isset($array['do_not_exist']))
				$_tpl->set("btc_no_type",true);
			else {
				$ok = empty($array['need_src']) && empty($array['need_btc']) && $array['limit_btc']==0 && empty($array['prix_res']) && empty($array['prix_trn']) && empty($array['prix_unt']);
			
				$_tpl->set("btc_infos", $array);
				$_tpl->set("const_btc_ok", $ok);
				if($ok) {
					Unt::editVlg($_user['mid'], get_conf("btc", $type, "prix_unt"), -1);
					unt::editBtc($_user['mid'], get_conf("btc", $type, "prix_unt"));
					
					Trn::mod($_user['mid'], get_conf("btc", $type, "prix_trn"), -1);
					Res::mod($_user['mid'], get_conf("btc", $type, "prix_res"), -1);
					
					Btc::add($_user['mid'], $type);
					// MAJ du $cache - on refait le select DB
					//$cache['btc_todo'] = Btc::getNb($_user['mid'], array(), array(BTC_ETAT_TODO));
					$mbr = new member($_user['mid']);
				}
			}
		}
	} elseif($_sub == 'cancel') {// annuler construction en cours
		$_tpl->set("btc_act","cancel");
		$bid = request("bid", "uint", "get");
		if(!$bid)
			$_tpl->set("btc_no_bid", true);
		else {
			$infos = Btc::get($_user['mid'], [$bid]);
			$_tpl->set("can_btc_ok", $infos);

			if($infos) {
				$type = $infos[$bid]['btc_type'];
				Btc::del($_user['mid'], $bid);
				
				Unt::editVlg($_user['mid'], get_conf("btc", $type, "prix_unt"), 1);
				unt::editBtc($_user['mid'], get_conf("btc", $type, "prix_unt"), -1);
					
				Trn::mod($_user['mid'], get_conf("btc", $type, "prix_trn"), 1);
				Res::mod($_user['mid'], get_conf("btc", $type, "prix_res"), 0.5);
				// MAJ du $cache
				//$cache['btc_todo'] = Btc::getNb($_user['mid'], array(), array(BTC_ETAT_TODO));
				$mbr = new member($_user['mid']);
			}
		}
	}

	/* Y'en a en construction ? */
	$btc_todo = $mbr->btc(array(), array(BTC_ETAT_TODO));
	if($btc_todo) {
		$_tpl->set("btc_conf",get_conf("btc"));
		$_tpl->set("btc_todo",$btc_todo);
	}
	$nb_todo = count($btc_todo);
	$btc_todo = $mbr->nb_btc(array(), array(BTC_ETAT_TODO));

	$btc_tmp = array();
	for($i = 1; $i <= get_conf("race_cfg", "btc_nb"); ++$i) {
		$btc_tmp[$i]['bad'] = $mbr->can_btc($i); // can_btc($_user['mid'], $i, $cache);
		$btc_tmp[$i]['conf'] = get_conf("btc", $i);
		/*
		if(isset($cache['btc_done'][$i]['btc_nb']))
			$btc_tmp[$i]['btc_nb'] = $cache['btc_done'][$i]['btc_nb'];
		else
			$btc_tmp[$i]['btc_nb'] = 0;
		*/
		$btc_tmp[$i]['btc_nb'] = $mbr->nb_btc($i);
		/*
		if(isset($cache['btc_todo'][$i]['btc_nb']))
			$btc_tmp[$i]['btc_todo'] = $cache['btc_todo'][$i]['btc_nb'];
		*/
		if(isset($btc_todo[$i]))
			$btc_tmp[$i]['btc_todo'] = $btc_todo[$i]['btc_nb'];
	}

	$btc_ok = array(); // constructible, montrable
	$btc_bad = array(); // pas constructible, mais montrable 
	$btc_limit = array(); // limite atteinte, mais montrable
	foreach($btc_tmp as $bid => $array) {
		// pas montrable on ignore
		if($array['bad']['need_src'] || $array['bad']['need_btc']) continue;
		// limite ou manque de terrains
		if($array['bad']['limit_btc'] || $array['bad']['prix_trn'])
			$btc_limit[$bid] = $array;
		// manque ressources ou unités
		else if($array['bad']['prix_res'] || $array['bad']['prix_unt'])
			$btc_bad[$bid] = $array;
		else {
			if($nb_todo <= TODO_MAX_BTC)
				unset($array['bad']);
			$btc_ok[$bid] = $array;
		}
	}

	unset($btc_tmp);

	$_tpl->set("btc_const", true);
	$_tpl->set_ref("btc_ok",$btc_ok);
	$_tpl->set_ref("btc_bad",$btc_bad);
	$_tpl->set_ref("btc_limit",$btc_limit);

} elseif($_act == 'use')  {
    // recherche ou formation ou ressources
	$_tpl->set("module_tpl","modules/btc/use.tpl");
	$btc_type = request("btc_type", "uint", "get");
	
	if($btc_type && !get_conf("btc", $btc_type))
		$btc_type = 0;
		
	//On liste les batiments d'un type - ou tous
	if($_sub == 'list')
	{
		$_tpl->set("btc_act","list2");
		
		if(!$btc_type || !get_conf("btc", $btc_type)){
			$btc = array();
			$btc_conf = get_conf("btc");
		}
		else{
			$btc = array($btc_type);
			$_tpl->set("btc_id",$btc_type);
			$btc_conf = get_conf("btc", $btc_type);
		}
			
		$btc_array = Btc::get($_user['mid'], $btc, [BTC_ETAT_OK, BTC_ETAT_REP, BTC_ETAT_BRU,BTC_ETAT_DES]);
		
		// regrouper les bat par etat
		$btc_ar1 = array();
		foreach($btc_array as $value){
			$btc_ar1[$value['btc_etat']][] = $value;
		}
		
		$_tpl->set("btc_ar1", $btc_ar1);
		$_tpl->set("btc_conf",$btc_conf);
	}
	elseif($_sub == 'det') /* Supprime un bâtiment */
	{
		if(!empty($_POST))
			$arr_bid = request('bid', 'array', 'post');
		else{
			$btc_bid = request("btc_bid", "uint", "get");
			$arr_bid[$btc_bid] = 'on';
		}
		$ok = request("ok", "bool", "post");
		
		$_tpl->set('btc_act','det');
		$_tpl->set('btc_bid',$arr_bid);
		
		if(!$arr_bid)
			$_tpl->set('btc_no_bid',true);
		else if($ok) {
			$_tpl->set('btc_ok', true);
			foreach($arr_bid as $btc_bid => $value){
				$infos = Btc::get($_user['mid'], [$btc_bid]);
				$_tpl->set("btc_det_ok", $infos);
				if($infos) {
					$type = $infos[$btc_bid]['btc_type'];
					Btc::del($_user['mid'], $btc_bid);
					Unt::editVlg($_user['mid'], get_conf("btc", $type, "prix_unt"), 1);
					unt::editBtc($_user['mid'], get_conf("btc", $type, "prix_unt"), -1);

					Trn::mod($_user['mid'], get_conf("btc", $type, "prix_trn"), 1);
					Res::mod($_user['mid'], get_conf("btc", $type, "prix_res"), 0.5);

					$place = get_conf("btc", $type, "prod_pop");
					if($place) {
						Mbr::edit($_user['mid'], array("place" => $_user['place'] - $place));
						$_user['place'] -= $place;
					}
				}
			}
		} else
			$_tpl->set('btc_ok',false);
		
	} elseif($_sub == 'des' OR $_sub == 'act' OR $_sub == 'rep') {
        // désactiver / activer / réparer un bat
		if(!empty($_POST))
			$arr_bid = request('bid', 'array', 'post');
		else{
			$btc_bid = request("btc_bid", "uint", "get");
			$arr_bid[$btc_bid] = 'on';
		}
		
		$_tpl->set('btc_bid',$arr_bid);
		$_tpl->set('btc_act','mod_etat');
		
		if(!$arr_bid)
			$_tpl->set('btc_no_bid',true);
		else {
			foreach($arr_bid as $btc_bid => $value){
				$infos = Btc::get($_user['mid'], [$btc_bid]);
				if(!$infos)
					$_tpl->set('btc_no_bid',true);
				else {
					$etat = $infos[$btc_bid]['btc_etat'];
					$bonus = get_conf("btc", $infos[$btc_bid]['btc_type'], "bonus");
					switch($_sub) {
					case 'des':
						if ($bonus)
							$res = -1;
						else if($etat == BTC_ETAT_OK)
							$res = Btc::edit($_user['mid'], array($btc_bid => array('etat' => BTC_ETAT_DES)));
						else
							$res = 0;
						break;
					case 'act':
						if($etat == BTC_ETAT_DES || $etat == BTC_ETAT_REP)
							$res = Btc::edit($_user['mid'], array($btc_bid => array('etat' => BTC_ETAT_OK)));
						else
							$res = 0;
						break;
					case 'rep':
						$res = Btc::edit($_user['mid'], array($btc_bid => array('etat' => BTC_ETAT_REP)));
						break;
					}
					$_tpl->set('btc_mod_etat',$res);
				}
			}
		}
        
	//Gérer : formation / recherche / ressources dispo dans le bâtiment
	} else {
		$_tpl->set("btc_act", "use");
		// lister les bâtiments de ce type disponibles
		$btc_array = $mbr->btc(array($btc_type), array(BTC_ETAT_OK, BTC_ETAT_DES, BTC_ETAT_REP, BTC_ETAT_BRU));
		$btc_nb_total = count($btc_array);
		$btc_nb = 0;
		foreach($btc_array as $value) {
			if($value['btc_etat'] == BTC_ETAT_OK)
				$btc_nb++;
		}
		
		if(!$btc_nb) { // aucun bât de ce type
			$_tpl->set("btc_act","no_btc");
			$_tpl->set("btc_id", $btc_type);
		} else {
			define("INDEX_BTC",true);
		
			$btc_conf = get_conf("btc", $btc_type);
			
			$_tpl->set('man_array',array($btc_type => $btc_conf));
			$_tpl->set('man_race',$_user['race']);

			$_tpl->set("btc_id",$btc_type);
			$_tpl->set("btc_conf", $btc_conf);
			$_tpl->set("btc_nb",$btc_nb);
			$_tpl->set("btc_nb_total",$btc_nb_total);
            // un template spécifique par race & batiment - vide sauf donjon (btc_type=1)
			$_tpl->set("btc_tpl","modules/btc/".$_user['race']."/".$btc_type.".tpl");
			
			/* Principal */
            // un include spécifique par race & batiment - vide sauf donjon (btc_type=1)
			include(SITE_DIR."modules/btc/" .$_user['race']."/".$btc_type.".php");
			
			/* Autres trucs, en fonction de la conf */
            // former unité, liste des unités, annuler une formation
			if(isset($btc_conf['prod_unt'])) include("modules/btc/inc/unt.php");
            // liste recherches, nouvelle recherche, annuler recherche en cours
			if(isset($btc_conf['prod_src'])) include("modules/btc/inc/src.php");
            // idem ressources
			if(isset($btc_conf['prod_res'])) include("modules/btc/inc/res.php");
            // tout le commerce (marché achat vente cours ...)
			if(isset($btc_conf['com'])) include("modules/btc/inc/com.php");
            // la page "info" du bat
			include("modules/btc/inc/info.php");

		}
	}

}

}
?>
