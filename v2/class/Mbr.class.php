<?php

/**
 * Membre
 * lien avec les héros : hro_mid = mbr_id
 * lien avec la carte : mbr_mapcid = map_id
 */
class Mbr extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
// override table name
    protected $table = 'mbr';

    static function count(array $cond = []) {
        $ret = Mbr::get(array_merge($cond, ['count' => true]));
        if ($ret && isset($ret[0]['mbr_nb']))
            return $ret[0]['mbr_nb'];
        else
            return 0;
    }

    /* un seul joueur - infos completes */

    static function getFull(int $mid) {
        return Mbr::get(['mid' => $mid, 'full' => true]);
    }

    /* un seul joueur */
    static function get(array $cond) {

        $count = false;
        $list = false;
        $full = false;
        $mid = 0;
        $mid_excl = 0;
        $login = "";
        $mail = "";
        $pseudo = "";
        $etat = array();
        $race = array();
        $order = "";
        $by = "";
        $ltpoint = 0;
        $gtpoint = 0;
        $pass = '';
        $ltpts_arm = 0;
        $gtpts_arm = 0;
        $mapcid = 0;
        $aid = 0;
        $ip = "";
        $gid = array();
        $dst = array();
        $limite1 = 0;
        $limite2 = 0;
        $aetat = array();
        $limite = 0;
        $group = "";
        $parrain = 0;

        if (isset($cond['op']) && protect($cond['op'], "string") == "AND")
            $op = "AND";
        else
            $op = "OR";

        if (isset($cond['limite1'])) {
            $limite1 = protect($cond['limite1'], "uint");
            $limite++;
        }
        if (isset($cond['limite2'])) {
            $limite2 = protect($cond['limite2'], "uint");
            $limite++;
        }
        if (isset($cond['dst'][0]))
            $dst['x'] = protect($cond['dst'][0], "uint");
        if (isset($cond['dst'][1]))
            $dst['y'] = protect($cond['dst'][1], "uint");
        if (isset($cond['aid']))
            $aid = protect($cond['aid'], "uint");
        if (isset($cond['aetat']) && $aid)
            $aetat = protect($cond['aetat'], "array");
        if (isset($cond['etat']))
            $etat = protect($cond['etat'], "array");
        if (isset($cond['gid']))
            $gid = protect($cond['gid'], "array");
        if (isset($cond['race']))
            $race = protect($cond['race'], "array");
        if (isset($cond['mid'])) { /* peut etre 1 seul membre ou un tableau */
            if (is_array($cond['mid']))
                $mid = protect($cond['mid'], array("uint"));
            else
                $mid = protect($cond['mid'], "uint");
        }
        if (isset($cond['mid_excl'])) // exclure un mid (pour compter par exemple)
            $mid_excl = protect($cond['mid_excl'], "uint");
        if (isset($cond['parrain']))
            $parrain = protect($cond['parrain'], "uint");
        if (isset($cond['login']))
            $login = protect($cond['login'], "string");
        if (isset($cond['pass']))
            $pass = protect($cond['pass'], "string");
        if (isset($cond['mail']))
            $mail = protect($cond['mail'], "string");
        if (isset($cond['pseudo']))
            $pseudo = protect($cond['pseudo'], "string");
        if (isset($cond['ip']))
            $ip = protect($cond['ip'], "string");
        if (isset($cond['ltpoint']))
            $ltpoint = protect($cond['ltpoint'], "uint");
        if (isset($cond['gtpoint']))
            $gtpoint = protect($cond['gtpoint'], "uint");
        if (isset($cond['ltpts_arm']))
            $ltpts_arm = protect($cond['ltpts_arm'], "uint");
        if (isset($cond['gtpts_arm']))
            $gtpts_arm = protect($cond['gtpts_arm'], "uint");
        if (isset($cond['full']))
            $full = protect($cond['full'], "bool");
        if (isset($cond['list']))
            $list = protect($cond['list'], "bool");
        if (isset($cond['count']))
            $count = protect($cond['count'], "bool");
        if (isset($cond['orderby']) && count($cond['orderby']) == 2) {
            $order = protect($cond['orderby'][0], "string");
            $by = protect($cond['orderby'][1], "string");
        }
        if (isset($cond['group'])) {
            $group = protect($cond['group'], "string");
        }
        if (isset($cond['mapcid']))
            $mapcid = protect($cond['mapcid'], "uint");

        //if(!$mid && !$login && !$mail && !$pseudo) return array();

        if ($full) {
            $sql = "mbr_mid,mbr_login,mbr_pseudo,mbr_vlg,mbr_mail,mbr_lang,mbr_etat";
            $sql .= ",mbr_gid,mbr_decal,mbr_race,mbr_mapcid,mbr_population,mbr_place,";
            $sql .= "mbr_points,mbr_pts_armee,mbr_atq_nb, _DATE_FORMAT(mbr_ldate) as mbr_ldate,";
            $sql .= " _DATE_FORMAT(mbr_inscr_date) as mbr_inscr_date, ";
            $sql .= "_DATE_FORMAT(mbr_lmodif_date) as mbr_lmodif_date,mbr_lip,mbr_sexe,";
            $sql .= " ambr_etat, al_name, ambr_aid, al_nb_mbr,al_open ";
            $sql .= ",mbr_sign, mbr_descr, mbr_lip ";
            $sql .= ",map_cid,map_x,map_y,map_type,map_region, mbr_parrain, mbr_numposts ";
            // replace date formatting:
            $sql = mysqliext::$bdd->parse_query($sql);
        } else if ($list) {
            $sql = "mbr_mid,mbr_pseudo,mbr_lang,mbr_etat,mbr_gid,mbr_race,mbr_mapcid, mbr_population, mbr_place, mbr_points, mbr_pts_armee, ";
            $sql .= " mbr_lip,ambr_etat, IF(ambr_etat=" . ALL_ETAT_DEM . ", 0, IFNULL(ambr_aid,0)) as ambr_aid, ";
            $sql .= " IF(ambr_etat=" . ALL_ETAT_DEM . ", NULL, al_name) as al_name  ";
            $sql .= ",map_x,map_y ";
        } else if ($count) {
            $sql = "COUNT(*) as mbr_nb ";
        } else {
            $sql = "mbr_mid, mbr_pseudo, mbr_gid, mbr_etat, mbr_points, mbr_pts_armee, IFNULL(ambr_aid,0) as ambr_aid , mbr_race, mbr_sexe ";
        }
        if (!$count && count($dst) == 2) {
            $sql .= ", GREATEST(ABS( " . $dst['x'] . " - map_x ), ABS( " . $dst['y'] . "- map_y)) AS mbr_dst ";
        }

        $req = Mbr::select(DB::raw($sql));

        if ($aid && (!$full && !$list)){
            $req->join("al_mbr", "mbr.mbr_mid", '=', "al_mbr.ambr_mid");
        }

        if ($full || $list || count($dst) == 2) {
            $req->leftJoin('map', 'mbr.mbr_mapcid', '=', 'map.map_cid');
            if (!$aid) {
                $req->leftJoin("al_mbr", "mbr.mbr_mid", '=', "al_mbr.ambr_mid");
                $req->leftJoin("al", "al_mbr.ambr_aid", '=', "al.al_aid");
            } else {
                $req->join("al_mbr", "mbr.mbr_mid", '=', "al_mbr.ambr_mid");
                $req->join("al", "al_mbr.ambr_aid", '=', "al.al_aid");
            }
        } else {
            $req->leftJoin("al_mbr", "mbr.mbr_mid", '=', "al_mbr.ambr_mid");
        }

        if ($mid || $login || $mail || $pseudo || $etat || $gid || $race || $ip || $aid || $parrain || $mapcid) {
            // TODO : gerer le OR? tous les WHERE sont & AND &
            if ($aid){
                $req->where('ambr_aid', '=', $aid);
            }
            if ($mid) {
                if (is_array($mid)) // liste de $mid
                    $req->whereIn("mbr_mid", $mid);
                else
                    $req->where("mbr_mid", $mid);
            }
            if ($mid_excl)
                $req->where("mbr_mid", '<>', $mid_excl);
            if ($parrain)
                $req->where("mbr_parrain", $parrain);
            if ($login)
                $req->where("mbr_login", $login);
            if ($pass)
                $req->where("mbr_pass", $pass);
            if ($mail)
                $req->where("mbr_mail", $mail);
            if ($pseudo)
                $req->where("mbr_pseudo", 'LIKE', $pseudo);
            if ($ip)
                $req->where("mbr_lip", 'LIKE', $ip);
            if ($aetat)
                $req->whereIn("ambr_etat", $aetat);
            if ($etat)
                $req->whereIn("mbr_etat", $etat);
            if ($gid)
                $req->whereIn("mbr_gid", $gid);
            if ($race)
                $req->whereIn("mbr_race", $race);
            if ($ltpoint)
                $req->where("mbr_points", '<', $ltpoint);
            if ($gtpoint)
                $req->where("mbr_points", '>', $gtpoint);
            if ($ltpts_arm)
                $req->where("mbr_pts_armee", '<', $ltpts_arm);
            if ($gtpts_arm)
                $req->where("mbr_pts_armee", '>', $gtpts_arm);
            if ($mapcid)
                $req->where("mbr_mapcid", $mapcid);
        }

        if ($group){
            $req->groupBy($group);
        }

        if (in_array($order, ['DESC', 'ASC'])) {
            if (in_array($by, ['mid', 'points', 'pts_armee', 'population', 'race', 'pseudo', 'gid', 'dst'])) {
                $req->orderBy("mbr_$by", $order);
            } else if (in_array($by, ['alliance_aid', 'alliance_name', 'alliance_points', 'alliance_open', 'alliance_nb_mbr'])) {
                $req->orderBy(str_replace("alliance_", "al_", $by), $order);
            }
        }

        if ($limite){
            if ($limite == 2){
                $req->offset($limite1)->limit($limite2);
            }else{
                $req->limit($limite1);
            }
        }

        return $req->get()->toArray();
    }

    static function countRaces(array $races = []) {
    $req = Mbr::selectRaw('mbr_race,COUNT(*) as race_nb');
    if(!empty($races)){
            $req->whereIn('mbr_race', $races);
    }
     $result = $req->groupBy('mbr_race')->orderBy('mbr_race', 'asc')->get()->toArray();
     return index_array($result, 'mbr_race');
}


