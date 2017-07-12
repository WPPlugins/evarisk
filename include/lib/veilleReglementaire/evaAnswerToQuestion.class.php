<?php
/**
 * 
 * @author Evarisk
 * @version v5.0
 */
require_once(EVA_CONFIG );
require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaGroupeQuestion.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaAnswer.class.php' );

class EvaAnswerToQuestion {
	
/*
 * Autres methodes
 */
	
	/**
	  * Returns the latest answer to of element the question.
	  * @param int $idQuestion Question identifier.
	  * @param string $tableElement Element table name.
	  * @param int $idElement Element that responded identifier.
	  * @return The latest answer of element to the question.
	  */
	static function getLatestAnswerByQuestionAndElement($idQuestion, $tableElement, $idElement)
	{
		global $wpdb;
		
		$idQuestion = (digirisk_tools::IsValid_Variable($idQuestion));
		$tableElement = (digirisk_tools::IsValid_Variable($tableElement));
		$idElement = (digirisk_tools::IsValid_Variable($idElement));
		
		$resultat = $wpdb->get_row
		(
			"SELECT * 
			FROM " . TABLE_REPONSE_QUESTION . " trq1
			WHERE trq1.id_question = '" . $idQuestion . "'
			AND trq1.nomTableElement = '" . $tableElement . "' 
			AND trq1.id_element = '" . $idElement . "'
			AND NOT EXISTS
			(
				SELECT * 
				FROM " . TABLE_REPONSE_QUESTION . " trq2
				WHERE trq2.id_question = '" . $idQuestion . "'
				AND trq2.nomTableElement = '" . $tableElement . "'
				AND trq2.id_element = '" . $idElement . "'
				AND trq2.date > trq1.date
			)"
		);
		return $resultat;
	}
	
	/**
	  * Returns the answers of the element at the date.
	  * @param date $date Date consider.
	  * @param string $tableElement Element table name.
	  * @param int $idElement Element that responded identifier.
	  * @param string $where SQL where condition.
	  * @param string $order SQL order condition.
	  * @return The answers of the element at the date.
	  */
	static function getAnswersByDateAndElement($date, $tableElement, $idElement, $where = 1, $order = "id_question ASC")
	{
		global $wpdb;
		
		$tableElement = (digirisk_tools::IsValid_Variable($tableElement));
		$idElement = (digirisk_tools::IsValid_Variable($idElement));
		$where = (digirisk_tools::IsValid_Variable($where));
		$order = (digirisk_tools::IsValid_Variable($order));
		$date = (digirisk_tools::IsValid_Variable($date));
		$sql = "SELECT * 
			FROM " . TABLE_REPONSE_QUESTION . "
			WHERE date = '" . $date . "'
			AND nomTableElement = '" . $tableElement . "'
			AND id_element = '" . $idElement. "'
			AND " . $where. "
			ORDER BY " . $order;
		$resultat = $wpdb->get_results($sql);
		return $resultat;
	}
	
	/**
	  * Returns all answers to all questions maching with the where condition and order by the order condition.
	  * @param string $where SQL where condition.
	  * @param string $order SQL order condition.
	  * @return The answers to all questions maching with the where condition and order by the order condition.
	  */
	static function getAnswersToQuestions($where = "1", $order = "date DESC") 
	{
		global $wpdb;
		
		$where = (digirisk_tools::IsValid_Variable($where));
		$order = (digirisk_tools::IsValid_Variable($order));
		
		$resultat = $wpdb->get_results( "SELECT * FROM " . TABLE_REPONSE_QUESTION . " WHERE " . $where . " ORDER BY " . $order);
		return $resultat;
	}
	
