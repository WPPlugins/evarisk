<?php
/**
* Product management
*
*	The product are defined into the wpshop plugin. Methods in this file will be used to access to the product definition, to display product and to store some informations of the product into digirisk database
* @author Evarisk <dev@evarisk.com>
* @version 5.1.2.9
* @package Digirisk
* @subpackage librairies
*/

/**
* The product are defined into the wpshop plugin. Methods in this file will be used to access to the product definition, to display product and to store some informations of the product into digirisk database
* @package Digirisk
* @subpackage librairies
*/
class digirisk_product {

	/**
	 *	Build the product post box output
	 *
	 *	@param array $params The parameters added by wordpress for each displayed box
	 *
	 *	@return string The box content with the product to affect and the affected product
	 */
	function getProductPostBox($params) {
		/*	Get the entire element list	*/
		$categories = array();
		/*	Get the list of categories to output. This list is defined by the options set by the administrator	*/
		$categories = digirisk_product_categories::get_selected_categories('list');

		$input_def['type'] = 'select';
		$input_def['possible_value'] = $categories;
		$input_def['value'] = '';
		$input_def['valueToPut'] = 'index';
		$input_def['name'] = 'digi_wpshop_product_category_selector';
		$categoryListingSelector = digirisk_form::check_input_type($input_def);

		echo '
<div style="display:none;" id="messageInfo_' . $params['tableElement'] . '_' . $params['idElement'] . '_affectProduct" ></div>
' . __('Afficher uniquement les produits de la cat&eacute;gorie', 'evarisk') . '&nbsp:&nbsp;' . $categoryListingSelector . '
<div id="productList' . $params['tableElement'] . '" >' . digirisk_product::affectationPostBoxContent($params['tableElement'], $params['idElement']) . '</div>';
	}

