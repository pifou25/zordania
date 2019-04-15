<?php

class leg_gen extends legion { /* surcharge du constructeur pour 1 légion ... */

	function __construct($leg, $unt = [], $res = []){ /* les 'data' dans les tableaux */
		$this->infos = $leg;
		$this->mid = $this->infos['mbr_mid'];
		$this->race = $this->infos['mbr_race'];
		$this->cid = $this->infos['leg_cid'];
		$this->lid = $this->infos['leg_id'];
		$this->etat = $this->infos['leg_etat'];
		if($this->infos['hro_id']) { // récupérer la vie du héros
			$this->hid = $this->infos['hro_id'];
			$this->infos['hro_vie_conf'] = Config::get($this->race, 'unt', $this->getHro('type'), 'vie');
			$this->comp = $this->infos['bonus'];
		}

		if(is_array($unt)) {
			$this->unt = $unt; // définir les unités si on connait
			$this->w_load_unt = true;
		} else
			$this->w_load_unt = (bool) $unt;

		if(is_array($res)) {
			$this->res = $res; // les ressources de la légion si on connait
			$this->w_load_res = true;
		} else
			$this->w_load_res = (bool) $res;
		return true;
	}
	function __destruct() { parent::__destruct(); }

} /* fin classe leg_gen extends legion */



class legions { /* classe pour plusieurs légions ... */
	public  $legs = array(); // tableau d'objets
	public  $vlg_lid = 0; // id légion du village
	public  $btc_lid = 0; // id legion batiments
	public  $mid = 0; // mid du joueur si toutes les légions ont le même mid
	public  $cid = 0; // si toutes les légions sont à la même place
	public  $cids = []; // emplacements de chaque légion
	public  $lids = []; // liste des id des légions
	private $load_unt = false;
        private $load_res = false;

	function __construct($cond, $unt = false, $res = false){ /* plusieurs légions objet */
		/* $cond = tableau des critères pour la fonction get_leg_gen :
		$cond['mid'] pour toutes les légions du joueur $mid
		$cond['lid'] = tableau lid des légions
		$cond['cid'] toutes les légions à cette position
		$cond['etat'] = filtre les légions sur leur état (tableau)
		*/
		$cond['leg'] = true;
		$cond['mbr'] = true;
		if(isset($cond['etat']))
			$cond['etat'] = protect($cond['etat'], ['uint']);
//		else
//			$cond['etat'] = array(Leg::ETAT_VLG, Leg::ETAT_GRN, Leg::ETAT_POS,
//				Leg::ETAT_DPL, Leg::ETAT_ALL, Leg::ETAT_RET, Leg::ETAT_ATQ);

		$leg_array = Leg::get($cond); // récupérer les légions

		if(empty($leg_array)) return false;
		// initialisation
		$this->mid = $leg_array[0]['mbr_mid'];
		$this->cid = $leg_array[0]['leg_cid'];

		foreach($leg_array as $leg){
			$this->lids[$leg['leg_id']]  = $leg['leg_id'];  // lister les légions trouvées
			$this->cids[$leg['leg_cid']] = $leg['leg_cid']; // lister les emplacements
			if($this->mid != $leg['mbr_mid']) $this->mid = 0; // plusieurs joueurs
			if($this->cid != $leg['leg_cid']) $this->cid = 0; // plusieurs positions
			if($leg['leg_etat'] == Leg::ETAT_VLG)
				$this->vlg_lid = $leg['leg_id']; // la légion au village
			else if($leg['leg_etat'] == Leg::ETAT_BTC)
				$this->btc_lid = $leg['leg_id']; // legion batiments
		}

		if($res)
			$res_leg = $this->get_all_res();
		else
			$res_leg = [];

		if($unt)
			$unt_leg = $this->get_all_unts();
		else
			$unt_leg = [];

		foreach($leg_array as $leg){ // définir les objets legion
			$lid = $leg['leg_id'];
			if(!$unt) $unt_leg[$lid] = false; // non initialisé
			else if(!isset($unt_leg[$lid])) $unt_leg[$lid] = [];
			if(!$res) $res_leg[$lid] = false; // non initialisé
			else if(!isset($res_leg[$lid])) $res_leg[$lid] = [];
			$this->legs[$lid] = new leg_gen($leg, $unt_leg[$lid], $res_leg[$lid]);
		}
		return true;
	}

	function get_all_unts(){// toutes les unités pour ces légions
		$unt_leg = array();
		if ($this->load_unt){// déjà chargé
			foreach($this->legs as $leg)
				$unt_leg[$leg->lid] = $leg->get_unt();
		} elseif ($this->lids) {
			$cond = array('unt' => true, 'leg' => $this->lids);
			$unt_array = Leg::get($cond);
			foreach($unt_array as $values) // rassembler les unités par $lid
				$unt_leg[$values['unt_lid']][$values['unt_type']] = $values['unt_nb'];
			$this->load_unt = true;
		}
		return $unt_leg;
	}

	function get_all_res(){ // toutes les ressources des légions sélectionnées
            $res = [];
            if($this->load_res){
                foreach($this->legs as $leg){
                    $res[$leg->lid] = $leg->get_res();
                }
            }else{
                $arr = LegRes::whereIn('lres_lid', $this->lids)->get()->toArray();
                $res = [];
                foreach ($arr as $val){
                    $res[$val['lres_lid']][$val['lres_type']] = $val['lres_nb'];
                }
                $this->load_res = true;
            }
            return $res;
	}

	function hasUntByRole($role){ // oui si au moins une légion a une unité de ce rôle
		foreach($this->legs as $leg){
			$unts = $leg->getUntByRole($role);
			if(!empty($unts))
				return true;
		}
		return false;
	}

        /**
         * unused
         * get_square pour toutes les légions
         * dst = array(x=>xx, y=>yy)  pour distance à {xx,yy}
         * @param type $dst
         * @return type
         */
	function get_cids($dst = false) { 
		$squares = Map::getGen($this->cids, $dst);
		$this->cids = array();
		foreach ($squares as $key => $sqr)
			$this->cids[$sqr['map_cid']] = $sqr;

		foreach($this->legs as $leg)
			if (isset($this->cids[$leg->cid]))
				$leg->sqr = &$this->cid;
		return $this->cids;
	}

	function get_all_legs_infos(){
		$legs = array();
		foreach($this->legs as $leg)
			$legs[$leg->lid] = $leg->infos;
		return $legs;
	}

	public function flush() { 
		// MAJ edit unités
		foreach($this->legs as $leg)
			$leg->flush();
	}

} /* fin classe legions */