	static function getAnswersForStats($date, $tableElement, $idElement, $racineTexte)
	{
		global $wpdb;
		
		$idsQuestions = EvaGroupeQuestions::getIdsToutesQuestionsDuGroupeQuestions($racineTexte, $order="id ASC");
		foreach($idsQuestions as $idQuestion)
		{
			$tabIds[$idQuestion] = $idQuestion;
		}
		$reponses = EvaAnswer::getAnswers();
		unset($tabReponse);
		foreach($reponses as $reponse)
		{
			$tabReponse[$reponse->id] = $reponse->nom;
		}
		
		$where = "id_question in (" . implode(', ', $idsQuestions) . ")";
		$order = "id_reponse ASC";
		$reponsesAuxQuestions = EvaAnswerToQuestion::getAnswersByDateAndElement($date, $tableElement, $idElement, $where, $order);
		unset($resultat);
		if(($reponsesAuxQuestions != null) AND (count($reponsesAuxQuestions) > 0))
		{
			foreach($reponsesAuxQuestions as $key => $reponseALaQuestion)
			{
				$resultat[$tabReponse[$reponseALaQuestion->id_reponse]][] = $reponseALaQuestion->id_question;
				unset($tabIds[$reponseALaQuestion->id_question]);
			}
		}
		foreach($tabIds as $key => $id)
		{
			$resultat['Non r&eacute;pondu'][] = $id;
		}
		return $resultat;
	}
/*
  * Persistance
  */
	/**
	  * Save a new answer to a question
	  * @param int $idQuestion Question identifier.
	  * @param string $tableElement Element table name.
	  * @param int $idElement Element that responded identifier.
	  * @param date $date date of answer
	  * @param int $idReponse Identifier of answer in answer table.
	  * @param int $valeur Value of the answer
	  * @param string $observation Comments on the answer
	  * @param string $limiteValidite Date of expiry of the response.
	  */
	static function saveNewAnswerToQuestion($idQuestion, $tableElement, $idElement, $date, $idReponse, $valeur = null, $observation = null, $limiteValidite = null)
	{
		$status = 'error';
		global $wpdb;
		
		$idQuestion = digirisk_tools::IsValid_Variable($idQuestion);
		$tableElement = digirisk_tools::IsValid_Variable($tableElement);
		$idElement = digirisk_tools::IsValid_Variable($idElement);
		$date = digirisk_tools::IsValid_Variable($date);
		$idReponse = digirisk_tools::IsValid_Variable($idReponse);
		$valeur = digirisk_tools::IsValid_Variable($valeur);
		$observation =digirisk_tools::IsValid_Variable($observation);
		$limiteValidite =digirisk_tools::IsValid_Variable($limiteValidite);

		$latestAnswer = EvaAnswerToQuestion::getLatestAnswerByQuestionAndElement($idQuestion, $tableElement, $idElement);
		if($latestAnswer != NULL)
		{
			$latestAnswerDate = $latestAnswer->date;
		}
		else
		{
			$latestAnswerDate = '';
		}

		if($date != $latestAnswerDate)
		{
			if(($valeur == null) || ($valeur == ''))
			{
				$valeur = 'NULL';
			}
			else
			{
				$valeur = " '" . ($valeur) . "' ";
			}
			if(($observation == null) || ($observation == ''))
			{
				$observation = 'NULL';
			}
			else
			{
				$observation = " '" . ($observation) . "' ";
			}
			if(($limiteValidite == null) || ($limiteValidite == ''))
			{
				$limiteValidite = 'NULL';
			}
			else
			{
				$limiteValidite = " '" . ($limiteValidite) . "' ";
			}

			$sql = "INSERT INTO " . TABLE_REPONSE_QUESTION . " (id_question, id_element, nomTableElement, id_reponse, date, valeur, observation, limiteValidite, Status) VALUES ('" .  ($idQuestion) . "', '" .  ($idElement) . "', '" .  ($tableElement) . "', '" .  ($idReponse) . "', '" .  ($date) . "', " . $valeur . ", " .  $observation . ", " .  $limiteValidite . ", 'Valid')";
			if($wpdb->query($sql))
			{
				$status = 'ok';
			}
		}
		else
		{
			$status = EvaAnswerToQuestion::updateAnswerToQuestion($idQuestion, $tableElement, $idElement, $date, $idReponse, $valeur, $observation, $limiteValidite);
		}

		return $status;
	}
	
	/**
	  * Update the answer of the element to the question at the date.
	  * @param int $idQuestion Question identifier (not update).
	  * @param string $tableElement Element table name (not update).
	  * @param int $idElement Element that responded identifier (not update).
	  * @param date $date date of answer (not update).
	  * @param int $idReponse Identifier of answer in answer table.
	  * @param int $valeur Value of the answer.
	  * @param string $observation Comments on the answer.
	  * @param string $limiteValidite Date of expiry of the response.
	  */
	static function updateAnswerToQuestion($idQuestion, $tableElement, $idElement, $date, $idReponse, $valeur = null, $observation = null, $limiteValidite)
	{
		$status = 'error';
		global $wpdb;
		
		$idQuestion = digirisk_tools::IsValid_Variable($idQuestion);
		$tableElement = digirisk_tools::IsValid_Variable($tableElement);
		$idElement = digirisk_tools::IsValid_Variable($idElement);
		$date = digirisk_tools::IsValid_Variable($date);
		$idReponse = digirisk_tools::IsValid_Variable($idReponse);
		$valeur = digirisk_tools::IsValid_Variable($valeur);
		$observation = digirisk_tools::IsValid_Variable($observation);
		$limiteValidite = digirisk_tools::IsValid_Variable($limiteValidite);

		if(($valeur == null) || ($valeur == ''))
		{
			$valeur = 'NULL';
		}
		else
		{
			$valeur = " '" . ($valeur) . "' ";
		}
		if(($observation == null) || ($observation == ''))
		{
			$observation = 'NULL';
		}
		else
		{
			$observation = " '" . ($observation) . "' ";
		}
		if(($limiteValidite == null) || ($limiteValidite == ''))
		{
			$limiteValidite = 'NULL';
		}
		else
		{
			$limiteValidite = " '" . ($limiteValidite) . "' ";
		}
		
		$sql = "UPDATE " . TABLE_REPONSE_QUESTION . " SET id_reponse='" . ($idReponse) . "', valeur=" . $valeur . ", observation=" . $observation . ", limiteValidite=" . $limiteValidite . "
		WHERE id_question = '" . ($idQuestion) . "'
		AND nomTableElement = '" . ($tableElement) . "'
		AND id_element = '" . ($idElement) . "'
		AND date = '" . ($date) . "'";
		if( ($wpdb->query($sql)) )
		{
			$status = 'ok';
		}
		elseif($wpdb->last_error == '')
		{
			$status ='ok';
		}

		return $status;	
	}
	
	/**
	  * Set the status of the answer of the element to question at date to Delete 
	  */
	static function deleteAnswerToQuestion($idQuestion, $tableElement, $idElement, $date)
	{
		global $wpdb;
		$idQuestion = (digirisk_tools::IsValid_Variable($idQuestion));
		$tableElement = (digirisk_tools::IsValid_Variable($tableElement));
		$idElement = (digirisk_tools::IsValid_Variable($idElement));
		$date = (digirisk_tools::IsValid_Variable($date));
		
		$sql = "UPDATE " . TABLE_REPONSE_QUESTION . " set Status='Delete' 
		WHERE id_question = '" . $idQuestion . "'
		AND nomTableElement = '" . $tableElement . "'
		AND id_element = '" . $idElement . "'
		AND date = '" . $date . "'";
		$wpdb->query($sql);
	}
}