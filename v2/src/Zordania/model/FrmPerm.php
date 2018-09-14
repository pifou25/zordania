<?php

/**
 * forums et catégories
 * lien avec les membres
 * unt_lid = leg_id
 */
class FrmPerm extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'frm_forum_perms';

    /**
     * 
     * @param int $fid forum_id
     * @param int $gid group_id
     * @return array
     */
    static function get(int $fid, int $gid) {
        if ($fid && $gid) {
            $ret = FrmPerm::where('forum_id', $fid)->where('group_id', $fid)
                    ->get()->toArray();
            if (empty($ret)) // autorisé par défaut
                return ['read_forum' => 1, 'post_topics' => 1, 'post_replies' => 1];
            else
                return $ret[0];
        }
        return ['read_forum' => 0, 'post_topics' => 0, 'post_replies' => 0];
    }

    /**
     * check right on forum for group
     * @param int $fid forum_id
     * @param int $gid group_id
     * @param string $act [read|reply|create]
     * @return bool
     */
    static function can(int $fid, int $gid, string $act) : bool {
        $droits = FrmPerm::get($fid, $gid);
        switch($act){
            case 'read':
                return $droits['read_forum'];
            case 'reply':
                return $droits['post_replies'];
            default: // create topic:
                return $droits['post_topics'];
        }
    }
    
}
