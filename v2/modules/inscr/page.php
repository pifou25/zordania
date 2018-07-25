<?php
if(!defined("_INDEX_")){ exit; }

$_tpl->set("module_tpl","modules/inscr/inscr.tpl");

require_once("lib/member.lib.php");
require_once("lib/ini.lib.php");

if(!$_act || $_act == "new") {
	/* Nouveau Membre */
	$_tpl->set("mbr_act","new");
	$_tpl->set("mbr_show_form",true);

	$parrain = request("mbr_parrain", "uint", "post", request("parrain", "uint", "get"));
	$pass = request("pass", "string", "post");
	$pass2 = request("pass2", "string", "post");
	$login = request("login", "string", "post");
	$mail = request("mail", "string", "post");
	$mail2 = request("mail2", "string", "post");
	$lang = request("lang", "string", "post");
	$decal = request("decal", "string", "post");

	if ($parrain)
	{
		$mbr_array = Mbr::getFull($parrain);
		if (!$mbr_array)
			$parrain = 0;
	}

	$_tpl->set("mbr_login", $login);
	$_tpl->set("mbr_parrain", $parrain);
	$_tpl->set("mbr_mail", $mail);
	$_tpl->set("mbr_lang", $lang);
	$_tpl->set("mbr_decal", $decal);
	$_tpl->set("mbr_date", date("H:i:s"));


	$questions = request("questions", "array", "post");

	$max_inscrits = SITE_MAX_INSCRITS;
	$inscrits = Mbr::count();

	if($max_inscrits <= $inscrits) {
		$_tpl->set("mbr_max_inscrit",true);
	} else if(can_d(DROIT_PLAY)) {
		$_tpl->set("mbr_is_loged",true);
	} else if(!$login && !$pass && !$pass2 && !mailverif($mail) 
		&& !$lang && !$decal) {
		$_tpl->set("mbr_new",true);
	} else if(!$login || !$pass || !$pass2 || !mailverif($mail) 
		|| !$lang || !$decal || !in_array($lang,$_langues)) {
		$_tpl->set("mbr_notallpost",true);
	} else if(!ctype_alnum($login)) {
		$_tpl->set("mbr_name_not_correct", true);
	} elseif($pass != $pass2) {
		$_tpl->set("mbr_pass_inegal",true);
	} else if($mail != $mail2) {
		$_tpl->set("mbr_mail_inegal",true);
	} else if(!count($questions) || in_array(0,$questions)) {
		$_tpl->set("mbr_questionaire_faux",true);
	} else { // verifier si humain
		$score = true;
		if ($score)
		{
			// v�rifier unicit� du login et du mail
			$count = Mbr::where('mbr_login', $login)->orWhere('mbr_mail', $mail)->count();
			if($count > 0)
				$_tpl->set("mbr_error",true);
			else if($mid = Mbr::add($login, $_ses->crypt($login,$pass), $mail, $lang, MBR_ETAT_INI, GRP_JOUEUR, $decal, get_ip(), $_css[0], $parrain)) {// membre ajout�, envoyer le mail
				$_tpl->set("mbr_show_form",false);
				$_tpl->set("mbr_ok", mail_init($mid, $login, $pass, $mail));
				$_ses->login($login, $pass);// se connecter (!)
			} else { // cas d'erreur non pr�vu
				$_tpl->set("mbr_error",true);
			}
		}
		else
		{
			$_tpl->set("mbr_not_human",true);
		}
	}
} else if($_act == "vldpass") { /* valider le changement de pass */
	$_tpl->set("mbr_act","vldpass");

	$key = request("key", "string", "get");
	$mid = request("mid", "uint", "get");
	$pass = request("pass", "string", "get");

	$vld_array = Vld::get($mid);
	foreach($vld_array as $value) {
		if($value['vld_act'] == 'edit' && $key == $value['vld_rand']) {
			Vld::del($key, $mid);
			Mbr::edit($mid, array("pass" =>$pass));// modifier le mot de passe
			$mbr_array = Mbr::get(['mid' => $mid])[0];
			$_tpl->set("mbr_edit", true);
			if($mbr_array['mbr_etat']==MBR_ETAT_INI){// compte non encore valid�: relancer mail
				Vld::del($mid);// on annule tout ancien changement
				$_tpl->set("mbr_newkey", mail_init($mid));
			}
			break;
		}
	}

} else if($_act == "newpass") { /* Perdu son pass */
	$_tpl->set("mbr_act","newpass");

	$login = request("login", "string", "post");
	$mail = request("mail", "string", "post");
	if(!$login || !$mail){
		$login = request("login", "string", "get");
		$mail = request("mail", "string", "get");
	}
	$_tpl->set('mbr_login', $login);
	$_tpl->set('mbr_mail', $mail);
	if(!$login || !$mail)
		$_tpl->set("mbr_form",true);
	else {
		$mbr_array = Mbr::get(array('login' => $login, 'mail' => $mail, 'op' => 'AND'));

		if(!$mbr_array){
			$_tpl->set("mbr_not_exist",true);
			$_tpl->set("mbr_form",true);
		}else{
			$mid = $mbr_array[0]['mbr_mid'];
			Vld::del($mid);// on annule tout ancien changement
			$_tpl->set("mbr_edit",mail_chg_pass(array('mid' => $mid)));
		}
	}
} else if($_act == "del") { /* supprimer inscription */
	$_tpl->set("mbr_act","del");
	$mid = request("mid", "uint", "get");
	$key = request('key', 'string', 'get');
	$mail = request("mail", "string", "get");

	if($key && $mid){ // rechercher la validation
		$vld_array = Vld::get($mid);

		if(!$vld_array) {
			$_tpl->set("mbr_no_del", true);
		} else if($vld_array[0]['vld_act'] != 'del' and $vld_array[0]['vld_act'] != 'rest') {
			$_tpl->set("mbr_another_valid", true);
		} else if($vld_array[0]['vld_rand'] != $key) { /* On a pas, ou pas la bonne clef */
			$_tpl->set("mbr_no_key", true);
		} else {// suppression ok!
			/* Includes */
			require_once("lib/unt.lib.php");
			require_once("lib/res.lib.php");
			require_once("lib/src.lib.php");
			require_once("lib/alliances.lib.php");

			if($mbr_user = init_get_mbr(array('mid'=>$mid))){
				Mbr::cls($mid, $mbr_user['mbr_mapcid'], $mbr_user['mbr_race']);
				$_tpl->set("mbr_cls", true);
			}else
				$_tpl->set("mbr_cls", false);
		}

	}else if($mid && $mail){//envoyer un mail de confirmation
		Vld::del($mid);// on annule tout ancien changement
		$_tpl->set('send_mail',mail_del($mid, $mail));
	}
} else if($_act == 'rest') { /* relance mail : quitter ou r�activer */
	$_tpl->set("mbr_act","rest");
	$mid = request("mid", "uint", "get");
	$login = request('login', 'string', 'get');
	$pass = request('pass', 'string', 'get');
	$key = request('key', 'string', 'get');

	if($key && $login && $pass){ // rechercher la validation
		if(!$_ses->login($login, $pass, true)) // se connecter (?)
			$_tpl->set("mbr_bad_pass", true);
		else {
			$mid = $_ses->get('mid');
			$vld_array = Vld::get($mid);
			$_tpl->set("key",$key);

			if(!$vld_array) {
				$_tpl->set('mbr_no_del', true);
			} else if($vld_array[0]['vld_act'] != 'rest') {
				$_tpl->set('mbr_another_valid', $vld_array[0]['vld_act'] );
			} else if($vld_array[0]['vld_rand'] != $key) { /* aucune ou mauvaise clef */
				$_tpl->set('mbr_no_key', true);
			} else { // identification par login & cl� suite au mail de relance OK
				$_tpl->set('mid',$mid);
			}		
		}		
	} else {
		$_tpl->set('err_no_param',true);
	}
}
?>
