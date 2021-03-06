<?php
/**
 * Fichier du controleur principal pour la gestion des modules internes dans les extensions wordpress / Main controller file for internal modules management into wordpress plugins
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 2.0
 */

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'EVA_PLUGIN_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'digi-modmanager-i18n') );
}

/**
 * Classe du controleur principal pour la gestion des modules internes dans les extensions wordpress / Main controller class for internal modules management into wordpress plugins
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 2.0
 */
class digi_module_management {

	private static $module_directory;

	private static $core_directory;

	private static $option_name = 'digirisk_module';

	private static $text_domain = 'digi-modmanager-i18n';

	private static $log_name = 'wps_addon';

	/**
	 * Instanciation du gestionnaire de modules /  Instanciate modules manager
	 */
	function __construct() {
		/**	Assign var for directory containing modules	*/
		self::$core_directory =  WPDIGI_PATH . 'core/';
		self::$module_directory =  WPDIGI_PATH . 'modules/';

		/**	Ajoute une interface aux options pour gérer les modules / Add an interface to plugin options screen in order to manage modules	*/
		add_action( 'admin_init', array( $this, 'declare_options' ), 11 );

		/**	Appel des styles pour l'administration / Call style for administration	*/
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_assets' ) );
	}

	/**
	 * Inclusion des feuilles de styles pour l'administration / Admin css enqueue
	 */
	function admin_assets() {
		wp_register_style( 'digi-modmanager-admin-css', DIGIMODMAN_URL . '/assets/css/backend.css', '', DIGIMODMAN_VERSION );
		wp_enqueue_style( 'digi-modmanager-admin-css' );
	}

	/**
	 * OPTIONS - Déclare les options permettant de gérer les statuts des modules / Declare add-on configuration panel for managing modules status
	 */
	function declare_options() {
		add_settings_section( 'wps_internal_modules', '<i class="dashicons dashicons-admin-plugins"></i>' . __( 'Internal modules management', self::$text_domain ), '', 'wpshop_addons_options' );
		register_setting( 'wpshop_options', self::$option_name, array( &$this, 'validate_options' ) );

		add_settings_field( 'wpshop_opinions_field', __( 'Internal modules management', self::$text_domain ), array( &$this, 'module_listing' ), 'wpshop_addons_options', 'wps_internal_modules' );


		add_filter( 'digi-settings-tab', 'test_tab' );
		function test_tab() {
			echo '<li><a href="#digi-addons" title="optionsContent" id="tabOptions_Recommandation" >Addons</a></li>';
		}
		add_filter( 'digi-settings-tab-content', 'test_tab_content' );
		function test_tab_content() {
			echo '<div id="digi-addons" >'.do_settings_sections('wps_internal_modules').'</div>';
		}
	}

