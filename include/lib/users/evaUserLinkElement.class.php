<?php
/**
*	The different utilities to manage users in evarisk
*
*	@package 		Evarisk
*	@subpackage Users
* @author			Evarisk <contact@evarisk.com>
*/

class evaUserLinkElement {

	/**
	 *	Output a table with the different users binded to an element
	 *
	 *	@param mixed $tableElement The element type we want to get the user list for
	 *	@param integer $idElement The element identifier we want to get the user list for
	 *
	 *	@return mixed $utilisateursMetaBox The entire html code to output
	 */
	function afficheListeUtilisateurTable($tableElement, $idElement) {
		$utilisateursMetaBox = '';
		$idBoutonEnregistrer = 'save_group' . $tableElement;

		$idTable = 'listeIndividus' . $tableElement . $idElement;
		$titres = array( '', ucfirst(strtolower(__('Id.', 'evarisk'))), ucfirst(strtolower(__('Nom', 'evarisk'))), ucfirst(strtolower(__('Pr&eacute;nom', 'evarisk'))), ucfirst(strtolower(__('Inscription', 'evarisk'))));
		unset($lignesDeValeurs);

		//on r�cup�re les utilisateurs d�j� affect�s � l'�l�ment en cours.
		$listeUtilisateursLies = array();
		$utilisateursLies = evaUserLinkElement::getAffectedUser($tableElement, $idElement);
		if (is_array($utilisateursLies ) && (count($utilisateursLies) > 0)) {
			foreach ($utilisateursLies as $utilisateur) {
				$listeUtilisateursLies[$utilisateur->id_user] = $utilisateur;
			}
		}

		$listeUtilisateurs = evaUser::getCompleteUserList();
		if (is_array($listeUtilisateurs) && (count($listeUtilisateurs) > 0)) {
			foreach ($listeUtilisateurs as $utilisateur) {
				unset($valeurs);
				$idLigne = $tableElement . $idElement . 'listeUtilisateurs' . $utilisateur['user_id'];
				$idCbLigne = 'cb_' . $idLigne;
				$moreLineClass = 'userIsNotLinked';
				if(isset($listeUtilisateursLies[$utilisateur['user_id']])){
					$moreLineClass = 'userIsLinked';
				}
				$user_hiring_date = get_user_meta( $utilisateur['user_id'], 'digi_hiring_date', true );
				$valeurs[] = array('value'=>'<span id="actionButton' . $tableElement . 'UserLink' . $utilisateur['user_id'] . '" class="buttonActionUserLinkList ' . $moreLineClass . '  ui-icon pointer" >&nbsp;</span>');
				$valeurs[] = array('value'=>ELEMENT_IDENTIFIER_U . $utilisateur['user_id']);
				$valeurs[] = array('value'=>$utilisateur['user_lastname']);
				$valeurs[] = array('value'=>$utilisateur['user_firstname']);
				$valeurs[] = array('value' => !empty( $user_hiring_date ) ? mysql2date('d M Y', $user_hiring_date, true) . '<input type="hidden" value="' . $user_hiring_date . ' 00:00:00" name="digi-user-hiring-date" id="digi-user-hiring-date-' . $utilisateur['user_id'] . '" />' : mysql2date('d M Y', $utilisateur['user_registered'], true) );
				$lignesDeValeurs[] = $valeurs;
				$idLignes[] = $idLigne;
			}
		}
		else {
			$valeurs[] = array('value'=>'');
			$valeurs[] = array('value'=>'');
			$valeurs[] = array('value'=>__('Aucun r&eacute;sultat trouv&eacute;', 'evarisk'));
			$valeurs[] = array('value'=>'');
			$valeurs[] = array('value'=>'');
			$lignesDeValeurs[] = $valeurs;
			$idLignes[] = $tableElement . $idElement . 'listeUtilisateursVide';
		}

		$classes = array('addUserButtonDTable','userIdentifierColumn','','','');

		switch ( $tableElement ) {
			case digirisk_groups::dbTable:
				$more_script_affect = '
					jQuery("#user_date_of_affectation_action").val("' . current_time('mysql', 0) . '");
					cleanUserIdFiedList(jQuery("#user_id_for_affectation").val(), jQuery("#table_element_user_affectation").val());
					addUserIdFieldList(jQuery("#user_name_info_for_affectation").val(), jQuery("#user_id_for_affectation").val(), jQuery("#table_element_user_affectation").val(), "' . current_time('mysql', 0) . '");';
				$more_script_unaffect = '
					jQuery("#user_date_of_affectation_action").val("' . current_time('mysql', 0) . '");
					deleteUserIdFiedList(jQuery("#user_id_for_affectation").val(), jQuery("#table_element_user_affectation").val());';
				break;
			default:
				$more_script_affect = '
				jQuery("#digi_dialog_affect_user_' . $tableElement . '").dialog("open");
				jQuery("#digi_dialog_affect_user_' . $tableElement . '").dialog("option", "position", { my: "center", at: "center", of: jQuery("#userList' . $tableElement . '") });
				if ( undefined != jQuery( "#digi-user-hiring-date-" + currentId ).val() ) {
					jQuery("#digi_dialog_affect_user_' . $tableElement . '").children( "input" ).val( jQuery( "#digi-user-hiring-date-" + currentId ).val() );
				}';

				$more_script_unaffect = '
 				jQuery("#digi_dialog_unaffect_user_' . $tableElement . '").dialog("open");
				jQuery("#digi_dialog_unaffect_user_' . $tableElement . '").dialog("option", "position", { my: "center", at: "center", of: jQuery("#userList' . $tableElement . '") });';
			break;
		}

		$script =
'<script type="text/javascript">
	digirisk(document).ready(function(){
		digirisk("#' . $idTable . '").dataTable({
			"bAutoWidth": false,
			"bInfo": false,
			"bPaginate": false,
			"bFilter": false,
			"aaSorting": [[4,"asc"]]
		});
		digirisk("#' . $idTable . '").children("tfoot").remove();
		digirisk("#' . $idTable . '").removeClass("dataTables_wrapper");
		digirisk(".buttonActionUserLinkList").click(function(){
			if(digirisk(this).hasClass("addUserToLinkList")){
 				var currentId = digirisk(this).attr("id").replace("actionButton' . $tableElement . 'UserLink", "");
				var lastname = digirisk(this).parent("td").next().next().html();
				var firstname = digirisk(this).parent("td").next().next().next().html();
				jQuery("#user_name_info_for_affectation").val(' . ELEMENT_IDENTIFIER_U . 'currentId + " - " + lastname + " " + firstname);
				jQuery("#user_id_for_affectation").val(currentId);
				jQuery("#table_element_user_affectation").val("' . $tableElement . '");
				' . $more_script_affect . '
				jQuery("#ui-datepicker-div").hide();
			}
			else if(digirisk(this).hasClass("deleteUserToLinkList")){
				jQuery("#user_id_for_affectation").val(digirisk(this).attr("id").replace("actionButton' . $tableElement . 'UserLink", ""));
				jQuery("#table_element_user_affectation").val("' . $tableElement . '");
				' . $more_script_unaffect . '
				jQuery("#ui-datepicker-div").hide();
			}
			checkUserListModification("' . $tableElement . '", "' . $idBoutonEnregistrer . '");
		});

		digirisk("#completeUserList' . $tableElement . ' .odd, #completeUserList' . $tableElement . ' .even").click(function(){
			if(digirisk(this).children("td:first").children("span").hasClass("userIsNotLinked")){
 				var currentId = digirisk(this).attr("id").replace("' . $tableElement . $idElement . 'listeUtilisateurs", "");
				var lastname = digirisk(this).children("td:nth-child(3)").html();
				var firstname = digirisk(this).children("td:nth-child(4)").html();
				jQuery("#user_name_info_for_affectation").val("' . ELEMENT_IDENTIFIER_U . '" + currentId + " - " + lastname + " " + firstname);
				jQuery("#user_id_for_affectation").val(currentId);
				jQuery("#table_element_user_affectation").val("' . $tableElement . '");
				' . $more_script_affect . '
				jQuery("#ui-datepicker-div").hide();
			}
			else{
				jQuery("#user_id_for_affectation").val(digirisk(this).attr("id").replace("' . $tableElement . $idElement . 'listeUtilisateurs", ""));
				jQuery("#table_element_user_affectation").val("' . $tableElement . '");
				' . $more_script_unaffect . '
				jQuery("#ui-datepicker-div").hide();
			}
			checkUserListModification("' . $tableElement . '", "' . $idBoutonEnregistrer . '");
		});
	});
</script>';

		$utilisateursMetaBox .= evaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script);

