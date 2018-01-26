<if cond='empty({irc_chat})'>

	<h2>Vous aussi, partagez votre passion avec vos amis !</h2>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/fr_FR/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<div class="fb-like" data-href="https://www.facebook.com/pages/Zordania/487159031358707" data-send="true" data-layout="button_count" data-width="450" data-show-faces="false" data-font="arial" data-colorscheme="dark"></div>

	<br/>

	<a href="http://twitter.com/share" class="twitter-share-button" data-url="{SITE_URL}inscr.html?parrain={_user[mid]}" data-text="Je suis devenu le maitre sur Zordania, viendra-tu te mesurer à moi?" data-count="horizontal" data-via="Zordania" data-lang="fr">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>

	<br/>
	
	<p class="infos">NEW : la communauté se retrouve maintenant sur Discord, suivez le lien d'invitation: <a href="https://discord.gg/fjGXrkY" title="for the gamers">Discord app</a></p>
	
	<hr/>

	<p class="infos">
	<strong>Serveur:</strong> irc.quakenet.org<br/>
	<strong>Port:</strong> 6667<br/>
	<strong>Chan:</strong> #zordania<br/>
	</p>
	<p>
	Pour accéder au chat vous devez disposer d'un logiciel permettant de vous connecter à l'IRC comme <a href="http://mirc.com">mIRC</a>, <a href="http://www.xchat.org">xchat</a>.
	</p>
	
	<div class="menu_module">
	<a href="module--irc-webchat.html" target="_blank" title="Aucune installation nécessaire !">Utiliser l'interface web</a>
	- <a href="irc://irc.quakenet.org/zordania" title="Rejoindre le chat avec mon logiciel habituel">Utiliser mon logiciel habituel</a>
	- <a href="https://discord.gg/fjGXrkY" title="for the gamers">Discord app</a>
	</div>
</if>

<elseif cond='{irc_chat} == "webchat"'>

	<if cond="{_user[mid]} != 1">
		<iframe src="http://webchat.quakenet.org/?nick={pseudo}&channels=zordania&uio=OT10cnVlde" width="1200" height="800"></iframe>
	</if>
	<else>Vous n'êtes pas connecté !</else>
</elseif>
