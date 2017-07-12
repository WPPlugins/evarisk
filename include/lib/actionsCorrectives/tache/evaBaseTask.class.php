<?php
/**
 * Class to represent a task directly connect with the data base
 *
 * @author Evarisk
 * @version v5.0
 */
require_once(EVA_CONFIG);
require_once(EVA_LIB_PLUGIN_DIR . 'arborescence.class.php');

class EvaBaseTask
{
/*
 * Data base rows names
 */
	const id = 'id';
	const name = 'nom';
	const leftLimit = 'limiteGauche';
	const rightLimit = 'limiteDroite';
	const description = 'description';
	const startDate = 'dateDebut';
	const finishDate = 'dateFin';
	const place = 'lieu';
	const progression = 'avancement';
	const cost = 'cout';
	const idFrom = 'idProvenance';
	const tableFrom = 'tableProvenance';
	const status = 'Status';
	const ProgressionStatus = 'ProgressionStatus';
	const firstInsert = 'firstInsert';
	const idCreateur = 'idCreateur';
	const idResponsable = 'idResponsable';
	const idSoldeur = 'idSoldeur';
	const idSoldeurChef = 'idSoldeurChef';
	const dateSolde = 'dateSolde';
	const hasPriority = 'hasPriority';
	const efficacite = 'efficacite';
	const idPhotoAvant = 'idPhotoAvant';
	const idPhotoApres = 'idPhotoApres';
	const is_readable_from_external = 'is_readable_from_external';
	const nom_exportable_plan_action = 'nom_exportable_plan_action';
	const description_exportable_plan_action = 'description_exportable_plan_action';
	const real_start_date = 'real_start_date';
	const real_end_date = 'real_end_date';
	const estimate_cost = 'estimate_cost';
	const real_cost = 'real_cost';
	const planned_time = 'planned_time';
	const elapsed_time = 'elapsed_time';
/*
 * Class variable define
 */

	/**
	 * @var int The task identifier
	 */
	var $id;
	/**
	 * @var string The task name
	 */
	var $name;
	/**
	 * @var int The task Left limit to simulate the tree
	 */
	var $leftLimit;
	/**
	 * @var int The task Right limit to simulate the tree
	 */
	var $rightLimit;
	/**
	 * @var string The task description
	 */
	var $description;
	/**
	 * @var date The task start date
	 */
	var $startDate;
	/**
	 * @var date The task finish date
	 */
	var $finishDate;
	/**
	 * @var string The task place
	 */
	var $place;
	/**
	 * @var string The task progression
	 */
	var $progression;
	/**
	 * @var string The task cost
	 */
	var $cost;
	/**
	 * @var int The identifier of the element that induces the task
	 */
	var $idFrom;
	/**
	 * @var string The table of the element that induces the task
	 */
	var $tableFrom;
	/**
	 * @var string The task status
	 */
	var $status;
	/**
	 * @var string The task progression status
	 */
	var $ProgressionStatus;
	/**
	 * @var string The task insert date
	 */
	var $firstInsert;
	/**
	 * @var string The task creator id
	 */
	var $idCreateur;
	/**
	 * @var string The person in charge of the task
	 */
	var $idResponsable;
	/**
	 * @var string The person who says that the task is done
	 */
	var $idSoldeur;
	/**
	 * @var string The master chief who says taht the task is done
	 */
	var $idSoldeurChef;
	/**
	 * @var string The date tha task was marked as done
	 */
	var $dateSolde;
	/**
	 * @var string The task priority status
	 */
	var $hasPriority;
	/**
	 * @var string The task efficiency
	 */
	var $efficacite;
	/**
	 * @var string picture before action
	 */
	var $idPhotoAvant;
	/**
	 * @var string picture after action
	 */
	var $idPhotoApres;
	var $is_readable_from_external;
	var $nom_exportable_plan_action;
	var $description_exportable_plan_action;

	var $real_start_date;
	var $real_end_date;
	var $estimate_cost;
	var $real_cost;
	var $planned_time;
	var $elapsed_time;

/*
 *	Constructor, getters and setters
 */

