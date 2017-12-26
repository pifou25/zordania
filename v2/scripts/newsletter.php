<?php
/* args facultatifs */
$mid = (isset($argv[1]) && is_numeric($argv[1]) ? (int)$argv[1] : 0);

/* Config */
require_once("../conf/conf.inc.php");
require_once(SITE_DIR.'lib/divers.lib.php');

$_sql = new mysqliext(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_BASE);
$_sql->set_prebdd(MYSQL_PREBDD);

set_time_limit(0);
$sql = "SELECT mbr_pseudo,mbr_mail,mbr_mid FROM zrd_mbr" . (!empty($mid) ? " WHERE mbr_mid >= $mid" : '');
//$sql = "SELECT mbr_pseudo,mbr_mail, mbr_mid FROM zrd_mbr" . (!empty($mid) ? " LIMIT $mid, 1000" : '');
$mbr_array = $_sql->make_array($sql);

$_tpl = new Template();
$_tpl->set_dir(SITE_DIR .'templates');
$_tpl->set_tmp_dir(SITE_DIR .'tmp');
$_tpl->set_lang('fr_FR');
$_tpl->set("cfg_url",SITE_URL);

$i=0;
$ok=0;
foreach($mbr_array as $values) {
	$_tpl->set("mbr_pseudo", $values['mbr_pseudo']);
	$_tpl->set("mbr_mail", $values['mbr_mail']);

	$subject = 'Zordania : une nouvelle aventure !';
	$message = $_tpl->get("modules/inscr/mails/text_opengame.tpl");
	$to = $values['mbr_mail'];
	
	//echo $message;
	if (mailto('pifou@zordania.com', $to, $subject, $message, true))
		$ok++;
	else
		echo "error on " . $values['mbr_mid'] . " - " . $values['mbr_pseudo'] . "\r\n";

	// pause 10 secondes
	sleep(10);
	$i++;
	if(!($i%20)) echo date('c') . " = $i try and $ok sent (" . $values['mbr_mid'] . ")\r\n";
}
echo "job done: $i members try and $ok sent\r\n";
?>