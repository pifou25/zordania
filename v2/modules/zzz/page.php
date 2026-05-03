<?php
if(!defined("_INDEX_") || !can_d(DROIT_PLAY)) exit;
if(!can_d(DROIT_PLAY))
	$_tpl->set("need_to_be_loged",true); 
else {

require_once("lib/member.lib.php");
require_once("lib/war.lib.php");
require_once("lib/unt.lib.php");

$_tpl->set("module_tpl","modules/zzz/zzz.tpl");

$_tpl->set("ZZZ_MIN",ZZZ_MIN);

$passmd5 = $_ses->crypt($_user['login'],request("mbr_pass", "string", "post"));
    
//empecher de se mettre en veille si on vient d'attaquer    
     //quelle est la dernière attaque + infos
    $cond['mid'] = $_user['mid'];
    $atq_array = get_atq_gen( $cond);
    $lid = $atq_array[0]['atq_lid1']; //id de la légion
    $atq_date_time = strtotime($atq_array[0]['atq_date']); //date de l'atq
    
    $cond = array();
	$cond['leg'] = array($lid);
	$cond['mid'] = $_user['mid'];
    $leg_array = get_leg_gen($cond);
    
    $leg_etat = $leg_array[0]['leg_etat']; //état
    $leg_stop_time = strtotime($leg_array[0]['leg_stop']); //h d'arrivée
        
    //calcul
    $time_can_sleep = $leg_stop_time + 3600 * (ZZZ_ATQ_DELAY / (60/ZORD_SPEED));    
    $cantSleep = ( $leg_etat == LEG_ETAT_RET || $time_can_sleep < $atq_date_time);
    

if($_act == "ronflz" && $_user['etat'] == MBR_ETAT_OK && $_user['pass'] == $passmd5) {
	edit_mbr($_user['mid'], array('etat' => 3,'ldate' => true));
	$_tpl->set('zzz_act','ronflz');
} elseif($_act == "dring" && $_user['etat'] == MBR_ETAT_ZZZ) {
	$_tpl->set('zzz_act','dring');

	$mbr_array = get_mbr_by_mid_full($_user['mid']);
	$ldate  = $mbr_array[0]['mbr_ldate'];

	$ldate = explode(' ',$ldate);
	$ldate[0] = explode('-',$ldate[0]);
	$ldate[1] = explode(':',$ldate[2]);

	$ltimestamp = mktime($ldate[1][0], $ldate[1][1], $ldate[1][2], $ldate[0][1], $ldate[0][0], $ldate[0][2]);
	$timestamp =time();

	if(($timestamp - $ltimestamp) > ZZZ_MIN * 24 * 60 * 60) {
		edit_mbr($_user['mid'], array('etat' => 1,'ldate' => true));
		$_tpl->set('zzz_ok',true);
	} else
		$_tpl->set('zzz_ok',false);

} else if($_user['etat'] == MBR_ETAT_ZZZ) {
	$_tpl->set('zzz_act','stats');

	$mbr_array = get_mbr_by_mid_full($_user['mid']);
	$ldate  = $mbr_array[0]['mbr_ldate'];

	$ldate_aff=$ldate;
	$ldate = explode(' ',$ldate);
	$ldate[0] = explode('-',$ldate[0]);
	$ldate[1] = explode(':',$ldate[2]);

	$ltimestamp = mktime($ldate[1][0], $ldate[1][1], $ldate[1][2], $ldate[0][1], $ldate[0][0], $ldate[0][2]);
	$timestamp = time();

	$_tpl->set('zzz_date',$ldate_aff);

	if(($timestamp - $ltimestamp) > ZZZ_MIN * 24 * 60 * 60)
		$_tpl->set('zzz_ok',true);
	else
		$_tpl->set('zzz_ok',false);
} else if($cantSleep) {
    
    $_tpl->set('zzz_date_canSleep', date('d-m-Y \à H:i', $time_can_sleep));    
	$_tpl->set('zzz_act','cant_sleep');
    
} else
	$_tpl->set('zzz_act','rien');

}
?>