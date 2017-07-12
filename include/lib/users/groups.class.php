<?php
/**
 * Different users' group management
 *
 * Define the different method to manage the different users' group type (evaluators, employees)
 * @author Evarisk <dev@evarisk.com>
 * @version 5.1.2.9
 * @package Digirisk
 * @subpackage librairies
 */

/**
 * Define the different method to manage the different users' group type (evaluators, employees)
 * @package Digirisk
 * @subpackage librairies
 */
class digirisk_groups {
	/**
	*	Define the database table to ue un the entire script
	*/
	const dbTable = DIGI_DBT_USER_GROUP;

	/**
	*	Creation of the element management page
	*/
	function elementMainPage()
	{
		$output = $message = '';
		$action = isset($_REQUEST['action']) ? digirisk_tools::IsValid_Variable($_REQUEST['action']) : '';
		$save = isset($_REQUEST['save']) ? digirisk_tools::IsValid_Variable($_REQUEST['save']) : '';
		$formAction = isset($_REQUEST[self::dbTable . '_action']) ? digirisk_tools::IsValid_Variable($_REQUEST[self::dbTable . '_action']) : '';
		$id = isset($_REQUEST['id']) ? digirisk_tools::IsValid_Variable($_REQUEST['id']) : '';
		$editionInProgress = false;

		$actionResult = digirisk_groups::elementAction();
		if(($actionResult == 'done') || ($actionResult == 'nothingToUpdate'))
		{
			$message = '<img src="' . EVA_MESSAGE_SUCCESS . '" alt="' . $actionResult . '" class="messageIcone" />' . __('Le groupe a &eacute;t&eacute; correctement enregistr&eacute;', 'evarisk');
			if($formAction == 'delete')
			{
			$message = '<img src="' . EVA_MESSAGE_SUCCESS . '" alt="' . $actionResult . '" class="messageIcone" />' . __('Le groupe a &eacute;t&eacute; correctement supprim&eacute;', 'evarisk');
			}
		}
		elseif(($actionResult == 'error'))
		{
			$message = '<img src="' . EVA_MESSAGE_ERROR . '" alt="' . $actionResult . '" class="messageIcone" />' . __('Une erreur est survenue lors de l\'enregistrement du groupe', 'evarisk');
		}
		elseif($save == 'ok')
		{
			$message = '<img src="' . EVA_MESSAGE_SUCCESS . '" alt="' . $actionResult . '" class="messageIcone" />' . __('Le groupe a &eacute;t&eacute; correctement ajout&eacute;', 'evarisk');
		}

		if((($action == 'view') || ($action == 'edit') && ($id > 0)) || ($action == 'add') || ($action == 'emadd') || ($action == 'evadd'))
		{
			/*	On v�rifie que l'utilisateur a bien les droits sur la page courante, sinon on lui affiche un message en le remettant sur la page principale	*/
			$userHasAccess = '';
			/*	Get informations about the current element being edited	*/
			$currentEditedElement = digirisk_groups::getElement($id);

			/*	Check if the wanted element realy exist	*/
			if((count($currentEditedElement) > 0) && ($action != 'add') && ($action != 'emadd') && ($action != 'evadd'))
			{
				$editionPageTitle = sprintf(__('&Eacute;dition du groupe: %s', 'evarisk'), '<span class="digiriskUserGroupEditionName" >' . stripslashes($currentEditedElement[0]->name) . '</span>');
				$editionInProgress = true;
				/*	Si on est dans le cas d'un ajout de groupe d'utilisateur	*/
				if($currentEditedElement[0]->group_type == 'evaluator')
				{
					if(!current_user_can('digi_edit_evaluator_group') && !current_user_can('digi_view_detail_evaluator_group'))
					{
						$editionInProgress = false;
						$userHasAccess = 'userNotAllowed';
					}
					elseif(current_user_can('digi_view_detail_evaluator_group'))
					{
						$editionInProgress = true;
						$userHasAccess = '';
					}
				}
				/*	Si on est dans le cas d'un ajout de groupe d'�valuateur	*/
				if($currentEditedElement[0]->group_type == 'employee')
				{
					if(!current_user_can('digi_edit_user_group') && !current_user_can('digi_view_detail_user_group'))
					{
						$editionInProgress = false;
						$userHasAccess = 'userNotAllowed';
					}
					elseif(current_user_can('digi_view_detail_user_group'))
					{
						$editionInProgress = true;
						$userHasAccess = '';
					}
				}
			}
			elseif(($action == 'add') || ($action == 'emadd') || ($action == 'evadd'))
			{
				$moreTitle = __('d\'utilisateur', 'evarisk');
				if($action == 'evadd')
				{
					$moreTitle = __('d\'&eacute;valuateur', 'evarisk');
				}
				$id = '';
				$currentEditedElement = '';
				$editionPageTitle = sprintf(__('Ajouter un groupe %s', 'evarisk'), $moreTitle);
				$editionInProgress = true;
				/*	Si on est dans le cas d'un ajout de groupe d'utilisateur	*/
				if(!current_user_can('digi_add_evaluator_group') && !current_user_can('digi_add_user_group') && ($action == 'add'))
				{
					$editionInProgress = false;
					$userHasAccess = 'userNotAllowed';
				}
				/*	Si on est dans le cas d'un ajout de groupe d'utilisateur	*/
				if(!current_user_can('digi_add_evaluator_group') && ($action == 'evadd'))
				{
					$editionInProgress = false;
					$userHasAccess = 'userNotAllowed';
				}
				/*	Si on est dans le cas d'un ajout de groupe d'�valuateur	*/
				if(!current_user_can('digi_add_user_group') && ($action == 'emadd'))
				{
					$editionInProgress = false;
					$userHasAccess = 'userNotAllowed';
				}
			}
			/*	On v�rifie si l'utilisateur peut acc�der � la page qu'il demande	*/
			if($userHasAccess == 'userNotAllowed')
			{
				$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Vous n\'&ecirc;tes pas autoris&eacute; &agrave; utiliser cette fonctionnalit&eacute;', 'evarisk') . '</strong>');
				$output .=
'<script type="text/javascript">
digirisk(document).ready(function(){
	actionMessageShow("#message", "' . $message . '");
	setTimeout(\'actionMessageHide("#message")\',7500);
});
</script>';
			}
		}

