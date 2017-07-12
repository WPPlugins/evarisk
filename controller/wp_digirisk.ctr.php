<?php
/**
 * Fichier du controlleur principal de l'extension digirisk pour wordpress / Main controller file for digirisk plugin
 *
 * @author Evarisk development team <dev@evarisk.com>
 * @version 6.0
 */

/**
 * Classe du controlleur principal de l'extension digirisk pour wordpress / Main controller class for digirisk plugin
 *
 * @author Evarisk development team <dev@evarisk.com>
 * @version 6.0
 */
class wp_digirisk {

	/**
	 * Instanciation principale de l'extension / Plugin instanciation
	 */
	function __construct() {
		/**	Appel des scripts et styles pour le module digirisk dans la partie administration / Include styles and scripts for backend	*/
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_assets' ) );
		/**	Appel des scripts communs pour le module digirisk / Include common styles and scripts	*/
		add_action( 'admin_enqueue_scripts', array( &$this, 'common_js' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'common_js' ) );

		/*	Création du menu dans l'administration pour le module digirisk / Create the administration menu for digirisk plugin */
		add_action('admin_menu', array( &$this, 'admin_menu' ) );

		/**	Appel des scripts de mise à jour automatique de la base de données / Call automatic database structure update	*/
		if ( digirisk_options::getDbOption('base_evarisk') > 1 ) {
			add_action('admin_init', array( 'digirisk_install', 'update_digirisk' ) );
		}
	}

	/**
	 * Déclaration des scripts et styles / Enqueue scripts and styles
	 *
	 * @uses wp_register_style
	 * @uses wp_enqueue_style
	 * @uses wp_enqueue_script
	 */
	public function admin_assets() {
		wp_register_style( 'wpdigi-admin-styles', WPDIGI_URL . 'assets/css/admin.css', '', WPDIGI_VERSION );
		wp_enqueue_style( 'wpdigi-admin-styles' );
	}

	/**
	 * Inclusion des scripts permettant l'utilisation de Angular JS dans le module / Include scripts for using Angular JS into module
	 */
	public function common_js() {
		//wp_enqueue_script( 'wpdigi-angularjs', WPDIGI_URL . 'assets/js/angular.js', '', WPDIGI_VERSION, false );
	}

	/**
	 * Définition du menu dans l'administration de wordpress pour Digirisk / Define the menu for wordpress administration
	 */
	public function admin_menu() {
		/**	Ajout de la page des options pour le plugin quelque soit la version / Add the options menu in the options section	*/
		add_options_page( __( 'Digirisk : Settings', 'wpdigi-i18n' ), __( 'Digirisk', 'wpdigi-i18n' ), 'digi_view_options_menu', DIGI_URL_SLUG_MAIN_OPTION, array( 'digirisk_options', 'optionMainPage' ) );

		/**	Récupération de l'option contenant les informations de transfert de Digirisk / Get transfer option to know if transfer have been done or not	*/
// 		$digirisk_transfer = get_option( 'wpdigi-dtransfert' );

		/** Pour une première installation on affiche la page de "configuration" / For a first launch display the "config" page	*/
		if ( digirisk_options::getDbOption('base_evarisk') < 1 ) {
			/**	Création du menu principal / Create the main menu	*/
			add_menu_page( __( 'Digirisk : Installation', 'wpdigi-i18n' ), __( 'Digirisk', 'wpdigi-i18n' ), 'activate_plugins', 'digirisk_installation', array('digirisk_install', 'installation_form'), EVA_FAVICON, 3);
				/**	Création du premier sous-menu pour une redondance de l'installation / Duplicate the installer menu	*/
	// 			add_submenu_page( 'digirisk_installation', __( 'Digirisk : Installation', 'wpdigi-i18n' ), __( 'Installation', 'wpdigi-i18n' ),  'activate_plugins', 'digirisk_installation', array('digirisk_install', 'installation_form'));
		}

		/**	Dans le cas ou la version de digirisk correspond à la version du transfert on affiche l'interface de transfert / In case the digirisk version correspond to the transfer version, display transfer interface	*/
// 		else if ( ( digirisk_options::getDbOption( 'base_evarisk' ) == 92 ) && ( empty( $digirisk_transfer ) || empty( $digirisk_transfer[ 'state' ] ) ) ) {
// 			$wpdigi_transfert = new wpdigi_dtransfert_ctr();
// 			add_menu_page( __( 'Manage datas transfer from digirisk V5.X', 'wp-digi-dtrans-i18n' ), __( 'Digirisk', 'wp-digi-dtrans-i18n' ), 'manage_options', 'digirisk-transfert', array( &$wpdigi_transfert, 'transfer_page' ), EVA_FAVICON, 3);
// 		}

		/**	On affiche le menu général de Digirisk / Display the general menu for Digirisk	*/
		else {
			/**
			 * Menu principal / Main menu
			 */
			/**	Création du menu principal / Create the main menu	*/
			add_menu_page( __( 'Digirisk : Dashboard', 'wpdigi-i18n' ), __( 'Digirisk', 'wpdigi-i18n' ), 'digi_view_dashboard_menu', 'digirisk_dashboard', array( 'dashboard', 'dashboardMainPage' ), EVA_FAVICON, 3);
				/**	Création du premier sous-menu pour une redondance du tableau de bord / Duplicate the dashboard menu	*/
				add_submenu_page( 'digirisk_dashboard',  __( 'Digirisk : Dashboard', 'wpdigi-i18n' ), __( 'Dashboard', 'wpdigi-i18n' ), 'digi_view_dashboard_menu', 'digirisk_dashboard', array( 'dashboard', 'dashboardMainPage' ) );

				/**	Création du menu de gestion de la structure de l'entreprise et de l'évaluation des risques / Create the society structure and risk evaluation	*/
				add_submenu_page( 'digirisk_dashboard',  __( 'Digirisk : Society management and Risk evaluation', 'wpdigi-i18n' ), __( 'Risk evaluation', 'wpdigi-i18n' ), 'digi_view_evaluation_menu', 'digirisk_risk_evaluation', array( 'evaluationDesRisques', 'evaluationDesRisquesMainPage' ));

				/**	Création du menu pour la gestion des actions correctives / Create the correctiv actions management menu	*/
				add_submenu_page( 'digirisk_dashboard',  __( 'Digirisk : Correctiv actions', 'wpdigi-i18n' ), __( 'Correctiv actions', 'wpdigi-i18n' ), 'digi_view_correctiv_action_menu', 'digirisk_correctiv_actions', array( 'actionsCorrectives', 'actionsCorrectivesMainPage' ));

			/**
			 * Les menus utilisateurs sont gérés dans le module "users" / Users' menu are managed by "users" module
			 */

			/**	Création du menu pour les outils de digirisk / Create digirisk's tools menu	*/
			add_management_page( __( 'Digirisk : Digirisk tools', 'wpdigi-i18n'), __( 'Digirisk', 'wpdigi-i18n'), 'digi_tools_menu', 'digirisk_tools', array( 'digirisk_tools', 'main_page') );
		}
	}

}
