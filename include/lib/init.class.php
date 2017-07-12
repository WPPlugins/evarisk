<?php
/**
 * Plugin Loader
 *
 * Define the different element usefull for the plugin usage. The menus, includes script, start launch script, css, translations
 * @author Evarisk <dev@evarisk.com>
 * @version 5.1.2.9
 * @package Digirisk
 * @subpackage librairies
 */

/**
 * Define the different element usefull for the plugin usage. The menus, includes script, start launch script, css, translations
 * @package Digirisk
 * @subpackage librairies
 */
class digirisk_init
{
	/**
	*	Load the different element need to create the plugin environnement
	*/
	public static function digirisk_plugin_load(){
		add_action( 'admin_notices', array('digirisk_admin_notification', 'admin_notice_message_define') );


		/* Ajout des options	*/
		add_action('admin_init', array('digirisk_options', 'declare_options'));

		/*	Get the current language to translate the different text in plugin	*/
		$locale = get_locale();
		$moFile = EVA_HOME_DIR . 'languages/evarisk-' . $locale . '.mo';
		if( !empty($locale) && (is_file($moFile)) ){
			load_textdomain('evarisk', $moFile);
		}
		else{
			load_textdomain('evarisk', EVA_HOME_DIR . 'languages/evarisk-fr_FR.mo');
		}

		if((isset($_GET['page']) && (substr($_GET['page'], 0, 9) == 'digirisk_')) || (basename($_SERVER['PHP_SELF']) == 'user-edit.php')){
			// Ajout des script de eva_admin
			add_action('admin_head', array('digirisk_init', 'digirisk_add_admin_js'));
			/*	Include the different javascript	*/
			add_action('admin_init', array('digirisk_init', 'digirisk_admin_js') );
			/*	Include the different css	*/
			add_action('admin_init', array('digirisk_init', 'digirisk_admin_css') );
		}

		/*	Add the right management for users	*/
		if(current_user_can('digi_manage_user_right')){
			add_action('edit_user_profile', array('digirisk_permission', 'user_permission_management'));
		}
		add_action('admin_init', array('digirisk_permission', 'user_permission_set'));

		/* On initialise le formulaire seulement dans la page de cr�ation/�dition */
		if (isset($_GET['page'],$_GET['action']) && $_GET['page']=='digirisk_doc' && $_GET['action']=='edit') {
			add_action('admin_init', array('digirisk_doc', 'init_wysiwyg'));
		}
		/* On r�cup�re la liste des pages document�es afin de les comparer a la page courante */
		$digirisk_doc = new digirisk_doc();
		$pages_list = $digirisk_doc->get_doc_pages_name_array();
		if (isset($_GET['page']) && in_array($_GET['page'], $pages_list)) {
			add_action('contextual_help', array('digirisk_doc', 'pippin_contextual_help'), 10, 3);
		}

		/*	Include the different css and js for frontend output	*/
		$main_options = get_option('digirisk_options');
		if($main_options['digi_ac_allow_front_ask'] == 'oui'){
			add_action('wp_print_styles', array('digirisk_init', 'frontend_css'));
			add_action('wp_head', array('digirisk_init', 'frontend_js_output'));
			add_action('init', array('digirisk_init', 'frontend_js'));
		}

		add_filter( 'manage_users_columns', array( 'digirisk_init', 'column_register_wpse_101322' ) );
		add_filter( 'manage_users_custom_column', array( 'digirisk_init', 'column_display_wpse_101322' ) , 10, 3 );
	}

	public static function column_register_wpse_101322( $columns ) {
		$columns['uid'] = 'ID';
		return $columns;
	}

	public static function column_display_wpse_101322(  $empty, $column_name, $user_id  ) {
		if ( 'uid' != $column_name )
			return $empty;

		return "<strong>" . ELEMENT_IDENTIFIER_U . $user_id . "</strong>";
	}



