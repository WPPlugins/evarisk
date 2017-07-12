<?php
	//Postbox definition
	$postBoxTitle = __('Fiche d\'unit&eacute; de travail', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
	$postBoxId = 'postBoxFicheDePoste';
	$postBoxCallbackFunction = 'getWorkUnitSheetPostBoxBody';
	add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_UNITES_DE_TRAVAIL, 'rightSide', 'default');

	require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/ficheDePoste/ficheDePoste.class.php' );

	function getWorkUnitSheetPostBoxBody($arguments) {
		if (((int)$arguments['idElement']) == 0) {
			$script = '<script type="text/javascript">
					digirisk(document).ready(function() {
						digirisk("#' . $postBoxId . '").hide();
					});
				</script>';
			echo $script;
		}
		else {
			$idElement = $arguments['idElement'];
			$tableElement = $arguments['tableElement'];

			$corpsPostBoxRisque = '
<div id="message' . TABLE_FP . '" class="updated fade" style="cursor:pointer; display:none;"></div>
<input type="hidden" name="subTabSelector" id="subTabSelector" value="" />
<ul class="eva_tabs" style="margin-bottom:2px;" >';
	if ( current_user_can('digi_edit_unite') || current_user_can('digi_edit_unite_' . $arguments['idElement']) ) {
		$userNotAllowed = '';
		$corpsPostBoxRisque .= '
	<li id="ongletImpressionFicheDePoste" class="tabs selected_tab" style="display:inline; margin-left:0.4em;"><label tabindex="1">' . ucfirst(strtolower( __('Fiches de l\'unit&eacute; de travail', 'evarisk'))) . '</label></li>
	<li id="ongletImpressionListingRisque" class="tabs" style="display:inline; margin-left:0.4em;"><label tabindex="1">' . ucfirst(strtolower( __('Synth&eacute;se des risques', 'evarisk'))) . '</label></li>
	<li id="ongletImpressionFichesPenibilite" class="tabs" style="display:inline; margin-left:0.4em;"><label tabindex="1">' . ucfirst(strtolower( __('Fiches de p&eacute;nibilit&eacute;', 'evarisk'))) . '</label></li>';
	}
	else {
		$userNotAllowed = 'digirisk("#ongletHistoriqueDocument").click();';
	}
	$corpsPostBoxRisque .= '
	<li id="ongletHistoriqueDocument" class="tabs" style="display:inline; margin-left:0.4em;"><label tabindex="2">' . ucfirst(strtolower( __('Historique des fiches de l\'unit&eacute; de travail', 'evarisk'))) . '</label></li>
</ul>
<div id="divImpressionFicheDePoste" class="eva_tabs_panel">' . eva_WorkUnitSheet::getWorkUnitSheetGenerationForm($tableElement, $idElement) . '</div>
<div id="divImpressionListingRisque" class="eva_tabs_panel" style="display:none"></div>
<div id="divImpressionFichesPenibilite" class="eva_tabs_panel" style="display:none"></div>
<div id="divHistoriqueFicheDePoste" class="eva_tabs_panel" style="display:none"></div>
<script type="text/javascript" >
	function loadBilanBoxContent_FP(boxId, action, table) {
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
		digirisk("#ongletImpressionFicheDePoste").click(function(){
			commonTabChange("postBoxFicheDePoste", "#divImpressionFicheDePoste", "#ongletImpressionFicheDePoste");
			loadBilanBoxContent_FP("#divImpressionFicheDePoste", "generateWorkUnitSheet", "' . TABLE_FP . '");
		});

		digirisk("#ongletImpressionListingRisque").click(function(){
			commonTabChange("postBoxFicheDePoste", "#divImpressionListingRisque", "#ongletImpressionListingRisque");
			loadBilanBoxContent_FP("#divImpressionListingRisque", "riskListingGeneration", "' . TABLE_DUER . '");
		});

		digirisk("#ongletImpressionFichesPenibilite").click(function(){
			commonTabChange("postBoxFicheDePoste", "#divImpressionFichesPenibilite", "#ongletImpressionFichesPenibilite");
			loadBilanBoxContent_FP("#divImpressionFichesPenibilite", "ficheDePenibiliteGeneration", "' . TABLE_DUER . '");
		});

		digirisk("#ongletHistoriqueDocument").click(function(){
			commonTabChange("postBoxFicheDePoste", "#divHistoriqueFicheDePoste", "#ongletHistoriqueDocument");
			loadBilanBoxContent_FP("#divHistoriqueFicheDePoste", "workUnitSheetHisto", "' . TABLE_FP . '");
		});

		' . $userNotAllowed . '
	});
</script>';

			echo $corpsPostBoxRisque;
		}
	}
