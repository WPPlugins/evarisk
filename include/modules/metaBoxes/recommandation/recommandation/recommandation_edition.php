<?php

	//Postbox definition
	$postBoxTitle = __('Informations g&eacute;n&eacute;rales', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
	$postBoxId = 'postBoxRecommandationEdition';
	add_meta_box($postBoxId, $postBoxTitle, array('evaRecommandation', 'recommandation_form'), PAGE_HOOK_EVARISK_PRECONISATION, 'rightSide', 'default');