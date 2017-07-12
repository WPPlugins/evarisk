<?php
/**
 *
 * @author Evarisk
 * @version v5.0
 */
include_once(EVA_CONFIG );
class Arborescence {

	static function getIdsFilAriane($table, $element)
	{
		$elementsAncetres = Arborescence::getAncetre($table, $element, "limiteGauche ASC");
		$idsFilAriane = '';
		foreach($elementsAncetres as $elementAncetre)
		{
			$idsFilAriane = $idsFilAriane . '-' . $elementAncetre->id;
		}
		if(strlen($idsFilAriane)>2)
		{
			$idsFilAriane = explode('-', str_replace('-1-', '', $idsFilAriane));
		}
		else
		{
			$idsFilAriane = null;
		}
		return $idsFilAriane;
	}

	static function getElement($table, $id, $where='1')
	{
		global $wpdb;
		$element = $wpdb->get_row( "SELECT * FROM " . $table . " WHERE id=" . $id . " AND " . $where);
		return $element;
	}

	static function getRacine($table, $where='1')
	{
		global $wpdb;
		$racine = $wpdb->get_row( "SELECT * FROM " . $table . " table1 WHERE NOT EXISTS(SELECT * FROM " . $table . " table2 WHERE table2.limiteGauche < table1.limiteGauche AND " . $where . ") AND " . $where);
		return $racine;
	}

	static function getMaxLimiteDroite($table, $where='1')
	{
		global $wpdb;
		$right_limit='';
		$resultat = $wpdb->get_row( "SELECT * FROM " . $table . " table1 WHERE NOT EXISTS (SELECT * FROM " . $table . " table2 WHERE table1.limiteDroite < table2.limiteDroite AND " . $where . ") AND " . $where);
		if(!empty($resultat))
			$right_limit=$resultat->limiteDroite;

		return $right_limit;
	}

	static function getByLimites($table, $limiteGauche, $limiteDroite, $where='1')
	{
		global $wpdb;
		$resultat = $wpdb->get_results( "SELECT * FROM " . $table . " WHERE " . $where . "  AND Status = 'Valid' AND limiteGauche >= " . $limiteGauche . " AND limiteDroite <= " . $limiteDroite);
		return $resultat;
	}

	static function getAncetre($table, $element, $order= "limiteGauche ASC", $where='1', $status = "AND Status = 'Valid'") {
		global $wpdb;
		$resultat = $wpdb->get_results( "SELECT * FROM " . $table . " WHERE " . $where . "  " . $status . " AND limiteGauche < " . (!empty($element->limiteGauche) ? $element->limiteGauche : 0) . " AND limiteDroite > " . (!empty($element->limiteDroite) ? $element->limiteDroite : 0) . "	ORDER BY " . $order );
		return $resultat;
	}

	static function getDescendants($table, $element, $where='1', $order="id ASC", $status = "AND Status = 'Valid'")
	{
		global $wpdb;
		$resultat = $wpdb->get_results( "SELECT * FROM " . $table . " WHERE " . $where . " AND limiteGauche > " . $element->limiteGauche . " AND limiteDroite < " . $element->limiteDroite . " " . $status . " ORDER BY " . $order);
		return $resultat;
	}

