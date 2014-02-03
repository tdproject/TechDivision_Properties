<?php

/**
 * License: GNU General Public License
 *
 * Copyright (c) 2009 TechDivision GmbH.  All rights reserved.
 * Note: Original work copyright to respective authors
 *
 * This file is part of TechDivision GmbH - Connect.
 *
 * TechDivision_Properties is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * TechDivision_Properties is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,
 * USA.
 *
 * @package TechDivision_Properties
 */

require_once 'TechDivision/Collections/HashMap.php';
require_once 'TechDivision/Lang/String.php';
require_once 'TechDivision/Lang/Exceptions/NullPointerException.php';
require_once
	'TechDivision/Properties/Exceptions/PropertyFileParseException.php';
require_once
	'TechDivision/Properties/Exceptions/PropertyFileStoreException.php';
require_once
	'TechDivision/Properties/Exceptions/PropertyFileNotFoundException.php';

/**
 * The Properties class represents a persistent set of properties.
 * The Properties can be saved to a stream or loaded from a stream.
 * Each key and its corresponding value in the property list is a string.
 *
 * A property list can contain another property list as its "defaults";
 * this second property list is searched if the property key is not
 * found in the original property list.
 *
 * Because Properties inherits from HashMap, the put method can be
 * applied to a Properties object. Their use is strongly discouraged
 * as they allow the caller to insert entries whose keys or values are
 * not Strings. The setProperty method should be used instead. If the
 * store or save method is called on a "compromised" Properties object
 * that contains a non-String key or value, the call will fail.
 *
 * @package TechDivision_Properties
 * @author Tim Wagner <t.wagner@techdivision.com>
 * @copyright TechDivision GmbH
 * @link http://www.techdivision.com
 * @license GPL
 */
