<?php

/* classe pour les légions */
class legion {
	public    $infos = array(); // infos des légions
	public    $mid = 0; // pour savoir si la légion a été chargée ou pas
	public    $lid;
	public    $cid;
	public    $etat;
	public    $race;
	public    $comp = 0; // id de la compétence active ou 0
	public    $hid = false; // a un héro (id)
	protected $w_load_unt = false; // lecture des unités?
	protected $w_load_res = false; // lecture des ressource?
	protected $unt = array(); // infos des unités : get_unt
	protected $res = array(); // ressources de la légion: get_res
	protected $stats = array();
	/* bonus attaque, defense, heros(XP), batiment, competence */
	protected $bonus = ['atq' => 0, 'def' => 0, 'btc' => 0, 'cpt' => array(), 'vie' => 0];
	private   $edit_unt = array(); // pour maj unités
        private   $edit_hro = []; // pour maj unités
	public    $sqr = false; // la case (map)


	function __construct(int $lid, int $mid = 0){ // rechercher la légion par lid et mid si existe

		$this->lid = $lid;

		$cond = array('leg' => array($lid), 
				'etat' => array(Leg::ETAT_VLG, Leg::ETAT_GRN, Leg::ETAT_POS, 
					Leg::ETAT_DPL, Leg::ETAT_ALL, Leg::ETAT_RET, Leg::ETAT_ATQ), 'mbr' => true);
		if ($mid)
			$cond['mid'] = $mid;

		$leg = Leg::get($cond);
		if($leg){
			$this->infos = $leg[0];
			$this->mid = $this->infos['mbr_mid'];
			$this->race = $this->infos['mbr_race'];
			$this->cid = $this->infos['leg_cid'];
			$this->etat = $this->infos['leg_etat'];
			if($this->infos['hro_id']) { // récupérer la vie du héros
				$this->hid = $this->infos['hro_id'];
				$this->infos['hro_vie_conf'] = Config::get($this->race, 'unt', $this->getHro('type'), 'vie');
				$this->comp = $this->infos['bonus'];
			}
			return true;
		} else
			return false;
	}

	function __destruct(){
		// destruction de la legion
		$this->flush();
	}
        
        // vider le cache, maj BDD
        public function flush(){
		if (!empty($this->edit_unt)) {
                    Unt::edit($this->lid, $this->edit_unt);
                    $this->edit_unt = [];
                }
                if(!empty($this->edit_hro)) {
                    Hro::edit($this->mid, $this->edit_hro);
                    $this->edit_hro = [];
                }
        }

	private function loadUnt(){ // remplace get_unt_leg
		// toutes les unités sauf celles des batiments
		$tmp = Leg::get(array('leg' => array($this->lid), 'sum' => true,
			'etat' => array(Leg::ETAT_VLG, Leg::ETAT_GRN, Leg::ETAT_DPL, Leg::ETAT_RET, Leg::ETAT_ATQ)));
		$this->unt = array();
		foreach($tmp as $unt)
			$this->unt[$unt['unt_type']] = $unt['unt_sum'];
		$this->w_load_unt = true; // au cas où la légion est vraiment vide !!
		return !empty($this->unt);
	}

	function getHro($key) { // lire une caractérisique du héros (si existe)
		if ($this->hid && isset($this->infos['hro_'.$key]))
			return $this->infos['hro_'.$key];
		else
			return 0;
	}

	function setHro($key, $value) { // maj une caractérisique du héros (si existe)
		if ($this->hid && isset($this->infos['hro_'.$key])){
                    // max d'energie pour le heros
                    if($key == 'nrj' && $value > HEROS_NRJ_MAX){
                        $value = HEROS_NRJ_MAX;
                    }
                    $this->infos['hro_'.$key] = $value;
                    $this->edit_hro[$key] = $value;
                }
	}

	function addHro($key, $value) { // increment une caractérisique du héros (si existe)
            $this->setHro($key, $this->getHro($key) + $value);
	}
        
	function cp_bonus($type) { // donne le bonus de la compétence $type si elle est activée
		if ($this->comp == $type) {
			$return = Config::get($this->race, 'comp', $type, 'bonus');
			if (is_numeric($return))
				return $return;
			else
				return 0;
		}
		else
			return 0;		
	}


