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

// Non menu entry case
//header("Location:../../central.php");

// Entry menu case
//use Glpi\Event;
define('GLPI_ROOT', '../..');
include (GLPI_ROOT . "/inc/includes.php");

if (isset($_SESSION['glpiname'])) {

global $DB;
/*
 $events = new Event();

      $iterator = $DB->request([
         'SELECT' => ['date'],
         'FROM'   => $events->getTable(),
         'WHERE'  => [
					"level" => 3,
					"message" => ['LIKE', '%'.$_SESSION['glpiname'].' inició%'],								
         ],
         'LIMIT'  => 2,
		 'ORDER'    => 'id DESC',
      ]);
	  
*/

  $sesion = new PluginSeguridadSeguridad();	 
			
      $iterator = $DB->request([   
		 'SELECT' => ['date'],      
         'FROM'   => $sesion->getTable(),
         'WHERE'  => [					
					"name" => $_SESSION['glpiname'],	
					"success" => 1,									
         ],
         'LIMIT'  => 2,
		 'ORDER'    => 'id DESC',
      ]);	  
	  
      if (count($iterator)>1) {
         $iterator->next();
	  }

         if ($row = $iterator->next()) {
            //TRANS: %s is the date
			printf(__('Último Acceso %s'), HTML::convDateTime($row["date"]));            
         }

}
