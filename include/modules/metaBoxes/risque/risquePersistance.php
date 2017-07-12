<?php
require_once(EVA_CONFIG );
require_once(EVA_LIB_PLUGIN_DIR . 'risque/Risque.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/tache/evaTask.class.php' );
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

if($_REQUEST['act'] == 'save'){
	$idRisque = digirisk_tools::IsValid_Variable($_REQUEST['idRisque']);

	/*	Check if there are correctiv actions to link with this risk	*/
	$actionsCorrectives = !empty($_REQUEST['actionsCorrectives'])?digirisk_tools::IsValid_Variable($_REQUEST['actionsCorrectives']):'';
	if (($actionsCorrectives != '') && ($idRisque > 0)) {
		Risque::update_risk_rating_link_with_task($idRisque, $actionsCorrectives, array('before', 'after'));
		$task = new EvaTask();
		$task->setId($actionsCorrectives);
		$task->load();
		$task->setEfficacite($_REQUEST['action_efficiency']);
		$task->save();
	}
	else {
		$idDanger = !empty($_REQUEST['idDanger']) ? digirisk_tools::IsValid_Variable($_REQUEST['idDanger']) : '';
		$idMethode = !empty($_REQUEST['idMethode']) ? digirisk_tools::IsValid_Variable($_REQUEST['idMethode']) : '';
		$tableElement = !empty($_REQUEST['tableElement']) ? digirisk_tools::IsValid_Variable($_REQUEST['tableElement']) : '';
		$idElement = !empty($_REQUEST['idElement']) ? digirisk_tools::IsValid_Variable($_REQUEST['idElement']) : '';
		$date_debut = !empty($_REQUEST['risk_start_date']) ? digirisk_tools::IsValid_Variable($_REQUEST['risk_start_date']) : '';
		$date_fin = !empty($_REQUEST['risk_end_date']) ? digirisk_tools::IsValid_Variable($_REQUEST['risk_end_date']) : '';
		$risk_eval_date = !empty($_REQUEST['risk_eval_date']) ? digirisk_tools::IsValid_Variable($_REQUEST['risk_eval_date']) : '';
		$risk_status = !empty($_REQUEST['force_status']) ? digirisk_tools::IsValid_Variable($_REQUEST['force_status']) : '';
		$variables = $_REQUEST['variables'];
		$description = !empty($_REQUEST['description_risque']) ? digirisk_tools::IsValid_Variable($_REQUEST['description_risque']) : '';
		$histo = digirisk_tools::IsValid_Variable($_REQUEST['histo']);
		$idRisque = Risque::saveNewRisk($idRisque, $idDanger, $idMethode, $tableElement, $idElement, $variables, $description, $histo, $date_debut, $date_fin, $risk_eval_date, $risk_status);
	}

	/*	Check if there are recommendation to link with this risk	*/
	$preconisationRisqueTitle = !empty($_REQUEST['preconisationRisqueTitle']) ? digirisk_tools::IsValid_Variable($_REQUEST['preconisationRisqueTitle']) : '';
	$preconisationRisque = !empty($_REQUEST['preconisationRisque']) ? digirisk_tools::IsValid_Variable($_REQUEST['preconisationRisque']) : '';
	if ( !empty($preconisationRisque) || !empty($preconisationRisqueTitle ) ) {
		EvaTask::save_task_from_risk_form($preconisationRisqueTitle, $preconisationRisque, $idRisque);
	}

	if($_REQUEST['act'] == 'save')
		echo '<script type="text/javascript" >goTo("#postBoxRisques");</script>';
}

if($_REQUEST['act'] == 'delete')
{
	$table = digirisk_tools::IsValid_Variable($_REQUEST['table']);
	$idRisque = digirisk_tools::IsValid_Variable($_REQUEST['idRisque']);
	$tableElement = digirisk_tools::IsValid_Variable($_REQUEST['tableElement']);
	$idElement = digirisk_tools::IsValid_Variable($_REQUEST['idElement']);
	$deleteResult = Risque::deleteRisk($idRisque, $tableElement, $idElement);

	$messageInfo =
	'<script type="text/javascript">
		digirisk(document).ready(function(){
			digirisk("#message' . $table . '").addClass("updated");';
		if($deleteResult != 'error')
		{
			$messageInfo .= '
			digirisk("#message' . $table . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Le risque a &eacute;t&eacute; supprim&eacute;.', 'evarisk') . '</strong></p>') . '");';
		}
		else
		{
			$messageInfo .= '
			digirisk("#message' . $table . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="no-response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Le risque n\'a pas pu &ecirc;tre supprim&eacute;.', 'evarisk') . '</strong></p>') . '");';
		}
		$messageInfo .= '
			digirisk("#message' . $table . '").show();
			setTimeout(function(){
				digirisk("#message' . $table . '").removeClass("updated");
				digirisk("#message' . $table . '").hide();
			},5000);

			digirisk("#ongletVoirLesRisques").click();
		});
	</script>';
	echo $messageInfo;
}