<?php
/**
 *
 * @author Evarisk
 * @version v5.0
 */
require_once(EVA_CONFIG);
require_once(EVA_LIB_PLUGIN_DIR . 'risque/Risque.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'adresse/evaAddress.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'users/evaUser.class.php');

class eva_UniteDeTravail {

	/**
	* @var int The working unit identifier
	*/
	var $id;
	/**
	* @var string The working unit name
	*/
	var $name;
	/**
	* @var string The working unit picture path
	*/
	var $picture;

/*
*	Constructeur et accesseurs
*/
	/**
	* Constructor of the working unit class
	* @param int $id The identifier to set
	* @param string $name The name to set
	* @param string $picture The picture path to set
	*/
	function UniteDeTravail($id = NULL, $name = '', $picture = EVA_DEFAULT_UT_IMAGE) {
		$this->id = $id;
		$this->name = $name;
		$this->picture = $picture;
	}

	/**
	* Returns the working unit identifier
	* @return int The identifier
	*/
	function getId()
	{
		return $this->id;
	}
	/**
	* Set the working unit identifier
	* @param int $id The identifier to set
	*/
	function setId($id)
	{
		$this->id = $id;
	}
	/**
	* Returns the working unit name
	* @return string The name
	*/
	function getName()
	{
		return $this->name;
	}
	/**
	* Set the working unit name
	* @param string $name The name to set
	*/
	function setName($name)
	{
		$this->name = $name;
	}
	/**
	* Returns the working unit picture path
	* @return string The picture path
	*/
	function getPicture()
	{
		return $this->picture;
	}
	/**
	* Set the working unit picture path
	* @param string $picture The picture path to set
	*/
	function setpicture($picture)
	{
		$this->picture = $picture;
	}

/*
* Autres Methodes
*/

	/**
	* Returns the working unit witch is the identifier
	* @param int $id Working unit identifier search
	* @return The working unit  witch is the identifier
	*/
	function getWorkingUnit($id)
	{
		global $wpdb;
		$id = (int) $id;
		return $wpdb->get_row( "SELECT * FROM " . TABLE_UNITE_TRAVAIL . " WHERE id = " . $id);
	}

	/**
	* Returns the working unit witch is the name
	* @param string $nom Working unit name search
	* @return The working unit
	*/
	function getWorkingUnitByName($nom)
	{
		global $wpdb;
		$resultat = $wpdb->get_row( "SELECT * FROM " . TABLE_UNITE_TRAVAIL . " WHERE nom='" . $nom . "'");
		return $resultat;
	}

	/**
	* Returns all working units maching with the where condition and order by the order condition
	* @param string $where SQL where condition
	* @param string $order SQL order condition
	* @return The working units  maching with the where condition and order by the order condition
	*/
	function getWorkingUnits($where = "1", $order = "id ASC") {
		global $wpdb;
		$resultat = $wpdb->get_results( "SELECT * FROM " . TABLE_UNITE_TRAVAIL . " WHERE " . $where . " ORDER BY " . $order);
		return $resultat;
	}

	/**
	* Returns all working units name whitout the specifie
	* @param string $saufUnite Working unit name not consider
	* @return All the  working unit name whitout the specifie
	*/
	function getWorkingUnitsName($saufUnite = '', $workingUnitStatus = '')
	{
		$unites = eva_UniteDeTravail::getWorkingUnits( $workingUnitStatus );
		foreach($unites as $unite)
		{
			if($unite->nom != $saufUnite)
			{
				$tab_unites[]=$unite->nom;
			}
		}
		if(isset($tab_unites))
			return $tab_unites;
		else return null;
	}

