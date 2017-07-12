<?php
/**
* Template manager
*
* Define the different method to manage the plugin template
* @author Evarisk <dev@evarisk.com>
* @version 5.1.4.5
* @package digirisk
* @subpackage librairies
*/

/**
* Define the different method to manage the plugin template
*
* @package digirisk
* @subpackage librairies
*/
class digirisk_display
{

	/**
	* Returns the header display of a classical HTML page.
	*
	* @see end_page
	*
	* @param string $titrePage Title of the page.
	* @param string $icone Path of the icon.
	* @param string $titreIcone Title attribute of the icon.
	* @param string $altIcon Alt attribute of the icon.
	* @param string $element_type Table where the page is link.
	* @param bool $boutonAjouter Must the page have a button "Add" next to the title ?
	* @param string $messageInfo The information message.
	* @param bool $choixAffichage Must the page offer a choice of display ?
	*
	* @return string HTML code of the header display.
	*/
	public static function start_page($titrePage, $icone, $titreIcone, $altIcon, $element_type, $boutonAjouter=true, $messageInfo='', $choixAffichage=false, $affichageNotes = true, $page_icon_id = ''){
		$debutPage = '';

		ob_start();
?>
<div class="digirisk_hide" id="loadingImg" ><div class="main_loading_pic_container" ><img src="<?php echo PICTO_LOADING; ?>" alt="loading..." /></div></div>
<div class="digirisk_hide" id="round_loading_img" ><div class="round_loading_img" ><img src="<?php echo PICTO_LOADING_ROUND; ?>" alt="loading..." /></div></div>
<div class="digirisk_hide" id="dataTable_search_icon" ><span class='ui-icon searchDataTableIcon' >&nbsp;</span></div>
<div class="wrap">
	<div class="icon32" <?php echo $page_icon_id; ?> >
<?php
	if($icone != ''){
?>
	<img alt="<?php echo $altIcon; ?>" src="<?php echo $icone; ?>" title="<?php echo $titreIcone; ?>" />
<?php
	}
	else{
?>
		&nbsp;
<?php
	}
?>
</div>
	<h2 >
<?php
		echo $titrePage;
		if($boutonAjouter){
?>
		<a class="button add-new-h2" onclick="javascript:document.getElementById(\'act\').value=\'add\'; document.forms.form.submit();"><?php _e('Ajouter', 'evarisk'); ?></a>
<?php
		}
?>
	</h2>
	<div id="champsCaches" class="clear digirisk_hide" >
		<input type="hidden" id="pagemainPostBoxReference" value="1" />
		<input type="hidden" id="identifiantActuellemainPostBox" value="1" />
	</div>
	<div id="message" class="fade below-h2 evaMessage"><?php echo $messageInfo; ?></div>
	<div class="main_page_options_container" >
<?php
		if($affichageNotes){
			echo evaNotes::noteDialogMaker();
?>
	<script type="text/javascript">
		digirisk(document).ready(function(){
<?php echo evaNotes::noteDialogScriptMaker(); ?>
		});
	</script>
<?php
		}
		if($choixAffichage){
?>
		<div id="digirisk_shape_selector" >
			<span id="rightEnlarging" class="rightEnlarging"></span>
			<div id="enlarging" class="enlarging"></div>
			<span id="equilize" class="enlarging digirisk_hide"></span>
			<span id="leftEnlarging" class="leftEnlarging"></span>
		</div>
<?php
		}
?>
	</div>
<?php

		$debutPage = ob_get_contents();;
		ob_end_clean();

		return $debutPage;
	}

	/**
	* Closes the "div" tag open in the header display  of a classical HTML page.
	*
	* @see start_page
	* @return  the closure.
	*/
	public static function end_page(){
		$end_page = '';

		ob_start();
?>
	<div class="clear digirisk_hide" id="ajax-response" >&nbsp;</div>
</div>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		main_page_shape_selector();
	});
