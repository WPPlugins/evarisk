<?php

class actionsCorrectives {

	function save_action_sheet( $tableElement, $idElement ) {
		global $wpdb;

		switch ( $tableElement ) {
			case TABLE_TACHE:
				$identifier = ELEMENT_IDENTIFIER_T;
				break;
			case TABLE_ACTIVITE:
				$identifier = ELEMENT_IDENTIFIER_ST;
				break;
		}

		/**	Get current element document last revision	*/
		$query = $wpdb->prepare( "SELECT COUNT(DM.id) AS revision FROM " . TABLE_GED_DOCUMENTS_META . " AS DM INNER JOIN " . TABLE_GED_DOCUMENTS . " AS D ON (D.id = DM.document_id) AND D.table_element = %s AND D.id_element = %d AND DM.meta_key = %s", array( $tableElement, $idElement, '_digi_doc_revision') );
		$revision = $wpdb->get_var( $query ) + 1;

		/**	Get current element main definition from main table	*/
		$query = $wpdb->prepare( "SELECT * FROM " . $tableElement . " WHERE id = %d", $idElement );
		$current_element_main_definition = $wpdb->get_row( $query );

		/**	Save document main definition	*/
		$doc_name = sanitize_file_name( str_replace('-', '', substr(current_time( 'mysql', 0 ), 0, 10) ) . '_' . $identifier . $idElement . '_' . remove_accents($current_element_main_definition->nom) . '_V' . $revision) . '.odt';
		$doc_def = array(
				'status' => 'valid',
				'parDefaut' => 'non',
				'dateCreation' => current_time( 'mysql', 0 ),
				'idCreateur' => get_current_user_id(),
				'id_element' => $idElement,
				'table_element' => $tableElement,
				'categorie' => 'printed_fiche_action',
				'nom' => $doc_name,
				'chemin' => 'planDActions/' . $tableElement . '/' . $idElement . '/',
		);
		$wpdb->insert( TABLE_GED_DOCUMENTS, $doc_def );
		$new_document_id = $wpdb->insert_id;

		/**	Save document revision */
		$wpdb->insert( TABLE_GED_DOCUMENTS_META, array( 'document_id' => $new_document_id, 'meta_key' => '_digi_doc_revision', 'meta_value' => $revision) );
		/**	Save document model	*/
		$wpdb->insert( TABLE_GED_DOCUMENTS_META, array( 'document_id' => $new_document_id, 'meta_key' => '_digi_doc_model', 'meta_value' => $_POST['model_id']) );

		/**	Save current element main informations for history	*/
		$main_infos = array();
		if ( !empty($current_element_main_definition) ) {
			/**	Main informations about the task	*/
			$main_infos['referenceAction'] = $identifier . $current_element_main_definition->id;
			if ( !empty($current_element_main_definition->idResponsable) ) {
				$responsible_infos = get_userdata( $current_element_main_definition->idResponsable );
				$main_infos['responsableAction'] = htmlentities($responsible_infos->display_name, ENT_QUOTES, 'UTF-8');
			}
			else {
				$main_infos['responsableAction'] = __('Aucun utilisateur n\'a &eacute;t&eacute; choisi comme responsable', 'evarisk');
			}
			$creator_infos = get_userdata( $current_element_main_definition->idCreateur );
			$main_infos['personneAjoutAction'] = htmlentities($creator_infos->display_name, ENT_QUOTES, 'UTF-8');
			$main_infos['nomAction'] = htmlentities($current_element_main_definition->nom, ENT_QUOTES, 'UTF-8');
			$main_infos['descriptionAction'] = htmlentities($current_element_main_definition->description, ENT_QUOTES, 'UTF-8');
			$main_infos['dateAjoutAction'] = mysql2date('d F Y', $current_element_main_definition->firstInsert, true);

			/**	Build element hierarchy	*/
			switch ( $tableElement ) {
				case TABLE_TACHE:
					$main_infos['hierarchieAction'] = '';

					$racine = Arborescence::getRacine(TABLE_TACHE, " id='" . $current_element_main_definition->id . "' ");
					$parents = Arborescence::getAncetre(TABLE_TACHE, $racine);
					if ( !empty( $parents ) ) {
						$spacer = "";
						foreach ( $parents as $parent ) {
							if ( ($parent->nom != __('Tache Racine', 'evarisk')) && ( $current_element_main_definition->id != $parent->id ) ) {
								$main_infos['hierarchieAction'] .= htmlentities(ELEMENT_IDENTIFIER_T . $parent->id . ' - ' . $parent->nom, ENT_NOQUOTES, 'UTF-8') . "
" . $spacer;
								$spacer = "    ";
							}
						}
					}
					break;
				case TABLE_ACTIVITE:
					$main_infos['hierarchieAction'] = '';

					$query = $wpdb->prepare( "SELECT nom FROM " . TABLE_TACHE . " WHERE id = %d", $current_element_main_definition->id_tache );
					$current_element_parent_task = $wpdb->get_row( $query );
					$task_name = $current_element_parent_task->nom;

					$racine = Arborescence::getRacine(TABLE_TACHE, " id='" . $current_element_main_definition->id_tache . "' ");
					$parents = Arborescence::getAncetre(TABLE_TACHE, $racine);
					if ( !empty( $parents ) ) {
						$spacer = "";
						foreach ( $parents as $parent ) {
							if ( $parent->nom != __('Tache Racine', 'evarisk') ) {
								$main_infos['hierarchieAction'] .= htmlentities(ELEMENT_IDENTIFIER_T . $parent->id . ' - ' . $parent->nom, ENT_NOQUOTES, 'UTF-8') . "
" . $spacer;
								$spacer = "    ";
							}
						}
					}

					$main_infos['hierarchieAction'] .= $spacer . htmlentities(ELEMENT_IDENTIFIER_T . $current_element_main_definition->id_tache . ' - ' . $task_name, ENT_NOQUOTES, 'UTF-8');
					break;
			}

			/** Get the main picture for current element	*/
			$defaultPicture = evaPhoto::getMainPhoto($tableElement, $idElement);
			$defaultPictureToSet = '';
			if ($defaultPicture != 'error') {
				$main_infos['photoPrincipale'] = $defaultPicture;
			}
			else {
				$main_infos['photoPrincipale'] = 'noDefaultPicture';
			}

			/**	Informations about task planning	*/
			$main_infos['avancementAction'] = actionsCorrectives::check_progression_status_for_output( $current_element_main_definition->ProgressionStatus ) . ' (' . $current_element_main_definition->avancement . '%)';
			$main_infos['dateDebutActionPrevu'] = !empty($current_element_main_definition->dateDebut) && ($current_element_main_definition->dateDebut != '0000-00-00') ? mysql2date('d/m/Y', $current_element_main_definition->dateDebut, true) : '-';
			$main_infos['dateDebutActionReel'] = !empty($current_element_main_definition->real_start_date) && ($current_element_main_definition->real_start_date != '0000-00-00') ? mysql2date('d/m/Y', $current_element_main_definition->real_start_date, true) : '-';
			$main_infos['dateFinActionPrevu'] = !empty($current_element_main_definition->dateFin) && ($current_element_main_definition->dateFin != '0000-00-00') ? mysql2date('d/m/Y', $current_element_main_definition->dateFin, true) : '-';
			$main_infos['dateFinActionReel'] = !empty($current_element_main_definition->real_end_date) && ($current_element_main_definition->real_end_date != '0000-00-00') ? mysql2date('d/m/Y', $current_element_main_definition->real_end_date, true) : '-';
			$planned_time = $current_element_main_definition->planned_time;
			$planned_time_hour = floor( $current_element_main_definition->planned_time / 60 );
			$planned_time_hour = ($planned_time_hour != 0) && ($planned_time_hour < 10) ? '0' . $planned_time_hour : $planned_time_hour;
			$planned_time_minutes = $current_element_main_definition->planned_time % 60;
			$planned_time_minutes = ($planned_time_hour != 0) && ($planned_time_minutes < 10) ? '0' . $planned_time_minutes : $planned_time_minutes;
			$main_infos['dureeActionPrevu'] = ((!empty($planned_time_hour) || !empty($planned_time_minutes)) ? sprintf(__('%s H %s Minutes', 'evarisk'), (!empty($planned_time_hour) ? $planned_time_hour : 0), (!empty($planned_time_minutes) ? $planned_time_minutes : 0)) : '-');
			$elapsed_time = $current_element_main_definition->elapsed_time;
			$elapsed_time_hour = floor( $current_element_main_definition->elapsed_time / 60 );
			$elapsed_time_hour = !empty($elapsed_time_hour) && ($elapsed_time_hour < 10) ? '0' . $elapsed_time_hour : $elapsed_time_hour;
			$elapsed_time_minutes = $current_element_main_definition->elapsed_time % 60;
			$elapsed_time_minutes = !empty($elapsed_time_hour) && ($elapsed_time_minutes < 10) ? '0' . $elapsed_time_minutes : $elapsed_time_minutes;
			$main_infos['dureeActionReel'] = ((!empty($elapsed_time_hour) || !empty($elapsed_time_minutes)) ? sprintf(__('%s H %s Minutes', 'evarisk'), (!empty($elapsed_time_hour) ? $elapsed_time_hour : 0), (!empty($elapsed_time_minutes) ? $elapsed_time_minutes : 0)) : '-');
			$main_infos['coutActionPrevu'] = !empty($current_element_main_definition->cout) ? $current_element_main_definition->cout : '-';
			$main_infos['coutActionReel'] = ($tableElement == TABLE_TACHE) ? (!empty($current_element_main_definition->real_cost) ? $current_element_main_definition->real_cost : '-') : (!empty($current_element_main_definition->cout_reel) ? $current_element_main_definition->cout_reel : '-');

			/**	Informations about task follow up	*/
			$main_infos['projectFollowUp'] = array();
			$main_infos['noProjectFollowUp'] = array();
			$has_follow_up = false;
			$follow_up_list = suivi_activite::getSuiviActivite($tableElement, $idElement, 'follow_up');
			if ( !empty($follow_up_list) ) {
				foreach ( $follow_up_list as $key => $comment ) {
					if ( !empty( $comment->export ) && (( true === $comment->export ) || ($comment->export != 'no')) ) {
						$has_follow_up = true;
						$main_infos[ 'projectFollowUp' ][$key][ 'idActionFollowUp' ] = ELEMENT_IDENTIFIER_C . $comment->id;
						$main_infos[ 'projectFollowUp' ][$key][ 'dateFollowUpAction' ] = !empty($comment->date_ajout) && ( $comment->date_ajout != '0000-00-00 00:00:00' ) ? mysql2date( 'd/m/Y H:i', $comment->date_ajout, true ) : '-';

						$user_id_to_use = !empty($comment->id_user_performer) ? $comment->id_user_performer : $comment->id_user;
						$user_info = get_userdata($user_id_to_use);
						$user_lastname = '';
						if ( (isset($user_info->user_lastname) && ($user_info->user_lastname != '')) ) {
							$user_lastname = $user_info->user_lastname;
						}
						$user_firstname = $user_info->user_nicename;
						if ( (isset($user_info->user_firstname) && ($user_info->user_firstname != '')) ) {
							$user_firstname = $user_info->user_firstname;
						}
						$main_infos[ 'projectFollowUp' ][$key][ 'userFollowUpAction' ] = !empty($user_lastname) || !empty($user_firstname) ? $user_lastname . ' ' . $user_firstname : ($user_info->display_name);
						$main_infos[ 'projectFollowUp' ][$key][ 'descriptionFollowUpAction' ] = htmlentities($comment->commentaire, ENT_NOQUOTES, 'UTF-8');
						$elapsed_time = $comment->elapsed_time;
						$elapsed_time_hour = floor( $comment->elapsed_time / 60 );
						$elapsed_time_hour = !empty($elapsed_time_hour) && ($elapsed_time_hour < 10) ? '0' . $elapsed_time_hour : $elapsed_time_hour;
						$elapsed_time_minutes = $comment->elapsed_time % 60;
						$elapsed_time_minutes = !empty($elapsed_time_hour) && ($elapsed_time_minutes < 10) ? '0' . $elapsed_time_minutes : $elapsed_time_minutes;
						$main_infos[ 'projectFollowUp' ][$key][ 'timeFollowUpAction' ] = ((!empty($elapsed_time_hour) || !empty($elapsed_time_minutes)) ? sprintf(__('%s H %s Minutes', 'evarisk'), (!empty($elapsed_time_hour) ? $elapsed_time_hour : 0), (!empty($elapsed_time_minutes) ? $elapsed_time_minutes : 0)) : '-');
						$main_infos[ 'projectFollowUp' ][$key][ 'costFollowUpAction' ] = !empty($comment->cost) || ($comment->cost == 0) ? $comment->cost : '-';

						$main_infos[ 'projectFollowUp' ][$key][ 'dateModifFollowUpAction' ] = !empty($comment->date) && ( $comment->date != '0000-00-00 00:00:00' ) ? mysql2date( 'd/m/Y H:i', $comment->date, true ) : '-';
						$main_infos[ 'projectFollowUp' ][$key][ 'dateAutoFollowUpAction' ] = !empty($comment->date_modification) && ( $comment->date_modification != '0000-00-00 00:00:00' ) ? mysql2date( 'd/m/Y H:i', $comment->date_modification, true ) : '-';

						$user_info = get_userdata($comment->id_user);
						$user_lastname = '';
						if ( (isset($user_info->user_lastname) && ($user_info->user_lastname != '')) ) {
							$user_lastname = $user_info->user_lastname;
						}
						$user_firstname = $user_info->user_nicename;
						if ( (isset($user_info->user_firstname) && ($user_info->user_firstname != '')) ) {
							$user_firstname = $user_info->user_firstname;
						}
						$main_infos[ 'projectFollowUp' ][$key][ 'creatorFollowUp' ] = !empty($user_lastname) || !empty($user_firstname) ? $user_lastname . ' ' . $user_firstname : ($user_info->display_name);
					}
				}
			}
			if ( !$has_follow_up ) {
				$main_infos['noProjectFollowUp'] = array( 'empty' );
			}

			/**	Informations about task free comments	*/
			$main_infos['actionsNotes'] = array();
			$main_infos['noNotes'] = array();
			$notes_list = suivi_activite::getSuiviActivite($tableElement, $idElement);
			$has_notes = false;
			if ( !empty($notes_list) ) {
				foreach ( $notes_list as $key => $comment ) {
					if ( !empty( $comment->export ) && (( true === $comment->export ) || ($comment->export != 'no')) ) {
						$has_notes = true;
						$main_infos[ 'actionsNotes' ][$key][ 'idNote' ] = ELEMENT_IDENTIFIER_C . $comment->id;
						$main_infos[ 'actionsNotes' ][$key][ 'dateNote' ] = !empty($comment->date_ajout) && ( $comment->date_ajout != '0000-00-00 00:00:00' ) ? mysql2date( 'd/m/Y H:i', $comment->date_ajout, true ) : '-';

						$user_id_to_use = !empty($comment->id_user_performer) ? $comment->id_user_performer : $comment->id_user;
						$user_info = get_userdata($user_id_to_use);
						$user_lastname = '';
						if ( (isset($user_info->user_lastname) && ($user_info->user_lastname != '')) ) {
							$user_lastname = $user_info->user_lastname;
						}
						$user_firstname = $user_info->user_nicename;
						if ( (isset($user_info->user_firstname) && ($user_info->user_firstname != '')) ) {
							$user_firstname = $user_info->user_firstname;
						}
						$main_infos[ 'actionsNotes' ][$key][ 'userNote' ] = !empty($user_lastname) || !empty($user_firstname) ? $user_lastname . ' ' . $user_firstname : ($user_info->display_name);
						$main_infos[ 'actionsNotes' ][$key][ 'contenuNote' ] = htmlentities($comment->commentaire, ENT_NOQUOTES, 'UTF-8');
						$main_infos[ 'actionsNotes' ][$key][ 'dateModifNote' ] = !empty($comment->date) && ( $comment->date != '0000-00-00 00:00:00' ) ? mysql2date( 'd/m/Y H:i', $comment->date, true ) : '-';
						$main_infos[ 'actionsNotes' ][$key][ 'dateAutoNote' ] = !empty($comment->date_modification) && ( $comment->date_modification != '0000-00-00 00:00:00' ) ? mysql2date( 'd/m/Y H:i', $comment->date_modification, true ) : '-';

						$user_info = get_userdata($comment->id_user);
						$user_lastname = '';
						if ( (isset($user_info->user_lastname) && ($user_info->user_lastname != '')) ) {
							$user_lastname = $user_info->user_lastname;
						}
						$user_firstname = $user_info->user_nicename;
						if ( (isset($user_info->user_firstname) && ($user_info->user_firstname != '')) ) {
							$user_firstname = $user_info->user_firstname;
						}
						$main_infos[ 'actionsNotes' ][$key][ 'creatorNote' ] = !empty($user_lastname) || !empty($user_firstname) ? $user_lastname . ' ' . $user_firstname : ($user_info->display_name);
					}
				}
			}
			if ( !$has_notes ) {
				$main_infos['noNotes'] = array( 'empty' );
			}

			/**	Informations task's associated user	*/
			$main_infos['utilisateurAffecteAction'] = array();
			$main_infos['aucunUtilisateurAffecte'] = array();
			$utilisateursLies = evaUserLinkElement::getAffectedUser($tableElement, $idElement, "'valid'");
			if ( !empty( $utilisateursLies ) ) {
				foreach( $utilisateursLies as $index => $utilisateur ){
					$main_infos['utilisateurAffecteAction'][ $index ][ 'identifiantUtilisateur' ] = ELEMENT_IDENTIFIER_U . $utilisateur->id_user;
					$currentUser = evaUser::getUserInformation( $utilisateur->id_user );
					$main_infos['utilisateurAffecteAction'][ $index ][ 'nomUtilisateur' ] = !empty( $currentUser[ $utilisateur->id_user ]['user_lastname'] ) ? $currentUser[ $utilisateur->id_user ]['user_lastname'] : 'NC';
					$main_infos['utilisateurAffecteAction'][ $index ][ 'prenomUtilisateur' ] = !empty( $currentUser[ $utilisateur->id_user ]['user_firstname'] ) ? $currentUser[ $utilisateur->id_user ]['user_firstname'] : 'NC';
					foreach ( $currentUser[ $utilisateur->id_user ] as $key => $value ) {
						$main_infos['utilisateurAffecteAction'][ $index ][ $key ] = $value;
					}

					$main_infos['utilisateurAffecteAction'][ $index ][ 'dateAutoAffectationUtilisateur' ] = (!empty($utilisateur->date_affectation_reelle) && ($utilisateur->date_affectation_reelle != '0000-00-00 00:00:00')) ? mysql2date('d/m/Y H:i', $utilisateur->date_affectation_reelle, 'true') : '';
					$main_infos['utilisateurAffecteAction'][ $index ][ 'dateAffectationUtilisateur' ] = (!empty($utilisateur->date_affectation) && ($utilisateur->date_affectation != '0000-00-00 00:00:00')) ? mysql2date('d/m/Y H:i', $utilisateur->date_affectation, 'true') : '';
					$main_infos['utilisateurAffecteAction'][ $index ][ 'dateDesAffectationUtilisateur' ] = (!empty($utilisateur->date_desaffectation_reelle) && ($utilisateur->date_desaffectation_reelle != '0000-00-00 00:00:00')) ? mysql2date('d/m/Y H:i', $utilisateur->date_desaffectation_reelle, 'true') : '';
					$main_infos['utilisateurAffecteAction'][ $index ][ 'dateAutoDesAffectationUtilisateur' ] = (!empty($utilisateur->date_desAffectation) && ($utilisateur->date_desAffectation != '0000-00-00 00:00:00')) ? mysql2date('d/m/Y H:i', $utilisateur->date_desAffectation, 'true') : '';
				}
			}
			else {
				$main_infos['aucunUtilisateurAffecte'] = array( 'empty' );
			}

			/**	Informations task's associated documents	*/
			$main_infos['documentAssocieAction'] = array();
			$main_infos['aucunDocumentAssocieAction'] = array();
			$associated_document_list = eva_gestionDoc::getDocumentList( $tableElement, $idElement, $tableElement );
			if ( !empty($associated_document_list) ) {
				foreach ( $associated_document_list as $index => $doc ) {
					$main_infos['documentAssocieAction'][ $index ][ 'identifiantDocument' ] = ELEMENT_IDENTIFIER_DOC . $doc->id;
					$main_infos['documentAssocieAction'][ $index ][ 'nomDocument' ] = $doc->nom;
					$main_infos['documentAssocieAction'][ $index ][ 'cheminVersDocument' ] = EVA_GENERATED_DOC_URL . $doc->chemin . $doc->nom;
				}
			}
			else {
				$main_infos['aucunDocumentAssocieAction'] = array( 'empty' );
			}

			/**	Informations task's associated picture	*/
			$main_infos['photoAssocieeAction'] = array();
			$main_infos['aucunePhotoAssocieAction'] = array();
			$associated_picture = EvaPhoto::getPhotos($tableElement, $idElement);
			if ( !empty($associated_picture) ) {
				foreach ( $associated_picture as $index => $picture ) {
					$main_infos['photoAssocieeAction'][ $index ][ 'identifiantPhoto' ] = ELEMENT_IDENTIFIER_PIC . $picture->id;
					$main_infos['photoAssocieeAction'][ $index ][ 'photo' ] = $picture->photo;
					$main_infos['photoAssocieeAction'][ $index ][ 'informationPhoto' ] = '';
					if ( !empty($picture->isMainPicture) && ($picture->isMainPicture == 'yes') ) {
						$main_infos['photoAssocieeAction'][ $index ][ 'informationPhoto' ] .= __('Photo principale', 'evarisk') . "
";
					}
					if ( !empty($current_element_main_definition->idPhotoAvant) && ($current_element_main_definition->idPhotoAvant == $picture->id) ) {
						$main_infos['photoAssocieeAction'][ $index ][ 'informationPhoto' ] .= __('Photo avant l\'action', 'evarisk') . "
";
					}
					if ( !empty($current_element_main_definition->idPhotoApres) && ($current_element_main_definition->idPhotoApres == $picture->id) ) {
						$main_infos['photoAssocieeAction'][ $index ][ 'informationPhoto' ] .= __('Photo apr&egrave;s l\'action', 'evarisk');
					}
				}
			}
			else {
				$main_infos['aucunePhotoAssocieAction'] = array( 'empty' );
			}
			$wpdb->insert( TABLE_GED_DOCUMENTS_META, array( 'status' => 'valid', 'document_id' => $new_document_id, 'meta_key' => '_digi_element_main_def', 'meta_value' => serialize($main_infos), ) );

			eva_gestionDoc::generate_task_odt($tableElement, $idElement, $new_document_id);
			$response[ 'status' ] = true;
			$response[ 'reponse' ] = $doc_def['chemin'] . $doc_def['nom'];
		}
		else {
			$response[ 'status' ] = false;
		}

		return $response;
	}

