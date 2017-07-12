<?php
/**
* Plugin database start content definition file.
*
*	This file contains the different definitions for the database content.
* @author Evarisk <dev@evarisk.com>
* @version 5.1.4.9
* @package digirisk
* @subpackage librairies-db
*/

$digirisk_options = new digirisk_options();
$current_db_version = $digirisk_options->getDbOption('base_evarisk');

$digirisk_db_update = array();
$digirisk_db_content_add = array();
$digirisk_db_content_update = array();
$digirisk_db_options_add = array();
$digirisk_db_options_update = array();
$digirisk_db_version = 0;

{/*	Define message send for each notification	*/
	$standard_message_subject_to_send = __('Notification de modification sur %s', 'evarisk');
	$standard_message_to_send = __('Bonjour,

Une modification a &eacute;t&eacute; apport&eacute;e &agrave; %s.
Lien vers l\'&eacute;l&eacute;ment modifi&eacute;: %s.
Action: %s.
Date: %s.
Personne ayant r&eacute;alis&eacute; la modification: %s.

%s

Vous recevez cette e-mail car vous &ecirc;tes affect&eacute; &agrave; l\'&eacute;l&eacute;ment concern&eacute; par la modification et que vous &ecirc;tes inscrit &agrave; la liste de notification de cet &eacute;l&eacute;ment', 'evarisk');
}

{/*	Version 0	*/
	$digirisk_db_version = 0;

	$digirisk_db_content_add[$digirisk_db_version][TABLE_TACHE][] = array('Status' => 'Valid', 'nom' => __('Tache Racine', 'evarisk'), 'limiteGauche' => 0, 'limiteDroite' => 1, 'firstInsert' => current_time('mysql', 0));
	$digirisk_db_content_add[$digirisk_db_version][TABLE_GROUPEMENT][] = array('Status' => 'Valid', 'nom' => __('Groupement Racine', 'evarisk'), 'limiteGauche' => 0, 'limiteDroite' => 1, 'creation_date' => current_time('mysql', 0));
	$digirisk_db_content_add[$digirisk_db_version][TABLE_CATEGORIE_DANGER][] = array('Status' => 'Valid', 'nom' => __('Categorie Racine', 'evarisk'), 'limiteGauche' => 0, 'limiteDroite' => 1);
	$digirisk_db_content_add[$digirisk_db_version][TABLE_GROUPE_QUESTION][] = array('Status' => 'Valid', 'nom' => __('Groupe Question Racine', 'evarisk'), 'limiteGauche' => 0, 'limiteDroite' => 1, 'code' => 0);

	$digirisk_db_content_add[$digirisk_db_version][TABLE_ETALON][] = array('Status' => 'Valid', 'min' => 0, 'max' => 100, 'pas' => 1);
	for($i=0; $i<=100; $i++){
		unset($valeur_etalon);
		$valeur_etalon = array('Status' => 'Valid', 'valeur' => $i);
		switch($i){
			case 0:
				$valeur_etalon['niveauSeuil'] = 1;
			break;
			case 48:
				$valeur_etalon['niveauSeuil'] = 2;
			break;
			case 51:
				$valeur_etalon['niveauSeuil'] = 3;
			break;
			case 80:
				$valeur_etalon['niveauSeuil'] = 4;
			break;
		}

		$digirisk_db_content_add[$digirisk_db_version][TABLE_VALEUR_ETALON][] = $valeur_etalon;
	}

	$digirisk_db_content_add[$digirisk_db_version][TABLE_REPONSE][] = array('Status' => 'Valid', 'nom' => __('Oui', 'evarisk'), 'min' => 'null', 'max' => 'null');
	$digirisk_db_content_add[$digirisk_db_version][TABLE_REPONSE][] = array('Status' => 'Valid', 'nom' => __('Non', 'evarisk'), 'min' => 'null', 'max' => 'null');
	$digirisk_db_content_add[$digirisk_db_version][TABLE_REPONSE][] = array('Status' => 'Valid', 'nom' => __('NA', 'evarisk'), 'min' => 'null', 'max' => 'null');
	$digirisk_db_content_add[$digirisk_db_version][TABLE_REPONSE][] = array('Status' => 'Valid', 'nom' => __('NC', 'evarisk'), 'min' => 'null', 'max' => 'null');
	$digirisk_db_content_add[$digirisk_db_version][TABLE_REPONSE][] = array('Status' => 'Valid', 'nom' => __('%', 'evarisk'), 'min' => 0, 'max' => 100);

	$digirisk_db_options_add[$digirisk_db_version]['digirisk_options']['responsable_Tache_Obligatoire'] = 'non';
	$digirisk_db_options_add[$digirisk_db_version]['digirisk_options']['responsable_Action_Obligatoire'] = 'non';
	$digirisk_db_options_add[$digirisk_db_version]['digirisk_options']['possibilite_Modifier_Tache_Soldee'] = 'non';
	$digirisk_db_options_add[$digirisk_db_version]['digirisk_options']['possibilite_Modifier_Action_Soldee'] = 'non';
	$digirisk_db_options_add[$digirisk_db_version]['digirisk_options']['avertir_Solde_Action_Non_100'] = 'oui';
	$digirisk_db_options_add[$digirisk_db_version]['digirisk_options']['avertir_Solde_Tache_Ayant_Action_Non_100'] = 'oui';
	$digirisk_db_options_add[$digirisk_db_version]['digirisk_options']['affecter_uniquement_tache_soldee_a_un_risque'] = 'oui';
	$digirisk_db_options_add[$digirisk_db_version]['digirisk_options']['action_correctives_avancees'] = 'non';
	$digirisk_db_options_add[$digirisk_db_version]['digirisk_options']['risques_avances'] = 'non';
	$digirisk_db_options_add[$digirisk_db_version]['digirisk_options']['export_only_priority_task'] = 'oui';
	$digirisk_db_options_add[$digirisk_db_version]['digirisk_options']['export_tasks'] = 'non';
	$digirisk_db_options_add[$digirisk_db_version]['digirisk_options']['taille_photo_poste_fiche_de_poste'] = '8';
	$digirisk_db_options_add[$digirisk_db_version]['digirisk_options']['recommandation_efficiency_activ'] = 'non';
}

