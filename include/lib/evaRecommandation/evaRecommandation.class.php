<?php
/**
* Recommandation management file
*
* @author Evarisk
* @version v5.0
*/

require_once(EVA_LIB_PLUGIN_DIR . 'evaRecommandation/evaRecommandationCategory.class.php');

/**
* Recommandation management class
*
* @author Evarisk
* @version v5.0
*/
class evaRecommandation
{
	function get_recommandation_id($informations_to_get = array('id'), $conditions = ''){
		global $wpdb;
		$informations = '';

		$query = $wpdb->prepare("SELECT " . implode(', ', $informations_to_get) . " FROM " . TABLE_PRECONISATION . " WHERE 1" . $conditions, "");

		if(count($informations_to_get) == 1){
			$informations = $wpdb->get_var($query);
		}
		else{
			$informations = $wpdb->get_results($query);
		}

		return $informations;
	}

	/**
	*	Define the different element to load when user is located on correctiv action
	*
	*	@param integer $idElement The element identifier user want to view details for. If null, don't load element details
	*	@param string $chargement Define if all boxes have to be loaded, or only some
	*
	*/
	function includes_recommandation_boxes($idElement, $chargement = 'tout'){
		if($chargement == 'tout'){
			require_once(EVA_METABOXES_PLUGIN_DIR . 'recommandation/recommandation/recommandation_edition.php');
			if(((int)$idElement) != 0){
				require_once(EVA_METABOXES_PLUGIN_DIR . 'galeriePhotos/galeriePhotos.php');
			}
		}
	}

	/**
	*	Define the different parameters for the evaluation risk page
	*/
	function recommandation_main_page(){
		$recommandation_page_parameters = array();

		/*	Page parameters	*/
		$recommandation_page_parameters['element_type'] = TABLE_CATEGORIE_PRECONISATION;

		/*	Tree parameters	*/
		$recommandation_page_parameters['tree_identifier'] = 'main_table_' . $recommandation_page_parameters['element_type'];
		$recommandation_page_parameters['tree_root_name'] = __("Cat&eacute;gories", 'evarisk');
		$recommandation_page_parameters['tree_element_are_draggable'] = false;
		$recommandation_page_parameters['tree_action_display'] = true;

		return $recommandation_page_parameters;
	}

/**************************************************************************************************************************************/
/******************************					GETTERS																								*****************************************/
/**************************************************************************************************************************************/

/**
*	Get the complete list of existing recommandation with an extra result for the recommandation add line
*
*	@return object A wordpress database object with the complete result list
*/
	function getRecommandationList(){
		global $wpdb;

		$query = $wpdb->prepare(
			"(SELECT '' as id, '' as status, '' as id_categorie_preconisation, '' as creation_date, 'Add' as nom, '' as description, RECOMMANDATION_CAT.nom AS recommandation_category_name, RECOMMANDATION_CAT.id AS recommandation_category_id, '' as photo
			FROM " . TABLE_CATEGORIE_PRECONISATION . " AS RECOMMANDATION_CAT
			WHERE RECOMMANDATION_CAT.status = 'valid')
			UNION
			(SELECT RECOMMANDATION.*, RECOMMANDATION_CAT.nom AS recommandation_category_name, RECOMMANDATION_CAT.id AS recommandation_category_id, PIC.photo
			FROM " . TABLE_CATEGORIE_PRECONISATION . " AS RECOMMANDATION_CAT
				LEFT JOIN " . TABLE_PRECONISATION . " AS RECOMMANDATION ON ((RECOMMANDATION.id_categorie_preconisation = RECOMMANDATION_CAT.id) AND (RECOMMANDATION.status = 'valid'))
				LEFT JOIN " . TABLE_PHOTO_LIAISON . " AS LINK_ELT_PIC ON ((LINK_ELT_PIC.idElement = RECOMMANDATION.id) AND (LINK_ELT_PIC.tableElement = '" . TABLE_PRECONISATION . "') AND (LINK_ELT_PIC.isMainPicture = 'yes') AND (LINK_ELT_PIC.status = 'valid'))
				LEFT JOIN " . TABLE_PHOTO . " AS PIC ON ((PIC.id = LINK_ELT_PIC.idPhoto) AND (PIC.status = 'valid'))
			WHERE RECOMMANDATION_CAT.status = 'valid' )", "");
		$recommandationList = $wpdb->get_results($query);

		return $recommandationList;
	}

/**
*	Get a specific recommandation
*
*	@param integer $recommandationId The recommandation identifier we want to get
*
*	@return object A wordpress database object with the complete result
*/
	function getRecommandation($recommandationId)
	{
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT *
			FROM " . TABLE_PRECONISATION . "
			WHERE id = %d ", $recommandationId);

		return $wpdb->get_row($query);
	}

