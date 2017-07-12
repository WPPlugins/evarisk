<?php
/**
 * This class allows to work on single address (equivalent to single row in data base) 
 *
 * @author Evarisk
 * @version v5.0
 */
include_once(EVA_LIB_PLUGIN_DIR . 'adresse/evaBaseAddress.class.php');

class EvaAddress extends EvaBaseAddress
{	
	/**
	 * Return an array with the latitude and the longitude of the address
	 * @return array The array with the latitude and the longitude of the address
	 */
	function getGeoLoc()
	{
		unset($geoLoc);
		$geoLoc['latitude'] = $this->getLatitude();
		$geoLoc['longitude'] = $this->getLongitude();
		return $geoLoc;		
	}
}