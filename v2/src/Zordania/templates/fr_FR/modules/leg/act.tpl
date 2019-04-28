<div class="menu_module">
	<a href="leg-view.html?lid={leg[leg_id]}" title="Gérer cette légion">Gérer</a>
	<if cond="empty({unt_leg[{leg[leg_id]}]})">
	 - <a href="leg-del.html?lid={leg[leg_id]}" title="Supprimer cette légion">Supprimer</a>
	</if>

	<if cond="{leg[leg_cid]} != {_user[mapcid]}">
		<if cond="empty({unt_leg[{leg[leg_id]}]})">
		- <a href="leg-recup.html?lid={leg[leg_id]}" title="récupérer cette légion">récupérer</a>
		</if>
		<elseif cond="{leg[leg_dest]} != {_user[mapcid]}">
		- <a href="leg-move.html?sub=sou&amp;cid={_user[mapcid]}&amp;lid={leg[leg_id]}" title="ramener la légion au village">rentrer</a>
		</elseif>
	</if>
	<elseif cond='isset({thisetat}) && {thisetat} != Leg::ETAT_VLG'>
		- <a href="leg-view.html?sub=butin&amp;lid={leg[leg_id]}" title="récupérer butin">butin</a>
	</elseif>

	<if cond="{leg[leg_etat]} == Leg::ETAT_POS && !empty({unt_leg[{leg[leg_id]}]})">
		- <a href="war-make_atq.html?lid1={leg[leg_id]}" id="make_atq">attaquer</a>
	</if>
</div>