	/**
	*	Get the output for the recommandation list for a category
	*
	*	@param integer $recommandationCategoryId The recommandation category identifier we want to get the recommandation for
	*	@param string $outputMode Define the output type we want to get for the recommandation list
	*
	*	@return mixed $recommandationListOutput The complete output
	*/
	function getRecommandationListByCategory($recommandationCategoryId, $outputMode = 'pictos', $selectedRecommandation = '', $arguments = array()) {
		global $wpdb;
		$recommandationListOutput = '';
		$specific_container = !empty($arguments) && !empty($arguments['form_container']) ? $arguments['form_container'] . '_' : '';

		$query = $wpdb->prepare(
			"SELECT RECOMMANDATION.*, PIC.photo
			FROM " . TABLE_PRECONISATION . " AS RECOMMANDATION
				LEFT JOIN " . TABLE_PHOTO_LIAISON . " AS LINK_ELT_PIC ON ((LINK_ELT_PIC.idElement = RECOMMANDATION.id) AND (tableElement = '" . TABLE_PRECONISATION . "') AND (LINK_ELT_PIC.isMainPicture = 'yes') AND (LINK_ELT_PIC.status = 'valid'))
				LEFT JOIN " . TABLE_PHOTO . " AS PIC ON ((PIC.id = LINK_ELT_PIC.idPhoto) AND (PIC.status = 'valid'))
			WHERE RECOMMANDATION.status = 'valid'
				AND RECOMMANDATION.id_categorie_preconisation = %d", $recommandationCategoryId);

		$recommandationList = $wpdb->get_results($query);

		if (count($recommandationList) <= 0) {
			if($outputMode == 'list'){
				return $recommandationList;
			}
			$recommandationListOutput = __('Il n\'y a aucune pr&eacute;conisation pour cette cat&eacute;gorie', 'evarisk');
		}
		else {
			if ($outputMode == 'pictos') {
				$i = 0;
				$selectedId = '';
				foreach ($recommandationList as $recommandation) {
					$recommandationMainPicture = evaPhoto::checkIfPictureIsFile($recommandation->photo, TABLE_PRECONISATION);
					if ( !$recommandationMainPicture ) {
						$recommandationMainPicture = '';

						$recommandationMainPicture = '<img class="recommandationDefaultPictosList" style="width:' . TAILLE_PICTOS . ';" src="' . EVA_RECOMMANDATION_ICON . '" alt="' . sprintf(__('Photo par d&eacute;faut pour %s', 'evarisk'), $recommandation->nom) . '" />';
					}
					else {
						$checked = $selectedClass = '';
						if ( ($selectedRecommandation != '') && ($selectedRecommandation == $recommandation->id) ) {
							$checked =  ' checked="checked" ';
							$selectedClass = 'recommandationSelected';
							$selectedId = 'evarisk("#' . $specific_container . 'recommandation_id").val("' . $recommandation->id . '");
		evarisk("#' . $specific_container . 'recommandationNameIndication").html("<br/>' . ELEMENT_IDENTIFIER_P . $recommandation->id . ' - ' . ucfirst(strtolower($recommandation->nom)) . '");';
						}
						else if (($i == 0) && empty($selectedRecommandation)) {
// 							$checked =  ' checked="checked" ';
// 							$selectedClass = 'recommandationSelected default';
// 							$selectedId = 'evarisk("#' . $specific_container . 'recommandation_id").val("' . $recommandation->id . '");
// 		evarisk("#' . $specific_container . 'recommandationNameIndication").html("<br/>' . ELEMENT_IDENTIFIER_P . $recommandation->id . ' - ' . ucfirst(strtolower($recommandation->nom)) . '");';
						}
						$recommandationMainPicture = '
<div class="alignleft ' . $specific_container . 'recommandationBloc recommandationBloc ' . $selectedClass . '" >
	<input type="hidden" name="preconisation_type" class="preconisation_type" id="' . $specific_container . 'preconisation_type_' . $recommandation->id . '" value="' . $recommandation->preconisation_type . '" />
	<input class="' . $specific_container . 'recommandation" type="' . (!empty($selectedRecommandation) ? 'radio' : 'checkbox') . '" ' . $checked . ' id="' . $specific_container . 'recommandation' . $recommandation->id . '" name="recommandation[]" value="' . $recommandation->id . '" />
	<label for="' . $specific_container . 'recommandation' . $recommandation->id . '" >
		<img class="recommandationDefaultPictosList" src="' . $recommandationMainPicture . '" alt="' . ucfirst(strtolower($recommandation->nom)) . '" title="' . ELEMENT_IDENTIFIER_P . $recommandation->id . ' - ' . ucfirst(strtolower($recommandation->nom)) . '" />
	</label>
</div>';
					}
					$recommandationListOutput .= $recommandationMainPicture;
					$i++;
				}
			}
			else if ( $outputMode == 'selectablelist' ) {
				$recommandationListOutput = EvaDisplayInput::afficherComboBox($recommandationList, 'recommandation', __('Pr&eacute;conisations', 'evarisk'), 'recommandation', "", "");
			}
			else if ( $outputMode == 'list' ) {
				return $recommandationList;
			}
		}

		$recommandationListOutput .= '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		' . $selectedId . '

		jQuery(".' . $specific_container . 'recommandation").click( function(){
			var nb_of_box_checked = 0;
			var recomandation_container = "";
			jQuery(this).closest( "div.recommandationBloc" ).parent( "div" ).find( "div.recommandationBloc" ).find( "input[type=checkbox]:checked" ).each(function(){
				nb_of_box_checked++;
				recomandation_container = jQuery( this ).attr( "class" ).replace( "_recommandation", "_associationFormContainer" );
			});
			if ( "" != recomandation_container ) {
				if ( 1 < nb_of_box_checked ) {
					jQuery(this).closest( "#" + recomandation_container ).children( "#recommandationFormContent" ).children( "textarea" ).val( "" ).hide();
				}
				else {
					jQuery(this).closest( "#" + recomandation_container ).children( "#recommandationFormContent" ).children( "textarea" ).show();
				}
			}';

		if ( !empty($selectedRecommandation) ) {
			$recommandationListOutput .= '
			jQuery(this).closest( "div.recommandationBloc" ).parent( "div" ).find( "div.recommandationBloc" ).each(function(){
				jQuery( this ).removeClass("recommandationSelected");
			});
			jQuery( this ).parent( "div" ).addClass( "recommandationSelected" );
			jQuery( "#' . $specific_container . 'recommandationNameIndication" ).html( "<br>" + jQuery( this ).parent( "div" ).children( "label" ).children( "img" ).attr( "title" ) );
			';
		}
		else {
			$recommandationListOutput .= '
			if ( !jQuery( this ).is( ":checked" ) ) {
				jQuery(this).parent( ".recommandationBloc" ).removeClass("recommandationSelected");
				jQuery("#' . $specific_container . 'recommandationNameIndication").html( jQuery("#' . $specific_container . 'recommandationNameIndication").html().replace( "<br>" + jQuery(this).parent("div").children("label").children("img").attr("title"), "" ) );
			}
			else {
				jQuery(this).parent("div").addClass("recommandationSelected");
				jQuery("#' . $specific_container . 'recommandationNameIndication").append("<br>" + jQuery(this).parent("div").children("label").children("img").attr("title"));
			}';
		}

		$recommandationListOutput .= '
			jQuery("#' . $specific_container . 'recommandation_id").val( jQuery(this).attr("id").replace("' . $specific_container . 'recommandation", "") );
			jQuery("#' . $specific_container . 'preconisation_type").val( jQuery(this).parent("div").children("input[type=hidden].preconisation_type").val() );
		});
	});
</script>';

