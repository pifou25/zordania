<h3>Mes Quêtes</h3>

<if cond="{quete}">
<include file="modules/qst/qst.tpl" quete="{quete}" cache="1" />

<p class="infos">Vous pouvez retrouver votre quête en cours dans la page "Mon Compte" / "Mes Quêtes".
    <br/>
    <input type="checkbox" name="read" id="read" value="read" />
    <label for="read">Ne plus afficher cette quête.</label>
</p>
</if>
<else><p class="infos">Aucune quête disponible.</p></else>