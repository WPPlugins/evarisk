jQuery( document ).ready( function(){
	jQuery( ".wpdigi-datas-transfert-form" ).ajaxForm({
		dataType: "json",
		beforeSubmit: function( formData, jqForm, options ) {
			var current_action = jqForm.children( "input[name=action]" ).val();

			jQuery( ".wpdigi-dtransfert-message-container" ).html( "" ).hide();
			jqForm.children( "img" ).show();
			if ( ( "wpdigi-datas-transfert" == current_action ) && ( "no" == jqForm.children( "input[name=autoreload]" ).val() ) ) {
				get_treated_element( jQuery( "input[name=element_type_to_transfert]" ).val() );
			}
		},
		success: function( responseText, statusText, xhr, $form ){
			var current_action = $form.children( "input[name=action]" ).val();
			$form.children( "img" ).hide();
			if ( responseText[ 'reload_transfert' ] ) {
				jQuery( "#digi-datas-transfert-progression-container-" + responseText[ 'type' ] ).html( responseText[ 'nb' ]);
				$form.children( "input[name=autoreload]" ).val( "yes" );
				$form.children( "button" ).click( );
				if ( ( "wpdigi-heavydatas-transfert" == current_action ) && ( "" != responseText[ "moved_text" ] ) ) {
					jQuery( "#digi-datas-transfert-progression-container-documents" ).html( responseText[ 'moved_text' ]);
				}
			}
			else {
				if ( "wpdigi-datas-transfert" == current_action ) {
					jQuery( ".wpdigi-dtransfert-message-container" ).html( responseText[ 'message' ] ).show();
					jQuery( ".wpdigi-dtransfert-steps-container div" ).removeClass( "wpdigi-dtransfert-step-current" );
					jQuery( ".wpdigi-dtransfert-steps-container div.wpdigi-dtransfert-step-container-3" ).addClass( "wpdigi-dtransfert-step-current" );

					$form.children( "input[name=action]" ).val( "wpdigi-heavydatas-transfert" );
					jQuery( "button[name=wpeo_itrack_digi_transfer_submitter]" ).html( responseText[ "buttonText" ] );
					$form.closest( "div" ).toggleClass( "wpdigi-dtransfert-form-container-step-1 wpdigi-dtransfert-form-container-step-3" );
				}
				else if ( "wpdigi-heavydatas-transfert" == current_action ) {
					jQuery( "button[name=wpeo_itrack_digi_transfer_submitter]" ).removeClass( "button-primary" ).addClass( "button-secondary" ).prop( "disabled", true );
					if ( responseText[ "dashboard_link" ] != "" ) {
						jQuery( "div.wpeotm-dashboard-link-container" ).html( responseText[ "dashboard_link" ] );
						jQuery( "div.wpeotm-dashboard-link-container a" ).addClass( "button button-primary" );
					}
				}
			}
		}
	});

	jQuery( "input[name=wpdigi-dtrans-userid-behaviour]" ).click( function(){
		if ( jQuery( this ).is( ":checked" ) ) {
			jQuery( ".wp-digi-dtrans-userid-options-container" ).html( "" ).hide();
		}
		else {
			var data = {
				"action": "wpdigi-dtrans-transfert-options-load",
				"element_type": jQuery( this ).closest( "form" ).children( "input[name=element_type_to_transfert]" ).val(),
			};
			jQuery( ".wp-digi-dtrans-userid-options-container" ).load( ajaxurl, data ).show();
		}
	});
});

/**
 *	Get the number of element created during transfer in order to inform user
 */
function get_treated_element( element ) {
	var data = {
		action: "wpdigi-dtrans-get-done-element",
		element: element,
	};
	jQuery.post( ajaxurl, data, function( response ) {
		var i;
		for (i = 0; i < response['transfert'].length; i++) {
			jQuery( "#digi-datas-transfert-progression-container-" + response['transfert'][ i ][ 'type' ] ).html( response['transfert'][ i ][ 'text' ]);
		}

		if ( response[ "auto_reload" ] ) {
			setTimeout( function(){
				get_treated_element( element );
			}, "500");
		}
	}, "json");
}
