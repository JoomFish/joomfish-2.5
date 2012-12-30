<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2013, Think Network GmbH, Konstanz, 2007-2009 GWE Systems Ltd
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
 * @subpackage jfrouter
 * @version 2.0
 *
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

function JFRouterHelperContact ($router,&$uri){
	static $aliases;
	static $cataliases;
	
	if (!isset($aliases)){
		$aliases = array();
		$cataliases = array();
	}
	
	$id=intval($uri->getVar("id",0));
	$catid=intval($uri->getVar("catid",0));
	
	$alias = $uri->getVar("alias",false);
	$catalias = $uri->getVar("catalias",false);
	
	$db = JFactory::getDBO();
	if ($id>0 && !$alias ){
		if (!array_key_exists($id,$aliases)){
			$sql = "SELECT c.alias FROM #__contact_details as c WHERE id=".$id;
			$db->setQuery($sql);
			$aliases[$id]=$db->loadResult();
		}
		$alias = $aliases[$id];
		$uri->setVar("alias",$alias);
		
	}
	if ($catid>0 && !$catalias){
		if (!array_key_exists($catid,$cataliases)){
			$sql = "SELECT cc.alias FROM #__categories as cc WHERE id=".$catid;
			$db->setQuery($sql);
			$cataliases[$catid]=$db->loadResult();
		}
		$catalias = $cataliases[$catid];
		$uri->setVar("catalias",$catalias);
		
	}
}
