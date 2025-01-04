<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<if cond='{_user[race]}'>
	<load file="race/{_user[race]}.config" />
	<load file="race/{_user[race]}.descr.config" />
	</if>
	<load file="config/config.config" />
	<title><if cond="isset({spec_title})">{spec_title} - Zordania</if>
		<else>Zordania - {pages[{module}]} <if cond='isset({btc_id}) AND {btc[{_user[race]}][alt][{btc_id}]}'>- {btc[{_user[race]}][alt][{btc_id}]}</if></else></title>
	<link rel="shortcut icon" type="image/png" href="img/favicon.png" />
	<link rel="icon"  type="image/png" href="img/favicon.png" />
	<meta name="viewport" content="width=device-width"/>
	<meta http-equiv="Content-type" content="text/html; charset={charset}" />
	<meta name="generator" content="Les mains de Iksaif" />
	<meta name="revisit-after" content="7 days" />
	<meta name="description" content="Jeu de Gestion/Stratégie dans un univers Médiéval-Fantastique. Fondez un village, construisez des bâtiments pour produire des ressources, érigez votre armée et partez à la conquête du monde !" />
	<meta name="keywords" content="zordania;jeu;php;medieval;orc;humain;nain;drow;elfe" />
	<meta name="author" content="CHARY Corentin" />
	<link rel="browser-game-info" href="http://www.zordania.com/xml/info.xml" />

	<link type="text/css" href="skin/jquery/jquery-ui-1.10.0.custom.min.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" media="screen" title="{css[{_user[design]}][1]}" href="skin/{css[{_user[design]}][0]}/style.css" />
	<link rel="stylesheet" media="all and (max-width: 800px)" href="skin/imports/mobile.css" />
	
	<script type="text/javascript">
	var cfg_url = '{cfg_url}';
	var user_css = {_user[design]};
	<if cond="isset({_user[mobile]})">var mobilePhp = '{_user[mobile]}';
	</if>
	</script>
	<# script type="text/javascript" src="js/functionAddEvent.js"></script #>
	<# script type="text/javascript" src="js/toolTipLib.js"></script #>
	<script type="text/javascript" src="js/script.js?v=250104"></script>
	<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script type="text/javascript" src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script type="text/javascript" src="js/jquery.zordania.js"></script>
	<script type="text/javascript" src="js/jquery.ddslick.min.js"></script>
</head>
<body>
<if cond='{_display} != "module" && {_display} != "popup" '>
<div id="contenu">

	<div id="logo">
		<div class="haut">&nbsp;</div>
		<div class="centre">
			<div class="contenu">
				<span id="text_logo"><a href="{cfg_url}">Zordania</a> - <a href="#menu">Menu</a> - <a href="#content">Contenu</a></span>
				<h1><a href="{cfg_url}">Zordania</a></h1>
			</div>
		</div>
		<div class="bas">&nbsp;</div>
	</div>

	<div id="stats">
		<div class="haut">&nbsp;</div>
		<div class="centre">
			<div class="contenu">
				<include file="stats.tpl" cache="1" />
			</div>
		</div>
		<div class="bas">&nbsp;</div>
	</div>

	<div id="espace_stats_pub">&nbsp;</div>

	<div id="pub">
		<div class="haut">&nbsp;</div>
		<div class="centre">
			<div class="contenu">
				<include file="modules/session/header.tpl" cache="1" />
			</div>
		</div>
		<div class="bas">&nbsp;</div>
	</div>

	<div class="cleaner">&nbsp;</div>

<if cond='{ses_can_play} AND {ses_mbr_etat_ok}'>
	<div id="actbar">
	<include file="actbar.tpl" cache="1" />
	</div>
</if>

	<div id="menu">
	<include file="menu.tpl" cache="1" />
	</div>

	<div id="espace_menu_centre">&nbsp;</div>

	<div id="module">
		<a name="module"></a>
		<div class="haut">&nbsp;</div>
		<div class="centre">
			<div class="contenu">

			  <if cond="isset({sv_site_debug})"><div class="infos">Vous êtes sur le site de développement, veuillez aller sur <a href="https://zordania.com" >https://zordania.com</a> et vider vos caches/cookies</div></if>
			  <a id="content"></a>
			  <if cond='{cron_lock} == true'>
			  <p class="infos">Calculs des tours en cours, patientez quelques secondes, puis actualisez la page ...</p>
			  <div class="block">{cron_lock}</div>
			  </if>
			  <if cond='{no_cookies} == true'>
			  <p class="infos">Il faut activer les cookies pour pouvoir profiter du site !</p>
			  </if>
			  <if cond='{SITE_TRAVAUX} == true'>
			  <p class="error">Site en pause ... Travaux en cours ... Accès autorisé ...</p>
			  </if>
			  <if cond='isset({need_to_be_loged})'>
			  <p class="infos">Il faut se connecter pour accéder à cette partie du site.</p>
			  <meta http-equiv="refresh" content="1; url=presentation.html">
			  <include file="modules/session/connect.tpl" cache="1" />
			  </if>
			  <elseif cond='{cant_view_this} == true'>
			  <p class="infos">Vous n'avez pas les droits nécessaires pour voir cette partie du site.</p>
			  </elseif>
			  <elseif cond='isset({module_tpl})'>
				<h2 class="titre_module">{pages[{module}]}</h2>
				<if cond="{_user[etat]} == MBR_ETAT_INI">
					<p class="infos">{_user[pseudo]} : Compte non initialisé : <a href="ini.html" title="Initialiser!">initialiser</a></p>
				</if>
				<if cond='{_user[mid]} == 1'>
					<include file="modules/session/connect.tpl" cache="1" />
				</if>
				<include file="{module_tpl}" cache="1" />
			  </elseif>

		    	</div>
		</div>
		<div class="bas">&nbsp;</div>
	</div>

<div id="skyscraper">
<script type="text/javascript"><!--
google_ad_client = "ca-pub-5781750090543674";
/* zorddev zord2 skycraper large */
google_ad_slot = "5649706589";
google_ad_width = 160;
google_ad_height = 600;
//-->
</script>
<!-- script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script -->
</div>

	<div class="cleaner">&nbsp;</div>

	<div id="footer">
	<include file="footer.tpl" cache="1" />
	</div>

</div>
</if>
<else>
	<if cond='isset({need_to_be_loged})'>
		<p class="infos">Il faut se connecter pour accéder à cette partie du site.</p>
	</if>
	<if cond='isset({module_tpl})'>
	  <div id="popup">
		<include file="{module_tpl}" cache="1" />
       	  </div>
	</if>
	<if cond='{_display} == "popup"'>
		<div class="close_popup">
		[  <a href="javascript:self.close()" title="Fermer la fenêtre">Fermer</a> ]
		</div>
	</if>
</else>

<# div pour popup jquery #>
<div id="dialog-modal" title="Titre" style="display:none;"><div id="dialog-text"></div></div>

<if cond="isset({sv_site_debug})"><include file="debug.tpl" cache="1" /></if>

<script type="text/javascript">
//var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
//document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>

<script type="text/javascript">
/*
try {
var pageTracker = _gat._getTracker("UA-6710160-1");
pageTracker._trackPageview();
} catch(err) {}
*/
</script>
<span id="bp_mobile" class="bp_checking"></span>
<span id="bp_desktop" class="bp_checking"></span>
</body>
</html>
