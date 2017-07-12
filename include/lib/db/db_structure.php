<?php
/**
* Plugin database definition file.
*
*	This file contains the different definitions for the database structure. It will permit to check if database is correctly build
* @author Evarisk <dev@evarisk.com>
* @version 5.1.4.9
* @package digirisk
* @subpackage librairies-db
*/

$digirisk_update_way = array();
$digirisk_db_table = array();
$digirisk_db_table_list = array();
$digirisk_db_table_operation_list = array();
$digirisk_table_structure_change = array();
$digirisk_db_version = 0;

/*	Table structure definition	*/

/* Structure de la table `wp_eva__accident`	*/
$t = DIGI_DBT_ACCIDENT;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `creation_date` datetime default NULL,
  `last_update_date` datetime default NULL,
  `id_element` int(10) unsigned NOT NULL,
  `table_element` char(255) collate utf8_unicode_ci NOT NULL,
  `accident_caused_by_third_party` enum('oui','non') collate utf8_unicode_ci NOT NULL default 'non',
  `accident_make_other_victim` enum('oui','non') collate utf8_unicode_ci NOT NULL default 'non',
  `police_report` enum('oui','non') collate utf8_unicode_ci NOT NULL default 'non',
  `declaration_state` enum('in_progress','done') collate utf8_unicode_ci NOT NULL default 'in_progress',
  `declaration_step` int(2) unsigned NOT NULL,
  `accident_date` date default NULL,
  `accident_hour` time default NULL,
  `accident_title` char(255) collate utf8_unicode_ci NOT NULL,
  `police_report_writer` char(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `declaration_state` (`declaration_state`),
  KEY `declaration_step` (`declaration_step`),
  KEY `id_element` (`id_element`),
  KEY `table_element` (`table_element`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__accident_details`	*/
$t = DIGI_DBT_ACCIDENT_DETAILS;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `creation_date` datetime default NULL,
  `last_update_date` datetime default NULL,
  `accident_date` date default NULL,
  `accident_hour` time default NULL,
  `id_accident` bigint(20) unsigned NOT NULL default '0',
  `accident_victim_transported_at` char(65) collate utf8_unicode_ci NOT NULL,
  `accident_place` char(80) collate utf8_unicode_ci NOT NULL,
  `accident_consequence` char(80) collate utf8_unicode_ci NOT NULL,
  `accident_hurt_place` char(255) collate utf8_unicode_ci NOT NULL,
  `accident_hurt_nature` char(255) collate utf8_unicode_ci NOT NULL,
  `accident_victim_work_shedule` char(255) collate utf8_unicode_ci NOT NULL,
  `accident_details` text collate utf8_unicode_ci NOT NULL,
  `accident_declaration` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `id_accident` (`id_accident`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__accident_location`	*/
$t = DIGI_DBT_ACCIDENT_LOCATION;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `creation_date` datetime default NULL,
  `last_update_date` datetime default NULL,
  `id_accident` bigint(20) unsigned NOT NULL default '0',
  `id_location` int(20) unsigned NOT NULL,
  `location_type` enum('employer','establishment') collate utf8_unicode_ci NOT NULL default 'employer',
  `siret` char(15) collate utf8_unicode_ci default NULL,
  `siren` char(15) collate utf8_unicode_ci default NULL,
  `social_activity_number` char(15) collate utf8_unicode_ci default NULL,
  `adress_postal_code` char(15) collate utf8_unicode_ci default NULL,
  `adress_city` char(26) collate utf8_unicode_ci default NULL,
  `adress_line_1` char(32) collate utf8_unicode_ci default NULL,
  `adress_line_2` char(32) collate utf8_unicode_ci default NULL,
  `telephone` char(21) collate utf8_unicode_ci default NULL,
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `id_accident` (`id_accident`),
  KEY `id_location` (`id_location`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__accident_third_party`	*/
$t = DIGI_DBT_ACCIDENT_THIRD_PARTY;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `creation_date` datetime default NULL,
  `last_update_date` datetime default NULL,
  `id_accident` bigint(20) unsigned NOT NULL default '0',
  `id_user` bigint(20) unsigned NOT NULL,
  `third_party_type` enum('witness','third_party') collate utf8_unicode_ci NOT NULL default 'witness',
  `firstname` varchar(255) collate utf8_unicode_ci default NULL,
  `lastname` varchar(255) collate utf8_unicode_ci default NULL,
  `insurance_corporation` varchar(255) collate utf8_unicode_ci default NULL,
  `adress_line_1` varchar(255) collate utf8_unicode_ci default NULL,
  `adress_line_2` varchar(255) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `id_accident` (`id_accident`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__accident_victim`	*/
$t = DIGI_DBT_ACCIDENT_VICTIM;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `creation_date` datetime default NULL,
  `last_update_date` datetime default NULL,
  `id_accident` int(10) unsigned NOT NULL,
  `id_user` bigint(20) unsigned NOT NULL,
  `victim_seniority` date default NULL,
  `victim_meta` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `id_accident` (`id_accident`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__actions_correctives_actions`	*/
$t = TABLE_ACTIVITE;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment COMMENT 'Activity Identifier',
  `is_readable_from_external` enum('yes', 'no') collate utf8_unicode_ci NOT NULL default 'no',
  `id_tache` int(10) NOT NULL COMMENT 'Task which the activity depends on',
  `idCreateur` bigint(20) NOT NULL COMMENT 'The identifier of the user who create the action',
  `idResponsable` bigint(20) NOT NULL COMMENT 'The identifier of the user who is in charge of the action',
  `idSoldeur` int(10) NOT NULL COMMENT 'The identifier of the user who close the action',
  `idSoldeurChef` int(10) NOT NULL COMMENT 'The identifier of the user who close the action by closing parent task',
  `idPhotoAvant` int(10) unsigned NOT NULL,
  `idPhotoApres` int(10) unsigned NOT NULL,
  `planned_time` int(10) collate utf8_unicode_ci default NULL,
  `elapsed_time` int(10) collate utf8_unicode_ci default NULL,
  `nom` varchar(255) collate utf8_unicode_ci NOT NULL COMMENT 'Activity name',
  `nom_exportable_plan_action` enum('yes', 'no') collate utf8_unicode_ci NOT NULL default 'no',
  `description` text collate utf8_unicode_ci COMMENT 'Activity description',
  `description_exportable_plan_action` enum('yes', 'no') collate utf8_unicode_ci NOT NULL default 'no',
  `dateDebut` date default NULL COMMENT 'Activity start date',
  `dateFin` date default NULL COMMENT 'Activity finish date',
  `real_start_date` date default NULL,
  `real_end_date` date default NULL,
  `avancement` int(10) default NULL COMMENT 'Activity progression',
  `cout` float default NULL COMMENT 'Activity realisation cost',
  `cout_reel` float default NULL COMMENT 'Activity realisation cost',
  `lieu` varchar(255) collate utf8_unicode_ci default NULL COMMENT 'Activity place',
  `Status` enum('Valid','Moderated','Deleted','Aborded','Asked') collate utf8_unicode_ci NOT NULL default 'Valid' COMMENT 'Activity status',
  `firstInsert` datetime default NULL COMMENT 'Activity creation date',
  `ProgressionStatus` enum('notStarted','inProgress','Done','DoneByChief') collate utf8_unicode_ci NOT NULL default 'notStarted' COMMENT 'Activity status',
  `dateSolde` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `idSoldeurChef` (`idSoldeurChef`),
  KEY `idPhotoAvant` (`idPhotoAvant`),
  KEY `idPhotoApres` (`idPhotoApres`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table containing the activity (for corrective actions)';";

/** Structure de la table wp_eva__actions_correctives_suivi	*/
$t = TABLE_ACTIVITE_SUIVI;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  id int(10) unsigned NOT NULL auto_increment,
  follow_up_type enum('note','follow_up') collate utf8_unicode_ci NOT NULL default 'note',
  status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  date datetime NOT NULL,
  date_ajout datetime NOT NULL,
  date_modification datetime NOT NULL,
  export enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  id_parent int(10) NOT NULL,
  id_user bigint(20) NOT NULL,
  id_user_performer bigint(20) NOT NULL,
  id_element int(10) NOT NULL,
  table_element varchar(255) collate utf8_unicode_ci NOT NULL,
  commentaire longtext collate utf8_unicode_ci NOT NULL,
  elapsed_time int(10) collate utf8_unicode_ci NOT NULL,
  cost float default NULL,
  PRIMARY KEY (id),
  KEY status (status),
  KEY id_user (id_user),
  KEY id_element (id_element)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Allows to follow an action progress';";


/* Structure de la table `wp_eva__actions_correctives_tache`	*/
$t = TABLE_TACHE;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment COMMENT 'Task Identifier',
  `is_readable_from_external` enum('yes', 'no') collate utf8_unicode_ci NOT NULL default 'no',
  `nom` varchar(255) collate utf8_unicode_ci NOT NULL COMMENT 'Task name',
  `nom_exportable_plan_action` enum('yes', 'no') collate utf8_unicode_ci NOT NULL default 'no',
  `limiteGauche` int(10) NOT NULL COMMENT 'Left limit to simulate the tree',
  `limiteDroite` int(10) NOT NULL COMMENT 'Right limit to simulate the tree',
  `description` text collate utf8_unicode_ci COMMENT 'Task description',
  `description_exportable_plan_action` enum('yes', 'no') collate utf8_unicode_ci NOT NULL default 'no',
  `dateDebut` date default NULL COMMENT 'Task start date',
  `dateFin` date default NULL COMMENT 'Task finish date',
  `real_start_date` date default NULL,
  `real_end_date` date default NULL,
  `estimate_cost` float default NULL,
  `real_cost` float default NULL,
  `planned_time` int(10) collate utf8_unicode_ci default NULL,
  `elapsed_time` int(10) collate utf8_unicode_ci default NULL,
  `avancement` int(10) default NULL COMMENT 'Task progression',
  `cout` float default NULL COMMENT 'Task realisation cost',
  `idPhotoAvant` int(10) unsigned NOT NULL,
  `idPhotoApres` int(10) unsigned NOT NULL,
  `lieu` varchar(255) collate utf8_unicode_ci default NULL COMMENT 'Task place',
  `tableProvenance` varchar(255) collate utf8_unicode_ci default NULL COMMENT 'Table of the element that induces the task',
  `idProvenance` int(10) default NULL COMMENT 'Identifier of the element that induces the task',
  `idCreateur` bigint(20) NOT NULL COMMENT 'The identifier of the user who create the task',
  `idResponsable` bigint(20) NOT NULL COMMENT 'The identifier of the user who is in charge of the task',
  `idSoldeur` int(10) NOT NULL COMMENT 'The identifier of the user who close the task',
  `idSoldeurChef` int(10) NOT NULL COMMENT 'The identifier of the user who close the task by closing parent task',
  `Status` enum('Valid','Moderated','Deleted','Aborded','Asked') collate utf8_unicode_ci NOT NULL default 'Valid' COMMENT 'Task status',
  `firstInsert` datetime default NULL COMMENT 'Task creation date',
  `ProgressionStatus` enum('notStarted','inProgress','Done','DoneByChief') collate utf8_unicode_ci NOT NULL default 'notStarted' COMMENT 'Task status',
  `dateSolde` datetime NOT NULL,
  `hasPriority` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `efficacite` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `idSoldeurChef` (`idSoldeurChef`),
  KEY `hasPriority` (`hasPriority`),
  KEY `efficacite` (`efficacite`),
  KEY `idPhotoAvant` (`idPhotoAvant`),
  KEY `idPhotoApres` (`idPhotoApres`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table containing the task (for corrective actions)';";

/* Structure de la table `wp_eva__adresse`	*/
$t = TABLE_ADRESSE;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `ligne1` varchar(32) collate utf8_unicode_ci NOT NULL,
  `ligne2` varchar(32) collate utf8_unicode_ci NOT NULL,
  `ville` varchar(26) collate utf8_unicode_ci NOT NULL,
  `codePostal` varchar(5) collate utf8_unicode_ci NOT NULL,
  `longitude` float NOT NULL,
  `latitude` float NOT NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__avoir_operateur`	*/
$t = TABLE_AVOIR_OPERATEUR;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id_methode` int(10) NOT NULL,
  `operateur` varchar(20) collate utf8_unicode_ci NOT NULL,
  `ordre` int(10) NOT NULL,
  `date` datetime NOT NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`id_methode`,`operateur`,`ordre`,`date`),
  KEY `id_methode` (`id_methode`),
  KEY `operateur` (`operateur`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__avoir_variable`	*/
$t = TABLE_AVOIR_VARIABLE;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id_methode` int(10) NOT NULL,
  `id_variable` int(10) NOT NULL,
  `ordre` int(10) NOT NULL,
  `date` datetime NOT NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`id_methode`,`id_variable`,`ordre`,`date`),
  KEY `id_methode` (`id_methode`),
  KEY `id_variable` (`id_variable`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__categorie_danger`	*/
$t = TABLE_CATEGORIE_DANGER;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `nom` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci,
  `limiteGauche` int(16) NOT NULL,
  `limiteDroite` int(16) NOT NULL,
  `Status` varchar(255) NOT NULL default '',
  `methode_eva_defaut` int(11) NOT NULL default '0',
  `position` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__danger`	*/
$t = TABLE_DANGER;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `id_categorie` int(10) NOT NULL,
  `code_danger` int(10) default NULL,
  `nom` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  `choix_danger` varchar(255) NOT NULL default '',
  `methode_eva_defaut` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `nom` (`nom`),
  UNIQUE KEY `id_categorie_2` (`id_categorie`,`code_danger`),
  KEY `id_categorie` (`id_categorie`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__documentation`	*/
$t = $wpdb->prefix . digirisk_doc::prefix . '__documentation';
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `doc_id` int(11) unsigned NOT NULL auto_increment,
  `doc_page_name` varchar(255) NOT NULL,
  `doc_url` varchar(255) NOT NULL,
  `doc_html` text NOT NULL,
  `doc_creation_date` datetime NOT NULL,
  `doc_active` enum('active','deleted') default 'active',
  PRIMARY KEY  (`doc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

/* Structure de la table `wp_eva__element_modification`	*/
$t = DIGI_DBT_ELEMENT_MODIFICATION;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') NOT NULL default 'valid',
  `creation_date` datetime NOT NULL,
  `last_update_date` datetime NOT NULL,
  `id_user` bigint(20) unsigned NOT NULL,
  `id_action` int(10) unsigned NOT NULL,
  `id_element` int(10) unsigned NOT NULL,
  `table_element` char(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `old_content` longtext,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `table_element` (`table_element`),
  KEY `id_action` (`id_action`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

/* Structure de la table `wp_eva__element_notification`	*/
$t = DIGI_DBT_ELEMENT_NOTIFICATION;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') NOT NULL default 'valid',
  `creation_date` datetime NOT NULL,
  `last_update_date` datetime NOT NULL,
  `table_element` char(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `action` char(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `action_title` char(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `message_subject` char(255) NOT NULL,
  `message_to_send` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `table_element` (`table_element`),
  KEY `action` (`action`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

/* Structure de la table `wp_eva__equivalence_etalon`	*/
$t = TABLE_EQUIVALENCE_ETALON;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id_methode` int(10) NOT NULL,
  `id_valeur_etalon` int(10) NOT NULL,
  `date` datetime NOT NULL,
  `valeurMaxMethode` float default NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`id_methode`,`id_valeur_etalon`,`date`),
  KEY `id_methode` (`id_methode`),
  KEY `id_valeur_etalon` (`id_valeur_etalon`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__etalon`	*/
$t = TABLE_ETALON;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `min` int(10) NOT NULL,
  `max` int(10) NOT NULL,
  `pas` int(10) NOT NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`min`,`max`,`pas`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__ged_documents`	*/
$t = TABLE_GED_DOCUMENTS;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `parDefaut` enum('oui','non') collate utf8_unicode_ci NOT NULL default 'non' COMMENT 'Define if the document is the deault document for the category',
  `dateCreation` datetime NOT NULL,
  `idCreateur` int(10) unsigned NOT NULL,
  `dateSuppression` datetime NOT NULL,
  `idSuppresseur` int(10) unsigned NOT NULL,
  `id_element` int(10) unsigned NOT NULL,
  `table_element` char(128) collate utf8_unicode_ci NOT NULL,
  `categorie` varchar(255) collate utf8_unicode_ci NOT NULL,
  `nom` varchar(255) collate utf8_unicode_ci NOT NULL,
  `chemin` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `id_element` (`id_element`),
  KEY `table_element` (`table_element`),
  KEY `parDefaut` (`parDefaut`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Document management';";

/* Structure de la table `wp_eva__ged_documents`	*/
$t = TABLE_GED_DOCUMENTS_META;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
	id int(10) unsigned NOT NULL auto_increment,
	status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
	document_id int(10) unsigned NOT NULL COMMENT 'The related document identifier',
	meta_key varchar(255) collate utf8_unicode_ci NOT NULL,
	meta_value longtext,
	PRIMARY KEY (id),
  	KEY status (status),
  	KEY document_id (document_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Document content';";

/* Structure de la table `wp_eva__ged_documents_document_unique`	*/
$t = TABLE_DUER;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment COMMENT 'Single document identifier',
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid' COMMENT 'The document status',
  `element` char(255) collate utf8_unicode_ci NOT NULL COMMENT 'The element type the single document will be affected to',
  `elementId` int(10) unsigned NOT NULL COMMENT 'the id of the element associated with the single document',
  `id_model` int(10) unsigned NOT NULL default '1' COMMENT 'The model used to generate the DUER',
  `referenceDUER` varchar(64) collate utf8_unicode_ci NOT NULL default '' COMMENT 'Single document reference',
  `dateGenerationDUER` datetime default NULL COMMENT 'Single document creation date',
  `nomDUER` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Document name',
  `dateDebutAudit` date default NULL COMMENT 'Audit start date',
  `dateFinAudit` date default NULL COMMENT 'Audit end date',
  `nomSociete` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Society name',
  `telephoneFixe` char(30) collate utf8_unicode_ci default NULL COMMENT 'Society phone number',
  `telephonePortable` char(30) collate utf8_unicode_ci default NULL COMMENT 'Society cellular phone number',
  `telephoneFax` char(30) collate utf8_unicode_ci default NULL COMMENT 'Society fax number',
  `emetteurDUER` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Transmitter of the single document',
  `destinataireDUER` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Recipient of the single document',
  `revisionDUER` int(3) default NULL COMMENT 'Single document version',
  `planDUER` longtext collate utf8_unicode_ci COMMENT 'The single document scheme',
  `groupesUtilisateurs` longtext collate utf8_unicode_ci COMMENT 'A serialise array with the different users group',
  `groupesUtilisateursAffectes` longtext collate utf8_unicode_ci NOT NULL COMMENT 'A serialise array with the different users group affected to the current element',
  `risquesUnitaires` longtext collate utf8_unicode_ci COMMENT 'A serialise array with the different single risqs',
  `risquesParUnite` longtext collate utf8_unicode_ci COMMENT 'A serialise array with the different risqs by unit',
  `methodologieDUER` longtext collate utf8_unicode_ci COMMENT 'Methodology used to create the single document',
  `sourcesDUER` longtext collate utf8_unicode_ci COMMENT 'The different document used to create the single document',
  `alerteDUER` longtext collate utf8_unicode_ci COMMENT 'Warning about the single document',
  `conclusionDUER` longtext collate utf8_unicode_ci COMMENT 'conclusion about the sthe single document',
  `plan_d_action` longtext collate utf8_unicode_ci,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `referenceDUER` (`referenceDUER`),
  KEY `id_model` (`id_model`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table containing the different single document';";

/* Structure de la table `wp_eva__ged_documents_fiches`	*/
$t = TABLE_FP;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `creation_date` datetime default NULL,
  `document_type` char(30) collate utf8_unicode_ci NOT NULL,
  `id_model` int(10) unsigned NOT NULL default '1' COMMENT 'The model used to generate the document',
  `revision` int(3) default NULL COMMENT 'Document version',
  `affected_user` bigint(20) unsigned NOT NULL,
  `id_element` int(10) unsigned NOT NULL COMMENT 'The element''s id associated to the document',
  `table_element` char(255) collate utf8_unicode_ci NOT NULL COMMENT 'The element''s type associated to the document',
  `reference` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `name` varchar(128) collate utf8_unicode_ci default NULL,
  `description` varchar(255) collate utf8_unicode_ci default NULL,
  `adresse` varchar(255) collate utf8_unicode_ci default NULL,
  `telephone` varchar(255) collate utf8_unicode_ci default NULL,
  `defaultPicturePath` varchar(255) collate utf8_unicode_ci default NULL,
  `societyName` text collate utf8_unicode_ci,
  `document_final_dir` text collate utf8_unicode_ci,
  `users` longtext collate utf8_unicode_ci COMMENT 'A serialised array containing the different users',
  `userGroups` longtext collate utf8_unicode_ci COMMENT 'A serialised array containing the different users group',
  `evaluators` longtext collate utf8_unicode_ci COMMENT 'A serialised array containing the different users who were present during evaluation',
  `evaluatorsGroups` longtext collate utf8_unicode_ci COMMENT 'A serialised array containing the different evaluators group',
  `unitRisk` longtext collate utf8_unicode_ci COMMENT 'A serialised array containing the different risks',
  `recommandation` longtext collate utf8_unicode_ci,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `id_model` (`id_model`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table containing the different single document';";

/* Structure de la table `wp_eva__groupement`	*/
$t = TABLE_GROUPEMENT;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `typeGroupement` enum('none','employer') collate utf8_unicode_ci NOT NULL default 'none',
  `nom` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci,
  `id_adresse` int(10) default NULL,
  `telephoneGroupement` varchar(21) collate utf8_unicode_ci default NULL,
  `id_responsable` int(10) default NULL,
  `effectif` int(10) default NULL,
  `mainPhoto` varchar(255) collate utf8_unicode_ci default NULL,
  `limiteGauche` int(16) NOT NULL,
  `limiteDroite` int(16) NOT NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  `creation_date` datetime NOT NULL,
  `lastupdate_date` datetime NOT NULL,
  `siren` char(15) collate utf8_unicode_ci NOT NULL,
  `siret` char(15) collate utf8_unicode_ci NOT NULL,
  `social_activity_number` char(15) collate utf8_unicode_ci NOT NULL,
  creation_date_of_society datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `id_adresse` (`id_adresse`),
  KEY `id_responsable` (`id_responsable`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__liaison_photo_element`	*/
$t = TABLE_PHOTO_LIAISON;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `isMainPicture` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `idPhoto` int(10) unsigned NOT NULL,
  `idElement` int(10) unsigned NOT NULL,
  `tableElement` char(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `isMainPicture` (`isMainPicture`),
  KEY `idPhoto` (`idPhoto`),
  KEY `idElement` (`idElement`),
  KEY `status` (`status`),
  KEY `tableElement` (`tableElement`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Contain the different link between picture and element';";

/* Structure de la table `wp_eva__liaison_preconisation_element`	*/
$t = TABLE_LIAISON_PRECONISATION_ELEMENT;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `id_preconisation` int(10) NOT NULL,
  `efficacite` int(3) NOT NULL,
  `id_element` int(10) NOT NULL,
  `date_affectation` datetime NOT NULL,
  `date_update_affectation` datetime NOT NULL,
  `table_element` char(255) collate utf8_unicode_ci NOT NULL,
  `preconisation_type` char(255) collate utf8_unicode_ci NOT NULL,
  `commentaire` text collate utf8_unicode_ci,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `id_preconisation` (`id_preconisation`),
  KEY `id_element` (`id_element`),
  KEY `table_element` (`table_element`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__liaison_produit_element`	*/
$t = DIGI_DBT_LIAISON_PRODUIT_ELEMENT;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `date_affectation` datetime NOT NULL,
  `id_attributeur` bigint(20) NOT NULL,
  `date_desAffectation` datetime NOT NULL,
  `id_desAttributeur` bigint(20) NOT NULL,
  `id_product` bigint(20) NOT NULL,
  `id_element` int(10) NOT NULL,
  `table_element` char(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `id_product` (`id_product`),
  KEY `id_element` (`id_element`),
  KEY `table_element` (`table_element`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__liaison_tache_element`	*/
$t = TABLE_LIAISON_TACHE_ELEMENT;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `wasLinked` enum('before','after','demand') collate utf8_unicode_ci NOT NULL default 'before' COMMENT 'Allows to know if the action was link to the element before or after it realisation',
  `date` datetime NOT NULL,
  `id_tache` int(10) NOT NULL,
  `id_element` int(10) NOT NULL,
  `table_element` char(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniqueKey` (`wasLinked`,`id_tache`,`id_element`,`table_element`),
  KEY `status` (`status`),
  KEY `id_tache` (`id_tache`),
  KEY `id_element` (`id_element`),
  KEY `table_element` (`table_element`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__liaison_utilisateur_element`	*/
$t = TABLE_LIAISON_USER_ELEMENT;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `date_affectation` datetime NOT NULL,
  `id_attributeur` bigint(20) NOT NULL,
  `date_desAffectation` datetime NOT NULL,
  `id_desAttributeur` bigint(20) NOT NULL,
  `id_user` bigint(20) NOT NULL,
  `id_element` int(10) NOT NULL,
  `table_element` char(255) collate utf8_unicode_ci NOT NULL,
  `date_affectation_reelle` datetime NOT NULL,
  `date_desaffectation_reelle` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `id_user` (`id_user`),
  KEY `id_element` (`id_element`),
  KEY `table_element` (`table_element`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__liaison_utilisateur_groupe_element`	*/
$t = DIGI_DBT_LIAISON_USER_GROUP;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `date_affectation` datetime NOT NULL,
  `id_attributeur` bigint(20) NOT NULL,
  `date_desAffectation` datetime NOT NULL,
  `id_desAttributeur` bigint(20) NOT NULL,
  `id_group` int(10) NOT NULL,
  `id_element` int(10) NOT NULL,
  `table_element` char(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `id_group` (`id_group`),
  KEY `id_element` (`id_element`),
  KEY `table_element` (`table_element`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__liaison_utilisateur_notification`	*/
$t = DIGI_DBT_LIAISON_USER_NOTIFICATION_ELEMENT;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `date_affectation` datetime NOT NULL,
  `id_attributeur` bigint(20) NOT NULL,
  `date_desAffectation` datetime NOT NULL,
  `id_desAttributeur` bigint(20) NOT NULL,
  `id_user` bigint(20) NOT NULL,
  `id_notification` int(10) NOT NULL,
  `id_element` int(10) NOT NULL,
  `table_element` char(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `id_user` (`id_user`),
  KEY `id_notification` (`id_notification`),
  KEY `id_element` (`id_element`),
  KEY `table_element` (`table_element`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__message`	*/
$t = DIGI_DBT_MESSAGES;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted','archived') NOT NULL default 'valid',
  `send_status` enum('sent','resent') NOT NULL default 'sent',
  `creation_date` datetime NOT NULL,
  `last_dispatch_date` datetime NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `id_notification` int(10) NOT NULL,
  `id_element` int(10) NOT NULL,
  `table_element` char(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `title` char(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

/* Structure de la table `wp_eva__message_histo`	*/
$t = DIGI_DBT_HISTORIC;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') NOT NULL default 'valid',
  `creation_date` datetime NOT NULL,
  `message_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

/* Structure de la table `wp_eva__methode`	*/
$t = TABLE_METHODE;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  default_methode enum('yes', 'no') collate utf8_unicode_ci NOT NULL default 'no',
  `nom` varchar(255) collate utf8_unicode_ci NOT NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__operateur`	*/
$t = TABLE_OPERATEUR;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `symbole` varchar(20) collate utf8_unicode_ci NOT NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`symbole`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__permission_role`	*/
$t = DIGI_DBT_PERMISSION_ROLE;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `creation_date` datetime NOT NULL,
  `creation_user_id` bigint(20) NOT NULL,
  `deletion_date` datetime NOT NULL,
  `deletion_user_id` bigint(20) NOT NULL,
  `last_update_date` datetime NOT NULL,
  `role_internal_name` char(255) collate utf8_unicode_ci NOT NULL,
  `role_name` char(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__photo`	*/
$t = TABLE_PHOTO;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `photo` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__preconisation`	*/
$t = TABLE_PRECONISATION;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `id_categorie_preconisation` int(10) unsigned NOT NULL,
  `preconisation_type` enum('organisationnelles','collectives','individuelles') collate utf8_unicode_ci NOT NULL default 'organisationnelles',
  `creation_date` datetime default NULL,
  `nom` varchar(128) collate utf8_unicode_ci default NULL,
  `description` text collate utf8_unicode_ci,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `id_categorie_preconisation` (`id_categorie_preconisation`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table containing the different recommendation';";

/* Structure de la table `wp_eva__preconisation_categorie`	*/
$t = TABLE_CATEGORIE_PRECONISATION;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `creation_date` datetime default NULL,
  `impressionRecommandationCategorie` enum('textandpicture','textonly','pictureonly') collate utf8_unicode_ci NOT NULL default 'textandpicture' COMMENT 'Define the way to print the recommandation category into the different document',
  `tailleimpressionRecommandationCategorie` float(3,2) NOT NULL default '2.00' COMMENT 'Define the end size of the category picture into the different printed document',
  `impressionRecommandation` enum('textandpicture','textonly','pictureonly') collate utf8_unicode_ci NOT NULL default 'textandpicture' COMMENT 'Define the way to print the recommandation into the different document',
  `tailleimpressionRecommandation` float(3,2) NOT NULL default '0.80' COMMENT 'Define the end size of the recommandation picture into the different printed document',
  `nom` varchar(128) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `impressionRecommandationCategorie` (`impressionRecommandationCategorie`),
  KEY `impressionRecommandation` (`impressionRecommandation`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table containing the different recommendation categories';";

/* Structure de la table `wp_eva__produit`	*/
$t = DIGI_DBT_PRODUIT;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `creation_date` datetime default NULL,
  `last_update_date` datetime default NULL,
  `product_id` int(10) unsigned default NULL,
  `product_last_update_date` datetime default NULL,
  `category_id` char(255) collate utf8_unicode_ci NOT NULL,
  `category_name` char(255) collate utf8_unicode_ci NOT NULL,
  `product_name` char(255) collate utf8_unicode_ci NOT NULL,
  `product_description` longtext collate utf8_unicode_ci,
  `product_metadata` longtext collate utf8_unicode_ci,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__produit_document`	*/
$t = DIGI_DBT_PRODUIT_ATTACHEMENT;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `creation_date` datetime default NULL,
  `last_update_date` datetime default NULL,
  `product_attachment_id` int(10) unsigned default NULL,
  `product_id` int(10) unsigned default NULL,
  `product_attachment_last_update_date` datetime default NULL,
  `product_attachment_name` varchar(200) collate utf8_unicode_ci NOT NULL,
  `product_attachment_title` text collate utf8_unicode_ci NOT NULL,
  `product_attachment_mime_type` varchar(100) collate utf8_unicode_ci NOT NULL,
  `product_attachment_metadata` longtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `product_id` (`product_id`),
  KEY `product_attachment_id` (`product_attachment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__risque`	*/
$t = TABLE_RISQUE;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `id_danger` int(10) NOT NULL,
  `id_methode` int(10) NOT NULL,
  `id_element` int(10) NOT NULL,
  `nomTableElement` varchar(255) collate utf8_unicode_ci NOT NULL,
  `commentaire` text collate utf8_unicode_ci,
  `date` datetime NOT NULL,
  `dateDebutRisque` datetime NOT NULL,
  `dateFinRisque` datetime NOT NULL,
  `last_moved_date` datetime NOT NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  `risk_status` enum('open','closed') collate utf8_unicode_ci NOT NULL default 'open',
  PRIMARY KEY  (`id`),
  KEY `id_danger` (`id_danger`),
  KEY `id_methode` (`id_methode`),
  KEY `id_element` (`id_element`,`nomTableElement`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__risque_evaluation`	*/
$t = TABLE_AVOIR_VALEUR;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `id_evaluation` int(10) NOT NULL,
  `id_risque` int(10) NOT NULL,
  `id_variable` int(10) NOT NULL,
  `valeur` int(10) NOT NULL,
  `idEvaluateur` bigint(20) NOT NULL COMMENT 'Allow to know who is the person who change the risk',
  `date` datetime NOT NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  `commentaire` text collate utf8_unicode_ci,
  PRIMARY KEY (`id_risque`,`id_variable`,`date`),
  KEY `id_risque` (`id_risque`),
  KEY `id_variable` (`id_variable`),
  KEY `idEvaluateur` (`idEvaluateur`),
  KEY `id` (`id`),
  KEY `id_evaluation` (`id_evaluation`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__risque_histo`	*/
$t = TABLE_RISQUE_HISTO;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  id int(10) NOT NULL auto_increment,
  id_risque int(10) NOT NULL,
  date datetime NOT NULL,
  field varchar(255) collate utf8_unicode_ci NOT NULL,
  value longtext,
  PRIMARY KEY (id),
  KEY id_risque (id_risque)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__unite_travail`	*/
$t = TABLE_UNITE_TRAVAIL;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `nom` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci,
  `id_adresse` int(10) default NULL,
  `telephoneUnite` varchar(21) collate utf8_unicode_ci default NULL,
  `id_groupement` int(10) default NULL,
  `id_responsable` int(10) default NULL,
  `effectif` int(10) default NULL,
  `mainPhoto` varchar(255) collate utf8_unicode_ci default NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  `creation_date` datetime NOT NULL,
  `lastupdate_date` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id_adresse` (`id_adresse`),
  KEY `id_groupement` (`id_groupement`),
  KEY `id_responsable` (`id_responsable`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__utilisateurs_groupes`	*/
$t = DIGI_DBT_USER_GROUP;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `old_id` int(10) unsigned NOT NULL,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `group_type` enum('employee','evaluator') collate utf8_unicode_ci NOT NULL default 'employee',
  `last_update_date` datetime NOT NULL,
  `creation_date` datetime NOT NULL,
  `creation_user_id` bigint(20) unsigned NOT NULL COMMENT 'The user identifier that create the group',
  `deletion_date` datetime NOT NULL,
  `deletion_user_id` bigint(20) unsigned NOT NULL COMMENT 'The user identifier that delete the group',
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `creation_user_id` (`creation_user_id`),
  KEY `deletion_user_id` (`deletion_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__valeur_alternative`	*/
$t = TABLE_VALEUR_ALTERNATIVE;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id_variable` int(10) NOT NULL,
  `valeur` int(10) NOT NULL,
  `valeurAlternative` float NOT NULL,
  `date` date NOT NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  KEY `id_variable` (`id_variable`),
  KEY `id_variable_2` (`id_variable`,`valeur`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__valeur_etalon`	*/
$t = TABLE_VALEUR_ETALON;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `valeur` int(10) NOT NULL,
  `niveauSeuil` int(10) default NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`valeur`),
  UNIQUE KEY `niveauSeuil` (`niveauSeuil`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__variable`	*/
$t = TABLE_VARIABLE;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `nom` varchar(255) collate utf8_unicode_ci NOT NULL,
  `min` float unsigned NOT NULL,
  `max` float unsigned NOT NULL,
  `annotation` text collate utf8_unicode_ci,
  `affichageVar` ENUM('slide', 'checkbox') collate utf8_unicode_ci NOT NULL default 'slide',
  `questionVar` text NOT NULL default '',
  `questionTitre` varchar(255) NOT NULL default '',
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__veille_accepte_reponse`	*/
$t = TABLE_ACCEPTE_REPONSE;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id_question` int(10) NOT NULL,
  `id_reponse` int(10) NOT NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`id_question`,`id_reponse`),
  KEY `id_question` (`id_question`),
  KEY `id_reponse` (`id_reponse`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__veille_concerne_par_texte_referenciel`	*/
$t = TABLE_CONCERNE_PAR_TEXTE_REFERENCIEL;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id_texte_referenciel` int(10) NOT NULL,
  `id_element` int(10) NOT NULL,
  `nomTableElement` varchar(255) collate utf8_unicode_ci NOT NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`id_texte_referenciel`,`id_element`,`nomTableElement`),
  KEY `id_texte_referenciel` (`id_texte_referenciel`),
  KEY `id_element` (`id_element`,`nomTableElement`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__veille_correspond_texte_referenciel`	*/
$t = TABLE_CORRESPOND_TEXTE_REFERENCIEL;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id_texte_referenciel` int(10) NOT NULL,
  `id_groupe_question` int(10) NOT NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`id_texte_referenciel`,`id_groupe_question`),
  KEY `id_texte_referenciel` (`id_texte_referenciel`),
  KEY `id_groupe_question` (`id_groupe_question`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__veille_groupe_question`	*/
$t = TABLE_GROUPE_QUESTION;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `nom` varchar(255) collate utf8_unicode_ci NOT NULL,
  `code` varchar(255) collate utf8_unicode_ci NOT NULL,
  `extraitTexte` text collate utf8_unicode_ci,
  `limiteGauche` int(16) NOT NULL,
  `limiteDroite` int(16) NOT NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__veille_possede_question`	*/
$t = TABLE_POSSEDE_QUESTION;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id_groupe_question` int(10) NOT NULL,
  `id_question` int(10) NOT NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`id_groupe_question`,`id_question`),
  KEY `id_groupe_question` (`id_groupe_question`),
  KEY `id_question` (`id_question`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__veille_question`	*/
$t = TABLE_QUESTION;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `code` varchar(255) collate utf8_unicode_ci NOT NULL,
  `enonce` text collate utf8_unicode_ci NOT NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__veille_reponse`	*/
$t = TABLE_REPONSE;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `nom` varchar(255) collate utf8_unicode_ci NOT NULL,
  `min` int(10) default NULL,
  `max` int(10) default NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__veille_reponse_question`	*/
$t = TABLE_REPONSE_QUESTION;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id_question` int(10) NOT NULL,
  `id_element` int(10) NOT NULL,
  `nomTableElement` varchar(255) collate utf8_unicode_ci NOT NULL,
  `id_reponse` int(10) NOT NULL,
  `date` date NOT NULL,
  `limiteValidite` date default NULL,
  `valeur` int(10) default NULL,
  `observation` text collate utf8_unicode_ci,
  `Status` enum('Valid','Moderated','Deleted','archived') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`id_question`,`id_element`,`nomTableElement`,`date`),
  KEY `id_question` (`id_question`),
  KEY `id_element` (`id_element`,`nomTableElement`),
  KEY `id_reponse` (`id_reponse`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/* Structure de la table `wp_eva__veille_texte_referenciel`	*/
$t = TABLE_TEXTE_REFERENCIEL;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `rubrique` varchar(255) collate utf8_unicode_ci NOT NULL COMMENT 'The text name',
  `datePremiereRatification` date NOT NULL,
  `dateDerniereModification` date default NULL,
  `objet` text collate utf8_unicode_ci NOT NULL,
  `objetCout` text collate utf8_unicode_ci NOT NULL,
  `texteSousIntro` text collate utf8_unicode_ci NOT NULL,
  `adresseTexte` varchar(255) collate utf8_unicode_ci NOT NULL,
  `affectable` tinyint(1) NOT NULL default '0' COMMENT 'Is the text assignable',
  `analysable` tinyint(1) default NULL,
  `loi` tinyint(1) default NULL,
  `Status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `rubrique` (`rubrique`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";


/* Structure de la table `wp_eva__liaison_formulaire`	*/
$t = TABLE_FORMULAIRE_LIAISON;
$digirisk_db_table[$t] = "
CREATE TABLE {$t} (
	id int(10) unsigned NOT NULL auto_increment,
	status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
	date_started datetime default NULL,
	date_closed datetime default NULL,
	user int(10) unsigned NOT NULL,
	user_closed int(10) unsigned NOT NULL,
	idFormulaire int(10) unsigned NOT NULL,
	survey_id int(10) unsigned NOT NULL,
	idElement int(10) unsigned NOT NULL,
	tableElement char(255) collate utf8_unicode_ci NOT NULL,
	state char(255) collate utf8_unicode_ci NOT NULL,
PRIMARY KEY  (id),
KEY idFormulaire (idFormulaire),
KEY survey_id (survey_id),
KEY idElement (idElement),
KEY status (status),
KEY state (state),
KEY tableElement (tableElement)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Contain the different survey associated to digirisk element';";


/*	Start the different creation and update plan	*/

{/*	Version 0	*/
	$digirisk_db_version = 0;
	$digirisk_update_way[$digirisk_db_version] = 'creation';

	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array(TABLE_PHOTO, TABLE_ADRESSE, TABLE_GROUPEMENT, TABLE_UNITE_TRAVAIL, TABLE_CATEGORIE_DANGER, TABLE_DANGER, TABLE_OPERATEUR, TABLE_VARIABLE, TABLE_VALEUR_ALTERNATIVE, TABLE_METHODE, TABLE_AVOIR_VARIABLE, TABLE_AVOIR_OPERATEUR, TABLE_RISQUE, TABLE_AVOIR_VALEUR, TABLE_ETALON, TABLE_VALEUR_ETALON, TABLE_EQUIVALENCE_ETALON, TABLE_TEXTE_REFERENCIEL, TABLE_GROUPE_QUESTION, TABLE_CORRESPOND_TEXTE_REFERENCIEL, TABLE_QUESTION, TABLE_POSSEDE_QUESTION, TABLE_REPONSE, TABLE_ACCEPTE_REPONSE, TABLE_REPONSE_QUESTION, TABLE_CONCERNE_PAR_TEXTE_REFERENCIEL);

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_PHOTO, TABLE_ADRESSE, TABLE_GROUPEMENT, TABLE_UNITE_TRAVAIL, TABLE_CATEGORIE_DANGER, TABLE_DANGER, TABLE_OPERATEUR, TABLE_VARIABLE, TABLE_VALEUR_ALTERNATIVE, TABLE_METHODE, TABLE_AVOIR_VARIABLE, TABLE_AVOIR_OPERATEUR, TABLE_RISQUE, TABLE_AVOIR_VALEUR, TABLE_ETALON, TABLE_VALEUR_ETALON, TABLE_EQUIVALENCE_ETALON, TABLE_TEXTE_REFERENCIEL, TABLE_GROUPE_QUESTION, TABLE_CORRESPOND_TEXTE_REFERENCIEL, TABLE_QUESTION, TABLE_POSSEDE_QUESTION, TABLE_REPONSE, TABLE_ACCEPTE_REPONSE, TABLE_REPONSE_QUESTION, TABLE_CONCERNE_PAR_TEXTE_REFERENCIEL);
}

{/*	Version 3	*/
	$digirisk_db_version = 3;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_TEXTE_REFERENCIEL] = array('affectable');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_TEXTE_REFERENCIEL);
}

{/*	Version 5	*/
	$digirisk_db_version = 5;
	$digirisk_update_way[$digirisk_db_version] = 'creation';

	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array(TABLE_TACHE, TABLE_ACTIVITE);

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_TACHE, TABLE_ACTIVITE);
}

{/*	Version 7	*/
	$digirisk_db_version = 7;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_CHANGE'][TABLE_TACHE] = array(array('field' => 'dateFin', 'original_name' => 'dateFIN'));

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_TACHE);
}
{/*	Version 8	*/
	$digirisk_db_version = 8;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_TACHE] = array('firstInsert', 'cout');
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_ACTIVITE] = array('firstInsert', 'cout');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_TACHE, TABLE_ACTIVITE);
}
{/*	Version 9	*/
	$digirisk_db_version = 9;
	$digirisk_update_way[$digirisk_db_version] = 'creation';

	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array(TABLE_DUER);

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_DUER);
}

{/*	Version 15	*/
	$digirisk_db_version = 15;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_DROP'][TABLE_PHOTO] = array('Status');
	$digirisk_db_table_operation_list[$digirisk_db_version]['DROP_INDEX'][TABLE_PHOTO] = array('idDestination');
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_PHOTO] = array('status');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_PHOTO);

	/*	Special changes on table structure	*/
	$i = 0;
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_PHOTO][$i]['MAIN_ACTION'] = 'ALTER';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_PHOTO][$i]['ACTION_CONTENT'] = 'Status';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_PHOTO][$i]['ACTION'] = 'DROP';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_PHOTO][$i]['MAIN_ACTION'] = 'ALTER';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_PHOTO][$i]['ACTION_CONTENT'] = 'idDestination';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_PHOTO][$i]['ACTION'] = 'DROP INDEX';
}

{/*	Version 17	*/
	$digirisk_db_version = 17;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_CHANGE'][TABLE_DUER] = array(array('field' => 'groupesUtilisateurs', 'original_name' => 'codeHtmlGroupesUtilisateurs'), array('field' => 'risquesUnitaires', 'original_name' => 'codeHtmlRisqueUnitaire'), array('field' => 'risquesParUnite', 'original_name' => 'codeHtmlRisquesParUnite'));
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_DUER] = array('groupesUtilisateursAffectes', 'status');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_DUER);
}
{/*	Version 18	*/
	$digirisk_db_version = 18;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME'][] = array('old_name' => TABLE_AC_TACHE, 'name' => TABLE_TACHE);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME'][] = array('old_name' => TABLE_AC_ACTIVITE, 'name' => TABLE_ACTIVITE);

	// $digirisk_db_table_list[$digirisk_db_version] = array(TABLE_AC_TACHE, TABLE_AC_ACTIVITE);

	/*	Special changes on table structure	*/
	$i = 0;
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_AC_TACHE][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_AC_TACHE][$i]['ACTION_CONTENT'] = TABLE_TACHE;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_AC_TACHE][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_AC_ACTIVITE][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_AC_ACTIVITE][$i]['ACTION_CONTENT'] = TABLE_ACTIVITE;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_AC_ACTIVITE][$i]['ACTION'] = 'TO';
}
{/*	Version 19	*/
	$digirisk_db_version = 19;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_TACHE] = array('idCreateur', 'idResponsable', 'idSoldeur');
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_ACTIVITE] = array('idCreateur', 'idResponsable', 'idSoldeur');
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_AVOIR_VALEUR] = array('idEvaluateur');
	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_INDEX'][TABLE_AVOIR_VALEUR] = array('idEvaluateur');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_TACHE, TABLE_ACTIVITE, TABLE_AVOIR_VALEUR, TABLE_AVOIR_VALEUR_OLD);

	/*	Special changes on table structure	*/
	$i = 0;
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_AVOIR_VALEUR_OLD][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_AVOIR_VALEUR_OLD][$i]['ACTION_CONTENT'] = TABLE_AVOIR_VALEUR;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_AVOIR_VALEUR_OLD][$i]['ACTION'] = 'TO';
}
{/*	Version 20	*/
	$digirisk_db_version = 20;
	$digirisk_update_way[$digirisk_db_version] = 'creation';

	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array(TABLE_LIAISON_TACHE_ELEMENT);

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_LIAISON_TACHE_ELEMENT);
}
{/*	Version 21	*/
	$digirisk_db_version = 21;
	$digirisk_update_way[$digirisk_db_version] = 'multiple';

	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array(TABLE_LIAISON_USER_ELEMENT, TABLE_ACTIVITE_SUIVI);
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_TACHE] = array('idSoldeurChef', 'dateSolde', 'ProgressionStatus');
	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_INDEX'][TABLE_TACHE] = array('idSoldeurChef');
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_ACTIVITE] = array('idSoldeurChef', 'dateSolde', 'ProgressionStatus');
	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_INDEX'][TABLE_ACTIVITE] = array('idSoldeurChef');
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_LIAISON_TACHE_ELEMENT] = array('wasLinked');
	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_INDEX'][TABLE_LIAISON_TACHE_ELEMENT] = array('wasLinked', 'uniqueKey');
	$digirisk_db_table_operation_list[$digirisk_db_version]['DROP_INDEX'][TABLE_AVOIR_VALEUR] = array('id_risque_2');
	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_INDEX'][TABLE_AVOIR_VALEUR] = array('PRIMARY', 'id', 'id_evaluation');
	/* Unique key could not be an unique index	*/
	// $digirisk_db_table_operation_list[$digirisk_db_version]['ADD_INDEX'][TABLE_AVOIR_VALEUR] = array('unique_key_evaluation', 'id', 'id_evaluation');
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_AVOIR_VALEUR] = array('id', 'id_evaluation');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_LIAISON_USER_ELEMENT, TABLE_ACTIVITE_SUIVI, TABLE_TACHE, TABLE_ACTIVITE, TABLE_LIAISON_TACHE_ELEMENT);

	/*	Special changes on table structure	*/
	$i = 0;
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_AVOIR_VALEUR][$i]['MAIN_ACTION'] = 'ALTER';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_AVOIR_VALEUR][$i]['ACTION_CONTENT'] = 'id_risque_2';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_AVOIR_VALEUR][$i]['ACTION'] = 'DROP INDEX';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_AVOIR_VALEUR][$i]['MAIN_ACTION'] = 'ALTER';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_AVOIR_VALEUR][$i]['ACTION_CONTENT'] = '(id_risque, id_variable, date)';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_AVOIR_VALEUR][$i]['ACTION'] = 'ADD UNIQUE';
}

