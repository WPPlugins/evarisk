<?php
/*
 * @version v5.0
 */
require_once(EVA_CONFIG );
require_once(EVA_LIB_PLUGIN_DIR . 'arborescence.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'adresse/evaAddress.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/groupement/eva_groupement.class.php' );
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

$search = "Status='Valid' AND nom<>'Groupement Racine'";
$groupement = EvaGroupement::getGroupements($search);

if ($_REQUEST['act'] == 'save') {
	$nom = (digirisk_tools::IsValid_Variable($_REQUEST['nom_groupement']));
	EvaGroupement::saveNewGroupement($nom);
	$_REQUEST['id'] = $wpdb->insert_id;
	$_REQUEST['act'] = 'update';
}
if ($_REQUEST['act'] == 'update') {
	$id_groupement = $_REQUEST['id'];
	$nom = (digirisk_tools::IsValid_Variable($_REQUEST['nom_groupement']));

	$groupementUpdate = EvaGroupement::getGroupementByName($nom);
	$ligne1 = $_REQUEST['adresse_ligne_1'];
	$ligne2 = $_REQUEST['adresse_ligne_2'];
	$ville = $_REQUEST['ville'];
	$codePostal = $_REQUEST['code_postal'];
	$latitude = $_REQUEST['latitude'];
	$longitude = $_REQUEST['longitude'];
	$address = new EvaAddress($groupementUpdate->id_adresse, $ligne1, $ligne2, $codePostal, $ville, $latitude, $longitude, 'Valid');
	$address->save();
	$idAdresse = $address->getId();

	$idResponsable = (digirisk_tools::IsValid_Variable($_REQUEST['responsable_groupement']));
	$idGroupementPere = (digirisk_tools::IsValid_Variable($_REQUEST['groupementPere']));
	$typeGroupement = (digirisk_tools::IsValid_Variable($_REQUEST['typeGroupement']));
	$siren = (digirisk_tools::IsValid_Variable($_REQUEST['siren']));
	$siret = (digirisk_tools::IsValid_Variable($_REQUEST['siret']));
	$social_activity_number = (digirisk_tools::IsValid_Variable($_REQUEST['social_activity_number']));
	$creation_date_of_society = (digirisk_tools::IsValid_Variable($_REQUEST['creation_date_of_society']));

	$effectif = !empty($_REQUEST['effectif']) ? (digirisk_tools::IsValid_Variable($_REQUEST['effectif'])): '';
	if($effectif == '')
	{
		$effectif = null;
	}
	$description = (digirisk_tools::IsValid_Variable($_REQUEST['description']));
	if($description == '')
	{
		$description = null;
	}
	$telephone = (digirisk_tools::IsValid_Variable($_REQUEST['telephone']));
	if($telephone == '')
	{
		$telephone = null;
	}
	EvaGroupement::updateGroupement($id_groupement, $nom, $description, $telephone, $effectif, $idAdresse, $idGroupementPere, $typeGroupement, $siren, $siret, $social_activity_number, $creation_date_of_society, $idResponsable);
}
if ($_REQUEST['act'] == 'delete') {
	EvaGroupement::deleteGroupement($_REQUEST['id']);
}