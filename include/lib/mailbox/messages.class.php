<?php
/**
*	Notification messages management
* 
* Define method to manage notification messages
* @author Evarisk <dev@evarisk.com>
* @version 5.1.4.5
* @package Digirisk
* @subpackage librairies
*/

/**
* Define method to manage notification messages
* @package Digirisk
* @subpackage librairies
*/
class digirisk_messages{
	const message_db_table = DIGI_DBT_MESSAGES;
	const message_history_db_table = DIGI_DBT_HISTORIC;

	/** Get a message by id
	* @return array
	*/
	function get_message($mid) {
		global $wpdb;
		
		$message = $wpdb->get_row('
			SELECT * FROM '.self::message_db_table.' 
			LEFT JOIN '.$wpdb->users.' ON user_id=ID
			WHERE id='.$mid.';
		');
		
		return !empty($message) ? $message : array();
	}
	
	/** Get the messages historic by message id
	* @return array
	*/
	function get_histo($mid) {
		global $wpdb;
		$histo = $wpdb->get_results('SELECT * FROM '.self::message_history_db_table.' WHERE message_id='.$mid.';');
		return !empty($histo) ? $histo : array();
	}
	
	/** Get the messages (unique)
	* @return void
	*/
	function get_messages($type='valid') {
		global $wpdb;
		
		if($type=='archived') {
			$messages = $wpdb->get_results('
				SELECT * FROM '.self::message_db_table.' 
				LEFT JOIN '.$wpdb->users.' ON user_id=ID
				WHERE status="archived"
				ORDER BY last_dispatch_date DESC
			');
		}
		else {
			$messages = $wpdb->get_results('
				SELECT * FROM '.self::message_db_table.' 
				LEFT JOIN '.$wpdb->users.' ON user_id=ID
				ORDER BY last_dispatch_date DESC
			');
		}

		return !empty($messages) ? $messages : array();
	}

	/** 
	*	Store a new message
	* @return boolean
	*/
	function add_message($recipient_id, $email, $title, $message, $id_notification, $id_element, $table_element){
		global $wpdb;
		$date = current_time('mysql', 0);

		// Insertion message
		$wpdb->insert(self::message_db_table, array(
			'status' => 'valid',
			'send_status' => 'sent',
			'creation_date' => $date,
			'last_dispatch_date' => $date,
			'user_id' => $recipient_id,
			'id_notification' => $id_notification,
			'id_element' => $id_element,
			'table_element' => $table_element,
			'title' => $title,
			'user_email' => $email,
			'message' => $message
		));
		$message_id = $wpdb->insert_id;

		// Insertion dans l'historique
		$wpdb->insert(self::message_history_db_table, array(
			'status' => 'valid',
			'creation_date' => $date,
			'message_id' => $message_id
		));

		return true;
	}
	
	/** Return the number of messages by type
	* @return void
	*/
	function message_count($type='valid') {
		global $wpdb;
		
		if($type=='archived') {
			$count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.self::message_db_table.' WHERE status="archived";', ''));
		}
		else {
			$count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.self::message_db_table.';', ''));
		}
		
		return !empty($count) ? $count : 0;
	}
}
?>