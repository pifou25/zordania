<?php
if(!defined("_INDEX_")) exit;
if(!$_ses->canDo(DROIT_PLAY)) 
	$_tpl->set("need_to_be_loged",true); 
else {
	$_tpl->set('module_tpl','modules/histo/histo.tpl');

	if($_act == "all") {
		$limite1 = 0;
	} else {
		$limite1 = LIMIT_PAGE;
	}
	
	$_tpl->set("histo_array",Hst::get($_user['mid'],$limite1));
	$_tpl->set('histo_key',calc_key($_file, $_user['login']));
/*
foreach ($_tpl->var->histo_array as $key => $val) 
	if ($val['histo_type'] == Hst::LEG_ATQ_LEG or $val['histo_type'] == Hst::LEG_ATQ_VLG) print_r($val);
*/
$_debugvars['histo'] = $_tpl->var->histo_array ;
}
