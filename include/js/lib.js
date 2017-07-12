var evarisk = jQuery.noConflict();
var digirisk = jQuery.noConflict();

jQuery( document ).ready( function(){
	/**	Add listener on export link	*/
	jQuery( document ).off( "click", ".wpes-final-survey-evaluation-view-export-button" );
	jQuery( document ).on( "click", ".wpes-survey-current-state .wpes-final-survey-evaluation-view-export-button", function( e ){
		e.preventDefault();
		jQuery( "#wpes-final-survey-evaluation-export-message-" + jQuery(this).closest( "div.wpes-audit-result-export-container" ).children( "input[name=wpes-final-survey-evaluation-view-final-survey-id]" ).val() ).html( "" );
		jQuery(this).closest( "div.wpes-audit-result-export-container" ).children( "img.wpes-loading-picture" ).show();

		var export_type = jQuery( this ).closest( "li" ).attr( "class" ).replace( "wpes-final-survey-evaluation-view-export-to-", "" );
		var data = {
			action: "digi-ajax-final-survey-evaluation-result-export",
			survey_id: jQuery(this).closest( "div.wpes-audit-result-export-container" ).children( "input[name=wpes-final-survey-evaluation-view-survey-id]" ).val(),
			final_survey_id: jQuery(this).closest( "div.wpes-audit-result-export-container" ).children( "input[name=wpes-final-survey-evaluation-view-final-survey-id]" ).val(),
			evaluation_state: jQuery(this).closest( "div.wpes-audit-result-export-container" ).children( "input[name=wpes-final-survey-evaluation-view-evaluation-state]" ).val(),
			evaluation_id: jQuery(this).closest( "div.wpes-audit-result-export-container" ).children( "input[name=wpes-final-survey-evaluation-view-evaluation-id]" ).val(),
			element_id: jQuery( "#post_ID" ).val(),
			element_type: jQuery( "#post_type" ).val(),
			export_type: export_type,
		};
		jQuery.post( ajaxurl, data, function( response ){
			jQuery( "img.wpes-loading-picture-" + response[ "final_survey_id" ] ).hide();
			if ( (true == response[ "status" ]) && ( "" != response[ "output" ] ) )  {
				jQuery( ".wpes-existing-export-container-" + response[ "final_survey_id" ] ).html( response[ "output" ] );
			}
			jQuery( "#wpes-final-survey-evaluation-export-message-" + response[ "final_survey_id" ] ).html( response[ "message" ] );
			setTimeout(function(){
				jQuery( "#wpes-final-survey-evaluation-export-message-" + response[ "final_survey_id" ] ).html( "" );
			}, "2500");
		}, "json");

	} );
} );


function digi_html_accent_for_js(text){
	text = text.replace(/&Agrave;/g, "\300");
	text = text.replace(/&Aacute;/g, "\301");
	text = text.replace(/&Acirc;/g, "\302");
	text = text.replace(/&Atilde;/g, "\303");
	text = text.replace(/&Auml;/g, "\304");
	text = text.replace(/&Aring;/g, "\305");
	text = text.replace(/&AElig;/g, "\306");
	text = text.replace(/&Ccedil;/g, "\307");
	text = text.replace(/&Egrave;/g, "\310");
	text = text.replace(/&Eacute;/g, "\311");
	text = text.replace(/&Ecirc;/g, "\312");
	text = text.replace(/&Euml;/g, "\313");
	text = text.replace(/&Igrave;/g, "\314");
	text = text.replace(/&Iacute;/g, "\315");
	text = text.replace(/&Icirc;/g, "\316");
	text = text.replace(/&Iuml;/g, "\317");
	text = text.replace(/&Eth;/g, "\320");
	text = text.replace(/&Ntilde;/g, "\321");
	text = text.replace(/&Ograve;/g, "\322");
	text = text.replace(/&Oacute;/g, "\323");
	text = text.replace(/&Ocirc;/g, "\324");
	text = text.replace(/&Otilde;/g, "\325");
	text = text.replace(/&Ouml;/g, "\326");
	text = text.replace(/&Oslash;/g, "\330");
	text = text.replace(/&Ugrave;/g, "\331");
	text = text.replace(/&Uacute;/g, "\332");
	text = text.replace(/&Ucirc;/g, "\333");
	text = text.replace(/&Uuml;/g, "\334");
	text = text.replace(/&Yacute;/g, "\335");
	text = text.replace(/&THORN;/g, "\336");
	text = text.replace(/&Yuml;/g, "\570");
	text = text.replace(/&szlig;/g, "\337");
	text = text.replace(/&agrave;/g, "\340");
	text = text.replace(/&aacute;/g, "\341");
	text = text.replace(/&acirc;/g, "\342");
	text = text.replace(/&atilde;/g, "\343");
	text = text.replace(/&auml;/g, "\344");
	text = text.replace(/&aring;/g, "\345");
	text = text.replace(/&aelig;/g, "\346");
	text = text.replace(/&ccedil;/g, "\347");
	text = text.replace(/&egrave;/g, "\350");
	text = text.replace(/&eacute;/g, "\351");
	text = text.replace(/&ecirc;/g, "\352");
	text = text.replace(/&euml;/g, "\353");
	text = text.replace(/&igrave;/g, "\354");
	text = text.replace(/&iacute;/g, "\355");
	text = text.replace(/&icirc;/g, "\356");
	text = text.replace(/&iuml;/g, "\357");
	text = text.replace(/&eth;/g, "\360");
	text = text.replace(/&ntilde;/g, "\361");
	text = text.replace(/&ograve;/g, "\362");
	text = text.replace(/&oacute;/g, "\363");
	text = text.replace(/&ocirc;/g, "\364");
	text = text.replace(/&otilde;/g, "\365");
	text = text.replace(/&ouml;/g, "\366");
	text = text.replace(/&oslash;/g, "\370");
	text = text.replace(/&ugrave;/g, "\371");
	text = text.replace(/&uacute;/g, "\372");
	text = text.replace(/&ucirc;/g, "\373");
	text = text.replace(/&uuml;/g, "\374");
	text = text.replace(/&yacute;/g, "\375");
	text = text.replace(/&thorn;/g, "\376");
	text = text.replace(/&yuml;/g, "\377");
	text = text.replace(/&oelig;/g, "\523");
	text = text.replace(/&OElig;/g, "\522");
	text = text.replace(/&nbsp;/g, "\240");
	return text;
}

