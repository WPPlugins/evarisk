<?php
/*
 * This file manages the contents of the postbox of PPE.
 *
 * @author Evarisk
 * @version v5.0
 */
	//Postbox definition
	$postBoxTitle = __('&Eacute;quipements de protection individuelle','evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
	$postBoxId = 'postBoxEPI';
	$postBoxCallbackFunction = 'getEPIPostBoxBody';
	add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_UNITES_DE_TRAVAIL, 'rightSide', 'default');

	/**
	 *
	 */
	function getEPIPostBoxBody($arguments)
	{
		require_once(EVA_LIB_PLUGIN_DIR . 'evaDisplayInput.class.php' );
		require_once(EVA_LIB_PLUGIN_DIR . 'epi/evaEPITable.class.php');

		$idElement = $arguments['idElement'];
		$tableElement = $arguments['tableElement'];
		$EPIPostBoxBody = '';
		if($idElement == "")
		{
			switch($tableElement)
			{
				case TABLE_GROUPEMENT:
					$element = __('le groupement', 'evarisk');
					break;
				case TABLE_UNITE_TRAVAIL:
					$element = __('l\'unit&eacute; de travail', 'evarisk');
					break;
				default :
					$element = __('l\'&eacute;l&eacute;ment', 'evarisk');
			}
			$EPIPostBoxBody = sprintf(__("Veuillez d'abord enregistrer %s.", 'evarisk'), $element);
		}
		else
		{
			$EPIComparaison = new EvaEPITable();
			$EPIs = $EPIComparaison->getEPIsAndUse($tableElement, $idElement);

			if(count($EPIs) > 0)
			{// Their is some PPEs
				$formName = 'EPIForm';
				$EPIPostBoxBody = $EPIPostBoxBody . '<div id="messageEPI" class="fade" style="display:none" ></div>';
				$EPIPostBoxBody = $EPIPostBoxBody . EvaDisplayInput::ouvrirForm('POST', $formName, $formName);
				{// Hidden fields
					$EPIPostBoxBody = $EPIPostBoxBody . EvaDisplayInput::afficherInput('hidden', 'act', '', '', null, 'act', false, false);
					$EPIPostBoxBody = $EPIPostBoxBody . EvaDisplayInput::afficherInput('hidden', 'tableElement', $tableElement, '', null, 'tableElement', false, false);
					$EPIPostBoxBody = $EPIPostBoxBody . EvaDisplayInput::afficherInput('hidden', 'idElement', $idElement, '', null, 'idElement', false, false);
				}
				foreach($EPIs as $EPI)
				{// Display of each PPE
					// Input creation
					$labelInput = '<img style="max-width:60%;" alt="' . utf8_encode($EPI['EPI']->getName()) . '" src="' . $EPI['EPI']->getPath() . '" title="' . utf8_encode($EPI['EPI']->getName()) . '"/><br />';
					$nomChamps = "epi[]";
					$idChamps = digirisk_tools::slugify('epi_' . $EPI['EPI']->getId());
					$script = '';
					if($EPI['utilise'])
					{// Script of checking box
						$script = '
							<script type="text/javascript">
								digirisk(document).ready(function(){digirisk(\'#' . $idChamps . '\').prop("checked", "checked");});
							</script>';
					}
					$EPIPostBoxBody = $EPIPostBoxBody . EvaDisplayInput::afficherInput('checkbox', $idChamps, '', '', $labelInput, $nomChamps, false, true, 255, 'eva_epi', '', '', $script, 'left; text-align:center; max-width: 24%', true);
				}
				$idBouttonEnregistrer = 'saveEPIS';
				$scriptEnregistrement = '<script type="text/javascript">
					digirisk(document).ready(function() {
						digirisk(\'#' . $idBouttonEnregistrer . '\').click(function(){
							var epis = new Array();
							digirisk(\'.eva_epi\').each(function(){
								if(digirisk(this).prop("checked"))
								{
									epis.push(digirisk(this).attr("id").replace(/epi_/,""));
								}
							});

							digirisk(\'#ajax-response\').load(\'' . EVA_INC_PLUGIN_URL . 'ajax.php\', {
								"post": "true",
								"table": "' . TABLE_UTILISE_EPI . '",
								"epis": epis,
								"tableElement":"' . $tableElement . '",
								"idElement":"' . $idElement . '"
							});
						});
						return false;
					});
					</script>';
				$EPIPostBoxBody = $EPIPostBoxBody . EvaDisplayInput::afficherInput('button', $idBouttonEnregistrer, __('Enregistrer', 'evarisk'), null, '', 'save', false, true, '', 'button-primary alignright', '', '', $scriptEnregistrement);
				$EPIPostBoxBody = $EPIPostBoxBody . EvaDisplayInput::fermerForm($formName);
			}
			else
			{// Their is no PPE
				$EPIPostBoxBody = __('Il n\'y a pas d\'EPI', 'evarisk');
			}
		}
		echo $EPIPostBoxBody;
	}
?>