{/*	Version 24	*/
	$digirisk_db_version = 24;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_ACTIVITE] = array('idPhotoAvant', 'idPhotoApres');
	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_INDEX'][TABLE_ACTIVITE] = array('idPhotoAvant', 'idPhotoApres');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_ACTIVITE);
}
{/*	Version 25	*/
	$digirisk_db_version = 25;
	$digirisk_update_way[$digirisk_db_version] = 'datas';
}
{/*	Version 26	*/
	$digirisk_db_version = 26;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_DUER] = array('id_model');
	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_INDEX'][TABLE_DUER] = array('id_model');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_DUER);
}
{/*	Version 27	*/
	$digirisk_db_version = 27;
	$digirisk_update_way[$digirisk_db_version] = 'creation';

	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array(TABLE_GED_DOCUMENTS);

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_GED_DOCUMENTS);
}

{/*	Version 29	*/
	$digirisk_db_version = 29;
	$digirisk_update_way[$digirisk_db_version] = 'multiple';

	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array(TABLE_PHOTO_LIAISON);
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_DROP'][TABLE_PHOTO] = array('isMainPicture', 'idDestination', 'tableDestination');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_PHOTO_LIAISON);
}

{/*	Version 30	*/
	$digirisk_db_version = 30;
	$digirisk_update_way[$digirisk_db_version] = 'datas';
}
{/*	Version 31	*/
	$digirisk_db_version = 31;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_TACHE] = array('hasPriority');
	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_INDEX'][TABLE_TACHE] = array('hasPriority');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_TACHE);
}