function changementPage(partie, table, page, idPere, affichage, option){
	var partContainer = 'partieEdition';
	if(partie == 'left'){
		var partContainer = 'partieGauche';
	}

	var query_data = {
		"post": "true",
		"table": table,
		"act": "changementPage",
		"page": page,
		"idPere": idPere,
		"partie": partie,
		"affichage": affichage,
		"expanded": option,
		"menu": digirisk("#menu").val()
	};

	if ( undefined != jQuery( "#digi-tree-element-status" ).val() ) {
		query_data['tree-options'] = new Array();
		query_data['tree-options']['task-progression-status'] = jQuery( "#digi-tree-element-status" ).val();
	}

	digirisk("#" + partContainer).html(digirisk("#loadingImg").html());
	digirisk("#" + partContainer).load(EVA_AJAX_FILE_URL, query_data );
}

function commonTabChange(boxId, divId, tabId){
	digirisk("#" + boxId + " .tabs").each(function(){
		digirisk(this).removeClass("selected_tab");
	});
	digirisk("#" + boxId + " .eva_tabs_panel").each(function(){
		digirisk(this).hide();
	});
	digirisk(divId).show();
	digirisk(tabId).addClass("selected_tab");
}
function tabChange(divId, tabId){
	digirisk("#postBoxRisques .tabs").each(function(){
		digirisk(this).removeClass("selected_tab");
	});
	digirisk("#postBoxRisques .eva_tabs_panel").each(function(){
		digirisk(this).hide();
	});
	digirisk(divId).show();
	digirisk(tabId).addClass("selected_tab");
}
function hideExtraTab(){
	digirisk("#ongletEditerRisque").css("display","none");
	digirisk("#ongletControlerActionDemandee").css("display","none");
	digirisk("#ongletDemandeActionCorrective" + TABLE_RISQUE).css("display","none");
	digirisk("#divDemandeAction" + TABLE_RISQUE).html("");
	digirisk("#ongletSuiviActionCorrective" + TABLE_RISQUE).css("display","none");
	digirisk("#divSuiviAction" + TABLE_RISQUE).html("");
	digirisk("#ongletFicheActionCorrective" + TABLE_RISQUE).css("display","none");
	digirisk("#divFicheAction" + TABLE_RISQUE).html("");
	digirisk("#ongletHistoRisk" + TABLE_RISQUE).css("display","none");
	digirisk("#divHistoRisk" + TABLE_RISQUE).html("");
	digirisk("#divMassUpdater" + TABLE_RISQUE).html("");
}

function selectRowInTreeTable(tableId){
	// Make visible that a row is clicked
	digirisk("table#" + tableId + " tbody tr").click(function() {
		digirisk("tr.selected").removeClass("selected"); // Deselect currently selected rows
		digirisk("tr.edited").removeClass("edited"); // Deselect currently selected rows
		digirisk(this).addClass("selected");
	});

	// Make sure row is selected when span is clicked
	digirisk("table#" + tableId + " tbody tr span").click(function() {
		digirisk(digirisk(this).parents("tr")[0]).trigger("click");
	});
}
function reInitTreeTable(){
	digirisk("#rightEnlarging").show();
	digirisk("#equilize").click();
	var expanded = new Array();
	digirisk(".expanded").each(function(){
		expanded.push(digirisk(this).attr("id"));
	});
	return expanded;
}

function initialiseClassicalPage(){
	if(digirisk("#rightSide-sortables").html() == ""){
		digirisk("#rightEnlarging").hide();
	}
	else{
		digirisk("#rightEnlarging").show();
	}
	if(digirisk("#leftSide-sortables").html() == ""){
		digirisk("#leftEnlarging").hide();
	}
	else{
		digirisk("#leftEnlarging").show();
	}
}
function initialiseEditedElementInGridMode(idToEdit){
	digirisk("#tablemainPostBox tbody tr:nth-child(3)").each(function(){
		for(var i=1; i<=digirisk(this).children("td").length; i++){
			if(digirisk(this).children("td:nth-child(" + i + ")").children("img").attr("id") == idToEdit){
				digirisk(this).prevAll("tr:not(tr:first-child)").andSelf().children("td:nth-child(" + i + ")").addClass("edited");
				// 3 * i car nomInfo + : + info
				digirisk(this).prevAll("tr:first-child").children("td:nth-child(" + (3 * i) + ")").addClass("edited");
				digirisk(this).prevAll("tr:first-child").children("td:nth-child(" + (3 * i - 1) + ")").addClass("edited");
				digirisk(this).prevAll("tr:first-child").children("td:nth-child(" + (3 * i - 2) + ")").addClass("edited");
			}
		}
	});
}

function actionMessageShow(idToShow, messageToShow){
	digirisk(idToShow).show();
	digirisk(idToShow).addClass("updated");
	digirisk(idToShow).html(messageToShow);
}
function actionMessageHide(idToHide){
	digirisk(idToHide).hide();
	digirisk(idToHide).html("");
	digirisk(idToHide).removeClass("updated");
}

