-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le :  mar. 19 déc. 2017 à 20:32
-- Version du serveur :  5.7.19
-- Version de PHP :  7.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `zordania`
--

DELIMITER $$
--
-- Procédures
-- move_member = deplacement d'un joueur: village + legions
--
DROP PROCEDURE IF EXISTS `move_member`$$
CREATE DEFINER=`zordania`@`%` PROCEDURE `move_member` (IN `member` INT, IN `move_to` INT)  BEGIN
  -- position de départ
  declare move_from int;
  select mbr_mapcid into move_from from zrd_mbr where mbr_mid = member;

-- déplacer toutes les légions
UPDATE zrd_leg SET leg_cid= move_to, leg_dest = 0, leg_etat = IF(leg_etat > 3, 3, leg_etat)
  WHERE leg_mid=member;
-- rentrer les légions ennemies
UPDATE zrd_leg SET leg_cid= (SELECT mbr_mapcid FROM zrd_mbr WHERE mbr_mid=leg_mid), leg_dest = 0, leg_etat = IF(leg_etat > 3, 3, leg_etat)
  WHERE leg_dest=move_from;
-- déplacer le village
UPDATE zrd_mbr SET mbr_mapcid= move_to
WHERE mbr_mid = member;
-- type des cases
UPDATE zrd_map SET map_type=7 WHERE map_cid = move_to;
UPDATE zrd_map SET map_type=6 WHERE map_cid = move_from;

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_al`
-- alliance (id / nom / member id du chef / nb de points / alliance ouverte au recrutement / nb de membres / description / regles / diplomatie)
--

DROP TABLE IF EXISTS `zrd_al`;
CREATE TABLE IF NOT EXISTS `zrd_al` (
  `al_aid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `al_name` varchar(150) NOT NULL DEFAULT '',
  `al_mid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `al_points` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `al_open` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `al_nb_mbr` tinyint(2) UNSIGNED NOT NULL DEFAULT '1',
  `al_descr` text NOT NULL,
  `al_rules` text NOT NULL,
  `al_diplo` text,
  PRIMARY KEY (`al_aid`),
  KEY `al_points` (`al_points`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_al_mbr`
-- membres d'alliance (membre id / alliance id / date d'entree dans l'alliance / grade d'alliance)
--