	static function getPere($table, $element, $where="Status='Valid'")
	{
		global $wpdb;
		$query = $wpdb->prepare( "
		SELECT *
		FROM " . $table . " table1
		WHERE " . $where . "
		AND table1.limiteGauche < " . $element->limiteGauche . "
		AND table1.limiteDroite > " . $element->limiteDroite . "
		AND NOT
		EXISTS (
			SELECT *
			FROM " . $table . " table2
			WHERE " . $where . "
			AND table2.limiteGauche < " . $element->limiteGauche . "
			AND table2.limiteDroite > " . $element->limiteDroite . "
			AND table1.limiteGauche < table2.limiteGauche
		)", "");
		$resultat = $wpdb->get_row($query);
		return $resultat;
	}

	static function getFils($table, $element, $order = "id ASC", $numeroPage = null, $nombreElementsParPage = null, $where = " Status='Valid' ") {
		global $wpdb;
		$limit = "";
		if ($nombreElementsParPage != null) {
			$limit = "
		LIMIT " . ($numeroPage - 1) . ", " . $nombreElementsParPage;
		}
		$query = "SELECT *
		FROM " . $table . " AS table1
		WHERE " . $where . "
			AND table1.limiteGauche > " . $element->limiteGauche . "
			AND table1.limiteDroite < " . $element->limiteDroite . "
			AND NOT EXISTS (
				SELECT *
				FROM " . $table . " AS table2
				WHERE " . $where . "
					AND table2.limiteGauche > " . $element->limiteGauche . "
					AND table2.limiteDroite < " . $element->limiteDroite . "
					AND table1.limiteGauche > table2.limiteGauche
					AND table1.limiteDroite < table2.limiteDroite
			)
		ORDER BY " . $order . $limit;

		$resultat = $wpdb->get_results($query);

		return $resultat;
	}

	/**
	  * Transfer an element in a tree
	  * @param string $table Table name of the element to transfer
	  * @param Element_of_a_tree $racine The element to transfer tree  root
	  * @param Element_of_a_tree $elementFils The element to transfer
	  * @param Element_of_a_tree $elementDestination The element to transfer father-to-be
	  */
	static function deplacerElements($table, $racine, $elementFils, $elementDestination) {
		global $wpdb;
		unset($tableauElements);
		$limiteGauche = $elementFils->limiteGauche;
		$limiteDroite = $elementFils->limiteDroite;
		$ecart = $limiteDroite - $limiteGauche + 1;
		$limiteDroiteDestination;
		$tableauElements = array();

		/**
		  * On extrait l'�l�ment fils et ses descendants de l'arbre :
		  *	- On d�place l'�l�ment fils et ses descendants avant 0
		  */
		// $limiteDroite majore les limites de l'�l�ment fils et de ses descendants
		$decrement = $limiteDroite + 1;
		// Tout �l�ment dont les limites sont comprises entre $limiteGauche et $limiteDroite est soit  l'�l�ment fils, soit un de ses descendants
		$elements = Arborescence::getByLimites($table, $limiteGauche, $limiteDroite);
		// Pour chaque �l�ment, on d�duit le decrement de ses limites
		foreach($elements as $element) {
			$sql = "UPDATE " . $table . " SET `limiteGauche`= '" . ($element->limiteGauche  - $decrement ) . "', `limiteDroite`= '" . ($element->limiteDroite - $decrement ) . "' WHERE `id` = '" . $element->id . "' AND nom != 'Tache Racine'";
			$wpdb->query($sql);
		}

		/**
		  * On referme l'arbre :
		  *	- On tri les limites des �l�ments de l'arbre par ordre croissant
		  *	- On affecte les valeurs de 0 � 2*(nombre d'�l�ments de l'abre) -1 dans l'ordre
		  */
		// Tout �l�ment dont les limites sont comprises entre la  limite gauche et la limite droite de la racine est un �l�ment de l'arbre
		$elements = Arborescence::getByLimites($table, $racine->limiteGauche, $racine->limiteDroite);
		for ($i=0; $i<count($elements); $i++) {
			$tableauElements[$i*2][0] = $elements[$i]->limiteGauche;
			$tableauElements[$i*2][1] = 'g';
			$tableauElements[$i*2][2] = $elements[$i]->id;
			$tableauElements[$i*2+1][0] = $elements[$i]->limiteDroite;
			$tableauElements[$i*2+1][1] = 'd';
			$tableauElements[$i*2+1][2] = $elements[$i]->id;
		}
		// On tri le tableau $tableauElements � partir de l'�l�ment 0 de chaque indice
		sort($tableauElements);
		for ($i=0; $i<count($tableauElements); $i++) {
			if($tableauElements[$i][1] == 'g') {
				$wpdb->update( $table, array( 'limiteGauche' => $i, ), array( 'id' => $tableauElements[$i][2], ) );
			}
			else {
				if($tableauElements[$i][2] == $elementDestination->id) {
					$limiteDroiteDestination = $i;
				}
				$wpdb->update( $table, array( 'limiteDroite' => $i, ), array( 'id' => $tableauElements[$i][2], ) );
			}
		}

		/**
		  * On r�int�gre l'�l�ment fils :
		  *	- On ouvre l'abre � l'emplacement o� l'on veut inserer l'�l�ment fils et ses descendants
		  *	- On ins�re l'�l�ment fils et ses descendants dans l'arbre
		  */
		// Tout �l�ment dont les limites sont comprises entre $limiteDroiteDestination et la limite droite de la racine est un �l�ment dont les deux limites doivent �tre d�plac� de $ecart
		$elements = Arborescence::getByLimites($table, $limiteDroiteDestination, $racine->limiteDroite);
		foreach($elements as $element) {
			$wpdb->update( $table, array( 'limiteGauche' => ($element->limiteGauche + $ecart), 'limiteDroite' => ($element->limiteDroite + $ecart), ), array( 'id' => $element->id, ) );
		}
		// Tout �l�ment dont la limite droite est comprise entre $limiteDroiteDestination et la limite droite de la racine
		// et la limite gauche est avant $limiteDroiteDestination est un �l�ment dont la limite droite doit �tre d�plac� de $ecart
		foreach($elements as $element) {
			$elementsApres[] = $element->id;
		}
		$elements = Arborescence::getByLimites($table, $racine->limiteGauche, ($limiteDroiteDestination - 1));
		foreach($elements as $element) {
			$elementsAvant[] = $element->id;
		}
		$elementsArbre = Arborescence::getByLimites($table, $racine->limiteGauche, $racine->limiteDroite);
		foreach($elementsArbre as $elementArbre) {
			// Tout �l�ment qui n'est ni avant ni apr�s $limiteDroiteDestination la chevauche (donc un �l�m�nt � modifier)
			if (!empty($elementsAvant) && !empty($elementsApres)) {
				if((!(in_array($elementArbre->id, $elementsAvant))) && (!(in_array($elementArbre->id, $elementsApres)))) {
					$wpdb->update( $table, array( 'limiteDroite' => ($elementArbre->limiteDroite + $ecart), ), array( 'id' => $elementArbre->id, ) );
				}
			}
			elseif( !empty($elementsAvant) ) {
				if(!(in_array($elementArbre->id, $elementsAvant))) {
					$wpdb->update( $table, array( 'limiteDroite' => ($elementArbre->limiteDroite + $ecart), ), array( 'id' => $elementArbre->id, ) );
				}
			}
			elseif( !empty($elementsApres) ) {
				if(!(in_array($elementArbre->id, $elementsApres))) {
					$wpdb->update( $table, array( 'limiteDroite' => ($elementArbre->limiteDroite + $ecart), ), array( 'id' => $elementArbre->id, ) );
				}
			}
			else {
					$wpdb->update( $table, array( 'limiteDroite' => ($elementArbre->limiteDroite + $ecart), ), array( 'id' => $elementArbre->id, ) );
			}
		}

		// On r�cup�re l'�l�ment fils et ses descendants
		$elements = Arborescence::getByLimites($table, ($limiteGauche - $decrement ), ($limiteDroite - $decrement ));
		$ecartAuNouvelEmplacement = ($limiteDroiteDestination - ($limiteDroite - $decrement ) + $ecart - 1);
		foreach ($elements as $element) {
			$wpdb->update( $table, array( 'limiteGauche' => ($element->limiteGauche + $ecartAuNouvelEmplacement), 'limiteDroite' => ($element->limiteDroite + $ecartAuNouvelEmplacement), ), array( 'id' => $element->id, ) );
		}
	}


	/**
	*	Get all the tree for a given element
	*
	*	@param mixed $tableElement The element type we want to get the tree
	*	@param integer $idElement The element identifier we want to get the tree
	*
	*	@see completeTreeRecursive()
	*
	*	@return array $completeTree An array with the tree for the element we are on (with all the descendant)
	*/
	static function completeTree($tableElement, $idElement)
	{
		$completeTree = array();

		$racine = Arborescence::getElement($tableElement,$idElement);
		$idTable = $tableElement . '-' . $idElement;

		$elements = Arborescence::getFils(TABLE_GROUPEMENT, $racine, "nom ASC");
		$subElements = EvaGroupement::getUnitesDuGroupement($racine->id);
		$sousTable = TABLE_UNITE_TRAVAIL;

		/*	add the actual element	*/
		$completeTree[$tableElement.'-'.$idElement]['table'] = TABLE_GROUPEMENT;
		$completeTree[$tableElement.'-'.$idElement]['id'] = $racine->id;
		$completeTree[$tableElement.'-'.$idElement]['nom'] = $racine->nom;
		$completeTree[$tableElement.'-'.$idElement]['content'] = array();

		if((count($elements) > 0) || (count($subElements) > 0))
		{
			$completeTree[$tableElement.'-'.$idElement]['content'] = Arborescence::completeTreeRecursive($elements, $racine, $tableElement);
		}

		return $completeTree;
	}

	/**
	*	Get recursively the risqs for an element. (NB: this method is called by bilanParUnite())
	*
	*	@param mixed $elementsFils An object with the different children of the element we are looking for the risqs
	*	@param mixed $elementPere An object with the parent of the element we are looking for the risqs
	*	@param mixed $table The element type we want to get descendant and risqs
	*	@param mixed $idTable An identifier for the array to keep the link between all elements
	*
	*	@param array $sousElements An array with all sub elements (with they risqs) for a given element
	*/
	static function completeTreeRecursive($elementsFils, $elementPere, $table, $idTable = '')
	{
		$sousElements = $sousElementsEnfants = array();

		if(count($elementsFils) != 0)
		{
			foreach ($elementsFils as $element )
			{
				$idTable = $element->id;
				$sousElements[$idTable]['table'] = TABLE_GROUPEMENT;
				$sousElements[$idTable]['id'] = $element->id;
				$sousElements[$idTable]['nom'] = $element->nom;
				$sousElements[$idTable]['content'] = array();

				$elements_fils = Arborescence::getFils(TABLE_GROUPEMENT, $element, "nom ASC");
				$subElements = EvaGroupement::getUnitesDuGroupement($element->id);
				$trouveElement = count($elements_fils) + count($subElements);
				if($trouveElement)
				{
					$sousElements[$idTable]['content'] = Arborescence::completeTreeRecursive($elements_fils, $element, $table, $idTable);
				}
			}
		}

		$subElements = EvaGroupement::getUnitesDuGroupement($elementPere->id);
		$i = 0;
		if($idTable == '')
		{
			$idTable = $elementPere->id;
		}
		foreach($subElements as $subElement)
		{
			$sousElements[$idTable][$i]['table'] = TABLE_UNITE_TRAVAIL;
			$sousElements[$idTable][$i]['id'] = $subElement->id;
			$sousElements[$idTable][$i]['nom'] = $subElement->nom;
			$i++;
		}

		return $sousElements;
	}

	/**
	*	Return an array with the complete list of work unit placed under a group
	*
	*	@param mixed $tableElement The element type we want to have the work unit list
	*	@param integer $idElement The element identifier we want to have the work unit list
	*
	*	@return array $unit An array with the list of work unit
	*/
	function getCompleteUnitList($tableElement, $idElement)
	{
		$completeTree = arborescence::completeTree($tableElement, $idElement);
		$unit = array();
		if( is_array($completeTree) )
		{
			foreach($completeTree as $key => $content)
			{
				if(isset($content['content']) && is_array($content['content']))
				{
					foreach($content['content'] as $index => $subContent)
					{
						if( isset($subContent['table']) && isset($subContent['id']) )
						{
							$unit = array_merge($unit, arborescence::getCompleteUnitList($subContent['table'], $subContent['id']));
						}
						else
						{
							foreach($subContent as $subContentIndex => $subContentContent)
							{
								$unit[] = $subContentContent;
							}
						}
					}
				}
			}
		}

		return $unit;
	}

	/**
	*	Return an array with the complete list of group placed under a group
	*
	*	@param mixed $tableElement The element type we want to have the group list
	*	@param integer $idElement The element identifier we want to have the group list
	*
	*	@return array $group An array with the list of work group
	*/
	function getCompleteGroupList($tableElement, $idElement)
	{
		$completeTree = arborescence::completeTree($tableElement, $idElement);
		$group = array();
		if( is_array($completeTree) )
		{
			foreach($completeTree as $key => $content)
			{
				if($content['table'] == TABLE_GROUPEMENT)
				{
					unset($groupInfos);$groupInfos = array();
					$groupInfos['table'] = $content['table'];
					$groupInfos['id'] = $content['id'];
					$groupInfos['nom'] = $content['nom'];
					$group[] = $groupInfos;
				}

				if(isset($content['content']) && is_array($content['content']))
				{
					foreach($content['content'] as $index => $subContent)
					{
						if( isset($subContent['table']) && isset($subContent['id']) )
						{
							$group = array_merge($group, arborescence::getCompleteGroupList($subContent['table'], $subContent['id']));
						}
						else
						{
							if($subContent['table'] == TABLE_GROUPEMENT)
							{
								foreach($subContent as $subContentIndex => $subContentContent)
								{
									$groupInfos['table'] = $subContentContent['table'];
									$groupInfos['id'] = $subContentContent['id'];
									$groupInfos['nom'] = $subContentContent['nom'];
									$group[] = $groupInfos;
								}
							}
						}
					}
				}
			}
		}

		return $group;
	}

	function display_element_main_infos( $table_element, $id_element ) {
		$main_infos = '';

		if ( !empty($table_element) && !empty($id_element) ) {
			global $wpdb;
			$query = $wpdb->prepare(" SELECT nom FROM " . $table_element . " WHERE id = %d", $id_element);
			$element_name = $wpdb->get_var( $query );
			switch ( $table_element ) {
				case TABLE_TACHE:
					$main_infos = ' - ' . ELEMENT_IDENTIFIER_T . $id_element . ' - ' . $element_name;
					break;
				case TABLE_ACTIVITE:
					$main_infos = ' - ' . ELEMENT_IDENTIFIER_ST . $id_element . ' - ' . $element_name;
					break;
				case TABLE_GROUPEMENT:
					$main_infos = ' - ' . ELEMENT_IDENTIFIER_GP . $id_element . ' - ' . $element_name;
					break;
				case TABLE_UNITE_TRAVAIL:
					$main_infos = ' - ' . ELEMENT_IDENTIFIER_UT . $id_element . ' - ' . $element_name;
					break;
				case TABLE_CATEGORIE_DANGER:
					$main_infos = ' - ' . ELEMENT_IDENTIFIER_CD . $id_element . ' - ' . $element_name;
					break;
				case TABLE_DANGER:
					$main_infos = ' - ' . ELEMENT_IDENTIFIER_D . $id_element . ' - ' . $element_name;
					break;
				case TABLE_METHODE:
					$main_infos = ' - ' . ELEMENT_IDENTIFIER_ME . $id_element . ' - ' . $element_name;
					break;
				case TABLE_CATEGORIE_PRECONISATION:
					$main_infos = ' - ' . ELEMENT_IDENTIFIER_CP . $id_element . ' - ' . $element_name;
					break;
				case TABLE_PRECONISATION:
					$main_infos = ' - ' . ELEMENT_IDENTIFIER_P . $id_element . ' - ' . $element_name;
					break;
			}
		}

		return $main_infos;
	}
}