<?php
/*-------------------------*/
/*---- Lib Quest Config ---*/
/*-------------------------*/

function add_qst_cfg($mid, $titre, $descr, $qst_statut, $race, $qst_com, $req_quest, $req_pts, $req_pts_armee,  $req_btc,  $req_src,  $btc_id_1, $btc_nb_1, $btc_id_2, $btc_nb_2, $unt_id_1, $unt_nb_1, $unt_id_2, $unt_nb_2,  $res_id, $res_nb, $src_id, $rec_res_id1, $rec_res_val1, $rec_res_id2, $rec_res_val2, $rec_xp)
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
	
	$sql = "INSERT INTO ".$_sql->prebdd."qst_cfg (`qst_mid`, `qst_title`, `qst_descr`, `qst_statut`, `qst_race`, `qst_common`, `qst_req_qid`, `qst_req_pts`, `qst_req_pts_armee`, `qst_req_btc`, `qst_req_src`, `qst_btc_id1`, `qst_btc_nb1`, `qst_btc_id2`, `qst_btc_nb2`, `qst_unt_id1`, `qst_unt_nb1`, `qst_unt_id2`, `qst_unt_nb2`,`qst_res_id`, `qst_res_nb`, `qst_src_id`, `qst_rec_res1`, `qst_rec_val1`, `qst_rec_res2`, `qst_rec_val2`, `qst_rec_xp`) VALUES ($mid,'$titre','$descr','$qst_statut','$race','$qst_com','$req_quest','$req_pts','$req_pts_armee','$req_btc','$req_src','$btc_id_1','$btc_nb_1','$btc_id_2','$btc_nb_2','$unt_id_1','$unt_nb_1','$unt_id_2','$unt_nb_2','$res_id','$res_nb','$src_id','$rec_res_id1','$rec_res_val1','$rec_res_id2','$rec_res_val2','$rec_xp')";
	return $_sql->query($sql);
}
	
function get_qst_cfg($qid=0, $selrace)
{
	global $_sql;
    
	$qid = protect($qid, "uint");
	$selrace = protect($selrace, "string");

$sql = "SELECT 
            cfg.qst_id, 
            cfg.qst_mid, 
            cfg.qst_title, 
            cfg.qst_descr, 
            cfg.qst_statut, 
            cfg.qst_race, 
            cfg.qst_common, 
            cfg.qst_req_qid, 
            linked.qst_title AS req_qst_title, 
            cfg.qst_req_pts, 
            cfg.qst_req_pts_armee, 
            cfg.qst_req_btc, 
            cfg.qst_req_src, 
            cfg.qst_btc_id1, 
            cfg.qst_btc_nb1, 
            cfg.qst_btc_id2, 
            cfg.qst_btc_nb2, 
            cfg.qst_unt_id1, 
            cfg.qst_unt_nb1, 
            cfg.qst_unt_id2, 
            cfg.qst_unt_nb2, 
            cfg.qst_res_id, 
            cfg.qst_res_nb, 
            cfg.qst_src_id, 
            cfg.qst_rec_res1, 
            cfg.qst_rec_val1, 
            cfg.qst_rec_res2, 
            cfg.qst_rec_val2, 
            cfg.qst_rec_xp, 
            _DATE_FORMAT(cfg.qst_created) as qst_date_formated 
        FROM ".$_sql->prebdd."qst_cfg AS cfg 
        LEFT JOIN ".$_sql->prebdd."qst_cfg AS linked 
        ON linked.qst_id = cfg.qst_req_qid 
        WHERE "; 

    
    if($selrace == "com")   $sql.="cfg.qst_common = 1";
    elseif(($selrace > 0 && $selrace <= 7)) $sql.="cfg.qst_race = $selrace AND cfg.qst_common = 0";
    elseif($qid) $sql.="cfg.qst_id = $qid";    
    else $sql.="1";
    
	$sql.=" ORDER BY qst_id ASC";
	
	return $_sql->make_array($sql);
}
	
