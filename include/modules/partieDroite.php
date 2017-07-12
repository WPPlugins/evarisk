<?php

	$_POST['tableElement'] = $_POST['table'];
	$_POST['idElement'] = isset($_POST['id']) ? $_POST['id'] : null;
	$menu = isset($_POST['menu'])?$_POST['menu']:'';
	$tableElement = $_POST['tableElement'];
	$idElement = $_POST['idElement'];
	$partition = isset($_POST['partition'])?$_POST['partition']:'tout';
	$element = $script = '';
	$v_nb = 0;

	require_once( EVA_CONFIG );

	switch ( $tableElement ) {
		case TABLE_METHODE:
			$pageHook = PAGE_HOOK_EVARISK_EVALUATION_METHODE;
			MethodeEvaluation::includes_evaluation_method_boxes($idElement, $partition);
			$element = MethodeEvaluation::getMethod($idElement);
			$element_status = !empty($element)?ucfirst($element->Status):'';
			$markers = '';
			$v_nb = 1;
		break;

		case TABLE_MENU:
			$pageHook = PAGE_HOOK_EVARISK_MENU;
			digirisk_menu::includes_menu_boxes($idElement, $partition);
			$element = digirisk_menu::get_element("'valid'", array('id' => $idElement), '');
			$element_status = !empty($element)?ucfirst($element->status):'';
			$markers = '';
			$v_nb = 1;
		break;

		case TABLE_CATEGORIE_PRECONISATION:
			$pageHook = PAGE_HOOK_EVARISK_CATEGORIE_PRECONISATION;
			evaRecommandationCategory::includes_recommandation_category_boxes($idElement, $partition);
			$element = evaRecommandationCategory::getCategoryRecommandation($idElement);
			$element_status = !empty($element)?ucfirst($element->status):'';
			$markers = '';
		break;
		case TABLE_PRECONISATION:
			$pageHook = PAGE_HOOK_EVARISK_PRECONISATION;
			evaRecommandation::includes_recommandation_boxes($idElement, $partition);
			$element = evaRecommandation::getRecommandation($idElement);
			$element_status = !empty($element)?ucfirst($element->status):'';
			$markers = '';
		break;

		case TABLE_CATEGORIE_DANGER:
			$pageHook = PAGE_HOOK_EVARISK_CATEGORIES_DANGERS;
			digirisk_danger::includesDangers($idElement, $partition);
			$element = categorieDangers::getCategorieDanger($idElement);
			$element_status = !empty($element)?$element->Status:'';
			$markers = '';
			$v_nb = 1;
		break;
		case TABLE_DANGER:
			$pageHook = PAGE_HOOK_EVARISK_DANGERS;
			digirisk_danger::includesDangers($idElement, $partition);
			$element = EvaDanger::getDanger($idElement);
			$element_status = !empty($element)?$element->Status:'';
			$markers = '';
			$v_nb = 1;
		break;

		default:
			switch ( $menu ) {
				case 'gestiongrptut':
					switch ( $tableElement ) {
						case TABLE_GROUPEMENT:
							require_once(EVA_MODULES_PLUGIN_DIR . 'gestionGroupementUniteTravail/includesGestionGroupementUniteTravail.php');
							require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/groupement/eva_groupement.class.php');
							$pageHook = PAGE_HOOK_EVARISK_GROUPEMENTS_GESTION;
							$markers = array(EvaGroupement::getMarkersGeoLoc($idElement));
							includesGestionGroupementUniteTravail($idElement, $partition);
						break;
						case TABLE_UNITE_TRAVAIL:
							require_once(EVA_MODULES_PLUGIN_DIR . 'gestionGroupementUniteTravail/includesGestionGroupementUniteTravail.php');
							require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/uniteDeTravail/uniteDeTravail.class.php');
							$pageHook = PAGE_HOOK_EVARISK_UNITES_DE_TRAVAIL;
							$markers = array(eva_UniteDeTravail::getMarkersGeoLoc($idElement));
							includesGestionGroupementUniteTravail($idElement, $partition);
						break;
					}
				break;

				default:
					switch ( $tableElement ) {
						case TABLE_GROUPEMENT:
							require_once(EVA_MODULES_PLUGIN_DIR . 'evaluationDesRisques/includesEvaluationDesRisques.php');
							require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/groupement/eva_groupement.class.php');
							$pageHook = PAGE_HOOK_EVARISK_GROUPEMENTS;
							$markers = array(EvaGroupement::getMarkersGeoLoc($idElement));
							includesEvaluationDesRisques($idElement, $partition);
						break;
						case TABLE_UNITE_TRAVAIL:
							require_once(EVA_MODULES_PLUGIN_DIR . 'evaluationDesRisques/includesEvaluationDesRisques.php');
							require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/uniteDeTravail/uniteDeTravail.class.php');
							$pageHook = PAGE_HOOK_EVARISK_UNITES_DE_TRAVAIL;
							$markers = array(eva_UniteDeTravail::getMarkersGeoLoc($idElement));
							includesEvaluationDesRisques($idElement, $partition);
						break;
						case TABLE_TACHE:
							require_once(EVA_MODULES_PLUGIN_DIR . 'actionsCorrectives/includesActionsCorrectives.php');
							$pageHook = PAGE_HOOK_EVARISK_TACHE;
							includesActionsCorrectives($idElement, $partition);
							$markers = '';
						break;
						case TABLE_ACTIVITE:
							require_once(EVA_MODULES_PLUGIN_DIR . 'actionsCorrectives/includesActionsCorrectives.php');
							$pageHook = PAGE_HOOK_EVARISK_ACTIVITE;
							includesActionsCorrectives($idElement, $partition);
							$markers = '';
						break;
					}
				break;
			}

			$script = getScriptPartieDroite($pageHook);
			$v_nb = 2;
		break;
	}

	$idsFilAriane = isset($_POST['idsFilAriane'])?$_POST['idsFilAriane']:null;
	$expanded = isset($_POST['expanded'])?$_POST['expanded']:null;
	$page = isset($_POST['page'])?$_POST['page']:null;
	$idPere = isset($_POST['idPere'])?$_POST['idPere']:null;
	$affichage = isset($_POST['affichage'])?$_POST['affichage']:null;
	$partie = isset($_POST['partie'])?$_POST['partie']:'right';

	if($v_nb == 1){
		if((($element != '') && is_object($element) && ($element_status == 'Valid')) || ($_REQUEST['act'] == 'add')){
			echo '
	<script type="text/javascript">
		digirisk(document).ready(function(){
			jQuery(".if-js-closed").removeClass("if-js-closed").addClass("closed");
			jQuery(".postbox h3, .postbox .handlediv").each(function(){jQuery(this).unbind("click");});
			postboxes.add_postbox_toggles("' . $pageHook . '");
			postboxes.init("' . $pageHook . '");

			postboxes.save_state("' . $pageHook . '");
			postboxes.save_order("' . $pageHook . '");

			jQuery( "#mainPostBox.hide-if-js" ).removeClass( "hide-if-js" );
		});
	</script>
	<div class="metabox-holder digirisk_meta_box_holder clear">';
			do_meta_boxes($pageHook, $partie . 'Side', array('tableElement' => $tableElement, 'idElement' => $idElement, 'idPere' => $idPere, 'affichage' => $affichage, 'idsFilAriane' => $idsFilAriane, 'markers' => $markers, 'page' => $page, 'expanded' => $expanded));
			echo '
	</div>';
		}
		elseif($partie == 'right'){
			_e('L\'&eacute;l&eacute;ment demand&eacute; ne peut &ecirc;tre affich&eacute;. Peut &ecirc;tre a-t-il &eacute;t&eacute; supprim&eacute;.', 'evarisk');
		}
	}
	else{
		echo $script . '<div class="metabox-holder clear">';
			//Add the postBoxes
			do_meta_boxes($pageHook, $partie . 'Side', array('tableElement' => $tableElement, 'idElement' => $idElement, 'idPere' => $idPere, 'affichage' => $affichage, 'idsFilAriane' => $idsFilAriane, 'markers' => $markers, 'page' => $page, 'expanded' => $expanded));
			//To preserve postBoxes order
			wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
			//To preserve postBoxes closure state
			wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
		echo '</div>';
	}
