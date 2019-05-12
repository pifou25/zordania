<p class="menu_module">
    <a href="admin.html?module=qst">Admin Quêtes</a>
    <a href="admin-exp.html?module=qst">Export des Quêtes</a>
    <a href="forum-topic.html?fid={FORUM_QUETES}">Forum des Quêtes</a>
</p>

<h3>Paramétrage des Quêtes - <a href="admin-config.html?module=qst">update</a></h3>

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
<elseif cond='isset({qstDetail})'>
<foreach cond="{qstDetail} as {quete}">

    (<a href="forum.html?pid={quete->cfg_pid}#{quete->cfg_pid}"> forum </a>) :
    <a href="admin-edit.html?module=qst&amp;id={quete->cfg_id}"> {quete->cfg_subject} </a>
    [ id = {quete->cfg_id} ]
    <include file="modules/qst/view.tpl" quete="{quete}" cache="1" />
    
    <if cond="!empty({quete->cfg_param1})">( param1({quete->cfg_param1}) = {quete->cfg_value1} )</if>
    <if cond="!empty({quete->cfg_param2})"> - ( param2({quete->cfg_param2}) = {quete->cfg_value2} )</if>
    <if cond="!empty({quete->cfg_param3})"> - ( param4({quete->cfg_param3}) = {quete->cfg_value3} )</if>
    <if cond="!empty({quete->cfg_param4})"> - ( param3({quete->cfg_param4}) = {quete->cfg_value4} )</if>
    <if cond="!empty({quete->cfg_objectif})"> - {quete->cfg_objectif} = {quete->cfg_obj_value}</if>
    <br/>
</foreach>
</elseif>
<elseif cond="isset({qst})">
<foreach cond="{qst} as {val}">

    {val[subject]} (<a href="forum.html?tid={val[tid]}#{val[tid]}"> Forum </a>)
    (<a href="admin.html?module=qst&amp;tid={val[tid]}"> Paramètres </a>)
    <br/>
</foreach>
    
    <p class="infos">Le forum permet de créer de nouvelles quêtes. Ensuite, faire "update",
        puis "editer" pour la paramétrer ici.
        <br/>
    il faut au minimum spécifier l'objectif, éventuellement une valeur,
    et autant de paramètres comme prérequis et leur valeur associée.</p>
</elseif>