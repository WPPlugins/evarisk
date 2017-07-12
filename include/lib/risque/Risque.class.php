<?php
/**
 *
 * @author Soci&eacute;t&eacute; Evarisk
 * @version v5.0
 */
include_once(EVA_CONFIG);
include_once(EVA_LIB_PLUGIN_DIR . 'methode/methodeEvaluation.class.php');
include_once(EVA_LIB_PLUGIN_DIR . 'methode/eva_variable.class.php');

class Risque {

	function getScoreRisque( $risque, $method_option = '' ) {
		$date_to_take = $risque[0]->date;
		/*	Add option allowing to modify method behavior */
		if(is_array($method_option)){
			if(isset($method_option['date_to_take']) && ($method_option['date_to_take'] != '')){
				$date_to_take = $method_option['date_to_take'];
			}
		}

		$methode = MethodeEvaluation::getMethod($risque[0]->id_methode);
		$listeVariables = MethodeEvaluation::getVariablesMethode($methode->id, $date_to_take);
		unset($listeIdVariables);
		$listeIdVariables = array();
		foreach($listeVariables as $ordre => $variable){
			$listeIdVariables['"' . $variable->id . '"'][]=$ordre;
		}
		unset($listeValeurs);
		foreach($risque as $ligneRisque){
			if(!empty($listeIdVariables) && !empty($listeIdVariables['"' . $ligneRisque->id_variable . '"']) && is_array($listeIdVariables['"' . $ligneRisque->id_variable . '"'])){
				foreach($listeIdVariables['"' . $ligneRisque->id_variable . '"'] as $ordre){
					if(isset($method_option['value_to_take']) && ($method_option['value_to_take'] != '')){
						$listeValeurs[$ordre] = Eva_variable::getValeurAlternative($ligneRisque->id_variable, $method_option['value_to_take'][$ligneRisque->id_variable], $date_to_take);
					}
					else{
						$listeValeurs[$ordre] = Eva_variable::getValeurAlternative($ligneRisque->id_variable, $ligneRisque->valeur, $date_to_take);
					}
				}
			}
		}

		$listeOperateursComplexe = MethodeEvaluation::getOperateursMethode($methode->id, $date_to_take);
		unset($listeOperateurs);$listeOperateurs = array();
		foreach ( $listeOperateursComplexe as $operateurComplexe ) {
			$listeOperateurs[] = $operateurComplexe->operateur;
		}
		$listeValeursSimples;
		$listeOperateursSimple;

		//r�solution des op�ration de forte priorit� (i.e. * et /)
		$scoreRisque = !empty($listeValeurs[0]) ? $listeValeurs[0] : 0;
		$numeroValeur = 0;
		if($listeOperateurs != null){
			// invariant de boucle : la valeur $listeValeurs[$numeroValeur] est trait�
			foreach($listeOperateurs as $operateur){
				$numeroValeur = $numeroValeur + 1;
				switch($operateur){
					case '*' :
						$scoreRisque = $scoreRisque * $listeValeurs[$numeroValeur];
						break;
					case '/' :
						$scoreRisque = $scoreRisque / $listeValeurs[$numeroValeur];
						break;
					//default <=> op�rateur de faible priorit� (i.e. + et -)
					default :
						$listeValeursSimples[] = $scoreRisque;
						$listeOperateursSimples[] = $operateur;
						$scoreRisque = $listeValeurs[$numeroValeur];
					break;
				}
			}
		}
		//Comme il y a une valeur de plus que d'operateur, on la range � la fin
		$listeValeursSimples[] = $scoreRisque;

		//r�solution du score
		$scoreRisque = $listeValeursSimples[0];
		$numeroValeur = 0;
		if(isset($listeOperateursSimples) && ($listeOperateursSimples != null)){
			// invariant de boucle : la valeur $listeValeursSimples[$numeroValeur] est trait�
			foreach($listeOperateursSimples as $operateur){
				$numeroValeur = $numeroValeur + 1;
				switch($operateur){
					case '+' :
						$scoreRisque = $scoreRisque + $listeValeursSimples[$numeroValeur];
						break;
					case '-' :
						$scoreRisque = $scoreRisque - $listeValeursSimples[$numeroValeur];
						break;
					default : break;
				}
			}
		}

		return $scoreRisque;
	}

