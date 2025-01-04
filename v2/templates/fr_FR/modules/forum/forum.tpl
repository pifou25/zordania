<p class="menu_module">
<a href="forum.html" title="Sommaire du forum"> Sommaire </a>
<a href="forum-search.html" title="Rechercher"> Rechercher </a>
<a href="forum-search.html?action=show_new" title="Nouvaux messages depuis la dernière connexion"> Nouveaux </a>
<a href="forum-search.html?action=show_24h" title="Nouvaux messages depuis hier"> 24H </a>
<a href="a_propos.html" title="Equipe de modération"> Modérateurs </a>
</p>


<if cond='{frm_act} == "rep"'>
	<if cond="isset({no_perm})"><p class="error">Catégorie introuvabe, ou permission refusée pour cette catégorie</p></if>
	<else>
	<h3 class="head_forum">
		<a href="forum.html">Forums</a>
		<img src="img/right.png" /> <a href="forum-cat.html?cid={frm[cid]}">{frm[cat_name]}</a>
		<img src="img/right.png" /> <a href="forum-topic.html?fid={frm[fid]}".html>{frm[forum_name]}</a>
		<if cond='{new} != "topic"'>
			<img src="img/right.png" /> <a href="forum-<math oper="str2url({pst[subject]})"/>.html?tid={pst[tid]}">{pst[subject]}</a>
		</if>
	</h3>

	<!-- formulaire de réponse / topic / édit -->

	<form class="center" action="{form_url}" method="post" id="newpost">
		<if cond='{new} == "topic"'>
			<label for="pst_titre">Sujet : <input id="pst_titre" name="pst_titre" type="text" size="60" /></label>
		</if>
		<elseif cond='{new} == "edit" && ({is_modo} || {pst[pid]} == {pst[first_pid]})'>
			<label for="pst_titre">Sujet : <input id="pst_titre" name="pst_titre" type="text" size="60" value="{pst[subject]}" /></label>
		</elseif>
		<else>
			<input type="hidden" id="pst_titre" name="pst_titre" value="{pst[subject]}" />
		</else>

		<include file='commun/bbcode.tpl' cache='1' />
		<textarea id="message" name="pst_msg" rows="10" cols="60"><if cond="isset({pst[message]})">{pst[message]}</if><elseif cond="isset({quote})">[quote={quote[poster]}]{quote[message]}[/quote]</elseif></textarea><br />

		<if cond="{is_modo}">
		<label for="closed"> Fermé :<input type="checkbox" name="closed" id="closed" <if cond='isset({pst[closed]}) && {pst[closed]} == 1'>checked="checked"</if> /></label>
		<label for="sticky"> Annonce :<input type="checkbox" name="sticky" id="sticky" <if cond='isset({pst[sticky]}) && {pst[sticky]} == 1'>checked="checked"</if> /></label>
		<if cond='{new} == "edit"'>
			<if cond='{frm[cid]} == "1"'><label for="silent"> Modification discrète :<input type="checkbox" name="silent" id="silent" checked="checked" /></label></if>

			<label for="move">Déplacer le sujet :
			<select id="move" name="move">
				<option value="-1">Ne pas déplacer</option>
				<foreach cond="{cat_array} as {cid} => {cat}">
					<optgroup label="{cat[cat_name]}">
					<foreach cond="{cat[frm]} as {forum}">
						<option value="{forum[fid]}">{forum[forum_name]}</option>
					</foreach>
					</optgroup>
				</foreach>
			</select>
			</label>
			<if cond="{pst[forum_id]} == FORUM_BUG_FID || {pst[forum_id]} == FORUM_SUGGEST_FID ">	
				<label for="statut">Statut : </label>				
					<select id="statut" name="statut">
						<option value="-1" >Laisser</option>
						<option value="{REPORT_STATUT_TALK}">En discussion</option>
						<option value="{REPORT_STATUT_OK}">Valider</option>
						<option value="{REPORT_STATUT_NOK}">Refuser</option>
						<option value="{REPORT_STATUT_DUBL}">Doublon</option>
						<option value="{REPORT_STATUT_DEV}">Ok en DEV</option>
						<option value="{REPORT_STATUT_ON}">Codé/corrigé</option>							
					</select>
				<label for="statut">Type : </label>				
					<select id="type" name="type">
						<option value="-1" >Laisser</option>
						<option value="{REPORT_TYPE_WAR}">Leg/War</option>
						<option value="{REPORT_TYPE_HERO}">Héros/Comp</option>
						<option value="{REPORT_TYPE_UNT}">Unités</option>
						<option value="{REPORT_TYPE_ALLI}">All/Diplo</option>
						<option value="{REPORT_TYPE_GEN}">Donjon</option>
						<option value="{REPORT_TYPE_VLG}">Village</option>
						<option value="{REPORT_TYPE_GAME}">Inter/GameP</option>
						<option value="{REPORT_TYPE_COM}">Marché</option>
						<option value="{REPORT_TYPE_MSG}">Message</option>
						<option value="{REPORT_TYPE_ELSE}">Autres</option>						
					</select>
				</if><br />
		</if>
		</if>
