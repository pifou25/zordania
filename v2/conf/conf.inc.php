<?php
require_once('secret_parameters.php');

/* === Configuration Site === */
define('ZORD_VERSION',"2.3.1");
define('ZORD_ANNEE',date('Y'));
define('ZORD_SPEED_VFAST', 0.16667); // 1 Tour toutes les 30sec
define('ZORD_SPEED_FAST', 5); // 1 Tour toutes les 5 minutes
define('ZORD_SPEED_MEDIUM', 15); // 1 Tour toutes les 15 minutes
define('ZORD_SPEED_NORMAL', 30); // 1 Tour par demie heure
define('ZORD_SPEED_SLOW',60); // 1 Tour par heures
define('ZORD_SPEED', ZORD_SPEED_MEDIUM);

define('SITE_MAX_CONNECTED', 300);
define('SITE_MAX_INSCRITS', 10000);

$host = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : "zordania.com";
define('SITE_URL', "".$_SERVER['REQUEST_SCHEME']."://".$host."/");
define('SITE_DIR', str_replace('conf','',dirname(__FILE__)));
define('WWW_DIR', SITE_DIR . "www/");
define('ZORDLOG_URL', 'zordania.free.fr'); // URL des archives

define('SITE_WEBMASTER_MAIL', 'webmaster@zordania.com');
define('SITE_CHARSET', 'utf-8'); // iso-8859-1, utf-8, ...

define('GEN_LENGHT', 6); /* Taille des chaines générées aléatoirement (pass, etc ...) */

/* ==== Utilisateurs === */
define('SITE_FLOOD_TIME', 30); /* Temps en seconde pour écrire deux messages a la suite */
define('TAILLE_MAX_LOGIN', 50); /* Nombre de caractères max d'un login : limité par les caractéristiques du champ correspondant dans la BDD */
define('TAILLE_MAX_PSEUDO', 50); /* Nombre de caractères max d'un pseudo : limité par les caractéristiques du champ correspondant dans la BDD */
define('TAILLE_MAX_MAIL', 50); /* Nombre de caractères max d'une adresse mail : limité par les caractéristiques du champ correspondant dans la BDD */

/* === Configuration Jeu === */

/* Nombre maximum en todo */
define('TODO_MAX_BTC',5);
define('TODO_MAX_RES',250);
define('TODO_MAX_UNT',250);
define('TODO_MAX_SRC',5);

/* Nombre unité max */
define('TOTAL_MAX_UNT_1', 700); //Toutes
define('TOTAL_MAX_UNT_2', 1000); // Sauf les  gobelins

/* Donjon */
// Distance à partir de laquelle une légion est visible dans le donjon
define('DST_VIEW_MAX', 50);

/* Légion */
define('LEG_MAX_NB', 40);
define('LEG_MAX_RANG', 22);
define('LEG_MAX_RANG_UNT', 700);
define('LEG_MAX_RANG_SAME_UNT', 1);

/* Ressource principale (pour le commerce) */
define('GAME_RES_PRINC',1);
/* Nourriture */
define('GAME_RES_BOUF',4);

/* Points */
define('MBR_NIV_1',7000);
define('MBR_NIV_2',35000);

/* Alliance */
define('ALL_MAX',6); // Nombre maximum de joueurs
define('ALL_MIN_PTS', 4000); // Points pour entrer
define('ALL_MIN_ADM_PTS', 7000); // Points pour créer une alliance
define('ALL_CREATE_PRICE', 1000); // Prix pour créer une alliance
define('ALL_JOIN_PRICE', 200); //Prix pour rejoindre une alliance
define('ALL_NOOB_TIME', 1); // Temps en jour pendant le quel on reste "NOOB"
define('ALL_MIN_DEP', 10); // Dépot minimal
define('ALL_MBR_TODISPL', 1); // Nombre minimum de joueur avant affichage dans la liste all

define('ALL_TAX',10); // Taxe du grenier (%)
define('ALL_SEUIL_PILLAGE', 5000); //En dessous de 1000 on ne considère pas un retrait comme un pillage potentiel

define('SHOOT_LIMIT_PAGE',20);
define('SHOOT_LIMIT_NB_PAGE',15);

$_limite_grenier = array(
	 1 =>  4294967295,   // # Or
	 2 =>  60000,        // # Bois
	 3 =>  60000,        // # Pierre
	 4 =>  600000,      // # Nourriture
	 5 =>  12000,        // # Fer
	 6 =>  12000,        // # Charbon
	 7 =>  1000,         // # Chevaux
	 8 =>  5000,        // # Acier
	 9 =>  4000,         // # Mithril
	 10 => 1000,         // # Bouclier en bois
	 11 => 1000,         // # Bouclier en acier
	 12 => 1000,         // # Epee
	 13 => 1000,         // # Epee longue
	 14 => 1000,         // # Arc
	 15 => 1000,         // # Arbalete
	 16 => 1000,         // # Cotte de mail
	 17 => 1000          // # Cotte de mithril
	 );

