<?php
echo php_sapi_name()."\n";
$mid = (isset($argv[1]) && is_numeric($argv[1]) ? (int)$argv[1] : 0);
$limit = (isset($argv[2]) && is_numeric($argv[2]) ? (int)$argv[2] : 0);

require_once("../conf/conf.inc.php");
require_once(SITE_DIR . "lib/divers.lib.php");
require_once(SITE_DIR . "lib/mysql.class.php");
require_once(SITE_DIR . "lib/Template.class.php");
require_once(SITE_DIR . "lib/vld.lib.php");
require_once(SITE_DIR . "lib/member.lib.php");

/* BDD */
$_sql = new mysqliext(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_BASE);
$_sql->set_prebdd(MYSQL_PREBDD);
$_sql->set_debug(SITE_DEBUG);

/* template */
$_tpl = new Template();
$_tpl->set_dir(SITE_DIR .'templates');
$_tpl->set_tmp_dir(SITE_DIR .'tmp');
$_tpl->set_lang('fr_FR');
$_tpl->set("cfg_url",SITE_URL);

/* SELECT TOUS LES COMPTES! 
$sql = 'SELECT mbr_mid, mbr_login, mbr_mail, mbr_pseudo, mbr_pass, mbr_etat, mbr_decal, mbr_ldate, mbr_lmodif_date, mbr_inscr_date ';
$sql.= ' FROM zrd_mbr WHERE ';
if($mid) $sql .= "mbr_mid > $mid AND ";
$sql.= ' 1'; //ATTENTION CA ENVOIE A TOUT LE MONDE!!
$sql.= ' ORDER BY zrd_mbr.mbr_lmodif_date ASC';*/

/* sélection des comptes validés en veille sauf exilés et visiteur vieux de - de 30 jours pour savoir pourquoi ils se sont pas revenus*/
$sql = 'SELECT mbr_mid, mbr_login, mbr_pseudo, mbr_mail, mbr_pass, mbr_etat, mbr_decal, mbr_ldate, mbr_lmodif_date, mbr_inscr_date ';
$sql.= ' FROM '.$_sql->prebdd.'mbr WHERE ';
if($mid) $sql .= "mbr_mid = $mid ";
else $sql.= '  mbr_etat ='.MBR_ETAT_OK.''; // AND mbr_gid NOT IN ('.GRP_VISITEUR.','.GRP_EXILE.','.GRP_EXILE_TMP.')AND datediff(NOW(), `mbr_ldate`) < 30
$sql.= ' ORDER BY mbr_ldate ASC';

//echo $sql;

$mbr_array = $_sql->make_array($sql);
$nb=0;
foreach($mbr_array as $mbr){
	/* supprime toute clé de validation existante 
	cls_vld($mbr['mbr_mid']);

	/* nouvelle clé de validation 
	$key = genstring(GEN_LENGHT);
	new_vld($key, $mbr['mbr_mid'], 'rest'); // restauration
	/* les autres valeurs sont new res del et edit */

	/* envoi du mail de relance */
	// On filtre les serveurs qui rencontrent des bogues.
		if (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $mbr['mbr_mail'])) 
			{
				$passage_ligne = "\r\n";
			}
		else
			{
				$passage_ligne = "\n";
			}
	//destinataire
		$destinataire = $mbr['mbr_mail'];
	// Le lien d'activation est composé du login(log) et de la clé(cle)
		//Message format txt
		$message_txt = 
			'
			';
		//message format html
		$message_html = 
			'
			<html>
			<head>
			</head>
			<body>
				<p>Bonjour '.urlencode($mbr['mbr_pseudo']).',</p>
				<p></p>
				<p>Grydur veut que j&apos;envoie des mails pour que vous ne nous oubliez pas, donc voilà!  </p>
				<p>Bon dimanche :D</p>
				<p></p>
				<br>
				Zordialement,<br>
				Cet email a été envoyé à partir de https://zordania.com <br>
				Suivez-nous aussi, sur :<br>
				○ Discord : https://discord.gg/Wmwf829<br>
				○ Facebook : https://www.facebook.com/zordania2015/<br>
				○ Twitter : https://twitter.com/Zordania<br>
				
				<p>---------------</p>
				<A HREF="http://www.monsite.com/desabonnement.php?cle=XXXXXX">Me désabonner</A> (Ne marche pas du tout, tu as cru pouvoir nous éviter :D )
				<p>Si ce mail ne vous est pas concerné, ignorez-le.</p>
			</body>
			</html>
			';
	//=====Création de la boundary
		$boundary = "-----=".md5(rand());
	//sujet
		$sujet =  " Hello" ;
	//en-tête
		$header = "From: \"Zordania\"<webmaster@zordania.com>".$passage_ligne ;
		$header.= "MIME-Version: 1.0".$passage_ligne;
		$header.= "Content-Type: multipart/alternative;".$passage_ligne." boundary=\"$boundary\"".$passage_ligne;
	//=====Création du message.
		$message = $passage_ligne."--".$boundary.$passage_ligne;
		//=====Ajout du message au format texte.
		$message.= "Content-Type: text/plain; charset=\"utf-8\"".$passage_ligne;
		$message.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
		$message.= $passage_ligne.$message_txt.$passage_ligne;
		//==========
		$message.= $passage_ligne."--".$boundary.$passage_ligne;
		//=====Ajout du message au format HTML
		$message.= "Content-Type: text/html; charset=\"ISO-8859-1\"".$passage_ligne;
		$message.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
		$message.= $passage_ligne.$message_html.$passage_ligne;
		//==========
		$message.= $passage_ligne."--".$boundary."--".$passage_ligne;
		$message.= $passage_ligne."--".$boundary."--".$passage_ligne;	
	// Envoi du mail
		mail($destinataire, $sujet, $message, $header) ; 
if(	mail($destinataire, $sujet, $message, $header)) {
		// debug !
		echo $mbr['mbr_ldate'].' - mail à '.$mbr['mbr_mail']." : $sujet\n";
		if ($mid) echo "$txt\n";

		//$sql = 'UPDATE '.$_sql->prebdd.'mbr SET mbr_ldate = NOW() - INTERVAL 30 DAY WHERE mbr_mid = '.$mbr['mbr_mid'];
		//$_sql->query($sql);
	} else
		echo $mbr['mbr_ldate'].' - ECHEC à '.$mbr['mbr_mail']."\n";
	$nb++;
	if($limit && $limit<=$nb) break;// limiter le nombre de mails à envoyer

}




		

echo "fin traitement relance : $nb mails envoyés.\n";

?>
