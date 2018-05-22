/* Javascript pour afficher le village */
var decX = 0;
var decY = 0;

// Vlg v1 old version - useless
var Vlg = {
	w: 500,
	h: 350,
	VlgCoords: new Array(),
	SrcCoords: new Array(),
	
	init: function(race, back, user_css) {
		if(race < 1 || race > 7) race = 1;
		/* initialiser les coordonnées par race */
		this.initCoord(race, user_css);

		/* mettre le fond de l'image */
		if(user_css != 6){
			$("#village").css('width', this.w + "px").css('height', this.h + "px")
				.css('backgroundImage', "url(img/"+ race + "/vlg/back" + back + ".png)");
		}
			
		/* positionner les batiments */
		$.each(this.VlgCoords, function(index, value) { 
		  if(value) {
			var img = $("#btc_"+index);
			if(user_css != 6){
				img.attr('src', "img/"+ race + "/vlg/" + back + "/" + index + ".png");
			}
			img.css('top', value[1]+decX).css('left', value[0]+decY).css('position', 'absolute');
		  }
		});

		/* positionner les recherches si y'a */
		$.each(this.SrcCoords, function(index, value) { 
		  if(value) {
			//console.log('recherche:' + index + '=' + value[0] + "-" + value[1]);
			var img = $("#src_"+index);
			img.attr('src', "img/"+ race + "/vlg/src/" + index + ".png");
			img.css('top', value[1] + decX).css('left', value[0] + decY).css('position', 'absolute');
		  }
		});

	},

	/* coordonnées de chaque bâtiment du village par race */
	initCoord: function(race, user_css) {
		switch(race) {
		case 1:
			if(user_css == 6){ // CSS specifique
				this.VlgCoords[1] = new Array(727, 234);
				this.VlgCoords[2] = new Array(445, 10);
				this.VlgCoords[3] = new Array();
				this.VlgCoords[4] = new Array(507, 110);
				this.VlgCoords[5] = new Array(932, 323);
				this.VlgCoords[6] = new Array(813, 315);
				this.VlgCoords[7] = new Array(100, 292);
				this.VlgCoords[8] = new Array(229, 420);
				this.VlgCoords[9] = new Array(784, 50);
				this.VlgCoords[10] = new Array(351, 268);
				this.VlgCoords[11] = new Array(681, 6);
				this.VlgCoords[12] = new Array(301, 31);
				this.VlgCoords[13] = new Array(309, 134);
				this.VlgCoords[14] = new Array(198, 211);
				this.VlgCoords[15] = new Array(382, 373);
				this.VlgCoords[16] = new Array(382, 514);
				this.VlgCoords[17] = new Array(896, 232);
				this.VlgCoords[18] = new Array(536, 210);
				this.VlgCoords[19] = new Array(33, 401);
				this.VlgCoords[20] = new Array(218, 322);
				this.VlgCoords[21] = new Array(775, 464);
				this.VlgCoords[22] = new Array(7, 80);
			}else{
				this.VlgCoords[1] = new Array(22,89);
				this.VlgCoords[2] = new Array(328,71);
				this.VlgCoords[3] = new Array(240,285);
				this.VlgCoords[4] = new Array(88,128);
				this.VlgCoords[5] = new Array(341,297);
				this.VlgCoords[6] = new Array(151,153);
				this.VlgCoords[7] = new Array(24,178);
				this.VlgCoords[8] = new Array(94,100);
				this.VlgCoords[9] = new Array(214,54);
				this.VlgCoords[10] = new Array(152,63);
				this.VlgCoords[11] = new Array(291,40);
				this.VlgCoords[12] = new Array(134,40);
				this.VlgCoords[13] = new Array(166,40);
				this.VlgCoords[14] = new Array(242,98);
				this.VlgCoords[15] = new Array(311,97);
				this.VlgCoords[16] = new Array(456,43);
				this.VlgCoords[17] = new Array(398,188);
				this.VlgCoords[18] = new Array(76,147);
				this.VlgCoords[19] = new Array(63,34);
				this.VlgCoords[20] = new Array(62,198);
				this.VlgCoords[21] = new Array(186,86);
			}
			break;
		case 2:
			this.VlgCoords[1] = new Array(245,143);
			this.VlgCoords[2] = new Array(316,32);
			this.VlgCoords[3] = new Array(102,19);
			this.VlgCoords[4] = new Array(178,168);
			this.VlgCoords[5] = new Array(231,82);
			this.VlgCoords[6] = new Array(122,211);
			this.VlgCoords[7] = new Array(140,267);
			this.VlgCoords[8] = new Array(268,225);
			this.VlgCoords[9] = new Array(92,161);
			this.VlgCoords[10] = new Array(59,112);
			this.VlgCoords[11] = new Array(426,265);
			this.VlgCoords[12] = new Array(297,81);
			this.VlgCoords[13] = new Array(341,133);
			this.VlgCoords[14] = new Array(179,26);
			this.VlgCoords[15] = new Array(61,273);
			this.VlgCoords[16] = new Array(235,185);
			this.VlgCoords[17] = new Array(133,94);
			this.VlgCoords[18] = new Array(187,122);
			break;
		case 3:
		
			this.VlgCoords[1] = new Array(223,31);
			this.VlgCoords[2] = new Array(281,149);
			this.VlgCoords[3] = new Array(275,132);
			this.VlgCoords[4] = new Array(61,215);
			this.VlgCoords[5] = new Array(323,212);
			this.VlgCoords[6] = new Array(307,224);
			this.VlgCoords[7] = new Array(336,167);
			this.VlgCoords[8] = new Array(254,159);
			this.VlgCoords[9] = new Array(400,180);
			this.VlgCoords[10] = new Array(298,241);
			this.VlgCoords[11] = new Array(223,229);
			this.VlgCoords[12] = new Array(419,191);
			this.VlgCoords[13] = new Array(341,190);
			this.VlgCoords[14] = new Array(355,165);
			this.VlgCoords[15] = new Array(174,220);
			this.VlgCoords[16] = new Array(121,209);
			this.VlgCoords[17] = new Array(138,107);
			this.VlgCoords[18] = new Array(202,119);
			this.VlgCoords[19] = new Array(159,93);
			this.VlgCoords[20] = new Array(249,259);
			break;
		case 4:
			this.VlgCoords[1] = new Array(137,130);
			this.VlgCoords[2] = new Array(450,204);
			this.VlgCoords[3] = new Array(304,296);
			this.VlgCoords[4] = new Array(271,80);
			this.VlgCoords[5] = new Array(404,205);
			this.VlgCoords[6] = new Array(94,169);
			this.VlgCoords[7] = new Array(124,211);
			this.VlgCoords[8] = new Array(138,89);
			this.VlgCoords[9] = new Array(237,109);
			this.VlgCoords[10] = new Array(172,24);
			this.VlgCoords[11] = new Array(13,188);
			this.VlgCoords[12] = new Array(10,47);
			this.VlgCoords[13] = new Array(134,18);
			this.VlgCoords[14] = new Array(103,33);
			this.VlgCoords[15] = new Array(447,256);
			this.VlgCoords[16] = new Array(387,290);
			this.VlgCoords[17] = new Array(282,91);
			this.VlgCoords[18] = new Array(36,215);
			this.VlgCoords[19] = new Array(108,66);
			this.VlgCoords[20] = new Array(24,98);
			this.VlgCoords[21] = new Array(4,122);
			this.VlgCoords[22] = new Array(44,32);
			this.VlgCoords[23] = new Array(73,83);
			break;
		case 5:
			this.VlgCoords[1] = new Array(277,138);
			this.VlgCoords[2] = new Array(436,113);
			this.VlgCoords[3] = new Array(334,44);
			this.VlgCoords[4] = new Array(242,23);
			this.VlgCoords[5] = new Array(20,182);
			this.VlgCoords[6] = new Array(125,18);
			this.VlgCoords[7] = new Array(417,206);
			this.VlgCoords[8] = new Array(225,112);
			this.VlgCoords[9] = new Array(363,79);
			this.VlgCoords[10] = new Array(114,147);
			this.VlgCoords[11] = new Array(112,82);
			this.VlgCoords[12] = new Array(157,126);
			this.VlgCoords[13] = new Array(362,279);
			this.VlgCoords[14] = new Array(225,198);
			this.VlgCoords[15] = new Array(398,29);
			this.VlgCoords[16] = new Array(294,106);
			this.VlgCoords[17] = new Array(256,86);
			this.VlgCoords[18] = new Array(236,270);
			this.VlgCoords[19] = new Array(13,59);
			this.VlgCoords[20] = new Array(121,282);
			this.VlgCoords[21] = new Array(229,130);
			this.VlgCoords[22] = new Array(164,164);
			this.VlgCoords[23] = new Array(347,137);
			break;
		case 6:
			this.VlgCoords[1] = new Array(277,138);
			this.VlgCoords[2] = new Array(436,113);
			break;
		case 7:
			this.VlgCoords[1] = new Array(340,205);
			this.VlgCoords[2] = new Array(210,17);
			this.VlgCoords[3] = new Array(341,297);
			this.VlgCoords[4] = new Array(88,100);
			this.VlgCoords[5] = new Array(409,316);
			this.VlgCoords[6] = new Array(20,218);
			this.VlgCoords[7] = new Array(134,10);
			this.VlgCoords[8] = new Array(82,218);
			this.VlgCoords[9] = new Array(10,138);
			this.VlgCoords[10] = new Array(155,150);
			this.VlgCoords[11] = new Array(5,95);
			this.VlgCoords[12] = new Array(17,60);
			this.VlgCoords[13] = new Array(242,98);
			this.VlgCoords[14] = new Array(30,175);
			this.VlgCoords[15] = new Array(290,93);
			this.VlgCoords[16] = new Array(447,262);
			this.VlgCoords[17] = new Array(155,185);
			this.VlgCoords[18] = new Array(90,170);
			this.VlgCoords[19] = new Array(70,54);
			this.VlgCoords[20] = new Array(356,32);
			this.VlgCoords[21] = new Array(3,275);
			// recherches affichent une img?
			this.SrcCoords[15] = new Array(75,0);
			this.SrcCoords[16] = new Array(0,22);
			break;
		}
	}
};

