<?php
/**
* This file is part of Ozio Gallery 3.
*
* Ozio Gallery 3 is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 2 of the License, or
* (at your option) any later version.
*
* Foobar is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
*
* @copyright Copyright (C) 2010 Open Source Solutions S.L.U. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see RT-LICENSE.php
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

class Com_OzioGallery3InstallerScript
{
	protected $component_name;
	protected $extension_name;

	function install($parent)
	{

		//Inzio attivazione Plugin
		$manifest 	= $parent->get("manifest");
		$parent 	= $parent->getParent();
		$source 	= $parent->getPath("source");
		$installer 	= new JInstaller();

		foreach($manifest->plugins->plugin as $plugin)
		{
			$attributes = $plugin->attributes();
			$plg = $source . DS . $attributes['folder'].DS.$attributes['plugin'];
			$installer->install($plg);
		}
		$db = JFactory::getDbo();
		$tableExtensions = $db->nameQuote("#__extensions");
		$columnEnabled   = $db->nameQuote("enabled");
		$columnElement   = $db->nameQuote("element");
		$columnType      = $db->nameQuote("type");

		// Attiva plugin
		$db->setQuery(
		"UPDATE  $tableExtensions  SET $columnEnabled=1  WHERE $columnElement='ozio'   AND  $columnType='plugin'"
		);
		$db->query();

		//Inzio creazione cartelle
		$folder[0][0]	=	'images' . DS . 'oziogallery3' . DS ;
		$folder[0][1]	= 	JPATH_ROOT . DS .  $folder[0][0];
		$folder[0][0]	=	'images' . DS . 'oziodownload' . DS ;
		$folder[0][2]	= 	JPATH_ROOT . DS .  $folder[0][0];
		$file 		= "index.html";
		$file2 		= "info.png";
		$source 	= 	JPATH_ROOT . DS . 'components' . DS . 'com_oziogallery3';
		$source2 	= 	JPATH_ROOT . DS . 'components' . DS . 'com_oziogallery3' . DS . 'assets';
		$dest 		=   JPATH_ROOT . DS . 'images' . DS . 'oziogallery3';
		$dest2 		=   JPATH_ROOT . DS . 'images' . DS . 'oziodownload';

		$message = '';
		$error	 = array();
		foreach ($folder as $key => $value)
		{
			if (!JFolder::exists( $value[1]))
			{
				if (JFolder::create( $value[1], 0755 ) && JFolder::create( $value[2], 0755 ) && @copy($source. DS .$file,$dest. DS .$file) && @copy($source. DS .$file,$dest2. DS .$file) && @copy($source2. DS .$file2,$dest. DS .$file2))
				{

					$message .= '<p><b><span style="color:#009933">Folder</span> ' . $value[0]
					.' <span style="color:#009933">created!</span></b></p>';
					$error[] = 0;
				}
				else
				{
					$message .= '<p><b><span style="color:#CC0033">Folder</span> ' . $value[0]
					.' <span style="color:#CC0033">creation failed!</span></b> Please create it manually.</p>';
					$error[] = 1;
				}
			}
			else//Folder exist
			{
				$message .= '<p><b><span style="color:#009933">Folder</span> ' . $value[0]
				.' <span style="color:#009933">exists!</span></b></p>';
				$error[] = 0;
			}
		}

		$this->message();
	}

	function uninstall($parent)
	{

		echo '<p>The component Ozio Gallery 3 Component for Joomla 1.6/1.7/2.5! has been uninstalled successfully.</p>';
	}


	function update($parent)
	{
		$manifest = $parent->get("manifest");
		$parent = $parent->getParent();
		$source = $parent->getPath("source");
		$installer = new JInstaller();

		foreach($manifest->plugins->plugin as $plugin)
		{
			$attributes = $plugin->attributes();
			$plg = $source . "/" . $attributes['folder'] . "/" . $attributes['plugin'];
			$installer->install($plg);
		}

		$db = JFactory::getDBO();

		// Fixes a Joomla bug, wich adds a second repository rather than overwrite the first one if they are different
		$query = "DELETE FROM `#__update_sites` WHERE `name` = '" . $this->extension_name . " update site';";
		$db->setQuery($query);
		$db->query();

		// Clear updates cache related to this extension
		$query = "DELETE FROM `#__updates` WHERE `name` = '" . $this->extension_name . "';";
		$db->setQuery($query);
		$db->query();

		// Shows the installation/upgrade message
		$this->message();
	}


	function preflight($type, $parent)
	{
		$this->component_name = $parent->get("element");
		$this->extension_name = substr($this->component_name, 4);
	}

	function postflight($type, $parent)
	{
	}

	function message()
	{
		echo '<p>Congratulations! Ozio Gallery 3 Component for Joomla 1.6/1.7/2.5! has been installed successfully</p>';
		echo "<p><img src=\"" . JURI::base(true) . "/components/com_oziogallery3/assets/images/responsive.jpg" . "\"></p>";
		echo "<p>Take a look to 'Fuerte': our new, responsive and adaptive skin." . "</p>";

		require_once JPATH_SITE . "/components/com_oziogallery3/oziogallery.inc";
		if (!$GLOBALS["oziogallery3"]["registered"])
		{
			echo "<p>" .
			'
			<div style="float:left;margin-right:16px;margin-left:10px;">
			<a href="http://www.opensourcesolutions.es/index.php?option=com_content&view=article&id=25%3Aozio-gallery" target="_blank">
			<img src="' . JURI::base(true) . '/components/com_oziogallery3/assets/images/buy_now.jpg" border="0" alt="Buy now">
			</a>
			</div>
			<p><strong>This is a non-commercial version of Ozio Gallery. Remove the signature [Powered by Ozio Gallery] below each gallery, by purchasing a paid version. There aren`t other limitations in functionality, but by purchasing a license code you help us continue development and support.</strong></p>
			' . "</p>";
		}
	}

}