{/*	Version 27	*/
	$digirisk_db_version = 27;

	$digirisk_db_content_add[$digirisk_db_version][TABLE_GED_DOCUMENTS][] = array('status' => 'valid', 'dateCreation' => current_time('mysql', 0), 'idCreateur' => 1, 'dateSuppression' => null, 'idSuppresseur' => 0, 'id_element' => 0, 'table_element' => 'all', 'categorie' => 'document_unique', 'nom' => 'modeleDefaut.odt', 'chemin' => 'uploads/modeles/documentUnique/');
}

{/*	Version 30	*/
	$digirisk_db_version = 30;

	$digirisk_db_content_update[$digirisk_db_version][TABLE_LIAISON_USER_ELEMENT][] = array('datas' => array('table_element' => TABLE_UNITE_TRAVAIL . '_evaluation'), 'where' => array('table_element' => TABLE_UNITE_TRAVAIL));
	$digirisk_db_content_update[$digirisk_db_version][TABLE_LIAISON_USER_ELEMENT][] = array('datas' => array('table_element' => TABLE_GROUPEMENT . '_evaluation'), 'where' => array('table_element' => TABLE_GROUPEMENT));
}

{/*	Version 33	*/
	$digirisk_db_version = 33;

	$digirisk_db_content_update[$digirisk_db_version][TABLE_TACHE][] = array('datas' => array('ProgressionStatus' => 'notStarted'), 'where' => array('avancement' => 0));
	$digirisk_db_content_update[$digirisk_db_version][TABLE_ACTIVITE][] = array('datas' => array('ProgressionStatus' => 'notStarted'), 'where' => array('avancement' => 0));
}
{/*	Version 34	*/
	$digirisk_db_version = 34;

	$digirisk_db_content_add[$digirisk_db_version][TABLE_GED_DOCUMENTS][] = array('status' => 'valid', 'dateCreation' => current_time('mysql', 0), 'idCreateur' => 1, 'dateSuppression' => null, 'idSuppresseur' => 0, 'id_element' => 0, 'table_element' => 'all', 'categorie' => 'fiche_de_poste', 'nom' => 'modeleDefaut.odt', 'chemin' => 'uploads/modeles/ficheDePoste/');
}
{/*	Version 35	*/
	$digirisk_db_version = 35;

	$digirisk_db_update[$digirisk_db_version][TABLE_GED_DOCUMENTS][] = "UPDATE " . TABLE_GED_DOCUMENTS . " SET chemin = REPLACE(chemin, 'uploads/', 'uploads/')";
	$digirisk_db_update[$digirisk_db_version][TABLE_GED_DOCUMENTS][] = "UPDATE " . TABLE_GED_DOCUMENTS . " SET chemin = REPLACE(chemin, 'medias/results/', 'results/')";
	$digirisk_db_update[$digirisk_db_version][TABLE_PHOTO][] = "UPDATE " . TABLE_PHOTO . " SET photo = REPLACE(photo, 'uploads/', 'uploads/')";
	$digirisk_db_update[$digirisk_db_version][TABLE_PHOTO][] = "UPDATE " . TABLE_PHOTO . " SET photo = REPLACE(photo, 'medias/results/', 'results/')";
}