	function getEquivalenceEtalon($idMethode, $score, $date=null) {
		global $wpdb;

		if($date==null){
			$date=current_time('mysql', 0);
		}

		$score = digirisk_tools::IsValid_Variable($score);
		$idMethode = digirisk_tools::IsValid_Variable($idMethode);
		$resultat = $wpdb->get_row("SELECT tableEquivalenceEtalon1.id_valeur_etalon equivalenceEtalon
			FROM " . TABLE_EQUIVALENCE_ETALON . " tableEquivalenceEtalon1
			WHERE tableEquivalenceEtalon1.valeurMaxMethode >= " . $score . "
			AND tableEquivalenceEtalon1.id_methode = " . $idMethode . "
			AND tableEquivalenceEtalon1.date <= '" . $date . "'
                        AND tableEquivalenceEtalon1.Status = 'Valid'
			AND NOT EXISTS
			(
				SELECT *
				FROM " . TABLE_EQUIVALENCE_ETALON . " tableEquivalenceEtalon2
				WHERE tableEquivalenceEtalon2.valeurMaxMethode >= " . $score . "
				AND tableEquivalenceEtalon2.id_methode = " . $idMethode . "
				AND tableEquivalenceEtalon2.date <= '" . $date . "'
				AND tableEquivalenceEtalon2.date > tableEquivalenceEtalon1.date
				AND tableEquivalenceEtalon2.id_valeur_etalon < tableEquivalenceEtalon1.id_valeur_etalon
			)");

		return !empty($resultat->equivalenceEtalon) ? $resultat->equivalenceEtalon : '0';
	}

	function getSeuil($quotation) {
		global $wpdb;

		$quotation = digirisk_tools::IsValid_Variable($quotation);
		$resultat = $wpdb->get_row( "
			SELECT tableValeurEtalon1.niveauSeuil niveauSeuil
			FROM " . TABLE_VALEUR_ETALON . " tableValeurEtalon1
			WHERE tableValeurEtalon1.valeur <= " . $quotation . "
			AND tableValeurEtalon1.Status = 'Valid'
			AND tableValeurEtalon1.niveauSeuil != 'NULL'
			AND NOT EXISTS
			(
				SELECT *
				FROM " . TABLE_VALEUR_ETALON . " tableValeurEtalon2
				WHERE tableValeurEtalon2.valeur >= " . $quotation . "
				AND tableValeurEtalon2.Status = 'Valid'
				AND tableValeurEtalon2.niveauSeuil != 'NULL'
				AND tableValeurEtalon2.valeur < tableValeurEtalon1.valeur
			)
			ORDER BY tableValeurEtalon1.niveauSeuil DESC
			LIMIT 1"
		);
		$niveauSeuil = !empty($resultat->niveauSeuil) ? (int)$resultat->niveauSeuil : 0;
		return $niveauSeuil;
	}

	function getNiveauRisque($niveauSeuil){
		switch($niveauSeuil){
			case 0:
				$niveauRisque = __(EVA_RISQUE_SEUIL_0_NOM, 'evarisk');
				break;
			case 1:
				$niveauRisque = __(EVA_RISQUE_SEUIL_1_NOM, 'evarisk');
				break;
			case 2:
				$niveauRisque = __(EVA_RISQUE_SEUIL_2_NOM, 'evarisk');
				break;
			case 3:
				$niveauRisque = __(EVA_RISQUE_SEUIL_3_NOM, 'evarisk');
				break;
			case 4:
				$niveauRisque = __(EVA_RISQUE_SEUIL_4_NOM, 'evarisk');
				break;
			case 5:
				$niveauRisque = __(EVA_RISQUE_SEUIL_5_NOM, 'evarisk');
				break;
			case 6:
				$niveauRisque = __(EVA_RISQUE_SEUIL_6_NOM, 'evarisk');
				break;
		}
		return $niveauRisque;
	}

	function getRisque($id){
		global $wpdb;
		$id = digirisk_tools::IsValid_Variable($id);

		$query = $wpdb->prepare("SELECT tableRisque.*,
				tableAvoirValeur.date date, tableAvoirValeur.Status status, tableAvoirValeur.id_risque id_risque, tableAvoirValeur.id_variable id_variable, tableAvoirValeur.valeur valeur, tableAvoirValeur.id_evaluation,
				tableDanger.nom nomDanger, tableDanger.id idDanger, tableDanger.description descriptionDanger, tableDanger.id_categorie idCategorie
			FROM " . TABLE_RISQUE . " tableRisque
				LEFT JOIN " . TABLE_AVOIR_VALEUR . " tableAvoirValeur ON (tableAvoirValeur.Status = 'Valid' AND tableAvoirValeur.id_risque=tableRisque.id),
				" . TABLE_DANGER . " tableDanger
			WHERE tableRisque.id=" . ($id) . "
				AND tableRisque.id_danger=tableDanger.id", "");
		$resultat = $wpdb->get_results($query);

		return $resultat;
	}

	function getRisques($nomTableElement = 'all', $idTableElement = 'all', $status='all', $where = '1', $order='tableRisque.id ASC', $evaluation_status = "'Valid'") {
		global $wpdb;
		$where = digirisk_tools::IsValid_Variable($where);
		$order = digirisk_tools::IsValid_Variable($order);
		if( $status=='all' ){
			$status = '1';
		}
		else{
			$status = "tableRisque.Status = '" . $status . "'";
		}

		$tableElement = $idElement = "1";
		if($nomTableElement != 'all'){
			$tableElement = "tableRisque.nomTableElement='" . ($nomTableElement) . "' ";
		}
		if($idTableElement != 'all'){
			$idElement = "tableRisque.id_element = '" . ($idTableElement) . "' ";
		}

		$query = $wpdb->prepare(
			"SELECT tableRisque.*,
				tableAvoirValeur.id_risque id_risque, tableAvoirValeur.id_variable id_variable, tableAvoirValeur.valeur valeur, tableAvoirValeur.id_evaluation, tableAvoirValeur.Status AS evaluation_status, DATE_FORMAT(tableAvoirValeur.date, %s) AS evaluation_date, tableAvoirValeur.commentaire AS histo_com, tableAvoirValeur.date AS unformatted_evaluation_date,
				tableDanger.nom nomDanger, tableDanger.id idDanger, tableDanger.description descriptionDanger, tableDanger.id_categorie idCategorie
			FROM " . TABLE_RISQUE . " tableRisque
				LEFT JOIN " . TABLE_AVOIR_VALEUR . " tableAvoirValeur ON (tableAvoirValeur.id_risque = tableRisque.id AND tableAvoirValeur.Status IN (" . $evaluation_status . ")),
				" . TABLE_DANGER . " tableDanger
			WHERE " . $tableElement . "
				AND " . $idElement . "
				AND " . $status . "
				AND " . $where . "
				AND tableRisque.id_danger = tableDanger.id
			ORDER BY " . $order, '%Y-%m-%d %r');
		$resultat = $wpdb->get_results( $query );

		return $resultat;
	}

	function getriskLinkToTask($idRisque, $id_tache, $beforeAfter){
		global $wpdb;

		$query =
			"SELECT
				R.id id, R.id_danger id_danger, R.id_methode id_methode, R.commentaire commentaire, R.date date,
				VR.id_risque id_risque, VR.id_variable id_variable, VR.valeur valeur,
				D.nom nomDanger, D.id idDanger, D.description descriptionDanger, D.id_categorie idCategorie
			FROM " . TABLE_RISQUE . " R
				INNER JOIN " . TABLE_DANGER . " D ON (D.id = R.id_danger)
				INNER JOIN " . TABLE_AVOIR_VALEUR . " VR ON (VR.id_risque = R.id)
				INNER JOIN " . TABLE_LIAISON_TACHE_ELEMENT . " LTE ON ((LTE.id_element = VR.id_evaluation) AND (LTE.table_element = '" . TABLE_AVOIR_VALEUR . "') AND (LTE.wasLinked = '" . $beforeAfter . "') AND (LTE.id_tache = '" . $id_tache . "'))
			WHERE VR.id_risque = '" . ($idRisque) . "'
				AND VR.Status != 'Deleted' ";
		$listeVariableAvecValeur = $wpdb->get_results( $query );

		return $listeVariableAvecValeur;
	}

	function getNombreRisques($nomTableElement, $idElement, $status='all', $where = '1', $order='id ASC'){
		global $wpdb;
		$where = digirisk_tools::IsValid_Variable($where);
		$order = digirisk_tools::IsValid_Variable($order);
		if($status=='all'){
			$status = '1';
		}
		else{
			$status = "Status = '" . $status . "'";
		}
		$resultat = $wpdb->get_row( "SELECT count(id) nombreRisques FROM " . TABLE_RISQUE . " WHERE id_element=" . ($idElement) . " AND nomTableElement='" . ($nomTableElement) . "' AND " . $status . " AND " . ($where) . " ORDER BY " . ($order));
		return $resultat->nombreRisques;
	}

	function saveNewRisk ($idRisque, $idDanger, $idMethode, $tableElement, $idElement, $variables, $description, $histo, $date_debut, $date_fin, $date_evaluation = '', $risk_status = '') {
		global $wpdb,
			   $current_user;

		$idDanger = digirisk_tools::IsValid_Variable($idDanger);
		$idMethode = digirisk_tools::IsValid_Variable($idMethode);
		$tableElement = digirisk_tools::IsValid_Variable($tableElement);
		$idElement = digirisk_tools::IsValid_Variable($idElement);
		$description = digirisk_tools::IsValid_Variable($description);
		$description = str_replace("[retourALaLigne]","\n", $description);
		$description = str_replace("�","'", $description);
		$histoStatus = 'Valid';

		if ($idRisque == '') { /**	Add a new risk	*/
			$new_risque = $wpdb->insert(TABLE_RISQUE, array('id_danger' => $idDanger, 'id_methode' => $idMethode, 'id_element' => $idElement, 'nomTableElement' => $tableElement, 'commentaire' => $description, 'date' => current_time('mysql', 0), 'Status' => 'Valid', 'dateDebutRisque' => $date_debut, 'dateFinRisque' => $date_fin));
			$idRisque = $wpdb->insert_id;
			if ($new_risque && !empty($idRisque) && is_int($idRisque)) {
				echo '
<script type="text/javascript" >
	actionMessageShow("#message' . TABLE_RISQUE . '", "' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Le risque a bien &eacute;t&eacute; ajout&eacute;', 'evarisk') . '</strong></p>') . '");
	setTimeout(\'actionMessageHide("#message' . TABLE_RISQUE . '")\',7500);
</script>';
			}
			else {
				echo '
<script type="text/javascript" >
	actionMessageShow("#message' . TABLE_RISQUE . '", "' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('Le risque n\'a pas pu &ecirc;tre ajout&eacute;', 'evarisk') . '</strong></p>') . '");
	setTimeout(\'actionMessageHide("#message' . TABLE_RISQUE . '")\',7500);
</script>';
			}
		}
		else {/**	Update an existing risk	*/
			$query = $wpdb->prepare( "SELECT dateDebutRisque, dateFinRisque FROM " . TABLE_RISQUE . " WHERE id = %d", $idRisque );
			$current_date = $wpdb->get_row( $query );
			$wpdb->insert( TABLE_RISQUE_HISTO, array('id_risque' => $idRisque, 'date' => current_time( 'mysql', 0), 'field' => 'dateDebutRisque', 'value' => $current_date->dateDebutRisque) );
			if ( !empty($current_date->dateFinRisque) && ($current_date->dateFinRisque != '0000-00-00 00:00:00') ) {
				$wpdb->insert( TABLE_RISQUE_HISTO, array('id_risque' => $idRisque, 'date' => current_time( 'mysql', 0), 'field' => 'dateFinRisque', 'value' => $current_date->dateFinRisque) );
			}

			$wpdb->update(TABLE_RISQUE, array('id_danger'=>$idDanger, 'id_methode' => $idMethode, 'id_element' => $idElement, 'nomTableElement' => $tableElement, 'commentaire' => $description, 'date' => current_time('mysql', 0), 'Status' => 'Valid', 'dateDebutRisque' => $date_debut, 'dateFinRisque' => $date_fin), array('id' => $idRisque));

			if ($histo != 'false') {
				$wpdb->update(TABLE_AVOIR_VALEUR, array('Status'=>'Moderated'), array('id_risque'=>$idRisque, 'Status'=>'Valid'));
			}
			else {
				/*	Check if the current risk evaluation is linked to a correctiv action	*/
				$task_link= 0;
				$query = $wpdb->prepare("
	SELECT LINKTASKELMT.id_tache AS task_id, LINKTASKELMT.id as link_id
	FROM " . TABLE_AVOIR_VALEUR . " AS AVALEUR
		LEFT JOIN " . TABLE_LIAISON_TACHE_ELEMENT . " AS LINKTASKELMT ON ((LINKTASKELMT.id_element = AVALEUR.id_evaluation) AND (LINKTASKELMT.table_element = %s) AND (LINKTASKELMT.wasLinked = %s))
		INNER JOIN " . TABLE_TACHE . " AS TASK ON ((TASK.id = LINKTASKELMT.id_tache) AND (TASK.tableProvenance = %s) AND (TASK.idProvenance = %d))
	WHERE AVALEUR.id_risque = %d
		AND AVALEUR.Status = %s",
	TABLE_AVOIR_VALEUR, 'after', TABLE_RISQUE, $idRisque, $idRisque, 'Valid');
				$task_link = $wpdb->get_row($query);
				if(is_object($task_link)){
					$wpdb->update(TABLE_LIAISON_TACHE_ELEMENT, array('status' => 'deleted'), array('id' => $task_link->link_id));
				}

				$wpdb->update(TABLE_AVOIR_VALEUR, array('Status'=>'Deleted'), array('id_risque'=>$idRisque, 'Status'=>'Valid'));
			}
		}

		$sql = "SELECT MAX(id_evaluation) + 1 AS newId FROM " . TABLE_AVOIR_VALEUR;
		$newId = $wpdb->get_row($sql);
		if((INT)$newId->newId <= 0)$newId->newId = 1;
		$r_nb = 1;
		foreach($variables as $idVariable => $valeurVariable){
			if( !empty($idVariable) && ($valeurVariable != 'undefined') ) {
				$comment = null;
				if ( $r_nb == 1 ) {
					$comment = $description;
				}
				$idVariable = digirisk_tools::IsValid_Variable($idVariable);
				$valeurVariable = digirisk_tools::IsValid_Variable($valeurVariable);
				$wpdb->insert(TABLE_AVOIR_VALEUR, array('id_risque'=>$idRisque, 'id_evaluation'=>$newId->newId, 'id_variable'=>$idVariable, 'valeur'=>$valeurVariable, 'idEvaluateur'=>$current_user->ID, 'date' => /* (!empty($date_evaluation) ? $date_evaluation :  */current_time('mysql', 0)/* ) */, 'Status'=>$histoStatus, 'commentaire'=>$comment));
				$r_nb++;
			}
		}

		$options = get_option('digirisk_options');
		$current_risk = Risque::getRisque( $idRisque );
		$idMethode = $current_risk[0]->id_methode;
		$score = Risque::getScoreRisque( $current_risk );
		$quotation = Risque::getEquivalenceEtalon($idMethode, $score, $current_risk[0]->date);
		if ( (!empty($risk_status) && ($risk_status == 'true')) || (!empty($date_fin) && ($date_fin != '0000-00-00 00:00') && (strtolower($options['digi_risk_close_state_end_date_filled']) == strtolower(__('Oui', 'evarisk')))) || (($quotation == 0) && (strtolower($options['digi_risk_close_state_cotation_null']) == strtolower(__('Oui', 'evarisk')))) ) {
			$wpdb->update(TABLE_RISQUE, array('risk_status' => 'closed', 'dateFinRisque' => (!empty($date_fin) && ($date_fin != '0000-00-00 00:00')) ? $date_fin : substr( current_time('mysql', 0), 0, -3 )), array('id' => $idRisque));
		}

		if ( !empty($description) ) {
			$wpdb->insert(TABLE_ACTIVITE_SUIVI, array('id' => null, 'status' => 'Valid', 'date' => current_time('mysql', 0), 'id_user' => $current_user->ID, 'id_element' => $newId->newId, 'table_element' => TABLE_AVOIR_VALEUR, 'commentaire' => $description, 'date_ajout' => current_time('mysql', 0), 'export' => 'yes'));
		}

		if(($histo == 'false') && (is_object($task_link))){/**	Check if the last evaluation is linked to a task. That means that we don't have the choice to show or not risk evaluation into statistics	*/
			evaTask::liaisonTacheElement(TABLE_AVOIR_VALEUR, $newId->newId, $task_link->task_id, 'after');
		}

		return $idRisque;
	}

	function deleteRisk($idRisque, $tableElement, $idElement){
		global $wpdb;
		$status = 'error';

		$delete_element = $wpdb->update(TABLE_RISQUE, array('Status' => 'Deleted'), array('id' => $idRisque, 'id_element' => $idElement, 'nomTableElement' => $tableElement));
		if($delete_element !== false){
			$status = 'ok';
		}

		return $status;
	}

	/**
	*	Get a sum of the different risqs for an element (with the subelements)
	*
	*	@param mixed $table The element type we want to get the risqs sum for
	*	@param integer $elementId The element identifier we want to ger the risqs sum for
	*
	*	@return array $info An array with the sum of risqs
	*/
	function getSommeRisque($table, $elementId){
		$temp = Risque::getRisques($table, $elementId, "Valid");
		if ($temp != null) {
			foreach ($temp as $risque) {
				$risques['"' . $risque->id . "'"][] = $risque;
			}
		}
		$sumR = 0;
		if (isset($risques) && ($risques != null)) {
			foreach ($risques as $risque) {
				$idMethode = $risque[0]->id_methode;
				$score = Risque::getScoreRisque($risque);
				$sumR += Risque::getEquivalenceEtalon($idMethode, $score, $risque[0]->date);
			}
		}
		$scoreRisqueGroupement = $sumR;
		$info['value'] = $scoreRisqueGroupement;
		$info['class'] = '';

		return $info;
	}

	/**
	 *
	 */
	function getTableQuotationRisque($tableElement, $idElement, $unique_id = '', $option = ''){
		switch($tableElement)
		{
			case TABLE_RISQUE :
				$risque = Risque::getRisque($idElement);
				{//Creation de la table
					unset($tableauVariables);
					foreach($risque as $ligneRisque){
						$valeurVariables[$ligneRisque->id_variable] = $ligneRisque->valeur;
					}
					$methode = MethodeEvaluation::getMethod($risque[0]->id_methode);
					$listeVariables = MethodeEvaluation::getVariablesMethode($methode->id, $risque[0]->date);
					foreach($listeVariables as $ordre => $variable){
						$tableauVariables[] = array('id' => $variable->id, 'nom' => $variable->nom, 'valeur' => $valeurVariables[$variable->id]);
					}

					unset($titres,$classes, $idLignes, $lignesDeValeurs);
					$idLignes = null;
					$idTable = 'tableDemandeAction_' . $unique_id . $tableElement . $idElement;
					$titres[] = __("Id.", 'evarisk');
					$titres[] = __("Cotation", 'evarisk');
					$titres[] = ucfirst(strtolower(sprintf(__("nom %s", 'evarisk'), __("du danger", 'evarisk'))));
					$titres[] = ucfirst(strtolower(sprintf(__("commentaire %s", 'evarisk'), __("sur le risque", 'evarisk'))));
					$classes[] = 'columnRId';
					$classes[] = 'columnQuotation';
					$classes[] = 'columnNomDanger';
					$classes[] = 'columnCommentaireRisque';

					$idligne = 'risque-' . $risque[0]->id;
					$idLignes[] = $idligne;

					$idMethode = $risque[0]->id_methode;
					$score = Risque::getScoreRisque($risque, $option);
					$quotation = Risque::getEquivalenceEtalon($idMethode, $score, $risque[0]->date);
					$niveauSeuil = Risque::getSeuil($quotation);

					unset($ligneDeValeurs);
					$ligneDeValeurs[] = array('value' => ELEMENT_IDENTIFIER_R . $risque[0]->id, 'class' => '');
					$ligneDeValeurs[] = array('value' => $quotation, 'class' => 'Seuil_' . $niveauSeuil);
					$ligneDeValeurs[] = array('value' => $risque[0]->nomDanger, 'class' => '');
					if(!empty($option['description_to_take'])){
						$ligneDeValeurs[] = array('value' => nl2br($option['description_to_take']), 'class' => '');
					}
					else{
						$ligneDeValeurs[] = array('value' => nl2br($risque[0]->commentaire), 'class' => '');
					}
					foreach($tableauVariables as $variable){
						$titres[] = substr($variable['nom'], 0, 3) . '.';
						$classes[] = 'columnVariableRisque';
						if(!empty($option['value_to_take']) && ($unique_id == 'new_quote_control')){
							$var_class = 'digirisk_risk_level_control_action_more';
							if($option['value_to_take'][$variable['id']] < $variable['valeur']){
								$var_class = 'digirisk_risk_level_control_action_less';
							}
							elseif($option['value_to_take'][$variable['id']] === $variable['valeur']){
								$var_class = 'digirisk_risk_level_control_action_equal';
							}
							$ligneDeValeurs[] = array('value' => $option['value_to_take'][$variable['id']], 'class' => $var_class);
						}
						else{
							$ligneDeValeurs[] = array('value' => $variable['valeur'], 'class' => '');
						}
					}
					$lignesDeValeurs[] = $ligneDeValeurs;

					$lignesDeValeurs = (isset($lignesDeValeurs))?$lignesDeValeurs:null;
					$script = '<script type="text/javascript">
						digirisk(document).ready(function(){
							digirisk("#' . $idTable . ' tfoot").remove();
						});
					</script>';

					$output = EvaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script);
					if($unique_id == 'new_quote_control'){
						$output .= '<ul class="alignright digirisk_control_action_legend" ><li><a href="#" >' . __('Valeurs identiques', 'evarisk') . '</a><span class="digirisk_risk_level_control_action_equal" >X</span></li><li><a href="#" >' . __('Valeurs inf&eacute;rieures', 'evarisk') . '</a><span class="digirisk_risk_level_control_action_less" >X</span></li><li><a href="#" >' . __('Valeurs sup&eacute;rieures', 'evarisk') . '</a><span class="digirisk_risk_level_control_action_more" >X</span></li></ul><br class="clear" />';
					}

					return $output;
				}
			break;
			default:
				return 'Pensez &agrave; <b>ajouter</b> le <b>cas ' . $tableElement . '</b> dans le <b>switch</b> ligne <b>' . __LINE__ . '</b> du fichier "' . dirname(__FILE__) . '\<b>' . basename(__FILE__) . '</b>"<br />';
			break;
		}
	}

	/**
	 *	Get a table with information about a given risk level
	 *
	 *	@param string $tableElement The element tye we want to get information about
	 *	@param integer $idElement The element identifier we want to get information about
	 *	@param object $actionCorrective The correctiv action we want to output for the given element
	 *	@param mixed $idDiv An unique id allowing to spot the good container for action
	 *	@param array $association_time An array containing the different moment we want to get correctiv action information
	 *
	 *	@return string The html output with information about risk level
	 */
	function get_risk_level_summary_by_moment($tableElement, $idElement, $actionCorrective, $idDiv, $association_time = array()){
		unset($titres, $classes, $idLignes, $lignesDeValeurs);
		$idLignes = null;
		$idTable = 'tableSuiviAction' . $tableElement . $idElement;
		$titres[] = __(" ", 'evarisk');
		$titres[] = __("Quotation", 'evarisk');
		$classes[] = '';
		$classes[] = 'columnQuotation';

		if(is_array($association_time) && (count($association_time) > 0)){
			$i = 0;
			foreach($association_time as $time){
					$idLignes[] = 'risque-' . $actionCorrective->getId() . '-' . $time;
					/*	Get the name for the association time	*/
					switch($time){
						case 'before':
						{
							$moment_name = __('Avant', 'evarisk');
						}
						break;
						case 'after':
						{
							$moment_name = __('Apr&eacute;s', 'evarisk');
						}
						break;
						case 'demand':
						{
							$moment_name = __('Demande', 'evarisk');
						}
						break;
						default:
						{
							$moment_name = __('Actuelle', 'evarisk');
						}
						break;
					}

					if(in_array($time, array('before', 'after', 'demand'))){
						unset($risque);$risque = array();
						$risque = Risque::getriskLinkToTask($actionCorrective->getIdFrom(), $actionCorrective->getId(), $time);
					}
					else{
						$risque = Risque::getRisque($actionCorrective->getIdFrom());
					}

					unset($tableauVariables);$tableauVariables = array();
					foreach($risque as $ligneRisque){
						$valeurVariables[$ligneRisque->id_variable] = $ligneRisque->valeur;
					}

					$quotation=$niveauSeuil=null;
					if(!empty($risque)){
						$methode = MethodeEvaluation::getMethod($risque[0]->id_methode);
						$listeVariables = MethodeEvaluation::getVariablesMethode($methode->id, $risque[0]->date);
						foreach($listeVariables as $ordre => $variable){
							$tableauVariables[] = array('nom' => $variable->nom, 'valeur' => $valeurVariables[$variable->id]);
						}

						$idMethode = $risque[0]->id_methode;
						$score = Risque::getScoreRisque($risque);

						$quotation = Risque::getEquivalenceEtalon($idMethode, $score, $risque[0]->date);
					}
					$niveauSeuil = Risque::getSeuil($quotation);

					unset($ligneDeValeurs);
					$ligneDeValeurs[] = array('value' => $moment_name, 'class' => '');

					if(!is_array($tableauVariables) || (count($tableauVariables) <= 0)){
						$ligneDeValeurs[] = array('value' => __('Non &eacute;valu&eacute;', 'evarisk'), 'class' => '');
					}
					else{
						$ligneDeValeurs[] = array('value' => $quotation, 'class' => 'risque' . $niveauSeuil . 'Text');
					}

					if(is_array($tableauVariables) && (count($tableauVariables) > 0)){
						foreach($tableauVariables as $variable){
							if((count($titres) <= 2) || (count($titres) != (count($tableauVariables) + 2))){
								$titres[] = substr($variable['nom'], 0, 3) . '.';
								$classes[] = 'columnVariableRisque';
							}
							$ligneDeValeurs[] = array('value' => $variable['valeur'], 'class' => '');
						}
					}

					$lignesDeValeurs[] = $ligneDeValeurs;
					$i++;

			}
		}

		$lignesDeValeurs = (isset($lignesDeValeurs)) ? $lignesDeValeurs : null;
		$script = '<script type="text/javascript">
			digirisk(document).ready(function(){
				digirisk("#' . $idTable . ' tfoot").remove();
			});
		</script>';

		return '<div id="' . $idDiv . '-affichage-quotation" class="affichageAction" style="margin:6px 0px;" >' . EvaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script) . '</div>';
	}

	/**
	 *
	 */
	function getRisqueAssociePhoto($idPicture){
		global $wpdb;
		$output = '';

		$query = $wpdb->prepare(
			"SELECT RISQUE.*
			FROM " . TABLE_PHOTO_LIAISON . " AS PICTURE_LINK
				INNER JOIN " . TABLE_RISQUE . " AS RISQUE ON ((RISQUE.id = PICTURE_LINK.idElement) AND (RISQUE.Status = 'Valid'))
			WHERE PICTURE_LINK.idPhoto = '%d'
				AND PICTURE_LINK.tableElement = '%s'
				AND PICTURE_LINK.status = 'valid' ",
			$idPicture, TABLE_RISQUE
		);
		$queryResult = $wpdb->get_results($query);
		if (count($queryResult) > 0) {
			$riskArray = array();
			$i = 0;
			foreach ($queryResult as $risk) {
				$risque = Risque::getRisque($risk->id);
				$score = Risque::getEquivalenceEtalon($risk->id_methode, Risque::getScoreRisque($risque), $risk->date);

				$last_comment_output = '';
				$query = $wpdb->prepare("SELECT date_ajout, commentaire, date FROM " . TABLE_ACTIVITE_SUIVI . " WHERE status = 'valid' AND table_element = %s AND id_element IN (SELECT id_evaluation FROM " . TABLE_AVOIR_VALEUR . " WHERE id_risque = %d) ORDER BY date_ajout DESC", TABLE_AVOIR_VALEUR, $risque[0]->id);
				$last_comments = $wpdb->get_results($query);
				if ( !empty($last_comments) ) {
					$first_comment = $other_comments = '';
					$i = 1;
					foreach ( $last_comments as $last_comment ) {
						if ( $i == 1 ) {
							$first_comment = '<span class="digi_risk_comment_date" >' . mysql2date('d F Y', $last_comment->date_ajout, true) . '</span> : ' . nl2br( stripslashes( $last_comment->commentaire )) . '<br/>';
						}
						else {
							$other_comments .= '<span class="digi_risk_comment_date" >' . mysql2date('d F Y', ($last_comment->date_ajout != '0000-00-00 00:00:00' ? $last_comment->date_ajout : $last_comment->date), true) . '</span> : ' . nl2br( stripslashes($last_comment->commentaire ) ) . '<br/>';
						}
						$i++;
					}
					$last_comment_output = $first_comment .(!empty($other_comments) ? '<div class="other_comment_display" ><div class="alignright pointer" ><span class="ui-icon alignleft comment_display_state_icon" style="background-position: 0px -192px;" ></span>' . __('Voir les autres commentaires', 'evarisk') . '</div><div class="clear hide close other_comment_container">' . $other_comments . '</div></div>' : '');
				}

				$output .= '
						<div class="clear" id="loadRiskId' . $risk->id . '" >
							<span class="alignleft riskAssociatedToPicture" >-&nbsp;' . evaDanger::getDanger($risk->id_danger)->nom . '&nbsp;(' . $score . ')&nbsp;:&nbsp;' . $last_comment_output . '</span>
							<span id="deleteRiskId' . $risk->id . '" class="ui-icon deleteLinkBetweenRiskAndPicture alignright" title="' . __('Supprimer cette liaison', 'evarisk') . '" >&nbsp;</span>
						</div>';
			}

			$script = '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		digirisk(".riskAssociatedToPicture").unbind("click");
		digirisk(".riskAssociatedToPicture").click(function(){
			digirisk("#formRisque").html(digirisk("#loadingImg").html());
			tabChange("#divFormRisque", "#ongletAjouterRisque");
			digirisk("#formRisque").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true",	"table":"' . TABLE_RISQUE . '", "act":"load", "idRisque": digirisk(this).parent("div").attr("id").replace("loadRiskId", ""), "idElement":"' . $queryResult[0]->id_element . '", "tableElement":"' . $queryResult[0]->nomTableElement . '"});
		});
		digirisk(".deleteLinkBetweenRiskAndPicture").unbind("click");
		digirisk(".deleteLinkBetweenRiskAndPicture").click(function(){
			if(confirm(digi_html_accent_for_js("' . __('&Ecirc;tes vous sur de vouloir supprimer cette liaison?', 'evarisk') . '"))){
				var curent_pict_id = jQuery(this).closest(".riskAssociatedToPictureContainer").attr("id").replace("riskAssociatedToPicturepicture_", "").replace("_", "");
				jQuery(this).closest(".riskAssociatedToPictureContainer").html( digirisk("#loading_round_pic div").html() );
				digirisk("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
					"post":"true",
					"table":"' . TABLE_RISQUE . '",
					"tableElement":"' . TABLE_RISQUE . '",
					"idElement":digirisk(this).attr("id").replace("deleteRiskId", ""),
					"act":"unAssociatePicture",
					"idPicture":curent_pict_id
				});
			}
		});
		digirisk(".riskAssociatedToPicture").draggable({
			start: function(event, ui){
				draggedObjectFather = digirisk(this).parent().closest("div").attr("id").replace("riskAssociatedToPicture", "");
			}
		});
	});
</script>';
			$output = '<fieldset class="clear" style="white-space:normal;margin:3px 12px;width:90%;" ><legend ><span style="text-decoration:underline;" >' . __('Risques associ&eacute;s &agrave; la photo', 'evarisk') . '</span>&nbsp;:&nbsp;<span class="bold" >' . __('Nom danger(Score) : Commentaire', 'evarisk') . '</span></legend>' . $output . '</fieldset>' . $script;
		}
		else
		{
			$output = '<div style="margin:3px 12px;" >' . __('Aucun risque n\'est associ&eacute; &agrave; cette photo', 'evarisk') . '</div>';
		}

		return $output;
	}