class TechDivision_Properties_Properties
    extends TechDivision_Collections_HashMap {

	/**
	 * This member is TRUE if the sections should be parsed, else FALSE
	 * @var boolean
	 */
	protected $_sections = false;

	/**
	 * The default constructor.
	 *
	 * @param Properties $defaults
	 * @return void
	 */
	public function __construct(
	    TechDivision_Properties_Properties $defaults = null)
	{
		// check if properties are passed
		if ($defaults != null) {
		    // if yes set them
			TechDivision_Collections_HashMap::__construct($defaults->toArray());
		} else {
			TechDivision_Collections_HashMap::__construct();
		}
	}

	/**
	 * Factory method.
	 *
	 * @param TechDivision_Properties_Properties $defaults
	 * 		Default properties to initialize the new ones with
	 * @return TechDivision_Properties_Properties The initialized properties
	 */
	public static function create(
	    TechDivision_Properties_Properties $defaults = null)
	{
        return new TechDivision_Properties_Properties($defaults);
	}

	/**
	 * Reads a property list (key and element pairs)
	 * from the passed file.
	 *
	 * @param string $file
	 * 		The path and the name of the file to load the properties from
	 * @param sections $sections
	 * 		Has to be true to parse the sections
	 * @return TechDivision_Properties_Properties
	 * 		The initialized properties
	 * @throws PropertyFileParseException
	 * 		Is thrown if an error occurse while parsing the property file
	 * @throws PropertyFileNotFoundException
	 * 		Is thrown if the property file passed as parameter does not
	 * 		exist in the include path
	 */
	public function load($file, $sections = false)
	{
		// try to load the file content
		$content = @file_get_contents($file, FILE_USE_INCLUDE_PATH);
		// check if file has succuessfully been loaded
		if (!$content) {
			// throw an exception if the file can not be found in the include path
			throw new TechDivision_Properties_Exceptions_PropertyFileNotFoundException(
				'File ' . $file . ' not found in include path'
			);
		}	
		// parse the file content
		$properties = parse_ini_string(
			$content, 
			$this->_sections = $sections
		);
		// check if property file was parsed successfully
		if ($properties == false) {				
			// throw an exception if an error occurs
			throw new TechDivision_Properties_Exceptions_PropertyFileParseException(
				'File ' . $file . ' can not be parsed as property file'
			);
		}
		// set the found values
		$this->_items = $properties;
		// return the initialized properties
		return $this;
	}

	/**
	 * Stores the properties in the property file. This method is
	 * NOT using the include path for storing the file.
	 *
	 * @param string $file
	 * 		The path and the name of the file to store the properties to
	 * @return void
	 * @throws TechDivision_Properties_Exceptions_PropertyFileStoreException
	 * 		Is thrown if the file could not be written
	 * @todo
	 * 		Actually only properties without sections will be stored, if a
	 * 		section is specified, then it will be ignored
	 */
	public function store($file)
	{
		// create a new file or replace the old one if it exists
		if (($handle = @fopen($file, "w+")) === false) {
			throw new TechDivision_Properties_Exceptions_PropertyFileStoreException(
				'Can\'t open property file ' . $file . ' for writing'
			);
		}
		// store the property in the file
		foreach ($this->_items as $name => $value) {
		    $written = @fwrite(
		        $handle,
		        $name . " = " . addslashes($value) . PHP_EOL
		    );
			if ($written === false) {
				throw new TechDivision_Properties_Exceptions_PropertyFileStoreException(
					'Can\'t attach property with name ' .
				    $name . ' to property file ' . $file
				);
			}
		}
		// saves and closes the file and returns TRUE if the
		// file was written successfully
		if (!@fclose($handle)) {
			throw new TechDivision_Properties_Exceptions_PropertyFileStoreException(
				'Error while closing and writing property file ' . $file
			);
		}
	}

	/**
	 * Searches for the property with the specified
	 * key in this property list.
	 *
	 * @param string $key Holds the key of the value to return
	 * @param string $section
	 * 		Holds a string with the section name to return the key for
	 * 		(only matters if sections is set to TRUE)
	 * @return string Holds the value of the passed key
	 * @throws NullPointerException
	 * 		Is thrown if the passed key, or, if sections are TRUE,
	 * 		the passed section is NULL
	 */
	public function getProperty($key, $section = null)
	{
		// initialize the property value
		$property = null;
		// check if the sections are included
		if ($this->_sections) {
			// if the passed section OR the passed key is NULL
			// throw an exception
			if ($section == null) {
				throw new TechDivision_Lang_Exceptions_NullPointerException(
					'Passed section is null'
				);
			}
			if ($key == null) {
				throw new TechDivision_Lang_Exceptions_NullPointerException(
					'Passed key is null'
				);
			}
			// if the section exists ...
			if (TechDivision_Collections_AbstractCollection::exists($section)) {
				// get all entries of the section
				$entries = new TechDivision_Collections_HashMap(
				    $this->get($section)
				);
				if ($entries->exists($key)) {
					// if yes set it
					$property = $entries->get($key);
				}
			}
		} else {
			// if the passed key is NULL throw an exception
			if ($key == null) {
				throw new TechDivision_Lang_Exceptions_NullPointerException(
					'Passed key is null'
			    );
			}
			// check if the property exists in the internal list
			if ($this->exists($key)) {
				// if yes set it
				$property = $this->get($key);
			}
		}
		// return the property or null
		return $property;
	}

	/**
	 * Calls the HashMap method add.
	 *
	 * @param string $key Holds the key of the value to return
	 * @param mixed $value Holds the value to add to the properties
	 * @param string $section
	 * 		Holds a string with the section name to return the key for
	 * 		(only matters if sections is set to TRUE)
	 * @return void
	 * @throws NullPointerException
	 * 		Is thrown if the passed key, or, if sections are TRUE,
	 * 		the passed section is NULL
	 */
	public function setProperty($key, $value, $section = null)
	{
		// check if the sections are included
		if ($this->_sections) {
			// if the passed section OR the passed key is NULL
			// throw an exception
			if ($section == null) {
				throw new TechDivision_Lang_Exceptions_NullPointerException(
					'Passed section is null'
				);
			}
			if ($key == null) {
				throw new TechDivision_Lang_Exceptions_NullPointerException(
					'Passed key is null'
				);
			}
			// if the section exists ...
			if (TechDivision_Collections_AbstractCollection::exists($section)) {
				// get all entries of the section
				$entries = new TechDivision_Collections_HashMap(
				    $this->get($section)
				);
				$entries->add($key, $value);
			}
		} else {
			// if the passed key is NULL throw an exception
			if ($key == null) {
				throw new TechDivision_Lang_Exceptions_NullPointerException(
					'Passed key is null'
				);
			}
			// add the value with the passed
			$this->add($key, $value);
		}
	}

	/**
	 * Returns all properties with their keys
	 * as a string.
	 *
	 * @return string String with all key -> properies pairs
	 */
	public function __toString()
	{
		// initialize the return value
		$return = "";
		// iterate over all items and concatenate the values to
		// the return string
		foreach ($this->_items as $key => $value) {
			// if sections are set to true there can be subarrays
			// with key/value pairs
			if (is_array($value)) {
				// set the section and add the key/value pairs to the section
				$return .= "[" . $key . "]";
				foreach ($value as $sectionKey => $sectionValue) {
					$return .= $sectionKey . "=" . $sectionValue . PHP_EOL;
				}
			}
			// add the key/value pair
			$return .= $key . "=" . $value . PHP_EOL;
		}
		// return the string
		return $return;
	}

	/**
	 * Returns all properties with their keys
	 * as a String.
	 *
	 * @return TechDivision_Lang_String String with all key -> properies pairs
	 */
	public function toString()
	{
		// return the String
		return new TechDivision_Lang_String($this->__toString());
	}

	/**
	 * Returns all key values as
	 * an array.
	 *
	 * @return array The keys as array values
	 */
	public function getKeys()
	{
	    // check if the propery file is sectioned
	    if ($this->_sections) {
            // initialize the array for the keys
	        $keys = array();
            // iterate over the sections and merge all sectioned keys
	        foreach ($this->_items as $key => $item) {
	            $keys = array_merge($keys, array_keys($item));
	        }
            // return the keys
	        return $keys;
	    } else {
		    return array_keys($this->_items);
	    }
	}
}