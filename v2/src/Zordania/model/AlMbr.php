<?php

/**
 * Membres d'Alliances
 * lien avec le joueur : ambr_mid = mbr_id
 * lien avec l'alliance : al.al_aid = al_mbr.ambr_aid
 */
class AlMbr extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'al_mbr';

    /**
     * virer un membre - ou tous si $mid=0
     * @param int $aid
     * @param int $mid
     * @return type
     */
    static function del(int $aid, int $mid = 0) {
        if ($mid) {
            return AlMbr::where('ambr_mid', $mid)->delete();
        } else {
            return AlMbr::where('ambr_aid', $aid)->delete();
        }
    }

    /**
     * virer un membre d'une alliance
     */
    static function delMbr(int $mid) {

        /* Il est dans une alliance ? */
        $mbr_infos = Mbr::getFull($mid);
        if (!$mbr_infos)
            return 0;

        $aid = $mbr_infos[0]['ambr_aid'];
        $etat = $mbr_infos[0]['ambr_etat'];

        if (!$aid)
            return 0;

        if ($etat == ALL_ETAT_DEM) {
            return AlMbr::del($aid, $mid);
        }

        $ally = allyFactory::getAlly($aid);
        if ($ally and $ally->al_mid != $mid)/* il n'est pas le chef on peut supprimer */ {
            Al::edit($aid, ['nb_mbr' => -1]);
            return AlMbr::del($aid, $mid);
        }

        /* recherche du nouveau chef par ordre hiérarchique */
        $chef = $ally->getMembers(ALL_ETAT_SECD);
        if (empty($chef))
            $chef = $ally->getMembers(ALL_ETAT_INTD);
        if (empty($chef))
            $chef = $ally->getMembers(ALL_ETAT_RECR);
        if (empty($chef))
            $chef = $ally->getMembers(ALL_ETAT_DPL);

        if (empty($chef))
            foreach ($ally->getMembers() as $chef) /* Sinon, on fait n'importe quoi */
                break;

        if (empty($chef) or $chef['mbr_mid'] == $mid) /* Personne ne peut la prendre en charge */
            return Al::del($aid);
        else
            return AlMbr::del($aid, $mid) +
                    Al::edit($aid, array('mid' => $chef['mbr_mid'], 'nb_mbr' => -1));
    }

    static function add(int $aid, int $mid, int $etat) {
        $request = ['ambr_mid' => $mid,
            'ambr_aid' => $aid,
            'ambr_etat' => $etat,
            'ambr_date' => DB::raw('NOW()')];
        return AlMbr::insertGetId($request);
    }

    /**
     * info d'alliance d'un joueur
     * @param int $mid
     * @return array
     */
    static function get(int $mid) {
        $result = AlMbr::select('ambr_aid', 'ambr_etat', 'al_mid', 'al_name')
                        ->join('al', 'ambr_aid', 'al_aid')->where('ambr_mid', $mid)
                        ->get()->toArray();
        return empty($result) ? false : $result[0];
    }

    //Compter le temps qu'il reste avant accès grenier
    static function getAccess(int $mid) {

        //ajout délais à la date d'entrée dans l'alli
        return AlMbr::selectRaw('DATE_ADD(ambr_date, INTERVAL ? DAY) as end_date', [ALL_NOOB_TIME])
                        ->where('ambr_mid', $mid)->get()->toArray();
    }

}
