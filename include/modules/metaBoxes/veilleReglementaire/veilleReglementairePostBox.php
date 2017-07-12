<?php

	global $wpdb;
	$query = $wpdb->prepare( "SELECT PM.post_id FROM {$wpdb->postmeta} AS PM INNER JOIN {$wpdb->posts} AS P ON (P.ID = PM.post_id) WHERE P.post_status = 'publish' AND PM.meta_key = %s AND PM.meta_value LIKE ('%%%s%%') AND P.post_type = %s", '_wpes_survey_association',  $_REQUEST['table'], 'wpes_survey');
	$associated_surveys_list = $wpdb->get_results( $query );

	if ( !empty($associated_surveys_list) ) {
		switch ( $_REQUEST['table'] ) {
			case TABLE_GROUPEMENT:
				$element_associated = PAGE_HOOK_EVARISK_GROUPEMENTS;
				break;
			case TABLE_UNITE_TRAVAIL:
				$element_associated = PAGE_HOOK_EVARISK_UNITES_DE_TRAVAIL;
				break;
			case TABLE_TACHE:
				$element_associated = PAGE_HOOK_EVARISK_TACHE;
				break;
			case TABLE_ACTIVITE:
				$element_associated = PAGE_HOOK_EVARISK_ACTIVITE;
				break;
		}

		foreach ( $associated_surveys_list as $survey ) {
			/**	Define metabox allowing to associate survey to existing custom post type */
			add_meta_box( $_REQUEST['table'] . '-survey-association-' . $survey->post_id, sprintf( _x('%s', 'Title for metabox displaying survey in associated post type', 'evarisk'), get_the_title( $survey->post_id ) ), 'display_survey_for_digirisk', $element_associated, 'rightSide', 'default', array( 'parent_element_id' => $survey->post_id, 'parent_element_type' => 'survey' ) );
		}
	}

	function display_survey_for_digirisk( $current_element, $args ) {
		global $wpes_survey,
				$wpdb;

		$query = $wpdb->prepare( "SELECT * FROM " . TABLE_FORMULAIRE_LIAISON . " WHERE tableElement = %s AND idElement = %d AND idFormulaire = %d AND state = %s ORDER BY date_started LIMIT 1" , array( $current_element[ 'tableElement' ], $current_element[ 'idElement' ], $args['args']['parent_element_id'], 'started') );
		$current_element_evaluation[ 'in_progress' ] = $wpdb->get_row( $query, ARRAY_A );

		$query = $wpdb->prepare( "SELECT * FROM " . TABLE_FORMULAIRE_LIAISON . " WHERE tableElement = %s AND idElement = %d AND idFormulaire = %d AND state = %s ORDER BY date_started DESC" , array( $current_element[ 'tableElement' ], $current_element[ 'idElement' ], $args['args']['parent_element_id'], 'closed') );
		$current_element_evaluation[ 'closed' ] = $wpdb->get_results( $query, ARRAY_A );

		$current_element_evaluation[ 'ajax_action' ] = "digi-ajax-final-survey-evaluation-result-view&amp;tableElement=" . $current_element[ 'tableElement' ] . "&amp;idElement=" . $current_element[ 'idElement' ];
		$current_element_evaluation[ 'element_type' ] = $current_element[ 'tableElement' ];

		$final_survey = $wpes_survey->final_survey_display( $current_element[ 'idElement' ], $args['args']['parent_element_id'], $current_element_evaluation );

		echo '<div id="digi-survey-content" >' . $final_survey['content'] . '</div>
			<input type="hidden" value="' . $wpes_survey->get_total_number_of_issue() . '" name="wpes-final-survey-total-answer-to-give" id="wpes-final-survey-total-answer-to-give" />
			<input type="hidden" value="' . $current_element[ 'idElement' ] . '" name="post_ID" id="post_ID" />
			<input type="hidden" value="' . $current_element[ 'tableElement' ] . '" name="post_type" id="post_type" />
			<script type="text/javascript" >
				jQuery( document ).ready( function(){
					jQuery( document ).off("click", ".wpes-final-survey-evaluation-close-button");
					jQuery( document ).on("click", ".wpes-final-survey-evaluation-close-button", function( e ) {
						e.preventDefault();

						var current_survey = jQuery(this).closest(".wpes-survey-current-state").attr("id").replace("wpes-survey-current-state-", "");
						var current_element = jQuery( "#post_ID" ).val();

						if ( ( (jQuery("#wpes-final-survey-" + current_survey + "-current-progression").val() == 100) && confirm( wpes_convert_html_accent( WPES_JS_VAR_FINAL_SURVEY_EVALUATION_CLOSE_ALL_ANSWER ) ) )
							|| confirm( wpes_convert_html_accent( WPES_JS_VAR_FINAL_SURVEY_EVALUATION_CLOSE_NOT_ALL_ANSWER ) ) ) {
							jQuery(this).next( "img" ).show();

							var data = {
								action: "digi-close-evaluation",
								"wpes-ajax-close-evaluation": WPES_JS_VAR_FINAL_SURVEY_EVALUATION_CLOSE_AJAX_NONCE,
								survey_id: current_survey,
								post_ID: current_element,
							};

							/**	Launch ajax request */
							jQuery.post( ajaxurl, data, function(response) {
								if ( response[ "status" ] ) {
									jQuery("#" + jQuery( "#post_type" ).val() + "-survey-association-" + response[ "survey_id" ] + " .inside #digi-survey-content").html( response[ "output" ] );
								}
								jQuery(this).next( "img" ).hide();
							}, "json");
						}
					});

					jQuery( document ).off( "click", ".wpes-final-survey-new-evaluation-start" );
					jQuery( document ).on( "click", ".wpes-final-survey-new-evaluation-start", function( e ){
						e.preventDefault();

						var current_survey_id = jQuery(this).attr("id").replace("wpes-final-survey-new-evaluation-start-", "");
						jQuery(this).next( "img" ).show();

						/**	Launch ajax request */
						var data = {
							action: "digi-start-new-evaluation-for-wpes",
							"wpes-ajax-new-evaluation-start": WPES_JS_VAR_FINAL_SURVEY_NEW_EVALUATION_START_AJAX_NONCE,
							survey_id: current_survey_id,
							post_ID: jQuery("#post_ID").val(),
							post_type: jQuery("#post_type").val(),
						};
						jQuery.post( ajaxurl, data, function(response) {
							if ( response[ "status" ] ) {
								jQuery("#' . $current_element[ 'tableElement' ] . '-survey-association-" + response[ "survey_id" ] + " .inside #digi-survey-content").html( response[ "output" ] );
							}
						}, "json");

					});
				});
			</script>
		';
	}

?>