        /**
         * récupérer toutes les infos d'un bonus, format array
         * tableau générique array( heros=> array, bonus=> %, tours=>tours, prix_xp=>prix, race=>$race, res=>$resultat)
         * @param int $cp_id
         * @param int $race
         * @param type $res
         * @return array
         */
        function getComp($res = false) {
                $cp = Config::get($this->race, 'comp', $this->comp);
                $cp['cpid'] = $this->comp;
                $cp['race'] = $this->race;
                if ($res !== false)
                        $cp['res'] = $res;
                return $cp;
        }

	function get_unt(int $type = 0){ // renvoyer les unités de la légion, toutes ou celles de $type

		if(!$this->w_load_unt) // rechercher toutes les unités si pas fait
			$this->loadUnt();

		if($type)
			if(isset($this->unt[$type]))
				return $this->unt[$type];
			else
				return 0;
		else // renvoyer toutes les unités
			return $this->unt;
	}

	function getUntByRole(int $role){ // rend les unités qui ont ce rôle (constants TYPE_UNT*)
		$result = array();

		if(!$this->w_load_unt) // rechercher toutes les unités si pas fait
			$this->loadUnt();

		foreach($this->unt as $type => $nb){
			$untRole = Config::get($this->race, 'unt', $type, 'role');
			if($untRole == $role)
				$result[$type] = $nb;
		}
		return $result;
	}

	function unit_normal(int $type = 0) { // unités 'normales' = sauf le héros
		$unt = $this->get_unt($type);
		$hro_type = $this->getHro('type');
		if ($hro_type) unset($unt[$hro_type]);
		if($type)
			if(isset($unt[$type]))
				return $unt[$type];
			else
				return 0;
		else // renvoyer toutes les unités
			return $unt;
	}

	function vide(){ // la légion est-elle vide ?? ni unités ni héros
		if(!$this->w_load_unt) $this->get_unt(); // rechercher les unités si pas fait
		return empty($this->unt);
	}

	function add_unt(int $unt, int $nb = 1, int $factor = 1){
	/* ajouter $nb unités $unt (peut être négatif)
		ou ($nb * $factor) unités $unt
		si $unt = array : $nb est pris en tant que facteur et $factor est ignoré
		return = modifications effectuées
	*/
		if (is_array($unt)) {
			$return = array();
			foreach ($unt as $key => $value)
				$return[$key] = $this->add_unt($key, $value, $nb);
			return $return;
		} else {
			$nb = round($nb * $factor);
			$rang = (int) Config::get($this->race, 'unt', $unt, 'rang');
			// modifier les unités si déjà chargé
			if ($this->w_load_unt) {
				if (!isset($this->unt[$unt])) $this->unt[$unt] =0;
				if ($this->unt[$unt] + $nb < 0) $nb = -$this->unt[$unt]; // test si on enlève trop!
				$this->unt[$unt] += $nb;
				if ($this->unt[$unt] == 0) unset($this->unt[$unt]);
			}
			// cumuler en cache
			if (isset($this->edit_unt[$rang]) && isset($this->edit_unt[$rang][$unt]))
				$this->edit_unt[$rang][$unt] += $nb;
			else
				$this->edit_unt[$rang][$unt] = $nb;
			return $nb;
		}
	}

	function del_unt( $unt, int $nb = -1) { // supprimer nb unités; inverser le résultat
		$return = $this->add_unt($unt, $nb, -1);
		if (is_array($unt))
			foreach($return as $key => $val)
				$return[$key] = - $val;
		else
			$return = -$return;
		return $return;
	}

	function get_edit_unt($type = 0) { // recup nb d'unites a modifier
		if ($type) {
			$rang = (int) Config::get($this->race, 'unt', $type, 'rang');
			if(isset($this->edit_unt[$rang][$type]))
				return $this->edit_unt[$rang][$type];
			else
				return 0;
		} else
			return $this->edit_unt;
	}

	public function flushUnt(bool $get = false) { // exécuter la MAJ unités ou récupérer
            Unt::edit($this->lid, $this->edit_unt);
            $this->edit_unt = [];
	}

	function get_res(int $type = 0){ // renvoyer les ressources de la légion, toutes ou celles de $type

		if(!$this->w_load_res){ // rechercher toutes les ressources
			$res = LegRes::get($this->mid, $this->lid);
			if(!empty($res))
				foreach($res as $value) // réindexer !
					$this->res[$value['lres_type']] = $value['lres_nb'];
			$this->w_load_res = true; // si la légion est vraiment vide !!
		}

		if($type)
			if(isset($this->res[$type]))
				return $this->res[$type];
			else
				return 0;
		else // renvoyer toutes les ressources
			return $this->res;
	}

