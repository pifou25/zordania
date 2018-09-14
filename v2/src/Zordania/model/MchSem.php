<?php

/**
 * Marché
 * lien membre : mch_mid = mbr_mid
 */
class MchSem extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'mch_sem';

    static function get(int $res = 0, int $debut = 0) { // cours entre J et J+7
        $fin = $debut + 7;
        $req = MchSem::whereRaw('msem_date BETWEEN (NOW() - INTERVAL ? DAY) AND (NOW() - INTERVAL ? DAY)', [$fin, $debut]);
        if ($res) {
            $req->where('msem_res', $res);
        }
        return $req->orderBy('msem_res', 'asc')->orderBy('msem_date', 'asc')->take(112)->get()->toArray();
    }

    static function edit(int $res, float $cours, string $date) {

        return MchSem::where('msem_res', $res)->where('msem_date', $date)->update(['msem_cours' => $cours]);
    }

}
