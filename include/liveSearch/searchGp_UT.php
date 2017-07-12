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

if(($search_in_element == 'all') || in_array(TABLE_UNITE_TRAVAIL, $search_in_element)){
	$more_unit_query = "";
	if(($table_element == TABLE_UNITE_TRAVAIL) && ($id_element > 0)){
		$more_unit_query = " AND UT.id != '" . $id_element . "'";
	}
	$query = $wpdb->prepare("
	SELECT CONCAT('" . TABLE_UNITE_TRAVAIL . "-_-', UT.id) AS id, CONCAT('" . ELEMENT_IDENTIFIER_UT . "', UT.id, ' - ', UT.nom) AS name
	FROM " . TABLE_UNITE_TRAVAIL . " AS UT
	WHERE UT.Status = 'valid'" . $more_unit_query . "
	ORDER BY UT.nom", "");
	$unit_list = $wpdb->get_results($query);
	foreach($unit_list as $unit){
		$items[$unit->name] = $unit->id;
	}
}

if(($search_in_element == 'all') || in_array(TABLE_GROUPEMENT, $search_in_element)){
	$more_grpt_query = "";
	if(($table_element == TABLE_GROUPEMENT) && ($id_element > 0)){
		$more_grpt_query = " AND GP.id != '" . $id_element . "'";
	}
	$query = $wpdb->prepare("
	SELECT CONCAT('" . TABLE_GROUPEMENT . "-_-', GP.id) AS id, CONCAT('" . ELEMENT_IDENTIFIER_GP . "', GP.id, ' - ', GP.nom) AS name
	FROM " . TABLE_GROUPEMENT . " AS GP
	WHERE GP.Status = 'valid'
		AND nom != 'Groupement Racine'" . $more_grpt_query . "
	ORDER BY GP.nom", "");
	$groupement_list = $wpdb->get_results($query);
	foreach($groupement_list as $gpt){
		$items[$gpt->name] = $gpt->id;
	}
}

if(($search_in_element == 'all') || in_array(TABLE_RISQUE, $search_in_element)){
	$more_grpt_query = "";
	if(($table_element == TABLE_RISQUE) && ($id_element > 0)){
		$more_grpt_query = " AND R.id != '" . $id_element . "'";
	}
	$query = $wpdb->prepare("
	SELECT CONCAT('" . TABLE_RISQUE . "-_-', R.id) AS id, CONCAT('" . ELEMENT_IDENTIFIER_R . "', R.id, ' - ', D.nom) AS name
	FROM " . TABLE_RISQUE . " AS R
		INNER JOIN " . TABLE_DANGER . " AS D ON ((D.id = R.id_danger) AND (D.Status = 'Valid'))
	WHERE R.Status = 'valid'" . $more_grpt_query . "
	ORDER BY D.nom", "");
	$risk_list = $wpdb->get_results($query);
	foreach($risk_list as $risk){
		$items[$risk->name] = $risk->id;
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