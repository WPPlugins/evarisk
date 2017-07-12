<?php
/**
* Work accident management
*
*	Define the different tools to manage work accident
* @author Evarisk <dev@evarisk.com>
* @version 5.1.4.0
* @package Digirisk
* @subpackage librairies
*/

/**
*	Define the different tools to manage work accident
* @package Digirisk
* @subpackage librairies
*/
class digirisk_accident
{
	/**
	*	Define the current page database
	*/
	const dbTable = DIGI_DBT_ACCIDENT;
	/**
	*	Define the current page code
	*/
	const currentPageCode = 'accident';

	/**
	*	Define the work accident box content
	*
	*	@param array $arguments The different informations for the post box definition
	*
	*	@return string $post_box_content The html output code for the work accident box
	*/
	function get_post_box($arguments){
		$idElement = $arguments['idElement'];
		$tableElement = $arguments['tableElement'];
		$post_box_content = '
		<div id="message_accident" class="hide" >&nbsp;</div>
		<ul class="eva_tabs" style="margin-bottom:2px;" >
			<li id="ongletVoirLesAccidents" class="tabs selected_tab" style="display:inline; margin-left:0.4em;" ><label tabindex="1">' . ucfirst(strtolower(sprintf(__('voir %s', 'evarisk'), __('les accidents', 'evarisk')))) . '</label></li>
			<li id="ongletAjouterUnAccident" class="tabs" style="display:inline; margin-left:0.4em;" ><label tabindex="2">' . ucfirst(strtolower(sprintf(__('Ajouter %s', 'evarisk'), __('un accident', 'evarisk')))) . '</label></li>
		</ul>
		<div id="divAccidenContainer" class="eva_tabs_panel" >' . digirisk_accident::get_accident_list($tableElement, $idElement) . '</div>
		<script type="text/javascript">
			digirisk(document).ready(function(){
				//	Show the risk list for the actual element
				jQuery("#ongletVoirLesAccidents").click(function(){
					jQuery("#divAccidenContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
						"post":"true",
						"table":"' . self::dbTable . '",
						"act":"reloadVoirAccident",
						"tableElement":"' . $tableElement . '",
						"idElement":' . $idElement . '
					});
					commonTabChange("postBoxAccidents", "#divAccidenContainer", "#ongletVoirLesAccidents");
				});
				jQuery("#ongletAjouterUnAccident").live("click", function(){
					jQuery("#divAccidenContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
						"post":"true",
						"table":"' . self::dbTable . '",
						"act":"addAccident",
						"tableElement":"' . $tableElement . '",
						"idElement":' . $idElement . '
					});
					commonTabChange("postBoxAccidents", "#divAccidenContainer", "#ongletAjouterUnAccident");
				});
			});
		</script>';

		echo $post_box_content;
	}

	/***
	*	Return an output of the existing accident
	*
	*	@param string $tableElement The element type we want to associate the accident to
	*	@param integer $idElement The element identifier we want to associate the accident to
	*
	*	@return mixed The html output list of existing accident
	*/
	function get_accident_list($tableElement, $idElement){
		$element_list_metabox = '';

		/*	Start the table definition	*/
		$tableId = self::dbTable . '_list';
		$tableSummary = __('Existing accident listing', 'evarisk');
		$tableTitles = array();
		$tableTitles[] = __('Id.', 'evarisk');
		$tableTitles[] = __('Titre', 'evarisk');
		$tableTitles[] = __('Date', 'evarisk');
		$tableTitles[] = __('Statut', 'evarisk');
		$tableTitles[] = __('Victime', 'evarisk');
		$tableTitles[] = __('Actions', 'evarisk');
		$tableClasses = array();
		$tableClasses[] = 'evarisk_' . self::currentPageCode . '_identifier_column';
		$tableClasses[] = 'evarisk_' . self::currentPageCode . '_title_column';
		$tableClasses[] = 'evarisk_' . self::currentPageCode . '_date_column';
		$tableClasses[] = 'evarisk_' . self::currentPageCode . '_state_column';
		$tableClasses[] = 'evarisk_' . self::currentPageCode . '_victim_column';
		$tableClasses[] = 'evarisk_' . self::currentPageCode . '_action_column';
		unset($lignesDeValeurs);

		$accident_list = self::getElement();
		if(is_array($accident_list) && (count($accident_list) > 0)){
			foreach($accident_list as $accident){
				$row_id = 'accident-' . $accident->id;
				unset($tableRowValue);
				$tableRowValue[] = array('class' => self::currentPageCode . '_identifier_cell', 'value' => ELEMENT_IDENTIFIER_AT . $accident->id);
				$tableRowValue[] = array('class' => self::currentPageCode . '_title_cell', 'value' => (($accident->accident_title != '') ? $accident->accident_title : 'NA'));
				$tableRowValue[] = array('class' => self::currentPageCode . '_date_cell', 'value' => (($accident->accident_date != '') ? mysql2date('d/m/Y', $accident->accident_date, true) : 'NA'));
				$tableRowValue[] = array('class' => self::currentPageCode . '_' . $accident->declaration_state . '_state_cell', 'value' => __($accident->declaration_state, 'evarisk'));
				$user_meta = evaUser::getUserInformation($accident->victim_id);
				$tableRowValue[] = array('class' => self::currentPageCode . '_victim_cell', 'value' => (($accident->victim_id > 0) ? ELEMENT_IDENTIFIER_U . $accident->victim_id . '&nbsp;-&nbsp;' . $user_meta[$accident->victim_id]['user_firstname'] . '&nbsp;' . $user_meta[$accident->victim_id]['user_lastname'] : 'NA') );
				$tableRowValue[] = array('class' => self::currentPageCode . '_action_cell', 'value' => '<img style="width:' . TAILLE_PICTOS . ';" id="' . $row_id . '-edit" src="' . PICTO_EDIT . '" alt="' . __('Editer', 'evarisk') . '" title="' . __('Editer', 'evarisk') . '" class="edit-accident" /><img style="width:' . TAILLE_PICTOS . ';" id="' . $row_id . '-delete" src="' . PICTO_DELETE . '" alt="' . __('Supprimer', 'evarisk') . '" title="' . __('Supprimer', 'evarisk') . '" class="delete-accident" />');
				$tableRows[] = $tableRowValue;
				$tableRowsId[] = $row_id;
			}
		}
		else{
			unset($tableRowValue);
			$tableRowValue[] = array('class' => self::currentPageCode . '_identifier_cell', 'value' => '');
			$tableRowValue[] = array('class' => self::currentPageCode . '_title_cell', 'value' => __('Aucun accident', 'evarisk'));
			$tableRowValue[] = array('class' => self::currentPageCode . '_date_cell', 'value' => '');
			$tableRowValue[] = array('class' => self::currentPageCode . '_state_cell', 'value' => '');
			$tableRowValue[] = array('class' => self::currentPageCode . '_victim_cell', 'value' => '');
			$tableRowValue[] = array('class' => self::currentPageCode . '_action_cell', 'value' => '');
			$tableRows[] = $tableRowValue;
			$tableRowsId[] = 'no-accident-found';
		}

		$element_list_metabox .= EvaDisplayDesign::getTable($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, '') . '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#' . $tableId . '").dataTable({
			"oLanguage":{
				"sUrl": "' . EVA_INC_PLUGIN_URL . 'js/dataTable/jquery.dataTables.common_translation.txt"
			}
		});

		jQuery(".edit-accident").click(function(){
			jQuery("#divAccidenContainer").html(jQuery("#loadingImg").html());
			commonTabChange("postBoxAccidents", "#divAccidenContainer", "#ongletAjouterUnAccident");
			jQuery("#divAccidenContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
				"post":"true",
				"table":"' . DIGI_DBT_ACCIDENT . '",
				"act":"load",
				"accident_id": jQuery(this).attr("id").replace("accident-", "").replace("-edit", ""),
				"idElement":"' . $idElement . '",
				"tableElement":"' . $tableElement . '"
			});
		});

		jQuery(".delete-accident").click(function(){
			if(confirm(digi_html_accent_for_js("' . __('&Eacute;tes vous s&ucirc;r de vouloir supprimer cet accident?', 'evarisk') . '"))){
				jQuery("#divAccidenContainer").html(jQuery("#loadingImg").html());
				jQuery("#divAccidenContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
					"post":"true",
					"table":"' . DIGI_DBT_ACCIDENT . '",
					"act":"delete_accident",
					"accident_id": jQuery(this).attr("id").replace("accident-", "").replace("-delete", ""),
					"idElement":"' . $idElement . '",
					"tableElement":"' . $tableElement . '"
				});
			}
		});
	});
