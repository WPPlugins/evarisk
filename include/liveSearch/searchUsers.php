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

$search_type = (isset($_GET['search_type']) && (trim($_GET['search_type']) != '')) ? strtolower(digirisk_tools::IsValid_Variable($_GET['search_type'])) : 'full';
$items = array();

switch($search_type){
	case 'work_accident':
	{
		$table_element = (isset($_GET['table_element']) && (trim($_GET['table_element']) != '')) ? strtolower(digirisk_tools::IsValid_Variable($_GET['table_element'])) : '';
		$id_element = (isset($_GET['id_element']) && (trim($_GET['id_element']) != '')) ? strtolower(digirisk_tools::IsValid_Variable($_GET['id_element'])) : '';
		$all_user = (isset($_GET['all_user']) && (trim($_GET['all_user']) != '')) ? strtolower(digirisk_tools::IsValid_Variable($_GET['all_user'])) : '';

		if($all_user == 'no'){
			$utilisateursLies = evaUserLinkElement::getAffectedUser($table_element, $id_element);
			foreach($utilisateursLies as $utilisateur){
				$currentUser = evaUser::getUserInformation($utilisateur->id_user);
				$listeUtilisateurs[$utilisateur->id_user]['user_id'] = $utilisateur->id_user;
				$listeUtilisateurs[$utilisateur->id_user]['user_lastname'] = $currentUser[$utilisateur->id_user]['user_lastname'];
				$listeUtilisateurs[$utilisateur->id_user]['user_firstname'] = $currentUser[$utilisateur->id_user]['user_firstname'];
			}
		}
		elseif($all_user == 'yes'){
			$listeUtilisateurs = evaUser::getCompleteUserList();
		}
	}
	break;
	default:
	{
		$listeUtilisateurs = evaUser::getCompleteUserList();
	}
	break;
}

if(is_array($listeUtilisateurs) && (count($listeUtilisateurs) > 0)){
	foreach($listeUtilisateurs as $utilisateur){
		$items[ELEMENT_IDENTIFIER_U . $utilisateur['user_id'] . ' - ' . $utilisateur['user_lastname'] . ' ' . $utilisateur['user_firstname']] = $utilisateur['user_id'];
	}
}

$output_search = '';
$found_result = false;
if(!empty($items)){
	$output_search = '';
	foreach ($items as $key=>$value){
		if (strpos(strtolower($key), $q) !== false){
			$found_result = true;

			$output_search .= '{"id": "' . $value . '", "label": "' . $key . '", "value": "' . $value . '"}, ';
		}
	}
	$output_search = '[' . substr($output_search, 0, -2) . ']';
}

echo $output_search;


?>