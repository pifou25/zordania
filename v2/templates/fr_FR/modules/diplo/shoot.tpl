<if cond="{pacte[dpl_al1]} != {_user[alaid]} && {pacte[dpl_al2]} != {_user[alaid]}">
	<p class="infos">Vous essayez d'espionner une autre coalition!</p>
</if>
<elseif cond=" {pacte[dpl_did]} == 0 || {pacte[dpl_etat]} == DPL_ETAT_FIN">
	<p class="infos">Ce pacte n'existe pas ou plus!</p>
</elseif>
<elseif cond="{pacte[dpl_etat]} == DPL_ETAT_ATT || {pacte[dpl_etat]} == DPL_ETAT_PROP">
	<p class="infos">Votre pacte n'est pas encore accepté!</p>
</elseif>
<elseif cond="{pacte[dpl_etat]} == DPL_ETAT_OK">
	<h3>Discussion commune avec nos alliés: <a href="alliances-view.html?al_aid={pacte[dpl_al]}" title="{pacte[al_name]}">
		<img class="mini_al_logo" alt="{pacte[al_name]}" src="img/al_logo/{pacte[dpl_al]}-thumb.png" />
		{pacte[al_name]}</a></h3>


<if cond="isset({dpl_msg_del})">
		<p class="ok">Message supprimé !</p>
	</if>
	<if cond="isset({dpl_msg_post})">
		<p class="ok">Message posté !</p>
	</if>

	<form class="center" action="diplo-shoot.html?did={pacte[dpl_did]}&amp;sub=post" method="post" id="newpost">
	<include file='commun/bbcode.tpl' cache='1' /><br/>
	<input type="hidden" id="pst_titre" name="pst_titre" value="{pacte[al_name]}" />
	<textarea id="message" name="pst_msg" rows="5" cols="40"></textarea><br />
	<input type="submit" value="Envoyer" />
	<input type="button" id="btpreview" value="Prévisualiser" />
	</form>
	<div id="preview"></div>


	
	<if cond='is_array({pg->get})'>
	<p class="pages">
	<foreach cond="{pg->links} as {page}">
		<if cond='is_numeric({page})'>
		<a href="diplo-shoot.html?did={pacte[dpl_did]}&amp;page={page}">{page}</a>
		</if>
		<else>{page}</else>
	</foreach>
	</p>

		<foreach cond='{pg->get} as {result}'>
			<div class="block" id="{result[dpl_shoot_msgid]}">
			<img class="blason" title="{result[mbr_pseudo]}" src="img/mbr_logo/{result[dpl_shoot_mid]}.png" />
			<h4><zurlmbr mid="{result[dpl_shoot_mid]}" pseudo="{result[mbr_pseudo]}"/> le {result[dpl_shoot_date_formated]}<br/></h4>
			<p>
			{result[dpl_shoot_texte]}
			</p>
			<p class="signature">{result[mbr_sign]}</p>
			<if cond="{result[mbr_mid]} == {_user[mid]}">
				<a href="diplo-shoot.html?did={pacte[dpl_did]}&amp;sub=del&amp;msgid={result[dpl_shoot_msgid]}" title="Supprimer">
					<img src="img/drop.png" alt="Supprimer" title="Supprimer" />
				</a>
			</if>
			</div>
			<br />
		</foreach>
		
	<p class="pages">
	<foreach cond="{pg->links} as {page}">
		<if cond='is_numeric({page})'>
		<a href="diplo-shoot.html?did={pacte[dpl_did]}&amp;page={page}">{page}</a>
		</if>
		<else>{page}</else>
	</foreach>
	</p>
	</if>
</elseif>
