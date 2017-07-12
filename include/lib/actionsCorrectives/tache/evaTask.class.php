<?php
/**
 * This class allows to work on single task (equivalent to single row in data base)
 *
 * @author Evarisk
 * @version v5.0
 */
require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/tache/evaBaseTask.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/tache/evaTaskTable.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/activite/evaActivity.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/activite/evaActivityTable.class.php');

class EvaTask extends EvaBaseTask
{
	/**
	* Save or update the Task in data base
	*/
	function save() {
		global $wpdb;
		global $current_user;

		{//Variables cleaning
			$id = (int) digirisk_tools::IsValid_Variable($this->getId());
			$name = digirisk_tools::IsValid_Variable($this->getName());
			$leftLimit = (int) digirisk_tools::IsValid_Variable($this->getLeftLimit());
			$rightLimit = (int) digirisk_tools::IsValid_Variable($this->getRightLimit());
			$description = digirisk_tools::IsValid_Variable($this->getDescription());
			$startDate = digirisk_tools::IsValid_Variable($this->getStartDate());
			$finishDate = digirisk_tools::IsValid_Variable($this->getFinishDate());
			$place = digirisk_tools::IsValid_Variable($this->getPlace());
			$progression = (int) digirisk_tools::IsValid_Variable($this->getProgression());
			$cost = (int) digirisk_tools::IsValid_Variable($this->getCost());
			$idFrom = (int) digirisk_tools::IsValid_Variable($this->getIdFrom());
			$tableFrom = digirisk_tools::IsValid_Variable($this->getTableFrom());
			$status = digirisk_tools::IsValid_Variable($this->getStatus());
			$idCreateur = digirisk_tools::IsValid_Variable($current_user->ID);
			$idResponsable = digirisk_tools::IsValid_Variable($this->getidResponsable());
			$idSoldeur = digirisk_tools::IsValid_Variable($this->getidSoldeur());
			$idSoldeurChef = digirisk_tools::IsValid_Variable($this->getidSoldeurChef());
			$ProgressionStatus = digirisk_tools::IsValid_Variable($this->getProgressionStatus());
			$dateSolde = digirisk_tools::IsValid_Variable($this->getdateSolde());
			$hasPriority = digirisk_tools::IsValid_Variable($this->gethasPriority());
			$efficacite = digirisk_tools::IsValid_Variable($this->getEfficacite());
			$idPhotoAvant = digirisk_tools::IsValid_Variable($this->getidPhotoAvant());
			$idPhotoApres = digirisk_tools::IsValid_Variable($this->getidPhotoApres());
			$nom_exportable_plan_action = digirisk_tools::IsValid_Variable($this->getnom_exportable_plan_action());
			$is_readable_from_external = digirisk_tools::IsValid_Variable($this->get_external_readable());
			$description_exportable_plan_action = digirisk_tools::IsValid_Variable($this->getdescription_exportable_plan_action());

			$real_start_date = digirisk_tools::IsValid_Variable($this->getreal_start_date());
			$real_end_date = digirisk_tools::IsValid_Variable($this->getreal_end_date());
			$estimate_cost = digirisk_tools::IsValid_Variable($this->getestimate_cost());
			$real_cost = digirisk_tools::IsValid_Variable($this->getreal_cost());
			$planned_time = $this->getplanned_time();
			$elapsed_time = $this->getelapsed_time();
		}

		/**		Build action information for database insertion	*/
		$tache_main_args = array(
			self::name => $name,
			self::leftLimit => $leftLimit,
			self::rightLimit => $rightLimit,
			self::description => $description,
			self::startDate => $startDate,
			self::finishDate => $finishDate,
			self::place => $place,
			self::progression => $progression,
			self::cost => $cost,
			self::idFrom => $idFrom,
			self::tableFrom => $tableFrom,
			self::status => $status,
			self::idResponsable => $idResponsable,
			self::idSoldeur => $idSoldeur,
			self::idSoldeurChef => $idSoldeurChef,
			self::ProgressionStatus => $ProgressionStatus,
			self::dateSolde => $dateSolde,
			self::hasPriority => $hasPriority,
			self::efficacite => $efficacite,
			self::idPhotoAvant => $idPhotoAvant,
			self::idPhotoApres => $idPhotoApres,
			self::nom_exportable_plan_action => $nom_exportable_plan_action,
			self::description_exportable_plan_action => $description_exportable_plan_action,
			self::is_readable_from_external => $is_readable_from_external,
			self::real_start_date => $real_start_date,
			self::real_end_date => $real_end_date,
			self::estimate_cost => $estimate_cost,
			self::real_cost => $real_cost,
			self::planned_time => $planned_time,
			self::elapsed_time => $elapsed_time,
		);
		/**		Laucnh query to database	*/
		$tache_save_operation = false;
		if( $id == 0 ) {
			$activite_creation_action = wp_parse_args( array(
				self::firstInsert => current_time('mysql', 0),
				self::idCreateur => $idCreateur,
			), $tache_main_args );
			$tache_save_operation = $wpdb->insert( TABLE_TACHE, $activite_creation_action );
		}
		else {//Update of the data base
			$tache_save_operation = $wpdb->update( TABLE_TACHE, $tache_main_args, array( self::id => $id, ) );
		}

		//Query execution
		/* We use identity (===) because query can return both, 0 and false
		 * if 0 is return, their is no change but no trouble in database
	 	 */
		if ( $tache_save_operation === false ) {//Their is some troubles
			$this->setStatus('error');
		}
		else {//Their is no trouble
			if ( $this->getId() == null ) {
				$id = $wpdb->insert_id;
				$this->setId($id);
			}
		}
	}

