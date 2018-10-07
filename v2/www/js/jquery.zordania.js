/* zordania fonctions jQuery */

$(document).ready(  function()
{
	// mobile or desktop design
	var isMobile  = isVisible('#bp_mobile');

	/* envoyer le device au script php */
	if (typeof mobilePhp == "undefined" || mobilePhp != isMobile) {
		$.ajax({
			url: 'index.php',
			data: 'mobile='+ isMobile,
			success: function(reponse) {
				console.log(reponse);
			}
		});
	}
	
	/* forcer les valeurs du menu avec celles du fond 
	 * 	$('.menu_gauche').css('background-color', $('body').css('background-color'));
	 * 	$('.menu_gauche ul').css('background-color', $('#module').css('background-color'));
	 * */

	/* animation pour le menu lateral - en mode mobile */
	var isMenuOpen = false;

	$('#openmenulat').click(function()
	{
		if (isMenuOpen == false)
		{
			$("#menu").clearQueue().animate({
				left : '2px'
			})
			isMenuOpen = true;
		}
		else
		{
			$("#menu").clearQueue().animate({
				left : '-260px'
			})
			isMenuOpen = false;
		}
	});
	

	/*
	 * la class="toggle" sur un lien ou une image
	 * affiche / masque un autre item
	 */
	initToggle();

	// Lorsqu'un button #preview est cliqué
	// forum msg note shoot et shoot diplo
	$("input#btpreview").click(function ()
	{
		$.post(
			cfg_url+"ajax--forum-post.html", 
			$("form#newpost").serialize(), 
			function(txt){ $("#preview").html(txt); }
		);
	});


    // Commerce - achat : module/fr/btc/inc/com.tpl - Lorsqu'un lien a.com est cliqué
    $("table#showComForm a").each(function(){
		$(this).click(function(){
            var url = $(this).attr('href');
            var output = $(this).parents('tr');
            var len = output.children('td').length;
            output.html('<td colspan="' + len + '"></td>');
			jqShowMod(url, output.children());
			return false;
		});
	});
        
	// ce script pilote le popup de confirmation (leg/act.tpl)
	$("#make_atq").click(function(){ // au clic sur le lien
            $("#dialog-text").text("Confirmez vous l'attaque ?");
		$("#dialog-modal").dialog({ // popup
			buttons: [{
				text: "Annuler", // bouton annuler
				click: function() {
				$( this ).dialog( "close" );}
			},{
				text: "Confirmer", // bouton attaquer renvoie vers le lien #make_atq
				click: function() {
				window.location = $("#make_atq").attr('href');}
			}]
		});
		return false;
	});
  
	// activer le comportement ajax sur les formulaires
	$("form.ajax").submit(funcZrdFormulaire);
    
    // réponse ajax dans une popup
    traiterZrdPopUp();

});


function initToggle(){
	/* un élément avec id et classe 'toggle' permet
     * d'afficher / masquer un élément 'id_toggle'
	 * tous sont masqués au chargement ( style="display: none;" )
	 */

	// Lorsqu'un lien a.toggle est cliqué
	$("a.toggle").click(function() {
		$("#"+$(this).attr('id')+"_toggle" ).toggle('slide');
		return false;
	});
	// Lorsqu'une image .toggle est cliquée
	$("img.toggle").click(function() {
		$("#"+$(this).attr('id')+"_toggle" ).toggle('slide');
		var src=($(this).attr('src')==='img/plus.png'?'img/minus.png':'img/plus.png');
		$(this).attr('src',src);
	});
	
	// remove class to avoid reccursive effects
	$(".toggle").removeClass("toggle");
}

var funcZrdFormulaire = function(event){
	// Stop form from submitting normally
	event.preventDefault();

	// Get some values from elements on the page:
	var $form = $( this ),
	term = $form.serialize(),
	url = "ajax--" + $form.attr( "action" );

	$form.attr("action", url);
	// Send the data using post
	$.post( url, term, function(data){
		var output = $("#dialog-modal").html(data);
		output.find("a.zrdPopUp").click(funcZrdPopup) ;// tous les liens restent dans la popup
		output.dialog({ // popup
			buttons: [{
				text: "Fermer", // bouton annuler
				click: function() {
				$( this ).dialog( "close" );}
			}],
			resizable:false,
			draggable:false,
			title:'Opération terminée',
			hide: {effect: "fadeOut", duration: 1000}
		}, setTimeout(function(){$("#dialog-modal").dialog("close");},3000)
		);
	});
};

