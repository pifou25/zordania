<?php
if(!defined("_INDEX_")){ exit; }

if(!$_ses->canDo(DROIT_PLAY) && $_act != "rp")
	$_tpl->set("need_to_be_loged",true); /* Les visiteurs ne peuvent voir que la partie rp */
else {

$_tpl->set("module_tpl","modules/carte/carte.tpl");

$type = request("map_type", "string", "cookie", "lite");
if(!$_act)
	$_act = $type;

if($_act != $type && ($_act == "ajax" || $_act == "lite") )
	setcookie('map_type',$_act, time() + 60 * 60 * 24 * 7 * 30);

if($_act == "lite" || $_act == "ajax") {
	if(empty($_GET['map_x']))
		$map_x = -1;
	else
		$map_x = request("map_x", "uint", "get", -1);
	if(empty($_GET['map_y']))
		$map_y = -1;
	else
		$map_y = request("map_y", "uint", "get", -1);
	$map_cid = request("map_cid", "uint", "get", -1);
	$map_pseudo = request("map_pseudo", "string", "get");

	$diff = ($_display == "module") ? 30 : 20;

	if($map_cid != -1) {
		$coord = Map::getGen($map_cid, ['x'=>$_user['map_x'], 'y'=>$_user['map_y']]);
		if($coord) {
			$map_x = $coord['map_x'] - $diff / 2;
			$map_y = $coord['map_y'] - $diff / 2;
		}
	} else if($map_pseudo) {
		$cond = ['pseudo' => $map_pseudo, 'limit1' => 1, 'list' => true];
		$mbr_array = Mbr::get($cond);
		if($mbr_array && !($mbr_array[0]['mbr_race'] == 6 || $mbr_array[0]['mbr_gid'] == GRP_PNJ)) {
			$map_x = $mbr_array[0]['map_x'] - $diff / 2;
			$map_y = $mbr_array[0]['map_y'] - $diff / 2;
		}
	}

	if($map_x == -1 || $map_y == -1) {
		$map_x = $_user['map_x'] - $diff / 2;
		$map_y = $_user['map_y'] - $diff / 2;
	}

	if($map_x < 0)
		$map_x = 0;
	if(($map_x+$diff) > MAP_W)
		$map_x = MAP_W - $diff;
	if($map_y < 0)
		$map_y = 0;
	if(($map_y+$diff) > MAP_H)
		$map_y = MAP_H - $diff;

	$_tpl->set('map_x',$map_x);
	$_tpl->set('map_y',$map_y);
	$_tpl->set('map_cid', request("view", "uint", "get"));
	$_tpl->set('map_usr_x', $_user['map_x'] - $diff / 2);
	$_tpl->set('map_usr_y', $_user['map_y'] - $diff / 2);
	$_tpl->set('leg_usr', Leg::getMap($_user['mid']));

}

if($_act == "view") {
	if($_display == "ajax")
		$_tpl->set("module_tpl","modules/carte/carte_view.tpl");

	$_tpl->set('map_type','view');

	/* mes pactes */
	$dpl_atq = new diplo(array('aid' => $_user['alaid']));
	$dpl_atq_arr = $dpl_atq->actuels(); // les pactes actifs en tableau

	$map_cid = request("map_cid", "uint", "get");
	if(!$map_cid){ // chercher map_cid
		$map_x = request("map_x", "uint", "get", -1);
		$map_y = request("map_y", "uint", "get", -1);
		if($map_x != -1 and $map_y != -1)
			$map_cid = Map::getCid($map_x,$map_y);
	}

	$leg_array = Leg::canAtq(Map::getLegGen([$map_cid]), $_user['pts_arm'], $_user['mid'], $_user["groupe"], $_user['alaid'], $dpl_atq_arr);
	$_tpl->set("leg_array", $leg_array);

	$map_array = Map::getGen($map_cid, ['x'=>$_user['map_x'], 'y'=>$_user['map_y']]);

	if (isset($map_array['mbr_mid']) && $map_array['mbr_mid']) {
		$mbr = Mbr::getFull($map_array['mbr_mid']);
		$mbr = Mbr::canAtq($mbr,$_user['pts_arm'], $_user['mid'], $_user['groupe'], $_user['alaid'], $dpl_atq_arr); 
		$mbr = $mbr[0];
		$map_array['can_atq'] = $mbr['can_atq'];
		$map_array['can_pro'] = $mbr['can_def'];
	}
	$_tpl->set('map_array', $map_array);
} else if($_act == "rp") {
	$_tpl->set('map_sub', $_sub);
	$_tpl->set('map_type','rp');
} else if($_act == "ajax") {
	$_tpl->set('map_type','ajax');
} else if($_act == "lite") {
	$_tpl->set('map_type','lite');

	$_tpl->set("orig_x", $map_x);
	$_tpl->set("orig_y", $map_y);
	
	// 1 square = 50x50px
	// edit skin/imports/carte.css #carte_big and #carte_lite
	// for the correct height and width according to the number of squares:
	if($_display == "module") {
		// 30x30
		$max_x2 = $map_x + 29;
		$max_y2 = $map_y + 29;
	} else {
		// 20x20
		$max_x2 = $map_x + 19;
		$max_y2 = $map_y + 19;
	}

	$map_array = Map::get($_user["mid"], $map_x,  $map_y, $max_x2, $max_y2);
	$_tpl->set('map_array',$map_array);
}
}