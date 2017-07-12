<?php
/**
* Admin notification utilities
* 
* Define the different tools for plugin admin notification management
* @author Evarisk <dev@evarisk.com>
* @version 5.1.5.4
* @package Digirisk
* @subpackage librairies
*/

/**
* Define the different tools for plugin admin notification management
* @package Digirisk
* @subpackage librairies
*/
class digirisk_admin_notification{

	/**
	*
	*
	*/
	public static function admin_notification_define(){
		$notification = array();

		{/*	V-5.1.5.4	*/
			$version = '5.1.5.4';
			$notification[$version]['admin_notice'] = __('Une nouvelle version de l\'ED840 &eacute;dit&eacute; par l\'INRS a &eacute;t&eacute; mise en place dans le logiciel digirisk', 'evarisk');
			$notification[$version]['admin_notice_view_more'] = true;
			$notification[$version]['admin_full_notification'] = 
				sprintf(__('<li><a href="#" >Cr&eacute;ation des cat&eacute;gories de danger suivantes</a>:<ul><li>%s</li><li>%s</li><li>%s</li></ul></li>', 'evarisk'), __('Circulations internes', 'evarisk'), __('Rayonnements', 'evarisk'), __('Risques psychosociaux', 'evarisk')) . 
				sprintf(__('<li><a href="#" >Renommage des cat&eacute;gories de danger suivantes</a>:<ul><li>%s devient %s</li><li>%s devient %s</li><li>%s devient %s</li><li>%s devient %s</li><li>%s devient %s</li></ul></li>', 'evarisk'), __('Manutention manuelle', 'evarisk'), __('Activit&eacute; physique', 'evarisk'), __('Produits chimiques, d&eacute;chets', 'evarisk'), __('Produits, &eacute;missions et d&eacute;chets', 'evarisk'), __('Manque d\'hygi&egrave;ne', 'evarisk'), __('Agents biologique', 'evarisk'), __('Machine et outils', 'evarisk'), __('&Eacute;quipements de travail', 'evarisk'), __('Chute de plain-pied', 'evarisk'), __('Accident de plain-pied', 'evarisk')) . 
				sprintf(__('<li><a href="#" >D&eacute;placement des dangers suivants</a>:<ul><li>Le danger %s devient %s et est transf&eacute;r&eacute; vers la cat&eacute;gorie %s</li><li>Le danger %s devient %s et est transf&eacute;r&eacute; vers la cat&eacute;gorie %s</li><li>Le danger %s devient %s et est transf&eacute;r&eacute; vers la cat&eacute;gorie %s</li></ul></li>', 'evarisk'), __('Divers Manque de formation', 'evarisk'), __('Manque de formation', 'evarisk'), __('Autres', 'evarisk'), __('Divers travail sur &eacute;cran', 'evarisk'), __('Travail sur &eacute;cran', 'evarisk'), __('Activit&eacute; physique', 'evarisk'), __('Divers soci&eacute;t&eacute ext&eacuterieure', 'evarisk'), __('Soci&eacute;t&eacute ext&eacuterieure', 'evarisk'), __('Autres', 'evarisk'));
		}

		return $notification;
	}

	/**
	*
	*/
	function admin_notice_container($message, $container_class = ''){
?>
		<div class="updated digirisk_admin_notice <?php echo $container_class; ?>" id="<?php echo $container_class; ?>" >
			<div class="digirisk_message_container">
				<h4><?php echo $message; ?></h4>
			</div>
		</div>
<?php
	}

	/**
	*
	*/
	public static function admin_notice_message_define(){
		$view_more = false;
		$container_class = '';

		/*	Check if the current user has already read the notice	*/
		$current_user = wp_get_current_user();
		$user_meta_notification_read = get_user_meta($current_user->ID, 'digirisk_notification', true);

		/*	Get existing list of admin notification	*/
		$admin_notification_define = self::admin_notification_define();

		/*	Check if an notice exists for current db version	*/
		if(!empty($admin_notification_define[EVA_PLUGIN_VERSION]['admin_notice'])){
			if(empty($user_meta_notification_read['wiewed_notification'][EVA_PLUGIN_VERSION])){
				$user_meta_notification_read['wiewed_notification'][EVA_PLUGIN_VERSION] = true;
				$meta_update_result = update_user_meta($current_user->ID, 'digirisk_notification', $user_meta_notification_read);
			}
			$message = $admin_notification_define[EVA_PLUGIN_VERSION]['admin_notice'];
			if(!empty($admin_notification_define[EVA_PLUGIN_VERSION]['admin_notice_view_more']))$view_more = $admin_notification_define[EVA_PLUGIN_VERSION]['admin_notice_view_more'];
			$container_class = 'digirisk_message_version_' . str_replace('.', '_', EVA_PLUGIN_VERSION);
		}

		if(!empty($message) && empty($user_meta_notification_read['readed_notification'][EVA_PLUGIN_VERSION])){
			if($view_more)$message .= '<br/><br/><a class="digirisk_admin_notice_view_more_link" href="' . admin_url('tools.php?page=digirisk_tools') . '" > >> ' . __('Voir le d&eacute;tail', 'evarisk') . '</a>';
			self::admin_notice_container($message, $container_class);
		}
	}