	/**
	 * Constructor of the Task class
	 * @param int $id The identifier to set
	 * @param string $name The name to set
	 * @param string $leftLimit The left limit to set
	 * @param string $rightLimit The right limit to set
	 * @param string $description The description to set
	 * @param string $startDate The start date to set
	 * @param string $finishDate The finish date to set
	 * @param string $place The place to set
	 * @param string $progression The progression to set
	 * @param float $cost The cost to set
	 * @param string $idFrom The table of the element that induces the task to set
	 * @param string $tableFrom The identifier of the element that induces the task to set
	 * @param string $status The status to set
	 * @param date $firstInsert The insert date to set
	 * @param integer $idCreateur The task creator identifier
	 * @param integer $idResponsable The identifier of the person in charge of the task
	 * @param integer $idSoldeur The task maker
	 * @param string $ProgressionStatus The progression status
	 * @param string $dateSolde
	 * @param string $hasPriority
	 * @param string $efficacite
	 */
	function EvaBaseTask($id = null, $name = '',	$leftLimit = 0,	$rightLimit = 1, $description = '', $startDate = '', $finishDate = '', $place = '', $progression = '', $cost = 0, $idPhotoAvant = 0, $idPhotoApres = 0, $idFrom = 0, $tableFrom = '', $status = 'Valid', $firstInsert ='', $idCreateur ='', $idResponsable ='', $idSoldeur ='',  $idSoldeurChef ='', $ProgressionStatus ='', $dateSolde ='', $hasPriority ='', $efficacite ='', $is_readable_from_external ='', $real_start_date ='', $real_end_date ='', $estimate_cost ='', $real_cost ='', $planned_time ='', $elapsed_time ='')
	{
		$this->id = $id;
		$this->name = $name;
		$this->leftLimit = $leftLimit;
		$this->rightLimit = $rightLimit;
		$this->description = $description;
		$this->startDate = $startDate;
		$this->finishDate = $finishDate;
		$this->place = $place;
		$this->progression = $progression;
		$this->cost = $cost;
		$this->idPhotoAvant = $idPhotoAvant;
		$this->idPhotoApres = $idPhotoApres;
		$this->idFrom = $idFrom;
		$this->tableFrom = $tableFrom;
		$this->status = $status;
		$this->firstInsert = $firstInsert;
		$this->idCreateur = $idCreateur;
		$this->idResponsable = $idResponsable;
		$this->idSoldeur = $idSoldeur;
		$this->idSoldeurChef = $idSoldeurChef;
		$this->ProgressionStatus = $ProgressionStatus;
		$this->is_readable_from_external = $is_readable_from_external;
		$this->dateSolde = $dateSolde;
		$this->hasPriority = $hasPriority;
		$this->efficacite = $efficacite;

		$this->real_start_date = $real_start_date;
		$this->real_end_date = $real_end_date;
		$this->estimate_cost = $estimate_cost;
		$this->real_cost = $real_cost;
		$this->planned_time = $planned_time;
		$this->elapsed_time = $elapsed_time;
	}

	/**
	 * Returns the Task identifier
	 * @return int The identifier
	 */
	function getId()
	{
		return $this->id;
	}

	/**
	 * Set the Task identifier
	 * @param int $id The identifier to set
	 */
	function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Returns the Task name
	 * @return string The name
	 */
	function getName()
	{
		return $this->name;
	}

	/**
	 * Set the Task name
	 * @param string $name The name to set
	 */
	function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * Returns the Task left limit
	 * @return string The left limit
	 */
	function getLeftLimit()
	{
		return $this->leftLimit;
	}

	/**
	 * Set the Task left limit
	 * @param string $leftLimit The left limit to set
	 */
	function setLeftLimit($leftLimit)
	{
		$this->leftLimit = $leftLimit;
	}

	/**
	 * Returns the Task right limit
	 * @return string The right limit
	 */
	function getRightLimit()
	{
		return $this->rightLimit;
	}

	/**
	 * Set the Task right limit
	 * @param string $rightLimit The right limit to set
	 */
	function setRightLimit($rightLimit)
	{
		$this->rightLimit = $rightLimit;
	}

	/**
	 * Returns the Task second lige
	 * @return string The second lige
	 */
	function getDescription()
	{
		return $this->description;
	}

	/**
	 * Set the Task description
	 * @param string $description The description to set
	 */
	function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * Returns the Task start date
	 * @return string The start date
	 */
	function getStartDate()
	{
		return $this->startDate;
	}

