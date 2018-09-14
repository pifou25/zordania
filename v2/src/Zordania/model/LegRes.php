<?php

/**
 * butin des Légions
 * lien avec les légionss : lres_lid = leg_id
 */
class LegRes extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'leg_res';


    /* del ressources d'une légion */
    static function del(int $lid){
        return LegRes::where('lres_lid', $lid)->delete();
    }

    static function get(int $mid, int $lid = 0){
        if($lid == 0){
            return LegRes::whereIn('lres_lid', function($query) use ($mid) {
                $query->select('leg_id')
                        ->from('leg')
                        ->where(['leg_mid' => $mid]);
            })->get()->toArray();
        }else{
            return LegRes::where('lres_lid', $lid)->get()->toArray();
        }
    }
    
    static function edit(int $lid, array $res, int $factor = 1){

        //$legRes = LegRes::get(0, $lid);
        //$legRes = index_array( $legRes, 'lres_type');
        $legRes = LegRes::where('lres_lid', $lid)->get()->keyBy('lres_type');
	foreach($res as $type => $nb) {
            if(isset($legRes[$type])){ // incrementer nb ressources
                LegRes::where('lres_lid', $lid)->where('lres_type', $type)->increment('lres_nb', $nb * $factor);
            } else { // insert
                $request = ['lres_lid' => $lid,
                    'lres_type' => $type,
                    'lres_nb' => $nb * $factor];
                LegRes::insertGetId($request);
            }
	}
    }

    /* del unités de toutes les légions d'un membre */
    static function delAll(int $mid){
        return LegRes::whereIn('lres_lid', function($query) use ($mid) {
            $query->select('leg_id')
                    ->from('leg')
                    ->where(['leg_mid' => $mid]);
        })->delete();
    }
 
}