	/**
	 *
	 */
	function getRisqueNonAssociePhoto($tableElement, $idElement) {
		global $wpdb;
		$output = '';

		$query = $wpdb->prepare(
			"SELECT RISQUE.*
			FROM " . TABLE_RISQUE . " AS RISQUE
			WHERE RISQUE.status = 'Valid'
				AND RISQUE.id_element = '%s'
				AND RISQUE.nomTableElement = '%s'
				AND RISQUE.id NOT IN (
					SELECT idElement
					FROM " . TABLE_PHOTO_LIAISON . "
					WHERE tableElement = '%s'
						AND status = 'valid'
				)",
			$idElement, $tableElement, TABLE_RISQUE
		);
		$unaffected_risks = $wpdb->get_results( $query );
		if ( !empty($unaffected_risks) ) {
			$riskArray = array();
			$i = 0;
			foreach ($unaffected_risks as $risk) {
				$risque = Risque::getRisque($risk->id);
				$score = Risque::getEquivalenceEtalon($risk->id_methode, Risque::getScoreRisque($risque), $risk->date);

				$output .= '<div class="clear riskAssociatedToPicture" id="loadRiskId' . $risk->id . '" style="cursor:move;" ><span class="ui-icon alignleft" style="background-position: 0 -80px;" ></span>&nbsp;' . evaDanger::getDanger($risk->id_danger)->nom . '&nbsp;(' . $score . ')&nbsp;:&nbsp;' . $risk->commentaire . '</div>';
			}

			$script = '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery(".riskAssociatedToPicture").draggable({
			revert : function(event, ui) {
				jQuery(this).data("uiDraggable").originalPosition = {
					top : 0,
					left : 0
				};
				return !event;
            }
		});
	});
