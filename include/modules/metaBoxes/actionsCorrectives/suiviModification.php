<?php

	$postBoxTitle = __('Suivi de projet', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
	$postBoxId = 'postBox_project_follow_up';
	$postBoxCallbackFunction = 'digi_postbox_project_follow_up';
	add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_ACTIVITE, 'rightSide', 'high');
	function digi_postbox_project_follow_up($arguments) {
		$idElement = $arguments['idElement'];
		$tableElement = $arguments['tableElement'];

		echo '<div id="project_follow_up_message_' . $tableElement . $idElement . '" class="hide" ></div>
			<div id="project_follow_up_container' . $tableElement . $idElement . '" >' . suivi_activite::formulaire_ajout_suivi_projet($tableElement, $idElement) . '</div>';
	}

	add_meta_box($postBoxId, $postBoxTitle, array('suivi_activite', 'digi_postbox_project'), PAGE_HOOK_EVARISK_TACHE, 'rightSide', 'high');

?>