<?php

/**
 * Parametrage des quetes
 */
class QstCfg extends Illuminate\Database\Eloquent\Model {

    // override table name
    protected $table = 'qst_cfg';

    const PARAMS = [
        0 => 'Aucun paramètre',
        1 => 'Race',
        2 => 'Précédente quête terminée',
        3 => 'Former une unité',
        4 => 'Construire un bâtiment',
        5 => 'Découvrir une recherche',
        6 => 'Fabriquer un objet ou une ressource',
        7 => 'Vendre ou acheter au commerce',
        8 => 'Rejoindre une alliance',
        9 => 'Mener une attaque',
        10 => 'Défendre son village',
        11 => 'Former une légion',
        12 => 'Se présenter sur le forum',
        13 => 'Former un héros',
        14 => 'Utiliser une compétence du héros',
    ];

    public static function get(int $id) {
        $res = QstCfg::where('cfg_id', $id)->get()->toArray();
        if(empty($res)) return [];
        $res = $res[0];
        $post = FrmPost::getById($res['cfg_pid']);
        if($res['cfg_mid']){
            $pnj = Mbr::get(['mid' => $res['cfg_mid']]);
            if(!empty($pnj)){
                $post['poster_id'] = $pnj[0]['mbr_mid'];
                $post['username'] = $pnj[0]['mbr_pseudo'];
                $post['mbr_gid'] = $pnj[0]['mbr_gid'];
            }
        }

        return array_merge($res, $post);
    }

    /**
     * inserer toutes les quetes non paramétrées
     */
    public static function majAll() {

        $sql = 'INSERT INTO zrd_qst_cfg (cfg_pid, cfg_tid, cfg_subject)
    SELECT p.id, topic_id, subject FROM zrd_frm_posts p 
      INNER JOIN zrd_frm_topics t ON topic_id = t.id
    WHERE forum_id = ?
    AND NOT EXISTS (SELECT 1 FROM zrd_qst_cfg WHERE p.id = cfg_pid AND topic_id = cfg_tid);';

        DB::insert($sql, [QUETES_FID]);
        
    }

}
