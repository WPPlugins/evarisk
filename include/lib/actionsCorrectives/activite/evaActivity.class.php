<?php
/**
 * This class allows to work on single activity (equivalent to single row in data base)
 *
 * @author Evarisk
 * @version v5.0
 */
require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/activite/evaBaseActivity.class.php');

class EvaActivity extends EvaBaseActivity
{

	/*
	* Data base link
	*/

	/**
	* Save or update the activity in data base
	*/
	function save() {
		global $wpdb;
		global $current_user;

		{//Variables cleaning
			$id = (int) digirisk_tools::IsValid_Variable($this->getId());
			$relatedTaskId = (int) digirisk_tools::IsValid_Variable($this->getRelatedTaskId());
			$name = digirisk_tools::IsValid_Variable($this->getName());
			$description = digirisk_tools::IsValid_Variable($this->getDescription());
			$startDate = digirisk_tools::IsValid_Variable($this->getStartDate());
			$finishDate = digirisk_tools::IsValid_Variable($this->getFinishDate());
			$place = digirisk_tools::IsValid_Variable($this->getPlace());
			$cout = (float) digirisk_tools::IsValid_Variable($this->getCout());
			$progression = (int) digirisk_tools::IsValid_Variable($this->getProgression());
			$status = digirisk_tools::IsValid_Variable($this->getStatus());
			$idCreateur = digirisk_tools::IsValid_Variable($current_user->ID);
			$idResponsable = digirisk_tools::IsValid_Variable($this->getidResponsable());
			$idSoldeur = digirisk_tools::IsValid_Variable($this->getidSoldeur());
			$idSoldeurChef = digirisk_tools::IsValid_Variable($this->getidSoldeurChef());
			$ProgressionStatus = digirisk_tools::IsValid_Variable($this->getProgressionStatus());
			$dateSolde = digirisk_tools::IsValid_Variable($this->getdateSolde());
			$idPhotoAvant = digirisk_tools::IsValid_Variable($this->getidPhotoAvant());
			$idPhotoApres = digirisk_tools::IsValid_Variable($this->getidPhotoApres());
			$nom_exportable_plan_action = digirisk_tools::IsValid_Variable($this->getnom_exportable_plan_action());
			$description_exportable_plan_action = digirisk_tools::IsValid_Variable($this->getdescription_exportable_plan_action());
			$planned_time = digirisk_tools::IsValid_Variable($this->getplanned_time());
			$elapsed_time = digirisk_tools::IsValid_Variable($this->getelapsed_time());
			$cout_reel = digirisk_tools::IsValid_Variable($this->getcout_reel());
			$real_start_date = digirisk_tools::IsValid_Variable($this->getreal_start_date());
			$real_end_date = digirisk_tools::IsValid_Variable($this->getreal_end_date());
		}

		/**		Build action information for database insertion	*/
		$activite_main_args = array(
			self::relatedTaskId => $relatedTaskId,
			self::name => $name,
			self::description => $description,
			self::startDate => $startDate,
			self::finishDate => $finishDate,
			self::place => $place,
			self::cout => $cout,
			self::progression => $progression,
			self::status => $status,
			self::idResponsable => $idResponsable,
			self::idSoldeur => $idSoldeur,
			self::idSoldeurChef => $idSoldeurChef,
			self::ProgressionStatus => $ProgressionStatus,
			self::dateSolde => $dateSolde,
			self::idPhotoAvant => $idPhotoAvant,
			self::idPhotoApres => $idPhotoApres,
			self::nom_exportable_plan_action => $nom_exportable_plan_action,
			self::description_exportable_plan_action => $description_exportable_plan_action,
			self::planned_time => $planned_time,
			self::elapsed_time => $elapsed_time,
			self::cout_reel => $cout_reel,
			self::real_start_date => $real_start_date,
			self::real_end_date => $real_end_date,
		);
		/**		Laucnh query to database	*/
		$activite_save_operation = false;
		if( $id == 0 ) {
			$activite_creation_action = wp_parse_args( array(
				self::firstInsert => current_time('mysql', 0),
				self::idCreateur => $idCreateur,
			), $activite_main_args );
			$activite_save_operation = $wpdb->insert( TABLE_ACTIVITE, $activite_creation_action );
		}
		else {//Update of the data base
			$activite_save_operation = $wpdb->update( TABLE_ACTIVITE, $activite_main_args, array( self::id => $id, ) );
		}

		//Query execution
		/* We use identity (===) because query can return both, 0 and false
		 * if 0 is return, their is no change but no trouble in database
	 	 */
		if ( $activite_save_operation === false ) {//Their is some troubles
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
	* Load the activity with identifier key
	*/
	function load()
	{
		global $wpdb;
		$id = (int) digirisk_tools::IsValid_Variable($this->getId());
		if($id != 0)
		{
			$wpdbActivity = $wpdb->get_row( "SELECT * FROM " . TABLE_ACTIVITE . " WHERE " . self::id . " = " . $id);

			if($wpdbActivity != null)
			{
				$this->convertWpdb($wpdbActivity);
			}
		}
	}

/*
* Others methods
*/
	/**
	* Transfert an activity from one task to another.
	*
	* @param int $newRelatedTaskId New relative task identifier
	*
	*/
	function transfert($newRelatedTaskId) {
		global $wpdb;
		$wpdb->update( TABLE_ACTIVITE, array( self::relatedTaskId => $newRelatedTaskId, ), array( self::id => $this->getId(), ) );
	}

	/**
	*	Save a new activity (sub-task) into correctiv action database
	*
	* @return array $result The different result state and information
	*/
	function saveNewActivity(){
		global $wpdb;
		require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/tache/evaTask.class.php');

		$activite = new EvaActivity();
		$activite->setName($_POST['nom_activite']);
		$activite->setDescription($_POST['description']);
		$activite->setStartDate($_POST['date_debut']);
		$activite->setFinishDate($_POST['date_fin']);
		$activite->setCout($_POST['cout']);
		$activite->setProgression($_POST['avancement']);
		$activite->setProgressionStatus('notStarted');
		$activite->setnom_exportable_plan_action(!empty($_POST['nom_exportable_plan_action'])?$_POST['nom_exportable_plan_action']:'no');
		$activite->setdescription_exportable_plan_action(!empty($_POST['description_exportable_plan_action'])?$_POST['description_exportable_plan_action']:'no');
		if($_POST['avancement'] > '0'){
			$activite->setProgressionStatus('inProgress');
		}
		if($_POST['avancement'] == '100'){
			$activite->setProgressionStatus('Done');
			global $current_user;
			$activite->setidSoldeur($current_user->ID);
		}
		$activite->setidResponsable($_POST['responsable_activite']);

		$tache = new EvaTask($_POST['parentTaskId']);
		$tache->load();
		$activite->setRelatedTaskId($tache->getId());
		$activite->save();

		$tache->getTimeWindow();
		$tache->computeProgression();
		$tache->save();

		/*	Update the task ancestor	*/
		$wpdbTasks = Arborescence::getAncetre(TABLE_TACHE, $tache->convertToWpdb());
		foreach($wpdbTasks as $task){
			unset($ancestorTask);
			$ancestorTask = new EvaTask($task->id);
			$ancestorTask->load();
			$ancestorTask->computeProgression();
			$ancestorTask->save();
			unset($ancestorTask);
		}

		$result['task_status'] = $tache->getStatus();
		$result['action_status'] = $activite->getStatus();
		$result['task_id'] = $tache->getId();
		$result['action_id'] = $activite->getId();

		return $result;
	}

	/**
	*	Function allowing to display the correctiv action form in frontend
	*/
	function task_asker($args){
		$task_asker = '';

		$main_options = get_option('digirisk_options');
		$taks_asker_available = false;
		$task_asker_not_available_explanation = '';

		if($main_options['digi_ac_allow_front_ask'] == 'oui'){
			if($main_options['digi_ac_front_ask_parent_task_id'] > 1){
				$task_to_check = new EvaTask($main_options['digi_ac_front_ask_parent_task_id']);
				$task_to_check->load();
				if($task_to_check->getName() != ''){
					$taks_asker_available = true;
				}
				else{
					$task_asker_not_available_explanation = __('Impossible de trouver la t&acirc;che principale permettant d\'effectuer les demandes. Vous pouvez contacter votre administrateur pour plus d\'informations', 'evarisk');
				}
			}
			else{
				$task_asker_not_available_explanation = __('Impossible de trouver la t&acirc;che principale permettant d\'effectuer les demandes. Vous pouvez contacter votre administrateur pour plus d\'informations', 'evarisk');
			}
		}
		else{
			$task_asker_not_available_explanation = __('La demande d\'action est d&eacute;sactiv&eacute;e pour le moment. Vous pouvez contacter votre administrateur pour plus d\'informations', 'evarisk');
		}

		if ($taks_asker_available) {
			$ask_argument['tableProvenance'] = 'correctiv_action_ask';
			$ask_argument['provenance'] = 'ask_correctiv_action';
			$ask_argument['output_mode'] = 'return';
			$ask_argument['requested_action'] = 'ask_correctiv_action';
			if( empty( $main_options['digi_ac_front_ask_must_be_logged_in'] ) || ( is_user_logged_in() && current_user_can( 'digi_ask_action_front' ) ) ) {
				$task_asker = '
	<div id="message' . $ask_argument['tableProvenance'] . '" class="digirisk_hide" >&nbsp;</div>
	<div class="clear" >' .
		self::sub_task_creation_form($ask_argument) . '
	</div>
	<div class="digirisk_hide" id="ajax-response" >&nbsp;</div>';
			}
			else{
				$task_asker = __('Pour demander la cr&eacute;ation d\'une nouvelle action corrective, vous devez &ecirc;tre connect&eacute; et l\'administrateur du site doit vous donner les droits', 'evarisk') . wp_login_form(array('echo' => false));
			}
		}
		else{
			$task_asker = $task_asker_not_available_explanation;
		}

		echo $task_asker;
	}
	/**
	*
	*/
	function task_asker_add_picture($table_provenance, $token){
		return '<input type="file" name="correctiv_action_picture" id="correctiv_action_picture" />';
		// evaPhoto::getUploadForm($table_provenance, $token, 'jQuery("#ask_correctiv_action_picture_form").html("");jQuery("#ask_correctiv_action_picture img").attr("src", "' . EVA_UPLOADS_PLUGIN_URL . $table_provenance . "/" . $token . '/" + response); jQuery("#ask_correctiv_action_picture").show();');
	}

	/**
	*	Create the form used for new activity creation. Return different shape following parameters
	*
	*	@param array $arguments An array with the different parameters for form outputting (As element table, identifier, original request, ...)
	*
	*	@return string $sub_task_creation_form An html output of the form
	*/
	function sub_task_creation_form($arguments) {
		$idElement = $activite_new = $addPictureButton = '';
		$options = get_option('digirisk_options');

		/*	Check if an element has been passed as parameter => If there is an element, load the good activity	*/
		if ( !empty($arguments['idElement']) ) {
			$idElement = $arguments['idElement'];
			$activite = new EvaActivity($idElement);
			$activite->load();
			$contenuInputTitre = $activite->getName();
			$contenuInputDescription = $activite->getDescription();
			$contenuInputAvancement = $activite->getProgression();
			$contenuInputResponsable = $activite->getidResponsable();
			$contenuInputRealisateur = $activite->getidSoldeur();
			$ProgressionStatus = $activite->getProgressionStatus();
			$nom_exportable_plan_action = $activite->getnom_exportable_plan_action();
			$description_exportable_plan_action = $activite->getdescription_exportable_plan_action();
			$firstInsert = $activite->getFirstInsert();
			$idCreateur = $activite->getidCreateur();
			$grise = false;
			$idPere = $activite->getRelatedTaskId();
			$saveOrUpdate = 'update';
			if(isset($arguments['requested_action']) && ($arguments['requested_action'] == 'control_asked_action')){
				$saveOrUpdate = 'add_control';
				$contenuInputAvancement = 100;
			}
		}
		else {
			$activite = null;
			$contenuInputTitre = $contenuInputDescription = $contenuInputRealisateur = $contenuInputResponsable = $firstInsert = $nom_exportable_plan_action = $description_exportable_plan_action = '';
			$ProgressionStatus = 'notStarted';
			$idCreateur = 0;
			$contenuInputAvancement = 0;
			$idPere = !empty($arguments['idPere']) ? $arguments['idPere'] : '';
			$grise = true;
			$saveOrUpdate = (isset($arguments['requested_action']) && ($arguments['requested_action'] != '') && (!in_array($arguments['requested_action'], array('demandeAction', 'ficheAction')))) ? $arguments['requested_action'] : 'save';
			if(isset($arguments['requested_action']) && ($arguments['requested_action'] == 'demandeAction'))
				$saveOrUpdate = 'addAction';
			elseif(isset($arguments['requested_action']) && ($arguments['requested_action'] == 'ficheAction')){
				$saveOrUpdate = 'add_control';
				$contenuInputAvancement = 100;
			}
		}

		/*	Recupere la tache parent pour v�rifier si on peut cocher les cases d'export dans le document unique	*/
		$parent_task = new EvaTask($idPere);
		$parent_task->load();

		/*	Form initialisation	*/
		$idBouttonEnregistrer = 'save_activite';
		$idBouttonSold = 'actionDone';

		/*	Check if the user in charge of the action is mandatory */
		$idResponsableIsMandatory = digirisk_options::getOptionValue('responsable_Action_Obligatoire');
		/*	Check if an alert must be shown to user when he try to mark an action as done when not at 100 percent		*/
		$alertWhenMarkActionAsDone = digirisk_options::getOptionValue('avertir_Solde_Action_Non_100');

		{/*	Hidden field					*/
			$activite_new .=
				EvaDisplayInput::afficherInput('hidden', 'post', 'true', '', null, 'post', false, false) .
				EvaDisplayInput::afficherInput('hidden', 'act', $saveOrUpdate, '', null, 'act', false, false) .
				EvaDisplayInput::afficherInput('hidden', 'original_act', (!empty($arguments['requested_action'])?$arguments['requested_action']:''), '', null, 'original_act', false, false) .
				EvaDisplayInput::afficherInput('hidden', 'affichage_activite', (!empty($arguments['affichage']) ? $arguments['affichage'] : '' ), '', null, 'affichage', false, false) .
				EvaDisplayInput::afficherInput('hidden', 'table', TABLE_ACTIVITE, '', null, 'table', false, false) .
				EvaDisplayInput::afficherInput('hidden', 'id_activite', $idElement, '', null, 'id', false, false) .
				EvaDisplayInput::afficherInput('hidden', 'idPere_activite', $idPere, '', null, 'idPere', false, false) .
				EvaDisplayInput::afficherInput('hidden', 'idsFilAriane_activite', (!empty($arguments['idsFilAriane']) ? $arguments['idsFilAriane'] : ''), '', null, 'idsFilAriane', false, false) .
				EvaDisplayInput::afficherInput('hidden', 'idProvenance_activite', (!empty($arguments['idProvenance'])?$arguments['idProvenance']:''), '', null, 'idProvenance', false, false) .
				EvaDisplayInput::afficherInput('hidden', 'tableProvenance_activite', (!empty($arguments['tableProvenance'])?$arguments['tableProvenance']:''), '', null, 'tableProvenance', false, false);
		}
		{/*	Sub-Task name					*/
			$contenuAideTitre = "";
			$labelInput = ucfirst(sprintf(__("nom %s", 'evarisk'), __("de l'action",'evarisk'))) . '&nbsp;<span class="fieldInfo required" >' . __('(obligatoire)', 'evarisk') . '</span> : ';
			$exportable_option = '';
			if ( !empty ( $parent_task ) ) {
				if ( ( $parent_task->name != __('Tache Racine', 'evarisk') ) && ( $parent_task->nom_exportable_plan_action == 'no' ) ) {
					$labelInput .= '<input type="hidden" name="nom_exportable_plan_action" value="no" />';
					$exportable_option = ' disabled="disabled" title="' . __('L\'export ne peut &ecirc;tre activ&eacute; si la t&acirc;che parente n\'est pas exportable', 'evarisk') . '"';
					$nom_exportable_plan_action = 'no';
				}
			}
			if ( !empty($idElement) && ( $ProgressionStatus == 'Done' ) && (digirisk_options::getOptionValue('possibilite_Modifier_Action_Soldee') == 'non') ) {
				$exportable_option = ' disabled="disabled" title="' . __('L\'export ne peut &ecirc;tre activ&eacute; car cette t&acirc;che est sold&eacute;e', 'evarisk') . '"';
			}
			$checked = '';
			if ( (empty($nom_exportable_plan_action) || ($nom_exportable_plan_action=='yes') ) && ((empty($options['digi_ac_activity_default_exportable_plan_action']) && empty($options['digi_ac_activity_default_exportable_plan_action']['name'])) || ($options['digi_ac_activity_default_exportable_plan_action']['name'] == 'oui')) ) {
				$checked = ' checked="checked" ';
			}
			else {
				$nom_exportable_plan_action = 'no';
			}
			$labelInput .= '<div class="alignright" ><input type="checkbox" name="nom_exportable_plan_action" id="nom_exportable_plan_action"' . $exportable_option . ' value="yes"' . $checked . ' />&nbsp;<label for="nom_exportable_plan_action" >'.__('Exporter dans le plan d\'action', 'evarisk').'</label></div>';
			$nomChamps = "nom_activite";
			$idTitre = "nom_activite";
			$activite_new .= EvaDisplayInput::afficherInput('text', $idTitre, $contenuInputTitre, $contenuAideTitre, $labelInput, $nomChamps, $grise, true, 255, 'titleInput', '', '99%', '', '', false, '1') . '
<script type="text/javascript" >
	evarisk(document).ready(function(){
		jQuery("#nom_exportable_plan_action").click(function(){
			if( !jQuery(this).is(":checked") ) {
				jQuery("#description_exportable_plan_action").prop("checked",false);
				jQuery("#description_exportable_plan_action").prop("disabled",true);
			}
			else{
				jQuery("#description_exportable_plan_action").prop("disabled",false);
				if ("' . (empty($options) && (empty($options['digi_ac_activity_default_exportable_plan_action']) || empty($options['digi_ac_activity_default_exportable_plan_action']['description'])) || (!empty($options) && !empty($options['digi_ac_activity_default_exportable_plan_action']['description'])) ? $options['digi_ac_activity_default_exportable_plan_action']['description'] : '') . '" == "oui") {
					jQuery("#description_exportable_plan_action").prop("checked", true);
				}
			}
		});
	});
</script>';
		}
		{/*	Sub-Task Description	*/
			$contenuAideDescription = "";
			$labelInput = __("Description", 'evarisk') . ' : ';
			$exportable_option = '';
			if ( !empty ( $parent_task ) && ( $parent_task->nom_exportable_plan_action == 'no' ) || ( $nom_exportable_plan_action == 'no' ) ) {
				$labelInput .= '<input type="hidden" name="description_exportable_plan_action" value="no" />';
				$exportable_option = ' disabled="disabled" title="' . __('L\'export ne peut &ecirc;tre activ&eacute; si la t&acirc;che parente n\'est pas exportable', 'evarisk') . '"';
				$description_exportable_plan_action = 'no';
			}
			if ( ( $ProgressionStatus == 'Done' ) && (digirisk_options::getOptionValue('possibilite_Modifier_Action_Soldee') == 'non') ) {
				$exportable_option = ' disabled="disabled" title="' . __('L\'export ne peut &ecirc;tre activ&eacute; car cette t&acirc;che est sold&eacute;e', 'evarisk') . '"';
			}
			$checked = '';
			if ( (empty($description_exportable_plan_action) || ($description_exportable_plan_action=='yes') ) && ((empty($options['digi_ac_activity_default_exportable_plan_action']) && empty($options['digi_ac_activity_default_exportable_plan_action']['description'])) || ($options['digi_ac_activity_default_exportable_plan_action']['description'] == 'oui')) ) {
				$checked = ' checked="checked" ';
			}
			else {
				$description_exportable_plan_action = 'no';
			}
			$labelInput .= '<div class="alignright" ><input type="checkbox" name="description_exportable_plan_action" id="description_exportable_plan_action"' . $exportable_option . ' value="yes"' . $checked . ' />&nbsp;<label for="description_exportable_plan_action" >'.__('Exporter dans le plan d\'action', 'evarisk').'</label></div>';
			$id = "description_activite";
			$nomChamps = "description";
			$rows = 5;
			$activite_new .= EvaDisplayInput::afficherInput('textarea', $id, $contenuInputDescription, $contenuAideDescription, $labelInput, $nomChamps, $grise, true, $rows, '', '', '99%', '', '', false, '2');
		}

		{/*	Sub-Task creation informations		*/
			if(($firstInsert != '') || ($idCreateur > 0)){
				$activite_new .= '<div class="digi_action_created_by_infos" >';
				if(($firstInsert != '') && ($idCreateur > 0)){
					$subtask_creator_infos = evaUser::getUserInformation($idCreateur);
					$activite_new .= sprintf(__('Ajout&eacute;e le %s par %s', 'evarisk'), mysql2date('d M Y', $firstInsert, true), $subtask_creator_infos[$idCreateur]['user_lastname'] . ' ' . $subtask_creator_infos[$idCreateur]['user_firstname']);
				}
				elseif($firstInsert != ''){
					$activite_new .= sprintf(__('Ajout&eacute;e le %s', 'evarisk'), mysql2date('d M Y', $firstInsert, true));
				}
				elseif($idCreateur > 0){
					$subtask_creator_infos = evaUser::getUserInformation($idCreateur);
					$activite_new .= sprintf(__('Ajout&eacute;e par %s', 'evarisk'), $subtask_creator_infos[$idCreateur]['user_lastname'] . ' ' . $subtask_creator_infos[$idCreateur]['user_firstname']);
				}
				$activite_new .= '</div>';
			}
		}

		if (empty($arguments['provenance']) || (!empty($arguments['provenance']) && ($arguments['provenance'] != 'ask_correctiv_action'))) {
			/*	Sub-Task Responsible	*/
			$contenuAideDescription = "";
			$labelInput = __("Responsable", 'evarisk');
			if(digirisk_options::getOptionValue('responsable_Action_Obligatoire') == 'oui'){
				$labelInput .= '&nbsp;<span class="fieldInfo required" >' . __('(obligatoire)', 'evarisk') . '</span>';
			}
			$labelInput .= ' : <span class="fieldInfo" >' . sprintf(__('(vous pouvez d&eacute;finir si ce champs est obligatoire ou non dans le menu %s du plugin)', 'evarisk'), '<a href="' . get_bloginfo('siteurl') . '/wp-admin/options-general.php?page=' . DIGI_URL_SLUG_MAIN_OPTION . '#digirisk_options_correctivaction" target="optionPage" >' . __('Options', 'evarisk') . '</a>') . '</span>';
			$id = "responsable_activite";
			$nomChamps = "responsable_activite";

			$activite_new .= '<div class="digi_action_responsible clear" ><label for="search_user_responsable_' . $arguments['tableElement'] . '" class="clear" >' . $labelInput . '</label>' . EvaDisplayInput::afficherInput('hidden', $id, $contenuInputResponsable, '', null, $nomChamps, false, false);
			$search_input_state = '';
			$change_input_state = 'hide';
			if($contenuInputResponsable > 0){
				$search_input_state = 'hide';
				$change_input_state = '';
				$responsible = evaUser::getUserInformation($contenuInputResponsable);
				$activite_new .= '<div id="responsible_name" >' . ELEMENT_IDENTIFIER_U . $contenuInputResponsable . '&nbsp;-&nbsp;' . $responsible[$contenuInputResponsable]['user_lastname'] . ' ' . $responsible[$contenuInputResponsable]['user_firstname'];
			}
			else
				$activite_new .= '<div id="responsible_name" class="hide" >&nbsp;';

			$activite_new .= '</div>&nbsp;<span id="change_responsible_' . $arguments['tableElement'] . 'responsible" class="' . $change_input_state . ' change_ac_responsible" >' . __('Changer', 'evarisk') . '</span>&nbsp;&nbsp;<span id="delete_responsible_' . $arguments['tableElement'] . 'responsible" class="' . $change_input_state . ' delete_ac_responsible" >' . __('Enlever le responsable', 'evarisk') . '</span>

	<input class="searchUserToAffect ac_responsable ' . $search_input_state . '" type="text" name="responsable_name_' . $arguments['tableElement'] . '" id="search_user_responsable_' . $arguments['tableElement'] . '" placeholder="' . __('Rechercher dans la liste des utilisateurs', 'evarisk') . '" tabindex="3" /><div id="completeUserList' . $arguments['tableElement'] . 'responsible' . (!empty($arguments['requested_action']) ? $arguments['requested_action']: '') . '" class="completeUserList completeUserListActionResponsible hide clear" >' . evaUser::afficheListeUtilisateurTable_SimpleSelection($arguments['tableElement'] . 'responsible', $arguments['idElement']) . '</div>
	<script type="text/javascript" >
		digirisk(document).ready(function(){
			jQuery("#search_user_responsable_' . $arguments['tableElement'] . '").click(function(){
				jQuery(".completeUserListActionResponsible").show();
			});
			jQuery("#search_user_responsable_' . $arguments['tableElement'] . '").autocomplete({
				source: "' . EVA_INC_PLUGIN_URL . 'liveSearch/searchUsers.php?table_element=' . $arguments['tableElement'] . '&id_element=' . $arguments['idElement'] . '",
				select: function( event, ui ){
					jQuery("#responsable_activite").val(ui.item.value);
					jQuery("#responsible_name").html(ui.item.label);
					jQuery("#responsible_name").show();

					jQuery(".completeUserListActionResponsible").hide();
					jQuery(".searchUserToAffect").hide();
					jQuery("#change_responsible_' . $arguments['tableElement'] . 'responsible").show();
					jQuery("#delete_responsible_' . $arguments['tableElement'] . 'responsible").show();

					setTimeout(function(){
						jQuery("#search_user_responsable_' . $arguments['tableElement'] . '").val("");
						jQuery("#search_user_responsable_' . $arguments['tableElement'] . '").blur();
					}, 2);
				}
			});



			jQuery("#change_responsible_' . $arguments['tableElement'] . 'responsible").click(function(){
				jQuery("#search_user_responsable_' . $arguments['tableElement'] . '").show();
				jQuery("#completeUserList' . $arguments['tableElement'] . 'responsible").show();
				jQuery(this).hide();
			});
			jQuery("#delete_responsible_' . $arguments['tableElement'] . 'responsible").click(function(){
				jQuery("#responsable_activite").val("");
				jQuery("#responsible_name").html("&nbsp;");
				jQuery("#responsible_name").hide();
				jQuery(this).hide();
				jQuery("#change_responsible_' . $arguments['tableElement'] . 'responsible").hide();
				jQuery("#search_user_responsable_' . $arguments['tableElement'] . '").show();
			jQuery("#completeUserList' . $arguments['tableElement'] . 'responsible").hide();
			});
		});
	</script></div><br/>';
		}

		if (empty($arguments['requested_action']) || (!empty($arguments['requested_action']) && ($arguments['requested_action'] != 'ficheAction') && !empty($arguments['provenance']) && ($arguments['provenance'] != 'ask_correctiv_action'))) {
			ob_start();
			suivi_activite::digi_postbox_project( $arguments );
			$activite_new .= '
<div id="project_follow_up_summary_' . $arguments['tableElement'] . '_' . $arguments['idElement'] . '" >' . ob_get_contents() . '
</div>';
			ob_end_clean();
		}

		if(empty($arguments['provenance']) || ($arguments['provenance'] != 'ask_correctiv_action')){
			/*	Sub-Task progression	*/
			$id = "avancement_activite";
			$nomChamps = "avancement";
			$activite_new .= '<br class="clear" /><br/>' . __("Avancement", 'evarisk') . ' :
<input type="text" name="' . $nomChamps . '" id="' . $id . '" style="width:5%;" value="' . $contenuInputAvancement . '" tabindex="10"  />' . __('%', 'evarisk') . '<div id="sliderAvancement" >&nbsp;</div>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#' . $id . '").prop("readonly", "readonly");
		jQuery("#sliderAvancement").slider({
			value:' . $contenuInputAvancement . ',
			min: 0,
			max: 100,
			step: 1,
			slide:function(event, ui){
				jQuery("#' . $id . '").val(ui.value);
			}
		});
		jQuery("#' . $id . '").val(jQuery( "#sliderAvancement" ).slider("value"));
		jQuery("#' . $id . '").attr("style", jQuery( "#' . $id . '" ).attr("style") + "border:0px solid #000000;");
	});
</script>';
		}


		if(!empty($arguments['provenance']) && ($arguments['provenance'] != 'ask_correctiv_action')){/*	Add possibility for user to add a picture and to affect to an existing element for the asked tasks	*/
			/*	Add a picture	*/
			$token = rand();
			$activite_new .= '<div class="digirisk_hide" id="loading_round_pic" ><div class="main_loading_pic_container" ><img src="' . admin_url('images/loading.gif') . '" alt="loading..." /></div></div><input type="hidden" name="token_for_element" id="token_for_element" value="' . $token . '" /><label for="correctiv_action_before_pic" class="clear" >' . __('Photo', 'evarisk') . '</label>&nbsp;:&nbsp;<div id="ask_correctiv_action_picture_form" >' . self::task_asker_add_picture($arguments['tableProvenance'], $token) . '</div><div id="ask_correctiv_action_picture" class="digirisk_hide" ><img src="' . admin_url('images/loading.gif') . '" alt="correctiv_action_ask_picture" /><div class="pointer" id="dac_pic_change" >' . __('Changer l\'image', 'evarisk') . '</div><script type="text/javascript" >digirisk(document).ready(function(){jQuery("#dac_pic_change").click(function(){jQuery("#ask_correctiv_action_picture_form").html(jQuery("#loading_round_pic").html());jQuery("#ask_correctiv_action_picture img").attr("src", "' . admin_url('images/loading.gif') . '");jQuery("#ask_correctiv_action_picture").hide();jQuery("#ask_correctiv_action_picture_form").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{"post":"true", "table":"' . TABLE_ACTIVITE . '", "act":"reload_correctiv_asker_picture_form", "tableProvenance":"' . $arguments['tableProvenance'] . '", "token":"' . $token . '", "delete_old":"yes"});});});</script></div>';

			/*	Affect to existing element	*/
			$activite_new .= __('Affecter cette t&acirc;che &agrave; un &eacute;l&eacute;ment existant', 'evarisk') . '&nbsp;:&nbsp;<br/>' . arborescence_special::search_form();
		}

		if(isset($arguments['requested_action']) && (($arguments['requested_action'] == 'ficheAction') || ($arguments['requested_action'] == 'control_asked_action'))){/*	Risk new level cotation	*/
			/**	Task efficiency	*/
			$activite_new .= sprintf(__('Efficacit&eacute; de la t&acirc;che %s', 'evarisk'), '<input type="text" name="correctiv_action_efficiency_control" id="correctiv_action_efficiency_control' . $idElement . '" value="0" class="correctiv_action_efficiency_control" readonly="readonly" />%') . '<div id="correctiv_action_efficiency_control_slider' . $idElement . '" class="correctiv_action_efficiency_control_slider" >&nbsp;</div>';

			/**	Load the different var for the risk associated method	*/
			$activite_new .= '
				<fieldset id="divVariablesFormRisque-simpleFAC-fieldset" >
					<legend class="bold" >' . __('Nouvelle &Eacute;valuation', 'evarisk') . ' :</legend>
					<script type="text/javascript">
						digirisk(document).ready(function(){
							setTimeout(function(){
								digirisk("#divVariablesFormRisque-simpleFAC").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
									"post":"true",
									"table":"' . TABLE_METHODE . '",
									"act":"reloadVariables",
									"idRisque":"' . $arguments['idProvenance'] . '"
								});
							}, 700);
							digirisk("#sliderAvancement").hide();
						})
					</script>
					<div id="divVariablesFormRisque-simpleFAC" ></div>
				</fieldset>';

