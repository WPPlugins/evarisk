<?php
/*
 * @version v5.0
 */

//Postbox definition
$postBoxTitle = __('Informations G&eacute;n&eacute;rales', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
$postBoxId = 'postBoxGeneralInformation';
$postBoxCallbackFunction = 'getWorkingUnitGeneralInformationPostBoxBody';
add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_UNITES_DE_TRAVAIL, 'rightSide', 'default');

function getWorkingUnitGeneralInformationPostBoxBody($arguments)
{
	require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/groupement/eva_groupement.class.php' );
	require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/uniteDeTravail/uniteDeTravail.class.php' );
	require_once(EVA_LIB_PLUGIN_DIR . 'arborescence.class.php' );
	require_once(EVA_LIB_PLUGIN_DIR . 'evaDisplayInput.class.php' );
	require_once(EVA_LIB_PLUGIN_DIR . 'evaGoogleMaps.class.php' );
	require_once(EVA_LIB_PLUGIN_DIR . 'adresse/evaAddress.class.php' );

	$id = $arguments['idElement'];
	$idPere = $arguments['idPere'];
	$affichage = $arguments['affichage'];
	$idsFilAriane = $arguments['idsFilAriane'];
	$uniteDeTravail_new = '';

	{//Initializing
		if($id!=null)
		{
			$saveOrUpdate = 'update';
			$uniteTravail = eva_UniteDeTravail::getWorkingUnit($id);
			$saufUniteTravail = $uniteTravail->nom;
			$groupementPere = EvaGroupement::getGroupement($uniteTravail->id_groupement);
			$contenuInputTitre = $uniteTravail->nom;
			$contenuInputResponsable = $uniteTravail->id_responsable;
			$contenuInputDescription = $uniteTravail->description;
			if($uniteTravail->id_adresse != 0 AND $uniteTravail->id_adresse != null)
			{
				$address = new EvaAddress($uniteTravail->id_adresse);
				$address->load();
				$contenuInputLigne1 = $address->getFirstLine();
				$contenuInputLigne2 = $address->getSecondLine();
				$contenuInputCodePostal = $address->getPostalCode();
				$contenuInputVille = $address->getCity();
			}
			else
			{
				$contenuInputLigne1 = '';
				$contenuInputLigne2 = '';
				$contenuInputCodePostal = '';
				$contenuInputVille = '';
			}
			$contenuInputTelephone = $uniteTravail->telephoneUnite;
			if($uniteTravail->effectif != 0)
			{
				$contenuInputEffectif = $uniteTravail->effectif;
			}
			else
			{
				$contenuInputEffectif = '';
			}
			$grise = false;
		}
		else
		{
			$contenuInputTitre = '';
			$contenuInputDescription = '';
			$contenuInputResponsable = '';
			$contenuInputLigne1 = '';
			$contenuInputLigne2 = '';
			$contenuInputCodePostal = '';
			$contenuInputVille = '';
			$contenuInputTelephone = '';
			$contenuInputEffectif = '';
			$saveOrUpdate = 'save';
			$saufUniteTravail = '';
			$groupementPere = EvaGroupement::getGroupement($idPere);
			$grise = true;
		}
	}

	/*	Add dialog box in case that option is activate	*/
	if(digirisk_options::getOptionValue('digi_tree_recreation_dialog', 'digirisk_tree_options') == 'oui')
	{
		$checkRecreate = (digirisk_options::getOptionValue('digi_tree_recreation_default', 'digirisk_tree_options') == 'recreate') ? ' checked="checked" ' : '';
		$checkReactivate = (digirisk_options::getOptionValue('digi_tree_recreation_default', 'digirisk_tree_options') == 'reactiv') ? ' checked="checked" ' : '';
		$uniteDeTravail_new .= '<div id="existingElementDialog" class="hide" title="' . __('&Eacute;l&eacute;ment existant', 'evarisk') . '" ><div class="existingElementMainExplanation" >' . __('Un &eacute;l&eacute;ment portant le nom que vous venez de choisir existe d&eacute;j&agrave;. Vous pouvez d&eacute;cider de l\'action &agrave; effectuer dans la liste ci-dessous.', 'evarisk') . '</div><input type="hidden" name="nameOfElementToActiv" id="nameOfElementToActiv" value="" /><div class="actionForExistingElement" ><input type="radio" id="recreate_gpt" name="actionforexisting_gpt" value="recreate" class="existingElementChoice" ' . $checkRecreate . ' /><label for="recreate_gpt" >' . __('Ignorer l\'existant en recr&eacute;ant une nouvelle unit&eacute;de travail', 'evarisk') . '</label></div><div class="actionForExistingElement" ><input type="radio" id="reactiv_gpt" name="actionforexisting_gpt" value="reactiv" class="existingElementChoice" ' . $checkReactivate . ' /><label for="reactiv_gpt" >' . __('Restaurer l\'anciene unit&eacute;de travail', 'evarisk') . '</label></div><div class="actionForExistingElement" >' . __('Pour changer le nom du nouvel &eacute;l&eacute;ment &agrave; cr&eacute;er, cliquez sur "Annuler"', 'evarisk') . '</div></div>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		digirisk("#existingElementDialog").dialog({
			autoOpen: false, height: 275, width: 500, modal: true,
			buttons:{
				"' . __('Annuler', 'evarisk') . '": function(){ digirisk(this).dialog("close"); },
				"' . __('Valider', 'evarisk') . '": function(){
					var choice = "";
					digirisk(".existingElementChoice").each(function(){ if(digirisk(this).is(":checked")){ choice = digirisk(this).val(); } });
					if(choice != ""){
						if(choice == "recreate"){ createUniteTravail("' . $saveOrUpdate . '", "' . TABLE_UNITE_TRAVAIL . '"); digirisk(this).dialog("close"); }
						else if(choice == "reactiv"){ digirisk("#ajax-response").load(EVA_AJAX_FILE_URL,{ "post": "true",  "table": "' . TABLE_UNITE_TRAVAIL . '", "act": "reactiv_deleted", "nom_unite": digirisk("#nameOfElementToActiv").val() }); }
					}
					else{ alert(digi_html_accent_for_js("' . __('Merci de choisir l\'action &agrave; effectuer', 'evarisk') . '")); }
				}
			},
			close:function(){ digirisk("#nom_unite_travail").focus(); }
		});
	});
