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

    /* ajouter un héros */

    static function add(int $mid, string $name, int $type, int $lid, int $vie) {

        $request = ['hro_mid' => $mid,
            'hro_lid' => $lid,
            'leg_type' => $type,
            'leg_name' => $name,
            'hro_vie' => $vie,
            'hro_xp' => 0 ];

        return Hro::insertGetId($request);
    }

    /* supprimer un héros */
    static function del(int $mid) {

        return Hro::where('hro_mid', $mid)->delete();
    }

    /**
     * editer un héros. et l'unité liée.
     * @global type $_user
     * @param int $mid
     * @param array $new
     * @return boolean|int
     */
    static function edit(int $mid, array $new = []){
        
	$request = [];
        foreach ($new as $key => $val) {
            if ($key == 'bonus_to'){
                $request['to'] = $val;
            } else {
                $request["hro_$key"] = $val;
            }
        }
        
	if(!$mid || empty($request))
		return 0; // aucune modif nécessaire

	if(isset($new['lid'])) { // transfer du héros de légions
		global $_user;
		// enlever l'unité de la légion actuelle, il suffit de supprimer le rang
		$rang = get_conf_gen($_user['race'], 'unt', $_user['hro_type'], 'rang');
                Unt::where([['unt_lid', $_user['hro_lid']], ['unt_rang', $rang]])->delete();
		// ajouter l'unité dans la nouvelle légion
                Unt::edit($new['lid'], [$rang => [ $_user['hro_type'] => 1]]);
	}

        Hro::where('hro_mid', $mid)->update($request);
        return true;

    }
    
    /**
     * utiliser une compétence
     * @param int $mid
     * @param int $bonus_id
     * @param int $tours = durée de validité ( minutes )
     * @param int $hro_xp = xp restant après dépense
     */
    static function bonus(int $mid, int $bonus_id, int $tours, int $hro_xp){

        $request = ['hro_bonus' => $bonus_id];
        if($bonus_id != 0){
            $request['hro_bonus_from'] = DB::raw('NOW()');
            $request['hro_bonus_to'] = DB::raw("DATE_ADD(NOW(), INTERVAL $tours MINUTE)");
            $request['hro_xp'] = $hro_xp;
        }
        Hro::where('hro_mid', $mid)->update($request);
    }

}
