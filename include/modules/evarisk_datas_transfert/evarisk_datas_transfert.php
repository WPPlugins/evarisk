<?php
/*
Plugin Name: Evarisk - Datas transfer
Description: Display an interface for transfering datas from Digirisk v5 to wordpress database
Version: 1.0
Author: Evarisk
*/

/**
 * Bootstrap file
 *
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 */

/**
 * Define the current version for the plugin. Interresting for clear cache for plugin style and script
 * @var string Plugin current version number
 */
DEFINE('DIGI_DTRANS_VERSION', '1.0');

/**
 * Get the plugin main dirname. Allows to avoid writing path directly into code
 * @var string Dirname of the plugin
 */
DEFINE('DIGI_DTRANS_DIR', basename( dirname( __FILE__ ) ) );
DEFINE('DIGI_DTRANS_PATH', str_replace( DIGI_DTRANS_DIR, "", dirname( __FILE__ ) ) );
DEFINE('DIGI_DTRANS_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', str_replace( "\\", "/", DIGI_DTRANS_PATH ) ) );

/**	Load plugin translation	*/
load_plugin_textdomain( 'wp-digi-dtrans-i18n', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/**	Define the templates directories	*/
DEFINE( 'DIGI_DTRANS_TEMPLATES_MAIN_DIR', DIGI_DTRANS_PATH . DIGI_DTRANS_DIR . '/templates/' );
DEFINE( 'DIGI_DTRANS_NB_ELMT_PER_PAGE', 10 );
DEFINE( 'DIGI_DTRANS_MEDIAN_MAX_STEP', 3 );
DEFINE( 'DIGI_DTRANS_MAX_STEP', 4 );

/**	Controllers	*/
require_once( DIGI_DTRANS_PATH . DIGI_DTRANS_DIR . '/controller/wpdigi_dtransfert_ctr.php' );

/** Plugin initialisation */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( "WPMyTime/wp-my-time.php" ) ) {
	$wpdigi_dtransfert = new wpdigi_dtransfert_ctr( true );
	add_filter( 'wpdigi-display-datas-transfert-interface', array( $wpdigi_dtransfert, 'display_transfert_interface' ), 10, 2 );
}

?>