<#
		<if cond='{new} == "topic"'>
			<label for="sondage">Sondage : <input type="checkbox" name="sondage" id="sondage" /></label><br/>
			<label for="sdg_rep1">Réponse 1 : <input id="sdg_rep[]" name="sdg_rep[]" type="text" size="60" value="" /></label><br/>
			<label for="sdg_rep2">Réponse 2 : <input id="sdg_rep[]" name="sdg_rep[]" type="text" size="60" value="" /></label><br/>
			<label for="sdg_rep3">Réponse 3 : <input id="sdg_rep[]" name="sdg_rep[]" type="text" size="60" value="" /></label><br/>
			<label for="sdg_rep4">Réponse 4 : <input id="sdg_rep[]" name="sdg_rep[]" type="text" size="60" value="" /></label><br/>
		</if>
#>
		<input type="submit" value="Envoyer" />
		<input type="button" id="btpreview" value="Prévisualiser" />
	</form>

	<div id="preview"></div>

		<if cond='isset({messages})'>	
		<h3>Derniers Posts :</h3>
		<foreach cond='{messages} as {post}'>
			<div class="block_forum">
			<img class="blason" title="{post[username]}" src="img/mbr_logo/{post[poster_id]}.png" />
			<a href="forum-<math oper="str2url({post[subject]})"/>.html?pid={post[pid]}#{post[pid]}">{post[subject]}</a>

			<p><math oper='parse({post[message]})' /></p>
			<if cond='{post[edited]}'>
				<p><em>édité par {post[edited_by]} le {post[edited]}</em></p>
			</if>

			<p>
			<a href="#" pid="{post[pid]}" class="jqquote" title="citer"><img src="img/forum/post.png" /></a>
			<a href="forum-post.html?pid={post[pid]}#{post[pid]}">le {post[posted]}</a> par
			<if cond="{post[mbr_gid]}">
				<zurlmbr gid="{post[mbr_gid]}" mid="{post[poster_id]}" pseudo="{post[username]}"/>
				<if cond='{post[al_aid]}'>
					- <a href="alliances-view.html?al_aid={post[al_aid]}" title="Infos sur {post[al_name]}"><img src="img/al_logo/{post[al_aid]}-thumb.png" class="mini_al_logo" /></a>
				</if>
			</if>
			<else>{post[username]}</else>
			</p>
			</div>
		</foreach>
		</if>
	</else>
</if>


