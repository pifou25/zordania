<?php
function zrd_dump($mid, $full = false, $prefix = 'zrd_', $mode = 2) //dumpMySQL($serveur, $login, $password, $base, $mode)
{
	global $_sql;
	$mid = protect($mid, 'uint');

    $entete = "-- ----------------------\n";
    $entete .= "-- dump de la base zordania, mid=$mid au ".date("d-M-Y")."\n";
    $entete .= "-- ----------------------\n";
    $creations = "";
    $insertions = "\n";
    $deletions = "\n";
    
    //$listeTables = mysql_query("show tables", $connexion);
	// liste des tables avec leur colonnde à filtrer sur $mid
	if ($full)
		$listeTables = array($prefix.'mbr'=> 'mbr_mid', 
			$prefix.'al_mbr' => 'ambr_mid',
			$prefix.'al_res_log' => 'arlog_mid',
			$prefix.'al_shoot' => 'shoot_mid',
			$prefix.'atq' => 'atq_mid1',
			$prefix.'atq' => 'atq_mid2', // 2x la même table
			$prefix.'atq_mbr' => 'atq_mid',
			$prefix.'bon' => 'bon_mid',
			$prefix.'btc' => 'btc_mid',
			$prefix.'cmt' => 'cmt_mid',
			$prefix.'hero' => 'hro_mid',
			$prefix.'histo' => 'histo_mid',
			$prefix.'leg' => 'leg_mid',
			$prefix.'log' => 'log_mid',
			$prefix.'mbr_log' => 'mlog_mid',
			$prefix.'mch' => 'mch_mid',
			$prefix.'msg_env' => 'menv_mid',
			$prefix.'msg_rec' => 'mrec_mid',
			$prefix.'ntes' => 'nte_mid',
			$prefix.'rec' => 'rec_mid',
			$prefix.'res' => 'res_mid',
			$prefix.'res_todo' => 'rtdo_mid',
			$prefix.'src' => 'src_mid',
			$prefix.'src_todo' => 'stdo_mid',
			$prefix.'trn' => 'trn_mid',
			$prefix.'unt_todo' => 'utdo_mid',
			$prefix.'vld' => 'vld_mid');
	else // version light
		$listeTables = array($prefix.'mbr'=> 'mbr_mid', 
			$prefix.'al_mbr' => 'ambr_mid',
			$prefix.'al_res_log' => 'arlog_mid',
			$prefix.'atq' => 'atq_mid1',
			$prefix.'atq' => 'atq_mid2', // 2x la même table
			$prefix.'atq_mbr' => 'atq_mid',
			$prefix.'bon' => 'bon_mid',
			$prefix.'btc' => 'btc_mid',
			$prefix.'hero' => 'hro_mid',
			$prefix.'histo' => 'histo_mid',
			$prefix.'leg' => 'leg_mid',
			$prefix.'mch' => 'mch_mid',
			$prefix.'rec' => 'rec_mid',
			$prefix.'res' => 'res_mid',
			$prefix.'res_todo' => 'rtdo_mid',
			$prefix.'src' => 'src_mid',
			$prefix.'src_todo' => 'stdo_mid',
			$prefix.'trn' => 'trn_mid',
			$prefix.'unt_todo' => 'utdo_mid',
			$prefix.'vld' => 'vld_mid');

	// tables secondaires : un autre id que mid fait la jointure
	$ListeTablesSec = array(
		$prefix.'al' => array('al_aid' => array( $prefix.'al_mbr' => 'ambr_aid')),
		$prefix.'al_res' => array('ares_aid' => array( $prefix.'al_mbr' => 'ambr_aid')),
		$prefix.'al_res_log' => array('arlog_aid' => array( $prefix.'al_mbr' => 'ambr_aid')),
		$prefix.'al_shoot' => array('al_aid' => array( $prefix.'al_mbr' => 'ambr_aid')),
		$prefix.'diplo' => array('al_aid' => array( $prefix.'al_mbr' => 'ambr_aid')),
		$prefix.'leg_res' => array('lres_lid' => array( $prefix.'leg' => 'leg_id')),
		$prefix.'unt' => array('unt_lid' => array( $prefix.'leg' => 'leg_id')));

    foreach($listeTables as $table => $cle)
    {
        // si l'utilisateur a demandé la structure ou la totale
        if($mode == 1 || $mode == 3)
        {
            $creations .= "-- -----------------------------\n";
            $creations .= "-- creation de la table ".$table."\n";
            $creations .= "-- -----------------------------\n";
			$data = $_sql->make_array("show create table ".$table);
			foreach($data as $value){
				$creations .= $value['Create Table'] . ";\n\n";
			}
        }
        // si l'utilisateur a demandé les données ou la totale
        if($mode > 1)
        {
			$sql = "SELECT * FROM $table WHERE $cle = $mid";
			// extraction avec ID conservés
			$insertions .= CreeInsertSQL($sql, $table);
			// extraction avec NULL à la place des ID PK
			//$insertions .= CreeInsertSQL($sql, $table, array($cle => '##mid##'));
			$deletions .= "DELETE * FROM $table WHERE $cle = $mid;\n";
        }
    }

	foreach($ListeTablesSec as $table => $cle_arr)
	{
        // si l'utilisateur a demandé la structure ou la totale
        if($mode == 1 || $mode == 3)
        {
            $creations .= "-- -----------------------------\n";
            $creations .= "-- creation de la table ".$table."\n";
            $creations .= "-- -----------------------------\n";
			$data = $_sql->make_array("show create table ".$table);
			foreach($data as $value){
				$creations .= $value['Create Table'] . ";\n\n";
			}
        }
        // si l'utilisateur a demandé les données ou la totale
        if($mode > 1)
        {
			foreach($cle_arr as $cle => $jointable_arr)
			{
				foreach($jointable_arr as $jointable => $joincle)
				{
					if(!isset($listeTables[$jointable]))
					{
						echo("$jointable inexistant dans listeTables!");
					}
					else
					{
						$join  = " INNER JOIN $jointable ON $table.$cle = $jointable.$joincle ";
						$join .= "WHERE $jointable.".$listeTables[$jointable]." = $mid";
					}
				}
			}
			$sql = "SELECT $table.* FROM $table $join";
			$insertions .= CreeInsertSQL($sql, $table);
			$deletions .= "DELETE * FROM $table WHERE $cle IN (SELECT $joincle FROM {$jointable} WHERE {$listeTables[$jointable]} = $mid;\n";
		}
	}
 
/* 
    $fichierDump = fopen("dump.sql", "wb");
    fwrite($fichierDump, $entete);
    fwrite($fichierDump, $creations);
    fwrite($fichierDump, $insertions);
    fclose($fichierDump);
    echo "Sauvegarde réalisée avec succès !!";
*/
	return "$entete \n $creations \n $deletions \n $insertions";
}

