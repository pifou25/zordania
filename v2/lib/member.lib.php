<?php

/**
 * vérifier sur chaque membre de $mbr_array s'il peut attaquer / défendre qq1
 * @param type $mbr_array = liste des membres à enrichir
 * @param type $points
 * @param type $mid
 * @param type $groupe
 * @param type $alaid
 * @param type $dpl_array
 * @return array $mbr_array
 */
function can_atq_lite($mbr_array, $points, $mid, $groupe, $alaid, $dpl_array = false)
{
	$mid = protect($mid, "uint");
	$points = protect($points, "uint");
	$groupe = protect($groupe, "uint");
	$alaid = protect($alaid, "int");
	if($dpl_array !== false) $dpl_array = protect($dpl_array, 'array');

	foreach($mbr_array as $key => $mbr) {
		$pts = $mbr['mbr_pts_armee'];	
		$alid = $mbr['ambr_aid'];

		/* si c'est un allié qu'on peut défendre */
		$mbr_array[$key]['can_def'] = false;
		$mbr_array[$key]['pna'] = false;

		/* staff intouchable  - sauf par lui même 
		$staff = array(GRP_GARDE, GRP_PRETRE, GRP_DEMI_DIEU, GRP_DIEU, GRP_DEV, GRP_ADM_DEV);
		if (in_array($mbr['mbr_gid'], $staff) and !in_array($groupe, $staff)) {
			$mbr_array[$key]['can_atq'] = false;
			continue;
		} */

		/* grade event = protégé */
		if ($mbr['mbr_gid'] == GRP_EVENT and $groupe != GRP_EVENT) {
			$mbr_array[$key]['can_atq'] = false;
			continue;
		}

		if($alid && $alaid){
			if ($alid == $alaid) // même alliance
				$mbr_array[$key]['can_def'] = true;
			elseif (isset($dpl_array[$alid])) { // a un pacte
				if($dpl_array[$alid] == DPL_TYPE_MIL or $dpl_array[$alid] == DPL_TYPE_MC)
					$mbr_array[$key]['can_def'] = true;
				if($dpl_array[$alid] == DPL_TYPE_PNA)
					$mbr_array[$key]['pna'] = true;
			}
		}

		$mbr_array[$key]['can_atq'] = false;
		if((!$mbr_array[$key]['can_def'] // pas allié
			&& !$mbr_array[$key]['pna'] // pas de PNA
		) && (
			(abs($pts - $points) < ATQ_PTS_DIFF)  /* Trop de points de différences */
			&& ($pts > ATQ_PTS_MIN)  /* Pas assez de points pour etre attaqué */
			&& ($points > ATQ_PTS_MIN)  /* Pas assez de points pour attaquer */
			|| ($pts >= ATQ_LIM_DIFF && $points >= ATQ_LIM_DIFF) /* Arène */
		) && $mbr['mbr_mid'] != $mid /* Soit même */
		&& $groupe != GRP_VISITEUR /* Faut pas être un visiteur */
		&& $mbr['mbr_etat'] == MBR_ETAT_OK) /* Validé et pas en Veille */
			$mbr_array[$key]['can_atq'] = true;
	}
	return $mbr_array;	
}


function upload_logo_mbr($mid, $fichier)
{
	$mid = protect($mid, "uint");
	$fichier = protect($fichier, "array");
		
	if(!$fichier)
		return false;
		
	$nom = $fichier['name'];
	$taille = $fichier['size'];
	$tmp = $fichier['tmp_name'];
	$type = $fichier['type'];
	$erreur = $fichier['error'];
		
	if($erreur)
		return false;

	if($taille > MBR_LOGO_SIZE || !strstr(MBR_LOGO_TYPE, $type))
		return false;
		
	$nom_destination = MBR_LOGO_DIR.$mid.'.png';
	move_uploaded_file($tmp, $nom_destination);

	list($width, $height, $type, $attr) = getimagesize(MBR_LOGO_DIR.$mid.'.png');
		
	if($width <= MBR_LOGO_MAX_X_Y && $height <= MBR_LOGO_MAX_X_Y)
		return true;
	else { /* On redimensionne */
		$owidth = $width;
		$oheight= $height;
		$rap = $width / $height;
		$width = round(($width == $height) ? MBR_LOGO_MAX_X_Y : (($width > $height) ? MBR_LOGO_MAX_X_Y : MBR_LOGO_MAX_X_Y * $rap));
		$height = round($width / $rap);
			
		$im1 = imagecreatefrompng($nom_destination);
			
		$im2 = imagecreatetruecolor ($width, $height);
		imagecopyresized ( $im2, $im1, 0, 0, 0, 0, $width, $height, $owidth, $oheight);
		imagepng($im2,$nom_destination);
		return true;
	}
}

