<?php
$postBoxTitle = __('Notes', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
$postBoxId = 'postBoxModificationFollowUp';
$postBoxCallbackFunction = 'getActivityModificationFollowUpPostBoxBody';
add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_TACHE, 'rightSide', 'default');
add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_ACTIVITE, 'rightSide', 'default');
require_once( EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/suivi_activite.class.php');
function getActivityModificationFollowUpPostBoxBody($arguments) {
	$idElement = $arguments['idElement'];
	$tableElement = $arguments['tableElement'];

	echo '<div id="digi_msg_note_' . $tableElement . $idElement . '" style="display:none;" ></div>
			<div id="digi_content_note_' . $tableElement . $idElement . '" >' . suivi_activite::formulaireAjoutSuivi($tableElement, $idElement, true, '', TABLE_ACTIVITE_SUIVI) . '</div>';
}


