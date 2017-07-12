<?php
/**
* User notification management
*
* Define method to manage user notification in plugin
* @author Evarisk <dev@evarisk.com>
* @version 5.1.4.5
* @package Digirisk
* @subpackage librairies
*/

/**
* Define method to manage user notification in plugin
* @package Digirisk
* @subpackage librairies
*/
class digirisk_user_notification{
	/**
	*	Define the database table to use in the entire script
	*/
	const dbTable = DIGI_DBT_ELEMENT_NOTIFICATION;

	/**
	*	Define the different hook that will called the user notification box and the good function to use for box creation
	*/
	function user_notification_box_caller(){
		$postBoxTitle = __('Notifications des utilisateurs', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
		$postBoxId = 'postBoxUserNotify';
		add_meta_box($postBoxId, $postBoxTitle, array('digirisk_user_notification', 'user_notification_box'), PAGE_HOOK_EVARISK_TACHE, 'rightSide', 'default');
		add_meta_box($postBoxId, $postBoxTitle, array('digirisk_user_notification', 'user_notification_box'), PAGE_HOOK_EVARISK_ACTIVITE, 'rightSide', 'default');
	}

	/**
	* Return an action list limited by given parameters
	*
	*	@param array $action_infos An array with the different parameters to limited result
	*/
	function get_action($action_infos){
		global $wpdb;
		$actions = array();
		$conditions = '';

		if(is_array($action_infos) && (count($action_infos) > 0)){
			foreach($action_infos as $field_name => $field_infos){
				$conditions .= "
	AND " . $field_name . " = " . $field_infos[0];
				$conditions_value[] = $field_infos[1];
			}
		}

		$query = $wpdb->prepare(
"SELECT *
FROM " . self::dbTable . "
WHERE 1" . $conditions, $conditions_value);
		$actions = $wpdb->get_results($query);

		return $actions;
	}

	/**
	*	Allows to affect documents to corrective actions
	*/
	function user_notification_box($arguments){
		$utilisateursMetaBox = '';
		$element_identifier = $arguments['idElement'];
		$element_type = $arguments['tableElement'];

		/*	Ajout de la pop up d'�dition pour les �crans plus petits	*/
		$utilisateursMetaBox = '
<div id="userNotificationManager" class="hide" title="' . __('Notifications des utilisateurs', 'evarisk') . '" >
	<div id="userNotificationDialogMessage" class="hide" >&nbsp;</div>
	<div id="userNotificationManagerForm" class="" >&nbsp;</div>
	<div id="userNotificationManagerLoading" class="hide" >&nbsp;</div>
</div>
<div class="hide" id="message_' . $element_type . '_' . $element_identifier . '_userNotification" ></div>

<div class="clear" >
	<div id="openNotificationManagerDialog" class="alignright " ><img src="' . DIGI_OPEN_POPUP . '" alt="' . __('Ouvrir dans une fen&ecirc;tre externe', 'evarisk') . '" title="' . __('Ouvrir dans une fen&ecirc;tre externe', 'evarisk') . '" /></div>
</div>

<!--	User list -->
<div id="userNotificationContainerBox" class="clear" >' . self::get_user_notification_table($element_type, $element_identifier) . '</div>

<!--	Save button -->
<div class="clear" id="saveButtonBoxContainer" >';
	$saveButtonOuput = true;
	$user_has_right = true;
	switch($element_type){
		case TABLE_TACHE:
			$currentTask = new EvaTask($element_identifier);
			$currentTask->load();
			$ProgressionStatus = $currentTask->getProgressionStatus();

			if((($ProgressionStatus == 'Done') || ($ProgressionStatus == 'DoneByChief')) && (digirisk_options::getOptionValue('possibilite_Modifier_Tache_Soldee') == 'non') ){
				$saveButtonOuput = false;
			}
			if (!current_user_can('digi_edit_task') && !current_user_can('digi_edit_task_' . $arguments['idElement'])) {
				$saveButtonOuput = false;
				$user_has_right = false;
			}
		break;
		case TABLE_ACTIVITE:
			$current_action = new EvaActivity($element_identifier);
			$current_action->load();
			$ProgressionStatus = $current_action->getProgressionStatus();

			if((($ProgressionStatus == 'Done') || ($ProgressionStatus == 'DoneByChief')) && (digirisk_options::getOptionValue('possibilite_Modifier_Action_Soldee') == 'non') ){
				$saveButtonOuput = false;
			}
			if (!current_user_can('digi_edit_action') && !current_user_can('digi_edit_action_' . $arguments['idElement'])) {
				$saveButtonOuput = false;
				$user_has_right = false;
			}
		break;
	}
	if ( $saveButtonOuput ) {
		$utilisateursMetaBox .= '
	<div id="saveButtonLoading_userNotification' . $element_type . '" style="display:none;" class="clear alignright" ><img src="' . PICTO_LOADING_ROUND . '" alt="loading in progress" /></div>
	<div id="saveButtonContainer_userNotification' . $element_type . '" ><input type="button" value="' . __('Enregistrer', 'evarisk') . '" id="save_user_notification_' . $element_type . '" name="save_user_notification_' . $element_type . '"" class="button-primary alignright" /></div>';
	}
	else if ( $user_has_right ) {
		$utilisateursMetaBox .= '<div class="alignright button-primary" id="TaskSaveButton" >' .
					__('Cette t&acirc;che est sold&eacute;e, vous ne pouvez pas ajouter de commentaire', 'evarisk') .
				'</div>';
	}
	$utilisateursMetaBox .= '
</div>

<script type="text/javascript" >
	digirisk("#userNotificationManager").dialog({
		autoOpen: false,
		height: 600,
		width: 800,
		modal: true,
		buttons: {
			"' . __('Enregistrer et fermer', 'evarisk') . '": function(){
				jQuery("#user_notification_form").submit();
				setTimeout(digirisk(this).dialog("close"), \'1000\');
			},
			"' . __('Enregistrer', 'evarisk') . '": function(){
				jQuery("#user_notification_form").submit();
			},
			"' . __('Annuler', 'evarisk') . '": function(){
				digirisk(this).dialog("close");
			}
		},
		close: function(){
			digirisk("#userNotificationManagerForm").html("");
			digirisk("#userNotificationContainerBox").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
				"post": "true",
				"table": "' . self::dbTable . '",
				"act": "reload_user_notification_box",
				"tableElement": "' . $element_type . '",
				"idElement": "' . $element_identifier . '"
			});
			digirisk("#saveButtonBoxContainer").show();
		}
	});

	digirisk("#openNotificationManagerDialog").click(function(){
		digirisk("#userNotificationContainerBox").html("");
		digirisk("#saveButtonBoxContainer").hide();
		digirisk("#userNotificationManagerForm").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
			"post":"true",
			"table":"' . self::dbTable . '",
			"act":"reload_user_notification_box",
			"tableElement":"' . $element_type . '",
			"idElement":"' . $element_identifier . '"
		});
		digirisk("#userNotificationManager").dialog("open");
	});

