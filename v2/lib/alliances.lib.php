<?php
	
/**
 * common alliance
 * @param type $aid
 * @return type
 */	
	
function upload_aly_logo($alid, $fichier)
{
	$alid = protect($alid, "uint");
	
	$fichier = protect($fichier, "array");
	
	$nom = protect($fichier['name'], "string");
	$taille = protect($fichier['size'], "uint");
	$tmp = protect($fichier['tmp_name'], "string");
	$type = protect($fichier['type'], "string");
	$erreur = protect($fichier['error'], "string");
	
	if($erreur)
		return $erreur;
	
	if($taille > ALL_LOGO_SIZE OR !strstr(ALL_LOGO_TYPE, $type))
		return false;
		
	$nom_destination = ALL_LOGO_DIR.$alid.'.png';
	move_uploaded_file($tmp, $nom_destination);
	list($width, $height, $type, $attr) = getimagesize(ALL_LOGO_DIR.$alid.'.png');
	if($width <= ALL_LOGO_MAX_X_Y AND $height <= ALL_LOGO_MAX_X_Y)
		return make_aly_thumb($alid,$width,$height);
	else
	{
		$owidth = $width;
		$oheight= $height;
		$rap = $width / $height;
		$width = round(($width == $height) ? ALL_LOGO_MAX_X_Y : (($width > $height) ? ALL_LOGO_MAX_X_Y : ALL_LOGO_MAX_X_Y * $rap));
		$height = round($width / $rap);
		
		$im1 = imagecreatefrompng($nom_destination);	
		$im2 = imagecreatetruecolor ($width, $height);
		imagecopyresized ( $im2, $im1, 0, 0, 0, 0, $width, $height, $owidth, $oheight);
		imagepng($im2,ALL_LOGO_DIR.$alid.'.png');
		
		return make_aly_thumb($alid,$width,$height);
	}
}

function make_aly_thumb($alid,$owidth,$oheight)
{
	$alid = protect($alid, "uint");
	$logo = ALL_LOGO_DIR.$alid.'.png';
	$owidth = protect($owidth, "uint");
	$oheight = protect($oheight, "uint");
	$width = 20;
	$height = 20;
	
	$image_p = imagecreatetruecolor($width, $height);
	$image = imagecreatefrompng($logo);
	$col = imagecolorallocatealpha($image_p, 255,255,255,255);
	imagecolortransparent($image_p, $col);
	imagecopyresampled($image_p, $image, 0, 0, 0, 0,$width, $height, $owidth, $oheight);
	return imagepng($image_p, ALL_LOGO_DIR.$alid.'-thumb.png');	
}


/**
 * virer un membre d'une alliance
 */
function cls_aly(int $mid) {

	/* Il est dans une alliance ? */
	$mbr_infos = Mbr::getFull($mid);
	if(!$mbr_infos)
		return 0;

	$aid = $mbr_infos[0]['ambr_aid'];
	$etat = $mbr_infos[0]['ambr_etat'];

	if(!$aid)
		return 0;

	if($etat == ALL_ETAT_DEM)
	{
		return AlMbr::del($aid, $mid);
	}

	$ally = allyFactory::getAlly($aid);
	if($ally and $ally->al_mid != $mid)/* il n'est pas le chef on peut supprimer */
	{
		Al::edit($aid, array('nb_mbr' => -1));
		return AlMbr::del($aid, $mid);
	}

	/* recherche du nouveau chef par ordre hiérarchique */
	$chef = $ally->getMembers(ALL_ETAT_SECD);
	if(empty($chef)) $chef = $ally->getMembers(ALL_ETAT_INTD);
	if(empty($chef)) $chef = $ally->getMembers(ALL_ETAT_RECR);
	if(empty($chef)) $chef = $ally->getMembers(ALL_ETAT_DPL);

	if(empty($chef))
		foreach($ally->getMembers() as $chef) /* Sinon, on fait n'importe quoi */
				break;

	if(empty($chef) or $chef['mbr_mid'] == $mid) /* Personne ne peut la prendre en charge */
		return Al::del($aid);
	else
		return AlMbr::del($aid, $mid) +
			Al::edit($aid, array('mid' => $chef['mbr_mid'], 'nb_mbr' => -1));
}