function get_mbr_logo($mid) {
	$mid = protect($mid, "uint");
	
	$file = MBR_LOGO_DIR.$mid.'.png';
	if(file_exists($file))
		return 'img/mbr_logo/'.$mid.'.png';
	else
		return 'img/mbr_logo/0.png';
}


function calc_dst($x1, $y1, $x2, $y2) {
	$x1 = protect($x1, "uint");
	$y1 = protect($y1, "uint");
	$x2 = protect($x2, "uint");
	$y2 = protect($y2, "uint");

	/* On ne calcule pas la distance a vol d'oiseau, mais la distance que la légion devrais parcourir */
	$diffx = abs($x1 - $x2); 
	$diffy = abs($y1 - $y2);
	if($diffx == $diffy) { /* juste une diagonale */
		return $diffx;
	} else {
		return max($diffx, $diffy); 
	}
}

/**
 * MbrOld historique des membres
 * @global type $_sql
 * @param type $mid
 * @return type
 */
function add_old_mbr($mid) {
	global $_sql;

	$mid = protect($mid, "uint");
	
	$sql = "INSERT INTO ".$_sql->prebdd."mbr_old (mold_mid, mold_pseudo, mold_mail, mold_lip) ";
	$sql.= "SELECT mbr_mid, mbr_pseudo, mbr_mail, mbr_lip FROM ".$_sql->prebdd."mbr WHERE mbr_mid = $mid";
	return $_sql->query($sql);
}

function get_old_mbr($cond = array()) {
	global $_sql;
	
	$cond = protect($cond, "array");
	
	$mid = 0; $pseudo = ""; $ip = "";
	
	if(isset($cond['mid']))
		$mid = protect($cond['mid'], "uint");
	if(isset($cond['pseudo']))
		$pseudo = protect($cond['pseudo'], "string");
	if(isset($cond['ip']))
		$ip = protect($cond['ip'], "string");
		
	$sql = "SELECT * FROM ".$_sql->prebdd."mbr_old ";
	if($mid || $pseudo || $ip) {
		$sql .= "WHERE ";
			
		if($mid) $sql .= "mold_mid = $mid AND ";
		if($pseudo) $sql .= "mold_pseudo LIKE '%$pseudo%' AND ";
		if($ip) $sql .= "mold_lip LIKE '%$ip%' AND ";
		
		$sql = substr($sql, 0, strlen($sql) - 4);
	}
	return $_sql->make_array($sql);
}

/**
 * log des IP MbrLog
 * @global type $_sql
 * @param type $mid
 * @param type $ip
 * @param type $select
 * @param type $gid
 * @return type
 */
function get_log_ip($mid = 0, $ip = '', $select = '',$gid = ''){
	global $_sql;

	$gid = protect($gid, "uint");
	$mid = protect($mid, 'uint');
	$ip = protect($ip, 'string');

	if($select == 'full')
		$sql = 'SELECT mlog_mid, IFNULL(mbr_pseudo, mold_pseudo) AS mbr_pseudo, IFNULL(mbr_gid,0) AS mbr_gid, IFNULL(mbr_mail, mold_mail) AS mbr_mail, mlog_ip, _DATE_FORMAT(mlog_date) AS mlog_date
		FROM '.$_sql->prebdd.'mbr_log
		LEFT JOIN '.$_sql->prebdd.'mbr ON mlog_mid = mbr_mid
		LEFT JOIN '.$_sql->prebdd.'mbr_old ON mlog_mid = mold_mid';
	else
		$sql = 'SELECT mlog_mid, mlog_ip, _DATE_FORMAT(mlog_date) AS mlog_date
		FROM '.$_sql->prebdd.'mbr_log';
	$where = '';
	if($mid)
		$where .= " AND mlog_mid = $mid ";
	if($ip)
		$where .= " AND mlog_ip = '$ip' ";
	if($where)
		$sql .= ' WHERE '.substr($where, 4);
	if($select == 'full' and $gid != GRP_DIEU)
		$sql.= " AND mbr_gid NOT IN (".GRP_GARDE.",".GRP_PRETRE.",".GRP_DEMI_DIEU.",".GRP_DIEU.",".GRP_DEV.",".GRP_ADM_DEV.") ";
	$sql .= ' ORDER BY '.$_sql->prebdd.'mbr_log.mlog_date DESC LIMIT 0, 15';

	return $_sql->make_array($sql);
}

