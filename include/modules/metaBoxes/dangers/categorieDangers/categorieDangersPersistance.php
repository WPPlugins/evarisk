<?php

require_once(EVA_CONFIG );
require_once(EVA_LIB_PLUGIN_DIR . 'arborescence.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'danger/categorieDangers/categorieDangers.class.php' );
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

$search = "`Status`='Valid' AND nom<>'Categorie Racine'";
$categories_danger = categorieDangers::getCategoriesDanger($search);

if($_REQUEST['act'] == 'save')
{
	$nom = (digirisk_tools::IsValid_Variable($_REQUEST['nom_categorie']));
	categorieDangers::saveNewCategorie($nom);
	
	$_REQUEST['act'] = 'update';
	$_REQUEST['id'] = $wpdb->insert_id;
}
if($_REQUEST['act'] == 'update')
{
	$id_categorie = $_REQUEST['id'];
	$nom = (digirisk_tools::IsValid_Variable($_REQUEST['nom_categorie']));
	$description = (digirisk_tools::IsValid_Variable($_REQUEST['description']));
	$idCategorieMere = (digirisk_tools::IsValid_Variable($_REQUEST['categorieMere']));
	categorieDangers::updateCategorie($id_categorie, $nom, $description, $idCategorieMere);
}
if($_REQUEST['act'] == 'delete')
{
	$id_categorie = $_REQUEST['id'];
	categorieDangers::deleteCategorie($id_categorie);
}