<elseif cond='{frm_act} == "post"'>
	<!-- liste des posts -->
	<if cond='isset({conf})'>
		<form class="center" action="forum-post.html?sub=del&pid={pid}" method="post">
		<p class="infos">Voulez vous vraiment supprimer ce message ?
			<input type="submit" name="Oui" value="Oui" />
			<input type="submit" name="Non" value="Non" /></p>
		</form>
	</if>

	<if cond='isset({cant_create})'>
		<p class="error">Vous n'avez pas le droit de créer un topic ici.</p>
	</if>
	<elseif cond='isset({tpc_vide})'>
		<p class="error">Il manque un titre ou un message à votre topic !</p>
	</elseif>
	<elseif cond='isset({tpc_f5})'>
		<p class="error">Votre dernier message était le même !</p>
	</elseif>
	<elseif cond='isset({cant_post})'>
		<p class="error">Vous n'avez pas le droit de poster ici !</p>
	</elseif>
	<elseif cond='isset({pst_vide})'>
		<p class="error">Il n'y a pas de texte dans votre message !</p>
	</elseif>
	<elseif cond='isset({post_f5})'>
		<p class="error">Vous avez déjà  envoyé ce message !</p>
	</elseif>
	<elseif cond='isset({cant_edit})'>
		<p class="error">Vous n'avez pas le droit d'éditer ce message</p>
	</elseif>
	<elseif cond='isset({edit})'>
		<p class="ok">Votre message a bien été édité.</p>
	</elseif>
	<elseif cond='isset({cant})'>
		<p class="error">Vous n'avez pas le droit de faire cela.</p>
	</elseif>
	<elseif cond='isset({edit_tpc})'>
		<p class="ok">Le topic a été modifié.</p>
	</elseif>
	<elseif cond='isset({stick})'>
		<if cond="{stick}"><p class="ok">le topic a bien été mis en annonce.</p></if>
		<else><p class="ok">Ce topic n'est plus une annonce.</p></else>
	</elseif>
	<elseif cond='isset({close})'>
		<if cond="{close}"><p class="ok">Ce topic est maintenant fermé.</p></if>
		<else><p class="ok">Ce topic n'est plus fermé.</p></else>
	</elseif>
	<elseif cond='isset({cant_del})'>
		<p class="error">Vous n'avez pas le droit de supprimer ce message.</p>
	</elseif>
	<elseif cond='isset({del})'>
		<p class="ok">Votre message a bien été suprimé</p>
	</elseif>
	<elseif cond='isset({cant_read})'>
		<p class="error">Vous n'avez pas le droit de lire ce topic !</p>
	</elseif>
	<elseif cond='isset({empty})'>
		<p class="error">Erreur ! discussion inexistante...</p>
	</elseif>

	<if cond='isset({messages})'>
		<h3 class="head_forum"><a href="forum.html">Forums</a> <img src="img/right.png" /> <a href="forum.html?cid={tpc[cid]}">{tpc[cat_name]}</a> <img src="img/right.png" /> <a href="forum-topic.html?fid={tpc[forum_id]}">{tpc[forum_name]}</a>  <img src="img/right.png" /> <a href="forum-<math oper="str2url({tpc[subject]})"/>.html?tid={tpc[tid]}">{tpc[subject]}</a></h3>

		<if cond="{tpc[cid]} == 1">
			<!-- AddThis Button BEGIN -->
			<div class="addthis_toolbox addthis_default_style blason block">
			<a class="addthis_button_preferred_1"></a>
			<a class="addthis_button_preferred_2"></a>
			<a class="addthis_button_preferred_3"></a>
			<a class="addthis_button_preferred_4"></a>
			<a class="addthis_button_compact"></a>
			<a class="addthis_counter addthis_bubble_style"></a>
			</div>
			<script type="text/javascript">var addthis_config = { "data_track_clickback" : true };</script>
			<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4deb7c502858e825"></script>
			<!-- AddThis Button END -->
		</if>

		<p class="menu_module">
			<a href="#lst_pst" id="fst_pst">
			<img src="img/down.png" alt=" " /> Fin</a>
			<if cond='{tpc[post_topics]} || {is_modo}'>
				- <img src="img/forum/topic.png" alt="Nouveau topic" title="Nouveau Sujet"/> <a href="forum-rep.html?fid={tpc[forum_id]}"> Nouveau Sujet</a>
			</if>
			<if cond="({tpc[post_replies]} && !{tpc[closed]}) || {is_modo}">
				- <img src="img/reply.png" alt="Répondre" title="Répondre"/> <a href="forum-rep.html?tid={tpc[tid]}"> Répondre</a>
			</if>
		</p>

		<if cond="isset({arr_pge})">
			<p class="pages">
			<foreach cond="{arr_pge} as {i}">
				<if cond='{i} == {pge} || {i} == "..."'> {i} </if>
				<else> <a href="forum-post.html?tid={tpc[tid]}&p={i}" title="page {i}">{i}</a> </else>
			</foreach>
			</p>
		</if>

		<foreach cond='{messages} as {post}'>
   			<div class="block_forum" id="{post[pid]}">
   				<img class="blason" title="{post[username]}" src="img/mbr_logo/{post[poster_id]}.png" />
				<a class="titre" href="forum-<math oper="str2url({tpc[subject]})"/>.html?pid={post[pid]}#{post[pid]}">{tpc[subject]}</a>

				<p class="post"><math oper="parse({post[message]})" /></p>
				<if cond='{post[edited]}'>
					<p class="edited"><em>édité par {post[edited_by]} le {post[edited]}</em></p>
				</if>

				<p class="author">
				<a href="forum-rep.html?tid={tpc[tid]}&qt={post[pid]}"><img src="img/forum/post.png"  title="citer"/></a>
				<if cond='{is_modo}'>
					<a href="forum-post.html?sub=conf&pid={post[pid]}">
						<img src="img/drop.png" alt="Supprimer" title="Supprimer" />
					</a>
				</if>
				<if cond='{is_modo} || ({mid} == {post[poster_id]})'>
					<a href="forum-rep.html?sub=edit&pid={post[pid]}">
						<img src="img/editer.png" alt="Editer" title="Editer" />
					</a>
				</if>
                                                            
				<a href="forum-post.html?pid={post[pid]}#{post[pid]}">le {post[posted]}</a> par 
				<if cond="{post[mbr_gid]}">
					<zurlmbr gid="{post[mbr_gid]}" mid="{post[poster_id]}" pseudo="{post[username]}"/>
					<if cond='{post[al_aid]}'>
						- <a href="alliances-view.html?al_aid={post[al_aid]}" title="Infos sur {post[al_name]}"><img src="img/al_logo/{post[al_aid]}-thumb.png" class="mini_al_logo"/> </a>
					</if>
				</if>
				<else>{post[username]}</else>
				</p>

				<if cond="!empty({post[mbr_sign]})"><p class="signature">{post[mbr_sign]}</p></if>
			</div>
		</foreach>


		<if cond="{tpc[cid]} == 1">
			<!-- AddThis Button BEGIN -->
			<div class="addthis_toolbox addthis_default_style blason block">
			<a class="addthis_button_preferred_1"></a>
			<a class="addthis_button_preferred_2"></a>
			<a class="addthis_button_preferred_3"></a>
			<a class="addthis_button_preferred_4"></a>
			<a class="addthis_button_compact"></a>
			<a class="addthis_counter addthis_bubble_style"></a>
			</div>
			<!-- AddThis Button END -->
		</if>

		<p class="menu_module">
		<a href="#fst_pst" id="lst_pst"><img src="img/up.png" alt=" " /> Début</a>
		<if cond='{tpc[post_topics]} || {is_modo}'>
			- <img src="img/forum/topic.png" alt="Nouveau topic" title="Nouveau Sujet"/> <a href="forum-rep.html?fid={tpc[forum_id]}"> Nouveau Sujet</a>
		</if>
		<if cond="({tpc[post_replies]} && !{tpc[closed]}) || {is_modo}">
			- <img src="img/reply.png" alt="Répondre" title="Répondre"/> <a href="forum-rep.html?tid={tpc[tid]}"> Répondre</a>
		</if>
		</p>

		<if cond="isset({arr_pge})">
			<p>
			<foreach cond="{arr_pge} as {i}">
				<if cond='{i} == {pge} || {i} == "..."'> {i} </if>
				<else> <a href="forum-post.html?tid={tpc[tid]}&p={i}" title="page {i}">{i}</a> </else>
			</foreach>
			</p>
		</if>



		<if cond='{is_modo}'><!-- panel de modération -->
		<form class="center" action="forum-post.html?sub=modo&tid={tpc[tid]}" method="post" >
			<p>
				<label for="close">Fermé : </label><input type="checkbox" <if cond='{tpc[closed]}'>checked="checked"</if> name="close" id="close"/>
				<label for="stick">Annonce : </label><input type="checkbox" <if cond='{tpc[sticky]}'>checked="checked"</if> name="stick" id="stick"/>
				<label for="move">Déplacer dans : </label>

				<select id="move" name="move">
				<option value="-1" >Ne pas déplacer</option>
				<foreach cond="{cat_array} as {cid} => {cat}">
					<optgroup label="{cat[cat_name]}">
					<foreach cond="{cat[frm]} as {forum}">
						<option value="{forum[fid]}">{forum[forum_name]}</option>
					</foreach>
					</optgroup>
				</foreach>
				</select>				
				
				<if cond="{tpc[forum_id]} == FORUM_BUG_FID || {tpc[forum_id]} == FORUM_SUGGEST_FID ">	
				<label for="statut">Statut : </label>				
					<select id="statut" name="statut">
						<option value="-1" >Laisser</option>
						<option value="{REPORT_STATUT_TALK}">En discussion</option>
						<option value="{REPORT_STATUT_OK}">Valider</option>
						<option value="{REPORT_STATUT_NOK}">Refuser</option>
						<option value="{REPORT_STATUT_DUBL}">Doublon</option>
						<option value="{REPORT_STATUT_DEV}">Ok en DEV</option>
						<option value="{REPORT_STATUT_ON}">Codé/corrigé</option>						
					</select>
				<label for="statut">Type : </label>				
					<select id="type" name="type">
						<option value="-1" >Laisser</option>
						<option value="{REPORT_TYPE_WAR}">Leg/War</option>
						<option value="{REPORT_TYPE_HERO}">Héros/Comp</option>
						<option value="{REPORT_TYPE_UNT}">Unités</option>
						<option value="{REPORT_TYPE_ALLI}">All/Diplo</option>
						<option value="{REPORT_TYPE_GEN}">Donjon</option>
						<option value="{REPORT_TYPE_VLG}">Village</option>
						<option value="{REPORT_TYPE_GAME}">Inter/GameP</option>
						<option value="{REPORT_TYPE_COM}">Marché</option>
						<option value="{REPORT_TYPE_MSG}">Message</option>
						<option value="{REPORT_TYPE_ELSE}">Autres</option>						
					</select>
				</if>

				<input type="submit" name="modo" value="Effectuer" />
			</p>
		</form>
		</if>


		<h3 class="head_forum"><a href="forum.html">Forums</a> <img src="img/right.png" /> <a href="forum.html?cid={tpc[cid]}">{tpc[cat_name]}</a> <img src="img/right.png" /> <a href="forum-topic.html?fid={tpc[forum_id]}".html>{tpc[forum_name]}</a>  <img src="img/right.png" /> <a href="forum-<math oper="str2url({tpc[subject]})"/>.html?tid={tpc[tid]}">{tpc[subject]}</a></h3> 
	</if>
