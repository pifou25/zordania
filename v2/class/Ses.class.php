<?php

/**
 * Marché
 * lien membre : mch_mid = mbr_mid
 */
class Ses extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'ses';

    static function get(int $mid) {
        return Ses::where('ses_mid', $mid)->get()->toArray();
    }

    /**
     * On vire les anciennes sessions qu'il pouvait avoir 
     * @param int $sid
     * @param int $mid
     * @param string $ip
     * @return type
     */
    static function del(string $sid, int $mid = 0, string $ip = '') {
        $req = Ses::where('ses_sesid', $sid);
        if ($mid && $ip) {
            $req->orWhere(
                    function($query) use ($mid, $ip) {
                $query->where('ses_mid', $mid)->where('ses_mid', '!=', 1)->where('ses_ip', '!=', $ip);
            });
        }
        return $req->delete();
    }

    static function add(string $sid, int $mid, string $ip) {
        $request = ['ses_sesid' => $sid,
            'ses_mid' => $mid,
            'ses_ip' => $ip,
            'ses_lact' => 'session',
            'ses_ldate' => DB::raw('NOW()'),
            'ses_rand' => 0];
        return Ses::insertGetId($request);
    }

    /**
     *  Mise a jour des sessions
     * @param string $sid
     * @param string $act
     * @return type
     */
    static function edit(string $sid, string $act) {
        $rand = gettimeofday(); /* Pour être sur de mettre a jour */
        $request = ['ses_lact' => $act,
            'ses_ldate' => DB::raw('NOW()'),
            'ses_rand' => $rand['usec']];
        return Ses::where('ses_sesid', $sid)->update($request);
    }

    /**
     * unused
     * @param int $seconds
     * @return type
     */
    static function delOld(int $seconds = 300) {
        return Ses::whereRaw('ses_ldate < (NOW() - INTERVAL ? SECOND)', [$seconds])->delete();
    }

    /* Nombre de membres en ligne */

    static function count() {

        return Ses::where('ses_mid', '!=', 1)
                        ->join('mbr', 'ses_mid', 'mbr_mid')->where('mbr_etat', MBR_ETAT_OK)->count();
    }

    static function isOnline(int $mid) {

        return Ses::where('ses_mid', $mid)->count();
    }

    /* Liste - TODO convert and paginate */

    static function getOnline() {

        $sql = "mbr_etat,mbr_gid,mbr_mid,mbr_pseudo,mbr_mapcid,mbr_race,mbr_population,mbr_place,";
        $sql .= "mbr_gid,ses_ip,ses_lact, mbr_points,mbr_pts_armee, ses_mid,mbr_lang,";
        $sql .= "_DATE_FORMAT(ses_ldate) as ses_ldate,";
        $sql .= " ambr_etat, IF(ambr_etat=" . ALL_ETAT_DEM . ", 0, IFNULL(ambr_aid,0)) as ambr_aid, ";
        $sql .= " IF(ambr_etat=" . ALL_ETAT_DEM . ", NULL, al_name) as al_name  ";
        // replace date formatting:
        $req = Ses::selectRaw(mysqliext::$bdd->parse_query($sql));
        return $req->join('mbr', 'ses_mid', 'mbr_mid')
                        ->leftJoin('al_mbr', 'mbr_mid', 'ambr_mid')
                        ->leftJoin('al', 'ambr_aid', 'al_aid')
                        ->where('mbr_mid', '!=', 1)->where('mbr_etat', MBR_ETAT_OK)
                        ->orderBy('ses_ldate', 'desc');
    }

}
