<?php
/**
* Plugin group manager
*
*	Define the different method to manage the group into the plugin
* @author Evarisk <dev@evarisk.com>
* @version 5.1.3.2
* @package Digirisk
* @subpackage librairies
*/


/**
* Define the different method to manage the group into the plugin
* @package Digirisk
* @subpackage librairies
*/
class eva_GroupSheet
{

	/**
	*	Return the form template for generating a work unit sheet
	*	@return string HTML code of the form
	*/
	function getGroupSheetForm()
	{
		return
'<table summary="" border="0" cellpadding="0" cellspacing="0" align="center" class="tabcroisillon" style="width:100%;" >
	<tr>
		<td style="width:60%;vertical-align:top;" >
			<table summary="" cellpadding="0" cellspacing="0" border="0" class="tabformulaire" style="width:100%;" >
				<tr>
					<td ><label for="nomFicheDeGroupement">' . __('nom de la fiche', 'evarisk') . '</label></td>
					<td >' . EvaDisplayInput::afficherInput('text', 'nomFicheDeGroupement', '#NOMDOCUMENT#', '', '', 'nomFicheDeGroupement', false, false, 150, '', '', '100%', '', 'left;width:100%;', false) . '</td>
				</tr>
				<tr>
					<td >&nbsp;</td>
					<td style="padding:12px 0px;" >
						<div>
							<input type="checkbox" id="FGPmodelDefaut" checked="checked" name="modelUse" value="modeleDefaut" />
							<label for="FGPmodelDefaut" style="vertical-align:middle;" >' . __('Utiliser le mod&egrave;le par d&eacute;faut', 'evarisk') . '</label>
						</div>
						<div id="modelListForGeneration" style="display:none;" >&nbsp;</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						' . EvaDisplayInput::afficherInput('hidden', 'nomEntreprise', '#NOMENTREPRISE#', '', '', 'nomEntreprise', false, false, 150, '', '', '100%', '', 'left', false) . '
						<input class="button-primary alignright" type="button" id="genereFGP" name="genereFGP" value="' . __('G&eacute;n&eacute;rer la fiche du groupement', 'evarisk') . '" />
						<input class="button-primary alignright" type="button" id="genereFSGP" name="genereFSGP" value="' . __('G&eacute;n&eacute;rer les fiches des sous-groupements', 'evarisk') . '" />
					</td>
				</tr>
			</table>
		</td>
		<td style="width:40%;" id="GroupSheetResultContainer" >&nbsp;</td>
	</tr>
</table>';
	}

