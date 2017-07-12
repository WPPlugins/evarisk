<?php
/**
 * Class to represent an Address directly connect with the data base
 *
 * @author Evarisk
 * @version v5.0
 */
include_once(EVA_CONFIG);
class EvaBaseAddress
{
	/**
	 * @var int The Address identifier
	 */
	var $id;
	/**
	 * @var string The Address first line
	 */
	var $firstLine;
	/**
	 * @var string The Address second line
	 */
	var $secondLine;
	/**
	 * @var string The Address postal code
	 */
	var $codePostal;
	/**
	 * @var string The Address city
	 */
	var $city;
	/**
	 * @var string The Address longitude
	 */
	var $longitude;
	/**
	 * @var string The Address latitude
	 */
	var $latitude;
	/**
	 * @var string The Address status
	 */
	var $status;

/*
 *	Constructeur et accesseurs
 */
	/**
	 * Constructor of the Address class
	 * @param int $id The id to set
	 * @param string $firstLine The fisrt line to set
	 * @param string $secondLine The second line to set
	 * @param string $codePostal The postal code to set
	 * @param string $city The city to set
	 * @param string $latitude The latitude to set
	 * @param string $longitude The longitude to set
	 * @param string $status The status to set
	 */
	function EvaBaseAddress($id = null, $firstLine = '', $secondLine = '', $codePostal = '', $city = '', $latitude = '', $longitude = '', $status = 'Valid')
	{
		$this->id = $id;
		$this->firstLine = $firstLine;
		$this->secondLine = $secondLine;
		$this->codePostal = $codePostal;
		$this->city = $city;
		$this->latitude = $latitude;
		$this->longitude = $longitude;
		$this->status = $status;
	}

	/**
	 * Returns the Address identifier
	 * @return int The identifier
	 */
	function getId()
	{
		return $this->id;
	}

	/**
	 * Set the Address identifier
	 * @param int $id The identifier to set
	 */
	function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Returns the Address first line
	 * @return string The first line
	 */
	function getFirstLine()
	{
		return $this->firstLine;
	}

	/**
	 * Set the Address first line
	 * @param string $firstLine The first line to set
	 */
	function setFirstLine($firstLine)
	{
		$this->firstLine = $firstLine;
	}

	/**
	 * Returns the Address second lige
	 * @return string The second lige
	 */
	function getSecondLine()
	{
		return $this->secondLine;
	}

	/**
	 * Set the Address second line
	 * @param string $secondLine The second line to set
	 */
	function setSecondLine($secondLine)
	{
		$this->secondLine = $secondLine;
	}

	/**
	 * Returns the Address postal code
	 * @return string The postal code
	 */
	function getPostalCode()
	{
		return $this->codePostal;
	}

	/**
	 * Set the Address postal code
	 * @param string $codePostal The postal code to set
	 */
	function setPostalCode($codePostal)
	{
		$this->codePostal = $codePostal;
	}

	/**
	 * Returns the Address city
	 * @return string The city
	 */
	function getCity()
	{
		return $this->city;
	}

	/**
	 * Set the Address city
	 * @param string $city The city to set
	 */
	function setCity($city)
	{
		$this->city = $city;
	}

	/**
	 * Returns the Address latitude
	 * @return int The latitude
	 */
	function getLatitude()
	{
		return $this->latitude;
	}

	/**
	 * Set the Address latitude
	 * @param int $latitude The latitude to set
	 */
	function setLatitude($latitude)
	{
		$this->latitude = $latitude;
	}

	/**
	 * Returns the Address longitude
	 * @return int The longitude
	 */
	function getLongitude()
	{
		return $this->longitude;
	}

	/**
	 * Set the Address longitude
	 * @param int $longitude The longitude to set
	 */
	function setLongitude($longitude)
	{
		$this->longitude = $longitude;
	}

	/**
	 * Returns the Address status
	 * @return string The status
	 */
	function getStatus()
	{
		return $this->status;
	}

	/**
	 * Set the Address status
	 * @param string $status The city to status
	 */
	function setStatus($status)
	{
		$this->status = $status;
	}

/*
 * Persistance
 */

	/**
	 * Save or update the address in data base
	 */
	function save()
	{
		global $wpdb;

		{//Variables cleaning
			$id = (int) digirisk_tools::IsValid_Variable($this->getId());
			$firstLine = digirisk_tools::IsValid_Variable($this->getFirstLine());
			$secondLine = digirisk_tools::IsValid_Variable($this->getSecondLine());
			$codePostal = digirisk_tools::IsValid_Variable($this->getPostalCode());
			$city = digirisk_tools::IsValid_Variable($this->getCity());
			$latitude = (float) digirisk_tools::IsValid_Variable($this->getLatitude());
			$longitude = (float) digirisk_tools::IsValid_Variable($this->getLongitude());
			$status = digirisk_tools::IsValid_Variable($this->getStatus());
		}

		$address_args = array(
			'ligne1' => $firstLine,
			'ligne2' => $secondLine,
			'ville' => $city,
			'codePostal' => $codePostal,
			'latitude' => $latitude,
			'longitude' => $longitude,
			'Status' => $status,
		);

		//Query creation
		$address_query_treatment = false;
		if($id == 0) {// Insert in data base
			$address_query_treatment = $wpdb->insert( TABLE_ADRESSE, $address_args );
		}
		else {//Update of the data base
			$address_query_treatment = $wpdb->update( TABLE_ADRESSE, $address_args, array( 'id' => $id, ) );
		}

		//Query execution
		if ( false !== $address_query_treatment ) {//Their is no trouble
			$id = $wpdb->insert_id;
			if($this->getId() == null) {
				$this->setId($id);
			}
		}
		else {//Their is some troubles
			$this->setStatus("error");
		}
	}


	/**
	 * Load the Address with identifier key
	 */
	function load()
	{
		global $wpdb;
		$id = (int) digirisk_tools::IsValid_Variable($this->getId());
		if($id != 0)
		{
			$wpdbAddress = $wpdb->get_row( "SELECT * FROM " . TABLE_ADRESSE . " WHERE id = " . $id);

			if($wpdbAddress != null)
			{
				$this->setId($wpdbAddress->id);
				$this->setFirstLine($wpdbAddress->ligne1);
				$this->setSecondLine($wpdbAddress->ligne2);
				$this->setPostalCode($wpdbAddress->codePostal);
				$this->setCity($wpdbAddress->ville);
				$this->setLatitude($wpdbAddress->latitude);
				$this->setLongitude($wpdbAddress->longitude);
				$this->setStatus($wpdbAddress->Status);
			}
		}
	}
}