		return $recommandationListOutput;
	}

/**
*	Get the output for the recommandation list for a given element
*
*	@param string $table_element The element type we want to get the recommandation list for
*	@param integer $id_element The element id we want to get the recommandation list for
*
*	@return mixed $recommandationListOutput The complete output
*/
	function getRecommandationListForElement($table_element, $id_element, $recommandationLinkId = '') {
		global $wpdb;

		$moreQuery = "";
		if ($recommandationLinkId != '') {
			$moreQuery = "
				AND LINK_RECO_ELMT.id = '" . $recommandationLinkId . "' ";
		}

		$query = $wpdb->prepare(
			"SELECT LINK_RECO_ELMT.*, CAT_RECO.nom AS recommandation_category_name, CAT_RECO.id AS recommandation_category_id, CAT_RECO.impressionRecommandationCategorie AS impressionRecommandationCategorie, CAT_RECO.tailleimpressionRecommandationCategorie AS tailleimpressionRecommandationCategorie, CAT_RECO.impressionRecommandation AS impressionRecommandation, CAT_RECO.tailleimpressionRecommandation AS tailleimpressionRecommandation, RECO.nom AS recommandation_name, PIC.photo
			FROM " . TABLE_LIAISON_PRECONISATION_ELEMENT . " AS LINK_RECO_ELMT
				INNER JOIN " . TABLE_PRECONISATION . " AS RECO ON ((RECO.id = LINK_RECO_ELMT.id_preconisation) AND (RECO.status = 'valid'))
					LEFT JOIN " . TABLE_PHOTO_LIAISON . " AS LINK_ELT_PIC ON ((LINK_ELT_PIC.idElement = RECO.id) AND (tableElement = '" . TABLE_PRECONISATION . "') AND (LINK_ELT_PIC.isMainPicture = 'yes') AND (LINK_ELT_PIC.status = 'valid'))
					LEFT JOIN " . TABLE_PHOTO . " AS PIC ON ((PIC.id = LINK_ELT_PIC.idPhoto) AND (PIC.status = 'valid'))
				INNER JOIN " . TABLE_CATEGORIE_PRECONISATION . " AS CAT_RECO ON ((CAT_RECO.id = RECO.id_categorie_preconisation) AND (CAT_RECO.status = 'valid'))
			WHERE LINK_RECO_ELMT.status = 'valid'
				AND LINK_RECO_ELMT.table_element = %s
				AND LINK_RECO_ELMT.id_element = %d" . $moreQuery . "
			ORDER BY recommandation_category_id",
			$table_element, $id_element);;

		return	$wpdb->get_results($query);
	}



/**************************************************************************************************************************************/
/******************************					SETTERS										  *****************************************/
/**************************************************************************************************************************************/

/**
*	Save a new recommandation association in database
*
*	@param array $recommandationsinformations An array with the different information we want to set for the new recommandation association
*
*	@return string $reponseRequete A message that allows to know if the recommandation creation has been done correctly or not
*/
	function saveRecommandationAssociation($recommandationsinformations)
	{
		global $wpdb;

		$create = $wpdb->insert( TABLE_LIAISON_PRECONISATION_ELEMENT, $recommandationsinformations );

		if( $create )
		{
			$reponseRequete = 'done';
		}
		else
		{
			$reponseRequete = 'error';
		}

		return $reponseRequete;
	}