		if(!$editionInProgress)
		{	/*	In case that we are on the listing page	*/
			/*	Output the list of employees groups	*/
			if(current_user_can('digi_view_user_group'))
			{
				$output .= EvaDisplayDesign::afficherDebutPage(__('Groupes d\'utilisateurs', 'evarisk'), DIGI_USER_GROUP_ICON_S, __('Groupes d\'utilisateurs', 'evarisk'), __('Groupes d\'utilisateurs', 'evarisk'), self::dbTable, false, $message, false);
				if(current_user_can('digi_add_user_group'))
				{
					$output .= '<h2 class="clear" ><a href="' . admin_url('users.php?page=' . DIGI_URL_SLUG_USER_GROUP . '&amp;action=emadd') . '" class="button add-new-h2" >' . __('Ajouter un groupe d\'utilisateur', 'evarisk') . '</a></h2>';
				}
				$elementList = digirisk_groups::getElement('', "'valid', 'moderated'", 'employee');
				$output .= digirisk_groups::elementList($elementList, 'employee');
			}

			/*	Output the list of evaluators groups	*/
			if(current_user_can('digi_view_evaluator_group'))
			{
				$elementList = digirisk_groups::getElement('', "'valid', 'moderated'", 'evaluator');
				$output .= '<div class="icon32"><img alt="digirisk_evaluator_icon" src="' . DIGI_USER_GROUP_ICON_S . '"title="' . __('Groupes d\'utilisateurs', 'evarisk') . '"/></div>
				<h2 class="alignleft" >' . __('Groupes d\'&eacute;valuateurs', 'evarisk') . '</h2>';
				if(current_user_can('digi_add_evaluator_group'))
				{
					$output .= '<h2 class="clear" ><a href="' . admin_url('users.php?page=' . DIGI_URL_SLUG_USER_GROUP . '&amp;action=evadd') . '" class="button add-new-h2" >' . __('Ajouter un groupe d\'&eacute;valuateur', 'evarisk') . '</a></h2>';
				}
				$output .= digirisk_groups::elementList($elementList, 'evaluator');
			}

