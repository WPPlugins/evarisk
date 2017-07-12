<?php

define('DOING_AJAX', true);
define('WP_ADMIN', true);
require_once('../../../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/admin.php');
require_once('../../evarisk.php');
require_once(EVA_INC_PLUGIN_DIR . 'includes.php' );

@header('Content-Type: text/html; charset=' . get_option('blog_charset'));

$table_element = (isset($_GET['table_element']) && (trim($_GET['table_element']) != '')) ? strtolower(digirisk_tools::IsValid_Variable($_GET['table_element'])) : '';
$id_element = (isset($_GET['id_element']) && (trim($_GET['id_element']) != '')) ? strtolower(digirisk_tools::IsValid_Variable($_GET['id_element'])) : '0';
$search_in_element = (isset($_GET['element_type']) && (trim($_GET['element_type']) != '')) ? explode('-t-', $_GET['element_type']) : 'all';
$q = strtolower($_GET["term"]);
if (!$q) return;

$items = array();


if(($search_in_element == 'all') || in_array(TABLE_ACTIVITE, $search_in_element)){
	$more_query = "";
	if(($table_element == TABLE_ACTIVITE) && ($id_element > 0)){
		$more_query = " AND ST.id != '" . $id_element . "'";
	}
	$query = $wpdb->prepare("
	SELECT ST.id, CONCAT('" . ELEMENT_IDENTIFIER_ST . "', ST.id, ' - ', ST.nom) AS name
	FROM " . TABLE_ACTIVITE . " AS ST
	WHERE ST.Status = 'valid'" . $more_query . "
	ORDER BY ST.nom", "");
	$unit_list = $wpdb->get_results($query);
	foreach($unit_list as $unit){
		$items[$unit->name] = $unit->id;
	}
}

if(($search_in_element == 'all') || in_array(TABLE_TACHE, $search_in_element)){
	$more_query = "";
	if(($table_element == TABLE_TACHE) && ($id_element > 0)){
		$more_query = " AND T.id != '" . $id_element . "'";
	}
	$query = $wpdb->prepare("
	SELECT T.id, CONCAT('" . ELEMENT_IDENTIFIER_T . "', T.id, ' - ', T.nom) AS name
	FROM " . TABLE_TACHE . " AS T
	WHERE T.Status = 'valid'
		AND T.nom != 'Tache Racine'" . $more_query . "
	ORDER BY T.nom", "");
	$groupement_list = $wpdb->get_results($query);
	foreach($groupement_list as $gpt){
		$items[$gpt->name] = $gpt->id;
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