			{/*	Current risk description	*/
				$contenuInput = '';
				if($arguments['idProvenance'] != ''){
					$risque = Risque::getRisque($arguments['idProvenance']);
				}
				else{
					$risque = null;
				}
				if($risque[0] != null){// Si l'on �dite un risque, on remplit l'aire de texte avec sa description
					$contenuInput = $risque[0]->commentaire;
				}
				$labelInput = ucfirst(strtolower(sprintf(__("commentaire %s", 'evarisk'), __('sur le risque', 'evarisk'))));
				$labelInput[1] = ($labelInput[0] == "&") ? ucfirst($labelInput[1]) : $labelInput[1];
				$activite_new .= '<br/><div id="divDescription" class="clear risk_description_container" >' . EvaDisplayInput::afficherInput('textarea', 'descriptionFormRisque', $contenuInput, '', $labelInput . ' : ', 'description_risque', false, DESCRIPTION_RISQUE_OBLIGATOIRE, 3, '', '', '95%', '') . '</div>';
			}
		}

		{/*	Put in progress button	*/
			$idBouttonSetInProgress = 'setActivityInProgress';
			$scriptEnregistrementInProgress = '<script type="text/javascript">
				digirisk("#' . $idBouttonSetInProgress . '").click(function(){
					digirisk("#inProgressButtonContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
						"post": "true",
						"table": "' . TABLE_ACTIVITE . '",
						"act": "setActivityInProgress",
						"id": digirisk("#id_activite").val()
					});
				});
			</script>';
		}

		$sub_task_creation_form =
