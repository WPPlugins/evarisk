<?php
/*
 * @version v5.0
 */
	//Postbox definition
	$postBoxTitle = __('R&eacute;capitulatif', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
	$postBoxId = 'postBoxHeaderUniteTravail';
	$postBoxCallbackFunction = 'getHeaderUniteTravailPostBoxBody';
	add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_UNITES_DE_TRAVAIL, 'rightSide', 'default');

	function getHeaderUniteTravailPostBoxBody($arguments)
	{
		$tableElement = $arguments['tableElement'];
		$idElement = $arguments['idElement'];

		include_once(EVA_CONFIG);
		require_once(EVA_LIB_PLUGIN_DIR . 'arborescence.class.php');
		require_once(EVA_LIB_PLUGIN_DIR . 'evaDisplayDesign.class.php');
		require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/groupement/eva_groupement.class.php');
		require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/uniteDeTravail/uniteDeTravail.class.php');
		require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/documentUnique/documentUnique.class.php');
		require_once(EVA_LIB_PLUGIN_DIR . 'risque/Risque.class.php');

		if(((int)$idElement) == 0)
		{
			$script = '<script type="text/javascript">
					digirisk(document).ready(function() {
						digirisk("#postBoxHeaderUniteTravail").hide();
					});
				</script>';
			echo $script;
		}
		else
		{
			$nomUniteTravail = __('Nouvelle unit&eacute; de travail', 'evarisk');
			$responsable = null;
			if($idElement!=null)
			{
				$uniteTravail = eva_UniteDeTravail::getWorkingUnit($idElement);
				$nomUniteTravail = $uniteTravail->nom;
				$groupementPere = EvaGroupement::getGroupement($uniteTravail->id_groupement);
				$responsable = $uniteTravail->id_responsable;

				$scoreRisqueUniteTravail = 0;
				$riskAndSubRisks = eva_documentUnique::listRisk($tableElement, $idElement);
				foreach($riskAndSubRisks as $risk)
				{
					$scoreRisqueUniteTravail += $risk[2]['value'];
				}
				$nombreRisqueUniteTravail = count($riskAndSubRisks);
			}
			else
			{
				$groupementPere = EvaGroupement::getGroupement($argument['idPere']);
			}
			$ancetres = Arborescence::getAncetre(TABLE_GROUPEMENT, $groupementPere);
			$miniFilAriane = __('Hi&eacute;rarchie', 'evarisk') . ' : ';
			foreach($ancetres as $ancetre)
			{
				if($ancetre->nom != "Groupement Racine")
				{
					$miniFilAriane = $miniFilAriane . $ancetre->nom . ' &raquo; ';
				}
			}
			$nomResponsable = '';
			$texteResponsable = __('Responsable', 'evarisk');
			if ( !empty( $responsable ) ) {
				$responsible = evaUser::getUserInformation( $responsable );
				$nomResponsable = ELEMENT_IDENTIFIER_U . $responsable . '&nbsp;-&nbsp;' . $responsible[ $responsable ]['user_lastname'] . ' ' . $responsible[ $responsable ]['user_firstname'];
			}

			if($groupementPere->nom != "Groupement Racine")
				$miniFilAriane = $miniFilAriane . $groupementPere->nom;
			$renduPage =
			'<div id="enTeteDroite">
				<div id="Informations">
					<div id="nomElement" class="titleDiv">';
			$idTitreWU = 'titreWU' . $idElement;
			$workingUnitsNames = eva_UniteDeTravail::getWorkingUnitsName();
			$workingUnitsNames[] = "";

			$valeurActuelleIn = 'digirisk("#' . $idTitreWU . '").val ()in {';
			foreach($workingUnitsNames as $workingUnitName)
			{
				$valeurActuelleIn = $valeurActuelleIn . "'" . addslashes($workingUnitName) . "':'', ";
			}
			$valeurActuelleIn = substr($valeurActuelleIn, 0, strlen($valeurActuelleIn) - 2);
			$valeurActuelleIn = $valeurActuelleIn . "}";
			$idButton = 'validChangeTitre';
			$script = '<script type="text/javascript">
						digirisk(document).ready(function(){
							digirisk("#' . $idButton . '").hide();
							digirisk("#' . $idButton . '").click(function(){
								digirisk("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
								{
									"post": "true",
									"table": "' . TABLE_UNITE_TRAVAIL . '",
									"act": "updateByField",
									"id": ' . $idElement . ',
									"whatToUpdate": "nom",
									"whatToSet": digirisk("#' . $idTitreWU . '").val()
								});
							});
						})
					</script>';
			if(current_user_can('digi_edit_unite') || current_user_can('digi_edit_unite_' . $idElement))
			{
				$renduPage .= EvaDisplayInput::afficherInput('button', 'validChangeTitre', 'Valider', null, null, 'validChangeTitre', false, false, 1,'','','',$script,'',true);
			}
			$script = '<script type="text/javascript">
						digirisk(document).ready(function(){
							digirisk("#' . $idTitreWU . '").focus(function(){
								digirisk(this).select();
								digirisk("#' . $idTitreWU . '").addClass("titleInfoSelected");
							});
							digirisk("#' . $idTitreWU . '").blur(function(){
								if(!digirisk("#' . $idButton . '").is(":visible")){
									digirisk("#' . $idTitreWU . '").removeClass("titleInfoSelected");
								}
							});
							digirisk("#' . $idTitreWU . '").keyup(function(){
								digirisk("#nom_unite_travail").val(digirisk("#' . $idTitreWU . '").val());
								if(digirisk("#nom_unite_travail").val() != ""){
									digirisk("#nom_unite_travail").removeClass("form-input-tip");
								}
								else{
									digirisk("#nom_unite_travail").addClass("form-input-tip");
								}
								if(' . $valeurActuelleIn . '){
									digirisk("#' . $idButton . '").hide();
								}
								else{
									digirisk("#' . $idButton . '").show();
								}
							});
						})
					</script>';
			$renduPage .= '<div class="alignleft element_identifier_recap" >' . ELEMENT_IDENTIFIER_UT . $idElement . '&nbsp;-&nbsp;</div>';
			if(current_user_can('digi_edit_groupement') || current_user_can('digi_edit_groupement_' . $idElement))
			{
				$renduPage .= EvaDisplayInput::afficherInput('text', $idTitreWU, $nomUniteTravail, null, null, $idTitreWU, false, false, 255,'titleInfo', '','', $script, 'left');
			}
			else
			{
				$renduPage .= $nomUniteTravail;
			}
			$locale = preg_replace('/([^_]+).+/', '$1', get_locale());
			$locale = ($locale == 'en') ? '' : $locale;
			$renduPage .= '
					</div>
					<div class="mainInfosDiv">
						<div class="mainInfos1 alignleft" style="width: 68%">
							<p class="">
								<span id="miniFilAriane">' . $miniFilAriane . '</span><br />
								' . $texteResponsable . ' : <strong>' . $nomResponsable . '</strong><br />
							</p>
						</div>
						<div class="mainInfos2 alignleft" style="width: 30%">
							<p>
								<span class="bold" >' . __('Somme des risques', 'evarisk') . '</span>&nbsp;:&nbsp;<span id="riskSum' . $tableElement . $idElement . '" >' . $scoreRisqueUniteTravail . '</span><br/>
								<span class="bold" >' . __('Nombre de risques', 'evarisk') . '</span>&nbsp;:&nbsp;<span id="riskNb' . $tableElement . $idElement . '" >' . $nombreRisqueUniteTravail . '</span>
							</p>
						</div>
					</div>
					<div class="digi-workUnit-extra-actions clear" >
						<div class="digi-workUnit-duplicate-container" style="width:50%;" >
							<div id="digi-workUnit-duplication-message-container" ></div>
							<button name="digi-launch-workUnit-duplication-button" class="button button-secondary" >' . __( 'Dupliquer cette unit&eacute; de travail', 'evarisk' ) . '</button>
							<div class="digirisk_hide" id="digi-duplicate-workUnit-form-container" >
								<form action="' . admin_url( 'admin-ajax.php' ) . '" method="post" id="digi-duplicate-workunit-form" >
									<input type="hidden" name="action" value="digi-duplicate-workUnit" />
									<input type="hidden" name="id_element" value="' . $arguments[ 'idElement' ] . '" />

									<fieldset>
										<legend>' . __( 'Parent de l\'unit&eacute; de travail', 'evarisk' ) . '</legend>
										<div id="digi-workUnit-duplication-tree-container" ><img src="' . admin_url( 'images/loading.gif' ) . '" alt="loading in progress pleas wait" /></div>
									</fieldset>

									<fieldset>
										<legend>' . __( '&Eacute;l&eacute;ments &agrave; copier dans l\'unit&eacute; de travail', 'evarisk' ) . '</legend>

										<label ><input type="checkbox" value="risks" name="duplication-element[]" /> ' . __( 'Dupliquer les risques associés', 'evarisk' ) . '</label>
										<div class="digirisk_hide" id="digi-workUnit-duplication-associated-risks" >
											' . __( 'Date de d&eacute;but du risque', 'evarisk' ) . '<input type="text" class="digi-workUnit-duplication-date" name="duplication-elements[risks][start_date]" value="" />
											<br/>' . __( 'Date de fin du risque', 'evarisk' ) . '<input type="text" class="digi-workUnit-duplication-date" name="duplication-elements[risks][end_date]" value="" />
										</div>
										<br class="clear" />
										<label ><input type="checkbox" value="users" name="duplication-element[]" /> ' . __( 'Dupliquer les personnes associéss', 'evarisk' ) . '</label>
										<div class="digirisk_hide" id="digi-workUnit-duplication-associated-users" >
											' . __( 'Date d\'affectation &agrave; utiliser', 'evarisk' ) . '<input type="text" class="digi-workUnit-duplication-date" name="duplication-elements[users][date]" value="" />
										</div>
										<br class="clear" />
										<label ><input type="checkbox" value="recommandation" name="duplication-element[]" /> ' . __( 'Dupliquer les préconisations associéss', 'evarisk' ) . '</label>
										<div class="digirisk_hide" id="digi-workUnit-duplication-associated-recommandation" ></div>
									</fieldset>

									<div class="alignright" >
										<label class="alignright" ><input type="checkbox" checked="checked" name="wp_digi_auto_redirect_to_new_work_unit" value="autoredirect" />' . __( 'Aller vers la nouvelle unit&eacute; cr&eacute;&eacute;e', 'evarisk' ) . '</label><br/>
										<button class="button button-primary alignright" >' . __( 'Dupliquer cette unit&eacute; de travail', 'evarisk' ) . '</button>
									</div>
								</form>
							</div>
							<script type="text/javascript" >
								jQuery( document ).ready( function(){
									jQuery( "button[name=digi-launch-workUnit-duplication-button]" ).click( function(){
										jQuery( "#digi-duplicate-workUnit-form-container" ).slideDown();
										jQuery( this ).hide();

										jQuery( "#digi-workUnit-duplication-tree-container" ).load( ajaxurl, {
											action: "digi_load_list_groupement",
										});
									});

									jQuery( "#digi-duplicate-workunit-form input[type=checkbox]" ).click( function(){
										if ( jQuery( this ).is( ":checked" ) ) {
											jQuery( "#digi-workUnit-duplication-associated-" + jQuery( this ).val() ).show();
										}
										else {
											jQuery( "#digi-workUnit-duplication-associated-" + jQuery( this ).val() ).hide();
										}
									});

									jQuery(".digi-workUnit-duplication-date").datepicker( jQuery.datepicker.regional["' . $locale . '"] );
									jQuery(".digi-workUnit-duplication-date").datepicker( "option", "dateFormat", "yy-mm-dd");
									jQuery(".digi-workUnit-duplication-date").datepicker( "option", "changeMonth", true);
									jQuery(".digi-workUnit-duplication-date").datepicker( "option", "changeYear", true);
									jQuery(".digi-workUnit-duplication-date").datepicker( "option", "navigationAsDateFormat", true);
									jQuery(".digi-workUnit-duplication-date").datepicker( "setDate", "' . current_time( 'mysql', 0 ) . '" );

									jQuery( "#digi-duplicate-workunit-form" ).ajaxForm({
										dataType: "json",
										success: function( response ){
											jQuery( "#digi-workUnit-duplication-message-container" ).html( response[ "message" ] );
											jQuery( "#digi-duplicate-workunit-form input[type=checkbox]:checked" ).click();

											jQuery("#partieGauche").load(EVA_AJAX_FILE_URL,{
												"post": "true",
												"table": "' . TABLE_UNITE_TRAVAIL . '",
												"act": "edit",
												"id": response[ "new_element_id" ],
												"partie": "left",
												"menu": "risq",
												"affichage": "affichageListe",
												"expanded": response[ "new_element_tree" ]
											});

											if ( response[ "auto_redirect" ] ) {
												jQuery("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
													"post": "true",
													"table": "' . TABLE_UNITE_TRAVAIL . '",
													"act": "edit",
													"id": response[ "new_element_id" ],
													"partie": "right",
													"menu": jQuery("#menu").val(),
													"affichage": "affichageListe",
													"expanded": response[ "new_element_tree" ]
												});
											}
										}
									});

									jQuery( document ).on( "click", ".digi-view-duplicated-workUnit", function( event ){
										event.preventDefault();

										var expanded = reInitTreeTable();
										jQuery("#partieEdition").html(jQuery("#loadingImg").html());
										jQuery("#partieEdition").load(EVA_AJAX_FILE_URL,{
											"post": "true",
											"table": "' . TABLE_UNITE_TRAVAIL . '",
											"act": "edit",
											"id": jQuery( this ).attr( "id" ).replace( "digi-view-duplicated-workUnit-", "" ),
											"partie": "right",
											"menu": "risq",
											"affichage": "affichageListe",
											"expanded": expanded
										});
									});
								});
							</script>
						</div>
					</div>
				</div>
			</div>
			<br class="clear" />';

			echo $renduPage;
		}
	}

?>