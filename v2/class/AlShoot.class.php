<?php

/**
 * shoot d'Alliances
 * lien avec l'alliance : al.al_aid = ares.ares_aid
 */
class AlShoot extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'al_shoot';

    /**
     * nb msg shootbox
     * @param int $aid
     * @return type
     */
    static function count(int $aid) {
        return AlShoot::where('shoot_aid', $aid)->count();
    }

    /**
     * 
     * @param int $aid
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    static function page(int $aid){
        
        $sql = "mbr_mid, shoot_msgid, shoot_mid, shoot_texte,mbr_pseudo,mbr_sign,_DATE_FORMAT(shoot_date) as shoot_date_formated,
		DATE_FORMAT(shoot_date,'%a, %d %b %Y %T') as shoot_date_rss";
        // replace date formatting:
        $sql = mysqliext::$bdd->parse_query($sql);

        $req = AlShoot::selectRaw($sql);
        $req->leftJoin('mbr', 'shoot_mid', 'mbr_mid');
        $req->where('shoot_aid', $aid);
        $req->orderBy('shoot_date', 'desc');
        return $req;
    }
    
    static function get(int $aid, int $limite1, int $limite2 = 0) {

        $sql = "mbr_mid, shoot_msgid, shoot_mid, shoot_texte,mbr_pseudo,mbr_sign,_DATE_FORMAT(shoot_date) as shoot_date_formated,
		DATE_FORMAT(shoot_date,'%a, %d %b %Y %T') as shoot_date_rss";
        // replace date formatting:
        $sql = mysqliext::$bdd->parse_query($sql);

        $req = AlShoot::selectRaw($sql);
        $req->leftJoin('mbr', 'shoot_mid', 'mbr_mid');
        $req->where('shoot_aid', $aid);
        $req->orderBy('shoot_date', 'desc');
        if ($limite2) {
            $req->offset($limite2)->limit($limite1);
        } else {
            $req->limit($limite1);
        }
        return $req->get()->toArray();
    }

    static function add(int $aid, string $text, int $mid) {

        $request = ['shoot_mid' => $mid,
            'shoot_aid' => $aid,
            'shoot_date' => DB::raw('NOW()'),
            'shoot_texte' => $text];
        return AlShoot::insertGetId($request);
    }

    /**
     * supprimer un msg de la shoot.
     * @param int $aid
     * @param int $msgid
     * @param int $mid
     * @param bool $chef
     * @return type
     */
    static function del(int $aid, int $msgid, int $mid, bool $chef = false) {

        $req = AlShoot::where('shoot_msgid', $msgid)->where('shoot_aid', $aid);
        if (!$chef){
            $req->where('shoot_mid', $mid);
        }
        return $req->delete();
    }

}
