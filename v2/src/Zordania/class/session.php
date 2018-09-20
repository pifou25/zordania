<?php
class session
{
	var $vars;
	var $dateformat = '%d-%m-%y à %H:%i:%s'; /* Mettre ça dans les tpl */
	var $decal = '00:00:00';
	static public $SES;
        
	function __construct()
	{
		if(!CRON){
			$this->vars = & $_SESSION['user'];
		}
                session::$SES = $this;
	}

	function __destruct()
	{
		if(!CRON) $_SESSION['user'] = $this->vars;
	}

	function set_cookie($login, $pass) {
		if(CRON) return true;
		else return setcookie("zrd",serialize(array($login,$pass)), time() + 60 * 60 * 24 * 7);
	}

	function del_cookie()
	{
		if(CRON) return true;
		$_COOKIE["zrd"] = array();
		return setcookie("zrd", "", -1);
	}
	
	function id() {
		if(CRON) return $this->get('sesid');
		else return session_id();
	}
	
	function login_as_guest() {
		return $this->login("guest","guest");
	}
	
	function session_opened() {
		return !empty($this->vars);
	}
	
	function get_vars() {
		return empty($this->vars) ? false : $this->vars;
	}
	
	function set($name, $value) {
		//$_SESSION['user'][$name] = $value;
		$this->vars[$name] = $value;
	}
	
	function get($name) {
		return isset($this->vars[$name]) ? $this->vars[$name] : false;
	}
	
	function init_vars() {
		$this->vars = array();
		//$_SESSION['user'] = array();
	}
	
	function set_vars($mbr_infos) {
		$this->set("mid",$mbr_infos['mbr_mid']);
		$this->set("login", $mbr_infos['mbr_login']);
		$this->set("pseudo", $mbr_infos['mbr_pseudo']);
		$this->set("vlg", $mbr_infos['mbr_vlg']);
		//$this->set("pass", $mbr_infos['mbr_pass']);
		$this->set("lang", $mbr_infos['mbr_lang']);
		$this->set("race", $mbr_infos['mbr_race']);
		$this->set("droits", Config::getDroits($mbr_infos['mbr_gid']));
		$this->set("groupe", $mbr_infos['mbr_gid']);
		$this->set("decal", $mbr_infos['mbr_decal']);
		$this->set("place", $mbr_infos['mbr_place']);
		$this->set("population", $mbr_infos['mbr_population']);
		$this->set("points", $mbr_infos['mbr_points']);
                $this->set("xp", $mbr_infos['mbr_xp']);
		$this->set("pts_arm",$mbr_infos['mbr_pts_armee']);
		$this->set("mail", $mbr_infos['mbr_mail']);
		$this->set("mapcid", $mbr_infos['mbr_mapcid']);
		$this->set("etat", $mbr_infos['mbr_etat']);
		$this->set("lmodif", $mbr_infos['mbr_lmodif_date']);
		$this->set("sign", $mbr_infos['mbr_sign']);
		$this->set("ldate", $mbr_infos['mbr_ldate']);
		$this->set("regen", true);
		$this->set("atqnb", $mbr_infos['mbr_atq_nb']);
		if(CRON) $this->set("ip", $mbr_infos['mbr_lip']);
		else     $this->set("ip", $this->getIp());
		$this->set("design", $mbr_infos['mbr_design']);
		$this->set("parrain", $mbr_infos['mbr_parrain']);
		$this->set("numposts", $mbr_infos['mbr_numposts']);
                if(!$this->get('mobile')){
                    $this->set('mobile', false);
                    $this->set('btc', '');
                }else{
                    $this->set('btc', '/2');
                }

		/* Visiteur */
		if($this->get("login") == "guest") {
			$this->set("loged", false);
			$this->set("lang", $this->getLang());
		} else
			$this->set("loged", true);
	}
	
	function update_vars() {
		$this->update_grp();
		$this->update_msg();

		/* Session prise a partir du cache */
		$this->set("regen", false);
	}

	function update_grp() {
		$gid = $this->get("groupe");
		$this->set("droits", Config::getDroits($gid));
	}

	function update_msg() {
		$mid = $this->get("mid");
		
		if($this->get("login") != "guest") {
                    /* Nouveaux messages */
			$this->set("msg", MsgRec::count($mid));
			
			/* Select la dernière news si plus récente que la dernière connexion */
                        $result = FrmTopic::where('forum_id', ZORD_NEWS_FID)
                                ->where('posted', '>', function($query) use ($mid){
                                    $query->from('mbr')->selectRaw('UNIX_TIMESTAMP(mbr_ldate)')->where('mbr_mid', $mid);
                                })
                                ->orderBy('posted', 'desc')->get()->toArray();
                        if(count($result) > 0){
                            $this->set("tid", $result[0]['id']);
                            $this->set("sub", $result[0]['subject']);
                        }else{
                            $this->set("tid", 0);
                            $this->set("sub", 0);
                        }
		}	
		
		
			
	}

