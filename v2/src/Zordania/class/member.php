<?php

/*
 * cache de tout ce qui concerne le membre 
 * mbr unt res btc src leg et certains "todo"
 */

class member {

    private $mid;
    private $mbr;
    private $mbr_load = false;

    /** unites + legions
      ["unt_lid"]=>
      ["unt_type"]=>
      ["unt_rang"]=>
      ["unt_nb"]=>
      ["leg_id"]=>
      ["leg_mid"]=>
      ["leg_cid"]=>
      ["leg_etat"]=>
      ["leg_name"]=>
      ["leg_xp"]=>
      ["leg_vit"]=>
      ["leg_dest"]=>
      ["leg_tours"]=>
      ["leg_fat"]=>
      ["leg_stop"]=>
     */
    private $unt; // unt civiles & militaires
    private $unt_leg; // toutes unités par etat (vlg btc ou liste des legions)
    private $unt_load = false;
    private $unt_todo;
    private $unt_todo_load = false;
    private $nb_unt; // type => nb
    private $nb_unt_load = false;

    /** legion + heros
      ["leg_id"]=>
      ["leg_mid"]=>
      ["leg_cid"]=>
      ["leg_etat"]=>
      ["leg_vit"]=>
      ["leg_name"]=>
      ["leg_xp"]=>
      ["leg_dest"]=>
      ["leg_tours"]=>
      ["leg_fat"]=>
      ["leg_stop"]=>
      ["hro_id"]=>
      ["hro_nom"]=>
      ["hro_type"]=>
      ["hro_xp"]=>
      ["hro_vie"]=>
      ["bonus"]=>
      ["hro_bonus_from"]=>
      ["bonus_to"]=>
     */
    private $leg;
    private $leg_load = false;
    private $res;
    private $res_load = false;
    private $res_todo;
    private $res_todo_load = false;
    private $src;
    private $src_load = false;
    private $src_todo;
    private $src_todo_load = false;
    private $btc;
    private $btc_load = false;
    private $nb_btc;
    private $nb_btc_load = false;
    private $trn;
    private $trn_load = false;

    function __construct($mid) {
        $this->mid = $mid;
    }

    function getConf($type = "", $key0 = "", $key1 = "") {
        return Config::get($this->mbr()['mbr_race'], $type, $key0, $key1);
    }

    function mbr() {
        if (!$this->mbr_load) {
            $this->mbr = Mbr::get(array('mid' => $this->mid));
            $this->mbr = $this->mbr[0];
            $this->mbr_load = true;
        }
        return $this->mbr;
    }

    /*     * * unites et unites en legion ** */

    function unt() {
        if (!$this->unt_load) {
            $this->unt = Leg::get(['mid' => $this->mid, 'unt' => true, 'leg' => true]);

            $tmp = array();
            foreach ($this->unt as $value) {

                if (isset($tmp[$value['leg_etat']][$value['unt_type']]))
                    $tmp[$value['leg_etat']][$value['unt_type']] += $value['unt_nb'];
                else
                    $tmp[$value['leg_etat']][$value['unt_type']] = $value['unt_nb'];
            }
            $this->unt_leg = $tmp;
            $this->unt_load = true;
        }

        return $this->unt;
    }

    function unt_leg($type = null) {
        if (!$this->unt_load) {
            $this->unt();
        }
        if ($type != null)
            return isset($this->unt_leg[$type]) ? $this->unt_leg[$type] : array();
        else
            return $this->unt_leg;
    }

    function nb_unt($type = null) {
        if (!$this->nb_unt_load) {
            $tmp = array();
            foreach ($this->unt() as $value) {
                if (isset($tmp[$value['unt_type']]))
                    $tmp[$value['unt_type']] += $value['unt_nb'];
                else
                    $tmp[$value['unt_type']] = $value['unt_nb'];
            }
            $this->nb_unt = $tmp;
            $this->nb_unt_load = true;
        }

        if ($type === null)
            return $this->nb_unt;
        else if (isset($this->nb_unt[$type]))
            return $this->nb_unt[$type];
        else
            return 0;
    }

    /* Les unités faites, mais au village */

