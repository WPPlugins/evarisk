<?php
/**
 *
 * @author Evarisk
 * @version v5.0
 */

class categorieDangers {

	/**
	 * @var int The danger categorie identifier
	 */
	var $id;
	/**
	 * @var string The danger categorie name
	 */
	var $name;

/*
 *	Constructeur et accesseurs
 */

	/**
	 * Constructor of the danger categorie class
	 * @param int $id The identifier to setI
	 * @param string $name The name to set
	 */
	function categorieDanger($id = NULL, $name = '') {
		$this->id = $id;
		$this->name = $name;
	}

	/**
	 * Returns the danger categorie identifier
	 * @return int The identifier
	 */
	function getId()
	{
		return $this->id;
	}
	/**
	 * Set the danger categorie identifier
	 * @param int $id The identifier to set
	 */
	function setId($id)
	{
		$this->id = $id;
	}
	/**
	 * Returns the danger categorie name
	 * @return string The name
	 */
	function getName()
	{
		return $this->name;
	}
	/**
	 * Set the danger categorie name
	 * @param string $name The name to set
	 */
	function setName($name)
	{
		$this->name = $name;
	}

/*
 * Autres methodes
 */
	function getCategorieDanger($id, $status = " Status = 'Valid' ")
	{
		global $wpdb;
		$id = (int) $id;
		$resultat = $wpdb->get_row( "SELECT * FROM " . TABLE_CATEGORIE_DANGER . " WHERE 1 AND id = " . $id . " ORDER BY position");
		return $resultat;
	}

	function getCategorieDangerByName($nom)
	{
		global $wpdb;
		$resultat = $wpdb->get_row( "SELECT * FROM " . TABLE_CATEGORIE_DANGER . " WHERE Status = 'Valid' AND nom='" . $nom . "'");
		return $resultat;
	}

	function getCategoriesDanger($where = "1", $order = "id ASC") {
		global $wpdb;
		$resultat = $wpdb->get_results( "SELECT * FROM " . TABLE_CATEGORIE_DANGER . " WHERE Status = 'Valid' AND " . $where . " ORDER BY " . $order);
		return $resultat;
	}

	function getCategoriesName($saufCategorie = '')
	{
		$categories = categorieDangers::getCategoriesDanger();
		foreach($categories as $categorie)
		{
			if($categorie->nom != $saufCategorie)
			{
				$tab_categories[]=$categorie->nom;
			}
		}
		return $tab_categories;
	}

	function getDangersDeLaCategorie($idCategorie, $where = "1", $order="nom ASC")
	{
		global $wpdb;
		$resultat = $wpdb->get_results( "SELECT * FROM " . TABLE_DANGER . " WHERE  Status = 'Valid' AND id_categorie =" . $idCategorie . " AND " . $where . " ORDER BY ". $order);
		return $resultat;
	}

	function saveNewCategorie($nom, $position = 0 ) {
		global $wpdb;

		$lim = Arborescence::getMaxLimiteDroite(TABLE_CATEGORIE_DANGER);
		$new_cat_default_args = array('nom' => $nom, 'Status' => 'Valid', 'limiteGauche' => $lim, 'limiteDroite' =>($lim+1) );
		if ( !empty( $position ) ) {
			$new_cat_default_args[ 'position' ] = $position;
		}
		$wpdb->insert(TABLE_CATEGORIE_DANGER, $new_cat_default_args);
		$new_category = $wpdb->insert_id;
		$wpdb->update(TABLE_CATEGORIE_DANGER, array('limiteDroite'=>($lim + 2)), array('nom'=>'Categorie Racine'));

		return $new_category;
	}

	function updateCategorie($id_categorie, $nom, $description, $idCategorieMere) {
		global $wpdb;

		$wpdb->update(TABLE_CATEGORIE_DANGER, array('nom'=>$nom, 'description'=>$description), array('id'=>$id_categorie));

		$categorieFille =  categorieDangers::getCategorieDanger($id_categorie);
		$categorieDestination =  categorieDangers::getCategorieDanger($idCategorieMere);
		$catMere = Arborescence::getPere(TABLE_CATEGORIE_DANGER, $categorieFille);

		if ($categorieDestination->nom != $catMere->nom) {
			$racine = categorieDangers::getCategorieDangerByName("Categorie Racine");
			Arborescence::deplacerElements(TABLE_CATEGORIE_DANGER, $racine, $categorieFille, $categorieDestination);
		}
	}

