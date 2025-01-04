<?php

/*recup des mid*/
$sql = "SELECT mbr_mid
        FROM ".$_sql->prebdd."mbr
        WHERE mbr_gid >= ".GRP_JOUEUR." AND mbr_etat = ".MBR_ETAT_OK;

$mid_array = $_sql->make_array($sql);

// Vérifier que $mid_array contient des données avant de boucler
if (!empty($mid_array)) {
    foreach ($mid_array as $row) {
        $mbr_mid = $row['mbr_mid'] ?? null; 
        $mbr_pts = $row['mbr_points']; 
        $mbr_pts_armee = $row['mbr_pts_armee']; 
        
        if ($mbr_mid !== null) {
            // recup des quêtes du joueur
            $qst_array = get_qst($mbr_mid, 0); 
            if (!empty($qst_array)) { // Vérifier que $qst_array contient des quêtes
                foreach ($qst_array as $qst_row) {//action pour chaque quete
                    
                    $qid = $qst_row['qst_mbr_qid']; // Récupérer l'ID de la quête
                    
                    //récupérer les unités
                    
                    //récupérer les btc
                    
                    //récupérer les src
                    
                    //récupérer les res
                    
                    //mettre à jour les avancement
                    
                    //est-ce que tout est validé
                    $btcValid = ($qst_row['qst_btc_nb1'] == 0 || $qst_row['qst_etat_btc1'] >= $qst_row['qst_btc_nb1']) 
                    && ($qst_row['qst_btc_nb2'] == 0 || $qst_row['qst_etat_btc2'] >= $qst_row['qst_btc_nb2']);

                    $untValid = ($qst_row['qst_unt_nb1'] == 0 || $qst_row['qst_etat_unt1'] >= $qst_row['qst_unt_nb1']) 
                        && ($qst_row['qst_unt_nb2'] == 0 || $qst_row['qst_etat_unt2'] >= $qst_row['qst_unt_nb2']);

                    $resValid = ($qst_row['qst_res_nb'] == 0 || $qst_row['qst_etat_res'] >= $qst_row['qst_res_nb']);

                    $srcValid = ($qst_row['qst_src_id'] == 0 || $qst_row['qst_etat_src'] >= 1);

                    if ($btcValid && $untValid && $resValid && $srcValid) {
                       qst_statut_update($mbr_mid, $qid, QST_MBR_END); //mettre la quête comme terminée
                    }

                    // ajouter les nouvelles quetes si la quête est finie, ou cond; pts, pts_armee, btc, src
                    
                    
                    
                }
            }
            
            
            
            
        }
    }
} else {
    echo "No members found matching the criteria.";
}

?>