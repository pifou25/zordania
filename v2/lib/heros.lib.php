<?php

/**
 * former un héros.
 * payer le prix configuré (ressources et unités). 
 * ajouté le héros au village 
 * il faut avoir vérifié la dispo des ressources & unités
 * @param type $mid
 * @param type $name
 * @param type $type
 * @return boolean
 */
function add_hero(int $mid, string $name, int $type) {
	
	// si l'id qu'on a récupéré c'est pas celui d'un héros ... Ben on lui dit que c'est pas bien de faire ça ! 
	if(Session::$SES->getConf('unt', $type, 'role') != TYPE_UNT_HEROS) 
		return false;
	// récpérer l'id de la légion au village
	$lid = Leg::get($mid, LEG_ETAT_VLG)[0]['leg_id'];
	$vie = Session::$SES->getConf('unt',$type, 'vie');

	// prix en unités de la légion
	$edit_unt = Session::$SES->getConf('unt', $type, 'prix_unt');
	// prix en ressources
	$prix_res = Session::$SES->getConf('unt', $type, 'prix_res');
	// ajouter le héros, l'unité et payer le prix
        Hro::add($mid, $name, $type, $lid, $vie);
	$edit_unt[$type] = -1; // ajouter le héros comme unité
	Unt::editVlg($mid, $edit_unt, -1); // prix unités & héros
	Res::mod($mid, $prix_res, -1);// ressources payées
	// maj mbr
	$_sql->query('UPDATE '.$_sql->prebdd.'mbr SET mbr_lmodif_date = NOW()');
	return true;
}

/**
 * supprimer le héros et l'unité. pas de remboursement.
 * @param int $lid
 * @param int $type
 * @return boolean
 */
function del_hero(int $lid, int $type) {
	global $_sql;

    if(!$lid || !$type) return false;

	// supprimer l'unité dans la légion
        $rang = Session::$SES->getConf('unt', $type, 'rang');
        Unt::edit($lid, [$rang => [$type => -1]]);
	// supprimer le héros
        Hro::del($lid);
	// maj mbr
	$_sql->query('UPDATE '.$_sql->prebdd.'mbr SET mbr_lmodif_date = NOW()');
	return true;
}

/**
 * Pour 'supprimer' un bonus, donc le désactiver, mettre $bonus_id à 0.
 * Pour activer un bonus, suffit de mettre l'id du bonus.
 * @global type $_sql
 * @param int $mid
 * @param int $bonus_id
 * @return boolean
 */
function edit_bonus(int $mid, int $bonus_id){ // 
	global $_sql;
	
	$prix_xp = Session::$SES->getConf("comp", $bonus_id, "prix_xp");
	$array_hero = Hro::get($mid);
	
        // savoir si y'a assez d'xp pour payer le cout du bonus ou non.
	if($prix_xp <= $array_hero[0]['hro_xp'] or $bonus_id == 0){ 
            
            $tours = Session::$SES->getConf("comp", $bonus_id, "tours");//délais du bonus.
            /* ZORD_SPEED = durée d'un tour en minutes */
            $tours *= ZORD_SPEED;
            Xro::edit($mid, $bonus_id, $tours, $array_hero[0]['hro_xp'] - $prix_xp);
            // maj mbr
            $_sql->query('UPDATE '.$_sql->prebdd.'mbr SET mbr_lmodif_date = NOW()');
            return true;
	}
	else
            return false;
}

function get_comp(int $cp_id, int $race, $res = false) {
// récupérer toutes les infos d'un bonus, format array
// tableau générique array( heros=> array, bonus=> %, tours=>tours, prix_xp=>prix, race=>$race, res=>$resultat)
	$cp = Config::get($race, 'comp', $cp_id);
	$cp['cpid'] = $cp_id;
	$cp['race'] = $race;
	if ($res !== false)
		$cp['res'] = $res;
	return $cp;
}
