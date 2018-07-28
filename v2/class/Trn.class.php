<?php

/**
* terrains
*/
class Trn extends Illuminate\Database\Eloquent\Model {
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
	
    // override table name
    protected $table = 'trn';

    /* Récupère les terrains du joueur */
    static function get(int $mid, array $filter = null)
    {
            $row = Trn::where('trn_mid', '=', $mid)->get();
            if(count($row) == 0) {
                return [];
            }
            $row = $row[0];
            $result = ['1' => $row['trn_type1'],
                    '2' => $row['trn_type2'],
                    '3' => $row['trn_type3'],
                    '4' => $row['trn_type4'],
                    '5' => $row['trn_type5'],
            ];
            if($filter){
                    return array_intersect_key($result, array_flip($filter));
            }else{
                    return $result;
            }
    }


    // incrémente les ressources
    static function mod(int $mid, array $trn, float $factor = 1)
    {
        if ($mid && $trn)
        {
            $req = Trn::where('trn_mid', '=', $mid);
            foreach(self::factor($trn, $factor) as $type => $number){
                $req->increment($type, $number);
            }
            return $req->get();
        }
        return true;
    }

    // Modifie les ressources d'un membre
    static function edit($mid, $trn, $factor = 1)
    {
        if ($mid && $trn)
        {
            return Trn::where('trn_mid', '=', $mid)->update(self::factor($trn, $factor))->get();
        }
        return true;
    }

    /**
     * génère le nom des colonnes de la table zrd_res.
     * applique un facteur multiplicatif sur la quantité de ressources
     * @param array $trn
     * @param float $factor
     * @return type
     */
    static function factor(array $trn, float $factor = 1){
        $result = [];
        foreach($trn as $type => $number){
            if($number && $factor){
                $result['trn_type' . protect($type, 'uint')] = protect($number, 'int') * $factor;
            }
        }
        return $result;
    }

    
    /* Quand on crée un membre */
    static function init(int $mid)
    {
        $trn = self::factor(Session::$SES->getConf('race_cfg', 'debut', 'trn'));
        return Trn::insertGetId(array_merge(['trn_mid' => $mid], $trn));
    }
    
    /* Quand on le vire */
    static function clear($mid)
    {
        return Trn::where('trn_mid', '=', $mid)->delete();
    }

}
?>