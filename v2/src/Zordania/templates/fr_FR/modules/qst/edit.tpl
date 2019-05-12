<h4>
<a href="forum-<math oper="Template::str2url({quete->subject})"/>.html?pid={quete->pid}#{quete->pid}">{quete->subject}</a>
</h4>
<if cond="isset({update})"><p class="infos"> {update} mise à jour</p></if>
<include file="modules/qst/view.tpl" quete="{quete}" cache="1" />

<form action="admin-edit.html?module=qst&amp;id={quete->cfg_id}" method="post">

    <p><label for="cfg_subject">Modifier le titre :</label>
    <input type="text" id="cfg_subject" name="cfg_subject" value="{quete->cfg_subject}" tabindex="1" />

    <p><label for="msg_pseudo">PNJ de la quête :</label>
    <input type="text" id="msg_pseudo" name="msg_pseudo" value="{quete->mbr->mbr_pseudo}" tabindex="2" maxlength="{TAILLE_MAX_PSEUDO}" />
    ( <a href="member-liste.html" title="Liste des joueurs">Liste</a> )</p>

    <h4>Prérequis pour cette quête :</h4>
    <p class="infos">Attention : si on modifie ces paramètres, ça ne s'applique pas aux joueurs qui ont déjà débuté cette quête.</p>

<select id="cfg_param1" name="cfg_param1">
    <foreach cond=" {qst_param} as {key} => {value}">
        <option value="{key}" <if cond="{key} == {quete->cfg_param1}">selected="selected"</if>>{value}</option>
    </foreach>
</select>
<input id="cfg_value1" name="cfg_value1" type="text" size="8" value="{quete->cfg_value1}" /><br/>

<select id="cfg_param2" name="cfg_param2">
    <foreach cond=" {qst_param} as {key} => {value}">
        <option value="{key}" <if cond="{key} == {quete->cfg_param2}">selected="selected"</if>>{value}</option>
    </foreach>
</select>
<input id="cfg_value2" name="cfg_value2" type="text" size="8" value="{quete->cfg_value2}" /><br/>

<select id="cfg_param3" name="cfg_param3">
    <foreach cond=" {qst_param} as {key} => {value}">
        <option value="{key}" <if cond="{key} == {quete->cfg_param3}">selected="selected"</if>>{value}</option>
    </foreach>
</select>
<input id="cfg_value3" name="cfg_value3" type="text" size="8" value="{quete->cfg_value3}" /><br/>

<select id="cfg_param4" name="cfg_param4">
    <foreach cond=" {qst_param} as {key} => {value}">
        <option value="{key}" <if cond="{key} == {quete->cfg_param4}">selected="selected"</if>>{value}</option>
    </foreach>
</select>
<input id="cfg_value4" name="cfg_value4" type="text" size="8" value="{quete->cfg_value4}" /><br/>

<label for="cfg_objectif">Objectif de la quête : </label>
<select id="cfg_objectif" name="cfg_objectif">
    <foreach cond=" {qst_param} as {key} => {value}">
        <option value="{key}" <if cond="{key} == {quete->cfg_objectif}">selected="selected"</if>>{value}</option>
    </foreach>
</select>
    Type :
<input id="cfg_obj_value" name="cfg_obj_value" type="text" size="8" value="{quete->cfg_obj_value}" />
    Nombre :
<input id="cfg_obj_counter" name="cfg_obj_counter" type="text" size="8" value="{quete->cfg_obj_counter}" /><br/>


<input type="submit" name="Valider" value="Valider" />
<input type="reset" name="Annuler" value="Annuler" />
</form>
