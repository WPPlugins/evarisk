<?php

function getMainPostBoxBody($arguments)
{
	include_once(EVA_CONFIG );
	require_once(EVA_LIB_PLUGIN_DIR . 'arborescence.class.php' );
	require_once(EVA_LIB_PLUGIN_DIR . 'evaDisplayDesign.class.php' );

	$postBoxId = 'mainPostBox';
	$idPere = $arguments['idPere'];
	$table = $arguments['tableElement'];
	switch($table)
	{
		case TABLE_GROUPEMENT:
			$sousTable = TABLE_UNITE_TRAVAIL;
			break;
		case TABLE_UNITE_TRAVAIL:
			$table = TABLE_GROUPEMENT;
			$sousTable = TABLE_UNITE_TRAVAIL;
			break;
		case TABLE_CATEGORIE_DANGER:
			$sousTable = TABLE_DANGER;
			break;
		case TABLE_DANGER:
			$table = TABLE_CATEGORIE_DANGER;
			$sousTable = TABLE_DANGER;
			break;
		case TABLE_TACHE:
			$sousTable = TABLE_ACTIVITE;
			break;
		case TABLE_ACTIVITE:
			$table = TABLE_TACHE;
			$sousTable = TABLE_ACTIVITE;
			break;
	}
	$elementPere = ($idPere != 0) ? Arborescence::getElement($table, $idPere) : Arborescence::getRacine($table);
	if($arguments['affichage'] == 'affichageTable')
	{
		$page = ((int)$arguments['page'] < 1)?1:(int)$arguments['page'];

		$listeElements = Arborescence::getFils($table, $elementPere);
		if(!isset($arguments['tableElement']))
			$arguments['tableElement'] = null;
		if(!isset($arguments['idElement']))
			$arguments['idElement'] = null;
		$liste = EvaDisplayDesign::creerListe($table, $page, $elementPere->id, $postBoxId, $arguments['tableElement'], $arguments['idElement']);
		$mainPostBoxBody = '
			<div id="containerElement' . $postBoxId . '" class="containerElement">
				<div id="listePrimaire' . $postBoxId . '" class="listeEnLigne">' . $liste . '
				</div>
			<div class="clear"></div>
		';

		//Ajout des boutons "ajouter" et "remonter"
		switch($table)
		{
			case TABLE_GROUPEMENT:
				$first = __("Nouveau groupement", 'evarisk');
				$second = __("Nouvelle unit&eacute; de travail", 'evarisk');
				break;
			case TABLE_CATEGORIE_DANGER:
				$first = __("Nouvelle cat&eacute;gorie de danger", 'evarisk');
				$second = __("Nouveau danger", 'evarisk');
				break;
		}
		$scriptEvaluationRisques = '
			<script type="text/javascript">
				digirisk(document).ready(function(){
					//On affiche le fil d\'ariane
					digirisk(\'#filAriane\').show();

					//On change le titre de la metaBox
					if(' . $elementPere->id . ' != 1)
						digirisk(\'#' . $postBoxId . ' h3 span\').html("' . addslashes($elementPere->nom) . '");

					digirisk("#' . $postBoxId . ' .handlediv").after(\'<div id="favorite-actions-' . $postBoxId . '" class="alignright favorite-actions"><div id="favorite-toggle-' . $postBoxId . '" class="favorite-toggle alignright"></div><div id="favorite-first-' . $postBoxId . '" class="favorite-first"><a href="#" onclick="return false;">' . __('Ajouter', 'evarisk') . '...</a></div><div id="favorite-inside-' . $postBoxId . '" style="display: none;" class="favorite-inside"><div class="favorite-action"><a id="favorite-first-link-' . $postBoxId . '" href="#">' . $first . '</a></div><div class="favorite-action"><a id="favorite-second-link-' . $postBoxId . '" href="#">' . $second . '</a></div></div></div>\');

					digirisk(\'#favorite-inside-' . $postBoxId . '\').css("width", digirisk(\'#favorite-actions-' . $postBoxId . '\').innerWidth() -4 + "px");
					digirisk(\'#favorite-toggle-' . $postBoxId . '\').hover(function() {
						digirisk(\'#favorite-first-' . $postBoxId . '\').addClass("slide-down");
						digirisk(\'#favorite-inside-' . $postBoxId . '\').addClass("slideDown");
						digirisk(\'#favorite-inside-' . $postBoxId . '\').slideDown(100);
					});
					digirisk(\'#favorite-first-' . $postBoxId . '\').click(function() {
						digirisk(\'#favorite-first-' . $postBoxId . '\').addClass("slide-down");
						digirisk(\'#favorite-inside-' . $postBoxId . '\').addClass("slideDown");
						digirisk(\'#favorite-inside-' . $postBoxId . '\').slideDown(100);
					});
					var timeoutFavoriteActions;
					digirisk(\'#favorite-actions-' . $postBoxId . '\').hover(function() {
						clearTimeout(timeoutFavoriteActions);
					},function() {
						timeoutFavoriteActions = setTimeout
						(
							function()
							{
								digirisk(\'#favorite-inside-' . $postBoxId . '\').slideUp(100);
								setTimeout
								(
									function()
									{
										digirisk(\'#favorite-first-' . $postBoxId . '\').removeClass("slide-down");
										digirisk(\'#favorite-inside-' . $postBoxId . '\').removeClass("slideDown");
									},
									100
								);
							},
							500
						);
					});
					digirisk(\'#favorite-first-link-' . $postBoxId . '\').click(function() {
						digirisk(\'#rightEnlarging\').show();
						digirisk(\'#equilize\').click();

						digirisk(\'#partieEdition\').load(\'' . EVA_INC_PLUGIN_URL . 'ajax.php\', {"post": "true",
							"table": "' . $table . '",
							"act": "add",
							"page": digirisk(\'#page' . $postBoxId . 'Reference\').val(),
							"idPere": digirisk(\'#identifiantActuelle' . $postBoxId . '\').val(),
							"partie": "right",
							"menu": digirisk("#menu").val(),
							"affichage": "affichageTable",
							"partition": "tout"
						});
						digirisk(\'#partieGauche\').load(\'' . EVA_INC_PLUGIN_URL . 'ajax.php\', {"post": "true",
							"table": "' . $table . '",
							"act": "add",
							"page": digirisk(\'#page' . $postBoxId . 'Reference\').val(),
							"idPere": digirisk(\'#identifiantActuelle' . $postBoxId . '\').val(),
							"partie": "left",
							"menu": digirisk("#menu").val(),
							"affichage": "affichageTable",
							"partition": "tout"
						});

						digirisk(\'#partieEdition\').html(\'<center><img src="' . PICTO_LOADING . '" /></center>\');
						digirisk(\'#partieGauche\').html(\'<center><img src="' . PICTO_LOADING . '" /></center>\');
						return false;
					});
					digirisk(\'#favorite-second-link-' . $postBoxId . '\').click(function() {
						digirisk(\'#rightEnlarging\').show();
						digirisk(\'#equilize\').click();

						digirisk(\'#partieEdition\').load(\'' . EVA_INC_PLUGIN_URL . 'ajax.php\', {"post": "true",
							"table": "' . $sousTable . '",
							"act": "add",
							"page": digirisk(\'#page' . $postBoxId . 'Reference\').val(),
							"idPere": digirisk(\'#identifiantActuelle' . $postBoxId . '\').val(),
							"partie": "right",
							"menu": digirisk("#menu").val(),
							"affichage": "affichageTable",
							"partition": "tout"
						});
						digirisk(\'#partieGauche\').load(\'' . EVA_INC_PLUGIN_URL . 'ajax.php\', {"post": "true",
							"table": "' . $sousTable . '",
							"act": "add",
							"page": digirisk(\'#page' . $postBoxId . 'Reference\').val(),
							"idPere": digirisk(\'#identifiantActuelle' . $postBoxId . '\').val(),
							"partie": "left",
							"menu": digirisk("#menu").val(),
							"affichage": "affichageTable",
							"partition": "tout"
						});

						digirisk(\'#partieEdition\').html(\'<center><img src="' . PICTO_LOADING . '" /></center>\');
						digirisk(\'#partieGauche\').html(\'<center><img src="' . PICTO_LOADING . '" /></center>\');
						return false;
					});


					digirisk("#favorite-actions-' . $postBoxId . '").after(\'<div id="' . $postBoxId . 'Pere" title="' . __("Cliquer pour remonter d\'un niveau.", "evarisk") . '" class="flechePere"><img alt="pere" src="' . PICTO_FLECHE_PERE . '" title=""/></div>\');
					if(digirisk("#filAriane a:last").attr("id") == digirisk(\'#filAriane :first-child\').attr("id"))
					{
						digirisk(\'#' . $postBoxId . 'Pere\').hide();
					}
					else
					{
						digirisk(\'#' . $postBoxId . 'Pere\').show();
					}
					digirisk(\'#' . $postBoxId . 'Pere\').unbind("click");
					digirisk(\'#' . $postBoxId . 'Pere\').click(function() {
						if(digirisk(\'#filAriane :last-child\').attr("id") != digirisk(\'#filAriane :first-child\').attr("id"))
						{
							digirisk(\'#page' . $postBoxId . 'Reference\').val(1);
							if(digirisk(\'#filAriane :last-child\').is("label"))
							{
								digirisk(\'#filAriane :last-child\').remove();
							}
							digirisk(\'#filAriane :last-child\').remove();
							digirisk(\'#filAriane :last-child\').remove();
							digirisk(\'#' . $postBoxId . ' h3 span\').html(digirisk(\'#filAriane :last-child\').html());
							var id = digirisk(\'#filAriane :last-child\').attr("id");
							var reg = new  RegExp("(element)", "g");
							var id = id.replace(reg, "");
							digirisk(\'#identifiantActuelle' . $postBoxId . '\').val(id);
							digirisk(\'#rightEnlarging\').hide();

							changementPage("right", "' . $table . '", digirisk("#page' . $postBoxId . 'Reference").val(), digirisk("#identifiantActuelle' . $postBoxId . '").val(), "affichageTable", "main");
							changementPage("left", "' . $table . '", digirisk("#page' . $postBoxId . 'Reference").val(), digirisk("#identifiantActuelle' . $postBoxId . '").val(), "affichageTable", "main");
							return false;
						}
					});
				});
			</script>';
	}
	else
	{
		$racine = Arborescence::getRacine($table);
		$nomTable = "mainTable";
		switch($table)
		{
			case TABLE_GROUPEMENT:
				$enTeteTable = __("Groupements", 'evarisk');
				break;
			case TABLE_UNITE_TRAVAIL:
				$table = TABLE_GROUPEMENT;
				$enTeteTable = __("Groupements", 'evarisk');
				break;
			case TABLE_CATEGORIE_DANGER:
				$enTeteTable = __("Cat&eacute;gories", 'evarisk');
				break;
			case TABLE_DANGER:
				$table = TABLE_CATEGORIE_DANGER;
				$enTeteTable = __("Cat&eacute;gories", 'evarisk');
				break;
			case TABLE_TACHE:
				$enTeteTable = __("Actions correctives", 'evarisk');
				break;
			case TABLE_ACTIVITE:
				$table = TABLE_TACHE;
				$enTeteTable = __("Actions correctives", 'evarisk');
				break;
		}
		$scriptAfterEvaluationRisques = '<script type="text/javascript">
			digirisk(document).ready(function() {
				digirisk(\'#' . $postBoxId . ' .inside\').each(function(){digirisk(this).addClass("noPadding");});
				digirisk(\'#filAriane\').hide();';
		if(is_array($arguments['expanded']) && (count($arguments['expanded']) > 0))
			foreach($arguments['expanded'] as $expanded)
			{
				$scriptAfterEvaluationRisques= $scriptAfterEvaluationRisques . '
					digirisk("#' . $expanded . ' span.expander").click();';
			}
		$scriptAfterEvaluationRisques= $scriptAfterEvaluationRisques . '
			});
		</script>';
		$mainPostBoxBody = EvaDisplayDesign::getTableArborescence($racine, $table, $nomTable, $enTeteTable);
	}
	if (isset($scriptEvaluationRisques)) {
		echo $scriptEvaluationRisques;
	}
	echo '
		<div style="float:right; margin-right:10px;" ><span class="digi_tree_complete_expander digi_tree_complete_expander_open pointer" >' . __('D&eacute;plier l\'arbre', 'evarisk') . '</span> / <span class="digi_tree_complete_expander digi_tree_complete_expander_close pointer" >' . __('Replier l\'arbre', 'evarisk') . '</span></div>
		<script type="text/javascript" >
			digirisk(document).ready(function() {
				jQuery.fn.expandAll = function() {
				    jQuery(this).find("tr").removeClass("collapsed").addClass("expanded").each(function(){
				        jQuery(this).expand();
				    });
				};
				jQuery(".digi_tree_complete_expander_open").live("click", function(){
					jQuery("#mainTable").expandAll();
				});

				jQuery.fn.collapseAll = function() {
				    jQuery(this).find("tr").addClass("collapsed").removeClass("expanded").each(function(){
				        jQuery(this).collapse();
				    });
				};
				jQuery(".digi_tree_complete_expander_close").live("click", function(){
					jQuery("#mainTable").collapseAll();
				    jQuery("#node-mainTable-1").expand();
				});
			});
		</script>' . $mainPostBoxBody;

	if (isset($scriptAfterEvaluationRisques)) {
		echo $scriptAfterEvaluationRisques;
	}

}
