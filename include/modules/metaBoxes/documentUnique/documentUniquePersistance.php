<?php
require_once(EVA_CONFIG );
require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/documentUnique/documentUnique.class.php' );
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

if($_POST['act'] == 'saveDocumentUnique')
{
	$tableElement = digirisk_tools::IsValid_Variable($_POST['tableElement']);
	$idElement = digirisk_tools::IsValid_Variable($_POST['idElement']);

	$informationDocumentUnique['dateCreation'] = digirisk_tools::IsValid_Variable($_POST['dateCreation']);
	$informationDocumentUnique['dateDebutAudit'] = digirisk_tools::IsValid_Variable($_POST['dateDebutAudit']);
	$informationDocumentUnique['dateFinAudit'] = digirisk_tools::IsValid_Variable($_POST['dateFinAudit']);
	$informationDocumentUnique['nomEntreprise'] = digirisk_tools::IsValid_Variable($_POST['nomEntreprise']);

	$informationDocumentUnique['telephoneFixe'] = digirisk_tools::IsValid_Variable($_POST['telephoneFixe']);
	$informationDocumentUnique['telephonePortable'] = digirisk_tools::IsValid_Variable($_POST['telephonePortable']);
	$informationDocumentUnique['numeroFax'] = digirisk_tools::IsValid_Variable($_POST['numeroFax']);

	$informationDocumentUnique['emetteur'] = digirisk_tools::IsValid_Variable($_POST['emetteur']);
	$informationDocumentUnique['destinataire'] = digirisk_tools::IsValid_Variable($_POST['destinataire']);
	$informationDocumentUnique['nomDuDocument'] = digirisk_tools::IsValid_Variable($_POST['nomDuDocument']);
	$informationDocumentUnique['methodologie'] = digirisk_tools::IsValid_Variable($_POST['methodologie']);
	$informationDocumentUnique['sources'] = digirisk_tools::IsValid_Variable($_POST['sources']);
	$informationDocumentUnique['alerte'] = digirisk_tools::IsValid_Variable($_POST['alerte']);
	$informationDocumentUnique['localisation'] = digirisk_tools::IsValid_Variable($_POST['localisation']);
	$informationDocumentUnique['id_model'] = ($_POST['id_model']);

	$messageInfo = $moremessageInfo = '';
	$sauvegardeDocumentUnique = eva_documentUnique::saveNewDocumentUnique($tableElement, $idElement, $informationDocumentUnique);
	$messageInfo = '<script type="text/javascript">
			digirisk(document).ready(function(){
				digirisk("#message' . TABLE_DUER . '").addClass("updated");';

	if($sauvegardeDocumentUnique['result'] != 'error')
	{
		$messageToOutput = "<img src='" . EVA_MESSAGE_SUCCESS . "' alt='success' class='messageIcone' />" . __('Le document unique &agrave; bien &eacute;t&eacute; sauvegard&eacute;.', 'evarisk');
		$moremessageInfo = 'digirisk("#subTabSelector").val("DUER");
				digirisk("#ongletHistoriqueDocument").click();';
	}
	else
	{
		$messageToOutput = "<img src='" . EVA_MESSAGE_ERROR . "' alt='error' class='messageIcone' />" . __('Le document unique n\'&agrave; pas pu &ecirc;tre sauvegard&eacute;', 'evarisk');
	}

	$messageInfo = '
		<script type="text/javascript">
			digirisk(document).ready(function(){
				actionMessageShow("#message' . TABLE_DUER . '", "' . $messageToOutput . '");
				setTimeout(\'actionMessageHide("#message' . TABLE_DUER . '")\',5000);
				' . $moremessageInfo . '
			});
		</script>';

	echo $messageInfo;
}