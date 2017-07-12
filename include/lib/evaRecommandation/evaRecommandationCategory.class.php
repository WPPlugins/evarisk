<?php
/**
 * Recommandation category management file
 *
 * @author Evarisk
 * @version v5.0
 */

/**
 * Recommandation category management class
 *
 * @author Evarisk
 * @version v5.0
 */
class evaRecommandationCategory
{

	/**
	*	Define the different element to load when user is located on correctiv action
	*
	*	@param integer $idElement The element identifier user want to view details for. If null, don't load element details
	*	@param string $chargement Define if all boxes have to be loaded, or only some
	*
	*/
	function includes_recommandation_category_boxes($idElement, $chargement = 'tout'){
		if($chargement == 'tout'){
			require_once(EVA_METABOXES_PLUGIN_DIR . 'recommandation/recommandation_category/category_edition.php');
			if(((int)$idElement) != 0){
				require_once(EVA_METABOXES_PLUGIN_DIR . 'galeriePhotos/galeriePhotos.php');
			}
		}
	}

	/**
	*	Get the existing recommandation category list
	*
	*	@return object $CategoryRecommandationList A wordpresse database object containing the complete result list
	*/
	function getCategoryRecommandationList(){
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT RECOMMANDATION_CAT.*, PIC.photo
			FROM " . TABLE_CATEGORIE_PRECONISATION . " AS RECOMMANDATION_CAT
				LEFT JOIN " . TABLE_PHOTO_LIAISON . " AS LINK_ELT_PIC ON ((LINK_ELT_PIC.idElement = RECOMMANDATION_CAT.id) AND (tableElement = '" . TABLE_CATEGORIE_PRECONISATION . "') AND (LINK_ELT_PIC.isMainPicture = 'yes'))
				LEFT JOIN " . TABLE_PHOTO . " AS PIC ON ((PIC.id = LINK_ELT_PIC.idPhoto))
			WHERE RECOMMANDATION_CAT.status = 'valid'
				GROUP BY RECOMMANDATION_CAT.id", "");

		$CategoryRecommandationList = $wpdb->get_results($query);

		return $CategoryRecommandationList;
	}

/**
*	Get a specific recommandation category
*
*	@param integer $categoryRecommandationId The category identifier we want to get information about
*
*	@return object A wordpresse database object containing the recommandation category informations
*/
	function getCategoryRecommandation($categoryRecommandationId)
	{
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT *
			FROM " . TABLE_CATEGORIE_PRECONISATION . "
			WHERE id = %d ", $categoryRecommandationId);

		return $wpdb->get_row($query);
	}

/**
*	Save a new recommandation category in database
*
*	@param array $categoryRecommandationInformations An array with the different information we want to set for the new recommandation category
*
*	@return string $reponseRequete A message that allows to know if the recommandation category creation has been done correctly or not
*/
	function saveRecommandationCategory($categoryRecommandationInformations) {
		global $wpdb;

		foreach($categoryRecommandationInformations as $field => $value) {
			if ($field != 'id') {
				$category_recommandation_query_args[ $field ] = $value;
			}
		}
		$category_recommandation_query = $wpdb->insert( TABLE_CATEGORIE_PRECONISATION, $category_recommandation_query_args );

		if ( false !== $category_recommandation_query ) {
			$reponseRequete = 'done';
		}
		else {
			$reponseRequete = 'error';
		}

		return $reponseRequete;
	}

/**
*	Update an existing recommandation category in database
*
*	@param array $categoryRecommandationInformations An array with the different information we want to set for the recommandation category
*
*	@return string $reponseRequete A message that allows to know if the recommandation category update has been done correctly or not
*/
	function updateRecommandationCategory($categoryRecommandationInformations, $id)
	{
		global $wpdb;
		$reponseRequete = '';

		foreach($categoryRecommandationInformations as $field => $value) {
			if ($field != 'id') {
				$categoryRecommandation_query_args[ $field ] = $value;
			}
		}
		$category_recommandation_query = $wpdb->update( TABLE_CATEGORIE_PRECONISATION, $categoryRecommandation_query_args, array( 'id' => $id, ) );

		if ( false !== $category_recommandation_query ) {
			$reponseRequete = 'done';
		}
		elseif( $category_recommandation_query == 0 ){
			$reponseRequete = 'nothingToUpdate';
		}
		else {
			$reponseRequete = 'error';
		}

		return $reponseRequete;
	}

