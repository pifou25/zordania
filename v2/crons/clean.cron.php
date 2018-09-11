<?php

$log_clean = "Ramasse-miettes";

function zrd_clean() {
	global $_h, $_m;

        Ses::whereRaw('ses_ldate < (NOW() - INTERVAL 600 SECOND)')->delete();

	$sql = "DELETE FROM ".DB::getTablePrefix()."atq_mbr ";
	$sql.= "WHERE atq_aid IN (
		SELECT atq_aid FROM ".DB::getTablePrefix()."atq
		WHERE atq_date < (NOW() - INTERVAL ".HISTO_DEL_OLD." DAY))";
	DB::delete($sql);

        Atq::whereRaw('atq_date < (NOW() - INTERVAL ? DAY)', HISTO_DEL_OLD)->delete();

	if($_h == 0) {
                // lister les tables SQL
                $tables = DB::sel( 'SHOW TABLES FROM ' . MYSQL_BASE);
                $tables = array_map(function($val)
                {
                    foreach ($val as $value)
                        return $value;
                }, $tables);
                $sql = join( $tables, ',');
                DB::connection()->getPdo()->prepare('OPTIMIZE TABLE '.$sql)->fetchAll();
	}
}