{/*	Version 33	*/
	$digirisk_db_version = 33;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_CHANGE'][TABLE_TACHE] = array(array('field' => 'ProgressionStatus', 'type' => "enum('notStarted','inProgress','Done','DoneByChief')"));
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_CHANGE'][TABLE_ACTIVITE] = array(array('field' => 'ProgressionStatus', 'type' => "enum('notStarted','inProgress','Done','DoneByChief')"));

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_TACHE, TABLE_ACTIVITE);
}
{/*	Version 34	*/
	$digirisk_db_version = 34;
	$digirisk_update_way[$digirisk_db_version] = 'update';


}
{/*	Version 35	*/
	$digirisk_db_version = 35;
	$digirisk_update_way[$digirisk_db_version] = 'update';
}

{/*	Version 37	*/
	$digirisk_db_version = 37;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_GED_DOCUMENTS] = array('parDefaut');
	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_INDEX'][TABLE_GED_DOCUMENTS] = array('parDefaut');
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME'][] = array('old_name' => TABLE_DUER_OLD, 'name' => TABLE_DUER);

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_GED_DOCUMENTS);

	/*	Special changes on table structure	*/
	$i = 0;
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_DUER_OLD][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_DUER_OLD][$i]['ACTION_CONTENT'] = TABLE_DUER;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_DUER_OLD][$i]['ACTION'] = 'TO';
}
{/*	Version 38	*/
	$digirisk_db_version = 38;
	$digirisk_update_way[$digirisk_db_version] = 'creation';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_CHANGE'][TABLE_DUER] = array(array('field' => 'planDUER', 'type' => "longtext"), array('field' => 'groupesUtilisateurs', 'type' => "longtext"), array('field' => 'groupesUtilisateursAffectes', 'type' => "longtext"), array('field' => 'risquesUnitaires', 'type' => "longtext"), array('field' => 'risquesParUnite', 'type' => "longtext"), array('field' => 'methodologieDUER', 'type' => "longtext"), array('field' => 'sourcesDUER', 'type' => "longtext"), array('field' => 'alerteDUER', 'type' => "longtext"), array('field' => 'conclusionDUER', 'type' => "longtext"));
	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array(TABLE_CATEGORIE_PRECONISATION, TABLE_PRECONISATION, TABLE_LIAISON_PRECONISATION_ELEMENT);

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_DUER, TABLE_CATEGORIE_PRECONISATION, TABLE_PRECONISATION, TABLE_LIAISON_PRECONISATION_ELEMENT);
}
{/*	Version 39	*/
	$digirisk_db_version = 39;
	$digirisk_update_way[$digirisk_db_version] = 'multiple';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_CATEGORIE_PRECONISATION] = array('impressionRecommandationCategorie', 'tailleimpressionRecommandationCategorie', 'impressionRecommandation', 'tailleimpressionRecommandation');
	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_INDEX'][TABLE_CATEGORIE_PRECONISATION] = array('impressionRecommandationCategorie', 'impressionRecommandation');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_CATEGORIE_PRECONISATION);
}

