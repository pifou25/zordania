<?php
$log_qst = "Quetes";

function glob_qst() {
	global $_tpl,$_sql;


/*recup des mid*/
$sql = "SELECT mbr_mid, mbr_race, mbr_pseudo, mbr_points, mbr_pts_armee
        FROM ".$_sql->prebdd."mbr
        WHERE mbr_gid >= ".GRP_JOUEUR." AND mbr_etat = ".MBR_ETAT_OK;

$mid_array = $_sql->make_array($sql);

// Vérifier que $mid_array contient des données avant de boucler
if (!empty($mid_array)) {
    foreach ($mid_array as $row) {
        $mbr_mid = $row['mbr_mid'] ?? null; 
        $mbr_race = $row['mbr_race'];
        $mbr_pseudo = $row['mbr_pseudo'];  
        
        if ($mbr_mid !== null) {
            // recup des quêtes du joueur
            $qst_array = get_qst($mbr_mid, 0); 
            if (!empty($qst_array)) { // Vérifier que $qst_array contient des quêtes
                foreach ($qst_array as $qst_row) {//action pour chaque quete

                    $qid = $qst_row['qst_mbr_qid']; // Récupérer l'ID de la quête
                    
                    //On s'occupe des quêtes en cours
                    if($qst_row['qst_mbr_statut'] < QST_MBR_END) {
                        /*-avancements-*/
                        //avancement des btc
                        if($qst_row['qst_btc_nb1'] > 0 ){
                            $get_btc = get_nb_btc_done($mbr_mid, array( $qst_row['qst_btc_id1']));
                            if (!empty($get_btc)) {
                                // Vérifier si le type de BTC existe
                                $btc_type = $qst_row['qst_btc_id1'];
                                    $btc_nb = $get_btc[$btc_type]['btc_nb']; // Récupérer btc_nb pour ce type

                                    $new = array('btc1' => $btc_nb);
                                    edit_qst($mbr_mid, $qid, $new);
                                
                                    $etat_btc_1 = $new['btc1'];
                            }
                        }                    
                        if($qst_row['qst_btc_nb2'] > 0 ){
                            $get_btc = get_nb_btc_done($mbr_mid, array( $qst_row['qst_btc_id2']));
                            if (!empty($get_btc)) {
                                // Vérifier si le type de BTC existe
                                $btc_type = $qst_row['qst_btc_id2'];
                                    $btc_nb = $get_btc[$btc_type]['btc_nb']; // Récupérer btc_nb pour ce type

                                    $new = array('btc2' => $btc_nb);
                                    edit_qst($mbr_mid, $qid, $new);
                                    
                                    $etat_btc_2 = $new['btc2'];
                            }
                        }

                        //avancement des unités
                        if($qst_row['qst_unt_nb1'] > 0 ){
                            $get_unt = get_unt_total($mbr_mid, array($qst_row['qst_unt_id1']));
                            if($get_unt){
                                $get_unt = $get_unt[0];

                                $new = array('unt1' => $get_unt['unt_sum'] );
                                edit_qst($mbr_mid, $qid, $new);
                                
                                $etat_unt_1 = $new['unt1'];
                            }
                        }                    
                        if($qst_row['qst_unt_nb2'] > 0 ){
                            $get_unt = get_unt_total($mbr_mid, array($qst_row['qst_unt_id2']));
                            if($get_unt){
                                $get_unt = $get_unt[0];

                                $new = array('unt2' => $get_unt['unt_sum'] );
                                edit_qst($mbr_mid, $qid, $new);
                                
                                $etat_unt_2 = $new['unt2'];
                            }
                        }

                        //avancement des res       
                        if($qst_row['qst_res_nb'] > 0 ){
                            $get_res = get_res_done($mbr_mid, array($qst_row['qst_res_id']), 0, 0);
                            if(!empty($get_res)){
                                $get_res = $get_res[0];

                                $new = array('res' => $get_res['res_type'.$qst_row['qst_res_id']] );
                                edit_qst($mbr_mid, $qid, $new);
                                
                                $etat_res = $new['res'];
                            } 
                        }

                        //avancement des src
                        if($qst_row['qst_src_id'] > 0 ){
                            if(get_src_done($mbr_mid, array($qst_row['qst_src_id']))){

                                $new = array('src' => 1 );
                                edit_qst($mbr_mid, $qid, $new);
                                
                                $etat_src = $new['src'];
                            }    
                        }

                        /*- est-ce que tout est validé -*/
                        $btcValid1  = valid_qst($qst_row['qst_btc_nb1'], $etat_btc_1); 
                        $btcValid2 = valid_qst($qst_row['qst_btc_nb2'], $etat_btc_2); 
                        $untValid1  = valid_qst($qst_row['qst_unt_nb1'], $etat_unt_1); 
                        $untValid2  = valid_qst($qst_row['qst_unt_nb2'], $etat_unt_2); 
                        $resValid   = valid_qst($qst_row['qst_res_nb'], $etat_res); 

                        $srcValid = ($qst_row['qst_src_id'] == 0 || $etat_src >= 1);

                        if ($btcValid1 && $btcValid2 && $untValid1 && $untValid2 && $resValid && $srcValid) {

                            $new = array('statut' => QST_MBR_END );
                            edit_qst($mbr_mid, $qid, $new); //mettre la quête comme terminée
                        }

                    }           
                }
            }
            
            // ajout des nouvelles quêtes pour le joueur
            $qst_array = get_qst_cfg(0, 0); //recup des quêtes de sa race
            if (!empty($qst_array)) { // Vérifier que $qst_array contient des quêtes
                foreach ($qst_array as $qst_row) {//action pour chaque quete
                if ( $qst_row['qst_common'] == 1 || $mbr_race == $qst_row['qst_race']){
                    $qid = $qst_row['qst_id']; // Récupérer l'ID de la quête
                    
                    //la quête n'existe pas chez le jouer?
                    if (!get_qst($mbr_mid, $qid)){
                        
                        //quête requise ok?
                        if ($qst_row['qst_req_qid'] > 0){
                            $qstValid = false;
                            
                            $qst_mbr = get_qst($mbr_mid, $qst_row['qst_req_qid']);
                            //on vérifie qu'il a déjà reçu la quête
                            if ($qst_mbr){
                                $qst_mbr = $qst_mbr[0];      
                                //est-elle finie?
                                $qstValid = ($qst_mbr['qst_mbr_statut'] >= QST_MBR_END);
                            }
                                
                        }
                        else $qstValid = true; 
                        
                        //pts et pts armée?                        
                        $mbr_pts = $row['mbr_points']; 
                        $mbr_pts_armee = $row['mbr_pts_armee']; 
                                                
                        $ptsValid       = valid_qst($qst_row['qst_req_pts'], $mbr_pts);                         
                        $ptsarmeeValid  = valid_qst($qst_row['qst_req_pts_armee'], $mbr_pts_armee); 
                        
                        //btc requis ok?
                        if ($qst_row['qst_req_btc'] > 0) {
                            $btcValid = false; 
                            $get_btc = get_nb_btc_done($mbr_mid, array($qst_row['qst_req_btc']));

                            if (!empty($get_btc)) {
                                $btc_type = $qst_row['qst_req_btc'];
                                // Vérification que la quantité BTC est > 0 et la donnée existe
                                $btcValid = isset($get_btc[$btc_type]) && $get_btc[$btc_type]['btc_nb'] > 0;
                            }
                        } 
                        else $btcValid = true; 
                        
                        //src requis ok?
                        if ($qst_row['qst_req_src'] > 0) {
                            $srcValid = false;

                            $get_src = get_src_done($mbr_mid, array($qst_row['qst_req_src']));

                            if (!empty($get_src)) {
                                $srcValid = true;
                            }
                        } 
                        else $srcValid = true;  
                        
                        //quête en ligne
                        $isOnline = ($qst_row['qst_statut'] == 1);
                        
                        
                        //on insère
                        if ($qstValid && $ptsValid && $ptsarmeeValid && $btcValid && $srcValid && $isOnline){
                            add_qst($mbr_mid, $qid);
                            /* envoyer la notif */                            
                            
                                $_tpl->set('pseudo',$mbr_pseudo);
                                $_tpl->set('qid',$qst_row['qst_id']);
                                $_tpl->set('titre',$qst_row['qst_title']);
                            $msg = nl2br($_tpl->get("modules/qst/new.txt.tpl",1));
                            $titre = "[Nouvelle quête] ".$qst_row['qst_title'];
                            send_msg(MBR_WELC, $mbr_mid ,$titre, $msg,true);
                            
                        }
                        
                    }
                }
                }
            }
        }
    }
} else {
    echo "No members found matching the criteria.";
}
}
?>