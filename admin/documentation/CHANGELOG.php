<?
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2012, Think Network GmbH, Munich
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
 * $Id: CHANGELOG.php 238 2011-06-13 08:33:38Z alex $
 *
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

1. Copyright and disclaimer
---------------------------
This application is opensource software released under a variant of the GPL.
Please see source code and the LICENSE file for more details.

Copyright (C) 2003 - 2012, Think Network GmbH, Munich
- All Rights Reserved.


2. Changelog
------------
This is a non-exhaustive (but still near complete) changelog for
the Joom!Fish 2.x, including beta and release candidate versions.

The Joom!Fish 2.x is based on the JoomFish 1.8 releases but includes some
drastic technical changes.


Legend:

* -> Security Fix
# -> Bug Fix
+ -> Addition
^ -> Change
- -> Removed
! -> Note

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

2011-06-12 Alex Kempkens
 # 342: 2.2 not translating meta-tags - http://www.joomfish.net/forum/viewtopic.php?f=47&t=9186&p=34147#p34147

2011-05-29 Alex Kempkens
 # ensured that the news module is not shown if the RSS feed is not available

2011-05-06 Alex Kempkens
 ! removed the necessarity for the view. The view is removed completely from the installation and all other references

2011-04-16 Alex Kempkens
  # [#24270] JF fails to change current language on visitor first visit
 
2011-04-16 Geraint Edwards
 # Ensure insert/select type subquery doesn't trigger setreftables
 # ensure profile data is not processed if it isn't an array
 # ensure content items marked as non-default language don't trigger the creation of inappropriate translations in the frontend when updating content

2011-03-24 Alex Kempkens
 # fixing issue with wrong media path in flag image determination

2011-01-19 Geraint Edwards
 # Reinstate fallback language support
 
2011-01-04 Geraint Edwards
 # Missing clone when creating introtext translation out of fulltext
 # Fix language switching module when using subdomain
 # Allow for non-numeric primary key values in translation
 # Replaced correct language string reference for default language in content languages page
 # changed error messages to not show "failed to get table info" regardless of the error
 # remove bad reference to order lists in elements overview
 # Add missing database indexes
 # Correct handling of introtext and fulltext when only one has been translated

 2010-10-14 Alex Kempkens
 # Fixed problem with image path in windows environments
 
2010-10-05 Alex Kempkens
 ! preparing for the club public release of 2.1
 ! going to rename 2.1 into 2.2 to reduce confusion with add-ons

2010-10-04 Alex Kempkens
 # Fixed issue with manage translations
 # Fixed issue with translation statistics

2010-09-24 Alex Kempkens
 # Fixed an issue installation loading wrong classes
 # Fixed JFLanguagesModule to work correctly with table classes
 + Method to installer auto loading already installed languages if language table is empty

2010-09-06 Alex Kempkens
 # Fixed issue with JFTable class not loading correctly in PHP 5.2 environments
 # Fixed missing language strings in latest commit en-GB reference

2010-08-26 Alex Kempkens
 + added Khemer flag to media repository
  
2010-08-24 Alex Kempkens
 # Removed create view from inital database population script to avoid issues during upgrade process
 
2010-08-22 Alex Kempkens
 # corrected problem with extensionHelper called without JFile being imported

2010-08-18 Alex Kempkens
 # Integrated automatic SQL structure upgrade routine during installation process
 ! language files updated to include special keys
 # fixed smaller bugs with declaration of classes
 # prepared for stable release
 
2010-08-17 Alex Kempkens
 # Changed presentation of long fields in translation overview. The text cut's off now and a tooltip shows the full text 
 # double checked all files to include JEXEC checks - thx to Andrew for the jscan script.
 # split TranslationFilter.php file into class depended pieces
 # _createFilter functions converted to public createFilter
 # Corrected the declaration for all existing classes
 # Corrected methods in controllerHelper to have correct naming for public method
 # Changed the help & tutorials screen to include the most current information and better introduction to the first time users.
 ! The content of these files is now moved to /admin/help/en-GB/. The idea is to provide an full translated package of all help files this way.
 ! Translation of all files in these directories would be perfect

