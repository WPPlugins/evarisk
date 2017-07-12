<?php
/**
* Product categories management
* 
*	Define the different tools to access product categories available from WP Shop plugin
* @author Evarisk <dev@evarisk.com>
* @version 5.1.3.9
* @package Digirisk
* @subpackage librairies
*/

/**
*	Define the different tools to access product categories available from WP Shop plugin
* @package Digirisk
* @subpackage librairies
*/
class digirisk_product_categories
{

	/**
	*	
	*
	*	@param 
	*
	*	@return array $categories An array with the list of categories we have to output
	*/
	function get_selected_categories($output = 'list', $categoryToDisplay = ''){
		$categories = array();

		if($output == 'list'){
			$categories[] = __('Toutes', 'evarisk');
		}

		/*	Get the list of categories to output. This list is defined by the options set by the administrator	*/
		$digiriskSelectedCategories = unserialize(digirisk_options::getOptionValue('product_categories', 'digirisk_product_options'));
		if(is_array($digiriskSelectedCategories)){
			/*	Read the category list for getting informations about it	*/
			foreach($digiriskSelectedCategories as $digiriskCategories){
				if($output == 'list'){
					$category = get_term($digiriskCategories, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
					$categories[$digiriskCategories] = $category->name;
				}
				else{
					if((($categoryToDisplay != '') && ($categoryToDisplay == $digiriskCategories)) || ($categoryToDisplay == '') || ($categoryToDisplay == '0')){
						$category = get_term($digiriskCategories, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
						$categories[$digiriskCategories]['name'] = $category->name;
						$categories[$digiriskCategories]['slug'] = $category->slug;
					}
				}
			}
		}
		else{
			$categories_list = get_terms(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, array('hide_empty' => 0));
			foreach($categories_list as $category){
				if($output == 'list'){
					$categories[$category->term_id] = $category->name;
				}
				else{
					if((($categoryToDisplay != '') && ($categoryToDisplay == $category->term_id)) || ($categoryToDisplay == '') || ($categoryToDisplay == '0')){
						$categories[$category->term_id]['name'] = $category->name;
						$categories[$category->term_id]['slug'] = $category->slug;
					}
				}
			}
		}

		return $categories;
	}

	/**
	*	Get the sub categories of a given category
	*
	*	@param integer $parent_category The main category we want to have the sub categories for
	*/
	function options_category_tree_output($category_id = 0){
		$category_tree_output = '';

		$categories = get_terms(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, array('hide_empty' => '0', 'parent' => $category_id));
		if(count($categories) > 0){
			$options = get_option('digirisk_product_options');
			$choosenCategories = !empty($options['product_categories'])?unserialize($options['product_categories']):array();
			foreach($categories as $category){
				$checked = (is_array($choosenCategories) && in_array($category->term_id, $choosenCategories)) ? ' checked="checked" ' : '';
				$category_tree_output .= '
<ul class="digirisk_options_categories_list" >
	<li><input ' . $checked . ' type="checkbox" name="digirisk_product_options[product_categories][]" value="' . $category->term_id . '" id="wpshop_product_categories_' . $category->term_id . '" /><label for="wpshop_product_categories_' . $category->term_id . '" >' . $category->name . '</label>
		' . digirisk_product_categories::options_category_tree_output($category->term_id) . '
	</li>
</ul>';
			}
		}

		return $category_tree_output;
	}

}