/*
* jQuery ajax get
* variable globale = cfg_url
* module = url cible de la requete ajax
* GET, pas de data, la réponse est renvoyée dans le html du jquery ouput
*/
function jqShowMod(module, output) {
	$.ajax({
		url: cfg_url+"ajax--"+module,
		success: function(html) {
            output.html(html);
			// gérer les nouveaux formulaires & popup
			traiterFormulaires();
            traiterZrdPopUp();
		},
		error: function(jqXHR, textStatus, errorThrown){
			output.html('<p class="error">Erreur : ' + textStatus + ' sur le module ' + module + ' - ' + errorThrown + '</p>');
		},
		complete: function(jqXHR, textStatus){
			if(textStatus != 'success')
				output.append('<p class="infos">' + textStatus + '</p>');
		}
	});
	return false;
}

/*
 * pour le déménagement: gen.tpl et member/admin.tpl
 * le formulaire contient map_x et map_y ou map_cid
 * le bouton affiche le preview de map dans #carte_infos
 */
function showMapInfo() {
	var cid = $("#map_cid").val();
	if(cid.length==0){
		var map_x = $("#map_x").val();
		var map_y = $("#map_y").val();
		if(map_x.length==0 && map_y.length==0){
			alert('faut donner le map_cid OU les coordonnées X/Y à vérifier!');
			return false;
		}
		var url = "carte-view.html?map_x=" + map_x + '&map_y=' + map_y;
	}else{
		var url = "carte-view.html?map_cid=" + cid;
	}

	jqShowMod(url,$('#carte_infos'));
	return false;
}

// ce script pilote les - petites - popup ajax (confirmation & co)
// unt.html
function traiterZrdPopUp() {

	// autre popup ajax pour les modules - plus grande. specifique selon le CSS
    if(user_css == 6) {
		// popup specifique
		$("#village .zrdPopUp").click(funcZrdModal);
		$(".liste .zrdPopUp").click(funcZrdModal);
		$("#dialog-modal .zrdPopUp").click(funcZrdModal);
	}else{
		// popup jquery-iu classique
		$("#village .zrdPopUp").click(funcZrdPopup);
		$(".liste .zrdPopUp").click(funcZrdPopup);
		$("#dialog-modal .zrdPopUp").click(funcZrdPopup);
	}
}

var funcZrdPopup = function(){ // au clic sur le lien

	var url = $(this).attr('href');
	var title = $(this).attr('title');
	var output = $("#dialog-modal");
	console.log('popup ' + title + ' vers url=' + url);
	
	$.ajax({
		url: cfg_url+"ajax--"+url,
		dataType:"html",
		success: function(html) {
			output.html(html);
			// remettre les memes comportements sur la reponse ajax
			output.find(".liste .zrdPopUp").click(funcZrdPopup);
			// remettre les toggle
			initToggle();
			// activer le comportement ajax sur les formulaires
			output.find("form.ajax").submit(funcZrdFormulaire);
			// tous les liens restent dans la popup
			output.find("a.zrdPopUp").click(funcZrdPopup);
			
			output.dialog({ // popup
				buttons: [{
					text: "Fermer", // bouton annuler
					click: function() {
					$( this ).dialog( "close" );}
				}],
				resizable:false,
				draggable:false,
				width: 500,
				closeText: ""
			});
			if(title){
				output.dialog("option", "title", title);
			}
			output.dialog('moveToTop');
		}
	});
	return false;
}

var funcZrdModal = function(){ // au clic sur le lien - popup CSS style selendia

	var url = $(this).attr('href');
	var title = $(this).attr('title');
	var output = $("#dialog-modal");
	console.log('module ' + title + ' vers url=' + url);
	var header = '<div class="header"><h3>' + title + '</h3></div><div class="close" onclick="$(\'#dialog-modal\').hide();"></div>';
	
	$.ajax({
		url: cfg_url+"module--"+url,
		dataType:"html",
		success: function(html) {
			output.html(header + '<div class="centre">' + html + '</div>');
			// remettre les memes comportements sur la reponse ajax
			output.find(".liste .zrdPopUp").click(funcZrdPopup);
			// remettre les toggle
			initToggle();
			
			traiterFormulaires();
			output.show();
		}
	});
	return false;
}

// check visibility of an element
// used to detect desktop or mobile responsive design
var isVisible = function(element) {
	return $(element).is(':visible');
};