function edit_qst_cfg($qid, $mid, $titre, $descr, $qst_statut, $race, $qst_com, $req_quest, $req_pts, $req_pts_armee,  $req_btc,  $req_src,  $btc_id_1, $btc_nb_1, $btc_id_2, $btc_nb_2, $unt_id_1, $unt_nb_1, $unt_id_2, $unt_nb_2,  $res_id, $res_nb, $src_id, $rec_res_id1, $rec_res_val1, $rec_res_id2, $rec_res_val2, $rec_xp)
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
	
	$sql="UPDATE ".$_sql->prebdd."qst_cfg SET qst_title = '$titre', qst_descr = '$descr', qst_statut = '$qst_statut', qst_race = '$race', qst_common = '$qst_com', qst_req_qid = '$req_quest', qst_req_pts = '$req_pts', qst_req_pts_armee = '$req_pts_armee', qst_req_btc = '$req_btc', qst_req_src = '$req_src', qst_btc_id1 = '$btc_id_1', qst_btc_nb1 = '$btc_nb_1', qst_btc_id2 = '$btc_id_2', qst_btc_nb2 = '$btc_nb_2', qst_unt_id1 = '$unt_id_1', qst_unt_nb1 = '$unt_nb_1', qst_unt_id2 = '$unt_id_2', qst_unt_nb2 = '$unt_nb_2', qst_res_id = '$res_id', qst_res_nb = '$res_nb' , qst_src_id = '$src_id' , qst_rec_res1 = '$rec_res_id1' , qst_rec_val1 = '$rec_res_val1' , qst_rec_res2 = '$rec_res_id2' , qst_rec_val2 = '$rec_res_val2' , qst_rec_xp = '$rec_xp' WHERE qst_id = $qid" ;
	$_sql->query($sql);
	return $_sql->affected_rows();
}
	
function del_qst_cfg($qid)
{
	global $_sql;

	//$mid = protect($mid, "uint");
	$qid = protect($qid, "uint");
	
	$sql="DELETE FROM ".$_sql->prebdd."qst_cfg WHERE qst_id = $qid";
	/*if($qid)
		$sql.=" AND nte_qid = $qid";*/
	
	$_sql->query($sql);
	return $_sql->affected_rows();
}
	
function cls_qst_cfg($mid) {
	return del_qst_cfg($mid);
}


/*-------------------------*/
/*----  Lib Quest user  ---*/
/*-------------------------*/

function get_qst($mid, $qid=0)
{
	global $_sql;
	
	$mid = protect($mid, "uint");
    $qid = protect($qid, "uint");
    
    	$sql = "SELECT *";
	$sql .=" FROM ".$_sql->prebdd."qst";
	$sql .=" INNER JOIN ".$_sql->prebdd."qst_cfg ON qst_mbr_qid = qst_id";
	$sql .=" INNER JOIN ".$_sql->prebdd."mbr ON qst_mbr_mid = mbr_mid";
	$sql .=" WHERE qst_mbr_mid = $mid";
    
    if($qid)
    $sql.=" AND qst_mbr_qid = $qid ";
    
	return  $_sql->make_array($sql);
}

function qst_add_rec($mid, $res, $nb)
{
	global $_sql;
	
	$mid = protect($mid, "uint");
    $res = protect($res, "uint");
    $nb = protect($nb, "uint");
    
	$sql="UPDATE ".$_sql->prebdd."res SET res_type$res = res_type$res+$nb WHERE res_mid = $mid" ;
	$_sql->query($sql);
	return $_sql->affected_rows();
}

function qst_statut_update($mid, $qid, $statut)
{
	global $_sql;
	
	$mid = protect($mid, "uint");
    $qid = protect($qid, "uint");
    $statut = protect($statut, "uint");
    
	$sql="UPDATE ".$_sql->prebdd."qst SET qst_mbr_statut = $statut WHERE qst_mbr_mid = $mid AND qst_mbr_qid =$qid" ;
	$_sql->query($sql);
	return $_sql->affected_rows();
}

/*
function get_count()
{
	global $_sql;
	

	$sql="SELECT count(qst_id) as cnt_tp";
	$sql.=" FROM ".$_sql->prebdd."qst_cfg ";
	$sql.=" WHERE 1";
	//$sql.=" ORDER BY last_post DESC";

	return $_sql->index_array($sql);
}*/
?>