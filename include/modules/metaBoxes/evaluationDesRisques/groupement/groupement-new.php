<?php
/*
 * @version v5.0
 */

//Postbox definition
$postBoxTitle = __('Informations G&eacute;n&eacute;rales', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
$postBoxId = 'postBoxGeneralInformation';
$postBoxCallbackFunction = 'getGroupGeneralInformationPostBoxBody';
add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_GROUPEMENTS_GESTION, 'rightSide', 'default');

function getGroupGeneralInformationPostBoxBody($arguments){
	require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/groupement/eva_groupement.class.php' );
	require_once(EVA_LIB_PLUGIN_DIR . 'arborescence.class.php' );
	require_once(EVA_LIB_PLUGIN_DIR . 'evaDisplayInput.class.php' );
	require_once(EVA_LIB_PLUGIN_DIR . 'evaGoogleMaps.class.php' );
	require_once(EVA_LIB_PLUGIN_DIR . 'adresse/evaAddress.class.php' );

	$postId = $groupement_new = '';
	if($arguments['idElement']!=null)
	{
		$saveOrUpdate = 'update';
		$postId = $arguments['idElement'];
		$groupement = EvaGroupement::getGroupement($arguments['idElement']);
		$saufGroupement = $groupement->nom;
		$contenuInputTitre = $groupement->nom;
		$contenuInputDescription = $groupement->description;
		$contenuInputResponsable = $groupement->id_responsable;
		$contenuInputType = $groupement->typeGroupement;
		$contenuInputSiren = $groupement->siren;
		$contenuInputSiret = $groupement->siret;
		$contenuInputsocial_activity_number = $groupement->social_activity_number;
		$contenuInputcreation_date_of_society = !empty( $groupement->creation_date_of_society ) && ( $groupement->creation_date_of_society != '0000-00-00') ? $groupement->creation_date_of_society : '';
		if($groupement->id_adresse != 0 AND $groupement->id_adresse != null)
		{
			$address = new EvaAddress($groupement->id_adresse);
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
		$contenuInputTelephone = $groupement->telephoneGroupement;
		if($groupement->effectif != 0)
		{
			$contenuInputEffectif = $groupement->effectif;
		}
		else
		{
			$contenuInputEffectif = '';
		}
		$grise = false;
		$groupementPere = Arborescence::getPere(TABLE_GROUPEMENT, $groupement);
	}
	else
	{
		$contenuInputTitre = '';
		$contenuInputResponsable = '';
		$contenuInputDescription = '';
		$contenuInputLigne1 = '';
		$contenuInputLigne2 = '';
		$contenuInputCodePostal = '';
		$contenuInputVille = '';
		$contenuInputTelephone = '';
		$contenuInputEffectif = '';
		$contenuInputType = '';
		$contenuInputSiret = '';
		$contenuInputsocial_activity_number = '';
		$contenuInputcreation_date_of_society = '';
		$saveOrUpdate = 'save';
		$saufGroupement = '';
		$grise = true;
		$groupementPere = EvaGroupement::getGroupement($arguments['idPere']);
	}

	/*	Add dialog box in case that option is activate	*/
	if(digirisk_options::getOptionValue('digi_tree_recreation_dialog', 'digirisk_tree_options') == 'oui')
	{
		$checkRecreate = (digirisk_options::getOptionValue('digi_tree_recreation_default', 'digirisk_tree_options') == 'recreate') ? ' checked="checked" ' : '';
		$checkReactivate = (digirisk_options::getOptionValue('digi_tree_recreation_default', 'digirisk_tree_options') == 'reactiv') ? ' checked="checked" ' : '';
		$groupement_new .= '<div id="existingElementDialog" class="hide" title="' . __('&Eacute;l&eacute;ment existant', 'evarisk') . '" ><div class="existingElementMainExplanation" >' . __('Un &eacute;l&eacute;ment portant le nom que vous venez de choisir existe d&eacute;j&agrave;. Vous pouvez d&eacute;cider de l\'action &agrave; effectuer dans la liste ci-dessous.', 'evarisk') . '</div><input type="hidden" name="nameOfElementToActiv" id="nameOfElementToActiv" value="" /><div class="actionForExistingElement" ><input type="radio" id="recreate_gpt" name="actionforexisting_gpt" value="recreate" class="existingElementChoice" ' . $checkRecreate . ' /><label for="recreate_gpt" >' . __('Ignorer l\'existant en recr&eacute;ant un nouveau groupement', 'evarisk') . '</label></div><div class="actionForExistingElement" ><input type="radio" id="reactiv_gpt" name="actionforexisting_gpt" value="reactiv" class="existingElementChoice" ' . $checkReactivate . ' /><label for="reactiv_gpt" >' . __('Restaurer l\'ancien groupement', 'evarisk') . '</label></div><div class="actionForExistingElement" >' . __('Pour changer le nom du nouvel &eacute;l&eacute;ment &agrave; cr&eacute;er, cliquez sur "Annuler"', 'evarisk') . '</div></div>
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
						if(choice == "recreate"){ createGroupement("' . $saveOrUpdate . '", "' . TABLE_GROUPEMENT . '"); digirisk(this).dialog("close"); }
						else if(choice == "reactiv"){ digirisk("#ajax-response").load(EVA_AJAX_FILE_URL,{ "post": "true",  "table": "' . TABLE_GROUPEMENT . '", "act": "reactiv_deleted", "nom_groupement": digirisk("#nameOfElementToActiv").val() }); }
					}
					else{ alert(digi_html_accent_for_js("' . __('Merci de choisir l\'action &agrave; effectuer', 'evarisk') . '")); }
				}
			},
			close:function(){ digirisk("#nom_groupement").focus(); }
		});
	});
