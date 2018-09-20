<include file="commun/races.tpl" select="{man_race}" general="0" url="{man_url}" />
<p class="block_forum">
Le manuel parle du site et du système de jeu en général. Pour avoir des informations sur une race en particulier, il suffit de cliquer sur la race puis sur la partie qui vous intéresse.
</p>
<h3>Manuel des <img src="img/{man_race}/{man_race}.png" alt="{race[{man_race}]}" title="{race[{man_race}]}" />  {race[{man_race}]} :</h3>

<if cond='{man_race}'>
	<if cond='isset({man_load})'>
		<load file="race/{man_race}.config" />
		<load file="race/{man_race}.descr.config" />
	</if>
</if>

<if cond='isset({mnl_tpl})'>
<hr />
<include file="{mnl_tpl}" cache="1" />

</if>


