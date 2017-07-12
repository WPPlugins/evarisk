<?php
/**
 * Include the meta-boxes files for groups and working units
 *
 * @author Evarisk
 * @version v5.0
 */

	function includesGestionGroupementUniteTravail($idElement, $chargement = 'tout') {
		require_once(EVA_LIB_PLUGIN_DIR . 'scriptPartieDroite.php');
		require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/evaluationRisquesPostBox.php');
		if (( $chargement == 'tout' ) ||  ( $chargement == 'edit' ) ) {
			require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/uniteDeTravail/uniteTravail-new.php');
			require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/groupement/groupement-new.php');
			if ( ((int)$idElement) != 0 ) {
				require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/uniteDeTravail/headerPartieDroiteUniteTravail.php');
				require_once(EVA_METABOXES_PLUGIN_DIR . 'evaluationDesRisques/groupement/headerPartieDroiteGroupement.php');
				require_once(EVA_METABOXES_PLUGIN_DIR . 'galeriePhotos/galeriePhotos.php');
				require_once(EVA_METABOXES_PLUGIN_DIR . 'geolocalisation/geolocalisation.php' );
				if(file_exists(EVA_TEMPLATES_PLUGIN_DIR . 'includesEvaluationDesRisquesPerso.php'))
					include_once(EVA_TEMPLATES_PLUGIN_DIR . 'includesEvaluationDesRisquesPerso.php');
			}
		}
	}