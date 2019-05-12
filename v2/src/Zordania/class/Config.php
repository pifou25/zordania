<?php

/*
 * Gestion de config
 * - config des races
 * - gestion des accès
 * - autre ?
 */

class Config {

    private static $conf = [];

    /* Droits */

    const DROITS = [
        GRP_VISITEUR => [DROIT_SITE, DROIT_PUNBB_GUEST],
        GRP_EXILE => [],
        GRP_EXILE_TMP => [DROIT_SITE, DROIT_MSG, DROIT_PUNBB_GUEST],
        GRP_JOUEUR => [DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER],
        GRP_EVENT => [DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER],
        GRP_PNJ => [DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER, DROIT_ANTI_FLOOD],
        GRP_CHEF_REG => [DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER],
        GRP_CHAMP_REG => [DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER],
        GRP_SCRIBE => [DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER],
        GRP_NOBLE => [DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER],
        GRP_KING => [DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER],
        GRP_SAGE => [DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER, DROIT_ANTI_FLOOD],
        GRP_GARDE => [DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER, DROIT_ANTI_FLOOD, DROIT_ADM, DROIT_ADM_AL, DROIT_ADM_MBR],
        GRP_PRETRE => [DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER, DROIT_PUNBB_MOD, DROIT_ANTI_FLOOD],
        GRP_DEV => [DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER, DROIT_ADM],
        GRP_ADM_DEV >= [DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_MMSG,
    DROIT_ADM, DROIT_ADM_AL, DROIT_ADM_COM, DROIT_ADM_MBR, DROIT_ADM_TRAV, DROIT_ADM_EDIT,
    DROIT_PUNBB_MEMBER, DROIT_PUNBB_MOD, DROIT_PUNBB_ADMIN, DROIT_SDG, DROIT_ANTI_FLOOD],
        GRP_DEMI_DIEU => [DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_MMSG,
            DROIT_ADM, DROIT_ADM_AL, DROIT_ADM_COM, DROIT_ADM_MBR, DROIT_ADM_EDIT,
            DROIT_PUNBB_MEMBER, DROIT_PUNBB_MOD, DROIT_PUNBB_ADMIN, DROIT_SDG, DROIT_ANTI_FLOOD],
        GRP_DIEU => [DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_MMSG,
            DROIT_ADM, DROIT_ADM_AL, DROIT_ADM_COM, DROIT_ADM_MBR, DROIT_ADM_TRAV, DROIT_SDG, DROIT_ADM_EDIT,
            DROIT_PUNBB_MEMBER, DROIT_PUNBB_MOD, DROIT_PUNBB_ADMIN, DROIT_ANTI_FLOOD]
    ];

    /**
     * recherche la configuration des races
     * @param int $race
     * @param string $type = unt btc src res ...
     * @param type $key0 =  index de l'item $type
     * @param type $key1 = clé de $key0
     * @return type ( array ou int ou ...)
     */
    static public function get(int $race, string $type = "", $key0 = "", $key1 = "") {

        if (!Config::load($race))
            return []; // error

        if (!$type) // full config
            return self::$conf[$race];

        if (!isset(self::$conf[$race]->$type))
            return []; // error, bad $type

        if (!$key0) // all items $type
            return self::$conf[$race]->$type;

        $list = & self::$conf[$race]->$type;
        if (!isset($list[$key0]))
            return []; // error, bad $key0

        if (!$key1) // all config from $race / $type / $key0
            return $list[$key0];

        if (!isset($list[$key0][$key1]))
            return []; // error, bad $key1

        return $list[$key0][$key1];
    }

    /**
     * filtrer la config on ne conserve que les elements dont $key = $value.
     * ex: filtrer les unités dont 'ROLE' = TYPE_UNT_HEROS
     * @param int $race
     * @param string $type [unt / src / btc / res / trn ...]
     * @param type $key
     * @param type $value
     * @return type
     */
    static public function filter(int $race, string $type, $key, $value){
        $arr = self::get($race, $type);
        if(empty($arr))
            return [];
        return array_filter($arr, function($val) use ($key, $value) {
            return isset($val[$key]) && $val[$key] == $value;
        });
    }
    
    /**
     * chargement du fichier de config de race
     * @param int $race
     * @return boolean
     */
    static public function load(int $race) {
        if (!isset(self::$conf[$race])) {
            if (file_exists(SITE_DIR . "conf/" . $race . ".php")) {
                require_once(SITE_DIR . "conf/" . $race . ".php");
                $confname = "config" . $race;
                self::$conf[$race] = new $confname;
                return true;
            } else
                return false;
        } else
            return true;
    }

    /* Récupère les droits pour un groupe donné */

    static function getDroits($gid) {
        if (!isset(Config::DROITS[$gid]))
            return [];
        else
            return Config::DROITS[$gid];
    }

}
