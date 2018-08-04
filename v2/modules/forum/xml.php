<?php
if(!defined("_INDEX_")){ exit; }

include('lib/forum.lib.php');

$_tpl->set("module_tpl","modules/forum/forum.tpl");
$pid = request('pid','uint','get');
if(!$pid)
	exit;

$_module_tpl = "modules/forum/forum_xml.tpl";
$post = FrmPost::get(array('pid'=>$pid));
if(!empty($post)){
	if(FrmPerm::can($post[0]['forum_id'], $_user['groupe'], 'read'))
		$_tpl->set('post', $post[0]);
	else
		$_tpl->set('noperm', true);
}else
		$_tpl->set('noperm', true);