2010-08-16 Alex Kempkens
 # Fixed problem with mod_jflanguageselection and URL's including array definitions
 # Moved component mapping information from mod_translate parameter to content element files.
 ! The module parameter overwrite existing mapping in the content element file 
 # Corrected core content element files to include component mapping information
 # [#19541] Error 500 with joomfish and joomla 1.5.15 with debug activated resolved - thx simon gendrin
 # [#21802] Some flags - thx Andrew McCarthy
 # [#12752] The dropdown view of the module should allways be in the dropdown even if there is only one language

2010-08-04 Alex Kempkens
 + User splash screen with information and reference to support information
 # changed parameter section titles to use css styles with better visability

2010-07-29 Alex Kempkens
 + Introduced iJFTranslatable interface for the implementation of external models or tables from 3rd party extensions
 ! The iJFTranslatable interface is still under heavy development and might change until the final release of version 2.2
 ! The idea is that any 3rd party extension can specify in their own translation objects using what ever technique to store and identify the various
 ! language versions.
 # changed ContentObject to use new interface and PHP5 syntax
 # fixed issue with ContentObject accessing private variables of JFManager
 # config issue with  'Overwrite global config values' http://www.joomfish.net/forum/viewtopic.php?f=15&t=5318

2010-07-22 Alex Kempkens
 # fixed problem with CSS for the module
 
2010-07-14 Alex Kempkens
 # Corrented mod_translate package structure
 # implemented special installation routine to determine old table structure
 
2010-07-08 Alex Kempkens
 # Implemented config system parameters dialog as modal window in language manager
 # corrected JFLanguage class methods to load language information correctly
 # updated images to be conform with new JoomFish logos
 # Implementing delete and add methods to the language manager
 
2010-07-06 Alex Kempkens
 # Corrected flag path for mod_translate
 # Corrected various methods in JoomFishManager to retrieve the language data correctly
 # Made further class PHP 5 conform
 
2010-07-06 Geraint Edwards
 + Integrated initial version of Google translate within translation edit

2010-07-05 Alex Kempkens
 # Optimized language manager user interface
 # completed integration of flag image browser and selection
 + new JLanguage table representation
 ! changed JFLanguage table object to be only place holder for aggregated copies of JLanguage and JFLanguageExt
 ! all thrid party extensions shall only use JFLanguage as primary table. All methods are directed to the corresponding objects
 ! please make sure you are not using TableJLanguage or TableJFLanguageExt directly as they do not keep the information to the other table in synch
 # added special __set method to redirect information
 
2010-07-01 Alex Kempkens
 # Changed the occurance of deprecated attributes (id, code, name, shortcode) to their corresponding new attributes
 # Implemented the default language change within the JoomFish language manager
 # Fixed some minor issues with the select lists for languages
 # changed JoomFishManager to be PHP 5 syntax conform

2010-06-29 Alex Kempkens
 # Changed copyright notice in help screens
 # changed version information
 # corrected mod_jflanguage tests to reflect new language attributes
 # corrected extension helper methods for language image determination to first search for extended image information and later treat Joomla language image like sef information
 
2010-06-25 Alex Kempkens
 # Separation of JFLanguage table class into an exact represenation of the Joomla language table and a special extended version for JoomFish
 # correcting some other email references
 
2010-06-22 Alex Kempkens
 # Refactoring of performance status check -> moved checks into generic system check
 - removed tab for performance tests in cpanel
 - removed special performance test methods
 # changed cpanel module class methods to reflect correct access attributes in PHP 5
 # Corrected copyright dates in various files
 # Updated frontend language file to include only tags used by the core Joomfish frontend
 # Updated admin language file to include new performance check tags
 
