<?php

/**
 * unités
 * lien avec les légions - Leg
 * unt_lid = leg_id
 */
class SdgVte extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'sdg_vte';

    /* $mid vote $vid au sondage $sid */

    static function add(int $sid, int $mid, int $rid) {

        $result = SdgVte::insert(['svte_sid' => $sid, 'svte_mid' => $mid, 'svte_rid' => $rid]);

        if ($result) {
            SdgRep::where('srep_id', $rid)->increment('srep_nb');
            Sdg::where('sdg_id', $sid)->increment('sdg_rep_nb');
        }
    }

    static function count(int $sid, int $mid) {
        return SdgVte::where('svte_sid', $sid)->where('svte_mid', $mid)->count();
    }

}
