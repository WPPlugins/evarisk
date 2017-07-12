<?php
require_once(EVA_CONFIG);
require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaGroupeQuestion.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaQuestion.class.php' );
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

switch($_POST['act'])
{
	case 'save' :
		$enonce = (digirisk_tools::IsValid_Variable($_POST['enonce']));
		$code = (digirisk_tools::IsValid_Variable($_POST['code']));
		$idGroupeQuestion = (digirisk_tools::IsValid_Variable($_POST['idGroupeQuestion']));
		EvaQuestion::saveNewQuestion($enonce, $code, $idGroupeQuestion);
		break;
	case 'update' :
		$idQuestion = (digirisk_tools::IsValid_Variable($_POST['id']));
		$enonce = (digirisk_tools::IsValid_Variable($_POST['enonce']));
		$code = (digirisk_tools::IsValid_Variable($_POST['code']));
		EvaQuestion::updateQuestion($idQuestion, $enonce, $code);
		break;
	case 'delete':
		$idQuestion = (digirisk_tools::IsValid_Variable($_POST['idQuestion']));
		$idGroupeQuestions = (digirisk_tools::IsValid_Variable($_POST['idGroupeQuestions']));
		EvaQuestion::deleteQuestion($idQuestion, $idGroupeQuestions);
		break;
}