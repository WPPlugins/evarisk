<?php
/**
 * 
 * @author Evarisk
 * @version v5.0
 */
require_once(EVA_CONFIG );
require_once(EVA_LIB_PLUGIN_DIR . 'arborescence.class.php' );

class EvaGroupeQuestions {
	
/*
 * Autres methodes
 */
	
	/**
	  * Returns the question group witch is the identifier.
	  * @param int $id Question group identifier search.
	  * @return The question  witch is the identifier.
	  */
	static function getGroupeQuestions($id)
	{
		global $wpdb;
		$id = (digirisk_tools::IsValid_Variable($id));
		$id = (int) $id;
		$resultat = $wpdb->get_row( "SELECT * FROM " . TABLE_GROUPE_QUESTION . " WHERE id = " . $id);
		return $resultat;
	}
	
	/**
	  * Returns the question group witch is the name.
	  * @param string $nom Question group name search.
	  * @return The group  witch is the name.
	  */
	static function getGroupeQuestionsByName($nom)
	{
		global $wpdb;
		$nom = (digirisk_tools::IsValid_Variable($nom));
		$resultat = $wpdb->get_row( "SELECT * FROM " . TABLE_GROUPE_QUESTION . " WHERE nom='" . $nom . "'");
		return $resultat;
	}
	
	/**
	  * Returns all question group maching with the where condition and order by the order condition.
	  * @param string $where SQL where condition.
	  * @param string $order SQL order condition.
	  * @return The question groups maching with the where condition and order by the order condition.
	  */
	static function getGroupesQuestions($where = "Status='Valid'", $order = "code ASC") {
		global $wpdb;
		$where = (digirisk_tools::IsValid_Variable($where));
		$order = (digirisk_tools::IsValid_Variable($order));
		$resultat = $wpdb->get_results( "SELECT * FROM " . TABLE_GROUPE_QUESTION . " WHERE " . $where . " ORDER BY " . $order);
		return $resultat;
	}
	
	/**
	  * Returns all questions belonging to the question group witch is identifier.
	  * @param int $idGroupeQuestion The SQL order condition.
	  * @param string $order The SQL order condition.
	  * @param string $status The state (Valid, Moderated, Deleted) search .
	  * @return the questions  belonging to the group witch is identifier.
	  */
	static function getQuestionsDuGroupeQuestions($idGroupeQuestion, $order="code ASC", $status="Valid")
	{
		global $wpdb;
		$idGroupeQuestion = (digirisk_tools::IsValid_Variable($idGroupeQuestion));
		$order = (digirisk_tools::IsValid_Variable($order));
		$resultat = $wpdb->get_results( "SELECT * FROM " . TABLE_QUESTION . " WHERE id IN (SELECT id_question FROM " . TABLE_POSSEDE_QUESTION . " WHERE id_groupe_question =" . $idGroupeQuestion . " AND Status='" . $status . "') AND Status='" . $status . "' ORDER BY ". $order );
		return $resultat;
	}
	
	/**
	  * Returns all questions belonging to the question group or his  descendants witch is identifier.
	  * @param int $idGroupeQuestion The SQL order condition.
	  * @param string $order The SQL order condition.
	  * @param string $status The state (Valid, Moderated, Deleted) search .
	  * @return the questions  belonging to the group witch is identifier.
	  */
	static function getIdsToutesQuestionsDuGroupeQuestions($idGroupeQuestion, $order="id ASC", $status="Valid")
	{
		global $wpdb;
		$idGroupeQuestion = (digirisk_tools::IsValid_Variable($idGroupeQuestion));
		$order = (digirisk_tools::IsValid_Variable($order));
		$groupeQuestion = EvaGroupeQuestions::getGroupeQuestions($idGroupeQuestion);
		
		$resultats = $wpdb->get_results( "
			SELECT * 
			FROM " . TABLE_QUESTION . " q
			WHERE q.id IN 
			(
				SELECT pq.id_question 
				FROM " . TABLE_POSSEDE_QUESTION . " pq
				WHERE pq.id_groupe_question in 
				(
					SELECT gq.id
					FROM " . TABLE_GROUPE_QUESTION . " gq
					WHERE gq.limiteGauche >= " . $groupeQuestion->limiteGauche . "
					AND gq.limiteDroite <= " . $groupeQuestion->limiteDroite . "
					AND Status = '" . $status . "'
				)
				AND Status = '" . $status . "'
			)
			AND Status = '" . $status . "'
			ORDER BY ". $order
		);
		unset($ids);
		$ids;
		if($resultats != null)
		{
			foreach($resultats as $resultat)
			{
				$ids[] = $resultat->id;
			}
		}
		return $ids;
	}
	
/*
  * Persistance
  */
	/**
	  * Save a new question group
	  * @param string $nom question group unit name
	  */
	static function saveNewGroupeQuestions($nom)
	{
		global $wpdb;
		$nom = (digirisk_tools::IsValid_Variable($nom));		
		
		$lim = Arborescence::getMaxLimiteDroite(TABLE_GROUPE_QUESTION);
		$sql = "INSERT INTO " . TABLE_GROUPE_QUESTION . " (`nom`, `Status`, `limiteGauche`, `limiteDroite`) VALUES ('" . $nom . "', 'Valid', '" . ($lim) . "', '" . ($lim+1) . "')";
		$wpdb->query($sql);
		$sql = "UPDATE " . TABLE_GROUPE_QUESTION . " SET `limiteDroite`= '" . ($lim + 2)  . "' WHERE`nom` = ('Groupe Question Racine')";
		$wpdb->query($sql);
	}
	