			/*	Ajoute le formulaire de suppression d'un element	*/
			$output .= '<form method="post" id="' . self::dbTable . '_delete_form" action="" ><input type="hidden" name="' . self::dbTable . '_action" id="' . self::dbTable . '_action" value="delete" /><input type="hidden" name="' . self::dbTable . '[id]" id="' . self::dbTable . '_delete_form_id" value="" /></form>';
		}
		else
		{	/*	In case that we are on the edition/addition page	*/
			/*	Start the page content	*/
			$output .= EvaDisplayDesign::afficherDebutPage($editionPageTitle, DIGI_USER_GROUP_ICON_S, __('Groupes d\'utilisateurs', 'evarisk'), __('Groupes d\'utilisateurs', 'evarisk'), self::dbTable, false, $message, false);

			/*	Add the form to edit the element	*/
			$output .= digirisk_groups::elementEdition($currentEditedElement, $id);
		}

		/*	Close the page content	*/
		$output .= EvaDisplayDesign::afficherFinPage();

		if(($actionResult != '') || ($save == 'ok'))
		{
			$output .= '
<script type="text/javascript" >
	digirisk("#message").addClass("updated");
</script>';
		}

		echo $output;
	}

	/**
	*	Regroup the different action to manage the element
	*/
	function elementAction() {
		global $wpdb;
		global $current_user;

		/*	Initialize the different vars usefull for the action	*/
		$pageMessage = $actionResult = '';
		$action = isset($_REQUEST[self::dbTable . '_action']) ? digirisk_tools::IsValid_Variable($_REQUEST[self::dbTable . '_action']) : '';
		$id = isset($_REQUEST[self::dbTable]['id']) ? digirisk_tools::IsValid_Variable($_REQUEST[self::dbTable]['id']) : '';

		/*	Basic action 	*/
		if (($action != '') && (($action == 'edit') || ($action == 'editandcontinue'))) {/*	Edit action	*/
			$_REQUEST[self::dbTable]['last_update_date'] = current_time('mysql', 0);
			$actionResult = eva_database::update($_REQUEST[self::dbTable], $id, self::dbTable);
		}
		else if(($action != '') && (($action == 'delete'))) {/*	Delete action	*/
			$_REQUEST[self::dbTable]['deletion_date'] = current_time('mysql', 0);
			$_REQUEST[self::dbTable]['deletion_user_id'] = $current_user->ID;
			$_REQUEST[self::dbTable]['status'] = 'deleted';
			$actionResult = eva_database::update($_REQUEST[self::dbTable], $id, self::dbTable);
		}
		else if (($action != '') && (($action == 'save') || ($action == 'saveandcontinue') || ($action == 'add') || ($action == 'emadd') || ($action == 'evadd'))) {/*	Add action	*/
			$_REQUEST[self::dbTable]['creation_date'] = current_time('mysql', 0);
			$_REQUEST[self::dbTable]['creation_user_id'] = $current_user->ID;
			$actionResult = eva_database::save($_REQUEST[self::dbTable], self::dbTable);
			$id = $wpdb->insert_id;
			wp_redirect(admin_url('users.php?page=' . DIGI_URL_SLUG_USER_GROUP . "&action=edit&id=" . $id . "&save=ok"));
		}

		/*	Additionnal action	*/
		if (($action != '') && (($actionResult == 'done') || ($actionResult == 'nothingToUpdate'))) {
			evaUserLinkElement::setLinkUserElement(self::dbTable, $id, $_REQUEST['affectedUserIdList' . self::dbTable], false);
		}

		return $actionResult;
	}
	/**
	*	Create a html table output for element list presentation
	*
	*	@param object $elementList A wordpress object containing the entire element list with the different informations to ouput
	*	@param string $table_identifier A string to defined an unique identifier for the table in order to avoid conflict into the same page when several table are present
	*
	*	@return string $elementOutputTable The html output completely build with the element's list to output
	*/
	function elementList($elementList, $table_identifier)
	{
		/*	Define the different table column and column class	*/
		unset($titres,$classes, $idLignes, $lignesDeValeurs);
		$idLignes = null;
		$idTable = 'digirisk_user_groups_' . $table_identifier;
		$titres[] = __("Identifiant", 'evarisk');
		$titres[] = __("Nom du groupe", 'evarisk');
		$titres[] = __("Description", 'evarisk');
		$titres[] = __("Nombre d'utilisateur", 'evarisk');
		$titres[] = __("D&eacute;tails", 'evarisk');
		$titres[] = __("Informations", 'evarisk');
		$titres[] = __("Actions", 'evarisk');
		$classes[] = 'digirisk_user_groups_column_id';
		$classes[] = 'digirisk_user_groups_column_name';
		$classes[] = 'digirisk_user_groups_column_description';
		$classes[] = 'digirisk_user_groups_column_usernumber';
		$classes[] = 'digirisk_user_groups_column_userdetail';
		$classes[] = 'digirisk_user_groups_column_infos';
		$classes[] = 'digirisk_user_groups_column_action';

		unset($ligneDeValeurs);
		$i=0;
		if(count($elementList) > 0)
		{
			foreach($elementList as $element)
			{
				/*	Get the group's user list	*/
				$userListOutput = '  ';
				$userList = explode(',', $element->userList);
				foreach($userList as $userId)
				{
					if($userId > 0)
					{
						$userInformations = evaUser::getUserInformation($userId);
						$userListOutput .= $userInformations[$userId]['user_lastname'] . '&nbsp;' . $userInformations[$userId]['user_firstname'] . ', ';
					}
				}
				$userListOutput = substr($userListOutput, 0, -2);
				if($userListOutput == '')
				{
					$userListOutput = __('Aucun utilisateur dans ce groupe', 'evarisk');
				}

				/*	Define each line id for the table	*/
				$idLignes[] = 'digirisk_users_groups_' . $element->id;

				/*	Define each column value for each line	*/
				$lignesDeValeurs[$i][] = array('value' => ELEMENT_IDENTIFIER_GPU . $element->id, 'class' => 'digirisk_user_groups_cell_id');
				$lignesDeValeurs[$i][] = array('value' => $element->name, 'class' => 'digirisk_user_groups_cell_name');
				$lignesDeValeurs[$i][] = array('value' => $element->description, 'class' => 'digirisk_user_groups_cell_description');
				$lignesDeValeurs[$i][] = array('value' => $element->userNumber, 'class' => 'digirisk_user_groups_cell_usernumber');
				$lignesDeValeurs[$i][] = array('value' => $userListOutput, 'class' => 'digirisk_user_groups_cell_userdetail');
				$lignesDeValeurs[$i][] = array('value' => sprintf(__('Cr&eacute;&eacute; le %s', 'evarisk'), mysql2date('d M Y', $element->creation_date, true)) . '<br/>' . sprintf(__('Modifi&eacute; le %s', 'evarisk'), mysql2date('d M Y', $element->last_update_date, true)), 'class' => 'digirisk_user_groups_cell_infos');
				$userGroupAction = '';
				if($table_identifier == 'employee')
				{
					if(current_user_can('digi_delete_user_group'))
					{
						$userGroupAction .= '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'delete_vs.png" alt="' . __('Supprimer ce groupe', 'evarisk') . '" title="' . __('Supprimer ce groupe', 'evarisk') . '" class="alignright deleteGroup" />';
					}
					if(current_user_can('digi_edit_user_group'))
					{
						$userGroupAction .= '<a href="' . admin_url('users.php?page=' . DIGI_URL_SLUG_USER_GROUP . "&amp;action=edit&amp;id=" . $element->id) . '" ><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'edit_vs.png" alt="' . __('&Eacute;diter ce groupe', 'evarisk') . '" title="' . __('&Eacute;diter ce groupe', 'evarisk') . '" class="alignright editGroup" /></a>';
					}
					elseif(current_user_can('digi_view_detail_user_group'))
					{
						$userGroupAction .= '<a href="' . admin_url('users.php?page=' . DIGI_URL_SLUG_USER_GROUP . "&amp;action=view&amp;id=" . $element->id) . '" ><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'view_vs.png" alt="' . __('Voir ce groupe', 'evarisk') . '" title="' . __('Voir ce groupe', 'evarisk') . '" class="alignright editGroup" /></a>';
					}
				}
				elseif($table_identifier == 'evaluator')
				{
					if(current_user_can('digi_delete_evaluator_group'))
					{
						$userGroupAction .= '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'delete_vs.png" alt="' . __('Supprimer ce groupe', 'evarisk') . '" title="' . __('Supprimer ce groupe', 'evarisk') . '" class="alignright deleteGroup" />';
					}
					if(current_user_can('digi_edit_evaluator_group') || current_user_can('digi_view_detail_evaluator_group'))
					{
						$userGroupAction .= '<a href="' . admin_url('users.php?page=' . DIGI_URL_SLUG_USER_GROUP . "&amp;action=edit&amp;id=" . $element->id) . '" ><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'edit_vs.png" alt="' . __('&Eacute;diter ce groupe', 'evarisk') . '" title="' . __('&Eacute;diter ce groupe', 'evarisk') . '" class="alignright editGroup" /></a>';
					}
					elseif(current_user_can('digi_view_detail_evaluator_group'))
					{
						$userGroupAction .= '<a href="' . admin_url('users.php?page=' . DIGI_URL_SLUG_USER_GROUP . "&amp;action=view&amp;id=" . $element->id) . '" ><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'edit_vs.png" alt="' . __('Voir ce groupe', 'evarisk') . '" title="' . __('Voir ce groupe', 'evarisk') . '" class="alignright editGroup" /></a>';
					}
				}
				$lignesDeValeurs[$i][] = array('value' => $userGroupAction, 'class' => 'digirisk_user_groups_cell_action');
				$i++;
			}
		}
		else
		{
			/*	Define the line id when no result is found	*/
			$idLignes[] = 'no_users_groups';

			/*	Define the line content when no result is found	*/
			$lignesDeValeurs[$i][] = array('value' => __('Aucun groupe d\'utilisateur n\'a &eacute;t&eacute; trouv&eacute;', 'evarisk'), 'class' => '');
			$lignesDeValeurs[$i][] = array('value' => '', 'class' => '');
			$lignesDeValeurs[$i][] = array('value' => '', 'class' => '');
			$lignesDeValeurs[$i][] = array('value' => '', 'class' => '');
			$lignesDeValeurs[$i][] = array('value' => '', 'class' => '');
			$lignesDeValeurs[$i][] = array('value' => '', 'class' => '');
			$lignesDeValeurs[$i][] = array('value' => '', 'class' => '');
		}

		/*	Transform the html table into a "datatable" (jqueyr plugin) table	*/
		/*	For option adding see jqueyr datatable documentation	*/
		$script = '
<script type="text/javascript">
	digirisk(document).ready(function(){
		digirisk("#' . $idTable . ' tfoot").remove();
		digirisk("#' . $idTable . '").dataTable({
			"bInfo": false,
			"bLengthChange": false,
			"oLanguage":{
				"sUrl": "' . EVA_INC_PLUGIN_URL . 'js/dataTable/jquery.dataTables.common_translation.txt"
			}
		});
	});
	digirisk(".deleteGroup").click(function(){
		if(confirm(digi_html_accent_for_js("' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer ce groupe?', 'evarisk') . '"))){
			var clickedId = digirisk(this).parent("td").parent("tr").attr("id").replace("digirisk_users_groups_", "");
			digirisk("#' . self::dbTable . '_delete_form_id").val(clickedId);
			digirisk("#' . self::dbTable . '_delete_form").submit();
		}
	});
</script>';

		/*	Call the table display function	*/
		$elementOutputTable = EvaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script);

		return $elementOutputTable;
	}
	/**
	*	Get informations about an element into database
	*
	*	@param integer $id optionnal The identifier of the element we want to get
	*	@param string $status optionnal Allows to define if we want to get the entire list of element or just element that have a specific status
	*	@param string $type optionnal The type of the element we want to get
	*
	*	@return object|array A wordpress object with the element informations on case that the request works fine. In the other case return an empty array
	*/
	function getElement($id = '', $status = "'valid', 'moderated'", $type = '')
	{
		global $wpdb;
		$element = array();
		$moreQuery = "";

		if($id != '')
		{
			$moreQuery .= " AND GP.id = '" . $wpdb->escape($id) . "' ";
		}
		if($type != '')
		{
			$moreQuery .= " AND GP.group_type = '" . $wpdb->escape($type) . "' ";
		}

		$query = $wpdb->prepare(
			"SELECT GP.*, COUNT( DISTINCT(GROUP_USER_DETAILS.id_user) ) AS userNumber, GROUP_CONCAT( GROUP_USER_DETAILS.id_user , ',' SEPARATOR '') AS userList
			FROM " . self::dbTable . " AS GP
				LEFT JOIN " . TABLE_LIAISON_USER_ELEMENT . " AS GROUP_USER_DETAILS ON ((GROUP_USER_DETAILS.id_element = GP.id) AND (GROUP_USER_DETAILS.table_element = '" . self::dbTable . "') AND (GROUP_USER_DETAILS.status = 'valid'))
			WHERE GP.status IN (" . $status . ")
				" . $moreQuery . "
			GROUP BY GP.id", ""
		);
		$element = $wpdb->get_results($query);

		return $element;
	}
	/**
	*	Return the different button to save the item currently being added or edited
	*
	*	@return string $currentPageButton The html output code with the different button to add to the interface
	*/
	function getPageFormButton()
	{
		$action = isset($_REQUEST['action']) ? digirisk_tools::IsValid_Variable($_REQUEST['action']) : 'add';
		$currentPageButton = '';

		if(($action == 'add') || ($action == 'evadd') || ($action == 'emadd'))
		{
			if((($action == 'evadd') && current_user_can('digi_edit_evaluator_group')) || (($action == 'emadd') && current_user_can('digi_edit_user_group')))
			{
				$currentPageButton .= '<input type="submit" class="button-primary" id="add" name="add" value="' . __('Ajouter', 'evarisk') . '" />';
			}
		}
		elseif(current_user_can('digi_edit_user_group'))
		{
			$currentPageButton .= '<input type="submit" class="button-primary" id="save" name="save" value="' . __('Enregistrer', 'evarisk') . '" />';
			//<input type="button" class="button-primary" id="saveandcontinue" name="saveandcontinue" value="' . __('Enregistrer et continuer l\'&eacute;dition', 'evarisk') . '" />';
		}
		if(($action != 'add') && ($action != 'evadd') && ($action != 'emadd') && current_user_can('digi_delete_user_group'))
		{
			$currentPageButton .= '<input type="button" class="button-primary" id="delete" name="delete" value="' . __('Supprimer', 'evarisk') . '" />';
		}

		$currentPageButton .= '<a href="' . admin_url('users.php?page=' . DIGI_URL_SLUG_USER_GROUP) . '" class="button add-new-h2" >' . __('Retour', 'evarisk') . '</a>';

		return $currentPageButton;
	}
	/**
	*	Define the form to output for the element
	*
	*	@param array $elementInformations The different informations about the element to edit, stored into an array
	*
	*	@return string $elementEditionOutput An html output with the complete edition form for the current element
	*/
	function elementEdition($elementInformations = '', $currentElementId)
	{
		$elementEditionOutput = '';
		$dbFieldToHide = array('old_id', 'creation_user_id', 'deletion_date', 'deletion_user_id');
		$action = isset($_REQUEST['action']) ? digirisk_tools::IsValid_Variable($_REQUEST['action']) : 'add';

		if($action == 'edit')
		{
			$dbFieldToHide = array_merge($dbFieldToHide/* , array('group_type') */);
		}

		$dbFieldList = eva_database::fields_to_input(self::dbTable);
		$the_form_content_hidden = $the_form_general_content = '';
		foreach ($dbFieldList as $input_key => $input_def) {
			if (!in_array($input_def['name'], $dbFieldToHide)) {
				$requestFormValue = isset($_REQUEST[self::dbTable][$input_def['name']]) ? digirisk_tools::IsValid_Variable($_REQUEST[self::dbTable][$input_def['name']]) : '';

				if (is_array($elementInformations)) {
					$input_def['value'] = $elementInformations[0]->$input_def['name'];
				}
				elseif (($action != '') && ($requestFormValue != '')) {
					$input_def['value'] = $requestFormValue;
				}
				$input_def['value'] = stripslashes($input_def['value']);

				if($input_def['name'] == 'group_type')
				{
					if ($action == 'emadd') {
// 						$input_def['type'] = 'hidden';
						$input_def['value'] = 'employee';
					}
					elseif ($action == 'evadd') {
// 						$input_def['type'] = 'hidden';
						$input_def['value'] = 'evaluator';
					}
					else {
						foreach ($input_def['possible_value'] as $possible_value_key => $possible_value) {
							if (!current_user_can('digi_add_user_group') && ($possible_value == 'employee')) {
								unset($input_def['possible_value'][$possible_value_key]);
							}
							elseif (!current_user_can('digi_add_evaluator_group') && ($possible_value == 'evaluator')) {
								unset($input_def['possible_value'][$possible_value_key]);
							}
						}
					}
				}
				$the_input = digirisk_form::check_input_type($input_def, self::dbTable);

				if ($input_def['type'] != 'hidden') {
					$label = 'for="' . $input_def['name'] . '"';
					if (($input_def['type'] == 'radio') || ($input_def['type'] == 'checkbox')) {
						$label = '';
					}
					$input = '
				<div class="clear" >
					<div class="digirisk_form_label digirisk_attr_' . $input_def['name'] . '_label alignleft" >
						<label ' . $label . ' >' . __($input_def['name'], 'evarisk') . '</label>
					</div>
					<div class="digirisk_form_input digirisk_attr_' . $input_def['name'] . '_input alignleft" >
						' . $the_input . '
					</div>
				</div>';
					$the_form_general_content .= $input;
				}
				else {
					$the_form_content_hidden .= '
			' . $the_input;
				}
			}
		}

		$elementEditionOutput = '
<form action="" method="post" id="' . self::dbTable . '_form" >
	<input type="hidden" name="' . self::dbTable . '_action" id="' . self::dbTable . '_action" value="' . $action . '" />
	' . $the_form_content_hidden . '
	' . $the_form_general_content . '
	<fieldset class="clear digiriskUserGroupDetails" >
		<legend>' . __('Utilisateurs du groupe', 'evarisk') . '</legend>
	' . evaUserLinkElement::afficheListeUtilisateur(self::dbTable, $currentElementId, false) . '
	</fieldset>
	<div id="pageHeaderButtonContainer" class="pageHeaderButton" >' . digirisk_groups::getPageFormButton() . '</div>
</form>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		digirisk("#delete").click(function(){
			if(confirm(digi_html_accent_for_js("' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer ce groupe ?', 'evarisk') . '"))){
				digirisk("#' . self::dbTable . '_action").val("delete");
				digirisk("#' . self::dbTable . '_form").submit();
			}
		});
	});