/**
 * ajoute une surveillance - Surv
 * @global type $_sql
 * @param type $mid
 * @param type $mid_admin
 * @param type $type
 * @param type $cause
 * @return type
 */
function add_surv($mid, $mid_admin, $type, $cause){
	$mid = protect($mid, "uint");
	$type = protect($type, "uint");
	$mid_admin = protect($mid_admin, "uint");
	$cause = protect($cause, "string");
	global $_sql;
	
	$sql = "INSERT INTO ".$_sql->prebdd."surv (surv_id, surv_mid, surv_admin, surv_debut, surv_etat, surv_type, surv_cause) ";
	$sql .= "VALUES (NULL, '$mid', '$mid_admin', NOW(), '".SURV_OK."', '$type', '$cause') ";
	
	return $_sql->query($sql);
}

//renvoie les surveillances admin affectées au joueur dont on passe l'id comme argument
function get_surv($mid){
	$mid = protect($mid, 'uint');
	global $_sql;
	
	$sql = "SELECT surv_id, surv_mid, surv_etat, surv_admin, _DATE_FORMAT(surv_debut) as debut, ";
	$sql.= " _DATE_FORMAT(surv_debut + INTERVAL ".SURV_DUREE." SECOND) as fin, surv_type, surv_cause"; 
	$sql.= " FROM ".$_sql->prebdd."surv";
	$sql.= " WHERE surv_mid=".$mid;
	$sql.= " AND surv_etat = ".SURV_OK;
	return $_sql->make_array($sql);
}

function get_surv_by_sid($sid){
	$sid = protect($sid, 'uint');
	global $_sql;
	
	$sql = "SELECT surv_mid, surv_type, surv_etat, _DATE_FORMAT(surv_debut + INTERVAL ".SURV_DUREE." SECOND) as fin ";
	$sql.= "FROM ".$_sql->prebdd."surv";
	$sql.= " WHERE surv_id = ".$sid;
	$sql.= " AND surv_etat = ".SURV_OK;
	return $_sql->make_array($sql);
}

function get_surv_list(){
	global $_sql;
	$sql = " SELECT surv_id, surv_mid, surv_admin, surv_type, surv_debut, surv_cause, mbr.mbr_pseudo AS surv_pseudo, mbr2.mbr_pseudo AS surv_adm_pseudo ";
	$sql.= " FROM ".$_sql->prebdd."surv ";
	$sql.= " JOIN ".$_sql->prebdd."mbr as mbr ON mbr.mbr_mid = surv_mid";
	$sql.= " JOIN ".$_sql->prebdd."mbr as mbr2 ON mbr2.mbr_mid = surv_admin";
	$sql.= " WHERE surv_etat = ".SURV_OK;
	return $_sql->make_array($sql);
}
function get_fin_surv_list (){
	global $_sql;
	$sql = " SELECT surv_id, surv_mid, surv_admin, surv_type, surv_fin, surv_cause, mbr.mbr_pseudo AS surv_pseudo, mbr2.mbr_pseudo AS surv_adm_pseudo ";
	$sql.= " FROM ".$_sql->prebdd."surv ";
	$sql.= " JOIN ".$_sql->prebdd."mbr as mbr ON mbr.mbr_mid = surv_mid";
	$sql.= " JOIN ".$_sql->prebdd."mbr as mbr2 ON mbr2.mbr_mid = surv_admin";
	$sql.= " WHERE surv_etat = ".SURV_CLOSE. " AND surv_fin >= DATE_SUB(NOW(),INTERVAL 1 MONTH)";
	return $_sql->make_array($sql);

}
function close_surv($sid){
	$sid =  protect($sid, "uint");
	global $_sql;
	
	$sql = " UPDATE ".$_sql->prebdd."surv ";
	$sql.= " SET surv_fin = NOW(), surv_etat = ".SURV_CLOSE;
	$sql.= " WHERE surv_id = ".$sid;
	return $_sql->query($sql);
}

function is_surv($mid){
	$mid = protect($mid, "uint");
	$array = get_surv($mid);
	if(empty($array))
		return false;
	return true;
}
