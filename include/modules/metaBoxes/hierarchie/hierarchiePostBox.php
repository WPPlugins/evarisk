<?php

	//Postbox definition
	$postBoxTitle = __('Services, Unit&eacute;s de travail et risques', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
	$postBoxId = 'postBoxHierarchie';
	$postBoxCallbackFunction = 'getHierarchiePostBoxBody';
	add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_TACHE, 'rightSide', 'default');
	require_once( EVA_LIB_PLUGIN_DIR . 'arborescence/arborescence_special.class.php');

	function getHierarchiePostBoxBody($arguments) {
		$idElement = $arguments['idElement'];
		$tableElement = $arguments['tableElement'];
		$current_content = '&nbsp;';

		switch ($tableElement) {
			case TABLE_TACHE:
				$currentTask = new EvaTask($idElement);
				$currentTask->load();
				$currentTaskAffectedTableId = $currentTask->getIdFrom();
				$currentTaskAffectedTable = $currentTask->getTableFrom();
				$ProgressionStatus = $currentTask->getProgressionStatus();

				$arborescenceRisque = arborescence_special::arborescenceRisque(TABLE_GROUPEMENT, 1);

				$idBouttonEnregistrer = 'enregistrerProvenanceTache';
				$scriptEnregistrement = '';

				if (current_user_can('digi_edit_task') || current_user_can('digi_edit_task_' . $idElement)) {
					if ( ($ProgressionStatus == 'inProgress') || ($ProgressionStatus == 'notStarted') || (digirisk_options::getOptionValue('possibilite_Modifier_Tache_Soldee')== 'oui') ) {
						$save_button =
							'<div id="saveLinkTaskElement" >' . EvaDisplayInput::afficherInput('submit', $idBouttonEnregistrer, __('Enregistrer', 'evarisk'), null, '', 'saveTache', false, true, '', 'button-primary alignright', '', '', $scriptEnregistrement) . '</div><div class="digirisk_hide alignright" id="savingLinkTaskElement" >&nbsp;</div>';
					}
					else {
						$save_button =
							'<div class="alignright button-secondary" id="TaskSaveButton" >' . __('Cette t&acirc;che est sold&eacute;e, vous ne pouvez pas la modifier', 'evarisk') . '</div>';
					}
				}
				$current_content = arborescence_special::display_mini_hierarchy_for_element_affectation($currentTaskAffectedTable, $currentTaskAffectedTableId);
			break;
		}

		$output = '
<div id="messageh' . $tableElement . '" class="updated fade digirisk_hide pointer" >&nbsp;</div>
<form action="' . EVA_INC_PLUGIN_URL . 'ajax.php" method="post" id="hierarchy_affectation_form" >
	<input type="hidden" name="post" value="true" />
	<input type="hidden" name="table" value="' . TABLE_TACHE . '" />
	<input type="hidden" name="act" value="updateProvenance" />
	<input type="hidden" name="id" value="' . $idElement . '" />
	' . arborescence_special::search_form($currentTaskAffectedTable, $currentTaskAffectedTableId, $current_content, $tableElement, $idElement) . '
	<div class="clear" >' . $save_button . '</div>
</form>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		/*	Create an ajax form	*/
		jQuery("#hierarchy_affectation_form").ajaxForm({
			target: "#ajax-response",
			beforeSubmit: function(){
				jQuery("#savingLinkTaskElement").html(jQuery("#loading_round_pic div").html());
				jQuery("#saveLinkTaskElement").hide();
				jQuery("#savingLinkTaskElement").show();
			}
		});
	});
</script>';

		echo $output;
	}

?>