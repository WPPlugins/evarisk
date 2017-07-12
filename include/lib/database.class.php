<?php
/**
 * Database class
 * 
 * This file allows to define the different database tools
 * @author Evarisk <dev@evarisk.com>
 * @version 1.0
 * @package evarisk
 * @subpackage librairies
 */

/**
 * Database tools
 * @package evarisk
 * @subpackage librairies
 */
class eva_database
{

	/**
	*	Prepare the different field before use them in the query
	*
	*	@param array $prm An array containing the fields to prepare
	*	@param mixed $operation The type of query we are preparing the vars for
	*
	*	@return mixed $preparedFields The fields ready to be injected in the query
	*/
	function prepareQuery($prm, $operation = 'creation')
	{
		$preparedFields = array();

		foreach($prm as $field => $value)
		{
			if($field != 'id')
			{
				if($operation == 'creation')
				{
					$preparedFields['fields'][] = $field;
					$preparedFields['values'][] = "'" . ($value) . "'";
				}
				elseif($operation == 'update')
				{
					$preparedFields['values'][] = $field . " = '" . ($value) . "'";
				}
			}
		}

		return $preparedFields;
	}

	/**
	*	Get the field list into a database table
	*
	*	@param string $table_name The name of the table we want to retrieve field list for
	*
	*	@return object $field_list A wordpress database object containing the different field of the table
	*/
	function get_field_list($table_name)
	{
		global $wpdb;

		$query = $wpdb->prepare("SHOW COLUMNS FROM " . $table_name, "");
		$field_list = $wpdb->get_results($query);

		return $field_list;
	}
	/**
	*	Get a field defintion into a database table
	*
	*	@param string $table_name The name of the table we want to retrieve field list for
	*
	*	@return object $field A wordpress database object containing the field definition into the database table
	*/
	function get_field_definition($table_name, $field)
	{
		global $wpdb;

		$query = $wpdb->prepare("SHOW COLUMNS FROM " . $table_name . " WHERE Field = %s", $field);
		$fieldDefinition = $wpdb->get_results($query);

		return $fieldDefinition;
	}

	/**
	*	Make a translation of the different database field type into a form input type
	*
	*	@param string $table_name The name of the table we want to retrieve field input type for
	*
	*	@return array $field_to_form An array with the list of field with its type, name and value
	*/
	function fields_to_input($table_name)
	{

		$list_of_field_to_convert = eva_database::get_field_list($table_name);

		$field_to_form = self::fields_type($list_of_field_to_convert);

		return $field_to_form;
	}

	function fields_type($list_of_field_to_convert)
	{
		$field_to_form = array();
		$i = 0;
		foreach ($list_of_field_to_convert as $Key => $field_definition){

			$field_to_form[$i]['name'] = $field_definition->Field;
			$field_to_form[$i]['value'] = $field_definition->Default;

			$type = 'text';
			if(($field_definition->Key == 'PRI') || ($field_definition->Field == 'creation_date') || ($field_definition->Field == 'last_update_date'))
			{
				$type =  'hidden';
			}
			else
			{
				$fieldtype = explode('(',$field_definition->Type);
				if(!empty($fieldtype[1]))$fieldtype[1] = str_replace(')','',$fieldtype[1]);

				if(($fieldtype[0] == 'char') || ($fieldtype[0] == 'varchar') || ($fieldtype[0] == 'int'))
				{
					$type = 'text';
				}
				elseif($fieldtype[0] == 'text')
				{
					$type = 'textarea';
				}
				elseif($fieldtype[0] == 'enum')
				{
					$fieldtype[1] = str_replace("'","",$fieldtype[1]);
					$possible_value = explode(",",$fieldtype[1]);

					if(count($possible_value) > 1)
					{
						$type = 'select';
					}
					else
					{
						$type = 'radio';
					}

					$field_to_form[$i]['possible_value'] = $possible_value;
				}
			}
			$field_to_form[$i]['type'] = $type;
			
			$i++;
		}
		return $field_to_form;
	}

	/**
	*	Save a new attribute in database
	*
	*	@param array $informationsToSet An array with the different information we want to set
	*
	*	@return string $requestResponse A message that allows to know if the creation has been done correctly or not
	*/
	function save($informationsToSet, $dataBaseTable)
	{
		global $wpdb;
		$requestResponse = '';

		$updateResult = $wpdb->insert($dataBaseTable, $informationsToSet, '%s');
		if( $updateResult != false )
		{
			$requestResponse = 'done';
		}
		else
		{
			$requestResponse = 'error';
		}

		return $requestResponse;
	}
	/**
	*	Update an existing attribute in database
	*
	*	@param array $informationsToSet An array with the different information we want to set
	*
	*	@return string $requestResponse A message that allows to know if the update has been done correctly or not
	*/
	function update($informationsToSet, $id, $dataBaseTable)
	{
		global $wpdb;
		$requestResponse = '';

		$updateResult = $wpdb->update($dataBaseTable, $informationsToSet , array( 'id' => $id ), '%s', array('%d') );

		if( $updateResult == 1 )
		{
			$requestResponse = 'done';
		}
		elseif( $updateResult == 0 )
		{
			$requestResponse = 'nothingToUpdate';
		}
		elseif( $updateResult == false )
		{
			$requestResponse = 'error';
		}

		return $requestResponse;
	}

}