</script>
<?php
		$end_page = ob_get_contents();;
		ob_end_clean();

		return $end_page;
	}

	/**
	*
	*
	*/
	function page_content($page_parameters){

		switch($page_parameters['element_type']){
			case TABLE_GROUPEMENT:
				switch($menu){
					case 'gestiongrptut':
						$pageHook = PAGE_HOOK_EVARISK_GROUPEMENTS_GESTION;
					break;
					default:
						$pageHook = PAGE_HOOK_EVARISK_GROUPEMENTS;
					break;
				}
			break;
			case TABLE_UNITE_TRAVAIL:
				$pageHook = PAGE_HOOK_EVARISK_UNITES_DE_TRAVAIL;
			break;
			case TABLE_CATEGORIE_DANGER:
				$pageHook = PAGE_HOOK_EVARISK_CATEGORIES_DANGERS;
			break;
			case TABLE_DANGER:
				$pageHook = PAGE_HOOK_EVARISK_DANGERS;
			break;
			case TABLE_TACHE:
				$pageHook = PAGE_HOOK_EVARISK_TACHE;
			break;
			case TABLE_ACTIVITE:
				$pageHook = PAGE_HOOK_EVARISK_ACTIVITE;
			break;
		}
		$load_element = false;
		if(isset($_GET['elt']) && ($_GET['elt'] != '')){
			$element_type_to_output = (substr($_GET['elt'], 0, 4) != 'edit') ? substr($_GET['elt'], 0, 4) : substr($_GET['elt'], 5, 4);
			$menu = (substr($_GET['elt'], 0, 4) != 'edit') ? 'risq' : 'gestiongrptut';
			switch($page_parameters['element_type']){
				case TABLE_GROUPEMENT:
					switch($element_type_to_output){
						case 'leaf':{
							$_POST['table'] = TABLE_UNITE_TRAVAIL;
						}break;
						case 'node':{
							$_POST['table'] = $page_parameters['element_type'];
						}break;
					}
				break;
				case TABLE_CATEGORIE_DANGER:
					switch($element_type_to_output){
						case 'leaf':{
							$_POST['table'] = TABLE_DANGER;
						}break;
						case 'node':{
							$_POST['table'] = $page_parameters['element_type'];
						}break;
					}
				break;
				case TABLE_TACHE:
					switch($element_type_to_output){
						case 'leaf':{
							$_POST['table'] = TABLE_ACTIVITE;
						}break;
						case 'node':{
							$_POST['table'] = $page_parameters['element_type'];
						}break;
					}
				break;
			}

			$load_element = true;
			$_POST['affichage'] = 'affichageListe';

			$_POST['id'] = str_replace('-name', '', $_GET['elt']);
			$_POST['id'] = str_replace('edit-' . $element_type_to_output, '', $_POST['id']);
			$_POST['id'] = str_replace('main_table_' . $page_parameters['element_type'] . '-', '', $_POST['id']);
			$_POST['id'] = str_replace($element_type_to_output . '-', '', $_POST['id']);

			$_POST['menu'] = $menu;
			$_POST['post'] = 'true';
			$_POST['act'] = 'edit';
		}

?>
	<div class="metabox-holder clear digirisk_meta_box_holder" >
		<div id="digirisk_left_container" class="digirisk_left_container" >
			<div class="clear" id="main_tree_container" >
<?php

	switch($page_parameters['element_type']){
		case TABLE_CATEGORIE_PRECONISATION:
		case TABLE_METHODE:
		case TABLE_MENU:
			echo digirisk_display::standard_configuration_tree($page_parameters['element_type'],  $page_parameters['tree_identifier'], $page_parameters['tree_root_name'], $page_parameters['tree_element_are_draggable'], $page_parameters['tree_action_display']);
		break;
		default:
			echo digirisk_display::standard_tree($page_parameters['tree_root'], $page_parameters['element_type'],  $page_parameters['tree_identifier'], $page_parameters['tree_root_name'], $page_parameters['tree_element_are_draggable'], $page_parameters['tree_action_display']);
		break;
	}
?>
			</div>
			<div id="digirisk_left_side" class="digirisk_left_side_metabox" >
<?php
		if($load_element){
			$_POST['partie'] = 'left';
			include(EVA_MODULES_PLUGIN_DIR . 'partieDroite.php');
		}
		else{
?>
			&nbsp;
<?php
		}
?>
			</div>
		</div>
		<div id="digirisk_right_container" class="digirisk_right_container" >
			<div id="digirisk_right_side" class="digirisk_right_side_metabox" >
<?php
		if($load_element){
			$_POST['partie'] = 'right';
			include(EVA_MODULES_PLUGIN_DIR . 'partieDroite.php');
		}
		else{
?>
			&nbsp;
<?php
		}
?>
			</div>
		</div>
	</div>
<?php
	}

	/**
	* Returns the list view tree table with scripts that allow you to display the right part by clicking on the elements and the drag and drop.
	*
	* @see standard_tree_element
	*
	* @param Element_of_a_tree $tree_root Root element of the table.
	* @param string $element_type Table name.
	* @param int $output_table_id HTML Id attribute for the table.
	* @param string $nomRacine Text to be displayed in the root of the table.
	* @return string HTML code of the table.
	*/
	function standard_tree($tree_root, $element_type, $output_table_id, $nomRacine, $draggable = true, $outputAction = true){
		$monCorpsTable = $class = $infoRacine = $actions = $script_action = $tableArborescente = '';
		$showTrashUtilities = $output_info = false;

		$main_tree_id = $tree_root->id;

		switch($element_type){
			case TABLE_GROUPEMENT:{
				$sub_element_type = TABLE_UNITE_TRAVAIL;
				$divDeChargement = 'message';
				$titreInfo = __("Somme des risques", 'evarisk');
				$output_info = true;
				$actionSize = 5;
				if($outputAction){
					$actions = '
			<td class="noPadding addMain" id="addMain' . $main_tree_id . '">';
					if(current_user_can('digi_add_groupement')){
						$actions .= '
				<img style="width:' . TAILLE_PICTOS_ARBRE . ';"  src="' . PICTO_LTL_ADD_GROUPEMENT . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('un groupement', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('un groupement', 'evarisk')) . '" />';
					}
					else{
						$actions .= '&nbsp;';
					}
					$actions .= '
			</td>';
					/*	Add trash	*/
					$main_option = get_option('digirisk_options');
					if(($main_option['digi_activ_trash'] == 'oui') && (current_user_can('digi_view_groupement_trash') || current_user_can('digi_view_unite_trash'))){
						$showTrashUtilities = true;
						$actions .= '
			<td colspan="' . ($actionSize - 2) . '" >&nbsp;</td>
			<td class="noPadding trash" id="trash' . $main_tree_id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';"  src="' . EVA_IMG_ICONES_PLUGIN_URL . 'trash.png" alt="Trash" title="' . __('Acc&eacute;der &agrave; la corbeille', 'evarisk') . '" /></td>';
					}
					else{
						$actions .= '
							<td colspan="' . ($actionSize - 1) . '" >&nbsp;</td>';
					}

					$script_action = 'main_tree_action_node("' . $output_table_id . '", "' . $element_type . '", "' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer cet &eacute;l&eacute;ment?\r\nATTENTION: si cet &eacute;l&eacute;ment poss&egrave;de des sous-&eacute;l&eacute;ment, ils seront inaccessibles', 'evarisk') . '");
		main_tree_action_leaf("' . $output_table_id . '", "' . $sub_element_type . '", "' . __('Etes vous sur de vouloir supprimer cet element?', 'evarisk') . '");';
				}

			}break;
			case TABLE_CATEGORIE_DANGER:{
				$sub_element_type = TABLE_DANGER;
				$divDeChargement = 'message';
				$titreInfo = null;
				$actionSize = 4;
				if($outputAction){
					$actions = '
							<td class="noPadding addMain" id="addMain' . $main_tree_id . '">';
					if(current_user_can('digi_add_danger_category')){
						$actions .= '<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_LTL_ADD_CATEGORIE_DANGER . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('une cat&eacute;gorie de dangers', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('une cat&eacute;gorie de dangers', 'evarisk')) . '" />';
					}
					else{
						$actions .= '&nbsp;';
					}
					$actions .= '</td>';
					/*	Add trash	*/
					$main_option = get_option('digirisk_options');
					if(($main_option['digi_activ_trash'] == 'oui') && (current_user_can('digi_view_groupement_trash') || current_user_can('digi_view_unite_trash'))){
						$showTrashUtilities = true;
						$actions .= '
							<td colspan="' . ($actionSize - 2) . '" >&nbsp;</td>
							<td class="noPadding trash" id="trash' . $main_tree_id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';"  src="' . EVA_IMG_ICONES_PLUGIN_URL . 'trash.png" alt="Trash" title="' . __('Acc&eacute;der &agrave; la corbeille', 'evarisk') . '" /></td>';
					}
					else{
						$actions .= '
							<td colspan="' . ($actionSize - 1) . '" >&nbsp;</td>';
					}

					$script_action = 'main_tree_action_node("' . $output_table_id . '", "' . $element_type . '", "' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer cet &eacute;l&eacute;ment?\r\nATTENTION: si cet &eacute;l&eacute;ment poss&egrave;de des sous-&eacute;l&eacute;ment, ils seront inaccessibles', 'evarisk') . '");
		main_tree_action_leaf("' . $output_table_id . '", "' . $sub_element_type . '", "' . __('Etes vous sur de vouloir supprimer cet element?', 'evarisk') . '");';
				}

			}break;
			case TABLE_TACHE:{
				$sub_element_type = TABLE_ACTIVITE;
        $tacheRacine = new EvaTask($main_tree_id);
				$divDeChargement = 'message';
				$titreInfo = __("Avancement", 'evarisk');
				$output_info = true;
				$actionSize = 4;
				if($outputAction){
					$actions = '
						<td class="noPadding addMain" id="addMain' . $main_tree_id . '">';
					if(current_user_can('digi_add_task')){
						$actions .=
							'<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_LTL_ADD_TACHE . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('une t&acirc;che', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('une t&acirc;che', 'evarisk')) . '" />';
					}
					else{
						$actions .= '&nbsp;';
					}
					$actions .= '
						</td>';
					/*	Add trash	*/
					$main_option = get_option('digirisk_options');
					if(($main_option['digi_activ_trash'] == 'oui') && (current_user_can('digi_view_groupement_trash') || current_user_can('digi_view_unite_trash'))){
						$showTrashUtilities = true;
						$actions .= '
							<td colspan="' . ($actionSize - 2) . '" >&nbsp;</td>
							<td class="noPadding trash" id="trash' . $main_tree_id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';"  src="' . EVA_IMG_ICONES_PLUGIN_URL . 'trash.png" alt="Trash" title="' . __('Acc&eacute;der &agrave; la corbeille', 'evarisk') . '" /></td>';
					}
					else{
						$actions .= '
							<td colspan="' . ($actionSize - 1) . '" >&nbsp;</td>';
					}

					$script_action = 'main_tree_action_node("' . $output_table_id . '", "' . $element_type . '", "' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer cet &eacute;l&eacute;ment?\r\nATTENTION: si cet &eacute;l&eacute;ment poss&egrave;de des sous-&eacute;l&eacute;ment, ils seront inaccessibles', 'evarisk') . '");
		main_tree_action_leaf("' . $output_table_id . '", "' . $sub_element_type . '", "' . __('Etes vous sur de vouloir supprimer cet element?', 'evarisk') . '");';
				}

			}break;
			case TABLE_GROUPE_QUESTION:{
				$sub_element_type = TABLE_QUESTION;
				$divDeChargement = 'ajax-response';
				$titreInfo = null;
				$actionSize = 3;
				$actions = '
							<td class="noPadding addMain" id="add-node-' . $main_tree_id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_INSERT . '" alt="' . __('Inserer sous le titre', 'evarisk') . '" title="' . __('Inserer sous le titre', 'evarisk') . '" /></td>
							<td class="noPadding addSecondary" id="edit-node-' . $main_tree_id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';display:none;" id="img_edit_tree_root" src="' . PICTO_EDIT . '" alt="' . __('Modifier le titre', 'evarisk') . '" title="' . __('Modifier le titre', 'evarisk') . '" /></td>
							<td></td>';
			}break;
		}

		/*	Add trash utilities if user is allowed	*/
		$trashScript = '';
		if($showTrashUtilities){
			$tableArborescente .= '
				<div id="trashContainer" title="' . __('Liste des &eacute;l&eacute;ments supprim&eacute;s', 'evarisk') . '" >&nbsp;</div>';
			$trashScript = '

		main_tree_trash("' . $element_type . '");';
		}

		/*	Add support for dragging element on treee	*/
		$draggableScript = '';
		if($draggable){
			$draggableScript .= '

		main_tree_draggable("' . $output_table_id . '", "' . $element_type . '", "' . $divDeChargement . '", "' . __('Transfert en cours...', 'evarisk') . '");';
		}

		$infoRacine = digirisk_display::get_info_column_content($element_type, $main_tree_id);
		$tableArborescente .= '
<table id="' . $output_table_id . '" cellspacing="0" class="widefat post fixed digirisk_main_table" >
	<thead>
		<tr>
			<th>' . $infoRacine['title'] . '</th>';
		$info_racine_output='';
		if($output_info){
			$tableArborescente .= '
			<th class="infoList">' . $titreInfo . '</th>';
			$info_racine_output = '
			<td id="info-' . $main_tree_id . '" class="' . $infoRacine['class'] . '" >&nbsp;</td>';
		}
		$tableArborescente .= ($outputAction ? '
			<th colspan="' . $actionSize . '" class="actionButtonList" ><div class="action_column_name" >' . __('Actions', 'evarisk') . '</div><div class="main_metabox_collapser" >&nbsp;</div></th>' : '');
		$tableArborescente .= '
		</tr>
	</thead>
	<tbody>
		<tr id="node-' . $output_table_id . '-' . $main_tree_id . '" class="parent racineArbre">
			<td id="tdRacine' . $output_table_id . '">&nbsp;</td>' . $info_racine_output . $actions . '
		</tr>
		' . digirisk_display::standard_tree_element($main_tree_id, $element_type, $titreInfo, $output_table_id, 0) . '
	</tbody>
</table>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		table_to_treeTable("' . $output_table_id . '", "' . $main_tree_id . '", "' . $element_type . '", "' . $sub_element_type . '", "' . (($element_type == TABLE_GROUPEMENT) ? 'gestiongrptut' : '') . '");

		' . $script_action . '
		' . $trashScript . '
		' . $draggableScript;

		/*	If option is set to not load complete tree	*/
		$options = get_option('digirisk_tree_options');
		if(!empty($options['digi_tree_load_complete_tree']) && ($options['digi_tree_load_complete_tree'] == 'non')){
			$tableArborescente .= '
		jQuery("#' . $output_table_id . ' .nomNoeudArbre").click(function(){
			if(jQuery(this).parent("tr").hasClass("expanded") && !jQuery(this).parent("tr").hasClass("already_load")){
				jQuery("#ajax-response").html(jQuery("#loadingImg").html());
				jQuery("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
					"post": "true",
					"act": "load_main_tree_children",
					"element_type": "' . $element_type . '",
					"element_type_identifier": jQuery(this).parent("tr").attr("id").replace("node-' . $output_table_id . '-", ""),
					"output_info": "' . $output_info . '",
					"output_table_id": "' . $output_table_id . '"
				});
			}
		});';
		}

		/*	Automatically open an element and all it's parent	*/
		if(isset($_REQUEST['expanded']) && ($_REQUEST['expanded'] != '')){
			foreach($_REQUEST['expanded'] as $element_to_expand){
				$tableArborescente .= '
		jQuery("#' . $element_to_expand . '-name").children("span.expander").click(); /*	Expanded	*/';
			}
		}
		/*	Automatically open an element and all it's parent	*/
		if(isset($_REQUEST['elt']) && ($_REQUEST['elt'] != '')){
			/*	Check the element passed through url	*/
			$element_type_to_output = substr($_REQUEST['elt'], 0, 4);
			$selected_element_identifier = str_replace('-name', '', str_replace($element_type_to_output . '-', '', str_replace('main_table_' . $element_type . '-', '', $_REQUEST['elt'])));
			switch($element_type){
				case TABLE_GROUPEMENT:
					$main_root_name = 'Groupement';
					switch($element_type_to_output){
						case 'leaf':{
							$pageHook = PAGE_HOOK_EVARISK_UNITES_DE_TRAVAIL;
							$element = eva_UniteDeTravail::getWorkingUnit($selected_element_identifier);
							$directParent = EvaGroupement::getGroupement($element->id_groupement);
						}break;
						case 'node':{
							$pageHook = PAGE_HOOK_EVARISK_GROUPEMENTS;
							$directParent = EvaGroupement::getGroupement($selected_element_identifier);
						}break;
					}
				break;
				case TABLE_CATEGORIE_DANGER:
					$main_root_name = 'Categorie';
					switch($element_type_to_output){
						case 'leaf':{
							$pageHook = PAGE_HOOK_EVARISK_DANGERS;
							$element = EvaDanger::getDanger($selected_element_identifier);
							$directParent = categorieDangers::getCategorieDanger($element->id_categorie);
						}break;
						case 'node':{
							$pageHook = PAGE_HOOK_EVARISK_CATEGORIES_DANGERS;
							$directParent = categorieDangers::getCategorieDanger($selected_element_identifier);
						}break;
					}
				break;
				case TABLE_TACHE:
					$main_root_name = 'Tache';
					switch($element_type_to_output){
						case 'leaf':{
							$pageHook = PAGE_HOOK_EVARISK_ACTIVITE;
							$element = new EvaActivity();
							$element->setId($selected_element_identifier);
							$element->load();
							$element->limiteGauche = $directParent->leftLimit;
							$element->limiteDroite = $directParent->rightLimit;
							$directParent = new EvaTask();
							$directParent->setId($element->getRelatedTaskId());
							$directParent->load();
							$directParent->limiteGauche = $directParent->leftLimit;
							$directParent->limiteDroite = $directParent->rightLimit;
						}break;
						case 'node':{
							$pageHook = PAGE_HOOK_EVARISK_TACHE;
							$directParent = new EvaTask();
							$directParent->setId($selected_element_identifier);
							$directParent->load();
							$directParent->limiteGauche = $directParent->leftLimit;
							$directParent->limiteDroite = $directParent->rightLimit;
						}break;
					}
				break;
			}
			$ancetres = Arborescence::getAncetre($element_type, $directParent);
			foreach($ancetres as $ancetre){
				if(($ancetre->nom != $main_root_name . " Racine") && !in_array('node-main_table_' . $element_type . '-' . $ancetre->id, $_REQUEST['expanded'])){
					$tableArborescente .= '
		jQuery("#node-main_table_' . $element_type . '-' . $ancetre->id . '-name").children("span.expander").click(); /*	Ancester	*/';
				}
			}
			if((($element_type_to_output == 'leaf') || !empty($_REQUEST['idPere'])) && ($directParent->nom != $main_root_name . " Racine") && !in_array('node-main_table_' . $element_type . '-' . $directParent->id, $_REQUEST['expanded'])){
				$tableArborescente .= '
		jQuery("#node-main_table_' . $element_type . '-' . $directParent->id . '-name").children("span.expander").click(); /*	Direct parent	*/';
			}
		}

		$tableArborescente .= (isset($_GET['risk']) && ($_GET['risk'] != '')) ? '
		setTimeout(function(){ jQuery("#' . $_GET['risk'] . '").click(); },2000);' : '';

		$tableArborescente .= '
	});
