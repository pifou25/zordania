<?php

/**
 * unités
 * lien avec les légions - Leg
 * unt_lid = leg_id
 */
class Sdg extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'sdg';

    static function add(string $texte, int $nb = 0) {
        $request = ['sdg_texte' => $texte,
            'sdg_rep_nb' => $nb,
            'sdg_date' => DB::raw('NOW()')];
        return Sdg::insertGetId($request);
    }

    static function getSdg(int $sid, int $mid = 0) {
        if ($mid)
            return Sdg::get(['sid' => $sid, 'mid' => $mid]);
        else
            return Sdg::get(['sid' => $sid]);
    }

    static function get(array $cond = []) {
        $mid = 0;
        $sid = 0;

        if (isset($cond['mid']))
            $mid = protect($cond['mid'], "uint");
        if (isset($cond['sid']))
            $sid = protect($cond['sid'], "uint");

        $sql = "sdg_id, sdg_texte, _DATE_FORMAT(sdg_date) as sdg_date, sdg_rep_nb ";

        if ($mid)
            $sql .= ", svte_rid as sdg_my_vte";
        $sql = mysqliext::$bdd->parse_query($sql);
        $req = Sdg::selectRaw($sql);

        if ($mid) { /* Selectionner les sondages ou on a voté */
            $req->leftJoin('sdg_vte', function($join) use ($mid){
                $join->on('sdg_id', 'svte_sid')->where('svte_mid', $mid);
            });
        }

        if ($sid) {
            $req->where('sdg_id', $sid);
        } else {
            $req->orderBy('sdg_id', 'desc');
        }

        return $req->get()->toArray();
    }

    /* Modifie la question du sondage $id */

    static function edit(int $sid, string $texte) {
        return Sdg::where('sdg_id', $sid)->update(['sdg_texte' => $texte]);
    }

    /**
     * delete sondage, ses questions ses reponses
     * @param type $id
     * @return type
     */
    static function del($id) {
        $nb = Sdg::where('sdg_id', $id)->delete();
        $nb += SdgRep::where('srep_sid', $id)->delete();
        $nb += SdgVte::where('svte_sid', $id)->delete();
        return $nb;
    }

}