	/**
	* Load the Task with identifier key
	*/
	function load()
	{
		global $wpdb;

		$id = (int) digirisk_tools::IsValid_Variable($this->getId());
		if($id != 0)
		{
			$wpdbTask = $wpdb->get_row( "SELECT * FROM " . TABLE_TACHE . " WHERE " . self::id . " = " . $id);
			if($wpdbTask != null)
			{
				$this->convertWpdb($wpdbTask);
			}
		}
	}

/*
 * Others methods
 */
	/**
	* Get the activities depend on the task
	* @return object The activities in the form of wpdb object
	*/
	function getWPDBActivitiesDependOn()
	{
		global $wpdb;

		$query = $wpdb->prepare("
			SELECT *
			FROM " . TABLE_ACTIVITE . "
			WHERE " . EvaActivity::relatedTaskId . " = %d
				AND " . EvaActivity::status . " = 'Valid'", $this->getId()
		);
		return $wpdb->get_results($query);
	}

	/**
	* Get the activities depend on the task
	* @return EvaActivity The activities in the form of EvaActivity object
	*/
	function getActivitiesDependOn()
	{
		$wpdbActivities = $this->getWPDBActivitiesDependOn();
		$activities = new EvaActivityTable();
		foreach($wpdbActivities as $wpdbActivity)
		{
			$activity = new EvaActivity();
			$activity->convertWpdb($wpdbActivity);
			$activities->addActivity($activity);
		}
		return $activities;
	}

	/**
	* Transfert a (sub-)task from one task to another.
	* @param int $newMotherTaskId New mother task identifier
	*/
	function transfert($newMotherTaskId) {
		$rootTask = new EvaTask(1);
		$rootTask->load();
		$wpdbRootTask = $rootTask->convertToWpdb();
		$wpdbDaughterTask = $this->convertToWpdb();

		$motherTask = new EvaTask( $newMotherTaskId );
		$motherTask->load();
		$wpdbMotherTask = $motherTask->convertToWpdb();
		Arborescence::deplacerElements(TABLE_TACHE, $wpdbRootTask, $wpdbDaughterTask, $wpdbMotherTask);
	}

	/**
	* Get the descendants of the task.
	* @return EvaTaskTable The descendants tasks
	*/
	function getDescendants() {
		$wpdbTask = $this->convertToWpdb();
		$wpdbTasks = Arborescence::getDescendants(TABLE_TACHE, $wpdbTask, $where = 1, $order= "limiteGauche ASC");
		$descendants = new EvaTaskTable();
		$descendants->removeAllTasks();

		foreach ($wpdbTasks as $wpdbTask) {
			$task = new EvaTask();
			$task->convertWpdb($wpdbTask);
			$descendants->addTask($task);
		}

		return $descendants;
	}

