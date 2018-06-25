<?php

/**
 * terrains
 */
class Btc extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'btc';

    /** count nb batiments en construction */
    static function getNbTodo(int $mid, array $btc = array()) {
        return Btc::getNb($mid, $btc, [BTC_ETAT_TODO]);
    }

    /** count nb batiments terminés sauf todo */
    static function getNbDone(int $mid, array $btc = array()) {
        return Btc::getNb($mid, $btc, [BTC_ETAT_OK, BTC_ETAT_DES, BTC_ETAT_REP, BTC_ETAT_BRU]);
    }

    /** count nb batiments actif */
    static function getNbActive(int $mid, array $btc = array()) {
        return Btc::getNb($mid, $btc, [BTC_ETAT_OK, BTC_ETAT_BRU]);
    }

    /* Nombres de bâtiments de ce type pour ce mid et ces etats */

    static function getNb(int $mid, array $btc = [], array $etat = []) {
        $predicate = DB::raw('COUNT(btc_id) as btc_nb');
        $req = Btc::select('btc_mid', 'btc_type', $predicate)->where('btc_mid', '=', $mid);
        if (!empty($btc))
            $req->whereIn('btc_type', $btc);
        if (!empty($etat))
            $req->whereIn('btc_etat', $etat);
        // indexer le resultat sur btc_type
        $result = $req->groupBy(['btc_mid', 'btc_type'])->get()->toArray();
        return index_array($result, 'btc_type');
    }

    /** tous les batiments termines, reparation, brule */
    static function getDone(int $mid, array $btc = array()) {
        return Btc::get($mid, [BTC_ETAT_OK, BTC_ETAT_REP, BTC_ETAT_BRU], $btc);
    }

    /* Bâtiments de ce type pour ce mid et ces états */

    static function get(int $mid, array $btc = array(), array $etat = array()) {
        $req = Btc::where('btc_mid', '=', $mid);
        if (!empty($btc))
            $req->whereIn('btc_id', $btc);
        if (!empty($etat))
            $req->whereIn('btc_etat', $etat);
        // indexer le resultat sur btc_id
        $result = $req->get()->toArray();
        return index_array($result, 'btc_id');
    }

    static function add(int $mid, $btc) {
        if (is_array($btc)) {

            if (empty($btc))
                return false;

            $result = [];
            foreach ($btc as $type => $value) {

                if (isset($values['etat']))
                    $etat = protect($values['etat'], "uint");
                else
                    $etat = BTC_ETAT_OK;

                if (isset($values['vie']))
                    $vie = protect($values['vie'], "uint");
                else
                    $vie = get_conf("btc", $type, "vie");

                $request = ['btc_mid' => $mid,
                    'btc_type' => protect($type, "uint"),
                    'btc_etat' => $etat,
                    'btc_vie' => $vie];

                $result[] = Btc::insertGetId($request);
            }
            // return list of insertedId:
            return count($result) > 1 ? $result : $result[0];
        }else if (is_numeric($btc) && settype($btc, 'integer')) {
            // insert one unique building
            return Btc::add($mid, [$btc => ['etat' => BTC_ETAT_TODO, 'vie' => 0]]);
        } else {
            // wrong parameter $btc
            throw new Exception('$btc wrong type : ' + print_r($btc, true));
        }
    }

    /* Quand on crée un membre */

    static function init(int $mid) {
        return Btc::add($mid, get_conf('race_cfg', 'debut', 'btc'));
    }

    /* Quand on le vire */

    static function clear(int $mid) {
        return Btc::where('btc_mid', '=', $mid)->delete();
    }

    /**
     * supprimer un batiment $bid.
     * si $bid=0 : supprimer tous les batiments du user $mid.
     */
    static function del(int $mid, int $bid = 0) {
        if ($bid == 0) {
            return Btc::clear($mid);
        } else {
            return Btc::where(['btc_mid' => $mid, 'btc_id' => $bid])->delete();
        }
    }

    /* Modifie l'état ou la vie de bâtiments */

    static function edit(int $mid, array $btc) {

        $clean = false;

        foreach ($btc as $bid => $values) {
            if (isset($values['vie'])) { /* Vie absolue */
                Btc::where('btc_bid', '=', $bid)->update(['btc_vie' => protect($values['vie'], "uint")])->get();
                $clean = true;
            } else if (isset($values['vie_comp'])) { /* Relative */
                Btc::where('btc_bid', '=', $bid)->increment('btc_vie', protect($values['vie_comp'], "uint"))->get();
            }
            if (isset($values['etat'])) { /* Etat (réparation, construction, etc ..) */
                Btc::where('btc_bid', '=', $bid)->update(['btc_etat' => protect($values['etat'], "uint")])->get();
            }
        }
        if ($clean) {
            Btc::where('btc_etat', '=', BTC_ETAT_TODO)->where('btc_vie', '=', 0)->delete();
        }
        return true;
    }

    /**
     * batiments militaires et leurs bonus
     * $btc_array = Btc::getDone($mid) = detail des batiments ($bid, vie, type...)
     */
    static function milit($btc_array, $race) {
        $nb = array();     // nb de batiments (type => nb) y compris non defensif
        $nb_def = array(); // nb de bat defensif & actifs seulement
        $def = array();    // liste des batiments defensifs & actifs (sauf donjon)
        $bonus = array('gen' => 0, 'bon' => 0); // bonus & defense batiments
        /* Calcul des bonus batiment, une fois pour toute */
        foreach ($btc_array as $bid => $btc) {
            $bonus1 = get_conf_gen($race, "btc", $btc['btc_type'], "bonus");
            if ($bonus1) {
                if ($btc['btc_type'] != 1) /* On met pas le donjon */
                    $def[$bid] = $btc;
                foreach ($bonus1 as $key => $value) /* key=gen ou bon */
                    $bonus[$key] += $value;
            }
            if (!isset($nb[$btc['btc_type']]))
                $nb[$btc['btc_type']] = 0;
            $nb[$btc['btc_type']] += 1; // compter le nb de bat par type
        }
        foreach ($def as $bid => $btc) // compter les batiments def par type
            $nb_def[$btc['btc_type']] = $nb[$btc['btc_type']];

        return array('nb' => $nb, 'def' => $def, 'bonus' => $bonus, 'nb_def' => $nb_def);
    }

}
