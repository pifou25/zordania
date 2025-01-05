<?php
//Verifiparamions
if(!defined("_INDEX_")){ exit; }
if(can_d(DROIT_PLAY)!=true) $_tpl->set("need_to_be_loged",true); 
else{
    
    $_tpl->set('module_tpl', 'modules/qst/quetes.tpl');
    require_once("lib/qst.lib.php");
    require_once("lib/member.lib.php");
    
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
        if(del_qst_cfg( $qid))
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
            
            
        //post
        $qst_statut = request("qst_statut", "uint", "post");
        $qst_com = request("qst_com", "uint", "post");
            
        $req_quest = request("req_qid", "uint", "post");     
        $req_pts = request("req_pts", "uint", "post");    
        $req_pts_armee = request("req_pts_armee", "uint", "post"); 
        $req_btc = request("req_btc", "uint", "post"); 
        $req_src = request("req_src", "uint", "post"); 
            
        $btc_id_1 = request("btc_id_1", "uint", "post");
        $btc_nb_1 = request("btc_nb_1", "uint", "post");    
        $btc_id_2 = request("btc_id_2", "uint", "post");
        $btc_nb_2 = request("btc_nb_2", "uint", "post");     
        $unt_id_1 = request("unt_id_1", "uint", "post");
        $unt_nb_1 = request("unt_nb_1", "uint", "post");     
        $unt_id_2 = request("unt_id_2", "uint", "post");
        $unt_nb_2 = request("unt_nb_2", "uint", "post");    
        $res_id = request("res_id", "uint", "post");
        $res_nb = request("res_nb", "uint", "post");    
        $src_id = request("src_id", "uint", "post");
            
        $rec_res_id1 = request("rec_1", "string", "post");
        $rec_res_val1 = request("rec_val1", "uint", "post");
        $rec_res_id2 = request("rec_2", "string", "post");
        $rec_res_val2 = request("rec_val2", "uint", "post");                 
        $rec_xp = request("rec_xp", "uint", "post"); 
            
        $titre = request("pst_titre", "string", "post");
        $descr = request("pst_msg", "string", "post");
            
            
        
        $_tpl->set('man_btc', $_conf[$race]->btc);
        $_tpl->set('man_unt', $_conf[$race]->unt);
        $_tpl->set('man_res', $_conf[$race]->res);
        $_tpl->set('man_src', $_conf[$race]->src);


        //gestion de la page
        if($qid) { // edit
            $_tpl->set('qst_qid',$qid);
            
            //liste des quetes
            $qst_array = get_qst_cfg(0, "all");
            if($qst_array)
            $_tpl->set('qst_array',$qst_array);
                else $_tpl->set('qst_array',false);
            
            //infos de la quête
            $qst_array = get_qst_cfg( $qid, "all");
            if($qst_array) {
                $qst_array = $qst_array[0];
                
                $race = $qst_array['qst_race'];
                $param_1 = $qst_array['qst_common'];
                
                $_tpl->set('pst_titre', $qst_array['qst_title']);
                $_tpl->set('pst_msg', unparse($qst_array['qst_descr']));
                $_tpl->set('qst_statut', $qst_array['qst_statut']);
                $_tpl->set('raceid', $qst_array['qst_race']);
                $_tpl->set('qst_com', $qst_array['qst_common']);
                
                $_tpl->set('req_qid', $qst_array['qst_req_qid']);
                $_tpl->set('req_pts', $qst_array['qst_req_pts']);
                $_tpl->set('req_pts_armee', $qst_array['qst_req_pts_armee']);
                $_tpl->set('req_btc', $qst_array['qst_req_btc']);
                $_tpl->set('req_src', $qst_array['qst_req_src']);
                                
                $_tpl->set('btc_id_1', $qst_array['qst_btc_id1']);
                $_tpl->set('btc_nb_1', $qst_array['qst_btc_nb1']);
                $_tpl->set('btc_id_2', $qst_array['qst_btc_id2']);
                $_tpl->set('btc_nb_2', $qst_array['qst_btc_nb2']);
                $_tpl->set('unt_id_1', $qst_array['qst_unt_id1']);
                $_tpl->set('unt_nb_1', $qst_array['qst_unt_nb1']);
                $_tpl->set('unt_id_2', $qst_array['qst_unt_id2']);
                $_tpl->set('unt_nb_2', $qst_array['qst_unt_nb2']);
                $_tpl->set('res_id', $qst_array['qst_res_id']);
                $_tpl->set('res_nb', $qst_array['qst_res_nb']);
                $_tpl->set('src_id', $qst_array['qst_src_id']);
                                
                $_tpl->set('rec_1',$qst_array['qst_rec_res1']);
                $_tpl->set('rec_val1',$qst_array['qst_rec_val1']);
                $_tpl->set('rec_2',$qst_array['qst_rec_res2']);
                $_tpl->set('rec_val2',$qst_array['qst_rec_val2']);
                $_tpl->set('rec_xp',$qst_array['qst_rec_xp']);	
                
            } else
                $_tpl->set('qst_bad_qid',true);

            if($titre && $descr)
                $_tpl->set('qst_ok',edit_qst_cfg($qid, $_user['mid'], $titre, parse($descr), $qst_statut, $race, $qst_com, $req_quest, $req_pts, $req_pts_armee,  $req_btc,  $req_src,  $btc_id_1, $btc_nb_1, $btc_id_2, $btc_nb_2, $unt_id_1, $unt_nb_1, $unt_id_2, $unt_nb_2, $res_id, $res_nb, $src_id, $rec_res_id1, $rec_res_val1, $rec_res_id2, $rec_res_val2, $rec_xp));

        }
        else{ // new
            $_tpl->set('qst_qid',0);

            if($titre && $descr)
                $_tpl->set('qst_ok',add_qst_cfg($_user['mid'], $titre, parse($descr), $qst_statut, $race, $qst_com, $req_quest, $req_pts, $req_pts_armee,  $req_btc,  $req_src,  $btc_id_1, $btc_nb_1, $btc_id_2, $btc_nb_2, $unt_id_1, $unt_nb_1, $unt_id_2, $unt_nb_2,  $res_id, $res_nb, $src_id, $rec_res_id1, $rec_res_val1, $rec_res_id2, $rec_res_val2, $rec_xp));
            else {
                
                $qst_array = get_qst_cfg( 0, "all");
            if($qst_array)
               // $qst_array = $qst_array[0];
            $_tpl->set('qst_array',$qst_array);
                else $_tpl->set('qst_array',false);
                
                $_tpl->set('pst_titre',htmlspecialchars($titre));
                $_tpl->set('pst_msg',htmlspecialchars($descr));
                $_tpl->set('qst_statut', QST_CFG_OFF);
                $_tpl->set('raceid', $race);
                $_tpl->set('qst_com', 0);
                
                $_tpl->set('req_qid', );
                $_tpl->set('req_pts', );
                $_tpl->set('req_pts_armee', );
                $_tpl->set('req_btc', );
                $_tpl->set('req_src', );
                                
                $_tpl->set('btc_id_1', );
                $_tpl->set('btc_nb_1', 0);
                $_tpl->set('btc_id_2', );
                $_tpl->set('btc_nb_2', 0);
                $_tpl->set('unt_id_1', );
                $_tpl->set('unt_nb_1', 0);
                $_tpl->set('unt_id_2', );
                $_tpl->set('unt_nb_2', 0);
                $_tpl->set('res_id', );
                $_tpl->set('res_nb', 0);
                $_tpl->set('src_id', );
                                
                $_tpl->set('rec_1', );
                $_tpl->set('rec_val1',0);
                $_tpl->set('rec_2', );
                $_tpl->set('rec_val2',0);
                $_tpl->set('rec_xp',0);	
            }	

            if($titre || $descr) {
                $qst_array = get_qst_cfg( 0, "all");
            if($qst_array)
               // $qst_array = $qst_array[0];
            $_tpl->set('qst_array',$qst_array);
                else $_tpl->set('qst_array',false);
                $_tpl->set('pst_titre',$titre);
                $_tpl->set('pst_msg',$descr);
                $_tpl->set('qst_statut', $qst_statut);
                $_tpl->set('raceid', $race);                
                $_tpl->set('qst_com', $qst_com);
                
                $_tpl->set('req_qid', $req_quest);
                $_tpl->set('req_pts', $req_pts);
                $_tpl->set('req_pts_armee', $req_pts_armee);
                $_tpl->set('req_btc', $req_btc);
                $_tpl->set('req_src', $req_src);
                                
                $_tpl->set('btc_id_1', $btc_id_1);
                $_tpl->set('btc_nb_1', $btc_nb_1);
                $_tpl->set('btc_id_2', $btc_id_2);
                $_tpl->set('btc_nb_2', $btc_nb_2);
                $_tpl->set('unt_id_1', $unt_id_1);
                $_tpl->set('unt_nb_1', $unt_nb_1);
                $_tpl->set('unt_id_2', $unt_id_2);
                $_tpl->set('unt_nb_2', $unt_nb_2);
                $_tpl->set('res_id', $res_id);
                $_tpl->set('res_nb', $res_nb);
                $_tpl->set('src_id', $src_id);
                                
                $_tpl->set('rec_1',$rec_res_id1);
                $_tpl->set('rec_val1',$rec_res_val1);
                $_tpl->set('rec_2',$rec_res_id2);
                $_tpl->set('rec_val2',$rec_res_val2);
                $_tpl->set('rec_xp',$rec_xp);
                
            }

        }
        break;
            
    case "view_conf":
        //Voir
        if($qid) {
            $qst_array = get_qst_cfg($qid, "all");
            if($qst_array)
                $qst_array = $qst_array[0];
            
            $_tpl->set('qst_array',$qst_array);
        } else
            $_tpl->set('qst_bad_qid',true);

        break;
            
    case "conf":
         //filtre conf
            $selrace = request("selrace", "string", "get");
            
        $qst_array = get_qst_cfg($qid, $selrace);
        $_tpl->set('qst_array',$qst_array);
        
        $_tpl->set('selrace',$selrace);
        break;
            
    case "view":  
        if($qid) {
            
            $qst_array = get_qst($_user['mid'], $qid);
            if($qst_array){
                $qst_array = $qst_array[0];
                $_tpl->set('qst_array',$qst_array);
                
                if ($qst_array['qst_mbr_statut'] == QST_MBR_NEW) {
                    $new = array('statut' => QST_MBR_START );
                    edit_qst($_user['mid'], $qid, $new);
                    $_tpl->set('qst_valid',QST_MBR_START);
                }

                elseif ($qst_array['qst_mbr_statut'] == QST_MBR_START) {
                    $_tpl->set('qst_valid',QST_MBR_START);
                }
                elseif ($qst_array['qst_mbr_statut'] == QST_MBR_END) {
                    $_tpl->set('qst_valid',QST_MBR_END);
                }
                else $_tpl->set('qst_valid',QST_MBR_VALID);
            }
        } 
        else
            $_tpl->set('qst_bad_qid',true);

        break; 
            
    case "valid":  
        if($qid) {  
            $qst_array = get_qst_cfg($qid, "all");
            if($qst_array) {
                $qst_array = $qst_array[0]; 
                
                $validRec1 = $validRec2 = $validRecxp = false;
                
                    if ($qst_array['qst_rec_res1'] > 0) {                        
                        $validRec1 = (add_qst_rec($_user['mid'], $qst_array['qst_rec_res1'], $qst_array['qst_rec_val1']) != 1);
                    }
                    if ($qst_array['qst_rec_res2'] > 0 && !$validRec1) {
                        $validRec2 = (add_qst_rec($_user['mid'], $qst_array['qst_rec_res2'], $qst_array['qst_rec_val2']) != 1);
                    }
                    if ($qst_array['qst_rec_xp'] > 0 && !$validRec1 && !$validRec2) {
                        $new = array('xp' => $qst_array['qst_rec_xp'] );
                        $validRecxp = (edit_mbr($_user['mid'], $new) != 1);
                    }
                            
                if ($validRec1 || $validRec2 || $validRecxp){
                    add_qst_rec($_user['mid'], -$qst_array['qst_rec_res1'], $qst_array['qst_rec_val1']);                                    
                    $_tpl->set('rec_ok',false); 
                }
                else {
                    $new = array('statut' => QST_MBR_VALID );
                    edit_qst($_user['mid'], $qid, $new);
                    $_tpl->set('rec_ok',true); 
                }
                
            }
        } 
        else
            $_tpl->set('qst_bad_qid',true);
        break;   
            
    default:     
               
        $qst_array = get_qst($_user['mid'], $qid);
        $_tpl->set('qst_array',$qst_array);
        
        break;
    }


	
}
?>