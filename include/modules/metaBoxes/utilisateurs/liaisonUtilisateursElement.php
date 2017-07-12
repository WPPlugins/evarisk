<?php

	//Postbox definition
	$postBoxId = 'postBoxUtilisateurs';
	$postBoxCallbackFunction = 'getUtilisateursPostBoxBody';
	switch ( $_POST['tableElement'] ) {
		case TABLE_TACHE:
			$postBoxTitle = __('Utilisateurs affect&eacute;s &agrave; la r&eacute;alisation de la t&acirc;che', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
			add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_TACHE, 'rightSide', 'default');
		break;
		case TABLE_ACTIVITE:
			$postBoxTitle = __('Utilisateurs affect&eacute;s &agrave; la r&eacute;alisation de l\'action', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
			add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_ACTIVITE, 'rightSide', 'default');
		break;
		case TABLE_GROUPEMENT:
			$postBoxTitle = __('Utilisateurs du groupement', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
			add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_GROUPEMENTS, 'rightSide', 'default');
			$postBoxId = 'postBoxUtilisateursEvaluated';
			$postBoxTitle = __('Utilisateurs participant &agrave; l\'&eacute;valuation', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
			add_meta_box($postBoxId, $postBoxTitle, 'getParticipantPostBoxBody', PAGE_HOOK_EVARISK_GROUPEMENTS, 'rightSide', 'default', array('tableElement' => TABLE_GROUPEMENT . '_evaluation'));
		break;
		case TABLE_UNITE_TRAVAIL:
			$postBoxTitle = __('Utilisateurs de l\'unit&eacute;', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
			add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_UNITES_DE_TRAVAIL, 'rightSide', 'default');
			$postBoxId = 'postBoxUtilisateursEvaluated';
			$postBoxTitle = __('Utilisateurs participant &agrave; l\'&eacute;valuation', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
			add_meta_box($postBoxId, $postBoxTitle, 'getParticipantPostBoxBody', PAGE_HOOK_EVARISK_UNITES_DE_TRAVAIL, 'rightSide', 'default', array('tableElement' => TABLE_UNITE_TRAVAIL . '_evaluation'));
		break;
		default:
			$postBoxTitle = __('Utilisateurs affect&eacute;s', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
		break;
	}

	require_once(EVA_LIB_PLUGIN_DIR . 'users/evaUserLinkElement.class.php');


	function getUtilisateursPostBoxBody( $arguments, $moreArgs = '' ) {
		$idElement = $arguments['idElement'];
		$tableElement = $arguments['tableElement'];

		if (is_array($moreArgs) && isset($moreArgs['args']['tableElement'])) {
			$tableElement = $moreArgs['args']['tableElement'];
		}

		echo
'<div style="display:none;" id="messageInfo_' . $tableElement . $idElement . '_affectUser" ></div>
<div id="userList' . $tableElement . '" >' . evaUserLinkElement::afficheListeUtilisateur( $tableElement, $idElement ) . '</div>';
	}


	function getUserMultipleAffectationTable( $tableElement, $idElement ) {
		/**	Get all user existing	*/
		$listeUtilisateurs = get_users( array(
			"exclude" => array( 1 ),
		) );

		/**	Add a date input to chose the date that we viewed people	*/
		$contenuAideTitre = "";
		$id = "date_affectation_reelle";
		$labelInput = '';
		$nomChamps = "date_affectation_reelle";

		$output = '
		<form action="' . admin_url( 'admin-ajax.php' ) . '" method="POST" id="digi-affect-user-' . $tableElement . '" class="digi-affect-user-form" >
			<input type="hidden" value="digi_affect_users_to_element" name="action" />
			<input type="hidden" value="' . $tableElement . '" name="tableElement" />
			<input type="hidden" value="' . $idElement . '" name="idElement" />
			<fieldset style="border-top: 1px solid #000; margin-top: 25px;" >
				<legend class="digi-users-opener">' . __( 'Affecter des utilisateurs', 'digirisk' ) . '</legend>

				<div class="alignright" id="digi-users-affectation-' . $tableElement . '-default-values" >
					<label for="' . $id . '" >' . __( 'Date', 'digirisk' ) . '</label> : <span class="fieldInfo pointer putTodayInDate">' . __('Aujourd\'hui', 'evarisk') . '</span>' . EvaDisplayInput::afficherInput( 'text', $id, substr( current_time( 'mysql', 0 ), 0, -3 ), $contenuAideTitre, $labelInput, $nomChamps, false, true, 255, '', '', '125px', '', '', true, '100', ' readonly="readonly"');
		switch ( $tableElement ) {
			case TABLE_GROUPEMENT:
			case TABLE_UNITE_TRAVAIL:
				$output .= '<label>' . __( '&Eacute;craser la date d\'embauche (si pr&eacute;sente) par la date sp&eacute;cifi&eacute;e', 'evarisk' ) . ' <input type="checkbox" value="yes" name="overwrite_hiring_date" /></label>';
			break;
			case TABLE_GROUPEMENT . '_evaluation':
			case TABLE_UNITE_TRAVAIL . '_evaluation':
				$input_hour = '<input type="text" name="digi-users-affectation-default-duration-hour" class="digi-users-affectation-duration digi-users-affectation-duration-hour" value="" id="digi-users-affectation-default-duration-hour" style="width: 30px;" />';
				$input_minute = '<input type="text" name="digi-users-affectation-default-duration-minutes" class="digi-users-affectation-duration digi-users-affectation-duration-minutes" value="15" id="digi-users-affectation-default-duration-minutes" style="width: 30px" />';

				$output .= __( 'Dur&eacute;e d&eacute;faut', 'digirisk' ) . ' ' . $input_hour . ' ' . __('H', 'evarisk') . ' ' . $input_minute;
			break;
		}
		$output .= '
				</div>
				<table class="clear" id="digi-users-affectation-' . $tableElement . '" >
					<thead>
						<th></th>
						<th style="text-align: left;" >' . __( 'Id.', 'digirisk' ) . '</th>
						<th style="text-align: left;" >' . __( 'Nom', 'digirisk' ) . '</th>
						<th style="text-align: left;" >' . __( 'Pr&eacute;nom', 'digirisk' ) . '</th>';
		switch ( $tableElement ) {
			case TABLE_GROUPEMENT:
			case TABLE_UNITE_TRAVAIL:
				$output .= '<th>' . __( 'Date embauche', 'digirisk' ) . '</th>';
				break;
			case TABLE_GROUPEMENT . '_evaluation':
			case TABLE_UNITE_TRAVAIL . '_evaluation':
				$output .= '<th>' . __( 'Dur&eacute;e', 'digirisk' ) . '</th>';
				break;
		}
		$output .= '
					</thead>

					<tbody>';
		foreach ( $listeUtilisateurs as $user ) {
			$currentUser = evaUser::getUserInformation( $user->ID );

			$input_hour = '<input type="text" name="digi_user_time[' . $user->ID . '][hour]" class="digi-users-affectation-duration digi-users-affectation-duration-hour" value="" style="width: 30px;" />';
			$input_minute = '<input type="text" name="digi_user_time[' . $user->ID . '][minutes]" class="digi-users-affectation-duration digi-users-affectation-duration-minutes" value="" style="width: 30px" />';

			$output .= '
						<tr>
							<td><input type="checkbox" name="digi_user[]" value="' . $user->ID . '" id="digi_users_evaluation_affectation_' . $user->ID . '" class="digi-users-affectation-checkbox" /></td>
							<td><label for="digi_users_evaluation_affectation_' . $user->ID . '" >' . ELEMENT_IDENTIFIER_U . $user->ID . '</label></td>
							<td><label for="digi_users_evaluation_affectation_' . $user->ID . '" >' . $currentUser[ $user->ID ][ 'user_lastname' ] . '</label></td>
							<td><label for="digi_users_evaluation_affectation_' . $user->ID . '" >' . $currentUser[ $user->ID ][ 'user_firstname' ] . '</label></td>';
		switch ( $tableElement ) {
			case TABLE_GROUPEMENT:
			case TABLE_UNITE_TRAVAIL:
				$contenuAideTitre = "";
				$id = "date_embauche_$user->ID";
				$labelInput = '';
				$nomChamps = "date_embauche[$user->ID]";
				$value = !empty( $currentUser ) && !empty( $currentUser[ $user->ID ] ) && !empty( $currentUser[ $user->ID ][ 'digi_hiring_date' ] ) ? $currentUser[ $user->ID ][ 'digi_hiring_date' ] : null;
				$output .= '<td style="text-align:' . ( !empty( $value ) ? 'left' : 'center' ) . ';" >' . ( !empty( $value ) ? mysql2date( get_option( 'date_format' ), $value, true ) : __( 'Pas de date enregistr&eacute;e', 'evarisk' ) ) . EvaDisplayInput::afficherInput( 'hidden', $id, $value, $contenuAideTitre, $labelInput, $nomChamps, false, true, 255, '', '', '100%', '', '', true, '100', ' readonly="readonly"') . '</td>';
				break;
			case TABLE_GROUPEMENT . '_evaluation':
			case TABLE_UNITE_TRAVAIL . '_evaluation':
				$output .= '<td style="text-align: center;" >' . $input_hour . ' ' . __('H', 'evarisk') . ' ' . $input_minute . '</td>';
				break;
		}
		$output .= '
						</tr>
			';
		}

		$output .= '</tbody>
				</table>
				<input type="submit" class="button button-primary alignright" value="' . __( 'Enregistrer', 'digirisk' ) . '" name="digi-affect-user-form-button" />
				<img src="' . admin_url( 'images/loading.gif' ) . '" alt="' . __( 'saving users link', 'digirisk') . '" title="' . __( 'saving users link', 'digirisk') . '" id="digi-affect-user-form-loader" class="alignright digirisk_hide" style="margin: 5px;"  />
			</fieldset>
		</form>
		<script type="text/javascript" >
			digirisk(document).ready(function(){
				digirisk("#digi-users-affectation-' . $tableElement . '").dataTable({
					"sScrollY": "200px",
					"bPaginate": false,
					"bScrollCollapse": true,
					"bLengthChange": false,
					"bInfo": false,
					"bFilter": true,
					"aoColumnDefs": [
					{ "bSortable": false, "aTargets": [ 0, 4 ] },
					],
					"oLanguage":{
					"sUrl": "' . EVA_INC_PLUGIN_URL . 'js/dataTable/jquery.dataTables.common_translation.txt"
					},
				});
			});
		</script>';

		return $output;
	}


	function getParticipantPostBoxBody( $arguments, $moreArgs = '' ) {
		$idElement = $arguments['idElement'];
		$tableElement = $arguments['tableElement'];

		if (is_array($moreArgs) && isset($moreArgs['args']['tableElement'])) {
			$tableElement = $moreArgs['args']['tableElement'];
		}

		/**	Display metabox content	*/
		echo '<div style="display:none;" id="messageInfo_' . $tableElement . $idElement . '_affectUser" ></div>
<div>';

	/**	Get users affected to current element	*/
	$utilisateursLies = evaUserLinkElement::getAffectedUser( $tableElement, $idElement );

		echo '
	<table style="width: 100%; border: 1px solid #DDD;" id="userList' . $tableElement . '" >
		<thead>

			<th>' . __( 'Date - Heure', 'digirisk' ) . '</th>
			<th style="text-align: left;" >' . __( 'Nom', 'digirisk' ) . '</th>
			<th style="text-align: left;" >' . __( 'Pr&eacute;nom', 'digirisk' ) . '</th>
			<th style="text-align: left;" >' . __( 'Dur&eacute;e', 'digirisk' ) . '</th>
			<th style="width: 25px;" ></th>
		</thead>

		<tbody>';

		if ( !empty( $utilisateursLies ) ) {
			$final_tabs = array();
			foreach ( $utilisateursLies as $theUser ) {
				$currentUser = evaUser::getUserInformation( $theUser->id_user );
				$final_tabs[ mysql2date( get_option( 'date_format' ) , $theUser->date_affectation_reelle ) ][ mysql2date( get_option( 'time_format' ) , $theUser->date_affectation_reelle ) ][] = array(
					'user_name' 		=> $currentUser[ $theUser->id_user ][ 'user_lastname' ] . ' ' . $currentUser[ $theUser->id_user ][ 'user_firstname' ],
					'user_lastname' 	=> $currentUser[ $theUser->id_user ][ 'user_lastname' ],
					'user_firstname' 	=> $currentUser[ $theUser->id_user ][ 'user_firstname' ],
					'user_viewed_time'  => $theUser->duration_in_hour,
					'user_id' 			=> $theUser->id_user,
					'link_id' 			=> $theUser->id,
				);
			}

			foreach ( $final_tabs as $theDate => $theDateContent ) {
				foreach ( $theDateContent as $theTime => $theUsers ) {
					foreach ( $theUsers as $theUser ) {
						echo '
					<tr id="digi-user-affectation-' . $theUser[ 'link_id' ] . '" >
						<td style="text-align: right;" >' . $theDate . ' - ' . $theTime . '</td>
						<td style="text-align: center;" >' . $theUser[ 'user_lastname' ] . '</td>
						<td style="text-align: center;" >' . $theUser[ 'user_firstname' ] . '</td>
						<td style="text-align: center;" >' . $theUser[ 'user_viewed_time' ] . '</td>
						<td style="width: 25px; text-align: middle;" class="digi-user-affectation-actions" id="digi-user-affectation-' . $theUser[ 'link_id' ] . '" ><img style="cursor: pointer;" src="' . PICTO_DELETE_VSMALL . '" alt="' . __( 'Supprimer', 'digirisk' ) . '" /></td>
					</tr>';
					}
				}
			}
		}
		else {
			echo '
				<tr id="digi-user-affectation-no-user" >
					<td style="text-align: right;" >-</td>
					<td style="text-align: right;" >-</td>
					<td style="text-align: center;" >' . __( 'Aucun utilisateur affect&eacute; pour le moment', 'digirisk' ) . '</td>
					<td style="text-align: center;" >-</td>
					<td style="width: 25px; text-align: middle;" >&nbsp;</td>
				</tr>';
		}

		echo '
		</tbody>
	</table>';

	if ( !empty( $moreArgs ) && is_array( $moreArgs ) && !empty( $moreArgs['action_success'] ) && ( 1 == $moreArgs['action_success'] ) ) {
		echo '<div class="updated" >' . __( 'Les utilisateurs ont bien &eacute;t&eacute; ajout&eacute;s &agrave; la liste', 'digirisk' ) . '</div>';
	}

	echo getUserMultipleAffectationTable(  $tableElement, $idElement  );

	echo '
</div>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		oTable = digirisk("#userList' . $tableElement . '").dataTable({
	        "aoColumnDefs": [
	            { "bSortable": false, "aTargets": [ 4 ] },
	        ],
	        "aaSorting": [[ 0, "asc" ]],
	        "oLanguage":{
				"sUrl": "' . EVA_INC_PLUGIN_URL . 'js/dataTable/jquery.dataTables.common_translation.txt"
			},
	        "bPaginate": false,
	        "bScrollCollapse": true,
	        "bLengthChange": false,
	        "bInfo": false,
	    });

		digirisk( ".digi-user-affectation-actions img" ).click(function( e ){
			if ( confirm( digi_html_accent_for_js( "' . __( '&Eacute;tes vous s&ucirc;r de vouloir supprimer cette affectation', 'digirisk' ) . '" ) ) ) {
				var the_link_id = jQuery( this ).parent( "td" ).attr( "id" ).replace( "digi-user-affectation-", "" );
				var data = {
					"action": "digi_ajax_delete_user_affectation",
					"affectation-id": the_link_id,
				};
				digirisk.post( ajaxurl, data, function( response ){
					if ( response[ "status" ] ) {
						digirisk( "#digi-user-affectation-" + response[ "link_id" ] ).remove();
					}

					actionMessageShow( "#messageInfo_' . $tableElement . $idElement . '_affectUser", response[ "message" ] );
					setTimeout( function(){
						actionMessageHide( "#messageInfo_' . $tableElement . $idElement . '_affectUser" );
					}, "1500");
				}, "json")
			}
		});

		jQuery( document ).on( "click", ".digi-users-affectation-checkbox", function(){
			if ( jQuery( this ).is( ":checked" ) ) {
				jQuery( this ).closest( "tr" ).find( ".digi-users-affectation-duration-hour" ).val( jQuery( "#digi-users-affectation-default-duration-hour" ).val() );
				jQuery( this ).closest( "tr" ).find( ".digi-users-affectation-duration-minutes" ).val( jQuery( "#digi-users-affectation-default-duration-minutes" ).val() );
			}
		} );

		jQuery(".digi-users-affectation-duration").keypad({
			keypadOnly: false,
		}).numeric().on( "keyup", function() {
			if ( "" != jQuery(this).val() ) {
				jQuery( this ).closest( "tr" ).children( "td" ).children( "input[type=checkbox]" ).prop( "checked", true );
			}
		} ).on( "keypress", function( event ){
			if (event.which && (event.which < 48 || event.which >57) && event.which != 8) {
				event.preventDefault();
			}
		} );

		jQuery("#date_affectation_reelle").datetimepicker({
			dateFormat: "yy-mm-dd",
			timeFormat: "hh:mm",
			changeMonth: true,
			changeYear: true,
			navigationAsDateFormat: true,
			showButtonPanel: false,
			onClose: function(selectedDate, input) {

			}
		});
		jQuery("#date_affectation_reelle").val("' . substr( current_time( 'mysql', 0 ), 0, -3 ) . '");

		digirisk( ".digi-affect-user-form" ).ajaxForm({
			success: function( responseText, statusText, xhr, $form ){
				jQuery( "#postBoxUtilisateursEvaluated .inside" ).html( responseText );
			},
			beforeSubmit: function( formData, jqForm, options ){
				jQuery( "input[name=digi-affect-user-form-button]" ).hide();
				jQuery( "#digi-affect-user-form-loader" ).show();
			},
		});

		setTimeout( function(){
			jQuery( "#digi-users-affectation-' . $tableElement . '_filter" ).after( jQuery( "#digi-users-affectation-' . $tableElement . '-default-values" ) );
		}, "1000");
	});
</script>';

	}

?>