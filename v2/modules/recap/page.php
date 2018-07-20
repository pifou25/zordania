<?php
//Verifications
if(!defined("_INDEX_")){ exit; }
if(can_d(DROIT_PLAY)!=true)
	$_tpl->set("need_to_be_loged",true); 
else
{
	require_once("lib/recap.lib.php");
$_tpl->set('module_tpl', 'modules/recap/recap.tpl');
	
	
//quelle liste afficher?	
$fid = request('fid', 'uint', 'get');
//type pour le tri
$type =  request('type', 'uint', 'get');
	
	get_recap($fid, $type);
	$recap_array = get_recap($fid, $type);
	$get_count = get_count($fid, $type);
	$count=$get_count[0]['cnt_tp'];
	
	if ($count == 0)
	{
		$_tpl->set('empty',true);
		$_tpl->set('fid_get','0');
	}
	else 
	{
		$_tpl->set('recap_array',$recap_array);
		$_tpl->set('fid_get', '8');
		$_tpl->set('empty',false);
	}
	
	
}
?>