<?php
/**
 *
 * @author Evarisk
 * @version v5.0
 */

class eva_Variable {

	/**
	 * @var Integer The variable identifier
	 */
	var $id;
	/**
	 * @var String The variable name
	 */
	var $name;
	/**
	 * @var Integer The variable minimum
	 */
	var $min;
	/**
	 * @var Integer The variable maximum
	 */
	var $max;
	/**
	 * @var String The variable annotation
	 */
	var $annotation;

/*
 *	Constructeur et accesseurs
 */

	/**
	 * Constructor of the variable class
	 * @param $id Integer The identifier to setI
	 * @param $name String The name to set
	 * @param $min Integer The minimum to set
	 * @param $max Integer The maximum to set
	 * @param $annotation String The annotation to set
	 */
	function EvaVariable($id = NULL, $name = '', $min = '', $max = '', $annotation = '') {
		$this->id = $id;
		$this->name = $name;
		$this->min = $min;
		$this->max = $max;
		$this->annotation = $annotation;
	}

	/**
	 * Return the variable identifier
	 * @return Integer The identifier
	 */
	function getId()
	{
		return $this->id;
	}
	/**
	 * Set the variable identifier
	 * @param $id Integer The identifier to set
	 */
	function setId($id)
	{
		$this->id = $id;
	}
	/**
	 * Return the variable name
	 * @return String The name
	 */
	function getName()
	{
		return $this->name;
	}
	/**
	 * Set the variable name
	 * @param $name String The name to set
	 */
	function setName($name)
	{
		$this->name = $name;
	}
	/**
	 * Return the variable minimum
	 * @return $min Integer The minimum
	 */
	function getMin($min)
	{
		return $this->min;
	}
	/**
	 * Set the variable minimum
	 * @param $min Integer The minimum to set
	 */
	function setMin($min)
	{
		$this->min = $min;
	}
	/**
	 * Return the variable maximum
	 * @return $max String The maximum
	 */
	function getMax($max)
	{
		return $this->max;
	}
	/**
	 * Set the variable maximum
	 * @param $max String The maximum to set
	 */
	function setMax($max)
	{
		$this->max = $max;
	}
	/**
	 * Return the variable annotation
	 * @return $annotation String The annotation
	 */
	function getAnnotation($annotation)
	{
		return $this->annotation;
	}
	/**
	 * Set the variable annotation
	 * @param $annotation String The annotation to set
	 */
	function setAnnotation($annotation)
	{
		$this->annotation = $annotation;
	}

/*
 * Autres variables
 */
	function getVariable($id)
	{
		global $wpdb;
		$id = (int) $id;
		$t = TABLE_VARIABLE;
		return $wpdb->get_row( "SELECT * FROM {$t} WHERE id = " . $id);
	}

	function getVariables($where = "1", $order = "id ASC")
	{
		global $wpdb;
		$t = TABLE_VARIABLE;
		$resultat = $wpdb->get_results( "SELECT * FROM {$t} WHERE " . $where . " ORDER BY " . $order);
		return $resultat;
	}

	function getValeurAlternative($idVariable, $valeur, $date = ""){
		global $wpdb;

		if(empty($date)){
			$date = current_time('mysql', 0);
		}

		$sql = "
			SELECT *
			FROM " . TABLE_VALEUR_ALTERNATIVE . " tva1
			WHERE tva1.id_variable = " . $idVariable . "
			AND tva1.valeur = " . $valeur . "
			AND tva1.date < '" . $date . "'
			AND NOT EXISTS
			(
				SELECT *
				FROM " . TABLE_VALEUR_ALTERNATIVE . " tva2
				WHERE tva2.id_variable = " . $idVariable . "
				AND tva2.valeur = " . $valeur . "
				AND tva2.date < '" . $date . "'
				AND tva2.date > tva1.date
			)
			";
		$resultat = $wpdb->get_row($sql);
		if($resultat != null){
			$valeurAlternative = $resultat->valeurAlternative;
		}
		else{
			$valeurAlternative = $valeur;
		}

		return $valeurAlternative;
	}

