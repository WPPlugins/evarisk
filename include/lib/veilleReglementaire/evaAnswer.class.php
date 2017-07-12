<?php
/**
 * 
 * @author Evarisk
 * @version v5.0
 */
include_once(EVA_CONFIG );

class EvaAnswer {
	
/*
 * Autres methodes
 */
	
	/**
	  * Returns the answer witch is the identifier.
	  * @param int $id Answer identifier search.
	  * @return The answer witch is the identifier.
	  */
	static function getAnswer($id)
	{
		global $wpdb;
		$id = (digirisk_tools::IsValid_Variable($id));
		$id = (int) $id;
		
		$resultat = $wpdb->get_row( "SELECT * FROM " . TABLE_REPONSE . " WHERE id = " . $id);
		return $resultat;
	}
	
	/**
	  * Returns the question witch is the name.
	  * @param string $nom Question name search.
	  * @return The question witch is the name.
	  */
	static function getAnswerByName($nom)
	{
		global $wpdb;
		$nom = (digirisk_tools::IsValid_Variable($nom));
		
		$resultat = $wpdb->get_row( "SELECT * FROM " . TABLE_REPONSE . " WHERE nom='" . $nom . "'");
		return $resultat;
	}
	
	/**
	  * Returns all answers maching with the where condition and order by the order condition.
	  * @param string $where SQL where condition.
	  * @param string $order SQL order condition.
	  * @return The answers maching with the where condition and order by the order condition.
	  */
	static function getAnswers($where = "1", $order = "id ASC") {
		global $wpdb;
		$where = (digirisk_tools::IsValid_Variable($where));
		$order = (digirisk_tools::IsValid_Variable($order));
		
		$resultat = $wpdb->get_results( "SELECT * FROM " . TABLE_REPONSE . " WHERE " . $where . " ORDER BY " . $order);
		return $resultat;
	}
}