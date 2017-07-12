<?php
/**
*	The different utilities to manage users in evarisk
*
*	@package 		Digirisk
*	@subpackage users
* @author			Evarisk <dev@evarisk.com>
*/

class evaUser {
	/**
	*	Set the current attribute group regarding the
	*/
	protected $_currentAttributeGroup = '';
	/**
	*	Set the current attribute group regarding the
	*/
	protected $_userToUpdate = '';

	/**
	*	Get the wordpress' user list
	*
	*	@return array $userlist An object containing the different subscriber
	*/
	function getUserList() {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT U.ID
			FROM {$wpdb->users} AS U", '');
		$userList = $wpdb->get_results($query);

		return $userList;
	}
	/**
	*	Get the wordpress' user list
	*
	*	@return array $userlist An object containing the different subscriber
	*/
	function getCompleteUserList( $check_unhiring = false ) {
		$listeComplete = array();

		$listeUtilisateurs = evaUser::getUserList();
		foreach($listeUtilisateurs as $utilisateurs){
			if( $utilisateurs->ID != 1 ){
				$user_unhiring_date = trim( get_user_meta( $utilisateurs->ID, 'digi_unhiring_date', true ) );

				if ( ($check_unhiring && empty($user_unhiring_date)) || !$check_unhiring ) {
					$user_info = get_userdata($utilisateurs->ID);

					unset($valeurs);
					$valeurs['user_id'] = $user_info->ID;
					$valeurs['user_registered'] = $user_info->user_registered;
					if( (isset($user_info->user_lastname) && ($user_info->user_lastname != '')) ){
						$valeurs['user_lastname'] = $user_info->user_lastname;
					}
					else{
						$valeurs['user_lastname'] = '';
					}
					if( (isset($user_info->user_firstname) && ($user_info->user_firstname != '')) ){
						$valeurs['user_firstname'] = $user_info->user_firstname;
					}
					else{
						$valeurs['user_firstname'] = $user_info->user_nicename;
					}

					$listeComplete[$user_info->ID] = $valeurs;
				}
			}
		}

		return $listeComplete;
	}
	/**
	*	Get the wordpress' user list
	*
	*	@return array $userlist An object containing the different subscriber
	*/
	function getUserInformation($userId) {
		$listeComplete = array();

		$user_info = get_userdata($userId);

		unset($valeurs);
		$valeurs['user_id'] = $userId;
		if( (isset($user_info->user_lastname) && ($user_info->user_lastname != '')) )
			$valeurs['user_lastname'] = $user_info->user_lastname;
		else
			$valeurs['user_lastname'] = (isset($user_info->display_name) && ($user_info->display_name != '')) ? $user_info->display_name : '';

		if( (isset($user_info->user_firstname) && ($user_info->user_firstname != '')) )
			$valeurs['user_firstname'] = $user_info->user_firstname;
		else
			$valeurs['user_firstname'] = '';

		/**	Get user meta informations	*/
		$user_metas = (!empty($userId) ? get_user_meta($userId) : array());
		if ( !empty($user_metas) ) {
			foreach ( $user_metas as $meta_key => $meta_value ) {
				if ( $meta_key == 'digirisk_information' ) {
					$valeurs['digirisk_information'] = unserialize( $meta_value[0] );
				}
				else if ( substr($meta_key, 0, 5) == 'digi_' ) {
					$valeurs[$meta_key] = $meta_value[0];
				}
				else {
					$valeurs[$meta_key] = $meta_value;
				}
			}
		}
		$listeComplete[$userId] = $valeurs;

		return $listeComplete;
	}

	/**
	*	Add the different mandatory fields for the user in case of accident
	*/
	function user_additionnal_field_save($user_id){
		global $userWorkAccidentMandatoryFields, $wpdb;
		$user_is_valid_for_accident = 'yes';
		foreach($userWorkAccidentMandatoryFields as $field_identifier){
			if(isset($_REQUEST['digirisk_user_information'][$field_identifier]) && (trim($_REQUEST['digirisk_user_information'][$field_identifier]) == '')){
				$user_is_valid_for_accident = 'no';
			}
		}
		$_REQUEST['digirisk_user_information']['user_is_valid_for_accident'] = $user_is_valid_for_accident;
		update_user_meta($user_id, 'digirisk_information', $_REQUEST['digirisk_user_information']);

		if ( !empty($_REQUEST['digirisk_user_information_meta']) ) {
			foreach ( $_REQUEST['digirisk_user_information_meta'] as $meta_key => $meta_value ) {
				update_user_meta($user_id, $meta_key, $meta_value);

				if ( ($meta_key == 'digi_unhiring_date') && !empty($meta_value) ) {
					$update_user = $wpdb->update( TABLE_LIAISON_USER_ELEMENT, array('status' => 'deleted', 'date_desAffectation' => current_time('mysql', 0), 'date_desaffectation_reelle' => $meta_value, 'id_desAttributeur' => get_current_user_id()), array('id_user' => $user_id, 'status' => 'valid') );
				}
			}
		}
	}

	/**
	 *	Add the different mandatory fields for the user in case of accident
	 */
	function user_additionnal_field($user, $output_type = 'normal') {
		global $optionUserGender, $optionUserNationality, $userWorkAccidentMandatoryFields;
		$user_additionnal_field = $user_additionnal_field_alert = '';
		$required_class = array();

		/*	Get the current user meta	*/
		$user_meta = (!empty($user)?get_user_meta($user->ID, 'digirisk_information', false):array());
		$user_metas = (!empty($user) ? get_user_meta($user->ID) : array());
		$user_additionnal_field_alert = '<span class="required" >' . __('L\'ensemble des champs ci-dessous sont obligatoire pour que l\'utilisateur soit &eacute;ligible &agrave; la d&eacute;claration d\'accident du travail', 'evarisk') . '</span>';
		$required_class['user_imatriculation'] = $required_class['user_birthday'] = $required_class['user_gender'] = $required_class['user_nationnality'] = $required_class['user_adress'] = $required_class['digi_hiring_date'] = $required_class['digi_unhiring_date'] = $required_class['user_profession'] = $required_class['user_professional_qualification'] = ' class="required" ';
		$required_class['user_adress_2']=$required_class['user_insurance_ste']=' ';
		$required_filed_empty = 0;
		if(!empty($user_meta[0])){
			foreach($user_meta[0] as $field_identifier => $field_content){
				$required_class[$field_identifier] = '';
				if ( (trim($field_content) == '') && (in_array($field_identifier, $userWorkAccidentMandatoryFields))	) {
					if($field_identifier == 'user_imatriculation_key')
						$field_identifier = 'user_imatriculation';
					elseif($field_identifier == 'user_adress_2')
						$field_identifier = 'user_adress';

					$required_class[$field_identifier] = ' class="required" ';
					$required_filed_empty++;
				}
			}
		}
		if( !empty($user_metas) ) {
			foreach( $user_metas as $meta_key => $meta_content ){
				$required_class[$meta_key] = '';
				if ( ( empty($meta_content) || empty($meta_content[0]) ) && in_array($meta_key, $userWorkAccidentMandatoryFields)) {
					$required_class[$meta_key] = ' class="required" ';
					$required_filed_empty++;
				}
			}
		}
		if ( $required_filed_empty > 0 ) {
			$user_additionnal_field_alert = '<span class="required" >' . __('Les champs marqu&eacute;s en rouge sont obligatoire pour que l\'utilisateur soit &eacute;ligible &agrave; la d&eacute;claration d\'accident du travail', 'evarisk') . '</span>';
		}

		if ( $output_type == 'normal' ) {
			$user_additionnal_field .= '
<h3 id="digi_user_informations" >' . __('Informations compl&eacute;mentaires pour le logiciel Digirisk', 'digirisk') . '</h3>
' . $user_additionnal_field_alert;
			if ( !empty($user_meta[0]) && ($user_meta[0]['user_is_valid_for_accident'] == 'yes')) {
				$user_additionnal_field .= '<span class="user_is_valid_for_accident" >' . __('L\'utilisateur est &eacute;ligible &agrave; la d&eacute;claration d\'un accident du travail', 'evarisk') . '</span>';
			}
			elseif ( !empty($user_meta[0]) && ($user_meta[0]['user_is_valid_for_accident'] == 'no') ) {
				$user_additionnal_field .= '<span class="user_is_not_valid_for_accident" >' . __('L\'utilisateur n\'est pas &eacute;ligible &agrave; la d&eacute;claration d\'un accident du travail', 'evarisk') . '</span>';
			}
		}
$user_additionnal_field .= '
<table class="' . (($output_type == 'normal') ? 'form-table' : '') . '" >
	<tr>
		<th>
			<label for="user_imatriculation" ' . $required_class['user_imatriculation'] . ' >' . __('N&ordm; d\'immatriculation', 'evarisk') . '</label>
		</th>
		<td>
			' .	EvaDisplayInput::afficherInput('text', 'user_imatriculation', (!empty($user_meta[0])?$user_meta[0]['user_imatriculation']:''), '', null, 'digirisk_user_information[user_imatriculation]', false, false, 13, 'regular-text', '', '', '', 'digi_user_immatriculation', true) . '
			' .	EvaDisplayInput::afficherInput('text', 'user_imatriculation_key', (!empty($user_meta[0])?$user_meta[0]['user_imatriculation_key']:''), '', null, 'digirisk_user_information[user_imatriculation_key]', false, false, 2, '', '', '10%', '', '', true) . '
		</td>
	</tr>
	<tr>
		<th>
			<label for="user_birthday" ' . $required_class['user_birthday'] . ' >' . __('Date de naissance', 'evarisk') . '</label>
		</th>
		<td>
			' .	EvaDisplayInput::afficherInput('text', 'user_birthday', (!empty($user_meta[0])?$user_meta[0]['user_birthday']:''), '', null, 'digirisk_user_information[user_birthday]', false, false, 10, 'regular-text', 'date', '') . '
		</td>
	</tr>
	<tr>
		<th>
			<label for="user_gender" ' . $required_class['user_gender'] . ' >' . __('Sexe', 'evarisk') . '</label>
		</th>
		<td>
			' .	EvaDisplayInput::createComboBox('user_gender', 'digirisk_user_information[user_gender]', $optionUserGender, (!empty($user_meta[0])?$user_meta[0]['user_gender']:''), 'user_combo') . '
		</td>
	</tr>
	<tr>
		<th>
			<label for="user_nationnality" ' . $required_class['user_nationnality'] . ' >' . __('Nationalit&eacute;', 'evarisk') . '</label>
		</th>
		<td>
			' .	EvaDisplayInput::createComboBox('user_nationnality', 'digirisk_user_information[user_nationnality]', $optionUserNationality, (!empty($user_meta[0])?$user_meta[0]['user_nationnality']:''), 'user_combo') . '
		</td>
	</tr>';
	if ( $output_type == 'normal' ) {
		$user_additionnal_field .=
	'<tr>
		<th>
			&nbsp;
		</th>
		<td>
			&nbsp;
		</td>
	</tr>';
	}
		$user_additionnal_field .= '
	<tr>
		<th>
			<label for="user_adress" ' . $required_class['user_adress'] . ' >' . __('Adresse ligne 1', 'evarisk') . '</label><br/>
			<label for="user_adress_2" ' . $required_class['user_adress_2'] . ' >' . __('Adresse ligne 2', 'evarisk') . '</label>
		</th>
		<td>
			' .	EvaDisplayInput::afficherInput('text', 'user_adress', (!empty($user_meta[0])?$user_meta[0]['user_adress']:''), '', null, 'digirisk_user_information[user_adress]', false, false, 255, 'regular-text', '', '', '', '', true) . '
			' .	EvaDisplayInput::afficherInput('text', 'user_adress_2', (!empty($user_meta[0])?$user_meta[0]['user_adress_2']:''), '', null, 'digirisk_user_information[user_adress_2]', false, false, 255, 'regular-text', '', '', '', '', true) . '
		</td>
	</tr>
	<tr>
		<th>
			<label for="user_adress_postal_code" ' . $required_class['user_adress'] . ' >' . __('Code postal', 'evarisk') . '</label>
		</th>
		<td>
			' .	EvaDisplayInput::afficherInput('text', 'user_adress_postal_code', (!empty($user_meta[0])?$user_meta[0]['user_adress_postal_code']:''), '', null, 'digirisk_user_information[user_adress_postal_code]', false, false, 255, 'regular-text', '', '', '', '', true) . '
		</td>
	</tr>
	<tr>
		<th>
			<label for="user_adress_city" ' . $required_class['user_adress'] . ' >' . __('Ville', 'evarisk') . '</label>
		</th>
		<td>
			' .	EvaDisplayInput::afficherInput('text', 'user_adress_city', (!empty($user_meta[0])?$user_meta[0]['user_adress_city']:''), '', null, 'digirisk_user_information[user_adress_city]', false, false, 255, 'regular-text', '', '', '', '', true) . '
		</td>
	</tr>
	<tr>
		<th>
			&nbsp;
		</th>
		<td>
			&nbsp;
		</td>
	</tr>
	<tr>
		<th>
			<label for="digi_hiring_date" ' . $required_class['digi_hiring_date'] . ' >' . __('Date d\'embauche', 'evarisk') . '</label>
		</th>
		<td>
			' .	EvaDisplayInput::afficherInput('text', 'digi_hiring_date', (!empty($user_metas) && !empty($user_metas['digi_hiring_date']) ? $user_metas['digi_hiring_date'][0] : ''), '', null, 'digirisk_user_information_meta[digi_hiring_date]', false, false, 10, 'regular-text', 'date', '') . '
		</td>
	</tr>
	<tr>
		<th>
			<label for="digi_unhiring_date" ' . $required_class['digi_unhiring_date'] . ' >' . __('Date de sortie de la soci&eacute;t&eacute;', 'evarisk') . '</label>
		</th>
		<td>
			' .	EvaDisplayInput::afficherInput('text', 'digi_unhiring_date', (!empty($user_metas) && !empty($user_metas['digi_unhiring_date']) ? $user_metas['digi_unhiring_date'][0] : ''), '', null, 'digirisk_user_information_meta[digi_unhiring_date]', false, false, 10, 'regular-text', 'date', '') . '
		</td>
	</tr>
	<tr>
		<th>
			<label for="user_profession" ' . $required_class['user_profession'] . ' >' . __('Profession', 'evarisk') . '</label>
		</th>
		<td>
			' .	EvaDisplayInput::afficherInput('text', 'user_profession', (!empty($user_meta[0])?$user_meta[0]['user_profession']:''), '', null, 'digirisk_user_information[user_profession]', false, false, 255, 'regular-text', '', '', '', '', true) . '
		</td>
	</tr>
	<tr>
		<th>
			<label for="user_professional_qualification" ' . $required_class['user_professional_qualification'] . ' >' . __('Qualification professionnelle', 'evarisk') . '</label>
		</th>
		<td>
			' .	EvaDisplayInput::afficherInput('text', 'user_professional_qualification', (!empty($user_meta[0])?$user_meta[0]['user_professional_qualification']:''), '', null, 'digirisk_user_information[user_professional_qualification]', false, false, 255, 'regular-text', '', '', '', '', true) . '
		</td>
	</tr>
	<tr>
		<th>
			&nbsp;
		</th>
		<td>
			&nbsp;
		</td>
	</tr>
	<tr>
		<th>
			<label for="user_insurance_ste" ' . $required_class['user_insurance_ste'] . ' >' . __('Soci&eacute;t&eacute; d\'assurance', 'evarisk') . '</label>
		</th>
		<td>
			' .	EvaDisplayInput::afficherInput('text', 'user_insurance_ste', (!empty($user_meta[0])?$user_meta[0]['user_insurance_ste']:''), '', null, 'digirisk_user_information[user_insurance_ste]', false, false, 10, 'regular-text', '', '') . '
		</td>
	</tr>';

	$options = get_option('digirisk_options');
	$user_extra_fields = (!empty($options['digi_users_digirisk_extra_field'])?unserialize($options['digi_users_digirisk_extra_field']):array());
	if (is_array($user_extra_fields) && (count($user_extra_fields) > 0)) {
		foreach ( $user_extra_fields as $field ) {
			$user_additionnal_field .=
	'<tr>
		<th>
			<label for="user_' . $field . '" ' . $required_class[$field] . ' >' . __($field, 'evarisk') . '</label>
		</th>
		<td>
			' .	EvaDisplayInput::afficherInput('text', 'user_' . $field, (!empty($user_meta[0])?$user_meta[0][$field]:''), '', null, 'digirisk_user_information[' . $field . ']', false, false, 10, 'regular-text', '', '') . '
		</td>
	</tr>';
		}
	}

	$user_additionnal_field .=
