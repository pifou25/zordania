<?php

$log_mbr = "Membres";

function glob_mbr() {
	global $_c;

        MsgEnv::whereRaw('menv_date < (NOW() - INTERVAL ? DAY)', MSG_DEL_OLD)->delete();
	// supprimer les msg reçu sauf ceux signalés
        MsgRec::whereRaw('mrec_date < (NOW() - INTERVAL ? DAY)', MSG_DEL_OLD)->where('msg_sign', 0)->delete();

        Hst::whereRaw('histo_date < (NOW() - INTERVAL ? DAY)', HISTO_DEL_OLD)->delete();

        Mbr::whereRaw('mbr_ldate < (NOW() - INTERVAL ? DAY)', ZZZ_TRIGGER)->where('mbr_mid', '<>', 1)
                ->where('mbr_etat', MBR_ETAT_OK)->update(['mbr_etat' => MBR_ETAT_ZZZ]);

	/* Chef de région et parrains */
	if($_c == "24h") {
		/* reinit Champions et Champion des Champions */
            Mbr::whereIn('mbr_gid', [GRP_CHEF_REG ,GRP_CHAMP_REG])
                    ->update(['mbr_gid' => GRP_JOUEUR]);

		$sql = "SELECT MAX( mbr_points ) as mbr_points, map_region FROM ".DB::getTablePrefix()."mbr ";
		$sql.= "JOIN ".DB::getTablePrefix()."map ON map_cid = mbr_mapcid ";
		$sql.= "WHERE mbr_gid = ".GRP_JOUEUR." ";
		$sql.= " AND mbr_etat = " . MBR_ETAT_OK;
		$sql.= " GROUP BY map_region ";
		$points = DB::sel($sql);

		foreach($points as $value) {
			$pts = $value['mbr_points'];
			$reg = $value['map_region'];

			$sql = "SELECT mbr_mid FROM ".DB::getTablePrefix()."mbr ";
			$sql.= "JOIN ".DB::getTablePrefix()."map ON map_cid = mbr_mapcid ";
			$sql.= "WHERE mbr_points = $pts AND map_region = $reg AND mbr_gid = ".GRP_JOUEUR;
			$sql.= " AND mbr_etat = " . MBR_ETAT_OK;
			$mids = DB::sel($sql);

			foreach($mids as $value) {
				$mid = $value['mbr_mid'];

				$new = array();
				$new['gid'] = GRP_CHEF_REG;
				Mbr::edit($mid, $new);
			}
		}

		/* Champion des Champions */
		$sql = "SELECT mbr_mid FROM ".DB::getTablePrefix()."mbr 
			WHERE mbr_points=(SELECT MAX(mbr_points) FROM ".DB::getTablePrefix()."mbr WHERE mbr_gid = ".GRP_CHEF_REG.") ";
		$champ = DB::sel($sql);
		if(!empty($champ)){ // si existe un champion dans cette province
			$new['gid'] = GRP_CHAMP_REG;
			Mbr::edit($champ[0]['mbr_mid'], $new);
		}

		/* effacer puis remettre les récompenses liées au parrainage */
		$sql = "SELECT mbr_parrain, COUNT(*) as mbr_filleuls FROM ".DB::getTablePrefix()."mbr ";
		$sql.= "WHERE mbr_parrain <> 0 ";
		$sql.= " GROUP BY mbr_parrain HAVING mbr_filleuls >= ".PARRAIN_GRD1;
		$parrains = DB::sel($sql);

                Rec::whereIn('rec_type', [7, 8, 9])->delete();
		foreach ($parrains as $value)
		{
			$nb = $value['mbr_filleuls'];
			$mid = $value['mbr_parrain'];
			if ($nb >= PARRAIN_GRD3)
				$type = 9;
			else if ($nb >= PARRAIN_GRD2)
				$type = 8;
			else if ($nb >= PARRAIN_GRD1)
				$type = 7;
			else
				continue;
			Rec::add($mid, $type);
		}

	}
}

function mbr_mbr( &$user) {
	// soigner le heros s'il est au village
	if (isset($user['hro']) && $user['hro']['leg_cid'] == $user['mbr_mapcid']) {
		$vie_max = Config::get($user['mbr_race'], 'unt', $user['hro']['hro_type'], 'vie');
		// s'il n'est pas mort et qu'il n'est pas au max
		if ($user['hro']['hro_vie'] < $vie_max && $user['hro']['hro_vie'] > 0) {
			$addvie = 5; // soigner le heros de +5PDV

			if ($user['hro']['hro_bonus'] == CP_REGENERATION || $user['hro']['hro_bonus'] == CP_REGENERATION_ORC )
			{ // compétence régénération
				$bonus = Config::get($user['mbr_race'], 'comp', $user['hro']['hro_bonus'], 'bonus');
				$addvie = $addvie * (1 + $bonus / 100);
			}

			$vie = $user['hro']['hro_vie'] + $addvie; // soigner le heros de +5PDV
			if ($vie > $vie_max) $vie = $vie_max;
                        Hro::where('hro_lid', $user['hro']['hro_lid'])->update(['hro_vie' => $vie]);
		}
	}
}
