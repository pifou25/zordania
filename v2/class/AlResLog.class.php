<?php

/**
 * grenier d'Alliances
 * lien avec l'alliance : al.al_aid = ares.ares_aid
 */
class AlResLog extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'al_res_log';

    /**
     * log des retraits du grenier
     * @param int $aid
     * @param int $limite2
     * @param int $limite1
     * @param bool $synth
     * @return type
     */
    static function get(int $aid, int $limite2 = 0, int $limite1 = 0, bool $synth = false) {

        if ($synth) {
            $sql = "mbr_pseudo, mbr_gid,mbr_mid,arlog_mid,arlog_type,SUM(arlog_nb) as total,arlog_ip ";
        } else {
            $sql = "mbr_pseudo, mbr_gid,mbr_mid,arlog_mid,arlog_type,arlog_nb,_DATE_FORMAT(arlog_date) as arlog_date_formated ,arlog_ip ";
            // replace date formatting:
            $sql = mysqliext::$bdd->parse_query($sql);
        }

        $req = AlResLog::selectRaw($sql);
        $req->leftJoin('mbr', 'arlog_mid', 'mbr_mid');

        $req->where('arlog_aid', $aid);
        if ($synth) {
            $req->groupBy('arlog_mid', 'arlog_type');
        } else {
            $req->orderBy('arlog_date', 'desc');
            if ($limite1) {
                $req->offset($limite1)->limit($limite2);
            } else if($limite2) {
                $req->limit($limite2);
            } else {
                return $req; // return query as it for paginator
            }
        }

        return $req->get()->toArray();
    }

    /**
     * log des retraits du grenier
     * @param int $aid
     * @param int $mid
     * @param int $type
     * @param int $nb
     * @return type
     */
    static function add(int $aid, int $mid, int $type, int $nb) {

        $request = ['arlog_aid' => $aid,
            'arlog_mid' => $mid,
            'arlog_type' => $type,
            'arlog_nb' => $nb,
            'arlog_date' => DB::raw('NOW()'),
            'arlog_ip' => get_ip()];
        return AlResLog::insertGetId($request);
    }

}