'</table>';

		echo $user_additionnal_field;
	}

	/**
	*	Get the identifier of the groups bind with an element
	*
	*	@param integer $elementId The identifier of the element (in its table) we want to bind
	*	@param string $elementTable The table of the element we want to bind
	*
	*	@return array An array containing the groups identifiers
	*/
	function getBindUsers($elementId, $elementTable)
	{
		global $wpdb;

		$elementId = ($elementId);
		$elementTable = ($elementTable);

		$queryCleanGroupBind = $wpdb->prepare("SELECT id_user FROM " . TABLE_LIAISON_USER_ELEMENT . " WHERE table_element = '%s' AND id_element = %d and status='valid'", $elementTable, $elementId);

		return $wpdb->get_results($queryCleanGroupBind);
	}

	/**
	*	Output a table with the different users binded to an element
	*
	*	@param mixed $tableElement The element type we want to get the user list for
	*	@param integer $idElement The element identifier we want to get the user list for
	*
	*	@return mixed $utilisateursMetaBox The entire html code to output
	*/
	function afficheListeUtilisateurTable_SimpleSelection($tableElement, $idElement){
		$utilisateursMetaBox = '';
		$idBoutonEnregistrer = 'save_group' . $tableElement;

		$idTable = 'listeIndividus' . $tableElement . $idElement;
		$titres = array( '', ucfirst(strtolower(__('Id.', 'evarisk'))), ucfirst(strtolower(__('Nom', 'evarisk'))), ucfirst(strtolower(__('Pr&eacute;nom', 'evarisk'))), ucfirst(strtolower(__('Inscription', 'evarisk'))));
		if(is_int(strpos($tableElement, DIGI_DBT_ACCIDENT))){
			$titres[] = ucfirst(strtolower(__('Valide accident', 'evarisk')));
		}
		unset($lignesDeValeurs);
		$default_sort_column = 4;

		//on r�cup�re les utilisateurs d�j� affect�s � l'�l�ment en cours.
		$listeUtilisateursLies = array();
		$utilisateursLies = evaUserLinkElement::getAffectedUser($tableElement, $idElement);
		if(is_array($utilisateursLies ) && (count($utilisateursLies) > 0)){
			foreach($utilisateursLies as $utilisateur){
				$listeUtilisateursLies[$utilisateur->id_user] = $utilisateur;
			}
		}
		$listeUtilisateurs = evaUser::getCompleteUserList();
		if(is_array($listeUtilisateurs) && (count($listeUtilisateurs) > 0)){
			foreach($listeUtilisateurs as $utilisateur){
				unset($valeurs);
				$idLigne = $tableElement . $idElement . 'listeUtilisateurs' . $utilisateur['user_id'];
				$idCbLigne = 'cb_' . $idLigne;
				$moreLineClass = 'userIsNotLinked';
				if(isset($listeUtilisateursLies[$utilisateur['user_id']])){
					$moreLineClass = 'userIsLinked';
				}
				$valeurs[] = array('value'=>'<span id="actionButton' . $tableElement . 'UserLink' . $utilisateur['user_id'] . '" class="buttonActionUserLinkList ' . $moreLineClass . '  ui-icon pointer" >&nbsp;</span>');
				$valeurs[] = array('value'=>ELEMENT_IDENTIFIER_U . $utilisateur['user_id']);
				$valeurs[] = array('value'=>$utilisateur['user_lastname']);
				$valeurs[] = array('value'=>$utilisateur['user_firstname']);
				$valeurs[] = array('value'=>mysql2date('d M Y', $utilisateur['user_registered'], true));

				if(is_int(strpos($tableElement, DIGI_DBT_ACCIDENT))){
					$user_meta = get_user_meta($utilisateur['user_id'], 'digirisk_information', false);
					$user_is_valid_for_accident = __('Non', 'evarisk');
					if(current_user_can('edit_users')){
						$user_is_valid_for_accident .= '
						<a target="digi_user_edit" href="' . admin_url('user-edit.php?user_id=' . $utilisateur['user_id']) . '#digi_user_informations" ><img src="' . str_replace('.png', '_vs.png', PICTO_EDIT) . '" title="' . __('&Eacute;diter l\'utilisateur', 'evarisk') . '" alt="edit user" /></a>';
					}
					$user_is_valid_for_accident_class = 'user_is_not_valid_for_accident';
					if(isset($user_meta[0]) && (isset($user_meta[0]['user_is_valid_for_accident']) && ($user_meta[0]['user_is_valid_for_accident'] == 'yes'))){
						$user_is_valid_for_accident = __('Oui', 'evarisk');
						$user_is_valid_for_accident_class = 'user_is_valid_for_accident';
					}
					$valeurs[] = array('value'=>$user_is_valid_for_accident, 'class'=>$user_is_valid_for_accident_class);
					$default_sort_column = 5;
				}
				$lignesDeValeurs[] = $valeurs;
				$idLignes[] = $idLigne;
			}
		}
		else{
			$valeurs[] = array('value'=>'');
			$valeurs[] = array('value'=>'');
			$valeurs[] = array('value'=>__('Aucun r&eacute;sultat trouv&eacute;', 'evarisk'));
			$valeurs[] = array('value'=>'');
			$valeurs[] = array('value'=>'');
			if(is_int(strpos($tableElement, DIGI_DBT_ACCIDENT))){
				$valeurs[] = array('value'=>'');
				$default_sort_column = 5;
			}
			$lignesDeValeurs[] = $valeurs;
			$idLignes[] = $tableElement . $idElement . 'listeUtilisateursVide';
		}

		$classes = array('addUserButtonDTable','userIdentifierColumn','','','','valid_accident_col');
		switch($tableElement){
			case DIGI_DBT_ACCIDENT . 'witness':{
				$user_infos_container = 'accident_witness_" + currentId + "';
				$user_infos_act = 'loadWitnessInfo';
				$user_act_add_container = 'jQuery("#search_accident_witness_details").append("<div id=\'accident_witness_" + currentId  + "\' ></div>");';
				$user_more_action = '
				jQuery("#' . $user_infos_container . '").load(EVA_AJAX_FILE_URL,{
					"post":"true",
					"act":"' . $user_infos_act . '",
					"id_user":currentId
				});';
			}
			break;
			case DIGI_DBT_ACCIDENT . 'third_party':{
				$user_infos_container = 'accident_third_party_" + currentId + "';
				$user_infos_act = 'loadThirdPartyInfo';
				$user_act_add_container = 'jQuery("#search_accident_third_party_details").append("<div id=\'accident_third_party_" + currentId  + "\' ></div>");';
				$user_more_action = '
				jQuery("#' . $user_infos_container . '").load(EVA_AJAX_FILE_URL,{
					"post":"true",
					"act":"' . $user_infos_act . '",
					"id_user":currentId
				});';
			}
			break;
			case DIGI_DBT_ACCIDENT:{
				$user_infos_container = 'work_accident_user_details';
				$user_act_add_container = 'jQuery("#' . $user_infos_container . '").html(jQuery("#loadingImg").html());';
				$user_infos_act = 'loadUserInfo';
				$user_more_action = '
				jQuery("#' . $user_infos_container . '").load(EVA_AJAX_FILE_URL,{
					"post":"true",
					"act":"' . $user_infos_act . '",
					"id_user":currentId
				});
				jQuery("#victim_selector").hide();
				jQuery("#victim_changer").show();';
			}
			break;
			case TABLE_TACHE . 'responsible':{
				$user_infos_container = '';
				$user_act_add_container = '';
				$user_infos_act = '';
				$user_more_action = '
				var lastname = digirisk(this).children("td:nth-child(3)").html();
				var firstname = digirisk(this).children("td:nth-child(4)").html();
				jQuery("#responsable_tache").val(currentId);
				jQuery(".completeUserListActionResponsible").hide();
				jQuery(".searchUserToAffect").hide();
				jQuery("#responsible_name").html("' . ELEMENT_IDENTIFIER_U . '" + currentId + " - " + lastname + " " + firstname);
				jQuery("#responsible_name").show();
				jQuery("#change_responsible_' . $tableElement . '").show();
				jQuery("#delete_responsible_' . $tableElement . '").show();';
			}
			break;
			case TABLE_UNITE_TRAVAIL . 'responsible':
			case TABLE_GROUPEMENT . 'responsible':
			case TABLE_RISQUE . 'responsible':
			case TABLE_ACTIVITE . 'responsible':{
				$user_infos_container = '';
				$user_act_add_container = '';
				$user_infos_act = '';
				$user_more_action = '
				var lastname = digirisk(this).children("td:nth-child(3)").html();
				var firstname = digirisk(this).children("td:nth-child(4)").html();
				jQuery("#' . $tableElement . '").val(currentId);
				jQuery(".completeUserListActionResponsible").hide();
				jQuery(".searchUserToAffect").hide();
				jQuery("#responsible_name").html("' . ELEMENT_IDENTIFIER_U . '" + currentId + " - " + lastname + " " + firstname);
				jQuery("#responsible_name").show();
				jQuery("#change_responsible_' . $tableElement . '").show();
				jQuery("#delete_responsible_' . $tableElement . '").show();';
			}
			break;
			case DIGI_DBT_USER:{
				$user_infos_container = '';
				$user_act_add_container = 'jQuery("#user_profil_edition_tabs").html(jQuery("#loadingImg").html());
			jQuery("#complete_user_list").hide();';
				$user_infos_act = '';
				$user_more_action = '
			window.top.location.href = "' . admin_url('users.php?page=digirisk_users_profil&user_to_edit=') . '" + currentId;
			var lastname = digirisk(this).children("td:nth-child(3)").html();
			var firstname = digirisk(this).children("td:nth-child(4)").html();
			jQuery("#digi_user_list").val(digi_html_accent_for_js("' . ELEMENT_IDENTIFIER_U . '" + currentId + " - " + lastname + " " + firstname));';
			}
			break;
			default:{
				$user_infos_container = '';
				$user_act_add_container = '';
				$user_infos_act = '';
				$user_more_action = '';
			}
			break;
		}
		$script =
'<script type="text/javascript">
	digirisk(document).ready(function(){
		jQuery("#' . $idTable . '").dataTable({
			"bAutoWidth": false,
			"bInfo": false,
			"bPaginate": false,
			"bFilter": false,
			"aaSorting": [[' . $default_sort_column . ',"desc"]]
		});
		jQuery("#' . $idTable . '").children("tfoot").remove();
		jQuery("#' . $idTable . '").removeClass("dataTables_wrapper");
		jQuery(".tr_' . $idTable . '.odd, .tr_' . $idTable . '.even").click(function(){
			if(jQuery(this).children("td:first").children("span").hasClass("userIsNotLinked")){
				var currentId = jQuery(this).attr("id").replace("' . $tableElement . $idElement . 'listeUtilisateurs", "");
				' . $user_act_add_container . '' . $user_more_action . '
			}
		});
	});
</script>';

		$utilisateursMetaBox .= evaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script);

		return $utilisateursMetaBox;
	}


	/**
	 *	Return different informations about users
	 */
	function stats_builder() {
		global $wpdb;

		$query = $wpdb->prepare(
				"SELECT

				1 AS EXPORT_USER,

				(
					SELECT GROUP_CONCAT( DISTINCT( U.ID ) )
					FROM " . $wpdb->prefix . "users AS U
					WHERE U.ID != 1
				) AS TOTAL_USER,

				(
					SELECT GROUP_CONCAT( DISTINCT( USER_LINK_EVALUATION.id_user ) )
					FROM " . TABLE_LIAISON_USER_ELEMENT . " AS USER_LINK_EVALUATION
						INNER JOIN {$wpdb->users} AS U ON (U.ID = USER_LINK_EVALUATION.id_user)
					WHERE ((USER_LINK_EVALUATION.table_element = '" . TABLE_UNITE_TRAVAIL . "_evaluation') OR (USER_LINK_EVALUATION.table_element = '" . TABLE_GROUPEMENT . "_evaluation'))
						OR (
							(USER_LINK_EVALUATION.table_element = '" . DIGI_DBT_USER_GROUP . "')
							AND (USER_LINK_EVALUATION.id_element IN (
								SELECT DISTINCT USER_LINK_GROUP.id_group
								FROM " . DIGI_DBT_LIAISON_USER_GROUP . " AS USER_LINK_GROUP
								WHERE USER_LINK_GROUP.status = 'valid'
							))
						   )
				) AS EVALUATED_USER,

				1 AS NOT_EVALUATED_USER_SINCE,

				1 AS NOT_EVALUATED_USER,

				(
					SELECT GROUP_CONCAT( DISTINCT( U.ID ) )
					FROM {$wpdb->users} AS U
					WHERE U.ID != 1
						AND U.ID NOT IN (
							SELECT id_user
							FROM " . TABLE_LIAISON_USER_ELEMENT . "
							WHERE status = 'valid'
						)
						AND U.ID NOT IN (
							SELECT U.ID
							FROM {$wpdb->users} AS U
								INNER JOIN {$wpdb->usermeta} AS UM ON (UM.user_id = U.ID)
							WHERE UM.meta_key = 'digi_unhiring_date'
								AND UM.meta_value != ''
						)
				) AS NOT_AFFECTED_USER,

				(
					SELECT GROUP_CONCAT( DISTINCT( U.ID ) )
					FROM {$wpdb->users} AS U
						INNER JOIN {$wpdb->usermeta} AS UM ON (UM.user_id = U.ID)
					WHERE UM.meta_key = 'digi_unhiring_date'
						AND UM.meta_value != ''
				) AS USERS_OUT_OF_SOCIETY,

				(
					SELECT GROUP_CONCAT( DISTINCT( U.ID ) )
					FROM {$wpdb->users} AS U
						INNER JOIN {$wpdb->usermeta} AS UM ON (UM.user_id = U.ID)
					WHERE UM.meta_key = 'digi_unhiring_date'
						AND UM.meta_value != ''
						AND U.ID != 1
						AND U.ID NOT IN (
							SELECT id_user
							FROM " . TABLE_LIAISON_USER_ELEMENT . "
						)
				) AS USERS_OUT_OF_SOCIETY_NEVER_AFFECTED,

				1 AS USERS_MOUVEMENT

			LIMIT 1", ""
		);
		$user_stats = $wpdb->get_row($query);

		return $user_stats;
	}

	/**
	 *
	 */
	function digi_ajax_stats_user() {
		$userDashboardStats = evaUser::stats_builder();

		$idTable = 'userDashBordStats';
		$titres = array( __('Stat index', 'evarisk'), __('Statistique', 'evarisk'), __('Valeur', 'evarisk'), '');
		if (count($userDashboardStats) > 0) {
			$stats_counter = 1;
			foreach ($userDashboardStats as $statName => $statValue) {
				$action = '';
				$id = 'undefined';
				$number = 0;
				switch($statName) {
					case 'TOTAL_USER':
						$statName = __('Nombre d\'utilisateurs total', 'evarisk');
						$id = 'full_user_list';
					break;
					case 'EVALUATED_USER':
						$statName = __('Utilisateur ayant particip&eacute; au moins une fois &agrave; l\'audit', 'evarisk');
						$id = 'user_affected_to_evaluation';
					break;
					case 'NOT_EVALUATED_USER_SINCE':
						$statName = __('Utilisateur n\'ayant pas particip&eacute; &agrave; un audit depuis un certains temps', 'evarisk');
						$id = 'user_not_affected_to_evaluation_SINCE';
						$statValue = 'none';
					break;
					case 'NOT_EVALUATED_USER':
						$statName = __('Utilisateur absent lors de l\'audit', 'evarisk');
						$id = 'user_not_affected_to_evaluation';
						$statValue = implode( ',',  array_diff( explode(',', $userDashboardStats->TOTAL_USER), explode(',', $userDashboardStats->EVALUATED_USER) ) );
					break;
					case 'NOT_AFFECTED_USER':
						$statName = __('Utilisateur non affect&eacute;s actuellement', 'evarisk');
						$id = 'user_not_affected_to_element';
					break;
					case 'USERS_OUT_OF_SOCIETY':
						$statName = __('Personnel ayant quitt&eacute; la soci&eacute;t&eacute;', 'evarisk');
						$id = 'user_out_of_society';
					break;
					case 'USERS_OUT_OF_SOCIETY_NEVER_AFFECTED':
						$statName = __('Personnel ayant quitt&eacute; la soci&eacute;t&eacute; sans avoir &eacute;t&eacute; affect&eacute;', 'evarisk');
						$id = 'user_out_of_society_without_affectation';
					break;
					case 'USERS_MOUVEMENT':
						$statName = __('Mouvement de personnel', 'evarisk');
						$id = 'users_mouvement';
						$statValue = 'none';
					break;

					case 'EXPORT_USER':
						$statName = __('Export du personnel au format csv', 'evarisk');
						$id = 'users_export';
						$statValue = 'none';
					break;
				}

				if ( !empty($statValue) ) {
					$action = '<span class="digi_stats_action_button pointer" id="' . $id . '" >' . (($id == 'users_export') ? __('Exporter la liste', 'evarisk') : __('Voir la liste', 'evarisk')) . '</span>';
					$number = ($statValue != 'none') ? count( explode(',', $statValue) ) . '<input type="hidden" id="list2display_' . $id . '" value="' . $statValue . '" />' : '';
				}

				unset($valeurs);
				$valeurs[] = array('value' => $stats_counter);
				$valeurs[] = array('value' => $statName);
				$valeurs[] = array('value' => $number);
				$valeurs[] = array('value' => $action);
				$lignesDeValeurs[] = $valeurs;
				$idLignes[] = 'userDashboardStat' . $stats_counter;
				$outputDatas = true;
				$stats_counter++;
			}
		}
		else {
			unset($valeurs);
			$valeurs[] = array('value'=>'');
			$valeurs[] = array('value'=>__('Aucun r&eacute;sultat trouv&eacute;', 'evarisk'));
			$valeurs[] = array('value'=>'');
			$valeurs[] = array('value'=>'');
			$lignesDeValeurs[] = $valeurs;
			$idLignes[] = 'userDashboardStatEmpty';
			$outputDatas = false;
		}

		$classes = array('','','', '');
		$tableOptions = '';

		if ( $outputDatas ) {
			$script = '
<script type="text/javascript">
	digirisk(document).ready(function() {
		digirisk("#' . $idTable . '").dataTable({
			"bInfo": false,
			"bPaginate": false,
	        "bLengthChange": false,
	        "bFilter": false,
	        "bSort": false,
			"aoColumns":	[
				{"bVisible": false},
				null,
				null,
				null,
			]
			' . $tableOptions . '
		});
		digirisk("#' . $idTable . '").children("thead").remove();
		digirisk("#' . $idTable . '").children("tfoot").remove();
		digirisk("#' . $idTable . '_wrapper").removeClass("dataTables_wrapper");
		digirisk("#vracStatsTabs").tabs();

		jQuery("#digi_stats_user_dialog").dialog({
			autoOpen: false,
			width: 800,
			height: 600,
		});

		jQuery(".digi_stats_action_button").click(function(){
			jQuery("#digi_stats_user_dialog").attr("title", "");
			jQuery("#digi_stats_user_dialog").html( digirisk("#loadingPicContainer").html() );
			if ( jQuery(this).attr("id") == "users_export" ) {
				jQuery.post("' . admin_url('admin-ajax.php') . '", {action: "digi_ajax_load_field_for_export", export_type: "global",}, function(response){
					jQuery("#digi_stats_user_dialog").html( response );
				});
			}
			else {
				var data = {
					action: "digi_ajax_load_user_stat",
					type: jQuery(this).attr("id"),
					list_to_display: jQuery("#list2display_" + jQuery(this).attr("id")).val(),
				};
				jQuery.post("' . admin_url('admin-ajax.php') . '", data, function( response ){
					jQuery("#digi_stats_user_dialog").dialog("option", "title", digi_html_accent_for_js( response[0] ) );
					jQuery("#digi_stats_user_dialog").html( response[1] );
				}, "json");
			}
			digirisk("#digi_stats_user_dialog").dialog("open");
		});
	});
</script>';
			echo evaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script) . '<div id="digi_stats_user_dialog" title="' . __('Utilisateurs du logiciel digirisk', 'evarisk') . '" ></div>';
		}
		die();
	}
	/**
	 *
	 */
	function digi_ajax_load_user_stat() {
		global $wpdb;
		$output = '';
		$title = '';
		$user_list_to_output = null;
		$output_from_another_way = false;
		$empty_list_message = '';

		switch ( $_POST['type'] ) {
			case 'full_user_list' :
				$title = __('Liste des utilisateurs inscrit dans le logiciel', 'evarisk');
				$empty_list_message = __("Il n'y a aucun utilisateur enregistr&eacute;", 'evarisk');
			break;

			case 'user_not_affected_to_evaluation' :
				$title = __("Les utilisateurs absent lors de l'audit", 'evarisk');
				$empty_list_message = __("Tous les utilisateurs inscrits ont particip&eacute; &agrave; l'audit", 'evarisk');
			break;

			case 'user_affected_to_evaluation' :
				$title = __("Les utilisateurs pr&eacutesents lors de l'audit", 'evarisk');
				$empty_list_message = __("Aucun utilisateur inscrit n'a particip&eacute; &agrave; l'audit pour le moment", 'evarisk');
			break;

			case 'user_not_affected_to_element' :
				$title = __('Liste des utilisateurs non affect&eacute;s &agrave; un &eacute;l&eacute;ment (Groupement / unit&eacute;)', 'evarisk');
				$empty_list_message = __("Tous les utilisateurs sont actuellement affect&eacute;s &agrave; au moins un &eacute;l&eacute;ment", 'evarisk');
			break;

			case 'user_out_of_society' :
				$title = __('Liste des personnels ne faisant plus partie de la soci&eacute;t&eacute;', 'evarisk');
				$empty_list_message = __("Aucun personnel n'a quitt&eacute; la soci&eacute;t&eacute; pour le moment", 'evarisk');
			break;

			case 'user_out_of_society_without_affectation' :
				$title = __('Liste des personnels ne faisant plus partie de la soci&eacute;t&eacute; et n\'ayant jamais &eacute;t&eacute; affect&eacute; &agrave; un &eacute;l&eacute;ment', 'evarisk');
				$empty_list_message = __("Aucun personnel n'a quitt&eacute; la soci&eacute;t&eacute; sans avoir &eacute;t&eacute; affect&eacute;", 'evarisk');
			break;

			case 'users_mouvement':
				$title = __('Voir les mouvements du personnel entre des dates', 'wpshop');
				$_POST['list_to_display'] = '';
			break;
			case 'users_export':
				$title = __('Export de la liste du personnel au format csv', 'wpshop');
				$_POST['list_to_display'] = '';
			break;
			case 'user_not_affected_to_evaluation_SINCE':
				$title = __('Voir la liste des personnes absentes des audits depuis une date donn&eacute;e', 'wpshop');
				$_POST['list_to_display'] = '';
			break;
		}

		/**	Display user list asked for the current interface	*/
		$user_list_2_display = explode( ',', $_POST['list_to_display'] );
		if ( !empty( $user_list_2_display ) && (count($user_list_2_display ) >= 1) && !empty($user_list_2_display[0]) ) {
			$output .= self::display_user_list( $user_list_2_display );
		}
		else if ( $_POST['type'] == 'users_mouvement' ) {
			$from_date = '<input type="text" id="users_mouvement_between_from" name="users_mouvement_between_from" class="digi_user_mouvment_datepicker" value="" />';
			$to_date = '<input type="text" id="users_mouvement_between_to" name="users_mouvement_between_to" class="digi_user_mouvment_datepicker" value="" />';
			$get_mouvement_button = '<button id="view_users_mouvement_list" >' . __('Voir', 'evarisk') . '</button>';
			$output .= sprintf( __('Mouvements des personnels entre les dates du %s au %s %s', 'evarisk'), $from_date, $to_date, $get_mouvement_button ) . '
<div id="digi_users_mouvment_betwwen_dates" ></div>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery(".digi_user_mouvment_datepicker").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: "yy-mm-dd",
		});
		jQuery("#view_users_mouvement_list").click(function(){
			var data = {
				action: "digi_ajax_load_user_stat_mouvement_between_dates",
				date_from: jQuery("#users_mouvement_between_from").val(),
				date_to: jQuery("#users_mouvement_between_to").val(),
			};
			jQuery.post("' . admin_url( 'admin-ajax.php' ) . '", data, function(response) {
				jQuery("#digi_users_mouvment_betwwen_dates").html( response);
			});
		});
	});
