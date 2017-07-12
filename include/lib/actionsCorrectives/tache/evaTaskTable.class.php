<?php
/**
 * This class allows to work on many tasks (equivalent to many rows in data base)
 *
 * @author Evarisk
 * @version v5.0
 */
require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/tache/evaBaseTask.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/tache/evaTask.class.php');

class EvaTaskTable extends EvaBaseTask
{
	/**
	 * @var array Collection of evaTask object indexed by identifier.
	 */
	var $tasks;

/*
 *	Constructeur et accesseurs
 */
	/**
	 * Constructor of the EvaTaskTable class
	 * @param array $tasks Collection of evaTask object indexed by identifier.
	 */
	function EvaTaskTable($tasks = array()){
		$this->tasks = $tasks;
		unset($this->id);
		unset($this->leftLimit);
		unset($this->rightLimit);
		unset($this->name);
		unset($this->description);
		unset($this->startDate);
		unset($this->finishDate);
		unset($this->progression);
		unset($this->cost);
		unset($this->place);
		unset($this->idFrom);
		unset($this->tableFrom);
		unset($this->firstInsert);
		unset($this->idCreateur);
		unset($this->idResponsable);
		unset($this->idSoldeur);
		unset($this->idSoldeurChef);
		unset($this->ProgressionStatus);
		unset($this->dateSolde);
		unset($this->hasPriority);
		unset($this->efficacite);
		unset($this->idPhotoAvant);
		unset($this->idPhotoApres);
		unset($this->is_readable_from_external);
		unset($this->nom_exportable_plan_action);
		unset($this->description_exportable_plan_action);
		unset($this->real_start_date);
		unset($this->real_end_date);
		unset($this->estimate_cost);
		unset($this->real_cost);
		unset($this->planned_time);
		unset($this->elapsed_time);
	}

	/**
	 * Returns the collection of tasks
	 * @return array The collection of tasks indexed by their identifier
	 */
	function getTasks()
	{
		return $this->tasks;
	}

	/**
	 * Add a task to the collection
	 * @param EvaTask Task to add to the collection
	 */
	function addTask($task)
	{
		$this->tasks[$task->getId()] = $task;
	}

	/**
	 * Remove a task from the collection
	 * @param EvaTask Task to remove from the collection
	 * @return EvaTask The task removed
	 */
	function removeTask($taskId)
	{
		$task = $this->tasks[$taskId];
		unset($this->tasks[$taskId]);
		return $task;
	}