	/**
	*
	*/
	function admin_message(){
		$message = 	'';
		/*	Get existing list of admin notification	*/
		$admin_notification_define = self::admin_notification_define();

		/*	Check if the current user has already read the notice	*/
		$current_user = wp_get_current_user();
		$user_meta_notification_read = get_user_meta($current_user->ID, 'digirisk_notification', true);

		foreach($admin_notification_define as $version => $notification){
			$notification_state = 'readed';
			if(empty($user_meta_notification_read['readed_notification'][$version])){
				$notification_state = 'not_readed';
			}
			$message .= '
<li class="clear digirisk_notification_message_' . $notification_state . '" id="digirsk_notification_' . $version . '" >
	<a class="digirisk_notification_version_number" href="#digirsk_notification_' . $version . '" ><img class="digirisk_notification_mail_state" src="' . EVA_IMG_PLUGIN_URL . $notification_state . '_notification.png" alt="' . $notification_state . '" />' . sprintf(__('Version %s', 'evarisk'), $version) . '&nbsp;:</a><br class="clear" />
	<ul class="digirisk_notification_version_detail" >' . $notification['admin_full_notification'] . '</ul>';
			if($notification_state == 'not_readed'){
				$message .= '
	<br/><span id="digi_notif_v_' . $version . '" class="digirisk_notification_mark_as_read_main_container digi_notice_marker_' . $notification_state . '" >' . sprintf(__('Marquer la notification de la version %s comme lue', 'evarisk'), $version) . ': <span class="digirisk_notification_mark_as_read pointer bold" >' . __('Pour moi uniquement', 'evarisk') . '</span>' .((current_user_can('digi_mark_notice_as_read_for_all')) ? ' - <span class="digirisk_notification_mark_as_read mark_for_all_user pointer" >' . sprintf(__('Pour tous les utilisateurs du site', 'evarisk'), $version) . '</span>' : '') . '</span>';
			}
			else{
				$message .= '
	<br/><span id="digi_notif_v_' . $version . '" class="pointer digirisk_notification_mark_as_unread digi_notice_marker_' . $notification_state . '" >' . sprintf(__('Marquer la notification de la version %s comme non lue', 'evarisk'), $version) . '</span>';
			}
			$message .= '
</li>';
		}

		if(!empty($admin_notification_define)){
			$message = '<ul>
' . $message . '
</ul>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery(".digirisk_notification_mark_as_read").click(function(){
			jQuery(this).html(jQuery("#round_loading_img").html());
			var forall = false;
			var ok_for_mark_as_read = true;
			if(jQuery(this).hasClass("mark_for_all_user")){
				forall = true;
				if(!confirm(digi_html_accent_for_js("' . __('&Ecirc;tes vous s&ucirc;r de vouloir marquer ce message comme lu pour l\'ensemble des utilisateurs?', 'evarisk') . '"))){
					ok_for_mark_as_read = false;
				}
			}
			if(ok_for_mark_as_read){
				jQuery("#digirisk_notification_tab").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
					"post": "true", 
					"nom": "digirisk_notification",
					"act": "mark_as_read",
					"version": jQuery(this).parent("span").attr("id").replace("digi_notif_v_", ""),
					"for_all_user" : forall
				});
			}
		});
		jQuery(".digirisk_notification_mark_as_unread").click(function(){
			jQuery(this).html(jQuery("#round_loading_img").html());
			jQuery("#digirisk_notification_tab").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
				"post": "true", 
				"nom": "digirisk_notification",
				"act": "mark_as_unread",
				"version": jQuery(this).attr("id").replace("digi_notif_v_", "")
			});
		});
	});
</script>';
		}
	
		return $message;
	}

}

?>