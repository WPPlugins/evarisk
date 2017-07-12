<?php
/**
* Include every file we need in the plugin.
*
*	It avoid to include files in every script and allows to make changes on the filename easily. And to know wich file is included and were it is located
* @author Evarisk <dev@evarisk.com>
* @version 5.1.2.9
* @package Digirisk
*/
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

include_once( WPDIGI_PATH . '/core/module_management/module_management.php');

require_once(EVA_LIB_PLUGIN_DIR . 'outils/tools.class.php' );

require_once(EVA_LIB_PLUGIN_DIR . 'methode/eva_variable.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'methode/eva_operateur.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'photo/evaPhoto.class.php' );

require_once(EVA_LIB_PLUGIN_DIR . 'options.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'install.class.php' );

require_once(EVA_LIB_PLUGIN_DIR . 'arborescence.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'arborescence/arborescence_special.class.php' );

require_once(EVA_LIB_PLUGIN_DIR . 'mailbox/messages.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'notification/notification.class.php' );

require_once(EVA_LIB_PLUGIN_DIR . 'display/display.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'evaDisplayDesign.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'evaDisplayInput.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'doc.class.php' );

require_once(EVA_LIB_PLUGIN_DIR . 'gestionDocumentaire/gestionDoc.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'database.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'form.class.php');

require_once(EVA_LIB_PLUGIN_DIR . 'dashboard/dashboard.class.php');

require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/evaluationDesRisques.class.php');

require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/actionsCorrectives.class.php');

require_once(EVA_LIB_PLUGIN_DIR . 'methode/methodeEvaluation.class.php' );

require_once(EVA_LIB_PLUGIN_DIR . 'danger/danger.class.php' );

require_once(EVA_LIB_PLUGIN_DIR . 'users/evaUser.class.php' );
	add_action('edit_user_profile', array('evaUser', 'user_additionnal_field'));
	add_action('edit_user_profile_update', array('evaUser', 'user_additionnal_field_save'));
require_once(EVA_LIB_PLUGIN_DIR . 'users/evaUserLinkElement.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'users/groups.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'users/digi_user_notification.class.php' );

require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/veilleReglementaire.class.php' );

require_once(EVA_LIB_PLUGIN_DIR . 'evaRecommandation/evaRecommandation.class.php' );

require_once(EVA_LIB_PLUGIN_DIR . 'produits/produits.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'produits/categories.class.php' );

require_once(EVA_LIB_PLUGIN_DIR . 'accident/accident.class.php' );

require_once(EVA_LIB_PLUGIN_DIR . 'permission.class.php' );

require_once(EVA_LIB_PLUGIN_DIR . 'evaGoogleMaps.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'adresse/evaAddress.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'danger/categorieDangers/categorieDangers.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'danger/danger/evaDanger.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/groupement/eva_groupement.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/uniteDeTravail/uniteDeTravail.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaAnswerToQuestion.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaGroupeQuestion.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/tache/evaTask.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/tache/evaTaskTable.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/activite/evaActivity.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/suivi_activite.class.php');

require_once(EVA_LIB_PLUGIN_DIR . 'risque/Risque.class.php');

require_once(EVA_LIB_PLUGIN_DIR . 'Zip/Zip.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'evaNotes.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/ficheDePoste/ficheDePoste.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/ficheDePoste/ficheDeGroupement.class.php');

require_once(EVA_LIB_PLUGIN_DIR . 'db/db_data.php');
require_once(EVA_LIB_PLUGIN_DIR . 'db/db_structure.php');

require_once(EVA_INC_PLUGIN_DIR . 'digi_ajax.php');