		return $utilisateursMetaBox;
	}

	/**
	*	Output a table with the different users binded to an element
	*
	*	@param mixed $tableElement The element type we want to get the user list for
	*	@param integer $idElement The element identifier we want to get the user list for
	*
	*	@return mixed $utilisateursMetaBox The entire html code to output
	*/
	function afficheListeUtilisateur($tableElement, $idElement, $showButton = true) {
		$utilisateursMetaBox = '';
		$alreadyLinkedUserId = $alreadyLinkedUser = '';
		$idBoutonEnregistrer = 'save_group' . $tableElement;

		//on r�cup�re les utilisateurs d�j� affect�s � l'�l�ment en cours.
		$listeUtilisateursLies = array();
		$utilisateursLies = evaUserLinkElement::getAffectedUser($tableElement, $idElement, "'valid', 'deleted'");
		if ( is_array($utilisateursLies ) && (count($utilisateursLies) > 0) ) {
			foreach($utilisateursLies as $utilisateur){
				if ( $utilisateur->status == 'valid' ) {
					$listeUtilisateursLies[$utilisateur->id_user] = $utilisateur;
					$alreadyLinkedUserId .= $utilisateur->id_user . ', ';
				}
				$currentUser = evaUser::getUserInformation($utilisateur->id_user);
				$user_affectations_date = (!empty($utilisateur->date_affectation_reelle) && ($utilisateur->date_affectation_reelle != '0000-00-00 00:00:00')) ? __('Entr&eacute;e', 'evarisk') . ' : ' . mysql2date('d/m/Y H:i', $utilisateur->date_affectation_reelle, 'true') : '';
				$user_affectations_date .= (!empty($utilisateur->date_desaffectation_reelle) && ($utilisateur->date_desaffectation_reelle != '0000-00-00 00:00:00')) ? '<br/>' . __('Sortie', 'evarisk') . ' : ' . mysql2date('d/m/Y H:i', $utilisateur->date_desaffectation_reelle, 'true') : '';
				$alreadyLinkedUser .= '<div class="' . (( $utilisateur->status == 'valid' ) ? 'selecteduserOP' : 'deleteduserOP digirisk_hide') . '" id="' . (( $utilisateur->status == 'valid' ) ? 'affectedUser' : 'desaffectedUser') . '' . $tableElement . $utilisateur->id_user . '" title="' . __('Cliquez pour supprimer', 'evarisk') . '" >' . ELEMENT_IDENTIFIER_U . $utilisateur->id_user . '&nbsp;-&nbsp;' . $currentUser[$utilisateur->id_user]['user_lastname'] . ' ' . $currentUser[$utilisateur->id_user]['user_firstname'] . (( $utilisateur->status == 'valid' ) ? '<div class="ui-icon deleteUserFromList" >&nbsp;</div>' : '') . '<div class="user_affectation_date" ><input type="hidden" name="digi_user_affectation_date_' . $utilisateur->id_user . '" id="digi_user_affectation_date_' . $utilisateur->id_user . '" value="' . ((!empty($utilisateur->date_affectation_reelle) && ($utilisateur->date_affectation_reelle != '0000-00-00 00:00:00')) ? substr($utilisateur->date_affectation_reelle, 0, -3) : 'none') . '" />' . $user_affectations_date . '</div></div>';
			}
		}
		else {
			$alreadyLinkedUser = '<span id="noUserSelected' . $tableElement . '" style="margin:5px 10px;color:#646464;" >' . __('Aucun utilisateur affect&eacute;', 'evarisk') . '</span>';
		}

		$utilisateursMetaBox = '
<input type="hidden" name="actuallyAffectedUserIdList' . $tableElement . '" id="actuallyAffectedUserIdList' . $tableElement . '" value="' . $alreadyLinkedUserId . '" />
<input type="hidden" name="affectedUserIdList' . $tableElement . '" id="affectedUserIdList' . $tableElement . '" value="' . $alreadyLinkedUserId . '" />

<div class="alignleft" style="width:44%;" >
	<div id="userListOutput' . $tableElement . '" class="userListOutput ui-widget-content clear" >' . $alreadyLinkedUser . '</div>
</div>

<div class="alignright" style="width:55%;" >';

		if ( current_user_can('add_users') ) {
			$utilisateursMetaBox .= '
	<span class="alignright" ><a target="_blank" href="' . admin_url('users.php?page=digirisk_import_users') . '">' . __('Ajouter des utilisateurs', 'evarisk') . '</a></span>';
		}

		switch ( $tableElement ) {
			case digirisk_groups::dbTable:
				$more_script_affect = '
				jQuery("#completeUserList' . $tableElement . ' .buttonActionUserLinkList").each(function(){
					if(jQuery(this).hasClass("userIsNotLinked")){
						jQuery(this).click();
					}
				});';
				$more_script_unaffect = '
				jQuery("#completeUserList' . $tableElement . ' .buttonActionUserLinkList").each(function(){
					if(jQuery(this).hasClass("userIsLinked")){
						jQuery(this).click();
					}
				});';
			break;
			default:
				$more_script_affect = '
				jQuery("#digi_dialog_affect_user_' . $tableElement . '").dialog("open");
				jQuery("#digi_dialog_affect_user_' . $tableElement . '").dialog("option", "position", { my: "center", at: "center", of: jQuery("#userList' . $tableElement . '") });';

				$more_script_unaffect = '
 				jQuery("#digi_dialog_unaffect_user_' . $tableElement . '").dialog("open");
				jQuery("#digi_dialog_unaffect_user_' . $tableElement . '").dialog("option", "position", { my: "center", at: "center", of: jQuery("#userList' . $tableElement . '") });';
			break;
		}

		$utilisateursMetaBox .= '
	<div class="clear addLinkUserElement" >
		<div class="clear" >
			<span class="searchUserInput ui-icon" >&nbsp;</span>
			<input class="searchUserToAffect" type="text" name="affectedUser' . $tableElement . '" id="searchUser' . $tableElement . '" placeholder="' . __('Rechercher dans la liste des utilisateurs', 'evarisk') . '" />
		</div>
		<div id="completeUserList' . $tableElement . '" class="completeUserList clear" >' . evaUserLinkElement::afficheListeUtilisateurTable($tableElement, $idElement) . '</div>
	</div>
	<div id="massAction' . $tableElement . '" ><span class="checkAll" >' . __('cochez tout', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll" >' . __('d&eacute;cochez tout', 'evarisk') . '</span></div>
</div>

<div class="clear digi_affected_user_list_options_container" id="digi_affected_user_list_options_container" >
	<input type="checkbox" name="view_user_list[]" id="view_user_list_current' . $tableElement . '" value="current_affected" checked /> <label for="view_user_list_current' . $tableElement . '" >' . __('Voir les utilisateurs actuellement affect&eacute;s', 'evarisk') . '</label>
	<br/><input type="checkbox" name="view_user_list[]" id="view_user_list_old' . $tableElement . '" value="old_affected" /> <label for="view_user_list_old' . $tableElement . '" >' . __('Voir les utilisateurs anciennement affect&eacute;s', 'evarisk') . '</label>
	<br/><input type="checkbox" name="view_user_details" id="view_user_affectation_date' . $tableElement . '" value="yes" checked /> <label for="view_user_affectation_date' . $tableElement . '" >' . __('Afficher les dates d\'affectation', 'evarisk') . '</label>
