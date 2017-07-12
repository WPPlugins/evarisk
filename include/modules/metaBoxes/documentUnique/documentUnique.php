<?php

	//Postbox definition
	$postBoxTitle = __('Bilan', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
	$postBoxId = 'postBoxDocumentUnique';
	$postBoxCallbackFunction = 'getDocumentUniquePostBoxBody';
	add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_GROUPEMENTS, 'rightSide', 'default');

	require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/documentUnique/documentUnique.class.php' );

	function getDocumentUniquePostBoxBody($arguments)
	{
		if(((int)$arguments['idElement']) == 0)
		{
			$script = '<script type="text/javascript">
					digirisk(document).ready(function() {
						digirisk("#postBoxDocumentUnique").hide();
					});
				</script>';
			echo $script;
		}
		else
		{
			$idElement = $arguments['idElement'];
			$tableElement = $arguments['tableElement'];

			$scriptRisque =
			'<script type="text/javascript">
				function loadBilanBoxContent(boxId, action, table) {
					digirisk(boxId).html(digirisk("#loadingImg").html());
					digirisk(boxId).load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
						"post":"true",
						"table":table,
						"act":action,
						"tableElement":"' . $tableElement . '",
						"idElement":' . $idElement . '
					});
				}

				digirisk(document).ready(function(){
					digirisk("#ongletRisquesLignes").click(function(){
						commonTabChange("postBoxDocumentUnique", "#divRisquesLignes", "#ongletRisquesLignes");
						loadBilanBoxContent("#divRisquesLignes", "voirRisqueLigne", "' . TABLE_RISQUE . '");
					});

					digirisk("#ongletRisquesUnites").click(function(){
						commonTabChange("postBoxDocumentUnique", "#divRisquesUnites", "#ongletRisquesUnites");
						loadBilanBoxContent("#divRisquesUnites", "voirRisqueUnite", "' . TABLE_RISQUE . '");
					});

					digirisk("#ongletDocumentUnique").click(function(){
						commonTabChange("postBoxDocumentUnique", "#divDocumentUnique", "#ongletDocumentUnique");
						loadBilanBoxContent("#divDocumentUnique", "generateSummary", "' . TABLE_DUER . '");
					});

					digirisk("#ongletHistoriqueDocument").click(function(){
						commonTabChange("postBoxDocumentUnique", "#divHistoriqueDocument", "#ongletHistoriqueDocument");
						loadBilanBoxContent("#divHistoriqueDocument", "voirHistoriqueDocument", "' . TABLE_DUER . '");
					});
				});
			</script>';

			$corpsPostBoxRisque = $scriptRisque . '
				<div id="message' . TABLE_DUER . '" class="updated fade" style="cursor:pointer; display:none;"></div>
				<ul class="eva_tabs" style="margin-bottom:2px;" >';
			if (current_user_can('digi_edit_groupement') || current_user_can('digi_edit_groupement_' . $idElement)) {
				$userNotAllowed = '';
				$corpsPostBoxRisque .= '
					<li id="ongletDocumentUnique" class="tabs selected_tab" style="display:inline; margin-left:0.4em;"><label tabindex="1">' . ucfirst(strtolower( __('G&eacute;n&eacute;rer le bilan', 'evarisk'))) . '</label></li>';
			}
			else {
				$userNotAllowed = '<script type="text/javascript" >digirisk("#ongletHistoriqueDocument").click()</script>';
			}
			$corpsPostBoxRisque .= '
					<li id="ongletRisquesLignes" class="tabs" style="display:inline; margin-left:0.4em;"><label tabindex="2">' . ucfirst(strtolower( __('Risques unitaires', 'evarisk'))) . '</label></li>
					<li id="ongletRisquesUnites" class="tabs" style="display:inline; margin-left:0.4em;"><label tabindex="3">' . ucfirst(strtolower( __('Risques par unit&eacute;', 'evarisk'))) . '</label></li>
					<li id="ongletHistoriqueDocument" class="tabs" style="display:inline; margin-left:0.4em;"><label tabindex="4">' . ucfirst(strtolower( __('Historique des documents', 'evarisk'))) . '</label></li>
				</ul>
				<div id="divDocumentUnique" class="eva_tabs_panel">' . eva_documentUnique::getBoxBilan($tableElement, $idElement) . '</div>
				<div id="divRisquesUnites" class="eva_tabs_panel" style="display:none"></div>
				<div id="divRisquesLignes" class="eva_tabs_panel" style="display:none"></div>
				<div id="divHistoriqueDocument" class="eva_tabs_panel" style="display:none"></div>
				<input type="hidden" name="subTabSelector" id="subTabSelector" value="" />' . $userNotAllowed;

			echo $corpsPostBoxRisque;
		}
	}