</script>';
			$output = '
<fieldset class="clear" style="white-space:normal; padding:0px 0px 9px 6px; margin:3px 12px; width:250%; border: 2px solid #DDDDDD;" >
	<legend >' . __('Risques non associ&eacute;s &agrave; une photo', 'evarisk') . '</span></legend>
	<div id="riskToAssociate">
		' . $output . '
	</div>
</fieldset>' . $script;
		}

		return $output;
	}

/*	START OF RISK STAT	*/
	function getRiskRange($rangeType){
		$completeRiskList = Risque::getRisques('all', 'all', 'Valid', '1', 'tableRisque.id ASC');
		if($completeRiskList != null)
		{
			foreach($completeRiskList as $risque)
			{
				$risques["'" . $risque->id . "'"][] = $risque;
			}
		}
		$lowerRisk = $higherRisk = 0;
		if(isset($risques) && ($risques != null))
		{
			foreach($risques as $risque)
			{
				$idMethode = $risque[0]->id_methode;
				$score = Risque::getScoreRisque($risque);
				$riskLevel = Risque::getEquivalenceEtalon($idMethode, $score, $risque[0]->date);
				if(($riskLevel > $higherRisk))
				{
					$higherRisk = $riskLevel;
				}
				if(($riskLevel < $lowerRisk))
				{
					$lowerRisk = $riskLevel;
				}
			}
		}

		$riskRange['HIGHER_RISK'] = $higherRisk;
		$riskRange['LOWER_RISK'] = $lowerRisk;

		return $riskRange;
	}

	function dashBoardStats(){
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT
				(
					SELECT COUNT(id)
					FROM " . TABLE_RISQUE . "
					WHERE Status = 'Valid'
				) AS TOTAL_RISK_NUMBER
			", ""
		);

		return $wpdb->get_row($query);
	}

	function digi_ajax_risk_stats() {
		$userDashboardStats = (array) Risque::dashBoardStats();
		$userDashboardStats = array_merge($userDashboardStats, Risque::getRiskRange('higher'));

		$idTable = 'riskDashBordStats';
		$titres = array( __('Stat index', 'evarisk'), __('Statistique', 'evarisk'), __('Valeur', 'evarisk') );
		if ( count($userDashboardStats) > 0 ) {
			foreach($userDashboardStats as $statName => $statValue) {
				switch($statName) {
					case 'TOTAL_RISK_NUMBER':
						$statId = 1;
						$statName = __('Nombre total de risque', 'evarisk');
					break;
					case 'HIGHER_RISK':
						$statId = 2;
						$statName = __('Quotation la plus &eacute;lev&eacute;e', 'evarisk');
					break;
					case 'LOWER_RISK':
						$statId = 3;
						$statName = __('Quotation la plus basse', 'evarisk');
					break;
				}
				unset($valeurs);
				$valeurs[] = array('value' => $statId);
				$valeurs[] = array('value' => $statName);
				$valeurs[] = array('value' => $statValue);
				$lignesDeValeurs[] = $valeurs;
				$idLignes[] = 'riskDashboardStat' . $statId;
				$outputDatas = true;
			}
		}
		else {
			unset($valeurs);
			$valeurs[] = array('value'=>'');
			$valeurs[] = array('value'=>__('Aucun r&eacute;sultat trouv&eacute;', 'evarisk'));
			$valeurs[] = array('value'=>'');
			$lignesDeValeurs[] = $valeurs;
			$idLignes[] = 'riskDashboardStatEmpty';
			$outputDatas = false;
		}

		$classes = array('','','');
		$tableOptions = '';

		if ( $outputDatas ) {
			$script =
'<script type="text/javascript">
	digirisk(document).ready(function() {
		digirisk("#' . $idTable . '").dataTable({
			"bInfo": false,
			"bPaginate": false,
	        "bLengthChange": false,
	        "bFilter": false,
	        "bSort": false,
			"aoColumns":	[
				{"bVisible": false},
				null,
				null
			]
			' . $tableOptions . '
		});
		digirisk("#' . $idTable . '").children("thead").remove();
		digirisk("#' . $idTable . '").children("tfoot").remove();
		digirisk("#' . $idTable . '_wrapper").removeClass("dataTables_wrapper");
	});
</script>';
			echo evaDisplayDesign::getTable($idTable, $titres, $lignesDeValeurs, $classes, $idLignes, $script);
		}
		die();
	}
