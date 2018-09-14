<pre><?php
/* 
  correction de la place ET de la légion batiment
*/

require_once("../conf/conf.inc.php");

require_once(SITE_DIR . "lib/mysql.class.php");
require_once(SITE_DIR . 'lib/divers.lib.php');
require_once(SITE_DIR . 'lib/unt.lib.php');
require_once(SITE_DIR . 'lib/res.lib.php');
require_once(SITE_DIR . 'lib/member.lib.php');

$_sql = new mysql(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_BASE);
$_sql->set_prebdd(MYSQL_PREBDD);
$_sql->set_debug(SITE_DEBUG);

$exec = request('exec', 'uint', 'get');

function del_btc_type($mid, $race, $type, $nb, &$arr_unt = array(), &$arr_res = false) {
/* détruire $nb bat $type du membre $mid et rembourser dans $res */
	global $_sql;
	$arr_btc = Btc::getDone( $mid, array($type));

	$del = 0;
	$arr_bid = array();
	foreach($arr_btc as $btc) {
		$arr_bid[] = $btc['btc_id'];
		if ($arr_res !== false) { /* rembourser le prix ressources */
			$prix = Config::get($race, 'btc', $type, 'prix_res');
			foreach ($prix as $res => $nb_res)
				if (isset($arr_res[$res]))
					$arr_res[$res] += $nb_res;
				else
					$arr_res[$res] = $nb_res;
		}
		/* supprimer les unités du bat */
		$prix = Config::get($race, 'btc', $type, 'prix_unt');
		if (!empty($prix))
			foreach ($prix as $unt => $nb_unt)
				if (isset($arr_unt[$unt]))
					$arr_unt[$unt] += $nb_unt;
				else
					$arr_unt[$unt] = $nb_unt;
		/* on ne gère pas les terrains */
		$del++;
		if ($del == $nb)
			break;
	}
	return $arr_bid;	
}

// tous les membres, sauf non initialisé (2)
$sql = 'SELECT mbr_mid, mbr_race, mbr_pseudo, mbr_place
FROM zrd_mbr
WHERE mbr_etat IN (1, 3) AND mbr_race <> 0
ORDER BY mbr_mid LIMIT 2000';

$mid_array = $_sql->make_array($sql);
$sql=array();
foreach($mid_array as $_user)
{
	$mid  = $_user['mbr_mid'];
	$race = $_user['mbr_race'];
	$place_totale = 0;
	$res = array(); // remboursement en ressources

	$btc_array = Btc::getNbDone($mid);
	$arr_unt = array(); /* unités à supprimer */
	$arr_res = array(); /* resources à rembourser */
	$arr_bid = array(); /* liste des btc à détruire */
	foreach ($btc_array as $btc) {
		$maxi = Config::get($race, 'btc', $btc['btc_type'], 'limite');
		if ($maxi && $btc['btc_nb'] > $maxi) {
			/* compter les bat à supprimer */
			echo $_user['mbr_pseudo']." ($mid)($race) : ".($btc['btc_nb']-$maxi). 'bat ' . $btc['btc_type'].' en trop.'."\n";
			/* calcul btc à supprimer */
			$tmp_bid = del_btc_type($mid, $race, $btc['btc_type'], $btc['btc_nb'] - $maxi, $arr_unt, $arr_res);
			$arr_bid = array_merge($arr_bid, $tmp_bid);
			$btc['btc_nb'] = $maxi;
		}

		/* compter la place totale */
		$prod_pop = Config::get($race, 'btc', $btc['btc_type'], 'prod_pop');
		if ($prod_pop)
			$place_totale += $prod_pop * $btc['btc_nb'];
	}

	if (!empty($arr_bid)) { /* supprimer les bat */
		$sql = "DELETE FROM ".DB::getTablePrefix()."btc WHERE btc_mid = $mid ";
		$sql.= "AND btc_id IN (".implode(',',$arr_bid).");";
		if ($exec)
			$_sql->query($sql);
		else
			echo "\n".$sql;

		if (!empty($arr_unt)) { /* suppr unités */
			if ($exec)
				echo "\n".Unt::editBtc($mid, $arr_unt, -1). ' type d\'unités del.';
		}

		if (!empty($arr_res)) { /* payer le prix ressources */
			if ($exec)
				Res::mod($mid, $arr_res);
		}
	}

	if ($place_totale != $_user['mbr_place']) {
		echo "\n{$_user['mbr_pseudo']} place : {$_user['mbr_place']} corrigee $place_totale\n";
		if ($exec) Mbr::edit($mid, array('place' => $place_totale));
	}

} /* foreach $mid */
?></pre>
