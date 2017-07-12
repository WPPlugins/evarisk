<?php

require_once(EVA_LIB_PLUGIN_DIR . 'tcpdf/config/lang/fra.php');
require_once(EVA_LIB_PLUGIN_DIR . 'tcpdf/config/configPDF.php');
require_once(EVA_LIB_PLUGIN_DIR . 'tcpdf/tcpdf.php');
require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaReferentialText.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaGroupeQuestion.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaAnswerToQuestion.class.php' );
require_once(EVA_LIB_PLUGIN_DIR . 'veilleReglementaire/evaAnswer.class.php' );


function stripAccents($string)
{
	$newString = str_replace(array('à', 'á', 'â', 'ã', 'ä'), 'a', $string);
	$newString = str_replace(array('À', 'Á', 'Â', 'Ã', 'Ä'), 'A', $newString);
	$newString = str_replace(array('é', 'è', 'ê', 'ë'), 'e', $newString);
	$newString = str_replace(array('É', 'È', 'Ê', 'Ë'), 'E', $newString);
	$newString = str_replace(array('ì', 'í', 'î', 'ï'), 'i', $newString);
	$newString = str_replace(array('Ì', 'Í', 'Î', 'Ï'), 'I', $newString);
	$newString = str_replace(array('ò', 'ó', 'ô', 'ö', 'õ'), 'o', $newString);
	$newString = str_replace(array('Ò', 'Ó', 'Ô', 'Ö', 'Õ'), 'O', $newString);
	$newString = str_replace(array('ù', 'ú', 'û', 'ü'), 'u', $newString);
	$newString = str_replace(array('Ù', 'Ú', 'Û', 'Ü'), 'U', $newString);
	$newString = str_replace(array('ý', 'ÿ'), 'y', $newString);
	$newString = str_replace(array('Ý', 'Ÿ'), 'Y', $newString);
	$newString = str_replace('ç', 'c', $newString);
	$newString = str_replace('Ç', 'C', $newString);
	$newString = str_replace('ñ', 'n', $newString);
	$newString = str_replace('Ñ', 'N', $newString);
	$newString = str_replace('n°', '', $newString);
	$newString = str_replace('°', '_', $newString);
	return $newString;
}

function getTableauReponses($questionGroup)
{
	global $reponses;
	global $nonConformites;
	global $tableElement;
	global $idElement;
	if($questionGroup->code != "" AND $questionGroup->code != 0)
	{
		$reponses[] = array('type' => 'groupeQuestion', 'enonce' => $questionGroup->code . '. ' . ucfirst($questionGroup->nom), 'reponse'=> '', 'observation'=>'');
	}
	if($questionGroup->extraitTexte != null)
	{
		$reponses[] = array('type' => 'extrait', 'enonce' => $questionGroup->extraitTexte, 'reponse'=> '', 'observation'=>'');
	}
	$questions = EvaGroupeQuestions::getQuestionsDuGroupeQuestions($questionGroup->id , "code ASC", "Valid");
	if(count($questions) > 0)
	{
		foreach($questions as $question)
		{
			$reponseALaQuestion = EvaAnswerToQuestion::getLatestAnswerByQuestionAndElement($question->id, $tableElement, $idElement);
			if($reponseALaQuestion->date >= $dateControle)
			{
				$valeur = "";
				$reponseQuestion = "";
				$limiteValidite = "";
				if($reponseALaQuestion->valeur != null)
				{
					$valeur = $reponseALaQuestion->valeur;
				}
				if($reponseALaQuestion->id_reponse != null)
				{
					$reponseQuestion = EvaAnswer::getAnswer($reponseALaQuestion->id_reponse);
					$reponseQuestion = $reponseQuestion->nom;
				}
				if(($valeur != "" AND $valeur < 100) OR strtolower($reponseQuestion) == "non" OR strtolower($reponseQuestion) == "nc")
				{
					$observations = explode("\n", $reponseALaQuestion->observation);
					if($observations[0] == null)
					{
						$nonConformites[] = array('numero' => 'Q' . $question->id . ' - 1', 'constat' => 'Cf. Q' . $question->id);
					}
					else
					{
						;
						for($i=0; $i<count($observations); $i ++)
						{
							$nonConformites[] = array('numero' => 'Q' . $question->id . ' - ' . ($i + 1), 'constat' => ucfirst($observations[$i]));
						}
					}
				}
				$reponses[] = array('type' => 'question', 'enonce' => 'Q' . $question->id . ' : ' . ucfirst($question->enonce), 'reponse'=> $valeur . $reponseQuestion, 'observation'=> ucfirst($reponseALaQuestion->observation));
			}
			else
			{
				$reponses[] = array('type' => 'question', 'enonce' => 'Q' . $question->id . ' : ' . ucfirst($question->enonce), 'reponse'=>'', 'observation'=>'');
			}
		}
	}
	$groupChildren = Arborescence::getFils(TABLE_GROUPE_QUESTION, $questionGroup, 'code ASC');
	
	if(count($groupChildren) > 0)
	{
		foreach($groupChildren as $groupChild)
		{
			getTableauReponses($groupChild);
		}
	}
}

