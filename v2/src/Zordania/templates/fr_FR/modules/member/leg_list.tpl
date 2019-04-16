<dl>
<foreach cond="{leg_array} as {leg}">
<if cond="!isset({thisetat}) || {leg[leg_etat]} == {thisetat}">
	<dt><if cond="!isset({thisetat})">{leg_etat[{leg[leg_etat]}]} : </if>
		{leg[leg_name]} <if cond='{leg[leg_etat]} == Leg::ETAT_VLG'>(Village)</if>
	</dt>
	<dd>
		<if cond="isset({unt_leg[{leg[leg_id]}]})"><p>
		<foreach cond="{unt_leg[{leg[leg_id]}]} as {type} => {nb}">
			<if cond='{type} != {hro_array[hro_type]}'>{nb} <zimgunt race="{leg_race}" type="{type}" /></if>
		</foreach>
		</p></if>

		<if cond='{hro_array[hro_id]} != 0 || isset({hro_array[leg_id]})'> 
		<if cond='{leg[leg_id]} == {hro_array[leg_id]}'>
		<fieldset><legend>{hro_array[hro_nom]}</legend>
			<p><img src="img/{leg_race}/unt/{hro_array[hro_type]}.png" title="{hro_array[hro_nom]}" />
			<zimgbar per="{hro_array[hro_vie]}" max="{hro_array[hro_vie_conf]}" /> <img src="img/{leg_race}/div/vie.png" alt="Vie" /> {hro_array[hro_vie]} / {hro_array[hro_vie_conf]} </br><zimgnrj per="{hro_array[hro_nrj]}" max="{HEROS_NRJ_MAX}" /> <img src="img/eclair.png" alt="Energie" /> {hro_array[hro_nrj]} / {HEROS_NRJ_MAX} </br></p>
			<if cond='{hro_array[hro_vie]} <= 0'><p class="infos">Votre héros est mort...</p></if>
                        
                        <form action="admin-view.html?module=member&amp;mid={mbr_array[mbr_mid]}" method="post" >
                        <label for="hro_nrj">Editer l'énergie du héros ({hro_array[hro_nrj]}) : </label><input type="text" size="6" value="{hro_array[hro_nrj]}" name="hro_nrj" />
                        <label for="hro_add_nrj">ou augmenter l'énergie de : </label><input type="text" size="6" value="0" name="hro_add_nrj" />
                        <label for="hro_vie">Editer la vie du héros ({hro_array[hro_vie]}) : </label><input type="text" size="6" value="{hro_array[hro_vie]}" name="hro_vie" />
                        <br/>
                        <label for="mbr_xp">Editer XP du joueur ({mbr_array[mbr_xp]}) : </label><input type="text" size="6" value="{mbr_array[mbr_xp]}" name="mbr_xp" />
                        <input type="submit" value="Envoyer" name="submit" />
                        </form>
		</fieldset>
		</if>
		</if>


		<if cond="!empty({res_leg[{leg[leg_id]}]})">
		<p>
			<if cond='{_file}=="admin"'>
			<a href="admin-view.html?module=member&amp;mid={leg[leg_mid]}&amp;leg_res_init={leg[leg_id]}"><img src="img/drop.png" title="Vider ses ressources!"/></a>
			</if>
			<foreach cond="{res_leg[{leg[leg_id]}]} as {type} => {nb}">
				<if cond="{nb}">{nb} <zimgres race="{leg_race}" type="{type}" /></if>
			</foreach>
		</p>
		</if>

	</dd>
</if>
</foreach>
</dl>
