<?php

class suivi_activite {

	function formulaire_ajout_suivi_projet($tableElement, $idElement, $complete_interface = true, $specific_follow_up = "") {
		$output = '';

		$saveButtonOuput = true;
		global $wpdb;

		switch ($tableElement) {
			case TABLE_TACHE:
				$currentTask = new EvaTask($idElement);
				$currentTask->load();
				$ProgressionStatus = $currentTask->getProgressionStatus();

				if((($ProgressionStatus == 'Done') || ($ProgressionStatus == 'DoneByChief')) && (digirisk_options::getOptionValue('possibilite_Modifier_Tache_Soldee') == 'non') ){
					$saveButtonOuput = false;
				}
				if (!current_user_can('digi_edit_task') && !current_user_can('digi_edit_task_' . $idElement)) {
					$saveButtonOuput = false;
				}
				$document_type_to_print = __('Plan d\'action', 'evarisk');
			break;
			case TABLE_ACTIVITE:
				$current_action = new EvaActivity($idElement);
				$current_action->load();
				$ProgressionStatus = $current_action->getProgressionStatus();

				if((($ProgressionStatus == 'Done') || ($ProgressionStatus == 'DoneByChief')) && (digirisk_options::getOptionValue('possibilite_Modifier_Action_Soldee') == 'non') ){
					$saveButtonOuput = false;
				}
				if (!current_user_can('digi_edit_action') && !current_user_can('digi_edit_action_' . $idElement)) {
					$saveButtonOuput = false;
				}
				$document_type_to_print = __('Plan d\'action', 'evarisk');
			break;

			case TABLE_UNITE_TRAVAIL:
			case TABLE_UNITE_TRAVAIL:
			case TABLE_AVOIR_VALEUR:
				if (!current_user_can('digi_edit_unite') && !current_user_can('digi_edit_unite_' . $idElement)) {
					$saveButtonOuput = false;
				}
				$document_type_to_print = __('DUER', 'evarisk');
				break;
		}

		$selected_date = current_time('mysql', 0);
		$export_state = '';
		$comment = '';
		$id_user_performer = '';
		$elapsed_time_hour = $elapsed_time_minutes = $cost = '';
		if ( !empty($specific_follow_up) ) {
			$query = $wpdb->prepare("SELECT * FROM " . TABLE_ACTIVITE_SUIVI . " WHERE id = %d", $specific_follow_up);
			$follow_up_infos = $wpdb->get_row($query);
			$selected_date = $follow_up_infos->date_ajout;
			$export_state = (!empty($follow_up_infos->export) && ($follow_up_infos->export == 'yes')) ? ' checked="checked"' : '';
			$comment = stripslashes( $follow_up_infos->commentaire );
			$id_user_performer =  $follow_up_infos->id_user_performer;
			$elapsed_time = $follow_up_infos->elapsed_time;
			$elapsed_time_hour = floor( $elapsed_time / 60 );
			$elapsed_time_hour = ($elapsed_time_hour < 10) ? '0' . $elapsed_time_hour : $elapsed_time_hour;
			$elapsed_time_minutes = $elapsed_time % 60;
			$elapsed_time_minutes = ($elapsed_time_minutes < 10) ? '0' . $elapsed_time_minutes : $elapsed_time_minutes;
			$cost = $follow_up_infos->cost;
		}

		$listeUtilisateurs = evaUser::getCompleteUserList();
		$user_doing_task = __('Aucun utilisateur trouv&eacute;', 'evarisk');
		if ( !empty($listeUtilisateurs) ) {
			$user_list = array();
			foreach ( $listeUtilisateurs as $user_infos) {
				$user_list[$user_infos['user_id']] = $user_infos['user_lastname'] . ' ' . $user_infos['user_firstname'];
			}
			$user_doing_task = EvaDisplayInput::createComboBox('follow_up_id_user_performer', TABLE_ACTIVITE_SUIVI . '[id_user_performer]', $user_list, (!empty($id_user_performer) ? $id_user_performer : get_current_user_id()), '', ' tabindex="16" ');
		}

		/**	Sub-Task elapsed time					*/
		$input_hour = '<input type="text" name="' . TABLE_ACTIVITE_SUIVI . '[elapsed_time][hour]" value="' . $elapsed_time_hour . '" id="project_elapsed_time_hour" maxlength="255" tabindex="17" style="width:30%;" /><script type="text/javascript" >digirisk(document).ready(function(){ jQuery("#project_elapsed_time_hour").keypress(function(event) { if (event.which && (event.which < 48 || event.which >57) && event.which != 8) { event.preventDefault(); } }); });</script>';
		$input_minute = '<input type="text" name="' . TABLE_ACTIVITE_SUIVI . '[elapsed_time][minutes]" value="' . $elapsed_time_minutes . '" id="project_elapsed_time_minutes" maxlength="255" tabindex="18" style="width:30%;" /><script type="text/javascript" >digirisk(document).ready(function(){ jQuery("#planned_time_minutes").keypress(function(event) { if (event.which && (event.which < 48 || event.which >57) && event.which != 8) { event.preventDefault(); } }); });</script>';
		$input_cost = '<input type="text" name="' . TABLE_ACTIVITE_SUIVI . '[cost]" value="' . $cost . '" id="cost" maxlength="255" tabindex="18" style="width:79%;" /> &euro;<script type="text/javascript" >digirisk(document).ready(function(){ jQuery("#cost").keypress(function(event) { if (event.which && (event.which < 48 || event.which >57) && event.which != 8 && event.which != 46) { event.preventDefault(); } }); });</script>';

		$output .= '
<form action="' . admin_url('admin-ajax.php') . '" method="post" id="digi_projet_follow_form' . $specific_follow_up . '" class="alignleft" >
	<input type="hidden" name="action" value="digi_ajax_save_activite_follow" />
	<input type="hidden" name="specific_follow_up" value="' . $specific_follow_up . '" />
	<input type="hidden" name="' . TABLE_ACTIVITE_SUIVI . '[follow_up_type]" value="follow_up" />
	<input type="hidden" name="tableElement" value="' . $tableElement . '" />
	<input type="hidden" name="idElement" value="' . $idElement . '" />
	<input type="hidden" name="digi_ajax_nonce" value="' . wp_create_nonce("digi_ajax_save_activite_follow") . '" />

	<ul style="width:300px;" >
		<li>
			<span style="display: inline-block; width: 200px;" >
				<label for="date_ajout' . $tableElement . $idElement . '_' . $specific_follow_up . '" >' . __('Date', 'evarisk') . '</label>
				<br/><input id="project_date_ajout' . $tableElement . $idElement . '_' . $specific_follow_up . '" type="text" value="' . $selected_date . '" name="' . TABLE_ACTIVITE_SUIVI . '[date_ajout]" tabindex="15" style="width: 99%;" >
			</span>
			<span style="display: inline-block; width: 90px;" >
				<label for="project_elapsed_time_hour" >' . __('Temps pass&eacute;', 'evarisk') . '</label>
				<br/>' . $input_hour . ' ' . __('H', 'evarisk') . ' ' . $input_minute . '
			</span>
		</li>
		<li>
			<span style="display: inline-block; width: 200px;" >
				<label for="follow_up_id_user_performer" >' . __('Ressources humaine', 'evarisk') . '</label>
				<br/>' . $user_doing_task . '
			</span>
			<span style="display: inline-block; width: 90px;" >
				' . __('Co&ucirc;t', 'evarisk') . '
				<br/>' . $input_cost . '
			</span>
		</li>
		<li>
			<div class="digi_ac_project_elapsed_time_comment" >' . __('Description', 'evarisk') . '</div>
			' . EvaDisplayInput::afficherInput('textarea', 'project_commentaire' . $tableElement . $idElement . '_' . $specific_follow_up, $comment, '', '', TABLE_ACTIVITE_SUIVI . '[commentaire]', false, true, 3, '', '', '100%', '', '', true, '19') . '
		</li>
		<li>
			<label for="digi_print_comment_in_doc' . $tableElement . $idElement .'_' . $specific_follow_up .  '" >' . sprintf( __('Imprimer dans %s', 'evarisk'), $document_type_to_print ) . '
				<input' . $export_state . ' type="checkbox" name="' . TABLE_ACTIVITE_SUIVI . '[export]" value="yes" id="digi_print_comment_in_doc' . $tableElement . $idElement .'_' . $specific_follow_up .  '" tabindex="20" />
			</label>';

			$idBouttonEnregistrer = 'saveActionFollowProject';
			if ( $complete_interface ) {
				$scriptEnregistrement = '';
				$output .='<div id="digi_ac_project_bttn' . $idBouttonEnregistrer . '" class="alignright" >' . EvaDisplayInput::afficherInput('submit', $idBouttonEnregistrer, __('Ajouter', 'evarisk'), null, '', $idBouttonEnregistrer, false, true, '', 'button-primary alignright', '', '', $scriptEnregistrement, '', false, '21') . '</div>
				<div id="digi_ac_project_load' . $idBouttonEnregistrer . '" class="alignright hide" ></div>';
			}
			else {
				$output .= '';
			}

			$output .= '
		</li>
	</ul>
</form>
<div id="digi_ac_project_follow_up" >';
			if ( $complete_interface ) {
				$output .= suivi_activite::tableauSuiviActivite($tableElement, $idElement, 'follow_up');
			}
			$output .=
'</div>
<div title="' . __('Modification d\'un suivi', 'evarisk') . '" id="digi_project_follow_up_update_box" ></div>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#project_date_ajout' . $tableElement . $idElement .'_' . $specific_follow_up .  '").datetimepicker({
			dateFormat: "yy-mm-dd",
			timeFormat: "hh:mm",
			regional: "fr_FR",
		});

		jQuery("#project_elapsed_time_hour").keypad({
			keypadOnly: false,
		});
		jQuery("#project_elapsed_time_minutes").keypad({
			keypadOnly: false,
		});
		jQuery("#cost").keypad({
			keypadOnly: false,
		});

		jQuery("#follow_up_id_user_performer").chosen();

		jQuery("#digi_project_follow_up_update_box").dialog({
			autoOpen: false,
			modal: true,
			width: 850,
			buttons: {
				"' . __('Enregistrer', 'evarisk') . '": function(){
					jQuery("#digi_projet_follow_form' . $specific_follow_up . '").submit();
					jQuery(this).dialog("close");
				},
				"' . __('Annuler', 'evarisk') . '": function(){
					jQuery(this).dialog("close");
				}
			},
			close: function(){
				jQuery("#digi_project_follow_up_update_box").html("");
			},
		});

		jQuery("#digi_projet_follow_form' . $specific_follow_up . '").submit(function(){
			jQuery(this).ajaxSubmit({
				dataType: "json",
				beforeSubmit: function() {
					jQuery("#digi_ac_project_bttn' . $idBouttonEnregistrer . '").hide();
					jQuery("#digi_ac_project_load' . $idBouttonEnregistrer . '").show();
				},
				success: function(response){
					if ( response[0] == "ok" ) {
						jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Les modifications ont correctement &eacute;t&eacute enregistr&eacute;es', 'evarisk') . '</strong></p>') . '");
						if ( jQuery("#digi_projet_follow_form' . $specific_follow_up . '") && jQuery("#digi_projet_follow_form' . $specific_follow_up . '")[0] ) {
							jQuery("#digi_projet_follow_form' . $specific_follow_up . '")[0].reset();
						}
						jQuery("#follow_up_id_user_performer").val("").trigger("liszt:updated");
						jQuery("#digi_ac_project_follow_up").load("' . admin_url('admin-ajax.php') . '", {action:"digi_ajax_load_activite_follow",digi_ajax_nonce:"' .  wp_create_nonce("digi_ajax_load_activite_follow") . '",tableElement:"' . $tableElement . '",idElement:"' . $idElement .'",follow_up_type:"follow_up"});

						if ( jQuery("#project_follow_up_summary_' . $tableElement . '_' . $idElement . '") && (response[1] != "") ) {
							jQuery("#project_follow_up_summary_' . $tableElement . '_' . $idElement . '").html(response[1]);
						}
						if ( jQuery("#inProgressButtonContainer") && (response[2] == "notStarted") ) {
							jQuery("#inProgressButtonContainer").remove();
						}
						if ( jQuery(".activityInfoContainer-' . $idElement . '") && (response[3] != "")) {
							jQuery(".activityInfoContainer-' . $idElement . '").html(response[3]);
						}
					}
					else {
						jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Les modifications n\'ont pas toutes &eacute;t&eacute correctement enregistr&eacute;es', 'evarisk') . '</strong></p>"') . '");
					}

					jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").show();
					jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").addClass("updated");
					setTimeout(function(){
						jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").removeClass("updated");
						jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").hide();
					},7500);

					jQuery("#digi_ac_project_bttn' . $idBouttonEnregistrer . '").show();
					jQuery("#digi_ac_project_load' . $idBouttonEnregistrer . '").hide();
				},
			});
			return false;
		});
	});
