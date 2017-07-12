<?php
	/** 
	* @author Soci&eacute;t&eacute; Evarisk
	* @version v5.0
	*/

	{	/*	Template generaux	*/
		$premiereDeCouvertureDocumentUnique = 
		'<table summary="" cellpadding="0" cellspacing="0" class="pagePremiereDeCouvDU">
			<tr>
				<td class="infosPrincipalesPremiereDeCouvDU" >
					<div class="titrePrincipalPremiereDeCouvDU" >' . strtoupper(__('Document Unique des risques professionnels','evarisk')) . '</div>
					<div class="sousTitrePremiereDeCouvDU separationElementsTitrePrincipalCouvDU" >' . __('R&eacute;f&eacute;rence n&deg; #NUMREF#', 'evarisk') . '</div>
					<div class="titrePrincipalPremiereDeCouvDU separationElementsTitrePrincipalCouvDU" >#NOMENTREPRISE#</div>
					<div class="sousTitrePremiereDeCouvDU" >' . __('Audit du #DEBUTAUDIT# au #FINAUDIT#', 'evarisk') . '</div>
				</td>
			</tr>
			<tr>
				<td class="infosBasPremiereDeCouvDU">
					<table summary="" cellpadding="0" cellspacing="0" class="tableauInformationsBasPremiereDeCouvDU" >
						<tr>
							<td class="bold">' . __('Emetteur', 'evarisk') . '&nbsp;:</td>
							<td >#NOMPRENOMEMETTEUR#</td>
							<td class="bold">' . __('Date', 'evarisk') . '&nbsp;:</td>
							<td >#DATE#</td>
						</tr>
						<tr>
							<td class="bold">' . __('Destinataire', 'evarisk') . '&nbsp;:</td>
							<td >#NOMPRENOMDESTINATAIRE#</td>
							<td class="bold">
								' . __('T&eacute;l&eacute;phone', 'evarisk') . '&nbsp;:<br/>
								' . __('Mobile', 'evarisk') . '&nbsp;:<br/>
								' . __('Fax', 'evarisk') . '&nbsp;:
							</td>
							<td >
								#TELFIXE#<br/>
								#TELMOBILE#<br/>
								#TELFAX#
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="footerPremiereDeCouvDU">
					EVARISK  -  SARL au capital de 8000 &euro;  -  5 bis rue du pont de Lattes  -  34070 MONTPELLIER  -  T&eacute;l : 09.52.84.08.22 Fax : 01 34 29 61 83
					SIRET : 449 269 208 00020  -  Site Internet : http://www.evarisk.com
				</td>
			</tr>
		</table>';

		$templatePageDocumentUnique =   
		'<table summary="" cellpadding="0" cellspacing="0" class="pageDU" >
			<tr>
				<td class="headerDU" ><img src="' . IMAGE_HEADER_PAGE_DOCUMENT_UNIQUE . '" alt="evarisk_document_unique_header" /></td>
			</tr>
			<tr>
				<td class="contentPageDU" >#CONTENTPAGE#</td>
			</tr>
			<tr>
				<td class="footerDU">
					<table summary="" cellpadding="0" cellspacing="0" class="footer" >
						<tr>
							<td class="titreFooterDU" >' . __('Nom du document', 'evarisk') . '</td>
							<td class="titreFooterDU" >' . __('Auteur', 'evarisk') . '</td>
							<td class="titreFooterDU" >' . __('R&eacute;vision', 'evarisk') . '</td>
							<td class="titreFooterDU" >' . __('Date', 'evarisk') . '</td>
							<td class="titreFooterDU" >' . __('&Eacute;tat', 'evarisk') . '</td>
							<td class="titreFooterDU" >' . __('Page', 'evarisk') . '</td>
						</tr>
						<tr>
							<td class="contenuFooterDU" >#NOMDOCUMENT#</td>
							<td class="contenuFooterDU" >Evarisk</td>
							<td class="contenuFooterDU" >#REVISION#</td>
							<td class="contenuFooterDU" >#DATE#</td>
							<td class="contenuFooterDU" >' . __('Final', 'evarisk') . '</td>
							<td class="contenuFooterDU bold">#PAGE#</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>';

		$methodologieParDefaut = '
* ' . __('Etape 1 : R&eacute;cup&eacute;ration d\'informations', 'evarisk') . '
- ' . __('Visite de l\'usine avec des membres du CHSCT', 'evarisk') . '
- ' . __('Recensement de toutes les machines de l\'usine', 'evarisk') . '
- ' . __('Remise du questionnaire d\'informations', 'evarisk') . '
- ' . __('Traitement des donn&eacute;es et constitution des informations de base (personnel, formation, groupe, atelier, plan, accidents d&eacute;j&agrave; survenus...)', 'evarisk') . '