{/*	Version 37	*/
	$digirisk_db_version = 37;

	$digirisk_db_content_update[$digirisk_db_version][TABLE_GED_DOCUMENTS][] = array('datas' => array('parDefaut' => 'oui'), 'where' => array('id_element' => '0', 'table_element' => 'all'));
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$eva_gestionDoc = new eva_gestionDoc();
	$digirisk_db_content_update[$digirisk_db_version][TABLE_FP][] = array('datas' => array('id_model' => $eva_gestionDoc->getDefaultDocument('fiche_de_poste')), 'where' => array());
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//$digirisk_db_content_update[$digirisk_db_version][TABLE_FP][] = array('datas' => array('id_model' => eva_gestionDoc::getDefaultDocument('fiche_de_poste')), 'where' => array());

}
{/*	Version 38	*/
	$digirisk_db_version = 38;

	$digirisk_db_content_add[$digirisk_db_version][TABLE_CATEGORIE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'obligation');
	$digirisk_db_content_add[$digirisk_db_version][TABLE_CATEGORIE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'interdiction');
	$digirisk_db_content_add[$digirisk_db_version][TABLE_CATEGORIE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'recommandation');
}
{/*	Version 39	*/
	$digirisk_db_version = 39;

	$digirisk_db_content_add[$digirisk_db_version][TABLE_CATEGORIE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => '&eacute;quipements de protection individuelle', 'impressionRecommandation' => 'pictureonly', 'tailleimpressionRecommandation' => 2, 'dependance' => array(TABLE_PHOTO => array('medias/images/Pictos/preconisations/obligations/preconisationEPI_s.png', 'yes', TABLE_CATEGORIE_PRECONISATION, '&eacute;quipements de protection individuelle')));
	$digirisk_db_content_add[$digirisk_db_version][TABLE_CATEGORIE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'recommandations', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/recommandations/recommandationsGenerale_s.png', 'yes', TABLE_CATEGORIE_PRECONISATION, 'recommandations')));

	$digirisk_db_content_update[$digirisk_db_version][TABLE_CATEGORIE_PRECONISATION][] = array('datas' => array('status' => 'valid'), 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/obligations/obligationGenerale_s.png', 'yes', TABLE_CATEGORIE_PRECONISATION, 'obligation')), 'where' => array('nom' => 'obligation'));
	$digirisk_db_content_update[$digirisk_db_version][TABLE_CATEGORIE_PRECONISATION][] = array('datas' => array('status' => 'valid'), 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/interdictions/interdictionGenerale_s.png', 'yes', TABLE_CATEGORIE_PRECONISATION, 'interdiction')), 'where' => array('nom' => 'interdiction'));
	$digirisk_db_content_update[$digirisk_db_version][TABLE_CATEGORIE_PRECONISATION][] = array('datas' => array('status' => 'valid'), 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerGeneral_s.png', 'yes', TABLE_CATEGORIE_PRECONISATION, 'recommandation')), 'where' => array('nom' => 'recommandation'));

	{/*	Add the different basic epi obligation	*/
		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Divers obligation', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/obligations/obligationGenerale_s.png', 'yes', TABLE_PRECONISATION, 'Divers obligation')), 'parent_element' => 'obligation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Divers protection individuelle', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/obligations/preconisationEPI_s.png', 'yes', TABLE_PRECONISATION, 'Divers protection individuelle')), 'parent_element' => '&eacute;quipements de protection individuelle');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Protection obligatoire de la vue', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/obligations/obligationVue_s.png', 'yes', TABLE_PRECONISATION, 'Protection obligatoire de la vue')), 'parent_element' => '&eacute;quipements de protection individuelle');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Protection obligatoire de la t&ecirc;te', 'dependance' => array(TABLE_PHOTO => array('medias/images/Pictos/preconisations/obligations/obligationTete_s.png', 'yes', TABLE_PRECONISATION, 'Protection obligatoire de la t&ecirc;te')), 'parent_element' => '&eacute;quipements de protection individuelle');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Protection obligatoire de l\'ou&iuml;e', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/obligations/obligationOuie_s.png', 'yes', TABLE_PRECONISATION, 'Protection obligatoire de l\'ou&iuml;e')), 'parent_element' => '&eacute;quipements de protection individuelle');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Protection obligatoire des voies respiratoires', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/obligations/obligationVoiesRespiratoires_s.png', 'yes', TABLE_PRECONISATION, 'Protection obligatoire des voies respiratoires')), 'parent_element' => '&eacute;quipements de protection individuelle');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Protection obligatoire des pieds', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/obligations/obligationPieds_s.png', 'yes', TABLE_PRECONISATION, 'Protection obligatoire des pieds')), 'parent_element' => '&eacute;quipements de protection individuelle');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Protection obligatoire des mains', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/obligations/obligationMains_s.png', 'yes', TABLE_PRECONISATION, 'Protection obligatoire des mains')), 'parent_element' => '&eacute;quipements de protection individuelle');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Protection obligatoire du corps', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/obligations/obligationCorps_s.png', 'yes', TABLE_PRECONISATION, 'rotection obligatoire du corps')), 'parent_element' => '&eacute;quipements de protection individuelle');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Protection obligatoire de la figure', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/obligations/obligationFigure_s.png', 'yes', TABLE_PRECONISATION, 'Protection obligatoire de la figure')), 'parent_element' => '&eacute;quipements de protection individuelle');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Protection individuelle obligatoire contre les chutes', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/obligations/obligationChute_s.png', 'yes', TABLE_PRECONISATION, 'Protection individuelle obligatoire contre les chutes')), 'parent_element' => '&eacute;quipements de protection individuelle');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Protection obligatoire pour pi&eacute;tons', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/obligations/obligationPietons_s.png', 'yes', TABLE_PRECONISATION, 'Protection obligatoire pour pi&eacute;tons')), 'parent_element' => '&eacute;quipements de protection individuelle');
	}

	{/*	Add the different basic prohibition	*/
		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Divers interdiction', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/interdictions/interdictionGenerale_s.png', 'yes', TABLE_PRECONISATION, 'Divers interdiction')), 'parent_element' => 'interdiction');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'D&eacute;fense de fumer', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/interdictions/interdictionFumer_s.png', 'yes', TABLE_PRECONISATION, 'D&eacute;fense de fumer')), 'parent_element' => 'interdiction');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Flamme nue interdite et d&eacute;fense de fumer', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/interdictions/interdictionFlammeNue_s.png', 'yes', TABLE_PRECONISATION, 'Flamme nue interdite et d&eacute;fense de fumer')), 'parent_element' => 'interdiction');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Interdit aux pi&eacute;tons', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/interdictions/interdictionPietons_s.png', 'yes', TABLE_PRECONISATION, 'Interdit aux pi&eacute;tons')), 'parent_element' => 'interdiction');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'D&eacute;fense d\'&eacute;teindre avec de l\'eau', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/interdictions/interdictionEteindreAvecEau_s.png', 'yes', TABLE_PRECONISATION, 'D&eacute;fense d\'&eacute;teindre avec de l\'eau')), 'parent_element' => 'interdiction');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Eau non potable', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/interdictions/interdictionEauNonPotable_s.png', 'yes', TABLE_PRECONISATION, 'Eau non potable')), 'parent_element' => 'interdiction');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Entr&eacute;e interdite aux personnes non autoris&eacute;es', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/interdictions/interdictionPersonnesNonAutorisees_s.png', 'yes', TABLE_PRECONISATION, 'Entr&eacute;e interdite aux personnes non autoris&eacute;es')), 'parent_element' => 'interdiction');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Interdit aux v&eacute;hicules de manutention', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/interdictions/interdictionVehiculesManutention_s.png', 'yes', TABLE_PRECONISATION, 'Interdit aux v&eacute;hicules de manutention')), 'parent_element' => 'interdiction');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Ne pas toucher', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/interdictions/interdictionToucher_s.png', 'yes', TABLE_PRECONISATION, 'Ne pas toucher')), 'parent_element' => 'interdiction');
	}

	{/*	Add the different basic warning	*/
		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Divers danger', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerGeneral_s.png', 'yes', TABLE_PRECONISATION, 'Divers danger')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Emplacement o&ugrave; une atmosph&egrave;re explosible peut se pr&eacute;senter', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerEX_s.png', 'yes', TABLE_PRECONISATION, 'Emplacement o&ugrave; une atmosph&egrave;re explosible peut se pr&eacute;senter')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Mati&egrave;res inflammables ou haute temp&eacute;rature', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerMatieresInflammables_s.png', 'yes', TABLE_PRECONISATION, 'Mati&egrave;res inflammables ou haute temp&eacute;rature')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Mati&egrave;res explosives Risque d\'explosion', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerMatieresExplosives_s.png', 'yes', TABLE_PRECONISATION, 'Mati&egrave;res explosives Risque d\'explosion')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Mati&egrave;res toxiques', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerMatieresToxiques_s.png', 'yes', TABLE_PRECONISATION, 'Mati&egrave;res toxiques')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Mati&egrave;res corrosives', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerMatieresCorrosives_s.png', 'yes', TABLE_PRECONISATION, 'Mati&egrave;res corrosives')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Mati&egrave;res radioactives radiations ionisantes', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerRayonnementIonisantes_s.png', 'yes', TABLE_PRECONISATION, 'Mati&egrave;res radioactives radiations ionisantes')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Charges suspendues', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerChargesSuspendues_s.png', 'yes', TABLE_PRECONISATION, 'Charges suspendues')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'V&eacute;hicules de manutention', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerVehiculeManutention_s.png', 'yes', TABLE_PRECONISATION, 'V&eacute;hicules de manutention')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Danger &eacute;lectrique', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerElectrique_s.png', 'yes', TABLE_PRECONISATION, 'Danger &eacute;lectrique')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Rayonnement laser', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerRayonnementLaser_s.png', 'yes', TABLE_PRECONISATION, 'Rayonnement laser')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Mati&egrave;res comburantes', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerMatieresComburantes_s.png', 'yes', TABLE_PRECONISATION, 'Mati&egrave;res comburantes')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Radiations non ionisantes', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerRayonnementNonIonisantes_s.png', 'yes', TABLE_PRECONISATION, 'Radiations non ionisantes')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Champ magn&eacute;tique important', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerChampMagnetiqueImportant_s.png', 'yes', TABLE_PRECONISATION, 'Champ magn&eacute;tique important')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Tr&eacute;buchement', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerTrebuchement_s.png', 'yes', TABLE_PRECONISATION, 'Tr&eacute;buchement')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Chute avec d&eacute;nivellation', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerChuteDenivele_s.png', 'yes', TABLE_PRECONISATION, 'Chute avec d&eacute;nivellation')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Risque biologique', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerRisqueBiologique_s.png', 'yes', TABLE_PRECONISATION, 'Risque biologique')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Basse temp&eacute;rature', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerBasseTemperature_s.png', 'yes', TABLE_PRECONISATION, 'Basse temp&eacute;rature')), 'parent_element' => 'recommandation');

		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Mati&egrave;res nocives ou irritantes', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/avertissements/dangerMatieresNocivesIrritantes_s.png', 'yes', TABLE_PRECONISATION, 'Mati&egrave;res nocives ou irritantes')), 'parent_element' => 'recommandation');
	}

	{/*	Add the different basic recommandation	*/
		$digirisk_db_content_add[$digirisk_db_version][TABLE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => 'Divers recommandations', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/recommandations/recommandationsGenerale_s.png', 'yes', TABLE_PRECONISATION, 'Divers recommandations')), 'parent_element' => 'recommandations');
	}
}

