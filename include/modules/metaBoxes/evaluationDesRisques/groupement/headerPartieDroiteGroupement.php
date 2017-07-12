<?php
/*
 * @version v5.0
 */
	//Postbox definition
	$postBoxTitle = __('R&eacute;capitulatif', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
	$postBoxId = 'postBoxHeaderGroupement';
	$postBoxCallbackFunction = 'getHeaderGroupementPostBoxBody';
	add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_GROUPEMENTS, 'rightSide', 'default');
	add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_GROUPEMENTS_GESTION, 'rightSide', 'default');

	function getHeaderGroupementPostBoxBody($arguments)
	{
		$tableElement = $arguments['tableElement'];
		$idElement = $arguments['idElement'];

		require_once(EVA_CONFIG);
		require_once(EVA_LIB_PLUGIN_DIR . 'arborescence.class.php');
		require_once(EVA_LIB_PLUGIN_DIR . 'evaDisplayDesign.class.php');
		require_once(EVA_LIB_PLUGIN_DIR . 'evaDisplayInput.class.php');
		require_once(EVA_LIB_PLUGIN_DIR . 'evaGoogleMaps.class.php' );
		require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/groupement/eva_groupement.class.php');
		require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/uniteDeTravail/uniteDeTravail.class.php');
		require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/documentUnique/documentUnique.class.php');
		require_once(EVA_LIB_PLUGIN_DIR . 'risque/Risque.class.php');

		if(((int)$idElement) == 0)
		{
			$script = '<script type="text/javascript">
					digirisk(document).ready(function() {
						digirisk("#postBoxHeaderGroupement").hide();
					});
				</script>';
			echo $script;
		}
		else
		{//En-t�te
			$responsable = null;
			if($idElement!=null)
			{
				$groupement = EvaGroupement::getGroupement($idElement);
				$nomGroupement = $groupement->nom;
				$groupementPere = Arborescence::getPere($tableElement, $groupement);
				$responsable = $groupement->id_responsable;

				$scoreRisqueGroupement = 0;
				$riskAndSubRisks = eva_documentUnique::listRisk($tableElement, $idElement);
				foreach($riskAndSubRisks as $risk)
				{
					$scoreRisqueGroupement += $risk[2]['value'];
				}
				$nombreRisqueGroupement = count($riskAndSubRisks);
			}
			else
			{
				$nomGroupement = __('Nouveau groupement', 'evarisk');
				$groupementPere = EvaGroupement::getGroupement($argument['idPere']);
			}
			$ancetres = Arborescence::getAncetre(TABLE_GROUPEMENT, $groupementPere);
			$miniFilAriane = '';
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
			$renduPage = '<div id="enTeteDroite">
				<div id="Informations">
					<div id="nomElement" class="titleDiv">';
			$idTitreGp = 'titreGp' . $idElement;
			$groupsNames = EvaGroupement::getGroupementsName();
			$groupsNames[] = "";
			$valeurActuelleIn = 'false';
			$valeurActuelleIn = 'digirisk("#' . $idTitreGp . '").val() in {';
			foreach($groupsNames as $groupName)
			{
				$valeurActuelleIn .= "'" . addslashes($groupName) . "':'', ";
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
									"table": "' . TABLE_GROUPEMENT . '",
									"act": "updateByField",
									"id": ' . $idElement . ',
									"whatToUpdate": "nom",
									"whatToSet": digirisk("#' . $idTitreGp . '").val()
								});
							});
						})
					</script>';
			if(current_user_can('digi_edit_groupement') || current_user_can('digi_edit_groupement_' . $idElement))
			{
				$renduPage .= EvaDisplayInput::afficherInput('button', 'validChangeTitre', __('Enregistrer'), null, null, 'validChangeTitre', false, false, 1,'','','',$script,'',true);
			}
			$script = '<script type="text/javascript">
						digirisk(document).ready(function(){
							digirisk("#' . $idTitreGp . '").focus(function(){
								digirisk(this).select();
								digirisk("#' . $idTitreGp . '").addClass("titleInfoSelected");
							});
							digirisk("#' . $idTitreGp . '").blur(function(){
								if(!digirisk("#' . $idButton . '").is(":visible")){
									digirisk("#' . $idTitreGp . '").removeClass("titleInfoSelected");
								}
							});
							digirisk("#' . $idTitreGp . '").keyup(function(){
								digirisk("#nom_groupement").val(digirisk("#' . $idTitreGp . '").val());
								if(digirisk("#nom_groupement").val() != ""){
									digirisk("#nom_groupement").removeClass("form-input-tip");
								}
								else{
									digirisk("#nom_groupement").addClass("form-input-tip");
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
			$renduPage .= '<div class="alignleft element_identifier_recap" >' . ELEMENT_IDENTIFIER_GP . $idElement . '&nbsp;-&nbsp;</div>';
			if(current_user_can('digi_edit_groupement') || current_user_can('digi_edit_groupement_' . $idElement))
			{
				$renduPage .= EvaDisplayInput::afficherInput('text', $idTitreGp, $nomGroupement, null, null, $idTitreGp, false, false, 255,'titleInfo', 'alignright','', $script, 'left');
			}
			else
			{
				$renduPage .= $nomGroupement;
			}
			 $renduPage .= '
					</div>
					<div class="mainInfosDiv">
						<div class="mainInfos1 alignleft" style="width: 68%">
							<p>
								<span id="miniFilAriane">' . __('Hi&eacute;rarchie', 'evarisk') . ' : ' . $miniFilAriane . '</span><br />
								' . $texteResponsable . ' : <strong>' . $nomResponsable . '</strong><br />
							</p>
						</div>
						<div class="alignleft" style="width: 30%">
							<p>
								<span class="bold" >' . __('Somme des risques', 'evarisk') . '</span>&nbsp;:&nbsp;<span id="riskSum' . $tableElement . $idElement . '" >' . $scoreRisqueGroupement . '</span><br/>
								<span class="bold" >' . __('Nombre de risques', 'evarisk') . '</span>&nbsp;:&nbsp;<span id="riskNb' . $tableElement . $idElement . '" >' . $nombreRisqueGroupement . '</span>
							</p>
						</div>
					</div>
				</div>
			</div>
			<br class="clear" />';
			echo $renduPage;
		}

	}
?>