<?php
/*
*
* @author Evarisk
* @version v5.0
*/
define('DOING_AJAX', true);
define('WP_ADMIN', true);
require_once('../../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/admin.php');
require_once('../evarisk.php');
require_once(EVA_INC_PLUGIN_DIR . 'includes.php');

@header('Content-Type: text/html; charset=' . get_option('blog_charset'));

/*
* Param�tres pass�s en POST
*/
if(!empty($_REQUEST['post']) && ($_REQUEST['post'] == 'true')){
	/*	Refactoring actions	*/
	if(isset($_REQUEST['act'])){
		switch($_REQUEST['act'])
		{
			case 'edit':
			case 'add':
			case 'changementPage':{
				$output = '
					<script type="text/javascript">
						digirisk(document).ready(function() {
							initialiseClassicalPage();';
				if($_REQUEST['affichage'] == "affichageTable"){
					$output .= '
							initialiseEditedElementInGridMode("photo' . $_REQUEST['table'] . $_REQUEST['id'] . '");';
				}
				else{
					$tableId = 'mainTable';
					switch($_REQUEST['table']){
						case TABLE_CATEGORIE_DANGER:
						case TABLE_CATEGORIE_PRECONISATION:
						case TABLE_METHODE:
							$tableId = 'main_table_' . $_REQUEST['table'];
						break;
					}
					switch($_REQUEST['table']){
						case TABLE_CATEGORIE_DANGER:
						case TABLE_CATEGORIE_PRECONISATION:
						case TABLE_METHODE:
						case TABLE_GROUPEMENT:
						case TABLE_TACHE:
							if(!empty($_REQUEST['id'])){
								$output .= '
							digirisk("#node-' . $tableId . '-' . $_REQUEST['id'] . '").addClass("edited");';
							}
						break;
						case TABLE_UNITE_TRAVAIL:
						case TABLE_ACTIVITE:
						case TABLE_DANGER:
						case TABLE_PRECONISATION:
							if(!empty($_REQUEST['id'])){
								$output .= '
							digirisk("#leaf-' . $_REQUEST['id'] . '").addClass("edited");';
							}
						break;
					}
				}
				$output .= '
						});
					</script>';
				echo $output;
				require(EVA_MODULES_PLUGIN_DIR . 'partieDroite.php');
			}
			break;

			case 'reload_main_tree':{
				switch($_REQUEST['table']){
					case TABLE_UNITE_TRAVAIL:
					case TABLE_GROUPEMENT:{
						$page_parameters = evaluationDesRisques::risk_evaluation_main_page_parameters();
					}break;
					case TABLE_CATEGORIE_DANGER:
					case TABLE_DANGER:{
						$page_parameters = digirisk_danger::danger_main_page();
					}break;
				}

				echo digirisk_display::standard_tree($page_parameters['tree_root'], $page_parameters['element_type'],  $page_parameters['tree_identifier'], $page_parameters['tree_root_name'], $page_parameters['tree_element_are_draggable'], $page_parameters['tree_action_display']);
			}break;
			case 'reload_config_tree':{
				switch($_REQUEST['table']){
					case TABLE_CATEGORIE_PRECONISATION:
					case TABLE_PRECONISATION:{
						$page_parameters = evaRecommandation::recommandation_main_page();
					}break;
					case TABLE_METHODE:{
						$page_parameters = MethodeEvaluation::evaluation_method_main_page();
					}break;
				}

				echo digirisk_display::standard_configuration_tree($page_parameters['element_type'], $page_parameters['tree_identifier'], $page_parameters['tree_root_name'], $page_parameters['tree_element_are_draggable'], $page_parameters['tree_action_display']);
			}break;

			/*	Actions on picture	*/
			case 'defaultPictureSelection':
				echo evaPhoto::setMainPhotoAction($_REQUEST['table'], $_REQUEST['idElement'], $_REQUEST['idPhoto']);
			break;
			case 'DeleteDefaultPictureSelection':
				echo evaPhoto::setMainPhotoAction($_REQUEST['table'], $_REQUEST['idElement'], $_REQUEST['idPhoto'], 'no');
			break;
			case 'deletePicture':
				echo evaPhoto::deletePictureAction($_REQUEST['table'], $_REQUEST['idElement'], $_REQUEST['idPicture']);
			break;
			case 'reloadGallery':
				$script =
				'<script type="text/javascript">
					digirisk(document).ready(function(){
						digirisk(".qq-upload-list").hide();
					});
				</script>';
				echo $script . evaPhoto::outputGallery($_REQUEST['table'], $_REQUEST['idElement']);
			break;
			case 'showGallery':
				echo evaPhoto::getGallery($_REQUEST['table'], $_REQUEST['idElement']);
			break;

			case 'saveDigiNote':
				echo evaNotes::saveDigiNote($_REQUEST['notesContent']);
			break;
			case 'loadDiginote':
				echo evaNotes::noteDialogForm();
			break;

			case 'loadUserInfo':
			{
				echo digirisk_accident::get_victim_accident_informations($_REQUEST['id_user']);
			}
			break;

			case 'loadWitnessInfo':
			{
				echo digirisk_accident::get_accident_third_party_informations($_REQUEST['id_user'], 'witness');
			}
			break;

			case 'loadThirdPartyInfo':
			{
				echo digirisk_accident::get_accident_third_party_informations($_REQUEST['id_user'], 'third_party');
			}
			break;
		}
	}

	if(isset($_REQUEST['table'])){
		switch($_REQUEST['table']){
			case TABLE_GROUPEMENT:
				switch($_REQUEST['act'])
				{
					case 'transfert':
					{
						$fils = $_REQUEST['idElementSrc'];
						$pere = $_REQUEST['idElementDest'];
						$idPere = str_replace('node-' . $_REQUEST['location'] . '-','', $pere);
						$idFils = (string)((int) str_replace('node-' . $_REQUEST['location'] . '-','', $fils));
						$groupementPere = EvaGroupement::getGroupement($idPere);
						if($idFils == str_replace('node-' . $_REQUEST['location'] . '-','', $fils)) //Le fils est un groupement
						{
							$groupement = EvaGroupement::getGroupement($idFils);
							$descendants = Arborescence::getDescendants(TABLE_GROUPEMENT, $groupement, $element, '1', 'id ASC', "");
							$sourceIsParentOfDest = false;
							foreach($descendants as $sourceDescendants)
							{
								if($sourceDescendants->id == $idPere)
								{
									$sourceIsParentOfDest = true;
								}
							}
							if(!$sourceIsParentOfDest)
							{
								$pereActu = Arborescence::getPere($_REQUEST['table'], $groupement, "1");
								if($pereActu->id != $idPere)
								{
									$_REQUEST['act'] = 'update';
									$_REQUEST['id'] = $groupement->id;
									$_REQUEST['nom_groupement'] = $groupement->nom;
									$_REQUEST['description'] = $groupement->description;
									$_REQUEST['telephone'] = $groupement->telephoneGroupement;
									$_REQUEST['effectif'] = $groupement->effectif;

									$address = new EvaAddress($groupement->id_adresse);

									$address->load();
									$contenuInputLigne1 = $address->getFirstLine();
									$contenuInputLigne2 = $address->getSecondLine();
									$contenuInputCodePostal = $address->getPostalCode();
									$contenuInputVille = $address->getCity();

									$_REQUEST['adresse_ligne_1'] = $address->getFirstLine();
									$_REQUEST['adresse_ligne_2'] = $address->getSecondLine();
									$_REQUEST['code_REQUESTal'] = $address->getPostalCode();
									$_REQUEST['ville'] = $address->getCity();
									$_REQUEST['longitude'] = $address->getLongitude();
									$_REQUEST['latitude'] = $address->getLatitude();
									$_REQUEST['groupementPere'] = $idPere;
									require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/groupement/groupementPersistance.php');
								}
							}
							else
							{
								echo
	'<script type="text/javascript" >
			setTimeout(\'actionMessageShow("#message", "<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="error" />' . __('Vous ne pouvez pas effectuer ce d&eacute;placement', 'evarisk') . '")\', 700);
			setTimeout(\'actionMessageHide("#message")\',7500);
	</script>';
							}
						}
						else //Le fils est une unit�
						{
							$idFils = str_replace('leaf-','', $fils);
							eva_uniteDeTravail::transfertUnit($idFils, $idPere);
						}
					}
					break;
					case 'save':
					case 'update':
					{
						global $wpdb;
						switch($_REQUEST['act'])
						{
							case 'save':
								$action = __('sauvegard&eacute;e', 'evarisk');
								$afterActionList = '
										digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
										{
											"post": "true",
											"table": "' . TABLE_GROUPEMENT . '",
											"act": "edit",
											"id": "' . $_REQUEST['id'] . '",
											"partie": "left",
											"menu": digirisk("#menu").val(),
											"affichage": "affichageListe",
											"expanded": expanded
										});';
								$afterActionTable = '
										digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
											"table": "' . TABLE_GROUPEMENT . '",
											"act": "edit",
											"id": "' . $_REQUEST['id'] . '",
											"partie": "left",
											"menu": digirisk("#menu").val(),
											"affichage": "affichageTable",
											"page": digirisk("#pagemainPostBoxReference").val(),
											"idPere": digirisk("#identifiantActuellemainPostBox").val(),
											"partition": "tout"
										})';
								break;
							case 'update':
								$action = __('mise &agrave; jour', 'evarisk');
								$afterActionList = '';
								$afterActionTable = '';
								break;
						}
						require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/groupement/groupementPersistance.php');
						$messageInfo = '
							<script type="text/javascript">
								digirisk(document).ready(function(){
									actionMessageShow("#message", "' . addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La fiche %s a correctement &eacute;t&eacute; %s', 'evarisk') . '</strong></p>', __('du groupement', 'evarisk') . ' "' . stripslashes($_REQUEST['nom_groupement']) . '"', $action)) . '");
									setTimeout(\'actionMessageHide("#message")\',7500);

									digirisk("#rightEnlarging").show();
									digirisk("#equilize").click();
									digirisk("#partieEdition").html(digirisk("#loadingImg").html());
									if("' . $_REQUEST['affichage'] . '" == "affichageTable")
									{
										if(digirisk("#filAriane :last-child").is("label"))
											digirisk("#filAriane :last-child").remove();
										digirisk("#filAriane :last-child").after("<label>&nbsp;&raquo;&nbsp;&Eacute;dition&nbsp;de&nbsp;' . $_REQUEST['nom_groupement'] . '</label>");
										digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
											"table": "' . TABLE_GROUPEMENT . '",
											"act": "edit",
											"id": "' . $_REQUEST['id'] . '",
											"partie": "right",
											"menu": digirisk("#menu").val(),
											"affichage": "affichageTable",
											"page": digirisk("#pagemainPostBoxReference").val(),
											"idPere": digirisk("#identifiantActuellemainPostBox").val(),
											"partition": "tout"
										});
										' . $afterActionTable	. '
									}
									else
									{
										var expanded = new Array();
										digirisk(".expanded").each(function(){expanded.push(digirisk(this).attr("id"));});
										digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
											"table": "' . TABLE_GROUPEMENT . '",
											"act": "edit",
											"id": "' . $_REQUEST['id'] . '",
											"partie": "right",
											"menu": digirisk("#menu").val(),
											"affichage": "affichageListe",
											"expanded": expanded
										});
										' . $afterActionList . '
									}
									digirisk("#node-mainTable-' . $_REQUEST['id'] . ' td:first-child").children("span.nomNoeudArbre").html("' . $_REQUEST['nom_groupement'] . '");
								});
							</script>';
						echo $messageInfo;
					}
					break;
					case 'delete':
					{
						require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/groupement/groupementPersistance.php');
						echo '
							<script type="text/javascript">
								digirisk(document).ready(function() {
									initialiseClassicalPage();
									digirisk("#partieEdition").html(" ");
								});
							</script>';
						require_once(EVA_MODULES_PLUGIN_DIR . 'partieDroite.php');
					}
					break;
					case 'updateByField':
					{
						$id_Groupement = (digirisk_tools::IsValid_Variable($_REQUEST['id']));
						$whatToUpdate = (digirisk_tools::IsValid_Variable($_REQUEST['whatToUpdate']));
						$whatToSet = (digirisk_tools::IsValid_Variable($_REQUEST['whatToSet']));

						switch($whatToUpdate){
							case 'nom':
							{
								$messageContainerId = 'message';
								$actionAfterSuccess = 'digirisk("#validChangeTitre").hide();
			digirisk("#titreGp' . $id_Groupement . '").removeClass("titleInfoSelected");
			digirisk("#node-mainTable-' . $id_Groupement . '-name span.nomNoeudArbre").html("' . $whatToSet . '");';
							}
							break;
							default:
							{
								$messageContainerId = 'message';
								$actionAfterSuccess = '';
							}
							break;
						}

						$updateGroupement = EvaGroupement::updateGroupementByField($id_Groupement, $whatToUpdate, $whatToSet);
						if($updateGroupement)
						{
							$messageInfo = addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Sauvegarde r&eacute;ussie', 'evarisk') . '</strong></p>');
						}
						else
						{
							$messageInfo = addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La sauvegarde a &eacute;chou&eacute;e', 'evarisk') . '</strong></p>');
							$actionAfterSuccess = '';
						}
	echo
	'<script type="text/javascript">
		digirisk(document).ready(function(){
			actionMessageShow("#' . $messageContainerId . '", "' . $messageInfo . '");
			setTimeout(\'actionMessageHide("#' . $messageContainerId . '")\',7500);

			' . $actionAfterSuccess . '
		});
	</script>';
					}
					break;
					case 'reactiv_deleted':
					{
						$nom_groupement = (isset($_REQUEST['nom_groupement']) && ($_REQUEST['nom_groupement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['nom_groupement']) : '';
						$currentGpt = EvaGroupement::getGroupements(" nom = '" . $nom_groupement . "' ");
						if(EvaGroupement::updateGroupementByField($currentGpt[0]->id, 'Status', 'Valid'))
						{
							$messageInfo = addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La restauration a r&eacute;ussie', 'evarisk') . '</strong></p>');
							$moreAction = 'digirisk("#partieEdition").html(" ");digirisk("#partieGauche").html(digirisk("#loadingImg").html());
		var expanded = new Array();
		digirisk(".expanded").each(function(){expanded.push(digirisk(this).attr("id"));});
		digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
			"post": "true",
			"table": "' . TABLE_GROUPEMENT . '",
			"act": "edit",
			"id": "' . $_REQUEST['id'] . '",
			"partie": "left",
			"menu": digirisk("#menu").val(),
			"affichage": "affichageListe",
			"expanded": expanded
		});';
						}
						else
						{
							$messageInfo = addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La restauration a &eacute;chou&eacute;e', 'evarisk') . '</strong></p>');
							$moreAction = '';
						}

						echo
	'<script type="text/javascript">
		digirisk("#existingElementDialog").dialog("close");

		actionMessageShow("#message", "' . $messageInfo . '");
		setTimeout(\'actionMessageHide("#message")\',7500);

	' . $moreAction . '
	</script>';
					}
					break;

					case 'load_groupement_form':
					{
						include_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/groupement/groupement-new.php');
						$_REQUEST['dont_display_button'] = 'yes';
						$_REQUEST['form_id'] = '_accident';
						getGroupGeneralInformationPostBoxBody($_REQUEST);
					}
					break;
					case 'save_groupement_missing_informations':
					{
						$_REQUEST['act'] = 'update';
						require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/groupement/groupementPersistance.php');
						echo '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#divAccidenContainer").load(EVA_AJAX_FILE_URL,{
			"post":"true",
			"table":"' . DIGI_DBT_ACCIDENT . '",
			"act":"previous_step",
			"accident_id": jQuery("#accident_form #tableElement").val(),
			"tableElement": jQuery("#accident_form #tableElement").val(),
			"idElement": jQuery("#accident_form #idElement").val(),
			"step_to_load":jQuery("#accident_form #accident_form_step").val()
		});
	});
</script>';
					}
					break;
				}
				break;
			case TABLE_UNITE_TRAVAIL:
				switch($_REQUEST['act'])
				{
					case 'save':
					case 'update':
					{
						$previousAct = $_REQUEST['act'];
						$workingUnitResult = false;
						require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/uniteDeTravail/uniteTravailPersistance.php' );
						if($workingUnitResult)
						{
							switch($previousAct)
							{
								case 'save':
								{
									$action = __('sauvegard&eacute;e', 'evarisk');
									$afterActionList = '
										digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
										{
											"post": "true",
											"table": "' . TABLE_UNITE_TRAVAIL . '",
											"act": "edit",
											"id": "' . $_REQUEST['id'] . '",
											"partie": "left",
											"menu": digirisk("#menu").val(),
											"affichage": "affichageListe",
											"expanded": expanded
										});';
									$afterActionTable = '
											digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
											{
												"post": "true",
												"table": "' . TABLE_UNITE_TRAVAIL . '",
												"id": "' . $_REQUEST['id'] . '",
												"page": digirisk("#pagemainPostBoxReference").val(),
												"idPere": digirisk("#identifiantActuellemainPostBox").val(),
												"act": "edit",
												"partie": "left",
												"menu": digirisk("#menu").val(),
												"affichage": "affichageTable",
												"partition": "tout"
											});';
								}
								break;
								case 'update':
								{
									$action = __('mise &agrave; jour', 'evarisk');
									$afterActionList = '
		digirisk("#leaf-' . $_REQUEST['id'] . ' span.nomFeuilleArbre").html("' . $_REQUEST['nom_unite_travail'] . '");';
									$afterActionTable = '';
								}
								break;
							}

							$messageInfo = '
<script type="text/javascript">
	digirisk(document).ready(function(){
		actionMessageShow("#message", "' . addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La fiche %s a correctement &eacute;t&eacute; %s', 'evarisk') . '</strong></p>', __('de l\'unit&eacute; de travail', 'evarisk') . ' "' . stripslashes($_REQUEST['nom_unite_travail']) . '"', $action)) . '");
		setTimeout(\'actionMessageHide("#message")\',7500);

		digirisk("#rightEnlarging").show();
		digirisk("#equilize").click();
		digirisk("#partieEdition").html(digirisk("#loadingImg").html());';
							if($_REQUEST['affichage'] == 'affichageTable')
							{
								$messageInfo .= '
		if(digirisk("#filAriane :last-child").is("label"))
			digirisk("#filAriane :last-child").remove();
		digirisk("#filAriane :last-child").after(\'<label>&nbsp;&raquo;&nbsp;&Eacute;dition&nbsp;de&nbsp;' . $_REQUEST['nom_unite_travail'] . '</label>\');
		if(digirisk("#filAriane :last-child").is("label"))
			digirisk("#filAriane :last-child").remove();
		digirisk("#rightEnlarging").show();
		digirisk("#equilize").click();
		digirisk("#filAriane :last-child").after(\'<label>&nbsp;&raquo;&nbsp;&Eacute;dition&nbsp;de&nbsp;' . $_REQUEST['nom_unite_travail'] . '</label>\');
		digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
		{
			"post": "true",
			"table": "' . TABLE_UNITE_TRAVAIL . '",
			"id": "' . $_REQUEST['id'] . '",
			"page": digirisk("#pagemainPostBoxReference").val(),
			"idPere": digirisk("#identifiantActuellemainPostBox").val(),
			"act": "edit",
			"partie": "right",
			"menu": digirisk("#menu").val(),
			"affichage": "affichageTable",
			"partition": "tout"
		});
		' . $afterActionTable;
							}
							else
							{
								$messageInfo .= '
		var expanded = new Array();
		digirisk(".expanded").each(function(){expanded.push(digirisk(this).attr("id"));});
		digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
		{
			"post": "true",
			"table": "' . TABLE_UNITE_TRAVAIL . '",
			"act": "edit",
			"id": "' . $_REQUEST['id'] . '",
			"partie": "right",
			"menu": digirisk("#menu").val(),
			"affichage": "affichageListe",
			"expanded": expanded
		});
		' . $afterActionList;
							}
							$messageInfo .= '
	});
</script>';
						}
						else
						{
							$messageInfo = '
<script type="text/javascript">
	digirisk(document).ready(function(){
		actionMessageShow("#message", "' . addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Une erreur est survenue lors de l\'enregistrement de la fiche %s', 'evarisk') . '</strong></p>', __('de l\'unit&eacute; de travail', 'evarisk') . ' "' . stripslashes($_REQUEST['nom_unite_travail']) . '"')) . '");
		setTimeout(\'actionMessageHide("#message")\',7500);
	});
</script>';
						}
						echo $messageInfo;
					}
					break;
					case 'delete':
					{
						require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/uniteDeTravail/uniteTravailPersistance.php' );
						echo '
							<script type="text/javascript">
								digirisk(document).ready(function() {
									initialiseClassicalPage();
									digirisk("#partieEdition").html(" ");
								});
							</script>';
						require_once(EVA_MODULES_PLUGIN_DIR . 'partieDroite.php');
					}
					break;
					case 'updateByField':
					{
						$id_unite = (digirisk_tools::IsValid_Variable($_REQUEST['id']));
						$whatToUpdate = (digirisk_tools::IsValid_Variable($_REQUEST['whatToUpdate']));
						$whatToSet = (digirisk_tools::IsValid_Variable($_REQUEST['whatToSet']));

						switch($whatToUpdate){
							case 'nom':
							{
								$messageContainerId = 'message';
								$actionAfterSuccess = 'digirisk("#validChangeTitre").hide();
			digirisk("#titreWU' . $id_unite . '").removeClass("titleInfoSelected");
			digirisk("#leaf-' . $id_unite . ' span.nomFeuilleArbre").html("' . $whatToSet . '");';
							}
							break;
							default:
							{
								$messageContainerId = 'message';
								$actionAfterSuccess = '';
							}
							break;
						}

						$updateGroupement = eva_UniteDeTravail::updateWorkingUnitByField($id_unite, $whatToUpdate, $whatToSet);
						if($updateGroupement)
						{
							$messageInfo = addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Sauvegarde r&eacute;ussie', 'evarisk') . '</strong></p>');
						}
						else
						{
							$messageInfo = addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La sauvegarde a &eacute;chou&eacute;e', 'evarisk') . '</strong></p>');
							$actionAfterSuccess = '';
						}
	echo
	'<script type="text/javascript">
		digirisk(document).ready(function(){
			actionMessageShow("#' . $messageContainerId . '", "' . $messageInfo . '");
			setTimeout(\'actionMessageHide("#' . $messageContainerId . '")\',7500);

			' . $actionAfterSuccess . '
		});
	</script>';
					}
					break;
					case 'reactiv_deleted':
					{
						$nom_groupement = (isset($_REQUEST['nom_groupement']) && ($_REQUEST['nom_groupement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['nom_groupement']) : '';
						$currentGpt = EvaGroupement::getGroupements(" nom = '" . $nom_groupement . "' ");
						if(EvaGroupement::updateGroupementByField($currentGpt[0]->id, 'Status', 'Valid'))
						{
							$messageInfo = addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La restauration a r&eacute;ussie', 'evarisk') . '</strong></p>');
							$moreAction = 'digirisk("#partieEdition").html(" ");
		var expanded = new Array();
		digirisk(".expanded").each(function(){expanded.push(digirisk(this).attr("id"));});
		digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
			"post": "true",
			"table": "' . TABLE_GROUPEMENT . '",
			"act": "edit",
			"id": "' . $_REQUEST['id'] . '",
			"partie": "left",
			"menu": digirisk("#menu").val(),
			"affichage": "affichageListe",
			"expanded": expanded
		});';
						}
						else
						{
							$messageInfo = addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La restauration a &eacute;chou&eacute;e', 'evarisk') . '</strong></p>');
							$moreAction = '';
						}

						echo
	'<script type="text/javascript">
		digirisk("#existingElementDialog").dialog("close");

		actionMessageShow("#message", "' . $messageInfo . '");
		setTimeout(\'actionMessageHide("#message")\',7500);

	' . $moreAction . '
	</script>';
					}
				}
				break;
			case TABLE_CATEGORIE_DANGER:
				switch($_REQUEST['act']){
					case 'save':
					case 'update':{					/*	Add or update an element	*/
						switch($_REQUEST['act']){
							case 'save':
								$action = __('sauvegard&eacute;e', 'evarisk');
								$load = '
									var expanded = new Array();
									jQuery(".expanded").each(function(){expanded.push(jQuery(this).attr("id"));});
									var expanded = new Array();
									jQuery(".expanded").each(function(){expanded.push(jQuery(this).attr("id"));});
									jQuery("#main_tree_container").load(EVA_AJAX_FILE_URL,{
										"post": "true",
										"act": "reload_main_tree",
										"table": sub_element_type,
										"idPere": "' . $_REQUEST['categorieMere'] . '",
										"elt": "node-main_table_" + sub_element_type + "-" + leafId,
										"expanded": expanded
									});
									side_reloader(sub_element_type, leafId, menu, expanded)';
							break;
							case 'update':
								$action = __('mise &agrave; jour', 'evarisk');
								$load = '
									jQuery("#node-main_table_' . $_REQUEST['table'] . '-' . $_REQUEST['id'] . '-name .node_name").html("' . stripslashes($_REQUEST['nom_categorie']) . '");';
							break;
						}
						require_once(EVA_METABOXES_PLUGIN_DIR . 'dangers/categorieDangers/categorieDangersPersistance.php');
						$messageInfo = '
							<script type="text/javascript">
								digirisk(document).ready(function(){
									actionMessageShow("#message", "' . addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La fiche %s a correctement &eacute;t&eacute; %s', 'evarisk') . '</strong></p>', __('de la cat&eacute;gorie de dangers', 'evarisk') . ' "' . stripslashes($_REQUEST['nom_categorie']) . '"', $action)) . '");
									setTimeout(\'actionMessageHide("#message")\',7500);

									jQuery("#rightEnlarging").show();
									jQuery("#equilize").click();
									sub_element_type = "' . $_REQUEST['table'] . '";
									leafId = "' . $_REQUEST['id'] . '";
									menu = "";' . $load . '
								});
							</script>';
						echo $messageInfo;
					}break;
					case 'delete':{					/*	Delete an element	*/
						$dangerCategoryResult = categorieDangers::deleteCategorie($_REQUEST['id']);
					}break;

					case 'transfert':
					{
						$fils = $_REQUEST['idElementSrc'];
						$pere = $_REQUEST['idElementDest'];
						$idFils = (string)((int) str_replace('node-' . $_REQUEST['location'] . '-','', $fils));
						$idPere = str_replace('node-' . $_REQUEST['location'] . '-','', $pere);
						if($idFils == str_replace('node-' . $_REQUEST['location'] . '-','', $fils))
						//Le fils est une cat�gorie
						{
							$categorie = categorieDangers::getCategorieDanger($idFils);
							$pereActu = Arborescence::getPere($_REQUEST['table'], $categorie);
							if($pereActu->id != $idPere)
							{
								$_REQUEST['act'] = 'update';
								$_REQUEST['id'] = $idFils;
								$_REQUEST['nom_categorie'] = $categorie->nom;
								$_REQUEST['categorieMere'] = $idPere;
								require_once(EVA_METABOXES_PLUGIN_DIR . 'dangers/categorieDangers/categorieDangersPersistance.php');
							}
						}
						else
						//Le fils est un danger
						{
							$idFils = str_replace('leaf-','', $fils);
							evaDanger::transfertDanger($idFils, $idPere);
						}
					}
					break;
					case 'reloadComboDangers':

						$formId = digirisk_tools::IsValid_Variable($_REQUEST['formId']);
						$idElement = digirisk_tools::IsValid_Variable($_REQUEST['idElement']);
						$dangers = categorieDangers::getDangersDeLaCategorie($idElement, 'Status="Valid"');
						$script = '';
						if ( $dangers[0]->id != null ) {
							$script .= '
								<script type="text/javascript">
									evarisk(document).ready(function(){
										evarisk("#needDangerCategory").show();';
							if ( count($dangers) > 1 ) {
								$script .= '
										evarisk("#' . $formId . 'divDangerFormRisque").show();
										evarisk("#boutonAvanceRisque").children("span:first").html("-");';
							}
							else {
								$script .= '
										evarisk("#' . $formId . 'divDangerFormRisque").hide();
										evarisk("#boutonAvanceRisque").children("span:first").html("+");';
							}
							$script .=
									'})
								</script>';
						}
						else {
							$script .= '
<script type="text/javascript">
	evarisk(document).ready(function(){
		evarisk("#needDangerCategory").hide();
	})
</script>';
						}
						$nomDangerDefaut = '';
						foreach($dangers as $danger) {
                            $methode = 1;
                            if ( $danger->choix_danger != "" ) {
                                $tableau = unserialize($danger->choix_danger);
                                if(in_array("defaut", $tableau)) {
                                    $nomDangerDefaut = $danger;
								}
                                if ( !empty( $danger->methode_eva_defaut ) ) {
                                    $methode = $danger->methode_eva_defaut;
                                }
							}
                            $script .= '<input type="hidden" value="'.$methode.'" id="methode_danger_'.$danger->id.'" />';
                        }

                       $script .= '
<script type="text/javascript">
jQuery(document).ready(function(){
	//jQuery("#methodeFormRisque").val( jQuery("#methode_danger_" + jQuery("#dangerFormRisque").val()).val() );
	//digirisk("#' . $formId . 'divVariablesFormRisque").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_METHODE . '", "act":"reloadVariables", "idMethode":jQuery("#methode_danger_" + jQuery("#dangerFormRisque").val()).val(), "idRisque": "0", "formId":"' . $formId . '"});
	jQuery("#dangerFormRisque").change(function(){
		jQuery("#methodeFormRisque").val( jQuery("#methode_danger_" + jQuery(this).val()).val() );
		digirisk("#' . $formId . 'divVariablesFormRisque").html(digirisk("#loadingImg").html());
		//digirisk("#' . $formId . 'divVariablesFormRisque").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_METHODE . '", "act":"reloadVariables", "idMethode":jQuery("#methode_danger_" + jQuery(this).val()).val(), "idRisque": "0", "formId":"' . $formId . '"});
	});
})
</script>';

						echo $script . EvaDisplayInput::afficherComboBox($dangers, $formId . 'dangerFormRisque', __('Dangers de la cat&eacute;gorie', 'evarisk') . ' : ', 'danger', '', $nomDangerDefaut);
					break;
				}
			break;
			case TABLE_DANGER:
				switch($_REQUEST['act'])
				{
					case 'save':
					case 'update':{					/*	Add or update an element	*/
						switch($_REQUEST['act'])
						{
							case 'save':
								$action = __('sauvegard&eacute;e', 'evarisk');
								$load = '
									var expanded = new Array();
									jQuery(".expanded").each(function(){expanded.push(jQuery(this).attr("id"));});
									jQuery("#main_tree_container").load(EVA_AJAX_FILE_URL,{
										"post": "true",
										"act": "reload_main_tree",
										"table": sub_element_type,
										"elt": "leaf-" + leafId + "-name",
										"expanded": expanded
									});
									side_reloader(sub_element_type, leafId, menu, expanded);';
							break;
							case 'update':
								$action = __('mise &agrave; jour', 'evarisk');
								$load = '
									jQuery("#leaf-' . $_REQUEST['id'] . '-name .leaf_name").html("' . stripslashes($_REQUEST['nom_danger']) . '");';
							break;
						}
						require_once(EVA_METABOXES_PLUGIN_DIR . 'dangers/danger/dangerPersistance.php' );
						$messageInfo = '
							<script type="text/javascript">
								evarisk(document).ready(function(){
									actionMessageShow("#message", "' . addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La fiche %s a correctement &eacute;t&eacute; %s', 'evarisk') . '</strong></p>', __('du danger', 'evarisk') . ' "' . stripslashes($_REQUEST['nom_danger']) . '"', $action)) . '");
									setTimeout(\'actionMessageHide("#message")\',7500);

									evarisk("#rightEnlarging").show();
									evarisk("#equilize").click();
									sub_element_type = "' . $_REQUEST['table'] . '";
									leafId = "' . $_REQUEST['id'] . '";
									menu = "";' . $load . '
								});
							</script>';
						echo $messageInfo;
					}break;
					case 'delete':{					/*	Delete an element	*/
						$dangerResult = EvaDanger::deleteDanger($_REQUEST['id']);
					}	break;
				}
				break;
			case TABLE_RISQUE:
				switch($_REQUEST['act'])
				{
					case 'save':
					{
						$idRisque = '';
						$pictureId = isset($_REQUEST['pictureId']) ? (digirisk_tools::IsValid_Variable($_REQUEST['pictureId'])) : '';
						$retourALaLigne = array("\r\n", "\n", "\r");
						$_REQUEST['description'] = !empty($_REQUEST['description'])?str_replace($retourALaLigne, "[retourALaLigne]",$_REQUEST['description']):isset($_REQUEST['follow_up_content']) ? str_replace($retourALaLigne, "[retourALaLigne]",digirisk_tools::IsValid_Variable($_REQUEST['follow_up_content'])) : '';
						$_REQUEST['description'] = str_replace("…", "...",$_REQUEST['description']);
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];
						require_once(EVA_METABOXES_PLUGIN_DIR . 'risque/risquePersistance.php');

						/**	Save recommandation associated to this risk	*/
						$recommandation_ids = isset($_REQUEST['recommandation']) ? $_REQUEST['recommandation'] : '';
						if ( is_array($recommandation_ids) && !empty($recommandation_ids) ) {
							foreach ( $recommandation_ids as $recommandation_id ) {
								$recommandation_efficacite = isset($_REQUEST['recommandation_efficacite']) ? (digirisk_tools::IsValid_Variable($_REQUEST['recommandation_efficacite'])) : '';
								$recommandation_type = isset($_REQUEST['recommandation_type']) ? (digirisk_tools::IsValid_Variable($_REQUEST['recommandation_type'])) : '';
								$recommandation_commentaire = isset($_REQUEST['recommandation_commentaire']) ? (digirisk_tools::IsValid_Variable($_REQUEST['recommandation_commentaire'])) : '';
								$recommandationsinformations = array();
								$recommandationsinformations['id_preconisation'] = $recommandation_id;
								$recommandationsinformations['efficacite'] = $recommandation_efficacite;
								$recommandationsinformations['commentaire'] = $recommandation_commentaire;
								$recommandationsinformations['preconisation_type'] = $recommandation_type;
								$recommandationsinformations['id_element'] = $idRisque;
								$recommandationsinformations['table_element'] = TABLE_RISQUE;
								$recommandationsinformations['status'] = 'valid';
								$recommandationsinformations['date_affectation'] = current_time('mysql', 0);
								$recommandationActionResult = evaRecommandation::saveRecommandationAssociation($recommandationsinformations);
							}
						}

						$follow_up_content = !empty($_REQUEST['follow_up_content']) ? (digirisk_tools::IsValid_Variable($_REQUEST['follow_up_content'])) : '';
						$follow_up_export = isset($_REQUEST['follow_up_export']) ? (digirisk_tools::IsValid_Variable($_REQUEST['follow_up_export'])) : '';
						$follow_up_date = isset($_REQUEST['follow_up_date']) ? (digirisk_tools::IsValid_Variable($_REQUEST['follow_up_date'])) : '';
						if ( !empty($follow_up_content) ) {
							$query =
							$wpdb->prepare(
								"SELECT id_evaluation
								FROM " . TABLE_AVOIR_VALEUR . "
								WHERE id_risque = '%d'
									AND Status = 'Valid'
								ORDER BY id DESC
								LIMIT 1",
								$idRisque
							);
							$evaluation = $wpdb->get_row($query);
							suivi_activite::save(TABLE_AVOIR_VALEUR, $evaluation->id_evaluation, array('commentaire' => $follow_up_content, 'date_ajout' => $follow_up_date, 'export' => $follow_up_export));
						}

						if ($pictureId != '') {
							if ($idRisque > 0) {
								$moreMessage = '
		actionMessageShow("#' . $pictureId . 'content", "' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Le risque a bien &eacute;t&eacute; ajout&eacute;', 'evarisk') . '</strong></p>') . '");
		setTimeout(\'digirisk("#' . $pictureId . 'content").html("");digirisk("#' . $pictureId . 'content").removeClass("updated");\',3000);
		goTo("#' . $pictureId . '");';
							}
							else {
								$moreMessage = '
		actionMessageShow("#' . $pictureId . 'content", "' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Le risque n\'a pas pu &ecirc;tre ajout&eacute;', 'evarisk') . '</strong></p>') . '");
		setTimeout(\'digirisk("#' . $pictureId . 'content").html("");digirisk("#' . $pictureId . 'content").removeClass("updated");\',3000);';
							}

							require_once(EVA_LIB_PLUGIN_DIR . 'photo/evaPhoto.class.php');
							evaPhoto::associatePicture(TABLE_RISQUE, $idRisque, str_replace('picture', '', str_replace('_', '', $pictureId)));
							echo '
<script type="text/javascript" >
	digirisk("#addRiskForPictureText' . $pictureId . '").html("' . __('Ajouter un risque pour cette photo', 'evarisk') . '");
	digirisk("#divDangerContainerSwitchPic' . $pictureId . '").attr("src").replace("collapse", "expand");
	digirisk("#riskAssociatedToPicture' . $pictureId . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
		"post":"true",
		"table":"' . TABLE_RISQUE . '",
		"act":"reloadRiskAssociatedToPicture",
		"idPicture":"' . str_replace('picture', '', str_replace('_', '', $pictureId)) . '"
	});
	' . $moreMessage . '
</script>';
						}
						else {
							echo '<script type="text/javascript" >digirisk(document).ready(function(){digirisk("#formRisque").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true",	"table":"' . TABLE_RISQUE . '", "act":"load", "idRisque": "' . $idRisque . '", "idElement":"' . $idElement . '", "tableElement":"' . $tableElement . '"});})</script>';
// 							require_once(EVA_METABOXES_PLUGIN_DIR . 'risque/risque.php');
// 							echo getFormulaireCreationRisque($tableElement, $idElement, $idRisque);
							exit();
						}
					}
					break;
					case 'associateRiskToPicture':
					{
						$tableElement = isset($_REQUEST['tableElement']) ? (digirisk_tools::IsValid_Variable($_REQUEST['tableElement'])) : '';
						$idElement = isset($_REQUEST['idElement']) ? (digirisk_tools::IsValid_Variable($_REQUEST['idElement'])) : '';
						$oldidPicture = isset($_REQUEST['oldidPicture']) ? (digirisk_tools::IsValid_Variable($_REQUEST['oldidPicture'])) : '';
						$idPhoto = isset($_REQUEST['idPicture']) ? (digirisk_tools::IsValid_Variable($_REQUEST['idPicture'])) : '';

						/*	Unassociate the risk to the picture	*/
						if ($oldidPicture != '') {
							evaPhoto::unAssociatePicture($tableElement, $idElement, str_replace('picture', '', str_replace('_', '', $oldidPicture)));
							echo '
<script type="text/javascript" >
	digirisk("#riskAssociatedToPicture' . $oldidPicture . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
		"post":"true",
		"table":"' . TABLE_RISQUE . '",
		"act":"reloadRiskAssociatedToPicture",
		"idPicture":"' . str_replace('picture', '', str_replace('_', '', $oldidPicture)) . '"
	});
</script>';
						}
						/*	Associate the risk to the picture	*/
						$associateResult = evaPhoto::associatePicture($tableElement, $idElement, str_replace('picture', '', str_replace('_', '', $idPhoto)));
						echo '
<script type="text/javascript" >
	digirisk("#riskAssociatedToPicture' . $idPhoto . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
		"post":"true",
		"table":"' . TABLE_RISQUE . '",
		"act":"reloadRiskAssociatedToPicture",
		"idPicture":"' . str_replace('picture', '', str_replace('_', '', $idPhoto)) . '"
	});
	jQuery.post("' . admin_url( 'admin-ajax.php' ) . '", {action: "digi_ajax_reload_unassociated_risk_to_pics", tableElement: "' . $_REQUEST['table_element_parent'] . '", idElement: "' . $_REQUEST['id_element_parent'] . '",}, function (response) { jQuery("#digi_unassociated_risk_container").html(response); } );
</script>';
					}
					break;
					case 'unAssociatePicture': {
						$tableElement = isset($_REQUEST['tableElement']) ? (digirisk_tools::IsValid_Variable($_REQUEST['tableElement'])) : '';
						$idElement = isset($_REQUEST['idElement']) ? (digirisk_tools::IsValid_Variable($_REQUEST['idElement'])) : '';
						$idPhoto = isset($_REQUEST['idPicture']) ? (digirisk_tools::IsValid_Variable($_REQUEST['idPicture'])) : '';

						/*	Unassociate the risk to the picture	*/
						if($idPhoto != '') {
							evaPhoto::unAssociatePicture($tableElement, $idElement, $idPhoto);

							/**	Get the parent element of current risk	*/
							$query = $wpdb->prepare( "SELECT id_element, nomTableElement FROM " . TABLE_RISQUE . " WHERE id = %d", $idElement);
							$parent_infos = $wpdb->get_row( $query );

							echo '
<script type="text/javascript" >
	if(digirisk("#riskAssociatedToPicturepicture_' . $idPhoto . '_")){
		digirisk("#riskAssociatedToPicturepicture_' . $idPhoto . '_").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
			"post":"true",
			"table":"' . TABLE_RISQUE . '",
			"act":"reloadRiskAssociatedToPicture",
			"idPicture":"' . str_replace('picture', '', str_replace('_', '', $idPhoto)) . '"
		});
	}
	jQuery.post("' . admin_url( 'admin-ajax.php' ) . '", {action: "digi_ajax_reload_unassociated_risk_to_pics", tableElement: "' . $parent_infos->nomTableElement . '", idElement: "' . $parent_infos->id_element . '",}, function (response) { jQuery("#digi_unassociated_risk_container").html(response); } );
	if(digirisk("#associatedPictureContainer")){
		digirisk("#associatedPictureContainer").html("");
	}
</script>';
						}
					}
					break;
					case 'reloadRiskAssociatedToPicture':
						$idPicture = isset($_REQUEST['idPicture']) ? (digirisk_tools::IsValid_Variable($_REQUEST['idPicture'])) : '';
						echo Risque::getRisqueAssociePhoto($idPicture);
					break;
					case 'delete':
						require_once(EVA_METABOXES_PLUGIN_DIR . 'risque/risquePersistance.php');
					break;
					case 'load':
						require_once(EVA_METABOXES_PLUGIN_DIR . 'risque/risque.php');
						echo getFormulaireCreationRisque($_REQUEST['tableElement'], $_REQUEST['idElement'], $_REQUEST['idRisque']);
					break;
					case 'reloadVoirRisque' :
						require_once(EVA_METABOXES_PLUGIN_DIR . 'risque/risque.php');
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];
						echo getVoirRisque($tableElement, $idElement);
					break;
					case 'voirRisqueLigne' :
						require_once(EVA_METABOXES_PLUGIN_DIR . 'documentUnique/documentUnique.php');
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];
						echo eva_documentUnique::bilanRisque($tableElement, $idElement, 'ligne');
					break;
					case 'reloadRiskForm' :
						require_once(EVA_METABOXES_PLUGIN_DIR . 'risque/risque.php');
						echo getFormulaireCreationRisque($_REQUEST['tableElement'], $_REQUEST['idElement']);
					break;
					case 'loadAdvancedRiskForm' :
						require_once(EVA_METABOXES_PLUGIN_DIR . 'risque/risque.php');
						echo getAvancedFormulaireCreationRisque($_REQUEST['tableElement'], $_REQUEST['idElement']);
					break;
					case 'voirRisqueUnite' :
						require_once(EVA_METABOXES_PLUGIN_DIR . 'documentUnique/documentUnique.php');
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];
						echo eva_documentUnique::bilanRisque($tableElement, $idElement, 'unite');
					break;
					case 'addRiskByPicture':
					{
						$addRiskByPictureForm = '';
						$tableElement = isset($_REQUEST['tableElement']) ? (digirisk_tools::IsValid_Variable($_REQUEST['tableElement'])) : '';
						$idElement = isset($_REQUEST['idElement']) ? (digirisk_tools::IsValid_Variable($_REQUEST['idElement'])) : '';
						$currentId = isset($_REQUEST['currentId']) ? (digirisk_tools::IsValid_Variable($_REQUEST['currentId'])) : '';

						require_once(EVA_METABOXES_PLUGIN_DIR . 'risque/risque.php');
						echo  getFormulaireCreationRisque($tableElement, $idElement, '', $currentId);
					}
					break;

					case 'loadAssociatedTask': {
						$id_risque = (!empty($_REQUEST['idRisque'])?digirisk_tools::IsValid_Variable($_REQUEST['idRisque']):'');
						$extra = (!empty($_REQUEST['extra'])?digirisk_tools::IsValid_Variable($_REQUEST['extra']):'');
						$idElement = (!empty($_REQUEST['idElement'])?digirisk_tools::IsValid_Variable($_REQUEST['idElement']):'');
						$tableElement = (!empty($_REQUEST['tableElement'])?digirisk_tools::IsValid_Variable($_REQUEST['tableElement']):'');
						$idTable = 'loadAssociatedTask_' . $extra . '_' . $id_risque;
						/*	Get the different corrective actions for the actual risk	*/
						$actionsCorrectives = '';
						$risk_list = array($id_risque => $id_risque, );
						$tachesActionsCorrectives = actionsCorrectives::get_activity_associated_to_risk('', '', $risk_list, array('hasPriority' => "yes"));
						if(count($tachesActionsCorrectives[0]) > 0){
							$hasActions = true;
							$spacer = '';
							$actionsCorrectives .= '
				<div class="hide" id="riskAssociatedTask' . $id_risque . '" title="' . __('D&eacute;tails d\'une action corrective', 'evarisk') . '" >&nbsp;</div>
				<table id="' . $idTable . '" cellspacing="0" class="widefat post fixed">
					<thead>
						<tr class="white_background" >
							<th >' . sprintf(__('Actions correctives associ&eacute;es au risque %s', 'evarisk'), ELEMENT_IDENTIFIER_R . $id_risque) . '</th>
							<th >' . __('Informations', 'evarisk') . '</th>
							<th class="CorrectivActionFollowStateActionColumn" >&nbsp;</th>
						</tr>
					</thead>
					<tbody>';
							foreach($tachesActionsCorrectives[0] as $taskDefinition){
								$monCorpsTable = '';
								$racine = Arborescence::getRacine(TABLE_TACHE, " id='" . $taskDefinition->id . "' ");
								$task_already_controled = false;
								$task_associated_element = EvaTask::get_element_link_to_task($taskDefinition->id);
								if(is_array($task_associated_element) && (count($task_associated_element) > 0)){
									foreach($task_associated_element as $link_information){
										$task_already_controled = false;
										if($link_information->wasLinked == 'after'){
											$task_already_controled = true;
										}
									}
								}

								if(current_user_can('digi_control_task')){
									$task_picto = '<img style="width:' . TAILLE_PICTOS . ';" id="risque-' . $taskDefinition->id . '-FAC" src="' . PICTO_LTL_ADD_ACTION . '" alt="' . __('Contr&ocirc;le d\'action corrective', 'evarisk') . '" title="' . __('Contr&ocirc;le d\'action corrective', 'evarisk') . '" class="correctiv_action_control" />';
								}
								if($task_already_controled){
									$task_picto = '<img class="digi_normal_pointer" src="' . EVA_MESSAGE_SUCCESS . '" alt="' . __('taches controllee', 'evarisk') . '" title="' . __('T&acirc;che d&eacute;j&agrave; controll&eacute;e', 'evarisk') . '" />';
								}
								elseif((digirisk_options::getOptionValue('affecter_uniquement_tache_soldee_a_un_risque') == 'oui') && !in_array($racine->ProgressionStatus, array('Done', 'DoneByChief'))){
									$task_picto = '&nbsp;';
								}

								$actionsCorrectives .= '
						<tr id="node-' . $idTable . '-' . $racine->id . '" class="parent racineArbre">
							<td id="tdRacine' . $idTable . ELEMENT_IDENTIFIER_T . $racine->id . '" class="loadAssociatedTask_elt_name" >' . ELEMENT_IDENTIFIER_T . $racine->id . '&nbsp;-&nbsp;' . $racine->nom . '</td>
							<td id="tdInfoRacine' . $idTable . ELEMENT_IDENTIFIER_T . $racine->id . '">' . $racine->avancement . '%&nbsp;(' . actionsCorrectives::check_progression_status_for_output($racine->ProgressionStatus) . ')&nbsp;-&nbsp;&nbsp;' . __('D&eacute;but', 'evarisk') . '&nbsp;' . mysql2date('d M Y', $racine->dateDebut, true) . '&nbsp;-&nbsp;' . __('Fin', 'evarisk') . '&nbsp;' . mysql2date('d M Y', $racine->dateFin, true) . '</td>
							<td id="tdActionRacine' . $idTable . ELEMENT_IDENTIFIER_T . $racine->id . '" class="CorrectivActionFollowStateActionColumn" >';
							if(current_user_can('digi_view_detail_task')){
								$actionsCorrectives .= '<img src="' . str_replace('.png', '_vs.png', PICTO_VIEW) . '" alt="view_details" id="' . TABLE_TACHE . '_t_elt_' . $racine->id . '" class="view_correctiv_action" />';
							}
							$actionsCorrectives .= $task_picto . '</td>
						</tr>';

								$elements = Arborescence::getFils(TABLE_TACHE, $racine, "nom ASC");
								$actionsCorrectives .= EvaDisplayDesign::build_tree($elements, $racine, TABLE_TACHE, 'Info', $idTable, true);
							}
							$actionsCorrectives .= '
					</tbody>
				</table>
				<script type="text/javascript">
					digirisk(document).ready(function(){
						/*	Change the simple table in treetable	*/
						jQuery("#' . $idTable . '").treeTable();
						jQuery("#' . $idTable . ' tr.parent").each(function(){
							var childNodes = jQuery("table#' . $idTable . ' tbody tr.child-of-" + jQuery(this).attr("id"));
							if(childNodes.length > 0){
								jQuery(this).addClass("aFils");
								var premierFils = jQuery("table#' . $idTable . ' tbody tr.child-of-" + jQuery(this).attr("id") + ":first").attr("id");
								if(premierFils != premierFils.replace(/node/g,"")){
									jQuery(this).addClass("aFilsNoeud");
								}
								else{
									jQuery(this).addClass("aFilsFeuille");
								}
							}
							else{
								jQuery(this).removeClass("aFils");
								jQuery(this).addClass("sansFils");
							}
						});

						/*	Add the dialog box in order to see correctiv action details	*/
						jQuery("#riskAssociatedTask' . $id_risque . '").dialog({
							"autoOpen":false,
							"height":460,
							"width":800,
							"modal":true,
							"buttons":{
								"' . __('fermer', 'jQuery') . '": function(){
									jQuery(this).dialog("close");
								}
							},
							"close":function(){
								jQuery("#riskAssociatedTask' . $id_risque . '").html("");
							}
						});

						/*	Add the action when user click on the 	*/
						jQuery(".view_correctiv_action").click(function(){
							jQuery("#riskAssociatedTask' . $id_risque . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
								"post": "true",
								"table": "' . TABLE_TACHE . '",
								"id": jQuery(this).attr("id"),
								"act": "loadDetails"
							});

							jQuery("#riskAssociatedTask' . $id_risque . '").dialog("open");
						});
						jQuery(".view_correctiv_action_sub_task").click(function(){
							jQuery("#riskAssociatedTask' . $id_risque . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
								"post": "true",
								"table": "' . TABLE_TACHE . '",
								"id": jQuery(this).attr("id"),
								"act": "loadDetails"
							});

							jQuery("#riskAssociatedTask' . $id_risque . '").dialog("open");
						});

						jQuery(".correctiv_action_control").click(function(){
							jQuery("#formRisque").html(jQuery("#loadingImg").html());
							hideExtraTab();
							jQuery("#ongletControlerActionDemandee").show();
							tabChange("#formRisque", "#ongletControlerActionDemandee");
							jQuery("#formRisque").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
								"post":"true",
								"table":"' . TABLE_RISQUE . '",
								"act":"load",
								"idRisque": ' . $id_risque . ',
								"idElement":"' . $idElement . '",
								"tableElement":"' . $tableElement . '",
								"task_to_associate":jQuery(this).attr("id").replace("risque-", "").replace("-FAC", ""),
								"sub_action":"control_asked_action"
							});
							jQuery("#divFormRisque").show();
							jQuery("#risqManagementselector div").each(function(){
								jQuery(this).hide();
							});
						});
					});
				</script>';
						}
						else{
							$actionsCorrectives .= __('Aucune action n\'est associ&eacute;e &agrave; ce risque', 'evarisk');
						}

						echo $actionsCorrectives;
					}
					break;

					case 'load_quote_validation':
						$risque = Risque::getRisque($_REQUEST['idProvenance']);
						$vars = array();
						foreach($_REQUEST['vars'] as $var){
							$current_var = explode('[', $var['var']);
							$vars[str_replace(']', '', $current_var[1])] = $var['val'];
						}

						$output = __('Cotation actuelle', 'evarisk') . '
<div id="current_risk_summary" class="current_risk_summary_task" >
	' . Risque::getTableQuotationRisque($_REQUEST['tableProvenance'], $_REQUEST['idProvenance'], 'current_quote_control') . '
</div><br/>' . __('Nouvelle cotation', 'evarisk') . '
<div class="new_risk_summary_task" >
	' . Risque::getTableQuotationRisque($_REQUEST['tableProvenance'], $_REQUEST['idProvenance'], 'new_quote_control', array('date_to_take' => current_time('mysql', 0), 'value_to_take' => $vars, 'description_to_take' => $_REQUEST['new_description'])) . '
</div>
<br/>
<input type="button" class="button-secondary alignright" id="cancel_new_quote" value="' . __('Annuler', 'evarisk') . '" />
<input type="button" class="button-primary alignright" id="confirm_new_quote" value="' . __('Confirmer', 'evarisk') . '" />
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#cancel_new_quote").click(function(){
			jQuery("#add_picture_alert").dialog("close");
		});
		jQuery("#confirm_new_quote").click(function(){
			jQuery("#add_picture_alert").dialog("close");
			jQuery("#informationGeneralesActivite #act").val("add_control_picture");
			jQuery("#informationGeneralesActivite").submit();
		});
	});
</script>';

						echo $output;
					break;

					case 'copy_risk':
						$new_element = explode('-_-', $_REQUEST['new_element']);
						/*	Get risk last evaluation	*/
						$query = $wpdb->prepare(
"SELECT R.*,
	R_EVAL.id_variable, R_EVAL.valeur, R_EVAL.idEvaluateur, R_EVAL.date, R_EVAL.Status
FROM " . TABLE_RISQUE . " AS R
	INNER JOIN " . TABLE_AVOIR_VALEUR . " AS R_EVAL ON ((R_EVAL.id_risque = R.id) AND (R_EVAL.Status = 'Valid'))
WHERE R.id = %d", $_REQUEST['id_risque']);
						$risq_info = $wpdb->get_results($query);

						/*	Read risk informations for new risk save	*/
						foreach($risq_info as $risk){
							$variables[$risk->id_variable] = $risk->valeur;
						}
						$idDanger = $risq_info[0]->id_danger;
						$idMethode = $risq_info[0]->id_methode;
						$tableElement = $new_element[0];
						$idElement = $new_element[1];
						$description = '';//$risq_info[0]->commentaire;
						$date_debut = '';//$risq_info[0]->dateDebutRisque;
						$date_fin = '';//$risq_info[0]->dateFinRisque;
						$histo = false;
						$new_risk = Risque::saveNewRisk('', $idDanger, $idMethode, $tableElement, $idElement, $variables, $description, $histo, $date_debut, $date_fin);

						$message = '';
						$more_action = '';
						if($new_risk > 0){
							$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;' . __('Le risque a correctement &eacute;t&eacute; copi&eacute;', 'evarisk'));
							$more_action = '
	digirisk(document).ready(function(){
		jQuery("#ongletVoirLesRisques").click();
	});';
						}
						else{
							$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;' . __('Le risque n\'a pas pu &ecirc;tre copi&eacute;', 'evarisk') . $risk_var_nb .'_'. $real_risk_nb);
						}

						echo '
<script type="text/javascript" >
	actionMessageShow("#messagewp_eva__risque", "' . $message . '");
	setTimeout(\'actionMessageHide("#messagewp_eva__risque")\',7500);
	jQuery("#loading_picto_risk_mover_container").html("");
	jQuery("#loading_picto_risk_mover_container").hide();
	jQuery("#button_risk_mover_container").show();' . $more_action . '
</script>';
					break;
					case 'move_risk':{
						$new_element = explode('-_-', $_REQUEST['new_element']);
						$update_result = $wpdb->update(TABLE_RISQUE, array('last_moved_date' => current_time('mysql', 0), 'id_element' => $new_element[1], 'nomTableElement' => $new_element[0]), array('id' => $_REQUEST['id_risque']));

						$message = '';
						$more_action = '';
						if(($update_result == 1) || ($update_result == 0)){
							$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;' . __('Le risque a correctement &eacute;t&eacute; d&eacute;plac&eacute;', 'evarisk'));
							$more_action = '
	digirisk(document).ready(function(){
		jQuery("#ongletVoirLesRisques").click();
	});';
						}
						else{
							$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;' . __('Le risque n\'a pas pu &ecirc;tre d&eacute;plac&eacute;', 'evarisk'));
						}

						echo '
<script type="text/javascript" >
	actionMessageShow("#messagewp_eva__risque", "' . $message . '");
	setTimeout(\'actionMessageHide("#messagewp_eva__risque")\',7500);
	jQuery("#loading_picto_risk_mover_container").html("");
	jQuery("#loading_picto_risk_mover_container").hide();
	jQuery("#button_risk_mover_container").show();' . $more_action . '
</script>';
					}break;
				}
				break;
			case TABLE_METHODE:
				switch($_REQUEST['act'])
				{
					case 'load_method_variable_container':{
						echo MethodeEvaluation::evaluation_method_management_content($_REQUEST);
					}break;
					case 'reloadVariables':{
						$idMethode = (!empty($_REQUEST['idMethode'])?digirisk_tools::IsValid_Variable($_REQUEST['idMethode'], ''):'');
						$idRisque = (!empty($_REQUEST['idRisque'])?digirisk_tools::IsValid_Variable($_REQUEST['idRisque']):'');
						$idElement = (!empty($_REQUEST['idElement'])?digirisk_tools::IsValid_Variable($_REQUEST['idElement']):'');
						$tableElement = (!empty($_REQUEST['tableElement'])?digirisk_tools::IsValid_Variable($_REQUEST['tableElement']):'');
						$formId = (isset($_REQUEST['formId'])) ? digirisk_tools::IsValid_Variable($_REQUEST['formId']) : '';
						unset($valeurInitialVariables);
						$is_closed = false;
						if($idRisque != ''){
							$risque = Risque::getRisque($idRisque);
							foreach($risque as $ligneRisque){
								$idMethode = ($idMethode == '') ? $ligneRisque->id_methode : $idMethode;
								$valeurInitialVariables[$ligneRisque->id_variable] = $ligneRisque->valeur;
							}
							if ( $risque[0]->risk_status == 'closed' ) {
								$is_closed = true;
							}
						}
						$variables = MethodeEvaluation::getDistinctVariablesMethode($idMethode);
						$affichage = '<div class="evaluation_var_slider_container" >';
						$vars_scripts = '';
						foreach($variables as $variable){
							$valeurInitialVariable = 0;
							if(!empty($valeurInitialVariables[$variable->id])){
								$valeurInitialVariable = $valeurInitialVariables[$variable->id];
							}
							else{
								$valeurInitialVariable = $variable->min;
							}

							{//Affichage de la variable
								$query = $wpdb->prepare("SELECT * FROM " . TABLE_VALEUR_ALTERNATIVE . " WHERE id_variable = %d and Status = %s", $variable->id, 'Valid');
								$existing_alternativ_vars = $wpdb->get_results($query);
								if ( !empty($existing_alternativ_vars) ) {
									foreach ( $existing_alternativ_vars as $alternativ ) {
										$affichage .= '<input type="hidden" value="' . $alternativ->valeurAlternative . '" id="' . $formId . 'slider-range-min' . $variable->id . '_alternativ_' . $alternativ->valeur . '" name="' . $formId . 'slider-range-min' . $variable->id . '_alternativ_' . $alternativ->valeur . '" />';
									}
								}
								if ( $variable->affichageVar == "slide" ) {
									if ( !$is_closed ) {
										$vars_scripts .= '
										jQuery("#' . $formId . 'slider-range-min' . $variable->id . '").slider({
											range: "min",
											value: ' . $valeurInitialVariable . ',
											min:	' . $variable->min . ',
											max:	' . $variable->max . ',
											slide: function(event, ui){
												if ( jQuery("#' . $formId . 'slider-range-min' . $variable->id . '_alternativ_" + ui.value).val() != undefined ) {
													jQuery("#' . $formId . '_digi_eval_method_var_' . $variable->id . '").val( jQuery("#' . $formId . 'slider-range-min' . $variable->id . '_alternativ_" + ui.value).val() );
												}
												else{
													jQuery("#' . $formId . '_digi_eval_method_var_' . $variable->id . '").val(ui.value);
												}
											},
											stop: function(event, ui){
												live_risk_calcul();
											}
										});
										jQuery("#' . $formId . 'var' . $variable->id . 'FormRisque").val(jQuery("#' . $formId . 'slider-range-min' . $variable->id . '").slider("value"));';
									}
									$affichage .= '
									<label for="' . $formId . '_digi_eval_method_var_' . $variable->id . '">' . ELEMENT_IDENTIFIER_V . $variable->id . ' - ' . $variable->nom . ' :</label>
									<input value="' . $valeurInitialVariable . '" type="text" class="sliderValue digi_method_var_value" readonly="readonly" id="' . $formId . '_digi_eval_method_var_' . $variable->id . '" name="' . $formId . 'variables[' . $variable->id . ']" />
									<div id="' . $formId . 'slider-range-min' . $variable->id . '" class="slider_variable"></div>';
								}
								else {
                                	$affichage .= '<p class="digi_question_penibilite" >' . ELEMENT_IDENTIFIER_V . $variable->id . ' - '.$variable->questionTitre.'</p>';

                                 	$tableau = unserialize($variable->questionVar);
                                 	$i = $variable->min;
                                 	foreach ($tableau as $t) {
                                 		if ( !$is_closed ) {
                                 			$checked = ($i == $valeurInitialVariable) ? ' checked="checked"' : '';
                                 			$affichage.= '<input type="radio" id="' . $formId . '_digi_eval_method_var_' . $variable->id . '-x-' . $i . '"'.$checked.' name="' . $formId . 'variables[' . $variable->id . ']" class="digi_method_var_value score_risque_checkbox" value="'.$i.'" /><label for="' . $formId . '_digi_eval_method_var_' . $variable->id . '-x-' . $i . '" >'.(strpos($t['question'], '%s') ? sprintf($t['question'], $t['seuil']) : $t['question']).'</label><br/>';
                                 		}
                                 		else if ( $i == $valeurInitialVariable ) {
                                 			$affichage.=(strpos($t['question'], '%s') ? sprintf($t['question'], $t['seuil']) : $t['question']);
                                 		}
                                 		$i++;
                                 	}
			                    	$affichage .='<input type="hidden" id="' . $formId . '_digi_eval_method_var_' . $variable->id . '" value="" />
                                 	<script type="text/javascript" >
                                 		jQuery(".score_risque_checkbox").change( function() {
                                 			var score = parseInt(jQuery(this).val());
                                 			jQuery("#' . $formId . '_digi_eval_method_var_' . $variable->id . '").val(score);
                                 			live_risk_calcul();
                                 		 });
                                 	</script>';
								}
							}
						}
						$affichage .= '</div>
								<script type="text/javascript">
									digirisk(document).ready(function() {
										' . $vars_scripts . '
									});
								</script>';
						$quotation = 0;
						if ( !empty($risque) ) {
							$score = Risque::getScoreRisque($risque);
							$quotation = Risque::getEquivalenceEtalon($idMethode, $score);
						}
						$niveauSeuil = Risque::getSeuil($quotation);
						$affichage .= '<input type="hidden" value="' . $niveauSeuil . '" id="niveau_seuil_courant" /><input type="hidden" value="' . $quotation . '" id="current_qr" /><div class="qr_current_risk" ><div class="alignleft" >' . __('Q. risque', 'evarisk') . '&nbsp;:</div><div class="qr_risk Seuil_' . $niveauSeuil . '" >' . $quotation . '</div><div class="clear" ></div></div>';


						/*	START - Get the explanation picture if exist - START	*/
						$methodExplanationPicture = '';
						$defaultPicture = evaPhoto::getMainPhoto(TABLE_METHODE, $idMethode);
						if(($defaultPicture != '') && (is_file(EVA_GENERATED_DOC_DIR . $defaultPicture))){
							$methodExplanationPicture = '<img src="' . EVA_GENERATED_DOC_URL . $defaultPicture . '" alt="evaluation method explanation picture" class="digi_eval_pic" />';
						}
						/*	END - Get the explanation picture if exist - END	*/

						$method_operator_date = null;
						if(!empty($risque)){
							$method_operator_date = $risque[0]->date;
						}

						$listeOperateur = MethodeEvaluation::getOperateursMethode($idMethode, $method_operator_date);
						$listeVariables = MethodeEvaluation::getVariablesMethode($idMethode, $method_operator_date);
						$formule = '';
						foreach($listeVariables as $index => $variable){
							$query = $wpdb->prepare("SELECT * FROM " . TABLE_VALEUR_ALTERNATIVE . " WHERE id_variable = %d and Status = %s", $variable->id, 'Valid');
							$formule .= 'parseInt(jQuery("#' . $formId . '_digi_eval_method_var_' . $variable->id . '").val())';
							$formule .= (isset($listeOperateur[$index]->operateur) && ($listeOperateur[$index]->operateur!= '')) ? ' ' . $listeOperateur[$index]->operateur . ' ' : '';
						}
						if($formule == ''){
							$formule = '""';
						}
						echo '
<div class="eval_method_var" >' . $affichage . '</div><div class="eval_method_explanation" >' . $methodExplanationPicture . '</div>';
						if(!empty($risque) ) {
							echo '
<div class="clear risq_mover_container" >
	<div id="risq_mover_title" class="alignright" >' . __('D&eacute;placer ce risque', 'evarisk') . '</div>
	<div id="risq_mover" class="clear hide" >
		<input type="hidden" name="risk_to_move" id="risk_to_move" value="' . $idRisque . '" />
		<input type="hidden" name="receiver_element" id="receiver_element" value="" />
		<div class="clear auto-search-container" >
			<input class="auto-search-input" type="text" id="search_element" placeholder="' . __('Rechercher dans la liste des &eacute;l&eacute;ments', 'evarisk') . '" />
			<span class="auto-search-ui-icon ui-icon" >&nbsp;</span>
		</div>
		<div class="clear hide alignright" id="loading_picto_risk_mover_container" >&nbsp;</div>
		<div class="clear" id="button_risk_mover_container" >';
			if ( !$is_closed ) {
				echo '<input type="buttton" name="move_risk" id="move_risk" value="' . __('D&eacute;placer', 'evarisk') . '" class="button-secondary clear alignright" />';
			}
			echo '<input type="buttton" name="copy_risk" id="copy_risk" value="' . __('Copier', 'evarisk') . '" class="button-secondary alignright" />
		</div>
	</div>
</div>';
						}

						/**	Add possibility to define a date for risk evaluation	*/
						$date_shortcode = '';
						$employer_date = null;
						switch ( $tableElement ) {
							case TABLE_GROUPEMENT:
								$query = $wpdb->prepare( "SELECT typeGroupement, creation_date_of_society FROM " . TABLE_GROUPEMENT . " WHERE id = %d", $idElement );
								$current_groupement_infos = $wpdb->get_row( $query );
								if ( ($current_groupement_infos->typeGroupement == 'employer') && !empty($current_groupement_infos->creation_date_of_society) && ($current_groupement_infos->creation_date_of_society != '0000-00-00')) {
									$employer_date = $current_groupement_infos->creation_date_of_society;
								}
								else {
									$employer_date = EvaGroupement::get_closest_employer( $idElement );
								}
								break;
							case TABLE_UNITE_TRAVAIL:
								$query = $wpdb->prepare("SELECT id_groupement FROM " . TABLE_UNITE_TRAVAIL . " WHERE id = %d", $idElement);
								$current_work_unit_parent = $wpdb->get_var( $query );
								$query = $wpdb->prepare( "SELECT typeGroupement, creation_date_of_society FROM " . TABLE_GROUPEMENT . " WHERE id = %d", $current_work_unit_parent );
								$current_groupement_infos = $wpdb->get_row( $query );
								if ( ($current_groupement_infos->typeGroupement == 'employer') && !empty($current_groupement_infos->creation_date_of_society) && ($current_groupement_infos->creation_date_of_society != '0000-00-00')) {
									$employer_date = $current_groupement_infos->creation_date_of_society;
								}
								else {
									$employer_date = EvaGroupement::get_closest_employer( $current_work_unit_parent );
								}
								break;
						}
						if ( !empty($employer_date) && ($employer_date != '0000-00-00 00:00:00') ) {
							$date_shortcode = ' / <input type="hidden" value="' . substr( $employer_date, 0, -3 ) . '" id="digi_parent_creation_date" /><span id="digi_use_parent_date_for_risk" style="font-style: italic;cursor: pointer;" title="' . __('Date de cr&eacute;ation du groupement employeur', 'evarisk') . '" >' . __('C', 'evarisk') . '</span>';
						}
						echo '
<div class="clear" ></div>
<ul class="risk_date_change wpdigi-risk-eval-rsiksdate wpdigi-risk-eval-options" >
	<li>
		<div>
			' . __('Début du risque', 'evarisk') . '<br/>
			<input type="text" name="digi_risk_date_start" id="digi_risk_date_start" value=""' . ($is_closed ? ' disabled="disabled" class="digi_input_date_disable" ' : '' ) . ' />' . (!$is_closed ? '<span title="' . __( 'Maintenant', 'evarisk' ) . '" style="font-style: italic;cursor: pointer;" id="date_for_digi_risk_date_start" class="digi_use_current_date_for_risk" >' . __('M', 'evarisk') . '</span>' . $date_shortcode : '') . '
			' . ( !empty($employer_date) && ($employer_date != '0000-00-00 00:00:00') && !empty($risque[0]->dateDebutRisque) && ( $risque[0]->dateDebutRisque != '0000-00-00 00:00:00') && (strtotime( $risque[0]->dateDebutRisque ) < strtotime( $employer_date )) ? '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'veille-no-reponse.gif" alt="' . __('La date de d&eacute;but de risque est sup&eacute;rieure &agrave; la date de cr&eacute;ation du groupement employeur', 'evarisk') . '" title="' . __('La date de d&eacute;but de risque est sup&eacute;rieure &agrave; la date de cr&eacute;ation du groupement employeur', 'evarisk') . '" />' : '' ). '
		</div>
	</li>
					<!--
	<li>
		<div>
			' . __('Date d\'&eacute;valuation du risque', 'evarisk') . '<br/>
			<input type="text" name="digi_risk_evaluation_date" id="digi_risk_evaluation_date" value=""' . ($is_closed ? ' disabled="disabled" class="digi_input_date_disable" ' : '' ) . ' />
			' . (!$is_closed ? '<br/><span style="font-style: italic;cursor: pointer;" id="date_for_digi_risk_evaluation_date" class="digi_use_current_date_for_risk" >' . __('Maintenant', 'evarisk') . '</span>' : '') . '
		</div>
	</li>
					-->
	<li>
		<div>
			' . __('Fin du risque', 'evarisk') . '<br/>
			<input type="text" name="digi_risk_end_start" id="digi_risk_end_start" value=""' . ($is_closed ? ' disabled="disabled"' : '' ) . ' />' . (!$is_closed ? '<span title="' . __( 'Maintenant', 'evarisk' ) . '" style="font-style: italic;cursor: pointer;" id="date_for_digi_risk_end_start" class="digi_use_current_date_for_risk" >' . __('M', 'evarisk') . '</span>' : '') . '
			' . ( !empty($risque[0]->dateDebutRisque) && ($risque[0]->dateDebutRisque != '0000-00-00 00:00:00') && !empty($risque[0]->dateFinRisque) && ( $risque[0]->dateFinRisque != '0000-00-00 00:00:00') && (strtotime( $risque[0]->dateFinRisque ) < strtotime( $risque[0]->dateDebutRisque )) ? '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'veille-no-reponse.gif" alt="' . __('La date de fin de risque est sup&eacute;rieure &agrave; la date de cr&eacute;ation du groupement employeur', 'evarisk') . '" title="' . __('La date de fin de risque est sup&eacute;rieure &agrave; la date de d&eacute;but du risque', 'evarisk') . '" />' : ( !empty($employer_date) && ($employer_date != '0000-00-00 00:00:00') && !empty($risque[0]->dateFinRisque) && ( $risque[0]->dateFinRisque != '0000-00-00 00:00:00') && (strtotime( $risque[0]->dateFinRisque ) < strtotime( $employer_date )) ? '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'veille-no-reponse.gif" alt="' . __('La date de fin de risque est sup&eacute;rieure &agrave; la date de cr&eacute;ation du groupement employeur', 'evarisk') . '" title="' . __('La date de d&eacute;but de risque est sup&eacute;rieure &agrave; la date de cr&eacute;ation du groupement employeur', 'evarisk') . '" />' : '' ) ) . '
		</div>
	</li>
</ul>';


						$locale = preg_replace('/([^_]+).+/', '$1', get_locale());
						$locale = ($locale == 'en') ? '' : $locale;

						$risk_start_date_to_use = substr( current_time( 'mysql', 0), 0, -3 );
						$options = get_option('digirisk_options');
						if ( !empty($options['digi_risk_start_date']) && ($options['digi_risk_start_date'] == 'employerCreationDate') && !empty($employer_date) ) {
							$risk_start_date_to_use = $employer_date;
						}
						$start_date_min_date = '';
						if ( !empty($employer_date) ) {
							$start_date_min_date = '
		minDate: "' . $employer_date . '",';
						}
						$end_date_min_date = '';
						if ( !empty($employer_date) ) {
							$end_date_min_date = '
		minDate: "' . $employer_date . '",';
						}
						else if ( !empty($risque[0]) && !empty($risque[0]->dateDebutRisque) && ($risque[0]->dateDebutRisque != '0000-00-00 00:00:00') ) {
							$end_date_min_date = '
		minDate: "' . $risque[0]->dateDebutRisque . '",';
						}
						$current_date = substr( current_time('mysql', 0), 0, -3 );

						echo '
<script type="text/javascript" >
	var text = "' . __('&#77;aintenant', 'evarisk') . '";
	digirisk(document).ready(function(){
		jQuery("#risq_mover_title").click(function(){
			jQuery("#risq_mover").toggle();
		});

		jQuery(".digi_use_current_date_for_risk").click(function(){
			var put_date = true;
			if ( jQuery("#digi_parent_creation_date").val() != undefined ) {
				if ( jQuery("#digi_parent_creation_date").val() + ":00" > "' . $current_date . ':00" ) {
					if ( jQuery( this ).attr( "id" ) == "date_for_digi_risk_date_start" ) {
						alert( digi_html_accent_for_js( "' . __('Vous ne pouvez pas mettre une date de d&eacute;but de risque inf&eacute;rieure &agrave; la date de cr&eacute;ation du groupement employeur', 'evarisk') . '" ) );
					}
					else if ( jQuery( this ).attr( "id" ) == "date_for_digi_risk_end_start" ) {
						alert( digi_html_accent_for_js( "' . __('Vous ne pouvez pas mettre une date de fin de risque inf&eacute;rieure &agrave; la date de cr&eacute;ation du groupement employeur', 'evarisk') . '" ) );
					}
					put_date = false;
				}
			}

			if ( (jQuery( this ).attr( "id" ) == "date_for_digi_risk_end_start") && ( jQuery("#digi_risk_date_start").val() + ":00" > "' . $current_date . ':00" ) ) {
				alert( digi_html_accent_for_js( "' . __('Vous ne pouvez pas mettre une date de fin de risque inf&eacute;rieure &agrave; la date de d&eacute;but du risque', 'evarisk') . '" ) );
				put_date = false;
			}

			if ( put_date ) {
				jQuery("#" + jQuery(this).attr("id").replace("date_for_", "") ).val( "' . $current_date . '" );
			}
		});

		jQuery("#digi_use_parent_date_for_risk").click(function(){
			jQuery("#digi_risk_date_start").val( jQuery("#digi_parent_creation_date").val() );
		});

		jQuery.datepicker.regional["fr"] = {
			monthNames: ["' . __('Janvier', 'evarisk') . '","' . __('F&eacute;vrier', 'evarisk') . '","' . __('Mars', 'evarisk') . '","' . __('Avril', 'evarisk') . '","' . __('Mai', 'evarisk') . '","' . __('Juin', 'evarisk') . '", "' . __('Juillet', 'evarisk') . '","' . __('Ao&ucirc;t', 'evarisk') . '","' . __('Septembre', 'evarisk') . '","' . __('Octobre', 'evarisk') . '","' . __('Novembre', 'evarisk') . '","' . __('D&eacute;cembre', 'evarisk') . '"],
			monthNamesShort: ["Jan", "Fev", "Mar", "Avr", "Mai", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            dayNames: ["' . __('Dimanche', 'evarisk') . '", "' . __('Lundi', 'evarisk') . '", "' . __('Mardi', 'evarisk') . '", "' . __('Mercredi', 'evarisk') . '", "' . __('Jeudi', 'evarisk') . '", "' . __('Vendredi', 'evarisk') . '", "' . __('Samedi', 'evarisk') . '"],
			dayNamesShort: ["' . __('Dim', 'evarisk') . '", "' . __('Lun', 'evarisk') . '", "' . __('Mar', 'evarisk') . '", "' . __('Mer', 'evarisk') . '", "' . __('Jeu', 'evarisk') . '", "' . __('Ven', 'evarisk') . '", "' . __('Sam', 'evarisk') . '"],
			dayNamesMin: ["' . __('Di', 'evarisk') . '", "' . __('Lu', 'evarisk') . '", "' . __('Ma', 'evarisk') . '", "' . __('Me', 'evarisk') . '", "' . __('Je', 'evarisk') . '", "' . __('Ve', 'evarisk') . '", "' . __('Sa', 'evarisk') . '"],
		}
    	jQuery.datepicker.setDefaults(jQuery.datepicker.regional["fr"]);
		jQuery.timepicker.regional["fr"] = {
                timeText: "' . __('Heure', 'evarisk') . '",
                hourText: "' . __('Heures', 'evarisk') . '",
                minuteText: "' . __('Minutes', 'evarisk') . '",
                amPmText: ["AM", "PM"],
                currentText: text,
                closeText: "' . __('OK', 'evarisk') . '",
                timeOnlyTitle: "' . __('Choisissez l\'heure', 'evarisk') . '",
                closeButtonText: "' . __('Fermer', 'evarisk') . '",
                nowButtonText: text,
                deselectButtonText: "' . __('D&eacute;s&eacute;lectionner', 'evarisk') . '",
		}
    	jQuery.timepicker.setDefaults(jQuery.timepicker.regional["fr"]);

		jQuery("#digi_risk_date_start").datetimepicker({
			dateFormat: "yy-mm-dd",
			timeFormat: "hh:mm",
			changeMonth: true,
			changeYear: true,
			navigationAsDateFormat: true,
			showButtonPanel: false,' . $start_date_min_date . '
			onClose: function(selectedDate, input) {
				if ( (jQuery(this).val() != "") && (jQuery("#digi_parent_creation_date").val() != undefined) && (jQuery("#digi_parent_creation_date").val() + ":00" > jQuery(this).val() + ":00" ) ) {
				alert( digi_html_accent_for_js( "' . __('Vous ne pouvez pas mettre une date de d&eacute;but de risque inf&eacute;rieure &agrave; la date de cr&eacute;ation du groupement employeur', 'evarisk') . '" ) );
				jQuery(this).val( jQuery("#digi_parent_creation_date").val() );
				}
				if ( (jQuery(this).val() != "") && ( jQuery(this).val() != undefined ) ) {
					var current_end_date = jQuery("#digi_risk_end_start").val();
					jQuery("#digi_risk_end_start").datepicker("option", "minDate", jQuery(this).val());
					jQuery("#digi_risk_end_start").val( current_end_date );
				}
			}
		});
		jQuery("#digi_risk_date_start").val("' . (!empty($risque[0]) && !empty($risque[0]->dateDebutRisque) && ($risque[0]->dateDebutRisque != '0000-00-00 00:00:00') ? substr( $risque[0]->dateDebutRisque, 0, -3 ) : $risk_start_date_to_use) . '");

		jQuery("#digi_risk_end_start").datetimepicker({
			dateFormat: "yy-mm-dd",
			timeFormat: "hh:mm",
			changeMonth: true,
			changeYear: true,
			navigationAsDateFormat: true,
			showButtonPanel: false,' . $end_date_min_date . '
			onClose: function(selectedDate, input) {
				if ( (jQuery(this).val() != "") && (jQuery("#digi_parent_creation_date").val() != undefined) && ( jQuery("#digi_parent_creation_date").val() + ":00" > jQuery(this).val() + ":00" ) ) {
					alert( digi_html_accent_for_js( "' . __('Vous ne pouvez pas mettre une date de fin de risque inf&eacute;rieure &agrave; la date de cr&eacute;ation du groupement employeur', 'evarisk') . '" ) );
					jQuery(this).val( jQuery("#digi_parent_creation_date").val() );
				}

				if ( (jQuery(this).val() != "") && (jQuery(this).val() < jQuery("#digi_risk_date_start").val()) ) {
					alert( digi_html_accent_for_js( "' . __('Vous ne pouvez pas mettre une date de fin de risque inf&eacute;rieure &agrave; la date de d&eacute;but du risque', 'evarisk') . '" ) );
					jQuery(this).val( "" );
				}
			},
		});
		jQuery("#digi_risk_end_start").val("' . (!empty($risque[0]) && !empty($risque[0]->dateFinRisque) && ($risque[0]->dateFinRisque != '0000-00-00 00:00:00') ? substr( $risque[0]->dateFinRisque, 0, -3 ) : '') . '");

		jQuery("#digi_risk_evaluation_date").datetimepicker({
			dateFormat: "yy-mm-dd",
			timeFormat: "hh:mm",
			changeMonth: true,
			changeYear: true,
			navigationAsDateFormat: true,
		});
		jQuery("#digi_risk_evaluation_date").val("' . substr( current_time('mysql', 0), 0, -3 ) . '");


		/*	Autocomplete search	*/
		jQuery("#search_element").autocomplete({
			source: "' . EVA_INC_PLUGIN_URL . 'liveSearch/searchGp_UT.php?table_element=' . $tableElement . '&id_element=' . $idElement . '&element_type=' . TABLE_UNITE_TRAVAIL . '-t-' . TABLE_GROUPEMENT . '-t-' . TABLE_RISQUE . '",
			select: function( event, ui ){
				jQuery("#receiver_element").val(ui.item.value);

				setTimeout(function(){
					jQuery("#search_element").val(ui.item.label);
					jQuery("#search_element").blur();
				}, 2);
			}
		});

		jQuery("#move_risk").click(function(){
			jQuery("#loading_picto_risk_mover_container").html(jQuery("#loading_round_pic").html());
			jQuery("#loading_picto_risk_mover_container").show();
			jQuery("#button_risk_mover_container").hide();
			jQuery("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
				"post": "true",
				"table": "' . TABLE_RISQUE . '",
				"act": "move_risk",
				"id_risque": jQuery("#risk_to_move").val(),
				"new_element": jQuery("#receiver_element").val()
			});
		});
		jQuery("#copy_risk").click(function(){
			jQuery("#loading_picto_risk_mover_container").html(jQuery("#loading_round_pic").html());
			jQuery("#loading_picto_risk_mover_container").show();
			jQuery("#button_risk_mover_container").hide();
			jQuery("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
				"post": "true",
				"table": "' . TABLE_RISQUE . '",
				"act": "copy_risk",
				"id_risque": jQuery("#risk_to_move").val(),
				"new_element": jQuery("#receiver_element").val()
			});
		});
	});

	function live_risk_calcul(){
		var QR = ' . $formule . ';
		jQuery(".qr_risk").removeClass("Seuil_' . $niveauSeuil . '");
		jQuery(".qr_risk").html("<img src=\'' . PICTO_LOADING_ROUND . '\' alt=\'' . __('loading', 'evarisk') . '\' />");
		jQuery.post(ajaxurl, {
			action: "digi_ajax_reload_current_risk_cotation",
			score_risque: QR,
			date: "' . $method_operator_date . '",
			idMethode: "' . $idMethode . '",
		}, function( response ){
			jQuery(".qr_risk").html( response["cotation"] );
			jQuery("#current_qr").val( response["cotation"] );
			jQuery(".qr_risk").removeClass("Seuil_" + jQuery("#niveau_seuil_courant").val());
			jQuery("#niveau_seuil_courant").val( response["seuil"] );
			jQuery(".qr_risk").addClass("Seuil_" + response["seuil"]);
		}, "json");
	}

</script>';
					}
					break;

					case 'save_method_var_equivalence':{
						$id_methode = digirisk_tools::IsValid_Variable($_REQUEST['id_methode'], 0);
						$message = $more_action = '';

						if(!current_user_can('digi_edit_method')){
							$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Vous n\'avez pas les droits n&eacute;cessaire pour effectuer cette action', 'evarisk');
						}
						else{
							/* Mark old variable equivalence definition to deleted	*/
							$wpdb->update(TABLE_EQUIVALENCE_ETALON, array('Status' => 'Deleted'), array('id_methode' => $id_methode), array('%s'), array('%d'));

							$inserted_var_equivalence = $empty_var_equivalence = 0;
							foreach($_POST['equivalent'] as $valeurEtalon => $valeurMaxMethode){
								if($valeurMaxMethode != ''){
									$inserted_var_equivalence += $wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode' => $id_methode, 'id_valeur_etalon' => $valeurEtalon, 'valeurMaxMethode' => $valeurMaxMethode, 'date' => current_time('mysql', 0), 'Status' => 'Valid'));
								}
								else{
									$empty_var_equivalence++;
								}
							}

							$total_equivalence = $inserted_var_equivalence + $empty_var_equivalence;
							if($total_equivalence != count($_POST['equivalent'])){
								$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Une erreur est survenue lors de la mise &agrave; jour de l\'&eacute;quivalence des variables de la m&eacute;thode.', 'evarisk');
							}
							else{
								$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' class=\'messageIcone\' alt=\'succes\' />' . __('La mise &agrave; jour de l\'&eacute;quivalence des variables de la m&eacute;thode a correctement &eacute;t&eacute; effectu&eacute;e', 'evarisk');
							}
						}

						if($message != ''){
							echo '
<script type="text/javascript" >
	actionMessageShow("#evaluation_method_var_equivalence_message", "' . $message . '");
	setTimeout(\'actionMessageHide("#evaluation_method_var_equivalence_message")\', \'7000\');
' . $more_action . '
	goTo("#postBoxMethodeEvaluationEquivalenceVariable");
</script>';
						}
					}break;
					case 'save_method_var':{
						$id_methode = digirisk_tools::IsValid_Variable($_REQUEST['id_methode'], 0);
						$message = $more_action = '';
						if(!current_user_can('digi_edit_method')){
							$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Vous n\'avez pas les droits n&eacute;cessaire pour effectuer cette action', 'evarisk');
						}
						else{
							/* Mark old variable definition to deleted	*/
							$wpdb->update(TABLE_AVOIR_VARIABLE, array('Status' => 'Deleted'), array('id_methode' => $id_methode), array('%s'), array('%d'));
							/* Mark old operator definition to deleted	*/
							$wpdb->update(TABLE_AVOIR_OPERATEUR, array('Status' => 'Deleted'), array('id_methode' => $id_methode), array('%s'), array('%d'));

							/*	Insert new var set for evaluation method	*/
							$inserted_var = 0;
							$ordre = 1;
							foreach($_REQUEST['var'] as $var_id){
								$inserted_var += $wpdb->insert(TABLE_AVOIR_VARIABLE, array('id_methode' => $id_methode, 'id_variable' => $var_id, 'ordre' => $ordre, 'date' => current_time('mysql', 0), 'Status' => 'Valid'));

								$ordre++;
							}

							/*	Insert new operator for evaluation method if many var has been set	*/
							$inserted_operator = 0;
							$ordre = 1;
							if($_REQUEST['op'] != null){
								foreach($_REQUEST['op'] as $temp){
									$operateur = str_replace(' ','',digirisk_tools::IsValid_Variable($temp));
									$inserted_operator += $wpdb->insert(TABLE_AVOIR_OPERATEUR, array('id_methode' => $id_methode, 'operateur' => $operateur, 'ordre' => $ordre, 'date' => current_time('mysql', 0), 'Status' => 'Valid'));

									$ordre++;
								}
							}

							if(($inserted_var != count($_REQUEST['var'])) || ($inserted_operator != count($_REQUEST['op']))){
								$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Une erreur est survenue lors de la modification des variables de la m&eacute;thode.', 'evarisk');
							}
							elseif(($inserted_var == count($_REQUEST['var'])) && ($inserted_operator == count($_REQUEST['op']))){
								$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' class=\'messageIcone\' alt=\'succes\' />' . __('Les variables de la m&eacute;thode ont correctement &eacute;t&eacute; modifi&eacute;es.', 'evarisk');
								if($inserted_var >= 2){
									$method_formule = '';
									$i=0;
									foreach($_REQUEST['var'] as $var_index => $var_id){
										$method_var = eva_Variable::getVariable($var_id);
										$operator_indew_to_take = ($var_index-1);
										if($i>0 && !empty($_REQUEST['op'][$operator_indew_to_take])){
											$method_formule .= ' ' . $_REQUEST['op'][$operator_indew_to_take] . ' ';
										}
										$method_formule .= $method_var->nom;
										$i++;
									}
								}
								else{
									$method_formule = eva_Variable::getVariable($_REQUEST['var'][0]);
									$method_formule = $method_formule->nom;
								}
								$more_action = '
	jQuery("#info-' . $_REQUEST['table'] . '-' . $id_methode . '.evaluation_method_formule_cell").html("' . stripslashes($method_formule) . '");';
							}
							else{
								$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Une erreur est survenue lors de la mise &agrave; jour des variables de la m&eacute;thode', 'evarisk');
							}
						}

						if($message != ''){
							echo '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		actionMessageShow("#evaluation_method_var_message", "' . $message . '");
		setTimeout(\'actionMessageHide("#evaluation_method_var_message")\', \'7000\');
' . $more_action . '
	});
</script>';
						}
					}break;
					case 'save':{
						$id = digirisk_tools::IsValid_Variable($_REQUEST['id_methode'], 0);
						$message = $more_action = '';
						if((($id <= 0) && !current_user_can('digi_add_method')) || (($id > 0) && !current_user_can('digi_edit_method'))){
							$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Vous n\'avez pas les droits n&eacute;cessaire pour effectuer cette action', 'evarisk');
						}
						else{
							$nom = digirisk_tools::IsValid_Variable($_REQUEST['nom_methode']);
							$default_methode = digirisk_tools::IsValid_Variable($_REQUEST['default_methode'], 'no');
							if ( $default_methode == 'yes' ) {
								$wpdb->update(TABLE_METHODE, array('default_methode' => 'no'), array('default_methode' => 'yes'));
							}
							if($id <= 0){
								$method_result = $wpdb->insert(TABLE_METHODE, array('nom' => $nom, 'Status' => 'Valid', 'default_methode' => $default_methode), array('%s', '%s', '%s'));
								$id = $wpdb->insert_id;
								$more_action = '
	side_reloader("' . $_REQUEST['table'] . '", "' . $id . '", "", "");
	jQuery("#main_tree_container").load(EVA_AJAX_FILE_URL,{
		"post": "true",
		"act": "reload_config_tree",
		"table": "' . $_REQUEST['table'] . '",
		"elt": "node-main_table_' . $_REQUEST['table'] . '-' . $id . '"
	})';
								$error_message = __('Une erreur est survenue lors de la cr&eacute;ation de la m&eacute;thode.', 'evarisk');
								$success_message = __('La m&eacute;thode a correctement &eacute;t&eacute; cr&eacute;e.', 'evarisk');
							}
							else{
								$method_result = $wpdb->update(TABLE_METHODE, array('nom' => $nom, 'default_methode' => $default_methode), array('id' => $id), array('%s'), array('%d'));
								$more_action = '
	jQuery("#node-main_table_' . $_REQUEST['table'] . '-' . $id . '-name .node_name").html("' . stripslashes($nom) . '");';
								$error_message = __('Une erreur est survenue lors de la modification de la m&eacute;thode.', 'evarisk');
								$success_message = __('La m&eacute;thode a correctement &eacute;t&eacute; modifi&eacute;e.', 'evarisk');
							}

							if($method_result === 'error'){
								$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . $error_message;
								$more_action = '';
							}
							else{
								$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' class=\'messageIcone\' alt=\'succes\' />' . $success_message;
							}
						}

						if($message != ''){
							echo '
<script type="text/javascript" >
	actionMessageShow("#message", "' . $message . '");
	setTimeout(\'actionMessageHide("#message")\', \'7000\');
' . $more_action . '
</script>';
						}
					}break;
					case 'delete':{
						$message = $more_action = '';
						if(!current_user_can('digi_delete_method')){
							$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Vous n\'avez pas les droits n&eacute;cessaire pour effectuer cette action', 'evarisk');
						}
						else{
							$id = digirisk_tools::IsValid_Variable($_REQUEST['id']);
							$update_method_result = $wpdb->update(TABLE_METHODE, array('Status' => 'deleted'), array('id' => $id), array('%s'), array('%d'));
							if($update_method_result == 'error'){
								$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Une erreur est survenue lors de la suppresssion de la m&eacute;thode.', 'evarisk');
							}
							else{
								$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' class=\'messageIcone\' alt=\'succes\' />' . __('La m&eacute;thode a correctement &eacute;t&eacute; supprim&eacute;e.', 'evarisk');
								$more_action = '
	jQuery("#node-main_table_' . $_REQUEST['table'] . '-' . $id . '").remove();';
							}
						}

						if($message != ''){
							echo '
<script type="text/javascript" >
	actionMessageShow("#message", "' . $message . '");
	setTimeout(\'actionMessageHide("#message")\', \'7000\');
' . $more_action . '
</script>';
						}
					}break;
				}
			break;
			case TABLE_VARIABLE:
				switch($_REQUEST['act']){
					case 'load_variable_management':{
						echo eva_Variable::existing_var_output();
					}break;
					case 'load_variable_management_form':{
						$id = !empty($_REQUEST['id'])?digirisk_tools::IsValid_Variable($_REQUEST['id']):0;
						echo eva_Variable::var_edition_form($id);
					}break;
					case 'delete_var':{
						$id = !empty($_REQUEST['id'])?digirisk_tools::IsValid_Variable($_REQUEST['id']):0;
						$message = '';

						if(current_user_can('digi_delete_method_var')){
							$var_action_result = $wpdb->update(TABLE_VARIABLE, array('Status' => 'Deleted'), array('id' => $id), array('%s'), array('%d'));
							if(!$var_action_result){
								$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Une erreur est survenue lors de la suppresssion de la variable.', 'evarisk');
							}
							else{
								$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' class=\'messageIcone\' alt=\'succes\' />' . __('La variable a correctement &eacute;t&eacute; supprim&eacute;e.', 'evarisk');
								$more_action = '
	jQuery("#eval-method-var-' . $id . '").remove();';
							}
						}
						else{
							$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Vous n\'avez pas les droits n&eacute;cessaire pour effectuer cette action', 'evarisk');
						}

						if($message != ''){
							echo '
<script type="text/javascript" >
	actionMessageShow("#var_management_message", "' . $message . '");
	setTimeout(\'actionMessageHide("#var_management_message")\', \'7000\');
' . $more_action . '
</script>';
						}
					}break;
					case 'save':{
						$id = digirisk_tools::IsValid_Variable($_REQUEST['id_variable'], 0);
						$message = $more_action = '';
						if((($id <= 0) && !current_user_can('digi_add_method_var')) || (($id > 0) && !current_user_can('digi_edit_method_var'))){
							$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Vous n\'avez pas les droits n&eacute;cessaire pour effectuer cette action', 'evarisk');
						}
						else{
							$nom = digirisk_tools::IsValid_Variable($_REQUEST['newvarname']);
							$minimum = digirisk_tools::IsValid_Variable($_REQUEST['newvarmin'], 0);
							$maximum = digirisk_tools::IsValid_Variable($_REQUEST['newvarmax'], 0);
							$annotation = digirisk_tools::IsValid_Variable($_REQUEST['newvarannotation'], '');
							$alterValues = $_REQUEST['newVariableAlterValue'];
                                                        $methodeAffichage = digirisk_tools::IsValid_Variable($_REQUEST['methodeAffichage']);
                                                        $questionTitre = digirisk_tools::IsValid_Variable($_REQUEST['newvarquestion']);
                                                        $questionValues = $_REQUEST['varQuestion'];
                                                        $seuilValues = $_REQUEST['varSeuil'];

							if($id <= 0){

								$result = $wpdb->insert(TABLE_VARIABLE, array('nom' => $nom, 'min' => $minimum, 'max' => $maximum, 'annotation' => $annotation, 'Status' => 'Valid', 'affichageVar' => $methodeAffichage, 'questionTitre' => $questionTitre), array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'));
								$id = $wpdb->insert_id;
							}
							else{
								$result = $wpdb->update(TABLE_VARIABLE, array('nom' => $nom, 'min' => $minimum, 'max' => $maximum, 'annotation' => $annotation, 'affichageVar' => $methodeAffichage, 'questionTitre' => $questionTitre), array('id' => $id), array('%s', '%s', '%s', '%s', '%s', '%s', '%s'), array('%d'));
								if(($result == 1) || ($result == 0)){
									$result = true;
									$wpdb->update(TABLE_VALEUR_ALTERNATIVE, array('Status' => 'Deleted'), array('id_variable' => $id), array('%s'), array('%d'));
								}
							}

							/*	Create  new alternativ vars at each var creation/modification	*/
							if(!empty($alterValues) && is_array($alterValues)){
								foreach($alterValues as $value => $alterValue){
									$alterValue = digirisk_tools::IsValid_Variable($alterValue);
									if($alterValue != ''){
										$result = $wpdb->insert(TABLE_VALEUR_ALTERNATIVE, array('id_variable' => $id, 'valeur' => $value, 'valeurAlternative' => $alterValue, 'date' => current_time('mysql', 0), 'Status' => 'Valid'), array('%s', '%s', '%s', '%s', '%s'));
									}
								}
							}
                                                        /*  Save an array in the database with every question and step of the var */
                                                        if(!empty($questionValues) && !empty($seuilValues))
                                                        {
                                                            $dataTab;
                                                            for($i = $minimum; $i <= $maximum; $i++)
                                                            {
                                                                $dataTab[]= array('question' => $questionValues[$i], 'seuil' => $seuilValues[$i]);
                                                            }

                                                            $tabToSave = serialize($dataTab);
                                                            $wpdb -> update(TABLE_VARIABLE, array('questionVar' => $tabToSave), array('id' => $id), array('%s'), array('%d'));
                                                        }

							if(!$result){
								$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Une erreur est survenue lors de l\'enregistrement de la variable.', 'evarisk');
								$more_action = '';
							}
							else{
								$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' class=\'messageIcone\' alt=\'succes\' />' . __('La m&eacute;thode a correctement &eacute;t&eacute; enregitr&eacute;e.', 'evarisk');
								$more_action = '
		jQuery("#var_manager_window").html(evarisk("#loadingImg").html());
		jQuery("#var_manager_window").load(EVA_AJAX_FILE_URL,{
			"post": "true",
			"table": "' . TABLE_VARIABLE . '",
			"act": "load_variable_management"
		});
		// alert(jQuery("#method_var_form #id_methode").val());
		if(jQuery("#method_var_form #id_methode").val()>0){
			jQuery(".evaluation_method_var_container").html(evarisk("#loadingImg").html());
			jQuery(".evaluation_method_var_container").load(EVA_AJAX_FILE_URL,{
				"post": "true",
				"table": "' . TABLE_METHODE . '",
				"act": "load_method_variable_container",
				"idElement": jQuery("#method_var_form #id_methode").val()
			});
		}
		jQuery("#evaluation_method_form_container").dialog("close");';
							}
						}

						if($message != ''){
							echo '
<script type="text/javascript" >
' . $more_action . '
	actionMessageShow("#var_management_message", "' . $message . '");
	setTimeout(\'actionMessageHide("#var_management_message")\', "7000");
</script>';
						}
					}break;
				}
				break;
			break;
			case TABLE_GROUPE_QUESTION:
				switch($_REQUEST['act']){
					case 'transfert': {
						$fils = $_REQUEST['idElementSrc'];
						$pere = $_REQUEST['idElementDest'];
						$pereOriginel = $_REQUEST['idElementOrigine'];
						$idPere = str_replace('node-' . $_REQUEST['location'] . '-','', $pere);
						$idFils = (string)((int) str_replace('node-' . $_REQUEST['location'] . '-','', $fils));
						$idPereOriginel = str_replace('node-' . $_REQUEST['location'] . '-','', $pereOriginel);
						if($idFils == str_replace('node-' . $_REQUEST['location'] . '-','', $fils))
						//Le fils est un groupe de questions
						{
							$idFils = str_replace('node-' . $_REQUEST['location'] . '-','', $fils);
							$groupeQuestion = evaGroupeQuestions::getGroupeQuestions($idFils);
							$pereActu = Arborescence::getPere($_REQUEST['nom'], $groupeQuestion);
							if($pereActu->id != $idPere)
							{
								$_REQUEST['act'] = 'update';
								$_REQUEST['id'] = $groupeQuestion->id;
								$_REQUEST['nom'] = $groupeQuestion->nom;
								$_REQUEST['code'] = $groupeQuestion->code;
								$_REQUEST['idPere'] = $idPere;
								require_once(EVA_METABOXES_PLUGIN_DIR . 'veilleReglementaire/groupeQuestionPersistance.php');
							}
						}
						else
						//Le fils est une question
						{
							$idFils = str_replace('leaf-','', $fils);
							evaQuestion::transfertQuestion($idFils, $idPere, $idPereOriginel);
						}
					}
					break;
					case 'save':
					{
						switch($_REQUEST['choix'])
						{
							case 'titre':
								if ( !empty($_REQUEST['nom']) ) {
									$retourALaLigne = array("\r\n", "\n", "\r");
									$_REQUEST['nom'] = str_replace($retourALaLigne, "[retourALaLigne]",$_REQUEST['nom']);
									$_REQUEST['extrait'] = null;
									require_once(EVA_METABOXES_PLUGIN_DIR . 'veilleReglementaire/groupeQuestionPersistance.php');
								}
								break;
						}
					}
					break;
					case 'update':
					{
						switch($_REQUEST['choix'])
						{
							case 'titre':
								if($_REQUEST['nom'] != null and $_REQUEST['nom'] != '')
								{
									$retourALaLigne = array("\r\n", "\n", "\r");
									$_REQUEST['nom'] = str_replace($retourALaLigne, "[retourALaLigne]",$_REQUEST['nom']);
									$temp = EvaGroupeQuestions::getGroupeQuestions($_REQUEST['id']);
									$temp = Arborescence::getPere($_REQUEST['table'],$temp);
									$_REQUEST['idPere'] = $temp->id;
									require_once(EVA_METABOXES_PLUGIN_DIR . 'veilleReglementaire/groupeQuestionPersistance.php');
								}
								break;
						}
					}
					break;
					case 'addExtrait' :
						$groupeQuestion = EvaGroupeQuestions::getGroupeQuestions($_REQUEST['idGroupeQuestion']);
						if($groupeQuestion->extraitTexte != null AND $groupeQuestion->extraitTexte != '') {
							$extraitActuel = $groupeQuestion->extraitTexte . "
							";
						}
					case 'replaceExtrait' :
						if(!isset($extraitActuel))
						{
							$extraitActuel = '';
						}
						$retourALaLigne = array("\r\n", "\n", "\r");
						$_REQUEST['extrait'] = str_replace($retourALaLigne, "[retourALaLigne]",$extraitActuel . $_REQUEST['extrait']);
						$_REQUEST['act'] = "addExtrait";
						unset($extraitActuel);
						require_once(EVA_METABOXES_PLUGIN_DIR . 'veilleReglementaire/groupeQuestionPersistance.php');
					break;
					case 'reloadCombo':
						$nomRacine = (isset($_REQUEST['nomRacine']) && (trim($_REQUEST['nomRacine']) != '') && (is_string($_REQUEST['nomRacine']))) ? digirisk_tools::IsValid_Variable($_REQUEST['nomRacine']) : '';
						if($nomRacine != '')
						{
							$query = $wpdb->prepare("SELECT * FROM " . TABLE_GROUPE_QUESTION . " WHERE nom = %s", $nomRacine);
							$racine = $wpdb->get_row($query);
							$valeurDefaut = $racine->nom;
							$selection = $_REQUEST['selection'];
							echo evaDisplayInput::afficherComboBoxArborescente($racine, TABLE_GROUPE_QUESTION, $_REQUEST['idSelect'], $_REQUEST['labelSelect'], $_REQUEST['nameSelect'], $valeurDefaut, $selection);
						}
						else
						{
							echo __('Vous devez sp&eacute;cifier un nom', 'evarisk');
						}
					break;
					case 'reloadTableArborescente':
						$racine = EvaGroupeQuestions::getGroupeQuestions($_REQUEST['idRacine']);
						$nomRacine = $_REQUEST['nomRacine'];
						$idTable = $_REQUEST['idTable'];
						echo evaDisplayDesign::getTableArborescence($racine, $_REQUEST['table'], $idTable, $nomRacine);
					break;
				}
				break;
			case TABLE_QUESTION:
				switch($_REQUEST['act'])
				{
					case 'edit':
					case 'save':
						$_REQUEST['code'] = '';
						$retourALaLigne = array("\r\n", "\n", "\r");
						$_REQUEST['enonce'] = str_replace($retourALaLigne, "[retourALaLigne]",$_REQUEST['enonce']);
						require_once(EVA_METABOXES_PLUGIN_DIR . 'veilleReglementaire/questionPersistance.php');
					break;
				}
				break;
			case TABLE_TACHE:
			if(!empty($_REQUEST['act'])){
				switch($_REQUEST['act']){
					case 'transfert':
					{
						$fils = $_REQUEST['idElementSrc'];
						$pere = $_REQUEST['idElementDest'];
						$idPere = str_replace('node-' . $_REQUEST['location'] . '-','', $pere);
						$idOrigine = str_replace('node-' . $_REQUEST['location'] . '-','', $_REQUEST['idElementOrigine']);
						$idFils = (string)((int) str_replace('node-' . $_REQUEST['location'] . '-','', $fils));
						if($idFils == str_replace('node-' . $_REQUEST['location'] . '-','', $fils)){//Le fils est une t�che
							$tache = new EvaTask($idFils);
							$tache->load();
							$old_parent = Arborescence::getPere(TABLE_TACHE, $tache->convertToWpdb());
							$tache->transfert($idPere);

							$relatedTask = new EvaTask($idPere);
							$relatedTask->load();
							/*	Check the state of export checkboxes in order to update sub element of current element	*/
							if ( ( $relatedTask->name != __('Tache Racine', 'evarisk') ) &&  ( $relatedTask->getnom_exportable_plan_action() == 'no' ) ) {
								/*	Change the sub task exportable status if no is selected for the current element	*/
								$task_children = $relatedTask->getDescendants();
								if ( !empty ( $task_children->tasks ) ) {
									foreach ( $task_children->tasks as $task_id => $task_detail ) {
										$sub_task = new EvaTask($task_id);
										$sub_task->load();
										$sub_task->setnom_exportable_plan_action('no');
										$wpdb->update( TABLE_ACTIVITE, array( 'nom_exportable_plan_action' => 'no', 'description_exportable_plan_action' => 'no', ), array( 'id_tache' => $task_id, ) );
										$sub_task->save();
									}
								}
							}

							/*	Log modification on element and notify user if user subscribe	*/
							digirisk_user_notification::log_element_modification(TABLE_TACHE, $idFils, 'transfer', ($old_parent->id), ($idPere));
						}
						else{//Le fils est une activit�
							$idFils = str_replace('leaf-','', $fils);
							$activite = new EvaActivity($idFils);
							$activite->load();
							$old_parent = $activite->getRelatedTaskId();
							$activite->transfert($idPere);

							/*	Log modification on element and notify user if user subscribe	*/
							digirisk_user_notification::log_element_modification(TABLE_ACTIVITE, $idFils, 'transfer', ($old_parent), ($idPere));

							/*	Update the action ancestor	*/
							$relatedTask = new EvaTask($idPere);
							$relatedTask->load();

							/*	Check the state of export checkboxes in order to update sub element of current element	*/
							if ( $relatedTask->getnom_exportable_plan_action() == 'no' ) {
								$wpdb->update( TABLE_ACTIVITE, array( 'nom_exportable_plan_action' => 'no', 'description_exportable_plan_action' => 'no', ), array( 'id_tache' => $relatedTask->getId(), ) );
							}

							$relatedTask->computeProgression();
							$relatedTask->save();
							unset($relatedTask);

							/*	Update the action ancestor	*/
							$relatedTask = new EvaTask($idOrigine);
							$relatedTask->load();
							$relatedTask->computeProgression();
							$relatedTask->save();
						}
					}
					break;
					case 'updateProvenance':
					{
						$id = digirisk_tools::IsValid_Variable($_REQUEST['id']);
						$provenance = digirisk_tools::IsValid_Variable($_REQUEST['receiver_element']);
						if($provenance != ''){
							$provenanceComponents = explode('-_-', $provenance);

							$tache = new EvaTask($id);
							$tache->load();
							$old_task_from[] = $tache->getIdFrom();
							$old_task_from[] = $tache->getTableFrom();
							$tache->setIdFrom($provenanceComponents[1]);
							$tache->setTableFrom($provenanceComponents[0]);
							$tache->save();

							if($tache->getStatus() != 'error'){
								if($provenance === '0'){
									$provenanceComponents = 'none';
								}
								/*	Log modification on element and notify user if user subscribe	*/
								digirisk_user_notification::log_element_modification(TABLE_TACHE, $id, 'affectation_update', $old_task_from, $provenanceComponents);

								$updateMessage = 'digirisk("#messageh' . $_REQUEST['table'] . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('L\'affectation de la t&acirc;che a correctement &eacute;t&eacute; effectu&eacute;e', 'evarisk') . '</strong></p>') . '");';

								switch($provenanceComponents[0]){
									case TABLE_RISQUE:
										/*	Make the link between a corrective action and a risk evaluation	*/
										$query =
											$wpdb->prepare(
												"SELECT id_evaluation
												FROM " . TABLE_AVOIR_VALEUR . "
												WHERE id_risque = '%d'
													AND Status = 'Valid'
												ORDER BY id DESC
												LIMIT 1",
												$provenanceComponents[1]
											);
										$evaluation = $wpdb->get_row($query);
										$provenanceComponents[0] = TABLE_AVOIR_VALEUR;
										$provenanceComponents[1] = $evaluation->id_evaluation;
									break;
								}
								evaTask::liaisonTacheElement($provenanceComponents[0], $provenanceComponents[1], $id, 'before');
							}
							else{
								$updateMessage = 'digirisk("#messageh' . $_REQUEST['table'] . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="no-response" style="vertical-align:middle;" />&nbsp;<strong>' . __('L\'affectation de la t&acirc;che n\'a pas &eacute;t&eacute; effectu&eacute;e.', 'evarisk') . '</strong></p>') . '");';
							}

							$messageInfo =
							'<script type="text/javascript">
								digirisk(document).ready(function(){
									digirisk("#savingLinkTaskElement").html("");
									digirisk("#savingLinkTaskElement").hide();
									digirisk("#saveLinkTaskElement").show();
									jQuery("#current_element").val("' . $provenance . '");
									jQuery("#saveLinkTaskElement input").addClass("button-secondary");
									jQuery("#saveLinkTaskElement input").removeClass("button-primary");
									digirisk("#messageh' . $_REQUEST['table'] . '").addClass("updated");
									' . $updateMessage . '
									digirisk("#messageh' . $_REQUEST['table'] . '").show();
									setTimeout(function(){
										digirisk("#messageh' . $_REQUEST['table'] . '").removeClass("updated");
										digirisk("#messageh' . $_REQUEST['table'] . '").hide();
									},7500);
									jQuery("#current_hierarchy_display").html(jQuery("#loading_round_pic").html());
									jQuery("#current_hierarchy_display").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
										"post": "true",
										"nom": "hierarchy",
										"action": "load_partial",
										"selected_element": "' . $provenance . '"
									});
								});
							</script>';
							echo $messageInfo;
						}
						else{
							echo '<script type="text/javascript">
								digirisk(document).ready(function(){
									digirisk("#savingLinkTaskElement").html("");
									digirisk("#savingLinkTaskElement").hide();
									digirisk("#saveLinkTaskElement").show();
								});
							</script>';
						}
					}
					break;

					case 'delete':
					{
						$tache = new EvaTask($_REQUEST['id']);
						$tache->load();
						$tache->setStatus('Deleted');
						$task_children = $tache->getDescendants();
						foreach($task_children->tasks as $index => $task){
							$sub_tasks = EvaTask::getChildren($task->id);
							$wpdb->update(TABLE_TACHE, array('Status'=>'Deleted'), array('id'=>$task->id));
							foreach($sub_tasks as $index_2 => $sub_task){
								$wpdb->update(TABLE_ACTIVITE, array('Status'=>'Deleted'), array('id'=>$sub_task->id));
							}
						}
						$tache->save();

						$messageInfo = '<script type="text/javascript">';
						if($tache->getStatus() != 'error'){
							/**	Get options for checking if current deleted task is setted as parent task for frontend asking or for control task association	*/
							$digirisk_options = get_option( 'digirisk_options' );
							$save_option_for_tasks_association = false;
							if ( !empty( $digirisk_options ) && !empty( $digirisk_options[ 'digi_ac_front_ask_parent_task_id' ] ) && ( $_REQUEST['id'] == $digirisk_options[ 'digi_ac_front_ask_parent_task_id' ] ) ) {
								unset( $digirisk_options[ 'digi_ac_front_ask_parent_task_id' ] );
								$save_option_for_tasks_association = true;
							}
							if ( !empty( $digirisk_options ) && !empty( $digirisk_options[ 'digi_ac_control_action_affectation' ] ) && ( $_REQUEST['id'] == $digirisk_options[ 'digi_ac_control_action_affectation' ] ) ) {
								unset( $digirisk_options[ 'digi_ac_control_action_affectation' ] );
								$save_option_for_tasks_association = true;
							}
							if ( digi_ac_control_action_affectation ) {
								update_option( 'digirisk_options', $digirisk_options );
							}

							/*	Log modification on element and notify user if user subscribe	*/
							digirisk_user_notification::log_element_modification(TABLE_TACHE, $_REQUEST['id'], 'delete', '', '');

							$messageInfo = $messageInfo . '
								digirisk(document).ready(function(){
									digirisk("#message").addClass("updated");
									digirisk("#message").html("' . addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La fiche %s a correctement &eacute;t&eacute; supprim&eacute;e', 'evarisk') . '</strong></p>', __('de la t&acirc;che', 'evarisk') . ' "' . stripslashes($tache->getName()) . '"')) . '");
									digirisk("#message").show();
									setTimeout(function(){
										digirisk("#message").removeClass("updated");
										digirisk("#message").hide();
									},7500);';
						}
						else{
							$messageInfo = $messageInfo . '
								digirisk(document).ready(function(){
									digirisk("#message").addClass("updated");
									digirisk("#message").html("' . addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="no-response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La fiche %s n\'a pas &eacute;t&eacute; supprim&eacute;e', 'evarisk') . '</strong></p>', __('de la t&acirc;che', 'evarisk') . ' "' . stripslashes($tache->getName()) . '"')) . '");
									digirisk("#message").show();
									setTimeout(function(){
										digirisk("#message").removeClass("updated");
										digirisk("#message").hide();
									},7500);';
						}
						$messageInfo = $messageInfo . '
									digirisk("#rightEnlarging").show();
									digirisk("#equilize").click();
									if("' . $_REQUEST['affichage'] . '" == "affichageTable")
									{
										if(digirisk("#filAriane :last-child").is("label"))
											digirisk("#filAriane :last-child").remove();
										digirisk("#filAriane :last-child").after("<label>&nbsp;&raquo;&nbsp;&Eacute;dition&nbsp;de&nbsp;' . $_REQUEST['nom_tache'] . '</label>");
										digirisk("#partieEdition").html("");
										digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
											"table": "' . TABLE_TACHE . '",
											"id": "' . $tache->getId() . '",
											"page": digirisk("#pagemainPostBoxReference").val(),
											"idPere": digirisk("#identifiantActuellemainPostBox").val(),
											"act": "edit",
											"partie": "left",
											"menu": digirisk("#menu").val(),
											"affichage": "affichageTable",
											"partition": "tout"
										});
									}
									else
									{
										var expanded = new Array();
										digirisk(".expanded").each(function(){expanded.push(digirisk(this).attr("id"));});
										digirisk("#partieEdition").html("");
										digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
											"table": "' . TABLE_TACHE . '",
											"act": "edit",
											"id": "' . $tache->getId() . '",
											"partie": "left",
											"menu": digirisk("#menu").val(),
											"affichage": "affichageListe",
											"expanded": expanded
										});
									}
								});
							</script>';
						echo $messageInfo;
					}
					break;
					case 'exportTask':{
						$id = digirisk_tools::IsValid_Variable($_REQUEST['id']);

						$existingPreconisation = '';
						$tache = new EvaTask($id);
						$tache->load();
						$TasksAndSubTasks = $tache->getDescendants();
						$TasksAndSubTasks->addTask($tache);
						$TasksAndSubTasks = $TasksAndSubTasks->getTasks();
						if($TasksAndSubTasks != null AND count($TasksAndSubTasks) > 0){
							foreach($TasksAndSubTasks as $task){
								if($task->id != $tache->id){
									$existingPreconisation .= ELEMENT_IDENTIFIER_T . $task->id . ' - ' . $task->name;
									if(!empty($task->description)){
										$existingPreconisation .= ' (' . str_replace("
", " / ", $task->description) . ')';
									}
									$existingPreconisation .= "
";
								}
								$activities = $task->getActivitiesDependOn();
								$activities = $activities->getActivities();
								if(($activities != null) AND (count($activities) > 0)){
									foreach($activities as $activity){
										$existingPreconisation .= "		" . ELEMENT_IDENTIFIER_ST . $activity->id . ' - ' . $activity->name;
										if($activity->description != ''){
											$existingPreconisation .= ' (' . str_replace("
", " / ", $activity->description) . ')';
										}
										$existingPreconisation .= "
";
									}
								}
							}

							$existingPreconisation = $existingPreconisation . "


" . htmlentities($existingPreconisation, ENT_NOQUOTES, 'UTF-8');
						}

						$dirToSaveExportedFile = EVA_UPLOADS_PLUGIN_DIR . $_REQUEST['table'];
						if(!is_dir($dirToSaveExportedFile)){
							mkdir($dirToSaveExportedFile, 0755, true);
							exec('chmod -R 755 ' . EVA_GENERATED_DOC_DIR);
						}
						file_put_contents($dirToSaveExportedFile . '/taskExport.txt' ,$existingPreconisation);
						if(is_file($dirToSaveExportedFile . '/taskExport.txt')){
							/*	Log modification on element and notify user if user subscribe	*/
							digirisk_user_notification::log_element_modification(TABLE_TACHE, $_REQUEST['id'], 'export', '', '');

							echo '<a href="' . str_replace(EVA_UPLOADS_PLUGIN_DIR, EVA_UPLOADS_PLUGIN_URL, $dirToSaveExportedFile) . '/taskExport.txt" title="' . __('Pour le t&eacute;l&eacute;charger, faites un clic droit puis enregistrer sous', 'evarisk') . '" >' . __('T&eacute;l&eacute;charger le fichier g&eacute;n&eacute;r&eacute;', 'evarisk') . '</a>';
						}
					}
					break;
					case 'actualiseProgressionInTree':
					{
						$id = digirisk_tools::IsValid_Variable($_REQUEST['id']);
						$tache = new EvaTask($id);
						$tache->load();
						$statutProgression = '';
						switch($tache->getProgressionStatus())
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
						echo $tache->getProgression() . '%&nbsp;(' . $statutProgression . ')';
					}
					break;
					case 'loadDetails':
					case 'load_details_simple':
					{
						$output = $moreInfos = '';
						$id = digirisk_tools::IsValid_Variable($_REQUEST['id']);
						$element_identifier = explode('_t_elt_', $id);

						/*	On r�cup�re les utilisateurs d�j� affect�s � l'�l�ment en cours.	*/
						$linkedUser = '';
						$utilisateursLies = evaUserLinkElement::getAffectedUser($element_identifier[0], $element_identifier[1]);
						if(is_array($utilisateursLies ) && (count($utilisateursLies) > 0)){
							foreach($utilisateursLies as $utilisateur){
								$currentUser = evaUser::getUserInformation($utilisateur->id_user);
								$linkedUser .= '<div class="correctiveActionSelecteduserOP" id="affectedUser' . $tableElement . $utilisateur->id_user . '" title="' . __('Cliquez pour supprimer', 'evarisk') . '" >' . ELEMENT_IDENTIFIER_U . $utilisateur->id_user . '&nbsp;-&nbsp;' . $currentUser[$utilisateur->id_user]['user_lastname'] . ' ' . $currentUser[$utilisateur->id_user]['user_firstname'] . '</div>';
							}
						}
						else{
							$linkedUser = __('Aucun utilisateur affect&eacute;', 'evarisk');
						}

						$description = $name = '';
						switch($element_identifier[0])
						{
							case TABLE_TACHE:
							{
								$postId = $element_identifier[1];
								$tache = new EvaTask($postId);
								$tache->load();
								$name = html_entity_decode($tache->getName(), ENT_NOQUOTES, 'UTF-8');
								$description = $tache->getDescription();
								$contenuInputResponsable = $tache->getidResponsable();
								$contenuInputRealisateur = $tache->getidSoldeur();
								$ProgressionStatus = $tache->getProgressionStatus();
								$efficacite = $tache->getEfficacite();
								$startDate = $tache->getStartDate();
								$endDate = $tache->getFinishDate();
								if(($startDate != '') && ($endDate != '') && ($startDate != '0000-00-00') && ($endDate != '0000-00-00')){
									$date = '
	<tr>
		<td colspan="2" >' . __('D&eacute;but', 'evarisk') . '&nbsp;' . mysql2date('d M Y', $startDate, true) . '&nbsp;-&nbsp;' . __('Fin', 'evarisk') . '&nbsp;' . mysql2date('d M Y', $endDate, true) . '&nbsp;<span style="font-size:9px;" >(' . __('Ces dates sont calcul&eacute;es en fonction de sous-t&acirc;ches', 'evarisk') . ')</span></td>
	</tr>';
								}
								$link_to_element = '<a href="' . get_bloginfo('siteurl') . '/wp-admin/admin.php?page=digirisk_correctiv_actions&amp;elt=edit-node' . $tache->id . '" target="seePassedFac" >' . __('Voir l\'action', 'evarisk') . '</a>';

								if($_REQUEST['act'] == 'load_details_simple'){
									$moreInfos .= '
	<tr>
		<td class="correctivActionDetailsFieldName" >' . __('&Eacute;valuation du risque associ&eacute;', 'evarisk') . '</td>
	</tr>
	<tr>
		<td colspan="2">' . Risque::get_risk_level_summary_by_moment($element_identifier[0], $element_identifier[1], $tache, 'correctivActionFollow', array('demand', 'current')) . '</td>
	</tr>';
									$link_to_element = '';
								}
								else{
									$moreInfos .= '
	<tr>
		<td class="correctivActionDetailsFieldName" >' . __('Efficacit&eacute; de l\'action', 'evarisk') . '</td>
		<td >' . $efficacite . '&nbsp;%</td>
	</tr>
	<tr>
		<td class="correctivActionDetailsFieldName" >' . __('&Eacute;valuation du risque associ&eacute;', 'evarisk') . '</td>
	</tr>
	<tr>
		<td colspan="2">' . Risque::get_risk_level_summary_by_moment($element_identifier[0], $element_identifier[1], $tache, 'correctivActionFollow', array('demand', 'current', 'before', 'after')) . '</td>
	</tr>';
								}
							}
							break;
							case TABLE_ACTIVITE:
							{
								$postId = $element_identifier[1];
								$tache = new EvaActivity($postId);
								$tache->load();
								$name = html_entity_decode($tache->getName(), ENT_NOQUOTES, 'UTF-8');
								$description = $tache->getDescription();
								$contenuInputResponsable = $tache->getidResponsable();
								$contenuInputRealisateur = $tache->getidSoldeur();
								$ProgressionStatus = $tache->getProgressionStatus();
								$startDate = $tache->getStartDate();
								$endDate = $tache->getFinishDate();
								if(($startDate != '') && ($endDate != '') && ($startDate != '0000-00-00') && ($endDate != '0000-00-00')){
									$date = '
	<tr>
		<td colspan="2" >' . __('D&eacute;but', 'evarisk') . '&nbsp;' . mysql2date('d M Y', $startDate, true) . '&nbsp;-&nbsp;' . __('Fin', 'evarisk') . '&nbsp;' . mysql2date('d M Y', $endDate, true) . '</td>
	</tr>';
								}
								$query = $wpdb->prepare(
									"SELECT photo
									FROM " . TABLE_PHOTO . " AS P
										INNER JOIN " . TABLE_ACTIVITE . " AS A ON (A.idPhotoAvant = P.id)
									WHERE A.id = '%s' ",
									$postId
								);
								$pictureBefore = $wpdb->get_var($query);
								$pictureBeforeOutput = __('Aucune photo d&eacute;finie', 'evarisk');
								if($pictureBefore != ''){
									$pictureBeforeOutput = '<img src="' . EVA_GENERATED_DOC_URL . $pictureBefore . '" alt="picture before corrective action" style="width:40%;" />';
								}
								$query = $wpdb->prepare(
									"SELECT photo
									FROM " . TABLE_PHOTO . " AS P
										INNER JOIN " . TABLE_ACTIVITE . " AS A ON (A.idPhotoApres = P.id)
									WHERE A.id = '%s' ",
									$postId
								);
								$pictureAfter = $wpdb->get_var($query);
								$pictureAfterOutput = __('Aucune photo d&eacute;finie', 'evarisk');
								if($pictureAfter != ''){
									$pictureAfterOutput = '<img src="' . EVA_GENERATED_DOC_URL . $pictureAfter . '" alt="picture before corrective action" style="width:40%;" />';
								}

								$moreInfos .= '
	<tr>
		<td class="correctivActionDetailsFieldName" >' . __('Photos', 'evarisk') . '</td>
	</tr>
	<tr>
		<td colspan="2">
			<table summary="correctiv action picture" cellpadding="0" cellspacing="0" id="correctionActionPictureTable" >
				<tr>
					<th>' . __('Photo avant', 'evarisk') . '</td>
					<th>' . __('Photo apr&egraves', 'evarisk') . '</td>
				</tr>
				<tr>
					<td class="correctivActionPicture" >' . $pictureBeforeOutput . '</td>
					<td class="correctivActionPicture" >' . $pictureAfterOutput . '</td>
				</tr>
			</table>
		</td>
	</tr>';
								$link_to_element = '<a href="' . get_bloginfo('siteurl') . '/wp-admin/admin.php?page=digirisk_correctiv_actions&amp;elt=edit-leaf' . $tache->id . '" target="seePassedFac" >' . __('Voir l\'action', 'evarisk') . '</a>';

								if($_REQUEST['act'] == 'load_details_simple'){
									$moreInfos = '';
									$link_to_element = '';
								}
							}
							break;
						}

						$output .= '
<table summary="task details" cellpadding="0" cellspacing="0" >' . $date . '
	<tr>
		<td class="correctivActionDetailsFieldName" >' . __("Nom", 'evarisk') . '</td>
		<td>' . $name . '</td>
	</tr>
	<tr>
		<td class="correctivActionDetailsFieldName" >' . __("Description", 'evarisk') . '</td>
		<td>' . $description . '</td>
	</tr>
	<tr>
		<td class="correctivActionDetailsFieldName" >' . __('Utilisateurs affect&eacute;s', 'evarisk') . '</td>
		<td>' . $linkedUser . '</td>
	</tr>
	<tr>
		<td class="correctivActionDetailsFieldName" >' . __('Suivi de l\'action corrective', 'evarisk') . '</td>';
							$suivi = suivi_activite::tableauSuiviActivite($element_identifier[0], $element_identifier[1]);
							if(trim($suivi) == ''){
								$output .= '
		<td >' . __('Aucun suivi pour cette action', 'evarisk');
							}
							else{
								$output .= '
	</tr>
	<tr>
		<td colspan="2" class="correctiveActionFollow" >' . $suivi;
							}
							$output .=
		'</td>
	</tr>' . $moreInfos . '
</table>
<div class="alignright digi_correctiv_action_link_container" >' . $link_to_element . '</div>';

						echo $output;
					}
					break;
					case 'closeTask':
					{
						$output = '';
						$tache = new EvaTask($_REQUEST['id']);
						$tache->load();
						$contenuInputTitre = html_entity_decode($tache->getName(), ENT_NOQUOTES, 'UTF-8');
						$contenuInputDescription = $tache->getDescription();
						$idProvenance = $tache->getIdFrom();
						$tableProvenance = $tache->getTableFrom();
						$contenuInputResponsable = $tache->getidResponsable();
						$contenuInputRealisateur = $tache->getidSoldeur();
						$ProgressionStatus = $tache->getProgressionStatus();
						$progression = $tache->getProgression();
						$startDate = $tache->getStartDate();
						$endDate = $tache->getFinishDate();
						{//Date de d�but de l'action
							$contenuAideTitre = "";
							$id = "date_debut";
							$label = '<label for="' . $id . '" >' . ucfirst(sprintf(__("Date de d&eacute;but %s", 'evarisk'), __("de la t&acirc;che",'evarisk'))) . '</label> : <span class="fieldInfo pointer" id="putTodayActionStart" >' . __('Aujourd\'hui', 'evarisk') . '</span>';
							$labelInput = '';
							$nomChamps = "date_debut";
							$output .= $label . EvaDisplayInput::afficherInput('text', $id, $startDate, $contenuAideTitre, $labelInput, $nomChamps, $grise, true, 255, '', 'date') . '';
						}
						{//Date de d�but de l'action
							$contenuAideTitre = "";
							$id = "date_fin";
							$label = '<label for="' . $id . '" >' . ucfirst(sprintf(__("Date de fin %s", 'evarisk'), __("de la t&acirc;che",'evarisk'))) . '</label> : <span class="fieldInfo pointer" id="putTodayActionEnd" >' . __('Aujourd\'hui', 'evarisk') . '</span>';
							$labelInput = '';
							$nomChamps = "date_fin";
							$output .= $label . EvaDisplayInput::afficherInput('text', $id, $endDate, $contenuAideTitre, $labelInput, $nomChamps, $grise, true, 255, '', 'date') . '';
						}
						{//Avancement
							$contenuAideDescription = "";
							$labelInput = __("Avancement", 'evarisk') . ' : ';
							$id = "avancement";
							$nomChamps = "avancement";
							$output .= EvaDisplayInput::afficherInput('text', $id, $progression, $contenuAideDescription, $labelInput, $nomChamps, true, true, 3, '', 'number', '10%') . '<div id="sliderAvancement" ></div>';
						}
						{//Mark all sub element as done
							$output .= '<br/><br/><br/><input type="checkbox" value="markSubAsDone" id="markSubAsDone" name="markSubAsDone" /><label for="markSubAsDone" >' . __('Appliquer ces changements &eacutegalement &agrave; tous les sous-&eacute;l&eacute;ments de l\'&eacute;l&eacute;ment courant', 'evarisk') . '</label>';
						}
						$output .= '
<script type="text/javascript">
	digirisk(document).ready(function(){
		digirisk("#sliderAvancement").slider({
			value:' . $progression . ',
			min: 0,
			max: 100,
			step: 1,
			slide: function(event, ui){
				digirisk( "#' . $id . '" ).val( ui.value );
			}
		});
		digirisk( "#' . $id . '" ).val( digirisk( "#sliderAvancement" ).slider( "value" ) );
		digirisk( "#' . $id . '" ).attr("style",digirisk( "#' . $id . '" ).attr("style") + "border:0px solid #000000;");
		digirisk("#putTodayActionStart").click(function(){
			digirisk("#date_debut").val("' . date('Y-m-d') . '");
		});
		digirisk("#putTodayActionEnd").click(function(){
			digirisk("#date_fin").val("' . date('Y-m-d') . '");
		});
	});
</script>';
						echo $output;
					}
					break;
				}
			}
			break;
			case TABLE_ACTIVITE:
				switch($_REQUEST['act'])
				{
					case 'save':
					case 'update':
					case 'update_from_external':
					case 'actionDone':
					{
						global $wpdb;
						switch($_REQUEST['act']){
							case 'save':
								$action = __('sauvegard&eacute;e', 'evarisk');
							break;
							case 'update':
							case 'actionDone':
							case 'update_from_external':
								$action = __('mise &agrave; jour', 'evarisk');
							break;
						}
						$orignal_requested_act = $_REQUEST['act'];
						if($_REQUEST['act'] == 'update_from_external')
							$_REQUEST['act'] = 'update';

						$activite = new EvaActivity($_REQUEST['id']);
						$activite->load();
						$old_activite = new EvaActivity($_REQUEST['id']);
						$old_activite->load();
						$activite->setName($_REQUEST['nom_activite']);
						$activite->setDescription($_REQUEST['description']);
						$activite->setRelatedTaskId($_REQUEST['idPere']);
						$activite->setStartDate(!empty($_REQUEST['date_debut'])?$_REQUEST['date_debut']:'');
						$activite->setFinishDate(!empty($_REQUEST['date_fin'])?$_REQUEST['date_fin']:'');
						$activite->setCout(!empty($_REQUEST['cout'])?$_REQUEST['cout']:'');
						$planned_time = !empty($_REQUEST['planned_time']) ? $_REQUEST['planned_time'] : '';
						$total_planned_time = 0;
						if ( !empty($planned_time) ) {
							if ( !empty($planned_time['hour']) ) {
								$total_planned_time += ($planned_time['hour'] * 60);
							}
							if ( !empty($planned_time['minutes']) ) {
								$total_planned_time += $planned_time['minutes'];
							}
						}
						$activite->setplanned_time( $total_planned_time );
						$activite->setProgression(!empty($_REQUEST['avancement'])?$_REQUEST['avancement']:'');
						$activite->setnom_exportable_plan_action(!empty($_REQUEST['nom_exportable_plan_action'])?$_REQUEST['nom_exportable_plan_action']:'no');
						$activite->setdescription_exportable_plan_action(!empty($_REQUEST['description_exportable_plan_action'])?$_REQUEST['description_exportable_plan_action']:'no');
						$activity_current_progression_status = $activite->getProgressionStatus();
						if ( empty($activity_current_progression_status) ) {
							$activite->setProgressionStatus('notStarted');
						}
						if((!empty($_REQUEST['avancement']) && ($_REQUEST['avancement'] > '0')) || ($activite->getProgressionStatus() == 'inProgress'))
							$activite->setProgressionStatus('inProgress');

						if((!empty($_REQUEST['avancement']) && ($_REQUEST['avancement'] == '100')) || ($_REQUEST['act'] == 'actionDone')){
							$activite->setProgressionStatus('Done');
							global $current_user;
							$activite->setidSoldeur($current_user->ID);
							$activite->setdateSolde(current_time('mysql', 0));
						}
						$activite->setidResponsable(!empty($_REQUEST['responsable_activite'])?$_REQUEST['responsable_activite']:0);
						$activite->save();

						/*	Update the action ancestor	*/
						$relatedTask = new EvaTask($activite->getRelatedTaskId());
						$relatedTask->load();
						$relatedTask->getTimeWindow();
						$relatedTask->computeProgression();
						$relatedTask->setEfficacite(!empty($_REQUEST['correctiv_action_efficiency_control'])?$_REQUEST['correctiv_action_efficiency_control']:'');
						$relatedTask->save();

						/*	Update the task ancestor	*/
						$wpdbTasks = Arborescence::getAncetre(TABLE_TACHE, $relatedTask->convertToWpdb());
						foreach($wpdbTasks as $task){
							unset($ancestorTask);
							$ancestorTask = new EvaTask($task->id);
							$ancestorTask->load();
							$ancestorTask->computeProgression();
							$ancestorTask->save();
							unset($ancestorTask);
						}

						$activity_save_message = '';
						if($activite->getStatus() != 'error'){
							$activite->load();
							switch($orignal_requested_act){
								case 'save':
									/*	Log modification on element and notify user if user subscribe	*/
									digirisk_user_notification::log_element_modification(TABLE_TACHE, $_REQUEST['idPere'], 'add_new_subtask', '', array(TABLE_ACTIVITE, $activite->getId(), $_REQUEST['nom_activite'], $_REQUEST['description']));
								break;
								case 'update':
								case 'update_from_external':
									foreach($activite as $key => $value){
										$activity_content[$key] = $value;
									}
									/*	Log modification on element and notify user if user subscribe	*/
									digirisk_user_notification::log_element_modification(TABLE_ACTIVITE, $_REQUEST['id'], 'update', $old_activite, $activity_content);
								break;
								case 'actionDone':
									/*	Log modification on element and notify user if user subscribe	*/
									digirisk_user_notification::log_element_modification(TABLE_ACTIVITE, $_REQUEST['id'], 'mark_done', '', '');
								break;
							}

							$activity_save_message= addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La fiche %s a correctement &eacute;t&eacute; %s', 'evarisk') . '</strong></p>', __('de l\'action', 'evarisk') . ' "' . stripslashes($_REQUEST['nom_activite']) . '"', $action));
						}
						else{
							$activity_save_message = addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="no-response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La fiche %s n\'a pas &eacute;t&eacute; %s.', 'evarisk') . '</strong></p>', __('de l\'action', 'evarisk') . ' "' . stripslashes($_REQUEST['nom_activite']) . '"', $action));
						}
						$activite->load();

						$messageInfo = '';
						$message_container = 'message' . $_REQUEST['tableProvenance'];
						if($orignal_requested_act != 'update_from_external'){
							$message_container = 'message';
							$messageInfo .= '
									jQuery("#rightEnlarging").show();
									jQuery("#equilize").click();
									jQuery("#partieEdition").html(jQuery("#loadingImg").html());
									if("' . $_REQUEST['affichage'] . '" == "affichageTable"){
										if(jQuery("#filAriane :last-child").is("label"))
											jQuery("#filAriane :last-child").remove();
										jQuery("#filAriane :last-child").after(\'<label>&nbsp;&raquo;&nbsp;&Eacute;dition&nbsp;de&nbsp;' . $_REQUEST['nom_activite'] . '</label>\');
										jQuery("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
											"post": "true",
											"table": "' . TABLE_ACTIVITE . '",
											"id": "' . $activite->getId() . '",
											"page": jQuery("#pagemainPostBoxReference").val(),
											"idPere": jQuery("#identifiantActuellemainPostBox").val(),
											"act": "edit",
											"partie": "right",
											"menu": jQuery("#menu").val(),
											"affichage": "affichageTable",
											"partition": "tout"
										});
									}
									else{
										var expanded = new Array();
										jQuery(".expanded").each(function(){expanded.push(jQuery(this).attr("id"));});
										jQuery("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
											"post": "true",
											"table": "' . TABLE_ACTIVITE . '",
											"act": "edit",
											"id": "' . $activite->getId() . '",
											"partie": "right",
											"menu": jQuery("#menu").val(),
											"affichage": "affichageListe",
											"expanded": expanded
										});
										jQuery("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
											"post": "true",
											"table": "' . TABLE_ACTIVITE . '",
											"act": "edit",
											"id": "' . $activite->getId() . '",
											"partie": "left",
											"menu": jQuery("#menu").val(),
											"affichage": "affichageListe",
											"expanded": expanded
										});
									}';
						}
						else{
							if(isset($_REQUEST['variables']) && (count($_REQUEST['variables']) > 0)){
								Risque::update_risk_rating_link_with_task($_REQUEST['idProvenance'], $actionSave['task_id'], array('before', 'after'));
							}

							$messageInfo .= '
									jQuery("#ongletVoirLesRisques").click();';
						}

						$messageInfo = '
<script type="text/javascript">
	digirisk(document).ready(function(){
		actionMessageShow("#' . $message_container . '", "' . $activity_save_message . '");
		setTimeout(\'actionMessageHide("#' . $message_container . '")\', "7500");
		' . $messageInfo . '
	});
</script>';
						echo $messageInfo;
					}
					break;


					case "ask_correctiv_action":
						global $current_user;
						$provenance = digirisk_tools::IsValid_Variable($_POST['receiver_element']);
						$token_for_element = digirisk_tools::IsValid_Variable($_POST['token_for_element']);
						$main_options = get_option('digirisk_options');

						if ( !empty($main_options['digi_ac_front_ask_create_parent_task']) ) {
							$_POST['parentTaskId'] = $main_options['digi_ac_front_ask_parent_task_id'];
							$asked_task = new EvaTask($main_options['digi_ac_front_ask_parent_task_id']);
						}
						else {
							$_POST['parentTaskId'] = evaTask::saveNewTask();
							$asked_task = new EvaTask($_POST['parentTaskId']);
							$asked_task->load();
							$asked_task->transfert($main_options['digi_ac_front_ask_parent_task_id']);
						}

						$actionSave = evaActivity::saveNewActivity();
						$asked_action = new EvaActivity($actionSave['action_id']);
						$asked_action->load();
						$asked_task->load();
						/*	Log modification on element and notify user if user subscribe	*/
						digirisk_user_notification::log_element_modification(TABLE_TACHE, $main_options['digi_ac_front_ask_parent_task_id'], 'add_new_subtask', '', array(TABLE_TACHE, $_POST['parentTaskId'], $_REQUEST['nom_activite'], $_REQUEST['description']));

						/*	Add the picture before action	*/
						if($token_for_element != ''){
							$associated_pictures = EvaPhoto::getPhotos($_REQUEST['tableProvenance'], $token_for_element);
							if(count($associated_pictures) > 0){
								if ( empty($main_options['digi_ac_front_ask_create_parent_task']) ) {
									EvaPhoto::associatePicture(TABLE_TACHE, $_POST['parentTaskId'], $associated_pictures[0]->id);
									$asked_task->setidPhotoAvant($associated_pictures[0]->id);
								}
								EvaPhoto::associatePicture(TABLE_ACTIVITE, $actionSave['action_id'], $associated_pictures[0]->id);
								$asked_action->setidPhotoAvant($associated_pictures[0]->id);
								EvaPhoto::unAssociatePicture($_REQUEST['tableProvenance'], $token_for_element, $associated_pictures[0]->id);
							}
						}

						/*	Add link to an element if user mark as linked	*/
						$provenance = digirisk_tools::IsValid_Variable($_POST['receiver_element']);
						if($provenance != ''){
							$provenanceComponents = explode('-_-', $provenance);

							$asked_task->setIdFrom($provenanceComponents[1]);
							$asked_task->setTableFrom($provenanceComponents[0]);
						}

						if ( empty($main_options['digi_ac_front_ask_create_parent_task']) ) {
							$asked_task->save();
						}
						$asked_action->save();

						evaUserLinkElement::setLinkUserElement(TABLE_TACHE, $actionSave['task_id'], $current_user->ID);
						evaUserLinkElement::setLinkUserElement(TABLE_ACTIVITE, $actionSave['action_id'], $current_user->ID);

						$task_notif_list = digirisk_user_notification::get_notification_list(TABLE_TACHE);
						foreach ($task_notif_list as $notification) {
							$wpdb->insert(DIGI_DBT_LIAISON_USER_NOTIFICATION_ELEMENT, array('status' => 'valid', 'date_affectation' => current_time('mysql', 0), 'id_attributeur' => $current_user->ID, 'id_user' => $current_user->ID, 'id_notification' => $notification->id, 'id_element' => $actionSave['task_id'],	'table_element' => TABLE_TACHE));
						}
						$action_notif_list = digirisk_user_notification::get_notification_list(TABLE_ACTIVITE);
						foreach ($action_notif_list as $notification) {
							$wpdb->insert(DIGI_DBT_LIAISON_USER_NOTIFICATION_ELEMENT, array('status' => 'valid', 'date_affectation' => current_time('mysql', 0), 'id_attributeur' => $current_user->ID, 'id_user' => $current_user->ID, 'id_notification' => $notification->id, 'id_element' => $actionSave['action_id'],	'table_element' => TABLE_ACTIVITE));
						}

						$messageInfo = '<script type="text/javascript">
								digirisk(document).ready(function(){
									jQuery("#message' . $_REQUEST['tableProvenance'] . '").addClass("updated");';
						if(($actionSave['task_status'] != 'error') && ($actionSave['action_status'] != 'error')){
							if($_REQUEST['tableProvenance'] == 'correctiv_action_ask'){
								$messageInfo .= '
									jQuery("#message' . $_REQUEST['tableProvenance'] . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="no-response" style="vertical-align:middle;" />&nbsp;<strong>' . sprintf( __('Votre demande d\'action corrective a bien &eacute;t&eacute; envoy&eacute;e, vous pourrez la retrouver sous le num&eacute;ro %s', 'evarisk'), ELEMENT_IDENTIFIER_ST . $actionSave['action_id'] ) . '</strong></p>') . '");';
							}
							else{
								$messageInfo .= '
									jQuery("#message' . $_REQUEST['tableProvenance'] . '").html("' . addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La fiche %s a correctement &eacute;t&eacute; %s', 'evarisk') . '</strong></p>', __('de l\'action corrective', 'evarisk') . ' "' . stripslashes($_REQUEST['nom_activite']) . '"', __('sauvegard&eacute;e', 'evarisk'))) . '");';
							}
						}
						else{
								$messageInfo .= '
									jQuery("#message' . $_REQUEST['tableProvenance'] . '").html("' . addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="no-response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La fiche %s n\'a pas &eacute;t&eacute; %s.', 'evarisk') . '</strong></p>', __('de l\'action corrective', 'evarisk') . ' "' . stripslashes($_REQUEST['nom_activite']) . '"', __('sauvegard&eacute;e', 'evarisk'))) . '");';
						}
						$messageInfo .= '
									jQuery("#ask_correctiv_action_picture_form").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
										"post":"true",
										"table":"' . TABLE_ACTIVITE . '",
										"act":"reload_correctiv_asker_picture_form",
										"tableProvenance":"' . $_REQUEST['tableProvenance'] . '",
										"token":"' . $token_for_element . '"
									});
									jQuery("#ask_correctiv_action_picture img").attr("src", "' . admin_url('images/loading.gif') . '");
									jQuery("#ask_correctiv_action_picture").hide();
									jQuery("#receiver_element").val("");
									jQuery("#receiver_element").val("");
									jQuery("#current_hierarchy_display").html("");
									jQuery("#save_button_container").show();
									jQuery("#save_in_progress").hide();
									jQuery("#message' . $_REQUEST['tableProvenance'] . '").show();
									setTimeout(function(){
										jQuery("#message' . $_REQUEST['tableProvenance'] . '").removeClass("updated");
										jQuery("#message' . $_REQUEST['tableProvenance'] . '").hide();
									},7500);
									jQuery("#ongletVoirLesRisques").click();
								});
							</script>';
						echo $messageInfo;
					break;


					case 'reload_correctiv_asker_picture_form':{
						if(isset($_REQUEST['delete_old']) && ($_REQUEST['delete_old'] == 'yes')){
							$associated_pictures = EvaPhoto::getPhotos($_REQUEST['tableProvenance'], $_REQUEST['token']);
							if(count($associated_pictures) > 0){
								EvaPhoto::unAssociatePicture($_REQUEST['tableProvenance'], $_REQUEST['token'], $associated_pictures[0]->id);
							}
						}
						echo EvaActivity::task_asker_add_picture($_REQUEST['tableProvenance'], $_REQUEST['token']);
					}break;

					case "addAction" :
					{
						$_POST['parentTaskId'] = evaTask::saveNewTask();
						$actionSave = evaActivity::saveNewActivity();

						/*	Make the link between a corrective action and a risk evaluation	*/
						$query =
							$wpdb->prepare(
								"SELECT id_evaluation
								FROM " . TABLE_AVOIR_VALEUR . "
								WHERE id_risque = '%d'
									AND Status = 'Valid'
								ORDER BY id DESC
								LIMIT 1",
								$_REQUEST['idProvenance']
							);
						$evaluation = $wpdb->get_row($query);
						evaTask::liaisonTacheElement(TABLE_AVOIR_VALEUR, $evaluation->id_evaluation, $actionSave['task_id'], 'demand');

						$messageInfo = '<script type="text/javascript">
								digirisk(document).ready(function(){
									digirisk("#message' . $_REQUEST['tableProvenance'] . '").addClass("updated");';
						if(($actionSave['task_status'] != 'error') && ($actionSave['action_status'] != 'error')){
							$messageInfo = $messageInfo . '
									digirisk("#message' . $_REQUEST['tableProvenance'] . '").html("' . addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La fiche %s a correctement &eacute;t&eacute; %s', 'evarisk') . '</strong></p>', __('de l\'action corrective', 'evarisk') . ' "' . stripslashes($_REQUEST['nom_activite']) . '"', __('sauvegard&eacute;e', 'evarisk'))) . '");';
						}
						else{
							$messageInfo = $messageInfo . '
									digirisk("#message' . $_REQUEST['tableProvenance'] . '").html("' . addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="no-response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La fiche %s n\'a pas &eacute;t&eacute; %s.', 'evarisk') . '</strong></p>', __('de l\'action corrective', 'evarisk') . ' "' . stripslashes($_REQUEST['nom_activite']) . '"', __('sauvegard&eacute;e', 'evarisk'))) . '");';
						}
						$messageInfo = $messageInfo . '
									digirisk("#message' . $_REQUEST['tableProvenance'] . '").show();
									setTimeout(function(){
										digirisk("#message' . $_REQUEST['tableProvenance'] . '").removeClass("updated");
										digirisk("#message' . $_REQUEST['tableProvenance'] . '").hide();
									},7500);
									digirisk("#ongletVoirLesRisques").click();
								});
							</script>';
						echo $messageInfo;
					}
					break;
					case 'add_control':
					case 'add_control_picture':
					{
						$options = get_option('digirisk_options');
						$create_task_and_subtask = ( !empty( $options['digi_ac_control_action_affectation'] ) && !empty( $options['digi_ac_control_create_parent_task'] ) && ( 'yes' == $options['digi_ac_control_create_parent_task'] ) ) ? true : false;

						$original_request_act = $_REQUEST['act'];

						if ( !$create_task_and_subtask ) {
							$_POST['parentTaskId'] = evaTask::saveNewTask();
						}
						else {
							$_POST['parentTaskId'] = $options['digi_ac_control_action_affectation'];
						}
						$actionSave = evaActivity::saveNewActivity();

						if(isset($_REQUEST['variables']) && (count($_REQUEST['variables']) > 0)){
							Risque::update_risk_rating_link_with_task($_REQUEST['idProvenance'], $actionSave['task_id'], array('before', 'after'));
						}

						if(isset($_REQUEST['original_act']) && ($_REQUEST['original_act'] == 'demandeAction')){
							$query =
								$wpdb->prepare(
									"SELECT id_evaluation
									FROM " . TABLE_AVOIR_VALEUR . "
									WHERE id_risque = '%d'
										AND Status = 'Valid'
									ORDER BY id DESC
									LIMIT 1",
									$_REQUEST['idProvenance']
								);
							$evaluation = $wpdb->get_row($query);
							evaTask::liaisonTacheElement(TABLE_AVOIR_VALEUR, $evaluation->id_evaluation, $actionSave['task_id'], 'demand');
						}

						$messageInfo = '
						<script type="text/javascript">
							digirisk(document).ready(function(){
								jQuery("#message' . $_REQUEST['tableProvenance'] . '").addClass("updated");';
						if(($actionSave['task_status'] != 'error') && ($actionSave['action_status'] != 'error')){
							$messageInfo .= '
								jQuery("#message' . $_REQUEST['tableProvenance'] . '").html("' . addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La fiche %s a correctement &eacute;t&eacute; %s', 'evarisk') . '</strong></p>', __('de l\'action corrective', 'evarisk') . ' "' . stripslashes($_REQUEST['nom_activite']) . '"', __('sauvegard&eacute;e', 'evarisk'))) . '");';
						}
						else{
							$messageInfo .= '
								jQuery("#message' . $_REQUEST['tableProvenance'] . '").html("' . addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="no-response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La fiche %s n\'a pas &eacute;t&eacute; %s.', 'evarisk') . '</strong></p>', __('de l\'action corrective', 'evarisk') . ' "' . stripslashes($_REQUEST['nom_activite']) . '"', __('sauvegard&eacute;e', 'evarisk'))) . '");';
						}
						$messageInfo .= '
								jQuery("#message' . $_REQUEST['tableProvenance'] . '").show();
								setTimeout(function(){
									jQuery("#message' . $_REQUEST['tableProvenance'] . '").removeClass("updated");
									jQuery("#message' . $_REQUEST['tableProvenance'] . '").hide();
								},7500);';

						if($original_request_act == 'add_control'){
							$messageInfo .= '
								jQuery("#ongletVoirLesRisques").click();';
						}
						elseif($original_request_act == 'add_control_picture'){
							$messageInfo .= '
								jQuery("#id_activite").val("' . $actionSave['action_id'] . '");
								jQuery("#idPere_activite").val("' . $actionSave['task_id'] . '");
								jQuery("#ActionSaveButton").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
									"post":"true",
									"nom":"reload_new_activity_button_container"
								});
								jQuery("#photosActionsCorrectives").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
									"post":"true",
									"table":"' . TABLE_ACTIVITE . '",
									"act":"pictureLoad",
									"tableElement":jQuery("#tableProvenance_activite").val(),
									"idElement":"' . $actionSave['action_id'] . '"
								});
								jQuery(".slider_variable").slider({ disabled: true });
								jQuery("#descriptionFormRisque").prop("disabled", true);
								jQuery(".slider_variable, #descriptionFormRisque").click(function(){
									alert(digi_html_accent_for_js("' . __('Vous ne pouvez plus modifier le risque &agrave; partir de cette interface. Vous devez ajouter une nouvelle action de contr&ocirc;le pour cela.', 'evarisk') . '"));
								});';
						}

						$messageInfo .= '
							});
						</script>';
						echo $messageInfo;
					}
					break;

					case 'pictureLoad':
					{
						_e( 'Cette fonctionnalité a été momentanément désactivée. VEuillez nous excuser pour la gêne occasionnée', 'evarisk' );
					}
					break;
					case 'actualiserAvancement':
					{
						$activites = $_REQUEST['activites'];
						$status = 'Valid';
						foreach($activites as $idActivite => $avancementActivite)
						{
							$activite = new EvaActivity($idActivite);
							$activite->load();
							$activite->setProgression($avancementActivite);
							if($_REQUEST['avancement'] == '100')
							{
								$activite->setProgressionStatus('Done');
								global $current_user;
								$activite->setidSoldeur($current_user->ID);
							}
							$activite->save();
							if($activite->getStatus() == 'error')
							{
								$status = 'error';
							}

							/*	Update the action ancestor	*/
							$relatedTask = new EvaTask($activite->getRelatedTaskId());
							$relatedTask->load();
							$relatedTask->getTimeWindow();
							$relatedTask->computeProgression();
							$relatedTask->save();
								/*	Update the task ancestor	*/
								$wpdbTasks = Arborescence::getAncetre(TABLE_TACHE, $relatedTask->convertToWpdb());
								foreach($wpdbTasks as $task)
								{
									unset($ancestorTask);
									$ancestorTask = new EvaTask($task->id);
									$ancestorTask->load();
									$ancestorTask->computeProgression();
									$ancestorTask->save();
									unset($ancestorTask);
								}
						}

						$messageInfo = '<script type="text/javascript">
							digirisk(document).ready(function(){
								digirisk("#message' . $_REQUEST['tableProvenance'] . '").addClass("updated");';
						if($status != 'error')
						{
							$messageInfo = $messageInfo . '
								digirisk("#message' . $_REQUEST['tableProvenance'] . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Les modifications ont correctement &eacute;t&eacute enregistr&eacute;es', 'evarisk') . '</strong></p>') . '");';
						}
						else
						{
							$messageInfo = $messageInfo . '
								digirisk("#message' . $_REQUEST['tableProvenance'] . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Les modifications n\'ont pas toutes &eacute;t&eacute correctement enregistr&eacute;es', 'evarisk') . '</strong></p>"') . '");';
						}
						$messageInfo = $messageInfo . '
									digirisk("#message' . $_REQUEST['tableProvenance'] . '").show();
									setTimeout(function(){
										digirisk("#message' . $_REQUEST['tableProvenance'] . '").removeClass("updated");
										digirisk("#message' . $_REQUEST['tableProvenance'] . '").hide();
									},7500);
									digirisk("#divSuiviAction' . $_REQUEST['tableProvenance'] . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "nom":"suiviAction",	"tableProvenance":"' . $_REQUEST['tableProvenance'] . '", "idProvenance": "' . $_REQUEST['idProvenance'] . '"});
									digirisk("#divSuiviAction' . TABLE_RISQUE . '").html(digirisk("#loadingImg").html());
								});
							</script>';
						echo $messageInfo;
					}
					break;
					case 'delete':
					{
						$activite = new EvaActivity($_REQUEST['id']);
						$activite->load();
						$activite->setStatus('Deleted');
						$activite->save();

						$messageInfo = '<script type="text/javascript">';
						if($activite->getStatus() != 'error'){
							/*	Log modification on element and notify user if user subscribe	*/
							digirisk_user_notification::log_element_modification(TABLE_ACTIVITE, $_REQUEST['id'], 'delete', '', '');

							$messageInfo = $messageInfo . '
								digirisk(document).ready(function(){
									digirisk("#message").addClass("updated");
									digirisk("#message").html("' . addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La fiche %s a correctement &eacute;t&eacute; supprim&eacute;e', 'evarisk') . '</strong></p>', __('de l\'action', 'evarisk') . ' "' . stripslashes($activite->getName()) . '"')) . '");
									digirisk("#message").show();
									setTimeout(function(){
										digirisk("#message").removeClass("updated");
										digirisk("#message").hide();
									},7500);';
						}
						else{
							$messageInfo = $messageInfo . '
								digirisk(document).ready(function(){
									digirisk("#message").addClass("updated");
									digirisk("#message").html("' . addslashes(sprintf('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="no-response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La fiche %s n\'a pas &eacute;t&eacute; supprim&eacute;e.', 'evarisk') . '</strong></p>', __('de l\'action', 'evarisk') . ' "' . stripslashes($activite->getName()) . '"')) . '");
									digirisk("#message").show();
									setTimeout(function(){
										digirisk("#message").removeClass("updated");
										digirisk("#message").hide();
									},7500);';
						}
						$messageInfo .= '
									digirisk("#rightEnlarging").show();
									digirisk("#equilize").click();
									if("' . $_REQUEST['affichage'] . '" == "affichageTable")
									{
										if(digirisk("#filAriane :last-child").is("label"))
											digirisk("#filAriane :last-child").remove();
										digirisk("#filAriane :last-child").after("<label>&nbsp;&raquo;&nbsp;&Eacute;dition&nbsp;de&nbsp;' . $_REQUEST['nom_activite'] . '</label>");
										digirisk("#partieEdition").html("");
										digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
										{
											"post": "true",
											"table": "' . TABLE_ACTIVITE . '",
											"id": "' . $activite->getId() . '",
											"page": digirisk("#pagemainPostBoxReference").val(),
											"idPere": digirisk("#identifiantActuellemainPostBox").val(),
											"act": "changementPage",
											"partie": "left",
											"menu": digirisk("#menu").val(),
											"affichage": "affichageTable",
											"partition": "tout"
										});
									}
									else
									{
										var expanded = new Array();
										digirisk(".expanded").each(function(){expanded.push(digirisk(this).attr("id"));});
										digirisk("#partieEdition").html("");
										digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
										{
											"post": "true",
											"table": "' . TABLE_ACTIVITE . '",
											"act": "changementPage",
											"id": "' . $activite->getId() . '",
											"partie": "left",
											"menu": digirisk("#menu").val(),
											"affichage": "affichageListe",
											"expanded": expanded
										});
									}
								});
							</script>';
						echo $messageInfo;
					}
					break;
					case 'setActivityInProgress':{
						$id = digirisk_tools::IsValid_Variable($_REQUEST['id']);
						$activite = new EvaActivity($id);
						$activite->load();
						$activite->setProgressionStatus('inProgress');
						$taskId = $activite->getRelatedTaskId();
						$activite->save();
						/*	Log modification on element and notify user if user subscribe	*/
						digirisk_user_notification::log_element_modification(TABLE_ACTIVITE, $id, 'set_in_progress', '', '');

						$updateTaskProgressionInTree = '';
						if(($taskId != '') && ($taskId > 0)){
							/*	Update the action ancestor	*/
							$relatedTask = new EvaTask($taskId);
							$relatedTask->load();
							$relatedTask->getTimeWindow();
							$relatedTask->computeProgression();
							$relatedTask->save();
							/*	Log modification on element and notify user if user subscribe	*/
							digirisk_user_notification::log_element_modification(TABLE_TACHE, $taskId, 'set_in_progress', '', '');
							$updateTaskProgressionInTree .= '
	digirisk(".taskInfoContainer-' . $taskId . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
		"post": "true",
		"table": "' . TABLE_TACHE . '",
		"act": "actualiseProgressionInTree",
		"id": "' . $taskId . '"
	});';

							/*	Update the task ancestor	*/
							$wpdbTasks = Arborescence::getAncetre(TABLE_TACHE, $relatedTask->convertToWpdb());
							foreach($wpdbTasks as $task)
							{
								unset($ancestorTask);
								$ancestorTask = new EvaTask($task->id);
								$ancestorTask->load();
								$ancestorTask->computeProgression();
								$ancestorTask->save();
								unset($ancestorTask);
								/*	Don't update the tree information if it is the root task	*/
								if($task->id != 1){
									/*	Log modification on element and notify user if user subscribe	*/
									digirisk_user_notification::log_element_modification(TABLE_TACHE, $task->id, 'set_in_progress', '', '');
									$updateTaskProgressionInTree .= '
	digirisk(".taskInfoContainer-' . $task->id . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
	{
		"post": "true",
		"table": "' . TABLE_TACHE . '",
		"act": "actualiseProgressionInTree",
		"id": "' . $task->id . '"
	});';
								}
							}
						}

						echo '
<script type="text/javascript">
	digirisk(".activityInfoContainer-' . $id . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
	{
		"post": "true",
		"table": "' . TABLE_ACTIVITE . '",
		"act": "actualiseProgressionInTree",
		"id": "' . $id . '"
	});' . $updateTaskProgressionInTree . '
</script>';
					}
					break;
					case 'actualiseProgressionInTree':
					{
						$id = digirisk_tools::IsValid_Variable($_REQUEST['id']);
						$action = new EvaActivity($id);
						$action->load();
						$statutProgression = '';
						switch($action->getProgressionStatus())
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
						echo $action->getProgression() . '%&nbsp;(' . $statutProgression . ')';
					}
					break;
				}
			break;

			case TABLE_ACTIVITE_SUIVI:
				$tableElement = (!empty($_REQUEST['table_element']) ? $_REQUEST['table_element'] : (!empty($_REQUEST['tableElement']) ? $_REQUEST['tableElement'] : ''));
				$idElement = (!empty($_REQUEST['id_element']) ? $_REQUEST['id_element'] :  (!empty($_REQUEST['idElement']) ? $_REQUEST['idElement'] : ''));
				require_once( EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/suivi_activite.class.php');
				switch($_REQUEST['act']) {
					case 'load_follow_up_edition_form':
						$follow_up_2_edit = digirisk_tools::IsValid_Variable($_REQUEST['follow_up_2_edit']);
						$table_element = digirisk_tools::IsValid_Variable($_REQUEST['table_element']);
						$id_element = digirisk_tools::IsValid_Variable($_REQUEST['id_element']);
						$follow_up_type = digirisk_tools::IsValid_Variable($_REQUEST['follow_up_type']);
						if ( empty($follow_up_type) || ( $follow_up_type == 'note' ) ) {
							echo suivi_activite::formulaireAjoutSuivi($table_element, $id_element, false, $follow_up_2_edit, TABLE_ACTIVITE_SUIVI);
						}
						elseif( !empty($follow_up_type) && ($follow_up_type == 'follow_up') ) {
							echo suivi_activite::formulaire_ajout_suivi_projet($table_element, $id_element, false, $follow_up_2_edit);
						}
					break;
				}
			break;

			case TABLE_LIAISON_USER_ELEMENT:
				require_once(EVA_LIB_PLUGIN_DIR . 'users/evaUserLinkElement.class.php');
				switch ( $_REQUEST['act'] ) {
					case 'save':
						evaUserLinkElement::setLinkUserElement($_REQUEST['tableElement'], $_REQUEST['idElement'], $_REQUEST['utilisateurs'], true, $_REQUEST['date_action_affection']);
					break;
					case 'reload_user_affectation_box':
						echo evaUserLinkElement::afficheListeUtilisateur($_REQUEST['tableElement'], $_REQUEST['idElement']);
					break;
				}
				break;
			case TABLE_DUER:
				$output='';
				$tableElement = $_REQUEST['tableElement'];
				$idElement = $_REQUEST['idElement'];
				switch ($_REQUEST['act']) {
					case 'generateSummary' :
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];
						echo  eva_documentUnique::getBoxBilan($tableElement, $idElement);
					break;
					case 'voirHistoriqueDocument' :
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];

						$output .= '
<div class="clear" id="summaryGeneratedDocumentSlector" >
	<div class="alignleft selected" id="generatedDUER" >' . __('Document unique', 'evarisk') . '</div>
	<div class="alignleft" id="generatedFGP" >' . __('Fiches de groupement', 'evarisk') . '</div>
	<div class="alignleft" id="generatedFP" >' . __('Fiches des unit&eacute;s de travail', 'evarisk') . '</div>
	<div class="alignleft" id="generatedRS" >' . __('Synth&egrave;se des risques', 'evarisk') . '</div>
	<div class="alignleft" id="generatedFEP" >' . __('Fiches de p&eacute;nibilit&eacute;', 'evarisk') . '</div>
</div>';

						/*	Start "document unique" part	*/
						$output .= '<div id="generatedDUERContainer" class="generatedDocContainer" ><div class="clear bold" >' . __('Documents unique pour le groupement', 'evarisk') . '</div><div class="DUERContainer" >' . eva_documentUnique::getDUERList($tableElement, $idElement) . '</div></div>';

						/*	Start groupe sheet part	*/
						$output .= '<div class="hide generatedDocContainer" id="generatedFGPContainer" ><div class="clear bold" >' . __('Fiches du groupement courant', 'evarisk') . '</div>
						<div class="FGPContainer" >' . eva_gestionDoc::getGeneratedDocument($tableElement, $idElement, 'list', '', 'fiche_de_groupement') . '</div><div class="clear" >&nbsp;</div>
						<div class="clear bold" >' . __('Fiches des sous-groupements du groupement courant', 'evarisk') . '</div>
						<div class="FGPContainer" >' . eva_GroupSheet::getGroupSheetCollectionHistory($tableElement, $idElement) . '</div></div>';

						/*	Start work unit sheet part	*/
						$output .= '<div class="hide generatedDocContainer" id="generatedFPContainer" ><div class="clear bold" >' . __('Fiches des unit&eacute;s de travail du groupement', 'evarisk') . '</div>
						<div class="FPContainer" >' . eva_WorkUnitSheet::getWorkUnitSheetCollectionHistory($tableElement, $idElement) . '</div></div>';

						/*	Start risk listing summary part	*/
						$output .= '<div class="hide generatedDocContainer" id="generatedRSContainer" ><div class="clear bold" >' . __('Fiches de synth&egrave;se des risques', 'evarisk') . '</div>
						<div class="RSContainer" >' . eva_gestionDoc::getGeneratedDocument($tableElement, $idElement, 'list', '', 'listing_des_risques') . '</div></div>';

						/*	Start risk listing summary part	*/
						$output .= '<div class="hide generatedDocContainer" id="generatedFEPContainer" ><div class="clear bold" >' . __('Fiches de p&eacute;nibilit&eacute;', 'evarisk') . '</div>
						<div class="FEPContainer" >' . eva_gestionDoc::getGeneratedDocument($tableElement, $idElement, 'list', '', 'fiche_exposition_penibilite') . '</div></div>';

						$output .= '
<div><a href="' . LINK_TO_DOWNLOAD_OPEN_OFFICE . '" target="OOffice" >' . __('T&eacute;l&eacute;charger Open Office', 'evarisk') . '</a></div>
<script type="text/javascript" >
	digirisk("#summaryGeneratedDocumentSlector div").click(function(){
		digirisk("#summaryGeneratedDocumentSlector div").each(function(){
			digirisk(this).removeClass("selected");
		});
		digirisk(".generatedDocContainer").each(function(){
			digirisk(this).hide();
		});
		digirisk(this).addClass("selected");
		digirisk("#" + jQuery(this).attr("id") + "Container").show();
	});

	var currentTab = digirisk("#subTabSelector").val();
	if(currentTab != ""){
		digirisk("#generated" + currentTab).click();
	}
	digirisk("#subTabSelector").val("");
</script>';
						echo $output;
					break;
					case 'saveDocumentUnique' :
						require_once(EVA_METABOXES_PLUGIN_DIR . 'documentUnique/documentUniquePersistance.php');
						require_once(EVA_METABOXES_PLUGIN_DIR . 'documentUnique/documentUnique.php');
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];
						echo eva_documentUnique::formulaireGenerationDocumentUnique($tableElement, $idElement) . '<script type="text/javascript" >digirisk(document).ready(function(){digirisk("#ui-datepicker-div").hide();});</script>';
					break;
					case 'loadNewModelForm':
						echo evaDisplayDesign::getNewModelUploadForm($tableElement, $idElement);
					break;
					case 'workSheetUnitCollectionGenerationForm':
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];
						echo eva_WorkUnitSheet::getWorkUnitSheetCollectionGenerationForm($tableElement, $idElement);
					break;
					case 'riskListingGeneration':
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];
						echo eva_gestionDoc::getRiskListingGenerationForm($tableElement, $idElement);
					break;
					case 'ficheDePenibiliteGeneration':
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];
						echo eva_gestionDoc::get_form_penibilite_generation($tableElement, $idElement);
					break;
					case 'documentUniqueGenerationForm':
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];
						echo eva_documentUnique::formulaireGenerationDocumentUnique($tableElement, $idElement);
					break;
					case 'reGenerateDUER':
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];
						$idDocument = $_REQUEST['idDocument'];
						eva_gestionDoc::generateSummaryDocument($tableElement, $idElement, 'odt', $idDocument);
						$documentUniqueInfos = eva_documentUnique::getDernierDocumentUnique($tableElement, $idElement, $idDocument);
						$odtFile = 'documentUnique/' . $tableElement . '/' . $idElement . '/' . $documentUniqueInfos->nomDUER . '_V' . $documentUniqueInfos->revisionDUER . '.odt';
						if( is_file(EVA_RESULTATS_PLUGIN_DIR . $odtFile) )
						{
							echo
'<script type="text/javascript" >
	(function(){
		jQuery("#reGenerateDUER' . $idDocument . 'Container").html(\'' . __('Nouveau', 'evarisk') . '&nbsp;<a href="' . EVA_RESULTATS_PLUGIN_URL . $odtFile . '" target="evaDUEROdt" >Odt</a>\');
	})(digirisk)
</script>';
						}
					break;
					case 'deleteDUER':
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];
						$idDocument = $_REQUEST['idDocument'];
						$delete_document = $wpdb->update( TABLE_DUER, array( 'status' => 'deleted', ), array( 'id' => $idDocument, ) );
						if( false !== $delete_document )
						{
							$messageToOutput = "<img src='" . EVA_MESSAGE_SUCCESS . "' alt='success' class='messageIcone' />" . __('Le document unique &agrave; bien &eacute;t&eacute; supprim&eacute;.', 'evarisk');
							echo
'<script type="text/javascript" >
	(function(){
		actionMessageShow("#message' . TABLE_DUER . '", "' . $messageToOutput . '");
		setTimeout(\'actionMessageHide("#message' . TABLE_DUER . '")\',5000);
		digirisk("#ongletHistoriqueDocument").click();
	})(digirisk)
</script>';
						}
					break;
					case 'groupementSheetGeneration':
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];
						echo eva_GroupSheet::getGroupSheetGenerationForm($tableElement, $idElement);
					break;
				}
				break;
			case TABLE_FP:
				switch($_REQUEST['act'])
				{
					case 'saveFichePoste':
					case 'generateWorkUnitSheet':
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];
						if($_REQUEST['act'] == 'saveFichePoste')
						{
							$workUnitinformations = eva_UniteDeTravail::getWorkingUnit($idElement);

							$_POST['description'] = $workUnitinformations->description;
							$_POST['telephone'] = $workUnitinformations->telephoneUnite;

							$workUnitAdress = new EvaBaseAddress($workUnitinformations->id_adresse);
							$workUnitAdress->load();

							$_POST['adresse'] = serialize( array( 'adresse' => trim( $workUnitAdress->getFirstLine() . " " . $workUnitAdress->getSecondLine() ), 'codePostal' => trim($workUnitAdress->getPostalCode()), 'ville' => trim($workUnitAdress->getCity()) ) );

							require_once(EVA_METABOXES_PLUGIN_DIR . 'ficheDePoste/ficheDePostePersistance.php');
						}
						echo eva_WorkUnitSheet::getWorkUnitSheetGenerationForm($tableElement, $idElement) . '<script type="text/javascript" >digirisk(document).ready(function(){digirisk("#ui-datepicker-div").hide();});</script>';
					break;
					case 'workUnitSheetHisto' :
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];

						$output = '
<div class="clear" id="summaryGeneratedDocumentSlector" >
	<div class="alignleft selected" id="generatedFP" >' . __('Fiches de l\'unit&eacute; de travail', 'evarisk') . '</div>
	<div class="alignleft" id="generatedRS" >' . __('Synth&egrave;se des risques', 'evarisk') . '</div>
	<div class="alignleft" id="generatedFEP" >' . __('Fiches de p&eacute;nibilit&eacute;', 'evarisk') . '</div>
</div>
<div id="generatedFPContainer" class="generatedDocContainer" ><div class="clear bold" >' . __('Fiches de l\'unit&eacute; de travail', 'evarisk') . '</div><div class="DUERContainer" >' . eva_gestionDoc::getGeneratedDocument($tableElement, $idElement, 'list', '', 'fiche_de_poste') . '</div></div>
<div class="hide generatedDocContainer" id="generatedRSContainer" ><div class="clear bold" >' . __('Fiches de synth&egrave;se des risques', 'evarisk') . '</div><div class="RSContainer" >' . eva_gestionDoc::getGeneratedDocument($tableElement, $idElement, 'list', '', 'listing_des_risques') . '</div></div>
<div class="hide generatedDocContainer" id="generatedFEPContainer" ><div class="clear bold" >' . __('Fiches de p&eacute;nibilit&eacute;', 'evarisk') . '</div><div class="RSContainer" >' . eva_gestionDoc::getGeneratedDocument($tableElement, $idElement, 'list', '', 'fiche_exposition_penibilite') . '</div></div>
<div><a href="' . LINK_TO_DOWNLOAD_OPEN_OFFICE . '" target="OOffice" >' . __('T&eacute;l&eacute;charger Open Office', 'evarisk') . '</a></div>
<script type="text/javascript" >
	digirisk(".digi_regenerate_file").unbind("click");
	digirisk(".digi_regenerate_file").click(function(){
		var doc_id = jQuery(this).attr("id");
		var data = {
			action: "digi_ajax_regenerate_file",
			doc_id: doc_id.replace("digi_file_to_regenerate_", ""),
			table_element: jQuery("#" + doc_id + "_table_element" ).val(),
			id_element: jQuery("#" + doc_id + "_id_element" ).val(),
			document_type: jQuery("#" + doc_id + "_document_type" ).val(),
		};
		jQuery.post(ajaxurl, data, function(response){
			if ( response[0] ) {
				jQuery("#" + response[1]).html( response[2] );
			}
			else {
				jQuery("#" + response[1]).append( response[2] );
			}
		}, "json");
	});

	digirisk("#summaryGeneratedDocumentSlector div").click(function(){
		digirisk("#summaryGeneratedDocumentSlector div").each(function(){
			digirisk(this).removeClass("selected");
		});
		digirisk(".generatedDocContainer").each(function(){
			digirisk(this).hide();
		});
		digirisk(this).addClass("selected");
		digirisk("#" + jQuery(this).attr("id") + "Container").show();
	});

	var currentTab = digirisk("#subTabSelector").val();
	if(currentTab != ""){
		digirisk("#generated" + currentTab).click();
	}
	digirisk("#subTabSelector").val("");
</script>';
						echo $output;
						break;
					case 'saveWorkUnitSheetForGroupement':{
						$file_to_zip = array();

						$mainTableElement = $tableElement = $_REQUEST['tableElement'];
						$mainIDElement = $idElement = $_REQUEST['idElement'];
						$groupementParent = EvaGroupement::getGroupement($idElement);
						$arbre = arborescence::getCompleteUnitList($tableElement, $idElement);
						$pathToZip = EVA_RESULTATS_PLUGIN_DIR . 'documentUnique/' . $tableElement . '/' . $idElement. '/';
						$dir_with_files = $pathToZip . date('YmdHis') . '_fichesDesUnitesDeTravail';
						wp_mkdir_p($dir_with_files);
						foreach ( $arbre as $workUnit ) {
							$workUnitinformations = eva_UniteDeTravail::getWorkingUnit($workUnit['id']);
							$_POST['description'] = $workUnitinformations->description;
							$_POST['telephone'] = $workUnitinformations->telephoneUnite;

							$workUnitAddress = new EvaBaseAddress($workUnitinformations->id_adresse);
							$workUnitAddress->load();
							$_POST['adresse'] = trim($workUnitAddress->getFirstLine() . " " . $workUnitAddress->getSecondLine() . " " . $workUnitAddress->getPostalCode() . " " . $workUnitAddress->getCity());

							$_POST['tableElement'] = $workUnit['table'];
							$_POST['idElement'] = $workUnit['id'];
							$_POST['nomDuDocument'] = date('Ymd') . '_' . ELEMENT_IDENTIFIER_UT . $workUnit['id'] . '_' . sanitize_title(str_replace(' ', '_', str_replace('/', '_', sanitize_title($workUnit['nom']))));
							$_POST['nomEntreprise'] = $groupementParent->nom;

							include(EVA_METABOXES_PLUGIN_DIR . 'ficheDePoste/ficheDePostePersistance.php');
							$lastDocument = eva_gestionDoc::getGeneratedDocument($tableElement, $idElement, 'last', '', 'fiche_de_poste');
							$odt_file_name = $lastDocument->name . '_V' . $lastDocument->revision . '.odt';
							$odtFile = 'ficheDePoste/' . $workUnit['table'] . '/' . $workUnit['id'] . '/' . $odt_file_name;
							if ( is_file(EVA_RESULTATS_PLUGIN_DIR . $odtFile) ) {
								$file_to_zip[] = EVA_RESULTATS_PLUGIN_DIR . $odtFile;
								copy(EVA_RESULTATS_PLUGIN_DIR . $odtFile, $dir_with_files . '/' . $odt_file_name);
							}
						}

						if ( is_dir($dir_with_files) ) {
							eva_gestionDoc::saveNewDoc('fiche_de_poste_groupement', $mainTableElement, $mainIDElement, str_replace(EVA_GENERATED_DOC_DIR, '', $dir_with_files));
						}

						$saveZipFileActionMessage = '';
						wp_mkdir_p($pathToZip);
						if ( count($file_to_zip) > 0 ) {
							/*	ZIP THE FILE	*/
							$zipFileName = date('YmdHis') . '_fichesDesUnitesDeTravail.zip';
							$archive = new eva_Zip($zipFileName);
							$archive->setFiles($file_to_zip);
							$archive->compressToPath($pathToZip);
							$saveWorkSheetUnitStatus = eva_gestionDoc::saveNewDoc('fiche_de_poste_groupement', $mainTableElement, $mainIDElement, str_replace(EVA_GENERATED_DOC_DIR, '', $pathToZip . $zipFileName));
							if ( $saveWorkSheetUnitStatus == 'error' ) {
								$messageInfo = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Une erreur est survenue lors de l\'enregistrement des fiches des unit&eacute; de travail pour ce groupement', 'evarisk');
							}
							else {
								$messageInfo = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' class=\'messageIcone\' alt=\'succes\' />' . __('Les fiches des unit&eacute;s de travail ont correctement &eacute;t&eacute; enregistr&eacute;es.', 'evarisk');
								rmdir($dir_with_files);
							}
						$saveZipFileActionMessage = '
	digirisk(document).ready(function(){
			actionMessageShow("#message' . TABLE_DUER . '", "' . $messageInfo . '");
			setTimeout(\'actionMessageHide("#message' . TABLE_DUER . '")\',7500);
	});';
						}

						echo '
<script type="text/javascript" >
	digirisk("#subTabSelector").val("FP");
	' . $saveZipFileActionMessage . '
	digirisk("#ongletHistoriqueDocument").click();
</script>';
					}
					break;
					case 'voirHistoriqueFicheDePosteGroupement':
					{
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];
						$output = eva_WorkUnitSheet::getWorkUnitSheetCollectionGenerationForm($tableElement, $idElement) . '
<div class="clear" >
	' . eva_WorkUnitSheet::getWorkUnitSheetCollectionHistory($tableElement, $idElement) . '
</div>';
						echo $output;
					}
					break;

					case 'saveFicheGroupement':
						$tableElement = $_REQUEST['tableElement'];
						$idElement = $_REQUEST['idElement'];
						$groupementInformations = EvaGroupement::getGroupement($idElement);
						$_POST['description'] = $groupementInformations->description;
						$_POST['telephone'] = $groupementInformations->telephoneGroupement;

						$groupementAddress = new EvaBaseAddress($groupementInformations->id_adresse);
						$groupementAddress->load();
						$_POST['adresse'] = trim($groupementAddress->getFirstLine() . " " . $groupementAddress->getSecondLine() . " " . $groupementAddress->getPostalCode() . " " . $groupementAddress->getCity());

						require_once(EVA_METABOXES_PLUGIN_DIR . 'ficheDePoste/ficheDeGroupementPersistance.php');
						echo eva_GroupSheet::getGroupSheetGenerationForm($tableElement, $idElement) . '<script type="text/javascript" >digirisk(document).ready(function(){digirisk("#generateFGP").click();});</script>';
					break;
					case 'saveGroupSheetForGroupement':
					{
						$file_to_zip = array();

						$mainTableElement = $tableElement = $_REQUEST['tableElement'];
						$mainIDElement = $idElement = $_REQUEST['idElement'];
						$groupementParent = EvaGroupement::getGroupement($idElement);
						$arbre = arborescence::getCompleteGroupList($tableElement, $idElement);
						$pathToZip = EVA_RESULTATS_PLUGIN_DIR . 'ficheDeGroupement/' . $tableElement . '/' . $idElement. '/';
						foreach($arbre as $group)
						{
							$_POST['tableElement'] = $group['table'];
							$_POST['idElement'] = $group['id'];
							$_POST['nomDuDocument'] = date('Ymd') . '_' . ELEMENT_IDENTIFIER_GP . $group['id'] . '_' . sanitize_title(str_replace(' ', '_', $group['nom']));
							$_POST['nomEntreprise'] = $groupementParent->nom;

							$_POST['description'] = $groupementParent->description;
							$_POST['telephone'] = $groupementParent->telephoneGroupement;

							$groupementAddress = new EvaBaseAddress($groupementParent->id_adresse);
							$groupementAddress->load();
							$_POST['adresse'] = trim($groupementAddress->getFirstLine() . " " . $groupementAddress->getSecondLine() . " " . $groupementAddress->getPostalCode() . " " . $groupementAddress->getCity());


							include(EVA_METABOXES_PLUGIN_DIR . 'ficheDePoste/ficheDeGroupementPersistance.php');
							$lastDocument = eva_gestionDoc::getGeneratedDocument($tableElement, $idElement, 'last', '', 'fiche_de_groupement');
							$odtFile = 'ficheDeGroupement/' . $group['table'] . '/' . $group['id'] . '/' . $lastDocument->name . '_V' . $lastDocument->revision . '.odt';
							if( is_file(EVA_RESULTATS_PLUGIN_DIR . $odtFile) )
							{
								$file_to_zip[] = EVA_RESULTATS_PLUGIN_DIR . $odtFile;
							}
						}

						$saveZipFileActionMessage = '';
						wp_mkdir_p($pathToZip);
						if(count($file_to_zip) > 0)
						{
							/*	ZIP THE FILE	*/
							$zipFileName = date('YmdHis') . '_fichesDeGroupement.zip';
							$archive = new eva_Zip($zipFileName);
							$archive->setFiles($file_to_zip);
							$archive->compressToPath($pathToZip);
							$saveWorkSheetUnitStatus = eva_gestionDoc::saveNewDoc('fiches_de_groupement', $mainTableElement, $mainIDElement, str_replace(EVA_GENERATED_DOC_DIR, '', $pathToZip . $zipFileName));
							if($saveWorkSheetUnitStatus == 'error')
							{
								$messageInfo = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Une erreur est survenue lors de l\'enregistrement des fiches de groupement pour ce groupement', 'evarisk');
							}
							else
							{
								$messageInfo = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' class=\'messageIcone\' alt=\'succes\' />' . __('Les fiches de groupement ont correctement &eacute;t&eacute; enregistr&eacute;es.', 'evarisk');
							}
						$saveZipFileActionMessage = '
	digirisk(document).ready(function(){
			actionMessageShow("#message' . TABLE_DUER . '", "' . $messageInfo . '");
			setTimeout(\'actionMessageHide("#message' . TABLE_DUER . '")\',7500);
	});';
						}

						echo '
<script type="text/javascript" >
	digirisk("#subTabSelector").val("FGP");
	' . $saveZipFileActionMessage . '
	digirisk("#ongletHistoriqueDocument").click();
</script>';
					}
					break;
				}
			break;
			case TABLE_GED_DOCUMENTS:
				$tableElement = !empty($_REQUEST['tableElement']) ? $_REQUEST['tableElement'] : '';
				$idElement = !empty($_REQUEST['idElement']) ? $_REQUEST['idElement'] : '';
				switch ($_REQUEST['act']) {
					case 'reload_fiche_penibilite_container':
							$table_element = !empty($_REQUEST['table_element']) ? $_REQUEST['table_element'] : '';
							$id_element = !empty($_REQUEST['id_element']) ? $_REQUEST['id_element'] : '';
							$id_user = !empty($_REQUEST['id_user']) ? $_REQUEST['id_user'] : '';
							echo eva_gestionDoc::getGeneratedDocument($table_element, $id_element, 'list', '', 'fiche_exposition_penibilite', $id_user);
						break;
					case 'save_fiche_penibilite_specific_user':
						$element = !empty($_REQUEST['element_infos']) ? $_REQUEST['element_infos'] : '';
						if ( !empty($element) ) {
							$elements = explode('_-digi-_', $element);
							if ( (count($elements) == 3) && ($elements[0] == DIGI_DBT_USER) && ($elements[1] == 'all') ) {
								$query = $wpdb->prepare(
									"SELECT *
									FROM " . TABLE_LIAISON_USER_ELEMENT . "
									WHERE id_user = %d
										AND table_element IN ('" . TABLE_GROUPEMENT . "','" . TABLE_UNITE_TRAVAIL . "')
										AND status IN ('valid', 'moderated', 'deleted')"
										, $elements[2]
								);
								$user_affected_elements = $wpdb->get_results($query);
								if ( !empty($user_affected_elements) ) {
									$file_to_zip = array();
									$pathToZip = EVA_RESULTATS_PLUGIN_DIR . 'ficheDeRisques/' . $elements[2] . '/';
									$element_to_use = array();
									$reload_script = '';
									foreach ( $user_affected_elements as $element ) {
										$element_to_use[$element->table_element][$element->id_element][] = $element;
										$reload_script .= '
												jQuery("#digi_generate_FEP_' . $element->table_element . '_-digi-_' . $element->id_element . '_-digi-_' . $elements[2] . '_container").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
													"post" : "true",
													"table" : "' . TABLE_GED_DOCUMENTS . '",
													"act" : "reload_fiche_penibilite_container",
													"table_element" : "' . $element->table_element . '",
													"id_element" : "' . $element->id_element . '",
													"id_user" : "' . $elements[2] . '",
												});';
									}
									if ( !empty($element_to_use) ) {
										foreach ( $element_to_use as $table_element => $table_element_details) {
											foreach ( $table_element_details as $id_element => $element_details) {
												$last_file = eva_gestionDoc::generate_fiche_penibilite($table_element, $id_element, $element_details);

												if ( !empty($last_file['path']) && is_file($last_file['path']) ) {
													$file_to_zip[] = $last_file['path'];
												}
											}
										}
									}

									$saveZipFileActionMessage = '';
									wp_mkdir_p($pathToZip);
									if ( !empty($file_to_zip) ) {
										/*	ZIP THE FILE	*/
										$zipFileName = date('YmdHis') . '_ficheDeRisques.zip';
										$archive = new eva_Zip($zipFileName);
										$archive->setFiles($file_to_zip);
										$archive->compressToPath($pathToZip);
										$saveWorkSheetUnitStatus = eva_gestionDoc::saveNewDoc('fiche_exposition_penibilite', 'USER', $elements[2], str_replace(EVA_GENERATED_DOC_DIR, '', $pathToZip . $zipFileName));
										if ($saveWorkSheetUnitStatus == 'error') {
											$messageInfo = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Une erreur est survenue lors de l\'enregistrement des fiches de p&eacute;nibilit&eacute; pour ce groupement', 'evarisk');
										}
										else {
											$messageInfo = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' class=\'messageIcone\' alt=\'succes\' />' . __('Les fiches de p&eacute;nibilit&eacute; ont correctement &eacute;t&eacute; enregistr&eacute;es.', 'evarisk');
										}
									}
								}

								echo eva_GroupSheet::getGroupSheetCollectionHistory('USER', $elements[2], 'fiche_exposition_penibilite', ELEMENT_IDENTIFIER_GFEP);
								if ( !empty($reload_script) ) {
									echo '<script type="text/javascript" >digirisk(document).ready(function(){' . $reload_script . '});</script>';
								}
							}
							else {
								$query = $wpdb->prepare(
									"SELECT *
									FROM " . TABLE_LIAISON_USER_ELEMENT . "
									WHERE id_element = '%s'
										AND table_element = '%s'
										AND status IN ('valid', 'moderated', 'deleted')
										AND id_user = %d"
									, $elements[1], $elements[0], $elements[2]
								);
								$users = $wpdb->get_results($query);
								eva_gestionDoc::generate_fiche_penibilite($elements[0], $elements[1], $users);

								echo eva_gestionDoc::getGeneratedDocument($elements[0], $elements[1], 'list', '', 'fiche_exposition_penibilite', $elements[2]);
							}
						}
						break;
					case 'save_list_risk':
						$sheet_infos = array();
						$sheet_infos['sheet_type'] = 'digi_risk_listing';
						$sheet_infos['sheet_output_type'] = 'export_risk_summary';
						$sheet_infos['document_type'] = 'listing_des_risques';
						$sheet_infos['dateCreation'] = date('Ymd');
						$sheet_infos['recursiv_mode'] = !empty($_POST['recursiv_mode']) ? $_POST['recursiv_mode'] : false;

						$messageInfo = $moremessageInfo = '';
						$sauvegardeFicheDePoste = eva_gestionDoc::save_element_sheet($tableElement, $idElement, $sheet_infos);

						if ($sauvegardeFicheDePoste['result'] != 'error') {
							$messageToOutput = "<img src='" . EVA_MESSAGE_SUCCESS . "' alt='success' class='messageIcone' />" . __('La synth&eacute;se &agrave; bien &eacute;t&eacute; sauvegard&eacute;e.', 'evarisk');
							$moremessageInfo = 'digirisk("#subTabSelector").val("RS");
				digirisk("#ongletHistoriqueDocument").click();';
						}
						else {
							$messageToOutput = "<img src='" . EVA_MESSAGE_ERROR . "' alt='error' class='messageIcone' />" . __('La synth&eacute;se n\'a pas pu &ecirc;tre sauvegard&eacute;e', 'evarisk');
						}

						$table_message_to_update = TABLE_DUER;
						if( $table_element == TABLE_UNITE_TRAVAIL ){
							$table_message_to_update = TABLE_FP;
						}
						$messageInfo = '
			<script type="text/javascript">
				digirisk(document).ready(function(){
					actionMessageShow("#message' . $table_message_to_update . '", "' . $messageToOutput . '");
					setTimeout(\'actionMessageHide("#message' . $table_message_to_update . '")\',5000);
					' . $moremessageInfo . '
				});
			</script>';

						echo $messageInfo;
					break;
					case 'delete_document':
						global $current_user;
						$delete_result = $wpdb->update(TABLE_GED_DOCUMENTS, array('status' => 'deleted', 'dateSuppression' => current_time('mysql', 0), 'idSuppresseur' => $current_user->ID), array('id' => $_REQUEST['idDocument']));

						$action_after_deletion = '';
						if(($delete_result == '1') || ($delete_result == '0')){
							$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' class=\'messageIcone\' alt=\'success\' />' . __('Le document a correctement &eacute;t&eacute; supprim&eacute;', 'evarisk');
							$action_after_deletion = 'jQuery("#associated_document_line_' . $_REQUEST['idDocument'] . '").remove();';

							switch($tableElement){
								case TABLE_ACTIVITE:
								case TABLE_TACHE:
									/*	Log modification on element and notify user if user subscribe	*/
									digirisk_user_notification::log_element_modification($tableElement, $idElement, 'doc_delete', '', $_REQUEST['idDocument']);
								break;
							}
						}
						else {
							$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Une erreur est survenue lors de la suppression du document', 'evarisk');
						}

						echo '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		actionMessageShow(".digi_' . $_REQUEST['tableElement'] . '_associated_document", digi_html_accent_for_js("' . $message . '"));
		setTimeout(\'actionMessageHide(".digi_' . $_REQUEST['tableElement'] . '_associated_document")\',7500);
		' . $action_after_deletion . '
	});
</script>';
					break;
					case 'load_associated_document_list':
						$category = $_REQUEST['category'];
						$document_list = eva_gestionDoc::get_associated_document_list($_REQUEST['tableElement'], $_REQUEST['idElement'], $category, "dateCreation DESC");
						echo $document_list;
					break;

					case 'load_model_combobox':
						$script = '';
						$category = $_REQUEST['category'];
						$selection = (isset($_REQUEST['selection']) && ($_REQUEST['selection'] != '') && ($_REQUEST['selection'] != '0')) ? digirisk_tools::IsValid_Variable($_REQUEST['selection']) : '';
						$documentList = eva_gestionDoc::getDocumentList($tableElement, $idElement, $category, "dateCreation DESC");
						if (count($documentList) > 0) {
							$modelList = evaDisplayInput::afficherComboBox($documentList, 'modelToUse' . $tableElement, '', 'modelToUse' . $tableElement, '', $selection);
							if ( $selection != '' ) {
								$script = '<script type="text/javascript" >digirisk("#modelToUse' . $tableElement . '").val("' . $selection . '")</script>';
							}
						}
						else {
							$modelList = '<span style="color:#FF0000;" >' . __('Aucun mod&eacute;le &agrave; pr&eacute;senter', 'evarisk') . '</span>';
						}
						echo '<div style="margin:12px 24px;" class="bold" ><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="modelToUse" style="width: 15px;" />' . __('Mod&egrave;le &agrave; utiliser', 'evarisk') . '<br/>' . $modelList . $script . '</div>';
					break;

					case 'loadExistingDocument':
						echo EvaDisplayDesign::getExistingModelList($tableElement, $idElement);
					break;
				}
				break;
			case TABLE_PRECONISATION:
				switch($_REQUEST['act'])
				{
					case 'loadRecommandationManagementForm':
					{
						$id = (isset($_REQUEST['id']) && ($_REQUEST['id'] != '') && ($_REQUEST['id'] != '0')) ? digirisk_tools::IsValid_Variable($_REQUEST['id']) : '0';
						if($id <= 0)
						{
							$id_preconisation = $nom_preconisation = $description_preconisation = '';
							$id_categorie_preconisation = (isset($_REQUEST['id_categorie_preconisation']) && ($_REQUEST['id_categorie_preconisation'] != '') && ($_REQUEST['id_categorie_preconisation'] != '0')) ? digirisk_tools::IsValid_Variable($_REQUEST['id_categorie_preconisation']) : '0';
							$moreRecommandationForm = '';
						}
						else
						{
							$recommandationInfos = evaRecommandation::getRecommandation($id);
							$id_categorie_preconisation = $recommandationInfos->id_categorie_preconisation;
							$id_preconisation = $id;
							$nom_preconisation = html_entity_decode($recommandationInfos->nom, ENT_QUOTES, 'UTF-8');
							$description_preconisation = html_entity_decode($recommandationInfos->description, ENT_QUOTES, 'UTF-8');
							$moreRecommandationForm =
		'digirisk("#recommandationPictureGalery").show();
		digirisk("#pictureGallery' . TABLE_PRECONISATION . '_' . $id . '").html(digirisk("#loadingImg").html());
		digirisk("#pictureGallery' . TABLE_PRECONISATION . '_' . $id . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
		{
			"post":"true",
			"table":"' . TABLE_PRECONISATION . '",
			"act":"reloadGallery",
			"idElement":"' . $id . '"
		});';
						}
						echo evaRecommandation::recommandationForm($id_categorie_preconisation, $id_preconisation, $nom_preconisation, $description_preconisation) . '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		digirisk("#loadingRecommandationForm").html("");
		digirisk("#loadingRecommandationForm").hide();
		digirisk("#recommandationFormContainer").show();
' . $moreRecommandationForm . '
	});
</script>';
					}
					break;
					case 'saveRecommandation':{
						$moreRecommandationScript = '';

						$nom_preconisation = (isset($_REQUEST['nom_preconisation']) && ($_REQUEST['nom_preconisation'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['nom_preconisation']) : '';
						$description_preconisation = (isset($_REQUEST['description_preconisation']) && ($_REQUEST['description_preconisation'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['description_preconisation']) : '';
						$preconisation_type = (isset($_REQUEST['preconisation_type']) && ($_REQUEST['preconisation_type'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['preconisation_type']) : '';
						$id_preconisation = (isset($_REQUEST['id_preconisation']) && ($_REQUEST['id_preconisation'] != '') && ($_REQUEST['id_preconisation'] != '0')) ? digirisk_tools::IsValid_Variable($_REQUEST['id_preconisation']) : '0';
						$id_categorie_preconisation = (isset($_REQUEST['id_categorie_preconisation']) && ($_REQUEST['id_categorie_preconisation'] != '') && ($_REQUEST['id_categorie_preconisation'] != '0')) ? digirisk_tools::IsValid_Variable($_REQUEST['id_categorie_preconisation']) : '0';

						$recommandations_informations = array();
						$recommandations_informations['nom'] = $nom_preconisation;
						$recommandations_informations['description'] = $description_preconisation;
						$recommandations_informations['preconisation_type'] = $preconisation_type;

						//Check the value of the recommandation identifier.
						if(($id_preconisation <= 0) && current_user_can('digi_add_recommandation')){	//	If the value is equal or less than 0 we create a new recommandation
							$recommandations_informations['status'] = 'valid';
							$recommandations_informations['id_categorie_preconisation'] = $id_categorie_preconisation;
							$recommandations_informations['creation_date'] = current_time('mysql', 0);
							$recommandationActionResult = evaRecommandation::saveRecommandation($recommandations_informations);
							$moreRecommandationScript .= '
	var expanded = new Array();
	jQuery(".expanded").each(function(){expanded.push(jQuery(this).attr("id"));});
	side_reloader("' . $_REQUEST['table'] . '", "' . $recommandationActionResult . '", "", expanded);
	jQuery("#main_tree_container").load(EVA_AJAX_FILE_URL,{
		"post": "true",
		"act": "reload_config_tree",
		"table": "' . TABLE_CATEGORIE_PRECONISATION . '",
		"idPere": "' . $id_categorie_preconisation . '",
		"elt": "leaf-' . $recommandationActionResult . '",
		"expanded": expanded
	});';
						}
						elseif(($id_preconisation > 0) && current_user_can('digi_edit_recommandation')){	//	If the value is more than 0 we update the corresponding recommandation
							$recommandationActionResult = evaRecommandation::updateRecommandation($recommandations_informations, $id_preconisation);
							$moreRecommandationScript .= '
	jQuery("#leaf-' . $id_preconisation . '-name .leaf_name").html("' . stripslashes($recommandations_informations['nom']) . '");';
						}
						else{
							$recommandationActionResult = 'userNotAllowed';
						}

						if($recommandationActionResult == 'error'){
							$recommandationActionMessage = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Une erreur est survenue lors de l\'enregistrement de la pr&eacute;conisation. Merci de r&eacute;essayer.', 'evarisk');
							$moreRecommandationScript = '';
						}
						elseif($recommandationActionResult == 'userNotAllowed'){
							$recommandationActionMessage = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Vous n\'avez pas les droits n&eacute;cessaire pour effectuer cette action', 'evarisk');
							$moreRecommandationScript = '';
						}
						else{
							$recommandationActionMessage = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' class=\'messageIcone\' alt=\'succes\' />' . __('La pr&eacute;conisation a correctement &eacute;t&eacute; enregistr&eacute;e.', 'evarisk');
						}

						echo '
<script type="text/javascript" >
	actionMessageShow("#message", "' . $recommandationActionMessage . '");
	setTimeout(\'actionMessageHide("#message")\', \'7000\');
' . $moreRecommandationScript . '
</script>';
					}
					break;
					case 'reloadRecommandationList':
					{
						echo evaRecommandation::getRecommandationTable();
					}
					break;
					case 'deleteRecommandation':
					{
						$id = (isset($_REQUEST['id']) && ($_REQUEST['id'] != '') && ($_REQUEST['id'] != '0')) ? digirisk_tools::IsValid_Variable($_REQUEST['id']) : '';
						$recommandations_informations['status'] = 'deleted';
						if(current_user_can('digi_delete_recommandation'))
						{
							$recommandationActionResult = evaRecommandation::updateRecommandation($recommandations_informations, $id);
						}
						else
						{
							$recommandationActionResult = 'userNotAllowed';
						}
						$moreRecommandationScript = '';
						if($recommandationActionResult == 'error')
						{
							$recommandationActionMessage = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Une erreur est survenue lors de la suppression de la pr&eacute;conisation. Merci de r&eacute;essayer.', 'evarisk');
						}
						elseif($recommandationActionResult == 'userNotAllowed')
						{
							$recommandationActionMessage = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Vous n\'avez pas les droits n&eacute;cessaire pour effectuer cette action', 'evarisk');
						}
						else
						{
							$recommandationActionMessage = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' class=\'messageIcone\' alt=\'succes\' />' . __('La pr&eacute;conisation a correctement &eacute;t&eacute; supprim&eacute;e.', 'evarisk');
							$moreRecommandationScript = '
	digirisk("#recommandationTable").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
	{
		"post":"true",
		"table":"' . TABLE_PRECONISATION . '",
		"act":"reloadRecommandationList"
	});';
						}

						echo '
<script type="text/javascript" >
	actionMessageShow("#message", "' . $recommandationActionMessage . '");
	setTimeout(\'actionMessageHide("#message")\', \'7000\');
' . $moreRecommandationScript . '
</script>';
					}
					break;

					case 'deleteRecommandationLink':
					{
						$id = (isset($_REQUEST['id']) && ($_REQUEST['id'] != '') && ($_REQUEST['id'] != '0')) ? digirisk_tools::IsValid_Variable($_REQUEST['id']) : '';
						$table_element = (isset($_REQUEST['table_element']) && ($_REQUEST['table_element'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['table_element']) : '';
						$recommandations_informations['status'] = 'deleted';
						$recommandations_informations['date_update_affectation'] = current_time('mysql', 0);
						$recommandationActionResult = evaRecommandation::updateRecommandationAssociation($recommandations_informations, $id);
						$moreRecommandationScript = '';
						if($recommandationActionResult == 'error')
						{
							$recommandationActionMessage = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Une erreur est survenue lors de la d&eacute;saffectation de la pr&eacute;conisation. Merci de r&eacute;essayer.', 'evarisk');
						}
						else
						{
							$recommandationActionMessage = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' class=\'messageIcone\' alt=\'succes\' />' . __('La pr&eacute;conisation a correctement &eacute;t&eacute; d&eacute;saffect&eacute;e.', 'evarisk');
							$moreRecommandationScript = '
	digirisk("#ongletListePreconisation").click();';
						}

						echo '
<script type="text/javascript" >
	actionMessageShow("#message' . TABLE_PRECONISATION . '-' . $table_element .'", "' . $recommandationActionMessage . '");
	setTimeout(\'actionMessageHide("#message' . TABLE_PRECONISATION . '-' . $table_element .'")\', \'7000\');
' . $moreRecommandationScript . '
</script>';
					}
					break;
					case 'loadRecomandationForElement':
					{
						$id_element = (isset($_REQUEST['id_element']) && ($_REQUEST['id_element'] != '') && ($_REQUEST['id_element'] != '0')) ? digirisk_tools::IsValid_Variable($_REQUEST['id_element']) : '';
						$table_element = (isset($_REQUEST['table_element']) && ($_REQUEST['table_element'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['table_element']) : '';

						echo evaRecommandation::getRecommandationListForElementOutput($table_element, $id_element);
					}
					break;
					case 'loadRecomandationLink':
						$recommandation_link_id = (isset($_REQUEST['recommandation_link_id']) && ($_REQUEST['recommandation_link_id'] != '') && ($_REQUEST['recommandation_link_id'] != '0')) ? digirisk_tools::IsValid_Variable($_REQUEST['recommandation_link_id']) : '0';
						$recommandation_link_action = (isset($_REQUEST['recommandation_link_action']) && ($_REQUEST['recommandation_link_action'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['recommandation_link_action']) : '';
						$outputMode = (isset($_REQUEST['outputMode']) && ($_REQUEST['outputMode'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['outputMode']) : '';
						$table_element = (isset($_REQUEST['table_element']) && ($_REQUEST['table_element'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['table_element']) : '';
						$id_element = (isset($_REQUEST['id_element']) && ($_REQUEST['id_element'] != '') && ($_REQUEST['id_element'] != '0')) ? digirisk_tools::IsValid_Variable($_REQUEST['id_element']) : '0';
						$selectRecommandation = '';

						if ( ( $recommandation_link_action == 'update' ) && ( $recommandation_link_id > 0 ) ) {
							$selectedRecommandation = evaRecommandation::getRecommandationListForElement($table_element, $id_element, $recommandation_link_id);
							$selectRecommandation['id_categorie_preconisation'] = $selectedRecommandation[0]->recommandation_category_id;
							$selectRecommandation['id_preconisation'] = $selectedRecommandation[0]->id_preconisation;
							$selectRecommandation['commentaire_preconisation'] = $selectedRecommandation[0]->commentaire;
							$selectRecommandation['efficacite_preconisation'] = $selectedRecommandation[0]->efficacite;
							$selectRecommandation['preconisation_type'] = $selectedRecommandation[0]->preconisation_type;
						}

						echo evaRecommandation::recommandationAssociation( $outputMode, $selectRecommandation, array( 'tableElement' => $table_element, 'idElement' => $id_element, 'form_container' => TABLE_RISQUE == $table_element ? TABLE_RISQUE . '_' . $id_element . '_reco_container' : 'single_preco') );
					break;
				}
				break;
			case TABLE_CATEGORIE_PRECONISATION:
				switch($_REQUEST['act'])
				{
					case 'delete':
					{
						$id = (isset($_REQUEST['id']) && ($_REQUEST['id'] != '') && ($_REQUEST['id'] != '0')) ? digirisk_tools::IsValid_Variable($_REQUEST['id']) : '';
						$recommandations_informations['status'] = 'deleted';
						if(current_user_can('digi_delete_recommandation_cat'))
						{
							$recommandationActionResult = evaRecommandationCategory::updateRecommandationCategory($recommandations_informations, $id);
						}
						else
						{
							$recommandationActionResult = 'userNotAllowed';
						}
						$moreRecommandationScript = '';
						if($recommandationActionResult == 'error')
						{
							$recommandationActionMessage = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Une erreur est survenue lors de la suppression de la famille de pr&eacute;conisation. Merci de r&eacute;essayer.', 'evarisk');
						}
						elseif($recommandationActionResult == 'userNotAllowed')
						{
							$recommandationActionMessage = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Vous n\'avez pas les droits n&eacute;cessaire pour effectuer cette action', 'evarisk');
						}
						else
						{
							$recommandationActionMessage = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' class=\'messageIcone\' alt=\'succes\' />' . __('La famille de pr&eacute;conisation a correctement &eacute;t&eacute; supprim&eacute;e.', 'evarisk');
							$moreRecommandationScript = '
	jQuery("#main_tree_container").load(EVA_AJAX_FILE_URL,{
		"post": "true",
		"act": "reload_config_tree",
		"table": "' . TABLE_CATEGORIE_PRECONISATION . '",
		"idPere": "1",
		"elt": "",
		"expanded": "",
	});';
						}

						echo '
<script type="text/javascript" >
	actionMessageShow("#message", "' . $recommandationActionMessage . '");
	setTimeout(\'actionMessageHide("#message")\', \'7000\');
' . $moreRecommandationScript . '
</script>';
					}
					break;
					case 'saveRecommandationCategorie': {
						$moreRecommandationCategoryScript = '';

						$nom_categorie = (isset($_REQUEST['nom_categorie']) && ($_REQUEST['nom_categorie'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['nom_categorie']) : '';
						$impressionRecommandationCategorie = (isset($_REQUEST['impressionRecommandationCategorie']) && ($_REQUEST['impressionRecommandationCategorie'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['impressionRecommandationCategorie']) : '';
						$impressionRecommandation = (isset($_REQUEST['impressionRecommandation']) && ($_REQUEST['impressionRecommandation'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['impressionRecommandation']) : '';
						$tailleimpressionRecommandationCategorie = (isset($_REQUEST['tailleimpressionRecommandationCategorie']) && ($_REQUEST['tailleimpressionRecommandationCategorie'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['tailleimpressionRecommandationCategorie']) : '';
						$tailleimpressionRecommandation = (isset($_REQUEST['tailleimpressionRecommandation']) && ($_REQUEST['tailleimpressionRecommandation'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['tailleimpressionRecommandation']) : '';
						$id_categorie_preconisation = (isset($_REQUEST['id_categorie_preconisation']) && ($_REQUEST['id_categorie_preconisation'] != '') && ($_REQUEST['id_categorie_preconisation'] != '0')) ? digirisk_tools::IsValid_Variable($_REQUEST['id_categorie_preconisation']) : '0';

						$recommandationCategory_informations = array();
						$recommandationCategory_informations['nom'] = $nom_categorie;
						$recommandationCategory_informations['impressionRecommandation'] = $impressionRecommandation;
						$recommandationCategory_informations['tailleimpressionRecommandation'] = str_replace(',', '.', $tailleimpressionRecommandation);
						$recommandationCategory_informations['impressionRecommandationCategorie'] = $impressionRecommandationCategorie;
						$recommandationCategory_informations['tailleimpressionRecommandationCategorie'] = str_replace(',', '.', $tailleimpressionRecommandationCategorie);

						//Check the value of the recommandation identifier.
						if(($id_categorie_preconisation <= 0) && current_user_can('digi_edit_recommandation_cat'))
						{	//	If the value is equal or less than 0 we create a new recommandation
							$recommandationCategory_informations['status'] = 'valid';
							$recommandationCategory_informations['creation_date'] = current_time('mysql', 0);
							$recommandationActionResult = evaRecommandationCategory::saveRecommandationCategory($recommandationCategory_informations);
							$moreRecommandationCategoryScript .= '';
						}
						elseif(($id_categorie_preconisation > 0) && current_user_can('digi_edit_recommandation_cat'))
						{	//	If the value is more than 0 we update the corresponding recommandation
							$recommandationActionResult = evaRecommandationCategory::updateRecommandationCategory($recommandationCategory_informations, $id_categorie_preconisation);
						}
						else
						{
							$recommandationActionResult = 'userNotAllowed';
						}

						if($recommandationActionResult == 'error')
						{
							$recommandationCategoryActionMessage = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Une erreur est survenue lors de l\'enregistrement de la famille de pr&eacute;conisation. Merci de r&eacute;essayer.', 'evarisk');
						}
						elseif($recommandationActionResult == 'userNotAllowed')
						{
							$recommandationActionMessage = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Vous n\'avez pas les droits n&eacute;cessaire pour effectuer cette action', 'evarisk');
						}
						else
						{
							$recommandationCategoryActionMessage = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' class=\'messageIcone\' alt=\'succes\' />' . __('La famille de pr&eacute;conisation a correctement &eacute;t&eacute; enregistr&eacute;e.', 'evarisk');
							$moreRecommandationCategoryScript .= '
	jQuery("#main_tree_container").load(EVA_AJAX_FILE_URL,{
		"post": "true",
		"act": "reload_config_tree",
		"table": "' . TABLE_CATEGORIE_PRECONISATION . '",
		"idPere": "1",
		"elt": "",
		"expanded": "",
	});';
						}

						echo '
<script type="text/javascript" >
	actionMessageShow("#message", "' . $recommandationCategoryActionMessage . '");
	setTimeout(\'actionMessageHide("#message")\', \'7000\');
' . $moreRecommandationCategoryScript . '
</script>';
					}
					break;
					case 'loadRecommandationCategoryManagementForm':
					{
						$id = (isset($_REQUEST['id']) && ($_REQUEST['id'] != '') && ($_REQUEST['id'] != '0')) ? digirisk_tools::IsValid_Variable($_REQUEST['id']) : '0';
						if($id <= 0)
						{
							$id_preconisation = $nom_preconisation = $description_preconisation = '';
							$id_categorie_preconisation = (isset($_REQUEST['id_categorie_preconisation']) && ($_REQUEST['id_categorie_preconisation'] != '') && ($_REQUEST['id_categorie_preconisation'] != '0')) ? digirisk_tools::IsValid_Variable($_REQUEST['id_categorie_preconisation']) : '0';
							$moreRecommandationForm = '';
						}
						else
						{
							$recommandationCategoryInfos = evaRecommandationCategory::getCategoryRecommandation($id);
							$id_categorie_preconisation = $id;
							$moreRecommandationForm =
		'digirisk("#recommandationCategoryPictureGalery").show();
		digirisk("#pictureGallery' . TABLE_CATEGORIE_PRECONISATION . '_' . $id . '").html(digirisk("#loadingImg").html());
		digirisk("#pictureGallery' . TABLE_CATEGORIE_PRECONISATION . '_' . $id . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
		{
			"post":"true",
			"table":"' . TABLE_CATEGORIE_PRECONISATION . '",
			"act":"reloadGallery",
			"idElement":"' . $id . '"
		});';
						}
						echo evaRecommandationCategory::recommandationCategoryForm($id_categorie_preconisation, $recommandationCategoryInfos) . '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		digirisk("#loadingCategoryRecommandationForm").html("");
		digirisk("#loadingCategoryRecommandationForm").hide();
		digirisk("#recommandationCategoryFormContainer").show();
' . $moreRecommandationForm . '
	});
</script>';
					}
					break;
				}
				break;
			case DIGI_DBT_LIAISON_USER_GROUP:
				switch($_REQUEST['act'])
				{
					case 'save':
						digirisk_groups::setLinkGroupElement($_REQUEST['tableElement'], $_REQUEST['idElement'], $_REQUEST['element']);
					break;
				}
				break;
			case DIGI_DBT_LIAISON_PRODUIT_ELEMENT:
				switch($_REQUEST['act'])
				{
					case 'save':
						digirisk_product::setLinkProductElement($_REQUEST['tableElement'], $_REQUEST['idElement'], $_REQUEST['element']);
					break;
					case 'reloadCategoryChoice':
						echo digirisk_product::affectationPostBoxContent($_REQUEST['tableElement'], $_REQUEST['idElement'], true, $_REQUEST['category']);
					break;
				}
				break;
			case DIGI_DBT_PERMISSION:
				switch($_REQUEST['act'])
				{
					case 'save':
					{
						$actionResponse = '';
						$tableElement = (isset($_REQUEST['tableElement']) && ($_REQUEST['tableElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['tableElement']) : '';
						$idElement = (isset($_REQUEST['idElement']) && ($_REQUEST['idElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['idElement']) : '';

						$rightType = array('user_see', 'user_edit', 'user_delete', 'user_add_gpt', 'user_add_unit', 'user_add_task', 'user_add_action');

						$oldAssignedRight = null;
						$newAssignedRight = null;

						/*	Read the recursiv content to set recursiv right for the selected user	*/
						$recursivRight = '';
						$recursivRightUser = (isset($_REQUEST['user_recursif']) && ($_REQUEST['user_recursif'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['user_recursif']) : '';
						if($recursivRightUser != '')
						{
							$recursivUsers = explode('#!#', $recursivRightUser);
							foreach($recursivUsers as $recursivUser)
							{
								$userRecursiv = explode('!#!', $recursivUser);
								if(isset($userRecursiv[1]) && ($userRecursiv[1] != ''))
								{
									$recursivRight[] = $userRecursiv[1];
								}
							}
						}

						/*	Read the different right for user attribution	*/
						foreach($rightType as $rightName)
						{
							$right = (isset($_REQUEST[$rightName]) && ($_REQUEST[$rightName] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST[$rightName]) : '';
							$oldRight = (isset($_REQUEST[$rightName . '_old']) && ($_REQUEST[$rightName . '_old'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST[$rightName . '_old']) : '';

							if($oldRight != '')
							{
								$userElements = explode('#!#', $oldRight);
								foreach($userElements as $userElement)
								{
									$userRight = explode('!#!', $userElement);
									if(isset($userRight[1]) && ($userRight[1] != ''))
									{
										$oldAssignedRight[$userRight[0]][] = $userRight[1];
									}
								}
							}

							if($right != '')
							{
								$userElements = explode('#!#', $right);
								foreach($userElements as $userElement)
								{
									$userRight = explode('!#!', $userElement);
									if(isset($userRight[1]) && ($userRight[1] != ''))
									{
										$newAssignedRight[$userRight[0]][] = $userRight[1];
										$user = new WP_User($userRight[1]);
										if(!$user->has_cap($userRight[0]))
										{
											$user->add_cap($userRight[0]);
										}

										switch($tableElement)
										{
											case TABLE_GROUPEMENT:
												if(is_array($recursivRight) && in_array($userRight[1], $recursivRight))
												{
													digirisk_permission::addRecursivRight($tableElement, $idElement, $user, str_replace('groupement_' . $idElement, '', $userRight[0]), 'add');
												}
											break;
										}
									}
								}
							}

							$actionResponse .= 'digirisk("#' . $rightName . '_old' . '").val("' . $right . '");
	';

							if ( !empty($oldAssignedRight) ) {
								foreach($oldAssignedRight as $permissionName => $permissionUser)
								{
									foreach($permissionUser as $userId)
									{
										if((!empty($newAssignedRight) && !empty($newAssignedRight[$permissionName]) && (!in_array($userId, $newAssignedRight[$permissionName]))) || empty($newAssignedRight))
										{
											$user = new WP_User($userId);
											if($user->has_cap($permissionName))
											{
												$user->remove_cap($permissionName);
											}

											switch($tableElement)
											{
												case TABLE_GROUPEMENT:
													if(is_array($recursivRight) && in_array($userId, $recursivRight))
													{
														digirisk_permission::addRecursivRight($tableElement, $idElement, $user, str_replace('groupement_' . $idElement, '', $permissionName), 'remove');
													}
												break;
											}
										}
									}
								}

								unset($oldAssignedRight);
								unset($newAssignedRight);
							}
						}

						$message = '<img src=\'' . EVA_MESSAGE_SUCCESS . '\' class=\'messageIcone\' />' . __('Les droits ont bien &eacute;t&eacute; mis &agrave; jour', 'evarisk');

						echo '
<script type="text/javascript" >
	' . $actionResponse . '
	digirisk("#saveButtonLoading_userRight' . $tableElement . '").hide();
	digirisk("#saveButtonContainer_userRight' . $tableElement . '").show();

	actionMessageShow("#' . $_REQUEST['message'] . '", "' . $message . '");
	setTimeout(\'actionMessageHide("#' . $_REQUEST['message'] . '")\',7500);

	digirisk("#' . $_REQUEST['tableContainer'] . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
		"post": "true",
		"table": "' . DIGI_DBT_PERMISSION . '",
		"act": "reload_user_right_box",
		"tableElement": "' . $tableElement . '",
		"idElement": "' . $idElement . '"
	});
</script>';
					}
					break;
					case 'reload_user_right_box':
						echo digirisk_permission::generateUserListForRightDatatable($_REQUEST['tableElement'], $_REQUEST['idElement']);
					break;
				}
				break;
			case DIGI_DBT_ACCIDENT:
				switch($_REQUEST['act'])
				{
					case 'save-accident':
					{
						$save_result = '';
						$current_accident = null;
						$tableElement = (isset($_REQUEST['tableElement']) && ($_REQUEST['tableElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['tableElement']) : '';
						$idElement = (isset($_REQUEST['idElement']) && ($_REQUEST['idElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['idElement']) : '';
						$accident_id = (isset($_REQUEST['accident_id']) && ($_REQUEST['accident_id'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['accident_id']) : 0;
						$accident_form_step = (isset($_REQUEST['accident_form_step']) && ($_REQUEST['accident_form_step'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['accident_form_step']) : 1;

						/*	Create the accident if not existing into database	*/
						if($accident_id <= 0){
							$accident_main_informations['status'] = 'valid';
							$accident_main_informations['creation_date'] = current_time('mysql', 0);
							$accident_main_informations['id_element'] = $idElement;
							$accident_main_informations['table_element'] = $tableElement;
							$accident_main_informations['declaration_state'] = 'in_progress';
							$accident_main_informations['declaration_step'] = 1;
							$save_result = eva_database::save($accident_main_informations, DIGI_DBT_ACCIDENT);
							$accident_id = $wpdb->insert_id;
						}
						else{
							$current_accident = digirisk_accident::getElement($accident_id);
						}

						/*	Define the message by checking the action result	*/
						if($save_result == 'error'){
							$message = '<img src=\'' . EVA_MESSAGE_ERROR . '\' alt=\'' . $save_result . '\' class=\'messageIcone\' />' . __('Une erreur est survenue lors de l\'enregistrement de l\'accident de travail', 'evarisk');
						}
						elseif(in_array($save_result, array('done', 'nothingToUpdate'))){/*	If creation	*/
							$message = '<img src=\'' . EVA_MESSAGE_SUCCESS . '\' alt=\'' . $save_result . '\' class=\'messageIcone\' />' . __('L\'accident de travail a bien &eacute;t&eacute; sauvegard&eacute;', 'evarisk');
						}

						if($accident_form_step >= 1){/*	Save first step => Employer and establishment	*/
							/*	Save employer informations	*/
							$location['employer']['status'] = 'valid';
							$location['employer']['id_accident'] = $accident_id;
							$location['employer']['id_location'] = $_POST['employer']['id'];
							$location['employer']['location_type'] = 'employer';
							$location['employer']['telephone'] = $_POST['employer']['telephone'];
							$location['employer']['name'] = $_POST['employer']['name'];
							$location['employer']['adress_line_1'] = $_POST['employer']['address_1'];
							$location['employer']['adress_line_2'] = $_POST['employer']['address_2'];
							$location['employer']['adress_postal_code'] = $_POST['employer']['postal_code'];
							$location['employer']['adress_city'] = $_POST['employer']['city'];
							if(($current_accident != null) && ($current_accident->declaration_state == 'in_progress') && ($current_accident->employer_location_id > 0)){
								$location['employer']['last_update_date'] = current_time('mysql', 0);
								$save_result = eva_database::update($location['employer'], $current_accident->employer_location_id, DIGI_DBT_ACCIDENT_LOCATION);
							}
							else{
								if(($current_accident != null) && ($current_accident->declaration_state == 'done')){
									$location_employer['last_update_date'] = current_time('mysql', 0);
									$location_employer['status'] = 'moderated';
									$save_result = eva_database::update($location_employer, $current_accident->employer_location_id, DIGI_DBT_ACCIDENT_LOCATION);
								}
								$location['employer']['creation_date'] = current_time('mysql', 0);
								$save_result = eva_database::save($location['employer'], DIGI_DBT_ACCIDENT_LOCATION);
							}

							/*	Save establishment informations	*/
							$location['establishment']['status'] = 'valid';
							$location['establishment']['id_accident'] = $accident_id;
							$location['establishment']['id_location'] = $_POST['establishment']['id'];
							$location['establishment']['location_type'] = 'establishment';
							$location['establishment']['telephone'] = $_POST['establishment']['telephone'];
							$location['establishment']['name'] = $_POST['establishment']['name'];
							$location['establishment']['siret'] = $_POST['establishment']['siret'];
							$location['establishment']['social_activity_number'] = $_POST['establishment']['social_activity_number'];
							$location['establishment']['adress_line_1'] = $_POST['establishment']['address_1'];
							$location['establishment']['adress_line_2'] = $_POST['establishment']['address_2'];
							$location['establishment']['adress_postal_code'] = $_POST['establishment']['postal_code'];
							$location['establishment']['adress_city'] = $_POST['establishment']['city'];
							if(($current_accident != null) && ($current_accident->declaration_state == 'in_progress') && ($current_accident->establishment_location_id > 0)){
								$location['establishment']['last_update_date'] = current_time('mysql', 0);
								$save_result = eva_database::update($location['establishment'], $current_accident->establishment_location_id, DIGI_DBT_ACCIDENT_LOCATION);
							}
							else{
								if(($current_accident != null) && ($current_accident->declaration_state == 'done')){
									$location_establishment['last_update_date'] = current_time('mysql', 0);
									$location_establishment['status'] = 'moderated';
									$save_result = eva_database::update($location_establishment, $current_accident->establishment_location_id, DIGI_DBT_ACCIDENT_LOCATION);
								}
								$location['establishment']['creation_date'] = current_time('mysql', 0);
								$save_result = eva_database::save($location['establishment'], DIGI_DBT_ACCIDENT_LOCATION);
							}

							unset($location);

							/*	Update accident form step	*/
							if($save_result != 'error'){
								$accident_main_informations['declaration_step'] = 2;
								$accident_main_informations['declaration_state'] = 'in_progress';
							}
						}
						if($accident_form_step >= 2){/*	Save second step => Victim	*/
							$victim['status'] = 'valid';
							$victim['id_accident'] = $accident_id;
							$victim['id_user'] = $_POST['accident_user']['victim_id'];
							$victim['victim_seniority'] = $_POST['accident_user']['accident_user_seniority'];
							$accident_main_informations['accident_make_other_victim'] = $_POST['accident_user']['accident_make_other_victims'];
							$user_meta = get_user_meta($_POST['accident_user']['victim_id'], 'digirisk_information', false);
							$victim['victim_meta'] = serialize($user_meta[0]);
							if(($current_accident != null) && ($current_accident->declaration_state == 'in_progress') && ($current_accident->accident_victim_id > 0)){
								$victim['last_update_date'] = current_time('mysql', 0);
								$save_result = eva_database::update($victim, $current_accident->accident_victim_id, DIGI_DBT_ACCIDENT_VICTIM);
							}
							else{
								if(($current_accident != null) && ($current_accident->declaration_state == 'done')){
									$current_victim['last_update_date'] = current_time('mysql', 0);
									$current_victim['status'] = 'moderated';
									$save_result = eva_database::update($current_victim, $current_accident->accident_victim_id, DIGI_DBT_ACCIDENT_VICTIM);
								}
								$victim['creation_date'] = current_time('mysql', 0);
								$save_result = eva_database::save($victim, DIGI_DBT_ACCIDENT_VICTIM);
							}

							unset($victim);

							/*	Update accident form step	*/
							if($save_result != 'error'){
								$accident_main_informations['declaration_step'] = 3;
								$accident_main_informations['declaration_state'] = 'in_progress';
							}
						}
						if($accident_form_step >= 3){/*	Save third step => accident	*/
							$accident['status'] = 'valid';
							$accident['id_accident'] = $accident_id;
							$accident['accident_victim_transported_at'] = $_POST['accident']['accident_victim_transported_at'];
							$accident['accident_place'] = $_POST['accident']['accident_place'];
							$accident['accident_consequence'] = $_POST['accident']['accident_consequence'];
							$accident['accident_victim_work_shedule'] = serialize(array('from1' => sprintf('%02d', $_POST['accident']['accident_victim_work_shedule']['accident_from_hour_1']) . ':' . sprintf('%02d', $_POST['accident']['accident_victim_work_shedule']['accident_from_minute_1']), 'to1' => sprintf('%02d', $_POST['accident']['accident_victim_work_shedule']['accident_to_hour_1']) . ':' . sprintf('%02d', $_POST['accident']['accident_victim_work_shedule']['accident_to_minute_1']), 'from2' => sprintf('%02d', $_POST['accident']['accident_victim_work_shedule']['accident_from_hour_2']) . ':' . sprintf('%02d', $_POST['accident']['accident_victim_work_shedule']['accident_from_minute_2']), 'to2' => sprintf('%02d', $_POST['accident']['accident_victim_work_shedule']['accident_to_hour_2']) . ':' . sprintf('%02d', $_POST['accident']['accident_victim_work_shedule']['accident_to_minute_2'])));
							$accident['accident_declaration'] = serialize($_POST['accident']['accident_declaration']);
							$accident['accident_details'] = $_POST['accident']['accident_details'];
							$accident['accident_hurt_place'] = $_POST['accident']['accident_hurt_place'];
							$accident['accident_hurt_nature'] = $_POST['accident']['accident_hurt_nature'];

							$accident_time = explode(" ", $_POST['accident']['accident_date']);
							$accident['accident_date'] = $accident_time[0];
							$accident['accident_hour'] = $accident_time[1];

							$accident_main_informations['accident_date'] = $accident['accident_date'];
							$accident_main_informations['accident_hour'] = $accident['accident_hour'];
							$accident_main_informations['accident_title'] = $_POST['accident']['accident_title'];

							if(($current_accident != null) && ($current_accident->declaration_state == 'in_progress') && ($current_accident->accident_details_id > 0)){
								$accident['last_update_date'] = current_time('mysql', 0);
								$save_result = eva_database::update($accident, $current_accident->accident_details_id, DIGI_DBT_ACCIDENT_DETAILS);
							}
							else{
								if(($current_accident != null) && ($current_accident->declaration_state == 'done')){
									$current_accident_details['last_update_date'] = current_time('mysql', 0);
									$current_accident_details['status'] = 'moderated';
									$save_result = eva_database::update($current_accident_details, $current_accident->accident_details_id, DIGI_DBT_ACCIDENT_DETAILS);
								}
								$accident['creation_date'] = current_time('mysql', 0);
								$save_result = eva_database::save($accident, DIGI_DBT_ACCIDENT_DETAILS);
							}

							unset($accident);

							/*	Update accident form step	*/
							if($save_result != 'error'){
								$accident_main_informations['declaration_step'] = 4;
								$accident_main_informations['declaration_state'] = 'in_progress';
							}
						}
						if($accident_form_step >= 4){/*	Save four step => witnesses	*/
							if(isset($_POST['accident_witness']) && is_array($_POST['accident_witness'])){
								foreach($_POST['accident_witness'] as $witness_index => $witness_infos){
									$user_meta = get_user_meta($witness_infos['user_id'], 'digirisk_information', false);
									$user_main_info = evaUser::getUserInformation($witness_infos['user_id']);
									$accident_witness['status'] = 'valid';
									$accident_witness['third_party_type'] = 'witness';
									$accident_witness['id_user'] = $witness_infos['user_id'];
									$accident_witness['id_accident'] = $accident_id;
									$accident_witness['firstname'] = $user_main_info[$witness_infos['user_id']]['user_firstname'];
									$accident_witness['lastname'] = $user_main_info[$witness_infos['user_id']]['user_lastname'];
									$accident_witness['adress_line_1'] = $user_meta[0]['user_adress'];
									$accident_witness['adress_line_2'] = $user_meta[0]['user_adress_2'];

									if($witness_infos['user_id'] > 0){
										if(($current_accident != null) && ($current_accident->declaration_state == 'in_progress')
												&& (isset($witness_infos['tparty_id']) && ($witness_infos['tparty_id'] > 0)) ){
											$accident_witness['last_update_date'] = current_time('mysql', 0);
											$save_hurt_result = eva_database::update($accident_witness, $witness_infos['tparty_id'], DIGI_DBT_ACCIDENT_THIRD_PARTY);
										}
										else{
											if(($current_accident != null) && ($current_accident->declaration_state == 'done')){
												$current_accident_witness['last_update_date'] = current_time('mysql', 0);
												$current_accident_witness['status'] = 'moderated';
												$save_result = eva_database::update($current_accident_witness, $witness_infos['tparty_id'], DIGI_DBT_ACCIDENT_THIRD_PARTY);
											}
											$accident_witness['creation_date'] = current_time('mysql', 0);
											$save_hurt_result = eva_database::save($accident_witness, DIGI_DBT_ACCIDENT_THIRD_PARTY);
										}
									}
								}
							}
							$accident_main_informations['police_report'] = $_POST['accident_police_report'];
							$accident_main_informations['police_report_writer'] = $_POST['accident_police_report_writer'];

							unset($accident_witness);

							/*	Update accident form step	*/
							if($save_result != 'error'){
								$accident_main_informations['declaration_step'] = 5;
								$accident_main_informations['declaration_state'] = 'in_progress';
							}
						}
						if($accident_form_step >= 5){/*	Save four step => third party	*/
							if(isset($_POST['accident_third_party']) && is_array($_POST['accident_third_party'])){
								foreach($_POST['accident_third_party'] as $third_party_index => $third_party_infos){
									$user_meta = get_user_meta($third_party_infos['user_id'], 'digirisk_information', false);
									$user_main_info = evaUser::getUserInformation($third_party_infos['user_id']);
									$accident_third_party['status'] = 'valid';
									$accident_third_party['third_party_type'] = 'third_party';
									$accident_third_party['id_user'] = $third_party_infos['user_id'];
									$accident_third_party['id_accident'] = $accident_id;
									$accident_third_party['firstname'] = $user_main_info[$third_party_infos['user_id']]['user_firstname'];
									$accident_third_party['lastname'] = $user_main_info[$third_party_infos['user_id']]['user_lastname'];
									$accident_third_party['adress_line_1'] = $user_meta[0]['user_adress'];
									$accident_third_party['adress_line_2'] = $user_meta[0]['user_adress_2'];

									if($third_party_infos['user_id'] > 0){
										if(($current_accident != null) && ($current_accident->declaration_state == 'in_progress')
												&& (isset($third_party_infos['tparty_id']) && ($third_party_infos['tparty_id'] > 0)) ){
											$accident_third_party['last_update_date'] = current_time('mysql', 0);
											$save_hurt_result = eva_database::update($accident_third_party, $third_party_infos['tparty_id'], DIGI_DBT_ACCIDENT_THIRD_PARTY);
										}
										else{
											if(($current_accident != null) && ($current_accident->declaration_state == 'done')){
												$current_accident_third_party['last_update_date'] = current_time('mysql', 0);
												$current_accident_third_party['status'] = 'moderated';
												$save_result = eva_database::update($current_accident_third_party, $third_party_infos['tparty_id'], DIGI_DBT_ACCIDENT_THIRD_PARTY);
											}
											$accident_witness['creation_date'] = current_time('mysql', 0);
											$save_hurt_result = eva_database::save($accident_third_party, DIGI_DBT_ACCIDENT_THIRD_PARTY);
										}
									}
								}
							}
							$accident_main_informations['accident_caused_by_third_party'] = $_POST['accident_caused_by_third_party'];

							unset($accident_third_party);

							/*	Update accident form step	*/
							if($save_result != 'error'){
								$accident_main_informations['declaration_step'] = 5;
								$accident_main_informations['declaration_state'] = 'done';
							}
						}

						$accident_main_informations['last_update_date'] = current_time('mysql', 0);
						$save_result = eva_database::update($accident_main_informations, $accident_id, DIGI_DBT_ACCIDENT);
						if(($save_result == 'done') || ($save_result == 'nothingToUpdate')){
							$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' alt=\'response\' style=\'vertical-align:middle;\' />&nbsp;' . __('L\'accident a &eacute;t&eacute; correctement mis &agrave; jour', 'evarisk');
						}
						elseif($save_result == 'error'){
							$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' alt=\'response\' style=\'vertical-align:middle;\' />&nbsp;' . __('Une erreur est survenue lors de l\'enregistrement de l\'accident', 'evarisk');
						}

						echo '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		actionMessageShow("#message_accident", "' . $message . '");
		setTimeout(\'actionMessageHide("#message_accident")\',7500);
		jQuery("#divAccidenContainer").html(jQuery("#loadingImg").html());
		jQuery("#divAccidenContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
			"post":"true",
			"table":"' . DIGI_DBT_ACCIDENT . '",
			"act":"load",
			"accident_id": "' . $accident_id . '",
			"idElement":"' . $idElement . '",
			"tableElement":"' . $tableElement . '"
		});
	});
</script>';
					}
					break;

					case 'reloadVoirAccident':
					{
						$tableElement = (isset($_REQUEST['tableElement']) && ($_REQUEST['tableElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['tableElement']) : '';
						$idElement = (isset($_REQUEST['idElement']) && ($_REQUEST['idElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['idElement']) : '';
						echo digirisk_accident::get_accident_list($tableElement, $idElement);
					}
					break;

					case 'addAccident':
					{
						$tableElement = (isset($_REQUEST['tableElement']) && ($_REQUEST['tableElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['tableElement']) : '';
						$idElement = (isset($_REQUEST['idElement']) && ($_REQUEST['idElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['idElement']) : '';
						echo digirisk_accident::get_accident_form($tableElement, $idElement);
					}
					break;

					case 'load':
					{
						$tableElement = (isset($_REQUEST['tableElement']) && ($_REQUEST['tableElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['tableElement']) : '';
						$idElement = (isset($_REQUEST['idElement']) && ($_REQUEST['idElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['idElement']) : '';
						$accident_id = (isset($_REQUEST['accident_id']) && ($_REQUEST['accident_id'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['accident_id']) : '';
						echo digirisk_accident::get_accident_form($tableElement, $idElement, $accident_id);
					}
					break;

					case 'delete_accident':
					{
						$tableElement = (isset($_REQUEST['tableElement']) && ($_REQUEST['tableElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['tableElement']) : '';
						$idElement = (isset($_REQUEST['idElement']) && ($_REQUEST['idElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['idElement']) : '';
						$accident_id = (isset($_REQUEST['accident_id']) && ($_REQUEST['accident_id'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['accident_id']) : '';

						$accident_main_informations['status'] = 'deleted';
						$accident_main_informations['last_update_date'] = current_time('mysql', 0);
						$save_result = eva_database::update($accident_main_informations, $accident_id, DIGI_DBT_ACCIDENT);
						if(($save_result == 'done') || ($save_result == 'nothingToUpdate')){
							$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' alt=\'response\' style=\'vertical-align:middle;\' />&nbsp;' . __('L\'accident a &eacute;t&eacute; correctement supprim&eacute;', 'evarisk');
						}
						elseif($save_result == 'error'){
							$message = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' alt=\'response\' style=\'vertical-align:middle;\' />&nbsp;' . __('Une erreur est survenue lors de la suppression de l\'accident', 'evarisk');
						}

						echo '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		actionMessageShow("#message_accident", "' . $message . '");
		setTimeout(\'actionMessageHide("#message_accident")\',7500);
		jQuery("#divAccidenContainer").html(jQuery("#loadingImg").html());
		jQuery("#divAccidenContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
			"post":"true",
			"table":"' . DIGI_DBT_ACCIDENT . '",
			"act":"reloadVoirAccident",
			"idElement":"' . $idElement . '",
			"tableElement":"' . $tableElement . '"
		});
	});
</script>';
					}
					break;

					case 'reload_accident_place_part':
					{
						$tableElement = (isset($_REQUEST['tableElement']) && ($_REQUEST['tableElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['tableElement']) : '';
						$idElement = (isset($_REQUEST['idElement']) && ($_REQUEST['idElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['idElement']) : '';
						$part = (isset($_REQUEST['part']) && ($_REQUEST['part'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['part']) : '';
						$outputPart = digirisk_accident::get_accident_form_part($part, $tableElement, $idElement);
						echo $outputPart['part'] . '
						<script type="text/javascript" >
							digirisk(document).ready(function(){
								jQuery("#accident_form_error_nb").val(parseInt(jQuery("#accident_form_error_nb").val()) + parseInt(' . $outputPart['error'] . '));
								jQuery(".edit_missing_information").click(function(){
									jQuery("#accident_form_error_nb").val(0);
									jQuery("#accident_element_updater").dialog("open");
									jQuery("#accident_element_updater").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
										"post": "true",
										"table": "' . TABLE_GROUPEMENT . '",
										"act": "load_groupement_form",
										"tableElement": "' . TABLE_GROUPEMENT . '",
										"idElement": jQuery(this).attr("id").replace("element_", "")
									});
								});
								if(jQuery("#accident_form_error_nb").val() > 0){
									jQuery("#save_accident").attr("disabled", "disabled");
								}
								else{
									jQuery("#save_accident").attr("disabled", false);
								}
							});
						</script>';
					}
					break;

					case 'previous_step':
					{
						$tableElement = (isset($_REQUEST['tableElement']) && ($_REQUEST['tableElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['tableElement']) : '';
						$idElement = (isset($_REQUEST['idElement']) && ($_REQUEST['idElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['idElement']) : '';
						$accident_id = (isset($_REQUEST['accident_id']) && ($_REQUEST['accident_id'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['accident_id']) : '';
						$step_to_load = (isset($_REQUEST['step_to_load']) && ($_REQUEST['step_to_load'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['step_to_load']) : '';
						$accident_main_informations['last_update_date'] = current_time('mysql', 0);
						$accident_main_informations['declaration_step'] = $step_to_load;
						$accident_main_informations['declaration_state'] = ($step_to_load < 5) ? 'in_progress' : 'done';
						$save_result = eva_database::update($accident_main_informations, $accident_id, DIGI_DBT_ACCIDENT);
						echo '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#divAccidenContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
			"post":"true",
			"table":"' . DIGI_DBT_ACCIDENT . '",
			"act":"load",
			"accident_id": "' . $accident_id . '",
			"idElement":"' . $idElement . '",
			"tableElement":"' . $tableElement . '"
		});
	});
</script>';
					}
					break;
				}
			break;
			case DIGI_DBT_ELEMENT_NOTIFICATION:
				switch($_REQUEST['act']){
					case 'save_user_notification':{
						global $current_user;
						$message = $message_output = '';
						$tableElement = (isset($_REQUEST['tableElement']) && ($_REQUEST['tableElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['tableElement']) : '';
						$idElement = (isset($_REQUEST['idElement']) && ($_REQUEST['idElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['idElement']) : '';
						$notification_to_delete = (isset($_REQUEST['notification_to_delete']) && ($_REQUEST['notification_to_delete'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['notification_to_delete']) : '';

						if(!empty($_REQUEST['user_notification_insert'])){
							$action_nb=$done_link=0;
							foreach($_REQUEST['user_notification_insert'] as $action => $user_list){
								$list = '  ';
								foreach($user_list as $user_id => $action_id){
									$action_nb++;
									$done_link += $wpdb->insert(DIGI_DBT_LIAISON_USER_NOTIFICATION_ELEMENT, array('status' => 'valid', 'date_affectation' => current_time('mysql', 0), 'id_attributeur' => $current_user->ID, 'id_user' => $user_id, 'id_notification' => $action_id, 'id_element' => $idElement,	'table_element' => $tableElement));
								}
							}
							if($action_nb == $done_link){
								$message .= addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" class="messageIcone" />&nbsp;') . __('Les notifications ont bien &eacute;t&eacute; enregistr&eacute;es', 'evarisk');
							}
							else{
								$message .= addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" class="messageIcone" />&nbsp;') . sprintf(__('Une erreur est survenue lors de l\'enregistrement des notifications seules %d des notifications demand&eacute;es ont &eacute;t&eacute; enregistr&eacute;es sur %d', 'evarisk'), $done_link, $action_nb);
							}
						}
						if($notification_to_delete != ''){
							$message .= '<br/>';
							$notification_to_delete_list = explode('-', $notification_to_delete);
							$done_deletion = $deletion_to_do = 0;
							foreach($notification_to_delete_list as $link){
								if($link != ''){
									$deletion_to_do++;
									$element_of_link = explode('_', $link);
									$done_deletion += $wpdb->update(DIGI_DBT_LIAISON_USER_NOTIFICATION_ELEMENT, array('status' => 'deleted', 'date_desAffectation' => current_time('mysql', 0), 'id_desAttributeur' => $current_user->ID),array('id_notification' => $element_of_link[0], 'id_user' => $element_of_link[1], 'status' => 'valid'));
								}
							}
							if($deletion_to_do == $done_deletion){
								$message .= addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" class="messageIcone" />&nbsp;') . __('Les notifications ont bien &eacute;t&eacute; supprim&eacute;es', 'evarisk');
							}
							else{
								$message .= addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" class="messageIcone" />&nbsp;') . sprintf(__('Une erreur est survenue lors de la suppression des notifications seules %d des demandes de suppression ont &eacute;t&eacute; enregistr&eacute;es sur %d', 'evarisk'), $done_deletion, $deletion_to_do);
							}
						}

						if($message != ''){
							$message_output = '
		actionMessageShow("#digi_link_notification_user_message", "' . $message . '");
		setTimeout(function(){actionMessageHide("#digi_link_notification_user_message");}, "7000");';
						}

						echo '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#saveButtonLoading_userNotification' . $tableElement . '").hide();
		jQuery("#saveButtonContainer_userNotification' . $tableElement . '").show();
		jQuery("#check_all").prop("checked", false);
		jQuery(".check_all_action_column").prop("checked", false);
		jQuery(".check_all_user_line").prop("checked", false);
		jQuery("#toDelete").val("");' . $message_output . '
	});
</script>';
					}
					break;
					case 'reload_user_notification_box':{
						$tableElement = (isset($_REQUEST['tableElement']) && ($_REQUEST['tableElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['tableElement']) : '';
						$idElement = (isset($_REQUEST['idElement']) && ($_REQUEST['idElement'] != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['idElement']) : '';
						echo digirisk_user_notification::get_user_notification_table($tableElement, $idElement);
					}
					break;
				}
			break;
		}
	}

	if(isset($_REQUEST['nom'])){
		switch($_REQUEST['nom']){
			case "digirisk_install":{
				$install_date = current_time('mysql', 0);
				$max_number = 0;

				digirisk_permission::digirisk_init_permission();

				/*	Create the complete database	*/
				foreach($digirisk_db_table as $table_name => $table_structure){
					dbDelta($table_structure);
				}
				foreach($digirisk_update_way as $number => $operation){
					digirisk_install::insert_data_for_version($number);
					if($number > $max_number){
						$max_number = $number;
					}
				}

				/*	Method component management	*/
				if(isset($_REQUEST['insert_basic_operator']) && ($_REQUEST['insert_basic_operator'] == 'yes')){
					eva_Operateur::create_basic_operator();
				}
				if(isset($_REQUEST['insert_basic_vars']) && ($_REQUEST['insert_basic_vars'] == 'yes')){
					eva_Variable::create_basic_variable();
				}

				/*	Danger categories and danger management	*/
				if(isset($_REQUEST['insert_inrs_danger_cat']) && ($_REQUEST['insert_inrs_danger_cat'] == 'yes')){
					$left_limit = 1;
					$right_limit = 2;
					foreach($inrs_danger_categories as $danger_cat){
						$new_danger_cat_id = categorieDangers::saveNewCategorie($danger_cat['nom']);

						/*	Add a danger in the current category	*/
						$wpdb->insert(TABLE_DANGER, array('nom' => __('Divers', 'evarisk') . ' ' . strtolower($danger_cat['nom']), 'id_categorie' => $new_danger_cat_id));
						if(!empty($danger_cat['risks']) && is_array($danger_cat['risks'])){
							foreach($danger_cat['risks'] as $risk_to_create){
								$wpdb->insert(TABLE_DANGER, array('nom' => $risk_to_create, 'id_categorie' => $new_danger_cat_id));
							}
						}

						/*	Insert picture for danger categories	*/
						$new_cat_pict_id = EvaPhoto::saveNewPicture(TABLE_CATEGORIE_DANGER, $new_danger_cat_id, $danger_cat['picture']);
						EvaPhoto::setMainPhoto(TABLE_CATEGORIE_DANGER, $new_danger_cat_id, $new_cat_pict_id, 'yes');
						$left_limit = $right_limit + 1;
						$right_limit = $left_limit + 1;
					}
					$wpdb->update(TABLE_CATEGORIE_DANGER, array('limiteDroite' => $left_limit), array('nom' => __('Categorie Racine', 'evarisk')));
				}

				/*	Evarisk default method management	*/
				if(isset($_REQUEST['insert_evarisk_main_method']) && ($_REQUEST['insert_evarisk_main_method'] == 'yes')){
					if(!isset($_REQUEST['insert_basic_operator'])){
						eva_Operateur::create_basic_operator();
					}
					if(!isset($_REQUEST['insert_basic_vars'])){
						eva_Variable::create_basic_variable();
					}

					$wpdb->insert(TABLE_METHODE, array('nom' => 'Evarisk', 'default_methode' => 'yes'));
					$new_method_id = $wpdb->insert_id;

					$method_picture_id = EvaPhoto::saveNewPicture(TABLE_METHODE, $new_method_id, 'uploads/wp_eva__methode/1/tabcoeff.gif');
					EvaPhoto::setMainPhoto(TABLE_METHODE, $new_method_id, $method_picture_id, 'yes');

					/*	Method's var management	*/
					foreach($evaluation_main_vars as $var_index => $var_definition){
						$query = $wpdb->prepare("SELECT id FROM " . TABLE_VARIABLE . " WHERE nom = %s", $var_definition['nom']);
						$var_id = $wpdb->get_var($query);

						/*	Insert link between method and var	*/
						$wpdb->insert(TABLE_AVOIR_VARIABLE, array('id_methode' => $new_method_id, 'id_variable' => $var_id, 'ordre' => ($var_index + 1), 'date' => $install_date));
					}
					/*	Method's operator management	*/
					for($i = 1; $i < count($evaluation_main_vars); $i++){
						$wpdb->insert(TABLE_AVOIR_OPERATEUR, array('id_methode' => $new_method_id, 'operateur' => '*', 'ordre' => $i, 'date' => $install_date));
					}
					/*	Method var comparator	*/
					foreach($evaluation_method_evarisk__etalon as $index => $correspondence){
						$wpdb->insert(TABLE_EQUIVALENCE_ETALON, array('id_methode' => $new_method_id, 'id_valeur_etalon' => $correspondence['id_valeur_etalon'], 'date' => $install_date, 'valeurMaxMethode' => $correspondence['valeurMaxMethode'], 'Status' => 'Valid'));
					}
				}

				/*	Theme management	*/
				digirisk_tools::copyEntireDirectory( EVA_HOME_DIR . 'evariskthemeplugin', WP_CONTENT_DIR . '/themes/Evarisk' );
				if ( isset($_REQUEST['activate_evarisk_theme']) && ($_REQUEST['activate_evarisk_theme'] == 'yes') ) {
					switch_theme('Evarisk', 'Evarisk');
				}

				/*
				 * Execution des action specifiques pour certaines version
				 */
				$version_to_make = array(73, 79, 80, 81, 86, 89, 90, 91);
				foreach($version_to_make as $version_number){
					digirisk_install::make_specific_operation_on_update($version_number);
				}

				/*	Create option allowing to know information about plugin databse version	*/
				add_option('digirisk_db_option', array('base_evarisk' => ($max_number+1), 'last_update_date' => substr(current_time('mysql', 0), 0, 10), 'last_db_update_time' => substr(current_time('mysql', 0), 11, 8)));

				echo json_encode(array('status' => true));
			}break;

			case 'setAsBeforePicture':
			{
				switch($_REQUEST['table']){
					case TABLE_TACHE:
					{
						$element = new EvaTask($_REQUEST['idElement']);
					}
					break;
					case TABLE_ACTIVITE:
					{
						$element = new EvaActivity($_REQUEST['idElement']);
					}
					break;
				}
				$element->load();
				$old_photo_avant = $element->getidPhotoAvant();
				$element->setidPhotoAvant($_REQUEST['idPhoto']);
				$element->save();
				$messageInfo = '<script type="text/javascript">
						digirisk(document).ready(function(){
							digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").addClass("updated");';
				if($element->getStatus() != 'error'){
					/*	Log modification on element and notify user if user subscribe	*/
					digirisk_user_notification::log_element_modification($_REQUEST['table'], $_REQUEST['idElement'], 'picture_as_before_add', '', $_REQUEST['idPhoto']);
					$messageInfo .= '
							digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La photo a bien &eacute;t&eacute; d&eacute;finie comme photo avant l\'action', 'evarisk') . '</strong></p>') . '");';
				}
				else{
					$messageInfo .= '
							digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La photo n\'a pas pu &ecirc;tre d&eacute;finie comme photo avant l\'action', 'evarisk') . '</strong></p>') . '");';
				}
				$messageInfo .= '
							digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").show();
							setTimeout(function(){
								digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").removeClass("updated");
								digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").hide();
							},7500);
							reloadcontainer(\'' . $_REQUEST['table'] . '\', \'' . $_REQUEST['idElement'] . '\', \'' . PICTO_LOADING_ROUND . '\');
						});
				</script>';
				echo $messageInfo;
			}
			break;
			case 'setAsAfterPicture':
			{
				switch($_REQUEST['table']){
					case TABLE_TACHE:
					{
						$element = new EvaTask($_REQUEST['idElement']);
					}
					break;
					case TABLE_ACTIVITE:
					{
						$element = new EvaActivity($_REQUEST['idElement']);
					}
					break;
				}
				$element->load();
				$old_photo_apres = $element->getidPhotoApres();
				$element->setidPhotoApres($_REQUEST['idPhoto']);
				$element->save();
				$messageInfo = '<script type="text/javascript">
						digirisk(document).ready(function(){
							digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").addClass("updated");';
				if($element->getStatus() != 'error'){
					/*	Log modification on element and notify user if user subscribe	*/
					digirisk_user_notification::log_element_modification($_REQUEST['table'], $_REQUEST['idElement'], 'picture_as_after_add', $old_photo_apres, $_REQUEST['idPhoto']);
					$messageInfo .= '
							digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La photo a bien &eacute;t&eacute; d&eacute;finie comme photo apr&egrave;s l\'action', 'evarisk') . '</strong></p>') . '");';
				}
				else{
					$messageInfo .= '
							digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La photo n\'a pas pu &ecirc;tre d&eacute;finie comme photo apr&egrave;s l\'action', 'evarisk') . '</strong></p>') . '");';
				}
				$messageInfo .= '
							digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").show();
							setTimeout(function(){
								digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").removeClass("updated");
								digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").hide();
							},7500);
							reloadcontainer(\'' . $_REQUEST['table'] . '\', \'' . $_REQUEST['idElement'] . '\', \'' . PICTO_LOADING_ROUND . '\');
						});
				</script>';
				echo $messageInfo;
			}
			break;
			case 'unsetAsBeforePicture':
			{
				switch($_REQUEST['table']){
					case TABLE_TACHE:
					{
						$element = new EvaTask($_REQUEST['idElement']);
					}
					break;
					case TABLE_ACTIVITE:
					{
						$element = new EvaActivity($_REQUEST['idElement']);
					}
					break;
				}
				$element->load();
				$old_photo_avant = $element->getidPhotoAvant();
				$element->setidPhotoAvant("0");
				$element->save();
				$messageInfo = '<script type="text/javascript">
						digirisk(document).ready(function(){
							digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").addClass("updated");';
				if($element->getStatus() != 'error'){
					/*	Log modification on element and notify user if user subscribe	*/
					digirisk_user_notification::log_element_modification($_REQUEST['table'], $_REQUEST['idElement'], 'picture_as_before_delete', $old_photo_avant, $old_photo_avant);
					$messageInfo .= '
							digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La photo n\'est plus d&eacute;finie comme photo avant l\'action', 'evarisk') . '</strong></p>') . '");';
				}
				else{
					$messageInfo .= '
							digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La photo est toujours d&eacute;finie comme photo avant l\'action', 'evarisk') . '</strong></p>') . '");';
				}
				$messageInfo .= '
							digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").show();
							setTimeout(function(){
								digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").removeClass("updated");
								digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").hide();
							},7500);
							reloadcontainer(\'' . $_REQUEST['table'] . '\', \'' . $_REQUEST['idElement'] . '\', \'' . PICTO_LOADING_ROUND . '\');
						});
				</script>';
				echo $messageInfo;
			}
			break;
			case 'unsetAsAfterPicture':
			{
				switch($_REQUEST['table']){
					case TABLE_TACHE:
					{
						$element = new EvaTask($_REQUEST['idElement']);
					}
					break;
					case TABLE_ACTIVITE:
					{
						$element = new EvaActivity($_REQUEST['idElement']);
					}
					break;
				}
				$element->load();
				$element->setidPhotoApres("0");
				$old_photo_apres = $element->getidPhotoApres();
				$element->save();
				$messageInfo = '<script type="text/javascript">
						digirisk(document).ready(function(){
							digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").addClass("updated");';
				if($element->getStatus() != 'error'){
					/*	Log modification on element and notify user if user subscribe	*/
					digirisk_user_notification::log_element_modification($_REQUEST['table'], $_REQUEST['idElement'], 'picture_as_after_delete', $old_photo_apres, $old_photo_apres);
					$messageInfo .= '
							digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La photo n\'est plus d&eacute;finie comme photo apr&egrave;s l\'action', 'evarisk') . '</strong></p>') . '");';
				}
				else{
					$messageInfo .= '
							digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La photo est toujours d&eacute;finie comme photo apr&egrave;s l\'action', 'evarisk') . '</strong></p>') . '");';
				}
				$messageInfo .= '
							digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").show();
							setTimeout(function(){
								digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").removeClass("updated");
								digirisk("#message' . $_REQUEST['table'] . '_' . $_REQUEST['idElement'] . '").hide();
							},7500);
							reloadcontainer(\'' . $_REQUEST['table'] . '\', \'' . $_REQUEST['idElement'] . '\', \'' . PICTO_LOADING_ROUND . '\');
						});
				</script>';
				echo $messageInfo;
			}
			break;
			case "loadFieldsNewVariable": {
				$var_id = digirisk_tools::IsValid_Variable($_REQUEST['var_id'], 0);
				$choixTypeAffichage = digirisk_tools::IsValid_Variable($_REQUEST['choixTypeAffichage']);
				$alternativ_vars = array();
				if(!empty($var_id)){
					$query = $wpdb->prepare("SELECT * FROM " . TABLE_VALEUR_ALTERNATIVE . " WHERE id_variable = %d and Status = %s", $var_id, 'Valid');
					$existing_alternativ_vars = $wpdb->get_results($query);
					foreach($existing_alternativ_vars as $var_equivalence){
						$alternativ_vars[$var_equivalence->valeur] = $var_equivalence->valeurAlternative;
					}
				}
				for($i=$_REQUEST['min']; $i<=$_REQUEST['max']; $i++){
					$idInput = 'newVariableAlterValueFor' . $i;
					$nomChamps = 'newVariableAlterValue[' . $i . ']';
					$labelInput = sprintf(__('Valeur r&eacute;el de la variable quand elle vaut %d : ', 'evarisk'), $i);

					if ( $choixTypeAffichage == "checkbox" ) {
						$query = $wpdb->get_row("SELECT questionVar FROM " . TABLE_VARIABLE. " WHERE id = ".$var_id." ");
						$tableauVar = unserialize($query->questionVar);

						$nomChampsQuestion = 'varQuestion[' . $i . ']';
						$nomChampsSeuil = 'varSeuil[' . $i . ']';
						$labelInputQuestion = sprintf(__('Question n %s &agrave; poser : ', 'evarisk'), $i);
						$labelInputSeuil = sprintf(__('Valeur du Seuil n %s : ', 'evarisk'), $i);


						echo '<div style="background : #F3F3F3; padding : 10px 5px 10px 5px; margin-bottom : 15px;">';
						echo EvaDisplayInput::afficherInput('text', 'newVarSeuil'.$i, $tableauVar[$i-1]['seuil'], '', $labelInputSeuil, $nomChampsSeuil, true, false, 100, '', 'Float');
						echo EvaDisplayInput::afficherInput('text', 'newVarQuestion'.$i, $tableauVar[$i-1]['question'], '', $labelInputQuestion,$nomChampsQuestion, true, false, 100, '');
						echo '</div>';
					}
					else {
						echo EvaDisplayInput::afficherInput('text', $idInput, (!empty($alternativ_vars[$i]) ? $alternativ_vars[$i] : ''), '', $labelInput, $nomChamps, true, false, 100, '', 'Float');
					}
				}
			}
			break;
			case "veilleSummary":
			{
					$tableElement = digirisk_tools::IsValid_Variable($_REQUEST['tableElement']);
					$idElement = digirisk_tools::IsValid_Variable($_REQUEST['idElement']);
					$veilleResult = evaAnswerToQuestion::getAnswersForStats(date('Y-m-d'), $tableElement, $idElement, 2);
					$myCharts = ' ';
					if( count($veilleResult) > 0)
					{
					foreach($veilleResult as $responseName => $listResponse)
					{
						$myCharts .= '[digi_html_accent_for_js(\''.$responseName.' ('.count($listResponse).')\'),'.count($listResponse).'],';
					}
					$chartContent = trim(substr($myCharts,0,-1));

					$messageInfo .= '<script type="text/javascript" language="javascript">
						digirisk(document).ready(function(){
							line1 = [' . $chartContent . '];
							plot2 = $.jqplot("resultChart", [line1], {
								seriesDefaults:{renderer:$.jqplot.PieRenderer},
								legend:{show:true, escapeHtml:true}
							});
						});
					</script>
					<div id="resultChart" style="margin-top:20px; margin-left:20px; width:400px; height:300px;">
					</div>';
					};
					echo $messageInfo;
				}
			break;
			case "veilleClicPagination":
			{
				require_once(EVA_METABOXES_PLUGIN_DIR . 'veilleReglementaire/formulaireReponse.php');
				$summary = ($_REQUEST['act'] == 'summary') ? true : false ;
				echo '<div id="plotLocation"></div><div id="interractionVeille">' . getFormulaireReponse($_REQUEST['idElement'], $_REQUEST['tableElement'], $summary) . '</div>';
			}
			break;
			case "veilleClicValidation":
			{
					$questionID = digirisk_tools::IsValid_Variable($_REQUEST['idQuestion']);
					$tableElement = digirisk_tools::IsValid_Variable($_REQUEST['tableElement']);
					$idElement = digirisk_tools::IsValid_Variable($_REQUEST['idElement']);
					$reponse = digirisk_tools::IsValid_Variable($_REQUEST['reponse']);
					$valeurReponse = digirisk_tools::IsValid_Variable($_REQUEST['valeur']);
					$observationReponse = digirisk_tools::IsValid_Variable($_REQUEST['observation']);
					$soumission = digirisk_tools::IsValid_Variable($_REQUEST['soumission']);
					$limiteValidite = digirisk_tools::IsValid_Variable($_REQUEST['limiteValidite']);
					$save = EvaAnswerToQuestion::saveNewAnswerToQuestion($questionID, $tableElement, $idElement, date('Y-m-d'), $reponse, $valeurReponse, $observationReponse, $limiteValidite);
					$messageInfo = '';
					// if($soumission != 'totale')
					// {
						if($save == 'ok')
						{
							$messageInfo =
								'<span id="message" class="updated fade below-h2" style="cursor:pointer;" >
									<strong><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;La r&eacute;ponse a bien &eacute;t&eacute; enregistr&eacute;e.</strong>
								</span>
								<script type="text/javascript" >
									setTimeout(function(){digirisk(\'#observationTropLongue' . $questionID . '\').html("")},5000);
								</script>';
						}
						else
						{
							$messageInfo =
								'<span id="message" class="updated fade below-h2">
									<p><strong><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="noresponse" style="vertical-align:middle;" />&nbsp;La r&eacute;ponse n\'a pas pu &ecirc;tre enregistr&eacute;e</strong></p>
								</span>
								<script type="text/javascript" >
									setTimeout(function(){digirisk(\'#observationTropLongue' . $questionID . '\').html("")},5000);
								</script>';
						}
					// }
					// else
					// {
						// if($save == 'ok')
						// {
							// $_REQUEST['statusVeille'] = $_REQUEST['statusVeille'] . true;
						// }
						// else
						// {
							// $_REQUEST['statusVeille'] = false;
						// }
					// }
					echo $messageInfo;
				}
			break;

			case "chargerInfosGeneralesVeille" :
			{
					require_once(EVA_METABOXES_PLUGIN_DIR . 'veilleReglementaire/infoGeneraleVeille.php');
				}
			break;

			case 'updateTrash':
			{
				$tableProvenance = digirisk_tools::IsValid_Variable($_REQUEST['tableProvenance']);
				$elementToRestore = digirisk_tools::IsValid_Variable($_REQUEST['elementToRestore']);
				$elementToRestore = explode(', ', $elementToRestore);
				$queryResult = $i = 0;
				if(is_array($elementToRestore) && (count($elementToRestore) > 0)){
					foreach($elementToRestore as $elements){
						if($elements != '')
						{
							$element = explode('_element_to_restore_', $elements);
							$queryResult += $wpdb->update($element[0], array('Status' => 'Valid'), array('id' => $element[1]));
							$i++;
						}
					}
				}
				if($queryResult == $i){
					$message = __('La restauration des &eacute;l&eacute;ments s&eacute;lectionn&eacute;s a &eacute;t&eacute; effectu&eacute;e avec succ&egrave;s', 'evarisk');
				}
				else{
					$message = __('Des erreurs sont survenues lors de la restauration des &eacute;l&eacute;ments s&eacute;lectionn&eacute;s', 'evarisk');
				}

switch($tableProvenance)
{
	case TABLE_CATEGORIE_PRECONISATION:
	case TABLE_PRECONISATION:
	{
		$actionAfterUpdate = '
		digirisk("#recommandationTable").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
		{
			"post":"true",
			"table":"' . TABLE_PRECONISATION . '",
			"act":"reloadRecommandationList"
		});';
	}
	break;
	case TABLE_METHODE:
	{
		$actionAfterUpdate = '
		digirisk("#methode-filter").submit();';
	}
	break;
	default:
	{
		$actionAfterUpdate = '
		changementPage("left", "' . $tableProvenance . '", 1, 1, "affichageListe", "main");';
	}
	break;
}

				echo '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		digirisk("#trashContainer").dialog("close");

		actionMessageShow("#message", "' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . $message . '</strong></p>') . '");
		setTimeout(\'actionMessageHide("#message")\',7500);
' . $actionAfterUpdate . '
	});
</script>';
			}
			break;
			case 'loadTrash':
			{
				$output = '';

				$userIsAllowedToUpdateTrash = false;
				$main_option = get_option('digirisk_options');
				$tableProvenance = digirisk_tools::IsValid_Variable($_REQUEST['tableProvenance']);
				$trash_elements = array();
				$i =0;
				$statusFieldValue = 'Deleted';
				switch ($tableProvenance) {
					case TABLE_GROUPEMENT:
						if (($main_option['digi_activ_trash'] == 'oui') && current_user_can('digi_view_groupement_trash')) {
							$trash_elements[$i]['element'] = $tableProvenance;
							$trash_elements[$i]['name'] = __('Groupements', 'evarisk');
							$trash_elements[$i]['prefix_identifier'] = ELEMENT_IDENTIFIER_GP;
							$i++;
						}
						if (($main_option['digi_activ_trash'] == 'oui') && current_user_can('digi_view_unite_trash')) {
							$trash_elements[$i]['element'] = TABLE_UNITE_TRAVAIL;
							$trash_elements[$i]['name'] = __('Unit&eacute;s de travail', 'evarisk');
							$trash_elements[$i]['prefix_identifier'] = ELEMENT_IDENTIFIER_UT;
							$i++;
						}
					break;
					case TABLE_TACHE:
						if (($main_option['digi_activ_trash'] == 'oui') && current_user_can('digi_view_task_trash')) {
								$trash_elements[$i]['element'] = $tableProvenance;
								$trash_elements[$i]['name'] = __('T&acirc;ches', 'evarisk');
								$trash_elements[$i]['prefix_identifier'] = ELEMENT_IDENTIFIER_T;
								$i++;
						}
						if (($main_option['digi_activ_trash'] == 'oui') && current_user_can('digi_view_action_trash')) {
							$trash_elements[$i]['element'] = TABLE_ACTIVITE;
							$trash_elements[$i]['name'] = __('Sous-t&acirc;ches', 'evarisk');
							$trash_elements[$i]['prefix_identifier'] = ELEMENT_IDENTIFIER_ST;
							$i++;
						}
					break;
					case TABLE_CATEGORIE_DANGER:
						if (($main_option['digi_activ_trash'] == 'oui') && current_user_can('digi_view_danger_category_trash')) {
								$trash_elements[$i]['element'] = $tableProvenance;
								$trash_elements[$i]['name'] = __('Cat&eacute;gories de danger', 'evarisk');
								$trash_elements[$i]['prefix_identifier'] = ELEMENT_IDENTIFIER_CD;
								$i++;
						}
						if (($main_option['digi_activ_trash'] == 'oui') && current_user_can('digi_view_danger_trash')) {
							$trash_elements[$i]['element'] = TABLE_DANGER;
							$trash_elements[$i]['name'] = __('Dangers', 'evarisk');
							$trash_elements[$i]['prefix_identifier'] = ELEMENT_IDENTIFIER_D;
							$i++;
						}
					break;
					case TABLE_METHODE:
						if (($main_option['digi_activ_trash'] == 'oui') && current_user_can('digi_view_method_trash')) {
							$trash_elements[$i]['element'] = $tableProvenance;
							$trash_elements[$i]['name'] = __('M&eacute;thodes d\'&eacute;valuation', 'evarisk');
							$trash_elements[$i]['prefix_identifier'] = ELEMENT_IDENTIFIER_ME;
							$i++;
						}
					break;
					case DIGI_DBT_PERMISSION_ROLE:
						if (($main_option['digi_activ_trash'] == 'oui') && current_user_can('digi_view_user_role_trash')) {
							$trash_elements[$i]['element'] = $tableProvenance;
							$trash_elements[$i]['name'] = __('R&ocirc;le pour les utilisateurs', 'evarisk');
							$trash_elements[$i]['prefix_identifier'] = ELEMENT_IDENTIFIER_UR;
							$i++;
						}
					break;
					case TABLE_CATEGORIE_PRECONISATION:
						$statusFieldValue = 'deleted';
						if (($main_option['digi_activ_trash'] == 'oui') && current_user_can('digi_view_recommandation_category_trash')) {
								$trash_elements[$i]['element'] = $tableProvenance;
								$trash_elements[$i]['name'] = __('Cat&eacute;gories de pr&eacute;conisations', 'evarisk');
								$trash_elements[$i]['prefix_identifier'] = ELEMENT_IDENTIFIER_CP;
								$i++;
						}
						if (($main_option['digi_activ_trash'] == 'oui') && current_user_can('digi_view_recommandation_trash')) {
								$trash_elements[$i]['element'] = TABLE_PRECONISATION;
								$trash_elements[$i]['name'] = __('Pr&eacute;conisations', 'evarisk');
								$trash_elements[$i]['prefix_identifier'] = ELEMENT_IDENTIFIER_P;
								$i++;
						}
					break;
				}

				if (is_array($trash_elements) && (count($trash_elements) > 0)) {
					foreach ($trash_elements as $element => $element_definition) {
						$subTrashOutput = '';
						/*	Check if there are something to display in trash for the current element	*/
						$query = $wpdb->prepare("SELECT * FROM " . $element_definition['element'] . " WHERE status = '" . $statusFieldValue . "';", "");
						$trashedElement = $wpdb->get_results($query);
						if (count($trashedElement) > 0) {
							unset($lignesDeValeurs);
							$idTable = 'trashedElement' . $element_definition['element'];
							$titres = array(__('Photo', 'evarisk'), __('Nom', 'evarisk'), __('Description', 'evarisk'));
							$classes = array('trashPicColumn', 'trashNameColumn', 'trashDescriptionColumn');
							$columnAdded = false;
							foreach ($trashedElement as $element) {
								$nameField = 'nom';

								unset($ligne);
								$elementMainPicture = evaPhoto::getMainPhoto($element_definition['element'], $element->id);
								$elementMainPicture = evaPhoto::checkIfPictureIsFile($elementMainPicture, $element_definition['element']);
								$elementPicture = ( $elementMainPicture != '' ) ? '<img class="elementPicture" style="width:' . TAILLE_PICTOS . ';" src="' . $elementMainPicture . '" alt="element picture" />' : __('Pas de photo', 'evarisk');
								$ligne[] = array('value' => $elementPicture, 'class' => '');
								$ligne[] = array('value' => $element_definition['prefix_identifier'] . $element->id . '&nbsp;-&nbsp;' . $element->$nameField, 'class' => '');
								$ligne[] = array('value' => $element->description, 'class' => '');

								$has_sub_element = false;
								$able_to_restore_current_element = true;
								$parent_class = '';
								$is_disabled = '';

								/*	Add the different column for each element type	*/
								switch ($element_definition['element']) {
									case TABLE_GROUPEMENT: {
										if (current_user_can('digi_edit_groupement_trash')) {
											$userIsAllowedToUpdateTrash = true;
										}
										if (!$columnAdded) {
											$titres[] = __('Hi&eacute;rarchie', 'evarisk');
											$classes[] = 'trashParentColumn';
											$titres[] = __('Descendants', 'evarisk');
											$classes[] = 'trashChildrenColumn';
											$columnAdded = true;
										}
										$ancetres = Arborescence::getAncetre($element_definition['element'], $element, "limiteGauche ASC", '1', "");
										$direct_parent = Arborescence::getPere($element_definition['element'], $element, "1");
										$parent_class .= ' direct_' . TABLE_GROUPEMENT . '_element_to_restore_' . $direct_parent->id;
										if ($direct_parent->Status != 'Valid') {
											$is_disabled = ' disabled="disabled" ';
											$parent_class .= ' orignal_disabled';
										}
										$miniFilAriane = '         ';
										foreach ($ancetres as $ancetre) {
											$ancester_children = EvaGroupement::getUnitesDuGroupement($ancetre->id);
											if (is_array($ancester_children) && (count($ancester_children) > 0)) {
												$able_to_restore_current_element = false;
											}
											if ($ancetre->nom != "Groupement Racine") {
												$miniFilAriane .= $element_definition['prefix_identifier'] . $ancetre->id . '&nbsp;-&nbsp;' . $ancetre->nom . ' &raquo; ';
												$parent_class .= ' children_of_' . TABLE_GROUPEMENT . '_element_to_restore_' . $ancetre->id;
											}
										}
										$ligne[] = array('value' => substr($miniFilAriane, 0, -9), 'class' => '');
										$miniFilAriane = '         ';
										$descendants = Arborescence::getDescendants($element_definition['element'], $element, '1', 'id ASC', "");
										if (count($descendants) > 0) {
											foreach ($descendants as $descendant) {
												$miniFilAriane .= ELEMENT_IDENTIFIER_GP . $descendant->id . '&nbsp;-&nbsp;' . $descendant->nom . ' &raquo; ';
											}
											$has_sub_element = true;
										}
										$descendants = EvaGroupement::getUnitesDescendantesDuGroupement($element->id, '1', 'nom ASC', "");
										if (count($descendants) > 0) {
											foreach ($descendants as $descendant) {
												$miniFilAriane .= ELEMENT_IDENTIFIER_UT . $descendant->id . '&nbsp;-&nbsp;' . $descendant->nom . ' &raquo; ';
											}
											$has_sub_element = true;
										}
										$ligne[] = array('value' => substr($miniFilAriane, 0, -9), 'class' => '');
									}
									break;
									case TABLE_UNITE_TRAVAIL: {
										if (current_user_can('digi_edit_groupement_trash')) {
											$userIsAllowedToUpdateTrash = true;
										}
										if (!$columnAdded) {
											$titres[] = __('Hi&eacute;rarchie', 'evarisk');
											$classes[] = 'trashParentColumn';
											$columnAdded = true;
										}
										$directParent = EvaGroupement::getGroupement($element->id_groupement);
										$ancetres = Arborescence::getAncetre(TABLE_GROUPEMENT, $directParent, "limiteGauche ASC", '1', "");
										$miniFilAriane = '         ';
										foreach ($ancetres as $ancetre) {
											if ($ancetre->nom != "Groupement Racine") {
												$miniFilAriane .= ELEMENT_IDENTIFIER_GP . $ancetre->id . '&nbsp;-&nbsp;' . $ancetre->nom . ' &raquo; ';
												$parent_class .= ' children_of_' . TABLE_GROUPEMENT . '_element_to_restore_' . $ancetre->id;
											}
										}
										if ($directParent->nom != "Groupement Racine") {
											$miniFilAriane .= ELEMENT_IDENTIFIER_GP . $directParent->id . '&nbsp;-&nbsp;' . $directParent->nom . ' &raquo; ';
											$parent_class .= ' children_of_' . TABLE_GROUPEMENT . '_element_to_restore_' . $directParent->id . ' direct_' . TABLE_GROUPEMENT . '_element_to_restore_' . $directParent->id;
											$parent_direct_children = Arborescence::getDescendants(TABLE_GROUPEMENT, $directParent);
											if (is_array($parent_direct_children) && (count($parent_direct_children) > 0)) {
												$able_to_restore_current_element = false;
											}
											if ($directParent->Status != 'Valid') {
												$is_disabled = ' disabled="disabled" ';
												$parent_class .= ' orignal_disabled';
											}
										}
										$ligne[] = array('value' => substr($miniFilAriane, 0, -9), 'class' => '');
									}
									break;
									case TABLE_TACHE:
									{
										if(current_user_can('digi_edit_task_trash')){
											$userIsAllowedToUpdateTrash = true;
										}
										if(!$columnAdded){
											$titres[] = __('Hi&eacute;rarchie', 'evarisk');
											$classes[] = 'trashParentColumn';
											$titres[] = __('Descendants', 'evarisk');
											$classes[] = 'trashChildrenColumn';
											$columnAdded = true;
										}
										$ancetres = Arborescence::getAncetre(TABLE_TACHE, $element, "limiteGauche ASC", '1', "");
										$direct_parent = Arborescence::getPere($element_definition['element'], $element, "1");
										$parent_class .= ' direct_' . TABLE_TACHE . '_element_to_restore_' . $direct_parent->id;
										if ($direct_parent->Status != 'Valid') {
											$is_disabled = ' disabled="disabled" ';
											$parent_class .= ' orignal_disabled';
										}
										$miniFilAriane = '         ';
										foreach($ancetres as $ancetre){
											$ancester_children = EvaTask::getChildren($ancetre->id);
											if (is_array($ancester_children) && (count($ancester_children) > 0)) {
// 												$able_to_restore_current_element = false;
											}
											if($ancetre->nom != "Tache Racine"){
												$miniFilAriane .= ELEMENT_IDENTIFIER_T . $ancetre->id . '&nbsp;-&nbsp;' . $ancetre->nom . ' &raquo; ';
												$parent_class .= ' children_of_' . TABLE_TACHE . '_element_to_restore_' . $ancetre->id;
											}
										}
										$ligne[] = array('value' => substr($miniFilAriane, 0, -9), 'class' => '');
										$miniFilAriane = '         ';
										$descendants = Arborescence::getDescendants($element_definition['element'], $element, '1', 'id ASC', "");
										if (count($descendants) > 0) {
											foreach($descendants as $descendant){
												$miniFilAriane .= ELEMENT_IDENTIFIER_T . $descendant->id . '&nbsp;-&nbsp;' . $descendant->nom . ' &raquo; ';
											}
											$has_sub_element = true;
										}
										$descendants = EvaTask::getChildren($element->id);
										if (count($descendants) > 0) {
											foreach($descendants as $descendant){
												$miniFilAriane .= ELEMENT_IDENTIFIER_ST . $descendant->id . '&nbsp;-&nbsp;' . $descendant->nom . ' &raquo; ';
											}
										}
										$ligne[] = array('value' => substr($miniFilAriane, 0, -9), 'class' => '');
									}
									break;
									case TABLE_ACTIVITE:
									{
										if(current_user_can('digi_edit_action_trash')){
											$userIsAllowedToUpdateTrash = true;
										}
										if(!$columnAdded){
											$titres[] = __('Hi&eacute;rarchie', 'evarisk');
											$classes[] = 'trashParentColumn';
											$columnAdded = true;
										}
										$directParent = new EvaTask();
										$directParent->setId($element->id_tache);
										$directParent->load();
										$directParent->limiteGauche = $directParent->leftLimit;
										$directParent->limiteDroite = $directParent->rightLimit;
										$ancetres = Arborescence::getAncetre(TABLE_TACHE, $directParent, "limiteGauche ASC", '1', "");
										$miniFilAriane = '         ';
										foreach($ancetres as $ancetre){
											if($ancetre->nom != "Tache Racine"){
												$miniFilAriane .= ELEMENT_IDENTIFIER_T . $ancetre->id . '&nbsp;-&nbsp;' . $ancetre->nom . ' &raquo; ';
												$parent_class .= ' children_of_' . TABLE_TACHE . '_element_to_restore_' . $ancetre->id;
											}
										}
										if($directParent->nom != "Tache Racine"){
											$miniFilAriane .= ELEMENT_IDENTIFIER_T . $directParent->id . '&nbsp;-&nbsp;' . $directParent->name . ' &raquo; ';
											$parent_class .= ' children_of_' . TABLE_TACHE . '_element_to_restore_' . $directParent->id . ' direct_' . TABLE_TACHE . '_element_to_restore_' . $directParent->id;
											$parent_direct_children = Arborescence::getDescendants(TABLE_TACHE, $directParent);
											if (is_array($parent_direct_children) && (count($parent_direct_children) > 0)) {
												$able_to_restore_current_element = false;
											}
											if ($directParent->Status != 'Valid') {
												$is_disabled = ' disabled="disabled" ';
												$parent_class .= ' orignal_disabled';
											}
										}
										$ligne[] = array('value' => substr($miniFilAriane, 0, -9), 'class' => '');
									}
									break;
									case TABLE_CATEGORIE_DANGER:
									{
										if(current_user_can('digi_edit_danger_category_trash')){
											$userIsAllowedToUpdateTrash = true;
										}
										if(!$columnAdded){
											$titres[] = __('Hi&eacute;rarchie', 'evarisk');
											$classes[] = 'trashParentColumn';
											$titres[] = __('Descendants', 'evarisk');
											$classes[] = 'trashChildrenColumn';
											$columnAdded = true;
										}
										$ancetres = Arborescence::getAncetre($element_definition['element'], $element);
										$miniFilAriane = '         ';
										foreach($ancetres as $ancetre){
											if($ancetre->nom != "Categorie Racine"){
												$miniFilAriane .= $element_definition['prefix_identifier'] . $ancetre->id . '&nbsp;-&nbsp;' . $ancetre->nom . ' &raquo; ';
											}
										}
										$ligne[] = array('value' => substr($miniFilAriane, 0, -9), 'class' => '');
										$miniFilAriane = '         ';
										$descendants = Arborescence::getDescendants($element_definition['element'], $element);
										foreach($descendants as $descendant){
											$miniFilAriane .= ELEMENT_IDENTIFIER_CD . $descendant->id . '&nbsp;-&nbsp;' . $descendant->nom . ' &raquo; ';
										}
										$descendants = categorieDangers::getChildren($element->id);
										foreach($descendants as $descendant){
											$miniFilAriane .= ELEMENT_IDENTIFIER_D . $descendant->id . '&nbsp;-&nbsp;' . $descendant->nom . ' &raquo; ';
										}
										$ligne[] = array('value' => substr($miniFilAriane, 0, -9), 'class' => '');
									}
									break;
									case TABLE_DANGER:
									{
										if(current_user_can('digi_edit_danger_trash')){
											$userIsAllowedToUpdateTrash = true;
										}
										if(!$columnAdded){
											$titres[] = __('Hi&eacute;rarchie', 'evarisk');
											$classes[] = 'trashParentColumn';
											$columnAdded = true;
										}
										$directParent = categorieDangers::getCategorieDanger($element->id_categorie);
										$ancetres = Arborescence::getAncetre(TABLE_CATEGORIE_DANGER, $directParent);
										$miniFilAriane = '         ';
										foreach($ancetres as $ancetre){
											if($ancetre->nom != "Categorie Racine"){
												$miniFilAriane .= $element_definition['prefix_identifier'] . $ancetre->id . '&nbsp;-&nbsp;' . $ancetre->nom . ' &raquo; ';
											}
										}
										if($directParent->nom != "Categorie Racine"){
											$miniFilAriane .= ELEMENT_IDENTIFIER_CD . $directParent->id . '&nbsp;-&nbsp;' . $directParent->nom . ' &raquo; ';
										}
										$ligne[] = array('value' => substr($miniFilAriane, 0, -9), 'class' => '');
									}
									break;
									case TABLE_METHODE:
									{
										if(current_user_can('digi_edit_method_trash')){
											$userIsAllowedToUpdateTrash = true;
										}
									}
									break;
									case DIGI_DBT_PERMISSION_ROLE:
									{
										if(current_user_can('digi_edit_user_role_trash')){
											$userIsAllowedToUpdateTrash = true;
										}
										$nameField = 'role_name';
									}
									break;
									case TABLE_CATEGORIE_PRECONISATION:
									{
										if(current_user_can('digi_edit_recommandation_category_trash')){
											$userIsAllowedToUpdateTrash = true;
										}
									}
									break;
									case TABLE_PRECONISATION:
									{
										if(current_user_can('digi_edit_recommandation_trash')){
											$userIsAllowedToUpdateTrash = true;
										}
										if(!$columnAdded){
											$titres[] = __('Hi&eacute;rarchie', 'evarisk');
											$classes[] = 'trashParentColumn';
											$columnAdded = true;
										}
										$directParent = evaRecommandationCategory::getCategoryRecommandation($element->id_categorie_preconisation);
										$ligne[] = array('value' => ELEMENT_IDENTIFIER_CP . $directParent->id . '&nbsp;-&nbsp;' . $directParent->nom, 'class' => '');
									}
									break;
								}

								if($userIsAllowedToUpdateTrash){
									if($able_to_restore_current_element){
										$col_value = '<input ' . $is_disabled . ' type="checkbox" class="alignright elementToRestore ' . $parent_class . '" value="' . $element_definition['element'] . '_element_to_restore_' . $element->id . '" id="' . $element_definition['element'] . '_element_to_restore_' . $element->id . '" /><label class="alignright" for="' . $element_definition['element'] . '_element_to_restore_' . $element->id . '" >' . __('Cet &eacute;l&eacute;ment', 'evarisk') . '</label>';
										if($has_sub_element){
											$col_value .= '<br class="clear" />
										<input ' . $is_disabled . ' type="checkbox" class="alignright elementToRestoreRecursif ' . $parent_class . ' recursiv_for_' . $element_definition['element'] . '_element_to_restore_' . $element->id . '" value="' . $element_definition['element'] . '_element_to_restore_recusively_' . $element->id . '" id="' . $element_definition['element'] . '_element_to_restore_recusively_' . $element->id . '" /><label class="alignright" for="' . $element_definition['element'] . '_element_to_restore_recusively_' . $element->id . '" >' . __('Et sous-&eacute;l&eacute;ments', 'evarisk') . '</label>';
										}
									}
									else{
										$col_value = __('Restauration impossible', 'evarisk');
									}
									$ligne[] = array('value' => $col_value, 'class' => '');
								}

								$lignesDeValeurs[] = $ligne;
								$idLignes[] = 't' . $element_definition['prefix_identifier'] . $element->id;
							}
							if($userIsAllowedToUpdateTrash){
								$titres[] = '';
								$classes[] = 'trashActionColumn';
							}
							$script = '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		digirisk("#' . $idTable . ' tfoot").remove();
		digirisk("#' . $idTable . '").dataTable({
			"bPaginate": false,
			"bInfo": false,
			"bLengthChange": false,
			"oLanguage":{
				"sUrl": "' . EVA_INC_PLUGIN_URL . 'js/dataTable/jquery.dataTables.common_translation.txt"
			}
		});
	});
</script>';
							$subTrashOutput .= EvaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script);
						}
						else
						{
							$subTrashOutput .= '<div class="trashContentCenter" >' . __('La corbeille de cet &eacute;l&eacute;ment est vide', 'evarisk') . '</div>';
						}

						/*	Add output for each element	*/
						$output .= '
<fieldset class="elementTrashContainer" >
	<legend>' . sprintf(__('Contenu de la corbeille pour les %s', 'evarisk'), $element_definition['name']) . '</legend>
	' . $subTrashOutput . '
</fieldset>';
					}
				}
				else
				{
					$output ='<div class="trashContentCenter" >' .  __('Aucun &eacute;l&eacute;ment n\'a &eacute;t&eacute; s&eacute;lectionn&eacute;', 'evarisk') . '</div>';
				}

				if($userIsAllowedToUpdateTrash){
					$output .= '
<input type="hidden" value="" name="elementToRestore" id="elementToRestore" />
<input type="button" class="button-secondary updateTrash alignright" id="updateTrash" disabled="disabled" value="' . __('Restaurer la s&eacute;lection', 'evarisk') . '" />
<script type="text/javascript" >
	digirisk(document).ready(function(){

		jQuery(".elementToRestore").live("click", function(){
			add_element_to_restore(jQuery(this));
			var classes_element_to_restore = jQuery(this).attr("class").split(" ");
			var current_attr_id = jQuery(this).attr("id");

			if(!jQuery("#" + current_attr_id).is(":checked")){
				jQuery(".recursiv_for_" + jQuery(this).attr("id")).prop("checked", false);
			}
			if(jQuery("#" + current_attr_id).is(":checked")){
				jQuery(".direct_" + current_attr_id).each(function(){
					jQuery(this).prop("disabled", false);
				});
			}
			else{
				jQuery(".direct_" + current_attr_id).each(function(){
					if(jQuery(this).hasClass("orignal_disabled")){
						jQuery(this).prop("disabled", true);
						jQuery(this).prop("checked", false);
					}
				});
			}
			jQuery.each(classes_element_to_restore, function(index, item){
				if(item.substr(0, ' . strlen('direct_' . PREFIXE_EVARISK) . ') == "direct_' . PREFIXE_EVARISK . '"){
					jQuery("." + item).each(function(){
						if(jQuery(this).hasClass("elementToRestore") && (current_attr_id != jQuery(this).attr("id"))){
							if(jQuery("#" + current_attr_id).is(":checked")){
								jQuery(this).prop("disabled", true);
								jQuery(".recursiv_for_" + jQuery(this).attr("id")).each(function(){
									jQuery(this).prop("disabled", true);
								});
								jQuery(".children_of_" + jQuery(this).attr("id")).each(function(){
									jQuery(this).prop("disabled", true);
								});
							}
							else{
								jQuery(this).prop("disabled", false);
								jQuery(".recursiv_for_" + jQuery(this).attr("id")).each(function(){
									if(!jQuery(this).hasClass("orignal_disabled")){
										jQuery(this).prop("disabled", false);
									}
								});
								jQuery(".children_of_" + jQuery(this).attr("id")).each(function(){
									if(!jQuery(this).hasClass("orignal_disabled")){
										jQuery(this).prop("disabled", false);
									}
								});
							}
						}
					});
				}
			});
		});

		jQuery(".elementToRestoreRecursif").click(function(){
			var current_normal_element = jQuery(this).attr("id").replace("recusively_", "");
			var classes_element_to_restore = jQuery("#" + current_normal_element).attr("class").split(" ");
			var selector_id = jQuery(this).attr("id");
			if(jQuery(this).is(":checked")){
				jQuery("#" + current_normal_element).prop("checked", true);
				jQuery.each(classes_element_to_restore, function(index, item){
					if(item.substr(0, ' . strlen('direct_' . PREFIXE_EVARISK) . ') == "direct_' . PREFIXE_EVARISK . '"){
						jQuery("." + item).each(function(){
							if(jQuery(this).attr("id") != selector_id){
								jQuery(this).prop("disabled", true);
							}
						});
					}
				});
				jQuery(".children_of_" + current_normal_element).each(function(){
					jQuery(this).prop("disabled", false);
					jQuery(this).prop("checked", true);
					add_element_to_restore(jQuery(this));
				});
				jQuery("#" + current_normal_element).prop("checked", true);
				jQuery("#" + current_normal_element).prop("disabled", true);
			}
			else{
				jQuery("#" + current_normal_element).prop("checked", false);
				jQuery("#" + current_normal_element).prop("disabled", false);
				jQuery.each(classes_element_to_restore, function(index, item){
					if(item.substr(0, ' . strlen('direct_' . PREFIXE_EVARISK) . ') == "direct_' . PREFIXE_EVARISK . '"){
						jQuery("." + item).each(function(){
							jQuery(this).prop("disabled", false);
						});
					}
				});
				if(confirm(digi_html_accent_for_js("' . __('Souhaitez-vous d&eacute;cocher tous les sous-&eacute;l&eacute;ments &eacute;galement?', 'evarisk') . '"))){
					jQuery(".children_of_" + current_normal_element).each(function(){
						if(jQuery(this).hasClass("orignal_disabled")){
							jQuery(this).prop("disabled", true);
						}
						jQuery(this).prop("checked", false);
						add_element_to_restore(jQuery(this));
					});
				}
			}
			add_element_to_restore(jQuery("#" + current_normal_element));
		});

		jQuery("#updateTrash").click(function(){
			jQuery("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
				"post": "true",
				"tableProvenance": "' . $tableProvenance . '",
				"nom": "updateTrash",
				"elementToRestore" : jQuery("#elementToRestore").val()
			});
		});
	});

	function add_element_to_restore(element){
		var currentElementToRestore = jQuery("#elementToRestore").val();
		if(!element.hasClass("elementToRestoreRecursif")){
			var elementToAdd = element.val() + ", ";
			currentElementToRestore = currentElementToRestore.replace(elementToAdd, "");
			if(element.is(":checked")){
				currentElementToRestore = currentElementToRestore.replace(elementToAdd, "") + elementToAdd;
			}
			jQuery("#elementToRestore").val(currentElementToRestore);
			if(jQuery("#elementToRestore").val() != ""){
				jQuery("#updateTrash").prop("disabled", "");
				jQuery("#updateTrash").removeClass("button-secondary");
				jQuery("#updateTrash").addClass("button-primary");
			}
			else{
				jQuery("#updateTrash").prop("disabled", "disabled");
				jQuery("#updateTrash").removeClass("button-primary");
				jQuery("#updateTrash").addClass("button-secondary");
			}
		}
	}
</script>';
				}

				echo $output;
			}
			break;
			case 'initialize_groupement_tree':
				$query = $wpdb->prepare("SELECT id FROM " . TABLE_GROUPEMENT . " WHERE nom != %s ORDER BY id ASC", 'Groupement Racine');
				$groupements = $wpdb->get_results($query);
				$limiteGauche = 1;
				$limiteDroite = 2;
				foreach($groupements as $groupement){
					$wpdb->update(TABLE_GROUPEMENT, array('limiteGauche' => $limiteGauche, 'limiteDroite' => $limiteDroite), array('id' => $groupement->id));
					$limiteGauche=$limiteDroite+1;
					$limiteDroite=$limiteGauche+1;
				}
				$wpdb->update(TABLE_GROUPEMENT, array('limiteDroite' => $limiteDroite), array('nom' => 'Groupement Racine'));
				echo json_encode(array(true,''));
			break;
			case 'initialize_task_tree':
				$query = $wpdb->prepare("SELECT id FROM " . TABLE_TACHE . " WHERE nom != %s ORDER BY id ASC", 'Tache Racine');
				$groupements = $wpdb->get_results($query);
				$limiteGauche = 1;
				$limiteDroite = 2;
				foreach ($groupements as $groupement) {
					$wpdb->update(TABLE_TACHE, array('limiteGauche' => $limiteGauche, 'limiteDroite' => $limiteDroite), array('id' => $groupement->id));
					$limiteGauche = $limiteDroite+1;
					$limiteDroite = $limiteGauche+1;
				}
				$wpdb->update(TABLE_TACHE, array('limiteDroite' => $limiteGauche), array('nom' => 'Tache Racine'));
				echo json_encode(array(true,''));
			break;

			case "ficheAction" :
			case "demandeAction" :
			case "control_asked_action" :
				$idElement = ($_REQUEST['nom'] == 'control_asked_action') ? $_REQUEST['idElement'] : null;
				$output = '
<div id="current_risk_summary" class="current_risk_summary_task" >
	' . Risque::getTableQuotationRisque($_REQUEST['tableProvenance'], $_REQUEST['idProvenance'], $_REQUEST['nom']) . '
</div>
' . EvaActivity::sub_task_creation_form(array('tableElement' => $_REQUEST['tableElement'], 'idElement' => $idElement, 'idPere' => 1, 'affichage' => null, 'idsFilAriane' => null, 'output_mode' => 'return', 'requested_action' => $_REQUEST['nom'], 'tableProvenance' => $_REQUEST['tableProvenance'], 'idProvenance' => $_REQUEST['idProvenance'])) . '
<script type="text/javascript">
	digirisk(document).ready(function(){
		digirisk("#idProvenance_activite").val("' . $_REQUEST['idProvenance'] . '");
		digirisk("#tableProvenance_activite").val("' . $_REQUEST['tableProvenance'] . '");
		digirisk("#formRisque").html("");
	})
</script>';

				echo $output;
			break;

			case 'histo-risk':
			{
				$output = '';
				$tableElement = (isset($_REQUEST['tableElement']) && (trim($_REQUEST['tableElement']) != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['tableElement']) : '';
				$idElement = (isset($_REQUEST['idElement']) && (trim($_REQUEST['idElement']) != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['idElement']) : '';
				$tableProvenance = (isset($_REQUEST['tableProvenance']) && (trim($_REQUEST['tableProvenance']) != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['tableProvenance']) : '';
				$idProvenance = (isset($_REQUEST['idProvenance']) && (trim($_REQUEST['idProvenance']) != '')) ? "tableRisque.id = '" . digirisk_tools::IsValid_Variable($_REQUEST['idProvenance']) . "'" : '1';
				$output_mistake = (isset($_REQUEST['output_mistake']) && (trim($_REQUEST['output_mistake']) != '')) ? ", 'Deleted'" : '';
				$reload = (isset($_REQUEST['reload']) && (trim($_REQUEST['reload']) != '')) ? $_REQUEST['reload'] : '';

				$completeRiskList = Risque::getRisques($tableElement, $idElement, 'Valid', $idProvenance, 'tableRisque.id ASC', "'Valid', 'Moderated'" . $output_mistake);
				if($completeRiskList != null){
					foreach($completeRiskList as $risque){
						$risques[$risque->id][$risque->id_evaluation][] = $risque;
					}
				}

				$lowerRisk = $higherRisk = 0;
				if(isset($risques) && ($risques != null)){
					foreach($risques as $id_risque => $evaluation){
						if($reload == ''){
							$output .= '
	<div id="histo_risk_container_' . $id_risque . '" >';
						}

						$line = '  ';
						foreach($evaluation as $risque){
							$idMethode = $risque[0]->id_methode;
							$score = Risque::getScoreRisque($risque);
							$riskLevel = Risque::getEquivalenceEtalon($idMethode, $score, $risque[0]->date);
							$evaluation_status = ($risque[0]->evaluation_status == 'Deleted') ? '"' . $riskLevel . ' - (' . __('Erreur', 'evarisk') . ')"' : $riskLevel;
							$line .= '["' . $risque[0]->evaluation_date . '",' . $riskLevel . ', "' . str_replace( "
", "", $risque[0]->histo_com ) . '"], ';
						}

						$line = trim(substr($line, 0, -2));
						if($line != ''){
							$output .= '
		<div id="risk_chart_' . $id_risque . '" class="risk_histo_chart" ></div>';

							if(current_user_can('digi_view_mistake_risk_history')){
								$checked = ($output_mistake != '') ? 'checked="checked"' : '';
								$output .= '
		<br class="clear" />
		<input type="checkbox" name="output_mistake" class="output_mistake" value="yes" id="output_mistake' . $id_risque . '" ' . $checked . ' /><label for="output_mistake' . $id_risque . '" >' . __('Afficher les corrections', 'evarisk') . '</label>';
							}

							$output .= '
		<script type="text/javascript" >
			digirisk(document).ready(function(){
				var line1=[' . $line . '];
				var plot1 = jQuery.jqplot("risk_chart_' . $id_risque . '", [line1], {
					title:digi_html_accent_for_js("' . sprintf(__('Historique du risque %s', 'evarisk'), ELEMENT_IDENTIFIER_R . $id_risque) . ' - ' . $risque[0]->nomDanger . '"),
					axes:{
						xaxis:{
							renderer:jQuery.jqplot.DateAxisRenderer,
							tickOptions:{
								formatString:"%d/%m/%y %T"
							}
						},
						yaxis:{
							min:0,
							max:100,
							tickOptions:{
								formatString:"%s"
							}
						}
					},
					seriesDefaults:{
						pointLabels:{
							show:true,
							ypadding:4
						}
					},
					highlighter:{
						show:true,
						showMarker:false,
						tooltipAxes: "xy",
						yvalues: 3,
						formatString:"<table class\'jqplot-highlighter\' ><tr><td>'.__('Date', 'evarisk').'</td><td>%s</td></tr><tr><td>'.__('Niveau', 'evarisk').'</td><td>%d</td></tr><tr><td>'.__('Commentaire', 'evarisk').'</td><td>%s</td></tr></table>"
					},
					cursor:{
						show: false
					}
				});
			});
		</script>';
						}
						else{
							$output .= __('Il n\'y a aucun historique &agrave; afficher pour ce risque', 'evarisk');
						}

						if($reload == ''){
							$output .= '
	</div>';
						}
					}
				}

				echo $output . '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		/*	Add support for the mistake output	*/
		jQuery(".output_mistake").click(function(){
			var output_mistake = "";
			if(jQuery(this).is(":checked")){
				output_mistake = "yes";
			}
			var idProvenance = jQuery(this).attr("id").replace("output_mistake", "");
			digirisk("#histo_risk_container_" + idProvenance).html(digirisk("#loadingImg").html());
			digirisk("#histo_risk_container_" + idProvenance).load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
				"post":"true",
				"tableElement":"' . $tableElement . '",
				"idElement":"' . $idElement . '",
				"nom":"histo-risk",
				"tableProvenance":"' . $tableProvenance . '",
				"idProvenance":idProvenance,
				"output_mistake":output_mistake,
				"reload":"true"
			});
		});
	});
</script>';
			}
			break;

			case "suiviAction" :
				$risques = array();
				$riskList = Risque::getRisque($_REQUEST['idProvenance']);
				if ( $riskList != null ) {
					foreach ( $riskList as $risque ) {
						$risques[$risque->id][] = $risque;
					}
				}
				echo actionsCorrectives::output_correctiv_action_by_risk($risques, '
	"bFilter": false,
	"bPaginate": false,
	"bLengthChange": false,') . '
	<script type="text/javascript">
		digirisk("#pic_line' . ELEMENT_IDENTIFIER_R . $_REQUEST['idProvenance'] . '").click();
	</script>';
			break;
			case "loadPictureAC":
			{
					switch($_REQUEST['act'])
					{
						case 'before':
							$query = $wpdb->prepare(
								"SELECT photo
								FROM " . TABLE_PHOTO . " AS P
									INNER JOIN " . TABLE_ACTIVITE . " AS A ON (A.idPhotoAvant = P.id)
								WHERE A.id = '%s' ",
								$_REQUEST['idProvenance']
							);
							$picture = $wpdb->get_row($query);
							echo '<img src="' . EVA_GENERATED_DOC_URL . $picture->photo . '" alt="picture before corrective action" style="width:40%;" />';
						break;
						case 'after':
							$query = $wpdb->prepare(
								"SELECT photo
								FROM " . TABLE_PHOTO . " AS P
									INNER JOIN " . TABLE_ACTIVITE . " AS A ON (A.idPhotoApres = P.id)
								WHERE A.id = '%s' ",
								$_REQUEST['idProvenance']
							);
							$picture = $wpdb->get_row($query);
							echo '<img src="' . EVA_GENERATED_DOC_URL . $picture->photo . '" alt="picture after corrective action" style="width:40%;" />';
						break;
					}
				}
			break;

			case "suiviFicheAction" :
				$tableElement = $_REQUEST['tableElement'];
				$idElement = $_REQUEST['idElement'];
				$risques = array();
				$riskList = Risque::getRisques($tableElement, $idElement, "Valid");
				if($riskList != null){
					foreach($riskList as $risque){
						$risques[$risque->id][] = $risque;
					}
				}
				echo actionsCorrectives::output_correctiv_action_by_risk($risques);
			break;

			case "reload_new_activity_button_container":
			{
				//Bouton Enregistrer
				$idBouttonEnregistrer = 'update_control';
				$scriptEnregistrementSave = '';

				echo EvaDisplayInput::afficherInput('button', $idBouttonEnregistrer, __('Enregistrer', 'evarisk'), null, '', $idBouttonEnregistrer, false, true, '', 'button-primary', '', '', $scriptEnregistrementSave, 'left') . '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#update_control").click(function(){
			jQuery("#act").val("update_from_external");
			jQuery("#informationGeneralesActivite").submit();
		});
	});
</script>';
			}
			break;

			case 'saveMarkerNewPosition':
			{
				$newPositions = (isset($_REQUEST['positions']) && (trim($_REQUEST['positions']) != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['positions']) : '';
				if(trim($newPositions) != ''){
					$newPositionsList = explode('_pos_separator_', $newPositions);
					foreach($newPositionsList as $newPositionsElement){
						if($newPositionsElement != ''){
							/*	Make different operation on the posted datas	*/
							$positionDefinition = explode('-val-', $newPositionsElement);
							$adressIdentifier = str_replace("adressIdentifier", "", $positionDefinition[0]);
							$positionComponent = explode(", ", str_replace('(', '', str_replace(')', '', $positionDefinition[1])));

							/*	Load the current adress	*/
							$elementAdress = new EvaBaseAddress($adressIdentifier);
							$elementAdress->load();
							$elementAdress->setLatitude($positionComponent[0]);
							$elementAdress->setLongitude($positionComponent[1]);
							$elementAdress->save();
						}
					}
					echo '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		actionMessageShow("#geoloc_message", "<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' alt=\'success\' />' . __('Donn&eacute;es enregistr&eacute;es avec succ&eacute;s', 'evarisk') . '");
		setTimeout(\'actionMessageHide("#geoloc_message")\',5000);
		digirisk("#saveNewPosition").hide();
	});
</script>';
				}
			}
			break;

			/*	Added v5.1.4.8	*/
			case 'hierarchy':{
				switch($_REQUEST['action']){
					case 'load_complete':{
						$selected_element = (isset($_REQUEST['selected']) && (trim($_REQUEST['selected']) != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['selected']) : '';
						$element_to_select = explode('-_-', $selected_element);

						echo '
<table id="complete_hierarchy_table" summary="arborescence societe" cellpadding="0" cellspacing="0" class="widefat post fixed">' .
	arborescence_special::lectureArborescenceRisque(arborescence_special::arborescenceRisque(TABLE_GROUPEMENT, 1), (!empty($element_to_select[0])?$element_to_select[0]:''), (!empty($element_to_select[1])?$element_to_select[1]:0)) . '
</table>';
					}break;
					case 'load_partial':{
						$selected_element = (isset($_REQUEST['selected_element']) && (trim($_REQUEST['selected_element']) != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['selected_element']) : '';
						$element_to_select = explode('-_-', $selected_element);

						echo arborescence_special::display_mini_hierarchy_for_element_affectation($element_to_select[0], $element_to_select[1]);
					}break;
				}
			}break;

			/*	Added v5.1.5.0	*/
			case 'tools':{
				switch($_REQUEST['action']){
					case 'db_manager':{
						/*	Display a list of operation made for the different version	*/
						$plugin_db_modification_content = '';
						$error_nb = 0; $error_list = array();
						$warning_nb = 0; $warning_list = array();
						foreach($digirisk_db_table_operation_list as $plugin_db_version => $plugin_db_modification){
							$plugin_db_modification_content .= '
<div class="tools_db_modif_list_version_number" id="digi_plugin_v_' . $plugin_db_version . '" >
	' . __('Version', 'evarisk') . '&nbsp;' . $plugin_db_version . '
</div>
<div class="tools_db_modif_list_version_details" >
	<ul>';
							foreach($plugin_db_modification as $modif_name => $modif_list){
								switch($modif_name){
									case 'FIELD_ADD':{
										foreach($modif_list as $table_name => $field_list){
											$sub_modif = '  ';
											foreach($field_list as $column_name){
												$query = $wpdb->prepare("SHOW COLUMNS FROM " .$table_name . " WHERE Field = %s", $column_name);
												$columns = $wpdb->get_row($query);
												$sub_modif .= $column_name;
												if( !empty($columns->Field) && ($columns->Field == $column_name) ){
													$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Field has been created', 'evarisk') . '" title="' . __('Field has been created', 'evarisk') . '" class="db_added_field_check" />';
												}
												else{
													$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Field does not exist', 'evarisk') . '" title="' . __('Field does not exist', 'evarisk') . '" class="db_added_field_check" />';
													$error_nb++;
													if ( !empty($error_list[$plugin_db_version]) ) {
														$error_list[$plugin_db_version] += 1;
													}
													else {
														$error_list[$plugin_db_version] = 1;
													}
												}
												$sub_modif .= ' / ';
											}
											$plugin_db_modification_content .= '<li class="added_field" >' . sprintf(__('Added field list for %s', 'evarisk'), $table_name) . '&nbsp;:&nbsp;' .  substr($sub_modif, 0, -2) . '</li>';
										}
									}break;
									case 'FIELD_DROP':{
										foreach($modif_list as $table_name => $field_list){
											$sub_modif = '  ';
											foreach($field_list as $column_name){
												$query = $wpdb->prepare("SHOW COLUMNS FROM " .$table_name . " WHERE Field = %s", $column_name);
												$columns = $wpdb->get_row($query);
												$sub_modif .= $column_name;
												if(empty($columns) || ($columns->Field != $column_name)){
													$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Field has been deleted', 'evarisk') . '" title="' . __('Field has been deleted', 'evarisk') . '" class="db_deleted_field_check" />';
												}
												else{
													$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Field exists', 'evarisk') . '" title="' . __('Field exists', 'evarisk') . '" class="db_deleted_field_check" />';
													$error_nb++;
													$error_list[$plugin_db_version] += 1;
												}
												$sub_modif .= ' / ';
											}
											$plugin_db_modification_content .= '<li class="deleted_field" >' . sprintf(__('Liste des champs supprim&eacute;s pour la table %s', 'evarisk'), $table_name) . '&nbsp;:&nbsp;' .  substr($sub_modif, 0, -2) . '</li>';
										}
									}break;
									case 'FIELD_CHANGE':{
										foreach($modif_list as $table_name => $field_list){
											$sub_modif = '  ';
											foreach($field_list as $field_infos){
												$query = $wpdb->prepare("SHOW COLUMNS FROM " .$table_name . " WHERE Field = %s", $field_infos['field']);
												$columns = $wpdb->get_row($query);
												$what_is_changed = '';
												if(isset($field_infos['type'])){
													$what_is_changed = __('field type', 'evarisk');
													$changed_key = 'type';
													if($columns->Type == $field_infos['type']){
														$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Field has been created', 'evarisk') . '" title="' . __('Field has been created', 'evarisk') . '" class="db_added_field_check" />';
													}
													else{
														$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Field does not exist', 'evarisk') . '" title="' . __('Field does not exist', 'evarisk') . '" class="db_added_field_check" />';
														$error_nb++;
														$error_list[$plugin_db_version] += 1;
													}
													$sub_modif .= sprintf(__('Change %s for field %s to %s', 'evarisk'), $what_is_changed, $field_infos['field'], $field_infos[$changed_key]);
												}
												if(isset($field_infos['original_name'])){
													$what_is_changed = __('field name', 'evarisk');
													$changed_key = 'original_name';
													if($columns->Field == $field_infos['field']){
														$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Field has been created', 'evarisk') . '" title="' . __('Field has been created', 'evarisk') . '" class="db_added_field_check" />';
													}
													else{
														$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Field does not exist', 'evarisk') . '" title="' . __('Field does not exist', 'evarisk') . '" class="db_added_field_check" />';
														$error_nb++;
														$error_list[$plugin_db_version] += 1;
													}
													$sub_modif .= sprintf(__('Change %s for field %s to %s', 'evarisk'), $what_is_changed, $field_infos[$changed_key], $field_infos['field']);
												}
												$sub_modif .= ' / ';
											}
											$sub_modif = substr($sub_modif, 0, -2);
											$plugin_db_modification_content .= '<li class="changed_field" >' . sprintf(__('Updated field list for %s', 'evarisk'), $table_name) . '&nbsp;:&nbsp;' . $sub_modif . '</li>';
										}
									}break;

									case 'DROP_INDEX':{
										foreach($modif_list as $table_name => $field_list){
											$sub_modif = '   ';
											foreach($field_list as $column_name){
												$query = $wpdb->prepare("SHOW INDEX FROM " .$table_name . " WHERE Column_name = %s", $column_name);
												$columns = $wpdb->get_row($query);
												$sub_modif .= $column_name;
												if((empty($columns)) || ($columns->Column_name != $column_name)){
													$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Index has been deleted', 'evarisk') . '" title="' . __('Index has been deleted', 'evarisk') . '" class="db_deleted_index_check" />';
												}
												else{
													$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Index does not exists', 'evarisk') . '" title="' . __('Index does not exists', 'evarisk') . '" class="db_deleted_index_check" />';
													$error_nb++;
													$error_list[$plugin_db_version] += 1;
												}
												$sub_modif .= ' / ';
											}
											$plugin_db_modification_content .= '<li class="deleted_index" >' . sprintf(__('Liste des index supprim&eacute;s pour la table %s', 'evarisk'), $table_name) . '&nbsp;:&nbsp;' .  substr($sub_modif, 0, -3) . '</li>';
										}
									}break;
									case 'ADD_INDEX':{
										foreach($modif_list as $table_name => $field_list){
											$sub_modif = '   ';
											foreach($field_list as $column_name){
												$query = $wpdb->prepare("SHOW INDEX FROM " . $table_name . " WHERE Column_name = %s OR Key_name = %s", $column_name, $column_name);
												$columns = $wpdb->get_row($query);
												$sub_modif .= $column_name;
												if(($columns->Column_name == $column_name) || ($columns->Key_name == $column_name)){
													$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Index has been created', 'evarisk') . '" title="' . __('Index has been created', 'evarisk') . '" class="db_added_index_check" />';
												}
												else{
													$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Index does not exist', 'evarisk') . '" title="' . __('Index does not exist', 'evarisk') . '" class="db_added_index_check" />';
													$error_nb++;
													$error_list[$plugin_db_version] += 1;
												}
												$sub_modif .= ' / ';
											}
											$plugin_db_modification_content .= '<li class="added_index" >' . sprintf(__('Liste des index ajout&eacute;s pour la table %s', 'evarisk'), $table_name) . '&nbsp;:&nbsp;' .  substr($sub_modif, 0, -3) . '</li>';
										}
									}break;

									case 'ADD_TABLE':{
										$sub_modif = '  ';
										foreach($modif_list as $table_name){
											$sub_modif .= $table_name;
											$query = $wpdb->prepare("SHOW TABLES FROM " . DB_NAME . " LIKE %s", $table_name);
											$table_exists = $wpdb->query($query);
											if($table_exists == 1){
												$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Table has been created', 'evarisk') . '" title="' . __('Table has been created', 'evarisk') . '" class="db_table_check" />';
											}
											else{
												$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Table has not been created', 'evarisk') . '" title="' . __('Table has not been created', 'evarisk') . '" class="db_table_check" />';
												$error_nb++;
												if ( !empty($error_list[$plugin_db_version]) ) {
													$error_list[$plugin_db_version] += 1;
												}
												else {
													$error_list[$plugin_db_version] = 1;
												}
											}
											$sub_modif .= ' / ';
										}
										$plugin_db_modification_content .= '<li class="added_table" >' . __('Added table list', 'evarisk') . '&nbsp;:&nbsp;' . substr($sub_modif, 0, -2);
									}break;
									case 'TABLE_RENAME':{
										$sub_modif = '  ';
										foreach($modif_list as $table){
											$sub_modif .= sprintf(__('Table %s renomm&eacute;e en %s', 'evarisk'), $table['old_name'], $table['name']);
											$query = $wpdb->prepare("SHOW TABLES FROM " . DB_NAME . " LIKE %s", $table['name']);
											$table_exists = $wpdb->query($query);
											$query = $wpdb->prepare("SHOW TABLES FROM " . DB_NAME . " LIKE %s", $table['old_name']);
											$old_table_exists = $wpdb->query($query);
											if(($table_exists == 1) && ($old_table_exists == 1)){
												$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Les deux tables sont toujours pr&eacute;sentes', 'evarisk') . '" title="' . __('Les deux tables sont toujours pr&eacute;sentes', 'evarisk') . '" class="db_rename_table_check" />';
												$error_nb++;
												if ( !empty($error_list[$plugin_db_version]) ) {
													$error_list[$plugin_db_version] += 1;
												}
												else {
													$error_list[$plugin_db_version] = 1;
												}
											}
											elseif($table_exists == 1){
												$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Table has been renamed', 'evarisk') . '" title="' . __('Table has been renamed', 'evarisk') . '" class="db_rename_table_check" />';
											}
											else{
												$sub_modif .= '<img src="' . admin_url('images/no.png') . '" alt="' . __('Table has not been renamed', 'evarisk') . '" title="' . __('Table has not been renamed', 'evarisk') . '" class="db_rename_table_check" />';
												$error_nb++;
												if ( !empty($error_list[$plugin_db_version]) ) {
													$error_list[$plugin_db_version] += 1;
												}
												else {
													$error_list[$plugin_db_version] = 1;
												}
											}
											$sub_modif .= ' / ';
										}
										$plugin_db_modification_content .= '<li class="renamed_table" >' . __('Renamed table list', 'evarisk') . '&nbsp;:&nbsp;' . substr($sub_modif, 0, -2);
									}break;
									case 'TABLE_RENAME_FOR_DELETION':{
										$sub_modif = '  ';
										foreach($modif_list as $table){
											$sub_modif .= sprintf(__('Table %s renomm&eacute;e en %s', 'evarisk'), $table['old_name'], $table['name']);
											$query = $wpdb->prepare("SHOW TABLES FROM " . DB_NAME . " LIKE %s", $table['name']);
											$table_delete_exists = $wpdb->query($query);
											$query = $wpdb->prepare("SHOW TABLES FROM " . DB_NAME . " LIKE %s", $table['old_name']);
											$old_table_exists = $wpdb->query($query);
											if(($table_delete_exists == 1) || ($old_table_exists == 1)){
												if($old_table_exists == 1){
													$deleted_table_result = '<img src="' . admin_url('images/no.png') . '" alt="' . __('Table has not been renamed', 'evarisk') . '" title="' . __('Table has not been renamed', 'evarisk') . '" class="db_deleted_table_check" />';
													$error_nb++;
													if ( !empty($error_list[$plugin_db_version]) ) {
														$error_list[$plugin_db_version] += 1;
													}
													else {
														$error_list[$plugin_db_version] = 1;
													}
												}
												else{
// 													$deleted_table_result = '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'warning_vs.gif" alt="' . __('Table has not been deleted', 'evarisk') . '" title="' . __('Table has not been renamed', 'evarisk') . '" class="db_deleted_table_check" />';
// 													$warning_nb++;
// 													if ( !empty($warning_list[$plugin_db_version]) ) {
// 														$warning_list[$plugin_db_version] += 1;
// 													}
// 													else {
// 														$warning_list[$plugin_db_version] = 1;
// 													}
												}
												$sub_modif .= $deleted_table_result;
											}
											else{
												$sub_modif .= '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Table has been deleted', 'evarisk') . '" title="' . __('Table has been deleted', 'evarisk') . '" class="db_deleted_table_check" />';
											}
											$sub_modif .= ' / ';
										}
										$plugin_db_modification_content .= '<li class="renamed_table" >' . __('Liste des tables renomm&eacute;es pour suppression', 'evarisk') . '&nbsp;:&nbsp;' . substr($sub_modif, 0, -2);
									}break;
									case 'DATA_EXPLANATION':
										foreach ( $modif_list as $table_name => $done_modif ) {
											$sub_modif = implode(' / ', $done_modif);
											$plugin_db_modification_content .= '<li class="data_changes" >' . sprintf( __('Modifications apport&eacute;es sur les donn&eacute;es de la table %s', 'evarisk'), $table_name) . '&nbsp;:&nbsp;' . $sub_modif . ' <button class="digi_repair_db_datas_version" id="digi_repair_db_datas_version_' . $plugin_db_version . '" >' . __('Relancer la modifications sur les donn&eacute;es', 'evarisk') . '</button></li>';
										}
										break;
								}
							}
							$plugin_db_modification_content .= '
	</ul>
</div>';
						}

						$db_table_field_error = '';
						foreach($digirisk_db_table as $table_name => $table_definition){
							if(!empty($table_definition)){
								$table_line = explode("
", $table_definition);

								$sub_db_table_field_error = '  ';
								foreach($table_line as $table_definition_line){
									$def_line = trim($table_definition_line);
									if(substr($def_line, 0, 1) == "`"){
										$line_element = explode(" ", $def_line);
										$field_name = str_replace("`", "", $line_element[0]);
										$query = $wpdb->prepare("SHOW COLUMNS FROM " .$table_name . " WHERE Field = %s", $field_name);
										$columns = $wpdb->get_row($query);
										if ( !empty($columns->Field) && ($columns->Field != $field_name)) {
											$sub_db_table_field_error .= $field_name . ', '/*  . ' : <img src="' . admin_url('images/no.png') . '" alt="' . __('Field does not exist', 'evarisk') . '" title="' . __('Field does not exist', 'evarisk') . '" class="db_added_field_check" />' */;
											$error_nb++;
										}
									}
								}
								$sub_db_table_field_error = trim(substr($sub_db_table_field_error, 0, -2));
								if(!empty($sub_db_table_field_error)){
									$db_table_field_error .= sprintf(__('Les champs suivants de la table %s ne sont pas pr&eacute;sents: %s', 'evarisk'), '<span class="bold" >' . $table_name . '</span>', $sub_db_table_field_error) . '<br/>';
								}
							}
						}
						if(!empty($db_table_field_error)){
							$db_table_field_error = '<hr class="clear" />' . $db_table_field_error . '<hr/>';
						}

						/*	Start display	*/
						$plugin_install_error = '<img src="' . admin_url('images/yes.png') . '" alt="' . __('Digirisk install is ok', 'evarisk') . '" title="' . __('Digirisk install is ok', 'evarisk') . '" />&nbsp;' . __('Votre installation du logiciel digirisk ne contient aucune erreur au niveau de la base de donn&eacute;es. Veuillez trouver le d&eacute;tail ci-dessous', 'evarisk') . '<hr/>';
						if($error_nb > 0){
							$plugin_install_error = '<img src="' . admin_url('images/no.png') . '" alt="' . __('Error in digirisk install', 'evarisk') . '" title="' . __('Error in digirisk install', 'evarisk') . '" />&nbsp;' . __('Il y a des erreurs dans votre installation du logiciel digirisk. Veuillez trouver le d&eacute;tail ci-dessous', 'evarisk') . '<br/>
							<ul>';
							foreach($error_list as $version => $element_nb){
								$plugin_install_error .= '<li>' . sprintf(__('Il y a %d erreur(s) &agrave; la version %s', 'evarisk'), $element_nb, '<a href="#digi_plugin_v_' . $version . '" >' . $version . '</a>') . ' - <button id="digi_repair_db_version_' . $version  . '" class="digi_repair_db_version" >' . __('R&eacute;parer', 'evarisk') . '</button></li>';
							}
							$plugin_install_error .= '
							</ul>';
						}
						if($warning_nb > 0){
							$plugin_install_error .= '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'warning_vs.gif" alt="' . __('Warning in digirisk install', 'evarisk') . '" title="' . __('Warning in digirisk install', 'evarisk') . '" />&nbsp;' . __('Des &eacute;l&eacute;ments de votre installation m&eacute;rite votre attention, ceux-ci n\'affectent pas le bon fonctionnement du logiciel. Veuillez trouver le d&eacute;tail ci-apr&egrave;s', 'evarisk') . '<br/>';
							foreach($warning_list as $version => $element_nb){
								$plugin_install_error .= '&nbsp;&nbsp;' . sprintf(__('Il y a %d avertissement(s) &agrave; la version %s', 'evarisk'), $element_nb, '<a href="#digi_plugin_v_' . $version . '" >' . $version . '</a>') . ' - ';
							}
							$plugin_install_error = substr($plugin_install_error, 0, -3) . '<hr/>';
						}

						$max_number = 0;
						foreach($digirisk_update_way as $number => $operation){
							if($number > $max_number){
								$max_number = $number;
							}
						}
						echo $plugin_install_error . sprintf(__('Version de la base de donn&eacute;es - Th&eacute;orique : %d - R&eacute;elle : %d', 'evarisk'), ($max_number+1), digirisk_options::getDbOption('base_evarisk')) . $db_table_field_error . $plugin_db_modification_content;
					}
					break;
					case 'db_reinit':{
						echo digirisk_tools::db_deletion();
					}
					break;
					case 'db_reinit_launch':{
						$output = '';
						$redirect = (isset($_REQUEST['redirect']) && (trim($_REQUEST['redirect']) != '')) ? digirisk_tools::IsValid_Variable($_REQUEST['redirect']) : '';
						$deleted_table = $not_deleted_table = $not_found_table = '  ';
						foreach($digirisk_db_table as $table_name => $table_definition){
							$select_query = $wpdb->prepare("SHOW TABLES LIKE '" . $table_name . "' ", "");
							$table_exist = $wpdb->query($select_query);
							if($wpdb->num_rows == 1){
								$query = $wpdb->prepare("DROP TABLE " . $table_name, "");
								$table_deletion_operation = $wpdb->query($query);
								if($table_deletion_operation){
									$deleted_table .= $table_name . ', ';
								}
								else{
									$not_deleted_table .= $table_name . ', ';
								}
							}
							else{
								$not_found_table .= $table_name . ', ';
							}
						}
						$deleted_table = trim(substr($deleted_table, 0, -2));
						if(!empty($deleted_table))echo sprintf(__('Les table suivantes ont &eacute;&eacute; supprim&eacute;es: %s', 'evarisk'), '<span class="digirisk_table_deleted" >' . $deleted_table . '</span>');
						$not_deleted_table = trim(substr($not_deleted_table, 0, -2));
						if(!empty($not_deleted_table))echo sprintf(__('Les tables suivantes n\'ont pas pu &ecirc;tre supprim&eacute;es: %s', 'evarisk'), '<span class="digirisk_table_not_deleted" >' . $not_deleted_table . '</span>');
						$not_found_table = trim(substr($not_found_table, 0, -2));
						if(!empty($not_found_table))echo sprintf(__('Les tables suivantes n\'ont pas &eacute;t&eacute; trouv&eacute;es: %s', 'evarisk'), '<span class="digirisk_table_not_deleted" >' . $not_found_table . '</span>');

						$digi_delete_options_query = "DELETE FROM " . $wpdb->options . " WHERE option_name LIKE '%digirisk%' ";
						$delete_digi_options = $wpdb->query($digi_delete_options_query);
						if($delete_digi_options){
							$output .= '<br/><br/>' . sprintf(__('Les configurations du logiciel digirisk ont bien &eacute;t&eacute; supprim&eacute;es. %d lignes supprim&eacute;s', 'evarisk'), $wpdb->num_rows);
						}

						if($redirect == 'yes'){
							$output .= __('Vous allez &ecirc;tre redirig&eacute; vers la page d\'installation du logiciel digirisk d\'ici quelques secondes', 'evarisk') . '<hr class="clear" /><script type="text/javascript" >digirisk(document).ready(function(){setTimeout(function(){window.top.location.href = "' . admin_url("admin.php?page=digirisk_installation") . '";}, 2000)});</script>';
						}

						echo $output;
					}
					break;
				}
			}break;

			/*	Added v5.1.5.3	*/
			/*	Plugin configuration part	*/
			case "configuration":{
				switch($_REQUEST['action']){
					case 'evaluation_method':{
						echo digirisk_display::page_content(MethodeEvaluation::evaluation_method_main_page());
					}break;
					case 'danger':{
						echo digirisk_display::page_content(digirisk_danger::danger_main_page());
					}break;
					case 'recommandation':{
						echo digirisk_display::page_content(evaRecommandation::recommandation_main_page());
					}break;
					case 'menu':{
						echo digirisk_display::page_content(digirisk_menu::main_page());
					}break;
				}
			}break;

			/*	Added v5.1.5.4	*/
			case 'digirisk_notification':{
				switch($_REQUEST['act']){
					case 'mark_as_read':{
						$meta_update_result = false;
						$version_number = digirisk_tools::IsValid_Variable($_REQUEST['version']);
						if(!empty($version_number)){

							if(!empty($_REQUEST['for_all_user']) && ($_REQUEST['for_all_user'] == 'true')){
								$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->users, "");
								$user_list = $wpdb->get_results($query);
								foreach($user_list as $user){
									$user_meta_notification_read = get_user_meta($user->ID, 'digirisk_notification', true);
									$user_meta_notification_read['readed_notification'][$version_number] = true;
									$meta_update_result = update_user_meta($user->ID, 'digirisk_notification', $user_meta_notification_read);
								}
							}
							else{
								/*	Update the user readed notice	*/
								$current_user = wp_get_current_user();
								$user_meta_notification_read = get_user_meta($current_user->ID, 'digirisk_notification', true);
								$user_meta_notification_read['readed_notification'][$version_number] = true;
								$meta_update_result = update_user_meta($current_user->ID, 'digirisk_notification', $user_meta_notification_read);
							}
						}

						echo digirisk_admin_notification::admin_message() . '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#digirisk_message_version_' . str_replace('.', '_', $_REQUEST['version']) . '").hide();
	});
</script>';
					}break;
					case 'mark_as_unread':{
						$meta_update_result = false;
						$version_number = digirisk_tools::IsValid_Variable($_REQUEST['version']);
						if(!empty($version_number)){
							/*	Update the user readed notice	*/
							$current_user = wp_get_current_user();
							$user_meta_notification_read = get_user_meta($current_user->ID, 'digirisk_notification', true);
							unset($user_meta_notification_read['readed_notification'][$version_number]);
							$meta_update_result = update_user_meta($current_user->ID, 'digirisk_notification', $user_meta_notification_read);
						}

						echo digirisk_admin_notification::admin_message();
					}break;
				}
			}break;
		}
	}

	//Chargement des meta-boxes
	if(isset($_REQUEST['nomMetaBox'])){
		switch($_REQUEST['nomMetaBox']){
			case 'Geolocalisation':
				$markers = array();
				if($_REQUEST['markers'] != ""){
					foreach($_REQUEST['markers'] as $markerImplode){
						$markerNArray = explode('"; "', stripcslashes($markerImplode));
						for($i=0; $i<count($_REQUEST["keys"]); $i++){
							$markerAArray[$_REQUEST["keys"][$i]] = $markerNArray[$i];
						}
						$markers[] = $markerAArray;
					}
				}
				echo EvaGoogleMaps::getGoogleMap($_REQUEST['idGoogleMapsDiv'], $markers, $_REQUEST['table_element'], $_REQUEST['id_element']);
			break;
		}
	}
}
/*
* Param�tres pass�s en GET
*/
else{
	switch($_REQUEST['nom']){
		case TABLE_GROUPEMENT:
			switch($_REQUEST['act'])
			{
				case 'none':
					switch($_REQUEST['affichage'])
					{
						case "affichageTable":
						case "affichageListe":
							$_REQUEST['affichage'] = $_REQUEST['affichage'];
							require_once(EVA_MODULES_PLUGIN_DIR . 'evaluationDesRisques/partieGaucheEvaluationDesRisques.php');
							echo $script . $partieGauche;
							break;
					}
					break;
			}
			break;
		case TABLE_TACHE:
			switch($_REQUEST['act'])
			{
				case 'none':
					switch($_REQUEST['affichage'])
					{
						case "affichageTable":
						case "affichageListe":
							$_REQUEST['affichage'] = $_REQUEST['affichage'];
							require_once(EVA_MODULES_PLUGIN_DIR . 'evaluationDesRisques/partieGaucheEvaluationDesRisques.php');
							echo $script . $partieGauche;
							break;
					}
					break;
			}
			break;
		case TABLE_GROUPE_QUESTION:
			switch($_REQUEST['act'])
			{
				case 'delete':
					$_REQUEST['idGroupeQuestion'] = $_REQUEST['id'];
					$_REQUEST['act'] = $_REQUEST['act'];
					require_once(EVA_METABOXES_PLUGIN_DIR . 'veilleReglementaire/groupeQuestionPersistance.php');
				break;
			}
			break;
		case TABLE_QUESTION:
			switch($_REQUEST['act']) {
				case 'delete':
					$_REQUEST['idQuestion'] = $_REQUEST['id'];
					$_REQUEST['idGroupeQuestions'] = $_REQUEST['idPere'];
					$_REQUEST['act'] = $_REQUEST['act'];
					require_once(EVA_METABOXES_PLUGIN_DIR . 'veilleReglementaire/questionPersistance.php');
				break;
			}
			break;
	}
}