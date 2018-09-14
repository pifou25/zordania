<?php

/**
 * Membre
 * lien avec les héros : hro_mid = mbr_id
 * lien avec la carte : mbr_mapcid = map_id
 */
class Stq extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
// override table name
    protected $table = 'stq';

    static function get(string $date1, string $date2 = ''){
        	
        $req = Stq::where('stq_date', 'LIKE', $date1);
        if($date2){
            $req->OrWhere('stq_date', 'LIKE', $date2);
        }
        return $req->orderBy('stq_date', 'desc')->get()->toArray();
    }
}