/**
*	Update an existing recommandation association in database
*
*	@param array $recommandationsinformations An array with the different information we want to set for the recommandation association
*
*	@return string $reponseRequete A message that allows to know if the recommandation update has been done correctly or not
*/
	function updateRecommandationAssociation($recommandationsinformations, $id)
	{
		global $wpdb;
		$reponseRequete = '';

		foreach($recommandationsinformations as $field => $value) {
			if ($field != 'id') {
				$recommandation_query_args[ $field ] = $value;
			}
		}
		$recommandation_query = $wpdb->update( TABLE_LIAISON_PRECONISATION_ELEMENT, $recommandation_query_args, array( 'id' => $id, ) );

		if ( false !== $recommandation_query ) {
			$reponseRequete = 'done';
		}
		elseif( $recommandation_query == 0 ){
			$reponseRequete = 'nothingToUpdate';
		}
		else
		{
			$reponseRequete = 'error';
		}

		return $reponseRequete;
	}

	/**
	*	Save a new recommandation in database
	*
	*	@param array $recommandationsinformations An array with the different information we want to set for the new recommandation
	*
	*	@return string $reponseRequete A message that allows to know if the recommandation creation has been done correctly or not
	*/
	function saveRecommandation($recommandationsinformations){
		global $wpdb;

		foreach($recommandationsinformations as $field => $value) {
			if ($field != 'id') {
				$recommandation_query_args[ $field ] = $value;
			}
		}
		$recommandation_query = $wpdb->insert( TABLE_PRECONISATION, $recommandation_query_args );

		if ( false !== $recommandation_query ) {
			$reponseRequete = $wpdb->insert_id;
		}
		else{
			$reponseRequete = 'error';
		}

		return $reponseRequete;
	}

	/**
	*	Update an existing recommandation in database
	*
	*	@param array $recommandationsinformations An array with the different information we want to set for the recommandation
	*
	*	@return string $reponseRequete A message that allows to know if the recommandation update has been done correctly or not
	*/
	function updateRecommandation($recommandationsinformations, $id){
		global $wpdb;
		$reponseRequete = '';

		foreach($recommandationsinformations as $field => $value) {
			if ($field != 'id') {
				$recommandation_query_args[ $field ] = $value;
			}
		}
		$recommandation_query = $wpdb->update( TABLE_PRECONISATION, $recommandation_query_args, array( 'id' => $id, ) );

		if ( false !== $recommandation_query ) {
			$reponseRequete = 'done';
		}
		elseif( $recommandation_query == 0 ){
			$reponseRequete = 'nothingToUpdate';
		}
		else{
			$reponseRequete = 'error';
		}

		return $reponseRequete;
	}



/**************************************************************************************************************************************/
/******************************					OUTPUT										  *****************************************/
/**************************************************************************************************************************************/

	/**
	*	Get the content of the recommandation postbox
	*
	*	@param array $arguments An array with the different element that specify the postbox location and other postbox's information
	*
	*	@return mixed The complete output for the postbox
	*/
	function getRecommandationsPostBoxBody($arguments){
		$idElement = $arguments['idElement'];
		$tableElement = $arguments['tableElement'];
		$arguments['form_container'] = 'single_preco';

		$outputMode = 'pictos';

		echo '
<input type="hidden" id="recommandation_link_action" name="recommandation_link_action" value="add" />
<input type="hidden" id="recommandation_link_id" name="recommandation_link_id" value="" />
<ul class="eva_tabs">
	<li id="ongletAjoutPreconisation" class="tabs selected_tab" ><a href="#postBoxRecommandations" tabindex="1">' . ucfirst(strtolower( __('Ajout d\'une pr&eacute;conisation', 'evarisk'))) . '</a></li>
	<li id="ongletListePreconisation" class="tabs" ><a href="#postBoxRecommandations" tabindex="2">' . ucfirst(strtolower( __('Pr&eacute;conisation affect&eacute;es', 'evarisk'))) . '</a></li>
</ul>
<div id="divAjoutPreconisation" class="eva_tabs_panel digi_recommandation_form_container" >' . evaRecommandation::recommandationAssociation($outputMode, '', $arguments) . '</div>
<div id="divListePreconisation" class="eva_tabs_panel hide digi_recommandation_form_container" >&nbsp;</div>
<script type="text/javascript">
	digirisk(document).ready(function(){
		evarisk("#ongletAjoutPreconisation").click(function(){
			commonTabChange("postBoxRecommandations", "#divAjoutPreconisation", "#ongletListePreconisation");
			evarisk("#recommandation_link_action").val("add");
			evarisk("#recommandation_link_id").val("");
			evarisk("#divAjoutPreconisation").html(evarisk("#loadingImg").html());
			evarisk("#divAjoutPreconisation").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
				"post":"true",
				"table":"' . TABLE_PRECONISATION . '",
				"act":"loadRecomandationLink",
				"table_element":"' . $tableElement . '",
				"id_element":"' . $idElement . '",
				"outputMode":"' . $outputMode . '",
				"recommandation_link_action":evarisk("#recommandation_link_action").val(),
				"recommandation_link_id":evarisk("#recommandation_link_id").val(),
			});
		});
		evarisk("#ongletListePreconisation").click(function(){
			commonTabChange("postBoxRecommandations", "#divListePreconisation", "#ongletAjoutPreconisation");
			evarisk("#recommandation_link_action").val("add");
			evarisk("#recommandation_link_id").val("");
			evarisk("#divListePreconisation").html(evarisk("#loadingImg").html());
			evarisk("#divListePreconisation").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
				"post":"true",
				"table":"' . TABLE_PRECONISATION . '",
				"act":"loadRecomandationForElement",
				"table_element":"' . $tableElement . '",
				"id_element":"' . $idElement . '"
			});
		});
	});
