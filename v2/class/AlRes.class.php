<?php

/**
 * grenier d'Alliances
 * lien avec l'alliance : al.al_aid = ares.ares_aid
 */
class AlRes extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'al_res';

    /**
     * grenier 'normalisé' [$type => $nb]
     * @param int $aid
     * @param int $type
     */
    static function get(int $aid, int $type = 0) {

        if (!$type) {
            $result = AlRes::where('ares_aid', $aid)->orderBy('ares_type', 'asc')->get()->toArray();
        } else {
            $result = AlRes::where('ares_aid', $aid)->where('ares_type', $type)->get()->toArray();
        }
        foreach ($result as $row)
            $return[$row['ares_type']] = $row['ares_nb'];
        return $return;
    }

    /**
     *  prendre/retirer au grenier 1 ressource 
     * @param int $aid
     * @param int $mid
     * @param int $type
     * @param int $nb
     * @return type
     */
    function add(int $aid, int $mid, int $type, int $nb) {
        return AlRes::edit($aid, $mid, array($type => $nb));
    }

    /**
     *  prendre/retirer au grenier plusieurs ressources 
     * @param int $aid
     * @param int $mid
     * @param array $res
     * @param int $coef
     * @return type
     */
    static function edit(int $aid, int $mid, array $res, int $coef = 1) {

        $grenier = AlRes::get($aid);
        foreach ($res as $type => $nb) {
            if ($nb) {
                $nb = $nb * $coef;
                if(isset($grenier[$type])){
                    AlRes::where('ares_aid', $aid)->where('ares_type', $type)->increment('ares_nb', $nb);
                }else{
                    $request = ['ares_aid' => $aid,
                        'ares_type' => $type,
                        'ares_nb' => $nb];
                    AlRes::insertGetId($request);
                }

                AlResLog::add($aid, $mid, $type, $nb);
            }
        }
    }

}
