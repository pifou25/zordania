<?php
//Verif
if(!defined("_INDEX_")){ exit; }
		
$_tpl->set("module_tpl","modules/news/news.tpl");
$_tpl->set('is_modo',$_ses->canDo(DROIT_PUNBB_MOD));

// news vue, check
$_ses->set('news', 0);

// Regarde toutes les news + pagination
// tout autre lien renvoie sur le forum
$frm = Frm::get(0, ZORD_NEWS_FID);
if (!empty($frm))
{
	$_tpl->set('frm',$frm[0]);
        $topics = new Paginator( FrmTopic::get(
                ['fid' => ZORD_NEWS_FID, 'select' => 'first_pid', 'order' => $frm[0]['sort_by']]));
        $_tpl->set('nwss', $topics); // only list of first posts ID

	foreach($topics->get as $topic)
		$pids[] = $topic['first_pid'];

	$posts = FrmPost::get(['select'=>'mbr', 'pid_list'=>$pids], 'pid')->get()->keyBy('pid');
	$_tpl->set('posts_array',$posts); // all posts selecteds
}
