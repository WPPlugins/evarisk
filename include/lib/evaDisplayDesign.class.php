<?php
/**
 * This class contains the methods allowing to dipslay a classical screen of the Evarisk pluggin
 * @author Evarisk
 * @version v5.0
 */
require_once(EVA_CONFIG );
require_once(EVA_LIB_PLUGIN_DIR . 'arborescence.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/groupement/eva_groupement.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/uniteDeTravail/uniteDeTravail.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/documentUnique/documentUnique.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'danger/categorieDangers/categorieDangers.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'danger/danger/evaDanger.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaQuestion.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaGroupeQuestion.class.php');
require_once(EVA_LIB_PLUGIN_DIR . 'actionsCorrectives/tache/evaTask.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'evaNotes.class.php' );

class EvaDisplayDesign {
	/**
	 * Returns the header display of a classical HTML page.
	 * @see afficherFinPage
	 * @param string $titrePage Title of the page.
	 * @param string $icone Path of the icon.
	 * @param string $titreIcone Title attribute of the icon.
	 * @param string $altIcon Alt attribute of the icon.
	 * @param string $table Table where the page is link.
	 * @param bool $boutonAjouter Must the page have a button "Add" next to the title ?
	 * @param string $messageInfo The information message.
	 * @param bool $choixAffichage Must the page offer a choice of display ?
	 * @return string HTML code of the header display.
	 */
	static function afficherDebutPage($titrePage, $icone, $titreIcone, $altIcon, $table, $boutonAjouter=true, $messageInfo='', $choixAffichage=false, $affichageNotes = true)
	{
		$debutPage = '<div class="wrap">
			<div class="icon32"><img alt="' . $altIcon . '" src="' . $icone . '" title="' . $titreIcone . '"/></div>
			<h2 class="" >' . $titrePage;
		if($boutonAjouter)
		{
			$debutPage .= ' <a class="button add-new-h2" onclick="javascript:document.getElementById(\'act\').value=\'add\'; document.forms.form.submit();">' . __('Ajouter', 'evarisk') . '</a>';
		}
		$debutPage .= '
		</h2>
';
		if($affichageNotes)
		{
			$debutPage .= evaNotes::noteDialogMaker() . '
		<div id="champsCaches" class="clear" ></div>
		<script type="text/javascript">
			digirisk(document).ready(function(){
				setTimeout(function(){
						digirisk("#message").hide();
				}, 10000);

' . evaNotes::noteDialogScriptMaker() . '
			});
		</script>';
		}
		if($choixAffichage)
		{
			$racine = Arborescence::getRacine($table);
			$idPere = $racine->id;
			$debutPage = $debutPage . '<script type="text/javascript">
				digirisk(document).ready(function() {
					digirisk(\'#affichageTable\').click(function() {
						digirisk(\'#affichageListe\').removeClass(\'selectedAffichage\');
						digirisk(\'#affichageTable\').addClass(\'selectedAffichage\');
						digirisk(\'#identifiantActuellemainPostBox\').val(1);
						digirisk("#pagemainPostBoxReference").val(1);

						while(digirisk(\'#filAriane :last-child\').attr("id") != "element1")
						{
							digirisk(\'#filAriane :last-child\').remove();
						}

						changementPage("right", "' . $table . '", digirisk("#pagemainPostBoxReference").val(), digirisk("#identifiantActuellemainPostBox").val(), "affichageTable", "main");
						changementPage("left", "' . $table . '", digirisk("#pagemainPostBoxReference").val(), digirisk("#identifiantActuellemainPostBox").val(), "affichageTable", "main");
					});
					digirisk(\'#affichageListe\').click(function() {
						digirisk(\'#affichageTable\').removeClass(\'selectedAffichage\');
						digirisk(\'#affichageListe\').addClass(\'selectedAffichage\');
						digirisk(\'#identifiantActuellemainPostBox\').val(1);
						digirisk("#pagemainPostBoxReference").val(1);

						changementPage("right", "' . $table . '", digirisk("#pagemainPostBoxReference").val(), digirisk("#identifiantActuellemainPostBox").val(), "affichageListe", "main");
						changementPage("left", "' . $table . '", digirisk("#pagemainPostBoxReference").val(), digirisk("#identifiantActuellemainPostBox").val(), "affichageListe", "main");
					});
				});
			</script>';
			$debutPage = $debutPage . '
		<!--	<div id="choixAffichage">
			<span class="textAffichage">' . __('Affichage', 'evarisk') . '</span> :
<a id="affichageTable" onclick="return false;"><img alt="' . __('Affichage en grille' , 'evarisk') . '" src="' . PICTO_GRILLE . '" title="' . __('Affichage en grille' , 'evarisk') . '"/> ' . __('Grille' , 'evarisk') . '</a> -
<a id="affichageListe" onclick="return false;"><img alt="' . __('Affichage en liste' , 'evarisk') . '" src="' . PICTO_LISTE . '" title="' . __('Affichage en liste' , 'evarisk') . '"/> ' . __('Liste' , 'evarisk') . '</a>
			</div> -->
			<noscript><h3 style="color:red;" >' . __('Merci d\'activer le javascript pour pouvoir utiliser le logiciel digirisk', 'evarisk') . '</h3></noscript>
			<div id="choseEnlarging" class="choseEnlarging" style="text-align: center">
				<span style="" id="rightEnlarging" class="rightEnlarging"></span>
				<div id="enlarging" class="enlarging"></div>
				<span style="display:none;" id="equilize" class="enlarging"></span>
				<span style="" id="leftEnlarging" class="leftEnlarging"></span>
			</div>';

			if ( false && TABLE_TACHE == $table ) {
				$tasks_status = array( 'notStarted','inProgress','Done','DoneByChief' );
				$debutPage .= '
				<fieldset class="clear" id="digi-tree-options-container" >
					<legend>' . __( 'Options d\'affichage de l\'arbre', 'evarisk' ) . '</legend>
					<div class="digi-tree-options-content" >
						<div class="digi-tree-options-task-status" >
							<label>
								' . __( 'Affichez uniquement les éléments ayant le statut :', 'evarisk' ) . '<br/>
								<select id="digi-tree-element-status" multiple="multiple" >';
				foreach ( $tasks_status as $status ) {
				$debutPage .= '
									<option value="' . $status . '" selected="selected" >' . __( $status, 'evarisk' ) . '</option>';
				}
				$debutPage .= '
								</select>
							</label>
						</div>
					</div>
				</fieldset>
				<script type="text/javascript" >
					jQuery( document ).ready( function(){

					});
				</script>';
			}

		}
		$debutPage .= '<div id="message" class="fade below-h2 evaMessage">' . $messageInfo . '</div>';
		return $debutPage;
	}

	/**
	 * Closes the "div" tag open in the header display  of a classical HTML page.
	 * @see afficherDebutPage
	 * @return  the closure.
	 */
	static function afficherFinPage()
	{
		return '
				<div class="clear" id="ajax-response"></div>
				<div class="clear"></div>
			</div>';
	}

	/**
	 *  Returns the HTML code for the site wire.
	 * @param string $titreFilAriane Displayed name for the first site wire element
	 * @param string $idsFilAriane Identifiers of the elements to add to the site wire
	 * @param string $table Identifiers of the elements to add to the site wire
	 * @param string $idPostBox Identifiers of the postbox whitch the wire is link
	 * @return string HTML code for the site wire.
	 */
	static function getFilAriane($affichage, $titreFilAriane, $table, $idPostBox, $idsFilAriane='')
	{
		$display = '';
		if($affichage == 'affichageListe')
		{
			$display = 'style="display: none"';
		}
		$filAriane = '<div ' . $display . ' id="filAriane"><a href="#" id="element1">' . $titreFilAriane . '</a></div>';
		$script = '<script type="text/javascript">
				digirisk(document).ready(function() {
					digirisk(\'#element1\').click(function() {
						digirisk("#equilize").click();
												digirisk(\'#identifiantActuelle' . $idPostBox . '\').val(1);
						digirisk(\'#page' . $idPostBox . 'Reference\').val(1);
						while(digirisk(\'#filAriane :last-child\').attr("id") != "element1")
						{
							digirisk(\'#filAriane :last-child\').remove();
						}
						changementPage("right", "' . $table . '", digirisk("#page' . $idPostBox . 'Reference").val(), digirisk("#identifiantActuelle' . $idPostBox . '").val(), "affichageTable", "main");
						changementPage("left", "' . $table . '", digirisk("#page' . $idPostBox . 'Reference").val(), digirisk("#identifiantActuelle' . $idPostBox . '").val(), "affichageTable", "main");
						return false;
					});
				});
			</script>';
			if($idsFilAriane != null)
			{
				$element = '';
				if($idsFilAriane != '-1')
				{
					foreach($idsFilAriane as $idFilAriane)
					{
						switch($table)
						{
							case TABLE_GROUPEMENT:
								$element = EvaGroupement::getGroupement($idFilAriane);
								break;
							case TABLE_CATEGORIE_DANGER:
								$element = categorieDangers::getCategorieDanger($idFilAriane);
								break;
						}
						$script = $script .'<script type="text/javascript">
		digirisk(document).ready(function() {
			digirisk(\'#identifiantActuelle' . $idPostBox . '\').val(' . $element->id . ');
			digirisk(\'#page' . $idPostBox . 'Reference\').val(1);
			digirisk(\'#' . $idPostBox . ' h3 span\').html("' . addslashes($element->nom) . '");

			digirisk(\'#filAriane :last-child\').after(\'<label>&nbsp;&raquo;&nbsp;&Eacute;dition&nbsp;de&nbsp;' . addslashes($element->nom) . '</label>\');
			digirisk(\'#element' . $element->id . '\').click(function() {
				digirisk(\'#identifiantActuelle' . $idPostBox . '\').val("' . $element->id . '");
				digirisk(\'#page' . $idPostBox . 'Reference\').val(1);
				while(digirisk(\'#filAriane :last-child\').attr("id") != "element' . $element->id . '")
				{
					digirisk(\'#filAriane :last-child\').remove();
				}
				changementPage("right", "' . $table . '", digirisk("#page' . $idPostBox . 'Reference").val(), digirisk("#identifiantActuelle' . $idPostBox . '").val(), "affichageTable", "main");
				changementPage("left", "' . $table . '", digirisk("#page' . $idPostBox . 'Reference").val(), digirisk("#identifiantActuelle' . $idPostBox . '").val(), "affichageTable", "main");
				return false;
			});
		});
	</script>';
					}
				}
				$script = $script . '
<script type="text/javascript">
	digirisk(document).ready(function() {
		digirisk(\'#element' . $element->id . '\').click();
	});
</script>';
			}
		return $script . $filAriane;
	}

	/**
	 * Open a "div" with the metabox-holder class.
	 * @see fermerMetaboxHolder
	 * @return  the "div".
	 */
	static function ouvrirMetaboxHolder()
	{
		$metaboxHolder = '
				<div class="metabox-holder">';
		return $metaboxHolder;
	}

	/**
	 * Closes the "div" open by the ouvrirMetaboxHolder function.
	 * @see ouvrirMetaboxHolder
	 * @return  the closure.
	 */
	static function fermerMetaboxHolder()
	{
		$metaboxHolder = '
				</div>';
		return $metaboxHolder;
	}

	/**
	 * Returns the HTML code of a splited in two screen .
	 * @param string $partieGauche The left body part.
	 * @param string $partieDroite The right body part.
	 * @param int $largeurGauche The left body percentage width.
	 * @param string $numero A string to add to the id of the element of splited screen.
	 * @return string HTML code of the splited screen.
	 */
	static function splitEcran($partieGauche, $partieDroite, $largeurGauche = LARGEUR_GAUCHE, $numero = '')
	{
		$splitEcran = '
			<script type="text/javascript">
				digirisk(document).ready(function() {
					digirisk(\'#leftEnlarging' . $numero . '\').click(function() {
						digirisk(\'#partieEdition' . $numero . '\').hide();
						digirisk(\'#partieGauche' . $numero . '\').show();
						digirisk(\'#partieGauche' . $numero . '\').css(\'width\', \'98%\');
						adminMenu.fold();
						digirisk("#enlarging' . $numero . ' .ui-slider-range").css("width","100%");
						digirisk("#enlarging' . $numero . ' .ui-slider-handle").css("left","100%");
					});
					digirisk(\'#rightEnlarging' . $numero . '\').click(function() {
						digirisk(\'#partieGauche' . $numero . '\').hide();
						digirisk(\'#partieEdition' . $numero . '\').show();
						digirisk(\'#partieEdition' . $numero . '\').css(\'width\', \'98%\');
						adminMenu.fold();
						digirisk("#enlarging' . $numero . ' .ui-slider-range").css("width","0%");
						digirisk("#enlarging' . $numero . ' .ui-slider-handle").css("left","0%");
					});
					digirisk(\'#equilize' . $numero . '\').click(function() {
						digirisk(\'#partieGauche' . $numero . '\').show();
						digirisk(\'#partieEdition' . $numero . '\').show();
						digirisk(\'#partieEdition' . $numero . '\').css(\'width\', \'49%\');
						digirisk(\'#partieGauche' . $numero . '\').css(\'width\', \'49%\');
						digirisk("#enlarging' . $numero . ' .ui-slider-range").css("width","50%");
						digirisk("#enlarging' . $numero . ' .ui-slider-handle").css("left","50%");
					});
				});
			</script>';


		$script = '<script type="text/javascript">
				digirisk(document).ready(function() {
					digirisk("#enlarging' . $numero . ' .ui-slider-horizontal").css("width","100px");
					digirisk("#enlarging' . $numero . '").slider({
						range: "min",
						value: 50,
						min: 25,
						max:  75,
						slide: function(event, ui) {
							var largeurGauche = ui.value - 1;
							var largeurDroite = 98 - largeurGauche;
							if(largeurGauche == 24 || largeurDroite == 24)
							{
								adminMenu.fold();
							}
							digirisk(\'#partieEdition' . $numero . '\').show();
							digirisk(\'#partieGauche' . $numero . '\').show();
							digirisk("#partieGauche' . $numero . '").css("width", largeurGauche  + "%");
							digirisk("#partieEdition' . $numero . '").css("width", largeurDroite  + "%");
						}
					});
				});
			</script>';


		$splitEcran = $script . $splitEcran . '<div id="partieGauche' . $numero . '" style="width:' . $largeurGauche . '%;" class="postbox-container">';
		$splitEcran = $splitEcran . $partieGauche;
		$splitEcran = $splitEcran .'
					</div>';
		$splitEcran = $splitEcran .'		<div id="partieEdition' . $numero . '" style="width:' . (98 - $largeurGauche) . '%;" class="alignleft partieDroite postbox-container">
						' . $partieDroite . '
					</div>';
		return $splitEcran;
	}

	/**
	 * Returns the HTML code of all postboxes of the array.
	 * @see ajouterPostbox
	 * @param array $postBoxes Array of all postboxes to display.
	 * @return string HTML code the postboxes.
	 */
	static function displayPostBoxes($postBoxes, $idDiv='')
	{
		$display = '
						<div id="' . $idDiv . '" class="">';
		foreach($postBoxes as $postBox)
		{
			$id = (isset($postBox['id']))?$postBox['id']:'';
			$titre = (isset($postBox['titre']))?$postBox['titre']:'titre';
			$corps = (isset($postBox['corps']))?$postBox['corps']:'';
			$pagination = (isset($postBox['pagination']))?$postBox['pagination']:false;
			$table = (isset($postBox['table']))?$postBox['table']:'';
			$display = $display . EvaDisplayDesign::ajouterPostbox($id,$titre,$corps,$pagination,$table);
		}
		if($postBox['pagination'])
		{
			$display = $display . '
			<script type="text/javascript">
				digirisk(document).ready(function() {
				});
			</script';
		}
		$display = $display .'
						</div>';
		return $display;
	}

	/**
	 * Returns the HTML code of a postbox with the scripts for.
	 * If the postbox need a paging system, it is link to the site wire
	 * For the good working of the page, maximum one postbox per page should have a paging system
	 * @param string $idPostBox Id attribute for the postbox.
	 * @param string $titrePostBox Title to be displayed in the postbox.
	 * @param string $corpsPostBox Postbox body HTML code.
	 * @param bool $pagination Need the postbox a paging system ?
	 * @param string $table Postbox-relative table name.
	 * @return string HTML code the postboxes.
	 */
	static function ajouterPostbox($idPostBox, $titrePostBox, $corpsPostBox, $pagination, $table)
	{
		switch($table)
		{
			case TABLE_GROUPEMENT:
				$table2 = TABLE_UNITE_TRAVAIL;
				break;
			case TABLE_CATEGORIE_DANGER:
				$table2 = TABLE_DANGER;
				break;
			default:
				$table2 = '';
				break;
		}
		$scriptPostBox = '<script type="text/javascript">
	digirisk(document).ready(function() {
		digirisk(\'#' . $idPostBox . 'Fleche\').click(function() {
			digirisk(\'#' . $idPostBox . '\').toggleClass(\'closed\');
		});
		if(document.getElementById(\'filAriane\').lastChild == document.getElementById(\'filAriane\').firstChild)
		{
			digirisk(\'#' . $idPostBox . 'Pere\').addClass(\'hidden\');
		}
		else
		{
			digirisk(\'#' . $idPostBox . 'Pere\').removeClass(\'hidden\');
		}
		digirisk(\'#' . $idPostBox . 'Pere\').click(function() {
			if(digirisk(\'#filAriane :last-child\') != digirisk(\'#filAriane :first-child\'))
			{
				digirisk(\'#page' . $idPostBox . 'Reference\').val(1);
				if(digirisk(\'#filAriane :last-child\').is("label"))
				{
					digirisk(\'#filAriane :last-child\').remove();
				}
				digirisk(\'#filAriane :last-child\').remove();
				digirisk(\'#filAriane :last-child\').remove();
				digirisk(\'#' . $idPostBox . ' h3 span\').html(digirisk(\'#filAriane :last-child\').html());
				var id = digirisk(\'#filAriane :last-child\').attr("id");
				var reg = new  RegExp("(element)", "g");
				var id = id.replace(reg, "");
				digirisk(\'#identifiantActuelle' . $idPostBox . '\').val(id);

				changementPage("right", "' . $table . '", digirisk("#page' . $idPostBox . 'Reference").val(), digirisk("#identifiantActuelle' . $idPostBox . '").val(), "affichageTable", "main");
				changementPage("left", "' . $table . '", digirisk("#page' . $idPostBox . 'Reference").val(), digirisk("#identifiantActuelle' . $idPostBox . '").val(), "affichageTable", "main");
				return false;
			}
		});
		if(document.getElementById(\'favorite-inside-' . $idPostBox . '\') != null)
		{
			document.getElementById(\'favorite-inside-' . $idPostBox . '\').style.width = digirisk(\'#favorite-actions-' . $idPostBox . '\').innerWidth() -4 + "px";
		}
		digirisk(\'#favorite-toggle-' . $idPostBox . '\').hover(function() {
			digirisk(\'#favorite-first-' . $idPostBox . '\').addClass("slide-down");
			digirisk(\'#favorite-inside-' . $idPostBox . '\').addClass("slideDown");
			digirisk(\'#favorite-inside-' . $idPostBox . '\').slideDown(100);
		});
		digirisk(\'#favorite-first-' . $idPostBox . '\').click(function() {
			digirisk(\'#favorite-first-' . $idPostBox . '\').addClass("slide-down");
			digirisk(\'#favorite-inside-' . $idPostBox . '\').addClass("slideDown");
			digirisk(\'#favorite-inside-' . $idPostBox . '\').slideDown(100);
		});
		var timeoutFavoriteActions;
		digirisk(\'#favorite-actions-' . $idPostBox . '\').hover(function() {
			clearTimeout(timeoutFavoriteActions);
		},function() {
			timeoutFavoriteActions = setTimeout
			(
				function()
				{
					//document.getElementById(\'favorite-inside-' . $idPostBox . '\').style.display = "none";
					digirisk(\'#favorite-inside-' . $idPostBox . '\').slideUp(100);
					setTimeout
					(
						function()
						{
							digirisk(\'#favorite-first-' . $idPostBox . '\').removeClass("slide-down");
							digirisk(\'#favorite-inside-' . $idPostBox . '\').removeClass("slideDown");
						},
						100
					);
				},
				500
			);
		});

		digirisk(\'#favorite-first-link-' . $idPostBox . '\').click(function() {
			digirisk("#rightEnlarging").show();
			digirisk("#equilize").click();

			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
				"table": "' . $table . '",
				"act": "add",
				"page": digirisk(\'#page' . $idPostBox . 'Reference\').val(),
				"idPere": digirisk(\'#identifiantActuelle' . $idPostBox . '\').val(),
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageTable",
				"partition": "tout"
			});
			digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
				"table": "' . $table . '",
				"act": "add",
				"page": digirisk(\'#page' . $idPostBox . 'Reference\').val(),
				"idPere": digirisk(\'#identifiantActuelle' . $idPostBox . '\').val(),
				"partie": "left",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageTable",
				"partition": "tout"
			});

			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieGauche").html(digirisk("#loadingImg").html());
			return false;
		});

		digirisk(\'#favorite-second-link-' . $idPostBox . '\').click(function() {
			digirisk("#rightEnlarging").show();
			digirisk("#equilize").click();

			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
				"table": "' . $table2 . '",
				"act": "add",
				"page": digirisk(\'#page' . $idPostBox . 'Reference\').val(),
				"idPere": digirisk(\'#identifiantActuelle' . $idPostBox . '\').val(),
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageTable",
				"partition": "tout"
			});
			digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
				"table": "' . $table2 . '",
				"act": "add",
				"page": digirisk(\'#page' . $idPostBox . 'Reference\').val(),
				"idPere": digirisk(\'#identifiantActuelle' . $idPostBox . '\').val(),
				"partie": "left",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageTable",
				"partition": "tout"
			});

			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieGauche").html(digirisk("#loadingImg").html());
			return false;
		});
	});
</script>';
		$postBox = '<div class="postbox " id="' . $idPostBox . '">' . $scriptPostBox . '
						<div id="' . $idPostBox . 'Fleche" title="Cliquer pour inverser." class="handlediv"></div>';
		if($pagination)
		{
			$postBox = $postBox . '
						<div id="' . $idPostBox . 'Pere" title="' . __('Cliquer pour remonter d\'un niveau.', 'evarisk') . '" class="flechePere"><img alt="pere" src="' . PICTO_FLECHE_PERE . '" title="' . $titreIcone . '"/></div>';
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
			$postBox = $postBox . '
						<div id="favorite-actions-' . $idPostBox . '" class="alignright favorite-actions">
							<div id="favorite-toggle-' . $idPostBox . '" class="favorite-toggle alignright"></div>
							<div id="favorite-first-' . $idPostBox . '" class="favorite-first"><a href="#" onclick="return false;">' . __('Ajouter', 'evarisk') . '...</a>
							</div>
							<div id="favorite-inside-' . $idPostBox . '" style="display: none;" class="favorite-inside">
								<div class="favorite-action"><a id="favorite-first-link-' . $idPostBox . '" href="#">' . $first . '</a></div>
								<div class="favorite-action"><a id="favorite-second-link-' . $idPostBox . '" href="#">' . $second . '</a></div>
							</div>
						</div>';
		}
		$postBox = $postBox . '
						<h3 class="handle"><span>' . $titrePostBox . '</span></h3>
						<div class="inside" id="' . $idPostBox . 'Container" >' . $corpsPostBox . '</div>
					</div>';
		return $postBox;
	}

	/**
	 * Returns the HTML code for the paging system with the scripts for.
	 * @param array $id Identifier of the post witch is link the paging system.
	 * @param string $pageMax Paging system maximum page.
	 * @param string $table Paging system-relative table name.
	 * @return string HTML code of the table.
	 */
	static function afficherPagination($id, $pageMax, $table)
	{
		$pagination = '
<script type="text/javascript">
	digirisk(document).ready(function() {
		digirisk(\'#element1\').unbind("click");
		digirisk(\'#element1\').click(function() {
			digirisk(\'#identifiantActuelle' . $id . '\').val(1);
			digirisk(\'#page' . $id . 'Reference\').val(1);
			while(digirisk(\'#filAriane :last-child\').attr("id") != "element1")
			{
				digirisk(\'#filAriane :last-child\').remove();
			}

			changementPage("right", "' . $table . '", digirisk("#page' . $id . 'Reference").val(), digirisk("#identifiantActuelle' . $id . '").val(), "affichageTable", "main");
			changementPage("left", "' . $table . '", digirisk("#page' . $id . 'Reference").val(), digirisk("#identifiantActuelle' . $id . '").val(), "affichageTable", "main");
			digirisk(\'#' . $id . ' h3 span\').html(digirisk(\'#filAriane :first-child\').html());
			return false;
		});
	});
	digirisk(document).ready(function() {
		digirisk(\'#page' . $id . '\').keypress(function(event) {
			if (event.which && (event.which < 48 || event.which >57) && event.which != 8 && event.which != 13) {
				event.preventDefault();
			}
		});
		digirisk(\'#page' . $id . '\').keyup(function() {
			digirisk(\'#page' . $id . 'Reference\').val(digirisk(\'#page' . $id . '\').val())
		});

		var page = digirisk(\'#page' . $id . 'Reference\').val();
		digirisk(\'#page' . $id . '\').val(page);
		digirisk(\'#formPagination' . $id . '\').click(function(event) {
			if(digirisk(event.target).is(\'.button\'))
			{
				if(digirisk("#filAriane :last-child").is("label"))
					digirisk("#filAriane :last-child").remove();
				switch((event.target).id)
				{
					case "first' . $id . '":
						page = 1;
						break;
					case "previous' . $id . '":
						page = parseInt(digirisk(\'#page' . $id . 'Reference\').val()) - 1;
						if(page < 1)
						{
							page = 1;
						}
						break;
					case "next' . $id . '":
						page = parseInt(digirisk(\'#page' . $id . 'Reference\').val()) + 1;
						if(page > digirisk(\'#pageMax' . $id . '\').value)
						{
							page = digirisk(\'#pageMax' . $id . '\').val();
						}
						break;
					case "last' . $id . '":
						page = parseInt(digirisk(\'#pageMax' . $id . '\').val());
						break;
					case "text' . $id . '":
						if(parseInt(digirisk(\'#page' . $id . 'Reference\').val()) > parseInt(digirisk(\'#pageMax' . $id . '\').val()))
						{
							page = parseInt(digirisk(\'#pageMax' . $id . '\').val());
						}
						else
						{
							if(parseInt(digirisk(\'#page' . $id . 'Reference\').val()) < 1)
							{
								page = 1;
							}
							else
							{
								if(digirisk(\'#page' . $id . 'Reference\').val() != "")
								{
									page = parseInt(digirisk(\'#page' . $id . 'Reference\').val());
								}
							}
						}
						break;
				}
				digirisk(\'#page' . $id . 'Reference\').val(page);

				changementPage("right", "' . $table . '", digirisk("#page' . $id . 'Reference").val(), digirisk("#identifiantActuelle' . $id . '").val(), "affichageTable", "main");
				changementPage("left", "' . $table . '", digirisk("#page' . $id . 'Reference").val(), digirisk("#identifiantActuelle' . $id . '").val(), "affichageTable", "main");

				digirisk(\'#page' . $id . '\').val(page);
				return false;
			}
		});
	});
</script>
		<form method="POST" id="formPagination' . $id . '" name="formPagination' . $id . '">
			<input type="submit" onclick="return false;" id="text' . $id . '" class="button alignright hidden" />
			<input type="submit" onclick="return false;" id="last' . $id . '" value=">>" class="button alignright" />
			<input type="submit" onclick="return false;" id="next' . $id . '" value=">" class="button alignright" />
			<input type="text" value=' . $pageMax . ' name="pageMax' . $id . '" id="pageMax' . $id . '" class="alignright textPagination" readonly="readonly"/>
			<span class="alignright">/</span>
			<input type="text" value=1 name="page' . $id . '" id="page' . $id . '" class="alignright textPagination" />
			<input type="submit" onclick="return false;" id="previous' . $id . '" value="<" class="button alignright" />
			<input type="submit" onclick="return false;" id="first' . $id . '" value="<<" class="button alignright" />
		<form>
		<div class="clear"></div><br />';
		return $pagination;
	}

	/**
	 * Returns the list view tree table with scripts that allow you to display the right part by clicking on the elements and the drag and drop.
	 * @see getCorpsTableArborescence
	 * @param Element_of_a_tree $racine Root element of the table.
	 * @param string $table Table name.
	 * @param int $idTable HTML Id attribute for the table.
	 * @param string $nomRacine Text to be displayed in the root of the table.
	 * @return string HTML code of the table.
	 */
	public static function getTableArborescence($racine, $table, $idTable, $nomRacine, $draggable = true, $outputAction = true)
	{
		$elements = $monCorpsTable = $class = $infoRacine = $tableArborescente = '';
		$showTrashUtilities = false;
		switch ($table) {
			case TABLE_GROUPEMENT:
				$elements = Arborescence::getFils($table, $racine, "nom ASC");
				$sousTable = TABLE_UNITE_TRAVAIL;
				$subElements = EvaGroupement::getUnitesDuGroupement($racine->id);
				$divDeChargement = 'message';
				$titreInfo = __("Somme des risques", 'evarisk');
				$actionSize = 6;
				$actions = '
							<td class="noPadding addMain" id="addMain' . $racine->id . '">';
				if(current_user_can('digi_add_groupement'))
				{
					$actions .=
							'<img style="width:' . TAILLE_PICTOS_ARBRE . ';"  src="' . PICTO_LTL_ADD_GROUPEMENT . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('un groupement', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('un groupement', 'evarisk')) . '" />';
				}
				else
				{
					$actions .= '&nbsp;';
				}
				$actions .= '
							</td>';
				/*	Add trash	*/
				$main_option = get_option('digirisk_options');
				if(($main_option['digi_activ_trash'] == 'oui') && (current_user_can('digi_view_groupement_trash') || current_user_can('digi_view_unite_trash')))
				{
					$showTrashUtilities = true;
					$actions .= '
							<td colspan="' . ($actionSize - 3) . '" >&nbsp;</td>
							<td class="noPadding trash" id="trash' . $racine->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';"  src="' . EVA_IMG_ICONES_PLUGIN_URL . 'trash.png" alt="Trash" title="' . __('Acc&eacute;der &agrave; la corbeille', 'evarisk') . '" /></td>';
				}
			break;
			case TABLE_CATEGORIE_DANGER:
				$elements = Arborescence::getFils($table, $racine, "nom ASC");
				$sousTable = TABLE_DANGER;
				$subElements = categorieDangers::getDangersDeLaCategorie($racine->id);
				$divDeChargement = 'message';
				$titreInfo = null;
				$nomRacine = '&nbsp;';
				$actionSize = 4;
				$actions = '
							<td class="noPadding addMain" id="addMain' . $racine->id . '">';
				if(current_user_can('digi_add_danger_category'))
				{
					$actions .= '<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_LTL_ADD_CATEGORIE_DANGER . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('une cat&eacute;gorie de dangers', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('une cat&eacute;gorie de dangers', 'evarisk')) . '" />';
				}
				else
				{
					$actions .= '&nbsp;';
				}
				$actions .= '</td>';
				$addMainPicture = '<img style=\'width:' . TAILLE_PICTOS_ARBRE . ';\' src=\'' .PICTO_LTL_ADD_CATEGORIE_DANGER . '\' alt=\'' . sprintf(__('Ajouter %s', 'evarisk'), __('une cat&eacute;gorie de dangers', 'evarisk')) . '\' title=\'' . sprintf(__('Ajouter %s', 'evarisk'), __('une cat&eacute;gorie de dangers', 'evarisk')) . '\' />';
				$addSecondaryPicture = '<img style=\'width:' . TAILLE_PICTOS_ARBRE . ';\' src=\'' .PICTO_LTL_ADD_DANGER . '\' alt=\'' . sprintf(__('Ajouter %s', 'evarisk'), __('un danger', 'evarisk')) . '\' title=\'' . sprintf(__('Ajouter %s', 'evarisk'), __('un danger', 'evarisk')) . '\' />';
				/*	Add trash	*/
				$main_option = get_option('digirisk_options');
				if(($main_option['digi_activ_trash'] == 'oui') && (current_user_can('digi_view_groupement_trash') || current_user_can('digi_view_unite_trash')))
				{
					$showTrashUtilities = true;
					$actions .= '
							<td colspan="' . ($actionSize - 2) . '" >&nbsp;</td>
							<td class="noPadding trash" id="trash' . $racine->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';"  src="' . EVA_IMG_ICONES_PLUGIN_URL . 'trash.png" alt="Trash" title="' . __('Acc&eacute;der &agrave; la corbeille', 'evarisk') . '" /></td>';
				}
			break;
			case TABLE_TACHE:
				$elements = Arborescence::getFils( $table, $racine, "nom ASC" );
				$sousTable = TABLE_ACTIVITE;
		        $tacheRacine = new EvaTask( $racine->id );
		        $tacheRacine->load( );
				$subElements = $tacheRacine->getWPDBActivitiesDependOn( );
				$divDeChargement = 'message';
				$titreInfo = __( 'Avancement', 'evarisk' );
				$nomRacine = __( 'T&acirc;ches', 'evarisk' );
				$actionSize = 7;
				$actions = '
						<td class="noPadding addMain" id="addMain' . $racine->id . '">';
				if ( current_user_can('digi_add_task') ) {
					$actions .=
							'<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_LTL_ADD_TACHE . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('une t&acirc;che', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('une t&acirc;che', 'evarisk')) . '" />';
				}
				else {
					$actions .= '&nbsp;';
				}
				$actions .= '
						</td>';
				$addMainPicture = '<img style=\'width:' . TAILLE_PICTOS_ARBRE . ';\' src=\'' .PICTO_LTL_ADD_TACHE . '\' alt=\'' . sprintf(__('Ajouter %s', 'evarisk'), __('une t&acirc;che', 'evarisk')) . '\' title=\'' . sprintf(__('Ajouter %s', 'evarisk'), __('une t&acirc;che', 'evarisk')) . '\' />';
				$addSecondaryPicture = '<img style=\'width:' . TAILLE_PICTOS_ARBRE . ';\' src=\'' .PICTO_LTL_ADD_ACTIVITE . '\' alt=\'' . sprintf(__('Ajouter %s', 'evarisk'), __('une action', 'evarisk')) . '\' title=\'' . sprintf(__('Ajouter %s', 'evarisk'), __('une action', 'evarisk')) . '\' />';
				/*	Add trash	*/
				$main_option = get_option('digirisk_options');
				if ( ( $main_option['digi_activ_trash'] == 'oui' ) && ( current_user_can('digi_view_groupement_trash') || current_user_can('digi_view_unite_trash') ) ) {
					$showTrashUtilities = true;
					$actions .= '
							<td colspan="' . ($actionSize - 2) . '" >&nbsp;</td>
							<td class="noPadding trash" id="trash' . $racine->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';"  src="' . EVA_IMG_ICONES_PLUGIN_URL . 'trash.png" alt="Trash" title="' . __('Acc&eacute;der &agrave; la corbeille', 'evarisk') . '" /></td>';
				}
			break;
			case TABLE_GROUPE_QUESTION:
				$elements = Arborescence::getFils($table, $racine, "code ASC");
				$sousTable = TABLE_QUESTION;
				$subElements = EvaGroupeQuestions::getQuestionsDuGroupeQuestions($racine->id);
				$divDeChargement = 'ajax-response';
				$titreInfo = null;
				$actionSize = 3;
				$actions = '
							<td class="noPadding addMain" id="add-node-' . $racine->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_INSERT . '" alt="' . __('Inserer sous le titre', 'evarisk') . '" title="' . __('Inserer sous le titre', 'evarisk') . '" /></td>
							<td class="noPadding addSecondary" id="edit-node-' . $racine->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';display:none;" id="img_edit_racine" src="' . PICTO_EDIT . '" alt="' . __('Modifier le titre', 'evarisk') . '" title="' . __('Modifier le titre', 'evarisk') . '" /></td>
							<td></td>';
				$addMainPicture = '<img style=\'width:' . TAILLE_PICTOS_ARBRE . ';\' src=\'' .PICTO_INSERT . '\' alt=\'' . __('Inserer sous le titre', 'evarisk') . '\' title=\'' . __('Inserer sous le titre', 'evarisk') . '\' />';
				$addSecondaryPicture = '';
			break;
		}

		$trouveElement = count($elements);
		if ( $trouveElement ) {
			$monCorpsTable = EvaDisplayDesign::getCorpsTableArborescence($elements, $racine, $table, $titreInfo, $idTable);
		}
		$monCorpsSubElements = '';
		foreach($subElements as $subElement)
		{
			switch($table)
			{
				case TABLE_GROUPEMENT:
					$subAffichage = $subElement->nom;
					break;
				case TABLE_CATEGORIE_DANGER:
					$subAffichage = $subElement->nom;
					break;
				case TABLE_TACHE:
					$subAffichage = $subElement->nom;
					break;
				case TABLE_GROUPE_QUESTION:
					$subAffichage = $subElement->enonce;
					break;
			}
		}

		$draggableScript = '';
		if($draggable){
			$draggableScript .= '
		//	Draggable interface
		var draggedObject;
		var draggedObjectFather;

		// Configure draggable nodes
		digirisk("#' . $idTable . ' .noeudArbre, #' . $idTable . ' .feuilleArbre").draggable({
			start: function(event, ui){
				draggedObject = jQuery(this).closest("tr").attr("id");//event.target.id;
				var classNames = jQuery(this).closest("tr").attr("class").split(\' \');//event.target.className.split(\' \');
				draggedObjectFather = "temp";
				for(key in classNames){
					if(classNames[key].match("child-of-")){
						draggedObjectFather = digirisk("#" + classNames[key].substring(9));
						draggedObjectFather = draggedObjectFather.attr("id");
					}
				}
			},
			helper: "clone",
			opacity: .75,
			refreshPositions: true,
			revert: "invalid",
			revertDuration: 300,
			scroll: true
		});

		var dropFunction = function(event, ui){
			// Call jQuery treeTable plugin to move the branch
			digirisk(digirisk(ui.draggable)).appendBranchTo(this);
			var dropLocation = jQuery(this).closest("tr").attr("id");//event.target.id;

			digirisk("#equilize").click();
			digirisk("#' . $divDeChargement . '").addClass("updated");
			digirisk("#' . $divDeChargement . '").html("' . __('Transfert en cours...', 'evarisk') . '");
			digirisk("#' . $divDeChargement . '").show();
			digirisk("#' . $divDeChargement . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
				"post":"true",
				"table":"' . $table . '",
				"location":"' . $idTable . '",
				"act":"transfert",
				"idElementSrc":draggedObject,
				"idElementOrigine":draggedObjectFather,
				"idElementDest":dropLocation
			});
			setTimeout(
				function(){
					digirisk("#' . $idTable . ' tr.parent").each(function(){
						var childNodes = digirisk("table#' . $idTable . ' tbody tr.child-of-" + digirisk(this).attr("id"));
						if(childNodes.length > 0){
							digirisk(this).removeClass("sansFils");
							digirisk(this).addClass("aFils");
							var premierFils = digirisk("table#' . $idTable . ' tbody tr.child-of-" + digirisk(this).attr("id") + ":first").attr("id");
							if(premierFils != premierFils.replace(/node-' . $idTable . '-/g,"")){
								digirisk(this).addClass("aFilsNoeud");
								digirisk(this).droppable( "option", "accept", ".noeudArbre" );
								digirisk("#' . $idTable . ' #addSecondary" + digirisk(this).attr("id").replace(/node-' . $idTable . '-/g,"") + " img").hide();
								digirisk("#' . $idTable . ' #addMain" + digirisk(this).attr("id").replace(/node-' . $idTable . '-/g,"") + " img").show();
							}
							else{
								digirisk(this).addClass("aFilsFeuille");
								digirisk(this).droppable( "option", "accept", ".feuilleArbre" );
								digirisk("#' . $idTable . ' #addMain" + digirisk(this).attr("id").replace(/node-' . $idTable . '-/g,"") + " img").hide();
								digirisk("#' . $idTable . ' #addSecondary" + digirisk(this).attr("id").replace(/node-' . $idTable . '-/g,"") + " img").show();
							}
						}
						else{
							digirisk(this).removeClass("aFilsNoeud");
							digirisk(this).removeClass("aFilsFeuille");
							digirisk(this).removeClass("aFils");
							digirisk(this).addClass("sansFils");
							digirisk(this).droppable("option", "accept", ".noeudArbre, .feuilleArbre");
							digirisk("#' . $idTable . ' #addSecondary" + digirisk(this).attr("id").replace(/node-' . $idTable . '-/g,"") + " img").show();
							digirisk("#' . $idTable . ' #addMain" + digirisk(this).attr("id").replace(/node-' . $idTable . '-/g,"") + " img").show();
						}
					});
					digirisk(document).ajaxStop(function(){
						digirisk("#' . $divDeChargement . '").removeClass("updated");
					});
				},
				10
			);
		}

		overFunction = function(event, ui){
			// Make the droppable branch expand when a draggable node is moved over it.
			if(this.id != digirisk(ui.draggable.parents("tr")[0]).id && !digirisk(this).is(".expanded")){
				var overObject = digirisk(this);
				setTimeout(function(){
					if(overObject.is(".accept")){
						overObject.expand();
					}
				},
					500
				);
		  }
		}
		digirisk("#' . $idTable . ' .aFilsNoeud, #' . $idTable . ' .racineArbre").droppable({
			accept: "#' . $idTable . ' .noeudArbre",
			drop: dropFunction,
			hoverClass: "accept",
			over: overFunction
		});
		digirisk("#' . $idTable . ' .aFilsFeuille").droppable({
			accept: "#' . $idTable . ' .feuilleArbre",
			drop: dropFunction,
			hoverClass: "accept",
			over: overFunction
		});
		digirisk("#' . $idTable . ' .sansFils").droppable({
			accept: "#' . $idTable . ' .feuilleArbre, #' . $idTable . ' .noeudArbre",
			drop: dropFunction,
			hoverClass: "accept",
			over: overFunction
		});';
		}

		$tableArborescente .= '
				<table id="' . $idTable . '" style="border-spacing: 0px; border-collapse: collapse;" class="widefat post fixed">
					<thead>
						<tr>
							<th >' . $nomRacine . '</th>';
		if($titreInfo != null)
		{
			//$tableArborescente .= '<th class="infoList">' . $titreInfo . '</th>';
			$infoRacine = '';//EvaDisplayDesign::getInfoArborescence($table, $racine->id);
// 			$infoRacine = '
// 							<td id ="info-' . $racine->id . '" class="' . $infoRacine['class'] . '"></td>';
		}
		if($outputAction)
		{
			$tableArborescente .= '
							<th colspan="' . $actionSize . '" class="actionButtonList">' . __('Actions', 'evarisk') . '</th>';
		}
			$tableArborescente .= '
						</tr>
					</thead>
					<tbody>
						<tr id="node-' . $idTable . '-' . $racine->id . '" class="parent racineArbre">
							<td id="tdRacine' . $idTable . '">&nbsp;</td>' . $infoRacine;
		if($outputAction)
		{
			$tableArborescente .= $actions;
		}
		$tableArborescente .= '
						</tr>
						' . $monCorpsTable . '
					</tbody>
				</table>';

		/*	Add trash utilities if user is allowed	*/
		$trashScript = '';
		if($showTrashUtilities){
			$tableArborescente .= '
				<div id="trashContainer" title="' . __('Liste des &eacute;l&eacute;ments supprim&eacute;s', 'evarisk') . '" >&nbsp;</div>';
			$trashScript = '
		digirisk("#trashContainer").dialog({
			autoOpen: false,
			modal: true,
			width: 800,
			height: 600,
			close: function(){
				digirisk(this).html("");
			}';
			switch ( $table ) {
				case TABLE_GROUPEMENT:
					$trashScript .= ',
			buttons:{
				"' . __('Reinitialiser l\'arbre des groupements', 'evarisk') . '": function() {
					if(confirm(digi_html_accent_for_js("' . __('Si vous effectuez cette action, tous les groupements de l\'arbre seront replac&eacute;s &agrave; la racine de votre architecture', 'evarisk') . '"))){
						jQuery.getJSON(EVA_AJAX_FILE_URL, { post: "true", nom: "initialize_groupement_tree" },
							function(data){
								if(data[0]) {
									changementPage("left", "' . $table . '", 1, 1, "affichageListe", "main");
									digirisk("#trashContainer").dialog("close");
								}
								else {
									alert(data[1]);
								}
							});
					}
				}
			}';
					break;
				case TABLE_TACHE:
					$trashScript .= ',
			buttons:{
				"' . __('Reinitialiser l\'arbre des taches', 'evarisk') . '" : function() {
					if(confirm(digi_html_accent_for_js("' . __('Si vous effectuez cette action, toutes les t&acirc;ches de l\'arbre seront replac&eacute;se &agrave; la racine de votre architecture', 'evarisk') . '"))){
						jQuery.getJSON(EVA_AJAX_FILE_URL, { post: "true", nom: "initialize_task_tree" },
							function(data){
								if(data[0]) {
									changementPage("left", "' . $table . '", 1, 1, "affichageListe", "main");
									digirisk("#trashContainer").dialog("close");
								}
								else {
									alert(data[1]);
								}
							});
					}
				}
			}';
					break;
			}

			$trashScript .= '
		});
		digirisk(".trash img").click(function(){
			digirisk("#trashContainer").dialog("open");
			digirisk("#trashContainer").html(digirisk("#loadingImg").html());
			digirisk("#trashContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
				"post": "true",
				"tableProvenance": "' . $table . '",
				"nom": "loadTrash"
			});
		});';
		}

		$script = '
<script type="text/javascript">
	digirisk(document).ready(function(){
		//	Change the simple table in treetable
		digirisk("#' . $idTable . '").treeTable();
		selectRowInTreeTable("' . $idTable . '");

		var span = document.getElementById("tdRacine' . $idTable . '").firstChild;
		digirisk("#' . $idTable . ' #node-' . $idTable . '-' . $racine->id . '").toggleBranch();
		document.getElementById("tdRacine' . $idTable . '").removeChild(span);

		digirisk("#' . $idTable . ' tr.parent").each(function(){
			var childNodes = digirisk("table#' . $idTable . ' tbody tr.child-of-" + digirisk(this).attr("id"));
			if(childNodes.length > 0){
				digirisk(this).addClass("aFils");
				var premierFils = digirisk("table#' . $idTable . ' tbody tr.child-of-" + digirisk(this).attr("id") + ":first").attr("id");
				if(premierFils != premierFils.replace(/node/g,"")){
					digirisk(this).addClass("aFilsNoeud");
				}
				else{
					digirisk(this).addClass("aFilsFeuille");
				}
			}
			else{
				digirisk(this).removeClass("aFils");
				digirisk(this).addClass("sansFils");
			}
		});

		' . $trashScript . '

		' . $draggableScript . '

		digirisk("#' . $idTable . ' .addMain img").click(function(){
			var nodeId = digirisk(this).parent("td").parent("tr").attr("id").replace("node-' . $idTable . '-", "");
			var expanded = reInitTreeTable();';
		if($table == TABLE_GROUPEMENT){
		$script .= '
			digirisk("#menu").val("gestiongrptut");';
		}
			$script .= '
			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
				"post": "true",
				"table": "' . $table . '",
				"act": "add",
				"page": digirisk("#pagemainPostBoxReference").val(),
				"idPere": nodeId,
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageListe",
				"partition": "tout",
				"expanded": expanded
			});
		});

		digirisk("#' . $idTable . ' .addSecondary img").click(function(){
			var nodeId = digirisk(this).parent("td").parent("tr").attr("id").replace("node-' . $idTable . '-", "");
			var expanded = reInitTreeTable();';
		if($table == TABLE_GROUPEMENT){
		$script .= '
			digirisk("#menu").val("gestiongrptut");';
		}
			$script .= '
			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
			{
				"post": "true",
				"table": "' . $sousTable . '",
				"act": "add",
				"page": digirisk("#pagemainPostBoxReference").val(),
				"idPere": nodeId,
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageListe",
				"partition": "tout",
				"expanded": expanded
			});
		});';

		if($table == TABLE_GROUPE_QUESTION)
		{
			$script .= '

		digirisk("#' . $idTable . ' #add-node-' . $racine->id . '").click(function(){
			for (var i=0;i<document.regulatoryWatchForm.titrePere.options.length;i++)
			{
				if (document.regulatoryWatchForm.titrePere.options[i].value == ' . $racine->id . ')
					document.regulatoryWatchForm.titrePere.options[i].selected = true;
			}
			digirisk("#traiter").click();
		});';
		}

		switch($table)
		{
			case TABLE_CATEGORIE_DANGER :
			case TABLE_GROUPEMENT :
			case TABLE_TACHE :
			{	/*	Tree leaf	*/
				$script .= '
		//	The user click on the delete button of a leaf
		digirisk("#' . $idTable . ' .delete-leaf").click(function(){
			var leafId = digirisk(this).parent("tr").attr("id").replace("leaf-", "");
			digirisk("#menu").val("gestiongrptut");
			var expanded = reInitTreeTable();
			if(confirm("' . __('Etes vous sur de vouloir supprimer cet element?', 'evarisk') . '")){
				digirisk("#partieEdition").html("");
				digirisk("#partieGauche").html(digirisk("#loadingImg").html());
				digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
				{
					"post": "true",
					"table": "' . $sousTable . '",
					"act": "delete",
					"id": leafId,
					"partie": "left",
					"menu": digirisk("#menu").val(),
					"affichage": "affichageListe",
					"partition": "tout",
					"expanded": expanded
				});
			}
		});

		//	The user click on the edit button of a leaf
		digirisk("#' . $idTable . ' .edit-leaf").click(function(){
			var leafId = digirisk(this).parent("tr").attr("id").replace("leaf-", "");
			selectRowInTreeTable("' . $idTable . '");
			digirisk("#menu").val("gestiongrptut");
			var expanded = reInitTreeTable();
			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
				"post": "true",
				"table": "' . $sousTable . '",
				"act": "edit",
				"id": leafId,
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageListe",
				"expanded": expanded,
				"partition": "edit",
			});
		});

		//	The user click on the name of the leaf
		digirisk(".nomFeuilleArbre, .treeTableInfoColumn").click(function(){
			var leafId = digirisk(this).parent("tr").attr("id").replace("leaf-", "");
			selectRowInTreeTable("' . $idTable . '");
			digirisk("#menu").val("risq");
			var expanded = reInitTreeTable();
			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
				"table": "' . $sousTable . '",
				"act": "edit",
				"id": leafId,
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageListe",
				"expanded": expanded
			});
		});

		//	The user click on the edit button of a leaf
		digirisk("#' . $idTable . ' .summary-leaf").click(function(){
			var leafId = digirisk(this).parent("tr").attr("id").replace("leaf-", "");
			selectRowInTreeTable("' . $idTable . '");
			digirisk("#menu").val("risq");
			var expanded = reInitTreeTable();
			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
				"post": "true",
				"table": "' . $sousTable . '",
				"act": "edit",
				"id": leafId,
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageListe",
				"partition": "bilan",
				"expanded": expanded
			});
		});

		digirisk("#' . $idTable . ' .docmanager-leaf").click(function(){
			var leafId = digirisk(this).parent("tr").attr("id").replace("leaf-", "");
			selectRowInTreeTable("' . $idTable . '");
			digirisk("#menu").val("risq");
			var expanded = reInitTreeTable();
			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
				"post": "true",
				"table": "' . $sousTable . '",
				"act": "edit",
				"id": leafId,
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageListe",
				"partition": "docmanager",
				"expanded": expanded
			});
		});

		digirisk("#' . $idTable . ' .timepicker-leaf").click(function(){
			var leafId = digirisk(this).parent("tr").attr("id").replace("leaf-", "");
			selectRowInTreeTable("' . $idTable . '");
			digirisk("#menu").val("risq");
			var expanded = reInitTreeTable();
			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
				"post": "true",
				"table": "' . $sousTable . '",
				"act": "edit",
				"id": leafId,
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageListe",
				"partition": "timepicker",
				"expanded": expanded
			});
		});

		//	The user click on the edit button of a leaf
		digirisk("#' . $idTable . ' .risk-leaf").click(function(){
			var leafId = digirisk(this).parent("tr").attr("id").replace("leaf-", "");
			selectRowInTreeTable("' . $idTable . '");
			digirisk("#menu").val("risq");
			var expanded = reInitTreeTable();
			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
				"post": "true",
				"table": "' . $sousTable . '",
				"act": "edit",
				"id": leafId,
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageListe",
				"partition": "risq",
				"expanded": expanded
			});
		});';
			}
			{	/*	Tree node	*/
				$script .= '
		//	The user click on the delete button of a node
		digirisk("#' . $idTable . ' .delete-node").click(function(){
			var nodeId = digirisk(this).parent("tr").attr("id").replace("node-' . $idTable . '-", "").replace("-name", "");
			digirisk("#menu").val("gestiongrptut");
			var expanded = reInitTreeTable();
			if(confirm("' . __('Etes vous sur de vouloir supprimer cet element?\r\nATTENTION: si cet element possede des sous elements, ils seront inaccessibles', 'evarisk') . '")){
				digirisk("#partieGauche").html(digirisk("#loadingImg").html());
				digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
				{
					"post": "true",
					"table": "' . $table . '",
					"act": "delete",
					"id": nodeId,
					"partie": "left",
					"menu": digirisk("#menu").val(),
					"affichage": "affichageListe",
					"partition": "tout",
					"expanded": expanded
				});
			}
		});
		//	The user click on the delete button of a node
		digirisk("#' . $idTable . ' .edit-node").click(function(){
			var nodeId = digirisk(this).parent("tr").attr("id").replace("node-' . $idTable . '-", "").replace("-name", "");
			selectRowInTreeTable("' . $idTable . '");
			digirisk("#menu").val("gestiongrptut");
			var expanded = reInitTreeTable();
			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
			{
				"post": "true",
				"table": "' . $table . '",
				"act": "edit",
				"id": nodeId,
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageListe",
				"partition": "edit",
				"expanded": expanded
			});
		});
		//	The user click on the summary button of a node
		digirisk("#' . $idTable . ' .summary-node").click(function(){
			var nodeId = digirisk(this).parent("tr").attr("id").replace("node-' . $idTable . '-", "").replace("-name", "");
			selectRowInTreeTable("' . $idTable . '");
			digirisk("#menu").val("risq");
			var expanded = reInitTreeTable();
			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
			{
				"post": "true",
				"table": "' . $table . '",
				"act": "edit",
				"id": nodeId,
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageListe",
				"partition": "bilan",
				"expanded": expanded
			});
		});

		digirisk("#' . $idTable . ' .docmanager-node").click(function(){
			var nodeId = digirisk(this).parent("tr").attr("id").replace("node-' . $idTable . '-", "").replace("-name", "");
			selectRowInTreeTable("' . $idTable . '");
			digirisk("#menu").val("risq");
			var expanded = reInitTreeTable();
			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
			{
				"post": "true",
				"table": "' . $table . '",
				"act": "edit",
				"id": nodeId,
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageListe",
				"partition": "docmanager",
				"expanded": expanded
			});
		});

		digirisk("#' . $idTable . ' .timepicker-node").click(function(){
			var nodeId = digirisk(this).parent("tr").attr("id").replace("node-' . $idTable . '-", "").replace("-name", "");
			selectRowInTreeTable("' . $idTable . '");
			digirisk("#menu").val("risq");
			var expanded = reInitTreeTable();
			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
			{
				"post": "true",
				"table": "' . $table . '",
				"act": "edit",
				"id": nodeId,
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageListe",
				"partition": "timepicker",
				"expanded": expanded
			});
		});
		//	The user click on the risk button of a node
		digirisk("#' . $idTable . ' .risq-node").click(function(){
			var nodeId = digirisk(this).parent("tr").attr("id").replace("node-' . $idTable . '-", "").replace("-name", "");
			selectRowInTreeTable("' . $idTable . '");
			digirisk("#menu").val("risq");
			var expanded = reInitTreeTable();
			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
			{
				"post": "true",
				"table": "' . $table . '",
				"act": "edit",
				"id": nodeId,
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageListe",
				"partition": "risq",
				"expanded": expanded
			});
		});
		//	The user click on the delete button of a node
		digirisk("#' . $idTable . ' .nomNoeudArbre, #' . $idTable . ' .treeTableGroupInfoColumn").click(function(e){
			if(!digirisk(e.target).hasClass("expander")){
				if(digirisk(e.target).hasClass("nomNoeudArbre")){
					var nodeId = digirisk(this).attr("id").replace("node-' . $idTable . '-", "").replace("-name", "");
				}
				else if(digirisk(e.target).hasClass("treeTableGroupInfoColumn")){
					var nodeId = digirisk(this).parent("tr").attr("id").replace("node-' . $idTable . '-", "").replace("-name", "");
				}
				selectRowInTreeTable("' . $idTable . '");
				digirisk("#menu").val("all");
				var expanded = reInitTreeTable();
				digirisk("#partieEdition").html(digirisk("#loadingImg").html());
				digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
				{
					"post": "true",
					"table": "' . $table . '",
					"act": "edit",
					"id": nodeId,
					"partie": "right",
					"menu": digirisk("#menu").val(),
					"affichage": "affichageListe",
					"partition": "tout",
					"expanded": expanded
				});
			}
		});';
			}
			break;
			case TABLE_QUESTION :
			if(false){	/*	Tree leaf	*/
				$script = '
				<script type="text/javascript">
					digirisk(document).ready(function()
					{
						digirisk("#' . $idTable . ' #delete-leaf-' . $subElement->id . '").click(function(){
							var adresse = "' . EVA_INC_PLUGIN_URL . 'ajax.php?nom=' . $sousTable . '&id=' . $subElement->id . '&idPere=' . $elementPere->id . '&act=delete";
							digirisk("#ajax-response").html(digirisk("#loadingImg").html());
							digirisk("#ajax-response").load(adresse);
						});
					});
				</script>';
			}
			if(false){	/*	Tree node	*/
				$script = '
					<script type="text/javascript">
						digirisk(document).ready(function()
						{
							digirisk("#' . $idTable . ' #delete-node-' . $element->id . '").click(function(){
								var adresse = "' . EVA_INC_PLUGIN_URL . 'ajax.php?nom=' . $table . '&id=' . $element->id . '&act=delete";
								digirisk("#ajax-response").html(digirisk("#loadingImg").html());
								digirisk("#ajax-response").load(adresse);
							});
							digirisk("#' . $idTable . ' #add-node-' . $element->id . '").click(function(){
								for (var i=0;i<document.regulatoryWatchForm.titrePere.options.length;i++)
								{
									if (document.regulatoryWatchForm.titrePere.options[i].value == ' . $element->id . ')
										document.regulatoryWatchForm.titrePere.options[i].selected = true;
								}
								digirisk("#traiter").click();
							});
							digirisk("#' . $idTable . ' #edit-node-' . $element->id . '").click(function(){
								for (var i=0;i<document.regulatoryWatchForm.titrePere.options.length;i++)
								{
									if (document.regulatoryWatchForm.titrePere.options[i].value == ' . $element->id . ')
										document.regulatoryWatchForm.titrePere.options[i].selected = true;
								}
								if(digirisk("#codeTitre").val() == "")
								{
									digirisk("#codeTitre").val(' . $element->code . ')
								}
								digirisk("#updateVeille").click();
							});
						});
					</script>';
			}
			break;
		}
		$script .= '

	});
</script>';

		return $tableArborescente . $script;
	}

	/**
	 * Returns the inner table of the list view tree with scripts that allow you to display the right part by clicking on the elements.
	 * This recursive function path tree from the father element to his leaves.
	 * @param array[Element_of_a_tree] $elementsFils Array of all the elements son of the father element.
	 * @param Element_of_a_tree $elementPere Father element.
	 * @param string $table Father element table name.
	 * @return string HTML code of the inner table.
	 */
	public static function getCorpsTableArborescence($elementsFils, $elementPere, $table, $titreInfo, $idTable)
	{
		$monCorpsTable = $monCorpsSubElements = '';
		$ddFeuilleClass = 'feuilleArbre';
		$nomFeuilleClass = 'nomFeuilleArbre';

		switch($table)
		{
			case TABLE_CATEGORIE_DANGER :
				$sousTable = TABLE_DANGER;
				$subElements = categorieDangers::getDangersDeLaCategorie($elementPere->id);
				$actionSize = 4;
				break;
			case TABLE_GROUPEMENT :
				$sousTable = TABLE_UNITE_TRAVAIL;
				$subElements = EvaGroupement::getUnitesDuGroupement($elementPere->id);
				$actionSize = 4;
				break;
			case TABLE_TACHE :
				$sousTable = TABLE_ACTIVITE;
				$tacheMere = new EvaTask($elementPere->id);
				$tacheMere->load();
				$subElements = $tacheMere->getWPDBActivitiesDependOn();
				$actionSize = 3;
				break;
			case TABLE_GROUPE_QUESTION :
				$sousTable = TABLE_QUESTION;
				$subElements = EvaGroupeQuestions::getQuestionsDuGroupeQuestions($elementPere->id);
				$actionSize = 3;
				break;
		}
		$monCorpsSubElements = '';
		foreach($subElements as $subElement)
		{
			$ddFeuilleClass = 'feuilleArbre';
			$nomFeuilleClass = 'nomFeuilleArbre';
			switch($table)
			{
				case TABLE_CATEGORIE_DANGER :
				{
					$tdSubEdit = '
							<td colspan="2">&nbsp;</td>
							<td class="noPadding edit-leaf" id="edit-leaf' . $subElement->id . '">';
					if(current_user_can('digi_edit_danger'))
					{
						$tdSubEdit .= '<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_EDIT . '" alt="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('le danger', 'evarisk')) . '" title="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('le danger', 'evarisk')) . '" />';
					}
					else
					{
						$tdSubEdit .= '&nbsp;';
					}
					$tdSubEdit .= '</td>';
					$tdSubDelete = '<td class="noPadding delete-leaf" id="delete-leaf' . $subElement->id . '">';
					if(current_user_can('digi_delete_danger'))
					{
						$tdSubDelete .= '<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_DELETE . '" alt="' . sprintf(__('Supprimer %s', 'evarisk'), __('le danger', 'evarisk')) . '" title="' . sprintf(__('Supprimer %s', 'evarisk'), __('le danger', 'evarisk')) . '" />';
					}
					else
					{
						$tdSubDelete .= '&nbsp;';
					}
					$tdSubDelete .= '</td>';
					$subAffichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_D .  $subElement->id . ' - </span>' . $subElement->nom;
					$subActions = $tdSubEdit . $tdSubDelete;
					if(!current_user_can('digi_move_danger'))
					{
						$ddFeuilleClass = '';
					}
					if(!current_user_can('digi_view_detail_danger'))
					{
						$nomFeuilleClass = 'userForbiddenActionCursor';
					}
				}
				break;
				case TABLE_GROUPEMENT :
				{
					$affichagePictoEvalRisque = (!AFFICHAGE_PICTO_EVAL_RISQUE) ? 'display:none;' : '';
					$tdSubEdit = '
							<td colspan="2">&nbsp;</td>';
					if(current_user_can('digi_edit_unite') || current_user_can('digi_edit_unite_' . $subElement->id)){
						$tdSubEdit .= '
							<td style="' . $affichagePictoEvalRisque . '" class="noPadding risk-leaf" id="risq-leaf' . $subElement->id . '">
								<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' .PICTO_LTL_EVAL_RISK . '" alt="' . sprintf(__('Risques %s', 'evarisk'), __('de l\'unit&eacute; de travail', 'evarisk')) . '" title="' . sprintf(__('Risques %s', 'evarisk'), __('de l\'unit&eacute; de travail', 'evarisk')) . '" />
							</td>
							<td class="noPadding summary-leaf" id="summary-leaf' . $subElement->id . '">
								<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' .PICTO_LTL_PRINT_RISK_SUMMARY . '" alt="' . __('Bilan', 'evarisk') . '" title="' . __('Bilan', 'evarisk') . '" />
							</td>
							<td class="noPadding edit-leaf" id="edit-leaf' . $subElement->id . '">
								<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_EDIT . '" alt="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('l\'unit&eacute; de travail', 'evarisk')) . '" title="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('l\'unit&eacute; de travail', 'evarisk')) . '" />
							</td>';
					}
					elseif(current_user_can('digi_view_detail_unite') || current_user_can('digi_view_detail_unite_' . $subElement->id)){
						$tdSubEdit .= '
							<td style="' . $affichagePictoEvalRisque . '" class="noPadding risk-leaf" id="risq-leaf' . $subElement->id . '">
								<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' .PICTO_LTL_EVAL_RISK . '" alt="' . sprintf(__('Risques %s', 'evarisk'), __('de l\'unit&eacute; de travail', 'evarisk')) . '" title="' . sprintf(__('Risques %s', 'evarisk'), __('de l\'unit&eacute; de travail', 'evarisk')) . '" />
							</td>
							<td class="noPadding summary-leaf" id="summary-leaf' . $subElement->id . '">
								<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' .PICTO_LTL_PRINT_RISK_SUMMARY . '" alt="' . __('Bilan', 'evarisk') . '" title="' . __('Bilan', 'evarisk') . '" />
							</td>
							<td class="noPadding edit-leaf" id="edit-leaf' . $subElement->id . '">
								<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_VIEW . '" alt="' . sprintf(__('Voir %s', 'evarisk'), __('l\'unit&eacute; de travail', 'evarisk')) . '" title="' . sprintf(__('Voir %s', 'evarisk'), __('l\'unit&eacute; de travail', 'evarisk')) . '" />
							</td>';
					}
					else{
						$tdSubEdit .= '<td class="noPadding" >&nbsp;</td><td class="noPadding" >&nbsp;</td>';
					}

					if(current_user_can('digi_delete_unite') || current_user_can('digi_delete_unite_' . $subElement->id)){
						$tdSubDelete = '<td class="noPadding delete-leaf" id="delete-leaf' . $subElement->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';"  src="' . PICTO_DELETE . '" alt="' . sprintf(__('Supprimer %s', 'evarisk'), __('l\'unit&eacute; de travail', 'evarisk')) . '" title="' . sprintf(__('Supprimer %s', 'evarisk'), __('l\'unit&eacute; de travail', 'evarisk')) . '" />';
					}
					else{
						$tdSubDelete = '<td class="noPadding" >&nbsp;';
					}
					$tdSubDelete .= '</td>';

					$subAffichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_UT .  $subElement->id . ' - </span>' . $subElement->nom;
					$subActions = $tdSubEdit . $tdSubDelete;
					if(!current_user_can('digi_move_unite')){
						$ddFeuilleClass = '';
					}
					if(!current_user_can('digi_view_detail_unite') && !current_user_can('digi_view_detail_unite_' . $subElement->id)
						&& !current_user_can('digi_edit_unite') && !current_user_can('digi_edit_unite_' . $subElement->id)){
						$nomFeuilleClass = 'userForbiddenActionCursor';
					}
				}
				break;
				case TABLE_TACHE :
					$tdSubEdit = '
							<td colspan="2">&nbsp;</td>';
					if ( current_user_can('digi_edit_action') || current_user_can('digi_edit_action_' . $subElement->id) ) {
						$tdSubEdit .= '
							<td class="noPadding docmanager-leaf" id="docmanager-leaf' . $subElement->id . '">
								<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'user_notifications/doc_add_s.png" alt="' . __('Gestion des documents', 'evarisk') . '" title="' . __('Gestion des documents', 'evarisk') . '" />
							</td>
							<td class="noPadding timepicker-leaf" id="timepicker-leaf' . $subElement->id . '">
								<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'suivi_projet/chrono_vert_vs.png" alt="' . __('Pointage temps', 'evarisk') . '" title="' . __('Pointage temps', 'evarisk') . '" />
							</td>
							<td class="noPadding summary-leaf" id="summary-leaf' . $subElement->id . '">
								<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_LTL_PRINT_RISK_SUMMARY . '" alt="' . __('Bilan', 'evarisk') . '" title="' . __('Bilan', 'evarisk') . '" />
							</td>';
						$tdSubEdit .= '
							<td class="noPadding edit-leaf" id="edit-leaf' . $subElement->id . '">
								<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_EDIT . '" alt="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('l\'action', 'evarisk')) . '" title="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('l\'action', 'evarisk')) . '" />
							</td>';
					}
					elseif(current_user_can('digi_view_detail_action') || current_user_can('digi_view_detail_action_' . $subElement->id)){
						$tdSubEdit .= '
							<td class="noPadding edit-leaf" id="edit-leaf' . $subElement->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_VIEW . '" alt="' . sprintf(__('Voir %s', 'evarisk'), __('l\'action', 'evarisk')) . '" title="' . sprintf(__('Voir %s', 'evarisk'), __('l\'action', 'evarisk')) . '" /></td>';
					}
					else{
						$tdSubEdit .= '<td class="noPadding" >&nbsp;</td>';
					}

					if(current_user_can('digi_delete_action') || current_user_can('digi_delete_action_' . $subElement->id)){
						$tdSubDelete = '<td class="noPadding delete-leaf" id="delete-leaf' . $subElement->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';"  src="' . PICTO_DELETE . '" alt="' . sprintf(__('Supprimer %s', 'evarisk'), __('l\'action', 'evarisk')) . '" title="' . sprintf(__('Supprimer %s', 'evarisk'), __('l\'action', 'evarisk')) . '" />';
					}
					else{
						$tdSubDelete = '<td class="noPadding" >&nbsp;';
					}
					$tdSubDelete .= '</td>';
					$subAffichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_ST .  $subElement->id . ' - </span>' . $subElement->nom;
					$subActions = $tdSubEdit . $tdSubDelete;
					if(!current_user_can('digi_move_action')){
						$ddFeuilleClass = '';
					}
					if(!current_user_can('digi_view_detail_action') && !current_user_can('digi_edit_action')){
						$nomFeuilleClass = 'userForbiddenActionCursor';
					}
				break;
				case TABLE_GROUPE_QUESTION :
					$tdSubDelete = '
							<td colspan="2">&nbsp;</td>
							<td class="noPadding delete-leaf" id="delete-leaf-' . $subElement->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_DELETE . '" alt="' . sprintf(__('&Eacute;ffacer %s', 'evarisk'), __('la question', 'evarisk')) . '" title="' . sprintf(__('&Eacute;ffacer %s', 'evarisk'), __('la question', 'evarisk')) . '" /></td>';
					$subAffichage = ELEMENT_IDENTIFIER_Q . $subElement->id . ' : ' . ucfirst($subElement->enonce);
					$subActions = $tdSubDelete;
				break;
			}
			$info = '';//EvaDisplayDesign::getInfoArborescence($sousTable, $subElement->id);
			$monCorpsSubElements .= '
				<tr id="leaf-' . $subElement->id . '" class="cursormove child-of-node-' . $idTable . '-' . $elementPere->id . ' ' . $ddFeuilleClass . '">
					<td id="leaf-' . $subElement->id . '-name" class="' . $nomFeuilleClass . '" >' . $subAffichage . '</td>';
				if($titreInfo != null) {
					$monCorpsSubElements = $monCorpsSubElements; // . '<td class="' . $info['class'] . '">' . $info['value'] . '</td>';
				}
				$monCorpsSubElements .= $subActions . '
				</tr>';
		}

		if (count($elementsFils) != 0) {
			foreach ($elementsFils as $element ) {
				$elements_fils = '';
				$elements_fils = Arborescence::getFils($table, $element, "nom ASC");
				$elements_pere = Arborescence::getPere($table, $element, " Status = 'Deleted' ");
				$elements_pere_valid = Arborescence::getPere($table, $element, " Status = 'Valid' ");
				$ddNoeudClass = 'noeudArbre';
				$nomNoeudClass = 'nomNoeudArbre';

				if (count($elements_pere) <= 0) {
					switch ($table) {
						case TABLE_CATEGORIE_DANGER :
							$sousTable = TABLE_DANGER;
							$subElements = categorieDangers::getDangersDeLaCategorie($element->id);
						break;
						case TABLE_GROUPEMENT :
							$sousTable = TABLE_UNITE_TRAVAIL;
							$subElements = EvaGroupement::getUnitesDuGroupement($element->id);
						break;
						case TABLE_TACHE :
							$sousTable = TABLE_ACTIVITE;
							$tache = new EvaTask($element->id);
							$tache->load();
							$subElements = $tache->getWPDBActivitiesDependOn();
						break;
						case TABLE_GROUPE_QUESTION :
							$sousTable = TABLE_QUESTION;
							$subElements = EvaGroupeQuestions::getQuestionsDuGroupeQuestions($element->id);
						break;
					}
					$trouveElement = count($elements_fils) + count($subElements);

					$element->nom = stripslashes($element->nom);
					switch($table)
					{
						case TABLE_CATEGORIE_DANGER :
						{
							$affichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_CD . $element->id . '</span> - ' . $element->nom;
							$tdAddMainStyle = 'display:none;';
							$tdAddSecondaryStyle = 'display:none;';
							if(count($elements_fils) > 0)
							{
								$tdAddMainStyle = '';
							}
							elseif(count($subElements) > 0)
							{
								$tdAddSecondaryStyle = '';
							}
							elseif((count($elements_fils) == 0) && (count($subElements) == 0))
							{
								$tdAddMainStyle = '';
								$tdAddSecondaryStyle = '';
							}

							if(current_user_can('digi_add_danger_category'))
							{
								$tdAddMain = '<td class="noPadding addMain" id="addMain' . $element->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $tdAddMainStyle . '" src="' .PICTO_LTL_ADD_CATEGORIE_DANGER . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('une cat&eacute;gorie de dangers', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('une cat&eacute;gorie de dangers', 'evarisk')) . '" /></td><td id="addMain' . $element->id . 'Alt" style="display:none;">';
							}
							else
							{
								$tdAddMain = '<td class="noPadding >&nbsp;';
							}
							$tdAddMain .= '</td>';

							if(current_user_can('digi_add_danger'))
							{
								$tdAddSecondary = '<td class="noPadding addSecondary" id="addSecondary' . $element->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $tdAddSecondaryStyle . '" src="' .PICTO_LTL_ADD_DANGER . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('un danger', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('un danger', 'evarisk')) . '" /></td><td id="addSecondary' . $element->id . 'Alt" style="display:none;">';
							}
							else
							{
								$tdAddSecondary = '<td class="noPadding >&nbsp;';
							}
							$tdAddSecondary .= '</td>';

							if(current_user_can('digi_edit_danger_category'))
							{
								$tdEdit = '<td class="noPadding edit-node" id="edit-node' . $element->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' .PICTO_EDIT . '" alt="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('la cat&eacute;gorie de dangers', 'evarisk')) . '" title="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('la cat&eacute;gorie de dangers', 'evarisk')) . '" />';
							}
							else
							{
								$tdEdit = '<td class="noPadding >&nbsp;';
							}
							$tdEdit .= '</td>';

							if(current_user_can('digi_delete_danger_category'))
							{
								$tdDelete = '<td class="noPadding delete-node" id="delete-node' . $element->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' .PICTO_DELETE . '" alt="' . sprintf(__('Supprimer %s', 'evarisk'), __('la cat&eacute;gorie de dangers', 'evarisk')) . '" title="' . sprintf(__('Supprimer %s', 'evarisk'), __('la cat&eacute;gorie de dangers', 'evarisk')) . '" />';
							}
							else
							{
								$tdDelete = '<td class="noPadding >&nbsp;';
							}
							$tdDelete .= '</td>';

							if(!current_user_can('digi_move_danger_category'))
							{
								$ddNoeudClass = '';
							}
							if(!current_user_can('digi_view_detail_danger_category'))
							{
								$nomNoeudClass = 'userForbiddenActionCursor';
							}

							$actions = '
								' . $tdAddMain . '
								' . $tdAddSecondary . '
								' . $tdEdit . '
								' . $tdDelete;
						}
						break;
						case TABLE_GROUPEMENT :
						{
							$affichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_GP . $element->id . '</span> - ' . $element->nom;
							$tdAddMainStyle = 'display:none;';
							$tdAddSecondaryStyle = 'display:none;';
							if(count($elements_fils) > 0)
							{
								$tdAddMainStyle = '';
							}
							elseif(count($subElements) > 0)
							{
								$tdAddSecondaryStyle = '';
							}
							elseif((count($elements_fils) == 0) && (count($subElements) == 0))
							{
								$tdAddMainStyle = '';
								$tdAddSecondaryStyle = '';
							}

							/*	Boutons d'ajouts d'un groupement ou d'une unit�	*/
							if(current_user_can('digi_add_groupement') || current_user_can('digi_add_groupement_groupement_' . $element->id))
							{
								$tdAddMain = '<td class="noPadding addMain" id="addMain' . $element->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $tdAddMainStyle . '" src="' .PICTO_LTL_ADD_GROUPEMENT . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('un groupement', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('un groupement', 'evarisk')) . '" />';
							}
							else
							{
								$tdAddMain = '<td class="noPadding" >&nbsp;';
							}
							$tdAddMain .= '</td>';
							if(current_user_can('digi_add_unite') || current_user_can('digi_add_unite_groupement_' . $element->id))
							{
								$tdAddSecondary = '<td class="noPadding addSecondary" id="addSecondary' . $element->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $tdAddSecondaryStyle . '" src="' .PICTO_LTL_ADD_UNIT . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('une unit&eacute; de travail', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('une unit&eacute; de travail', 'evarisk')) . '" />';
							}
							else
							{
								$tdAddSecondary = '<td class="noPadding" >&nbsp;';
							}
							$tdAddSecondary .= '</td>';

							if(current_user_can('digi_edit_groupement') || current_user_can('digi_edit_groupement_' . $element->id))
							{
								$affichagePictoEvalRisque = (!AFFICHAGE_PICTO_EVAL_RISQUE) ? 'display:none;' : '';
								$tdEdit = '
									<td style="' . $affichagePictoEvalRisque . '" class="noPadding risq-node" id="risq-node' . $element->id . '">
										<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' .PICTO_LTL_EVAL_RISK . '" alt="' . __('&Eacute;valuation des risques', 'evarisk') . '" title="' . __('&Eacute;valuation des risques', 'evarisk') . '" />
									</td>
									<td class="noPadding summary-node" id="summary-node' . $element->id . '">
										<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' .PICTO_LTL_PRINT_RISK_SUMMARY . '" alt="' . __('Bilan', 'evarisk') . '" title="' . __('Bilan', 'evarisk') . '" />
									</td>
									<td class="noPadding edit-node" id="edit-node' . $element->id . '">
										<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' .PICTO_EDIT . '" alt="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('le groupement', 'evarisk')) . '" title="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('le groupement', 'evarisk')) . '" />
									</td>';
							}
							elseif(current_user_can('digi_view_detail_groupement') || current_user_can('digi_view_detail_groupement_' . $element->id))
							{
								$affichagePictoEvalRisque = (!AFFICHAGE_PICTO_EVAL_RISQUE) ? 'display:none;' : '';
								$tdEdit = '
									<td style="' . $affichagePictoEvalRisque . '" class="noPadding risq-node" id="risq-node' . $element->id . '">
										<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' .PICTO_LTL_EVAL_RISK . '" alt="' . __('&Eacute;valuation des risques', 'evarisk') . '" title="' . __('&Eacute;valuation des risques', 'evarisk') . '" />
									</td>
									<td class="noPadding summary-node" id="summary-node' . $element->id . '">
										<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' .PICTO_LTL_PRINT_RISK_SUMMARY . '" alt="' . __('Bilan', 'evarisk') . '" title="' . __('Bilan', 'evarisk') . '" />
									</td>
									<td class="noPadding edit-node" id="edit-node' . $element->id . '">
										<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_VIEW . '" alt="' . sprintf(__('Voir %s', 'evarisk'), __('le groupement', 'evarisk')) . '" title="' . sprintf(__('Voir %s', 'evarisk'), __('le groupement', 'evarisk')) . '" />
									</td>';
							}
							else
							{
								$tdEdit = '<td class="noPadding" >&nbsp;</td><td class="noPadding" >&nbsp;</td><td class="noPadding" >&nbsp;</td>';
							}

							/*	Bouton de suppression d'un groupement */
							if(current_user_can('digi_delete_groupement') || current_user_can('digi_delete_groupement_' . $element->id))
							{
								$tdDelete = '<td class="noPadding delete-node" id="delete-node' . $element->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_DELETE . '" alt="' . sprintf(__('Supprimer %s', 'evarisk'), __('le groupement', 'evarisk')) . '" title="' . sprintf(__('Supprimer %s', 'evarisk'), __('le groupement', 'evarisk')) . '" />';
							}
							else
							{
								$tdDelete = '<td class="noPadding" >&nbsp;';
							}
							$tdDelete .= '</td>';

							if(!current_user_can('digi_move_groupement'))
							{
								$ddNoeudClass = '';
							}
							if(!current_user_can('digi_view_detail_groupement') && !current_user_can('digi_view_detail_groupement_' . $element->id)
									&& !current_user_can('digi_edit_groupement') && !current_user_can('digi_edit_groupement_' . $element->id))
							{
								$nomNoeudClass = 'userForbiddenActionCursor';
							}

							/*	Ajout des diff�rents boutons � l'interface	*/
							$actions = '
								' . $tdAddMain . '
								' . $tdAddSecondary . '
								' . $tdEdit . '
								' . $tdDelete;
						}
						break;
						case TABLE_TACHE :
						{
							$affichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_T . $element->id . '</span> - ' . $element->nom;
							$tdAddMainStyle = 'display:none;';
							$tdAddSecondaryStyle = 'display:none;';
							if(count($elements_fils) > 0)
							{
								$tdAddMainStyle = '';
							}
							elseif(count($subElements) > 0)
							{
								$tdAddSecondaryStyle = '';
							}
							elseif((count($elements_fils) == 0) && (count($subElements) == 0))
							{
								$tdAddMainStyle = '';
								$tdAddSecondaryStyle = '';
							}

							if(current_user_can('digi_add_task') || current_user_can('digi_add_task_task_' . $element->id)){
								$tdAddMain = '<td class="noPadding addMain" id="addMain' . $element->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $tdAddMainStyle . '" src="' .PICTO_LTL_ADD_TACHE . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('une t&acirc;che', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('une t&acirc;che', 'evarisk')) . '" />';//<td id="addMain' . $element->id . 'Alt" style="display:none;"></td>
							}
							else{
								$tdAddMain = '<td class="noPadding" >&nbsp;';
							}
							$tdAddMain .= '</td>';
							if(current_user_can('digi_add_action') || current_user_can('digi_add_action_task_' . $element->id)){
								$tdAddSecondary = '<td class="noPadding addSecondary" id="addSecondary' . $element->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';' . $tdAddSecondaryStyle . '" src="' .PICTO_LTL_ADD_ACTIVITE . '" alt="' . sprintf(__('Ajouter %s', 'evarisk'), __('une action', 'evarisk')) . '" title="' . sprintf(__('Ajouter %s', 'evarisk'), __('une action', 'evarisk')) . '" />';//<td id="addSecondary' . $element->id . 'Alt" style="display:none;"></td>
							}
							else{
								$tdAddSecondary = '<td class="noPadding" >&nbsp;';
							}
							$tdAddSecondary .= '</td>';

							if(current_user_can('digi_edit_task') || current_user_can('digi_edit_task_' . $element->id)){
								$tdEdit = '';


								$tdEdit .= '
							<td class="noPadding docmanager-node" id="docmanager-node' . $element->id . '">
								<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'user_notifications/doc_add_s.png" alt="' . __('Gestion des documents', 'evarisk') . '" title="' . __('Gestion des documents', 'evarisk') . '" />
							</td>
							<td class="noPadding timepicker-node" id="timepicker-node' . $element->id . '">
								<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'suivi_projet/chrono_vert_vs.png" alt="' . __('Temps consomm&eacute;', 'evarisk') . '" title="' . __('Temps consomm&eacute;', 'evarisk') . '" />
							</td>
							<td class="noPadding summary-node" id="summary-node' . $element->id . '">
								<img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_LTL_PRINT_RISK_SUMMARY . '" alt="' . __('Bilan', 'evarisk') . '" title="' . __('Bilan', 'evarisk') . '" />
							</td>

										<td class="noPadding edit-node" id="edit-node' . $element->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';"src="' .PICTO_EDIT . '" alt="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('le groupement', 'evarisk')) . '" title="' . sprintf(__('&Eacute;diter %s', 'evarisk'), __('la t&acirc;che', 'evarisk')) . '" />';

							}
							elseif(current_user_can('digi_view_detail_task') || current_user_can('digi_view_detail_task_' . $element->id)){
								$tdEdit = '<td class="noPadding edit-node" id="edit-node' . $element->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_VIEW . '" alt="' . sprintf(__('Voir %s', 'evarisk'), __('le groupement', 'evarisk')) . '" title="' . sprintf(__('Voir %s', 'evarisk'), __('le groupement', 'evarisk')) . '" />';
							}
							else{
								$tdEdit = '<td class="noPadding" >&nbsp;';
							}
							$tdEdit .= '</td>';
							if(current_user_can('digi_delete_task') || current_user_can('digi_delete_task_' . $element->id)){
								$tdDelete = '<td class="noPadding delete-node" id="delete-node' . $element->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_DELETE . '" alt="Effacer le titre" title="' . sprintf(__('&Eacute;ffacer %s', 'evarisk'), __('la t&acirc;che', 'evarisk')) . '" />';
							}
							else{
								$tdDelete = '<td class="noPadding" >&nbsp;';
							}
							$tdDelete .= '</td>';

							if(!current_user_can('digi_move_task')){
								$ddNoeudClass = '';
							}
							if(!current_user_can('digi_view_detail_task') && !current_user_can('digi_view_detail_task_' . $element->id) && !current_user_can('digi_edit_task') && !current_user_can('digi_edit_task_' . $element->id)){
								$nomNoeudClass = 'userForbiddenActionCursor';
							}

							$actions = '
								' . $tdAddMain . '
								' . $tdAddSecondary . '
								' . $tdEdit . '
								' . $tdDelete;
						}
						break;
						case TABLE_GROUPE_QUESTION :
						{
							$affichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_GQ . $element->id . '</span> - ' . $element->code . '-' . ucfirst($element->nom);
							$tdAdd = '<td class="noPadding addMain" id="add-node-' . $element->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="'.PICTO_INSERT.'" alt="' . __('Inserer sous le titre', 'evarisk') . '" title="Inserer sous le titre" /></td>';
							$tdEdit = '<td class="noPadding edit-node" id="edit-node-' . $element->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_EDIT . '" alt="Modifier le titre" title="' . __('Modifier le titre', 'evarisk') . '" /></td>';
							$tdDelete = '<td class="noPadding delete-node" id="delete-node-' . $element->id . '"><img style="width:' . TAILLE_PICTOS_ARBRE . ';" src="' . PICTO_DELETE . '" alt="Effacer le titre" title="' . sprintf(__('&Eacute;ffacer %s', 'evarisk'), __('le titre', 'evarisk')) . '" />';
							$actions = $tdAdd . $tdEdit . $tdDelete;
						}
						break;
					}
					$info = '';
					if($titreInfo != null)
					{
						$info = '';//EvaDisplayDesign::getInfoArborescence($table, $element->id);
						//$info = '<td id="info-' . $element->id . '" class="' . $info['class'] . '">' . $info['value'] . '</td>';
					}
					$monCorpsTable .= '
						<tr id="node-' . $idTable . '-' . $element->id . '" class="child-of-node-' . $idTable . '-' . $elementPere->id . ' ' . $ddNoeudClass . ' parent">
							<td id="node-' . $idTable . '-' . $element->id . '-name" class="' . $nomNoeudClass . '" >' . $affichage . '</td>
							' . $info . $actions . '
						</tr>';

					if($trouveElement)
					{
						$monCorpsTable .= EvaDisplayDesign::getCorpsTableArborescence($elements_fils, $element, $table, $titreInfo, $idTable);
					}
				}
			}
		}

		return $monCorpsTable . $monCorpsSubElements;
	}

	/**
	* Returns the inner table of the list view tree with scripts that allow you to display the right part by clicking on the elements.
	* This recursive function path tree from the father element to his leaves.
	* @param array[Element_of_a_tree] $elementsFils Array of all the elements son of the father element.
	* @param Element_of_a_tree $elementPere Father element.
	* @param string $table Father element table name.
	* @return string HTML code of the inner table.
	*/
	function build_tree($elementsFils, $elementPere, $table, $titreInfo, $idTable, $moreInfo){
		$monCorpsTable = $monCorpsSubElements = '';
		$ddFeuilleClass = 'feuilleArbre';
		$nomFeuilleClass = 'nomFeuilleArbre';

		$user_is_allowed_to_view_details = false;
		switch($table)
		{
			case TABLE_CATEGORIE_DANGER :
				$sousTable = TABLE_DANGER;
				$subElements = categorieDangers::getDangersDeLaCategorie($elementPere->id);
				$actionSize = 4;
				break;
			case TABLE_GROUPEMENT :
				$sousTable = TABLE_UNITE_TRAVAIL;
				$subElements = EvaGroupement::getUnitesDuGroupement($elementPere->id);
				$actionSize = 4;
				break;
			case TABLE_TACHE :
				$sousTable = TABLE_ACTIVITE;
				$tacheMere = new EvaTask($elementPere->id);
				$tacheMere->load();
				$subElements = $tacheMere->getWPDBActivitiesDependOn();
				$actionSize = 3;
				$user_is_allowed_to_view_details = current_user_can('digi_view_detail_action');
				break;
			case TABLE_GROUPE_QUESTION :
				$sousTable = TABLE_QUESTION;
				$subElements = EvaGroupeQuestions::getQuestionsDuGroupeQuestions($elementPere->id);
				$actionSize = 3;
				break;
		}
		$monCorpsSubElements = '';
		foreach($subElements as $subElement){
			$ddFeuilleClass = '';
			$nomFeuilleClass = 'nomFeuilleArbre';
			$info = '';//EvaDisplayDesign::getInfoArborescence($sousTable, $subElement->id, $moreInfo);
			$monCorpsSubElements .= '
				<tr id="leaf-' . $subElement->id . '" class="cursormove child-of-node-' . $idTable . '-' . $elementPere->id . ' ' . $ddFeuilleClass . '">
					<td id="leaf-' . $subElement->id . '-name" class="' . $nomFeuilleClass . '" >' . ELEMENT_IDENTIFIER_ST . $subElement->id . '&nbsp;-&nbsp;' . $subElement->nom . '</td>';
			if($titreInfo != null && !empty($info)){
				$monCorpsSubElements = $monCorpsSubElements . '<td class="' . $info['class'] . '" >' . $info['value'] . '</td>';
			}
			$monCorpsSubElements .= '
					<td id="tdActionRacine' . $idTable . ELEMENT_IDENTIFIER_ST . $subElement->id . '" >';
			if($user_is_allowed_to_view_details){
				$monCorpsSubElements .= '
						<img src="' . str_replace('.png', '_vs.png', PICTO_VIEW) . '" alt="view_details" id="' . $sousTable . '_t_elt_' . $subElement->id . '" class="view_correctiv_action_sub_task" />';
			}
			$monCorpsSubElements .=
					'</td>
				</tr>';
		}

		if(count($elementsFils) != 0){
			foreach($elementsFils as $element){
				$elements_fils = '';
				$elements_fils = Arborescence::getFils($table, $element, "nom ASC");
				$elements_pere = Arborescence::getPere($table, $element, " Status = 'Deleted' ");
				$ddNoeudClass = 'noeudArbre';
				$nomNoeudClass = 'nomNoeudArbre';

				if(count($elements_pere) <= 0){
					switch($table){
						case TABLE_CATEGORIE_DANGER :
							$sousTable = TABLE_DANGER;
							$subElements = categorieDangers::getDangersDeLaCategorie($element->id);
						break;
						case TABLE_GROUPEMENT :
							$sousTable = TABLE_UNITE_TRAVAIL;
							$subElements = EvaGroupement::getUnitesDuGroupement($element->id);
						break;
						case TABLE_TACHE :
							$sousTable = TABLE_ACTIVITE;
							$tache = new EvaTask($element->id);
							$tache->load();
							$subElements = $tache->getWPDBActivitiesDependOn();
						break;
						case TABLE_GROUPE_QUESTION :
							$sousTable = TABLE_QUESTION;
							$subElements = EvaGroupeQuestions::getQuestionsDuGroupeQuestions($element->id);
						break;
					}
					$trouveElement = count($elements_fils) + count($subElements);

					$user_is_allowed_to_view_details = false;
					switch($table){
						case TABLE_CATEGORIE_DANGER :
							$affichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_CD . $element->id . '</span> - ' . $element->nom;
						break;
						case TABLE_GROUPEMENT :
							$affichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_GP . $element->id . '</span> - ' . $element->nom;
						break;
						case TABLE_TACHE :
							$affichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_T . $element->id . '</span> - ' . $element->nom;
							$user_is_allowed_to_view_details = current_user_can('digi_view_detail_task');
						break;
						case TABLE_GROUPE_QUESTION :
							$affichage = '<span class="italic" >' . ELEMENT_IDENTIFIER_GQ . $element->id . '</span> - ' . $element->code . '-' . ucfirst($element->nom);
						break;
					}

					$info = '';
					if($titreInfo != null){
						$info = '';//EvaDisplayDesign::getInfoArborescence($table, $element->id, $moreInfo);
						$info = '<td id="info-' . $element->id . '" class="' . $info['class'] . '">' . $info['value'] . '</td>';
					}

					$monCorpsTable .= '
						<tr id="node-' . $idTable . '-' . $element->id . '" class="child-of-node-' . $idTable . '-' . $elementPere->id . ' ' . $ddNoeudClass . ' parent">
							<td id="node-' . $idTable . '-' . $element->id . '-name" class="' . $nomNoeudClass . '" >' . $affichage . '</td>
							' . $info . '
							<td id="tdActionRacine' . $idTable . ELEMENT_IDENTIFIER_T . $element->id . '" class="CorrectivActionFollowStateActionColumn" >';
					if($user_is_allowed_to_view_details){
						$monCorpsTable .=
							'<img src="' . str_replace('.png', '_vs.png', PICTO_VIEW) . '" alt="view_details" id="' . $table . '_t_elt_' . $element->id . '" />';
					}
					$monCorpsTable .=
							'</td>
						</tr>';

					if($trouveElement){
						$monCorpsTable .= EvaDisplayDesign::build_tree($elements_fils, $element, $table, $titreInfo, $idTable, $moreInfo);
					}
				}
			}
		}

		return $monCorpsTable . $monCorpsSubElements;
	}


	/**
	* Returns information on an element to be displayed in the list view.
	* @param string $table Element table name.
	* @param int $elementId Identifier of the element.
	* @return string The information.
	*/
	static function getInfoArborescence($table, $elementId, $more_info = false)
	{
		switch($table)
		{
			case TABLE_DANGER :
				$info['value'] = '';
				$info['class'] = 'treeTableInfoColumn';
				if(!current_user_can('digi_view_detail_danger'))
				{
					$info['class'] = 'userForbiddenActionCursor';
				}
				break;
			case TABLE_CATEGORIE_DANGER :
				$info['value'] = '';
				$info['class'] = 'treeTableGroupInfoColumn';
				if(!current_user_can('digi_view_detail_danger_category'))
				{
					$info['class'] = 'userForbiddenActionCursor';
				}
				break;
			case TABLE_TACHE :
				$tache = new EvaTask($elementId);
				$tache->load();
				$moreInfo = '';
				if($more_info){
				 $moreInfo = '&nbsp;-&nbsp;&nbsp;' . __('D&eacute;but', 'evarisk') . '&nbsp;' . mysql2date('d M Y', $tache->getStartDate(), true) . '&nbsp;-&nbsp;' . __('Fin', 'evarisk') . '&nbsp;' . mysql2date('d M Y', $tache->getFinishDate(), true);
				}
				$info['value'] = $tache->getProgression() . '%&nbsp;(' . actionsCorrectives::check_progression_status_for_output($tache->getProgressionStatus()) . ')' . $moreInfo;
				$info['class'] = 'treeTableGroupInfoColumn taskInfoContainer-' . $elementId;
				if(!current_user_can('digi_view_detail_task') && !current_user_can('digi_view_detail_task_' . $elementId)
						&& !current_user_can('digi_edit_task') && !current_user_can('digi_edit_task_' . $elementId))
				{
					$info['class'] = 'userForbiddenActionCursor taskInfoContainer-' . $elementId;
				}
				break;
			case TABLE_ACTIVITE :
				$action = new EvaActivity($elementId);
				$action->load();
				$moreInfo = '';
				if($more_info){
				 $moreInfo = '&nbsp;-&nbsp;&nbsp;' . __('D&eacute;but', 'evarisk') . '&nbsp;' . mysql2date('d M Y', $action->getStartDate(), true) . '&nbsp;-&nbsp;' . __('Fin', 'evarisk') . '&nbsp;' . mysql2date('d M Y', $action->getFinishDate(), true);
				}
				$info['value'] = $action->getProgression() . '%&nbsp;(' . actionsCorrectives::check_progression_status_for_output($action->getProgressionStatus()) . ')' . $moreInfo;
				$info['class'] = 'treeTableInfoColumn activityInfoContainer-' . $elementId;
				if(!current_user_can('digi_view_detail_action') && !current_user_can('digi_view_detail_action_' . $elementId)
						&& !current_user_can('digi_edit_action') && !current_user_can('digi_edit_action_' . $elementId))
				{
					$info['class'] = 'userForbiddenActionCursor activityInfoContainer-' . $elementId;
				}
				break;
			case TABLE_GROUPEMENT :
				$scoreRisqueGroupement = 0;
				$riskAndSubRisks = eva_documentUnique::listRisk(TABLE_GROUPEMENT, $elementId);
				foreach($riskAndSubRisks as $risk)
				{
					$scoreRisqueGroupement += $risk[2]['value'];
				}
				$info['value'] = '<span id="LeftRiskSum' . $table . $elementId . '" >' . $scoreRisqueGroupement . '</span>&nbsp;-&nbsp;<span id="LeftRiskNb' . $table . $elementId . '" >' . count($riskAndSubRisks) . '</span> ' . __('risque(s)', 'evarisk');
				$info['class'] = 'treeTableGroupInfoColumn';
				if(!current_user_can('digi_view_detail_groupement') && !current_user_can('digi_view_detail_groupement_' . $elementId)
									&& !current_user_can('digi_edit_groupement') && !current_user_can('digi_edit_groupement_' . $elementId))
				{
					$info['class'] = 'userForbiddenActionCursor';
				}
				break;
			case TABLE_UNITE_TRAVAIL :
				$scoreRisqueGroupement = 0;
				$riskAndSubRisks = eva_documentUnique::listRisk(TABLE_UNITE_TRAVAIL, $elementId);
				foreach($riskAndSubRisks as $risk)
				{
					$scoreRisqueGroupement += $risk[2]['value'];
				}
				$info['value'] = '<span id="LeftRiskSum' . $table . $elementId . '" >' . $scoreRisqueGroupement . '</span>&nbsp;-&nbsp;<span id="LeftRiskNb' . $table . $elementId . '" >' . count($riskAndSubRisks) . '</span> ' . __('risque(s)', 'evarisk');
				$info['class'] = 'treeTableInfoColumn';
				if(!current_user_can('digi_view_detail_unite') && !current_user_can('digi_view_detail_unite_' . $elementId)
									&& !current_user_can('digi_edit_unite') && !current_user_can('digi_edit_unite_' . $elementId))
				{
					$info['class'] = 'userForbiddenActionCursor';
				}
				break;
		}
		return $info;
	}

	/*
	*
	*/
	static function getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script = '', $display_footer = true) {
		$barreTitre = '';
		$corpsTable = '';
		for($i=0; $i<count($titres); $i++){
			$barreTitre .= '<th class="' . (!empty($classes[$i]) ? $classes[$i] : '') . '" scope="col">' . $titres[$i] . '</th>';
		}
		if($barreTitre != '')
			$barreTitre = '<tr valign="top">' . $barreTitre . '</tr>';

		if(!empty($lignesDeValeurs)){
			for($numeroLigne=0; $numeroLigne<count($lignesDeValeurs); $numeroLigne++){
				$ligneDeValeurs = $lignesDeValeurs[$numeroLigne];
				$corpsTable .= '<tr id="' . $idLignes[$numeroLigne] . '" valign="top" class="tr_' . $idTable . '" >';
				for($i=0; $i<count($ligneDeValeurs); $i++){
					$corpsTable .= '
						<td class="' . (!empty($classes[$i]) ? $classes[$i] : '') . ' ' . (!empty($ligneDeValeurs[$i]['class']) ? $ligneDeValeurs[$i]['class'] : '') . '">' . $ligneDeValeurs[$i]['value'] . '</td>';
				}
				$corpsTable .= '</tr>';
			}
		}
		$table = $script . '<table id="' . $idTable . '" cellspacing="0" class="widefat post fixed" >
				<thead>
						' . $barreTitre . '
				</thead>';
		if($display_footer){
			$table .=
				'<tfoot>
						' . $barreTitre . '
				</tfoot>';
		}
			$table .=
				'<tbody >'
				 . $corpsTable .
				'
				</tbody>
			</table>';
		return $table;
	}

	/**
	 * Returns the  HTML list of the three elements to be dipslayed
	 * @see insererSociete
	 * @param string $table Elements to return table name.
	 * @param string $page Page of the paging.
	 * @param string $id Identifier of the element in wicth are the elements of the list.
	 * @param string $idPostBox Identifier of the Postbox in wicth the list is displayed.
	 * @return string HTML code of the list.
	 */
	static function creerListe($table, $page, $id, $idPostBox, $tableFils = null, $idFils = null)
	{
		switch($table)
		{
			case TABLE_GROUPEMENT:
				$element = EvaGroupement::getGroupement($id);
				$listeElems = Arborescence::getFils($table, $element, "nom ASC");
				unset($listeElements);
				$indiceListeElements = 0;
				$indiceFils = -1;
				foreach($listeElems as $elem)
				{
					if($tableFils == TABLE_GROUPEMENT && $elem->id == $idFils)
						$indiceFils = $indiceListeElements;
					$listeElements[] = array('id'=>$elem->id, 'table'=>TABLE_GROUPEMENT);
					$indiceListeElements ++;
				}
				$listeUnites = EvaGroupement::getUnitesDuGroupement($id);
				foreach($listeUnites as $elem)
				{
					if($tableFils == TABLE_UNITE_TRAVAIL && $elem->id == $idFils)
						$indiceFils = $indiceListeElements;
					$listeElements[] = array('id'=>$elem->id, 'table'=>TABLE_UNITE_TRAVAIL);
					$indiceListeElements ++;
				}
				$messageAbsence = __('Aucun groupement ni aucune unit&eacute; de travail n\'existe dans', 'evarisk');
				$mainAdd = __('Ajouter un nouveau groupement', 'evarisk');
				$mainSrc = PICTO_ADD_GROUPEMENT;
				$secondaryAdd = __('Ajouter une nouvelle unit&eacute; de travail', 'evarisk');
				$secondarySrc = PICTO_ADD_UNIT;
				$nombreElements = NOMBRE_ELEMENTS_AFFICHAGE_GRILLE_EVAL_RISQUES;
				break;
			case TABLE_CATEGORIE_DANGER:
				$element = categorieDangers::getCategorieDanger($id);
				$listeElems = Arborescence::getFils($table, $element, "nom ASC");
				unset($listeElements);
				$indiceListeElements = 0;
				$indiceFils = -1;
				unset($listeElements);
				foreach($listeElems as $elem)
				{
					if($tableFils == TABLE_CATEGORIE_DANGER && $elem->id == $idFils)
						$indiceFils = $indiceListeElements;
					$listeElements[] = array('id'=>$elem->id, 'table'=>TABLE_CATEGORIE_DANGER);
					$indiceListeElements ++;
				}
				$listeUnites = categorieDangers::getDangersDeLaCategorie($id);
				foreach($listeUnites as $elem)
				{
					if($tableFils == TABLE_DANGER && $elem->id == $idFils)
						$indiceFils = $indiceListeElements;
					$listeElements[] = array('id'=>$elem->id, 'table'=>TABLE_DANGER);
					$indiceListeElements ++;
				}
				$messageAbsence = __('Aucune categorie ni aucun danger n\'existe dans ', 'evarisk');
				$mainAdd = __('Ajouter une nouvelle cat&eacute;gorie de dangers.', 'evarisk');
				$mainSrc = PICTO_ADD_CATEGORIE_DANGER;
				$secondaryAdd = __('Ajouter un nouveau danger.', 'evarisk');
				$secondarySrc = PICTO_ADD_DANGER;
				$nombreElements = NOMBRE_ELEMENTS_AFFICHAGE_GRILLE_DANGERS;
				break;
		}
		if($indiceFils != -1)
			$page = ceil(($indiceFils + 1) / NOMBRE_ELEMENTS_AFFICHAGE_GRILLE_EVAL_RISQUES);
		$indice = ($page - 1) * $nombreElements;
		$pageMax = isset($listeElements)?ceil(count($listeElements)/$nombreElements):1;
		$liste  = '
<script type="text/javascript">
	digirisk(document).ready(function() {
		digirisk(\'#page' . $idPostBox . 'Reference\').val(' . $page . ');
		digirisk(\'#page' . $idPostBox . '\').val(' . $page . ');
		if(document.getElementById(\'filAriane\').lastChild == document.getElementById(\'filAriane\').firstChild)
		{
			digirisk(\'#' . $idPostBox . 'Pere\').addClass(\'hidden\');
		}
		else
		{
			digirisk(\'#' . $idPostBox . 'Pere\').removeClass(\'hidden\');
		}
		digirisk(\'#pageMax' . $idPostBox . '\').val(' . $pageMax . ');
		if(digirisk(\'#pageMax' . $idPostBox . '\').val() == 0)
		{
			digirisk(\'#pageMax' . $idPostBox . '\').val(1);
		}
		if(' . $page . ' <= 1)
		{
			digirisk(\'#first' . $idPostBox . '\').prop("disabled", "disabled");
			digirisk(\'#previous' . $idPostBox . '\').prop("disabled", "disabled");
		}
		else
		{
			digirisk(\'#first' . $idPostBox . '\').prop("disabled", "");
			digirisk(\'#previous' . $idPostBox . '\').prop("disabled", "");
		}
		if(parseInt(digirisk(\'#pageMax' . $idPostBox . '\').val()) == ' . $page . ')
		{
			digirisk(\'#last' . $idPostBox . '\').prop("disabled", "disabled");
			digirisk(\'#next' . $idPostBox . '\').prop("disabled", "disabled");
		}
		else
		{
			digirisk(\'#last' . $idPostBox . '\').prop("disabled", "");
			digirisk(\'#next' . $idPostBox . '\').prop("disabled", "");
		}
	});
</script>';
		if(isset($listeElements[$indice]))
		{
			$liste = $liste . EvaDisplayDesign::getTableMainDisplay(array_slice($listeElements, $indice, $nombreElements), $idPostBox, ceil(count($listeElements)/3));
		}
		else
		{
			$liste  = $liste . '
	<script type="text/javascript">
		digirisk(document).ready(function() {
			digirisk("#favorite-actions-mainPostBox").hide();
			digirisk("#favorite-second-link-' . $idPostBox . '").show();
			digirisk("#favorite-first-link-' . $idPostBox . '").show();
			digirisk(\'#infoLi\').html("' . $messageAbsence . '\"" + digirisk(\'#filAriane\').children(\'a:last\').html() + "\".<br />" +
		"<a href=\"#\" id=\"addMain\"><img src=\"' . $mainSrc . '\" alt=\"mainAdd\" title=\"' . $mainAdd . '\" />' . $mainAdd . '</a><br />" +
		"<a href=\"#\" id=\"addSecondary\"><img src=\"' . $secondarySrc . '\" alt=\"secondaryAdd\" title=\"' . $secondaryAdd . '\" />' . $secondaryAdd . '</a>");
			digirisk("#addMain").click(function(){
				digirisk(\'#favorite-first-link-' . $idPostBox . '\').click();
				return false;
			});
			digirisk("#addSecondary").click(function(){
				digirisk(\'#favorite-second-link-' . $idPostBox . '\').click();
				return false;
			});
		});
	</script>';
			$liste = $liste . '<p id="infoLi" class="paragraphe" style="width:98%;"></p>';
		}
		return  $liste;
	}

	/**
	 * Returns the table of elements with their main photo some of their informations and the scripts for use.
	 * @see getTableInfos
	 * @param array $elements Array of elements (key : 'table' => element table name, key : 'id' => element id name).
	 * @param string $idPostBox Postbox in wicth the table is displayed.
	 * @return string HTML code of the table.
	 */
	static function getTableMainDisplay($elements, $idPostBox, $pageMaxPostBox)
	{
		$nombreElements = 0;
		$script = '';
		$chargement = '
			digirisk("#rightEnlarging").show();
			digirisk("#equilize").click();
			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
				"table": "' . $_POST['table'] . '",
				"id": "' . $_POST['idPere'] . '",
				"page": digirisk(\'#page' . $idPostBox . 'Reference\').val(),
				"idPere": digirisk(\'#identifiantActuelle' . $idPostBox . '\').val(),
				"act": "edit",
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageTable",
				"partition": "tout"
			});
			digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
				"table": "' . $_POST['table'] . '",
				"id": "' . $_POST['idPere'] . '",
				"page": digirisk(\'#page' . $idPostBox . 'Reference\').val(),
				"idPere": digirisk(\'#identifiantActuelle' . $idPostBox . '\').val(),
				"act": "edit",
				"partie": "left",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageTable",
				"partition": "tout"
			});
			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieGauche").html(digirisk("#loadingImg").html());
			return false;';
		foreach($elements as $elementObject)
		{
			$table = $elementObject['table'];
			$idElement = $elementObject['id'];
			$chargement = '
				digirisk("#rightEnlarging").show();
				digirisk("#equilize").click();
				digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
					"table": "' . $table . '",
					"id": "' . $idElement . '",
					"page": digirisk(\'#page' . $idPostBox . 'Reference\').val(),
					"idPere": digirisk(\'#identifiantActuelle' . $idPostBox . '\').val(),
					"act": "edit",
					"partie": "right",
					"menu": digirisk("#menu").val(),
					"affichage": "affichageTable",
					"partition": "tout"
				});
				digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
					"table": "' . $_POST['table'] . '",
					"id": "' . $_POST['idPere'] . '",
					"page": digirisk(\'#page' . $idPostBox . 'Reference\').val(),
					"idPere": digirisk(\'#identifiantActuelle' . $idPostBox . '\').val(),
					"act": "edit",
					"partie": "left",
					"menu": digirisk("#menu").val(),
					"affichage": "affichageTable",
					"partition": "tout"
				});
				digirisk("#partieEdition").html(digirisk("#loadingImg").html());
				digirisk("#partieGauche").html(digirisk("#loadingImg").html());
				return false;';
			$ligneEditer[] = array('value' => '<a id="edit' . $table . $idElement . '" class="button boutonInfos"><img alt="" title="' . __('&Eacute;diter', 'evarisk') . '" src="' . PICTO_EDIT . '"/>' . __('&Eacute;diter', 'evarisk') . '</a>', 'class' => 'boutonsInfoElement');
			switch($table)
			{
				case TABLE_GROUPEMENT:
					$mainTable = TABLE_GROUPEMENT;
					$sousTable = TABLE_UNITE_TRAVAIL;
					$element = EvaGroupement::getGroupement($idElement);
					$defaultPicto = DEFAULT_GROUP_PICTO;
					$infosElement = EvaGroupement::getInfosGroupement($idElement);
					$ligneSelect[] = array('value' => '<a id="select' . $table . $idElement . '" class="button boutonInfos">' . __('Parcourir', 'evarisk') . '</a>', 'class' => 'boutonsInfoElement');
					$ligneEditer[count($ligneEditer)-1]['value'] = $ligneEditer[count($ligneEditer)-1]['value'] . '<a id="risq' . $table . $idElement . '" class="button boutonInfos"><img alt="" title="' . __('Risques', 'evarisk') . '" src="' . PICTO_LTL_EVAL_RISK . '"/>' . __('Risques', 'evarisk') . '</a>';
					$nbFilsElement = count(Arborescence::getFils($table, $element));
					$nbUnitesElement = count(EvaGroupement::getUnitesDuGroupement($idElement));

					if($nbFilsElement >= 0 and $nbUnitesElement == 0)
					{
						$ligneAddMain[] = array('class' => 'boutonsInfoElement', 'value' => '<a id="addMain' . $table . $idElement . '" class="button boutonInfos"><img alt="" title="' . __('Aj. groupement', 'evarisk') . '" src="' . PICTO_LTL_ADD_GROUPEMENT . '"/>' . __('Aj. gpmt', 'evarisk') . '</a>');
					}
					else
					{
						$ligneAddMain[] = array('class' => 'boutonsInfoElement', 'value' => '');
					}
					if($nbUnitesElement >= 0 and $nbFilsElement == 0)
					{
						$ligneAddSecondary[] = array('class' => 'boutonsInfoElement', 'value' => '<a id="addSecondary' . $table . $idElement . '" class="button boutonInfos"><img alt="" title="' . __('Aj. unit&eacute;', 'evarisk') . '" src="' . PICTO_LTL_ADD_UNIT . '"/>' . __('Aj. unit&eacute;', 'evarisk') . '</a>');
					}
					else
					{
						$ligneAddSecondary[] = array('class' => 'boutonsInfoElement', 'value' => '');
					}
					$aFilsNoeud = 'true';
					$aFilsFeuille = 'false';
					$nombreElementsAffichage = NOMBRE_ELEMENTS_AFFICHAGE_GRILLE_EVAL_RISQUES;
					break;
				case TABLE_UNITE_TRAVAIL:
					$mainTable = TABLE_GROUPEMENT;
					$sousTable = TABLE_UNITE_TRAVAIL;
					$element = eva_UniteDeTravail::getWorkingUnit($idElement);
					$defaultPicto = DEFAULT_WORKING_UNIT_PICTO;
					$infosElement = eva_UniteDeTravail::getWorkingUnitInfos($idElement);
					$ligneSelect[] = array('value' => '', 'class' => 'boutonsInfoElement');
					$ligneEditer[count($ligneEditer)-1]['value'] = $ligneEditer[count($ligneEditer)-1]['value'] . '<a id="risq' . $table . $idElement . '" class="button boutonInfos"><img alt="" title="' . __('Risques', 'evarisk') . '" src="' . PICTO_LTL_EVAL_RISK . '"/>' . __('Risques', 'evarisk') . '</a>';
					$aFilsNoeud = 'false';
					$aFilsFeuille = 'true';
					$nombreElementsAffichage = NOMBRE_ELEMENTS_AFFICHAGE_GRILLE_EVAL_RISQUES;
					break;
				case TABLE_CATEGORIE_DANGER:
					$mainTable = TABLE_CATEGORIE_DANGER;
					$sousTable = TABLE_DANGER;
					$element = categorieDangers::getCategorieDanger($idElement);
					$defaultPicto = DEFAULT_DANGER_CATEGORIE_PICTO;
					$ligneSelect[] = array('value' => '<a id="select' . $table . $idElement . '" class="button boutonInfos">' . __('S&eacute;lectionner', 'evarisk') . '</a>', 'class' => 'boutonsInfoElement');
					$infosElement = null;
					$nbFilsElement = count(Arborescence::getFils($table, $element));
					$nbUnitesElement = count(categorieDangers::getDangersDeLaCategorie($idElement));

					if($nbFilsElement >= 0 and $nbUnitesElement == 0)
					{
						$ligneAddMain[] = array('class' => 'boutonsInfoElement', 'value' => '<a id="addMain' . $table . $idElement . '" class="button boutonInfos"><img alt="" title="' . __('Aj. groupement', 'evarisk') . '" src="' . PICTO_LTL_ADD_CATEGORIE_DANGER . '"/>' . __('Aj. gpmt', 'evarisk') . '</a>');
					}
					else
					{
						$ligneAddMain[] = array('class' => 'boutonsInfoElement', 'value' => '');
					}
					if($nbUnitesElement >= 0 and $nbFilsElement == 0)
					{
						$ligneAddSecondary[] = array('class' => 'boutonsInfoElement', 'value' => '<a id="addSecondary' . $table . $idElement . '" class="button boutonInfos"><img alt="" title="' . __('Aj. unit&eacute;', 'evarisk') . '" src="' . PICTO_LTL_ADD_DANGER . '"/>' . __('Aj. danger', 'evarisk') . '</a>');
					}
					else
					{
						$ligneAddSecondary[] = array('class' => 'boutonsInfoElement', 'value' => '');
					}
					$aFilsNoeud = 'true';
					$aFilsFeuille = 'false';
					$nombreElementsAffichage = NOMBRE_ELEMENTS_AFFICHAGE_GRILLE_DANGERS;
					break;
				case TABLE_DANGER:
					$mainTable = TABLE_CATEGORIE_DANGER;
					$sousTable = TABLE_DANGER;
					$element = EvaDanger::getDanger($idElement);
					$defaultPicto = DEFAULT_DANGER_PICTO;
					$infosElement = null;
					$ligneSelect[] = array('value' => '', 'class' => 'boutonsInfoElement');
					$aFilsNoeud = 'false';
					$aFilsFeuille = 'true';
					$nombreElementsAffichage = NOMBRE_ELEMENTS_AFFICHAGE_GRILLE_DANGERS;
					break;
			}
			$scriptFilAriane = '
				if(digirisk(\'#filAriane :last-child\').is("label"))
					digirisk(\'#filAriane :last-child\').remove();
				digirisk(\'#filAriane\').append(\'<label>&nbsp;&raquo;&nbsp;</label><a href="#" id="element' . $element->id . '" class="elementFilAriane">' . addslashes($element->nom) . '</a>\');
				digirisk(\'#page' . $idPostBox . 'Reference\').val(1);
				digirisk(document).ready(function() {
					digirisk(\'#element' . $element->id . '\').click(function() {
						digirisk(\'#identifiantActuelle' . $idPostBox . '\').val("' . $element->id . '");
						digirisk(\'#page' . $idPostBox . 'Reference\').val(1);
						while(digirisk(\'#filAriane :last-child\').attr("id") != "element' . $element->id . '")
						{
							digirisk(\'#filAriane :last-child\').remove();
						}

						changementPage("right", "' . $table . '", digirisk("#page' . $idPostBox . 'Reference").val(), digirisk("#identifiantActuelle' . $idPostBox . '").val(), "affichageTable", "main");
						changementPage("left", "' . $table . '", digirisk("#page' . $idPostBox . 'Reference").val(), digirisk("#identifiantActuelle' . $idPostBox . '").val(), "affichageTable", "main");

						digirisk(\'#' . $idPostBox . ' h3 span\').html("' . addslashes($element->nom) . '");
						return false;
					});
				});';

			$indiceLigneValeur = 1;
			$lignesDeValeurs[$indiceLigneValeur++][] = array('value' => $element->nom, 'class' => 'nomElement');
			// $photoElement = EVA_HOME_URL . $element->mainPhoto;
			// if($photoElement == EVA_HOME_URL)
			// {
				// $photoElement = $defaultPicto;
			// }
			$photoElement = evaPhoto::getMainPhoto($table, $idElement);
			switch($table)
			{
				case TABLE_CATEGORIE_DANGER:
					$photoElement = ($photoElement != 'error') ? (EVA_HOME_URL . $photoElement) : DEFAULT_DANGER_CATEGORIE_PICTO;
				break;
				case TABLE_GROUPEMENT:
					$photoElement = ($photoElement != 'error') ? (EVA_HOME_URL . $photoElement) : DEFAULT_GROUP_PICTO;
				break;
				case TABLE_UNITE_TRAVAIL:
					$photoElement = ($photoElement != 'error') ? (EVA_HOME_URL . $photoElement) : DEFAULT_WORKING_UNIT_PICTO;
				break;
			}

			$lignesDeValeurs[$indiceLigneValeur++][] = array('value' => '<img id="photo' . $table . $idElement . '" src="' . $photoElement . '" alt="mainPhoto" title="mainPhoto" />', 'class' => 'photoElement');
			for($i=$indiceLigneValeur; $i<(count($infosElement)+$indiceLigneValeur); $i++)
			{
				$infoElement = $infosElement[$i - $indiceLigneValeur];
				$lignesDeValeurs[$i][] = array('value' => $infoElement['nom'], 'class' => 'nomInfoElement');
				$lignesDeValeurs[$i][] = array('value' => ':', 'class' => 'deuxPoints');
				$lignesDeValeurs[$i][] = array('value' => $infoElement['valeur'], 'class' => 'valeurInfoElement ' . $infoElement['classeValeur']);
			}
			$nombreElements = $nombreElements + 1;

			$script = $script .'
				<script type="text/javascript">
					digirisk(document).ready(function() {
						if(' . $aFilsNoeud . ')
						{
							digirisk("#favorite-second-link-' . $idPostBox . '").hide();
							digirisk("#favorite-first-link-' . $idPostBox . '").show();
						}
						if(' . $aFilsFeuille . ')
						{
							digirisk("#favorite-first-link-' . $idPostBox . '").hide();
							digirisk("#favorite-second-link-' . $idPostBox . '").show();
						}
						var timeoutDbl' . $table . $idElement . '_0;
						var timeoutDbl' . $table . $idElement . '_1;
						var nbClic = 0;
						digirisk(\'#photo' . $table . $idElement . '\').dblclick(function() {
							clearTimeout(timeoutDbl' . $table . $idElement . '_0);
							clearTimeout(timeoutDbl' . $table . $idElement . '_1);
							if((digirisk("#select' . $table . $idElement . '").attr("id")) != undefined)
								digirisk("#select' . $table . $idElement . '").click();
							else
								digirisk("#edit' . $table . $idElement . '").click();
						});
						digirisk(\'#photo' . $table . $idElement . '\').parent().click(function(event){
							if(nbClic == 0)
							{
								timeoutDbl' . $table . $idElement . '_0 = setTimeout
								(
									function()
									{
										digirisk("#edit' . $table . $idElement . '").click();
									},
									300
								);
							}
							else
							{
								timeoutDbl' . $table . $idElement . '_1 = setTimeout
								(
									function()
									{
										digirisk("#edit' . $table . $idElement . '").click();
									},
									300
								);
							}
							nbClic = (nbClic + 1)%2;
							return("false");
						});
						digirisk(\'#edit' . $table . $idElement . '\').click(function() {
							digirisk("#menu").val(\'gestiongrptut\');
							if(digirisk(\'#filAriane :last-child\').is("label"))
								digirisk(\'#filAriane :last-child\').remove();
							digirisk(\'#filAriane :last-child\').after(\'<label>&nbsp;&raquo;&nbsp;&Eacute;dition&nbsp;de&nbsp;' . $element->nom . '</label>\');
							' . $chargement . '
						});
						digirisk(\'#risq' . $table . $idElement . '\').click(function() {
							digirisk("#menu").val(\'risq\');
							if(digirisk(\'#filAriane :last-child\').is("label"))
								digirisk(\'#filAriane :last-child\').remove();
							digirisk(\'#filAriane :last-child\').after(\'<label>&nbsp;&raquo;&nbsp;&Eacute;valuation des risques de ' . $element->nom . '</label>\');
							' . $chargement . '
						});
						digirisk(\'#addMain' . $table . $idElement . '\').click(function() {
							digirisk(\'#identifiantActuelle' . $idPostBox . '\').val("' . $element->id . '");
							digirisk(\'#page' . $idPostBox . 'Reference\').val(1);
							digirisk("#rightEnlarging").show();
							digirisk("#equilize").click();
							digirisk("#menu").val(\'gestiongrptut\');
							' . $scriptFilAriane . '
							digirisk("#filAriane :last-child").after("<label>&nbsp;&raquo;&nbsp;Ajout d\'un nouveau groupement &agrave; ' . $element->nom . '</label>");

							digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
								"table": "' . $table . '",
								"act": "add",
								"page": digirisk(\'#page' . $idPostBox . 'Reference\').val(),
								"idPere": ' . $element->id . ',
								"partie": "right",
								"menu": digirisk("#menu").val(),
								"affichage": "affichageTable",
								"partition": "tout"
							});
							digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
								"table": "' . $table . '",
								"act": "add",
								"page": digirisk(\'#page' . $idPostBox . 'Reference\').val(),
								"idPere": ' . $element->id . ',
								"partie": "left",
								"menu": digirisk("#menu").val(),
								"affichage": "affichageTable",
								"partition": "tout"
							});

							digirisk("#partieEdition").html(digirisk("#loadingImg").html());
							digirisk("#partieGauche").html(digirisk("#loadingImg").html());
							return false;
						});
						digirisk(\'#addSecondary' . $table . $idElement . '\').click(function() {
							digirisk(\'#identifiantActuelle' . $idPostBox . '\').val("' . $element->id . '");
							digirisk(\'#page' . $idPostBox . 'Reference\').val(1);
							digirisk("#rightEnlarging").show();
							digirisk("#equilize").click();
							digirisk("#menu").val(\'gestiongrptut\');
							' . $scriptFilAriane . '
							digirisk("#filAriane :last-child").after("<label>&nbsp;&raquo;&nbsp;Ajout d\'une nouvelle unit&eacute; de travail &agrave; ' . $element->nom . '</label>");

							digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
								"table": "' . $sousTable . '",
								"act": "add",
								"page": 1,
								"idPere": ' . $element->id . ',
								"partie": "right",
								"menu": digirisk("#menu").val(),
								"affichage": "affichageTable",
								"partition": "tout"
							});
							digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
								"table": "' . $sousTable . '",
								"act": "add",
								"page": digirisk(\'#page' . $idPostBox . 'Reference\').val(),
								"idPere": digirisk(\'#identifiantActuelle' . $idPostBox . '\').val(),
								"partie": "left",
								"menu": digirisk("#menu").val(),
								"affichage": "affichageTable",
								"partition": "tout"
							});

							digirisk("#partieEdition").html(digirisk("#loadingImg").html());
							digirisk("#partieGauche").html(digirisk("#loadingImg").html());
							return false;
						});
						digirisk(\'#select' . $table . $idElement . '\').click(function() {
							if(!(digirisk(this).prop("disabled")))
							{
								if(digirisk(\'#filAriane :last-child\').is("label"))
									digirisk(\'#filAriane :last-child\').remove();
								digirisk(\'#identifiantActuelle' . $idPostBox . '\').val(' . $idElement . ');
								' . $scriptFilAriane . '

								changementPage("right", "' . $table . '", digirisk("#page' . $idPostBox . 'Reference").val(), digirisk("#identifiantActuelle' . $idPostBox . '").val(), "affichageTable", "main");
								changementPage("left", "' . $table . '", digirisk("#page' . $idPostBox . 'Reference").val(), digirisk("#identifiantActuelle' . $idPostBox . '").val(), "affichageTable", "main");

								digirisk(\'#' . $idPostBox . ' h3 span\').html("' . addslashes($element->nom) . '");
								return false;
							}
						});
					});
				</script>';
		}
		for($i = 0; $i<$nombreElements; $i++)
		{
			$lignesDeValeurs[0][] = array('value' => null, 'class' => 'nomInfoElement');
			$lignesDeValeurs[0][] = array('value' => null, 'class' => 'deuxPoints');
			$lignesDeValeurs[0][] = array('value' => null, 'class' => 'valeurInfoElement');
		}

		$lignesDeValeurs[] = $ligneEditer;
		$lignesDeValeurs[] = $ligneAddMain;
		$lignesDeValeurs[] = $ligneAddSecondary;
		$lignesDeValeurs[] = $ligneSelect;
		$lignesDeValeurs[][] = array('value' => EvaDisplayDesign::afficherPagination($idPostBox, $pageMaxPostBox, $mainTable), 'class' => 'pagination');

		$idTable = 'table' . $idPostBox;
		$titres = null;
		$classes = null;
		$idLignes = null;
		$largeur = 98 / $nombreElementsAffichage;
		$largeurNomInfo = $largeur * 5 / 11 ;
		$largeurValeurInfo = $largeur * 3 / 11 ;
		$largeurDeuxPoints = $largeur * 1 / 11 ;
		$largeurDerniereColonne = $largeur * 2 / 11 ;
		$listeBoutons = '';

		$chargement = '
			digirisk("#rightEnlarging").show();
			digirisk("#equilize").click();
			digirisk("#partieEdition").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
				"table": "' . $mainTable . '",
				"id": "' . $_POST['idPere'] . '",
				"page": digirisk(\'#page' . $idPostBox . 'Reference\').val(),
				"idPere": digirisk(\'#identifiantActuelle' . $idPostBox . '\').val(),
				"act": "edit",
				"partie": "right",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageTable",
				"partition": "tout"
			});
			digirisk("#partieGauche").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post": "true",
				"table": "' . $mainTable . '",
				"id": "' . $_POST['idPere'] . '",
				"page": digirisk(\'#page' . $idPostBox . 'Reference\').val(),
				"idPere": digirisk(\'#identifiantActuelle' . $idPostBox . '\').val(),
				"act": "edit",
				"partie": "left",
				"menu": digirisk("#menu").val(),
				"affichage": "affichageTable",
				"partition": "tout"
			});
			digirisk("#partieEdition").html(digirisk("#loadingImg").html());
			digirisk("#partieGauche").html(digirisk("#loadingImg").html());
			return false;';
		//ajout des �l�ments de la barre latt�rale/side-bar
		switch($_POST['table'])
		{
			case TABLE_GROUPEMENT :
			case TABLE_UNITE_TRAVAIL :
			{
				$elementPere = Arborescence::getPere(TABLE_GROUPEMENT ,$element);
				if($elementPere == null)
				{
					$elementPere = EvaGroupement::getGroupement($element->id_groupement);
				}
				if($_POST['idPere'] == 1)
				{// On est � la racine
					$listeBoutons = '
						"<img src=\"' . PICTO_LTL_ADD_GROUPEMENT . '\" id=\"addMain' . $_POST['table'] . $_POST['idPere'] . '\"/><br />" +
					';
					$elementPere->nom = "la racine";
				}
				else
				{// On n'est pas � la racine
					// On regarde le bouton editer qui existe forc�ment
					// pour pouvoir deviner si l'on a � faire � un ensemble de sous-groupement
					// ou � un ensemble de d'unit�s
					if(substr($lignesDeValeurs[8][0]['value'], strlen('<a id="edit'), strlen(TABLE_GROUPEMENT)) == TABLE_GROUPEMENT)
					{// C'est un ensemble de sous-groupement
						$listeBoutons = '
							"<img src=\"' . PICTO_LTL_ADD_GROUPEMENT . '\" id=\"addMain' . $_POST['table'] . $_POST['idPere'] . '\"/><br />" +
						';
					}
					else
					{// C'est un ensemble d'unit�s
						$listeBoutons = '
							"<img src=\"' . PICTO_LTL_ADD_UNIT . '\" id=\"addSecondary' . $_POST['table'] . $_POST['idPere'] . '\"/><br />" +
						';
					}
					$listeBoutons = $listeBoutons . '
						"<img src=\"' . PICTO_EDIT . '\" id=\"edit' . $_POST['table'] . $_POST['idPere'] . '\"/><br />" +
						"<img src=\"' . PICTO_LTL_EVAL_RISK . '\" id=\"risq' . $_POST['table'] . $_POST['idPere'] . '\"/><br />" +
					';
				}
				$scriptColDroite = '<script type="text/javascript">
					digirisk("#favorite-actions-mainPostBox").hide();
					digirisk(document).ready(function() {
						digirisk(\'#edit' . $_POST['table'] . $_POST['idPere'] . '\').click(function() {
							digirisk("#menu").val(\'gestiongrptut\');
							if(digirisk(\'#filAriane :last-child\').is("label"))
								digirisk(\'#filAriane :last-child\').remove();
							digirisk(\'#filAriane :last-child\').after(\'<label>&nbsp;&raquo;&nbsp;&Eacute;dition&nbsp;de&nbsp;' . $elementPere->nom . '</label>\');
							' . $chargement . '
						});
						digirisk(\'#risq' . $_POST['table'] . $_POST['idPere'] . '\').click(function() {
							digirisk("#menu").val(\'risq\');
							if(digirisk(\'#filAriane :last-child\').is("label"))
								digirisk(\'#filAriane :last-child\').remove();
							digirisk(\'#filAriane :last-child\').after(\'<label>&nbsp;&raquo;&nbsp;&Eacute;valuation des risques de ' . $elementPere->nom . '</label>\');
							' . $chargement . '
						});
						digirisk(\'#addMain' . $_POST['table'] . $_POST['idPere'] . '\').click(function() {
							digirisk("#rightEnlarging").show();
							digirisk("#equilize").click();
							digirisk("#menu").val(\'gestiongrptut\');
							if(digirisk(\'#filAriane :last-child\').is("label"))
								digirisk(\'#filAriane :last-child\').remove();
							digirisk("#filAriane :last-child").after("<label>&nbsp;&raquo;&nbsp;Ajout d\'un nouveau groupement &agrave; ' . $elementPere->nom . '</label>");
							digirisk("#favorite-first-link-mainPostBox").click();
						});
						digirisk(\'#addSecondary' . $_POST['table'] . $_POST['idPere'] . '\').click(function() {
							digirisk("#rightEnlarging").show();
							digirisk("#equilize").click();
							digirisk("#menu").val(\'gestiongrptut\');
							if(digirisk(\'#filAriane :last-child\').is("label"))
								digirisk(\'#filAriane :last-child\').remove();
							digirisk("#filAriane :last-child").after("<label>&nbsp;&raquo;&nbsp;Ajout d\'une nouvelle unit&eacute; de travail &agrave; ' . $elementPere->nom . '</label>");
							digirisk("#favorite-second-link-mainPostBox").click();
						});
					});
				</script>';
				break;
			}
			case TABLE_CATEGORIE_DANGER :
			case TABLE_DANGER :
			{
				$elementPere = Arborescence::getPere(TABLE_CATEGORIE_DANGER ,$element);
				if($elementPere == null)
				{
					$elementPere = categorieDangers::getCategorieDanger($element->id_categorie);
				}
				if($_POST['idPere'] == 1)
				{// On est � la racine
					$listeBoutons = '
						"<img src=\"' . PICTO_LTL_ADD_CATEGORIE_DANGER . '\" id=\"addMain' . $_POST['table'] . $_POST['idPere'] . '\"/><br />" +
					';
					$elementPere->nom = "la racine";
				}
				else
				{// On n'est pas � la racine
					// On regarde le bouton editer qui existe forc�ment
					// pour pouvoir deviner si l'on a � faire � un ensemble de cat�gories
					// ou � un ensemble de de dangers
					if(substr($lignesDeValeurs[count($lignesDeValeurs) - 3][0]['value'], strlen('<a id="edit'), strlen(TABLE_CATEGORIE_DANGER)) == TABLE_CATEGORIE_DANGER)
					{// C'est un ensemble de cat�gories
						$listeBoutons = '
							"<img src=\"' . PICTO_LTL_ADD_CATEGORIE_DANGER . '\" id=\"addMain' . $_POST['table'] . $_POST['idPere'] . '\"/><br />" +
						';
					}
					else
					{// C'est un ensemble de dangers
						$listeBoutons = '
							"<img src=\"' . PICTO_LTL_ADD_DANGER . '\" id=\"addSecondary' . $_POST['table'] . $_POST['idPere'] . '\"/><br />" +
						';
					}
					$listeBoutons = $listeBoutons . '
						"<img src=\"' . PICTO_EDIT . '\" id=\"edit' . $_POST['table'] . $_POST['idPere'] . '\"/><br />" +
					';
				}
				$scriptColDroite = '<script type="text/javascript">
					digirisk("#favorite-actions-mainPostBox").hide();
					digirisk(document).ready(function() {
						digirisk(\'#edit' . $_POST['table'] . $_POST['idPere'] . '\').click(function() {
							digirisk("#menu").val(\'gestiongrptut\');
							if(digirisk(\'#filAriane :last-child\').is("label"))
								digirisk(\'#filAriane :last-child\').remove();
							digirisk(\'#filAriane :last-child\').after(\'<label>&nbsp;&raquo;&nbsp;&Eacute;dition&nbsp;de&nbsp;' . $elementPere->nom . '</label>\');
							' . $chargement . '
						});
						digirisk(\'#addMain' . $_POST['table'] . $_POST['idPere'] . '\').click(function() {
							digirisk("#rightEnlarging").show();
							digirisk("#equilize").click();
							digirisk("#menu").val(\'gestiongrptut\');
							if(digirisk(\'#filAriane :last-child\').is("label"))
								digirisk(\'#filAriane :last-child\').remove();
							digirisk("#filAriane :last-child").after("<label>&nbsp;&raquo;&nbsp;Ajout d\'une nouvelle cat&eacute;gorie de dangers &agrave; ' . $elementPere->nom . '</label>");
							digirisk("#favorite-first-link-mainPostBox").click();
						});
						digirisk(\'#addSecondary' . $_POST['table'] . $_POST['idPere'] . '\').click(function() {
							digirisk("#rightEnlarging").show();
							digirisk("#equilize").click();
							digirisk("#menu").val(\'gestiongrptut\');
							if(digirisk(\'#filAriane :last-child\').is("label"))
								digirisk(\'#filAriane :last-child\').remove();
							digirisk("#filAriane :last-child").after("<label>&nbsp;&raquo;&nbsp;Ajout d\'un nouveau danger &agrave; ' . $elementPere->nom . '</label>");
							digirisk("#favorite-second-link-mainPostBox").click();
						});
					});
				</script>';
				break;
			}
			default :
			{
				echo 'Pensez &agrave; <b>ajouter</b> le <b>cas ' . $_POST['table'] . '</b> dans le <b>switch</b> ligne <b>' . __LINE__ . '</b> du fichier "' . dirname(__FILE__) . '\<b>' . basename(__FILE__) . '</b>" pour avoir la liste de boutons.<br />';
				break;
			}
		}
		$scriptTable = '<script type="text/javascript">
				digirisk(document).ready(function() {
					digirisk("#' . $idTable . ' tr:first").append(
						"<td rowspan=' . (count($lignesDeValeurs) - 1) . ' style=\"width:' . $largeurDerniereColonne . '%;\">" + ' . $listeBoutons . '
						"</td>"
					);
					digirisk("#' . $idTable . ' tr:first").append();
					digirisk("#' . $idTable . ' .nomElement, #' . $idTable . ' .photoElement, #' . $idTable . ' .boutonsInfoElement").each(function(){
						digirisk(this).attr("colspan", "3");
					});
					digirisk("#' . $idTable . ' .nomInfoElement").each(function(){
						digirisk(this).css("width","' . $largeurNomInfo . '%");
					});
					digirisk("#' . $idTable . ' .deuxPoints").each(function(){
						digirisk(this).css("width","' . $largeurDeuxPoints . '%");
					});
					digirisk("#' . $idTable . ' .valeurInfoElement").each(function(){
						digirisk(this).css("width","' . $largeurValeurInfo . '%");
					});
					digirisk("#' . $idTable . ' .pagination").each(function(){
						digirisk(this).attr("colspan", "' . ($nombreElements * 3 + 1) . '");
					});
					digirisk("#' . $idPostBox . ' .inside").each(function(){
						digirisk(this).css("padding", "0");
					});
				});
			</script>';
		$tableMainDisplay = EvaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $scriptTable);

		$tableMainDisplay = $script . $tableMainDisplay . $scriptColDroite;
		return $tableMainDisplay;
	}

	/**
	*	Allow to output a template filled with external vars
	*
	*	@param mixed $template The html template with the different element to replace
	*	@param array $param An array with the different value to replace elements in the template
	*
	*	@return mixed $template The html template filled with the different element
	*/
	static function feedTemplate($template, $param)
	{
    if(is_array($param))
		{
			foreach($param as $key => $value)
			{
				if(!is_array($value))
				{
					$template=str_replace($key, $value, $template);
				}
				else
				{
					$template=str_replace($key, "<div style=\"border:1px solid red;\" >$key = <pre>".print_r($value)."</pre></div>", $template);
				}
			}
			return $template;
    }
		else
		{
        return str_replace($param, $value, $template);
    }
	}



	/**
	*	Return the form template for generating the single document
	*	@return string HTML code of the form
	*/
	static function getFormulaireGenerationDUER()
	{
		return
				'<table summary="" border="0" cellpadding="0" cellspacing="0" align="center" class="tabcroisillon" style="width:100%;" >
					<tr>
						<td style="width:60%;" >' .
							EvaDisplayInput::ouvrirForm('POST', 'infosGenerationDU', 'infosGenerationDU') .
							'<table summary="" cellpadding="0" cellspacing="0" border="0" class="tabformulaire" style="width:100%;" >
								<tr>
									<td ><label for="dateCreation">' . __('date de cr&eacute;ation', 'evarisk') . '</label></td>
									<td >' . EvaDisplayInput::afficherInput('text', 'dateCreation', '#DATEFORM1#', '', '', 'dateCreation', false, false, 150, '', 'Date', '100%', '', 'left;width:100%;', false) . '</td>
								</tr>
								<tr>
									<td ><label for="dateDebutAudit">' . __('date de d&eacute;but d\'audit', 'evarisk') . '</label></td>
									<td >' . EvaDisplayInput::afficherInput('text', 'dateDebutAudit', '#DATEDEBUT1#', '', '', 'dateDebutAudit', false, false, 150, '', 'Date', '100%', '', 'left;width:100%;', false) . '</td>
								</tr>
								<tr>
									<td ><label for="dateFinAudit">' . __('date de fin d\'audit', 'evarisk') . '</label></td>
									<td >' . EvaDisplayInput::afficherInput('text', 'dateFinAudit', '#DATEFIN1#', '', '', 'dateFinAudit', false, false, 150, '', 'Date', '100%', '', 'left;width:100%;', false) . '</td>
								</tr>
								<tr>
									<td ><label for="nomEntreprise">' . __('nom de l\'entreprise', 'evarisk') . '</label></td>
									<td >' . EvaDisplayInput::afficherInput('text', 'nomEntreprise', '#NOMENTREPRISE#', '', '', 'nomEntreprise', false, false, 150, '', '', '100%', '', 'left;width:100%;', false) . '</td>
								</tr>
								<tr>
									<td ><label for="telephoneFixe">' . __('t&eacute;l&eacute;phone fixe', 'evarisk') . '</label></td>
									<td >' . EvaDisplayInput::afficherInput('text', 'telephoneFixe', '#TELFIXE#', '', '', 'telephoneFixe', false, false, 150, '', '', '100%', '', 'left;width:100%;', false) . '</td>
								</tr>
								<tr>
									<td ><label for="telephonePortable">' . __('t&eacute;l&eacute;phone portable', 'evarisk') .'</label></td>
									<td >' . EvaDisplayInput::afficherInput('text', 'telephonePortable', '#TELPORTABLE#', '', '', 'telephonePortable', false, false, 150, '', '', '100%', '', 'left;width:100%;', false) . '</td>
								</tr>
								<tr>
									<td ><label for="numeroFax">' . __('num&eacute;ro de fax', 'evarisk') . '</label></td>
									<td >' . EvaDisplayInput::afficherInput('text', 'numeroFax', '#TELFAX#', '', '', 'numeroFax', false, false, 150, '', '', '100%', '', 'left;width:100%;', false) . '</td>
								</tr>
								<tr>
									<td ><label for="emetteur">' . __('&eacute;metteur', 'evarisk') . '</label></td>
									<td >' . EvaDisplayInput::afficherInput('text', 'emetteur', '#EMETTEUR#', '', '', 'emetteur', false, false, 150, '', '', '100%', '', 'left;width:100%;', false) . '</td>
								</tr>
								<tr>
									<td ><label for="destinataire">' . __('destinataire', 'evarisk') . '</label></td>
									<td >' . EvaDisplayInput::afficherInput('text', 'destinataire', '#DESTINATAIRE#', '', '', 'destinataire', false, false, 150, '', '', '100%', '', 'left;width:100%;', false) . '</td>
								</tr>
								<tr>
									<td ><label for="nomDuDocument">' . __('nom du document', 'evarisk') . '</label></td>
									<td >' . EvaDisplayInput::afficherInput('text', 'nomDuDocument', '#NOMDOCUMENT#', '', '', 'nomDuDocument', false, false, 150, '', '', '100%', '', 'left;width:100%;', false) . '</td>
								</tr>
								<tr>
									<td ><label for="methodologie">' . __('m&eacute;thodologie', 'evarisk') . '</label></td>
									<td style="text-align:center;"><textarea id="methodologie" name="methodologie" class="textarea14" style="width:100%" ;>#METHODOLOGIE#</textarea></td>
								</tr>
								<tr>
									<td  ><label for="sources">' . __('sources', 'evarisk') . '</label></td>
									<td style="text-align:center;"><textarea id="sources" name="sources" class="textarea14" style="width:100%" ;>#SOURCES#</textarea></td>
								</tr>
								<tr>
									<td  ><label for="remarque_important">' . __('remarque importante', 'evarisk') . '</label></td>
									<td style="text-align:center;"><textarea id="remarque_important" name="remarque_important" class="textarea14" style="width:100%" ;>#REMARQUEIMPORTANT#</textarea></td>
								</tr>
								<tr>
									<td  ><label for="localisation">' . __('localisation', 'evarisk') . '</label></td>
									<td style="text-align:center;"><textarea id="localisation" name="localisation" class="textarea14" style="width:100%" ;>#LOCALISATION#</textarea></td>
								</tr>
								<tr>
									<td >&nbsp;</td>
									<td style="padding:12px 0px;" >
										<div>
											<input type="checkbox" id="modelDefaut" checked="checked" name="modelUse" value="modeleDefaut" />
											<label for="modelDefaut" style="vertical-align:middle;" >' . __('Utiliser le mod&egrave;le par d&eacute;faut', 'evarisk') . '</label>
										</div>
										<div id="modelListForGeneration" style="display:none;" >&nbsp;</div>
									</td>
								</tr>
								<tr>
									<td colspan="2"><input class="button-primary alignright" type="button" id="genererDUER" name="genererDUER" value="' . __('G&eacute;n&eacute;rer le document unique', 'evarisk') . '" /></td>
								</tr>
							</table>' .
							EvaDisplayInput::fermerForm('infosGenerationDU') .
						'</td>
						<td style="width:40%;" id="documentUniqueResultContainer" >&nbsp;</td>
					</tr>
				</table>';
	}

	/**
	*	Return the form template to upload a new DUER model
	*	@return string HTML code of the form
	*/
	static function getNewModelUploadForm( $tableElement, $idElement ) {
		$idUpload = 'model' . $tableElement;
		$allowedExtensions = "['odt']";
		$multiple = false;
		$actionUpload = str_replace('\\', '/', EVA_LIB_PLUGIN_URL . "gestionDocumentaire/uploadFile.php");

		switch ( $tableElement ) {
			case TABLE_GROUPEMENT:
				$repertoireDestination = str_replace('\\', '/', EVA_MODELES_PLUGIN_DIR . 'documentUnique/');
				$defaultModelLink = EVA_MODELES_PLUGIN_URL . 'documentUnique/modeleDefaut.odt';
				$table = TABLE_DUER;
			break;

			case TABLE_GROUPEMENT . '_FP' :
				$repertoireDestination = str_replace('\\', '/', EVA_MODELES_PLUGIN_DIR . 'ficheDePoste/');
				$defaultModelLink = EVA_MODELES_PLUGIN_URL . 'ficheDePoste/modeleDefaut.odt';
				$table = TABLE_FP;
			break;

			case TABLE_GROUPEMENT . '_FGP' :
				$repertoireDestination = str_replace('\\', '/', EVA_MODELES_PLUGIN_DIR . 'ficheDeGroupement/');
				$defaultModelLink = EVA_MODELES_PLUGIN_URL . 'ficheDeGroupement/modeleDefaut_groupement.odt';
				$table = TABLE_FP;
			break;

			case TABLE_UNITE_TRAVAIL:
				$repertoireDestination = str_replace('\\', '/', EVA_MODELES_PLUGIN_DIR . 'ficheDePoste/');
				$defaultModelLink = EVA_MODELES_PLUGIN_URL . 'ficheDePoste/modeleDefaut.odt';
				$table = TABLE_FP;
			break;

			case TABLE_GROUPEMENT . '_FEP':
			case TABLE_UNITE_TRAVAIL . '_FEP':
			case DIGI_DBT_USER . '_FEP':
				$repertoireDestination = str_replace('\\', '/', EVA_MODELES_PLUGIN_DIR . 'ficheDeRisques/');
				$defaultModelLink = EVA_MODELES_PLUGIN_URL . 'ficheDeRisques/modeleDefault_fiche_penibilite.odt';
				$table = TABLE_FP;
			break;

			case TABLE_ACTIVITE . '_FA':
			case TABLE_TACHE . '_FA':
				$repertoireDestination = str_replace('\\', '/', EVA_MODELES_PLUGIN_DIR . 'planDActions/');
				$defaultModelLink = EVA_MODELES_PLUGIN_URL . 'planDActions/modele_default_fiche_action.odt';
				$table = TABLE_ACTIVITE;
			break;

			default:
				sprintf(__('Le cas % n\'a pas &eacute;t&eacute; pr&eacute;vu dans %s &agrave; la ligne %s', 'evarisk'), $tableElement, __FILE__, __LINE__);
			break;
		}

		$newModelForm =
			'<div style="margin:0px auto;width:92%;" >
				<div id="moreModelChoice" >
					' . EvaDisplayDesign::getExistingModelList($tableElement, $idElement) . '
				</div>
				<div style="margin:6px 0px;" class="bold" >' . __('Ajouter un nouveau mod&egrave;le', 'evarisk') . '&nbsp:</div>
				<ol>
					<li><a class="bold" href="' . $defaultModelLink . '" >' . __('T&eacute;l&eacute;chargez le mod&egrave;le par d&eacute;faut', 'evarisk') . '</a></li>
					<li>
						<span class="bold" >' . __('Modifiez le mod&egrave;le', 'evarisk') . '</span>
						<ul>
							<li style="text-align:justify;" >' . __('Apportez vos modifications au fichier t&eacute;l&eacute;charg&eacute;', 'evarisk') . '<br/><span style="color:red;" >' . __('Attention &agrave; ne pas supprimer ou modifier les parties du document qui sont sous la forme "{UnTexte}"', 'evarisk') . '</span></li>
						</ul>
					</li>
					<li>
						<span class="bold" >' . __('Envoyez votre mod&egrave;le', 'evarisk') . '</span>
						<ul>
							<li>' . __( 'Cette fonctionnalité a été momentanément désactivée. VEuillez nous excuser pour la gêne occasionnée', 'evarisk' ) . '</li>
						</ul>
					</li>
				</ol>
			</div>';

		return $newModelForm;
	}

	/**
	 *	Return the combo box with the different existing model
	 *	@return string $moreModelChoice HTML code containing the different existing model
	 */
	function getExistingModelList($tableElement, $idElement) {

		$element_container_for_model_list = '';
		switch($tableElement) {
			case TABLE_GROUPEMENT:
				$documentType = 'document_unique';
			break;
			case TABLE_GROUPEMENT . '_FP':
				$documentType = 'fiche_de_poste';
			break;
			case TABLE_UNITE_TRAVAIL:
				$documentType = 'fiche_de_poste';
			break;
			case TABLE_UNITE_TRAVAIL . '_FEP':
			case TABLE_GROUPEMENT . '_FEP':
			case DIGI_DBT_USER . '_FEP':
				$documentType = 'fiche_exposition_penibilite';
				$element_container_for_model_list = '_' . $tableElement . '_-digi-_' .$idElement;
			break;
			case TABLE_TACHE . '_FA':
			case TABLE_ACTIVITE . '_FA':
				$documentType = 'fiche_action';
			break;
		}

		$moreModelChoice = '';
		$documentList = eva_gestionDoc::getCompleteDocumentList($documentType,
		"	AND SUBSTRING(chemin FROM 1 FOR 8) != 'results/'
			AND id NOT IN (
				SELECT id
				FROM " . TABLE_GED_DOCUMENTS . "
				WHERE id_element = '" . $idElement . "'
					AND table_element = '" . $tableElement . "'
			)
			AND nom NOT IN (
				SELECT nom
				FROM " . TABLE_GED_DOCUMENTS . "
				WHERE id_element = '" . $idElement . "'
					AND table_element = '" . $tableElement . "'
			)			", "dateCreation DESC", $tableElement, $idElement);

		if ( !empty($documentList) ) {
			$moreModelChoice =
				'<div style="margin:6px 0px;" class="bold" >' . __('Affecter un mod&egrave;le existant', 'evarisk') . '&nbsp:</div>
				<ol>
					<li>
						' . __('S&eacute;lectionner un mod&eacute;le', 'evarisk') . '
						<div style="margin:3px 24px;" class="bold" >
							' . evaDisplayInput::afficherComboBox($documentList, 'modelToDuplicate' . $tableElement . '_' . $idElement, '', 'modelToDuplicate' . $tableElement . '_' . $idElement, '', '') . '
							<input type="button" value="' . __('Affecter', 'evarisk') . '" class="button-primary" id="duplicateModel' . $tableElement . '_' . $idElement . '" name="duplicateModel" />
							<script type="text/javascript" >
								digirisk("#duplicateModel' . $tableElement . '_' . $idElement . '").click(function(){
									var data = {
										action: "digi_ajax_duplicate_document",
										element_type: "' . $tableElement . '",
										element_id: "' . $idElement . '",
										document_to_duplicate: jQuery("#modelToDuplicate' . $tableElement . '_' . $idElement . '").val(),
										document_type: "' . $documentType . '",
									};
									jQuery.post(ajaxurl, data, function(response) {
										jQuery("#modelListForGeneration").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
											"post":"true",
											"table":"' . TABLE_GED_DOCUMENTS . '",
											"act":"load_model_combobox",
											"tableElement": response[0],
											"idElement": response[1],
											"category": response[2],
										});
										jQuery("#moreModelChoice").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
											"post":"true",
											"table":"' . TABLE_GED_DOCUMENTS . '",
											"act":"loadExistingDocument",
											"tableElement": response[0],
											"idElement": response[1],
										});
									}, "json");
								});
							</script>
						</div>
					</li>
				</ol>
				<div style="display:table;width:70%;margin:12px auto;" ><hr style="width:35%;" class="alignleft" /><span class="alignleft" style="text-align:center;width:15%;margin:0px 12px;" >' . __('ou', 'evarisk') . '</span><hr style="width:35%;" class="alignleft" /></div>';
		}

		return $moreModelChoice;
	}

}