</script>';

		return $element_list_metabox;
	}

	/**
	*	Define the form to declare a new accident or to edit an existing one
	*
	*	@param string $tableElement The element type we want to manage accident for
	*	@param string $idElement The element identifier we want to manage accident for
	*	@param integer $accident_id OPTIONNAL An accident id allowing to edit an existing accident
	*
	*	@return mixed $accident_form The form to manage accidents
	*/
	function get_accident_form($tableElement, $idElement, $accident_id = ''){
		$global_form_error = 0;
		$accident = null;

		$accident_step = 1;
		if($accident_id > 0){
			$accident = self::getElement($accident_id);
			$accident_step = $accident->declaration_step;
		}

		$accident_form = sprintf(__('Les informations demand&eacute;es ci-dessous proviennent du cerfa n&ordm;%s', 'evarisk'), '<a target="cerfa_accident_travail_ameli" href="' . CERFA_ACCIDENT_TRAVAIL_LINK . '" >' . CERFA_ACCIDENT_TRAVAIL_IDENTIFIER . '</a>');
		$accident_form .= '
	<div id="accident_element_updater" title="' . __('&Eacute;dition d\'informations manquantes', 'evarisk') . '" class="hide" >&nbsp;</div>
	<form id="accident_form" action="' . EVA_INC_PLUGIN_URL . 'ajax.php" method="post" >
		<input type="hidden" name="post" id="post" value="true" />
		<input type="hidden" name="table" id="table" value="' . DIGI_DBT_ACCIDENT . '" />
		<input type="hidden" name="tableElement" id="tableElement" value="' . $tableElement . '" />
		<input type="hidden" name="idElement" id="idElement" value="' . $idElement . '" />
		<input type="hidden" name="act" id="act" value="save-accident" />
		<input type="hidden" name="accident_id" id="accident_id" value="' . $accident_id . '" />
		<input type="hidden" name="accident_form_error_nb" id="accident_form_error_nb" value="0" />
		<input type="hidden" name="accident_form_step" id="accident_form_step" value="' . $accident_step . '" />';

		/*	Add employer part	*/
		$accident_place_part = self::get_accident_form_part('accident_place', $tableElement, $idElement, $accident);
		$global_form_error += $accident_place_part['error'];
		$accident_form .= '<div id="accident_place_part" >' . $accident_place_part['part'] . '</div>';

		/*	Add the user part	*/
		$accident_form .= '<div id="accident_user_part" >';
		if($accident_step >= 2){
			$accident_victim_part = self::get_accident_form_part('victim', $tableElement, $idElement, $accident);
			$global_form_error += $accident_victim_part['error'];
			$accident_form .= $accident_victim_part['part'];
		}
		$accident_form .= '</div>';

		/*	Add the accident informations part	*/
		$accident_form .= '<div id="accident_part" >';
		if($accident_step >= 3){
			$accident_part = self::get_accident_form_part('accident', $tableElement, $idElement, $accident);
			$global_form_error += $accident_part['error'];
			$accident_form .= $accident_part['part'];
		}
		$accident_form .= '</div>';

		/*	Add the witnesses part	*/
		$accident_form .= '<div id="accident_witnesses_part" >';
		if($accident_step >= 4){
			$witness_part = self::get_accident_form_part('witness', $tableElement, $idElement, $accident);
			$global_form_error += $witness_part['error'];
			$accident_form .= $witness_part['part'];
		}
		$accident_form .= '</div>';

		/*	Add the third party part	*/
		$accident_form .= '<div id="accident_third_party_part" >';
		if($accident_step >= 5){
			$third_party_part = self::get_accident_form_part('third_party', $tableElement, $idElement, $accident);
			$global_form_error += $third_party_part['error'];
			$accident_form .= $third_party_part['part'];
		}
		$accident_form .= '</div>';

		$save_button_class = '';
		if($global_form_error > 0){
			$save_button_class = 'disabled="disabled"';
		}

		/*	Save button	*/
		$save_accident_button = __('&Eacute;tape suivante', 'evarisk');
		if($accident_step >= 5){
			$save_accident_button = __('Enregistrer l\'accident', 'evarisk');
		}
		if(($accident_step >= 2)){
			$accident_form .= '
		<input type="button" name="previous_step" id="previous_step" value="' . __('&Eacute;tape pr&eacute;c&eacute;dente') . '" class="button-primary alignleft" />';
		}
		if($accident_id > 0){
			$accident_form .= '
	<img id="reload_accident_form" src="' . EVA_IMG_ICONES_PLUGIN_URL . 'reload_vs.png" alt="" title="" class="alignleft" />';
		}
		$accident_form .= '
		<input type="submit" ' . $save_button_class . ' name="save_accident" id="save_accident" value="' . $save_accident_button . '" class="button-primary" />';

		$accident_form .= '
	</form>';

		{/*	Javascript action	*/
			$accident_form .= '
		<script type="text/javascript" >
			digirisk(document).ready(function(){
				/**
				*	Add a dialog box allowing to edit different element composing an accident
				*/
				jQuery("#accident_element_updater").dialog({
					autoOpen: false, width: 800, height: 600, modal: true,
					close: function(){
						jQuery(this).html("");
					},
					buttons:{
						"' . __('Enregistrer', 'evarisk') . '": function(){
							createGroupement("save_groupement_missing_informations", "' . TABLE_GROUPEMENT . '");
							jQuery(this).dialog("close");
						}
					}
				});
				jQuery(".edit_missing_information").click(function(){
					jQuery("#accident_form_error_nb").val(0);
					jQuery("#accident_element_updater").dialog("open");
					jQuery("#accident_element_updater").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
						"post": "true",
						"table": "' . TABLE_GROUPEMENT . '",
						"act": "load_groupement_form",
						"tableElement": "' . TABLE_GROUPEMENT . '",
						"idElement": jQuery(this).attr("id").replace("element_", "")
					});
				});

				/**
				*
				*/
				jQuery("#reload_accident_form").click(function(){
					var accident_id = jQuery("#accident_id").val();
					jQuery("#divAccidenContainer").html(jQuery("#loadingImg").html());
					jQuery("#divAccidenContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
						"post":"true",
						"table":"' . DIGI_DBT_ACCIDENT . '",
						"act":"load",
						"accident_id": accident_id,
						"idElement":"' . $idElement . '",
						"tableElement":"' . $tableElement . '"
					});
				});

				/*	Victim Search autocompletion	*/
				jQuery("#accident_victim_search_in_all_user").click(function(){
					if(jQuery(this).is(":checked")){
						jQuery("#search_in_all_user_' . $tableElement . '").val("yes");
					}
					else{
						jQuery("#search_in_all_user_' . $tableElement . '").val("no");
					}
				});

				/*	Autocomplete search	*/
				jQuery("#searchUser_accident_' . $tableElement . '").autocomplete({
					source: "' . EVA_INC_PLUGIN_URL . 'liveSearch/searchUsers.php?table_element=' . $tableElement . '&id_element=' . $idElement . '&search_type=work_accident&all_user=yes",
					select: function( event, ui ){
						jQuery("#work_accident_user_details").html(jQuery("#loadingImg").html());
						jQuery("#work_accident_user_details").load(EVA_AJAX_FILE_URL,{
							"post":"true",
							"act":"loadUserInfo",
							"id_user":ui.item.value
						});

						setTimeout(function(){
							jQuery("#searchUser_accident_' . $tableElement . '").val("");
							jQuery("#searchUser_accident_' . $tableElement . '").blur();
						}, 2);
					}
				});

				jQuery("#victim_changer").click(function(){
					jQuery("#victim_selector").toggle();
					jQuery("#victim_changer span").toggleClass("accident_container_opener");
					jQuery("#victim_changer span").toggleClass("accident_container_closer");
					jQuery("#work_accident_user_details").toggle();
				});

				/*	Autocomplete search	*/
				jQuery("#search_accident_witness_' . $tableElement . '").autocomplete({
					source: "' . EVA_INC_PLUGIN_URL . 'liveSearch/searchUsers.php?table_element=' . $tableElement . '&id_element=' . $idElement . '&search_type=work_accident_witness",
					select: function( event, ui ){
						jQuery("#search_accident_witness_details").append("<div id=\'accident_witness_" + ui.item.value  + "\' ></div>");
						jQuery("#accident_witness_" + ui.item.value).load(EVA_AJAX_FILE_URL,{
							"post":"true",
							"act":"loadWitnessInfo",
							"id_user":ui.item.value
						});

						setTimeout(function(){
							jQuery("#search_accident_witness_' . $tableElement . '").val("");
							jQuery("#search_accident_witness_' . $tableElement . '").blur();
						}, 2);
					}
				});

				/**
				*	Add action for accident declaration type combo box
				*/
				if(jQuery("#accident_declaration_type").val() == "registered"){
					jQuery("#people_declaration").hide();
					jQuery("#infirmary_register").show();
				}
				else{
					jQuery("#people_declaration").show();
					jQuery("#infirmary_register").hide();
				}
				jQuery("#accident_declaration_type").change(function(){
					if(jQuery(this).val() == "registered"){
						jQuery("#people_declaration").hide();
						jQuery("#infirmary_register").show();
					}
					else{
						jQuery("#people_declaration").show();
						jQuery("#infirmary_register").hide();
					}
				});

				/**
				*	THIRD PARTY
				*/
				if(jQuery("#caused_by_third_party").val() == "oui"){
					jQuery("#third_party_details").show();
				}
				jQuery("#caused_by_third_party").change(function(){
					if(jQuery(this).val() == "non"){
						jQuery("#third_party_details").hide();
					}
					else{
						jQuery("#third_party_details").show();
					}
				});

				/*	Autocomplete search	*/
				jQuery("#search_accident_third_party_' . $tableElement . '").autocomplete({
					source: "' . EVA_INC_PLUGIN_URL . 'liveSearch/searchUsers.php?table_element=' . $tableElement . '&id_element=' . $idElement . '&search_type=work_accident_third_party",
					select: function( event, ui ){
						jQuery("#search_accident_third_party_details").append("<div id=\'accident_third_party_" + ui.item.value  + "\' ></div>");
						jQuery("#accident_third_party_" + ui.item.value).load(EVA_AJAX_FILE_URL,{
							"post":"true",
							"act":"loadThirdPartyInfo",
							"id_user":ui.item.value
						});
						setTimeout(function(){
							jQuery("#search_accident_third_party_' . $tableElement . '").val("");
							jQuery("#search_accident_third_party_' . $tableElement . '").blur();
						}, 2);
					}
				});

				/**
				*	POLICE REPORT
				*/
				if(jQuery("#police_report").val() == "oui"){
					jQuery("#accident_witnesses_police_report_writer").show();
					jQuery("#accident_witnesses_police_report_writer_1").show();
				}
				jQuery("#police_report").change(function(){
					if(jQuery(this).val() == "non"){
						jQuery("#accident_witnesses_police_report_writer").hide();
					}
					else{
						jQuery("#accident_witnesses_police_report_writer").show();
					}
				});

				/*	Autocomplete search	*/
				jQuery("#accident_police_report_writer").autocomplete({
					source: "' . EVA_INC_PLUGIN_URL . 'liveSearch/searchUsers.php?table_element=' . $tableElement . '&id_element=' . $idElement . '&search_type=work_accident_police_report_writer",
					select: function( event, ui ){
						jQuery(this).val(ui.item.label);

						setTimeout(function(){
							jQuery("#accident_police_report_writer").val("");
							jQuery("#accident_police_report_writer").blur();
						}, 2);
					}
				});

				/**
				*	Add save button action
				*/
				jQuery("#accident_form").ajaxForm({
					target: "#ajax-response"
				});

				/**
				*	Add action on part legend
				*/
				jQuery(".digi_work_accident_part_separator legend").click(function(){
					var current_element = jQuery(this).attr("id");
					jQuery("#accident_"+ current_element).toggle();
					if(jQuery("#opener_"+ current_element).hasClass("accident_container_opener")){
						jQuery("#opener_"+ current_element).addClass("accident_container_closer");
						jQuery("#opener_"+ current_element).removeClass("accident_container_opener");
					}
					else if(jQuery("#opener_"+ current_element).hasClass("accident_container_closer")){
						jQuery("#opener_"+ current_element).addClass("accident_container_opener");
						jQuery("#opener_"+ current_element).removeClass("accident_container_closer");
					}
				});

				/**
				*	Add action on previous step button
				*/
				jQuery("#previous_step").click(function(){
					var new_step = parseInt(jQuery("#accident_form_step").val()) - 1;
					jQuery("#divAccidenContainer").html(jQuery("#loadingImg").html());
					jQuery("#divAccidenContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
						"post":"true",
						"table":"' . DIGI_DBT_ACCIDENT . '",
						"act":"previous_step",
						"accident_id": "' . $accident_id . '",
						"idElement":"' . $idElement . '",
						"tableElement":"' . $tableElement . '",
						"step_to_load":new_step
					});
				});
			});

			/**
			*	Delete a line
			*/
			function remove_current_line(type, line_number){
				jQuery("#delete_selected_" + type + "_" + line_number).remove();
				jQuery("#accident_" + type + "_" + line_number).remove();
			}
		</script>';
		}

		return $accident_form;
	}

	/**
	*	Define the different part of the accident creation form
	*/
	function get_accident_form_part($part, $tableElement = '', $idElement = '', $accident = null){
		global $wpdb, $digi_hour, $digi_minute, $optionAciddentConsequence, $optionAccidentDeclarationType, $optionAccidentDeclarationBy, $optionYesNoList;
		$error = 0;
		$container_state = $container_more_content = $accident_form_part = '';
		$container_state_opener = 'accident_container_closer';

		/*	Add the current working element information (Just for information)	*/
		switch($tableElement){
			case TABLE_GROUPEMENT:
			{
				$groupement = EvaGroupement::getGroupement($idElement);
				$groupementPere = Arborescence::getPere($tableElement, $groupement);

				$nomElement = $groupement->nom;
			}
			break;
			case TABLE_UNITE_TRAVAIL:
			{
				$uniteTravail = eva_UniteDeTravail::getWorkingUnit($idElement);
				$groupementPere = EvaGroupement::getGroupement($uniteTravail->id_groupement);

				$nomElement = $uniteTravail->nom;
			}
			break;
		}
		$ancetres = Arborescence::getAncetre(TABLE_GROUPEMENT, $groupementPere);
		$miniFilAriane = '';
		foreach($ancetres as $ancetre){
			if($ancetre->nom != "Groupement Racine"){
				$miniFilAriane .= $ancetre->nom . ' &raquo; ';
			}
		}
		if($groupementPere->nom != "Groupement Racine"){
			$miniFilAriane .= $groupementPere->nom . ' &raquo; ';
		}

		switch($part){
			case 'accident_place':{
				if(($accident != null) && ($accident->declaration_state == 'in_progress') && ($accident->declaration_step > 1)){
					$container_state = ' class="hide" ';
					$container_more_content = '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'veille-reponse.gif" alt="step_done" />';
					$container_state_opener = 'accident_container_opener';
				}
				$idAdresse = 0;
				$parent_employer = false;
				$employer_part = '';
				$accident_form_part .= '
		<fieldset class="digi_work_accident_part_separator" >
			<legend id="employer" ><span id="opener_employer" class="alignleft ui-icon ' . $container_state_opener . '" >&nbsp;</span>' . __('L\'employeur', 'evarisk') . '&nbsp;' . $container_more_content . '</legend>
			<div id="accident_employer" ' . $container_state . '>';
				if($groupement->typeGroupement != 'employer'){
					if($groupementPere->typeGroupement == 'employer'){
						$parent_employer = true;
						$employer_part .= '
				<input class="clear employer_input" type="hidden" readonly="readonly"  id="employer_id" value="' . $groupementPere->id . '" name="employer[id]"/>
				<input class="clear employer_input" style="width:95%" type="text" readonly="readonly"  id="employer" value="' . $miniFilAriane . $nomElement . '" name="employer[name]"/>';
						$idAdresse = $groupementPere->id_adresse;
						$employer_id = $groupementPere->id;
					}
					else{
						reset($ancetres);
						foreach($ancetres as $ancetre){
							if(($ancetre->nom != "Groupement Racine") && ($ancetre->typeGroupement == 'employer') && !$parent_employer){
								$employer_part .= '
				<input class="clea employer_inputr" type="hidden" readonly="readonly"  id="employer_id" value="' . $ancetre->id . '" name="employer[id]"/>
				<input class="clear employer_input" style="width:95%" type="text" readonly="readonly"  id="employer" value="' . $ancetre->nom . '" name="employer[name]"/>';
								$idAdresse = $ancetre->id_adresse;
								$parent_employer = true;
								$employer_id = $ancetre->id;
							}
						}
					}
				}
				else{
					$employer_part .= '
				<input class="clearemployer_input" type="hidden" id="employer_id" value="' . $idElement . '" name="employer[id]"/>
				<input class="clearemployer_input" style="width:95%" type="text" readonly="readonly"  id="employer" value="' . $miniFilAriane . $nomElement . '" name="employer[name]"/>';
					$idAdresse = $groupement->id_adresse;
					$employer_id = $idElement;
				}
				if(!empty($employer_id)){
					$employer_part .= self::get_element_address($idAdresse, 'employer');
					$accident_form_part .= '
			<div class="edit_missing_information employer_editor alignright" id="element_' . $employer_id . '" ><img src="' . str_replace(".png", "_vs.png", PICTO_EDIT) . '" alt="" /></div>';
					if($parent_employer){
						// $accident_form_part .= '
			// <span class="" >' . __('L\'&eacute;l&eacute;ment que vous avez s&eacute;lectionn&eacute; n\'est pas un employeur, nous avons s&eacute;lectionn&eacute; l\'&eacute;l&eacute;ment situ&eacute; au dessus dans la hi&eacute;rarchie et d&eacute;fini comme employeur', 'evarisk') . '</span>';
					}
					$accident_form_part .= $employer_part . '
			<br/><label for="employer_telephone" >' . __('N&ordm; de T&eacute;l&eacute;phone', 'evarisk') . '</label>
			<input class="clear employer_input" style="width:45%" type="text" readonly="readonly" id="employer_telephone" value="' . $groupement->telephoneGroupement . '" name="employer[telephone]"/>';
				}
				else{
					$accident_form_part .= '
		' . __('Vous n\'avez d&eacute;fini aucun &eacute;l&eacute;ment de votre architecture comme &eacute;tant un employeur', 'evarisk');
					$error++;
				}
				$accident_form_part .= '
			</div>
		</fieldset>';

				$accident_form_part .= '
		<fieldset class="digi_work_accident_part_separator" >
			<legend id="establishment" ><span id="opener_establishment" class="alignleft ui-icon ' . $container_state_opener . '" >&nbsp;</span>' . __('L\'&Eacute;tablissement', 'evarisk') . '&nbsp;' . $container_more_content . '</legend>
			<div id="accident_establishment" ' . $container_state . '>
				<div class="edit_missing_information establishment_editor alignright" id="element_' . $idElement . '" ><img src="' . str_replace(".png", "_vs.png", PICTO_EDIT) . '" alt="" /></div>
				<input class="clear establishment_input" type="hidden" readonly="readonly" id="establishment_id" value="' . $groupementPere->id . '" name="establishment[id]"/>
				<input class="clear establishment_input" style="width:95%" type="text" readonly="readonly"  id="establishment_name" value="' . $nomElement . '" tabindex="1" name="establishment[name]"/>
				' . self::get_element_address($groupement->id_adresse, 'establishment') . '
				<br/><label for="establishment_telephone" >' . __('N&ordm; de T&eacute;l&eacute;phone', 'evarisk') . '</label>
				<input class="clear establishment_input" style="width:45%" type="text" readonly="readonly" id="establishment_telephone" value="' . $groupement->telephoneGroupement . '" name="establishment[telephone]"/>
				<br/><label for="establishment_siret" >' . __('N&ordm; Siret de l\'&eacute;tablissement', 'evarisk') . '</label>
				<input class="clear establishment_input" style="width:99%" type="text" readonly="readonly" id="establishment_siret" value="' . $groupement->siret . '" name="establishment[siret]"/>
				<br/><label for="establishment_social_activity_number" >' . __('Num&eacute;ro de risque S&eacute;curit&eacute; Sociale figurant sur la notification du taux applicable &agrave; l\'activit&eacute; dans laquelle est comptabilis&eacute; le salaire de la victime', 'evarisk') . '</label>
				<input class="clear establishment_input" style="width:99%" type="text" readonly="readonly" id="establishment_social_activity_number" value="' . $groupement->social_activity_number . '" name="establishment[social_activity_number]"/>
			</div>
		</fieldset>';
			}
			break;
			case 'victim':
			{
				if(($accident != null) && ($accident->declaration_state == 'in_progress') && ($accident->declaration_step > 2)){
					$container_state = ' hide ';
					$container_more_content = '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'veille-reponse.gif" alt="step_done" />';
					$container_state_opener = 'accident_container_opener';
				}
				if(($accident != null) && ($accident->victim_id <= 0)){
					$error++;
				}
				$accident_form_part .= '
		<fieldset class="digi_work_accident_part_separator" >
			<legend id="victim" ><span id="opener_victim" class="alignleft ui-icon ' . $container_state_opener . '" >&nbsp;</span>' . __('La victime', 'evarisk') . '&nbsp;' . $container_more_content . '</legend>
			<div class="clear ' . $container_state . '" id="accident_victim" >
				<div id="victim_changer" class="clear hide" ><span class="ui-icon accident_container_opener alignleft" >&nbsp;</span>' . __('Changer la victime', 'evarisk') . '</div>
				<div id="victim_selector" >';
				if(current_user_can('add_users')){
					$accident_form_part .= '
					<span class="alignright" ><a href="' .  admin_url('users.php?page=digirisk_import_users') . '" >' . __('Ajouter des utilisateurs', 'evarisk') . '</a></span>';
				}
				$accident_form_part .= '
					<span class="searchUserInput ui-icon" >&nbsp;</span>
					<input class="searchUserToAffect" type="text" name="affectedUser' . $tableElement . '" id="searchUser_accident_' . $tableElement . '" placeholder="' . __('Rechercher dans la liste des utilisateurs', 'evarisk') . '" /><!-- <div class="accident_search_params clear" ><input type="checkbox" name="accident_victim_search_in_all_user" id="accident_victim_search_in_all_user" value="yes" /><input type="hidden" name="search_in_all_user_' . $tableElement . '" id="search_in_all_user_' . $tableElement . '" value="no" />' . __('Chercher dans tous les utilisteurs', 'evarisk') . '</div> -->
					<div id="completeUserList' . DIGI_DBT_ACCIDENT . '" class="completeUserList clear" >' . evaUser::afficheListeUtilisateurTable_SimpleSelection(DIGI_DBT_ACCIDENT, $idElement) . '</div>
				</div>';

				$accident_form_part .= '
				<div id="work_accident_user_details" class="clear" >';
				if(($accident != null) && ($accident->victim_id > 0)){
					$accident_form_part .= self::get_victim_accident_informations($accident->victim_id, $accident);
				}
				$accident_form_part .= '
				</div>
			</div>
		</fieldset>';
			}
			break;
			case 'accident':
			{
				if(($accident != null) && ($accident->declaration_state == 'in_progress') && ($accident->declaration_step > 3)){
					$container_state = ' class="hide" ';
					$container_more_content = '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'veille-reponse.gif" alt="step_done" />';
					$container_state_opener = 'accident_container_opener';
				}
				$accident_form_part .= '
		<fieldset class="digi_work_accident_part_separator" >
			<legend id="details" ><span id="opener_victim" class="alignleft ui-icon ' . $container_state_opener . '" >&nbsp;</span>' . __('L\'accident', 'evarisk') . '&nbsp;' . $container_more_content . '</legend>
			<div id="accident_details" ' . $container_state . ' >';

			{/*	Work accident title. Allows to define a human readable element	*/
				$locale = preg_replace('/([^_]+).+/', '$1', get_locale());
				$locale = ($locale == 'en') ? '' : $locale;
				$accident_form_part .= EvaDisplayInput::afficherInput('text', 'titre_accident', (($accident != null) ? $accident->accident_title : ''), '', __('Titre', 'evarisk'), 'accident[accident_title]', false, false, 255, '', '', '95%') . '
			<div class="clear accident_part" >
				<div class="accident_date" >
					' .	EvaDisplayInput::afficherInput('text', 'accident_date', (($accident != null) ? $accident->accident_date . ' ' . $accident->accident_hour : ''), '', __('Date de l\'accident', 'evarisk'), 'accident[accident_date]', false, false, 255, '', '', '95%') . '
				</div>
			</div>
			<script type="text/javascript" >
				digirisk(document).ready(function(){
					jQuery("#accident_date").datepicker(jQuery.datepicker.regional["' . $locale . '"]);
					jQuery("#accident_date").datepicker("option", "dateFormat", "yy-mm-dd");
					jQuery("#accident_date").datepicker("option", "changeMonth", true);
					jQuery("#accident_date").datepicker("option", "changeYear", true);
					jQuery("#accident_date").datepicker("option", "navigationAsDateFormat", true);
					jQuery("#accident_date").val("' . str_replace('"', '\"', str_replace("
", "\\n", $accident->accident_date)) . '");
				});
			</script>';
			}

			{/*	Victim work schedule	*/
				$accident_from_hour_1 = $accident_from_minute_1 = $accident_to_hour_1 = $accident_to_minute_1 = '';
				$accident_from_hour_2 = $accident_from_minute_2 = $accident_to_hour_2 = $accident_to_minute_2 = '';
				if($accident != null){
					$accident_victim_work_shedule_details = unserialize($accident->accident_victim_work_shedule);
					$from_1 = explode(":", $accident_victim_work_shedule_details['from1']);
					$accident_from_hour_1 = $from_1[0];
					$accident_from_minute_1 = $from_1[1];
					$to_1 = explode(":", $accident_victim_work_shedule_details['to1']);
					$accident_to_hour_1 = $to_1[0];
					$accident_to_minute_1 = $to_1[1];
					$from_2 = explode(":", $accident_victim_work_shedule_details['from2']);
					$accident_from_hour_2 = $from_2[0];
					$accident_from_minute_2 = $from_2[1];
					$to_2 = explode(":", $accident_victim_work_shedule_details['to2']);
					$accident_to_hour_2 = $to_2[0];
					$accident_to_minute_2 = $to_2[1];
				}
				$accident_form_part .= '
			<div class="clear accident_part" >
				<label>' . __('Horaire de la victime le jour de l\'accident', 'evarisk') . '</label><br/>
				<div class="accident_victim_work_shedule_1" >
					<div class="alignleft" >
						' . __('De', 'evarisk') . '&nbsp;
						' .	EvaDisplayInput::createComboBox('accident_from_hour_1', 'accident[accident_victim_work_shedule][accident_from_hour_1]', $digi_hour, $accident_from_hour_1) . ':
						' .	EvaDisplayInput::createComboBox('accident_from_minute_1', 'accident[accident_victim_work_shedule][accident_from_minute_1]', $digi_minute, $accident_from_minute_1) . '
					</div>
					<div class="alignleft" >
						&nbsp;&nbsp;' . __('&agrave;', 'evarisk') . '&nbsp;
						' .	EvaDisplayInput::createComboBox('accident_to_hour_1', 'accident[accident_victim_work_shedule][accident_to_hour_1]', $digi_hour, $accident_to_hour_1) . ':
						' .	EvaDisplayInput::createComboBox('accident_to_minute_1', 'accident[accident_victim_work_shedule][accident_to_minute_1]', $digi_minute, $accident_to_minute_1) . '
					</div>
				</div>
				<div class="accident_victim_work_shedule_2" >
					<div  class="alignleft" >
						' . __('Et', 'evarisk') . '&nbsp;&nbsp;&nbsp;&nbsp;
						' . __('De', 'evarisk') . '&nbsp;
						' .	EvaDisplayInput::createComboBox('accident_from_hour_2', 'accident[accident_victim_work_shedule][accident_from_hour_2]', $digi_hour, $accident_from_hour_2) . ':
						' .	EvaDisplayInput::createComboBox('accident_from_minute_2', 'accident[accident_victim_work_shedule][accident_from_minute_2]', $digi_minute, $accident_from_minute_2) . '
					</div>
					<div  class="alignleft" >
						&nbsp;&nbsp;' . __('&agrave;', 'evarisk') . '&nbsp;
						' .	EvaDisplayInput::createComboBox('accident_to_hour_2', 'accident[accident_victim_work_shedule][accident_to_hour_2]', $digi_hour, $accident_to_hour_2) . ':
						' .	EvaDisplayInput::createComboBox('accident_to_minute_2', 'accident[accident_victim_work_shedule][accident_to_minute_2]', $digi_minute, $accident_to_minute_2) . '
					</div>
				</div>
			</div>';
			}

			{/*	Accident details	*/
				$accident_form_part .= '
			<div class="clear accident_part" >'
				. EvaDisplayInput::afficherInput('text', 'accident_place', (($accident != null) ? $accident->accident_place : ''), '', __('Lieu de l\'accident', 'evarisk'), 'accident[accident_place]', false, false, 61, '', '', '95%')
				. EvaDisplayInput::afficherInput('textarea', 'circonstance_accident', (($accident != null) ? $accident->accident_details : ''), '', __('Circonstance d&eacute;taill&eacute;es de l\'accident', 'evarisk') . '&nbsp;<span class="accident_details_explanation" >' . __('Indiquez, le cas &eacute;ch&eacute;ant l\'appareil, la machine ou le moyen de locomotion utilis&eacute;', 'evarisk') . '</span>', 'accident[accident_details]', false, false, 6, '', '', '95%') . '
			</div>';
			}

			{/*	Accident hurts type	*/
				$accident_form_part .= '
			<div class="clear accident_part" id="accident_hurt_part" >'
				. EvaDisplayInput::afficherInput('text', 'accident_hurt_place', (($accident != null) ? $accident->accident_hurt_place : ''), '', __('Si&egrave;ge des l&eacute;sions', 'evarisk'), 'accident[accident_hurt_place]', false, false, 61, '', '', '95%', '', '', true)
				. EvaDisplayInput::afficherInput('text', 'accident_hurt_nature', (($accident != null) ? $accident->accident_hurt_nature : ''), '', __('Nature des l&eacute;sions', 'evarisk'), 'accident[accident_hurt_nature]', false, false, 61, '', '', '95%', '', '', true) . '
			</div>';
			}

			{/*	Accident victim transported at	*/
				$accident_form_part .= '
			<div class="clear accident_part" >'
				. EvaDisplayInput::afficherInput('text', 'accident_victim_transported_at', (($accident != null) ? $accident->accident_victim_transported_at : ''), '', __('Victime transport&eacute;e &agrave;', 'evarisk'), 'accident[accident_victim_transported_at]', false, false, 65, '', '', '95%') . '
			</div>';
			}

			{/*	Accident declaration type	*/
				$accident_declaration_type = $accident_declaration_by = $accident_declaration_register_nb = $accident_declaration_register_nb = '';
				if($accident != null){
					$accident_declaration_details = unserialize($accident->accident_declaration);
					$accident_declaration_type = $accident_declaration_details['type'];
					$accident_declaration_by = $accident_declaration_details['accident_declaration_by'];
					$accident_declaration_register_nb = $accident_declaration_details['accident_declaration_register_nb'];
					$accident_declaration_date = $accident_declaration_details['accident_declaration_date'];
				}

				$locale = preg_replace('/([^_]+).+/', '$1', get_locale());
				$locale = ($locale == 'en') ? '' : $locale;
				$accident_form_part .= '
			<div class="clear accident_part" >
				<label >' . __('L\'accident &agrave; &eacute;t&eacute;', 'evarisk') . '</label><br/>
				<table summary="accident declaration details" cellpadding="0" cellspacing="0" class="accident_declaration_table" >
					<tr>
						<td>' .	EvaDisplayInput::createComboBox('accident_declaration_type', 'accident[accident_declaration][type]', $optionAccidentDeclarationType, $accident_declaration_type) . '</td>
						<td >
							<div id="people_declaration" >' .	EvaDisplayInput::createComboBox('accident_declaration_by', 'accident[accident_declaration][accident_declaration_by]', $optionAccidentDeclarationBy, $accident_declaration_by) . '</div>
							<div class="hide alignleft" id="infirmary_register" ><div class="accident_declaration_made_by_prefix" >' . __('sous le n&ordm;', 'evarisk') . '</div>' .	EvaDisplayInput::afficherInput('text', 'accident_declaration_register_nb', $accident_declaration_register_nb, '', null, 'accident[accident_declaration][accident_declaration_register_nb]', false, false, 255, '', '', '', '', 'left') . '</div>
						</td>
						<td>
							<div class="accident_declaration_by_date_prefix" >' . __('Le', 'evarisk') . '</div>
							' .	EvaDisplayInput::afficherInput('text', 'accident_declaration_date', $accident_declaration_date, '', null, 'accident[accident_declaration][accident_declaration_date]', false, false, 10, '', '', '90%', '', 'left', true) . '
							<script type="text/javascript" >
								digirisk(document).ready(function(){
									jQuery("#accident_declaration_date").datepicker(jQuery.datepicker.regional["' . $locale . '"]);
									jQuery("#accident_declaration_date").datepicker("option", "dateFormat", "yy-mm-dd");
									jQuery("#accident_declaration_date").datepicker("option", "changeMonth", true);
									jQuery("#accident_declaration_date").datepicker("option", "changeYear", true);
									jQuery("#accident_declaration_date").datepicker("option", "navigationAsDateFormat", true);
									jQuery("#accident_declaration_date").val("' . str_replace('"', '\"', str_replace("
", "\\n", $accident_declaration_date)) . '");
								});
							</script>
						</td>
					</tr>
				</table>
			</div>';
			}

			{/*	Accident consequences	*/
				$accident_form_part .= '
			<div class="clear accident_part" >
				<label >' . __('Cons&eacute;quences de l\'accident', 'evarisk') . '</label><br/>
				' .	EvaDisplayInput::createComboBox('accident_consequence', 'accident[accident_consequence]', $optionAciddentConsequence, (($accident != null) ? $accident->accident_consequence : ''), 'user_combo') . '
			</div>';
			}

				$accident_form_part .= '
				</div>
		</fieldset>';
			}
			break;
			case 'witness':
			{
				if(($accident != null) && ($accident->declaration_state == 'in_progress') && ($accident->declaration_step > 4)){
					$container_state = ' class="hide" ';
					$container_more_content = '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'veille-reponse.gif" alt="step_done" />';
					$container_state_opener = 'accident_container_opener';
				}
				$witness_list = '&nbsp;';
				if($accident != null){
					$query = $wpdb->prepare("SELECT * FROM " . DIGI_DBT_ACCIDENT_THIRD_PARTY . " WHERE status = 'valid' AND id_accident = %d AND third_party_type = %s", $accident->id, 'witness');
					$witnesses_list = $wpdb->get_results($query);
					$witness_count = 0;
					foreach($witnesses_list as $witness){
						$witness_list .= self::get_accident_third_party_informations($witness->id_user, 'witness', $witness, $accident);
						$witness_count++;
					}
				}
				$accident_form_part = '
		<fieldset class="digi_work_accident_part_separator" >
			<legend id="witness" ><span id="opener_victim" class="alignleft ui-icon ' . $container_state_opener . '" >&nbsp;</span>' . __('T&eacute;moins', 'evarisk') . '&nbsp;' . $container_more_content . '</legend>
			<div id="accident_witness" ' . $container_state . ' >
				<span class="searchUserInput ui-icon" >&nbsp;</span>
				<input class="searchUserToAffect" type="text" name="search_accident_witness_' . $tableElement . '" id="search_accident_witness_' . $tableElement . '" placeholder="' . __('Rechercher dans la liste des utilisateurs', 'evarisk') . '" />';
				if(current_user_can('add_users')){
					$accident_form_part .= '
				<span class="alignright" ><a href="' .  admin_url('users.php?page=digirisk_import_users') . '" >' . __('Ajouter des utilisateurs', 'evarisk') . '</a></span>';
				}
				$accident_form_part .= '
				<div id="completeUserList' . DIGI_DBT_ACCIDENT . 'witness" class="completeUserList clear" >' . evaUser::afficheListeUtilisateurTable_SimpleSelection(DIGI_DBT_ACCIDENT . 'witness', $idElement) . '</div>
				<div id="search_accident_witness_details" class="clear" >' . $witness_list . '</div>
				<br/><br/>
				<div>
					' . __('Un rapport de police a-t-il &eacute;t&eacute; &eacute;tabli', 'evarisk') . '&nbsp;:&nbsp;' . EvaDisplayInput::createComboBox('police_report', 'accident_police_report', $optionYesNoList, (($accident != null) ? $accident->police_report : '')) . '
					<div id="accident_witnesses_police_report_writer" class="hide" >'
						. EvaDisplayInput::afficherInput('text', 'accident_police_report_writer', (($accident != null) ? $accident->police_report_writer : ''), '', __('par :', 'evarisk'), 'accident_police_report_writer', false, false, 61, '', '', '95%', '', '', true) . '
					</div>
				</div>
			</div>
		</fieldset>';
			}
			break;
			case 'third_party':
			{
				if(($accident != null) && ($accident->declaration_state == 'in_progress') && ($accident->declaration_step > 5)){
					$container_state = ' class="hide" ';
					$container_more_content = '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'veille-reponse.gif" alt="step_done" />';
					$container_state_opener = 'accident_container_opener';
				}
				$third_party_list = '&nbsp;';
				if($accident != null){
					$query = $wpdb->prepare("SELECT * FROM " . DIGI_DBT_ACCIDENT_THIRD_PARTY . " WHERE status = 'valid' AND id_accident = %d AND third_party_type = %s", $accident->id, 'third_party');
					$third_parties = $wpdb->get_results($query);
					foreach($third_parties as $third_party){
						$third_party_list .= self::get_accident_third_party_informations($third_party->id_user, 'third_party', $third_party, $accident);
					}
				}
				$accident_form_part = '
		<fieldset class="digi_work_accident_part_separator" >
			<legend id="third_party" ><span id="opener_victim" class="alignleft ui-icon ' . $container_state_opener . '" >&nbsp;</span>' . __('Tiers', 'evarisk') . '&nbsp;' . $container_more_content . '</legend>
			<div id="accident_third_party" ' . $container_state . ' >
				<div>
					' . __('L\'accident a t\'il &eacute;t&eacute; caus&eacute; par un tiers', 'evarisk') . ' ?' . EvaDisplayInput::createComboBox('caused_by_third_party', 'accident_caused_by_third_party', $optionYesNoList, (($accident != null) ? $accident->accident_caused_by_third_party : '')) . '
				</div>
				<br/>
				<div class="hide" colspan="4" id="third_party_details" >
					<span class="searchUserInput ui-icon" >&nbsp;</span>
					<input class="searchUserToAffect" type="text" name="search_accident_third_party_' . $tableElement . '" id="search_accident_third_party_' . $tableElement . '" placeholder="' . __('Rechercher dans la liste des utilisateurs', 'evarisk') . '" />';
					if(current_user_can('add_users'))
					{
						$accident_form_part .= '
					<span class="alignright" ><a href="' .  admin_url('users.php?page=digirisk_import_users') . '" >' . __('Ajouter des utilisateurs', 'evarisk') . '</a></span>';
					}
					$accident_form_part .= '
					<div id="completeUserList' . DIGI_DBT_ACCIDENT . 'third_party" class="completeUserList clear" >' . evaUser::afficheListeUtilisateurTable_SimpleSelection(DIGI_DBT_ACCIDENT . 'third_party', $idElement) . '</div>
					<div id="search_accident_third_party_details" class="clear" >' . $third_party_list . '</div>
				</div>
			</div>
		</fieldset>';
			}
			break;
			default:
			{
				$accident_form_part = __('Vous n\'avez pas s&eacute;lectionn&eacute; d\'&eacute;tapes', 'evarisk');
			}
			break;
		}

		$form['part'] = $accident_form_part;
		$form['error'] = $error;

		return $form;
	}

	/**
	*	Get and output the adress for a given element
	*/
	function get_element_address($id_address, $field_prefix){
		$noAddress = false;
		if($id_address > 0){
			$address = new EvaAddress($id_address);
			$address->load();
			if(($address->getFirstLine() == '') && ($address->getSecondLine() == '') && ($address->getPostalCode() == '') && ($address->getCity() == '')){
				$noAddress = true;
			}
		}
		else{
			$noAddress = true;
		}

		if($noAddress){
			$address_output = '
	<span class="required" >' . __('Aucune adresse n\'a &eacute;t&eacute; renseign&eacute;e pour cet &eacute;l&eacute;ment', 'evarisk') . '</span>';
		}
		else{
			$address_output .= '
		<label >' . __('Adresse', 'evarisk') . '</label>
		<input class="clear ' . $field_prefix . '_input" type="hidden" id="' . $field_prefix . '_adresse_id" value="' . $idElement . '" name="' . $field_prefix . '[adresse_id]"/>
		<input class="clear ' . $field_prefix . '_input" style="width:95%" type="text" readonly="readonly" id="' . $field_prefix . '_address_1" value="' . $address->getFirstLine() . '" name="' . $field_prefix . '[address_1]"/>
		<input class="clear ' . $field_prefix . '_input" style="width:95%" type="text" readonly="readonly" id="' . $field_prefix . '_address_2" value="' . $address->getSecondLine() . '" name="' . $field_prefix . '[address_2]"/>
		<input class="clear ' . $field_prefix . '_input" style="width:45%" type="text" readonly="readonly" id="' . $field_prefix . '_postal_code" value="' . $address->getPostalCode() . '" name="' . $field_prefix . '[postal_code]"/>
		<input class="clear ' . $field_prefix . '_input" style="width:45%" type="text" readonly="readonly" id="' . $field_prefix . '_city" value="' . $address->getCity() . '" name="' . $field_prefix . '[city]"/>';
	}

		return $address_output;
	}
	/**
	*	Get and output informations about victim
	*/
	function get_victim_accident_informations($id_user, $accident = null){
		global $optionYesNoList;
		$user_meta = get_user_meta($id_user, 'digirisk_information', false);
		$user_metas = get_user_meta($id_user);
		if(is_array($user_meta[0]) && (count($user_meta[0]) > 0) && ($user_meta[0]['user_is_valid_for_accident'] == 'yes')){
			$user_main_info = evaUser::getUserInformation($id_user);
			foreach($user_meta[0] as $field_identifier => $field_content){
				$user_meta[0][$field_identifier] = stripslashes($field_content);
			}
			$user_form = '
<input type="hidden" name="accident_user[victim_id]" id="victim_id" value="' . $id_user . '" />
<table id="work_accident_user_information" summary="accident user information" cellspacing="0" cellpadding="0" >
	<tr>
		<td class="bold" >' . __('Nom', 'evarisk') . '</td><td>' . $user_main_info[$id_user]['user_lastname'] . '</td>
		<td class="bold" >' . __('Date de naissance', 'evarisk') . '</td><td>' . mysql2date('d M Y', $user_meta[0]['user_birthday'], true) . '</td>
	</tr>
	<tr>
		<td class="bold" >' . __('Pr&eacute;nom', 'evarisk') . '</td><td>' . $user_main_info[$id_user]['user_firstname'] . '</td>
		<td class="bold" >' . __('Sexe', 'evarisk') . '</td><td>' . $optionUserGender[$user_meta[0]['user_gender']] . '</td>
	</tr>
	<tr>
		<td class="bold" >&nbsp;</td><td>&nbsp;</td>
		<td class="bold" >' . __('Nationnalit&eacute;', 'evarisk') . '</td><td>' . $optionUserNationality[$user_meta[0]['user_nationnality']] . '</td>
	</tr>
	<tr>
		<td class="bold" >&nbsp;</td><td>&nbsp;</td>
		<td class="bold" >' . __('N&ordm; d\'immatriculation', 'evarisk') . '</td><td>' . $user_meta[0]['user_imatriculation'] . '&nbsp;' . $user_meta[0]['user_imatriculation_key'] . '</td>
	</tr>
	<tr>
		<td class="bold" colspan="4" >&nbsp;</td>
	</tr>
	<tr>
		<td class="bold" colspan="2" >' . __('Adresse', 'evarisk') . '</td><td colspan="2" >' . $user_meta[0]['user_adress'] . '</td>
	</tr>
	<tr>
		<td class="bold" colspan="2" >&nbsp;</td><td colspan="2" >' . $user_meta[0]['user_adress_2'] . '</td>
	</tr>
	<tr>
		<td class="bold" colspan="2" >&nbsp;</td><td colspan="2" >' . $user_meta[0]['user_adress_postal_code'] . '&nbsp;' . $user_meta[0]['user_adress_city'] . '</td>
	</tr>
	<tr>
		<td class="bold" colspan="4" >&nbsp;</td>
	</tr>
	<tr>
		<td class="bold" colspan="2" >' . __('Date d\'embauche', 'evarisk') . '</td><td colspan="2" >' . (!empty($user_metas) && (!empty($user_metas['digi_hiring_date'][0])) ? mysql2date('d M Y', $user_metas['digi_hiring_date'][0], true) : '' ) . '</td>
	</tr>
	<tr>
		<td class="bold" colspan="2" >' . __('Profession', 'evarisk') . '</td><td colspan="2" >' . $user_meta[0]['user_profession'] . '</td>
	</tr>
	<tr>
		<td class="bold" colspan="2" >' . __('Qualification professionnelle', 'evarisk') . '</td><td colspan="2" >' . $user_meta[0]['user_professional_qualification'] . '</td>
	</tr>
	<tr>
		<td class="bold" colspan="4" >&nbsp;</td>
	</tr>
	<tr>
		<td class="bold" colspan="2" >' . __('Anciennet&eacute; dans le poste', 'evarisk') . '</td><td colspan="2" >' .	EvaDisplayInput::afficherInput('text', 'accident_user_seniority', (($accident != null) ? $accident->victim_seniority : ''), '', null, 'accident_user[accident_user_seniority]', false, false, 10, '', 'date', '') . '</td>
	</tr>
	<tr>
		<td class="bold" colspan="2" >' . __('L\'accident a t\'il fait d\'autres victimes', 'evarisk') . '?</td><td colspan="2" >' . EvaDisplayInput::createComboBox('accident_make_other_victims', 'accident_user[accident_make_other_victims]', $optionYesNoList, (($accident != null) ? $accident->accident_make_other_victim : '')) . '</td>
	</tr>
