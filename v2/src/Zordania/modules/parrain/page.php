<?php

if(!defined("_INDEX_")){ exit; }
if(!$_ses->canDo(DROIT_PLAY))
	$_tpl->set("need_to_be_loged",true); 
else
{
	$_tpl->set('module_tpl','modules/parrain/parrain.tpl');

	$cond = array();
	$cond['parrain'] = $_user['mid'];
	$cond['list'] = true;
	$filleuls = Mbr::get($cond);
	$filleuls = Mbr::canAtq($filleuls, $_user['pts_arm'], $_user['mid'], $_user['groupe'], $_user['alaid']);
	$_tpl->set("filleuls", $filleuls);
}

