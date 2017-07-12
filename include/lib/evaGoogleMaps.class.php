<?php
/**
 * Class who allows to interract with the googleMap API
 *
 * @author Evarisk
 * @version v5.0
 */
include_once(EVA_CONFIG );

class EvaGoogleMaps {
	/**
	 * returns a script that geolocalise the address
	  * @param string $idElementHover Id attribut of the element on witch the script is call.
	  * @param int $postId Id of the element display.
	  * @param string $idLigne1 Id attribut of the first ligne input.
	  * @param string $idLigne2 Id attribut of the second ligne input.
	  * @param string $idCodePostal Id attribut of the postal code input.
	  * @param string $idVille Id attribut of the city input.
	  * @param string $idLatitude Id attribut of the latitude input.
	  * @param string $idLongitude Id attribut of the longitude input.
	  * @return the script for geolocalise the address.
	 */
	static function scriptGeoloc($idElementHover, $postId, $idLigne1, $idLigne2, $idCodePostal, $idVille, $idLatitude, $idLongitude)
	{
		$geolocObligatoire = GEOLOC_OBLIGATOIRE?"true":"false";
		return '
			<script type="text/javascript">
				digirisk(document).ready(function() {
					geolocPossible = true;
					digirisk(\'#' . $idElementHover . '\').hover(function() {
						geolocPossible = true;
						digirisk(\'#latitude\').removeClass(\'form-input-tip\');
						digirisk(\'#longitude\').removeClass(\'form-input-tip\');
							digirisk(\'#adresseUnite' . $postId . ' input\').each(function(){
								digirisk(this).blur();
							});
							digirisk(\'#adresseUnite' . $postId . ' .form-input-tip\').each(function(){
								if(!(digirisk(this).attr("id") == "' . $idLigne2 . '"))
								{
									geolocPossible = false;
								}
							});
						if(!(' . $geolocObligatoire . ' && !geolocPossible))
						{
							if(geolocPossible)
							{
								geocoder = new google.maps.Geocoder();
								geocoder.geocode({"address": digirisk(\'#' . $idLigne1 . '\').val() + " " + digirisk(\'#' . $idLigne2 . '\').val() + ", " + digirisk(\'#' . $idCodePostal . '\').val() + ", " + digirisk(\'#' . $idVille . '\').val()}, function(results, status) {
									digirisk(\'#' . $idLatitude . '\').val(results[0].geometry.location.lat());
									digirisk(\'#' . $idLongitude . '\').val(results[0].geometry.location.lng());
									if(digirisk(\'#' . $idLatitude . '\').val() == 0 && digirisk(\'#' . $idLongitude . '\').val() == 0)
									{
										var ligne1 = digirisk(\'#' . $idLigne1 . '\').val();
										ligne1 = ligne1.replace(/[\d]+[\s]?[,]?/, "");
										geocoder.geocode({"address": ligne1 + " " + digirisk(\'#' . $idLigne2 . '\').val() + ", " + digirisk(\'#' . $idCodePostal . '\').val() + ", " + digirisk(\'#' . $idVille . '\').val()}, function(results, status) {
											digirisk(\'#' . $idLatitude . '\').val(results[0].geometry.location.lat());
											digirisk(\'#' . $idLongitude . '\').val(results[0].geometry.location.lng());
										});
									}
								});
							}
						}
					});
				});
			</script>';
	}

	/**
	* Returns the script and div for the google map
	*
	* @param string $idGoogleMapsDiv Id attribut of the div for the google map.
	* @param array $markers Markers to display. A marker is an array with the keys 'longitude', 'latitude', 'info' and 'image'.
	*
	* @return the script and div for the google map.
	*/
	static function getGoogleMap($idGoogleMapsDiv, $markers, $table_element, $id_element) {
		$google_map_marker_more_content = '';
		$nbElements = count($markers);
		$sudMax = 180;
		$nordMax = -180;
		$ouestMax = 90;
		$estMax = -90;
		for($indice=0; $indice< $nbElements; $indice++){
			if($markers[$indice]['latitude'] == null OR $markers[$indice]['latitude'] == ''){
				unset($markers[$indice]);
			}
		}
		for($indice=0; $indice< $nbElements; $indice++){
			if(isset($markers[$indice])){
				for($indice2=0; $indice2<$nbElements; $indice2++){
					if(isset($markers[$indice]) AND isset($markers[$indice2])){
						if($indice != $indice2 AND $indice < $indice2 AND $markers[$indice]['latitude'] == $markers[$indice2]['latitude'] AND $markers[$indice]['longitude'] == $markers[$indice2]['longitude']){
							$markers[$indice]['info'] = $markers[$indice]['info'] . '<hr />' . $markers[$indice2]['info'];
							unset($markers[$indice2]);
						}
					}
				}
				if($markers[$indice]['latitude'] < $sudMax){
					$sudMax = $markers[$indice]['latitude'];
				}
				if($markers[$indice]['latitude'] > $nordMax){
					$nordMax = $markers[$indice]['latitude'];
				}
				if($markers[$indice]['longitude'] < $ouestMax){
					$ouestMax = $markers[$indice]['longitude'];
				}
				if($markers[$indice]['longitude'] > $estMax){
					$estMax = $markers[$indice]['longitude'];
				}
			}
		}
		if($nordMax == -180){
			$nordMax = 0;
			$sudMax = 0;
			$ouestMax = 0;
			$estMax = 0;
		}
		$scrollWheel = 'false';
		if(ZOOM_SCROLL_MAP){
			$scrollWheel = 'true';
		}
		$googleMap = '
			<script type="text/javascript">
				function getDraggedCoordonees(response){
					if (!response || response.Status.code != 200){
						alert("Status Code:" + response.Status.code);
					}
					else {
						place = response.Placemark[0];
						alert(place.Point.coordinates[1]);
						alert(place.Point.coordinates[0]);
					}
				}

				function initialize(){
					sud = ' . $sudMax . ';
					nord = ' . $nordMax . ';
					ouest = ' . $ouestMax . ';
					est = ' . $estMax . ';
					zoom = 5;
					centerLat = 46.75;
					centerLng = 2.5;
					if (google.loader.ClientLocation){
						centerLat = google.loader.ClientLocation.latitude;
						centerLng = google.loader.ClientLocation.longitude;
						zoom = 13;
					}
					var myOptions = {
						zoom: zoom,
						center: new google.maps.LatLng(centerLat, centerLng),
						mapTypeId: google.maps.MapTypeId.ROADMAP,
						scrollwheel: ' . $scrollWheel . '
					}
					var map = new google.maps.Map(document.getElementById("' . $idGoogleMapsDiv . '"), myOptions);
					if(sud == nord){
						sud = sud - 0.02;
						nord = nord + 0.02;
					}
					if(est == ouest){
						est = est + 0.02;
						ouest = ouest - 0.02;
					}
					if(!(sud == -0.02 && nord == 0.02 && est == 0.02 && ouest == -0.02)){
						var southWest = new google.maps.LatLng(sud,ouest);
						var northEast = new google.maps.LatLng(nord,est);
						var bounds = new google.maps.LatLngBounds(southWest,northEast);
						map.fitBounds(bounds);
					}
					';
					if(count($markers)>0 AND $markers[0]!=null){
						$i = 0;
						foreach($markers as $marker){
							$googleMap = $googleMap . '
							var image = "' . $marker['image'] . '";
							var marker' . $idGoogleMapsDiv . '_' . $i . ' = new google.maps.Marker({
								position: new google.maps.LatLng(' . $marker['latitude'] . ', ' . $marker['longitude'] . '),
								icon: image,
								draggable: true
							});
							var infowindow' . $idGoogleMapsDiv . '_' . $i . ' = new google.maps.InfoWindow({
								content: "<div>' . addslashes($marker['info']) . '</div>"
							});
							google.maps.event.addListener(marker' . $idGoogleMapsDiv . '_' . $i . ', \'click\', function() {
								infowindow' . $idGoogleMapsDiv . '_' . $i . '.open(map,marker' . $idGoogleMapsDiv . '_' . $i . ');
							});
							google.maps.event.addListener(marker' . $idGoogleMapsDiv . '_' . $i . ', "dragend", function() {
								var markerCenter = marker' . $idGoogleMapsDiv . '_' . $i . '.getPosition();
								digirisk("#adressIdentifier' . $marker['adress'] . '").val(markerCenter);
								digirisk("#saveNewPosition").show();
							});
							marker' . $idGoogleMapsDiv . '_' . $i . '.setMap(map);';
							$i ++;
							$google_map_marker_more_content .= '<input type="hidden" value="" class="markerNewPosition" name="newPosition" id="adressIdentifier' . $marker['adress'] . '" >';
						}
					}
					$googleMap .= '
				}
				digirisk(document).ready(function(){
					if (typeof google === "object" && typeof google.load === "function") {
						google.load("maps", "3",  {callback: initialize, other_params:"sensor=false"});
					}
					digirisk("#saveNewPosition").click(function(){
						var new_position = "_pos_separator_";
						digirisk(".markerNewPosition").each(function(){
							new_position += digirisk(this).attr("id") + "-val-" + digirisk(this).val() + "_pos_separator_";
						});
						digirisk("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",{
							"post": "true",
							"tableProvenance": "' . TABLE_ADRESSE . '",
							"nom": "saveMarkerNewPosition",
							"positions": new_position
						});
					});
				});
			</script>
			' . $google_map_marker_more_content . '
			<div id="' . $idGoogleMapsDiv . '" style="width: 100%; height: 300px"></div>';
			$save_button = true;
			switch ( $table_element ) {
				case TABLE_GROUPEMENT:
					if (!current_user_can('digi_edit_groupement') && !current_user_can('digi_edit_groupement_' . $idElement)) {
						$save_button = false;
					}
				break;
				case TABLE_UNITE_TRAVAIL:
					if (!current_user_can('digi_edit_unite') && !current_user_can('digi_edit_unite_' . $idElement)) {
						$save_button = false;
					}
				break;
			}
			if ( $save_button ) {
				$googleMap .= '<input type="button" value="' . __('Enregistrer les nouvelles coordonn&eacute;es', 'evarisk') . '" class="button-primary hide" id="saveNewPosition" name="saveNewPosition" />';
			}
		return $googleMap;
	}

}