</script>';
		}
		else if ( $_POST['type'] == 'users_export' ) {

		}
		else if ( $_POST['type'] == 'user_not_affected_to_evaluation_SINCE' ) {
			$from_date = '<input type="text" id="users_not_present_since" name="users_not_present_since" class="digi_users_not_present_since_datepicker" value="" />';
			$get_mouvement_button = '<button id="view_users_not_present_since" >' . __('Voir', 'evarisk') . '</button>';
			$output .= sprintf( __('Personnes n\'ayant pas particip&eacute;e a un audit depuis %s %s', 'evarisk'), $from_date, $get_mouvement_button ) . '
<div id="digi_users_not_present_since" ></div>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery(".digi_users_not_present_since_datepicker").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: "yy-mm-dd",
		});
		jQuery("#view_users_not_present_since").click(function(){
			var data = {
				action: "digi_ajax_load_user_not_present_since_date",
				date_from: jQuery("#users_not_present_since").val(),
			};
			jQuery.post("' . admin_url( 'admin-ajax.php' ) . '", data, function(response) {
				jQuery("#digi_users_not_present_since").html( response);
			});
		});
	});
</script>';
		}
		else if ( !$output_from_another_way ) {
			$output = $empty_list_message;
		}

		echo json_encode( array($title, $output) );
		die();
	}

	function display_user_list( $user_list_2_display ) {
		$done_users = array();
		$output =  '
<table style="width:100%;" id="user_list_table"  >
	<thead>
		<tr>
			<th style="text-align:left; " >' . __( 'Id.', 'evarisk' ) . '</th>
			<th style="text-align:left; " >' . __( 'Nom', 'evarisk' ) . '</th>
			<th style="text-align:left; " >' . __( 'Pr&eacute;nom', 'evarisk' ) . '</th>
			<th style="text-align:left; " >' . __( 'Entr&eacute;e', 'evarisk' ) . '</th>
			<th style="text-align:left; " >' . __( 'Sortie', 'evarisk' ) . '</th>
			<th style="text-align:right; " >-</th>
		</tr>
	</thead>
	<tbody>';
		foreach ( $user_list_2_display as $user_id ) {
			if ( !in_array( $user_id, $done_users ) ) {
				$user_info = evaUser::getUserInformation( $user_id );
				$user_hiring_date = get_user_meta( $user_id, 'digi_hiring_date', true);
				$user_unhiring_date = get_user_meta( $user_id, 'digi_unhiring_date', true);
				$output .= '
	<tr>
		<td>' . ELEMENT_IDENTIFIER_U . $user_id . '</td>
		<td>' . $user_info[$user_id]['user_lastname'] . '</td>
		<td>' . $user_info[$user_id]['user_firstname'] . '</td>
		<td>' . ( !empty( $user_hiring_date ) ? mysql2date( 'd/m/Y', $user_hiring_date, true ) : '-' ) . '</td>
		<td>' . ( !empty( $user_unhiring_date ) ? mysql2date( 'd/m/Y', $user_unhiring_date, true ) : '-' ) . '</td>
		<td><a href="' . admin_url('users.php?page=digirisk_users_profil&amp;user_to_edit=' . $user_id) . '" target="digi_user_profil" ><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'view_vs.png" alt="' . __( 'Voir la fiche de l\'utiilsateur', 'evarisk' ) . '" title="' . __( 'Voir la fiche de l\'utiilsateur', 'evarisk' ) . '" /></a></td>
	</tr>';
				$done_users[] = $user_id;
			}
		}
		$output .= '
	</tbody>
	<tfoot>
		<tr>
			<th style="text-align:left; " >' . __( 'Id.', 'evarisk' ) . '</th>
			<th style="text-align:left; " >' . __( 'Nom', 'evarisk' ) . '</th>
			<th style="text-align:left; " >' . __( 'Pr&eacute;nom', 'evarisk' ) . '</th>
			<th style="text-align:left; " >' . __( 'Entr&eacute;e', 'evarisk' ) . '</th>
			<th style="text-align:left; " >' . __( 'Sortie', 'evarisk' ) . '</th>
			<th style="text-align:right; " >-</th>
		</tr>
	</tfoot>
</table>
<script type="text/javascript" >
	jQuery( document ).ready( function(){
		var table = jQuery( "#user_list_table" ).dataTable( {
			"bAutoWidth": false,
			"bInfo": false,
			"bPaginate": false,
			"bFilter": false,
	    });
	});
</script>';

		return $output;
	}

	function digi_ajax_load_user_not_present_since_date() {
		global $wpdb;
		$output = '';

		$query = $wpdb->prepare( "
			SELECT *
			FROM " . TABLE_LIAISON_USER_ELEMENT . " AS USER_LINK_EVALUATION
			WHERE date_affectation_reelle < %s
				AND ((USER_LINK_EVALUATION.table_element = '" . TABLE_UNITE_TRAVAIL . "_evaluation') OR (USER_LINK_EVALUATION.table_element = '" . TABLE_GROUPEMENT . "_evaluation'))
						OR (
							(USER_LINK_EVALUATION.table_element = '" . DIGI_DBT_USER_GROUP . "')
							AND (USER_LINK_EVALUATION.id_element IN (
								SELECT DISTINCT USER_LINK_GROUP.id_group
								FROM " . DIGI_DBT_LIAISON_USER_GROUP . " AS USER_LINK_GROUP
								WHERE USER_LINK_GROUP.status = 'valid'
							))
						   )
				AND id_user NOT IN (
					SELECT id_user
					FROM " . TABLE_LIAISON_USER_ELEMENT . " AS USER_LINK_EVALUATION
					WHERE date_affectation_reelle > %s
						AND ((USER_LINK_EVALUATION.table_element = '" . TABLE_UNITE_TRAVAIL . "_evaluation') OR (USER_LINK_EVALUATION.table_element = '" . TABLE_GROUPEMENT . "_evaluation'))
						OR (
							(USER_LINK_EVALUATION.table_element = '" . DIGI_DBT_USER_GROUP . "')
							AND (USER_LINK_EVALUATION.id_element IN (
								SELECT DISTINCT USER_LINK_GROUP.id_group
								FROM " . DIGI_DBT_LIAISON_USER_GROUP . " AS USER_LINK_GROUP
								WHERE USER_LINK_GROUP.status = 'valid'
							))
						   )
				)
			GROUP BY id_user
			ORDER BY date_affectation_reelle
		" , $_POST[ "date_from" ], $_POST[ "date_from" ]);
		$user_list = $wpdb->get_results( $query );

		$output .= '<fieldset style="margin:36px 0 0 0;" ><legend>' . sprintf( __('Personnel n\'ayant pas &eacute;t&eacute; revu depuis le %s', 'evarisk'), mysql2date( 'd/m/Y', $_POST[ "date_from" ], true ) ) . '</legend>';
		if ( !empty($user_list) ) {

			$output .=  '
<table style="width:100%;" id="user_list_table"  >
	<thead>
		<tr>
			<th style="text-align:left; " >' . __( 'Id.', 'evarisk' ) . '</th>
			<th style="text-align:left; " >' . __( 'Nom', 'evarisk' ) . '</th>
			<th style="text-align:left; " >' . __( 'Pr&eacute;nom', 'evarisk' ) . '</th>
			<th style="text-align:left; " >' . __( 'Derni&eacute;re &eacute;valuation', 'evarisk' ) . '</th>
			<th style="text-align:right; " >-</th>
		</tr>
	</thead>
	<tbody>';
			foreach ( $user_list as $user ) {
				if ( !in_array( $user->id_user, $done_users ) ) {
					$user_info = evaUser::getUserInformation( $user->id_user );
					$element_datas = '';
					switch ( $user->table_element ) {
						case TABLE_GROUPEMENT . '_evaluation':
							$query = $wpdb->prepare( "SELECT nom FROM " . TABLE_GROUPEMENT . " WHERE id = %d", $user->id_element );
							$nom_groupement = $wpdb->get_var( $query );
							$element_datas = ELEMENT_IDENTIFIER_GP . $user->id_element . ' - ' . $nom_groupement;
							break;
						case TABLE_UNITE_TRAVAIL . '_evaluation':
							$query = $wpdb->prepare( "SELECT nom FROM " . TABLE_UNITE_TRAVAIL . " WHERE id = %d", $user->id_element );
							$nom_unite = $wpdb->get_var( $query );
							$element_datas = ELEMENT_IDENTIFIER_GP . $user->id_element . ' - ' . $nom_unite;
							break;
						case DIGI_DBT_LIAISON_USER_GROUP:

							break;
					}
					$output .= '
	<tr>
		<td>' . ELEMENT_IDENTIFIER_U . $user->id_user . '</td>
		<td>' . $user_info[$user->id_user]['user_lastname'] . '</td>
		<td>' . $user_info[$user->id_user]['user_firstname'] . '</td>
		<td>' . sprintf( __( 'Sur %s le %s', 'evarisk' ), $element_datas, mysql2date( 'd/m/Y H:i', $user->date_affectation_reelle, true ) ) . '</td>
		<td><a href="' . admin_url('users.php?page=digirisk_users_profil&amp;user_to_edit=' . $user->id_user) . '" target="digi_user_profil" ><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'view_vs.png" alt="' . __( 'Voir la fiche de l\'utiilsateur', 'evarisk' ) . '" title="' . __( 'Voir la fiche de l\'utiilsateur', 'evarisk' ) . '" /></a></td>
	</tr>';
					$done_users[] = $user_id;
				}
			}
			$output .= '
	</tbody>
	<tfoot>
		<tr>
			<th style="text-align:left; " >' . __( 'Id.', 'evarisk' ) . '</th>
			<th style="text-align:left; " >' . __( 'Nom', 'evarisk' ) . '</th>
			<th style="text-align:left; " >' . __( 'Pr&eacute;nom', 'evarisk' ) . '</th>
			<th style="text-align:left; " >' . __( 'Derni&eacute;re &eacute;valuation', 'evarisk' ) . '</th>
			<th style="text-align:right; " >-</th>
		</tr>
	</tfoot>
</table>
<script type="text/javascript" >
	jQuery( document ).ready( function(){
		var table = jQuery( "#user_list_table" ).dataTable( {
			"bAutoWidth": false,
			"bInfo": false,
			"bPaginate": false,
			"bFilter": false,
	    });
	});
</script>';





		}
		else {
			$output .= __("Aucun utilisateur entrant uniquement sur cette p&eacute;riode", 'evarisk');
		}
		$output .= '</fieldset>';

		wp_die($output);
	}

	function digi_ajax_mouvement_between_dates() {
		global $wpdb;
		$output = '';

		$query = $wpdb->prepare("
			SELECT user_id
			FROM " . $wpdb->usermeta . '
			WHERE
				meta_key = "%1$s"
				AND (meta_value BETWEEN "%2$s" AND "%3$s")
				AND user_id NOT IN (
					SELECT user_id
					FROM wp_usermeta
					WHERE
						meta_key = "%4$s"
						AND (meta_value BETWEEN "%2$s" AND "%3$s")
				)'
			, 'digi_hiring_date', $_POST['date_from'], $_POST['date_to'], 'digi_unhiring_date');
		$user_list = $wpdb->get_results( $query );
		$output .= '<fieldset style="margin:36px 0 0 0;" ><legend>' . __('Personnel entrant', 'evarisk') . '</legend>';
		if ( !empty($user_list) ) {
			$user_list_2_display = array();
			foreach ( $user_list as $user ) {
				$user_list_2_display[] = $user->user_id;
			}
			$output .= self::display_user_list( $user_list_2_display );
		}
		else {
			$output .= __("Aucun utilisateur entrant uniquement sur cette p&eacute;riode", 'evarisk');
		}
		$output .= '</fieldset>';

		$query = $wpdb->prepare("
			SELECT user_id
			FROM " . $wpdb->usermeta . '
			WHERE
				meta_key = "%1$s"
				AND (meta_value BETWEEN "%2$s" AND "%3$s")
				AND user_id NOT IN (
					SELECT user_id
					FROM wp_usermeta
					WHERE
						meta_key = "%4$s"
						AND (meta_value BETWEEN "%2$s" AND "%3$s")
				)'
			, 'digi_unhiring_date', $_POST['date_from'], $_POST['date_to'], 'digi_hiring_date');
		$user_list = $wpdb->get_results( $query );
		$output .= '<fieldset style="margin:36px 0 0 0;" ><legend>' . __('Personnel entrant', 'evarisk') . '</legend>';
		if ( !empty($user_list) ) {
			$user_list_2_display = array();
			foreach ( $user_list as $user ) {
				$user_list_2_display[] = $user->user_id;
			}
			$output .= self::display_user_list( $user_list_2_display );
		}
		else {
			$output .= __("Aucun utilisateur sortant uniquement sur cette p&eacute;riode", 'evarisk');
		}
		$output .= '</fieldset>';

		$query = $wpdb->prepare("
			SELECT user_id
			FROM " . $wpdb->usermeta . '
			WHERE
				meta_key = "%1$s"
				AND (meta_value BETWEEN "%2$s" AND "%3$s")
				AND user_id IN (
					SELECT user_id
					FROM wp_usermeta
					WHERE
						meta_key = "%4$s"
						AND (meta_value BETWEEN "%2$s" AND "%3$s")
				)'
				, 'digi_unhiring_date', $_POST['date_from'], $_POST['date_to'], 'digi_hiring_date');
		$user_list = $wpdb->get_results( $query );
		$output .= '<fieldset style="margin:36px 0 0 0;" ><legend>' . __('Personnel entrant et sortant sur la p&eacute;riode', 'evarisk') . '</legend>';
		if ( !empty($user_list) ) {
			$user_list_2_display = array();
			foreach ( $user_list as $user ) {
				$user_list_2_display[] = $user->user_id;
			}
			$output .= self::display_user_list( $user_list_2_display );
		}
		else {
			$output .= __("Aucun utilisateur entrant et sortant sur cette p&eacute;riode", 'evarisk');
		}
		$output .= '</fieldset>';

		echo $output;
		die();
	}


	/**
	*
	*/
	function importUserPage(){
		global $wpdb;
		$separatorExample = '<span class="fieldSeparator" >[fieldSeparator]</span>';

		$importAction = isset($_POST['act']) ? digirisk_tools::IsValid_Variable($_POST['act']) : '';
		$userRoles = isset($_POST['userRoles']) ? digirisk_tools::IsValid_Variable($_POST['userRoles']) : '';
		$fieldSeparator = isset($_POST['fieldSeparator']) ? digirisk_tools::IsValid_Variable($_POST['fieldSeparator']) : '';
		$sendUserMail = isset($_POST['sendUserMail']) ? digirisk_tools::IsValid_Variable($_POST['sendUserMail']) : '';

		$optionEmailDomain = '';
		$checkEmailDomain = digirisk_options::getOptionValue('emailDomain');
		if(isset($_POST['domaineMail']) && ($checkEmailDomain != $_POST['domaineMail'])){
			digirisk_options::updateDigiOption('emailDomain', $_POST['domaineMail']);
			$checkEmailDomain = digirisk_options::getOptionValue('emailDomain');
		}

		if($importAction != ''){
			$userToCreate = array();
			$importResult = '';

			/*	Check if there are lines to create without sending a file	*/
			$userLinesToCreate = isset($_POST['userLinesToCreate']) ? (string) digirisk_tools::IsValid_Variable($_POST['userLinesToCreate']) : '';
			if($userLinesToCreate != '')
				$userToCreate = array_merge($userToCreate, explode("\n", trim($userLinesToCreate)));
			else
				$importResult .= __('Aucun utilisateurs n\'a &eacute;t&eacute; ajout&eacute; depuis le champs texte', 'evarisk') . '<br/>';

			/*	Check if a file has been sending */
			if($_FILES['userFileToCreate']['error'] != UPLOAD_ERR_NO_FILE){
				$file = $_FILES['userFileToCreate'];
				if($file['error']){
					switch ($file['error']){
						case UPLOAD_ERR_INI_SIZE:
							$subFileError .= sprintf(__('Le fichier que vous avez envoy&eacute; est trop lourd: %s taille autoris&eacute;e %s', 'evarisk'), $file['size'], upload_max_filesize);
						break;
						case UPLOAD_ERR_FORM_SIZE:
							$subFileError .= sprintf(__('Le fichier que vous avez envoy&eacute; est trop lourd: %s taille autoris&eacute;e %s', 'evarisk'), $file['size'], upload_max_filesize);
						break;
						case UPLOAD_ERR_PARTIAL:
							$subFileError .= __('Le fichier que vous avez envoy&eacute; n\'a pas &eacute;t&eacute; compl&eacute;tement envoy&eacute;', 'evarisk');
						break;
					}
					$importResult .= '<h4 style="color:#FF0000;">' . __('Une erreur est survenue lors de l\'envoie du fichier', 'evarisk') . '</h4><p>' . $subFileError . '</p>';
				}
				elseif(!is_uploaded_file($file['tmp_name']))
					$importResult .= sprintf(__('Le fichier %s n\'a pas pu &ecirc;tre envoy&eacute;', 'evarisk'), $file['name']);
				else
					$userToCreate = array_merge($userToCreate, file($file['tmp_name']));
			}

			if(is_array($userToCreate) && (count($userToCreate) > 0)){
				$createdUserNumber = 0;
				$errors = array();

				foreach($userToCreate as $userInfos) {
					$userInfosComponent = array();
					if (trim($userInfos) != ''){
						$userInfosComponent = explode($fieldSeparator, $userInfos);
						$userInfosComponent[0] = trim(strtolower(digirisk_tools::slugify_noaccent($userInfosComponent[0])));
						$userInfosComponent[1] = trim($userInfosComponent[1]);
						$userInfosComponent[2] = trim($userInfosComponent[2]);
						$userInfosComponent[3] = trim($userInfosComponent[3]);
						$userInfosComponent[4] = trim(strtolower(digirisk_tools::slugify_noaccent($userInfosComponent[4])));
						$userInfosComponent[5] = trim($userInfosComponent[5]);
						$checkErrors = 0;

						/*	Check if the email adress is valid or already exist	*/
						if(!is_email($userInfosComponent[4])){
							$errors[] = sprintf(__('L\'adresse email <b>' . $userInfosComponent[4] . '</b> de la ligne %s n\'est <b>pas valide</b>', 'evarisk'), $userInfos);
							$checkErrors++;
						}
						$checkIfMailExist = $wpdb->get_row("SELECT user_email FROM " . $wpdb->users . " WHERE user_email = '" . ($userInfosComponent[4]) . "'");
						if($checkIfMailExist){
							$errors[] = sprintf(__('L\'adresse email <b>' . $userInfosComponent[4] . '</b> de la ligne %s est <b>d&eacute;j&agrave; utilis&eacute;</b>', 'evarisk'), $userInfos);
							$checkErrors++;
						}

						/*	Check if the username is valid or already exist	*/
						if(!validate_username($userInfosComponent[0])){
							$errors[] = sprintf(__('L\'identifiant <b>' . $userInfosComponent[0] . '</b> de la ligne %s n\'est <b>pas valide</b>', 'evarisk'), $userInfos);
							$checkErrors++;
						}
						if(username_exists($userInfosComponent[0])){
							$errors[] = sprintf(__('L\'identifiant <b>' . $userInfosComponent[0] . '</b> de la ligne %s est <b>d&eacute;j&agrave; utilis&eacute;</b>', 'evarisk'), $userInfos);
							$checkErrors++;
						}

						/*	There are no errors on the email and username so we can create the user	*/
						if($checkErrors == 0){
							/*	Check if the password is given in the list to create, if not we generate one */
							if($userInfosComponent[3] == '')
								$userInfosComponent[3] = substr(md5(uniqid(microtime())), 0, 7);

							/*	Start creating the user	*/
							$newUserID = 0;
							$newUserID =
								wp_insert_user(
									array(
											"user_login" => $userInfosComponent[0],
											"first_name" => $userInfosComponent[1],
											"last_name" => $userInfosComponent[2],
											"user_pass" => $userInfosComponent[3],
											"user_email" => $userInfosComponent[4]
										)
								);

							if($newUserID <= 0)
								$errors[] = sprintf(__('L\'utilisateur de la ligne %s n\'a pas pu &ecirc;tre ajout&eacute;', 'evarisk'), $userInfos);
							else{
								$user_import['user_imatriculation'] = $userInfosComponent[6];
								$user_import['user_imatriculation_key'] = $userInfosComponent[7];
								$user_import['user_birthday'] = $userInfosComponent[8];
								$user_import['user_gender'] = $userInfosComponent[9];
								$user_import['user_nationnality'] = $userInfosComponent[10];
								$user_import['user_adress'] = $userInfosComponent[11];
								$user_import['user_adress_2'] = $userInfosComponent[12];
								$user_import['user_adress_postal_code'] = $userInfosComponent[13];
								$user_import['user_adress_city'] = $userInfosComponent[14];
// 								$user_import['digi_hiring_date'] = $userInfosComponent[15];
								$user_import['user_profession'] = $userInfosComponent[16];
								$user_import['user_professional_qualification'] = $userInfosComponent[17];
								$user_import['user_insurance_ste'] = $userInfosComponent[18];
// 								$user_import['digi_unhiring_date'] = $userInfosComponent[19];

								global $userWorkAccidentMandatoryFields;
								$user_is_valid_for_accident = 'yes';
								foreach($userWorkAccidentMandatoryFields as $field_identifier){
									if(isset($user_import[$field_identifier]) && (trim($user_import[$field_identifier]) == ''))
										$user_is_valid_for_accident = 'no';
								}
								$user_import['user_is_valid_for_accident'] = $user_is_valid_for_accident;

								update_user_meta($newUserID, 'digirisk_information', $user_import);
								update_user_meta($newUserID, 'digi_hiring_date', $userInfosComponent[15]);
								update_user_meta($newUserID, 'digi_unhiring_date', $userInfosComponent[19]);

								if($sendUserMail != '')
									wp_new_user_notification($newUserID, $userInfosComponent[3]);
								$createdUserNumber++;

								/*	Affect a role to the new user regarding on the import file or lines and if empty the main roe field	*/
								if ($userInfosComponent[5] == '')
									$userInfosComponent[5] = $userRoles;

								$userRole = new WP_User($newUserID);
								$userRole->set_role($userInfosComponent[5]);
							}
						}
					}
				}

				if($createdUserNumber >= 1){
					$subResult = sprintf(__('%s utilisateur a &eacute;t&eacute; cr&eacute;&eacute;', 'evarisk'), $createdUserNumber);
					if($createdUserNumber > 1)
						$subResult = sprintf(__('%s utilisateurs ont &eacute;t&eacute; cr&eacute;&eacute;s', 'evarisk'), $createdUserNumber);

					$importResult .= '<h4 style="color:#00CC00;">' . __('L\'import s\'est termin&eacute; avec succ&eacute;s. Veuillez trouver le r&eacute;sultat ci-dessous', 'evarisk') . '</h4><ul>' . $subResult . '</ul>';

					if($sendUserMail != '')
						$importResult .= '<div style="font-weight:bold;" >' . __('Les nouveaux utilisateurs recevront leurs mot de passe par email', 'evarisk') . '</div>';
				}
				if(is_array($errors) && (count($errors) > 0)){
					$subErrors = '';
					foreach($errors as $er){
						$subErrors .= '<li>' . $er . '</li>';
					}
					$importResult .= '<h4 style="color:#FF0000;">' . __('Des erreurs sont survenues. Veuillez trouver la liste ci-dessous', 'evarisk') . '</h4><ul>' . $subErrors . '</ul>';
				}
			}
?>
		<div style="width:80%;margin:18px auto;padding:6px;border:1px dashed;"  ><?php echo $importResult; ?></div>
<?php
		}
?>
<div id="icon-users" class="icon32"><br /></div>
<h2><?php _e('Import d\'utilisateurs', 'evarisk'); ?></h2>
<br/>
<div id="ajax-response" style="display:none;" >&nbsp;</div>
<script type="text/javascript" >
	function changeSeparator(){
		digirisk('.fieldSeparator').html(digirisk('#fieldSeparator').val());
	}
	digirisk(document).ready(function(){
		changeSeparator();
		digirisk('#fieldSeparator').blur(function(){changeSeparator()});
		digirisk('#userLinesToCreate').blur(function(){
			if(jQuery(this).val() != ''){
				jQuery("#importSubmit_rapid").attr("disabled", false);
			}
			else{
				jQuery("#importSubmit_rapid").attr("disabled", true);
			}
		});
		digirisk('#userLinesToCreate').keypress(function(){
			if(jQuery(this).val() != ''){
				jQuery("#importSubmit_rapid").attr("disabled", false);
			}
			else{
				jQuery("#importSubmit_rapid").attr("disabled", true);
			}
		});

		jQuery('#ajouterUtilisateurListe').click(function(){
			var error = 0;
			digirisk('#mailDomainContainer').css('color', '#000000');
			digirisk('#firstNameContainer').css('color', '#000000');
			digirisk('#lastNameContainer').css('color', '#000000');
			jQuery('#emailContainer').css('color', '#000000');
			digirisk('#fastAddErrorMessage').hide();

			digirisk('#domaineMail').val(digirisk('#domaineMail').val().replace("@", ""));

			if(digirisk('#domaineMail').val() == ""){
				digirisk('#mailDomainContainer').css('color', '#FF0000');
				error++;
			}
			if(digirisk('#prenomUtilisateur').val() == ""){
				digirisk('#firstNameContainer').css('color', '#FF0000');
				error++;
			}
			if(digirisk('#nomUtilisateur').val() == ""){
				digirisk('#lastNameContainer').css('color', '#FF0000');
				error++;
			}
			if(jQuery('#emailUtilisateur').val() == ""){
				jQuery('#emailContainer').css('color', '#FF0000');
				error++;
			}

			if(error > 0){
				digirisk('#fastAddErrorMessage').show();
			}
			else{
				jQuery("#importSubmit_rapid").attr("disabled", false);
				identifiant = digirisk('#prenomUtilisateur').val() + '.' + digirisk('#nomUtilisateur').val();
				prenom = digirisk('#prenomUtilisateur').val();
				nom = digirisk('#nomUtilisateur').val();
				motDePasse = digirisk('#motDePasse').val();
				emailUtilisateur = digirisk('#emailUtilisateur').val()
				roleUtilisateur = digirisk('#userRoles').val();

				user_imatriculation = digirisk('#user_imatriculation').val();
				user_imatriculation_key = digirisk('#user_imatriculation_key').val();
				user_birthday = digirisk('#user_birthday').val();
				user_gender = digirisk('#user_gender').val();
				user_nationnality = digirisk('#user_nationnality').val();
				user_adress_1 = digirisk('#user_adress').val();
				user_adress_2 = digirisk('#user_adress_2').val();
				user_adress_postal_code = digirisk('#user_adress_postal_code').val();
				user_adress_city = digirisk('#user_adress_city').val();
				digi_hiring_date = digirisk('#digi_hiring_date').val();
				digi_unhiring_date = digirisk('#digi_unhiring_date').val();
				user_profession = digirisk('#user_profession').val();
				user_professional_qualification = digirisk('#user_professional_qualification').val();
				user_insurance_ste = digirisk('#user_insurance_ste').val();

				newline = identifiant + digirisk('#fieldSeparator').val() + prenom + digirisk('#fieldSeparator').val() + nom + digirisk('#fieldSeparator').val() + motDePasse + digirisk('#fieldSeparator').val() + emailUtilisateur + digirisk('#fieldSeparator').val() + roleUtilisateur;

				newline += digirisk('#fieldSeparator').val() + user_imatriculation + digirisk('#fieldSeparator').val() + user_imatriculation_key + digirisk('#fieldSeparator').val() + user_birthday + digirisk('#fieldSeparator').val() + user_gender + digirisk('#fieldSeparator').val() + user_nationnality + digirisk('#fieldSeparator').val() + user_adress_1 + digirisk('#fieldSeparator').val() + user_adress_2 + digirisk('#fieldSeparator').val() + user_adress_postal_code + digirisk('#fieldSeparator').val() + user_adress_city + digirisk('#fieldSeparator').val() + digi_hiring_date + digirisk('#fieldSeparator').val() + user_profession + digirisk('#fieldSeparator').val() + user_professional_qualification + digirisk('#fieldSeparator').val() + user_insurance_ste + digirisk('#fieldSeparator').val() + digi_unhiring_date;

				if(digirisk('#userLinesToCreate').val() != ''){
					newline = '\r\n' + newline;
				}
				digirisk('#userLinesToCreate').val(digirisk('#userLinesToCreate').val() + newline);
				digirisk('#prenomUtilisateur').val("");
				digirisk('#nomUtilisateur').val("");
				digirisk('#emailUtilisateur').val("");
				evarisk('#emailUtilisateur').val("");

				digirisk('#user_imatriculation').val("");
				digirisk('#user_imatriculation_key').val("");
				digirisk('#user_birthday').val("");
				digirisk('#user_gender').val("");
				digirisk('#user_nationnality').val("");
				digirisk('#user_adress').val("");
				digirisk('#user_adress_2').val("");
				digirisk('#user_adress_postal_code').val("");
				digirisk('#user_adress_city').val("");
				digirisk('#digi_hiring_date').val("");
				digirisk('#digi_unhiring_date').val("");
				digirisk('#user_profession').val("");
				digirisk('#user_professional_qualification').val("");
				digirisk('#user_insurance_ste').val("");

<?php echo $optionEmailDomain;	?>
			}
		});

		jQuery('#prenomUtilisateur').blur(function(){
			if((jQuery('#prenomUtilisateur').val() != "") && (jQuery('#nomUtilisateur').val() != "")){
				jQuery('#emailUtilisateur').val(jQuery('#prenomUtilisateur').val() + '.' + jQuery('#nomUtilisateur').val() + '@' + jQuery('#domaineMail').val());
				if(jQuery('#domaineMail').val() == ""){
					jQuery('#email_domain_error').show();
				}
				else{
					jQuery('#email_domain_error').hide();
				}
			}
		});

		jQuery("#import_user_form_file_container_switcher").click(function(){
			jQuery("#import_user_form_file_container").toggle();
			jQuery("#user_import_container_switcher_icon").toggleClass("user_import_container_opener");
			jQuery("#user_import_container_switcher_icon").toggleClass("user_import_container_closer");
		});

		jQuery("#complementary_fieds_switcher").click(function(){
			goTo("#digirisk_import_user_easy_form_container");
			jQuery("#complementary_fieds").toggle();
			jQuery("#complementary_fieds_icon").toggleClass("user_import_container_opener");
			jQuery("#complementary_fieds_icon").toggleClass("user_import_container_closer");
		});
	});
</script>
<form enctype="multipart/form-data" method="post" action="#" >
	<input type="hidden" name="act" id="act" value="1" />

	<!-- 	Start of fast add part	-->
	<h3 class="clear" ><?php echo __('Ajout rapide d\'utilisateurs', 'evarisk'); ?></h3>
	<table class="digirisk_import_user_easy_form_container" id="digirisk_import_user_easy_form_container" >
		<tr>
			<td class="bold" ><?php _e('Informations obligatoires', 'evarisk'); ?></td>
			<td id="complementary_fieds_switcher" class="pointer" ><span id="complementary_fieds_icon" class="alignleft ui-icon user_import_container_opener" >&nbsp;</span><?php _e('Champs suppl&eacute;mentaires', 'evarisk'); ?></td>
		</tr>
		<tr>
			<td class="digi_mandatory_fields_container" >
				<table class="digirisk_import_user_easy_form" >
					<tr>
						<td class="digi_import_user_main_info_name" id="mailDomainContainer"><?php echo ucfirst(strtolower(__('domaine de l\'adresse email', 'evarisk'))); ?></td>
						<td class="digi_import_user_main_info_input" ><div class="alignleft" ><?php _e('adresse.email', 'evarisk'); ?>@</div><input type="text" value="<?php echo $checkEmailDomain; ?>" id="domaineMail" name="domaineMail" /></td>
					</tr>
					<tr>
						<td class="digi_import_user_main_info_name" ><?php echo ucfirst(strtolower(__('mot de passe par d&eacute;faut', 'evarisk'))); ?></td>
						<td class="digi_import_user_main_info_input"><input type="text" value="" id="motDePasse" name="motDePasse" /><br/>
						<span style="font-size:9px;" ><?php echo __('Laissez vide pour un mot de passe al&eacute;atoire', 'evarisk'); ?></span></td>
					</tr>
					<tr>
						<td class="digi_import_user_main_info_name" >
							<?php echo __('Envoyer le mot de passe aux utilisateurs.', 'evarisk'); ?>
						</td>
						<td class="digi_import_user_main_info_input" >
							<input type="checkbox" name="sendUserMail" id="sendUserMail" /><span style="font-weight:bold;font-size:9px;" ><?php echo __('(Peut ne pas fonctionner sur certains serveurs)', 'evarisk'); ?></span>
						</td>
					</tr>
					<tr>
						<td class="digi_import_user_main_info_name" id="lastNameContainer"><?php echo ucfirst(strtolower(__('nom', 'evarisk'))); ?></td>
						<td class="digi_import_user_main_info_input" ><input type="text" value="" id="nomUtilisateur" name="nomUtilisateur" /></td>
					</tr>
					<tr>
						<td class="digi_import_user_main_info_name" id="firstNameContainer"><?php echo ucfirst(strtolower(__('prenom', 'evarisk'))); ?></td>
						<td class="digi_import_user_main_info_input" ><input type="text" value="" id="prenomUtilisateur" name="prenomUtilisateur" /></td>
					</tr>
					<tr>
						<td class="wpsendsms_import_user_main_info_name" id="emailContainer"><?php echo ucfirst(strtolower(__('email', 'sendsms'))); ?></td>
						<td class="wpsendsms_import_user_main_info_input" ><input type="text" value="" id="emailUtilisateur" name="emailUtilisateur" /><div id="email_domain_error" style="display:none;color:#FF0000;" ><?php echo __('Vous pouvez remplir le champs "Domaine de l\'adresse email" pour que vos emails soient automatique cr&eacute;&eacute;s', 'sendsms'); ?></div></td>
					</tr>
					<tr>
						<td class="digi_import_user_main_info_name">
							<?php echo __('R&ocirc;le pour les utilisateurs', 'evarisk'); ?><br/>
							<span style="font-style:italic;font-size:10px;" ><?php echo __('Si aucun r&ocirc;le n\'a &eacute;t&eacute; d&eacute;fini dans le fichier', 'evarisk'); ?></span>
						</td>
						<td class="digi_import_user_main_info_input" >
							<select name="userRoles" id="userRoles" >
								<?php
									if ( !isset($wp_roles) )
									{
										$wp_roles = new WP_Roles();
									}
									foreach ($wp_roles->get_names() as $role => $roleName)
									{
										$selected = '';
										if(($userRoles == '') && ($role == 'subscriber'))
										{
											$selected = 'selected = "selected"';
										}
										elseif(($userRoles != '') && ($role == $userRoles))
										{
											$selected = 'selected = "selected"';
										}
										echo '<option value="' . $role . '" ' . $selected . ' >' . __($roleName) . '</option>';
									}
								?>
							</select>
						</td>
					</tr>
				</table>

			</td>
			<td class="digi_complmentary_fields_container" >
				<div id="complementary_fieds" class="hide" ><?php self::user_additionnal_field(null, 'import'); ?></div>
			</td>
		</tr>

		<tr>
			<td colspan="2" >&nbsp;</td>
		</tr>

		<tr>
			<td colspan="2" style="text-align:center;" ><input type="button" class="button-primary" value="<?php echo __('Ajouter &agrave; la liste des utilisateurs &agrave; importer', 'evarisk'); ?>" id="ajouterUtilisateurListe" name="ajouterUtilisateurListe" /><div id="fastAddErrorMessage" style="display:none;color:#FF0000;" ><?php echo __('Merci de remplir les champs marqu&eacute;s en rouge', 'evarisk'); ?></div></td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:center;" ><textarea name="userLinesToCreate" id="userLinesToCreate" cols="70" rows="5"></textarea></td>
		</tr>
	</table>
	<!-- 	Submit form button	-->
<?php
	if(current_user_can('digi_import_user')){
?>
	<div class="user_rapid_import_button" ><input disabled="disabled" type="submit" class="button-primary" name="importSubmit_rapid" id="importSubmit_rapid" value="<?php echo __('Importer les utilisateurs', 'evarisk'); ?>" /></div>
<?php
	}
?>

	<br/>
	<br/>
	<br/>

	<!-- 	Start of file specification part	-->
	<h3 class="pointer" id="import_user_form_file_container_switcher" ><span id="user_import_container_switcher_icon" class="alignleft ui-icon user_import_container_opener" >&nbsp;</span><?php echo __('Ajout d\'utilisateur depuis un fichier', 'evarisk'); ?></h3>
	<div id="import_user_form_file_container" class="hide" >
		<div >
			<div><a href="<?php echo EVA_MODELES_PLUGIN_URL; ?>import_users.ods" ><?php echo __('Vous pouvez t&eacute;l&eacute;charger le fichier pour construire l\'import ici', 'evarisk'); ?></a></div>
			<?php echo __('Chaque ligne devra respecter le format ci-apr&egrave;s&nbsp;:', 'evarisk'); ?>
			<br/><span style="font-style:italic;font-size:10px;" ><?php echo '<span style="color:#CC0000;" >' . __('Les champs identifiants et email sont obligatoires.', 'evarisk') . '</span><br/>' . __('Vous n\'&ecirc;tes pas oblig&eacute; de renseigner tous les champs mais tous les s&eacute;parateur doivent &ecirc;tre pr&eacute;sent.', 'evarisk') . '&nbsp;&nbsp;' . __('Exemple&nbsp;', 'evarisk') . '&nbsp;<span style="font-weight:bold;" >' . __('identifiant', 'evarisk') . $separatorExample . $separatorExample . $separatorExample . $separatorExample . __('email', 'evarisk') . $separatorExample . '</span>' . $separatorExample . $separatorExample . $separatorExample . $separatorExample . $separatorExample . $separatorExample . $separatorExample . $separatorExample . $separatorExample . $separatorExample . $separatorExample . $separatorExample . $separatorExample; ?></span>
			<div style="margin:3px 6px;padding:12px;border:1px solid #333333;width:80%;text-align:center;" ><?php echo '<span style="color:#CC0000;" >' . __('identifiant', 'evarisk') . '</span>' . $separatorExample . __('prenom', 'evarisk') . $separatorExample . __('nom', 'evarisk') . $separatorExample . __('mot de passe', 'evarisk') . $separatorExample . '<span style="color:#CC0000;" >' . __('email', 'evarisk') . '</span>' . $separatorExample . __('role', 'evarisk') . '<span class="italic digi_import_user_additionnal_field" >' . $separatorExample . __('n&ordm; immatriculation', 'evarisk') . $separatorExample . __('cl&eacute; immatriculation', 'evarisk') . $separatorExample . __('date de naissance', 'evarisk') . $separatorExample . __('sexe', 'evarisk') . $separatorExample . __('nationalit&eacute;', 'evarisk') . $separatorExample . __('adresse 1', 'evarisk') . $separatorExample . __('adresse 2', 'evarisk') . $separatorExample . __('code postal', 'evarisk') . $separatorExample . __('ville', 'evarisk') . $separatorExample . __('date embauche', 'evarisk') . $separatorExample . __('profession', 'evarisk') . $separatorExample . __('qualification professionnelle', 'evarisk') . $separatorExample . __('societe d\'assurance', 'evarisk') . '</span>'; ?></div>
		</div>
		<div >
			<table style="margin:0px 36px;" summary="" cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<?php echo __('S&eacute;parateur de champs', 'evarisk'); ?>
					</td>
					<td>
						<input type="text" name="fieldSeparator" id="fieldSeparator" value=";" />
					</td>
				</tr>
			</table>
		</div><?php echo __('Vous pouvez envoyer un fichier contenant les utilisateurs &agrave; cr&eacute;er (extension autoris&eacute;e *.odt, *.csv, *.txt)', 'evarisk'); ?>
		<input type="file" id="userFileToCreate" name="userFileToCreate" />
		<!-- 	Submit form button	-->
<?php
	if(current_user_can('digi_import_user')){
?>
		<div class="user_import_button" ><input type="submit" class="button-primary" name="importSubmit" id="importSubmit" value="<?php echo __('Importer les utilisateurs', 'evarisk'); ?>" /></div>
<?php
	}
?>
	</div>

</form>
<?php
	}

	/**
	*
	*/
	function digi_user_profil() {
		global $current_user, $wpdb;

		$user_profil_content = '';

		$user_to_edit = (isset($_REQUEST['user_to_edit']) && ((int)$_REQUEST['user_to_edit'] > 0)) ? digirisk_tools::IsValid_Variable($_REQUEST['user_to_edit']) : $current_user->ID;
		$_REQUEST['user_id'] = $user_to_edit;

		$user = new WP_User($user_to_edit);
		$user_infos = self::getUserInformation($user_to_edit);
		$user_infos = $user_infos[$user_to_edit];

		{/*	Get user right	*/
			$user_roles = '  ';
			$digiPermissionForm = '
				<div class="digi-profil-utilisateur-links-to-permission-editor" >
					<span class="digi_alert" >' . __( 'ATTENTION! Cette interface ne permet que la visualisation des droits de l\'utilisateur. Pour modifier ces permissions utilisez un des liens ci-dessous', 'evarisk' ) . '</span>
					<ul>
						<li><a href="' . admin_url( 'users.php?page=digirisk_user_right&action=edit&role=' . $user->roles[ 0 ] ) . '" target="_digi_edit_user_role" >' . __( '&Eacute;dition des droits du r&ocirc;le de l\'utilisateur', 'evarisk' ) . '</a></li>
						<li>';

			if ( $user_to_edit != $current_user->ID ) {
				$digiPermissionForm .= '
							<a href="' . admin_url( 'user-edit.php?user_id=' . $user_to_edit . '#digi-user-right-table' ) . '" target="_digi_edit_user_specific_permission" >' . __( '&Eacute;dition des droits sp&eacute;cifique &agrave; l\'utilisateur', 'evarisk' ) . '</a>';
			}
			else {
				$digiPermissionForm .=	__( 'Vous ne pouvez pas &eacute;diter vos propres permissions', 'evarisk' );
			}

			$digiPermissionForm .= '
						</li>
					</ul>
				</div>
			';
			foreach($user->roles as $role){
				$user_roles .= translate_user_role($role) . ', ';
			}
			$user_roles = trim(substr($user_roles, 0, -2));
			if($user_roles != ''){
				$digiPermissionForm .= sprintf(__('R&ocirc;le de l\'utilisateur %s', 'evarisk'), $user_roles);
			}
			ob_start();
			digirisk_permission::permission_management($user, 'digi_user_profile');
			$digiPermissionForm .= ob_get_contents();
			ob_end_clean();
		}

		{/*	Get element associated 	*/
			$user_tree_affecation = $user_tree_eval_affecation = $user_ac_affecation = '';
			$gpt_list = $ut_list = $gpt_eval_list = $ut_eval_list = $t_list = $st_list = array();
			$user_affectation = evaUserLinkElement::get_user_affected_element($user_to_edit);
			foreach($user_affectation as $affectation_information){
				unset($sub_content);$sub_content = '';

				/*	Get information about current element	*/
				$affectation_information_table_element = str_replace('_evaluation', '', $affectation_information->table_element);
				$query = $wpdb->prepare("SELECT * FROM " . $affectation_information_table_element . " WHERE Status = 'Valid' AND id = %d", $affectation_information->id_element);
				$element = $wpdb->get_row($query);

				if( !empty($element) && ($element->Status == 'Valid')){
					switch($affectation_information->table_element){
						case TABLE_GROUPEMENT:{
							/*	Read element ancestor	*/
							$ancetres = Arborescence::getAncetre($affectation_information->table_element, $element, "limiteGauche ASC", '1', "");
							$miniFilAriane = '         ';
							foreach($ancetres as $ancetre){
								if($ancetre->nom != "Groupement Racine"){
									$miniFilAriane .= '<span class="element_identifier" >' . ELEMENT_IDENTIFIER_GP . $ancetre->id . '</span>&nbsp;-&nbsp;' . $ancetre->nom . ' &raquo; ';
								}
							}
							$miniFilAriane = trim(substr($miniFilAriane, 0, -9));
							if($miniFilAriane != ''){
								$sub_content .= '-&nbsp;' . sprintf(__('Arborescence parente de l\'&eacute;l&eacute;ment : %s ', 'evarisk'), $miniFilAriane) . '<br/>';
							}

							/*	Read element children	*/
							$miniFilAriane = '         ';
							$descendants = Arborescence::getDescendants($affectation_information->table_element, $element, '1', 'id ASC', "");
							if(count($descendants) > 0){
								foreach($descendants as $descendant){
									$miniFilAriane .= '<span class="element_identifier" >' . ELEMENT_IDENTIFIER_GP . $descendant->id . '</span>&nbsp;-&nbsp;' . $descendant->nom . ' &raquo; ';
								}
							}
							$descendants = EvaGroupement::getUnitesDescendantesDuGroupement($element->id, '1', 'nom ASC', "");
							if(count($descendants) > 0){
								foreach($descendants as $descendant){
									$miniFilAriane .= '<span class="element_identifier" >' . ELEMENT_IDENTIFIER_UT . $descendant->id . '</span>&nbsp;-&nbsp;' . $descendant->nom . ' &raquo; ';
								}
							}
							$miniFilAriane = trim(substr($miniFilAriane, 0, -9));
							if($miniFilAriane != ''){
								$sub_content .= '-&nbsp;' . sprintf(__('Arborescence descendante de l\'&eacute;l&eacute;ment : %s ', 'evarisk'), $miniFilAriane) . '<br/>';
							}

							$gpt_list[ELEMENT_IDENTIFIER_GP . $affectation_information->id_element]['name'] = '<span class="alignleft" >' . ELEMENT_IDENTIFIER_GP . $affectation_information->id_element . '&nbsp;-&nbsp;' . $element->nom . '</span>';
							$gpt_list[ELEMENT_IDENTIFIER_GP . $affectation_information->id_element]['title'] = '<span class="alignleft digi_opener" >' . ELEMENT_IDENTIFIER_GP . $affectation_information->id_element . '&nbsp;-&nbsp;' . $element->nom . '</span><span class="user_element_view_container" ><a href="' . admin_url('admin.php?page=digirisk_risk_evaluation&elt=edit-node' . $affectation_information->id_element) . '" target="view_user_associated_element" class="ui-icon user_element_view" title="' . __('Voir l\'&eacute;l&eacute;ment', 'evarisk') . '" >&nbsp;</a></span>';
							$gpt_list[ELEMENT_IDENTIFIER_GP . $affectation_information->id_element]['detail'] = $sub_content;
						}break;
						case TABLE_UNITE_TRAVAIL:{
							$directParent = EvaGroupement::getGroupement($element->id_groupement);
							$ancetres = Arborescence::getAncetre(TABLE_GROUPEMENT, $directParent, "limiteGauche ASC", '1', "");
							$miniFilAriane = '         ';
							foreach($ancetres as $ancetre){
								if($ancetre->nom != "Groupement Racine"){
									$miniFilAriane .= '<span class="element_identifier" >' . ELEMENT_IDENTIFIER_GP . $ancetre->id . '</span>&nbsp;-&nbsp;' . $ancetre->nom . ' &raquo; ';
								}
							}
							if($directParent->nom != "Groupement Racine"){
								$miniFilAriane .= '<span class="element_identifier" >' . ELEMENT_IDENTIFIER_GP . $directParent->id . '</span>&nbsp;-&nbsp;' . $directParent->nom . ' &raquo; ';
							}
							$miniFilAriane = trim(substr($miniFilAriane, 0, -9));
							if($miniFilAriane != ''){
								$sub_content .= '-&nbsp;' . sprintf(__('Arborescence parente de l\'&eacute;l&eacute;ment : %s ', 'evarisk'), $miniFilAriane) . '<br/>';
							}

							$ut_list[ELEMENT_IDENTIFIER_UT . $affectation_information->id_element]['title'] = '<span class="alignleft digi_opener" >' . ELEMENT_IDENTIFIER_UT . $affectation_information->id_element . '&nbsp;-&nbsp;' . $element->nom . '</span><span class="user_element_view_container" ><a href="' . admin_url('admin.php?page=digirisk_risk_evaluation&elt=edit-leaf' . $affectation_information->id_element) . '" target="view_user_associated_element" class="ui-icon user_element_view" title="' . __('Voir l\'&eacute;l&eacute;ment', 'evarisk') . '" >&nbsp;</a></span>';
							$ut_list[ELEMENT_IDENTIFIER_UT . $affectation_information->id_element]['name'] = '<span class="alignleft digi_opener" >' . ELEMENT_IDENTIFIER_UT . $affectation_information->id_element . '&nbsp;-&nbsp;' . $element->nom . '</span>';
							$ut_list[ELEMENT_IDENTIFIER_UT . $affectation_information->id_element]['detail'] = $sub_content;
						}break;

						case TABLE_GROUPEMENT . '_evaluation':{
							/*	Read element ancestor	*/
							$ancetres = Arborescence::getAncetre($affectation_information->table_element, $element, "limiteGauche ASC", '1', "");
							$miniFilAriane = '         ';
							foreach($ancetres as $ancetre){
								if($ancetre->nom != "Groupement Racine"){
									$miniFilAriane .= '<span class="element_identifier" >' . ELEMENT_IDENTIFIER_GP . $ancetre->id . '</span>&nbsp;-&nbsp;' . $ancetre->nom . ' &raquo; ';
								}
							}
							$miniFilAriane = trim(substr($miniFilAriane, 0, -9));
							if($miniFilAriane != ''){
								$sub_content .= '-&nbsp;' . sprintf(__('Arborescence parente de l\'&eacute;l&eacute;ment : %s ', 'evarisk'), $miniFilAriane) . '<br/>';
							}

							/*	Read element children	*/
							$miniFilAriane = '         ';
							$descendants = Arborescence::getDescendants($affectation_information->table_element, $element, '1', 'id ASC', "");
							if(count($descendants) > 0){
								foreach($descendants as $descendant){
									$miniFilAriane .= '<span class="element_identifier" >' . ELEMENT_IDENTIFIER_GP . $descendant->id . '</span>&nbsp;-&nbsp;' . $descendant->nom . ' &raquo; ';
								}
							}
							$descendants = EvaGroupement::getUnitesDescendantesDuGroupement($element->id, '1', 'nom ASC', "");
							if(count($descendants) > 0){
								foreach($descendants as $descendant){
									$miniFilAriane .= '<span class="element_identifier" >' . ELEMENT_IDENTIFIER_UT . $descendant->id . '</span>&nbsp;-&nbsp;' . $descendant->nom . ' &raquo; ';
								}
							}
							$miniFilAriane = trim(substr($miniFilAriane, 0, -9));
							if($miniFilAriane != ''){
								$sub_content .= '-&nbsp;' . sprintf(__('Arborescence descendante de l\'&eacute;l&eacute;ment : %s ', 'evarisk'), $miniFilAriane) . '<br/>';
							}

							$gpt_eval_list[ELEMENT_IDENTIFIER_GP . $affectation_information->id_element]['title'] = '<span class="alignleft digi_opener" >' . ELEMENT_IDENTIFIER_GP . $affectation_information->id_element . '&nbsp;-&nbsp;' . $element->nom . '</span><span class="user_element_view_container" ><a href="' . admin_url('admin.php?page=digirisk_risk_evaluation&elt=edit-node' . $affectation_information->id_element) . '" target="view_user_associated_element" class="ui-icon user_element_view" title="' . __('Voir l\'&eacute;l&eacute;ment', 'evarisk') . '" >&nbsp;</a></span>';
							$gpt_eval_list[ELEMENT_IDENTIFIER_GP . $affectation_information->id_element]['detail'] = $sub_content;
						}break;
						case TABLE_UNITE_TRAVAIL . '_evaluation':{
							/*	Read element ancestor	*/
							$directParent = EvaGroupement::getGroupement($element->id_groupement);
							$ancetres = Arborescence::getAncetre(TABLE_GROUPEMENT, $directParent, "limiteGauche ASC", '1', "");
							$miniFilAriane = '         ';
							foreach($ancetres as $ancetre){
								if($ancetre->nom != "Groupement Racine"){
									$miniFilAriane .= '<span class="element_identifier" >' . ELEMENT_IDENTIFIER_GP . $ancetre->id . '</span>&nbsp;-&nbsp;' . $ancetre->nom . ' &raquo; ';
								}
							}
							if($directParent->nom != "Groupement Racine"){
								$miniFilAriane .= '<span class="element_identifier" >' . ELEMENT_IDENTIFIER_GP . $directParent->id . '</span>&nbsp;-&nbsp;' . $directParent->nom . ' &raquo; ';
							}
							$miniFilAriane = trim(substr($miniFilAriane, 0, -9));
							if($miniFilAriane != ''){
								$sub_content .= '-&nbsp;' . sprintf(__('Arborescence parente de l\'&eacute;l&eacute;ment : %s ', 'evarisk'), $miniFilAriane) . '<br/>';
							}

							$ut_eval_list[ELEMENT_IDENTIFIER_UT . $affectation_information->id_element]['title'] = '<span class="alignleft digi_opener" >' . ELEMENT_IDENTIFIER_UT . $affectation_information->id_element . '&nbsp;-&nbsp;' . $element->nom . '</span><span class="user_element_view_container" ><a href="' . admin_url('admin.php?page=digirisk_risk_evaluation&elt=edit-leaf' . $affectation_information->id_element) . '" target="view_user_associated_element" class="ui-icon user_element_view" title="' . __('Voir l\'&eacute;l&eacute;ment', 'evarisk') . '" >&nbsp;</a></span>';
							$ut_eval_list[ELEMENT_IDENTIFIER_UT . $affectation_information->id_element]['detail'] = $sub_content;
						}break;

						case TABLE_TACHE:{
							$ancetres = Arborescence::getAncetre(TABLE_TACHE, $element);
							$miniFilAriane = '         ';
							foreach($ancetres as $ancetre){
								if($ancetre->nom != "Tache Racine"){
									$miniFilAriane .= '<span class="element_identifier" >' . ELEMENT_IDENTIFIER_T . $ancetre->id . '</span>&nbsp;-&nbsp;' . $ancetre->nom . ' &raquo; ';
								}
							}
							$miniFilAriane = trim(substr($miniFilAriane, 0, -9));
							if($miniFilAriane != ''){
								$sub_content .= '-&nbsp;' . sprintf(__('Arborescence parente de l\'&eacute;l&eacute;ment : %s ', 'evarisk'), $miniFilAriane) . '<br/>';
							}

							$miniFilAriane = '         ';
							$descendants = Arborescence::getDescendants($affectation_information->table_element, $element);
							foreach($descendants as $descendant){
								$miniFilAriane .= '<span class="element_identifier" >' . ELEMENT_IDENTIFIER_T . $descendant->id . '</span>&nbsp;-&nbsp;' . $descendant->nom . '    /    ';
							}
							if(count($descendants) <= 0){
								$descendants = EvaTask::getChildren($element->id);
								foreach($descendants as $descendant){
									$miniFilAriane .= '<span class="element_identifier" >' . ELEMENT_IDENTIFIER_ST . $descendant->id . '</span>&nbsp;-&nbsp;' . $descendant->nom . ' &raquo; ';
								}
							}
							$miniFilAriane = trim(substr($miniFilAriane, 0, -9));
							if($miniFilAriane != ''){
								$sub_content .= '-&nbsp;' . sprintf(__('Arborescence directe descendante de l\'&eacute;l&eacute;ment : %s ', 'evarisk'), $miniFilAriane) . '<br/>';
							}

							$t_list[ELEMENT_IDENTIFIER_T . $affectation_information->id_element]['title'] = '<span class="alignleft digi_opener" >' . ELEMENT_IDENTIFIER_T . $affectation_information->id_element . '&nbsp;-&nbsp;' . $element->nom . '</span><span class="user_element_view_container" ><a href="' . admin_url('admin.php?page=digirisk_risk_evaluation&elt=edit-node' . $affectation_information->id_element) . '" target="view_user_associated_element" class="ui-icon user_element_view" title="' . __('Voir l\'&eacute;l&eacute;ment', 'evarisk') . '" >&nbsp;</a></span>';
							$t_list[ELEMENT_IDENTIFIER_T . $affectation_information->id_element]['detail'] = $sub_content;
						}break;
						case TABLE_ACTIVITE:{
							$directParent = new EvaTask();
							$directParent->setId($element->id_tache);
							$directParent->load();
							$directParent->limiteGauche = $directParent->leftLimit;
							$directParent->limiteDroite = $directParent->rightLimit;
							$ancetres = Arborescence::getAncetre(TABLE_TACHE, $directParent);
							$miniFilAriane = '         ';
							foreach($ancetres as $ancetre){
								if($ancetre->nom != "Tache Racine"){
									$miniFilAriane .= '<span class="element_identifier" >' . ELEMENT_IDENTIFIER_T . $ancetre->id . '</span>&nbsp;-&nbsp;' . $ancetre->nom . ' &raquo; ';
								}
							}
							if($directParent->nom != "Tache Racine"){
								$miniFilAriane .= '<span class="element_identifier" >' . ELEMENT_IDENTIFIER_T . $directParent->id . '</span>&nbsp;-&nbsp;' . $directParent->name . ' &raquo; ';
							}
							$miniFilAriane = trim(substr($miniFilAriane, 0, -9));
							if($miniFilAriane != ''){
								$sub_content .= '-&nbsp;' . sprintf(__('Arborescence parente de l\'&eacute;l&eacute;ment : %s ', 'evarisk'), $miniFilAriane) . '<br/>';
							}

							$st_list[ELEMENT_IDENTIFIER_ST . $affectation_information->id_element]['title'] = '<span class="alignleft digi_opener" >' . ELEMENT_IDENTIFIER_ST . $affectation_information->id_element . '&nbsp;-&nbsp;' . $element->nom . '</span><span class="user_element_view_container" ><a href="' . admin_url('admin.php?page=digirisk_correctiv_actions&elt=edit-leaf' . $affectation_information->id_element) . '" target="view_user_associated_element" class="ui-icon user_element_view" title="' . __('Voir l\'&eacute;l&eacute;ment', 'evarisk') . '" >&nbsp;</a></span>';
							$st_list[ELEMENT_IDENTIFIER_ST . $affectation_information->id_element]['detail'] = $sub_content;
						}break;
					}
				}
			}

			$user_tree_affecation .= '<div class="clear affected_element_to_user_final_container" ><img src="' . DEFAULT_GROUP_PICTO . '" alt="' . __('Groupements', 'evarisk') . '" class="middleAlign user_affecation_picto" />' .  __('Liste des groupements auxquels l\'utilisateur est affect&eacute; directement', 'evarisk') . '<div class="user_affecation_element_container" >';
			if(count($gpt_list) > 0){
				foreach($gpt_list as $elt_id => $elt_detail){
					$user_tree_affecation .= '
						<div class="affected_element_line clear" >
							<div class="line_title" id="gpt_title_' . $elt_id . '"  ><span class="user_element_detail_opener ui-icon alignleft digi_opener" >&nbsp;</span>' . $elt_detail['title'] . '</div>
							<div class="clear detail_line digirisk_hide" id="gpt_detail_' . $elt_id . '" >' . $elt_detail['detail'] . '</div>
							<div class="clear" ></div>
						</div>';
				}
			}
			else{
				$user_tree_affecation .= __('Cet utilisateur n\'est affect&eacute; a aucun groupement pour le moment', 'evarisk');
			}
			$user_tree_affecation .= '</div></div>
			<div class="clear affected_element_to_user_final_container" ><img src="' . DEFAULT_WORKING_UNIT_PICTO . '" alt="' . __('Unit&eacute; de travail', 'evarisk') . '" class="middleAlign user_affecation_picto" />' .  __('Liste des unit&eacute;s de travail auxquelles l\'utilisateur est affect&eacute; directement', 'evarisk') . '<div class="user_affecation_element_container" >';
			if(count($ut_list) > 0){
				foreach($ut_list as $elt_id => $elt_detail){
					$user_tree_affecation .= '
						<div class="affected_element_line clear" >
							<div class="line_title" id="ut_title_' . $elt_id . '"  ><span class="user_element_detail_opener ui-icon alignleft digi_opener" >&nbsp;</span>' . $elt_detail['title'] . '</div>
							<div class="clear detail_line digirisk_hide" id="ut_detail_' . $elt_id . '" >' . $elt_detail['detail'] . '</div>
							<div class="clear" ></div>
						</div>';
				}
			}
			else{
				$user_tree_affecation .= __('Cet utilisateur n\'est affect&eacute; a aucune unit&eacute; de travail pour le moment', 'evarisk');
			}
			$user_tree_affecation .= '</div></div>';

			$user_tree_eval_affecation .= '<div class="clear affected_element_to_user_final_container" ><img src="' . DEFAULT_GROUP_PICTO . '" alt="' . __('Groupements', 'evarisk') . '" class="middleAlign user_affecation_picto" />' .  __('Liste des groupements dans lesquels l\'utilisateur a particip&eacute; &agrave; l\'&eacute;valuation', 'evarisk') . '<div class="user_affecation_element_container" >';
			if(count($gpt_eval_list) > 0){
				foreach($gpt_eval_list as $elt_id => $elt_detail){
					$user_tree_eval_affecation .= '
						<div class="affected_element_line clear" >
							<div class="line_title" id="gpt_eval_title_' . $elt_id . '"  ><span class="user_element_detail_opener ui-icon alignleft digi_opener" >&nbsp;</span>' . $elt_detail['title'] . '</div>
							<div class="clear detail_line digirisk_hide" id="gpt_eval_detail_' . $elt_id . '" >' . $elt_detail['detail'] . '</div>
							<div class="clear" ></div>
						</div>';
				}
			}
			else{
				$user_tree_eval_affecation .= __('Cet utilisateur n\'a particip&eacute; a aucune &eacute;valuation sur des groupements pour le moment', 'evarisk');
			}
			$user_tree_eval_affecation .= '</div></div>
			<div class="clear affected_element_to_user_final_container" ><img src="' . DEFAULT_WORKING_UNIT_PICTO . '" alt="' . __('Unit&eacute; de travail', 'evarisk') . '" class="middleAlign user_affecation_picto" />' .  __('Liste des unit&eacute;s de travail dans lesquelles l\'utilisateur a particip&eacute; &agrave; l\'&eacute;valuation', 'evarisk') . '<div class="user_affecation_element_container" >';
			if(count($ut_eval_list) > 0){
				foreach($ut_eval_list as $elt_id => $elt_detail){
					$user_tree_eval_affecation .= '
						<div class="affected_element_line clear" >
							<div class="line_title" id="ut_eval_title_' . $elt_id . '"  ><span class="user_element_detail_opener ui-icon alignleft digi_opener" >&nbsp;</span>' . $elt_detail['title'] . '</div>
							<div class="clear detail_line digirisk_hide" id="ut_eval_detail_' . $elt_id . '" >' . $elt_detail['detail'] . '</div>
							<div class="clear" ></div>
						</div>';
				}
			}
			else{
				$user_tree_eval_affecation .= __('Cet utilisateur n\'a particip&eacute; a aucune &eacute;valuation sur des unit&eacute;s de travail pour le moment', 'evarisk');
			}
			$user_tree_eval_affecation .= '</div></div>';

			$user_ac_affecation .= '
			<div class="clear affected_element_to_user_final_container" ><img src="' . PICTO_TACHE . '" alt="' . __('Sous t&acirc;che des actions correctives', 'evarisk') . '" class="middleAlign user_affecation_picto" />' .  __('Liste des t&acirc;ches auxquelles l\'utilisateur est affect&eacute;', 'evarisk') . '<div class="user_affecation_element_container" >';
			if(count($t_list) > 0){
				foreach($t_list as $elt_id => $elt_detail){
					$user_ac_affecation .= '
						<div class="affected_element_line clear" >
							<div class="line_title" id="t_title_' . $elt_id . '"  ><span class="user_element_detail_opener ui-icon alignleft digi_opener" >&nbsp;</span>' . $elt_detail['title'] . '</div>
							<div class="clear detail_line digirisk_hide" id="t_detail_' . $elt_id . '" >' . $elt_detail['detail'] . '</div>
							<div class="clear" ></div>
						</div>';
				}
			}
			else{
				$user_ac_affecation .= __('Cet utilisateur n\'est affect&eacute; a aucune t&acirc;che pour le moment', 'evarisk');
			}
			$user_ac_affecation .= '</div></div>
			<div class="clear affected_element_to_user_final_container" ><img src="' . PICTO_LTL_ACTION . '" alt="' . __('Sous t&acirc;che des actions correctives', 'evarisk') . '" class="middleAlign user_affecation_picto" />' .  __('Liste des sous-t&acirc;ches auxquelles l\'utilisateur est affect&eacute;', 'evarisk') . '<div class="user_affecation_element_container" >';
			if(count($st_list) > 0){
				foreach($st_list as $elt_id => $elt_detail){
					$user_ac_affecation .= '
						<div class="affected_element_line clear" >
							<div class="line_title" id="st_title_' . $elt_id . '"  ><span class="user_element_detail_opener ui-icon alignleft digi_opener" >&nbsp;</span>' . $elt_detail['title'] . '</div>
							<div class="clear detail_line digirisk_hide" id="st_detail_' . $elt_id . '" >' . $elt_detail['detail'] . '</div>
							<div class="clear" ></div>
						</div>';
				}
			}
			else{
				$user_ac_affecation .= __('Cet utilisateur n\'est affect&eacute; a aucune sous t&acirc;che pour le moment', 'evarisk');
			}
			$user_ac_affecation .= '</div></div>';
		}

		$digiPenibility = __('Cet utilisateur n\'est affect&eacute; a aucun &eacute;l&eacute;ment pour le moment', 'evarisk');
		$user_affectations = evaUserLinkElement::get_user_affected_element($user_to_edit, '', "'valid', 'moderated', 'deleted'");
		if ( !empty($user_affectations) ) {
			$element_list = array();
			foreach ( $user_affectations as $user_affectation ) {
				$element_list[$user_affectation->table_element][$user_affectation->id_element] = $user_affectation->id_element;
			}
			$digiPenibility = '';

			if ( !empty($element_list) ) {
				$digiPenibility .= '
							<div class="user_profil_generated_doc generatedDocContainer" id="generatedFEPContainer_for_all_-digi-_' . $user_to_edit . '" >
								<div class="clear bold" >
									' . __('G&eacute;n&eacute;rer les fiches de p&eacute;nibilit&eacute; pour toutes les affectations de l\'utilisateur', 'evarisk') . '
								</div>
								<br class="clear" />
								<div>
									<input type="checkbox" id="modelDefaut_' . DIGI_DBT_USER . '_-digi-_all" checked="checked" name="modelUse" class="choose_model" value="modeleDefaut" />
									<label for="modelDefaut_' . DIGI_DBT_USER . '_-digi-_all" style="vertical-align:middle;" >' . __('Utiliser le mod&egrave;le par d&eacute;faut', 'evarisk') . '</label>
								</div>
								<div id="modelListForGeneration_' . DIGI_DBT_USER . '_-digi-_all" class="digirisk_hide alignleft" ></div>
								<div id="documentUniqueResultContainer_' . DIGI_DBT_USER . '_-digi-_all" class="alignleft" ></div>
								<br class="clear" />
								<button class="digi_generate_FEP" id="digi_generate_FEP_' . DIGI_DBT_USER . '_-digi-_all_-digi-_' . $user_to_edit . '" >' . __('G&eacute;n&eacute;rer', 'evarisk') . '</button>
								<div class="clear FEPContainer" id="digi_generate_FEP_' . DIGI_DBT_USER . '_-digi-_all_-digi-_' . $user_to_edit . '_container" >' . eva_GroupSheet::getGroupSheetCollectionHistory('USER', $user_to_edit, 'fiche_exposition_penibilite', ELEMENT_IDENTIFIER_GFEP) . '</div>
							</div>';

				foreach ( $element_list as $table_element => $list_of_element ) {
					foreach ( $list_of_element as $id_element ) {
						$query = $wpdb->prepare("SELECT * FROM " . $table_element . " WHERE Status = 'Valid' AND id = %d", $id_element);
						$element = $wpdb->get_row($query);
						switch ( $table_element ) {
							case TABLE_GROUPEMENT:
								$digiPenibility .= '
									<div class="user_profil_generated_doc generatedDocContainer" id="generatedFEPContainer_' . TABLE_GROUPEMENT . '_' . str_replace(ELEMENT_IDENTIFIER_GP, '', $id_element) . '" >
										<div class="clear bold" >
											<img src="' . DEFAULT_GROUP_PICTO . '" alt="' . __('Groupements', 'evarisk') . '" class="alignleft middleAlign user_affectation_picto" /><span class="alignleft digi_opener" >' . ELEMENT_IDENTIFIER_GP . $id_element . '&nbsp;-&nbsp;' . $element->nom . '</span><span class="user_element_view_container" ><a href="' . admin_url('admin.php?page=digirisk_risk_evaluation&elt=edit-node' . $id_element) . '" target="view_user_associated_element" class="ui-icon user_element_view" title="' . __('Voir l\'&eacute;l&eacute;ment', 'evarisk') . '" >&nbsp;</a></span>
										</div>
										<br class="clear" />
										<div>
											<input type="checkbox" id="modelDefaut_' . TABLE_GROUPEMENT . '_-digi-_' . $id_element . '" checked="checked" name="modelUse" class="choose_model" value="modeleDefaut" />
											<label for="modelDefaut_' . TABLE_GROUPEMENT . '_-digi-_' . $id_element . '" style="vertical-align:middle;" >' . __('Utiliser le mod&egrave;le par d&eacute;faut', 'evarisk') . '</label>
										</div>
										<div id="modelListForGeneration_' . TABLE_GROUPEMENT . '_-digi-_' . $id_element . '" class="digirisk_hide alignleft" ></div>
										<div id="documentUniqueResultContainer_' . TABLE_GROUPEMENT . '_-digi-_' . $id_element . '" class="alignleft" ></div>
										<br class="clear" />
										<button class="digi_generate_FEP" id="digi_generate_FEP_' . TABLE_GROUPEMENT . '_-digi-_' . str_replace(ELEMENT_IDENTIFIER_GP, '', $id_element) . '_-digi-_' . $user_to_edit . '" >' . __('G&eacute;n&eacute;rer', 'evarisk') . '</button>
										<div class="clear FEPContainer" id="digi_generate_FEP_' . TABLE_GROUPEMENT . '_-digi-_' . str_replace(ELEMENT_IDENTIFIER_GP, '', $id_element) . '_-digi-_' . $user_to_edit . '_container" >' . eva_gestionDoc::getGeneratedDocument(TABLE_GROUPEMENT, str_replace(ELEMENT_IDENTIFIER_GP, '', $id_element), 'list', '', 'fiche_exposition_penibilite', $user_to_edit) . '</div>
									</div>';
								break;
							case TABLE_UNITE_TRAVAIL:
								$digiPenibility .= '
									<div class="user_profil_generated_doc generatedDocContainer" id="generatedFEPContainer' . TABLE_UNITE_TRAVAIL . '_' . str_replace(ELEMENT_IDENTIFIER_UT, '', $id_element) . '" >
										<div class="clear bold" >
											<img src="' . DEFAULT_WORKING_UNIT_PICTO . '" alt="' . __('Unit&eacute; de travail', 'evarisk') . '" class="alignleft middleAlign user_affectation_picto" /><span class="alignleft digi_opener" >' . ELEMENT_IDENTIFIER_UT . $id_element . '&nbsp;-&nbsp;' . $element->nom . '</span><span class="user_element_view_container" ><a href="' . admin_url('admin.php?page=digirisk_risk_evaluation&elt=edit-leaf' . $id_element) . '" target="view_user_associated_element" class="ui-icon user_element_view" title="' . __('Voir l\'&eacute;l&eacute;ment', 'evarisk') . '" >&nbsp;</a></span>
										</div>
										<br class="clear" />
										<div>
											<input type="checkbox" id="modelDefaut_' . TABLE_UNITE_TRAVAIL . '_-digi-_' . $id_element . '" checked="checked" name="modelUse" class="choose_model" value="modeleDefaut" />
											<label for="modelDefaut_' . TABLE_UNITE_TRAVAIL . '_-digi-_' . $id_element . '" style="vertical-align:middle;" >' . __('Utiliser le mod&egrave;le par d&eacute;faut', 'evarisk') . '</label>
										</div>
										<div id="modelListForGeneration_' . TABLE_UNITE_TRAVAIL . '_-digi-_' . $id_element . '" class="digirisk_hide alignleft" ></div>
										<div id="documentUniqueResultContainer_' . TABLE_UNITE_TRAVAIL . '_-digi-_' . $id_element . '" class="alignleft" ></div>
										<br class="clear" />
										<button class="digi_generate_FEP" id="digi_generate_FEP_' . TABLE_UNITE_TRAVAIL . '_-digi-_' . str_replace(ELEMENT_IDENTIFIER_UT, '', $id_element) . '_-digi-_' . $user_to_edit . '" >' . __('G&eacute;n&eacute;rer', 'evarisk') . '</button>
										<div class="clear FEPContainer" id="digi_generate_FEP_' . TABLE_UNITE_TRAVAIL . '_-digi-_' . str_replace(ELEMENT_IDENTIFIER_UT, '', $id_element) . '_-digi-_' . $user_to_edit . '_container" >' . eva_gestionDoc::getGeneratedDocument(TABLE_UNITE_TRAVAIL, str_replace(ELEMENT_IDENTIFIER_UT, '', $id_element), 'list', '', 'fiche_exposition_penibilite', $user_to_edit) . '</div>
									</div>';
								break;
						}
					}
				}
			}
		}

	/**	Start output building	*/
