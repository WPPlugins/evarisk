<div class="about-wrap wp-digi-wrap">
	<h1><?php _e( 'Transfer datas for digirisk', 'wp-digi-dtrans-i18n' ); ?></h1>
	<div class="about-text"><?php _e( 'Next digirisk version will use more wordpress fonctionnalities. For this purpose we need to transfer some datas from our specific storage to wordpress storage.', 'wp-digi-dtrans-i18n' ); ?></div>

	<h2 class="wp-digi-alert wp-digi-alert-error wp-digi-center" ><span class="wp-digi-bold" ><?php _e( 'Important note', 'wp-digi-dtrans-i18n' ); ?> :</span> <?php _e( 'Be sure to make a backup of your datas before launching datas transfer', 'wp-digi-dtrans-i18n' ); ?></h2>

	<ul class="wp-digi-elements-to-transfer" >
	<?php 		/**	Read the different types	*/
		$documents_to_transfer = $documents_transfered = 0;
		$element_to_treat = null;
		foreach ( $this->element_types as $element_type ) :
			/**	Define element to treat by default with first array entry	*/
			$element_to_treat = empty( $element_to_treat ) ? $element_type : $element_to_treat;

			/**	Define the subelement type from main given	*/
			$sub_element_type = '';
			switch ( $element_type ) :
				case TABLE_TACHE:
					$main_element_name = __( 'Tasks', 'wp-digi-dtrans-i18n' );
					$sub_element_type = TABLE_ACTIVITE;
					$sub_element_name = __( 'Sub tasks', 'wp-digi-dtrans-i18n' );
					break;

				case TABLE_GROUPEMENT:
					$main_element_name = __( 'Groups', 'wp-digi-dtrans-i18n' );
					$sub_element_type = TABLE_UNITE_TRAVAIL;
					$sub_element_name = __( 'Work unit', 'wp-digi-dtrans-i18n' );
					break;
			endswitch;

			/**	Count the different eleent that have to be transfered	*/
			$element_to_transfert_count = $this->build_element_transfert_number( $element_type, $sub_element_type );

			/**		*/
			if ( ( $element_to_treat == $element_type ) && ( $element_to_transfert_count[ $element_type ][ 'transfered' ] + $element_to_transfert_count[ $sub_element_type ][ 'transfered' ] ) == ( $element_to_transfert_count[ $element_type ][ 'to_transfer' ] + $element_to_transfert_count[ $sub_element_type ][ 'to_transfer' ] ) ) {
				$element_to_treat = null;
			}

			/**	Increment document number to transfer	*/
			$documents_to_transfer += $element_to_transfert_count[ $element_type ][ 'doc_to_transfer' ];
			$documents_transfered = $element_to_transfert_count[ $element_type ][ 'doc_transfered' ];
			$documents_not_transfered = $element_to_transfert_count[ $element_type ][ 'doc_not_transfered' ];
		?>
		<li>
			<div class="wp-digi-datastransfer-element-type-name" ><?php if ( $element_to_transfert_count[ $element_type ][ 'to_transfer' ] == $element_to_transfert_count[ $element_type ][ 'transfered' ] ) : ?><i class="dashicons dashicons-yes" ></i><?php endif; ?><?php echo $main_element_name; ?></div>
			<ul class="wp-digi-datastransfer-element-type-detail" >
				<li><?php _e( 'Total', 'wp-digi-dtrans-i18n' ); ?> : <span><?php echo $element_to_transfert_count[ $element_type ][ 'to_transfer' ]; ?></span></li>
				<li><?php _e( 'Transfered', 'wp-digi-dtrans-i18n' ); ?> : <span class="wpdigi-transfered-element-nb-<?php echo $element_type; ?>" ><?php echo $element_to_transfert_count[ $element_type ][ 'transfered' ]; ?></span></li>
				<li>&nbsp;</li>
			</ul>
		</li>
		<li>
			<div class="wp-digi-datastransfer-element-type-name" ><?php if ( $element_to_transfert_count[ $sub_element_type ][ 'to_transfer' ] == $element_to_transfert_count[ $sub_element_type ][ 'transfered' ] ) : ?><i class="dashicons dashicons-yes" ></i><?php endif; ?><?php echo $sub_element_name; ?></div>
			<ul class="wp-digi-datastransfer-element-type-detail" >
				<li><?php _e( 'Total', 'wp-digi-dtrans-i18n' ); ?> : <span><?php echo $element_to_transfert_count[ $sub_element_type ][ 'to_transfer' ]; ?></span></li>
				<li><?php _e( 'Transfered', 'wp-digi-dtrans-i18n' ); ?> : <span class="wpdigi-transfered-element-nb-<?php echo $sub_element_type; ?>" ><?php echo $element_to_transfert_count[ $sub_element_type ][ 'transfered' ]; ?></span></li>
				<li>&nbsp;</li>
			</ul>
		</li>
	<?php endforeach; ?>

		<?php /**	Display	document transfer informations */	?>
		<li>
			<div class="wp-digi-datastransfer-element-type-name" ><?php _e( 'Documents', 'wp-digi-dtrans-i18n' ); ?></div>
			<ul class="wp-digi-datastransfer-element-type-detail" >
				<li><?php _e( 'Total', 'wp-digi-dtrans-i18n' ); ?> : <span><?php echo $documents_to_transfer; ?></span></li>
				<li><?php _e( 'Transfered', 'wp-digi-dtrans-i18n' ); ?> : <span class="wpdigi-transfered-element-nb-documents" ><?php echo $documents_transfered; ?></span></li>
				<li><?php _e( 'Not Transfered', 'wp-digi-dtrans-i18n' ); ?> : <span class="wpdigi-not-transfered-element-nb-documents" ><?php echo $documents_not_transfered; ?></span></li>
			</ul>
		</li>
	</ul>

	<div class="wp-digi-alert wp-digi-alert-error wp-digi-center" id="wp-digi-transfert-message" ></div>

	<form action="<?php echo admin_url( 'admin-ajax.php' ); ?>" id="wpdigi-datastransfer-form" method="post" >
		<input type="hidden" name="action" value="wpdigi-datas-transfert" />
		<input type="hidden" name="sub_action" value="<?php echo ( empty( $element_to_treat ) ? 'docs' : 'elements' ); ?>" />
		<input type="hidden" name="wpdigi-nonce" value="<?php echo wp_create_nonce( 'wpdigi-launchtransfer-form' ); ?>" />
		<input type="hidden" name="element_type_to_transfert" value="<?php echo ( empty( $element_to_treat ) ? $this->element_types[ 0 ] :  $element_to_treat ); ?>" />
		<input type="hidden" name="number_per_page" value="<?php echo DIGI_DTRANS_NB_ELMT_PER_PAGE; ?>" />

		<button class="wp-digi-bton wp-digi-bton-first alignright" ><?php _e( 'Launch transfer', 'wp-digi-dtrans-i18n' ); ?></button>
	</form>

</div>