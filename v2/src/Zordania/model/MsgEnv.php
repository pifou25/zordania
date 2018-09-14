<?php

/**
 * messages envoyés
 */
class MsgEnv extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'msg_env';


static function get(int $mid, int $msgid = 0) {

	$req = "menv_titre,menv_id,menv_mid,menv_to, ";
	$req.= "_DATE_FORMAT(menv_date) as menv_date_formated,";
	$req.= "mbr_pseudo,mbr_gid, IFNULL(mrec_readed,1) as mrec_readed";
	if($msgid)
		$req.= ",menv_texte";

        // replace date formatting:
        $sql = session::$SES->parseQuery($req);
        
        $req = MsgEnv::selectRaw($sql);
        $req->join('mbr', 'menv_to', 'mbr_mid');
        $req->leftJoin('msg_rec', 'menv_mrec_id', 'mrec_id');
        $req->where('menv_mid', $mid);
        
	if ($msgid){
            $req->where('menv_id', $msgid);
        }else{
            $req->orderBy('menv_date', 'desc');
        }
	return $req->get()->toArray();
}

/**
 * useless - check flood sending message
 * @param int $mid
 * @return bool
 */
static function flood_msg(int $mid) {

    return (MsgEnv::where('menv_mid', $mid)
            ->whereRaw('menv_date > (NOW() - INTERVAL ? SECOND)', [MSG_FLOOD_TIME])
            ->count() == 0);
}

static function del(int $mid, $msgid = 0)
{
        if ($msgid != null && !is_array($msgid)) {
            return MsgEnv::del($mid, [$msgid]);
        }
        // $msgid is null or is array
        if ($msgid == null || count($msgid) == 0) {
            $return = MsgEnv::where('menv_mid', $mid)->delete();
        } else {
            $return = MsgEnv::where('menv_mid', $mid)->whereIn('menv_id', $msgid)->delete();
        }
}

    static function add(int $mid, int $mid2, int $msgid, string $titre, string $text) {

        $request = ['menv_mid' => $mid,
            'menv_to' => $mid2,
            'menv_mrec_id' => $msgid,
            'menv_date' => DB::raw('NOW()'),
            'menv_titre' => $titre,
            'menv_texte' => $text];

        return MsgEnv::insertGetId($request);
    }

}