    function nb_unt_done($unt = null) {
        $tmp = array();
        foreach ($this->unt() as $value) {
            if ($unt == null || $value['unt_type'] == $unt) {
                if ($value['leg_etat'] == LEG_ETAT_VLG) {
                    if (isset($tmp[$value['unt_type']]))
                        $tmp[$value['unt_type']] += $value['unt_nb'];
                    else
                        $tmp[$value['unt_type']] = $value['unt_nb'];
                }
            }
        }
        if ($unt == null)
            return $tmp;
        else
            return (isset($tmp[$unt]) ? $tmp[$unt] : 0);
    }

    /*     * * unites en legion ** */

    function leg() {
        if (!$this->leg_load) {
            $this->leg = Leg::get(array('mid' => $this->mid, 'leg' => true));
            $this->leg_load = true;
        }
        return $this->leg;
    }

    /*     * *  unites en todo list ** */

    function unt_todo() {
        if (!$this->unt_todo_load) {
            $this->unt_todo = UntTodo::get($this->mid);
            $this->unt_todo = index_array($this->unt_todo, "unt_todo");
            $this->unt_todo_load = true;
        }
        return $this->unt_todo;
    }

    /* Verifie qu'on peut faire $nb unités du type $type */

    function can_unt(int $type, int $nb) {

        $bad = $this->canDo('unt', $type, $nb);
        if (isset($bad['do_not_exist']))
            return $bad;

        /* La limite */
        $have_unt_todo = $this->unt_todo();
        $unt_nb = $this->nb_unt($type);
        if (isset($have_unt_todo[$type]['utdo_nb']))
            $unt_nb += $have_unt_todo[$type]['utdo_nb'];

        $limite = $this->getConf("unt", $type, "limite");
        if ($limite && $unt_nb >= $limite)
            $bad['limit_unt'] = $limite;
        else
            $bad['limit_unt'] = [];

        return $bad;
    }

    /*     * * ressources ** */

    function res() {
        if (!$this->res_load) {
            $this->res = Res::get($this->mid);
            $this->res_load = true;
        }
        return $this->res;
    }

    function res_todo() {
        if (!$this->res_todo_load) { // TODO?
            $this->res_todo = get_res_gen(array('mid' => $this->mid));
            $this->res_toto_load = true;
        }
        return $this->res_todo;
    }

    /* Verifie qu'on peut faire telle ou telle ressource */

    function can_res(int $type, int $nb) {
        return $this->canDo('src', $type, $nb);
    }

    /*     * * recherches ** */

    function src() {
        if (!$this->src_load) {
            $tmp = Src::get($this->mid);
            $this->src = index_array($tmp, 'src_type');
            $this->src_load = true;
        }
        return $this->src;
    }

    function src_todo() {
        if (!$this->src_todo_load) { // TODO?
            $this->src_todo = SrcTodo::get($this->mid);
            $this->src_todo_load = true;
        }
        return $this->src_todo;
    }

    /* Verifie qu'on peut faire telle ou telle recherche */

    function can_src(int $type) {

        $bad = $this->canDo('src', $type);
        if (isset($bad['do_not_exist']))
            return $bad;

        $bad['need_no_src'] = [];

        /* Les recherches qu'on ne doit pas avoir */
        $need_no_src = Session::$SES->getConf("src", $type, "need_no_src");
        $have_src = $this->src();
        foreach ($need_no_src as $src_type) {
            if (isset($have_src[$src_type]))
                $bad['need_no_src'][$src_type] = $src_type;
        }

        /* La recherche qu'on veut est elle déjà en cours ? */
        $bad['todo'] = isset($this->src_todo()[$type]);
        $bad['done'] = ($bad['todo'] || isset($have_src[$type]));

        return $bad;
    }

    /*     * *  batiments ** */

    function btc($btc = array(), $etat = array()) {
        // mise en cache du resultat sql
        if (!$this->btc_load) {
            $this->btc = Btc::get($this->mid);
            $this->btc_load = true;
        }
        $result = $this->btc;

        if (!empty($etat)) {
            // filtrer sur l'etat des btc
            $result = array_filter($result, function($v) use($etat) {
                return in_array($v['btc_etat'], $etat);
            });
        }

        // filtrer les types de btc
        if (!empty($btc)) {
            $result = array_filter($result, function($v) use($btc) {
                return in_array($v['btc_type'], $btc);
            });
        }

        return $result;
    }

