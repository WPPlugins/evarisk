<?php
/**
* Plugin permissions management
*
* Define method to manage permission for the software
* @author Evarisk <dev@evarisk.com>
* @version 5.1.3.1
* @package Digirisk
* @subpackage librairies
*/

/**
* Define method to manage permission for the software
* @package Digirisk
* @subpackage librairies
*/
class digirisk_permission
{

	/**
	*	Define the database table to use in the entire script
	*/
	const dbTable = DIGI_DBT_PERMISSION_ROLE;

	public static function permission_list(){
		{/*	Menu permission	*/
		$permission['digi_view_dashboard_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'dashboard', 'permission_sub_module' => 'menu');
		$permission['digi_view_recommandation_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'recommandation', 'permission_sub_module' => 'menu');
		$permission['digi_view_method_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'method', 'permission_sub_module' => 'menu');
		$permission['digi_view_danger_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'danger', 'permission_sub_module' => 'menu');
		$permission['digi_view_evaluation_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'evaluation', 'permission_sub_module' => 'menu');
		$permission['digi_view_correctiv_action_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'correctiv_action', 'permission_sub_module' => 'menu');
		$permission['digi_view_user_profil_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'user', 'permission_sub_module' => 'menu');
		$permission['digi_view_user_groups_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'user', 'permission_sub_module' => 'menu');
		$permission['digi_view_user_import_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'user', 'permission_sub_module' => 'menu');
		$permission['digi_user_right_management_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'user', 'permission_sub_module' => 'menu');
		$permission['digi_view_options_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'option', 'permission_sub_module' => 'menu');
		$permission['digi_view_regulatory_monitoring_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'referential', 'permission_sub_module' => 'menu');
		$permission['digi_documentation_management_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'documentation', 'permission_sub_module' => 'menu');
		$permission['digi_tools_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'tools', 'permission_sub_module' => 'menu');
		}

		{/*	User group permission	*/
		$permission['digi_view_user_group'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'user', 'permission_sub_module' => 'user');
		$permission['digi_add_user_group'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'user', 'permission_sub_module' => 'user');
		$permission['digi_edit_user_group'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'user', 'permission_sub_module' => 'user');
		$permission['digi_view_detail_user_group'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'user', 'permission_sub_module' => 'user');
		$permission['digi_delete_user_group'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'user', 'permission_sub_module' => 'user');
		}

		{/*	Evaluator group permission	*/
		$permission['digi_view_evaluator_group'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'user', 'permission_sub_module' => 'evaluator');
		$permission['digi_add_evaluator_group'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'user', 'permission_sub_module' => 'evaluator');
		$permission['digi_edit_evaluator_group'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'user', 'permission_sub_module' => 'evaluator');
		$permission['digi_view_detail_evaluator_group'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'user', 'permission_sub_module' => 'evaluator');
		$permission['digi_delete_evaluator_group'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'user', 'permission_sub_module' => 'evaluator');
		}

		{/*	Role permission	*/
		$permission['digi_add_user_role'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'user', 'permission_sub_module' => 'role');
		$permission['digi_edit_user_role'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'user', 'permission_sub_module' => 'role');
		$permission['digi_view_detail_user_role'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => 'detail', 'permission_module' => 'user', 'permission_sub_module' => 'role');
		$permission['digi_delete_user_role'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'user', 'permission_sub_module' => 'role');
		$permission['digi_manage_user_right'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'user', 'permission_sub_module' => 'right');
		}

		{/*	Recommandation permission	*/
		$permission['digi_add_recommandation_cat'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'recommandation', 'permission_sub_module' => 'category');
		$permission['digi_view_detail_recommandation_cat'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => 'detail', 'permission_module' => 'recommandation', 'permission_sub_module' => 'category');
		$permission['digi_edit_recommandation_cat'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'recommandation', 'permission_sub_module' => 'category');
		$permission['digi_delete_recommandation_cat'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'recommandation', 'permission_sub_module' => 'category');
		$permission['digi_add_recommandation'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'recommandation', 'permission_sub_module' => '');
		$permission['digi_view_detail_recommandation'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => 'detail', 'permission_module' => 'recommandation', 'permission_sub_module' => '');
		$permission['digi_edit_recommandation'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'recommandation', 'permission_sub_module' => '');
		$permission['digi_delete_recommandation'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'recommandation', 'permission_sub_module' => '');
		}

		{/*	Evaluation method permission	*/
		$permission['digi_add_method'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'method', 'permission_sub_module' => '');
		$permission['digi_edit_method'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'method', 'permission_sub_module' => '');
		$permission['digi_view_detail_method'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => 'detail', 'permission_module' => 'method', 'permission_sub_module' => '');
		$permission['digi_delete_method'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'method', 'permission_sub_module' => '');
		}

		{/*	Evaluation method Vars permission	*/
		$permission['digi_add_method_var'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'method', 'permission_sub_module' => 'vars');
		$permission['digi_edit_method_var'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'method', 'permission_sub_module' => 'vars');
		$permission['digi_view_detail_method_var'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => 'detail', 'permission_module' => 'method', 'permission_sub_module' => 'vars');
		$permission['digi_delete_method_var'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'method', 'permission_sub_module' => 'vars');
		}

		{/*	Danger permission	*/
		$permission['digi_add_danger_category'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'danger', 'permission_sub_module' => 'category');
		$permission['digi_edit_danger_category'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'danger', 'permission_sub_module' => 'category');
		$permission['digi_move_danger_category'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'move', 'permission_module' => 'danger', 'permission_sub_module' => 'category');
		$permission['digi_view_detail_danger_category'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => 'detail', 'permission_module' => 'danger', 'permission_sub_module' => 'category');
		$permission['digi_delete_danger_category'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'danger', 'permission_sub_module' => 'category');
		$permission['digi_add_danger'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'danger', 'permission_sub_module' => '');
		$permission['digi_edit_danger'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'danger', 'permission_sub_module' => '');
		$permission['digi_move_danger'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'move', 'permission_module' => 'danger', 'permission_sub_module' => '');
		$permission['digi_view_detail_danger'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => 'detail', 'permission_module' => 'danger', 'permission_sub_module' => '');
		$permission['digi_delete_danger'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'danger', 'permission_sub_module' => '');
		}

		/*	Options permission	*/
		$permission['digi_edit_option'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'option', 'permission_sub_module' => '');

		/*	Tools permission	*/
		$permission['digi_delete_database'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'tools', 'permission_sub_module' => 'db_tools');
		$permission['digi_mark_notice_as_read_for_all'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'tools', 'permission_sub_module' => 'admin_notice');

		/*	User permission	*/
		$permission['digi_import_user'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'user', 'permission_sub_module' => 'import');

		{/*	Risk persmission	*/
		$permission['digi_view_risk_histo'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'risk', 'permission_sub_module' => 'histo');
		$permission['digi_not_historicize_risk'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'risk', 'permission_sub_module' => 'histo');
		$permission['digi_view_mistake_risk_history'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'risk', 'permission_sub_module' => 'histo');
		}

		{/*	Groupement permission	*/
		$permission['digi_add_groupement'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'arborescence', 'permission_sub_module' => 'groupement');
		$permission['digi_edit_groupement'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'arborescence', 'permission_sub_module' => 'groupement');
		$permission['digi_move_groupement'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'move', 'permission_module' => 'arborescence', 'permission_sub_module' => 'groupement');
		$permission['digi_view_detail_groupement'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => 'detail', 'permission_module' => 'arborescence', 'permission_sub_module' => 'groupement');
		$permission['digi_delete_groupement'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'arborescence', 'permission_sub_module' => 'groupement');
		}

		{/*	Work unit permission	*/
		$permission['digi_add_unite'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'arborescence', 'permission_sub_module' => 'unite');
		$permission['digi_edit_unite'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'arborescence', 'permission_sub_module' => 'unite');
		$permission['digi_move_unite'] = array('set_by_default' => 'no', 'permission_type' => '', 'permission_sub_type' => 'move', 'permission_module' => 'arborescence', 'permission_sub_module' => 'unite');
		$permission['digi_view_detail_unite'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => 'detail', 'permission_module' => 'arborescence', 'permission_sub_module' => 'unite');
		$permission['digi_delete_unite'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'arborescence', 'permission_sub_module' => 'unite');
		}

		{/*	Correctiv action permission	*/
		$permission['digi_view_correctiv_action'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'correctiv_action', 'permission_sub_module' => '');
		$permission['digi_view_task_trash'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'correctiv_action', 'permission_sub_module' => 'task');
		$permission['digi_view_action_trash'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'correctiv_action', 'permission_sub_module' => 'action');

		$permission['digi_follow_action'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'correctiv_action', 'permission_sub_module' => '');
		$permission['digi_control_task'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'correctiv_action', 'permission_sub_module' => 'task');

		$permission['digi_ask_action_front'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'correctiv_action', 'permission_sub_module' => '');

		$permission['digi_add_task'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'correctiv_action', 'permission_sub_module' => 'task');
		$permission['digi_edit_task'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'correctiv_action', 'permission_sub_module' => 'task');
		$permission['digi_move_task'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'move', 'permission_module' => 'correctiv_action', 'permission_sub_module' => 'task');
		$permission['digi_view_detail_task'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => 'detail', 'permission_module' => 'correctiv_action', 'permission_sub_module' => 'task');
		$permission['digi_delete_task'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'correctiv_action', 'permission_sub_module' => 'task');

		$permission['digi_add_action'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'correctiv_action', 'permission_sub_module' => 'action');
		$permission['digi_edit_action'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'correctiv_action', 'permission_sub_module' => 'action');
		$permission['digi_move_action'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'move', 'permission_module' => 'correctiv_action', 'permission_sub_module' => 'action');
		$permission['digi_view_detail_action'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => 'detail', 'permission_module' => 'correctiv_action', 'permission_sub_module' => 'action');
		$permission['digi_delete_action'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'correctiv_action', 'permission_sub_module' => 'action');
		}

		{/*	Trash permission	*/
		$permission['digi_view_risk_trash'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'risk', 'permission_sub_module' => '');
		$permission['digi_view_danger_trash'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'danger', 'permission_sub_module' => '');
		$permission['digi_view_danger_category_trash'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'danger', 'permission_sub_module' => 'category');
		$permission['digi_view_recommandation_trash'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'recommandation', 'permission_sub_module' => '');
		$permission['digi_view_recommandation_category_trash'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'recommandation', 'permission_sub_module' => 'category');
		$permission['digi_view_groupement_trash'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'arborescence', 'permission_sub_module' => 'groupement');
		$permission['digi_view_unite_trash'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'arborescence', 'permission_sub_module' => 'unite');
		$permission['digi_view_method_trash'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'method', 'permission_sub_module' => '');
		$permission['digi_view_user_group_trash'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'user', 'permission_sub_module' => 'user');
		$permission['digi_view_evaluator_group_trash'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'user', 'permission_sub_module' => 'evaluator');
		$permission['digi_view_user_role_trash'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'user', 'permission_sub_module' => 'role');
		$permission['digi_edit_task_trash'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'correctiv_action', 'permission_sub_module' => 'task');
		$permission['digi_edit_action_trash'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'correctiv_action', 'permission_sub_module' => 'action');
		$permission['digi_edit_risk_trash'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'risk', 'permission_sub_module' => '');
		$permission['digi_edit_danger_trash'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'danger', 'permission_sub_module' => '');
		$permission['digi_edit_danger_category_trash'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'danger', 'permission_sub_module' => 'category');
		$permission['digi_edit_recommandation_trash'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'recommandation', 'permission_sub_module' => '');
		$permission['digi_edit_recommandation_category_trash'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'recommandation', 'permission_sub_module' => 'category');
		$permission['digi_edit_groupement_trash'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'arborescence', 'permission_sub_module' => 'groupement');
		$permission['digi_edit_unite_trash'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'arborescence', 'permission_sub_module' => 'unite');
		$permission['digi_edit_method_trash'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'method', 'permission_sub_module' => '');
		$permission['digi_edit_user_group_trash'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'user', 'permission_sub_module' => 'user');
		$permission['digi_edit_evaluator_group_trash'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'user', 'permission_sub_module' => 'evaluator');
		$permission['digi_edit_user_role_trash'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'user', 'permission_sub_module' => 'role');
		}

		return $permission;
	}

	/**
	*	Initialise permission for the administrator when installing the plugin
	*/
	public static function digirisk_init_permission() {
		/*	R�cup�ration du r�le administrateur	*/
		$role = get_role('administrator');

		/*	R�cup�ration des "anciens" droits	*/
		$droits = digirisk_permission::getDroitdigirisk();
		foreach($droits as $droit => $appellation) {/*	Lecture des "anciens" droits pour les retirer � l'administrateur	*/
			if(($role != null) && $role->has_cap($droit))
			{
				$role->remove_cap($droit);
			}
		}

		/*	R�cup�ration des "nouveaux" droits	*/
		$droits = self::permission_list();
		foreach($droits as $droit => $droit_definition)
		{/*	Lecture des "nouveaux" droits pour affectation � l'administrateur	*/
			if(($role != null) && !$role->has_cap($droit))
			{
				$role->add_cap($droit);
			}
		}

		/*	Vidage de l'objet r�le	*/
		unset($role);
	}

	/**
	*	Call the different element in order to edit rights per user
	*
	*	@return string The html output of the permission list for a specific user
	*/
	function user_permission_management()
	{
		global $digi_wp_role;

		/*	R�cup�ration des informations concernant l'utilisateur en cours d'�dition	*/
		$user = new WP_User($_REQUEST['user_id']);

		ob_start();
		self::permission_management($user);
		$digiPermissionForm = ob_get_contents();
		ob_end_clean();
		echo '<h3>' . __('Droits d\'acc&egrave;s de l\'utilisateur pour le logiciel Digirisk', 'digirisk') . '</h3>' . $digiPermissionForm;

		ob_start();
		self::digiSpecificPermission($user);
		$digiSpecificPermission = ob_get_contents();
		ob_end_clean();
		echo '<h3>' . __('Droits d\'acc&egrave;s sp&eacute;cifiques de l\'utilisateur pour le logiciel Digirisk', 'digirisk') . '</h3>' . $digiSpecificPermission;
	}

	/**
	*	Creation of the element management page
	*/
	function elementMainPage()
	{
		global $digi_wp_role;
		global $digi_role;

		$output = $message = '';
		$action = isset($_REQUEST['action']) ? digirisk_tools::IsValid_Variable($_REQUEST['action']) : '';
		$save = isset($_REQUEST['save']) ? digirisk_tools::IsValid_Variable($_REQUEST['save']) : '';
		$formAction = isset($_REQUEST[self::dbTable . '_action']) ? digirisk_tools::IsValid_Variable($_REQUEST[self::dbTable . '_action']) : '';
		$role = isset($_REQUEST['role']) ? digirisk_tools::IsValid_Variable($_REQUEST['role']) : '';
		$editionInProgress = false;

		/*	Instanciation de l'objet role de worpdress	*/
		$digi_wp_role = new WP_Roles();

		/*	R�cup�ration des roles cr��s dans digirisk	*/
		$digi_role = array();
		$digiRoles = self::digirisk_get_role();
		foreach($digiRoles as $digiRole)
		{
			$digi_role[$digiRole->role_internal_name] = $digiRole;
		}

		$actionResult = self::elementAction();
		if(($actionResult == 'done') || ($actionResult == 'nothingToUpdate'))
		{
			$message = '<img src="' . EVA_MESSAGE_SUCCESS . '" alt="' . $actionResult . '" class="messageIcone" />' . __('Le r&ocirc;le a &eacute;t&eacute; correctement enregistr&eacute;', 'evarisk');
			if($formAction == 'delete')
			{
			$message = '<img src="' . EVA_MESSAGE_SUCCESS . '" alt="' . $actionResult . '" class="messageIcone" />' . __('Le r&ocirc;le a &eacute;t&eacute; correctement supprim&eacute;', 'evarisk');
			}
		}
		elseif(($actionResult == 'error'))
		{
			$message = '<img src="' . EVA_MESSAGE_ERROR . '" alt="' . $actionResult . '" class="messageIcone" />' . __('Une erreur est survenue lors de l\'enregistrement du r&ocirc;le', 'evarisk');
		}
		elseif(($actionResult == 'rightAdded'))
		{
			$message = '<img src="' . EVA_MESSAGE_SUCCESS . '" alt="' . $actionResult . '" class="messageIcone" />' . __('Les droits du r&ocirc;le ont bien &eacute;t&eacute; mis &agrave; jour', 'evarisk');
		}
		elseif($save == 'ok')
		{
			$message = '<img src="' . EVA_MESSAGE_SUCCESS . '" alt="' . $actionResult . '" class="messageIcone" />' . __('Le r&ocirc;le a &eacute;t&eacute; correctement ajout&eacute;', 'evarisk');
		}

		if((($action == 'edit') && ($role != '')) || ($action == 'add'))
		{
			/*	Get informations about the current element being edited	*/
			$currentEditedElement = self::getElement($role);

			/*	Check if the wanted element realy exist	*/
			if((count($currentEditedElement) > 0) && ($action != 'add'))
			{
				$editionPageTitle = sprintf(__('&Eacute;dition du r&ocirc;le: %s', 'evarisk'), '<span class="digiriskUserGroupEditionName" >' . translate_user_role($currentEditedElement['name']) . '</span>');
				$editionInProgress = true;
				/*	On v�rifie que l'utilisateur a bien les droits sur la page courante, sinon on lui affiche un message en le remettant sur la page principale	*/
				if(!current_user_can('digi_edit_user_role'))
				{
					$editionInProgress = false;
					$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Vous n\'&ecirc;tes pas autoris&eacute; &agrave; utiliser cette fonctionnalit&eacute;', 'evarisk') . '</strong>');
					$output .=
'<script type="text/javascript">
	digirisk(document).ready(function(){
		actionMessageShow("#message", "' . $message . '");
		setTimeout(\'actionMessageHide("#message")\',7500);
	});
</script>';
				}
			}
			elseif($action == 'add')
			{
				$id = '';
				$currentEditedElement = '';
				$editionPageTitle = __('Ajouter un r&ocirc;le pour digirisk', 'evarisk');
				$editionInProgress = true;
				/*	On v�rifie que l'utilisateur a bien les droits sur la page courante, sinon on lui affiche un message en le remettant sur la page principale	*/
				if(!current_user_can('digi_add_user_role'))
				{
					$editionInProgress = false;
					$message = addslashes('<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Vous n\'&ecirc;tes pas autoris&eacute; &agrave; utiliser cette fonctionnalit&eacute;', 'evarisk') . '</strong>');
					$output .=
'<script type="text/javascript">
	digirisk(document).ready(function(){
		actionMessageShow("#message", "' . $message . '");
		setTimeout(\'actionMessageHide("#message")\',7500);
	});
</script>';
				}
			}
		}

		if(!$editionInProgress)
		{	/*	In case that we are on the listing page	*/
			/*	Output the list of employees groups	*/
			$output .= EvaDisplayDesign::afficherDebutPage(__('Gestion des droits des utilisateurs par r&ocirc;le', 'evarisk'), DIGI_USER_RIGHT_ICON_S, __('Gestion des droits des utilisateurs par r&ocirc;le', 'evarisk'), __('Gestion des droits des utilisateurs par r&ocirc;le', 'evarisk'), self::dbTable, false, $message, false);
			if(current_user_can('digi_add_user_role'))
			{
				$output .= '<h2 class="clear" ><a href="' . admin_url('users.php?page=' . DIGI_URL_SLUG_USER_RIGHT . '&amp;action=add') . '" class="button add-new-h2" >' . __('Ajouter un r&ocirc;le', 'evarisk') . '</a></h2>';
			}
			$elementList = self::getElement();
			$output .= self::elementList($elementList);

			/*	Ajoute le formulaire de suppression d'un element	*/
			$output .= '<form method="post" id="' . self::dbTable . '_delete_form" action="" ><input type="hidden" name="' . self::dbTable . '_action" id="' . self::dbTable . '_action" value="delete" /><input type="hidden" name="' . self::dbTable . '[id]" id="' . self::dbTable . '_delete_form_id" value="" /></form>';
		}
		else
		{	/*	In case that we are on the edition/addition page	*/
			/*	Start the page content	*/
			$output .= EvaDisplayDesign::afficherDebutPage($editionPageTitle, DIGI_USER_GROUP_ICON_S, __('Groupes d\'utilisateurs', 'evarisk'), __('Groupes d\'utilisateurs', 'evarisk'), self::dbTable, false, $message, false);

			/*	Add the form to edit the element	*/
			$output .= self::elementEdition($currentEditedElement, $role);
		}

		/*	Close the page content	*/
		$output .= EvaDisplayDesign::afficherFinPage();

		if(($actionResult != '') || ($save == 'ok'))
		{
			$output .= '
<script type="text/javascript" >
	digirisk("#message").addClass("updated");
</script>';
		}

		echo $output;
	}

	/**
	*	Regroup the different action to manage the element
	*/
	function elementAction()
	{
		global $wpdb;
		global $current_user;
		global $digi_role;
		global $digi_wp_role;

		/*	Initialize the different vars usefull for the action	*/
		$pageMessage = $actionResult = '';
		$action = isset($_REQUEST[self::dbTable . '_action']) ? digirisk_tools::IsValid_Variable($_REQUEST[self::dbTable . '_action']) : '';
		$role = isset($_REQUEST[self::dbTable]['id']) ? digirisk_tools::IsValid_Variable($_REQUEST[self::dbTable]['id']) : '';

		if(($role == '') || array_key_exists($role, $digi_role)){
			$roleForId = self::digirisk_get_role($role, 'role_internal_name');
			$roleId = (!empty($roleForId)?$roleForId[0]->id:0);
			/*	Basic action 	*/
			if(($action != '') && (($action == 'edit') || ($action == 'editandcontinue')))
			{/*	Edit action	*/
				$_REQUEST[self::dbTable]['last_update_date'] = current_time('mysql', 0);
				$actionResult = eva_database::update($_REQUEST[self::dbTable], $roleId, self::dbTable);
			}
			elseif(($action != '') && (($action == 'delete')))
			{/*	Delete action	*/
				$_REQUEST[self::dbTable]['deletion_date'] = current_time('mysql', 0);
				$_REQUEST[self::dbTable]['deletion_user_id'] = $current_user->ID;
				$_REQUEST[self::dbTable]['status'] = 'deleted';
				$actionResult = eva_database::update($_REQUEST[self::dbTable], $roleId, self::dbTable);
				$digi_wp_role->remove_role($role);
			}
			elseif(($action != '') && (($action == 'save') || ($action == 'saveandcontinue') || ($action == 'add')))
			{/*	Add action	*/
				$_REQUEST[self::dbTable]['role_internal_name'] = 'digirisk_' . str_replace('-', '_', sanitize_title($_REQUEST[self::dbTable]['role_name']));
				$_REQUEST[self::dbTable]['creation_date'] = current_time('mysql', 0);
				$_REQUEST[self::dbTable]['creation_user_id'] = $current_user->ID;
				$actionResult = eva_database::save($_REQUEST[self::dbTable], self::dbTable);

				$role = $_REQUEST[self::dbTable]['role_internal_name'];
				$roleName = $_REQUEST[self::dbTable]['role_name'];
				$digi_wp_role->add_role($role, $roleName);

				$moreParamsForRoleCreation = '';
				$roleToCopy = isset($_REQUEST['roleToCopy']) ? digirisk_tools::IsValid_Variable($_REQUEST['roleToCopy']) : 'subscriber';
				if($roleToCopy != ''){
					$moreParamsForRoleCreation = '&roleToCopy=' . $roleToCopy;
					$basic_caps = $digi_wp_role->get_role($roleToCopy);
				}

				wp_redirect(admin_url('users.php?page=' . DIGI_URL_SLUG_USER_RIGHT . "&action=edit&role=" . $role . "&save=ok" . $moreParamsForRoleCreation));
			}
		}
		elseif(!array_key_exists($role, $digi_role))
			$actionResult = 'rightAdded';


		/*	Permission affectation to the selected role	*/
		if(($role != '') && ($action != 'delete') && ($action != 'add'))
		{
			$roleInEdition = $digi_wp_role->get_role($role);
			$existingPermission = self::permission_list();
			foreach($existingPermission as $permission => $permission_definition)
			{
				if(!$roleInEdition->has_cap($permission) && is_array($_POST['digi_permission']) && array_key_exists($permission, $_POST['digi_permission']))
				{
					$roleInEdition->add_cap($permission);
				}
				elseif(($roleInEdition->has_cap($permission) && is_array($_POST['digi_permission']) && !array_key_exists($permission, $_POST['digi_permission'])) || (!is_array($_POST['digi_permission'])))
				{
					$roleInEdition->remove_cap($permission);
				}
			}
		}

		return $actionResult;
	}
	/**
	*	Create a html table output for element list presentation
	*
	*	@param object $elementList A wordpress object containing the entire element list with the different informations to ouput
	*
	*	@return string $elementOutputTable The html output completely build with the element's list to output
	*/
	function elementList($elementList)
	{
		global $digi_role;

		/*	Define the different table column and column class	*/
		unset($titres,$classes, $idLignes, $lignesDeValeurs);
		$idLignes = null;
		$idTable = 'digirisk_user_groups_';
		$titres[] = __("Nom du r&ocirc;le", 'evarisk');
		$titres[] = __("Droits digirisk", 'evarisk');
		$titres[] = __("Actions", 'evarisk');
		$classes[] = 'digirisk_user_role_column_name';
		$classes[] = 'digirisk_user_role_column_caps_details';
		$classes[] = 'digirisk_user_role_column_action';

		/*	R�cup�re les droits li�s au logiciel digirisk	*/
		$digiriskPermission = self::permission_list();
		foreach($digiriskPermission as $permission => $permission_definition)
		{
			$digiRight[$permission_definition['permission_module']][] = $permission;
		}

		unset($ligneDeValeurs);
		$i=0;
		if(count($elementList) > 0)
		{
			foreach($elementList as $elementKey => $element)
			{
				/*	Define each line id for the table	*/
				$idLignes[] = 'digirisk_users_roles_' . $elementKey;

				/*	Define each column value for each line	*/
				$roleName = translate_user_role($element['name']);
				if(array_key_exists($elementKey, $digi_role))
				{
					$roleName = __($digi_role[$elementKey]->role_name, 'evarisk');
				}
				$lignesDeValeurs[$i][] = array('value' => $roleName, 'class' => 'digirisk_user_groups_cell_name');
				$roleCapabilities = '  ';
				foreach($digiRight as $rightCategory => $rightCategoryContent)
				{
					$rolePermission = ' ';
					foreach($rightCategoryContent as $capabilityName)
					{
						if(array_key_exists($capabilityName, $element['capabilities']))
						{
							$rolePermission .= __($capabilityName, 'evarisk') . ', ';
						}
					}
					$rolePermission = trim(substr($rolePermission, 0, -2));
					if($rolePermission != '')
					{
						$roleCapabilities .= '<span class="digi_permission_category_name" >' . __('permission_' . $rightCategory, 'evarisk') . '&nbsp;:&nbsp;</span>' . $rolePermission . '<br/>';
					}
				}
				if(!current_user_can('digi_view_detail_user_role'))
				{
					$roleCapabilities = __('Vous n\'avez pas les autorisations pour voir le d&eacute;tail de ce r&ocirc;le', 'evarisk');
				}
				elseif(trim($roleCapabilities) == '')
				{
					$roleCapabilities = __('Aucun droit du logiciel digirisk n\'est affect&eacute; &agrave; ce r&ocirc;le', 'evarisk');
				}
				$lignesDeValeurs[$i][] = array('value' => $roleCapabilities, 'class' => 'digirisk_user_role_cell_caps_details');
				$userRoleAction = '';
				if(current_user_can('digi_delete_user_role') && array_key_exists($elementKey, $digi_role))
				{
					$userRoleAction .= '<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'delete_vs.png" alt="' . __('Supprimer ce r&ocirc;le', 'evarisk') . '" title="' . __('Supprimer ce r&ocirc;le', 'evarisk') . '" class="alignright deleteRole" />';
				}
				if(current_user_can('digi_edit_user_role'))
				{
					$userRoleAction .= '<a href="' . admin_url('users.php?page=' . DIGI_URL_SLUG_USER_RIGHT . "&amp;action=edit&amp;role=" . $elementKey) . '" ><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'edit_vs.png" alt="' . __('&Eacute;diter ce r&ocirc;le', 'evarisk') . '" title="' . __('&Eacute;diter ce r&ocirc;le', 'evarisk') . '" class="alignright editRole" /></a>';
				}
				$lignesDeValeurs[$i][] = array('value' => $userRoleAction, 'class' => 'digirisk_user_role_cell_action');
				$i++;
			}
		}
		else
		{
			/*	Define the line id when no result is found	*/
			$idLignes[] = 'no_users_groups';

			/*	Define the line content when no result is found	*/
			$lignesDeValeurs[$i][] = array('value' => __('Aucun r&ocirc;le n\'a &eacute;t&eacute; trouv&eacute;', 'evarisk'), 'class' => '');
			$lignesDeValeurs[$i][] = array('value' => '', 'class' => '');
			$lignesDeValeurs[$i][] = array('value' => '', 'class' => '');
		}

		/*	Transform the html table into a "datatable" (jqueyr plugin) table	*/
		/*	For option adding see jqueyr datatable documentation	*/
		$script = '
<script type="text/javascript">
	digirisk(document).ready(function(){
		digirisk("#' . $idTable . ' tfoot").remove();
		digirisk("#' . $idTable . '").dataTable({
			"bInfo": false,
			"bLengthChange": false,
			"oLanguage":{
				"sUrl": "' . EVA_INC_PLUGIN_URL . 'js/dataTable/jquery.dataTables.common_translation.txt"
			}
		});
		digirisk(".deleteRole").click(function(){
			if(confirm(digi_html_accent_for_js("' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer ce r&ocirc;le?', 'evarisk') . '"))){
				var clickedId = digirisk(this).parent("td").parent("tr").attr("id").replace("digirisk_users_roles_", "");
				digirisk("#' . self::dbTable . '_delete_form_id").val(clickedId);
				digirisk("#' . self::dbTable . '_delete_form").submit();
			}
		});
	});
</script>';

		/*	Call the table display function	*/
		$elementOutputTable = EvaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script);

		return $elementOutputTable;
	}
	/**
	*	Get informations about an element into database
	*
	*	@param integer $id optionnal The identifier of the element we want to get
	*	@param string $status optionnal Allows to define if we want to get the entire list of element or just element that have a specific status
	*	@param string $type optionnal The type of the element we want to get
	*
	*	@return object|array A wordpress object with the element informations on case that the request works fine. In the other case return an empty array
	*/
	function getElement($selectedRole = '')
	{
		global $digi_wp_role;
		$roles = '';

		/*	R�cup�re la liste des r�les existant	*/
		$roles = $digi_wp_role->roles;

		/*	Si on a s�lectionn� un role en particulier alors on returne uniquement ce role	*/
		if($selectedRole != '')
		{
			$roles = $roles[$selectedRole];
		}

		return $roles;
	}
	/**
	*	Return the different button to save the item currently being added or edited
	*
	*	@return string $currentPageButton The html output code with the different button to add to the interface
	*/
	function getPageFormButton()
	{
		global $digi_role;

		$action = isset($_REQUEST['action']) ? digirisk_tools::IsValid_Variable($_REQUEST['action']) : 'add';
		$role = isset($_REQUEST['role']) ? digirisk_tools::IsValid_Variable($_REQUEST['role']) : '';
		$currentPageButton = '';

		if(($action == 'add') && current_user_can('digi_add_user_role'))
		{
			$currentPageButton .= '<input type="submit" class="button-primary" id="add" name="add" value="' . __('Ajouter', 'evarisk') . '" />';
		}
		elseif(current_user_can('digi_edit_user_group'))
		{
			$currentPageButton .= '<input type="submit" class="button-primary" id="save" name="save" value="' . __('Enregistrer', 'evarisk') . '" />';
			//<input type="button" class="button-primary" id="saveandcontinue" name="saveandcontinue" value="' . __('Enregistrer et continuer l\'&eacute;dition', 'evarisk') . '" />';
		}
		if(($action != 'add') && current_user_can('digi_delete_user_role') && array_key_exists($role, $digi_role))
		{
			$currentPageButton .= '<input type="button" class="button-primary" id="delete" name="delete" value="' . __('Supprimer', 'evarisk') . '" />';
		}

		$currentPageButton .= '<a href="' . admin_url('users.php?page=' . DIGI_URL_SLUG_USER_RIGHT) . '" class="button add-new-h2" >' . __('Retour', 'evarisk') . '</a>';

		return $currentPageButton;
	}
	/**
	*	Define the form to output for the element
	*
	*	@param array $elementInformations The different informations about the element to edit, stored into an array
	*
	*	@return string $elementEditionOutput An html output with the complete edition form for the current element
	*/
	function elementEdition($elementInformations = '', $currentElementId)
	{
		global $digi_wp_role;
		global $digi_role;

		$elementEditionOutput = '';
		$dbFieldToHide = array('creation_user_id', 'deletion_user_id', 'deletion_date', 'creation_date', 'last_update_date', 'role_internal_name', 'status');
		$action = isset($_REQUEST['action']) ? digirisk_tools::IsValid_Variable($_REQUEST['action']) : 'add';

		$the_form_content_hidden = $the_form_general_content = '';
		if($action == 'add')
		{
			$dbFieldList = eva_database::fields_to_input(self::dbTable);
			foreach($dbFieldList as $input_key => $input_def)
			{
				if(!in_array($input_def['name'], $dbFieldToHide))
				{
					if(($currentElementId == '') || array_key_exists($currentElementId, $digi_role))
					{
						$requestFormValue = isset($_REQUEST[self::dbTable][$input_def['name']]) ? digirisk_tools::IsValid_Variable($_REQUEST[self::dbTable][$input_def['name']]) : '';
						$currentFieldValue = $input_def['value'];
						if(is_array($elementInformations))
						{
							$currentFieldValue = $elementInformations['name'];
						}
						elseif(($action != '') && ($requestFormValue != ''))
						{
							$currentFieldValue = $requestFormValue;
						}

						if(array_key_exists($currentElementId, $digi_role)) {
							if($input_def['name'] == 'id') {
								$currentFieldValue = $currentElementId;
							}
							elseif($input_def['name'] == 'role_name') {

							}
						}

						$input_def['value'] = $currentFieldValue;
						$the_input = digirisk_form::check_input_type($input_def, self::dbTable);

						if($input_def['type'] != 'hidden')
						{
							$label = 'for="' . $input_def['name'] . '"';
							if(($input_def['type'] == 'radio') || ($input_def['type'] == 'checkbox'))
							{
								$label = '';
							}
							$input = '
					<div class="clear" >
						<div class="digirisk_form_label digirisk_attr_' . $input_def['name'] . '_label alignleft" >
							<label ' . $label . ' >' . __($input_def['name'], 'evarisk') . '</label>
						</div>
						<div class="digirisk_form_input digirisk_attr_' . $input_def['name'] . '_input alignleft" >
							' . $the_input . '
						</div>
					</div>';
							$the_form_general_content .= $input;
						}
						else
						{
							$the_form_content_hidden .= '
				' . $the_input;
						}
					}
				}
			}
			$the_form_general_content .= '
					<div class="clear" >
						<div class="digirisk_form_label digirisk_attr_role_to_copy_from_label alignleft" >
							<label for="role_to_copy_from" >
								' . __('Cr&eacute;er le r&ocirc;le &agrave; partir d\'un r&ocirc;le existant', 'evarisk') . '
								<div class="digi_permission_explanation" >' . __('Si vous choisissez un r&ocirc;le &agrave; copier, les droits de ce r&ocirc;le seront automatiquement coch&eacute; dans le prochain &eacute;cran', 'evarisk') . '</div>
							</label>
						</div>
						<div class="digirisk_form_input digirisk_attr_role_to_copy_from_input alignleft" >
							<select name="roleToCopy" id="role_to_copy_from" >';
			foreach($digi_wp_role->roles as $roleKey => $roleContent)
			{
				$the_form_general_content .= '
								<option value="' . $roleKey . '" >' . translate_user_role($roleContent['name']) . '</option>';
			}
				$the_form_general_content .= '
							</select>
						</div>
					</div>';
		}
		else
		{
			$the_form_content_hidden .= '<input type="hidden" name="wp_eva__permission_role[id]" id="wp_eva__permission_role_id" value="' . $currentElementId . '" />';
		}

		/*	R�cup�ration des droits affect�s au role en cours d'�dition	*/
		$digiPermissionForm = '';
		$form_button = self::getPageFormButton();
		if($currentElementId != '') {
			$roleInEdition = $digi_wp_role->get_role($currentElementId);
			/*	R�cup�ration du code permettant d'afficher la liste des droits disponible pour le logiciel digirisk	*/
			ob_start();
			self::permission_management($roleInEdition);
			$digiPermissionForm = ob_get_contents();
			ob_end_clean();
			$digiPermissionForm = '
	<fieldset class="clear digiriskUserRoleCapabilitiesDetails" >
		<legend>' . __('Permissions du r&ocirc;le', 'evarisk') . '</legend>
		' . $digiPermissionForm . '
	</fieldset>';

			if ( $currentElementId == 'administrator' ) {
				$form_button = __('La modification du r&ocirc;le administrateur n\'est pas possible', 'evarisk');
			}
		}

		$elementEditionOutput = '
<form action="" method="post" id="' . self::dbTable . '_form" >
	<input type="hidden" name="' . self::dbTable . '_action" id="' . self::dbTable . '_action" value="' . $action . '" />
	' . $the_form_content_hidden . '
	' . $the_form_general_content . '
	' . $digiPermissionForm . '
	<div id="pageHeaderButtonContainer" class="pageHeaderButton" >' . $form_button . '</div>
</form>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		digirisk("#delete").click(function(){
			if(confirm(digi_html_accent_for_js("' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer ce r&ocirc;le ?', 'evarisk') . '"))){
				digirisk("#' . self::dbTable . '_action").val("delete");
				digirisk("#' . self::dbTable . '_form").submit();
			}
		});
	});
</script>';

		return $elementEditionOutput;
	}



	/**
	*	Allows to get the role list added for digirisk
	*
	*	@return object $digiriskRoleList A wordpress database object with the existing role list
	*/
	function digirisk_get_role($id = '', $field = '')
	{
		global $wpdb;

		$moreQuery = "";
		if(($id != '') && ($field != ''))
		{
			$moreQuery .= "
			AND " . $field . " = '" . $id . "' ";
		}

		$query = $wpdb->prepare(
		"SELECT * FROM
		" . DIGI_DBT_PERMISSION_ROLE . "
		WHERE status = 'valid' " . $moreQuery, "");

		$digiriskRoleList = $wpdb->get_results($query);

		return $digiriskRoleList;
	}


	/**
	*	Update user right's. Check if there is a user id send by post method, if it is the case so we launch user rights' update process
	*
	*/
	public static function user_permission_set() {
		/*	V�rification qu'il existe bien un utilisateur � mettre � jour avant d'effectuer une action	*/
		if ( empty($_POST['user_id']) ) return;
		/*	R�cup�ration des informations concernant l'utilisateur en cours d'�dition	*/
		$user = new WP_User($_POST['user_id']);

		/*	R�cup�ration des permissions envoy�es	*/
		$userCapsList = !empty($_POST['digi_permission']) ? $_POST['digi_permission'] : array();

		/*	R�cup�ration des permissions existantes	*/
		$existingPermission = self::permission_list();
		foreach ($existingPermission as $permission => $permission_definition) {
			/*	V�rification de la permission actuelle au cas ou elle serait nulle	*/
			if ($permission != '') {
				/*	Si l'utilisateur poss�de une permission mais que celle ci n'est plus coch�e => Suppression de la permission	*/
				if ( $user->has_cap($permission) && ((!array_key_exists($permission, $userCapsList)) || (isset($userCapsList[$permission]) && ($userCapsList[$permission] != 'yes'))) ) {
					$user->remove_cap($permission);
				}
				/*	Si l'utilisateur ne poss�de pas la permission mais que celle ci est coch�e  => Ajout de la permission	*/
				else if ( !$user->has_cap($permission) && ($userCapsList[$permission] == 'yes')) {
					$user->add_cap($permission);
				}
			}
		}
	}

	/**
	*
	*/
	function userRightPostBox($arguments, $moreArgs = '')
	{
		$idElement = $arguments['idElement'];
		$tableElement = $arguments['tableElement'];

		/*	Script associ� au boutton de sauvegarde	*/
		$scriptEnregistrement = '
<script type="text/javascript">
	digirisk("#save_right_' . $tableElement . '").click(function(){
		digirisk("#saveButtonLoading_userRight' . $tableElement . '").show();
		digirisk("#saveButtonContainer_userRight' . $tableElement . '").hide();

		saveRightForUsers("' . $tableElement . '", "' . $idElement . '", "' . DIGI_DBT_PERMISSION . '", "message_' . $tableElement . '_' . $idElement . '_userRight", "userRightContainerBox");
	});
</script>';

		/*	Ajout de la pop up d'�dition pour les �crans plus petits	*/
		$utilisateursMetaBox = '
<div id="userPermissionManager" class="hide" title="' . __('Droits des utilisateurs', 'evarisk') . '" >
	<div id="rightDialogMessage" class="hide" >&nbsp;</div>
	<div id="userPermissionManagerForm" class="" >&nbsp;</div>
	<div id="userPermissionManagerLoading" class="hide" >&nbsp;</div>
</div>
<div class="hide" id="message_' . $tableElement . '_' . $idElement . '_userRight" ></div>

<div class="clear" >
	<div id="openRightManagerDialog" class="alignright " ><img src="' . DIGI_OPEN_POPUP . '" alt="' . __('Ouvrir dans une fen&ecirc;tre externe', 'evarisk') . '" title="' . __('Ouvrir dans une fen&ecirc;tre externe', 'evarisk') . '" /></div>
</div>

<!--	User list -->
<div id="userRightContainerBox" class="clear" >' . self::generateUserListForRightDatatable($tableElement, $idElement) . '</div>

<!--	Save button -->
<div class="clear" id="saveButtonBoxContainer" >
	<div id="saveButtonLoading_userRight' . $tableElement . '" style="display:none;" class="clear alignright" ><img src="' . PICTO_LOADING_ROUND . '" alt="loading in progress" /></div>
	<div id="saveButtonContainer_userRight' . $tableElement . '" >' . EvaDisplayInput::afficherInput('button', 'save_right_' . $tableElement , __('Enregistrer', 'evarisk'), null, '', 'save', false, true, '', 'button-primary alignright', '', '', $scriptEnregistrement) . '</div>
</div>

<script type="text/javascript" >
	digirisk("#userPermissionManager").dialog({
		autoOpen: false,
		height: 600,
		width: 800,
		modal: true,
		buttons: {
			"' . __('Enregistrer et fermer', 'evarisk') . '": function(){
				saveRightForUsers("' . $tableElement . '", "' . $idElement . '", "' . DIGI_DBT_PERMISSION . '", "message_' . $tableElement . '_' . $idElement . '_userRight", "userRightContainerBox");
				setTimeout(digirisk(this).dialog("close"), \'1000\');
			},
			"' . __('Enregistrer', 'evarisk') . '": function(){
				saveRightForUsers("' . $tableElement . '", "' . $idElement . '", "' . DIGI_DBT_PERMISSION . '", "rightDialogMessage", "userPermissionManagerForm");
			},
			"' . __('Annuler', 'evarisk') . '": function(){
				digirisk(this).dialog("close");
			}
		},
		close: function(){
			digirisk("#userPermissionManagerForm").html("");
			digirisk("#userRightContainerBox").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
				"post": "true",
				"table": "' . DIGI_DBT_PERMISSION . '",
				"act": "reload_user_right_box",
				"tableElement": "' . $tableElement . '",
				"idElement": "' . $idElement . '"
			});
			digirisk("#saveButtonBoxContainer").show();
		}
	});
	digirisk("#openRightManagerDialog").click(function(){
		digirisk("#userRightContainerBox").html("");
		digirisk("#saveButtonBoxContainer").hide();
		digirisk("#userPermissionManagerForm").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
		{
			"post":"true",
			"table":"' . DIGI_DBT_PERMISSION . '",
			"act":"reload_user_right_box",
			"tableElement":"' . $tableElement . '",
			"idElement":"' . $idElement . '"
		});
		digirisk("#userPermissionManager").dialog("open");
	});
</script>';

		echo $utilisateursMetaBox;
	}


	/**
	 *	Create the output for the user list with the different right to affect to the user
	 *
	 *	@param string $tableElement The element type we are editing the right for
	 *	@param integer $idElement The element identifier we are editing the right for
	 *
	 *	@return mixed $outputTable The html output of the user list in a jquery dataTable
	 */
	function generateUserListForRightDatatable($tableElement, $idElement) {
		$outputTable = '';
		$rightType = array('see', 'edit', 'delete', 'add_gpt', 'add_unit', 'add_task', 'add_action');

		/*	Initialisation des variables recevants les listes des droits des utilisateurs d�j� affect�s	*/
		foreach ($rightType as $rightName) {
			$userRightDetail[$rightName] = '';
		}

		/*	on r�cup�re les utilisateurs affect�s � l'�l�ment en cours.	*/
		$listeUtilisateursLies = array();
		$utilisateursLies = evaUserLinkElement::getAffectedUser($tableElement, $idElement);
		if (is_array($utilisateursLies) && (count($utilisateursLies) > 0)) {
			foreach ($utilisateursLies as $utilisateur) {
				$listeUtilisateursLies[$utilisateur->id_user] = $utilisateur;
			}
		}

		{/*	Affichage des racourcis de cochage/d�cochage en masse	*/
			$idTable = 'checkAllInterface' . $tableElement . $idElement;
			unset($titres);
			$titres[] = __('Affect&eacute; &agrave; l\'&eacute;l&eacute;ment', 'evarisk');
			$titres[] = ucfirst(strtolower(__('Nom', 'evarisk')));
			$titres[] = __('Voir', 'evarisk');
			$titres[] = __('&Eacute;diter', 'evarisk');
			$titres[] = __('Supprimer', 'evarisk');
			switch ($tableElement) {
				case TABLE_GROUPEMENT;
					$titres[] = __('Ajouter un groupement', 'evarisk');
					$titres[] = __('Ajouter une unit&eacute;', 'evarisk');
					$titres[] = __('R&eacute;cursif', 'evarisk');
				break;
				case TABLE_TACHE;
					$titres[] = __('Ajouter une t&acirc;che', 'evarisk');
					$titres[] = __('Ajouter une sous-t&acirc;che', 'evarisk');
					$titres[] = __('R&eacute;cursif', 'evarisk');
				break;
			}

			unset($valeurs);
			$valeurs[] = array('value'=>'b');
			$valeurs[] = array('value'=>__('Pour tous les utilisateurs', 'evarisk'), 'class'=>'middleAlign');
			if(!SHOW_PICTURE_FOR_RIGHT_HEADER_MASS_SELECTOR_COLUMN) {
				$valeurs[] = array('value'=>'<span class="checkAll_user_" id="see" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_" id="not_see" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell');
				$valeurs[] = array('value'=>'<span class="checkAll_user_" id="edit" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_" id="not_edit" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell');
				$valeurs[] = array('value'=>'<span class="checkAll_user_" id="delete" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_" id="not_delete" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell');
			}
			else {
				$valeurs[] = array('value'=>'<span class="checkAll_user_" id="see" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /></span><br/><span class="uncheckAll_user_" id="not_see" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell');
				$valeurs[] = array('value'=>'<span class="checkAll_user_" id="edit" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /></span><br/><span class="uncheckAll_user_" id="not_edit" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell');
				$valeurs[] = array('value'=>'<span class="checkAll_user_" id="delete" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /></span><br/><span class="uncheckAll_user_" id="not_delete" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell');
			}
			switch($tableElement) {
				case TABLE_GROUPEMENT;
					if(!SHOW_PICTURE_FOR_RIGHT_HEADER_MASS_SELECTOR_COLUMN) {
						$valeurs[] = array('value'=>'<span class="checkAll_user_" id="add_groupement" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_" id="not_add_groupement" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell');
						$valeurs[] = array('value'=>'<span class="checkAll_user_" id="add_unite" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_" id="not_add_unite" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell');
						$valeurs[] = array('value'=>'<span class="checkAll_user_" id="recursif" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_" id="recursif" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell');
					}
					else {
						$valeurs[] = array('value'=>'<span class="checkAll_user_" id="add_groupement" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /></span><br/><span class="uncheckAll_user_" id="not_add_groupement" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell');
						$valeurs[] = array('value'=>'<span class="checkAll_user_" id="add_unite" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /></span><br/><span class="uncheckAll_user_" id="not_add_unite" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell');
						$valeurs[] = array('value'=>'<span class="checkAll_user_" id="recursif" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /></span><br/><span class="uncheckAll_user_" id="recursif" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell');
					}
				break;

				case TABLE_TACHE;
					if (!SHOW_PICTURE_FOR_RIGHT_HEADER_MASS_SELECTOR_COLUMN) {
						$valeurs[] = array('value'=>'<span class="checkAll_user_" id="add_task" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_" id="not_add_task" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell');
						$valeurs[] = array('value'=>'<span class="checkAll_user_" id="add_action" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_" id="not_add_action" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell');
						$valeurs[] = array('value'=>'<span class="checkAll_user_" id="task_recursif" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_" id="not_task_recursif" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell');
					}
					else {
						$valeurs[] = array('value'=>'<span class="checkAll_user_" id="add_task" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /><br/></span><span class="uncheckAll_user_" id="not_add_task" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell');
						$valeurs[] = array('value'=>'<span class="checkAll_user_" id="add_action" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /></span><br/><span class="uncheckAll_user_" id="not_add_action" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell');
						$valeurs[] = array('value'=>'<span class="checkAll_user_" id="task_recursif" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /></span><br/><span class="uncheckAll_user_" id="not_task_recursif" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell');
					}
				break;
			}
			$lignesDeValeurs[] = $valeurs;
			$idLignes[] = $tableElement . $idElement . 'listeUtilisateursDroits';

			if(count($listeUtilisateursLies) > 0) {
				unset($valeurs);
				$valeurs[] = array('value'=>'a');
				$valeurs[] = array('value'=>__('Pour tous les utilisateurs affect&eacute;s', 'evarisk'), 'class'=>'userAffecte middleAlign');
				if(!SHOW_PICTURE_FOR_RIGHT_HEADER_MASS_SELECTOR_COLUMN) {
					$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_see" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_affected_" id="not_a_see" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell userAffecte');
					$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_edit" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_affected_" id="not_a_edit" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell userAffecte');
					$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_delete" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_affected_" id="not_a_delete" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell userAffecte');
				}
				else
				{
					$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_see" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /></span><br/><span class="uncheckAll_user_affected_" id="not_a_see" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell userAffecte');
					$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_edit" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /></span><br/><span class="uncheckAll_user_affected_" id="not_a_edit" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell userAffecte');
					$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_delete" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /></span><br/><span class="uncheckAll_user_affected_" id="not_a_delete" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell userAffecte');
				}
				switch ($tableElement) {
					case TABLE_GROUPEMENT;
						if(!SHOW_PICTURE_FOR_RIGHT_HEADER_MASS_SELECTOR_COLUMN) {
							$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_add_groupement" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_affected_" id="not_a_add_groupement" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell userAffecte');
							$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_add_unite" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_affected_" id="not_a_add_unite" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell userAffecte');
							$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_recursif" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_affected_" id="not_a_recursif" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell userAffecte');
						}
						else {
							$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_add_groupement" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /><br/></span><span class="uncheckAll_user_affected_" id="not_a_add_groupement" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell userAffecte');
							$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_add_unite" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /></span><br/><span class="uncheckAll_user_affected_" id="not_a_add_unite" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell userAffecte');
							$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_recursif" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /></span><br/><span class="uncheckAll_user_affected_" id="not_a_recursif" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell userAffecte');
						}
					break;

					case TABLE_TACHE;
						if (!SHOW_PICTURE_FOR_RIGHT_HEADER_MASS_SELECTOR_COLUMN) {
							$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_add_task" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_affected_" id="not_a_add_task" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell userAffecte');
							$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_add_action" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_affected_" id="not_a_add_action" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell userAffecte');
							$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_task_recursif" >' . __('Tous', 'evarisk') . '</span>&nbsp;/&nbsp;<span class="uncheckAll_user_affected_" id="not_a_task_recursif" >' . __('Aucun', 'evarisk') . '</span>', 'class'=>'rightCell userAffecte');
						}
						else {
							$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_add_task" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /><br/></span><span class="uncheckAll_user_affected_" id="not_a_add_task" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell userAffecte');
							$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_add_action" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /></span><br/><span class="uncheckAll_user_affected_" id="not_a_add_action" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell userAffecte');
							$valeurs[] = array('value'=>'<span class="checkAll_user_affected_" id="a_task_recursif" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_ALL) . '" alt="' . __('Tous', 'evarisk') . '" title="' . __('Tous', 'evarisk') . '" /></span><br/><span class="uncheckAll_user_affected_" id="not_a_task_recursif" ><img src="' . str_replace('.png', '_vs.png', DIGI_USER_SELECT_NOBODY) . '" alt="' . __('Aucun', 'evarisk') . '" title="' . __('Aucun', 'evarisk') . '" /></span>', 'class'=>'rightCell userAffecte');
						}
					break;
				}
				$lignesDeValeurs[] = $valeurs;
				$idLignes[] = $tableElement . $idElement . 'listeUtilisateursAffectesDroit';
			}

			$classes = array('','','rightColumn','rightColumn','rightColumn');
			$script = '
<script type="text/javascript">
	digirisk(document).ready(function(){
		digirisk(".checkAll_user_").click(function(){
			var rightToManage = digirisk(this).attr("id");
			digirisk("." + rightToManage).each(function(){
				if(!digirisk(this).prop("disabled")){
					digirisk(this).prop("checked", true);
				}
			});
		});
		digirisk(".uncheckAll_user_").click(function(){
			var rightToManage = digirisk(this).attr("id").replace("not_", "");
			digirisk("." + rightToManage).each(function(){
				if(!digirisk(this).prop("disabled")){
					digirisk(this).prop("checked", false);
				}
			});
		});
		digirisk(".checkAll_user_affected_").click(function(){
			var rightToManage = digirisk(this).attr("id").replace("a_", "");
			digirisk(".userAffecte ." + rightToManage).each(function(){
				if(!digirisk(this).prop("disabled")){
					digirisk(this).prop("checked", true);
				}
			});
		});
		digirisk(".uncheckAll_user_affected_").click(function(){
			var rightToManage = digirisk(this).attr("id").replace("not_a_", "");
			digirisk(".userAffecte ." + rightToManage).each(function(){
				if(!digirisk(this).prop("disabled")){
					digirisk(this).prop("checked", false);
				}
			});
		});
		digirisk("#' . $idTable . '").dataTable({
			"bAutoWidth": false,
			"bInfo": false,
			"aaSorting": [[0, "asc"]],
			"sScrollY": "200px",
			"bPaginate": false,
			"bFilter": false,
			"aoColumns": [
				{ "bVisible": false},
				null,
				null,
				null,';
			switch($tableElement)
			{
				case TABLE_GROUPEMENT;
					$script .= '
				null,
				null,
				null,';
				$classes[] = 'rightColumn';
				$classes[] = 'rightColumn';
				$classes[] = 'rightColumn';
				break;
				case TABLE_TACHE;
				$script .= '
				null,
				null,
				null,';
				$classes[] = 'rightColumn';
				$classes[] = 'rightColumn';
				$classes[] = 'rightColumn';
				break;
			}
				$script .= '
				null
			],
			"oLanguage":{
				"sLengthMenu": "' . sprintf(__('Afficher %s enregistrements', 'evarisk'), '_MENU_') . '",
				"sZeroRecords": "' . __('Aucun r&eacute;sultat', 'evarisk') . '",
				"sSearch": "<span class=\'ui-icon searchDataTableIcon\' >&nbsp;</span>"
			}
		});
		digirisk("#' . $idTable . '_wrapper").removeClass("dataTables_wrapper");
		digirisk("#' . $idTable . ' tfoot").remove();
		digirisk("#' . $idTable . ' thead").remove();
	});
</script>';
			$checkAllTable = evaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script);
		}
		{/*	Affichage de la liste des utilisateurs	*/
		$idTable = 'listeIndividusPourDroits' . $tableElement . $idElement;
		unset($titres);
		$titres[] = __('Affect&eacute; &agrave; l\'&eacute;l&eacute;ment', 'evarisk');
		$titres[] = ucfirst(strtolower(__('Id.', 'evarisk')));
		$titres[] = ucfirst(strtolower(__('Nom', 'evarisk')));
		$titres[] = ucfirst(strtolower(__('Pr&eacute;nom', 'evarisk')));
		if (!SHOW_PICTURE_FOR_RIGHT_HEADER_COLUMN) {
			$titres[] = __('Voir', 'evarisk');
			$titres[] = __('&Eacute;diter', 'evarisk');
			$titres[] = __('Supprimer', 'evarisk');
		}
		else {
			$titres[] = '<img src="' . str_replace('.png', '_vs.png', PICTO_VIEW) . '" alt="' . __('Voir', 'evarisk') . '" title="' . __('Voir', 'evarisk') . '" />';
			$titres[] = '<img src="' . str_replace('.png', '_vs.png', PICTO_EDIT) . '" alt="' . __('&Eacute;diter', 'evarisk') . '" title="' . __('&Eacute;diter', 'evarisk') . '" />';
			$titres[] = '<img src="' . str_replace('.png', '_vs.png', PICTO_DELETE) . '" alt="' . __('Supprimer', 'evarisk') . '" title="' . __('Supprimer', 'evarisk') . '" />';
		}
		switch($tableElement) {
			case TABLE_GROUPEMENT;
				/*	Add button for groupement or unit adding	*/
				if(!SHOW_PICTURE_FOR_RIGHT_HEADER_COLUMN) {
					$titres[] = __('Ajouter un groupement', 'evarisk');
					$titres[] = __('Ajouter une unit&eacute;', 'evarisk');
					$titres[] = ucfirst(strtolower(__('R&eacute;cursif', 'evarisk')));
				}
				else {
					$titres[] = '<img src="' . str_replace('.png', '_vs.png', PICTO_LTL_ADD_GROUPEMENT) . '" alt="' . __('Ajouter un groupement', 'evarisk') . '" title="' . __('Ajouter un groupement', 'evarisk') . '" />';
					$titres[] = '<img src="' . str_replace('.png', '_vs.png', PICTO_LTL_ADD_UNIT) . '" alt="' . __('Ajouter une unit&eacute;', 'evarisk') . '" title="' . __('Ajouter une unit&eacute;', 'evarisk') . '" />';
					$titres[] = '<img src="' . str_replace('.png', '_vs.png', DIGI_RECURSE) . '" alt="' . __('R&eacute;cursif', 'evarisk') . '" title="' . __('R&eacute;cursif', 'evarisk') . '" />';
				}
			break;

			case TABLE_TACHE;
				/*	Add button for groupement or unit adding	*/
				if(!SHOW_PICTURE_FOR_RIGHT_HEADER_COLUMN) {
					$titres[] = __('Ajouter une t&acirc;che', 'evarisk');
					$titres[] = __('Ajouter une sous-t&acirc;che', 'evarisk');
					$titres[] = ucfirst(strtolower(__('R&eacute;cursif', 'evarisk')));
				}
				else {
					$titres[] = '<img src="' . str_replace('_s.png', '_vs.png', PICTO_LTL_ADD_TACHE) . '" alt="' . __('Ajouter une t&acirc;che', 'evarisk') . '" title="' . __('Ajouter une t&acirc;che', 'evarisk') . '" />';
					$titres[] = '<img src="' . str_replace('_s.png', '_vs.png', PICTO_LTL_ADD_ACTIVITE) . '" alt="' . __('Ajouter une sous-t&acirc;che', 'evarisk') . '" title="' . __('Ajouter une sous-t&acirc;che', 'evarisk') . '" />';
					$titres[] = '<img src="' . str_replace('.png', '_vs.png', DIGI_RECURSE) . '" alt="' . __('R&eacute;cursif', 'evarisk') . '" title="' . __('R&eacute;cursif', 'evarisk') . '" />';
				}
			break;
		}
		unset($lignesDeValeurs);

		/*	On lit la liste des utilisateurs si elle n'est pas vide	*/
		$listeUtilisateurs = evaUser::getCompleteUserList();
		if(is_array($listeUtilisateurs) && (count($listeUtilisateurs) > 0))
		{
			foreach($listeUtilisateurs as $utilisateur)
			{
				$user = new WP_User($utilisateur['user_id']);

				unset($valeurs);
				$idLigne = $tableElement . $idElement . 'listeUtilisateurs' . $utilisateur['user_id'];
				$idCbLigne = 'cb_' . $idLigne;

				$utilisateurAffecteClass = '';
				$utilisateurAffecte = ucfirst(strtolower(__('non', 'evarisk')));
				if(array_key_exists($utilisateur['user_id'], $listeUtilisateursLies))
				{
					$utilisateurAffecteClass = 'userAffecte';
					$utilisateurAffecte = ucfirst(strtolower(__('oui', 'evarisk')));
				}

				$valeurs[] = array('value'=>$utilisateurAffecte, 'class'=>$utilisateurAffecteClass);
				$valeurs[] = array('value'=>ELEMENT_IDENTIFIER_U . $utilisateur['user_id'], 'class'=>$utilisateurAffecteClass);
				$valeurs[] = array('value'=>$utilisateur['user_lastname'], 'class'=>$utilisateurAffecteClass);
				$valeurs[] = array('value'=>$utilisateur['user_firstname'], 'class'=>$utilisateurAffecteClass);
				switch ($tableElement) {
					case TABLE_GROUPEMENT;
						$endPermission = 'groupement';
					break;
					case TABLE_UNITE_TRAVAIL;
						$endPermission = 'unite';
					break;
					case TABLE_TACHE:
						$endPermission = 'task';
					break;
					case TABLE_TACHE:
						$endPermission = 'action';
					break;
					default:
						$endPermission = '';
					break;
				}
				$viewCheckBox = '';
				if($user->has_cap('digi_view_detail_' . $endPermission) || $user->has_cap('digi_view_detail_' . $endPermission . '_' . $idElement))
				{
					$viewCheckBox = ' checked="checked" ';
					if($user->has_cap('digi_view_detail_' . $endPermission))
					{
						$viewCheckBox .= ' disabled="disabled" ';
					}
					$userRightDetail['see'] .= 'digi_view_detail_' . $endPermission . '_' . $idElement . '!#!' . $utilisateur['user_id'] . '#!#';
				}
				$valeurs[] = array('class'=>'rightCell ' . $utilisateurAffecteClass, 'value'=>'<input type="checkbox" name="user_see[' . $tableElement . '_' . $idElement . '_' . $utilisateur['user_id'] . ']" value="digi_view_detail_' . $endPermission . '_' . $idElement . '" id="user_see_' . $utilisateur['user_id'] . '" class="see" ' . $viewCheckBox . ' />');
				$editCheckBox = '';
				if($user->has_cap('digi_edit_' . $endPermission) || $user->has_cap('digi_edit_' . $endPermission . '_' . $idElement))
				{
					$editCheckBox = ' checked="checked" ';
					if($user->has_cap('digi_edit_' . $endPermission))
					{
						$editCheckBox .= ' disabled="disabled" ';
					}
					$userRightDetail['edit'] .= 'digi_edit_' . $endPermission . '_' . $idElement . '!#!' . $utilisateur['user_id'] . '#!#';
				}
				$valeurs[] = array('class'=>'rightCell ' . $utilisateurAffecteClass, 'value'=>'<input type="checkbox" name="user_edit[' . $tableElement . '_' . $idElement . '_' . $utilisateur['user_id'] . ']" value="digi_edit_' . $endPermission . '_' . $idElement . '" id="user_edit_' . $utilisateur['user_id'] . '" class="edit" ' . $editCheckBox . ' />');
				$deleteCheckBox = '';
				if($user->has_cap('digi_delete_' . $endPermission) || $user->has_cap('digi_delete_' . $endPermission . '_' . $idElement))
				{
					$deleteCheckBox = ' checked="checked" ';
					if($user->has_cap('digi_delete_' . $endPermission))
					{
						$deleteCheckBox .= ' disabled="disabled" ';
					}
					$userRightDetail['delete'] .= 'digi_delete_' . $endPermission . '_' . $idElement . '!#!' . $utilisateur['user_id'] . '#!#';
				}
				$valeurs[] = array('class'=>'rightCell ' . $utilisateurAffecteClass, 'value'=>'<input type="checkbox" name="user_delete[' . $tableElement . '_' . $idElement . '_' . $utilisateur['user_id'] . ']" value="digi_delete_' . $endPermission . '_' . $idElement . '" id="user_delete_' . $utilisateur['user_id'] . '" class="delete" ' . $deleteCheckBox . ' />');
				switch($tableElement)
				{
					case TABLE_GROUPEMENT;
						/*	Add button for groupement or unit adding	*/
						$viewCheckBox = '';
						if($user->has_cap('digi_add_groupement') || $user->has_cap('digi_add_groupement_' . $endPermission . '_' . $idElement))
						{
							$viewCheckBox = ' checked="checked" ';
							if($user->has_cap('digi_add_groupement'))
							{
								$viewCheckBox .= ' disabled="disabled" ';
							}
							$userRightDetail['add_gpt'] .= 'digi_add_groupement_' . $endPermission . '_' . $idElement . '!#!' . $utilisateur['user_id'] . '#!#';
						}
						$valeurs[] = array('class'=>'rightCell ' . $utilisateurAffecteClass, 'value'=>'<input type="checkbox" name="user_add_gpt[' . $tableElement . '_' . $idElement . '_' . $utilisateur['user_id'] . ']" value="digi_add_groupement_' . $endPermission . '_' . $idElement . '" id="user_add_gpt' . $utilisateur['user_id'] . '" class="add_groupement" ' . $viewCheckBox . ' />');
						$viewCheckBox = '';
						if($user->has_cap('digi_add_unite') || $user->has_cap('digi_add_unite_' . $endPermission . '_' . $idElement))
						{
							$viewCheckBox = ' checked="checked" ';
							if($user->has_cap('digi_add_unite'))
							{
								$viewCheckBox .= ' disabled="disabled" ';
							}
							$userRightDetail['add_unit'] .= 'digi_add_unite_' . $endPermission . '_' . $idElement . '!#!' . $utilisateur['user_id'] . '#!#';
						}
						$valeurs[] = array('class'=>'rightCell ' . $utilisateurAffecteClass, 'value'=>'<input type="checkbox" name="user_add_unit[' . $tableElement . '_' . $idElement . '_' . $utilisateur['user_id'] . ']" value="digi_add_unite_' . $endPermission . '_' . $idElement . '" id="user_add_unit' . $utilisateur['user_id'] . '" class="add_unite" ' . $viewCheckBox . ' />');
						$valeurs[] = array('class'=>'rightCell ' . $utilisateurAffecteClass, 'value'=>'<input type="checkbox" name="user_recursif[' . $tableElement . '_' . $idElement . '_' . $utilisateur['user_id'] . ']" value="digi_recursif_' . $endPermission . '_' . $idElement . '" id="user_recursif' . $utilisateur['user_id'] . '" class="recursif" />');
					break;

					case TABLE_TACHE;
						/*	Add button for groupement or unit adding	*/
						$viewCheckBox = '';
						if($user->has_cap('digi_add_task') || $user->has_cap('digi_add_task_' . $endPermission . '_' . $idElement)) {
							$viewCheckBox = ' checked="checked" ';
							if ($user->has_cap('digi_add_task')) {
								$viewCheckBox .= ' disabled="disabled" ';
							}
							$userRightDetail['add_task'] .= 'digi_add_task_' . $endPermission . '_' . $idElement . '!#!' . $utilisateur['user_id'] . '#!#';
						}
						$valeurs[] = array('class'=>'rightCell ' . $utilisateurAffecteClass, 'value'=>'<input type="checkbox" name="user_add_task[' . $tableElement . '_' . $idElement . '_' . $utilisateur['user_id'] . ']" value="digi_add_task_' . $endPermission . '_' . $idElement . '" id="user_add_task' . $utilisateur['user_id'] . '" class="add_task" ' . $viewCheckBox . ' />');
						$viewCheckBox = '';
						if($user->has_cap('digi_add_action') || $user->has_cap('digi_add_action_' . $endPermission . '_' . $idElement)) {
							$viewCheckBox = ' checked="checked" ';
							if($user->has_cap('digi_add_action')) {
								$viewCheckBox .= ' disabled="disabled" ';
							}
							$userRightDetail['add_action'] .= 'digi_add_action_' . $endPermission . '_' . $idElement . '!#!' . $utilisateur['user_id'] . '#!#';
						}
						$valeurs[] = array('class'=>'rightCell ' . $utilisateurAffecteClass, 'value'=>'<input type="checkbox" name="user_add_action[' . $tableElement . '_' . $idElement . '_' . $utilisateur['user_id'] . ']" value="digi_add_action_' . $endPermission . '_' . $idElement . '" id="user_add_action' . $utilisateur['user_id'] . '" class="add_action" ' . $viewCheckBox . ' />');
						$valeurs[] = array('class'=>'rightCell ' . $utilisateurAffecteClass, 'value'=>'<input type="checkbox" name="user_recursif[' . $tableElement . '_' . $idElement . '_' . $utilisateur['user_id'] . ']" value="digi_recursif_' . $endPermission . '_' . $idElement . '" id="user_recursif' . $utilisateur['user_id'] . '" class="recursif" />');
					break;
				}

				$lignesDeValeurs[] = $valeurs;
				$idLignes[] = $idLigne;
			}
		}
		else
		{
			unset($valeurs);
			$valeurs[] = array('value'=>'');
			$valeurs[] = array('value'=>'');
			$valeurs[] = array('value'=>__('Aucun r&eacute;sultat trouv&eacute;', 'evarisk'));
			$valeurs[] = array('value'=>'');
			$valeurs[] = array('value'=>'');
			$valeurs[] = array('value'=>'');
			$valeurs[] = array('value'=>'');

			switch($tableElement)
			{
				case TABLE_GROUPEMENT;
					$valeurs[] = array('value'=>'');
					$valeurs[] = array('value'=>'');
					$valeurs[] = array('value'=>'');
				break;

				case TABLE_TACHE;
					$valeurs[] = array('value'=>'');
					$valeurs[] = array('value'=>'');
					$valeurs[] = array('value'=>'');
				break;
			}

			$lignesDeValeurs[] = $valeurs;
			$idLignes[] = $tableElement . $idElement . 'listeUtilisateursVide';
		}

		$classes = array('','userIdentifierColumn','middleAlign','middleAlign','rightColumn','rightColumn','rightColumn');
		$script = '
<script type="text/javascript">
	digirisk(document).ready(function(){
		digirisk("#' . $idTable . '").dataTable({
			"bAutoWidth": false,
			"bInfo": false,
			"aaSorting": [[0, "desc"]],
			"sScrollY": "200px",
			"bPaginate": false,
			"aoColumns": [
				{ "bVisible": false},
				null,
				null,
				null,
				{ "bSortable": false},
				{ "bSortable": false},';
			switch($tableElement)
			{
				case TABLE_GROUPEMENT;
					$script .= '
				{ "bSortable": false},
				{ "bSortable": false},
				{ "bSortable": false},';
				$classes[] = 'rightColumn';
				$classes[] = 'rightColumn';
				$classes[] = 'rightColumn';
				break;
				case TABLE_TACHE;
				$script .= '
				{ "bSortable": false},
				{ "bSortable": false},
				{ "bSortable": false},';
				$classes[] = 'rightColumn';
				$classes[] = 'rightColumn';
				$classes[] = 'rightColumn';
				break;
			}
				$script .= '
				{ "bSortable": false}
			],
			"oLanguage":{
				"sLengthMenu": "' . sprintf(__('Afficher %s enregistrements', 'evarisk'), '_MENU_') . '",
				"sZeroRecords": "' . __('Aucun r&eacute;sultat', 'evarisk') . '",
				"sSearch": "<span class=\'ui-icon searchDataTableIcon\' >&nbsp;</span>"
			}
		});
	});
</script>';
			$userListTable = evaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script);
		}

		/*	Ajout de la liste des droits d�j� affect�s aux utilisateurs et des nouveaux droits en cours d'affectation	*/
		foreach($rightType as $rightName) {
			$outputTable .= '
<input type="hidden" name="userRightDetail_' . $rightName . '" id="userRightDetail_' . $rightName . '" value="" />';
			$outputTable .= '
<input type="hidden" name="userRightDetail_' . $rightName . '_old" id="userRightDetail_' . $rightName . '_old" value="' . $userRightDetail[$rightName] . '" />';
		}
		$outputTable .= '
<input type="hidden" name="userRightDetail_recursif" id="userRightDetail_recursif" value="" />';

		$outputTable .= '
<fieldset class="clear" >
	<legend>' . __('L&eacute;gende', 'evarisk') . '</legend>';
		if(count($listeUtilisateursLies) > 0)
		{
			$outputTable .= '
	<div class="userAffecte userAffecteExplanation" >' . __('Utilisateurs affect&eacute;s &agrave; l\'&eacute;l&eacute;ment en cours d\'&eacute;dition', 'evarisk') . '</div>';
		}
		$outputTable .= '
	<input type="checkbox" name="explanationBoxDisabled" id="explanationBoxDisabled" value="" checked="checked" disabled="disabled" />&nbsp;' . __('Le droit provient du r&ocirc;le de l\'utilisateur et ne peut &ecirc;tre supprim&eacute; depuis cette interface', 'evarisk') . '
</fieldset>
<div class="clear userRightMassManagement userRightManagement_overflow" >
	' . $checkAllTable . '
</div>
<div class="userTableContainer clear userRightManagement_overflow" >
	' . $userListTable . '
</div>';

		return $outputTable;
	}



	/**
	 *	Define the permission that was create at the plugin beginning. From version 5.1.3.1 is used for delete existing right
	 *	@deprecated deprecated since version 5.1.3.1
	 *
	 *	@return array The different right previously added by the plugin (before version 5.1.3.1)
	 */
	public static function getDroitdigirisk()
	{
		return array(
			'Evarisk_:_utiliser_le_plugin' => __('utiliser le plugin','evarisk'),
			'Evarisk_:_voir_les_groupements' => sprintf(__('voir %s','evarisk'), __('les groupements','evarisk')),
			'Evarisk_:_voir_son_groupement' => sprintf(__('voir %s','evarisk'), __('son groupement','evarisk')),
			'Evarisk_:_voir_les_unites' => sprintf(__('voir %s','evarisk'), __('les unit&eacute;s de travail','evarisk')),
			'Evarisk_:_voir_son_unite' => sprintf(__('voir %s','evarisk'), __('son unit&eacute; de travail','evarisk')),
			'Evarisk_:_voir_les_dangers' => sprintf(__('voir %s','evarisk'), __('les dangers','evarisk')),
			'Evarisk_:_voir_les_methodes' => sprintf(__('voir %s','evarisk'), __('les m&eacute;thodes d\'&eacute;valuation','evarisk')),
			'Evarisk_:_voir_les_risques' =>sprintf(__('voir %s','evarisk'), __('les risques','evarisk')),
			'Evarisk_:_voir_les_veilles' => sprintf(__('voir %s','evarisk'), __('les veilles','evarisk')),
			'Evarisk_:_voir_les_actions' => sprintf(__('voir %s','evarisk'), __('les actions correctives','evarisk')),
			'Evarisk_:_editer_les_groupements' => sprintf(__('&eacute;diter %s','evarisk'), __('les groupements','evarisk')),
			'Evarisk_:_editer_les_unites' => sprintf(__('&eacute;diter %s','evarisk'), __('les unit&eacute;s de travail','evarisk')),
			'Evarisk_:_editer_les_dangers' => sprintf(__('&eacute;diter %s','evarisk'), __('les dangers','evarisk')),
			'Evarisk_:_editer_les_methodes' => sprintf(__('&eacute;diter %s','evarisk'), __('les methodes','evarisk')),
			'Evarisk_:_editer_les_risques' => sprintf(__('&eacute;diter %s','evarisk'), __('les risques','evarisk')),
			'Evarisk_:_editer_les_veilles' => sprintf(__('&eacute;diter %s','evarisk'), __('les veilles','evarisk')),
			'Evarisk_:_creer_referenciel' => sprintf(__('cr&eacute;er %s','evarisk'), __('des r&eacute;f&eacute;renciels','evarisk')),
			'Evarisk_:_gerer_attributs' => sprintf(__('g&eacute;rer %s','evarisk'), __('les attributs','evarisk')),
			'Evarisk_:_gerer_groupes_utilisateurs' => sprintf(__('g&eacute;rer %s','evarisk'), __('les groupes d\'utilisateurs','evarisk')),
			'Evarisk_:_gerer_droit_d_acces' => sprintf(__('g&eacute;rer %s','evarisk'), __('les droits d\'acc&egrave;s','evarisk')),
			'Evarisk_:_gerer_groupes_evaluateurs' => sprintf(__('g&eacute;rer %s','evarisk'), __('les groupes d\'&eacute;valuateurs','evarisk')),
			'Evarisk_:_gerer_utilisateurs' => sprintf(__('g&eacute;rer %s','evarisk'), __('les utilisateurs','evarisk')),
			'Evarisk_:_editer_les_options' => sprintf(__('g&eacute;rer %s','evarisk'), __('les options','evarisk')),
			'Evarisk_:_voir_les_preconisations' => sprintf(__('voir %s','evarisk'), __('les pr&eacute;conisations','evarisk'))
		);
	}


	/**
	 *
	 */
	function addRecursivRight($tableElement, $idElement, $userToAssociateRight, $rightToAssociate, $associationType)
	{
		$completeTree = Arborescence::completeTree($tableElement, $idElement);
		if( is_array($completeTree) )
		{
			foreach($completeTree as $key => $content)
			{
				if( isset($content['nom']) )
				{
					switch($tableElement)
					{
						case TABLE_GROUPEMENT:
							$elementType = 'groupement';
						break;
						case TABLE_UNITE_TRAVAIL:
							$elementType = 'unite';
						break;
					}
					$right = $rightToAssociate . $elementType . '_' . $idElement;
					if(($associationType == 'add') && !$userToAssociateRight->has_cap($right))
					{
						$userToAssociateRight->add_cap($right);
					}
					if(($associationType == 'remove') && $userToAssociateRight->has_cap($right))
					{
						$userToAssociateRight->remove_cap($right);
					}
				}

				if(isset($content['content']) && is_array($content['content']))
				{
					foreach($content['content'] as $index => $subContent)
					{
						if( isset($subContent['table']) && isset($subContent['id']) )
						{
							$completeTree[$key]['content'][$index] = self::addRecursivRight($subContent['table'], $subContent['id'], $userToAssociateRight, $rightToAssociate, $associationType);
						}
						else
						{
							foreach($subContent as $subContentIndex => $subContentContent)
							{
								switch($subContentContent['table'])
								{
									case TABLE_GROUPEMENT:
										$elementType = 'groupement';
									break;
									case TABLE_UNITE_TRAVAIL:
										$elementType = 'unite';
									break;
								}
								$right = $rightToAssociate . $elementType . '_' . $subContentContent['id'];
								if(($associationType == 'add') && !$userToAssociateRight->has_cap($right))
								{
									$userToAssociateRight->add_cap($right);
								}
								if(($associationType == 'remove') && $userToAssociateRight->has_cap($right))
								{
									$userToAssociateRight->remove_cap($right);
								}
							}
						}
					}
				}
			}
		}

		return $completeTree;
	}


	/**
	 *	Output an html table with the specific permission for the user into the element tree
	 */
	function digiSpecificPermission($user)
	{
		global $digi_wp_role;

		$specificPermission = array();

		foreach($user->caps as $cap => $value)
		{
			if(!$digi_wp_role->is_role($cap) && (substr($cap, 0, 5) == 'digi_'))
			{
				/*	On explose la permission pour r�cup�rer les diff�rentes informations	*/
				$permissionDetail = explode('_', $cap);
				/*	Comptage du nombre de partie dans la permission	*/
				$nbElementPermission = count($permissionDetail);

				/*	On r�cup�re le type d'�l�ment auquel le droit est associ�, le type de l'�l�ment est toujours en avant derni�re position	*/
				switch($permissionDetail[$nbElementPermission - 2]){
					case 'unite':
						$tableElement = TABLE_UNITE_TRAVAIL;
					break;
					case 'groupement':
						$tableElement = TABLE_GROUPEMENT;
					break;
					default:
						$tableElement = '';
					break;
				}

				/*	Suppression des �l�ments inutiles dans le nom de la permission pour garder uniquement les parties permettant de g�n�rer les noms des �l�ments	*/
				$permission = str_replace('digi_', '', $cap);
				$permission = str_replace('_' . $permissionDetail[$nbElementPermission - 2] . '_', '', $permission);
				$permission = str_replace($permissionDetail[$nbElementPermission - 1], '', $permission);

				$specificPermission[$permissionDetail[$nbElementPermission - 2]]['table'] = $tableElement;
				$specificPermission[$permissionDetail[$nbElementPermission - 2]][$permissionDetail[$nbElementPermission - 1]][] = $permission;
			}
		}

?>
<span class="digi_permission_check_all" ><?php _e('Ces droits sont d&eacute;finis sur chaque &eacute;l&eacute;ment de l\'arbre dans le menu "&Eacute;valuation des risques"', 'evarisk'); ?></span>
<table class="form-table" >
	<tr>
		<th>&nbsp;</th>
		<td>
<?php
			foreach($specificPermission as $elementType => $elementTypeDetails)
			{
?>
			<div class="sub_module <?php echo ($elementType != '') ? 'permission_module_' . $elementType : ''; ?>" >
				<div class="sub_module_name" >
<?php
				_e('permission_' . $elementType, 'evarisk');
?>
				</div>
				<div class="sub_module_content" >
<?php
				/*	Liste des permissions pour le module et le sous-module	*/
				foreach($elementTypeDetails as $elementIdentifier => $elementDetails)
				{
					$elementInformations = '';
					$elementPrefix = '';
					switch($elementTypeDetails['table'])
					{
						case TABLE_UNITE_TRAVAIL:
							$elementInformations = eva_UniteDeTravail::getWorkingUnit($elementIdentifier);
							$elementPrefix = 'UT' . $elementIdentifier;
						break;
						case TABLE_GROUPEMENT:
							$elementInformations = EvaGroupement::getGroupement($elementIdentifier);
							$elementPrefix = 'GP' . $elementIdentifier;
						break;
					}

					/*		*/
					if($elementInformations != '')
					{
						if($elementIdentifier != 'table')
						{
							$permissionName = '';
							foreach($elementDetails as $permission)
							{
								if($permissionName != '')
								{
									$permissionName .= '<br/>';
								}
								$permissionName .= '&nbsp;&nbsp;-&nbsp;';
								switch($permission)
								{
									case 'edit':
										$permissionName .= __('&Eacute;diter', 'evarisk');
									break;
									case 'add':
										$permissionName .= __('Ajouter', 'evarisk');
									break;
									case 'delete':
										$permissionName .= __('Supprimer', 'evarisk');
									break;
									case 'view_detail':
										$permissionName .= __('Voir', 'evarisk');
									break;
								}
							}
							if($permissionName != '')
							{
								$permissionName = '<br/>' . $permissionName;
							}
							echo $elementPrefix . '&nbsp;-&nbsp;' . $elementInformations->nom . $permissionName . '<br/><br/>';
						}
					}
				}
?>
				</div>
			</div>
<?php
			}
?>
		</td>
	</tr>
</table>
<?php
	}

	/**
	*	Output the html table with the permission list stored by module and sub-module
	*/
	function permission_management($elementToManage, $interface_provenance = ''){
		global $digi_wp_role;
		if(!is_object($digi_wp_role)){
			/*	Instanciation de l'objet role de worpdress	*/
			$digi_wp_role = new WP_Roles();
		}
		$permissionList = array();
		$permissionCap = array();

		/*	R�cup�ration des permissions cr��es pour rangement par module	*/
		$existingPermission = self::permission_list();
		foreach($existingPermission as $permission => $permission_definition){
			$permissionList[$permission_definition['permission_module']][$permission_definition['permission_sub_module']][] = $permission;
			$permissionCap[$permission]['type'] = $permission_definition['permission_type'];
			$permissionCap[$permission]['subtype'] = $permission_definition['permission_sub_type'];
		}
?>
<table class="form-table" id="digi-user-right-table" >
<?php
		if(!empty($_REQUEST['user_id'])){
?>
	<tr>
		<td><?php _e('L&eacute;gende', 'evarisk'); ?></td>
		<td>
			<span class="permissionGrantedFromParent" ><?php if($interface_provenance == 'digi_user_profile'){ ?><img src="<?php echo admin_url('images/yes.png'); ?>" alt="user right affectation" class="middleAlign" /><?php }else{ ?><input type="checkbox" name="explanationBoxDisabled" id="explanationBoxDisabled" value="" checked="checked" disabled="disabled" /><?php } ?>&nbsp;<?php _e('Le droit provient du r&ocirc;le de l\'utilisateur et ne peut &ecirc;tre supprim&eacute; depuis cette interface', 'evarisk'); ?></span><br/>
			<span class="permissionGranted" ><?php if($interface_provenance == 'digi_user_profile'){ ?><img src="<?php echo admin_url('images/yes.png'); ?>" alt="user right affectation" class="middleAlign" /><?php }else{ ?><input type="checkbox" name="explanationBoxEnabled" id="explanationBoxEnabled" value="" checked="checked" /><?php } ?>&nbsp;<?php _e('Permission ajout&eacute;e en plus de celle du r&ocirc;le de l\'utilisateur', 'evarisk'); ?></span>
		</td>
	</tr>
<?php
		}

		if(($interface_provenance != 'digi_user_profile') && (!empty($elementToManage) && !empty($elementToManage->name) && ($elementToManage->name != 'administrator')) ){
?>
	<tr>
		<td><?php _e('Raccourci d\'attribution', 'evarisk'); ?></td>
		<td>
			<span class="checkall_right" id="add_checkall" ><?php _e('Tous les droits', 'evarisk'); ?></span>&nbsp;/&nbsp;<span class="uncheckall_right" id="remove_uncheckall" ><?php _e('Aucun droit', 'evarisk'); ?></span><br/>
			<span class="checkall_link" id="add_menu" ><?php _e('Tous les menus', 'evarisk'); ?></span>&nbsp;/&nbsp;<span class="uncheckall_link" id="remove_menu" ><?php _e('Aucun menu', 'evarisk'); ?></span><br/>
			<span class="checkall_link" id="add_read" ><?php _e('Tous les droits en lecture', 'evarisk'); ?></span>&nbsp;/&nbsp;<span  class="uncheckall_link" id="remove_read" ><?php _e('Aucun droit en lecture', 'evarisk'); ?></span><br/>
			<span class="checkall_link" id="add_write" ><?php _e('Tous les droits en &eacute;criture', 'evarisk'); ?></span>&nbsp;/&nbsp;<span  class="uncheckall_link" id="remove_write" ><?php _e('Aucun droit en &eacute;criture', 'evarisk'); ?></span><br/>
			<span class="checkall_link" id="add_delete" ><?php _e('Tous les droits en suppression', 'evarisk'); ?></span>&nbsp;/&nbsp;<span  class="uncheckall_link" id="remove_delete" ><?php _e('Aucun droit en suppression', 'evarisk'); ?></span><br/>
		</td>
	</tr>
	<tr>
		<td colspan="2" >&nbsp;</td>
	</tr>
<?php
		}
		foreach($permissionList as $module => $subModule){
?>
	<tr>
		<th>
			<?php _e('permission_' . $module, 'evarisk'); ?>
<?php
				//if(($interface_provenance != 'digi_user_profile') && (!empty($elementToManage) && !empty($elementToManage->name) && ($elementToManage->name != 'administrator')) ){
?>
			<div class="digi_permission_check_all" ><span id="check_selector_<?php echo $module; ?>" class="checkall" ><?php _e('Tout cocher', 'evarisk'); ?></span>&nbsp;/&nbsp;<span id="uncheck_selector_<?php echo $module; ?>" class="uncheckall" ><?php _e('Tout d&eacute;cocher', 'evarisk'); ?></span></div>
<?php
				//}
?>
		</th>
		<td>
<?php
			foreach($subModule as $subModuleName => $moduleContent){
?>
			<div class="sub_module <?php echo ($subModuleName != '') ? 'permission_module_' . $subModuleName : ''; ?>" >
				<div class="sub_module_name" >
<?php
				if($subModuleName)
					_e('permission_' . $module . '_' . $subModuleName, 'evarisk');
				else
					_e('permission_' . $module, 'evarisk');
?>
				</div>
				<div class="sub_module_content" >
<?php
				//if(($interface_provenance != 'digi_user_profile') && (!empty($elementToManage) && !empty($elementToManage->name) && ($elementToManage->name != 'administrator')) ){
?>
					<div class="digi_permission_check_all" ><span id="check_selector_<?php echo $module . '_' . $subModuleName; ?>" class="checkall" ><?php _e('Tout cocher', 'evarisk'); ?></span>&nbsp;/&nbsp;<span id="uncheck_selector_<?php echo $module . '_' . $subModuleName; ?>" class="uncheckall" ><?php _e('Tout d&eacute;cocher', 'evarisk'); ?></span></div>
<?php
				//}
				/*	Liste des permissions pour le module et le sous-module	*/
				foreach($moduleContent as $permission){
					$checked = $permissionNameClass = '';
					$checked_picto = admin_url('images/no.png');
					$roleToCopy = isset($_REQUEST['roleToCopy']) ? digirisk_tools::IsValid_Variable($_REQUEST['roleToCopy']) : '';
					$action = isset($_REQUEST['save']) ? digirisk_tools::IsValid_Variable($_REQUEST['save']) : '';
					if(($roleToCopy != '') && ($action == 'ok')){
						$roleDetails = $digi_wp_role->get_role($roleToCopy);
						if($roleDetails->has_cap($permission)){
							$checked = 'checked="checked"';
							$checked_picto = admin_url('images/yes.png');
						}
					}
					elseif(($elementToManage != null) && $elementToManage->has_cap($permission)){
						$checked = 'checked="checked"';
						$checked_picto = admin_url('images/yes.png');
						$permissionNameClass = 'permissionGranted';
						if(isset($elementToManage->roles) && (count($elementToManage->caps) >= count($elementToManage->roles)) && apply_filters('additional_capabilities_display', true, $elementToManage)){
							$roleDetails = $digi_wp_role->get_role(implode('', $elementToManage->roles));
							if(!empty($roleDetails) && $roleDetails->has_cap($permission)){
								$permissionNameClass = 'permissionGrantedFromParent';
								$checked .= ' disabled="disabled" ';
							}
						}
					}
					if($interface_provenance == 'digi_user_profile'){
						echo '<img src="' . $checked_picto . '" alt="user right affectation" class="middleAlign" />&nbsp;<label for="digi_permission_' . $permission . '" class="' . $permissionNameClass . '" >' . __($permission, 'evarisk') . '</label><br/>';
					}
					else{
						if((!empty($elementToManage) && !empty($elementToManage->name) && ($elementToManage->name == 'administrator')) )
							$checked .= ' disabled="disabled" ';
						echo '<input type="checkbox" class="' . $module . ' ' . $subModuleName . ' ' . $module . '_' . $subModuleName . ' ' . $permissionCap[$permission]['type'] . ' ' . $permissionCap[$permission]['subtype'] . ' ' . $permissionCap[$permission]['type'] . '_' . $permissionCap[$permission]['subtype'] . '" name="digi_permission[' . $permission . ']" id="digi_permission_' . $permission . '" value="yes" ' . $checked . ' />&nbsp;<label for="digi_permission_' . $permission . '" class="' . $permissionNameClass . '" >' . __($permission, 'evarisk') . '</label><br/>';
					}
				}
?>
				</div>
			</div>
<?php
			}
?>
		</td>
	</tr>
<?php
		}
?>
</table>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		/**
		*	Define action when clicking on checkall/uncheckall for a module or a sub module
		*/
		digirisk('.checkall').click(function(){
			var module = digirisk(this).attr("id").replace("check_selector_", "");
			digirisk("." + module).each(function(){
				if(!digirisk(this).prop("disabled")){
					digirisk(this).prop("checked", true);
				}
			});
		});
		digirisk('.uncheckall').click(function(){
			var module = digirisk(this).attr("id").replace("uncheck_selector_", "");
			digirisk("." + module).each(function(){
				if(!digirisk(this).prop("disabled")){
					digirisk(this).prop("checked", false);
				}
			});
		});

		/**
		*	Define action chen clicking on checkall/uncheckall into the link
		*/
		digirisk('.checkall_link').click(function(){
			var module = digirisk(this).attr("id").replace("add_", "");
			digirisk("." + module).each(function(){
				if(!digirisk(this).prop("disabled")){
					digirisk(this).prop("checked", true);
				}
			});
		});
		digirisk('.uncheckall_link').click(function(){
			var module = digirisk(this).attr("id").replace("remove_", "");
			digirisk("." + module).each(function(){
				if(!digirisk(this).prop("disabled")){
					digirisk(this).prop("checked", false);
				}
			});
		});

		/**
		*	Define action chen clicking on checkall/uncheckall into the link
		*/
		digirisk('.checkall_right').click(function(){
			var module = digirisk(this).attr("id").replace("add_", "");
			digirisk("." + module).each(function(){
				digirisk(this).click();
			});
		});
		digirisk('.uncheckall_right').click(function(){
			var module = digirisk(this).attr("id").replace("remove_", "");
			digirisk("." + module).each(function(){
				digirisk(this).click();
			});
		});
	});
</script>
<?php
	}

}