<?php
/*
 -------------------------------------------------------------------------
 seguridad plugin for GLPI
 Copyright (C) 2020 by the CARM Development Team.

 https://github.com/pluginsGLPI/seguridad
 -------------------------------------------------------------------------

 LICENSE

 This file is part of seguridad.

 seguridad is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 seguridad is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with seguridad. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Javier David Marín Zafrilla
// Purpose of file:
// ----------------------------------------------------------------------

define ('PLUGIN_SEGURIDAD_VERSION', '1.2.0');

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_seguridad() {
   global $PLUGIN_HOOKS,$CFG_GLPI;

   // Params : plugin name - string type - ID - Array of attributes
   // No specific information passed so not needed
   //Plugin::registerClass('PluginSeguridadSeguridad',
   //                      array('classname'              => 'PluginSeguridadSeguridad',
   //                        ));


   $Plugin = new Plugin();
   if ($Plugin->isActivated('seguridad')) {	   

	  // Registro de clases	 
	 	 Plugin::registerClass('PluginSeguridadProfile', array('addtabon' => array('Profile'))); // Perfil  //[jmz18g] [inicio] sesion seguridad deshabilitado
 				
   }

   Plugin::registerClass('PluginSeguridadConfig');
//[jmz18g] [inicio] sesion seguridad deshabilitado
  Plugin::registerClass('PluginSeguridadSeguridad',
                         ['notificationtemplates_types' => true, 
						  'addtabon' => array('User'),                         
                          'link_types' => true]);
					  
		
		Plugin::registerClass('PluginSeguridadStatistic', 
		array('addtabon' => array('PluginSeguridadSeguridad'))); // Estadisticas de Sesiones						  
//[jmz18g] [final] sesion seguridad deshabilitado	

   // Display a menu entry ?

     $PLUGIN_HOOKS['add_javascript']['seguridad'] = array('js/seguridad.js');
     $PLUGIN_HOOKS['add_css']['seguridad']        = array('css/seguridad.scss'); 

		if (Session::haveRight("plugin_seguridad",READ)) {
 
//[jmz18g] [inicio] sesion seguridad deshabilitado
      $PLUGIN_HOOKS['menu_toadd']['seguridad'] = ['config' => 'PluginSeguridadSeguridad',
												/*'assets' => 'PluginSeguridadSeguridad',
                                                'admin' => 'PluginSeguridadSeguridad',
                                                'helpdesk' => 'PluginSeguridadSeguridad',          
                                                'management' => 'PluginSeguridadSeguridad',                                                
                                                'plugins' => 'PluginSeguridadSeguridad',
                                                'tools'   => 'PluginSeguridadSeguridad'*/ ];	

//[jmz18g] [final] sesion seguridad deshabilitado												
												
      $PLUGIN_HOOKS["helpdesk_menu_entry"]['seguridad'] = true;
   }

//[jmz18g] [inicio] sesion seguridad deshabilitado

   // Config page
   if (Session::haveRight('config', UPDATE)) {
      $PLUGIN_HOOKS['config_page']['seguridad'] = 'front/config.form.php?id=1';
   }
   
//[jmz18g] [final] sesion seguridad deshabilitado
   
     // $PLUGIN_HOOKS['item_add']['seguridad']  = array('Event' => 'plugin_seguridad_item_add');

   // Init session
   $PLUGIN_HOOKS['init_session']['seguridad'] = 'plugin_init_session_seguridad'; //[jmz18g] [inicio] sesion seguridad deshabilitado
   // Change profile
   $PLUGIN_HOOKS['change_profile']['seguridad'] = 'plugin_change_profile_seguridad';
   // Change entity
   //$PLUGIN_HOOKS['change_entity']['seguridad'] = 'plugin_change_entity_seguridad';


   $PLUGIN_HOOKS['post_init']['seguridad'] = 'plugin_seguridad_postinit';   

   // CSRF compliance : All actions must be done via POST and forms closed by Html::closeForm();
    $PLUGIN_HOOKS['csrf_compliant']['seguridad'] = true;
	
	if (!isset($_SESSION['MENSAJE_SEGURIDAD'])) { 

    $PLUGIN_HOOKS['display_central']['seguridad'] = "plugin_seguridad_display_central";
   
	}

   $PLUGIN_HOOKS['display_login']['seguridad'] = "plugin_seguridad_display_login";
   $PLUGIN_HOOKS['infocom']['seguridad'] = "plugin_seguridad_infocom_hook";
   


}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_seguridad() {
   return [
      'name'           => 'Plugin Seguridad',
      'version'        => PLUGIN_SEGURIDAD_VERSION,
      'author'         => '<a href="http://www.carm.es">CARM</a>',
      'license'        => 'GPLv2+',
      'homepage'       => 'http://www.carm.es',
      'requirements'   => [
         'glpi' => [
            'min' => '9.5',
            'dev' => true
         ]
      ]
   ];
}


/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_seguridad_check_prerequisites() {

   $version = rtrim(GLPI_VERSION, '-dev');
   if (version_compare($version, '9.5', 'lt')) {
      echo "This plugin requires GLPI 9.5";
      return false;
   }

   return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_seguridad_check_config($verbose = false) {
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      echo __('Installed / not configured', 'seguridad');
   }
   return false;
}
