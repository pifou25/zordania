<?php

/**
 * Membre
 * lien avec les héros : hro_mid = mbr_id
 * lien avec la carte : mbr_mapcid = map_id
 */
class Surv extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
// override table name
    protected $table = 'surv';

    /**
     * ajoute une surveillance - Surv
     * @param int $mid
     * @param int $mid_admin
     * @param int $type
     * @param int $cause
     * @return int
     */
    static function add(int $mid, int $mid_admin, int $type, string $cause) {

        $request = ['surv_mid' => $mid,
            'surv_admin' => $mid_admin,
            'surv_debut' => DB::raw('NOW()'),
            'surv_etat' => SURV_OK,
            'surv_type' => $type,
            'surv_cause' => $cause];
        Surv::insertGetId($request);
    }

    /**
     * renvoie les surveillances admin affectées au joueur dont on passe l'id comme argument
     * @param int $mid
     * @return type
     */
    static function get(int $mid) {

        $sql = "surv_id, surv_mid, surv_etat, surv_admin, _DATE_FORMAT(surv_debut) as debut, ";
        $sql .= " _DATE_FORMAT(surv_debut + INTERVAL ? SECOND) as fin, surv_type, surv_cause";
        // replace date formatting:
        $sql = mysqliext::$bdd->parse_query($sql);
        $req = Surv::selectRaw($sql, [SURV_DUREE])->where('surv_mid', $mid)->where('surv_etat', SURV_OK);
        return $req->get()->toArray();
    }

    static function getBySid(int $sid) {

        $sql = "surv_mid, surv_type, surv_etat, _DATE_FORMAT(surv_debut + INTERVAL ? SECOND) as fin ";
        // replace date formatting:
        $sql = mysqliext::$bdd->parse_query($sql);
        $req = Surv::selectRaw($sql, [SURV_DUREE])->where('surv_id', $sid)->where('surv_etat', SURV_OK);
        return $req->get()->toArray();
    }

    static function getList() {

        return Surv::select('surv_id', 'surv_mid', 'surv_admin', 'surv_type', 'surv_debut', 'surv_cause', 'mbr.mbr_pseudo AS surv_pseudo', 'mbr2.mbr_pseudo AS surv_adm_pseudo')
                        ->join('mbr AS mbr', 'surv_mid', 'mbr.mbr_mid')
                        ->join('mbr AS mbr2', 'surv_admin', 'mbr2.mbr_mid')
                        ->where('surv_etat', SURV_OK)
                        ->get()->toArray();
    }

    static function getFin() {
        return Surv::select('surv_id', 'surv_mid', 'surv_admin', 'surv_type', 'surv_fin', 'surv_debut', 'surv_cause', 'mbr.mbr_pseudo AS surv_pseudo', 'mbr2.mbr_pseudo AS surv_adm_pseudo')
                        ->join('mbr AS mbr', 'surv_mid', 'mbr.mbr_mid')
                        ->join('mbr AS mbr2', 'surv_admin', 'mbr2.mbr_mid')
                        ->where('surv_etat', SURV_CLOSE)->whereRaw('surv_fin >= DATE_SUB(NOW(),INTERVAL 1 MONTH)')
                        ->get()->toArray();
    }

    static function close(int $sid) {

        return Surv::where('surv_id', $sid)
                        ->update(['surv_fin' => DB::raw('NOW()'),
                            'surv_etat' => SURV_CLOSE]);
    }

    static function isSurv(int $mid) {
        return !empty(Surv::get($mid));
    }

}