{/*	Version 42	*/
	$digirisk_db_version = 42;
	$digirisk_update_way[$digirisk_db_version] = 'update';

}

{/*	Version 44	*/
	$digirisk_db_version = 44;
	$digirisk_update_way[$digirisk_db_version] = 'multiple';

	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array(DIGI_DBT_USER_GROUP, DIGI_DBT_LIAISON_USER_GROUP);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_UTILISE_EPI, 'name' => TRASH_DIGI_DBT_UTILISE_EPI);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_EPI, 'name' => TRASH_DIGI_DBT_EPI);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_LIAISON_USER_EVALUATION, 'name' => TRASH_DIGI_DBT_LIAISON_USER_EVALUATION);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_EAV_USER_DATETIME, 'name' => TRASH_DIGI_DBT_EAV_USER_DATETIME);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_EAV_USER_DECIMAL, 'name' => TRASH_DIGI_DBT_EAV_USER_DECIMAL);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_EAV_USER_INT, 'name' => TABLE_TABLE_EAV_USER_INT);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_EAV_USER_TEXT, 'name' => TRASH_DIGI_DBT_EAV_USER_TEXT);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_EAV_USER_VARCHAR, 'name' => TRASH_DIGI_DBT_EAV_USER_VARCHAR);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_EVA_ROLES, 'name' => TRASH_DIGI_DBT_EVA_ROLES);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_EVA_USER_GROUP_ROLES_DETAILS, 'name' => TRASH_DIGI_DBT_EVA_USER_GROUP_ROLES_DETAILS);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_ENTITY, 'name' => TRASH_DIGI_DBT_ENTITY);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_ENTITY_ATTRIBUTE_LINK, 'name' => TRASH_DIGI_DBT_ENTITY_ATTRIBUTE_LINK);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_ATTRIBUTE_SET, 'name' => TRASH_DIGI_DBT_ATTRIBUTE_SET);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_ATTRIBUTE, 'name' => TRASH_DIGI_DBT_ATTRIBUTE);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_ATTRIBUTE_GROUP, 'name' => TRASH_DIGI_DBT_ATTRIBUTE_GROUP);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_ATTRIBUTE_OPTION, 'name' => TRASH_DIGI_DBT_ATTRIBUTE_OPTION);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_ATTRIBUTE_OPTION_VALUE, 'name' => TRASH_DIGI_DBT_ATTRIBUTE_OPTION_VALUE);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_PERSONNE, 'name' => TRASH_DIGI_DBT_PERSONNE);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_VERSION, 'name' => TRASH_DIGI_DBT_VERSION);
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => TABLE_OPTION, 'name' => TRASH_DIGI_DBT_OPTION);

	$digirisk_db_table_list[$digirisk_db_version] = array(DIGI_DBT_USER_GROUP, DIGI_DBT_LIAISON_USER_GROUP);

	/*	Special changes on table structure	*/
	$i = 0;
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EPI][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EPI][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_EPI;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EPI][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_UTILISE_EPI][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_UTILISE_EPI][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_UTILISE_EPI;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_UTILISE_EPI][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_LIAISON_USER_EVALUATION][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_LIAISON_USER_EVALUATION][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_LIAISON_USER_EVALUATION;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_LIAISON_USER_EVALUATION][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EAV_USER_DATETIME][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EAV_USER_DATETIME][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_EAV_USER_DATETIME;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EAV_USER_DATETIME][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EAV_USER_DECIMAL][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EAV_USER_DECIMAL][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_EAV_USER_DECIMAL;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EAV_USER_DECIMAL][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EAV_USER_INT][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EAV_USER_INT][$i]['ACTION_CONTENT'] = TABLE_TABLE_EAV_USER_INT;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EAV_USER_INT][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EAV_USER_TEXT][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EAV_USER_TEXT][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_EAV_USER_TEXT;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EAV_USER_TEXT][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EAV_USER_VARCHAR][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EAV_USER_VARCHAR][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_EAV_USER_VARCHAR;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EAV_USER_VARCHAR][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EVA_ROLES][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EVA_ROLES][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_EVA_ROLES;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EVA_ROLES][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EVA_USER_GROUP_ROLES_DETAILS][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EVA_USER_GROUP_ROLES_DETAILS][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_EVA_USER_GROUP_ROLES_DETAILS;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_EVA_USER_GROUP_ROLES_DETAILS][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ENTITY][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ENTITY][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_ENTITY;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ENTITY][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ENTITY_ATTRIBUTE_LINK][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ENTITY_ATTRIBUTE_LINK][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_ENTITY_ATTRIBUTE_LINK;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ENTITY_ATTRIBUTE_LINK][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ATTRIBUTE_SET][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ATTRIBUTE_SET][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_ATTRIBUTE_SET;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ATTRIBUTE_SET][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ATTRIBUTE][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ATTRIBUTE][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_ATTRIBUTE;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ATTRIBUTE][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ATTRIBUTE_GROUP][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ATTRIBUTE_GROUP][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_ATTRIBUTE_GROUP;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ATTRIBUTE_GROUP][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ATTRIBUTE_OPTION][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ATTRIBUTE_OPTION][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_ATTRIBUTE_OPTION;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ATTRIBUTE_OPTION][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ATTRIBUTE_OPTION_VALUE][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ATTRIBUTE_OPTION_VALUE][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_ATTRIBUTE_OPTION_VALUE;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_ATTRIBUTE_OPTION_VALUE][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_PERSONNE][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_PERSONNE][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_PERSONNE;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_PERSONNE][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_VERSION][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_VERSION][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_VERSION;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_VERSION][$i]['ACTION'] = 'TO';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_OPTION][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_OPTION][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_OPTION;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_OPTION][$i]['ACTION'] = 'TO';
}
{/*	Version 45	*/
	$digirisk_db_version = 45;
	$digirisk_update_way[$digirisk_db_version] = 'multiple';

	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array(DIGI_DBT_PRODUIT, DIGI_DBT_LIAISON_PRODUIT_ELEMENT);
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_DUER] = array('plan_d_action');
	$digirisk_db_table_operation_list[$digirisk_db_version]['DROP_INDEX'][TABLE_LIAISON_USER_ELEMENT] = array('uniqueKey');

	$digirisk_db_table_list[$digirisk_db_version] = array(DIGI_DBT_PRODUIT, DIGI_DBT_LIAISON_PRODUIT_ELEMENT, TABLE_DUER);

	/*	Special changes on table structure	*/
	$i = 0;
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_LIAISON_USER_ELEMENT][$i]['MAIN_ACTION'] = 'ALTER';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_LIAISON_USER_ELEMENT][$i]['ACTION_CONTENT'] = 'uniqueKey';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_LIAISON_USER_ELEMENT][$i]['ACTION'] = 'DROP INDEX';
}

