<?php

/**
 * signalement de message privés
 * lien avec le msg: msg_rec.mrec_id = sign_msgid
 * lien avec admin: mbr.mbr_mid = sign_admid
 */
class Sign extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'sign';

    /**
     * signaler un message
     * @param int $msgid
     * @param string $com
     * @return type
     */
    static function add(int $msgid, string $com) {

        $request = ['sign_msgid' => $msgid,
            'sign_debut' => DB::raw('NOW()'),
            'sign_admid' => 0,
            'sign_com' => $com];
        Sign::insertGetId($request);
        return MsgRec::sign($msgid);
    }

    static function del(int $mid){
        return Sign::whereIn('sign_msgid', function($query) use ($mid) {
            $query->select('mrec_id')
                    ->from('msg_rec')
                    ->where('mrec_mid', $mid);
        })->delete();
    }
    
    /**
     *  assigner le signalement à un admin
     * @param int $sid
     * @param int $mid
     * @param string $com
     * @return boolean
     */
    static function assign(int $sid, int $mid, string $com = '') {

        $request = ['sign_admid' => $mid,
            'sign_fin' => DB::raw('NOW()')];
        if ($com != '') {
            $request['sign_com'] = DB::raw("CONCAT(sign_com, '\n<hr/>\n ? ')", [$com]);
        }
        return Sign::where('sign_id', $sid)->update($request);
    }

    static function comm(int $sid, string $com) {

        $result = MsgRec::getSign(['id' => $sid]);
        if($result){
            $request['sign_com'] = $result[0]['sign_com'] . "\n<hr/>\n" . $com;
            Sign::where('sign_id', $sid)->update($request);
        }
        return $result;
    }

    static function count(array $cond = []) {

        if (isset($cond['admid'])) {
            return Sign::where('sign_admid', $cond['admid'])->count();
        } else {
            return Sign::selectRaw('*')->count();
        }
    }

}
