<?php
session_start();
ignore_user_abort();
error_reporting (E_ALL | E_STRICT | E_RECOVERABLE_ERROR);
date_default_timezone_set("Europe/Paris");

/* Fonctions de Bench */
function mtime()
{
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

function mark($title_or_get)
{
	static $array = array();

	if($title_or_get === true)
		return $array;
	else {
		$array[$title_or_get] = mtime();
		return true;
	}
}

$t1 = mtime();
mark('start');

/* Includes g�n�raux */
require_once 'vendor/autoload.php';
require_once("src/Zordania/lib/divers.lib.php");
require_once("conf/conf.inc.php");
ini_set("include_path", SITE_DIR);

/* Gestion des erreurs : fonctions dans ExceptionHandler.php */
$_excHandler = new ExceptionHandler();

$_cache = new cache('global');
/*  infos admin en cache : variable globale */
$admin_cache = new cache('admin');

/*
* Templates
*/
$_tpl = new Template();
$_tpl->set_dir(__DIR__ . '/src/Zordania/templates');
$_tpl->set("charset", SITE_CHARSET);

/* display : xhtml - module - ajax - popup - xml */
$_display = request("display", "string", "get", "xhtml"); /* Type d'affichage */
$_tpl->set_ref("_display", $_display);
$_tpl->set_ref("_races", $_races);
$_tpl->set_ref("_races_aly", $_races_aly);
$_tpl->set_ref("_def_atq", $_def_atq);
$_tpl->set_ref("_langues", $_langues);
$_tpl->set("_cache", $_cache->get_array());

$_tpl->set('no_cookies',!$_COOKIE);
/* set tpl to the error handler */
$_excHandler->_tpl = $_tpl;

mark('lib');

DB::init($settings['database']);
mark('eloquent');

/*
* Sessions
*/
$_ses = new session();
$_file = request("file", "string", "get", "vlg");
$_type = request("type", "string", "get");
$_act = request("act", "string", "get");
$_sub = request("sub", "string", "get", request("sub", "string", "post"));

/*
* device: mobile ou desktop: mettre la variable en session
*/
$_mobile = request('mobile', 'string', 'get');
if(!empty($_mobile)){
	$_ses->set('mobile', $_mobile);
	die($_mobile);
}

if($_file == "session" AND ($_act == "login" OR $_act == "logout"))
	$log_in_out = true;
else
	$log_in_out = false;

/* Si c'est la premi�re fois qu'on vient, on se connecte automatiquement*/
if(!$_ses->session_opened()) {
	if(!$_ses->auto_login()) die( "Erreur auto-login");
} elseif(!$log_in_out) { /* Sinon, si on est sur une page normale */
	if(!$_ses->update($_file)) { /* On maj la session */
		if(!$_ses->auto_login()) die( "Erreur auto-login"); /* Ca a merd� .. on tente de se connecter en n'importe quoi */
	}
}

$_user = & $_ses->vars;
if($_file == "connec" && $_user["loged"]) {
	$_file = "news";
}
$_excHandler->_user =  "{$_user['pseudo']} ({$_user['mid']})";

mark('ses');

/* Historique */
$_histo = new Hst();

if(SITE_TRAVAUX && !$_ses->canDo(DROIT_ADM_TRAV))
{
	//die( "DROIT $_file $_act $cron_lock");
	$_ses->logout();
	$_tpl->set('sv_site_debug', false);
	$_tpl->set("cfg_url",SITE_URL);
	$_tpl->set_lang('all');
	$_tpl->set('page','tests.tpl');
	echo $_tpl->get('index.tpl',1);
	exit;
}

/*
* Petit trucs a faire une fois qu'on a $_user
*/
$_tpl->set_lang($_user['lang']);
$_tpl->set_ref('_user',$_user);
$_tpl->set('_file',$_file);

/*
* Affichage
*/
if(file_exists(SITE_DIR.'logs/cron.lock'))
	$cron_lock = nl2br(file_get_contents(SITE_DIR.'logs/cron.lock'));
else
	$cron_lock = "";

$_tpl->set("cron_lock",$cron_lock);

/* Const */
$const = get_defined_constants(true);
$_tpl->set($const['user']);

/* Config */
$_tpl->set("cfg_url",SITE_URL);
$_tpl->set("_act",$_act);
$_tpl->set("_sub",$_sub);
$_tpl->set("cfg_style",request("style", "string", "cookie", "Marron"));
$_tpl->set("zordlog_url",ZORDLOG_URL);
if(!in_array($_user['design'], $_css)) // check if the current css exist
	$_user['design'] = 4;
$_tpl->set("adsense_code",$_adsense_css[$_user['design']]);

/* Droits */
$_tpl->set("ses_loged", ($_user['login'] != "guest"));
$_tpl->set("ses_admin", $_ses->canDo(DROIT_ADM));
$_tpl->set("ses_can_play", $_ses->canDo(DROIT_PLAY));
$_tpl->set("ses_mbr_etat_ok", ($_user['etat'] == MBR_ETAT_OK));
$_tpl->set("ses_adm_msg", $_ses->canDo(DROIT_ADM_MBR) && $admin_cache->msg_report > 0);


$lock_array = array('msg','forum','news','admin','404','manual','faq','irc','a_propos','notes','bonus','session');

if(preg_match("/(http|ftp|\/|\.)/i",$_file))
	$_file = "404";

/* header utf-8 pour tout le site */
$charset = SITE_CHARSET; // iso-8859-1, utf-8, ...

if($_display == "xml") { /* Sortie en XML */
	header("Content-Type: application/xml; charset=$charset");
	$filen = MOD_DIR.$_file."/xml.php";

	if(!file_exists($filen))
		exit;

	require_once($filen);
	mark($_file);

	if($_type != "ajax"){
		echo $_tpl->get($_module_tpl,1);
	}
	mark('tpl');
} else if($_display == "json") {
	if($_user['etat'] != MBR_ETAT_ZZZ and $_user['etat'] != MBR_ETAT_INI and !$cron_lock) {
		header("Content-Type: application/json; charset=$charset");
		$filen = MOD_DIR.$_file."/json.php";

		if(!file_exists($filen))
			exit;

		require_once($filen);
		mark($_file);

		if($_type != "ajax"){
			echo $_tpl->get($_module_tpl,1);
		}
		mark('tpl');
	}
} else if($_display == "ajax") {
	if($_user['etat'] == MBR_ETAT_ZZZ || $_user['etat'] == MBR_ETAT_INI and !$cron_lock)
		exit;
		
	header("Content-Type: text/html; charset=$charset");
	if(!$cron_lock || in_array($_file,$lock_array)) {
		$filen = MOD_DIR.$_file."/page.php";

		if(!file_exists($filen)) {
			$_file = "404";
			require_once(MOD_DIR."404/page.php");
		} else
			require_once($filen);

		mark($_file);
	}

	$_tpl->set('module',$_file);
	$_tpl->set("cant_view_this",!$_ses->canDo(DROIT_SITE));
	echo $_tpl->get("ajax.tpl",1);
	mark('tpl');
} else {
    
    // v�rifier si une qu�te a �t� achev�e
    if(isset($_user['qst']) && is_array($_user['qst']) && isset($_user['qst']['qst_id'])){
        if($_ses->checkParam($_user['qst']['cfg_objectif'], $_user['qst']['cfg_obj_value'])){
            // valider la qu�te - rechercher la suivante
            Qst::where('qst_id', $_user['qst']['qst_id'])->update(['finished_at' => DB::raw('NOW()')]);
            $_ses->update_qst();
            //$_file = 'qst';
        }
    }
	header("Content-Type: text/html; charset=$charset");
	
	if($_file != "session") {
		if($_user['etat'] == MBR_ETAT_ZZZ)
			$_file = 'zzz';
		else if($_user['etat'] == MBR_ETAT_INI) {
			$ok = array('ini', 'carte', 'manual', 'inscr', 'forum', 'a_propos', 'sdg', 'news', 'stat', 'member', 'parrain', 'irc', 'notes', 'msg', 'histo', 'presentation');
			if(!in_array($_file, $ok))
				$_file = 'ini';
		}
		else if($_user['mid'] == 1) { // guest
			$ok = array( 'carte', 'manual', 'inscr', 'forum', 'a_propos', 'sdg', 'news', 'stat', 'parrain', 'irc', 'presentation');
			if(!in_array($_file, $ok))
				$_file = 'presentation';
		}
	}
	
	// var contenant des infos de d�bugage ! $debugvars
	$_debugvars = [];
	$_tpl->set_ref('debugvars', $_debugvars);

	if(!$cron_lock || in_array($_file,$lock_array)) {
		$filen = MOD_DIR.$_file."/page.php";

		if(!file_exists($filen)) {
			$_file = "404";
			require_once(MOD_DIR."404/page.php");
		} else
			require_once($filen);
		mark($_file);
	}

	$_tpl->set('module',$_file);

	require_once("src/Zordania/lib/stats.php");
	mark('stats');

	$_tpl->set("cant_view_this",!$_ses->canDo(DROIT_SITE));

	$_tpl->set("sv_nbreq", count(DB::connection()->getQueryLog()));
	$_tpl->set("sv_diff",$t1);

	if(SITE_DEBUG)
	{
		unset($_histo);
		$_tpl->set_globals();
		$_tpl->set('sv_site_debug',true);
                $mysql = DB::getSqlTime();
		$_tpl->set('sv_total_sql_time', $mysql);
		$_tpl->set('sv_queries',[]);
                $_tpl->set('eloQueries',DB::connection()->getQueryLog());
		$t2 = mtime();
        }

	if($_file == "connec")
		echo $_tpl->get("connec.tpl", 1);
	else
		echo $_tpl->get("index.tpl",1);

	mark('tpl');

	if(SITE_DEBUG) {
		echo '<div class="debug"><ul>';
		foreach(mark(true) as $title => $time)
		{
			if(!isset($prev_time))
			$prev_time = $time;
			echo  "<li>$title: ".round($time-$prev_time, 5). "</li>";
			$prev_time = $time;
		}


                // log every sql requests into mysql.log
                DB::sqlLog(' WEB mid='.$_user['mid']);

		$total = (mtime() - $t1);
		$templ = (mtime() - $t2);
		$php = $total - $mysql - $templ;
		echo "<li>Mysql: ".$mysql."</li>";
		echo "<li>Templates: ".$templ."</li>";
		echo "<li>Php: ".$php."</li>";
		echo "<li>Total: ".$total."</li>";
		echo "</ul></div>";
	}
}

            