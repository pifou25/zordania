<?php

/**
 * unités
 * lien avec les légions - Leg
 * unt_lid = leg_id
 */
class Unt extends Illuminate\Database\Eloquent\Model {
    use \Zordania\model\HasCompositePrimaryKey;

    /**
     * composite primary key with 3 columns:
     */
    protected $primaryKey = ['unt_lid', 'unt_type', 'unt_rang'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'unt';

    /* toutes les unités d'une légion */
    static function get(int $lid) {
        $unts = Unt::where('unt_lid', '=', $lid)->get()->toArray();
        return index_array($unts, 'unt_type');
    }

    /* Initialisation des unités */
    static function init(int $lid) {

        // init des unités
        $debut = Session::$SES->getConf("race_cfg", "debut", "unt");
        Unt::edit($lid, $debut);
    }

    /* del unités d'une légion */
    static function del(int $lid){
        return Unt::where(['unt_lid' => $lid])->delete();
    }

    /* del unités de toutes les légions d'un membre */
    static function delAll(int $mid){
        return Unt::whereIn('unt_lid', function($query) use ($mid) {
            $query->select('leg_id')
                    ->from('leg')
                    ->where('leg_mid', $mid);
        })->delete();
    }

    static function editVlg(int $mid, array $unt, int $factor = 1){
        $lid = Leg::where('leg_mid', $mid)->where('leg_etat', Leg::ETAT_VLG)->first()->leg_id;
        if(empty($lid)){
            return false;
        }
        Unt::edit($lid, $unt, $factor);
        return true;
    }

    static function editBtc(int $mid, array $unt, int $factor = 1){
        $lid = Leg::get(['mid' => $mid, 'etat' => [Leg::ETAT_BTC]]);
        if(empty($lid)){
            return false;
        }
        Unt::edit($lid[0]['leg_id'], $unt, $factor);
        return true;
    }
    
    /**
     * incrémenter / décrémenter les unités d'une légion
     * @param int $lid
     * @param array $unt = [ rang => [ type => value ]]
     * @param int $factor
     */
    static function edit(int $lid, array $unt, int $factor = 1){

        $leg = Unt::get($lid);
	foreach($unt as $rang => $value) {
            if(!is_array($value)){ // si array simple: toutes les unités sur rang 0
                $unt1[0][$rang] = $value;
            }else{
                foreach($value as $type => $nb) {
                    if(isset($leg[$type])){ // incrementer nb d'unites
                        Unt::where([['unt_lid', $lid], ['unt_type', $type]])->increment('unt_nb', $nb * $factor);
                    } else { // inserer le nouveau rang
                        $request = ['unt_lid' => $lid,
                            'unt_type' => $type,
                            'unt_rang' => $rang,
                            'unt_nb' => $nb * $factor];
                        Unt::insertGetId($request);
                    }
                }
            }
	}
        if(isset($unt1)){
            Unt::edit($lid, $unt1, $factor);
        }
    }
    
}
