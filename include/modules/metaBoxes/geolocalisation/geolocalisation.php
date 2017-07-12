<?php
	require_once(EVA_CONFIG);
	//Postbox definition
	$postBoxTitle = __('G&eacute;olocalisation', 'evarisk') . (!empty($_REQUEST['table']) && !empty($_REQUEST['id']) ? Arborescence::display_element_main_infos( $_REQUEST['table'], $_REQUEST['id'] ) : '');
	$postBoxId = 'postBoxGeolocation';
	$postBoxCallbackFunction = 'getGeolocationPostBoxBody';
	add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_UNITES_DE_TRAVAIL, 'rightSide', 'default');
	add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_GROUPEMENTS_GESTION, 'rightSide', 'default');
	add_meta_box($postBoxId, $postBoxTitle, $postBoxCallbackFunction, PAGE_HOOK_EVARISK_EVALUATION_DES_RISQUES, 'normal', 'default');

	function getGeolocationPostBoxBody($arguments)
	{
		$idGoogleMapsDivWrap = 'waitingGeoloc' . $arguments['tableElement'] . $arguments['idElement'];
		echo '<div id="geoloc_message" >&nbsp;</div><div id="' . $idGoogleMapsDivWrap . '"><img src="' . esc_url( admin_url( 'images/wpspin_light.gif' ) ) . '" alt="" />&nbsp;' . esc_js( __( 'Loading...' ) ) . '</div>';
		$markers = $arguments['markers'];
		$idGoogleMapsDiv = 'map' . $arguments['tableElement'] . $arguments['idElement'];
		$script = '<script type="text/javascript">
				digirisk(document).ready(function() {
					var idGoogleMapsDiv = "' . $idGoogleMapsDiv . '";';
		if($markers[0] != null)
		{
			$script = $script . 'var markers = new Array(';
			foreach($markers as $keyMarkers => $marker)
			{
				$script = $script . '"' . addslashes(nl2br(implode('"; "', $marker))) . '", ';
			}
			$script = substr($script, 0, strlen($script) - 2);
			$script = $script . ');';
			$script = $script . 'var keys = new Array(';
			foreach($markers[0] as $keyMarker => $null)
			{
				$script = $script . '"' . addslashes($keyMarker) . '", ';
			}
			$script = substr($script, 0, strlen($script) - 2);
			$script = $script . ');';
		}
		else
		{
			$script = $script . 'var markers = "";
				var keys = "";';
		}
		$script = $script . '
					digirisk("#' . $idGoogleMapsDivWrap . '").load("' . EVA_INC_PLUGIN_URL . 'ajax.php", {
						"post": "true",
						"nomMetaBox": "Geolocalisation",
						"idGoogleMapsDiv": idGoogleMapsDiv,
						"keys":keys,
						"markers": markers,
						"table_element": "' . $arguments['tableElement'] . '",
						"id_element": "' . $arguments['idElement'] . '",
					});
				});
			</script>';
		echo $script;
	}
?>