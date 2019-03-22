<?php

/**
 * messages reçus
 * lien avec membre : mbr_id = mrec_from
 * 
 */
class MsgRec extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'msg_rec';

    static function get(int $mid, int $msgid = 0) {

        $sql = "mrec_titre,mrec_id,mrec_from,mrec_readed,";
        $sql .= "_DATE_FORMAT(mrec_date) as mrec_date_formated, msg_sign,";
        $sql .= "IFNULL(mbr_pseudo,mold_pseudo) AS mbr_pseudo,"
                . "ifnull(mbr_gid,1) AS mbr_gid,IFNULL(mbr_sign,'membre disparus') AS mbr_sign";
        if ($msgid)
            $sql .= ",mrec_texte";
        
        // replace date formatting:
        $raw = session::$SES->parseQuery($sql);

        $req = MsgRec::selectRaw($raw)->leftJoin('mbr', 'mrec_from', 'mbr_mid')
                ->leftJoin('mbr_old', 'mrec_from', 'mold_mid')
                ->where('mrec_mid', $mid);
        if ($msgid) {
            $req->where('mrec_id', $msgid);
        } else {
            $req->orderBy('mrec_date', 'desc');
        }

        return $req->get()->toArray();
    }

    static function count(int $mid){
        return MsgRec::where('mrec_mid', $mid)->where('mrec_readed', 0)->count();
    }

    static function add(int $mid, int $mid2, string $titre, string $text, bool $copy = true) {

        $request = ['mrec_mid' => $mid2,
            'mrec_from' => $mid,
            'mrec_date' => DB::raw('NOW()'),
            'mrec_titre' => $titre,
            'mrec_texte' => $text,
            'mrec_readed' => 0];

        $mrec_id = MsgRec::insertGetId($request);
        if($copy){
            MsgEnv::add($mid, $mid2, $mrec_id, $titre, $text);
        }
    }

    static function del(int $mid, $msgid = []) {
        if ($msgid != null && !is_array($msgid)) {
            return MsgRec::del($mid, [$msgid]);
        }

        // $msgid is null or is array
        if ($msgid == null || count($msgid) == 0) {
            $return = MsgRec::where('mrec_mid', $mid)->delete();
        } else {
            $return = MsgRec::where('mrec_mid', $mid)->whereIn('mrec_id', $msgid)->delete();
        }

        /* avec le msg reçu on supprime aussi les signalements */
        if ($msgid)
            return $return + Sign::whereIn('sign_msgid', $msgid)->delete();
        else
            return $return + Sign::del($mid);
    }

    static function mark(int $mid, int $msgid = 0) {

        $req = MsgRec::where('mrec_mid', $mid);
        if ($msgid) {
            $req->where('mrec_id', $msgid);
        }
        return $req->update(['mrec_readed' => 1]);
    }

    static function init($mid) {
        return (MsgRec::del($mid) + MsgEnv::del($mid));
    }

    static function addAll(int $mid, string $titre, string $text, array $grp = []) {

        /**
         * Wherever your Select may come from
         **/
        $select = Mbr::where('mbr_etat', MBR_ETAT_OK);
        if (!empty($grp)) {
                $select->whereIn('mbr_gid', $grp);
        }
        $select->selectRaw(' mbr_mid, ? ,NOW(), ? , ? ,0',  [$mid, $titre, $text]);
        
        /**
         * get the binding parameters
         **/ 
        $bindings = $select->getBindings();
        /**
         * now go down to the "Network Layer"
         * and do a hard coded select, Laravel is a little
         * stupid here
         */
        $sql = 'INSERT INTO zrd_msg_rec (mrec_mid, mrec_from, mrec_date, mrec_titre, mrec_texte, mrec_readed) '
                        . $select->toSql();

        DB::insert($sql, $bindings);

        MsgEnv::add($mid, $mid, 0, $titre, $text);
    }

    static function sign(int $msgid) {
        return MsgRec::where('mrec_id', $msgid)->where('msg_sign', 1)->count() == 1;
    }

    static function getSign(array $cond = []) {
        // liste des messages signalés
        
        if (isset($cond['id']))
            $id = protect($cond['id'], 'uint');
        else
            $id = 0;
        if (isset($cond['mrec_id']))
            $mrec_id = protect($cond['mrec_id'], 'uint');
        else
            $mrec_id = 0;
        if (isset($cond['pge']))
            $limit1 = protect($cond['pge'], 'uint') * LIMIT_PAGE;
        else
            $limit1 = 0;

        $sql = 'sign_id, zrd_msg.mrec_id, sign_admid, 
		_DATE_FORMAT(sign_debut) AS sign_debut, 
		_DATE_FORMAT(sign_fin) AS sign_fin, sign_com, 
		IFNULL(zrd_mbr2.mbr_pseudo,\'aucun\') AS sign_adm_pseudo, 
		_DATE_FORMAT(zrd_msg.mrec_date) as mrec_date_formated, 
		zrd_msg.mrec_titre, zrd_mbr.mbr_sign, ';
        $sql .= 'zrd_msg.mrec_mid, zrd_msg.mrec_from, '
                . 'zrd_mbr.mbr_pseudo AS sign_to_pseudo, zrd_mbr.mbr_gid AS sign_to_gid, '
                . 'zrd_fro.mbr_gid, zrd_fro.mbr_pseudo ';
        if ($id or $mrec_id)
            $sql .= ', zrd_msg.mrec_texte ';

        // replace date formatting:
        $raw = session::$SES->parseQuery($sql);

        $req = Sign::selectRaw($raw);
        $req->leftJoin('mbr as mbr2', 'sign_admid', 'mbr2.mbr_mid');
        $req->leftJoin('msg_rec as msg', 'sign_msgid', 'msg.mrec_id');
        $req->join('mbr as mbr', 'msg.mrec_mid', 'mbr.mbr_mid');
        $req->join('mbr as fro', 'msg.mrec_from', 'fro.mbr_mid');
        
        if (isset($cond['etat'])){
            $req->where('sign_etat', $cond['etat']);
        }
        if ($id){
            $req->where('sign_id', $id);
        }
        if ($mrec_id){
            $req->where('mrec_id', $mrec_id);
        }
        $req->orderBy('sign_debut', 'desc');

        // TODO : pagination ?
        //$req->skip($limit1)->take(LIMIT_PAGE)->toSql();
        return $req->get()->toArray();

    }

}