2010-06-11 Alex Kempkens
 # Frontend language file for the component cleaned from all unneeded tags
 # Frontend language file changed to 1.6 tag format and changed output for component view
 # Frontend helper changed to PHP 5 scope format
 ! Method _contentElementFields changed to contentElementFields and public access (needed in missing translation plugin)
 # changed missing translation plugin to use public method
 
2010-05-18 Alex Kempkens
 # updating tests to fit to new structure and optimization for tests within ZendEclipse or without
 ! The ModuleHTMLTests are still incomplete which is known

2010-05-10 Alex Kempkens
 # Removed duplicated image detection methods
 + new extensionHelper for generic Joomfish supporting methods
 # refactored jflanguageselection module to use the new extensionHelper instead of duplicated implementation for image/flag detection
 # Updated tests to reflect new classes and structures

2010-04-18 Alex Kempkens
 # support for recovery of existing connections (Martin N. Brampton)

2010-04-15 Alex Kempkens
 # Fixed problem in JFRouter with not defined language parameters in com_content

2010-04-09 Alex Kempkens
 # Moved all flags to the media/com_joomfish/default directory
 # Introduced new parameter to allow specification of default directory for media files
 ! To create new flag set's make sure they are all stored in one directory having the short ISO codes as their name
 ! flag images are expected to be gif files
 ! it is possible to place flags now in your template, assumed location is /templates/<your_template>/ the further path depends on your image path or shortcode
 ! if you use the short code of a language instead of a selected image it is always assumed that the images are located in the subfolder flags/<iso>.gif
 # changed mod_languageselection to recognice the new media directory for flags
 # Update of version information in preparation for 2.1 release
 

2010-03-16 Alex Kempkens
 # fixing even more PHP ereg usage files
 # changed translation of jfrouter configuration to make sure it refers to domains rather than subdomains
 # Fixed a little issue with the JError integratin
 # [215] integrated RSS supported in Joomfish broken, thx gruz
 # Changed copyright years and text
 # Changed SVN properties
 # Fixed problem with showing messages from the controler

2010-03-15 Alex Kempkens
 # Fixing error reporting to use JError methods

2010-02-12 Alex Kempkens
 # fixed copy button in all other editors ;-)
 # changed cpanel display and rendering of templates

2010-01-15 Geraint Edwards
 # Fix JCE copy button

2009-12-16 Alex Kempkens
 # 254: Bug: Button to go back to the Control Panel doesn't work in the Content Elements subsections

2009-12-15 Alex Kempkens
 # correcting default value for default language view
 # packaging second pre-release for club members
 
2009-11-22 Alex Kempkens
 # fixed weblinks field length of url 
 ! http://www.joomfish.net/forum/viewtopic.php?f=16&t=5466&p=23374#p23191

2009-10-23 Alex Kempkens
 # Fixed XXS vulnerability in language switching module

2009-10-06 Geraint Edwards
 # Fix modified date for translations to take account of server offsets
 
2009-10-01 Alex Kempkens
 # fixed little reference issue within jfdatabase
 - removed decorator tests in jfdatabase
 # replacing mldatabase with JFLegacyDatabase to make sure old usage of MambelFish classes breaks
 - removing _JOOMFISH_MANAGER global variable completely; use JoomFishManager::getInstance() instead

2009-09-26 Alex Kempkens
 # removing all old PHP references from PHP4 to be complaint with PHP 5
 
2009-09-20 Alex Kempkens
 # Replacing deprecated use of regular expressions for PHP5.3 compability

2009-09-08 Geraint Edwards
 # PHP 5.3 compatability changes

2009-09-07 Geraint Edwards
 ^ Replace Domit xml libraries with built in PHP5 xml functions
 
2009-08-10 Geraint Edwards
 + Add prehandler function to ContentElementTableField.php to support external menu link translation
 + Add more filters for content elements
 # Enable translation of SQL queries that don't use the AS sql keyword (affects mysql only no mysqli)

2009-08-01 Alex Kempkens
 # Fixed: Can't copy HTML text http://www.joomfish.net/forum/viewtopic.php?f=28&t=4758&start=0

