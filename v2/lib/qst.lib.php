<?php

function add_qst($mid, $titre, $descr, $qst_statut, $race, $qst_com, $req_quest, $req_pts, $req_pts_armee,  $req_btc,  $req_src,  $btc_id_1, $btc_nb_1, $btc_id_2, $btc_nb_2, $unt_id_1, $unt_nb_1, $unt_id_2, $unt_nb_2,  $res_id, $res_nb, $src_id, $rec_res_id1, $rec_res_val1, $rec_res_id2, $rec_res_val2, $rec_xp)
{
	global $_sql;
	
	$mid = protect($mid, "uint");
	$titre = protect($titre, "string");
	$descr = protect($descr, "bbcode");
    $qst_statut = protect($qst_statut, "uint");
    $race = protect($race, "uint");
    $qst_com = protect($qst_com, "uint");
    $req_quest = protect($req_quest, "uint");
    $req_pts = protect($req_pts, "uint");
    $req_pts_armee = protect($req_pts_armee, "uint");
    $req_btc = protect($req_btc, "uint");
    $req_src = protect($req_src, "uint");
    
    $btc_id_1 = protect($btc_id_1, "uint");
    $btc_nb_1 = protect($btc_nb_1, "uint");
    $btc_id_2 = protect($btc_id_2, "uint");
    $btc_nb_2 = protect($btc_nb_2, "uint");
    $unt_id_1 = protect($unt_id_1, "uint");
    $unt_nb_1 = protect($unt_nb_1, "uint");
    $unt_id_2 = protect($unt_id_2, "uint");
    $unt_nb_2 = protect($unt_nb_2, "uint");
    $res_id = protect($res_id, "uint");
    $res_nb = protect($res_nb, "uint");
    $src_id = protect($src_id, "uint");
    
    $rec_res_id1 = protect($rec_res_id1, "uint");
    $rec_res_val1 = protect($rec_res_val1, "uint");
    $rec_res_id2 = protect($rec_res_id2, "uint");
    $rec_res_val2 = protect($rec_res_val2, "uint");
    $rec_xp = protect($req_pts_armee, "uint");
	
	$sql = "INSERT INTO ".$_sql->prebdd."qst_details (`qst_mid`, `qst_title`, `qst_descr`, `qst_statut`, `qst_race`, `qst_common`, `qst_req_qid`, `qst_req_pts`, `qst_req_pts_armee`, `qst_req_btc`, `qst_req_src`, `qst_btc_id1`, `qst_btc_nb1`, `qst_btc_id2`, `qst_btc_nb2`, `qst_unt_id1`, `qst_unt_nb1`, `qst_unt_id2`, `qst_unt_nb2`,`qst_res_id`, `qst_res_nb`, `qst_src_id`, `qst_rec_res1`, `qst_rec_val1`, `qst_rec_res2`, `qst_rec_val2`, `qst_rec_xp`) VALUES ($mid,'$titre','$descr','$qst_statut','$race','$qst_com','$req_quest','$req_pts','$req_pts_armee','$req_btc','$req_src','$btc_id_1','$btc_nb_1','$btc_id_2','$btc_nb_2','$unt_id_1','$unt_nb_1','$unt_id_2','$unt_nb_2','$res_id','$res_nb','$src_id','$rec_res_id1','$rec_res_val1','$rec_res_id2','$rec_res_val2','$rec_xp')";
	return $_sql->query($sql);
}
	
function get_qst($qid=0)
{
	global $_sql;
    
	$qid = protect($qid, "uint");

	$sql="SELECT qst_id, qst_mid, qst_title, qst_descr, qst_statut, qst_race, qst_common, qst_req_qid, qst_req_pts, qst_req_pts_armee, qst_req_btc, qst_req_src, qst_btc_id1, qst_btc_nb1, qst_btc_id2, qst_btc_nb2, qst_unt_id1, qst_unt_nb1, qst_unt_id2, qst_unt_nb2, qst_res_id, qst_res_nb, qst_src_id, qst_rec_res1, qst_rec_val1, qst_rec_res2, qst_rec_val2, qst_rec_xp, _DATE_FORMAT(qst_created) as qst_date_formated ";
	$sql.=" FROM ".$_sql->prebdd."qst_details WHERE ";
    
    if($qid) $sql.="qst_id = $qid";
    else $sql.="1";
	$sql.=" ORDER BY qst_id ASC";
	
	return $_sql->make_array($sql);
}
	
