<p class="menu_module">
<a href="qst.html" title="Quetes">Quetes</a>
- <a href="qst-edit.html?qid=0&race=1" title="Ajouter une Quête">Nouvelle Quête</a>
</p>

		
<if cond='{qst_act} == "edit"'>
    
    <p class="infos">Si nouvelle quête, pour ne pas perdre les données:<br/> 
        1) Choisir la race<br/> 
        2) Choisir le paramètre pour l'objectif<br/> 
        3) Définir un objectif et un nombre<br/>
        4) Faut-il une quête avant celle la?<br/> 
        5) Choisir les récompenses et la quantité<br/> 
        6) Remplir titre et description<br/>  
        7) c/c vos textes si vous voulez changer de race ou d'objectif!!!<br/> 
    </p>
        <br/> 
    
	<if cond="isset({qst_bad_qid})">
		<p class="error">Quête Inexistante !</p>
	</if>
	<else>
		<if cond="isset({qst_ok})">
			<p class="ok">Quête enregistrée !</p>
		</if>
    <form action="qst-edit.html?qid={qst_qid}&race={raceid}&param={param}" method="post" id="newpost">
        Race:
        <select name="race" onChange="location.href=''+this.options[this.selectedIndex].value+'';">
            <option value="">Race</option>
            <foreach cond="{_races} as {race_id} =>  {value}">
                <if cond="{value} === true">
                <option value="qst-edit.html?qid={qst_qid}&race={race_id}&param={param}" <if cond="{race_id} == {raceid}">selected</if>>{race[{race_id}]}</option>
                </if>
            </foreach>
        </select>
        <br/>
        <br/>  
    
        <load file="race/{raceid}.config" />
        
        Objectif:<br/>  
        <select name="qst_param_1" id="qst_param_1" onChange="location.href=''+this.options[this.selectedIndex].value+'';">
            <option value="">Paramètre</option>
            <option value="qst-edit.html?qid={qst_qid}&race={raceid}&param=btc" <if cond="{param} == "btc"">selected</if>>Bâtiments</option>
            <option value="qst-edit.html?qid={qst_qid}&race={raceid}&param=unt" <if cond="{param} == "unt"">selected</if>>Unités</option>
            <option value="qst-edit.html?qid={qst_qid}&race={raceid}&param=res" <if cond="{param} == "res"">selected</if>>Ressources</option>
        </select>

        <if cond="{param_ok} == true">
            <select name="obj_1" id="obj_1">
                <option value="">Objectif</option>
                <foreach cond="{param_obj} as {obj_id} => {obj_value}">
                    <if cond="{value} === true">
                    <option value="{obj_id}" <if cond="{obj_id} == {obj_1}">selected</if>>{{param}[{raceid}][alt][{obj_id}]}</option>
                    </if>
                </foreach>
            </select>            
            <input name="obj_val1" id="obj_val1" type="Number" value="{obj_val1}" placeholder="Valeur" />
            <br/> 
            <select name="req_qid" id="req_qid">
                <option value="">Quête requise</option>
                <option value=0>NON</option>
                <if cond="{qst_array}">
                    <foreach cond="{qst_array} as {quest}">
                      <option value="{quest[qst_id]}" <if cond="{quest[qst_id]} == {req_qid}">selected</if>>[{race[{quest[qst_race]}]}] {quest[qst_title]}</option>
                    </foreach>
                </if>
            </select>
            <br/> 
            <br/> 

            Récompenses:<br/> 
            <select name="rec_1" id="rec_1">
                <option value="">res</option>
                <foreach cond="{man_res} as {res_id} => {res_value}">
                    <if cond="{value} === true">
                    <option value="{res_id}" <if cond="{res_id} == {rec_1}">selected</if>>{res[{raceid}][alt][{res_id}]}</option>
                    </if>
                </foreach>
            </select>
            <input name="rec_val1" id="rec_val1" type="Number" value="{rec_val1}" placeholder="Valeur 1" />
            <br/>

            <select name="rec_2" id="rec_2">
                <option value="">res</option>
                <foreach cond="{man_res} as {res_id} => {res_value}">
                    <if cond="{value} === true">
                    <option value="{res_id}" <if cond="{res_id} == {rec_2}">selected</if>>{res[{raceid}][alt][{res_id}]}</option>
                    </if>
                </foreach>
            </select>
            <input name="rec_val2" id="rec_val2" type="Number" value="{rec_val2}" placeholder="Valeur 2" />
            <br/>
            Expérience: <input name="rec_xp" id="rec_xp" type="Number" value="{rec_xp}" placeholder="Valeur xp" />
            <br/> 
            <br/>  
            <br/>  
            <label for="pst_titre">Titre de la quête:</label> <input type="text" id="pst_titre" name="pst_titre" value="{pst_titre}" />

            <br/>   
            <br/>
            Description de la quête:
            <include file='commun/bbcode.tpl' cache='1' /><br/>
            <textarea id="message" cols="60" rows="11" name="pst_msg">{pst_msg}</textarea> 
            <br/> 


            <select name="qst_statut" >
                <option value="{QST_CFG_OFF}" <if cond="{qst_statut} == {QST_CFG_OFF}">selected</if>>Hors ligne</option> 
                <option value="{QST_CFG_ON}" <if cond="{qst_statut} == {QST_CFG_ON}">selected</if>>En ligne</option>
            </select> 

            <input type="submit" value="Enregistrer" />
            <input type="button" id="btpreview" value="Prévisualiser" />

         </if>
    </form>

    <div id="preview"></div>
	</else>
