<if cond='isset({btc_tpl}) || {btc_act} == "list2" || {_display}=="ajax"'>

	<p class="menu_module">
		<if cond="isset({btc_conf[prod_unt]})">
			<a href="btc-use.html?btc_type={btc_id}&amp;sub=unt" title="Unités" class="zrdPopUp">{btcopt[{_user[race]}][{btc_id}][unt]}</a>
		</if>
		<if cond="isset({btc_conf[prod_src]})">
			<a href="btc-use.html?btc_type={btc_id}&amp;sub=src" title="Recherches" class="zrdPopUp">{btcopt[{_user[race]}][{btc_id}][src]}</a>
		</if>
		<if cond="isset({btc_conf[prod_res]})">
			<a href="btc-use.html?btc_type={btc_id}&amp;sub=res" title="Ressources" class="zrdPopUp">{btcopt[{_user[race]}][{btc_id}][res]}</a>
		</if>
		<if cond="isset({btc_conf[com]})">
			<a href="btc-use.html?btc_type={btc_id}&amp;sub=my" title="Ventes" class="zrdPopUp">Vos Ventes</a> 
			<a href="btc-use.html?btc_type={btc_id}&amp;sub=ven" title="Vendre" class="zrdPopUp">Vendre</a> 
			<a href="btc-use.html?btc_type={btc_id}&amp;sub=ach" title="Acheter" class="zrdPopUp">Acheter</a> 
			<a href="btc-use.html?btc_type={btc_id}&amp;sub=cours" title="Cours moyens" class="zrdPopUp">Cours</a>
			<a href="btc-use.html?btc_type={btc_id}&amp;sub=cours_sem" title="Cours sur la semaine" class="zrdPopUp">Cours de la Semaine</a>
		</if>
		<a href="btc-use.html?btc_type={btc_id}" title="Infos" class="zrdPopUp">Infos</a>
		<if cond="isset({btc_id})">
			<a href="btc-use.html?btc_type={btc_id}&amp;sub=list" title="Liste des bâtiments" class="zrdPopUp">Liste</a>
		</if>
		<a href="btc-use.html?sub=list" title="Liste des bâtiments" class="zrdPopUp">Liste&nbsp;complète</a>
	</p>

</if>


<if cond='!isset({btc_act}) || {btc_act} == "list"'>
	<script type="text/javascript">
		var fortif = 0;
	</script>

<if cond="isset({sv_site_debug})">
<style>
#village .btc{
    border: 1px solid #FFF;
}
#village .btc:hover{
    border: 1px solid #F00;
}
</style>
</if>

	<div id="village">
		<foreach cond='{src_array} as {src_vars}'>
			<if cond="isset({src_conf[{src_vars[src_type]}][vlg]})">
				<zimgsrc race="{_user[race]}" type="{src_vars[src_type]}" class="btc" id="src_{src_vars[src_type]}" />
			</if>
		</foreach>

		<foreach cond='{btc_array} as {btc_vars}'>
			<if cond="{btc_vars[btc_type]} == {btc_max}">
				<script type="text/javascript">fortif = 1;</script>
			</if>
			<else>
			<a href="btc-use.html?btc_type={btc_vars[btc_type]}" class="zrdPopUp" title="{btc[{_user[race]}][alt][{btc_vars[btc_type]}]}"><zimgbtc race="{_user[race]}" type="{btc_vars[btc_type]}" class="btc" id="btc_{btc_vars[btc_type]}" /></a>
			</else>
		</foreach>
	</div>
	<script type="text/javascript" src="js/vlg.js"></script>
	<script type="text/javascript">
		Vlg.init({_user[race]}, fortif);
	</script>
</if>
<elseif cond='{btc_act} == "list2"'>

	<form action="alert('javascript!');" method="post" id="form_btc">
	<dl>
	<foreach cond='{btc_ar1} as {etat} => {btc_array}'>
		<dt>{btc_etat[{etat}]}</dt>
		<foreach cond='{btc_array} as {btc_vars}'>

			<set name="btc_vie" value="{btc_conf[vie]}" />
			<set name="btc_bid" value="{btc_vars[btc_id]}" />
			<dd>

				<zimgba2 per="{btc_vars[btc_vie]}" max="{btc_vie}" />

				<label for="btc{btc_bid}">
					<if cond="!isset({btc_id})">
						<zimgbtc race="{_user[race]}" type="{btc_vars[btc_type]}" />
						{btc[{_user[race]}][alt][{btc_vars[btc_type]}]} 
					</if>
					<input type="checkbox" name="bid[{btc_bid}]" id="btc{btc_bid}">
					Solidité : <math oper='round(({btc_vars[btc_vie]} / ({btc_vie})*100))' /> % | <em>{btc_vars[btc_vie]}/{btc_vie}</em>
				</label>

				<a href="btc-use.html?btc_type={btc_vars[btc_type]}" title="{btc[{_user[race]}][alt][{btc_vars[btc_type]}]}">Liste
				</a> - <a href="btc-use.html?btc_bid={btc_bid}&amp;sub=det" title="Détruire le Bâtiment et récupérer la moitié des ressources" class="zrdPopUp">Détruire</a>
				<if cond="{btc_vars[btc_etat]} == {BTC_ETAT_OK}">
					- <a href="btc-use.html?btc_bid={btc_bid}&amp;sub=des" title="Désactiver le Bâtiment">Désactiver</a>
				</if>
				<elseif cond="{btc_vars[btc_etat]} == {BTC_ETAT_DES} || {btc_vars[btc_etat]} == {BTC_ETAT_REP}">
					- <a href="btc-use.html?btc_bid={btc_bid}&amp;sub=act" title="Activer le Bâtiment">Activer</a>
				</elseif>
				<if cond="{btc_vars[btc_vie]} - {btc_vie} != 0 AND {btc_vars[btc_etat]} != {BTC_ETAT_REP}">
					- <a href="btc-use.html?btc_bid={btc_bid}&amp;sub=rep" title="Réparer le Bâtiment (le rend inutilisable durant la réparation)">Réparer</a>
				</if>

			</dd>
		</foreach>
	</foreach>
	</dl>

	<!-- // URL détruire: sub = det
	désactiver : sub = des
	réparer : sub = rep
	activer : sub = act
	 -->
	<script type="text/javascript">
	function post_btc(act)
	{
		if(act=='det')
			if(confirm('Êtes vous sûr de vouloir supprimer un ou plusieurs bâtiments?')==false)
				return false;
		document.forms['form_btc'].action = "btc-use.html?sub="+act;
		document.forms['form_btc'].submit();
		return false;
	}
	</script>

	<input type="hidden" name="ok" value="oui" />
	[ Pour la sélection : <a href="#" onclick="post_btc('det');">Détruire</a> -
	<a href="#" onclick="post_btc('des');">Désactiver</a> -
	<a href="#" onclick="post_btc('rep');">Réparer</a> -
	<a href="#" onclick="post_btc('act');">Activer</a> ]
	</form>