	/**
	  * Set the status of the group wich is the identifier to Delete
	 */
	function deleteCategorie($id)
	{
		global $wpdb;
		$delete_danger_cat = $wpdb->update( TABLE_CATEGORIE_DANGER, array( 'Status' => 'Deleted', ), array( 'id' => $id, ) );
		if ( false !== $delete_danger_cat ) {
			echo
				'<script type="text/javascript">
					digirisk(document).ready(function(){
						digirisk("#message").addClass("updated");
						digirisk("#message").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La cat&eacute;gorie a bien &eacute;t&eacute; supprim&eacute;e', 'evarisk') . '</strong></p>') . '");
						digirisk("#message").show();
						setTimeout(function(){
							digirisk("#message").removeClass("updated");
							digirisk("#message").hide();
						},7500);
					});
				</script>';
		}
		else {
			echo
				'<script type="text/javascript">
					digirisk(document).ready(function(){
						digirisk("#message").addClass("updated");
						digirisk("#message").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('La cat&eacute;gorie n\'a pas pu &ecirc;tre supprim&eacute;e', 'evarisk') . '</strong></p>') . '");
						digirisk("#message").show();
						setTimeout(function(){
							digirisk("#message").removeClass("updated");
							digirisk("#message").hide();
						},7500);
					});
				</script>';
		}
	}

	function getCategorieDangerForRiskEvaluation($risque, $formId = '') {
		global $wpdb;

		$categoryResult = array();
		$categoryResult['list'] = '';
		$categoryResult['script'] = '';
		$categoryResult['selectionCategorie'] = '';

		$nomRacine = 'Categorie Racine';
		$categorieRacine = categorieDangers::getCategorieDangerByName($nomRacine);

		if ( empty($risque) || DIGI_ALLOW_RISK_CATEGORY_CHANGE ) {
			$categoriesDangers = Arborescence::getDescendants(TABLE_CATEGORIE_DANGER, $categorieRacine, '1', 'position');
		}
		else {
			$categoriesDangers[] = categorieDangers::getCategorieDanger($risque[0]->idCategorie);
		}

		if ( !empty($risque) && ($risque[0] != null) ) {// Si l'on �dite un risque, on s�lectionne la bonne cat�gorie de dangers
			$selectionCategorie = $risque[0]->idCategorie;
		}
		else {// Sinon on s�lectionne la racine
			$selectionCategorie = $categorieRacine->id;
		}

		if (AFFICHAGE_PICTO_CATEGORIE) {
			/*	Get the default methode	*/
			$query = $wpdb->prepare("SELECT id FROM " . TABLE_METHODE . " WHERE default_methode = 'yes'", "");
			$default_methode = $wpdb->get_var($query);

			foreach ($categoriesDangers as $categorieDangers) {
				if ($selectionCategorie == $categorieRacine->id) {
					$selectionCategorie = $categorieDangers->id;
				}
				$categoryResult['selectionCategorie'] = $selectionCategorie;
				$categorieDangerMainPhoto = evaPhoto::getMainPhoto(TABLE_CATEGORIE_DANGER, $categorieDangers->id);
				$categorieDangerMainPhoto = evaPhoto::checkIfPictureIsFile($categorieDangerMainPhoto, TABLE_CATEGORIE_DANGER);
				$conteneur_penibilite = '';
				$dangers = categorieDangers::getDangersDeLaCategorie($categorieDangers->id, 'Status="Valid"');
				if ( !empty($dangers) && is_array($dangers) ) {
					foreach ($dangers as $danger) {
						if ( !empty($danger->choix_danger) ) {
							$choix_danger = unserialize($danger->choix_danger);
							if ( is_array($choix_danger) && in_array('penibilite', $choix_danger) ) {
								$conteneur_penibilite = '<div class="case_penibilite" id="case_penibilite_'.$danger->methode_eva_defaut.'" >' . __('P', 'evarisk') . '</div>';
							}
						}
					}
				}
				$categoryResult['list'] .= '<div class="content_radio_picto_categorie"><div class="radioPictoCategorie" ><input id="' . $formId . 'cat' . $categorieDangers->id . '" type="radio" name="' . $formId . 'categoriesDangers"  class="categoriesDangers" value="' . $categorieDangers->id . '" /><label for="' . $formId . 'cat' . $categorieDangers->id  . '" ><img class="default_methode" src="' . $categorieDangerMainPhoto . '" alt="' . ELEMENT_IDENTIFIER_CD . $categorieDangers->id . ' - ' . $categorieDangers->nom . '" title="' . ELEMENT_IDENTIFIER_CD . $categorieDangers->id . ' - ' . $categorieDangers->nom . '" id="' . $formId . 'imgCat' . $categorieDangers->id . '" />' . $conteneur_penibilite . '<div class="digirisk_danger_cat_identifier" >' . ELEMENT_IDENTIFIER_CD . $categorieDangers->id . '</div></label></div></div>';
			}



			if ($categoryResult['list'] != '') {
				$formIdSelector = ($formId != '') ? '#' . $formId . ' ' : '';
				$categoryResult['script'] .= '
			digirisk("#' . $formId . 'divCategorieDangerFormRisque").hide();
			digirisk("#' . $formId . 'cat' . $selectionCategorie . '").click();
			var ' . $formId . 'oldCatId = "' . $selectionCategorie . '";';

					if ( empty($risque) || DIGI_ALLOW_RISK_CATEGORY_CHANGE ) {
						$categoryResult['script'] .= '
			digirisk("' . $formIdSelector . '.categoriesDangers").click(function(){
				var ' . $formId . 'newCatId = (digirisk(this).attr("id")).replace("' . $formId . 'cat","");
				if (' . $formId . 'oldCatId != ' . $formId . 'newCatId) {
					digirisk("#' . $formId . 'categorieDangerFormRisque").val(' . $formId . 'newCatId);
					digirisk("#' . $formId . 'categorieDangerFormRisque").change();
					' . $formId . 'oldCatId = ' . $formId . 'newCatId;
				}
			});

			jQuery(".default_methode").unbind("click");
			jQuery(".default_methode").click( function() {
          		jQuery("#' . $formId . 'methodeFormRisque").val("'.$default_methode.'");
          		jQuery("#' . $formId . 'divVariablesFormRisque").html(digirisk("#loadingImg").html());
				jQuery("#' . $formId . 'divVariablesFormRisque").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_METHODE . '", "act":"reloadVariables", "idMethode":digirisk("#' . $formId . 'methodeFormRisque").val(), "idRisque": "' . (!empty($risque[0]) ? $risque[0]->id : 0) . '", "formId":"' . $formId . '"});
			});

			jQuery(".case_penibilite").unbind("click");
			jQuery(".case_penibilite").live("click", function(){
          		jQuery("#' . $formId . 'methodeFormRisque").val(jQuery(this).attr("id").replace("case_penibilite_", ""));
          		jQuery("#' . $formId . 'divVariablesFormRisque").html(digirisk("#loadingImg").html());
				jQuery("#' . $formId . 'divVariablesFormRisque").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_METHODE . '", "act":"reloadVariables", "idMethode":digirisk("#' . $formId . 'methodeFormRisque").val(), "idRisque": "' . (!empty($risque[0]) ? $risque[0]->id : 0) . '", "formId":"' . $formId . '"});
			});

			digirisk("#' . $formId . 'categorieDangerFormRisque").change(function(){
				digirisk("#' . $formId . 'divDangerFormRisque").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
					"post":"true",
					"table":"' . TABLE_CATEGORIE_DANGER . '",
					"act":"reloadComboDangers",
					"idElement": digirisk("#' . $formId . 'categorieDangerFormRisque").val(),
					"formId":"' . $formId . '"
				});
			});';
					}
			}
		}
		$categoryResult['list'] .= '<input type="hidden" id="valeurPenibilite" value="0"/>
		<div id="' . $formId . 'divCategorieDangerFormRisque" >' . EvaDisplayInput::afficherComboBoxArborescente($categorieRacine, TABLE_CATEGORIE_DANGER, $formId . 'categorieDangerFormRisque', __('Cat&eacute;gorie de dangers', 'evarisk') . ' : ', 'categorieDangers', ucfirst(strtolower(sprintf(__("choisissez %s", 'evarisk'), __("une cat&eacute;gorie de dangers", 'evarisk')))), $selectionCategorie) . '</div>';

		return $categoryResult;
	}

	/**
	* Returns all working unit belonging to the group witch is identifier or belonging to his descendants
	* @param int $elementId The group identifier
	* @param string $where The SQL where condition
	* @param string $order The SQL order condition
	* @return the working units  belonging to the group witch is identifier
	*/
	function getChildren($elementId, $where = "1", $order="nom ASC")
	{
		global $wpdb;
		$element = categorieDangers::getCategorieDanger($elementId, '');
		$subElements = Arborescence::getDescendants(TABLE_CATEGORIE_DANGER, $element);
		unset($tabId);
		$tabId[] = $elementId;
		foreach($subElements as $subElement)
		{
			$tabId[] = $subElement->id;
		}
		$resultat = $wpdb->get_results( "SELECT * FROM " . TABLE_DANGER . " WHERE id_categorie in (" . implode(', ', $tabId) . ") AND " . $where . " ORDER BY ". $order);
		return $resultat;
	}
}