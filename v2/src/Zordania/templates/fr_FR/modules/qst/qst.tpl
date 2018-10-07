<div class="block_forum">
<img class="blason" title="{quete[username]}" src="img/mbr_logo/{quete[poster_id]}.png" />
    <h4>{quete[subject]}</h4>

<p><math oper='Parser::parse({quete[message]})' /></p>
<if cond='{quete[edited]}'>
        <p><em>édité par {quete[edited_by]} le {quete[edited]}</em></p>
</if>

<p>
    <strong>Objectif :</strong>
    <math oper="QstCfg::PARAMS[{quete[cfg_objectif]}]" />
<if cond="{quete[cfg_obj_value]} != 0">
    <if cond="{quete[cfg_objectif]} == 3">
        <zimgunt race="{_user[race]}" type="{quete[cfg_obj_value]}" />
    </if>
    <elseif cond="{quete[cfg_objectif]} == 4">
        <zimgbtc type="{quete[cfg_obj_value]}" race="{_user[race]}" /> 
    </elseif>
    <elseif cond="{quete[cfg_objectif]} == 5">
        <zimgsrc type="{quete[cfg_obj_value]}" race="{_user[race]}" />
    </elseif>
    <elseif cond="{quete[cfg_objectif]} == 6">
        <zimgres type="{quete[cfg_obj_value]}" race="{_user[race]}" />
    </elseif>
    <elseif cond="{quete[cfg_objectif]} == 14">
        <zimgcomp type="{quete[cfg_obj_value]}" race="{_user[race]}" />
    </elseif>
    <else>{quete[cfg_obj_value]}</else>
</if>
</p>

<p>
    <a href="forum-post.html?pid={quete[pid]}#{quete[pid]}">le {quete[posted]}</a> par
    <if cond="{quete[mbr_gid]}">
            <zurlmbr gid="{quete[mbr_gid]}" mid="{quete[poster_id]}" pseudo="{quete[username]}"/>
    </if>
    <else>{quete[username]}</else>
</p>
</div>
