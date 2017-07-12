<?php
/**
 * 
 * @author Evarisk
 * @version v5.0
 */
require_once(EVA_CONFIG );
require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaGroupeQuestion.class.php' );

class EvaQuestion {
	
/*
 * Autres methodes
 */
	
	/**
	  * Returns the question witch is the identifier.
	  * @param int $id Question identifier search.
	  * @return The question witch is the identifier.
	  */
	static function getQuestion($id)
	{
		global $wpdb;
		$id = (digirisk_tools::IsValid_Variable($id));
		$id = (int) $id;
		
		$resultat = $wpdb->get_row( "SELECT * FROM " . TABLE_QUESTION . " WHERE id = " . $id);
		return $resultat;
	}
	
	/**
	  * Returns the question witch is the statement.
	  * @param string $enonce Question statement search.
	  * @return The question witch is the statement.
	  */
	static function getQuestionByStatement($enonce)
	{
		global $wpdb;
		$enonce = (digirisk_tools::IsValid_Variable($enonce));
		
		$resultat = $wpdb->get_row( "SELECT * FROM " . TABLE_QUESTION . " WHERE enonce='" . $enonce . "'");
		return $resultat;
	}
	
	/**
	  * Returns all question maching with the where condition and order by the order condition.
	  * @param string $where SQL where condition.
	  * @param string $order SQL order condition.
	  * @return The questions maching with the where condition and order by the order condition.
	  */
	static function getQuestions($where = "1", $order = "code ASC") {
		global $wpdb;
		$where = (digirisk_tools::IsValid_Variable($where));
		$order = (digirisk_tools::IsValid_Variable($order));
		
		$resultat = $wpdb->get_results( "SELECT * FROM " . TABLE_QUESTION . " WHERE " . $where . " ORDER BY " . $order);
		return $resultat;
	}
	
	/**
	  * Returns all questions belonging to the question group witch is identifier.
	  * @param int $idGroupeQuestion The SQL order condition.
	  * @param string $order The SQL order condition.
	  * @return the questions  belonging to the group witch is identifier.
	  */
	static function getQuestionsDuGroupeQuestions($idGroupeQuestion, $order="code ASC")
	{
		global $wpdb;
		$idGroupeQuestion = (digirisk_tools::IsValid_Variable($idGroupeQuestion));
		$order = (digirisk_tools::IsValid_Variable($order));
		
		$resultat = $wpdb->get_results( "SELECT * FROM " . TABLE_GROUPE_QUESTION . " WHERE id IN (SELECT id_groupe_question FROM " . TABLE_POSSEDE_QUESTION . " WHERE id_groupe_question =" . $idGroupeQuestion . ") ORDER BY ". $order);
		return $resultat;
	}
	
/*
  * Persistance
  */
	/**
	  * Save a new question
	  * @param string $enonce question statement
	  * @param string $code question code
	  */
	static function saveNewQuestion($enonce, $code, $idGroupeQuestions)
	{
		global $wpdb;
		$enonce = (digirisk_tools::IsValid_Variable($enonce));
		$code = (digirisk_tools::IsValid_Variable($code));
		$nomGroupeQuestion = (digirisk_tools::IsValid_Variable($nomGroupeQuestion));
		$enonce = str_replace("[retourALaLigne]","\n", $enonce);
		
		/* Question table filling */
		$sql = "INSERT INTO " . TABLE_QUESTION . " (`enonce`, `code`, `Status`) VALUES ('" . $enonce . "', '" . $code . "', 'Valid')";
		$wpdb->query($sql);
		/* Question and questions group link table filling */
		$question = EvaQuestion::getQuestionByStatement($enonce);
		$idQuestion = $question->id;
		$sql = "INSERT INTO " . TABLE_POSSEDE_QUESTION . " (`id_groupe_question`, `id_question`, `Status`) VALUES ('" . $idGroupeQuestions . "', '" . $idQuestion . "', 'Valid')";
		$wpdb->query($sql);
		/* Question and answer link table filling */
		for($i=1; $i<=5; $i++)
		{
			$sql = "INSERT INTO " . TABLE_ACCEPTE_REPONSE . " (`id_question`, `id_reponse`, `Status`) VALUES ('" . $idQuestion . "', '" . $i . "', 'Valid')";
			$wpdb->query($sql);
		}
	}
	
	/**
	  * @todo
	  */
	static function updateQuestion($idQuestion, $enonce, $code = '')
	{
	}	
	
	/**
	  *
	  */
	static function transfertQuestion($idQuestion, $idGroupeQuestions, $idGroupeQuestionsOriginel)
	{
		global $wpdb;
		
		$idQuestion = digirisk_tools::IsValid_Variable($idQuestion);
		$idGroupeQuestions = digirisk_tools::IsValid_Variable($idGroupeQuestions);
		$idGroupeQuestionsOriginel = digirisk_tools::IsValid_Variable($idGroupeQuestionsOriginel);
		$sql = "UPDATE " . TABLE_POSSEDE_QUESTION . " set `id_groupe_question`=" . ($idGroupeQuestions) . " WHERE `id_question`=" . ($idQuestion) . " AND `id_groupe_question`=" . $idGroupeQuestionsOriginel;
		$wpdb->query($sql);
	}
	
	/**
	  * Set the status of the question wich is the identifier to Delete 
	  */
	static function deleteQuestion($idQuestion, $idGroupeQuestions)
	{
		global $wpdb;
		
		$sql = "UPDATE " . TABLE_POSSEDE_QUESTION . " set `Status`='Deleted' WHERE `id_question`=" .  ($idQuestion) . " AND `id_groupe_question`=" .  ($idGroupeQuestions);
		$wpdb->query($sql);
	}
}