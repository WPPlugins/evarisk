<?php
require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/tache/evaTask.class.php');

class dashboard {

	const boxNumber = 0;

	function dashboardMainPage()
	{
?>
	<div class="wrap">

	<div id="welcome-panel" class="welcome-panel digi-welcome-panel" >
		<div class="welcome-panel-content">
			<h3><?php _e( 'Bienvenue dans Digirisk', 'evarisk'); ?></h3>
			<p class="about-description"><?php _e( 'Progiciel gratuit d\'aide &agrave; l\'&eacute;valuation des risques professionnels', 'evarisk'); ?></p>

			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column">
					<h4><?php printf( __( '%sEvarisk%s est une soci&eacute;t&eacute; specialis&eacute;e dans l\'&eacute;tude m&eacute;thodologique de la pr&eacute;vention des risques.', 'evarisk'), '<a href="http://www.evarisk.com" target="_blank" >', '</a>'); ?></h4>
					<p><?php _e( 'Evarisk vous propose le premier logiciel libre d\'aide &agrave; la r&eacute;daction du document unique. Gr&acirc;ce &agrave; ce logiciel gratuit en Open Source, vous pourrez g&eacute;rer la totalit&eacute; des acteurs, des unit&eacute;s, et des dangers que vous devez recenser dans votre document unique.', 'evarisk' ); ?></p>
				</div>
				<div class="welcome-panel-column">
					<h4><?php _e( 'Plus d\'informations', 'evarisk'); ?></h4>
					<ul>
						<li><div class="dashicons dashicons-book-alt"></div><?php printf( __( 'Plus d\'informations sur le logiciel Digirisk %s ou %s', 'evarisk'), sprintf( __( 'avec %sla documentation en ligne%s', 'evarisk'), '<a target="_blank" href="http://www.evarisk.com/document-unique-logiciel/documentation" >', '</a>'), sprintf( __( '%sle forum%s', 'evarisk'), '<a target="_blank" href="http://www.evarisk.com/forums" >', '</a>') ); ?></li>
						<li><div class="dashicons dashicons-admin-plugins"></div><?php printf( __( 'Visitez notre %sboutique%s. Nous vous proposons des affiches, supports de formations ainsi que des illustrations', 'evarisk'), '<a target="_blank" href="http://www.evarisk.com/boutique" >', '</a>'); ?></li>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<div class="metabox-holder clear">
		<script type="text/javascript">
			digirisk(document).ready( function() {
				digirisk('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				digirisk('.postbox h3, .postbox .handlediv').each(function(){digirisk(this).unbind("click");});
				postboxes.add_postbox_toggles("evaDashboard");
				postboxes.init("evaDashboard");
				postboxes.save_state("evaDashboard");
				postboxes.save_order("evaDashboard");
			});
		</script>
<?php

	dashboard::getDashBoardContent();

?>
		<div style="float:left;width:49%;" id="evaDashboard_LeftSide" >
			<?php
				/*	Output the different Meta Box define for the dashboard page	*/
				do_meta_boxes("evaDashboard", "leftSide", null);
			?>
		</div>
		<div style="float:right;width:49%;" id="evaDashboard_RightSide" >
			<?php
				/*	Output the different Meta Box define for the dashboard page	*/
				do_meta_boxes("evaDashboard", "rightSide", null);
				//To preserve postBoxes order
				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false);
				//To preserve postBoxes closure state
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false);
			?>
		</div>
	</div>
</div>
<?php
	}

	/**
	* The global function that call the different functions defined to build the dashboard
	*/
	function getDashBoardContent()
	{
		/*	Get the not finished and passed end date corrective actions 	*/
		dashboard::actionCorrectivesNonTermineesDateDepassees_Box();

		/*	Get the finished actions with parent task not mark as done 	*/
		dashboard::actionCorrectivesTermineesTachesASolder_Box();

		/*	Get the finished corrective actions to reevaluate the associated element 	*/
		dashboard::actionCorrectivesTermineesAReEvaluer_Box();

		/*	Get different stats 	*/
		dashboard::vracStats_Box();
	}

