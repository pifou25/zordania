<?php

$dep_src = array("btc");
$log_src = "Recherches";

function mbr_src(&$_user) {
	global $_histo;
	$mid = $_user['mbr_mid'];
	$race = $_user['mbr_race'];

	if(isset($_user["no_src_todo"]))
		return;

	$src_todo = SrcTodo::get($mid);

	if(!$src_todo)
		return;

	$need_btc = array();
	for($i = 1; $i <= Config::get($race, "race_cfg", "btc_nb"); ++$i)
		if(Config::get($race, "btc", $i, "prod_src"))
			$need_btc[] = $i;

	$speed = Btc::where('btc_mid', $mid)->whereIn('btc_type', $need_btc)->count();

	$sql="UPDATE ".DB::getTablePrefix()."src_todo SET stdo_tours = CASE ";
	foreach($src_todo as $src_info)  /* Toutes les recherches de ce type là */
	{
		$tours = $src_info['stdo_tours'];
		$type = $src_info['stdo_type'];
		if($tours - $speed > 0) {
			$sql.=" WHEN stdo_type = $type THEN  stdo_tours - $speed";
			$speed = 0;
		} else {
			$sql.=" WHEN stdo_type = $type THEN  0";
			$_histo->add($mid, $mid, HISTO_SRC_DONE,array("src_type" => $type));
			$speed -= $tours;
		}

		if($speed <= 0)
			break;
	}
	$sql .=" ELSE stdo_tours END WHERE stdo_mid = $mid";
	DB::update($sql);
}

function glob_src() {
    SrcTodo::where('stdo_tours', 0)->delete();
}