// 		add_thickbox();
		$user_profil_content .= '
<div class="clear alignright" >
	<a href="#" id="digi-mass-change-user-informations-opener" >' . __('Changement en masse sur les utilisateurs', 'evarisk') . '</a>
	<div id="digi-mass-change-user-informations" title="' . __('Changement en masse sur les utilisateurs', 'evarisk')  . '" ></div>
</div>';

		/**	Add a field allowing user to change user for edition	*/
		$user_profil_content .= '
<div class="clear user_selector" >
	<span class="searchUserInput ui-icon" >&nbsp;</span>
	<input class="searchUserToAffect" type="text" name="digi_user_list" id="digi_user_list" placeholder="' . __('Rechercher un utilisateur dans la liste pour acc&eacute;der &agrave; sa fiche', 'evarisk') . '" />
	<div id="complete_user_list" class="digirisk_hide completeUserList clear" >' . evaUser::afficheListeUtilisateurTable_SimpleSelection(DIGI_DBT_USER, $user_to_edit) . '</div>
</div>';

		/**	Add the different component of output	*/
		$user_profil_content .= '
<div id="user_profil_edition_tabs" class="eva_tabs clear" >
	<ul >
		<li><a href="#digirisk_user_tree_affectation" title="digirisk_user_tree_affectation" id="digirisk_user_tree_affectation_tab" >' . __('Affectation', 'evarisk') . '</a></li>
		<li><a href="#digirisk_user_risk_evaluation_affectation" title="digirisk_user_risk_evaluation_affectation" id="digirisk_user_risk_evaluation_affectation_tab" >' . __('&Eacute;valuation', 'evarisk') . '</a></li>
		<li><a href="#digirisk_user_ca_affectation" title="digirisk_user_ca_affectation" id="digirisk_user_ca_affectation_tab" >' . __('Actions correctives', 'evarisk') . '</a></li>
		<li><a href="#digirisk_user_rights" title="digirisk_user_rights" id="digirisk_user_rights_tab" >' . __('Droits', 'evarisk') . '</a></li>
		<li><a href="#digirisk_user_penibility" title="digirisk_user_penibility" id="digirisk_user_penibility_tab" >' . __('P&eacute;nibilit&eacute;', 'evarisk') . '</a></li>
	</ul>
	<div id="digirisk_user_tree_affectation" >' . $user_tree_affecation . '</div>
	<div id="digirisk_user_risk_evaluation_affectation" >' . $user_tree_eval_affecation . '</div>
	<div id="digirisk_user_ca_affectation" >' . $user_ac_affecation . '</div>
	<div id="digirisk_user_rights" >' . $digiPermissionForm . '</div>
	<div id="digirisk_user_penibility" >' . $digiPenibility . '</div>