function emptyOptionForm(){
	digirisk("#actionOption").val("save");
	digirisk("#idOption").val("");
	digirisk("#nomOption").val("");
	digirisk("#type").val("");
	digirisk("#optionName").html("");
	digirisk("#optionEdition").html("");
	digirisk("#light").hide();
	digirisk("#fade").hide();
}
function goTo(ancre){
	var speed = 1000;
	jQuery("html,body").animate({scrollTop:jQuery(ancre).offset().top},speed,"swing",function(){
		if(ancre != "body"){window.location.hash = ancre;} else {window.location.hash = "#";}
		jQuery(ancre).attr("tabindex","-1");
		jQuery(ancre).focus();
		jQuery(ancre).removeAttr("tabindex");
	});
}

function updateTips( t, container ){
	container
		.text( t )
		.addClass( "ui-state-highlight" );
	setTimeout(function() {
		container.removeClass( "ui-state-highlight", 1500 );
	}, 500 );
	setTimeout(function() {
		container.text( "" );
	}, 5000 );
}
function checkLength( o, n, min, max, msg, errorContainer ){
	if ( (o.val().length > max) || (o.val().length < min) ){
		o.addClass( "ui-state-error" );
		updateTips( digi_html_accent_for_js(msg.replace("!#!term!#!", n).replace("!#!minlength!#!", min).replace("!#!maxlength!#!", max)), errorContainer );
		return false;
	}
	else{
		return true;
	}
}
function checkRegexp( o, regexp, n, errorContainer  ){
	if ( !( regexp.test( o.val() ) ) ){
		o.addClass( "ui-state-error" );
		updateTips( n, errorContainer );
		return false;
	}
	else{
		return true;
	}
}

/*	Picture galery functions	*/
function reloadcontainer(tableElement, idElement, PICTO_LOADING_ROUND){
	digirisk("#pictureGallery" + tableElement + "_" + idElement).html('<img src="' + PICTO_LOADING_ROUND + '" alt="loading" />');
	digirisk("#pictureGallery" + tableElement + "_" + idElement).load(EVA_AJAX_FILE_URL, {
		"post": "true",
		"table": tableElement,
		"idElement": idElement,
		"act": "reloadGallery"
	});
}
function showGallery(tableElement, idElement, PICTO_LOADING_ROUND){
	digirisk("#pictureGallery" + tableElement + "_" + idElement).html('<img src="' + PICTO_LOADING_ROUND + '" alt="loading" />');
	digirisk("#pictureGallery" + tableElement + "_" + idElement).load(EVA_AJAX_FILE_URL, {
		"post": "true",
		"table": tableElement,
		"idElement": idElement,
		"act": "showGallery"
	});
}

/*	Recommandation functions	*/
function deleteRecommandationCategory(recommandationCategoryId, tableElement, alertMessage){
	if(confirm(digi_html_accent_for_js(alertMessage))){
		digirisk("#ajax-response").load(EVA_AJAX_FILE_URL, {
			"post":"true",
			"table":tableElement,
			"act":"deleteRecommandationCategory",
			"id":recommandationCategoryId
		});
	}
}
function editRecommandationCategory(recommandationCategoryId, tableElement){
	digirisk("#recommandationCategoryFormContainer").hide();
	digirisk("#loadingCategoryRecommandationForm").html(digirisk("#loadingImg").html());
	digirisk("#loadingCategoryRecommandationForm").show();
	digirisk("#recommandationCategoryInterfaceContainer").dialog("open");
	digirisk("#recommandationCategoryFormContainer").load(EVA_AJAX_FILE_URL, {
		"post":"true",
		"table":tableElement,
		"act":"loadRecommandationCategoryManagementForm",
		"id":recommandationCategoryId
	});
}

/*	User link functions	*/
function userDeletion(userId, tableElement){
	id = userId.replace("affectedUser" + tableElement, "");
	deleteUserIdFiedList(id, tableElement);
	checkUserListModification(tableElement, "save_group" + tableElement);
}
function addUserIdFieldList(name, id, tableElement, selected_date){
	digirisk("#noUserSelected" + tableElement).remove();
	digirisk("#userListOutput" + tableElement).attr("scrollTop",0);

	digirisk(digirisk("#userBlocContainer").html()).prependTo("#userListOutput" + tableElement);
	digirisk("#userListOutput" + tableElement + " div:first").attr("id", "affectedUser" + tableElement + id);
	digirisk("#affectedUser" + tableElement + id).html( digirisk("#affectedUser" + tableElement + id).html().replace("#USERDATEAFFECTATION#", digi_html_accent_for_js(DIGI_USER_AFFECTATION_DATE_TEXT_IN) . selected_date).replace("#USERNAME#", name) );
}
function checkUserListModification(tableElement, idButton) {
	var actualUserList = digirisk("#actuallyAffectedUserIdList" + tableElement).val();
	var userList = digirisk("#affectedUserIdList" + tableElement).val();

	if(actualUserList == userList){
		digirisk("#" + idButton).prop("disabled", "disabled");
		digirisk("#" + idButton).addClass("button-secondary");
		digirisk("#" + idButton).removeClass("button-primary");
	}
	else{
		digirisk("#" + idButton).prop("disabled", "");
		digirisk("#" + idButton).removeClass("button-secondary");
		digirisk("#" + idButton).addClass("button-primary");
	}
}
function cleanUserIdFiedList(id, tableElement){
	var actualAffectedUserList = digirisk("#affectedUserIdList" + tableElement).val().replace(" " + id + ", ", "");
	digirisk("#affectedUserIdList" + tableElement).val( actualAffectedUserList + id + ", ");

	if (digirisk("#affectedUser" + tableElement + id)) {
		digirisk("#affectedUser" + tableElement + id).remove();
	}

	digirisk("#actionButton" + tableElement + "UserLink" + id).addClass("userIsLinked");
	digirisk("#actionButton" + tableElement + "UserLink" + id).removeClass("userIsNotLinked");
}
function deleteUserIdFiedList(id, tableElement){
	var actualAffectedUserList = digirisk("#affectedUserIdList" + tableElement).val().replace(id + ", ", "");
	digirisk("#affectedUserIdList" + tableElement).val( actualAffectedUserList );
	//digirisk("#affectedUser" + tableElement + id).remove();
	digirisk("#affectedUser" + tableElement + id).addClass( 'unSelecteduserOP' );

	digirisk("#actionButton" + tableElement + "UserLink" + id).removeClass("userIsLinked");
	digirisk("#actionButton" + tableElement + "UserLink" + id).addClass("userIsNotLinked");
}

