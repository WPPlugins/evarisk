<?php

	//Postbox definition
	$postBoxTitle = __('Informations g&eacute;n&eacute;rales', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
	$postBoxId = 'postBoxRecommandationCategoryEdition';
	add_meta_box($postBoxId, $postBoxTitle, array('evaRecommandationCategory', 'recommandation_category_form'), PAGE_HOOK_EVARISK_CATEGORIE_PRECONISATION, 'rightSide', 'default');