	/**
	* @todo
	*/
	function getWorkingUnitInfos($idWorkingUnit)
	{
		unset($infos, $info);
		$uniteDeTravail = eva_UniteDeTravail::getWorkingUnit($idWorkingUnit);

		$info['nom'] = __('Niveau de risque', 'evarisk');
		$scoreRisqueUniteTravail = eva_UniteDeTravail::getScoreRisque($idWorkingUnit);
		$info['valeur'] = eva_UniteDeTravail::getNiveauRisque($scoreRisqueUniteTravail);
		$info['classeValeur'] = 'risque' . Risque::getSeuil($scoreRisqueUniteTravail) . 'Text risqueText' . TABLE_UNITE_TRAVAIL . $idWorkingUnit;
		$infos[] = $info;
		$info['nom'] = __('Employ&eacute;s &eacute;valu&eacute;s', 'evarisk');
		$info['valeur'] = count(evaUser::getBindUsers($idWorkingUnit, TABLE_UNITE_TRAVAIL));
		$info['classeValeur'] = '';
		$infos[] = $info;
		return $infos;
	}

	function getScoreRisque($id)
	{
		$temp = Risque::getRisques(TABLE_UNITE_TRAVAIL, $id, "Valid");
		if($temp != null)
		{
			foreach($temp as $risque)
			{
				$risques['"' . $risque->id . "'"][] = $risque;
			}
		}
		$scoreTotal = 0;
		$diviseur = 0;
		$scoreToReturn = 0;
		if(isset($risques) && ($risques != null))
		{
			foreach($risques as $risque)
			{
				$idMethode = $risque[0]->id_methode;
				$score = Risque::getScoreRisque($risque);
				$quotation = Risque::getEquivalenceEtalon($idMethode, $score, $risque[0]->date);
				$niveauSeuil = Risque::getSeuil($quotation);
				$scoreTotal += $quotation * ($niveauSeuil);
				$diviseur += ($niveauSeuil);
			}
			if($diviseur > 0)
			{
				$scoreToReturn = round($scoreTotal/$diviseur, 0);
			}
		}

		return $scoreToReturn;
	}

	function getNiveauRisque($quotation)
	{
		return Risque::getNiveauRisque(Risque::getSeuil($quotation));
	}

	function getNombreRisques($id)
	{
		return Risque::getNombreRisques(TABLE_UNITE_TRAVAIL, $id, "Valid");
	}

	//@todo getResponsables
	function getResponsables($id)
	{
		return null;
	}

	/**
	* Returns the marker informations of a working unit for the google maps
	* @param string $id Working unit identifier
	* @return the marker informations for the google maps
	*/
	public static function getMarkersGeoLoc($id)
	{
		global $wpdb;
		if($id == null)
		{
			$geoLoc = null;
		}
		else
		{
			$workingUnit = $wpdb->get_row( "SELECT * FROM " . TABLE_UNITE_TRAVAIL . " WHERE id =" . $id);
			$address = new EvaAddress($workingUnit->id_adresse);
			$address->load();
			$geoLoc = $address->getGeoLoc();
			$scoreRisque = eva_UniteDeTravail::getScoreRisque($workingUnit->id);
			$geoLoc['info'] = '<img class="alignleft" style="margin-right:0.5em;" src="' . EVA_WORKING_UNIT_ICON . '" alt="Unit&eacute; de travail : "/><strong>' . $workingUnit->nom . '</strong><br /><em>' . __('Risque', 'evarisk') . ' : <span class="valeurInfoElement risque' . Risque::getSeuil($scoreRisque) . 'Text">' . eva_UniteDeTravail::getNiveauRisque($scoreRisque) . '</span></em>';
			$geoLoc['type'] = "unit&eacute; de travail";
			$geoLoc['adress'] = $workingUnit->id_adresse;
			$geoLoc['image'] = GOOGLEMAPS_UNITE;
		}
		return $geoLoc;
	}

/*
* Persistance
*/
	/**
	* Save a new working unit.
	* @param string $nom Working unit name.
	* @param string $idGroupementPere Father group id.
	*/
  	function saveNewWorkingUnit($nom, $idGroupementPere) {
		global $wpdb;
		$new_work_unit_args = array(
			'nom' => $nom,
			'id_groupement' => $idGroupementPere,
			'Status' => 'Valid',
			'creation_date' => current_time('mysql', 0),
		);
		$new_work_unit = $wpdb->insert( TABLE_UNITE_TRAVAIL, $new_work_unit_args );
		return $new_work_unit;
	}

