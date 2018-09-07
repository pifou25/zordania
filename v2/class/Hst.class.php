<?php

/* Historique */
define('HISTO_HRO_CP', 1);
define('HISTO_COM_ACH', 11);
define('HISTO_BTC_OK', 21);
define('HISTO_BTC_REP', 22);
define('HISTO_BTC_BRU', 23);
define('HISTO_SRC_DONE', 31);
define('HISTO_LEG_ARV', 41);
define('HISTO_LEG_ATQ_VLG', 42);
define('HISTO_LEG_ATQ_LEG', 43);
define('HISTO_LEG_VIDE_BACK', 44);
define('HISTO_LEG_IDLE', 45);
define('HISTO_MSG_NEW', 51);
define('HISTO_UNT_BOUFF', 61);
define('HISTO_PARRAIN_BONUS', 71);

/**
 * Historique - les événements
 * lien avec les membres : histo_mid = mbr_id et histo_mid2 = mbr_mid
 */
class Hst extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'histo';
    var $histos = array();

    function add(int $mid, int $mid2, int $type, array $vars = []) {
        $this->histos[$mid][] = array('mid2' => $mid2, 'type' => $type, 'vars' => $vars);
    }

    function __destruct() {
        if (empty($this->histos))
            return;

        foreach ($this->histos as $mid => $mid_array) {
            foreach ($mid_array as $value) {
                $request = ['histo_date' => DB::raw('NOW()'),
                    'histo_mid' => $mid,
                    'histo_mid2' => $value['mid2'],
                    'histo_vars' => base64_encode(serialize($value['vars'])),
                    'histo_type' => $value['type']];
                Hst::insertGetId($request);
            }
        }
    }

    static function get(int $mid, int $limite1 = 0) {

        $req = Hst::select('histo_hid','histo_mid2','mbr_pseudo','mbr_gid','histo_type','histo_vars')
                ->selectRaw(session::$SES->parseQuery("_DATE_FORMAT(histo_date) as histo_date_formated,"
         . "DATE_FORMAT(histo_date,'%Y-%m-%dT%H:%i:%s') as histo_date_rss"));
        if(session::$SES->get('decal') != '00:00:00'){
            $req->selectRaw('UNIX_TIMESTAMP(histo_date + INTERVAL ? HOUR_SECOND) AS histo_date', session::$SES->get('decal') );
        }else{
            $req->selectRaw('UNIX_TIMESTAMP(histo_date) AS histo_date');
        }
        $req->join('mbr', 'histo_mid2', 'mbr_mid')->where('histo_mid', $mid)
                ->orderBy('histo_date', 'desc');

        if ($limite1)
            $req->take($limite1);

        $array = $req->get()->toArray();
        foreach ($array as $key => $result) /* Rendre ça exploitable */
            if ($result['histo_vars']) {
                $array[$key]['histo_vars'] = safe_unserialize($result['histo_vars']);
                //$array[$key]['histo_vars'] = base64_decode(@unserialize($result['histo_vars']), true);
            }
        return $array;
    }

    static function clear(int $mid) {
        return Hst::where('histo_mid', $mid)->delete();
    }

}