</div>

<div id="userBlocContainer" class="clear hide" ><div onclick="javascript:userDeletion(digirisk(this).attr(\'id\'), \'' . $tableElement . '\');" class="selecteduserOP" title="' . __('Cliquez pour supprimer', 'evarisk') . '" >#USERNAME#<span class="ui-icon deleteUserFromList" >&nbsp;</span><div class="user_affectation_date" >#USERDATEAFFECTATION#</div></div></div>
<div title="' . __('Affectation d\'un utilisateur', 'evarisk') . '" class="digi_affect_user_to_element" id="digi_dialog_affect_user_' . $tableElement . '" >
	<label for="date_ajout' . $tableElement . $idElement . '" >' . __('Date d\'affectation', 'evarisk') . '</label> <input id="date_ajout' . $tableElement . $idElement . '" type="text" value="' . substr(current_time('mysql', 0), 0, 16) . '" name="date_ajout" />
</div>
<div title="' . __('D&eacute;s-affectation d\'un utilisateur', 'evarisk') . '" class="digi_unaffect_user_to_element" id="digi_dialog_unaffect_user_' . $tableElement . '" >
	<label for="date_suppression' . $tableElement . $idElement . '" >' . __('Date de d&eacute;saffectation', 'evarisk') . '</label> <input id="date_suppression' . $tableElement . $idElement . '" type="text" value="' . substr(current_time('mysql', 0), 0, 16) . '" name="date_suppression" />