{/*	Version 48	*/
	$digirisk_db_version = 48;

	$digirisk_db_content_update[$digirisk_db_version][TABLE_CATEGORIE_PRECONISATION][] = array('datas' => array('nom' => 'avertissements'), 'where' => array('nom' => 'recommandation'));
	$digirisk_db_content_update[$digirisk_db_version][TABLE_CATEGORIE_PRECONISATION][] = array('datas' => array('nom' => 'obligations'), 'where' => array('nom' => 'obligation'));
	$digirisk_db_content_update[$digirisk_db_version][TABLE_CATEGORIE_PRECONISATION][] = array('datas' => array('nom' => 'interdictions'), 'where' => array('nom' => 'interdiction'));
}
{/*	Version 49	*/
	$digirisk_db_version = 49;

	$digirisk_db_content_add[$digirisk_db_version][TABLE_GED_DOCUMENTS][] = array('status' => 'valid', 'dateCreation' => current_time('mysql', 0), 'idCreateur' => 1, 'dateSuppression' => null, 'idSuppresseur' => 0, 'id_element' => 0, 'table_element' => 'all', 'categorie' => 'fiche_de_groupement', 'nom' => 'modeleDefaut_groupement.odt', 'chemin' => 'uploads/modeles/ficheDeGroupement/', 'parDefaut' => 'oui');
}

