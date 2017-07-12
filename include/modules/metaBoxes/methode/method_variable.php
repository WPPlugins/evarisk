<?php

	//Postbox definition
	$postBoxTitle = __('Variables de la m&eacute;thode', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
	$postBoxId = 'postBoxMethodeEvaluationVariable';
	add_meta_box($postBoxId, $postBoxTitle, array('MethodeEvaluation', 'evaluation_method_variable_manager'), PAGE_HOOK_EVARISK_EVALUATION_METHODE, 'rightSide', 'default');