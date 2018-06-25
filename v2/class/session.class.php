<?php
class session
{
	var $sql;
	var $vars;
	
	function __construct(&$db)
	{
		$this->sql = &$db; //objet de la classe mysql
		if(!CRON){
                    $this->vars = & $_SESSION['user'];
		}
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
		$this->set("droits", get_droits($mbr_infos['mbr_gid']));
		$this->set("groupe", $mbr_infos['mbr_gid']);
		$this->set("decal", $mbr_infos['mbr_decal']);
		$this->set("place", $mbr_infos['mbr_place']);
		$this->set("population", $mbr_infos['mbr_population']);
		$this->set("points", $mbr_infos['mbr_points']);
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
		else     $this->set("ip", get_ip());
		$this->set("design", $mbr_infos['mbr_design']);
		$this->set("parrain", $mbr_infos['mbr_parrain']);
		$this->set("numposts", $mbr_infos['mbr_numposts']);
		$this->set("mobile", isset($_SESSION['mobile']) ? $_SESSION['mobile'] : false);
                if(!$this->get('mobile')){
                    $this->set('btc', '');
                }else{
                    $this->set('btc', '/2');
                }

		/* Visiteur */
		if($this->get("login") == "guest") {
			$this->set("loged", false);
			$this->set("lang", get_lang());
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
		$this->set("droits", get_droits($gid));
	}

	function update_msg() {
		$mid = $this->get("mid");
		
		if($this->get("login") != "guest") {
                    /* Nouveaux messages */
			$sql="SELECT COUNT(*) AS nb FROM ".$this->sql->prebdd."msg_rec JOIN "
                                .$this->sql->prebdd."mbr ON mrec_from = mbr_mid WHERE mrec_mid = $mid "
                                . "AND mrec_readed = 0";
			$result = $this->sql->make_array_result($sql);
			$this->set("msg", $result['nb']);
		
		
			/* Nouvelle news? mbr_ldate = dernière connexion heure locale */
			$sql="SELECT count(*) AS nb FROM ".$this->sql->prebdd."frm_topics ".$this->sql->prebdd.
			  " WHERE forum_id =".ZORD_NEWS_FID." AND posted > (SELECT mbr_ldate FROM "
                                .$this->sql->prebdd."mbr WHERE mbr_mid = $mid)";
			$result = $this->sql->make_array_result($sql);
			$this->set("news", $result['nb']);
                        
			if($result['nb'] > 0){
                            /*Select la dernière news*/
                            $sql="SELECT id, subject FROM ".$this->sql->prebdd."frm_topics ".$this->sql->prebdd
                                    ." WHERE forum_id =".ZORD_NEWS_FID." AND posted="
                                    . "(SELECT MAX(posted) FROM ".$this->sql->prebdd."frm_topics)";
                            $result = $this->sql->make_array_result($sql);
                            $this->set("tid", $result['id']);
                            $this->set("sub", $result['subject']);
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
                                        get_conf_gen($this->get('race'), 'unt', $result['hro_type'], 'vie'));
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
				$ip = get_ip();
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
}