	function mod_res($res){ // ajouter / enlever les ressources
		LegRes::edit($this->lid, $res);
		// modifier les ressources si déjà chargé
		foreach($res as $type => $nb)
			if(isset($this->res[$type]))
				$this->res[$type] += $nb;
	}

	function back_unt($type) { // rentrer les unités $type au village
        if (!$this->vide())
            if (isset($this->unt[$type]) && $this->unt[$type])
                // si la legion est bien au village
                if (session::$SES->get('mapcid') == $this->cid && $this->infos['leg_etat'] == Leg::ETAT_GRN) {
                    $vlg = Leg::where('leg_mid', $this->mid)->where('leg_etat', Leg::ETAT_VLG);
                    if($vlg->count() == 0){
                        echo 'Aucun village ici';
                    }else{
                        $nb = $this->unt[$type];
                        // supprimer les unités de la légion en cours
                        Unt::where('unt_lid', $this->lid)->where('unt_type', $type)->delete();
                        // ajouter au village
                        Unt::edit($vlg->first()->leg_id, [$type, $nb]);
                        unset($this->unt[$type]); // suppr en mémoire
                    }
                }
        }

        function stats($type = ''){ // stats de la légion
		if(empty($this->stats)){ // calculer les stats
			$this->stats = array('unt_nb' => 0, 'atq_unt' => 0, 'atq_btc' => 0, 'def' => 0, 'vit' => 0,
				'vie' => 0); // zero par défaut
			if(!$this->vide()) {
				foreach($this->unt as $unt => $nb) {
					/* on ne prend pas en compte les unités civiles */
					$role = Config::get($this->race, 'unt', $unt, 'role');
					if ($role != TYPE_UNT_CIVIL) {
						$this->stats['unt_nb']  += $nb;
						$this->stats['atq_unt'] += (int) Config::get($this->race, 'unt', $unt, 'atq_unt') * $nb;
						$this->stats['atq_btc'] += (int) Config::get($this->race, 'unt', $unt, 'atq_btc') * $nb;
						$this->stats['def']     += (int) Config::get($this->race, 'unt', $unt, 'def') * $nb;
						if ($role == TYPE_UNT_HEROS) // vie restante du héros
							$this->stats['vie'] += $this->getHro('vie');
						else
							$this->stats['vie'] += (int) Config::get($this->race, 'unt', $unt, 'vie') * $nb;
						$bon_unt = Config::get($this->race, "unt", $unt, "bonus");
						foreach ($bon_unt as $key => $value)
							$this->bonus[$key] += $value * $nb;
					}
				}
				// bonus maxi = 30
				$this->bonus['atq'] = min( 30, $this->bonus['atq']);
				$this->bonus['def'] = min( 30, $this->bonus['def']);
				// vie : bonus maxi = 70. formule alambiquée
				$this->bonus['vie'] = min(round(log10(($this->bonus['vie']+20)/20) * ATQ_RATIO_DIST, 1), 70);
				//$this->bonus['hro'] = round($this->hro('xp') / 100,1); // plus de bonus XP

				if ($this->comp) {
					$bonus = Config::get($this->race, 'comp', $this->comp, 'bonus');
					if ($this->comp == CP_BOOST_OFF || $this->comp == CP_BOOST_OFF_DEF)
						$this->bonus['cpt']['atq'] = $bonus;
					if ($this->comp == CP_BOOST_DEF || $this->comp == CP_BOOST_OFF_DEF)
						$this->bonus['cpt']['def'] = $bonus;
					if ($this->comp == CP_RESISTANCE)
						$this->bonus['cpt']['vie'] = $bonus;
					if ($this->comp == CP_CASS_BAT)
						$this->bonus['cpt']['btc'] = $bonus;
					if ($this->comp == CP_VITESSE)
						$this->bonus['cpt']['vit'] = $bonus;
				}
				// calculer la vitesse
				if($this->etat == Leg::ETAT_DPL)
					$this->stats['vit'] = $this->infos['leg_vit'];
				else
					$this->stats['vit'] = $this->calc_vit();
			}
			$this->stats = $this->stats;
		}

		if($type != '')
			return (isset($this->stats[$type]) ? $this->stats[$type] : 0);
		else
			return $this->stats;
	}

	function bonus($type = '') { // les bonus de la légion
		if(empty($this->stats)) // le 1er appel force à calculer les stats
			$this->stats();
		if ($type != '')
			return (isset($this->bonus[$type]) ? $this->bonus[$type] : 0);
		else
			return $this->bonus;
	}