* ' . __('Etape 2 : Pr&eacute;sentation de la d&eacute;marche', 'evarisk') . '
- ' . __('R&eacute;union avec les membres du CHSCT pour la pr&eacute;sentation du projet (D&eacute;finition d\'une fiche de risques type, validation d\'une fiche standard...)', 'evarisk') . '
* ' . __('Etape 3  : Cr&eacute;ation et validation de la forme du document unique', 'evarisk') . '
- ' . __('Etablissement des fiches de risques d&eacute;finies avec le CHSCT', 'evarisk') . '
- ' . __('Validation des fiches de risques avec le CHSCT', 'evarisk') . '

* ' . __('Etape 4  : R&eacute;alisation de l\'&eacute;tude de risques', 'evarisk') . '
- ' . __('Sensibilisation des op&eacute;rateurs aux risques', 'evarisk') . '
- ' . __('Cr&eacute;ation des unit&eacute;s de travail avec les responsables d\'ateliers et les op&eacute;rateurs', 'evarisk') . '
- ' . __('Evaluations des risques par unit&eacute;s de travail avec les responsables d\'ateliers et les op&eacute;rateurs', 'evarisk') . '

* ' . __('Etape 5', 'evarisk') . '
- ' . __('Traitement et r&eacute;daction du document unique', 'evarisk');

		$sourcesParDefaut=
		'
' . __('Le document de l\'INRS ED 840 pour la sensibilisation aux risques, le document 20020831_critere_evaluation.doc pour les crit&egrave;res d\'&eacute;valuation', 'evarisk') . '</div>';
	}

