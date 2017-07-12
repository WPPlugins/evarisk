<?php

//Postbox definition
$postBoxTitle = __('Cat&eacute;gories', 'evarisk');
$postBoxId = 'mainPostBox';
require_once(EVA_METABOXES_PLUGIN_DIR . 'mainPostBox.php');
$postBoxCallbackFunction = 'getMainPostBoxBody';
add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_CATEGORIES_DANGERS, 'leftSide', 'default');
add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_DANGERS, 'leftSide', 'default');

?>