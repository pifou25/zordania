<?php
//Gestions page récapitulatif bug et suggestions
// liste 
function get_recap($fid, $type)
{
	global $_sql;
	
	$fid = protect($fid, "uint");
	$type = protect($type, "uint");

	$sql="SELECT id, poster, subject, statut, forum_id, report_type";
	$sql.=" FROM ".$_sql->prebdd."frm_topics ";
	$sql.=" WHERE forum_id = $fid AND report_type = $type AND statut <> ".REPORT_STATUT_DUBL."";
	$sql.=" ORDER BY last_post DESC";

	return $_sql->index_array($sql);
}
function get_count($fid, $type)
{
	global $_sql;
	
	$fid = protect($fid, "uint");
	$type = protect($type, "uint");

	$sql="SELECT count(report_type) as cnt_tp";
	$sql.=" FROM ".$_sql->prebdd."frm_topics ";
	$sql.=" WHERE forum_id = $fid AND report_type = $type AND statut <> ".REPORT_STATUT_DUBL."";
	$sql.=" ORDER BY last_post DESC";

	return $_sql->index_array($sql);
}
?>