<?php
if(!defined("_INDEX_")){ exit; }
if(!can_d(DROIT_PLAY)) { 
	$_tpl->set("need_to_be_loged",true); 
} else {
$_tpl->set('module_tpl','modules/notes/notes.tpl');

$nid = request("nid", "uint", "get");

$_tpl->set('nte_act',$_act);

switch($_act) {
case "del":
	//Effacer
	if(Nte::del($_user['mid'], $nid))
		$_tpl->set('nte_ok',true);
	else
		$_tpl->set('nte_bad_nid',true);

	break;
case "edit":
	//Editer ou ajouter
	$titre = request("pst_titre", "string", "post");
	$texte = request("pst_msg", "string", "post");
	$import = request("nte_import", "uint", "post");

	if($nid) { // edit
		$_tpl->set('nte_nid',$nid);
		$nte_array = Nte::get($_user['mid'], $nid);
		if($nte_array) {
			$nte_array = $nte_array[0];
			$_tpl->set('nte_titre',$nte_array['nte_titre']);
			$_tpl->set('nte_texte',Parser::unparse($nte_array['nte_texte']));
			$_tpl->set('nte_import',$nte_array['nte_import']);
		} else
			$_tpl->set('nte_bad_nid',true);

		if($titre && $texte)
			$_tpl->set('nte_ok',Nte::edit($_user['mid'], $nid, $titre, Parser::parse($texte), $import));

	}else{ // new
		$_tpl->set('nte_nid',0);

		if($titre && $texte)
			$_tpl->set('nte_ok',Nte::add($_user['mid'], $titre, Parser::parse($texte), $import));
		else {
			$_tpl->set('nte_titre',htmlspecialchars($titre));
			$_tpl->set('nte_texte',htmlspecialchars($texte));
			$_tpl->set('nte_import',$import);	
		}	

		if($titre || $texte || $import) {
			$_tpl->set('nte_titre',$titre);
			$_tpl->set('nte_texte',$texte);
			$_tpl->set('nte_import',$import);	
		}

	}
	break;
case "view":
	//Voir
	if($nid) {
		$nte_array = Nte::get($_user['mid'], $nid);
		if($nte_array)
			$nte_array = $nte_array[0];
		$_tpl->set('nte_array',$nte_array);
	} else
		$_tpl->set('nte_bad_nid',true);

	break;
default:
	$nte_array = Nte::get($_user['mid']);
	$_tpl->set('nte_array',$nte_array);
	break;
}
}
?>
