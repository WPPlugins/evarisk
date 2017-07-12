<?php
/**
 * 
 * @author Evarisk
 * @version v5.0
 */
require_once(EVA_CONFIG );
require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaGroupeQuestion.class.php' );

class EvaReferentialText {
	
/*
 * Autres methodes
 */
	
	/**
	  * Returns the referential text witch is the identifier.
	  * @param int $id Referential text identifier search.
	  * @return The referential text witch is the identifier.
	  */
	static function getReferentialText($id)
	{
		global $wpdb;
		$id = digirisk_tools::IsValid_Variable($id);
		$id = (int) $id;
		$result = $wpdb->get_row( "SELECT * FROM " . TABLE_TEXTE_REFERENCIEL . " WHERE id = " . ($id));
		return $result;
	}
	
	/**
	  * Returns the  referential text witch is the rubric.
	  * @param string $rubric  Referential text rubric search.
	  * @return The referential text witch is the rubric.
	  */
	static function getReferentialTextByRubric($rubric)
	{
		global $wpdb;
		$rubric = digirisk_tools::IsValid_Variable($rubric);
		$result = $wpdb->get_row( "SELECT * FROM " . TABLE_TEXTE_REFERENCIEL . " WHERE rubrique='" . ($rubric) . "'");
		return $result;
	}
	
	/**
	  * Returns all  referential texts maching with the where condition and order by the order condition.
	  * @param string $where SQL where condition.
	  * @param string $order SQL order condition.
	  * @param string $status Referential text status.
	  * @return The referential texts maching with the where condition and order by the order condition.
	  */
	static function getReferentialTexts($where = "1", $order = "id ASC", $status = "Valid") 
	{
		global $wpdb;
		$where =digirisk_tools::IsValid_Variable($where);
		$order = digirisk_tools::IsValid_Variable($order);
		$status =digirisk_tools::IsValid_Variable($status);
		$results = $wpdb->get_results( "SELECT * FROM " . TABLE_TEXTE_REFERENCIEL . " WHERE " .  ($where) . " AND status='" . ($status) . "' ORDER BY " .  ($order));
		return $results;
	}
	
/*
  * Persistance
  */
	/**
	  * Save a new referential text
	  */
	static function saveNewGroupeQuestions()
	{
	}
	
	/**
	 * Update the referential text which is the identifier.
	 * @param int $id Referential text identifier (not update).
	 */
	static function updateGroupeQuestions($id)
	{
	}	
	
	/**
	  * Set the status of the referential text wich is the identifier to Delete 
	 */
	static function deleteGroupeQuestions($id)
	{
		global $wpdb;
		$id = digirisk_tools::IsValid_Variable($id);
		
		$sql = "UPDATE " . TABLE_TEXTE_REFERENCIEL . " set `Status`='Deleted' WHERE `id`=" . ($id);
		$wpdb->query($sql);
	}
}