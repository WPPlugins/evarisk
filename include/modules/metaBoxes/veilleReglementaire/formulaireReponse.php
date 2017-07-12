<?php

function getFormulaireReponse($idElement, $tableElement, $summary = false)
{
	require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaGroupeQuestion.class.php' );
	require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaAnswerToQuestion.class.php' );
	
	$chartScript = '<script type="text/javascript" src="' . EVA_INC_PLUGIN_URL . 'js/charts/jquery.jqplot.js" ></script>
		<script type="text/javascript" src="' . EVA_INC_PLUGIN_URL . 'js/charts/jqplot.pieRenderer.min.js" ></script>
		<!--[if IE]>
		<script language="javascript" type="text/javascript" src="' . EVA_INC_PLUGIN_URL . 'js/charts/excanvas.js"></script>
		<![endif]-->';
	$css = '<link rel="stylesheet" type="text/css" href="' . EVA_INC_PLUGIN_URL . 'css/charts/jquery.jqplot.css" />';

	$messageInfo = '';
	$numeroRubrique = "Rubrique 2220";
	$rubrique = evaGroupeQuestions::getGroupeQuestionsByName($numeroRubrique);

	$actualPage = isset($_REQUEST['actualPage']) ? digirisk_tools::IsValid_Variable($_REQUEST['actualPage']) : 1 ;
	if($actualPage == 'null')
	{
		$actualPage = null;
	}
	$actualPageElementNb = isset($_REQUEST['actualPageElementNb']) ? digirisk_tools::IsValid_Variable($_REQUEST['actualPageElementNb']) : 1 ;
	if($actualPageElementNb == 'null')
	{
		$actualPageElementNb = null;
	}
	
	$script = '
		<script type="text/javascript">
			var totalSubmit = false;
			function page(nb)
			{
				digirisk(\'#veilleMainContent\').html(\'<center><img src="' . EVA_IMG_DIVERS_PLUGIN_URL . 'loader.gif" /></center>\');
				digirisk(\'#interractionVeille\').load(\'' . EVA_INC_PLUGIN_URL . 'ajax.php\', {\'post\':\'true\', \'nom\':\'veilleClicPagination\', \'actualPage\':nb, \'tableElement\':\'' . $tableElement . '\',\'idElement\':\'' . $idElement . '\'});
			}
			function summary()
			{
				digirisk(\'#veilleMainContent\').html(\'<center><img src="' . EVA_IMG_DIVERS_PLUGIN_URL . 'loader.gif" /></center>\');
				digirisk(\'#interractionVeille\').load(\'' . EVA_INC_PLUGIN_URL . 'ajax.php\', {\'post\':\'true\', \'nom\':\'veilleClicPagination\', \'actualPage\':\'null\', \'actualPageElementNb\':\'null\', \'act\' : \'summary\', \'tableElement\':\'' . $tableElement . '\',\'idElement\':\'' . $idElement . '\'});
				digirisk(\'#plotLocation\').load(\'' . EVA_INC_PLUGIN_URL . 'ajax.php\', {\'post\':\'true\', \'nom\':\'veilleSummary\', \'tableElement\':\'' . $tableElement . '\',\'idElement\':\'' . $idElement . '\'});
				digirisk(\'#plotLocation\').show();
			}
			
			digirisk(document).ready(function(){
				// digirisk(\'#veille-PDF-creation\').click(function(){
					// digirisk(\'#plotLocation\').hide();
					// digirisk(\'#veilleMainContent\').load(\'' . EVA_INC_PLUGIN_URL . 'ajax.php\', {\'post\':\'true\', \'nom\':\'chargerInfosGeneralesVeille\', \'numeroRubrique\':\'' . $numeroRubrique . '\', \'tableElement\':\'' . $tableElement . '\', \'idElement\':\'' . $idElement . '\'});
				// });
				digirisk(\'#veille-summary\').click(function(){
					summary();
				});
				digirisk(\'#veille-liste-groupe-question\').click(function(){
					digirisk(\'#plotLocation\').hide();
				});
				digirisk(\'#submit-veille\').click(function(){
					totalSubmit = true;
					digirisk(\'.soumissionQuetionVeille\').each(function(){
						digirisk(this).click();
					});
					digirisk(\'#messageInfoVeille\').html(\'<span id="message" class="updated fade below-h2" style="cursor:pointer;" ><strong>&nbsp;Enregistrement en cours...</strong></span>\');
					digirisk(this).ajaxStop(function(){
						setTimeout
						( 
							function(){
								totalSubmit = false;
									setTimeout(function(){digirisk(\'#messageInfoVeille\').html("");},4999);
									setTimeout
									( 
										function(){
											page(parseInt(digirisk(\'#actualPage\').val()) + 1);
										}
										,5000
									);
							}
							,100
						);
					});
					return false;
				});
			});
		</script>';
	function get_title($question_group_id, $stage = 0, $page = null, $element_nb = null)
	{
		$title_list = array();

		$questionGroup = EvaGroupeQuestions::getGroupeQuestions($question_group_id);
		$group_children = Arborescence::getFils(TABLE_GROUPE_QUESTION, $questionGroup, 'code ASC', $page, $element_nb);

		foreach($group_children as $group_key => $group)
		{
			$title_list[$group->id]['CODE'] = $group->code;
			$title_list[$group->id]['NAME'] = nl2br(ucfirst(mb_strtolower($group->nom,'UTF8')));
			$title_list[$group->id]['EXCERPT'] = nl2br($group->extraitTexte);
		}

		return $title_list;
	}

	function output_title($id, $stage = 0, $page = 1)
	{
		$output = '';

		$spacer = '';$total_spacer = 1;
		for($i=0;$i<=$stage;$i++)
		{
			$total_spacer++;
			$spacer .= '&nbsp;';
		}

		$group_fils = get_title($id, $stage);
		if( count($group_fils) > 0 )
		{
			$counter = 1;
			foreach($group_fils as $group_id => $group)
			{
				$response_pic = $onclick = $moreStyle = '';
				
				if($stage == 0)
				{
					if($counter != 1)
					{
						$output .= '<br/>';
					}
					$onclick = ' onclick="javascript:page('. $counter . ');" ';
					if( $counter == $page)
					{
						$moreStyle = ' style="background-color:#DDDDDD;color:#000000;" ';
					}
				}
				$counter++;
				$output .= '<div ' . $moreStyle . '><div class="alignleft" >' . $response_pic . '</div><div id="title'. $group_id . '" ' . $onclick . ' style="display:table;cursor:pointer;" >';
				/* If there is a code we ouput it */
				if( ($group['CODE'] != '') && ($group['CODE'] != '0') )
				{
					$output .= $spacer . $group['CODE'] . '.&nbsp;';
				}
				/* Output the title */
				$output .= '<span >' . $group['NAME'] . '</span><br/>';

				/* Make our function recursiv */
				$next_stage = $stage+1;
				$output .= output_title($group_id, $next_stage, $page);
				$output .= '</div></div>';
			}
		}

		return $output;
	}

	function output_form($id, $stage = 0, $pageEnCours = 1, $elementNumber = 1, $tableElement, $idElement)
	{
		$output = '';
		$next_stage = $stage;

		$spacer = '';$total_spacer = 1;
		for($i=0;$i<=$stage;$i++){$total_spacer++;$spacer .= '&nbsp;';}

		$page = $element_nb = null;
		if( $stage == 0 )
		{
			$page = $pageEnCours;
			$element_nb = $elementNumber;
		}

		$group_fils = get_title($id, $stage, $page, $element_nb);
		if( count($group_fils) > 0 )
		{
			foreach($group_fils as $group_id => $group)
			{
				/* If there is a code we ouput it */
				if( ($group['CODE'] != '') && ($group['CODE'] != '0') )
				{
					$output .= $spacer . $group['CODE'] . '.&nbsp;';
				}
				/* Output the title */
				$output .= '<span class="veille-reponse-titre" >' . $group['NAME'] . '</span><br/>';

				/* If there is an excerpt of the law we output it to the user */
				if( ($group['EXCERPT'] != '') )
				{
					$output .= '<div class="veille-reponse-extraitTexte" >' . $group['EXCERPT'] . '</div>';
				}

				/* Get the question group list to choose wich on we want to work on */
				$questions = EvaGroupeQuestions::getQuestionsDuGroupeQuestions($group_id);
				if( count($questions) > 0 )
				{
					foreach($questions as $question_key => $question)
					{
						$output .= '<div class="veille-response-container" >' . $spacer . $spacer . '-&nbsp;';
						if( ($question->code != '') && ($question->code != '0') )
						{
							$output .= $question->code . '.&nbsp;';
						}
						$output .= '<span class="veille-reponse-question" >&nbsp;' . __('Q (question)', 'evarisk') . $question->id . '&nbsp;:&nbsp;' . nl2br(ucfirst(mb_strtolower($question->enonce,'UTF8'))) . '&nbsp;:</span><br/>';

						/* Get the possible response for the actual question */
						$possible_response = '';
						$possible_response = get_possible_reponse($question->id, $spacer, $tableElement, $idElement);
						if( $possible_response != '')
						{
							$output .= $spacer . '<span class="veille-reponse-response" >' . $possible_response . '</span>';
						}

						$output .= '</div>';
					}
				}

				/* Make our function recursiv */
				$next_stage = $stage+1;
				$output .= output_form($group_id, $next_stage, $pageEnCours, $elementNumber, $tableElement, $idElement);
			}
		}

		return $output;
	}

	function get_possible_reponse($id_question, $spacer, $tableElement, $idElement)
	{
		global $wpdb;
		$response_output = '';
		$id_question = (int) $id_question;
		$sql = 
			"SELECT REPONSE.* 
			FROM " . TABLE_REPONSE . " AS REPONSE 
				INNER JOIN " . TABLE_ACCEPTE_REPONSE . " AS REPONSEACCEPTEE ON (REPONSEACCEPTEE.id_reponse = REPONSE.id)
			WHERE REPONSEACCEPTEE.id_question = '" . ($id_question) . "' ";
		$resultat = $wpdb->get_results( $sql );

		if( count($resultat) > 0 )
		{
			$field_name = $id_question;
			$response_output .= $spacer . $spacer;
			$idReponseALaQuestion = $observationReponseALaQuestion = $valueReponseALaQuestion = $limiteValiditeDeLaQuestion = '';

			if( !isset($_POST['response'][$id_question]) )
			{
				$reponseALaQuestion = EvaAnswerToQuestion::getLatestAnswerByQuestionAndElement($id_question, $tableElement, $idElement);
				//reflechir archivage...
				// if( $reponseALaQuestion->date == date('Y-m-d'))
				// {
					$idReponseALaQuestion = $reponseALaQuestion->id_reponse;
					$valueReponseALaQuestion = $reponseALaQuestion->valeur;
					$observationReponseALaQuestion = $reponseALaQuestion->observation;
					$limiteValiditeDeLaQuestion = $reponseALaQuestion->limiteValidite;
				// }
			}
			else
			{
				$idReponseALaQuestion =  digirisk_tools::IsValid_Variable($_POST['response'][$id_question]['reponse']);
				$observationReponseALaQuestion =  digirisk_tools::IsValid_Variable($_POST['response'][$id_question]['observation']);
				$valueReponseALaQuestion = digirisk_tools::IsValid_Variable( $_POST['response'][$id_question]['value'.$idReponseALaQuestion]);
				$limiteValiditeDeLaQuestion = digirisk_tools::IsValid_Variable( $_POST['response'][$id_question]['limiteValidite']);
			}
			
			foreach($resultat as $response_id => $response)
			{
				$field_id = 'r' . $field_name . '_' . $response->id;
				$moreresponse = '';
				if( $response->min != null AND $response->max != null )
				{
					$script = '<script type="text/javascript">
					digirisk(document).ready(function(){
						digirisk(\'#id' . $field_name . 'Value' . $response->id . '\').keypress(function(event) {
							if (event.which && (event.which < 48 || event.which >57) && event.which != 8) {
								event.preventDefault();
							}
						});
					});</script>';
					$moreresponse = $script . '<input onclick="javascript:digirisk(\'#' . $field_id . '\').click()" type="text" name="response[' . $field_name . '][value' . $response->id . ']" value="' . $valueReponseALaQuestion . '" class="veille-response-input" id="id' . $field_name . 'Value' . $response->id . '"/>';
				}
				$checked = '';
				if( $idReponseALaQuestion == $response->id )
				{
					$checked = ' checked = "checked" ';
				}

				$response_output .= '<span class="veille-response" ><input ' . $checked . ' type="radio" name="response[' . $field_name . '][reponse]" id="' . $field_id . '" value="' . $response->id . '" class="question' . $field_name . '" />' . $moreresponse . '<label for="' . $field_id . '" >' . $response->nom . '</label></span>';
			}
			$locale = preg_replace('/([^_]+).+/', '$1', get_locale());
			$locale = ($locale == 'en') ? '' : $locale;
			$script = '<script type="text/javascript">
				digirisk(document).ready(function(){
					digirisk(\'#limiteValidite' . $field_name . '\').datepicker($.datepicker.regional["' . $locale . '"]);
					digirisk(\'#limiteValidite' . $field_name . '\').datepicker("option", "dateFormat", "yy-mm-dd");
					digirisk(\'#limiteValidite' . $field_name . '\').datepicker("option", "changeMonth", true);
					digirisk(\'#limiteValidite' . $field_name . '\').datepicker("option", "changeYear", true);
					digirisk(\'#limiteValidite' . $field_name . '\').datepicker("option", "navigationAsDateFormat", true);
					digirisk(\'#observation' . $field_name . '\').keyup(function(event) {
						var chaine = digirisk(\'#observation' . $field_name . '\').val();
						if (chaine.length > ' . EVA_MAX_LONGUEUR_OBSERVATIONS . ') 
						{
							digirisk(\'#observation' . $field_name . '\').val(chaine.substring(0, ' . EVA_MAX_LONGUEUR_OBSERVATIONS . '));
							digirisk(\'#observationTropLongue' . $field_name . '\').html(\'Observation trop longue\');
						}
						else
						{
							digirisk(\'#observationTropLongue' . $field_name . '\').html(\'\');
						}
					});
					digirisk(\'#q' . $field_name . '-submit\').click(function() { 
						var reponse = -1;
						var valeur ="";
						for(var i=1; i <= parseInt(' . $response->id . '); i++)
						{
							if(digirisk(\'#r' . $field_name . '_\'+i).is(\':checked\'))
							{
								reponse = i;
								valeur = digirisk(\'#id' . $field_name . 'Value\'+i).val();
								if(valeur == undefined)
								{
									valeur = "";
								}
								else
								{
									if(valeur == "")
									{
										var reponse = -2;
									}
									else
									{
										valeur = parseInt(valeur);
									}
								}
							}
						}
						var observation =	digirisk(\'#observation' . $field_name . '\').val();
						var limiteValidite =	digirisk(\'#limiteValidite' . $field_name . '\').val();
						if(reponse == -1)
						{
							if(!totalSubmit)
							{
								var message = ("' . __('Vous n\'avez pas s&eacute;l&eacute;ctionner de r&eacute;ponse','evarisk') . '");
								alert(digi_html_accent_for_js(message));
							}
						}
						else if(reponse == -2)
						{
							if(!totalSubmit)
							{
								var message = ("' . __('Vous n\'avez pas donner de valeur &agrave; la r&eacute;ponse','evarisk') . '");
								alert(digi_html_accent_for_js(message));
							}
							else
							{
								var message = ("' . __('Vous n\'avez pas donner de valeur &agrave; la r&eacute;ponse &agrave; la question','evarisk') . ' ' . __('Q (question)', 'evarisk') . $field_name . '");
								alert(digi_html_accent_for_js(message));
								statusGlobal = "error";
							}
							digirisk(\'#observationTropLongue' . $field_name . '\').html(\'<div id="message" class="updated fade below-h2"><p><strong><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="noresponse" style="vertical-align:middle;" />&nbsp;La r&eacute;ponse n\\\'a pas pu &ecirc;tre enregistr&eacute;e</strong></p></div>\');
							setTimeout(function(){digirisk(\'#observationTropLongue' . $field_name . '\').html("")},5000);
						}
						else
						{
							var idQuestion = ' . $field_name . ';
							var tableElement = \'' . $tableElement . '\';
							var idElement = ' . $idElement . ';
							soumission = "simple";
							if(totalSubmit)
							{
								soumission = "totale";
							}
							digirisk(\'#observationTropLongue' . $field_name . '\').load(\'' . EVA_INC_PLUGIN_URL . 'ajax.php\', {\'post\':\'true\',\'nom\':\'veilleClicValidation\',\'idQuestion\':idQuestion,\'tableElement\':tableElement,\'idElement\':idElement,\'reponse\':reponse,\'valeur\':valeur,\'observation\':observation,\'soumission\':soumission,\'limiteValidite\':limiteValidite});
						}
						return false;
					});
				});
			</script>';
			if(AFFICHAGE_VALIDATION_PAR_QUESTION)
			{
				$displayValidationParQuestion = 'block';
			}
			else
			{
				$displayValidationParQuestion = 'none';
			}
			$response_output .= '<br/>' . $spacer . $spacer . $spacer . $script .'<span ><label for="observation' . $field_name . '" >' . __('Observations','evarisk') . '&nbsp;:</label><br/>' . $spacer . $spacer . $spacer . '<textarea style="width:60%;" rows="3" cols="150" name="response[' . $field_name . '][observation]" id="observation' . $field_name . '" >' . $observationReponseALaQuestion . '</textarea><span class="observationTropLongue" id="observationTropLongue' . $field_name . '"></span ></span>
			<div>
				<label for="limiteValidite' . $field_name . '" >' . $spacer . $spacer . $spacer . __('Date de limite de validit&eacute; de la r&eacute;ponse','evarisk') . ' : </label><br/>
				' . $spacer . $spacer . $spacer . '<input value="' . $limiteValiditeDeLaQuestion . '" type="text" id="limiteValidite' . $field_name . '" name="response[' . $field_name . '][limiteValidite]">
			</div>
			<div style="display:' . $displayValidationParQuestion . '">
				<input type="submit" name="submit" value="' . __('Valider cette r&eacute;ponse', 'evarisk') . '" id="q' . $field_name . '-submit" class="soumissionQuetionVeille alignright" />
			</div>
			<div class="clear"></div>';
		}

		return $response_output;
	}
	$formVeilleReponse = '';
	if($summary)
	{
		$formVeilleReponse = $chartScript;
	}
	$formVeilleReponse = $formVeilleReponse . $css . $script . '
		<div style="width:79%;float:left;" id="veilleMainContent" >
			<form method="post" name="formulaire_reponse" action="" >
				<input type="hidden" name="nom" id="nom" value="' . $nom . '" />
				<input type="hidden" name="actualPage" id="actualPage" value="' . $actualPage . '" />
				<div id="response"  >';
	$formVeilleReponse = $formVeilleReponse . output_form($rubrique->id, 0, $actualPage, $actualPageElementNb, $tableElement, $idElement);
	$formVeilleReponse = $formVeilleReponse . '</div>
				<hr/>
				<input type="submit" name="submit" value="' . __('Valider le formulaire', 'evarisk') . '" id="submit-veille" class="alignright" />
			</form>
			<div id="messageInfoVeille">
				' . $messageInfo . '
			</div>
		</div>
		<div style="width:19%;float:right;" >
			<ul class="veille-right-menu" >
				<li class="wp-first-item menu-top menu-top-first menu-top-last" id="veille-liste-groupe-question">' . output_title($rubrique->id,0,$actualPage) . '</li>
				<li class="wp-first-item menu-top menu-top-first menu-top-last" id="veille-summary" style="cursor:pointer;" >R&eacute;sulat</li>
				<!-- <li class="wp-first-item menu-top menu-top-first menu-top-last" id="veille-PDF-creation" style="cursor:pointer;" >G&eacute;n&eacute;rer le PDF</li> -->
			</ul>
		</div>';
	return $formVeilleReponse;
}