/* Attaques */
define('ATQ_MAX_NB24H', 5);
define('ATQ_PTS_DIFF', 3200);  /* Trop de points de différences */
define('ATQ_PTS_MIN', 50);  /* Pas assez de points armée pour attaquer */
define('ATQ_LIM_DIFF', 24000); /* Arène  =>  dépends des points armée */
define('ATQ_RATIO_COEF_ATQ', 7); /* dégats créé par l'attaquant */
define('ATQ_RATIO_COEF_DEF', 8); /* dégats créé par le défenseur */
define('ATQ_RATIO_COEF_BAT', 0.6); /* dégats batiments */
define('ATQ_RATIO_HEROS', 1.3); /* % perte dégats PDV héros */
define('ATQ_RATIO_DIST', 33); /* bonus unités à distance $nb*log($nb)/[ratio] */
define('ATQ_FAT', 5); /* inutile ici */
define('ATQ_LEG_IDLE', 5); /* legion idle en position d'attaque - nb jours */

/*Héros*/
define('HEROS_NRJ_MAX', 200);

/* Butins */
define('BUT_PILLAGE_COEF', 4); // On ne peux piller au maximum que le ($stock_d'une_ressource / BUT_PILLAGE_COEF)

/* Mise en veille (en jours)
 * Conseillé : ZZZ_TRIGGER >= ZZZ_MIN
 */
define('ZZZ_TRIGGER',7); // Temps de mise en veille automatique
define('ZZZ_MIN',6); // Durée minimale de mise en veille

/* Limites de points pour afficher un donjon ou une forteresse */
define('MAP_REGIONS', 9); /* Divisé en 9 */
/* News */
define('NWS_LIMIT_PAGE',5);

/* Limites de pages */
define('LIMIT_PAGE',15);
define('LIMIT_MBR_PAGE',50);
define('LIMIT_NB_PAGE',15);

/* Messages */
define('MSG_DEL_OLD',60); //60 jours
define('MSG_FLOOD_TIME',30); /* 30 secondes */
define('MSG_MAX_MMSG',5); /* Max de multi messages */
/* mid qui envoie le msg d'accueil cf ini/page.php */
define('MBR_WELC', 7203 );

/* Historique */
define('HISTO_DEL_OLD',15); //7 jours
define('HISTO_DEL_LOG_ALLY', 15); // 15 jours

/* Commerce */
define('MCH_ACCEPT',4); //Nombre de tours avant acceptation (avec un petit rand() dans le cron)
define('MCH_MAX',1000); //Nombre de tours max
define('MCH_COURS_MIN',1);
define('MCH_OLD',30); //calcul des cours du marché sur NB jours (les ventes plus vieilles sont supprimées)

define('COM_MAX_NB1',250);
define('COM_MAX_NB2',3000);
define('COM_MAX_NB3',5000);
define('COM_MAX_VENTES1',6);
define('COM_MAX_VENTES2',12);
define('COM_MAX_VENTES3',24);
define('COM_TAUX_MIN',0.80);
define('COM_TAUX_MAX',1.20);
define('COM_TAX',10);
define('COM_NEGO_TAUX',10);

/* Carte */
define('MAP_W',500);
define('MAP_H',500);

/* Allopass */
define('GAME_MAX_BONUS',1000);
define('MIN_BONUS_NB',30);

/* Parrains */
define('PARRAIN_GRD1', 5);
define('PARRAIN_GRD2', 20);
define('PARRAIN_GRD3', 50);
define('PARRAIN_BONUS_PRC', 5);

/* coefficient de defense groupee: modules/war/page.php */
$cst_ratio_def = array(0 => 1, 1 => 0.5, 2 => 0.33);

/* Langues disponibles */
$_langues = array('unknown' => 'fr_FR', 'fr' => 'fr_FR');
/* Races id => visible */
$_races = array(1=>true, 2=>true, 3=>true, 4=>true, 5=>true, 6=>false, 7=>true, 8=>false);
/* quotas de race par alliances: race du chef => (race=>nb max) */
$_races_aly = array(
		1 => array(1 => 12, 2 => 12, 3 => 12, 4 => 12, 5 => 12, 7=> 12, 8=>12),
		2 => array(1 => 12, 2 => 12, 3 => 12, 4 => 12, 5 => 12, 7=> 12, 8=>12),
		3 => array(1 => 12, 2 => 12, 3 => 12, 4 => 12, 5 => 12, 7=> 12, 8=>12),
		4 => array(1 => 12, 2 => 12, 3 => 12, 4 => 12, 5 => 12, 7=> 12, 8=>12),
		5 => array(1 => 12, 2 => 12, 3 => 12, 4 => 12, 5 => 12, 7=> 12, 8=>12),	
		6 => array(6 => 12),
		7 => array(1 => 12, 2 => 12, 3 => 12, 4 => 12, 5 => 12, 7=> 12, 8=>12),
		8 => array(1 => 12, 2 => 12, 3 => 12, 4 => 12, 5 => 12, 7=> 12, 8=>12),
);

