<?php
/**
 *
 * @author Soci&eacute;t&eacute; Evarisk
 * @version v5.0
 */

class eva_WorkUnitSheet {

	/**
	*	Return the form template for generating a work unit sheet
	*	@return string HTML code of the form
	*/
	function getWorkUnitSheetForm() {
		return
'<table summary="" border="0" cellpadding="0" cellspacing="0" align="center" class="tabcroisillon" style="width:100%;" >
	<tr>
		<td style="width:60%;vertical-align:top;" >
			<table summary="" cellpadding="0" cellspacing="0" border="0" class="tabformulaire" style="width:100%;" >
				<tr>
					<td ><label for="nomFicheDePoste">' . __('nom de la fiche', 'evarisk') . '</label></td>
					<td >' . EvaDisplayInput::afficherInput('text', 'nomFicheDePoste', '#NOMDOCUMENT#', '', '', 'nomFicheDePoste', false, false, 150, '', '', '100%', '', 'left', false) . '</td>
				</tr>
				<tr>
					<td >&nbsp;</td>
					<td style="padding:12px 0px;" >
						<div>
							<input type="checkbox" id="FPmodelDefaut" checked="checked" name="modelUse" value="modeleDefaut" />
							<label for="FPmodelDefaut" style="vertical-align:middle;" >' . __('Utiliser le mod&egrave;le par d&eacute;faut', 'evarisk') . '</label>
						</div>
						<div id="modelListForGeneration" style="display:none;" >&nbsp;</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						' . EvaDisplayInput::afficherInput('hidden', 'nomEntreprise', '#NOMENTREPRISE#', '', '', 'nomEntreprise', false, false, 150, '', '', '100%', '', 'left', false) . '
						<input class="button-primary alignright" type="button" id="genererFP" name="genererFP" value="' . __('g&eacute;n&eacute;rer', 'evarisk') . '" />
					</td>
				</tr>
			</table>
		</td>
		<td style="width:40%;" id="workUnitSheetResultContainer" >&nbsp;</td>
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
	function getWorkUnitSheetGenerationForm($tableElement, $idElement) {
		unset($formulaireDocumentUniqueParams);
		$formulaireDocumentUniqueParams = array();
		$formulaireDocumentUniqueParams['#DATEFORM1#'] = date('Y-m-d');

		$workUnitinformations = eva_UniteDeTravail::getWorkingUnit($idElement);
		$formulaireDocumentUniqueParams['#NOMDOCUMENT#'] = date('Ymd') . '_' . ELEMENT_IDENTIFIER_UT . $idElement . '_' . sanitize_title(str_replace(' ', '_', $workUnitinformations->nom));
		$groupementPere = EvaGroupement::getGroupement($workUnitinformations->id_groupement);
		$ancetres = Arborescence::getAncetre(TABLE_GROUPEMENT, $groupementPere);
		$arborescence = '';
		foreach ( $ancetres as $ancetre ) {
			if ( $ancetre->nom != "Groupement Racine" ) {
				$arborescence .= $ancetre->nom . ' - ';
			}
		}
		if ( $groupementPere->nom != "Groupement Racine" ) {
			$arborescence .= $groupementPere->nom . ' - ';
		}
		$formulaireDocumentUniqueParams['#NOMENTREPRISE#'] = digirisk_tools::slugify_noaccent($arborescence) . digirisk_tools::slugify_noaccent($workUnitinformations->nom);

		$modelChoice = '';
		$lastWorkUnitSheet = eva_gestionDoc::getGeneratedDocument($tableElement, $idElement, 'last', '', 'fiche_de_poste');
		$model_id = eva_gestionDoc::getDefaultDocument('fiche_de_poste');
		if ( !empty($lastWorkUnitSheet) && is_object($lastWorkUnitSheet) && !empty($lastWorkUnitSheet->id_model) && ($lastWorkUnitSheet->id_model != $model_id) ) {
			$model_id = $lastWorkUnitSheet->id_model;
			$modelChoice = '
			setTimeout(function(){
				digirisk("#FPmodelDefaut").click();
			},100);';
		}

		$output = EvaDisplayDesign::feedTemplate(eva_WorkUnitSheet::getWorkUnitSheetForm(), $formulaireDocumentUniqueParams) . '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		digirisk("#genererFP").click(function(){
			digirisk("#divImpressionFicheDePoste").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
				"post":"true",
				"table":"' . TABLE_FP . '",
				"act":"saveFichePoste",
				"tableElement":"' . $tableElement . '",
				"idElement":"' . $idElement . '",
				"nomDuDocument":digirisk("#nomFicheDePoste").val(),
				"nomEntreprise":digirisk("#nomEntreprise").val(),
				"id_model":digirisk("#modelToUse' . $tableElement . '").val()
			});
			digirisk("#divImpressionFicheDePoste").html(digirisk("#loadingImg").html());
		});
		digirisk("#FPmodelDefaut").click(function(){
			clearTimeout();
			setTimeout(function(){
				if(!digirisk("#FPmodelDefaut").is(":checked")){
					digirisk("#workUnitSheetResultContainer").html(\'<img src="' . EVA_IMG_DIVERS_PLUGIN_URL . 'loading.gif" alt="loading" />\');
					digirisk("#workUnitSheetResultContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_DUER . '", "act":"loadNewModelForm", "tableElement":"' . $tableElement . '", "idElement":"' . $idElement . '"});
					digirisk("#modelListForGeneration").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_GED_DOCUMENTS . '", "act":"load_model_combobox", "tableElement":"' . $tableElement . '", "idElement":"' . $idElement . '", "category":"fiche_de_poste", "selection":"' . $model_id . '"});
					digirisk("#modelListForGeneration").show();
				}
				else{
					digirisk("#workUnitSheetResultContainer").html("");
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
	function getWorkUnitSheetCollectionGenerationForm($tableElement, $idElement) {
		$tableElementForDoc = $tableElement . '_FP';
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
			<input type="button" class="clear button-primary" value="' . __('G&eacute;n&eacute;rer les fiches des unit&eacute;s de travail', 'evarisk') . '" id="saveWorkUnitSheetForGroupement" />
		</td>
		<td id="documentModelContainer" >&nbsp;</td>
	</tr>
</table>
<script type="text/javascript" >
	digirisk("#saveWorkUnitSheetForGroupement").click(function() {
		digirisk("#documentFormContainer").html(digirisk("#loadingImg").html());
		digirisk("#documentFormContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
			"post":"true",
			"table":"' . TABLE_FP . '",
			"act":"saveWorkUnitSheetForGroupement",
			"tableElement":"' . $tableElement . '",
			"idElement":' . $idElement . ',
			"id_model":digirisk("#modelToUse' . $tableElementForDoc . '").val()
		});
	});

	digirisk("#modelDefaut").click(function(){
		setTimeout(function(){
			if (!digirisk("#modelDefaut").is(":checked")) {
				digirisk("#documentModelContainer").html(\'<img src="' . EVA_IMG_DIVERS_PLUGIN_URL . 'loading.gif" alt="loading" />\');
				digirisk("#documentModelContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_DUER . '", "act":"loadNewModelForm", "tableElement":"' . $tableElementForDoc . '", "idElement":"' . $idElement . '"});
				digirisk("#modelListForGeneration").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_GED_DOCUMENTS . '", "act":"load_model_combobox", "tableElement":"' . $tableElementForDoc . '", "idElement":"' . $idElement . '", "category":"fiche_de_poste", "selection":""});
				digirisk("#modelListForGeneration").show();
			}
			else {
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
	function getWorkUnitSheetCollectionHistory($tableElement, $idElement) {
		$output = '';

		$ficheDePoste_du_Groupement = eva_gestionDoc::getDocumentList($tableElement, $idElement, 'fiche_de_poste_groupement', "dateCreation DESC");
		if (count($ficheDePoste_du_Groupement) > 0) {
			foreach($ficheDePoste_du_Groupement as $fdpGpt) {
				if(is_file(EVA_GENERATED_DOC_DIR . $fdpGpt->chemin . $fdpGpt->nom)) {
					$output .= '-&nbsp;' . sprintf(__('G&eacute;n&eacute;r&eacute; le %s: (%s) <a href="%s" >%s</a>', 'evarisk'), mysql2date('d M Y', $fdpGpt->dateCreation, true), ELEMENT_IDENTIFIER_GFP . $fdpGpt->id, EVA_GENERATED_DOC_URL . $fdpGpt->chemin . $fdpGpt->nom, $fdpGpt->nom) . '<br/>';
				}
			}
		}
		else {
			$output .= __('Aucune fiche n\'a &eacute;t&eacute; cr&eacute;e pour le moment', 'evarisk');
		}

		return $output;
	}

}