<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<body>

<include file="commun/mail_debut.tpl" cache="1" />
<p><strong>
<if cond="isset({mbr_pseudo})">{mbr_pseudo}</if>
<else>{mbr_mail}</else>
</strong></p>

<p>Info : suite à des changements de serveur, l'adresse actuelle du jeu 
est : 
<a href="{cfg_url}">{cfg_url}</a></p>

<p>Le monde a traversé une période noire, mais personne n'a oublié 
Zordania. Aujourd'hui c'est officiel, Zordania is back! Pour le
 meilleur, et seulement pour le meilleur. Vous vous êtes inscrit 
 sur le jeu il y a longtemps... Aujourd'hui on compte sur vous à 
 nouveau pour venir défendre les vôtres! Il suffit de vous inscrire
 de nouveau :<br/>
<a href="{cfg_url}">{cfg_url}</a>
</p>

<p>
Pour rappel, Zordania c'est 6 races qui combattent pour la 
domination du monde, et parmi les nouveautés c'est aussi un nouveau
 design adapté aux mobiles, des tours d'une durée de 15 minutes. Et
 bientôt de nouveaux graphismes pour tous nos villages et bâtiments.
</p>

<p>
Suivez-nous aussi pour nous encourager sur @FB :
<a href="https://www.facebook.com/zordania2015/">
https://www.facebook.com/zordania2015/</a>
<br/>
Et - nouveau! - sur le chat Discord 
<a href="https://discord.gg/fjGXrkY">https://discord.gg/fjGXrkY</a>
</p>

<p>
Cordialement,
<br/>
<br/>
<include file="commun/mail_fin.tpl" cache="1" />

</p>
</body></html>