</table>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#save_accident").attr("disabled", false);
		jQuery("#victim_changer span").removeClass("accident_container_closer");
		jQuery("#victim_changer span").addClass("accident_container_opener");
		jQuery("#victim_selector").hide();
		jQuery("#victim_changer").show();
		jQuery("#work_accident_user_details").show();
	});
</script>';
		}
		else{
			$user_form = '
<div class="no_user_available" >
	' . __('Vous ne pouvez pas d&eacute;clarer d\'accident pour cette personne. Des informations obligatoires sont manquantes', 'evarisk');
			if(current_user_can('edit_users')){
				$user_form .= '<br/>
	<a target="digi_user_edit" href="' . admin_url('user-edit.php?user_id=' . $id_user) . '#digi_user_informations" >' . __('&Eacute;diter l\'utilisateur', 'evarisk') . '</a>';
			}
			$user_form .= '
</div>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#save_accident").attr("disabled", "disabled");
		jQuery("#victim_selector").show();
		jQuery("#work_accident_user_details").show();
		jQuery("#victim_changer span").removeClass("accident_container_opener");
		jQuery("#victim_changer span").addClass("accident_container_closer");
	});
</script>';
		}

		return $user_form;
	}
	/**
	*	Get and output informations about third_party
	*/
	function get_accident_third_party_informations($id_user, $third_party_type, $third_party_infos = null, $accident = null){
		global $optionYesNoList;
		$user_meta = get_user_meta($id_user, 'digirisk_information', false);
		$user_main_info = evaUser::getUserInformation($id_user);
		if(is_array($user_meta[0]) && (count($user_meta[0]) > 0)
				|| ($third_party_infos != null)
			/* && ($user_meta[0]['user_adress'] != '') && ($user_meta[0]['user_adress_2'] != '')
			&& ($user_main_info[$id_user]['user_lastname'] != '') && ($user_main_info[$id_user]['user_firstname'] != '') */){
			if(($third_party_infos == null) || ($accident->declaration_state == 'in_progress')){
				if(isset($user_meta[0]) && is_array($user_meta[0])){
					foreach($user_meta[0] as $field_identifier => $field_content){
						$user_meta[0][$field_identifier] = stripslashes($field_content);
					}
				}
				$user_lastname = $user_main_info[$id_user]['user_lastname'];
				$user_firstname = $user_main_info[$id_user]['user_firstname'];
				$user_adress = $user_meta[0]['user_adress'];
				$user_adress_2 = $user_meta[0]['user_adress_2'];
				$user_insurance_ste = $user_meta[0]['user_insurance_ste'];
				$third_party_identifier = '<input type="hidden" name="accident_' . $third_party_type . '[' . $id_user . '][tparty_id]" id="accident_' . $third_party_type . '_' . $id_user . '" value="' . $third_party_infos->id . '" />';
			}
			elseif($third_party_infos != null){
				$user_lastname = $third_party_infos->lastname;
				$user_firstname = $third_party_infos->firstname;
				$user_adress = $third_party_infos->adress_line_1;
				$user_adress_2 = $third_party_infos->adress_line_2;
				$user_insurance_ste = $third_party_infos->insurance_corporation;
				$third_party_identifier = '';
			}
			$user_form = '
<table class="accident_' . $third_party_type . '" summary="accident ' . $third_party_type . ' information" cellspacing="0" cellpadding="0" >
	<tr>
		<td class="bold" >' . __('Nom', 'evarisk') . '</td><td>' . $user_lastname . '</td>
		<td rowspan="' . (($third_party_type == 'witness') ? 4 : 3) . '" ><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'delete_vs.png" alt="' . __('Supprimez ce t&eacute;moin', 'evarisk') . '" id="delete_selected_' . $third_party_type . '_' . $id_user . '" class="delete_selected_ alignright" onclick="javascript:remove_current_line(\'' . $third_party_type . '\', ' . $id_user . ');" />
			<input type="hidden" name="accident_' . $third_party_type . '[' . $id_user . '][user_id]" id="accident_' . $third_party_type . '_' . $id_user . '" value="' . $id_user . '" />
			' . $third_party_identifier . '</td>
	</tr>
	<tr>
		<td class="bold" >' . __('Pr&eacute;nom', 'evarisk') . '</td><td>' . $user_firstname . '</td>
	</tr>';
			if($third_party_type == 'witness'){
				$user_form .= '
	<tr>
		<td class="bold" >' . __('Adresse', 'evarisk') . '</td><td >' . $user_adress . '</td>
	</tr>
	<tr>
		<td class="bold" >&nbsp;</td>
		<td >' . $user_adress_2 . '</td>
	</tr>';
			}
			elseif($third_party_type == 'third_party'){
				$user_form .= '
	<tr>
		<td class="bold" >' . __('Soci&eacute;t&eacute; d\'assurance', 'evarisk') . '</td><td >' . $user_insurance_ste . '</td>
	</tr>
	<tr>
		<td class="bold" >&nbsp;</td>
	</tr>';
			}
			$user_form .= '
</table>';
		}
		else{
			$user_form = '
<div class="no_user_available" id="accident_' . $third_party_type . '_' . $id_user . '" >';
			if($third_party_type == 'witness'){
				$user_form .= '
	' . __('Vous ne pouvez pas d&eacute;clarer cette personne comme t&eacute;moin. Des informations obligatoires sont manquantes', 'evarisk');
			}
			else{
				$user_form .= '
	' . __('Vous ne pouvez pas d&eacute;clarer cette personne comme tiers. Des informations obligatoires sont manquantes', 'evarisk');
			}
			if(current_user_can('edit_users')){
				$user_form .= '
	<br/>
	<a target="digi_user_edit" href="' . admin_url('user-edit.php?user_id=' . $id_user) . '#digi_user_informations" >' . __('&Eacute;diter l\'utilisateur', 'evarisk') . '</a>';
			}
			$user_form .= '
	<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'delete_vs.png" alt="' . __('Supprimez ce t&eacute;moin', 'evarisk') . '" id="delete_selected_' . $third_party_type . '_' . $id_user . '" class="delete_selected_' . $third_party_type . ' alignright" onclick="javascript:remove_current_line(\'' . $third_party_type . '\', ' . $id_user . ');" />