	/**
	 * Set the Task start date
	 * @param string $startDate The start date to set
	 */
	function setStartDate($startDate)
	{
		$this->startDate = $startDate;
	}

	/**
	 * Returns the Task finish date
	 * @return string The finish date
	 */
	function getFinishDate()
	{
		return $this->finishDate;
	}

	/**
	 * Set the Task finish date
	 * @param string $finishDate The finish date to set
	 */
	function setFinishDate($finishDate)
	{
		$this->finishDate = $finishDate;
	}

	/**
	 * Returns the Task place
	 * @return int The place
	 */
	function getPlace()
	{
		return $this->place;
	}

	/**
	 * Set the Task place
	 * @param int $place The place to set
	 */
	function setPlace($place)
	{
		$this->place = $place;
	}

	/**
	 * Returns the Task progression
	 * @return int The progression
	 */
	function getProgression()
	{
		return $this->progression;
	}

	/**
	 * Set the Task progression
	 * @param int $progression The progression to set
	 */
	function setProgression($progression)
	{
		$this->progression = $progression;
	}

	/**
	 * Returns the Task cost
	 * @return float The cost
	 */
	function getCost()
	{
		return $this->cost;
	}

	/**
	 * Set the Task cost
	 * @param float $cost The cost to set
	 */
	function setCost($cost)
	{
		$this->cost = $cost;
	}

	/**
	 * Returns the identifier of the element that induces the task
	 * @return string The identifier
	 */
	function getIdFrom()
	{
		return $this->idFrom;
	}

	/**
	 * Set the identifier of the element that induces the task
	 * @param string $idFrom The identifier to set
	 */
	function setIdFrom($idFrom)
	{
		$this->idFrom = $idFrom;
	}

	/**
	 * Returns the table of the element that induces the task
	 * @return string The table
	 */
	function getTableFrom()
	{
		return $this->tableFrom;
	}

	/**
	 * Set the table of the element that induces the task
	 * @param string $tableFrom The table to set
	 */
	function setTableFrom($tableFrom)
	{
		$this->tableFrom = $tableFrom;
	}

	/**
	 * Returns the Task status
	 * @return string The status
	 */
	function getStatus()
	{
		return $this->status;
	}

	/**
	 * Set the Task status
	 * @param string $status The status to set
	 */
	function setStatus($status)
	{
		$this->status = $status;
	}

	/**
	 * Returns the activity insert date
	 * @return date The activity insert date
	 */
	function getFirstInsert()
	{
		return $this->firstInsert;
	}

	/**
	 * Returns the activity insert date
	 * @param date $firstInsert The date to set
	 */
	function setFirstInsert($firstInsert)
	{
		$this->firstInsert = $firstInsert;
	}

	/**
	 * Returns the task creator id
	 * @return integer The task creator id
	 */
	function getidCreateur()
	{
		return $this->idCreateur;
	}

	/**
	 * Returns the task creator id
	 * @param integer $idCreateur The task creator id
	 */
	function setidCreateur($idCreateur)
	{
		$this->idCreateur = $idCreateur;
	}

	/**
	 * Returns the task maker id
	 * @return integer The task maker id
	 */
	function getidSoldeur()
	{
		return $this->idSoldeur;
	}

	/**
	 * Returns the task maker id
	 * @param integer $idSoldeur The task maker id
	 */
	function setidSoldeur($idSoldeur)
	{
		$this->idSoldeur = $idSoldeur;
	}

	/**
	 * Returns the task maker id
	 * @return integer The task maker id
	 */
	function getidSoldeurChef()
	{
		return $this->idSoldeurChef;
	}

	/**
	 * Returns the task maker id
	 * @param integer $idSoldeurChef The task maker id
	 */
	function setidSoldeurChef($idSoldeurChef)
	{
		$this->idSoldeurChef = $idSoldeurChef;
	}

	/**
	 * Returns the identifier of the person in charge of the task
	 * @return integer The identifier of the person in charge of the task
	 */
	function getidResponsable()
	{
		return $this->idResponsable;
	}

	/**
	 * Returns the identifier of the person in charge of the task
	 * @param integer $idResponsable The identifier of the person in charge of the task
	 */
	function setidResponsable($idResponsable)
	{
		$this->idResponsable = $idResponsable;
	}

