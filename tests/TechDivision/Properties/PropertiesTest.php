<?php

/**
 * License: GNU General Public License
 *
 * Copyright (c) 2009 TechDivision GmbH.  All rights reserved.
 * Note: Original work copyright to respective authors
 *
 * This file is part of TechDivision GmbH - Connect.
 *
 * faett.net is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * faett.net is distributed in the hope that it will be useful,
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

require_once "TechDivision/Properties/Properties.php";

/**
 * This is the test for the Properties class.
 *
 * @package TechDivision_Properties
 * @author Tim Wagner <t.wagner@techdivision.com>
 * @copyright TechDivision GmbH
 * @link http://www.techdivision.com
 * @license GPL
 */
class TechDivision_Properties_PropertiesTest
    extends PHPUnit_Framework_TestCase
{

	/**
	 * This test tries to load a not existent property file
	 * and expects an exception therefore.
	 *
	 * @return void
	 */
	public function testLoadWithPropertyFileNotFoundException()
	{
	    // set the expected exception
	    $this->setExpectedException(
	        'TechDivision_Properties_Exceptions_PropertyFileNotFoundException'
	    );
	    // initialize and load a not existent property file
		$properties = TechDivision_Properties_Properties::create();
		$properties->load(
			'Invalid/Path/To/File'
		);
	}

	/**
	 * This test tries to load an invalid property file
	 * and expects an exception therefore.
	 *
	 * @return void
	 */
	public function testLoadWithPropertyFileParseException()
	{
	    // set the expected exception
	    $this->setExpectedException(
	        'TechDivision_Properties_Exceptions_PropertyFileParseException'
	    );
	    // initialize and load a not existent property file
		$properties = TechDivision_Properties_Properties::create();
		$properties->load(
			'TechDivision/Properties/invalid.properties'
		);
	}

	/**
	 * This test tries to load a property file
	 * without sections.
	 *
	 * @return void
	 */
	public function testLoadWithoutSections()
	{
	    // initialize and load a simple property file
		$properties = TechDivision_Properties_Properties::create();
		$properties->load(
			'TechDivision/Properties/test.no.sections.properties'
		);
        // check the values
		$this->assertEquals(
			'Foo test',
		    $properties->getProperty('property.key.01')
		);
		$this->assertEquals(
			'Bar test',
		    $properties->getProperty('property.key.02')
		);
	}

	/**
	 * This test tries to load a property file
	 * without sections.
	 *
	 * @return void
	 */
	public function testLoadWithSections()
	{
	    // initialize and load a sectioned property file
		$properties = TechDivision_Properties_Properties::create();
		$properties->load(
			'TechDivision/Properties/test.with.sections.properties',
		    true
		);
        // check the values
		$this->assertEquals(
			'Foo test',
		    $properties->getProperty('property.key.01', 'foo')
		);
		$this->assertEquals(
			'Bar test',
		    $properties->getProperty('property.key.02', 'foo')
		);
        // check the values
		$this->assertEquals(
			'Test foo',
		    $properties->getProperty('property.key.03', 'bar')
		);
		$this->assertEquals(
			'Test bar',
		    $properties->getProperty('property.key.04', 'bar')
		);
	}

	/**
	 * This test tries to store a property file
	 * without sections.
	 *
	 * @return void
	 */
	public function testStoreWithoutSections()
	{
	    // create a new property file
		$created = TechDivision_Properties_Properties::create();
        // set some properties
		$created->setProperty('property.key.01', 'Foo test');
		$created->setProperty('property.key.02', 'Bar test');
        // store the property to a file
		$created->store(
		    $toStore = 'TechDivision/Properties/stored.test.properties'
		);
	    // initialize and load the stored property file
		$properties = TechDivision_Properties_Properties::create();
		$properties->load($toStore);
        // check the values
		$this->assertEquals(
			'Foo test',
		    $properties->getProperty('property.key.01')
		);
		$this->assertEquals(
			'Bar test',
		    $properties->getProperty('property.key.02')
		);
	}

	/**
	 * This test tries to store a property file to an
	 * invalid path and expects an exception therefore.
	 *
	 * @return void
	 */
	public function testStoreWithPropertyFileStoreException()
	{
	    // set the expected exception
	    $this->setExpectedException(
	    	'TechDivision_Properties_Exceptions_PropertyFileStoreException'
	    );
	    // create a new property file
		$created = TechDivision_Properties_Properties::create();
        // set some properties
		$created->setProperty('property.key.01', 'Foo test');
        // try store the property file
		$created->store(
		    $toStore = '/Invalid/Path/In/FileSystem'
		);
	}

	/**
	 * This test tries to load the keys of property file
	 * without sections.
	 *
	 * @return void
	 */
	public function testGetKeysWithoutSections()
	{
	    // initialize and load the stored property file
		$properties = TechDivision_Properties_Properties::create();
		$properties->load(
			'TechDivision/Properties/test.no.sections.properties'
		);
        // load the keys
        $keys = $properties->getKeys();
        // check the expected keys
        $this->assertTrue(in_array('property.key.01', $keys));
        $this->assertTrue(in_array('property.key.02', $keys));
	}

	/**
	 * This test tries to load the keys of sectioned
	 * property file.
	 *
	 * @return void
	 */
	public function testGetKeysWithSections()
	{
	    // initialize and load the stored property file
		$properties = TechDivision_Properties_Properties::create();
		$properties->load(
			'TechDivision/Properties/test.with.sections.properties',
		    true
		);
        // load the keys
        $keys = $properties->getKeys();
        // check the expected keys
        $this->assertTrue(in_array('property.key.01', $keys));
        $this->assertTrue(in_array('property.key.02', $keys));
        $this->assertTrue(in_array('property.key.03', $keys));
        $this->assertTrue(in_array('property.key.04', $keys));
	}
}