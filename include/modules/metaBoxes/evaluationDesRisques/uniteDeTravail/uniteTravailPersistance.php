<?php
/*
 * @version v5.0
 */
require_once(EVA_CONFIG );
require_once(EVA_LIB_PLUGIN_DIR . 'arborescence.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'adresse/evaAddress.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/groupement/eva_groupement.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/uniteDeTravail/uniteDeTravail.class.php' );
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

if($_REQUEST['act'] == 'save')
{
	$nom = (digirisk_tools::IsValid_Variable($_REQUEST['nom_unite_travail']));
	$idGroupementPere = (digirisk_tools::IsValid_Variable($_REQUEST['groupementPere']));

	$groupement = EvaGroupement::getGroupement($idGroupementPere);
	$groupementDescendant = Arborescence::getDescendants(TABLE_GROUPEMENT, $groupement);
	if(count($groupementDescendant) == 0)
	{
		$workingUnitResult = eva_UniteDeTravail::saveNewWorkingUnit($nom, $idGroupementPere);

		$_REQUEST['act'] = 'update';
		$_REQUEST['id'] = $wpdb->insert_id;
	}
	else
	{
echo '
<script type="text/javascript" >
	digirisk("#message").html(\'<img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="error" />' . __('Vous ne pouvez pas ajouter une unit&eacute; de travail au m&ecirc;me niveau qu\\\'un groupement', 'evarisk') . '\');
	digirisk("#message").addClass("updated");
	digirisk("#message").show();
	setTimeout(function(){
		digirisk("#message").removeClass("updated");
		digirisk("#message").hide();
	},7500);
</script>';
	}
}
if($_REQUEST['act'] == 'update')
{
	$id_unite_travail = $_REQUEST['id'];
	$nom = (digirisk_tools::IsValid_Variable($_REQUEST['nom_unite_travail']));
	$idGroupementPere = (digirisk_tools::IsValid_Variable($_REQUEST['groupementPere']));
	$idResponsable = (digirisk_tools::IsValid_Variable($_REQUEST['responsable_unite']));

	$uniteTravailUpdate = eva_UniteDeTravail::getWorkingUnitByName($nom);

	$ligne1 = $_REQUEST['adresse_ligne_1'];
	$ligne2 = $_REQUEST['adresse_ligne_2'];
	$ville = $_REQUEST['ville'];
	$codePostal = $_REQUEST['code_postal'];
	$latitude = $_REQUEST['latitude'];
	$longitude = $_REQUEST['longitude'];
	$address = new EvaAddress($uniteTravailUpdate->id_adresse, $ligne1, $ligne2, $codePostal, $ville, $latitude, $longitude, 'Valid');
	$address->save();
	$idAdresse = $address->getId();

	$effectif = (!empty($_REQUEST['effectif']) ? digirisk_tools::IsValid_Variable($_REQUEST['effectif']) : '');
	if($effectif == '')
	{
		$effectif = null;
	}
	$description = (!empty($_REQUEST['description']) ? digirisk_tools::IsValid_Variable($_REQUEST['description']) : '');
	if($description == '')
	{
		$description = null;
	}
	$telephone = (!empty($_REQUEST['telephone']) ? digirisk_tools::IsValid_Variable($_REQUEST['telephone']) : '');
	if($telephone == '')
	{
		$telephone = null;
	}

	$workingUnitResult = eva_UniteDeTravail::updateWorkingUnit($id_unite_travail, $nom, $description, $telephone, $effectif, $idAdresse, $idGroupementPere, $idResponsable);
}
if($_REQUEST['act'] == 'delete')
{
	$workingUnitResult = eva_UniteDeTravail::deleteWorkingUnit($_REQUEST['id']);
}