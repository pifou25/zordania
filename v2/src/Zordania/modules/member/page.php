<?php
if(!defined("_INDEX_")){ exit; }

$_tpl->set("module_tpl","modules/member/member.tpl");

$mid = request("mid", "uint", "get");


if(!$_ses->canDo(DROIT_PLAY) && ($_act != "view" && $_act != "liste"))
	$_tpl->set("mbr_not_loged",true);
else if(!$_act) {
	/* Afficher les détails sur mon compte */
	$array = Mbr::getFull($_user['mid']);
	$array = $array[0];
	if($array['ambr_etat'] == ALL_ETAT_DEM) {
		$array['al_name'] = "";
		$array['ambr_aid'] = 0;
	}
	$_tpl->set("mbr_array",$array);
	$_tpl->set('rec_array', Rec::get($_user['mid']));

	$vld_array = Vld::get($_user['mid']);
	$_tpl->set('vld_array',$vld_array);
} elseif($_act == "del") {
	$_tpl->set("mbr_act","del");

	$vld_array = Vld::get($_user['mid']);
	if($_sub == "pre") { /* On prépare la suppression */
		$_tpl->set("mbr_sub",$_sub);
		if (!empty($vld_array)) {
			$_tpl->set("mbr_another_valid", true);
		}else{
			$key = genstring(GEN_LENGHT);
			$result = Vld::add($key, $_user['mid'], 'del');
			if(!$result)
				$_tpl->set("mbr_another_valid", true);
			else {
				$_tpl->set("vld_key", $key);

				$sujet = $_tpl->get("modules/member/mails/objet_del.tpl",1);
				$texte = $_tpl->get("modules/member/mails/text_del.tpl",1);
				mailto(SITE_WEBMASTER_MAIL,$_user['mail'], $sujet, $texte);
			}
		}
	} else {
		$key = request("key", "string", "post", request("key", "string", "get"));

		$_tpl->set("vld_key",$key);

		if(!$vld_array) {
			$_tpl->set("mbr_no_del", true);
		} else if($vld_array[0]['vld_act'] != "del") {
			$_tpl->set("mbr_another_valid", true);
		} else if($vld_array[0]['vld_rand'] != $key) { /* On a pas, ou pas la bonne clef */
			$_tpl->set("mbr_no_key", true);
		} else {

			Mbr::cls($_user['mid'], $_user['mapcid'], $_user['race']);
			$_tpl->set("mbr_cls", true);	
		}
	}
} else if($_act == "liste") {
	$_tpl->set("module_tpl","modules/member/liste.tpl");
	$_tpl->set('mbr_act','liste');

	/* gestion du where */
	$pseudo = request("pseudo", "string", "post", request("pseudo", "string", "get"));
	$race = request("race", "uint", "post", request("race", "uint", "get"));
	$diffpoint = request("diffpoint", "uint", "post", request("diffpoint", "uint", "get"));
	$diffpts_arm = request("diffpts_arm","uint", "post", request("diffpts_arm","uint", "get"));
	
        $cond = ['etat' => [MBR_ETAT_OK], 'op' => 'AND'];
	if($_ses->canDo(DROIT_PLAY))
		$cond['dst'] = array($_user['map_x'], $_user['map_y']);
	else
		$cond['dst'] = array(0, 0);

	if($pseudo)
		$cond['pseudo'] = "%$pseudo%";
	if($race && in_array($race, $_races))
		$cond['race'] = array($race);
	if($diffpoint) {
		$cond['ltpoint'] = $_user['points'] + $diffpoint;
		$cond['gtpoint'] = $_user['points'] - $diffpoint;
	}
	if($diffpts_arm) {
		$cond['ltpts_arm'] = $_user['pts_arm'] + $diffpts_arm;
		$cond['gtpts_arm'] = $_user['pts_arm'] - $diffpts_arm;
	}
        
      
	$_tpl->set("mbr_pseudo", $pseudo);
	$_tpl->set("mbr_race", $race);
	$_tpl->set("mbr_diffpoint", $diffpoint);
	$_tpl->set("mbr_diffpts_arm",$diffpts_arm);

	/* gestion du order by */
	$order = request("order", "uint", "get", request("order", "uint", "post"));
	$by = request("by", "string", "get", request("by", "string", "post"));
	if($by) {
		if($order == 2)
			$order = "DESC";
		else
			$order = "ASC";

		$order_by = array($order, $by);
	} else
		$order_by = array('DESC', 'points');

	$_tpl->set("mbr_order", $order_by[0] == "DESC" ? 2 : 1);
	$_tpl->set("mbr_by", $order_by[1]);

	$mbr_page = request("mbr_page", "int", "get", -1);

	$mbr_nb = Mbr::count($cond);
        

	$current_i = $mbr_page - LIMIT_NB_PAGE/2;
	$current_i = round($current_i < 0 ? 0 : $current_i)*LIMIT_MBR_PAGE;
	$nombre_page = ($mbr_nb / LIMIT_MBR_PAGE);
	$nombre_total = ceil($nombre_page);
	$nombre = $nombre_total - 1;

	$_tpl->set('limite_page',LIMIT_MBR_PAGE);
	$_tpl->set('limite_nb_page',LIMIT_NB_PAGE);
	$_tpl->set('current_i',$current_i);
	$_tpl->set("mbr_nb",$mbr_nb);

	if($mbr_page > 0 || $order_by != array('DESC','points'))
		$limite_mysql = LIMIT_MBR_PAGE * $mbr_page;
	elseif($_ses->canDo(DROIT_PLAY) && $mbr_page < 0)
	{
		$tmp_cond = $cond;
		$tmp_cond['ltpoint'] = $_user['points'];

		$position = Mbr::count($tmp_cond);
		$position = ($mbr_nb - $position);
		$limite_mysql = (floor($position / LIMIT_MBR_PAGE) * LIMIT_MBR_PAGE);
		$mbr_page = $limite_mysql / LIMIT_MBR_PAGE;
	}
	else
		$limite_mysql = 0;

	/* mes pactes */
	$dpl_atq = new diplo(array('aid' => $_user['alaid']));
	$dpl_atq_arr = $dpl_atq->actuels(); // les pactes actifs en tableau

	$mbr_array = Mbr::get(array_merge($cond, 
                ['limite1'=>$limite_mysql, 'limite2'=>LIMIT_MBR_PAGE, 'orderby'=>$order_by,'list'=>true]));
	$mbr_array = Mbr::canAtq($mbr_array, $_user['pts_arm'],$_user['mid'],$_user['groupe'], $_user['alaid'], $dpl_atq_arr);

	$_tpl->set("mbr_array",$mbr_array);	
	$_tpl->set('mbr_page',$mbr_page);
	$_tpl->set('mbr_dpl',$dpl_atq_arr);
}
elseif($_act == "liste_online")
{
	//liste online
	$_tpl->set("module_tpl","modules/member/liste.tpl");
	$_tpl->set("mbr_act","liste_online");

	/* mes pactes */
	$dpl_atq = new diplo(array('aid' => $_user['alaid']));
	$dpl_atq_arr = $dpl_atq->actuels(); // les pactes actifs en tableau

        $pg = new Paginator(Ses::getOnline());
        $pg->get = Mbr::canAtq($pg->get, $_user['pts_arm'],$_user['mid'],$_user['groupe'], $_user['alaid'], $dpl_atq_arr);
	$_tpl->set('pager',$pg);
	$_tpl->set('mbr_dpl',$dpl_atq_arr);
}
elseif($_act == "edit")
{
	$_tpl->set("mbr_act","edit");
	
	$design = request("design", "uint", "post");
	$mail = request("mail", "string", "post");
	$lang = request("lang", "string", "post");
	$decal = request("decal", "string", "post");
	$sign = request("sign", "string", "post");
	$descr = request("descr", "string", "post");
	$pass = request("pass", "string", "post");
	$sexe = request("sexe", "uint", "post");
	
	$_tpl->set("mbr_design", $design);
	$_tpl->set("mbr_mail", $mail);
	$_tpl->set("mbr_lang", $lang);
	$_tpl->set("mbr_decal", $decal);
	$_tpl->set("mbr_sign", htmlspecialchars($sign));
	$_tpl->set("mbr_descr", htmlspecialchars($descr));
	$_tpl->set("mbr_date",date("H:i:s"));
	$_tpl->set("mbr_sexe",$sexe);
	
	$_tpl->set('logo_type',MBR_LOGO_TYPE);
	$_tpl->set('logo_x_y',MBR_LOGO_MAX_X_Y);
	$_tpl->set('logo_size',MBR_LOGO_SIZE);
	
	if(!$_sub) {
		$_tpl->set("mbr_sub","");

		$array = Mbr::getFull($_user['mid']);
		$array = $array[0];
		$_tpl->set("mbr_pseudo",$array['mbr_pseudo']);
		$_tpl->set("mbr_mail",$array['mbr_mail']);
		$_tpl->set("mbr_lang",$array['mbr_lang']);
		$_tpl->set("mbr_decal",$array['mbr_decal']);
		$_tpl->set("mbr_sign",Parser::unparse($array['mbr_sign']));
		$_tpl->set("mbr_descr",Parser::unparse($array['mbr_descr']));
		$_tpl->set("mbr_date",date("H:i:s"));
		$_tpl->set("mbr_sexe",$array['mbr_sexe']);
	} elseif($_sub == "pass") {
		$_tpl->set("mbr_sub","pass");
		
		$pass2 = request("pass2", "string", "post");
		$oldpass = request("oldpass", "string", "post");
		
		if(!$oldpass || !$pass || !$pass2)
			$_tpl->set("mbr_not_all_post",true);	
		else if($_ses->crypt($_user['login'], $oldpass) != $_user['pass']  || $pass != $pass2)
			$_tpl->set("mbr_not_same_pass",true);
		else {
			$pass = $_ses->crypt($_user['login'], $pass);
			$_tpl->set("mbr_edit",Mbr::edit($_user['mid'], array("pass" => $pass)));
			$_ses->set("pass",$pass); /* On change pour pas avoir a se déco */
		}
	} elseif($_sub == "mail") {
		$_tpl->set("mbr_sub","mail");
		
		if(!$mail || !$pass)
			$_tpl->set("mbr_not_all_post",true);
		elseif($_ses->crypt($_user['login'], $pass) != $_user['pass'])
			$_tpl->set("mbr_not_same_pass",true);
		else
			$_tpl->set("mbr_edit",Mbr::edit($_user['mid'], array("mail" => $mail)));
		
	} elseif($_sub == "reset") {
		$_tpl->set("mbr_sub","reset");

		$vld_array = Vld::get($_user['mid']);
		if (!empty($vld_array)) {
			$_tpl->set("mbr_sub",$_sub);
			$_tpl->set("mbr_another_valid", true);
		} else {
			$key = genstring(GEN_LENGHT);
			$_tpl->set('vld_mid',$_user['mid']);
			$_tpl->set('vld_key',$key);
			
			$result = Vld::add($key, $_user['mid'], 'res');
			if(!$result)
			{
				$objet = $_tpl->get("modules/member/mails/objet_reset.tpl",1);
				$texte = $_tpl->get("modules/member/mails/text_reset.tpl",1);
				mailto(SITE_WEBMASTER_MAIL,$_user['mail'],$objet, $texte);	
				$_tpl->set("mbr_edit",true);
			} else
				$_tpl->set("mbr_another_valid",true);
		}
	} else if($_sub == "design") {
		$_tpl->set("mbr_sub","design");
		if(in_array($design, $_css)) {
			$edit['design'] = $design;
			$edit['ldate'] = true;
		
			$_tpl->set("mbr_edit", Mbr::edit($_user['mid'], $edit));
			$_user['design'] = $design;
		}
	} elseif($_sub == "oth") {
		$_tpl->set("mbr_sub","oth");

		$edit = array();
		if($lang && in_array($lang, $_langues))
			$edit['lang'] = $lang;
		
		if($decal)
			$edit['decal'] = $decal;
		
		if($sexe == 1 or $sexe == 2)
			$edit['sexe'] = $sexe;

		$edit['sign'] = Parser::parse($sign, true);
		$edit['descr'] = Parser::parse($descr);
		$edit['ldate'] = true; /* Dans le cas ou rien n'a changé, pour ne pas afficher d'erreur */

		$_tpl->set("mbr_edit", Mbr::edit($_user['mid'], $edit));
	}	
	elseif($_sub == 'logo')
	{
		$_tpl->set('mbr_sub','logo');
		$mbr_logo = request("mbr_logo", "array", "files");
		if($mbr_logo && $mbr_logo['name'])
			$_tpl->set('mbr_edit',  compte::uploadLogo($_user['mid'],$mbr_logo));
		else
			$_tpl->set('mbr_edit', false);
	}
	elseif($_sub == 'del_vld')
	{
		$_tpl->set('mbr_sub','del_vld');
		if(Vld::init($_user['mid']))
			$_tpl->set("mbr_edit",true);
	}
	//edit le compte 
}
elseif($_act == "move") // déménagement
{
	$cid = request("cid", "uint", "get");
	$_tpl->set("module_tpl","modules/member/view.tpl");
	$_tpl->set("cid",$cid);

	if($cid) {
		// calculer le cout et vérifier qu'on a assez + confirmation
		$prix = array(GAME_RES_PRINC => 500, 1=>500, 2=>500);
		$ok = request("ok", "uint", "get");
		if ($ok) {
			// déménager & payer
		} else {
			// afficher prix et demander confirmation
		}
	} else {
		// choisir une destination
	}
}
elseif($mid) //elseif($_act == "view" && $mid)
{
	$_tpl->set("module_tpl","modules/member/view.tpl");
	
	/* mes pactes */
	$dpl_atq = new diplo(array('aid' => $_user['alaid']));
	$dpl_atq_arr = $dpl_atq->actuels(); // les pactes actifs en tableau
	$_tpl->set('mbr_dpl',$dpl_atq_arr);

	//Infos sur un type
	$mbr_array = Mbr::getFull($mid);

	$mbr_array = Mbr::canAtq($mbr_array, $_user['pts_arm'],$_user['mid'],$_user['groupe'], $_user['alaid'], $dpl_atq_arr);

	if(!empty($mbr_array)) {
		$mbr_array = $mbr_array[0];
		$_tpl->set("mbr_array",$mbr_array);

		if($mbr_array['ambr_etat'] == ALL_ETAT_DEM) {
			$mbr_array['al_name'] = "";
			$mbr_array['ambr_aid'] = 0;
		}
		$_tpl->set("spec_title", $mbr_array['mbr_pseudo']); // titre HTML

		//Pour avoir la distance
		if($mbr_array['mbr_etat'] != MBR_ETAT_INI) {
			if($_user['loged'])
				$_tpl->set("mbr_dst", Map::distance($_user['map_x'], $_user['map_y'], $mbr_array['map_x'], $mbr_array['map_y']));
			else
				$_tpl->set("mbr_dst", 0);
		}

		$_tpl->set("mbr_online", Ses::isOnline($mid));
		$mbr_surv = Surv::get($mid);  // surveillance en cours ?
		if(!empty($mbr_surv))
			$_tpl->set("mbr_surv", $mbr_surv[0]);
		$rec_array = Rec::get($mid);
		$_tpl->set("mbr_rec", $rec_array);

		//Lister les filleuls
		$cond = array();
		$cond['parrain'] = $mid;
		$cond['list'] = true;
		$filleuls = Mbr::get($cond);
		$filleuls = Mbr::canAtq($filleuls, $_user['pts_arm'], $_user['mid'], $_user['groupe'], $_user['alaid']);
		$_tpl->set("filleuls", $filleuls);

	}
	else
		$_tpl->set("mbr_mid", $mid);

}
