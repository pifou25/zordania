<?php

namespace Zordania\model;

/**
 * Parametrage des quetes
 */
class QstCfg extends \Illuminate\Database\Eloquent\Model {

    // override table name
    protected $table = 'qst_cfg';

    private $mbr; // membre auteur de la quete
    private $post; // FrmPost du forum
    
    /**
     * PNJ auteur de la quête
     * @return Mbr
     */
    function mbr(){
        if($this->mbr == null){
            $this->mbr = $this->belongsTo('Mbr', 'cfg_mid', 'mbr_mid');
        }
        return $this->mbr;
    }
   
    /**
     * post du forum correspondant à la quête
     * @return FrmPost
     */
    function post(){
        if($this->post == null){
            $this->post = $this->belongsTo('FrmPost', 'cfg_pid');
        }
        return $this->post;
    }
    
    public static function get(int $id) {
        $res = QstCfg::where('cfg_id', $id)->get()->toArray();
        if(empty($res)) return [];
        $res = $res[0];
        $post = \FrmPost::getById($res['cfg_pid']);
        if($res['cfg_mid']){
            $pnj = \Mbr::get(['mid' => $res['cfg_mid']]);
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

        DB::insert($sql, [FORUM_QUETES]);
        
    }

}
