<?php

/**
 * Marché
 * lien membre : mch_mid = mbr_mid
 */
class Mch extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'mch';

    /**
     * toutes les ventes en cours d'un joueur
     * @param int $mid
     * @return array
     */
    static function getByMid(int $mid) {
        // replace date formatting:
        $sql = mysqliext::$bdd->parse_query('*, _DATE_FORMAT(mch_time) as mch_time_formated');
        return Mch::select(DB::raw($sql))
                        ->where('mch_mid', $mid)
                        ->where('mch_etat', '!=', COM_ETAT_ACH)
                        ->orderBy('mch_cid', 'desc')
                        ->get()->toArray();
    }

    /**
     * rechercher une vente par son ID
     * @param int $cid
     * @return type
     */
    static function get(int $cid) {
        return Mch::where('mch_cid', $cid)
                        ->where('mch_etat', '!=', COM_ETAT_ACH)
                        ->get()->toArray();
    }

    /**
     * liste des ventes disponibles pour un joueur, i.e. sauf les siennes
     * @param int $mid
     * @param bool $valide
     * @return type
     */
    static function getList(int $mid = 0, bool $valide = true) {
        $req = Mch::selectRaw('mch_type, count(*) AS nb');
        if ($mid) {
            $req->where('mch_mid', '!=', $mid);
        }
        return $req->where('mch_etat', ($valide ? COM_ETAT_OK : COM_ETAT_ATT))
                        ->groupBy('mch_type')
                        ->orderBy('mch_type', 'asc')->orderBy('nb', 'desc')
                        ->get()->toArray();
    }

    /**
     * liste des ressources dispo au marché
     * @param int $mid = sauf pour ce joueur
     * @param int $type = uniquement cette ressource
     * @param bool $valide = ventes validées (true) ou en attente (false)
     * @return array
     */
    static function getRes(int $mid = 0, int $type = 0, bool $valide = true) {
        $req = Mch::select('mch_cid', 'mch_mid', 'mch_type', 'mch_nb', 'mch_prix', 'mbr_pseudo');
        $req->join('mbr', 'mch_mid', 'mbr_mid');
        if ($mid) {
            $req->where('mch_mid', '!=', $mid);
        }
        if ($type) {
            $req->where('mch_type', '=', $type);
        }
        $req->where('mch_etat', ($valide ? COM_ETAT_OK : COM_ETAT_ATT))
                ->orderBy('mch_type', 'asc')->orderBy('mch_nb', 'desc')->orderBy('mch_prix', 'asc');
        return $req->get()->toArray();
    }

    /**
     * convert getRes info into total and avg
     * @param array $com_array
     * @return [total_ventes => $val1, ventes => [mch_nb => $nb, mch_prix => $tot]]
     */
    static function makeSum(array $com_array) {
        $total = [0, 0, 0];
        foreach ($com_array as $value) {
            $total[1] += $value['mch_nb']; //ressoures au total contre ca
            $total[2] += $value['mch_prix']; //idem
        }
        $total[0] = round($total[2] / $total[1], 2);
        return ['total_ventes' => count($com_array), 'ventes' => $total];
    }

    static function getPrice(int $type) {

        $tmp = Mch::where('mch_type', $type)->where('mch_etat', COM_ETAT_OK)
                        ->get()->toArray();
        if (!$tmp)
            return [];
        $total = [1 => 0, 2 => 0];
        foreach ($tmp as $value) {
            $total[1] += $value['mch_nb'];
            $total[2] += $value['mch_prix'];
            $prix_unit = $value['mch_prix'] / $value['mch_nb'];
            if (!isset($min) || $prix_unit < $min)
                $min = $prix_unit;
            if (!isset($max) || $prix_unit > $max)
                $max = $prix_unit;
        }

        if (!$total[1])
            $avg = 0;
        else
            $avg = round($total[2] / $total[1]);

        return ['min' => round($min, 2), 'max' => round($max, 2), 'avg' => $avg];
    }

    /**
     * acheter la vente $cid
     * @param int $cid
     * @return type
     */
    static function achat(int $cid) {
        return Mch::where('mch_cid', $cid)->where('mch_etat', COM_ETAT_OK)
                        ->update(['mch_etat' => COM_ETAT_ACH]);
    }

    /**
     * ajouter une vente au marché
     * @param int $mid
     * @param int $type
     * @param int $nb
     * @param int $prix
     * @return type
     */
    static function add(int $mid, int $type, int $nb, int $prix) {

        $request = ['mch_mid' => $mid,
            'mch_type' => $type,
            'mch_nb' => $nb,
            'mch_prix' => $prix,
            'mch_time' => DB::raw('NOW()'),
            'mch_etat' => COM_ETAT_ATT];
        return Mch::insertGetId($request);
    }

    /**
     * supprimer une vente en cours
     * @param int $mid
     * @param array $cid
     * @return type
     */
    static function del(int $mid, array $cid = []) {
        if (empty($cid))
            return Mch::where('mch_mid', $mid)
                            ->where('mch_etat', '!=', COM_ETAT_ACH)
                            ->delete();
        else
            return Mch::where('mch_mid', $mid)
                            ->whereIn('mch_cid', $cid)
                            ->where('mch_etat', '!=', COM_ETAT_ACH)
                            ->delete();
    }

}
