<if cond="!isset({mbr_array})"><p class="error">Ce membre n'existe pas</p></if>
<else>

<foreach cond="{race} as {race_id} =>{race_name}">
	<load file="race/{race_id}.config" />
	<load file="race/{race_id}.descr.config" />
</foreach>

<div class="menu_module">
	<a href="admin-histo.html?mid={mbr_array[mbr_mid]}&amp;module=war&amp;sub=atq" title="Attaques de vos légions sur les autres joueurs">Attaque</a>
	<a href="admin-histo.html?mid={mbr_array[mbr_mid]}&amp;module=war&amp;sub=def" title="Attaques des autres joueurs sur vos légions">Défense</a>
</div>

<if cond='{war_sub} == "def"'>
	<h2>Défense</h2>
</if>
<else>
	<h2>Attaque</h2>
</else>

<script type="text/javascript">
<!-- // requete ajax : copier journal vers bbcode ...
function copyToClipboard1(element) {
  var $temp = $("<textarea>");
  $("body").append($temp);
  $temp.val($(element).val()).select();
  try {
    var succeed = document.execCommand('copy');
	if(succeed){
	  alert('Journal copié avec succès ! Ctrl+v ou menu \'coller\' pour le copier dans un forum, une note, un message privé ou sur la shootbox.');
	  $temp.remove();
	}else{
	  alert('Echec de copie dans le presse-papier. Copiez le bbcode ci dessus');
	  $(element).display();
	}
  }catch(err){
	alert('erreur:' + err); 
  }
  return succeed;
}

function CopyJournalToBBCode(aid) {
	var output =  $('#RCBBcode'+aid);
	output.val('Wait a minute please...');
	$.ajax({
		url: cfg_url+"ajax--admin-histo.html?mid={mbr_array[mbr_mid]}&module=war&aid="+aid,
		success: function(html) {
			output.val(html);
			alert('clic sur le 2e bouton !');
			/*
			if(copyToClipboard1(html)){
				alert('Journal copié avec succès ! Ctrl+v ou menu \'coller\' pour le copier dans un forum, une note, un message privé ou sur la shootbox.');
			}else{
				alert('Echec de copie dans le presse-papier. Copiez le bbcode ci dessus');
			}*/
		},
		error: function(jqXHR, textStatus, errorThrown){
			 output.val('<p class="error">Erreur : ' + textStatus + ' sur le module admin-histo - ' + errorThrown + '</p>');
		},
		complete: function(jqXHR, textStatus){
			if(textStatus != 'success')
				 output.append('<p class="infos">' + textStatus + '</p>');
		}
	});
	return false;
}
// -->
</script>