function starToLi($string)
{
	if(substr($string,0,1) == '*')
	{
		$string = "\n" . $string;
	}
	return str_replace("\n*", "</li><li>", $string);
}

{//Instanciation des variables
	$numeroRubrique = $_POST['numeroRubrique'];
	$textReferentiel = evaReferentialText::getReferentialTextByRubric($numeroRubrique);
	$rubrique = $numeroRubrique;
	$relativiteRubrique = $textReferentiel->objet;
	$shortRelativiteRubrique = $textReferentiel->objetCourt;
	$dateArrete = $textReferentiel->datePremiereRatification ;
	$dateDernierArrete = $textReferentiel->dateDerniereModification;
	$texteSousIntro = $textReferentiel->texteSousIntro . "
*Arrêté du " . mysql2date('d M Y', $dateDernierArrete, true) . " modifiant l'arrêté du " . mysql2date('d M Y', $dateArrete, true) . " relatif aux prescriptions générales applicables aux installations classées soumises à déclaration sous la " . $rubrique . " " . $relativiteRubrique . "";
	
	$dateDeclarationInstallation = $_POST['dateDeclarationInstallation'];
	
	$nomClient = $_POST['nomClient'];
	$site = $_POST['site'];
	$adresse = $_POST['adresse'];
	$effectifInstallation = $_POST['effectif'];
	$dateMiseServiceInstallation = $_POST['dateMiseServiceInstallation'];
	$groupeAuQuelInstallationAppartient = $_POST['groupeAuQuelInstallationAppartient'];
	
	unset($arretesPrefectoraux, $pointsNonTraites);
	$arretesPrefectoraux[] = $_POST['arretesPrefectoraux'];
	$controleurDernierControle = $_POST['controleurDernierControle'];
	$organismeDernierControle = $_POST['organismeDernierControle'];
	$dateDernierControle = $_POST['dateDernierControle'];
	$prenomApprobateur = $_POST['prenomApprobateur'];
	$nomApprobateur = $_POST['nomApprobateur'];
	$prenomInspecteur = $_POST['prenomInspecteur'];
	$nomInspecteur = $_POST['nomInspecteur'];
	$approbateur = ucwords(strtolower($prenomApprobateur)) . ' ' . strtoupper($nomApprobateur);
	$inspecteur = ucwords(strtolower($prenomInspecteur)) . ' ' . strtoupper($nomInspecteur);
	$dateControle = $_POST['dateAudit'];
	
	
	
	$tableElement = $_POST['tableElement'];
	$idElement = $_POST['idElement'];
	$questionGroup = EvaGroupeQuestions::getGroupeQuestionsByName($rubrique);
	getTableauReponses($questionGroup);	
	
	$numeroRapport = str_replace('-', '', $dateControle) . '_' . str_replace(' ', '_', stripAccents($nomClient)) . '_' . str_replace(' ', '_', stripAccents($rubrique));
		
	{//Préparation des variables
		$backgroundColorTPE = "#FFFFFF";
		$backgroundColorPME = "#FFFFFF";
		$backgroundColorGE = "#FFFFFF";
		if($effectifInstallation < 10)
		{
			$backgroundColorTPE = "#FFFFCC";
		}
		if($effectifInstallation >= 10 AND $effectifInstallation < 250)
		{
			$backgroundColorPME = "#FFFFCC";
		}
		if($effectifInstallation >= 250)
		{
			$backgroundColorGE = "#FFFFCC";
		}
		$listeArretePrefectoraux = "Liste des arrêtés préfectoraux relatifs à l'installation concernée :<ul><li>";
		if(count($arretesPrefectoraux) > 0)
		{
			foreach($arretesPrefectoraux as $arretePrefectoral)
			{
				$listeArretePrefectoraux = $listeArretePrefectoraux . "\n" . $arretePrefectoral;
			}
		}
		$listeArretePrefectoraux = $listeArretePrefectoraux . "</li></ul>";
	}
}

