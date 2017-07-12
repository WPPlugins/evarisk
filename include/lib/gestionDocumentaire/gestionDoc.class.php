<?php
/**
* Plugin document manager
*
*	Define the different method to manage the different document into the plugin
* @author Evarisk <dev@evarisk.com>
* @version 5.0
* @package Digirisk
* @subpackage librairies
*/


/**
 * Define the different method to manage the different document into the plugin
 * @package Digirisk
 * @subpackage librairies
 */
class eva_gestionDoc {

	/**
	* Return an upload form
	*
	* @param string $tableElement Table of the element which is the photo relative to.
	* @param int $idElement Identifier in the table of the element which is the photo relative to.
	* @param string $repertoireDestination Repository of the uploaded file.
	* @param string $idUpload HTML div identifier.
	* @param string $allowedExtensions Allowed extensions for the upload (ex:"['jpeg','png']"). All extensions is written "[]".
	* @param bool $multiple Can the user upload multiple files in one time ?
	* @param string $actionUpload The url of the file call when the user press on upload button.
	*
	* @return string The upload form with eventually a thumbnail.
	*/
	function getFormulaireUpload($table, $tableElement, $idElement, $repertoireDestination, $idUpload, $allowedExtensions, $multiple, $actionUpload, $texteBoutton = ''){
		_e( 'Cette fonctionnalité a été momentanément désactivée. VEuillez nous excuser pour la gêne occasionnée', 'evarisk' );
	}

	/**
	*	Save a new document into database
	*
	*	@param string $table Used to determine the category fo the new document
	*	@param string $tableElement The type of the element the document will be associated to
	*	@param integer $idElement The identifier of the element the document will be associated to
	*	@param string $fichier The complete path of the file
	*
	*	@return string $result The result of the saving
	*/
	function saveNewDoc($table, $tableElement, $idElement, $fichier) {
		global $wpdb,
			   $current_user;
		$result = 'error';

		/*	Determination of the file category	*/
		switch ($table) {
			case TABLE_DUER:
				$categorie = 'document_unique';
			break;

			case TABLE_FP:
				$categorie = 'fiche_de_poste';
				if($tableElement == TABLE_GROUPEMENT . '_FGP') {
					$categorie = 'fiche_de_groupement';
				}
				else if ( ($tableElement == TABLE_UNITE_TRAVAIL . '_FEP') || ($tableElement == TABLE_GROUPEMENT . '_FEP') ) {
					$categorie = 'fiche_exposition_penibilite';
				}
			break;

			case TABLE_ACTIVITE:
				$categorie = $table;
				if($tableElement == TABLE_ACTIVITE . '_FA') {
					$categorie = 'fiche_action';
				}
			break;

			default:
				$categorie = $table;
			break;
		}

		/*	Determination of the file name	*/
		$nomDocument = basename($fichier);

		/*	Determination of the file directory	*/
		$cheminDocument = str_replace(str_replace('\\', '/', EVA_GENERATED_DOC_DIR), '', dirname($fichier)) . '/';

		$doc_params = array('status' => 'valid', 'dateCreation' => current_time('mysql', 0), 'idCreateur' => $current_user->ID, 'id_element' => $idElement, 'table_element' => $tableElement, 'categorie' => $categorie, 'nom' => $nomDocument, 'chemin' => $cheminDocument);
		$new_sheet = $wpdb->insert(TABLE_GED_DOCUMENTS, $doc_params);
		if ( $new_sheet === false ) {
			$result = 'error';
		}
		else {
			switch ( $tableElement ) {
				case TABLE_ACTIVITE:
				case TABLE_TACHE:
					/*	Log modification on element and notify user if user subscribe	*/
					digirisk_user_notification::log_element_modification($tableElement, $idElement, 'doc_add', '', $new_sheet);
				break;
			}
			$result = 'ok';
		}

		return $result;
	}

	/**
	*	Create a new document from another existing one
	*
	*	@param string $tableElement The type of the element to associate the duplicated document to
	*	@param integer $idElement The identifier of the element to associate the duplicated document to
	*	@param integer $idDocument The identifier of the document to duplicate
	*
	*	@return string $result The result of the duplication
	*/
	function duplicate_document() {
		global $wpdb;
		$result = '';

		$tableElement = $_POST['element_type'];
		$idElement = $_POST['element_id'];
		$idDocument = $_POST['document_to_duplicate'];

		/*	Preparing the filed list to duplicate the document	*/
		$query = $wpdb->prepare(
			"SHOW COLUMNS FROM " . TABLE_GED_DOCUMENTS, ""
		);
		$columnList = $wpdb->get_results($query);
		$columns = "  ";
		foreach ( $columnList as $column ) {
			if ( $column->Key != 'PRI' ) {
				$columns .= $column->Field . ", ";
			}
		}

		$columns = substr($columns, 0, -2);
		if (trim($columns) != "") {
			$query = $wpdb->prepare(
				"INSERT INTO " . TABLE_GED_DOCUMENTS . "
				SELECT '', " . $columns . " FROM " . TABLE_GED_DOCUMENTS . " WHERE id = '%s' ",
				$idDocument
			);
			if($wpdb->query($query)) {
				$update_affectation = $wpdb->update( TABLE_GED_DOCUMENTS , array('table_element' => $tableElement, 'id_element' => $idElement), array('id' => $wpdb->insert_id) );
				if ( $update_affectation !== false ) {
					$result[] = $tableElement;
					$result[] = $idElement;
					$result[] = $_POST['document_type'];
				}
			}
		}

		echo json_encode( $result );
		die();
	}

	/**
	*	Return the associated document list for a given element
	*
	*	@param string $tableElement The type of the element we want to get the document list for
	*	@param integer $idElement The identifier of the element we want to get the document list for
	*	@param string $category optionnal The category of document to get
	*	@param string $order optionnal Allow to specify in which order the document will be returned
	*
	*	@return array|object $documentList Return the document list for the selected element in case that there are document associated
	*/
	function getDocumentList($tableElement, $idElement, $category = "", $order = "nom ASC") {
		global $wpdb;
		$documentList = array();

		$morequery = "";
		if($category != "")
		{
			$morequery = "
				AND categorie = '" . $category . "' ";
		}

		$query = $wpdb->prepare(
			"SELECT * FROM
			" . TABLE_GED_DOCUMENTS . "
			WHERE table_element = %s
				AND id_element = %d
				AND status = 'valid'
				" . $morequery . "
			ORDER BY " . $order,
			$tableElement, $idElement);

		$documentList = $wpdb->get_results($query);

		return $documentList;
	}

	/**
	 *	Get the last document generated for a given element
	 *
	 *	@param mixed $tableElement The element type we want to get the last document for
	 *	@param integer $idElement The element identifier we want to get the lat document for
	 *
	 *	@return mixed $lastDocument An object with all information about the last document
	 */
	function getGeneratedDocument($tableElement, $idElement, $type = 'last', $id = '', $document_type = '', $affected_user = '') {
		global $wpdb;
		$lastDocument = array();

		$queryOrder = "";
		$query_order_for_zip = "";
		$query_params = array($idElement, $tableElement);
		$query_params_for_zip = array($idElement, $tableElement);

		if ( !empty($id) ) {
			$queryOrder .= "
				AND id = %d";
			$query_params[] = $id;
		}

		if ( !empty($document_type) ) {
			$queryOrder .= "
				AND document_type = %s";
			$query_params[] = $document_type;

			$query_order_for_zip .= "
				AND categorie = %s";
			$query_params_for_zip[] = $document_type;
		}

		if ( !empty($affected_user) ) {
			$queryOrder .= "
				AND affected_user = %s";
			$query_params[] = $affected_user;
		}

		switch ($type) {
			case 'last':
				$queryOrder .= "
				ORDER BY id DESC
			LIMIT 1";
				$query_order_for_zip .= "
				ORDER BY id DESC
			LIMIT 1";
			break;
			case 'list':
				$queryOrder .= "
				ORDER BY creation_date DESC, revision DESC";
				$query_order_for_zip .= "
				ORDER BY dateCreation DESC";
			break;
		}

		$query = $wpdb->prepare(
				"SELECT *
			FROM " . TABLE_FP . "
			WHERE id_element = %d
				AND table_element = %s " . $queryOrder,
				$query_params
		);
		$lastDocument = $wpdb->get_results($query);

		if ( count($lastDocument) > 0 ) {

			switch ( $document_type ) {
				case 'fiche_de_groupement':
					$document_prefix = 'ficheDeGroupement';
				break;
				case 'fiche_de_poste':
					$document_prefix = 'ficheDePoste';
				break;
				case 'listing_des_risques':
					$document_prefix = 'listingRisque';
				break;
				case 'fiche_exposition_penibilite':
					$document_prefix = 'ficheDeRisques';
					$sub_query = $wpdb->prepare( " SELECT * FROM " . TABLE_GED_DOCUMENTS . " WHERE id_element = %d AND table_element = %s " . $query_order_for_zip, $query_params_for_zip );
					$last_document_zip = $wpdb->get_results( $sub_query );
				break;
				case 'user_global_export':
					$document_prefix = ELEMENT_IDENTIFIER_GUE;
				break;
			}

			switch ($type) {
				case 'last':
					$outputListeDocumentUnique = $wpdb->get_row($query);
					break;
				case 'list':
						$outputListeDocumentUnique = '';
						$listeParDate = array();
						foreach ($lastDocument as $index => $document) {
							$dateElement = explode(' ', $document->creation_date);
							if ($document->name == '') {
								$document->name = str_replace('-', '', $dateElement[0]) . '_' . $document_prefix . '_' . digirisk_tools::slugify_noaccent(str_replace(' ', '_', $document->societyName)) . '_V' . $document->revisionDUER;
							}
							if ( $document_type == 'fiche_exposition_penibilite') {
								$listeParDate[$dateElement[0]][$document->affected_user][$document->id]['name'] = $document->name;
								$listeParDate[$dateElement[0]][$document->affected_user][$document->id]['user_info'] = unserialize($document->users);
								$listeParDate[$dateElement[0]][$document->affected_user][$document->id]['fileName'] = $document->document_final_dir . $document->name . '_V' . $document->revision;
								$listeParDate[$dateElement[0]][$document->affected_user][$document->id]['revision'] = 'V' . $document->revision;
							}
							else if ( $document_type == 'user_global_export') {
								$listeParDate[$dateElement[0]][$document->id]['name'] = $document->name;
								$listeParDate[$dateElement[0]][$document->id]['fileName'] = $document->document_final_dir . '/' . $document->name;
								$listeParDate[$dateElement[0]][$document->id]['revision'] = 'V' . $document->revision;
							}
							else {
								$listeParDate[$dateElement[0]][$document->id]['name'] = $document->name;
								$listeParDate[$dateElement[0]][$document->id]['fileName'] = $document->name . '_V' . $document->revision;
								$listeParDate[$dateElement[0]][$document->id]['revision'] = 'V' . $document->revision;
							}
						}

						$list_of_grouped_document = array();
						if ( !empty( $last_document_zip ) ) {
							foreach ( $last_document_zip as $index => $document) {
								$dateElement = explode(' ', $document->dateCreation);
								if ( $document_type == 'fiche_exposition_penibilite') {
									$list_of_grouped_document[$dateElement[0]][$document->id]['name'] = $document->nom;
									$list_of_grouped_document[$dateElement[0]][$document->id]['fileName'] = $document->chemin . $document->nom;
								}
							}
						}

						if ( count($listeParDate) > 0 ) {
							$outputListeDocumentUnique .=
							'<table summary="" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;" >';
							foreach ($listeParDate as $date => $listeDUDate) {
								$outputListeDocumentUnique .= '
									<tr >
										<td colspan="3" style="text-decoration:underline;font-weight:bold;" >Le ' . mysql2date('d F Y', $date, true) . '</td>
									</tr>';
								$sub_result = '';
								if ( empty($affected_user) && !empty($list_of_grouped_document) && !empty($list_of_grouped_document[$date]) ) {
									foreach ( $list_of_grouped_document[$date] as $doc_id => $doc_infos ) {
										/**	Check if an odt file exist to be downloaded	*/
										if ( is_file(EVA_GENERATED_DOC_DIR . $doc_infos['fileName']) ) {
												$sub_result .= '
									<tr>
										<td>&nbsp;&nbsp;&nbsp;- (' . ELEMENT_IDENTIFIER_ZFEP . $doc_id . ')&nbsp;&nbsp;' . $doc_infos['name'] . '</td>
										<td><a href="' . EVA_GENERATED_DOC_URL . $doc_infos['fileName'] . '" target="evaFEPZip" >Zip</a></td>
									</tr>';
										}
									}
								}
								if ( !empty($sub_result) ) {
									$outputListeDocumentUnique .= '
									<tr>
										<td>- &nbsp;&nbsp;' . __('Regroupement des fiches de postes', 'evarisk') . '</td>
									</tr>' . $sub_result;
								}

								foreach ($listeDUDate as $index => $DUER) {
									if ( $document_type == 'fiche_exposition_penibilite') {
										$user_lastname = $user_firstname = '';
										$sub_result = '';
										foreach ( $DUER as $doc_id => $user_FEP ) {
											$user_firstname = !empty($user_FEP['user_info']['user_firstname']) ? $user_FEP['user_info']['user_firstname'] : '';
											$user_lastname = !empty($user_FEP['user_info']['user_lastname']) ? $user_FEP['user_info']['user_lastname'] : '';

											/**	Check if an odt file exist to be downloaded	*/
											$odtFile = $document_prefix . '/' .  $user_FEP['fileName'] . '.odt';
											if ( is_file(EVA_RESULTATS_PLUGIN_DIR . $odtFile) ) {
												$sub_result .= '
									<tr>
										<td>&nbsp;&nbsp;&nbsp;- (' . ELEMENT_IDENTIFIER_FEP . $doc_id . ')&nbsp;&nbsp;' . $user_FEP['name'] . '_' . $user_FEP['revision'] . '</td>
										<td><a href="' . EVA_RESULTATS_PLUGIN_URL . $odtFile . '" target="evaFPOdt" >Odt</a></td>
									</tr>';
											}
										}
										if ( !empty($sub_result) ) {
											$outputListeDocumentUnique .= '
									<tr>
										<td>- ' . ELEMENT_IDENTIFIER_U . $index . '&nbsp;&nbsp;' . $user_firstname . ' ' . $user_lastname . '</td>
									</tr>' . $sub_result;
										}
									}
									else if ( $document_type == 'user_global_export') {
										/**	Check if an odt file exist to be downloaded	*/
										$odtFile = $DUER['fileName'] . '.csv';
										if ( is_file(EVA_RESULTATS_PLUGIN_DIR . $odtFile) ) {
											$outputListeDocumentUnique .= '
									<tr>
										<td>&nbsp;&nbsp;&nbsp;- (' . ELEMENT_IDENTIFIER_GUE . $index . ')&nbsp;&nbsp;' . $DUER['name'] . '</td>
										<td><a href="' . EVA_RESULTATS_PLUGIN_URL . $odtFile . '" target="evaFPOdt" >Csv</a></td>
									</tr>';
										}
									}
									else {
										/**	Check if an odt file exist to be downloaded	*/
										$odtFile = $document_prefix . '/' . $tableElement . '/' . $idElement . '/' . $DUER['fileName'] . '.odt';
										if ( is_file(EVA_RESULTATS_PLUGIN_DIR . $odtFile) ) {
											$outputListeDocumentUnique .= '
									<tr>
										<td>&nbsp;&nbsp;&nbsp;- (' . ELEMENT_IDENTIFIER_FP . $index . ')&nbsp;&nbsp;' . $DUER['name'] . '_' . $DUER['revision'] . '</td>
										<td><a href="' . EVA_RESULTATS_PLUGIN_URL . $odtFile . '" target="evaFPOdt" >Odt</a></td>
									</tr>';
										}
									}
									$outputListeDocumentUnique .= '
									<tr ><td>&nbsp;</td></tr>';
								}
							}
							$outputListeDocumentUnique .= '
							</table>';
						}
					break;
			}
		}
		else {
			$outputListeDocumentUnique = '<div class="noResultInBox" >' . __('Aucune fiche n\'a &eacute;t&eacute; g&eacute;n&eacute;r&eacute;e pour le moment', 'evarisk') . '</div>';
		}

		return $outputListeDocumentUnique;
	}

	/**
	*	Return the complete list of existing document. It is possible to specify the category of the document list we want
	*
	*	@param string $category optionnal The category we want to get the document list for
	*	@param string $morequery optionnal An additionnal possibilities to filter the document list
	*	@param string $order optionnal Allow to specify in which order the document will be returned
	*
	*	@return array|object $documentList Return the document list for the selected element in case that there are document associated
	*/
	function getCompleteDocumentList( $category = "", $morequery = "", $order = "nom ASC", $tableElement = '', $idElement = '' ) {
		global $wpdb;
		$documentList = array();;

		if ( $category != "" ) {
			$morequery .= "
				AND categorie = '" . $category . "' ";
		}

		$query = $wpdb->prepare(
			"SELECT * FROM
			" . TABLE_GED_DOCUMENTS . "
			WHERE status = 'valid'
				" . $morequery . "
				AND table_element != 'all'
			GROUP BY chemin, nom
			ORDER BY " . $order,
			$tableElement, $idElement);
		$documentList = $wpdb->get_results($query);

		return $documentList;
	}

	/**
	*	Get the path for a given document
	*
	*	@param integer $idDocument The document identifier we want to get the path for
	*
	*	@return string $path The path of the selected document
	*/
	function getDocumentPath($idDocument)
	{
		global $wpdb;
		$path = '';

		$query = $wpdb->prepare(
			"SELECT chemin, nom
			FROM " . TABLE_GED_DOCUMENTS . "
			WHERE id = %d",
		$idDocument);
		$pathComponents = $wpdb->get_row($query);

		$path = $pathComponents->chemin . $pathComponents->nom;

		return $path;
	}

	/**
	*	Get the default document for a given category
	*
	*	@param string $category The document category we want to get the default document for
	*
	*	@return integer $idDocument The identifier of the default document for the category
	*/
	function getDefaultDocument($category)
	{
		global $wpdb;
		$idDocument = 0;

		$query = $wpdb->prepare(
			"SELECT id
			FROM " . TABLE_GED_DOCUMENTS . "
			WHERE parDefaut = 'oui'
				AND status = 'valid'
				AND categorie = %s
			ORDER BY dateCreation
			LIMIT 1", $category
		);
		$documentDefaultId = $wpdb->get_row($query);
		if(!empty($documentDefaultId))
			$idDocument = $documentDefaultId->id;

		return $idDocument;
	}