	function set_bonus_btc($bonus) {
		if ($this->comp == CP_DEFENSE_EPIQUE)
			$bonus_cp = Config::get($this->race, 'comp', $this->comp, 'bonus');
		else
			$bonus_cp = 0;
		$this->bonus['btc'] = $bonus['bon'] + $bonus_cp;
	}

	function vitesse(){ // méthode publique pour récupérer la vitesse
		return $this->stats('vit');
	}

	function nb_unt(){ return $this->stats('unt_nb'); }
	/* calcul du total avec tous les bonus possibles */
	function atq_unt(){
		return round($this->stats('atq_unt')
			* ( 1 + ( $this->bonus('atq')
				+ (isset($this->bonus['cpt']['atq']) ? $this->bonus['cpt']['atq'] : 0)
			) / 100 ) ) ;
	}
	function atq_btc(){
		return round($this->stats('atq_btc')
			* ( 1 + ( $this->bonus('atq')
				+ (isset($this->bonus['cpt']['btc']) ? $this->bonus['cpt']['btc'] : 0)
			) / 100 ) ) ;
	}
	function def_unt(){
		return round($this->stats('def')
			* ( 1 + ( $this->bonus('def')
				+ $this->bonus('btc')
				+ (isset($this->bonus['cpt']['def']) ? $this->bonus['cpt']['def'] : 0)
			) / 100 ) ) ;
	}
	function unt_vie($type = 0){ // vie des unités $type avec bonus compétence
		if ($type == 0) // vie globale légion
			return round( $this->stats('vie')
				* ( 1 + (isset($this->bonus['cpt']['vie'])?$this->bonus['cpt']['vie']:0) / 100));
		else if(empty($this->unt[$type]))
			return 0;
		else // nb unités * vie * bonus compétence
			return round( $this->get_unt[$type] * (int) Config::get($this->race, 'unt', $type, 'vie')
				* ( 1 + (isset($this->bonus['cpt']['vie'])?$this->bonus['cpt']['vie']:0) / 100));
	}

	function atq_fin() { // caractéristiques pour l'attaque
		$att   = $this->atq_unt();
		return array('unt' => $att,
			'fin' => round($att / ATQ_RATIO_COEF_ATQ),
			'bat' => $this->atq_btc(),
			'nb' => $this->nb_unt());
	}

	function def_fin($ratio = 1) { // caractéristiques pour la défense
		// $ratio = coef pour la défense groupée [0-1]
		$def = $this->def_unt();
		return array('unt' => round($def * $ratio),
			'fin' => round($def * $ratio / ATQ_RATIO_COEF_DEF),
			'nb' => $this->nb_unt());
	}

	public function calc_vit() { // calculer la vitesse d'après les unités de la légion
		$have_unt = $this->get_unt();	
		if(empty($have_unt))
			return 0;

		$speed_array = $carry_array = array();
		foreach($have_unt as $type => $nb) {
			$vit = protect(Config::get($this->race, "unt", $type, "vit"), "uint");
			$carry = protect(Config::get($this->race, "unt", $type, "carry"), "uint");
		
			if($carry) {
				if(!isset($carry_array[$vit]))
					$carry_array[$vit] = 0;
				$carry_array[$vit] += $nb * $carry; // capacité de transport
			}
			if(!isset($speed_array[$vit]))
				$speed_array[$vit] = 0;
			$speed_array[$vit] += $nb; // nb d'unités par vitesse
		}
		ksort($speed_array);
		ksort($carry_array);
	
		foreach($carry_array as $vitc => $nbc) {// décompter les unités transportées
			foreach($speed_array as $vitu => $nbu) {
				if($vitc <= $vitu || !$nbc)
					continue;
				if(!isset($speed_array[$vitc]))
					$speed_array[$vitc] = 0;
			
				if($nbc >= $nbu) {
					$nbc -= $nbu;
					$speed_array[$vitu] -= $nbu;
					$speed_array[$vitc] += $nbu;
				} else {
					$speed_array[$vitu] -= $nbc;
					$speed_array[$vitc] += $nbc;
					$nbc = 0;
				}
			}
		}

		$total = 0; $nbt = 0; // calcul vitesse totale (moyenne)	
		foreach($speed_array as $vit => $nb) {
			$total += $vit * $nb;
			$nbt += $nb;
		}
		/* compétence 'pas de course' : ajouter x % */
		if (isset($this->bonus['cpt']['vit'])) {
			$total = floor($total * (1 + $this->bonus['cpt']['vit'] / 100));
		}
		return ($nbt == 0 ? 0 : round($total / $nbt));
	}

