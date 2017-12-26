<?php
/*
 * RAZ du jeu : supprime tout ce qui est lié au gameplay
 * laisse forums / mp / notes ...
 */ 

require_once("../lib/divers.lib.php");
require_once("../conf/conf.inc.php");

$_sql = new mysqliext(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_BASE);
$_sql->set_prebdd(MYSQL_PREBDD);
$_sql->set_debug(SITE_DEBUG);

// liste des tables à supprimer
$drop = array( 'al_res', 'al_res_log', 'atq', 'atq_mbr', 'btc',
	'diplo', 'diplo_shoot', 'hero', 'histo', 'leg', 'leg_res', 'mch', 'mch_sem', 'reg', 'res',
	'res_todo', 'src', 'src_todo', 'trn', 'unt', 'unt_todo', 'vld');
foreach($drop as $tbl)
{
	$sql = 'TRUNCATE TABLE '. $_sql->prebdd. $tbl;
	$_sql->query($sql);
	echo "\ndelete $tbl..." . $_sql->affected_rows();
}
// update sur map, mbr
$sql = 'UPDATE '. $_sql->prebdd.'map SET map_type = '.MAP_LIBRE.' WHERE map_type = '.MAP_VILLAGE;
$_sql->query($sql);
echo "\nupdate map ... raz villages ..." . $_sql->affected_rows();

$sql = 'UPDATE '. $_sql->prebdd.'mbr SET mbr_etat='.MBR_ETAT_INI.', mbr_race=0, mbr_mapcid=0, 
  mbr_place=0, mbr_population=0, mbr_points=0, mbr_pts_armee=0, mbr_atq_nb=0, mbr_pseudo = ""
  WHERE mbr_etat IN('.MBR_ETAT_OK.','.MBR_ETAT_ZZZ.') AND mbr_mid <> ' . MBR_WELC;
$_sql->query($sql);
echo "\nupdate mbr ... reinit ..." . $_sql->affected_rows();

echo"\n ... RAZ finite\n";

/* tables conservées :
 * bon, con, frm*, grp, log, mbr_log, mbr_old, mch_cours, msg, msg_env, msg_rec, ntes, rec
 * sdg*, sign, stq, surv
 * et aussi al et al_mbr et al_shoot pour les alliances
 */


?>