</script>';

		return $elementEditionOutput;
	}


	/**
	*	Get the identifier of the groups bind with an element
	*
	*	@param integer $idElement The identifier of the element (in its table) we want to bind
	*	@param string $tableElement The table of the element we want to bind
	*
	*	@return object $bindedGroups A wordpress database object with the different groups' id that are binded to the given element
	*/
	function getBindGroups($idElement, $tableElement)
	{
		global $wpdb;

		$idElement = ($idElement);
		$tableElement = ($tableElement);

		$query = $wpdb->prepare(
		"SELECT GP_LINK.*
		FROM " . DIGI_DBT_LIAISON_USER_GROUP . " AS GP_LINK
		WHERE GP_LINK.table_element = '%s'
			AND GP_LINK.id_element = %d
			AND GP_LINK.status = 'valid'
		", $tableElement, $idElement);
		$bindedGroups = $wpdb->get_results($query);

		return $bindedGroups;
	}
	/**
	*	Get informations about the different group binded to an element
	*
	*	@param integer $idElement The identifier of the element (in its table) we want to bind
	*	@param string $tableElement The table of the element we want to bind
	*
	*	@return array $groups An array containing the groups informations
	*/
	function getBindGroupsWithInformations($idElement, $tableElement)
	{
		$groups = array();

		$bindGroups = digirisk_groups::getBindGroups($idElement, $tableElement);
		foreach($bindGroups as $groupDefinition)
		{
			$groupeInfos = digirisk_groups::getElement($groupDefinition->id_group, $status = "'valid'");
			foreach($groupeInfos as $groupe)
			{
				$groups[$groupDefinition->id_group]['id'] = $groupDefinition->id_group;
				$groups[$groupDefinition->id_group]['name'] = $groupe->name;
				$groups[$groupDefinition->id_group]['description'] = $groupe->description;
				$groups[$groupDefinition->id_group]['userNumber'] = $groupe->userNumber;
				$groups[$groupDefinition->id_group]['userList'] = $groupe->userList;
			}
		}

		return $groups;
	}

	/**
	*	Define the affectation box
	*
	*	@param array $arguments An array with the _POST vars sent in order to specify the content we want to have
	*	@param array $moreArgs An array we can specify on callback function call to add more parameters to our function
	*
	*	@return string The postbox output
	*/
	function groupPostBox($arguments, $moreArgs = '')
	{
		$idElement = $arguments['idElement'];
		$tableElement = $arguments['tableElement'];

		if(is_array($moreArgs))
		{
			if(isset($moreArgs['args']['groupType']))
			{
				$tableElement = $tableElement . '_' . $moreArgs['args']['groupType'];
			}
		}

		echo '
<div style="display:none;" id="messageInfo_' . $tableElement . '_' . $idElement . '_affectGroups" ></div>
<div id="groupeList' . $tableElement . '" >' . digirisk_groups::affectationPostBoxContent($tableElement, $idElement) . '</div>';
	}
	/**
	*	Create the content of the affectation box
	*
	*	@param string $tableElement The element type we want to affect something to
	*	@param string $idElement The element identifier we want to affect something to
	*	@param boolean $showButton Allows to specify if the save button must be shown or not
	*
	*	@return string $output The html code that contains the box content to output
	*/
	function affectationPostBoxContent($tableElement, $idElement, $showButton = true)
	{
		$output = '';
		$alreadyLinked = $alreadyLinkedListOutput = '';
		$idBoutonEnregistrer = 'affectation_' . $tableElement;

		/*	Get the list of element already linked	*/
		$linkedElementList = array();
		$linkedElement = digirisk_groups::getBindGroups($idElement, $tableElement);
		if(is_array($linkedElement ) && (count($linkedElement) > 0))
		{
			foreach($linkedElement as $element)
			{
				$linkedElementList[$element->id_group] = $element;
				$alreadyLinked .= $element->id_group . ', ';
				$currentElement = digirisk_groups::getElement($element->id_group);
				$alreadyLinkedListOutput .= '<div class="selectedelementGPU" id="affectedElement' . $tableElement . $element->id_group . '" title="' . __('Cliquez pour supprimer', 'evarisk') . '" >' . ELEMENT_IDENTIFIER_GPU . $element->id_group . '&nbsp;-&nbsp;' . $currentElement[0]->name . '<div class="ui-icon deleteElementFromList" >&nbsp;</div></div>';
			}
		}
		else
		{
			$alreadyLinkedListOutput = '<span id="noElementSelected' . $tableElement . '" class="noElementLinked" >' . __('Aucun groupe affect&eacute;', 'evarisk') . '</span>';
		}

		$output = '
<input type="hidden" name="actuallyAffectedList' . $tableElement . '" id="actuallyAffectedList' . $tableElement . '" value="' . $alreadyLinked . '" />
<input type="hidden" name="affectedList' . $tableElement . '" id="affectedList' . $tableElement . '" value="' . $alreadyLinked . '" />

<div class="alignleft affectationCompleteListOutput" >
	<div id="affectedListOutput' . $tableElement . '" class="affectedElementListOutput ui-widget-content clear" >' . $alreadyLinkedListOutput . '</div>
</div>

<div class="alignright" style="width:55%;" >
	<span class="alignright" >';

	switch($tableElement)
	{
		case TABLE_GROUPEMENT . '_employee':
		case TABLE_UNITE_TRAVAIL . '_employee':
			if(current_user_can('digi_add_user_group'))
			{
				$output .=
	'<a href="' . admin_url('users.php?page=' . DIGI_URL_SLUG_USER_GROUP) . '">' . __('Ajouter des groupes', 'evarisk') . '</a>';
			}
			else
			{
				$output .= '&nbsp;';
			}
		break;
		case TABLE_GROUPEMENT . '_evaluator':
		case TABLE_UNITE_TRAVAIL . '_evaluator':
			if(current_user_can('digi_add_evaluator_group'))
			{
				$output .=
	'<a href="' . admin_url('users.php?page=' . DIGI_URL_SLUG_USER_GROUP) . '">' . __('Ajouter des groupes', 'evarisk') . '</a>';
			}
			else
			{
				$output .= '&nbsp;';
			}
		break;
	}

	/*	Get the table type for the current box	*/
	$tableType = explode('_', $tableElement);

	$output .= '
	</span>
	<div class="clear addLinkElementElement" >
		<div class="clear" >
			<span class="searchElementInput ui-icon" >&nbsp;</span>
			<input class="searchElementToAffect" type="text" name="affectedElement' . $tableElement . '" id="affectedElement' . $tableElement . '" placeholder="' . __('Rechercher dans la liste des groupes', 'evarisk') . '" />
		</div>
		<div id="completeList' . $tableElement . '" class="completeList clear" >' . digirisk_groups::elementListForAffectation($tableElement, $idElement) . '</div>
	</div>
	<div id="massAction' . $tableElement . '" ><span class="checkAll" >' . __('cochez tout', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll" >' . __('d&eacute;cochez tout', 'evarisk') . '</span></div>
</div>
<div id="elementBlocContainer' . $tableElement . '" class="clear hide" ><div onclick="javascript:elementDeletion(digirisk(this).attr(\'id\'), \'' . $tableElement . '\', \'' . $idBoutonEnregistrer . '\');" class="selectedelementGPU" title="' . __('Cliquez pour supprimer', 'evarisk') . '" >#ELEMENTNAME#<span class="ui-icon deleteElementFromList" >&nbsp;</span></div></div>

<script type="text/javascript" >
	digirisk(document).ready(function(){
		/*	Mass action : check / uncheck all	*/
		jQuery("#massAction' . $tableElement . ' .checkAll").unbind("click");
		jQuery("#massAction' . $tableElement . ' .checkAll").click(function(){
			jQuery("#completeList' . $tableElement . ' .buttonActionElementLinkList' . $tableElement . '").each(function(){
				if(jQuery(this).hasClass("elementIsNotLinked")){
					jQuery(this).click();
				}
			});
		});
		jQuery("#massAction' . $tableElement . ' .uncheckAll").unbind("click");
		jQuery("#massAction' . $tableElement . ' .uncheckAll").click(function(){
			jQuery("#completeList' . $tableElement . ' .buttonActionElementLinkList' . $tableElement . '").each(function(){
				if(jQuery(this).hasClass("elementIsLinked")){
					jQuery(this).click();
				}
			});
		});

		/*	Action when click on delete button	*/
		jQuery(".selectedelementGPU").click(function(){
			elementDivId = jQuery(this).attr("id").replace("affectedElement' . $tableElement . '", "");
			deleteElementIdFiedList(elementDivId, "' . $tableElement . '");
			checkElementListModification("' . $tableElement . '", "' . $idBoutonEnregistrer . '");
		});

		/*	Autocomplete search	*/
		jQuery("#affectedElement' . $tableElement . '").autocomplete({
			source: "' . EVA_INC_PLUGIN_URL . 'liveSearch/searchGroups.php?group_type=' . $tableType[max(array_keys($tableType))] . '",
			select: function (event, ui) {
				cleanElementIdFiedList(ui.item.value, "' . $tableElement . '");
				addElementIdFieldList(ui.item.label, ui.item.value, "' . $tableElement . '");

				checkElementListModification("' . $tableElement . '", "' . $idBoutonEnregistrer . '");

				setTimeout(function(){
					jQuery("#affectedElement' . $tableElement . '").val("");
					jQuery("#affectedElement' . $tableElement . '").blur();
				}, 2);
			}
		});
	});
</script>';

		if ($showButton) {
			switch ($tableElement) {
				case TABLE_GROUPEMENT . '_employee':
				case TABLE_GROUPEMENT . '_evaluator':
					if (!current_user_can('digi_edit_groupement') && !current_user_can('digi_edit_groupement_' . $idElement)) {
						$showButton = false;
					}
				break;
				case TABLE_UNITE_TRAVAIL . '_employee':
				case TABLE_UNITE_TRAVAIL . '_evaluator':
					if (!current_user_can('digi_edit_unite') && !current_user_can('digi_edit_unite_' . $idElement)) {
						$showButton = false;
					}
				break;
			}
		}

		if($showButton)
		{//Bouton Enregistrer
			$scriptEnregistrement = '
<script type="text/javascript">
	digirisk(document).ready(function(){
		checkElementListModification("' . $tableElement . '", "' . $idBoutonEnregistrer . '");
		digirisk("#' . $idBoutonEnregistrer . '").click(function(){
			digirisk("#saveButtonLoading' . $tableElement . '").show();
			digirisk("#saveButtonContainer' . $tableElement . '").hide();
			digirisk("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
				"post": "true",
				"table": "' . DIGI_DBT_LIAISON_USER_GROUP . '",
				"act": "save",
				"element": digirisk("#affectedList' . $tableElement . '").val(),
				"tableElement": "' . $tableElement . '",
				"idElement": "' . $idElement . '"
			});
		});
	});
</script>';

			$output .= '<div class="clear" ><div id="saveButtonLoading' . $tableElement . '" style="display:none;" class="clear alignright" ><img src="' . PICTO_LOADING_ROUND . '" alt="loading in progress" /></div><div id="saveButtonContainer' . $tableElement . '" >' . EvaDisplayInput::afficherInput('button', $idBoutonEnregistrer, __('Enregistrer', 'evarisk'), null, '', 'save', false, true, '', 'button-primary alignright', '', '', $scriptEnregistrement) . '</div></div>';
		}

		return $output;
	}
	/**
	*	Build a html table with the existing element list, in order to show them for affectation
	*
	*	@param string $tableElement The type of element we want to affect something to
	*	@param integer $idElement The element identifier we want to affect something to
	*
	*	@return string $elementList_Table The html code that we have to output for displaying the existing element list
	*/
	function elementListForAffectation($tableElement, $idElement)
	{
		$elementList_Table = $script = '';
		$idBoutonEnregistrer = 'affectation_' . $tableElement;

		$idTable = 'elementList' . $tableElement . $idElement;
		$titres = array('', ucfirst(strtolower(__('Groupe', 'evarisk'))));
		$classes = array('addElementButtonDTable','');

		/*	Get the element list already linked	*/
		$linkedElementCheckList = array();
		$linkedElementList = digirisk_groups::getBindGroups($idElement, $tableElement);
		if(is_array($linkedElementList ) && (count($linkedElementList) > 0))
		{
			foreach($linkedElementList as $linkedElement)
			{
				$linkedElementCheckList[$linkedElement->id_group] = $linkedElement;
			}
		}

		/*	Get the entire element list	*/
		switch($tableElement)
		{
			case substr($tableElement, -9) == 'evaluator':
				$type = 'evaluator';
			break;
			case substr($tableElement, -8) == 'employee':
				$type = 'employee';
			break;
		}
		$completeElementList = digirisk_groups::getElement('', "'valid'", $type);

		/*	Start building the table	*/
		unset($lignesDeValeurs);
		if(is_array($completeElementList) && (count($completeElementList) > 0))
		{
			foreach($completeElementList as $element)
			{
				unset($valeurs);
				$idLigne = $tableElement . $idElement . '_elementList_' . $element->id;
				$idCbLigne = 'cb_' . $idLigne;
				$moreLineClass = 'elementIsNotLinked';
				if(isset($linkedElementCheckList[$element->id]))
				{
					$moreLineClass = 'elementIsLinked';
				}
				$valeurs[] = array('value'=>'<span id="actionButton' . $tableElement . 'ElementLink' . $element->id . '" class="buttonActionElementLinkList' . $tableElement . ' ' . $moreLineClass . '  ui-icon pointer" >&nbsp;</span>');
				$valeurs[] = array('value'=>ELEMENT_IDENTIFIER_GPU . $element->id . '&nbsp;-&nbsp;' . $element->name);
				$lignesDeValeurs[] = $valeurs;
				$idLignes[] = $idLigne;
			}
		}
		else
		{
			$valeurs[] = array('value'=>'');
			$valeurs[] = array('value'=>__('Aucun r&eacute;sultat trouv&eacute;', 'evarisk'));
			$lignesDeValeurs[] = $valeurs;
			$idLignes[] = $tableElement . $idElement . '_emptyElementList';
		}

		/*	Add the js option for the table	*/
		$script =
'<script type="text/javascript">
	digirisk(document).ready(function(){
		digirisk("#' . $idTable . '").dataTable({
			"bAutoWidth": false,
			"bInfo": false,
			"bPaginate": false,
			"bFilter": false,
			"aaSorting": [[0,"desc"]]
		});
		digirisk("#' . $idTable . '").children("tfoot").remove();
		digirisk("#' . $idTable . '_wrapper").removeClass("dataTables_wrapper");

		/*	Add the action when clicking on a button in the list	*/
		digirisk("#completeList' . $tableElement . ' .odd, #completeList' . $tableElement . ' .even").click(function(){
			if(digirisk(this).children("td:first").children("span").hasClass("elementIsNotLinked")){
				var currentId = digirisk(this).attr("id").replace("' . $tableElement . $idElement . '_elementList_", "");
				cleanElementIdFiedList(currentId, "' . $tableElement . '");

				var elementContent = digirisk(this).children("td:nth-child(2)").html();

				addElementIdFieldList(elementContent, currentId, "' . $tableElement . '");
			}
			else{
				deleteElementIdFiedList(digirisk(this).attr("id").replace("' . $tableElement . $idElement . '_elementList_", ""), "' . $tableElement . '");
			}
			checkElementListModification("' . $tableElement . '", "' . $idBoutonEnregistrer . '");
		});
	});
</script>';

		/*	Add the tabe result into the output	*/
		$elementList_Table .= evaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script);

		return $elementList_Table;
	}


	/**
	*	Create a link between an element and a group
	*
	*	@param mixed $tableElement The element type we want to create a link to
	*	@param integer $idElement The element identifier we want to create a link to
	*	@param array $element An group list id to create link with the selected element
	*
	*	@return mixed $messageInfo An html output that contain the result message
	*/
	function setLinkGroupElement($tableElement, $idElement, $element, $outputMessage = true)
	{
		global $wpdb;
		global $current_user;
		$elementToTreat = "  ";
		$messageInfoContainerIdExt = '_affectGroups';
		$idBoutonEnregistrer = 'affectation_' . $tableElement;

		/*	Get the element list already linked	*/
		$linkedElementCheckList = array();
		$linkedElementList = digirisk_groups::getBindGroups($idElement, $tableElement);
		if(is_array($linkedElementList ) && (count($linkedElementList) > 0))
		{
			foreach($linkedElementList as $linkedElement)
			{
				$linkedElementCheckList[$linkedElement->id_group] = $linkedElement;
			}
		}

		/*	Transform the new element list to affect into an array	*/
		$newElementList = explode(", ", $element);

		/*	Read the product list already linked for checking if they are again into the list or if we have to delete them form the list	*/
		foreach($linkedElementCheckList as $elements)
		{
			if(is_array($newElementList) && !in_array($elements->id_group, $newElementList))
			{
				$wpdb->update( DIGI_DBT_LIAISON_USER_GROUP, array( 'status' => 'deleted', 'date_desAffectation' => current_time('mysql', 0), 'id_desAttributeur' => $current_user->ID, ), array( 'id' => $elements->id, ) );
			}
		}
		if(is_array($newElementList) && (count($newElementList) > 0))
		{
			foreach($newElementList as $elementId)
			{
				if((trim($elementId) != '') && !array_key_exists($elementId, $linkedElementCheckList))
				{
					$elementToTreat .= "('', 'valid', '" . current_time('mysql', 0) . "', '" . $current_user->ID . "', '0000-00-00 00:00:00', '', '" . $elementId . "', '" . $idElement . "', '" . $tableElement . "'), ";
				}
			}
		}

		$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Il n\'a aucune modification a apporter', 'evarisk') . '</strong>');
		$endOfQuery = trim(substr($elementToTreat, 0, -2));
		if($endOfQuery != "")
		{
			$query = $wpdb->prepare(
				"REPLACE INTO " . DIGI_DBT_LIAISON_USER_GROUP . "
					(id, status ,date_affectation ,id_attributeur ,date_desAffectation ,id_desAttributeur ,id_group ,id_element ,table_element)
				VALUES
					" . $endOfQuery . "", ""
			);
			if($wpdb->query($query))
			{
				$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Les modifications ont correctement &eacute;t&eacute enregistr&eacute;es', 'evarisk') . '</strong>');
			}
			else
			{
				$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Les modifications n\'ont pas toutes &eacute;t&eacute correctement enregistr&eacute;es', 'evarisk') . '</strong>"');
			}
		}

		if($outputMessage)
		{
			echo
'<script type="text/javascript">
	digirisk(document).ready(function(){
		actionMessageShow("#messageInfo_' . $tableElement . '_' . $idElement . $messageInfoContainerIdExt . '", "' . $message . '");
		setTimeout(\'actionMessageHide("#messageInfo_' . $tableElement . '_' . $idElement . $messageInfoContainerIdExt . '")\',7500);
		digirisk("#saveButtonLoading' . $tableElement . '").hide();
		digirisk("#saveButtonContainer' . $tableElement . '").show();
		digirisk("#actuallyAffectedList' . $tableElement . '").val(digirisk("#affectedList' . $tableElement . '").val());
		checkElementListModification("' . $tableElement . '", "' . $idBoutonEnregistrer . '");
	});
</script>';
		}
	}


	/**
	*	Prepare the output of all the existing user groups
	*
	*	@param array $groupToRead The complete group list we have to treat
	*	@param mixed $outputType Specify wich output type is asked (html, print)
	*
	*	@return mixed $outputContent Depending on the "typeSortie" parameter, output an array or an html code
	*/
	function outputGroupListing($groupToRead, $outputType = 'html')
	{
		if($outputType == 'html')
		{
			$outputContent = '';
		}
		elseif($outputType == 'print')
		{
			$outputContent = array();
		}

		if( is_array($groupToRead) )
		{
			foreach($groupToRead as $index => $groupDefinition)
			{
				$groupDefinition->usersFromGroup = '';
				if($groupDefinition->name == '')$groupDefinition->name = '&nbsp;';
				if($groupDefinition->description == '')$groupDefinition->description = '&nbsp;';
				if($groupDefinition->userList == '')
				{
					$groupDefinition->TOTALUSERNUMBER = '0';
				}
				else
				{
					if(substr($groupDefinition->userList, -1) == ',')
					{
						$groupDefinition->userList = substr($groupDefinition->userList, 0, -1);
					}
					$groupUsers = explode(',', $groupDefinition->userList);
					$groupDefinition->TOTALUSERNUMBER = count($groupUsers);
					foreach($groupUsers as $user)
					{
						if($user > 0)
						{
							$userInformations = evaUser::getUserInformation($user);
							$groupDefinition->usersFromGroup .= $userInformations[$user]['user_lastname'] . ' ' . $userInformations[$user]['user_firstname'] . ', ';
						}
					}
				}
				if($outputType == 'html')
				{
					$outputContent .=
						'<tr>
							<td>' . $groupDefinition->name . '</td>
							<td>' . $groupDefinition->description . '</td>
							<td>' . $groupDefinition->TOTALUSERNUMBER . '</td>
							<td>' . $groupDefinition->usersFromGroup . '</td>
						</tr>';
				}
				elseif($outputType == 'print')
				{
					$outputContent[$index]['userGroupId'] = $groupDefinition->id;
					$outputContent[$index]['userGroupName'] = $groupDefinition->name;
					$outputContent[$index]['userGroupDescription'] = $groupDefinition->description;
					$outputContent[$index]['userGroupTotalUserNumber'] = $groupDefinition->TOTALUSERNUMBER;
					$outputContent[$index]['userGroupUsers'] = $groupDefinition->usersFromGroup;
				}
			}
		}

		return $outputContent;
	}

}