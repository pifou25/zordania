<?php

$log_leg = "Légions";

function glob_leg() {
    global $_histo, $hro_list, $leg_move_array, $unt_move_list;

    /* SELECT de toutes les légions sauf village et batiments */
    $sql = "SELECT leg_id, leg_mid, leg_cid, SUM(unt_nb) as unt_nb, IFNULL(lres_nb,0) as lres_nb, 
		mbr_mapcid, mbr_race, IFNULL(hro_bonus,0) AS hro_bonus, leg_name, leg_dest ";
    $sql .= "FROM " . DB::getTablePrefix() . "leg ";
    $sql .= "JOIN " . DB::getTablePrefix() . "mbr ON mbr_mid = leg_mid AND mbr_etat = " . MBR_ETAT_OK . " ";
    $sql .= "LEFT JOIN " . DB::getTablePrefix() . "leg_res ON leg_id = lres_lid AND lres_type = " . GAME_RES_BOUF . " ";
    $sql .= "LEFT JOIN " . DB::getTablePrefix() . "hero ON leg_id = hro_lid ";
    $sql .= "LEFT JOIN " . DB::getTablePrefix() . "unt ON leg_id = unt_lid ";
    $sql .= "WHERE leg_etat NOT IN (" . LEG_ETAT_VLG . "," . LEG_ETAT_BTC . ") ";
    $sql .= "GROUP BY leg_id, leg_mid, leg_cid, IFNULL(lres_nb,0),"
            . " mbr_mapcid, mbr_race, IFNULL(hro_bonus,0), leg_name ";

    $leg_array = DB::sel($sql);

    foreach ($leg_array as $value) { /* nourriture des légions sauf village & bâtiments */
        $bouf = $value['lres_nb'];
        $nb = $value['unt_nb'];
        $lid = $value['leg_id'];
        $mid = $value['leg_mid'];

        if ($nb == 0) {
            // Si la légion est vide on supprime le butin et on place la légion chez le propriétaire
            if($value['leg_cid'] != $value['mbr_mapcid']){
                LegRes::where('lres_lid', $lid)->delete();
                $_histo->add($mid, $mid, HISTO_LEG_VIDE_BACK, array("leg_name" => $value['leg_name']));
            }

            Leg::where('lres_lid', $lid)->update([
                'leg_cid' => $value['mbr_mapcid'],
                'leg_dest' => 0,
                'leg_vit' => 0,
                'leg_stop' => DB::raw('NOW()'),
                'leg_etat' => LEG_ETAT_GRN,
            ]);

        } else if ($nb != 0) {
            /* vérifier la  compétence privation */
            if ($value['hro_bonus'] == CP_SURVIVANT)
                continue; /* légion suivante */

            /* Si la légion est au village, on va piquer un peu de bouffe là bas */
            if ($bouf < $nb && $value['leg_cid'] == $value['mbr_mapcid']) {
                $tmp = Res::get($mid, array(GAME_RES_BOUF));
                $boufvlg = $tmp[GAME_RES_BOUF];
                $diff = $nb - $bouf;
                $boufvlg = min($boufvlg, $diff);
                Res::mod($mid, array(GAME_RES_BOUF => $boufvlg), -1);
                $bouf += $boufvlg;
            }

            if ($bouf < $nb) { /* On tue des gens - sauf des héros */
                $sql = "SELECT unt_type, unt_nb, unt_rang, leg_name FROM " . DB::getTablePrefix() . "unt ";
                $sql .= "JOIN " . DB::getTablePrefix() . "leg ON leg_id = unt_lid AND unt_nb > 0 ";
                $sql .= "WHERE leg_id = $lid ";
                if (isset($hro_list[$value['mbr_race']]))
                    $sql .= 'AND unt_type NOT IN (' . implode(',', $hro_list[$value['mbr_race']]) . ') ';

                $sql .= "ORDER BY RAND() LIMIT 1";
                $unt_array = DB::sel($sql);

                if ($unt_array) {
                    $unt_array = $unt_array[0];
                    $rang = $unt_array['unt_rang'];
                    $type = $unt_array['unt_type'];
                    $name = $unt_array['leg_name'];

                    if (($nb - $bouf) > $unt_array['unt_nb'])
                    // On évite de tuer une légion en 2 tours ... 
                        $nb = rand(1, round($unt_array['unt_nb'] / 3));
                    else
                        $nb = rand(1, $nb - $bouf);

                    if ($nb < $unt_array['unt_nb']) {
                        Unt::where('unt_type', $type)->where('unt_lid', $lid)->where('unt_rang', $rang)
                                ->decrement('unt_nb', $nb);
                    } else {
                        Unt::where('unt_type', $type)->where('unt_lid', $lid)->where('unt_rang', $rang)
                                ->delete();
                    }
                    $nb = min($nb, $unt_array['unt_nb']);
                    if ($nb){
                        // recalcul vitesse legion
                        $legion = new legion($lid);
                        $vitesse = $legion->calc_vit();
                        Leg::where('leg_id', $lid)->update(['leg_vit' => $vitesse]);
                        
                        $_histo->add($mid, $mid, HISTO_UNT_BOUFF, ['unt_type' => $type, 'unt_nb' => $nb,
                            'leg_name' => $name, 'leg_vit' => $vitesse]);
                    }
                    
                }
                $nb = $bouf;
            }

            if ($bouf == $nb) {
                LegRes::where('lres_type', GAME_RES_BOUF)->where('lres_lid', $lid)
                        ->update(['lres_nb' => 0]);
            } else {
                LegRes::where('lres_type', GAME_RES_BOUF)->where('lres_lid', $lid)
                        ->decrement('lres_nb', $nb);
            }
        }
    }

    /* légions camping */
    $sql = "SELECT leg_id, leg_mid, leg_dest, leg_cid, leg_vit, leg_etat, leg_name ";
    $sql .= "FROM " . DB::getTablePrefix() . "leg ";
    $sql .= "WHERE leg_etat = " . LEG_ETAT_POS . " AND leg_stop < (NOW() - INTERVAL " . ATQ_LEG_IDLE . " DAY)";

    $leg_array = DB::sel($sql);
    foreach ($leg_array as $value) {
        /* TODO ? traiter les tirs défensifs sur les armées en camping */

        /* rentrer les armées en place depuis trop longtemps : évènement */
        $_histo->add($value['leg_mid'], $value['leg_mid'], HISTO_LEG_IDLE, array("leg_name" => $value['leg_name'])); // évènement
    }
    /* rentrer les légions camping */
    $sql = "UPDATE " . DB::getTablePrefix() . "leg AS leg ";
    $sql .= "JOIN " . DB::getTablePrefix() . "mbr AS mbr ON mbr.mbr_mid = leg.leg_mid ";
    $sql .= "SET leg.leg_dest = mbr.mbr_mapcid, leg_etat = " . LEG_ETAT_ALL . " ";
    $sql .= "WHERE leg.leg_etat = " . LEG_ETAT_POS . " AND leg.leg_stop < (NOW() - INTERVAL " . ATQ_LEG_IDLE . " DAY)";
    DB::update($sql);

    /* Avancer les légions en déplacement */
    $sql = "SELECT leg_id, leg_dest, leg_cid, leg_vit, leg_etat, leg_mid, ";
    $sql .= " a.map_x as leg_x, a.map_y as leg_y, b.map_x as dest_x, b.map_y as dest_y, b.map_type as dest_type ";
    $sql .= "FROM " . DB::getTablePrefix() . "leg ";
    $sql .= "JOIN " . DB::getTablePrefix() . "map as a ON a.map_cid = leg_cid ";
    $sql .= "JOIN " . DB::getTablePrefix() . "map as b ON b.map_cid = leg_dest ";
    $sql .= "JOIN " . DB::getTablePrefix() . "mbr ON mbr_mid = leg_mid AND mbr_etat = " . MBR_ETAT_OK . " ";
    $sql .= "WHERE leg_etat IN (" . LEG_ETAT_DPL . "," . LEG_ETAT_ALL . "," . LEG_ETAT_RET . ") ";

    $leg_array = DB::sel($sql);
    foreach ($leg_array as $value) { /* déplacement des légions 1 par 1 */

        $x1 = $value['leg_x'];
        $y1 = $value['leg_y'];
        $x2 = $value['dest_x'];
        $y2 = $value['dest_y'];
        $lid = $value['leg_id'];
        $diffx = ($x1 < $x2) ? 1 : -1;
        $diffy = ($y1 < $y2) ? 1 : -1;
        $vit = $value['leg_vit'];
        $mid = $value['leg_mid'];
        while ($vit && ($x1 != $x2 || $y1 != $y2)) {
            if ($x1 != $x2)
                $x1 += $diffx;
            if ($y1 != $y2)
                $y1 += $diffy;
            $vit--;
        }
        $sql = "UPDATE " . DB::getTablePrefix() . "leg ";
        $sql .= "SET leg_cid = ";
        $sql .= "(SELECT map_cid FROM " . DB::getTablePrefix() . "map WHERE map_x = $x1 AND map_y = $y1)";
        if ($x1 == $x2 && $y1 == $y2) {// la légion est arrivée à destination!
            $sql .= ", leg_dest = 0, leg_stop = NOW(), leg_etat = ";
            $sql .= ($value['leg_etat'] == LEG_ETAT_DPL) ? LEG_ETAT_POS : LEG_ETAT_GRN;
            /* vérifier si la légion contient l'unité caravane ET si l'emplacement est libre */
            if (isset($leg_move_array[$lid]) && $value['dest_type'] == MAP_LIBRE) {
                $leg = $leg_move_array[$lid];
                /* déplacement du village */
                DB::select('CALL move_member(?, ?)', [$mid, $value['leg_dest']]);
                /* edit = rang => array(type => nb) */
                $edit = $unt_move_list[$leg['mbr_race']];
                foreach ($edit as $rang => $unt)
                    if ($unt == $leg['unt_type']) // en principe toujours vrai (!)
                        $edit[$rang] = array($leg['unt_type'] => $leg['unt_nb']);
                /* tuer la caravane */
                Leg::edit($lid, $edit);
                Mbr::edit($mid, array('population' => Leg::countUnt($mid)));
            }
        }
        $sql .= " WHERE leg_id = $lid";
        DB::update($sql);
    }
}
