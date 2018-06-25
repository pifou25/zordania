<?php

/**
 * Membre
 */
class MbrLog extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
// override table name
    protected $table = 'mbr_log';

    /**
     * log des IP MbrLog
     * @param int $mid
     * @param string $ip
     * @param bool $full
     * @param int $gid
     * @return type
     */
    static function get(int $mid, int $take = 0) {

        $sql = 'mlog_mid, mlog_ip, _DATE_FORMAT(mlog_date) AS mlog_date';
        // replace date formatting:
        $sql = mysqliext::$bdd->parse_query($sql);
        $req = MbrLog::select(DB::raw($sql));

        $req->where('mlog_mid', $mid);
        $req->orderBy('mlog_date', 'desc');
        if ($take) {
            $req->take($take);
        }

        return $req->get()->toArray();
    }

    static function getByIp(string $ip, bool $full = false, int $gid = 0) {

        if ($full)
            $sql = 'mlog_mid, IFNULL(mbr_pseudo, mold_pseudo) AS mbr_pseudo, IFNULL(mbr_gid,0) AS mbr_gid, IFNULL(mbr_mail, mold_mail) AS mbr_mail, mlog_ip, _DATE_FORMAT(mlog_date) AS mlog_date';
        else
            $sql = 'mlog_mid, mlog_ip, _DATE_FORMAT(mlog_date) AS mlog_date';

        // replace date formatting:
        $sql = mysqliext::$bdd->parse_query($sql);
        $req = MbrLog::select(DB::raw($sql));

        if ($full) {
            $req->leftJoin('mbr', 'mlog_mid', 'mbr_mid');
            $req->leftJoin('mbr_old', 'mlog_mid', 'mold_mid');
        }


        $req->where('mlog_ip', $ip);
        if ($full and $gid != GRP_DIEU and $gid != GRP_DEMI_DIEU) {
            $req->whereNotIn('mbr_gid', [GRP_GARDE, GRP_PRETRE, GRP_DEMI_DIEU, GRP_DIEU, GRP_DEV, GRP_ADM_DEV]);
        }
        $req->orderBy('mlog_date', 'desc');

        return $req->get()->toArray();
    }

    static function add(int $mid, string $ip){
        $request = ['mlog_mid' => $mid,
            'mlog_ip' => $ip,
            'mlog_date' => DB::raw('NOW()')];
        return MbrLog::insertGetId($request);
    }
}