	/**
	 * Define the javascript to include in each page
	 */
	public static function digirisk_admin_js(){
		global $wp_version;
		wp_enqueue_script('jquery');

		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-form');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_script('jquery-ui-autocomplete');
		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-droppable');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('eva_timepicker', EVA_INC_PLUGIN_URL . 'js/jquery-libs/jquery-datetimepicker.js', '', EVA_PLUGIN_VERSION, true);

		wp_enqueue_script('eva_main_js', EVA_INC_PLUGIN_URL . 'js/lib.js', '', EVA_PLUGIN_VERSION);

		wp_enqueue_script('eva_jq_datatable', EVA_INC_PLUGIN_URL . 'js/dataTable/jquery.dataTables.js', '', EVA_PLUGIN_VERSION);
		wp_enqueue_script('eva_jq_chosen', EVA_INC_PLUGIN_URL . 'js/jquery-libs/chosen.jquery.min.js', '', EVA_PLUGIN_VERSION);
		wp_enqueue_script('eva_jq_treetable', EVA_INC_PLUGIN_URL . 'js/jquery-libs/jquery.treeTable.js', '', EVA_PLUGIN_VERSION);
		wp_enqueue_script('eva_jq_galleriffic', EVA_INC_PLUGIN_URL . 'js/jquery-libs/jquery.galleriffic.js', '', EVA_PLUGIN_VERSION);
		wp_enqueue_script('eva_jq_galover', EVA_INC_PLUGIN_URL . 'js/jquery-libs/jquery.opacityrollover.js', '', EVA_PLUGIN_VERSION);
		wp_enqueue_script('eva_jq_fieldselect', EVA_INC_PLUGIN_URL . 'js/jquery-libs/jquery-fieldselection.js', '', EVA_PLUGIN_VERSION);
		wp_enqueue_script('eva_role_js', EVA_INC_PLUGIN_URL . 'js/role.js', '', EVA_PLUGIN_VERSION);
		wp_enqueue_script('eva_jq_numeric', EVA_INC_PLUGIN_URL . 'js/jquery-libs/jquery.numeric.js', '', EVA_PLUGIN_VERSION);

		wp_enqueue_script('eva_google_jsapi', 'http://www.google.com/jsapi', '', EVA_PLUGIN_VERSION);
		/*	Wordpress postbox	*/
		wp_enqueue_script('eva_wp_postbox_js', admin_url() . 'js/postbox.js', '', EVA_PLUGIN_VERSION);

		wp_enqueue_script('jquery_keypad_min', EVA_INC_PLUGIN_URL . 'js/keypad/jquery.keypad.min.js', '', EVA_PLUGIN_VERSION);
		wp_enqueue_script('jquery_keypad_fr', EVA_INC_PLUGIN_URL . 'js/keypad/jquery.keypad-fr.js', '', EVA_PLUGIN_VERSION);

		/*	Jquery plot utilities	*/
		if(!empty($_GET['page']) && ($_GET['page'] == 'digirisk_risk_evaluation')){
			wp_enqueue_script('eva_jqplot', EVA_INC_PLUGIN_URL . 'js/charts/jquery.jqplot.js', '', EVA_PLUGIN_VERSION);
			wp_enqueue_script('eva_jqplot_excanvas', EVA_INC_PLUGIN_URL . 'js/charts/excanvas.js', '', EVA_PLUGIN_VERSION);
			wp_enqueue_script('eva_jqplot_pointLabel', EVA_INC_PLUGIN_URL . 'js/charts/jqplot.pointLabel.min.js', '', EVA_PLUGIN_VERSION);
			wp_enqueue_script('eva_jqplot_cursor', EVA_INC_PLUGIN_URL . 'js/charts/jquery.cursor.min.js', '', EVA_PLUGIN_VERSION);
			wp_enqueue_script('eva_jqplot_dateAxisRenderer', EVA_INC_PLUGIN_URL . 'js/charts/jquery.dateAxisRenderer.min.js', '', EVA_PLUGIN_VERSION);
			wp_enqueue_script('eva_jqplot_highlighter', EVA_INC_PLUGIN_URL . 'js/charts/jquery.highlighter.min.js', '', EVA_PLUGIN_VERSION);
		}
	}