	/**
	 * Returns the progression status, if the taks is done or not
	 * @return string The progression status
	 */
	function getProgressionStatus()
	{
		return $this->ProgressionStatus;
	}

	/**
	 * Returns The progression status of the task
	 * @param string $ProgressionStatus The progression status of the task
	 */
	function setProgressionStatus($ProgressionStatus)
	{
		$this->ProgressionStatus = $ProgressionStatus;
	}

	/**
	 * Returns the progression status, if the taks is done or not
	 * @return string The progression status
	 */
	function getdateSolde()
	{
		return $this->dateSolde;
	}

	/**
	 * Returns The progression status of the task
	 * @param string $dateSolde The progression status of the task
	 */
	function setdateSolde($dateSolde)
	{
		$this->dateSolde = $dateSolde;
	}

	/**
	 * Returns the task's priority status
	 * @return string The priority status
	 */
	function gethasPriority()
	{
		return $this->hasPriority;
	}

	/**
	 * Returns The priority status of the task
	 * @param string $hasPriority The priority status of the task
	 */
	function sethasPriority($hasPriority)
	{
		$this->hasPriority = $hasPriority;
	}

	/**
	 * Returns the task's priority status
	 * @return string The priority status
	 */
	function getEfficacite()
	{
		return $this->efficacite;
	}

	/**
	 * Returns The priority status of the task
	 * @param string $efficacite The priority status of the task
	 */
	function setEfficacite($efficacite)
	{
		$this->efficacite = $efficacite;
	}

	/**
	 * Returns the task's priority status
	 * @return string The priority status
	 */
	function get_external_readable()
	{
		return $this->is_readable_from_external;
	}

	/**
	 * Returns The priority status of the task
	 * @param string $efficacite The priority status of the task
	 */
	function set_external_readable($is_readable_from_external)
	{
		$this->is_readable_from_external = $is_readable_from_external;
	}


	/**
	 * Returns The date that the action was mark as done
	 * @return date the date
	 */
	function getidPhotoAvant()
	{
		return $this->idPhotoAvant;
	}

	/**
	 * Returns The date that the action was mark as done
	 * @param string $dateSolde The date
	 */
	function setidPhotoAvant($idPhotoAvant)
	{
		$this->idPhotoAvant = $idPhotoAvant;
	}

	/**
	 * Returns The date that the action was mark as done
	 * @return date the date
	 */
	function getidPhotoApres()
	{
		return $this->idPhotoApres;
	}

	/**
	 * Returns The date that the action was mark as done
	 * @param string $dateSolde The date
	 */
	function setidPhotoApres($idPhotoApres)
	{
		$this->idPhotoApres = $idPhotoApres;
	}

	/**
	 * Returns The date that the action was mark as done
	 * @return date the date
	 */
	function getdescription_exportable_plan_action()
	{
		return $this->description_exportable_plan_action;
	}

	/**
	 * Returns The date that the action was mark as done
	 * @param string $dateSolde The date
	 */
	function setdescription_exportable_plan_action($description_exportable_plan_action)
	{
		$this->description_exportable_plan_action = $description_exportable_plan_action;
	}

	/**
	 * Returns The date that the action was mark as done
	 * @return date the date
	 */
	function getnom_exportable_plan_action()
	{
		return $this->nom_exportable_plan_action;
	}

	/**
	 * Returns The date that the action was mark as done
	 * @param string $dateSolde The date
	 */
	function setnom_exportable_plan_action($nom_exportable_plan_action)
	{
		$this->nom_exportable_plan_action = $nom_exportable_plan_action;
	}

	function getreal_start_date() {
		return $this->real_start_date;
	}
	function setreal_start_date($real_start_date) {
		$this->real_start_date = $real_start_date;
	}

	function getreal_end_date() {
		return $this->real_end_date;
	}
	function setreal_end_date($real_end_date) {
		$this->real_end_date = $real_end_date;
	}

	function getestimate_cost() {
		return $this->estimate_cost;
	}
	function setestimate_cost($estimate_cost) {
		$this->estimate_cost = $estimate_cost;
	}

	function getreal_cost() {
		return $this->real_cost;
	}
	function setreal_cost($real_cost) {
		$this->real_cost = $real_cost;
	}

	function getplanned_time() {
		return $this->planned_time;
	}
	function setplanned_time($planned_time) {
		$this->planned_time = $planned_time;
	}