</script>';

		return $output;
	}

	function formulaireAjoutSuivi($tableElement, $idElement, $complete_interface = true, $specific_follow_up = "", $specific_name_for_input = "", $output_type = "table") {
		$saveButtonOuput = true;
		global $wpdb;

		switch ($tableElement) {
			case TABLE_TACHE:
				$currentTask = new EvaTask($idElement);
				$currentTask->load();
				$ProgressionStatus = $currentTask->getProgressionStatus();

				if((($ProgressionStatus == 'Done') || ($ProgressionStatus == 'DoneByChief')) && (digirisk_options::getOptionValue('possibilite_Modifier_Tache_Soldee') == 'non') ){
					$saveButtonOuput = false;
				}
				if (!current_user_can('digi_edit_task') && !current_user_can('digi_edit_task_' . $idElement)) {
					$saveButtonOuput = false;
				}
				$document_type_to_print = __('Plan d\'action', 'evarisk');
			break;
			case TABLE_ACTIVITE:
				$current_action = new EvaActivity($idElement);
				$current_action->load();
				$ProgressionStatus = $current_action->getProgressionStatus();

				if((($ProgressionStatus == 'Done') || ($ProgressionStatus == 'DoneByChief')) && (digirisk_options::getOptionValue('possibilite_Modifier_Action_Soldee') == 'non') ){
					$saveButtonOuput = false;
				}
				if (!current_user_can('digi_edit_action') && !current_user_can('digi_edit_action_' . $idElement)) {
					$saveButtonOuput = false;
				}
				$document_type_to_print = __('Plan d\'action', 'evarisk');
			break;

			case TABLE_UNITE_TRAVAIL:
			case TABLE_UNITE_TRAVAIL:
			case TABLE_AVOIR_VALEUR:
				if (!current_user_can('digi_edit_unite') && !current_user_can('digi_edit_unite_' . $idElement)) {
					$saveButtonOuput = false;
				}
				$document_type_to_print = __('DUER', 'evarisk');
			break;
		}

		$output = '';
		if ( $saveButtonOuput ) {
			$idBouttonEnregistrer = 'saveActionFollow';
			$scriptEnregistrement = '';
			if ( ($tableElement == TABLE_AVOIR_VALEUR) && $complete_interface) {
				$scriptEnregistrement = '';
			}

			$selected_date = current_time('mysql', 0);
			$export_state = '';
			$comment = '';
			if ( !empty($specific_follow_up) ) {
				$query = $wpdb->prepare("SELECT * FROM " . TABLE_ACTIVITE_SUIVI . " WHERE id = %d", $specific_follow_up);
				$follow_up_infos = $wpdb->get_row($query);
				$selected_date = substr( $follow_up_infos->date_ajout, 0, -3 );
				$export_state = (!empty($follow_up_infos->export) && ($follow_up_infos->export == 'yes')) ? ' checked="checked"' : '';
				$comment = stripslashes( $follow_up_infos->commentaire );
			}

			$options = get_option('digirisk_options');
			$export_state = !empty($options['digi_export_comment_in_doc']) && (strtolower( $options['digi_export_comment_in_doc'] ) == strtolower( __('Oui', 'evarisk') )) && empty( $export_state ) ? ' checked="checked"' : ( !empty( $export_state ) ? $export_state : '' );

			$date_input = '<input id="date_ajout' . $tableElement . $idElement . '_' . $specific_follow_up . '" type="text" value="' . $selected_date . '" name="' . (!empty($specific_name_for_input) ? $specific_name_for_input . '[date_ajout]' : 'date_ajout') . '">';
			$export_input = '<input' . $export_state . ' type="checkbox" name="' . (!empty($specific_name_for_input) ? $specific_name_for_input . '[export]' : 'export') . '" value="yes" id="digi_print_comment_in_doc_note' . $tableElement . $idElement .'_' . $specific_follow_up .  '" /> <label for="digi_print_comment_in_doc_note' . $tableElement . $idElement .'_' . $specific_follow_up .  '" >' . sprintf( __('Imprimer dans %s', 'evarisk'), $document_type_to_print ) . '</label>';
			$comment_input = EvaDisplayInput::afficherInput('textarea', 'commentaire' . $tableElement . $idElement . '_' . $specific_follow_up, $comment, '', '', (!empty($specific_name_for_input) ? $specific_name_for_input . '[commentaire]' : 'commentaire'), false, true, 7);

			if ( !empty($output_type) && ( $output_type == 'inline' ) ) {
				$output .= $date_input . '<br/>' . $export_input . $comment_input;
			}
			else {
				if ( !empty($output_type) && ( $output_type != 'no_form' ) ) {
					$output .= '
<form action="' . admin_url('admin-ajax.php') . '" method="post" id="digi_projet_follow_form' . $tableElement . $idElement . '_' . $specific_follow_up . '" >
	<input type="hidden" name="action" value="digi_ajax_save_activite_follow" />
	<input type="hidden" name="specific_follow_up" value="' . $specific_follow_up . '" />
	<input type="hidden" name="' . TABLE_ACTIVITE_SUIVI . '[follow_up_type]" value="note" />
	<input type="hidden" name="tableElement" value="' . $tableElement . '" />
	<input type="hidden" name="idElement" value="' . $idElement . '" />
	<input type="hidden" name="digi_ajax_nonce" value="' . wp_create_nonce("digi_ajax_save_activite_follow") . '" />';
				}

				$output .= '
	<table summary="" cellpadding="0" cellspacing="0" style="width:100%;" >
		<tr>
			<td style="width:20%;" >' . __('Commentaire', 'evarisk') . '</td>
			<td style="width:40%;" >' . $date_input . '<span title="' . __( 'Maintenant', 'evarisk' ) . '" style="font-style: italic;cursor: pointer;" class="digi_use_current_date" >' . __('M', 'evarisk') . '</span></td>
			<td style="width:40%;" >' . $export_input . '</td>
			<td rowspan="2" >';

				if ( $complete_interface ) {
					$output .=
				'<div id="bttn' . $idBouttonEnregistrer . '" >' . EvaDisplayInput::afficherInput('button', $idBouttonEnregistrer, __('Ajouter', 'evarisk'), null, '', $idBouttonEnregistrer, false, true, '', 'button-primary alignright', '', '', $scriptEnregistrement) . '</div><div style="float:right;display:none;" id="load' . $idBouttonEnregistrer . '" ></div>';
				}
				else {
					$output .= '';
				}

				$output .=
			'</td>
		</tr>
		<tr>
			<td colspan="3" >' . $comment_input . '</td>
		</tr>
	</table>';

				if ( !empty($output_type) && ( $output_type != 'no_form' ) ) {
					$output .= '
<form>';
				}
			}
						$current_date = substr( current_time('mysql', 0), 0, -3 );

			$output .= '
<div title="' . __('Modification d\'un commentaire', 'evarisk') . '" id="digi_follow_up_update_box" ></div>
<input type="hidden" name="" value="' . $tableElement . '" id="" />
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#date_ajout' . $tableElement . $idElement .'_' . $specific_follow_up .  '").datetimepicker({
			dateFormat: "yy-mm-dd",
			timeFormat: "hh:mm",
			regional: "fr_FR",
		});
		jQuery("#date_ajout' . $tableElement . $idElement .'_' . $specific_follow_up .  '").blur();

		jQuery(".digi_use_current_date").click(function(){
			var put_date = true;
			if ( jQuery("#digi_parent_creation_date").val() != undefined ) {
				if ( jQuery( "#digi_parent_creation_date" ).val() + ":00" > "' . $current_date . ':00" ) {
					alert( digi_html_accent_for_js( "' . __('Vous ne pouvez pas mettre une date inf&eacute;rieure &agrave; la date de cr&eacute;ation du groupement employeur', 'evarisk') . '" ) );
					put_date = false;
				}
			}

			if ( put_date ) {
				jQuery( "#date_ajout' . $tableElement . $idElement . '_' . $specific_follow_up . '" ).val( "' . $current_date . '" );
			}
		});

		jQuery("#digi_follow_up_update_box").dialog({
			autoOpen: false,
			modal: true,
			width: 850,
			buttons: {
				"' . __('Enregistrer', 'evarisk') . '": function(){
					jQuery("#digi_projet_follow_form' . $tableElement . $idElement . '_' . $specific_follow_up . '").submit();
					jQuery(this).dialog("close");
				},
				"' . __('Annuler', 'evarisk') . '": function(){
					jQuery(this).dialog("close");
				}
			},
			close: function(){
				jQuery("#digi_follow_up_update_box").html("");
			},
		});

		jQuery("#' . $idBouttonEnregistrer . '").click(function(){
			jQuery("#digi_projet_follow_form' . $tableElement . $idElement .'_' . $specific_follow_up . '").submit();
		});
		jQuery("#digi_projet_follow_form' . $tableElement . $idElement .'_' . $specific_follow_up . '").submit(function(){
			jQuery(this).ajaxSubmit({
				dataType: "json",
				beforeSubmit: function() {
					if (digirisk("#commentaire' . $tableElement . $idElement . '").val() != "") {
						digirisk("#load' . $idBouttonEnregistrer . '").html(\'<img src="' . PICTO_LOADING_ROUND . '" />\');
						digirisk("#bttn' . $idBouttonEnregistrer . '").hide();
						digirisk("#load' . $idBouttonEnregistrer . '").show();
					}
					else {
						alert(digi_html_accent_for_js("' . __('Vous ne pouvez pas ajouter de commentaire vide', 'evarisk') . '"));
						return false;
					}
				},
				success: function(response){
					after_save_follow_up_action(response) ;
				},
			});
			return false;
		});

		jQuery("#' . $idBouttonEnregistrer . '").unbind("click");
		jQuery("#' . $idBouttonEnregistrer . '").click(function() {
			var export_is_checked = "no";
			if ( jQuery("#digi_print_comment_in_doc_note' . $tableElement . $idElement . '_' . $specific_follow_up . '").is(":checked") ) {
				export_is_checked = "yes";
			}
			var data = {
				action: "digi_ajax_save_activite_follow",
				tableElement: "' . $tableElement . '",
				idElement: "' . $idElement . '",
				digi_ajax_nonce: "' . wp_create_nonce("digi_ajax_save_activite_follow") . '",
				specific_follow_up: "' . $specific_follow_up . '",
				' . TABLE_ACTIVITE_SUIVI . '_follow_up_type: "note",
				' . TABLE_ACTIVITE_SUIVI . '_date_ajout: jQuery("#date_ajout' . $tableElement . $idElement . '_' . $specific_follow_up . '").val(),
				' . TABLE_ACTIVITE_SUIVI . '_commentaire: jQuery("#commentaire' . $tableElement . $idElement . '_' . $specific_follow_up . '").val(),
				' . TABLE_ACTIVITE_SUIVI . '_export: export_is_checked,
			};
			jQuery.post("' . admin_url('admin-ajax.php') . '", data, function(response) {
				after_save_follow_up_action(response) ;
			}, "json");
		});
	});

	function after_save_follow_up_action(response) {
		if ( response[0] == "ok" ) {
			jQuery("#digi_msg_note_' . $tableElement . $idElement . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Les modifications ont correctement &eacute;t&eacute enregistr&eacute;es', 'evarisk') . '</strong></p>') . '");

			if ( jQuery("#digi_content_note_' . $tableElement . $idElement . '") && (response[1] != "") ) {
				jQuery("#digi_content_note_' . $tableElement . $idElement . '").html(response[1]);
			}
		}
		else {
			jQuery("#digi_msg_note_' . $tableElement . $idElement . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Les modifications n\'ont pas toutes &eacute;t&eacute correctement enregistr&eacute;es', 'evarisk') . '</strong></p>"') . '");
		}

		jQuery("#digi_msg_note_' . $tableElement . $idElement . '").show();
		jQuery("#digi_msg_note_' . $tableElement . $idElement . '").addClass("updated");
		setTimeout(function(){
			jQuery("#digi_msg_note_' . $tableElement . $idElement . '").removeClass("updated");
			jQuery("#digi_msg_note_' . $tableElement . $idElement . '").hide();
		},7500);

		jQuery("#bttn' . $idBouttonEnregistrer . '").show();
		jQuery("#load' . $idBouttonEnregistrer . '").hide();
	}
</script>';
		}
		else {
			$output .= '<div class="alignright " id="TaskSaveButton" >' .
					__('Vous ne pouvez pas ajouter de commentaire', 'evarisk') .
				'</div>';
		}

		if ( $complete_interface ) {
			$type = 'follow_up';
			if ( ( !empty( $specific_name_for_input ) && ( TABLE_ACTIVITE_SUIVI == $specific_name_for_input ) ) || ( TABLE_AVOIR_VALEUR == $tableElement ) ) {
				$type = 'note';
			}
			$output .= suivi_activite::tableauSuiviActivite($tableElement, $idElement, $type );
		}

		return $output;
	}

	function save( $tableElement, $idElement, $args = array() ) {
		global $wpdb;
		global $current_user;
		$result = array();

		$follow_up_params = array( 'id' => null, 'status' => 'valid', 'date' => current_time('mysql', 0), 'id_user' => $current_user->ID, 'id_element' => $idElement, 'table_element' => $tableElement );
		$follow_up_params['commentaire'] = !empty($args['commentaire']) ? str_replace("�","'", $args['commentaire']) : '';
		$follow_up_params['date_ajout'] = !empty($args['date_ajout']) ? $args['date_ajout'] : '';
		$follow_up_params['export'] = !empty($args['export']) ? $args['export'] : '';
		$follow_up_params['follow_up_type'] = !empty($args['follow_up_type']) ? $args['follow_up_type'] : 'note';
		$total_elapsed_time = 0;
		if ( !empty($args['elapsed_time']) ) {
			if ( !empty($args['elapsed_time']['hour']) ) {
				$total_elapsed_time += ($args['elapsed_time']['hour'] * 60);
			}
			if ( !empty($args['elapsed_time']['minutes']) ) {
				$total_elapsed_time += $args['elapsed_time']['minutes'];
			}
		}
		$follow_up_params['elapsed_time'] = $total_elapsed_time;
		$follow_up_params['cost'] = !empty($args['cost']) ? $args['cost'] : '';
		$follow_up_params['id_user_performer'] = !empty($args['id_user_performer']) ? $args['id_user_performer'] : '';

		$request = $wpdb->insert(TABLE_ACTIVITE_SUIVI, $follow_up_params);
		$last_comment = $wpdb->insert_id;
		if ($request && !empty($last_comment) && is_int($last_comment)) {
			$result = 'ok';
			switch ( $tableElement ) {
				case TABLE_AVOIR_VALEUR:
					$wpdb->update(TABLE_AVOIR_VALEUR, array('commentaire' => $follow_up_params['commentaire']), array('id' => $idElement));
				break;
			}
		}
		else {
			$result = 'error';
		}

		return array($result, $last_comment);
	}

	function save_suivi_activite( $id_suivi, $table_element, $id_element, $suivi_content_to_save ) {
		global $wpdb;
		$save_result = 'error';

		if ( !empty($id_suivi) ) {
			$saveFollow = array();
			$id_follow_up = digirisk_tools::IsValid_Variable($id_suivi);
			$new_follow_up = suivi_activite::save($table_element, $id_element, $suivi_content_to_save);
			$old_follow = $wpdb->update( TABLE_ACTIVITE_SUIVI, array('date_modification' => current_time('mysql', 0), 'status' => 'moderated', 'id_parent' => $new_follow_up[1]), array('id' => $id_follow_up) );
			if ( $old_follow !== false ) {
				digirisk_user_notification::log_element_modification($table_element, $id_element, 'follow_update', '', $suivi_content_to_save['commentaire']);
				$save_result = 'ok';
			}
			else {
				$save_result= 'error';
			}
		}
		else {
			$saveFollow = suivi_activite::save($table_element, $id_element, $suivi_content_to_save);
			$save_result = $saveFollow[0];
			if ( $saveFollow[0] == 'ok' ) {
				/**	Log modification on element and notify user if user subscribe	*/
				digirisk_user_notification::log_element_modification($table_element, $id_element, 'follow_add', '', $suivi_content_to_save['commentaire']);
			}
		}

		return $save_result;
	}

	public static function getSuiviActivite($tableElement, $idElement, $type = 'note', $specific_follow_up = 0) {
		global $wpdb;

		$more_query = '';
		switch ($tableElement) {
			case TABLE_AVOIR_VALEUR:
				$query = $wpdb->prepare("SELECT GROUP_CONCAT(id_evaluation) as risk_eval_list FROM " . TABLE_AVOIR_VALEUR . " WHERE id_risque = (SELECT DISTINCT(id_risque) FROM " . TABLE_AVOIR_VALEUR . " WHERE id_evaluation = %d) GROUP BY id_risque", $idElement);
				$risk_eval_list = $wpdb->get_var($query);
				$request_params = array($tableElement, $type);
				$more_query = "";
				if ( !empty($specific_follow_up) ) {
					$more_query = " AND id = %d";
					$request_params[] = $specific_follow_up;
				}
				$query = $wpdb->prepare(
					"SELECT *
			FROM " . TABLE_ACTIVITE_SUIVI . "
			WHERE id_element IN (" . $risk_eval_list . ")
				AND table_element = '%s'
				AND status = 'valid'
				AND follow_up_type = %s
				" . $more_query . "
			ORDER BY date_ajout DESC",
					$request_params
				);
				break;
			default:
				$request_params = array($idElement, $tableElement, $type);
				$more_query = "";
				if ( !empty($specific_follow_up) ) {
					$more_query = " AND id = %d";
					$request_params[] = $specific_follow_up;
				}
				$query = $wpdb->prepare(
					"SELECT *
					FROM " . TABLE_ACTIVITE_SUIVI . "
					WHERE id_element = '%s'
						AND table_element = '%s'
						AND status = 'valid'
						AND follow_up_type = %s
						" . $more_query . "
					ORDER BY date DESC",
						$request_params
				);
			break;
		}

		return $wpdb->get_results($query);
	}

	function display_elapsed_time_summary($elapsed_time_hour, $elapsed_time_minutes, $total_planned_time) {
		$class = '';
		$input_hour = '<input type="text" name="elapsed_time[hour]" disabled="disabled" value="' . ($elapsed_time_hour < 10 ? '0' . $elapsed_time_hour : $elapsed_time_hour) . '" id="elapsed_time_hour" maxlength="255" style="clear:both;width:10%;" class="input_required form-input-tip">';
		$input_minute = '<input type="text" name="elapsed_time[minutes]" disabled="disabled" value="' . ($elapsed_time_minutes < 10 ? '0' . $elapsed_time_minutes : $elapsed_time_minutes) . '" id="elapsed_time_minutes" maxlength="255" style="clear: both;width:10%;" class="input_required form-input-tip">';

		$total_elapsed_time = ($elapsed_time_hour * 60) + $elapsed_time_minutes;
		if ( $total_elapsed_time > $total_planned_time ) {
			$class = ' digi_alert';
		}

		$elapsed_time_output = '<label>' . __('Temps pass&eacute; : ', 'evarisk') . '</label><br/>' . sprintf(__('%sH %sMinutes', 'evarisk'), $input_hour, $input_minute);

		return array($elapsed_time_output, $class);
	}

	function tableauSuiviActivite ( $tableElement, $idElement, $follow_up_type = 'follow_up' ) {
		$output = '';

		$listSuivi = suivi_activite::getSuiviActivite($tableElement, $idElement, $follow_up_type);

		if( !empty($listSuivi) ) {
			switch ($tableElement) {
				case TABLE_TACHE:
				case TABLE_ACTIVITE:
					$document_type_to_print = __('le plan d\'action', 'evarisk');
					$follow_up_container = 'project_follow_up_summary_';
					break;

				case TABLE_UNITE_TRAVAIL:
				case TABLE_UNITE_TRAVAIL:
				case TABLE_AVOIR_VALEUR:
					$saveButtonOuput = 'yes';
					$document_type_to_print = __('Le DUER', 'evarisk');
					$follow_up_container = 'digi_content_note_';
					break;
			}

			$idTable = 'tableauSuiviModification' . $tableElement . $idElement . $follow_up_type;

			$titres[] = __('Date', 'evarisk');
			$titres[] = __('Heure', 'evarisk');
			// 					$titres[] = __('Dur&eacute;e', 'evarisk');
			$titres[] = __('Ressources / Dur&eacute;e / Description', 'evarisk');
			// 					$titres[] = __('Ressources', 'evarisk');
			$titres[] = __('Impression', 'evarisk'); // sprintf( __('Publier dans %s', 'evarisk'), $document_type_to_print );

			$titres[] = __('Actions', 'evarisk');



			$classes[] = 'digi_suivi_modif_date';
			$classes[] = 'digi_suivi_modif_heure';
			// 					$classes[] = 'digi_suivi_modif_elapsed_time_col';
			$classes[] = 'digi_suivi_modif_contenu';
			// 					$classes[] = '';
			$classes[] = 'digi_suivi_modif_export_col';

			$classes[] = 'digi_suivi_modif_action_col';


			unset($lignesDeValeurs);
			foreach ($listSuivi as $suivi) {
				unset($valeurs);
				$user_id_to_use = !empty($suivi->id_user_performer) ? $suivi->id_user_performer : $suivi->id_user;
				$user_info = get_userdata($user_id_to_use);
				$user_lastname = '';
				if ( (isset($user_info->user_lastname) && ($user_info->user_lastname != '')) ) {
					$user_lastname = $user_info->user_lastname;
				}
				$user_firstname = $user_info->user_nicename;
				if ( (isset($user_info->user_firstname) && ($user_info->user_firstname != '')) ) {
					$user_firstname = $user_info->user_firstname;
				}

				$valeurs[] = array('value' => mysql2date( get_option( 'date_format' ), $suivi->date_ajout, true ));
				$valeurs[] = array('value' => mysql2date( get_option( 'time_format' ), $suivi->date_ajout, true ));

				$elapsed_time = $suivi->elapsed_time;
				$elapsed_time_hour = floor( $suivi->elapsed_time / 60 );
				$elapsed_time_hour = ($elapsed_time_hour < 10) ? '0' . $elapsed_time_hour : $elapsed_time_hour;
				$elapsed_time_minutes = $suivi->elapsed_time % 60;
				$elapsed_time_minutes = ($elapsed_time_minutes < 10) ? '0' . $elapsed_time_minutes : $elapsed_time_minutes;
				// 						$valeurs[] = array('value' => sprintf( __('%s H %s Minutes <br/>(%s)', 'evarisk'), (!empty($elapsed_time_hour) ? $elapsed_time_hour : 0), (!empty($elapsed_time_minutes) ? $elapsed_time_minutes : 0), $suivi->cost . '&euro;' ) );
				$valeurs[] = array('value' => $user_lastname . ' ' . $user_firstname . ' - ' . sprintf( __('%s H %s Minutes', 'evarisk'), (!empty($elapsed_time_hour) ? $elapsed_time_hour : 0), (!empty($elapsed_time_minutes) ? $elapsed_time_minutes : 0), $suivi->cost . '&euro;' ) . '<br/>' . stripslashes( $suivi->commentaire ));
				// 						$valeurs[] = array('value' => $user_lastname . ' ' . $user_firstname);
				$valeurs[] = array('value' => (!empty($suivi->export) && ($suivi->export == 'yes')) ? __('Oui', 'evarisk')/* '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'veille-reponse.gif" alt="publish_in_doc" title="' . sprintf( __('Ce commentaire sera publi&eacute; dans %s', 'evarisk'), $document_type_to_print ) . '" />' */ : __('Non', 'evarisk'));

				$valeurs[] = array('value' => '<input type="hidden" value="' . $suivi->follow_up_type . '" id="digi_edit_follow_up_line_' . $suivi->id . '_folow_up_type" /><img src="' . PICTO_EDIT . '" alt="' . __('Edit this comment', 'evarisk') . '" class="digi_edit_follow_up_line" id="digi_edit_follow_up_line_' . $suivi->id . '" /><img src="' . PICTO_DELETE . '" alt="' . __('Delete this comment', 'evarisk') . '" class="digi_delete_follow_up_line" id="digi_delete_follow_up_line_' . $suivi->id . '" />');

				$lignesDeValeurs[] = $valeurs;
				$idLignes[] = $tableElement . $idElement . 'suiviModification';
			}

			$scriptTableauSuiviModification =
			'<script type="text/javascript">
						/* Formating function for row details */
						function fnFormatDetails ( oTable, nTr ){
						    var aData = oTable.fnGetData( nTr );
						    var sOut = \'<table cellpadding="7" cellspacing="0" border="0" style="padding-left:50px;">\';
						    sOut += \'<tr><td>' . __( 'Ressource', 'digirisk' ) . '</td><td>\'+aData[5]+\'</td></tr>\';
						    sOut += \'<tr><td>' . __( 'Impression dans le plan d action', 'digirisk' ) . '</td><td>\'+aData[6]+\'</td></tr>\';
						    sOut += \'</table>\';

						    return sOut;
						}
						digirisk(document).ready(function() {



						    /* Add event listener for opening and closing details
						     * Note that the indicator for showing which row is open is not controlled by DataTables,
						     * rather it is done here
						     */
						    digirisk( document ).on("click", "#' . $idTable . ' tbody td img", function () {
						        var nTr = digirisk(this).parents("tr")[0];
						        if ( oTable.fnIsOpen(nTr) )
						        {
						            /* This row is already open - close it */
						            this.src = "' . EVA_IMG_ICONES_PLUGIN_URL . 'details_open.png";
						            oTable.fnClose( nTr );
						        }
						        else
						        {
						            /* Open this row */
						            this.src = "' . EVA_IMG_ICONES_PLUGIN_URL . 'details_close.png";
						            oTable.fnOpen( nTr, fnFormatDetails(oTable, nTr), "details" );
						        }
						    } );

							oTable = digirisk("#' . $idTable . '").dataTable({
						        "fnDrawCallback": function ( oSettings ) {
						            if ( oSettings.aiDisplay.length == 0 ) {
						                return;
						            }

						            var nTrs = digirisk("#' . $idTable . ' tbody tr");
						            var iColspan = nTrs[0].getElementsByTagName("td").length;
						            var sLastGroup = "";
						            for ( var i=0 ; i<nTrs.length ; i++ ) {
						                var iDisplayIndex = oSettings._iDisplayStart + i;
						                var sGroup = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[0];
						                if ( sGroup != sLastGroup ) {
						                    var nGroup = document.createElement( "tr" );
						                    var nCell = document.createElement( "td" );
						                    nCell.colSpan = iColspan;
						                    nCell.className = "group";
						                    nCell.innerHTML = sGroup;
						                    nGroup.appendChild( nCell );
						                    nTrs[i].parentNode.insertBefore( nGroup, nTrs[i] );
						                    sLastGroup = sGroup;
						                }
						            }
						        },
						        "aoColumnDefs": [
						            { "bVisible": false, "aTargets": [ 0, 3 ] },
			          				{ "bSortable": false, "aTargets": [ 4 ] },
						        ],
						        "aaSortingFixed": [[ 0, "asc" ]],
						        "aaSorting": [[ 1, "asc" ]],
						        "sDom": \'lfr<"giveHeight"t>ip\',
						        "oLanguage":{
									"sUrl": "' . EVA_INC_PLUGIN_URL . 'js/dataTable/jquery.dataTables.common_translation.txt"
								},

						        "bPaginate": false,
						        "bScrollCollapse": true,
						        "bLengthChange": false,
						        "bInfo": false,
						    });
							digirisk("#' . $idTable . '").children("tfoot").remove();

							jQuery(".digi_delete_follow_up_line").unbind("click");
							jQuery(".digi_delete_follow_up_line").click(function(){
								if ( confirm(digi_html_accent_for_js("' . __('Etes vous s&ucirc;r de vouloir supprimer ce commentaire?', 'evarisk') . '")) ) {
									jQuery.post("' . admin_url('admin-ajax.php') . '", {
										"action":"digi_ajax_save_activite_follow",
										"digi_ajax_nonce":"' . wp_create_nonce("digi_ajax_save_activite_follow") . '",
										"specific_follow_up": jQuery(this).attr("id").replace("digi_delete_follow_up_line_", ""),
										"sub_action": "delete",
										"tableElement": "' . $tableElement . '",
										"idElement": "' . $idElement . '",
									}, function (response) {
										if ( response[0] == "ok" ) {
											jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('&Eacute;l&eacute;ment supprim&eacute; avec succ&eacute;s', 'evarisk') . '</strong></p>') . '");
											jQuery("#digi_ac_project_follow_up").load("' . admin_url('admin-ajax.php') . '", {action:"digi_ajax_load_activite_follow",digi_ajax_nonce:"' .  wp_create_nonce("digi_ajax_load_activite_follow") . '",tableElement:"' . $tableElement . '",idElement:"' . $idElement .'",follow_up_type:"follow_up"});

											if ( jQuery("#' . $follow_up_container . $tableElement . '_' . $idElement . '") && (response[1] != "") ) {
												jQuery("#' . $follow_up_container . $tableElement . '_' . $idElement . '").html(response[1]);
											}
										}
										else {
											jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La suppression de l\'&eacute;l&eacute;ment a &eacute;chou&eacute;e', 'evarisk') . '</strong></p>"') . '");
										}

										jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").show();
										jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").addClass("updated");
										setTimeout(function(){
											jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").removeClass("updated");
											jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").hide();
										},7500);
									}, "json");
								}
							});
							jQuery(".digi_edit_follow_up_line").unbind("click");
							jQuery(".digi_edit_follow_up_line").click(function(){
								if ( jQuery("#" + jQuery(this).attr("id") + "_folow_up_type").val() == "note") {
									jQuery("#digi_follow_up_update_box").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
										"post":"true",
										"table":"' . TABLE_ACTIVITE_SUIVI . '",
										"act":"load_follow_up_edition_form",
										"table_element": "' . $tableElement . '",
										"id_element": "' . $idElement . '",
										"follow_up_type": jQuery("#" + jQuery(this).attr("id") + "_folow_up_type").val(),
										"follow_up_2_edit": jQuery(this).attr("id").replace("digi_edit_follow_up_line_", ""),
									});
									jQuery("#digi_follow_up_update_box").dialog("open");
								}
								else {
									jQuery("#digi_project_follow_up_update_box").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
										"post":"true",
										"table":"' . TABLE_ACTIVITE_SUIVI . '",
										"act":"load_follow_up_edition_form",
										"table_element": "' . $tableElement . '",
										"id_element": "' . $idElement . '",
										"follow_up_type": jQuery("#" + jQuery(this).attr("id") + "_folow_up_type").val(),
										"follow_up_2_edit": jQuery(this).attr("id").replace("digi_edit_follow_up_line_", ""),
									});
									jQuery("#digi_project_follow_up_update_box").dialog("open");
									jQuery("#project_commentaire' . $tableElement . '' . $idElement . '_" + jQuery(this).attr("id").replace("digi_edit_follow_up_line_", "")).click();
								}
							});
						});
					</script>';

			$output .= evaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $scriptTableauSuiviModification);
		}

		return $output;
	}

	function tableauSuiviActivite1 ($tableElement, $idElement, $follow_up_type = 'note', $edition_button = true) {
		$listSuivi = suivi_activite::getSuiviActivite($tableElement, $idElement, $follow_up_type);
		$outputSuivi = '';

		if( !empty($listSuivi) ) {
			switch ($tableElement) {
				case TABLE_TACHE:
				case TABLE_ACTIVITE:
					$document_type_to_print = __('le plan d\'action', 'evarisk');
					$follow_up_container = 'project_follow_up_summary_';
				break;

				case TABLE_UNITE_TRAVAIL:
				case TABLE_UNITE_TRAVAIL:
				case TABLE_AVOIR_VALEUR:
					$saveButtonOuput = 'yes';
					$document_type_to_print = __('Le DUER', 'evarisk');
					$follow_up_container = 'digi_content_note_';
				break;
			}

			$idTable = 'tableauSuiviModification' . $tableElement . $idElement . $follow_up_type;


			if ( $follow_up_type == 'follow_up' ) {
				$titres[] = __('Date', 'evarisk');
				$titres[] = __('Personne', 'evarisk');
				$titres[] = __('Description', 'evarisk');
				$titres[] = __('Co&ucirc;t', 'evarisk');
			}
			else {
				$titres = array( '', __('Id.', 'evarisk'), );
				$titres[] = __('Suivi modifications', 'evarisk');
			}
			$titres[] = __('Impression', 'evarisk'); // sprintf( __('Publier dans %s', 'evarisk'), $document_type_to_print );
			if ( $edition_button ) {
				$titres[] = __('Actions', 'evarisk');
			}

			$classes = array( 'digi_suivi_modif_date_ajout_unformated', 'digi_suivi_identifier_col', );
			if ( $follow_up_type == 'follow_up' ) {
				$classes[] = 'digi_suivi_modif_elapsed_time_col';
				$classes[] = '';
				$classes[] = '';
				$classes[] = 'digi_suivi_modif_cost_col';
			}
			else {
				$classes[] = '';
			}
			$classes[] = 'digi_suivi_modif_export_col';
			if ( $edition_button ) {
				$classes[] = 'digi_suivi_modif_action_col';
			}

			unset($lignesDeValeurs);
			foreach ($listSuivi as $suivi) {
				unset($valeurs);
				$user_id_to_use = !empty($suivi->id_user_performer) ? $suivi->id_user_performer : $suivi->id_user;
				$user_info = get_userdata($user_id_to_use);
				$user_lastname = '';
				if ( (isset($user_info->user_lastname) && ($user_info->user_lastname != '')) ) {
					$user_lastname = $user_info->user_lastname;
				}
				$user_firstname = $user_info->user_nicename;
				if ( (isset($user_info->user_firstname) && ($user_info->user_firstname != '')) ) {
					$user_firstname = $user_info->user_firstname;
				}

				$valeurs[] = array('value' => $suivi->date_ajout);
				$valeurs[] = array('value' => ELEMENT_IDENTIFIER_C . $suivi->id);
				if ( $follow_up_type == 'follow_up' ) {
					$elapsed_time = $suivi->elapsed_time;
					$elapsed_time_hour = floor( $suivi->elapsed_time / 60 );
					$elapsed_time_hour = ($elapsed_time_hour < 10) ? '0' . $elapsed_time_hour : $elapsed_time_hour;
					$elapsed_time_minutes = $suivi->elapsed_time % 60;
					$elapsed_time_minutes = ($elapsed_time_minutes < 10) ? '0' . $elapsed_time_minutes : $elapsed_time_minutes;
					$valeurs[] = array('value' => sprintf(__('Le <b>%s</b> <br/>%s', 'evarisk'), mysql2date('d M Y (H:i:s)', $suivi->date_ajout, true), ((!empty($elapsed_time_hour) || !empty($elapsed_time_minutes)) ? sprintf(__('Dur&eacute;e %s H %s Minutes', 'evarisk'), (!empty($elapsed_time_hour) ? $elapsed_time_hour : 0), (!empty($elapsed_time_minutes) ? $elapsed_time_minutes : 0)) : '')));
					$valeurs[] = array('value' => $user_lastname . ' ' . $user_firstname);
					$valeurs[] = array('value' => stripslashes( $suivi->commentaire ));
					$valeurs[] = array('value' => $suivi->cost . '&euro;');
				}
				else {
					$valeurs[] = array('value' => sprintf(__('Le <b>%s</b>, <b>%s</b> dit <i>%s</i>', 'evarisk'), mysql2date('d M Y (H:i:s)', $suivi->date_ajout, true), $user_lastname . ' ' . $user_firstname, stripslashes( $suivi->commentaire )));
				}
				$valeurs[] = array('value' => (!empty($suivi->export) && ($suivi->export == 'yes')) ? __('Oui', 'evarisk')/* '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'veille-reponse.gif" alt="publish_in_doc" title="' . sprintf( __('Ce commentaire sera publi&eacute; dans %s', 'evarisk'), $document_type_to_print ) . '" />' */ : __('Non', 'evarisk'));
				if ( $edition_button ) {
					$valeurs[] = array('value' => '<input type="hidden" value="' . $suivi->follow_up_type . '" id="digi_edit_follow_up_line_' . $suivi->id . '_folow_up_type" /><img src="' . PICTO_EDIT . '" alt="' . __('Edit this comment', 'evarisk') . '" class="digi_edit_follow_up_line" id="digi_edit_follow_up_line_' . $suivi->id . '" /><img src="' . PICTO_DELETE . '" alt="' . __('Delete this comment', 'evarisk') . '" class="digi_delete_follow_up_line" id="digi_delete_follow_up_line_' . $suivi->id . '" />');
				}
				$lignesDeValeurs[] = $valeurs;
				$idLignes[] = $tableElement . $idElement . 'suiviModification';
			}

			$scriptTableauSuiviModification =
			'<script type="text/javascript">
				digirisk(document).ready(function() {
					digirisk("#' . $idTable . '").dataTable({
						"bInfo": false,
						"aaSorting": [[ 0, "desc" ]],
						"oLanguage":{
							"sUrl": "' . EVA_INC_PLUGIN_URL . 'js/dataTable/jquery.dataTables.common_translation.txt"
						},
						"aoColumnDefs": [
	                        { "bSearchable": false, "bVisible": false, "aTargets": [ 0 ] },
	                    ],
					});
					digirisk("#' . $idTable . '").children("tfoot").remove();

					jQuery(".digi_delete_follow_up_line").unbind("click");
					jQuery(".digi_delete_follow_up_line").click(function(){
						if ( confirm(digi_html_accent_for_js("' . __('Etes vous s&ucirc;r de vouloir supprimer ce commentaire?', 'evarisk') . '")) ) {
							jQuery.post("' . admin_url('admin-ajax.php') . '", {
								"action":"digi_ajax_save_activite_follow",
								"digi_ajax_nonce":"' . wp_create_nonce("digi_ajax_save_activite_follow") . '",
								"specific_follow_up": jQuery(this).attr("id").replace("digi_delete_follow_up_line_", ""),
								"sub_action": "delete",
								"tableElement": "' . $tableElement . '",
								"idElement": "' . $idElement . '",
							}, function (response) {
								if ( response[0] == "ok" ) {
									jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('&Eacute;l&eacute;ment supprim&eacute; avec succ&eacute;s', 'evarisk') . '</strong></p>') . '");
									jQuery("#digi_ac_project_follow_up").load("' . admin_url('admin-ajax.php') . '", {action:"digi_ajax_load_activite_follow",digi_ajax_nonce:"' .  wp_create_nonce("digi_ajax_load_activite_follow") . '",tableElement:"' . $tableElement . '",idElement:"' . $idElement .'",follow_up_type:"follow_up"});

									if ( jQuery("#' . $follow_up_container . $tableElement . '_' . $idElement . '") && (response[1] != "") ) {
										jQuery("#' . $follow_up_container . $tableElement . '_' . $idElement . '").html(response[1]);
									}
								}
								else {
									jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La suppression de l\'&eacute;l&eacute;ment a &eacute;chou&eacute;e', 'evarisk') . '</strong></p>"') . '");
								}

								jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").show();
								jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").addClass("updated");
								setTimeout(function(){
									jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").removeClass("updated");
									jQuery("#project_follow_up_message_' . $tableElement . $idElement . '").hide();
								},7500);
							}, "json");
						}
					});
					jQuery(".digi_edit_follow_up_line").unbind("click");
					jQuery(".digi_edit_follow_up_line").click(function(){
						if ( jQuery("#" + jQuery(this).attr("id") + "_folow_up_type").val() == "note") {
							jQuery("#digi_follow_up_update_box").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
								"post":"true",
								"table":"' . TABLE_ACTIVITE_SUIVI . '",
								"act":"load_follow_up_edition_form",
								"table_element": "' . $tableElement . '",
								"id_element": "' . $idElement . '",
								"follow_up_type": jQuery("#" + jQuery(this).attr("id") + "_folow_up_type").val(),
								"follow_up_2_edit": jQuery(this).attr("id").replace("digi_edit_follow_up_line_", ""),
							});
							jQuery("#digi_follow_up_update_box").dialog("open");
						}
						else {
							jQuery("#digi_project_follow_up_update_box").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
								"post":"true",
								"table":"' . TABLE_ACTIVITE_SUIVI . '",
								"act":"load_follow_up_edition_form",
								"table_element": "' . $tableElement . '",
								"id_element": "' . $idElement . '",
								"follow_up_type": jQuery("#" + jQuery(this).attr("id") + "_folow_up_type").val(),
								"follow_up_2_edit": jQuery(this).attr("id").replace("digi_edit_follow_up_line_", ""),
							});
							jQuery("#digi_project_follow_up_update_box").dialog("open");
							jQuery("#project_commentaire' . $tableElement . '' . $idElement . '_" + jQuery(this).attr("id").replace("digi_edit_follow_up_line_", "")).click();
						}
					});
				});
			</script>';

			$outputSuivi .= evaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $scriptTableauSuiviModification);
		}

		return $outputSuivi;
	}

	function digi_postbox_project($arguments) {
		$idElement = $arguments['idElement'];
		$tableElement = $arguments['tableElement'];

		if ( $idElement != null ) {
			switch ($tableElement) {
				case TABLE_TACHE:
					$tache = new EvaTask($idElement);
					$tache->load();

					$contenuInputEstimateStartDate = $tache->getStartDate();
					$contenuInputEstimateEndDate = $tache->getFinishDate();
					$contenuInputEstimateCost = $tache->getCost();
					$contenuInputRealCost = $tache->getreal_cost();
					$contenuInputRealStartDate = $tache->getreal_start_date();
					$contenuInputRealEndDate = $tache->getreal_end_date();
					$contenuInputPlannedTime = $tache->getplanned_time();
					$contenuInputElapsedTime = $tache->getelapsed_time();
					break;
				case TABLE_ACTIVITE:
					$activity = new EvaActivity($idElement);
					$activity->load();

					$contenuInputEstimateStartDate = $activity->getStartDate();
					$contenuInputEstimateEndDate = $activity->getFinishDate();
					$contenuInputEstimateCost = $activity->getCout();
					$contenuInputRealCost = $activity->getcout_reel();
					$contenuInputRealStartDate = $activity->getreal_start_date();
					$contenuInputRealEndDate = $activity->getreal_end_date();
					$contenuInputPlannedTime = $activity->getplanned_time();
					$contenuInputElapsedTime = $activity->getelapsed_time();
					break;
			}

			$start_date_status = $end_date_status = $time_status = $cost_status = '';
			$start_date_class_status = $end_date_class_status = $time_class_status = $cost_class_status = '';

			$start_date_label_status = $end_date_label_status = $time_label_status = $cost_label_status = '';
			$start_date_label_class_status = $end_date_label_class_status = $time_label_class_status = $cost_label_class_status = '';
			if ( ($contenuInputEstimateStartDate != '0000-00-00') && ( $contenuInputRealStartDate != '0000-00-00') ) {
				$date_to_compare = array($contenuInputEstimateStartDate, $contenuInputRealStartDate);

				if ( max($date_to_compare) == $contenuInputRealStartDate ) {
					$start_date_status = '<img src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'suivi_projet/calendrier_rouge_vs.png" />';
					$start_date_class_status = ' class="digi_alert" ';
				}
			}
			else if (($contenuInputEstimateStartDate == '0000-00-00') && ( $contenuInputRealStartDate != '0000-00-00')) {
				$start_date_label_status = '<img src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'suivi_projet/calendrier_rouge_xs.png" style="vertical-align:middle" /> ';
				$start_date_label_class_status = ' class="digi_alert" ';
			}
			if ( ($contenuInputEstimateEndDate != '0000-00-00') && ( $contenuInputRealEndDate != '0000-00-00') ) {
				$date_to_compare = array($contenuInputEstimateEndDate, $contenuInputRealEndDate);

				if ( max($date_to_compare) == $contenuInputRealEndDate ) {
					$end_date_status = '<img src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'suivi_projet/calendrier_rouge_vs.png" />';
					$end_date_class_status = ' class="digi_alert" ';
				}
			}
			else if (($contenuInputEstimateEndDate == '0000-00-00') && ( $contenuInputRealEndDate != '0000-00-00')) {
				$end_date_label_status = '<img src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'suivi_projet/calendrier_rouge_xs.png" style="vertical-align:middle" /> ';
				$end_date_label_class_status = ' class="digi_alert" ';
			}

			if ( !empty($contenuInputEstimateCost) && !empty($contenuInputRealCost) && ($contenuInputEstimateCost < $contenuInputRealCost) ) {
				$cost_status = '<img src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'suivi_projet/euros_rouge_vs.png" />';
				$time_class_status = ' class="digi_alert" ';
			}
			else if ( empty($contenuInputEstimateCost) && !empty($contenuInputRealCost) ) {
				$cost_label_status = '<img src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'suivi_projet/euros_rouge_xs.png" style="vertical-align:middle" /> ';
				$cost_label_class_status = ' class="digi_alert" ';
			}
			if (  !empty($contenuInputPlannedTime) && !empty($contenuInputElapsedTime) && ($contenuInputPlannedTime < $contenuInputElapsedTime) ) {
				$time_status = '<img src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'suivi_projet/chrono_rouge_vs.png" />';
				$cost_class_status = ' class="digi_alert" ';
			}
			else if ( empty($contenuInputPlannedTime) && !empty($contenuInputElapsedTime) ) {
				$time_label_status = '<img src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'suivi_projet/chrono_rouge_xs.png" style="vertical-align:middle" /> ';
				$time_label_class_status = ' class="digi_alert" ';
			}

			$grise = false;
		}
		else {
			$start_date_status = '';
			$end_date_status = '';
			$tim_status = '';
			$cost_status = '';
			$contenuInputEstimateStartDate = '';
			$contenuInputEstimateEndDate = '';
			$contenuInputEstimateCost = '';
			$contenuInputRealCost = '';
			$contenuInputRealStartDate = '';
			$contenuInputRealEndDate = '';
			$contenuInputPlannedTime = '';
			$contenuInputElapsedTime = '';

			$estimate_start_date = $estimate_end_date = $planned_time = $estimate_cost = '';
			$start_date_status = $end_date_status = $time_status = $cost_status = '';
			$start_date_class_status = $end_date_class_status = $time_class_status = $cost_class_status = '';

			$start_date_label_status = $end_date_label_status = $time_label_status = $cost_label_status = '';
			$start_date_label_class_status = $end_date_label_class_status = $time_label_class_status = $cost_label_class_status = '';
		}

		switch ($tableElement) {
			case TABLE_TACHE:
				$waiting_for_worker_txt = __('En attente de pointage sur les sous-t&acirc;ches', 'evarisk');
				break;
			case TABLE_ACTIVITE:
				$waiting_for_worker_txt = __('En attente de pointage', 'evarisk');
				break;
			default:
				$waiting_for_worker_txt = __('En attente de pointage', 'evarisk');
				break;
		}

		/*	Sub-Task start date		*/
		$contenuAideTitre = "";
		$id = "estimate_start_date";
		$label = '<label for="' . $id . '"' . $start_date_label_class_status . ' >' . ucfirst(__("Date de d&eacute;but pr&eacute;vue", 'evarisk')) . '</label>';
		$labelInput = '';
		$nomChamps = "estimate_start_date";
		$grise = false;
		switch ( $tableElement ) {
			case TABLE_TACHE:
				$estimate_start_date = $label . (!empty($contenuInputEstimateStartDate) && ($contenuInputEstimateStartDate != '0000-00-00') ? '<div class="digi_action_estimate_start_date" >' . EvaDisplayInput::afficherInput('text', $id, $contenuInputEstimateStartDate, $contenuAideTitre, $labelInput, $nomChamps, $grise, true, 255, '', 'date', '', '', '', true, '7', ' disabled="disabled"') . '</div>' : '<br/><span style="font-style: italic; margin-left: 10px;" >' . __('En attente d\'informations des sous-t&acirc;ches', 'evarisk') . '</span>');
				break;
			case TABLE_ACTIVITE:
				$nomChamps = "date_debut";
				$estimate_start_date = $label . ' : <span class="fieldInfo pointer put_today_date" id="put_today_date_' . $id . '" >' . __('Aujourd\'hui', 'evarisk') . '</span><div class="digi_action_estimate_start_date" >' . EvaDisplayInput::afficherInput('text', $id, $contenuInputEstimateStartDate, '', $labelInput, $nomChamps, $grise, true, 255, '', 'date', '', '', '', true, '4') . '</div>';
				break;
		}

		/*	Sub-Task end date */
		$contenuAideTitre = "";
		$id = "estimate_end_date";
		$label = '<label for="' . $id . '"' . $end_date_label_class_status . ' >' . ucfirst(__("Date de fin pr&eacute;vue", 'evarisk')) . '</label>';
		$labelInput = '';
		$nomChamps = "estimate_end_date";
		$grise = false;
		switch ( $tableElement ) {
			case TABLE_TACHE:
				$estimate_end_date = $label . (!empty($contenuInputEstimateEndDate) && ($contenuInputEstimateEndDate != '0000-00-00') ? '<div class="digi_action_estimate_end_date" >' . EvaDisplayInput::afficherInput('text', $id, $contenuInputEstimateEndDate, $contenuAideTitre, $labelInput, $nomChamps, $grise, true, 255, '', 'date', '', '', '', true, '10', ' disabled="disabled"') . '</div>' : '<br/><span style="font-style: italic; margin-left: 10px;" >' . __('En attente d\'informations des sous-t&acirc;ches', 'evarisk') . '</span>');
				break;
			case TABLE_ACTIVITE:
				$nomChamps = "date_fin";
				$estimate_end_date = $label . ' : <span class="fieldInfo pointer put_today_date" id="put_today_date_' . $id . '" >' . __('Aujourd\'hui', 'evarisk') . '</span><div class="digi_action_estimate_end_date" >' . EvaDisplayInput::afficherInput('text', $id, $contenuInputEstimateEndDate, '', $labelInput, $nomChamps, $grise, true, 255, '', 'date', '', '', '', true, '5') . '</div>';
				break;
		}

		/*	Sub-Task start date		*/
		$contenuAideTitre = "";
		$id = "real_start_date";
		$label = '<label for="' . $id . '" >' . ucfirst(__("Date de d&eacute;but r&eacute;elle", 'evarisk')) . '</label>';
		$labelInput = '';
		$nomChamps = "real_start_date";
		$real_start_date = $label . (!empty($contenuInputRealStartDate) && ($contenuInputRealStartDate != '0000-00-00') ? '<div class="digi_action_estimate_start_date" >' . EvaDisplayInput::afficherInput('text', $id, $contenuInputRealStartDate, $contenuAideTitre, $labelInput, $nomChamps, $grise, true, 255, '', 'date', '', '', '', true, '19', ' disabled="disabled"') . '</div>' : '<br/><span style="font-style: italic; margin-left: 10px;" >' . $waiting_for_worker_txt . '</span>');

		/*	Sub-Task end date */
		$contenuAideTitre = "";
		$id = "real_end_date";
		$label = '<label for="' . $id . '" >' . ucfirst(__("Date de fin r&eacute;elle", 'evarisk')) . '</label>';
		$labelInput = '';
		$nomChamps = "real_end_date";
		$real_end_date = $label . (!empty($contenuInputRealEndDate) && ($contenuInputRealEndDate != '0000-00-00') ? '<div class="digi_action_estimate_end_date" >' . EvaDisplayInput::afficherInput('text', $id, $contenuInputRealEndDate, $contenuAideTitre, $labelInput, $nomChamps, $grise, true, 255, '', 'date', '', '', '', true, '22', ' disabled="disabled"') . '</div>' : '<br/><span style="font-style: italic; margin-left: 10px;" >' . $waiting_for_worker_txt . '</span>');

		/**	Sub-Task cost */
		/**	Expected cost	*/
		$contenuInputCout = "";
		$id = "estimate_cost";
		$label = '<label for="' . $id . '"' . $cost_label_class_status . ' >' . __("Co&ucirc;t pr&eacute;vu", 'evarisk') . '</label> :';
		$labelInput = '';
		$nomChamps = "estimate_cost";
		$grise = false;
		switch ( $tableElement ) {
			case TABLE_TACHE:
				$estimate_cost = $label . (!empty($contenuInputEstimateCost) ? EvaDisplayInput::afficherInput('text', $id, $contenuInputEstimateCost, $contenuInputCout, $labelInput, $nomChamps, $grise, true, 255, '', '', '', '', '', true, '16', ' disabled="disabled"') : '<br/><span style="font-style: italic; margin-left: 10px;" >' . __('En attente d\'informations des sous-t&acirc;ches', 'evarisk') . '</span>');
				break;
			case TABLE_ACTIVITE:
				$nomChamps = "cout";
				$estimate_cost = $label . EvaDisplayInput::afficherInput('text', $id, $contenuInputEstimateCost, '', $labelInput, $nomChamps, $grise, true, 255, '', '', '', '', '', true, '10');
				break;
		}

		/**	Real cost	*/
		$contenuInputCout = "";
		$id = "real_cost";
		$label = '<label for="' . $id . '" >' . __("Co&ucirc;t r&eacute;el", 'evarisk') . '</label> :';
		$labelInput = '';
		$nomChamps = "real_cost";
		$real_cost = $label . (!empty($contenuInputRealCost) ? EvaDisplayInput::afficherInput('text', $id, $contenuInputRealCost, $contenuInputCout, $labelInput, $nomChamps, $grise, true, 255, '', '', '', '', '', true, '30', ' disabled="disabled"') : '<br/><span style="font-style: italic; margin-left: 10px;" >' . $waiting_for_worker_txt . '</span>');

		/**	Sub-Task time */
		/**	Expected time	*/
		$contenuInputAidePlannedTime = "";
		$id = "planned_time_hour";
		$label = '<label for="' . $id . '"' . $time_label_class_status . ' >' . __("Temps pr&eacute;vu", 'evarisk') . '</label> :';
		$labelInput = '';
		$nomChamps = "planned_time";
		$planned_time_hour = $planned_time_minutes = 0;
		if ( !empty($contenuInputPlannedTime) ) {
			$planned_time_hour = floor($contenuInputPlannedTime / 60);
			$planned_time_minutes = ($contenuInputPlannedTime % 60);
		}
		switch ( $tableElement ) {
			case TABLE_TACHE:
				$input_hour = '<input type="text" name="planned_time[hour]" value="' . ($planned_time_hour < 10 ? '0' . $planned_time_hour : $planned_time_hour) . '" id="planned_time_hour" maxlength="255" tabindex="13" style="width:15%;" disabled="disabled" />';
				$input_minute = '<input type="text" name="planned_time[minutes]" value="' . ($planned_time_minutes < 10 ? '0' . $planned_time_minutes : $planned_time_minutes) . '" id="planned_time_minutes" maxlength="255" tabindex="14" style="width:15%;" disabled="disabled" />';
				$planned_time = $label . (!empty($contenuInputPlannedTime) ? '<div>' . $input_hour . ' ' . __('H', 'evarisk') . ' ' . $input_minute . ' ' . __('Minutes', 'evarisk') . '</div>' : '<br/><span style="font-style: italic; margin-left: 10px;" >' . __('En attente d\'informations des sous-t&acirc;ches', 'evarisk') . '</span>');
				break;
			case TABLE_ACTIVITE:
				$input_hour = '<input type="text" name="planned_time[hour]" value="' . ($planned_time_hour < 10 ? '0' . $planned_time_hour : $planned_time_hour) . '" id="planned_time_hour" maxlength="255" tabindex="8" style="width:15%;" /><script type="text/javascript" >digirisk(document).ready(function(){ jQuery("#planned_time_hour").keypress(function(event) { if (event.which && (event.which < 48 || event.which >57) && event.which != 8) { event.preventDefault(); } }); });</script>';
				$input_minute = '<input type="text" name="planned_time[minutes]" value="' . ($planned_time_minutes < 10 ? '0' . $planned_time_minutes : $planned_time_minutes) . '" id="planned_time_minutes" maxlength="255" tabindex="9" style="width:15%;" /><script type="text/javascript" >digirisk(document).ready(function(){ jQuery("#planned_time_minutes").keypress(function(event) { if (event.which && (event.which < 48 || event.which >57) && event.which != 8) { event.preventDefault(); } }); });</script>';
				$planned_time = $label . '<div class="clear" ></div>' . $input_hour . ' ' . __('H', 'evarisk') . ' ' . $input_minute . ' ' . __('Minutes', 'evarisk');
				break;
		}

		/**	Real time	*/
		$contenuInputAideElapsedTime = "";
		$id = "elapsed_time";
		$label = '<label for="' . $id . '" >' . __("Temps r&eacute;el", 'evarisk') . '</label> :';
		$labelInput = '';
		$nomChamps = "elapsed_time";
		$elapsed_time_hour = $elapsed_time_minutes = 0;
		if ( !empty($contenuInputElapsedTime) ) {
			$elapsed_time_hour = floor($contenuInputElapsedTime / 60);
			$elapsed_time_minutes = ($contenuInputElapsedTime % 60);
		}
		$input_hour = '<input type="text" name="elapsed_time[hour]" value="' . ($elapsed_time_hour < 10 ? '0' . $elapsed_time_hour : $elapsed_time_hour) . '" id="elapsed_time_hour" maxlength="255" tabindex="25" style="width:15%;" disabled="disabled" /><script type="text/javascript" >digirisk(document).ready(function(){ jQuery("#elapsed_time_hour").keypress(function(event) { if (event.which && (event.which < 48 || event.which >57) && event.which != 8) { event.preventDefault(); } }); });</script>';
		$input_minute = '<input type="text" name="elapsed_time[minutes]" value="' . ($elapsed_time_minutes < 10 ? '0' . $elapsed_time_minutes : $elapsed_time_minutes) . '" id="elapsed_time_minutes" maxlength="255" tabindex="26" style="width:15%;" disabled="disabled" /><script type="text/javascript" >digirisk(document).ready(function(){ jQuery("#elapsed_time_minutes").keypress(function(event) { if (event.which && (event.which < 48 || event.which >57) && event.which != 8) { event.preventDefault(); } }); });</script>';
		$elapsed_time = $label . (!empty($contenuInputElapsedTime) ? '<div>' . $input_hour . ' ' . __('H', 'evarisk') . ' ' . $input_minute . ' ' . __('Minutes', 'evarisk') . '</div>' : '<br/><span style="font-style: italic; margin-left: 10px;" >' . $waiting_for_worker_txt . '</span>');

		echo '
<div class="clear" ></div>
<div id="project_follow_up_message_' . $tableElement . $idElement . '" class="hide" ></div>
<div class="project_follow_up_container" id="project_follow_up_container' . $tableElement . $idElement . '" >
	<table style="width:100%;" >
		<tr>
			<td></td>
			<td>' . __('Pr&eacute;visionnel', 'evarisk') . '</td>
			<td></td>
			<td>' . __('R&eacute;el', 'evarisk') . '</td>
		</tr>
		<tr>
			<td>' . $start_date_label_status . '</td>
			<td style="padding: 0 0 0 12px;"' . $start_date_class_status . ' >' . $estimate_start_date . '</td>
			<td>' . $start_date_status . '</td>
			<td style="padding: 0 0 0 12px;"' . $start_date_class_status . ' >' . $real_start_date . '</td>
		</tr>
		<tr>
			<td>' . $end_date_label_status . '</td>
			<td style="padding: 0 0 0 12px;"' . $end_date_class_status . ' >' . $estimate_end_date . '</td>
			<td>' . $end_date_status . '</td>
			<td style="padding: 0 0 0 12px;"' . $end_date_class_status . ' >' . $real_end_date . '</td>
		</tr>
		<tr>
			<td>' . $time_label_status . '</td>
			<td style="padding: 0 0 0 12px;"' . $time_class_status . ' >' . $planned_time . '</td>
			<td>' . $time_status . '</td>
			<td style="padding: 0 0 0 12px;"' . $time_class_status . ' >' . $elapsed_time . '</td>
		</tr>
		<tr>
			<td>' . $cost_label_status . '</td>
			<td style="padding: 0 0 0 12px;"' . $cost_class_status . ' >' . $estimate_cost . '</td>
			<td>' . $cost_status . '</td>
			<td style="padding: 0 0 0 12px;"' . $cost_class_status . ' >' . $real_cost . '</td>
		</tr>
	</table>
	<script type="text/javascript" >
		digirisk(document).ready(function() {
			jQuery(".put_today_date").click(function(){
				jQuery("#" + jQuery(this).attr("id").replace("put_today_date_", "")).val("' . substr( current_time('mysql', 0), 0, 10 ) . '");
			});
		});
	</script>
	<div class="clear" ></div>
</div>';
	}
}