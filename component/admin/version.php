<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2012, Think Network GmbH, Munich
 *
 * All rights reserved. The Joom!Fish project is a set of extentions for
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------------
 * $Id: version.php 226M 2011-05-27 07:29:41Z (local) $
 * @package joomfish
 * @subpackage version
 *
*/


defined( '_JEXEC' ) or die( 'Restricted access' );

class JoomFishVersion {
	var $_version	= '2.6.0';
	var $_versionid	= 'Tarw';
	var $_date		= '2011-10-31';
	var $_status	= 'Preview';
	var $_revision	= '$Rev: 1520 $';
	var $_copyyears = '2003-2011';

	/**
	 * This method delivers the full version information in one line
	 *
	 * @return string
	 */
	function getVersion() {
		return 'V' .$this->_version. ' ('.$this->_versionid.')';
	}

	/**
	 * This method delivers a special version String for the footer of the application
	 *
	 * @return string
	 */
	function getCopyright() {
		return '&copy; ' .$this->_copyyears;
	}

	/**
	 * Returns the complete revision string for detailed packaging information
	 *
	 * @return unknown
	 */
	function getRevision() {
		return '' .$this->_revision. ' (' .$this->_date. ')';
	}
}
