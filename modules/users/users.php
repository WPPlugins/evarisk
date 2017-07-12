<?php
/**
 * Plugin Name: Digirisk users management
 * Description: Gestion des utilisateurs pour digirisk ( droits / groupes / import / profil ) / Manage user into digirisk ( rights / groups / import / profile )
 * Version: 1.0
 * Author: Evarisk development team <dev@evarisk.com>
 * Author URI: http://www.evarisk.com/
 */

/**
 * Module bootstrap file
 * @author Evarisk development team <dev@evarisk.com>
 * @version 1.0
 */

/**
 * Define the current version for the plugin. Interresting for clear cache for plugin style and script
 * @var string Plugin current version number
 */
DEFINE('WPDIGI_USERS_VERSION', '1.0');

/**	Définition des constantes pour le module / Define constant for module	*/
DEFINE('WPDIGI_USERS_DIR', basename(dirname(__FILE__)));
DEFINE('WPDIGI_USERS_PATH_TO_MODULE', str_replace( str_replace( "\\", "/", WP_PLUGIN_DIR ), "", str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) ) );
DEFINE( 'WPDIGI_USERS_PATH', str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) );
DEFINE( 'WPDIGI_USERS_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPDIGI_USERS_PATH ) );

/**	Appel des traductions pour le module / Call translation for module	*/
load_plugin_textdomain( 'wpdigi-users-i18n', false, dirname(plugin_basename(__FILE__)) . '/languages/' );

/**	Définition du chemin absolu vers les templates / Define the templates absolute directories	*/
DEFINE( 'WPDIGI_USERS_TEMPLATES_MAIN_DIR', WPDIGI_USERS_PATH . '/templates/');

/**	Instanciation du module / Instanciate module	*/
include( WPDIGI_USERS_PATH . '/controller/wpdigi_users_ctr.php' );
$wpdigi_users_ctr  = new wpdigi_users_ctr();