/*	Start - correctives actions	*/


	/**
	*	Return the meta-box with the different actions with the date already passed but not finished
	*	@see actionCorrectivesNonTermineesDateDepassees_Check
	*/
	function actionCorrectivesNonTermineesDateDepassees_Box()
	{
		add_meta_box("evaDashboard_ac_passed", __("Actions correctives non termin&eacute;es dont la date est d&eacute;pass&eacute;e", "evarisk"), array("dashboard", "actionCorrectivesNonTermineesDateDepassees_Check"), "evaDashboard", "leftSide");
	}
	/**
	*	Return the content of the meta-box of not finished but passed actions
	*/
	function actionCorrectivesNonTermineesDateDepassees_Check()
	{
		$tasks = evaTask::getTaskForDashBoard('passed');
		echo $tasks;
	}

	/**
	*	Return the meta-box with the different task to sold because the sub-actions are done
	*	@see actionsCorrectivesTermineesTachesASolder_Check
	*/
	function actionCorrectivesTermineesTachesASolder_Box()
	{
		add_meta_box("evaDashboard_task_toDone", __("T&acirc;ches &agrave; solder dont toutes les actions sont termin&eacute;es", "evarisk"), array("dashboard", "actionsCorrectivesTermineesTachesASolder_Check"), "evaDashboard", "leftSide");
	}
	/**
	*	Return the content of the meta-box of done actions but not done task
	*/
	function actionsCorrectivesTermineesTachesASolder_Check()
	{
		$tasks = evaTask::getTaskForDashBoard('taskToMarkAsDone');
		echo $tasks;
	}

	/**
	*	Return the meta-box with the different actions that are already done but the associed risk have not been reevaluated
	*	@see actionCorrectivesTermineesAReEvaluer_Check
	*/
	function actionCorrectivesTermineesAReEvaluer_Box()
	{
		add_meta_box("evaDashboard_ac_done", __("Risques &agrave; re-&eacute;valuer suite &agrave; une action corrective", "evarisk"), array("dashboard", "actionCorrectivesTermineesAReEvaluer_Check"), "evaDashboard", "leftSide");
	}
	/**
	*	Return the content of the meta-box of done task but risk to reevaluate
	*/
	function actionCorrectivesTermineesAReEvaluer_Check()
	{
		$tasks = evaTask::getTaskForDashBoard('toEvaluate');
		echo $tasks;
	}


/*	End - correctives actions	*/



/*	Start - Stats en vrac	*/

	/**
	*	Return the meta-box with the different actions that are already done but the associed risk have not been reevaluated
	*	@see actionCorrectivesTermineesAReEvaluer_Check
	*/
	function vracStats_Box() {
		add_meta_box("evaDashboard_vracStats", __("Statistiques", "evarisk"), array("dashboard", "vracStats_Check"), "evaDashboard", "rightSide");
	}
	/**
	*	Return the content of the meta-box of done task but risk to reevaluate
	*/
	function vracStats_Check() {
		$output =
			'<div class="hide" id="loadingPicContainer" ><img src="' . PICTO_LOADING_ROUND . '" alt="loading ..." /></div>
			<div id="vracStatsTabs" >
				<ul>
					<li><a href="' . admin_url( 'admin-ajax.php' ) . '?action=digi_ajax_stats_user" title="vracStatsContent" >' . __('Personnel', 'evarisk') . '</a></li>
					<li><a href="' . admin_url( 'admin-ajax.php' ) . '?action=digi_ajax_risk_stats" title="vracStatsContent" >' . __('Risques', 'evarisk') . '</a></li>
				</ul>
			</div>
			<script type="text/javascript" >
				digirisk(document).ready(function(){
					digirisk("#vracStatsTabs").tabs();
				});
			</script>';

		echo $output;
	}

/*	End - Stats en vrac	*/


}