{/*	Version 52	*/
	$digirisk_db_version = 52;

	$digirisk_db_options_add[$digirisk_db_version]['digirisk_tree_options']['digi_tree_recreation_dialog'] = 'non';
	$digirisk_db_options_add[$digirisk_db_version]['digirisk_tree_options']['digi_tree_recreation_default'] = 'recreate';
}

{/*	Version 55	*/
	$digirisk_db_version = 55;

	$digirisk_db_options_update[$digirisk_db_version]['digirisk_options']['digi_activ_trash'] = 'non';
}

{/*	Version 57	*/
	$digirisk_db_version = 57;

	$digirisk_db_options_update[$digirisk_db_version]['digirisk_product_options']['digi_product_uncategorized_field'] = 'oui';
}

{/*	Version 64	*/
	$digirisk_db_version = 64;

	$digirisk_db_options_update[$digirisk_db_version]['digirisk_options']['digi_ac_allowed_ext'] = array('txt', 'odt', 'pdf', 'doc', 'docx');

	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'update', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'delete', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'mark_done', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'export', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'affectation_update', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'doc_add', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'doc_delete', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'picture_add', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'picture_delete', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'picture_as_main_add', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'picture_as_main_delete', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'picture_as_before_add', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'picture_as_before_delete', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'picture_as_after_add', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'picture_as_after_delete', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'user_affectation_update', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'follow_add', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'add_new_subtask', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);

	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'update', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'delete', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'mark_done', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'set_in_progress', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'doc_add', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'doc_delete', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'picture_add', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'picture_delete', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'picture_as_main_add', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'picture_as_main_delete', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'picture_as_before_add', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'picture_as_before_delete', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'picture_as_after_add', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'picture_as_after_delete', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'user_affectation_update', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'follow_add', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
}
{/*	Version 65	*/
	$digirisk_db_version = 65;

	$digirisk_db_content_update[$digirisk_db_version][TABLE_TACHE][] = array('datas' => array('Status' => 'Deleted'), 'where' => array('nom' => ''));
	$digirisk_db_content_update[$digirisk_db_version][TABLE_ACTIVITE][] = array('datas' => array('Status' => 'Deleted'), 'where' => array('nom' => ''));
}