	function pertes ($deg, $civils = false, $hit_hro = true) { /* pertes unités */
	/* 'consomme' les dégats en éliminant les unités
	IN : $deg = nb de points de dégats infligés
		 $civils = true pour tuer (aussi) les civils
	OUT: array ('unt' => array(type => nombre des unités perdues),
			'deg_hro' => dégats subit par le héros,
			'hro_reste' => vie restante)
	*/
		$unt = $this->get_unt();
		$pertes = array();
		$bonus = min($this->bonus('vie'), 100); // dégat minoré si bonus unités distance
		$deg_min = $deg * (1 - $bonus / 100);
		$deg_calc = $deg_min;

		foreach($unt as $type => $nb) {
			/* supprimer les unites civiles ?? pas les heros */
			$role = Config::get($this->race, "unt", $type, "role");
			if (($civils || $role != TYPE_UNT_CIVIL) && $role != TYPE_UNT_HEROS) {
				$vie_unt = Config::get($this->race, "unt", $type, "vie");
				$nb_max = round($deg_calc / $vie_unt);
				$pertes[$type] = $this->del_unt($type, $nb_max);
				$deg_calc -= $nb * $vie_unt;
				if ($deg_calc <= 0) break;
			}
		}

		/* gestion du héros :
		mini ATQ_RATIO_HEROS% s'il est bien entouré
		maxi 100% du dégat s'il est tout seul dans la legion
		*/
		$deg_hro = 0; $hro_reste = 0; $ratio = 0; /* initialisation si aucun héros */
		if ($hit_hro && $this->getHro('vie')) {
			$ratio = $this->getHro('vie_conf') / $this->stats('vie') * ATQ_RATIO_HEROS;
			$deg_hro = ceil($deg * $ratio);
			$hro_reste = max(0, $this->getHro('vie') - $deg_hro);
			if ($hro_reste <= 0) { /* tuer le heros, sauf compétence resurrection ! */
				if ($this->comp == CP_RESURECTION) {
					/* ressucité dans la légion */
					$hro_reste = $this->getHro('vie_conf');
				} else
					$pertes[$this->getHro('type')] = 1;
			}
		}

		return array('deg_sub' => $deg_min, 'deg_rest' => $deg_calc,'degats' => $deg, 'unt' => $pertes, 'bonus_vie' => $bonus,
			'deg_hro' => $deg_hro, 'hro_reste' => $hro_reste, 'hro_ratio' => $ratio);
	} /* fin calcul des pertes unités & héros */

	function edit($new) {
		// editer aussi le heros si existe
		if ($this->hid) {
			$edit_hro = [];
			if(isset($new['hro_name']))
				$edit_hro['name'] = $new['hro_name'];
			if(isset($new['hro_type']))
				$edit_hro['type'] = $new['hro_type'];
			if(isset($new['hro_lid']))
				$edit_hro['lid'] = $new['hro_lid'];
			if(isset($new['nrj']))
				$edit_hro['nrj'] = $new['nrj'];
			if(isset($new['bonus']))
				$edit_hro['bonus'] = $new['bonus'];
			if(isset($new['bonus_from']))
				$edit_hro['bonus_from'] = $new['bonus_from'];
			if(isset($new['bonus_to']))
				$edit_hro['bonus_to'] = $new['bonus_to'];
			if(isset($new['hro_vie'])){
				$edit_hro['vie'] = $new['hro_vie'];
				if($edit_hro['vie'] == 0) // héros mort = annuler son bonus
					$edit_hro['bonus'] = 0;
			}
			if (!empty($edit_hro))
				Hro::edit($this->mid, $edit_hro);
		}

                $editLeg = [];
                foreach($new as $key => $value){
                    if(in_array($key, ['etat', 'vit', 'dest', 'cid', 'name'])){
                        if($key == 'etat')
                            $this->etat = $value;
                        else if($key == 'cid'){
                            $this->cid = $value;
                            $editLeg['leg_stop'] = DB::raw('NOW()');
                        }else if($key == 'name')
                            $value = trim($value);
                        $editLeg["leg_$key"] = $value;
                        $this->infos["leg_$key"] = $value;
                    }
                }
		if(empty($editLeg))
			return 0;
                return Leg::where('leg_id', $this->lid)->update($editLeg);
	}

	function move($dest) {
		// donne la destination et la vitesse, et fait le départ sans attendre le tour
		$new = array('vit' => $this->vitesse(), 'dest' => $dest, 'etat' => Leg::ETAT_DPL);
		$this->edit($new);
	}

} /* fin classe légion */

