<?php
/*
 * @version $Id: HEADER 15930 2011-10-25 10:47:55Z jmd $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2020-2019 by the CARM Development Team.

 https://github.com/calidadcarm/seguridad
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Javier David Marín Zafrilla
// Purpose of file:
// ----------------------------------------------------------------------

// Class of the defined type
class PluginSeguridadConfig extends CommonDBTM {
   
   public $dohistory=true; // EN LA CABECERA
   static $tags = '[EXAMPLE_ID]';
   static $rightname = "plugin_seguridad";

   // Should return the localized name of the type
   static function getTypeName($nb = 0) {
      return 'Configuración de Seguridad';
   }
   
   static function canView() {

	return (Session::haveRight(self::$rightname, READ));

   }
   
   static function canCreate() {

	return (Session::haveRight("plugin_seguridad_purge", CREATE));

   }   
   
   public function canPurgeItem() {

      return (Session::haveRight("plugin_seguridad_purge", PURGE));
   }   
   
   public function canUpdateItem() {

    	return (Session::haveRight(self::$rightname, READ));
   }   
   
   /**
    * @see CommonGLPI::getMenuName()
   **/
   static function getMenuName() {
      return __('Seguridad');
   }

   static function install(Migration $migration) {
      global $DB;
      
      $table = getTableForItemType(__CLASS__);
      if (!$DB->TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,		  
		  `intentos` int(11) NOT NULL DEFAULT '0',  
		  `time_block` int(11) NOT NULL DEFAULT '0',  
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

         $DB->query($query) or die ("Error adding table $table");
		 
		// echo $query."<br>";
		 
 $query = "INSERT INTO `$table` (`id`,`intentos`,`time_block`) 
	VALUES 	(1,3,300);";
					 // echo $query."<br>";
         $DB->query($query) or die("Error adding Config"); 	

		$tabla='
			  <tr>
				<td colspan="2" align="left">&nbsp;&nbsp;<img style="vertical-align:middle;" alt="" src="'.$_SESSION["glpiroot"].'/plugins/seguridad/img/check.png">&nbsp;
				&nbsp;<strong><FONT color="#3a9b26">'.$table.'</FONT>.</strong>				
				</td>
			  </tr></table>';
				 
		 Session::addMessageAfterRedirect($tabla);					
		 
      }  	  
	  
   } 


   function defineTabs($options = []) {
      $ong = [];
	  
      $this->addDefaultFormTab($ong);
	  $this->addStandardTab('PluginSeguridadSeguridad_User', $ong, $options);
	  $this->addStandardTab('Log', $ong, $options);

      return $ong;
   } 
   

  function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'                 => 'common',
         'name'               => __('Configuración de Seguridad')
      ];


      $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'usehaving'          => true,
         'searchtype'         => 'equals',
		 'datatype' => 'itemlink',		 		 
      ];


		$tab[] = [
		'id' => 2,
		'table' => $this->getTable(),
		'field' => 'intentos',
		'name' => __('Intentos'),
		'datatype' => 'number',
		'massiveaction' => false,
		];


		$tab[] = [
		'id' => 3,
		'table' => $this->getTable(),
		'field' => 'time_block',
		'name' => __('Tiempo de bloqueo'),
		'datatype' => 'number',
		'massiveaction' => false,
		];
  
      return $tab;
   }   
   

   function showForm($ID, $options = []) {
      global $DB, $CFG_GLPI;

      $this->initForm($ID, $options);
      $this->showFormHeader($options);
	  $this->getFromDB($ID);

echo '<tr><td colspan="4" class=""></td></tr>';

      echo "<tr class='tab_bg_1'>";
				echo "<td>";				
				          echo "<div class='fa-label'>
            <i class='fas fa-shield-alt fa-fw'
               title='Inicios de sesion erróneos consecutivos permitidos.'></i>";
			   
			 
Dropdown::showNumber("intentos", array('value' => $this->fields['intentos'],
'min' => 1,
'max' => 10,
'step' => 1));	
			   
            echo "Logins <strong>Fallidos</strong> consecutivos permitidos.</div>";


				echo "<td>";				
				          echo "<div class='fa-label'>
            <i class='fas fa-clock fa-fw'
               title='".__('Tiempo de bloqueo del usuario (En segundos)')."'></i>";


Dropdown::showNumber("time_block", array('value' => $this->fields['time_block'],
'min' => 100,
'max' => 1000,
'step' => 100));			   			   	 

            echo "Tiempo de bloqueo del usuario (<strong>En Segundos</strong>)</div>";
				echo "</td>";						
	echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }





   



}
