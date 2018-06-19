<?php
class diplo {
	/* variables */
	//private static $sql; // objet mysql.class.php -> global $_sql
	private $fields = [
		'dpl_did' => false,
		'dpl_etat' => false,
		'dpl_type' => false,
		'dpl_al1' => false,
		'dpl_al2' => false,
		'dpl_debut' => false,
		'dpl_fin' => false];
	var $result;
	var $al_aid; // alliance de référence
	var $err = 'inexistant'; // si erreur

	private $w_count = []; // nb de pactes par type

	// nb max de pactes par type
	const MAX = [DPL_TYPE_PNA => 2,
		DPL_TYPE_MIL => 2, DPL_TYPE_COM => 2, DPL_TYPE_MC => 1];
	// prix du pacte à la signature (en or)
	const PRIX = [DPL_TYPE_PNA => [GAME_RES_PRINC => 100],
		DPL_TYPE_MIL => [GAME_RES_PRINC => 200],
		DPL_TYPE_COM => [GAME_RES_PRINC => 500],
		DPL_TYPE_MC => [GAME_RES_PRINC => 1000]];
	// durée probatoire d'un pacte accepté (HEURE)
	const DUREE_PROBA = 5;
	// taxe des ressources (pacte commercial
	const DPL_TAX = 20;

	/* constructeur */
	function __construct( $cond = []) {
		if (!empty($cond)) { // lecture
			// alliance référencée
			if (isset($cond['aid'])) $this->al_aid = protect($cond['aid'], 'uint');
			$this->result = self::select($cond);
			// lire le 1er résultat
			if($this->result) foreach($this->result as $key => $this->fields) break;
		}
	}

	/* méthodes set & get */
	function __set($key, $value) {
		if(isset($this->fields[$key])) {
			switch ($key) {
			case 'me':
				$this->fields[$key] = protect($value, 'bool');
				break;
			case 'dpl_did':
			case 'dpl_etat':
			case 'dpl_type':
			case 'dpl_al1':
			case 'dpl_al2':
			case 'dpl_al':
			case 'al_mid':
				$this->fields[$key] = protect($value, 'uint');
				break;
			default:
				$this->fields[$key] = protect($value, 'string');
				break;
			}
		} else echo "classe 'diplo'->$key = $value\n";
	}
	function __get($key) {
		if(isset($this->fields[$key]) and $this->fields[$key]!==false) return $this->fields[$key];
		else { echo "GET classe 'diplo'->$key\n"; return false; }
	}

	/* méthodes statiques */
	static function select($cond) {
		$aid = isset($cond['aid']) ? protect($cond['aid'],'uint') : 0;
                $full = isset($cond['full']) ? protect($cond['full'],'bool') : false;

                $result = Dpl::get($cond);

                // mise en forme du tableau
                if ($aid)
                    foreach($result as $key => $row)
                        if ($aid == $row['dpl_al1']) {
                            $result[$key]['dpl_al'] = $row['dpl_al2'];
                            $result[$key]['me'] = false; // on m'a proposé le pacte
                            if ($full) {
                                    $result[$key]['al_name'] = $row['al2_name'];
                                    $result[$key]['al_mid'] = $row['al2_mid'];
                            }
                        } else {
                            $result[$key]['dpl_al'] = $row['dpl_al1'];
                            $result[$key]['me'] = true; // j'ai proposé
                            if ($full) {
                                    $result[$key]['al_name'] = $row['al1_name'];
                                    $result[$key]['al_mid'] = $row['al1_mid'];
                            }
                        }
                return $result;
	}

	/* méthodes pour l'objet - non statiques */
	function exist_pacte($al, $type = false) { // vérifie que $al a déjà un pacte avec $this, et en option le type du pacte
		if (!$this->al_aid) return false;
		foreach($this->result as $pacte)
			if ($al == $pacte['dpl_al'] && $pacte['dpl_etat'] != DPL_ETAT_NO && $pacte['dpl_etat'] != DPL_ETAT_FIN)
				if ($type === false) // peu importe le type de pacte : on renvoie son type!
					return $pacte['dpl_type'];
				else // vérifie si c'est le bon pacte ?
					return ($type == $pacte['dpl_type']);
		return false;
	}

	function actuels() { // array des pactes actifs [aid => type_pace]
		if (!$this->al_aid) return false;
		$result = [];
		foreach($this->result as $pacte)
			if ($pacte['dpl_etat'] == DPL_ETAT_OK)
				$result[$pacte['dpl_al']] = $pacte['dpl_type'];
		return $result;
	}

	function proposer($al, $type) { // $al propose un new pacte a $this
		if (!$this->al_aid) return false;
		if ($this->count($type) >= self::$max[$type]) {
			$this->err = 'nb_pactes';
			return false;
		}
		if ($this->exist_pacte($al)) {
			$this->err = 'exist';
			return false;
		}
		$cond = ['al1' => $this->al_aid, 'al2' => $al, 'type' => $type];
		return Dpl::add($cond);
	}

	function rompre() {
		if ($this->fields['dpl_did']) {
			if ($this->dpl_etat == DPL_ETAT_ATT || $this->dpl_etat == DPL_ETAT_OK) {
				$cond = [
					'did' => $this->fields['dpl_did'],
					'etat' => DPL_ETAT_FIN,
					'fin' => 'now'];
				return Dpl::edit($cond);
			} else
				$this->err = 'etat';
		}
		return false;
	}

	function accepter() {
		if ($this->fields['dpl_did']) {
			if ($this->dpl_etat == DPL_ETAT_PROP) {
				$cond = [
					'did' => $this->fields['dpl_did'],
					'etat' => DPL_ETAT_ATT,
					'debut' => 'now'];
				return Dpl::edit($cond);
			} else
				$this->err = 'etat';
		}
		return false;
	}

	function refuser() {
		if ($this->fields['dpl_did']) {
			if ($this->dpl_etat == DPL_ETAT_PROP) {
				$cond = [
					'did' => $this->fields['dpl_did'],
					'etat' => DPL_ETAT_NO,
					'debut' => 'now'];
				return Dpl::edit($cond);
			} else
				$this->err = 'etat';
		}
		return false;
	}

	function auteur() { /* alliance qui a proposé le pacte en 1er = al2 */
		if ($this->fields['dpl_did'])
			return $this->fields['dpl_al2'];
		else
			return false;
	}
/*
	function save() {
		$fields = ''; $values = '';
		foreach( $this->fields as $key => $value)
			if ($value !== false) {
				$fields .= $key;
				$values .= $value;
			}
	}
*/
	function count($type = 0) { // nb de pactes d'un type donné
		if (empty($this->w_count)) { // compter les pactes actifs & en attente
			foreach(self::$max as $key => $nb) // init
				$this->w_count[$key] = 0;
			foreach($this->result as $pacte) // compter
				if ($pacte['dpl_etat'] != DPL_ETAT_NO && $pacte['dpl_etat'] != DPL_ETAT_FIN)
					$this->w_count[$pacte['dpl_type']]++;
		}
		if ($type == 0)
			return $this->w_count;
		else
			return isset($this->w_count[$type]) ? $this->w_count[$type] : 0;
	}
}

?>
