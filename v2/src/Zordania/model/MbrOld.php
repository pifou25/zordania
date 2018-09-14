<?php

/**
 * Membre
 * lien avec les héros : hro_mid = mbr_id
 * lien avec la carte : mbr_mapcid = map_id
 */
class MbrOld extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
// override table name
    protected $table = 'mbr_old';

    /**
     * MbrOld historique des membres
     * @param type $mid
     * @return type
     */
    static function add(int $mid) {
        $prefix = DB::connection()->getConfig()['prefix'];
        return DB::insert("INSERT INTO " . $prefix . "mbr_old (mold_mid, mold_pseudo, mold_mail, mold_lip) "
                        . "SELECT mbr_mid, mbr_pseudo, mbr_mail, mbr_lip FROM " . $prefix . "mbr WHERE mbr_mid = ?", [$mid]);
    }

    static function get(array $cond = []) {

        $req = new MbrOld();
        if (isset($cond['mid'])) {
            $req->where('mold_mid', $cond['mid']);
        }
        if (isset($cond['pseudo'])) {
            $req->where('mold_pseudo', 'LIKE', $cond['pseudo']);
        }
        if (isset($cond['ip'])) {
            $req->where('mold_lip', 'LIKE', $cond['ip']);
        }
        // TODO: add pagination
        return $req->take(50)->get()->toArray();
    }

}