	/**
	*
	*/
	function markAllSubElementAsDone($avancement, $date_debut, $date_fin)
	{
		global $current_user;

		$tacheDescendantes = $this->getDescendants();
		foreach($tacheDescendantes as $elements)
		{
			if(is_array($elements))
			{
				foreach($elements as $tache)
				{
					if(is_a($tache, 'evaTask'))
					{
						/*	For security reason we unset the subtask element	*/
						unset($subTask);

						$subTask = new EvaTask($tache->id);

						/*	Set tasks	*/
						$subTask->load();
						$subTask->setProgressionStatus('Done');
						$subTask->setProgression($avancement);
						$subTask->setStartDate($date_debut);
						$subTask->setFinishDate($date_fin);
						$subTask->setidSoldeurChef($current_user->ID);
						$subTask->setdateSolde(current_time('mysql', 0));
						$subTask->save();

						/*	Set actions	*/
						$actions = $subTask->getWPDBActivitiesDependOn();
						if( is_array($actions) && (count($actions) > 0) )
						{
							foreach($actions as $action)
							{
								unset($subAction);
								$subAction = new EvaActivity($action->id);
								$subAction->load();
								$subAction->setProgressionStatus('Done');
								$subAction->setProgression($avancement);
								$subAction->setStartDate($date_debut);
								$subAction->setFinishDate($date_fin);
								$subAction->setidSoldeurChef($current_user->ID);
								$subAction->setdateSolde(current_time('mysql', 0));
								$subAction->save();
								unset($subAction);
							}
						}

						/*	For security reason we unset the subtask element	*/
						unset($subTask);
					}
				}
			}
		}

		unset($actions);$actions = array();
		$actions = $this->getWPDBActivitiesDependOn();
		if( is_array($actions) && (count($actions) > 0) )
		{
			foreach($actions as $action)
			{
				unset($subAction);
				$subAction = new EvaActivity($action->id);
				$subAction->load();
				$subAction->setProgressionStatus('Done');
				$subAction->setProgression($avancement);
				$subAction->setStartDate($date_debut);
				$subAction->setFinishDate($date_fin);
				$subAction->setidSoldeurChef($current_user->ID);
				$subAction->setdateSolde(current_time('mysql', 0));
				$subAction->save();
				unset($subAction);
			}
		}
	}

	/**
	 * Get the level of the task in the tree.
	 * @return int The level.
	 */
	function getLevel()
	{
		$wpdbTask = $this->convertToWpdb();
		$ancestors = Arborescence::getAncetre(TABLE_TACHE, $wpdbTask);
		/* The root is not a real task.
		 * It is here to coagulate the table.
		 * So we must reduce the count by one.*/
		return count($ancestors) - 1;
	}

	/**
	 * Compute and set the Task start and finish dates
	 */
	function getTimeWindow() {
		$TasksAndSubTasks = $this->getDescendants();
		$TasksAndSubTasks->addTask($this);
		$TasksAndSubTasks = $TasksAndSubTasks->getTasks();
		if ($TasksAndSubTasks != null AND count($TasksAndSubTasks) > 0) {
			$startDate = $this->getStartDate();
			$finishDate = $this->getFinishDate();
			$real_start_date = $this->getreal_start_date();
			$real_end_date = $this->getreal_end_date();
			foreach ($TasksAndSubTasks as $task) {
				$activities = $task->getActivitiesDependOn();
				$activities = $activities->getActivities();
				if ($activities != null AND count($activities) > 0) {
					foreach ($activities as $activity) {
						if ( !is_null($activity->getreal_start_date()) && ($activity->getreal_start_date() != '0000-00-00') && !is_null($real_start_date) && ($real_start_date != '0000-00-00') ) {
							$dates_to_compare = array($activity->getreal_start_date(), $real_start_date);
							$real_start_date = min( $dates_to_compare ) ;
						}
						else if ( !is_null($activity->getreal_start_date()) && ($activity->getreal_start_date() != '0000-00-00') ) {
							$real_start_date = $activity->getreal_start_date();
						}

						if ( !is_null($activity->getreal_end_date()) && ($activity->getreal_end_date() != '0000-00-00') && !is_null($real_end_date) && ($real_end_date != '0000-00-00') ) {
							$dates_to_compare = array($activity->getreal_end_date(), $real_end_date);
							$real_end_date = max( $dates_to_compare ) ;
						}
						else if ( !is_null($activity->getreal_end_date()) && ($activity->getreal_end_date() != '0000-00-00') ) {
							$real_end_date = $activity->getreal_end_date();
						}

						if ( !is_null($activity->getStartDate()) && ($activity->getStartDate() != '0000-00-00') && !is_null($startDate) && ($startDate != '0000-00-00') ) {
							$dates_to_compare = array($activity->getStartDate(), $startDate);
							$startDate = min( $dates_to_compare ) ;
						}
						else if ( !is_null($activity->getStartDate()) && ($activity->getStartDate() != '0000-00-00') ) {
							$startDate = $activity->getStartDate();
						}

						if ( !is_null($activity->getFinishDate()) && ($activity->getFinishDate() != '0000-00-00') && !is_null($finishDate) && ($finishDate != '0000-00-00') ) {
							$dates_to_compare = array($activity->getFinishDate(), $finishDate);
							$finishDate = max( $dates_to_compare ) ;
						}
						else if ( !is_null($activity->getFinishDate()) && ($activity->getFinishDate() != '0000-00-00') ) {
							$finishDate = $activity->getFinishDate();
						}
					}
				}
			}

			$this->setreal_start_date($real_start_date);
			$this->setreal_end_date($real_end_date);

			$this->setStartDate($startDate);
			$this->setFinishDate($finishDate);
		}
	}

