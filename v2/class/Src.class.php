<?php

/**
 * Recherches
 * lien avec les membres : src_mid = mbr_id
 */
class Src extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'src';

    /* Ajoute une recherche pour de vrai */

    static function add(int $mid, int $type) {

        return Src::insertGetId(['src_mid' => $mid, 'src_type' => $type]);
    }

    /* Supprimer la recherche $type */

    static function del(int $mid, int $type = 0) {

        $req = Src::where('src_mid', $mid);
        if ($type) {
            $req->where('src_type', $type);
        }
        return $req->delete();
    }

    /* Récupere les recherches de $mid [ et de type $type ] */

    static function get(int $mid, array $src = []) {

        $req = Src::where('src_mid', $mid)
                ->whereNotIn('src_type', function($query) use ($mid) {
            $query->select('stdo_type')
            ->from('src_todo')
            ->where('stdo_mid', $mid);
        });
        if (!empty($src)) {
            $req->whereIn('src_type', $src);
        }
        return $req->get()->toArray();
    }

    
/* Quand on crée un membre */
static function init(int $mid) {
	$debut = get_conf("race_cfg", "debut", "src");
	foreach($debut as $type) {
		Src::add($mid, $type);
	}
}

}
