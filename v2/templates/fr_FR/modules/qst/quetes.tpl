<p class="menu_module">
<a href="qst-conf.html" title="Quetes">Config</a>
- <a href="qst-edit.html?qid=0&race=1" title="Ajouter une Quête">Nouvelle Quête</a>
</p>

		
<if cond='{qst_act} == "edit"'>
    
    <p class="infos">Si nouvelle quête, Choisir la race!!!:<br/> 
        /!\ la race par défaut sont les humains. Il faut au moins 1 objectif et 1 récompense, le reste n'est pas obligatoire<br/>
        1) Choisir la race<br/> 
        2) Définir si la quête est commune aux autres race, si oui laisser humain<br/>  
        3) Remplir les informations<br/> 
        4) Définir les prérequis<br/>
        5) Définir les objectifs, au moins un<br/>
        6) Choisir les récompenses et la quantité<br/>   
        8) c/c vos textes si vous voulez changer de race ou d'objectif!!!<br/> 
    </p>
        <br/> 
    
	<if cond="isset({qst_bad_qid})">
		<p class="error">Quête Inexistante !</p>
	</if>
	<else>
		<if cond="isset({qst_ok})">
			<p class="ok">Quête enregistrée !</p>
		</if>
    <form action="qst-edit.html?qid={qst_qid}&race={raceid}" method="post" id="newpost">
        Race:
        <select name="race" onChange="location.href=''+this.options[this.selectedIndex].value+'';">
            <foreach cond="{_races} as {race_id} =>  {value}">
                <if cond="{value} === true">
                <option value="qst-edit.html?qid={qst_qid}&race={race_id}" <if cond="{race_id} == {raceid}">selected</if>>{race[{race_id}]}</option>
                </if>
            </foreach>
        </select>
        <br/> 
    
        Quête: 
        <select name="qst_com" >
            <option value="{QST_CFG_NOTCOM}" <if cond="{qst_com} == {QST_CFG_NOTCOM}">selected</if>>Non commune</option> 
            <option value="{QST_CFG_COM}" <if cond="{qst_com} == {QST_CFG_COM}">selected</if>>Commune</option>
        </select> 
        <br/>

        Statut:   
        <select name="qst_statut" >
            <option value="{QST_CFG_OFF}" <if cond="{qst_statut} == {QST_CFG_OFF}">selected</if>>Hors ligne</option> 
            <option value="{QST_CFG_ON}" <if cond="{qst_statut} == {QST_CFG_ON}">selected</if>>En ligne</option>
        </select> 
        <br/>
        <br/> 

        <h3> Informations:</h3>
            <label for="pst_titre">Titre de la quête:</label> <input type="text" id="pst_titre" name="pst_titre" value="{pst_titre}" />

            <br/>   
            <br/>
            Description de la quête:
            <include file='commun/bbcode.tpl' cache='1' /><br/>
            <textarea id="message" cols="60" rows="11" name="pst_msg">{pst_msg}</textarea> 
            <br/>   

        <load file="race/{raceid}.config" />

        <h3> Prérequis:</h3>  
            Quête précédente: <select name="req_qid" id="req_qid">
            <option value=0 <if cond="0 == {req_qid}">selected</if>>Aucune</option>
            <if cond="{qst_array}">
                <foreach cond="{qst_array} as {quest}">
                    <option value="{quest[qst_id]}" <if cond="{quest[qst_id]} == {req_qid}">selected</if>> <if cond="{quest[qst_common]} == 1">[Commune]</if><else>[{race[{quest[qst_race]}]}]</else> {quest[qst_title]}</option>
                </foreach>
            </if>
            </select><br/> 

            Points: <input name="req_pts" id="req_pts" type="Number" value="{req_pts}" placeholder="Points" /><br/> 
            Force armée: <input name="req_pts_armee" id="req_pts_armee" type="Number" value="{req_pts_armee}" placeholder="Force armée" /><br/>  
            Bâtiments: 
            <select name="req_btc" id="req_btc">
            <option value=0 <if cond="0 == {req_btc}">selected</if>>Aucun</option>
                <foreach cond="{man_btc} as {obj_id} => {obj_value}">
                    <if cond="{value} === true">
                    <option value="{obj_id}" <if cond="{obj_id} == {req_btc}">selected</if>>{btc[{raceid}][alt][{obj_id}]}</option>
                    </if>
                </foreach>
            </select>        
            <br/> 

            Recherche: 
            <select name="req_src" id="req_src">
            <option value=0 <if cond="0 == {req_src}">selected</if>>Aucune</option>
                <foreach cond="{man_src} as {obj_id} => {obj_value}">
                    <if cond="{value} === true">
                    <option value="{obj_id}" <if cond="{obj_id} == {req_src}">selected</if>>{src[{raceid}][alt][{obj_id}]}</option>
                    </if>
                </foreach>
            </select>            
            <br/>  
            <br/> 

        <h3>Objectifs:</h3>  
            Bâtiments: 
            <select name="btc_id_1" id="btc_id_1">
            <option value=0 <if cond="0 == {btc_id_1}">selected</if>>Aucun</option>
                <foreach cond="{man_btc} as {obj_id} => {obj_value}">
                    <if cond="{value} === true">
                    <option value="{obj_id}" <if cond="{obj_id} == {btc_id_1}">selected</if>>{btc[{raceid}][alt][{obj_id}]}</option>
                    </if>
                </foreach>
            </select>            
            <input name="btc_nb_1" id="btc_nb_1" type="Number" value="{btc_nb_1}" placeholder="Valeur" /> 

            <select name="btc_id_2" id="btc_id_2">
            <option value=0 <if cond="0 == {btc_id_2}">selected</if>>Aucun</option>
                <foreach cond="{man_btc} as {obj_id} => {obj_value}">
                    <if cond="{value} === true">
                    <option value="{obj_id}" <if cond="{obj_id} == {btc_id_2}">selected</if>>{btc[{raceid}][alt][{obj_id}]}</option>
                    </if>
                </foreach>
            </select>            
            <input name="btc_nb_2" id="btc_nb_2" type="Number" value="{btc_nb_2}" placeholder="Valeur" />
            <br/> 

            Unités: 
            <select name="unt_id_1" id="unt_id_1">
            <option value=0 <if cond="0 == {unt_id_1}">selected</if>>Aucune</option>
                <foreach cond="{man_unt} as {obj_id} => {obj_value}">
                    <if cond="{value} === true">
                    <option value="{obj_id}" <if cond="{obj_id} == {unt_id_1}">selected</if>>{unt[{raceid}][alt][{obj_id}]}</option>
                    </if>
                </foreach>
            </select>            
            <input name="unt_nb_1" id="unt_nb_1" type="Number" value="{unt_nb_1}" placeholder="Valeur" />

            <select name="unt_id_2" id="unt_id_2">
            <option value=0 <if cond="0 == {unt_id_2}">selected</if>>Aucune</option>
                <foreach cond="{man_unt} as {obj_id} => {obj_value}">
                    <if cond="{value} === true">
                    <option value="{obj_id}" <if cond="{obj_id} == {unt_id_2}">selected</if>>{unt[{raceid}][alt][{obj_id}]}</option>
                    </if>
                </foreach>
            </select>            
            <input name="unt_nb_2" id="unt_nb_2" type="Number" value="{unt_nb_2}" placeholder="Valeur" />
            <br/> 

            Ressources: 
            <select name="res_id" id="res_id">
            <option value=0 <if cond="0 == {res_id}">selected</if>>Aucune</option>
                <foreach cond="{man_res} as {obj_id} => {obj_value}">
                    <if cond="{value} === true">
                    <option value="{obj_id}" <if cond="{obj_id} == {res_id}">selected</if>>{res[{raceid}][alt][{obj_id}]}</option>
                    </if>
                </foreach>
            </select>            
            <input name="res_nb" id="res_nb" type="Number" value="{res_nb}" placeholder="Valeur" />
            <br/>  

            Recherche: 
            <select name="src_id" id="src_id">
            <option value=0 <if cond="0 == {src_id}">selected</if>>Aucune</option>
                <foreach cond="{man_src} as {obj_id} => {obj_value}">
                    <if cond="{value} === true">
                    <option value="{obj_id}" <if cond="{obj_id} == {src_id}">selected</if>>{src[{raceid}][alt][{obj_id}]}</option>
                    </if>
                </foreach>
            </select>            
            <br/> 
            <br/> 

        <h3> Récompenses:</h3> 
            <select name="rec_1" id="rec_1">
            <option value=0 <if cond="0 == {rec_1}">selected</if>>Aucune</option>
                <foreach cond="{man_res} as {obj_id} => {obj_value}">
                    <if cond="{value} === true">
                    <option value="{obj_id}" <if cond="{obj_id} == {rec_1}">selected</if>>{res[{raceid}][alt][{obj_id}]}</option>
                    </if>
                </foreach>
            </select>
            <input name="rec_val1" id="rec_val1" type="Number" value="{rec_val1}" placeholder="Valeur 1" />
            <br/>

            <select name="rec_2" id="rec_2">
            <option value=0 <if cond="0 == {rec_2}">selected</if>>Aucune</option>
                <foreach cond="{man_res} as {obj_id} => {obj_value}">
                    <if cond="{value} === true">
                    <option value="{obj_id}" <if cond="{obj_id} == {rec_2}">selected</if>>{res[{raceid}][alt][{obj_id}]}</option>
                    </if>
                </foreach>
            </select>
            <input name="rec_val2" id="rec_val2" type="Number" value="{rec_val2}" placeholder="Valeur 2" />
            <br/>
            Expérience: <input name="rec_xp" id="rec_xp" type="Number" value="{rec_xp}" placeholder="Valeur xp" />
            <br/> 
            <br/> 

        <input type="submit" value="Enregistrer" />
        <input type="button" id="btpreview" value="Prévisualiser" />

         
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
	<a href="qst-edit.html?qid={qst_array[qst_id]}&race={qst_array[qst_race]}"><img src="img/editer.png" alt="Editer" title="Editer" /></a>
	-
	<a href="qst-del.html?qid={qst_array[qst_id]}"><img src="img/drop.png" alt="Supprimer" title="Supprimer" /></a><br/>
    Race: <img src="img/{qst_array[qst_race]}/{qst_array[qst_race]}.png" title="{race[{qst_array[qst_race]}]}" /><br/>
	Quête précédente: {qst_array[qst_req_qid]}<br/>
	
	Objectif:  {qst_array[qst_btc_nb1]}x <img src="img/{qst_array[qst_race]}/btc/{qst_array[qst_btc_id1]}.png" title="{btc[{qst_array[qst_race]}][alt][{qst_array[qst_btc_id1]}]}" />
    <br/> 
    <br/>
    
    Récompenses: 
        {qst_array[qst_rec_res1]}x <img src="img/{qst_array[qst_race]}/res/{qst_array[qst_rec_res1]}.png" title="{res[{qst_array[qst_race]}][alt][{qst_array[qst_rec_res1]}]}" /> - {qst_array[qst_rec_res2]}x <img src="img/{qst_array[qst_race]}/res/{qst_array[qst_rec_res2]}.png" title="{res[{qst_array[qst_race]}][alt][{qst_array[qst_rec_res2]}]}" />
    
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
				<th>Titre</th>
				<th>Req Qst</th>
				<th>Req Pts</th>
				<th>Req Pts armée</th>
				<th>Req Btc</th>
				<th>Req src</th>
				<th>Obj Bat</th>
				<th>Obj Unt</th>
				<th>Obj Res</th>
				<th>Obj Src</th>
				<th>Recomp.</th>
				<th>Rec XP</th>
				<th>Actions</th>
				</tr>
			<foreach cond="{qst_array} as {result}">
                <load file="race/{result[qst_race]}.config" />
				<tr>
				<td><if cond="{result[qst_common]} == 1"><img src="img/forum/0.png" title="Commune"/></if><else><img src="img/{result[qst_race]}/{result[qst_race]}.png" title="{race[{result[qst_race]}]}" /></else> <a href="qst-view.html?qid={result[qst_id]}" title="Voir '{result[qst_title]}'">{result[qst_title]}</td>
				<td>{result[qst_req_qid]}</td>
				<td>{result[qst_req_pts]}</td>
				<td>{result[qst_req_pts_armee]}</td>
				<td><if cond="{result[qst_req_btc]} >0">{btc[{result[qst_race]}][alt][{result[qst_req_btc]}]}</if></td>
				<td><if cond="{result[qst_req_src]} >0"><img src="img/{result[qst_race]}/src/{result[qst_req_src]}.png" title="{result[qst_race]}][alt][{result[qst_req_src]}]}" /></if></td>
                <td><if cond="{result[qst_btc_nb1]} >0">{result[qst_btc_nb1]} {btc[{result[qst_race]}][alt][{result[qst_btc_id1]}]}</if><if cond="{result[qst_btc_nb2]} >0"> + {result[qst_btc_nb2]} {btc[{result[qst_race]}][alt][{result[qst_btc_id2]}]}</if></td>
				<td><if cond="{result[qst_unt_nb1]} >0">{result[qst_unt_nb1]} <img src="img/{result[qst_race]}/unt/{result[qst_unt_id1]}.png" title="{result[qst_race]}][alt][{result[qst_unt_id1]}]}" /></if><if cond="{result[qst_unt_nb2]} >0"> + {result[qst_unt_nb2]} <img src="img/{result[qst_race]}/unt/{result[qst_unt_id2]}.png" title="{result[qst_race]}][alt][{result[qst_unt_id2]}]}" /></if></td>
                <td><if cond="{result[qst_res_nb]} >0">{result[qst_res_nb]} <img src="img/{result[qst_race]}/res/{result[qst_res_id]}.png" title="{result[qst_race]}][alt][{result[qst_res_id]}]}" /></if></td>
                <td><if cond="{result[qst_src_id]} >0"><img src="img/{result[qst_race]}/src/{result[qst_src_id]}.png" title="{result[qst_race]}][alt][{result[qst_src_id]}]}" /></if></td>
				<td><if cond="{result[qst_rec_val1]} >0">{result[qst_rec_val1]} <img src="img/{result[qst_race]}/res/{result[qst_rec_res1]}.png" title="{result[qst_race]}][alt][{result[qst_rec_res1]}]}" /></if>
				<if cond="{result[qst_rec_val2]} >0"> + {result[qst_rec_val2]} <img src="img/{result[qst_race]}/res/{result[qst_rec_res2]}.png" title="{result[qst_race]}][alt][{result[qst_rec_res2]}]}" /></if></td>
				<td>{result[qst_rec_xp]}</td>
				<td><if cond="{result[qst_statut]} == QST_CFG_ON"><img src="img/forum/3.png" title="En ligne"/></if><else><img src="img/forum/2.png" title="Hors ligne"/></else>
                     - 
					<a href="index.php?file=qst&amp;act=edit&amp;qid={result[qst_id]}&race={result[qst_race]}"><img src="img/editer.png" alt="Editer" title="Editer" /></a>
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
