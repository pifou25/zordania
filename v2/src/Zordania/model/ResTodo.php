<?php

class ResTodo extends Illuminate\Database\Eloquent\Model {

    // override table name
    protected $table = 'res_todo';

    // Met des ressource en création dans l'ordre du tableau
    static function add(int $mid, array $res) {

        if ($res) {
            $requests = [];

            foreach ($res as $type => $number){
                array_push($requests, [
                    'rtdo_mid' => $mid,
                    'rtdo_type' => protect($type, 'uint'),
                    'rtdo_nb' => protect($number, 'uint')
                ]);
            }

            if (count($res) == 1){
                $requests = $requests[0];
            }

            return ResTodo::insertGetId($requests);
        }
        return false;
    }

    // Annule la création d'une ressource
    static function cancel(int $mid, int $res, int $number) {
        return ResTodo::where('rtdo_mid', '=', $mid)->decrement($res, $number);
    }

    /* Ressources en cours du joueur */
    static function get(int $mid, array $cond = []) {
        $res = [];
        $rid = 0;

        if (isset($cond['res']))
            $res = protect($cond['res'], 'array');
        if (isset($cond['rid']))
            $rid = protect($cond['rid'], 'uint');

        $req = ResTodo::select(['rtdo_id', 'rtdo_type', 'rtdo_nb'])
                ->where('rtdo_mid', '=', $mid)
                ->where('rtdo_nb', '>', 0);

        if ($rid)
            $req->where('rtdo_id', '=', $rid);

        if ($res) {
            $list = [];
            foreach ($res as $type)
                array_push($list, protect($type, 'uint'));

            $req->where('rtdo_type', 'IN', $list);
        }
        $req->orderBy('rtdo_id', 'asc');
        return $result = $req->get();
    }

}