</script>';

		return $tableArborescente;
	}
	/**
	* Returns the inner table of the list view tree with scripts that allow you to display the right part by clicking on the elements.
	* This recursive function path tree from the father element to his leaves.
	*
	* @param object $element The element to start tree for
	* @param string $element_type The element type we want to start tree for
	* @param boolean $output_info Specify if therer is an action column to output
	* @param string $output_table_id The html table identifier
	*
	* @return string HTML code of the inner table.
	*/
	function standard_tree_element($element_id, $element_type, $output_info, $output_table_id, $loop_nb = 1){
		$element_children_tree = $element_direct_children_tree = '';
		$ddFeuilleClass = 'feuilleArbre';
		$nomFeuilleClass = 'nomFeuilleArbre';
		$options = get_option('digirisk_tree_options');

		/*	Check the element type for getting good sub element to get	*/
		switch($element_type){
			case TABLE_GROUPEMENT:{
				$sub_element_type = TABLE_UNITE_TRAVAIL;
				$element_sub_children = EvaGroupement::getUnitesDuGroupement($element_id);
				$element = EvaGroupement::getGroupement($element_id);
				$element_direct_children = Arborescence::getFils($element_type, $element, "nom ASC");
				$actionSize = 5;
			}break;
			case TABLE_CATEGORIE_DANGER:{
				$sub_element_type = TABLE_DANGER;
				$element_sub_children = categorieDangers::getDangersDeLaCategorie($element_id);
				$element = categorieDangers::getCategorieDanger($element_id);
				$element_direct_children = Arborescence::getFils($element_type, $element, "nom ASC");
				$actionSize = 4;
				$options['digi_tree_load_complete_tree'] = 'oui';
			}break;
			case TABLE_TACHE:{
				$sub_element_type = TABLE_ACTIVITE;
				$element = new EvaTask($element_id);
				$element->load();
				$element->limiteGauche = $element->leftLimit;
				$element->limiteDroite = $element->rightLimit;
				$element_sub_children = $element->getWPDBActivitiesDependOn();
				$element_direct_children = Arborescence::getFils($element_type, $element, "nom ASC");
				$actionSize = 3;
			}break;
			case TABLE_GROUPE_QUESTION:{
				$sub_element_type = TABLE_QUESTION;
				$element_sub_children = EvaGroupeQuestions::getQuestionsDuGroupeQuestions($element->id);
				$element_direct_children = Arborescence::getFils($element_type, $element, "nom ASC");
				$actionSize = 3;
			}break;
		}

		/*	Get the direct children of the main element	*/
		if(count($element_direct_children) != 0){
			foreach($element_direct_children as $element_direct_children_type){
				$elements_fils = $elements_pere = 0;
				if(isset($element_direct_children_type->limiteGauche) && isset($element_direct_children_type->limiteDroite)){
					$elements_fils = Arborescence::getFils($element_type, $element_direct_children_type, "nom ASC");
					$elements_pere = Arborescence::getPere($element_type, $element_direct_children_type, " Status = 'Deleted' ");
				}

				$ddNoeudClass = 'noeudArbre';
				$nomNoeudClass = 'nomNoeudArbre';

				if(count($elements_pere) <= 0){
					$element_direct_children_type->nom = stripslashes($element_direct_children_type->nom);
					switch($element_type){
						case TABLE_GROUPEMENT:{
							$sousTable = TABLE_UNITE_TRAVAIL;
							$subElements = EvaGroupement::getUnitesDuGroupement($element_direct_children_type->id);
							$affichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_GP . $element_direct_children_type->id . '</span> - <span class="node_name" >' . $element_direct_children_type->nom . '</span>';

							$tdAddMainStyle = 'display:none;';
							$tdAddSecondaryStyle = 'display:none;';
							if(count($elements_fils) > 0){
								$tdAddMainStyle = '';
							}
							elseif(count($subElements) > 0){
								$tdAddSecondaryStyle = '';
							}
							elseif((count($elements_fils) == 0) && (count($subElements) == 0)){
								$tdAddMainStyle = '';
								$tdAddSecondaryStyle = '';
							}

							/*	Boutons d'ajouts d'un groupement ou d'une unit�	*/
							if(current_user_can('digi_add_groupement') || current_user_can('digi_add_groupement_groupement_' . $element_direct_children_type->id)){
								$tdAddMain = '<td class="noPadding addMain" id="addMain' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $tdAddMainStyle . '" src="' .PICTO_LTL_ADD_GROUPEMENT . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('un groupement', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('un groupement', 'evarisk')) . '" />';
							}
							else{
								$tdAddMain = '<td class="noPadding" >&nbsp;';
							}
							$tdAddMain .= '</td>';
							if(current_user_can('digi_add_unite') || current_user_can('digi_add_unite_groupement_' . $element_direct_children_type->id)){
								$tdAddSecondary = '<td class="noPadding addSecondary" id="addSecondary' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $tdAddSecondaryStyle . '" src="' .PICTO_LTL_ADD_UNIT . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('une unit&eacute; de travail', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('une unit&eacute; de travail', 'evarisk')) . '" />';
							}
							else{
								$tdAddSecondary = '<td class="noPadding" >&nbsp;';
							}
							$tdAddSecondary .= '</td>';

							if(current_user_can('digi_edit_groupement') || current_user_can('digi_edit_groupement_' . $element_direct_children_type->id)){
								$affichagePictoEvalRisque = (!AFFICHAGE_PICTO_EVAL_RISQUE) ? 'display:none;' : '';
								$tdEdit = '<td class="noPadding risq-node" id="risq-node' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $affichagePictoEvalRisque . '" src="' .PICTO_LTL_EVAL_RISK . '" alt="' . sprintf(__('Risques %s', 'evarisk'), __('du groupement', 'evarisk')) . '" title="' . sprintf(__('Risques %s', 'evarisk'), __('du groupement', 'evarisk')) . '" /></td><td class="noPadding edit-node" id="edit-node' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' .PICTO_EDIT . '" alt="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('le groupement', 'evarisk')) . '" title="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('le groupement', 'evarisk')) . '" /></td>';
							}
							elseif(current_user_can('digi_view_detail_groupement') || current_user_can('digi_view_detail_groupement_' . $element_direct_children_type->id)){
								$affichagePictoEvalRisque = (!AFFICHAGE_PICTO_EVAL_RISQUE) ? 'display:none;' : '';
								$tdEdit = '<td class="noPadding risq-node" id="risq-node' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $affichagePictoEvalRisque . '" src="' .PICTO_LTL_EVAL_RISK . '" alt="' . sprintf(__('Risques %s', 'evarisk'), __('du groupement', 'evarisk')) . '" title="' . sprintf(__('Risques %s', 'evarisk'), __('du groupement', 'evarisk')) . '" /></td><td class="noPadding edit-node" id="edit-node' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_VIEW . '" alt="' . sprintf(__('Voir %s', 'evarisk'), __('le groupement', 'evarisk')) . '" title="' . sprintf(__('Voir %s', 'evarisk'), __('le groupement', 'evarisk')) . '" /></td>';
							}
							else{
								$tdEdit = '<td colspan="2">&nbsp;</td>';
							}

							/*	Bouton de suppression d'un groupement */
							if(current_user_can('digi_delete_groupement') || current_user_can('digi_delete_groupement_' . $element_direct_children_type->id)){
								$tdDelete = '<td class="noPadding delete-node" id="delete-node' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_DELETE . '" alt="' . sprintf(__('Supprimer %s', 'evarisk'), __('le groupement', 'evarisk')) . '" title="' . sprintf(__('Supprimer %s', 'evarisk'), __('le groupement', 'evarisk')) . '" />';
							}
							else{
								$tdDelete = '<td class="noPadding" >&nbsp;';
							}
							$tdDelete .= '</td>';

							if(!current_user_can('digi_move_groupement')){
								$ddNoeudClass = '';
							}
							if(!current_user_can('digi_view_detail_groupement') && !current_user_can('digi_view_detail_groupement_' . $element_direct_children_type->id)
									&& !current_user_can('digi_edit_groupement') && !current_user_can('digi_edit_groupement_' . $element_direct_children_type->id)){
								$nomNoeudClass = 'userForbiddenActionCursor';
							}

							/*	Ajout des diff�rents boutons � l'interface	*/
							$actions = '
								' . $tdAddMain . '
								' . $tdAddSecondary . '
								' . $tdEdit . '
								' . $tdDelete;
						}break;
						case TABLE_CATEGORIE_DANGER:{
							$sousTable = TABLE_DANGER;
							$subElements = categorieDangers::getDangersDeLaCategorie($element_direct_children_type->id);
							$affichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_CD . $element_direct_children_type->id . '</span> - <span class="node_name" >' . $element_direct_children_type->nom . '</span>';
							$tdAddMainStyle = 'display:none;';
							$tdAddSecondaryStyle = 'display:none;';
							if(count($elements_fils) > 0){
								$tdAddMainStyle = '';
							}
							elseif(count($subElements) > 0){
								$tdAddSecondaryStyle = '';
							}
							elseif((count($elements_fils) == 0) && (count($subElements) == 0)){
								$tdAddMainStyle = '';
								$tdAddSecondaryStyle = '';
							}

							if(current_user_can('digi_add_danger_category')){
								$tdAddMain = '<td class="noPadding addMain" id="addMain' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $tdAddMainStyle . '" src="' .PICTO_LTL_ADD_CATEGORIE_DANGER . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('une cat&eacute;gorie de dangers', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('une cat&eacute;gorie de dangers', 'evarisk')) . '" /></td><td id="addMain' . $element_direct_children_type->id . 'Alt" style="display:none;">';
							}
							else{
								$tdAddMain = '<td class="noPadding >&nbsp;';
							}
							$tdAddMain .= '</td>';

							if(current_user_can('digi_add_danger')){
								$tdAddSecondary = '<td class="noPadding addSecondary" id="addSecondary' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $tdAddSecondaryStyle . '" src="' .PICTO_LTL_ADD_DANGER . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('un danger', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('un danger', 'evarisk')) . '" /></td><td id="addSecondary' . $element_direct_children_type->id . 'Alt" style="display:none;">';
							}
							else{
								$tdAddSecondary = '<td class="noPadding >&nbsp;';
							}
							$tdAddSecondary .= '</td>';

							if(current_user_can('digi_edit_danger_category')){
								$tdEdit = '<td class="noPadding edit-node" id="edit-node' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' .PICTO_EDIT . '" alt="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('la cat&eacute;gorie de dangers', 'evarisk')) . '" title="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('la cat&eacute;gorie de dangers', 'evarisk')) . '" />';
							}
							else{
								$tdEdit = '<td class="noPadding >&nbsp;';
							}
							$tdEdit .= '</td>';

							if(current_user_can('digi_delete_danger_category')){
								$tdDelete = '<td class="noPadding delete-node" id="delete-node' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' .PICTO_DELETE . '" alt="' . sprintf(__('Supprimer %s', 'evarisk'), __('la cat&eacute;gorie de dangers', 'evarisk')) . '" title="' . sprintf(__('Supprimer %s', 'evarisk'), __('la cat&eacute;gorie de dangers', 'evarisk')) . '" />';
							}
							else{
								$tdDelete = '<td class="noPadding >&nbsp;';
							}
							$tdDelete .= '</td>';

							if(!current_user_can('digi_move_danger_category')){
								$ddNoeudClass = '';
							}
							if(!current_user_can('digi_view_detail_danger_category')){
								$nomNoeudClass = 'userForbiddenActionCursor';
							}

							$actions = '
								' . $tdAddMain . '
								' . $tdAddSecondary . '
								' . $tdEdit . '
								' . $tdDelete;
						}break;
						case TABLE_TACHE:
							$sousTable = TABLE_ACTIVITE;
							$tache = new EvaTask($element_direct_children_type->id);
							$tache->load();
							$subElements = $tache->getWPDBActivitiesDependOn();
							$affichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_T . $element_direct_children_type->id . '</span> - <span class="node_name" >' . $element_direct_children_type->nom . '</span>';
							$tdAddMainStyle = 'display:none;';
							$tdAddSecondaryStyle = 'display:none;';
							if(count($elements_fils) > 0){
								$tdAddMainStyle = '';
							}
							elseif(count($subElements) > 0){
								$tdAddSecondaryStyle = '';
							}
							elseif((count($elements_fils) == 0) && (count($subElements) == 0)){
								$tdAddMainStyle = '';
								$tdAddSecondaryStyle = '';
							}

							if(current_user_can('digi_add_task')){
								$tdAddMain = '<td class="noPadding addMain" id="addMain' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $tdAddMainStyle . '" src="' .PICTO_LTL_ADD_TACHE . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('une t&acirc;che', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('une t&acirc;che', 'evarisk')) . '" />';
							}
							else{
								$tdAddMain = '<td class="noPadding" >&nbsp;';
							}
							$tdAddMain .= '</td>';

							if(current_user_can('digi_add_action')){
								$tdAddSecondary = '<td class="noPadding addSecondary" id="addSecondary' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $tdAddSecondaryStyle . '" src="' .PICTO_LTL_ADD_ACTIVITE . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('une action', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('une action', 'evarisk')) . '" />';
							}
							else{
								$tdAddSecondary = '<td class="noPadding" >&nbsp;';
							}
							$tdAddSecondary .= '</td>';

							if(current_user_can('digi_edit_task') || current_user_can('digi_edit_task_' . $element_direct_children_type->id)){
								$tdEdit = '<td class="noPadding edit-node" id="edit-node' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';"src="' .PICTO_EDIT . '" alt="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('la t&acirc;che', 'evarisk')) . '" title="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('la t&acirc;che', 'evarisk')) . '" />';
							}
							elseif(current_user_can('digi_view_detail_task') || current_user_can('digi_view_task_' . $element_direct_children_type->id)){
								$tdEdit = '<td class="noPadding edit-node" id="edit-node' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_VIEW . '" alt="' . sprintf(__('Voir %s', 'evarisk'), __('la t&acirc;che', 'evarisk')) . '" title="' . sprintf(__('Voir %s', 'evarisk'), __('la t&acirc;che', 'evarisk')) . '" />';
							}
							else{
								$tdEdit = '<td class="noPadding" >&nbsp;';
							}
							$tdEdit .= '</td>';

							if(current_user_can('digi_delete_task') || current_user_can('digi_delete_task_' . $element_direct_children_type->id)){
								$tdDelete = '<td class="noPadding delete-node" id="delete-node' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_DELETE . '" alt="' . sprintf(__('&Eacute;ffacer %s', 'evarisk'), __('la t&acirc;che', 'evarisk')) . '" title="' . sprintf(__('&Eacute;ffacer %s', 'evarisk'), __('la t&acirc;che', 'evarisk')) . '" />';
							}
							else{
								$tdDelete = '<td class="noPadding" >&nbsp;';
							}
							$tdDelete .= '</td>';

							if(!current_user_can('digi_move_task')){
								$ddNoeudClass = '';
							}

							if(!current_user_can('digi_view_detail_task') && !current_user_can('digi_view_task_' . $element_direct_children_type->id) && !current_user_can('digi_edit_task') && !current_user_can('digi_edit_task_' . $element_direct_children_type->id)){
								$nomNoeudClass = 'userForbiddenActionCursor';
							}

							$actions = '
								' . $tdAddMain . '
								' . $tdAddSecondary . '
								' . $tdEdit . '
								' . $tdDelete;
						break;
						case TABLE_GROUPE_QUESTION:
							$sousTable = TABLE_QUESTION;
							$subElements = EvaGroupeQuestions::getQuestionsDuGroupeQuestions($element_direct_children_type->id);
							$affichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_GQ . $element_direct_children_type->id . '</span> - ' . $element_direct_children_type->code . '-' . ucfirst($element_direct_children_type->nom);
							$tdAdd = '<td class="noPadding addMain" id="add-node-' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="'.PICTO_INSERT.'" alt="' . __('Inserer sous le titre', 'evarisk') . '" title="Inserer sous le titre" /></td>';
							$tdEdit = '<td class="noPadding edit-node" id="edit-node-' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_EDIT . '" alt="Modifier le titre" title="' . __('Modifier le titre', 'evarisk') . '" /></td>';
							$tdDelete = '<td class="noPadding delete-node" id="delete-node-' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_DELETE . '" alt="Effacer le titre" title="' . sprintf(__('&Eacute;ffacer %s', 'evarisk'), __('le titre', 'evarisk')) . '" />';
							$actions = $tdAdd . $tdEdit . $tdDelete;
						break;
					}
					$trouveElement = count($elements_fils) + count($subElements);

					$info = '';
					if($output_info){
						$info = digirisk_display::get_info_column_content($element_type, $element_direct_children_type->id);
						$info = '
			<td id="info-' . $element_direct_children_type->id . '" class="' . $info['class'] . '">' . $info['value'] . '</td>';
					}

					$element_children_tree .= '
		<tr id="node-' . $output_table_id . '-' . $element_direct_children_type->id . '" class="' . (($loop_nb > 0) || ($options['digi_tree_load_complete_tree'] == 'oui') ? 'child-of-node-' . $output_table_id . '-' . $element->id : '') . ' ' . $ddNoeudClass . ' parent">
			<td id="node-' . $output_table_id . '-' . $element_direct_children_type->id . '-name" class="' . $nomNoeudClass . '" >' . $affichage . '</td>
			' . $info . $actions . '
		</tr>';

					if($trouveElement){
						if($options['digi_tree_load_complete_tree'] == 'oui'){
							$element_children_tree .= digirisk_display::standard_tree_element($element_direct_children_type->id , $element_type, $output_info, $output_table_id);
						}
						else{
							$element_children_tree .= '
		<tr id="node-' . $output_table_id . '-' . $element_direct_children_type->id . '_load" class="child-of-node-' . $output_table_id . '-' . $element_direct_children_type->id . ' ' . $ddNoeudClass . ' parent">
			<td colspan="' . (1 + (($output_info) ? ($actionSize + 1) : $actionSize)) . '" id="node-' . $output_table_id . '-' . $element_direct_children_type->id . '-name_load" class="' . $nomNoeudClass . '" ><img src="' . admin_url('images/loading.gif') . '" alt="loading children tree node" title="loading in progress, please wait" /></td>
		</tr>';
						}
					}
				}
			}
		}

		/*	If element has children that is not	*/
		if(count($element_sub_children) != 0){
			foreach($element_sub_children as $subElement){
				$ddFeuilleClass = 'feuilleArbre';
				$nomFeuilleClass = 'nomFeuilleArbre';
				switch($element_type){
					case TABLE_GROUPEMENT:{
						$affichagePictoEvalRisque = (!AFFICHAGE_PICTO_EVAL_RISQUE) ? 'display:none;' : '';

						/*	Check user right for element edition	*/
						$tdSubEdit = '
								<td colspan="2">&nbsp;</td>';
						if(current_user_can('digi_edit_unite') || current_user_can('digi_edit_unite_' . $subElement->id)){
							$tdSubEdit .= '
								<td class="noPadding risk-leaf" id="risq-leaf' . $subElement->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $affichagePictoEvalRisque . '" src="' .PICTO_LTL_EVAL_RISK . '" alt="' . sprintf(__('Risques %s', 'evarisk'), __('de l\'unit&eacute; de travail', 'evarisk')) . '" title="' . sprintf(__('Risques %s', 'evarisk'), __('de l\'unit&eacute; de travail', 'evarisk')) . '" /></td><td class="noPadding edit-leaf" id="edit-leaf' . $subElement->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_EDIT . '" alt="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('l\'unit&eacute; de travail', 'evarisk')) . '" title="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('l\'unit&eacute; de travail', 'evarisk')) . '" /></td>';
						}
						elseif(current_user_can('digi_view_detail_unite') || current_user_can('digi_view_detail_unite_' . $subElement->id)){
							$tdSubEdit .= '
								<td class="noPadding risk-leaf" id="risq-leaf' . $subElement->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $affichagePictoEvalRisque . '" src="' .PICTO_LTL_EVAL_RISK . '" alt="' . sprintf(__('Risques %s', 'evarisk'), __('de l\'unit&eacute; de travail', 'evarisk')) . '" title="' . sprintf(__('Risques %s', 'evarisk'), __('de l\'unit&eacute; de travail', 'evarisk')) . '" /></td><td class="noPadding edit-leaf" id="edit-leaf' . $subElement->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_VIEW . '" alt="' . sprintf(__('Voir %s', 'evarisk'), __('l\'unit&eacute; de travail', 'evarisk')) . '" title="' . sprintf(__('Voir %s', 'evarisk'), __('l\'unit&eacute; de travail', 'evarisk')) . '" /></td>';
						}
						else{
							$tdSubEdit .= '<td colspan="2">&nbsp;</td>';
						}

						/*	Check user right for element deletion	*/
						if(current_user_can('digi_delete_unite') || current_user_can('digi_delete_unite_' . $subElement->id)){
							$tdSubDelete = '<td class="noPadding delete-leaf" id="delete-leaf' . $subElement->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';"  src="' . PICTO_DELETE . '" alt="' . sprintf(__('Supprimer %s', 'evarisk'), __('l\'unit&eacute; de travail', 'evarisk')) . '" title="' . sprintf(__('Supprimer %s', 'evarisk'), __('l\'unit&eacute; de travail', 'evarisk')) . '" />';
						}
						else{
							$tdSubDelete = '<td class="noPadding" >&nbsp;';
						}
						$tdSubDelete .= '</td>';

						$subAffichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_UT .  $subElement->id . ' - </span><span class="leaf_name" >' . $subElement->nom . '</span>';
						$subActions = $tdSubEdit . $tdSubDelete;

						/*	Check user right for element move in tree	*/
						if(!current_user_can('digi_move_unite')){
							$ddFeuilleClass = '';
						}

						/*	Check user right for edition and visualisation of element on line element	*/
						if(!current_user_can('digi_view_detail_unite') && !current_user_can('digi_view_detail_unite_' . $subElement->id)
							&& !current_user_can('digi_edit_unite') && !current_user_can('digi_edit_unite_' . $subElement->id)){
							$nomFeuilleClass = 'userForbiddenActionCursor';
						}

					}break;
					case TABLE_CATEGORIE_DANGER:{
						$tdSubEdit = '
								<td colspan="2">&nbsp;</td>
								<td class="noPadding edit-leaf" id="edit-leaf' . $subElement->id . '">';
						if(current_user_can('digi_edit_danger')){
							$tdSubEdit .= '<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_EDIT . '" alt="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('le danger', 'evarisk')) . '" title="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('le danger', 'evarisk')) . '" />';
						}
						else{
							$tdSubEdit .= '&nbsp;';
						}
						$tdSubEdit .= '</td>';
						$tdSubDelete = '<td class="noPadding delete-leaf" id="delete-leaf' . $subElement->id . '">';
						if(current_user_can('digi_delete_danger')){
							$tdSubDelete .= '<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_DELETE . '" alt="' . sprintf(__('Supprimer %s', 'evarisk'), __('le danger', 'evarisk')) . '" title="' . sprintf(__('Supprimer %s', 'evarisk'), __('le danger', 'evarisk')) . '" />';
						}
						else{
							$tdSubDelete .= '&nbsp;';
						}
						$tdSubDelete .= '</td>';
						$subAffichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_D .  $subElement->id . ' - </span><span class="leaf_name" >' . $subElement->nom . '</span>';
						$subActions = $tdSubEdit . $tdSubDelete;
						if(!current_user_can('digi_move_danger')){
							$ddFeuilleClass = '';
						}
						if(!current_user_can('digi_view_detail_danger')){
							$nomFeuilleClass = 'userForbiddenActionCursor';
						}
					}break;
					case TABLE_TACHE:{
						$tdSubEdit = '
								<td colspan="2">&nbsp;</td>';
						if(current_user_can('digi_edit_action')){
							$tdSubEdit .= '
								<td class="noPadding edit-leaf" id="edit-leaf' . $subElement->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_EDIT . '" alt="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('l\'action', 'evarisk')) . '" title="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('l\'action', 'evarisk')) . '" /></td>';
						}
						elseif(current_user_can('digi_view_detail_action')){
							$tdSubEdit .= '
								<td class="noPadding edit-leaf" id="edit-leaf' . $subElement->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_VIEW . '" alt="' . sprintf(__('Voir %s', 'evarisk'), __('l\'action', 'evarisk')) . '" title="' . sprintf(__('Voir %s', 'evarisk'), __('l\'action', 'evarisk')) . '" /></td>';
						}
						else{
							$tdSubEdit .= '<td class="noPadding" >&nbsp;</td>';
						}

						if(current_user_can('digi_delete_action')){
							$tdSubDelete = '<td class="noPadding delete-leaf" id="delete-leaf' . $subElement->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';"  src="' . PICTO_DELETE . '" alt="' . sprintf(__('Supprimer %s', 'evarisk'), __('l\'action', 'evarisk')) . '" title="' . sprintf(__('Supprimer %s', 'evarisk'), __('l\'action', 'evarisk')) . '" />';
						}
						else{
							$tdSubDelete = '<td class="noPadding" >&nbsp;';
						}
						$tdSubDelete .= '</td>';
						$subAffichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_ST .  $subElement->id . ' - </span><span class="leaf_name" >' . $subElement->nom . '</span>';
						$subActions = $tdSubEdit . $tdSubDelete;
						if(!current_user_can('digi_move_action')){
							$ddFeuilleClass = '';
						}
						if(!current_user_can('digi_view_detail_action') && !current_user_can('digi_edit_action')){
							$nomFeuilleClass = 'userForbiddenActionCursor';
						}
					}break;
					case TABLE_GROUPE_QUESTION:{
						$tdSubDelete = '
								<td colspan="2">&nbsp;</td>
								<td class="noPadding delete-leaf" id="delete-leaf-' . $subElement->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_DELETE . '" alt="' . sprintf(__('&Eacute;ffacer %s', 'evarisk'), __('la question', 'evarisk')) . '" title="' . sprintf(__('&Eacute;ffacer %s', 'evarisk'), __('la question', 'evarisk')) . '" /></td>';
						$subAffichage = ELEMENT_IDENTIFIER_Q . $subElement->id . ' : ' . ucfirst($subElement->enonce);
						$subActions = $tdSubDelete;
					}break;
				}
				$element_direct_children_tree .= '
		<tr id="leaf-' . $subElement->id . '" class="cursormove child-of-node-' . $output_table_id . '-' . $element->id . ' ' . $ddFeuilleClass . '">
			<td id="leaf-' . $subElement->id . '-name" class="' . $nomFeuilleClass . '" >' . $subAffichage . '</td>';
					if($output_info){
						$info = digirisk_display::get_info_column_content($sub_element_type, $subElement->id);
						$element_direct_children_tree .= '
			<td class="' . $info['class'] . '">' . $info['value'] . '</td>';
					}
					$element_direct_children_tree .= $subActions . '
		</tr>';
			}
		}

		return $element_children_tree . $element_direct_children_tree;
	}

	/**
	* Returns information on an element to be displayed in the list view.
	*
	* @param string $table Element table name.
	* @param int $elementId Identifier of the element.
	*
	* @return string The information.
	*/
	function get_info_column_content($table, $elementId, $more_info = false){
		$info=array();
		switch($table){
			case TABLE_DANGER :
				$info['title'] = __('Cat&eacute;gories de danger', 'evarisk');
				$info['value'] = '';
				$info['class'] = 'treeTableInfoColumn';
				if(!current_user_can('digi_view_detail_danger')){
					$info['class'] = 'userForbiddenActionCursor';
				}
			break;
			case TABLE_CATEGORIE_DANGER :
				$info['title'] = __('Cat&eacute;gories de danger', 'evarisk');
				$info['value'] = '';
				$info['class'] = 'treeTableGroupInfoColumn';
				if(!current_user_can('digi_view_detail_danger_category')){
					$info['class'] = 'userForbiddenActionCursor';
				}
			break;
			case TABLE_TACHE :
				$info['title'] = __('T&acirc;ches', 'evarisk');
				$tache = new EvaTask($elementId);
				$tache->load();
				$moreInfo = '';
				if($more_info){
				 $moreInfo = '&nbsp;-&nbsp;&nbsp;' . __('D&eacute;but', 'evarisk') . '&nbsp;' . mysql2date('d M Y', $tache->getStartDate(), true) . '&nbsp;-&nbsp;' . __('Fin', 'evarisk') . '&nbsp;' . mysql2date('d M Y', $tache->getFinishDate(), true);
				}
				$info['value'] = $tache->getProgression() . '%&nbsp;(' . actionsCorrectives::check_progression_status_for_output($tache->getProgressionStatus()) . ')' . $moreInfo;
				$info['class'] = 'treeTableGroupInfoColumn taskInfoContainer-' . $elementId;
				if(!current_user_can('digi_view_detail_task') && !current_user_can('digi_view_detail_task_' . $elementId) && !current_user_can('digi_edit_task') && !current_user_can('digi_edit_task_' . $elementId)){

					$info['class'] = 'userForbiddenActionCursor taskInfoContainer-' . $elementId;
				}
			break;
			case TABLE_ACTIVITE :
				$info['title'] = __('T&acirc;ches', 'evarisk');
				$action = new EvaActivity($elementId);
				$action->load();
				$moreInfo = '';
				if($more_info){
				 $moreInfo = '&nbsp;-&nbsp;&nbsp;' . __('D&eacute;but', 'evarisk') . '&nbsp;' . mysql2date('d M Y', $action->getStartDate(), true) . '&nbsp;-&nbsp;' . __('Fin', 'evarisk') . '&nbsp;' . mysql2date('d M Y', $action->getFinishDate(), true);
				}
				$info['value'] = $action->getProgression() . '%&nbsp;(' . actionsCorrectives::check_progression_status_for_output($action->getProgressionStatus()) . ')' . $moreInfo;
				$info['class'] = 'treeTableInfoColumn activityInfoContainer-' . $elementId;
				if(!current_user_can('digi_view_detail_action') && !current_user_can('digi_view_detail_action_' . $elementId) && !current_user_can('digi_edit_action') && !current_user_can('digi_edit_action_' . $elementId)){
					$info['class'] = 'userForbiddenActionCursor activityInfoContainer-' . $elementId;
				}
			break;
			case TABLE_GROUPEMENT :
				$info['title'] = __('Groupements', 'evarisk');
				$scoreRisqueGroupement = 0;
				$riskAndSubRisks = eva_documentUnique::listRisk($table, $elementId);
				foreach($riskAndSubRisks as $risk){
					$scoreRisqueGroupement += $risk[2]['value'];
				}
				$info['value'] = '<span id="LeftRiskSum' . $table . $elementId . '" >' . $scoreRisqueGroupement . '</span>&nbsp;-&nbsp;<span id="LeftRiskNb' . $table . $elementId . '" >' . count($riskAndSubRisks) . '</span> ' . __('risque(s)', 'evarisk');
				$info['class'] = 'treeTableGroupInfoColumn';
				if(!current_user_can('digi_view_detail_groupement') && !current_user_can('digi_view_detail_groupement_' . $elementId) && !current_user_can('digi_edit_groupement') && !current_user_can('digi_edit_groupement_' . $elementId)){
					$info['class'] = 'userForbiddenActionCursor';
				}
			break;
			case TABLE_UNITE_TRAVAIL :
				$info['title'] = __('Groupements', 'evarisk');
				$scoreRisqueGroupement = 0;
				$riskAndSubRisks = eva_documentUnique::listRisk($table, $elementId);
				foreach($riskAndSubRisks as $risk){
					$scoreRisqueGroupement += $risk[2]['value'];
				}
				$info['value'] = '<span id="LeftRiskSum' . $table . $elementId . '" >' . $scoreRisqueGroupement . '</span>&nbsp;-&nbsp;<span id="LeftRiskNb' . $table . $elementId . '" >' . count($riskAndSubRisks) . '</span> ' . __('risque(s)', 'evarisk');
				$info['class'] = 'treeTableInfoColumn';
				if(!current_user_can('digi_view_detail_unite') && !current_user_can('digi_view_detail_unite_' . $elementId) && !current_user_can('digi_edit_unite') && !current_user_can('digi_edit_unite_' . $elementId)){
					$info['class'] = 'userForbiddenActionCursor';
				}
			break;
			default:
				$info['title'] = '';
				$info['value'] = '';
				$info['class'] = '';
			break;
		}

		return $info;
	}

	/**
	* Returns the list view tree table with scripts that allow you to display the right part by clicking on the elements and the drag and drop.
	*
	* @see getCorpsTableArborescence
	*
	* @param string $element_type Table name.
	* @param int $output_table_id HTML Id attribute for the table.
	* @param string $nomRacine Text to be displayed in the root of the table.
	*
	* @return string HTML code of the table.
	*/
	function standard_configuration_tree($element_type, $output_table_id, $nomRacine, $draggable = true, $outputAction = true){
		$infoRacine = $actions = $script_action = $tableArborescente = '';
		$showTrashUtilities = $output_info = false;

		switch($element_type){
			case TABLE_CATEGORIE_PRECONISATION:{
				$sub_element_type = TABLE_PRECONISATION;
				$divDeChargement = 'message';
				$titreInfo = null;
				$actionSize = 4;
				$main_tree_id = $element_type;
				if($outputAction){
					$actions = '
							<td class="noPadding addMain" id="addMain' . $element_type . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . EVA_IMG_ICONES_PLUGIN_URL . 'add_vs.png" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('une m&eacute;thode', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('une m&eacute;thode', 'evarisk')) . '" /></td>';
					/*	Add trash	*/
					$main_option = get_option('digirisk_options');
					if(($main_option['digi_activ_trash'] == 'oui') && (current_user_can('digi_view_recommandation_category_trash') || current_user_can('digi_view_recommandation_trash'))){
						$showTrashUtilities = true;
						$actions .= '
							<td colspan="' . ($actionSize - 2) . '" >&nbsp;</td>
							<td class="noPadding trash" id="trash' . $element_type . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';"  src="' . EVA_IMG_ICONES_PLUGIN_URL . 'trash.png" alt="Trash" title="' . __('Acc&eacute;der &agrave; la corbeille', 'evarisk') . '" /></td>';
					}
					else{
						$actions .= '
							<td colspan="' . ($actionSize - 1) . '" >&nbsp;</td>';
					}

					$script_action = '
		table_to_treeTable("' . $output_table_id . '", "' . $main_tree_id . '", "' . $element_type . '", "' . $sub_element_type . '", "");
		main_tree_action_node("' . $output_table_id . '", "' . $element_type . '", "' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer cet &eacute;l&eacute;ment?\r\nATTENTION: si cet &eacute;l&eacute;ment poss&egrave;de des sous-&eacute;l&eacute;ment, ils seront inaccessibles', 'evarisk') . '");
		main_tree_action_leaf("' . $output_table_id . '", "' . $sub_element_type . '", "' . __('Etes vous sur de vouloir supprimer cet element?', 'evarisk') . '");';
				}

				$output_info = true;
				$titreInfo = __('Ic&ocirc;ne', 'evarisk');
			}break;
			case TABLE_METHODE:{
				$divDeChargement = 'message';
				$titreInfo = null;
				$actionSize = 4;
				$main_tree_id = $element_type;
				if($outputAction){
					if(current_user_can('digi_add_method')){
						$actions = '
							<td class="noPadding addMain" id="addMain' . $element_type . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . EVA_IMG_ICONES_PLUGIN_URL . 'add_vs.png" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('une m&eacute;thode', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('une m&eacute;thode', 'evarisk')) . '" /></td>';
					}
					else{
						$actions = '
							<td class="noPadding addMain" id="addMain' . $element_type . '" >&nbsp;</td>';
					}

					/*	Add trash	*/
					$main_option = get_option('digirisk_options');
					if(($main_option['digi_activ_trash'] == 'oui') && current_user_can('digi_view_method_trash')){
						$showTrashUtilities = true;
						$actions .= '
							<td colspan="' . ($actionSize - 2) . '" >&nbsp;</td>
							<td class="noPadding trash" id="trash' . $element_type . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';"  src="' . EVA_IMG_ICONES_PLUGIN_URL . 'trash.png" alt="Trash" title="' . __('Acc&eacute;der &agrave; la corbeille', 'evarisk') . '" /></td>';
					}
					else{
						$actions .= '
							<td colspan="' . ($actionSize - 1) . '" >&nbsp;</td>';
					}

					$script_action = '
		action_on_add_button("' . $output_table_id . '", "' . $element_type . '", "", "");
		main_tree_action_node("' . $output_table_id . '", "' . $element_type . '", "' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer cet &eacute;l&eacute;ment?', 'evarisk') . '");';
				}

				$output_info = true;
				$titreInfo = __('Formule', 'evarisk');
			}break;
			case TABLE_MENU:{
				$divDeChargement = 'message';
				$titreInfo = null;
				$actionSize = 4;
				$main_tree_id = $element_type;
				if($outputAction){
					if(current_user_can('digi_add_manu')){
						$actions = '
							<td class="noPadding addMain" id="addMain' . $element_type . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $tdAddSecondaryStyle . '" src="' . EVA_IMG_ICONES_PLUGIN_URL . 'add_vs.png" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('un menu', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('un menu', 'evarisk')) . '" /></td>';
					}
					else{
						$actions = '
							<td class="noPadding addMain" id="addMain' . $element_type . '" >&nbsp;</td>';
					}

					/*	Add trash	*/
					$main_option = get_option('digirisk_options');
					if(($main_option['digi_activ_trash'] == 'oui') && current_user_can('digi_view_menu_trash')){
						$showTrashUtilities = true;
						$actions .= '
							<td colspan="' . ($actionSize - 2) . '" >&nbsp;</td>
							<td class="noPadding trash" id="trash' . $element_type . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';"  src="' . EVA_IMG_ICONES_PLUGIN_URL . 'trash.png" alt="Trash" title="' . __('Acc&eacute;der &agrave; la corbeille', 'evarisk') . '" /></td>';
					}
					else{
						$actions .= '
							<td colspan="' . ($actionSize - 1) . '" >&nbsp;</td>';
					}

					$script_action = '
		action_on_add_button("' . $output_table_id . '", "' . $element_type . '", "' . $sub_element_type . '", "");
		main_tree_action_node("' . $output_table_id . '", "' . $element_type . '", "' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer cet &eacute;l&eacute;ment?', 'evarisk') . '");';
				}
			}break;
		}

		/*	Add trash utilities if user is allowed	*/
		$trashScript = '';
		if($showTrashUtilities){
			$tableArborescente .= '
				<div id="trashContainer" title="' . __('Liste des &eacute;l&eacute;ments supprim&eacute;s', 'evarisk') . '" >&nbsp;</div>';
			$trashScript = '

		main_tree_trash("' . $element_type . '");';
		}

		/*	Add support for dragging element on treee	*/
		$draggableScript = '';
		if($draggable){
			$draggableScript .= '

		main_tree_draggable("' . $output_table_id . '", "' . $element_type . '", "' . $divDeChargement . '", "' . __('Transfert en cours...', 'evarisk') . '");';
		}

		$tableArborescente .= '
<table id="' . $output_table_id . '" cellspacing="0" class="widefat post fixed digirisk_main_table digirisk_config_treetable" >
	<thead>
		<tr>
			<th>' . $nomRacine . '</th>';
		if($output_info){
			$infoRacine = digirisk_display::get_info_column_content($element_type, $main_tree_id);
			$tableArborescente .= '
			<th class="infoList" >' . $titreInfo . '</th>';
			$infoRacine = '
			<td id="info-' . $main_tree_id . '" class="' . $infoRacine['class'] . '" >&nbsp;</td>';
		}
		$tableArborescente .= ($outputAction ? '
			<th colspan="' . $actionSize . '" class="actionButtonList" ><div class="action_column_name" >' . __('Actions', 'evarisk') . '</div><div class="main_metabox_collapser" >&nbsp;</div></th>' : '');
		$tableArborescente .= '
		</tr>
	</thead>
	<tbody>
		<tr id="node-' . $output_table_id . '-' . $main_tree_id . '" class="parent racineArbre">
			<td id="tdRacine' . $output_table_id . '">&nbsp;</td>' . $infoRacine . $actions . '
		</tr>
		' . digirisk_display::standard_configuration_tree_element($main_tree_id, $element_type, $output_info, $output_table_id, 1) . '
	</tbody>
</table>
<script type="text/javascript" >
	digirisk(document).ready(function(){

		' . $script_action . '
		' . $trashScript . '
		' . $draggableScript;

		/*	Automatically open an element and all it's parent	*/
		if(!empty($_REQUEST['idPere'])){
			$element = EvaGroupement::getGroupement($_REQUEST['idPere']);
			$parent_arborescence = Arborescence::getAncetre($_REQUEST['table'], $element);
			if(is_array($parent_arborescence) && !empty($parent_arborescence)){
				foreach($parent_arborescence as $selected_element_ancester){
					if(!in_array('node-main_table_' . $_REQUEST['table'] . '-' . $selected_element_ancester->id, $_REQUEST['expanded'])){
						$tableArborescente .= '
			jQuery("#node-main_table_' . $_REQUEST['table'] . '-' . $selected_element_ancester->id . '-name").children("span.expander").click();/* Expand ancester */';
					}
				}
			}
			if( !empty( $_REQUEST['expanded'] ) && is_array( $_REQUEST['expanded'] ) && !in_array('node-main_table_' . $_REQUEST['table'] . '-' . $_REQUEST['idPere'] . '-name', $_REQUEST['expanded'])){
				$tableArborescente .= '
		jQuery("#node-main_table_' . $_REQUEST['table'] . '-' . $_REQUEST['idPere'] . '-name").children("span.expander").click();';
			}
		}
		if(!empty($_REQUEST['elt'])){
			$tableArborescente .= '
		jQuery("#' . $_REQUEST['elt'] . '").addClass("edited");';
		}

		/*	Automatically open an element and all it's parent	*/
		if(isset($_REQUEST['expanded']) && ($_REQUEST['expanded'] != '')){
			foreach($_REQUEST['expanded'] as $element_to_expand){
				$tableArborescente .= '
		jQuery("#' . $element_to_expand . '-name").children("span.expander").click();/* Expanded */';
			}
		}

		$tableArborescente .= '
	});
</script>';

		return $tableArborescente;
	}
	/**
	* Returns the inner table of the list view tree with scripts that allow you to display the right part by clicking on the elements.
	* This recursive function path tree from the father element to his leaves.
	*
	* @param object $element The element to start tree for
	* @param string $element_type The element type we want to start tree for
	* @param boolean $output_info Specify if therer is an action column to output
	* @param string $output_table_id The html table identifier
	*
	* @return string HTML code of the inner table.
	*/
	function standard_configuration_tree_element($element_id, $element_type, $output_info, $output_table_id, $loop_nb = 1){
		$element_children_tree = $element_direct_children_tree = '';
		$ddFeuilleClass = 'feuilleArbre';
		$nomFeuilleClass = 'nomFeuilleArbre';
		$options = get_option('digirisk_tree_options');

		/*	Check the element type for getting good sub element to get	*/
		switch($element_type){
			case TABLE_CATEGORIE_PRECONISATION:{
				$sub_element_type = TABLE_PRECONISATION;
				$element_sub_children = array();
				if($loop_nb == 1){
					$element_direct_children = evaRecommandationCategory::getCategoryRecommandationList();
				}
				elseif((int)$element_id >= 1){
					$element_sub_children = evaRecommandation::getRecommandationListByCategory($element_id, 'list');
					$element_direct_children = array();
				}
				$actionSize = 4;
			}break;
			case TABLE_METHODE:{
				$sub_element_type = '';
				$element_sub_children = array();
				$element_direct_children = MethodeEvaluation::getMethods("Status = 'Valid'");
				$actionSize = 4;
			}break;
			case TABLE_MENU:{
				$sub_element_type = '';
				$element_sub_children = array();
				$element_direct_children = digirisk_menu::get_element("'valid'");
				$actionSize = 3;
			}break;
		}

		/*	Get the direct children of the main element	*/
		if(count($element_direct_children) != 0){
			foreach($element_direct_children as $element_direct_children_type){
				$ddNoeudClass = 'noeudArbre';
				$nomNoeudClass = 'nomNoeudArbre';
				$result_info = $result_info_class = '';

				$element_direct_children_type->nom = stripslashes($element_direct_children_type->nom);
				switch($element_type){
					case TABLE_CATEGORIE_PRECONISATION:{
						$sousTable = TABLE_PRECONISATION;
						$element_sub_children = evaRecommandation::getRecommandationListByCategory($element_direct_children_type->id, 'list');
						$affichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_CP . $element_direct_children_type->id . '</span> - <span class="node_name" >' . $element_direct_children_type->nom . '</span>';

						$tdAddMain = '<td class="noPadding" >&nbsp;</td>';

						if(current_user_can('digi_add_recommandation')){
							$tdAddSecondary = '<td class="noPadding addSecondary" id="addSecondary' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . EVA_IMG_ICONES_PLUGIN_URL . 'add_vs.png" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('une pr&eacute;conisation', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('une pr&eacute;conisation', 'evarisk')) . '" />';
						}
						else{
							$tdAddSecondary = '<td class="noPadding >&nbsp;';
						}
						$tdAddSecondary .= '</td>';

						if(current_user_can('digi_edit_recommandation')){
							$tdEdit = '<td class="noPadding edit-node" id="edit-node-' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_EDIT . '" alt="' . __('Modifier la cat&eacute;gorie de pr&eacute;conisation', 'evarisk') . '" title="' . __('Modifier la cat&eacute;gorie de pr&eacute;conisation', 'evarisk') . '" />';
						}
						else{
							$tdEdit = '<td class="noPadding >&nbsp;';
						}
						$tdEdit .= '</td>';

						if(current_user_can('digi_delete_recommandation')){
							$tdDelete = '<td class="noPadding delete-node" id="delete-node-' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_DELETE . '" alt="' . sprintf(__('&Eacute;ffacer %s', 'evarisk'), __('la cat&eacute;gorie de pr&eacute;conisation', 'evarisk')) . '" title="' . sprintf(__('&Eacute;ffacer %s', 'evarisk'), __('la cat&eacute;gorie de pr&eacute;conisation', 'evarisk')) . '" />';
						}
						else{
							$tdDelete = '<td class="noPadding >&nbsp;';
						}
						$tdDelete .= '</td>';

						$actions = $tdAddMain . $tdAddSecondary . $tdEdit . $tdDelete;

						$recommandationMainPicture = evaPhoto::checkIfPictureIsFile($element_direct_children_type->photo, $element_type, true);
						$result_info = '<img class="recommandationDefaultPictosList" style="width:' . TAILLE_PICTOS . ';" src="' . EVA_RECOMMANDATION_ICON . '" alt="' . sprintf(__('Photo par d&eacute;faut pour %s', 'evarisk'), $element_direct_children_type->nom) . '" />';
						if(!empty($recommandationMainPicture)){
							$result_info = '<img class="recommandationDefaultPictosList" src="' . $recommandationMainPicture . '" alt="' . ucfirst(strtolower($element_direct_children_type->nom)) . '" title="' . ELEMENT_IDENTIFIER_P . $element_direct_children_type->id . '&nbsp;-&nbsp;' . ucfirst(strtolower($element_direct_children_type->nom)) . '" />';
						}

						$trouveElement = count($element_sub_children);
					}break;
					case TABLE_METHODE:{
						$affichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_ME . $element_direct_children_type->id . '</span> - <span class="node_name" >' . $element_direct_children_type->nom . '</span>';

						$tdAddMain = '<td class="noPadding" >&nbsp;</td>';
						$tdAddSecondary = '<td class="noPadding" >&nbsp;</td>';

						if(current_user_can('digi_edit_method')){
							$tdEdit = '<td class="noPadding edit-node" id="edit-node-' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_EDIT . '" alt="' . __('Modifier la cat&eacute;gorie de pr&eacute;conisation', 'evarisk') . '" title="' . __('Modifier la cat&eacute;gorie de pr&eacute;conisation', 'evarisk') . '" />';
						}
						else{
							$tdEdit = '<td class="noPadding >&nbsp;';
						}
						$tdEdit .= '</td>';

						if(current_user_can('digi_delete_method')){
							$tdDelete = '<td class="noPadding delete-node" id="delete-node-' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_DELETE . '" alt="' . sprintf(__('&Eacute;ffacer %s', 'evarisk'), __('la cat&eacute;gorie de pr&eacute;conisation', 'evarisk')) . '" title="' . sprintf(__('&Eacute;ffacer %s', 'evarisk'), __('la cat&eacute;gorie de pr&eacute;conisation', 'evarisk')) . '" />';
						}
						else{
							$tdDelete = '<td class="noPadding >&nbsp;';
						}
						$tdDelete .= '</td>';

						$actions = $tdAddMain . $tdAddSecondary . $tdEdit . $tdDelete;

						$result_info = MethodeEvaluation::getFormule($element_direct_children_type->id);
						$result_info_class = 'evaluation_method_formule_cell';

						$trouveElement = 0;
					}break;
					case TABLE_MENU:{
						$affichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_MEN . $element_direct_children_type->id . '</span> - <span class="node_name" >' . $element_direct_children_type->nom . '</span>';

						$tdAddMain = '<td class="noPadding" >&nbsp;</td>';
						$tdAddSecondary = '<td class="noPadding" >&nbsp;</td>';

						if(current_user_can('digi_edit_menu')){
							$tdEdit = '<td class="noPadding edit-node" id="edit-node-' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_EDIT . '" alt="' . __('Modifier la cat&eacute;gorie de pr&eacute;conisation', 'evarisk') . '" title="' . __('Modifier la cat&eacute;gorie de pr&eacute;conisation', 'evarisk') . '" />';
						}
						else{
							$tdEdit = '<td class="noPadding >&nbsp;';
						}
						$tdEdit .= '</td>';

						if(current_user_can('digi_delete_menu')){
							$tdDelete = '<td class="noPadding delete-node" id="delete-node-' . $element_direct_children_type->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_DELETE . '" alt="' . sprintf(__('&Eacute;ffacer %s', 'evarisk'), __('la cat&eacute;gorie de pr&eacute;conisation', 'evarisk')) . '" title="' . sprintf(__('&Eacute;ffacer %s', 'evarisk'), __('la cat&eacute;gorie de pr&eacute;conisation', 'evarisk')) . '" />';
						}
						else{
							$tdDelete = '<td class="noPadding >&nbsp;';
						}
						$tdDelete .= '</td>';

						$actions = $tdAddMain . $tdAddSecondary . $tdEdit . $tdDelete;

						$trouveElement = 0;
					}break;
				}

				$info = '';
				if($output_info){
					$info = digirisk_display::get_info_column_content($element_type, $element_direct_children_type->id);
					$info = '
		<td id="info-' . $element_type . '-' . $element_direct_children_type->id . '" class="' . ($result_info_class != '' ? $result_info_class : $info['class']) . '" >' . ($result_info != '' ? $result_info : $info['value']) . '</td>';
				}

				$element_children_tree .= '
	<tr id="node-' . $output_table_id . '-' . $element_direct_children_type->id . '" class=" ' . $ddNoeudClass . ' parent">
		<td id="node-' . $output_table_id . '-' . $element_direct_children_type->id . '-name" class="' . $nomNoeudClass . '" >' . $affichage . '</td>
		' . $info . $actions . '
	</tr>';

				if($trouveElement){
					$loop_nb++;
					$element_children_tree .= digirisk_display::standard_configuration_tree_element($element_direct_children_type->id , $element_type, $output_info, $output_table_id, $loop_nb);
				}
			}
		}

		/*	If element has children that is not	*/
		if(is_array($element_sub_children) && !empty($element_sub_children) && ((int)$element_id >= 1)){
			foreach($element_sub_children as $subElement){
				$ddFeuilleClass = 'feuilleArbre';
				$nomFeuilleClass = 'nomFeuilleArbre';
				$result_info = '';

				switch($element_type){
					case TABLE_CATEGORIE_PRECONISATION:{
						$tdSubEdit = '
								<td colspan="2">&nbsp;</td>
								<td class="noPadding edit-leaf" id="edit-leaf' . $subElement->id . '">';
						if(current_user_can('digi_edit_danger')){
							$tdSubEdit .= '<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_EDIT . '" alt="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('la pr&eacute;conisation', 'evarisk')) . '" title="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('la pr&eacute;conisation', 'evarisk')) . '" />';
						}
						else{
							$tdSubEdit .= '&nbsp;';
						}
						$tdSubEdit .= '</td>';
						$tdSubDelete = '<td class="noPadding delete-leaf" id="delete-leaf' . $subElement->id . '">';
						if(current_user_can('digi_delete_danger')){
							$tdSubDelete .= '<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_DELETE . '" alt="' . sprintf(__('Supprimer %s', 'evarisk'), __('la pr&eacute;conisation', 'evarisk')) . '" title="' . sprintf(__('Supprimer %s', 'evarisk'), __('la pr&eacute;conisation', 'evarisk')) . '" />';
						}
						else{
							$tdSubDelete .= '&nbsp;';
						}
						$tdSubDelete .= '</td>';
						$subAffichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_P . $subElement->id . ' - </span><span class="leaf_name" >' . $subElement->nom . '</span>';
						$subActions = $tdSubEdit . $tdSubDelete;
						if(!current_user_can('digi_view_detail_danger')){
							$nomFeuilleClass = 'userForbiddenActionCursor';
						}

						$recommandationMainPicture = evaPhoto::checkIfPictureIsFile($subElement->photo, $element_type, true);
						$result_info = '<img class="recommandationDefaultPictosList" src="' . $recommandationMainPicture . '" alt="' . ucfirst(strtolower($subElement->nom)) . '" title="' . ELEMENT_IDENTIFIER_P . $subElement->id . '&nbsp;-&nbsp;' . ucfirst(strtolower(htmlentities($subElement->nom, ENT_NOQUOTES, 'UTF-8'))) . '" />';

					}break;
				}
				$element_direct_children_tree .= '
		<tr id="leaf-' . $subElement->id . '" class="child-of-node-' . $output_table_id . '-' . $element_id . ' ' . $ddFeuilleClass . '">
			<td id="leaf-' . $subElement->id . '-name" class="' . $nomFeuilleClass . '" >' . $subAffichage . '</td>';
				if($output_info){
					$info = digirisk_display::get_info_column_content($sub_element_type, $subElement->id);
					$element_direct_children_tree .= '
			<td id="info-' . $sub_element_type . '-' . $subElement->id . '" class="' . $info['class'] . '">' . ($result_info != '' ? $result_info : $info['value']) . '</td>';
				}
				$element_direct_children_tree .= $subActions . '
		</tr>';
			}
		}

		return $element_children_tree . $element_direct_children_tree;
	}

}