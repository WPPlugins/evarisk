<?php
/*
 * @version v5.0
 */
 
	define('DOING_AJAX', true);
	define('WP_ADMIN', true);
	require_once('../../../../../../wp-load.php');
	require_once(ABSPATH . 'wp-admin/includes/admin.php');
	require_once('../../../evarisk.php');
	require_once(EVA_INC_PLUGIN_DIR . 'includes.php' );

	require_once(EVA_LIB_PLUGIN_DIR . 'upload.php');
	require_once(EVA_LIB_PLUGIN_DIR . 'gestionDocumentaire/gestionDoc.class.php');

	$result = handleUpload();
	$table = digirisk_tools::IsValid_Variable($_GET['table']);
	$tableElement = digirisk_tools::IsValid_Variable($result['tableElement']);
	$idElement = digirisk_tools::IsValid_Variable($result['idElement']);
	$fichier = digirisk_tools::IsValid_Variable($result['fichier']);
	
	$uploadStatus = eva_gestionDoc::saveNewDoc($table, $tableElement, $idElement, $fichier);
	if($uploadStatus == 'error')
	{
		$result[success] = false;
	}

	echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