{/*	Version 67	*/
	$digirisk_db_version = 67;

	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'delete_user_from_affectation_list', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
}
{/*	Version 68	*/
	$digirisk_db_version = 68;

	$digirisk_db_options_update[$digirisk_db_version]['digirisk_options']['digi_ac_allow_front_ask'] = 'oui';
}
{/*	Version 69	*/
	$digirisk_db_version = 69;

	$digirisk_db_update[$digirisk_db_version][TABLE_GED_DOCUMENTS][] = "UPDATE " . TABLE_GED_DOCUMENTS . " SET chemin = REPLACE(chemin, 'uploads/', 'uploads/')";
	$digirisk_db_update[$digirisk_db_version][TABLE_GED_DOCUMENTS][] = "UPDATE " . TABLE_GED_DOCUMENTS . " SET chemin = REPLACE(chemin, 'medias/results/', 'results/')";
	$digirisk_db_update[$digirisk_db_version][TABLE_PHOTO][] = "UPDATE " . TABLE_PHOTO . " SET photo = REPLACE(photo, 'uploads/', 'uploads/')";
	$digirisk_db_update[$digirisk_db_version][TABLE_PHOTO][] = "UPDATE " . TABLE_PHOTO . " SET photo = REPLACE(photo, 'medias/results/', 'results/')";
}

{/*	Version 71	*/
	$digirisk_db_version = 71;

	if($current_db_version<=$digirisk_db_version){/*	Create the new danger categories	*/
		$inrs_danger_categories=unserialize(DIGI_INRS_DANGER_LIST);
		foreach($inrs_danger_categories as $danger_cat){
			if(!empty($danger_cat['version']) && ($danger_cat['version'] == $digirisk_db_version)){

				$categorieDangers = new CategorieDangers();
				$new_danger_cat_id = $categorieDangers->saveNewCategorie($danger_cat['nom']);

				/*	If user ask to add danger in categories	*/
				$wpdb->insert(TABLE_DANGER, array('nom' => __('Divers', 'evarisk') . ' ' . strtolower($danger_cat['nom']), 'id_categorie' => $new_danger_cat_id));
				if(!empty($danger_cat['risks']) && is_array($danger_cat['risks'])){
					foreach($danger_cat['risks'] as $risk_to_create){
						$wpdb->insert(TABLE_DANGER, array('nom' => $risk_to_create, 'id_categorie' => $new_danger_cat_id));
					}
				}

				/*	Insert picture for danger categories	*/

				$evaPhoto = new EvaPhoto();
				$new_cat_pict_id = $evaPhoto->saveNewPicture(TABLE_CATEGORIE_DANGER, $new_danger_cat_id, $danger_cat['picture']);
				$evaPhoto->setMainPhoto(TABLE_CATEGORIE_DANGER, $new_danger_cat_id, $new_cat_pict_id, 'yes');
			}
		}
	}

	/*	Rename the different danger categories	*/
	$digirisk_db_update[$digirisk_db_version][TABLE_CATEGORIE_DANGER][] = "UPDATE " . TABLE_CATEGORIE_DANGER . " SET nom = '" . __('Accident de plain-pied', 'evarisk') . "' WHERE LOWER(nom) = '" . strtolower(__('Chute de plain-pied', 'evarisk')) . "'";
	$digirisk_db_update[$digirisk_db_version][TABLE_CATEGORIE_DANGER][] = "UPDATE " . TABLE_CATEGORIE_DANGER . " SET nom = '" . __('Activit&eacute; physique', 'evarisk') . "' WHERE LOWER(nom) = '" . strtolower(__('Manutention manuelle', 'evarisk')) . "'";
	$digirisk_db_update[$digirisk_db_version][TABLE_CATEGORIE_DANGER][] = "UPDATE " . TABLE_CATEGORIE_DANGER . " SET nom = '" . __('Produits, &eacute;missions et d&eacute;chets', 'evarisk') . "' WHERE LOWER(nom) = '" . strtolower(__('Produits chimiques, d&eacute;chets', 'evarisk')) . "'";
	$digirisk_db_update[$digirisk_db_version][TABLE_CATEGORIE_DANGER][] = "UPDATE " . TABLE_CATEGORIE_DANGER . " SET nom = '" . __('Agents biologique', 'evarisk') . "' WHERE LOWER(nom) = '" . strtolower(__('Manque d\'hygi&egrave;ne', 'evarisk')) . "'";
	$digirisk_db_update[$digirisk_db_version][TABLE_CATEGORIE_DANGER][] = "UPDATE " . TABLE_CATEGORIE_DANGER . " SET nom = '" . __('&Eacute;quipements de travail', 'evarisk') . "' WHERE LOWER(nom) = '" . strtolower(__('Machines et outils', 'evarisk')) . "'";

	/*	Rename danger and transfer to another category	*/
	$digirisk_db_update[$digirisk_db_version][TABLE_DANGER][] = "UPDATE " . TABLE_DANGER . " SET nom = '" . __('Manque de formation', 'evarisk') . "', id_categorie = (" . $wpdb->prepare("SELECT id FROM " . TABLE_CATEGORIE_DANGER . " WHERE LOWER(nom) = %s", strtolower(__('Autres', 'evarisk'))) . ") WHERE LOWER(nom) = '" . strtolower(__('Divers Manque de formation', 'evarisk')) . "'";
	$digirisk_db_update[$digirisk_db_version][TABLE_DANGER][] = "UPDATE " . TABLE_DANGER . " SET nom = '" . __('Soci&eacute;t&eacute; ext&eacute;rieure', 'evarisk') . "', id_categorie = (" . $wpdb->prepare("SELECT id FROM " . TABLE_CATEGORIE_DANGER . " WHERE LOWER(nom) = %s", strtolower(__('Autres', 'evarisk'))) . ") WHERE LOWER(nom) = '" . strtolower(__('Divers Soci&eacute;t&eacute; ext&eacute;rieure', 'evarisk')) . "'";

	/*	Rename danger and transfer to another category	*/
	$digirisk_db_update[$digirisk_db_version][TABLE_DANGER][] = "UPDATE " . TABLE_DANGER . " SET nom = '" . __('Travail sur &eacute;cran', 'evarisk') . "', id_categorie = (" . $wpdb->prepare("SELECT id FROM " . TABLE_CATEGORIE_DANGER . " WHERE LOWER(nom) = %s", strtolower(__('Activit&eacute; physique', 'evarisk'))) . ") WHERE LOWER(nom) = '" . strtolower(__('Divers travail sur &eacute;cran', 'evarisk')) . "'";

	/*	Mark unused categories as deleted	*/
	$digirisk_db_update[$digirisk_db_version][TABLE_CATEGORIE_DANGER][] = "UPDATE " . TABLE_CATEGORIE_DANGER . " SET Status = 'Deleted' WHERE LOWER(nom) = '" . strtolower(__('Manque de formation', 'evarisk')) . "'";
	$digirisk_db_update[$digirisk_db_version][TABLE_CATEGORIE_DANGER][] = "UPDATE " . TABLE_CATEGORIE_DANGER . " SET Status = 'Deleted' WHERE LOWER(nom) = '" . strtolower(__('Soci&eacute;t&eacute; ext&eacute;rieure', 'evarisk')) . "'";
	$digirisk_db_update[$digirisk_db_version][TABLE_CATEGORIE_DANGER][] = "UPDATE " . TABLE_CATEGORIE_DANGER . " SET Status = 'Deleted' WHERE LOWER(nom) = '" . strtolower(__('Travail sur &eacute;cran', 'evarisk')) . "'";
}

