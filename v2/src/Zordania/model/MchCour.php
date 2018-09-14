<?php

/**
 * Marché
 * lien membre : mch_mid = mbr_mid
 */
class MchCour extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'mch_cours';

    static function get(int $res = 0) {
        if ($res) {
            return MchCour::where('mcours_res', $res)->orderBy('mcours_res', 'asc')->get()->toArray();
        } else {
            return MchCour::orderBy('mcours_res', 'asc')->get()->toArray();
        }
    }

    static function edit(int $res, float $cours) {
        return MchCour::where('mcours_res', $res)->update(['mcours_cours' => $cours]);
    }

}
