<?php

/**
 * Membre
 * lien avec les héros : hro_mid = mbr_id
 * lien avec la carte : mbr_mapcid = map_id
 */
class DplShoot extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
// override table name
    protected $table = 'diplo_shoot';

    //shout commune pactes
    static function count(int $did)/* nb msg shootbox */ {
        return DplShoot::where('dpl_shoot_did', $did)->count();
    }

    /**
     * get the query for all message of a shootbox pact
     * @param int $did
     * @return queryBuilder
     */
    static function get(int $did) {
        $sql = "mbr_mid, dpl_shoot_msgid, dpl_shoot_mid, dpl_shoot_did, " .
                "dpl_shoot_texte,mbr_pseudo,mbr_sign,_DATE_FORMAT(dpl_shoot_date) as dpl_shoot_date_formated," .
                "DATE_FORMAT(dpl_shoot_date,'%a, %d %b %Y %T') as dpl_shoot_date_rss";
        // replace date formatting:
        $sql = mysqliext::$bdd->parse_query($sql);

        return DplShoot::selectRaw($sql)
                        ->join('mbr', 'dpl_shoot_mid', 'mbr_mid')
                        ->where('dpl_shoot_did', $did)
                        ->orderBy('dpl_shoot_date', 'desc');
    }

    static function add(int $did, string $text, int $mid) {
        $request = ['dpl_shoot_mid' => $mid,
            'dpl_shoot_did' => $did,
            'dpl_shoot_texte' => $text,
            'dpl_shoot_date' => DB::raw('NOW()')];

        return DplShoot::insertGetId($request);
    }

    static function del(int $did, int $msgid, int $mid, $chef = false) {
        $req = DplShoot::where('dpl_shoot_msgid', $msgid)->where('dpl_shoot_did', $did);
        if (!$chef) {
            $req->where('dpl_shoot_mid', $mid);
        }
        return $req->delete();
    }

}