{/*	Version 72	*/
	$digirisk_db_version = 72;

	if($current_db_version<=$digirisk_db_version){/*	Insert picture for danger categories	*/
		$query = $wpdb->prepare("SELECT id FROM ".TABLE_CATEGORIE_DANGER." WHERE LOWER(nom) = %s", strtolower(__('Circulations internes', 'evarisk')));
		$cat_id = $wpdb->get_var($query);
		$new_cat_pict_id = $evaPhoto->saveNewPicture(TABLE_CATEGORIE_DANGER, $cat_id, 'medias/images/Pictos/categorieDangers/circulation.png');
		$evaPhoto->setMainPhoto(TABLE_CATEGORIE_DANGER, $cat_id, $new_cat_pict_id, 'yes');

		$query = $wpdb->prepare("SELECT id FROM ".TABLE_CATEGORIE_DANGER." WHERE LOWER(nom) = %s", strtolower(__('Rayonnements', 'evarisk')));
		$cat_id = $wpdb->get_var($query);
		$new_cat_pict_id = $evaPhoto->saveNewPicture(TABLE_CATEGORIE_DANGER, $cat_id, 'medias/images/Pictos/categorieDangers/rayonnement.png');
		$evaPhoto->setMainPhoto(TABLE_CATEGORIE_DANGER, $cat_id, $new_cat_pict_id, 'yes');

		$query = $wpdb->prepare("SELECT id FROM ".TABLE_CATEGORIE_DANGER." WHERE LOWER(nom) = %s", strtolower(__('Risques psychosociaux', 'evarisk')));
		$cat_id = $wpdb->get_var($query);
		$new_cat_pict_id = $evaPhoto->saveNewPicture(TABLE_CATEGORIE_DANGER, $cat_id, 'medias/images/Pictos/categorieDangers/rps.png');
		$evaPhoto->setMainPhoto(TABLE_CATEGORIE_DANGER, $cat_id, $new_cat_pict_id, 'yes');
	}
}

