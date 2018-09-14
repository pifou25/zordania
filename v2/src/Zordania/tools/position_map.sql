
-- liste des terrains de type village (7) qui n'ont pas de lien dans la table des membres
SELECT * FROM zrd_map
WHERE map_type=7 
AND map_cid NOT IN (SELECT mbr_mapcid FROM zrd_mbr)


-- 1: déplacer d'abord les légions au village de 47x167 en 47x168
UPDATE zrd_leg SET leg_cid=(SELECT map_cid FROM zrd_map WHERE map_x=47 AND map_y=168)
WHERE leg_mid=(
  SELECT mbr_mid 
  FROM zrd_mbr INNER JOIN zrd_map ON mbr_mapcid=map_cid 
  WHERE map_x=47 AND map_y=167)
AND leg_cid=(SELECT map_cid FROM zrd_map WHERE map_x=47 AND map_y=167);
-- 1 bis: déplacer ensuite le membre
UPDATE zrd_mbr SET mbr_mapcid=(SELECT map_cid FROM zrd_map WHERE map_x=47 AND map_y=168)
WHERE mbr_mapcid=(SELECT map_cid FROM zrd_map WHERE map_x=47 AND map_y=167);
-- 2: initialiser la nouvelle case à l'état village
UPDATE zrd_map SET map_type=7 WHERE map_x=47 AND map_y=168;
-- 3: réinitialiser l'ancien emplacement à l'état vide
UPDATE zrd_map SET map_type=5 WHERE map_x=47 AND map_y=167;


-- update stats du forum
update zrd_frm_forums f 
inner join 
 (select num_topics, num_posts, p2.last_post, t1.posted, t1.poster, subject , forum_id
  from zrd_frm_posts t1 inner join zrd_frm_topics p1 on t1.topic_id = p1.id
  inner join (
    select count(id) as num_topics, sum(num_replies)+count(id) as num_posts, max(last_post_id) as last_post
    from zrd_frm_topics  group by forum_id) p2 on t1.id = p2.last_post
) p3 on f.id = p3.forum_id
set
  f.num_topics = p3.num_topics,
  f.num_posts = p3.num_posts,
  f.last_post = p3.posted,
  f.last_post_id = p3.last_post,
  f.last_poster = p3.poster,
  f.last_subject = p3.subject;
-- si besoin de vider un forum vide... passer cette requete en 1er 
update zrd_frm_forums f 
set
  f.num_topics = 0,  f.num_posts = 0,  f.last_post = 0, f.last_post_id = 0,
  f.last_poster = '(anonyme)',  f.last_subject = '(vide)';
where not exists (select 1 from zrd_frm_topics where forum_id = id);