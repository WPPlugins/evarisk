<?php
/**
 *
 * @author Evarisk
 * @version v5.0
 */

class MethodeEvaluation {


	/**
	*	Define the different element to load when user is located on evaluation method page
	*
	*	@param integer $idElement The element identifier user want to view details for. If null, don't load element details
	*	@param string $chargement Define if all boxes have to be loaded, or only some element
	*
	*	@return void
	*/
	function includes_evaluation_method_boxes($idElement, $chargement = 'tout'){
		if($chargement == 'tout'){
			require_once(EVA_METABOXES_PLUGIN_DIR . 'methode/methode_edition.php');
			if(((int)$idElement) != 0){
				require_once(EVA_METABOXES_PLUGIN_DIR . 'galeriePhotos/galeriePhotos.php');
				require_once(EVA_METABOXES_PLUGIN_DIR . 'methode/method_variable.php');
				require_once(EVA_METABOXES_PLUGIN_DIR . 'methode/method_equivalence.php');
			}
		}
	}

	/**
	*	Define the different parameters for the evaluation method configuration page
	*
	*	@return array $recommandation_page_parameters The different parameters for evaluation method page output
	*/
	function evaluation_method_main_page(){
		$recommandation_page_parameters = array();

		/*	Page parameters	*/
		$recommandation_page_parameters['element_type'] = TABLE_METHODE;

		/*	Tree parameters	*/
		$recommandation_page_parameters['tree_identifier'] = 'main_table_' . $recommandation_page_parameters['element_type'];
		$recommandation_page_parameters['tree_root_name'] = __("M&eacute;thodes", 'evarisk');
		$recommandation_page_parameters['tree_element_are_draggable'] = false;
		$recommandation_page_parameters['tree_action_display'] = true;

		return $recommandation_page_parameters;
	}