	/**
	* Update the working unit which is the identifier.
	* @param int $id_unite Working unit identifier (not update).
	* @param string $nom Working unit name .
	* @param string $description Working unit description.
	* @param string $telephone Working unit telephone.
	* @param string $effectif Working unit effective .
	* @param string $idAdresse Identifier of the address working unit name in the Adress Table.
	* @param string $idGroupementPere  father group id.
	*/
	function updateWorkingUnit($id_unite, $nom, $description, $telephone, $effectif, $idAdresse, $idGroupementPere, $idResponsable) {
		global $wpdb;

		$work_unit = array();
		$work_unit[ 'nom' ] = $nom;
		$work_unit[ 'description' ] = $description;
		$work_unit[ 'telephoneUnite' ] = $telephone;
		$work_unit[ 'effectif' ] = $effectif;
		$work_unit[ 'id_adresse' ] = $idAdresse;
		$work_unit[ 'id_groupement' ] = $idGroupementPere;
		$work_unit[ 'id_responsable' ] = $idResponsable;
		$work_unit[ 'lastupdate_date' ] = current_time('mysql', 0);

		$update_result = $wpdb->update( TABLE_UNITE_TRAVAIL, $work_unit, array( 'id' => $id_unite, ) );
		return $update_result;
	}

	/**
	* Update a given working unit
	*
	* @param string $id_unite The wordking unit identifier we want to update
	* @param string $whatToUpdate The wordking unit information we want to update
	* @param string $whatToSet The value of the information we want to update
	*/
	function updateWorkingUnitByField($id_unite, $whatToUpdate, $whatToSet) {
		global $wpdb;

		$update_work_unit = $wpdb->update( TABLE_UNITE_TRAVAIL, array( $whatToUpdate => $whatToSet, 'lastupdate_date' => current_time('mysql', 0), ), array( 'id' => $id_unite, ) );

		return $update_work_unit;
	}
	/**
	* Transfer an working unit from a group to an other
	* @param int $idUnite Working unit to transfer identifier
	* @param int $idGroupementPere Group which receive the transfer identifier
	*/
	function transfertUnit($idUnite, $idGroupementPere)
	{
		global $wpdb;
		$update_work_unit = $wpdb->update( TABLE_UNITE_TRAVAIL, array( 'id_groupement' => $idGroupementPere, 'lastupdate_date' => current_time('mysql', 0), ), array( 'id' => $idUnite, ) );
	}
	/**
	* Set the status of the  working unit wich is the identifier to Delete
	* @param int $id Working unit identifier
	*/
	function deleteWorkingUnit($id) {
		global $wpdb;

		$delete_work_unit = $wpdb->update( TABLE_UNITE_TRAVAIL, array( 'Status' => 'Deleted', 'lastupdate_date' => current_time('mysql', 0), ), array( 'id' => $id, ) );
		if ( false !== $delete_work_unit ) {
			$message = addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('L\'unit&eacute; a bien &eacute;t&eacute; supprim&eacute;e', 'evarisk') . '</strong></p>');
		}
		else {
			$message = addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('L\'unit&eacute; n\'a pas pu &ecirc;tre supprim&eacute;e', 'evarisk') . '</strong></p>');
		}
		echo '<script type="text/javascript">
				digirisk(document).ready(function(){
					digirisk("#message").addClass("updated");
					digirisk("#message").html("' . $message . '");
					digirisk("#message").show();
					setTimeout(function(){
						digirisk("#message").removeClass("updated");
						digirisk("#message").hide();
					},7500);
				});
			</script>';
	}

}