	function create_basic_variable(){
		global $evaluation_main_vars, $wpdb;

		foreach($evaluation_main_vars as $var_index => $var_definition){
			$var_content = array();
			foreach($var_definition as $field_name => $field_value){
				$var_content[$field_name] = $field_value;
			}
			$wpdb->insert(TABLE_VARIABLE, $var_content);
		}
	}

		/**
	*	Create a listing of existing vars
	*
	*	@return string The complete html output with the existing var list
	*/
	function existing_var_output(){
		unset($titres, $classes, $lignesDeValeurs, $idLignes);

		$idTable = 'tableVariable';
		$titres[] = __('Id.', 'evarisk');
		$titres[] = __('Nom', 'evarisk');
		$titres[] = __('Min', 'evarisk');
		$titres[] = __('Max', 'evarisk');
		$titres[] = __('Annotation', 'evarisk');
		$titres[] = '';
		$classes[] = 'variableIdentifier';
		$classes[] = 'variableName';
		$classes[] = 'variableMin';
		$classes[] = 'variableMax';
		$classes[] = 'variableAnnotation';
		$classes[] = 'variableAction';

		$variables = eva_Variable::getVariables("Status = 'Valid'");
		foreach($variables as $variable){
			unset($ligneDeValeurs);
			$idLigne = 'eval-method-var-' . $variable->id;
			$idLignes[] = $idLigne;
			$ligneDeValeurs[] = array('value' => ELEMENT_IDENTIFIER_V . $variable->id, 'class' => '');
			$ligneDeValeurs[] = array('value' => $variable->nom, 'class' => '');
			$ligneDeValeurs[] = array('value' => $variable->min, 'class' => '');
			$ligneDeValeurs[] = array('value' => $variable->max, 'class' => '');
			$ligneDeValeurs[] = array('value' => nl2br(str_replace('\n', '<br/>', $variable->annotation)), 'class' => '');
			$actions = '';
			if(current_user_can('digi_delete_method_var')){
				$actions .= '<img id="delete_var_' . $variable->id . '" class="delete-eval-method-var alignright" src="' . str_replace('.png', '_vs.png', PICTO_DELETE) . '" alt="' . __('Supprimer la variable', 'evarisk') . '" title="' . __('Supprimer la variable', 'evarisk') . '" />';
			}
			if(current_user_can('digi_edit_method_var')){
				$actions .= '<img id="edit_var_' . $variable->id . '" class="edit-eval-method-var alignright" src="' . str_replace('.png', '_vs.png', PICTO_EDIT) . '" alt="' . __('&Eacute;diter la variable', 'evarisk') . '" title="' . __('&Eacute;diter la variable', 'evarisk') . '" />';
			}
			$ligneDeValeurs[] = array('value' => $actions, 'class' => '');
			$lignesDeValeurs[] = $ligneDeValeurs;
		}

		$scriptTable = '
<script type="text/javascript">
	digirisk(document).ready(function(){
		jQuery("#' . $idTable . '").dataTable({
			"sPaginationType": "full_numbers",
			"bAutoWidth": false,
			"aoColumns": [
				{ "bSortable": false },
				{ "bSortable": true, "sType": "html" },
				{ "bSortable": false },
				{ "bSortable": false },
				{ "bSortable": false },
				{ "bSortable": false }],
			"aaSorting": [[0,"asc"]],
			"oLanguage":{
				"sUrl": "' . EVA_INC_PLUGIN_URL . 'js/dataTable/jquery.dataTables.common_translation.txt"
			}
		});

		jQuery(".delete-eval-method-var").click(function(){
			if(confirm(digi_html_accent_for_js("' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer cette variable?', 'evarisk') . '"))){
				var var_id = jQuery(this).attr("id").replace("delete_var_", "");
				jQuery("#ajax-response").load(EVA_AJAX_FILE_URL,{
					"post": "true",
					"table": "' . TABLE_VARIABLE . '",
					"act": "delete_var",
					"id": var_id
				});
			}
		});
		jQuery(".edit-eval-method-var").click(function(){
			jQuery("#evaluation_method_form_container").dialog("open");
			jQuery("#evaluation_method_form_container").html(evarisk("#loadingImg").html());
			jQuery("#evaluation_method_form_container").load(EVA_AJAX_FILE_URL,{
				"post": "true",
				"table": "' . TABLE_VARIABLE . '",
				"act": "load_variable_management_form",
				"id": jQuery(this).attr("id").replace("edit_var_", "")
			});
		});
	});
</script>';

		return '
<div class="digirisk_hide fade below-h2 evaMessage" id="var_management_message" >&nbsp;</div>
<div>
	<div class="digirisk_hide" id="evaluation_method_form_container" title="" >&nbsp;</div>
	<h2 class="" >' . __('Variables des m&eacute;thodes d\'&eacute;valuation', 'evarisk') . '<input type="button" value="' . __('Ajouter une nouvelle variable', 'evarisk') . '" id="add_new_var" class="middleAlign button-secondary" /></h2>
</div>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#evaluation_method_form_container").dialog({
			autoOpen: false,
			modal: true,
			width: 500,
			height: 400,
			close: function(){
				jQuery(this).html("");
			}
		});

		jQuery("#add_new_var").click(function(){
			jQuery("#evaluation_method_form_container").dialog("open");
			jQuery("#evaluation_method_form_container").html(evarisk("#loadingImg").html());
			jQuery("#evaluation_method_form_container").load(EVA_AJAX_FILE_URL,{
				"post": "true",
				"table": "' . TABLE_VARIABLE . '",
				"act": "load_variable_management_form"
			});
		});
	});
