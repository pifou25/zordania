<a name="actbar"></a>
<ul>
    <li>
        <a href="#" id="openmenulat" title="Menu."><img src="img/acts/menu_bg.png" /></a>
    </li>
	<li>
		<a href="vlg.html" title="Gérer le village."><img src="img/acts/vlg{_user[race]}.png" /></a>
	</li>
	<li>	
		<if cond='{_user[msg]} == 1'><a href="gen.html" title="Vous avez {_user[msg]} nouveau message." ><img src="img/acts/gen.gif"/></a></if>
		<elseif cond='{_user[msg]} > 1'><a href="gen.html" title="Vous avez {_user[msg]} nouveaux messages." ><img src="img/acts/gen.gif"/></a></elseif>
		<else><a href="gen.html" title="Informations générales du village."><img src="img/acts/gen.png" /></a></else>
	</li>
    <li>
        <a href="forum.html" title="Forum."><if cond="{_user[new_post]} > 1"><img src="img/acts/codu.gif"/></if><else><img src="img/acts/codu.png" /></else></a>
    </li>
	<if cond='{_user[alaid]} != 0'>
		<li>
			<a href="alliances-my.html" title="Votre Alliance."><img src="img/acts/aly.png" /></a>
		</li>
	</if>
	<elseif cond='{_user[points]} >= {ALL_MIN_PTS}'>
		<li>
			<a href="alliances-my.html" title="Créer votre alliance."><img src="img/acts/aly.png" /></a>
		</li>
	</else>
	<li>
		<a href="leg.html" title="Attaques, Légions."><img src="img/acts/leg.png" /></a>
	</li>
	<li>
		<a href="btc-btc.html" title="Construire des bâtiments."><img src="img/acts/ctr.png" /></a>
	</li>
	<li>
		<foreach cond='{stats_prim_btc[ext]} as {btc_menu_type} => {btc_menu_array}'>
			<foreach cond='{btc_menu_array} as {btc_menu_sub}'>
				<a href="btc-use.html?btc_type={btc_menu_type}&amp;sub={btc_menu_sub}" title="{btcact[{_user[race]}][descr][{btc_menu_type}][{btc_menu_sub}]}"><img src="img/acts/{btc_menu_sub}.png" /></a>
			</foreach>
		</foreach>
	</li>
	<li>
		<a href="carte.html" title="Carte de Zordania."><img src="img/acts/map.png" /></a>
	</li>
	<li>
		<a href="unt.html" title="Voir la population."><img src="img/acts/unt{_user[race]}.png" /></a>
	</li>
	<li>
		<a href="res.html" title="Voir les ressources." class="zrdPopUp"><img src="img/acts/res.png" /></a>
	</li>
	<if cond='{ses_admin} OR {_user[groupe]} == {GRP_PRETRE}'><!-- link forum staff -->
		<li>
			<a href="forum.html?cid=3#cid3" title="Forum Staff."><img src="img/acts/codu.png" /></a>
		</li>
	</if>
	<if cond='{ses_admin}'>		
		<li>
			<if cond='{ses_adm_msg}'>
			<a href="admin.html?module=msg" title="Signalement de messages!"><img src="img/acts/adm_hi.png" /></a>
			</if><else>
			<a href="admin.html" title="Administration."><img src="img/acts/adm.png" /></a>
			</else>
		</li>
	</if>
</ul>
