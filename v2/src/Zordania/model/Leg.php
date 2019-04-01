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
    
    protected $primaryKey = 'leg_id';
    
    private $unites = null;

    /**
     * toutes les unités de la légion
     * @return type
     */
    function unites(){
        if($this->unites == null){
            $this->unites = $this->hasMany('Unt', 'unt_lid');
        }
        return $this->unites;
    }
    
    /**
     * un rang de la legion = 1 row de zrd_unt
     * @param type $type d'unite
     * @return type
     */
    function rang($type){
        if($this->unites == null){
            $this->unites = $this->hasMany('unt', 'unt_lid');
        }
        foreach($this->unites->get() as $unit){
            if($unit->unt_type == $type)
                return $unit;
        }
        return []; // empty result
    }
    
    /**
     * les légions village et batiment ne sont pas modifiables
     * @return type
     */
    function getIsModifiableAttribute(){
        return $this->leg_etat != LEG_ETAT_VLG && $this->leg_etat != LEG_ETAT_BTC;
    }
    
    /**
     * liste des fonctions 'static' : aucune reference a $this
     */
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
    
    static function canRename(int $mid, int $lid, string $name){
        // trim vire tab et espaces multiples
	$name = trim( str_replace("\t", " ", preg_replace("( +)", " ", $name)));

	if(!$mid || !$name)
		return false;

        return Leg::where('leg_mid', $mid)->where('leg_id', '<>', $lid)->where('leg_name', 'LIKE', $name)
                ->count() == 0;
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
			else if ($mbr)
				$sql .= ', mbr_pseudo, mbr_gid, mbr_mid, mbr_race, mbr_etat ';
			else if ($dest)
				$sql .= ', map_x, map_y, mbr_pseudo, mbr_gid, mbr_mid, mbr_race, mbr_etat ';
			if(!$get_unt && !$count_unt)
				$sql .= ', hro_id, hro_nom, hro_type, hro_xp AS hro_nrj, hro_vie, hro_bonus AS bonus, hro_bonus_from, hro_bonus_to AS bonus_to ';
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
                $req->join('unt', 'leg.leg_id', 'unt.unt_lid');
            } else if ($map) {
                $req->join('map AS p', 'leg.leg_cid', 'p.map_cid');
                $req->leftJoin('map AS d', 'leg.leg_dest', 'd.map_cid');
            } else if ($mbr) {
                $req->join('mbr', 'leg.leg_mid', 'mbr.mbr_mid');
            } else if ($dest) {
                $req->join('map', 'leg.leg_cid', 'map_cid');
                $req->join('mbr', 'leg.leg_mid', 'mbr.mbr_mid');
            }
            if ($get_leg && !$count_unt && !$sum) {
                $req->leftJoin('hero', 'leg.leg_id', 'hero.hro_lid');
            }
 	} else {
            $req->leftJoin('unt', 'leg.leg_id', 'unt.unt_lid');
	}

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
        Unt::del($lid);
        LegRes::del($lid);
        return Leg::where('leg_id', $lid)->delete();

    }

    /* supprimer toutes les légions d'un joueur */
    static function delAll(int $mid) {

        Unt::delAll($mid);
        LegRes::delAll($mid);
        return Leg::where('leg_mid', $mid)->delete();

    }
    
    /* les légions dans $leg_array peuvent être attaquées par un joueur ($mid) qui a $points, ($groupe) et ally=$alaid */
    static function canAtq(array $leg_array, int $points, int $mid, int $groupe, int $alaid, array $dpl_array = [])
    {
	$arr_cid = [];
	foreach($leg_array as $key => $value)
		if($value['mbr_mid'] == $mid)
			$arr_cid[$value['leg_cid']] = true;

	foreach($leg_array as $key => $value) {
		$pts = $value['mbr_pts_armee'];
		$alid = $value['ambr_aid'];
		$etat = $value['mbr_etat'];

		/* si c'est un allié qu'on peut défendre */
		$leg_array[$key]['can_def'] = false;
		if($alid && $alaid){
			if ($alid == $alaid) // même alliance
				$leg_array[$key]['can_def'] = true;
			elseif (isset($dpl_array[$alid]) and 
				($dpl_array[$alid] == DPL_TYPE_MIL or $dpl_array[$alid] == DPL_TYPE_MC)) // a un pacte
				$leg_array[$key]['can_def'] = true;
		}

		if((!$leg_array[$key]['can_def'] // pas allié
			&& (!$alid or !isset($dpl_array[$alid]) or $dpl_array[$alid] != DPL_TYPE_PNA) // pas de PNA
		) && (
			(abs($pts - $points) < ATQ_PTS_DIFF)  /* Trop de points de différences */
			&& ($pts > ATQ_PTS_MIN)  /* Pas assez de points pour attaquer */
			|| ($pts >= ATQ_LIM_DIFF && $points >= ATQ_LIM_DIFF) /* Arène */
		)
		&& session::$SES->canDo(DROIT_PLAY)/* Faut pas être un visiteur */
		&& $etat == MBR_ETAT_OK /* Validé et pas en Veille */
		&& isset($arr_cid[$value['leg_cid']])/* légion sur la même case */
		&& in_array($value['leg_etat'],[LEG_ETAT_VLG,LEG_ETAT_GRN,LEG_ETAT_DPL])/* légion en attende d'ordre*/
		)
			$leg_array[$key]['can_atq'] = true;
		else
			$leg_array[$key]['can_atq'] = false;
	}

	return $leg_array;
    }

}
