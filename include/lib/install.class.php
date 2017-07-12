<?php
/**
* Plugin installer
*
* Define the different methods to install digirisk plugin
* @author Evarisk <dev@evarisk.com>
* @version 5.1.3.5
* @package Digirisk
* @subpackage librairies
*/

/**
 * Define the different methods to install digirisk plugin
 * @package Digirisk
 * @subpackage librairies
 */
class digirisk_install	{

	/**
	 *	Define the main installation form
	 */
	public static function installation_form(){
		global $evaluation_method_operator, $evaluation_main_vars;
		$installation_form = '';

		/*	Create an ouput with the defined method operator	*/
		$basic_operator_list = '     ';
		foreach($evaluation_method_operator as $operator){
			$basic_operator_list .= $operator['symbole'] . '  ';
		}
		$basic_operator_list = (trim(substr($basic_operator_list, 2, -2)) !=  '')  ? '  (' . trim(substr($basic_operator_list, 2, -2)) . ')' : '';
		/*	Create an ouput with the defined method vars	*/
		$basic_vars_list = '      ';
		foreach($evaluation_main_vars as $operator){
			$basic_vars_list .= $operator['nom'] . ' / ';
		}
		$basic_vars_list = (trim(substr($basic_vars_list, 3, -3)) !=  '')  ? '  (' . trim(substr($basic_vars_list, 2, -2)) . ')' : '';
		/*	Create an output with the defined danger categories	*/
		$basic_danger_cat_list = '';
		$inrs_danger_categories = unserialize(DIGI_INRS_DANGER_LIST);
		foreach($inrs_danger_categories as $version_number => $category){
			if ( is_file(EVA_HOME_DIR . $category['picture']) ) {
				$picture = EVA_HOME_URL . $category['picture'];
			}
			else {
				$picture = EVA_CATEGORIE_DANGER_ICON;
			}
			$basic_danger_cat_list .= '<div class="alignleft inrs_picto_container_install" ><img src="' . $picture . '" alt="' . $category['nom'] . '" /><br/>' . $category['nom'] . '</div>';
		}

		$installation_form .= digirisk_display::start_page(__('Installation du logiciel d\'aide &agrave; l\'&eacute;valuation des risques', 'evarisk'), EVA_OPTIONS_ICON, __('Installation du logiciel d\'aide &agrave; l\'&eacute;valuation des risques', 'evarisk'), __('Installation du logiciel D\'aide &agrave; l\'&eacute;valuation des risques', 'evarisk'), TABLE_OPTION, false, '', false, false) . '
<form method="post" id="digi_install_form" action="' . EVA_INC_PLUGIN_URL . 'ajax.php" >
	<input type="hidden" name="post" value="true" />
	<input type="hidden" name="nom" value="digirisk_install" />

	<fieldset>
		<legend>' . __('M&eacute;thode d\'&eacute;valuation', 'evarisk')  . '</legend>
		<input type="checkbox" name="insert_basic_operator" id="insert_basic_operator" value="yes" checked="checked" disabled="disabled" />&nbsp;<label for="insert_basic_operator" >' . __('Ins&eacute;rer les op&eacute;rateurs de base', 'evarisk') . '</label>' . $basic_operator_list . '<br/>
		<input type="checkbox" name="insert_basic_vars" id="insert_basic_vars" value="yes" checked="checked" disabled="disabled" />&nbsp;<label for="insert_basic_vars" >' . __('Ins&eacute;rer les variables de base', 'evarisk') . '</label>' . $basic_vars_list . '<br/><br/><br/>
		<input type="checkbox" name="insert_evarisk_main_method" id="insert_evarisk_main_method" value="yes" checked="checked" />&nbsp;<label for="insert_evarisk_main_method" >' . __('Ins&eacute;rer la m&eacute;thode d\'&eacute;valuation d\'Evarisk', 'evarisk') . '</label><br/>
		<img src="' . EVA_HOME_URL . 'medias/uploads/wp_eva__methode/1/tabcoeff.gif" alt="' . __('Explication de la m&eacute;thode Evarisk', 'evarisk') . '" title="' . __('Explication de la m&eacute;thode Evarisk', 'evarisk') . '" />
	</fieldset>

	<fieldset>
		<legend>' . __('Cat&eacute;gories de danger', 'evarisk')  . '</legend>
		<input type="checkbox" name="insert_inrs_danger_cat" id="insert_inrs_danger_cat" value="yes" checked="checked" />&nbsp;<label for="insert_inrs_danger_cat" >' . __('Ins&eacute;rer les cat&eacute;gories de danger d&eacute;finies par l\'INRS', 'evarisk') . '</label> (' . __('Un danger sera ins&eacute;r&eacute; dans chaque cat&eacute;gorie. Sauf pour la cat&eacute;gorie "Autres" o&ugrave; plusieurs dangers seront ins&eacute;r&eacute;s', 'evarisk') . ')<br/>' . $basic_danger_cat_list . '
	</fieldset>

	<fieldset>
		<legend>' . __('Th&egrave;me pour le portail', 'evarisk')  . '</legend>
		<input type="checkbox" name="activate_evarisk_theme" id="activate_evarisk_theme" value="yes" checked="checked" />&nbsp;<label for="activate_evarisk_theme" >' . __('Activer le th&egrave;me Evarisk. (NB: vous pourrez activer le th&egrave;me ult&eacute;rieurement)', 'evarisk') . '</label>
	</fieldset>

	<div class="digirisk_install_button_container" >
		<div id="load_picture_container" >&nbsp;</div>
		<div id="button_container" ><input type="submit" class="button-primary" name="digirisk_install_button" value="' . __('Installer le logiciel', 'evarisk') . '" /></div>
	</div>
</form>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		var autoInstall = false;';
		if(STANDALONEVERSION){
			$installation_form .=
		'var autoInstall = true;';
		}

		$installation_form .= '
		jQuery("#insert_evarisk_main_method").click(function(){
			if(jQuery(this).is(":checked")){
				jQuery("#insert_basic_operator").prop("checked", true);
				jQuery("#insert_basic_operator").prop("disabled", true);
				jQuery("#insert_basic_vars").prop("checked", true);
				jQuery("#insert_basic_vars").prop("disabled", true);
			}
			else{
				jQuery("#insert_basic_operator").prop("disabled", false);
				jQuery("#insert_basic_vars").prop("disabled", false);
			}
		});

		jQuery("#digi_install_form").ajaxForm({
			target: "ajax-response",
			dataType:  "json",
			success: function(responseText, statusText, xhr, $form){
				if(responseText["status"]){
					jQuery("#load_picture_container").html(digi_html_accent_for_js("' . sprintf(__('Installation termin&eacute;e. Vous allez &ecirc;tre redirig&eacute; dans quelque secondes. Si ce n\'est pas le cas %s', 'evarisk'), '<a href=\'' . admin_url("options-general.php?page=digirisk_options") . '\'>' . __('Cliquez ici', 'evarisk') . '</a>') . '"));
					setTimeout(function(){window.top.location.href = "' . admin_url("options-general.php?page=digirisk_options") . '";}, 5000);
				}
			},
			beforeSubmit: function(formData, jqForm, options){
				var check_if_install_could_be_launch = false;
				if(!jQuery("#activate_evarisk_theme").is(":checked") || autoInstall){
					check_if_install_could_be_launch = true;
				}
				else if(jQuery("#activate_evarisk_theme").is(":checked") && confirm(digi_html_accent_for_js("' . __('&Ecirc;tes vous s&ucirc;r de vouloir activer le th&egrave;me Evarisk pour votre Blog?\nNB: Si vous avez un th&egrave;me personnalis&eacute; celui sera remplac&eacute; par le th&egrave;me Evarisk. Il restera disponible dans la liste des th&egrave;mes.', 'evarisk') . '"))){
					check_if_install_could_be_launch = true;
				}

				if(check_if_install_could_be_launch){
					jQuery("#load_picture_container").html(jQuery("#round_loading_img div.round_loading_img").html() + "   " + digi_html_accent_for_js("' . __('Installation en cours. Merci de patienter.', 'evarisk') . '"));
					jQuery("#button_container").hide();
				}
				else{
					return false;
				}
			}
		});';

		if(STANDALONEVERSION){
			$installation_form .=
		'jQuery("#digi_install_form").submit();';
		}

		$installation_form .= '
	});