</elseif>

<elseif cond='{frm_act} == "search"'>
	<!-- formulaire de recherche / résultats -->
	<include file="modules/forum/search.tpl" cache="1" />
</elseif>

<elseif cond='{frm_act} == "topic"'>
	<!-- liste des sujets (topic) pour un forum -->
	<if cond='isset({bad_fid})'><p class="error">Forum inexistant</p></if>
	<elseif cond='isset({cant_read})'><p class="error">Accès interdit !</p></elseif>
	<else>
		<if cond='isset({cant_create})'>
			<p class="error">Vous n'avez pas le droit de créer un topic ici ! </p>
		</if>
		<elseif cond='isset({tpc_f5})'>
			<p class="error">Vous avez déjà  envoyé ce message ! </p>
		</elseif>

		<h3 class="head_forum"><a href="forum.html">Forums</a> <img src="img/right.png" /> <a href="forum.html?cid={frm[cid]}">{frm[cat_name]}</a> <img src="img/right.png" /> <a href="forum-topic.html?fid={frm[fid]}".html>{frm[forum_name]}</a></h3>
		<p class="menu_module">
			<img src="img/forum/topic.png" alt="Nouveau topic" title="Nouveau Sujet"/> <a href="forum-rep.html?fid={frm[fid]}">Nouveau Sujet</a>
		</p>

		<if cond="empty({topic_array})"><p class="infos">Forum vide ... Soyez le premier à poster !</p></if>
		<else>
				<if cond="{frm[fid]} == FORUM_BUG_FID ">				
					<a href="recap.html?fid={FORUM_BUG_FID}&type=0" title="Récap bugs"> Récapitulatif des bugs </a>			
				</if>
				<elseif cond="{frm[fid]} == FORUM_SUGGEST_FID  ">				
					<a href="recap.html?fid={FORUM_SUGGEST_FID}&type=0" title="Récap Suggestions">Récapitulatif des Suggestions </a>			
				</elseif>
		<if cond="isset({arr_pge})">
			<p>
			<foreach cond="{arr_pge} as {i}">
				<if cond='{i} == {pge} || {i} == "..."'> {i} </if>
				<else> <a href="forum-topic.html?fid={frm[fid]}&p={i}" title="page {i}">{i}</a> </else>
			</foreach>
			</p>
		</if>

			<table class="liste">
			<tr>
				<th></th>
				<th>Sujet</th>
				<th>Auteur</th>
				<th>Rép</th>
				<th>Vue</th>
				<th>Dernière action</th>
			</tr>

			<foreach cond="{topic_array} as {topic}">
			<tr>
				<td><!-- Image pour l'état du topic -->				
				
				<if cond="({lu_forum_ldate} > {topic[posted_unformat]}) || isset({forum_lus[{topic[tid]}]})"><set name="etat" value="lu" /></if>
				<else><set name="etat" value="non_lu" /></else>
				<if cond="{topic[sticky]} == 1 AND {topic[closed]} == 1"><img src="img/forum/sticky-closed-{etat}.png" title="Post-it Fermé - {etat}" /></if>
				<elseif cond="{topic[closed]} == 1"><img src="img/forum/closed-{etat}.png" title="Fermé - {etat}" /></elseif>
				<elseif cond="{topic[sticky]} == 1"><img src="img/forum/sticky-{etat}.png" title="Post-it - {etat}" /></elseif>
				<else><img src="img/forum/{etat}.png" title="{etat}" /></else>
				<if cond="{topic[forum_id]} == FORUM_BUG_FID || {topic[forum_id]} == FORUM_SUGGEST_FID  ">				
					<img src="img/forum/{topic[statut]}.png" title="{report_statut[{topic[statut]}]}" />				
				</if>

				</td>

		        	<td><if cond="({lu_forum_ldate} < {topic[posted_unformat]}) && !isset({forum_lus[{topic[tid]}]})"><img src='img/reply.png' title='Nouveau' alt='Nouveau' /> </if>
				<a href="forum-<math oper="str2url({topic[subject]})"/>.html?tid={topic[tid]}" title="Début du message">{topic[subject]}</a>
				<if cond="isset({topic[arr_pgs]})">
					<br />[Page: 
					<foreach cond="{topic[arr_pgs]} as {i}">
						<if cond='{i} == "..."'> ... </if>
						<else><a href="forum-<math oper="str2url({topic[subject]})"/>.html?tid={topic[tid]}&p={i}" title="page {i}"> {i} </a></else>
					</foreach>]
				</if>
				</td>
				<td><if cond="isset({topic[auth_mid]})">
					<zurlmbr gid="{topic[auth_gid]}" mid="{topic[auth_mid]}" pseudo="{topic[poster]}"/>
				</if>
				<else> {topic[poster]}</else>
				</td>
			        <td>{topic[num_replies]}</td>
			        <td>{topic[num_views]}</td>
			        <td>{topic[last_post]}<br />par 
				<if cond="isset({topic[last_poster_mid]})">
					<zurlmbr gid="{topic[last_poster_gid]}" mid="{topic[last_poster_mid]}" pseudo="{topic[last_poster]}"/>
				</if>
				<else>{topic[last_poster]}</else>
				[<a href="forum-<math oper="str2url({topic[subject]})"/>.html?tid={topic[tid]}&pid={topic[last_post_id]}#{topic[last_post_id]}" title="Dernier message"><img src="img/right.png" /></a>]</td>
		        </tr>
			</foreach>
		</table>

		<if cond="isset({arr_pge})">
			<p>
			<foreach cond="{arr_pge} as {i}">
				<if cond='{i} == {pge} || {i} == "..."'> {i} </if>
				<else> <a href="forum-topic.html?fid={frm[fid]}&p={i}" title="page {i}">{i}</a> </else>
			</foreach>
			</p>
		</if>

		</else>

		<p class="menu_module">
			<img src="img/forum/topic.png" alt="Nouveau topic" title="Nouveau Sujet"/> <a href="forum-rep.html?fid={frm[fid]}">Nouveau Sujet</a>
		</p>


	</else>