	/**
	* Compute and set the Task progression regarding the sub tasks
	*/
	function computeProgression() {
		global $current_user;

		$TasksAndSubTasks = $this->getDescendants();
		$TasksAndSubTasks->addTask($this);
		$TasksAndSubTasks = $TasksAndSubTasks->getTasks();

		$taskDuration = $taskCompleteDuration = 0;
		$totalProgression = $totalSubTask = 0;
		$totalPlannedTime= $totalEstimatedCost = $totalElapsedTime= $totalRealCost = 0;
		$progressionStatusToSet = $this->getProgressionStatus();
		if (($TasksAndSubTasks != null) && (count($TasksAndSubTasks) > 0)) {
			foreach($TasksAndSubTasks as $task) {
				$activities = $task->getActivitiesDependOn();
				$activities = $activities->getActivities();
				if ($activities != null AND count($activities) > 0) {
					foreach ($activities as $activity) {
						if (($progressionStatusToSet != 'inProgress') && ($activity->getProgressionStatus() == 'inProgress')) {
							$progressionStatusToSet = 'inProgress';
						}
						$totalProgression += $activity->getProgression();

						$totalPlannedTime += $activity->getplanned_time();
						$totalElapsedTime += $activity->getelapsed_time();
						$totalEstimatedCost += $activity->getcout();
						$totalRealCost += $activity->getcout_reel();

						$totalSubTask++;
					}
				}
				else {
					$this->setProgression(0);
				}
			}
		}

		$this->setplanned_time( $totalPlannedTime );
		$this->setelapsed_time( $totalElapsedTime );
		$this->setCost( $totalEstimatedCost );
		$this->setreal_cost( $totalRealCost );

		if ($totalProgression > 0) {
			// $progressionToSet = round($taskCompleteDuration / $taskDuration * 100);
			$progressionToSet = round($totalProgression / $totalSubTask);
			$this->setProgression($progressionToSet);
			if (($progressionToSet > 0)) {
				$this->setProgressionStatus('inProgress');
			}
			if ($progressionToSet >= 100) {
				$this->setProgressionStatus('Done');
				$this->setidSoldeur($current_user->ID);
				$this->setdateSolde(current_time('mysql', 0));
			}
		}
		else if ($progressionStatusToSet == 'inProgress') {
			$this->setProgressionStatus('inProgress');
		}
	}

