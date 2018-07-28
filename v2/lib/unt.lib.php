<?php

function get_leg_dst_vlg($x, $y, $near) {
	global $_sql;
	$x = protect($x, "uint");
	$y = protect($y, "uint");
	$near = protect($near, "uint");
	
	$sql = "SELECT leg_name, mbr_pseudo, mbr_gid, ";
	$sql.= "a.map_x AS dst_x, a.map_y AS dst_y, leg_dest, mbr_mid, ";
	$sql.= "b.map_x, b.map_y , leg_cid ";
	$sql.= "FROM ".$_sql->prebdd."map AS a ";
	$sql.= "JOIN ".$_sql->prebdd."leg  ON leg_dest = a.map_cid ";
	$sql.= "JOIN ".$_sql->prebdd."map AS b ON leg_cid = b.map_cid ";
	$sql.= "JOIN ".$_sql->prebdd."mbr ON mbr_mid = leg_mid ";
	$sql.= "WHERE a.map_x BETWEEN ".($x-$near)." AND ".($x+$near)." ";
	$sql.= "AND a.map_y BETWEEN ".($y-$near)." AND ".($y+$near)." ";
	$sql.= " AND mbr_etat = ".MBR_ETAT_OK." AND leg_etat = ".LEG_ETAT_DPL. " ";
	
	return $_sql->make_array($sql);
}

/* Gestion des unités */

/* Toutes les légions ennemies se dirigeant vers $dest (cid) ou déjà sur place */
function get_leg_dest($mid, $dest) {
	global $_sql;
	$mid = protect($mid, "uint");
	$dest = protect($dest, "uint");
	
	$sql = "SELECT leg_name, leg_id, mbr_pseudo, mbr_race, mbr_gid, ambr_aid, al_name, hro_bonus, leg_cid, ";
	$sql.= "a.map_x, a.map_y, leg_dest, mbr_mid ";
	$sql.= "FROM ".$_sql->prebdd."leg ";
	$sql.= "JOIN ".$_sql->prebdd."mbr ON mbr_mid = leg_mid ";
	$sql.= "LEFT JOIN ".$_sql->prebdd."al_mbr ON ambr_mid = leg_mid ";
	$sql.= "LEFT JOIN ".$_sql->prebdd."al ON al_aid = ambr_aid ";
	$sql.=" LEFT JOIN ".$_sql->prebdd."hero ON leg_id = hro_lid ";
	$sql.= "LEFT JOIN ".$_sql->prebdd."map AS a ON leg_cid = a.map_cid ";
	$sql.= "WHERE (leg_cid = $dest OR leg_dest = $dest) ";
	$sql.= "AND mbr_mid <> $mid ";
	$sql.= " AND mbr_etat = ".MBR_ETAT_OK;
	
	return $_sql->make_array($sql);
}

/* Toutes les légions alliés en déplacement (état par défaut) */
function get_leg_dpl($mid, $etat = [LEG_ETAT_RET, LEG_ETAT_ALL, LEG_ETAT_DPL]){
	global $_sql;
	$mid = protect($mid, "uint");
	$etat = protect($etat, ['uint']);
	
	$sql = " SELECT leg_name, leg_mid, leg_cid, leg_etat, leg_id, leg_vit, mbr_dest.mbr_pseudo AS pseudo_dest, mbr_dest.mbr_race AS race_dest, mbr_dest.mbr_mid AS mid_dest, leg_dest,";
	$sql.= " lres_type, lres_nb,";
	$sql.= " pst.map_x AS leg_x, pst.map_y AS leg_y,";
	$sql.= " dest.map_x AS dest_x, dest.map_y AS dest_y";
	$sql.= " FROM ".$_sql->prebdd."leg ";
	$sql.= " JOIN ".$_sql->prebdd."mbr AS mbr_dest ON mbr_dest.mbr_mapcid = leg_dest ";
	$sql.= " JOIN ".$_sql->prebdd."leg_res ON lres_lid = leg_id AND lres_type = ".GAME_RES_BOUF." ";
	$sql.= " JOIN ".$_sql->prebdd."map AS pst ON pst.map_cid = leg_cid ";
	$sql.= " LEFT JOIN ".$_sql->prebdd."map AS dest ON dest.map_cid = leg_dest";
	$sql.= " WHERE leg_mid = $mid";
	$sql.= " AND leg_etat IN (".implode(',',$etat).") ";
	
	return $_sql->make_array($sql);
}