	/**
	 * Update the question group which is the identifier.
	 * @param int $idGroupeQuestion Question group identifier (not update).
	 * @param string $nom Question group name.
	 * @param string $code Question group code.
	 * @param string $nomGroupeQuestionPere father question group name.
	 * @param string $extraitTexte Question group piece of text. 
	 */
	static function updateGroupeQuestions($idGroupeQuestion, $nom, $code, $idGroupeQuestionPere, $extraitTexte = null)
	{
		global $wpdb;
		$idGroupeQuestion = (digirisk_tools::IsValid_Variable($idGroupeQuestion));
		$nom = (digirisk_tools::IsValid_Variable($nom));
		$code = (digirisk_tools::IsValid_Variable($code));
		$idGroupeQuestionPere = (digirisk_tools::IsValid_Variable($idGroupeQuestionPere));
		$extraitTexte = (digirisk_tools::IsValid_Variable($extraitTexte));
		$nom = str_replace("[retourALaLigne]","\n", $nom);
		
		$sql = "UPDATE `" . TABLE_GROUPE_QUESTION . "` SET `nom`='" . $nom . "', `code`='" . $code . "' WHERE `id`='" . $idGroupeQuestion . "'";
		$wpdb->query($sql);
		if($extraitTexte != null)
		{
			$sql = "UPDATE `" . TABLE_GROUPE_QUESTION . "` SET `extraitTexte`='" . $extraitTexte . "' WHERE `id`='" . $idGroupeQuestion . "'";
			$wpdb->query($sql);
		}
		
		$groupeQuestionFils = EvaGroupeQuestions::getGroupeQuestions($idGroupeQuestion);
		$groupeQuestionDestination = EvaGroupeQuestions::getGroupeQuestions($idGroupeQuestionPere);
		$groupeQuestionPere = Arborescence::getPere(TABLE_GROUPE_QUESTION, $groupeQuestionFils);
		if($groupeQuestionDestination->nom != $groupeQuestionPere->nom)
		{
			$racine = $wpdb->get_row( "SELECT * FROM " . TABLE_GROUPE_QUESTION . " WHERE nom='Groupe Question Racine'");
			Arborescence::deplacerElements(TABLE_GROUPE_QUESTION, $racine, $groupeQuestionFils, $groupeQuestionDestination);
		}
	}
	
	/**
	 * Update the piece of text of the question group which is the identifier.
	 * @param int $idGroupeQuestion Question group identifier (not update).
	 * @param string $extraitTexte Question group text piece. 
	 */
	static function updateExtraitGroupeQuestions($idGroupeQuestion, $extraitTexte)
	{
		global $wpdb;
		
		$idGroupeQuestion = digirisk_tools::IsValid_Variable($idGroupeQuestion);
		$extraitTexte = digirisk_tools::IsValid_Variable($extraitTexte);
		$extraitTexte = str_replace("[retourALaLigne]","\n", $extraitTexte);
		
		$sql = "UPDATE `" . TABLE_GROUPE_QUESTION . "` SET `extraitTexte`='" . ($extraitTexte) . "' WHERE `id`='" . ($idGroupeQuestion) . "'";
		$wpdb->query($sql);
	}
	
	
	/**
	  * Set the status of the question group wich is the identifier to Delete 
	 */
	static function deleteGroupeQuestions($id)
	{
		global $wpdb;
		$id = (digirisk_tools::IsValid_Variable($id));
		$groupeQuestions = EvaGroupeQuestions::getGroupeQuestions($id);
		
		$sql = "UPDATE " . TABLE_GROUPE_QUESTION . " set `Status`='Deleted' WHERE `limiteGauche`>=" . $groupeQuestions->limiteGauche . " AND `limiteDroite`<=" . $groupeQuestions->limiteDroite;
		$wpdb->query($sql);
	}
}