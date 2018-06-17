<?php

/**
 * Alliances
 * lien avec le chef d'alliance : al_mid = mbr_id
 * lien avec les autres membres : al_aid = al_mbr.ambr_aid
 */
class Al extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'al';

    
    static function get($cond) {
        if(is_numeric($cond)){
            
            /* get by $al_aid */
            $result = Al::where('al_aid', $cond)->get()->toArray();
            return (empty($result) ? false : $result[0]);
            
        } else if(is_array($cond)) {

            $limite1 = 0; $limite2 = 0;
            $limite = 0;
            $aid = 0; $name = 0;

            if(isset($cond['limite1'])) {
                    $limite1 = protect($cond['limite1'], "uint");
                    $limite++;	
            }
            if(isset($cond['limite2'])) {
                    $limite2 = protect($cond['limite2'], "uint");
                    $limite++;	
            }
            if(isset($cond['name']))
                    $name = protect($cond['name'], "string");

            $req = Al::select('al_aid','al_name','al_nb_mbr','al_mid',
                    'mbr_pseudo','mbr_race', 'mbr_gid', 'al_points','al_open');
            if(!$limite)
                    $req->addSelect('al_descr,al_rules');

            $req->join('mbr', 'al.al_mid', 'mbr.mbr_mid');

            if(isset($cond['aid']))
                $req->where('al_aid', isset($cond['aid']));
            if($name)
                $req->where('al_name', 'LIKE', "%$name%");

            $req->orderBy('al_points', 'desc');


            if ($limite){
                if ($limite == 2){
                    $req->offset($limite1)->limit($limite2);
                }else{
                    $req->limit($limite1);
                }
            }

            return $req->get()->toArray();
        }
    }


    /* supprimer une alliance */
    static function del(int $aid) {
        return Al::where('al_aid', $aid)->delete();
    }

    /**
     * editer une alliance
     * @global type $_user
     * @param int $mid
     * @param array $new
     * @return boolean|int
     */
    static function edit(int $aid, array $edit = []){
        
	$request = [];
        if(isset($edit['open'])) {
		$request['al_open'] = $edit['open'];
	}
	if(isset($edit['nb_mbr'])) {
            $request['al_nb_mbr'] = DB::raw("al_nb_mbr + ?", [$edit['nb_mbr']]);
	}
	if(isset($edit['mid'])) {
            $request['al_mid'] = $edit['mid'];
	}
	if(isset($edit['name'])) {
            $request['al_name'] = $edit['name'];
	}
	if(isset($edit['descr'])) {
            $request['al_descr'] = $edit['descr'];
	}
	if(isset($edit['rules'])) {
            $request['al_rules'] = $edit['rules'];
	}
	if(isset($edit['diplo'])) {
            $request['al_diplo'] = $edit['diplo'];
	}
        Al::where('al_aid', $aid)->update($request);
    }
  
    	
    /**
     *  création nouvelle alliance + image logo
     * @param int $mid
     * @param string $nom
     * @return type
     */
    static function add(int $mid, string $name){

        $request = ['al_mid' => $mid,
            'al_name' => $name,
            'al_descr' => '',
            'al_rules' => ''];

        $aid = Al::insertGetId($request);
	
	$im = imagecreatefrompng(ALL_LOGO_DIR.'0.png');
	imagepng($im, ALL_LOGO_DIR."$aid.png");
	make_aly_thumb($aid,imagesx($im),imagesy($im));
	
	return $aid;
    }

}
