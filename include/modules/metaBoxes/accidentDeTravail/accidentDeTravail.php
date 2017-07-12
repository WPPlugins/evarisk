<?php

	//Postbox definition
	$postBoxTitle = __('Accidents de travail', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
	$postBoxId = 'postBoxAccidents';
	$postBoxCallbackFunction = array('digirisk_accident', 'get_post_box');

	add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_GROUPEMENTS, 'rightSide', 'default');