	digirisk("#save_user_notification_' . $element_type . '").click(function(){
		jQuery("#saveButtonLoading_userNotification' . $element_type . '").show();
		jQuery("#saveButtonContainer_userNotification' . $element_type . '").hide();

		jQuery("#user_notification_form").submit();
	});
</script>';

		echo $utilisateursMetaBox;
	}

	/**
	*	Get the different possible notification available for the element type given in parameter
	*
	*	@param string $table_element The element type to get user and notification for
	*/
	function get_notification_list($table_element){
		global $wpdb;
		$notification_list = '';

		$query = $wpdb->prepare("
SELECT action, id, action_title
FROM " . self::dbTable . "
WHERE status = 'valid'
	AND table_element = %s", $table_element);
		$notification_list = $wpdb->get_results($query);

		return $notification_list;
	}

	/**
	*	Get the different possible notification available for the element type given in parameter
	*
	*	@param string $table_element The element type to get user and notification for
	*	@param string $id_element The element identifier to get user and notification for
	*
	*	@return object $notification_list A wordpress database object containing the list for the given element
	*/
	public static function get_link_user_notification_list($table_element, $id_element, $action = ''){
		global $wpdb;
		$notification_list = '';

		$more_query = "";
		$query_condition = array($table_element, $id_element);

		if($action != ''){
			$more_query .= "
	AND NOTI.action = %s";
			$query_condition[] = $action;
		}

		$query = $wpdb->prepare("
SELECT LUN.*, NOTI.action, NOTI.message_to_send, NOTI.message_subject
FROM " . DIGI_DBT_LIAISON_USER_NOTIFICATION_ELEMENT . " AS LUN
	INNER JOIN " . DIGI_DBT_ELEMENT_NOTIFICATION . " AS NOTI ON (NOTI.id = LUN.id_notification)
WHERE LUN.status = 'valid'
	AND LUN.table_element = %s
	AND LUN.id_element = %d" . $more_query, $query_condition);
		$notification_list = $wpdb->get_results($query);

		return $notification_list;
	}

	/**
	*	Display user list for notification configuration
	*
	*	@param string $table_element The element type to get user and notification for
	*	@param string $id_element The element identifier to get user and notification for
	*
	*	@return string $notification_box The interface allowing to make notification configuration
	*/
	function get_user_notification_table($table_element, $id_element){
		$notification_box = '';

		/*	on r�cup�re les utilisateurs affect�s � l'�l�ment en cours.	*/
		$utilisateursLies = evaUserLinkElement::getAffectedUser($table_element, $id_element);
		if(is_array($utilisateursLies) && (count($utilisateursLies) > 0)){
			$notification_list = self::get_notification_list($table_element);
			$notification_link_list = self::get_link_user_notification_list($table_element, $id_element);
			$stored_notification_link_list = array();
			foreach($notification_link_list as $notification_link){
				$stored_notification_link_list[$notification_link->id_user][] = $notification_link->id_notification;
			}
			{/*	Affichage de la liste des utilisateurs	*/
				$idTable = 'listeIndividusPourNotification' . $table_element . $id_element;
				unset($titres);
				$titres[] = ucfirst(strtolower(__('Id.', 'evarisk')));
				$titres[] = ucfirst(strtolower(__('Nom', 'evarisk')));
				$titres[] = ucfirst(strtolower(__('Pr&eacute;nom', 'evarisk')));
				$titres[] = '';
				$classes = array('userNotificationIdentifierColumn middleAlign','middleAlign','middleAlign','middleAlign rightBorder');
				foreach($notification_list as $action){
					$classes[] = 'middleAlign';
					$titres[] = '<img src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'user_notifications/' . $action->action . '_s.png" alt="' . __($action->action_title, 'evarisk') . '" title="' . __($action->action_title, 'evarisk') . '" />';
				}
			}
			$script = '';
			unset($lignesDeValeurs);

			/*	Affichage de la ligne permettant de cocher un colonne enti�re 	*/
			unset($valeurs);
			$valeurs[] = array('value' => '', 'class' => 'bottomBorder');
			$valeurs[] = array('value' => '', 'class' => 'bottomBorder');
			$valeurs[] = array('value' => '', 'class' => 'bottomBorder');
			$valeurs[] = array('value' => '<input type="checkbox" name="action_check_all" value="check_all" id="check_all" class="check_all" />', 'class' => 'bottomBorder');
			$user_notif_line = '';
			foreach($notification_list as $action){
				$valeurs[] = array('value' => '<input type="checkbox" name="action_check_all_column" value="' . $action->action . '" id="action_' . $action->id . '" class="check_all_action_column" />', 'class' => 'digi_textcenter bottomBorder');
			}
			$lignesDeValeurs[] = $valeurs;
			$idLignes[] = 12;

			/*	Affichage de la liste des utilisateurs	*/
			foreach($utilisateursLies as $utilisateur){
				unset($valeurs);
				$user_info = evaUser::getUserInformation($utilisateur->id_user);

				$idLigne='digi_notification_user_line-'.$utilisateur->id_user;
				$valeurs[] = array('value' => ELEMENT_IDENTIFIER_U . $utilisateur->id_user, 'class' => '');
				$valeurs[] = array('value' => $user_info[$utilisateur->id_user]['user_lastname'], 'class' => '');
				$valeurs[] = array('value' => $user_info[$utilisateur->id_user]['user_firstname'], 'class' => '');
				$valeurs[] = array('value' => '<input type="checkbox" name="user_check_all_line" value="' . $utilisateur->id_user . '" id="user_id_' . $utilisateur->id_user . '" class="check_all_user_line" />', 'class' => 'rightBorder');
				$user_notif_line = '';
				foreach($notification_list as $action){
					$checked = $element_class = '';
					$check_name = 'user_notification_insert';
					if(array_key_exists($utilisateur->id_user, $stored_notification_link_list) && in_array($action->id, $stored_notification_link_list[$utilisateur->id_user])){
						$check_name = 'user_notification_update';
						$checked = ' checked="checked" ';
						$element_class = 'already_linked';
					}
					$valeurs[] = array('value' => '<input class="notification_action action_' . $action->action . ' action_user_' . $utilisateur->id_user . ' ' . $element_class . '" type="checkbox" ' . $checked . ' name="' . $check_name . '[' . $action->action . '][' . $utilisateur->id_user . ']" value="' . $action->id . '" id="user_notification_' . $action->id . '_' . $utilisateur->id_user . '" />', 'class' => 'digi_textcenter');
				}

				$lignesDeValeurs[] = $valeurs;
				$idLignes[] = $idLigne;
			}
			$notification_box = '
<div class="hide evaMessage" id="digi_link_notification_user_message" >&nbsp;</div>
<form id="user_notification_form" action="' . EVA_INC_PLUGIN_URL . 'ajax.php" method="POST" >
	<input type="hidden" name="post" id="post" value="true" />
	<input type="hidden" name="table" id="table" value="' . self::dbTable . '" />
	<input type="hidden" name="act" id="act" value="save_user_notification" />
	<input type="hidden" name="tableElement" id="tableElement" value="' . $table_element . '" />
	<input type="hidden" name="idElement" id="idElement" value="' . $id_element . '" />
	<input type="hidden" name="notification_to_delete" id="toDelete" value="" />
	' . evaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script, false) . '
</form>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#user_notification_form").ajaxForm({
			target: "#ajax-response"
		});

		jQuery(".already_linked").click(function(){
			var element_identifier = "-" + jQuery(this).attr("id").replace("user_notification_", "");
			if(!jQuery(this).is(":checked")){
				jQuery("#toDelete").val(jQuery("#toDelete").val() + element_identifier);
			}
			else{
				jQuery("#toDelete").val(jQuery("#toDelete").val().replace(element_identifier, ""));
			}
		});
		jQuery(".check_all_user_line").click(function(){
			if(jQuery(this).is(":checked")){
				jQuery(".action_user_" + jQuery(this).val()).prop("checked", true);
				jQuery(".action_user_" + jQuery(this).val()).each(function(){
					if(jQuery(this).hasClass("already_linked")){
						jQuery("#toDelete").val(jQuery("#toDelete").val().replace("-" + jQuery(this).attr("id").replace("user_notification_", ""), ""));
					}
				});
			}
			else{
				jQuery(".action_user_" + jQuery(this).val()).prop("checked", false);
				jQuery(".action_user_" + jQuery(this).val()).each(function(){
					if(jQuery(this).hasClass("already_linked")){
						jQuery("#toDelete").val(jQuery("#toDelete").val() + "-" + jQuery(this).attr("id").replace("user_notification_", ""));
					}
				});
			}
		});
		jQuery(".check_all_action_column").click(function(){
			if(jQuery(this).is(":checked")){
				jQuery(".action_" + jQuery(this).val()).prop("checked", true);
				jQuery(".action_" + jQuery(this).val()).each(function(){
					if(jQuery(this).hasClass("already_linked")){
						jQuery("#toDelete").val(jQuery("#toDelete").val().replace("-" + jQuery(this).attr("id").replace("user_notification_", ""), ""));
					}
				});
			}
			else{
				jQuery(".action_" + jQuery(this).val()).prop("checked", false);
				jQuery(".action_" + jQuery(this).val()).each(function(){
					if(jQuery(this).hasClass("already_linked")){
						jQuery("#toDelete").val(jQuery("#toDelete").val() + "-" + jQuery(this).attr("id").replace("user_notification_", ""));
					}
				});
			}
		});
		jQuery("#check_all").click(function(){
			if(jQuery(this).is(":checked")){
				jQuery(".notification_action").each(function(){
					jQuery(this).prop("checked", true);
				});
			}
			else{
				jQuery(".notification_action").each(function(){
					jQuery(this).prop("checked", false);
				});
			}
		});
	});