/*	Element link functions	*/
function elementDeletion(elementId, tableElement, idButton){
	id = elementId.replace("affectedElement" + tableElement, "");
	deleteElementIdFiedList(id, tableElement);
	checkElementListModification(tableElement, idButton);
}
function addElementIdFieldList(name, id, tableElement){
	digirisk("#noElementSelected" + tableElement).remove();
	digirisk("#affectedListOutput" + tableElement).attr("scrollTop",0);

	digirisk(digirisk("#elementBlocContainer" + tableElement).html()).prependTo("#affectedListOutput" + tableElement);

	digirisk("#affectedListOutput" + tableElement + " div:first").attr("id", "affectedElement" + tableElement + id);
	digirisk("#affectedElement" + tableElement + id).html(digirisk("#affectedElement" + tableElement + id).html().replace("#ELEMENTNAME#", name));
}
function checkElementListModification(tableElement, idButton){
	var actuallyAffectedList = digirisk("#actuallyAffectedList" + tableElement).val();
	var affectedList = digirisk("#affectedList" + tableElement).val();

	if(actuallyAffectedList == affectedList){
		digirisk("#" + idButton).prop("disabled", "disabled");
		digirisk("#" + idButton).addClass("button-secondary");
		digirisk("#" + idButton).removeClass("button-primary");
	}
	else{
		digirisk("#" + idButton).prop("disabled", "");
		digirisk("#" + idButton).removeClass("button-secondary");
		digirisk("#" + idButton).addClass("button-primary");
	}
}
function cleanElementIdFiedList(id, tableElement){
	var actualAffectedUserList = digirisk("#affectedList" + tableElement).val().replace(" " + id + ", ", "");
	digirisk("#affectedList" + tableElement).val( actualAffectedUserList + id + ", ");

	if(digirisk("#affectedElement" + tableElement + id)){
		digirisk("#affectedElement" + tableElement + id).remove();
	}

	digirisk("#actionButton" + tableElement + "ElementLink" + id).addClass("elementIsLinked");
	digirisk("#actionButton" + tableElement + "ElementLink" + id).removeClass("elementIsNotLinked");
}
function deleteElementIdFiedList(id, tableElement){
	var actualAffectedUserList = digirisk("#affectedList" + tableElement).val().replace(id + ", ", "");
	digirisk("#affectedList" + tableElement).val( actualAffectedUserList );
	digirisk("#affectedElement" + tableElement + id).remove();

	digirisk("#actionButton" + tableElement + "ElementLink" + id).removeClass("elementIsLinked");
	digirisk("#actionButton" + tableElement + "ElementLink" + id).addClass("elementIsNotLinked");
}


function createGroupement(action, table){
	digirisk("#act").val(action);
	digirisk("#ajax-response").load(EVA_AJAX_FILE_URL, {
		"post": "true",
		"table": table,
		"act": digirisk("#act").val(),
		"id": digirisk("#id").val(),
		"typeGroupement": digirisk("#typeGroupement").val(),
		"responsable_groupement": digirisk("input[name=responsable_groupement]").val(),
		"nom_groupement": digirisk("#nom_groupement").val(),
		"groupementPere": digirisk("#groupementPere :selected").val(),
		"description": digirisk("#description").val(),
		"adresse_ligne_1": digirisk("#adresse_ligne_1").val(),
		"adresse_ligne_2": digirisk("#adresse_ligne_2").val(),
		"code_postal": digirisk("#code_postal").val(),
		"ville": digirisk("#ville").val(),
		"telephone": digirisk("#telephone").val(),
		"effectif": digirisk("#effectif").val(),
		"affichage": digirisk("#affichage").val(),
		"latitude": digirisk("#latitude").val(),
		"longitude": digirisk("#longitude").val(),
		"siren": digirisk("#siren").val(),
		"siret": digirisk("#siret").val(),
		"social_activity_number": digirisk("#social_activity_number").val(),
		"creation_date_of_society": digirisk("#creation_date_of_society").val(),
		"idsFilAriane": digirisk("#idsFilAriane").val()
	});
}
function createUniteTravail(action, table){
	digirisk("#act").val(action);
	digirisk("#ajax-response").load(EVA_AJAX_FILE_URL, {
		"post": "true",
		"table": table,
		"act": digirisk("#act").val(),
		"id": digirisk("#id").val(),
		"nom_unite_travail": digirisk("#nom_unite_travail").val(),
		"responsable_unite": digirisk("input[name=responsable_unite]").val(),
		"groupementPere": digirisk("#groupementPere :selected").val(),
		"description": digirisk("#description").val(),
		"adresse_ligne_1": digirisk("#adresse_ligne_1").val(),
		"adresse_ligne_2": digirisk("#adresse_ligne_2").val(),
		"code_postal": digirisk("#code_postal").val(),
		"ville": digirisk("#ville").val(),
		"telephone": digirisk("#telephone").val(),
		"effectif": digirisk("#effectif").val(),
		"effectif": digirisk("#effectif").val(),
		"affichage": digirisk("#affichage").val(),
		"latitude": digirisk("#latitude").val(),
		"longitude": digirisk("#longitude").val(),
		"idsFilAriane": digirisk("#idsFilAriane").val()
	});
}