$sommaireDocumentUnique = 
	'<table summary="duer summary" cellpadding="0" cellspacing="0" class="tabsommaire" >
		<tr>
			<td colspan="2" class="titrePrincipalPageSommaire">' . strtoupper(__('Sommaire', 'evarisk')) . '</td>
		</tr>
		<tr>
			<td class="grandTtitreSommaireDU">1 - ' . __('Chapitre administratif', 'evarisk') . '</td>
			<td class="numeroPageGrandTitreSommaireDU">3</td>
		</tr>
		<tr>
			<td class="sousTitreSommaireDU">1.1 - ' . __('La m&eacute;thodologie', 'evarisk') . '</td>
			<td class="numeroPageSousTitreSommaireDU">3</td>
		</tr>
		<tr>
			<td class="sousTitreSommaireDU">1.2 - ' . __('Les sources utilis&eacute;es', 'evarisk') . '</td>
			<td class="numeroPageSousTitreSommaireDU">3</td>
		</tr>
		<tr>
			<td class="sousTitreSommaireDU">1.3 - ' . __('La localisation de l\'&eacute;tude: #DISPODESPLANS#', 'evarisk') . '</td>
			<td class="numeroPageSousTitreSommaireDU">4</td>
		</tr>
		<tr>
			<td class="grandTtitreSommaireDU">2 - ' . __('Chapitre &eacute;valuation', 'evarisk') . '</td>
			<td class="numeroPageGrandTitreSommaireDU">5</td>
		</tr>
		<tr>
			<td class="sousTitreSommaireDU">2.1 - ' . __('D&eacute;finition d\'un risque', 'evarisk') . '</td>
			<td class="numeroPageSousTitreSommaireDU">5</td>
		</tr>
		<tr>
			<td class="sousTitreSommaireDU">2.2 - ' . __('D&eacute;finition d\'un danger', 'evarisk') . '</td>
			<td class="numeroPageSousTitreSommaireDU">5</td>
		</tr>
		<tr>
			<td class="sousTitreSommaireDU">2.3 - ' . __('Sch&eacute;matique', 'evarisk') . '</td>
			<td class="numeroPageSousTitreSommaireDU">5</td>
		</tr>
		<tr>
			<td class="sousTitreSommaireDU">2.4 - ' . __('La m&eacute;thode d\'&eacute;valuation', 'evarisk') . '</td>
			<td class="numeroPageSousTitreSommaireDU">6</td>
		</tr>
		<tr>
			<td class="sousTitreSommaireDU">2.5 - ' . __('La quantification', 'evarisk') . '</td>
			<td class="numeroPageSousTitreSommaireDU">6</td>
		</tr>
		<tr>
			<td class="sousTitreSommaireDU">2.6 - ' . __('La notion de groupes utilisateurs ou de m&eacute;tiers', 'evarisk') . '</td>
			<td class="numeroPageSousTitreSommaireDU">7</td>
		</tr>
		<tr>
			<td class="titreParagrapheSommaireDU">2.6.1 - ' . __('Les groupes de #NOMENTREPRISE#', 'evarisk') . '</td>
			<td class="numeroPageTitreParagrapheSommaireDU">7</td>
		</tr>
		<tr>
			<td class="sousTitreSommaireDU">2.7 - ' . __('L\'&eacute;tude des unit&eacute;s de travail', 'evarisk') . '</td>
			<td class="numeroPageSousTitreSommaireDU">8</td>
		</tr>
		<tr>
			<td class="sousTitreSommaireDU">2.8 - ' . __('L\'analyse', 'evarisk') . '</td>
			<td class="numeroPageSousTitreSommaireDU">8</td>
		</tr>
		<tr>
			<td class="titreParagrapheSommaireDU">2.8.1 - ' . __('Introduction', 'evarisk') . '</td>
			<td class="numeroPageTitreParagrapheSommaireDU">8</td>
		</tr>
		<tr>
			<td class="titreParagrapheSommaireDU">2.8.2 - ' . __('Le risque par ligne', 'evarisk') . '</td>
			<td class="numeroPageTitreParagrapheSommaireDU">8</td>
		</tr>
		<tr>
			<td class="titreParagrapheSommaireDU">2.8.3 - ' . __('Le risque total', 'evarisk') . '</td>
			<td class="numeroPageTitreParagrapheSommaireDU">8</td>
		</tr>
		<tr>
			<td class="titreParagrapheSommaireDU">2.8.4 - ' . __('Les r&eacute;sultats de la hi&eacute;rarchisation par lignes de risques', 'evarisk') . '</td>
			<td class="numeroPageTitreParagrapheSommaireDU">10</td>
		</tr>
		<tr>
			<td class="titreParagrapheSommaireDU">2.8.5 - ' . __('Les r&eacute;sultats de la hi&eacute;rarchisation par risque total', 'evarisk') . '</td>
			<td class="numeroPageTitreParagrapheSommaireDU">12</td>
		</tr>
		<tr>
			<td class="grandTtitreSommaireDU">3 - ' . __('Le plan d\'action', 'evarisk') . '</td>
			<td class="numeroPageGrandTitreSommaireDU">14</td>
		</tr>
	</table>';
	

$ChapitreAdministratif = 
'<div class="titrePrincipalPageDU">1 - ' . __('Chapitre administratif', 'evarisk') . '</div>
<div class="sousTitrePrincipalPageDU">1.1 - ' . __('La m&eacute;thodologie', 'evarisk') . '</div>
<div class="petitTitreGrasPageDu">' . __('L\'analyse des risques a &eacute;t&eacute; r&eacute;alis&eacute;e entre le #DEBUTAUDIT# et #FINAUDIT# de la fa&ccedil;on suivante', 'evarisk') . '&nbsp;:</div>
'.$methodologieParDefaut.'
<div class="sousTitrePrincipalPageDU">1.2 - ' . __('Les sources utilis&eacute;es', 'evarisk') . '&nbsp;:</div>		
'.$sourcesParDefaut.'';

