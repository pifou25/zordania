<div class="menu_module">
	<a href="war-histo.html" title="Historique de vos attaques, et de celles de vos ennemis.">Journal&nbsp;de&nbsp;guerre</a> - 
	<a href="leg.html" title="Créer, Modifier, Déplacer vos Légions">Gestion&nbsp;des&nbsp;Légions</a> -
	<a href="leg-hero.html" title="Gérer les compétences de votre héro">Gestion&nbsp;du&nbsp;Héros</a>
	<hr />
</div>
<if cond='isset({err})'><p class="error">
	<if cond='{err} == "imm_cause_comp"'>Votre héros et sa légion sont immobilisés par la compétence en cours :
	<a href="manual.html?race={_user[race]}&amp;type=comp#comp_{_user[bonus]}"><zimgcomp race="{_user[race]}" type="{_user[bonus]}" /> {comp[{_user[race]}][alt][{_user[bonus]}]}</a></if>
	<if cond='{err} == "ren_leg_name_exists"'>Une légion existe déjà avec ce nom, ou similaire : {ren_leg_name}</if>
	<if cond='{err} == "leg_bad_lid"'>Cette légion ne vous appartient pas !</if>
	<if cond='{err} == "leg_no_empty"'>Cette légion n'est pas vide !</if>
	</p>
</if>

        <if cond="isset({error})">
<load file="config/errors.properties" />
<if cond="isset({err[{error}]})">
    <p class="error">{err[{error}]}</p>
</if>
<else><p class="error">Undocumented Error : {error}</p></else>
        </if>
        
<if cond='{_act} == "unit1"'>
    <if cond="isset({legs[{lid}]}) && isset({unit})">

    <p>Déplacer des unités de {legs[{lid}]->leg_name} :
        <form method="post" action="leg-unit2.html">
        <fieldset>
            <legend>Gestion des unités du village</legend>
            <label for="unt_nb">max {unit->unt_nb} <zimgunt race="{_user[race]}" type="{unit->unt_type}" /></label>
            <input type="number" name="unt_nb" size="5" min="0" max="{unit->unt_nb}" />
            <input type="hidden" name="from_leg" value="{lid}" />
            <input type="hidden" name="unt_type" value="{unit->unt_type}" />
            <label for="to_leg">Ajouter dans</label>
            <select name="to_leg">
                <foreach cond="{legs} as {leg}">
                    <if cond='{leg->leg_etat} != Leg::ETAT_BTC && {leg->leg_id} != {lid}'>
                        <option value="{leg->leg_id}">{leg->leg_name}</option>
                    </if>
                </foreach>
            </select>
            <input type="submit" value="Déplacer" />
        </fieldset>
        </form>
    </p>

    </if>
</if>

                        <if cond='{_act} == "unit2" && isset({confirm})'><foreach cond="{confirm} as {key} => {value}">
<p class="ok">Déplacement effectué : {value} <zimgunt race="{_user[race]}" type="{key}" </p>
                        </foreach></if>

