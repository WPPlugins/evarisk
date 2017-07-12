<?php

define('DOING_AJAX', true);
define('WP_ADMIN', true);
require_once('../../../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/admin.php');
require_once('../../evarisk.php');
require_once(EVA_INC_PLUGIN_DIR . 'includes.php' );

@header('Content-Type: text/html; charset=' . get_option('blog_charset'));

$q = strtolower($_GET["term"]);
if (!$q) return;

$items = array();

$elementList = digirisk_groups::getElement('', "'valid'", strtolower($_GET["group_type"]));
if(is_array($elementList) && (count($elementList) > 0)){
	foreach($elementList as $element){
		$items[ELEMENT_IDENTIFIER_GPU . $element->id . ' - ' . $element->name] = $element->id;
	}
}

$output_search = '';
$found_result = false;
if(!empty($items)){
	$output_search = '[';
	foreach ($items as $key => $value){
		if (strpos(strtolower($key), $q) !== false){
			$found_result = true;

			$output_search .= '{"id": "' . $value . '", "label": "' . $key . '", "value": "' . $value . '"}, ';
		}
	}
	$output_search = substr($output_search, 0, -2) . ']';
}

echo $output_search;

?>