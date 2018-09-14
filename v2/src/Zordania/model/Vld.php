<?php

/**
 * Membre
 * lien avec les héros : hro_mid = mbr_id
 * lien avec la carte : mbr_mapcid = map_id
 */
class Vld extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'vld';

    /* supprimer une validation */

    static function del(string $key, int $mid) {
        return Vld::where('vld_mid', $mid)->where('vld_rand', $key)->delete();
    }

    /* Ajoute un acte à valider */

    static function add(string $key, int $mid, string $act) {
        $request = ['vld_mid' => $mid,
            'vld_rand' => $key,
            'vld_act' => $act,
            'vld_date' => DB::raw('NOW()')];
        return Vld::insertGetId($request);
    }

    /* Supprime tout ce que le membre peut avoir a valider, par type d'action */

    static function init(int $mid, string $act = '') {
        if ($act) {
            return Vld::where('vld_mid', $mid)->where('vld_act', $act)->delete();
        } else {
            return Vld::where('vld_mid', $mid)->delete();
        }
    }

    static function get(int $mid) {
        // replace date formatting:
        $sql = session::$SES->parseQuery('vld_act, vld_rand, _DATE_FORMAT(vld_date) as vld_date_formated');

        return Vld::selectRaw($sql)->where('vld_mid', $mid)->get()->toArray();
    }

}
