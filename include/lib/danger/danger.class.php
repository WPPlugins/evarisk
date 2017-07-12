<?php
/**
* Danger manager
* 
* Define the different method to manage danger
* @author Evarisk <dev@evarisk.com>
* @version 5.0.1
* @package digirisk
* @subpackage librairies
*/

/**
* Define the different method to manage danger
*
* @package digirisk
* @subpackage librairies
*/
class digirisk_danger
{

	/**
	*	Define the different parameters for the evaluation risk page
	*/
	function danger_main_page(){
		$danger_page_parameters = array();

		/*	Page parameters	*/
		$danger_page_parameters['page_title'] = __("Dangers",'evarisk');
		$danger_page_parameters['page_icon'] = EVA_DANGER_ICON;
		$danger_page_parameters['page_icon_title'] = 'Danger Category Icon';
		$danger_page_parameters['page_icon_alt'] = 'Danger Category Icon';
		$danger_page_parameters['element_type'] = TABLE_CATEGORIE_DANGER;
		$danger_page_parameters['has_add_button'] = false;
		$danger_page_parameters['message'] = '';
		$danger_page_parameters['display_choice'] = true;
		$danger_page_parameters['show_notes'] = true;

		/*	Tree parameters	*/
		$danger_page_parameters['tree_root'] = Arborescence::getRacine($danger_page_parameters['element_type']);
		$danger_page_parameters['tree_identifier'] = 'main_table_' . $danger_page_parameters['element_type'];
		$danger_page_parameters['tree_root_name'] = __("Cat&eacute;gories", 'evarisk');
		$danger_page_parameters['tree_element_are_draggable'] = true;
		$danger_page_parameters['tree_action_display'] = true;

		return $danger_page_parameters;
	}

	/**
	*	Define the different element to load when user is located on risk evaluation part for an element
	*
	*	@param integer $idElement The element identifier user want to view details for. If null, don't load element details
	*	@param string $chargement Define if all boxes have to be loaded, or only some
	*
	*/
	function includesDangers($idElement, $chargement = 'tout'){
		if($chargement == 'tout'){
			require_once(EVA_METABOXES_PLUGIN_DIR . 'dangers/categorieDangers/categorieDangers-new.php');
			require_once(EVA_METABOXES_PLUGIN_DIR . 'dangers/danger/danger-new.php');
			if(((int)$idElement) != 0){
				require_once(EVA_METABOXES_PLUGIN_DIR . 'galeriePhotos/galeriePhotos.php');
				if(file_exists(EVA_TEMPLATES_PLUGIN_DIR . 'includesDangersPerso.php')){
					include_once(EVA_TEMPLATES_PLUGIN_DIR . 'includesDangersPerso.php');
				}
			}
		}
	}
}