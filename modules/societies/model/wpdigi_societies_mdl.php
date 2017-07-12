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
class wpdigi_societies_mdl {

	function __construct() {}

	/**
	 * Récupèration de l'ensemble des éléments / Get all elements
	 *
	 * @return Ambigous <multitype:, multitype:number >
	 */
	public function get_posts( $post_parent = 0 ) {
		$societies = array();
		$root_societies = get_posts( array(
			'post_type' => WPDIGI_STES_POSTTYPE_MAIN,
			'posts_per_page' => -1,
			'post_parent' => $post_parent,
		) );
		if ( !empty( $root_societies ) ) {
			foreach ( $root_societies as $society ) {
				$societies = array_merge( $societies, $this->build_model( $society ) );
			}
		}

		$return = array(
			"status" => "success",
			"version" 	=> "0.1",
			"code" 		=> 0,
			"message" 	=> "id get api success",
			"checksum" 	=> md5( json_encode( $societies ) ),
			"data" 		=> $societies,
		);

		return $return;
	}

	/**
	 * Récupération d'un élément spécifique à partir d'un identifiant donné / Get information about a specific identifier
	 *
	 * @param integer $id An element identifier to get information for
	 *
	 * @return Ambigous <WP_Post, multitype:, NULL, unknown>
	 */
	public function get_post( $id ) {
		$society = get_post( $id );
		$return = array(
			"status" 	=> "success",
			"version" 	=> "0.1",
			"code" 		=> 0,
			"message" 	=> "id get api success",
			"checksum" 	=> md5( json_encode( $society ) ),
			"data" 		=> $this->build_model( $society ),
		);

		return ( $return );
	}

	public function new_post( $data ) {

	}
	public function edit_post() {


	}

	public function delete_post() {

	}


	public function build_model( $society ) {
		$the_society = array();

		$the_society[ $society->ID ][ '_type' ] 				= $society->post_type;
		$the_society[ $society->ID ][ 'id' ] 					= $society->ID;
		$the_society[ $society->ID ][ 'name' ] 					= $society->post_title;
		$the_society[ $society->ID ][ 'description' ] 			= $society->post_content;
		$the_society[ $society->ID ][ 'entry_creation_date' ] 	= $society->post_date;
		$the_society[ $society->ID ][ 'entry_lastupdate_date' ] = $society->post_modified;
		$the_society[ $society->ID ][ 'status' ] 				= $society->post_status;

		$the_society[ $society->ID ][ 'author' ] 				= $society->post_author;

		$the_society[ $society->ID ][ 'link' ] 					= get_permalink( $society->ID );
		$the_society[ $society->ID ][ 'parent' ] 				= $society->post_parent;

		$the_society[ $society->ID ][ 'documents' ] 			= array();
		$the_society[ $society->ID ][ 'thumbnail' ] 			= wp_get_attachment_image_src( get_post_thumbnail_id( $society->ID ) );

		$the_society[ $society->ID ][ 'contact' ] 				= array(
			'phone'		=> array(),
			'adress'	=> array(),
		);

		$the_society[ $society->ID ][ 'metas' ] 				= array(
			'responsible'				=> array(),
			'creation_date'				=> '',
			'workforce'					=> '',
			'siren'						=> '',
			'siret'						=> '',
			'social_activity_number'	=> '',
		);

		$the_society[ $society->ID ][ 'term' ] 					= array();

// 		$the_society[ $society->ID ][ 'children' ] 				= $this->get_posts( $society->ID );
		$the_society[ $society->ID ][ 'children' ] 				= array_merge(
			$this->get_posts( $society->ID ),
			$this->get_sub_posts( $society->ID )
		);

		return $the_society;
	}


	/**
	 * Récupèration de l'ensemble des éléments / Get all elements
	 *
	 * @return Ambigous <multitype:, multitype:number >
	 */
	public function get_sub_posts( $post_parent = 0 ) {
		$societies = array();
		$root_societies = get_posts( array(
			'post_type' => WPDIGI_STES_POSTTYPE_SUB,
			'posts_per_page' => -1,
			'post_parent' => $post_parent,
		) );
		if ( !empty( $root_societies ) ) {
			foreach ( $root_societies as $society ) {
				$societies = $this->build_model( $society );
			}
		}

		return $societies;
	}

}