{/*	Version 47	*/
	$digirisk_db_version = 47;
	$digirisk_update_way[$digirisk_db_version] = 'creation';

	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array(DIGI_DBT_PERMISSION_ROLE);

	$digirisk_db_table_list[$digirisk_db_version] = array(DIGI_DBT_PERMISSION_ROLE);
}
{/*	Version 48	*/
	$digirisk_db_version = 48;
	$digirisk_update_way[$digirisk_db_version] = 'datas';
}
{/*	Version 49	*/
	$digirisk_db_version = 49;
	$digirisk_update_way[$digirisk_db_version] = 'datas';
}

{/*	Version 50	*/
	$digirisk_db_version = 50;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['DROP_INDEX'][TABLE_GROUPEMENT] = array('nom');
	$digirisk_db_table_operation_list[$digirisk_db_version]['DROP_INDEX'][TABLE_UNITE_TRAVAIL] = array('nom');
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_GROUPEMENT] = array('creation_date', 'lastupdate_date');
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_UNITE_TRAVAIL] = array('creation_date', 'lastupdate_date');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_GROUPEMENT, TABLE_UNITE_TRAVAIL);

	/*	Special changes on table structure	*/
	$i = 0;
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_GROUPEMENT][$i]['MAIN_ACTION'] = 'ALTER';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_GROUPEMENT][$i]['ACTION_CONTENT'] = 'nom';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_GROUPEMENT][$i]['ACTION'] = 'DROP INDEX';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_UNITE_TRAVAIL][$i]['MAIN_ACTION'] = 'ALTER';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_UNITE_TRAVAIL][$i]['ACTION_CONTENT'] = 'nom';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_UNITE_TRAVAIL][$i]['ACTION'] = 'DROP INDEX';
}
{/*	Version 51	*/
	$digirisk_db_version = 51;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME'][] = array('old_name' => TABLE_FP_OLD, 'name' => TABLE_FP);

	/*	Special changes on table structure	*/
	$i = 0;
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_FP_OLD][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_FP_OLD][$i]['ACTION_CONTENT'] = TABLE_FP_OLD;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_FP_OLD][$i]['ACTION'] = 'TO';
}
{/*	Version 52	*/
	$digirisk_db_version = 52;
	$digirisk_update_way[$digirisk_db_version] = 'datas';
}
{/*	Version 53	*/
	$digirisk_db_version = 53;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_FP] = array('description', 'adresse', 'telephone');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_FP);
}