$_css = array(14,4,3,2,1,10,11,15,16,5,17);
$_adsense_css = array(1 => '9107849390', 2 => '2158156650', 3 => '2158156650', 
	4 => '2158156650',5 => '9107849390', 17 => '', 10 => '2087210871', 11 => '2087210871', 14 => '0166103822', 
	15 => '6454056819', 16 => '');


$_regions = array(1=> array(1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0),
	2 => array(1=>10, 2=>20, 3=>40, 4=>20, 5=>20, 6=>5, 7=>20, 8=>0),
	3 => array(1=>0, 2=> 0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0),
	4 => array(1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0),
	5 => array(1=>25, 2=>15, 3=>20, 4=>20, 5=>20, 6=>5, 7=>15, 8=>0),
	6 => array(1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0),
	7 => array(1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0),
	8 => array(1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>100, 7=>0, 8=>0),
	9 => array(1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0));

/* include pour les constantes descriptives du jeu */
include 'const.inc.php';

/* Droits */
$_droits = array();
$_droits[GRP_VISITEUR] = array(DROIT_SITE, DROIT_PUNBB_GUEST);
$_droits[GRP_EXILE] = array();
$_droits[GRP_EXILE_TMP] = array(DROIT_SITE, DROIT_MSG, DROIT_PUNBB_GUEST);
$_droits[GRP_JOUEUR] = array(DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER);
$_droits[GRP_EVENT] = array(DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER);
$_droits[GRP_PNJ] = array(DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER, DROIT_ANTI_FLOOD);
$_droits[GRP_CHEF_REG] = array(DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER);
$_droits[GRP_CHAMP_REG] = array(DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER);
$_droits[GRP_SCRIBE] = array(DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER);
$_droits[GRP_NOBLE] = array(DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER);
$_droits[GRP_KING] = array(DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER);
$_droits[GRP_SAGE] = array(DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER, DROIT_ANTI_FLOOD);
$_droits[GRP_GARDE] = array(DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER, DROIT_ANTI_FLOOD, DROIT_ADM, DROIT_ADM_AL, DROIT_ADM_MBR);
$_droits[GRP_PRETRE] = array(DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER,DROIT_PUNBB_MOD, DROIT_ANTI_FLOOD);
$_droits[GRP_DEV] = array(DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_PUNBB_MEMBER, DROIT_ADM);
$_droits[GRP_ADM_DEV] = array(DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_MMSG,
	DROIT_ADM, DROIT_ADM_AL, DROIT_ADM_COM, DROIT_ADM_MBR, DROIT_ADM_TRAV, DROIT_ADM_EDIT,
	DROIT_PUNBB_MEMBER,DROIT_PUNBB_MOD,DROIT_PUNBB_ADMIN, DROIT_SDG,DROIT_ANTI_FLOOD);
$_droits[GRP_DEMI_DIEU] = array(DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_MMSG,
	DROIT_ADM, DROIT_ADM_AL, DROIT_ADM_COM, DROIT_ADM_MBR, DROIT_ADM_EDIT,
	DROIT_PUNBB_MEMBER,DROIT_PUNBB_MOD,DROIT_PUNBB_ADMIN, DROIT_SDG,DROIT_ANTI_FLOOD);
$_droits[GRP_DIEU] = array(DROIT_SITE, DROIT_PLAY, DROIT_MSG, DROIT_MMSG,
	DROIT_ADM, DROIT_ADM_AL, DROIT_ADM_COM, DROIT_ADM_MBR, DROIT_ADM_TRAV,DROIT_SDG, DROIT_ADM_EDIT,
	DROIT_PUNBB_MEMBER,DROIT_PUNBB_MOD,DROIT_PUNBB_ADMIN, DROIT_ANTI_FLOOD);


// CRON ou INTERNET ?
define('CRON', !array_key_exists('SERVER_SOFTWARE', $_SERVER)
        && (php_sapi_name() === 'cli' || !array_key_exists('REMOTE_ADDR', $_SERVER)));
