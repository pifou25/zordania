<?php

/**
 * Membre
 * lien avec les héros : hro_mid = mbr_id
 * lien avec la carte : mbr_mapcid = map_id
 */
class Dpl extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
// override table name
    protected $table = 'diplo';

    /* méthodes statiques */

    static function get($cond) {
        $did = isset($cond['did']) ? protect($cond['did'], 'uint') : 0;
        $aid = isset($cond['aid']) ? protect($cond['aid'], 'uint') : 0;
        $etat = isset($cond['etat']) ? protect($cond['etat'], 'uint') : false;
        $type = isset($cond['type']) ? protect($cond['type'], 'uint') : false;
        $full = isset($cond['full']) ? protect($cond['full'], 'bool') : false;
        $jdeb = isset($cond['jdeb']) ? protect($cond['jdeb'], 'uint') * ZORD_SPEED : 0; // nb de tours depuis début

        $sql = 'dpl_did,dpl_etat,dpl_type,dpl_al1,dpl_al2,dpl_debut,dpl_fin ';
        if ($full) {
            $sql .= ', zrd_al1.al_name AS al1_name, zrd_al1.al_mid AS al1_mid, zrd_al2.al_name AS al2_name, zrd_al2.al_mid AS al2_mid';
        }

        $req = Dpl::selectRaw($sql);
        if ($full) {
            $req->leftJoin('al AS al1', 'diplo.dpl_al1', 'al1.al_aid');
            $req->leftJoin('al AS al2', 'diplo.dpl_al2', 'al2.al_aid');
        }

        if (!$did && !$aid && !$etat && !$type && $jdeb)
            return false;
        if ($did)
            $req->where('dpl_did', $did);
        if ($aid)
            $req->where('dpl_al1', $aid)->orWhere('dpl_al2', $aid);
        if ($etat !== false)
            $req->where('dpl_etat', $etat);
        if ($type !== false)
            $req->where('dpl_type', $type);
        if ($jdeb)
            $req->whereRaw('dpl_debut < (NOW() - INTERVAL ? MINUTE)', [$jdeb]);
      
        $result = $req->get()->toArray();
        $result = index_array($result, 'dpl_did');
        // mise en forme du tableau
        if ($aid)
            foreach ($result as $key => $row)
                if ($aid == $row['dpl_al1']) {
                    $result[$key]['dpl_al'] = $row['dpl_al2'];
                    $result[$key]['me'] = false; // on m'a proposé le pacte
                    if ($full) {
                        $result[$key]['al_name'] = $row['al2_name'];
                        $result[$key]['al_mid'] = $row['al2_mid'];
                    }
                } else {
                    $result[$key]['dpl_al'] = $row['dpl_al1'];
                    $result[$key]['me'] = true; // j'ai proposé
                    if ($full) {
                        $result[$key]['al_name'] = $row['al1_name'];
                        $result[$key]['al_mid'] = $row['al1_mid'];
                    }
                }
        return $result;
    }

    static function add($cond) {
        /* ajouter un pacte entre 2 alliances */
        $type = isset($cond['type']) ? protect($cond['type'], 'uint') : 0;
        $al1 = isset($cond['al1']) ? protect($cond['al1'], 'uint') : 0;
        $al2 = isset($cond['al2']) ? protect($cond['al2'], 'uint') : 0;
        if (!$type || !$al1 || !$al2)
            return false; // manque une info
        return Dpl::insertGetId(['dpl_etat' => DPL_ETAT_PROP,
                    'dpl_al1' => $al1,
                    'dpl_al2' => $al2,
                    'dpl_type' => $type,
                    'dpl_debut' => DB::raw('NOW()')]);
    }

    static function edit($cond) {

        /* modifier un pacte */
        $did = isset($cond['did']) ? protect($cond['did'], 'uint') : 0;
        if (!$did)
            return false; // clé primaire indispensable

        $request = [];
        foreach ($cond as $key => $val) {
            if (in_array($key, ['debut', 'fin'])) {
                $request["dpl_$key"] = ($val == 'now' ? DB::raw('NOW()') : $val);
            } else if ($key != 'did') {
                $request["dpl_$key"] = $val;
            }
        }
        if (empty($request))
            return false;
        return Dpl::where('dpl_did', $did)->update($request);
    }

    /**
     * @unused for now ...
     * @return type
     */
    static function cron_sign_pactes() {
        /* valider les pactes acceptés après un délais probatoire */
        return Dpl::where('dpl_etat', DPL_ETAT_ATT)
                        ->whereRaw('dpl_debut < (NOW() - INTERVAL ? MINUTE)', [self::DUREE_PROBA])
                        ->update(['dpl_debut' => DB::raw('NOW()'), 'dpl_etat' => DPL_ETAT_OK]);
    }

}
