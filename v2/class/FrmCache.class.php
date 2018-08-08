<?php

/**
 * forums : cache des recherches
 */
class FrmCache extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'frm_search_cache';

    static function get(int $id) {// retrouver une recherche en cache
        $row = FrmCache::where('id', $id)->where('ident', Session::$SES->get('mid'))->get()->toArray();
        if (!empty($row))
            return unserialize($row[0]['search_data']);
        else
            return false;
    }

    static function add(array $search) {// ajouter la recherche dans le cache
        // vider le cache des anciennes recherches - TODO dans cron
        // todo: ne peut pas marcher, compare un pseudo et un mid - à corriger
        FrmCache::whereNotIn('ident', function($query) {
            $query->select('ses_mid')->from('ses')->where('ses_mid', '<>', 1);
        })->delete();

        $search_id = mt_rand(1, 2147483647);
        $rqt = ['id' => $search_id,
            'ident' => Session::$SES->get('mid'),
            'search_data' => serialize($search)];
        FrmCache::insert($rqt);
        return $search_id;
    }

}