	/**
	 * Display administration scripts
	 */
	public static function digirisk_add_admin_js(){
		echo '<script type="text/javascript" >
			var EVA_AJAX_FILE_URL = "' . EVA_INC_PLUGIN_URL . 'ajax.php";
			var DIGI_CHOSEN_NO_RESULT = "' . __('Aucun r&eacute;sultat trouv&eacute;', 'evarisk') . '";
			var DIGI_CHOSEN_SELECT_FROM_MULTI_LIST = "' . __('Choisissez une valeur dans la liste', 'evarisk') . '";
			var DIGI_CHOSEN_SELECT_FROM_LIST = "' . __('Choisissez une valeur', 'evarisk') . '";
			var DIGI_AJAX_CHOSEN_KEEP_TYPING = "' . __('Continuez &agrave; taper pour lancer la recherche', 'evarisk') . '";
			var DIGI_AJAX_CHOSEN_SEARCHING = "' . __('La recherche est en cours pour', 'evarisk') . '";
			var DIGI_USER_AFFECTATION_DATE_TEXT_IN = "' . __('Entr&eacute;e', 'evarisk') . ' : ";
			var DIGI_USER_AFFECTATION_DATE_TEXT_OUT = "' . __('Sortie', 'evarisk') . ' : ";
			var DIGI_DATETIMEPICKER_NOW = "' . __('Maintenant', 'evarisk') . '";
			var DIGI_DATETIMEPICKER_DONE = "' . __('OK', 'evarisk') . '";
			var DIGI_USER_DESAFFECTATION_DATE_INCONSISTENCY = "' . __('La date s&eacute;lectionn&eacute;e est inf&eacute;rieure &agrave; la date d\'affectation.\r\nSi vous confirmez cet enregistrement, une incoh&eacute;rence sera enregistr&eacute;e sur l\'affectation de cet utilisateur', 'evarisk') . '";
			var DIGI_USER_LIST_DESAFFECTATION_DATE_INCONSISTENCY = "' . __('La date s&eacute;lectionn&eacute;e est inf&eacute;rieure &agrave; une ou plusieurs date(s) d\'affectation.\r\nSi vous confirmez cet enregistrement, une incoh&eacute;rence sera enregistr&eacute;e sur l\'affectation des utilisateurs %s', 'evarisk') . '";
			var DIGI_CHOSEN_ATTRS = {disable_search_threshold: 5, no_results_text: DIGI_CHOSEN_NO_RESULT, placeholder_text_single : DIGI_CHOSEN_SELECT_FROM_LIST, placeholder_text_multiple : DIGI_CHOSEN_SELECT_FROM_MULTI_LIST};
			jQuery.fn.highlight=function(b){function a(e,j){var l=0;if(e.nodeType==3){var k=e.data.toUpperCase().indexOf(j);if(k>=0){var h=document.createElement("span");h.className="highlight";var f=e.splitText(k);var c=f.splitText(j.length);var d=f.cloneNode(true);h.appendChild(d);f.parentNode.replaceChild(h,f);l=1}}else{if(e.nodeType==1&&e.childNodes&&!/(script|style)/i.test(e.tagName)){for(var g=0;g<e.childNodes.length;++g){g+=a(e.childNodes[g],j)}}}return l}return this.each(function(){a(this,b.toUpperCase())})};jQuery.fn.removeHighlight=function(){return this.find("span.highlight").each(function(){this.parentNode.firstChild.nodeName;with(this.parentNode){replaceChild(this.firstChild,this);normalize()}}).end()};
		</script>';
	}

	/**
	*	Admin javascript "frontend" part definition
	*/
	public static function frontend_js(){
		global $wp_version;
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-form');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_script('jquery-ui-slider');

		wp_enqueue_script('eva_main_js', EVA_INC_PLUGIN_URL . 'js/lib.js', '', EVA_PLUGIN_VERSION);

		wp_enqueue_script('jquery_keypad_min', EVA_INC_PLUGIN_URL . 'js/keypad/jquery.keypad.min.js', '', EVA_PLUGIN_VERSION);
		wp_enqueue_script('jquery_keypad_fr', EVA_INC_PLUGIN_URL . 'js/keypad/jquery.keypad-fr.js', '', EVA_PLUGIN_VERSION);
	}

	/**
	*	Define the css to include in each page
	*/
	public static function digirisk_admin_css(){
		wp_register_style('digirisk_admin_notif_css', EVA_INC_PLUGIN_URL . 'css/eva_admin_notification.css', '', EVA_PLUGIN_VERSION);
		wp_enqueue_style('digirisk_admin_notif_css');

		wp_register_style('eva_jquery_ui', EVA_INC_PLUGIN_URL . 'css/jquery-libs/jquery-ui.css', '', EVA_PLUGIN_VERSION);
		wp_enqueue_style('eva_jquery_ui');

		wp_register_style('eva_jquery_chosen', EVA_INC_PLUGIN_URL . 'css/jquery-libs/chosen.css', '', EVA_PLUGIN_VERSION);
		wp_enqueue_style('eva_jquery_chosen');

		wp_register_style('eva_jquery_datatable', EVA_INC_PLUGIN_URL . 'css/jquery-libs/datatable.css', '', EVA_PLUGIN_VERSION);
		wp_enqueue_style('eva_jquery_datatable');

		wp_register_style('eva_jquery_treetable', EVA_INC_PLUGIN_URL . 'css/jquery-libs/treeTable.css', '', EVA_PLUGIN_VERSION);
		wp_enqueue_style('eva_jquery_treetable');
		wp_enqueue_style('eva_jquery_fileuploader');
		wp_register_style('eva_lightbox_css', EVA_INC_PLUGIN_URL . 'css/lightbox.css', '', EVA_PLUGIN_VERSION);
		wp_enqueue_style('eva_lightbox_css');

		if(!empty($_GET['page']) && ($_GET['page'] == 'digirisk_risk_evaluation')){
			wp_register_style('eva_jqplot_css', EVA_INC_PLUGIN_URL . 'css/jquery-libs/jquery.jqplot.css', '', EVA_PLUGIN_VERSION);
			wp_enqueue_style('eva_jqplot_css');
		}

		wp_register_style('eva_jquery_keypad', EVA_INC_PLUGIN_URL . 'js/keypad/jquery.keypad.css', '', EVA_PLUGIN_VERSION);
		wp_enqueue_style('eva_jquery_keypad');

		wp_register_style('eva_main_css', EVA_INC_PLUGIN_URL . 'css/eva.css', '', EVA_PLUGIN_VERSION);
		wp_enqueue_style('eva_main_css');
	}

