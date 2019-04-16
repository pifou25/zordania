<?php

$log_aly = 'Alliances';

function glob_aly() {
	// vider les msg de la shootbox
        AlShoot::whereRaw('shoot_date < (NOW() - INTERVAL ? DAY)', MSG_DEL_OLD)->delete();
	// vider l'historique du grenier
        AlResLog::whereRaw('arlog_date < (NOW() - INTERVAL ? DAY)', HISTO_DEL_LOG_ALLY)->delete();
	// maj du statut 'noob'
        AlMbr::where('ambr_etat', ALL_ETAT_NOOB)->whereRaw('ambr_date < (NOW() - INTERVAL ? DAY)', ALL_NOOB_TIME)
                ->update(['ambr_etat' => ALL_ETAT_NOP]);
	// calcul des points de l'alliance
	$sql = 'UPDATE '.DB::getTablePrefix().'al SET al_points = (';
	$sql.= 'SELECT COALESCE(SUM(mbr_points), 0) FROM '.DB::getTablePrefix().'mbr ';
	$sql.= 'JOIN '.DB::getTablePrefix().'al_mbr ON mbr_mid = ambr_mid ';
	$sql.= 'WHERE ambr_aid = al_aid AND mbr_etat = '.MBR_ETAT_OK;
	$sql.= ' AND ambr_etat != '.ALL_ETAT_DEM.'  GROUP BY ambr_aid)'
                . ' WHERE al_aid in (SELECT ambr_aid FROM '.DB::getTablePrefix().'mbr'
                . ' JOIN '.DB::getTablePrefix().'al_mbr ON mbr_mid = ambr_mid'
                . ' WHERE ambr_aid = al_aid AND mbr_etat = 1 AND ambr_etat != 1)';
	DB::update($sql);
	// MAJ du nombre de membres dans l'ally
	$sql = 'UPDATE '.DB::getTablePrefix().'al SET al_nb_mbr = 
		(SELECT count(ambr_mid) FROM '.DB::getTablePrefix().'al_mbr WHERE ambr_aid = al_aid AND ambr_etat > 1)';
	DB::update($sql);

}
