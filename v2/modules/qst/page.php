<?php
//Verifiparamions
if(!defined("_INDEX_")){ exit; }
if(can_d(DROIT_PLAY)!=true) $_tpl->set("need_to_be_loged",true); 
else{
    
    $_tpl->set('module_tpl', 'modules/qst/quetes.tpl');
    require_once("lib/qst.lib.php");
    
    //smiley
    require_once('lib/parser.lib.php');
    $smileys_base = getSmileysBase();
    $smileys_more = getSmileysMore($smileys_base);
    $_tpl->set("smileys_base", $smileys_base);
    $_tpl->set("smileys_more", $smileys_more);
    
    //init tpl
    $qid = request("qid", "uint", "get");
    $_tpl->set('qst_act',$_act);

    switch($_act) {
    case "del":
        //Effacer
        if(del_qst(/*$_user['mid'],*/ $qid))
            $_tpl->set('qst_ok',true);
        else
            $_tpl->set('qst_bad_qid',true);

        break;
    case "edit":
        //Editer ou ajouter
        //cas de la race            
             $race = request("race", "uint", "get");
            
            if(!isset($_races[$race]) or !$_races[$race])
                $race = 1;

            load_conf($race);
            /* virer les races invisibles ici */
            foreach($_races as $key => $value)
                if(!$value)
                    unset($_races[$key]);
            
        //paramètre défini?
            $param_1 = request("param", "string", "get");
            
            if($param_1 != "") $_tpl->set('param_ok',true);
            else $_tpl->set('param_ok',false);    
            
            
        //post
        $qst_statut = request("qst_statut", "uint", "post");
            
        $objectif_1 = request("obj_1", "string", "post");
        $obj_val_1 = request("obj_val1", "uint", "post"); 
            
        $req_quest = request("req_qid", "uint", "post");    
            
        $recompense_1 = request("rec_1", "string", "post");
        $rec_val_1 = request("rec_val1", "uint", "post");
        $recompense_2 = request("rec_2", "string", "post");
        $rec_val_2 = request("rec_val2", "uint", "post");                 
        $rec_xp = request("rec_xp", "uint", "post"); 
            
        $titre = request("pst_titre", "string", "post");
        $descr = request("pst_msg", "string", "post");


        //gestion de la page
        if($qid) { // edit
            $_tpl->set('qst_qid',$qid);
            
            //liste des quetes
            $qst_array = get_qst(0);
            if($qst_array)
            $_tpl->set('qst_array',$qst_array);
                else $_tpl->set('qst_array',false);
            
            //infos de la quête
            $qst_array = get_qst(/*$_user['mid'],*/ $qid);
            if($qst_array) {
                $qst_array = $qst_array[0];
                
                $race = $qst_array['qst_race'];
                $param_1 = $qst_array['qst_param_1'];
                
                $_tpl->set('pst_titre', $qst_array['qst_title']);
                $_tpl->set('pst_msg', unparse($qst_array['qst_descr']));
                $_tpl->set('qst_statut', $qst_array['qst_statut']);
                $_tpl->set('raceid', $qst_array['qst_race']);
                $_tpl->set('req_qid', $qst_array['qst_req_qid']);
                if($param_1 != "") $_tpl->set('param_obj',$_conf[$race]->$param_1);
                $_tpl->set('param', $param_1);
                $_tpl->set('man_res', $_conf[$qst_array['qst_race']]->res);
                $_tpl->set('obj_1',$qst_array['qst_obj_1']);
                $_tpl->set('obj_val1',$qst_array['qst_obj_val1']);
                $_tpl->set('rec_1',$qst_array['qst_recomp_cat1']);
                $_tpl->set('rec_val1',$qst_array['qst_recomp_val1']);
                $_tpl->set('rec_2',$qst_array['qst_recomp_cat2']);
                $_tpl->set('rec_val2',$qst_array['qst_recomp_val2']);
                $_tpl->set('rec_xp',$qst_array['qst_recomp_val3']);	
                
            } else
                $_tpl->set('qst_bad_qid',true);

            if($titre && $descr)
                $_tpl->set('qst_ok',edit_qst($qid, $_user['mid'], $titre, parse($descr), $qst_statut, $race, $req_quest, $param_1, $objectif_1, $obj_val_1, $recompense_1, $rec_val_1, $recompense_2, $rec_val_2, $rec_xp));

        }
        else{ // new
            $_tpl->set('qst_qid',0);

            if($titre && $descr)
                $_tpl->set('qst_ok',add_qst($_user['mid'], $titre, parse($descr), $qst_statut, $race, $req_quest, $param_1, $objectif_1, $obj_val_1, $recompense_1, $rec_val_1, $recompense_2, $rec_val_2, $rec_xp ));
            else {
                
                $qst_array = get_qst(/*$_user['mid'],*/ 0);
            if($qst_array)
               // $qst_array = $qst_array[0];
            $_tpl->set('qst_array',$qst_array);
                else $_tpl->set('qst_array',false);
                
                $_tpl->set('pst_titre',htmlspecialchars($titre));
                $_tpl->set('pst_msg',htmlspecialchars($descr));
                $_tpl->set('qst_statut', QST_CFG_OFF);
                $_tpl->set('raceid', $race);
                $_tpl->set('req_qid', 0);
                $_tpl->set('param', $param_1);
                if($param_1 != "") $_tpl->set('param_obj',$_conf[$race]->$param_1);
                $_tpl->set('man_res',$_conf[$race]->res);
                $_tpl->set('obj_1',);
                $_tpl->set('obj_val1',0);
                $_tpl->set('rec_1',);
                $_tpl->set('rec_val1',0);
                $_tpl->set('rec_2',);
                $_tpl->set('rec_val2',0);
                $_tpl->set('rec_xp',0);	
            }	

            if($titre || $descr) {
                $qst_array = get_qst(/*$_user['mid'],*/ 0);
            if($qst_array)
               // $qst_array = $qst_array[0];
            $_tpl->set('qst_array',$qst_array);
                else $_tpl->set('qst_array',false);
                $_tpl->set('pst_titre',$titre);
                $_tpl->set('pst_msg',$descr);
                $_tpl->set('qst_statut', $qst_statut);
                $_tpl->set('raceid', $race);
                $_tpl->set('req_qid', $req_quest);
                if($param_1 != "") $_tpl->set('param_obj',$_conf[$race]->$param_1);	
                $_tpl->set('param', $param_1);	
                $_tpl->set('obj_1',$objectif_1);
                $_tpl->set('obj_val1',$obj_val_1);
                $_tpl->set('rec_1',$recompense_1);
                $_tpl->set('rec_val1',$rec_val_1);
                $_tpl->set('rec_2',$recompense_2);
                $_tpl->set('rec_val2',$rec_val_2);
                $_tpl->set('rec_xp',$rec_xp);
                
                $_tpl->set('man_res',$_conf[$race]->res);
            }

        }
        break;
    case "view":
        //Voir
        if($qid) {
            $qst_array = get_qst(/*$_user['mid'],*/ $qid);
            if($qst_array)
                $qst_array = $qst_array[0];
            $_tpl->set('qst_array',$qst_array);
        } else
            $_tpl->set('qst_bad_qid',true);

        break;
    default:
        $qst_array = get_qst($qid);
        $_tpl->set('qst_array',$qst_array);
        break;
    }


	
}
?>