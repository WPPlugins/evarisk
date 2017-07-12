<?php

define('DOING_AJAX', true);
define('WP_ADMIN', true);
require_once('../../../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/admin.php');
require_once('../../evarisk.php');
require_once(EVA_INC_PLUGIN_DIR . 'includes.php' );

@header('Content-Type: text/html; charset=' . get_option('blog_charset'));

$q = strtolower($_GET["q"]);
if (!$q) return;

$items = array();

$categories = array();
/*	Get the list of categories to output. This list is defined by the options set by the administrator	*/
$categories = digirisk_product_categories::get_selected_categories('', $categoryToDisplay);
$elementList = array();
if(is_array($categories) && (count($categories) > 0)){/*	In case that there are categories to output	*/
	/*	Retrieve product list for current configuration	*/
	$elementList = digirisk_product::get_product_list($categories);
}

if(is_array($elementList) && (count($elementList) > 0)){
	foreach($elementList as $elementId => $element)
	{
		$element['reference'] = (isset($element['reference']) && ($element['reference'] != '')) ? $element['reference'] : ' NC';
		$items[ELEMENT_IDENTIFIER_PDT . $elementId . ' -&nbsp' . $element['name'] . ' (' . __('R&eacute;f.', 'evarisk') . $element['reference'] . ')'] = $elementId;
	}
}

$output_search = '';
$found_result = false;
if(!empty($items)){
	$output_search = '[';
	foreach ($items as $key=>$value){
		if (strpos(strtolower($key), $q) !== false){
			$found_result = true;

			$output_search .= '{"id": "' . $value . '", "label": "' . $key . '", "value": "' . $value . '"}, ';
		}
	}
	$output_search = substr($output_search, 0, -2) . ']';
}

echo $output_search;

?>