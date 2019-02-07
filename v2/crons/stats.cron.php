<?php

$log_stats = "Statistiques";

function glob_stats() {
	global $_sql, $_m, $_h;
	
	if($_m != 0) { /* Nombre de connectés */
		$sql = "INSERT INTO ".$_sql->prebdd."con (con_nb) ";
		$sql.= "SELECT COUNT(DISTINCT ses_mid) FROM ".$_sql->prebdd."ses ";
		$_sql->query($sql);
	}
	
	if($_h == 1) { /* Statistiques */
		$sql = "INSERT INTO ".$_sql->prebdd."stq VALUES (NOW(),";
		$sql.= "(SELECT COUNT(*) FROM ".$_sql->prebdd."mbr WHERE mbr_etat = ".MBR_ETAT_OK."),";
		$sql.= "(SELECT COUNT(*) FROM ".$_sql->prebdd."mbr WHERE mbr_etat = ".MBR_ETAT_ZZZ."),";
		$sql.= "((SELECT SUM(con_nb) FROM ".$_sql->prebdd."con) / (SELECT COUNT(*) FROM ".$_sql->prebdd."con)));";
		$_sql->query($sql);
	}	
	
	if($_h == 2) { //on ne garde que 3 mois pour ne pas surcharger la db
	$sql = "DELETE FROM ".$_sql->prebdd."con WHERE con_date < (NOW() - INTERVAL 90 DAY)";
		$_sql->query($sql);
	}
}
?>