	/**
	* Define the box content allowing to manage the different vars affected to an evaluation method
	*
	*	@param array $argument The parameters automatically called by the metabox loader
	*
	*	@return string $methode_vars_form The complete html output for the box
	*/
	function evaluation_method_variable_manager($argument, $display_button = true){
		$id_methode = $argument['idElement'];
		$valeurVariable1 = $methode_vars_form = '';

		/*	Get the variables affected to the current method	*/
		$variablesMethode = MethodeEvaluation::getVariablesMethode($id_methode);
		$nbInput = count($variablesMethode);
		foreach($variablesMethode as $variableMethode){
			$varMethodeIds[] = $variableMethode->id;
			$varMethodeNames[] = $variableMethode->nom;
		}
		/*	Get the operators affected to the current method	*/
		$operateursMethode = MethodeEvaluation::getOperateursMethode($id_methode);
		foreach($operateursMethode as $operateurMethode){
			$opsMethode[] = $operateurMethode->operateur;
		}


		/*	Get the existing operator list	*/
		unset($operateur);
		$operateur = array();
		$ops = Eva_Operateur::getOperators();
		foreach($ops as $op){
			$operateur[]=$op->symbole;
			$operateurIndexSymbole[$op->symbole] = $op;
		}
		/*	Get the existing variables list	*/
		unset($variable); unset($variableIndexId);
		$variable = $variableIndexId = array();
		$vars = MethodeEvaluation::getAllVariables();
		$i=0;
		$vars_array_value = $vars_array_output = array();
		foreach($vars as $var){
			$variableIndexId[$var->id] = $var;
			$variable[] = $var->id;
			$vars_array_output[] = ELEMENT_IDENTIFIER_V . $var->id . ' - ' . $var->nom;
			$i++;
		}


		/*	Display the method var form output	*/
		$methode_vars_form .= '
	<div class="digirisk_hide fade below-h2 evaMessage" id="evaluation_method_var_message" >&nbsp;</div>
	<div id="var_line_container" class="digirisk_hide" >
		<div class="single_var_container clear" id="evaluation_method_#LINENUMBER#" >
			<div class="alignleft" >
				' . EvaDisplayInput::afficherComboBox($ops, 'op_#LINENUMBER#', null, 'op[]', '', '', $operateur, $operateur) .
				EvaDisplayInput::afficherComboBox($vars, 'var_#LINENUMBER+1#', null, 'var[]', '', '', $variable, $vars_array_output) . '
			</div>
			<div class="single_var_delete_container aligleft" ><img src="' . PICTO_DELETE_VSMALL . '" alt="' . __('Enlever cette variable', 'evarisk') . '" title="' . __('Enlever cette variable', 'evarisk') . '" class="alignright" /></div>
		</div>
	</div>
<form action="' . EVA_INC_PLUGIN_URL . 'ajax.php" id="method_var_form" method="post" >
	<input type="hidden" name="post" id="post" value="true" />
	<input type="hidden" name="table" id="table" value="' . TABLE_METHODE . '" />
	<input type="hidden" name="act" id="act" value="save_method_var" />
	<input type="hidden" name="id_methode" id="id_methode" value="' . $id_methode . '" />';

		/*	If user is allowed to edit method vars	*/
		$method_vars_script_action = '';
		if(current_user_can('digi_edit_method_var')){
			$methode_vars_form .= '
	<div class="open_var_manager_container" ><div id="var_manager_window" title="' . __('Gestion des variables', 'evarisk') . '" class="digirisk_hide" >&nbsp;</div>' . __('G&eacute;rer les variables', 'evarisk') . '<span class="ui-icon open_var_manager" >&nbsp;</span></div>';
			$method_vars_script_action = '
		/*	Create the dialog box allowing to manage vars	*/
		jQuery("#var_manager_window").dialog({
			autoOpen: false,
			modal: true,
			width: 800,
			height: 600,
			close: function(){
				jQuery(this).html("");
			}
		});

		/*	Add support on link for manage vars	*/
		jQuery(".open_var_manager_container").click(function(){
			jQuery("#var_manager_window").dialog("open");
			jQuery("#var_manager_window").html(evarisk("#loadingImg").html());
			jQuery("#var_manager_window").load(EVA_AJAX_FILE_URL,{
				"post": "true",
				"table": "' . TABLE_VARIABLE . '",
				"act": "load_variable_management"
			});
		});';
		}

		$methode_vars_form .= '
	<div class="evaluation_method_var_container" >';
		$methode_vars_form .= self::evaluation_method_management_content($argument);
		$methode_vars_form .= '
	</div>
	<div class="clear" id="add_new_var_to_method" >
		<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'add_vs.png" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('une variable &agrave; la m&eacute;thode', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('une variable &agrave; la m&eacute;thode', 'evarisk')) . '" />
	</div>
	<input type="submit" name="save_evaluation_method" id="save_evaluation_method" class="clear alignright button-primary" value="' . __('Enregistrer', 'evarisk') . '" />
</form>
<script type="text/javascript" >
	digirisk(document).ready(function(){' . $method_vars_script_action . '
		jQuery("#method_var_form").ajaxForm({
			target: "#ajax-response"
		});

		jQuery(".single_var_delete_container img").live("click", function(){
			jQuery(this).parent("div").parent("div .single_var_container").remove();
		});

		jQuery("#add_new_var_to_method").click(function(){
			var next_var_id = 1;
			if(jQuery(this).prev("div").hasClass("single_var_container")){
				next_var_id += parseInt(jQuery(this).prev(".single_var_container").attr("id").replace("evaluation_method_", ""));
			}
			jQuery(this).before(jQuery("#var_line_container").html());

			var new_line_id = jQuery(this).prev(".single_var_container").attr("id").replace("#LINENUMBER#", next_var_id);
			jQuery(this).prev(".single_var_container").attr("id", new_line_id);

			jQuery("#" + new_line_id + " div select:first").attr("id", jQuery("#" + new_line_id + " div select:first").attr("id").replace("#LINENUMBER#", next_var_id));
			jQuery("#" + new_line_id + " div label:first").attr("id", jQuery("#" + new_line_id + " div label:first").attr("id").replace("#LINENUMBER#", next_var_id));
			jQuery("#" + new_line_id + " div label:first").attr("for", jQuery("#" + new_line_id + " div select:first").attr("id"));
			jQuery("#" + new_line_id + " div select:last").attr("id", jQuery("#" + new_line_id + " div select:last").attr("id").replace("#LINENUMBER+1#", next_var_id + 1));
			jQuery("#" + new_line_id + " div label:last").attr("id", jQuery("#" + new_line_id + " div label:last").attr("id").replace("#LINENUMBER+1#", next_var_id + 1));
			jQuery("#" + new_line_id + " div label:last").attr("for", jQuery("#" + new_line_id + " div select:last").attr("id"));
		});
	});
</script>';

		echo $methode_vars_form;
	}
	function evaluation_method_management_content($argument){
		$id_methode = $argument['idElement'];
		$valeurVariable1 = $methode_vars_form = '';

		/*	Get the variables affected to the current method	*/
		$variablesMethode = MethodeEvaluation::getVariablesMethode($id_methode);
		$nbInput = count($variablesMethode);
		foreach($variablesMethode as $variableMethode){
			$varMethodeIds[] = $variableMethode->id;
			$varMethodeNames[] = $variableMethode->nom;
		}
		/*	Get the operators affected to the current method	*/
		$operateursMethode = MethodeEvaluation::getOperateursMethode($id_methode);
		foreach($operateursMethode as $operateurMethode){
			$opsMethode[] = $operateurMethode->operateur;
		}


		/*	Get the existing operator list	*/
		unset($operateur);
		$operateur = array();
		$ops = Eva_Operateur::getOperators();
		foreach($ops as $op){
			$operateur[]=$op->symbole;
			$operateurIndexSymbole[$op->symbole] = $op;
		}
		/*	Get the existing variables list	*/
		unset($variable); unset($variableIndexId);
		$variable = $variableIndexId = array();
		$vars = MethodeEvaluation::getAllVariables();
		$i=0;
		$vars_array_value = $vars_array_output = array();
		foreach($vars as $var){
			$variableIndexId[$var->id] = $var;
			$variable[] = $var->id;
			$vars_array_output[] = ELEMENT_IDENTIFIER_V . $var->id . ' - ' . $var->nom;
			$i++;
		}
		foreach($variableIndexId as $premiereVariable){
			$valeurVariable1 = (isset($varMethodeIds)) ? $variableIndexId[$varMethodeIds[0]] : $premiereVariable;
			break;
		}

		$output = '
		<div class="first_var" >
		' . EvaDisplayInput::afficherComboBox($vars, 'var_1', null, 'var[]', '', $valeurVariable1, $variable, $vars_array_output) . '
		</div>';
		if(isset($variablesMethode) && count($variablesMethode)>0){
			for($i=1; $i<count($variablesMethode); $i++){
				$output .= '
		<div class="single_var_container clear" id="evaluation_method_' . $i . '" >
			<div class="alignleft" >
				' . EvaDisplayInput::afficherComboBox($ops, 'op_' . $i, null, 'op[]', '', $operateurIndexSymbole[$opsMethode[($i-1)]], $operateur, $operateur) .
					EvaDisplayInput::afficherComboBox($vars, 'var_' . ($i + 1), null, 'var[]', '', $variableIndexId[$varMethodeIds[$i]], $variable, $vars_array_output) . '
			</div>
			<div class="single_var_delete_container aligleft" ><img src="' . PICTO_DELETE_VSMALL . '" alt="' . __('Enlever cette variable', 'evarisk') . '" title="' . __('Enlever cette variable', 'evarisk') . '" class="alignright" /></div>
		</div>';
			}
		}

		return $output;
	}