class PDFVeille extends TCPDF
{	
	function TitreChapitre($numero,$libelle)
	{
		if($libelle != null)
		{
			$this->SetFont('Times','B',14);
			$this->Cell(0,8,$numero . ".    " . $libelle,0,1,'L');
			$this->Ln(1);
		}
	}
	
	function CorpsChapitre($contenu)
	{
		$this->SetFont('Times','',12);
		$this->writeHTML('<span  style="text-align:justify;">' . $contenu . '</span>', true, 0, true, true);
	}
	
	function AjouterChapitre($numero, $titre, $contenu)
	{
		$this->TitreChapitre($numero,$titre);
		$this->CorpsChapitre($contenu);
		$this->Ln(2);
	}
	
	function AjouterSousTitre($sousTitre)
	{
		$this->SetFont('Times','',12);
		$this->writeHTML('<span  style="text-align:center;">' . strtoupper($sousTitre) . '</span>', true, 0, true, true);
		$this->Ln(2);
	}
}

{//Initialisation du pdf
// create new PDF document
	$pdf = new PDFVeille(PDF_PAGE_ORIENTATION_VEILLE, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator(VERSION_LOGICIEL);
	$pdf->SetAuthor($inspecteur);
	$pdf->SetTitle('Audit de "' . stripAccents($nomClient) . '" portant sur la ' . stripAccents($rubrique));
	$pdf->SetSubject('Resultat de l\'audit');
	$pdf->SetKeywords(ucfirst(stripAccents($rubrique)) . ',' . ucfirst(stripAccents($nomClient)) . ',' . ', Evarisk');

	// set default header data
	$pdf->SetHeaderData(PDF_HEADER_LOGO, 267);

	// set footer font
	$pdf->setFooterFont(Array('times', 'I', 8));

	//set margins
	$pdf->SetMargins(15, 34, 15);
	$pdf->SetHeaderMargin(5);
	$pdf->SetFooterMargin(10);

	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, 10);
}

$pdf->AddPage();

$sousTitre = "Rapport de controle des installations classees soumises a declaration<br />sous la " . $rubrique;
$pdf->AjouterSousTitre($sousTitre);

$numeroChapitre = 1;
{//Chapitre 1 : Introduction
	$pdf->AjouterChapitre($numeroChapitre++,"Introduction", "Ce contrôle est réalisé conformément aux dispositions de l'arrêté modifié du " . mysql2date('d M Y', $dateArrete, true) . " relatif aux prescriptions générales applicables aux installations classées soumises à déclaration sous la " . $rubrique . ".");
}
{//Chapitre 2 : Rappel de règlementation
	$pdf->AjouterChapitre($numeroChapitre++,"Rappel de règlementation", "<ul><li>Arrêté du 17 juin 2005 relatif aux prescriptions générales applicables aux installations classées soumises à déclaration sous la " . $rubrique . " <b>" . $relativiteRubrique . ".</b><br />" . nl2br(starToLi($texteSousIntro)) . "</li></ul>");
}

