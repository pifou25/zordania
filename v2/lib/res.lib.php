<?php 

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
		$have_btc = Btc::getNbDone($mid, $cond_btc);
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