</elseif>

<else>
	<if cond="count({cat_array})>1">
	<!-- ancres des catégories -->
		<h3><foreach cond="{cat_array} as {cid} => {cat}">
		<a href="#cid{cid}" title="{cat[cat_name]}">{cat[cat_name]}</a>
		</foreach></h3>
	</if>

	<!-- liste des catégories / forums -->
	<foreach cond="{cat_array} as {cid} => {cat}">
		<h3 class="head_forum"><a id="cid{cid}" href="forum.html?cid={cid}">{cat[cat_name]}</a></h3>
		<foreach cond="{cat[frm]} as {forum}">
		<div class="forum">
			<h4><a href="forum-topic.html?fid={forum[fid]}" title="{forum[forum_name]}">{forum[forum_name]}</a></h4>
			<p class="desc">{forum[forum_desc]}</p>
            <if cond="{forum[num_topics]}>0">
                <p class="stat">{forum[num_topics]} sujets - {forum[num_posts]} messages<br/>
                <if cond='{lu_forum_ldate} <= {forum[last_post_unformat]}'><img src='img/forum/non_lu.png' title='Nouveau' alt='Nouveau' /></if>
                <else><img src='img/forum/lu.png' title='Nouveau' alt='Nouveau' /></else>
                Dernier message : <a href="forum-<math oper="str2url({forum[last_subject]})"/>.html?pid={forum[last_post_id]}#{forum[last_post_id]}" title="Dernier message">{forum[last_subject]}</a> le {forum[last_post]} par 
                <if cond="isset({forum[mbr_mid]})">
                    <zurlmbr gid="{forum[mbr_gid]}" mid="{forum[mbr_mid]}" pseudo="{forum[last_poster]}"/>
                </if>
                <else>{forum[last_poster]}</else></p>
            </if><else><p class="stat">aucun message</p></else>
		</div>
	</foreach>
	</foreach>
</else>

<p class="menu_module">
<a href="forum.html"> Retour </a>
<if cond="{is_admin}">- <a href="/forums/admin_index.php" title="administrer le forum"> Administration </a></if>
</p>