{/*	Version 73	*/
	$digirisk_db_version = 73;

	$digirisk_db_update[$digirisk_db_version][TABLE_DANGER][] = "UPDATE " . TABLE_DANGER . " SET nom = '" . __('Divers', 'evarisk') . ' ' . strtolower( __('Activit&eacute; physique', 'evarisk')) . "' WHERE LOWER(nom) = '" . __('Divers', 'evarisk') . ' ' . strtolower(__('Manutention manuelle', 'evarisk')) . "'";
	if ( $current_db_version <= $digirisk_db_version ) {	/*	Insert picture for danger categories	*/
		$query = $wpdb->prepare("SELECT id FROM ".TABLE_CATEGORIE_DANGER." WHERE LOWER(nom) = %s", __('Divers', 'evarisk') . ' ' . strtolower(__('Manutention manuelle', 'evarisk')));
		$cat_id = $wpdb->get_var($query);
		$new_cat_pict_id = $evaPhoto->saveNewPicture(TABLE_CATEGORIE_DANGER, $cat_id, 'medias/images/Pictos/categorieDangers/rps.png');
		$evaPhoto->setMainPhoto(TABLE_CATEGORIE_DANGER, $cat_id, $new_cat_pict_id, 'yes');
	}
}

{/*	Version 75	*/
	$digirisk_db_version = 75;

	$digirisk_db_update[$digirisk_db_version][TABLE_DANGER][] = "UPDATE " . TABLE_METHODE . " SET default_methode = 'yes' WHERE LOWER(nom) = '" . __('Evarisk', 'evarisk') . "'";
}

{/*	Version 76	*/
	$digirisk_db_version = 76;

	$digirisk_db_content_add[$digirisk_db_version][TABLE_GED_DOCUMENTS][] = array('status' => 'valid', 'parDefaut' => 'oui', 'dateCreation' => current_time('mysql', 0), 'idCreateur' => 1, 'dateSuppression' => null, 'idSuppresseur' => 0, 'id_element' => 0, 'table_element' => 'all', 'categorie' => 'listing_des_risques', 'nom' => 'modeleDefault_listing_risque.odt', 'chemin' => 'uploads/modeles/listingRisque/');
}

{/*	Version 79	*/
	$digirisk_db_version = 79;

	$digirisk_db_content_add[$digirisk_db_version][TABLE_GED_DOCUMENTS][] = array('status' => 'valid', 'parDefaut' => 'oui', 'dateCreation' => current_time('mysql', 0), 'idCreateur' => 1, 'dateSuppression' => null, 'idSuppresseur' => 0, 'id_element' => 0, 'table_element' => 'all', 'categorie' => 'fiche_exposition_penibilite', 'nom' => 'modeleDefault_fiche_penibilite.odt', 'chemin' => 'uploads/modeles/ficheDeRisques/');
}

{/* Version 82 */
	$digirisk_db_version = 82;

	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'add_follow_up', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'add_follow_up', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'update_follow_up', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'update_follow_up', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_TACHE, 'action' => 'delete_follow_up', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
	$digirisk_db_content_add[$digirisk_db_version][DIGI_DBT_ELEMENT_NOTIFICATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'table_element' => TABLE_ACTIVITE, 'action' => 'delete_follow_up', 'message_to_send' => $standard_message_to_send, 'message_subject' => $standard_message_subject_to_send);
}

{/*	Version 86	*/
	$digirisk_db_version = 86;

	$digirisk_db_content_add[$digirisk_db_version][TABLE_GED_DOCUMENTS][] = array('status' => 'valid', 'parDefaut' => 'oui', 'dateCreation' => current_time('mysql', 0), 'idCreateur' => 1, 'dateSuppression' => null, 'idSuppresseur' => 0, 'id_element' => 0, 'table_element' => 'all', 'categorie' => 'fiche_action', 'nom' => 'modele_default_fiche_action.odt', 'chemin' => 'uploads/modeles/planDActions/');
}

{/*	Version 90	*/
	$digirisk_db_version = 90;

	$digirisk_db_content_update[$digirisk_db_version][TABLE_PRECONISATION][] = array('datas' => array('nom' => 'Protection obligatoire du corps'), 'where' => array('nom' => 'rotection obligatoire du corps'));
	$digirisk_db_content_add[$digirisk_db_version][TABLE_CATEGORIE_PRECONISATION][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'nom' => '&Eacute;quipement de protection collective', 'dependance' => array(TABLE_PHOTO =>  array('medias/images/Pictos/preconisations/epc/preconisations_epc_s.png', 'yes', TABLE_CATEGORIE_PRECONISATION, '&Eacute;quipement de protection collective')));
}

{/*	Version 91	*/
	$digirisk_db_version = 91;
}