	/**
	 *	Make the link between a task and an element (risk/work unit/...)
	 *
	 *	@param mixed $table The element type we want to link
	 * @param integer $id The element identifier we want to link
 	 * @param mixed $listeTaches A string composed by tasks id to link separeted by a delimiter
	 *	@param mixed $momentLiaison Defines if the link is made before or after, used for the risk evaluation to know risk level before and after an action
	 */
	function liaisonTacheElement($table, $id, $listeTaches, $momentLiaison = 'before'){
		global $wpdb;
		$actionsList = "  ";

		$actions = explode('_ac_', $listeTaches);
		foreach($actions as $actionIndex => $action){
			$action_details = explode('_aceff_', $action);
			if($action_details[0] > 0){
				$actionsList .= "('', 'valid', '" . $momentLiaison . "', '" . current_time('mysql', 0) . "', '" . $action_details[0] . "', '" . $id . "', '" . $table . "'), ";
			}
		}

		$actionsList = trim(substr($actionsList, 0, -2));
		if($actionsList != ''){
			$query = $wpdb->prepare(
				"REPLACE INTO " . TABLE_LIAISON_TACHE_ELEMENT . "
					(id, status, wasLinked, date, id_tache, id_element, table_element)
				VALUES
					 " . $actionsList, array() );
			$wpdb->query($query);
		}
	}
	/**
	*	Get associated element for a given task
	*
	* @param integer $ask_id The task identifier we want to get the link for
	*
	*	@return mixed $links If there are result contains a wordpress database object with the different existing link list
	*/
	function get_element_link_to_task($task_id){
		global $wpdb;
		$links = '';

		$query = $wpdb->prepare("
			SELECT *
			FROM " . TABLE_LIAISON_TACHE_ELEMENT . "
			WHERE id_tache = %d
				AND status = 'valid' ", $task_id);
		$links = $wpdb->get_results($query);

		return $links;
	}

	/**
	*	Create a new task from a set of value send by a form
	*
	*	@return integer The new task identifier
	*/
	function saveNewTask() {
		$tache = new EvaTask();

		$tache->setName($_POST['nom_activite']);
		$tache->setDescription($_POST['description']);
		$tache->setCost($_POST['cout']);
		$tache->setIdFrom($_POST['idProvenance']);
		$tache->setTableFrom($_POST['tableProvenance']);
		$tache->setidResponsable($_POST['responsable_activite']);
		$tache->setProgressionStatus('notStarted');
		$tache->setnom_exportable_plan_action(!empty($_POST['nom_exportable_plan_action'])?$_POST['nom_exportable_plan_action']:'no');
		$tache->setdescription_exportable_plan_action(!empty($_POST['description_exportable_plan_action'])?$_POST['description_exportable_plan_action']:'no');

		if(isset($_POST['correctiv_action_efficiency_control']))
			$tache->setEfficacite($_POST['correctiv_action_efficiency_control']);

		if($_POST['avancement'] > '0')
			$tache->setProgressionStatus('inProgress');


		if(isset($_POST['hasPriority']))
			$tache->sethasPriority($_POST['hasPriority']);
		else
			$tache->sethasPriority('no');

		if($_POST['avancement'] == '100'){
			$tache->setProgressionStatus('Done');
			global $current_user;
			$tache->setidSoldeur($current_user->ID);
		}

		$tache->setStartDate($_POST['date_debut']);
		$tache->setFinishDate($_POST['date_fin']);
		$tache->save();

		$options = get_option('digirisk_options');
		$task_to_take = (!empty($options['digi_ac_control_action_affectation'])) ? $options['digi_ac_control_action_affectation'] : 1;
		$tache->load();
		$tache->transfert( (int)$task_to_take );
		$tache->load();

		return $tache->getId();
	}

	/**
	*	Return the content of box displayed on the dashboard to get information on the different correctiv actions
	*
	*	@param mixed $dashbordParam The type of summary we want to output
	*
	*	@param mixed $acDashboardBox The html content of the box
	*/
	function getTaskForDashBoard($dashbordParam){
		require_once(EVA_LIB_PLUGIN_DIR . 'evaDisplayDesign.class.php');
		unset($lignesDeValeurs);
		global $wpdb;
		$moreScript = $acDashboardBox = '';
		$outputDatas = true;

		switch($dashbordParam)
		{
			case 'passed':
			{
				$query = $wpdb->prepare(
					"SELECT *
					FROM " . TABLE_TACHE . "
					WHERE Status = 'Valid'
						AND dateFin <= CURDATE()
						AND dateFin != '0000-00-00'
						AND ProgressionStatus IN ('inProgress', 'notStarted')
					ORDER BY dateFin DESC", ""
				);
				$taskList = $wpdb->get_results($query);

				$idTable = 'taskListPassedButNotMarkAsDone';
				$titres = array( __('Id', 'evarisk'),  __('Nom T&acirc;che', 'evarisk'), __('D&eacute;but', 'evarisk'), __('Fin', 'evarisk'), __('Progression (%)', 'evarisk'), __('Fiche', 'evarisk') );
				if(is_array($taskList) && (count($taskList) > 0)){
					foreach($taskList as $task){
						unset($valeurs);
						$valeurs[] = array('value'=>ELEMENT_IDENTIFIER_T . $task->id);
						$valeurs[] = array('value'=>$task->nom);
						$valeurs[] = array('value'=>$task->dateDebut);
						$valeurs[] = array('value'=>$task->dateFin);
						$valeurs[] = array('value'=>$task->avancement);
						$valeurs[] = array('value'=>'<a href="' . get_bloginfo('siteurl') . '/wp-admin/admin.php?page=digirisk_correctiv_actions&amp;elt=edit-node' . $task->id . '" target="seePassedFac" >' . __('Voir l\'action', 'evarisk') . '</a>');
						$lignesDeValeurs[] = $valeurs;
						$idLignes[] = 'taskListPassedButNotMarkedAsDone' . $task->id;
					}
				}
				else{
					unset($valeurs);
					$valeurs[] = array('value'=>'');
					$valeurs[] = array('value'=>__('Aucun r&eacute;sultat trouv&eacute;', 'evarisk'));
					$valeurs[] = array('value'=>'');
					$valeurs[] = array('value'=>'');
					$valeurs[] = array('value'=>'');
					$valeurs[] = array('value'=>'');
					$lignesDeValeurs[] = $valeurs;
					$idLignes[] = 'taskListPassedButNotMarkAsDoneEmpty';
					$acDashboardBox .= '<script type="text/javascript" >digirisk("#evaDashboard_ac_passed").remove();</script>';
					$outputDatas = false;
				}

				$classes = array('cbColumnLarge','','','cbNbUserGroup');
				$tableOptions = '';
			}
			break;
			case 'taskToMarkAsDone':
			{
				$query = $wpdb->prepare(
					"SELECT TASK.*
					FROM " . TABLE_TACHE . " AS TASK
					WHERE TASK.Status = 'Valid'
						AND TASK.dateFin >= CURDATE()
						AND TASK.dateFin != '0000-00-00'
						AND TASK.ProgressionStatus = 'inProgress'
						AND TASK.id != '1'
					ORDER BY TASK.dateFin DESC", ""
				);
				$taskList = $wpdb->get_results($query);
				foreach($taskList as $taskIndex => $task){
					$query = $wpdb->prepare(
						"SELECT ACTION.id, ACTION.id_tache, ACTION.ProgressionStatus
						FROM " . TABLE_ACTIVITE . " AS ACTION
						WHERE ACTION.id_tache = '%d'
						",
						$task->id
					);
					$actionList = $wpdb->get_results($query);
					foreach($actionList as $action){
						if($action->ProgressionStatus == 'inProgress'){
							unset($taskList[$taskIndex]);
						}
					}
				}

				$idTable = 'taskListToMarkAsDone';
				$titres = array( __('Id', 'evarisk'), __('Nom T&acirc;che', 'evarisk'), __('D&eacute;but', 'evarisk'), __('Fin', 'evarisk'), __('Progression (%)', 'evarisk'), __('Fiche', 'evarisk') );
				if(is_array($taskList) && (count($taskList) > 0)){
					foreach($taskList as $task){
						unset($valeurs);
						$valeurs[] = array('value'=>ELEMENT_IDENTIFIER_T . $task->id);
						$valeurs[] = array('value'=>$task->nom);
						$valeurs[] = array('value'=>$task->dateDebut);
						$valeurs[] = array('value'=>$task->dateFin);
						$valeurs[] = array('value'=>$task->avancement);
						$valeurs[] = array('value'=>'<a href="' . get_bloginfo('siteurl') . '/wp-admin/admin.php?page=digirisk_correctiv_actions&amp;elt=edit-node' . $task->id . '" target="seePassedFac" >' . __('Voir l\'action', 'evarisk') . '</a>');
						$lignesDeValeurs[] = $valeurs;
					$idLignes[] = 'taskListToMarkAsDone' . $task->id;
					}
				}
				else{
					unset($valeurs);
					$valeurs[] = array('value'=>'');
					$valeurs[] = array('value'=>__('Aucun r&eacute;sultat trouv&eacute;', 'evarisk'));
					$valeurs[] = array('value'=>'');
					$valeurs[] = array('value'=>'');
					$valeurs[] = array('value'=>'');
					$valeurs[] = array('value'=>'');
					$lignesDeValeurs[] = $valeurs;
					$idLignes[] = 'taskListToMarkAsDoneEmpty';
					$acDashboardBox .= '<script type="text/javascript" >digirisk("#evaDashboard_task_toDone").remove();</script>';
					$outputDatas = false;
				}

				$classes = array('cbColumnLarge','','','cbNbUserGroup');
				$tableOptions = '';
			}
			break;
			case 'toEvaluate':
			{
				$idTable = 'riskListToReEavaluate';
				$query = $wpdb->prepare(
					"SELECT RISK.*, DANGER.nom AS nomDanger
					FROM " . TABLE_RISQUE . " AS RISK
						INNER JOIN " . TABLE_AVOIR_VALEUR . " AS RISKEVAL ON ((RISKEVAL.id_risque = RISK.id) AND (RISKEVAL.Status = 'Valid'))
						INNER JOIN " . TABLE_LIAISON_TACHE_ELEMENT . " AS LTE ON (LTE.id_element = RISKEVAL.id_evaluation)

						INNER JOIN " . TABLE_DANGER . " AS DANGER ON (DANGER.id = RISK.id_danger)
					WHERE LTE.table_element = '" . TABLE_AVOIR_VALEUR . "'
						AND LTE.wasLinked = 'after'
						AND LTE.status = 'valid'
					GROUP BY RISK.id ", ""
				);
				$riskList = $wpdb->get_results($query);

				$titres = array(__('Id', 'evarisk'), __('Nom danger', 'evarisk'), __('Commentaire sur le risque', 'evarisk'), __('&Eacute;l&eacute;ment', 'evarisk'), __('Fiche', 'evarisk') );
				if(is_array($riskList) && (count($riskList) > 0)){
					foreach($riskList as $risk){
						unset($valeurs);
						$valeurs[] = array('value'=>ELEMENT_IDENTIFIER_R . $risk->id);
						$valeurs[] = array('value'=>$risk->nomDanger);
						$valeurs[] = array('value'=>$risk->commentaire);

						$risqueAffecteA = '';
						$idGroupement = 0;
						switch($risk->nomTableElement)
						{
							case TABLE_GROUPEMENT:
								$idGroupement = $risk->id_element;
								$infos = EvaGroupement::getGroupement($idGroupement);
								$nomElementCourant = $infos->nom;
								$elementToSee = 'node-mainTable-' . $risk->id_element . '-name';
							break;
							case TABLE_UNITE_TRAVAIL:
								$query = $wpdb->prepare("SELECT nom, id_groupement FROM " . TABLE_UNITE_TRAVAIL . " WHERE id = '" . $risk->id_element . "' ", "");
								$infos = $wpdb->get_row($query);
								$idGroupement = $infos->id_groupement;
								$nomElementCourant = $infos->nom;
								$elementToSee = 'leaf-' . $risk->id_element . '-name';
							break;
							default:
								$risqueAffecteA .= __('Il vous faut ajouter le cas ' . $risk->nomTableElement . ' dans le fichier ' . __FILE__ . ' &agrave; la ligne ' . __LINE__ . ' pour avoir les bonnes informations', 'evarisk');
								$nomElementCourant = $elementToSee = '';
							break;
						}
						if($idGroupement > 0){
							$groupementPere = EvaGroupement::getGroupement($idGroupement);
							$ancetres = Arborescence::getAncetre(TABLE_GROUPEMENT, $groupementPere);
							foreach($ancetres as $ancetre){
								if($ancetre->nom != "Groupement Racine"){
									$risqueAffecteA .= $ancetre->nom . ' &raquo; ';
								}
							}
						}
						$completeName = $risqueAffecteA;
						if($groupementPere->nom != $nomElementCourant){
							$completeName .= $groupementPere->nom . ' &raquo; ';
						}
						$valeurs[] = array('value'=>$completeName . $nomElementCourant);
						$valeurs[] = array('value'=>'<a href="' . get_bloginfo('siteurl') . '/wp-admin/admin.php?page=digirisk_risk_evaluation&amp;elt=' . $elementToSee . '&amp;risk=risque-' . $risk->id . '-edit" target="seeRiskToEvaluate" >' . __('Voir le risque', 'evarisk') . '</a>');
						$lignesDeValeurs[] = $valeurs;
						$idLignes[] = 'taskListToReEvaluateRisk' . $risk->id;
					}
				}
				else{
					unset($valeurs);
					$valeurs[] = array('value'=>__('Aucun r&eacute;sultat trouv&eacute;', 'evarisk'));
					$valeurs[] = array('value'=>'');
					$valeurs[] = array('value'=>'');
					$valeurs[] = array('value'=>'');
					$lignesDeValeurs[] = $valeurs;
					$idLignes[] = 'taskListToReEvaluateRiskEmpty';
					$acDashboardBox .= '<script type="text/javascript" >digirisk("#evaDashboard_ac_done").remove();</script>';
					$outputDatas = false;
				}

				$classes = array('cbColumnLarge','','','','');
				$tableOptions = '';
			}
			break;
			default:
				$taskList = sprintf(__('Vous devez d&eacute;clarer %s dans le fichier %s &agrave; la ligne %d si vous voulez cette information', 'evarisk'), $dashbordParam, __FILE__, __LINE__);
				$outputDatas = false;
			break;
		}

		if($outputDatas){
			$script =
			'<script type="text/javascript">
				digirisk(document).ready(function() {
					digirisk("#' . $idTable . '").dataTable({
						"bInfo": false,
						"oLanguage": {
							"sSearch": "<span class=\'ui-icon searchDataTableIcon\' >&nbsp;</span>",
							"sEmptyTable": "' . __('Aucune action trouv&eacute;e', 'evarisk') . '",
							"sLengthMenu": "' . __('Afficher _MENU_ actions', 'evarisk') . '",
							"sInfoEmpty": "' . __('Aucune action', 'evarisk') . '",
							"sZeroRecords": "' . __('Aucune action trouv&eacute;e', 'evarisk') . '",
							"oPaginate": {
								"sFirst": "' . __('Premi&eacute;re', 'evarisk') . '",
								"sLast": "' . __('Derni&egrave;re', 'evarisk') . '",
								"sNext": "' . __('Suivante', 'evarisk') . '",
								"sPrevious": "' . __('Pr&eacute;c&eacute;dente', 'evarisk') . '"
							}
						}
						' . $tableOptions . '
					});
					digirisk("#' . $idTable . '").children("tfoot").remove();
					digirisk("#' . $idTable . '_wrapper").removeClass("dataTables_wrapper");
				});
			</script>';
			$acDashboardBox .= evaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script);
		}

		return $acDashboardBox;
	}


	/**
	* Get the priority taks added to a risk
	*
	*	@param mixed $tableElement The element type we want to get the priority task for
	* @param integer $idElement The element identifier we want to get the priority task for
	*
	*	@return object A wordpress database object with the task identifier
	*/
	function getPriorityTask($tableElement, $idElement){
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT id
			FROM " . TABLE_TACHE . "
			WHERE tableProvenance = '%s'
				AND idProvenance = '%d'
				AND hasPriority = 'yes'
				AND Status = 'Valid'",
		$tableElement, $idElement);

		return $wpdb->get_results($query);
	}


