<?php

	//Postbox definition
	$postBoxTitle = __('Informations g&eacute;n&eacute;rales', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
	$postBoxId = 'postBoxMethodeEvaluationEdition';
	add_meta_box($postBoxId, $postBoxTitle, array('MethodeEvaluation', 'evaluation_method_form'), PAGE_HOOK_EVARISK_EVALUATION_METHODE, 'rightSide', 'default');