DROP TABLE IF EXISTS `zrd_al_mbr`;
CREATE TABLE IF NOT EXISTS `zrd_al_mbr` (
  `ambr_mid` int(10) UNSIGNED NOT NULL,
  `ambr_aid` int(10) UNSIGNED NOT NULL,
  `ambr_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ambr_etat` tinyint(1) UNSIGNED NOT NULL,
  PRIMARY KEY (`ambr_mid`),
  KEY `ambr_aid` (`ambr_aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_al_res`
-- grenier d'alliance (alliance id / ressource id / nb de ressources + ou -)
--

DROP TABLE IF EXISTS `zrd_al_res`;
CREATE TABLE IF NOT EXISTS `zrd_al_res` (
  `ares_aid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ares_type` smallint(3) UNSIGNED NOT NULL DEFAULT '0',
  `ares_nb` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`ares_aid`,`ares_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_al_res_log`
-- historique de grenier (id / alliance id / membre id / resource id / nb de resources + ou - / date de transaction / ip)
--

DROP TABLE IF EXISTS `zrd_al_res_log`;
CREATE TABLE IF NOT EXISTS `zrd_al_res_log` (
  `arlog_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `arlog_aid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `arlog_mid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `arlog_type` smallint(3) UNSIGNED NOT NULL DEFAULT '0',
  `arlog_nb` int(10) NOT NULL DEFAULT '0',
  `arlog_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `arlog_ip` varchar(50) NOT NULL,
  PRIMARY KEY (`arlog_id`),
  KEY `al_res_log_aid` (`arlog_aid`),
  KEY `al_res_log_date` (`arlog_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_al_shoot`
-- shootbox alliance (id / membre id / alliance id / date de post / message)
--

DROP TABLE IF EXISTS `zrd_al_shoot`;
CREATE TABLE IF NOT EXISTS `zrd_al_shoot` (
  `shoot_msgid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `shoot_mid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `shoot_aid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `shoot_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `shoot_texte` text NOT NULL,
  PRIMARY KEY (`shoot_msgid`),
  KEY `shoot_aid` (`shoot_aid`)
) ENGINE=InnoDB AUTO_INCREMENT=590 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_atq`
-- journal d'attaque (id/ membre1 / membre2 / legion1 / legion2 / type (atq/def) / date / cid: lieu sur la carte / bilan (array serialized))
--

DROP TABLE IF EXISTS `zrd_atq`;
CREATE TABLE IF NOT EXISTS `zrd_atq` (
  `atq_aid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `atq_mid1` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `atq_mid2` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `atq_lid1` int(10) UNSIGNED NOT NULL,
  `atq_lid2` int(10) UNSIGNED NOT NULL,
  `atq_type` tinyint(1) UNSIGNED NOT NULL,
  `atq_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atq_cid` int(10) UNSIGNED NOT NULL,
  `atq_bilan` text NOT NULL,
  PRIMARY KEY (`atq_aid`),
  KEY `atq_mid` (`atq_mid1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_atq_mbr`
-- attaque groupee (id attaque / membre id)
--

DROP TABLE IF EXISTS `zrd_atq_mbr`;
CREATE TABLE IF NOT EXISTS `zrd_atq_mbr` (
  `atq_aid` int(11) NOT NULL,
  `atq_mid` int(11) NOT NULL,
  PRIMARY KEY (`atq_aid`,`atq_mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='lien membres / attaque';

-- --------------------------------------------------------

--
-- Structure de la table `zrd_bon`
-- bonus - achat de ressources (id / membre id / date / code / ok / ressource / nombre)
--

DROP TABLE IF EXISTS `zrd_bon`;
CREATE TABLE IF NOT EXISTS `zrd_bon` (
  `bon_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `bon_mid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `bon_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bon_code` varchar(20) NOT NULL DEFAULT '',
  `bon_ok` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `bon_res_type` smallint(3) UNSIGNED NOT NULL DEFAULT '0',
  `bon_res_nb` int(5) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`bon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_btc`
-- batiments (id / membre id / batiment id / vie / etat)
--

DROP TABLE IF EXISTS `zrd_btc`;
CREATE TABLE IF NOT EXISTS `zrd_btc` (
  `btc_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `btc_mid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `btc_type` smallint(3) UNSIGNED NOT NULL DEFAULT '0',
  `btc_vie` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `btc_etat` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`btc_id`),
  KEY `btc_mid_type` (`btc_mid`,`btc_type`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_cmt`
-- commentaire pour les news - useless
--

DROP TABLE IF EXISTS `zrd_cmt`;
CREATE TABLE IF NOT EXISTS `zrd_cmt` (
  `cmt_cid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cmt_nid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `cmt_mid` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `cmt_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cmt_texte` text NOT NULL,
  `cmt_ip` varchar(50) NOT NULL,
  PRIMARY KEY (`cmt_cid`),
  KEY `cmd_nid` (`cmt_nid`),
  KEY `cmt_mid` (`cmt_mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_con`
-- statistiques ( date / nb de connectes)
--

DROP TABLE IF EXISTS `zrd_con`;
CREATE TABLE IF NOT EXISTS `zrd_con` (
  `con_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `con_nb` int(5) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`con_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_diplo`
-- diplomatie (id / etat du pacte -valide en cours en attente perime etc- / type du pacte / id ally1 / id ally2 / date debut / date fin)
--

DROP TABLE IF EXISTS `zrd_diplo`;
CREATE TABLE IF NOT EXISTS `zrd_diplo` (
  `dpl_did` int(11) NOT NULL AUTO_INCREMENT,
  `dpl_etat` tinyint(4) NOT NULL DEFAULT '0',
  `dpl_type` tinyint(4) NOT NULL DEFAULT '0',
  `dpl_al1` int(11) NOT NULL,
  `dpl_al2` int(11) NOT NULL,
  `dpl_debut` date NOT NULL,
  `dpl_fin` date DEFAULT NULL,
  PRIMARY KEY (`dpl_did`),
  KEY `dpl_al2` (`dpl_al2`),
  KEY `dpl_al1` (`dpl_al1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='diplomatie';

-- --------------------------------------------------------

--
-- Structure de la table `zrd_diplo_shoot`
-- shootbox diplo (id / membre id / diplo pacte id / date du msg / texte)
--

DROP TABLE IF EXISTS `zrd_diplo_shoot`;
CREATE TABLE IF NOT EXISTS `zrd_diplo_shoot` (
  `dpl_shoot_msgid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `dpl_shoot_mid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `dpl_shoot_did` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `dpl_shoot_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dpl_shoot_texte` text NOT NULL,
  PRIMARY KEY (`dpl_shoot_msgid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_frm_bans`
-- ban du forum - useless 
--

DROP TABLE IF EXISTS `zrd_frm_bans`;
CREATE TABLE IF NOT EXISTS `zrd_frm_bans` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(200) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `expire` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_frm_categories`
-- categories du forum (id / nom / position)
-- 1 categorie regroupe plusieurs forums
--

DROP TABLE IF EXISTS `zrd_frm_categories`;
CREATE TABLE IF NOT EXISTS `zrd_frm_categories` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(80) NOT NULL DEFAULT 'New Category',
  `disp_position` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_frm_censoring`
-- censure du forum (useless?)
--

DROP TABLE IF EXISTS `zrd_frm_censoring`;
CREATE TABLE IF NOT EXISTS `zrd_frm_censoring` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `search_for` varchar(60) NOT NULL DEFAULT '',
  `replace_with` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_frm_config`
-- config forum (useless? des variables de parametrage en DB)
--

DROP TABLE IF EXISTS `zrd_frm_config`;
CREATE TABLE IF NOT EXISTS `zrd_frm_config` (
  `conf_name` varchar(255) NOT NULL DEFAULT '',
  `conf_value` text,
  PRIMARY KEY (`conf_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_frm_forums`
-- un forum (id / nom / description / url / modos / nb de sujets /  nb de messages / dernier msg/ id du dernier msg / dernier user / dernier topic - titre / sort by(?) / position du forum / id categorie)
--

DROP TABLE IF EXISTS `zrd_frm_forums`;
CREATE TABLE IF NOT EXISTS `zrd_frm_forums` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `forum_name` varchar(80) NOT NULL DEFAULT 'New forum',
  `forum_desc` text,
  `redirect_url` varchar(100) DEFAULT NULL,
  `moderators` text,
  `num_topics` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `num_posts` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `last_post` int(10) UNSIGNED DEFAULT NULL,
  `last_post_id` int(10) UNSIGNED DEFAULT NULL,
  `last_poster` varchar(200) DEFAULT NULL,
  `last_subject` varchar(255) DEFAULT NULL,
  `sort_by` tinyint(1) NOT NULL DEFAULT '0',
  `disp_position` int(10) NOT NULL DEFAULT '0',
  `cat_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_frm_forum_perms`
-- permissions des forms ( id groupe / id forum / permission lecture/ perm reponse / perm nouveau sujet)
--

DROP TABLE IF EXISTS `zrd_frm_forum_perms`;
CREATE TABLE IF NOT EXISTS `zrd_frm_forum_perms` (
  `group_id` int(10) NOT NULL DEFAULT '0',
  `forum_id` int(10) NOT NULL DEFAULT '0',
  `read_forum` tinyint(1) NOT NULL DEFAULT '1',
  `post_replies` tinyint(1) NOT NULL DEFAULT '1',
  `post_topics` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`group_id`,`forum_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_frm_groups`
-- groupes du forum (id groupe / titres / titre / ... booleen pour les permissions)
--

DROP TABLE IF EXISTS `zrd_frm_groups`;
CREATE TABLE IF NOT EXISTS `zrd_frm_groups` (
  `g_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `g_title` varchar(50) NOT NULL DEFAULT '',
  `g_user_title` varchar(50) DEFAULT NULL,
  `g_read_board` tinyint(1) NOT NULL DEFAULT '1',
  `g_post_replies` tinyint(1) NOT NULL DEFAULT '1',
  `g_post_topics` tinyint(1) NOT NULL DEFAULT '1',
  `g_post_polls` tinyint(1) NOT NULL DEFAULT '1',
  `g_edit_posts` tinyint(1) NOT NULL DEFAULT '1',
  `g_delete_posts` tinyint(1) NOT NULL DEFAULT '1',
  `g_delete_topics` tinyint(1) NOT NULL DEFAULT '1',
  `g_set_title` tinyint(1) NOT NULL DEFAULT '1',
  `g_search` tinyint(1) NOT NULL DEFAULT '1',
  `g_search_users` tinyint(1) NOT NULL DEFAULT '1',
  `g_edit_subjects_interval` smallint(6) NOT NULL DEFAULT '300',
  `g_post_flood` smallint(6) NOT NULL DEFAULT '30',
  `g_search_flood` smallint(6) NOT NULL DEFAULT '30',
  PRIMARY KEY (`g_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_frm_online`
-- useless
--

DROP TABLE IF EXISTS `zrd_frm_online`;
CREATE TABLE IF NOT EXISTS `zrd_frm_online` (
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `ident` varchar(200) NOT NULL DEFAULT '',
  `logged` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `idle` tinyint(1) NOT NULL DEFAULT '0',
  KEY `frm_online_user_id_idx` (`user_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_frm_posts`
-- messages du forum (id / nom membre / id membre / ip membre / mbr email / message / ? / date du msg / edite / nom du modo editeur / id topic)
--

DROP TABLE IF EXISTS `zrd_frm_posts`;
CREATE TABLE IF NOT EXISTS `zrd_frm_posts` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `poster` varchar(200) NOT NULL DEFAULT '',
  `poster_id` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `poster_ip` varchar(15) DEFAULT NULL,
  `poster_email` varchar(50) DEFAULT NULL,
  `message` text,
  `hide_smilies` tinyint(1) NOT NULL DEFAULT '0',
  `posted` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `edited` int(10) UNSIGNED DEFAULT NULL,
  `edited_by` varchar(200) DEFAULT NULL,
  `topic_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `frm_posts_topic_id_idx` (`topic_id`),
  KEY `frm_posts_multi_idx` (`poster_id`,`topic_id`)
) ENGINE=InnoDB AUTO_INCREMENT=327 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_frm_ranks`
-- rang dans le forum (useless)
--

DROP TABLE IF EXISTS `zrd_frm_ranks`;
CREATE TABLE IF NOT EXISTS `zrd_frm_ranks` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rank` varchar(50) NOT NULL DEFAULT '',
  `min_posts` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_frm_reports`
-- signalement du forum (useless?)
--

DROP TABLE IF EXISTS `zrd_frm_reports`;
CREATE TABLE IF NOT EXISTS `zrd_frm_reports` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `topic_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `forum_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `reported_by` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `created` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `message` text,
  `zapped` int(10) UNSIGNED DEFAULT NULL,
  `zapped_by` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `frm_reports_zapped_idx` (`zapped`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_frm_search_cache`
-- recherche en cache
--

DROP TABLE IF EXISTS `zrd_frm_search_cache`;
CREATE TABLE IF NOT EXISTS `zrd_frm_search_cache` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ident` varchar(200) NOT NULL DEFAULT '',
  `search_data` text,
  PRIMARY KEY (`id`),
  KEY `frm_search_cache_ident_idx` (`ident`(8))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_frm_search_matches`
-- lien mots / messages
--

DROP TABLE IF EXISTS `zrd_frm_search_matches`;
CREATE TABLE IF NOT EXISTS `zrd_frm_search_matches` (
  `post_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `word_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `subject_match` tinyint(1) NOT NULL DEFAULT '0',
  KEY `frm_search_matches_word_id_idx` (`word_id`),
  KEY `frm_search_matches_post_id_idx` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_frm_search_words`
-- existence du mot
--

DROP TABLE IF EXISTS `zrd_frm_search_words`;
CREATE TABLE IF NOT EXISTS `zrd_frm_search_words` (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `word` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`word`),
  KEY `frm_search_words_id_idx` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5222 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_frm_topics`
-- sujet du forum (id / nom membre / sujet / date du topic / dernier post / id membre dernier post / nom membre dernier post /  nombre de vues / nb de reponses/ ferme / epingle / deplace / id forum)
--

DROP TABLE IF EXISTS `zrd_frm_topics`;
CREATE TABLE IF NOT EXISTS `zrd_frm_topics` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `poster` varchar(200) NOT NULL DEFAULT '',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `posted` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `last_post` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `last_post_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `last_poster` varchar(200) DEFAULT NULL,
  `num_views` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `num_replies` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `sticky` tinyint(1) NOT NULL DEFAULT '0',
  `moved_to` int(10) UNSIGNED DEFAULT NULL,
  `forum_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `frm_topics_forum_id_idx` (`forum_id`),
  KEY `frm_topics_moved_to_idx` (`moved_to`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_frm_users`
-- membres du forum (doublon avec mbr)
--

DROP TABLE IF EXISTS `zrd_frm_users`;
CREATE TABLE IF NOT EXISTS `zrd_frm_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `group_id` int(10) UNSIGNED NOT NULL DEFAULT '4',
  `login` varchar(200) NOT NULL,
  `password` varchar(40) NOT NULL DEFAULT '',
  `username` varchar(40) DEFAULT NULL,
  `signature` text,
  `disp_topics` tinyint(3) UNSIGNED DEFAULT NULL,
  `disp_posts` tinyint(3) UNSIGNED DEFAULT NULL,
  `show_smilies` tinyint(1) NOT NULL DEFAULT '1',
  `show_img` tinyint(1) NOT NULL DEFAULT '1',
  `show_img_sig` tinyint(1) NOT NULL DEFAULT '1',
  `show_avatars` tinyint(1) NOT NULL DEFAULT '1',
  `show_sig` tinyint(1) NOT NULL DEFAULT '1',
  `timezone` time NOT NULL DEFAULT '00:00:00',
  `language` varchar(25) NOT NULL DEFAULT 'fr_FR',
  `style` varchar(25) NOT NULL DEFAULT 'Classik',
  `num_posts` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `last_post` int(10) UNSIGNED DEFAULT NULL,
  `registration_ip` varchar(15) NOT NULL DEFAULT '0.0.0.0',
  `last_visit` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `alliance` varchar(30) NOT NULL,
  `alliance_id` int(10) UNSIGNED NOT NULL,
  `race` tinyint(1) UNSIGNED NOT NULL,
  `points` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `frm_users_username_idx` (`login`(8))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_hero`
-- hero (id / membre id / nom / quel hero / legion id / xp / vie / bonus active / date activation bonus / date fin bonus)
--

DROP TABLE IF EXISTS `zrd_hero`;
CREATE TABLE IF NOT EXISTS `zrd_hero` (
  `hro_id` int(11) NOT NULL AUTO_INCREMENT,
  `hro_mid` int(11) NOT NULL,
  `hro_nom` varchar(50) NOT NULL,
  `hro_type` tinyint(4) NOT NULL,
  `hro_lid` int(11) NOT NULL,
  `hro_xp` int(11) NOT NULL,
  `hro_vie` int(11) NOT NULL,
  `hro_bonus` tinyint(4) DEFAULT '0',
  `hro_bonus_from` timestamp NULL DEFAULT NULL,
  `hro_bonus_to` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`hro_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_histo`
-- evenements (id / date / id mbr1 / id mbr2 / type d'evenement / texte = array serialized)
--

DROP TABLE IF EXISTS `zrd_histo`;
CREATE TABLE IF NOT EXISTS `zrd_histo` (
  `histo_hid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `histo_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `histo_mid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `histo_mid2` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `histo_type` smallint(2) UNSIGNED NOT NULL DEFAULT '0',
  `histo_vars` text NOT NULL,
  PRIMARY KEY (`histo_hid`),
  KEY `histo_mid` (`histo_mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_leg`
-- legion (id / membre id / map id position de la legion / etat - deplacement retour attente ... / nom / xp / vitesse / destination / tours( pour idle?) / fatigue / stop(?) )
--

DROP TABLE IF EXISTS `zrd_leg`;
CREATE TABLE IF NOT EXISTS `zrd_leg` (
  `leg_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `leg_mid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `leg_cid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `leg_etat` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `leg_name` varchar(40) NOT NULL,
  `leg_xp` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `leg_vit` smallint(3) UNSIGNED NOT NULL DEFAULT '0',
  `leg_dest` int(10) UNSIGNED DEFAULT NULL,
  `leg_tours` smallint(4) UNSIGNED DEFAULT NULL,
  `leg_fat` smallint(3) UNSIGNED DEFAULT NULL,
  `leg_stop` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`leg_id`),
  KEY `leg_cid` (`leg_cid`),
  KEY `leg_mid_etat` (`leg_mid`,`leg_etat`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_leg_res`
-- ressources de la legion = butin (legion id / ressource / nombre)
--

DROP TABLE IF EXISTS `zrd_leg_res`;
CREATE TABLE IF NOT EXISTS `zrd_leg_res` (
  `lres_lid` int(10) UNSIGNED NOT NULL,
  `lres_type` smallint(3) UNSIGNED NOT NULL,
  `lres_nb` int(10) NOT NULL,
  PRIMARY KEY (`lres_lid`,`lres_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_map`
-- carte (id case / coord x / y / climat / type -mer terre montagne village libre / random / region)
-- random est un nombre aléatoire pour utiliser des images différentes pour des cases adjacentes identiques
--

DROP TABLE IF EXISTS `zrd_map`;
CREATE TABLE IF NOT EXISTS `zrd_map` (
  `map_cid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `map_x` smallint(5) NOT NULL DEFAULT '0',
  `map_y` smallint(5) NOT NULL DEFAULT '0',
  `map_climat` tinyint(1) UNSIGNED NOT NULL,
  `map_type` smallint(1) UNSIGNED NOT NULL DEFAULT '0',
  `map_rand` smallint(1) UNSIGNED NOT NULL DEFAULT '1',
  `map_region` tinyint(1) UNSIGNED NOT NULL,
  PRIMARY KEY (`map_cid`),
  KEY `map_x_y` (`map_x`,`map_y`),
  KEY `map_region` (`map_region`)
) ENGINE=InnoDB AUTO_INCREMENT=250001 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_mbr`
-- membre (id / login connexion / pseudo rp / pass / mail / langue / etat - init pause actif / groupe / decalage horaire / race / 
-- id map du village/ place population / population / points / points armee / nb attaque - N jours / date derniere connection / 
-- date modification / date inscription/ derniere ip / signature / description / CSS / nom village/ id mbr parrain / 
-- nb de messages du forum / sexe / votes (?) )
--

DROP TABLE IF EXISTS `zrd_mbr`;
CREATE TABLE IF NOT EXISTS `zrd_mbr` (
  `mbr_mid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mbr_login` varchar(50) NOT NULL,
  `mbr_pseudo` varchar(50) NOT NULL,
  `mbr_pass` varchar(40) NOT NULL DEFAULT '',
  `mbr_mail` varchar(50) NOT NULL DEFAULT '',
  `mbr_lang` char(5) NOT NULL,
  `mbr_etat` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `mbr_gid` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `mbr_decal` time NOT NULL DEFAULT '00:00:00',
  `mbr_race` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `mbr_mapcid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `mbr_place` smallint(4) UNSIGNED DEFAULT NULL,
  `mbr_population` smallint(4) UNSIGNED NOT NULL DEFAULT '0',
  `mbr_points` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `mbr_pts_armee` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `mbr_atq_nb` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `mbr_ldate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `mbr_lmodif_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `mbr_inscr_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mbr_lip` varchar(50) NOT NULL,
  `mbr_sign` text NOT NULL,
  `mbr_descr` text NOT NULL,
  `mbr_design` tinyint(2) UNSIGNED NOT NULL DEFAULT '1',
  `mbr_vlg` varchar(50) NOT NULL,
  `mbr_parrain` int(10) UNSIGNED NOT NULL,
  `mbr_numposts` int(11) NOT NULL DEFAULT '0',
  `mbr_sexe` smallint(6) DEFAULT NULL,
  `mbr_votes` int(11) DEFAULT NULL,
  PRIMARY KEY (`mbr_mid`),
  UNIQUE KEY `mbr_mail` (`mbr_mail`),
  UNIQUE KEY `mbr_pseudo` (`mbr_pseudo`),
  UNIQUE KEY `mbr_login` (`mbr_login`),
  KEY `mbr_lmodif_date` (`mbr_lmodif_date`),
  KEY `mbr_etat` (`mbr_etat`),
  KEY `mbr_race` (`mbr_race`),
  KEY `mbr_parrain` (`mbr_parrain`)
) ENGINE=InnoDB AUTO_INCREMENT=7224 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_mbr_log`
-- log des connexion (id / membre id / ip / date)
--

DROP TABLE IF EXISTS `zrd_mbr_log`;
CREATE TABLE IF NOT EXISTS `zrd_mbr_log` (
  `mlog_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mlog_mid` int(10) UNSIGNED NOT NULL,
  `mlog_ip` varchar(15) NOT NULL,
  `mlog_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`mlog_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1888 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_mbr_old`
-- anciens membres qui ont supprimé leur compte
--

DROP TABLE IF EXISTS `zrd_mbr_old`;
CREATE TABLE IF NOT EXISTS `zrd_mbr_old` (
  `mold_mid` int(10) UNSIGNED NOT NULL,
  `mold_pseudo` varchar(50) NOT NULL,
  `mold_mail` varchar(50) NOT NULL,
  `mold_lip` varchar(50) NOT NULL,
  `mold_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mold_mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_mch`
-- marche (id / membre id / resource / nombre / prix / date debut / etat)
--

DROP TABLE IF EXISTS `zrd_mch`;
CREATE TABLE IF NOT EXISTS `zrd_mch` (
  `mch_cid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mch_mid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `mch_type` smallint(3) UNSIGNED NOT NULL DEFAULT '0',
  `mch_nb` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `mch_prix` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `mch_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `mch_etat` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`mch_cid`),
  KEY `mch_mid` (`mch_mid`),
  KEY `mch_type` (`mch_type`),
  KEY `mch_etat` (`mch_etat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_mch_cours`
-- cours du marche aujourd'hui (resource / prix)
--

DROP TABLE IF EXISTS `zrd_mch_cours`;
CREATE TABLE IF NOT EXISTS `zrd_mch_cours` (
  `mcours_res` smallint(3) NOT NULL DEFAULT '0',
  `mcours_cours` float UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`mcours_res`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_mch_sem`
-- cours du marche semaine (resource/ date / prix)
--

DROP TABLE IF EXISTS `zrd_mch_sem`;
CREATE TABLE IF NOT EXISTS `zrd_mch_sem` (
  `msem_res` smallint(3) UNSIGNED NOT NULL DEFAULT '0',
  `msem_date` date NOT NULL,
  `msem_cours` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`msem_res`,`msem_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_msg_env`
-- message envoyes (id / membre id / id destinataire / ? / date message / sujet / message)
--

DROP TABLE IF EXISTS `zrd_msg_env`;
CREATE TABLE IF NOT EXISTS `zrd_msg_env` (
  `menv_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `menv_mid` int(10) UNSIGNED NOT NULL,
  `menv_to` int(10) UNSIGNED DEFAULT NULL,
  `menv_mrec_id` int(10) UNSIGNED NOT NULL,
  `menv_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `menv_titre` varchar(255) NOT NULL,
  `menv_texte` text NOT NULL,
  PRIMARY KEY (`menv_id`),
  KEY `menv_mid` (`menv_mid`)
) ENGINE=InnoDB AUTO_INCREMENT=346 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_msg_rec`
-- message recu (id / membre id / id expediteur / date message / sujet / message / lu / signalement modo)
--

DROP TABLE IF EXISTS `zrd_msg_rec`;
CREATE TABLE IF NOT EXISTS `zrd_msg_rec` (
  `mrec_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mrec_mid` int(10) UNSIGNED NOT NULL,
  `mrec_from` int(10) UNSIGNED NOT NULL,
  `mrec_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mrec_titre` varchar(255) NOT NULL,
  `mrec_texte` text NOT NULL,
  `mrec_readed` tinyint(1) UNSIGNED NOT NULL,
  `msg_sign` int(11) DEFAULT NULL,
  PRIMARY KEY (`mrec_id`),
  KEY `mrec_mid` (`mrec_mid`)
) ENGINE=InnoDB AUTO_INCREMENT=428 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_ntes`
-- notes (id / mbr id / titre / date / texte / importance)
--

DROP TABLE IF EXISTS `zrd_ntes`;
CREATE TABLE IF NOT EXISTS `zrd_ntes` (
  `nte_nid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nte_mid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `nte_titre` varchar(250) NOT NULL DEFAULT '',
  `nte_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `nte_texte` text NOT NULL,
  `nte_import` enum('0','1','2','3','4') NOT NULL DEFAULT '0',
  PRIMARY KEY (`nte_nid`),
  KEY `nte_mid` (`nte_mid`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_nws`
-- news (useless)
--

DROP TABLE IF EXISTS `zrd_nws`;
CREATE TABLE IF NOT EXISTS `zrd_nws` (
  `nws_nid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nws_mid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `nws_cat` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `nws_etat` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `nws_lang` char(5) NOT NULL,
  `nws_titre` varchar(100) NOT NULL DEFAULT '',
  `nws_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `nws_texte` text NOT NULL,
  `nws_closed` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`nws_nid`),
  KEY `nws_mid` (`nws_mid`),
  KEY `nws_etat` (`nws_etat`),
  KEY `nws_date` (`nws_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_rec`
-- recompenses accordées aux joueurs (distinction differente des grades)
--

DROP TABLE IF EXISTS `zrd_rec`;
CREATE TABLE IF NOT EXISTS `zrd_rec` (
  `rec_mid` int(10) UNSIGNED NOT NULL,
  `rec_type` smallint(3) UNSIGNED NOT NULL,
  `rec_nb` tinyint(2) UNSIGNED NOT NULL,
  PRIMARY KEY (`rec_mid`,`rec_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='RÃ©compenses';

-- --------------------------------------------------------

--
-- Structure de la table `zrd_reg`
-- statistique: nb de races par regions de la carte (region id / race / nb)
--

DROP TABLE IF EXISTS `zrd_reg`;
CREATE TABLE IF NOT EXISTS `zrd_reg` (
  `reg_id` smallint(3) UNSIGNED NOT NULL,
  `reg_race` smallint(2) UNSIGNED NOT NULL,
  `reg_nb` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`reg_id`,`reg_race`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_res`
-- ressources (mbr id / nb resources1 / ... nb resources17)
--

DROP TABLE IF EXISTS `zrd_res`;
CREATE TABLE IF NOT EXISTS `zrd_res` (
  `res_mid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type1` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type2` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type3` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type4` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type5` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type6` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type7` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type8` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type9` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type10` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type11` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type12` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type13` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type14` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type15` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type16` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `res_type17` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`res_mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_res_todo`
-- nb de resources en preparation pour cron (id / mbr id / ressource / nombre)
--

DROP TABLE IF EXISTS `zrd_res_todo`;
CREATE TABLE IF NOT EXISTS `zrd_res_todo` (
  `rtdo_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rtdo_mid` int(10) UNSIGNED NOT NULL,
  `rtdo_type` smallint(3) UNSIGNED NOT NULL,
  `rtdo_nb` smallint(3) UNSIGNED NOT NULL,
  PRIMARY KEY (`rtdo_id`),
  KEY `rtdo_mid_type` (`rtdo_mid`,`rtdo_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_sdg`
-- sondage (id / texte / nb reponses / Date)
--

DROP TABLE IF EXISTS `zrd_sdg`;
CREATE TABLE IF NOT EXISTS `zrd_sdg` (
  `sdg_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sdg_texte` text NOT NULL,
  `sdg_rep_nb` mediumint(6) UNSIGNED NOT NULL,
  `sdg_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`sdg_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_sdg_rep`
-- reponses de sondage (id / sondage id / texte / nombre de reponses)
--

DROP TABLE IF EXISTS `zrd_sdg_rep`;
CREATE TABLE IF NOT EXISTS `zrd_sdg_rep` (
  `srep_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `srep_sid` int(10) UNSIGNED NOT NULL,
  `srep_texte` text NOT NULL,
  `srep_nb` mediumint(6) UNSIGNED NOT NULL,
  PRIMARY KEY (`srep_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_sdg_vte`
-- votes de sondage (sondage id / membre id / reponse id)
--

DROP TABLE IF EXISTS `zrd_sdg_vte`;
CREATE TABLE IF NOT EXISTS `zrd_sdg_vte` (
  `svte_sid` mediumint(6) UNSIGNED NOT NULL,
  `svte_mid` int(10) UNSIGNED NOT NULL,
  `svte_rid` mediumint(6) UNSIGNED NOT NULL,
  PRIMARY KEY (`svte_sid`,`svte_mid`,`svte_rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_ses`
-- liste des users connectes (id / mbr id / ip / derniere page vue / dernier timestamp / random)
--

DROP TABLE IF EXISTS `zrd_ses`;
CREATE TABLE IF NOT EXISTS `zrd_ses` (
  `ses_sesid` varchar(40) NOT NULL DEFAULT '',
  `ses_mid` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `ses_ip` varchar(50) NOT NULL,
  `ses_lact` varchar(15) NOT NULL DEFAULT '0',
  `ses_ldate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ses_rand` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`ses_sesid`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_sign`
-- signalement des mp (id / msg id / id mbr admin concerne / date debut signalement / date fin / commentaire admin / etat)
--

DROP TABLE IF EXISTS `zrd_sign`;
CREATE TABLE IF NOT EXISTS `zrd_sign` (
  `sign_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sign_msgid` int(11) UNSIGNED NOT NULL,
  `sign_admid` int(11) UNSIGNED NOT NULL,
  `sign_debut` datetime NOT NULL,
  `sign_fin` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sign_com` text NOT NULL,
  `sign_etat` int(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`sign_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_src`
-- recherche (mbr id / type recherche)
--

DROP TABLE IF EXISTS `zrd_src`;
CREATE TABLE IF NOT EXISTS `zrd_src` (
  `src_mid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `src_type` smallint(3) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`src_mid`,`src_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_src_todo`
-- recherches en todo list (membre id / type recherche / nb de tours/ date debut)
--

DROP TABLE IF EXISTS `zrd_src_todo`;
CREATE TABLE IF NOT EXISTS `zrd_src_todo` (
  `stdo_mid` int(10) UNSIGNED NOT NULL,
  `stdo_type` smallint(3) UNSIGNED NOT NULL,
  `stdo_tours` smallint(4) UNSIGNED NOT NULL,
  `stdo_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`stdo_mid`,`stdo_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_stq`
-- statistiques (date / nb membres actifs / nb inactifs/ nb connectes)
--

DROP TABLE IF EXISTS `zrd_stq`;
CREATE TABLE IF NOT EXISTS `zrd_stq` (
  `stq_date` date NOT NULL,
  `stq_mbr_act` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `stq_mbr_inac` smallint(4) UNSIGNED NOT NULL DEFAULT '0',
  `stq_mbr_con` smallint(4) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`stq_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_surv`
-- surveillance des membres (id / mbr id / id mbr admin / date debut / etat / type de surveillance / cause - texte)
--

DROP TABLE IF EXISTS `zrd_surv`;
CREATE TABLE IF NOT EXISTS `zrd_surv` (
  `surv_id` int(11) NOT NULL AUTO_INCREMENT,
  `surv_mid` int(11) NOT NULL,
  `surv_admin` int(11) NOT NULL,
  `surv_debut` datetime NOT NULL,
  `surv_etat` int(11) NOT NULL,
  `surv_type` int(11) NOT NULL,
  `surv_cause` varchar(500) NOT NULL,
  PRIMARY KEY (`surv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_trn`
-- terrains (mbr id / nb terrain1 ... nb terrain5)
--

DROP TABLE IF EXISTS `zrd_trn`;
CREATE TABLE IF NOT EXISTS `zrd_trn` (
  `trn_mid` int(10) UNSIGNED NOT NULL,
  `trn_type1` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `trn_type2` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `trn_type3` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `trn_type4` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `trn_type5` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`trn_mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_unt`
-- unités (legion id / type d'unite / rang / nombre)
--

DROP TABLE IF EXISTS `zrd_unt`;
CREATE TABLE IF NOT EXISTS `zrd_unt` (
  `unt_lid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `unt_type` smallint(3) UNSIGNED NOT NULL DEFAULT '0',
  `unt_rang` tinyint(2) UNSIGNED NOT NULL,
  `unt_nb` int(10) NOT NULL,
  PRIMARY KEY (`unt_lid`,`unt_type`,`unt_rang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_unt_todo`
-- unites en preparation (id / mbr id / type unite / nombre)
--

DROP TABLE IF EXISTS `zrd_unt_todo`;
CREATE TABLE IF NOT EXISTS `zrd_unt_todo` (
  `utdo_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `utdo_mid` int(10) UNSIGNED NOT NULL,
  `utdo_type` smallint(3) UNSIGNED NOT NULL,
  `utdo_nb` smallint(3) UNSIGNED NOT NULL,
  PRIMARY KEY (`utdo_id`),
  KEY `utdo_mid_type` (`utdo_mid`,`utdo_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_vld`
-- validation (reset ou suppression de compte) (mbr id / cle random / date action / type action)
--

DROP TABLE IF EXISTS `zrd_vld`;
CREATE TABLE IF NOT EXISTS `zrd_vld` (
  `vld_mid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `vld_rand` varchar(40) NOT NULL DEFAULT '',
  `vld_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `vld_act` varchar(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`vld_mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `zrd_votes`
-- useless
--

DROP TABLE IF EXISTS `zrd_votes`;
CREATE TABLE IF NOT EXISTS `zrd_votes` (
  `votes_mid` int(11) NOT NULL DEFAULT '0',
  `votes_vid` int(11) NOT NULL DEFAULT '0',
  `votes_nb` int(11) NOT NULL DEFAULT '0',
  `votes_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