	/**
	* Define the box content allowing to manage the different equivalence between vars affected to an evaluation method and the basic scale
	*
	*	@param array $argument The parameters automatically called by the metabox loader
	*
	*	@return string $methode_vars_equivalence_form The complete html output for the box
	*/
	function evaluation_method_variable_equivalence($argument){
		$id_methode = $argument['idElement'];
		$methode_vars_equivalence_form = '';

		/**/
		$etalon = methodeEvaluation::getEtalon();

		/*	Display the method var form output	*/
		$methode_vars_equivalence_form .= '
<div class="digirisk_hide fade below-h2 evaMessage" id="evaluation_method_var_equivalence_message" >&nbsp;</div>
<form action="' . EVA_INC_PLUGIN_URL . 'ajax.php" id="method_var_equivalenceform_" method="post" >
	<input type="hidden" name="post" id="post" value="true" />
	<input type="hidden" name="table" id="table" value="' . TABLE_METHODE . '" />
	<input type="hidden" name="act" id="act" value="save_method_var_equivalence" />
	<input type="hidden" name="id_methode" id="id_methode" value="' . $id_methode . '" />
	<div class="evaluation_method_var_equivalence_container" >';

		/*	Read each equivalence possible value	*/
		$j=0;
		for($i = $etalon->min; $i <= $etalon->max; $i = $i + $etalon->pas){
			$equivalent = methodeEvaluation::getEquivalentEtalon($id_methode, $i);
			$contenuInput = (isset($equivalent)) ?$equivalent->valeurMaxMethode : '';

			/*	Add equivalence field to box output	*/
			$methode_vars_equivalence_form .= EvaDisplayInput::afficherInput('text', 'eqiv' . $i, $contenuInput, '', sprintf(__('Valeur maximale de la m&eacute;thode &eacute;quivalent &agrave; %d :', 'evarisk'), $i), 'equivalent[' . $i . ']', false, false, 15, '', 'Number', '20%', '');
			$j++;
		}

		$methode_vars_equivalence_form .= '
	</div>
	<input type="submit" name="save_evaluation_method_var_equivalence" id="save_evaluation_method_var_equivalence" class="clear alignright button-primary" value="' . __('Enregistrer', 'evarisk') . '" />
</form>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#method_var_equivalenceform_").ajaxForm({
			target: "#ajax-response"
		});
	});