</div>
<input type="hidden" name="user_date_of_affectation_action" id="user_date_of_affectation_action" value="" />
<input type="hidden" name="user_name_info_for_affectation" id="user_name_info_for_affectation" value="" />
<input type="hidden" name="table_element_user_affectation" id="table_element_user_affectation" value="" />
<input type="hidden" name="user_id_for_affectation" id="user_id_for_affectation" value="" />
<input type="hidden" name="affectation_type" id="affectation_type" value="single_user" />

<script type="text/javascript" >
	digirisk(document).ready(function(){
		/*	Mass action : check / uncheck all	*/
		jQuery("#massAction' . $tableElement . ' .checkAll").unbind("click");
		jQuery("#massAction' . $tableElement . ' .checkAll").click(function(){
			' . $more_script_affect . '
			jQuery("#table_element_user_affectation").val("' . $tableElement . '");
			jQuery("#ui-datepicker-div").hide();
			jQuery("#affectation_type").val("select_all");
		});
		jQuery("#massAction' . $tableElement . ' .uncheckAll").unbind("click");
		jQuery("#massAction' . $tableElement . ' .uncheckAll").click(function(){
			' . $more_script_unaffect . '
			jQuery("#table_element_user_affectation").val("' . $tableElement . '");
			jQuery("#ui-datepicker-div").hide();
			jQuery("#affectation_type").val("deselect_all");
		});
		jQuery.datepicker.regional["fr"] = {
			monthNames: ["' . __('Janvier', 'evarisk') . '","' . __('F&eacute;vrier', 'evarisk') . '","' . __('Mars', 'evarisk') . '","' . __('Avril', 'evarisk') . '","' . __('Mai', 'evarisk') . '","' . __('Juin', 'evarisk') . '", "' . __('Juillet', 'evarisk') . '","' . __('Ao&ucirc;t', 'evarisk') . '","' . __('Septembre', 'evarisk') . '","' . __('Octobre', 'evarisk') . '","' . __('Novembre', 'evarisk') . '","' . __('D&eacute;cembre', 'evarisk') . '"],
			monthNamesShort: ["Jan", "Fev", "Mar", "Avr", "Mai", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            dayNames: ["' . __('Dimanche', 'evarisk') . '", "' . __('Lundi', 'evarisk') . '", "' . __('Mardi', 'evarisk') . '", "' . __('Mercredi', 'evarisk') . '", "' . __('Jeudi', 'evarisk') . '", "' . __('Vendredi', 'evarisk') . '", "' . __('Samedi', 'evarisk') . '"],
			dayNamesShort: ["' . __('Dim', 'evarisk') . '", "' . __('Lun', 'evarisk') . '", "' . __('Mar', 'evarisk') . '", "' . __('Mer', 'evarisk') . '", "' . __('Jeu', 'evarisk') . '", "' . __('Ven', 'evarisk') . '", "' . __('Sam', 'evarisk') . '"],
			dayNamesMin: ["' . __('Di', 'evarisk') . '", "' . __('Lu', 'evarisk') . '", "' . __('Ma', 'evarisk') . '", "' . __('Me', 'evarisk') . '", "' . __('Je', 'evarisk') . '", "' . __('Ve', 'evarisk') . '", "' . __('Sa', 'evarisk') . '"],
		}
    	jQuery.datepicker.setDefaults(jQuery.datepicker.regional["fr"]);
		jQuery.timepicker.regional["fr"] = {
                timeText: "' . __('Heure', 'evarisk') . '",
                hourText: "' . __('Heures', 'evarisk') . '",
                minuteText: "' . __('Minutes', 'evarisk') . '",
                amPmText: ["AM", "PM"],
                currentText: "' . __('Maintenant', 'evarisk') . '",
                closeText: "' . __('OK', 'evarisk') . '",
                timeOnlyTitle: "' . __('Choisissez l\'heure', 'evarisk') . '",
                closeButtonText: "' . __('Fermer', 'evarisk') . '",
                nowButtonText: "' . __('Maintenant', 'evarisk') . '",
                deselectButtonText: "' . __('D&eacute;s&eacute;lectionner', 'evarisk') . '",
		}
    	jQuery.timepicker.setDefaults(jQuery.timepicker.regional["fr"]);


		jQuery("#date_ajout' . $tableElement . $idElement . '").datetimepicker({
			dateFormat: "yy-mm-dd",
			timeFormat: "hh:mm",
			defaultTime: "' . substr( current_time('mysql', 0), 12, 5) . '",
			defaultDate: "' . substr( current_time('mysql', 0), 0, 10) . '",
		});
	//	jQuery("#date_ajout' . $tableElement . $idElement . '").val( "' . substr(current_time('mysql', 0), 0, 10) . '" );
		jQuery("#date_suppression' . $tableElement . $idElement . '").datetimepicker({
			dateFormat: "yy-mm-dd",
			timeFormat: "hh:mm",
			defaultTime: "' . substr( current_time('mysql', 0), 12, 5) . '",
			defaultDate: "' . substr( current_time('mysql', 0), 0, 10) . '",
		});
		//jQuery("#date_suppression' . $tableElement . $idElement . '").val( "' . substr(current_time('mysql', 0), 0, 16) . '" );

		/*	Action when click on delete button	*/
		jQuery("#userList' . $tableElement . ' .selecteduserOP").click(function(){
			if ( jQuery(this).attr("id") ) {
				var current_table_element = jQuery(this).closest("div .userListOutput").attr("id").replace("userListOutput", "");
				userDivId = jQuery(this).attr("id").replace("affectedUser" + current_table_element, "");
 				jQuery("#digi_dialog_unaffect_user_" + current_table_element).dialog("open");
				jQuery("#digi_dialog_unaffect_user_" + current_table_element).dialog("option", "position", { my: "center", at: "center", of: jQuery("#userList" + current_table_element) });
				jQuery("#user_id_for_affectation").val(userDivId);
				jQuery("#table_element_user_affectation").val(current_table_element);
				jQuery("#ui-datepicker-div").hide();
			}
		});
		jQuery("#userListOutput' . $tableElement . ' .selecteduserOP").click(function(){
			if ( jQuery(this).attr("id") ) {
				var current_table_element = jQuery(this).closest("div .userListOutput").attr("id").replace("userListOutput", "");
				userDivId = jQuery(this).attr("id").replace("affectedUser" + current_table_element, "");
				//deleteUserIdFiedList(userDivId, current_table_element);
			}
		});

		/**	Transform a div into a dialog box	*/
		jQuery("#digi_dialog_affect_user_' . $tableElement . '").dialog({
			autoOpen: false,
			modal: true,
			buttons: {
				"' . __('Affecter', 'evarisk') . '": function(){
					jQuery("#user_date_of_affectation_action").val( jQuery("#date_ajout' . $tableElement . $idElement . '").val() );
					if ( jQuery("#affectation_type").val() == "single_user" ) {
						cleanUserIdFiedList(jQuery("#user_id_for_affectation").val(), jQuery("#table_element_user_affectation").val());
						//addUserIdFieldList(jQuery("#user_name_info_for_affectation").val(), jQuery("#user_id_for_affectation").val(), jQuery("#table_element_user_affectation").val(), jQuery("#user_date_of_affectation_action").val());
					}
					else if ( jQuery("#affectation_type").val() == "select_all" ) {
						jQuery("#completeUserList' . $tableElement . ' .buttonActionUserLinkList").each(function() {
							if(jQuery(this).hasClass("userIsNotLinked")) {
								var lastname = digirisk(this).parent("td").next().next().html();
								var firstname = digirisk(this).parent("td").next().next().next().html();
								jQuery("#user_name_info_for_affectation").val("' . ELEMENT_IDENTIFIER_U . '" + jQuery(this).attr("id").replace("actionButton' . $tableElement . 'UserLink", "") + " - " + lastname + " " + firstname);

								cleanUserIdFiedList(jQuery(this).attr("id").replace("actionButton' . $tableElement . 'UserLink", ""), jQuery("#table_element_user_affectation").val());
								//addUserIdFieldList(jQuery("#user_name_info_for_affectation").val(), jQuery(this).attr("id").replace("actionButton' . $tableElement . 'UserLink", ""), jQuery("#table_element_user_affectation").val(), jQuery("#user_date_of_affectation_action").val());
							}
						});
					}

					checkUserListModification("' . $tableElement . '", "' . $idBoutonEnregistrer . '");
					//jQuery("#' . $idBoutonEnregistrer . '").click();
					jQuery(this).dialog("close");
				},
				"' . __('Annuler', 'evarisk') . '": function(){
					jQuery(this).dialog("close");
				},
			},
			close: function(){
				jQuery("#user_name_info_for_affectation").val("");
				jQuery("#table_element_user_affectation").val("");
				jQuery("#user_id_for_affectation").val("");
				jQuery("#affectation_type").val("single_user");
				jQuery("#date_ajout' . $tableElement . $idElement . '").val("' . current_time('mysql', 0) . '");
			},
			position: { my: "center", at: "center", of: jQuery("#userList' . $tableElement . '") },
		});
		jQuery("#digi_dialog_unaffect_user_' . $tableElement . '").dialog({
			autoOpen: false,
			modal: true,
			buttons: {
				"' . __('Desaffecter', 'evarisk') . '": function() {
					jQuery("#user_date_of_affectation_action").val( jQuery("#date_suppression' . $tableElement . $idElement . '").val() );
					var launch_action = false;

					if ( jQuery("#affectation_type").val() == "single_user" ) {
						if ( (jQuery("#date_suppression' . $tableElement . $idElement . '").val() >= jQuery("#digi_user_affectation_date_" + jQuery("#user_id_for_affectation").val()).val()) || confirm( digi_html_accent_for_js(DIGI_USER_DESAFFECTATION_DATE_INCONSISTENCY) ) ) {
							deleteUserIdFiedList(jQuery("#user_id_for_affectation").val(), jQuery("#table_element_user_affectation").val());
							var launch_action = true;
						}
					}
					else if ( jQuery("#affectation_type").val() == "deselect_all" ) {
						var has_inconsistency_date = false;
						var user_list_inconsistency = "";
						jQuery("#completeUserList' . $tableElement . ' .buttonActionUserLinkList").each(function() {
							if(jQuery(this).hasClass("userIsLinked") && (jQuery("#date_suppression' . $tableElement . $idElement . '").val() >= jQuery("#digi_user_affectation_date_" + jQuery(this).attr("id").replace("actionButton' . $tableElement . 'UserLink", "")).val())) {
								has_inconsistency_date = true;
								var lastname = digirisk(this).parent("td").next().next().html();
								var firstname = digirisk(this).parent("td").next().next().next().html();
								user_list_inconsistency += "' . ELEMENT_IDENTIFIER_U . '" + jQuery(this).attr("id").replace("actionButton' . $tableElement . 'UserLink", "") + " - " + lastname + " " + firstname + " / ";
							}
						});
						if ( !has_inconsistency_date || confirm( digi_html_accent_for_js(DIGI_USER_LIST_DESAFFECTATION_DATE_INCONSISTENCY.replace("%s", user_list_inconsistency)) ) ) {
							jQuery("#completeUserList' . $tableElement . ' .buttonActionUserLinkList").each(function() {
								if(jQuery(this).hasClass("userIsLinked")) {
									deleteUserIdFiedList(jQuery(this).attr("id").replace("actionButton' . $tableElement . 'UserLink", ""), jQuery("#table_element_user_affectation").val());
								}
							});
							var launch_action = true;
						}
					}

					if ( launch_action ) {
						checkUserListModification("' . $tableElement . '", "' . $idBoutonEnregistrer . '");
						//jQuery("#' . $idBoutonEnregistrer . '").click();
						jQuery(this).dialog("close");
					}
				},
				"' . __('Annuler', 'evarisk') . '": function(){
					jQuery(this).dialog("close");
				},
			},
			close: function(){
				jQuery("#user_name_info_for_affectation").val("");
				jQuery("#table_element_user_affectation").val("");
				jQuery("#user_id_for_affectation").val("");
				jQuery("#date_suppression' . $tableElement . $idElement . '").val("' . current_time('mysql', 0) . '");
			},
			position: { my: "center", at: "center", of: jQuery("#userList' . $tableElement . '") },
		});

		jQuery("#date_ajout' . $tableElement . $idElement . '").live("click", function(){
			jQuery("#ui-datepicker-div").show();
		});

		jQuery("#date_suppression' . $tableElement . $idElement . '").live("click", function(){
			jQuery("#ui-datepicker-div").show();
		});

		/*	Autocomplete search	*/
		jQuery("#searchUser' . $tableElement . '").autocomplete({
			source: "' . EVA_INC_PLUGIN_URL . 'liveSearch/searchUsers.php",
			select: function( event, ui ) {
				if(jQuery("#completeUserList' . $tableElement . ' #actionButton' . $tableElement . 'UserLink" + ui.item.value).hasClass("userIsNotLinked")){
					jQuery("#completeUserList' . $tableElement . ' #actionButton' . $tableElement . 'UserLink" + ui.item.value).click();
				}
				jQuery("#user_name_info_for_affectation").val(ui.item.label);
				jQuery("#user_id_for_affectation").val(ui.item.value);
				jQuery("#table_element_user_affectation").val("' . $tableElement . '");
				jQuery("#ui-datepicker-div").hide();

				setTimeout(function(){
					jQuery("#searchUser' . $tableElement . '").val("");
					jQuery("#searchUser' . $tableElement . '").blur();
				}, 2);
			}
		});

		jQuery("#view_user_list_current' . $tableElement . '").live("click", function(){
			if ( jQuery(this).is(":checked") ) {
				jQuery("#userListOutput' . $tableElement . ' .selecteduserOP").each(function(){
					jQuery(this).show();
				});
			}
			else {
				jQuery("#userListOutput' . $tableElement . ' .selecteduserOP").each(function(){
					jQuery(this).hide();
				});
			}
		});

		jQuery("#view_user_list_old' . $tableElement . '").live("click", function(){
			if ( jQuery(this).is(":checked") ) {
				jQuery("#userListOutput' . $tableElement . ' .deleteduserOP").each(function(){
					jQuery(this).show();
				});
			}
			else {
				jQuery("#userListOutput' . $tableElement . ' .deleteduserOP").each(function(){
					jQuery(this).hide();
				});
			}
		});

		jQuery("#view_user_affectation_date' . $tableElement . '").live("click", function(){
			if ( jQuery(this).is(":checked") ) {
				jQuery("#userListOutput' . $tableElement . ' .selecteduserOP .user_affectation_date, #userListOutput' . $tableElement . ' .deleteduserOP .user_affectation_date").each(function(){
					jQuery(this).show();
				});
			}
			else {
				jQuery("#userListOutput' . $tableElement . ' .selecteduserOP .user_affectation_date, #userListOutput' . $tableElement . ' .deleteduserOP .user_affectation_date").each(function(){
					jQuery(this).hide();
				});
			}
		});
	});
</script>';

		if($showButton){
			$alternate_button = '';
			switch($tableElement){
				case TABLE_GROUPEMENT:
				case TABLE_GROUPEMENT . '_evaluation':
					if(!current_user_can('digi_edit_groupement') && !current_user_can('digi_edit_groupement_' . $idElement)){
						$showButton = false;
					}
				break;
				case TABLE_UNITE_TRAVAIL:
				case TABLE_UNITE_TRAVAIL . '_evaluation':
					if(!current_user_can('digi_edit_unite') && !current_user_can('digi_edit_unite_' . $idElement)){
						$showButton = false;
					}
				break;

				case TABLE_TACHE:
					if(!current_user_can('digi_edit_task')){
						$showButton = false;
					}
					else{
						$currentTask = new EvaTask($idElement);
						$currentTask->load();
						$ProgressionStatus = $currentTask->getProgressionStatus();

						if((($ProgressionStatus == 'Done') || ($ProgressionStatus == 'DoneByChief')) && (digirisk_options::getOptionValue('possibilite_Modifier_Tache_Soldee') == 'non') ){
							$alternate_button = '
				<br class="clear" />
				<div class="alignright button-primary" id="TaskSaveButton" >
					' . __('Cette t&acirc;che est sold&eacute;e, vous ne pouvez pas modifier les utilisateurs', 'evarisk') . '
				</div>';
						}
					}
				break;
				case TABLE_ACTIVITE:
					if(!current_user_can('digi_edit_action')){
						$showButton = false;
					}
					else{
						$current_action = new EvaActivity($idElement);
						$current_action->load();
						$ProgressionStatus = $current_action->getProgressionStatus();

						if((($ProgressionStatus == 'Done') || ($ProgressionStatus == 'DoneByChief')) && (digirisk_options::getOptionValue('possibilite_Modifier_Action_Soldee') == 'non') ){
							$alternate_button = '
				<br class="clear" />
				<div class="alignright button-primary" id="TaskSaveButton" >
					' . __('Cette t&acirc;che est sold&eacute;e, vous ne pouvez pas modifier les utilisateurs', 'evarisk') . '
				</div>';
						}
					}
				break;
			}
		}

		if($showButton){//Bouton Enregistrer
			$scriptEnregistrement = '<script type="text/javascript">
				digirisk(document).ready(function() {
					checkUserListModification("' . $tableElement . '", "' . $idBoutonEnregistrer . '");
					digirisk("#' . $idBoutonEnregistrer . '").click( function(){
						digirisk("#saveButtonLoading' . $tableElement . '").show();
						digirisk("#saveButtonContainer' . $tableElement . '").hide();
						digirisk("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
							"table": "' . TABLE_LIAISON_USER_ELEMENT . '",
							"act": "save",
							"utilisateurs": digirisk("#affectedUserIdList' . $tableElement . '").val(),
							"date_action_affection": jQuery("#user_date_of_affectation_action").val(),
							"tableElement": "' . $tableElement . '",
							"idElement": "' . $idElement . '"
						});
					});
				});
				</script>';

			if($alternate_button != ''){
				$utilisateursMetaBox .= $alternate_button;
			}
			else{
				$utilisateursMetaBox .= '<div class="clear" ><div id="saveButtonLoading' . $tableElement . '" style="display:none;" class="clear alignright" ><img src="' . PICTO_LOADING_ROUND . '" alt="loading in progress" /></div><div id="saveButtonContainer' . $tableElement . '" >' . EvaDisplayInput::afficherInput('button', $idBoutonEnregistrer, __('Enregistrer', 'evarisk'), null, '', 'save', false, true, '', 'button-primary alignright', '', '', $scriptEnregistrement) . '</div></div>';
			}
		}

		return $utilisateursMetaBox;
	}

	/**
	*	Get the user linked to an element
	*
	*	@param mixed $tableElement The element type we want to get the user list for
	*	@param integer $idElement The element identifier we want to get the user list for
	*
	*	@return object A wordpress object with the user list affected to the given element
	*/
	public static function getAffectedUser($tableElement, $idElement, $link_status = "'valid'", $return_data_type = 'OBJECT') {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT *, DATEDIFF( date_desaffectation_reelle, date_affectation_reelle ) AS duration_in_days, TIMEDIFF( date_desaffectation_reelle, date_affectation_reelle ) AS duration_in_hour
			FROM " . TABLE_LIAISON_USER_ELEMENT . "
			WHERE id_element = '%s'
				AND table_element = '%s'
				AND status IN (" . $link_status . ") "
			, $idElement, $tableElement
		);

		return $wpdb->get_results($query, $return_data_type);
	}

	/**
	*	Get the user linked to an element
	*
	*	@param mixed $tableElement The element type we want to get the user list for
	*	@param integer $user_id The user identifier we want to get the element list for
	*
	*	@return object A wordpress object with the user list affected to the given element
	*/
	function get_user_affected_element($user_id, $tableElement = '', $link_status = "'valid'"){
		global $wpdb;

		$condition = array();
		$condition[] = $user_id;
		$more_query = "";
		if($tableElement != ''){
			$condition[] = $tableElement;
			$more_query = "AND table_element = '%s'";
		}
		$query = $wpdb->prepare(
			"SELECT *
			FROM " . TABLE_LIAISON_USER_ELEMENT . "
			WHERE id_user = '%d'
				" . $more_query . "
				AND status IN (" . $link_status . ") "
			, $condition
		);

		return $wpdb->get_results($query);
	}

	/**
	*	Create a link betwwen an element and a user
	*
	*	@param mixed $tableElement The element type we want to create a link to
	*	@param integer $idElement The element identifier we want to create a link to
	*	@param array $userIdList An user list id to create link with the selected element
	*
	*	@return mixed $messageInfo An html output that contain the result message
	*/
	function setLinkUserElement($tableElement, $idElement, $userIdList, $outputMessage = true, $date = null) {
		global $wpdb;
		global $current_user;
		$userToTreat = "  ";

		//on r�cup�re les utilisateurs d�j� affect�s � l'�l�ment en cours.
		$listeUtilisateursLies = array();
		$utilisateursLies = evaUserLinkElement::getAffectedUser($tableElement, $idElement);
		if(is_array($utilisateursLies ) && (count($utilisateursLies) > 0)){
			foreach($utilisateursLies as $utilisateur){
				$listeUtilisateursLies[$utilisateur->id_user] = $utilisateur;
			}
		}

		/*	Transform the new element list to affect into an array	*/
		$newUserList = explode(", ", $userIdList);

		$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Il n\'a aucune modification a apporter', 'evarisk') . '</strong>');

		/*	Read the product list already linked for checking if they are again into the list or if we have to delete them form the list	*/
		$done_element = 0;
		$deleted_user_list = array();
		foreach($listeUtilisateursLies as $utilisateurs){
			if(is_array($newUserList) && !in_array($utilisateurs->id_user, $newUserList)){
				$dissociate_user_of_element = $wpdb->update( TABLE_LIAISON_USER_ELEMENT, array( 'status' => 'deleted', 'date_desaffectation_reelle' => (!empty($date) ? $date : current_time('mysql', 0)), 'date_desAffectation' => current_time('mysql', 0), 'id_desAttributeur' => $current_user->ID,), array( 'id' => $utilisateurs->id, ) );
				$done_element += $dissociate_user_of_element;
				if(($tableElement == TABLE_TACHE) || ($tableElement == TABLE_ACTIVITE)){
					$wpdb->update(DIGI_DBT_LIAISON_USER_NOTIFICATION_ELEMENT, array('status' => 'deleted', 'date_desAffectation' => current_time('mysql', 0), 'date_desaffectation_reelle' => (!empty($date) ? $date : current_time('mysql', 0)), 'id_desAttributeur' => $current_user->ID), array('id_user' => $utilisateurs->id_user, 'id_element' => $idElement, 'table_element' => $tableElement));
				}
				$deleted_user_list[] = $utilisateurs->id;

				/*	Log modification on element and notify user if user subscribe	*/
				digirisk_user_notification::log_element_modification($tableElement, $idElement, 'delete_user_from_affectation_list', $utilisateursLies, $deleted_user_list);
			}
		}
		if($done_element > 0){
			$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Les utilisateurs ont bien &eacute;t&eacute; supprim&eacute; de la liste des utilisateurs affect&eacute;s', 'evarisk') . '</strong>');
		}

		if(is_array($newUserList) && (count($newUserList) > 0)){
			foreach($newUserList as $userId){
				if((trim($userId) != '') && !array_key_exists($userId, $listeUtilisateursLies)){
					$userToTreat .= "('', 'valid', '" . current_time('mysql', 0) . "', '" . $current_user->ID . "', '0000-00-00 00:00:00', '', '" . $userId . "', '" . $idElement . "', '" . $tableElement . "', '" . (!empty($date) ? $date : current_time('mysql', 0)) . "', '0000-00-00 00:00:00'), ";
				}
			}
		}

		$endOfQuery = trim(substr($userToTreat, 0, -2));
		if($endOfQuery != ""){
			$query = $wpdb->prepare(
				"REPLACE INTO " . TABLE_LIAISON_USER_ELEMENT . "
					(id, status, date_affectation, id_attributeur, date_desAffectation, id_desAttributeur, id_user, id_element, table_element, date_affectation_reelle, date_desaffectation_reelle)
				VALUES
					" . $endOfQuery . "", ""
			);
			if($wpdb->query($query)){
				$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Les modifications ont correctement &eacute;t&eacute enregistr&eacute;es', 'evarisk') . '</strong>');
				switch($tableElement){
					case TABLE_ACTIVITE:
					case TABLE_TACHE:
						/*	Log modification on element and notify user if user subscribe	*/
						digirisk_user_notification::log_element_modification($tableElement, $idElement, 'user_affectation_update', $utilisateursLies, $newUserList);
					break;
				}
			}
			else{
				$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Les modifications n\'ont pas toutes &eacute;t&eacute correctement enregistr&eacute;es', 'evarisk') . '</strong>"');
			}
		}

		$script = '';
		if($outputMessage){
			$script .= '
		actionMessageShow("#messageInfo_' . $tableElement . $idElement . '_affectUser", "' . $message . '");
		setTimeout(\'actionMessageHide("#messageInfo_' . $tableElement . $idElement . '_affectUser")\',7500);
		jQuery("#saveButtonLoading' . $tableElement . '").hide();
		jQuery("#saveButtonContainer' . $tableElement . '").show();
		jQuery("#actuallyAffectedUserIdList' . $tableElement . '").val(jQuery("#affectedUserIdList' . $tableElement . '").val());
		checkUserListModification("' . $tableElement . '", "save_group' . $tableElement . '");';
			$script .= '
		jQuery("#userList' . $tableElement . '").html(jQuery("#loadingImg").html());
		jQuery("#userList' . $tableElement . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
			post:"true",
			"table": "' . TABLE_LIAISON_USER_ELEMENT . '",
			"act": "reload_user_affectation_box",
			"tableElement": "' . $tableElement . '",
			"idElement": "' . $idElement . '"
		});';
		}
		if(($tableElement == TABLE_TACHE) || ($tableElement == TABLE_ACTIVITE)){
			$script .= '
		jQuery("#userNotificationContainerBox").html(jQuery("#loadingImg").html());
		jQuery("#userNotificationContainerBox").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
			post:"true",
			"table": "' . DIGI_DBT_ELEMENT_NOTIFICATION . '",
			"act": "reload_user_notification_box",
			"tableElement": "' . $tableElement . '",
			"idElement": "' . $idElement . '"
		});';
		}

		if($script != ''){
			echo
'<script type="text/javascript">
	digirisk(document).ready(function(){
		' . $script . '
	});
</script>';
		}
	}

}