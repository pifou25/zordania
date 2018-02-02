<?php
/*
	can_src(), ini_src() ne peuvent être utilisés que dans le site, et pour quelqu'un de connecté
	avec le bon $_user['race']
*/

// Rajouter la recherche dont la conf est $conf en prévision
function scl_src($mid, $type)
{
	global $_sql2;

	$mid = protect($mid, 'uint');
	$type = protect($type, 'uint');
	add_src($mid, $type);

	return $_sql2::table('src_todo')->insertGetId([
		'stdo_mid'  => $mid, 
		'stdo_type' => $type,
		'stdo_tours' => get_conf('src', $type, 'tours')
	]);
}
// Ajoute une recherche pour de vrai
function add_src($mid, $type)
{
	global $_sql2;
	return $_sql2::table('src')->insertGetId(['src_mid'  => $mid, 'src_type' => $type]);
}
// Verifie qu'on peut faire telle ou telle recherche
function can_src($mid, $type, &$cache = [])
{
	$mid = protect($mid, 'uint');
	$type = protect($type, 'uint');
	$cache = protect($cache, 'array');

	if(!get_conf('src', $type))
		return ['do_not_exist' => true];

	$bad_src = ['need_src' => [], 'need_no_src' => []];
	$bad_btc = $bad_res = [];
	$done = false;
	// Bâtiments
	$need_btc = get_conf('src', $type, 'need_btc');
	// Recherches
	$need_src = get_conf('src', $type, 'need_src');
	$need_no_src = get_conf('src', $type, 'need_no_src');
	
	if(!isset($cache['btc_done']))
		$have_btc = index_array(get_nb_btc_done($mid, $need_btc), 'btc_type'); // need_btc est cond_btc
	else
		$have_btc = $cache['btc_done'];

	if(!isset($cache['src']))
		$have_src = index_array(get_src_done($mid, array_merge($need_src, $need_no_src)), 'src_type');
	else
		$have_src = $cache['src'];

	if(!isset($cache['src_todo']))
		$todo_src = index_array(get_src_todo($mid, [$type]), 'src_type');
	else
		$todo_src = $cache['src_todo'];

	// Les recherches qu'on ne doit pas avoir
	foreach($need_no_src as $src_type)
		if(isset($have_src[$src_type]))
			$bad_src['need_no_src'][$src_type] = $src_type;

	// Les recherches qu'il faut avoir
	foreach($need_src as $src_type)
		if(!isset($have_src[$src_type]))
			$bad_src['need_src'][$src_type] = $src_type;

	// La recherche qu'on veut est-elle déjà en cours ?
	$todo = isset($todo_src[$type]);
	$done = (isset($todo_src[$type]) || isset($have_src[$type]));
	// Ressources
	$prix_res = get_conf("src", $type, "prix_res");
	$cond_res = array_keys($prix_res);

	if(!isset($cache['res'])) {
		$have_res = clean_array_res(get_res_done($mid, $cond_res));
		$have_res = $have_res[0];
	} else
		$have_res = $cache['res'];

	foreach($prix_res as $res_type => $nombre) {
		$diff =  $nombre - $have_res[$res_type];

		if($diff > 0)
			$bad_res[$res_type] =  $diff;
	}

	// Verifications Bâtiments
	foreach($need_btc as $btc_type)
		if(!isset($have_btc[$btc_type]))
			$bad_btc[] = $btc_type;

	return [
		'need_btc' => $bad_btc, 
		'need_src' => $bad_src['need_src'], 
		'need_no_src' => $bad_src['need_no_src'], 
		'todo' => $todo, 
		'done' => $done, 
		'prix_res' => $bad_res
	];
}
// Annule la recherche $type 
function cnl_src($mid, $type)
{
	global $_sql2;

	$mid = protect($mid, 'uint');
	$type = protect($type, 'uint');

	del_src($mid, $type);

	return $_sql2::table('src_todo')->where([['stdo_type', '=', $type], ['stdo_mid', '=', $mid]])->delete();
}
// Supprimer la recherche $type
function del_src($mid, $type = 0)
{
	global $_sql2;

	$mid = protect($mid, 'uint');
	$type = protect($type, 'uint');

	$req = $_sql2::table('src')->where('src_mid', '=', $mid);

	if($type)
		$req->where('src_type', '=', $type);

	return $req->delete();
}
// Récupere les recherches de $mid [ et de type $type ]
function get_src_done($mid, $src = [])
{
	global $_sql2;
	
	$mid = protect($mid, 'uint');
	$src = protect($src, 'array');
	$req = $_sql2::table('src')
		->select(['src_mid', 'src_type'])
		->where('src_mid', '=', $mid)
		->whereNotIn('src_type', function ($req) use ($mid, $src)
		{
			$req->select('stdo_type')
				->from('src_todo')
				->where('stdo_mid', '=', $mid);

			if($src)
				$req->whereIn('src_type', protect($src, ['uint']));
		});

	return json_decode(json_encode($req->get()), true);
}
function get_src_todo($mid, $src = [])
{
	global $_sql2;
	
	$mid = protect($mid, 'uint');
	$src = protect($src, 'array');
	
	$req = $_sql2::table('src_todo')
		->select(['stdo_mid', 'stdo_type', 'stdo_tours'])
		->where('stdo_mid', '=', $mid);
	
	if($src)
		$req->whereIn('stdo_type', protect($src, ['uint']));

	return json_decode(json_encode($req->orderBy('stdo_time', 'asc')->get()), true);
}
// Quand on crée un membre
function ini_src($mid)
{
	foreach(get_conf('race_cfg', 'debut', 'src') as $type)
		add_src($mid, $type);
}
// Quand on le vire
function cls_src($mid)
{
	global $_sql2;

	$mid = protect($mid, 'uint');
	$req = $_sql2::table('src_todo')->where('stdo_mid', '=', $mid);

	return $req->delete() + del_src($mid);
}