</div>';
		}

		return $user_form;
	}

	/**
	*	Return an list of element
	*
	*	@param integer $element_id Optionnal If given get informations about this element
	*	@param string $element_status Optionnal If given specify the status of the element to get
	*	@param string $whatToSearch Optionnal The field to look at for the condition
	*
	*	@return object $element_list Could be a list or a single element depending on the different parameters
	*/
	function getElement($element_id = '', $element_status = "'valid', 'moderated'", $whatToSearch = 'id'){
		global $wpdb;

		$more_query = "";
		if($element_id > 0){
			$more_query = " AND ACC." . $whatToSearch . " = '" . $element_id . "' ";
		}

		$query = $wpdb->prepare(
			"SELECT ACC.*,
				ACC_LOCATION_EMPLOYER.id as employer_location_id, ACC_LOCATION_EMPLOYER.id_location as employer_id, ACC_LOCATION_EMPLOYER.adress_city as employer_city, ACC_LOCATION_EMPLOYER.adress_postal_code as employer_postal_code, ACC_LOCATION_EMPLOYER.adress_line_1 as employer_adress_1, ACC_LOCATION_EMPLOYER.adress_line_2 as employer_adress_2,
				ACC_LOCATION_ESTABLISHMENT.id as establishment_location_id, ACC_LOCATION_ESTABLISHMENT.id_location as establishment_id, ACC_LOCATION_ESTABLISHMENT.adress_city as establishment_city, ACC_LOCATION_ESTABLISHMENT.adress_postal_code as establishment_postal_code, ACC_LOCATION_ESTABLISHMENT.adress_line_1 as establishment_adress_1, ACC_LOCATION_ESTABLISHMENT.adress_line_2 as establishment_adress_2,
				ACC_VICTIM.id as accident_victim_id, ACC_VICTIM.id_user as victim_id, ACC_VICTIM.victim_seniority, ACC_VICTIM.victim_meta,
				ACC_DETAILS.id as accident_details_id, ACC_DETAILS.accident_victim_transported_at, ACC_DETAILS.accident_place, ACC_DETAILS.accident_consequence, ACC_DETAILS.accident_victim_work_shedule, ACC_DETAILS.accident_details, ACC_DETAILS.accident_declaration, ACC_DETAILS.accident_hurt_place, ACC_DETAILS.accident_hurt_nature
			FROM " . self::dbTable . " AS ACC
				LEFT JOIN " . DIGI_DBT_ACCIDENT_DETAILS . " AS ACC_DETAILS ON ((ACC_DETAILS.id_accident = ACC.id) && (ACC_DETAILS.status = 'valid'))
				LEFT JOIN " . DIGI_DBT_ACCIDENT_LOCATION . " AS ACC_LOCATION_EMPLOYER ON ((ACC_LOCATION_EMPLOYER.id_accident = ACC.id) && (ACC_LOCATION_EMPLOYER.status = 'valid') && (ACC_LOCATION_EMPLOYER.location_type = 'employer'))
				LEFT JOIN " . DIGI_DBT_ACCIDENT_LOCATION . " AS ACC_LOCATION_ESTABLISHMENT ON ((ACC_LOCATION_ESTABLISHMENT.id_accident = ACC.id) && (ACC_LOCATION_ESTABLISHMENT.status = 'valid') && (ACC_LOCATION_ESTABLISHMENT.location_type = 'establishment'))
				LEFT JOIN " . DIGI_DBT_ACCIDENT_VICTIM . " AS ACC_VICTIM ON ((ACC_VICTIM.id_accident = ACC.id) && (ACC_VICTIM.status = 'valid'))
			WHERE ACC.status IN (" . $element_status . ")
				" . $more_query . "
			GROUP BY ACC.id", "");
				// LEFT JOIN " . DIGI_DBT_ACCIDENT_THIRD_PARTY . " AS ACC_TPARTY ON ((ACC_TPARTY.id_accident = ACC.id) && (ACC_TPARTY.status = 'valid'))
		/*	Get the query result regarding on the function parameters. If there must be only one result or a collection	*/
		if($element_id == '')
		{
			$element_list = $wpdb->get_results($query);
		}
		else
		{
			$element_list = $wpdb->get_row($query);
		}

		return $element_list;
	}

}