2009-07-22 Alex Kempkens
 # Fixed author information and copyright notice

2009-06-23 Alex Kempkens
 # Wrong link to statistics help - thx tassu

2009-06-22 Alex Kempkens
 # [211330] browser-popup-translate-window-also-needed

2009-06-18 Alex Kempkens
 # changes to implement language translation - thx Selim Alamo (selimoff) for the hint
 + new content element file for languages
 # [#162483] Translate DropDown Names
 # updated CE file copyright information
 # updated version for pre-release

2009-06-04 Alex Kempkens
 + [#14371] New onAfterTranslationSave event, thanks for the patch
 # [#14033] Input field's MaxLength is too short; The maxlength attribute is only used if the field has a specific setting for it!
 # Fixed language problem with jfalternative usage of text NO TRANSLATION AVAILABLE
 + added new language file for en-GB.plg_jfalternative.ini

2009-05-21 Geraint Edwards
 + Add generalised keywords filter framework
 # Fix bad reference to ContenObject Class file


2009-05-13 Geraint Edwards
 ^ Change translation of content to merge intro and full text in the display but keep separate in the database

2009-04-16 Geraint Edwards
 # Fix for 3rd level menu language switching problems
 # Repair language switching when editing/creating translations broken on 14/03/09

==== 31 March 2009 Joom!Fish 2.0.3 Stable release =====

2009-03-31 Alex Kempkens
 - Usage of PHP5 variable declaration in jfdatabase plugin; ^ to PHP4 declaration
 # Installation issue in Joomla 1.5.10
 # reverted ~ Rename Norwegian Flag; after discussion with NO coordinator

2009-03-31 Geraint Edwards
 ~ change Norwegian flag to match ISO name

 2009-03-27 Geraint Edwards
 ~ Enable translation of menu aliases

2009-03-19 Geraint Edwards
~ Rename Norwegian Flag
~ New Georgian Flag

2009-03-14 Alex Kempkens
+ New configuration parameter to switch off the default language in the backend view
! New translation tag added for the config value
! Default value is: show default language (as current behavior)
# Translation model changed to determin the languages instead of JFManager
+ JFModel to integrate centralized method for getting the default site language
# changed translation views to show language selection depending on config

2009-02-06 Geraint Edwards
# Check collation/charset before creating
# Make Check for database key creation mysql 4.1 compliant#
# [#14622] Workaround for bug in Mysql 5.1.30 with null field values - for existing installations

2009-01-29 Alex Kempkens
 # Re: Error deleting orphan files..., thx to lesther
 # Error 500 when deleting language - thx to pellemans

==== 27 January 2009 Joom!Fish 2.0.2 Stable release =====

2009-01-26 Geraint Edwards
# [#14622] Workaround for bug in Mysql 5.1.30 with null field values

2009-01-16 Geraint Edwards
 # Make Joomfish respect limit and count args of setquery - needed to enable content table localisation plugin to work properly

2009-01-09 Geraint Edwards
 # Fix language switcher - when switching on sub menus the parent menu item was not translated

==== 7 January 2009 Joom!Fish 2.0.1 Stable release =====

2009-01-07
 # Minor change in the banner content element (http://www.joomfish.net/forum/viewtopic.php?f=16&t=2961)

2009-01-06 Ivo Apostolov
 ^ Change of headers, version info, adding license. Preparation for 2.0.1
 # Fixing a lot of issues around the language files. Thanks Tassu and Localicer.

2009-01-05 Geraint Edwards commited the fix / Ivo Apostolov (adding the to the log)
 # Fixing the bug with SEF addresses within the frontend module

2009-01-02 Ivo Apostolov
 # Fixing hardcoded database string. Thanks stevekwok (http://www.joomfish.net/forum/viewtopic.php?f=28&t=2917)

==== 31 December 2008 Joom!Fish 2.0 Stable public release =====

2008-12-24 Geraint Edwards
 # [#14089] intercept.jdatabasemysql.php : Missing back quotes in sql request
 # incorrect use of assigning the language variable by reference instead of by value

2008-12-23 Alex Kempkens
 # Corrected usage of translated texts in the various views
 + Language tag for statistics in the sub-menu
 # [#14028] Mod translate generates error if no language exists or is active
 # [12658] language codes are now determint and maped by the language manager. This sound also resolve the issue related to the migration
 # [#14038] Add unlink in the installation routine for prePost_translations.xml
 # [#14105] Deleting non translated item gives inappropriate message.
 # [#13951] change copy delete icons to new style in edit translation

2008-12-07 Ivo Apostolov
 # Language file field has no reason to be editable. See http://www.joomfish.net/forum/viewtopic.php?f=28&t=2727

2008-12-05 Geraint Edwards
 # [#13991] Filtering on sections/categories lead to blank list on return
 + [#13536] Adding filter for archived articles
 ^ Changed logic of language/CE selection so that javascript checks that both are real values before sumitting the form :)

2008-12-04 Geraint Edwards
 # [#13994] Apostrophe is not escaped in filtering and leads to a mass SQL error
 # [#13996] Fix the modal of the Direct Translations module - added auto close of modal window
 # [#13852] Content Element Installer - missing JFile import

2008-12-03 Geraint Edwards
 # Renable correct testing for overwritting global config values
 # [#13178] Correct the escaping of language config values
 # [#12752] Disable rather than remove languages suppressed by menu localisation plugin

2008-12-02 Geraint Edwards
 # in getNumRows must allow fallback language otherwise contenttablelocalisation plugin is skipped
 # [#12747] enable publishers and above to see unpublished translations in the front by default
 # Remove "x" default value for defaulttext in language configuration
 # [#13613] Add content element field ebuttons - false=> no editor buttons, array => buttons to suppress.  Default is to suppress readmore

2008-11-30 Alex Kempkens
 # Admin Module for translations now automatically opens the translation dialog

2008-11-27 Ivo Apostolov
 # Correcting the SQL file - spelling

2008-11-26 Alex Kempkens
 # [13899] Fixed issue with missing translation plugin and language file localtion (thx JM)
 # Reshaped the translate module for a better user experience
 # [#12943] Front-end editing saves in translation in stead of 'normal' article when user sets front-end language

2008-11-25 Geraint Edwards
 # [#13854] Fields in jf_content
 # [#12803] Copy function doesn't works when found the symbol '
 ^ [#13856] Add index to jf_content plus other indicies - performance gain in front and backend
 # [#13853] Uninstalling the last used content element brings an error.  Also added trap for bad content element file  in translation overview
 # [#13852] Content Element Installer.  I also changed the file copy function to be JFTP compatible.

2008-11-24 Ivo Apostolov
 # Fixing the uninstall bug

2008-11-16 Geraint Edwards
 # Bad field values for unused grid ordering hidden fields in orphan and translation lists
 # In language selection module switching off href caching now ensures that cache is not used when routing menu items (3rd party SEF specific problem)

2008-11-15 Alex Kempkens
 # [#12655] Frontend publishing - not working by default settings
 ^ Added default language values for the English language if a clean installation is processed

2008-11-04 Geraint Edwards
 # fixed [#13278] Wrong loadObject implementation for legacy mdatabase class

2008-10-09 Geraint Edwards
 ^ Change db cache to use compressed data stored in mediumblob
 ^ Change default state of Joomfish caching to be off
 ^ Language Module caching now respects joomfish component caching selection

2008-09-29 Geraint Edwards
 ^ Enable category translation filter to respect section selection
 ^ Switch setreftable caching to use JFile/JFolder functions and to disable this if FTP layer is enabled

2008-09-19 Geraint Edwards
 + Add frontpage filter to content items translations
 # Add trap for language config link before languages have been saved
 + Add DB Cache for translations
 # Fix memory reset for translation filters
 ^ Improvement to setRefTables - skipping the processing for joomfish base queries

2008-09-12 Geraint Edwards
 ^ Added new "trash" filter to content and menu items so that we can drop the old type of filter completely

2008-09-11 Ivo Apostolov
 # Adding missing language strings
 # Fix of Swedish flag

==== 09 September 2008 Joom!Fish 2.0 RC public release =====

2008-09-09 Alex Kempkens
 # updated versioning information for release preparations

2008-09-08 Alex Kempkens
 # fixed [#12241] List length
 # fixed [#12657] Translation of "other parameters" of mod_mainmenu

2008-09-08 Ivo Apostolov
 # Commit of Geraint's bug fix in the jfrouter

2008-09-05 Alex Kempkens
 # [#10295] You have an error in your SQL syntax
 ! affects only CE files menu.xml, content.xml was already fixed

2008-09-01 Ivo Apostolov
 # Varios new strings in the language files. Thanks Rued.
 + Adding en-GB.com_joomfish.menu.ini. Thanks Rued.

==== 27 August 2008 Joom!Fish 2.0 RC1 donors and contributors release =====

2008-08-27 Ivo Apostolov
 # Changing language files headers in order to be easier manipulated with the Translation Manager Extension
 ^ Setting the release date of RC1 version - 27 August 2008
 ^ Aligning XML files to contain same data

2008-08-26 Ivo Apostolov
 # Fixing the load of mod_translate without saving. Thanks Geraint
 + Adding pre-defined ordering of mod_jflanguageselection
 ^ Small change on the default mod_jflanguageselection display
 # Small fix of the description of mod_translate
 + Adding language files for the search plugins
 + Adding language files for mod_translate

2008-08-26 Geraint Edwards
 # fixed bad file location in jfrouter plugin config
 # removed setting locale in jfrouter (JLanguage does this for us now)
 + Added check an overwriting site module installation si that we don't get duplicate installed
 + Added default module visibiliy on ALL pages for new module install

2008-08-25 Ivo Apostolov
 ^ Changing the module default display type
 # Changing the revision in version.php
 + Adding new attribute in the installation routine of the system plugins. By adding "order" we prevent incorrect ordering of our plugins by putting them directly on positions -101 and -100.
 # Moving the language file for the JFrouter to the proper folder
 - Removed slugs plugin
 - Removed prePostTranslation plugin
 - Removed ContentLocalization plugin
 # Update of the installation manifest

2008-08-23 Geraint Edwards
 # Ensure content element files are loaded from the correct path by JoomFishManager following move of this file to classes subdirectory

2008-08-22 Geraint Edwards
 # Rationalised treatment of strip slashes when saving translations - html fields are allowed HTML all other are stripped if necessary
 # Fixed minor regression config should not be obtained dynamically in JoomFishManager
 # Remove readmore button in html editor for translations pending more advance treatment of readmore in JF 2.1

2008-18-19 Ivo Apostolov
 # Replacement of mosConfig_livesite with JURI::root() in mod_translate
 # Fixing cPanel donate image handling by adding JURI::root() in the URL

2008-18-19 Ivo Apostolov
 # Fixing minor bug in the orphan view (non closed table)
 ^ Replacing irrelevant texts from the postinstall info
 # Fixing the module language file, now ready for RC. Adding Bulgarian translation.
 + Adding language file for the plugin jfalternative
 + Adding language files for system plugins

2008-08-19 Ivo Apostolov
 ^ Minor GUI changes in the "Manage Translations" and "Statistics" to reflect the overall design of the images.
 + New language string "DETAIL" for the content elements toolbar

2008-08-15 Alex Kempkens
 # completed refactoring of view elements
 # integrated post install information based on /help/en-GB/postinstall.html
 ! translators note. You can translate all files within /help/ to your language. The files will be
 ! automatically used with changing the administrator language in Joomla!
 # fixed statistics mode
 # changed welcome to Joom!Fish information
 # fixed links in help dialogs
 # merged latest changes to branch
 # refactored manage translations dialog

2008-08-15 Geraint Edwards
 # Fix untranslated menus for logged in users - we must not allow any database calls within the translation routine
 + Added method upgrade to installer

2008-08-12 Geraint Edwards
 # Fix mod_jflanguageselection to handle ssl correctly

2008-08-11 Alex Kempkens
 ! merge from trunk to refactoring branch completed
 # fixed several problems with new default view class references
 # refactored translation view with table layout and view reference
 # refactored translation edit
 # refactored translation orphans

2008-08-10 Alex Kempkens
 + new default view class
 # updated elements view to new base view class
 # updated elements view to Joomla! generic view templ files
 # update installer methods and views related
 # updated various language texts
 # updated xml and primary files to new Joomla! 1.5 standards
 # moved JoomFishManager to own classes directory
 # updated frontend module and system plugins

2008-08-09 Alex Kempkens
 # Added help screens for all pages
 # Started refactoring of views to simplify the default view usage
 # fixed footer license link
 # standardized the sub menu navigation
 # split of management and statistic fuctions

2008-08-05 Geraint Edwards
 # Some Joomfish cache files were not being removed on expiry.  Add a garbage handler to the jfdatabase::onAfterRender method to take care of this

2008-08-03 Geraint Edwards
 + Add "Apply" button when editing translations

 2008-07-28 Geraint Edwards
 # Fixed JS copying and deleting of textarea params

 2008-07-23 Geraint Edwards
 # Remove option to delete language from languages overview - we don't have a method and have not defined what this should do.  Should it uninstall the Joomla language files?

 2008-07-23 Geraint Edwards
 # Force reload of content element cache when saving translations see http://www.joomfish.net/forum/viewtopic.php?f=28&t=1567&p=7387#p7387

2008-07-22 Alex Kempkens
 # Fixed broken link to license file

2008-07-21 Geraint Edwards
 # RSS feed from joomfish.net is incorrect and always gives error in the cPanel.
 # Remove broken "Display #10" at the top of various overview lists
 # remove checkboxes on CE installer list
 # Corrected check of plugin ordering - should be the order in the list not the value of the ordering field
 # fixed basic router to deal with pdf and feeds correctly when SEF with extensions is enabled
 # Finished fixing url parameters to allow selection of article in menu translation
 # remove reference to JPATH_COMPONENT_ADMINISTRATOR in JLoader::register - always refer to Joomfish in the path so we can inherit from Joomfish elsewhere

2008-07-18 Geraint Edwards
 # Fixed variety of bugs from Ivo's Beta2Bugs doc - deletion of CE, deletion of orphans, count of transltion for donate link in CPanel, back link in CE installer, irrelevant href link in lang config, date formatting in backend,
 + Added content attribute translation
 + Added translation of URLs for weblinks
 # Fixed problem with untranslated section and category titles on front page
 # Added trap for setreftable cache file being unwritable
 + Added basic router workaround for contact router problem with missing slug data

2008-07-14 Geraint Edwards
 # Fixed problem with translation where 2 fields with the same name were being incorrectly translated (see http://www.joomfish.net/forum/viewtopic.php?f=16&t=1523&p=7108#p7107)
 # Fixed call to onBeforeTranslation that resulted in items not being translated if the plugin returned true

2008-07-11 Geraint Edwards
 # Fixed various problems with 302 redirect and cookies see : http://www.joomfish.net/forum/viewtopic.php?f=28&t=1472&p=6899#p6888
 # Fixed bad call to translateListWithIDs when processing fallbacks - could lead to recursion with badly configured language configuration
 # Added missing handler for getNumRows to JFDatabase
 + Add ignoreifblank to ContentElementTableField to allow modules to ignore "content" field if original is blank
 ^ Replace usage of JPATH_COMPONENT and JPATH_COMPONENT_ADMINISTRATOR for future flexibility

2008-07-10 Geraint Edwards
 - Remove frontend component files from installation since they are not used anywhere

2008-07-03 Geraint Edwards
 # Correct use of ACL for frontend translation
 # Fix stripping of paramaters in translation slashes were getting dropped so textarea params were not translated properly


 # Fixing Czech language flag [#11406] Default country flag for czech language is wrong

2008-06-21 Geraint Edwards
 # Joomfish routine to find site default language was obtaining the current user's admin language!!  This is now fixed.
 # Fix translation preview - note that some editors are still broken e.g. xstandard (poor coding assuptions) and JCE/TinyMCE needed a workaround of a bug

2008-06-20 Geraint Edwards
 # Removed subtable from polls.xml content element file
 # replaced stray mosRedirect from translate controller
 ^ replace JURI::base() with JURI::root() in joomfish footer
 ^ remove redundance variables in joomfish.class.php
 # Add handler for initial config of jfrouter where sefprefix have not been saved
 ^ Improve check on legacy mode in jfdatabase
 # fixed declaration of JFDatabase::loadRowList ($translation field was missing)
 # Remove module caching option from language switching module - its too dangerous
 + Add sef URL caching option to language switching module


2008-06-15 Ivo Apostolov
 # Fixing of copy function in JCE (by Geraint) - forum http://www.joomfish.net/forum/viewtopic.php?f=28&t=1319
 # Applying the fix on the installation (removing comments from SQL files) - forum version by Geraint, http://www.joomfish.net/forum/viewtopic.php?f=28&t=1314
 ^ Resizing two flags for consistency

2008-06-14 Ivo Apostolov
 ^ Moving the no translations available JText to the missing translations plugin
 + Language file for missing_translations plugin
 + Adding the lang files in missing_translations.xml

2008-06-09 Ivo Apostolov
 + Adding Egyptian flag
 ^ Fixing CSS validation issue in the module


==== Joom!Fish 2.0 Beta 2 public release =====

2008-06-03 Geraint Edwards
 # Fixed overwrite global config - it was not in the config.xml file and hence ignored!
 ~ changed language specific global config to include the default text string to allow easy translation

2008-06-02 Ivo Apostolov
 - Removed all languages except English from the core (conference meeting)
 ^ Changed joomfish.xml to reflect the language files removal
 ! Language files moved to the documentation folder

2008-06-01 Geraint Edwards
 # Add models/manage.php to install xml file

2008-06-01 Ivo Apostolov
 # Fix of images wrong URLs in views/translate/tmpl/orphanes.php

2008-06-01 Geraint Edwards
 + Added utility method to JoomfishManager getLanguagesIndexedById
 # fix page navigation on content elements overview

2008-06-01 Ivo Apostolov
 # fixing the translations overview screen (incomplete usage of JText causing incorrect visualisation)
 # additing to the installer status_r.png, status_y.png and status_g.png used in views/translate/tmpl/overview.php

2008-06-01 Ivo Apostolov
 # fixing install file (using cap for translateconfig)
 # hardcoded database prefix in joomfish.sql

2008-05-30 Alex Kempkens
 # ampersand bug; http://www.joomfish.net/forum/posting.php?mode=reply&f=28&t=1181
 # refactored module loading of classes

2008-04-13 Alex Kempkens
 # Notice JoomFish 2.0 - J! 1.5.2, http://www.joomfish.net/forum/viewtopic.php?f=15&t=903

2008-04-12 Alex Kempkens
 # removed some php short tags
 # fixed wrong links in cpanel status (thx for the hint Mark)
 # fixed broken links in help dialogs
 + new information and postinstall page within help view
 + added cpanel icon statistic function
 # fixed sub-menu links

2008-04-11 Alex Kempkens
 # updated help screen layout based on patch from Mark - thx
 # updated versioning and copyright years

2008-02-18 Alex Kempkens
 # fixed wrong text presentation in the cpanel
 + Integrated help dialog
 # fixed News RSS integration

2008-02-18 Alex Kempkens
 + first integration of language manager
 ! Geraint did several other dialogs already ....

2008-01-03 Alex Kempkens
 + Integration of CPanel MVC

2008-12-30 Alex Kempkens
 + Inital setup of files and structures

 === Base of work procress is JoomFish 1.8 release                              ===