/**
*	Add utilities for page shape selection. show/hide right/left part, enlarge/shrink right/left part
*/
function main_page_shape_selector(){
	digirisk("#leftEnlarging").click(function() {
		jQuery("#digirisk_right_container").hide();
		jQuery("#digirisk_left_container").show();
		jQuery("#digirisk_left_container").css("width", "99%");
		adminMenu.fold();
		jQuery("#enlarging .ui-slider-range").css("width","100%");
		jQuery("#enlarging .ui-slider-handle").css("left","100%");
	});
	digirisk("#rightEnlarging").click(function() {
		jQuery("#digirisk_left_container").hide();
		jQuery("#digirisk_right_container").show();
		jQuery("#digirisk_right_container").css("width", "99%");
		adminMenu.fold();
		jQuery("#enlarging .ui-slider-range").css("width","0%");
		jQuery("#enlarging .ui-slider-handle").css("left","0%");
	});
	digirisk("#equilize").click(function() {
		jQuery("#digirisk_left_container").show();
		jQuery("#digirisk_right_container").show();
		jQuery("#digirisk_right_container").css("width", "50%");
		jQuery("#digirisk_left_container").css("width", "49%");
		jQuery("#enlarging .ui-slider-range").css("width","50%");
		jQuery("#enlarging .ui-slider-handle").css("left","50%");
	});
	digirisk("#enlarging .ui-slider-horizontal").css("width","100px");
	digirisk("#enlarging").slider({
		range: "min",
		value: 50,
		min: 25,
		max:  75,
		slide: function(event, ui) {
			var largeurGauche = ui.value - 1;
			var largeurDroite = 99 - largeurGauche;
			if((largeurGauche == 24) || (largeurDroite == 24)){
				adminMenu.fold();
			}
			jQuery("#digirisk_right_container").show();
			jQuery("#digirisk_left_container").show();
			jQuery("#digirisk_left_container").css("width", largeurGauche  + "%");
			jQuery("#digirisk_right_container").css("width", largeurDroite  + "%");
		}
	});
}

function check_if_value_changed(button){
	if(jQuery("#receiver_element").val() != jQuery("#current_element").val()){
		jQuery("#" + button).removeClass("button-secondary");
		jQuery("#" + button).addClass("button-primary");
	}
	else{
		jQuery("#" + button).addClass("button-secondary");
		jQuery("#" + button).removeClass("button-primary");
	}
}


/**
*	Add utilities for page shape selection. show/hide right/left part, enlarge/shrink right/left part
*/
function main_page_shape_selector(){
	jQuery("#leftEnlarging").click(function() {
		jQuery("#digirisk_right_container").hide();
		jQuery("#digirisk_left_container").show();
		jQuery("#digirisk_left_container").css("width", "99%");
		adminMenu.fold();
		jQuery("#enlarging .ui-slider-range").css("width","100%");
		jQuery("#enlarging .ui-slider-handle").css("left","100%");
	});
	evarisk("#rightEnlarging").click(function() {
		jQuery("#digirisk_left_container").hide();
		jQuery("#digirisk_right_container").show();
		jQuery("#digirisk_right_container").css("width", "99%");
		adminMenu.fold();
		jQuery("#enlarging .ui-slider-range").css("width","0%");
		jQuery("#enlarging .ui-slider-handle").css("left","0%");
	});
	evarisk("#equilize").click(function() {
		jQuery("#digirisk_left_container").show();
		jQuery("#digirisk_right_container").show();
		jQuery("#digirisk_right_container").css("width", "50%");
		jQuery("#digirisk_left_container").css("width", "49%");
		jQuery("#enlarging .ui-slider-range").css("width","50%");
		jQuery("#enlarging .ui-slider-handle").css("left","50%");
	});
	evarisk("#enlarging .ui-slider-horizontal").css("width","100px");
	if(evarisk("#enlarging").attr("id")){
		evarisk("#enlarging").slider({
			range: "min",
			value: 50,
			min: 25,
			max:  75,
			slide: function(event, ui) {
				var largeurGauche = ui.value - 1;
				var largeurDroite = 99 - largeurGauche;
				if((largeurGauche == 24) || (largeurDroite == 24)){
					adminMenu.fold();
				}
				evarisk("#digirisk_right_container").show();
				evarisk("#digirisk_left_container").show();
				evarisk("#digirisk_left_container").css("width", largeurGauche  + "%");
				evarisk("#digirisk_right_container").css("width", largeurDroite  + "%");
			}
		});
	}
}

/**
*	Transform a simple table into a tree table
*/
function table_to_treeTable(table_identifier, root_identifier, element_type, sub_element_type, menu){
	/*	Transform the table in a treeTable	*/
	jQuery("#" + table_identifier).treeTable();

	/*	On first tree loading open the main root branch	*/
	jQuery("#" + table_identifier + " #node-" + table_identifier + "-" + root_identifier).toggleBranch();
	jQuery("#tdRacine" + table_identifier).children("span.expander").remove();
	jQuery("#" + table_identifier +" tr.parent").each(function(){
		var childNodes = jQuery("table#" + table_identifier + " tbody tr.child-of-" + jQuery(this).attr("id"));
		if(childNodes.length > 0){
			jQuery(this).addClass("aFils");
			var premierFils = jQuery("table#" + table_identifier + " tbody tr.child-of-" + jQuery(this).attr("id") + ":first").attr("id");
			if(premierFils != premierFils.replace(/node/g,"")){
				jQuery(this).addClass("aFilsNoeud");
			}
			else{
				jQuery(this).addClass("aFilsFeuille");
			}
		}
		else{
			jQuery(this).removeClass("aFils");
			jQuery(this).addClass("sansFils");
		}
	});

	/*	Add possibility to collapse main tree	by clicking on table header*/
	jQuery(".digirisk_main_table thead").hover(function(){
		jQuery(".main_metabox_collapser").addClass("main_metabox_collapser_hover");
	},function(){
		jQuery(".main_metabox_collapser").removeClass("main_metabox_collapser_hover");
	});
	jQuery(".digirisk_main_table thead").click(function(){
		jQuery("#" + table_identifier + " tbody").toggle();
	});

	/*	Add action when clicking on main add button	*/
	action_on_add_button(table_identifier, element_type, sub_element_type, menu)
}