	function corrective_actions_print_box() {
		$postBoxTitle = __('Impression de la fiche de l\'action', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
		$postBoxId = 'postBoxCorrectivActionPrint';
		add_meta_box($postBoxId, $postBoxTitle, array('actionsCorrectives', 'display_print_box_for_correctiv_action'), PAGE_HOOK_EVARISK_TACHE, 'rightSide', 'default');
		add_meta_box($postBoxId, $postBoxTitle, array('actionsCorrectives', 'display_print_box_for_correctiv_action'), PAGE_HOOK_EVARISK_ACTIVITE, 'rightSide', 'default');
	}

	function display_print_box_for_correctiv_action( $arguments ) {
		$idElement = $arguments['idElement'];
		$tableElement = $arguments['tableElement'];

		$corpsPostBoxRisque = '
<div id="message' . TABLE_FP . '" class="updated fade" style="cursor:pointer; display:none;"></div>
<input type="hidden" name="subTabSelector" id="subTabSelector" value="" />
<ul class="eva_tabs" style="margin-bottom:2px;" >
	<li id="ongletImpressionFicheDAction" class="tabs selected_tab" style="display:inline; margin-left:0.4em;"><label tabindex="1">' . ucfirst(strtolower( __('Impression de la fiche', 'evarisk'))) . '</label></li>
	<li id="ongletHistoriqueFicheDAction" class="tabs" style="display:inline; margin-left:0.4em;"><label tabindex="2">' . ucfirst(strtolower( __('Historique des fiches', 'evarisk'))) . '</label></li>
</ul>
<div id="divImpressionFicheDAction" class="eva_tabs_panel">' . actionsCorrectives::action_print_form( $tableElement, $idElement ) . '</div>
<div id="divHistoriqueFicheDAction" class="eva_tabs_panel" style="display:none">' . eva_gestionDoc::get_associated_document_list($tableElement, $idElement, 'printed_fiche_action', "dateCreation DESC, id DESC", EVA_RESULTATS_PLUGIN_DIR) . '</div>
<script type="text/javascript" >
	function loadBilanBoxContent_FP(boxId, action, table) {
		digirisk(boxId).html(digirisk("#loadingImg").html());
		digirisk(boxId).load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
			"post":"true",
			"table":table,
			"act":action,
			"tableElement":"' . $tableElement . '",
			"idElement":' . $idElement . '
		});
	}