$localisationRemarques = 
'<div class="sousTitrePrincipalPageDU">1.3 - ' . __('La localisation de l\'&eacute;tude', 'evarisk') . '</div>
<div class="paragraphe1">#PLANS#</div>
<div class="sousTitrePrincipalPageDU">1.4 - ' . __('Remarque importante', 'evarisk') . '</div>
<div class="paragraphe1">#ALERTE#</div>';

$chapitreEvaluation = 
	'<div class="titrePrincipalPageDU">2 - ' . __('Chapitre &eacute;valuation', 'evarisk') . '</div>
		<div class="paragraphe1">' . __('L\'&eacute;valuation des risques a &eacute;t&eacute; men&eacute;e sur les r&egrave;gles ci-apr&egrave;s pour le rep&eacute;rage du danger, la sensibilisation et l\'&eacute;valuation', 'evarisk') . '&nbsp;:</div>
		<div class="sousTitrePrincipalPageDU">2.1 - ' . __('D&eacute;finition d\'un risque', 'evarisk') . '&nbsp;:</div>
		<div class="paragraphe1">' . __('Risque : n. masc Danger, inconv&eacute;nient auquel on s\'expose. Elle ne court aucun risque. Il y a un risque de p&eacute;nurie. Faire quelque chose &agrave; ses risques et p&eacute;rils, en prenant l\'enti&egrave;re responsabilit&eacute; des cons&eacute;quences.<br/>Un risque calcul&eacute;, couru en toute connaissance de cause. Au risque de : en s\'exposant &agrave;. Sauter d\'un mur &eacute;lev&eacute; au risque de se briser une jambe. Action de se risquer (1) ; situation dangereuse. Aimer le risque. Prendre des risques : s\'exposer &agrave; un danger.', 'evarisk') . '</div>
		<div class="paragraphe1">' . __('M&Eacute;D.Facteur de risque : Caract&eacute;ristique physique d\'un individu ou particularit&eacute; de son environnement l\'exposant, avec un risque plus &eacute;lev&eacute;, &agrave; telle ou telle maladie (ex : l\'habitude de fumer exag&eacute;r&eacute;ment constitue un facteur de risque pour la bronchite chronique ou le cancer du poumon ).', 'evarisk') . '</div>
		<div class="sousTitrePrincipalPageDU">2.2 -	' . __('D&eacute;finition d\'un danger', 'evarisk') . '&nbsp;:</div>
		<div class="paragraphe1">' . __('Danger : n. masc. (lat. pop. dominiarium &quot; pouvoir &quot;, de dominus &quot; ma&icirc;tre &quot; ; d\'abord fait d\'&ecirc;tre &agrave; la merci de quelqu\'un ). Ce qui menace la s&eacute;curit&eacute; ou la vie de quelqu\'un ; ce qui risque de compromettre une situation, une entreprise, etc.<br/>Les dangers d\'une exp&eacute;dition en montagne. Les dangers de l\'oisivet&eacute;. Danger de mort, d\'incendie. Notre position est en danger. Sa vie est en dangers: il risque de mourir. Fam. Il n\'y a pas de danger qu\'il vienne : il ne viendra certainement pas.<br/>Par ext. Danger public: individu qui menace la vie des autres par son comportement.', 'evarisk') . '</div>
		<div class="sousTitrePrincipalPageDU">2.3 -	' . __('Sch&eacute;matique', 'evarisk') . '</div>
		<div class=""><img src="' . EVA_IMG_PLUGIN_URL . 'documentUnique/shematisation.gif" alt="schematisationRisque" /></div>';