</script>';
	}

	$idForm = 'informationGeneralesUT';
	$uniteDeTravail_new .= EvaDisplayInput::ouvrirForm('POST', $idForm, $idForm);
	{//Champs cach�s
		$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherInput('hidden', 'act', '', '', null, 'act', false, false);
		$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherInput('hidden', 'latitude', '', '', null, 'latitude', false, false);
		$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherInput('hidden', 'longitude', '', '', null, 'longitude', false, false);
		$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherInput('hidden', 'affichage', $affichage, '', null, 'affichage', false, false);
		$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherInput('hidden', 'table', TABLE_UNITE_TRAVAIL, '', null, 'table', false, false);
		$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherInput('hidden', 'id', $id, '', null, 'id', false, false);
		$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherInput('hidden', 'idsFilAriane', $idsFilAriane, '', null, 'idsFilAriane', false, false);
	}
	if($id > 0){
		$uniteDeTravail_new .= ELEMENT_IDENTIFIER_UT . $id . '<br/>';
	}
	{//Nom de l'unit�
		$labelInput = ucfirst(strtolower(sprintf(__("nom %s", 'evarisk'), __("de l'unit&eacute; de travail", 'evarisk')))) . " :";
		$labelInput[1] = ($labelInput[0] == "&")?ucfirst($labelInput[1]):$labelInput[1];
		$nomChamps = "nom_unite_travail";
		$idTitre = "nom_unite_travail";
		$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherInput('text', $idTitre, $contenuInputTitre, '', $labelInput, $nomChamps, $grise, true, 255, 'titleInput');
	}
	{//Groupement p�re
		$search = "`Status`='Valid' AND nom<>'Groupement Racine'";
		$order = "nom ASC";
		$groupements = EvaGroupement::getGroupements($search,$order);
		unset($tabGroupement);
		foreach($groupements as $groupement)
		{
			$tabGroupement[] = $groupement->nom;
		}
		if((isset($groupementPere)))
		{
			$selection = $groupementPere->id;
		}
		$nameSelect = "groupementPere";
		$idSelect = "groupementPere";
		$labelSelect = ucfirst(strtolower(sprintf(__("%s p&egrave;re", 'evarisk'), __("groupement", 'evarisk')))) . ' :';
		$labelInput[1] = ($labelInput[0] == "&")?ucfirst($labelInput[1]):$labelInput[1];
		$valeurDefaut = "Aucun";
		$nomRacine = "Groupement Racine";
		$groupementRacine = EvaGroupement::getGroupementByName($nomRacine);
		$uniteDeTravail_new = $uniteDeTravail_new .  '<div style="display:none">';
		$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherComboBoxArborescente($groupementRacine, TABLE_GROUPEMENT, $idSelect, $labelSelect, $nameSelect, $valeurDefaut, $selection);
		$uniteDeTravail_new = $uniteDeTravail_new .  '</div>';
	}
	{//Description
		$labelInput = ucfirst(strtolower(__("Description", 'evarisk'))) . " :";
		$labelInput[1] = ($labelInput[0] == "&")?ucfirst($labelInput[1]):$labelInput[1];
		$idChamps = "description";
		$nomChamps = "description";
		$rows = 5;
		$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherInput('textarea', $idChamps, $contenuInputDescription, '', $labelInput, $nomChamps, $grise, DESCRIPTION_UNITE_TRAVAIL_OBLIGATOIRE, $rows);
	}
	{//Responsable
	$contenuAideDescription = "";
	$labelInput = __("Responsable", 'evarisk') . ' : ';
	$id = $arguments['tableElement'] . 'responsible';
	$nomChamps = "responsable_unite";

	$uniteDeTravail_new .= '<br/><label for="search_user_responsable_' . $arguments['tableElement'] . '" >' . $labelInput . '</label>' . EvaDisplayInput::afficherInput('hidden', $id, $contenuInputResponsable, '', null, $nomChamps, false, false);
	$search_input_state = '';
	$change_input_state = 'hide';
	if($contenuInputResponsable > 0){
		$search_input_state = 'hide';
		$change_input_state = '';
		$responsible = evaUser::getUserInformation($contenuInputResponsable);
		$uniteDeTravail_new .= '<div id="responsible_name" >' . ELEMENT_IDENTIFIER_U . $contenuInputResponsable . '&nbsp;-&nbsp;' . $responsible[$contenuInputResponsable]['user_lastname'] . ' ' . $responsible[$contenuInputResponsable]['user_firstname'];
	}
	else{
		$uniteDeTravail_new .= '<div id="responsible_name" class="hide" >&nbsp;';
	}
	$uniteDeTravail_new .= '</div>&nbsp;<span id="change_responsible_' . $arguments['tableElement'] . 'responsible" class="' . $change_input_state . ' change_ac_responsible" >' . __('Changer', 'evarisk') . '&nbsp;/&nbsp;</span><span id="delete_responsible_' . $arguments['tableElement'] . 'responsible" class="' . $change_input_state . ' delete_ac_responsible" >' . __('Enlever le responsable', 'evarisk') . '</span><input class="searchUserToAffect ac_responsable ' . $search_input_state . '" type="text" name="responsable_name_' . $arguments['tableElement'] . '" id="search_user_responsable_' . $arguments['tableElement'] . '" placeholder="' . __('Rechercher dans la liste des utilisateurs', 'evarisk') . '" /><div id="completeUserList' . $arguments['tableElement'] . 'responsible" class="completeUserList completeUserListActionResponsible hide clear" >' . evaUser::afficheListeUtilisateurTable_SimpleSelection($arguments['tableElement'] . 'responsible', $arguments['idElement']) . '</div>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#search_user_responsable_' . $arguments['tableElement'] . '").click(function(){
			jQuery(".completeUserListActionResponsible").show();
		});
		/*	Autocomplete search	*/
		jQuery("#search_user_responsable_' . $arguments['tableElement'] . '").autocomplete({
			source: "' . EVA_INC_PLUGIN_URL . 'liveSearch/searchUsers.php?table_element=' . $arguments['tableElement'] . '&id_element=' . $arguments['idElement'] . '",
			select: function( event, ui ){
					alert( "'.$id.'" );
				jQuery("#' . $id . '").val(ui.item.value);
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
			jQuery("#responsable_tache").val("");
			jQuery("#responsible_name").html("&nbsp;");
				jQuery("#responsible_name").hide();
			jQuery(this).hide();
			jQuery("#change_responsible_' . $arguments['tableElement'] . 'responsible").hide();
			jQuery("#search_user_responsable_' . $arguments['tableElement'] . '").show();
			jQuery("#completeUserList' . $arguments['tableElement'] . 'responsible").hide();
		});
	});