</script>
		' . digirisk_display::end_page();

		echo $installation_form;
	}

	/**
	 *	Method called when plugin is loaded for database update. This method allows to update the database structure, insert default content.
	 */
	public static function update_digirisk( $version_to_launch = -1 ){
		global $wpdb, $digirisk_db_table, $digirisk_db_table_list, $digirisk_update_way, $digirisk_db_content_add, $digirisk_db_content_update, $digirisk_db_options_add, $digirisk_table_structure_change, $digirisk_db_update, $standard_message_subject_to_send, $standard_message_to_send, $digirisk_db_table_operation_list;

		/** 	Initialisation des permissions (Lancement a chaque chargement du plugin) */
		digirisk_permission::digirisk_init_permission();

		/** Copie du dossier contenant les fichiers de modeles/images (Lancement a chaque chargement du plugin) */
		digirisk_tools::copyEntireDirectory(EVA_UPLOADS_PLUGIN_OLD_DIR, EVA_UPLOADS_PLUGIN_DIR);

		/** Copie/creation du dossier devant contenir les fichiers crees par le biais du logiciel DUER/Fiches de poste/... (Lancement a chaque chargement du plugin) */
		digirisk_tools::copyEntireDirectory(EVA_RESULTATS_PLUGIN_OLD_DIR, EVA_RESULTATS_PLUGIN_DIR);

		/**	Creation du dossier temporatire permettant de creer les documents au format odt (Lancement a chaque chargement du plugin) */
		if(!is_dir(EVA_RESULTATS_PLUGIN_DIR . 'tmp')){
			wp_mkdir_p( EVA_RESULTATS_PLUGIN_DIR . 'tmp' );
		}

		$current_db_version = digirisk_options::getDbOption('base_evarisk');

		$current_def_max_version = (string)max(array_keys($digirisk_update_way));
		if( !empty( $version_to_launch ) ||  ( $version_to_launch === 0 ) ) {
			$current_def_max_version = $version_to_launch;
		}
		$new_version = $current_def_max_version + 1;
		$version_nb_delta = $current_def_max_version - $current_db_version;

		$do_changes = false;

		/*	Check if there are modification to do	*/
		if($current_def_max_version >= $current_db_version){
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			/*	Check the lowest version of db to execute	*/
			$lowest_version_to_execute = (($current_def_max_version - $version_nb_delta) < 0) ? 0 : ($current_def_max_version - $version_nb_delta);

			for($i = $lowest_version_to_execute; $i <= $current_def_max_version; $i++){

				/*	Check if there are modification to do	*/
				if(isset($digirisk_update_way[$i])){
					$dependance_list = '';
					$dependance_to_make = false;

					/*	Check if there are modification to make on table	*/
					if ( !empty($digirisk_db_table_list[$i]) ) {
						foreach ( $digirisk_db_table_list[$i] as $table_name ) {
							if ( !empty($digirisk_db_table[$table_name]) ) {
								$table_update_result = dbDelta( $digirisk_db_table[$table_name] );
							}
						}
						$do_changes = true;
					}

			/********************/
			/*		Insert data	*/
			/********************/
					$do_changes = self::insert_data_for_version($i, $do_changes);

			/********************/
			/*		Update		*/
			/********************/
					if(is_array($digirisk_db_update) && !empty($digirisk_db_update[$i]) && (count($digirisk_db_update[$i]) > 0)){
						foreach($digirisk_db_update[$i] as $table_name => $def){
							foreach($def as $information_index => $table_information){
								$query = $wpdb->prepare($table_information, '');
								$wpdb->query( $query );
								$do_changes = true;
							}
						}
					}

			/*****************************************************/
			/*	Call specific data insertion for current version */
			/*****************************************************/
					$do_changes = self::make_specific_operation_on_update($i);

			/******************************************************/
			/*		Make special operation on database structure		*/
			/******************************************************/
					if(is_array($digirisk_table_structure_change) && !empty($digirisk_table_structure_change[$i]) && (count($digirisk_table_structure_change[$i]) > 0)){
						foreach($digirisk_table_structure_change[$i] as $table_name => $operations_to_make){
							foreach($operations_to_make as $operation_index => $operation){
								$query = $wpdb->prepare($operation['MAIN_ACTION'] . " TABLE " . $table_name . " " . $operation['ACTION'] . " " . $operation['ACTION_CONTENT'], "");
								$wpdb->query($query);
							}
						}
						$do_changes = true;
					}
				}
			}
		}

// 			self::make_specific_operation_on_update('dev');

		/*	Update the db version option value	*/
		// $do_changes = false;
		if($do_changes){
			$digirisk_db_options = array();
			$digirisk_db_options['base_evarisk'] = $new_version;
			update_option('digirisk_db_option', $digirisk_db_options);
		}
	}

	function repair_database( $version_number ) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb, $digirisk_db_table, $digirisk_db_table_list, $digirisk_update_way, $digirisk_db_content_add, $digirisk_db_content_update, $digirisk_db_options_add, $digirisk_table_structure_change, $digirisk_db_update, $standard_message_subject_to_send, $standard_message_to_send, $digirisk_db_table_operation_list;

		if(isset($digirisk_update_way[$version_number])){
			$dependance_list = '';
			$dependance_to_make = false;

			/*	Check if there are modification to make on table	*/
			if(isset($digirisk_db_table_list[$version_number])){
				foreach($digirisk_db_table_list[$version_number] as $table_name){
					if(!empty($digirisk_db_table[$table_name]))
						$table_update_result = dbDelta($digirisk_db_table[$table_name]);
				}
				$do_changes = true;
			}

			/********************/
			/*		Update		*/
			/********************/
			if(is_array($digirisk_db_update) && !empty($digirisk_db_update[$version_number]) && (count($digirisk_db_update[$version_number]) > 0)){
				foreach($digirisk_db_update[$version_number] as $table_name => $def){
					foreach($def as $version_numbernformation_index => $table_information){
						$query = $wpdb->prepare($table_information, '');
						$wpdb->query($table_information);
						$do_changes = true;
					}
				}
			}

			/******************************************************/
			/*		Make special operation on database structure		*/
			/******************************************************/
			if(is_array($digirisk_table_structure_change) && !empty($digirisk_table_structure_change[$version_number]) && (count($digirisk_table_structure_change[$version_number]) > 0)){
				foreach($digirisk_table_structure_change[$version_number] as $table_name => $operations_to_make){
					foreach($operations_to_make as $operation_index => $operation){
						$query = $wpdb->prepare($operation['MAIN_ACTION'] . " TABLE " . $table_name . " " . $operation['ACTION'] . " " . $operation['ACTION_CONTENT']);
						$wpdb->query($query);
					}
				}
				$do_changes = true;
			}
		}

		return $do_changes;
	}

	/**
	 *
	 */
	function insert_data_for_version($i, $do_changes = '') {
		global $wpdb, $digirisk_db_table, $digirisk_db_table_list, $digirisk_update_way, $digirisk_db_content_add, $digirisk_db_content_update, $digirisk_db_options_add, $digirisk_db_options_update, $standard_message_subject_to_send, $standard_message_to_send;
		$dependance_to_make = false;
		$dependance_list=array();

		/*	Options content	*/
		if(is_array($digirisk_db_options_add) && !empty($digirisk_db_options_add[$i]) && (count($digirisk_db_options_add[$i]) > 0)){
			foreach($digirisk_db_options_add[$i] as $option_name => $option_content){
				add_option($option_name, $option_content, '', 'yes');
			}
		}
		if(is_array($digirisk_db_options_update) && !empty($digirisk_db_options_update[$i]) && (count($digirisk_db_options_update[$i]) > 0)){
			foreach($digirisk_db_options_update[$i] as $option_name => $option_content){
				$option_current_content = get_option($option_name);
				foreach($option_content as $option_key => $option_value){
					$option_current_content[$option_key] = $option_value;
				}
				update_option($option_name, $option_current_content);
			}
		}

		/*	Add datas	*/
		if(is_array($digirisk_db_content_add) && !empty($digirisk_db_content_add[$i]) && (count($digirisk_db_content_add[$i]) > 0)){
			foreach($digirisk_db_content_add[$i] as $table_name => $def){
				foreach($def as $information_index => $table_information){
					if(isset($table_information['dependance'])){
						$dependance_list[] = $table_information['dependance'];
						$dependance_to_make = true;
						unset($table_information['dependance']);
					}
					if(isset($table_information['parent_element'])){
						$parent = self::get_parent_element_identifier($table_name, $table_information['parent_element']);
						unset($table_information['parent_element']);
						$table_information[$parent['name']] = $parent['id'];
					}
					$wpdb->insert($table_name, $table_information, '%s');
					$do_changes = true;
				}
			}
		}

		/*	Update datas	*/
		if(is_array($digirisk_db_content_update) && !empty($digirisk_db_content_update[$i]) && (count($digirisk_db_content_update[$i]) > 0)){
			foreach($digirisk_db_content_update[$i] as $table_name => $def){
				foreach($def as $information_index => $table_information){
					if(isset($table_information['dependance'])){
						$dependance_list[] = $table_information['dependance'];
						$dependance_to_make = true;
						unset($table_information['dependance']);
					}
					if(isset($table_information['parent_element'])){
						$parent = self::get_parent_element_identifier($table_name, $table_information['parent_element']);
						unset($table_information['parent_element']);
						$table_information['datas'][$parent['name']] = $parent['id'];
					}
					$wpdb->update($table_name, $table_information['datas'], $table_information['where'], '%s', '%s');
					$do_changes = true;
				}
			}
		}

		/*	Make dependance actions	*/
		if($dependance_to_make && (count($dependance_list) > 0)){
			foreach($dependance_list as $dependances){
				foreach($dependances as $dependance_table => $dependance){
					switch($dependance_table){
						case TABLE_PHOTO:{
							switch($dependance[2]){
								case TABLE_CATEGORIE_PRECONISATION:
									$element_identifier = evaRecommandationCategory::get_recommandation_category_id(array('id'), ' AND nom = "' . $dependance[3] . '"');
								break;
								case TABLE_PRECONISATION:
									$element_identifier = evaRecommandation::get_recommandation_id(array('id'), ' AND nom = "' . $dependance[3] . '"');
								break;
							}
							$new_cat_pict_id = EvaPhoto::saveNewPicture($dependance[2], $element_identifier, $dependance[0]);
							if($dependance[1] && ($dependance[1] == 'yes')){
								EvaPhoto::setMainPhoto($dependance[2], $element_identifier, $new_cat_pict_id, 'yes');
							}
						}
						break;
					}
				}
			}
		}

		return $do_changes;
	}

	/**
	 *
	 */
	function get_parent_element_identifier($element_type, $parent_data){
		$parent_infos = array();

		switch($element_type){
			case TABLE_PRECONISATION:{
				$parent_infos['id'] = evaRecommandationCategory::get_recommandation_category_id(array('id'), ' AND nom = "' . $parent_data . '"');
				$parent_infos['name'] = 'id_categorie_preconisation';
			}
			break;
		}

		return $parent_infos;
	}

	/**
	 *
	 */
	function make_specific_operation_on_update($version) {
		global $wpdb, $standard_message_subject_to_send, $standard_message_to_send;

		$do_changes_for_specific = false;
		switch($version){/*	Check different version for specific action	*/
			case 17:{
				$sql = "UPDATE " . TABLE_DUER . " SET groupesUtilisateursAffectes = groupesUtilisateurs ";
				$wpdb->query($sql);
				$do_changes_for_specific = true;
			}break;
			case 19:{
				$sql = "UPDATE " . TABLE_AVOIR_VALEUR . " SET idEvaluateur = '1' ";
				$wpdb->query($sql);
				$do_changes_for_specific = true;
			}break;
			case 20:{
				$sql = "UPDATE " . TABLE_AVOIR_VALEUR . " SET Status = 'Moderated' WHERE Status != 'Valid' ";
				$wpdb->query($sql);
				$do_changes_for_specific = true;
			}break;
			case 21:{
				$sql = "SELECT GROUP_CONCAT( id ) as LIST FROM " . TABLE_AVOIR_VALEUR . " GROUP BY id_risque, DATE";
				$listToUpdate = $wpdb->get_results($sql);
				foreach($listToUpdate as $listID){
					$sql = "SELECT MAX(id_evaluation) + 1 AS newId FROM " . TABLE_AVOIR_VALEUR;
					$newId = $wpdb->get_row($sql);

					$sql = $wpdb->prepare("UPDATE " . TABLE_AVOIR_VALEUR . " SET id_evaluation = '%d' WHERE id IN (" . $listID->LIST . ")", $newId->newId);
					$wpdb->query($sql);
				}
				$do_changes_for_specific = true;
			}break;
			case 25:{
				$sql = $wpdb->prepare("INSERT INTO " . TABLE_LIAISON_USER_ELEMENT . " SELECT '', status, date, 1, '', 0, id_user, id_element, table_element FROM " . TABLE_LIAISON_USER_EVALUATION ."", "");
				$wpdb->query($sql);
				$do_changes_for_specific = true;
			}break;
			case 26:{
				$sql = "UPDATE " . TABLE_DUER . " SET id_model = '1'";
				$wpdb->query($sql);
				/*	Move the directory containing the different models	*/
				if(is_dir(EVA_MODELES_PLUGIN_OLD_DIR) && !is_dir(EVA_MODELES_PLUGIN_DIR)){
					rename(EVA_MODELES_PLUGIN_OLD_DIR, EVA_MODELES_PLUGIN_DIR);
				}
				$do_changes_for_specific = true;
			}break;
			case 29:{
				$sql = "INSERT INTO " . TABLE_PHOTO_LIAISON . " SELECT '', status, isMainPicture, id, idDestination, tableDestination FROM " . TABLE_PHOTO . ";";
				$wpdb->query($sql);
				$do_changes_for_specific = true;
			}break;
			case 35:{
				wp_mkdir_p(EVA_GENERATED_DOC_DIR);
				/*	Move the directory containing the different models	*/
				if(is_dir(EVA_UPLOADS_PLUGIN_OLD_DIR) && !is_dir(EVA_UPLOADS_PLUGIN_DIR)){
					digirisk_tools::copyEntireDirectory(EVA_UPLOADS_PLUGIN_OLD_DIR, EVA_UPLOADS_PLUGIN_DIR);
				}
				/*	Move the directory containing the different models	*/
				if(is_dir(EVA_RESULTATS_PLUGIN_OLD_DIR) && !is_dir(EVA_RESULTATS_PLUGIN_DIR)){
					digirisk_tools::copyEntireDirectory(EVA_RESULTATS_PLUGIN_OLD_DIR, EVA_RESULTATS_PLUGIN_DIR);
				}
				$do_changes_for_specific = true;
			}break;
			case 41:{
				$sql = "SELECT * FROM " . TABLE_UTILISE_EPI;
				$epi_utilise_results = $wpdb->get_results($sql);

				$query = "  ";
				foreach($epi_utilise_results as $epi_utilise){
					switch($epi_utilise->ppeId){
						case 1:
							$newEpiId = 5;
						break;
						case 2:
							$newEpiId = 4;
						break;
						case 3:
							$newEpiId = 7;
						break;
						case 4:
							$newEpiId = 9;
						break;
						case 5:
							$newEpiId = 8;
						break;
						case 6:
							$newEpiId = 11;
						break;
						case 7:
							$newEpiId = 3;
						break;
						case 8:
							$newEpiId = 6;
						break;
					}
					$wpdb->insert(TABLE_LIAISON_PRECONISATION_ELEMENT, array('status' => 'valid', 'id_preconisation' => $newEpiId, 'id_element' => $epi_utilise->elementId, 'table_element' => $epi_utilise->elementTable));
				}
				$do_changes_for_specific = true;
			}break;
			case 44:{
				if( $wpdb->get_var("show tables like '" . TABLE_VERSION . "'") == TABLE_VERSION ){/*	Deplacement de la gestion des version	*/
					add_option('digirisk_db_option', array('base_evarisk' => digirisk_options::getDbOption('base_evarisk')));
				}
				if($wpdb->get_var("show tables like '" . TABLE_OPTION . "'") == TABLE_OPTION ){/*	Deplacement de la gestion des options	*/
					$optionToStore = array();

					/*	R�cup�ration de la liste des options existantes pour le transfert	*/
					$query = $wpdb->prepare("
						SELECT *
						FROM " . TABLE_OPTION . "", "");
					$optionsList = $wpdb->get_results($query);
					foreach($optionsList as $option){
						$optionToStore[$option->nom] = $option->valeur;
					}
					/*	Ajout de l'entr�e dans la table option avec toutes les valeurs des options	*/
					add_option('digirisk_options', $optionToStore);
				}

				if(($wpdb->get_var("show tables like '" . TABLE_EVA_USER_GROUP . "'") == TABLE_EVA_USER_GROUP) && ($wpdb->get_var("show tables like '" . TABLE_EVA_EVALUATOR_GROUP . "'") == TABLE_EVA_EVALUATOR_GROUP))
				{/*	Transfert des anciens groupes dans la nouvelle table	*/
					/*	Groupes d'employ�	*/
					$query = $wpdb->prepare("SELECT * FROM " . TABLE_EVA_USER_GROUP ."", "");
					$employeeGroups = $wpdb->get_results($query);
					$subQuery = "  ";
					foreach($employeeGroups as $employeeGroup)
					{
						$subQuery .= "('', '" . $wpdb->escape($employeeGroup->user_group_id) . "', '" . $wpdb->escape($employeeGroup->user_group_status) . "', 'employee', '" . current_time('mysql', 0) . "', 1, '', '', '" . $wpdb->escape($employeeGroup->user_group_name) . "', '" . $wpdb->escape($employeeGroup->user_group_description) . "'), ";
					}
					/*	Groupes	d'evaluateur	*/
					$query = $wpdb->prepare("SELECT * FROM " . TABLE_EVA_EVALUATOR_GROUP, "");
					$employeeGroups = $wpdb->get_results($query);
					foreach($employeeGroups as $employeeGroup)
					{
						$subQuery .= "('', '" . $wpdb->escape($employeeGroup->evaluator_group_id) . "', '" . $wpdb->escape($employeeGroup->evaluator_group_status) . "', 'evaluator', '" . $wpdb->escape($employeeGroup->evaluator_group_creation_date) . "', '" . $wpdb->escape($employeeGroup->evaluator_group_creation_user_id) . "', '" . $wpdb->escape($employeeGroup->evaluator_group_deletion_date) . "', '" . $wpdb->escape($employeeGroup->evaluator_deletion_user_id) . "', '" . $wpdb->escape($employeeGroup->evaluator_group_name) . "', '" . $wpdb->escape($employeeGroup->evaluator_group_description) . "'), ";
					}
					/*	Transfert dans la nouvelle table	*/
					$subQuery = trim(substr($subQuery, 0, -2));
					if($subQuery != "")
					{
						$query =
							"INSERT INTO " . DIGI_DBT_USER_GROUP . "
								(id, old_id, status, group_type, creation_date, creation_user_id, deletion_date, deletion_user_id, name, description)
							VALUES
								" . $subQuery;
						$wpdb->query($query);
					}

					/*	Transfert les tables non utilis�es vers la "trash" section	*/
					$query = $wpdb->prepare("RENAME TABLE " . TABLE_EVA_USER_GROUP . " TO " . TRASH_DIGI_DBT_USER_GROUP, "");
					$wpdb->query($query);
					$query = $wpdb->prepare("RENAME TABLE " . TABLE_EVA_EVALUATOR_GROUP . " TO " . TRASH_DIGI_DBT_EVALUATOR_GROUP, "");
					$wpdb->query($query);
				}

				if(($wpdb->get_var("show tables like '" . TABLE_LIAISON_USER_GROUPS . "'") == TABLE_LIAISON_USER_GROUPS) && ($wpdb->get_var("show tables like '" . TABLE_EVA_EVALUATOR_GROUP_BIND . "'") == TABLE_EVA_EVALUATOR_GROUP_BIND))
				{/*	Transfert la liaison des anciens groupes vers les nouveaux	*/
					/*	Groupes d'employ�	*/
					$query = $wpdb->prepare("SELECT * FROM " . TABLE_LIAISON_USER_GROUPS, "");
					$employeeGroupsLink = $wpdb->get_results($query);
					$subQuery = "  ";
					foreach($employeeGroupsLink as $employeeGroupLink)
					{
						$query = $wpdb->prepare("SELECT id FROM " . DIGI_DBT_USER_GROUP . " WHERE old_id = %d AND group_type = %s", $employeeGroupLink->id_group, 'employee');
						$newGroupId = $wpdb->get_row($query);
						$subQuery .= "('', '" . $wpdb->escape($employeeGroupLink->Status) . "', '" . $wpdb->escape($employeeGroupLink->date) . "', 1, '', '', '" . $wpdb->escape($newGroupId->id) . "', '" . $wpdb->escape($employeeGroupLink->id_element) . "', '" . $wpdb->escape($employeeGroupLink->table_element) . "_employee'), ";
					}
					/*	Groupes d'evaluateurs	*/
					$query = $wpdb->prepare("SELECT * FROM " . TABLE_EVA_EVALUATOR_GROUP_BIND, "");
					$evaluatorsGroupsLink = $wpdb->get_results($query);
					foreach($evaluatorsGroupsLink as $evaluatorsGroupLink)
					{
						$query = $wpdb->prepare("SELECT id FROM " . DIGI_DBT_USER_GROUP . " WHERE old_id = %d AND group_type = %s", $evaluatorsGroupLink->id_group, 'evaluator');
						$newGroupId = $wpdb->get_row($query);
						$subQuery .= "('', '" . $wpdb->escape($evaluatorsGroupLink->Status) . "', '" . $wpdb->escape($evaluatorsGroupLink->dateAffectation) . "', '" . $wpdb->escape($evaluatorsGroupLink->affectationUserId) . "', '" . $wpdb->escape($evaluatorsGroupLink->dateDesaffectation) . "', '" . $wpdb->escape($evaluatorsGroupLink->desaffectationUserId) . "', '" . $wpdb->escape($newGroupId->id) . "', '" . $wpdb->escape($evaluatorsGroupLink->id_element) . "', '" . $wpdb->escape($evaluatorsGroupLink->table_element) . "_evaluator'), ";
					}
					/*	Transfert dans la nouvelle table	*/
					$subQuery = trim(substr($subQuery, 0, -2));
					if($subQuery != "")
					{
						$query =
							"INSERT INTO " . DIGI_DBT_LIAISON_USER_GROUP . "
								(id, status, date_affectation, id_attributeur, date_desAffectation, id_desAttributeur, id_group, id_element, table_element)
							VALUES
								" . $subQuery;
						$wpdb->query($query);
					}

					/*	Transfert les tables non utilis�es vers la "trash" section	*/
					$query = $wpdb->prepare("RENAME TABLE " . TABLE_LIAISON_USER_GROUPS . " TO " . TRASH_DIGI_DBT_LIAISON_USER_GROUPS);
					$wpdb->query($query);
					$query = $wpdb->prepare("RENAME TABLE " . TABLE_EVA_EVALUATOR_GROUP_BIND . " TO " . TRASH_DIGI_DBT_EVALUATOR_GROUP_BIND);
					$wpdb->query($query);
				}

				if(($wpdb->get_var("show tables like '" . TABLE_EVA_USER_GROUP_DETAILS . "'") == TABLE_EVA_USER_GROUP_DETAILS) && ($wpdb->get_var("show tables like '" . TABLE_EVA_EVALUATOR_GROUP_DETAILS . "'") == TABLE_EVA_EVALUATOR_GROUP_DETAILS))
				{/*	Transfert des utilisateurs vers la table de liaison utilisateur element	*/
					/*	Groupes d'employ�	*/
					$query = $wpdb->prepare("SELECT * FROM " . TABLE_EVA_USER_GROUP_DETAILS, "");
					$employeeGroupsDetail = $wpdb->get_results($query);
					$subQuery = "  ";
					foreach($employeeGroupsDetail as $employeeGroupDetail)
					{
						$query = $wpdb->prepare("SELECT id FROM " . DIGI_DBT_USER_GROUP . " WHERE old_id = %d AND group_type = %s", $employeeGroupDetail->user_group_id, 'employee');
						$newGroupId = $wpdb->get_row($query);
						$subQuery .= "('', 'valid', '" . current_time('mysql', 0) . "', 1, '', '', '" . $wpdb->escape($employeeGroupDetail->user_id) . "', '" . $wpdb->escape($newGroupId->id) . "', '" . DIGI_DBT_USER_GROUP . "'), ";
					}
					/*	Groupes d'evaluateurs	*/
					$query = $wpdb->prepare("SELECT * FROM " . TABLE_EVA_EVALUATOR_GROUP_DETAILS, "");
					$evaluatorsGroupsDetail = $wpdb->get_results($query);
					foreach($evaluatorsGroupsDetail as $evaluatorsGroupDetail)
					{
						$query = $wpdb->prepare("SELECT id FROM " . DIGI_DBT_USER_GROUP . " WHERE old_id = %d AND group_type = %s", $evaluatorsGroupDetail->evaluator_group_id, 'evaluator');
						$newGroupId = $wpdb->get_row($query);
						$subQuery .= "('', '" . $wpdb->escape($evaluatorsGroupDetail->Status) . "', '" . $wpdb->escape($evaluatorsGroupDetail->dateEntree) . "', '" . $wpdb->escape($evaluatorsGroupDetail->affectationUserId) . "', '" . $wpdb->escape($evaluatorsGroupDetail->dateSortie) . "', '" . $wpdb->escape($evaluatorsGroupDetail->desaffectationUserId) . "', '" . $wpdb->escape($evaluatorsGroupDetail->user_id) . "', '" . $wpdb->escape($newGroupId->id) . "', '" . DIGI_DBT_USER_GROUP . "'), ";
					}
					/*	Transfert dans la nouvelle table	*/
					$subQuery = trim(substr($subQuery, 0, -2));
					if($subQuery != "")
					{
						$query =
							"INSERT INTO " . TABLE_LIAISON_USER_ELEMENT . "
								(id, status, date_affectation, id_attributeur, date_desAffectation, id_desAttributeur, id_user, id_element, table_element)
							VALUES
								" . $subQuery;
						$wpdb->query($query);
					}

					/*	Transfert les tables non utilis�es vers la "trash" section	*/
					$query = $wpdb->prepare("RENAME TABLE " . TABLE_EVA_USER_GROUP_DETAILS . " TO " . TRASH_DIGI_DBT_USER_GROUP_DETAILS, "");
					$wpdb->query($query);
					$query = $wpdb->prepare("RENAME TABLE " . TABLE_EVA_EVALUATOR_GROUP_DETAILS . " TO " . TRASH_DIGI_DBT_EVALUATOR_GROUP_DETAILS, "");
					$wpdb->query($query);
				}
				$do_changes_for_specific = true;
			}break;
			case 66:{
				$deleted_grpt_list = EvaGroupement::getGroupements("Status = 'deleted'");

				if(is_array($deleted_grpt_list)){
					foreach($deleted_grpt_list as $deleted_grpt){
						$listeUnitesDeTravail = EvaGroupement::getUnitesEtGroupementDescendants($deleted_grpt->id);
						if(is_array($listeUnitesDeTravail)){
							foreach($listeUnitesDeTravail as $key => $uniteDefinition){
								switch($uniteDefinition['table']){
									case TABLE_GROUPEMENT:{
										EvaGroupement::deleteGroupement($uniteDefinition['value']->id);
									}break;
									case TABLE_UNITE_TRAVAIL:{
										eva_UniteDeTravail::deleteWorkingUnit($uniteDefinition['value']->id);
									}break;
								}
							}
						}
					}
				}
				$do_changes_for_specific = true;
			}break;
			case 67:{
				$query = $wpdb->prepare("SELECT action FROM " . DIGI_DBT_ELEMENT_NOTIFICATION, "");
				$action_list = $wpdb->get_results($query);
				foreach($action_list as $action){
					$wpdb->update(DIGI_DBT_ELEMENT_NOTIFICATION, array('last_update_date' => current_time('mysql', 0), 'action_title' => __($action->action, 'evarisk'), 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send), array('action' => $action->action));
				}
				$do_changes_for_specific = true;
			}break;
			case 69:{
				$user_list = evaUser::getUserList();
				foreach($user_list as $user){
					$user = new WP_User($user->ID);
					if($user->has_cap('digi_add_action')){
						$user->has_cap('digi_ask_action_front');
					}
				}
				$do_changes_for_specific = true;
			}break;

			case '73':
		/* Mise à jour des sous elements de l'arbre des taches supprimees pour cohérence avec la corbeille des GP et UT */
				$query = $wpdb->prepare("SELECT id FROM " . TABLE_TACHE . " WHERE Status = %s", 'Deleted');
				$task_list = $wpdb->get_results($query);
				foreach ($task_list as $deleted_task) {
					$tache = new EvaTask($deleted_task->id);
					$tache->load();
					$task_children = $tache->getDescendants();
					foreach ($task_children->tasks as $index => $task) {
						$wpdb->update(TABLE_TACHE, array('Status'=>'Deleted'), array('id'=>$task->id));
						$sub_tasks = EvaTask::getChildren($task->id);
						foreach ($sub_tasks as $sub_task) {
							$wpdb->update(TABLE_ACTIVITE, array('Status'=>'Deleted'), array('id'=>$sub_task->id));
						}
					}
				}

				/*	Ajout des méthodes d'evaluation pour la pénibilité	*/
				$wpdb->insert(TABLE_METHODE, array('nom' => __('Manutention manuelle femme', 'evarisk'), 'Status' => 'Valid'));
				$methode_manutention_femme = $wpdb->insert_id;
				$wpdb->insert(TABLE_METHODE, array('nom' => __('Manutention manuelle homme', 'evarisk'), 'Status' => 'Valid'));
				$methode_manutention_homme = $wpdb->insert_id;
				$wpdb->insert(TABLE_METHODE, array('nom' => __('Vibration m&eacute;canique mains/bras', 'evarisk'), 'Status' => 'Valid'));
				$methode_vibration_main_bras = $wpdb->insert_id;
				$wpdb->insert(TABLE_METHODE, array('nom' => __('Vibration m&eacute;canique ensemble du corps', 'evarisk'), 'Status' => 'Valid'));
				$methode_vibration_corps = $wpdb->insert_id;
				$wpdb->insert(TABLE_METHODE, array('nom' => __('Posture p&eacute;nibles', 'evarisk'), 'Status' => 'Valid'));
				$methode_posture = $wpdb->insert_id;
				$wpdb->insert(TABLE_METHODE, array('nom' => __('Temp&eacute;ratures extr&ecirc;mes', 'evarisk'), 'Status' => 'Valid'));
				$methode_temperature = $wpdb->insert_id;
				$wpdb->insert(TABLE_METHODE, array('nom' => __('Bruit', 'evarisk'), 'Status' => 'Valid'));
				$methode_bruit = $wpdb->insert_id;

				/*	Changement d'image pour la catégorie	*/
				$activite_physique_cat_id_query = $wpdb->prepare("SELECT id FROM " . TABLE_CATEGORIE_DANGER . " WHERE nom = %s", strtolower( __('Activit&eacute; physique', 'evarisk')));
				$activite_physique_cat_id = $wpdb->get_var($activite_physique_cat_id_query);
				$activite_physique_cat_id_pict = EvaPhoto::saveNewPicture(TABLE_CATEGORIE_DANGER, $activite_physique_cat_id, 'medias/images/Pictos/categorieDangers/activitePhysique.png');
				EvaPhoto::setMainPhoto(TABLE_CATEGORIE_DANGER, $activite_physique_cat_id, $activite_physique_cat_id_pict, 'yes');

				/*	Create the new danger categories	*/
				$inrs_danger_categories = unserialize(DIGI_INRS_DANGER_LIST);
				foreach ($inrs_danger_categories as $danger_cat) {
					if (!empty($danger_cat['version']) && ($danger_cat['version'] == $version)) {
						$new_danger_cat_id = categorieDangers::saveNewCategorie($danger_cat['nom']);

						/*	If user ask to add danger in categories	*/
						$wpdb->insert(TABLE_DANGER, array('nom' => __('Divers', 'evarisk') . ' ' . strtolower($danger_cat['nom']), 'id_categorie' => $new_danger_cat_id));
						if ( !empty($danger_cat['risks']) && is_array($danger_cat['risks']) ) {
							foreach ( $danger_cat['risks'] as $risk_to_create ) {
								$wpdb->insert(TABLE_DANGER, array('nom' => $risk_to_create, 'id_categorie' => $new_danger_cat_id));
							}
						}

						/*	Insert picture for danger categories	*/
						if ( !empty($danger_cat['picture']) ) {
							$new_cat_pict_id = EvaPhoto::saveNewPicture(TABLE_CATEGORIE_DANGER, $new_danger_cat_id, $danger_cat['picture']);
							EvaPhoto::setMainPhoto(TABLE_CATEGORIE_DANGER, $new_danger_cat_id, $new_cat_pict_id, 'yes');
						}
					}
				}

		/* Ajout des variables permettant l'evaluation de la pénibilité */
				/*	Manutention pour les hommes	*/
				$question_manutention_homme[] = array('question'=>__('Charges < 10Kg ou 0,5 tonnes par jour', 'evarisk'), 'seuil'=>0);
				$question_manutention_homme[] = array('question'=>__('Charges < 15Kg ou 1 tonne par jour', 'evarisk'), 'seuil'=>48);
				$question_manutention_homme[] = array('question'=>__('Charges > 35Kg ou 2 tonnes par jour', 'evarisk'), 'seuil'=>51);
				$question_manutention_homme[] = array('question'=>__('Charges > 49Kg ou 5 tonnes par jour', 'evarisk'), 'seuil'=>80);
				$wpdb->insert(TABLE_VARIABLE, array('nom' => __('Port de charge (Homme)', 'evarisk'), 'Status' => 'Valid', 'min'=>1, 'max'=>4, 'annotation'=>__('Risque noir si des charges sont > &agrave; 49Kg ou 5 tonnes par jour\nRisque rouge si les charges sont > &agrave; 35Kg ou 2 tonnes par jour\nRisque orange si les charges sont < &agrave; 15Kg et 1 tonne par jour\nRisque blanc si les charges sont < 10Kg et 0,5 tonne par jour', 'evarisk'), 'affichageVar'=>'checkbox', 'questionVar'=>serialize($question_manutention_homme), 'questionTitre'=>__('Poids total transport&eacute; sur une journ&eacute;e de travail de 7 heures', 'evarisk')));
				$variable_manutention_homme = $wpdb->insert_id;
				/* Liaison entre variable et methode */
				$liaison_methode_manutention_variable = $wpdb->insert(TABLE_AVOIR_VARIABLE, array('id_methode'=>$methode_manutention_homme, 'id_variable'=>$variable_manutention_homme, 'ordre'=>1, 'date'=>current_time('mysql',0), 'Status'=>'Valid'));

				/*	Manutention pour les femmes	*/
				$question_manutention_femme[] = array('question'=>__('Charges < 5Kg ou 0,25 tonnes par jour', 'evarisk'), 'seuil'=>0);
				$question_manutention_femme[] = array('question'=>__('Charges < 7,5Kg ou 0,5 tonne par jour', 'evarisk'), 'seuil'=>48);
				$question_manutention_femme[] = array('question'=>__('Charges > 17,5Kg ou 1 tonnes par jour', 'evarisk'), 'seuil'=>51);
				$question_manutention_femme[] = array('question'=>__('Charges > 25Kg ou 2,5 tonnes par jour', 'evarisk'), 'seuil'=>80);
				$wpdb->insert(TABLE_VARIABLE, array('nom' => __('Port de charge (Femme)', 'evarisk'), 'Status' => 'Valid', 'min'=>1, 'max'=>4, 'annotation'=>__('Risque noir si des charges sont > &agrave; 25Kg ou 2,5 tonnes par jour\nRisque rouge si les charges sont > &agrave; 17,5Kg ou 1 tonne par jour\nRisque orange si les charges sont < &agrave; 7,5Kg et 0,5 tonne par jour\nRisque blanc si les charges sont < 5Kg et 0,25 tonne par jour', 'evarisk'), 'affichageVar'=>'checkbox', 'questionVar'=>serialize($question_manutention_femme), 'questionTitre'=>__('Poids total transport&eacute; sur une journ&eacute;e de travail de 7 heures', 'evarisk')));
				$variable_manutention_femme = $wpdb->insert_id;
				/* Liaison entre variable et methode */
				$liaison_methode_manutention_variable = $wpdb->insert(TABLE_AVOIR_VARIABLE, array('id_methode'=>$methode_manutention_femme, 'id_variable'=>$variable_manutention_femme, 'ordre'=>1, 'date'=>current_time('mysql',0), 'Status'=>'Valid'));

				/*	Postures penibles */
				$question_postures_penibles[] = array('question'=>__('Somme calcul&eacute; &agrave; partir de la check-list OSHA est < 3', 'evarisk'), 'seuil'=>0);
				$question_postures_penibles[] = array('question'=>__('Somme calcul&eacute; &agrave; partir de la check-list OSHA est < 5', 'evarisk'), 'seuil'=>48);
				$question_postures_penibles[] = array('question'=>__('Somme calcul&eacute; &agrave; partir de la check-list OSHA est >= 5', 'evarisk'), 'seuil'=>51);
				$question_postures_penibles[] = array('question'=>__('Somme calcul&eacute; &agrave; partir de la check-list OSHA est > 9', 'evarisk'), 'seuil'=>80);
				$wpdb->insert(TABLE_VARIABLE, array('nom' => __('Postures p&eacute;nibles', 'evarisk'), 'Status' => 'Valid', 'min'=>1, 'max'=>4, 'annotation'=>__('Risque noir si la somme est > 9 (choix arbitraire Evarisk)\nRisque rouge si la somme est >= 5 (choix OSHA)\nRisque orange si la somme est < 5 (choix arbitraire Evarisk)\nRisque blanc si la somme est < 3 (choix arbitraire Evarisk)', 'evarisk'), 'affichageVar'=>'checkbox', 'questionVar'=>serialize($question_postures_penibles), 'questionTitre'=>__('&Eacute;valuation des facteurs de risque relatifs aux membres sup&eacute;rieurs', 'evarisk')));
				$variable_postures_penible = $wpdb->insert_id;
				/* Liaison entre variable et methode */
				$liaison_methode_posture_variable = $wpdb->insert(TABLE_AVOIR_VARIABLE, array('id_methode'=>$methode_posture, 'id_variable'=>$variable_postures_penible, 'ordre'=>1, 'date'=>current_time('mysql',0), 'Status'=>'Valid'));

				/*	Vibrations Mains/bras */
				$question_vibrations[] = array('question'=>__('Vibrations < 1,25 m/s&sup2;', 'evarisk'), 'seuil'=>0);
				$question_vibrations[] = array('question'=>__('Vibrations < 2,5 m/s&sup2;', 'evarisk'), 'seuil'=>48);
				$question_vibrations[] = array('question'=>__('Vibrations > 2,5 m/s&sup2;', 'evarisk'), 'seuil'=>51);
				$question_vibrations[] = array('question'=>__('Vibrations > 5 m/s&sup2;', 'evarisk'), 'seuil'=>80);
				$wpdb->insert(TABLE_VARIABLE, array('nom' => __('Vibrations des mains/bras', 'evarisk'), 'Status' => 'Valid', 'min'=>1, 'max'=>4, 'annotation'=>__('Risque noir > 5 M/s&sup2;\nRisque rouge > 2,5 M/S&sup2;\nRisque orange < 2,5 m/s&sup2;\Risque Blanc < 1,25 m/s&sup2; (choix arbitraire Evarisk)', 'evarisk'), 'affichageVar'=>'checkbox', 'questionVar'=>serialize($question_vibrations), 'questionTitre'=>__('Pour une exposition quotidienne (8h)', 'evarisk')));
				$variable_vibrations_main_bras = $wpdb->insert_id;
				/* Liaison entre variable et methode */
				$liaison_methode_posture_variable = $wpdb->insert(TABLE_AVOIR_VARIABLE, array('id_methode'=>$methode_vibration_main_bras, 'id_variable'=>$variable_vibrations_main_bras, 'ordre'=>1, 'date'=>current_time('mysql',0), 'Status'=>'Valid'));

				/*	Vibrations Corps */
				$question_vibrations_corps[] = array('question'=>__('Vibrations < 0,25 m/s&sup2;', 'evarisk'), 'seuil'=>0);
				$question_vibrations_corps[] = array('question'=>__('Vibrations < 0,5 m/s&sup2;', 'evarisk'), 'seuil'=>48);
				$question_vibrations_corps[] = array('question'=>__('Vibrations > 0,5 m/s&sup2;', 'evarisk'), 'seuil'=>51);
				$question_vibrations_corps[] = array('question'=>__('Vibrations > 1,15 m/s&sup2;', 'evarisk'), 'seuil'=>80);
				$wpdb->insert(TABLE_VARIABLE, array('nom' => __('Vibration de l\'ensemble du corps', 'evarisk'), 'Status' => 'Valid', 'min'=>1, 'max'=>4, 'annotation'=>__('Risque noir > 1.15 m/s&sup2;\nRisque rouge > 0,5 m/s&sup2;\nRisque orange < 0,5 m/s&sup2;\nRisque Blanc < 0,25 m/s&sup2; (choix arbitraire Evarisk)', 'evarisk'), 'affichageVar'=>'checkbox', 'questionVar'=>serialize($question_vibrations_corps), 'questionTitre'=>__('Pour une exposition quotidienne (8h)', 'evarisk')));
				$variable_vibrations_corps = $wpdb->insert_id;
				/* Liaison entre variable et methode */
				$liaison_methode_posture_variable = $wpdb->insert(TABLE_AVOIR_VARIABLE, array('id_methode'=>$methode_vibration_corps, 'id_variable'=>$variable_vibrations_corps, 'ordre'=>1, 'date'=>current_time('mysql',0), 'Status'=>'Valid'));

				/*	Temperature */
				$question_temperatures[] = array('question'=>__('20&deg;C < T > 24&deg;C', 'evarisk'), 'seuil'=>0);
				$question_temperatures[] = array('question'=>__('15&deg;C < T > 30&deg;C', 'evarisk'), 'seuil'=>48);
				$question_temperatures[] = array('question'=>__('T > 30&deg;C ou < 10&deg;C', 'evarisk'), 'seuil'=>51);
				$question_temperatures[] = array('question'=>__('T > 33&deg;C ou < 5&deg;C', 'evarisk'), 'seuil'=>80);
				$wpdb->insert(TABLE_VARIABLE, array('nom' => __('Exposition &agrave; des temp&eacute;ratures extr&ecirc;mes', 'evarisk'), 'Status' => 'Valid', 'min'=>1, 'max'=>4, 'annotation'=>__('Risque noir si T&deg; > 33&deg;C ou si T&deg; < 5&deg;C (seuil INRS ed 931)\nRisque rouge si T&deg; > 30&deg;C ou si T&deg; < 10&deg;C (travailler mieux)\nRisque orange si  15&deg;C < T&deg;< 30&deg;C (Choix arbitraire Evarisk)\nRisque Blanc si 20&deg;C < T&deg;< 24&deg;C (Choix INRS)', 'evarisk'), 'affichageVar'=>'checkbox', 'questionVar'=>serialize($question_temperatures), 'questionTitre'=>__('Exposition aux temp&eacute;ratures extr&ecirc;mes (Dur&eacute;e de 6 heures par jour)', 'evarisk')));
				$variable_temperature = $wpdb->insert_id;
				/* Liaison entre variable et methode */
				$liaison_methode_temperature_variable = $wpdb->insert(TABLE_AVOIR_VARIABLE, array('id_methode'=>$methode_temperature, 'id_variable'=>$variable_temperature, 'ordre'=>1, 'date'=>current_time('mysql',0), 'Status'=>'Valid'));

				/*	Bruit */
				$question_bruit[] = array('question'=>__('Lex, 8h < 80 dB(A) ou < 135 dB(C)', 'evarisk'), 'seuil'=>0);
				$question_bruit[] = array('question'=>__('Lex, 8h entre 80 dB(A) et 85 dB(A) ou entre 135 dB(C) et 137 dB(C)', 'evarisk'), 'seuil'=>48);
				$question_bruit[] = array('question'=>__('Lex, 8h > 85 dB(A) ou > 137 dB(C)', 'evarisk'), 'seuil'=>51);
				$question_bruit[] = array('question'=>__('Lex, 8h > 87 dB(A) ou > 140 dB(C)', 'evarisk'), 'seuil'=>80);
				$wpdb->insert(TABLE_VARIABLE, array('nom' => __('Exposition au bruit', 'evarisk'), 'Status' => 'Valid', 'min'=>1, 'max'=>4, 'annotation'=>__('Risque noir Lex, 8h > 87 dB(A) ou > 140 dB(C) seuil r&eacute;glementaire (France et europe)\nRisque rouge Lex, 8h > 85 dB(A) ou > 137 dB(C) seuil r&eacute;glementaire (France et europe)\nRisque orange Lex, 8h entre 80 dB(A) et 85 dB(A) ou entre 135 dB(C) et 137 dB(C) seuil europ&eacute;en\nRisque Blanc Lex, 8h < 80 dB(A) ou < 135 dB(C) seuil travailler mieux', 'evarisk'), 'affichageVar'=>'checkbox', 'questionVar'=>serialize($question_bruit), 'questionTitre'=>__('Exposition au bruit', 'evarisk')));
				$variable_bruit = $wpdb->insert_id;
				/* Liaison entre variable et methode */
				$liaison_methode_bruit_variable = $wpdb->insert(TABLE_AVOIR_VARIABLE, array('id_methode'=>$methode_bruit, 'id_variable'=>$variable_bruit, 'ordre'=>1, 'date'=>current_time('mysql',0), 'Status'=>'Valid'));


		/* Equivalence variable etalon */
				/*	Manutention manuelle femme	*/
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_manutention_femme, 'id_valeur_etalon'=>0, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>1, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_manutention_femme, 'id_valeur_etalon'=>48, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>2, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_manutention_femme, 'id_valeur_etalon'=>51, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>3, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_manutention_femme, 'id_valeur_etalon'=>100, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>4, 'Status'=>'Valid'));

				/*	Manutention manuelle homme	*/
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_manutention_homme, 'id_valeur_etalon'=>0, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>1, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_manutention_homme, 'id_valeur_etalon'=>48, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>2, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_manutention_homme, 'id_valeur_etalon'=>51, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>3, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_manutention_homme, 'id_valeur_etalon'=>100, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>4, 'Status'=>'Valid'));

				/*	Postures penibles 	*/
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_posture, 'id_valeur_etalon'=>0, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>1, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_posture, 'id_valeur_etalon'=>48, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>2, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_posture, 'id_valeur_etalon'=>51, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>3, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_posture, 'id_valeur_etalon'=>100, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>4, 'Status'=>'Valid'));

				/*	Vibrations mécaniques mains/bras 	*/
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_vibration_main_bras, 'id_valeur_etalon'=>0, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>1, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_vibration_main_bras, 'id_valeur_etalon'=>48, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>2, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_vibration_main_bras, 'id_valeur_etalon'=>51, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>3, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_vibration_main_bras, 'id_valeur_etalon'=>100, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>4, 'Status'=>'Valid'));

				/*	Vibrations mécaniques ensemble du corps 	*/
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_vibration_corps, 'id_valeur_etalon'=>0, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>1, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_vibration_corps, 'id_valeur_etalon'=>48, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>2, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_vibration_corps, 'id_valeur_etalon'=>51, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>3, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_vibration_corps, 'id_valeur_etalon'=>100, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>4, 'Status'=>'Valid'));

				/*	Temperature 	*/
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_temperature, 'id_valeur_etalon'=>0, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>1, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_temperature, 'id_valeur_etalon'=>48, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>2, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_temperature, 'id_valeur_etalon'=>51, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>3, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_temperature, 'id_valeur_etalon'=>100, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>4, 'Status'=>'Valid'));

				/*	Bruit 	*/
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_bruit, 'id_valeur_etalon'=>0, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>1, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_bruit, 'id_valeur_etalon'=>48, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>2, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_bruit, 'id_valeur_etalon'=>51, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>3, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_bruit, 'id_valeur_etalon'=>100, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>4, 'Status'=>'Valid'));

		/* Affectation du statut "penible" aux risques concernés */
				$query = $wpdb->prepare("SELECT id FROM " . TABLE_DANGER . " WHERE nom = %s ", __('Divers', 'evarisk') . ' ' . strtolower(__('Nuisances sonores', 'evarisk')));
				$risque_nuissance_sonore = $wpdb->get_var($query);
				$wpdb->update(TABLE_DANGER, array('choix_danger' => serialize(array('penibilite')), 'methode_eva_defaut' => $methode_bruit), array('id' => $risque_nuissance_sonore));

				$query = $wpdb->prepare("SELECT id FROM " . TABLE_DANGER . " WHERE nom = %s ", __('Divers', 'evarisk') . ' ' . strtolower(__('Ambiances climatiques', 'evarisk')));
				$risque_temperature = $wpdb->get_var($query);
				$wpdb->update(TABLE_DANGER, array('choix_danger' => serialize(array('penibilite')), 'methode_eva_defaut' => $methode_temperature), array('id' => $risque_temperature));

				$query = $wpdb->prepare("SELECT id FROM " . TABLE_DANGER . " WHERE nom = %s ", __('Divers', 'evarisk') . ' ' . strtolower(__('Manutention manuelle', 'evarisk')));
				$risque_manutention_manuelle = $wpdb->get_var($query);
				$wpdb->update(TABLE_DANGER, array('choix_danger' => serialize(array('penibilite')), 'methode_eva_defaut' => $methode_manutention_homme), array('id' => $risque_manutention_manuelle));

				$query = $wpdb->prepare("SELECT id FROM " . TABLE_DANGER . " WHERE nom = %s ", __('Divers', 'evarisk') . ' ' . strtolower(__('Postures penibles', 'evarisk')));
				$risque_posture = $wpdb->get_var($query);
				$wpdb->update(TABLE_DANGER, array('choix_danger' => serialize(array('penibilite')), 'methode_eva_defaut' => $methode_posture), array('id' => $risque_posture));

				$query = $wpdb->prepare("SELECT id FROM " . TABLE_DANGER . " WHERE nom = %s ", __('Divers', 'evarisk') . ' ' . strtolower(__('Vibrations', 'evarisk')));
				$risque_vibrations = $wpdb->get_var($query);
				$wpdb->update(TABLE_DANGER, array('choix_danger' => serialize(array('penibilite')), 'methode_eva_defaut' => $methode_vibration_main_bras), array('id' => $risque_vibrations));
				$do_changes_for_specific = true;
			break;

			case 74:
				/*	Check if default categories exist	*/
				$inrs_danger_categories = unserialize(DIGI_INRS_DANGER_LIST);
				foreach ($inrs_danger_categories as $danger_cat) {
					$query = $wpdb->prepare("SELECT id FROM " . TABLE_CATEGORIE_DANGER . " WHERE nom = %s", $danger_cat['nom']);
					$category_id = $wpdb->get_var($query);
					if ( empty($category_id) ) {
						$new_danger_cat_id = categorieDangers::saveNewCategorie($danger_cat['nom']);

						/*	If user ask to add danger in categories	*/
						$query = $wpdb->prepare("SELECT D.id FROM " . TABLE_DANGER . " AS D WHERE D.nom = %s AND D.Status = %s AND D.id_categorie NOT IN (SELECT id FROM " . TABLE_CATEGORIE_DANGER . ")", __('Divers', 'evarisk') . ' ' . strtolower($danger_cat['nom']), 'Valid');
						$danger_id = $wpdb->get_var($query);
						if ( empty($danger_id) ) {
							$wpdb->insert(TABLE_DANGER, array('nom' => __('Divers', 'evarisk') . ' ' . strtolower($danger_cat['nom']), 'id_categorie' => $new_danger_cat_id));
						}
						else {
							$wpdb->update(TABLE_DANGER, array('id_categorie' => $new_danger_cat_id), array('id' => $danger_id));
						}

						/*	Insert picture for danger categories	*/
						if ( !empty($danger_cat['picture']) ) {
							$new_cat_pict_id = EvaPhoto::saveNewPicture(TABLE_CATEGORIE_DANGER, $new_danger_cat_id, $danger_cat['picture']);
							EvaPhoto::setMainPhoto(TABLE_CATEGORIE_DANGER, $new_danger_cat_id, $new_cat_pict_id, 'yes');
						}
					}
				}
				$do_changes_for_specific = true;
			break;

			case 76:
				$wpdb->update(TABLE_FP, array('document_type' => 'fiche_de_groupement'), array('table_element' => TABLE_GROUPEMENT));
				$wpdb->update(TABLE_FP, array('document_type' => 'fiche_de_poste'), array('table_element' => TABLE_UNITE_TRAVAIL));
				$do_changes_for_specific = true;
			break;

			case 77:
				/*	Vibrations Corps */
				$question_vibrations_corps[] = array('question'=>__('Vibrations < 0,25 m/s&sup2;', 'evarisk'), 'seuil'=>0);
				$question_vibrations_corps[] = array('question'=>__('Vibrations < 0,5 m/s&sup2;', 'evarisk'), 'seuil'=>48);
				$question_vibrations_corps[] = array('question'=>__('Vibrations > 0,5 m/s&sup2;', 'evarisk'), 'seuil'=>51);
				$question_vibrations_corps[] = array('question'=>__('Vibrations > 1,15 m/s&sup2;', 'evarisk'), 'seuil'=>80);
				$wpdb->update(TABLE_VARIABLE, array('annotation'=>__('Risque noir > 1.15 m/s&sup2;\nRisque rouge > 0,5 m/s&sup2;\nRisque orange < 0,5 m/s&sup2;\nRisque Blanc < 0,25 m/s&sup2; (choix arbitraire Evarisk)', 'evarisk'), 'affichageVar'=>'checkbox', 'questionVar'=>serialize($question_vibrations_corps)), array('nom' => __('Vibration de l\'ensemble du corps', 'evarisk')));

				/*	Temperature */
				$question_bruit[] = array('question'=>__('Lex, 8h < 80 dB(A) ou < 135 dB(C)', 'evarisk'), 'seuil'=>0);
				$question_bruit[] = array('question'=>__('Lex, 8h entre 80 dB(A) et 85 dB(A) ou entre 135 dB(C) et 137 dB(C)', 'evarisk'), 'seuil'=>48);
				$question_bruit[] = array('question'=>__('Lex, 8h > 85 dB(A) ou > 137 dB(C)', 'evarisk'), 'seuil'=>51);
				$question_bruit[] = array('question'=>__('Lex, 8h > 87 dB(A) ou > 140 dB(C)', 'evarisk'), 'seuil'=>80);
				$wpdb->update(TABLE_VARIABLE, array('annotation'=>__('Risque noir Lex, 8h > 87 dB(A) ou > 140 dB(C) seuil r&eacute;glementaire (France et europe)\nRisque rouge Lex, 8h > 85 dB(A) ou > 137 dB(C) seuil r&eacute;glementaire (France et europe)\nRisque orange Lex, 8h entre 80 dB(A) et 85 dB(A) ou entre 135 dB(C) et 137 dB(C) seuil europ&eacute;en\nRisque Blanc Lex, 8h < 80 dB(A) ou < 135 dB(C) seuil travailler mieux', 'evarisk'), 'affichageVar'=>'checkbox', 'questionVar'=>serialize($question_bruit)), array('nom' => __('Exposition au bruit', 'evarisk')));

				$query = $wpdb->prepare("SELECT RISK_EVAL.id, RISK.commentaire FROM " . TABLE_AVOIR_VALEUR . " AS RISK_EVAL INNER JOIN " . TABLE_RISQUE . " AS RISK ON (RISK.id = RISK_EVAL.id_risque) WHERE RISK.Status = 'Valid' AND RISK_EVAL.Status = 'Valid' GROUP BY RISK_EVAL.id_evaluation", "");
				$risk_list = $wpdb->get_results($query);
				if ( !empty($risk_list) ) {
					foreach ( $risk_list as $risk) {
						$wpdb->update(TABLE_AVOIR_VALEUR, array('commentaire' => $risk->commentaire), array('id' => $risk->id));
					}
				}
				$do_changes_for_specific = true;
			break;

			case 79:
				/**	Agents chimiques */
				/**	Methode	*/
				$wpdb->insert(TABLE_METHODE, array('nom' => __('Agents chimiques', 'evarisk'), 'Status' => 'Valid'));
				$methode = $wpdb->insert_id;

				/**	Question	*/
				$question = array();
				$question[] = array('question'=>__('Exposition à un ACD moins de 10 h/semaine', 'evarisk'), 'seuil'=>0);
				$question[] = array('question'=>__('Exposition à un ACD entre 10 h/semaine et 17 h/semaine', 'evarisk'), 'seuil'=>48);
				$question[] = array('question'=>__('Exposition à un ACD entre 17 h/semaine et 35 h/semaine', 'evarisk'), 'seuil'=>51);
				$question[] = array('question'=>__('Exposition à des CMR ou dépassement de la VLEP ou dépassement de la VLCT', 'evarisk'), 'seuil'=>80);
				$wpdb->insert(TABLE_VARIABLE, array('nom' => __('Exposition aux agents chimiques', 'evarisk'), 'Status' => 'Valid', 'min'=>1, 'max'=>4, 'annotation'=>__('Risque noir : exposition à des CMR ou dépassement de la VLEP ou dépassement de la VLCT\nRisque rouge : exposition à un ACD entre 17 h/semaine et 35 h/semaine\nRisque orange : exposition à un ACD entre 10 h/semaine et 17 h/semaine\nRisque blanc : exposition à un ACD moins de 10 h/semaine', 'evarisk'), 'affichageVar'=>'checkbox', 'questionVar'=>serialize($question), 'questionTitre'=>__('Exposition aux agents chimiques', 'evarisk')));
				$variable = $wpdb->insert_id;

				/** Liaison entre variable et methode */
				$liaison_methode_variable = $wpdb->insert(TABLE_AVOIR_VARIABLE, array('id_methode'=>$methode, 'id_variable'=>$variable, 'ordre'=>1, 'date'=>current_time('mysql',0), 'Status'=>'Valid'));

				/*	Equivalence etalon 	*/
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode, 'id_valeur_etalon'=>0, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>1, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode, 'id_valeur_etalon'=>48, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>2, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode, 'id_valeur_etalon'=>51, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>3, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode, 'id_valeur_etalon'=>100, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>4, 'Status'=>'Valid'));

				/* Affectation du statut "penible" aux risques concernés */
				$query = $wpdb->prepare("SELECT id FROM " . TABLE_DANGER . " WHERE nom = %s ", __('Divers', 'evarisk') . ' ' . strtolower(__('Produits, &eacute;missions et d&eacute;chets', 'evarisk')));
				$risque_categorie = $wpdb->get_var($query);
				if ( empty($risque_categorie) ) {
					$query = $wpdb->prepare("SELECT id FROM " . TABLE_DANGER . " WHERE nom = %s ", __('Divers', 'evarisk') . ' ' . strtolower(__('Produits chimiques, d&eacute;chets', 'evarisk')));
					$risque_categorie = $wpdb->get_var($query);
				}
				$wpdb->update(TABLE_DANGER, array('choix_danger' => serialize(array('penibilite')), 'methode_eva_defaut' => $methode), array('id' => $risque_categorie));

				/**	Travail de nuit	*/
				$wpdb->insert(TABLE_METHODE, array('nom' => __('Travail de nuit', 'evarisk'), 'Status' => 'Valid'));
				$methode = $wpdb->insert_id;

				/**	Question	*/
				$question = array();
				$question[] = array('question'=>__('Moins de 135 h sur 12 mois consécutifs (choix arbitraire Evarisk)', 'evarisk'), 'seuil'=>0);
				$question[] = array('question'=>__('Entre 135 h et 270 h sur 12 mois consécutifs (choix arbitraire Evarisk)', 'evarisk'), 'seuil'=>48);
				$question[] = array('question'=>__('Au moins 3 heures de travail quotidien pendant la période entre 21 h et 6 h, au moins deux fois par semaine ou 270 h sur 12 mois consécutifs', 'evarisk'), 'seuil'=>51);
				$question[] = array('question'=>__('Au moins 6 heures de travail quotidien pendant la période entre 21 h et 6 h, au moins deux fois par semaine ou 540 h sur 12 mois consécutifs', 'evarisk'), 'seuil'=>80);
				$wpdb->insert(TABLE_VARIABLE, array('nom' => __('Travail de nuit', 'evarisk'), 'Status' => 'Valid', 'min'=>1, 'max'=>4, 'annotation'=>__('Risque noir : au moins 6 heures de travail quotidien pendant la période entre 21 h et 6 h, au moins deux fois par semaine ou 540 h sur 12 mois consécutifs\nRisque rouge : au moins 3 heures de travail quotidien pendant la période entre 21 h et 6 h, au moins deux fois par semaine ou 270 h sur 12 mois consécutifs\nRisque orange : entre 135 h et 270 h sur 12 mois consécutifs (choix arbitraire Evarisk)\nRisque blanc : moins de 135 h sur 12 mois consécutifs (choix arbitraire Evarisk)', 'evarisk'), 'affichageVar'=>'checkbox', 'questionVar'=>serialize($question), 'questionTitre'=>__('Travail de nuit', 'evarisk')));
				$variable = $wpdb->insert_id;

				/** Liaison entre variable et methode */
				$liaison_methode_variable = $wpdb->insert(TABLE_AVOIR_VARIABLE, array('id_methode'=>$methode, 'id_variable'=>$variable, 'ordre'=>1, 'date'=>current_time('mysql',0), 'Status'=>'Valid'));

				/*	Equivalence etalon 	*/
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode, 'id_valeur_etalon'=>0, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>1, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode, 'id_valeur_etalon'=>48, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>2, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode, 'id_valeur_etalon'=>51, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>3, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode, 'id_valeur_etalon'=>100, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>4, 'Status'=>'Valid'));

				$query = $wpdb->prepare("SELECT id FROM " . TABLE_CATEGORIE_DANGER . " WHERE nom = %s", __('Risques psychosociaux', 'evarisk'));
				$category_id = $wpdb->get_var($query);
				$wpdb->insert(TABLE_DANGER, array('nom' => __('Travail de nuit', 'evarisk'), 'id_categorie' => $category_id));
				$risque_categorie = $wpdb->insert_id;
				$wpdb->update(TABLE_DANGER, array('choix_danger' => serialize(array('penibilite')), 'methode_eva_defaut' => $methode), array('id' => $risque_categorie));


				/**	Travail en équipe*/
				$wpdb->insert(TABLE_METHODE, array('nom' => __('Travail en équipe successives alternantes', 'evarisk'), 'Status' => 'Valid'));
				$methode = $wpdb->insert_id;

				/**	Question	*/
				$question = array();
				$question[] = array('question'=>__('Pas de travail posté (choix arbitraire Evarisk)', 'evarisk'), 'seuil'=>0);
				$question[] = array('question'=>__('Pas de travail posté (pas de 3 X 8, 4 X 8, 5 X 8) sauf cas exceptionnel (choix arbitraire Evarisk)', 'evarisk'), 'seuil'=>48);
				$question[] = array('question'=>__('Travail posté mais que en 5 X 8 (exclut les 3 X 8, 4 X 8) (seuil travailler mieux)', 'evarisk'), 'seuil'=>51);
				$question[] = array('question'=>__('Changement de poste entre poste du matin, du soir, de nuit ou de journée (un changement dans la semaine ou d’une semaine à l’autre ou d’une quinzaine à l’autre)', 'evarisk'), 'seuil'=>80);
				$wpdb->insert(TABLE_VARIABLE, array('nom' => __('Travail en équipe successives alternantes', 'evarisk'), 'Status' => 'Valid', 'min'=>1, 'max'=>4, 'annotation'=>__('Risque noir : changement de poste entre poste du matin, du soir, de nuit ou de journée (un changement dans la semaine ou d’une semaine l’autre ou d’une quinzaine l’autre)\nRisque rouge : travail posté mais que en 5 X 8 (exclut les 3 X 8, 4 X 8) seuil travailler mieux http://www.travailler-mieux.gouv.fr/Travail-en-equipes-successives.html\nRisque orange : pas de travail posté (pas de 3 X 8, 4 X 8, 5 X 8) sauf cas exceptionnel (choix arbitraire Evarisk)\nRisque blanc : pas de travail posté (choix arbitraire Evarisk)', 'evarisk'), 'affichageVar'=>'checkbox', 'questionVar'=>serialize($question), 'questionTitre'=>__('Travail en équipe successives alternantes', 'evarisk')));
				$variable = $wpdb->insert_id;

				/** Liaison entre variable et methode */
				$liaison_methode_variable = $wpdb->insert(TABLE_AVOIR_VARIABLE, array('id_methode'=>$methode, 'id_variable'=>$variable, 'ordre'=>1, 'date'=>current_time('mysql',0), 'Status'=>'Valid'));

				/*	Equivalence etalon 	*/
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode, 'id_valeur_etalon'=>0, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>1, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode, 'id_valeur_etalon'=>48, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>2, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode, 'id_valeur_etalon'=>51, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>3, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode, 'id_valeur_etalon'=>100, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>4, 'Status'=>'Valid'));

				$query = $wpdb->prepare("SELECT id FROM " . TABLE_CATEGORIE_DANGER . " WHERE nom = %s", __('Risques psychosociaux', 'evarisk'));
				$category_id = $wpdb->get_var($query);
				$wpdb->insert(TABLE_DANGER, array('nom' => __('Travail en équipe successives alternantes', 'evarisk'), 'id_categorie' => $category_id));
				$risque_categorie = $wpdb->insert_id;
				$wpdb->update(TABLE_DANGER, array('choix_danger' => serialize(array('default','penibilite')), 'methode_eva_defaut' => $methode), array('id' => $risque_categorie));


				/**	Travail répétitif	*/
				$wpdb->insert(TABLE_METHODE, array('nom' => __('Travail répétitif', 'evarisk'), 'Status' => 'Valid'));
				$methode = $wpdb->insert_id;

				/**	Question	*/
				$question = array();
				$question[] = array('question'=>__('Temps de cycle < à 30 secondes ou > à 40 actions/minute, moins de 10% du temps de travail', 'evarisk'), 'seuil'=>0);
				$question[] = array('question'=>__('Temps de cycle < à 30 secondes ou > à 40 actions/minute, entre 10% et 30% du temps de travail', 'evarisk'), 'seuil'=>48);
				$question[] = array('question'=>__('Temps de cycle < à 30 secondes ou > à 40 actions/minute, entre 30% et 50% du temps de travail', 'evarisk'), 'seuil'=>51);
				$question[] = array('question'=>__('Temps de cycle < à 30 secondes ou > à 40 actions/minute, plus de 50% du temps de travail', 'evarisk'), 'seuil'=>80);
				$wpdb->insert(TABLE_VARIABLE, array('nom' =>__('Travail en équipe successives alternantes', 'evarisk'), 'Status' => 'Valid', 'min'=>1, 'max'=>4, 'annotation'=>__('Risque noir : temps de cycle < à 30 secondes ou > à 40 actions /minute, plus de 50 % du temps de travail\nRisque rouge : temps de cycle < à 30 secondes ou > à 40 actions/minute, entre 30 % et 50 % du temps de travail\nRisque orange : temps de cycle < à 30 secondes ou > à 40 actions/minute, entre 10 % et 30 % du temps de travail\nRisque blanc : temps de cycle < à 30 secondes ou > à 40 actions/minute, moins de 10 % du temps de travail', 'evarisk'), 'affichageVar'=>'checkbox', 'questionVar'=>serialize($question), 'questionTitre'=>__('Travail répétitif', 'evarisk')));
				$variable = $wpdb->insert_id;

				/** Liaison entre variable et methode */
				$liaison_methode_variable = $wpdb->insert(TABLE_AVOIR_VARIABLE, array('id_methode'=>$methode, 'id_variable'=>$variable, 'ordre'=>1, 'date'=>current_time('mysql',0), 'Status'=>'Valid'));

				/*	Equivalence etalon 	*/
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode, 'id_valeur_etalon'=>0, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>1, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode, 'id_valeur_etalon'=>48, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>2, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode, 'id_valeur_etalon'=>51, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>3, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode, 'id_valeur_etalon'=>100, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>4, 'Status'=>'Valid'));

				/* Affectation du statut "penible" aux risques concernés */
				$query = $wpdb->prepare("SELECT id FROM " . TABLE_DANGER . " WHERE nom = %s ", __('Divers', 'evarisk') . ' ' . strtolower(__('Activit&eacute; physique', 'evarisk')));
				$risque_categorie = $wpdb->get_var($query);
				$wpdb->update(TABLE_DANGER, array('choix_danger' => serialize(array('penibilite')), 'methode_eva_defaut' => $methode), array('id' => $risque_categorie));
					$do_changes_for_specific = true;
			break;

			case 80:
				/**	Update real affectation date with affectation date defined by the click on the user	*/
				$query = $wpdb->prepare("UPDATE " . TABLE_LIAISON_USER_ELEMENT . " SET date_affectation_reelle = date_affectation WHERE date_affectation_reelle = '0000-00-00 00:00:00'", array());
				$wpdb->query($query);
				$query = $wpdb->prepare("UPDATE " . TABLE_LIAISON_USER_ELEMENT . " SET date_desaffectation_reelle = date_desAffectation WHERE date_desaffectation_reelle = '0000-00-00 00:00:00'", array());
				$wpdb->query($query);

				/**	Set the penibility level by default	*/
				$options = get_option('digirisk_options');
				global $typeRisque;
				$options['digi_risk_penibility_level'] = $typeRisque['risq51'];
				update_option('digirisk_options', $options);

				/**	Update recommandation from EPI categoy to individual type	*/
				$query = $wpdb->prepare("SELECT id FROM " . TABLE_CATEGORIE_PRECONISATION . " WHERE nom = %s", '&eacute;quipements de protection individuelle');
				$epi_cat_id = $wpdb->get_var($query);
				$wpdb->update(TABLE_PRECONISATION, array('preconisation_type' => 'individuelles'), array('id_categorie_preconisation' => $epi_cat_id));

				/**	Add date of affectation to element follow information	*/
				$wpdb->query( "UPDATE " . TABLE_ACTIVITE_SUIVI . " SET date_ajout = date WHERE date_ajout = '0000-00-00 00:00:00'" );

				/**	Transfer all evaluation comment into follow up table	*/
				$query = $wpdb->prepare( "SELECT id_evaluation, date, commentaire, idEvaluateur FROM " . TABLE_AVOIR_VALEUR . " GROUP BY id_evaluation ", array());
				$evaluation_comments = $wpdb->get_results($query);
				if ( !empty($evaluation_comments) ) {
					foreach ( $evaluation_comments as $evaluation_infos) {
						if ( !empty($evaluation_infos->commentaire) )
							$wpdb->insert(TABLE_ACTIVITE_SUIVI, array('id' => null, 'status' => 'Valid', 'date' => current_time('mysql', 0), 'id_user' => $evaluation_infos->idEvaluateur, 'id_element' => $evaluation_infos->id_evaluation, 'table_element' => TABLE_AVOIR_VALEUR, 'commentaire' => $evaluation_infos->commentaire, 'date_ajout' => $evaluation_infos->date, 'export' => 'yes'));
					}
				}
					$do_changes_for_specific = true;
			break;
			case 81:
				/**	Add a position to the danger categories	*/
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 1), array( 'nom' => __('Accident de plain-pied', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 2), array( 'nom' => __('Chute de hauteur', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 3), array( 'nom' => __('Circulations internes', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 4), array( 'nom' => __('Circulation, d&eacute;placements', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 5), array( 'nom' => __('Activit&eacute; physique', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 6), array( 'nom' => __('Manutention m&eacute;canique', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 7), array( 'nom' => __('Produits, &eacute;missions et d&eacute;chets', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 8), array( 'nom' => __('Agents biologique', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 9), array( 'nom' => __('&Eacute;quipements de travail', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 10), array( 'nom' => __('Effondrements, chute d\'objet', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 11), array( 'nom' => __('Nuisances sonores', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 12), array( 'nom' => __('Ambiances climatiques', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 13), array( 'nom' => __('Incendie, explosion', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 14), array( 'nom' => __('Electricit&eacute;', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 15), array( 'nom' => __('Eclairage', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 16), array( 'nom' => __('Rayonnements', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 17), array( 'nom' => __('Risques psychosociaux', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 18), array( 'nom' => __('Autres', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 19), array( 'nom' => __('Manutention manuelle', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 20), array( 'nom' => __('Postures penibles', 'evarisk') ));
				$wpdb->update(TABLE_CATEGORIE_DANGER, array('position' => 21), array( 'nom' => __('Vibrations', 'evarisk') ));

				/**	Correction de l'affectation de la variable vibration ensemble du corps pour la methode vibration ensemble du corps	*/
				$query = $wpdb->prepare( "SELECT id FROM " . TABLE_VARIABLE . " WHERE nom = %s", __('Port de charge (Homme)', 'evarisk') );
				$var_id = $wpdb->get_var( $query );
					$query = $wpdb->prepare( "SELECT id FROM " . TABLE_METHODE . " WHERE nom = %s", __('Manutention manuelle homme', 'evarisk') );
					$methode_id = $wpdb->get_var( $query );
				$wpdb->update(TABLE_AVOIR_VARIABLE, array('id_variable'=>$var_id, 'ordre'=>1, 'date'=>current_time('mysql',0)), array('id_methode'=>$methode_id));

				$query = $wpdb->prepare( "SELECT id FROM " . TABLE_VARIABLE . " WHERE nom = %s", __('Port de charge (Femme)', 'evarisk') );
				$var_id = $wpdb->get_var( $query );
					$query = $wpdb->prepare( "SELECT id FROM " . TABLE_METHODE . " WHERE nom = %s", __('Manutention manuelle femme', 'evarisk') );
					$methode_id = $wpdb->get_var( $query );
				$wpdb->update(TABLE_AVOIR_VARIABLE, array('id_variable'=>$var_id, 'ordre'=>1, 'date'=>current_time('mysql',0)), array('id_methode'=>$methode_id));

				$query = $wpdb->prepare( "SELECT id FROM " . TABLE_VARIABLE . " WHERE nom = %s", __('Postures p&eacute;nibles', 'evarisk') );
				$var_id = $wpdb->get_var( $query );
					$query = $wpdb->prepare( "SELECT id FROM " . TABLE_METHODE . " WHERE nom = %s", __('Posture p&eacute;nibles', 'evarisk') );
					$methode_id = $wpdb->get_var( $query );
				$wpdb->update(TABLE_AVOIR_VARIABLE, array('id_variable'=>$var_id, 'ordre'=>1, 'date'=>current_time('mysql',0)), array('id_methode'=>$methode_id));

				$query = $wpdb->prepare( "SELECT id FROM " . TABLE_VARIABLE . " WHERE nom = %s", __('Vibration de l\'ensemble du corps', 'evarisk') );
				$var_id = $wpdb->get_var( $query );
					$query = $wpdb->prepare( "SELECT id FROM " . TABLE_METHODE . " WHERE nom = %s", __('Vibration m&eacute;canique ensemble du corps', 'evarisk') );
					$methode_id = $wpdb->get_var( $query );
				$wpdb->update(TABLE_AVOIR_VARIABLE, array('id_variable'=>$var_id, 'ordre'=>1, 'date'=>current_time('mysql',0)), array('id_methode'=>$methode_id));

				$query = $wpdb->prepare( "SELECT id FROM " . TABLE_VARIABLE . " WHERE nom = %s", __('Vibrations des mains/bras', 'evarisk') );
				$var_id = $wpdb->get_var( $query );
					$query = $wpdb->prepare( "SELECT id FROM " . TABLE_METHODE . " WHERE nom = %s", __('Vibration m&eacute;canique mains/bras', 'evarisk') );
					$methode_id = $wpdb->get_var( $query );
				$wpdb->update(TABLE_AVOIR_VARIABLE, array('id_variable'=>$var_id, 'ordre'=>1, 'date'=>current_time('mysql',0)), array('id_methode'=>$methode_id));

				$query = $wpdb->prepare( "SELECT id FROM " . TABLE_VARIABLE . " WHERE nom = %s", __('Exposition &agrave; des temp&eacute;ratures extr&ecirc;mes', 'evarisk') );
				$var_id = $wpdb->get_var( $query );
					$query = $wpdb->prepare( "SELECT id FROM " . TABLE_METHODE . " WHERE nom = %s", __('Temp&eacute;ratures extr&ecirc;mes', 'evarisk') );
					$methode_id = $wpdb->get_var( $query );
				$wpdb->update(TABLE_AVOIR_VARIABLE, array('id_variable'=>$var_id, 'ordre'=>1, 'date'=>current_time('mysql',0)), array('id_methode'=>$methode_id));

				$query = $wpdb->prepare( "SELECT id FROM " . TABLE_VARIABLE . " WHERE nom = %s", __('Exposition au bruit', 'evarisk') );
				$var_id = $wpdb->get_var( $query );
					$query = $wpdb->prepare( "SELECT id FROM " . TABLE_METHODE . " WHERE nom = %s", __('Bruit', 'evarisk') );
					$methode_id = $wpdb->get_var( $query );
				$wpdb->update(TABLE_AVOIR_VARIABLE, array('id_variable'=>$var_id, 'ordre'=>1, 'date'=>current_time('mysql',0)), array('id_methode'=>$methode_id));

				$query = $wpdb->prepare( "SELECT id FROM " . TABLE_VARIABLE . " WHERE nom = %s", __('Exposition aux agents chimiques', 'evarisk') );
				$var_id = $wpdb->get_var( $query );
					$query = $wpdb->prepare( "SELECT id FROM " . TABLE_METHODE . " WHERE nom = %s", __('Agents chimiques', 'evarisk') );
					$methode_id = $wpdb->get_var( $query );
				$wpdb->update(TABLE_AVOIR_VARIABLE, array('id_variable'=>$var_id, 'ordre'=>1, 'date'=>current_time('mysql',0)), array('id_methode'=>$methode_id));

				$query = $wpdb->prepare( "SELECT id FROM " . TABLE_VARIABLE . " WHERE nom = %s", __('Travail de nuit', 'evarisk') );
				$var_id = $wpdb->get_var( $query );
					$query = $wpdb->prepare( "SELECT id FROM " . TABLE_METHODE . " WHERE nom = %s", __('Travail de nuit', 'evarisk') );
					$methode_id = $wpdb->get_var( $query );
				$wpdb->update(TABLE_AVOIR_VARIABLE, array('id_variable'=>$var_id, 'ordre'=>1, 'date'=>current_time('mysql',0)), array('id_methode'=>$methode_id));

				$query = $wpdb->prepare( "SELECT id FROM " . TABLE_VARIABLE . " WHERE nom = %s", __('Travail r&eacute;p&eacute;tif', 'evarisk') );
				$var_id = $wpdb->get_var( $query );
					$query = $wpdb->prepare( "SELECT id FROM " . TABLE_METHODE . " WHERE nom = %s", __('Travail r&eacute;p&eacute;tif', 'evarisk') );
					$methode_id = $wpdb->get_var( $query );
				$wpdb->update(TABLE_AVOIR_VARIABLE, array('id_variable'=>$var_id, 'ordre'=>1, 'date'=>current_time('mysql',0)), array('id_methode'=>$methode_id));

				$query = $wpdb->prepare( "SELECT id FROM " . TABLE_VARIABLE . " WHERE nom = %s", __('Travail en &eacute;quipe successives alternantes', 'evarisk') );
				$var_id = $wpdb->get_var( $query );
					$query = $wpdb->prepare( "SELECT id FROM " . TABLE_METHODE . " WHERE nom = %s", __('Travail en &eacute;quipe successives alternantes', 'evarisk') );
					$methode_id = $wpdb->get_var( $query );
				$wpdb->update(TABLE_AVOIR_VARIABLE, array('id_variable'=>$var_id, 'ordre'=>1, 'date'=>current_time('mysql',0)), array('id_methode'=>$methode_id));
				$do_changes_for_specific = true;
			break;

			case 82:
				/**	Set the penibility level by default	*/
				$options = get_option('digirisk_options');
				$options['digi_risk_display_picture_in_listing'] = 'oui';
				$options['digi_risk_display_picture_in_listing'] = 'employerCreationDate';
				$options['digi_ac_task_default_exportable_plan_action'] = array('name' => 'oui', 'description' => 'oui');
				$options['digi_ac_activity_default_exportable_plan_action'] = array('name' => 'oui', 'description' => 'oui');
				update_option('digirisk_options', $options);
				$do_changes_for_specific = true;
			break;

			case 83:
				/**	Store Hiring and unHirring date for users in other way to get fast request	*/
				$query = $wpdb->prepare( "SELECT U.ID FROM " . $wpdb->users . " AS U", '' );
				$users = $wpdb->get_results( $query );
				if ( !empty($users) ) {
					foreach ( $users as $user) {
						$do_update = false;
						$user_meta = get_user_meta( $user->ID, 'digirisk_information', true);
						if ( !empty($user_meta) && is_array( $user_meta ) && !empty($user_meta['digi_hiring_date']) ) {
							update_user_meta( $user->ID, 'digi_hiring_date', $user_meta['digi_hiring_date']);
							unset( $user_meta['digi_hiring_date'] );
							$do_update = true;
						}
						if ( !empty($user_meta) && is_array( $user_meta ) && !empty($user_meta['digi_unhiring_date']) ) {
							update_user_meta( $user->ID, 'digi_unhiring_date', $user_meta['digi_unhiring_date']);
							unset( $user_meta['digi_unhiring_date'] );
							$do_update = true;
						}
						if ( $do_update ) {
							update_user_meta( $user->ID, 'digirisk_information', $user_meta);
						}
					}
				}
				$do_changes_for_specific = true;
			break;

			case 84:
				$wpdb->update( TABLE_GED_DOCUMENTS, array('categorie' => 'fiche_exposition_penibilite'), array('categorie' => 'fiches_de_penibilite') );
				$wpdb->update( TABLE_FP, array('document_type' => 'fiche_exposition_penibilite'), array('document_type' => 'fiches_de_penibilite') );
				$do_changes_for_specific = true;
			break;

			case 85:
				$query = $wpdb->prepare( "SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value != NULL", 'digi_unhiring_date' );
				$user_to_unassociate = $wpdb->get_results( $query );
				foreach ( $user_to_unassociate as $user_meta ) {
					$update_user = $wpdb->update( TABLE_LIAISON_USER_ELEMENT, array('status' => 'deleted', 'date_desAffectation' => current_time('mysql', 0), 'date_desaffectation_reelle' => $user_meta->meta_value, 'id_desAttributeur' => get_current_user_id()), array('id_user' => $user_meta->user_id, 'status' => 'valid') );
				}
				$do_changes_for_specific = true;
			break;

			case 86:
				$query = $wpdb->prepare( "SELECT R.id, MIN(E.date) AS EVAL_DATE FROM " . TABLE_RISQUE . " AS R INNER JOIN " . TABLE_AVOIR_VALEUR . " AS E ON (E.id_risque = R.id) GROUP BY R.id", array() );
				$risk_list = $wpdb->get_results( $query );

				foreach ( $risk_list as $risk ) {
					$risk_new_params = array(
						'dateDebutRisque' => $risk->EVAL_DATE,
						'risk_status' => 'open',
					);
					$wpdb->update( TABLE_RISQUE, $risk_new_params, array( 'id' => $risk->id ) );
				}

				/**	Set option defining the default date to take in care for risk start date	*/
				$options = get_option('digirisk_options');
				$options['digi_risk_start_date'] = 'employerCreationDate';
				$options['digi_risk_close_state_cotation_null'] = __('Non', 'evarisk');
				$options['digi_risk_close_state_end_date_filled'] = __('Non', 'evarisk');
				$options['digi_popin_size']['width'] = '800';
				$options['digi_popin_size']['height'] = '600';
				$options['digi_export_comment_in_doc'] = 'oui';
				update_option('digirisk_options', $options);
				$do_changes_for_specific = true;
			break;

			case 88:
				/**	Set option defining if users' groups must be displayed or not AND if users' rights must be displayed */
				$options = get_option( 'digirisk_options' );
				$options['activGroupsManagement'] = 'non';
				$options['activRightsManagement'] = 'non';
				update_option( 'digirisk_options', $options );

				/**	Set the duration that users hes been viewed during evaluation	*/
				$tables = array( TABLE_UNITE_TRAVAIL . '_evaluation', TABLE_GROUPEMENT . '_evaluation' );
				$query = $wpdb->prepare( "SELECT id, date_affectation, date_desAffectation, date_affectation_reelle FROM " . TABLE_LIAISON_USER_ELEMENT . " WHERE table_element IN (".implode(', ', array_fill(0, count($tables), '%s')).")", $tables );
				$affectations = $wpdb->get_results( $query );
				if ( !empty( $affectations ) ) {
					foreach ( $affectations as $affectation ) {
						$new_end_date = !empty( $affectation->date_affectation_reelle ) ? date( 'Y-m-d H:i:s', strtotime( "+1 hour", mysql2date( 'U', $affectation->date_affectation_reelle ) ) )  : '';
						$wpdb->update( TABLE_LIAISON_USER_ELEMENT, array( 'date_desaffectation_reelle' => $new_end_date, ), array( 'id' => $affectation->id, ) );
					}
				}
				$do_changes_for_specific = true;
			break;

			case 89:
				/**	Create new method for Amiante	*/
				$wpdb->insert(TABLE_METHODE, array('nom' => __('Amiante', 'evarisk'), 'Status' => 'Valid'));
				$methode_amiante_id = $wpdb->insert_id;

				/**	Create the new danger categories	*/
				$inrs_danger_categories = unserialize(DIGI_INRS_DANGER_LIST);
				foreach ($inrs_danger_categories as $danger_cat) {
					if (!empty($danger_cat['version']) && ($danger_cat['version'] == $version)) {
						$new_danger_cat_id = categorieDangers::saveNewCategorie($danger_cat['nom'], $danger_cat['position']);

						$query = $wpdb->prepare( "SELECT id FROM " .TABLE_DANGER . " WHERE nom = %s", __('Divers', 'evarisk') . ' ' . strtolower($danger_cat['nom']) );
						$existing_amiante_danger_id = $wpdb->get_var( $query );
						if ( empty( $existing_amiante_danger_id ) ) {
							$wpdb->insert( TABLE_DANGER, array('nom' => __('Divers', 'evarisk') . ' ' . strtolower($danger_cat['nom']), 'id_categorie' => $new_danger_cat_id, 'methode_eva_defaut' => $methode_amiante_id, ) );
						}
						else {
							$wpdb->update( TABLE_DANGER, array( 'methode_eva_defaut' => $methode_amiante_id, ), array( 'id' => $existing_amiante_danger_id, ) );
						}

						/*	If user ask to add danger in categories	*/
						if ( !empty($danger_cat['risks']) && is_array($danger_cat['risks']) ) {
							foreach ( $danger_cat['risks'] as $risk_to_create ) {
								$wpdb->insert(TABLE_DANGER, array('nom' => $risk_to_create, 'id_categorie' => $new_danger_cat_id, 'methode_eva_defaut' => $methode_amiante_id, ));
							}
						}

						/*	Insert picture for danger categories	*/
						if ( !empty($danger_cat['picture']) ) {
							$new_cat_pict_id = EvaPhoto::saveNewPicture(TABLE_CATEGORIE_DANGER, $new_danger_cat_id, $danger_cat['picture']);
							EvaPhoto::setMainPhoto(TABLE_CATEGORIE_DANGER, $new_danger_cat_id, $new_cat_pict_id, 'yes');
						}
					}
				}

				/**	Add the new var for amiante method	*/
				$question_amiante[] = array('question'=>__('Aucune exposition', 'evarisk'), 'seuil' => 0, );
				$question_amiante[] = array('question'=>__('Exposition niveau 1: Inférieur à 100 Fibres/litres', 'evarisk'), 'seuil' => 48, );
				$question_amiante[] = array('question'=>__('Exposition niveau 2 : Entre 100 et 6000 Fibres/litres', 'evarisk'), 'seuil' => 51, );
				$question_amiante[] = array('question'=>__('Exposition niveau 3 : Entre 6000 et 25000Fibres/litres', 'evarisk'), 'seuil' => 80, );
				$wpdb->insert(TABLE_VARIABLE, array('nom' => __('Amiante', 'evarisk'), 'Status' => 'Valid', 'min'=>0, 'max'=>3, 'annotation'=>__('Risque noir si exposition entre 6000 et 25000 Fibres/litres\nRisque rouge si exposition entre 100 et 6000 Fibres/litres\nRisque orange si exposition inférieur à 100 FIbrers/litres\nRisque blanc si aucune exposition', 'evarisk'), 'affichageVar'=>'checkbox', 'questionVar'=>serialize($question_amiante), 'questionTitre'=>__('Exposition à l\'amiante', 'evarisk')));
				$amiante_var_id = $wpdb->insert_id;

				/** Set link between var and method */
				$link_between_method_and_var = $wpdb->insert( TABLE_AVOIR_VARIABLE, array( 'id_methode' => $methode_amiante_id, 'id_variable' => $amiante_var_id, 'ordre' => 1, 'date' => current_time('mysql',0), 'Status'=>'Valid', ) );

				/*	Amiante	*/
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_amiante_id, 'id_valeur_etalon'=>0, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>0, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_amiante_id, 'id_valeur_etalon'=>48, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>1, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_amiante_id, 'id_valeur_etalon'=>51, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>2, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$methode_amiante_id, 'id_valeur_etalon'=>100, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>3, 'Status'=>'Valid'));
				$do_changes_for_specific = true;
			break;

			case 90:
				$wpdb->delete( TABLE_AVOIR_VARIABLE, array( 'id_methode' => 0 ) );

				/**	Clean database for method vars not used	*/
				$query = $wpdb->prepare( "DELETE FROM " . TABLE_VARIABLE . " WHERE id NOT IN ( SELECT id_variable FROM " . TABLE_AVOIR_VARIABLE . ") ", array() );
				$wpdb->query( $query );
				$do_changes_for_specific = true;
			break;

			case 91:
				/**	Creation des categories et préconisations par défaut pour les équipeemnts de protection collectives, si ceux ci n'ont pas été créés auparavant	*/
				$query = $wpdb->prepare( "SELECT id FROM " . TABLE_PRECONISATION . " WHERE nom LIKE '%%quipement de protection collective'", "" );
				$equipement_protection_collective = $wpdb->get_var( $query );
				$query = $wpdb->prepare( "SELECT id FROM " . TABLE_CATEGORIE_PRECONISATION . " WHERE nom LIKE '%%quipement de protection collective'", "" );
				$equipement_protection_collective_categorie = $wpdb->get_var( $query );
				if ( empty( $equipement_protection_collective_categorie ) ) {
					$wpdb->insert( TABLE_CATEGORIE_PRECONISATION, array( 'nom' => __( 'Divers &eacute;quipement de protection collective', 'evarisk' ), 'creation_date' => current_time( 'mysql', 0 ), ) );
					$equipement_protection_collective_categorie = $wpdb->insert_id;
					$new_cat_pict_id = EvaPhoto::saveNewPicture( TABLE_CATEGORIE_PRECONISATION, $equipement_protection_collective_categorie, 'medias/images/Pictos/preconisations/epc/preconisations_epc_s.png' );
					EvaPhoto::setMainPhoto( TABLE_CATEGORIE_PRECONISATION, $equipement_protection_collective_categorie, $new_cat_pict_id, 'yes');
				}
				if ( empty( $equipement_protection_collective ) && !empty( $equipement_protection_collective_categorie ) ) {
					$wpdb->insert( TABLE_PRECONISATION, array( 'nom' => __( 'Divers &eacute;quipement de protection collective', 'evarisk' ), 'creation_date' => current_time( 'mysql', 0 ), 'id_categorie_preconisation' => $equipement_protection_collective_categorie, 'preconisation_type' => 'collectives', ) );
					$equipement_protection_collective = $wpdb->insert_id;

					$new_cat_pict_id = EvaPhoto::saveNewPicture( TABLE_PRECONISATION, $equipement_protection_collective, 'medias/images/Pictos/preconisations/epc/preconisations_epc_s.png' );
					EvaPhoto::setMainPhoto( TABLE_PRECONISATION, $equipement_protection_collective, $new_cat_pict_id, 'yes');
				}

				/**	Vérification des options pour l'affectation des demandes dans le front et pour l'affectation des taches de controle	*/
				$options = get_option( 'digirisk_options' );
				if ( empty( $options[ 'digi_ac_control_action_affectation' ] ) ) {
					$control_task = new EvaTask();
					$control_task->setName( __( 'T&acirc;che de controle', 'evarisk' ) );
					$control_task->setDescription(  __( 'T&acirc;che contenant toutes les tacirc;ches de controle', 'evarisk' )  );
					$control_task->setProgressionStatus( 'notStarted' );
					$control_task->setnom_exportable_plan_action( 'no' );
					$control_task->setdescription_exportable_plan_action( 'no' );
					$control_task->save();
					$control_task->transfert( 1 );
					$control_task->load();
					$the_task_id = $control_task->getId();
					$options['digi_ac_control_action_affectation'] = $the_task_id;
					update_option( 'digirisk_options', $options );
				}

				if ( empty( $options[ 'digi_ac_front_ask_parent_task_id' ] ) ) {
					$ask_task = new EvaTask();
					$ask_task->setName( __( 'T&acirc;che de demande frontend', 'evarisk' ) );
					$ask_task->setDescription(  __( 'T&acirc;che contenant toutes les tacirc;ches demand&eacute;e dans la partie frontend', 'evarisk' )  );
					$ask_task->setProgressionStatus( 'notStarted' );
					$ask_task->setnom_exportable_plan_action( 'no' );
					$ask_task->setdescription_exportable_plan_action( 'no' );
					$ask_task->save();
					$ask_task->transfert( 1 );
					$ask_task->load();
					$the_task_id = $ask_task->getId();
					$options['digi_ac_front_ask_parent_task_id'] = $the_task_id;
					update_option( 'digirisk_options', $options );
				}

				$do_changes_for_specific = true;
			break;

			case 92:
				/**	Create new method for Amiante	*/
				$wpdb->insert( TABLE_METHODE, array('nom' => __('Résultats Seirich', 'evarisk'), 'Status' => 'Valid') );
				$seirich_method = $wpdb->insert_id;

				/**	Add the new var for amiante method	*/
				$question_seirich = array();
				$question_seirich[] = array('question'=>__('Faible ( 0 )', 'evarisk'), 'seuil' => 0, );
				$question_seirich[] = array('question'=>__('Modéré ( Entre 1 et 99 )', 'evarisk'), 'seuil' => 48, );
				$question_seirich[] = array('question'=>__('Préoccupant ( Entre 100 et 9999 )', 'evarisk'), 'seuil' => 51, );
				$question_seirich[] = array('question'=>__('Sérieux ( Entre 10000 et 999999 )', 'evarisk'), 'seuil' => 80, );
				$wpdb->insert( TABLE_VARIABLE, array(
					'nom' => __('Résultats Seirich', 'evarisk'),
					'Status' => 'Valid',
					'min'=>0,
					'max'=>3,
					'annotation'=>__('Risque faible 0. Risque modéré entre 1 et 99. Risque préoccupant entre 100 et 9999. Risque sérieux entre 10000 et 999999', 'evarisk'),
					'affichageVar'=>'checkbox',
					'questionVar'=>serialize( $question_seirich ),
					'questionTitre'=>__( 'Echelle de notation Seirich', 'evarisk' )
				));
				$seirich_var = $wpdb->insert_id;

				/** Set link between var and method */
				$link_between_method_and_var = $wpdb->insert( TABLE_AVOIR_VARIABLE, array( 'id_methode' => $seirich_method, 'id_variable' => $seirich_var, 'ordre' => 1, 'date' => current_time('mysql',0), 'Status'=>'Valid', ) );

				/*	Amiante	*/
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$seirich_method, 'id_valeur_etalon'=>0, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>0, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$seirich_method, 'id_valeur_etalon'=>48, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>1, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$seirich_method, 'id_valeur_etalon'=>51, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>2, 'Status'=>'Valid'));
				$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode'=>$seirich_method, 'id_valeur_etalon'=>100, 'date'=>current_time('mysql', 0), 'valeurMaxMethode'=>3, 'Status'=>'Valid'));

				$do_changes_for_specific = true;
			break;
			case 93:
				$wpdb->update( TABLE_METHODE, array( 'nom' => __('Résultats Seirich', 'evarisk'),  ), array( 'nom' => __('Seirich', 'evarisk'), ) );
				$wpdb->update( TABLE_VARIABLE, array( 'nom' => __('Résultats Seirich', 'evarisk'),  ), array( 'nom' => __('Seirich', 'evarisk'), ) );

				$methode_picture = $wpdb->insert( TABLE_PHOTO, array( 'id' => null, 'photo' => 'uploads/' . TABLE_METHODE . '/14/resultat_seirich.jpg' ) );
				$wpdb->insert( TABLE_PHOTO_LIAISON, array( 'status' => 'valid', 'isMainPicture' => true, 'idPhoto' => $wpdb->insert_id, 'idElement' => 14, 'tableElement' => TABLE_METHODE, ) );
				$do_changes_for_specific = true;
			break;

		}

		return $do_changes_for_specific;
	}

}