$methodeEvaluationQuantification = 
	'<div class="paragraphe1">' . __('Quand un individu est en pr&eacute;sence d\'un danger et si ce danger peut engendrer des dommages alors le risque existe.', 'evarisk') . '</div>
	<div class="sousTitrePrincipalPageDU">2.4 -	' . __('La m&eacute;thode d\'&eacute;valuation', 'evarisk') . '</div>
	<div class="paragraphe1">' . __('La m&eacute;thode d\'&eacute;valuation des dangers propos&eacute;e est bas&eacute;e sur le mod&egrave;le de classement de KINNEY <span class="bold">R = G * E * P</span>. Nous rajouterons deux crit&egrave;res &agrave; cette m&eacute;thode afin d\'int&eacute;grer la particularit&eacute; de l\'entreprise. Le premier crit&egrave;re est la formation, le second est le niveau de protection existant.', 'evarisk') . '</div>
	<div class="puces"><span class="bold">R</span> : ' . __('C\'est le r&eacute;sultat il repr&eacute;sente une quantification du risque encouru par les personnes.', 'evarisk') . '</div>
	<div class="puces"><span class="bold">G</span> : ' . __('C\'est la gravit&eacute; du dommage subit par les personnes, si le danger vient en contact avec le ou les personnes. Que vont elles subir ?', 'evarisk') . '</div>
	<div class="puces"><span class="bold">E</span> : ' . __('C\'est l\'exposition au risque, c\'est le nombre de fois par unit&eacute; de temps que la personne sera en pr&eacute;sence du danger.', 'evarisk') . '</div>
	<div class="puces"><span class="bold">P</span> : ' . __('C\'est la probabilit&eacute; de survenu de l\'accident en s\'appuyant le retour d\'exp&eacute;rience, l\'historique. Cet accident s\'est-il d&eacute;j&agrave; produit ?, si oui combien de fois ?', 'evarisk') . '</div>
	<div class="puces"><span class="bold">F</span> : ' . __('C\'est la formation, l\'exp&eacute;rience les sp&eacute;cificit&eacute;s et la connaissance afin d\'occuper un poste sans se blesser.', 'evarisk') . '</div>
	<div class="puces"><span class="bold">P</span> : ' . __('C\'est la (ou les) protection(s) mise(s) en place afin de parer l\'&eacute;ventuel accident.', 'evarisk') . '</div>
	<div class="sousTitrePrincipalPageDU">2.5	' . __('La quantification', 'evarisk') . '</div>
	<div class="imageIllustration"><img src="' . EVA_IMG_PLUGIN_URL . 'documentUnique/tabcoeff.gif" alt="tableauQuantification" /></div>';

$groupesUtilisateurs = 
'<div class="sousTitrePrincipalPageDU">2.6 - ' . __('La notion de groupes utilisateurs ou de m&eacute;tiers', 'evarisk') . '</div>
<div class="paragraphe1">' . __('Afin de conserver la polyvalence et la flexibilit&eacute; des m&eacute;tiers, nous avons attribu&eacute; des groupes utilisateurs. Il est recommand&eacute; que chaque salari&eacute; appartienne &agrave; un groupe donn&eacute; afin de pouvoir travailler dans les unit&eacute;s concern&eacute;es par ce groupe.<br/>Cette notion de groupes est d&eacute;finie par les diff&eacute;rentes sp&eacute;cificit&eacute;s de formations, d\'exp&eacute;riences, de comp&eacute;tences et des particularit&eacute;s du poste.<br/>Il se peut, lorsque certains postes sont nominatifs ou utilis&eacute;s de fa&ccedil;on exceptionnelle par une personne en particulier, que l\'on note directement le nom du salari&eacute; sur la Fiche d\'Evaluation des Risques. Ceci reste des cas &agrave; part.', 'evarisk') . '</div>
<div class="petitTitreGrasPageDu">2.6.1 -	' . __('Les groupes d\'utilisateurs', 'evarisk') . '</div>
#GROUPESUTILISATEURS#<br/><br/>
<div class="petitTitreGrasPageDu">2.6.2 -	' . __('Les groupes de #NOMENTREPRISE# par affectation', 'evarisk') . '</div>
#GROUPESUTILISATEURSAFFECTES#';

