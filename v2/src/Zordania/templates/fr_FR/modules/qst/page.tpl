<h3>Mes Quêtes</h3>

<if cond="{quete}">
<include file="modules/qst/view.tpl" quete="{quete}" cache="1" />

<p class="infos">Vous pouvez retrouver votre quête en cours dans la page "Mon Compte" / "Mes Quêtes".
    <br/>
    <input type="checkbox" name="read" id="read" value="read" />
    <label for="read">Ne plus afficher cette quête.</label>
</p>
</if>
<else><p class="infos">Aucune quête disponible.</p></else>

<if cond="!empty({hist})">
<h3>Quêtes Terminées</h3>
    <table>
        <caption>Quêtes Terminées</caption>
        <tr>
        <th>Date Début</th>
        <th>Date Fin</th>
        <th>Quête</th>
        </tr>
    <foreach cond='{hist} as {value}'>
        <tr>
            <td>{value[created_at]}</td>
            <td>{value[finished_at]}</td>
            <td>{value[cfg_subject]}</td>
        </tr>
    </foreach>
    </table>
</if>