</script>
' . EvaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $scriptTable);
	}


	/**
	*	Define the form allowing to create or edit a evaluation method var
	*
	*	@param string|integer $var_id Optionnal. The var identifier to edit. If empty when form will be send a new var will be created
	*
	*	@return mixed $var_form The complet html form output for var creation/edition
	*/
	function var_edition_form($var_id = ''){
		global $wpdb;
		$var_form = '';

		$var_name = $var_minimum = $var_maximum = $var_annotation = '';
		$save_button_value = __('Ajouter', 'evarisk');
		if(($var_id != '') && ($var_id > 0)){
			$var_information = eva_Variable::getVariable($var_id);

			$var_name = $var_information->nom;
			$var_minimum = $var_information->min;
			$var_maximum = $var_information->max;
			$var_annotation = str_replace('\n', "", $var_information->annotation);
			$var_typeAffichage = $var_information->affichageVar;
			$var_question = $var_information->questionTitre;
			$query = $wpdb->prepare("SELECT * FROM " . TABLE_VALEUR_ALTERNATIVE . " WHERE id_variable = %d and Status = %s", $var_id, 'Valid');
			$existing_alternativ_vars = $wpdb->get_results($query);

			$save_button_value = __('Enregistrer', 'evarisk');
		}
        else {
        	$var_typeAffichage = "slide";
        }

		$idInputNom = 'newvarname';
		$idInputMin = 'newvarmin';
		$idInputMax = 'newvarmax';

		$var_form .= '
<script type="text/javascript" >
	function load_alternativ_value_for_var() {
		if(jQuery("#checkValues").is(":checked") && (jQuery("#'. $idInputMin . '").val() != "") && (jQuery("#'. $idInputMax . '").val() != "")){
			jQuery("#digi_alternativ_value_for_vars").html(jQuery("#loadingImg").html());
			jQuery("#digi_alternativ_value_for_vars").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
				"post":"true",
				"nom":"loadFieldsNewVariable",
				"min":jQuery("#'. $idInputMin . '").val(),
				"max":jQuery("#'. $idInputMax . '").val(),
				"choixTypeAffichage": jQuery("#typeVar").val(),
				"var_id":"' . $var_id . '"
			});
		}
	}

	function validate_var_form(formData, jqForm, options){
		/*	re-initialise field state	*/
		evarisk("#' . $idInputNom . '").removeClass("ui-state-error");
		evarisk("#' . $idInputMin . '").removeClass("ui-state-error");
		evarisk("#' . $idInputMax . '").removeClass("ui-state-error");

		for (var i=0; i < formData.length; i++) {
			if((formData[i].name == "' . $idInputNom . '") && !formData[i].value) {
				checkLength( evarisk("#' . $idInputNom . '"), "", 1, 255, "' . __('Le champs nom de la variable doit contenir entre !#!minlength!#! et !#!maxlength!#! caract&egrave;res', 'evarisk') . '" , evarisk(".var_form_error_message"))
				return false;
			}
			else if((formData[i].name == "' . $idInputMin . '") && !formData[i].value) {
				checkLength( evarisk("#' . $idInputMin . '"), "", 1, 255, "' . __('Le champs nom de la variable doit contenir entre !#!minlength!#! et !#!maxlength!#! caract&egrave;res', 'evarisk') . '" , evarisk(".var_form_error_message"))
				return false;
			}
			else if((formData[i].name == "' . $idInputMax . '") && !formData[i].value) {
				checkLength( evarisk("#' . $idInputMax . '"), "", 1, 255, "' . __('Le champs nom de la variable doit contenir entre !#!minlength!#! et !#!maxlength!#! caract&egrave;res', 'evarisk') . '" , evarisk(".var_form_error_message"))
				return false;
			}
		}

		if(parseFloat(evarisk("#' . $idInputMin . '").val()) > parseFloat(evarisk("#' . $idInputMax . '").val())){
			evarisk("#' . $idInputMin . '").addClass( "ui-state-error" );
			evarisk("#' . $idInputMax . '").addClass( "ui-state-error" );
			updateTips( digi_html_accent_for_js("' . __('La valeur maximale doit &ecirc;tre sup&eacute;rieure &agrave; la valeur minimale.', 'evarisk') . '"), evarisk(".var_form_error_message"));

			return false;
		}

		return true;
	}
	digirisk(document).ready(function(){
		jQuery("#var_editor").ajaxForm({
			target: "#ajax-response",
			beforeSubmit: validate_var_form
		});
		jQuery("#typeVar").val("'.$var_typeAffichage.'");
		jQuery("#varQuestion").hide();
		if ("'.$var_typeAffichage.'" === "checkbox") {
			jQuery("#varQuestion").show();
			jQuery("#checkValues").prop("checked", true);
			jQuery("#digi_alternativ_value_for_vars").toggleClass("hide-if-js");
			load_alternativ_value_for_var();
		}

		digirisk(".choixTypeAffichage").live ("change", function(){
			jQuery("#typeVar").val(jQuery(this).val());
			if (jQuery(this).val() == "checkbox") {
				jQuery("#varQuestion").show();
				jQuery("#digi_alternativ_value_for_vars").toggleClass("hide-if-js");
				jQuery("#checkValues").prop("checked", true);
				load_alternativ_value_for_var();
			}
			else {
				jQuery("#varQuestion").hide();
				load_alternativ_value_for_var();
			}
		});
	});