	/**
	*	Output a form with the different field needed to save and generate a new document
	*
	*	@param mixed $tableElement The element type we want to generate a document for
	*	@param integer $idElement The element identifier we want to generate a document for
	*
	*	@return mixed The complete html output of the form
	*/
	function getGroupSheetGenerationForm($tableElement, $idElement)
	{
		$tableElementForDoc = $tableElement . '_FGP';
		unset($formulaireDocumentUniqueParams);
		$formulaireDocumentUniqueParams = array();
		$formulaireDocumentUniqueParams['#DATEFORM1#'] = date('Y-m-d');

		$groupInformations = EvaGroupement::getGroupement($idElement);
		$formulaireDocumentUniqueParams['#NOMDOCUMENT#'] = date('Ymd') . '_' . ELEMENT_IDENTIFIER_GP . $idElement . '_' . sanitize_title(str_replace(' ', '_', $groupInformations->nom));
		$groupementPere = EvaGroupement::getGroupement( $groupInformations->id );
		$ancetres = Arborescence::getAncetre(TABLE_GROUPEMENT, $groupementPere);
		$arborescence = '';
		foreach($ancetres as $ancetre)
		{
			if($ancetre->nom != "Groupement Racine")
			{
				$arborescence .= $ancetre->nom . ' - ';
			}
		}
		if($groupementPere->nom != "Groupement Racine")
		{
			$arborescence .= $groupementPere->nom . ' - ';
		}
		$formulaireDocumentUniqueParams['#NOMENTREPRISE#'] = digirisk_tools::slugify_noaccent($arborescence) . digirisk_tools::slugify_noaccent($groupInformations->nom);

		$modelChoice = '';
		$lastGroupSheet = eva_gestionDoc::getGeneratedDocument($tableElement, $idElement, 'last', '', 'fiche_de_groupement');
		$model_id_to_use = eva_gestionDoc::getDefaultDocument('fiche_de_groupement');
		if( !empty($lastGroupSheet) && !empty($lastGroupSheet->id_model) && ($lastGroupSheet->id_model != $model_id_to_use)) {
			$modelChoice = '
			setTimeout(function(){
				digirisk("#FGPmodelDefaut").click();
			},100);';
			$model_id_to_use = $lastGroupSheet->id_model;
		}

		$output = EvaDisplayDesign::feedTemplate(eva_GroupSheet::getGroupSheetForm(), $formulaireDocumentUniqueParams) . '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		digirisk("#genereFGP").click(function(){
			digirisk("#bilanBoxContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
			{
				"post":"true",
				"table":"' . TABLE_FP . '",
				"act":"saveFicheGroupement",
				"tableElement":"' . $tableElement . '",
				"idElement":"' . $idElement . '",
				"nomDuDocument":digirisk("#nomFicheDeGroupement").val(),
				"nomEntreprise":digirisk("#nomEntreprise").val(),
				"id_model":digirisk("#modelToUse' . $tableElementForDoc . '").val()
			});
			digirisk("#bilanBoxContainer").html(digirisk("#loadingImg").html());
		});
		digirisk("#genereFSGP").click(function(){
			digirisk("#bilanBoxContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
			{
				"post":"true",
				"table":"' . TABLE_FP . '",
				"act":"saveGroupSheetForGroupement",
				"tableElement":"' . $tableElement . '",
				"idElement":"' . $idElement . '",
				"nomDuDocument":digirisk("#nomFicheDeGroupement").val(),
				"nomEntreprise":digirisk("#nomEntreprise").val(),
				"id_model":digirisk("#modelToUse' . $tableElementForDoc . '").val()
			});
			digirisk("#bilanBoxContainer").html(digirisk("#loadingImg").html());
		});
		digirisk("#FGPmodelDefaut").click(function(){
			clearTimeout();
			setTimeout(function(){
				if(!digirisk("#FGPmodelDefaut").is(":checked")){
					digirisk("#GroupSheetResultContainer").html(\'<img src="' . EVA_IMG_DIVERS_PLUGIN_URL . 'loading.gif" alt="loading" />\');
					digirisk("#GroupSheetResultContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_DUER . '", "act":"loadNewModelForm", "tableElement":"' . $tableElementForDoc . '", "idElement":"' . $idElement . '"});
					digirisk("#modelListForGeneration").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_GED_DOCUMENTS . '", "act":"load_model_combobox", "tableElement":"' . $tableElementForDoc . '", "idElement":"' . $idElement . '", "category":"fiche_de_groupement", "selection":"' . $model_id_to_use . '"});
					digirisk("#modelListForGeneration").show();
				}
				else{
					digirisk("#GroupSheetResultContainer").html("");
					digirisk("#modelListForGeneration").html("");
					digirisk("#modelListForGeneration").hide();
				}
			},500
			);
		});
		' . $modelChoice . '
	});