$unitesDeTravail = 
	'<div class="sousTitrePrincipalPageDU">2.7 -	' . __('L\'&eacute;tude des unit&eacute;s de travail', 'evarisk') . '</div>
	<div class="paragraphe1">' . __('Voir en annexes les fiches d\'&eacute;valuation par poste de travail.', 'evarisk') . '</div>
	<div class="sousTitrePrincipalPageDU">2.8 -	' . __('L\'analyse', 'evarisk') . '</div>
	<div class="petitTitreGrasPageDu">2.8.1 -	' . __('Introduction', 'evarisk') . '</div>
	<div class="paragraphe2">' . __('Les fiches de risque peuvent &ecirc;tre compos&eacute;es de plusieurs lignes de risques. L\'analyse portera donc sur deux param&egrave;tres : La valeur du risque par ligne et la somme totale des risques pour un poste de travail.', 'evarisk') . '</div>
	<div class="petitTitreGrasPageDu">2.8.2 -	' . __('Le risque par ligne', 'evarisk') . '</div>
	<div class="paragraphe2">' . __('Chaque ligne peut engendrer un risque maximum de 1 024. Ce risque est indiqu&eacute; dans la colonne Q_R.', 'evarisk') . '</div>
	<div class="imageIllustration"><img src="' . EVA_IMG_PLUGIN_URL . 'documentUnique/gendoc_coef.jpg" alt="illustrationNotationRisque" /></div>
	 
	<div class="paragraphe2">' . __('Cette hi&eacute;rarchisation des risques nous permet de d&eacute;finir des priorit&eacute;s qui sont dans le tableau ci-dessous', 'evarisk') . '&nbsp;:</div>
	<table summary="" cellpadding="0" cellspacing="0" class="tableauExplicationNotationRisque" >
		<tr>
			<td class="enTeteTableauExplicationNotationRisque" >' . __('Coefficient de risque Q_R', 'evarisk') . '</td>
			<td class="enTeteTableauExplicationNotationRisque" >' . __('Priorit&eacute;', 'evarisk') . '</td>
		</tr>
		<tr>
			<td class="notationRisqueInacceptable" >' . __(SEUIL_BAS_INACCEPTABLE . ' &agrave; ' . SEUIL_HAUT_INACCEPTABLE, 'evarisk') . '</td>
			<td >' . __('Risque Inacceptable', 'evarisk') . '</td>
		</tr>
		<tr>
			<td class="notationRisqueATraiter" >' . __(SEUIL_BAS_ATRAITER . ' &agrave; ' . SEUIL_HAUT_ATRAITER, 'evarisk') . '</td>
			<td >' . __('Risque &agrave; traiter', 'evarisk') . '</td>
		</tr>
		<tr>
			<td class="notationRisqueAPlanifier" >' . __(SEUIL_BAS_APLANIFIER . ' &agrave; ' . SEUIL_HAUT_APLANIFIER, 'evarisk') . '</td>
			<td >' . __('Risque &agrave; planifier', 'evarisk') . '</td>
		</tr>
		<tr>
			<td class="notationRisqueFaible" >' . __(SEUIL_BAS_FAIBLE . ' &agrave; ' . SEUIL_HAUT_FAIBLE, 'evarisk') . '</td>
			<td >' . __('Risque faible', 'evarisk') . '</td>
		</tr>
	</table>

	<div class="petitTitreGrasPageDu">2.8.3 -	' . __('Le risque total', 'evarisk') . '</div>
	<div class="paragraphe2">' . __('Le risque total est la somme des risques par lignes, il est class&eacute; par ordre d&eacute;croissant (Paragraphe 2.8.4). Nous vous conseillons de r&eacute;duire les risques concernant le premier tiers des fiches de postes.', 'evarisk') . '</div>';

$ficheDEvaluationDesRisques =
	'<div class="paragraphe2">' . __('La r&eacute;duction du coefficient total se fait de deux fa&ccedil;ons', 'evarisk') . '&nbsp;:</div>
	<div class="puces">&#149;	' . __('R&eacute;duction du risque par ligne', 'evarisk') . '</div>
	<div class="puces">&#149;	' . __('R&eacute;duction du nombre de risques dans l\'unit&eacute; de travail', 'evarisk') . '</div>
	<center><img src="' . EVA_IMG_PLUGIN_URL . 'documentUnique/illustrationFER.jpg" alt="illustrationFicheEvaluationDesRisques" /></center>';

