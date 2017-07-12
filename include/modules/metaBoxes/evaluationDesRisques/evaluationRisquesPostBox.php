<?php

//Postbox definition
$postBoxTitle = __('Evaluation des risques', 'evarisk');
$postBoxId = 'mainPostBox';
require_once(EVA_METABOXES_PLUGIN_DIR . 'mainPostBox.php');
$postBoxCallbackFunction = 'getMainPostBoxBody';
add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_UNITES_DE_TRAVAIL, 'leftSide', 'default');
add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_GROUPEMENTS, 'leftSide', 'default');
add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_GROUPEMENTS_GESTION, 'leftSide', 'default');

?>