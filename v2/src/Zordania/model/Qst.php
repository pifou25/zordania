<?php

namespace Zordania\model;

use session;

/**
 * Liste des quetes en cours et termnées des joueurs
 */
class Qst extends \Illuminate\Database\Eloquent\Model {

    // override table name
    protected $table = 'qst';

    /**
     * rechercher la 1ère quête à effectuer pour un membre
     * @param type $mid
     */
    public static function get(int $mid){
        
        $result = Qst::where('qst_mid', $mid)->whereNull('finished_at')
                ->orderBy('created_at', 'asc')->take(1)->get()->toArray();
        
        if(!empty($result)){
            return self::fillQst($result[0]);
        }
        
        // toutes les quêtes non faites par $mid
        $qsts = QstCfg::whereNotIn('cfg_id', function($query) use ($mid) {
            $query->select('qst_cfg_id')
                    ->from('qst')
                    ->where('qst_mid', $mid);
        })->get()->toArray();

        // rechercher une nouvelle quête ...
        foreach($qsts as $qst){
            // tester chaque param
            if(!session::$SES->checkParam( $qst['cfg_param1'], $qst['cfg_value1']))
                continue;

            if(!session::$SES->checkParam( $qst['cfg_param2'], $qst['cfg_value2']))
                continue;

            if(!session::$SES->checkParam( $qst['cfg_param3'], $qst['cfg_value3']))
                continue;

            if(!session::$SES->checkParam( $qst['cfg_param4'], $qst['cfg_value4']))
                continue;

            // résultat valide: l'ajouter comme quete en cours
            $request = ['qst_cfg_id' => $qst['cfg_id'],
                'qst_mid' => $mid];
            $id = Qst::insert($request);
            
            $result = Qst::where('qst_id', $id)->get()->toArray();
            if(empty($result[0])){
                return self::fillQst($result[0]);
            }

        }
        
        return false; // aucune quête disponible    
    }
    
    /**
     * rajouter la config dans l'array de quete
     * @param array $qst
     * @return type
     */
    private static function fillQst(array $qst){
        if(empty($qst['read_at'])){
            $qst['display'] = true;
        }else{
            $qst['dt_read_at'] = new DateTime($qst['read_at']);
        }
        $cfg = QstCfg::get($qst['qst_cfg_id']);
        return array_merge( $qst, $cfg);
    }
    
    /**
     * rechercher toutes les quêtes d'un membre
     * @param type $mid
     */
    public static function getAll(int $mid){
        
        return Qst::join('qst_cfg', 'qst_cfg_id', 'cfg_id')
                ->select('qst.*', 'cfg_subject')
                ->where('qst_mid', $mid)->orderBy('created_at', 'desc')->get()->toArray();

    }
}