	/**
	 *	Get the output for the recommandation categories list for a category
	 *
	 *	@param string $outputMode Define the output type we want to get for the recommandation categories list
	 *
	 *	@return mixed $categoryListOutput The complete output
	 */
	function getCategoryRecommandationListOutput($outputMode = 'pictos', $selectedRecommandationCategory = '', $arguments = array()) {
		$categoryListOutput = '';
		$categoryList = evaRecommandationCategory::getCategoryRecommandationList();
		$specific_container = !empty($arguments) && !empty($arguments['form_container']) ? $arguments['form_container'] . '_' : '';
		if ($outputMode == 'pictos') {
			$i = 0;
			foreach($categoryList as $category) {
				$recommandationMainPicture = evaPhoto::checkIfPictureIsFile($category->photo, TABLE_CATEGORIE_PRECONISATION);
				if (!$recommandationMainPicture) {
					$recommandationMainPicture = '';
				}
				else {
					$checked =  $selectedClass =  '';
					if (($selectedRecommandationCategory != '') && ($selectedRecommandationCategory == $category->id)) {
						$checked =  ' checked="checked" ';
						$selectedClass = 'recommandationCategorySelected';
					}
					$recommandationMainPicture = '
<div class="alignleft recommandationCategoryBloc ' . $selectedClass . '" >
	<label for="' . $specific_container . 'recommandationCategory' . $category->id . '" >
		<img class="recommandationDefaultPictosList" src="' . $recommandationMainPicture . '" alt="' . ucfirst(strtolower($category->nom)) . '" title="' . ELEMENT_IDENTIFIER_P . $category->id . '&nbsp;-&nbsp;' . ucfirst(strtolower($category->nom)) . '" />
	</label>
	<input class="hide ' . $specific_container . 'recommandationCategory" type="radio" ' . $checked . ' id="' . $specific_container . 'recommandationCategory' . $category->id . '" name="recommandationCategory" value="' . $category->id . '" />
</div>';
				}
				$categoryListOutput .= $recommandationMainPicture;
				$i++;
			}
		}
		else if ($outputMode == 'selectablelist') {
			$categoryListOutput = EvaDisplayInput::afficherComboBox($categoryList, 'recommandationCategory', __('Cat&eacute;gorie', 'evarisk'), 'recommandationCategory', "", "");
		}

		return $categoryListOutput;
	}

	function get_recommandation_category_id($informations_to_get = array('id'), $conditions = ''){
		global $wpdb;
		$informations = '';

		$query = $wpdb->prepare("SELECT " . implode(', ', $informations_to_get) . " FROM " . TABLE_CATEGORIE_PRECONISATION . " WHERE 1" . $conditions, "");

		if(count($informations_to_get) == 1){
			$informations = $wpdb->get_var($query);
		}
		else{
			$informations = $wpdb->get_results($query);
		}

		return $informations;
	}

