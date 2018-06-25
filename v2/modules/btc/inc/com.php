<?php
if(!defined("INDEX_BTC") ){ exit; }

require_once("lib/res.lib.php");
require_once("lib/mch.lib.php");
require_once("lib/src.lib.php");

$max_nb = 0;
$max_ventes = 0;
$com_conf = get_conf("btc", $btc_type, "com");

$need_src = array_keys($com_conf);
$have_src = Src::get($_user['mid'], $need_src);
$have_src = index_array($have_src, "src_type");

/* nb max de ventes, et prix maxi, selon recherche faite */
foreach($com_conf as $src_id => $max_array) {
	if(isset($have_src[$src_id])) {
		if($max_nb < $max_array[0]) $max_nb = $max_array[0];
		if($max_ventes < $max_array[1]) $max_ventes = $max_array[1];
	}
}

$_tpl->set('com_max_nb',$max_nb);

$_tpl->set('MCH_MAX',MCH_MAX);
$_tpl->set('COM_TAX',COM_TAX);
$_tpl->set('COM_TAUX_MIN',COM_TAUX_MIN);
$_tpl->set('COM_TAUX_MAX',COM_TAUX_MAX);
$_tpl->set('COM_ETAT_OK',COM_ETAT_OK);


if($_sub == "my") { /* liste des ventes en cours */
	$_tpl->set('btc_act','my');

	$com_cid = request("com_cid", "uint","get");
	$ok = request("ok", "bool", "get");

	if(!$com_cid) {
		$vente_array = Mch::getByMid($_user['mid']);
		$_tpl->set('vente_array',$vente_array);
	} elseif($ok != 'ok')
		$_tpl->set('com_cid',$com_cid);
	else
		if(mch_cnl($_user['mid'], $com_cid))
			$_tpl->set('com_cancel',true);
		else
			$_tpl->set('com_cancel',false);
} elseif($_sub == "cnl") { /* annuler ventes */
	$_tpl->set('btc_act','cnl');

	$mch_cnl = request("com_cnl", "array", "post");

	$have_mch = Mch::getByMid($_user['mid']);
	$have_mch = index_array($have_mch, "mch_cid");

	$mod_res = array();

	foreach($have_mch as $cid => $value) {
		if(!isset($mch_cnl[$cid]))
			unset($have_mch[$cid]);
		else {
			if(!isset($mod_res[$value['mch_type']]))
				$mod_res[$value['mch_type']] = 0;
			$mod_res[$value['mch_type']] += $value['mch_nb'] * (1 - (COM_TAX / 100));
		}
	}
	Res::mod($_user['mid'], $mod_res);
	Mch::del($_user['mid'], array_keys($have_mch));
	$_tpl->set("com_cnl", $have_mch);
} elseif($_sub == "ach") { /* marché / achat */
	$_tpl->set('btc_act','ach');
	if ($_user['bonus'] == CP_GENIE_COMMERCIAL)
		$_tpl->set('cpt_nego',true);

	$com_cid = request("com_cid", "uint","get");

	if(!$com_cid) /* liste des achats dispo : matière première */
	{
		$com_liste = Mch::getList($_user['mid']);
		$_tpl->set('com_liste',$com_liste);

		$com_type = request("com_type", "uint","get");

		if($com_type) { /* liste des achats dispo : détail d'une ressource */
			$cours_tmp = MchCour::get($com_type);
			$cours = array();
			foreach($cours_tmp as $key => $cours_value)
				$cours[$cours_value['mcours_res']] = $cours_value['mcours_cours']; 

			$_tpl->set('com_cours',$cours);

			$_tpl->set('com_type', $com_type);

			$com_array = Mch::getRes($_user['mid'], $com_type);
			$_tpl->set('com_array',$com_array);

			$com_infos = Mch::makeSum($com_array);
			$_tpl->set('com_infos',$com_infos);
		}

	} else { /* on veut acheter la vente $com_cid */
		if($_display == 'ajax')
			$_tpl->set("module_tpl","modules/btc/inc/com.tpl");

		$com_neg = request("com_neg", "bool","get"); // négocié?

		$_tpl->set('btc_sub','achat');

		if($com_neg) // on tente la négo?
		{
			$rand_neg = rand(1,4);
			if($rand_neg == 1 || $rand_neg == 2) $com_mod = 1;
			if($rand_neg == 3) $com_mod = 1 - (COM_NEGO_TAUX / 100);
			if($rand_neg == 4) $com_mod = 1 + (COM_NEGO_TAUX / 100);
			$com_mod_max = 1 + (COM_NEGO_TAUX / 100);
			$_tpl->set('com_neg',$com_mod);
		}
		else
		{
			$com_mod_max = 1;
			$com_mod = 1;
		}
		if ($_user['bonus'] == CP_GENIE_COMMERCIAL) {
			$bonus = get_conf('comp', CP_GENIE_COMMERCIAL, 'bonus');
			$com_mod_max = $com_mod_max*(1-$bonus/100);
			$com_mod = $com_mod*(1-$bonus/100);
		}

		$mch_array = Mch::get($com_cid);
		if(!$mch_array || $mch_array[0]['mch_etat'] != COM_ETAT_OK) { // déjà vendu?
			$_tpl->set('btc_achat','error');
		} else {
			$mch_array = $mch_array[0];
			if($mch_array['mch_prix'] > $max_nb) // trop cher
				$_tpl->set('btc_max_nb',$max_nb);
			elseif ($mch_array['mch_mid'] == $_user['mid'])
				$_tpl->set('btc_achat','error');
			else {
				$have_res = Res::get($_user['mid'], array(GAME_RES_PRINC));

				if($have_res[GAME_RES_PRINC] < $mch_array['mch_prix']*$com_mod_max)
					$_tpl->set('btc_achat','nores'); // trop cher
				else {
					$_tpl->set('btc_achat','ok');
					Res::mod($_user['mid'], array(1 => ($mch_array['mch_prix'] * $com_mod * -1), $mch_array['mch_type'] => $mch_array['mch_nb']));
					Res::mod($mch_array['mch_mid'], array(1 => $mch_array['mch_prix']));

					Mch::achat($com_cid);

					$histo_array = array("mch_type" => $mch_array['mch_type'], 'mch_nb' => $mch_array['mch_nb'], 'mch_prix' => $mch_array['mch_prix']);
					$_histo->add($mch_array['mch_mid'], $_user['mid'], HISTO_COM_ACH, $histo_array); // évènement
				}
			}
		}
	}
}
elseif($_sub == "ven") /* faire une vente */
{
	$_tpl->set('btc_act','ven');
	$nb_ventes = count(Mch::getByMid($_user['mid']));

	$_tpl->set('max_ventes',$max_ventes);
	$_tpl->set('nb_ventes',$nb_ventes);
        
	if($max_ventes > $nb_ventes)
	{
		$com_type = request("com_type", "uint", "post");
		$com_nb = request("com_nb", "uint", "post");
		$com_prix = request("com_prix", "uint", "post");
		$com_vente = request("com_vente", "uint", "post"); // nb de ventes identiques
		
		if($com_type <= 1 || !get_conf("res", $com_type)) {
			//choix du type de la ressource
			$_tpl->set('btc_sub','choix_type');
			$list_res = Res::get($_user['mid']);
			$_tpl->set('com_list_res',$list_res);
			
			// tous les cours
			$cours = MchCour::get();
			$cours1 = array();
			foreach($cours as $row){
				$rid = $row['mcours_res'];
				$value = $row['mcours_cours'];
				$cours1[$rid] = array('min' => round($value*COM_TAUX_MIN,2), 'moy' => $value, 'max' => round($value*COM_TAUX_MAX,2));
			}
			$_tpl->set('com_cours',$cours1);
		} else {
			//Cours
			$cours = MchCour::get($com_type);
			$cours = $cours[0]['mcours_cours'];
			$_tpl->set('com_cours',$cours);
			
			$cours_min = round($cours*COM_TAUX_MIN,2);
			$cours_max = round($cours*COM_TAUX_MAX,2);
			if($cours_max < MCH_COURS_MIN) $cours_max = MCH_COURS_MIN;
			
			$_tpl->set('com_cours_min',$cours_min);
			$_tpl->set('com_cours_max',$cours_max);
			
			if($com_nb && $com_prix)
				$com_cours = round($com_prix / $com_nb,2);
			if(!$com_nb || !$com_prix || $com_cours > $cours_max || $com_cours < $cours_min) {
				//choix des param (prix & nb)
				$_tpl->set('btc_sub','choix_param');
				if ($com_nb && $com_prix)
					$_tpl->set('btc_error', ($com_cours > $cours_max || $com_cours < $cours_min));

				$prices = Mch::getPrice($com_type);
				$com_array = Mch::getRes(0, $com_type);
				$com_infos = Mch::makeSum($com_array);

				$_tpl->set('com_infos',$com_infos);
				$_tpl->set('com_other_price',$prices);
				
				$_tpl->set('com_type',$com_type);
				$_tpl->set('com_nb',$com_nb);
				$_tpl->set('com_prix',$com_prix);
			} else {
				//mise en vente apres verif
				$_tpl->set('btc_sub','vente');
				if($com_prix > $max_nb) {
					$_tpl->set('btc_max_nb',$max_nb);
					$_tpl->set('vente_ok',false);
				} else if($com_vente == 0) {
					$_tpl->set('btc_max_nb',0);
					$_tpl->set('vente_ok',false);
				} else {
					/* Est ce qu'il a bien les ressources ? */
					$list_res = Res::get($_user['mid'], [$com_type]);
					$res_nb = $list_res[$com_type];
					if($res_nb < $com_nb)
					 	$_tpl->set("vente_ok", false);
					else {
						$ok = array();
						for($i = 1; ; $i++) { // $i <= $com_vente and $max_ventes < $nb_ventes+$i and $res_nb > $com_nb*$i
							if($i > $com_vente) break; // nb de ventes choisi
							if($max_ventes < $nb_ventes+$i){
								$ok['nb'] = $i-1;
								break; // nb max des ventes selon recherche
							}
							if($res_nb < $com_nb*$i){
								$ok['res'] = $i-1;
								break; // plus assez de ressources
							}
							/* Enlever les ressources */
							Res::mod($_user['mid'], array($com_type => $com_nb), -1);	
							/* Mettre en vente */
							Mch::add($_user['mid'], $com_type, $com_nb, $com_prix);
						}
						$_tpl->set("vente_ok", $ok);
					}
				}
			}
		}
	} else {
		$_tpl->set('btc_sub','error');
	}
}
elseif($_sub == "cours")
{
	$com_nb = request("com_nb", "uint", "post", 1);

	$_tpl->set('btc_act','cours');
	$_tpl->set('mch_cours',MchCour::get());
	
	$_tpl->set('com_nb',$com_nb);
}
elseif($_sub == "cours_sem")
{
	$com_type = request("com_type", "uint", "get");

	$annee = request("annee", "int", "get",date("Y"));
	$mois = request("mois", "int", "get",date("m"));
	$jour = request("jour", "int", "get",date("d"));

	if($mois > 12){ $mois = 1; $annee++;}
	if($mois < 1){ $mois = 12; $annee--;}
	$nb_jours = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
	if($jour > $nb_jours){ $jour = 1;$mois ++; }
	if($jour < 1){ $mois--;  }
	if($mois > 12){ $mois = 1; $annee++;}
	if($mois < 1){ $mois = 12; $annee--;}
	if($jour < 1){ $jour = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);}

	if(strlen ($jour) < 2){ $jour = "0".$jour; }
	if(strlen ($mois) < 2){ $mois = "0".$mois; }

	$_tpl->set('stq_annee',$annee);
	$_tpl->set('stq_mois',$mois);
	$_tpl->set('stq_jour',$jour);

	$now = new DateTime();
	$diff = $now->diff(new DateTime("$annee-$mois-$jour"))->days;
	$_tpl->set('btc_act','cours_sem');
	
	if(!$com_type)
	{
		$tmp = MchSem::get(0,$diff);
		$mch_cours = array();
		foreach($tmp as $result)
			$mch_cours[$result['msem_res']][] = $result;
			
		$_tpl->set('mch_cours',$mch_cours);
	} else {	
		$tmp = MchSem::get($com_type,$diff);
		$mch_cours = array();
		foreach($tmp as $result)
			$mch_cours[$com_type][] = $result;
			
		$_tpl->set('mch_cours',$mch_cours);
	}
}

?>