	/**
	 *	Create the content of the affectation box
	 *
	 *	@param string $tableElement The element type we want to affect something to
	 *	@param string $idElement The element identifier we want to affect something to
	 *	@param boolean $showButton Allows to specify if the save button must be shown or not
	 *
	 *	@return string $output The html code that contains the box content to output
	 */
	function affectationPostBoxContent($tableElement, $idElement, $showButton = true, $categoryToDisplay = '') {
		$output = '';
		$alreadyLinked = $alreadyLinkedListOutput = '';
		$idBoutonEnregistrer = 'affectation_' . $tableElement;

		/*	Get the list of element already linked	*/
		$linkedElementList = array();

		$linkedElement = digirisk_product::getBindElement($idElement, $tableElement);
		if(is_array($linkedElement ) && (count($linkedElement) > 0)){
			foreach($linkedElement as $element){
				$linkedElementList[$element->id_product] = $element;
				$alreadyLinked .= $element->id_product . ', ';
				$product = get_post($element->id_product);
				$product_meta = get_post_meta($element->id_product, '_wpshop_product_metadata', true);
				$product_meta['product_reference'] = ($product_meta['product_reference'] != '') ? $product_meta['product_reference'] : 'NC';
				if(is_object($product)){
					if($product->post_status != 'trash'){
						$alreadyLinkedListOutput .= '<div class="selectedelementOPContainer" id="affectedElement' . $tableElement . $element->id_product . '" ><span class="selectedelementOP" title="' . __('Cliquez pour supprimer', 'evarisk') . '">' . ELEMENT_IDENTIFIER_PDT . $element->id_product . '&nbsp;-&nbsp;' . $product->post_title . '&nbsp;(' . __('R&eacute;f.', 'evarisk') . $product_meta['product_reference'] . ')</span><span class="ui-icon deleteElementFromList" >&nbsp;</span>&nbsp;<span class="affected_product_action" ><a href="' . get_permalink($element->id_product) . '" target="product_sheet_view" ><img src="' . PICTO_VIEW . '" alt="' . __('Voir la fiche', 'evarisk') . '" title="' . __('Voir la fiche', 'evarisk') . '" class="view_affected_product_sheet" /></a>';
						if(current_user_can('wpshop_edit_product')){
							$alreadyLinkedListOutput .= '<a href="' . admin_url('post.php?post=' . $element->id_product . '&action=edit') . '" ><img src="' . PICTO_EDIT . '" alt="' . __('&Eacute;diter le produit', 'evarisk') . '" title="' . __('&Eacute;diter le produit', 'evarisk') . '" class="edit_affected_product_sheet" /></a>';
						}
						$alreadyLinkedListOutput .= '</span></div>';
					}
					else{
						$alreadyLinkedListOutput .= '<div class="selectedelementOPContainer trashed_product" id="affectedElement' . $tableElement . $element->id_product . '" ><span title="' . __('Ce produit a &eacute;t&eacute; plac&eacute; dans la corbeille', 'evarisk') . '" >' . ELEMENT_IDENTIFIER_PDT . $element->id_product . '&nbsp;-&nbsp;' . $product->post_title . '&nbsp;(' . __('R&eacute;f.', 'evarisk') . $product_meta['product_reference'] . ')</span><span class="ui-icon deleteElementFromList" >&nbsp;</span></div>';
					}
				}
				else{
					$alreadyLinkedListOutput .= '<div class="selectedelementOPContainer trashed_product" id="affectedElement' . $tableElement . $element->id_product . '" ><span title="' . __('Ce produit a &eacute;t&eacute; plac&eacute; dans la corbeille', 'evarisk') . '" >' . ELEMENT_IDENTIFIER_PDT . $element->id_product . '&nbsp;-&nbsp;' . __('Produit supprim&eacute;', 'evarisk') . '&nbsp;(' . __('R&eacute;f.', 'evarisk') . $product_meta['product_reference'] . ')</span><span class="ui-icon deleteElementFromList" >&nbsp;</span></div>';
				}
			}
		}
		else{
			$alreadyLinkedListOutput = '<span id="noElementSelected' . $tableElement . '" class="noElementLinked" >' . __('Aucun produit affect&eacute;', 'evarisk') . '</span>';
		}

		$output = '
<input type="hidden" name="actuallyAffectedList' . $tableElement . '" id="actuallyAffectedList' . $tableElement . '" value="' . $alreadyLinked . '" />
<input type="hidden" name="affectedList' . $tableElement . '" id="affectedList' . $tableElement . '" value="' . $alreadyLinked . '" />

<div class="alignleft affectationCompleteListOutput" >
	<div id="affectedListOutput' . $tableElement . '" class="affectedElementListOutput ui-widget-content clear" >' . $alreadyLinkedListOutput . '</div>
</div>
<div class="alignright" style="width:55%;" >
	<span class="alignright" ><a href="' . admin_url('post-new.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) . '">' . __('Ajouter des produits', 'evarisk') . '</a></span>
	<div class="clear addLinkElementElement" >
		<div class="clear" >
			<span class="searchElementInput ui-icon" >&nbsp;</span>
			<input class="searchElementToAffect" type="text" name="affectedElement' . $tableElement . '" id="affectedElement' . $tableElement . '" placeholder="' . __('Rechercher dans la liste des produits', 'evarisk') . '" />
		</div>
		<div id="completeList' . $tableElement . '" class="completeList clear" >' . digirisk_product::elementListForAffectation($tableElement, $idElement, $categoryToDisplay) . '</div>
	</div>
	<div id="massActionProduct' . $tableElement . '" ><span class="checkAll" >' . __('cochez tout', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll" >' . __('d&eacute;cochez tout', 'evarisk') . '</span></div>
</div>
<div id="elementBlocContainer' . $tableElement . '" class="clear hide" ><div class="selectedelementOPContainer" ><span onclick="javascript:elementDeletion(digirisk(this).closest(\'div\').attr(\'id\'), \'' . $tableElement . '\', \'' . $idBoutonEnregistrer . '\');" class="selectedelementOP" title="' . __('Cliquez pour supprimer', 'evarisk') . '">#ELEMENTNAME#</span><span class="ui-icon deleteElementFromList" >&nbsp;</span>&nbsp;<span class="affected_product_action" ><img src="' . PICTO_VIEW . '" alt="' . __('Voir la fiche', 'evarisk') . '" title="' . __('Voir la fiche', 'evarisk') . '" class="view_affected_product_sheet" onclick="javascript:alert(digirisk(this).closest(\'div\').attr(\'id\'));" />';
if(current_user_can('wpshop_edit_product')){
	$output .= '<a href="' . admin_url('post.php?post=#PRODUCTID#&action=edit') . '" ><img src="' . PICTO_EDIT . '" alt="' . __('&Eacute;diter le produit', 'evarisk') . '" title="' . __('&Eacute;diter le produit', 'evarisk') . '" class="edit_affected_product_sheet" onclick="javascript:digirisk(this).parent(\'a\').attr(\'href\', digirisk(this).parent(\'a\').attr(\'href\').replace(\'#PRODUCTID#\', digirisk(this).closest(\'div\').attr(\'id\').replace(\'affectedElement' . $tableElement . '\', \'\')));" /></a>';
}
	$output .= '</span></div></div>

<script type="text/javascript" >
	digirisk(document).ready(function(){
		/*	Mass action : check / uncheck all	*/
		digirisk("#massActionProduct' . $tableElement . ' .checkAll").unbind("click");
		digirisk("#massActionProduct' . $tableElement . ' .checkAll").click(function(){
			digirisk("#completeList' . $tableElement . ' .buttonActionElementLinkList' . $tableElement . '").each(function(){
				if(digirisk(this).hasClass("elementIsNotLinked")){
					digirisk(this).click();
				}
			});
		});
		digirisk("#massActionProduct' . $tableElement . ' .uncheckAll").unbind("click");
		digirisk("#massActionProduct' . $tableElement . ' .uncheckAll").click(function(){
			digirisk("#completeList' . $tableElement . ' .buttonActionElementLinkList' . $tableElement . '").each(function(){
				if(digirisk(this).hasClass("elementIsLinked")){
					digirisk(this).click();
				}
			});
		});

		/*	Action when click on delete button	*/
		digirisk(".selectedelementOP, .deleteElementFromList").unbind("click");
		digirisk(".selectedelementOP, .deleteElementFromList").click(function(){
			elementDivId = digirisk(this).closest("div").attr("id").replace("affectedElement' . $tableElement . '", "");
			deleteElementIdFiedList(elementDivId, "' . $tableElement . '");
			checkElementListModification("' . $tableElement . '", "' . $idBoutonEnregistrer . '");
		});

		/*	Autocomplete search	*/
		jQuery("#affectedElement' . $tableElement . '").autocomplete({
			source: "' . EVA_INC_PLUGIN_URL . 'liveSearch/searchProducts.php",
			select: function( event, ui ){
				cleanElementIdFiedList(ui.item.value, "' . $tableElement . '");
				addElementIdFieldList(ui.item.label, ui.item.value, "' . $tableElement . '");

				checkElementListModification("' . $tableElement . '", "' . $idBoutonEnregistrer . '");

				setTimeout(function(){
					jQuery("#affectedElement' . $tableElement . '").val("");
					jQuery("#affectedElement' . $tableElement . '").blur();
				}, 2);
			}
		});

		/*	Add the possibility to select only one category to display	*/
		digirisk("#digi_wpshop_product_category_selector").unbind("change");
		digirisk("#digi_wpshop_product_category_selector").change(function(){
			digirisk("#productList' . $tableElement . '").html(digirisk("#loadingImg").html());
			digirisk("#productList' . $tableElement . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
				"post":"true",
				"table":"' . DIGI_DBT_LIAISON_PRODUIT_ELEMENT . '",
				"act":"reloadCategoryChoice",
				"tableElement":"' . $tableElement . '",
				"idElement":"' . $idElement . '",
				"category":digirisk(this).val()
			});
		});
	});
</script>';

