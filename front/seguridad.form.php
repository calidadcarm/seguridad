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

include ('../../../inc/includes.php');
Session::checkLoginUser();

 Html::header(__('seguridad', 'seguridad'), $_SERVER['PHP_SELF'] ,"config", "pluginseguridadseguridad", "seguridad");

$seguridad = new PluginSeguridadSeguridad();

if (isset($_POST["update"])) {
	
	$seguridad->update($_POST);
	Html::redirect($_SERVER['HTTP_REFERER']);
 
 }   


    if (Session::haveRight('plugin_seguridad',READ)) {
		$seguridad->display($_GET);
		Html::footer();
	} else {
			Html::displayRightError();
	}  

