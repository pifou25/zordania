<h4>Arbre technologiques :</h1>
C'est le manuel mis en condensé, repris sous forme d'arbre de développement pour comprendre de manière visuelle les éléments qui débloquent les suivants.
<if cond='{man_race}'>
	<if cond='isset({man_load})'>
		<load file="race/{man_race}.config" />
		<load file="race/{man_race}.descr.config" />
	</if>
</if>

<if cond='isset({mnl_tpl})'>

</if>

<p align="center" class="menu_module">
<a href="manual.html?race={man_race}&page=9">Précédent : Titres et Récompenses</a>
<a href="manual.html?race={man_race}&page=0" title="Accueil du Manuel">Manuel</a>
</p>