<?php 
// Met des ressource en création dans l'ordre du tableau
function scl_res($mid, $research)
{
	global $_sql2;

	if($research)
	{
		$requests = [];

		foreach($research as $type => $number)
			array_push($requests, [
				'rtdo_mid'  => protect($mid, 'uint'), 
				'rtdo_type' => protect($type, 'uint'), 
				'rtdo_nb'   => protect($number, 'uint')
			]);

		if (count($research) == 1)
			$requests = $requests[0];

		return $_sql2::table('res_todo')->insertGetId($requests);
	}
	return false;
}
// Annule la création d'une ressource
function cancel_research($memberId, $researchId, $number)
{
	global $_sql2;
	return $_sql2::table('res_todo')->insertGetId([
		'rtdo_nb'  => protect($number, 'uint'), 
		'rtdo_mid' => protect($memberId, 'uint'), 
		'rtdo_id'  => protect($researchId, 'uint')
	]);
}
// Modifie les ressources d'un membre, mais comparativement (ressources courante + machin)
function mod_res($memberId, $research, $factor = 1)
{
	$research = protect($research, 'array');
	$factor = protect($factor, 'float');
	
	foreach($research as $type => $number)
		if($number && $factor)
			$research[$type] = $number * $factor;

	return edit_res_gen(['mid' => $memberId, 'comp' => true], $research);
}
// Modifie les ressources qui remplissent certaines conditions
function edit_res_gen($cond, $research)
{
	global $_sql2;
	
	if ($research)
	{
		$req = $_sql2::table('res');
		$cond = protect($cond, 'array');
		$mid = (isset($cond['mid'])) ? $req->where('res_mid', '=', protect($cond['mid'], 'uint')) : false;

		foreach(protect($research, 'array') as $type => $number)
			if(isset($cond['comp']) && protect($cond['comp'], 'bool'))
				$req->increment(['res_type' . protect($type, 'uint'), protect($number, 'int')]);
			else
				$req->update(['res_type' . protect($type, 'uint'), protect($number, 'int')]);

		return $req->get();
	}
	return true;
}
/* Récupère les ressources du joueur */
function get_res_done($mid, $res =  [], $race = 0, $exc = [])
{
	global $_sql2;
	$req = $_sql2::table('res');
	$mid = protect($mid, 'uint');
	$res = protect($res, 'array');
	$race = protect($race, 'uint');
	$exc = protect($exc, 'array');
	$columnsSelector = [];

	if(!$res)
	{
		if($race)
			$number = get_conf_gen($race, 'race_cfg', 'res_nb');
		else
			$number = get_conf('race_cfg', 'res_nb');

		for($i = 1; $i <= $number; ++$i)
			array_push($columnsSelector, "res_type" . $i);
	}
	else
	{
		if($exc)
			for($i = 1; $i <= 17; $i++)
				if(!in_array($i, $exc))
					$res[] = $i;

		foreach($res as $type)
			array_push($columnsSelector, "res_type" . protect($type, 'uint'));
	}
	return json_decode(json_encode($req->select($columnsSelector)->where('res_mid', '=', $mid)->get()), true);
}
/* Ressources en cours du joueur */
function get_res_todo($mid, $cond = [])
{
	global $_sql2;

	$mid = protect($mid, 'uint');
	$cond = protect($cond, 'array');
	$res = [];
	$rid = 0;

	if(isset($cond['res']))
		$res = protect($cond['res'], 'array');
	if(isset($cond['rid']))
		$rid = protect($cond['rid'], 'uint');

	$req = $_sql2::table('res_todo')
		->select(['rtdo_id', 'rtdo_type', 'rtdo_nb'])
		->where('rtdo_mid', '=', $mid)
		->where('rtdo_nb', '>', 0);
	
	if($rid)
		$req->where('rtdo_id', '=', $rid);
	
	if($res)
	{
		$list = [];
		foreach($res as $type)
			array_push($list, protect($type, 'uint'));

		$req->where('rtdo_type', 'IN', $list);
	}
	$req->orderBy('rtdo_id', 'asc');
	return json_decode(json_encode($req->get()), true);
}
/* Verifie qu'on peut faire telle ou telle ressource */
function can_res($mid, $type, $nb, &$cache = [])
{
	$mid = protect($mid, 'uint');
	$type = protect($type, 'uint');
	$cache = protect($cache, 'array');
	$bad_src = $bad_res = $bad_btc = [];

	if(!get_conf('res', $type))
		return ['do_not_exist' => true];

	/* Bâtiments */
	$need_btc = get_conf('res', $type, 'need_btc');
	$cond_btc = [$need_btc];

	if(!isset($cache['btc'])) {
		$have_btc = get_nb_btc_done($mid, $cond_btc);
		$have_btc = index_array($have_btc, 'btc_type');
	} else
		$have_btc = $cache['btc'];

	/* Recherches */
	$cond_src = [$type];
	$need_src = get_conf('res', $type, 'need_src');
	$cond_src = $need_src;

	if(!isset($cache['src'])) {
		$have_src = get_src_done($mid, $cond_src);
		$have_src = index_array($have_src, 'src_type');
	} else
		$have_src = $cache['src'];
	
	/* Ressources */
	$prix_res = get_conf('res', $type, 'prix_res');
	$cond_res = array_keys($prix_res);

	if(!isset($cache['res'])) {
		$have_res = get_res_done($mid, $cond_res);
		$have_res = clean_array_res($have_res);
		$have_res = $have_res[0];
	} else
		$have_res = $cache['res'];

	/* Les recherches qu'il faut avoir */
	foreach($need_src as $src_type) {
		if(!isset($have_src[$src_type]))
			$bad_src['need_src'][] = $src_type;
	}

	/* Vérifications ressources */
	foreach($prix_res as $res_type => $nombre) {
		$diff =  $nombre * $nb - $have_res[$res_type];
		if($diff > 0)
			$bad_res[$res_type] =  $diff;
	}

	/* Verifications Bâtiments */
	if(!isset($have_btc[$need_btc]))
		$bad_btc = $cond_btc;

	return ['need_src' => $bad_src, 'need_btc' => $bad_btc, 'prix_res' => $bad_res];
}
/* Permet d'avoir un tableau plus utilisable lors d'un get_res_done
 * Le array qu'on lui file doit être directement celui qui sort de get_res_done ou équivalent
 */
function clean_array_res($array)
{
	$array = protect($array, 'array');
	$return = [];

	if($array)
		foreach($array as $line => $values)
			foreach($values as $key => $value)
				$return[$line][str_replace('res_type', '', $key)] = $value;

	return $return;
}
/* Récupère les ressources du joueur + mise en forme */
function get_res_done2($mid, $res = [], $race = 0, $exc = [])
{
	return clean_array_res(get_res_done($mid, $res, $race, $exc));
}
/* Quand on crée un membre */
function ini_res($mid)
{
	global $_sql2;
	$mid = protect($mid, 'uint');
	$_sql2::table('res')->insertGetId(['res_mid' => $mid]);
	return mod_res($mid, get_conf('race_cfg', 'debut', 'res'));
}
/* Quand on le vire */
function cls_res($mid)
{
	global $_sql2;

	// Additionne les deux requêtes pour avoir le nombre total de lignes supprimés
	return $_sql2::table('res')->where('res_mid', '=', $mid)->delete() +
		   $_sql2::table('res_todo')->where('rtdo_mid', '=', $mid)->delete();
}
