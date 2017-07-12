<?php

require_once(EVA_CONFIG);
require_once(EVA_LIB_PLUGIN_DIR . 'evaDisplayDesign.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaReferentialText.class.php');

$titrePage = "Cr&eacute;ation de r&eacute;f&eacute;renciels";
$icone = "";
$titreIcone = "";
$altIcon = "icone";
$veilleReglementaireDisplay = evaDisplayDesign::afficherDebutPage($titrePage, $icone, $titreIcone, $altIcon, $table, true);
$idForm = "formVeilleReglementaire";
$veilleReglementaireDisplay = $veilleReglementaireDisplay . evaDisplayInput::ouvrirForm("POST", $idForm, $idForm);
$veilleReglementaireDisplay = $veilleReglementaireDisplay . EvaDisplayInput::afficherInput('hidden', 'act', '', '', null, 'act', false, false);
$veilleReglementaireDisplay = $veilleReglementaireDisplay . evaDisplayInput::fermerForm($idForm);
{//Création dataTable
	$idTable = 'tableTextesReferenciels';
	$titres = array(
		evaDisplayInput::afficherInput('checkbox', 'cb_tableTextesReferenciels', null, null, null, 'cb_tableTextesReferenciels'),
		__('Nom', 'evarisk'), 
		__('Affectable', 'evarisk'),  
		__('Unit&eacute;s affect&eacute;es', 'evarisk'));
	unset($lignesDeValeurs);
	//on récupère les textes.
	$textes = evaReferentialText::getReferentialTexts();
	$script = '';
	foreach($textes as $texte)
	{
		unset($valeurs);
		$idLigne = 'texte' . $texte->id;
		$valeurs[] = array('value'=>evaDisplayInput::afficherInput('checkbox', 'cb_' . $idLigne, null, null, null, 'cb_' . $idLigne));
		$valeurs[] = array('value'=>$texte->rubrique);
		$valeur = ($texte->affectable)?'oui':'non';
		$valeurs[] = array('value'=>$valeur);
		$valeurs[] = array('value'=>'&Agrave; faire');
		$lignesDeValeurs[] = $valeurs;
		$idLignes[] = $idLigne;
		$script = $script . '<script type="text/javascript">
			digirisk(document).ready(function() {
				digirisk("#cb_' . $idLigne . '").click(function(){
					toutCoche = true;
					digirisk(\'#' . $idTable . ' tbody :input\').each(function(){
						if(!(digirisk(this).prop("checked")))
						{
							toutCoche = false;
						}
					});
					if(toutCoche)
					{
						digirisk("#cb_tableTextesReferencielsHead").prop("checked", "checked");
						digirisk("#cb_tableTextesReferencielsFoot").prop("checked", "checked");
					}
					else
					{
						digirisk("#cb_tableTextesReferencielsHead").prop("checked", "");
						digirisk("#cb_tableTextesReferencielsFoot").prop("checked", "");
					}
				});
				digirisk("#' . $idLigne . ' .nomTexte").click(function(){
					alert("ouvrir texte ' . $texte->id . '")
				});
			});
		</script>';
	}
	$classes = array('cbColumn','nomTexte','analysable','unitesAffectees');
	$script = $script . '<script type="text/javascript">
		digirisk(document).ready(function() {
			digirisk("#cb_tableTextesReferenciels").attr("id", "cb_tableTextesReferencielsHead");
			digirisk("#cb_tableTextesReferenciels").attr("id", "cb_tableTextesReferencielsFoot");
			
			digirisk(\'#cb_tableTextesReferencielsHead\').click(function(){
				digirisk(\'#' . $idTable . ' tbody :input\').each(function(){
					if(digirisk(\'#cb_tableTextesReferencielsHead\').prop("checked"))
					{
						digirisk(this).prop("checked", "checked");
					}
					else
					{
						digirisk(this).prop("checked", "");
					}
				});
				if(digirisk(\'#cb_tableTextesReferencielsHead\').prop("checked"))
				{
					digirisk(\'#cb_tableTextesReferencielsFoot\').prop("checked", "checked");
				}
				else
				{
					digirisk(\'#cb_tableTextesReferencielsFoot\').prop("checked", "");
				}
			});
			digirisk(\'#cb_tableTextesReferencielsFoot\').click(function(){
				if(digirisk(\'#cb_tableTextesReferencielsHead\').prop("checked"))
				{
					digirisk(\'#cb_tableTextesReferencielsHead\').prop("checked", "");
				}
				else
				{
					digirisk(\'#cb_tableTextesReferencielsHead\').prop("checked", "checked");
				}
				digirisk(\'#cb_tableTextesReferencielsHead\').click();
				if(digirisk(\'#cb_tableTextesReferencielsHead\').prop("checked"))
				{
					digirisk(\'#cb_tableTextesReferencielsHead\').prop("checked", "");
				}
				else
				{
					digirisk(\'#cb_tableTextesReferencielsHead\').prop("checked", "checked");
				}
			});
			digirisk(\'#' . $idTable . '\').dataTable({"sPaginationType": \'full_numbers\', "bAutoWidth": false, "aoColumns": [
				{ "bSortable": false },
				{ "bSortable": true },
				{ "bSortable": false },
				{ "bSortable": true}],
				"aaSorting": [[1,\'asc\']]
			});
		});
	</script>';
	$veilleReglementaireDisplay =  $veilleReglementaireDisplay . evaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script);
}
$veilleReglementaireDisplay =  $veilleReglementaireDisplay . evaDisplayDesign::afficherFinPage();
echo $veilleReglementaireDisplay;
