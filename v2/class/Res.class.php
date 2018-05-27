<?php

use Illuminate\Database\Eloquent\Model;

/**
* resources
*/
class Res extends Model {
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /* Récupère les ressources du joueur */
    static function get(int $mid, array $filter = null)
    {
            $row = Res::where('res_mid', '=', $mid)->get();
            if(count($row) == 0) {
                return [];
            }
            $row = $row[0];
            $result = ['1' => $row['res_type1'],
                    '2' => $row['res_type2'],
                    '3' => $row['res_type3'],
                    '4' => $row['res_type4'],
                    '5' => $row['res_type5'],
                    '6' => $row['res_type6'],
                    '7' => $row['res_type7'],
                    '8' => $row['res_type8'],
                    '9' => $row['res_type9'],
                    '10' => $row['res_type10'],
                    '11' => $row['res_type11'],
                    '12' => $row['res_type12'],
                    '13' => $row['res_type13'],
                    '14' => $row['res_type14'],
                    '15' => $row['res_type15'],
                    '16' => $row['res_type16'],
                    '17' => $row['res_type17'],
            ];
            if($filter){
                    return array_intersect_key($result, array_flip($filter));
            }else{
                    return $result;
            }
    }

    // incrémente les ressources
    static function mod(int $mid, array $res, float $factor = 1)
    {
        if ($mid && $res)
        {
            $req = Res::where('res_mid', '=', $mid);
            foreach(self::factor($res, $factor) as $type => $number){
                $req->increment($type, $number);
            }
            return $req->get();
        }
        return true;
    }

    // Modifie les ressources d'un membre
    static function edit($mid, $res, $factor = 1)
    {
        if ($mid && $res)
        {
            return Res::where('res_mid', '=', $mid)->update(self::factor($res, $factor))->get();
        }
        return true;
    }

    /**
     * génère le nom des colonnes de la table zrd_res.
     * applique un facteur multiplicatif sur la quantité de ressources
     * @param array $res
     * @param float $factor
     * @return type
     */
    static function factor(array $res, float $factor = 1){
        $result = [];
        foreach($res as $type => $number){
            if($number && $factor){
                $result['res_type' . protect($type, 'uint')] = protect($number, 'int') * $factor;
            }
        }
        return $result;
    }

    
    /* Quand on crée un membre */
    static function init(int $mid)
    {
        $res = self::factor(get_conf('race_cfg', 'debut', 'res'));
        return Res::insertGetId(array_merge(['res_mid' => $mid], $res));
    }
    
    /* Quand on le vire */
    static function clear($mid)
    {
        return Res::where('res_mid', '=', $mid)->delete();
    }

}
?>