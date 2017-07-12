<?php
$messageInfo = '';

$_POST['table'] = TABLE_TACHE;
$titrePage = __("Actions Correctives", 'evarisk');
$icone = PICTO_LTL_ACTION;
$titreIcone = "Icone actions correctives";
$altIcon = "Icone AC";
$titreFilAriane= __("Actions correctives", 'evarisk');
if(!isset($_POST['affichage']))
{
	$_POST['affichage'] = "affichageListe";
}
include_once(EVA_LIB_PLUGIN_DIR . 'classicalPage.php' );	
// On enlève le choix de l'affichage
?>
<script type="text/javascript">
  digirisk(document).ready(function(){
    digirisk('#choixAffichage').hide();
  });
</script>
<?php
	if(isset($_GET['elt']) && ($_GET['elt'] != ''))
	{
		echo
			'<script type="text/javascript">
				digirisk(document).ready(function(){
					setTimeout(function(){
						digirisk("#' . $_GET['elt'] . '").click();
					},3000);
				})
			</script>';
	}
?>