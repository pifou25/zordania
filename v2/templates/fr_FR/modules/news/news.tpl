<if cond="!{_user[loged]}">
	<p class="infos">C'est votre première venue ? <a href="a_propos-whatiszord.html">C'est quoi Zordania ?</a></p>
</if>

<h3 class="head_forum">
	<a href="forum.html">Forums</a>
	<if cond="isset({frm})">
		<img src="img/right.png" /> <a href="forum.html?cid={frm[cid]}">{frm[cat_name]}</a>
		<img src="img/right.png" /> <a href="forum-topic.html?fid={frm[fid]}">{frm[forum_name]}</a>

        <foreach cond="{nwss->links} as {page}">
            <if cond='is_numeric({page})'>
                <a href="news.html?page={page}" title="page {page}">{page}</a>
            </if>
            <else>{page}</else>
        </foreach>
	</if>
</h3>

<if cond='!empty({nwss->get})'>
	<foreach cond='{nwss->get} as {nws}'>
		<set name="post" value="{posts_array[{nws[first_pid]}]}"/>

		<div class="block_forum" id="{post[pid]}">
			<img class="blason" title="{post[username]}" src="img/mbr_logo/{post[poster_id]}.png" />
			<a class="titre" href="forum-<math oper="Template::str2url({post[subject]})"/>.html?pid={post[pid]}#{post[pid]}">{post[subject]}</a>

			<p class="post"><math oper="Parser::parse({post[message]})" /></p>
			<if cond='{post[edited]}'>
				<p><em>édité par {post[edited_by]} le {post[edited]}</em></p>
			</if>

			<p>
			<a href="forum-rep.html?tid={post[tid]}&qt={post[pid]}"><img src="img/forum/post.png"  title="citer"/></a>
			<if cond='{is_modo} || ({_user[mid]} == {post[poster_id]})'>
				<a href="forum-post.html?sub=conf&pid={post[pid]}">
					<img src="img/drop.png" alt="Supprimer" title="Supprimer" />
				</a>
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
			- <a href="forum-rep.html?tid={nws[tid]}">{nws[num_replies]} commentaires</a>
			</p>

			<if cond="!empty({post[mbr_sign]})"><p class="signature">{post[mbr_sign]}</p></if>
		</div>
	</foreach>

        <foreach cond="{nwss->links} as {page}">
            <if cond='is_numeric({page})'>
                <a href="news.html?page={page}" title="page {page}">{page}</a>
            </if>
            <else>{page}</else>
        </foreach>
</if>

<else>
	<p class="error">Il n'y a pas de news pour le moment.</p>
</else>
