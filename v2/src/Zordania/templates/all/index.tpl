<!DOCTYPE html>
<html>
<head>
	<title>Zordania</title>
	<link rel="shortcut icon" type="image/png" href="img/favicon.png" />
	<link rel="icon"  type="image/png" href="img/favicon.png" />
	<meta name="viewport" content="width=device-width"/>
	<meta http-equiv="Content-type" content="text/html; charset={charset}" />
	<meta name="generator" content="Les mains de Iksaif" />
	<meta name="revisit-after" content="7 days" />
	<meta name="description" content="Jeu de Gestion/Stratégie dans un univers Médiéval-Fantastique. Fondez un village, construisez des bâtiments pour produire des ressources, érigez votre armée et partez à la conquête du monde !" />
	<meta name="keywords" content="zordania;jeu;php;medieval;orc;humain;nain;drow;elfe" />
	<meta name="author" content="CHARY Corentin" />

	<link rel="stylesheet" type="text/css" media="screen" title="Zord2" href="skin/brown_underground/style.css" />
	<link rel="stylesheet" media="all and (max-width: 800px)" href="skin/imports/mobile.css" />
	
</head>
<body>
<div id="contenu">
	<div id="module">
		<a name="module"></a>
		<div class="haut2">&nbsp;</div>
		<div class="centre2">
			<div class="contenu">
			<hr/>
			<h2 class="titre_module">Travaux en cours</h2>
			<hr/>

			<if cond='{page}'>
				<include file="{page}" cache="1" />
			</if>
			<hr/>
			</div>
			<div class="bas2">&nbsp;</div>
		</div>
	</div>
<if cond="{sv_site_debug}">
<include file="../debug.tpl" cache="1" />
</if>
</div>
</body>
</html>