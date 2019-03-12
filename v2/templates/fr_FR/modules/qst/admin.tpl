<p class="menu_module">
<a href="">Quêtes disponibles</a> <a href="/admin-exp.html?module=qst">Export des Quêtes</a>
</p>

<if cond='isset({post})'>
    <div class="block_forum" id="{post[pid]}">
        {post[poster]}
        <img class="blason" title="{post[poster]}" src="img/mbr_logo/{post[poster_id]}.png" />
        <p class="post"><math oper="parse({post[message]})" /></p>
    </div>
</if>

<elseif cond="!empty({topics})">
    
    <table class="liste">
    <tr>
        <th>Sujet</th>
        <th>Auteur</th>
        <th>Rép</th>
        <th>Vue</th>
        <th>Dernière action</th>
    </tr>

    <foreach cond="{topics} as {topic}">
    <tr>

        <td>
        <a href="admin.html?module=qst&amp;tid={topic[tid]}" title="Début du message">{topic[subject]}</a>
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

</elseif>

