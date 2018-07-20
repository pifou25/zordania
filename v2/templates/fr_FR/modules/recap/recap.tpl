<h3>Récapitulatif bugs</h3>
 <p class="menu_module">
<a href="recap.html?fid={FORUM_BUG_FID}" title="Récap bugs"> Récap bugs </a>
<a href="recap.html?fid={FORUM_SUGGEST_FID}" title="Récap Suggestions">Récap Suggestions </a>
</p>

<select name="nimporte" onChange="location.href=''+this.options[this.selectedIndex].value+'';">
  <option>Choix</option>
  <option value="recap.html?fid={fid_get}&type={TYPE_REPORT_NEW}">{forum_type[0]}</option>
  <option value="recap.html?fid={fid_get}&type={TYPE_REPORT_WAR}">{forum_type[1]}</option>
  <option value="recap.html?fid={fid_get}&type={TYPE_REPORT_HERO}">{forum_type[2]}</option>
  <option value="recap.html?fid={fid_get}&type={TYPE_REPORT_UNT}">{forum_type[3]}</option>
  <option value="recap.html?fid={fid_get}&type={TYPE_REPORT_ALLI}">{forum_type[4]}</option>
  <option value="recap.html?fid={fid_get}&type={TYPE_REPORT_GEN}">{forum_type[5]}</option>
  <option value="recap.html?fid={fid_get}&type={TYPE_REPORT_VLG}">{forum_type[6]}</option>
  <option value="recap.html?fid={fid_get}&type={TYPE_REPORT_GAME}">{forum_type[7]}</option>
  <option value="recap.html?fid={fid_get}&type={TYPE_REPORT_COM}">{forum_type[8]}</option>
  <option value="recap.html?fid={fid_get}&type={TYPE_REPORT_MSG}">{forum_type[9]}</option>
  <option value="recap.html?fid={fid_get}&type={TYPE_REPORT_ELSE}">{forum_type[10]}</option>
</select>
Retour forum <if cond="{fid_get} == {FORUM_BUG_FID}"><a href="forum-topic.html?fid={FORUM_BUG_FID}" title="Forum bugs"> Rapport de bugs </a></if>
<elseif cond="{fid_get} == {FORUM_SUGGEST_FID}"><a href="forum-topic.html?fid={FORUM_SUGGEST_FID}" title="Forum suggestion"> Suggestions</a></else>
<table class="liste">
	<tr>
		<th>Etat</th>
		<th>Auteur</th>
		<th>Sujet</th>
  </tr>
  <if cond="{empty}">Il n'y a rien dans cette rubrique</if>
  <else>
	<foreach cond="{recap_array} as {fid}">
		<tr >			
			<td><img src="img/forum/{fid[statut]}.png" title="{forum_statut[{fid[statut]}]}" /></td>
			<td>{fid[poster]}</td>
			<td><a href="forum-<math oper="str2url({fid[subject]})"/>.html?tid={fid[id]}" title="Nouvelle News!" >{fid[subject]}</a></td>
		</tr>
	</foreach>
</else>
</table>