<h4>
<a href="forum-<math oper="Template::str2url({quete->subject})"/>.html?pid={quete->pid}#{quete->pid}">{quete->subject}</a>
</h4>

<include file="modules/qst/view.tpl" quete="{quete}" cache="1" />

<form action="admin-edit.html?module=qst&amp;id={quete->cfg_id}" method="post">

    <p><label for="cfg_subject">Modifier le titre :</label>
    <input type="text" id="cfg_subject" name="cfg_subject" value="{quete->cfg_subject}" tabindex="1" />

    <p><label for="msg_pseudo">PNJ de la quête :</label>
    <input type="text" id="msg_pseudo" name="msg_pseudo" value="{quete->mbr->mbr_pseudo}" tabindex="2" maxlength="{TAILLE_MAX_PSEUDO}" />
    ( <a href="member-liste.html" title="Liste des joueurs">Liste</a> )</p>

    <h4>Prérequis pour cette quête :</h4>
    <p class="infos">Attention : si on modifie ces paramètres, ça ne s'applique pas aux joueurs qui ont déjà débuté cette quête.</p>

<select id="param1" name="param1">
    <foreach cond=" {qst_param} as {key} => {value}">
        <option value="{key}" <if cond="{key} == {quete->cfg_param1}">selected="selected"</if>>{value}</option>
    </foreach>
</select>
<input id="value1" name="value1" type="text" size="8" value="{quete->cfg_value1}" /><br/>

<select id="param2" name="param2">
    <foreach cond=" {qst_param} as {key} => {value}">
        <option value="{key}" <if cond="{key} == {quete->cfg_param2}">selected="selected"</if>>{value}</option>
    </foreach>
</select>
<input id="value2" name="value2" type="text" size="8" value="{quete->cfg_value2}" /><br/>

<select id="param3" name="param3">
    <foreach cond=" {qst_param} as {key} => {value}">
        <option value="{key}" <if cond="{key} == {quete->cfg_param3}">selected="selected"</if>>{value}</option>
    </foreach>
</select>
<input id="value3" name="value3" type="text" size="8" value="{quete->cfg_value3}" /><br/>

<select id="param4" name="param4">
    <foreach cond=" {qst_param} as {key} => {value}">
        <option value="{key}" <if cond="{key} == {quete->cfg_param4}">selected="selected"</if>>{value}</option>
    </foreach>
</select>
<input id="value4" name="value4" type="text" size="8" value="{quete->cfg_value4}" /><br/>

<label for="param5">Objectif de la quête : </label>
<select id="param5" name="param5">
    <foreach cond=" {qst_param} as {key} => {value}">
        <option value="{key}" <if cond="{key} == {quete->cfg_objectif}">selected="selected"</if>>{value}</option>
    </foreach>
</select>
<input id="value5" name="value5" type="text" size="8" value="{quete->cfg_obj_value}" /><br/>

<input type="submit" name="Valider" value="Valider" />
<input type="reset" name="Annuler" value="Annuler" />
</form>
