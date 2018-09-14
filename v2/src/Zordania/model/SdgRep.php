<?php

/**
 * unités
 * lien avec les légions - Leg
 * unt_lid = leg_id
 */
class SdgRep extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'sdg_rep';

    static function add(int $sid, array $reponses) {
        foreach ($reponses as $reponse) {
            $request[] = ['srep_sid' => $sid,
                'srep_texte' => $reponse,
                'srep_nb' => 0];
        }
        SdgRep::insert($request);
    }

    /* Réponses possibles pour $id */
    static function get(int $sid) {
        return SdgRep::where('srep_sid', $sid)->get()->toArray();
    }

    /* Modifie la réponse $id */
    static function edit(int $rid, string $texte) {
        return SdgRep::where('srep_id', $rid)->update(['srep_texte' => $texte]);
    }

}