/**
*	Add action when clicking on main add button
*/
function action_on_add_button(table_identifier, element_type, sub_element_type, menu){
	jQuery("#" + table_identifier + " .addMain img").click(function(){
		var nodeId = jQuery(this).parent("td").parent("tr").attr("id").replace("node-" + table_identifier + "-", "");
		var expanded = reInitTreeTable();
		jQuery("#digirisk_right_side").html(jQuery("#loadingImg").html());
		jQuery("#digirisk_right_side").load(EVA_AJAX_FILE_URL,{
			"post": "true",
			"table": element_type,
			"act": "add",
			"page": jQuery("#pagemainPostBoxReference").val(),
			"idPere": nodeId,
			"partie": "right",
			"menu": menu,
			"affichage": "affichageListe",
			"partition": "tout",
			"expanded": expanded
		});
	});
	/*	Add action when clicking on secondary add button	*/
	jQuery("#" + table_identifier + " .addSecondary img").click(function(){
		var nodeId = jQuery(this).parent("td").parent("tr").attr("id").replace("node-" + table_identifier + "-", "");
		var expanded = reInitTreeTable();
		jQuery("#digirisk_right_side").html(jQuery("#loadingImg").html());
		jQuery("#digirisk_right_side").load(EVA_AJAX_FILE_URL,{
			"post": "true",
			"table": sub_element_type,
			"act": "add",
			"page": jQuery("#pagemainPostBoxReference").val(),
			"idPere": nodeId,
			"partie": "right",
			"menu": menu,
			"affichage": "affichageListe",
			"partition": "tout",
			"expanded": expanded
		});
	});
}

/**
*	Create a sortable, drag and droppable tree
*/
function main_tree_draggable(table_identifier, element_type, loading_container, loading_text){
	//	Draggable interface
	var draggedObject;
	var draggedObjectFather;

	// Configure draggable nodes
	jQuery("#" + table_identifier + " .noeudArbre, #" + table_identifier + " .feuilleArbre").draggable({
		start: function(event, ui) {
			draggedObject = jQuery(this).closest("tr").attr("id");//event.target.id;
			var classNames = jQuery(this).closest("tr").attr("class").split(' ');//event.target.className.split(' ');
			draggedObjectFather = "temp";
			for(key in classNames) {
				if(classNames[key].match("child-of-")) {
					draggedObjectFather = jQuery("#" + classNames[key].substring(9));
					draggedObjectFather = draggedObjectFather.attr("id");
				}
			}
		},
		helper: "clone",
		opacity: .75,
		refreshPositions: true,
		revert: "invalid",
		revertDuration: 300,
		scroll: true
	});

	var dropFunction = function(event, ui){
		// Call jQuery treeTable plugin to move the branch
		jQuery(jQuery(ui.draggable)).appendBranchTo(this);
		var dropLocation = jQuery(this).closest("tr").attr("id");//event.target.id;

		jQuery("#equilize").click();
		jQuery("#" + loading_container).addClass("updated");
		jQuery("#" + loading_container).html(loading_text);
		jQuery("#" + loading_container).show();
		jQuery("#" + loading_container).load(EVA_AJAX_FILE_URL,{
			"post":"true",
			"table": element_type,
			"location": table_identifier,
			"act":"transfert",
			"idElementSrc":draggedObject,
			"idElementOrigine":draggedObjectFather,
			"idElementDest":dropLocation
		});
		setTimeout(
			function(){
				jQuery("#" + table_identifier + " tr.parent").each(function(){
					var childNodes = jQuery("table#" + table_identifier + " tbody tr.child-of-" + jQuery(this).attr("id"));
					if(childNodes.length > 0){
						jQuery(this).removeClass("sansFils");
						jQuery(this).addClass("aFils");
						var premierFils = jQuery("table#" + table_identifier + " tbody tr.child-of-" + jQuery(this).attr("id") + ":first").attr("id");
						if(premierFils != premierFils.replace("node-" + table_identifier + "-","")){
							jQuery(this).addClass("aFilsNoeud");
							jQuery(this).droppable( "option", "accept", ".noeudArbre" );
							jQuery("#" + table_identifier + " #addSecondary" + jQuery(this).attr("id").replace("node-" + table_identifier + "-","") + " img").hide();
							jQuery("#" + table_identifier + " #addMain" + jQuery(this).attr("id").replace("node-" + table_identifier + "-","") + " img").show();
						}
						else{
							jQuery(this).addClass("aFilsFeuille");
							jQuery(this).droppable( "option", "accept", ".feuilleArbre" );
							jQuery("#" + table_identifier + " #addMain" + jQuery(this).attr("id").replace("node-" + table_identifier + "-","") + " img").hide();
							jQuery("#" + table_identifier + " #addSecondary" + jQuery(this).attr("id").replace("node-" + table_identifier + "-","") + " img").show();
						}
					}
					else{
						jQuery(this).removeClass("aFilsNoeud");
						jQuery(this).removeClass("aFilsFeuille");
						jQuery(this).removeClass("aFils");
						jQuery(this).addClass("sansFils");
						jQuery(this).droppable("option", "accept", ".noeudArbre, .feuilleArbre");
						jQuery("#" + table_identifier + " #addSecondary" + jQuery(this).attr("id").replace("node-" + table_identifier + "-","") + " img").show();
						jQuery("#" + table_identifier + " #addMain" + jQuery(this).attr("id").replace("node-" + table_identifier + "-","") + " img").show();
					}
				});
				jQuery(document).ajaxStop(function(){
					jQuery("#" + loading_container).removeClass("updated");
				});
			},
			10
		);
	}

	overFunction = function(event, ui){
		// Make the droppable branch expand when a draggable node is moved over it.
		if((this.id != jQuery(ui.draggable.parents("tr")[0]).id) && !jQuery(this).is(".expanded")){
			var overObject = jQuery(this);
			setTimeout(function(){
				if(overObject.is(".accept")){
					overObject.expand();
				}
			},500 );
		}
	}
	jQuery("#" + table_identifier + " .aFilsNoeud, #" + table_identifier + " .racineArbre").droppable({
		accept: "#" + table_identifier + " .noeudArbre",
		drop: dropFunction,
		hoverClass: "accept",
		over: overFunction
	});
	jQuery("#" + table_identifier + " .aFilsFeuille").droppable({
		accept: "#" + table_identifier + " .feuilleArbre",
		drop: dropFunction,
		hoverClass: "accept",
		over: overFunction
	});
	jQuery("#" + table_identifier + " .sansFils").droppable({
		accept: "#" + table_identifier + " .feuilleArbre, #" + table_identifier + " .noeudArbre",
		drop: dropFunction,
		hoverClass: "accept",
		over: overFunction
	});
}

