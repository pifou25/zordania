<if cond='{_display} == "xml"'>
{vars[histo_vars][unt_nb]} {unt[{_user[race]}][alt][{vars[histo_vars][unt_type]}]} sont morts de faim dans votre légion {vars[histo_vars][leg_name]}
</if>
<else>
{vars[histo_vars][unt_nb]} <zimgunt type="{vars[histo_vars][unt_type]}" race="{_user[race]}" />
 sont morts de faim dans votre légion {vars[histo_vars][leg_name]}.
 <if cond="isset({vars[histo_vars][leg_vit]})">(vitesse = {vars[histo_vars][leg_vit]})</if>
</else>