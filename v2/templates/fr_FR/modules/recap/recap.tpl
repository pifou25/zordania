 <p class="menu_module">
<a href="recap.html?fid={FORUM_BUG_FID}&type={tp}" title="Récap bugs"> Récap bugs </a>
<a href="recap.html?fid={FORUM_SUGGEST_FID}&type={tp}" title="Récap Suggestions">Récap Suggestions </a>
</p>

<h3><if cond="{fid_get} == {FORUM_BUG_FID}">Récapitulatif des bugs</if>
<elseif cond="{fid_get} == {FORUM_SUGGEST_FID}">Récapitulatif des Suggestions</else>: {report_type[{tp}]}</h3>


<select name="nimporte" onChange="location.href=''+this.options[this.selectedIndex].value+'';">
  <option>Choix rubrique</option>
  <option value="recap.html?fid={fid_get}&type={REPORT_TYPE_NEW}">{report_type[0]}</option>
  <option value="recap.html?fid={fid_get}&type={REPORT_TYPE_WAR}">{report_type[1]}</option>
  <option value="recap.html?fid={fid_get}&type={REPORT_TYPE_HERO}">{report_type[2]}</option>
  <option value="recap.html?fid={fid_get}&type={REPORT_TYPE_UNT}">{report_type[3]}</option>
  <option value="recap.html?fid={fid_get}&type={REPORT_TYPE_ALLI}">{report_type[4]}</option>
  <option value="recap.html?fid={fid_get}&type={REPORT_TYPE_GEN}">{report_type[5]}</option>
  <option value="recap.html?fid={fid_get}&type={REPORT_TYPE_VLG}">{report_type[6]}</option>
  <option value="recap.html?fid={fid_get}&type={REPORT_TYPE_GAME}">{report_type[7]}</option>
  <option value="recap.html?fid={fid_get}&type={REPORT_TYPE_COM}">{report_type[8]}</option>
  <option value="recap.html?fid={fid_get}&type={REPORT_TYPE_MSG}">{report_type[9]}</option>
  <option value="recap.html?fid={fid_get}&type={REPORT_TYPE_ELSE}">{report_type[10]}</option>
</select>
Retour forum <if cond="{fid_get} == {FORUM_BUG_FID}"><a href="forum-topic.html?fid={FORUM_BUG_FID}" title="Forum bugs"> Rapport de bugs </a></if>
<elseif cond="{fid_get} == {FORUM_SUGGEST_FID}"><a href="forum-topic.html?fid={FORUM_SUGGEST_FID}" title="Forum suggestion"> Suggestions</a></else>
<table class="liste">
	<tr>
		<th>Etat</th>
		<th>Auteur</th>
		<th>Sujet</th>
  </tr>
  <br/>
  <if cond="{empty}">Il n'y a rien dans cette rubrique</if>
  <else>
	<foreach cond="{recap_array} as {fid}">
		<tr >			
			<td><img src="img/forum/{fid[statut]}.png" title="{report_statut[{fid[statut]}]}" /></td>
			<td>{fid[poster]}</td>
			<td><a href="forum-<math oper="str2url({fid[subject]})"/>.html?tid={fid[id]}" title="Nouvelle News!" >{fid[subject]}</a></td>
		</tr>
	</foreach>
</else>
</table>