		if($showButton){
			switch($tableElement){
				case TABLE_GROUPEMENT:
					if(!current_user_can('digi_edit_groupement') && !current_user_can('digi_edit_groupement_' . $idElement)){
						$showButton = false;
					}
				break;
				case TABLE_UNITE_TRAVAIL:
					if(!current_user_can('digi_edit_unite') && !current_user_can('digi_edit_unite_' . $idElement)){
						$showButton = false;
					}
				break;
			}
		}

		if($showButton){//Bouton Enregistrer
			$scriptEnregistrement = '
<script type="text/javascript">
	digirisk(document).ready(function(){
		checkElementListModification("' . $tableElement . '", "' . $idBoutonEnregistrer . '");
		digirisk("#' . $idBoutonEnregistrer . '").click(function(){
			digirisk("#saveButtonLoading' . $tableElement . '").show();
			digirisk("#saveButtonContainer' . $tableElement . '").hide();
			digirisk("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
				"post": "true",
				"table": "' . DIGI_DBT_LIAISON_PRODUIT_ELEMENT . '",
				"act": "save",
				"element": digirisk("#affectedList' . $tableElement . '").val(),
				"tableElement": "' . $tableElement . '",
				"idElement": "' . $idElement . '"
			});
		});
	});
</script>';

			$output .= '<div class="clear" ><div id="saveButtonLoading' . $tableElement . '" style="display:none;" class="clear alignright" ><img src="' . PICTO_LOADING_ROUND . '" alt="loading in progress" /></div><div id="saveButtonContainer' . $tableElement . '" >' . EvaDisplayInput::afficherInput('button', $idBoutonEnregistrer, __('Enregistrer', 'evarisk'), null, '', 'save', false, true, '', 'button-primary alignright', '', '', $scriptEnregistrement) . '</div></div>';
		}

		return $output;
	}

	/**
	*	Build a html table with the existing element list, in order to show them for affectation
	*
	*	@param string $tableElement The type of element we want to affect something to
	*	@param integer $idElement The element identifier we want to affect something to
	*
	*	@return string $elementList_Table The html code that we have to output for displaying the existing element list
	*/
	function elementListForAffectation($tableElement, $idElement, $categoryToDisplay = ''){
		global $WP_Query;
		$elementList_Table = $script = $tableOptions = '';
		$idBoutonEnregistrer = 'affectation_' . $tableElement;

		$idTable = 'elementList' . $tableElement . $idElement;
		$titres = array('', ucfirst(strtolower(__('Identifiant', 'evarisk'))), ucfirst(strtolower(__('R&eacute;f&eacute;rence', 'evarisk'))), ucfirst(strtolower(__('Nom', 'evarisk'))), ucfirst(strtolower(__('Cat&eacute;gorie', 'evarisk'))));
		$classes = array('addElementButtonDTable','','','','');

		/*	Get the element list already linked	*/
		$linkedElementCheckList = array();
		$linkedElementList = digirisk_product::getBindElement($idElement, $tableElement);
		if(is_array($linkedElementList ) && (count($linkedElementList) > 0)){
			foreach($linkedElementList as $linkedElement){
				$linkedElementCheckList[$linkedElement->id_product] = $linkedElement;
			}
		}

		$categories = array();
		/*	Get the list of categories to output. This list is defined by the options set by the administrator	*/
		$categories = digirisk_product_categories::get_selected_categories('', $categoryToDisplay);

		/*	Read the categories list	*/
		$productListing = array();
		if(is_array($categories) && (count($categories) > 0)){/*	In case that there are categories to output	*/
			/*	Retrieve product list for current configuration	*/
			$productListing = digirisk_product::get_product_list($categories, $categoryToDisplay);

			if(count($productListing) > 0){/*	Read the product listing	*/
				foreach($productListing as $productID => $productInformations){/*	Product line content	*/
					/*	Define informations for each line	*/
					$idLigne = $tableElement . $idElement . '_elementList_' . $productID;
					$idCbLigne = 'cb_' . $idLigne;
					$moreLineClass = 'elementIsNotLinked';
					if(isset($linkedElementCheckList[$productID])){
						$moreLineClass = 'elementIsLinked';
					}
					/*	Set each line content with the builded output	*/
					$valeurs = array();
					$valeurs[] = array('value'=>'<span id="actionButton' . $tableElement . 'ElementLink' . $productID . '" class="buttonActionElementLinkList' . $tableElement . ' ' . $moreLineClass . ' ui-icon pointer" >&nbsp;</span>');
					$valeurs[] = array('class' => 'digirisk_product_id_cell', 'value' => ELEMENT_IDENTIFIER_PDT . $productID);
					$productInformations['reference'] = (isset($productInformations['reference']) && ($productInformations['reference'] != '')) ? $productInformations['reference'] : '&nbsp;NC';
					$valeurs[] = array('class' => 'digirisk_product_ref_cell', 'value' => $productInformations['reference']);
					$valeurs[] = array('class' => 'digirisk_product_name_cell', 'value' => $productInformations['name']);
					$valeurs[] = array('class' => 'digirisk_product_category_cell', 'value' => implode(', ', $productInformations['categories']));
					$lignesDeValeurs[] = $valeurs;
					$idLignes[] = $idLigne;
				}
				$tableOptions = ',
			"aaSorting": [[2,"asc"]]';
			}
			else{/*	In case that no product is available into the selected categories	*/
				$idLigne = $tableElement . $idElement . '_elementList_no_product';
				/*	Overwrite the datatable titles and class	*/
				$titres = array(ucfirst(strtolower(__('Aucun produit disponible', 'evarisk'))));
				$classes = array('');
				/*	Define the content of the table	*/
				$valeurs = array();
				$valeurs[] = array('class' => 'digirisk_no_category_selected', 'value' => sprintf(__('Pour ajouter des produits %s', 'evarisk'), '<a href="' . admin_url('post-new.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) . '" >' . __('cliquez ici', 'evarisk') . '</a>'));
				$lignesDeValeurs[] = $valeurs;
				$idLignes[] = $idLigne;
			}
		}
		else{/*	In case that no categories was selected into options	*/
				$idLigne = $tableElement . $idElement . '_elementList_no_category_and_no_product';
			/*	Overwrite the datatable titles and class	*/
			$titres = array(ucfirst(strtolower(__('Aucune cat&eacute;gorie s&eacute;lectionn&eacute;e.', 'evarisk'))));
			$classes = array('addElementButtonDTable');
			/*	Define the content of the table	*/
			$valeurs = array();
			$valeurs[] = array('class' => 'digirisk_no_category_selected', 'value' => sprintf(__('Pour ajouter des cat&eacute;gories %s', 'evarisk'), '<a href="' . admin_url('options-general.php?page=' . DIGI_URL_SLUG_MAIN_OPTION) . '" >' . __('cliquez ici', 'evarisk') . '</a>'));
			$lignesDeValeurs[] = $valeurs;
			$idLignes[] = $idLigne;
		}

		/*	Add the js option for the table	*/
		$script =
'<script type="text/javascript">
	digirisk(document).ready(function(){
		digirisk("#' . $idTable . '").dataTable({
			"bAutoWidth": false,
			"bInfo": false,
			"bPaginate": false,
			"bFilter": false' .
			$tableOptions. '
		});
		digirisk("#' . $idTable . '").children("tfoot").remove();
		digirisk("#' . $idTable . '_wrapper").removeClass("dataTables_wrapper");

		/*	Add the action when clicking on a button in the list	*/
		digirisk("#completeList' . $tableElement . ' .odd, #completeList' . $tableElement . ' .even").click(function(){
			if(digirisk(this).children("td:first").children("span").hasClass("elementIsNotLinked")){
				var currentId = digirisk(this).attr("id").replace("' . $tableElement . $idElement . '_elementList_", "");
				cleanElementIdFiedList(currentId, "' . $tableElement . '");

				var elementContent = digirisk(this).children("td:nth-child(2)").html() + "&nbsp;-&nbsp;" + digirisk(this).children("td:nth-child(4)").html() + " (' . __('R&eacute;f.', 'evarisk') . '" + digirisk(this).children("td:nth-child(3)").html() + ")";

				addElementIdFieldList(elementContent, currentId, "' . $tableElement . '");
			}
			else{
				deleteElementIdFiedList(digirisk(this).attr("id").replace("' . $tableElement . $idElement . '_elementList_", ""), "' . $tableElement . '");
			}
			checkElementListModification("' . $tableElement . '", "' . $idBoutonEnregistrer . '");
		});
	});
</script>';

		/*	Add the tabe result into the output	*/
		$elementList_Table .= evaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script);

		return $elementList_Table;
	}

	/**
	*	Create a link between an element and a group
	*
	*	@param mixed $tableElement The element type we want to create a link to
	*	@param integer $idElement The element identifier we want to create a link to
	*	@param array $element An group list id to create link with the selected element
	*
	*	@return mixed $messageInfo An html output that contain the result message
	*/
	function setLinkProductElement($tableElement, $idElement, $element, $outputMessage = true){
		global $wpdb;
		global $current_user;
		$elementToTreat = "  ";
		$messageInfoContainerIdExt = '_affectProduct';
		$idBoutonEnregistrer = 'affectation_' . $tableElement;

		/*	Get the element list already linked	*/
		$linkedElementCheckList = array();
		$linkedElementList = digirisk_product::getBindElement($idElement, $tableElement);
		if(is_array($linkedElementList ) && (count($linkedElementList) > 0)){
			foreach($linkedElementList as $linkedElement){
				$linkedElementCheckList[$linkedElement->id_product] = $linkedElement;
			}
		}

		/*	Transform the new element list to affect into an array	*/
		$newElementList = explode(", ", $element);

		/*	Read the product list already linked for checking if they are again into the list or if we have to delete them form the list	*/
		foreach($linkedElementCheckList as $elements){
			if(is_array($newElementList) && !in_array($elements->id_product, $newElementList)){
				$wpdb->update( DIGI_DBT_LIAISON_PRODUIT_ELEMENT, array( 'status' => 'deleted', 'date_desAffectation' => current_time('mysql', 0), 'id_desAttributeur' => $current_user->ID), array( 'id' => $elements->id, ) );

				if(trim($elements->id_product) != ''){
					/*	Save product informations into digirisk database	*/
					digirisk_product::saveProductInformations($elements->id_product);
				}
			}
		}
		if(is_array($newElementList) && (count($newElementList) > 0)){
			foreach($newElementList as $elementId){
				if((trim($elementId) != '') && !array_key_exists($elementId, $linkedElementCheckList)){
					$elementToTreat .= "('', 'valid', '" . current_time('mysql', 0) . "', '" . $current_user->ID . "', '0000-00-00 00:00:00', '', '" . $elementId . "', '" . $idElement . "', '" . $tableElement . "'), ";
				}
				if(trim($elementId) != ''){
					/*	Save product informations into digirisk database	*/
					digirisk_product::saveProductInformations($elementId);
				}
			}
		}

		$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Il n\'a aucune modification a apporter', 'evarisk') . '</strong>');
		$endOfQuery = trim(substr($elementToTreat, 0, -2));
		if($endOfQuery != ""){
			$query = $wpdb->prepare(
				"REPLACE INTO " . DIGI_DBT_LIAISON_PRODUIT_ELEMENT . "
					(id, status ,date_affectation ,id_attributeur ,date_desAffectation ,id_desAttributeur ,id_product ,id_element ,table_element)
				VALUES
					" . $endOfQuery
			);
			if($wpdb->query($query))
			{
				$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Les modifications ont correctement &eacute;t&eacute enregistr&eacute;es', 'evarisk') . '</strong>');
			}
			else
			{
				$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Les modifications n\'ont pas toutes &eacute;t&eacute correctement enregistr&eacute;es', 'evarisk') . '</strong>"');
			}
		}

		if($outputMessage){
			echo
'<script type="text/javascript">
	digirisk(document).ready(function(){
		actionMessageShow("#messageInfo_' . $tableElement . '_' . $idElement . $messageInfoContainerIdExt . '", "' . $message . '");
		setTimeout(\'actionMessageHide("#messageInfo_' . $tableElement . '_' . $idElement . $messageInfoContainerIdExt . '")\',7500);
		digirisk("#saveButtonLoading' . $tableElement . '").hide();
		digirisk("#saveButtonContainer' . $tableElement . '").show();
		digirisk("#actuallyAffectedList' . $tableElement . '").val(digirisk("#affectedList' . $tableElement . '").val());
		checkElementListModification("' . $tableElement . '", "' . $idBoutonEnregistrer . '");
	});
</script>';
		}
	}

	/**
	*	Get the different element binded
	*
	*	@param integer $idElement The identifier of the element (in its table) we want to bind
	*	@param string $tableElement The table of the element we want to bind
	*
	*	@return object $bindedGroups A wordpress database object with the different groups' id that are binded to the given element
	*/
	function getBindElement($idElement, $tableElement)
	{
		global $wpdb;
		$bindedElement = array();

		$idElement = ($idElement);
		$tableElement = ($tableElement);

		$query = $wpdb->prepare(
		"SELECT ELT_LINK.*
		FROM " . DIGI_DBT_LIAISON_PRODUIT_ELEMENT . " AS ELT_LINK
		WHERE ELT_LINK.table_element = '%s'
			AND ELT_LINK.id_element = %d
			AND ELT_LINK.status = 'valid'
		", $tableElement, $idElement);
		$bindedElement = $wpdb->get_results($query);

		return $bindedElement;
	}

	/**
	*	Save some basic informations of the product into digirisk database
	*
	*	@param integer $productId The product identifier we want to save into database
	*/
	function saveProductInformations($productId)
	{
		global $wpdb;

		/*	Get information about existing product in order to check if the product has already been added into database and the last update date of the product to determine if we have to insert it or not	*/
		$query = $wpdb->prepare(
			"SELECT id, product_last_update_date
			FROM " . DIGI_DBT_PRODUIT . "
			WHERE product_id = %d
			ORDER BY id DESC
			LIMIT 1", $productId);
		$digiProductInformations = $wpdb->get_row($query);

		/*	Get informations about the product we want to save into database	*/
		$productInformations = get_post($productId);
		/*	Get the categories tha product is associated to	*/
		$associatedCategories = get_the_terms($productInformations->ID, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);

		/*	Check the action that we have to do on the product	*/
		if(is_object($digiProductInformations))
		{
			/*	Check if the product have to be update or not by checking the last update date of the product into wpshop plugin and into digirisk	*/
			if($digiProductInformations->product_last_update_date != $productInformations->post_modified)
			{
				$action = 'insertproduct';
			}
			else
			{
				$action = 'updateproduct';
			}
		}
		else
		{
			$action = 'insertproduct';
		}

		/*	Save the product information into database	*/
		if($action == 'updateproduct')
		{
			$newProduct = array();
			$newProduct['last_update_date'] = current_time('mysql', 0);
			$saveAction = eva_database::update($newProduct, $digiProductInformations->id, DIGI_DBT_PRODUIT);
		}
		elseif($action == 'insertproduct')
		{
			$newProduct = array();
			$newProduct['id'] = '';
			$newProduct['status'] = 'valid';
			$newProduct['creation_date'] = current_time('mysql', 0);
			$newProduct['product_id'] = $productId;
			/*	Build the category informations	*/
			$newProduct['category_id'] = $newProduct['category_name'] = '  ';
			if(is_array($associatedCategories) && (count($associatedCategories) > 0)){
				foreach($associatedCategories as $category)
				{
					$newProduct['category_id'] .= $category->term_id . ', ';
					$newProduct['category_name'] .= $category->name . ', ';
				}
			}
			$newProduct['category_id'] = substr($newProduct['category_id'], 0, -2);
			$newProduct['category_name'] = substr($newProduct['category_name'], 0, -2);
			$newProduct['product_last_update_date'] = $productInformations->post_modified;
			$newProduct['product_name'] = $productInformations->post_title;
			$newProduct['product_description'] = $productInformations->post_content;
			$product_meta = get_post_meta($productId, '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_metadata', false);
			$newProduct['product_metadata'] = serialize($product_meta[0]);
			$saveAction = eva_database::save($newProduct, DIGI_DBT_PRODUIT);
		}

		/*	Get the product attachment to save into digirisk database	*/
		$attachments = get_posts(array('post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $productId));
		if(is_array($attachments) && (count($attachments) > 0)){
			$attachmentsNumber = 0;
			foreach ($attachments as $attachment){
				/*	Get information about existing product in order to check if the product has already been added into database and the last update date of the product to determine if we have to insert it or not	*/
				$query = $wpdb->prepare(
					"SELECT id, product_attachment_last_update_date
					FROM " . DIGI_DBT_PRODUIT_ATTACHEMENT . "
					WHERE product_attachment_id = %d
					ORDER BY id DESC
					LIMIT 1", $attachment->ID);
				$digi_attachment_informations = $wpdb->get_row($query);

				/*	Check the action that we have to do on the product	*/
				if(is_object($digi_attachment_informations))
				{
					/*	Check if the product have to be update or not by checking the last update date of the product into wpshop plugin and into digirisk	*/
					if($digi_attachment_informations->product_attachment_last_update_date != $attachment->post_modified)
					{
						$action = 'insertattachment';
					}
					else
					{
						$action = 'updateattachment';
					}
				}
				else
				{
					$action = 'insertattachment';
				}

				/*	Save the product information into database	*/
				if($action == 'updateattachment')
				{
					$newProduct = array();
					$newProduct['last_update_date'] = current_time('mysql', 0);
					$saveAction = eva_database::update($newProduct, $digi_attachment_informations->id, DIGI_DBT_PRODUIT_ATTACHEMENT);
				}
				elseif($action == 'insertattachment')
				{
					$newProduct = array();
					$newProduct['id'] = '';
					$newProduct['status'] = 'valid';
					$newProduct['creation_date'] = current_time('mysql', 0);
					$newProduct['product_attachment_id'] = $attachment->ID;
					$newProduct['product_id'] = $productId;
					$newProduct['product_attachment_last_update_date'] = $attachment->post_modified;
					$newProduct['product_attachment_name'] = $attachment->post_name;
					$newProduct['product_attachment_title'] = $attachment->post_title;
					$newProduct['product_attachment_mime_type'] = $attachment->post_mime_type;
					$attachment_meta = wp_get_attachment_metadata($attachment->ID, false);
					$newProduct['product_attachment_metadata'] = serialize($attachment_meta);
					$saveAction = eva_database::save($newProduct, DIGI_DBT_PRODUIT_ATTACHEMENT);
				}
			}
		}
	}

	/**
	*	Return the product list from a given categories list. Could return the list of uncategorized product if option is set to yes
	*
	*	@param array $categories The categories list set in plugin configuration
	*
	*	@return array $productListing The product list associated to selected categories
	*/
	function get_product_list($categories, $categoryToDisplay){
		$productListing=array();

		/*	Build the get_posts parameters	*/
		$get_posts_parameters = array('numberposts' => '-1', 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
		$digiriskSelectedProductStatus = unserialize(digirisk_options::getOptionValue('product_status', 'digirisk_product_options'));
		if(is_array($digiriskSelectedProductStatus) && (count($digiriskSelectedProductStatus) > 0)){
			$get_posts_parameters['post_status'] = implode(', ', $digiriskSelectedProductStatus);
		}

		/*	Read the categories list for product listing building	*/
		foreach($categories as $categoryId => $categoryDefinition){
			$get_posts_parameters[WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES] = $categoryDefinition['slug'];

			$products = get_posts($get_posts_parameters);
			foreach($products as $product){
				$product_meta = get_post_meta($product->ID, '_wpshop_product_metadata', true);
				$productListing[$product->ID]['reference'] = $product_meta['product_reference'];
				$productListing[$product->ID]['name'] = $product->post_title;
				$productListing[$product->ID]['categories'][] = $categoryDefinition['name'];
			}
		}
		/*	Add uncategorized product	*/
		$digiriskUncategorizedProductOutput = digirisk_options::getOptionValue('digi_product_uncategorized_field', 'digirisk_product_options');
		if($digiriskUncategorizedProductOutput == 'oui'){
			$get_posts_parameters_uncategorized = $get_posts_parameters;
			$get_posts_parameters_uncategorized[WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES] = '';
			$products = get_posts($get_posts_parameters_uncategorized);
			foreach($products as $product){
				if(!isset($productListing[$product->ID])){
				$product_meta = get_post_meta($product->ID, '_wpshop_product_metadata', true);
					$productListing[$product->ID]['reference'] = $product_meta['product_reference'];
					$productListing[$product->ID]['name'] = $product->post_title;
					$productListing[$product->ID]['categories'][] = __('Produit non cat&eacute;goris&eacute;', 'evarisk');
				}
			}
		}

		return $productListing;
	}

}