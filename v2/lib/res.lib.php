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
		$have_res = Res::get($mid, $cond_res);
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

/* Quand on crée un membre */
function ini_res($mid)
{
	global $_sql2;
	$mid = protect($mid, 'uint');
	$_sql2::table('res')->insertGetId(['res_mid' => $mid]);
	return Res::mod($mid, get_conf('race_cfg', 'debut', 'res'));
}
/* Quand on le vire */
function cls_res($mid)
{
	global $_sql2;

	// Additionne les deux requêtes pour avoir le nombre total de lignes supprimés
	return $_sql2::table('res')->where('res_mid', '=', $mid)->delete() +
		   $_sql2::table('res_todo')->where('rtdo_mid', '=', $mid)->delete();
}
