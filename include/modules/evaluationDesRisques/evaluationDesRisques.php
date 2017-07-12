<?php
$messageInfo = '';

$_POST['table'] = TABLE_GROUPEMENT;
$titrePage = __("&Eacute;valuation des risques",'evarisk');
$icone = EVA_EVAL_RISK_ICON;
$titreIcone = "Risk Evaluation Icon";
$altIcon = "Risk Evaluation Icon";
$titreFilAriane = __("&Eacute;valuation des risques",'evarisk');
if(!isset($_POST['affichage']))
{
	$_POST['affichage'] = "affichageListe";
}


require_once(EVA_LIB_PLUGIN_DIR . 'evaDisplayInput.class.php' );
echo EvaDisplayInput::afficherInput('hidden', 'menu', 'gestiongrptut', '', NULL, 'menu');

require_once(EVA_LIB_PLUGIN_DIR . 'classicalPage.php' );	

	if(isset($_GET['elt']) && ($_GET['elt'] != ''))
	{
		echo
			'<script type="text/javascript">
				digirisk(document).ready(function(){
					digirisk(\'#partieEdition\').html(\'<center><img src="' . PICTO_LOADING . '" /></center>\');
					setTimeout(function(){
						digirisk("#' . $_GET['elt'] . '").click();
					},3000);
				})
			</script>';
	}
	if(isset($_GET['risk']) && ($_GET['risk'] != ''))
	{
		echo
			'<script type="text/javascript">
				digirisk(document).ready(function(){
					setTimeout(function(){
						digirisk("#' . $_GET['risk'] . '").click();
					},4000);
				})
			</script>';
	}

?>