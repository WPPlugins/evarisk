<?php

$messageInfo = '';

$_POST['table'] = TABLE_GROUPEMENT;
$titrePage = __("Gestion des groupements et unit&eacute;s de travail",'evarisk');
$icone = EVA_EVAL_RISK_ICON;
$titreIcone = "Risk Evaluation Icon";
$altIcon = "Risk Evaluation Icon";
$titreFilAriane = __("Gestion des groupements et unit&eacute;s de travail",'evarisk');
if(!isset($_POST['affichage']))
{
	$_POST['affichage'] = "affichageTable";
}

require_once(EVA_LIB_PLUGIN_DIR . 'evaDisplayInput.class.php' );
echo EvaDisplayInput::afficherInput('hidden', 'menu', 'gestiongrptut', '', NULL, 'menu');

require_once(EVA_LIB_PLUGIN_DIR . 'classicalPage.php' );	

?>