</script>';
	}

	/**
	*	Get the different component for the recommandation adding
	*/
	function recommandationAssociation( $outputMode, $selectedRecommandation = '', $arguments = '' ) {
		$recommandationAssociationOutput = $efficacite_preconisation_script = $efficatiteForm = '';
		$recommandationContainer = '&nbsp;';
		$recommandationContainerClass = 'hide';
		$saveRecommandationAssociationButton = __('Enregistrer', 'evarisk');
		$specific_container = !empty($arguments) && !empty($arguments['form_container']) ? $arguments['form_container'] . '_' : '';

		if (is_array($selectedRecommandation)) {
			$recommandationContainer = evaRecommandation::getRecommandationListByCategory( $selectedRecommandation['id_categorie_preconisation'], $outputMode, $selectedRecommandation['id_preconisation'], $arguments );
			$recommandationContainerClass = '';
			$saveRecommandationAssociationButton = __('Mettre &agrave; jour', 'evarisk') ;
		}

		$selectedRecommandationCategory = (is_array($selectedRecommandation) && (isset($selectedRecommandation['id_categorie_preconisation'])) && ($selectedRecommandation['id_categorie_preconisation'] != '')) ? digirisk_tools::IsValid_Variable($selectedRecommandation['id_categorie_preconisation']) : '';
		$commentaire_preconisation = (is_array($selectedRecommandation) && (isset($selectedRecommandation['commentaire_preconisation'])) && ($selectedRecommandation['commentaire_preconisation'] != '')) ? digirisk_tools::IsValid_Variable($selectedRecommandation['commentaire_preconisation']) : '';
		$efficacite_preconisation = (is_array($selectedRecommandation) && (isset($selectedRecommandation['efficacite_preconisation'])) && ($selectedRecommandation['efficacite_preconisation'] != '')) ? digirisk_tools::IsValid_Variable($selectedRecommandation['efficacite_preconisation']) : '0';
		$preconisation_type = (is_array($selectedRecommandation) && (isset($selectedRecommandation['preconisation_type'])) && ($selectedRecommandation['preconisation_type'] != '')) ? digirisk_tools::IsValid_Variable($selectedRecommandation['preconisation_type']) : '';

		if (digirisk_options::getOptionValue('recommandation_efficiency_activ') == 'oui') {
			$efficatiteForm =
'<label for="' . $specific_container . 'efficacite_preconisation">' . __('Efficacit&eacute; (%)', 'evarisk') . '</label>
<input type="text" class="sliderValue" disabled="disabled" id="' . $specific_container . 'efficacite_preconisation" name="efficacite_preconisation" value="' . $efficacite_preconisation . '" /><div id="' . $specific_container . 'slider-efficacite_preconisation" class="slider_variable"></div>
<div class="clear" >&nbsp;</div>';
			$efficacite_preconisation = 'evarisk("#' . $specific_container . 'efficacite_preconisation").val()';
			$efficacite_preconisation_script = '
		evarisk("#' . $specific_container . 'slider-efficacite_preconisation").slider({
			range:"min",
			value:' . $efficacite_preconisation . ',
			min:0,
			max:100,
			slide:function(event, ui){
				evarisk("#' . $specific_container . 'efficacite_preconisation").val(ui.value);
			}
		});
		evarisk("#' . $specific_container . 'efficacite_preconisation").val(evarisk("#' . $specific_container . 'slider-efficacite_preconisation").slider("value"));';
		}

		$recommandationAssociationOutput = '
<input type="hidden" id="' . $specific_container . 'id_element_recommandation" name="id_element_recommandation" value="' . $arguments['idElement'] . '" />
<input type="hidden" id="' . $specific_container . 'table_element_recommandation" name="table_element_recommandation" value="' . $arguments['tableElement'] . '" />
<div class="hide digi_recommandation_save_message digi_recommandation_save_message_for_' . $specific_container . '" >&nbsp;</div>
<div class="recommandationCategoryExplanation" >' . __('Choisissez une famille de pr&eacute;conisation', 'evarisk') . '</div>
' . evaRecommandationCategory::getCategoryRecommandationListOutput($outputMode, $selectedRecommandationCategory, $arguments) . '
<div class="clear ' . $recommandationContainerClass . '" id="' . $specific_container . 'associationFormContainer" >
	<div id="' . $specific_container . 'recommandationContainer" >' . $recommandationContainer . '</div>
	<div class="clear" >&nbsp;</div>
	<div id="recommandationFormContent" >
		' . $efficatiteForm . '
		<label for="' . $specific_container . 'preconisation_type" >' . __('Type de pr&eacute;vention', 'evarisk') . '</label><br/>
		' . EvaDisplayInput::createComboBox($specific_container . 'preconisation_type', 'preconisation_type', unserialize(DIGI_TYPE_PREVENTION), $preconisation_type) . '<br/>
		<label for="' . $specific_container . 'commentaire_preconisation" >' . __('Commentaire pour', 'evarisk') . '&nbsp;<div class="digi_recommandationNameIndication" id="' . $specific_container . 'recommandationNameIndication" >&nbsp;</div></label>
		<textarea id="' . $specific_container . 'commentaire_preconisation" name="commentaire_preconisation" rows="3" cols="10" class="recommandationInput" >' . $commentaire_preconisation . '</textarea>
		<div class="clear" >&nbsp;</div>';

		if ((current_user_can('digi_edit_unite') || current_user_can('digi_edit_unite_' . $arguments['idElement'])) && empty($arguments['hide_save_button'])) {
			$recommandationAssociationOutput .= '
		<input type="button" class="button-primary alignright" id="' . $specific_container . 'saveRecommandationLink" name="saveRecommandationLink" value="' . $saveRecommandationAssociationButton . '" />';
		}
		else if( !empty($arguments['idElement']) ) {
			$recommandationAssociationOutput .= '
		<button class="button-primary alignright" id="' . $specific_container . 'save_recommandation_for_risk" name="save_recommandation_for_risk" >' . __('Enregistrer la recommandation', 'evarisk') . '</button>';
		}

		$recommandationAssociationOutput .= '
		<input type="hidden" id="' . $specific_container . 'recommandation_id" name="recommandation_id" value="1" />
	</div>
</div>';

		$recommandationAssociationOutput .= '
<script type="text/javascript" >
' . $efficacite_preconisation_script . '
	jQuery(".' . $specific_container . 'recommandationCategory").click(function(){
		jQuery(".recommandationCategoryBloc").each(function(){
			jQuery(this).removeClass("recommandationCategorySelected");
		});
		jQuery(this).parent("div").addClass("recommandationCategorySelected");

		jQuery("#' . $specific_container . 'recommandationContainer").html(evarisk("#loadingImg").html());
		jQuery.post(ajaxurl, {action: "digi_ajax_load_recommandation_from_category", outputMode :"' . $outputMode . '", id_categorie_preconisation: evarisk(this).val(), specific_container: "' . $arguments['form_container'] . '"}, function(response) {
			jQuery("#' . $specific_container . 'recommandationContainer").html( response[0] );
			jQuery("#' . $specific_container . 'associationFormContainer").children( "#recommandationFormContent" ).children( "textarea" ).show();
		}, "json");

		jQuery("#' . $specific_container . 'associationFormContainer").show();
		jQuery("#' . $specific_container . 'commentaire_preconisation").val("");
		jQuery("#' . $specific_container . 'efficacite_preconisation").val("");
		jQuery("#' . $specific_container . 'recommandation_link_action").val("save");
		jQuery("#' . $specific_container . 'recommandation_link_id").val("");
		jQuery("#' . $specific_container . 'recommandationNameIndication").html("&nbsp;");
		jQuery("#' . $specific_container . 'saveRecommandationLink").val("' . __('Enregistrer', 'evarisk') . '");
	});

	jQuery("#' . $specific_container . 'saveRecommandationLink, #' . $specific_container . 'save_recommandation_for_risk").click(function(){
		var id_element = jQuery("#' . $specific_container . 'id_element_recommandation").val();
		var table_element = jQuery("#' . $specific_container . 'table_element_recommandation").val();
		if ( jQuery(this).attr("id") == "' . $specific_container . 'save_recommandation_for_risk" ) {
			var id_element = jQuery("#idRisque").val();
			var table_element = "' . TABLE_RISQUE . '";
		}

		var recommandation_to_save = new Array;
		jQuery( ".' . $specific_container . 'recommandation:checked" ).each(function(){
			recommandation_to_save.push( jQuery(this).val() );
		});
		var data = {
			action: "digi_ajax_save_single_recommandation",
			recommandation_ids: recommandation_to_save,
			recommandation_type: jQuery("#' . $specific_container . 'preconisation_type").val(),
			recommandation_comment: jQuery("#' . $specific_container . 'commentaire_preconisation").val(),
			id_element: id_element,
			table_element: table_element,
			recommandation_efficiency: ' . $efficacite_preconisation . ',
			recommandation_action: jQuery("#recommandation_link_action").val(),
			recommandation_to_update: jQuery("#recommandation_link_id").val(),
			message_container: "." + jQuery(this).closest( ".digi_recommandation_form_container" ).children( ".digi_recommandation_save_message" ).attr( "class" ).replace( "hide digi_recommandation_save_message ", "" ),
		};
		jQuery.post(ajaxurl, data, function(response){

			if ( response[1] ) {
				if ( response[4] == "' . TABLE_RISQUE . '" ) {
					jQuery.post(ajaxurl, {action:"digi_ajax_load_recommandation_form", id_element:response[3] , table_element:response[4]}, function(sub_response){
						jQuery("#digi_risk_eval_" + sub_response[0] + "_" + sub_response[1] + "_reco_container").html( sub_response[2] );
						actionMessageShow( response[0], response[2] );
						setTimeout( function(){
							actionMessageHide( response[0] );
						}, "7000");
					}, "json");
				}
				else {
					jQuery("#ongletAjoutPreconisation").click();
					jQuery("#recommandation_link_action").val("add");
					jQuery("#recommandation_link_id").val("");
					actionMessageShow( response[0], response[2] );
					setTimeout( function(){
						actionMessageHide( response[0] );
					}, "7000");
				}
			}
		}, "json");
		return false;
	});
</script>';

		return $recommandationAssociationOutput;
	}

	/**
	*	Get the output for the recommandation list for a given element
	*
	*	@param string $table_element The element type we want to get the recommandation list for
	*	@param integer $id_element The element id we want to get the recommandation list for
	*
	*	@return mixed $recommandationListOutput The complete output
	*/
	function getRecommandationListForElementOutput($table_element, $id_element, $display_button = true){
		$recommandationList = evaRecommandation::getRecommandationListForElement($table_element, $id_element);
		$outputMode = 'pictos';

		unset($titres, $classes, $idLignes, $lignesDeValeurs);
		$idLignes = null;
		$idTable = 'evaRecommandationList' . $table_element . '-' . $id_element;
		$titres[] = __("Cat&eacute;gorie de la pr&eacute;conisation", 'evarisk');
		$titres[] = '';
		$titres[] = __("Intitul&eacute;", 'evarisk');
		$titres[] = __("Commentaire", 'evarisk');
		if(digirisk_options::getOptionValue('recommandation_efficiency_activ') == 'oui')
		{
			$titres[] = __("Efficacit&eacute;", 'evarisk');
		}
		$titres[] = __("Actions", 'evarisk');
		$classes[] = '';
		$classes[] = 'recommandationIconColumn';
		$classes[] = '';
		$classes[] = '';
		if(digirisk_options::getOptionValue('recommandation_efficiency_activ') == 'oui')
		{
			$classes[] = 'recommandationEfficiencyColumn';
		}
		$classes[] = 'recommandationActionColumn';

		unset($ligneDeValeurs);
		$i=0;
		if ( count($recommandationList) > 0 ) {
			foreach ( $recommandationList as $recommandation ) {
				$idLignes[] = 'recommandationLink-id-' . $recommandation->id;

				$recommandationCategoryMainPicture = '';
				$mainPicture = evaPhoto::getMainPhoto(TABLE_CATEGORIE_PRECONISATION, $recommandation->recommandation_category_id);
				if ( $mainPicture != 'error' ) {
					if ( is_file(EVA_HOME_DIR . $mainPicture) ) {
						$recommandationCategoryMainPicture = '<img class="recommandationCategoryDefaultPictosList" style="width:' . TAILLE_PICTOS . ';" src="' . EVA_HOME_URL . $mainPicture . '" alt="' . sprintf(__('Photo par d&eacute;faut pour %s', 'evarisk'), $recommandation->recommandation_category_name) . '" />';
					}
					else if ( is_file(EVA_GENERATED_DOC_DIR . $mainPicture) ) {
						$recommandationCategoryMainPicture = '<img class="recommandationCategoryDefaultPictosList" style="width:' . TAILLE_PICTOS . ';" src="' . EVA_GENERATED_DOC_URL . $mainPicture . '" alt="' . sprintf(__('Photo par d&eacute;faut pour %s', 'evarisk'), $recommandation->recommandation_category_name) . '" />';
					}
				}
				$lignesDeValeurs[$i][] = array('value' => $recommandationCategoryMainPicture . '&nbsp;&nbsp;' . /* ELEMENT_IDENTIFIER_CP . $recommandation->recommandation_category_id . '&nbsp;-&nbsp;' .  */ucfirst($recommandation->recommandation_category_name), 'class' => '');

				$recommandationMainPicture = evaPhoto::checkIfPictureIsFile($recommandation->photo, TABLE_PRECONISATION);
				$recommandationMainPicture = !$recommandationMainPicture ? '' : '<img class="recommandationDefaultPictosList" style="width:' . TAILLE_PICTOS . ';" src="' . $recommandationMainPicture . '" alt="' . sprintf(__('Photo par d&eacute;faut pour %s', 'evarisk'), $recommandation->recommandation_name) . '" />';

				$lignesDeValeurs[$i][] = array('value' => $recommandationMainPicture . '<br/><span style="font-size: 9px;" >' . ELEMENT_IDENTIFIER_PA . $recommandation->id . '</span>', 'class' => '');
				$lignesDeValeurs[$i][] = array('value' => '<span class="pointer recommandationNameManagement" >' . /* ELEMENT_IDENTIFIER_P . $recommandation->id_preconisation . '&nbsp;-&nbsp;' .  */ucfirst($recommandation->recommandation_name) . '</span>', 'class' => '');
				$lignesDeValeurs[$i][] = array('value' => ucfirst($recommandation->commentaire), 'class' => '');
				if ( digirisk_options::getOptionValue('recommandation_efficiency_activ') == 'oui' ) {
					$lignesDeValeurs[$i][] = array('value' => ucfirst($recommandation->efficacite), 'class' => '');
				}
// 				if ( $display_button && (current_user_can('digi_edit_unite') || current_user_can('digi_edit_unite_' . $arguments['idElement'])) ) {
					$lignesDeValeurs[$i][] = array('value' => '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'delete_vs.png" alt="' . __('Supprimer cette pr&eacute;conisation', 'evarisk') . '" title="' . __('Supprimer cette pr&eacute;conisation', 'evarisk') . '" class="alignright deleteRecommandationLink" /><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'edit_vs.png" alt="' . __('&Eacute;diter cette pr&eacute;conisation', 'evarisk') . '" title="' . __('&Eacute;diter cette pr&eacute;conisation', 'evarisk') . '" class="alignright editRecommandationLink" />', 'class' => '');
// 				}
// 				else {
// 					$lignesDeValeurs[$i][] = array('value' => '&nbsp;', 'class' => '');
// 				}
				$i++;
			}
		}
		else {
			$idLignes[] = 'recommandationEmpty';
			$lignesDeValeurs[$i][] = array('value' => '', 'class' => '');
			$lignesDeValeurs[$i][] = array('value' => '', 'class' => '');
			$lignesDeValeurs[$i][] = array('value' => __('Aucune pr&eacute;conisation n\'a &eacute;t&eacute; affect&eacute;e &agrave; cet &eacute;l&eacute;ment', 'evarisk'), 'class' => '');
			if ( digirisk_options::getOptionValue('recommandation_efficiency_activ') == 'oui' ) {
				$lignesDeValeurs[$i][] = array('value' => '', 'class' => '');
			}
			$lignesDeValeurs[$i][] = array('value' => '', 'class' => '');
			$lignesDeValeurs[$i][] = array('value' => '', 'class' => '');
		}

		$script = '<script type="text/javascript">
			digirisk(document).ready(function(){
				evarisk("#' . $idTable . ' tfoot").remove();
				oTable = evarisk("#' . $idTable . '").dataTable({
					"fnDrawCallback": function ( oSettings ) {
						if ( oSettings.aiDisplay.length == 0 ){
							return;
						}
						var nTrs = evarisk("#' . $idTable . ' tbody tr");
						var iColspan = nTrs[0].getElementsByTagName("td").length;
						var sLastGroup = "";
						var ntrsLength = nTrs.length;
						for(i=0; i < ntrsLength; i++){
							var iDisplayIndex = oSettings._iDisplayStart + i;
							var sGroup = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[0];
							if ( sGroup != sLastGroup ){
								var nGroup = document.createElement( "tr" );
								var nCell = document.createElement( "td" );
								nCell.colSpan = iColspan;
								nCell.className = "group";
								nCell.innerHTML = sGroup;
								nGroup.appendChild( nCell );
								nTrs[i].parentNode.insertBefore( nGroup, nTrs[i] );
								sLastGroup = sGroup;
							}
						}
					},
					"aoColumns": [
						{ "bVisible":    false },
						{ "bSortable": false, },
						null,';
			if(digirisk_options::getOptionValue('recommandation_efficiency_activ') == 'oui') {
				$script .= '
						null,';
			}
			$script .= '
						null,
						{ "bSortable": false, },
					],
					"bPaginate": false,
					"bInfo": false,
					"bLengthChange": false,
					"oLanguage": {
						"sUrl": "' . EVA_INC_PLUGIN_URL . 'js/dataTable/jquery.dataTables.common_translation.txt"
					}
				});

				evarisk(".deleteRecommandationLink").click(function(){
					if(confirm(digi_html_accent_for_js("' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer cet &eacute;l&eacute;ment?', 'evarisk') . '"))){
						evarisk("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
							"post":"true",
							"table":"' . TABLE_PRECONISATION . '",
							"act":"deleteRecommandationLink",
							"table_element":evarisk("#table_element_recommandation").val(),
							"id":evarisk(this).parent("td").parent("tr").attr("id").replace("recommandationLink-id-", "")
						}, function(){
							if ( "' . TABLE_RISQUE . '" == "' . $table_element . '") {
								jQuery.post(ajaxurl, {action:"digi_ajax_load_recommandation_form", id_element:"' . $id_element . '" , table_element:"' . $table_element . '"}, function(sub_response){
									jQuery("#digi_risk_eval_" + sub_response[0] + "_" + sub_response[1] + "_reco_container").html( sub_response[2] );
								}, "json");
							}
						});
					}
				});

				evarisk(".editRecommandationLink").click(function(){
					evarisk("#recommandation_link_action").val( "update" );
					evarisk("#recommandation_link_id").val( evarisk(this).parent("td").parent("tr").attr("id").replace("recommandationLink-id-", "") );
					evarisk("#divAjoutPreconisation").html( "" );
					evarisk(this).closest(".digi_recommandation_form_container").html( evarisk("#loadingImg").html() ).load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
						"post":"true",
						"table":"' . TABLE_PRECONISATION . '",
						"act":"loadRecomandationLink",
						"table_element":"' . $table_element . '",
						"id_element":"' . $id_element . '",
						"outputMode":"' . $outputMode . '",
						"recommandation_link_action":evarisk("#recommandation_link_action").val(),
						"recommandation_link_id":evarisk("#recommandation_link_id").val(),
					});
				});
			});
		</script>';

		return EvaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script);
	}

	/**
	*	Define the form allowing to edit recommandation main information
	*
	*	@param mixed $argument The different parameters send to the function by the box hook
	*
	*	@return string The form html output allowing to edit information about recommandation
	*/
	function recommandation_form($argument) {
		$id_preconisation = $argument['idElement'];

		$nom_preconisation = $description_preconisation = $preconisation_type = '';
		if(($id_preconisation != '') && ($id_preconisation > 0)){
			$recommandationInfos = evaRecommandation::getRecommandation($id_preconisation);
			$id_categorie_preconisation = $recommandationInfos->id_categorie_preconisation;
			$nom_preconisation = html_entity_decode($recommandationInfos->nom, ENT_QUOTES, 'UTF-8');
			$preconisation_type = $recommandationInfos->preconisation_type;
			$description_preconisation = html_entity_decode($recommandationInfos->description, ENT_QUOTES, 'UTF-8');
		}
		else{
			$id_categorie_preconisation = $_REQUEST['idPere'];
		}

?>
<p class="recommandationFormErrorMessage digirisk_hide">&nbsp;</p>
<form action="<?php echo EVA_INC_PLUGIN_URL; ?>ajax.php" id="recommandation_form" method="post" >
	<input type="hidden" name="post" id="post" value="true" />
	<input type="hidden" name="table" id="table" value="<?php _e(TABLE_PRECONISATION); ?>" />
	<input type="hidden" name="act" id="act" value="saveRecommandation" />

	<input type="hidden" name="id_categorie_preconisation" id="id_categorie_preconisation" class="recommandationInput" value="<?php _e($id_categorie_preconisation); ?>" />
	<input type="hidden" name="id_preconisation" id="id_preconisation" class="recommandationInput" value="<?php _e($id_preconisation); ?>" />

	<label for="nom_preconisation" ><?php _e('Nom', 'evarisk'); ?></label><br/>
	<input type="text" name="nom_preconisation" id="nom_preconisation" class="recommandationInput" value="<?php _e($nom_preconisation); ?>" /><br/>
	<label for="preconisation_type" ><?php _e('Type de pr&eacute;vention', 'evarisk'); ?></label><br/>
	<?php echo EvaDisplayInput::createComboBox('preconisation_type', 'preconisation_type', unserialize(DIGI_TYPE_PREVENTION), $preconisation_type); ?><br/>
	<label for="description_preconisation" ><?php _e('Description', 'evarisk'); ?></label>
	<textarea rows="3" cols="10" name="description_preconisation" id="description_preconisation" class="recommandationInput" ><?php _e($description_preconisation); ?></textarea>

	<input type="submit" name="save_recommancation_category" id="save_recommancation_category" class="clear alignright button-primary" value="<?php _e('Enregistrer', 'evarisk'); ?>" />
</form>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#recommandation_form").ajaxForm({
			target: "#ajax-response",
			beforeSubmit: validate_recommandation_form
		});
	});

	function validate_recommandation_form(formData, jqForm, options){
		evarisk("#nom_preconisation").removeClass("ui-state-error");
		for(var i=0; i < formData.length; i++){
			if((formData[i].name == "nom_preconisation") && !formData[i].value){
				checkLength( evarisk("#nom_preconisation"), "", 1, 128, "<?php _e('Le champs nom de la pr&eacute;conisation doit contenir entre !#!minlength!#! et !#!maxlength!#! caract&egrave;res', 'evarisk'); ?>" , evarisk(".recommandationFormErrorMessage"))
				return false;
			}
		}

		return true;
	}
</script>
<?php
	}

}