/**
*	Declare utilities for trash usage
*/
function main_tree_trash(element_type){
	/*	Add trash utilities	*/
	jQuery("#trashContainer").dialog({
		autoOpen: false,
		modal: true,
		width: 800,
		height: 600,
		close: function(){
			evarisk(this).html("");
		}
	});
	jQuery(".trash img").click(function(){
		jQuery("#trashContainer").dialog("open");
		jQuery("#trashContainer").html(evarisk("#loadingImg").html());
		jQuery("#trashContainer").load(EVA_AJAX_FILE_URL,{
			"post": "true",
			"tableProvenance": element_type,
			"nom": "loadTrash"
		});
	});
}

/**
*	Define action to launch when a click is detected for a node in tree
*/
function main_tree_action_node(table_identifier, element_type, delete_message){
	/*	A click is detected on delete button	*/
	jQuery("#" + table_identifier + " .delete-node").click(function(){
		var nodeId = jQuery(this).parent("tr").attr("id").replace("node-" + table_identifier + "-", "").replace("-name", "");
		var expanded = reInitTreeTable();
		if(confirm(digi_html_accent_for_js(delete_message))){
			jQuery("#digirisk_right_side").html("");
			jQuery("#digirisk_left_side").html("");
			jQuery("#ajax-response").load(EVA_AJAX_FILE_URL,{
				"post": "true",
				"table": element_type,
				"act": "delete",
				"id": nodeId
			});
		}
	});
	/*	A click is detected on edit button	*/
	jQuery("#" + table_identifier + " .edit-node").click(function(){
		var nodeId = jQuery(this).parent("tr").attr("id").replace("node-" + table_identifier + "-", "").replace("-name", "");
		selectRowInTreeTable(table_identifier);
		var expanded = reInitTreeTable();
		jQuery("#digirisk_right_side").html(jQuery("#loadingImg").html());
		jQuery("#digirisk_right_side").load(EVA_AJAX_FILE_URL,{
			"post": "true",
			"table": element_type,
			"act": "edit",
			"id": nodeId,
			"partie": "right",
			"menu": "gestiongrptut",
			"affichage": "affichageListe",
			"partition": "tout",
			"expanded": expanded
		});
		jQuery("#digirisk_left_side").html("");
		jQuery("#digirisk_left_side").load(EVA_AJAX_FILE_URL,{
			"post": "true",
			"table": element_type,
			"act": "edit",
			"id": nodeId,
			"partie": "left",
			"menu": "gestiongrptut",
			"affichage": "affichageListe",
			"partition": "tout",
			"expanded": expanded
		});
	});
	/*	A click is detected on evaluation button	*/
	jQuery("#" + table_identifier + " .risq-node").click(function(){
		var nodeId = jQuery(this).parent("tr").attr("id").replace("node-" + table_identifier + "-", "").replace("-name", "");
		selectRowInTreeTable(table_identifier);
		var expanded = reInitTreeTable();
		jQuery("#digirisk_right_side").html(jQuery("#loadingImg").html());
		jQuery("#digirisk_right_side").load(EVA_AJAX_FILE_URL,{
			"post": "true",
			"table": element_type,
			"act": "edit",
			"id": nodeId,
			"partie": "right",
			"menu": "risq",
			"affichage": "affichageListe",
			"partition": "tout",
			"expanded": expanded
		});
		jQuery("#digirisk_left_side").html("");
		jQuery("#digirisk_left_side").load(EVA_AJAX_FILE_URL,{
			"post": "true",
			"table": element_type,
			"act": "edit",
			"id": nodeId,
			"partie": "left",
			"menu": "risq",
			"affichage": "affichageListe",
			"partition": "tout",
			"expanded": expanded
		});
	});
	/*	A click is detected on delete button	*/
	jQuery("#" + table_identifier + " .nomNoeudArbre, #" + table_identifier + " .treeTableGroupInfoColumn").unbind("click");
	jQuery("#" + table_identifier + " .nomNoeudArbre, #" + table_identifier + " .treeTableGroupInfoColumn").click(function(e){
		if(!jQuery(e.target).hasClass("expander")){
			if(jQuery(e.target).hasClass("nomNoeudArbre") || jQuery(e.target).hasClass("node_name")){
				var nodeId = jQuery(this).attr("id").replace("node-" + table_identifier + "-", "").replace("-name", "");
			}
			else if(jQuery(e.target).hasClass("treeTableGroupInfoColumn")){
				var nodeId = jQuery(this).parent("tr").attr("id").replace("node-" + table_identifier + "-", "").replace("-name", "");
			}
			selectRowInTreeTable(table_identifier);
			var expanded = reInitTreeTable();
			jQuery("#digirisk_right_side").html(jQuery("#loadingImg").html());
			jQuery("#digirisk_right_side").load(EVA_AJAX_FILE_URL,{
				"post": "true",
				"table": element_type,
				"act": "edit",
				"id": nodeId,
				"partie": "right",
				"menu": "risq",
				"affichage": "affichageListe",
				"partition": "tout",
				"expanded": expanded
			});
			jQuery("#digirisk_left_side").html("");
			jQuery("#digirisk_left_side").load(EVA_AJAX_FILE_URL,{
				"post": "true",
				"table": element_type,
				"act": "edit",
				"id": nodeId,
				"partie": "left",
				"menu": "risq",
				"affichage": "affichageListe",
				"partition": "tout",
				"expanded": expanded
			});
		}
	});
}

