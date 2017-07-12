<?php
/**
 *
 * @author Evarisk
 * @version v5.0
 */

class EvaDanger {

	/**
	 * @var int The danger  identifier
	 */
	var $id;
	/**
	 * @var string The danger  name
	 */
	var $name;

/*
 *	Constructeur et accesseurs
 */

	/**
	 * Constructor of the danger  class
	 * @param int $id The identifier to setI
	 * @param string $name The name to set
	 */
	function EvaDanger($id = NULL, $name = '') {
		$this->id = $id;
		$this->name = $name;
	}

	/**
	 * Returns the danger  identifier
	 * @return int The identifier
	 */
	function getId()
	{
		return $this->id;
	}
	/**
	 * Set the danger  identifier
	 * @param int $id The identifier to set
	 */
	function setId($id)
	{
		$this->id = $id;
	}
	/**
	 * Returns the danger  name
	 * @return string The name
	 */
	function getName()
	{
		return $this->name;
	}
	/**
	 * Set the danger  name
	 * @param string $name The name to set
	 */
	function setName($name)
	{
		$this->name = $name;
	}

/*
 * Autres methodes
 */
	function getDanger($id)
	{
		global $wpdb;
		$id = (int) $id;
		$resultat = $wpdb->get_row( "SELECT * FROM " . TABLE_DANGER . " WHERE Status = 'Valid' AND id = " . $id);
		return $resultat;
	}

	function getDangerByName($nom)
	{
		global $wpdb;
		$resultat = $wpdb->get_row( "SELECT * FROM " . TABLE_DANGER . " WHERE Status = 'Valid' AND nom='" . $nom . "'");
		return $resultat;
	}


	function getDangers($where = "1", $order = "id ASC") {
		global $wpdb;
		$resultat = $wpdb->get_results( "SELECT * FROM " . TABLE_DANGER . " WHERE Status = 'Valid' AND " . $where . " ORDER BY " . $order);
		return $resultat;
	}

	function getDangersName($saufDanger = '')
	{
		$dangers = EvaDanger::getDangers();
		foreach($dangers as $danger)
		{
			if($danger->nom != $saufDanger)
			{
				$tab_dangers[]=$danger->nom;
			}
		}
		return $tab_dangers;
	}

	/*
	Persistance
	*/
	function saveNewDanger($nom, $idCategorieMere) {
		global $wpdb;

		$nom = digirisk_tools::IsValid_Variable($nom);
		$idCategorieMere = digirisk_tools::IsValid_Variable($idCategorieMere);

		$danger_creation = $wpdb->insert( TABLE_DANGER, array( 'nom' => $nom, 'id_categorie' => $idCategorieMere, 'Status' => 'Valid', ) );
		return $wpdb->insert_id;
	}

	function updateDanger($id, $nom, $idCategorieMere, $description, $tab, $id_methode_eva) {
		global $wpdb;

		$id = digirisk_tools::IsValid_Variable($id);
		$nom = digirisk_tools::IsValid_Variable($nom);
		$idCategorieMere = digirisk_tools::IsValid_Variable($idCategorieMere);
		$description = digirisk_tools::IsValid_Variable($description);
		$id_methode_eva = digirisk_tools::IsValid_Variable($id_methode_eva);

		$wpdb->update( TABLE_DANGER, array( 'nom' => $nom, 'id_categorie' => $idCategorieMere, 'description' => $description, 'choix_danger' => $tab, 'methode_eva_defaut' => $id_methode_eva, ), array( 'id' => $id, ));
	}

	/**
	* Transfer an working unit from a group to an other
	* @param int $idDanger Working unit to transfer identifier
	* @param int $idCategorieMere Group which receive the transfer identifier
	*/
	function transfertDanger($idDanger, $idCategorieMere) {
		global $wpdb;

		$wpdb->update( TABLE_DANGER, array( 'id_categorie' => $idCategorieMere, ), array( 'id' => $idDanger, ));
	}

	function deleteDanger($id) {
		global $wpdb;

		$danger_deletion = $wpdb->update( TABLE_DANGER, array( 'Status' => 'Deleted', ), array( 'id' => $id, ));
		if ( false !== $danger_deletion ) {
			echo
				'<script type="text/javascript">
					digirisk(document).ready(function(){
						digirisk("#message").addClass("updated");
						digirisk("#message").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Le danger a bien &eacute;t&eacute; supprim&eacute;', 'evarisk') . '</strong></p>') . '");
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
						digirisk("#message").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Le danger n\'a pas pu &ecirc;tre supprim&eacute;', 'evarisk') . '</strong></p>') . '");
						digirisk("#message").show();
						setTimeout(function(){
							digirisk("#message").removeClass("updated");
							digirisk("#message").hide();
						},7500);
					});
				</script>';
		}
	}

	function getDangerForRiskEvaluation($selectionCategorie, $risque, $formId = '') {
		$dangerResult = array();
		$dangerResult['list'] = '';
		$dangerResult['script'] = '';

		$dangerResult['list'] .=
	'<div id="needDangerCategory">';
		$dangers = categorieDangers::getDangersDeLaCategorie($selectionCategorie, 'Status="Valid"');
		if(isset($dangers[0]) && ($dangers[0]->id != null))
		{
			$dangerResult['script'] .= '
	digirisk("#needDangerCategory").show();';
		}
		else
		{
			$dangerResult['script'] .= '
	digirisk("#needDangerCategory").hide();';
		}
		if($risque[0] != null) {// Si l'on �dite un risque, on s�lectionne le bon danger
			$selection = $risque[0]->idDanger;
			$selection = evaDanger::getDanger($selection);
		}
		else {// Sinon on s�lectionne le premier danger de la cat�gorie
			$selection = (isset($dangers[0]) && ($dangers[0]->id)) ? $dangers[0]->id : null;
		}
		if ($selection != null) {
			$nombreDeDangers = count($dangers);
			$afficheSelecteurDanger = '';
			if ( ( ( $nombreDeDangers <= 1 ) || !empty($risque) ) && !DIGI_ALLOW_RISK_CATEGORY_CHANGE && (is_object($selection)) ) {
				$afficheSelecteurDanger = ' display:none; ';
				$dangerResult['list'] .= '<div class="clear" >' . ELEMENT_IDENTIFIER_D . $selection->id . ' - ' . $selection->nom . '</div>';
			}
			$dangerResult['list'] .= '<div style="' . $afficheSelecteurDanger . '" class="clear" id="' . $formId . 'divDangerFormRisque" >' . EvaDisplayInput::afficherComboBox($dangers, $formId . 'dangerFormRisque', __('Dangers de la cat&eacute;gorie', 'evarisk') . ' : ', 'danger', '', $selection) . '</div><br />';
		}
		$dangerResult['list'] .= '
	</div><!--/needDangerCategory-->';

		return $dangerResult;
	}

}