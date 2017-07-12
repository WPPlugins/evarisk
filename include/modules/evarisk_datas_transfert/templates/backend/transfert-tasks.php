<div class="wpdigi-dtransfert-main-container" >
	<div class="wpdigi-dtransfert-message-container" ></div>
	<div class="wpdigi-dtransfert-steps-container" >
		<div class="wpdigi-dtransfert-step-container wpdigi-dtransfert-step-container-1<?php echo ( 1 == $current_step ? ' wpdigi-dtransfert-step-current' : '' ); ?>" >
			<i class="wpeo-circlerounded">1</i><span><?php echo _e( 'Projects', 'wp-digi-dtrans-i18n'); ?></span>
			<span class="wpdigi-dtransfert-step-progression" id="digi-datas-transfert-progression-container-<?php echo $main_element_type; ?>" ><?php echo ( $sub_element_already_moved + $main_element_already_moved ); ?> / <?php echo ( $nb_element_to_transfert->sub_element_nb + $nb_element_to_transfert->main_element_nb ); ?></span>
		</div>
		<div class="wpdigi-dtransfert-step-container wpdigi-dtransfert-step-container-2<?php echo ( 2 == $current_step ? ' wpdigi-dtransfert-step-current' : '' ); ?>" >
			<i class="wpeo-circlerounded">2</i><span><?php echo _e( 'Documents and pictures', 'wp-digi-dtrans-i18n'); ?></span>
			<span class="wpdigi-dtransfert-step-progression" id="digi-datas-transfert-progression-container-<?php echo $sub_element_type; ?>" >
				<?php echo $heavy_docs_already_done; ?> / <?php echo ( $nb_element_to_transfert->nb_pictures + $nb_element_to_transfert->nb_documents ); ?>
				<?php if ( !empty( $heavy_docs_unable_to_do ) ) : echo '<br/>' . $heavy_docs_unable_to_do; endif; ?>
			</span>
		</div>
		<div class="wpdigi-dtransfert-step-container wpdigi-dtransfert-step-container-3<?php echo ( DIGI_DTRANS_MEDIAN_MAX_STEP == $current_step ? ' wpdigi-dtransfert-step-current' : '' ); ?>" >
			<i class="wpeo-circlerounded">3</i><span><?php echo _e( 'Start using task manager', 'wp-digi-dtrans-i18n'); ?></span>
		</div>
	</div>

	<div class="wpdigi-dtransfert-form-container wpdigi-dtransfert-form-container-step-<?php echo $current_step; ?>" >
		<?php if ( DIGI_DTRANS_MAX_STEP > $current_step ) : ?>
			<form action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post" class="wpdigi-datas-transfert-form" >
				<input type="hidden" name="action" value="<?php echo ( DIGI_DTRANS_MEDIAN_MAX_STEP == $current_step ? 'wpdigi-heavydatas-transfert' : 'wpdigi-datas-transfert' ); ?>" />
				<input type="hidden" name="element_type_to_transfert" value="<?php echo $element_type_to_transfer; ?>" />
				<input type="hidden" name="number_per_page" value="<?php echo DIGI_DTRANS_NB_ELMT_PER_PAGE; ?>" />
				<input type="hidden" name="autoreload" value="<?php echo ( $main_element_already_moved < $nb_element_to_transfert->main_element_nb ) ? 'no' : 'yes'; ?>" />
				<img src="<?php echo admin_url( 'images/spinner.gif' ); ?>" alt="<?php _e( 'Please wait while transfert is in progress', 'wp-digi-dtrans-i18n' ); ?>" />

				<fieldset>
					<legend><?php _e( 'Transfert options', 'wp-digi-dtrans-i18n' ); ?></legend>

					<div>
						<label><input type="checkbox" value="keep_user_id" name="wpdigi-dtrans-userid-behaviour" checked="checked" /> <?php _e( 'Use same user id for import', 'wp-digi-dtrans-i18n' ); ?></label>
						<div class="wp-digi-dtrans-userid-options-container" ><?php _e( 'Loading transfert options...', 'wp-digi-dtrans-i18n' ); ?></div>
					</div>
				</fieldset>

				<button type="submit" class="button button-primary" name="wpeo_itrack_digi_transfer_submitter" ><?php echo ( DIGI_DTRANS_MEDIAN_MAX_STEP == $current_step ? __( 'Move documents and pictures', 'wp-digi-dtrans-i18n' ) : __( 'Move tasks', 'wp-digi-dtrans-i18n' ) ); ?></button>
			</form>
			<div id="digi-datas-transfert-progression-container" ></div>
		<?php else: ?>
			<?php _e( 'All transfert have been done. Please use link below in order to go to task management dashboard', 'wp-digi-dtrans-i18n' ); ?>
		<?php endif; ?>

		<div class="wpeotm-dashboard-link-container" ><?php require( self::get_template_part( DIGI_DTRANS_DIR, DIGI_DTRANS_TEMPLATES_MAIN_DIR, "backend", "transfert", "tasks-dashboardlink" ) ); ?></div>
	</div>
</div>