<?php
/**
 * This class allows to work on many activities (equivalent to many rows in data base)
 *
 * @author Evarisk
 * @version v5.0
 */
require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/activite/evaBaseActivity.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/activite/evaActivity.class.php');

class EvaActivityTable extends evaBaseActivity
{
	/**
	 * @var array Collection of EvaActivity object indexed by identifier.
	 */
	var $activities;

/*
 *	Constructeur et accesseurs
 */
	/**
	 * Constructor of the EvaActivityTable class
	 * @param array $activities Collection of EvaActivity object indexed by identifier.
	 */
	function EvaActivityTable($activities = null) {
		$this->activities = $activities;
		unset($this->id);
		unset($this->relatedTaskId);
		unset($this->name);
		unset($this->description);
		unset($this->startDate);
		unset($this->finishDate);
		unset($this->progression);
		unset($this->cout);
		unset($this->place);
		unset($this->firstInsert);
		unset($this->idCreateur);
		unset($this->idResponsable);
		unset($this->idSoldeur);
		unset($this->idSoldeurChef);
		unset($this->ProgressionStatus);
		unset($this->dateSolde);
		unset($this->idPhotoAvant);
		unset($this->idPhotoApres);
		unset($this->nom_exportable_plan_action);
		unset($this->description_exportable_plan_action);
		unset($this->planned_time);
		unset($this->cout_reel);
		unset($this->elapsed_time);
		unset($this->real_start_date);
		unset($this->real_end_date);
	}

	/**
	 * Returns the collection of activities
	 * @return array The collection of activities indexed by their identifier
	 */
	function getActivities() {
		return $this->activities;
	}

	/**
	 * Add a activity to the collection
	 * @param EvaActivity Activity to add to the collection
	 */
	function addActivity($activity)
	{
		$this->activities[$activity->getId()] = $activity;
	}

	/**
	 * Remove a activity from the collection
	 * @param EvaActivity Activity to remove from the collection
	 * @return EvaActivity The activity removed
	 */
	function removeActivity($activityId)
	{
		$activity = $this->activities[$activityId];
		unset($this->activities[$activityId]);
		return $activity;
	}

	/**
	 * Remove all activities from the collection
	 */
	function removeAllActivities()
	{
		unset($this->activities);
	}

	/**
	 * Get the activities like the one use to call (id and left and right limit are non use because they are unique)
	 * @param EvaActivity Activity use to compare
	 */
	function getActivitiesLike($activity)
	{
		global $wpdb;

		$this->removeAllActivities();

		{//Variables cleaning
			$name = digirisk_tools::IsValid_Variable($activity->getName());
			$relatedTaskId = (int) digirisk_tools::IsValid_Variable($activity->getRelatedTaskId());
			$description = digirisk_tools::IsValid_Variable($activity->getDescription());
			$startDate = digirisk_tools::IsValid_Variable($activity->getStartDate());
			$finishDate = digirisk_tools::IsValid_Variable($activity->getFinishDate());
			$place = digirisk_tools::IsValid_Variable($activity->getPlace());
			$cout = (int) digirisk_tools::IsValid_Variable($activity->getCout());
			$progression = (int) digirisk_tools::IsValid_Variable($activity->getProgression());
			$status = digirisk_tools::IsValid_Variable($activity->getStatus());
			$idCreateur = digirisk_tools::IsValid_Variable($activity->getidCreateur());
			$idResponsable = digirisk_tools::IsValid_Variable($activity->getidResponsable());
			$idSoldeur = digirisk_tools::IsValid_Variable($activity->getidSoldeur());
			$idSoldeurChef = digirisk_tools::IsValid_Variable($activity->getidSoldeurChef());
			$idPhotoAvant = digirisk_tools::IsValid_Variable($activity->getidPhotoAvant());
			$idPhotoApres = digirisk_tools::IsValid_Variable($activity->getidPhotoApres());
			$ProgressionStatus = digirisk_tools::IsValid_Variable($activity->ProgressionStatus());
			$nom_exportable_plan_action = digirisk_tools::IsValid_Variable($activity->getnom_exportable_plan_action());
			$description_exportable_plan_action = digirisk_tools::IsValid_Variable($activity->getdescription_exportable_plan_action());
			$dateSolde = digirisk_tools::IsValid_Variable($activity->dateSolde());
			$cout_reel = digirisk_tools::IsValid_Variable($activity->getcout_reel());
			$planned_time = digirisk_tools::IsValid_Variable($activity->getplanned_time());
			$elapsed_time = digirisk_tools::IsValid_Variable($activity->getelapsed_time());
			$real_start_date = digirisk_tools::IsValid_Variable($activity->getreal_start_date());
			$real_end_date = digirisk_tools::IsValid_Variable($activity->getreal_end_date());
		}
		{//Query creation
			$sql = "SELECT * FROM " . TABLE_TACHE . " WHERE 1";
			if($name != '')
			{
				$sql = $sql . " AND " . self::name . " = '" . ($name) . "'";
			}
			if($relatedTaskId != 0)
			{
				$sql = $sql . " AND " . self::relatedTaskId . " = " . ($relatedTaskId);
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
			if($cout != '')
			{
				$sql = $sql . " AND " . self::cout . " = '" . ($cout) . "'";
			}
			if($progression != 0)
			{
				$sql = $sql . " AND " . self::progression . " = " . ($progression);
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
        $sql = $sql . " AND " . self::ProgressionStatus . " = '" . ($ProgressionStatus) . "'";
      }
			if($dateSolde != '')
      {
        $sql = $sql . " AND " . self::dateSolde . " = '" . ($dateSolde) . "'";
      }
			if($idPhotoAvant != '')
      {
        $sql = $sql . " AND " . self::idPhotoAvant . " = '" . ($idPhotoAvant) . "'";
      }
			if($idPhotoApres != '')
      {
        $sql = $sql . " AND " . self::idPhotoApres . " = '" . ($idPhotoApres) . "'";
      }
			if($nom_exportable_plan_action != '')
      {
        $sql = $sql . " AND " . self::nom_exportable_plan_action . " = '" . ($nom_exportable_plan_action) . "'";
      }
			if($description_exportable_plan_action != '')
      {
        $sql = $sql . " AND " . self::description_exportable_plan_action . " = '" . ($description_exportable_plan_action) . "'";
      }
			if($cout_reel != '')
		      {
		        $sql = $sql . " AND " . self::cout_reel . " = '" . ($cout_reel) . "'";
		      }
			if($planned_time != '')
		      {
		        $sql = $sql . " AND " . self::planned_time . " = '" . ($planned_time) . "'";
		      }
			if($elapsed_time != '')
		      {
		        $sql = $sql . " AND " . self::elapsed_time . " = '" . ($elapsed_time) . "'";
		      }

			if($real_start_date != '')
		      {
		        $sql = $sql . " AND " . self::real_start_date . " = '" . ($real_start_date) . "'";
		      }
			if($real_end_date != '')
		      {
		        $sql = $sql . " AND " . self::real_end_date . " = '" . ($real_end_date) . "'";
		      }
		}

		$wpdbActivities = $wpdb->get_results($sql);

		foreach($wpdbActivities as $wpdbActivity)
		{
			$activity = new EvaActivity();
			$activity->convertWpdb($wpdbActivity);
			$this->addActivity($activity);
		}
	}
}