/**
*	Define action to launch when a click is detected for a leaf in tree (smaller element)
*/
function main_tree_action_leaf(table_identifier, sub_element_type, delete_message){
	/*	A click is detected on delete button	*/
	jQuery("#" + table_identifier + " .delete-leaf").click(function(){
		var leafId = jQuery(this).parent("tr").attr("id").replace("leaf-", "");
		var expanded = reInitTreeTable();
		if(confirm(digi_html_accent_for_js(delete_message))){
			jQuery("#digirisk_right_side").html("");
			jQuery("#digirisk_left_side").html("");
			jQuery("#ajax-response").load(EVA_AJAX_FILE_URL,{
				"post": "true",
				"table": sub_element_type,
				"act": "delete",
				"id": leafId
			});
		}
	});
	/*	A click is detected on edit button	*/
	jQuery("#" + table_identifier + " .edit-leaf").click(function(){
		var leafId = jQuery(this).parent("tr").attr("id").replace("leaf-", "");
		selectRowInTreeTable(table_identifier);
		var expanded = reInitTreeTable();
		jQuery("#digirisk_right_side").html(jQuery("#loadingImg").html());
		jQuery("#digirisk_right_side").load(EVA_AJAX_FILE_URL,{
			"post": "true",
			"table": sub_element_type,
			"act": "edit",
			"id": leafId,
			"partie": "right",
			"menu": "gestiongrptut",
			"affichage": "affichageListe",
			"expanded": expanded
		});
		jQuery("#digirisk_left_side").html("");
		jQuery("#digirisk_left_side").load(EVA_AJAX_FILE_URL,{
			"post": "true",
			"table": sub_element_type,
			"act": "edit",
			"id": leafId,
			"partie": "left",
			"menu": "gestiongrptut",
			"affichage": "affichageListe",
			"expanded": expanded
		});
	});
	/*	A click is detected on the line	*/
	jQuery(".nomFeuilleArbre, .treeTableInfoColumn").click(function(){
		var leafId = jQuery(this).parent("tr").attr("id").replace("leaf-", "");
		selectRowInTreeTable(table_identifier);
		var expanded = reInitTreeTable();
		jQuery("#digirisk_right_side").html(jQuery("#loadingImg").html());
		jQuery("#digirisk_right_side").load(EVA_AJAX_FILE_URL, {
			"post": "true",
			"table": sub_element_type,
			"act": "edit",
			"id": leafId,
			"partie": "right",
			"menu": "risq",
			"affichage": "affichageListe",
			"expanded": expanded
		});
		jQuery("#digirisk_left_side").html("");
		jQuery("#digirisk_left_side").load(EVA_AJAX_FILE_URL, {
			"post": "true",
			"table": sub_element_type,
			"act": "edit",
			"id": leafId,
			"partie": "left",
			"menu": "risq",
			"affichage": "affichageListe",
			"expanded": expanded
		});
	});
	/*	When a click is detected on evaluation button	*/
	jQuery("#" + table_identifier + " .risk-leaf").click(function(){
		var leafId = jQuery(this).parent("td").parent("tr").attr("id").replace("leaf-", "");
		selectRowInTreeTable(table_identifier);
		var expanded = reInitTreeTable();
		jQuery("#digirisk_right_side").html(jQuery("#loadingImg").html());
		jQuery("#digirisk_right_side").load(EVA_AJAX_FILE_URL,{
			"post": "true",
			"table": sub_element_type,
			"act": "edit",
			"id": leafId,
			"partie": "right",
			"menu": "risq",
			"affichage": "affichageListe",
			"expanded": expanded
		});
		jQuery("#digirisk_left_side").html("");
		jQuery("#digirisk_left_side").load(EVA_AJAX_FILE_URL,{
			"post": "true",
			"table": sub_element_type,
			"act": "edit",
			"id": leafId,
			"partie": "left",
			"menu": "risq",
			"affichage": "affichageListe",
			"expanded": expanded
		});
	});
}

/**
*
*/
function side_reloader(sub_element_type, leafId, menu, expanded){
	jQuery("#digirisk_right_side").html(jQuery("#loadingImg").html());
	jQuery("#digirisk_right_side").load(EVA_AJAX_FILE_URL,{
		"post": "true",
		"table": sub_element_type,
		"act": "edit",
		"id": leafId,
		"partie": "right",
		"menu": menu,
		"affichage": "affichageListe",
		"expanded": expanded
	});
	jQuery("#digirisk_left_side").html("");
	jQuery("#digirisk_left_side").load(EVA_AJAX_FILE_URL,{
		"post": "true",
		"table": sub_element_type,
		"act": "edit",
		"id": leafId,
		"partie": "left",
		"menu": menu,
		"affichage": "affichageListe",
		"expanded": expanded
	});
}