/* Permet de détecter les multis comptes */
static function getIps(string $ip = '', int $gid = 0)
{

	if(!$ip){
            $temp_array = Mbr::selectRaw('mbr_lip')
                    ->groupBy('mbr_lip')->havingRaw('COUNT(mbr_mid) > 1')->get()->toArray();
	
		if(empty($temp_array))
			return [];
		
		foreach($temp_array as $value) {
			$where_ip[] = $value['mbr_lip'];
		}
			
	}else
		$where_ip[] = $ip;

        $req = Mbr::select('mbr_pseudo','mbr_mid','mbr_mail','mbr_login','mbr_lip','mbr_ldate')
                ->whereIn('mbr_lip', $where_ip);
	if($gid != GRP_DIEU && $gid != GRP_DEMI_DIEU){
		$req->whereNotIn('mbr_gid', [GRP_GARDE,GRP_PRETRE,GRP_DEMI_DIEU,GRP_DIEU,GRP_DEV,GRP_ADM_DEV]);            
        }
        return $req->orderBy('mbr_lip')->orderBy('mbr_ldate', 'desc')->get()->toArray();
    }

    /* ajouter un joueur */

    static function add(string $login, string $pass, string $mail, string $lang, int $etat, int $gid, string $decal, string $ip, int $design, int $parrain) {
        $request = ['mbr_login' => $login,
            'mbr_pseudo' => $login,
            'mbr_pass' => $pass, /* Le pass doit avoir été crypté ! */
            'mbr_mail' => $mail,
            'mbr_lang' => $lang,
            'mbr_etat' => $etat,
            'mbr_gid' => $gid,
            'mbr_decal' => $decal,
            'mbr_ldate' => DB::raw('NOW()'),
            'mbr_inscr_date' => DB::raw('NOW()'),
            'mbr_lip' => $ip,
            'mbr_design' => $design,
            'mbr_parrain' => $parrain
        ];

        $mid = Mbr::insertGetId($request);

        if ($mid == 0){ /* Une des clefs unique existe déjà ! */
            return 0;
        } else {
            $im = imagecreatefrompng(MBR_LOGO_DIR . '0.png');
            imagepng($im, MBR_LOGO_DIR . "$mid.png");
            return $mid;
        }
    }

    /* Initialise un compte */

    static function init(int $mid, string $pseudo, string $vlg, int $race, int $cid, int $gid, int $sexe = 1) {

        $unt = get_conf_gen($race, "race_cfg", "debut", "unt");
        $btc = get_conf_gen($race, "race_cfg", "debut", "btc");

        $pop = 0;
        $pla = 0;
        foreach ($unt as $id => $nb) {
            $pop += $nb;
        }
        foreach ($btc as $id => $info) {
            $pla += get_conf("btc", $id, "prod_pop");
        }

        $edit = ['gid' => $gid,
            'vlg' => $vlg,
            'pseudo' => $pseudo,
            'race' => $race,
            'points' => 0,
            'population' => $pop,
            'place' => $pla,
            'etat' => MBR_ETAT_OK,
            'mapcid' => $cid,
            'sexe' => $sexe];

        Mbr::edit($mid, $edit);

        ini_map($mid, $cid);

        // légion village avec ses unités
        $lid = Leg::add($mid, $cid, LEG_ETAT_VLG, $vlg);
        Unt::init($lid);
        // légion des batiments - vide
        Leg::add($mid, $cid, LEG_ETAT_BTC, $vlg);
        Res::init($mid);
        ResTodo::where('rtdo_mid', '=', $mid)->delete();
        Src::init($mid);
        Btc::init($mid);
        Trn::init($mid);
    }

    /* Réinitialise un compte */

    static function reinit($mid, $pseudo, $vlg, $race, $cid, $oldcid, $gid, $sexe = 1) {
        cls_map($mid, $oldcid);
        cls_aly($mid);
        UntTodo::clear($mid);
        Leg::del($mid);
        Hro::del($mid);
        Btc::clear($mid);
        Res::clear($mid);
        ResTodo::where('rtdo_mid', '=', $mid)->delete();
        SrcTodo::del($mid);
        Trn::clear($mid);
        cls_com($mid);
        cls_atq($mid);
        cls_histo($mid);
        cls_vld($mid);

        Mbr::init($mid, $pseudo, $vlg, $race, $cid, $gid, $sexe);
    }

    /* modifie en général */ /* Le 'pass' doit être crypté ! */

    static function edit(int $mid, array $new) {

        if (empty($new)){
            return;
        }
        
        $request = [];
        foreach ($new as $key => $val) {
            if ($key == 'atqnb'){
                $request["mbr_atq_nb"] = $val;
            } else if ($key == 'ldate') {
                $request["mbr_$key"] = DB::raw('NOW()');
            } else {
                $request["mbr_$key"] = $val;
            }
        }

        // si on renomme le village, il faut renommer les 2 pseudo-légions (civils et village)
        if (isset($new['vlg'])) {
            Leg::where('leg_mid', '=', $mid)
                    ->whereIn('leg_etat', [LEG_ETAT_VLG, LEG_ETAT_BTC])
                    ->update(['leg_name' => $new['vlg']]);
        }

        return Mbr::where('mbr_mid', '=', $mid)->update($request);
    }

    /* supprimer un membre */

    static function del(int $mid) {

        return Mbr::where('mbr_mid', $mid)->delete();
    }

    static function getNbRace(int $race = 0) {
        $req = Mbr::select(DB::raw('mbr_race,COUNT(*) as race_nb'));
        if ($race){
            $req->where('mbr_race', '=', $race);
        }
        return $req->groupBy('mbr_race')->get()->toArray();
    }

    /* Libère les trucs d'un compte */

    static function cls($mid, $cid) {
        add_old_mbr($mid);
        cls_aly($mid);
        UntTodo::clear($mid);
        Btc::clear($mid);
        Res::clear($mid);
        ResTodo::where('rtdo_mid', '=', $mid)->delete();
        SrcTodo::del($mid);
        Trn::clear($mid);
        cls_com($mid);
        cls_atq($mid);
        cls_histo($mid);
        // cls_msg($mid); // on garde les messages
        cls_vld($mid);
        cls_map($mid, $cid);
        cls_nte($mid);
        //cls_frm($mid);

        Mbr::del($mid);
    }

}