	/**
	* Returns all working unit belonging to the group witch is identifier or belonging to his descendants
	*
	*	@see trash_utilities
	*
	* @param int $elementId The group identifier
	* @param string $where The SQL where condition
	* @param string $order The SQL order condition
	*
	* @return the working units  belonging to the group witch is identifier
	*/
	function getChildren($elementId, $where = "1", $order="nom ASC"){
		global $wpdb;

		$element = new EvaTask();
		$element->setId($elementId);
		$element->load();
		$element->limiteGauche = $element->leftLimit;
		$element->limiteDroite = $element->rightLimit;
		$subElements = Arborescence::getDescendants(TABLE_TACHE, $element);
		unset($tabId);
		$tabId[] = $elementId;
		foreach($subElements as $subElement){
			$tabId[] = $subElement->id;
		}
		$resultat = $wpdb->get_results( "SELECT * FROM " . TABLE_ACTIVITE . " WHERE id_tache in (" . implode(', ', $tabId) . ") AND " . $where . " ORDER BY ". $order);

		return $resultat;
	}


	function save_task_from_risk_form($preconisationRisqueTitle, $preconisationRisque, $idRisque) {
		global $wpdb;
		$_POST['nom_activite'] = !empty($preconisationRisqueTitle) ? $preconisationRisqueTitle : substr(nl2br($preconisationRisque), 0, 255);
		$_POST['description'] = $preconisationRisque;
		$_POST['print_action_description_in_duer'] = $preconisationRisque;
		$_POST['cout'] = '';
		$_POST['idProvenance'] = $idRisque;
		$_POST['tableProvenance'] = TABLE_RISQUE;
		$_POST['responsable_activite'] = '';
		$_POST['date_debut'] = date('Y-m-d');
		$_POST['date_fin'] = date('Y-m-d', mktime(0, 0, 0, date("m")+1, date("d"), date("Y")));
		$_POST['avancement'] = '0';
		$_POST['nom_exportable_plan_action'] = 'yes';
		$_POST['description_exportable_plan_action'] = 'yes';
		$_POST['hasPriority'] = 'yes';

		/*	Make the link between a corrective action and a risk evaluation	*/
		$query = $wpdb->prepare(
				"SELECT id_evaluation
			FROM " . TABLE_AVOIR_VALEUR . "
			WHERE id_risque = '%d'
				AND Status = 'Valid'
			ORDER BY id DESC
			LIMIT 1",
				$idRisque
		);
		$evaluation = $wpdb->get_row($query);
		$_POST['parentTaskId'] = evaTask::saveNewTask();
		evaTask::liaisonTacheElement(TABLE_AVOIR_VALEUR, $evaluation->id_evaluation, $_POST['parentTaskId'], 'demand');

		/*	Create automatically a sub-task for the priority task	*/
		evaActivity::saveNewActivity();
	}

}