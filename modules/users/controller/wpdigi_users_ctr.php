<?php
/**
 * Fichier du controlleur pour la gestion des utilisateurs / Main controller file for users management
 *
 * @author Evarisk development team <dev@evarisk.com>
 * @version 6.0
 */

/**
 * Classe du controlleur principal pour la gestion des utilisateurs / Main controller class for users management
 *
 * @author Evarisk development team <dev@evarisk.com>
 * @version 6.0
 */
class wpdigi_users_ctr {

	/**
	 * Instanciation principale de l'extension / Plugin instanciation
	 */
	function __construct() {
		/*	Création du menu dans l'administration pour le module digirisk / Create the administration menu for digirisk plugin */
		add_action('admin_menu', array( &$this, 'admin_menu' ) );
	}

	/**
	 * Définition du menu dans l'administration de wordpress pour Digirisk / Define the menu for wordpress administration
	 */
	public function admin_menu() {
		/**	Création du menu permettant de visualiser une fiche utilisateur / Create the menu allowing to view a user sheet	*/
		add_users_page( __( 'Digirisk : User\'s profile', 'wpdigi-users-i18n' ), __( 'Digirisk : Profile', 'wpdigi-users-i18n' ), 'digi_view_user_profil_menu', 'digirisk_users_profil', array('evaUser','digi_user_profil'));

		/**	Création du menu permettant d'accèder à l'import/création rapide d'utilisateurs / Create the menu allowing to import/quick add users	*/
		add_users_page(  __( 'Digirisk : Import', 'wpdigi-users-i18n' ), __('Digirisk : Import', 'wpdigi-users-i18n'), 'digi_view_user_import_menu', 'digirisk_import_users', array('evaUser','importUserPage'));

		/**	Création du menu de gestion des droits utilisateurs	*/
		add_users_page( __( 'Digirisk : Users\' rights', 'wpdigi-users-i18n' ), __( 'Digirisk : Rights', 'wpdigi-users-i18n'), 'digi_user_right_management_menu', DIGI_URL_SLUG_USER_RIGHT, array('digirisk_permission','elementMainPage'));

		/**	Création du menu de gestion des groupes utilisateurs si l'option est activée / Create the menu allowing to manage users' groups in case options is activ	*/
		if ( 'oui' == digirisk_options::getOptionValue( 'activGroupsManagement' ) ) {
			add_users_page( __( 'Digirisk : Users\' groups management', 'wpdigi-users-i18n' ), __( 'Digirisk : Groups', 'wpdigi-users-i18n'), 'digi_view_user_groups_menu', DIGI_URL_SLUG_USER_GROUP, array('digirisk_groups','elementMainPage'));
		}
	}

}
