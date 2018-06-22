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