// vlg.tpl using new Images from Iparcos
var VlgV2 = {
	w: 500,
	h: 350,
	VlgCoords: new Array(),
	VlgZindex: new Array(),
	SrcCoords: new Array(),
	FortCoords: null,
	
	init: function(race, isMobile, forteresse) {
		if(race < 1 || race > 7) race = 1;
		/* initialiser les coordonnées par race */
		this.initCoord(race, isMobile);
		
		/* image de fond du village */
		$("#fondvlg").attr('src', "img/" + race + "/vlg/vlg" + (forteresse?"f":"") + (isMobile?"1":"0") + ".jpg");
		
		/* positionner les batiments */
		$.each(this.VlgCoords, function(index, value) { 
		  if(value) {
			var img = $("#btc_"+index);			
			img.css('top', value[1]+decX).css('left', value[0]+decY).css('position', 'absolute');	
			 if(isMobile)
			 {
				img.attr('src', "img/"+ race + "/btc/2/" + index + ".png");			
			 }
			 else
			 {
				img.attr('src', "img/"+ race + "/btc/" + index + ".png");			
			 }
		  }
		});
		// z-index pour les images à mettre au dessus des autres
		$.each(this.VlgZindex, function(index, value) {
			if(value) {
			 $("#btc_"+index).css('z-index', value);
			  //console.log('btc:' + index + ' z-index=' + value);
			}
		});

		/* positionner les recherches si y'a */
		$.each(this.SrcCoords, function(index, value) { 
		  if(value) {
			//console.log('recherche:' + index + '=' + value[0] + "-" + value[1]);
			$("#src_"+index).css('top', value[1] + decX).css('left', value[0] + decY).css('position', 'absolute');
		  }
		});
		
		/* ajouter un div vide pour la forteresse */
		if(this.FortCoords != null){
			$("div#forteresse").css('top', this.FortCoords[1]+decX)
				.css('left', this.FortCoords[0]+decY).css('position', 'absolute')
				.css('height', this.FortCoords[3]).css('width', this.FortCoords[2]);
		}

	},

	/* coordonnées de chaque bâtiment du village par race */
	initCoord: function(race, isMobile) {
		if(isMobile) // coord pour mobile
			 {
				switch(race) {
				case 1:
					this.VlgCoords[1] = new Array(364, 117);
					this.VlgCoords[2] = new Array(222, 5);
					this.VlgCoords[3] = new Array(450, 8);
					this.VlgCoords[4] = new Array(253, 55);
					this.VlgCoords[5] = new Array(465, 162);
					this.VlgCoords[6] = new Array(406, 157);
					this.VlgCoords[7] = new Array(50, 146);
					this.VlgCoords[8] = new Array(114, 210);
					this.VlgCoords[9] = new Array(392, 25);
					this.VlgCoords[10] = new Array(175, 134);
					this.VlgCoords[11] = new Array(340, 3);
					this.VlgCoords[12] = new Array(150, 16);
					this.VlgCoords[13] = new Array(154, 67);
					this.VlgCoords[14] = new Array(99, 105);
					this.VlgCoords[15] = new Array(191, 186);
					this.VlgCoords[16] = new Array(191, 257);
					this.VlgCoords[17] = new Array(448, 116);
					this.VlgCoords[18] = new Array(268, 105);
					this.VlgCoords[19] = new Array(16, 201);
					this.VlgCoords[20] = new Array(109, 161);
					this.VlgCoords[21] = new Array(387, 232);
					this.VlgCoords[22] = new Array(3, 40);
					this.VlgZindex[6] = 10;
					this.VlgZindex[7] = 5;
					this.VlgZindex[9] = 10;
					this.VlgZindex[10] = 10;
					this.VlgZindex[13] = 10;
					this.VlgZindex[14] = 5;
					this.VlgZindex[20] = 10;
					break;
					
				case 2:
					this.VlgCoords[1] = new Array(353, 93);
					this.VlgCoords[2] = new Array(339, 58);
					this.VlgCoords[3] = new Array(103, 265);
					this.VlgCoords[4] = new Array(148, 215);
					this.VlgCoords[5] = new Array(425, 69);
					this.VlgCoords[6] = new Array(146, 181);
					this.VlgCoords[7] = new Array(236, 292);
					this.VlgCoords[8] = new Array(175, 56);
					this.VlgCoords[9] = new Array(36, 125);
					this.VlgCoords[10] = new Array(362, 167);
					this.VlgCoords[11] = new Array(279, 251);
					this.VlgCoords[12] = new Array(10, 243);
					this.VlgCoords[13] = new Array(277, 56);
					this.VlgCoords[14] = new Array(134, 48);
					this.VlgCoords[15] = new Array(175, 258);
					this.VlgCoords[16] = new Array(261, 111);
					this.VlgCoords[17] = new Array(61, 177);
					this.FortCoords = new Array(270, 185, 110, 52);
					//this.VlgCoords[18] = new Array(94,61);
					break;
				case 3:
					this.VlgCoords[1] = new Array(269, 87);
					this.VlgCoords[2] = new Array(346, 108);
					this.VlgCoords[3] = new Array(416, 80);
					this.VlgCoords[4] = new Array(340, 42);
					this.VlgCoords[5] = new Array(251, 7);
					this.VlgCoords[6] = new Array(166,5);
					this.VlgCoords[7] = new Array(81, 5);
					this.VlgCoords[8] = new Array(343, 161);
					this.VlgCoords[9] = new Array(264, 242);
					this.VlgCoords[10] = new Array(166, 232);
					this.VlgCoords[11] = new Array(177, 101);
					this.VlgCoords[12] = new Array(251, 157);
					this.VlgCoords[13] = new Array(89, 40);
					this.VlgCoords[14] = new Array(407, 47);
					this.VlgCoords[15] = new Array(108, 200);
					this.VlgCoords[16] = new Array(31, 133);
					this.VlgCoords[17] = new Array(362,251);
					this.VlgCoords[18] = new Array(91,105);
					this.VlgCoords[19] = new Array(178, 163);
					this.VlgCoords[20] = new Array(6, 43);
					this.FortCoords = new Array(420, 203, 92, 80);
					break;
					
				case 4:
					this.VlgCoords[1] = new Array(250, 44);
					this.VlgCoords[2] = new Array(357, 0);
					this.VlgCoords[3] = new Array(121, 74);
					this.VlgCoords[4] = new Array(312, 9);
					this.VlgCoords[5] = new Array(275, 254);
					this.VlgCoords[6] = new Array(159, 155);
					this.VlgCoords[7] = new Array(47, 19);
					this.VlgCoords[8] = new Array(186, 101);
					this.VlgCoords[9] = new Array(249, 153);
					this.VlgCoords[10] = new Array(378, 66);
					this.VlgCoords[11] = new Array(112, 195);
					this.VlgCoords[12] = new Array(3, 75);
					this.VlgCoords[13] = new Array(69, 130);
					this.VlgCoords[14] = new Array(318, 88);
					this.VlgCoords[15] = new Array(453, 151);
					this.VlgCoords[16] = new Array(443, 256);
					this.VlgCoords[17] = new Array(293, 116);
					this.VlgCoords[18] = new Array(161, 158);
					this.VlgCoords[19] = new Array(189, 23);
					this.VlgCoords[20] = new Array(91, 0);
					this.VlgCoords[21] = new Array(53, 72);
					this.VlgCoords[22] = new Array(371, 67);
					this.VlgCoords[23] = new Array(2, 131);
					this.VlgCoords[24] = new Array(314, 174);
					this.VlgZindex[3] = 5;
					this.VlgZindex[5] = 10;
					this.VlgZindex[6] = 10;
					this.VlgZindex[7] = 10;
					this.VlgZindex[10] = 10;
					this.VlgZindex[13] = 10;
					this.VlgZindex[14] = 10;
					this.VlgZindex[15] = 10;
					this.VlgZindex[16] = 10;
					break;
					
				case 5:
					this.VlgCoords[1] = new Array( 265, 105);
					this.VlgCoords[2] = new Array(346, 2);
					this.VlgCoords[3] = new Array(329, 64);
					this.VlgCoords[4] = new Array(366, 128);
					this.VlgCoords[5] = new Array(248, 200);
					this.VlgCoords[6] = new Array(417, 25);
					this.VlgCoords[7] = new Array(27, 206);
					this.VlgCoords[8] = new Array(5, 108);
					this.VlgCoords[9] = new Array(339, 192);
					this.VlgCoords[10] = new Array(415, 238);
					this.VlgCoords[11] = new Array(75, 81);
					this.VlgCoords[12] = new Array(434, 178);
					this.VlgCoords[13] = new Array(231, 29);
					this.VlgCoords[14] = new Array(165, 156);
					this.VlgCoords[15] = new Array(258, 3);
					this.VlgCoords[16] = new Array(155, 40);
					this.VlgCoords[17] = new Array(147, 82);
					this.VlgCoords[18] = new Array(100, 213);
					this.VlgCoords[19] = new Array(24, 148);
					this.VlgCoords[20] = new Array(99, 136);
					this.VlgCoords[21] = new Array(2, 14);
					this.VlgCoords[22] = new Array(154, 231);
					this.VlgCoords[23] = new Array(423, 58);
					this.FortCoords = new Array(10, 272, 142, 52);
					break;
					
				case 6:
					this.VlgCoords[1] = new Array(277,138);
					this.VlgCoords[2] = new Array(436,113);
					break;
					
				case 7:
					this.VlgCoords[1] = new Array(239, 9);
					this.VlgCoords[2] = new Array(119, 4);
					this.VlgCoords[3] = new Array(350, 134);
					this.VlgCoords[4] = new Array(0, 235);
					this.VlgCoords[5] = new Array(408, 132);
					this.VlgCoords[6] = new Array(259, 164);
					this.VlgCoords[7] = new Array(102, 69);
					this.VlgCoords[8] = new Array(101, 225);
					this.VlgCoords[9] = new Array(6, 131);
					this.VlgCoords[10] = new Array(181, 149);
					this.VlgCoords[11] = new Array(36, 0);
					this.VlgCoords[12] = new Array(306, 181);
					this.VlgCoords[13] = new Array(176, 90);
					this.VlgCoords[14] = new Array(103, 128);
					this.VlgCoords[15] = new Array(144, 276);
					this.VlgCoords[16] = new Array(426, 8);
					this.VlgCoords[17] = new Array(245, 204);
					this.VlgCoords[18] = new Array(0, 42);
					this.VlgCoords[19] = new Array(103, 190);
					this.VlgCoords[20] = new Array(1, 181);
					this.VlgCoords[21] = new Array(330, 277);
					this.VlgCoords[22] = new Array(239, 9);
					this.VlgZindex[6] = 10;
					this.VlgZindex[10] = 10;
					this.VlgZindex[16] = 10;
					break;
				}
			 }
		else // coord pour desktop
			{
				switch(race) {
				case 1:
					this.VlgCoords[1] = new Array(727, 234);
					this.VlgCoords[2] = new Array(445, 10);
					this.VlgCoords[3] = new Array(900, 16);
					this.VlgCoords[4] = new Array(507, 110);
					this.VlgCoords[5] = new Array(932, 323);
					this.VlgCoords[6] = new Array(813, 315);
					this.VlgCoords[7] = new Array(100, 292);
					this.VlgCoords[8] = new Array(229, 420);
					this.VlgCoords[9] = new Array(784, 50);
					this.VlgCoords[10] = new Array(351, 268);
					this.VlgCoords[11] = new Array(681, 6);
					this.VlgCoords[12] = new Array(301, 31);
					this.VlgCoords[13] = new Array(309, 134);
					this.VlgCoords[14] = new Array(198, 211);
					this.VlgCoords[15] = new Array(382, 373);
					this.VlgCoords[16] = new Array(382, 514);
					this.VlgCoords[17] = new Array(896, 232);
					this.VlgCoords[18] = new Array(536, 210);
					this.VlgCoords[19] = new Array(33, 401);
					this.VlgCoords[20] = new Array(218, 322);
					this.VlgCoords[21] = new Array(775, 464);
					this.VlgCoords[22] = new Array(7, 80);
					this.VlgZindex[6] = 10;
					this.VlgZindex[7] = 5;
					this.VlgZindex[9] = 10;
					this.VlgZindex[10] = 10;
					this.VlgZindex[13] = 10;
					this.VlgZindex[14] = 5;
					this.VlgZindex[20] = 10;
					break;
					
				case 2:
					this.VlgCoords[1] = new Array(706, 185);
					this.VlgCoords[2] = new Array(678, 117);
					this.VlgCoords[3] = new Array(206, 531);
					this.VlgCoords[4] = new Array(297, 431);
					this.VlgCoords[5] = new Array(850, 139);
					this.VlgCoords[6] = new Array(292, 363);
					this.VlgCoords[7] = new Array(473, 584);
					this.VlgCoords[8] = new Array(350, 113);
					this.VlgCoords[9] = new Array(73, 250);
					this.VlgCoords[10] = new Array(724, 333);
					this.VlgCoords[11] = new Array(559, 502);
					this.VlgCoords[12] = new Array(20, 486);
					this.VlgCoords[13] = new Array(554, 112);
					this.VlgCoords[14] = new Array(268, 96);
					this.VlgCoords[15] = new Array(350, 516);
					this.VlgCoords[16] = new Array(523, 223);
					this.VlgCoords[17] = new Array(121, 355);
					this.FortCoords = new Array(540, 370, 220, 105);
					//this.VlgCoords[18] = new Array(187,122);
					break;
				case 3:
					this.VlgCoords[1] = new Array(538, 173);
					this.VlgCoords[2] = new Array(692, 217);
					this.VlgCoords[3] = new Array(832, 160);
					this.VlgCoords[4] = new Array(680, 84);
					this.VlgCoords[5] = new Array(503, 14);
					this.VlgCoords[6] = new Array(331,10);
					this.VlgCoords[7] = new Array(163, 9);
					this.VlgCoords[8] = new Array(686, 321);
					this.VlgCoords[9] = new Array(528, 483);
					this.VlgCoords[10] = new Array(331, 464);
					this.VlgCoords[11] = new Array(354, 201);
					this.VlgCoords[12] = new Array(502, 315);
					this.VlgCoords[13] = new Array(179, 79);
					this.VlgCoords[14] = new Array(814, 94);
					this.VlgCoords[15] = new Array(217, 400);
					this.VlgCoords[16] = new Array(62, 265);
					this.VlgCoords[17] = new Array(723,501);
					this.VlgCoords[18] = new Array(181,211);
					this.VlgCoords[19] = new Array(356, 326);
					this.VlgCoords[20] = new Array(13, 86);
					this.FortCoords = new Array(820, 406, 175, 160);
					break;
					
				case 4:
					this.VlgCoords[1] = new Array(501, 85);
					this.VlgCoords[2] = new Array(714, 0);
					this.VlgCoords[3] = new Array(242, 147);
					this.VlgCoords[4] = new Array(623, 19);
					this.VlgCoords[5] = new Array(530, 508);
					this.VlgCoords[6] = new Array(319, 310);
					this.VlgCoords[7] = new Array(95, 38);
					this.VlgCoords[8] = new Array(371, 202);
					this.VlgCoords[9] = new Array(498, 307);
					this.VlgCoords[10] = new Array(756, 132);
					this.VlgCoords[11] = new Array(223, 390);
					this.VlgCoords[12] = new Array(7, 150);
					this.VlgCoords[13] = new Array(138, 261);
					this.VlgCoords[14] = new Array(635, 176);
					this.VlgCoords[15] = new Array(905, 303);
					this.VlgCoords[16] = new Array(886, 511);
					this.VlgCoords[17] = new Array(586, 232);
					this.VlgCoords[18] = new Array(322, 316);
					this.VlgCoords[19] = new Array(377, 45);
					this.VlgCoords[20] = new Array(183, 0);
					this.VlgCoords[21] = new Array(106, 144);
					this.VlgCoords[22] = new Array(742, 133);
					this.VlgCoords[23] = new Array(4, 262);
					this.VlgCoords[24] = new Array(628, 347);
					this.VlgZindex[3] = 5;
					this.VlgZindex[5] = 10;
					this.VlgZindex[6] = 10;
					this.VlgZindex[7] = 10;
					this.VlgZindex[10] = 10;
					this.VlgZindex[13] = 10;
					this.VlgZindex[14] = 10;
					this.VlgZindex[15] = 10;
					this.VlgZindex[16] = 10;
					break;
					
				case 5:
					this.VlgCoords[1] = new Array( 530, 210);
					this.VlgCoords[2] = new Array(693, 5);
					this.VlgCoords[3] = new Array(658, 127);
					this.VlgCoords[4] = new Array(733, 256);
					this.VlgCoords[5] = new Array(496, 401);
					this.VlgCoords[6] = new Array(834, 51);
					this.VlgCoords[7] = new Array(54, 413);
					this.VlgCoords[8] = new Array(11, 217);
					this.VlgCoords[9] = new Array(679, 384);
					this.VlgCoords[10] = new Array(831, 477);
					this.VlgCoords[11] = new Array(151, 161);
					this.VlgCoords[12] = new Array(869, 357);
					this.VlgCoords[13] = new Array(462, 58);
					this.VlgCoords[14] = new Array( 330, 311);
					this.VlgCoords[15] = new Array(517, 7);
					this.VlgCoords[16] = new Array(311, 80);
					this.VlgCoords[17] = new Array(295, 164);
					this.VlgCoords[18] = new Array(200, 427);
					this.VlgCoords[19] = new Array(49, 296);
					this.VlgCoords[20] = new Array( 198, 272);
					this.VlgCoords[21] = new Array(3, 28);
					this.VlgCoords[22] = new Array( 309, 462);
					this.VlgCoords[23] = new Array(846, 119);
					this.FortCoords = new Array(20, 545, 285, 105);
					break;
					
				case 6:
					this.VlgCoords[1] = new Array(277,138);
					this.VlgCoords[2] = new Array(436,113);
					break;
					
				case 7:
					this.VlgCoords[1] = new Array(479,19);
					this.VlgCoords[2] = new Array(239, 9);
					this.VlgCoords[3] = new Array(701, 268);
					this.VlgCoords[4] = new Array(0, 470);
					this.VlgCoords[5] = new Array(817, 264);
					this.VlgCoords[6] = new Array(518, 329);
					this.VlgCoords[7] = new Array(205, 139);
					this.VlgCoords[8] = new Array(202, 450);
					this.VlgCoords[9] = new Array(12, 262);
					this.VlgCoords[10] = new Array(363, 298);
					this.VlgCoords[11] = new Array(72, 0);
					this.VlgCoords[12] = new Array(613, 363);
					this.VlgCoords[13] = new Array(352, 180);
					this.VlgCoords[14] = new Array(207, 256);
					this.VlgCoords[15] = new Array(489, 551);
					this.VlgCoords[16] = new Array(852, 17);
					this.VlgCoords[17] = new Array(490, 408);
					this.VlgCoords[18] = new Array(0, 85);
					this.VlgCoords[19] = new Array(207, 381);
					this.VlgCoords[20] = new Array(2, 363);
					this.VlgCoords[21] = new Array(660, 553);
					this.VlgCoords[22] = new Array(479, 19);
					this.VlgZindex[6] = 10;
					this.VlgZindex[10] = 10;
					this.VlgZindex[16] = 10;
					break;
				}
			}
	}
};