function edit_qst($qid, $mid, $titre, $descr, $qst_statut, $race, $qst_com, $req_quest, $req_pts, $req_pts_armee,  $req_btc,  $req_src,  $btc_id_1, $btc_nb_1, $btc_id_2, $btc_nb_2, $unt_id_1, $unt_nb_1, $unt_id_2, $unt_nb_2,  $res_id, $res_nb, $src_id, $rec_res_id1, $rec_res_val1, $rec_res_id2, $rec_res_val2, $rec_xp)
{
	global $_sql;

	$mid = protect($mid, "uint");
	$titre = protect($titre, "string");
	$descr = protect($descr, "bbcode");
    $qst_statut = protect($qst_statut, "uint");
    $race = protect($race, "uint");
    $qst_com = protect($qst_com, "uint");
    $req_quest = protect($req_quest, "uint");
    $req_pts = protect($req_pts, "uint");
    $req_pts_armee = protect($req_pts_armee, "uint");
    $req_btc = protect($req_btc, "uint");
    $req_src = protect($req_src, "uint");
    
    $btc_id_1 = protect($btc_id_1, "uint");
    $btc_nb_1 = protect($btc_nb_1, "uint");
    $btc_id_2 = protect($btc_id_2, "uint");
    $btc_nb_2 = protect($btc_nb_2, "uint");
    $unt_id_1 = protect($unt_id_1, "uint");
    $unt_nb_1 = protect($unt_nb_1, "uint");
    $unt_id_2 = protect($unt_id_2, "uint");
    $unt_nb_2 = protect($unt_nb_2, "uint");
    $res_id = protect($res_id, "uint");
    $res_nb = protect($res_nb, "uint");
    $src_id = protect($src_id, "uint");
    
    $rec_res_id1 = protect($rec_res_id1, "uint");
    $rec_res_val1 = protect($rec_res_val1, "uint");
    $rec_res_id2 = protect($rec_res_id2, "uint");
    $rec_res_val2 = protect($rec_res_val2, "uint");
    $rec_xp = protect($req_pts_armee, "uint");
	
	$sql="UPDATE ".$_sql->prebdd."qst_details SET qst_title = '$titre', qst_descr = '$descr', qst_statut = '$qst_statut', qst_race = '$race', qst_common = '$qst_com', qst_req_qid = '$req_quest', qst_req_pts = '$req_pts', qst_req_pts_armee = '$req_pts_armee', qst_req_btc = '$req_btc', qst_req_src = '$req_src', qst_btc_id1 = '$btc_id_1', qst_btc_nb1 = '$btc_nb_1', qst_btc_id2 = '$btc_id_2', qst_btc_nb2 = '$btc_nb_2', qst_unt_id1 = '$unt_id_1', qst_unt_nb1 = '$unt_nb_1', qst_unt_id2 = '$unt_id_2', qst_unt_nb2 = '$unt_nb_2', qst_res_id = '$res_id', qst_res_nb = '$res_nb' , qst_src_id = '$src_id' , qst_rec_res1 = '$rec_res_id1' , qst_rec_val1 = '$rec_res_val1' , qst_rec_res2 = '$rec_res_id2' , qst_rec_val2 = '$rec_res_val2' , qst_rec_xp = '$rec_xp' WHERE qst_id = $qid" ;
	$_sql->query($sql);
	return $_sql->affected_rows();
}
	
function del_qst($qid)
{
	global $_sql;

	//$mid = protect($mid, "uint");
	$qid = protect($qid, "uint");
	
	$sql="DELETE FROM ".$_sql->prebdd."qst_details WHERE qst_id = $qid";
	/*if($qid)
		$sql.=" AND nte_qid = $qid";*/
	
	$_sql->query($sql);
	return $_sql->affected_rows();
}
	
function cls_qst($mid) {
	return del_qst($mid);
}

/*

function get_qst()
{
	global $_sql;
	
	//$fid = protect($fid, "uint");
	//$type = protect($type, "uint");

	$sql="SELECT *";
	$sql.=" FROM ".$_sql->prebdd."qst_details ";
	$sql.=" WHERE 1";
	//$sql.=" ORDER BY last_post DESC";

	return $_sql->index_array($sql);
}

function get_count()
{
	global $_sql;
	

	$sql="SELECT count(qst_id) as cnt_tp";
	$sql.=" FROM ".$_sql->prebdd."qst_details ";
	$sql.=" WHERE 1";
	//$sql.=" ORDER BY last_post DESC";

	return $_sql->index_array($sql);
}*/
?>