	/**
	 *	Save a new "work unit sheet" in database
	 *
	 *	@param mixed $tableElement The element type we want to save a new document for
	 *	@param integer $idElement The element identifier we want to save a new document for
	 *	@param array $informations An array with all information to create the new document. Those informations come from the form
	 *
	 *	@return array $status An array with the response status, if it's ok or not
	 */
	function save_element_sheet($tableElement, $idElement, $informations) {
		global $wpdb;
		$status = array();

		require_once(EVA_LIB_PLUGIN_DIR . 'photo/evaPhoto.class.php');

		$tableElement = digirisk_tools::IsValid_Variable($tableElement);
		$idElement = digirisk_tools::IsValid_Variable($idElement);

		$query_params = array($tableElement, $idElement);
		if (!empty($informations['document_type'])) {
			$query_extra_params = "
				AND document_type = %s";
			$query_params[] = $informations['document_type'];
		}

		/** Retrieve last revision for the document to generate	*/
		$revision = '';
		$query = $wpdb->prepare(
			"SELECT max(revision) AS lastRevision
			FROM " . TABLE_FP . "
			WHERE table_element = %s
				AND id_element = %d " . $query_extra_params,
		$query_params);
		$revision = $wpdb->get_row($query);
		$revisionDocument = $revision->lastRevision + 1;

		/** Generate a reference for the document	*/
		switch ($tableElement) {
			case TABLE_GROUPEMENT:
			case TABLE_GROUPEMENT . '_RS':
				$element = ELEMENT_IDENTIFIER_FGP;
				if ($informations['document_type'] == 'listing_des_risques') {
					$element = ELEMENT_IDENTIFIER_FSGP;
				}
				$current_element = EvaGroupement::getGroupement($idElement);
				$element_identifier = ELEMENT_IDENTIFIER_GP;
				break;
			case TABLE_UNITE_TRAVAIL:
			case TABLE_UNITE_TRAVAIL . '_RS':
				$element = ELEMENT_IDENTIFIER_UT;
				$status_to_get = "'valid', 'deleted', 'moderated' ";
				if ($informations['document_type'] == 'listing_des_risques') {
					$element = ELEMENT_IDENTIFIER_FSUT;
					$status_to_get = "'valid'";
				}
				elseif ($informations['document_type'] == 'fiche_exposition_penibilite') {
					$element = ELEMENT_IDENTIFIER_FEP;
					$status_to_get = "'valid'";
				}
				$current_element = eva_UniteDeTravail::getWorkingUnit($idElement);
				$element_identifier = ELEMENT_IDENTIFIER_UT;
				break;
			default:
				$element = $tableElement;
				break;
		}
		$referenceDocument = str_replace('-', '', $informations['dateCreation']) . '-' . $element . $idElement . '-V' . $revisionDocument;

		/** Retrieve informations about users and groups associated to an element	*/
		$affectedUserTmp = array();
		$affectedUserList = evaUserLinkElement::getAffectedUser($tableElement, $idElement, $status_to_get);
		foreach ($affectedUserList as $user) {
			$theUser = evaUser::getUserInformation( $user->id_user );
			$theUser[ $user->id_user ][ 'status' ] = $user->status;
			$theUser[ $user->id_user ][ 'dateAffectation' ] = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $user->date_affectation_reelle, true );
			$theUser[ $user->id_user ][ 'dateDesaffectation' ] = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $user->date_desaffectation_reelle, true );
			$affectedUserTmp[] = $theUser;
		}
		$affectedUser = serialize( $affectedUserTmp );
		$affectedUserGroups = serialize( digirisk_groups::getBindGroupsWithInformations( $idElement, $tableElement . '_employee' ) );

		/** Retrieve informations about evaluators users and groups asociated to an element	*/
		$affectedUserTmp = array();
		$affectedUserList = evaUserLinkElement::getAffectedUser($tableElement . '_evaluation', $idElement, $status_to_get);
		foreach ($affectedUserList as $user) {
			$theUser = evaUser::getUserInformation( $user->id_user );
			$theUser[ $user->id_user ][ 'status' ] = $user->status;
			$theUser[ $user->id_user ][ 'dateEntretien' ] = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $user->date_affectation_reelle, true );
			$theUser[ $user->id_user ][ 'dureeEntretien' ] = $user->duration_in_hour;
			$affectedUserTmp[] = $theUser;
		}
		$affectedEvaluators = serialize( $affectedUserTmp );
		$affectedEvaluatorsGroups = serialize( digirisk_groups::getBindGroupsWithInformations( $idElement, $tableElement . '_evaluator' ) );

		/** Get the main picture for current element	*/
		$defaultPicture = evaPhoto::getMainPhoto($tableElement, $idElement);
		$defaultPictureToSet = '';
		if ($defaultPicture != 'error') {
			$defaultPictureToSet = $defaultPicture;
		}
		else {
			$defaultPictureToSet = 'noDefaultPicture';
		}

		/** Default element	*/
		$element = $tableElement;

		/** Get risk list for current element	*/
		$unitRisk = serialize(eva_documentUnique::listRisk($tableElement, $idElement, (!empty($informations['sheet_output_type']) ? $informations['sheet_output_type'] : ''), $informations['recursiv_mode']));

		/** Check element type	*/
		if ( $informations['sheet_type'] == 'digi_groupement' ) {
			$recommandation = '';
			$element = $tableElement . '_FGP';
			$model_shape = 'fiche_de_groupement';
			$document_final_name = '_ficheDeGroupement_';
		}
		else if ( $informations['sheet_type'] == 'digi_unite_travail' ) {
			/** Get recommandations associated to the element */
			$recommandationList = array();
			$affectedRecommandation = evaRecommandation::getRecommandationListForElement($tableElement, $idElement);
			$i = $oldIdRecommandationCategory = 0;
			foreach ($affectedRecommandation as $recommandation) {
				if ($oldIdRecommandationCategory != $recommandation->recommandation_category_id) {
					$i = 0;
					$oldIdRecommandationCategory = $recommandation->recommandation_category_id;
				}
				$recommandationCategoryMainPicture = evaPhoto::getMainPhoto(TABLE_CATEGORIE_PRECONISATION, $recommandation->recommandation_category_id);
				$recommandationCategoryMainPicture = evaPhoto::checkIfPictureIsFile($recommandationCategoryMainPicture, TABLE_CATEGORIE_PRECONISATION);
				if ($recommandationCategoryMainPicture != false) {
					$recommandationList[$recommandation->recommandation_category_id][$i]['recommandation_category_photo'] = str_replace(EVA_HOME_URL, '', str_replace(EVA_GENERATED_DOC_URL, '', $recommandationCategoryMainPicture));
				}
				else {
					$recommandationList[$recommandation->recommandation_category_id][$i]['recommandation_category_photo'] = 'noDefaultPicture';
				}
				$recommandationList[$recommandation->recommandation_category_id][$i]['id_preconisation'] = $recommandation->id_preconisation;
				$recommandationList[$recommandation->recommandation_category_id][$i]['efficacite'] = $recommandation->efficacite;
				$recommandationList[$recommandation->recommandation_category_id][$i]['commentaire'] = $recommandation->commentaire;
				$recommandationList[$recommandation->recommandation_category_id][$i]['recommandation_category_name'] = $recommandation->recommandation_category_name;
				$recommandationList[$recommandation->recommandation_category_id][$i]['recommandation_name'] = $recommandation->recommandation_name;
				$recommandationList[$recommandation->recommandation_category_id][$i]['impressionRecommandationCategorie'] = $recommandation->impressionRecommandationCategorie;
				$recommandationList[$recommandation->recommandation_category_id][$i]['tailleimpressionRecommandationCategorie'] = $recommandation->tailleimpressionRecommandationCategorie;
				$recommandationList[$recommandation->recommandation_category_id][$i]['impressionRecommandation'] = $recommandation->impressionRecommandation;
				$recommandationList[$recommandation->recommandation_category_id][$i]['tailleimpressionRecommandation'] = $recommandation->tailleimpressionRecommandation;
				$recommandationList[$recommandation->recommandation_category_id][$i]['photo'] = $recommandation->photo;
				$i++;
			}
			$recommandation = serialize($recommandationList);
			$model_shape = 'fiche_de_poste';
			$document_final_name = '_ficheUniteDeTravail_';
		}
		else if ( $informations['sheet_type'] == 'digi_risk_listing' ) {
			$recommandation = '';
			$element = $tableElement . '_RS';
			$model_shape = 'listing_des_risques';
			$document_final_name = '_listingRisque_';
		}

		/** Check the model to use for document	*/
		$modelToUse = eva_gestionDoc::getDefaultDocument($model_shape);
		if ( ($informations['id_model'] != 'undefined') && ($informations['id_model'] > 0) ) {
			$modelToUse = $informations['id_model'];
		}

		/** Generate document name from given parameters	*/
		if ( $informations['nomDuDocument'] == '' ) {
			$dateElement = explode(' ', $informations['dateCreation']);
			$documentName = str_replace('-', '', $dateElement[0]) . $document_final_name . digirisk_tools::slugify_noaccent(str_replace(' ', '_', (!empty($informations['nomEntreprise']) ? $informations['nomEntreprise'] : $current_element->nom)));
			$informations['nomDuDocument'] = $documentName;
		}

		/**	Enregistrement du document	*/
		$new_sheet_params = array();
		$new_sheet_params['id'] 					= '';
		$new_sheet_params['creation_date'] 			= current_time('mysql', 0);
		$new_sheet_params['revision'] 				= $revisionDocument;
		$new_sheet_params['id_element'] 			= $idElement;
		$new_sheet_params['id_model'] 				= $modelToUse;
		$new_sheet_params['table_element'] 			= $tableElement;
		$new_sheet_params['reference'] 				= $referenceDocument;
		$new_sheet_params['name'] 					= $informations['nomDuDocument'];
		$new_sheet_params['description'] 			= $informations['description'];
		$new_sheet_params['adresse']				= $informations['adresse'];
		$new_sheet_params['telephone'] 				= $informations['telephone'];
		$new_sheet_params['defaultPicturePath'] 	= $defaultPictureToSet;
		$new_sheet_params['societyName'] 			= digirisk_tools::slugify_noaccent($informations['nomEntreprise']);
		$new_sheet_params['users'] 					= $affectedUser;
		$new_sheet_params['userGroups'] 			= $affectedUserGroups;
		$new_sheet_params['evaluators'] 			= $affectedEvaluators;
		$new_sheet_params['evaluatorsGroups'] 		= $affectedEvaluatorsGroups;
		$new_sheet_params['unitRisk'] 				= $unitRisk;
		$new_sheet_params['recommandation'] 		= $recommandation;
		$new_sheet_params['document_type'] 			= $model_shape;

		$new_sheet = $wpdb->insert(TABLE_FP, $new_sheet_params);
		if ( $new_sheet === false ) {
			$status['result'] = 'error';
			$status['errors']['query_error'] = __("Une erreur est survenue lors de l'enregistrement", 'evarisk');
		}
		else {
			$status['result'] = 'ok';
			/*	Save the odt file	*/
			eva_gestionDoc::generateSummaryDocument($element, $idElement, 'odt');
		}

		return $status;
	}


	/**
	 * Generate a penibility sheet
	 *
	 * @param string $tableElement The element type to generate the document for
	 * @param integer $idElement The element identifier to generate the document for
	 *
	 */
	function generate_fiche_penibilite( $tableElement, $idElement, $user_to_generate_doc_for = array() ) {
		global $wpdb;
		$status = array();
		$status['result'] = 'error';
		$status['errors']['query_error'] = __("Aucune fiche a g&eacute;n&eacute;rer", 'evarisk');

		$penibility_level = get_option('digi_risk_penibility_level', 51);

		switch ( $tableElement ) {
			case TABLE_GROUPEMENT:
				$element = $tableElement . '_FEP';
				$model_shape = 'fiche_exposition_penibilite';
				$document_final_name = '_ficheDePenibilite_' . ELEMENT_IDENTIFIER_GP . $idElement . '_';
				$current_element = EvaGroupement::getGroupement($idElement);
				$current_element_tel = $current_element->telephoneGroupement;
			break;
			case TABLE_UNITE_TRAVAIL:
				$element = $tableElement . '_FEP';
				$model_shape = 'fiche_exposition_penibilite';
				$document_final_name = '_ficheDePenibilite_' . ELEMENT_IDENTIFIER_UT . $idElement . '_';
				$current_element = eva_UniteDeTravail::getWorkingUnit($idElement);
				$current_element_tel = $current_element->telephoneUnite;
			break;
		}
		$nom_element = (!empty($informations) && !empty($informations['nomEntreprise']) ? $informations['nomEntreprise'] : $current_element->nom);
		$description_element = (!empty($informations) && !empty($informations['description']) ? $informations['description'] : $current_element->description);
		$telephone_element = (!empty($informations) && !empty($informations['telephone']) ? $informations['telephone'] : $current_element_tel);
		$description_element = (!empty($informations) && !empty($informations['description']) ? $informations['description'] : $current_element->description);

		/** Retrieve informations about users and groups associated to an element	*/
		$affectedUserList = !empty($user_to_generate_doc_for) ? $user_to_generate_doc_for : evaUserLinkElement::getAffectedUser($tableElement, $idElement, "'valid', 'moderated', 'deleted'");
		$users = array();

		$start_affecations = $end_affectation = array();
		foreach ($affectedUserList as $user) {
			$users[$user->id_user][$user->id]['affectation_status'] = $user->status;
			$users[$user->id_user][$user->id]['affectation_date_in'] = $user->date_affectation;
			$users[$user->id_user][$user->id]['affectation_date_out'] = $user->date_desAffectation;
			$users[$user->id_user][$user->id]['affectation_date_in_real'] = $user->date_affectation_reelle;
			$users[$user->id_user][$user->id]['affectation_date_out_real'] = $user->date_desaffectation_reelle;
			$users[$user->id_user][$user->id]['dateDebutAffectation'] = mysql2date('d/m/Y H:i', $user->date_affectation_reelle, true);
			$users[$user->id_user][$user->id]['dateFinAffectation'] = !empty($users[$user->id_user][$user->id]['affectation_date_out_real']) && ($users[$user->id_user][$user->id]['affectation_date_out_real'] != '0000-00-00 00:00:00') ? mysql2date('d/m/Y H:i', $users[$user->id_user][$user->id]['affectation_date_out_real'], true) : (!empty($users[$user->id_user][$user->id]['affectation_date_out']) && ($users[$user->id_user][$user->id]['affectation_date_out'] != '0000-00-00 00:00:00') ? mysql2date('d/m/Y H:i', $users[$user->id_user][$user->id]['affectation_date_out'], true) : __('Actuellement affect&eacute;', 'evarisk'));

			$start_affecations[] = $user->date_affectation_reelle;
			$end_affectation[] = $user->date_desaffectation_reelle;

			$user_information = evaUser::getUserInformation($user->id_user);
			$user_infos = (array)$user_information;
			$users[$user->id_user]['user_informations'] = $user_infos[$user->id_user];
		}

		/**	Retrieve existing risks marked to be in penibility cat	*/
		$query = $wpdb->prepare("SELECT id, nom, description FROM " . TABLE_DANGER . " WHERE choix_danger LIKE ('%%%s%%')", 'penibilite');
		$risk_penible = $wpdb->get_results($query);
		$penibility_risk = array();
		if ( !empty($risk_penible) ) {
			foreach ( $risk_penible as $risk_infos ) {
				$complete_risk_list_with_evaluation = Risque::getRisques($tableElement, $idElement, "all", "tableRisque.id_danger = '" . $risk_infos->id . "'", 'tableRisque.id ASC', "'Valid', 'Moderated'");
				if ( !empty($complete_risk_list_with_evaluation) ) {

					foreach ($complete_risk_list_with_evaluation as $risque) {
						$risques[$risque->id][$risque->id_evaluation][] = $risque;
					}

					foreach ( $risques as $risque_id => $evaluation ) {
						foreach ( $evaluation as $id_evaluation => $risk_evaluation ) {
							$methode = MethodeEvaluation::getMethod($risk_evaluation[0]->id_methode);
							$listeVariables = MethodeEvaluation::getVariablesMethode($methode->id, $risk_evaluation[0]->unformatted_evaluation_date);
							unset($listeIdVariables);
							$listeIdVariables = array();
							foreach ( $listeVariables as $variable ) {
								$listeIdVariables[$variable->id][] = $variable->ordre;
							}

							$score = Risque::getScoreRisque($risk_evaluation);
							$quotation = Risque::getEquivalenceEtalon($risk_evaluation[0]->id_methode, $score, $risk_evaluation[0]->unformatted_evaluation_date);

							$penibility_risk[$risque_id][$id_evaluation]['id_danger'] = $risk_evaluation[0]->id_danger;
							$penibility_risk[$risque_id][$id_evaluation]['nom_danger'] = $risk_evaluation[0]->nomDanger;
							$penibility_risk[$risque_id][$id_evaluation]['description_danger'] = $risk_evaluation[0]->descriptionDanger;

							$penibility_risk[$risque_id][$id_evaluation]['status'] = $risk_evaluation[0]->evaluation_status;
							$penibility_risk[$risque_id][$id_evaluation]['date'] = $risk_evaluation[0]->unformatted_evaluation_date;
							$penibility_risk[$risque_id][$id_evaluation]['dateDebutRisque'] = $risk_evaluation[0]->dateDebutRisque;
							$penibility_risk[$risque_id][$id_evaluation]['dateFinRisque'] = $risk_evaluation[0]->dateFinRisque;
							$penibility_risk[$risque_id][$id_evaluation]['quotation'] = $quotation;
							$penibility_risk[$risque_id][$id_evaluation]['chosen_method_var'] = '   ';
							$penibility_risk[$risque_id][$id_evaluation]['chosen_method_var_value'] = '   ';
							foreach ( $risk_evaluation as $evaluation_var ) {
								if ( !empty($listeIdVariables) && !empty($listeIdVariables[$evaluation_var->id_variable]) && is_array($listeIdVariables[$evaluation_var->id_variable]) ) {
									$chosen_var = Eva_variable::getVariable( $evaluation_var->id_variable );
									$penibility_risk[$risque_id][$id_evaluation]['chosen_method_var'] .= ELEMENT_IDENTIFIER_V . $evaluation_var->id_variable . ' - ' . $chosen_var->nom . ' / ';
									if ($chosen_var->affichageVar == 'checkbox') {
										$tableau = unserialize($chosen_var->questionVar);
										$i = $chosen_var->min;
										foreach ($tableau as $t) {
											if ($i == $evaluation_var->valeur) {
												$penibility_risk[$risque_id][$id_evaluation]['chosen_method_var_value'] .= (strpos($t['question'], '%s') ? sprintf($t['question'], $t['seuil']) : $t['question']) . ' / ';
											}
											$i++;
										}
									}
									else if ( $chosen_var->affichageVar == 'slide' ) {
										$penibility_risk[$risque_id][$id_evaluation]['chosen_method_var_value'] .= $chosen_var->nom . ' : ' . $evaluation_var->valeur . ' / ';
									}
								}
							}
							$penibility_risk[$risque_id][$id_evaluation]['chosen_method_var'] = substr($penibility_risk[$risque_id][$id_evaluation]['chosen_method_var'], 0, -3);
							$penibility_risk[$risque_id][$id_evaluation]['chosen_method_var_value'] = substr($penibility_risk[$risque_id][$id_evaluation]['chosen_method_var_value'], 0, -3);
						}
					}
				}
			}
		}

		/**	Build risk for the user	*/
		if ( !empty($users) && !empty($penibility_risk) ) {
			if ( count($users) > 1 ) {
				$file_to_zip = array();
			}
			foreach ( $users as $user_id => $user_affectations ) {
				$user_risk = array();
				$user_risk['riskPenibleSoumis'] = array();
				$effectiv_risks = array();
				$user_risk['riskPenibleSoumis'] = array();

				$users_affectation_list = $user_affectations;
				unset( $users_affectation_list['user_informations'] );

					foreach ( $penibility_risk as $risk_id => $risk_evaluations ) {
						foreach ( $risk_evaluations as $risk_evaluation_id => $risk_evaluation_details) {

							/**	Check user exposition	*/
							$user_is_exposed = false;
							foreach ( $users_affectation_list as $affectation_id => $affectation_detail ) {
								$affectation_date_to_take = !empty($affectation_detail['affectation_date_in_real']) && ($affectation_detail['affectation_date_in_real'] != '0000-00-00 00:00:00')  ? $affectation_detail['affectation_date_in_real'] : (!empty($affectation_detail['affectation_date_in']) ? $affectation_detail['affectation_date_in'] : '2012-01-01 00:00:00');
								$unaffectation_date_to_take = !empty($affectation_detail['affectation_date_out_real']) && ($affectation_detail['affectation_date_out_real'] != '0000-00-00 00:00:00') ? $affectation_detail['affectation_date_out_real'] : (!empty($affectation_detail['affectation_date_out']) && ($affectation_detail['affectation_date_out'] != '0000-00-00 00:00:00') ? $affectation_detail['affectation_date_out'] : '0000-00-00 00:00:00');

								$date_debut_exposition = $risk_evaluation_details['date'];
								if ( $affectation_date_to_take >= $date_debut_exposition ) {
									$date_debut_exposition = $affectation_date_to_take;
								}
								$date_fin_exposition = $unaffectation_date_to_take;
								if ( ($risk_evaluation_details['dateFinRisque'] != '0000-00-00 00:00:00') && (($date_fin_exposition == '0000-00-00 00:00:00') || ( $risk_evaluation_details['dateFinRisque'] < $date_fin_exposition)) ) {
									$date_fin_exposition = $risk_evaluation_details['dateFinRisque'];
								}

								if ( ($date_debut_exposition <= $date_fin_exposition) || ($date_fin_exposition == '0000-00-00 00:00:00') ) {
									$user_is_exposed = true;

									$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['datesAffectationEvaluation'][$affectation_id]['dateAffectation'] = $date_debut_exposition;
									$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['datesAffectationEvaluation'][$affectation_id]['dateDesAffectation'] = $date_fin_exposition != '0000-00-00 00:00:00' ? $date_fin_exposition : __('Actuellement expos&eacute;', 'evarisk');

								}
							}

							if ( $user_is_exposed ) {
								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['idRisque'] = ELEMENT_IDENTIFIER_R . $risk_id;
								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['idEvaluation'] = ELEMENT_IDENTIFIER_E . $risk_evaluation_id;
								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['intituleRisque'] = ELEMENT_IDENTIFIER_D . $risk_evaluation_details['id_danger'] . ' - ' . $risk_evaluation_details['nom_danger'];
								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['descriptionRisque'] = $risk_evaluation_details['description_danger'];

								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['dateEvaluation'] = $risk_evaluation_details['date'];
								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['dateDebutRisque'] = $risk_evaluation_details['dateDebutRisque'];
								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['dateFinRisque'] = !empty($risk_evaluation_details['dateFinRisque']) && ($risk_evaluation_details['dateFinRisque'] != '0000-00-00 00:00:00') ? $risk_evaluation_details['dateFinRisque'] : __('Risque toujours pr&eacute;sent', 'evarisk');

								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['evaluationCotation'] = $risk_evaluation_details['quotation'];
								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['levelCotation'] = $penibility_level;
								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['chosenMethodVar'] = str_replace('<br/>', "
", $risk_evaluation_details['chosen_method_var']);
								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['chosenMethodVarValue'] = str_replace('<br/>', "
", $risk_evaluation_details['chosen_method_var_value']);

								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['riskPenibleNon'] = (!empty($penibility_level) && (empty($risk_evaluation_details['quotation']) || (!empty($risk_evaluation_details['quotation']) && ($risk_evaluation_details['quotation'] < $penibility_level))) ) ? 'X' : '';
								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['riskPenibleOui'] = (!empty($penibility_level) && !empty($risk_evaluation_details['quotation']) && ($risk_evaluation_details['quotation'] >= $penibility_level)) ? 'X' : '';

								/**	Retrieve comment about the current risk	*/
								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['commentaireRisk'] = '';
								$query = $wpdb->prepare("SELECT GROUP_CONCAT(id_evaluation) as risk_eval_list FROM " . TABLE_AVOIR_VALEUR . " WHERE id_risque = %d GROUP BY id_risque", $risk_id);
								$risk_eval_list = $wpdb->get_var($query);
								$query = $wpdb->prepare(
									"SELECT *
									FROM " . TABLE_ACTIVITE_SUIVI . "
									WHERE id_element IN (" . $risk_eval_list . ")
										AND table_element = '%s'
										AND status = 'valid'
									ORDER BY date DESC",
										TABLE_AVOIR_VALEUR
								);
								$risk_comment_list = $wpdb->get_results($query);
								$risk_comment_export = '';
								if ( !empty($risk_comment_list) ) {
									foreach ( $risk_comment_list as $risk_comment ) {
										$comment_date_to_take = !empty($risk_comment->date_ajout) && ($risk_comment->date_ajout != '0000-00-00 00:00:00') ? $risk_comment->date_ajout : (!empty($risk_comment->date) && ($risk_comment->date != '0000-00-00 00:00:00') ? $risk_comment->date : '');
										if ( empty($comment_date_to_take) || ($risk_evaluation_details['date'] >= $comment_date_to_take) ) {
											$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['commentaireRisk'] .= str_replace('<br />', "
", (!empty($comment_date_to_take) ? mysql2date('d-m-Y H:i', $comment_date_to_take, true ) . ' - ' : '') . $risk_comment->commentaire) . "
";
										}
									}
								}

								/**	Retrieve recommandation about the current risk	*/
								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['recommandationOrganisationnelles'] = array();
								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['recommandationCollectives'] = array();
								$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['recommandationIndividuelles'] = array();
								$recommandationList = array();
								$affectedRecommandation = evaRecommandation::getRecommandationListForElement(TABLE_RISQUE, $risk_id);
								$i = $oldIdRecommandationCategory = 0;
								foreach ($affectedRecommandation as $recommandation) {
									$recommandation_affectation_date_to_take = !empty($recommandation->date_affectation) && ($recommandation->date_affectation != '0000-00-00 00:00:00') ? $recommandation->date_affectation : null;
									if ( !empty($recommandation->id_preconisation) && ( empty($recommandation_affectation_date_to_take) || ($recommandation_affectation_date_to_take <= $risk_evaluation_details['date']) ) ) {
										if ($oldIdRecommandationCategory != $recommandation->recommandation_category_id) {
											$i = 0;
											$oldIdRecommandationCategory = $recommandation->recommandation_category_id;
										}
										$recommandationCategoryMainPicture = evaPhoto::getMainPhoto(TABLE_CATEGORIE_PRECONISATION, $recommandation->recommandation_category_id);
										$recommandationCategoryMainPicture = evaPhoto::checkIfPictureIsFile($recommandationCategoryMainPicture, TABLE_CATEGORIE_PRECONISATION);
										if ($recommandationCategoryMainPicture != false) {
											$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['recommandation' . ucfirst($recommandation->preconisation_type)][$i]['recommandation_category_photo'] = str_replace(EVA_HOME_URL, '', str_replace(EVA_GENERATED_DOC_URL, '', $recommandationCategoryMainPicture));
										}
										else {
											$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['recommandation' . ucfirst($recommandation->preconisation_type)][$i]['recommandation_category_photo'] = 'noDefaultPicture';
										}
										$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['recommandation' . ucfirst($recommandation->preconisation_type)][$i]['id_preconisation'] = $recommandation->id_preconisation;
										$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['recommandation' . ucfirst($recommandation->preconisation_type)][$i]['efficacite'] = $recommandation->efficacite;
										$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['recommandation' . ucfirst($recommandation->preconisation_type)][$i]['commentaire'] = $recommandation->commentaire;
										$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['recommandation' . ucfirst($recommandation->preconisation_type)][$i]['recommandation_category_name'] = $recommandation->recommandation_category_name;
										$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['recommandation' . ucfirst($recommandation->preconisation_type)][$i]['recommandation_name'] = $recommandation->recommandation_name;
										$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['recommandation' . ucfirst($recommandation->preconisation_type)][$i]['impressionRecommandationCategorie'] = $recommandation->impressionRecommandationCategorie;
										$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['recommandation' . ucfirst($recommandation->preconisation_type)][$i]['tailleimpressionRecommandationCategorie'] = $recommandation->tailleimpressionRecommandationCategorie;
										$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['recommandation' . ucfirst($recommandation->preconisation_type)][$i]['impressionRecommandation'] = $recommandation->impressionRecommandation;
										$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['recommandation' . ucfirst($recommandation->preconisation_type)][$i]['tailleimpressionRecommandation'] = $recommandation->tailleimpressionRecommandation;
										$user_risk['riskPenibleSoumis'][$risk_evaluation_id]['details']['recommandation' . ucfirst($recommandation->preconisation_type)][$i]['photo'] = $recommandation->photo;
										$i++;
									}
								}

								$effectiv_risks[] = $risk_evaluation_details['id_danger'];
							}
							else {
								unset($user_risk['riskPenibleSoumis'][$risk_evaluation_id]);
							}
						}
					}
// 	exit;
					krsort($user_risk['riskPenibleSoumis']);

				/**	Risque nons soumis	*/
				if ( !empty($risk_penible) ) {
					foreach ( $risk_penible as $risk_infos ) {
						if ( empty($effectiv_risks) || !in_array($risk_infos->id, $effectiv_risks) ) {
							$user_risk['riskPenibleNonSoumis'][$risk_infos->id]['intituleRisque'] = $risk_infos->nom;
							$user_risk['riskPenibleNonSoumis'][$risk_infos->id]['descriptionRisque'] = $risk_infos->description;
							$user_risk['riskPenibleNonSoumis'][$risk_infos->id]['details'] = null;
						}
					}
				}

				/** Check the model to use for document	*/
				$modelToUse = eva_gestionDoc::getDefaultDocument( $model_shape );
				if ( !empty($_POST) && !empty($_POST['id_model']) && ($_POST['id_model'] != 'undefined') ) {
					$modelToUse = $_POST['id_model'];
				}

				/** Retrieve last revision for the document to generate	*/
				$revision = '';
				$query = $wpdb->prepare(
						"SELECT max(revision) AS lastRevision
			FROM " . TABLE_FP . "
			WHERE table_element = %s
				AND id_element = %d
				AND affected_user = %d
				AND document_type = %s
				AND REPLACE( SUBSTRING( `creation_date` , 1, 10 ) , '-', '' ) = %s",
						$tableElement, $idElement, $user_id, $model_shape, str_replace('-', '', substr(current_time('mysql', 0), 0, 10)));
				$revision = $wpdb->get_row($query);
				$revisionDocument = $revision->lastRevision + 1;

				/** Generate document name from given parameters	*/
				$dateElement = (!empty($informations) && !empty($informations['dateCreation']) ? $informations['dateCreation'] : current_time('mysql', 0));
				$ExplodedDateElement = explode(' ', $dateElement);
				$documentName = str_replace('-', '', $ExplodedDateElement[0]) . $document_final_name . str_replace('-', '', sanitize_title( $nom_element ) ) . '_' . ELEMENT_IDENTIFIER_U . $user_id . '_' . str_replace('-', '', sanitize_title( $user_affectations['user_informations']['user_lastname'] . $user_affectations['user_informations']['user_firstname'] ) );
				$informations['nomDuDocument'] = $documentName;
				$referenceDocument = str_replace('-', '', str_replace(':', '', str_replace(' ', '', $dateElement))) . '-' . ELEMENT_IDENTIFIER_U . $user_id . '-' . $element . $idElement . '-V' . $revisionDocument;

				/**	Enregistrement du document	*/
				$new_sheet_params = array();
				$new_sheet_params['id'] 					= '';
				$new_sheet_params['creation_date'] 			= current_time('mysql', 0);
				$new_sheet_params['revision'] 				= $revisionDocument;
				$new_sheet_params['id_element'] 			= $idElement;
				$new_sheet_params['id_model'] 				= $modelToUse;
				$new_sheet_params['table_element'] 			= $tableElement;
				$new_sheet_params['reference'] 				= $referenceDocument;
				$new_sheet_params['name'] 					= digirisk_tools::slugify_noaccent($informations['nomDuDocument']);
				$new_sheet_params['description'] 			= digirisk_tools::slugify_noaccent($description_element);
				$new_sheet_params['adresse']				= '';
				$new_sheet_params['telephone'] 				= $telephone_element;
				$new_sheet_params['defaultPicturePath'] 	= '';
				$new_sheet_params['societyName'] 			= digirisk_tools::slugify_noaccent($nom_element);
				$new_sheet_params['users'] 					= serialize( $users[ $user_id ] );
				$new_sheet_params['userGroups'] 			= '';
				$new_sheet_params['evaluators'] 			= '';
				$new_sheet_params['evaluatorsGroups'] 		= '';
				$new_sheet_params['unitRisk'] 				= serialize($user_risk);
				$new_sheet_params['recommandation'] 		= '';
				$new_sheet_params['document_type'] 			= $model_shape;
				$new_sheet_params['affected_user'] 			= $user_id;
				$new_sheet_params['document_final_dir'] 	= $user_id . '/' . $tableElement . '/' . $idElement . '/';

				$new_sheet = $wpdb->insert(TABLE_FP, $new_sheet_params);
				if ( $new_sheet === false ) {
					$status['result'] = 'error';
					$status['errors']['query_error'] = __("Une erreur est survenue lors de l'enregistrement", 'evarisk');
				}
				else {
					$status['result'] = 'ok';
					/*	Save the odt file	*/
					$status['path'] = eva_gestionDoc::generateSummaryDocument($element, $idElement, 'odt');
					if ( count($users) > 1 && !empty($status['path']) && is_file($status['path']) ) {
						$file_to_zip[] = $status['path'];
					}
				}
			}

			if ( !empty($file_to_zip) ) {
				$pathToZip = EVA_RESULTATS_PLUGIN_DIR . 'ficheDeRisques/' . $user_id . '/';
				wp_mkdir_p($pathToZip);
				/*	ZIP THE FILE	*/
				$zipFileName = date('YmdHis') . '_ficheDeRisques.zip';
				$archive = new eva_Zip($zipFileName);
				$archive->setFiles($file_to_zip);
				$archive->compressToPath($pathToZip);
				$saveWorkSheetUnitStatus = eva_gestionDoc::saveNewDoc('fiche_exposition_penibilite', $tableElement, $idElement, str_replace(EVA_GENERATED_DOC_DIR, '', $pathToZip . $zipFileName));
			}

		}

		return $status;
	}

	function generate_task_odt($tableElement, $idElement, $idDocument) {
		global $wpdb;
		require_once(EVA_LIB_PLUGIN_DIR . 'odtPhpLibrary/Odf.php');
		ini_set("memory_limit","256M");

		/**	Get the document to create	*/
		$query = $wpdb->prepare(
			"SELECT *
			FROM " . TABLE_GED_DOCUMENTS . " AS D
			WHERE table_element = %s
				AND id_element = %d
				AND status = 'valid'
				AND id = %d",
			array(
				$tableElement,
				$idElement,
				$idDocument,
			));
		$last_document = $wpdb->get_row($query);

		$query = $wpdb->prepare("
			SELECT meta_key, meta_value
			FROM " . TABLE_GED_DOCUMENTS_META . " AS DM
			WHERE document_id = %d",
			array(
				$idDocument,
			));
		$document_meta = $wpdb->get_results( $query );
		$document_infos = array();
		foreach ( $document_meta as $infos ) {
			$document_infos[ $infos->meta_key ] = substr( $infos->meta_value , 0, 2 ) == 'a:' ?unserialize( $infos->meta_value ) : $infos->meta_value;
		}

		switch ($tableElement) {
			case TABLE_ACTIVITE:
			case TABLE_TACHE:
				$DigiOdfModelFile = EVA_MODELES_PLUGIN_DIR . 'planDActions/modele_default_fiche_action.odt';
			break;
		}

		$config = array(
			'PATH_TO_TMP' => EVA_RESULTATS_PLUGIN_DIR . 'tmp'
		);
		/**	Get the default model regarding on the element type we are on	*/
		$DigiOdf = new DigiOdf($DigiOdfModelFile, $config);
		/**	Get the last used model	*/
		if( !empty($last_document) && !empty($document_infos) && !empty($document_infos[ '_digi_doc_model' ]) && ( $document_infos[ '_digi_doc_model' ] != 'default' ) ) {
			$pathToModelFile = eva_gestionDoc::getDocumentPath( $document_infos[ '_digi_doc_model' ] );
			$DigiOdf = new DigiOdf(EVA_GENERATED_DOC_DIR . $pathToModelFile, $config);
		}

		foreach ( $document_infos as $main_key => $content ) {
			if ( is_array( $content ) ) {
				foreach ( $content as $key => $value ) {
					if ( $key == 'photoPrincipale' ) {
						if( $value != 'noDefaultPicture' && is_file(EVA_GENERATED_DOC_DIR . $value)){
							$DigiOdf->setImage('photoPrincipale', EVA_GENERATED_DOC_DIR . $value, digirisk_options::getOptionValue('taille_photo_poste_fiche_de_poste'));
						}
						else{
							$DigiOdf->setVars('photoPrincipale', html_entity_decode( __('Aucun photo d&eacute;finie', 'evarisk')) );
						}
					}
					else if ( $key == 'photoAssocieeAction' ) {
						$digi_segment_content = $DigiOdf->setdigiSegment( $key );
						if ( is_object($digi_segment_content) ) {
							foreach ( $value as $the_digi_segment_content ) {
								foreach ( $the_digi_segment_content as $info_key => $info ) {
									if( $info_key == 'photo' ) {
										if ( is_file(EVA_GENERATED_DOC_DIR . $info) ){
											$digi_segment_content->setImage($info_key, EVA_GENERATED_DOC_DIR . $info, digirisk_options::getOptionValue('taille_photo_poste_fiche_de_poste'));
										}
										else{
											$digi_segment_content->setVars($info_key, html_entity_decode( __('Impossible de trouver l\'image', 'evarisk')) );
										}
									}
									else{
										$digi_segment_content->setVars($info_key, html_entity_decode( stripslashes( $info ) ) );
									}
								}
								$digi_segment_content->merge();
							}
							$DigiOdf->mergedigiSegment( $digi_segment_content );
						}
					}
					else if ( is_array( $value ) ) {
						$digi_segment_content = $DigiOdf->setdigiSegment( $key );
						if ( is_object($digi_segment_content) ) {
							foreach ( $value as $index_test => $the_digi_segment_content ) {
								if ( is_array( $the_digi_segment_content ) ) {
									foreach ( $the_digi_segment_content as $info_key => $info ) {
										$digi_segment_content->setVars( $info_key, html_entity_decode( stripslashes( $info )) );
									}
								}
								$digi_segment_content->merge();
							}
							$DigiOdf->mergedigiSegment( $digi_segment_content );
						}
					}
					else {
						$DigiOdf->setVars( $key , html_entity_decode( stripslashes( $value ) ) );
					}
				}
			}
		}

		if ( !is_dir(EVA_RESULTATS_PLUGIN_DIR . $last_document->chemin) ) {
			wp_mkdir_p( EVA_RESULTATS_PLUGIN_DIR . $last_document->chemin );
		}
		$DigiOdf->saveToDisk(EVA_RESULTATS_PLUGIN_DIR . $last_document->chemin . $last_document->nom);
	}

	/**
	 *	Generate an output of summary about the risk on an element. Could be a "single document" or a "work unit sheet"
	 *
	 *	@param mixed $tableElement The element type we want to generate the document for
	 *	@param integer $idElement The element identifier we want to generate the document for
	 *	@param mixed $outputType The output we want to get (html, odt, ...)
	 *	@param integer $idDocument The identifier of a specific document we want to get
	 *
	 *	@return mixed Depending on the output type we ask for, an html output or a file
	 */
	function generateSummaryDocument($tableElement, $idElement, $outputType, $idDocument = '') {
		global $typeRisque;
		global $typeRisquePlanAction;
		require_once(EVA_LIB_PLUGIN_DIR . 'evaluationDesRisques/documentUnique/templateDocumentUnique.tpl.php');
		require_once(EVA_LIB_PLUGIN_DIR . 'gestionDocumentaire/gestionDoc.class.php');
		require_once(EVA_LIB_PLUGIN_DIR . 'arborescence/arborescence_special.class.php');

		switch ($tableElement) {
			case TABLE_GROUPEMENT:
				/**	Get the last summary document generated for the current element OR Get a given generated summary document	*/
				$lastDocument = eva_documentUnique::getDernierDocumentUnique($tableElement, $idElement, $idDocument);
				/**	Store the different informations about the last generated summary document in an array for more usability	*/
				unset($documentUniqueParam);
				$documentUniqueParam['#NUMREF#'] = $lastDocument->referenceDUER;
				$documentUniqueParam['#NOMENTREPRISE#'] = $lastDocument->nomSociete;
				$documentUniqueParam['#DEBUTAUDIT#'] = date('d/m/Y', strtotime($lastDocument->dateDebutAudit));
				$documentUniqueParam['#FINAUDIT#'] = date('d/m/Y', strtotime($lastDocument->dateFinAudit));
				$documentUniqueParam['#DATE#'] = date('d/m/Y', strtotime($lastDocument->dateGenerationDUER));
				$documentUniqueParam['#NOMPRENOMEMETTEUR#'] = $lastDocument->emetteurDUER;
				$documentUniqueParam['#NOMPRENOMDESTINATAIRE#'] = $lastDocument->destinataireDUER  ;
				$documentUniqueParam['#TELFIXE#'] = $lastDocument->telephoneFixe ;
				$documentUniqueParam['#TELMOBILE#'] = $lastDocument->telephonePortable ;
				$documentUniqueParam['#TELFAX#'] = $lastDocument->telephoneFax ;
				$documentUniqueParam['#NOMDOCUMENT#'] = $lastDocument->nomDUER;
				$documentUniqueParam['#REVISION#'] = $lastDocument->revisionDUER;
				$documentUniqueParam['#METHODOLOGIE#'] = $lastDocument->methodologieDUER;
				$documentUniqueParam['#SOURCES#'] = $lastDocument->sourcesDUER;
				$documentUniqueParam['#DISPODESPLANS#'] = $lastDocument->planDUER;
				$documentUniqueParam['#ALERTE#'] = $lastDocument->alerteDUER;

				$DigiOdfModelFile = EVA_MODELES_PLUGIN_DIR . 'documentUnique/modeleDefaut.odt';
			break;
			case TABLE_UNITE_TRAVAIL:
				/**	Get the last summary document generated for the current element OR Get a given generated summary document	*/
				$lastDocument = eva_gestionDoc::getGeneratedDocument($tableElement, $idElement, 'last', $idDocument, 'fiche_de_poste');
				$DigiOdfModelFile = EVA_MODELES_PLUGIN_DIR . 'ficheDePoste/modeleDefaut.odt';
			break;
			case TABLE_GROUPEMENT . '_FGP' :
				/**	Get the last summary document generated for the current element OR Get a given generated summary document	*/
				$lastDocument = eva_gestionDoc::getGeneratedDocument(str_replace('_FGP', '', $tableElement), $idElement, 'last', $idDocument, 'fiche_de_groupement');
				$DigiOdfModelFile = EVA_MODELES_PLUGIN_DIR . 'ficheDeGroupement/modeleDefaut_groupement.odt';
			break;
			case TABLE_GROUPEMENT . '_RS' :
			case TABLE_UNITE_TRAVAIL . '_RS' :
				/**	Get the last summary document generated for the current element OR Get a given generated summary document	*/
				$lastDocument = eva_gestionDoc::getGeneratedDocument(str_replace('_RS', '', $tableElement), $idElement, 'last', $idDocument, 'listing_des_risques');
				$DigiOdfModelFile = EVA_MODELES_PLUGIN_DIR . 'listingRisque/modeleDefault_listing_risque.odt';
			break;
			case TABLE_GROUPEMENT . '_FEP' :
			case TABLE_UNITE_TRAVAIL . '_FEP' :
				/**	Get the last summary document generated for the current element OR Get a given generated summary document	*/
				$lastDocument = eva_gestionDoc::getGeneratedDocument(str_replace('_FEP', '', $tableElement), $idElement, 'last', $idDocument, 'fiche_exposition_penibilite');
				$DigiOdfModelFile = EVA_MODELES_PLUGIN_DIR . 'ficheDeRisques/modeleDefault_fiche_penibilite.odt';
			break;
		}

		/**	If user ask for an "odt" file we include different librairies and model	*/
		if($outputType == 'odt') {
			require_once(EVA_LIB_PLUGIN_DIR . 'odtPhpLibrary/Odf.php');

			$config = array(
				'PATH_TO_TMP' => EVA_RESULTATS_PLUGIN_DIR . 'tmp'
			);
			/**	Get the default model regarding on the element type we are on	*/
			$DigiOdf = new DigiOdf($DigiOdfModelFile, $config);
			/**	Get the last used model	*/
			if(!empty($lastDocument) && is_object($lastDocument) && $lastDocument->id_model > 1) {
				$pathToModelFile = eva_gestionDoc::getDocumentPath($lastDocument->id_model);
				$DigiOdf = new DigiOdf(EVA_GENERATED_DOC_DIR . $pathToModelFile, $config);
			}
		}

		/**	Generate a html output	*/
		if($outputType == 'html')
		{
			switch($tableElement)
			{
				case TABLE_GROUPEMENT:
				{
					$documentUnique = '';
					$nbPageTotal = 1;

					/**	Ajout du sommaire	*/
					$nbPageTotal++;
					unset($documentUniqueParam);
					$documentUniqueParam['#PAGE#'] = $nbPageTotal . '/#NBPAGETOTAL#';
					$documentUniqueParam['#CONTENTPAGE#'] = EvaDisplayDesign::feedTemplate($sommaireDocumentUnique, $pageParam);
					$output = EvaDisplayDesign::feedTemplate($templatePageDocumentUnique, $documentUniqueParam);
					$output = str_replace("
		","",str_replace("	","",$output));
					if ( $outputType == 'html' ) {
						$documentUnique .= $output;
					}

					/**	Chapitre Administratif	*/
					$nbPageTotal++;
					unset($documentUniqueParam);
					$documentUniqueParam['#PAGE#'] = $nbPageTotal . '/#NBPAGETOTAL#';
					$documentUniqueParam['#CONTENTPAGE#'] = EvaDisplayDesign::feedTemplate($ChapitreAdministratif, $pageParam);
					if ( $outputType == 'html' ) {
						$documentUnique .= EvaDisplayDesign::feedTemplate($templatePageDocumentUnique, $documentUniqueParam);
					}

					/**	Localisation et remarques importantes	*/
					$nbPageTotal++;
					unset($documentUniqueParam);
					$documentUniqueParam['#PAGE#'] = $nbPageTotal . '/#NBPAGETOTAL#';
					$documentUniqueParam['#CONTENTPAGE#'] = EvaDisplayDesign::feedTemplate($localisationRemarques, $pageParam);
					if ( $outputType == 'html' ) {
						$documentUnique .= EvaDisplayDesign::feedTemplate($templatePageDocumentUnique, $documentUniqueParam);
					}

					/*	Chapitre evaluation	*/
					$nbPageTotal++;
					unset($documentUniqueParam);
					$documentUniqueParam['#PAGE#'] = $nbPageTotal . '/#NBPAGETOTAL#';
					$documentUniqueParam['#CONTENTPAGE#'] = EvaDisplayDesign::feedTemplate($chapitreEvaluation, $pageParam);
					if ( $outputType == 'html' ) {
						$documentUnique .= EvaDisplayDesign::feedTemplate($templatePageDocumentUnique, $documentUniqueParam);
					}

					/*	Methode d'evaluation et quantification	*/
					$nbPageTotal++;
					unset($documentUniqueParam);
					$documentUniqueParam['#PAGE#'] = $nbPageTotal . '/#NBPAGETOTAL#';
					$documentUniqueParam['#CONTENTPAGE#'] = EvaDisplayDesign::feedTemplate($methodeEvaluationQuantification, $pageParam);
					if ( $outputType == 'html' ) {
						$documentUnique .= EvaDisplayDesign::feedTemplate($templatePageDocumentUnique, $documentUniqueParam);
					}

					/*	Groupes d'utilisateurs	*/
					$nbPageTotal++;
					unset($documentUniqueParam);
					$documentUniqueParam['#PAGE#'] = $nbPageTotal . '/#NBPAGETOTAL#';
					unset($pageParam);

					/*	Groupes Existant	*/
					$groupesUtilisateur = unserialize($lastDocument->groupesUtilisateurs);
					if ( is_array($groupesUtilisateur) ) {
						$listeGroupeUtilisateur =
							'<table summary="userGroupsSummary' . $tableElement . '-' . $idElement . '" cellpadding="0" cellspacing="0" class="widefat post fixed">
								<thead>
									<tr>
										<th>' . __('Nom du groupe', 'evarisk') . '</th>
										<th>' . __('Description du groupe', 'evarisk') . '</th>
										<th>' . __('Nombre d\'utilisateur du groupe', 'evarisk') . '</th>
									</tr>
								</thead>
								<tfoot></tfoot>
								<tbody>
									' . digirisk_groups::outputGroupListing($groupesUtilisateur, 'html') . '
								</tbody>
							</table>';
					}
					else {
						$listeGroupeUtilisateur = $lastDocument->groupesUtilisateurs;
					}
					$pageParam['#GROUPESUTILISATEURS#'] = $listeGroupeUtilisateur;

					/*	Groupes affectes	*/
					$groupesUtilisateursAffectes = unserialize($lastDocument->groupesUtilisateursAffectes);
					if ( is_array($groupesUtilisateursAffectes) ) {
						$listeGroupeUtilisateur =
							'<table summary="userGroupsSummary' . $tableElement . '-' . $idElement . '" cellpadding="0" cellspacing="0" class="widefat post fixed">
								<thead>
									<tr>
										<th>' . __('&Eacute;l&eacute;ment', 'evarisk') . '</th>
										<th>' . __('Groupes utilisateurs (m&eacute;tiers)', 'evarisk') . '</th>
									</tr>
								</thead>
								<tfoot></tfoot>
								<tbody>
									' . eva_documentUnique::readExportedDatas($groupesUtilisateursAffectes, 'affectedUserGroup', '', 'html') . '
								</tbody>
							</table>';
					}
					else {
						$listeGroupeUtilisateur = $lastDocument->groupesUtilisateursAffectes;
					}
					$pageParam['#GROUPESUTILISATEURSAFFECTES#'] = $listeGroupeUtilisateur;
					$documentUniqueParam['#CONTENTPAGE#'] = EvaDisplayDesign::feedTemplate($groupesUtilisateurs, $pageParam);
					if ( $outputType == 'html' ) {
						$documentUnique .= EvaDisplayDesign::feedTemplate($templatePageDocumentUnique, $documentUniqueParam);
					}

					/*	Unites de travail	*/
					$nbPageTotal++;
					unset($documentUniqueParam);
					$documentUniqueParam['#PAGE#'] = $nbPageTotal . '/#NBPAGETOTAL#';
					$documentUniqueParam['#CONTENTPAGE#'] = EvaDisplayDesign::feedTemplate($unitesDeTravail, $pageParam);
					if ( $outputType == 'html' ) {
						$documentUnique .= EvaDisplayDesign::feedTemplate($templatePageDocumentUnique, $documentUniqueParam);
					}

					/*	FER : Fiche d'Evaluation des Risques	*/
					$nbPageTotal++;
					unset($documentUniqueParam);
					$documentUniqueParam['#PAGE#'] = $nbPageTotal . '/#NBPAGETOTAL#';
					$documentUniqueParam['#CONTENTPAGE#'] = EvaDisplayDesign::feedTemplate($ficheDEvaluationDesRisques, $pageParam);
					if ( $outputType == 'html' ) {
						$documentUnique .= EvaDisplayDesign::feedTemplate($templatePageDocumentUnique, $documentUniqueParam);
					}

					/*	Introduction risques unitaires	*/
					$nbPageTotal++;
					unset($documentUniqueParam);
					$documentUniqueParam['#PAGE#'] = $nbPageTotal . '/#NBPAGETOTAL#';
					$documentUniqueParam['#CONTENTPAGE#'] = EvaDisplayDesign::feedTemplate($introductionRisquesUnitaires, $pageParam);
					if ( $outputType == 'html' ) {
						$documentUnique .= EvaDisplayDesign::feedTemplate($templatePageDocumentUnique, $documentUniqueParam);
					}

					/*	Risques unitaires	*/
					$nbPageTotal++;
					unset($documentUniqueParam);
					$documentUniqueParam['#PAGE#'] = $nbPageTotal . '/#NBPAGETOTAL#';
					unset($pageParam);
					$pageParam['#LIGNESRISQUESUNITAIRES#'] = '';
						$getRisquesUnitaires = unserialize($lastDocument->risquesUnitaires);
						$listeRisqueUnitaire = eva_documentUnique::readBilanUnitaire($getRisquesUnitaires);
						if ( is_array($getRisquesUnitaires) ) {
							krsort($listeRisqueUnitaire);
							foreach ( $listeRisqueUnitaire as $categorieRisque => $risques ) {
								foreach ( $risques as $niveauRisque => $risque ) {
									foreach ( $risque as $identifiantRisque => $risqueInformations ) {
										unset($paramLigneRisqueUnitaire);
										$paramLigneRisqueUnitaire['#NOMELEMENT#'] = $risqueInformations['nomElement'];
										$couleurRisque = COULEUR_RISQUE_FAIBLE;
										$couleurTexteRisque = COULEUR_TEXTE_RISQUE_FAIBLE;
										if ( $categorieRisque >= SEUIL_BAS_INACCEPTABLE ) {
											$couleurRisque = COULEUR_RISQUE_INACCEPTABLE;
											$couleurTexteRisque = COULEUR_TEXTE_RISQUE_INACCEPTABLE;
										}
										else if ( ($categorieRisque >= SEUIL_BAS_ATRAITER) && ($categorieRisque <= SEUIL_HAUT_ATRAITER) ) {
											$couleurRisque = COULEUR_RISQUE_ATRAITER;
											$couleurTexteRisque = COULEUR_TEXTE_RISQUE_ATRAITER;
										}
										else if ( ($categorieRisque >= SEUIL_BAS_APLANIFIER) && ($categorieRisque <= SEUIL_HAUT_APLANIFIER) ) {
											$couleurRisque = COULEUR_RISQUE_APLANIFIER;
											$couleurTexteRisque = COULEUR_TEXTE_RISQUE_APLANIFIER;
										}
										$paramLigneRisqueUnitaire['#QUOTATIONCOLOR#'] = $couleurRisque;
										$paramLigneRisqueUnitaire['#QUOTATIONTEXTCOLOR#'] = $couleurTexteRisque;
										$paramLigneRisqueUnitaire['#QUOTATION#'] = $risqueInformations['quotationRisque'];
										$paramLigneRisqueUnitaire['#NOMDANGER#'] = $risqueInformations['nomDanger'];
										$paramLigneRisqueUnitaire['#COMMENTAIRE#'] = $risqueInformations['commentaireRisque'];
										$pageParam['#LIGNESRISQUESUNITAIRES#'] .= EvaDisplayDesign::feedTemplate($risquesUnitairesLignes, $paramLigneRisqueUnitaire);
									}
								}
							}
							$pageParam['#IDTABLE#'] = $tableElement . '-' . $idElement;
							$documentUniqueParam['#CONTENTPAGE#'] = EvaDisplayDesign::feedTemplate($risquesUnitaires, $pageParam);
						}
						else {
							$documentUniqueParam['#CONTENTPAGE#'] = $lastDocument->risquesUnitaires;
						}
					if ( $outputType == 'html' ) {
						$documentUnique .= EvaDisplayDesign::feedTemplate($templatePageDocumentUnique, $documentUniqueParam);
					}

					/*	Introduction risques par unite	*/
					$nbPageTotal++;
					unset($documentUniqueParam);
					$documentUniqueParam['#PAGE#'] = $nbPageTotal . '/#NBPAGETOTAL#';
					$documentUniqueParam['#CONTENTPAGE#'] = EvaDisplayDesign::feedTemplate($introductionRisquesParUnite, $pageParam);
					if ( $outputType == 'html' ) {
						$documentUnique .= EvaDisplayDesign::feedTemplate($templatePageDocumentUnique, $documentUniqueParam);
					}

					/*	Risques par unite	*/
					$nbPageTotal++;
					unset($documentUniqueParam);
					$documentUniqueParam['#PAGE#'] = $nbPageTotal . '/#NBPAGETOTAL#';
					unset($pageParam);
					$bilanParUnite = unserialize($lastDocument->risquesParUnite);
					if ( is_array($bilanParUnite) ) {
						$risqueParUniteDeTravail =
							'<table summary="risqsSummary' . $tableElement . '-' . $idElement . '" cellpadding="0" cellspacing="0" class="widefat post fixed">
								<thead>
									<tr>
										<th>' . __('&Eacute;l&eacute;ment', 'evarisk') . '</th>
										<th>' . __('Somme des quotations', 'evarisk') . '</th>
									</tr>
								</thead>
								<tfoot></tfoot>
								<tbody>
									' . eva_documentUnique::readExportedDatas($bilanParUnite, 'riskByElement', '', 'html') . '
								</tbody>
							</table>';
					}
					else {
						$risqueParUniteDeTravail = $lastDocument->risquesParUnite;
					}
					$pageParam['#RISQUEPARUNITE#'] = $risqueParUniteDeTravail;
					$documentUniqueParam['#CONTENTPAGE#'] = EvaDisplayDesign::feedTemplate($risquesParUnite, $pageParam);
					if ( $outputType == 'html' ) {
						$documentUnique .= EvaDisplayDesign::feedTemplate($templatePageDocumentUnique, $documentUniqueParam);
					}

					/*	Le plan d'action	*/
					$nbPageTotal++;
					unset($documentUniqueParam);
					$documentUniqueParam['#PAGE#'] = $nbPageTotal . '/#NBPAGETOTAL#';
					$documentUniqueParam['#CONTENTPAGE#'] = EvaDisplayDesign::feedTemplate($planDAction, $pageParam);
					if ( $outputType == 'html' ) {
						$documentUnique .= EvaDisplayDesign::feedTemplate($templatePageDocumentUnique, $documentUniqueParam);
					}

					$documentUniqueParam['#NBPAGETOTAL#'] = $nbPageTotal;
					$completeOutput = EvaDisplayDesign::feedTemplate($premiereDeCouvertureDocumentUnique . $documentUnique, $documentUniqueParam);
				}
				break;
			}

			return $completeOutput;
		}
		/** Generate the odt file */
		else if ($outputType == 'odt') {
			ini_set("memory_limit","256M");
			switch ($tableElement) {
				case TABLE_GROUPEMENT:
				{
					$documentUniqueParam['#NOMENTREPRISE#'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent($documentUniqueParam['#NOMENTREPRISE#']));
					$DigiOdf->setVars('nomEntreprise', $documentUniqueParam['#NOMENTREPRISE#']);

					$documentUniqueParam['#NOMPRENOMEMETTEUR#'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent($documentUniqueParam['#NOMPRENOMEMETTEUR#']));
					$DigiOdf->setVars('emetteurDUER', $documentUniqueParam['#NOMPRENOMEMETTEUR#']);

					$documentUniqueParam['#NOMPRENOMDESTINATAIRE#'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent($documentUniqueParam['#NOMPRENOMDESTINATAIRE#']));
					$DigiOdf->setVars('destinataireDUER', $documentUniqueParam['#NOMPRENOMDESTINATAIRE#']);

					$documentUniqueParam['#TELFIXE#'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent($documentUniqueParam['#TELFIXE#']));
					$DigiOdf->setVars('telephone', $documentUniqueParam['#TELFIXE#']);

					$documentUniqueParam['#TELMOBILE#'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent($documentUniqueParam['#TELMOBILE#']));
					$DigiOdf->setVars('portable', $documentUniqueParam['#TELMOBILE#']);

					$documentUniqueParam['#TELFAX#'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent($documentUniqueParam['#TELFAX#']));
					$DigiOdf->setVars('fax', $documentUniqueParam['#TELFAX#']);

					$DigiOdf->setVars('dateGeneration', $documentUniqueParam['#DATE#']);
					$finAudit = '';if(($documentUniqueParam['#FINAUDIT#'] != '') && ($documentUniqueParam['#FINAUDIT#'] != $documentUniqueParam['#DEBUTAUDIT#'])){$finAudit = __(' au ', 'evarisk') . $documentUniqueParam['#FINAUDIT#'];}
					$DigiOdf->setVars('dateAudit', $documentUniqueParam['#DEBUTAUDIT#'] . $finAudit);

					$documentUniqueParam['#DISPODESPLANS#'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent($documentUniqueParam['#DISPODESPLANS#']));
					if (trim($documentUniqueParam['#DISPODESPLANS#']) == '') {
						$documentUniqueParam['#DISPODESPLANS#'] = __('La localisation n\'a pas &eacute;t&eacute; pr&eacute;cis&eacute;e', 'evarisk');
					}

					$DigiOdf->setVars('methodologie', str_replace('<br />', "
", digirisk_tools::slugify_noaccent($documentUniqueParam['#METHODOLOGIE#'])));
					$DigiOdf->setVars('dispoDesPlans', str_replace('<br />', "
", digirisk_tools::slugify_noaccent($documentUniqueParam['#DISPODESPLANS#'])));

					$DigiOdf->setVars('remarqueImportante', str_replace('<br />', "
", digirisk_tools::slugify_noaccent($documentUniqueParam['#ALERTE#'])));

					$DigiOdf->setVars('sources', str_replace('<br />', "
", digirisk_tools::slugify_noaccent($documentUniqueParam['#SOURCES#'])));

					{/*	Remplissage du template pour les groupes d'utilisateurs	*/
						$listeUserGroupe = array();
						$listeDesGroupes = unserialize($lastDocument->groupesUtilisateurs);
						$listeUserGroupe = digirisk_groups::outputGroupListing($listeDesGroupes, 'print');

						$userGroup = $DigiOdf->setdigiSegment('groupesUtilisateurs');
						if($userGroup)
						{
							foreach($listeUserGroupe AS $element)
							{
								$element['userGroupName'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent($element['userGroupName']));
								$element['userGroupName'] = str_replace('&nbsp;', '�', $element['userGroupName']);
								$element['userGroupDescription'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent($element['userGroupDescription']));
								$element['userGroupDescription'] = str_replace('&nbsp;', '�', $element['userGroupDescription']);
								$userGroup->nomGroupe(digirisk_tools::slugify_noaccent($element['userGroupName']));
								$userGroup->descriptionGroupe(digirisk_tools::slugify_noaccent($element['userGroupDescription']));
								$userGroup->nombreUtilisateursGroupe($element['userGroupTotalUserNumber']);
								$userGroup->listeUtilisateur($element['userGroupUsers']);
								$userGroup->merge();
							}
							$DigiOdf->mergedigiSegment($userGroup);
						}
					}

					{/*	Remplissage du template pour les groupes d'utilisateurs affectes	*/
						$listeUserGroupe = array();
						$listeDesGroupesAffectes = unserialize($lastDocument->groupesUtilisateursAffectes);
						$listeUserGroupe = eva_documentUnique::readExportedDatas($listeDesGroupesAffectes, 'affectedUserGroup', '', 'print');

						$userGroupAffected = $DigiOdf->setdigiSegment('groupesUtilisateursAffectes');
						if($userGroupAffected)
						{
							foreach($listeUserGroupe AS $element)
							{
								$element['nomElement'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent($element['nomElement']));

								$userGroupAffected->nomElement($element['nomElement']);
								$userGroupAffected->listeGroupes(digirisk_tools::slugify_noaccent($element['listeGroupes']));
								$userGroupAffected->merge();
							}
							$DigiOdf->mergedigiSegment($userGroupAffected);
						}
					}

					/*	Remplissage du template pour les risques unitaires	*/
					$listeRisques = unserialize($lastDocument->risquesUnitaires);
					$listeRisque = eva_documentUnique::readBilanUnitaire($listeRisques);

					/*	Lecture des types de risques existants	*/
					foreach ($typeRisque as $riskTypeIdentifier => $riskTypeValue) {
						$risque = $DigiOdf->setdigiSegment($riskTypeIdentifier);
						if($risque) {
							$DigiOdf->mergedigiSegment(self::transform_risk_listing ($listeRisque[$riskTypeValue], $risque));
						}
					}

					{/*	Remplissage du template pour les risques par groupement et unit�	*/
						$listeGroupement = array();
						$bilanParUnite = unserialize($lastDocument->risquesParUnite);
						$listeGroupement = eva_documentUnique::readExportedDatas($bilanParUnite, 'riskByElement', '', 'print');

						$risqueParFiche = $DigiOdf->setdigiSegment('risqueFiche');
						if($risqueParFiche)
						{
							foreach($listeGroupement AS $element)
							{
								$element['nomElement'] = str_replace('<br />', "
				", digirisk_tools::slugify_noaccent($element['nomElement']));

								$risqueParFiche->nomElement($element['nomElement']);
								$risqueParFiche->quotationTotale($element['quotationTotale']);

								$risqueParFiche->merge();
							}
							$DigiOdf->mergedigiSegment($risqueParFiche);
						}
					}

					{/*	Remplissage du template pour le plan d'action	*/
						$planDaction = unserialize($lastDocument->plan_d_action);
						$storedPlanDaction = eva_documentUnique::readBilanUnitaire($planDaction['affected'], 'plan_d_action');

						/*	Lecture des types de risques existants pour construction du plan d'action	*/
						foreach($typeRisquePlanAction as $riskTypeIdentifier => $riskTypeValue){
							$planDactionR = $DigiOdf->setdigiSegment($riskTypeIdentifier);
							if($planDactionR){
								if( is_array($storedPlanDaction[$riskTypeValue]) ){
									foreach($storedPlanDaction[$riskTypeValue] AS $elements){
										foreach($elements AS $element){
											$element['nomElement'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent_no_utf8decode($element['nomElement']));
											$element['identifiantRisque'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent_no_utf8decode($element['identifiantRisque']));
											$element['quotationRisque'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent_no_utf8decode($element['quotationRisque']));
											$element['nomDanger'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent_no_utf8decode($element['nomDanger']));
											$element['actionPrevention'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent_no_utf8decode($element['actionPrevention']));

											$planDactionR->setVars('nomElement', $element['nomElement'], true, 'UTF-8');
											$planDactionR->setVars('identifiantRisque', $element['identifiantRisque'], true, 'UTF-8');
											$planDactionR->setVars('quotationRisque', $element['quotationRisque'], true, 'UTF-8');
											$planDactionR->setVars('nomDanger', $element['nomDanger'], true, 'UTF-8');
											$planDactionR->setVars('actionPrevention', $element['actionPrevention'], true, 'UTF-8');

											$planDactionR->merge();
										}
									}
								}
								$DigiOdf->mergedigiSegment($planDactionR);
							}
						}

						$planDactionUA = $DigiOdf->setdigiSegment('planDaction');
						if ( $planDactionUA ) {
							if ( is_array($planDaction['unaffected']) ) {
								foreach ( $planDaction['unaffected'] AS $element ) {
									$element['idAction'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent_no_utf8decode($element['idAction']));
									$element['nomAction'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent_no_utf8decode($element['nomAction']));
									$element['descriptionAction'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent_no_utf8decode($element['descriptionAction']));
									$element['ajoutAction'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent_no_utf8decode($element['ajoutAction']));
									$element['responsableAction'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent_no_utf8decode($element['responsableAction']));
									$element['affectationAction'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent_no_utf8decode($element['affectationAction']));
									$element['etatAction'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent_no_utf8decode($element['etatAction']));

									$planDactionUA->setVars('affectationAction', $element['affectationAction'], true, 'UTF-8');
									$planDactionUA->setVars('idAction', $element['idAction'], true, 'UTF-8');
									$planDactionUA->setVars('etatAction', $element['etatAction'], true, 'UTF-8');
									$planDactionUA->setVars('nomAction', $element['nomAction'], true, 'UTF-8');
									$planDactionUA->setVars('descriptionAction', $element['descriptionAction'], true, 'UTF-8');
									$planDactionUA->setVars('ajoutAction', $element['ajoutAction'], true, 'UTF-8');
									$planDactionUA->setVars('responsableAction', $element['responsableAction'], true, 'UTF-8');

									$planDactionUA->merge();
								}
							}
							$DigiOdf->mergedigiSegment($planDactionUA);
						}
					}

					$finalDir = EVA_RESULTATS_PLUGIN_DIR . 'documentUnique/' . $tableElement . '/' . $idElement . '/';
					$fileName = str_replace(' ', '',$lastDocument->nomDUER) . '_V' . $lastDocument->revisionDUER;
				}
				break;
				case TABLE_GROUPEMENT . '_FGP':
				case TABLE_UNITE_TRAVAIL:
				{
					if ($tableElement == TABLE_GROUPEMENT . '_FGP') {
						$workUnitinformations = EvaGroupement::getGroupement($idElement);

						$DigiOdf->setVars('reference', ELEMENT_IDENTIFIER_GP . $idElement);
						$DigiOdf->setVars('nom', digirisk_tools::slugify_noaccent($workUnitinformations->nom));
						$finalDir = EVA_RESULTATS_PLUGIN_DIR . 'ficheDeGroupement/' . TABLE_GROUPEMENT . '/' . $idElement . '/';
					}
					else {
						$workUnitinformations = eva_UniteDeTravail::getWorkingUnit($idElement);

						$DigiOdf->setVars('referenceUnite', ELEMENT_IDENTIFIER_UT . $idElement);
						$DigiOdf->setVars('nomUnite', digirisk_tools::slugify_noaccent($workUnitinformations->nom));
						$DigiOdf->setVars('reference', ELEMENT_IDENTIFIER_UT . $idElement);
						$DigiOdf->setVars('nom', digirisk_tools::slugify_noaccent($workUnitinformations->nom));
						$finalDir = EVA_RESULTATS_PLUGIN_DIR . 'ficheDePoste/' . $tableElement . '/' . $idElement . '/';
					}

					$DigiOdf->setVars('description', str_replace('<br />', "
", digirisk_tools::slugify_noaccent($lastDocument->description)));
					$DigiOdf->setVars('telephone', str_replace('<br />', "
", digirisk_tools::slugify_noaccent($lastDocument->telephone)));

					$serialized_address = unserialize( $lastDocument->adresse );
					$DigiOdf->setVars( 'adresse', str_replace( '<br />', "
", ( !empty( $serialized_address ) && !empty( $serialized_address[ 'adresse' ] ) ? digirisk_tools::slugify_noaccent( $serialized_address[ 'adresse' ] ) : 'NC' ) ) );
					$DigiOdf->setVars( 'codePostal', str_replace( '<br />', "
", ( !empty( $serialized_address ) && !empty( $serialized_address[ 'codePostal' ] ) ? digirisk_tools::slugify_noaccent( $serialized_address[ 'codePostal' ] ) : 'NC' ) ) );
					$DigiOdf->setVars( 'ville', str_replace( '<br />', "
", ( !empty( $serialized_address ) && !empty( $serialized_address[ 'ville' ] ) ? digirisk_tools::slugify_noaccent( $serialized_address[ 'ville' ] ) : 'NC' ) ) );

					{/*	Remplissage du template pour les utilisateurs affectes	*/
						$listeUser = array();
						$listeUser = unserialize($lastDocument->users);

						$affectedUsers = $DigiOdf->setdigiSegment('utilisateursAffectes');
						if($affectedUsers)
						{
							foreach($listeUser AS $element)
							{
								foreach($element AS $elementInfos)
								{
									if ( empty( $elementInfos['status'] ) || ( "valid" == $elementInfos['status'] ) ) {
										$affectedUsers->setVars('idUtilisateur', ELEMENT_IDENTIFIER_U . $elementInfos['user_id'], true, 'UTF-8');
										$elementInfos['nomUtilisateur'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent($elementInfos['nomUtilisateur']));
										$affectedUsers->setVars('nomUtilisateur', $elementInfos['user_lastname'], true, 'UTF-8');
										$elementInfos['prenomUtilisateur'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent($elementInfos['prenomUtilisateur']));
										$affectedUsers->setVars('prenomUtilisateur', $elementInfos['user_firstname'], true, 'UTF-8');

										$affectedUsers->setVars('dateAffectationUtilisateur', $elementInfos['dateAffectation'], true, 'UTF-8');
										$affectedUsers->setVars('dateDesAffectationUtilisateur', $elementInfos['dateDesaffectation'], true, 'UTF-8');

										$affectedUsers->merge();
									}
								}
							}
							$DigiOdf->mergedigiSegment($affectedUsers);
						}

						$unAffectedUsers = $DigiOdf->setdigiSegment('utilisateursDesaffectes');
						if($unAffectedUsers)
						{
							foreach($listeUser AS $element)
							{
								foreach($element AS $elementInfos)
								{
									if ( empty( $elementInfos['status'] ) || ( "deleted" == $elementInfos['status'] ) || ( "moderated" == $elementInfos['status'] ) ) {
										$unAffectedUsers->setVars('idUtilisateur', ELEMENT_IDENTIFIER_U . $elementInfos['user_id'], true, 'UTF-8');
										$elementInfos['nomUtilisateur'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent($elementInfos['nomUtilisateur']));
										$unAffectedUsers->setVars('nomUtilisateur', $elementInfos['user_lastname'], true, 'UTF-8');
										$elementInfos['prenomUtilisateur'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent($elementInfos['prenomUtilisateur']));
										$unAffectedUsers->setVars('prenomUtilisateur', $elementInfos['user_firstname'], true, 'UTF-8');

										$unAffectedUsers->setVars('dateAffectationUtilisateur', $elementInfos['dateAffectation'], true, 'UTF-8');
										$unAffectedUsers->setVars('dateDesaffectationUtilisateur', $elementInfos['dateDesaffectation'], true, 'UTF-8');

										$unAffectedUsers->merge();
									}
								}
							}
							$DigiOdf->mergedigiSegment($unAffectedUsers);
						}
					}

					{/*	Remplissage du template pour les evaluateurs affectes	*/
						$listeUser = array();
						$listeUser = unserialize($lastDocument->evaluators);

						$affectedEvaluators = $DigiOdf->setdigiSegment('utilisateursPresents');
						if($affectedEvaluators)
						{
							foreach($listeUser AS $element)
							{
								foreach($element AS $elementInfos)
								{
									$affectedEvaluators->setVars('idUtilisateur', ELEMENT_IDENTIFIER_U . $elementInfos['user_id'], true, 'UTF-8');
									$elementInfos['nomUtilisateur'] = str_replace('<br />', "
		", digirisk_tools::slugify_noaccent($elementInfos['nomUtilisateur']));
									$affectedEvaluators->setVars('nomUtilisateur', $elementInfos['user_lastname'], true, 'UTF-8');
									$elementInfos['prenomUtilisateur'] = str_replace('<br />', "
		", digirisk_tools::slugify_noaccent($elementInfos['prenomUtilisateur']));
									$affectedEvaluators->setVars('prenomUtilisateur', $elementInfos['user_firstname'], true, 'UTF-8');

									$affectedEvaluators->setVars('dateEntretien', $elementInfos['dateEntretien'], true, 'UTF-8');
									$affectedEvaluators->setVars('dureeEntretien', $elementInfos['dureeEntretien'], true, 'UTF-8');

									$affectedEvaluators->merge();
								}
							}
							$DigiOdf->mergedigiSegment($affectedEvaluators);
						}
					}

					/*	Remplissage du template pour les groupes d'utilisateurs affectes	*/
						$listeDesGroupesAffectes = array();
						$listeDesGroupesAffectes = unserialize($lastDocument->userGroups);

						$userGroupAffected = $DigiOdf->setdigiSegment('gpUserAffected');
						if ($userGroupAffected) {
							$DigiOdf->mergedigiSegment( self::transform_users_group($listeDesGroupesAffectes, $userGroupAffected) );
						}

					/*	Remplissage du template pour les risques unitaires	*/
						$listeRisques = unserialize($lastDocument->unitRisk);
						$listeRisque = eva_documentUnique::readBilanUnitaire($listeRisques);

						/*	Lecture des types de risques existants	*/
						foreach ($typeRisque as $riskTypeIdentifier => $riskTypeValue) {
							$risque = $DigiOdf->setdigiSegment($riskTypeIdentifier);
							if($risque) {
								$DigiOdf->mergedigiSegment(self::transform_risk_listing($listeRisque[$riskTypeValue], $risque));
							}
						}

					{/*	Remplissage du template pour les pr�conisations afffect�es � l'unit� de travail	*/
						$listePreconisationsAffectees = array();
						$listePreconisationsAffectees = unserialize($lastDocument->recommandation);

						$afffectedRecommandation = $DigiOdf->setdigiSegment('affectedRecommandation');
						if($afffectedRecommandation)
						{
							foreach($listePreconisationsAffectees AS $recommandationCategory)
							{
								if(($recommandationCategory[0]['impressionRecommandationCategorie'] == 'textandpicture') || ($recommandationCategory[0]['impressionRecommandationCategorie'] == 'textonly'))
								{
									$recommandationCategoryName = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent( html_entity_decode( $recommandationCategory[0]['recommandation_category_name'] )));
								}
								else
								{
									$recommandationCategoryName = '';
								}
								$afffectedRecommandation->setVars('recommandationCategoryName', $recommandationCategoryName, true, 'UTF-8');

								if(($recommandationCategory[0]['impressionRecommandationCategorie'] == 'textandpicture') || ($recommandationCategory[0]['impressionRecommandationCategorie'] == 'pictureonly'))
								{
									$recommandationCategoryIcon = evaPhoto::checkIfPictureIsFile($recommandationCategory[0]['recommandation_category_photo'], TABLE_CATEGORIE_PRECONISATION);
									$recommandationCategoryIcon = str_replace(EVA_GENERATED_DOC_URL, EVA_GENERATED_DOC_DIR, $recommandationCategoryIcon);
									$recommandationCategoryIcon = str_replace(EVA_HOME_URL, EVA_HOME_DIR, $recommandationCategoryIcon);
									$afffectedRecommandation->setImage('recommandationCategoryIcon', $recommandationCategoryIcon , $recommandationCategory[0]['tailleImpressionPictoCategorie']);
								}
								else
								{
									$afffectedRecommandation->setVars('recommandationCategoryIcon', '', true, 'UTF-8');
								}

								foreach($recommandationCategory as $recommandation)
								{
									if($recommandationCategory[0]['impressionRecommandation'] == 'pictureonly')
									{
										$recommandation['recommandation_name'] = '';
// 										$recommandation['commentaire'] = '';
									}

									if($recommandation['commentaire'] != '') {
										$recommandation['commentaire'] = " : " . $recommandation['commentaire'] . "
	";
									}
									$afffectedRecommandation->recommandations->setVars('identifiantRecommandation', digirisk_tools::slugify_noaccent(ELEMENT_IDENTIFIER_P . $recommandation['id_preconisation']));
									$afffectedRecommandation->recommandations->setVars('recommandationName', str_replace('<br />', "
	", digirisk_tools::slugify_noaccent( html_entity_decode( $recommandation['recommandation_name'] ))));
									$afffectedRecommandation->recommandations->setVars('recommandationComment', str_replace('<br />', "
	", digirisk_tools::slugify_noaccent( html_entity_decode( $recommandation['commentaire'] ))));

									if (($recommandationCategory[0]['impressionRecommandation'] == 'pictureonly') || ($recommandationCategory[0]['impressionRecommandation'] == 'textandpicture')) {
										$recommandationIcon = evaPhoto::checkIfPictureIsFile($recommandation['photo'], TABLE_PRECONISATION);
										$recommandationIcon = str_replace(EVA_GENERATED_DOC_URL, EVA_GENERATED_DOC_DIR, $recommandationIcon);
										$recommandationIcon = str_replace(EVA_HOME_URL, EVA_HOME_DIR, $recommandationIcon);
										$afffectedRecommandation->recommandations->setImage('recommandationIcon', $recommandationIcon , $recommandationCategory[0]['tailleimpressionRecommandation']);
									}
									else {
										$afffectedRecommandation->recommandations->setVars('recommandationIcon', '');
									}

									$afffectedRecommandation->recommandations->merge();
								}

								$afffectedRecommandation->merge();
							}
							$DigiOdf->mergedigiSegment($afffectedRecommandation);
						}
					}

					if(is_file(EVA_GENERATED_DOC_DIR . $lastDocument->defaultPicturePath)){
						$DigiOdf->setImage('photoDefault', EVA_GENERATED_DOC_DIR . $lastDocument->defaultPicturePath, digirisk_options::getOptionValue('taille_photo_poste_fiche_de_poste'));
					}
					else{
						$DigiOdf->setVars('photoDefault', digirisk_tools::slugify_noaccent(__('Aucun photo d&eacute;finie', 'evarisk')));
					}

					$fileName = str_replace(' ', '',$lastDocument->name) . '_V' . $lastDocument->revision;
				}
				break;
				case TABLE_GROUPEMENT . '_RS' :
				case TABLE_UNITE_TRAVAIL . '_RS' :
						$listing_risk_params = array();
						if ($tableElement == TABLE_GROUPEMENT . '_RS') {
							$current_element = EvaGroupement::getGroupement($idElement);
							$element_identifier = ELEMENT_IDENTIFIER_GP;
							$element_directory = TABLE_GROUPEMENT;
						}
						else {
							$current_element = eva_UniteDeTravail::getWorkingUnit($idElement);
							$element_identifier = ELEMENT_IDENTIFIER_UT;
							$element_directory = TABLE_UNITE_TRAVAIL;
						}
						$DigiOdf->setVars('reference', $element_identifier . $idElement);
						$DigiOdf->setVars('nom', digirisk_tools::slugify_noaccent($current_element->nom));
						$finalDir = EVA_RESULTATS_PLUGIN_DIR . 'listingRisque/' . $element_directory . '/' . $idElement . '/';

						/*	Remplissage du template pour les risques unitaires	*/
						$listeRisques = unserialize($lastDocument->unitRisk);
						$listeRisque = eva_documentUnique::readBilanUnitaire($listeRisques, 'risk_summary');

						/*	Lecture des types de risques existants	*/
						foreach ($typeRisque as $riskTypeIdentifier => $riskTypeValue) {
							$risque = $DigiOdf->setdigiSegment($riskTypeIdentifier);
							if($risque) {
								$DigiOdf->mergedigiSegment(self::transform_risk_listing($listeRisque[$riskTypeValue], $risque));
							}
						}

						$fileName = str_replace(' ', '', $lastDocument->name) . '_V' . $lastDocument->revision;
					break;

				case TABLE_GROUPEMENT . '_FEP' :
				case TABLE_UNITE_TRAVAIL . '_FEP' :
					$doc_user = unserialize( $lastDocument->users );

					$DigiOdf->setVars('identifiantIndividu', ELEMENT_IDENTIFIER_U  . $doc_user['user_informations']['user_id']);
					$DigiOdf->setVars('nomIndividu', str_replace('<br />', "
", remove_accents($doc_user['user_informations']['user_lastname'])));
					$DigiOdf->setVars('prenomIndividu', str_replace('<br />', "
", remove_accents($doc_user['user_informations']['user_firstname'])));
					$DigiOdf->setVars('posteOccupeIndividu', str_replace('<br />', "
", !empty($doc_user['user_informations']['digirisk_information']) && !empty($doc_user['user_informations']['digirisk_information']['user_profession']) ? remove_accents( html_entity_decode($doc_user['user_informations']['digirisk_information']['user_profession'])) : ''));

					$affectations = $doc_user;
					unset( $affectations['user_informations'] );

					if ( !empty($affectations) ) {
						$affectation_list = $DigiOdf->setdigiSegment( 'listAffectationUtilisateur' );
						krsort($affectations);
						foreach ( $affectations as $affectation_id => $affectation_infos) {
							foreach ( $affectation_infos as $key => $value) {
								$value = str_replace('<br />', "
", digirisk_tools::slugify_noaccent( html_entity_decode($value)));
								$affectation_list->setVars($key, $value, true, 'UTF-8');
							}
							$affectation_list->merge();
						}
						$DigiOdf->mergedigiSegment($affectation_list);
					}

					$idUniteDeTravailIndividu = $lastDocument->id_element;
					switch ($lastDocument->table_element) {
						case TABLE_GROUPEMENT;
							$idUniteDeTravailIndividu = ELEMENT_IDENTIFIER_GP . $idUniteDeTravailIndividu;
						break;
						case TABLE_UNITE_TRAVAIL;
							$idUniteDeTravailIndividu = ELEMENT_IDENTIFIER_UT . $idUniteDeTravailIndividu;
						break;
					}
					$DigiOdf->setVars('identifiantUniteDeTravail', str_replace('<br />', "
", remove_accents($idUniteDeTravailIndividu)));
					$DigiOdf->setVars('uniteDeTravailIndividu', str_replace('<br />', "
", remove_accents( html_entity_decode($lastDocument->societyName))));

					$risk_list = unserialize($lastDocument->unitRisk);
					foreach ( $risk_list as $risk_type_line => $risk_detail ) {
						$risque_line = $DigiOdf->setdigiSegment($risk_type_line);
						foreach ( $risk_detail as $risk_id => $risk_line_detail ) {
							if($risque_line) {
								foreach ( $risk_line_detail as $info_key => $info_content ) {
									if ( $info_key != 'details' ) {
										$info_content = str_replace('<br />', "
", digirisk_tools::slugify_noaccent_no_utf8decode($info_content));
										$risque_line->setVars($info_key, $info_content, true, 'UTF-8');
									}
									else if( !empty($info_content) ) {
										foreach ( $info_content as $info_content_key => $info_content_value ) {
											if ( $info_content_key == 'datesAffectationEvaluation' ) {
												$debutExpositionDate = $finExpositionDate = '';
												krsort( $info_content_value );

												foreach ( $info_content_value as $affectation_key => $exposition_date) {
													if ( $exposition_date[ 'dateAffectation' ] <= $info_content['dateFinRisque'] ) {
														$debutExpositionDate .= mysql2date('d/m/Y H:i', $exposition_date[ 'dateAffectation' ], true) . "

";
														$finExpositionDate .= ($exposition_date['dateDesAffectation'] != __('Actuellement expos&eacute;', 'evarisk') ? mysql2date('d/m/Y H:i', $exposition_date[ 'dateDesAffectation' ], true) : $exposition_date['dateDesAffectation']) . "

";
													}
												}
												$risque_line->setVars( 'listeDateDebutExpositionUtilisateur' , html_entity_decode($debutExpositionDate) );
												$risque_line->setVars( 'listeDateFinExpositionUtilisateur' , html_entity_decode($finExpositionDate) );
											}
											else if ( substr($info_content_key, 0, 14) != "recommandation" ) {
												$info_content_value = str_replace('<br />', "
", digirisk_tools::slugify_noaccent_no_utf8decode( stripslashes($info_content_value)));
												$risque_line->setVars($info_content_key, $info_content_value, true, 'UTF-8');
											}
											else {
													$current_preco_type_list = '';
												if ( !empty($info_content_value) && is_array($info_content_value) ) {
													foreach ( $info_content_value as $preco ) {
														if ($info_content_value['impressionRecommandation'] == 'pictureonly') {
															$preco['recommandation_name'] = '';
															//$preco['commentaire'] = '';
														}

														if ($preco['commentaire'] != '') {
															$preco['commentaire'] = " : " . $preco['commentaire'];
														}

// 														if (($preco['impressionRecommandation'] == 'pictureonly') || ($preco['impressionRecommandation'] == 'textandpicture')) {
// 															$recommandationIcon = evaPhoto::checkIfPictureIsFile($preco['photo'], TABLE_PRECONISATION);
// 															$recommandationIcon = str_replace(EVA_GENERATED_DOC_URL, EVA_GENERATED_DOC_DIR, $recommandationIcon);
// 															$recommandationIcon = str_replace(EVA_HOME_URL, EVA_HOME_DIR, $recommandationIcon);
// 															$risque_line->$info_content_key->setImage('recommandationIcon', $recommandationIcon , $preco['tailleimpressionRecommandation']);
// 														}
// 														else {
// 															$risque_line->$info_content_key->setVars('recommandationIcon', '');
// 														}

														$current_preco_type_list .= str_replace('<br />', "
", digirisk_tools::slugify_noaccent( html_entity_decode(ELEMENT_IDENTIFIER_P . $preco['id_preconisation'] . ' - ' . $preco['recommandation_name'] . $preco['commentaire'] ) ) ) . "

";
													}
													$risque_line->setVars('toutes' . $info_content_key, $current_preco_type_list);
												}

												$risque_line->setVars('toutes' . $info_content_key, $current_preco_type_list);
											}
										}
									}
								}
								$risque_line->merge();
							}
						}
						$DigiOdf->mergedigiSegment($risque_line);
					}

					$finalDir = EVA_RESULTATS_PLUGIN_DIR . 'ficheDeRisques/' . $lastDocument->document_final_dir;
					$fileName = str_replace(' ', '', $lastDocument->name) . '_V' . $lastDocument->revision;
				break;
			}

			if(!is_dir($finalDir)){
				wp_mkdir_p($finalDir);
			}
			$DigiOdf->saveToDisk($finalDir . $fileName . '.odt');

			return $finalDir . $fileName . '.odt';
		}
	}

	/**
	 * Transform output for users group
	 *
	 * @param array $liste_groupes The users group list to read and to put into
	 * @param object $DigiOdf_element The element part to fill into document
	 * @return object
	 */
	function transform_users_group($liste_groupes, $DigiOdf_element) {

		foreach ($liste_groupes AS $element) {
			$element['name'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent($element['name']));
			$element['name'] = str_replace('&nbsp;', '�', $element['name']);
			$element['description'] = str_replace('<br />', "
	", digirisk_tools::slugify_noaccent($element['description']));
			$element['description'] = str_replace('&nbsp;', '�', $element['description']);
			$DigiOdf_element->idGroupe(digirisk_tools::slugify_noaccent(ELEMENT_IDENTIFIER_GPU . $element['id']));
			$DigiOdf_element->nomGroupe(digirisk_tools::slugify_noaccent($element['name']));
			$DigiOdf_element->descriptionGroupe(digirisk_tools::slugify_noaccent($element['description']));
			$userList = '';
			if ($element['userList'] == '') {
				$element['userNumber'] = '0';
			}
			else {
				if (substr($element['userList'], -1) == ',') {
					$element['userList'] = substr($element['userList'], 0, -1);
				}
				$groupUsers = explode(',', $element['userList']);
				$element['userNumber'] = count($groupUsers);
				foreach ($groupUsers as $user) {
					if ($user > 0) {
						$userInformations = evaUser::getUserInformation($user);
						$userList .= $userInformations[$user]['user_lastname'] . ' ' . $userInformations[$user]['user_firstname'] . ', ';
					}
				}
			}
			$DigiOdf_element->nombreUtilisateur($element['userNumber']);
			$DigiOdf_element->listeUtilisateur($userList);
			$DigiOdf_element->merge();
		}

		return $DigiOdf_element;
	}

	/**
	 * Generate risk listing
	 *
	 * @param array $listeRisque The element list to use for filling document
	 * @param object $risque The part of output document to fill
	 *
	 * @return object The generated output
	 */
	function transform_risk_listing ($listeRisque, $risque) {
		if ( is_array($listeRisque) ) {
			foreach ($listeRisque AS $elements) {
				foreach ($elements AS $element) {
					$element['nomElement'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent_no_utf8decode($element['nomElement']));
					$element['identifiantRisque'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent_no_utf8decode($element['identifiantRisque']));
					$element['quotationRisque'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent_no_utf8decode($element['quotationRisque']));
					$element['nomDanger'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent_no_utf8decode($element['nomDanger']));
					$element['commentaireRisque'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent_no_utf8decode( stripslashes( $element['commentaireRisque'] )));
					$element['actionPrevention'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent_no_utf8decode($element['actionPrevention']));
					$element['methodeElement'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent_no_utf8decode($element['methodeElement']));
					$element['infoQuotationRisque'] = str_replace('<br />', "
", digirisk_tools::slugify_noaccent_no_utf8decode($element['infoQuotationRisque']));

					$risque->setVars('nomElement', $element['nomElement'], true, 'UTF-8');
					$risque->setVars('identifiantRisque', $element['identifiantRisque'], true, 'UTF-8');
					$risque->setVars('quotationRisque', $element['quotationRisque'], true, 'UTF-8');
					$risque->setVars('nomDanger', $element['nomDanger'], true, 'UTF-8');
					$risque->setVars('commentaireRisque', $element['commentaireRisque'], true, 'UTF-8');
					$risque->setVars('actionPrevention', $element['actionPrevention'], true, 'UTF-8');
					$risque->setVars('methodeElement', $element['methodeElement'], true, 'UTF-8');
					$risque->setVars('infoQuotationRisque', ( !empty( $element['infoQuotationRisque'] ) ? ' - ' . $element['infoQuotationRisque'] : '' ), true, 'UTF-8');

					if(is_file(EVA_GENERATED_DOC_DIR . $element['photoAssociee'])){
						$risque->setImage('photoAssociee', EVA_GENERATED_DOC_DIR . $element['photoAssociee'], digirisk_options::getOptionValue('taille_photo_poste_fiche_de_poste'));
					}
					else{
						$risque->setVars('photoAssociee', digirisk_tools::slugify_noaccent(__('Aucun photo d&eacute;finie', 'evarisk')));
					}

					$risque->merge();
				}
			}
		}

		return $risque;
	}

	/**
	*	Allows to affect documents to corrective actions
	*/
	function document_box_caller(){
		$postBoxTitle = __('Documents', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
		$postBoxId = 'postBoxDocument';
		add_meta_box($postBoxId, $postBoxTitle, array('eva_gestionDoc', 'correctiv_action_document_box'), PAGE_HOOK_EVARISK_TACHE, 'rightSide', 'default');
		add_meta_box($postBoxId, $postBoxTitle, array('eva_gestionDoc', 'correctiv_action_document_box'), PAGE_HOOK_EVARISK_ACTIVITE, 'rightSide', 'default');
	}

	/**
	*	Define a box for associating document to corrective action
	*
	*	@return string $box_content The html output for the current box
	*/
	function correctiv_action_document_box($arguments){
		$box_content = '';

		/*	Check if allwoed extension are set into option 	*/
		$digi_ac_allowed_ext = digirisk_options::getOptionValue('digi_ac_allowed_ext', 'digirisk_options');
		if(is_array($digi_ac_allowed_ext) && (count($digi_ac_allowed_ext) > 0)){
			$idUpload = 'correctiv_action_document_' . $arguments['tableElement'];
			$allowedExtensions = "['" . implode("', '", $digi_ac_allowed_ext) . "']";
			$multiple = true;
			$actionUpload = str_replace('\\', '/', EVA_LIB_PLUGIN_URL . "gestionDocumentaire/uploadFile.php");

			$display_button = true;
			switch($arguments['tableElement']){
				case TABLE_TACHE:
					$repertoireDestination = '';
					$table = $arguments['tableElement'];

					$currentTask = new EvaTask($arguments['idElement']);
					$currentTask->load();
					$ProgressionStatus = $currentTask->getProgressionStatus();

					if((($ProgressionStatus == 'Done') || ($ProgressionStatus == 'DoneByChief')) && (digirisk_options::getOptionValue('possibilite_Modifier_Tache_Soldee') == 'non') ){
						$display_button = false;
						$box_content .= '
			<div class="alignright button-primary clear" id="TaskSaveButton" >
				' . __('Cette t&acirc;che est sold&eacute;e, vous ne pouvez pas ajouter de photos', 'evarisk') . '
			</div>';
					}
					if (!current_user_can('digi_edit_task') && !current_user_can('digi_edit_task_' . $arguments['idElement'])) {
						$display_button = false;
					}
				break;
				case TABLE_ACTIVITE:
					$repertoireDestination = '';
					$table = $arguments['tableElement'];

					$current_action = new EvaActivity($arguments['idElement']);
					$current_action->load();
					$ProgressionStatus = $current_action->getProgressionStatus();

					if((($ProgressionStatus == 'Done') || ($ProgressionStatus == 'DoneByChief')) && (digirisk_options::getOptionValue('possibilite_Modifier_Action_Soldee') == 'non') ){
						$display_button = false;
						$box_content .= '
			<div class="alignright button-primary clear" id="TaskSaveButton" >
				' . __('Cette t&acirc;che est sold&eacute;e, vous ne pouvez pas ajouter de photos', 'evarisk') . '
			</div>';
					}
					if (!current_user_can('digi_edit_action') && !current_user_can('digi_edit_action_' . $arguments['idElement'])) {
						$display_button = false;
					}
				break;
			}

			if($display_button){
				$box_content = __( 'Cette fonctionnalité a été momentanément désactivée. VEuillez nous excuser pour la gêne occasionnée', 'evarisk' );
			}
		}
		else{
			$box_content = sprintf(__('Vous n\'avez pas s&eacute;lectionn&eacute; les extensions &agrave; autoriser. %s', 'evarisk'), '<a href="' . admin_url("options-general.php?page=" . DIGI_URL_SLUG_MAIN_OPTION . "#digirisk_options_correctivaction") . '" >' . __('Ajouter des extensions', 'evarisk') . '</a>');
		}

		$box_content .= '<hr/>';

		/*	Get document already associated to current element	*/
		$correctiv_action_associated_doc = eva_gestionDoc::get_associated_document_list($arguments['tableElement'], $arguments['idElement'], $arguments['tableElement']);
		$box_content .= '
<div >
	<div class="hide digi_' . $arguments['tableElement'] . '_associated_document" >&nbsp;</div>
	<div class="digi_correctiv_action_document_list" >' . $correctiv_action_associated_doc . '</div>
</div>';

		echo $box_content;
	}

	/**
	*	Function allowing to display the complete list of asscoiated coument for a given element
	*/
	function get_associated_document_list($tableElement, $idElement, $document_category = "", $document_order = "dateCreation DESC", $path = '') {
		$document_list_output = '';
		$test_dir = EVA_GENERATED_DOC_DIR;
		$dir_url = EVA_GENERATED_DOC_URL;
		if ( !empty( $path ) && ( $path == EVA_RESULTATS_PLUGIN_DIR ) ) {
			$test_dir = EVA_RESULTATS_PLUGIN_DIR;
			$dir_url = EVA_RESULTATS_PLUGIN_URL;
		}

		$associated_document_list = eva_gestionDoc::getDocumentList($tableElement, $idElement, $document_category, $document_order);
		if(count($associated_document_list) > 0){
		$document_list_output .= '
<table summary="Document list associated to ' . $tableElement . '" cellpadding="0" cellspacing="0" class="associated_document_list" >';
			foreach($associated_document_list as $doc){
				$doc_creator = evaUser::getUserInformation($doc->idCreateur);
				$document_list_output .= '
	<tr id="associated_document_line_' . $doc->id . '" >
		<td>
			&nbsp;&nbsp;&nbsp;- (' . ELEMENT_IDENTIFIER_DOC . $doc->id . ')&nbsp;&nbsp;' . $doc->nom . '
		</td>
		<td class="associated_document_list_download_link" >';
				if(is_file($test_dir . $doc->chemin . $doc->nom)){
					$document_list_output .= '
			<div class="alignright delete_document_button_container" ><img id="delete_document_' . $doc->id . '" src="' . EVA_IMG_ICONES_PLUGIN_URL . 'delete_vs.png" alt="' . __('Supprimer ce document', 'evarisk') . '" title="' . __('Supprimer ce document', 'evarisk') . '" class="alignright delete_associated_document" /></div>
			<span class="ui-icon alignright element_associated_document_info" id="infos_element_associated_document' . $doc->id . '" >&nbsp;</span><a href="' . $dir_url . $doc->chemin . $doc->nom . '" target="associated_document_dl_file" >' . __('T&eacute;l&eacute;charger ce fichier', 'evarisk') . '</a>
			<div class="clear hide associated_document_infos_container alignright" id="element_associated_document' . $doc->id . '" >' . sprintf(__('Ajout&eacute; le %s &agrave; %s par %s', 'evarisk'), mysql2date('d F Y', $doc->dateCreation, true), mysql2date('H:i', $doc->dateCreation, true), $doc_creator[$doc->idCreateur]['user_lastname'] . '&nbsp;' . $doc_creator[$doc->idCreateur]['user_firstname']) . '</div>';
				}
				else{
					$document_list_output .= __('Impossible de trouver le fichier sur le disque', 'evarisk');
				}
				$document_list_output .=
		'</td>
	</tr>';
			}
			$document_list_output .= '
</table>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery(".element_associated_document_info").click(function(){
			jQuery("#" + jQuery(this).attr("id").replace("infos_", "")).toggle();
		});

		jQuery(".delete_associated_document").click(function(){
			if(confirm(digi_html_accent_for_js("' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer ce document?', 'evarisk') . '"))){
				jQuery("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
					"post":"true",
					"table":"' . TABLE_GED_DOCUMENTS . '",
					"tableElement":"' . $tableElement . '",
					"idElement":"' . $idElement . '",
					"act":"delete_document",
					"idDocument":jQuery(this).attr("id").replace("delete_document_", "")
				});
			}
		});
	});
</script>';
		}
		else{
			$document_list_output .= __('Aucun document n\'a &eacute;t&eacute; associ&eacute; pour le moment', 'evarisk');
		}

		return $document_list_output;
	}

	/**
	 *	Generate a form to save work unit sheet collection for a groupment
	 *
	 *	@param mixed $tableElement The element type we want to get form for
	 *	@param integer $idElement The element identifier we wan to get form for
	 *
	 *	@return string The hmtl code outputing the form to generate work unit sheet collection for a groupment
	 */
	function getRiskListingGenerationForm($tableElement, $idElement) {
		$tableElementForDoc = $tableElement . '_RS';
// 		<div id="workUnitSheetCollectionModelSelector" >
// 		<div>
// 		<input type="checkbox" id="modelDefaut" checked="checked" name="modelUse" value="modeleDefaut" />
// 		<label for="modelDefaut" style="vertical-align:middle;" >' . __('Utiliser le mod&egrave;le par d&eacute;faut', 'evarisk') . '</label>
// 		</div>
// 		<div id="modelListForGeneration" style="display:none;" >&nbsp;</div>
// 		</div>

		$output = '
<table summary="" border="0" cellpadding="0" cellspacing="0" align="center" class="tabcroisillon" style="width:100%;" >
	<tr>
		<td id="documentFormContainer" >';
		if ( $tableElement == TABLE_GROUPEMENT ) {
			$output .= '<div><input type="checkbox" checked="checked" class="clear" value="yes" id="recursiv_mode" name="recursiv_mode" /> <label for="recursiv_mode" >' . __('Lister les risques de mani&egrave;re r&eacute;cursive', 'evarisk') . '</label></div>';
		}
		$output .= '<input type="button" class="clear button-primary" value="' . __('G&eacute;n&eacute;rer la synth&egrave;se des risques', 'evarisk') . '" id="save_list_risk" />
		</td>
		<td id="documentModelContainer" >&nbsp;</td>
	</tr>
</table>
<script type="text/javascript" >
	digirisk("#save_list_risk").click(function() {
		var recursiv_mode = false;
		if (jQuery("#recursiv_mode").is(":checked")) {
			recursiv_mode = true;
		}
		digirisk("#documentFormContainer").html(digirisk("#loadingImg").html());
		digirisk("#documentFormContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
			"post":"true",
			"table":"' . TABLE_GED_DOCUMENTS . '",
			"act":"save_list_risk",
			"recursiv_mode": recursiv_mode,
			"tableElement":"' . $tableElement . '",
			"idElement":' . $idElement . ',
			"id_model":digirisk("#modelToUse' . $tableElementForDoc . '").val()
		});
	});

	digirisk("#modelDefaut").click(function(){
		setTimeout(function(){
			if (!digirisk("#modelDefaut").is(":checked")) {
				digirisk("#documentModelContainer").html(\'<img src="' . EVA_IMG_DIVERS_PLUGIN_URL . 'loading.gif" alt="loading" />\');
				digirisk("#documentModelContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_DUER . '", "act":"loadNewModelForm", "tableElement":"' . $tableElementForDoc . '", "idElement":"' . $idElement . '"});
				digirisk("#modelListForGeneration").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_GED_DOCUMENTS . '", "act":"load_model_combobox", "tableElement":"' . $tableElementForDoc . '", "idElement":"' . $idElement . '", "category":"fiche_de_poste", "selection":""});
				digirisk("#modelListForGeneration").show();
			}
			else {
				digirisk("#documentModelContainer").html("");
				digirisk("#modelListForGeneration").html("");
				digirisk("#modelListForGeneration").hide();
			}
		},600);
	});
</script>';

		return $output;
	}

	/**
	 *	Generate a form to save work unit sheet collection for a groupment
	 *
	 *	@param mixed $tableElement The element type we want to get form for
	 *	@param integer $idElement The element identifier we wan to get form for
	 *
	 *	@return string The hmtl code outputing the form to generate work unit sheet collection for a groupment
	 */
	function get_form_penibilite_generation($tableElement, $idElement) {
		$output = '
<table summary="" border="0" cellpadding="0" cellspacing="0" align="center" class="tabcroisillon" style="width:100%;" >
	<tr>
		<td id="documentFormContainer" >';
		if ( $tableElement == TABLE_GROUPEMENT ) {
		//	$output .= '<div><input type="checkbox" checked="checked" class="clear" value="yes" id="recursiv_mode" name="recursiv_mode" /> <label for="recursiv_mode" >' . __('Lister les risques de mani&egrave;re r&eacute;cursive', 'evarisk') . '</label></div>';
		}
		$output .= '
			<div>
				<input type="checkbox" id="modelDefaut" checked="checked" name="modelUse" value="modeleDefaut" />
				<label for="modelDefaut" style="vertical-align:middle;" >' . __('Utiliser le mod&egrave;le par d&eacute;faut', 'evarisk') . '</label>
			</div>
			<div id="modelListForGeneration" style="display:none;" >&nbsp;</div>
			<input type="button" class="clear button-primary" value="' . __('G&eacute;n&eacute;rer les fiches de p&eacute;nibilit&eacute;', 'evarisk') . '" id="save_fiche_penibilite" />
		</td>
		<td id="documentModelContainer" >&nbsp;</td>
	</tr>
</table>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#save_fiche_penibilite").click(function() {
			var recursiv_mode = false;
			if (jQuery("#recursiv_mode").is(":checked")) {
				recursiv_mode = true;
			}
			var model = jQuery("#modelToUse' . $tableElement . '_FEP").val();
			jQuery("#documentFormContainer").html( digirisk("#loadingImg").html() );
			var data = {
				action: "digi_ajax_save_document",
				element_type: "' . $tableElement . '",
				element_id: ' . $idElement . ',
				recursiv_mode: recursiv_mode,
				id_model: model,
				document_type: "fiche_exposition_penibilite",
			};
			jQuery.post(ajaxurl, data, function(response){
				jQuery("#subTabSelector").val("FEP");
				jQuery("#ongletHistoriqueDocument").click();
			}, "json");
		});

		jQuery("#modelDefaut").click(function() {
			setTimeout(function(){
				if (!digirisk("#modelDefaut").is(":checked")) {
					jQuery("#documentModelContainer").html(\'<img src="' . EVA_IMG_DIVERS_PLUGIN_URL . 'loading.gif" alt="loading" />\');
					jQuery("#documentModelContainer").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_DUER . '", "act":"loadNewModelForm", "tableElement":"' . $tableElement . '_FEP", "idElement":"' . $idElement . '"});
					jQuery("#modelListForGeneration").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {"post":"true", "table":"' . TABLE_GED_DOCUMENTS . '", "act":"load_model_combobox", "tableElement":"' . $tableElement . '_FEP", "idElement":"' . $idElement . '", "category":"fiche_exposition_penibilite", "selection":""});
					jQuery("#modelListForGeneration").show();
				}
				else {
					jQuery("#documentModelContainer").html("");
					jQuery("#modelListForGeneration").html("");
					jQuery("#modelListForGeneration").hide();
				}
			},600);
		});
	});
</script>';

		return $output;
	}

	/**
	 *	Get the history of work unit sheet generated for a given element
	 *
	 *	@param mixed $tableElement The element type we want to get form for
	 *	@param integer $idElement The element identifier we wan to get form for
	 *
	 *	@return string The html code output with the list of document or a message saying there no document for this element
	 */
	function getRiskListingGenerationHistory($tableElement, $idElement) {
		$output = '';

		$ficheDePoste_du_Groupement = eva_gestionDoc::getDocumentList($tableElement, $idElement, 'fiche_de_poste_groupement', "dateCreation DESC");
		if (count($ficheDePoste_du_Groupement) > 0) {
			foreach ($ficheDePoste_du_Groupement as $fdpGpt) {
				if (is_file(EVA_GENERATED_DOC_DIR . $fdpGpt->chemin . $fdpGpt->nom)) {
					$output .= '-&nbsp;' . sprintf(__('G&eacute;n&eacute;r&eacute; le %s: (%s) <a href="%s" >%s</a>', 'evarisk'), mysql2date('d M Y', $fdpGpt->dateCreation, true), ELEMENT_IDENTIFIER_GFP . $fdpGpt->id, EVA_GENERATED_DOC_URL . $fdpGpt->chemin . $fdpGpt->nom, $fdpGpt->nom) . '<br/>';
				}
			}
		}
		else {
			$output .= __('Aucune fiche n\'a &eacute;t&eacute; cr&eacute;e pour le moment', 'evarisk');
		}

		return $output;
	}


	function digi_ajax_export_csv_file() {
		global $wpdb;
		$output_message = '';
		$column_separator = ";";
		$column_separator_replacer = ",";
		$available_fields = unserialize( DIGI_AVAILABLE_FIELDS_FOR_EXPORT );
		$export_type = digirisk_tools::IsValid_Variable( $_POST['export_type'] );
		$column_to_export = !empty($_POST['column_to_export']) ? $_POST['column_to_export'] : null;
		$save_single_file = false;

		if ( !empty($column_to_export) ) {
			$files_to_zip = array();
			$user_list = evaUser::getCompleteUserList();
			if ( !empty($user_list) ) {
				foreach ( $user_list as $user_id => $user_informations ) {
					$file_line[$user_id] = array();
					$groupement_affectation = evaUserLinkElement::get_user_affected_element( $user_id, TABLE_GROUPEMENT, "'valid'");
					$workUnit_affectation = evaUserLinkElement::get_user_affected_element( $user_id, TABLE_UNITE_TRAVAIL, "'valid'");
					$user_all_affectation = array_merge($groupement_affectation, $workUnit_affectation);
					$file_have_only_one_line = false;
					if ( !empty( $user_all_affectation ) ) {
						$i = 1;
						foreach ( $user_all_affectation as $affectation_informations ) {
							/**	Add user information column in some case */
							if ( in_array($export_type, array('global')) ) {
								$file_line[$user_id][$i]['user_identifier'] = ELEMENT_IDENTIFIER_U . $user_id;
								$file_line[$user_id][$i]['user_lastname'] = $user_informations['user_lastname'];
								$file_line[$user_id][$i]['user_firstname'] = $user_informations['user_firstname'];
							}

							/**	Get user affectation informations	*/
							switch ( $affectation_informations->table_element ) {
								case TABLE_GROUPEMENT :
									$element_identifier = ELEMENT_IDENTIFIER_GP;
									$element_complete_infos = EvaGroupement::getGroupement( $affectation_informations->id_element );
								break;
								case TABLE_UNITE_TRAVAIL :
									$element_identifier = ELEMENT_IDENTIFIER_UT;
									$element_complete_infos = eva_UniteDeTravail::getWorkingUnit( $affectation_informations->id_element );
								break;
							}
							if ( in_array( 'ref_elt', $column_to_export ) ) {
								$file_line[$user_id][$i]['ref_elt'] = $element_identifier . $affectation_informations->id_element;
							}
							if ( in_array( 'name_elt', $column_to_export ) ) {
								$file_line[$user_id][$i]['name_elt'] = str_replace($column_separator, $column_separator_replacer, $element_complete_infos->nom);
							}
							if ( in_array( 'affectation_date', $column_to_export ) ) {
								$file_line[$user_id][$i]['affectation_date'] = (!empty($affectation_informations->date_affectation_reelle) && ($affectation_informations->date_affectation_reelle != '0000-00-00 00:00:00')) ? $affectation_informations->date_affectation_reelle : $affectation_informations->date_affectation;
							}
							if ( in_array( 'unaffectation_date', $column_to_export ) ) {
								$file_line[$user_id][$i]['unaffectation_date'] = (!empty($affectation_informations->date_desaffectation_reelle) && ($affectation_informations->date_desaffectation_reelle != '0000-00-00 00:00:00')) ? $affectation_informations->date_desaffectation_reelle : ((!empty($affectation_informations->date_desAffectation) && ($affectation_informations->date_desAffectation != '0000-00-00 00:00:00')) ? $affectation_informations->date_desAffectation : __('Actuellement affecte', 'evarisk'));
							}

							/**	For each affectation get existing risks	*/
							if ( in_array('ref_risk' , $column_to_export ) || in_array( 'risk_comment', $column_to_export ) || in_array( 'risk_status', $column_to_export ) || in_array( 'risk_cotation', $column_to_export ) ) {
								$risk_list_for_element = Risque::getRisques($affectation_informations->table_element, $affectation_informations->id_element, 'all', '1', 'tableRisque.id ASC', "'Valid'");
								$risques = array();
								if ( !empty($risk_list_for_element) ) {
									foreach ($risk_list_for_element as $risque) {
										$risques[$risque->id][] = $risque;
									}
								}
								/**	If there are risks we read them	*/
								if ( !empty($risques) ) {
									$j = 1;
									unset( $tmpLigneDeValeurs );
									foreach ( $risques as $risque_id => $risque ) {
										$idMethode = $risque[0]->id_methode;
										$score = Risque::getScoreRisque($risque);
										$quotation = Risque::getEquivalenceEtalon($idMethode, $score, $risque[0]->date);
										$niveauSeuil = Risque::getSeuil($quotation);

										if ( $j > 1 ) {
											$i++;
											$file_line[$user_id][$i] = $file_line[$user_id][$i - 1];
										}

										if ( in_array( 'ref_risk', $column_to_export ) ) {
											$file_line[$user_id][$i]['ref_risk'] = ELEMENT_IDENTIFIER_R . $risque_id . ' - ' . ELEMENT_IDENTIFIER_E . $risque[0]->id_evaluation;
										}
										if ( in_array( 'risk_cotation', $column_to_export ) ) {
											$file_line[$user_id][$i]['risk_cotation'] = $quotation;
										}
										if ( in_array( 'risk_comment', $column_to_export ) ) {
											$last_comment_output = '';
											$query = $wpdb->prepare("SELECT date_ajout, commentaire, date FROM " . TABLE_ACTIVITE_SUIVI . " WHERE status = 'valid' AND table_element = %s AND id_element IN (SELECT id_evaluation FROM " . TABLE_AVOIR_VALEUR . " WHERE id_risque = %d) ORDER BY date_ajout DESC", TABLE_AVOIR_VALEUR, $risque_id);
											$last_comments = $wpdb->get_results($query);
											if ( !empty($last_comments) ) {
												foreach ( $last_comments as $last_comment ) {
													$last_comment_output .= mysql2date('d F Y H:i:s', ($last_comment->date_ajout != '0000-00-00 00:00:00' ? $last_comment->date_ajout : $last_comment->date), true) . ' : ' . $last_comment->commentaire . "
";
												}
											}

											$file_line[$user_id][$i]['risk_comment'] = str_replace($column_separator, $column_separator_replacer, $last_comment_output );
										}
										if ( in_array( 'risk_status', $column_to_export ) ) {
											$query = $wpdb->prepare( "SELECT choix_danger FROM " . TABLE_DANGER . " WHERE id = %d", $risque[0]->id_danger );
											$choix_danger = $wpdb->get_var( $query );
											$danger_state = unserialize( $choix_danger );
											$penibility_level = get_option('digi_risk_penibility_level', 51);
											$file_line[$user_id][$i]['risk_status'] = (!empty($danger_state) && is_array($danger_state) && (in_array('penibilite', $danger_state)) ? ( !empty($quotation) && ($quotation > $penibility_level) ? __('Oui', 'evarisk') : __('Non', 'evarisk') ) : __('Danger non soumis a penibilite', 'evarisk') );
										}

										$j++;
									}
								}
							}
							$i++;
						}
					}
					else {
						switch ($export_type) {
							case 'global' :
								$file_line[$user_id][] = '"'. ELEMENT_IDENTIFIER_U . $user_id . '"' . $column_separator . '"'. $user_informations['user_lastname'] . '"' . $column_separator . '"'. $user_informations['user_firstname'] . '"' . $column_separator . '"' . __('Cet utilisateur n\'a pas eu d\'affectation pour le moment', 'evarisk') . '"' . $column_separator . str_repeat('"-"' . $column_separator, (count($column_to_export) - 4));
								break;
							default:
								$file_line[$user_id][] = '"' .__('Cet utilisateur n\'a pas eu d\'affectation pour le moment', 'evarisk') . '"' . $column_separator . str_repeat('"-"' . $column_separator, (count($column_to_export) - 1));
								break;
						}
						$file_have_only_one_line = true;
					}

					if ( !empty( $file_line[$user_id] ) && !empty( $column_to_export ) ) {
						switch ($export_type) {
							case 'user':
								$the_file = array();

								$file_header = array();
								foreach ( $column_to_export as $column_code ) {
									$file_header[] = html_entity_decode($available_fields[$column_code]);
								}
								$the_file[] = utf8_encode( '"' . implode('"' . $column_separator . '"', $file_header) . '"' );

								$i = 1;
								foreach ( $file_line[$user_id] as $line_column_key => $line_column_value ) {
									if ( !$file_have_only_one_line ) {
										$line_content = array();
										foreach ( $column_to_export as $column_name ) {
											$line_content[] = (!empty($file_line[$user_id][$line_column_key][$column_name]) ? $file_line[$user_id][$line_column_key][$column_name] : '-');
										}
										$the_file[$i] = '"' . implode('"' . $column_separator . '"', $line_content) . '"';
									}
									else {
										$the_file[] = $line_column_value;
									}
									$i++;
								}

								if ( !empty($the_file) ) {
									/** Check the model to use for document	*/
									$modelToUse = '0';
									$document_final_name = '_user_summary';
									$model_shape = 'user_summary_file';
									$idElement = $user_id;
									$tableElement = $wpdb->users;
									$affected_user = $user_id;
									$current_type_identifier = ELEMENT_IDENTIFIER_GUS;
									$path_to_file = 'users/' . $user_id;

									/** Retrieve last revision for the document to generate	*/
									$revision = '';
									$query = $wpdb->prepare(
											"SELECT max(revision) AS lastRevision
										FROM " . TABLE_FP . "
										WHERE table_element = %s
											AND id_element = %d
											AND affected_user = %d
											AND document_type = %s
											AND REPLACE( SUBSTRING( `creation_date` , 1, 10 ) , '-', '' ) = %s",
											$tableElement, $idElement, $affected_user, $model_shape, str_replace('-', '', substr(current_time('mysql', 0), 0, 10)));
									$revision = $wpdb->get_row($query);
									$revisionDocument = $revision->lastRevision + 1;

									/** Generate document name from given parameters	*/
									$dateElement = (!empty($informations) && !empty($informations['dateCreation']) ? $informations['dateCreation'] : current_time('mysql', 0));
									$ExplodedDateElement = explode(' ', $dateElement);
									$referenceDocument = ELEMENT_IDENTIFIER_U . $user_id . '-V' . $revisionDocument;
									$documentName = str_replace('-', '', $ExplodedDateElement[0]) . $document_final_name . '_' . $referenceDocument . '_' . sanitize_title( $user_informations['user_lastname'] . '_' . $user_informations['user_firstname'] );
									$informations['nomDuDocument'] = $documentName;

									/**	Enregistrement du document	*/
									$new_sheet_params = array();
									$new_sheet_params['id'] 					= '';
									$new_sheet_params['creation_date'] 			= current_time('mysql', 0);
									$new_sheet_params['revision'] 				= $revisionDocument;
									$new_sheet_params['id_element'] 			= $idElement;
									$new_sheet_params['id_model'] 				= $modelToUse;
									$new_sheet_params['table_element'] 			= $tableElement;
									$new_sheet_params['reference'] 				= $referenceDocument;
									$new_sheet_params['name'] 					= digirisk_tools::slugify_noaccent( $informations['nomDuDocument'] );
									$new_sheet_params['description'] 			= '';
									$new_sheet_params['adresse']				= '';
									$new_sheet_params['telephone'] 				= '';
									$new_sheet_params['defaultPicturePath'] 	= '';
									$new_sheet_params['societyName'] 			= '';
									$new_sheet_params['users'] 					= '';
									$new_sheet_params['userGroups'] 			= '';
									$new_sheet_params['evaluators'] 			= '';
									$new_sheet_params['evaluatorsGroups'] 		= '';
									$new_sheet_params['unitRisk'] 				= serialize( $the_file );
									$new_sheet_params['recommandation'] 		= '';
									$new_sheet_params['document_type'] 			= $model_shape;
									$new_sheet_params['affected_user'] 			= $user_id;
									$new_sheet_params['document_final_dir'] 	= $path_to_file;

									$new_sheet = $wpdb->insert(TABLE_FP, $new_sheet_params);
									if ( $new_sheet === false ) {
										$output_message = __("Une erreur est survenue lors de l'enregistrement", 'evarisk');
									}
									else {
										$status['result'] = 'ok';
										if ( !is_dir( EVA_RESULTATS_PLUGIN_DIR . $path_to_file ) ) {
											mkdir( EVA_RESULTATS_PLUGIN_DIR . $path_to_file, 0755, true);
										}
										/*	Save the odt file	*/
										$summary_filename = EVA_RESULTATS_PLUGIN_DIR . $path_to_file . '/' . $documentName . '.csv';
										$file_to_write = fopen( $summary_filename, 'w');
										fwrite($file_to_write , implode("
", $the_file) );
										$files_to_zip[] = $summary_filename;
										$output_message = __("L'export a bien &eacute;t&eacute; effectu&eacute;", 'evarisk');
									}
								}
							break;
						}
					}
				}

				if ( !empty( $file_line[$user_id] ) && !empty( $column_to_export ) ) {
					switch ($export_type) {
						case 'user':
							$result_list_output = eva_GroupSheet::getGroupSheetCollectionHistory( 'all', 0, $model_shape, $current_type_identifier );
						break;

						case 'tree_element':

						break;

						case 'global':
							$the_file = array();

							$file_header = array();
							foreach ( $column_to_export as $column_code ) {
								$file_header[] = html_entity_decode($available_fields[$column_code]);
							}
							$the_file[] = utf8_encode( '"' . implode('"' . $column_separator . '"', $file_header) . '"' );

							$i = 1;
							ksort($file_line);
							foreach ( $file_line as $user_id => $user_details ) {
								foreach ( $user_details as $line_column_key => $line_column_value ) {
									if ( count($user_details) >= 1 ) {
										$line_content = array();
										foreach ( $column_to_export as $column_name ) {
											$line_content[] = (!empty($user_details[$line_column_key][$column_name]) ? $user_details[$line_column_key][$column_name] : '-');
										}
										$the_file[$i] = '"' . implode('"' . $column_separator . '"', $line_content) . '"';
									}
									else {
										$the_file[] = $line_column_value;
									}
									$i++;
								}
							}

							if ( !empty($the_file) ) {
								/** Check the model to use for document	*/
								$modelToUse = '0';
								$document_final_name = '_user_global_export';
								$model_shape = 'user_global_export';
								$idElement = 0;
								$tableElement = 'all';
								$affected_user = 0;
								$current_type_identifier = ELEMENT_IDENTIFIER_GUE;
								$path_to_file = 'users';

								/** Retrieve last revision for the document to generate	*/
								$revision = '';
								$query = $wpdb->prepare(
										"SELECT max(revision) AS lastRevision
										FROM " . TABLE_FP . "
										WHERE table_element = %s
											AND id_element = %d
											AND affected_user = %d
											AND document_type = %s
											AND REPLACE( SUBSTRING( creation_date , 1, 10 ) , '-', '' ) = %s",
										$tableElement, $idElement, $affected_user, $model_shape, str_replace('-', '', substr(current_time('mysql', 0), 0, 10)));
								$revision = $wpdb->get_row($query);
								$revisionDocument = $revision->lastRevision + 1;

								/** Generate document name from given parameters	*/
								$dateElement = (!empty($informations) && !empty($informations['dateCreation']) ? $informations['dateCreation'] : current_time('mysql', 0));
								$ExplodedDateElement = explode(' ', $dateElement);
								$referenceDocument = ELEMENT_IDENTIFIER_GUE . '-V' . $revisionDocument;
								$documentName = str_replace('-', '', $ExplodedDateElement[0]) . $document_final_name . '_' . $referenceDocument;

								/**	Enregistrement du document	*/
								$new_sheet_params = array();
								$new_sheet_params['id'] 					= '';
								$new_sheet_params['creation_date'] 			= current_time('mysql', 0);
								$new_sheet_params['revision'] 				= $revisionDocument;
								$new_sheet_params['id_element'] 			= $idElement;
								$new_sheet_params['id_model'] 				= $modelToUse;
								$new_sheet_params['table_element'] 			= $tableElement;
								$new_sheet_params['reference'] 				= $referenceDocument;
								$new_sheet_params['name'] 					= digirisk_tools::slugify_noaccent( $documentName );
								$new_sheet_params['description'] 			= '';
								$new_sheet_params['adresse']				= '';
								$new_sheet_params['telephone'] 				= '';
								$new_sheet_params['defaultPicturePath'] 	= '';
								$new_sheet_params['societyName'] 			= '';
								$new_sheet_params['users'] 					= '';
								$new_sheet_params['userGroups'] 			= '';
								$new_sheet_params['evaluators'] 			= '';
								$new_sheet_params['evaluatorsGroups'] 		= '';
								$new_sheet_params['unitRisk'] 				= serialize( $the_file );
								$new_sheet_params['recommandation'] 		= '';
								$new_sheet_params['document_type'] 			= $model_shape;
								$new_sheet_params['affected_user'] 			= $affected_user;
								$new_sheet_params['document_final_dir'] 	= $path_to_file;

								$new_sheet = $wpdb->insert(TABLE_FP, $new_sheet_params);
								if ( $new_sheet === false ) {
									$output_message = __("Une erreur est survenue lors de l'enregistrement", 'evarisk');
								}
								else {
									if ( !is_dir( EVA_RESULTATS_PLUGIN_DIR . $path_to_file ) ) {
										mkdir( EVA_RESULTATS_PLUGIN_DIR . $path_to_file, 0755, true);
									}
									/*	Save the odt file	*/
									$summary_filename = EVA_RESULTATS_PLUGIN_DIR . $path_to_file . '/' . $documentName . '.csv';
									$file_to_write = fopen( $summary_filename, 'w');
									fwrite($file_to_write , implode("
", $the_file) );
									$output_message = __("L'export a bien &eacute;t&eacute; effectu&eacute;", 'evarisk');
								}
							}

							$result_list_output = eva_gestionDoc::getGeneratedDocument('all', 0, 'list', '', 'user_global_export', '0');
						break;
					}
				}
			}
			else {
				$output_message = __('Il n\'y a aucun utilisateur dans votre installation de Digirisk', 'evarisk');
			}

			if ( !empty($files_to_zip) ) {
				$pathToZip = EVA_RESULTATS_PLUGIN_DIR . 'users/summary/';
				if ( !is_dir( $pathToZip ) ) {
					mkdir( $pathToZip, 0755, true);
				}
				/**	ZIP THE FILE	*/
				$zipFileName = str_replace( '-', '', sanitize_title( current_time('mysql', 0) ) ) . $document_final_name . '.zip';
				$archive = new eva_Zip( $zipFileName );
				$archive->setFiles( $files_to_zip );
				$archive->compressToPath( $pathToZip );
				$saveWorkSheetUnitStatus = eva_gestionDoc::saveNewDoc( $model_shape, 'all', 0, str_replace(EVA_GENERATED_DOC_DIR, '', $pathToZip . $zipFileName) );
			}


		}
		else {
			$output_message = __('Vous n\'avez choisi aucune colonne &agrave; exporter', 'evarisk');
		}

		echo json_encode( array( $output_message, $result_list_output ) );
		die();
	}

	function digi_ajax_save_document() {
		$status = false;
		$output = '';

		switch ( $_POST['document_type'] ) {
			case 'fiche_exposition_penibilite':
				$penibility_sheet_generation = eva_gestionDoc::generate_fiche_penibilite($_POST['element_type'], $_POST['element_id']);
				if ($penibility_sheet_generation['result'] != 'error') {
					$status = true;
					$output = __('Fiche(s) g&eacute;n&eacute;r&eacute;es avec succ&eacute;s', 'evarisk');
				}
			break;
		}

		echo json_encode( array($status, $output) );
		die();
	}

}