</script><br class="clear" />';
	}
	{//Adresse
		$uniteDeTravail_new = $uniteDeTravail_new . '<div id="adresseUnite' . $id . '">';
		{//Ligne 1
			$labelInputL1 = ucfirst(strtolower(__("Adresse ligne 1", 'evarisk'))) . " :";
			$labelInputL1[1] = ($labelInputL1[0] == "&")?ucfirst($labelInputL1[1]):$labelInputL1[1];
			$idL1 = "adresse_ligne_1";
			$nomChamps = "adresse_ligne_1";
			$taille = 32;
			$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherInput('text', $idL1, $contenuInputLigne1, '', $labelInputL1, $nomChamps, $grise, ADRESSE_UNITE_TRAVAIL_OBLIGATOIRE, $taille);
		}
		{//Ligne 2
			$labelInputL2 = ucfirst(strtolower(__("Adresse ligne 2", 'evarisk'))) . " :";
			$labelInputL2[1] = ($labelInputL2[0] == "&")?ucfirst($labelInputL2[1]):$labelInputL2[1];
			$idL2 = "adresse_ligne_2";
			$nomChamps = "adresse_ligne_2";
			$taille = 32;
			$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherInput('text', $idL2, $contenuInputLigne2,'',  $labelInputL2, $nomChamps, $grise, false, $taille);
		}
		{//Code Postal
			$labelInputCP = ucfirst(strtolower(__("Code Postal", 'evarisk'))) . " :";
			$labelInputCP[1] = ($labelInputCP[0] == "&")?ucfirst($labelInputCP[1]):$labelInputCP[1];
			$idCP = "code_postal";
			$nomChamps = "code_postal";
			$taille = 5;
			$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherInput('text', $idCP, $contenuInputCodePostal, '', $labelInputCP, $nomChamps, $grise, ADRESSE_UNITE_TRAVAIL_OBLIGATOIRE, $taille, '', 'Number');
		}
		{//Ville
			$labelInputV = ucfirst(strtolower(__("Ville", 'evarisk'))) . " :";
			$labelInputV[1] = ($labelInputV[0] == "&")?ucfirst($labelInputV[1]):$labelInputV[1];
			$idV = "ville";
			$nomChamps = "ville";
			$taille = 32;
			$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherInput('text', $idV, $contenuInputVille, '', $labelInputV, $nomChamps, $grise, ADRESSE_UNITE_TRAVAIL_OBLIGATOIRE, $taille);
		}
		$uniteDeTravail_new = $uniteDeTravail_new . '</div>';
	}
	{//T�l�phone
		$labelInput = ucfirst(strtolower(__("T&eacute;l&eacute;phone", 'evarisk'))) . " :";
		$labelInput[1] = ($labelInput[0] == "&")?ucfirst($labelInput[1]):$labelInput[1];
		$idChamps = "telephone";
		$nomChamps = "telephone";
		$taille = 21;
		$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherInput('text', $idChamps, $contenuInputTelephone, '', $labelInput, $nomChamps, $grise, TELEPHONE_UNITE_TRAVAIL_OBLIGATOIRE, $taille, '', 'Number');
	}
	{//Effectif
		// $labelInput = ucfirst(strtolower(__("&Eacute;ffectif", 'evarisk'))) . " :";
		// $labelInput[1] = ($labelInput[0] == "&")?ucfirst($labelInput[1]):$labelInput[1];
		// $idChamps = "effectif";
		// $nomChamps = "effectif";
		// $taille = 10;
		// $uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherInput('text', $idChamps, $contenuInputEffectif, '', $labelInput, $nomChamps, $grise, EFFECTIF_UNITE_TRAVAIL_OBLIGATOIRE, $taille, '', 'Number');
	}

	if(current_user_can('digi_edit_unite') || current_user_can('digi_edit_unite_' . $arguments['idElement']) || (($saveOrUpdate == 'save') && current_user_can('digi_add_unite_groupement_' . $selection)))
	{//Bouton enregistrer
		$idBouttonEnregistrer = 'save';
		$existingDeletedWU = "false";
		$workingUnitsNames = eva_UniteDeTravail::getWorkingUnitsName($saufUniteTravail, " Status = 'Deleted' ");
		if(count($workingUnitsNames) != 0)
		{
			$existingDeletedWU = "valeurActuelle in {";
			foreach($workingUnitsNames as $workingUnitName)
			{
				$existingDeletedWU .= "'" . addslashes($workingUnitName) . "':'', ";
			}
			$existingDeletedWU .= "}";
		}
		$existingValidWU = "false";
		$workingUnitsNames = eva_UniteDeTravail::getWorkingUnitsName($saufUniteTravail, " Status = 'Valid' ");
		if(count($workingUnitsNames) != 0)
		{
			$existingValidWU = "valeurActuelle in {";
			foreach($workingUnitsNames as $workingUnitName)
			{
				$existingValidWU .= "'" . addslashes($workingUnitName) . "':'', ";
			}
			$existingValidWU .= "}";
		}
		$geolocObligatoire = GEOLOC_OBLIGATOIRE?"true":"false";
		$scriptGeolocalisation = evaGoogleMaps::scriptGeoloc($idBouttonEnregistrer, $id, $idL1, $idL2, $idCP, $idV, "latitude", "longitude");
		{//Script relatif � l'enregistrement
			$scriptEnregistrement = '
			<script type="text/javascript">
				digirisk(document).ready(function() {
					digirisk(\'#' . $idBouttonEnregistrer . '\').click(function() {
						if(digirisk(\'#' . $idTitre . '\').is(".form-input-tip"))
						{
							document.getElementById(\'' . $idTitre . '\').value=\'\';
							digirisk(\'#' . $idTitre . '\').removeClass(\'form-input-tip\');
						}

						if(' . $geolocObligatoire . ' && !geolocPossible)
						{
							var message = "' . ucfirst(sprintf(__('Vous devez obligatoirement remplir les champs de l\'adresse suivant : %s, %s et %s', 'evarisk'), substr($labelInputL1, 0, strlen($labelInputL1)-2),  substr($labelInputCP, 0, strlen($labelInputCP)-2),  substr($labelInputV, 0, strlen($labelInputV)-2))) . '.";
							alert(digi_html_accent_for_js(message));
						}
						else
						{
							valeurActuelle = digirisk(\'#' . $idTitre . '\').val();
							if(valeurActuelle == "")
							{
								var message = "' . ucfirst(sprintf(__('Vous n\'avez pas donn&eacute; de nom %s', 'evarisk'), __('&agrave; l\'unit&eacute; de travail', 'evarisk'))) . '";
								alert(digi_html_accent_for_js(message));
							}
							else
							{
								if(' . $existingDeletedWU . ')
								{
									if("' . digirisk_options::getOptionValue('digi_tree_recreation_dialog', 'digirisk_tree_options') . '" == "oui")
									{
										digirisk("#nameOfElementToActiv").val(valeurActuelle);
										digirisk("#existingElementDialog").dialog("open");
									}
									else if("' . digirisk_options::getOptionValue('digi_tree_recreation_default', 'digirisk_tree_options') . '" == "recreate")
									{
										createUniteTravail("' . $saveOrUpdate . '", "' . TABLE_UNITE_TRAVAIL . '");
									}
									else if("' . digirisk_options::getOptionValue('digi_tree_recreation_default', 'digirisk_tree_options') . '" == "reactiv")
									{
										digirisk("#ajax-response").load(EVA_AJAX_FILE_URL,{ "post": "true",  "table": "' . TABLE_UNITE_TRAVAIL . '", "act": "reactiv_deleted", "nom_groupement": valeurActuelle });
									}
								}
								else if(' . $existingValidWU . ')
								{
									var message = "' . ucfirst(sprintf(__('%s porte d&eacute;j&agrave; ce nom', 'evarisk'), __('une unit&eacute; de travail', 'evarisk'))) . '";
									alert(digi_html_accent_for_js(message));
								}
								else
								{
									createUniteTravail("' . $saveOrUpdate . '", "' . TABLE_UNITE_TRAVAIL . '");
								}
							}
						}
						geolocPossible = true;
					});
				});
			</script>';
		}
		$script = $scriptEnregistrement . $scriptGeolocalisation;
		$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::afficherInput('button', $idBouttonEnregistrer, __('Enregistrer', 'evarisk'), null, '', 'save', false, false, '', 'button-primary alignright', '', '', $script);
	}
	$uniteDeTravail_new = $uniteDeTravail_new . EvaDisplayInput::fermerForm($idForm);
	echo $uniteDeTravail_new;
}
?>