{/*	Version 55	*/
	$digirisk_db_version = 55;
	$digirisk_update_way[$digirisk_db_version] = 'datas';
}
{/*	Version 56	*/
	$digirisk_db_version = 56;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_CHANGE'][TABLE_DUER] = array(array('field' => 'telephoneFixe', 'type' => "char(30)"), array('field' => 'telephonePortable', 'type' => "char(30)"), array('field' => 'telephoneFax', 'type' => "char(30)"));

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_DUER);
}
{/*	Version 57	*/
	$digirisk_db_version = 57;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][DIGI_DBT_PRODUIT] = array('product_description', 'product_metadata');
	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array(DIGI_DBT_PRODUIT_ATTACHEMENT);

	$digirisk_db_table_list[$digirisk_db_version] = array(DIGI_DBT_PRODUIT, DIGI_DBT_PRODUIT_ATTACHEMENT);
}
{/*	Version 58	*/
	$digirisk_db_version = 58;
	$digirisk_update_way[$digirisk_db_version] = 'multiple';

	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array(DIGI_DBT_ACCIDENT, DIGI_DBT_ACCIDENT_VICTIM, DIGI_DBT_ACCIDENT_DETAILS, DIGI_DBT_ACCIDENT_THIRD_PARTY, DIGI_DBT_ACCIDENT_LOCATION);
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_GROUPEMENT] = array('typeGroupement', 'siren', 'siret', 'social_activity_number');

	$digirisk_db_table_list[$digirisk_db_version] = array(DIGI_DBT_ACCIDENT, DIGI_DBT_ACCIDENT_VICTIM, DIGI_DBT_ACCIDENT_DETAILS, DIGI_DBT_ACCIDENT_THIRD_PARTY, DIGI_DBT_ACCIDENT_LOCATION, TABLE_GROUPEMENT);
}
{/*	Version 59	*/
	$digirisk_db_version = 59;
	$digirisk_update_way[$digirisk_db_version] = 'creation';

	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array($wpdb->prefix.digirisk_doc::prefix . '__documentation');

	$digirisk_db_table_list[$digirisk_db_version] = array($wpdb->prefix.digirisk_doc::prefix . '__documentation');
}
{/*	Version 60	*/
	$digirisk_db_version = 60;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME_FOR_DELETION'][] = array('old_name' => DIGI_DBT_PERMISSION, 'name' => TRASH_DIGI_DBT_PERMISSION);

	/*	Special changes on table structure	*/
	$i = 0;
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][DIGI_DBT_PERMISSION][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][DIGI_DBT_PERMISSION][$i]['ACTION_CONTENT'] = TRASH_DIGI_DBT_PERMISSION;
	$digirisk_table_structure_change[$digirisk_db_version][DIGI_DBT_PERMISSION][$i]['ACTION'] = 'TO';
}
{/*	Version 61	*/
	$digirisk_db_version = 61;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_CHANGE'][TABLE_LIAISON_TACHE_ELEMENT] = array(array('field' => 'wasLinked', 'type' => "enum('before','after','demand')"));

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_LIAISON_TACHE_ELEMENT);
}
{/*	Version 62	*/
	$digirisk_db_version = 62;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_TACHE] = array('efficacite');
	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_INDEX'][TABLE_TACHE] = array('efficacite');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_TACHE);
}
{/*	Version 63	*/
	$digirisk_db_version = 63;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_TACHE] = array('idPhotoAvant', 'idPhotoApres');
	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_INDEX'][TABLE_TACHE] = array('idPhotoAvant', 'idPhotoApres');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_TACHE);
}
{/*	Version 64	*/
	$digirisk_db_version = 64;
	$digirisk_update_way[$digirisk_db_version] = 'creation';

	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array(DIGI_DBT_ELEMENT_NOTIFICATION, DIGI_DBT_LIAISON_USER_NOTIFICATION_ELEMENT, DIGI_DBT_MESSAGES, DIGI_DBT_HISTORIC);

	$digirisk_db_table_list[$digirisk_db_version] = array(DIGI_DBT_ELEMENT_NOTIFICATION, DIGI_DBT_LIAISON_USER_NOTIFICATION_ELEMENT, DIGI_DBT_MESSAGES, DIGI_DBT_HISTORIC);
}
{/*	Version 65	*/
	$digirisk_db_version = 65;
	$digirisk_update_way[$digirisk_db_version] = 'multiple';

	$digirisk_db_table_operation_list[$digirisk_db_version]['DROP_INDEX'][TABLE_TACHE] = array('idPhotoAvant_2', 'idPhotoApres_2');

	/*	Special changes on table structure	*/
	$i = 0;
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_TACHE][$i]['MAIN_ACTION'] = 'ALTER';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_TACHE][$i]['ACTION_CONTENT'] = 'idPhotoAvant_2';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_TACHE][$i]['ACTION'] = 'DROP INDEX';
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_TACHE][$i]['MAIN_ACTION'] = 'ALTER';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_TACHE][$i]['ACTION_CONTENT'] = 'idPhotoApres_2';
	$digirisk_table_structure_change[$digirisk_db_version][TABLE_TACHE][$i]['ACTION'] = 'DROP INDEX';
}
{/*	Version 66	*/
	$digirisk_db_version = 66;
	$digirisk_update_way[$digirisk_db_version] = 'mutlpile';

	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array(DIGI_DBT_ELEMENT_MODIFICATION);
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_RISQUE] = array('last_moved_date');

	$digirisk_db_table_list[$digirisk_db_version] = array(DIGI_DBT_ELEMENT_MODIFICATION, TABLE_RISQUE);
}
{/*	Version 67	*/
	$digirisk_db_version = 67;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][DIGI_DBT_ELEMENT_NOTIFICATION] = array('action_title');

	$digirisk_db_table_list[$digirisk_db_version] = array(DIGI_DBT_ELEMENT_NOTIFICATION);
}
{/*	Version 68	*/
	$digirisk_db_version = 68;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_CHANGE'][TABLE_TACHE] = array(array('field' => 'Status', 'type' => "enum('Valid','Moderated','Deleted','Aborded','Asked')"));
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_CHANGE'][TABLE_ACTIVITE] = array(array('field' => 'Status', 'type' => "enum('Valid','Moderated','Deleted','Aborded','Asked')"));

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_TACHE, TABLE_ACTIVITE);
}
{/*	Version 69	*/
	$digirisk_db_version = 69;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_CHANGE'][TABLE_DUER] = array(array('field' => 'planDUER', 'type' => "longtext"));

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_DUER);
}

