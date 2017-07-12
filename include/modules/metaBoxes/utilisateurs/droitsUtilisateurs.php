<?php

	//Postbox definition
	$postBoxId = 'postBoxDroitssUtilisateurs';
	$postBoxCallbackFunction = array('digirisk_permission', 'userRightPostBox');
	switch ( $_POST['tableElement'] ) {
		case TABLE_GROUPEMENT:
			$postBoxTitle = __('Droits des utilisateurs du groupement', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
			add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_GROUPEMENTS, 'rightSide', 'default');
		break;
		case TABLE_UNITE_TRAVAIL:
			$postBoxTitle = __('Droits des utilisateurs de l\'unit&eacute;', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
			add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_UNITES_DE_TRAVAIL, 'rightSide', 'default');
		break;

		case TABLE_TACHE:
			$postBoxTitle = __('Droits des utilisateurs de la t&acirc;che', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
			add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_TACHE, 'rightSide', 'default');
		break;

		default:
			$postBoxTitle = __('Droits des utilisateurs', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
		break;
	}
