<?php

/**
 * Recherches
 * lien avec les membres : src_mid = mbr_id
 */
class SrcTodo extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'src_todo';

    /**
     * supprimer les recherches faites et à faire
     * @param int $mid
     * @return type
     */
    static function del(int $mid, int $type = 0) {

        $nb = Src::del($mid, $type);

        /* del les recherches a faire */
        $req = SrcTodo::where('stdo_mid', $mid);
        if ($type) {
            $req->where('stdo_type', $type);
        }
        return $nb + $req->delete();
    }

    static function get(int $mid, array $src = []) {


        $req = SrcTodo::where('stdo_mid', $mid);
        if (!empty($src)) {
            $req->whereIn('stdo_type', $src);
        }
        return $req->orderBy('stdo_time', 'asc')->get()->keyBy('stdo_type');
    }

    /* Rajouter la recherche dont la conf est $conf en prévision */

    static function add(int $mid, int $type) {

        Src::add($mid, $type);
        $tours = Session::$SES->getConf("src", $type, "tours");

        return SrcTodo::insertGetId(['stdo_mid' => $mid,
                    'stdo_type' => $type,
                    'stdo_tours' => $tours,
                    'stdo_time' => DB::raw('NOW()')]);
    }

}