{/*	Version 70	*/
	$digirisk_db_version = 70;
	$digirisk_update_way[$digirisk_db_version] = 'update';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_TACHE] = array('is_readable_from_external');
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_ACTIVITE] = array('is_readable_from_external');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_ACTIVITE, TABLE_TACHE);
}

{/*	Version 71	*/
	$digirisk_db_version = 71;
	$digirisk_update_way[$digirisk_db_version] = 'update';
}
{/*	Version 72	*/
	$digirisk_db_version = 72;
	$digirisk_update_way[$digirisk_db_version] = 'multiple';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_TACHE] = array('nom_exportable_plan_action', 'description_exportable_plan_action');
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_ACTIVITE] = array('nom_exportable_plan_action', 'description_exportable_plan_action');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_ACTIVITE, TABLE_TACHE);
}


{/*	Version 73	*/
	$digirisk_db_version = 73;
	$digirisk_update_way[$digirisk_db_version] = 'multiple';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_VARIABLE] = array('affichageVar', 'questionVar', 'questionTitre');
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_DANGER] = array('choix_danger', 'methode_eva_defaut');
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_CATEGORIE_DANGER] = array('methode_eva_defaut');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_VARIABLE, TABLE_DANGER, TABLE_CATEGORIE_DANGER);
}

