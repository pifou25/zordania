<?php

$log_com = "Commerce";
require_once(SITE_DIR . "lib/mch.lib.php");

function glob_com() {
	global $_sql, $_h, $_c;

	// tt les 6h et uniquement à 6h du matin
	if($_h == 6 && $_c == '6h') {
		$sql = "INSERT INTO ".$_sql->prebdd."mch_sem (msem_res,msem_date,msem_cours) ";
		$sql.= "SELECT mcours_res, NOW(), mcours_cours FROM ".$_sql->prebdd."mch_cours ";
		$_sql->query($sql);

		$sql = "UPDATE ".$_sql->prebdd."mch_sem SET  msem_cours = (";
		$sql.= "SELECT ROUND(AVG(mch_prix/mch_nb),2) FROM ".$_sql->prebdd."mch WHERE mch_etat = ".COM_ETAT_ACH." AND msem_res = mch_type";
		$sql.= ") WHERE msem_date = CURDATE() AND msem_res IN (SELECT DISTINCT mch_type FROM ".$_sql->prebdd."mch WHERE mch_etat = ".COM_ETAT_ACH.")";
		$_sql->query($sql);

		//Virer les vieilles ventes qui ne seront plus prises en compte pour le calcul des cours
		$sql = "DELETE FROM ".$_sql->prebdd."mch ";
		$sql.= "WHERE mch_etat = ".COM_ETAT_ACH." AND mch_time < (NOW() - INTERVAL ".MCH_OLD. " DAY)";
		$_sql->query($sql);

		//Moyenne de la semaine /!\
		$sql = "TRUNCATE TABLE ".$_sql->prebdd."mch_cours";
		$_sql->query($sql);

		$sql = "INSERT INTO ".$_sql->prebdd."mch_cours (mcours_res,mcours_cours) ";
		$sql.= " SELECT msem_res,ROUND(AVG(msem_cours),2)";
		$sql.= " FROM ".$_sql->prebdd."mch_sem WHERE  msem_date > (NOW() - INTERVAL 3 DAY)";
		$sql.= " GROUP BY msem_res ";
		$_sql->query($sql);
	}


	//if($_c == '1h') { // tt les heures
		/*
		 * obtenir les ventes en cours pour le PNJ vendeur = MBR_WELC
		 * ajouter N ventes des matières premières au taux nominal COM_TAUX_MAX
		 */
		// ressources primaires à vendre = config
		//config
            $res_array = array(2, 3, 4, 5, 6, 8, 9);
            $nb_ventes_max = 3; // Nb max de ventes par ressource/quantité
            //3 niveaux de quantité spécifique
            $res_nb_niv = [
                4 => [100, 500, 1000],    // nourriture
                8 => [5, 20, 50],          // acier
                9 => [5, 10, 20],          // mithril
            ];
            // 3 niveaux de quantité par défaut
            $res_nb_def = [10, 100, 200];

        // Récupération des ventes existantes
        $vente_array = get_mch_by_mid(MBR_WELC);
        $vente_nb = array(); 

        foreach ($vente_array as $row) {
            $type = $row['mch_type'];
            $nb = $row['mch_nb']; // On récupére le nombre de vente
            if (!isset($vente_nb[$type][$nb])) {
                $vente_nb[$type][$nb] = 0;
            }
            $vente_nb[$type][$nb]++;
        }

        // Récupération des cours des ressources
        $cours = mch_get_cours();
        $cours = index_array($cours, 'mcours_res');

        //insert
        foreach ($res_array as $res_type) {
            // si niveau spécifique, si non niveau par défaut
            $res_nb_conf = isset($res_nb_niv[$res_type]) ? $res_nb_niv[$res_type] : $res_nb_def;

            foreach ($res_nb_conf as $res_nb) {
                if (!isset($vente_nb[$res_type][$res_nb])) {
                    $vente_nb[$res_type][$res_nb] = 0;
                }

                // Créer des ventes jusqu'à atteindre le max pour cette quantité
                for ($i = $vente_nb[$res_type][$res_nb]; $i < $nb_ventes_max; $i++) {
                    mch_vente(
                        MBR_WELC,
                        $res_type,
                        $res_nb,
                        $res_nb * $cours[$res_type]['mcours_cours'] * COM_TAUX_MAX
                    );
                }
            }
        }
	//} // fin de la vente auto pour le PNJ


	//Faire avancer les ventes dans le marché
	$time = (MCH_ACCEPT + rand(-1,1)) * ZORD_SPEED;  /* temps en minute */
	$max = MCH_MAX * ZORD_SPEED;

	$sql = "UPDATE ".$_sql->prebdd."mch SET mch_etat = ".COM_ETAT_OK.", mch_time = (NOW() + INTERVAL $max MINUTE)";
	$sql.= " WHERE mch_etat = ".COM_ETAT_ATT." AND ";
	$sql.= "NOW() > (mch_time + INTERVAL $time MINUTE)";
	$_sql->query($sql);

	//Virer les ventes en cours non vendues
	$sql = "DELETE FROM ".$_sql->prebdd."mch ";
	$sql.= "WHERE mch_etat = ".COM_ETAT_OK." AND mch_time < NOW()";
	$_sql->query($sql);
}

?>