</if>
<elseif cond='{qst_act} == "del"'>
	<if cond="isset({qst_ok})">
		<p class="ok">Quête(s) Supprimée(s) !</p>
	</if>
	<elseif cond="isset({qst_bad_qid})">
		<p class="error">Quête Inexistante !</p>
	</elseif>
</elseif>
<elseif cond='{qst_act} == "view"'>
<if cond="{qst_array}">
    <load file="race/{qst_array[qst_race]}.config" />
	<h4>Titre: {qst_array[qst_title]}</h3>
	Date: Le {qst_array[qst_date_formated]}<br/>
	Actions:
	<a href="qst-edit.html?qid={qst_array[qst_id]}&race={qst_array[qst_race]}&param={qst_array[qst_param_1]}"><img src="img/editer.png" alt="Editer" title="Editer" /></a>
	-
	<a href="qst-del.html?qid={qst_array[qst_id]}"><img src="img/drop.png" alt="Supprimer" title="Supprimer" /></a><br/>
    Race: <img src="img/{qst_array[qst_race]}/{qst_array[qst_race]}.png" title="{race[{qst_array[qst_race]}]}" /><br/>
	Quête précédente: {qst_array[qst_req_qid]}<br/>
	
	Objectif:  {qst_array[qst_obj_val1]}x <img src="img/{qst_array[qst_race]}/{qst_array[qst_param_1]}/{qst_array[qst_obj_1]}.png" title="{{qst_array[qst_param_1]}[{qst_array[qst_race]}][alt][{qst_array[qst_obj_1]}]}" />
    <br/> 
    <br/>
    
    Récompenses: 
        {qst_array[qst_recomp_val1]}x <img src="img/{qst_array[qst_race]}/res/{qst_array[qst_recomp_cat1]}.png" title="{res[{qst_array[qst_race]}][alt][{qst_array[qst_recomp_cat1]}]}" /> - {qst_array[qst_recomp_val2]}x <img src="img/{qst_array[qst_race]}/res/{qst_array[qst_recomp_cat2]}.png" title="{res[{qst_array[qst_race]}][alt][{qst_array[qst_recomp_cat2]}]}" />
    
    <br/>
    <br/>
    Décription:
	<div class="block">
	{qst_array[qst_descr]}
	</div>
    
</if>
</elseif>
<else>
		<if cond="{qst_array}">
			<table class="liste">
				<tr>
				<th>En ligne</th>
				<th>Titre</th>
				<th>Date</th>
				<th>Importance</th>
				<th>Actions</th>
				</tr>
			<foreach cond="{qst_array} as {result}">
				<tr>
				<td><if cond="{result[qst_statut]} == QST_CFG_ON"><img src="img/forum/3.png" title="En ligne"/></if><else><img src="img/forum/2.png" title="Hors ligne"/></else></td>
				<td><a href="qst-view.html?qid={result[qst_id]}" title="Voir '{result[qst_title]}'">{result[qst_title]}</td>
				<td>Le {result[qst_date_formated]}</td>
				<td>{result[qst_mid]}</td>
				<td>
					<a href="index.php?file=qst&amp;act=edit&amp;qid={result[qst_id]}&race={result[qst_race]}&param={result[qst_param_1]}"><img src="img/editer.png" alt="Editer" title="Editer" /></a>
					-
					<a href="index.php?file=qst&amp;act=del&amp;qid={result[qst_id]}"><img src="img/drop.png" alt="Supprimer" title="Supprimer" /></a>
				</td>
				</tr>
			</foreach>
			</table>
		</if>
		<else>
			<p class="infos">Aucune Quête.</p>
		</else>
</else>
<p class="retour_module"><a href="qst.html" title="Retour">Retour</a></p>