$pdf->AddPage();
{//Chapitre 3 : Champs de l'inspection
	$displayTotale = "";
	$displayPartielle = "";
	if(isset($pointsNonTraites) AND count($pointsNonTraites) > 0)
	{
		foreach($pointsNonTraites as $point)
		{
			$listePointsNonTraites = $listePointsNonTraites . '<li>' . $point . '</li>';
		}
		$displayTotale = "color:#CCCCCC;";
	}
	else
	{
		$listePointsNonTraites = "";
		$displayPartielle = "color:#CCCCCC;";
	}
	$pdf->AjouterChapitre($numeroChapitre++,"Champs de l'inspection", "Conformément au contrat, la " . $rubrique . " a été inspectée :<br /><br />
	<ul>
		<li style=\"" . $displayTotale . "\">Dans sa totalité</li>
		<li style=\"" . $displayPartielle . "\">Partiellement. Dans ce cas, point de l'arrêté type qui n'ont pas fait l'objet de l'inspection : </li>
		<li><ul>" . $listePointsNonTraites . "
		</ul></li>
	</ul>");
}

$pdf->AddPage();
{//Information sur l'audit
	$backgroundColor = "#EEEEEE";
	for($i=1; $i<=100; $i++)
	{
		$styleTH[$i] = "style=\"width: " . $i . "%; background-color:" . $backgroundColor . ";\"";
	}
	$largeurGauche = 40;
	$largeurDroite = 100 - $largeurGauche;
	$pdf->AjouterChapitre("", null, "<table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">
		<tr>
			<th " . $styleTH[$largeurGauche] . ">Nom du client</th>
			<td style=\"width: " . $largeurDroite . "%\" colspan=\"3\">" . nl2br($nomClient) . "</td>
		</tr>
		<tr>
			<th " . $styleTH[$largeurGauche] . ">Site</th>
			<td style=\"width: " . $largeurDroite . "%\" colspan=\"3\">" . nl2br($site) . "</td>
		</tr>
		<tr>
			<th " . $styleTH[$largeurGauche] . ">Adresse</th>
			<td style=\"width: " . $largeurDroite . "%\" colspan=\"3\">" . nl2br($adresse) . "</td>
		</tr>
		<tr>
			<th " . $styleTH[$largeurGauche] . ">Date de mise en service de l'installation</th>
			<td style=\"width: " . $largeurDroite/4	. "%\">" . mysql2date('d M Y', $dateMiseServiceInstallation, true) . "</td>
			<th " . $styleTH[$largeurDroite/2] . ">Date de déclaration de l'installation</th>
			<td style=\"width: " . $largeurDroite/4	. "%\">" .  mysql2date('d M Y', $dateDeclarationInstallation, true) . "</td>
		</tr>
	</table>");
	$largeurGauche = 20;
	$pdf->AjouterChapitre("", null, "<table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">
		<tr>
			<th " . $styleTH[$largeurGauche] . ">Date du dernier contrôle</th>
			<td style=\"width: " . $largeurGauche . "%\">" .  mysql2date('d M Y', $dateDernierControle, true) . "</td>
			<th " . $styleTH[$largeurGauche] . ">Contrôleur et Organisme : </th>
			<td style=\"width: " . $largeurGauche . "%\">" . nl2br($controleurDernierControle) . "</td>
			<td style=\"width: " . $largeurGauche . "%\">" . nl2br($organismeDernierControle) . "</td>
		</tr>
		<tr>
			<th " . $styleTH[$largeurGauche] . ">Présentation des arrêtés préfectoraux relatifs à l'installation concernée, pris en application de l'article L.512-12 du Code de l'Environement ou de l'article R.512-52</th>
			<td style=\"width: " . (100 - $largeurGauche) . "%\" colspan=\"4\">" . nl2br(starToLi($listeArretePrefectoraux)) . "</td>
		</tr>
		<tr>
			<th " . $styleTH[$largeurGauche] . ">Type d'entreprise</th>
			<td style=\"width: " . $largeurGauche . "%; background-color:" . $backgroundColorTPE . ";\">TPE (moins de 10 salariés)</td>
			<td style=\"width: " . $largeurGauche . "%; background-color:" . $backgroundColorPME . ";\">PME (entre 10 et 250 salariés)</td>
			<td style=\"width: " . $largeurGauche . "%; background-color:" . $backgroundColorGE . ";\">Grande entreprise (plus de 250 salariés)</td>
			<td style=\"width: " . $largeurGauche . "%\">Appartenance à un groupe : " . nl2br($groupeAuQuelInstallationAppartient) . "</td>
		</tr>
	</table>");
	$largeurGauche = 50;
	$pdf->AjouterChapitre("", null, "
	<table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">
		<tr>
			<th style=\"width: 100%; background-color: " . $backgroundColor . "; text-align:center;\" colspan=\"2\">CONTROLE PERIODIQUE</th>
		</tr>
		<tr>
			<td style=\"width: 50%\">Rapport de contrôle n°</td>
			<td style=\"width: 50%\">" . $numeroRapport . "</td>
		</tr>
		<tr>
			<td style=\"width: 50%\">Inspecteur : </td>
			<td style=\"width: 50%\">" . nl2br($inspecteur) . "</td>
		</tr>
		<tr>
			<td style=\"width: 50%\">Date du contrôle : </td>
			<td style=\"width: 50%\">" . mysql2date('d M Y', $dateControle, true) . "</td>
		</tr>
		<tr>
			<td style=\"width: 50%\">Date d'émission du rapport</td>
			<td style=\"width: 50%\">" . mysql2date('d M Y', date("Y-m-d"), true) . "</td>
		</tr>
	</table>");
}

$pdf->AddPage();
{//Résultats de l'audit
	foreach($reponses as $reponse)
	{
		switch($reponse['type'])
		{
			case "groupeQuestion" :
				$tableReponse = $tableReponse . "
					<tr>
						<th style=\"width:60%; background-color:" . $backgroundColor . ";\"><span  style=\"text-align:justify;\">" . $reponse['enonce'] . "</span></th>
						<th style=\"width:10%; background-color:" . $backgroundColor . ";\"></th>
						<th style=\"width:30%; background-color:" . $backgroundColor . ";\"></th>
					</tr>";
				break;
			case "extrait" :
				$tableReponse = $tableReponse . "
					<tr>
						<td style=\"width:60%;\"><span  style=\"text-align:justify;\">" . nl2br($reponse['enonce']) . "</span></td>
						<td style=\"width:10%;\"></td>
						<td style=\"width:30%;\"><span  style=\"text-align:justify;\"></span></td>
					</tr>";
				break;
			case "question" :
				$tableReponse = $tableReponse . "
					<tr>
						<td style=\"width:60%;\"><span  style=\"text-align:justify;\"><b>" . nl2br($reponse['enonce']) . "</b></span></td>
						<td style=\"width:10%;\">" . $reponse['reponse'] . "</td>
						<td style=\"width:30%;\"><span  style=\"text-align:justify;\">" . nl2br($reponse['observation']) . "</span></td>
					</tr>";
				break;
		}
	}
	for($i=1; $i<=100; $i++)
	{
		$headStyle[$i] = "style=\"width: " . $i . "%; background-color:" . $backgroundColor . "; text-align:center; font-size:1.05em; font-weight; bold;\"";
	}
	$pdf->AjouterChapitre("", null, "
	<table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">
		<thead>
			<tr>
				<th " . $headStyle[60] . ">" . ucfirst($rubrique) . " : " . ucfirst($shortRelativiteRubrique) . "</th>
				<th " . $headStyle[10] . ">Conformité</th>
				<th " . $headStyle[30] . ">Observations</th>
			</tr>
		</thead>
		<tbody>
			" . $tableReponse . "
		</tbody>
	</table>");
}

$pdf->AddPage();
{//Chapître 4 : Description des non-conformités
	$tableNonConformites = "";
	if(count($nonConformites))
	{
		foreach($nonConformites as $nonConformite)
		{
			$tableNonConformites = $tableNonConformites . "
				<tr>
					<td style=\"width:30%;\">" . $nonConformite['numero'] . "</td>
					<td style=\"width:70%;\">" . $nonConformite['constat'] . "</td>
				</tr>";
		}
	}
	$pdf->AjouterChapitre($numeroChapitre++, "Description des non-conformités", "
	<table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">
		<thead>
			<tr>
				<th style=\"width:30%; background-color: " . $backgroundColor . "; text-align:center;\"><span style=\"font-size:1.05em;\"><b>N°NC</b></span></th>
				<th style=\"width:70%; background-color: " . $backgroundColor . "; text-align:center;\"><span style=\"font-size:1.05em;\"><b>Non-conformités constatés</b></span><br />Points sur lesquels des mesures correctives ou préventives doivent être mises en oeuvre</th>
			</tr>
		</thead>
		<tbody>
			" . $tableNonConformites . "
		</tbody>
	</table>");
}
$pdf->ln(6);
{//Inspecteur et approbateur
$pdf->AjouterChapitre("", null, "
	<table cellspacing=\"0\" cellpadding=\"3\" border=\"0\">
		<tr>
			<td><div>L'inspecteur : <br />&nbsp;&nbsp;&nbsp;&nbsp;" . $inspecteur . "</div>
	<div>Visa<br /></div>
	<div>Le " . mysql2date('d M Y', date("Y-m-d"), true) . "</div></td>
			<td></td>
			<td></td>
			<td></td>
			<td><div>L'approbateur : <br />&nbsp;&nbsp;&nbsp;&nbsp;" . $approbateur . "</div></td>
		</tr>
	</table>");
}
$pdf->lastPage();

// Save PDF document
$pdf->Output(EVA_RESULTATS_PLUGIN_DIR . 'veilleReglementaire/' . stripAccents($numeroRapport) . '.pdf', 'F');
?>
