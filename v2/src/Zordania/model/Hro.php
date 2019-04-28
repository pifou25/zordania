<?php

/**
 * Héros
 * lien avec les membres : hro_mid = mbr_id
 * lien avec la légion : hro_lid = leg_id
 */
class Hro extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'hero';

    /* un seul héros par joueur */

    static function get(int $mid) {
        $result = Hro::where('hro_mid', '=', $mid)->get()->toArray();
        return (empty($result) ? false : $result[0]);
    }

    /**
     * former un héros.
     * payer le prix configuré (ressources et unités). 
     * ajouté le héros au village 
     * il faut avoir vérifié la dispo des ressources & unités
     * @param int $mid
     * @param string $name
     * @param int $type
     * @return int
     */
    function add(int $mid, string $name, int $type) {

        // si l'id qu'on a récupéré c'est pas celui d'un héros ... Ben on lui dit que c'est pas bien de faire ça ! 
        if (Session::$SES->getConf('unt', $type, 'role') != TYPE_UNT_HEROS)
            return false;
        // récpérer l'id de la légion au village
        $lid = Leg::get($mid, Leg::ETAT_VLG)[0]['leg_id'];
        $vie = Session::$SES->getConf('unt', $type, 'vie');

        // prix en unités de la légion
        $edit_unt = Session::$SES->getConf('unt', $type, 'prix_unt');
        // prix en ressources
        $prix_res = Session::$SES->getConf('unt', $type, 'prix_res');

        // ajouter le héros, l'unité et payer le prix
        $request = ['hro_mid' => $mid,
            'hro_lid' => $lid,
            'leg_type' => $type,
            'leg_name' => $name,
            'hro_vie' => $vie,
            'hro_xp' => 0];
        Hro::insertGetId($request);

        $edit_unt[$type] = -1; // ajouter le héros comme unité
        Unt::editVlg($mid, $edit_unt, -1); // prix unités & héros
        Res::mod($mid, $prix_res, -1); // ressources payées
        // maj mbr
        Mbr::edit($mid, ['lmodif_date' => true]);
        return true;
    }

    /**
     * supprimer le héros et l'unité. pas de remboursement.
     * @param int $lid
     * @param int $type
     * @return boolean
     */
    function del(int $lid, int $type) {

        if (!$lid || !$type)
            return false;

        // supprimer l'unité dans la légion
        $rang = Session::$SES->getConf('unt', $type, 'rang');
        Unt::edit($lid, [$rang => [$type => -1]]);
        // supprimer le héros
        Hro::where('hro_lid', $lid)->delete();
        return true;
    }

    /**
     * editer un héros. et l'unité liée.
     * @param int $mid
     * @param array $new
     * @return boolean|int
     */
    static function edit(int $mid, array $new = []) {

        $request = [];
        foreach ($new as $key => $val) {
            if ($key == 'bonus_to') {
                $request['to'] = $val;
            } else if ($key == 'nrj') {
                $request['hro_xp'] = $val;
            } else {
                $request["hro_$key"] = $val;
            }
        }

        if (!$mid || empty($request))
            return 0; // aucune modif nécessaire

        if (isset($new['lid'])) { // transfer du héros de légions
            // enlever l'unité de la légion actuelle, il suffit de supprimer le rang
            $rang = Config::get(session::$SES->get('race'), 'unt', session::$SES->get('hro_type'), 'rang');
            Unt::where([['unt_lid', session::$SES->get('hro_lid')], ['unt_rang', $rang]])->delete();
            // ajouter l'unité dans la nouvelle légion
            Unt::edit($new['lid'], [$rang => [session::$SES->get('hro_type') => 1]]);
        }

        Hro::where('hro_mid', $mid)->update($request);
        return true;
    }

    /**
     * activer / désactiver un bonus
     * Pour 'désactiver' un bonus, mettre $bonus_id à 0.
     * @param int $mid
     * @param int $bonus_id
     * @return boolean
     */
    static function bonus(int $mid, int $bonus_id) { // 
        $prix_xp = Session::$SES->getConf("comp", $bonus_id, "prix_xp");
        $array_hero = Hro::get($mid);

        // savoir si y'a assez d'xp pour payer le cout du bonus ou non.
        if ($prix_xp <= $array_hero[0]['hro_xp'] or $bonus_id == 0) {

            $tours = Session::$SES->getConf("comp", $bonus_id, "tours"); //délais du bonus.
            /* ZORD_SPEED = durée d'un tour en minutes */
            $tours *= ZORD_SPEED;
            $request = ['hro_bonus' => $bonus_id];
            if ($bonus_id != 0) {
                $request['hro_bonus_from'] = DB::raw('NOW()');
                $request['hro_bonus_to'] = DB::raw("DATE_ADD(NOW(), INTERVAL $tours MINUTE)");
                $request['hro_xp'] = $array_hero[0]['hro_xp'] - $prix_xp;
            }
            Hro::where('hro_mid', $mid)->update($request);

            // maj mbr
            Mbr::edit($mid, ['lmodif_date' => true]);
            return true;
        } else
            return false;
    }

}