<dl>
<foreach cond="{atq_array} as {value}">

	<dt>
	<a href="admin-histo.html?mid={mbr_array[mbr_mid]}&amp;module=war&amp;aid={value[atq_aid]}&amp;sub={war_sub}">Le {value[atq_date_formated]}</a>
	<a href="member-<math oper="str2url({value[atq_bilan][att][mbr_pseudo]})"/>.html?mid={value[atq_bilan][att][mbr_mid]}">{value[atq_bilan][att][mbr_pseudo]}</a> a attaqué 
	<a href="member-<math oper="str2url({value[mbr_pseudo2]})"/>.html?mid={value[atq_mid2]}">{value[mbr_pseudo2]}</a>
	</dt>


	<dd class="block_forum">
	<include file="modules/war/armee.tpl" cache="1" leg="{value[atq_bilan][att]}" />

	<include file="modules/war/armee.tpl" cache="1" leg="{value[atq_bilan][def][{value[atq_lid2]}]}" />

	<h3>Légions en défense :</h3>
	<foreach cond="{value[atq_bilan][def]} as {lid} => {leg}">
		<if cond="{lid} != {value[atq_lid2]}">
		<include file="modules/war/armee.tpl" cache="1" /><# ici {leg} est déjà défini #>
		</if>
	</foreach>


	<set name="leg" value="{value[atq_bilan][def][{value[atq_lid2]}]}" />
	<if cond="{value[atq_bilan][btc_def]}">
		<h3>Bâtiments défensifs :</h3>
		<foreach cond="{value[atq_bilan][btc_def]} as {btc2} => {nb}">
			{nb} <zimgbtc type="{btc2}" race="{leg[mbr_race]}" />
		</foreach>
		<if cond="isset({value[atq_bilan][btc_bonus][bon]})"><p>Bonus fourni par les bâtiments = {value[atq_bilan][btc_bonus][bon]} % (y compris le donjon)</p>
		</if>
	</if>

	<if cond="{value[atq_bilan][btc_edit]}">
		L'attaque sur les bâtiments a produit {value[atq_bilan][atq_bat]} points de dégâts.
		<h3>Bâtiments détruits :</h3>
		<foreach cond="{value[atq_bilan][btc_edit]} as {btc2}">
			<if cond="{btc2[vie]} == 0">
				{btc2[vie]} / {btc2[vie_max]} <zimgbtc type="{btc2[type]}" race="{leg[mbr_race]}" />
			</if>
		</foreach>

		<h3>Bâtiments endommagés :</h3>
		<foreach cond="{value[atq_bilan][btc_edit]} as {btc2}">
			<if cond="{btc2[vie]} != 0">
				<if cond='{war_sub} == "def"'>
					<a href="btc-use.html?btc_type={btc2[type]}&amp;sub=list" title="Réparer">
					{btc2[vie]} / {btc2[vie_max]} <zimgbtc type="{btc2[type]}" race="{leg[mbr_race]}" /></a>
				</if>
				<else>
					{btc2[vie]} / {btc2[vie_max]} <zimgbtc type="{btc2[type]}" race="{leg[mbr_race]}" />
				</else>
			</if>
		</foreach>
	</if>

	<h3>Butin attaquant :</h3>
	<foreach cond="{value[atq_bilan][butin][att]} as {type} => {nb}">
		<if cond="{nb}">{nb} <zimgres type="{type}" race="{mbr_array[mbr_race]}" /></if>
	</foreach>
	<span class="ok">{value[atq_bilan][att][xp_won]} XP gagnée par l'attaquant.</span>

	<br/>
	<h3>Butin défenseur :</h3>
	<foreach cond="{value[atq_bilan][butin][def]} as {type} => {nb}">
		<if cond="{nb}">{nb} <zimgres type="{type}" race="{mbr_array[mbr_race]}" /></if>
	</foreach>
	<foreach cond="{value[atq_bilan][def]} as {lid} => {leg}">
		<if cond="{leg[leg_etat]} != LEG_ETAT_VLG">
			<p class="ok">{leg[xp_won]} XP gagnée pour la légion {leg[leg_name]}</p>
		</if>
	</foreach>
	</dd>

<div class="debug"><debug print="{value}" /></div>

<p id="RCBBcode{value[atq_aid]}" style="display:none">
<include file="modules/war/bbcodelog.tpl" cache="1" />
</p>
<# input type="button" value="Call BBCode RC!" onclick="CopyJournalToBBCode({value[atq_aid]})" / #>
<input type="button" value="Copy RC!" onclick="copyToClipboard1('#RCBBcode{value[atq_aid]}')" />

</foreach>
</dl>


<p>Page :
<for cond='{i} = 0; {i} < {atq_nb}; {i}+={limite_page}'>
<if cond='{i} / {limite_page} != {war_page}'>
	<a href="war-histo.html?sub={war_sub}&amp;war_page=<math oper='({i} / {limite_page})' />"><math oper='(({i} / {limite_page})+1)' /></a>
</if>
<else>
	<math oper='(({i} / {limite_page})+1)' />
</else>
</for>
</p>

</else>