</script>
<p class="var_form_error_message digirisk_hide">&nbsp;</p>
<form method="post" action="' . EVA_INC_PLUGIN_URL . 'ajax.php" id="var_editor" >
	<input type="hidden" name="post" id="post" value="true" />
	<input type="hidden" name="table" id="table" value="' . TABLE_VARIABLE . '" />
	<input type="hidden" name="act" id="act" value="save" />
	<input type="hidden" name="id_variable" id="id_variable" value="' . $var_id . '" />';

		{//New variable name
			$nomChamps = 'newvarname';
			$labelInput = __('Nom de la nouvelle variable : ', 'evarisk');
			$var_form .= EvaDisplayInput::afficherInput('text', $idInputNom, $var_name, '', $labelInput, $nomChamps, true, false, 100);
		}
		{//New variable minimum
			$nomChamps = 'newvarmin';
			$labelInput = __('Minimum de la nouvelle variable : ', 'evarisk');
			$var_form .= EvaDisplayInput::afficherInput('text', $idInputMin, $var_minimum, '', $labelInput, $nomChamps, true, false, 100,'','Number');
		}
		{//New variable maximum
			$nomChamps = 'newvarmax';
			$labelInput = __('Maximum de la nouvelle variable : ', 'evarisk');
			$var_form .= EvaDisplayInput::afficherInput('text', $idInputMax, $var_maximum, '', $labelInput, $nomChamps, true, false, 100,'','Number');
		}
		{//New variable description
			$idInput = 'newvarannotation';
			$nomChamps = 'newvarannotation';
			$labelInput = __('Annotation sur la nouvelle variable : ', 'evarisk');
			$var_form .= EvaDisplayInput::afficherInput('textarea', $idInput, $var_annotation, '', $labelInput, $nomChamps, true, false, 5);
		}
		{//Variable's display method choice
			$checked['slide'] = ' checked="checked"';
			$checked['checkbox'] = '';
			if (!empty($var_typeAffichage) ) {
				$checked[$var_typeAffichage] = ' checked="checked"';
			}
            $form = '
            	<input type="radio" name="methodeAffichage" id="methodeAffichage_slide" class="choixTypeAffichage" value="slide"' . $checked['slide'] . ' > <label for="methodeAffichage_slide" >'.__('Slide ', 'evarisk').'</label>
            	<input type="radio" name="methodeAffichage" id="methodeAffichage_checkbox" class="choixTypeAffichage" value="checkbox"' . $checked['checkbox'] . ' > <label for="methodeAffichage_checkbox" >' .__('Checkbox ', 'evarisk').'</label>';
			$var_form .= __('Type d\'affichage de la variable : ', 'evarisk').$form.'<br/><br/>';
		}
        {//Variable question
			$idInputQuestion = 'newvarquestion';
			$nomChampsQuestion = 'newvarquestion';
			$labelInput = __('Question de la variable : ', 'evarisk');

                        $var_form .= '<div id="varQuestion">';

			$var_form .= EvaDisplayInput::afficherInput('text', $idInputQuestion, $var_question, '', $labelInput, $nomChampsQuestion, true, false, 100);
                        // Javascript actions to keep an eye on the variable's display method choice
                        $var_form .='<input type="hidden" id="typeVar" /></div>';
		}

		{//New variable discreet values
			$idInput = 'checkValues';
			$nomChamps = 'checkValues';
			$script = '
				<script type="text/javascript">
					digirisk(document).ready(function(){
						jQuery("#'. $idInput . '").click(function(){
							jQuery("#digi_alternativ_value_for_vars").toggleClass("hide-if-js");
							load_alternativ_value_for_var();
						});
						jQuery("#'. $idInputMin . '").blur(function(){
							load_alternativ_value_for_var();
						});
						jQuery("#'. $idInputMax . '").blur(function(){
							jQuery("#'. $idInputMin . '").blur();
						});';
			if(!empty($existing_alternativ_vars)){
					$script .= '
						jQuery("#'. $idInput . '").prop("checked", true);
						jQuery("#digi_alternativ_value_for_vars").toggleClass("hide-if-js");
						load_alternativ_value_for_var();';
			}
			$script .= '
					});

				</script>';
			$labelInput = __('Valeur diff&eacute;rente du chiffre', 'evarisk');
			$var_form .= EvaDisplayInput::afficherInput('checkbox', $idInput, 'variable_has_alternative_value', '', $labelInput, $nomChamps, true, false, 1, '', '', '3%',$script) . '<div class="hide-if-js" id="digi_alternativ_value_for_vars">&nbsp;</div>';
		}

		{//New variable submit button
			$idButton = "AjouterVariable";
			$script = '';
			$var_form .= EvaDisplayInput::afficherInput('submit', $idButton, $save_button_value, null, '', $idButton, false, false, '', 'alignright button-primary', '', '', $script);
		}

		$var_form .= '
</form>';

		return $var_form;
	}


}