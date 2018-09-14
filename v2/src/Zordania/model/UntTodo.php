<?php

/**
 * unités
 * lien avec les légions - Leg
 * unt_lid = leg_id
 */
class UntTodo extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'unt_todo';

    /* toutes les unités */
    static function get(int $mid, array $cond = []) {

        $req = UntTodo::where([['utdo_mid', '=', $mid], ['utdo_nb', '>', 0]]);
	if(isset($cond['uid'])){
            $req->where('utdo_id', '=', $cond['uid']);
        }
	if(isset($cond['unt'])){
            $req->whereIn('unt_type', $cond['unt']);
        }

        $req->orderBy('utdo_id', 'asc');
	return $req->get()->toArray();
    }

    /* Annule des unités a faire */
    static function del(int $mid, int $uid, int $nb){
        return UntTodo::where([['utdo_mid', '=',  $mid], ['utdo_id', '=', $uid], ['utdo_nb', '>=', $nb]])
                ->decrementer();
    }

    /* quand on supprime un membre */
    static function clear(int $mid){
        return UntTodo::where('utdo_mid', '=', $mid)->delete();
    }
    
    static function add(int $mid, array $unt, int $factor = 1){

	if(empty($unt)){
            return;
        }

	foreach($unt as $type => $nb) {
            $request = ['utdo_mid' => $mid,
                 'utdo_type' => $type,
                 'utdo_nb' => $nb * $factor];
             UntTodo::insertGetId($request);
 	}
    }
    
}
