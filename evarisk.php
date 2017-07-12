<?php
/*
 * Plugin Name: Evarisk
 * Plugin URI: http://www.evarisk.com/document-unique-logiciel
 * Description: Avec le plugin "Evarisk" vous pourrez r&eacute;aliser, de fa&ccedil;on simple et intuitive, le ou les documents uniques de vos entreprises et g&eacute;rer toutes les donn&eacute;es li&eacute;es &agrave; la s&eacute;curit&eacute; de votre personnel.
 * Version: 5.1.9.9
 * Author: Evarisk
 * Author URI: http://www.evarisk.com
*/


/**
 * Plugin main file.
 *
 * This file is the main file called by wordpress for our plugin use. It define the basic vars and include the different file needed to use the plugin
 * @author Evarisk <dev@evarisk.com>
 * @version 5.0
 * @package Digirisk
 */

DEFINE('EVA_PLUGIN_VERSION', '5.1.9.9');
DEFINE('EVA_PLUGIN_DIR', basename(dirname(__FILE__)));

/**	New plugin definition way	*/
DEFINE( 'WPDIGI_VERSION', EVA_PLUGIN_VERSION );
DEFINE( 'WPDIGI_DIR', basename( dirname( __FILE__ ) ) );
DEFINE( 'WPDIGI_PATH', str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) );
DEFINE( 'WPDIGI_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPDIGI_PATH ) );


/**	Chargement des fichiers de traductions / Load plugin translation	*/
load_plugin_textdomain( 'wpdigi-i18n', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );


/**	Include the different config for the plugin	*/
require_once(WP_PLUGIN_DIR . '/' . EVA_PLUGIN_DIR . '/include/config/config.php' );
DEFINE('EVA_CONFIG', EVA_INC_PLUGIN_DIR . 'config/config.php');

/**	Include the file which includes the different files used by all the plugin	*/
require_once(EVA_INC_PLUGIN_DIR . 'includes.php');

/**	On plugin activation launch actions	*/
register_activation_hook( __FILE__, array( 'wpdigi_utils', 'activation' ) );

/**	Appel automatique des modules présent dans le plugin / Install automatically modules into module directory	*/
digi_module_management::extra_modules();

/**	Instancation principale de l'extension digirisk pour wordpress / Main instanciation of digirisk plugin for wordpress	*/
require_once( WPDIGI_PATH . 'controller/wp_digirisk.ctr.php' );
$wp_digirisk = new wp_digirisk();


/** Include tools that will launch different action when plugin will be loaded	*/
require_once(EVA_LIB_PLUGIN_DIR . 'init.class.php' );
/**	On plugin loading, call the different element for creation output for our plugin */
add_action('plugins_loaded', array('digirisk_init', 'digirisk_plugin_load'));
add_filter( 'wpes_survey_association' ,  array('digirisk_init', 'digi_survey_association'), 10, 2);

/**	Add shortcode support for front	*/
add_shortcode('digirisk_correctiv_action', array('EvaActivity', 'task_asker')); // Ask a correctiv action

?>