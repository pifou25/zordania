<?php

/**
 * Légions
 * lien avec les membres : leg_mid = mbr_id
 * lien avec les unités : unt_lid = leg_id
 * lien avec la carte : unt_cid = map_cid
 */
class Leg extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'leg';

    /* une légion */

    static function getById(int $lid) {
        return Leg::where('leg_id', '=', $lid)->get()->toArray();
    }

    /* Toutes les légions sauf village et batiments (état par défaut) */
    static function getAll(int $mid, array $etat = [LEG_ETAT_GRN, LEG_ETAT_POS, LEG_ETAT_DPL, 
                            LEG_ETAT_ALL, LEG_ETAT_RET, LEG_ETAT_ATQ]) {
            return Leg::get(array('mid' => $mid, 'leg' => true, 'etat' => $etat));
    }
    
    /* Toutes les légions & leurs unités, sauf village et batiments (état par défaut) */
    static function getUnt(int $mid, array $etat = [LEG_ETAT_GRN, LEG_ETAT_POS, LEG_ETAT_DPL, 
                            LEG_ETAT_ALL, LEG_ETAT_RET, LEG_ETAT_ATQ]) {
            return Leg::get(array('mid' => $mid, 'leg' => true, 'count_unt' => true, 'etat' => $etat));
    }
    
    static function getMap(int $mid, array $etat = [LEG_ETAT_GRN, LEG_ETAT_POS, LEG_ETAT_DPL, 
                            LEG_ETAT_ALL, LEG_ETAT_RET, LEG_ETAT_ATQ]) {
            return Leg::get(array('mid' => $mid, 'leg' => true, 'etat' => $etat, 'map' => true));
    }

    /**
     * compter les unités
     * @param int $mid
     * @param array $etat
     * @return type
     */
    static function countUnt(int $mid, array $etat = []){
        $result = Leg::get(['mid' => $mid, 'etat' => $etat, 'sum' => true]);
        $count = 0;
        foreach($result as $value){
            $count += $value['unt_sum'];
        }
        return $count;
    }

    /**
     * compter le nombre de légions
     * @param int $mid
     * @param array $etat
     * @return type
     */
    static function count(int $mid, array $etat = []){
        return count(Leg::get(['mid' => $mid, 'etat' => $etat, 'mbr' => true]));
    }
    
    static function get(array $cond){
        
	$mid = 0;
	$leg = array();
	$unt = array();
	$etat = array();
	$get_unt = false; /* Pour avoir des infos sur les unités */
	$get_leg = false; /* Pour avoir des infos sur la legion */
	$cid = 0;
	$dest = 0; // destination d'une légion
	$rang = 0;
	$sum = false; /* Fait la somme des unités du même type */
	$count_unt = false;
	$mbr = false; /* informations membre */
	$map = false; /* afficher des infos supplémentaires de la carte (x y) */

	if(isset($cond['count_unt']))
		$count_unt = true;
	if(isset($cond['map']))
		$map = true;

	if(isset($cond['mid']))
		$mid = protect($cond['mid'], "uint");
	if(isset($cond['unt'])) {
		$unt = protect($cond['unt'], array('uint'));
		$get_unt = true;
	}
	if(isset($cond['leg'])) {
		$leg = protect($cond['leg'], array('uint'));
		$get_leg = true;
	}
	if(isset($cond['etat']))
		$etat = protect($cond['etat'], array('uint'));
	if(isset($cond['cid'])){
		$cid = protect($cond['cid'], "uint");
		$get_leg = true;
	}
	if(isset($cond['dest'])){
		$dest = protect($cond['dest'], "uint");
		$get_leg = true;
	}
	if(isset($cond['sum']))
		$sum = protect($cond['sum'], "bool");
	if(isset($cond['mbr']))
		$mbr = protect($cond['mbr'], "bool");
	if(isset($cond['rang']))
		$rang = protect($cond['rang'], "uint");
	
	if(!$get_leg && !$get_unt && !$sum) return array(); /* Rien a faire */
		
	if($sum) {
		$sql = "unt_type, unt_rang, SUM(unt_nb) as unt_sum ";
	} else if(!$get_leg || !$get_unt) {
		if($get_leg) {
			$sql = "leg_id, leg_mid, leg_cid, leg_etat, leg_vit, leg_name, leg_xp, leg_dest, leg_tours, leg_fat, leg_stop ";
			if(!$get_unt && $count_unt)
				$sql.=", SUM(unt_nb) as unt_nb ";
			else if ($map)
				$sql.=', zrd_p.map_x map_x, zrd_p.map_y map_y, zrd_d.map_x dest_x, zrd_d.map_y dest_y ';
			else if ($dest || $mbr)
				$sql .= ', mbr_pseudo, mbr_gid, mbr_mid, mbr_race, mbr_etat ';
			if(!$get_unt && !$count_unt)
				$sql .= ', hro_id, hro_nom, hro_type, hro_xp, hro_vie, hro_bonus AS bonus, hro_bonus_from, hro_bonus_to AS bonus_to ';
		}
		if($get_unt){
                    $sql = ($get_leg ? $sql . ",": "") . "unt_type, unt_rang, unt_nb ";
                }
        } else {
            $sql = '*';
        }
        
        $req = Leg::select(DB::raw($sql));

	if(!$get_unt) {
            if($count_unt || $sum) {
                $req->leftJoin('unt', 'leg.leg_id', 'unt.unt_lid');
            } else if ($map) {
                $req->join('map AS p', 'leg.leg_cid', 'p.map_cid');
                $req->leftJoin('map AS d', 'leg.leg_dest', 'd.map_cid');
            } else if ($dest || $mbr) {
                $req->join('mbr', 'leg.leg_mid', 'mbr.mbr_mid');
            }
            if ($get_leg && !$count_unt && !$sum) {
                $req->leftJoin('hero', 'leg.leg_id', 'hero.hro_lid');
            }
 	} else {
            $req->join('unt', 'leg.leg_id', 'unt.unt_lid');
	}

	if($unt)
            $req->where('unt_nb', '>', 0);
	if($mid)
            $req->where('leg_mid', '=', $mid);	
	if($leg)
            $req->whereIn('leg_id', $leg);	
	if($unt)
            $req->whereIn('unt_type', $unt);
	if($get_unt)
            $req->where('unt_nb', '>', 0);	
	if($etat)
            $req->whereIn('leg_etat', $etat);
	if($cid)
            $req->where('leg_cid', '=', $cid);	
	if($dest)
            $req->where('leg_dest', '=', $dest);
	if($rang)
            $req->where('unt_rang', '=', $rang);
		
        if($sum){
            $req->groupBy('unt_type');
        }
        if($count_unt){
            $req->groupBy('leg_id');
	}

	if($get_unt or $sum) // leg_stop: classement par ordre d'arrivée
            $req->orderBy('leg_stop', 'unt_rang');
	else
            $req->orderBy('leg_stop');

        return $req->get()->toArray();
    }

    /* ajouter une légion */
    static function add(int $mid, int $cid, int $etat, string $name) {

        $request = ['leg_mid' => $mid,
            'leg_cid' => $cid,
            'leg_etat' => $etat,
            'leg_name' => $name];

        return Leg::insertGetId($request);
    }

    static function edit(int $mid, int $lid, array $new = []){

        $request = [];
        foreach ($new as $key => $val) {
            if (in_array($key, ['etat', 'vit', 'dest', 'cid', 'xp', 'fat', 'name'])){
                $request["leg_$key"] = $val;
            }
        }

	if(!$mid || !$lid || empty($request)){
            return 0;
        }
        
        return Leg::where('leg_mid', '=', $mid)->where('leg_id', '=', $lid)->update($request);
    }
    
    /* supprimer une légion + unités + butin */
    static function del(int $mid, int $lid = 0) {

        if(!$lid){
            return Leg::delAll($mid);
        }
        Leg::where(['leg_mid' => $mid, 'leg_cid' => $lid])->delete();
        Unt::del($lid);
        LegRes::delAll($mid, $lid);
        return true;

    }

    /* supprimer toutes les légions d'un joueur */
    static function delAll(int $mid) {

        Leg::where(['leg_mid' => $mid])->delete();
        Unt::delAll($mid);
        LegRes::delAll($mid);
        return true;

    }

}
