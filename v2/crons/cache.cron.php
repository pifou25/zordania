<?php
$log_cache = "Cache";

function glob_cache() {
	global $_t, $_p, $_ts, $_cache;

    // enregistre ces valeurs dans le fichier /cache/global.cache.php
	$_cache->mtime = mtime();
	$_cache->nb_online = (int) Ses::count();
	$_cache->nb_mbr = (int) Mbr::count();
	$_cache->tour = $_t;
	$_cache->tours = $_ts;
	$_cache->period = $_p;

}