	/**
	 * Remove all tasks from the collection
	 */
	function removeAllTasks()
	{
		unset($this->tasks);
		$this->tasks = array();
	}

/*
 * Others methods
 */
  /**
	 * Get the tasks like the one in parameter (id and left and right limit are non use because they are unique)
	 * @param EvaTask Task use to compare
	 */
	function getTasksLike($task)
	{
		global $wpdb;

		$this->removeAllTasks();

		{//Variables cleaning
			$name = digirisk_tools::IsValid_Variable($task->getName());
			$description = digirisk_tools::IsValid_Variable($task->getDescription());
			$startDate = digirisk_tools::IsValid_Variable($task->getStartDate());
			$finishDate = digirisk_tools::IsValid_Variable($task->getFinishDate());
			$place = digirisk_tools::IsValid_Variable($task->getPlace());
			$progression = (int) digirisk_tools::IsValid_Variable($task->getProgression());
			$idFrom = (int) digirisk_tools::IsValid_Variable($task->getIdFrom());
			$tableFrom = digirisk_tools::IsValid_Variable($task->getTableFrom());
			$status = digirisk_tools::IsValid_Variable($task->getStatus());
			$idCreateur = digirisk_tools::IsValid_Variable($task->getidCreateur());
			$idResponsable = digirisk_tools::IsValid_Variable($task->getidResponsable());
			$idSoldeur = digirisk_tools::IsValid_Variable($task->getidSoldeur());
			$idSoldeurChef = digirisk_tools::IsValid_Variable($task->getidSoldeurChef());
			$idPhotoAvant = digirisk_tools::IsValid_Variable($task->getidPhotoAvant());
			$idPhotoApres = digirisk_tools::IsValid_Variable($task->getidPhotoApres());
			$ProgressionStatus = digirisk_tools::IsValid_Variable($task->getProgressionStatus());
			$dateSolde = digirisk_tools::IsValid_Variable($task->getdateSolde());
			$hasPriority = digirisk_tools::IsValid_Variable($task->gethasPriority());
			$efficacite = digirisk_tools::IsValid_Variable($task->getEfficacite());
			$is_readable_from_external = digirisk_tools::IsValid_Variable($task->get_external_readable());
			$nom_exportable_plan_action = digirisk_tools::IsValid_Variable($task->getnom_exportable_plan_action());
			$description_exportable_plan_action = digirisk_tools::IsValid_Variable($task->getdescription_exportable_plan_action());
			$real_start_date = digirisk_tools::IsValid_Variable($task->getreal_start_date());
			$real_end_date = digirisk_tools::IsValid_Variable($task->getreal_end_date());
			$estimate_cost = digirisk_tools::IsValid_Variable($task->getestimate_cost());
			$real_cost = digirisk_tools::IsValid_Variable($task->getreal_cost());
			$planned_time = digirisk_tools::IsValid_Variable($task->getplanned_time());
			$elapsed_time = digirisk_tools::IsValid_Variable($task->getelapsed_time());
		}
    {//Query creation
      $sql = "SELECT * FROM " . TABLE_TACHE . " WHERE 1";
      if($name != '')
      {
        $sql = $sql . " AND " . self::name . " = '" . ($name) . "'";
      }
      if($description != '')
      {
        $sql = $sql . " AND " . self::description . " = '" . ($description) . "'";
      }
      if($startDate != '' && $startDate != '0000-00-00')
      {
        $sql = $sql . " AND " . self::startDate . " = '" . ($startDate) . "'";
      }
      if($finishDate != '' && $finishDate != '0000-00-00')
      {
        $sql = $sql . " AND " . self::finishDate . " = '" . ($finishDate) . "'";
      }
      if($place != '')
      {
        $sql = $sql . " AND " . self::place . " = '" . ($place) . "'";
      }
      if($progression != 0)
      {
        $sql = $sql . " AND " . self::progression . " = " . ($progression);
      }
      if($idFrom != 0)
      {
        $sql = $sql . " AND " . self::idFrom . " = " . ($idFrom);
      }
      if($tableFrom != '')
      {
        $sql = $sql . " AND " . self::tableFrom . " = '" . ($tableFrom) . "'";
      }
      if($status != '')
      {
        $sql = $sql . " AND " . self::status . " = '" . ($status) . "'";
      }
	  if($idCreateur != '')
      {
        $sql = $sql . " AND " . self::idCreateur . " = '" . ($idCreateur) . "'";
      }
	  if($idSoldeur != '')
      {
        $sql = $sql . " AND " . self::idSoldeur . " = '" . ($idSoldeur) . "'";
      }
	  if($idSoldeurChef != '')
      {
        $sql = $sql . " AND " . self::idSoldeurChef . " = '" . ($idSoldeurChef) . "'";
      }
	  if($idResponsable != '')
      {
        $sql = $sql . " AND " . self::idResponsable . " = '" . ($idResponsable) . "'";
      }
	  if($ProgressionStatus != '')
      {
        $sql = $sql . " AND " . self::ProgressionStatus . " IN (" . ($ProgressionStatus) . ") ";
      }
	  if($dateSolde != '')
      {
        $sql = $sql . " AND " . self::dateSolde . " = '" . ($dateSolde) . "'";
      }
	  if($hasPriority != '')
      {
        $sql = $sql . " AND " . self::hasPriority . " = '" . ($hasPriority) . "'";
      }
	  if($efficacite != '')
      {
        $sql = $sql . " AND " . self::efficacite . " = '" . ($efficacite) . "'";
      }
	  if( !empty($idPhotoAvant) )
      {
        $sql = $sql . " AND " . self::idPhotoAvant . " = '" . ($idPhotoAvant) . "'";
      }
	  if( !empty($idPhotoAvant) )
      {
        $sql = $sql . " AND " . self::idPhotoApres . " = '" . ($idPhotoApres) . "'";
      }
	  if($is_readable_from_external != '')
      {
        $sql = $sql . " AND " . self::is_readable_from_external . " = '" . ($is_readable_from_external) . "'";
      }
	  if($nom_exportable_plan_action != '')
      {
        $sql = $sql . " AND " . self::nom_exportable_plan_action . " = '" . ($nom_exportable_plan_action) . "'";
      }
	  if($description_exportable_plan_action != '')
      {
        $sql = $sql . " AND " . self::description_exportable_plan_action . " = '" . ($description_exportable_plan_action) . "'";
      }

	  if($real_start_date != '')
      {
        $sql = $sql . " AND " . self::real_start_date . " = '" . ($real_start_date) . "'";
      }
	  if($real_end_date != '')
      {
        $sql = $sql . " AND " . self::real_end_date . " = '" . ($real_end_date) . "'";
      }
	  if($estimate_cost != '')
      {
        $sql = $sql . " AND " . self::estimate_cost . " = '" . ($estimate_cost) . "'";
      }
	  if($real_cost != '')
      {
        $sql = $sql . " AND " . self::real_cost . " = '" . ($description_exportable_plan_action) . "'";
      }
	  if($planned_time != '')
      {
        $sql = $sql . " AND " . self::planned_time . " = '" . ($planned_time) . "'";
      }
	  if($elapsed_time != '')
      {
        $sql = $sql . " AND " . self::elapsed_time . " = '" . ($elapsed_time) . "'";
      }
    }

    $wpdbTasks = $wpdb->get_results($sql);

    foreach($wpdbTasks as $wpdbTask)
    {
      $task = new EvaTask();
      $task->convertWpdb($wpdbTask);
      $this->addTask($task);
    }
	}
}