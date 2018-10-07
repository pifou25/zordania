<h3>Paramétrage des Quêtes - <a href="admin-update.html?module=qst">update</a></h3>

<if cond="isset({update})">
    <p class="ok">Mise à jour effectuée ({update})</p>
</if>

<if cond='{_act} == "mbr"'>
    <p class="infos">Quêtes du membre :</p>
    <foreach cond="Qst::getAll({_user[mid]}) as {qst}">
        {qst[qst_id]} (<a href="admin-edit.html?module=qst&amp;id={qst[qst_cfg_id]}"> editer </a>)
        Commencée le {qst[created_at]} 
        <if cond="{qst[finished_at]} == null"><strong>Quête en cours...</strong></if>
        <else>Terminée le {qst[finished_at]}</else>
        
        Dernière modif le {qst[updated_at]}
        <br/>
    </foreach>
</if>
<elseif cond="isset({quete})">

    <h4>
    <a href="forum-<math oper="Template::str2url({quete[subject]})"/>.html?pid={quete[pid]}#{quete[pid]}">{quete[subject]}</a>
    </h4>

    <include file="modules/qst/qst.tpl" quete="{quete}" cache="1" />
    
    <form action="admin-edit.html?module=qst&amp;id={quete[cfg_id]}" method="post">

        <p><label for="msg_pseudo">PNJ de la quête :</label>
        <input type="text" id="msg_pseudo" name="msg_pseudo" value="{quete[username]}" tabindex="1" maxlength="{TAILLE_MAX_PSEUDO}">
        ( <a href="member-liste.html" title="Liste des joueurs">Liste</a> )</p>

        <h4>Prérequis pour cette quête :</h4>
        <p class="infos">Attention : si on modifie ces paramètres, ça ne s'applique pas aux joueurs qui ont déjà cette quête en cours.</p>
        
    <select id="param1" name="param1">
        <foreach cond="QstCfg::PARAMS as {key} => {value}">
            <option value="{key}" <if cond="{key} == {quete[cfg_param1]}">selected="selected"</if>>{value}</option>
        </foreach>
    </select>
    <input id="value1" name="value1" type="text" size="8" value="{quete[cfg_value1]}" /><br/>
    
    <select id="param2" name="param2">
        <foreach cond="QstCfg::PARAMS as {key} => {value}">
            <option value="{key}" <if cond="{key} == {quete[cfg_param2]}">selected="selected"</if>>{value}</option>
        </foreach>
    </select>
    <input id="value2" name="value2" type="text" size="8" value="{quete[cfg_value2]}" /><br/>
    
    <select id="param3" name="param3">
        <foreach cond="QstCfg::PARAMS as {key} => {value}">
            <option value="{key}" <if cond="{key} == {quete[cfg_param3]}">selected="selected"</if>>{value}</option>
        </foreach>
    </select>
    <input id="value3" name="value3" type="text" size="8" value="{quete[cfg_value3]}" /><br/>
    
    <select id="param4" name="param4">
        <foreach cond="QstCfg::PARAMS as {key} => {value}">
            <option value="{key}" <if cond="{key} == {quete[cfg_param4]}">selected="selected"</if>>{value}</option>
        </foreach>
    </select>
    <input id="value4" name="value4" type="text" size="8" value="{quete[cfg_value4]}" /><br/>
    
    <label for="param5">Objectif de la quête : </label>
    <select id="param5" name="param5">
        <foreach cond="QstCfg::PARAMS as {key} => {value}">
            <option value="{key}" <if cond="{key} == {quete[cfg_objectif]}">selected="selected"</if>>{value}</option>
        </foreach>
    </select>
    <input id="value5" name="value5" type="text" size="8" value="{quete[cfg_obj_value]}" /><br/>
    
    <input type="submit" name="Valider" value="Valider" />
    <input type="reset" name="Annuler" value="Annuler" />
    </form>
    
</elseif>
<elseif cond="isset({qst})">
<foreach cond="{qst} as {val}">

    (<a href="forum.html?tid={val[cfg_tid]}#{val[cfg_tid]}"> forum </a>)
    <a href="forum.html?pid={val[cfg_pid]}#{val[cfg_pid]}">{val[subject]}</a> :
    (<a href="admin-edit.html?module=qst&amp;id={val[cfg_id]}"> editer </a>)
    <if cond="!empty({val[cfg_param1]})">( {val[cfg_param1]} = {val[cfg_value1]} )</if>
    <if cond="!empty({val[cfg_param2]})"> - ( {val[cfg_param2]} = {val[cfg_value2]} )</if>
    <if cond="!empty({val[cfg_param3]})"> - ( {val[cfg_param3]} = {val[cfg_value3]} )</if>
    <if cond="!empty({val[cfg_param4]})"> - ( {val[cfg_param4]} = {val[cfg_value4]} )</if>
    <if cond="!empty({val[cfg_objectif]})"> - {val[cfg_objectif]} = {val[cfg_obj_value]}</if>
    <br/>
</foreach>
    
    <p class="infos">Le forum permet de créer de nouvelles quêtes. Ensuite, faire "update", puis "editer" pour la paramétrer ici.
        <br/>
    il faut au minimum spécifier l'objectif, éventuellement une valeur,
    et autant de paramètres comme prérequis et leur valeur associée.</p>
</elseif>