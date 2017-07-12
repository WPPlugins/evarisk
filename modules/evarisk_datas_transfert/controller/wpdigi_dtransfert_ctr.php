<?php
/**
 * File for digirisk datas transfer control class definition
 *
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 *
 */

/**
 * Class for digirisk datas transfer control
 *
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 *
 */
class wpdigi_dtransfert_ctr {

	/**
	 * Déclaration de la correspondance entre les anciens types Evarisk et les nouveaux types dans wordpress / Declare an array for making correspondance between evarisk old types and wordpress new type
	 * @var array Correspondance between Evarisk types and wordpress types for element transfer
	 */
	protected $post_type = array(
		TABLE_TACHE => 'wpeomtm-tasks',
		TABLE_ACTIVITE => 'wpeomtm-tasks',
		TABLE_GROUPEMENT => 'wpdigi-ste',
		TABLE_UNITE_TRAVAIL => 'wpdigi-dpmt',
	);

	/**
	 * Déclaration des types principaux à transférer / Declare an array with main element to transfer
	 * @var array
	 */
	protected $element_types = array(
		TABLE_GROUPEMENT,
		TABLE_TACHE,
	);

	/**
	 * Initialise digirisk datas transfert controller
	 */
	function __construct( $with_menu = false ) {
		/**	Add admin menu for module management	*/
		if ( $with_menu ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}

		/**	Include the different javascript	*/
		add_action( 'admin_init', array( &$this, 'admin_js' ) );
		/**	Call style for administration	*/
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_css' ) );

		/**	Ajax actions	*/
		add_action( 'wp_ajax_wpdigi-datas-transfert', array( $this, 'ajax_launch_transfer' ), 150 );
		add_action( 'wp_ajax_wpdigi-heavydatas-transfert', array( $this, 'ajax_launch_heavy_transfer' ), 150 );
		add_action( 'wp_ajax_wpdigi-dtrans-get-done-element', array( $this, 'ajax_get_transfered_element_count' ), 150 );

		add_action( 'wp_ajax_wpdigi-dtrans-transfert-options-load', array( $this, 'ajax_load_transfert_options' ), 150 );
	}


	/**
	 * Include javascript librairies
	 */
	function admin_js() {
		wp_enqueue_script( 'wpdigi-datas-transfert', DIGI_DTRANS_URL . DIGI_DTRANS_DIR . '/assets/js/backend.js', array( 'jquery', 'jquery-form' ), DIGI_DTRANS_VERSION, true );
	}

	/**
	 * Include stylesheets
	 */
	function admin_css() {
		wp_register_style( 'wpdigi-datas-transfert', DIGI_DTRANS_URL . DIGI_DTRANS_DIR . '/assets/css/backend.css', '', DIGI_DTRANS_VERSION );
		wp_enqueue_style( 'wpdigi-datas-transfert' );
	}

	/**
	 * Create admin menu
	 */
	function admin_menu() {
		add_management_page( __( 'Manage datas transfert from digirisk V5.X', 'wp-digi-dtrans-i18n' ), __( 'Digirisk - transfer', 'wp-digi-dtrans-i18n' ), 'manage_options', 'digirisk-transfert', array( &$this, 'transfer_page' ));
	}


	/**
	 * Count the number of element to treat and the number of element already treated
	 *
	 * @param string $main_element_type The main element type to transfer
	 * @param string $sub_element_type The sub element type to transfer regarding the main element
	 *
	 * @return array The different element number stored into an array for having only one returned value
	 */
	function get_element_count( $main_element_type, $sub_element_type ) {
		global $wpdb;

		/**	Get the number of element that will be transfered for the given element type	*/
		$query = $wpdb->prepare( "SELECT (

			SELECT COUNT( DISTINCT( id ) )
			FROM {$main_element_type}
			WHERE id != 1

		) AS main_element_nb, (

			SELECT COUNT( DISTINCT( id ) )
			FROM {$sub_element_type}

		) AS sub_element_nb, (

			SELECT COUNT( DISTINCT( id ) )
			FROM " . TABLE_PHOTO_LIAISON . "
			WHERE tableElement IN ( %s, %s )

		) AS nb_pictures, (

			SELECT COUNT( DISTINCT( id ) )
			FROM " . TABLE_GED_DOCUMENTS . "
			WHERE table_element  IN ( %s, %s )

		) AS nb_documents", array( $main_element_type, $sub_element_type, $main_element_type, $sub_element_type, ) );

		/**	get the element number from database	*/
		$nb_element_to_transfert = $wpdb->get_row( $query );

		/**	Check if there has already been transfer done	*/
		$digirisk_transfert_options = get_option( 'wpdigi-dtransfert', array() );

		return array(
			'elements_to_transfer' => $nb_element_to_transfert,
			'allready_transfered' => $digirisk_transfert_options,
		);
	}


	/**
	 * DISPLAY - TOOLS - Display settings main page
	 */
	function transfer_page() {
		require( wpdigi_utils::get_template_part( DIGI_DTRANS_DIR, DIGI_DTRANS_TEMPLATES_MAIN_DIR, "backend", "transfert" ) );
	}


