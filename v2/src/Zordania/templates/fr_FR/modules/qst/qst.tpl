<div class="block_forum">
<img class="blason" title="Quêsteur" src="img/mbr_logo/{quete->mbr->mbr_mid}.png" />
    <h4>{quete->cfg_subject}</h4>

<p><math oper='Parser::parse({quete->post->message})' /></p>
<if cond='{quete->post->edited}'>
        <p><em>édité par {quete->post->edited_by} le {quete->post->edited}</em></p>
</if>

<p>
    <strong>Objectif :</strong> {qst_param[{quete->cfg_objectif}]}

<if cond="{quete->cfg_obj_value} != 0">
    <if cond="{quete->cfg_objectif} == 3">
        <zimgunt race="{_user[race]}" type="{quete->cfg_obj_value}" />
    </if>
    <elseif cond="{quete->cfg_objectif} == 4">
        <zimgbtc type="{quete->cfg_obj_value}" race="{_user[race]}" /> 
    </elseif>
    <elseif cond="{quete->cfg_objectif} == 5">
        <zimgsrc type="{quete->cfg_obj_value}" race="{_user[race]}" />
    </elseif>
    <elseif cond="{quete->cfg_objectif} == 6">
        <zimgres type="{quete->cfg_obj_value}" race="{_user[race]}" />
    </elseif>
    <elseif cond="{quete->cfg_objectif} == 14">
        <zimgcomp type="{quete->cfg_obj_value}" race="{_user[race]}" />
    </elseif>
    <else>{quete->cfg_obj_value}</else>
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
