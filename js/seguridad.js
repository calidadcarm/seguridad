/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2018 Teclib' and contributors.
 *
 * https://github.com/calidadcarm/seguridad
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the CARM Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 *
 * Original Author of file: Javier David Marín Zafrilla
 *
 */
 
   var _url = CFG_GLPI.root_doc+'/plugins/seguridad/stat.php';
   
   $.ajax({
      url: _url,
      type: 'GET',
      success: function(html) {
		 	
		$("#c_preference ul").append('<li id="time_sesion"><a href="#" title="'+html+'">'+html+'</a></li>');
		
      }
   });


function aceptar_aviso(){
        popbox3();		
}

function popbox3() {
	
	var x=$("#overbox3");
				x.slideToggle("slow");
	
 //   $('#overbox3').toggle();
}