	/**
	 * OPTIONS - Check the sended options before saving them into database
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	function validate_options( $settings ) {
		if ( is_array( $settings ) ) {
			$module_option = get_option( self::$option_name );
			$log_error = array();
			foreach ( $settings as $module => $module_state ) {
				if ( !array_key_exists( 'activated', $module_state ) && ( 'on' == $module_state[ 'old_activated' ] ) ) {
					$module_option[ $module ][ 'activated' ] = 'off';
					$module_option[ $module ][ 'date_off' ] = gmdate( "Y-m-d H:i:s", time() );
					$module_option[ $module ][ 'author_off' ] = get_current_user_id();
					$settings[ $module ] = $module_option[ $module ];

					/**	Log module activation	*/
					$user = get_userdata( $module_option[ $folder ][ 'author_on' ] );
					$author = $user->display_name;
					$log_error[ 'message' ] = sprintf( __( 'Activation made on %1$s by %2$s', self::$text_domain ), mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $settings[ $module ][ 'date_on' ], true ), $author);
				}
				else if ( array_key_exists( 'activated', $module_state ) && ( 'off' == $module_state[ 'old_activated' ] ) ) {
					$module_option[ $module ][ 'activated' ] = 'on';
					$module_option[ $module ][ 'date_on' ] = gmdate( "Y-m-d H:i:s", time() );
					$module_option[ $module ][ 'author_on' ] = get_current_user_id();
					$settings[ $module ] = $module_option[ $module ];

					/**	Log module activation	*/
					$user = get_userdata( $module_option[ $folder ][ 'author_off' ] );
					$author = $user->display_name;
					$log_error[ 'message' ] = sprintf( __( 'Deactivation made on %1$s by %2$s', self::$text_domain ), mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $settings[ $module ][ 'date_off' ], true ), $author);
				}
				else {
					$settings[ $module ] = $module_option[ $module ];
				}
				unset( $settings[ $module ][ 'old_activated' ] );
				$log_error[ 'object_id' ] = $module;
			}

			wpeologs_ctr::log_datas_in_files( self::$log_name, $log_error, 0 );
		}

		return $settings;
	}

	/**
	 * OPTIONS - Affiche les modules présents et leur état actuel / Display all modules and they current state
	 */
	function module_listing() {
		/**	Define the directory containing all extra modules for current plugin	*/
		$module_folder = self::$module_directory;

		/**	Get	current modules options to know if they are activated or not */
		$module_option = get_option( self::$option_name );

		/**	Check if the defined directory exists for reading and displaying an input to activate/deactivate the module	*/
		if( is_dir( $module_folder ) ) {
			$parent_folder_content = scandir( $module_folder );

			require_once( wpshop_tools::get_template_part( DIGIMODMAN_DIR, DIGIMODMAN_TEMPLATES_MAIN_DIR, 'backend', 'settings' ) );
		}
		else {
			_e( 'There is no modules to include into current plugin', self::$text_domain );
		}

	}

	/**
	 * CORE - Activation des modules "coeur" ne devant pas être désactivés / Activation of "core" modules that does not have to be deactivated
	 */
	public static function core_util() {
		/**	Define the directory containing all "core" modules for current plugin	*/
		$module_folder = self::$core_directory;

		/**	Check if the defined directory exists for reading and including the different modules	*/
		if( is_dir( $module_folder ) ) {
			$parent_folder_content = scandir( $module_folder );
			foreach ( $parent_folder_content as $folder ) {
				if ( $folder && substr( $folder, 0, 1) != '.' && ( DIGIMODMAN_DIR != $folder ) ) {
					if ( file_exists( $module_folder . $folder . '/' . $folder . '.php') ) {
						$f =  $module_folder . $folder . '/' . $folder . '.php';
						require( $f );
					}
				}
			}
		}
	}

	/**
	 * CORE - Activations des modules complémentaires pour l'extension / Activation of complementary modules for plugin
	 */
	public static function extra_modules() {
		/**	Define the directory containing all extra modules for current plugin	*/
		$module_folder = self::$module_directory;
		/**	Get	current modules options to know if they are activated or not */
		$module_option = get_option( self::$option_name );

		/**	Check if the defined directory exists for reading and including the different modules	*/
		if( is_dir( $module_folder ) ) {
			$parent_folder_content = scandir( $module_folder );
			$update_option = false;
			foreach ( $parent_folder_content as $folder ) {
				if ( $folder && substr( $folder, 0, 1) != '.' ) {
					$is_activated = false;
					/**	Check current module state to know if we have to include it or not	*/
					if ( !empty( $module_option ) && array_key_exists( $folder, $module_option ) && ( 'on' == $module_option[ $folder ][ 'activated' ] ) ) {
						$is_activated = true;
					}
					else if ( empty( $module_option ) || ( !empty( $module_option ) && !array_key_exists( $folder, $module_option ) ) ) {
						$modules_option[ $folder ] = array(
							'activated' => 'on',
							'date_on' => gmdate( "Y-m-d H:i:s", time() ),
							'author_on' => 'auto',
						);
						$is_activated = true;
						$update_option = true;
					}

					/**	Finaly include module if the state allow it	*/
					if ( $is_activated && file_exists( $module_folder . $folder . '/' . $folder . '.php') ) {
						$f =  $module_folder . $folder . '/' . $folder . '.php';
						require( $f );
					}
				}
			}
			/**	Update option only if it is necessary	*/
			if ( $update_option ) {
				update_option( self::$option_name, $modules_option );
			}
		}
	}

}
