<?php 
	require_once(EVA_LIB_PLUGIN_DIR . 'evaDisplayInput.class.php' );
	require_once(EVA_LIB_PLUGIN_DIR . 'evaDisplayDesign.class.php' );
	if(!isset($_REQUEST['Rubrique']))
	{
		$rubrique = 'Rubrique 2220';
	}
	else
	{
		$rubrique = $_REQUEST['Rubrique'];
	}
	$racine = $wpdb->get_row( 'SELECT * FROM ' . TABLE_GROUPE_QUESTION . ' where nom="' . $rubrique . '"');
	$idSelect = 'titrePere';
	$labelSelect = 'Se r&eacute;f&egrave;re au titre : ';
	$nameSelect = 'titrePere';
	$valeurDefaut = $racine->nom;
	$selection = $racine->id;
	$comboTitrePere = evaDisplayInput::afficherComboBoxArborescente($racine, TABLE_GROUPE_QUESTION, $idSelect, $labelSelect, $nameSelect, $valeurDefaut, $selection);
?>
<script type="text/javascript">
	
	digirisk(document).ready(function(){
		updateButton();
		var formulaire = document.regulatoryWatchForm;
		digirisk('#texteATraiter').keydown(update).keyup(update).mousedown(update).mouseup(update).mousemove(update);
		digirisk('#titrePere').keydown(updateButton).keyup(updateButton).mousedown(updateButton).mouseup(updateButton);
		
		digirisk('#codeTitre').click(function(){
			for (var i=0; i < formulaire.choixTitreOuQuestion.length; i++)
			{
				if (formulaire.choixTitreOuQuestion[i].checked)
				{
					formulaire.choixTitreOuQuestion[i].checked = false;
				}
				if (formulaire.choixTitreOuQuestion[i].value == "titre")
				{
					formulaire.choixTitreOuQuestion[i].checked = true;
				}
			}
			digirisk('#updateVeille').show(); digirisk('#img_edit_racine').hide(); updateButton();
		});
		
		digirisk('#refreshCombo').click(function(){
			var nomPere = formulaire.titrePere.options[formulaire.titrePere.options.selectedIndex].value;
			var nomRacine = 'Rubrique 2220';
			var idSelect = 'titrePere';
			var labelSelect = 'Se r&eacute;f&egrave;re au titre : ';
			var nameSelect = 'titrePere';
			digirisk('#comboTitrePere').load('<?php echo EVA_INC_PLUGIN_URL; ?>ajax.php', {'post': 'true', 'table': '<?php echo TABLE_GROUPE_QUESTION; ?>', 'act': 'reloadCombo', 'nomRacine' : nomRacine, 'idSelect': idSelect, 'labelSelect': labelSelect, 'nameSelect': nameSelect, 'selection': nomPere});
			digirisk('#divTableGroupeQuestion').load('<?php echo EVA_INC_PLUGIN_URL; ?>ajax.php', {'post': 'true', 'table': '<?php echo TABLE_GROUPE_QUESTION; ?>', 'act': 'reloadTableArborescente', 'idTable':'<?php echo 'tableGroupeQuestion';?>', 'idRacine' : <?php echo $racine->id; ?>, 'nomRacine': '<?php echo $racine->nom; ?>'});
			return false;
		})
		
		digirisk('#traiter').click(function(){
			var x = update();
			var value_radio;
			digirisk('#texteATraiter').replaceSelection("");
			for (var i=0; i < formulaire.choixTitreOuQuestion.length; i++)
			{
				if (formulaire.choixTitreOuQuestion[i].checked)
				{
					value_radio = formulaire.choixTitreOuQuestion[i].value;
				}
			}
			switch(value_radio)
			{
				case 'titre' :
					var code = formulaire.codeTitre.value;
					if(code == "")
					{
						alert("Pas de code");
					}
					else
					{
						
						var idPere = formulaire.titrePere.options[formulaire.titrePere.options.selectedIndex].value;
						digirisk('#ajax-response').load('<?php echo EVA_INC_PLUGIN_URL; ?>ajax.php', {'post': 'true', 'table': '<?php echo TABLE_GROUPE_QUESTION; ?>', 'act': 'save', 'nom': x, 'choix': value_radio, 'idPere': idPere, 'code': code}); 
						var idSelect;
						var labelSelect;
						var nameSelect;
						nomRacine = '<?php echo $rubrique; ?>';
						setTimeout
						( 
							function() 
							{ 
								idSelect = 'titrePere';
								labelSelect = 'Se r&eacute;f&egrave;re au titre : ';
								nameSelect = 'titrePere';
								digirisk('#comboTitrePere').load('<?php echo EVA_INC_PLUGIN_URL; ?>ajax.php', {'post': 'true', 'table': '<?php echo TABLE_GROUPE_QUESTION; ?>', 'act': 'reloadCombo', 'nomRacine' : nomRacine, 'idSelect': idSelect, 'labelSelect': labelSelect, 'nameSelect': nameSelect, 'selection': idPere}); 
								digirisk('#divTableGroupeQuestion').load('<?php echo EVA_INC_PLUGIN_URL; ?>ajax.php', {'post': 'true', 'table': '<?php echo TABLE_GROUPE_QUESTION; ?>', 'act': 'reloadTableArborescente', 'idTable':'<?php echo 'tableGroupeQuestion';?>', 'idRacine' : <?php echo $racine->id; ?>, 'nomRacine': '<?php echo $racine->nom; ?>'});
							}, 
							4000
						);
						formulaire.codeTitre.value = "";
						digirisk('#partieTraitementVeille').slideUp('fast');;
					}
					break;
					
				case 'extraitTexte' :
					var idGroupeQuestion = formulaire.titrePere.options[formulaire.titrePere.options.selectedIndex].value;
					digirisk('#ajax-response').load('<?php echo EVA_INC_PLUGIN_URL; ?>ajax.php', {'post': 'true', 'table': '<?php echo TABLE_GROUPE_QUESTION; ?>', 'choix': value_radio, 'act': 'addExtrait', 'extrait': x, 'idGroupeQuestion': idGroupeQuestion});
					formulaire.codeTitre.value = "";
					digirisk('#partieTraitementVeille').slideUp('fast');;

					break;
					
				case 'question' :
					var idGroupeQuestion = formulaire.titrePere.options[formulaire.titrePere.options.selectedIndex].value;
					digirisk('#ajax-response').load('<?php echo EVA_INC_PLUGIN_URL; ?>ajax.php', {'post': 'true', 'table': '<?php echo TABLE_QUESTION; ?>', 'act': 'save', 'enonce': x, 'idGroupeQuestion': idGroupeQuestion});
					formulaire.codeTitre.value = "";
					digirisk('#partieTraitementVeille').slideUp('fast');;

					break;
			}
			return false;
		});
		
		digirisk('#choixQuestion').click(function(){digirisk('#updateVeille').hide(); digirisk('#img_edit_racine').show(); updateButton();});
		digirisk('#choixExtraitTexte').click(function(){digirisk('#updateVeille').show(); digirisk('#img_edit_racine').show(); updateButton();});
		digirisk('#choixTitre').click(function(){digirisk('#updateVeille').show(); digirisk('#img_edit_racine').hide(); updateButton();digirisk("#codeTitre").focus();});
		digirisk('#updateVeille').click(function(){
			var x = update();
			var value_radio;
			digirisk('#texteATraiter').replaceSelection("");
			for (var i=0; i < formulaire.choixTitreOuQuestion.length; i++)
			{
				if (formulaire.choixTitreOuQuestion[i].checked)
				{
					value_radio = formulaire.choixTitreOuQuestion[i].value;
				}
			}
			switch(value_radio)
			{
				case 'titre' :
					var code = formulaire.codeTitre.value;
					if(code == "")
					{
						alert("Pas de code");
					}
					else
					{
						
						var idPere = formulaire.titrePere.options[formulaire.titrePere.options.selectedIndex].value;
						digirisk('#ajax-response').load('<?php echo EVA_INC_PLUGIN_URL; ?>ajax.php', {'post': 'true', 'table': '<?php echo TABLE_GROUPE_QUESTION; ?>', 'act': 'update', 'id': idPere, 'nom': x, 'choix': value_radio, 'code': code}); 
						var idSelect;
						var labelSelect;
						var nameSelect;
						nomRacine = 'Rubrique 2220';
						setTimeout
						( 
							function() 
							{ 
								idSelect = 'titrePere';
								labelSelect = 'Se r&eacute;f&egrave;re au titre : ';
								nameSelect = 'titrePere';
								digirisk('#comboTitrePere').load('<?php echo EVA_INC_PLUGIN_URL; ?>ajax.php', {'post': 'true', 'table': '<?php echo TABLE_GROUPE_QUESTION; ?>', 'act': 'reloadCombo', 'nomRacine' : nomRacine, 'idSelect': idSelect, 'labelSelect': labelSelect, 'nameSelect': nameSelect, 'selection': idPere});
								digirisk('#divTableGroupeQuestion').load('<?php echo EVA_INC_PLUGIN_URL; ?>ajax.php', {'post': 'true', 'table': '<?php echo TABLE_GROUPE_QUESTION; ?>', 'act': 'reloadTableArborescente', 'idTable':'<?php echo 'tableGroupeQuestion';?>', 'idRacine' : <?php echo $racine->id; ?>, 'nomRacine': '<?php echo $racine->nom; ?>'});
							}, 
							4000
						);
						formulaire.codeTitre.value = "";
						digirisk('#partieTraitementVeille').slideUp('fast');;
					}
					break;
					
				case 'extraitTexte' :
					var idGroupeQuestion = formulaire.titrePere.options[formulaire.titrePere.options.selectedIndex].value;
					digirisk('#ajax-response').load('<?php echo EVA_INC_PLUGIN_URL; ?>ajax.php', {'post': 'true', 'table': '<?php echo TABLE_GROUPE_QUESTION; ?>', 'choix': value_radio, 'act': 'replaceExtrait', 'extrait': x, 'idGroupeQuestion': idGroupeQuestion});
					formulaire.codeTitre.value = "";
					digirisk('#partieTraitementVeille').slideUp('fast');;

					break;
					
				case 'question' :
					break;
			}
			return false;
		});
		
		digirisk('#texteATraiter').focus(function() {
			if(digirisk('#texteATraiter').is(".form-input-tip"))
			{
				document.getElementById('texteATraiter').value="";
				digirisk('#texteATraiter').removeClass('form-input-tip');
			}
		});
		
		digirisk('#texteATraiter').blur(function() {
			if(document.getElementById('texteATraiter').value == "")
			{
				digirisk('#texteATraiter').addClass('form-input-tip');
				document.getElementById('texteATraiter').value="Copier le texte Reglementaire ici.";
			}
		});
	});

	function update() {
		var range = digirisk('#texteATraiter').getSelection();
		var text=(range.text);
		document.getElementById('texteVeilleSelectione').innerHTML = text.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2');
		if(range.text == '')
		{
			digirisk('#partieTraitementVeille').slideUp('fast');
		}
		else
		{
			digirisk('#partieTraitementVeille').slideDown('normal');
		}
		return range.text;
	}
	
	function updateButton() {
		var formulaire = document.regulatoryWatchForm;
		var value_radio;
		for (var i=0; i < formulaire.choixTitreOuQuestion.length; i++)
		{
			if (formulaire.choixTitreOuQuestion[i].checked)
			{
				value_radio = formulaire.choixTitreOuQuestion[i].value;
			}
		}
		var titreReferent = formulaire.titrePere.options[formulaire.titrePere.options.selectedIndex].innerHTML;
		titreReferent = titreReferent.replace(new RegExp("&nbsp;[&nbsp;]+", "g"),"");
		if(titreReferent == "<?php echo $rubrique;?>")
		{
			digirisk('#updateVeille').hide();
		}
		else
		{
			digirisk('#updateVeille').show();
		}
		if(value_radio == 'titre')
		{
			var changer = "le titre et la numerotation de " + titreReferent;
			var ajouter = "le titre sous le titre " + titreReferent;
		}
		else if(value_radio == 'extraitTexte')
		{
			var changer = "l'extrait de texte de " + titreReferent;
			var ajouter = "a l'extrait de texte du titre " + titreReferent;
		}
		else if(value_radio == 'question')
		{
			var changer = "";
			var ajouter = "la question au titre " + titreReferent;
			digirisk('#updateVeille').hide();
		}
		digirisk('#updateVeille').html("Changer " + changer);
		digirisk('#traiter').html("Ajouter " + ajouter);
	}
