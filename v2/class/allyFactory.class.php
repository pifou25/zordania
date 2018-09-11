<?php
/* tableau d'alliances et fonctions SQL dédiées */
class allyFactory {

	private static $allies = array(); // tableau d'alliances
	private static $table; // tableau de la dernière requete SQL SELECT = cache

	/* STATIC et CONSTANTES */
	/* droits & restrictions pour l'alliance : $_sub pour la page gestion alliance, et qui y a droit */
	/* + grenier + diplo */
	const DROITS_ALLY = array(
		'grenier'=> array(ALL_ETAT_OK,ALL_ETAT_INTD,ALL_ETAT_DPL,ALL_ETAT_RECR,ALL_ETAT_SECD,ALL_ETAT_CHEF),
		'accept' => array(ALL_ETAT_RECR,ALL_ETAT_SECD, ALL_ETAT_CHEF),
		'refuse' => array(ALL_ETAT_RECR,ALL_ETAT_SECD, ALL_ETAT_CHEF),
		'param'  => array(ALL_ETAT_RECR,ALL_ETAT_SECD, ALL_ETAT_CHEF),
		'rules'  => array(ALL_ETAT_INTD,ALL_ETAT_SECD,ALL_ETAT_CHEF),
		'perm'   => array(ALL_ETAT_INTD,ALL_ETAT_SECD,ALL_ETAT_CHEF),
		'diplo'  => array(ALL_ETAT_DPL,ALL_ETAT_SECD,ALL_ETAT_CHEF),
		'logo'   => array(ALL_ETAT_SECD,ALL_ETAT_SECD,ALL_ETAT_CHEF),
		'kick'   => array(ALL_ETAT_CHEF),
		'chef'   => array(ALL_ETAT_CHEF),
		'del'    => array(ALL_ETAT_CHEF),
	);

	const MAX_DRTS_ALLY = array(ALL_ETAT_CHEF => 1, ALL_ETAT_SECD => 1, ALL_ETAT_RECR => 1, ALL_ETAT_DPL => 1, ALL_ETAT_INTD => 1);

	/* méthodes statiques */
	static function select($cond) {
		$limite1 = 0; $limite2 = 0; $limite = 0; $name = 0;

		if(isset($cond['limite1'])) {
			$limite1 = protect($cond['limite1'], "uint");
			$limite++;	
		}
		if(isset($cond['limite2'])) {
			$limite2 = protect($cond['limite2'], "uint");
			$limite++;	
		}
	
		if(isset($cond['name']))
			$name = protect($cond['name'], "string");

                $req = Al::join('mbr', 'al_mid', 'mbr_mid');
                if($limite)
                    $req->select('al_aid','al_name','al_nb_mbr','al_mid', 'al_points','al_open','mbr_pseudo',
                            'mbr_race', 'mbr_gid','mbr_mid')->selectRaw(ALL_ETAT_CHEF . ' AS ambr_etat');
                else
                    $req->select('*')->selectRaw(ALL_ETAT_CHEF . ' AS ambr_etat');

                if(isset($cond['aid']))
                    $req->where('al_aid', $cond['aid']);
                if($name)
                    $req->where('al_name', 'LIKE', "%$name%");
                if(isset($cond['mini3'])) // 3 membres mini?
                    $req->where('al_nb_mbr', '>=', 3);

                $req->orderBy('al_points', 'desc');
		
		if($limite) {
			if($limite == 2)
                            $req->offset($limite2)->take($limite1);
			else
                            $req->take($limite1);
		}
		
		self::$table = $req->get()->toArray();
		$result = array();
		foreach(self::$table as $row){
			if (isset($result[$row['al_aid']]))
				$result[$row['al_aid']]->addMbr($row);
			else
				$result[$row['al_aid']] = new ally($row);
			if(!isset(self::$allies[$row['al_aid']]))
				self::$allies[$row['al_aid']] = $result[$row['al_aid']];
		}

		return $result;
	}

	static function getList($cond){
		self::select($cond);
		return self::$table;
	}

	static function nb($all = false)/* nb d'alliances */
	{
            if($all === false)
                $req = Al::where('al_nb_mbr', '>=', 3)->count();
            else
                $req = Al::count();
	}

	static function getAlly($aid){
		if(!isset(self::$allies[$aid]))
			allyFactory::select(array('aid' => $aid));
		return isset(self::$allies[$aid]) ? self::$allies[$aid] : null;
	}

}
