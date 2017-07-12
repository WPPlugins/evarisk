<?php
/**
 *
 *
 * @author Evarisk
 * @version v5.0
 */
include_once(EVA_CONFIG );

class EvaPhoto {

	/**
	 * Returns all photos of the element
	 * @param string $tableElement Table name of the element
	 * @param int $idElement Id of the element in the table
	 * @param string $where SQL where condition
	 * @param string $order SQL order condition
	 * @return The photos of the element maching with the where condition and order by the order condition
	 */
	function getPhotos($tableElement, $idElement, $where = "1", $order = "PICTURE.id ASC")
	{
		global $wpdb;

		$tableElement = digirisk_tools::IsValid_Variable($tableElement);
		$idElement = digirisk_tools::IsValid_Variable($idElement);
		$where = digirisk_tools::IsValid_Variable($where);
		$order = digirisk_tools::IsValid_Variable($order);

		$query = $wpdb->prepare(
			"SELECT PICTURE.*, PICTURE_LINK.isMainPicture
			FROM " . TABLE_PHOTO . " AS PICTURE
				INNER JOIN " . TABLE_PHOTO_LIAISON . " AS PICTURE_LINK ON (PICTURE_LINK.idPhoto = PICTURE.id)
			WHERE PICTURE_LINK.tableElement='" . ($tableElement) . "'
				AND PICTURE_LINK.idElement='" . ($idElement) . "'
				AND PICTURE_LINK.status = 'valid'
				AND " . $where . "
			ORDER BY " . $order, "");
		$photos = $wpdb->get_results($query);

		return $photos;
	}

/*
  * Persistance
  */

	/**
	 * Save a new picture.
	 * @param string $tableElement Table name of the element
	 * @param int $idElement Id of the element in the table
	 * @param mixed $photo The path to the picture
	 * @return mixed $status The picture identifier if the photo is well insert and "error" else.
	 */
	function saveNewPicture($tableElement, $idElement, $photo){
		global $wpdb;
		$status = 'error';

		if ( function_exists( 'current_user_can' ) && function_exists( 'wp_get_current_user' ) ) {
			if ( !current_user_can( 'upload_files' ) ) {
				return 	$status;
			}
		}

		$digirisk_tools = new digirisk_tools();
		$tableElement = $digirisk_tools->IsValid_Variable($tableElement);

		$digirisk_tools = new digirisk_tools();
		$idElement = $digirisk_tools->IsValid_Variable($idElement);

		$digirisk_tools = new digirisk_tools();
		$photo = $digirisk_tools->IsValid_Variable($digirisk_tools->slugify(str_replace(str_replace('\\', '/', EVA_GENERATED_DOC_DIR), '', $photo)));

		/*	Check if the picture have already been inserted into database	*/
		$query = $wpdb->prepare("SELECT id FROM ".TABLE_PHOTO." WHERE photo=%s", $photo);
		$picture_id = $wpdb->get_var($query);
		if(empty($picture_id)){
			$insert_picture_query = $wpdb->insert( TABLE_PHOTO, array( 'id' => null, 'photo' => $photo ) );
			if ( false !== $insert_picture_query ) {
				$picture_id = $wpdb->insert_id;
				$status = evaPhoto::associatePicture($tableElement, $idElement, $picture_id);
				switch($tableElement){
					case TABLE_ACTIVITE:
					case TABLE_TACHE:
						/*	Log modification on element and notify user if user subscribe	*/
						digirisk_user_notification::log_element_modification($tableElement, $idElement, 'picture_add', '', $picture_id);
					break;
				}
			}
		}
		else{
			$status = evaPhoto::associatePicture($tableElement, $idElement, $picture_id);
		}


		return $status;
	}

	/**
	 * Associate a picture to an element.
	 * @param string $tableElement Table name of the element
	 * @param int $idElement Id of the element in the table
	 * @param int $pictureId The picture identifier we want to associate
	 * @return mixed $status The picture identifier if the photo is well insert and "error" else.
	 */
	function associatePicture($tableElement, $idElement, $pictureId){
		global $wpdb;
		$status = 'error';

		$tableElement = digirisk_tools::IsValid_Variable($tableElement);
		$idElement = digirisk_tools::IsValid_Variable($idElement);

		$picture_accociation_query = $wpdb->insert( TABLE_PHOTO_LIAISON, array( 'id' => null, 'status' => 'valid', 'isMainPicture' => 'no', 'idPhoto' => $pictureId, 'idElement' => $idElement, 'tableElement' => $tableElement, )  );
		if ( false !== $picture_accociation_query ) {
			$status = $pictureId;
		}

		return $status;
	}

	/**
	*	Delete a picture. Put the picture status to "deleted"
	*
	*	@param integer $idPhoto The picture identifier to delete
	*
	*	@return mixed $status The result status ("ok" if all is ok, "error" is there is an error)
	*/
	function unAssociatePicture($tableElement, $idElement, $idPhoto)
	{
		global $wpdb;
		$status = 'error';

		$dissociate_picture = $wpdb->update(
			TABLE_PHOTO_LIAISON,
			array( 'status' => 'deleted', ),
			array( 'tableElement' => $tableElement, 'idElement' => $idElement, 'idPhoto' => $idPhoto, )
		);
		if( false !== $dissociate_picture ){
			$status = 'ok';
		}

		return $status;
	}

	/**
	*	Return a picture gallery for a given element, with the different action for each picture
	*
	*	@param mixed $tableElement The element type we want to get the gallery for
	*	@param integer $idElement The element identifier we want to get the gallery for
	*
	*	@return mixed $gallery The html code width the different element for the picture gallery
	*/
	function getGallery($tableElement, $idElement){
		$moreOutputOptions = '';
		$gallery = '
			<div id="galeriePhoto' . $tableElement . $idElement .'">
				<div class="galeryPhoto alignleft">
					<div id="thumbs' . $tableElement . $idElement .'" class="navigation" >
						<ul class="thumbs noscript clear">';

		$photos = evaPhoto::getPhotos($tableElement, $idElement);
		foreach($photos as $photo){
			$isFile = 'notAfile';
			if(is_file(EVA_GENERATED_DOC_DIR . $photo->photo))
			{
				$isFile = 'wpContent';
			}
			elseif(is_file(EVA_HOME_DIR . $photo->photo))
			{
				$isFile = 'evaContent';
			}
			switch($isFile)
			{
				case 'wpContent':
					$is_File = true;
					$pathToMediasDir = EVA_GENERATED_DOC_URL;
				break;
				case 'evaContent':
					$is_File = true;
					$pathToMediasDir = EVA_HOME_URL;
				break;
				default:
					$is_File = false;
				break;
			}
			$img_nb = 0;
			if($is_File){
				$current_picture_state = ($photo->isMainPicture == 'yes') ? '&nbsp;-&nbsp;<img style="vertical-align:middle; " src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'user_notifications/picture_as_main_add_s.png" title="' . __('Photo par d&eacute;faut', 'evarisk') . '" />' : '';

				$more_gallery = '';
				$add_button_action = true;
				switch($tableElement){
					case TABLE_TACHE:
						$current_task = new EvaTask($idElement);
						$current_task->load();
						$ProgressionStatus = $current_task->getProgressionStatus();
						if(($ProgressionStatus == 'notStarted') || ($ProgressionStatus == 'inProgress') || (digirisk_options::getOptionValue('possibilite_Modifier_Tache_Soldee') == 'oui') ){
							if($current_task->getidPhotoAvant() == $photo->id){
								$more_gallery .= '<div class="beforePictureSelection" onclick="javascript:unsetAsBeforePicture(\'' . $tableElement . '\',\'' . $idElement . '\',\'' . $photo->id . '\');" >
												-&nbsp;' . __('N\'est plus la photo avant la t&acirc;che', 'evarisk') . '
											</div>';
								$current_picture_state .= '&nbsp;-&nbsp;<img style="vertical-align:middle; " src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'user_notifications/picture_as_before_add_s.png" title="' . __('Photo avant', 'evarisk') . '" />';
							}
							else{
								$more_gallery .= '<div class="beforePictureDeselection" onclick="javascript:setAsBeforePicture(\'' . $tableElement . '\',\'' . $idElement . '\',\'' . $photo->id . '\');" >
												-&nbsp;' . __('D&eacute;finir comme photo avant la t&acirc;che', 'evarisk') . '
											</div>';
							}
							if($current_task->getidPhotoApres() == $photo->id){
								$more_gallery .= '<div class="afterPictureSelection" onclick="javascript:unsetAsAfterPicture(\'' . $tableElement . '\',\'' . $idElement . '\',\'' . $photo->id . '\');" >
												-&nbsp;' . __('N\'est plus la photo apr&egrave;s la t&acirc;che', 'evarisk') . '
											</div>';
								$current_picture_state .= '&nbsp;-&nbsp;<img style="vertical-align:middle; " src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'user_notifications/picture_as_after_add_s.png" title="' . __('Photo apr&eacute;s', 'evarisk') . '" />';
							}
							else{
								$more_gallery .= '<div class="afterPictureDeselection" onclick="javascript:setAsAfterPicture(\'' . $tableElement . '\',\'' . $idElement . '\',\'' . $photo->id . '\');" >
												-&nbsp;' . __('D&eacute;finir comme photo apr&egrave;s la t&acirc;che', 'evarisk') . '
											</div>';
							}
						}
						elseif((($ProgressionStatus == 'Done') || ($ProgressionStatus == 'DoneByChief')) && (digirisk_options::getOptionValue('possibilite_Modifier_Tache_Soldee') == 'non') ){
							$add_button_action = false;
						}
						break;
					case TABLE_ACTIVITE:
						$activite = new EvaActivity($idElement);
						$activite->load();
						$ProgressionStatus = $activite->getProgressionStatus();
						if(($ProgressionStatus == 'notStarted') || ($ProgressionStatus == 'inProgress') || (digirisk_options::getOptionValue('possibilite_Modifier_Action_Soldee') == 'oui') ){
							if($activite->getidPhotoAvant() == $photo->id){
								$more_gallery .= '<div class="beforePictureSelection" onclick="javascript:unsetAsBeforePicture(\'' . $tableElement . '\',\'' . $idElement . '\',\'' . $photo->id . '\');" >
												-&nbsp;' . __('N\'est plus la photo avant l\'action', 'evarisk') . '
											</div>';
								$current_picture_state .= '&nbsp;-&nbsp;<img style="vertical-align:middle; " src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'user_notifications/picture_as_before_add_s.png" title="' . __('Photo avant', 'evarisk') . '" />';
							}
							else{
								$more_gallery .= '<div class="beforePictureDeselection" onclick="javascript:setAsBeforePicture(\'' . $tableElement . '\',\'' . $idElement . '\',\'' . $photo->id . '\');" >
												-&nbsp;' . __('D&eacute;finir comme photo avant l\'action', 'evarisk') . '
											</div>';
							}
							if($activite->getidPhotoApres() == $photo->id){
								$more_gallery .= '<div class="afterPictureSelection" onclick="javascript:unsetAsAfterPicture(\'' . $tableElement . '\',\'' . $idElement . '\',\'' . $photo->id . '\');" >
												-&nbsp;' . __('N\'est plus la photo apr&egrave;s l\'action', 'evarisk') . '
											</div>';
								$current_picture_state .= '&nbsp;-&nbsp;<img style="vertical-align:middle; " src="' . EVA_IMG_PICTOS_PLUGIN_URL . 'user_notifications/picture_as_after_add_s.png" title="' . __('Photo apr&eacute;s', 'evarisk') . '" />';
							}
							else{
								$more_gallery .= '<div class="afterPictureDeselection" onclick="javascript:setAsAfterPicture(\'' . $tableElement . '\',\'' . $idElement . '\',\'' . $photo->id . '\');" >
												-&nbsp;' . __('D&eacute;finir comme photo apr&egrave;s l\'action', 'evarisk') . '
											</div>';
							}
						}
						elseif((($ProgressionStatus == 'Done') || ($ProgressionStatus == 'DoneByChief')) && (digirisk_options::getOptionValue('possibilite_Modifier_Action_Soldee') == 'non') ){
							$add_button_action = false;
						}
						break;
				}

				$gallery .= '
							<li class="alignleft" >
								<a class="thumb" target="picture' . $tableElement . $idElement .'" name="leaf" href="' . $pathToMediasDir . $photo->photo . '" >
									<div>' . ELEMENT_IDENTIFIER_PIC . $photo->id . $current_picture_state . '</div><img src="' . $pathToMediasDir . $photo->photo . '" />
								</a>
								<div class="caption">' . $more_gallery;



				if($add_button_action){
					if($photo->isMainPicture == 'yes'){
						$gallery .= '<div class="pictureDefaultSelection" onclick="javascript:DeleteDefaultPicture(\'' . $tableElement . '\',\'' . $idElement . '\',\'' . $photo->id . '\');" >
												-&nbsp;' . __('N\'est plus la photo par d&eacute;faut', 'evarisk') . '
											</div>';
					}
					else{
						$gallery .= '<div class="pictureDefaultSelection" onclick="javascript:defaultPicture(\'' . $tableElement . '\',\'' . $idElement . '\',\'' . $photo->id . '\');" >
												-&nbsp;' . __('D&eacute;finir comme photo principale', 'evarisk') . '
											</div>';
					}

					$gallery .= '<div class="pictureDeletion" onclick="javascript:pictureDelete(' . $photo->id . ');" >
										-&nbsp;' . __('Supprimer', 'evarisk') . '
									</div>
									<div class="pictureDownload" >
										-&nbsp;<a href="' . $pathToMediasDir . $photo->photo . '" target="associated_document_dl_file" >' . __('T&eacute;l&eacute;charger ce fichier', 'evarisk') . '</a>
									</div>';
				}
				$gallery .= '
								</div>
							</li>';
				$img_nb++;
			}
		}

		$gallery .= '
						</ul>
					</div>
					<!-- Start Advanced Gallery Html Containers -->
					<div id="gallery' . $tableElement . $idElement .'" class="content clear">
						<div id="controls" class="controls"></div>
						<div id="caption' . $tableElement . $idElement .'" class="caption-container"></div>
						<div class="slideshow-container">
							<div id="loading' . $tableElement . $idElement .'" class="loader"></div>
							<div id="slideshow' . $tableElement . $idElement .'" class="slideshow"></div>
						</div>
					</div>
				</div>
			</div>';

		if(!empty($img_nb)){	/*	Create the gallery with gallerific jquery plugin AND define the function for picture deletion	*/
			$gallery .=
			'<script type="text/javascript">
				function defaultPicture(tableElement, idElement, idPhoto){
					digirisk("#defaultPicture' . $tableElement . '_' . $idElement . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
						"post": "true",
						"table": "' . $tableElement . '",
						"idElement": "' . $idElement . '",
						"idPhoto": idPhoto,
						"act": "defaultPictureSelection"
					});
				}

				function setAsBeforePicture(tableElement, idElement, idPhoto){
					digirisk("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
						"post": "true",
						"table": "' . $tableElement . '",
						"idElement": "' . $idElement . '",
						"idPhoto": idPhoto,
						"nom": "setAsBeforePicture"
					});
				}
				function unsetAsBeforePicture(tableElement, idElement, idPhoto){
					digirisk("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
						"post": "true",
						"table": "' . $tableElement . '",
						"idElement": "' . $idElement . '",
						"idPhoto": idPhoto,
						"nom": "unsetAsBeforePicture"
					});
				}
				function setAsAfterPicture(tableElement, idElement, idPhoto){
					digirisk("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
						"post": "true",
						"table": "' . $tableElement . '",
						"idElement": "' . $idElement . '",
						"idPhoto": idPhoto,
						"nom": "setAsAfterPicture"
					});
				}
				function unsetAsAfterPicture(tableElement, idElement, idPhoto){
					digirisk("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
						"post": "true",
						"table": "' . $tableElement . '",
						"idElement": "' . $idElement . '",
						"idPhoto": idPhoto,
						"nom": "unsetAsAfterPicture"
					});
				}

				function DeleteDefaultPicture(tableElement, idElement, idPhoto){
					digirisk("#defaultPicture' . $tableElement . '_' . $idElement . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
						"post": "true",
						"table": "' . $tableElement . '",
						"idElement": "' . $idElement . '",
						"idPhoto": idPhoto,
						"act": "DeleteDefaultPictureSelection"
					});
				}

				function pictureDelete(idPicture){
					if(confirm("' . __('Etes vous sur de vouloir supprimer cette photo?', 'evarisk') . '")){
						digirisk("#caption' . $tableElement . $idElement .'").html(\'<img src="' . PICTO_LOADING_ROUND . '" alt="loading" />\');
						setTimeout(function(){
							digirisk("#pictureGallery' . $tableElement . '_' . $idElement . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
								"post": "true",
								"table": "' . $tableElement . '",
								"idElement": "' . $idElement . '",
								"idPicture": idPicture,
								"act": "deletePicture"
							});
						}, \'1500\');
					}
				}

				digirisk(document).ready(function(){
					// We only want these styles applied when javascript is enabled
					digirisk(\'div.navigation\').css({\'width\' : \'100%\', \'float\' : \'left\'});
					digirisk(\'div.content\').css(\'display\', \'block\');

					// Initially set opacity on thumbs and add
					// additional styling for hover effect on thumbs
					var onMouseOutOpacity = 0.67;
					digirisk(\'#thumbs' . $tableElement . $idElement .' ul.thumbs li\').opacityrollover({
						mouseOutOpacity:   onMouseOutOpacity,
						mouseOverOpacity:  1.0,
						fadeSpeed:         \'fast\',
						exemptionSelector: \'.selected\'
					});

					// Initialize Advanced Galleriffic Gallery
					var gallery = digirisk(\'#thumbs' . $tableElement . $idElement .'\').galleriffic({
						delay:                     2500,
						numThumbs:                 5,
						preloadAhead:              10,
						enableTopPager:            false,
						enableBottomPager:         true,
						maxPagesToShow:            8,
						imageContainerSel:         \'#slideshow' . $tableElement . $idElement .'\',
						controlsContainerSel:      \'#controls' . $tableElement . $idElement .'\',
						captionContainerSel:       \'#caption' . $tableElement . $idElement .'\',
						loadingContainerSel:       \'#loading' . $tableElement . $idElement .'\',
						renderSSControls:          true,
						renderNavControls:         true,
						enableKeyboardNavigation:  false,
						playLinkText:              \'Play Slideshow\',
						pauseLinkText:             \'Pause Slideshow\',
						prevLinkText:              \'&lsaquo; Previous Photo\',
						nextLinkText:              \'Next Photo &rsaquo;\',
						nextPageLinkText:          \'Next &rsaquo;\',
						prevPageLinkText:          \'&lsaquo; Prev\',
						enableHistory:             false,
						autoStart:                 false,
						syncTransitions:           true,
						defaultTransitionDuration: 0,
						onSlideChange:             function(prevIndex, nextIndex) {
							// \'this\' refers to the gallery, which is an extension of digirisk(\'#thumbs\')
							this.find(\'ul.thumbs\').children()
								.eq(prevIndex).fadeTo(\'fast\', onMouseOutOpacity).end()
								.eq(nextIndex).fadeTo(\'fast\', 1.0);
						},
						onPageTransitionOut:       function(callback) {
							this.fadeTo(\'fast\', 0.0, callback);
						},
						onPageTransitionIn:        function() {
							this.fadeTo(\'fast\', 1.0);
						}
					});
					setTimeout(function(){
						' . $moreOutputOptions . '
						if(typeof(removeSlideShowViewer) != "undefined"){
							digirisk(".slideshow").remove();
						}
					},500);
				});
			</script>';
		}

		return $gallery;
	}

	/**
	*	Return the form to upload picture with the jquery plugin uploadify
	*
	*	@param mixed $tableElement The element type we want to get the form for
	*	@param integer $idElement The element identifier we want to get the form for
	*
	*	@return mixed $uploadForm The html code for the upload form
	*/
	function getUploadForm($tableElement, $idElement, $on_complete_form = ''){
		$repertoireDestination = str_replace('\\', '/', EVA_UPLOADS_PLUGIN_DIR . $tableElement . '/' . $idElement . '/');
		$idUpload = 'mainPhoto' . $tableElement;
		$allowedExtensions = "['jpeg','jpg','png','gif']";
		$multiple = true;
		if($tableElement == 'correctiv_action_ask'){
			$multiple = false;
		}
		$actionUpload = str_replace('\\', '/', EVA_LIB_PLUGIN_URL . "photo/uploadPhoto.php");
		$photoDefaut = '';

		/*	Get the main picture for current element */
		$defaultPicture = evaPhoto::getMainPhoto($tableElement, $idElement);
		if(is_file(EVA_GENERATED_DOC_DIR . $defaultPicture))
		{
			$pathToMainPicture = EVA_GENERATED_DOC_URL;
		}
		elseif(is_file(EVA_HOME_DIR . $defaultPicture))
		{
			$pathToMainPicture = EVA_HOME_URL;
		}
		switch($tableElement)
		{
			case TABLE_CATEGORIE_DANGER:
				$photoDefaut = ($defaultPicture != 'error') ? ($pathToMainPicture . $defaultPicture) : DEFAULT_DANGER_CATEGORIE_PICTO;
				$texteBoutton = __('Envoyer une photo', 'evarisk');
			break;
			case TABLE_GROUPEMENT:
				$photoDefaut = ($defaultPicture != 'error') ? ($pathToMainPicture . $defaultPicture) : DEFAULT_GROUP_PICTO;
				$texteBoutton = __('Envoyer une photo', 'evarisk');
			break;
			case TABLE_UNITE_TRAVAIL:
				$photoDefaut = ($defaultPicture != 'error') ? ($pathToMainPicture . $defaultPicture) : DEFAULT_WORKING_UNIT_PICTO;
				$texteBoutton = __('Envoyer une photo', 'evarisk');
			break;
			case TABLE_PRECONISATION:
				$photoDefaut = ($defaultPicture != 'error') ? ($pathToMainPicture . $defaultPicture) : EVA_RECOMMANDATION_ICON;
				$texteBoutton = __('Envoyer une photo', 'evarisk');
			break;
			default:
				$photoDefaut = ($defaultPicture != 'error') ? ($pathToMainPicture . $defaultPicture) : '';
				$texteBoutton = __('Envoyer une photo', 'evarisk');
			break;
		}

		$uploadForm = __( 'Cette fonctionnalité a été momentanément désactivée. VEuillez nous excuser pour la gêne occasionnée', 'evarisk' );

		return $uploadForm;
	}

	/**
	*	Get the main photo for a given element
	*
	*	@param mixed $tableElement The element type we want to get the main picture for
	*	@param integer $idElement The element identifier we want to get the main picture for
	*
	*	@return mixed $status The result status (The picture if all is ok, "error" is there is an error)
	*/
	function getMainPhoto($tableElement, $idElement)
	{
		global $wpdb;
		$status = 'error';

		$query =
			$wpdb->prepare(
				"SELECT PICTURE.photo
				FROM " . TABLE_PHOTO . " AS PICTURE
					INNER JOIN " . TABLE_PHOTO_LIAISON . " AS PICTURE_LINK ON (PICTURE_LINK.idPhoto = PICTURE.id)
				WHERE PICTURE_LINK.tableElement = '%s'
						AND PICTURE_LINK.idElement = '%d'
						AND PICTURE_LINK.isMainPicture = 'yes'
						AND PICTURE_LINK.status = 'valid' "
				, $tableElement, $idElement);
		if($mainPhotoInformation = $wpdb->get_row($query))
		{
			$status = $mainPhotoInformation->photo;
		}
		if(($status != 'error') && !is_file(EVA_GENERATED_DOC_DIR . $status) && !is_file(EVA_HOME_DIR . $status))
		{
			$status = 'error';
		}

		return $status;
	}

	/**
	*
	*/
	function isMainPicture($tableElement, $idElement, $idPicture)
	{
		global $wpdb;
		$status = 'error';

		$query =
			$wpdb->prepare(
				"SELECT PICTURE_LINK.isMainPicture
				FROM " . TABLE_PHOTO . " AS PICTURE
					INNER JOIN " . TABLE_PHOTO_LIAISON . " AS PICTURE_LINK ON (PICTURE_LINK.idPhoto = PICTURE.id)
				WHERE PICTURE_LINK.tableElement = '%s'
						AND PICTURE_LINK.idElement = '%d'
						AND PICTURE.id = '%d'
						AND PICTURE_LINK.status = 'valid' "
				, $tableElement, $idElement, $idPicture);
		if($mainPhotoInformation = $wpdb->get_row($query))
		{
			$status = $mainPhotoInformation->isMainPicture;
		}

		return $status;
	}

	/**
	*	Set the main photo for a given element
	*
	*	@param mixed $tableElement The element type we want to set the main picture for
	*	@param integer $idElement The element identifier we want to set the main picture for
	*	@param integer $idPhoto The picture identifier we want to set as the main picture for the given element
	*
	*	@return mixed $status The result status ("ok" if all is ok, "error" is there is an error)
	*/
	function setMainPhoto($tableElement, $idElement, $idPhoto, $isMainPicture = 'yes')
	{
		global $wpdb;
		$status = 'error';

		if ( $isMainPicture == 'yes' ) {
			/*	Delete the old main photo	*/
			$dissociate_picture = $wpdb->update(
				TABLE_PHOTO_LIAISON,
				array( 'isMainPicture' => 'no', ),
				array( 'tableElement' => $tableElement, 'idElement' => $idElement, )
			);
		}

		/*	Set the main photo	*/
		$set_main_picture = $wpdb->update(
			TABLE_PHOTO_LIAISON,
			array( 'isMainPicture' => $isMainPicture, ),
			array( 'tableElement' => $tableElement, 'idElement' => $idElement, 'idPhoto' => $idPhoto, )
		);
		if ( false !== $set_main_picture ) {
			$status = 'ok';
		}

		return $status;
	}

	/**
	 * Return a upload form with a thumbnail if multiple is false
	 *
	 * @param string $tableElement Table of the element which is the photo relative to.
	 * @param int $idElement Identifier in the table of the element which is the photo relative to.
	 * @param string $repertoireDestination Repository of the uploaded file.
	 * @param string $idUpload HTML div identifier.
	 * @param string $allowedExtensions Allowed extensions for the upload (ex:"['jpeg','png']"). All extensions is written "[]".
	 * @param bool $multiple Can the user upload multiple files in one time ?
	 * @param string $actionUpload The url of the file call when the user press on upload button.
	 * @param string $photoDefaut The default photo to display.
	 *
	 * @return string The upload form with eventually a thumbnail.
	 */
	function getFormulaireUploadPhoto($tableElement, $idElement, $repertoireDestination, $idUpload, $allowedExtensions, $multiple, $actionUpload, $photoDefaut, $texteBoutton = '', $onCompleteAction = ''){
		_e( 'Cette fonctionnalité a été momentanément désactivée. VEuillez nous excuser pour la gêne occasionnée', 'evarisk' );
	}

	/**
	*	The different action called when we want to delete a picture (Set the status field to deleted). if the picture we want to delete is the main picture we update the different visible element containing this picture
	*
	*	@param mixed $tableElement The element type we want to delete the picture
	*	@param integer $idElement The element identifier we want to delete the picture
	*	@param integer $idPicture The picture identifier we want to delete
	*
	*	@return mixed The html code to update the different element and to output the succes message
	*/
	function deletePictureAction($tableElement, $idElement, $idPicture)
	{
		$tableElement = $_POST['table'];
		$idElement = $_POST['idElement'];

		/*	Check if the selected picture we want to delete is the main picture or not in order to update the main picture thumb	*/
		$mainPictureUpdate = '';
		$isTheMainPicture = evaPhoto::isMainPicture($tableElement, $idElement, $idPicture);
		if($isTheMainPicture == 'yes'){
			$definedDefaultPicture = '';
			switch($tableElement){
				case TABLE_CATEGORIE_DANGER:
					$definedDefaultPicture = DEFAULT_DANGER_CATEGORIE_PICTO;
				break;
				case TABLE_GROUPEMENT:
					$definedDefaultPicture = DEFAULT_GROUP_PICTO;
				break;
				case TABLE_UNITE_TRAVAIL:
					$definedDefaultPicture = DEFAULT_WORKING_UNIT_PICTO;
				break;
				case TABLE_PRECONISATION:
					$definedDefaultPicture = EVA_RECOMMANDATION_ICON;
				break;
			}

			$mainPictureUpdate =
				'digirisk("#defaultPicture' . $tableElement . '_' . $idElement . '").html("<img src=\'' . $definedDefaultPicture . '\' alt=\'main picture\' />");
				digirisk("#photo' . $tableElement . $idElement . '").attr("src", "' . $definedDefaultPicture . '");';
		}

		/*	Desactivation de la photo selectionnee	*/
		$updateAssociationResult = evaPhoto::unAssociatePicture($tableElement, $idElement, $idPicture);

		$messageInfo = '<script type="text/javascript">
			digirisk(document).ready(function(){
				digirisk("#message' . $tableElement . '_' . $idElement . '").addClass("updated");';
		if($updateAssociationResult != 'error'){
			switch($tableElement){
				case TABLE_ACTIVITE:
				case TABLE_TACHE:
					/*	Log modification on element and notify user if user subscribe	*/
					digirisk_user_notification::log_element_modification($tableElement, $idElement, 'picture_delete', $idPicture, $idPicture);
				break;
			}
			$messageInfo .= '
					digirisk("#message' . $tableElement . '_' . $idElement . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('L\'image a &eacute;t&eacute; supprim&eacute;e.', 'evarisk') . '</strong></p>') . '");';
		}
		else{
			$messageInfo .= '
					digirisk("#message' . $tableElement . '_' . $idElement . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="no-response" style="vertical-align:middle;" />&nbsp;<strong>' . __('L\'image n\'a pas pu &ecirc;tre supprim&eacute;e.', 'evarisk') . '</strong></p>') . '");';
		}
		$messageInfo .= '
					digirisk("#message' . $tableElement . '_' . $idElement . '").show();
					setTimeout(function(){
						digirisk("#message' . $tableElement . '_' . $idElement . '").removeClass("updated");
						digirisk("#message' . $tableElement . '_' . $idElement . '").hide();
					},7500);
				});
			' . $mainPictureUpdate . '
			</script>';

		/*	Sortie du formulaire 	*/
		return $messageInfo . evaPhoto::outputGallery($tableElement, $idElement);
	}

	/**
	*	The different action called when we want to set/unset a picture as the main picture for an element
	*
	*	@param mixed $tableElement The element type we want to set/unset the main photo for
	*	@param integer $idElement The element identifier we want to set/unset the main photo for
	*	@param integer $idPicture The picture identifier we want to set/unset as the main picture
	*	@param mixed $isMainPicture Define if we want to set or unset as the main picture
	*
	*	@return mixed The html code to update the different element and to output the succes message
	*/
	function setMainPhotoAction($tableElement, $idElement, $idPicture, $isMainPicture = 'yes')
	{
		$updateMainPhotoResult = evaPhoto::setMainPhoto($tableElement, $idElement, $idPicture, $isMainPicture);

		$messageInfo = '<script type="text/javascript">
			digirisk(document).ready(function(){
				digirisk("#message' . $tableElement . '_' . $idElement . '").addClass("updated");';

		if($isMainPicture == 'yes')
		{
			if($updateMainPhotoResult == 'ok')
			{
				switch($tableElement){
					case TABLE_ACTIVITE:
					case TABLE_TACHE:
						/*	Log modification on element and notify user if user subscribe	*/
						digirisk_user_notification::log_element_modification($tableElement, $idElement, 'picture_as_main_add', '', $idPicture);
					break;
				}
				$messageInfo .= '
						digirisk("#message' . $tableElement . '_' . $idElement . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('L\'image a &eacute;t&eacute; correctement d&eacute;finie comme photo principale.', 'evarisk') . '</strong></p>') . '");';
			}
			else
			{
				$messageInfo .= '
						digirisk("#message' . $tableElement . '_' . $idElement . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="no-response" style="vertical-align:middle;" />&nbsp;<strong>' . __('L\'image n\'a pas pu &ecirc;tre d&eacute;finie comme photo principale.', 'evarisk') . '</strong></p>') . '");';
			}
		}
		elseif($isMainPicture == 'no')
		{
			if($updateMainPhotoResult == 'ok')
			{
				switch($tableElement){
					case TABLE_ACTIVITE:
					case TABLE_TACHE:
						/*	Log modification on element and notify user if user subscribe	*/
						digirisk_user_notification::log_element_modification($tableElement, $idElement, 'picture_as_main_delete', '', $idPicture);
					break;
				}
				$messageInfo .= '
						digirisk("#message' . $tableElement . '_' . $idElement . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png" alt="response" style="vertical-align:middle;" />&nbsp;<strong>' . __('L\'image n\'est plus la photo principale.', 'evarisk') . '</strong></p>') . '");';
			}
			else
			{
				$messageInfo .= '
						digirisk("#message' . $tableElement . '_' . $idElement . '").html("' . addslashes('<p><img src="' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png" alt="no-response" style="vertical-align:middle;" />&nbsp;<strong>' . __('L\'image n\'a pas pu &ecirc;tre d&eacute;finie comme n\&eacute;tant plus la photo principale.', 'evarisk') . '</strong></p>') . '");';
			}
		}

		$definedDefaultPicture = '';
		switch($tableElement)
		{
			case TABLE_CATEGORIE_DANGER:
				$definedDefaultPicture = DEFAULT_DANGER_CATEGORIE_PICTO;
			break;
			case TABLE_GROUPEMENT:
				$definedDefaultPicture = DEFAULT_GROUP_PICTO;
			break;
			case TABLE_UNITE_TRAVAIL:
				$definedDefaultPicture = DEFAULT_WORKING_UNIT_PICTO;
			break;
		}
		$defaultPicture = evaPhoto::getMainPhoto($tableElement, $idElement);
		if(is_file(EVA_GENERATED_DOC_DIR . $defaultPicture))
		{
			$pathToMainPicture = EVA_GENERATED_DOC_URL;
		}
		elseif(is_file(EVA_HOME_DIR . $defaultPicture))
		{
			$pathToMainPicture = EVA_HOME_URL;
		}
		$resultDefaultPicture = ($defaultPicture != 'error') ? ($pathToMainPicture . $defaultPicture) : $definedDefaultPicture;

		$messageInfo .= '
					digirisk("#message' . $tableElement . '_' . $idElement . '").show();
					setTimeout(function(){
						digirisk("#message' . $tableElement . '_' . $idElement . '").removeClass("updated");
						digirisk("#message' . $tableElement . '_' . $idElement . '").hide();
					},5000);
					reloadcontainer(\'' . $tableElement . '\', \'' . $idElement . '\', \'' . PICTO_LOADING_ROUND . '\');
				});
				digirisk("#photo' . $tableElement . $idElement . '").attr("src", "' . $resultDefaultPicture . '");
			</script>';

		return $messageInfo . '<img src="' . $resultDefaultPicture . '" alt="" />';
	}

	/**
	*	Output the picture gallery. Check if there are several picture to show or if there is only one picture and no main picture defined
	*
	*	@param mixed $tableElement The element type we want to show the gallery for
	*	@param integer $idElement The element identifier we want to show the gallery for
	*
	*	@return mixed $galleryOutput The html code with the gallery if there are picture to show or a button to show the gallery when only one picture is present for the element
	*/
	function outputGallery($tableElement, $idElement, $userCanUploadPhoto = true){
		$galleryOutput = '';

		$listePhotoElement = evaPhoto::getPhotos($tableElement, $idElement);
		$elementMainPhoto = evaPhoto::getMainPhoto($tableElement, $idElement);

		if((!$userCanUploadPhoto) ||(count($listePhotoElement) > 1) || (($elementMainPhoto == 'error') && (count($listePhotoElement) > 0)))
		{
			$galleryOutput = evaPhoto::getGallery($tableElement, $idElement);
		}
		elseif(count($listePhotoElement) >= 1)
		{
			$galleryOutput = '<input type="button" value="' . __('Voir la galerie', 'evarisk') . '" onclick="javascript:showGallery(\'' . $tableElement . '\', \'' . $idElement . '\', \'' . PICTO_LOADING_ROUND . '\');" />';
		}

		return $galleryOutput;
	}

	/**
	*	Output the different element for a galery
	*
	*	@param mixed $tableElement The element type we want to show the gallery for
	*	@param integer $idElement The element identifier we want to show the gallery for
	*
	*	@return mixed The code wich will be shown in source code
	*/
	function galleryContent($tableElement, $idElement, $userCanUploadPhoto = true){
		$galleryOutput = '';

		$galleryOutput =
'<div id="message' . $tableElement . '_' . $idElement . '" ></div>';

		$upload_button = '';

		if ($userCanUploadPhoto) {

			$upload_button =
			'<div id="pictureUploadForm' . $tableElement . '_' . $idElement . '" >' . evaPhoto::getUploadForm($tableElement, $idElement) . '</div>';

			switch ($tableElement) {
				case TABLE_TACHE:
					$currentTask = new EvaTask($idElement);
					$currentTask->load();
					$ProgressionStatus = $currentTask->getProgressionStatus();

					if((($ProgressionStatus == 'Done') || ($ProgressionStatus == 'DoneByChief')) && (digirisk_options::getOptionValue('possibilite_Modifier_Tache_Soldee') == 'non') ){
						$upload_button = '
			<br class="clear" />
			<div class="alignright button-primary" id="TaskSaveButton" >
				' . __('Cette t&acirc;che est sold&eacute;e, vous ne pouvez pas ajouter de photos', 'evarisk') . '
			</div>';
					}
				break;

				case TABLE_ACTIVITE:
					$current_action = new EvaActivity($idElement);
					$current_action->load();
					$ProgressionStatus = $current_action->getProgressionStatus();

					if((($ProgressionStatus == 'Done') || ($ProgressionStatus == 'DoneByChief')) && (digirisk_options::getOptionValue('possibilite_Modifier_Action_Soldee') == 'non') ){
						$upload_button = '
			<br class="clear" />
			<div class="alignright button-primary" id="TaskSaveButton" >
				' . __('Cette t&acirc;che est sold&eacute;e, vous ne pouvez pas ajouter de photos', 'evarisk') . '
			</div>';
					}
				break;
			}
		}
		$galleryOutput .= $upload_button . '
<div id="pictureGallery' . $tableElement . '_' . $idElement . '" >' . evaPhoto::outputGallery($tableElement, $idElement, $userCanUploadPhoto) . '</div>';

		return $galleryOutput;
	}

	/**
	*
	*/
	function checkIfPictureIsFile($pictureToCheck, $tableElement, $putDefaultPicture = true){

		switch($tableElement){
			case TABLE_PRECONISATION:
				$defaultPicture = EVA_RECOMMANDATION_ICON;
			break;
			case TABLE_CATEGORIE_PRECONISATION:
				$defaultPicture = EVA_RECOMMANDATION_ICON;
			break;
			case TABLE_CATEGORIE_DANGER:
				$defaultPicture = DEFAULT_DANGER_CATEGORIE_PICTO;
			break;
			default:
				$defaultPicture = false;
			break;
		}

		$pictureExist = false;
		if($pictureToCheck != ''){
			if(is_file(EVA_HOME_DIR . $pictureToCheck)){
				$pictureExist = EVA_HOME_URL . $pictureToCheck;
			}
			elseif(is_file(EVA_GENERATED_DOC_DIR . $pictureToCheck)){
				$pictureExist = EVA_GENERATED_DOC_URL . $pictureToCheck;
			}
		}

		if(($putDefaultPicture) && (!$pictureExist) && ($defaultPicture != false) && (is_file(str_replace(EVA_HOME_URL, EVA_HOME_DIR, $defaultPicture)))){
			$pictureExist = $defaultPicture;
		}

		return $pictureExist;
	}

	/**
	*
	*/
	function picture_gallery_box($arguments){
		$idElement = $arguments['idElement'];
		$tableElement = $arguments['tableElement'];

		switch($tableElement)
		{
			case TABLE_CATEGORIE_DANGER:
				$userCanUploadPicture = current_user_can('digi_edit_danger_category');
				echo '
<script type="text/javascript" >
	digirisk(document).ready(function(){
		// jQuery("#mainPhoto' . $tableElement . '").hide();
		// setTimeout(function(){jQuery("#mainPhoto' . $tableElement . ' .qq-uploader .qq-upload-button").css("position", "");}, 1500);
		// setTimeout(function(){jQuery("#mainPhoto' . $tableElement . '").show();}, 2000);
	});
</script>';
			break;
			case TABLE_GROUPEMENT:
				$userCanUploadPicture = current_user_can('digi_edit_groupement');
				if(!$userCanUploadPicture){
					$userCanUploadPicture = current_user_can('digi_edit_groupement_' . $idElement);
				}
			break;
			case TABLE_UNITE_TRAVAIL:
				$userCanUploadPicture = current_user_can('digi_edit_unite');
				if(!$userCanUploadPicture){
					$userCanUploadPicture = current_user_can('digi_edit_unite_' . $idElement);
				}
			break;
			case TABLE_TACHE:
				$userCanUploadPicture = (current_user_can('digi_edit_task') || current_user_can('digi_edit_task_' . $idElement));
			break;
			case TABLE_ACTIVITE:
				$userCanUploadPicture = (current_user_can('digi_edit_action') || current_user_can('digi_edit_action_' . $idElement));
			break;
			case TABLE_CATEGORIE_PRECONISATION:
				$userCanUploadPicture = current_user_can('digi_edit_recommandation_cat');
			break;
			case TABLE_PRECONISATION:
				$userCanUploadPicture = current_user_can('digi_edit_recommandation');
			break;
			case TABLE_METHODE:
				$userCanUploadPicture = current_user_can('digi_edit_method');
			break;
			case TABLE_METHODE:
				$userCanUploadPicture = current_user_can('digi_edit_menu');
			break;
			default:
				$userCanUploadPicture = true;
			break;
		}


		$output = evaPhoto::galleryContent($tableElement, $idElement, $userCanUploadPicture);

		echo $output;
	}

}