	/**
	 * AJAX - Load options for digirisk datas transfert
	 */
	function ajax_load_transfert_options() {
		global $wpdb;

		switch ( $_POST[ 'element_type' ] ) {
			case TABLE_TACHE:
				$distinct_users = array();
				/**	Get distinct user id from different field of evarisk	*/
				$query = $wpdb->prepare( "
				SELECT
					( SELECT GROUP_CONCAT( DISTINCT ( idCreateur ) )
					FROM " . TABLE_TACHE . " ) AS idCreateur,
					( SELECT GROUP_CONCAT( DISTINCT ( idResponsable ) )
					FROM " . TABLE_TACHE . " ) AS idResponsable,
					( SELECT GROUP_CONCAT( DISTINCT ( idSoldeur ) )
					FROM " . TABLE_TACHE . " ) AS idSoldeur,
					( SELECT GROUP_CONCAT( DISTINCT ( idSoldeurChef ) )
					FROM " . TABLE_TACHE . " ) AS idSoldeurChef ", "" );
				$all_users_main = $wpdb->get_results( $query );
				foreach ( $all_users_main as $user_type => $user_ids ) {
					foreach ( $user_ids as $user_id_string ) {
						$users_id_of_type = explode( ',', $user_id_string );
						foreach ( $users_id_of_type as $user_id ) {
							if ( !in_array( $user_id, $distinct_users ) ) {
								$distinct_users[] = $user_id;
							}
						}
					}
				}

				$query = $wpdb->prepare( "
				SELECT
					( SELECT GROUP_CONCAT( DISTINCT ( idCreateur ) )
					FROM " . TABLE_ACTIVITE . " ) AS idCreateur,
					( SELECT GROUP_CONCAT( DISTINCT ( idResponsable ) )
					FROM " . TABLE_ACTIVITE . " ) AS idResponsable,
					( SELECT GROUP_CONCAT( DISTINCT ( idSoldeur ) )
					FROM " . TABLE_ACTIVITE . " ) AS idSoldeur,
					( SELECT GROUP_CONCAT( DISTINCT ( idSoldeurChef ) )
					FROM " . TABLE_ACTIVITE . " ) AS idSoldeurChef ", "" );
				$all_users_secondary = $wpdb->get_results( $query );
				foreach ( $all_users_secondary as $user_type => $user_ids ) {
					foreach ( $user_ids as $user_id_string ) {
						$users_id_of_type = explode( ',', $user_id_string );
						foreach ( $users_id_of_type as $user_id ) {
							if ( !in_array( $user_id, $distinct_users ) ) {
								$distinct_users[] = $user_id;
							}
						}
					}
				}

				/**	Read the user list in order to build the output	*/
				if ( !empty( $distinct_users ) ) {
					require( wpdigi_utils::get_template_part( DIGI_DTRANS_DIR, DIGI_DTRANS_TEMPLATES_MAIN_DIR, "backend", "transfert", "user-options" ) );
				}

			break;
		}

		wp_die();
	}

	/**
	 * AJAX - Launch data transfert when submitting the form into transfert interface
	 */
	function ajax_launch_transfer() {
		global $wpdb;

		$response = array (
			'element_nb_treated'	=> 0,
			'reload_transfert'		=> false,
			'message'				=> __( 'A required parameter is missing, please check your request and try again', 'wp-digi-dtrans-i18n' ),
		);
		$element_type = !empty( $_POST[ 'element_type_to_transfert' ] ) ? $_POST[ 'element_type_to_transfert' ] : '';
		$response[ 'element_type' ] = $element_type;

		if ( !empty( $element_type ) ) {

			$current_log_settings = get_option( '_wpeo_log_settings', array() );
			$current_log_settings[ 'my_services' ][ 'digirisk-datas-transfert-document' ] = array(
					'service_active' 		=> 1,
					'service_name' 			=> 'digirisk-datas-transfert-document',
					'service_size' 			=> 999999999999,
					'service_size_format' 	=> 'oc',
					'service_rotate' 		=> false,
			);
			$current_log_settings[ 'my_services' ][ 'digirisk-datas-transfert-picture' ] = array(
					'service_active' 		=> 1,
					'service_name' 			=> 'digirisk-datas-transfert-picture',
					'service_size' 			=> 999999999999,
					'service_size_format' 	=> 'oc',
					'service_rotate' 		=> false,
			);
			$current_log_settings[ 'my_services' ][ 'digirisk-datas-transfert-wp_eva__actions_correctives_tache' ] = array(
					'service_active' 		=> 1,
					'service_name' 			=> 'digirisk-datas-transfert-wp_eva__actions_correctives_tache',
					'service_size' 			=> 999999999999,
					'service_size_format' 	=> 'oc',
					'service_rotate' 		=> false,
			);
			$current_log_settings[ 'my_services' ][ 'digirisk-datas-transfert-wp_eva__actions_correctives_actions' ] = array(
					'service_active' 		=> 1,
					'service_name' 			=> 'digirisk-datas-transfert-wp_eva__actions_correctives_actions',
					'service_size' 			=> 999999999999,
					'service_size_format' 	=> 'oc',
					'service_rotate' 		=> false,
			);
			update_option( '_wpeo_log_settings', $current_log_settings );


			/**	Launch transfer for current element direct children of subtype	*/
			switch( $element_type ) {
				case TABLE_TACHE:
					$sub_element_type = TABLE_ACTIVITE;
					break;
				case TABLE_GROUPEMENT:
					$sub_element_type = TABLE_UNITE_TRAVAIL;
					break;
			}

			/**	Get transfert statistics */
			$element_count = $this->get_element_count( $element_type, $sub_element_type );
			$nb_element_to_transfert = $element_count[ 'elements_to_transfer' ];
			$main_element_already_moved = !empty( $element_count[ 'allready_transfered' ][ $element_type ] ) ? count( $element_count[ 'allready_transfered' ][ $element_type ] ) : 0;
			$sub_element_already_moved = !empty( $element_count[ 'allready_transfered' ][ $sub_element_type ] ) ? count( $element_count[ 'allready_transfered' ][ $sub_element_type ] ) : 0;

			$response[ 'reload_transfert' ] = true;
			$response[ 'message' ] = __( 'Import will automatically continue while all elements won\'t be transfered into database', 'wp-digi-dtrans-i18n' );
			if ( $main_element_already_moved < $nb_element_to_transfert->main_element_nb ) {
				/**	Check if current element type has a root element in order to exclude it from datas transfert	*/
				$root_element = Arborescence::getRacine( $element_type );
				/**	Retrieve elements to store into database	*/
				$first_level_elements = Arborescence::getFils( $element_type, $root_element, "limiteGauche ASC", null, null, "1" );
				foreach ( $first_level_elements as $element ) {
					if ( !empty( $element ) ) {
						$this->transfer( $element_type, $element );
					}
				}

				/**	Create the element that are orphelan due to errors during moving in old tree - Main element	*/
				$this->transfer_orphelan( $element_type );
			}
			else if ( $sub_element_already_moved < $nb_element_to_transfert->sub_element_nb ) {
				/**	Start the query buiding	*/
				$query = $wpdb->prepare( "SELECT * FROM " . $sub_element_type . " WHERE 1" );

				/**	Check if there are already element of current types that have been transferd in order to exclude them of query	*/
				$transfered_element = get_option( 'wpdigi-dtransfert', array() );
				if ( !empty( $transfered_element ) && !empty( $transfered_element[ $sub_element_type ] ) && is_array( $transfered_element[ $sub_element_type ] ) ) {
					$query .= " AND id NOT IN ('" . implode( "', '", $transfered_element[ $sub_element_type ] ) . "')";
				}

				$query .= " LIMIT 0, " . $_POST[ 'number_per_page' ];

				/**	Get current element sub type	*/
				$children = $wpdb->get_results( $query );
				if ( !empty( $children ) ) {
					foreach ( $children as $child ) {
						switch ( $element_type ) {
							case TABLE_TACHE:
								$field_for_parent = 'id_tache';
							break;

							case TABLE_GROUPEMENT:
								$field_for_parent = 'id_groupement';
							break;
						}
						/**	Get the current element parent identifier into the new system	*/
						$query = $wpdb->prepare( "
							SELECT post_id
							FROM {$wpdb->postmeta}
							WHERE ( meta_value = %s AND meta_key = %s )", $element_type . '#value_sep#' . $child->$field_for_parent, '_wpdigi_elt_old' );
						$new_element_id = $wpdb->get_var( $query );

						/**	Launch transfert for element subtype	*/
						$new_children_id = $this->transfer( $sub_element_type, $child, $new_element_id );
						if ( !empty( $new_children_id ) && is_int( $new_children_id ) ) {
							$response[ 'element_nb_treated' ]++;
						}
					}
				}
				$the_count_response = $this->build_element_transfert_number( $element_type );
				$response[ 'nb' ] = $the_count_response[ 'transfert' ][0][ 'text' ];
				$response[ 'type' ] = $element_type;
				//	$this->transfer_orphelan( $sub_element_type );
			}

			/**	In case that all element have been transfered (Main and Sub) stop the script	*/
			if ( ( $main_element_already_moved + $sub_element_already_moved ) == ( $nb_element_to_transfert->main_element_nb + $nb_element_to_transfert->sub_element_nb ) ) {
				$response[ 'reload_transfert' ] = false;
				$response[ 'message' ] = __( 'All elements have been transfered to new storage way into wordpress database. Now heavy datas will be transfered.', 'wp-digi-dtrans-i18n' );
				$response[ 'buttonText' ] = __( 'Move documents and pictures', 'wp-digi-dtrans-i18n' );

				/**	Get already done element */
				$digirisk_transfert_options = get_option( 'wpdigi-dtransfert', array() );
				$digirisk_transfert_options[ 'state' ] = 'first_step_complete';
				update_option( 'wpdigi-dtransfert', $digirisk_transfert_options );
			}

		}

		wp_die( json_encode( $response ) );
	}

	/**
	 * AJAX - Launch transfert for associated datas too heavy for being launched with all other datas
	 */
	function ajax_launch_heavy_transfer() {
		global $wpdb;
		$where = "";
		$all_heavy_element_done = false;
		$response = array(
			'reload_transfert' => true,
		);

		$main_element_type = !empty( $_POST[ 'element_type_to_transfert' ] ) ? $_POST[ 'element_type_to_transfert' ] : '';
		/**	Launch transfer for current element direct children of subtype	*/
		switch( $main_element_type ) {
			case TABLE_TACHE:
				$sub_element_type = TABLE_ACTIVITE;
				break;
			case TABLE_GROUPEMENT:
				$sub_element_type = TABLE_UNITE_TRAVAIL;
				break;
		}

		/**	Get already done element */
		$digirisk_transfert_options = get_option( 'wpdigi-dtransfert', array() );

		/**
		 *
		 * Pictures treatment
		 *
		 */
		if ( !empty( $digirisk_transfert_options ) && !empty( $digirisk_transfert_options[ 'pictures' ] ) && is_array( $digirisk_transfert_options[ 'pictures' ] ) ) {
			$pictures_to_check = array();
			if ( !empty( $digirisk_transfert_options[ 'pictures' ][ 'ok' ] ) && is_array( $digirisk_transfert_options[ 'pictures' ][ 'ok' ] ) ) {
				$pictures_to_check = array_merge( $pictures_to_check, $digirisk_transfert_options[ 'pictures' ][ 'ok' ] );
			}
			if ( !empty( $digirisk_transfert_options[ 'pictures' ][ 'nok' ] ) && is_array( $digirisk_transfert_options[ 'pictures' ][ 'nok' ] ) ) {
				foreach ( $digirisk_transfert_options[ 'pictures' ][ 'nok' ] as $id => $file ) {
					$pictures_to_check[] = $id;
				}
			}
			$where .= "AND PICTURE.id NOT IN ( '" . implode( "', '", $pictures_to_check ) . "' )";
		}
		$pics_are_done = true;
		$query = $wpdb->prepare(
			"SELECT PICTURE.*, PICTURE_LINK.isMainPicture, PICTURE_LINK.idElement, PICTURE_LINK.tableElement
			FROM " . TABLE_PHOTO . " AS PICTURE
				INNER JOIN " . TABLE_PHOTO_LIAISON . " AS PICTURE_LINK ON (PICTURE_LINK.idPhoto = PICTURE.id)
			WHERE PICTURE_LINK.tableElement IN ( '{$main_element_type}', '{$sub_element_type}' )
				{$where}
			ORDER BY PICTURE.id ASC
			LIMIT " . DIGI_DTRANS_NB_ELMT_PER_PAGE, ""
		);
		$pictures = $wpdb->get_results($query);
		if ( !empty( $pictures ) ) {
			foreach ( $pictures as $picture ) {
				$query = $wpdb->prepare( "
					SELECT P.ID
					FROM {$wpdb->posts} AS P
						INNER JOIN {$wpdb->postmeta} AS PMID ON ( PMID.post_id = P.ID )
						INNER JOIN {$wpdb->postmeta} AS PMTYPE ON ( PMTYPE.post_id = P.ID )
					WHERE
						PMID.meta_key = %s
						AND PMID.meta_value = %s
						AND PMTYPE.meta_key = %s
						AND PMTYPE.meta_value = %d
				", array( '_wpdigi_elt_old_type', $picture->tableElement, '_wpdigi_elt_old_id', $picture->idElement ) );
				$new_element_id = $wpdb->get_var( $query );

				$this->transfer_document( $picture, $new_element_id, 'picture' );
			}
			$pics_are_done = false;
		}

		/**
		 *
		 *	Documents treatment
		 *
		 */
		$where = "";
		if ( !empty( $digirisk_transfert_options ) && !empty( $digirisk_transfert_options[ 'documents' ] ) && is_array( $digirisk_transfert_options[ 'documents' ] ) ) {
			$documents_to_check = array();
			if ( !empty( $digirisk_transfert_options[ 'documents' ][ 'ok' ] ) && is_array( $digirisk_transfert_options[ 'documents' ][ 'ok' ] ) ) {
				$documents_to_check = array_merge( $documents_to_check, $digirisk_transfert_options[ 'documents' ][ 'ok' ] );
			}
			if ( !empty( $digirisk_transfert_options[ 'documents' ][ 'nok' ] ) && is_array( $digirisk_transfert_options[ 'documents' ][ 'nok' ] ) ) {
				foreach ( $digirisk_transfert_options[ 'documents' ][ 'nok' ] as $id => $file ) {
					$documents_to_check[] = $id;
				}
			}
			$where .= "AND DOCUMENT.id NOT IN ( '" . implode( "', '", $documents_to_check ) . "' )";
		}
		$docs_are_done = true;
		$query = $wpdb->prepare(
			"SELECT *
			FROM " . TABLE_GED_DOCUMENTS . " AS DOCUMENT
			WHERE DOCUMENT.table_element IN ( '{$main_element_type}', '{$sub_element_type}' )
				{$where}
			ORDER BY DOCUMENT.id ASC
			LIMIT " . DIGI_DTRANS_NB_ELMT_PER_PAGE, ""
		);
		$documents = $wpdb->get_results($query);
		if ( !empty( $documents ) ) {
			foreach ( $documents as $document ) {
				$query = $wpdb->prepare( "
					SELECT P.ID
					FROM {$wpdb->posts} AS P
						INNER JOIN {$wpdb->postmeta} AS PMID ON ( PMID.post_id = P.ID )
						INNER JOIN {$wpdb->postmeta} AS PMTYPE ON ( PMTYPE.post_id = P.ID )
					WHERE
						PMID.meta_key = %s
						AND PMID.meta_value = %s
						AND PMTYPE.meta_key = %s
						AND PMTYPE.meta_value = %d
				", array( '_wpdigi_elt_old_type', $document->table_element, '_wpdigi_elt_old_id', $document->id_element ) );
				$new_element_id = $wpdb->get_var( $query );

				$this->transfer_document( $document, $new_element_id, 'document' );
			}
			$docs_are_done = false;
		}

		/**	In case all pictures and documents have been treated	*/
		if ( $pics_are_done && $docs_are_done ) {
			unset($response[ 'reload_transfert' ]);
			$response[ 'reload_transfert' ] = false;

			$current_step = DIGI_DTRANS_MEDIAN_MAX_STEP;
			ob_start();
			require( wpdigi_utils::get_template_part( DIGI_DTRANS_DIR, DIGI_DTRANS_TEMPLATES_MAIN_DIR, "backend", "transfert", "tasks-dashboardlink" ) );
			$response[ 'dashboard_link' ] = ob_get_contents();
			ob_end_clean();

			/**	Get already done element */
			$digirisk_transfert_options = get_option( 'wpdigi-dtransfert', array() );
			$digirisk_transfert_options[ 'state' ] = 'second_step_complete';
			update_option( 'wpdigi-dtransfert', $digirisk_transfert_options );
		}

		/**	Build element to transfert count	*/
		$element_count = $this->get_element_count( $main_element_type, $sub_element_type );
		$moved_docs = $element_count[ 'elements_to_transfer' ]->nb_pictures + $element_count[ 'elements_to_transfer' ]->nb_documents;

		$heavy_docs_already_done = 0;
		$heavy_docs_unable_to_do = 0;
		if ( !empty( $element_count[ 'allready_transfered' ][ 'pictures' ] ) ) {
			$heavy_docs_already_done += !empty( $element_count[ 'allready_transfered' ][ 'pictures' ][ 'ok' ] ) ? count( $element_count[ 'allready_transfered' ][ 'pictures' ][ 'ok' ] ) : 0;
			if ( !empty( $element_count[ 'allready_transfered' ][ 'pictures' ][ 'nok' ] ) ) {
				$heavy_docs_unable_to_do += count( $element_count[ 'allready_transfered' ][ 'pictures' ][ 'nok' ] );
			}
		}
		if ( !empty( $element_count[ 'allready_transfered' ][ 'documents' ] ) ) {
			$heavy_docs_already_done += count( $element_count[ 'allready_transfered' ][ 'documents' ][ 'ok' ] );
			if ( !empty( $element_count[ 'allready_transfered' ][ 'documents' ][ 'nok' ] ) ) {
				$heavy_docs_unable_to_do += count( $element_count[ 'allready_transfered' ][ 'documents' ][ 'nok' ] );
			}
		}

		$response[ 'moved_text' ] = $heavy_docs_already_done . ' (' . $heavy_docs_unable_to_do . ') / ' . $moved_docs;

		wp_die( json_encode( $response ) );
	}

	/**
	 * AJAX - Get the different count of element treated in order to inform user of transfer progression
	 */
	function ajax_get_transfered_element_count() {
		global $wpdb;

		$response = array(
			"auto_reload" => true,
		);

		$main_element_type = !empty( $_POST ) && !empty( $_POST[ 'element' ] ) ?  $_POST[ 'element' ] : null;

		if ( !empty( $main_element_type ) ) {

			$current_transfert_nb = $this->build_element_transfert_number( $main_element_type );
			$response = wp_parse_args( $current_transfert_nb, $response );

			wp_die( json_encode( $response ) );
		}
	}

	/**
	 * Get the number of treated and not treated element in order to display them to user
	 *
	 * @param string $main_element_type THe element tyoe currently being transfered into wordpress storage
	 *
	 * @return array The response
	 */
	function build_element_transfert_number( $main_element_type ) {
		$response = array();

		switch ( $main_element_type ) {
			case TABLE_TACHE:
				$sub_element_type = TABLE_ACTIVITE;
				break;

			case TABLE_GROUPEMENT:
				$sub_element_type = TABLE_UNITE_TRAVAIL;
				break;
		}

		$element_count = $this->get_element_count( $main_element_type, $sub_element_type );

		$nb_element_to_transfert = $element_count[ 'elements_to_transfer' ];
		$main_element_already_moved = !empty( $element_count[ 'allready_transfered' ][ $main_element_type ] ) ? count( $element_count[ 'allready_transfered' ][ $main_element_type ] ) : 0;
		$sub_element_already_moved = !empty( $element_count[ 'allready_transfered' ][ $sub_element_type ] ) ? count( $element_count[ 'allready_transfered' ][ $sub_element_type ] ) : 0;

		$response['transfert'][0][ 'type' ] = $main_element_type;
		$response['transfert'][0][ 'text' ] = ( $main_element_already_moved + $sub_element_already_moved ) . ' / ' . ( $nb_element_to_transfert->main_element_nb + $nb_element_to_transfert->sub_element_nb );

		if ( ( $main_element_already_moved + $sub_element_already_moved ) >= ( $nb_element_to_transfert->main_element_nb + $nb_element_to_transfert->sub_element_nb ) ) {
			$response[ 'auto_reload' ] = false;
		}


		$response[ $main_element_type ][ 'to_transfer' ] = $nb_element_to_transfert->main_element_nb;
		$response[ $main_element_type ][ 'transfered' ] = $main_element_already_moved;
		$response[ $sub_element_type ][ 'to_transfer' ] = $nb_element_to_transfert->sub_element_nb;
		$response[ $sub_element_type ][ 'transfered' ] = $sub_element_already_moved;
		$response[ $main_element_type ][ 'doc_to_transfer' ] = ( $element_count[ 'elements_to_transfer' ]->nb_documents + $element_count[ 'elements_to_transfer' ]->nb_pictures );
		$response[ $main_element_type ][ 'doc_transfered' ] = 0;
		$response[ $main_element_type ][ 'doc_not_transfered' ] = 0;


		return $response;
	}


	/**
	 * TRANSFER - Function allowing to get task form current correctiv action database to post database
	 *
	 * @param string $element_type_being_transfered The element's type to transfer
	 * @param object $element_being_transfered A wordpress database object containing the complete definition of an element from the old evarisk database structure
	 * @param array $custom_fields Optionnal Define the
	 *
	 * @return int|WP_Error Return new element identifier if request is succesfull|A WP_Error object in case an error occured while saving new element
	 */
	function transfer( $element_type_being_transfered, $element_being_transfered, $element_parent = null ) {
		global $wpdb;
		$element_id = 0;

		/**	Define the fields that have to be treated for wordpress element creation from evarisk internal element	*/
		$custom_fields = array();
		switch( $element_type_being_transfered ) {
			case TABLE_TACHE:
			case TABLE_ACTIVITE:
				$custom_fields[ 'post_title' ]		= 'nom';
				$custom_fields[ 'post_content' ]	= 'description';
				$custom_fields[ 'post_author' ] 	= 'idCreateur';
				$custom_fields[ 'post_date' ]		= 'firstInsert';
				break;

			case TABLE_GROUPEMENT:
			case TABLE_UNITE_TRAVAIL:
				$custom_fields[ 'post_title' ]		= 'nom';
				$custom_fields[ 'post_content' ]	= 'description';
				$custom_fields[ 'post_date' ]		= 'creation_date';
				break;
		}

		/**	Get already transfered elements	*/
		$digirisk_transfer_options = get_option( 'wpdigi-dtransfert', array() );

		/**	Define the default field for new element into wordpress	*/
		$element_wp_definition = array(
			'post_type' => $this->post_type[ $element_type_being_transfered ],
		);
		if ( !empty( $element_parent ) ) {
			$element_wp_definition[ 'post_parent' ] = $element_parent;
		}

		/**	In case the element is already transfered don't treat it	*/
		if ( empty( $digirisk_transfer_options ) || empty( $digirisk_transfer_options[ $element_type_being_transfered ] ) || !in_array( $element_being_transfered->id, $digirisk_transfer_options[ $element_type_being_transfered ]) ) {
			/**	Define the post status from the current one	*/
			$element_wp_definition[ 'post_status' ] = ( $element_being_transfered->Status == 'Valid' ? 'publish' : ( $element_being_transfered->Status == 'Moderated' ? 'draft' : 'trash' ) );

			if ( !empty( $custom_fields ) ) {
				foreach ( $custom_fields as $post_field => $custom_field ) {

					$specific = false;
					if ( 'idCreateur' == $custom_field ) {
						$idCreateur = ( 0 == $element_being_transfered->$custom_field ) ? 1 : $element_being_transfered->$custom_field;
						if ( empty( $_POST[ 'wpdigi-dtrans-userid-behaviour' ] ) && !empty( $_POST[ 'wp_new_user' ] ) && !empty( $_POST[ 'wp_new_user' ][ $idCreateur ] ) ) {
							$element_wp_definition[ $post_field ] = $_POST[ 'wp_new_user' ][ $idCreateur ];
							$specific = true;
						}
					}
					if ( !$specific ) {
						$element_wp_definition[ $post_field ] = $element_being_transfered->$custom_field;
					}

					unset( $element_being_transfered->$custom_field );
				}
			}

			/**	Create element into wordpress database */
			$element_id = wp_insert_post( $element_wp_definition );

			/**	In case insertion has been successfull, read children in order to do same treatment and save extras informations into meta for the moment	*/
			if ( is_int( $element_id ) ) {
				/**	Log creation	*/
				wpeologs_ctr::log_datas_in_files( 'digirisk-datas-transfert-' . $element_type_being_transfered, array( 'object_id' => $element_being_transfered->id, 'message' => sprintf( __( 'Transfered from evarisk on post having id. %d', 'wp-digi-dtrans-i18n' ), $element_id), ), 0 );

				/**	Store an option to avoid multiple transfer	*/
				$digirisk_transfer_options[ $element_type_being_transfered ][] = $element_being_transfered->id;
				update_option( 'wpdigi-dtransfert', $digirisk_transfer_options );

				/** Start transfering users	*/
				$this->transfer_users( $element_being_transfered->id, $element_type_being_transfered, $element_id, '' );

				/**	Start transfering user notification if exists	*/
				$this->transfer_notification( $element_being_transfered->id, $element_type_being_transfered, $element_id, '' );

				/**	Start transfering survey that have been done with wp-easy-survey	*/
				$this->transfer_surveys( $element_being_transfered->id, $element_type_being_transfered, $element_id, '' );

				/**	Check curren type of element to launch specific transfer	*/
				switch( $element_type_being_transfered ) {
					case TABLE_TACHE:
					case TABLE_ACTIVITE:
						/**	Transfert follow up of tasks	*/
						$this->transfer_follow_up( $element_being_transfered->id, $element_type_being_transfered, $element_id );

						/**	Build the array that will be stored into database	*/
						$task_planning = array(
							'estimate_start_date'	=> $element_being_transfered->dateDebut,
							'estimate_end_date' 	=> $element_being_transfered->dateFin,
							'planned_time' 			=> $element_being_transfered->planned_time,
							'real_start_date' 		=> $element_being_transfered->real_start_date,
							'real_end_date' 		=> $element_being_transfered->real_end_date,
							'elapsed_time' 			=> $element_being_transfered->elapsed_time,
						);
						switch ( $element_type_being_transfered ) {
							case TABLE_TACHE:
								$task_planning[ 'estimate_cost' ] = $element_being_transfered->estimate_cost;
								$task_planning[ 'real_cost' ] = $element_being_transfered->real_cost;

								/**	Transfer link between tasks and other elements	*/
								$this->transfer_link_between_tasks_and_element( $element_being_transfered->id, $element_type_being_transfered, $element_id );

								break;
							case TABLE_ACTIVITE:
								$task_planning[ 'estimate_cost' ] = $element_being_transfered->cout;
								$task_planning[ 'real_cost' ] = $element_being_transfered->cout_reel;
								break;
						}
						update_post_meta( $element_id, '_wpeomtm_task_planning', $task_planning );
						/**	When done remove all entries already saved from old element in order to save it later	*/
						foreach ( $task_planning as $field => $value ) {
							unset( $element_being_transfered->$field );
						}

						/**	Set task manager	*/
						update_post_meta( $element_id, '_wpeo_manager_id', $element_being_transfered->idResponsable );

						/**	Set task progression information	*/
						$wpeoTMTaskController = new wpeoTMTaskController();
						$advancement_status = $element_being_transfered->ProgressionStatus;
						wp_set_object_terms( $element_id, $advancement_status, $wpeoTMTaskController->default_task_frontend_status );
						$task_progression = array(
							'advancement'			=> $element_being_transfered->avancement,
							'ended_date'			=> $element_being_transfered->dateSolde,
							'ended_user'			=> $element_being_transfered->idSoldeur,
							'ended_chief_user'		=> $element_being_transfered->idSoldeurChef,
						);
						update_post_meta( $element_id, '_wpeo_task_status', $task_progression );
						/**	When done remove all entries already saved from old element in order to save it later	*/
						foreach ( $task_progression as $field => $value ) {
							unset( $element_being_transfered->$field );
						}

					break;

					case TABLE_GROUPEMENT:

					break;
				}

				/**	Store the other data into meta	*/
				update_post_meta( $element_id, '_wpdigi_elt_old_type', $element_type_being_transfered );
				update_post_meta( $element_id, '_wpdigi_elt_old_id', $element_being_transfered->id );
				update_post_meta( $element_id, '_wpdigi_elt_old', $element_type_being_transfered . '#value_sep#' . $element_being_transfered->id );
				$old_element_identifier = $element_being_transfered->id;
				unset( $element_being_transfered->id );
				update_post_meta( $element_id, '_wpdigi_elt_def', $element_being_transfered );

				/**	Lauch transfer for current element direct children of same type	*/
				if ( property_exists( $element_being_transfered, 'limiteGauche') ) {
					$sub_elements = Arborescence::getFils( $element_type_being_transfered, $element_being_transfered, "limiteGauche ASC", null, null, "1" );
					foreach ( $sub_elements as $element ) {
						$new_children_id = $this->transfer( $element_type_being_transfered, $element, $element_id );
						if ( !empty( $new_children_id ) && is_int( $new_children_id ) ) {}
					}
				}
			}
			else {
				wpeologs_ctr::log_datas_in_files( 'digirisk-datas-transfert-' . $element_type_being_transfered, array( 'object_id' => $element_being_transfered->id, 'message' => __( 'Error transferring from evarisk to post.', 'wp-digi-dtrans-i18n' ), ), 2 );
			}
		}

		return $element_id;
	}

	/**
	 * TRANSFER - Launch the transfer for element not transfered with the normal way
	 *
	 * @param string $element_type
	 */
	function transfer_orphelan( $element_type ) {
		global $wpdb;

		/**	Do a final check for element possibly not transfer	*/
		$query = $wpdb->prepare( "
			SELECT T.*
			FROM {$element_type} AS T
			WHERE T.id NOT IN (
				SELECT PM.meta_value
				FROM {$wpdb->postmeta} AS PM
				INNER JOIN {$wpdb->postmeta} AS PM2 ON (PM2.post_id = PM.post_id)
				WHERE PM.meta_key = %s
				AND PM2.meta_key = %s
				AND PM2.meta_value = '{$element_type}'
			)
			AND T.id != %d", '_wpdigi_elt_old_id', '_wpdigi_elt_old_type', 1 );
		$not_transfered_element = $wpdb->get_results( $query );
		if ( !empty( $not_transfered_element ) ) {
			foreach ( $not_transfered_element as $element ) {
				$this->transfer( $element_type, $element );
			}
		}
	}

	/**
	 * TRANSFER -> Treat transfert for one element
	 *
	 * @param WP_OBject $document The current document to transfert into wordpress storage
	 * @param integer $new_element_id The identifier of element to associate document to
	 * @param string $document_origin The document type to transfert Defaut: picture
	 */
	function transfer_document( $document, $new_element_id, $document_origin = 'picture' ) {
		if ( !function_exists( 'wp_generate_attachment_metadata' ) )
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

		/**	Get wordpress uploads directory	*/
		$wp_upload_dir = wp_upload_dir();
		$associate_document_list = array();

		/**	Get associated picture list	*/
		switch ( $document_origin ) {
			case 'picture':
				$field_name = 'photo';
				break;
			case 'document':
				break;
		}

		$digirisk_transfert_options = get_option( 'wpdigi-dtransfert', array() );
		/**	Get the file content - force error ignore	*/
		$file = EVA_GENERATED_DOC_DIR . ( 'document' == $document_origin ? ( 'printed_fiche_action' == $document->categorie ? 'results/' : '' ) . $document->chemin . $document->nom : $document->$field_name );
		$the_file_content = @file_get_contents( $file );
		/**	Check if file is a vlid one	*/
		if ( $the_file_content !== FALSE ) {
			/**	Start by coping picture into wordpress uploads directory	*/
			$upload_result = wp_upload_bits( basename( $file ), null, file_get_contents( $file ) );

			/**	Get informations about the picture	*/
			$filetype = wp_check_filetype( basename( $upload_result[ 'file' ] ), null );
			/**	Set the default values for the current attachement	*/
			$attachment = array(
				'guid'           => $wp_upload_dir['url'] . '/' . basename( $upload_result[ 'file' ] ),
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $upload_result[ 'file' ] ) ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			/**	Get associated picture list	*/
			switch ( $document_origin ) {
				case 'document':
					$idCreateur = ( 0 == $document->idCreateur ) ? 1 : $document->idCreateur;
					if ( empty( $_POST[ 'wpdigi-dtrans-userid-behaviour' ] ) && !empty( $_POST[ 'wp_new_user' ] ) && !empty( $_POST[ 'wp_new_user' ][ $idCreateur ] ) ) {
						$idCreateur = $_POST[ 'wp_new_user' ][ $idCreateur ];
					}
					$attachment[ 'post_author' ] = $idCreateur;
					$attachment[ 'post_date' ] = $document->dateCreation;
				break;
			}

			/**	Save new picture into database	*/
			$attach_id = wp_insert_attachment( $attachment, $upload_result[ 'file' ], $new_element_id );
			/**	Create the different size for the given picture and get metadatas for this picture	*/
			$attach_data = wp_generate_attachment_metadata( $attach_id, $upload_result[ 'file' ] );
			/**	Finaly save pictures metadata	*/
			wp_update_attachment_metadata( $attach_id,  $attach_data );

			/**	Set the post thumbnail in case it is the case	*/
			if ( ( 'picture' == $document_origin ) && ( 'yes' == $document->isMainPicture ) ) {
				set_post_thumbnail( $new_element_id, $attach_id );
			}

			if ( 'valid' == $document->status ) {
				$associate_document_list[] = $attach_id;
			}

			/**	Get associated picture list	*/
			switch ( $document_origin ) {
				case 'document':
					update_post_meta( $attach_id, '_wpeo_digidoc_old', $document );
				break;
			}

			wpeologs_ctr::log_datas_in_files( 'digirisk-datas-transfert-' . $document_origin, array( 'object_id' => $document->id, 'message' => sprintf( __( '%s transfered from evarisk on post having to element #%d', 'wp-digi-dtrans-i18n' ), $document_origin, $new_element_id), ), 0 );
			$digirisk_transfert_options[ $document_origin . 's' ][ 'ok' ][] = $document->id;
		}
		else {
			$digirisk_transfert_options[ $document_origin . 's' ][ 'nok' ][ $document->id ][ 'file' ] = $file;
			$tocheck = $document->table_element;
			if ( 'picture' == $document_origin ) {
				$tocheck = $document->tableElement;
			}
			switch ( $tocheck ) {
				case TABLE_TACHE:
					$old_evarisk_element = ELEMENT_IDENTIFIER_T . ( 'picture' == $document_origin  ? $document->idElement : $document->id_element );
					break;
				case TABLE_ACTIVITE:
					$old_evarisk_element = ELEMENT_IDENTIFIER_ST . ( 'picture' == $document_origin  ? $document->idElement : $document->id_element );
					break;
				case TABLE_GROUPEMENT:
					$old_evarisk_element = ELEMENT_IDENTIFIER_GP . ( 'picture' == $document_origin  ? $document->idElement : $document->id_element );
					break;
				case TABLE_UNITE_TRAVAIL:
					$old_evarisk_element = ELEMENT_IDENTIFIER_UT . ( 'picture' == $document_origin  ? $document->idElement : $document->id_element );
					break;
			}
			wpeologs_ctr::log_datas_in_files( 'digirisk-datas-transfert-' . $document_origin, array( 'object_id' => $document->id, 'message' => sprintf( __( '%s could not being transfered to wordpress element. Filename: %s. Wordpress element: %d. Evarisk old element: %s', 'wp-digi-dtrans-i18n' ), $document_origin, $file, $new_element_id, $old_evarisk_element ), ), 2 );
		}
		/**	Set the new list of element treated	*/
		update_option( 'wpdigi-dtransfert', $digirisk_transfert_options );

		/**	Set the picture gallery for current element	*/
		if ( !empty( $associate_document_list ) ) {
			update_post_meta( $new_element_id, '_wpeofiles_associated', $associate_document_list );
		}
	}

	/**
	 * TRANSFER -> ASSOCIATED ELEMENT - Get associated users and transfer
	 *
	 * @param integer $old_element_id The element identifier into digirisk V5.X
	 * @param string $old_element_type The element type into digirisk V5.X
	 * @param integer $new_element_id The new element created into wordpress corresponding to the previous element into digirisk V5.0
	 */
	function transfer_users( $old_element_id, $old_element_type, $new_element_id, $user_role = '' ) {
		$currently_affected_user = get_post_meta( $new_element_id, '_wpeo_itrack_associated_users', true );
		$currently_affected_users_old = evaUserLinkElement::getAffectedUser( $old_element_type, $old_element_id, "'valid', 'moderated', 'deleted'" );
		if ( !empty( $currently_affected_users_old ) ) {
			$i = 0;
			foreach ( $currently_affected_users_old as $currently_affected_users ) {
				$idUser = ( 0 == $currently_affected_users->id_attributeur ) ? 1 : $currently_affected_users->id_user;
				if ( empty( $_POST[ 'wpdigi-dtrans-userid-behaviour' ] ) && !empty( $_POST[ 'wp_new_user' ] ) && !empty( $_POST[ 'wp_new_user' ][ $idUser ] ) ) {
					$idUser = $_POST[ 'wp_new_user' ][ $idUser ];
				}

				$currently_affected_user[ $idUser ][ $i ][ 'from' ][ 'date' ] = $currently_affected_users->date_affectation_reelle;

				$idAttributeur = ( 0 == $currently_affected_users->id_attributeur ) ? 1 : $currently_affected_users->id_attributeur;
				if ( empty( $_POST[ 'wpdigi-dtrans-userid-behaviour' ] ) && !empty( $_POST[ 'wp_new_user' ] ) && !empty( $_POST[ 'wp_new_user' ][ $idAttributeur ] ) ) {
					$idAttributeur = $_POST[ 'wp_new_user' ][ $idAttributeur ];
				}
				$currently_affected_user[ $idUser ][ $i ][ 'from' ][ 'by' ] = $idAttributeur;

				$currently_affected_user[ $idUser ][ $i ][ 'from' ][ 'on' ] = $currently_affected_users->date_affectation;
				$currently_affected_user[ $idUser ][ $i ][ 'to' ][ 'date' ] = $currently_affected_users->date_desaffectation_reelle;

				$idDesAttributeur = ( 0 == $currently_affected_users->id_desAttributeur ) ? 1 : $currently_affected_users->id_desAttributeur;
				if ( empty( $_POST[ 'wpdigi-dtrans-userid-behaviour' ] ) && !empty( $_POST[ 'wp_new_user' ] ) && !empty( $_POST[ 'wp_new_user' ][ $idDesAttributeur ] ) ) {
					$idDesAttributeur = $_POST[ 'wp_new_user' ][ $idDesAttributeur ];
				}
				$currently_affected_user[ $idUser ][ $i ][ 'to' ][ 'by' ] = $idDesAttributeur;

				$currently_affected_user[ $idUser ][ $i ][ 'to' ][ 'on' ] = $currently_affected_users->date_desAffectation;
				if ( !empty( $user_role ) )
					$currently_affected_user[ $idUser ][ $i ][ 'role' ] = $user_role;
				$i++;
			}
		}

		if ( !empty( $currently_affected_user ) ) {
			update_post_meta( $new_element_id, '_wpeo_itrack_associated_users', $currently_affected_user );
		}
	}

	/**
	 * TRANSFER -> ASSOCIATED ELEMENT - Get associated survey and transfer
	 *
	 * @param integer $old_element_id The element identifier into digirisk V5.X
	 * @param string $old_element_type The element type into digirisk V5.X
	 * @param integer $new_element_id The new element created into wordpress corresponding to the previous element into digirisk V5.0
	 */
	function transfer_surveys( $old_element_id, $old_element_type, $new_element_id, $user_role = '' ) {
		global $wpdb;
		$survey_results = array();

		/**	Get existing surveys	*/
		$query = $wpdb->prepare( "SELECT * FROM " .  TABLE_FORMULAIRE_LIAISON . " WHERE tableElement = %s AND idELement = %d ", $old_element_type, $old_element_id );
		$surveys = $wpdb->get_results( $query );

		/**	Check if there are surveys to transfer from evarisk storage way to wordpress storage way	*/
		if ( !empty( $surveys ) ) {
			foreach ( $surveys as $survey ) {
				$survey_results[ $survey->idFormulaire ][ $survey->state ][] = array(
					'date_started' => $survey->date_started,
					'date_closed' => $survey->date_closed,
					'state' => $survey->state,
					'user' => $survey->user,
					'user_closed' => $survey->user_closed,
					'survey_id' => $survey->survey_id,
				);
			}
		}

		/**	Save survey datas into the associated element	*/
		if ( !empty( $survey_results ) ) {
			foreach ( $survey_results as $original_survey_id => $final_survey ) {
				update_post_meta( $new_element_id, '_wpes_audit_' . $original_survey_id, $final_survey );
				wpeologs_ctr::log_datas_in_files( 'digirisk-datas-transfert-survey', array( 'object_id' => $original_survey_id, 'message' => __( 'Survey association have been transfered to normal way', 'wp-digi-dtrans-i18n' ), ), 0 );
			}
		}
	}



	/**
	 * SPECIFIC TRANSFER -> TASKS' NOTES - Transfer follow up into wordpress comment database table
	 *
	 * @param integer $old_element_id The element identifier into digirisk V5.X
	 * @param string $old_element_type The element type into digirisk V5.X
	 * @param integer $new_element_id The new element created into wordpress corresponding to the previous element into digirisk V5.0
	 */
	function transfer_follow_up( $old_element_id, $old_element_type, $new_element_id ) {
		/**	Get existing folow up for given element	*/
		$follow_up_types = array( 'note', 'follow_up' );
		foreach ( $follow_up_types as $follow_up_type ) {
			$follow_up_list = suivi_activite::getSuiviActivite( $old_element_type, $old_element_id, $follow_up_type );
			if ( !empty( $follow_up_list ) ) {
				foreach ( $follow_up_list as $follow_up ) {

					$idUser = ( 0 == $follow_up->id_user ) ? 1 : $follow_up->id_user;
					if ( empty( $_POST[ 'wpdigi-dtrans-userid-behaviour' ] ) && !empty( $_POST[ 'wp_new_user' ] ) && !empty( $_POST[ 'wp_new_user' ][ $idUser ] ) ) {
						$idUser = $_POST[ 'wp_new_user' ][ $idUser ];
					}
					$user_infos = get_userdata( $idUser );

					$data = array(
						'comment_post_ID' => $new_element_id,
						'comment_author' => $user_infos->display_name,
						'comment_author_email' => $user_infos->user_email,
						'comment_author_url' => $user_infos->user_url,
						'comment_content' => $follow_up->commentaire,
						'comment_type' => $follow_up->follow_up_type,
						'comment_parent' => 0,
						'user_id' => $idUser,
						'comment_author_IP' => '',
						'comment_agent' => '',
						'comment_date' => $follow_up->date_ajout,
						'comment_approved' => -1,
					);
					$comment_id = wp_insert_comment( $data );

					/**
					 * Create metadatas for new created comment
					 */
					/**	Store old follow up identifier	*/
					update_comment_meta( $comment_id, "_wpeo_comment_old_id", $follow_up->id );
					/**	Get current export status for the comment	*/
					update_comment_meta( $comment_id, "_wpeo_comment_exportable", $follow_up->export );
					$idUserPerformer = ( 0 == $follow_up->id_user_performer ) ? 1 : $follow_up->id_user_performer;
					if ( empty( $_POST[ 'wpdigi-dtrans-userid-behaviour' ] ) && !empty( $_POST[ 'wp_new_user' ] ) && !empty( $_POST[ 'wp_new_user' ][ $idUserPerformer ] ) ) {
						$idUserPerformer = $_POST[ 'wp_new_user' ][ $idUserPerformer ];
					}
					/**	Get current informations about tasks performer and task elapsed time	*/
					update_comment_meta( $comment_id, "_wpeo_comment_perform_infos", array(
						"performer_id" => $idUserPerformer,
						"perform_date" => $follow_up->date,
						"elapsed_time" => $follow_up->elapsed_time,
						"cost" => $follow_up->cost,
					) );
				}
			}
		}
	}

	/**
	 * SPECIFIC TRANSFER -> TASKS' USER NOTIFICATION - Transfer notification associated to an element
	 *
	 * @param integer $old_element_id The element identifier into digirisk V5.X
	 * @param string $old_element_type The element type into digirisk V5.X
	 * @param integer $new_element_id The new element created into wordpress corresponding to the previous element into digirisk V5.0
	 */
	function transfer_notification( $old_element_id, $old_element_type, $new_element_id ) {
		/**	Get the current user notification list for current element being transfered	*/
		$current_user_notification_list = digirisk_user_notification::get_link_user_notification_list( $old_element_type, $old_element_id );

		/**	If there are notification setted transfer them to new element	*/
		if ( !empty( $current_user_notification_list ) ) {
			$notifications = array();
			$n = 0;
			foreach ( $current_user_notification_list as $notification ) {
				$notifications[ $n ][ 'status' ] = $notification->status;
				$notifications[ $n ][ 'date_affectation' ] = $notification->date_affectation;

				$idAttributeur = ( 0 == $notification->id_attributeur ) ? 1 : $notification->id_attributeur;
				if ( empty( $_POST[ 'wpdigi-dtrans-userid-behaviour' ] ) && !empty( $_POST[ 'wp_new_user' ] ) && !empty( $_POST[ 'wp_new_user' ][ $idAttributeur ] ) ) {
					$idAttributeur = $_POST[ 'wp_new_user' ][ $idAttributeur ];
				}
				$notifications[ $n ][ 'id_attributeur' ] = $idAttributeur;
				$notifications[ $n ][ 'date_desAffectation' ] = $notification->date_desAffectation;

				$idDesAttributeur = ( 0 == $notification->id_desAttributeur ) ? 1 : $notification->id_desAttributeur;
				if ( empty( $_POST[ 'wpdigi-dtrans-userid-behaviour' ] ) && !empty( $_POST[ 'wp_new_user' ] ) && !empty( $_POST[ 'wp_new_user' ][ $idDesAttributeur ] ) ) {
					$idDesAttributeur = $_POST[ 'wp_new_user' ][ $idDesAttributeur ];
				}
				$notifications[ $n ][ 'id_desAttributeur' ] = $idDesAttributeur;
				$notifications[ $n ][ 'id_user' ] = $notification->id_user;
				$notifications[ $n ][ 'id_notification' ] = $notification->id_notification;
			}
			update_post_meta( $new_element_id, '_wpeo_element_notification', $notifications );
		}
	}

	/**
	 * SPECIFIC TRANSFER -> TASKS' ELEMENT LINK - Transfer links between task and other elements
	 *
	 * @param integer $old_element_id The element identifier into digirisk V5.X
	 * @param string $old_element_type The element type into digirisk V5.X
	 * @param integer $new_element_id The new element created into wordpress corresponding to the previous element into digirisk V5.0
	 */
	function transfer_link_between_tasks_and_element( $old_element_id, $old_element_type, $new_element_id ) {
		global $wpdb;

		/**	Get lined element with current task	*/
		$query = $wpdb->prepare( "SELECT * FROM " . TABLE_LIAISON_TACHE_ELEMENT . " WHERE id_tache = %d ORDER BY id_tache, date", $old_element_id );
		$existing_links = $wpdb->get_results( $query );

		if ( !empty( $existing_links ) ) {
			$links = array();
			foreach ( $existing_links as $link ) {
				$links[] = array(
					$link->table_element,
					$link->id_element,
					$link->date,
					$link->wasLinked,
				);
			}

			if ( !empty( $links ) ) {
				update_post_meta( $new_element_id, '_wpeo_element_links', $links );
			}
			else {
				wpeologs_ctr::log_datas_in_files( 'digirisk-datas-transfert-' . $old_element_type . '-association-error', array( 'object_id' => $old_element_id, 'message' => sprintf( __( 'Element linked to this %s have not been transfered to %d', 'wp-digi-dtrans-i18n' ), $old_element_type, $new_element_id ), ), 2 );
			}
		}

	}


}
