<?php
	//Postbox definition
	$postBoxTitle = __('Actions correctives', 'evarisk');
  $postBoxId = 'mainPostBox';
  require_once(EVA_METABOXES_PLUGIN_DIR . 'mainPostBox.php');
  $postBoxCallbackFunction = 'getMainPostBoxBody';
  add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_TACHE, 'leftSide', 'default');
  add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_ACTIVITE, 'leftSide', 'default');
?>