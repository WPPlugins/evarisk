<?php
require_once(EVA_CONFIG );
require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/documentUnique/documentUnique.class.php' );
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

if(($_POST['act'] == 'saveFicheGroupement') || ($_POST['act'] == 'saveGroupSheetForGroupement'))
{
	$tableElement = digirisk_tools::IsValid_Variable($_POST['tableElement']);
	$idElement = digirisk_tools::IsValid_Variable($_POST['idElement']);

	$sheet_infos = array();
	$sheet_infos['sheet_type'] = 'digi_groupement';

	$sheet_infos['nomDuDocument'] = digirisk_tools::IsValid_Variable($_POST['nomDuDocument']);
	$sheet_infos['nomEntreprise'] = digirisk_tools::IsValid_Variable($_POST['nomEntreprise']);
	$sheet_infos['dateCreation'] = date('Ymd');
	$sheet_infos['id_model'] = digirisk_tools::IsValid_Variable($_POST['id_model']);

	$sheet_infos['description'] = (isset($_POST['description']) && ($_POST['description'] != '') && ($_POST['description'] != NULL)) ? digirisk_tools::IsValid_Variable($_POST['description']) : __('NC', 'evarisk');
	$sheet_infos['adresse'] = (isset($_POST['adresse']) && ($_POST['adresse'] != '') && ($_POST['adresse'] != NULL)) ? digirisk_tools::IsValid_Variable($_POST['adresse']) : __('NC', 'evarisk');
	$sheet_infos['telephone'] = (isset($_POST['telephone']) && ($_POST['telephone'] != '') && ($_POST['telephone'] != NULL)) ? digirisk_tools::IsValid_Variable($_POST['telephone']) : __('NC', 'evarisk');

	$sheet_infos['recursiv_mode'] = !empty($_POST['recursiv_mode']) ? $_POST['recursiv_mode'] : false;
	$sheet_infos['document_type'] = 'fiche_de_groupement';

	$messageInfo = $moremessageInfo = '';
	$sauvegardeFicheDePoste = eva_gestionDoc::save_element_sheet($tableElement, $idElement, $sheet_infos);

	if ($_POST['act'] != 'saveGroupSheetForGroupement') {
		if ($sauvegardeFicheDePoste['result'] != 'error') {
			$messageToOutput = "<img src='" . EVA_MESSAGE_SUCCESS . "' alt='success' class='messageIcone' />" . __('La fiche du groupement &agrave; bien &eacute;t&eacute; sauvegard&eacute;e.', 'evarisk');
			$moremessageInfo = 'digirisk("#subTabSelector").val("FGP");
				digirisk("#ongletHistoriqueDocument").click();';
		}
		else {
			$messageToOutput = "<img src='" . EVA_MESSAGE_ERROR . "' alt='error' class='messageIcone' />" . __('La fiche du groupement n\'a pas pu &ecirc;tre sauvegard&eacute;e', 'evarisk');
		}

		$messageInfo = '
			<script type="text/javascript">
				digirisk(document).ready(function(){
					actionMessageShow("#message' . TABLE_DUER . '", "' . $messageToOutput . '");
					setTimeout(\'actionMessageHide("#message' . TABLE_DUER . '")\',5000);
					' . $moremessageInfo . '
				});
			</script>';
	}

	echo $messageInfo;
}