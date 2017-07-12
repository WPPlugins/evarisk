<?php
	//Postbox definition
	$postBoxTitle = __('Galerie Photos', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
	$postBoxId = 'postBoxGaleriePhotos';
	$postBoxCallbackFunction = array('EvaPhoto', 'picture_gallery_box');
	// $postBoxCallbackFunction = 'getGaleriePhotosPostBoxBody';
	add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_UNITES_DE_TRAVAIL, 'rightSide', 'default');
	add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_GROUPEMENTS_GESTION, 'rightSide', 'default');
  add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_TACHE, 'rightSide', 'default');
  add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_ACTIVITE, 'rightSide', 'default');

  add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_CATEGORIES_DANGERS, 'rightSide', 'default');
  add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_CATEGORIE_PRECONISATION, 'rightSide', 'default');
  add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_PRECONISATION, 'rightSide', 'default');
  add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_EVALUATION_METHODE, 'rightSide', 'default');
  add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_MENU, 'rightSide', 'default');