</script>';
	}

	$idForm = 'informationGeneralesGroupement';
	if(isset($arguments['form_id']) && ($arguments['form_id'] != '')){
		$idForm .= $arguments['form_id'];
	}
	$groupement_new .= EvaDisplayInput::ouvrirForm('post', $idForm, $idForm);
	{//Champs cach�s
		$groupement_new .= EvaDisplayInput::afficherInput('hidden', 'act', '', '', null, 'act', false, false);
		$groupement_new .= EvaDisplayInput::afficherInput('hidden', 'latitude', '', '', null, 'latitude', false, false);
		$groupement_new .= EvaDisplayInput::afficherInput('hidden', 'longitude', '', '', null, 'longitude', false, false);
		$groupement_new .= EvaDisplayInput::afficherInput('hidden', 'affichage', $arguments['affichage'], '', null, 'affichage', false, false);
		$groupement_new .= EvaDisplayInput::afficherInput('hidden', 'table', TABLE_GROUPEMENT, '', null, 'table', false, false);
		$groupement_new .= EvaDisplayInput::afficherInput('hidden', 'id', $postId, '', null, 'id', false, false);
		$groupement_new .= EvaDisplayInput::afficherInput('hidden', 'idsFilAriane', $arguments['idsFilAriane'], '', null, 'idsFilAriane', false, false);
	}

	if($postId > 0){
		$groupement_new .= ELEMENT_IDENTIFIER_GP . $postId . '<br/>';
	}

	$contenuAideTitre = $contenuAideDescription = $contenuAideLigne1 = $contenuAideLigne2 = $contenuAideCodePostal = $contenuAideVille = $contenuAideTelephone = $contenuAideEffectif = $contenuAideSiret = $contenuAideSiren = $social_activity_number = $contenuAideType = $contenuInputSiren = $contenuAidesocial_activity_number = $contenuAidecreation_date_of_society = "";
	{//Nom du groupement
		$labelInput = ucfirst(strtolower(sprintf(__("nom %s", 'evarisk'), __("du groupement", 'evarisk')))) . ' :';
		$labelInput[1] = ($labelInput[0] == "&")?ucfirst($labelInput[1]):$labelInput[1];
		$nomChamps = "nom_groupement";
		$idTitre = "nom_groupement";
		$groupement_new .= EvaDisplayInput::afficherInput('text', $idTitre, $contenuInputTitre, '', $labelInput, $nomChamps, $grise, true, 255, 'titleInput');
	}
	{//Responsable
	$contenuAideDescription = "";
	$labelInput = __("Responsable", 'evarisk') . ' : ';
	$id = $arguments['tableElement'] . 'responsible';
	$nomChamps = "responsable_groupement";

	$groupement_new .= '<br/><label for="search_user_responsable_' . $arguments['tableElement'] . '" >' . $labelInput . '</label>' . EvaDisplayInput::afficherInput('hidden', $id, $contenuInputResponsable, '', null, $nomChamps, false, false);
	$search_input_state = '';
	$change_input_state = 'hide';
	if($contenuInputResponsable > 0){
		$search_input_state = 'hide';
		$change_input_state = '';
		$responsible = evaUser::getUserInformation($contenuInputResponsable);
		$groupement_new .= '<div id="responsible_name" >' . ELEMENT_IDENTIFIER_U . $contenuInputResponsable . '&nbsp;-&nbsp;' . $responsible[$contenuInputResponsable]['user_lastname'] . ' ' . $responsible[$contenuInputResponsable]['user_firstname'];
	}
	else{
		$groupement_new .= '<div id="responsible_name" class="hide" >&nbsp;';
	}
	$groupement_new .= '</div>&nbsp;<span id="change_responsible_' . $arguments['tableElement'] . 'responsible" class="' . $change_input_state . ' change_ac_responsible" >' . __('Changer', 'evarisk') . '&nbsp;/&nbsp;</span><span id="delete_responsible_' . $arguments['tableElement'] . 'responsible" class="' . $change_input_state . ' delete_ac_responsible" >' . __('Enlever le responsable', 'evarisk') . '</span><input class="searchUserToAffect ac_responsable ' . $search_input_state . '" type="text" name="responsable_name_' . $arguments['tableElement'] . '" id="search_user_responsable_' . $arguments['tableElement'] . '" placeholder="' . __('Rechercher dans la liste des utilisateurs', 'evarisk') . '" /><div id="completeUserList' . $arguments['tableElement'] . 'responsible" class="completeUserList completeUserListActionResponsible hide clear" >' . evaUser::afficheListeUtilisateurTable_SimpleSelection($arguments['tableElement'] . 'responsible', $arguments['idElement']) . '</div>
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
	{//Type du groupement
		$labelInput = ucfirst(strtolower(sprintf(__("Type %s", 'evarisk'), __("du groupement", 'evarisk')))) . ' :';
		$labelInput[1] = ($labelInput[0] == "&")?ucfirst($labelInput[1]):$labelInput[1];
		$nomChamps = "typeGroupement";
		$idChamps = "typeGroupement";
		$type_input_possible_value = array('' => __('Choisir', 'evarisk'), 'employer' => __('Employeur', 'evarisk'));
		$groupement_new .= '<label for="' . $idChamps . '" >' . __('Type de groupement', 'evarisk') . '</label><br/>' . EvaDisplayInput::createComboBox($idChamps, $nomChamps, $type_input_possible_value, $contenuInputType);
	}
	{//Date de création du groupement
		$nomChamps = "creation_date_of_society";
		$idChamps = "creation_date_of_society";
		$groupement_new .= EvaDisplayInput::afficherInput('text', $idChamps, substr( $contenuInputcreation_date_of_society, 0, -3 ), '', __('Date de cr&eacute;ation', 'evarisk'), $nomChamps, $grise, true, 255, 'titleInput', '', '100%', '', '', true) . '
<span style="font-style: italic;cursor: pointer;" id="date_for_' . $idChamps . '" class="digi_use_current_date_for_gptk" >' . __('Maintenant', 'evarisk') . '</span>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery.datepicker.regional["fr"] = {
			monthNames: ["' . __('Janvier', 'evarisk') . '","' . __('F&eacute;vrier', 'evarisk') . '","' . __('Mars', 'evarisk') . '","' . __('Avril', 'evarisk') . '","' . __('Mai', 'evarisk') . '","' . __('Juin', 'evarisk') . '", "' . __('Juillet', 'evarisk') . '","' . __('Ao&ucirc;t', 'evarisk') . '","' . __('Septembre', 'evarisk') . '","' . __('Octobre', 'evarisk') . '","' . __('Novembre', 'evarisk') . '","' . __('D&eacute;cembre', 'evarisk') . '"],
			monthNamesShort: ["Jan", "Fev", "Mar", "Avr", "Mai", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            dayNames: ["' . __('Dimanche', 'evarisk') . '", "' . __('Lundi', 'evarisk') . '", "' . __('Mardi', 'evarisk') . '", "' . __('Mercredi', 'evarisk') . '", "' . __('Jeudi', 'evarisk') . '", "' . __('Vendredi', 'evarisk') . '", "' . __('Samedi', 'evarisk') . '"],
			dayNamesShort: ["' . __('Dim', 'evarisk') . '", "' . __('Lun', 'evarisk') . '", "' . __('Mar', 'evarisk') . '", "' . __('Mer', 'evarisk') . '", "' . __('Jeu', 'evarisk') . '", "' . __('Ven', 'evarisk') . '", "' . __('Sam', 'evarisk') . '"],
			dayNamesMin: ["' . __('Di', 'evarisk') . '", "' . __('Lu', 'evarisk') . '", "' . __('Ma', 'evarisk') . '", "' . __('Me', 'evarisk') . '", "' . __('Je', 'evarisk') . '", "' . __('Ve', 'evarisk') . '", "' . __('Sa', 'evarisk') . '"],
		}
    	jQuery.datepicker.setDefaults( jQuery.datepicker.regional["fr"] );
		jQuery.timepicker.regional["fr"] = {
                timeText: "' . __('Heure', 'evarisk') . '",
                hourText: "' . __('Heures', 'evarisk') . '",
                minuteText: "' . __('Minutes', 'evarisk') . '",
                amPmText: ["AM", "PM"],
                closeText: "' . __('OK', 'evarisk') . '",
                timeOnlyTitle: "' . __('Choisissez l\'heure', 'evarisk') . '",
                closeButtonText: "' . __('Fermer', 'evarisk') . '",
                deselectButtonText: "' . __('D&eacute;s&eacute;lectionner', 'evarisk') . '",
		}
    	jQuery.timepicker.setDefaults(jQuery.timepicker.regional["fr"]);

		jQuery("#' . $idChamps . '").datetimepicker({
			dateFormat: "yy-mm-dd",
			timeFormat: "hh:mm",
			changeMonth: true,
			changeYear: true,
			navigationAsDateFormat: true,
		});
		jQuery("#digi_risk_date_start").val("' . (!empty($contenuInputcreation_date_of_society) && ($contenuInputcreation_date_of_society != '0000-00-00 00:00:00') ? substr( $contenuInputcreation_date_of_society, 0, -3 ) : '') . '");

		jQuery("#date_for_' . $idChamps . '").click(function(){
			jQuery("#' . $idChamps . '" ).val( "' . substr( current_time('mysql', 0), 0, -3 ) . '" );
		});

	});
</script><br/>';
	}
	{//Groupement p�re
		$search = "`Status`='Valid' AND nom<>'Groupement Racine'";
		$order = "nom ASC";
		$groupements = EvaGroupement::getGroupements($search,$order);
		unset($tabGroupement);
		foreach($groupements as $groupement){
			$tabGroupement[] = $groupement->nom;
		}
		if((isset($groupementPere))){
			$selection = $groupementPere->id;
		}
		$nameSelect = "groupementPere";
		$idSelect = "groupementPere";
		$labelSelect = ucfirst(strtolower(sprintf(__("%s p&egrave;re", 'evarisk'), __("groupement", 'evarisk')))) . ' :';
		$labelInput[1] = ($labelInput[0] == "&")?ucfirst($labelInput[1]):$labelInput[1];
		$valeurDefaut = "Aucun";
		$nomRacine = "Groupement Racine";
		$groupementRacine = EvaGroupement::getGroupementByName($nomRacine);
		$groupement_new .=  '<div style="display:none">';
		$groupement_new .= EvaDisplayInput::afficherComboBoxArborescente($groupementRacine, TABLE_GROUPEMENT, $idSelect, $labelSelect, $nameSelect, $valeurDefaut, $selection);
		$groupement_new .=  '</div>';
	}
	{//Description
		$labelInput = ucfirst(strtolower(__("Description", 'evarisk'))) . " :";
		$labelInput[1] = ($labelInput[0] == "&")?ucfirst($labelInput[1]):$labelInput[1];
		$id = "description";
		$nomChamps = "description";
		$rows = 5;
		$groupement_new .= EvaDisplayInput::afficherInput('textarea', $id, $contenuInputDescription, $contenuAideDescription, $labelInput, $nomChamps, $grise, DESCRIPTION_GROUPEMENT_OBLIGATOIRE, $rows);
	}
	{//Adresse
		$groupement_new .= '<div id="adresseUnite' . $postId . '">';
		{//Ligne 1
			$labelInputL1 = ucfirst(strtolower(__("Adresse ligne 1", 'evarisk'))) . " :";
			$labelInputL1[1] = ($labelInputL1[0] == "&")?ucfirst($labelInputL1[1]):$labelInputL1[1];
			$idL1 = "adresse_ligne_1";
			$nomChamps = "adresse_ligne_1";
			$taille = 32;
			$groupement_new .= EvaDisplayInput::afficherInput('text', $idL1, $contenuInputLigne1, $contenuAideLigne1, $labelInputL1, $nomChamps, $grise, ADRESSE_GROUPEMENT_OBLIGATOIRE, $taille);
		}
		{//Ligne 2
			$labelInputL2 = ucfirst(strtolower(__("Adresse ligne 2", 'evarisk'))) . " :";
			$labelInputL2[1] = ($labelInputL2[0] == "&")?ucfirst($labelInputL2[1]):$labelInputL2[1];
			$idL2 = "adresse_ligne_2";
			$nomChamps = "adresse_ligne_2";
			$taille = 32;
			$groupement_new .= EvaDisplayInput::afficherInput('text', $idL2, $contenuInputLigne2,$contenuAideLigne2,  $labelInputL2, $nomChamps, $grise, false, $taille);
		}
		{//Code Postal
			$labelInputCP = ucfirst(strtolower(__("Code Postal", 'evarisk'))) . " :";
			$labelInputCP[1] = ($labelInputCP[0] == "&")?ucfirst($labelInputCP[1]):$labelInputCP[1];
			$idCP = "code_postal";
			$nomChamps = "code_postal";
			$taille = 5;
			$groupement_new .= EvaDisplayInput::afficherInput('text', $idCP, $contenuInputCodePostal, $contenuAideCodePostal, $labelInputCP, $nomChamps, $grise, ADRESSE_GROUPEMENT_OBLIGATOIRE, $taille, '', 'Number');
		}
		{//Ville
			$labelInputV = ucfirst(strtolower(__("Ville", 'evarisk'))) . " :";
			$labelInputV[1] = ($labelInputV[0] == "&")?ucfirst($labelInputV[1]):$labelInputV[1];
			$idV = "ville";
			$nomChamps = "ville";
			$taille = 32;
			$groupement_new .= EvaDisplayInput::afficherInput('text', $idV, $contenuInputVille, $contenuAideVille, $labelInputV, $nomChamps, $grise, ADRESSE_GROUPEMENT_OBLIGATOIRE, $taille);
		}
		$groupement_new .= '</div>';
	}
	{//T�l�phone
		$labelInput = ucfirst(strtolower(__("T&eacute;l&eacute;phone", 'evarisk'))) . " :";
		$labelInput[1] = ($labelInput[0] == "&")?ucfirst($labelInput[1]):$labelInput[1];
		$id = "telephone";
		$nomChamps = "telephone";
		$taille = 21;
		$groupement_new .= EvaDisplayInput::afficherInput('text', $id, $contenuInputTelephone, $contenuAideType, $labelInput, $nomChamps, $grise, TELEPHONE_GROUPEMENT_OBLIGATOIRE, $taille, '', 'Number');
	}
	{//Metas informations
		{//SIREN
			$labelInput = ucfirst(strtolower(__("Siren", 'evarisk'))) . " :";
			$labelInput[1] = ($labelInput[0] == "&")?ucfirst($labelInput[1]):$labelInput[1];
			$id = "siren";
			$nomChamps = "siren";
			$taille = 9;
			$groupement_new .= EvaDisplayInput::afficherInput('text', $id, $contenuInputSiren, $contenuAideSiren, $labelInput, $nomChamps, $grise, false, $taille, '', '');
		}
		{//SIRET
			$labelInput = ucfirst(strtolower(__("Siret", 'evarisk'))) . " :";
			$labelInput[1] = ($labelInput[0] == "&")?ucfirst($labelInput[1]):$labelInput[1];
			$id = "siret";
			$nomChamps = "siret";
			$taille = 14;
			$groupement_new .= EvaDisplayInput::afficherInput('text', $id, $contenuInputSiret, $contenuAideSiret, $labelInput, $nomChamps, $grise, false, $taille, '', '');
		}
		{//SOCIAL_ACTIVITY_NUMBER
			$labelInput = ucfirst(strtolower(__("Num&eacute;ro de risque S&eacute;curit&eacute; Sociale figurant sur la notification du taux applicable &agrave; l'activit&eacute; dans laquelle est comptabilis&eacute; le salaire de la victime", 'evarisk'))) . " :";
			$labelInput[1] = ($labelInput[0] == "&")?ucfirst($labelInput[1]):$labelInput[1];
			$id = "social_activity_number";
			$nomChamps = "social_activity_number";
			$taille = 255;
			$groupement_new .= EvaDisplayInput::afficherInput('text', $id, $contenuInputsocial_activity_number, $contenuAidesocial_activity_number, $labelInput, $nomChamps, $grise, false, $taille, '', '');
		}
	}
	{//Effectif
		// $labelInput = ucfirst(strtolower(__("&Eacute;ffectif", 'evarisk'))) . " :";
		// $labelInput[1] = ($labelInput[0] == "&")?ucfirst($labelInput[1]):$labelInput[1];
		// $id = "effectif";
		// $nomChamps = "effectif";
		// $taille = 10;
		// $groupement_new .= EvaDisplayInput::afficherInput('text', $id, $contenuInputEffectif, $contenuAideEffectif, $labelInput, $nomChamps, $grise, EFFECTIF_GROUPEMENT_OBLIGATOIRE, $taille, '', 'Number');
	}
	{//	Champs compl�mentaires
		$options = get_option('digirisk_options');
		$user_extra_fields = (!empty($options['digi_users_digirisk_extra_field'])?unserialize($options['digi_users_digirisk_extra_field']):array());
		if(is_array($user_extra_fields) && (count($user_extra_fields) > 0)){
			foreach($user_extra_fields as $field){
				$groupement_new .= EvaDisplayInput::afficherInput('text', 'user_' . $field, $user_meta[0][$field], '', __($field, 'evarisk'), 'digirisk_user_information[' . $field . ']', false, false, 10, 'regular-text', '', '');
			}
		}
	}

	if(current_user_can('digi_edit_groupement') || current_user_can('digi_edit_groupement_' . $arguments['idElement'])  || (($saveOrUpdate == 'save') && current_user_can('digi_add_groupement_groupement_' . $selection)))
	{//Bouton enregistrer
		$idBouttonEnregistrer = 'save';
		$geolocObligatoire = GEOLOC_OBLIGATOIRE ? "true" : "false";
		$scriptGeolocalisation = evaGoogleMaps::scriptGeoloc($idBouttonEnregistrer, $postId, $idL1, $idL2, $idCP, $idV, "latitude", "longitude");

		$groupsNames = EvaGroupement::getGroupementsName($saufGroupement, " Status = 'Deleted' ");
		$existingDeletedGtp = 'false';
		if(count($groupsNames) != 0)
		{
			$existingDeletedGtp = "valeurActuelle in {";
			foreach($groupsNames as $groupName)
			{
				$existingDeletedGtp .= "'" . addslashes($groupName) . "':'', ";
			}
			$existingDeletedGtp .= "}";
		}
		$groupsNames = EvaGroupement::getGroupementsName($saufGroupement, " Status = 'Valid' ");
		$existingValidGtp = 'false';
		if(count($groupsNames) != 0)
		{
			$existingValidGtp = "valeurActuelle in {";
			foreach($groupsNames as $groupName)
			{
				$existingValidGtp .= "'" . addslashes($groupName) . "':'', ";
			}
			$existingValidGtp .= "}";
		}
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
							valeurActuelle = digirisk("#' . $idTitre . '").val();
							if(valeurActuelle == "")
							{
								var message = "' . ucfirst(sprintf(__('Vous n\'avez pas donn&eacute; de nom %s', 'evarisk'), __('au groupement', 'evarisk'))) . '";
								alert(digi_html_accent_for_js(message));
							}
							else
							{
								if(' . $existingDeletedGtp . ')
								{
									if("' . digirisk_options::getOptionValue('digi_tree_recreation_dialog', 'digirisk_tree_options') . '" == "oui")
									{
										digirisk("#nameOfElementToActiv").val(valeurActuelle);
										digirisk("#existingElementDialog").dialog("open");
									}
									else if("' . digirisk_options::getOptionValue('digi_tree_recreation_default', 'digirisk_tree_options') . '" == "recreate")
									{
										createGroupement("' . $saveOrUpdate . '", "' . TABLE_GROUPEMENT . '");
									}
									else if("' . digirisk_options::getOptionValue('digi_tree_recreation_default', 'digirisk_tree_options') . '" == "reactiv")
									{
										digirisk("#ajax-response").load(EVA_AJAX_FILE_URL,{ "post": "true",  "table": "' . TABLE_GROUPEMENT . '", "act": "reactiv_deleted", "nom_groupement": valeurActuelle });
									}
								}
								else if(' . $existingValidGtp . ')
								{
									var message = "' . ucfirst(sprintf(__('%s porte d&eacute;j&agrave; ce nom', 'evarisk'), __('un groupement', 'evarisk'))) . '";
									alert(digi_html_accent_for_js(message));
								}
								else
								{
									createGroupement("' . $saveOrUpdate . '", "' . TABLE_GROUPEMENT . '");
								}
							}
						}
						geolocPossible = true;
					});
				});
				</script>';
		}
		$script = $scriptEnregistrement . $scriptGeolocalisation;
		if(!isset($arguments['dont_display_button']) || (isset($arguments['dont_display_button']) && ($arguments['dont_display_button'] == 'no'))){
			$groupement_new .= EvaDisplayInput::afficherInput('button', $idBouttonEnregistrer, __('Enregistrer', 'evarisk'), null, '', 'save', false, false, '', 'button-primary alignright', '', '', $script);
		}
	}
	$groupement_new .= EvaDisplayInput::fermerForm($idForm);

	echo $groupement_new;
}

?>