	function update_heros() {
		$mid = $this->get("mid");
		
		if($this->get("login") != "guest") {
			/* si on a un héros ? et une compétence active ? */
                    $result = Hro::get($mid);
			if(!$result){ // aucun héros
				$this->set('hro_id', 0);
				$this->set("hro_nom", null);
				$this->set("hro_type", null);
				$this->set("hro_lid", null);
				$this->set("hro_xp", null);
				$this->set("hro_vie", null);
				$this->set("bonus", null);
				$this->set("hro_bonus_from", null);
				$this->set("hro_bonus_to", null);
			} else { // placer en session toutes les infos du héros
				foreach($result as $key => $value)
					$this->set($key, $value);
				$this->set('hro_vie_conf',
                                        Config::get($this->get('race'), 'unt', $result['hro_type'], 'vie'));
			}
		}
	}
	
	function update_aly() {
		$mid = $this->get("mid");
		
		$this->set("alaid", 0);
		$this->set("aetat", ALL_ETAT_NULL);
		$this->set("achef", false);
		
		/* alliance */
		if($this->get("login") != "guest") {
                    $array = AlMbr::get($mid);
                    
			if($array) {
				$this->set("alaid", $array['ambr_aid']);
				$this->set("aetat", $array['ambr_etat']);
				$this->set("achef", $array['al_mid']);
			}
		}
	}
	
	function update_pos() {
		$mapcid = $this->get("mapcid");

		if($mapcid) {
                    $map_array = Map::where('map_cid', $mapcid)->get()->toArray();
			if($map_array) {
				$this->set("map_x", $map_array[0]['map_x']);
				$this->set("map_y", $map_array[0]['map_y']);
			}
		}
	}

	/* Trucs spécifiques a certains modules */
	function set_forum_vars($ldate) {
		/* Pour le forum */
		$this->set("forum_ldate", $ldate);
		$this->set("forum_lus", array());
	}
	
	function crypt($login, $pass) {
		return md5($login.strrev($pass));
	}
	
	function login(string $login, string $pass, bool $raw = false)
	{
		/* $pass est censé être le mot de passe en md5 */
		if(!$raw) $pass = $this->crypt($login, $pass);
                $req = Mbr::get(['login' => $login, 'pass' => $pass, 'full' => true]);

		if($req)
		{
			$req = $req[0];
			$mid = $req['mbr_mid'];
			$this->set('mid',$mid);

			if(CRON) { /* récupérer la session WEB active si existe */
				$resultat = Ses::get($mid);
				if($resultat){ // déjà co sur web
					$this->set('sesid', $resultat[0]['ses_sesid']);
					$this->set('ip', $resultat[0]['ses_ip']);
					return $this->update('irc');
				}else{ // créer une session CRON
					$ip = $req['mbr_lip']; // dernière ip connue
					$sesid = genstring(26);
				}
			}else{					
				$sesid = $this->id();
				$ip = $this->getIp();
			}

			/* On vire les anciennes sessions qu'il pouvait avoir */
                        Ses::del($sesid, $mid, $ip);
			
			/* On en remet une, en disant qu'il est sur la page session */
                        Ses::add($sesid, $mid, $ip);
			
			if($login != "guest" and !CRON) {
			/* Sa derniere ip ..  la dernière fois qu'il s'est connecté -> dans zrd_mbr */
                            $request = ['mbr_lip' => $ip];
                            if($req['mbr_etat'] != MBR_ETAT_ZZZ) { /* Seulement si pas en veille */
                                    $request['mbr_ldate'] = DB::raw('NOW()');
                            }
                            Mbr::where('mbr_mid', $mid)->update($request);
				
			/* On rajoute son ip et la date_heure dans zrd_mbr_log à chaque changement d'ip. 
			   Les anciennes ip sont conservées. */
                            $req2 = MbrLog::get($mid, 1);
				
				if ( (!isset($req2[0])) || ($ip != $req2[0]['mlog_ip']) ){
                                    MbrLog::add($mid, $ip);
				}
			}
			
			$this->init_vars();
			$this->set_vars($req);
			$this->set_forum_vars($req['mbr_ldate']);
			$this->update_msg();
			$this->update_heros();
			$this->update_aly();
			$this->update_pos();

			if($login != "guest")
				$this->set_cookie($login, $pass);

			return $this->get_vars();
		}else{
			return false;		
		}
	}
	
	function logout()
	{
            Ses::del($this->id());
		if(!CRON){ // interdit en CLI
			unset($_SESSION);
			$this->del_cookie();
			session_destroy();
			session_start();
		}
		return true;
	}
	