	/**
	*	Admin javascript "file" part definition
	*/
	public static function frontend_css() {
		wp_register_style('eva_main_css', EVA_INC_PLUGIN_URL . 'css/eva.css', '', EVA_PLUGIN_VERSION);
		wp_enqueue_style('eva_main_css');
		wp_register_style('eva_jquery_ui', EVA_INC_PLUGIN_URL . 'css/jquery-libs/jquery-ui.css', '', EVA_PLUGIN_VERSION);
		wp_enqueue_style('eva_jquery_ui');
		wp_register_style('eva_jquery_keypad', EVA_INC_PLUGIN_URL . 'js/keypad/jquery.keypad.css', '', EVA_PLUGIN_VERSION);
		wp_enqueue_style('eva_jquery_keypad');
	}

	/**
	*	Admin javascript "frontend" part definition
	*/
	public static function frontend_js_output(){
		echo '<script type="text/javascript">var EVA_AJAX_FILE_URL = "' . EVA_INC_PLUGIN_URL . 'ajax.php";</script>';
	}

	/**
	 * Allows to associate a survey to an element of digirisk
	 *
	 * @param string $current_possible_association_list The current html output for survey association with existing custom types
	 *
	 * @return string The new list of element available for association
	 */
	public static function digi_survey_association( $current_possible_association_list, $current_association ) {
		$display = new wpes_display();

		/**	Add Society to available list	*/
		$current_possible_association_list .= $display->display( 'wpes_survey_post_type_association_item', array( 'SURVEY_ASSOCIATION_POST_TYPE' => TABLE_GROUPEMENT, 'SURVEY_ASSOCIATION_POST_TYPE_NAME' => __('Groupements evarisk', 'evarisk'), 'SURVEY_ASSOCAITION_CHECKBOX_STATE' => (!empty($current_association) && is_array($current_association) && in_array(TABLE_GROUPEMENT, $current_association) ? ' checked="checked"' : '')) );

		/**	Add Work unit to available list	*/
		$current_possible_association_list .= $display->display( 'wpes_survey_post_type_association_item', array( 'SURVEY_ASSOCIATION_POST_TYPE' => TABLE_UNITE_TRAVAIL, 'SURVEY_ASSOCIATION_POST_TYPE_NAME' => __('Unités de travail evarisk', 'evarisk'), 'SURVEY_ASSOCAITION_CHECKBOX_STATE' => (!empty($current_association) && is_array($current_association) && in_array(TABLE_UNITE_TRAVAIL, $current_association) ? ' checked="checked"' : '')) );

		$current_possible_association_list .= $display->display( 'wpes_survey_post_type_association_item', array( 'SURVEY_ASSOCIATION_POST_TYPE' => TABLE_TACHE, 'SURVEY_ASSOCIATION_POST_TYPE_NAME' => __('Tâches evarisk', 'evarisk'), 'SURVEY_ASSOCAITION_CHECKBOX_STATE' => (!empty($current_association) && is_array($current_association) && in_array(TABLE_TACHE, $current_association) ? ' checked="checked"' : '')) );

		$current_possible_association_list .= $display->display( 'wpes_survey_post_type_association_item', array( 'SURVEY_ASSOCIATION_POST_TYPE' => TABLE_ACTIVITE, 'SURVEY_ASSOCIATION_POST_TYPE_NAME' => __('Sous-Tâches evarisk', 'evarisk'), 'SURVEY_ASSOCAITION_CHECKBOX_STATE' => (!empty($current_association) && is_array($current_association) && in_array(TABLE_ACTIVITE, $current_association) ? ' checked="checked"' : '')) );

		return $current_possible_association_list;
	}

}

?>