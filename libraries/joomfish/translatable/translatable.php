<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2013, Think Network GmbH, Konstanz
 *
 * All rights reserved.  The Joom!Fish project is a set of extentions for
 * the content management system Joomla!. It enables Joomla!
 * to manage multi lingual sites especially in all dynamic information
 * which are stored in the database.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------------
 * @package joomfish
 * @subpackage Models
 *
*/

/**
 * This interface describes a generic API for translateable objects. 
 * <p>It is typical for a translatable object that it retrieves its data
 * from any kind of data source and a language or localization identifier 
 * describes the various versions.</p>
 * <p>The interface can be used by any kind of model, table or other
 * representation of an in-memory-object reference. It does not matter
 * how the object generates or loads its data for the further on processing
 * within the translation process of JoomFish</p>
 * <p>The interface has a specific JoomFish namespace (JF) to separate it
 * from any general translatable interface the Joomla framework might get in
 * future. However it is accepted that this interface (even alone) is used
 * by third party extensions of any kind</p>
 *
 * @package joomfish
 * @subpackage interface
 * @copyright 2003 - 2013, Think Network GmbH, Konstanz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Revision: 1495 $
 * @author Alex Kempkens
 */
interface iJFTranslatable {
	/** Standard constructor
	 *
	 * @param	languageID		ID of the associated language
	 * @param	elementTable	Reference to the ContentElementTable object
	 */
	public function __construct( $languageID,& $contentElement, $id=-1 );

	/** Loads the information based on a certain content ID
	 */
	public function loadFromContentID( $id=null );

	/** Reads the information from the values of the form
	 * The content element will be loaded first and then the values of the override
	 * what is actually in the element
	 *
	 * @param	array	The values which should be bound to the object
	 * @param	string	The field prefix
	 * @param	string	An optional field
	 * @param 	boolean	try to bind the values to the object
	 * @param 	boolean	store original values too
	 */
	public function bind( $formArray, $prefix="", $suffix="", $tryBind=true, $storeOriginalText=false );
		
	/** Reads the information out of an existing JTable object into the translationObject.
	 *
	 * @param	object	instance of an mosDBTable object
	 */
	public function updateMLContent( &$dbObject , $language);

	/**
	 * This method copies a currect database object into the translations
	 * The original object might be the same kind of object and it is not required that
	 * both objects are of the type mosDBTable!
	 *
	 * @param object $dbObject new values for the translation
	 * @param object $origObject original values based on the db for reference
	 */
	public function copyContentToTranslation( &$dbObject, $origObject );

	/** Reads some of the information from the overview row
	 */
	public function readFromRow( $row );

	/** Returns the content element fields which are text and can be translated
	 *
	 * @param	boolean	onle translateable fields?
	 * @return	array	of fieldnames
	 */
	public function getTextFields( $translation = true );
	/**
	 * Returns the field type of a field
	 *
	 * @param string $fieldname
	 */
	public function getFieldType($fieldname);

	/** Sets all fields of this content object to a certain published state
	*/
	public function setPublished( $published );

	/** Updates the reference id of all included fields. This
	 * Happens e.g when the reference object was created new
	 *
	 * @param	referenceID		new reference id
	 */
	public function updateReferenceID( $referenceID );

	/** Stores all fields of the content element
	 */
	public function store();

	/** Checkouts all fields of this content element
	*/
	public function checkout( $who, $oid=null );

	/** Checkouts all fields of this content element
	*/
	public function checkin( $oid=null );

	/** Delets all translations (fields) of this content element
	*/
	public function delete( $oid=null );
	
	/** Returns the content element table this content is based on
	 */
	public function  getTable();
	
	/**
	 * Only used in native storage classes - for joomfish based tables create a do-nothing method
	 */
	public function generateTranslationMap( &$article, $isNew, $tablename, $elementTable=false);
}