$introductionRisquesUnitaires = 
	'<div class="petitTitreGrasPageDu">2.8.4 - ' . __('Les r&eacute;sultats de la hi&eacute;rarchisation par lignes de risques', 'evarisk') . '</div>
	<div class="paragraphe2">' . __('Dans le tableau ci-apr&egrave;s, la colonne de gauche indique le nom de l\'unit&eacute; de travail. Le coefficient de risque unitaire est situ&eacute; dans la deuxi&egrave;me colonne et sa couleur correspond aux crit&egrave;res du paragraphe 2.8.2.', 'evarisk') . '</div>';

$risquesUnitaires = 
	'<table summary="risqsLineSummary#IDTABLE#" cellpadding="0" cellspacing="0" class="widefat post fixed">
		<thead>
			<tr>
				<th>' . __('&Eacute;l&eacute;ment', 'evarisk') . '</th>
				<th>' . __('Quotation', 'evarisk') . '</th>
				<th>' . __('Nom du danger', 'evarisk') . '</th>
				<th>' . __('Commentaires', 'evarisk') . '</th>
			</tr>
		</thead>
		
		<tfoot>
		</tfoot>
		
		<tbody>
			#LIGNESRISQUESUNITAIRES#
		</tbody>
	</table>';

$risquesUnitairesLignes = 
	'<tr>
		<td>#NOMELEMENT#</td>
		<td style="background-color:#QUOTATIONCOLOR#;color:#QUOTATIONTEXTCOLOR#;" >#QUOTATION#</td>
		<td>#NOMDANGER#</td>
		<td>#COMMENTAIRE#</td>
	</tr>';

$introductionRisquesParUnite = 
	'<div class="petitTitreGrasPageDu">2.8.5 - ' . __('Les r&eacute;sultats de la hi&eacute;rarchisation par risque total', 'evarisk') . '</div>
		<div class="paragraphe2">' . __('La colonne de gauche contient le nom de l\'unit&eacute; de travail. Cette unit&eacute; est indiqu&eacute;e en rouge dans le tableau ci-dessous. la colonne commentaires vous permet de d&eacute;buter l\'analyse et d\'esquisser votre plan d\'action.', 'evarisk') . '</div>';

$risquesParUnite = 
	'#RISQUEPARUNITE#';

$planDAction = 
'<div class="titrePrincipalPageDU">3 - ' . __('Le plan d\'action', 'evarisk') . '</div>

<div class="paragraphe1">' . __('Ce paragraphe devra &ecirc;tre r&eacute;dig&eacute; suite &agrave; la mise en place d\'actions correctives visant &agrave; r&eacute;duire les risques ci-dessus. Il fait partie du document unique qui doit &ecirc;tre tenu &agrave; la disposition des organismes et/ou personnes v&eacute;rificateurs. (m&eacute;decin et inspection du travail, CRAM, membre du CHSCT ...)', 'evarisk') . '</div>

<div class="paragraphe1">' . __('Nous vous rappelons que le coefficient de probabilit&eacute; &eacute;tant un constat des accidents arriv&eacute;s, il vous est difficile de le r&eacute;duire. Vous pouvez par contre agir sur un ou plusieurs des autres coefficients qui composent le risque, &agrave; savoir', 'evarisk') . '&nbsp;:</div>

<div class="puces">&#149;	' . __('La gravit&eacute; (qui peut &ecirc;tre r&eacute;duite en minimisant l\'impact du danger sur la personne)', 'evarisk') . '</div>
<div class="puces">&#149;	' . __('L\'exposition (qui peut &ecirc;tre r&eacute;duite en diminuant le temps de contact entre la personne et le danger)', 'evarisk') . '</div>
<div class="puces">&#149;	' . __('La formation (qui formation peut &ecirc;tre am&eacute;lior&eacute;e en sensibilisant et en formant le personnel)', 'evarisk') . '</div>
<div class="puces">&#149;	' . __('La protection (qui peut &ecirc;tre am&eacute;lior&eacute;e en modifiant et / ou en am&eacute;nageant les unit&eacute;s de travail)', 'evarisk') . '</div>';