</script>';

		echo $methode_vars_equivalence_form;
	}


	/**
	 * @var Integer The method identifier
	 */
	var $id;
	/**
	 * @var String The method name
	 */
	var $name;

/*
 *	Constructeur et accesseurs
 */

	/**
	 * Constructor of the method class
	 * @param $id Integer The identifier to setI
	 * @param $name String The name to set
	 */
	function MethodeEvaluation($id = NULL, $name = '') {
		$this->id = $id;
		$this->name = $name;
	}

	/**
	 * Return the method identifier
	 * @return Integer The identifier
	 */
	function getId(){
		return $this->id;
	}
	/**
	 * Set the method identifier
	 * @param $id Integer The identifier to set
	 */
	function setId($id){
		$this->id = $id;
	}
	/**
	 * Return the method name
	 * @return String The name
	 */
	function getName(){
		return $this->name;
	}
	/**
	 * Set the method name
	 * @param $name String The name to set
	 */
	function setName($name){
		$this->name = $name;
	}

	function getMethod($id){
		global $wpdb;
		$id = (int) $id;
		$t = TABLE_METHODE;
		return $wpdb->get_row( "SELECT * FROM {$t} WHERE id = " . $id);
	}

	function getMethods($where = "1", $order = "nom ASC") {
		global $wpdb;
		$t = TABLE_METHODE;
		$resultat = $wpdb->get_results( "SELECT * FROM {$t} WHERE " . $where . " ORDER BY " . $order);
		return $resultat;
	}

	function getAllVariables($where = "1", $order = "nom ASC"){
		global $wpdb;
		$resultat = eva_Variable::getVariables($where, $order);
		return $resultat;
	}

	function getVariablesMethode($id_methode, $date=null){
		global $wpdb;

		if ($date==null) {
			$date=current_time('mysql', 0);
		}
		$id_methode = (int) $id_methode;
		$tav = TABLE_AVOIR_VARIABLE;
		$tv =  TABLE_VARIABLE ;
		return $wpdb->get_results( "SELECT *
			FROM " . $tv . ", " . $tav . " t1
			WHERE t1.id_methode=" . $id_methode . "
			AND t1.date < '" . $date . "'
			AND NOT EXISTS(
				SELECT *
				FROM " . $tav . " t2
				WHERE t2.id_methode=" . $id_methode . "
				AND t2.date < '" . $date . "'
				AND t1.date < t2.date
			)
			AND id_variable=id
			ORDER BY ordre ASC");
	}

	function getDistinctVariablesMethode($id_methode, $date=null){
		global $wpdb;

		if($date==null)
		{
			$date=current_time('mysql', 0);
		}
		$id_methode = (int) $id_methode;
		$tav = TABLE_AVOIR_VARIABLE;
		$tv =  TABLE_VARIABLE ;
		return $wpdb->get_results( "
			SELECT DISTINCT(nom), id, min, max, annotation, affichageVar, questionVar, questionTitre
			FROM " . $tv . ", " . $tav . " t1
			WHERE t1.id_methode=" . $id_methode . "
			AND t1.date < '" . $date . "'
			AND NOT EXISTS
			(
				SELECT *
				FROM " . $tav . " t2
				WHERE t2.id_methode=" . $id_methode . "
				AND t2.date < '" . $date . "'
				AND t1.date < t2.date
			)
			AND id_variable=id
			ORDER BY ordre ASC");
	}

	function getOperateursMethode($id_methode, $date = null){
		global $wpdb;

		if($date==null){
			$date = current_time('mysql', 0);
		}
		$id_methode = (int) $id_methode;
		$t = TABLE_AVOIR_OPERATEUR;
		$query = $wpdb->prepare("SELECT *
				FROM " . $t . " t1
				WHERE t1.id_methode = %d
				AND t1.date < %s
                                AND t1.Status = 'Valid'
				AND NOT EXISTS
				(
					SELECT *
					FROM " . $t . " t2
					WHERE t2.id_methode = %d
					AND t2.date < %s
					AND t1.date < t2.date
				)
				ORDER BY ordre ASC", $id_methode, $date, $id_methode, $date);
		return $wpdb->get_results($query);
	}

	function getFormule($id, $date=null){
		global $wpdb;

		if($date==null)
		{
			$date=current_time('mysql', 0);
		}
		$id = (int) $id;
		$formule = '';
		$t = TABLE_AVOIR_VARIABLE;
		//on r�cup�re les ids des variables
		$id_variables = $wpdb->get_results("
			SELECT *
			FROM " . $t . " t1
			WHERE t1.id_methode=" . $id . "
			AND t1.date < '" . $date . "'
			AND NOT EXISTS
			(
				SELECT *
				FROM " . $t . " t2
				WHERE t2.id_methode=" . $id . "
				AND t2.date < '" . $date . "'
				AND t1.date < t2.date
			)
			ORDER BY ordre ASC");
		$ordre=0;
		$table_var = TABLE_VARIABLE;
		$table_avoir_op = TABLE_AVOIR_OPERATEUR;
		//pour chaque id
		foreach($id_variables as $id_variable)
		{
			//on recupere la variable...
			$variable = $wpdb->get_row("SELECT * FROM " . $table_var . " WHERE id=" . $id_variable->id_variable);
			//et l'op�rateur
			$operateur = $wpdb->get_row("
				SELECT *
				FROM " . $table_avoir_op . " t1
				WHERE t1.id_methode=" . $id . "
				AND t1.date < '" . $date . "'
				AND t1.ordre=" . $ordre . "
				AND NOT EXISTS
				(
					SELECT *
					FROM " . $table_avoir_op . " t2
					WHERE t2.id_methode=" . $id . "
					AND t2.date < '" . $date . "'
					AND t2.ordre=" . $ordre . "
					AND t1.date < t2.date
				)
				ORDER BY ordre ASC");
			//et on compl�te la formule
			$operateur = (!isset($operateur) OR $operateur == null)?'':$operateur->operateur;
			$formule = $formule . ' ' . $operateur . ' ' . $variable->nom;
			$ordre = $ordre + 1;
		}
		return $formule;
	}

	function getEtalon(){
		global $wpdb;
		$table = TABLE_ETALON;
		$resultat = $wpdb->get_row( "SELECT * FROM " . $table);
		return $resultat;
	}

	function getEquivalentEtalon($idMethode, $valeurEtalon, $date=null){
		global $wpdb;

		if($date==null)
		{
			$date=current_time('mysql', 0);
		}
		$table = TABLE_EQUIVALENCE_ETALON;
		$resultat = $wpdb->get_row("
			SELECT *
			FROM " . $table . " t1
			WHERE t1.id_methode=" . $idMethode . "
			AND t1.id_valeur_etalon=" . $valeurEtalon . "
			AND t1.date < '" . $date . "'
			AND NOT EXISTS
			(
				SELECT *
				FROM " . $table . " t2
				WHERE t2.id_methode=" . $idMethode . "
				AND t1.id_valeur_etalon=" . $valeurEtalon . "
				AND t2.date < '" . $date . "'
				AND t1.date < t2.date
			)");
		return $resultat;
	}


	/**
	*
	*/
	function evaluation_method_form($argument){
		$id_methode = $argument['idElement'];

		$nom_methode = '';
		if(($id_methode != '') && ($id_methode > 0)){
			$method_informations = MethodeEvaluation::getMethod($id_methode);
			$nom_methode = $method_informations->nom;
		}

?>
<p class="evaluation_method_form_error_message digirisk_hide">&nbsp;</p>
<form action="<?php echo EVA_INC_PLUGIN_URL; ?>ajax.php" id="method_form" method="post" >
	<input type="hidden" name="post" id="post" value="true" />
	<input type="hidden" name="table" id="table" value="<?php _e(TABLE_METHODE); ?>" />
	<input type="hidden" name="act" id="act" value="save" />

	<input type="hidden" name="id_methode" id="id_methode" class="evaluation_method_input" value="<?php _e($id_methode); ?>" />

	<label for="nom_methode" ><?php _e('Nom', 'evarisk'); ?></label><br/>
	<input type="text" name="nom_methode" id="nom_methode" class="evaluation_method_input" value="<?php _e($nom_methode); ?>" />

	<br/><input type="checkbox" name="default_methode" id="default_methode" value="yes"<?php echo (!empty($method_informations->default_methode) && ($method_informations->default_methode == 'yes') ? ' checked' : ''); ?> /> <label for="default_methode" ><?php _e('M&eacute;thode par d&eacute;faut', 'evarisk'); ?></label><br/>


	<input type="submit" name="save_evaluation_method" id="save_evaluation_method" class="clear alignright button-primary" value="<?php _e('Enregistrer', 'evarisk'); ?>" />
</form>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#method_form").ajaxForm({
			target: "#ajax-response",
			beforeSubmit: validate_evaluation_method_form
		});
	});

	function validate_evaluation_method_form(formData, jqForm, options){
		evarisk("#nom_methode").removeClass("ui-state-error");
		for(var i=0; i < formData.length; i++){
			if((formData[i].name == "nom_methode") && !formData[i].value){
				checkLength( evarisk("#nom_methode"), "", 1, 255, "<?php _e('Le champs nom de la m&eacute;thode doit contenir entre !#!minlength!#! et !#!maxlength!#! caract&egrave;res', 'evarisk'); ?>" , evarisk(".evaluation_method_form_error_message"))
				return false;
			}
		}

		return true;
	}
</script>
<?php
	}


}