function get_leg_pos($mid){
	global $_sql;
	$mid = protect($mid, "uint");
	
	$sql = "SELECT leg_name, leg_mid, leg_cid, leg_etat, leg_id, leg_vit, mbr_pseudo AS dest_pseudo, mbr_dest.mbr_race AS race_dest, mbr_dest.mbr_mid AS mid_dest, ";
	$sql.= " lres_type, lres_nb ";
	$sql.= " FROM ".$_sql->prebdd."leg ";
	$sql.= " JOIN ".$_sql->prebdd."mbr AS mbr_dest ON mbr_dest.mbr_mapcid = leg_cid ";
	$sql.= " JOIN ".$_sql->prebdd."leg_res ON lres_lid = leg_id AND lres_type = ".GAME_RES_BOUF." ";
	$sql.= " WHERE leg_mid = $mid";
	$sql.= " AND leg_etat = ".LEG_ETAT_POS." ";
	
	return $_sql->make_array($sql);
}

/* virer les tab et multi espaces */
function trimUltime($chaine){
	$chaine = trim($chaine);
	$chaine = str_replace("\t", " ", $chaine);
	$chaine = preg_replace("( +)", " ", $chaine);
return $chaine;
}


/* vérifications pour renommer une légion */
function can_ren_leg($mid, $lid, $leg_name){
	global $_sql;

	$mid = protect($mid, "uint");
	$lid = protect($lid, "uint");
	$leg_name = trimUltime(protect($leg_name, "string"));

	if(!$mid || !$leg_name)
		return false;

	$sql = "SELECT COUNT(*) AS leg_cnt FROM ".$_sql->prebdd."leg ";
	$sql .= " WHERE leg_mid = $mid AND leg_id <> $lid ";
	$sql .= " AND leg_name LIKE '$leg_name'";
	//$sql .= " AND leg_name LIKE CONVERT( _utf8 '$leg_name' USING latin1 ) COLLATE latin1_swedish_ci";

	$res = $_sql->query($sql);
    $val = $_sql->result($res, 0, 'leg_cnt');
	return ($val == 0);

}


/* les légions dans $leg_array peuvent être attaquées par un joueur ($mid) qui a $points, ($groupe) et ally=$alaid */
function leg_can_atq_lite($leg_array, $points, $mid, $groupe, $alaid, $dpl_array = [])
{
	$mid = protect($mid, "uint");
	$points = protect($points, "uint");
	$groupe = protect($groupe, "uint");
	$alaid = protect($alaid, "int");
	$leg_array = protect($leg_array, "array");
	$dpl_array = protect($dpl_array, 'array');

	$arr_cid = [];
	foreach($leg_array as $key => $value)
		if($value['mbr_mid'] == $mid)
			$arr_cid[$value['leg_cid']] = true;

	foreach($leg_array as $key => $value) {
		$pts = $value['mbr_pts_armee'];
		$alid = $value['ambr_aid'];
		$mid2 = $value['mbr_mid'];
		$etat = $value['mbr_etat'];
		$leg_etat = $value['leg_etat'];

		/* si c'est un allié qu'on peut défendre */
		$leg_array[$key]['can_def'] = false;
		if($alid && $alaid){
			if ($alid == $alaid) // même alliance
				$leg_array[$key]['can_def'] = true;
			elseif (isset($dpl_array[$alid]) and 
				($dpl_array[$alid] == DPL_TYPE_MIL or $dpl_array[$alid] == DPL_TYPE_MC)) // a un pacte
				$leg_array[$key]['can_def'] = true;
		}

		if((!$leg_array[$key]['can_def'] // pas allié
			&& (!$alid or !isset($dpl_array[$alid]) or $dpl_array[$alid] != DPL_TYPE_PNA) // pas de PNA
		) && (
			(abs($pts - $points) < ATQ_PTS_DIFF)  /* Trop de points de différences */
			&& ($pts > ATQ_PTS_MIN)  /* Pas assez de points pour attaquer */
			|| ($pts >= ATQ_LIM_DIFF && $points >= ATQ_LIM_DIFF) /* Arène */
		)
		&& $_ses->canDo(DROIT_PLAY)/* Faut pas être un visiteur */
		&& $etat == MBR_ETAT_OK /* Validé et pas en Veille */
		&& isset($arr_cid[$value['leg_cid']])/* légion sur la même case */
		&& in_array($leg_etat,[LEG_ETAT_VLG,LEG_ETAT_GRN,LEG_ETAT_DPL])/* légion en attende d'ordre*/
		)
			$leg_array[$key]['can_atq'] = true;
		else
			$leg_array[$key]['can_atq'] = false;
	}

	return $leg_array;
}
