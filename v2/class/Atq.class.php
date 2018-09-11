<?php

// TODO : atq_mbr
class AtqMbr extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'atq_mbr';

}

/**
 * atq : atq mid1 sur mid2
 */
class Atq extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'atq';

    static function add(int $mid1, int $mid2, int $lid1, int $lid2, array $bilan, int $cid) {
// ajoute l'attaque de $mid1 (attaquant) sur $mid2 (defenseur) avec le $bilan
// $bilan contient tout, les autres variables sont redondantes

        $request = [
            'atq_mid1' => $mid1,
            'atq_mid2' => $mid2,
            'atq_lid1' => $lid1,
            'atq_lid2' => $lid2,
            'atq_type' => ATQ_TYPE_ATQ,
            'atq_date' => DB::raw('NOW()'),
            'atq_cid' => $cid,
            'atq_bilan' => $bilan
        ];
        return Atq::insertGetId($request);
    }

    static function del(int $mid, int $aid = 0) {

        if ($aid) {
            $rows = AtqMbr::where('atq_aid', $aid)->delete();
        } else {
            $rows = AtqMbr::whereIn('atq_aid', function($query) use ($mid) {
                        $query->select('atq_aid')
                                ->from('atq')
                                ->where('atq_mid1', $mid);
                    })->delete();
        }

        if ($aid) {
            return $rows + Atq::where('atq_aid', $aid)->delete();
        } else {
            return $rows + Atq::where('atq_mid1', $mid)->delete();
        }
    }

    static function addAll(array $bilan) {
        // ajoute l'attaque dans zrd_atq et tous les liens dans zrd_atq_mbr

        $mid1 = protect($bilan['att']['leg_mid'], "uint");
        $lid1 = protect($bilan['att']['leg_id'], "uint");
        $cid = protect($bilan['att']['leg_cid'], "uint");
        $mid2 = protect($bilan['mid2'], 'uint');

        $add_atq = 0;
        foreach ($bilan['def'] as $lid => $leg) {
            if (!$add_atq && $leg['leg_mid'] == $mid2) { // ajouter le résultat de l'attaque
                $lid2 = protect($leg['leg_id'], "uint");
                $add_atq = Atq::add($mid1, $mid2, $lid1, $lid2, $bilan, $cid);
                if (!$add_atq)
                    die('erreur add_atq');
                break;
            }
        }
        if (!$add_atq)
            return false;
        /* s'il y a eu defenseurs */
        foreach ($bilan['def'] as $lid => $leg) {
            $request = [
                'atq_aid' => $add_atq,
                'atq_mid' => $leg['leg_mid']
            ];
            AtqMbr::insertGetId($request);
        }
    }

    /**
     * compter nb d'attaques de $mid sur $mid2
     * @param int $mid = attaquant
     * @param int $mid2 = defenseur
     */
    static function count(int $mid, int $mid2) {
        return Atq::where('atq_mid1', $mid)->where('atq_mid2', $mid2)->count();
    }

    /**
     * compte le nombre de 'vraies' attaques (i.e. on ne compte pas l'espion fake)
     * @param int $mid
     * @param int $mid2
     * @return int
     */
    static function getReal(int $mid, int $mid2) {
        $array = Atq::where('atq_mid1', $mid)
                        ->where('atq_mid2', $mid2)
                        ->where('atq_date', '>', DB::raw('NOW() - INTERVAL 24 HOUR'))
                        ->get()->toArray();
        $nb_atq = 0;
        foreach ($array as $key => $atq) {
            $array[$key]['atq_bilan'] = safe_unserialize($atq['atq_bilan']);
            // additionner les x rangs de la légion
            foreach ($atq['atq_bilan']['def'] as $leg){
                if (isset($leg['pertes']['unt'])){
                    foreach ($leg['pertes']['unt'] as $nb){
                        if ($nb > 0) { // compter l'atq et passer à la suivante
                            $nb_atq++;
                            break;
                        }
                    }
                }
            }
        }
        return $nb_atq;
    }

    /**
     * 
     * @param int $aid
     * @return array les bilans
     */
    static function get(int $aid){
        $array = self::page(['aid' => $aid])->get()->toArray();
        return self::safeUnserialize($array);
    }

    /**
     * dé-serialiser les bilans
     * @param array $array
     * @return array
     */
    static function safeUnserialize($array){
        foreach ($array as $key => $value)
            $array[$key]['atq_bilan'] = safe_unserialize($value['atq_bilan']);
        return $array;
    }
    /**
     * 
     * @param array $cond
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    static function page( array $cond) {
        
        $mid = 0;
        $aid = 0;
        $type = [ATQ_TYPE_ATQ, ATQ_TYPE_DEF];

        if (isset($cond['mid']))
            $mid = protect($cond['mid'], "uint");
        if (isset($cond['aid']))
            $aid = protect($cond['aid'], 'uint');
        if (isset($cond['type']))
            $type = protect($cond['type'], "array");

        if (!$mid && !$aid)
            return [];

        $sql = "zrd_atq.atq_aid, _DATE_FORMAT(atq_date) as atq_date_formated, ";
        $sql .= " atq_bilan, atq_cid, atq_mid1, atq_lid1, atq_mid2, atq_lid2 ";
        if (count($type) == 1) {
            $sql .= ", mbr_race, mbr_pseudo, mbr_gid ";
        }
        $sql = session::$SES->parseQuery($sql);
        $req = Atq::selectRaw($sql);

        if ($mid && count($type) == 1 && $type[0] == ATQ_TYPE_DEF) {
            $req->join('atq_mbr', 'atq.atq_aid', 'atq_mbr.atq_aid');
        }
        if (count($type) == 1) {
            $req->join('mbr', 'atq_mid2', 'mbr_mid');
        }

        if ($mid && count($type) == 1) { /* attaque OU defense sinon c'est pas un filtre */
            if ($type[0] == ATQ_TYPE_ATQ) /* attaque:  mid1 */
                $req->where('atq_mid1', $mid);
            else /* défense : mid dans la table atq_mbr */
                $req->where('atq_mid', $mid);
        } else if ($mid)
            $req->where('atq_mid1', $mid);
        if ($aid)
            $req->where('atq_aid', $aid);

        return $req->orderBy('atq_date', 'desc');
    }

    /**
     * fonctions pour calcul des pertes et butins ...
     * calcul du butin : 33% de chaque ressource nécessaire
     * @param type $pertes = array (type => nombre d'unités)
     * @param int $race
     * @param array $butin (optionnel) = butin déjà récupéré à cumuler
     * @return array (type => nombre) des butins
     */
static function calcButin($pertes, int $race, array $butin = []){
    
	if(empty($pertes)) return $butin;
	foreach ($pertes as $type => $nb_pertes) {
		$prix = Config::get($race,"unt",$type,"prix_res");
		if ($prix && $type) {
			foreach($prix as $res => $prix_uni) {
				$nb = round($prix_uni * $nb_pertes / 3);// 33%
				if($nb) {
					if(!isset($butin[$res]))
						$butin[$res] = 0;
					$butin[$res] += $nb;
				}
			}
		}
	}
	return $butin;
}

}