</div>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery(".choose_model").click(function(){
			var current_element = jQuery(this).attr("id").replace("modelDefaut_", "");
			var current_element_spec = current_element.split("_-digi-_");
			setTimeout( function() {
				if ( !jQuery("#modelDefaut_" + current_element).is(":checked") ) {
					jQuery("#documentUniqueResultContainer_" + current_element).html(\'<img src="' . EVA_IMG_DIVERS_PLUGIN_URL . 'loading.gif" alt="loading" />\');
					jQuery("#documentUniqueResultContainer_" + current_element).load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_DUER . '", "act":"loadNewModelForm", "tableElement": current_element_spec[0] + "_FEP", "idElement": current_element_spec[1]});
					jQuery("#modelListForGeneration_" + current_element).load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_GED_DOCUMENTS . '", "act":"load_model_combobox", "tableElement": current_element_spec[0] + "_FEP", "idElement": current_element_spec[1], "category":"fiche_exposition_penibilite", "selection":""});
					jQuery("#modelListForGeneration_" + current_element).show();
				}
				else {
					jQuery("#documentUniqueResultContainer_" + current_element).html("");
					jQuery("#modelListForGeneration_" + current_element).html("");
					jQuery("#modelListForGeneration_" + current_element).hide();
				}
			},600);
		});

		/*	Create tabs for different profil element	*/
		jQuery("#user_profil_edition_tabs").tabs();

		/*	Add possiblity to change user easyly with a simple input	*/
		jQuery("#digi_user_list").click(function(){
			jQuery("#complete_user_list").show();
		});

		/*	Autocomplete search	*/
		jQuery("#digi_user_list").autocomplete({
			source: "' . EVA_INC_PLUGIN_URL . 'liveSearch/searchUsers.php?all_user=yes",
			select: function( event, ui ){
				jQuery("#complete_user_list").hide();
				jQuery("#user_profil_edition_tabs").html(jQuery("#loadingImg").html());
				window.top.location.href = "' . admin_url('users.php?page=digirisk_users_profil&user_to_edit=') . '" + ui.item.value;

				setTimeout(function(){
					jQuery("#digi_user_list").val("");
					jQuery("#digi_user_list").blur();
				}, 2);
			}
		});
		jQuery("#digi_user_list").blur(function(){
			if(jQuery(this).val() == ""){
				jQuery(this).val(digi_html_accent_for_js("' . __('Rechercher un utilisateur dans la liste pour acc&eacute;der &agrave; sa fiche', 'evarisk') . '"));
			}
		});

		/*	Add support for detail open	*/
		jQuery(".line_title span.digi_opener").click(function(){
			var detail_to_open_id = jQuery(this).parent().attr("id").replace("title", "detail");
			jQuery("#" + detail_to_open_id).toggleClass("digirisk_hide");
			if(jQuery("#" + detail_to_open_id).is(":visible")){
				jQuery("#" + detail_to_open_id).parent().children("div").children("span:first").removeClass("user_element_detail_opener");
				jQuery("#" + detail_to_open_id).parent().children("div").children("span:first").addClass("user_element_detail_closer");
			}
			else{
				jQuery("#" + detail_to_open_id).parent().children("div").children("span:first").addClass("user_element_detail_opener");
				jQuery("#" + detail_to_open_id).parent().children("div").children("span:first").removeClass("user_element_detail_closer");
			}
		});

		jQuery(".digi_generate_FEP").click(function(){
			var current_element_specs = jQuery(this).attr("id").replace("digi_generate_FEP_", "").split("_-digi-_");
			digirisk("#" + jQuery(this).attr("id") + "_container").html(jQuery("#loadingImg").html());
			digirisk("#" + jQuery(this).attr("id") + "_container").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
				"post":"true",
				"table":"' . TABLE_GED_DOCUMENTS . '",
				"act":"save_fiche_penibilite_specific_user",
				"element_infos": jQuery(this).attr("id").replace("digi_generate_FEP_", ""),
				"id_model": jQuery("#modelListForGeneration_" + current_element_specs[0] + "_-digi-_" + current_element_specs[1] + " #modelToUse" + current_element_specs[0] + "_FEP").val(),
			});
		});



		/*	Add the dialog box in order to make mass changes on users	*/
		jQuery( "#digi-mass-change-user-informations-opener" ).click(function( e ){
			e.preventDefault();
			var data = {
				action: "digi-mass-change-user-informations",
			};
			jQuery( "#digi-mass-change-user-informations" ).load( ajaxurl, data ).dialog("open");
		});
		jQuery("#digi-mass-change-user-informations").dialog({
			"autoOpen":false,
			"height":460,
			"width":800,
			"modal":true,
			"buttons":  {
				"' . __('Enregistrer les changements', 'evarisk') . '": function(){
					jQuery( "#digi-mass-user-updater-form" ).submit();
				},
			},
			"close":function(){
				jQuery("#digi-mass-change-user-informations").html("");
			}
		});
	});
