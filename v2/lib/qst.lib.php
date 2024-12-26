<?php

function add_qst($mid, $titre, $descr, $qst_statut, $race, $req_quest, $param_1, $objectif_1, $obj_val_1, $recompense_1, $rec_val_1, $recompense_2, $rec_val_2, $rec_xp)
{
	global $_sql;
	
	$mid = protect($mid, "uint");
	$titre = protect($titre, "string");
	$descr = protect($descr, "bbcode");
    $qst_statut = protect($qst_statut, "uint");
    $race = protect($race, "uint");
    $req_quest = protect($req_quest, "uint");
	$param_1 = protect($param_1, "string");
	$objectif_1 = protect($objectif_1, "string");
    $obj_val_1 = protect($obj_val_1, "uint");
	$recompense_1 = protect($recompense_1, "string");
    $rec_val_1 = protect($rec_val_1, "uint");
	$recompense_2 = protect($recompense_2, "string");
    $rec_val_2 = protect($rec_val_2, "uint");
    $rec_xp = protect($rec_xp, "uint");
	
	$sql = "INSERT INTO ".$_sql->prebdd."qst_details (`qst_mid`, `qst_title`, `qst_descr`, `qst_statut`, `qst_race`, `qst_req_qid`, `qst_param_1`, `qst_obj_1`, `qst_obj_val1`, `qst_recomp_cat1`, `qst_recomp_val1`, `qst_recomp_cat2`, `qst_recomp_val2`, `qst_recomp_val3`) VALUES ($mid,'$titre','$descr','$qst_statut','$race','$req_quest','$param_1','$objectif_1','$obj_val_1','$recompense_1','$rec_val_1','$recompense_2','$rec_val_2','$rec_xp')";
	return $_sql->query($sql);
}
	
function get_qst($qid=0)
{
	global $_sql;
    
	$qid = protect($qid, "uint");

	$sql="SELECT qst_id, qst_mid, qst_title, qst_descr, qst_statut, qst_race, qst_req_qid, qst_param_1, qst_obj_1, qst_obj_val1, qst_recomp_cat1, qst_recomp_val1, qst_recomp_cat2, qst_recomp_val2, qst_recomp_val3, _DATE_FORMAT(qst_created) as qst_date_formated ";
	$sql.=" FROM ".$_sql->prebdd."qst_details WHERE ";
    
    if($qid) $sql.="qst_id = $qid";
    else $sql.="1";
	$sql.=" ORDER BY qst_id ASC";
	
	return $_sql->make_array($sql);
}
	
function edit_qst($qid, $mid, $titre, $descr, $qst_statut, $race, $req_quest, $param_1, $objectif_1, $obj_val_1, $recompense_1, $rec_val_1, $recompense_2, $rec_val_2, $rec_xp)
{
	global $_sql;

	$qid = protect($qid, "uint");
	$mid = protect($mid, "uint");
	$titre = protect($titre, "string");
	$descr = protect($descr, "bbcode");
	$qst_statut = protect($qst_statut, "uint");
    $race = protect($race, "uint");
    $req_quest = protect($req_quest, "uint");
	$param_1 = protect($param_1, "string");
	$objectif_1 = protect($objectif_1, "string");
    $obj_val_1 = protect($obj_val_1, "uint");
	$recompense_1 = protect($recompense_1, "string");
    $rec_val_1 = protect($rec_val_1, "uint");
	$recompense_2 = protect($recompense_2, "string");
    $rec_val_2 = protect($rec_val_2, "uint");
    $rec_xp = protect($rec_xp, "uint");
	
	$sql="UPDATE ".$_sql->prebdd."qst_details SET qst_title = '$titre', qst_descr = '$descr', qst_statut = '$qst_statut', qst_race = '$race', qst_req_qid = '$req_quest', qst_param_1 = '$param_1', qst_obj_1 = '$objectif_1', qst_obj_val1 = '$obj_val_1', qst_recomp_cat1 = '$recompense_1', qst_recomp_val1 = '$rec_val_1', qst_recomp_cat2 = '$recompense_2', qst_recomp_val2 = '$rec_val_2', qst_recomp_val3 = '$rec_xp' WHERE qst_id = $qid" ;
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