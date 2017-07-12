<?php
/**
 * Include the meta-boxes files for groups and working units
 *
 * @author Evarisk
 * @version v5.0
 */

	function includesEvaluationDesRisques($idElement, $chargement = 'tout') {
		require_once(EVA_LIB_PLUGIN_DIR . 'scriptPartieDroite.php');
		require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/evaluationRisquesPostBox.php');

		if (((int)$idElement) != 0) {

			$options = get_option('digirisk_options');
			if ($chargement == 'tout') {

				require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/groupement/headerPartieDroiteGroupement.php');
				require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/uniteDeTravail/headerPartieDroiteUniteTravail.php');

				require_once(EVA_METABOXES_PLUGIN_DIR . 'risque/risque.php');
				require_once(EVA_METABOXES_PLUGIN_DIR . 'utilisateurs/liaisonUtilisateursElement.php');
				if ( !empty( $options ) && !empty( $options[ 'activGroupsManagement' ] ) && 'oui' == $options[ 'activGroupsManagement' ] ) {
					require_once(EVA_METABOXES_PLUGIN_DIR . 'utilisateurs/liaisonGroupesUtilisateursElement.php');
				}
				if ( ( !empty( $options ) && !empty( $options[ 'activRightsManagement' ] ) && 'oui' == $options[ 'activRightsManagement' ] ) && current_user_can('digi_manage_user_right') ) {
					require_once(EVA_METABOXES_PLUGIN_DIR . 'utilisateurs/droitsUtilisateurs.php');
				}
				require_once(EVA_METABOXES_PLUGIN_DIR . 'documentUnique/documentUnique.php' );
				require_once(EVA_METABOXES_PLUGIN_DIR . 'ficheDePoste/ficheDePoste.php' );
				require_once(EVA_METABOXES_PLUGIN_DIR . 'recommandation/recommandationPostBox.php' );
				//require_once(EVA_METABOXES_PLUGIN_DIR . 'actionsCorrectives/actionCorrective.php' );
				require_once(EVA_METABOXES_PLUGIN_DIR . 'accidentDeTravail/accidentDeTravail.php' );
				require_once(EVA_METABOXES_PLUGIN_DIR . 'veilleReglementaire/veilleReglementairePostBox.php');

				/*	Is wpshop plugin exist and is activ so we output the box in order to associate products	*/
				if (is_plugin_active(DIGI_WPSHOP_PLUGIN_MAINFILE)) {
					include_once(EVA_METABOXES_PLUGIN_DIR . 'produits/produitsPostBox.php');
				}

				if (file_exists(EVA_TEMPLATES_PLUGIN_DIR . 'includesEvaluationDesRisquesPerso.php')) {
					include_once(EVA_TEMPLATES_PLUGIN_DIR . 'includesEvaluationDesRisquesPerso.php');
				}
			}
			else if ( $chargement ==  'edit' ) {
				require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/groupement/headerPartieDroiteGroupement.php');
				require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/uniteDeTravail/headerPartieDroiteUniteTravail.php');
			}
			else if ( $chargement ==  'risq' ) {
				require_once(EVA_METABOXES_PLUGIN_DIR . 'risque/risque.php');
				require_once(EVA_METABOXES_PLUGIN_DIR . 'utilisateurs/liaisonUtilisateursElement.php');
				require_once(EVA_METABOXES_PLUGIN_DIR . 'recommandation/recommandationPostBox.php' );
				require_once(EVA_METABOXES_PLUGIN_DIR . 'accidentDeTravail/accidentDeTravail.php' );
				require_once(EVA_METABOXES_PLUGIN_DIR . 'veilleReglementaire/veilleReglementairePostBox.php');

				/*	Is wpshop plugin exist and is activ so we output the box in order to associate products	*/
				if (is_plugin_active(DIGI_WPSHOP_PLUGIN_MAINFILE)) {
					include_once(EVA_METABOXES_PLUGIN_DIR . 'produits/produitsPostBox.php');
				}
			}
			else if ( $chargement ==  'bilan' ) {
				require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/groupement/headerPartieDroiteGroupement.php');
				require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/uniteDeTravail/headerPartieDroiteUniteTravail.php');

				require_once(EVA_METABOXES_PLUGIN_DIR . 'documentUnique/documentUnique.php' );
				require_once(EVA_METABOXES_PLUGIN_DIR . 'ficheDePoste/ficheDePoste.php' );

				if ( ( !empty( $options ) && !empty( $options[ 'activRightsManagement' ] ) && 'oui' == $options[ 'activRightsManagement' ] ) && current_user_can('digi_manage_user_right') ) {
					require_once(EVA_METABOXES_PLUGIN_DIR . 'utilisateurs/droitsUtilisateurs.php');
				}
			}
		}
	}