'<form method="post" id="informationGeneralesActivite" action="' . EVA_INC_PLUGIN_URL . 'ajax.php" >' .
$activite_new . EvaDisplayInput::fermerForm('informationGeneralesActivite') . '<div class="clear" ></div><br/>';

		if((!empty($arguments['output_mode']) && $arguments['output_mode'] == 'return') && !empty($arguments['provenance']) && ($arguments['provenance'] != 'ask_correctiv_action')){/*	Add picture button			*/
			$sub_task_creation_form .= '<div id="photosActionsCorrectives" >&nbsp;</div>';
			$addPictureButton =
				'<div id="add_picture_alert" class="hide" title="' . __('Modification de la cotation d\'un risque depuis une action corrective', 'evarisk') . '" >&nbsp;</div><input type="button" name="add_control_picture" id="add_control_picture" class="button-primary alignleft" value="' . __('Enregistrer puis ajouter des photos', 'evarisk') . '" />&nbsp;';
		}
		{/*	Add buttons to output		*/
			$inProgressButton = '';
			if(($saveOrUpdate == 'update') && ($ProgressionStatus != '') && ($ProgressionStatus != 'inProgress') && ($contenuInputAvancement != '100')){
				$inProgressButton = '<span id="inProgressButtonContainer" class="alignleft" >' . EvaDisplayInput::afficherInput('button', $idBouttonSetInProgress, __('Passer en cours', 'evarisk'), null, '', $idBouttonSetInProgress, false, true, '', 'button-secondary', '', '', $scriptEnregistrementInProgress, 'left', false, '11') . '</span> ';
			}
			if(($saveOrUpdate == 'add_control') || ($saveOrUpdate == 'ask_correctiv_action') || ($saveOrUpdate == 'addAction') || ($saveOrUpdate == 'save') || ($ProgressionStatus == '') || ($ProgressionStatus == 'inProgress') || ($ProgressionStatus == 'notStarted') || (digirisk_options::getOptionValue('possibilite_Modifier_Action_Soldee') == 'oui')){
				$sub_task_creation_form .=
					' <div class="alignright" id="ActionSaveButton" >' . $inProgressButton;

				if(($saveOrUpdate == 'update') && (($ProgressionStatus == '') || ($ProgressionStatus == 'notStarted') || ($ProgressionStatus == 'inProgress'))){
					$sub_task_creation_form .=
						EvaDisplayInput::afficherInput('button', $idBouttonSold, __('Solder l\'action', 'evarisk'), null, '', $idBouttonSold, false, true, '', 'button-secondary', '', '', '', 'left');
				}
				elseif($saveOrUpdate == 'update'){
					$sub_task_creation_form .=
						'<div style="float:left;" id="ActionSaveButton" >
							<br/>
							<div class="alignright button-primary" >' .
								__('Cette action est sold&eacute;e', 'evarisk') .
							'</div>
						</div>';
				}

				$sub_task_creation_form .=
					$addPictureButton .
					'&nbsp;&nbsp;<div id="save_button_container" class="alignleft" >' . EvaDisplayInput::afficherInput('button', $idBouttonEnregistrer, __('Enregistrer', 'evarisk'), null, '', $idBouttonEnregistrer, false, true, '', 'button-primary', '', '', '', 'left', false, '12') .
					'</div><div id="save_in_progress" class="alignright digirisk_hide" ><img src="' . admin_url('images/loading.gif') . '" alt="loading in progress" /></div></div>';
			}
			else{
				$sub_task_creation_form .=
					'<div class="alignright button-primary" id="ActionSaveButton" >' .
						__('Cette action est sold&eacute;e, vous ne pouvez pas la modifier', 'evarisk') .
					'</div>';
			}
		}

		$sub_task_creation_form .= '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#cout_activite").keypad({
			keypadOnly: false,
		});
		jQuery("#planned_time_hour").keypad({
			keypadOnly: false,
		});
		jQuery("#planned_time_minutes").keypad({
			keypadOnly: false,
		});

		jQuery("#ActionSaveButton").children("br").remove();
		jQuery("#ActionSaveButton div").children("br").remove();

		jQuery("#putTodayActionStart").click(function(){
			jQuery("#date_debut_activite").val("' . date('Y-m-d') . '");
		});
		jQuery("#putTodayActionEnd").click(function(){
			jQuery("#date_fin_activite").val("' . date('Y-m-d') . '");
		});

		jQuery("#' . $idBouttonEnregistrer . '").click(function(){
			jQuery("#informationGeneralesActivite").submit();
		});

		jQuery("#add_picture_alert").dialog({
			autoOpen:false,
			height:400,
			width:800,
			modal:true
		});
		jQuery("#add_control_picture").click(function(){';
		if (isset($arguments['requested_action']) && ($arguments['requested_action'] != 'demandeAction')) {
			$sub_task_creation_form .= '
			var variables = new Array;
			jQuery("#divVariablesFormRisque-simpleFAC input").each(function(){
				variables.push({var: jQuery(this).attr("name"), val: jQuery(this).val()});
			});
			jQuery("#add_picture_alert").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
				"post":"true",
				"table":"' . TABLE_RISQUE . '",
				"act":"load_quote_validation",
				"idProvenance": "' . (!empty($_REQUEST['idProvenance']) ? $_REQUEST['idProvenance'] : ( !empty($arguments['idProvenance']) ? $arguments['idProvenance'] : '' )) . '",
				"tableProvenance": "' . (!empty($_REQUEST['tableProvenance']) ? $_REQUEST['tableProvenance'] : ( !empty($arguments['tableProvenance']) ? $arguments['tableProvenance'] : '' )) . '",
				"vars": variables,
				"new_description" : jQuery("#descriptionFormRisque").val()
			});
			jQuery("#add_picture_alert").dialog("open");';
		}
		else{
			$sub_task_creation_form .= '
			jQuery("#act").val("add_control_picture");
			jQuery("#informationGeneralesActivite").submit();';
		}
		$sub_task_creation_form .= '
		});

		jQuery("#' . $idBouttonSold . '").click(function(){
			if(((jQuery("#avancement_activite").val() == "100") && (jQuery("#avancement_activite").val() == "100")) || ("' . $alertWhenMarkActionAsDone . '" == "non") || (("' . $alertWhenMarkActionAsDone . '" == "oui") && confirm((digi_html_accent_for_js("' . __("Vous &eacute;tes sur le point de solder une action dont l\'avancement est de #avancement#%.#retour#Etes vous sur de vouloir continuer ?", 'evarisk') . '").replace("#avancement#", jQuery("#avancement_activite").val())).replace("#retour#", "\r\n")))){
				jQuery("#act").val("actionDone");
				jQuery("#actionDone").html(\'<img src="' . PICTO_LOADING_ROUND . '" alt="loading" />\');
				jQuery("#informationGeneralesActivite").submit();
			}
		});

		jQuery("#informationGeneralesActivite").ajaxForm({
			target: "#ajax-response",
			beforeSubmit: validate_activity_form,
			resetForm: true
		});

		jQuery(".correctiv_action_efficiency_control_slider").slider({
			value:0,
			min: 0,
			max: 100,
			step: 1,
			slide: function(event, ui){
				jQuery("#" + jQuery(this).attr("id").replace("correctiv_action_efficiency_control_slider", "correctiv_action_efficiency_control")).val( ui.value );
			}
		});
	});

	function validate_activity_form(formData, jqForm, options){
		if(digirisk("#' . $idTitre . '").is(".form-input-tip")){
			digirisk("#' . $idTitre . '").val("");
			digirisk("#' . $idTitre . '").removeClass("form-input-tip");
		}

		for(var i=0; i < formData.length; i++){
			if((formData[i].name == "' . $idTitre . '") && !formData[i].value){
				alert(digi_html_accent_for_js("' . __("Vous n\'avez pas donn&eacute; de nom a l'action", 'evarisk') . '"));
				return false;
			}
			else if((formData[i].name == "responsable_activite") && (!formData[i].value || (formData[i].value <= 0)) && ("' . $idResponsableIsMandatory . '" == "oui")){
				alert(digi_html_accent_for_js("' . __("Vous devez choisir une personne en charge de l\'action", 'evarisk') . '"));
				return false;
			}
		}

		if(jQuery("#ask_correctiv_action_picture_form").length > 0){
			jQuery("#ask_correctiv_action_picture_form").html("");
		}

		jQuery("#save_button_container").hide();
		jQuery("#save_in_progress").show();

		return true;
	}

</script>';

		if(!empty($arguments['output_mode']) && $arguments['output_mode'] == 'return'){
			return $sub_task_creation_form;
		}

		echo $sub_task_creation_form;
	}

}