    function nb_btc($btc = array(), $etat = array()) {
        // compter pour 1 type de bat:
        if (is_int($btc))
            return count($this->btc(array($btc), $etat));

        $tmp = $this->btc($btc, $etat);
        // compter les btc par type
        $result = array();
        foreach ($tmp as $value) {
            if (isset($result[$value['btc_type']]))
                $result[$value['btc_type']]['btc_nb'] ++;
            else
                $result[$value['btc_type']] = array('btc_mid' => $this->mid, 'btc_nb' => 1);
        }

        return $result;
    }

    function nb_btc_done($btc = array()) {
        return $this->nb_btc($btc, array(BTC_ETAT_OK, BTC_ETAT_DES, BTC_ETAT_REP, BTC_ETAT_BRU));
    }

    /* Verifie qu'on peut faire tel ou tel bâtiment */

    function can_btc($type) {

        $bad = $this->canDo('btc', $type);
        if (isset($bad['do_not_exist']))
            return $bad;

        /* La limite : recompter pour prendre en compte les btc TODO */
        $limite = (int) $this->getConf("btc", $type, "limite");
        if ($limite && $this->nb_btc($type) >= $limite) // isset($have_btc[$type]['btc_nb']) && $have_btc[$type]['btc_nb'] >= $limite)
            $bad['limit_btc'] = $limite;
        else
            $bad['limit_btc'] = [];

        return $bad;
    }

    /**
     * vérifier qu'on peut faire $nb quantité du $what $type
     * @param string $what = btc | src | res | trn | unt
     * @param int $type = index du $what à former
     * @param type $nb = quantité
     * @return array = ce qui manque ou [] si ok
     */
    function canDo(string $what, int $type, $nb = 1) {
        if (!$this->getConf($what, $type))
            return ["do_not_exist" => true];

        /* Bâtiments */
        $need_btc = $this->getConf($what, $type, "need_btc");

        /* Recherches */
        $need_src = $this->getConf($what, $type, "need_src");

        /* Ressources */
        $prix_res = $this->getConf($what, $type, "prix_res");

        /* Terrains */
        $prix_trn = $this->getConf($what, $type, "prix_trn");

        /* Unités */
        $prix_unt = $this->getConf($what, $type, "prix_unt");

        return $this->canPay($need_src, $need_btc, $prix_res, $prix_unt, $prix_trn, $nb);
    }

    /**
     * vérifier qu'on peut dépenser le prix indiqué
     * @param array $src = recherches à avoir
     * @param array $btc = batiments à avoir
     * @param array $res = ressources
     * @param array $unt = unités
     * @param array $trn = terrains
     * @param int $nb = coefficient du nb à acheter
     * @return array = ce qui manque à payer
     */
    function canPay(array $src, array $btc, array $res, array $unt, array $trn = [], int $nb = 1) {

        $result = ['need_src' => [], 'need_btc' => [], 'prix_res' => [], 'prix_trn' => [], 'prix_unt' => []];
        /* Les recherches qu'il faut avoir */
        $have_src = $this->src();
        foreach ($src as $src_type) {
            if (!isset($have_src[$src_type]))
                $result['need_src'][] = $src_type;
        }

        /* Vérifications ressources */
        $have_res = $this->res();
        foreach ($res as $res_type => $nombre) {
            $diff = $nombre * $nb - (isset($have_res[$res_type]) ? $have_res[$res_type] : 0);
            if ($diff > 0)
                $result['prix_res'][$res_type] = $diff;
        }

        /* Les terrains */
        $have_trn = $this->trn();
        foreach ($trn as $trn_type => $nombre) {
            $diff = $nombre * $nb - $have_trn[$trn_type];
            if ($diff > 0)
                $result['prix_trn'][$trn_type] = $diff;
        }

        /* Les unités */
        $have_unt = $this->nb_unt_done();
        foreach ($unt as $unt_type => $nombre) {
            $diff = $nombre * $nb - (isset($have_unt[$unt_type]) ? $have_unt[$unt_type] : 0);
            if ($diff > 0)
                $result['prix_unt'][$unt_type] = $diff;
        }

        /* Verifications Bâtiments */
        $have_btc = $this->nb_btc_done();
        foreach ($btc as $btc_type) {
            if (!isset($have_btc[$btc_type]))
                $result['need_btc'][] = $btc_type;
        }

        return $result;
    }

    /*     * * terrains ** */

    function trn() {
        if (!$this->trn_load) {
            $this->trn = Trn::get($this->mid);
            $this->trn_load = true;
        }
        return $this->trn;
    }

}