</script>

<div class="wrap">
	<div id="filArianeVeille">
	</div>
	<form id="regulatoryWatchAdd" method="POST" name="regulatoryWatchForm">
		<div id="partieTexteVeille">
			<p> S&eacute;l&eacute;ctionnez la partie de texte &agrave; traiter </p>
			<textarea id="texteATraiter" class="form-input-tip" rows=12 style="width: 100%;" aria-required="true" name="texteATraiter">Copier le texte Reglementaire ici.</textarea>
		</div>
		<div id="partieTraitementVeille" style="display: none">
			<div>
				Texte s&eacute;l&eacute;ction&eacute; :
			</div>
			<div id="texteVeilleSelectione" class="veille-reponse-extraitTexte">
			</div>
			<br />
			<div>
				<span style="display:none;" id="comboTitrePere">
				<?php 
					echo $comboTitrePere;
				?>
				</span>
			</div>			
			
			<ul class="listeCreationVeille">
				<li>
					<input type="radio" checked="checked" name="choixTitreOuQuestion" value="titre" id="choixTitre"/>
					<label for="choixTitre"> Titre </label>
					<label for="codeTitre"> dont la numerotation hierachique est : </label>
					<input type="text" name="codeTitre" id="codeTitre"/>
				</li>
				<li>
					<input type="radio" name="choixTitreOuQuestion" value="extraitTexte" id="choixExtraitTexte"/>
					<label for="choixExtraitTexte"> Extrait texte </label>
				</li>
				<li>
					<input type="radio" name="choixTitreOuQuestion" value="question" id="choixQuestion"/>
					<label for="choixQuestion"> Question </label>
				</li>
				<span>
					<a href=# class="button" id="refreshCombo"><img src="<?php echo PICTO_REFRECH;?>" alt="Rafraich&icirc;r" title="Rafraich&icirc;r la liste"></img></a>
				</span>
			</ul>
			<br />
			<div style="text-align: center; display:none;">
				<a href='#' class="button" id="updateVeille" style="display:none;"/></a>
				<a href='#' class="button" id="traiter"/>Ajouter &agrave; la <? echo $rubrique; ?></a>
			</div>
		</div>
		<br />
		<div id="divTableGroupeQuestion">
			<?php
				echo evaDisplayDesign::getTableArborescence($racine, TABLE_GROUPE_QUESTION, 'tableGroupeQuestion', $racine->nom);
			?>
		</div>
	</form>
	<div id="ajax-response"></div>
</div>