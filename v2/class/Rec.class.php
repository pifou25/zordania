<?php

/**
 * Recompense
 * lien avec le membre
 */
class Rec extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
// override table name
    protected $table = 'rec';

    static function get(int $mid, int $type = 0) {
        if (!$type) {
            return Rec::where('rec_mid', $mid)->get()->toArray();
        } else {
            return Rec::where('rec_mid', $mid)->where('rec_type', $type)->get()->toArray();
        }
    }

    static function del(int $mid, int $type = 0) {

        $req = Rec::where('rec_mid', $mid);
        if ($type) {
            $req->where('rec_type', $type);
        }
        return $req->limit(1)->delete();
    }

    static function add(int $mid, int $type) {

        $recs = Rec::get($mid, $type);
        if (empty($recs)) { // insert new rec
            $request = ['rec_mid' => $mid,
                'rec_type' => $type,
                'rec_nb' => 1];
            return Rec::insertGetId($request);
        } else { // increment rec
            return Rec::where('rec_mid', $mid)->where('rec_type', $type)->increment('rec_nb', 1);
        }
    }

    static function getMbr($rec) {
        return Rec::join('mbr', 'rec_mid', 'mbr_mid')->select('mbr_mid', 'mbr_pseudo')
                        ->where('rec_type', $rec)->get()->toArray();
    }

}
