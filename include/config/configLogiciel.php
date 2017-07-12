<?php
	DEFINE('STANDALONEVERSION', false);

	DEFINE('AFFICHAGE_PICTO_CATEGORIE', true);
	DEFINE('AFFICHAGE_PICTO_EVAL_RISQUE', true);
	DEFINE('TAILLE_PICTOS_MAX', '25'); // Between 25 and 48 (if px)

	/**
	 * G�olocalisation
	 */
	{
		DEFINE('GEOLOC_OBLIGATOIRE', false);
		DEFINE('ZOOM_SCROLL_MAP', false);
		DEFINE('CLEF_GOOGLE_MAPS', 'ABQIAAAAV24GLgNrS2xLKMCvbtBE4RR2Xs0hXoOUJbteCqQ4WXhB0mslnRTm5crPM7k4wvb2jqtGCRtKiuMsyg');
	}

	/**
	 * Risks Assessment
	 */
	{
		//DEFINE('OPTIONS_AVANCEEES_EVALUATION_RISQUE', 'collapsed'); // 3 possibles values : collapsed / expanded / invisible
	}

	/**
	 * Forms
	 */
	{
		/**
		 * Groups
		 */
		{
			// Displayed fields
			// Needed fields
			{
				DEFINE('DESCRIPTION_GROUPEMENT_OBLIGATOIRE', false);
				DEFINE('ADRESSE_GROUPEMENT_OBLIGATOIRE', true);
				DEFINE('TELEPHONE_GROUPEMENT_OBLIGATOIRE', false);
				DEFINE('EFFECTIF_GROUPEMENT_OBLIGATOIRE', true);
			}
		}
		/**
		 * Working units
		 */
		{
			// Displayed fields
			// Needed fields
			{
				DEFINE('DESCRIPTION_UNITE_TRAVAIL_OBLIGATOIRE', false);
				DEFINE('ADRESSE_UNITE_TRAVAIL_OBLIGATOIRE', true);
				DEFINE('TELEPHONE_UNITE_TRAVAIL_OBLIGATOIRE', false);
				DEFINE('EFFECTIF_UNITE_TRAVAIL_OBLIGATOIRE', true);
			}
		}
		/**
		 * Dangers
		 */
		{
			// Displayed fields
			// Needed fields
			{
				DEFINE('DESCRIPTION_DANGER_OBLIGATOIRE', true);
			}
		}
		/**
		 * Dangers Categories
		 */
		{
			// Displayed fields
			// Needed fields
			{
				DEFINE('DESCRIPTION_CATEGORIE_DANGER_OBLIGATOIRE', true);
			}
		}
		/**
		 * Risks
		 */
		{
			// Displayed fields
			// Needed fields
			{
				DEFINE('DESCRIPTION_RISQUE_OBLIGATOIRE', true);
			}
		}
		/**
		 * Corrective actions
		 */
		{
			// Displayed fields
			// Needed fields
			{
				DEFINE('DESCRIPTION_TACHE_OBLIGATOIRE', false);
			}
		}
		/**
		 * Reglementairy watch
		 */
		{
			// Displayed fields
			{
				DEFINE('AFFICHAGE_VALIDATION_PAR_QUESTION', true);
			}
			// Needed fields
			{
				DEFINE('NOM_APPROBATEUR_VEILLE_OBLIGATOIRE', true);
				DEFINE('PRENOM_APPROBATEUR_VEILLE_OBLIGATOIRE', true);
				DEFINE('NOM_INSPECTEUR_VEILLE_OBLIGATOIRE', true);
				DEFINE('PRENOM_INSPECTEUR_VEILLE_OBLIGATOIRE', true);
				DEFINE('LISTE_ARRETES_VEILLE_OBLIGATOIRE', false);
				DEFINE('INSPECTEUR_DERNIER_CONTROLE_VEILLE_OBLIGATOIRE', true);
				DEFINE('ORGANISME_DERNIER_CONTROLE_VEILLE_OBLIGATOIRE', true);
				DEFINE('DATE_DERNIER_CONTROLE_VEILLE_OBLIGATOIRE', true);
				DEFINE('DATE_DEBUT_AUDIT_VEILLE_OBLIGATOIRE', true);
				DEFINE('DATE_DECLARATION_INSTALLATION_VEILLE_OBLIGATOIRE', true);
				DEFINE('DATE_MISE_SERVICE_INSTALLATION_VEILLE_OBLIGATOIRE', true);
				DEFINE('GRAND_GROUP_VEILLE_OBLIGATOIRE', false);
			}
		}
	}
?>