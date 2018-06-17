<?php

/**
 * Membres d'Alliances
 * lien avec le joueur : ambr_mid = mbr_id
 * lien avec l'alliance : al.al_aid = al_mbr.ambr_aid
 */
class AlMbr extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'al_mbr';

    
    /**
     * virer un membre - ou tous si $mid=0
     * @param int $aid
     * @param int $mid
     * @return type
     */
    static function del(int $aid, int $mid = 0) {
        if($mid){
            return AlMbr::where('ambr_mid', $mid)->delete();
        }else{
            return AlMbr::where('ambr_aid', $aid)->delete();
        }
    }

    static function add(int $aid, int $mid, int $etat) {
        $request = ['ambr_mid' => $mid,
            'ambr_aid' => $aid,
            'ambr_etat' => $etat,
            'ambr_date' => DB::raw('NOW()')];
        return  AlMbr::insertGetId($request);
    }

}