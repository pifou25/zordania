<?php
//Verifications
if(!defined("_INDEX_")){ exit; }
if(!$_ses->canDo(DROIT_PLAY))
	$_tpl->set("need_to_be_loged",true); 
else
{

    // Leg: model eloquent
    $legs = Leg::where('leg_mid', $_user['mid'])
            ->where('leg_etat', '<>', LEG_ETAT_BTC)
            ->get()->keyBy('leg_id');
    $_tpl->set('legs', $legs);
    
    
$_tpl->set("module_tpl", "modules/leg/leg.tpl");
$_tpl->set("leg_race", $_user['race']);
$_sub = request('sub', 'string','get');

function list_comp($hro_type) { // liste des comp du héros
	$comp_array = session::$SES->getConf('comp');
	$result = array();
	foreach($comp_array as $id => $cp)
		if(in_array($hro_type, $cp['heros']))
			$result[$id] = $cp;
	return $result;
}


switch($_act) {
   
    // selection d'unites a deplacer d'une legion a une autre
case 'unit1':
    
    $lid = request('lid', 'uint', 'get', 0);
    $_tpl->set('lid', $lid);
    // $unit = unt_type 
    $type = request('type', 'uint', 'get', 0);
    if(isset($legs[$lid])){
        $leg = $legs[$lid];
        $rang = $leg->rang($type);
        //check si les unites sont militaires
        $civils = Config::get($_user['race'], 'unt', $type, 'role') == TYPE_UNT_CIVIL;
        if(empty($rang) || $civils){
            $_tpl->set('error', 'leg_empty');
        }else{
            $_tpl->set('unit', $rang);
        }
    }else{
        $_tpl->set('error', 'leg_no_leg');
    }
    
    break;

    // deplacer des unites d'une legion a une autre
case 'unit2':
    
    $fromLid = request('from_leg', 'uint', 'post', 0);
    $toLid = request('to_leg', 'uint', 'post', 0);
    $nb = request('unt_nb', 'uint', 'post', 0);
    $type = request("unt_type", "uint", "post");
    
    if(!isset($legs[$fromLid]) || !isset($legs[$toLid])){
        // une legion n'existe pas
        $_tpl->set('error', 'leg_bad_lid');
    }else if($legs[$fromLid]->leg_cid != $legs[$toLid]->leg_cid){
        // les 2 legions ne sont pas sur la meme position
        $_tpl->set('error', 'leg_bad_pos');
    }else{
        //check si les unites sont militaires
        $civils = Config::get($_user['race'], 'unt', $type, 'role') == TYPE_UNT_CIVIL;
        $toRang = $legs[$toLid]->rang($type);
        $fromRang = $legs[$fromLid]->rang($type);
        if(empty($fromRang) || $fromRang->unt_nb < $nb || $civils){
            // pas assez d'unites ou ils sont civils
            $_tpl->set('error', 'leg_no_unt');
        }else{
            if(empty($toRang)){
                $toRang = new Unt();
                $toRang->unt_lid = $toLid;
                $toRang->unt_type = $type;
                $toRang->unt_rang = Config::get( $_user['race'], 'unt', $type, 'rang');
                $toRang->unt_nb = $nb;
            }else{
                $toRang->unt_nb += $nb;
            }
            $fromRang->unt_nb -= $nb;
            if(!$toRang->save()){
                $_tpl->set('error', 'leg_ko1');
            }else if(!$fromRang->save()){
                $_tpl->set('error', 'leg_ko2');
            }else{
                // stop legion si elles sont en mouvement
                if(!in_array($legs[$toLid]->leg_etat, [LEG_ETAT_GRN, LEG_ETAT_VLG])){
                    $legs[$toLid]->leg_etat = LEG_ETAT_GRN;
                    $legs[$toLid]->leg_vit = 0;
                    $legs[$toLid]->save();
                }
                if(!in_array($legs[$fromLid]->leg_etat, [LEG_ETAT_GRN, LEG_ETAT_VLG])){
                    $legs[$fromLid]->leg_etat = LEG_ETAT_GRN;
                    $legs[$fromLid]->leg_vit = 0;
                    $legs[$fromLid]->save();
                }
                $_tpl->set('confirm', [$type => $nb]);
            }
        }
    }
    
    break;

case "move":

    // deplacer une legion sur la carte
	$cid = request("cid", "uint", "get");
	$lid_get = request("lid", "uint", "get");
	$arr_lid = request("move", "array", "post",array($lid_get => 1));
	$tele_lid = request("tele", "array", "post"); // téléportation

	$map_array = Map::getGen($cid, ['x'=>$_user['map_x'], 'y'=>$_user['map_y']]); // destination
	$_tpl->set("map_array", $map_array);
	$_tpl->set("map_cid", $cid);

	/* correction du bug 'légion bloquée en attaque à domicile' */
	if($cid == $_user['mapcid'])
		$_sub = 'sou';
	else{

		// verif qu'il y a bien un membre en état sur la case indiquée.
		if (!$map_array['mbr_mid'] || $map_array['mbr_etat'] == MBR_ETAT_ZZZ) {
			$_tpl->set("leg_move_pmbr",true);
			break;
		}

		$mbr_cible = Mbr::getFull($map_array['mbr_mid']);

		$_sub = 'atq'; // par défaut c'est toujours une attaque
		if($_user['alaid'] != 0 and $mbr_cible[0]['ambr_aid'] != 0){ // tt les 2 dans une alliance
			$pactes = new diplo(array('aid' => $_user['alaid'])); // mes pactes
			$pactes_array = $pactes->actuels();
		}
		else
			$pactes_array = array();

		$mbr_cible = Mbr::canAtq($mbr_cible,$_user['pts_arm'], $_user['mid'], $_user['groupe'], $_user['alaid'], $pactes_array); 
		$mbr_cible = $mbr_cible[0];
		if ($mbr_cible['pna'])
			$_sub = 'pna';
		elseif ($mbr_cible['can_def'])
			$_sub = 'def';
		else
			$_sub = 'atq';
	} /* fin correction de bug */

	$_tpl->set("leg_sub",$_sub);
	$_tpl->set("show_form",true);

	$cond = array('mid'=>$_user['mid']);
	$cond['etat'] = array(LEG_ETAT_GRN,LEG_ETAT_ALL,LEG_ETAT_POS,LEG_ETAT_DPL);
	$legions = new legions($cond, true, true);

	foreach($legions->legs as $lid => $leg)
		if ($leg->vide()) // virer les légions vides
			unset($legions->legs[$lid]);
			
	if($map_array ) {
		if($arr_lid) // déplacement normal
			foreach($arr_lid as $lid => $value) {
				if($lid == 0 || !isset($legions->legs[$lid]))
					{if($lid) $_tpl->set("leg_bad_lid", true);}
				else if($legions->legs[$lid]->comp == CP_INVULNERABILITE)
					$_tpl->set("leg_invincible", true); // restriction compétences
				else {
					// si la légion est déjà sur la bonne case, on passe en état d'attente.
					if ($legions->legs[$lid]->cid == $cid)
						$new = array('dest' => 0,
								'etat' => $_sub ==  'atq' ? LEG_ETAT_POS : LEG_ETAT_GRN);
					else
						// calculer et enregistrer la vitesse de la légion
						$new = array('vit' => $legions->legs[$lid]->vitesse(), 'dest' => $cid, 
								'etat' => $_sub == 'atq' ? LEG_ETAT_DPL : LEG_ETAT_ALL);
					$legions->legs[$lid]->edit($new);
					$_tpl->set("show_form",false);
					$_tpl->set("leg_move_ok", true);
				}
			}
		if($tele_lid) // téléportation
			foreach($tele_lid as $lid => $value) {
				if(!isset($legions->legs[$lid])) {
					if($lid) $_tpl->set("leg_bad_lid", true);
				} elseif($legions->legs[$lid]->comp != CP_TELEPORTATION)
					$_tpl->set("leg_no_tele", true);
				else { // téléporter la légion à destination & désactiver la compétence
					$new = array('dest'=>0,  'etat' => ($_sub=='atq'?LEG_ETAT_POS:LEG_ETAT_GRN),
						'cid'=>$cid, 'bonus'=>0);
					$legions->legs[$lid]->edit($new);
					$_tpl->set("show_form",false);
					$_tpl->set("leg_tele_ok", true);
				}
			}
	}
	break;
    
case "new": // creer une nouvelle légion
	$name = request("name", "string", "post");
	$_tpl->set('ren_leg_name', $name);

	if(!Leg::canRename($_user['mid'],0,$name))
		$_tpl->set('err', 'ren_leg_name_exists');
	elseif(Leg::count($_user['mid'], [LEG_ETAT_GRN, LEG_ETAT_POS, LEG_ETAT_ALL, LEG_ETAT_DPL,
		LEG_ETAT_RET, LEG_ETAT_ATQ]) < LEG_MAX_NB && $name)
		$_tpl->set("leg_new", Leg::add($_user['mid'], $_user['mapcid'], LEG_ETAT_GRN, $name));

	$_act = "";
	break;
case "del": // supprimer légion
	$lid = request("lid", "uint", "get");
	$ok = request("ok", "bool", "post");

	$cond = array();
	$cond['leg'] = array($lid);
	$cond['etat'] = array(LEG_ETAT_GRN, LEG_ETAT_DPL, LEG_ETAT_POS, LEG_ETAT_ALL, LEG_ETAT_RET);
	$cond['mid'] = $_user['mid'];
	$leg_array = Leg::get($cond);

	$cond = array();
	$cond['leg'] = array($lid);
	$cond['mid'] = $_user['mid'];
	$cond['unt'] = true;
	$unt_array = Leg::get($cond);

	if(!$lid || !$leg_array || $unt_array)
		$_tpl->set("leg_bad_lid", true);
	else if($_user['hro_id'] && $_user['hro_lid'] == $lid) // le héros (mort) doit être supprimé
		$_tpl->set("leg_not_empty", $lid);
	else if(!$ok) {
		$_tpl->set("leg_lid", $lid);
		$_tpl->set("leg_need_ok", true);
	} else
		$_tpl->set("leg_del", Leg::del($_user['mid'], $lid));

	$_act = "";
	break;
case "recup": // recuperer une legion vide
	$lid = request("lid", "uint", "get");

	$cond = array();
	$cond['leg'] = array($lid);
	$cond['etat'] = array(LEG_ETAT_POS);
	$cond['mid'] = $_user['mid'];
	$leg_array = Leg::get($cond);

	$cond = array();
	$cond['leg'] = array($lid);
	$cond['mid'] = $_user['mid'];
	$cond['unt'] = true;
	$unt_array = Leg::get($cond);

	if(!$lid || !$leg_array)
		$_tpl->set('err', "leg_bad_lid");
	elseif ($unt_array)
		$_tpl->set('err', "leg_no_empty");
	else { // récupérer la légion : on perd 30% XP et 50% des ressources
		$leg_array = $leg_array[0];
		$res_leg = LegRes::get($_user['mid'],  $lid);
		$mod_res = array();
		foreach($res_leg as $key => $value)
			$mod_res[$value['lres_type']] = -1 * $value['lres_nb'] * 0.5;

		LegRes::edit($lid, $mod_res);
		$edit_leg = array();
		$edit_leg['cid'] = $_user['mapcid'];
		$edit_leg['etat'] = LEG_ETAT_GRN;
		Leg::edit($_user['mid'], $lid, $edit_leg);
	}
	break;

case "hero":
    // gestion du heros
    
	$cond = array('mid'=>$_user['mid']);
	$legions = new legions($cond, true, true);
	// utiliser les valeurs connues dans $_ses
	if($_user['hro_id']){
		if($_sub == "form")
			$_tpl->set("already_hro", true);
		$hero_conf = $_ses->getConf('unt',$_user['hro_type']);
		$_tpl->set("hero_conf", $hero_conf);
		if($_user['hro_bonus'] != 0) // une compétence est active
			$_tpl->set("bonus_already", true);

		 // On récupère tout les bonus du héros pour la race :
		$comp_array = list_comp($_user['hro_type']);
		$_tpl->set("comp_array", $comp_array);
		// si la légion du héros est au village on peut le déplacer
		if ($legions->legs[$_user['hro_lid']]->cid == $_user['mapcid'])
			$_tpl->set("leg_array", $legions->get_all_legs_infos()); // liste des légions

		if($_sub == "bns" && $_user['hro_vie'] > 0){ // activer une comp ?
			$bid = request('bid', 'uint','post');
			if(!$bid && $_user['hro_bonus'] == 0)
				$_tpl->set("no_bid",true);
			else
			{
				if(isset($_GET['bid']) and $_GET['bid'] == 0 and $_user['hro_bonus'] != 0) // annuler une comp
					$cp = 0;
				else
					$cp = $legions->legs[$_user['hro_lid']]->getComp();
				if ($cp == 0 or in_array($_user['hro_type'], $cp['heros']))
				{
					$res = Hro::bonus($_user['mid'], $bid);
					if($res) {
						// ajouter un évenement
						$_histo->add($_user['mid'], $_user['mid'], HISTO_HRO_CP, $cp);
						$_tpl->set("ok_bonus",$bid);
					} else
						$_tpl->set("error_form",true);
				} else
					$_tpl->set("bad_bid",true);
			}
		}
		else if($_sub == "del_hero"){ // supprimer le héros ?
			$rep = request("Oui", "string", "post");
			if(isset($rep) && $rep == "Oui"){
				$nb = Hro::del($_user['hro_lid'], $_user['hro_type']);
				if ($nb) { // recompter la population & maj mbr
					$pop = Leg::countUnt($_user['mid']);
					Mbr::edit($_user['mid'], array('population' => $pop, 'lmodif_date' => true));
				}
				$_tpl->set("ok_del_hro", $_user['hro_nom']);
				$_ses->update_heros();
			}
			else
				$_tpl->set("verif_del_hro", true);
		}
		else if($_sub == "move_hero" && $_user['hro_vie'] > 0){// changer le héros de légion
			// interdire le changement de légion selon comp active
			if ($_user['hro_bonus'] == CP_INVULNERABILITE) {
				$_tpl->set("err",'imm_cause_comp');
			} else {
				$to = request("to", "string", "get", request("to", "string", "post"));
				if(!$to)
					$_tpl->set("no_info_move_hro", true);
				else{
					if($to == 'vlg')
						$to = $legions->vlg_lid;
					if (!isset($legions->legs[$to]))
						$_tpl->set("move_error",true);
					// les 2 légions sont sur la même case : déplacer le héros
					else if ($legions->legs[$_user['hro_lid']]->cid == $legions->legs[$to]->cid) {
						if(Hro::edit($_user['mid'], array('lid'=>$to))){ // ça marche !
							$_tpl->set("ok_hero_move", true);
							$_ses->update_heros();
						} else
							$_tpl->set("move_error",true);
					} else
						$_tpl->set("move_error",true);
				}
			}
		}
	} else if($_sub == "form"){ // formation héros étape 1: demander son nom
		// pour la formation, visu des bâtiments & recherches
		$id_hro = request("id_hro", "uint", "get");
		$name = request("hro_name", "string", "post");

		if($id_hro) { // vérifier qu'on peut le payer
			//$bad = $legions->legs[$legions->vlg_lid]->can_unt($id_hro, 1);
                        $member = new member($_user['mid']);
			$bad = $member->can_unt($id_hro, 1);
			foreach($bad as $key => $value) if (empty($value)) unset($bad[$key]);
			if (!empty($bad)) // trop cher
				$_tpl->set("no_res_hro", $bad);				
		}
		if(!$name)
			$_tpl->set("no_name", true);

		$_tpl->set("id_hro", $id_hro);
		if ($id_hro && $name && empty($bad)) {
			$res_add = Hro::add($_user['mid'], $name, $id_hro);
			if($res_add){
				$_tpl->set("ok_form_hro", true);
				$_tpl->set("comp_array",$_ses->getConf('comp'));// tout les bonus pour la race.
				$_ses->update_heros();
			} else
				$_tpl->set("error_form", true);
		}
		else
			$_tpl->set("no_hero", false);
	} else
		$_tpl->set("no_hero", false);
	break;
	
default: // mode 'view' = detail legion, ou page de toutes les légions
	$cond = array('mid'=>$_user['mid']);
	$legions = new legions($cond, true, true);
	if ($_act != 'view')
		$_act = "";

	$lid = request("lid", "uint", "get");

	if(isset($legions->legs[$lid])) {
		$res_array = $legions->legs[$lid]->get_res();
		$unt_array = $legions->legs[$lid]->get_unt();

		if($legions->legs[$lid]->etat == LEG_ETAT_VLG)// aucune action possible sur la légion village
			$_sub = "";
		if($legions->legs[$lid]->cid != $_user['mapcid'])
			$_sub = "";// aucune action possible hors du village

		switch($_sub) {
		case 'rang': //modifier les rangs de la légion
			// TODO
if($_display == "ajax") print_r($_POST);

			$_tpl->set("rang_ok", true);
			break;
		case "butin":// récupérer tout le butin
			$tmp = array();
			foreach($res_array as $res => $nb) {
				if($res != GAME_RES_BOUF) { // on ne récupère pas la bouffe
					$tmp[$res] = $nb * -1;
					unset($res_array[$res]);
				}
			}

			Res::mod($_user['mid'], $tmp, -1);
			$legions->legs[$lid]->mod_res($tmp);
			$_tpl->set("lres_ok", true);
			break;
		case "res":// modifier les ressources de la légion
			$factor = request("factor", "int", "post");
			$factor = ($factor < 0) ? -1 : 1;
			$res_type = request("res_type", "uint", "post");
			$res_nb = request("res_nb", "uint", "post");
			if($factor && $res_type && $res_nb && $_ses->getConf("res", $res_type)) {
				$have_res = Res::get($_user['mid']);// resources joueur

				$res_ok = false;
				if($factor > 0) {
					if($have_res[$res_type] >= $res_nb && $res_type = GAME_RES_BOUF) {
						$res_ok = true;
					}
				} else if(isset($res_array[$res_type]) && $res_array[$res_type] >= $res_nb) {
					$res_ok = true;
				}
				if($res_ok) {
					$res_nb *= $factor;
					Res::mod($_user['mid'], array($res_type => $res_nb), -1);// ressources joueur
					$legions->legs[$lid]->mod_res(array($res_type => $res_nb)); // ressources légion
					$have_res[$res_type] -= $res_nb;
					$_tpl->set("lres_ok", true);
				}
			}
			break;
		case "del":// rentrer un rang au village
			$type = request("type", "uint", "get");
			$legions->legs[$lid]->back_unt($type);
			break;
		default:
			break;
		}

		if($legions->legs[$lid]->etat != LEG_ETAT_VLG && $_act == 'view') { // position de la légion
			$pos_array = Map::getGen($legions->legs[$lid]->cid, 
                                ['x'=>$_user['map_x'], 'y'=>$_user['map_y']]);
			if($pos_array['mbr_mid'] == $_user['mid'])
				$pos_array['mbr_mid'] = 0;
			$_tpl->set('pos_array', $pos_array);
			if($legions->legs[$lid]->infos['leg_dest']) {// destination
				$dst_array = Map::getGen($legions->legs[$lid]->infos['leg_dest'], 
                                        ['x'=>$_user['map_x'], 'y'=>$_user['map_y']]);
				if($dst_array['mbr_mid'] == $_user['mid'])
					$dst_array['mbr_mid'] = 0;
				$_tpl->set('dst_array', $dst_array);
			}
		}

		if ($_act == 'view') {
			/* calcul bonus bat si legion au village */
			if ($legions->legs[$lid]->cid == $_user['mapcid']) {
				$btc_array = Btc::getDone($_user['mid']); // detail des batiments ($bid, vie, type...)
				$btc_list = Btc::milit($btc_array, $_user['race']);
				$legions->legs[$lid]->set_bonus_btc($btc_list['bonus']);
			}
			if (!$legions->legs[$lid]->vide())
				$_tpl->set("unt_leg", array($lid => false));
			$_tpl->set("unt_stats", $legions->legs[$lid]->stats());
			$_tpl->set("unt_bonus", $legions->legs[$lid]->bonus());
			$_tpl->set("unt_total", array('atq_unt' => $legions->legs[$lid]->atq_unt(),
									'atq_btc' => $legions->legs[$lid]->atq_btc(),
									'def' => $legions->legs[$lid]->def_unt(),
									'vie' => $legions->legs[$lid]->unt_vie() ) );
			$_tpl->set("leg", $legions->legs[$lid]->infos);
			$_tpl->set("unt_leg", array($lid => $legions->legs[$lid]->get_unt()));
			$_tpl->set("res_array", $legions->legs[$lid]->get_res());
			$_tpl->set("unt_conf", $_ses->getConf("unt"));

		}
	} else if ($lid)
		$_tpl->set("leg_bad_lid", true);

	break;

} // switch

if(!$_act or $_act == 'move' or $_act == 'recup') {
	if (!isset($legions)) {
		$cond = array('mid'=>$_user['mid']);
		$legions = new legions($cond, true, true);
	}
	$_tpl->set("res_leg", $legions->get_all_res());
	$_tpl->set("unt_leg", $legions->get_all_unts());
	$_tpl->set("leg_array", $legions->get_all_legs_infos());
	$_tpl->set("unt_conf", $_ses->getConf("unt"));
	$_tpl->set("lid_vlg", $legions->vlg_lid);
}
} // else
