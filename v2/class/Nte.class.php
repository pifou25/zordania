<?php

/**
 * unités
 * lien avec les légions - Leg
 * unt_lid = leg_id
 */
class Nte extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'ntes';

    static function add(int $mid, string $titre, string $texte, string $import) {

        $request = ['nte_mid' => $mid,
            'nte_titre' => $titre,
            'nte_texte' => $texte,
            'nte_date' => DB::raw('NOW()'),
            'nte_import' => $import];
        return Nte::insertGetId($request);
    }

    static function get(int $mid, int $nid = 0) {
        $sql = mysqliext::$bdd->parse_query('_DATE_FORMAT(nte_date) as nte_date_formated');
        //$req = Nte::;

        if ($nid) {
            return Nte::select('nte_nid', 'nte_titre', 'nte_import', 'nte_texte')->selectRaw($sql)
                            ->where('nte_nid', $nid)->get()->toArray();
        } else {
            return Nte::select('nte_nid', 'nte_titre', 'nte_import')->selectRaw($sql)
                            ->where('nte_mid', $mid)->orderBy('nte_date', 'desc')->get()->toArray();
        }
    }

    static function edit(int $mid, int $nid, string $titre, string $texte, string $import) {
        $request = ['nte_mid' => $mid,
            'nte_titre' => $titre,
            'nte_texte' => $texte,
            'nte_import' => $import,
            'nte_date' => DB::raw('NOW()')];
        return Nte::where('nte_nid', $nid)->update($request);
    }

    static function del($mid, $nid = 0) {
        if ($nid) {
            return Nte::where('nte_nid', $nid)->delete();
        } else {
            return Nte::where('nte_mid', $mid)->delete();
        }
    }

}
