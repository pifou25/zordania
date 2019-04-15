<p>

<if cond="{leg[mbr_mid]} == {_user[mid]}">
	<if cond="{leg[leg_etat]} == Leg::ETAT_VLG">Votre village </if>
	<else>Votre légion </else>
<strong>{leg[leg_name]}</strong>
	<if cond="isset({leg[ratio_def]}) && {leg[ratio_def]} < 1">(Défense groupée à <math oper="{leg[ratio_def]}*100" /> %)</if>
</if>
<else>
	<if cond="{leg[leg_etat]} == Leg::ETAT_VLG">Le village de</if>
	<else>La légion de</else>
<img src="img/{leg[mbr_race]}/{leg[mbr_race]}.png" title="{race[{leg[mbr_race]}]}" alt="{race[{leg[mbr_race]}]}"/>
<a href="member-view.html?mid={leg[mbr_mid]}" title="infos sur {leg[mbr_pseudo]}">{leg[mbr_pseudo]}</a> :
<strong>{leg[leg_name]}</strong>
	<if cond="isset({leg[ratio_def]}) && {leg[ratio_def]} < 1">(Défense groupée à <math oper="{leg[ratio_def]}*100" /> %)</if>
</else>

<if cond='{war_act} == "histo" && {leg[hro_vie]}'>
menée par <zimgunt type="{leg[hro_type]}" race="{leg[mbr_race]}" /> {leg[hro_nom]} 
<if cond="isset({leg[hro_nrj]})">
{leg[hro_nrj]} <img src="img/eclair.png" alt="Energie" /> <zimgnrj per="{leg[hro_nrj]}" max="{HEROS_NRJ_MAX}" />
</if>
	<if cond="!empty({leg[bonus]})">
		<include file="modules/comp/{leg[bonus]}.tpl" cache="1" cpt="{leg[comp]}" />
	</if>
</if>
</p>


<if cond='{SITE_DEBUG}'><# mode debug #>
<p>
<foreach cond="{leg[leg]} as {type} => {nb}">
<if cond="!isset({leg[hro_type]}) || {leg[hro_type]} != {type}">
{nb} <zimgunt type="{type}" race="{leg[mbr_race]}" />
</if>
</foreach>
</p>
</if>


<p>Pertes :
<foreach cond="{leg[pertes][unt]} as {type} => {nb}">
<if cond="!isset({leg[hro_type]}) || {leg[hro_type]} != {type}">
{nb} <zimgunt type="{type}" race="{leg[mbr_race]}" />
</if>
</foreach>
<if cond="{leg[hro_vie]}">
<br/>
<zimgunt type="{leg[hro_type]}" race="{leg[mbr_race]}" />
<zimgbar per="{leg[pertes][hro_reste]}" max="{leg[hro_vie_conf]}" />&nbsp;
<if cond='{leg[pertes][hro_reste]} == 0'>
<if cond="{leg[bonus]} == {CP_RESURECTION}">
<p class="ok"><zimgcomp type="{leg[bonus]}" race="{leg[mbr_race]}" /> Votre héros succombe à ses blessures... puis ressucite au village ! Ce combat vous rapporte {leg[xp_won]} XP.</p>
</if><else>
<span class="error">{leg[hro_nom]} a perdu {leg[pertes][deg_hro]} points de vie, il est mort au combat !</span>
</else>
</if>
<else>
{leg[hro_nom]} a perdu {leg[pertes][deg_hro]} points de vie (reste {leg[pertes][hro_reste]} <img src="img/{_user[race]}/div/vie.png" alt="Vie" />).
</else>
</if>
</p>
<if cond='{war_act} == "war_atq" && {leg[hro_vie]} && !empty({leg[bonus]})'>
<include file="modules/comp/{leg[bonus]}.tpl" cache="1" cpt="{leg[comp]}" /><br />
</if>