/*	END OF RISK STAT	*/

	/**
	*	Update a risk level and make a link between the risk evaluation and the correctiv action
	*
	*	@param integer $idRisque The risk identifier we are changing level for
	*	@param mixed $task_id Could be a task identifier Or an array containing several task
	*	@param array $rating Define the moment of the link between a task and a risk evaluation
	*
	*	@return void
	*/
	function update_risk_rating_link_with_task($idRisque, $task_id, $rating){
		global $wpdb;

		if(is_array($rating) && in_array('before', $rating)){	/*	Make the link between a corrective action and a risk evaluation	*/
			$query =
				$wpdb->prepare(
					"SELECT id_evaluation
					FROM " . TABLE_AVOIR_VALEUR . "
					WHERE id_risque = %d
						AND Status = %s
					ORDER BY id DESC
					LIMIT 1",
					$idRisque, 'Valid'
				);
			$evaluation = $wpdb->get_row($query);
			evaTask::liaisonTacheElement(TABLE_AVOIR_VALEUR, $evaluation->id_evaluation, $task_id, 'before');
		}

		$risque = Risque::getRisque($idRisque);

		/*	Save risk new level	*/
		$idDanger = digirisk_tools::IsValid_Variable($_REQUEST['idDanger'], $risque[0]->id_danger);
		$idMethode = digirisk_tools::IsValid_Variable($_REQUEST['idMethode'], $risque[0]->id_methode);
		$tableElement = digirisk_tools::IsValid_Variable($_REQUEST['tableElement'], $risque[0]->nomTableElement);
		$idElement = digirisk_tools::IsValid_Variable($_REQUEST['idElement'], $risque[0]->id_element);
		$variables = $_REQUEST['variables'];
		$description = digirisk_tools::IsValid_Variable($_REQUEST['description_risque'], $risque[0]->commentaire);
		$date_debut = digirisk_tools::IsValid_Variable($_REQUEST['risk_start_date'], $risque[0]->dateDebutRisque);
		$date_fin = digirisk_tools::IsValid_Variable($_REQUEST['risk_end_date'], $risque[0]->dateFinRisque);
		$date_evaluation = digirisk_tools::IsValid_Variable($_REQUEST['risk_end_date'], $risque[0]->date);
		$histo = 'true';
		$idRisque = Risque::saveNewRisk($idRisque, $idDanger, $idMethode, $tableElement, $idElement, $variables, $description, $histo, $date_debut, $date_fin, $date_evaluation);

		if(is_array($rating) && in_array('after', $rating)){	/*	Make the link between a corrective action and a risk evaluation	*/
			$query =
				$wpdb->prepare(
					"SELECT id_evaluation
					FROM " . TABLE_AVOIR_VALEUR . "
					WHERE id_risque = '%d'
						AND Status = 'Valid'
					ORDER BY id DESC
					LIMIT 1",
					$idRisque
				);
			$evaluation = $wpdb->get_row($query);
			evaTask::liaisonTacheElement(TABLE_AVOIR_VALEUR, $evaluation->id_evaluation, $task_id, 'after');
		}
	}

}