	function getelapsed_time() {
		return $this->elapsed_time;
	}
	function setelapsed_time($elapsed_time) {
		$this->elapsed_time = $elapsed_time;
	}


/*
 * Others methods
 */
	/**
	 * Convert a wpdb object to an EvaBaseTask object
	 * @param object $wpdbTask The object to convert
	 */
	function convertWpdb($wpdbTask){
		$this->setId($wpdbTask->id);
		$this->setName($wpdbTask->nom);
		$this->setDescription($wpdbTask->description);
		$this->setLeftLimit($wpdbTask->limiteGauche);
		$this->setRightLimit($wpdbTask->limiteDroite);
		$this->setStartDate($wpdbTask->dateDebut);
		$this->setFinishDate($wpdbTask->dateFin);
		$this->setPlace($wpdbTask->lieu);
		$this->setProgression($wpdbTask->avancement);
		$this->setCost($wpdbTask->cout);
		$this->setIdFrom($wpdbTask->idProvenance);
		$this->setTableFrom($wpdbTask->tableProvenance);
		$this->setStatus($wpdbTask->Status);
		$this->setFirstInsert($wpdbTask->firstInsert);
		$this->setidCreateur($wpdbTask->idCreateur);
		$this->setidResponsable($wpdbTask->idResponsable);
		$this->setidSoldeur($wpdbTask->idSoldeur);
		$this->setProgressionStatus($wpdbTask->ProgressionStatus);
		$this->setdateSolde($wpdbTask->dateSolde);
		$this->sethasPriority($wpdbTask->hasPriority);
		$this->setEfficacite($wpdbTask->efficacite);
		$this->setidPhotoAvant($wpdbTask->idPhotoAvant);
		$this->setidPhotoApres($wpdbTask->idPhotoApres);
		$this->set_external_readable($wpdbTask->is_readable_from_external);
		$this->setnom_exportable_plan_action($wpdbTask->nom_exportable_plan_action);
		$this->setdescription_exportable_plan_action($wpdbTask->description_exportable_plan_action);

		$this->setreal_start_date($wpdbTask->real_start_date);
		$this->setreal_end_date($wpdbTask->real_end_date);
		$this->setestimate_cost($wpdbTask->estimate_cost);
		$this->setreal_cost($wpdbTask->real_cost);
		$this->setplanned_time($wpdbTask->planned_time);
		$this->setelapsed_time($wpdbTask->elapsed_time);
	}

	/**
	 * Convert an EvaBaseTask object to a wpdb object
	 * @return object The converted object
	 */
	function convertToWpdb(){
		return
			(object)(
				array(
					self::id => $this->getId(),
					self::leftLimit => $this->getLeftLimit(),
					self::rightLimit => $this->getRightLimit(),
					self::name => $this->getName(),
					self::description => $this->getDescription(),
					self::startDate => $this->getStartDate(),
					self::finishDate => $this->getFinishDate(),
					self::place => $this->getPlace(),
					self::progression => $this->getProgression(),
					self::cost => $this->getCost(),
					self::idPhotoApres => $this->getidPhotoApres(),
					self::idPhotoAvant => $this->getidPhotoAvant(),
					self::idFrom => $this->getIdFrom(),
					self::tableFrom => $this->getTableFrom(),
					self::status => $this->getStatus(),
					self::firstInsert => $this->getFirstInsert(),
					self::idCreateur => $this->getidCreateur(),
					self::idResponsable => $this->getidResponsable(),
					self::idSoldeur => $this->getidSoldeur(),
					self::ProgressionStatus => $this->getProgressionStatus(),
					self::dateSolde => $this->getdateSolde(),
					self::hasPriority => $this->gethasPriority(),
					self::efficacite => $this->getEfficacite(),
					self::is_readable_from_external => $this->get_external_readable(),
					self::nom_exportable_plan_action => $this->getnom_exportable_plan_action(),
					self::description_exportable_plan_action => $this->getdescription_exportable_plan_action(),

					self::real_start_date => $this->getreal_start_date(),
					self::real_end_date => $this->getreal_end_date(),
					self::estimate_cost => $this->getestimate_cost(),
					self::real_cost => $this->getreal_cost(),
					self::planned_time => $this->getplanned_time(),
					self::elapsed_time => $this->getelapsed_time(),
				)
			);
	}

}