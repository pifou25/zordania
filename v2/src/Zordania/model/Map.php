<?php

/**
 * terrains
 */
class Map extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'map';

    /**
     * infos sur les cases les villages et les légions sur le carré visible
     * @param int $mid
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     * @param int $zoom
     * @return array[$y][$x][$value]
     */
    static function get(int $mid, int $x1, int $y1, int $x2, int $y2, int $zoom = 1) {
        $cases = Map::whereBetween('map_x', [$x1, $x2])->whereBetween('map_y', [$y1, $y2])->get()->toArray();

        if (!count($cases))
            return $return;

        /* On indexe par x-y, c'est franchement plus pratique */
        foreach ($cases as $result) {
            $return[$result['map_y']][$result['map_x']] = $result;
        }

        /* Personnes par ici */
        $members = Map::select(['map_x', 'map_y', 'mbr_mid', 'mbr_pseudo', 'mbr_points', 'mbr_etat', 'mbr_race'])
                        ->join('mbr', 'map_cid', 'mbr_mapcid')
                        ->whereIn('mbr_etat', [MBR_ETAT_OK, MBR_ETAT_ZZZ])
                        ->whereBetween('map_x', [$x1, $x2])->whereBetween('map_y', [$y1, $y2])
                        ->get()->toArray();

        foreach ($members as $result) {
            $x = $result['map_x'];
            unset($result['map_x']);
            $y = $result['map_y'];
            unset($result['map_y']);
            $return[$y][$x]['members'][] = $result;
        }

        /* Légions par ici, sauf légions invisibles */
        $legions = Map::select(['map_x', 'map_y', 'leg_id', 'mbr_pseudo', 'mbr_etat', 'mbr_race', 'leg_name', 'hro_bonus'])
                        ->join('leg', 'map_cid', 'leg_cid')
                        ->join('mbr', 'leg_mid', 'mbr_mid')
                        ->leftJoin('hero', 'leg_id', 'hro_lid')
                        ->whereIn('leg_etat', [LEG_ETAT_DPL, LEG_ETAT_RET, LEG_ETAT_ALL, LEG_ETAT_ATQ, LEG_ETAT_GRN, LEG_ETAT_POS, LEG_ETAT_VLG])
                        ->whereRaw('IFNULL(hro_bonus, 0) <> ?', [CP_INVISIBILITE])
                        ->whereBetween('map_x', [$x1, $x2])->whereBetween('map_y', [$y1, $y2])
                        ->get()->toArray();

        foreach ($legions as $result) {
            $x = $result['map_x'];
            unset($result['map_x']);
            $y = $result['map_y'];
            unset($result['map_y']);
            $return[$y][$x]['legions'][] = $result;
        }


        return $return;
    }

    /* convertir coord x,y en un map_cid */

    static function getCid(int $x, int $y) {
        if ($x and $y) {
            $result = Map::where('map_x', $x)->where('map_y', $y)->get()->toArray();
            return $result[0]['map_cid'];
        } else
            return false;
    }

    /**
     * informations sur un map_cid
     * $dst = array(x, y) => distance à ce point
     * $dst = true => distance par rapport au village $_user
     * @global type $_user
     * @param array $cids
     * @param bool $dst
     * @return boolean
     */
    static function getGen($cids, array $dst = []) {
        if (!is_array($cids)) {
            $array = Map::getGen([$cids], $dst);
            return $array ? $array[0] : [];
        } else if (empty($cids)) {
            return false;
        }
        if (isset($dst['x']) && isset($dst['y'])) {
            $dst['x'] = protect($dst['x'], "uint");
            $dst['y'] = protect($dst['y'], "uint");
        }

        $req = Map::select('map_type', 'map_rand', 'map_x', 'map_y', 'map_climat', 'map_region', 'map_cid', 'mbr_mid', 'mbr_pseudo', 'mbr_gid', 'mbr_etat', 'mbr_points', 'mbr_race', 'hro_bonus');
        if (count($dst) == 2) {
            $req->selectRaw('GREATEST(ABS( ? - map_x ), ABS( ? - map_y)) AS map_dst', [$dst['x'], $dst['y']]);
        }
        $req->leftJoin('mbr', 'map_cid', 'mbr_mapcid');
        $req->leftJoin('leg', 'map_cid', 'leg_cid');
        $req->leftJoin('hero', 'leg_id', 'hro_lid');
        $req->whereIn('mbr_etat', [MBR_ETAT_OK, MBR_ETAT_ZZZ]);
        $req->whereIn('map_cid', $cids);
        return $req->get()->toArray();
    }

    /**
     * légions présentes en $cid = array(cid)
     * @param array $list_cid
     * @return array
     */
    static function getLegGen(array $list_cid) {
        if (empty($list_cid))
            return false;

        $sql = "SELECT map_type,map_rand,map_x,map_y,map_climat,map_region, 
		mbr_mid,mbr_pseudo,mbr_race, mbr_gid, mbr_points, mbr_pts_armee, mbr_etat, 
		hro_bonus,
		leg_mid, leg_name, leg_xp, leg_id, leg_cid, leg_etat, IFNULL(ambr_aid, 0) as ambr_aid ";

        $req = Map::join('leg', 'map_cid', 'leg_cid');
        $req->leftJoin('hero', 'leg_id', 'hro_lid');
        $req->join('mbr', 'mbr_mid', 'leg_mid');
        $req->leftJoin('al_mbr', 'ambr_mid', 'mbr_mid');
        $req->where('mbr_etat', MBR_ETAT_OK);
        $req->where('leg_etat', '!=', LEG_ETAT_BTC);
        $req->whereIn('map_cid', $list_cid);
        $req->whereRaw('IFNULL(hro_bonus,0) <> ?', [CP_INVISIBILITE]);
        return $req->get()->toArray();
    }

    static function getRand(int $rid) {

        $res = Map::where('map_region', $rid)->where('map_type', MAP_LIBRE)
                        ->orderByRaw('RAND()')->take(1)->get()->toArray();

        if (count($res) > 0)
            $ncid = $res[0]['map_cid'];
        else {
            $res = Map::where('map_region', $rid)->where('map_type', '!=', MAP_VILLAGE)
                            ->orderByRaw('RAND()')->take(1)->get()->toArray();
            $ncid = $res[0]['map_cid'];
        }
        return $ncid;
    }

    /* liste des emplacememnts libres autour de x,y */

    static function getFree(int $x, int $y) {

        $res = Map::selectRaw('*, round( sqrt( pow(map_x- ? ,2) + pow(map_y- ? ,2)), 3) AS distance', [$x, $y])
                        ->where('map_type', MAP_LIBRE)
                        ->orderBy('distance')->take(5)->get();
        return index_array($res, 'map_cid');
    }

    static function init(int $cid) {
        return Map::where('map_cid', $cid)->update(['map_type' => MAP_VILLAGE]);
    }

    /* Désinitialise la carte pour $mid
     */

    static function reset($mid, $cid) {
        return Map::where('map_cid', $cid)->update(['map_type' => MAP_LIBRE]);
    }

    static function getRegions(array $regions) {
        global $_regions, $_races;

        $req = Map::selectRaw('COUNT(*) AS mbr_nb, mbr_race, map_region')
                ->join('mbr', 'map_cid', 'mbr_mapcid')
                ->where('mbr_etat', MBR_ETAT_OK);
        if ($regions) {
            $req->whereIn('map_region', $regions);
        }
        $infos = $req->groupBy('map_region', 'mbr_race')->get()->toArray();

        $sum = ['total' => 0];
        // faire un TCD $sum[region_id][race] et $sum[total]
        foreach ($infos as $value) {
            $sum['total'] += $value['mbr_nb'];

            if (!isset($sum[$value['map_region']]))
                $sum[$value['map_region']]['total'] = $value['mbr_nb'];
            else
                $sum[$value['map_region']]['total'] += $value['mbr_nb'];

            if (!isset($sum[$value['map_region']][$value['mbr_race']]))
                $sum[$value['map_region']][$value['mbr_race']] = $value['mbr_nb'];
            else
                $sum[$value['map_region']][$value['mbr_race']] += $value['mbr_nb'];
        }

        $libre = [];
        // TCD du nb de places libres par région / race
        foreach ($_regions as $rid => $val) {
            $total = isset($sum[$rid]) ? $sum[$rid]['total'] : 0;
            foreach ($_races as $race => $visible) {
                if (isset($_regions[$rid][$race]) and ! $_regions[$rid][$race])
                    $libre[$rid][$race] = 0;
                else if (!$total)
                    $libre[$rid][$race] = 1;
                else
                    $libre[$rid][$race] = isset($_regions[$rid][$race]) ? ceil($total * $_regions[$rid][$race] / 100) : 0;
            }
        }

        return ['libre' => $libre, 'occ' => $sum];
    }

    /**
     * On ne calcule pas la distance a vol d'oiseau, mais la distance que la légion devrais parcourir
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     * @return type
     */
    static function distance(int $x1, int $y1, int $x2, int $y2) {

        $diffx = abs($x1 - $x2);
        $diffy = abs($y1 - $y2);
        if ($diffx == $diffy) { /* juste une diagonale */
            return $diffx;
        } else {
            return max($diffx, $diffy);
        }
    }

}