        /**
         * chercher le cookie 'zrd':
         * si OK -> login avec les infos cookie
         * si KO -> login visiteur
         * @return boolean
         */
	function auto_login()
	{
		$zrd = request("zrd", "array", "cookie");
		if(count($zrd) == 2)
		{
			$login = $zrd[0];
			$pass = $zrd[1];
			if($this->login($login, $pass, true))
		  		 return true;
		  	else
				return $this->login_as_guest();
		 } else {
		   	$this->logout();
		   	return $this->login_as_guest();
		 }
	}
	
	function update($act)
	{
		$mid = $this->get("mid");
		$pass = $this->get("pass");
		$login = $this->get("login");
		$sesid = $this->id();
		$act = protect($act, "string");
		
		/* Est ce que la ligne a changée depuis la dernière fois ? on peut utiliser mid, parce que si elle est pas changée, elle a pas changée :D 
		   Par contre, si on vient de régénérer la session, il ne faut pas verifier la date, puisqu'on vient de la changer !
		*/
                $res = Mbr::selectRaw('UNIX_TIMESTAMP(mbr_lmodif_date) AS ldate')
                        ->where('mbr_mid', $mid)
                        ->get()->toArray();
		if(empty($res)) 
			return false;

		$lmodif = $res[0]['ldate'];
		
		/* Mise a jour de la session: sinon elle a expiré */
		if(!Ses::edit($sesid, $act)) {
			return false;
		}	
		
                /* une modif, on reprend tout - sauf CRON */
		if(!CRON and (!$lmodif OR $lmodif > $this->get("lmodif")))
		{
                        $req = Mbr::get(['login' => $login, 'pass' => $pass, 'full' => true]);
			if(!$req) {
				return false;
			} else {
				$req = $req[0];
				$req['mbr_lmodif_date'] = $lmodif;
				$this->set_cookie($login,$pass);
				$this->set_vars($req);
				$this->update_msg();
				$this->update_pos();
				$this->update_heros();
				$this->update_aly();
				
				return true;
			}
		} else {
			//on se sert du cache :)
			$this->update_vars();
			return true;
		}
	}
        
    /* Regarde si on a un droit */
    function canDo($droit) {
        if ($this->get('droits'))
            return in_array($droit, $this->get('droits'));
        else
            return false;
    }

    /**
     *  Pour jouer avec la conf 
     * @param string $type = unt btc src res ...
     * @param type $key0 =  index de l'item $type
     * @param type $key1 = clé de $key0
     * @return type ( array ou int ou ...)
     */
    function getConf($type = "", $key0 = "", $key1 = "") {
            return Config::get($this->get('race'), $type, $key0, $key1);
    }

    function getLang() {
            global $_langues;

            $pays = $this->getPays();
            if(isset($_langues[$pays]))
                    return $_langues[$pays];
            else
                    return $_langues["unknown"];
    }

    /* Trouve le pays du type */
    function getPays() {
            $lang = request("lang", "string", "get");

            if($lang) {
                    setcookie('lang',$lang);
            } else {
                    $lang = request("lang", "string", "cookie");
                    if(!$lang) {
                            $host= @gethostbyaddr($this->getIp());
                            $code = substr(strrchr($host,'.'),1);
                    }
            }

            if(!$code)
                    $code = "unknown";

            return $code;
    }

    function getIp() {
            $realip = "127.0.0.1"; /* Quand on trouve pas, c'est que c'est en cli */

            if (isset($_SERVER)) {
                if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                    $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
                } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
                    $realip = $_SERVER["HTTP_CLIENT_IP"];
                } elseif (isset($_SERVER["REMOTE_ADDR"])) {
                    $realip = $_SERVER["REMOTE_ADDR"];
                }
            } else {
                if (getenv('HTTP_X_FORWARDED_FOR')) {
                    $realip = getenv('HTTP_X_FORWARDED_FOR');
                } elseif (getenv('HTTP_CLIENT_IP')) {
                    $realip = getenv('HTTP_CLIENT_IP');
                } else {
                    $realip = getenv('REMOTE_ADDR');
                }
            }
            return $realip;
        }


        /* remplace (U)DATE_FORMAT gère le décalage horaire */
	function parseQuery($req) 
	{
		if($this->get('decal') != '00:00:00'){
			$req = preg_replace("/_UDATE_FORMAT\((.*?)\)/i","DATE_FORMAT(FROM_UNIXTIME($1) + INTERVAL '".$this->get('decal')."' HOUR_SECOND,'".$this->dateformat."')",$req);
			$req = preg_replace("/_DATE_FORMAT\((.*?)\)/i","DATE_FORMAT($1 + INTERVAL '".$this->get('decal')."' HOUR_SECOND,'".$this->dateformat."')",$req);
		}else{
			$req = preg_replace("/_UDATE_FORMAT\((.*?)\)/i","FROM_UNIXTIME($1,'".$this->dateformat."')",$req);
			$req = preg_replace("/_DATE_FORMAT\((.*?)\)/i","DATE_FORMAT($1,'".$this->dateformat."')",$req);
		}
		return $req;
	}

}