{/*	Version 74	*/
	$digirisk_db_version = 74;
	$digirisk_update_way[$digirisk_db_version] = 'datas';
}

{/*	Version 75	*/
	$digirisk_db_version = 75;
	$digirisk_update_way[$digirisk_db_version] = 'datas';

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_METHODE);
}

{/*	Version 76	*/
	$digirisk_db_version = 76;
	$digirisk_update_way[$digirisk_db_version] = 'multiple';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_FP] = array('document_type');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_FP);
}

{/*	Version 77	*/
	$digirisk_db_version = 77;
	$digirisk_update_way[$digirisk_db_version] = 'multiple';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_AVOIR_VALEUR] = array('commentaire');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_AVOIR_VALEUR);
}

{/*	Version 78	*/
	$digirisk_db_version = 78;
	$digirisk_update_way[$digirisk_db_version] = 'multiple';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_LIAISON_USER_ELEMENT] = array('date_affectation_reelle', 'date_desaffectation_reelle');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_LIAISON_USER_ELEMENT);
}

{/*	Version 79	*/
	$digirisk_db_version = 79;
	$digirisk_update_way[$digirisk_db_version] = 'multiple';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_PRECONISATION] = array('preconisation_type');
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_LIAISON_PRECONISATION_ELEMENT] = array('preconisation_type');
	$digirisk_db_table_operation_list[$digirisk_db_version]['TABLE_RENAME'][] = array('old_name' => EVA_TRASH_TABLE_ACTIVITE_SUIVI, 'name' => TABLE_ACTIVITE_SUIVI);

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_FP] = array('affected_user', 'document_final_dir');

	$digirisk_db_table_operation_list[$digirisk_db_version]['DATA_EXPLANATION'][TABLE_PRECONISATION][] = __('Mise &agrave; jour des types de pr&eacute;conisations existante', 'evarisk');
	$digirisk_db_table_operation_list[$digirisk_db_version]['DATA_EXPLANATION'][TABLE_VARIABLE][] = __('Ajout des variables manquantes pour l\'&eacute;valuation de la p&eacute;nibilit&eacute;', 'evarisk');
	$digirisk_db_table_operation_list[$digirisk_db_version]['DATA_EXPLANATION'][TABLE_METHODE][] = __('Ajout des m&eacute;thodes manquantes pour l\'&eacute;valuation de la p&eacute;nibilit&eacute;', 'evarisk');

	/*	Special changes on table structure	*/
	$i = 0;
	$i++;
	$digirisk_table_structure_change[$digirisk_db_version][EVA_TRASH_TABLE_ACTIVITE_SUIVI][$i]['MAIN_ACTION'] = 'RENAME';
	$digirisk_table_structure_change[$digirisk_db_version][EVA_TRASH_TABLE_ACTIVITE_SUIVI][$i]['ACTION_CONTENT'] = TABLE_ACTIVITE_SUIVI;
	$digirisk_table_structure_change[$digirisk_db_version][EVA_TRASH_TABLE_ACTIVITE_SUIVI][$i]['ACTION'] = 'TO';

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_PRECONISATION, TABLE_LIAISON_PRECONISATION_ELEMENT, TABLE_FP);
}

{/*	Version 80	*/
	$digirisk_db_version = 80;
	$digirisk_update_way[$digirisk_db_version] = 'structure';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_ACTIVITE_SUIVI] = array('date_ajout', 'export', 'date_modification', 'id_parent');
	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_LIAISON_PRECONISATION_ELEMENT] = array('date_affectation', 'date_update_affectation');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_ACTIVITE_SUIVI, TABLE_FP, TABLE_LIAISON_PRECONISATION_ELEMENT);
}

{/*	Version 81	*/
	$digirisk_db_version = 81;
	$digirisk_update_way[$digirisk_db_version] = 'structure';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_ADD'][TABLE_CATEGORIE_DANGER] = array('position');

	$digirisk_db_table_operation_list[$digirisk_db_version]['DATA_EXPLANATION'][TABLE_CATEGORIE_DANGER][] = __('Mise &agrave; jour des positions des cat&eacute;gories de danger', 'evarisk');

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_CATEGORIE_DANGER);
}

{/*	Version 82	*/
	$digirisk_db_version = 82;
	$digirisk_update_way[$digirisk_db_version] = 'structure';

	$digirisk_db_table_list[$digirisk_db_version] = array(TABLE_ACTIVITE_SUIVI, TABLE_ACTIVITE, TABLE_TACHE);
}

{/*	Version 83	*/
	$digirisk_db_version = 83;
	$digirisk_update_way[$digirisk_db_version] = 'data';
}

{/*	Version 84	*/
	$digirisk_db_version = 84;
	$digirisk_update_way[$digirisk_db_version] = 'data';
}

{/*	Version 85	*/
	$digirisk_db_version = 85;
	$digirisk_update_way[$digirisk_db_version] = 'data';
}

{/*	Version 86	*/
	$digirisk_db_version = 86;
	$digirisk_update_way[$digirisk_db_version] = 'multiple';

	$digirisk_db_table_list[$digirisk_db_version] = array( TABLE_RISQUE, TABLE_GED_DOCUMENTS_META, TABLE_GROUPEMENT, TABLE_RISQUE_HISTO );

	$digirisk_db_table_operation_list[$digirisk_db_version]['DATA_EXPLANATION'][TABLE_GROUPEMENT][] = __('Ajout d\'un champs pour la date de la cr&eacute;ation de la soci&eacute;t&eacute;', 'evarisk');
	$digirisk_db_table_operation_list[$digirisk_db_version]['DATA_EXPLANATION'][TABLE_RISQUE][] = __('Ajout des champs permettant de gérer les dates de début et de fin des risques', 'evarisk');
	$digirisk_db_table_operation_list[$digirisk_db_version]['DATA_EXPLANATION'][TABLE_GED_DOCUMENTS_META][] = __('Table permettant de stocker les informations concernant les informations associ&eacute;es &agrave; un document', 'evarisk');
	$digirisk_db_table_operation_list[$digirisk_db_version]['DATA_EXPLANATION'][TABLE_RISQUE_HISTO][] = __('Table permettant d\'enregistrer un historique pour les informations concernant un risque', 'evarisk');
	$digirisk_db_table_operation_list[$digirisk_db_version]['ADD_TABLE'] = array( TABLE_GED_DOCUMENTS_META, TABLE_RISQUE_HISTO );
}

{/*	Version 87	*/
	$digirisk_db_version = 87;
	$digirisk_update_way[$digirisk_db_version] = 'structure';

	$digirisk_db_table_list[$digirisk_db_version] = array( TABLE_FORMULAIRE_LIAISON );
}

{/*	Version 88	*/
	$digirisk_db_version = 88;
	$digirisk_update_way[$digirisk_db_version] = 'data';
}

{/*	Version 89	*/
	$digirisk_db_version = 89;
	$digirisk_update_way[$digirisk_db_version] = 'data';
}


{/*	Version 90	*/
	$digirisk_db_version = 90;
	$digirisk_update_way[$digirisk_db_version] = 'data';

	$digirisk_db_table_operation_list[$digirisk_db_version]['DATA_EXPLANATION'][TABLE_CATEGORIE_PRECONISATION][] = __('Ajout de la cat&eacute;gorie de pr&eacute;conisation: &Eacute;quipement de protection collective', 'evarisk');
	$digirisk_db_table_operation_list[$digirisk_db_version]['DATA_EXPLANATION'][TABLE_PRECONISATION][] = __('Ajout de la pr&eacute;conisation: &Eacute;quipement de protection collective', 'evarisk');
}

{/*	Version 91	*/
	$digirisk_db_version = 91;
	$digirisk_update_way[$digirisk_db_version] = 'structure';

	$digirisk_db_table_operation_list[$digirisk_db_version]['FIELD_CHANGE'][TABLE_ACTIVITE_SUIVI] = array(array('field' => 'commentaire', 'type' => "longtext"));

	$digirisk_db_table_list[$digirisk_db_version] = array( TABLE_ACTIVITE_SUIVI, TABLE_PRECONISATION, TABLE_CATEGORIE_PRECONISATION);
}

{/*	Version 92	*/
	$digirisk_db_version = 92;
	$digirisk_update_way[$digirisk_db_version] = 'data';
}

{/*	Version 93	*/
	$digirisk_db_version = 93;
	$digirisk_update_way[$digirisk_db_version] = 'data';
}
