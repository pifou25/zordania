<?php
if(!defined("_INDEX_")){ exit; }

require_once("lib/member.lib.php");

$_tpl->set("module_tpl","modules/class/class.tpl");

$type = protect($_act, "uint");
$race = request("race", "uint", "get", 0);
$region = request("region", "uint", "get", 0);
if($race != 0 and (!in_array($race,$_races) or !$_races[$race]))
	$race = 0;
if($type==0)
	$type=1;

$_tpl->set("class_race",$race);
$_tpl->set("class_type",$type);
$_tpl->set("class_region",$region);
$array = Mbr::getNbRace();
foreach($_races as $key => $value)
	if(!$value)
		unset($_races[$key]); /* masquer cette race */
	else if(!isset($array[$key]))
		$array[$key] = 0;

$_tpl->set("class_race_nb", $array);

switch ($type) {
    case 1: //Or
        $req = Res::select('mbr_gid', 'ambr_etat', 'mbr_mapcid', 'mbr_mid', 'mbr_pseudo', 'mbr_race', 'mbr_pts_armee', 'mbr_etat')
                ->selectRaw('res_type1 as res_nb,'
                        . 'IF(ambr_etat= ? , 0, IFNULL(ambr_aid,0)) as al_aid,'
                        . 'IF(ambr_etat= ?, NULL, al_name) as al_name ', [ALL_ETAT_DEM, ALL_ETAT_DEM])
                ->leftJoin('mbr', 'res_mid', 'mbr_mid')
                ->leftJoin('al_mbr', 'mbr_mid', 'ambr_mid')
                ->leftJoin('al', 'al_aid', 'ambr_aid');
        if ($region) {
            $req->join('map', 'mbr_mapcid', 'map_cid');
        }
        $req->where('res_type1', '>', 0)->where('mbr_etat', MBR_ETAT_OK);
        if ($race) {
            $req->where('mbr_race', $race);
        }
        if ($region) {
            $req->where('map_region', $region);
        }
        $req->orderBy('res_type1', 'desc')->take(50);

        break;
    case 2: //Xp (par légion) -- useless
        $req = Leg::select('mbr_gid', 'leg_id', 'leg_xp', 'leg_name', 'mbr_mid', 'mbr_pseudo', 'mbr_race', 'mbr_pts_armee', 'ambr_etat', 'mbr_mapcid')
                ->selectRaw('IF(ambr_etat= ? , 0, IFNULL(ambr_aid,0)) as al_aid,'
                        . 'IF(ambr_etat= ?, NULL, al_name) as al_name ', [ALL_ETAT_DEM, ALL_ETAT_DEM])
                ->leftJoin('mbr', 'leg_mid', 'mbr_mid')
                ->leftJoin('al_mbr', 'mbr_mid', 'ambr_mid')
                ->leftJoin('al', 'al_aid', 'ambr_aid');
        if ($region) {
            $req->join('map', 'mbr_mapcid', 'map_cid');
        }
        $req->where('leg_xp', '>', 0)->where('mbr_etat', MBR_ETAT_OK);
        if ($race) {
            $req->where('mbr_race', $race);
        }
        if ($region) {
            $req->where('map_region', $region);
        }
        $req->orderBy('leg_xp', 'desc')->take(50);

        break;
    case 3: // points
        $req = Mbr::select('mbr_gid', 'ambr_etat', 'mbr_mapcid', 'mbr_mid', 'mbr_pseudo', 'mbr_race',
                'mbr_points', 'mbr_pts_armee', 'mbr_etat')
                ->selectRaw('IF(ambr_etat= ? , 0, IFNULL(ambr_aid,0)) as al_aid,'
                        . 'IF(ambr_etat= ?, NULL, al_name) as al_name ', [ALL_ETAT_DEM, ALL_ETAT_DEM])
                ->leftJoin('al_mbr', 'mbr_mid', 'ambr_mid')
                ->leftJoin('al', 'al_aid', 'ambr_aid');
        if ($region) {
            $req->join('map', 'mbr_mapcid', 'map_cid');
        }
        $req->where('mbr_points', '>', 0)->where('mbr_etat', MBR_ETAT_OK);
        if ($race) {
            $req->where('mbr_race', $race);
        }
        if ($region) {
            $req->where('map_region', $region);
        }
        $req->orderBy('mbr_points', 'desc')->take(50);

        break;
    case 4: // place et population
        $req = Mbr::select('mbr_gid', 'ambr_etat', 'mbr_mapcid', 'mbr_mid', 'mbr_pseudo', 'mbr_race',
                'mbr_points', 'mbr_pts_armee', 'mbr_etat', 'mbr_place','mbr_population')
                ->selectRaw('IF(ambr_etat= ? , 0, IFNULL(ambr_aid,0)) as al_aid,'
                        . 'IF(ambr_etat= ?, NULL, al_name) as al_name ', [ALL_ETAT_DEM, ALL_ETAT_DEM])
                ->leftJoin('al_mbr', 'mbr_mid', 'ambr_mid')
                ->leftJoin('al', 'al_aid', 'ambr_aid');
        if ($region) {
            $req->join('map', 'mbr_mapcid', 'map_cid');
        }
        $req->where('mbr_population', '>', 0)->where('mbr_etat', MBR_ETAT_OK);
        if ($race) {
            $req->where('mbr_race', $race);
        }
        if ($region) {
            $req->where('map_region', $region);
        }
        $req->orderBy('mbr_population', 'desc')->take(50);

        break;
    case 5: // alliances
        $req = Al::select( 'al_aid','al_name','al_nb_mbr','al_mid','mbr_pseudo','al_points','al_open' )
            ->leftJoin('mbr', 'al_mid', 'mbr_mid');
        if ($region) {
            $req->join('map', 'mbr_mapcid', 'map_cid');
        }
        $req->where('al_points', '>', 0);
        if ($race) {
            $req->where('mbr_race', $race);
        }
        if ($region) {
            $req->where('map_region', $region);
        }
        $req->orderBy('al_points', 'desc')->take(50);

        break;
    case 6: // XP héros
        $req = Hro::select('hro_id', 'hro_mid', 'hro_nom', 'hro_type', 'hro_lid', 'hro_xp', 'hro_xp_tot', 'hro_vie', 'hro_bonus_from', 'mbr_gid', 'mbr_mid', 'mbr_pseudo', 'mbr_race')
                ->selectRaw(' hro_bonus AS bonus, hro_bonus_to AS bonus_to')
                ->leftJoin('mbr', 'hro_mid', 'mbr_mid');
        if ($region) {
            $req->join('map', 'mbr_mapcid', 'map_cid');
        }
        $req->where('mbr_etat', MBR_ETAT_OK);
        if ($race) {
            $req->where('mbr_race', $race);
        }
        if ($region) {
            $req->where('map_region', $region);
        }
        $req->orderBy('hro_xp', 'desc')->take(50);

        break;
    case 7: // force armée
        $req = Mbr::select('mbr_gid', 'ambr_etat', 'mbr_mapcid', 'mbr_mid', 'mbr_pseudo', 'mbr_race',
                'mbr_points', 'mbr_pts_armee', 'mbr_etat', 'mbr_place','mbr_population')
                ->selectRaw('IF(ambr_etat= ? , 0, IFNULL(ambr_aid,0)) as al_aid,'
                        . 'IF(ambr_etat= ?, NULL, al_name) as al_name ', [ALL_ETAT_DEM, ALL_ETAT_DEM])
                ->leftJoin('al_mbr', 'mbr_mid', 'ambr_mid')
                ->leftJoin('al', 'al_aid', 'ambr_aid');
        if ($region) {
            $req->join('map', 'mbr_mapcid', 'map_cid');
        }
        $req->where('mbr_pts_armee', '>', 0)->where('mbr_etat', MBR_ETAT_OK);
        if ($race) {
            $req->where('mbr_race', $race);
        }
        if ($region) {
            $req->where('map_region', $region);
        }
        $req->orderBy('mbr_pts_armee', 'desc')->take(50);

        break;
    default:

}

$tab_class = $req->get()->toArray();
if ($type != 6)
{
	foreach ($tab_class as $key => $mbr)
	{
		$tab_class[$key]['ambr_aid'] = $mbr['al_aid'];
		unset($tab_class[$key]['al_aid']);
	}
}

if($_user['alaid']) {
	/* mes pactes */
	$dpl_atq = new diplo(array('aid' => $_user['alaid']));
	$dpl_atq_arr = $dpl_atq->actuels(); // les pactes actifs en tableau
	$_tpl->set('mbr_dpl',$dpl_atq_arr);
}
else
	$dpl_atq_arr = array();

if($type != 5 && $type != 6)
	$tab_class = can_atq_lite($tab_class, $_user['pts_arm'], $_user['mid'], $_user['groupe'], $_user['alaid'], $dpl_atq_arr);
	
$_tpl->set("class_array", $tab_class);
