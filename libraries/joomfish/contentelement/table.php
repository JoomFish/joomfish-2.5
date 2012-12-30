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



// Don't allow direct linking
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomfish.contentelement.tablefield');

/**
 * Description of a content element table.
 *
 * @package joomfish
 * @subpackage administrator
 * @copyright 2003 - 2013, Think Network GmbH, Konstanz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Revision: 1502 $
 * @author Alex Kempkens
 */
class ContentElementTable {
	public $Name;
	public $Fields;
	public $Filter;

	/** Standard constructor
	*/
	public function __construct( $tableElement ) {
		$this->Name = trim( $tableElement->getAttribute( 'name' ) );

		$tableFields = $tableElement->getElementsByTagName( 'field' );
		$this->Fields =array();
		$this->IndexedFields =array();
		foreach( $tableFields as $tablefieldElement ) {
			$field = new ContentElementTablefield( $tablefieldElement );
			$this->Fields[] = $field;
			$this->IndexedFields[$field->Name] = $field;
		}

		$filterElement = $tableElement->getElementsByTagName('filter');
		if( $filterElement && $filterElement->length>0 ) {
			$this->Filter = $filterElement->item(0)->textContent;
		}
	}

	/** Retrieves one field based on the name
	 * @param	string	Fieldname
	 * @return	object	field
	 */
	public function getField( $name ) {
		$ret_field = null;
		foreach( $this->Fields  as $field ) {
			if ($field->Name == $name ) {
				$ret_field = $field;
				break;
			}
		}

		return $ret_field;
	}
}
?>