	digirisk(document).ready(function(){
		digirisk("#ongletImpressionFicheDAction").click(function(){
			commonTabChange("postBoxCorrectivActionPrint", "#divImpressionFicheDAction", "#ongletImpressionFicheDAction");
		});
		digirisk("#ongletHistoriqueFicheDAction").click(function(){
			commonTabChange("postBoxCorrectivActionPrint", "#divHistoriqueFicheDAction", "#ongletHistoriqueFicheDAction");
		});
	});
</script>';

		echo $corpsPostBoxRisque;
	}

	function action_print_form( $tableElement, $idElement ) {
		$document_type = 'fiche_action';

		$modelChoice = '';
		$last_document = eva_gestionDoc::getGeneratedDocument( $tableElement, $idElement, 'last', '', $document_type );
		$model_id = eva_gestionDoc::getDefaultDocument( $document_type );
		if ( !empty($last_document) && is_object($last_document) && !empty($last_document->id_model) && ($last_document->id_model != $model_id) ) {
			$model_id = $lastWorkUnitSheet->id_model;
			$modelChoice = '
			setTimeout(function(){
				digirisk("#use_default_model_ca").click();
			},100);';
		}

		$more_option_for_generation = '';
		switch ( $tableElement ) {
			case TABLE_TACHE:
				$more_option_for_generation = '<label class="selectit" ><input type="checkbox" id="create_recursiv_sheet" name="modelUse" value="yes" />' . __('Cr&eacute;er les fiches pour tous les sous-&eacute;l&eacute;ments', 'evarisk') . '</label><br/>';
				break;
		}

		return '
<table border="0" cellpadding="0" cellspacing="0" align="center" class="tabcroisillon" style="width:100%;" >
	<tr>
		<td style="width:40%; vertical-align:top;" >
			<table cellpadding="0" cellspacing="0" border="0" class="tabformulaire" style="width:100%;" >
				<tr>
					<td style="padding:12px auto;" >
						<div class="alignright" >
							' . $more_option_for_generation . '
							<label class="selectit" ><input type="checkbox" id="use_default_model_ca" checked="checked" name="modelUse" value="modeleDefaut" />' . __('Utiliser le mod&egrave;le par d&eacute;faut', 'evarisk') . '</label>
						</div>
						<div class="clear" >&nbsp;</div>
						<div id="modelListForGeneration" style="display:none;" class="alignright" ></div>
					</td>
				</tr>
				<tr>
					<td>
						<input class="button-primary alignright" type="submit" name="print_correctiv_action_sheet" id="print_correctiv_action_sheet" value="' . __('G&eacute;n&eacute;rer', 'evarisk') . '" />
					</td>
				</tr>
			</table>
		</td>
		<td style="width:60%;" id="workUnitSheetResultContainer" >&nbsp;</td>
	</tr>
</table>
<script type="text/javascript" >
	/**	Save a new sheet when clicking on generate button	*/
	jQuery("#print_correctiv_action_sheet").click(function(){
		var model_id = "default";
		if ( !jQuery("#use_default_model_ca").is(":checked")  ) {
			model_id = jQuery("#modelToUse' . $tableElement . '_FA").val();
		}
		var create_recursiv_sheet = false;
		if ( jQuery("#create_recursiv_sheet").is(":checked")  ) {
			create_recursiv_sheet = true;
		}
		jQuery.post( ajaxurl, {
			action: "digi_ajax_save_correctiv_action_sheet",
			model_id: model_id,
			create_recursiv_sheet: create_recursiv_sheet,
			tableElement: "' . $tableElement . '",
			idElement: "' . $idElement . '",
		}, function( response ) {
			jQuery("#ongletHistoriqueFicheDAction").click();
			jQuery("#divHistoriqueFicheDAction").html( response );
		});
	});

	/**	Add listener on model selection checkbox	*/
	jQuery( "#use_default_model_ca" ).click(function(){
		clearTimeout();
		setTimeout(function(){
			if ( !jQuery("#use_default_model_ca").is(":checked") ) {
				jQuery("#workUnitSheetResultContainer").html(\'<img src="' . EVA_IMG_DIVERS_PLUGIN_URL . 'loading.gif" alt="loading" />\');
				jQuery("#workUnitSheetResultContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_DUER . '", "act":"loadNewModelForm", "tableElement":"' . $tableElement . '_FA", "idElement":"' . $idElement . '"});
				jQuery("#modelListForGeneration").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_GED_DOCUMENTS . '", "act":"load_model_combobox", "tableElement":"' . $tableElement . '_FA", "idElement":"' . $idElement . '", "category":"' . $document_type . '", "selection":"' . $model_id . '"});
				jQuery("#modelListForGeneration").show();
			}
			else {
				jQuery("#workUnitSheetResultContainer").html("");
				jQuery("#modelListForGeneration").html("");
				jQuery("#modelListForGeneration").hide();
			}
		},500);
	});
	' . $modelChoice . '
</script>';
	}

	/**
	*	Get activity (sub-task) link with a given risk list
	*
	*	@param string $table_element The element type we want to check to correctiv action linked with
	*	@param integer $id_element The element identifier we want to check to correctiv action linked with
	*	@param array $risks Optionnal An array with the risk list to get directly correctiv action linked
	*	@param array $constraint Optionnal An array with the different constraint to check before getting correctiv actions
	*
	*	@return array $correctiv_actions An array with the list of linked correctiv actions
	*/
	function get_activity_associated_to_risk($table_element = '', $id_element = '', $risks = '', $constraint = '') {
		$correctiv_actions = array();

		if($risks === ''){
			$riskList = Risque::getRisques($table_element, $id_element, "Valid");
			if($riskList != null){
				foreach($riskList as $risque){
					$risks[$risque->id][] = $risque;
				}
			}
		}

		if(is_array($risks) && (count($risks) > 0)){
			foreach($risks as $idRisque => $infosRisque){
				$actionsCorrectives = '';
				$taches = new EvaTaskTable();
				$tacheLike = new EvaTask();
				$tacheLike->setIdFrom($idRisque);
				$tacheLike->setIdFrom($idRisque);
				$tacheLike->setTableFrom(TABLE_RISQUE);
				if(is_array($constraint) && (count($constraint) > 0)){
					foreach($constraint as $constraint_name => $constraint_value){
						switch($constraint_name){
							case 'ProgressionStatus':
								$tacheLike->setProgressionStatus($constraint_value);
							break;
							case 'hasPriority':
								// $tacheLike->sethasPriority($constraint_value);
							break;
						}
					}
				}
				$taches->getTasksLike($tacheLike);
				$return_task = $taches->getTasks();

				$correctiv_actions[] = $return_task;
			}
		}

		return $correctiv_actions;
	}

	/**
	*	Check the progression status in order to output the correct progression term
	*
	*	@param string $progression_status The progression status taken directly from database
	*
	* @return string $statutProgression A translated term for the current progression_status
	*/
	function check_progression_status_for_output($progression_status) {
		$statutProgression = '-';

		switch($progression_status)
		{
			case 'notStarted';
				$statutProgression = __('Non commenc&eacute;e', 'evarisk');
			break;
			case 'inProgress';
				$statutProgression = __('En cours', 'evarisk');
			break;
			case 'Done';
			case 'DoneByChief';
				$statutProgression = __('Sold&eacute;e', 'evarisk');
			break;
		}

		return $statutProgression;
	}

	/**
	 *	Create an output with the different risk associated to an element and the different correctiv actions associated to the risks
	 *
	 *	@param array $risques The list of risks associated to the current element
	 *	@param string $dataTableOptions Allows to define option for the outputed table
	 *
	 *	@return string A table with the risks list
	 */
	function output_correctiv_action_by_risk($risques, $dataTableOptions = '') {
		if ( count($risques) > 0 ) {
			$output = '';

			/**	Get different method used on the current element	*/
			$method_list = array();
			foreach ( $risques as $idRisque => $infosRisque ) {
				$method_list[$infosRisque[0]->id_methode] = $infosRisque[0]->id_methode;
			}

			foreach ( $method_list as $id_methode ) {
				unset($lignesDeValeurs);
				unset($idLignes);
				$idTable = 'suiviActionsCorrectiveElement_' . $id_methode;
				$titres = array('', __('Id.', 'evarisk'), __('Quotation', 'evarisk'), __('Danger', 'evarisk'), __('Commentaire', 'evarisk'));
				$classes = array('columnCollapser', 'columnRId', 'columnQuotation', 'columnNomDanger', 'columnCommentaireRisque');
				$vars = MethodeEvaluation::getVariablesMethode( $id_methode );
				$vars_in_method = array();
				foreach ( $vars as $var_def ) {
					$vars_in_method[] = $var_def->id;
				}
				foreach ( $risques as $idRisque => $infosRisque ) {

					if ( $id_methode == $infosRisque[0]->id_methode) {
						$tachesActionsCorrectives = actionsCorrectives::get_activity_associated_to_risk('', '', array($idRisque, ), '');

						unset($valeurs);
						if ( (count($tachesActionsCorrectives[0]) > 0) || (count($risques) == 1) ) {
							$valeurs[] = array('value' => '<img id="pic_line' . ELEMENT_IDENTIFIER_R . $idRisque . '" src="' . EVA_IMG_ICONES_PLUGIN_URL . 'details_open.png" alt="open_close_row" class="open_close_row_' . $id_methode . '" />', 'class' => '');
							$valeurs[] = array('value' => ELEMENT_IDENTIFIER_R . $idRisque, 'class' => '');

								$score = Risque::getScoreRisque( $infosRisque );
								$quotation = Risque::getEquivalenceEtalon($id_methode, $score, $infosRisque[0]->date);
								$niveauSeuil = Risque::getSeuil($quotation);
								$valeurs[] = array('value' => $quotation, 'class' => 'Seuil_' . $niveauSeuil);

							$valeurs[] = array('value' => !empty($infosRisque[0]->nomDanger) ? $infosRisque[0]->nomDanger : '', 'class' => '');
							$valeurs[] = array('value' => !empty($infosRisque[0]->commentaire) ? $infosRisque[0]->commentaire : '', 'class' => '');

							foreach ( $infosRisque as $variable ) {
								$var = eva_Variable::getVariable($variable->id_variable);
								if ( !empty($var->nom) && in_array( $variable->id_variable, $vars_in_method) ) {
									if (!isset($t[$var->nom]) ) {
										$titres[] = ELEMENT_IDENTIFIER_V . $variable->id_variable . ' - ' . substr($var->nom, 0, 3) . '.';
										$classes[] = 'columnVariableRisque';
										$t[$var->nom] = 1;
									}
									$valeurs[] = array('value' => $variable->valeur, 'class' => '');
								}
							}

							$idLignes[] = ELEMENT_IDENTIFIER_R . $idRisque . '_suiviActionCorrectives';
							$lignesDeValeurs[] = $valeurs;
						}
					}
				}

				$scriptTableauSuiviModification = '
<script type="text/javascript">
var oTable_' . $id_methode . ';

/* Formating function for row details */
function fnFormatDetails_' . $id_methode . ' ( nTr ){
	var aData_' . $id_methode . ' = oTable_' . $id_methode . '.fnGetData( nTr );
	var sOut_' . $id_methode . ' = "<div id=\'" + aData_' . $id_methode . '[1] + "\' >&nbsp;</div>";

	return sOut_' . $id_methode . ';
}

digirisk(document).ready(function(){
	oTable_' . $id_methode . ' = digirisk("#' . $idTable . '").dataTable({
		"aaSorting": [[2, "desc"]],
		"bInfo": false,' . $dataTableOptions . '
		"oLanguage":{
			"sUrl": "' . EVA_INC_PLUGIN_URL . 'js/dataTable/jquery.dataTables.common_translation.txt"
		}
	});
	digirisk("#' . $idTable . ' tfoot").remove();

	digirisk(".open_close_row_' . $id_methode . '").click(function(){
		var nTr = this.parentNode.parentNode;
		if ( this.src.match("details_close") ) {
			/* This row is already open - close it */
			this.src = "' . EVA_IMG_ICONES_PLUGIN_URL . 'details_open.png";
			oTable_' . $id_methode . '.fnClose( nTr );
		}
		else{
			/* Open this row */
			this.src = "' . EVA_IMG_ICONES_PLUGIN_URL . 'details_close.png";
			oTable_' . $id_methode . '.fnOpen( nTr, fnFormatDetails_' . $id_methode . '(nTr), "details" );
			var containerId = digirisk(this).attr("id").replace("pic_line", "");
			digirisk("#" + containerId).html(digirisk("#loadingImg").html());
			digirisk("#" + containerId).load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
				"post":"true",
				"table":"' . TABLE_RISQUE . '",
				"act":"loadAssociatedTask",
				"idRisque": containerId.replace("' . ELEMENT_IDENTIFIER_R . '", ""),
				"extra":"correctiv_action_follow"
			});
		}
	});
});
</script>';

				$current_method = MethodeEvaluation::getMethod( $id_methode );
				$output .= __( 'M&eacute;thode utilis&eacute;e', 'evarisk') . ' : ' . $current_method->nom . '<hr/>' . evaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $scriptTableauSuiviModification) . '<br/><br/>';
			}
			return $output;
		}
		else {
			return __('Il n\'y a aucun risque pour cet &eacute;l&eacute;ment', 'evarisk');
		}
	}

	/**
	 *
	 */
	function get_correctiv_action_for_duer() {
		global $wpdb;
		$actions = array();

		$query = $wpdb->prepare("
SELECT CONCAT('".ELEMENT_IDENTIFIER_T."',TASK.id) as idAction, TASK.*
FROM ".TABLE_TACHE." AS TASK
	/*	INNER JOIN ".TABLE_TACHE." AS TASK_PARENT ON ( (TASK_PARENT.limiteGauche < TASK.limiteGauche) && (TASK_PARENT.limiteDroite > TASK.limiteDroite) AND ( TASK_PARENT.tableProvenance != %s ) )	*/
WHERE TASK.nom_exportable_plan_action=%s
	AND TASK.tableProvenance != %s
	AND TASK.Status='Valid'
ORDER BY TASK.limiteGauche, TASK.limiteDroite
", TABLE_RISQUE, 'yes', TABLE_RISQUE);
		$action_list = $wpdb->get_results($query);
		foreach ( $action_list as $action ) {
			$racine = Arborescence::getRacine(TABLE_TACHE, " id='" . $action->id . "' ");
			$parents = Arborescence::getAncetre(TABLE_TACHE, $racine);
			$export_task = true;
			foreach ( $parents as $parent ) {
				if ( ( $parent->nom != __('Tache Racine', 'evarisk') ) && ( $parent->tableProvenance != TABLE_RISQUE ) && ( $parent->nom_exportable_plan_action == 'yes'  ) ) {
					$export_task = false;
				}
			}

			if ( $export_task ) {
				$actions[$action->idAction]['idAction'] = $action->idAction;
				$actions[$action->idAction]['etatAction'] = actionsCorrectives::check_progression_status_for_output($action->ProgressionStatus) . ' ('. (!empty($action->avancement) ? $action->avancement : 0) . '%)';
				$actions[$action->idAction]['nomAction'] = $action->nom;
				$actions[$action->idAction]['descriptionAction'] = $action->description;
				$follow_up_list = suivi_activite::getSuiviActivite(TABLE_TACHE, $action->id);
				if ( !empty($follow_up_list) ) {
					foreach ( $follow_up_list as $follow_up ) {
						if ( $follow_up->export == 'yes' ) {
							$actions[$action->idAction]['descriptionAction'] .= str_replace('<br />', "
", mysql2date('d-m-Y H:i', $follow_up->date_ajout, true ) . ' - ' . $follow_up->commentaire) . "

";
						}
					}
				}
				$actions[$action->idAction]['ajoutAction'] = mysql2date('d F Y', $action->firstInsert, true);
				$responsable_infos = evaUser::getUserInformation($action->idResponsable);
				$actions[$action->idAction]['responsableAction'] = (($action->idResponsable>0) ? ELEMENT_IDENTIFIER_U.$action->idResponsable.' - '.$responsable_infos['user_lastname'].' '.$responsable_infos['user_firstname'] : __('Pas de responsable d&eacute;fini', 'evarisk'));
				$affectation = $wpdb->prepare("SELECT nom FROM ".$action->tableProvenance." WHERE id=%d", $action->idProvenance);
				switch ( $action->tableProvenance ) {
					case TABLE_GROUPEMENT:
						$element_identifier = ELEMENT_IDENTIFIER_GP;
					break;
					case TABLE_UNITE_TRAVAIL:
						$element_identifier = ELEMENT_IDENTIFIER_UT;
					break;
				}
				$direct_parent = Arborescence::getPere(TABLE_TACHE, $racine);
				$actions[$action->idAction]['affectationAction'] = ( ($action->idProvenance>0) ? $element_identifier . $action->idProvenance.' - '.$wpdb->get_var($affectation) : __('Aucune affectation pour cette t&acirc;che', 'evarisk') );

				$elements = Arborescence::getFils(TABLE_TACHE, $racine, "nom ASC");
				$sub_element = eva_documentUnique::output_correctiv_action_tree($elements, $racine, TABLE_TACHE, 'unaffected_task');

				$actions = array_merge((array)$actions, (array)$sub_element);
			}

		}

		return $actions;
	}

	/**
	 *	Create the output for main correctiv action page
	 */
	function actionsCorrectivesMainPage(){
		$messageInfo = '';

		$_POST['table'] = TABLE_TACHE;
		$titrePage = __("Actions Correctives", 'evarisk');
		$icone = PICTO_LTL_ACTION;
		$titreIcone = "Icone actions correctives";
		$altIcon = "Icone AC";
		$titreFilAriane= __("Actions correctives", 'evarisk');
		if(!isset($_POST['affichage'])){
			$_POST['affichage'] = "affichageListe";
		}
		include_once(EVA_LIB_PLUGIN_DIR . 'classicalPage.php' );
		// On enl�ve le choix de l'affichage
?>
		<script type="text/javascript">
			digirisk(document).ready(function(){
				digirisk('#choixAffichage').hide();
			});
		</script>
<?php
		if(isset($_GET['elt']) && ($_GET['elt'] != '')){
			echo
				'<script type="text/javascript">
					digirisk(document).ready(function(){
						setTimeout(function(){
							digirisk("#' . $_GET['elt'] . '").click();
						},3000);
					})
				</script>';
		}
	}

}