<if cond='!{_act} || {_act} =="del" ||  {_act} =="recup" || {_act} == "edit" || {_act} == "etat" || {_act} =="move" || {_act} =="new"'>
	<if cond='{_act} == "del"'>
		<if cond="isset({leg_need_ok})">
			<div class="infos">
			Voulez-vous vraiment supprimer cette légion ?
			<form method="post" action="leg-del.html?lid={leg_lid}">
			<input name="ok" type="submit" value="oui" />
			</form>
			</div>
		</if>
		<else>
			<div class="ok">Légion supprimée !</div>
		</else>
	</if>

	<else>
		<if cond="isset({leg_new})"><p class="ok">Nouvelle légion créée ! {ren_leg_name}</p></if>
		<if cond='{_act} == "recup" and !isset({err})'><p class="ok">Légion retournée au village. La légion a perdu la moitié de ses ressources.</p></if>

		<if cond="isset({leg_bad_lid})">
			<div class="error">Cette légion ne peut pas se déplacer ici !</div>
		</if>
		<elseif cond="isset({leg_invincible})">
			<div class="error">La légion est clouée au village par une force divine, elle ne peut pas se déplacer !</div>
		</elseif>
		<elseif cond="isset({leg_no_unt_vlg})">
			<div class="error">Déplacer {action[nb]} <zimgunt race="{_user[race]}" type="{action[type]}" /> : Vous n'avez pas assez d'unités !</div>
		</elseif>
		<elseif cond="isset({leg_cant_add_unt})">
			<div class="error">Impossible de déplacer ces unités ici !</div>
		</elseif>
		<elseif cond="isset({leg_ok})">
			<div class="ok">{action[nb]} <zimgunt race="{_user[race]}" type="{action[type]}" /> : unités déplacées.</div>
		</elseif>
		<elseif cond="isset({leg_err_leg})">
			<div class="error">Cette légion est invalide pour cette action !</div>
		</elseif>
		<elseif cond="isset({leg_move_psub})">
			<div class="error">Problème dans le lien. A signaler dans le forum.</div>
		</elseif>
		<elseif cond="isset({leg_move_pmbr})">
			<div class="error">il n'y a pas de village à cette destination.</div>
		</elseif>
		<elseif cond="isset({leg_move_patq})">
			<div class="error">Vous ne pouvez pas attaquer ce membre.</div>
		</elseif>
		<elseif cond="isset({leg_move_psou})">
			<div class="error">Ce membre n'est pas votre allié.</div>
		</elseif>
		<elseif cond="isset({leg_move_ok})">
			<div class="ok">Légion envoyée !</div>
		</elseif>
		<elseif cond="isset({leg_no_tele})">
			<div class="error">La compétence requise pour cette action n'est pas active !</div>
		</elseif>
		<elseif cond="isset({leg_tele_ok})">
			<div class="ok">Le héros et sa légion se sont téléporté instantannément !</div>
		</elseif>
	</else>

	<if cond='{_act} =="move" && isset({show_form}) && {show_form}'><# déplacement des légions #>
		<if cond="isset({map_array}) && {map_array} ">
			<h3>Destination</h3>
			<set name="result" value="{map_array}" />
			<include file="modules/carte/tile.tpl" cache="1" />
			<h3>Déplacer une légion</h3>
			<if cond='empty({legs})'>
				<p class="error">Aucune légion disponible !</p>
			</if>
			<else>
				<form method="post" action="leg-move.html?sub={_sub}&amp;cid={map_cid}">
			</else>
		</if>
	</if>
	<else><# formulaire gestion des légions #>
		<if cond="count({legs}) - 1 < {LEG_MAX_NB}">
			<form method="post" action="leg-new.html">
				<fieldset>
					<legend>Nouvelle légion</legend>
					<label for="leg_name">Nom :</label>
					<input type="text" name="name" id="leg_name" />
					<input type="submit" value="Créer" />
				</fieldset>
			</form>
		</if>
                <!-- fin formulaire de gestion -->
	</else>
	<if cond='!empty({legs}) '>

		<if cond='!({_act} == "move" && isset({show_form}) && {show_form})'>
			<include file="modules/leg/list.tpl" cache="1" thisetat="Leg::ETAT_VLG" />
			<hr/>
		</if>

		<h3>{leg_etat[Leg::ETAT_GRN]}</h3>
		<# include avec paramètre 'thisetat' #>
		<include file="modules/leg/list.tpl" cache="1" thisetat="Leg::ETAT_GRN" />
		
		<if cond='! ({_act} == "move" && isset({show_form}) && {show_form} && {_sub} == "atq" )'>
			<hr/>
			<h3>{leg_etat[Leg::ETAT_POS]}</h3>
			<include file="modules/leg/list.tpl" cache="1" thisetat="Leg::ETAT_POS" />

			<hr/>
			<h3>{leg_etat[Leg::ETAT_DPL]}</h3>
			<include file="modules/leg/list.tpl" cache="1" thisetat="Leg::ETAT_DPL" />
			
			<hr/>
			<h3>{leg_etat[Leg::ETAT_ALL]}</h3>
			<include file="modules/leg/list.tpl" cache="1" thisetat="Leg::ETAT_ALL" />

			<if cond='!({_act} == "move" && isset({show_form}) && {show_form})'>
				<hr/>
				<h3>{leg_etat[Leg::ETAT_RET]}</h3>
				<include file="modules/leg/list.tpl" cache="1" thisetat="Leg::ETAT_RET" />

				<hr/>
				<h3>{leg_etat[Leg::ETAT_ATQ]}</h3>
				<include file="modules/leg/list.tpl" cache="1" thisetat="Leg::ETAT_ATQ" />
			</if>
		</if>

		<if cond='{_act} =="move" && !empty({legs}) && isset({show_form}) && {show_form}'>
			<input type="submit" value="Envoyer" />
			</form>
		</if>
	</if>
	
</if>
<elseif cond='{_act} =="view"'>
	<if cond="isset({leg_bad_lid})">
		<div class="error">Cette légion n'existe pas !</div>
	</if>
	<else>
		<include file="modules/leg/view.tpl" cache="1" />
	</else>
</elseif>
<elseif cond='{_act} == "hero" || {_act} == "bns"'>
	<include file="modules/leg/hero.tpl" cache="1" />
</elseif>
