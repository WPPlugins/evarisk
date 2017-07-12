<div class="wrap wpdigi-stes-dashboard-wrap">
	<div class="wpdigi-stes-dashboard-header" >
		<h2 class="alignleft" ><?php _e('Risk evaluation', 'wpdigi-societies-i18n'); ?></h2>
		<div class="alignright" ><?php echo evaNotes::noteDialogMaker(); ?></div>
	</div><!-- wpdigi-stes-dashboard-header -->

	<div class="wpdigi-stes-dashboard-content" >

		<div class="wpdigi-stes-dashboard-content-left" >Left</div><!-- wpdigi-stes-dashboard-content-left -->

		<div class="wpdigi-stes-dashboard-content-right" >
			<div>fds</div>
			<div id="wpdigi-stes-dashboard-widgetswrap" class="metabox-holder" ><?php
			/**	Create nonce for metabox order saving securisation	*/
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false);
			/**	Create nonce for metabox order saving securisation	*/
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false);

			/**	Call the different fonction to add meta boxes on dashboard	*/
			do_meta_boxes( 'wpdigi-stes-dashboard', 'wpdigi-stes-dashboard-summary', null );
			?></div><!-- wpdigi-stes-dashboard-widgetswrap -->
		</div><!-- wpdigi-stes-dashboard-content-right -->

	</div><!-- wpdigi-stes-dashboard-content -->

</div><!-- wpdigi-dashboard-wrap -->