function CreeInsertSQL($sql, $table, $field = null)
{
	global $_sql;
	
    $result = $_sql->query($sql);
	if ($result === false) return "-- $table en erreur\n\n";
	if($result->num_rows == 0) return "-- $table vide\n\n";
	
    $insertions  = "-- -----------------------------\n";
    $insertions .= "-- insertions dans la table ".$table."\n";
    $insertions .= "-- -----------------------------\n";
	
	$fields = $result->fetch_fields();
    $insert = "INSERT INTO ".$table." VALUES";
	$datas = array();
	
    while($nuplet = $result->fetch_array()) // select * from $table
    {
		$values = array();
		foreach($fields as $i => $finfo)
        {

		  // char ou assimilé ou date ( 7 )
		  if($finfo->type == 253 || $finfo->type == 254 || $finfo->type == 252 || $finfo->type == 7)
			  $sep = "'";
		  else
			  $sep = "";

		  $data = addslashes($nuplet[$i]);
		  if(is_array($field)){
			  foreach($field as $key => $value){
				  if($key == $finfo->name){
					  $data = $value;
					  break;
				  }
			  }
		  }
		  
          $values[] = $sep . $data . $sep; 
        }
        $datas[] =  "(" . join( ", ", $values) . ")";
		
		if(count($datas) > 10) {
			$insertions .= $insert . "\n" . join(",\n", $datas) . ";\n\n";
			$datas = array();
		}
    }
	$insertions .= $insert . "\n" . join(",\n", $datas) . ";\n\n";
	return $insertions;
}

?>
