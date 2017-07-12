<?php
/*
 * @version v5.0
 */

//Postbox definition
$postBoxTitle = __('Informations G&eacute;n&eacute;rales', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
$postBoxId = 'postBoxGeneralInformation';
add_meta_box($postBoxId, $postBoxTitle, array('EvaActivity', 'sub_task_creation_form'), PAGE_HOOK_EVARISK_ACTIVITE, 'rightSide', 'high');

?>