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
class wpdigi_societies_ctr {

	/**	Défini la route par défaut permettant d'accèder aux sociétés depuis WP Rest API  / Define the default route for accessing to society from WP Rest API	*/
	protected $base = '/digirisk/societe';

	/**
	 * Instanciation principale de l'extension / Plugin instanciation
	 */
	function __construct() {
		/*	Création du menu dans l'administration pour le module digirisk / Create the administration menu for digirisk plugin */
		add_action('admin_menu', array( &$this, 'admin_menu' ) );

		/**	Appel des styles pour l'administration / Call style for administration	*/
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_css' ) );

		/**	Création des types d'éléments pour la gestion des entreprises / Create element types for societies management	*/
		add_action( 'init', array( &$this, 'custom_post_type' ) );

		/**	Etend les routes du plugin WP-Rest-API / Extend WP-Rest-API plugin routes	*/
		add_filter( 'json_endpoints', array( &$this, 'register_routes' ) );
	}

	/**
	 * Définition du menu dans l'administration de wordpress pour Digirisk / Define the menu for wordpress administration
	 */
	public function admin_menu() {
		/**	Création du menu de gestion de la société et de l'évaluation des risques / Create the menu for society strcuture management and risk evaluation	*/
// 		add_submenu_page( 'digirisk_dashboard',  __( 'Digirisk : Society management and Risk evaluation', 'wpdigi-i18n' ), __( 'Risk evaluation', 'wpdigi-i18n' ), 'digi_view_evaluation_menu', 'digirisk-risk-evaluation', array( &$this, 'dashboard' ) );
	}

	/**
	 * Inclusion des feuilles de styles pour l'administration / Admin css enqueue
	 */
	function admin_css() {
		wp_register_style( 'wpdigi-stes-transfert', WPDIGI_STES_URL . '/assets/css/backend.css', '', WPDIGI_STES_VERSION );
		wp_enqueue_style( 'wpdigi-stes-transfert' );
	}

	/**
	 * SETTER - Création des types d'éléments pour la gestion de l'entreprise / Create the different element for society management
	 */
	function custom_post_type() {
		/**	Créé les sociétés: élément principal / Create society : main element 	*/
		$labels = array(
			'name'                => __( 'Societies', 'wpdigi-societies-i18n' ),
			'singular_name'       => __( 'Society', 'wpdigi-societies-i18n' ),
			'menu_name'           => __( 'Societies', 'wpdigi-societies-i18n' ),
			'name_admin_bar'      => __( 'Societies', 'wpdigi-societies-i18n' ),
			'parent_item_colon'   => __( 'Parent Item:', 'wpdigi-societies-i18n' ),
			'all_items'           => __( 'Societies', 'wpdigi-societies-i18n' ),
			'add_new_item'        => __( 'Add society', 'wpdigi-societies-i18n' ),
			'add_new'             => __( 'Add society', 'wpdigi-societies-i18n' ),
			'new_item'            => __( 'New society', 'wpdigi-societies-i18n' ),
			'edit_item'           => __( 'Edit society', 'wpdigi-societies-i18n' ),
			'update_item'         => __( 'Update society', 'wpdigi-societies-i18n' ),
			'view_item'           => __( 'View society', 'wpdigi-societies-i18n' ),
			'search_items'        => __( 'Search society', 'wpdigi-societies-i18n' ),
			'not_found'           => __( 'Not found', 'wpdigi-societies-i18n' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'wpdigi-societies-i18n' ),
		);
		$rewrite = array(
			'slug'                => '/',
			'with_front'          => true,
			'pages'               => true,
			'feeds'               => true,
		);
		$args = array(
			'label'               => __( 'Digirisk society', 'wpdigi-societies-i18n' ),
			'description'         => __( 'Manage societies into digirisk', 'wpdigi-societies-i18n' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes', ),
			'hierarchical'        => true,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'rewrite'             => $rewrite,
			'capability_type'     => 'page',
		);
		register_post_type( WPDIGI_STES_POSTTYPE_MAIN, $args );
	}

	/**
	 * DISPLAY - Affichage de l'écran principal pour la gestion de la structure de la société et l'évaluation des risques / Display main screen for society management and risk evaluation
	 */
	public function dashboard() {
		require_once( wpdigi_utils::get_template_part( WPDIGI_STES_DIR, WPDIGI_STES_TEMPLATES_MAIN_DIR, 'backend', 'dashboard/dashboard' ) );
	}

	/**
	 * Etend les routes du plugin WP-Rest-API / Extend WP-Rest-API plugin routes
	 *
	 * @param array $routes Current available routes into WP Rest API
	 *
	 * @return array Existing routes extended with routes for current module
	 */
	public function register_routes( $routes ) {
		$wpdigi_societies_mdl  = new wpdigi_societies_mdl();

		$routes[ '/0.1/get' . $this->base ] = array(
			array( array( $wpdigi_societies_mdl, 'get_posts'), WP_JSON_Server::READABLE | WP_JSON_Server::ACCEPT_JSON )
		);

		$routes[ '/0.1/put' . $this->base ] = array(
			array( array( $wpdigi_societies_mdl, 'new_post'), WP_JSON_Server::CREATABLE | WP_JSON_Server::ACCEPT_JSON ),
		);

		$routes[ '/0.1/get' . $this->base . '/(?P<id>\d+)'] = array(
			array( array( $wpdigi_societies_mdl, 'get_post'), WP_JSON_Server::READABLE ),
		);

		$routes[ '/0.1/post' . $this->base . '/(?P<id>\d+)'] = array(
			array( array( $wpdigi_societies_mdl, 'edit_post'), WP_JSON_Server::EDITABLE | WP_JSON_Server::ACCEPT_JSON ),
		);

		$routes[ '/0.1/delete' . $this->base . '/(?P<id>\d+)'] = array(
			array( array( $wpdigi_societies_mdl, 'delete_post'), WP_JSON_Server::DELETABLE ),
		);

		return $routes;
	}

}
