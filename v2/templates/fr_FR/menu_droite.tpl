<if cond='{ses_loged}'> 
	<if cond='{ses_can_play} AND {ses_mbr_etat_ok}'>
		<div class="menu_gauche">
		<h2><label for="menu4">Discord</label></h2>
		<input id="menu1" name="menu" type="radio" />
		<ul>
			<li>
				Ouvrir widget <img id="smileys" src="img/plus.png" alt="Tous les smileys" class="toggle" />
			<div id="smileys_toggle" style="display: none;">
				<iframe src="https://discordapp.com/widget?id=399295659838275587&theme=dark&username={_user[pseudo]}" width="350" height="500" allowtransparency="true" frameborder="0"></iframe>
			</div>
			</li>	
		</ul>
		</div>
	</if>
</if>