	/**
	*	Get the form to manage recommandation categories
	*
	*	@return mixed The complete output for the recommandation category management form
	*/
	function recommandation_category_form($argument){
		$id_categorie_preconisation = $argument['idElement'];

		$nom_categorie = $impressionRecommandation = $tailleimpressionRecommandation = $impressionRecommandationCategorie = $tailleimpressionRecommandationCategorie = '';

		if(($id_categorie_preconisation != '') && ($id_categorie_preconisation > 0)){
			$recommandationCategoryInfos = evaRecommandationCategory::getCategoryRecommandation($id_categorie_preconisation);
			$nom_categorie = html_entity_decode($recommandationCategoryInfos->nom, ENT_QUOTES, 'UTF-8');
			$impressionRecommandation = html_entity_decode($recommandationCategoryInfos->impressionRecommandation, ENT_QUOTES, 'UTF-8');
			$tailleimpressionRecommandation = html_entity_decode($recommandationCategoryInfos->tailleimpressionRecommandation, ENT_QUOTES, 'UTF-8');
			$impressionRecommandationCategorie = html_entity_decode($recommandationCategoryInfos->impressionRecommandationCategorie, ENT_QUOTES, 'UTF-8');
			$tailleimpressionRecommandationCategorie = html_entity_decode($recommandationCategoryInfos->tailleimpressionRecommandationCategorie, ENT_QUOTES, 'UTF-8');
		}

?>
<p class="recommandationCategoryFormErrorMessage digirisk_hide" >&nbsp;</p>
<form action="<?php echo EVA_INC_PLUGIN_URL; ?>ajax.php" id="recommandation_category_form" method="post" >
	<input type="hidden" name="post" id="post" value="true" />
	<input type="hidden" name="table" id="table" value="<?php _e(TABLE_CATEGORIE_PRECONISATION); ?>" />
	<input type="hidden" name="act" id="act" value="saveRecommandationCategorie" />
	<input type="hidden" name="id_categorie_preconisation" id="id_categorie_preconisation" value="<?php _e($id_categorie_preconisation); ?>" />

	<input type="hidden" name="impressionRecommandationCategorieValue" id="impressionRecommandationCategorieValue" value="<?php _e($impressionRecommandationCategorie); ?>" />
	<input type="hidden" name="impressionRecommandationValue" id="impressionRecommandationValue" value="<?php _e($impressionRecommandation); ?>" />

<!--	Recommandation category name	-->
	<label for="nom_categorie" ><?php _e('Nom', 'evarisk'); ?></label>
	<input type="text" name="nom_categorie" id="nom_categorie" class="recommandationInput" value="<?php _e($nom_categorie); ?>" />

<!--	Option recommandation category picture in document	-->
	<div class="recommandationCategoryOption" ><?php _e('Param&egrave;tres pour l\'impression de la famille de pr&eacute;conisations', 'evarisk'); ?></div>
	<div class="recommandationCategoryOptionChoice" ><input type="radio" class="impressionRecommandationCategorie" name="impressionRecommandationCategorie" id="impressionRecommandationCategorie_textandpicture" value="textandpicture" <?php (($impressionRecommandationCategorie == '') || ($impressionRecommandationCategorie == 'textandpicture')) ? _e('checked = "checked"') : ''; ?> /><label for="impressionRecommandationCategorie_textandpicture" class="recommandationCategoryOptionLabel" ><?php _e('Nom + image', 'evarisk'); ?></label></div>
	<div class="recommandationCategoryOptionChoice" ><input type="radio" class="impressionRecommandationCategorie" name="impressionRecommandationCategorie" id="impressionRecommandationCategorie_textonly" value="textonly" <?php (($impressionRecommandationCategorie != '') && ($impressionRecommandationCategorie == 'textonly')) ? _e('checked = "checked"') : ''; ?> /><label for="impressionRecommandationCategorie_textonly" class="recommandationCategoryOptionLabel" ><?php _e('Nom uniquement', 'evarisk'); ?></label></div>
	<div class="recommandationCategoryOptionChoice" ><input type="radio" class="impressionRecommandationCategorie" name="impressionRecommandationCategorie" id="impressionRecommandationCategorie_pictureonly" value="pictureonly" <?php (($impressionRecommandationCategorie != '') && ($impressionRecommandationCategorie == 'pictureonly')) ? _e('checked = "checked"') : ''; ?> /><label for="impressionRecommandationCategorie_pictureonly" class="recommandationCategoryOptionLabel" ><?php _e('Image uniquement', 'evarisk'); ?></label></div>
	<div class="recommandationCategoryOptionPicSizeContainer" id="pictureRecommandationCategoryContainer" ><label for="tailleimpressionRecommandationCategorie" class="recommandationCategoryOptionLabel" ><?php _e('Taille de l\'image (en cm)', 'evarisk'); ?></label><input type="text" name="tailleimpressionRecommandationCategorie" id="tailleimpressionRecommandationCategorie" value="<?php _e($tailleimpressionRecommandationCategorie); ?>" /></div>
	<div class="clear" >&nbsp;</div>

<!--	Option recommandation picture in document	-->
	<div class="recommandationCategoryOption" ><?php _e('Param&egrave;tres pour l\'impression des pr&eacute;conisations de cette famille', 'evarisk'); ?></div>
	<div class="recommandationCategoryOptionChoice" ><input type="radio" class="impressionRecommandation" name="impressionRecommandation" id="impressionRecommandation_textandpicture" value="textandpicture" <?php (($impressionRecommandation == '') || ($impressionRecommandation == 'textandpicture')) ? _e('checked = "checked"') : ''; ?> /><label for="impressionRecommandation_textandpicture" class="recommandationCategoryOptionLabel" ><?php _e('Nom + image', 'evarisk'); ?></label></div>
	<div class="recommandationCategoryOptionChoice" ><input type="radio" class="impressionRecommandation" name="impressionRecommandation" id="impressionRecommandation_textonly" value="textonly" <?php (($impressionRecommandation != '') && ($impressionRecommandation == 'textonly')) ? _e('checked = "checked"') : ''; ?> /><label for="impressionRecommandation_textonly" class="recommandationCategoryOptionLabel" ><?php _e('Nom uniquement', 'evarisk'); ?></label></div>
	<div class="recommandationCategoryOptionChoice" ><input type="radio" class="impressionRecommandation" name="impressionRecommandation" id="impressionRecommandation_pictureonly" value="pictureonly" <?php (($impressionRecommandation != '') && ($impressionRecommandation == 'pictureonly')) ? _e('checked = "checked"') : ''; ?> /><label for="impressionRecommandation_pictureonly" class="recommandationCategoryOptionLabel" ><?php _e('Image uniquement', 'evarisk'); ?></label></div>
	<div class="recommandationCategoryOptionPicSizeContainer" id="pictureRecommandationContainer" ><label for="tailleimpressionRecommandation" class="recommandationCategoryOptionLabel" ><?php _e('Taille de l\'image (en cm)', 'evarisk'); ?></label><input type="text" name="tailleimpressionRecommandation" id="tailleimpressionRecommandation" value="<?php _e($tailleimpressionRecommandation); ?>" /></div>

	<input type="submit" name="save_recommancation_category" id="save_recommancation_category" class="clear alignright button-primary" value="<?php _e('Enregistrer', 'evarisk'); ?>" />
</form>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery(".impressionRecommandation").click(function(){
			jQuery("#impressionRecommandationValue").val(jQuery(this).val());
			if(jQuery(this).val() == 'textonly'){
				jQuery("#pictureRecommandationContainer").hide();
			}
			else{
				jQuery("#pictureRecommandationContainer").show();
			}
		});
		jQuery(".impressionRecommandationCategorie").click(function(){
			jQuery("#impressionRecommandationCategorieValue").val(jQuery(this).val());
			if(jQuery(this).val() == 'textonly'){
				jQuery("#pictureRecommandationCategoryContainer").hide();
			}
			else{
				jQuery("#pictureRecommandationCategoryContainer").show();
			}
		});
		jQuery("#recommandation_category_form").ajaxForm({
			target: "#ajax-response",
			beforeSubmit: validate_recommandation_category_form
		});
	});

	function validate_recommandation_category_form(formData, jqForm, options){
		evarisk("#nom_categorie").removeClass("ui-state-error");
		for(var i=0; i < formData.length; i++){
			if((formData[i].name == "nom_categorie") && !formData[i].value){
				checkLength( evarisk("#nom_categorie"), "", 1, 128, "<?php _e('Le champs nom de la famille de pr&eacute;conisation doit contenir entre !#!minlength!#! et !#!maxlength!#! caract&egrave;res', 'evarisk'); ?>" , evarisk(".recommandationCategoryFormErrorMessage"))
				return false;
			}
		}

		return true;
	}
</script>
<?php
	}

}
