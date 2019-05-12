<div class="block_forum">
    <if cond="empty({quete->mbr})">PNJ</if>
    <else>
<img class="blason" title="Quêsteur" src="img/mbr_logo/{quete->mbr->mbr_mid}.png" />
    </else>
    <h4>{quete->cfg_subject}</h4>

<p><math oper='Parser::parse({quete->post->message})' /></p>
<if cond='{quete->post->edited}'>
        <p><em>édité par {quete->post->edited_by} le {quete->post->edited}</em></p>
</if>

<p>
    <strong>Objectif :</strong> {qst_param[{quete->cfg_objectif}]}

<if cond="{quete->cfg_obj_value} != 0">
    <if cond="{quete->cfg_objectif} == 3">
        {quete->cfg_obj_counter} <zimgunt race="{_user[race]}" type="{quete->cfg_obj_value}" />
    </if>
    <elseif cond="{quete->cfg_objectif} == 4">
        {quete->cfg_obj_counter} <zimgbtc type="{quete->cfg_obj_value}" race="{_user[race]}" /> 
    </elseif>
    <elseif cond="{quete->cfg_objectif} == 5">
        <zimgsrc type="{quete->cfg_obj_value}" race="{_user[race]}" />
    </elseif>
    <elseif cond="{quete->cfg_objectif} == 6">
        {quete->cfg_obj_counter} <zimgres type="{quete->cfg_obj_value}" race="{_user[race]}" />
    </elseif>
    <elseif cond="{quete->cfg_objectif} == 13">
        <zimgunt race="{_user[race]}" type="{quete->cfg_obj_value}" />
    </elseif>
    <elseif cond="{quete->cfg_objectif} == 14">
        <zimgcomp type="{quete->cfg_obj_value}" race="{_user[race]}" />
    </elseif>
    <else>(Type / Valeur par défaut) Type = {quete->cfg_objectif}, Valeur = {quete->cfg_obj_value}, compteur = {quete->cfg_obj_counter}</else>
</if>
</p>

<p>
    <a href="forum-post.html?pid={quete->cfg_pid}#{quete->cfg_pid}"><img src="img/msg.png" title="forum" /></a>
    le {quete->post->posted} par
    <if cond="isset({quete->mbr->mbr_gid})">
        <zurlmbr gid="{quete->mbr->mbr_gid}" mid="{quete->mbr->mbr_mid}" pseudo="{quete->mbr->mbr_pseudo}"/>
    </if>
    <else>Quêsteur</else>
</p>
</div>
