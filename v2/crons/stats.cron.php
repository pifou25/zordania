<?php

$log_stats = "Statistiques";

function glob_stats() {
	global $_m, $_h;
	
	if($_m != 0) { /* Nombre de connectés */
		$sql = "INSERT INTO ".DB::getTablePrefix()."con (con_nb) ";
		$sql.= "SELECT COUNT(DISTINCT ses_mid) FROM ".DB::getTablePrefix()."ses ";
		DB::insert($sql);
	}
	
	if($_h == 0) { /* Statistiques */
		$sql = "INSERT INTO ".DB::getTablePrefix()."stq VALUES (NOW(),";
		$sql.= "(SELECT COUNT(*) FROM ".DB::getTablePrefix()."mbr WHERE mbr_etat = ".MBR_ETAT_OK."),";
		$sql.= "(SELECT COUNT(*) FROM ".DB::getTablePrefix()."mbr WHERE mbr_etat = ".MBR_ETAT_ZZZ."),";
		$sql.= "((SELECT SUM(con_nb) FROM ".DB::getTablePrefix()."con) / (SELECT COUNT(*) FROM ".DB::getTablePrefix()."con)));";
		DB::insert($sql);
	}	
	
	if($_m == 0) { //on ne garde que 3 mois pour ne pas surcharger la db
            $sql = "DELETE FROM ".DB::getTablePrefix()."con WHERE con_date < (NOW() - INTERVAL 90 DAY)";
            DB::delete($sql);
	}
}