</script>';

		return $output;
	}

	/**
	*	Generate a form to save work unit sheet collection for a groupment
	*
	*	@param mixed $tableElement The element type we want to get form for
	*	@param integer $idElement The element identifier we wan to get form for
	*
	*	@return string The hmtl code outputing the form to generate work unit sheet collection for a groupment
	*/
	function getGroupSheetCollectionGenerationForm($tableElement, $idElement)
	{
		$tableElementForDoc = $tableElement . '_FGP';
		$output = '
<table summary="" border="0" cellpadding="0" cellspacing="0" align="center" class="tabcroisillon" style="width:100%;" >
	<tr>
		<td id="documentFormContainer" >
			<div id="workUnitSheetCollectionModelSelector" >
				<div>
					<input type="checkbox" id="modelDefaut" checked="checked" name="modelUse" value="modeleDefaut" />
					<label for="modelDefaut" style="vertical-align:middle;" >' . __('Utiliser le mod&egrave;le par d&eacute;faut', 'evarisk') . '</label>
				</div>
				<div id="modelListForGeneration" style="display:none;" >&nbsp;</div>
			</div>
			<input type="button" class="clear button-primary" value="' . __('G&eacute;n&eacute;rer les fiches de postes', 'evarisk') . '" id="saveGroupSheetForGroupement" />
		</td>
		<td id="documentModelContainer" >&nbsp;</td>
	</tr>
</table>
<script type="text/javascript" >
	digirisk("#saveGroupSheetForGroupement").click(function(){
		digirisk("#documentFormContainer").html(digirisk("#loadingImg").html());
		digirisk("#documentFormContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
		{
			"post":"true",
			"table":"' . TABLE_FP . '",
			"act":"saveGroupSheetForGroupement",
			"tableElement":"' . $tableElement . '",
			"idElement":' . $idElement . ',
			"id_model":digirisk("#modelToUse' . $tableElementForDoc . '").val()
		});
	});

	digirisk("#modelDefaut").click(function(){
		setTimeout(function(){
			if(!digirisk("#modelDefaut").is(":checked"))
			{
				digirisk("#documentModelContainer").html(\'<img src="' . EVA_IMG_DIVERS_PLUGIN_URL . 'loading.gif" alt="loading" />\');
				digirisk("#documentModelContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_DUER . '", "act":"loadNewModelForm", "tableElement":"' . $tableElementForDoc . '", "idElement":"' . $idElement . '"});
				digirisk("#modelListForGeneration").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_GED_DOCUMENTS . '", "act":"load_model_combobox", "tableElement":"' . $tableElementForDoc . '", "idElement":"' . $idElement . '", "category":"fiche_de_groupement", "selection":""});
				digirisk("#modelListForGeneration").show();
			}
			else
			{
				digirisk("#documentModelContainer").html("");
				digirisk("#modelListForGeneration").html("");
				digirisk("#modelListForGeneration").hide();
			}
		},600);
	});
</script>';

		return $output;
	}

	/**
	*	Get the history of work unit sheet generated for a given element
	*
	*	@param mixed $tableElement The element type we want to get form for
	*	@param integer $idElement The element identifier we wan to get form for
	*
	*	@return string The html code output with the list of document or a message saying there no document for this element
	*/
	function getGroupSheetCollectionHistory($tableElement, $idElement, $doc_type = 'fiches_de_groupement', $element_identifier = ELEMENT_IDENTIFIER_GFGP) {
		$output = '';

		$list_FicheDePoste_du_Groupement = eva_gestionDoc::getDocumentList($tableElement, $idElement, $doc_type, "dateCreation DESC");
		if (count($list_FicheDePoste_du_Groupement) > 0) {
			foreach($list_FicheDePoste_du_Groupement as $fdpGpt) {
				if (is_file(EVA_GENERATED_DOC_DIR . $fdpGpt->chemin . $fdpGpt->nom)) {
					$output .= '-&nbsp;' . sprintf(__('G&eacute;n&eacute;r&eacute; le %s: (%s) <a href="%s" >%s</a>', 'evarisk'), mysql2date('d M Y', $fdpGpt->dateCreation, true), $element_identifier . $fdpGpt->id, EVA_GENERATED_DOC_URL . $fdpGpt->chemin . $fdpGpt->nom, $fdpGpt->nom) . '<br/>';
				}
			}
		}
		else {
			$output .= __('Aucune fiche n\'a &eacute;t&eacute; cr&eacute;e pour le moment', 'evarisk');
		}

		return $output;
	}

}