</script>';
		}
		else{
			$notification_box = __('Aucun utilisateur n\'est affect&eacute; pour le moment. Vous devez en affecter pour pouvoir g&eacute;rer les notifications', 'evarisk');
		}

		return $notification_box;
	}



	/**
	*	Check and notify different user for modification
	*
	*	@param string $table_element The element type to notofy action for
	*	@param string $id_element The element identifier to notofy action for
	*	@param string $action The action to notify user that it has been done
	*
	*	@return void
	*/
	function notify_affiliated_user($table_element, $id_element, $action, $old_content, $modif_content){
		global $wpdb, $current_user;
		$done_user = array();
		get_currentuserinfo();

		/*	Get the user notification list	*/
		$user_notification_list = digirisk_user_notification::get_link_user_notification_list($table_element, $id_element, $action);
		$action_done_by_user = __($action, 'evarisk');

		switch($table_element){
			case TABLE_TACHE:
				$tache = new EvaTask($id_element);
				$tache->load();
				$element_name = ELEMENT_IDENTIFIER_T . $id_element . '&nbsp;-&nbsp;' . utf8_decode($tache->getName());
				$element_page = 'digirisk_correctiv_actions&elt=edit-node' . $id_element;
			break;
			case TABLE_ACTIVITE:
				$activite = new EvaActivity($id_element);
				$activite->load();
				$element_name = ELEMENT_IDENTIFIER_ST . $id_element . '&nbsp;-&nbsp;' . utf8_decode($activite->getName());
				$element_page = 'digirisk_correctiv_actions&elt=edit-leaf' . $id_element;
			break;
		}

		$headers = 'From: ' . get_bloginfo('admin_email') . ' <' . get_bloginfo('admin_email') . '>
';

		$content = '';
		$content = self::read_modification_details($modif_content, $table_element, $action);

		foreach($user_notification_list as $notification_infos){
			/*	Get the recipient email from it identifier	*/
			$user_info = get_userdata($notification_infos->id_user);

			/*	Make transformation on different mail element	*/
			$mail_subject = sprintf($notification_infos->message_subject, get_bloginfo('name') . ' -> ' . $element_name);
			$user_name = (($current_user->user_firstname != '') ? $current_user->user_firstname : $current_user->display_name) . '&nbsp;' . $current_user->user_lastname;

			$mail_content = sprintf($notification_infos->message_to_send, $element_name, admin_url('admin.php?page=' . $element_page), $action_done_by_user, mysql2date('d F Y', current_time('mysql', 0), true), ELEMENT_IDENTIFIER_U . $notification_infos->id_user . '&nbsp;-&nbsp;' . $user_name, $content);

			/*	Add the mail into database for history	*/
			digirisk_messages::add_message($notification_infos->id_user, $user_info->user_email, $mail_subject, $mail_content, $notification_infos->id_notification, $id_element, $table_element);

			/*	Check if the user email is a real email	*/
			if(is_email($user_info->user_email) && !in_array($user_info->user_email, $done_user)){
				$is_sent = wp_mail($user_info->user_email, html_entity_decode(utf8_decode($mail_subject)), html_entity_decode(utf8_decode($mail_content)), $headers);
				$done_user[] = $user_info->user_email;
			}
		}
	}

	/**
	 * Read information about modification made on different element and return an output with the different element
	 *
	 * @param string|array $modification_datas The data to transform into user readable output
	 * @param string $action The action corresponding to the data. Allows to define the output shape to create
	 *
	 * @return string $modification The modification content transformed to be user readable
	 */
	function read_modification_details($modification_datas, $table_element, $action){
		global $wpdb;
		$modification_content = '';

		if($modification_datas != ''){
			/*	Get action detailled informations	*/
			$action_detailled_information = self::get_action(array('action' => array('%s', $action), 'table_element' => array('%s', $table_element)));

			switch($action){
			/*	Action for correctiv action	*/
				case 'delete_user_from_affectation_list':{
					$modification_datas = (!is_array($modification_datas)) ? unserialize($modification_datas) : $modification_datas;
					if(is_array($modification_datas)){
						$modification_content .= __('Liste des utilisateurs d&eacute;saffect&eacute;s', 'evarisk') . "
";
						foreach($modification_datas as $user_id){
							if($user_id > 0){
								$user_info = evaUser::getUserInformation($user_id);
								$modification_content .= '- ' . ELEMENT_IDENTIFIER_U . $user_id . ' - ' . (!empty($user_info[$user_id]['user_lastname'])?$user_info[$user_id]['user_lastname']:'') . ' ' . (!empty($user_info[$user_id]['user_fistname'])?$user_info[$user_id]['user_fistname']:'') . "
";
							}
						}
					}
				}break;
				case 'user_affectation_update':{
					$modification_datas = (!is_array($modification_datas)) ? unserialize($modification_datas) : $modification_datas;
					if(is_array($modification_datas)){
						$modification_content .= __('Liste des utilisateurs modifi&eacute;s', 'evarisk') . "
";
						foreach($modification_datas as $user_id){
							if($user_id > 0){
								$user_info = evaUser::getUserInformation($user_id);
								$modification_content .= '- ' . ELEMENT_IDENTIFIER_U . $user_id . ' - ' . (!empty($user_info[$user_id]['user_lastname'])?$user_info[$user_id]['user_lastname']:'') . ' ' . (!empty($user_info[$user_id]['user_fistname'])?$user_info[$user_id]['user_fistname']:'') . "
";
							}
						}
					}
				}break;
				case 'transfer':{
					$task = new EvaTask($modification_datas);
					$task->load();
					$new_element_infos = ELEMENT_IDENTIFIER_ST . $modification_datas . '&nbsp;-&nbsp;' . $task->getName() . '  ->  ' . admin_url('admin.php?page=digirisk_correctiv_actions&elt=edit-node' . $modification_datas);
					$modification_content .= sprintf(__('L\'&eacute;l&eacute;ment a &eacute;t&eacute; transf&eacute;r&eacute; vers %s', 'evarisk'), $new_element_infos);
				}break;
				case 'picture_add':{
					$element_identifier = ELEMENT_IDENTIFIER_PIC . $modification_datas;
					$query = $wpdb->prepare("SELECT photo FROM " . TABLE_PHOTO . " WHERE id = %d", $modification_datas);
					$element_path = EvaPhoto::checkIfPictureIsFile($wpdb->get_var($query), $table_element, false);
					$modification_content .= sprintf(__('Photo ajout&eacute;e %s, voir la photo %s', 'evarisk'), $element_identifier, $element_path);
				}break;
				case 'picture_delete':{
					$element_identifier = ELEMENT_IDENTIFIER_PIC . $modification_datas;
					$query = $wpdb->prepare("SELECT photo FROM " . TABLE_PHOTO . " WHERE id = %d", $modification_datas);
					$modification_content .= sprintf(__('Photo supprim&eacute;e %s', 'evarisk'), $element_identifier);
				}break;
				case 'picture_as_main_add':{
					$element_identifier = ELEMENT_IDENTIFIER_PIC . $modification_datas;
					$query = $wpdb->prepare("SELECT photo FROM " . TABLE_PHOTO . " WHERE id = %d", $modification_datas);
					$element_path = EvaPhoto::checkIfPictureIsFile($wpdb->get_var($query), $table_element, false);
					$modification_content .= sprintf(__('Nouvelle photo principale %s, voir la photo %s', 'evarisk'), $element_identifier, $element_path);
				}break;
				case 'picture_as_main_delete':{
					$element_identifier = ELEMENT_IDENTIFIER_PIC . $modification_datas;
					$query = $wpdb->prepare("SELECT photo FROM " . TABLE_PHOTO . " WHERE id = %d", $modification_datas);
					$element_path = EvaPhoto::checkIfPictureIsFile($wpdb->get_var($query), $table_element, false);
					$modification_content .= sprintf(__('Ancienne photo principale %s, voir la photo %s', 'evarisk'), $element_identifier, $element_path);
				}break;
				case 'doc_add':{
					$element_identifier = ELEMENT_IDENTIFIER_DOC . $modification_datas;
					$query = $wpdb->prepare("SELECT nom FROM " . TABLE_GED_DOCUMENTS . " WHERE id = %d", $modification_datas);
					$doc_info = $wpdb->get_row($query);
					$element_identifier .= ' - ' . $doc_info->nom;
					$document_path = eva_gestionDoc::getDocumentPath($modification_datas);
					if(is_file(EVA_GENERATED_DOC_DIR . '/' . $document_path)){
						$element_path = EVA_GENERATED_DOC_URL . '/' . $document_path;
					}
					else{
						$element_path = __('Ce document est introuvable', 'evarisk') . $upload_dir['basedir'] . '/' . $document_path;
					}
					$modification_content .= sprintf(__('Document ajout&eacute; %s, Acc&eacute;der au document %s', 'evarisk'), $element_identifier, $element_path);
				}break;
				case 'doc_delete':{
					$element_identifier = ELEMENT_IDENTIFIER_DOC . $modification_datas;
					$query = $wpdb->prepare("SELECT nom FROM " . TABLE_GED_DOCUMENTS . " WHERE id = %d", $modification_datas);
					$doc_info = $wpdb->get_row($query);
					$element_identifier .= ' - ' . $doc_info->nom;
					$modification_content .= sprintf(__('Document supprim&eacute; %s', 'evarisk'), $element_identifier);
				}break;

				/*	Corrective action special part	*/
				case 'affectation_update':{
					if($modification_datas != 'none'){
						switch($modification_datas[0]){
							case TABLE_UNITE_TRAVAIL:{
								$element = eva_UniteDeTravail::getWorkingUnit($modification_datas[1]);
								$element_name = utf8_decode($element->nom);
								$element_identifier = ELEMENT_IDENTIFIER_UT;
							}break;
							case TABLE_GROUPEMENT:{
								$element = EvaGroupement::getGroupement($modification_datas[1]);
								$element_name = utf8_decode($element->nom);
								$element_identifier = ELEMENT_IDENTIFIER_GP;
							}break;
							case TABLE_RISQUE:{
								/*	Get the associated element	*/
								$query = $wpdb->prepare(
"SELECT D.nom
FROM " . TABLE_RISQUE . " AS R
INNER JOIN " . TABLE_DANGER . " AS D ON ((D.id = R.id_danger) AND (D.Status = 'Valid'))
WHERE R.id = %d", $modification_datas[1]);
								$element_name = $wpdb->get_var($query);
								$element_identifier = ELEMENT_IDENTIFIER_R;
							}break;
						}
						$modification_content .= sprintf(__('Nouvel &eacute;l&eacute;ment associ&eacute; %s - %s', 'evarisk'), $element_identifier . $modification_datas[1], $element_name);
					}
					else{
						$modification_content .= __('La t&acirc;che n\'est plus associ&eacute;e a aucun &eacute;l&eacute;ment', 'evarisk');
					}
				}break;
				case 'follow_add':{
					$modification_content .= sprintf(__('Nouveau commentaire ajout&eacute; : %s', 'evarisk'), $modification_datas);
				}break;
				case 'follow_update':{
					$modification_content .= sprintf(__('Commentaire mis &agrave; jour : %s', 'evarisk'), $modification_datas);
				}break;
				case 'picture_as_before_add':{
					$element_identifier = ELEMENT_IDENTIFIER_PIC . $modification_datas;
					$query = $wpdb->prepare("SELECT photo FROM " . TABLE_PHOTO . " WHERE id = %d", $modification_datas);
					$element_path = EvaPhoto::checkIfPictureIsFile($wpdb->get_var($query), $table_element, false);
					$modification_content .= sprintf(__('Photo avant la t&acirc;che %s, voir la photo %s', 'evarisk'), $element_identifier, $element_path);
				}break;
				case 'picture_as_after_add':{
					$element_identifier = ELEMENT_IDENTIFIER_PIC . $modification_datas;
					$query = $wpdb->prepare("SELECT photo FROM " . TABLE_PHOTO . " WHERE id = %d", $modification_datas);
					$element_path = EvaPhoto::checkIfPictureIsFile($wpdb->get_var($query), $table_element, false);
					$modification_content .= sprintf(__('Photo apr&egrave;s la t&acirc;che %s, voir la photo %s', 'evarisk'), $element_identifier, $element_path);
				}break;
				case 'picture_as_before_delete':{
					$element_identifier = ELEMENT_IDENTIFIER_PIC . $modification_datas;echo $element_identifier;
					$modification_content .= sprintf(__('La photo %s n\'est plus marqu&eacute;e comme &eacute;tant avant la t&acirc;che', 'evarisk'), $element_identifier);
				}break;
				case 'picture_as_after_delete':{
					$element_identifier = ELEMENT_IDENTIFIER_PIC . $modification_datas;
					$modification_content .= sprintf(__('La photo %s n\'est plus marqu&eacute;e comme &eacute;tant apr&egrave;s la t&acirc;che', 'evarisk'), $element_identifier);
				}break;
				case 'add_new_subtask':{
					switch($modification_datas[0]){
						case TABLE_TACHE:
							$element_identifier = ELEMENT_IDENTIFIER_T . $modification_datas[1];
						break;
						case TABLE_ACTIVITE:
							$element_identifier = ELEMENT_IDENTIFIER_ST . $modification_datas[1];
						break;
					}
					$modification_content .= sprintf(__('Nouvelle sous-t&acirc;che ajout&eacute;e :
Identifiant: %s
Nom: %s
Description: %s', 'evarisk'), $element_identifier, $modification_datas[2], $modification_datas[3]);
				}break;
				case 'update':{
					switch($table_element){
						case TABLE_TACHE:
							$key_to_output = array('id', 'name', 'description', 'startDate', 'finishDate', 'ProgressionStatus', 'idResponsable', 'idSoldeur', 'dateSolde', 'efficacite', 'real_start_date', 'real_end_date', 'estimate_cost', 'real_cost', 'planned_time', 'elapsed_time');
							$element_identifier = ELEMENT_IDENTIFIER_T;
						break;
						case TABLE_ACTIVITE:
							$key_to_output = array('id', 'name', 'description', 'startDate', 'finishDate', 'ProgressionStatus', 'idResponsable', 'idSoldeur', 'dateSolde', 'avancement', 'real_start_date', 'real_end_date', 'cout', 'cout_reel', 'planned_time', 'elapsed_time');
							$element_identifier = ELEMENT_IDENTIFIER_ST;
						break;
					}
					foreach($modification_datas as $key => $content){
						if(in_array($key, $key_to_output)){
							switch($key){
								case 'id':
									$content_to_output = __('Identifiant', 'evarisk') . ' : ' . $element_identifier . $content;
								break;
								case 'startDate':
									$content_to_output = __('Date de d&eacute;but', 'evarisk') . ' : ' . mysql2date('d F Y', $content, true);
								break;
								case 'finishDate':
									$content_to_output = __('Date de fin', 'evarisk') . ' : ' . mysql2date('d F Y', $content, true);
								break;
								case 'real_start_date':
									$content_to_output = (!empty($content) && ($content != '0000-00-00')) ? __('Date de d&eacute;but r&eacute;elle', 'evarisk') . ' : ' . mysql2date('d F Y', $content, true) : '';
								break;
								case 'real_end_date':
									$content_to_output = (!empty($content) && ($content != '0000-00-00')) ? __('Date de fin r&eacute;elle', 'evarisk') . ' : ' . mysql2date('d F Y', $content, true) : '';
								break;
								case 'estimate_cost':
								case 'cout':
									$content_to_output = !empty($content) ? __('Co&ucirc;t estim&eacute;', 'evarisk') . ' : ' . $content . ' &euro;' : '';
								break;
								case 'real_cost':
								case 'cout_reel':
									$content_to_output = !empty($content) ? __('Co&ucirc;t r&eacute;el', 'evarisk') . ' : ' . $content . ' &euro;' : '';
								break;
								case 'planned_time':
									$content_to_output = !empty($content) ? __('Temps estim&eacute;', 'evarisk') . ' : ' . $content . ' &euro;' : '';
								break;
								case 'elapsed_time':
									$content_to_output = !empty($content) ? __('Temps pass&eacute;', 'evarisk') . ' : ' . $content . ' &euro;' : '';
								break;
								case 'ProgressionStatus':
									$content_to_output = __('Statut', 'evarisk') . ' : ' . actionsCorrectives::check_progression_status_for_output($content);
									if(($content == 'Done') || ($content == 'DoneByChief')){
										$infos_soldeur = '';
										if($modification_datas->idSoldeurChef > 0){
											$responsable_infos = evaUser::getUserInformation($modification_datas->idSoldeurChef);
											$infos_soldeur = ELEMENT_IDENTIFIER_U . $modification_datas->idSoldeurChef . ' - ' . $responsable_infos[$modification_datas->idSoldeurChef]['user_lastname'] . ' ' . $responsable_infos[$modification_datas->idSoldeurChef]['user_firstname'];
										}
										elseif($modification_datas->idSoldeur > 0){
											$responsable_infos = evaUser::getUserInformation($modification_datas->idSoldeur);
											$infos_soldeur = ELEMENT_IDENTIFIER_U . $modification_datas->idSoldeurChef . ' - ' . $responsable_infos[$modification_datas->idSoldeur]['user_lastname'] . ' ' . $responsable_infos[$modification_datas->idSoldeur]['user_firstname'];
										}
										$content_to_output .= '  ' . sprintf(__('Sold&eacute;e le %s par %s', 'evarisk'), $modification_datas->dateSolde, $infos_soldeur);
									}
								break;
								case 'idResponsable':
									$content_to_output = __('Responsable', 'evarisk') . ' : ';
									if($content <= 0){
										$content_to_output .= __('Aucun responsable n\'a &eacute;t&eacute; d&eacute;fini pour le moment', 'evarisk');
									}
									else{
										$responsable_infos = evaUser::getUserInformation($content);
										$content_to_output .= ELEMENT_IDENTIFIER_U . $content . ' - ' . $responsable_infos[$content]['user_lastname'] . ' ' . $responsable_infos[$content]['user_firstname'];
									}
								break;
								case 'efficacite':
									$content_to_output = __('Efficacit&eacute;', 'evarisk') . ' : ';
									if($content <= 0){
										$content_to_output .= __('L\'efficacit&eacute; de la t&acirc;che n\'a pas &eacute;t&eacute; pr&eacute;cis&eacute;e', 'evarisk');
									}
									else{
										$content_to_output .= $content . '%';
									}
								break;
								case 'avancement':
									$content_to_output = __('Avancement de la sous-t&acirc;che', 'evarisk') . ' : ';
									if($content <= 0){
										$content_to_output .= __('L\'avancement de la sous-t&acirc;che n\'a pas &eacute;t&eacute; pr&eacute;cis&eacute;e', 'evarisk');
									}
									else{
										$content_to_output .= $content . '%';
									}
								break;
								case 'idSoldeur':
								case 'dateSolde':
									$content_to_output = '';
								break;
								default:
									$content_to_output = __($key, 'evarisk') . ' : ' . __($content, 'evarisk');
								break;
							}
							if($content_to_output != ''){
								$modification_content .= $content_to_output . '
';
							}
						}
					}
				}break;
			}

			if(trim($modification_content) != ''){
				$modification_content = __('Modification effectu&eacute;e', 'evarisk') . ":
" . $modification_content . "
";
			}
		}

		return $modification_content;
	}

	/**
	 *
	 */
	function log_element_modification($table_element, $id_element, $action, $old_content, $new_content){
		global $wpdb, $current_user;

		/*	Get action detailled informations	*/
		$action_detailled_information = self::get_action(array('action' => array('%s', $action), 'table_element' => array('%s', $table_element)));

		/*	Insert the modification into database	*/
		$wpdb->insert(DIGI_DBT_ELEMENT_MODIFICATION, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'id_user' => $current_user->ID, 'id_action' => (!empty($action_detailled_information) ? $action_detailled_information[0]->id : 0), 'id_element' => $id_element, 'table_element' => $table_element, 'old_content' => serialize($old_content)));

		digirisk_user_notification::notify_affiliated_user($table_element, $id_element, $action, $old_content, $new_content);
	}

}