</elseif>
<elseif cond='{btc_act} == "det"'>
	<if cond='isset({btc_no_bid})'>
			<p class="error">Aucun bâtiment sélectionné.</p>
	</if>
	<elseif cond='{btc_ok}'>
		<if cond='{btc_det_ok}'>
			<p class="ok">Ok, Bâtiment détruit.</p>
		</if>
		<else>
			<p class="error">Ce bâtiment n'existe pas ou ne peut pas être détruit.</p>
		</else>
	</elseif>
	<else>
		Êtes-vous sûr de vouloir détruire ce bâtiment ?
		<form action="btc-use.html?sub=det" method="post">
			<foreach cond="{btc_bid} as {val} => {validation}">
				<input type="hidden" name="bid[{val}]" value="on" />
			</foreach>
			<input type="submit" name="ok" value="Oui" />
		</form>
	</else>
</elseif>
<elseif cond='{btc_act} == "mod_etat"'>
	<if cond='isset({btc_no_bid})'>
		<p class="error">Aucun bâtiment sélectionné.</p>
	</if>
	<elseif cond='{btc_mod_etat}'>
		<if cond='{btc_mod_etat} < 0'>
		    <p class="error">Impossible de désactiver un bâtiment qui donne un bonus.</p>
		</if>
		<else>
		    <p class="ok">Ok, action effectuée.</p>
		</else>
	</elseif>
	<else>
		<p class="error">Ce Bâtiment n'existe pas.</p>
	</else>
</elseif>
<elseif cond='{btc_act} == "no_btc"'>
	<br />
	<p class="error">Vous ne possédez pas encore le bâtiment pour effectuer cette action ({btc[{_user[race]}][alt][{btc_id}]}).
		<br/>Il est aussi possible que ce bâtiment soit en réparation ou inactif.</p>
</elseif>
<if cond='isset({btc_tpl})'>

	<if cond='!isset({btc_conf[prod_unt]}) && !isset({btc_conf[prod_src]}) && !isset({btc_conf[prod_res]}) && !isset({btc_conf[com]}) && {btc_act}!="infos"'>
		<h3><zimgbtc race="{_user[race]}" type="{btc_id}" /> {btc[{_user[race]}][alt][{btc_id}]}</h3>
		{btc[{_user[race]}][descr][{btc_id}]}
		<include file="modules/btc/inc/info.tpl" cache="1" />
	</if>

	<include file="{btc_tpl}" cache="1" />
	<if cond="isset({btc_conf[prod_unt]})">
		<include file="modules/btc/inc/unt.tpl" cache="1" />
	</if>
	<if cond="isset({btc_conf[prod_src]})">
		<include file="modules/btc/inc/src.tpl" cache="1" />
	</if>
	<if cond="isset({btc_conf[prod_res]})">
		<include file="modules/btc/inc/res.tpl" cache="1" />
	</if>
	<if cond="isset({btc_conf[com]})">
		<include file="modules/btc/inc/com.tpl" cache="1" />
	</if>
	<if cond='{btc_act} == "infos"'>
		<include file="modules/btc/inc/info.tpl" cache="1" />
	</if>
</if>

<p class="retour_module">
	<if cond='{_display}=="ajax"'>
		<# do not display this link to close the jquery popup #>
		<# a href="#" title="Fermer" onclick="$('#dialog-modal').hide();">Fermer</a #>
	</if>
	<else>
		<if cond="!isset({btc_id})">
			<a href="btc-use.html?sub=list" title="Liste complète de tous les bâtiments">Liste&nbsp;complète</a>
		</if>
		<else>
			<a href="btc-use.html?btc_type={btc_id}&amp;sub=list" title="Liste complète des bâtiments de ce type">Liste</a>
		</else>
		<a href="vlg.html" title="Vue générale">Village</a>
	</else>
</p>