</script>';

		$page_title = sprintf(__('Profil utilisateur : %s', 'evarisk'), ELEMENT_IDENTIFIER_U . $user_to_edit . '&nbsp;-&nbsp;' . $user_infos['user_lastname'] . '&nbsp;' . $user_infos['user_firstname']);
		if($user_to_edit == $current_user->ID){
			$page_title = sprintf(__('Votre profil utilisateur : %s', 'evarisk'), ELEMENT_IDENTIFIER_U . $user_to_edit . '&nbsp;-&nbsp;' . $user_infos['user_lastname'] . '&nbsp;' . $user_infos['user_firstname']);
		}

		$user_hiring_date = get_user_meta( $user_to_edit, 'digi_hiring_date', true);
		$user_unhiring_date = get_user_meta( $user_to_edit, 'digi_unhiring_date', true);
		$page_title .= ( !empty( $user_hiring_date ) ? '<br/>' . __( 'Entr&eacute;e', 'evarisk' ) . ' : ' .  mysql2date( 'd m Y', $user_hiring_date, true ) : '' );
		$page_title .= ( !empty( $user_unhiring_date ) ? '<br/>' .__( 'Sortie', 'evarisk' ) . ' : ' .  mysql2date( 'd m Y', $user_unhiring_date, true ) : '' );

		$user_profil_page = digirisk_display::start_page($page_title, '', '', '', '', false, '', false, true, 'id="icon-users"') . $user_profil_content .	digirisk_display::end_page();

		echo $user_profil_page;
	}


	/**
	*	Create an instance for a user. Initialize the entity id related to the user
	*/
	function evaUser($AttributeGroupName)
	{
		global $eav_attribute;

		$eav_attribute->setCurrentEntityTypeId($eav_attribute->getEntityInformation('eva_users'));

		$this->_currentAttributeGroup = $AttributeGroupName;
	}
	/**
	*	Get the query that will be launch to update different attribute
	*
	*	@return mixed $this->_attributesValueQuery The query we will launch to update information
	*/
	function setUserToUpdate($userId)
	{
		$this->_userToUpdate = $userId;
	}
	/**
	*	Get the list of attribute available for a given group
	*
	*	@return mixed $attributeForm The different element to output
	*/
	function evaUserAttributeForm()
	{
		global $eav_attribute;

		$attributeForm = $eav_attribute->attributeFormOutput($this->_currentAttributeGroup, $this->_userToUpdate);

		return $this->evaUserAttributeFormTemplate($attributeForm);
	}
	/**
	*	Output a container with the different element for user
	*
	*	@param mixed $content The different element to output in the template
	*/
	function evaUserAttributeFormTemplate($content)
	{
		if(trim($content) != '')
		{
?>
<h3><?php _e('Informations compl&eacute;mentaires evarisk'); ?></h3>
<div class="form-table" id="evaUserInformation" >
	<?php echo $content; ?>
</div>
<?php
		}
	}
	/**
	*	Update the different information for an user profile. First we check that there is an user update form that were send, then if it's the case we do the job
	*/
	function evaUserUpdateProfile()
	{
		/* Check if we send the user update form, if it is not the case we return to the normal output */
		if(!isset($_POST['user_id'])) return;

		global $eav_attribute;

		if(isset($_REQUEST['userAttributes']) && is_array($_REQUEST['userAttributes']) && (count($_REQUEST['userAttributes']) > 0))
		{
			foreach ($_REQUEST['userAttributes'] as $attributeType => $attributeContent) :
					$eav_attribute->createAttributesValueHeaderQuery($attributeType);
				foreach ($attributeContent as $attributeCode => $attributeValue) :
					$eav_attribute->createAttributesValueQuery($attributeCode, $_POST['user_id'], $attributeValue);
				endforeach;
					$eav_attribute->attributesValueQueryTrimmer();
					$eav_attribute->setAttributesValue();
					$eav_attribute->attributesValueQueryTrimmer(true);
			endforeach;
		}
	}

}