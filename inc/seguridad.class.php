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
class PluginSeguridadSeguridad extends CommonDBTM {
   
   public $dohistory=true; // EN LA CABECERA
   static $tags = '[EXAMPLE_ID]';
   static $rightname = "plugin_seguridad";

   // Should return the localized name of the type
   static function getTypeName($nb = 0) {
      return 'Seguridad';
   }
   
   static function canView() {
	
		if ((isset($_REQUEST["_itemtype"])) and ($_REQUEST["_itemtype"]=="User")) {		
			return (Session::haveRight(self::$rightname, UPDATE));
		}  else {			
			return (Session::haveRight(self::$rightname, READ));
		}	

   }
   
   static function canCreate() {

	return false;

   }   
   
   public function canPurgeItem() {

      return false;
   }   
   
   public function canUpdateItem() {
	
		if ($_REQUEST["_itemtype"]=="User") {
			return (Session::haveRight(self::$rightname, UPDATE));
		}  else {
			return false;
		}			
   }   
   
   /**
    * @see CommonGLPI::getMenuName()
   **/
   static function getMenuName() {
      return __('Seguridad');
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (($item->getType()=='User') and (Session::haveRight(self::$rightname,UPDATE))) {
          //  return PluginSeguridadSeguridad::getTypeName(2);
		  return "Bloqueo Temporal";
      }
      return '';
   }


   static function DisplayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='User') {
         $ID = $item->getID();
         $user = new self();

         $user->showSession($ID);
      }
      return true;
   }

   static function install(Migration $migration) {
      global $DB;
      
      $table = getTableForItemType(__CLASS__);
      if (!$DB->TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (                  				  
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `users_id` int(11) DEFAULT '0',
				  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,				 				  
				  `ip` VARCHAR(50) COLLATE utf8_unicode_ci DEFAULT NULL,
				  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  `date_creation` datetime DEFAULT NULL,				  
				  `success` tinyint(1) DEFAULT 0,
				  `locked` tinyint(1) DEFAULT 0,
				  `unlocked` tinyint(1) DEFAULT 0,
				  PRIMARY KEY (`id`),				  
    			  KEY `users_id` (`users_id`),
				  KEY `name` (`name`),
				  KEY `ip` (`ip`),
				  KEY `date` (`date`),
				  KEY `date_creation` (`date_creation`),
				  KEY `success` (`success`),	
				  KEY `locked` (`locked`),
				  KEY `unlocked` (`unlocked`)				  
               ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query) or die ("Error adding table $table");
		 
		 
      			Session::addMessageAfterRedirect(__('			
						<table>
			  <tr>
				<td align="left"><img style="vertical-align:middle;" alt="" src="'.$_SESSION["glpiroot"].'/plugins/seguridad/img/install.png">&nbsp;&nbsp;</td>
				<td class="center">&nbsp;
				<FONT color="#4f35a2"><strong>Instalación</strong> realizada con <strong></font><font color="green">Éxito</font></strong> <br>- - - - - - - - - - - - - - - - - - <br>
				<font color="green"><strong>Plugin Seguridad</strong></font><FONT color="#4f35a2"> versión </font><strong><font color="green">'. PLUGIN_SEGURIDAD_VERSION .'</font></strong>		
				</td>
				
			  </tr>
			</table><FONT color="#4f35a2"><br>Instalando Tablas.....</FONT><table>','plugin_seguridad'),true, INFO);			 
         		 
		$tabla='
			  <tr>
				<td colspan="2" align="left">&nbsp;&nbsp;<img style="vertical-align:middle;" alt="" src="'.$_SESSION["glpiroot"].'/plugins/seguridad/img/check.png">&nbsp;
				&nbsp;<strong><FONT color="#3a9b26">'.$table.'</FONT>.</strong>				
				</td>
			  </tr>';
				 
		 Session::addMessageAfterRedirect($tabla);			 
		 
      }
   } 


   /**
    * @see CommonGLPI::getAdditionalMenuLinks()
   **/
 /*  static function getAdditionalMenuLinks() {
      global $CFG_GLPI;
      $links = [];

      $links['config'] = '/plugins/seguridad/front/config.form.php';
      $links["<img  src='".$CFG_GLPI["root_doc"]."/pics/menu_showall.png' title='".__s('Show all')."' alt='".__s('Show all')."'>"] = '/plugins/seguridad/front/seguridad.php?as_map=0&criteria[0][link]=AND&criteria[0][field]=view&criteria[0][searchtype]=contains&criteria[0][value]=&search=Buscar&itemtype=PluginSeguridadSeguridad&start=0';
    /*  $links[__s('Test link', 'seguridad')] = '/plugins/seguridad/index.php';*/

    /*  return $links;
   }*/

   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);	  
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   function showForm($ID, $options = []) {
      global $DB, $CFG_GLPI;

      $this->initForm($ID, $options);
      $this->showFormHeader($options);
	  $this->getFromDB($ID);
	  
	  if (!empty($this->fields['users_id'])){
	  
	  $usuario = new User();
	  $usuario -> getFromDB($this->fields['users_id']);
	  
	  $realname=$usuario->fields['firstname']." ".$usuario->fields['realname'];
	  $is_active=$usuario->fields['is_active'];
	  $phone1=$usuario->fields['phone'];
	  $phone2=$usuario->fields['phone2'];
	  $mobile=$usuario->fields['mobile'];
	  $last_login=$usuario->fields['last_login'];
	  
				if ((!empty($phone1)) and (!empty($phone2))){
				$phone = "Contiene 2 teléfonos: ".$phone1." y ".$phone2.". ";	
				} else {
				if (!empty($phone1)){
				$phone = $phone1.". ";
				} else {
				if (!empty($phone2)){
				$phone = $phone2.". ";
				} else {
				$phone = "";	
				} }	}			
				if (!empty($mobile)){ $phone .= " Móvil: ".$mobile;  }	  
	  
	  } else {
	
	  $realname="Este usuario no Existe";
	  $is_active=0;
	  $phone="";	  
	  $last_login="";	
		  
	  }	  

echo '<tr><td colspan="4" class=""></td></tr>';
echo '<th colspan="4" class="">Datos de Usuario (ID '.$this->fields['users_id'].')</th>';

      echo "<tr class='tab_bg_1'>";
				echo "<td style='width:100px'>Login</td><td>";				
				          echo "<div class='fa-label'>
            <i class='fas fa-user-secret fa-fw'
               title='Login'></i>";
			   
			   Html::autocompletionTextField($this,"name",array('size' => "124"));
			   
            echo "</div>";
				echo "</td>";

				echo "<td style='width:100px'>".__('Login')."</td><td>";				
				          echo "<div class='fa-label'>
            <i class='fas fa-user fa-fw'
               title='".__('Login')."'></i>
			   <input type='text' id='name' name='name' required
        size='90' value='".$realname."' >";

            echo "</div>";
				echo "</td>";						
	echo "</tr>";
	
					echo "<tr class='tab_bg_1'>";
				echo "<td>".__('Active')."</td>";
				echo "<td>";			
         echo "<div class='fa-label'>
            <i class='fas fa-lock fa-fw' title='".__('Active')." - ".Dropdown::getYesNo($is_active)."'></i>";
         $rand = mt_rand();
         echo "<span class='switch pager_controls'>
            <label for='is_activeswitch$rand' title='".__('Active')." - ".Dropdown::getYesNo($is_active)."'>
               <input type='hidden' name='is_active' value='0'>
                              <input type='checkbox' id='is_activeswitch$rand' name='is_active' value='1'".
                     ($is_active
                        ? "checked='checked'"
                        : "")."
               >
               <span class='lever'></span>
            </label>
         </span>";
         echo "</div>";					
				
				
				echo "</td>";
					
				
				echo "<td style='width:100px'>".__('Phone')."</td><td>";				
				          echo "<div class='fa-label'>
            <i class='fas fa-phone fa-fw'
               title='".__('Phone')."'></i>
			   <input type='text' id='name' name='name' required
        size='90' value='".$phone."' >";

            echo "</div>";
				echo "</td>";
				
				echo "</tr>";
				
      echo "<tr class='tab_bg_1'>";
				echo "<td style='width:100px'>Fecha Creación</td><td>";				
				          echo "<div class='fa-label'>
            <i class='fas fa-folder-plus fa-fw'
               title='Fecha Creación'></i>";
			  
 			   if (!empty($this->fields['users_id'])){
			   Html::autocompletionTextField($usuario,"date_creation",array('size' => "124"));
			   } else {
echo  "<input type='text' id='name' name='name' required value='' >";
			   }				   
			   
            echo "</div>";
				echo "</td>";

				echo "<td style='width:100px'>".__('Última sesión')."</td><td>";				
				          echo "<div class='fa-label'>
            <i class='fas fa-sign-in-alt fa-fw'
               title='".__('Última sesión')."'></i>
			   <input type='text' id='last_login' name='last_login' required
        size='90' value='".$last_login."' >";

            echo "</div>";
				echo "</td>";						
	echo "</tr>";				
				
				
echo '<th colspan="4" class="">Datos de la Sesión</th>';	
echo '<tr><td colspan="4" class=""></td></tr>';			
				
      echo "<tr class='tab_bg_1'>";
				echo "<td style='width:100px'>IP</td><td>";				
				          echo "<div class='fa-label'>
            <i class='fas fa-desktop fa-fw'
               title='".__('IP')."'></i>";
			   
			   Html::autocompletionTextField($this,"ip",array('size' => "124"));
			   
            echo "</div>";
				echo "</td>";

				echo "<td style='width:100px'>".__('Date')."</td><td>";				
				          echo "<div class='fa-label'>
            <i class='fas fa-calendar-alt fa-fw'
               title='".__('Date')."'></i>
			   <input type='text' id='date' name='date' required
        size='90' value='".$this->fields['date']."' >";

            echo "</div>";
				echo "</td>";						
	echo "</tr>";	

					echo "<tr class='tab_bg_1'>";
				echo "<td>".__('Sesion Exitosa')."</td>";
				echo "<td>";
				
         echo "<div class='fa-label'>
            <i class='fas fa-thumbs-up fa-fw' title='".__('Sesion Exitosa')." - ".Dropdown::getYesNo($this->fields['success'])."'></i>";
         $rand = mt_rand();
         echo "<span class='switch pager_controls'>
            <label for='successswitch$rand' title='".__('Sesion Exitosa')." - ".Dropdown::getYesNo($this->fields['success'])."'>
               <input type='hidden' name='success' value='0'>
                              <input type='checkbox' id='successswitch$rand' name='success' value='1'".
                     ($this->fields['success']
                        ? "checked='checked'"
                        : "")."
               >
               <span class='lever'></span>
            </label>
         </span>";
         echo "</div>";					
								
$query="SELECT name as 'Elemento', count(name) as 'Repeticiones',  DATE_FORMAT(date, '%Y-%m-%d') as fecha, sum(locked) as 'locked' FROM glpi_plugin_seguridad_seguridads where success=0 and name = '".$this->fields['name']."' and DATE_FORMAT(date, '%Y-%m-%d') = '".explode(" ",$this->fields['date'])[0]."'";

//echo $query;

         $result = $DB->query($query);

         if ($DB->numrows($result) > 0) {
            
            while ($data = $DB->fetch_assoc($result)) {

				$sesiones_fallidas=$data["Repeticiones"];
				$locked=(isset ($data["locked"])) ? $data["locked"] : 0;

			}
		 }
		 
		 if ($sesiones_fallidas>0) { $color="sesion_ko"; } else { $color="sesion_ok";  }
		 if ($locked>0) { $color_locked="sesion_ko"; } else { $color_locked="sesion_ok";  }

				          echo "</td><td></td><td colspan='2' style='width:100px'><div class='fa-label'>
            <i class='fas fa-exclamation-triangle fa-fw'
               title='Sesiones Fallidas'></i>";
			   
		 echo " <strong><span class='$color'>".$sesiones_fallidas."</span> Sesiones fallidas durante en este día.</strong>";				   
			   
            echo "</div>
			
			<div class='fa-label'>
            <i class='fas fa-user-lock fa-fw'
               title='Sesiones Fallidas'></i>";
			   
	     echo " <strong><span class='$color_locked'>".$locked."</span> Veces bloqueado su usuario en este día.</strong>";				   
			   
            echo "</div>
			
			</td></tr>";

				

      $this->showFormButtons($options);

      return true;
   }

   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'                 => 'common',
         'name'               => __('Sesiones detectadas')
      ];


      $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'usehaving'          => true,
         'searchtype'         => 'equals',		 
		 'datatype'           => 'number',
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Name'),	 		 
      ];


		$tab[] = [
			'id' => '3',
			'table' => 'glpi_users',
			'field' => 'name',
			'linkfield' => 'users_id',
			'name' => __('Login'),
			'datatype' => 'dropdown',
			'itemlink_type' => $this->getType(),
			'massiveaction' => false,
			'injectable' => false,
			'checktype' => 'text',
			'displaytype' => 'dropdown',
		];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'date',
         'name'               => __('Fecha Sesión'),
         'datatype'           => 'datetime',
         'massiveaction'      => false
      ];


      $tab[] = [
         'id'                 => '5',
         'table'              => $this->getTable(),
         'field'              => 'success',
         'name'               => __('Exito'),
		 'datatype'           => 'bool'
      ];

		$tab[] = [
			'id' => '6',
			'table' => 'glpi_users',
			'field' => 'is_active',
			'linkfield' => 'users_id',
			'name' => __('Active'),
			'datatype' => 'bool',
			'massiveaction' => false,
			'injectable' => false,
			'checktype' => 'text',
			'displaytype' => 'dropdown',
		];
		
      $tab[] = [
         'id'                 => '7',
         'table'              => $this->getTable(),
         'field'              => 'locked',
         'name'               => __('Sesión Bloqueada'),
		 'datatype'           => 'bool'
      ];		
      
	  $tab[] = [
         'id'                 => '8',
         'table'              => $this->getTable(),
         'field'              => 'unlocked',
         'name'               => __('Sesión Desbloqueada'),
		 'datatype'           => 'bool'
      ];

      return $tab;
   }



   static function getSpecificValueToDisplay($field, $values, array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'serial' :
            return "S/N: ".$values[$field];
      }
      return '';
   }



  /**
    * Get an history entry message
    *
    * @param $data Array from glpi_logs table
    *
    * @since GLPI version 0.84
    *
    * @return string
   **/
   static function getHistoryEntry($data) {
var_dump($data);
EXIT();
      switch ($data['linked_action'] - Log::HISTORY_PLUGIN) {
         case 0:
            return __('History from plugin seguridad', 'seguridad');
      }

      return '';
   }



static function showFor() {
global $DB, $CFG_GLPI;									  

Session::haveRight("plugin_seguridad", UNLOCK);

	  $sesion = new PluginSeguridadSeguridad();
	  $sesion->getFromDB($_GET["id"]);
	  
	  if (!empty($sesion->fields['users_id'])){
	  	  $usuario = new User();
	  $usuario -> getFromDB($sesion->fields['users_id']);
	  
 echo '<table class="tab_cadre_fixehov" width="100%"><th colspan="4" class=""><h3>Días con más de 1 Sesión errónea para el usuario: <font color="blue">'.$usuario->fields['firstname'].' '.$usuario->fields['realname'].'</font> - '.$usuario->fields['name'].' ('.$usuario->fields['id'].')</h3></th></table>';
	 $where=" where consulta.num > 1";
	 } else {
		

 echo '<table class="tab_cadre_fixehov" width="100%"><th colspan="4" class=""><h2><font color="red">¡¡Atención!! Este usuario no existe en GLPI.</font></h2>
	   <h3>Todas las Sesiones de este usuario: <font color="blue">'.$sesion->fields['name'].'</font></h3></th></table>';	
	
	$where="";
		 
	 }	 
 echo '<table class="tab_cadre_fixehov" width="100%">';	
echo '<tbody>
<tr>
<th class="">Sesiones en Total</th>
<th class="">Satisfactorias</th>
<th class="">Error</th>
<th class="">Bloqueos de usuario</th>
<th class="">Fecha</th>
</tr>';

$query="select consulta.* ,contador.total 
from 
( SELECT name as 'user', count(name) as 'num', sum(locked) as 'locked', DATE_FORMAT(date, '%d-%m-%Y') as date 
FROM glpi_plugin_seguridad_seguridads where success=0 and name = '".$sesion->fields['name']."' group by DATE_FORMAT(date, '%Y-%m-%d') ) as consulta 
left join 
(SELECT name as 'user', count(name) as 'total',  DATE_FORMAT(date, '%d-%m-%Y') as date 
FROM glpi_plugin_seguridad_seguridads where name = '".$sesion->fields['name']."'
group by DATE_FORMAT(date, '%Y-%m-%d')) as contador on consulta.user = contador.user and consulta.date=contador.date
".$where;

//echo $query;

         $result = $DB->query($query);

         if ($DB->numrows($result) > 0) {
            
            while ($data = $DB->fetch_assoc($result)) {
 
 if (empty($data["user"])){
	 
 $url=$CFG_GLPI["root_doc"]."/plugins/seguridad/front/seguridad.php?as_map=0&criteria%5B0%5D%5Blink%5D=AND&criteria%5B0%5D%5Bfield%5D=3&criteria%5B0%5D%5Bsearchtype%5D=equals&criteria%5B0%5D%5Bvalue%5D=0&criteria%5B1%5D%5Blink%5D=AND&criteria%5B1%5D%5Bfield%5D=4&criteria%5B1%5D%5Bsearchtype%5D=contains&criteria%5B1%5D%5Bvalue%5D=".$data["date"];
	 
 } else {
	 	 
 $url=$CFG_GLPI["root_doc"]."/plugins/seguridad/front/seguridad.php?as_map=0&criteria%5B0%5D%5Blink%5D=AND&criteria%5B0%5D%5Bfield%5D=2&criteria%5B0%5D%5Bsearchtype%5D=equals&criteria%5B0%5D%5Bvalue%5D=".$data["user"]."&criteria%5B1%5D%5Blink%5D=AND&criteria%5B1%5D%5Bfield%5D=4&criteria%5B1%5D%5Bsearchtype%5D=contains&criteria%5B1%5D%5Bvalue%5D=".$data["date"];
 
 } 
 
 		 if ($data["num"]>0) { $color="red"; } else { $color="green";  }
		 if ($data["locked"]>0) { $color_locked="red"; } else { $color_locked="green";  }
 
 echo "<tr>
<td align='center'><font color='green'><strong>".$data["total"]."</strong></font></td>
<td align='center'><font color='green'><strong>".($data["total"]-$data["num"])."</font></strong></td>
<td align='center'><font color='$color'><strong>".$data["num"]."</strong></font></td>
<td align='center'><font color='$color_locked'><strong>".$data["locked"]."</strong></font></td>
<td align='center'><a href='".$url."'>".$data["date"]."</a></td>
</tr>";
 
         } } else {
			 
echo "<tr>
<td class='center'>-----</td>
<td align='center'>0</td>
<td align='center'>0</td>
<td align='center'>0</td>
<td align='center'>-----</td>
</tr>";

		 }	 

echo '<tr>
<th class="">Estadística</th>
<th class="">Usuario</th>
<th class="">Número de sesiones</th>
<th class="">Bloqueos de usuario</th>
<th class="">Fecha</th>
</tr>
</tbody></table>';
 

				
}
   
   function showSession($ID, $options = []) {
	     
      global $DB, $CFG_GLPI;

      $result = $DB->request([
         'FROM'   => $this->getTable(),
         'WHERE'  => [
            "users_id" => $ID,
         ],
         'ORDER'  => [            
            'id DESC'
         ],
		 'LIMIT'  => 1
      ])->next();

		if ($result) {

	  $config = new PluginSeguridadConfig();
	  
	    $config->getFromDB(1);

      $this->initForm($result['id'], $options);
      $this->showFormHeader($options);
	  $this->getFromDB($result['id']);

if ($this->fields['unlocked']==1){
if (date_result($this->fields['date'])===false) {
$icon="unlock";
$locked=0;
$message=" Su tiempo de bloqueo ya ha <font color='green'><strong>expirado</strong></font>.";	
$time=" Bloqueo temporal <font color='green'><strong>EXPIRADO</strong></font>: <font color='green'><strong>".date_result($this->fields['date'],1)."</strong></font>.";
} else {	
$icon="lock";	
$locked=1;
$message="  Usuario <font color='red'><strong>Bloqueado</strong></font> Temporalmente.";	
$time=" Bloqueo temporal <font color='red'><strong>ACTIVADO</strong></font>: Fecha Vencimiento: <font color='red'><strong>".date_result($this->fields['date'],1)."</strong></font>.";
} } else {
$icon="unlock";
$locked=0;
$message=" Usuario <font color='green'><strong>NO bloqueado</strong></font>.";			
$time=" Bloqueo temporal definido en <font color='green'><strong>".$config->fields['time_block']." SEGUNDOS</strong></font>.";			
}	

echo '<tr><td colspan="4" class=""></td></tr>';

      echo "<tr class='tab_bg_1'>";
				echo "<td>";				
	   			
         echo "<div class='fa-label'>
            <i class='fas fa-$icon fa-fw' title='".strip_tags($message)." - ".Dropdown::getYesNo($locked)."'></i>";
         $rand = mt_rand();
         echo "<span class='switch pager_controls'>
            <label for='unlockedswitch$rand' title='".strip_tags($message)." - ".Dropdown::getYesNo($locked)."'>
               <input type='hidden' name='unlocked' value='0'>
                              <input type='checkbox' id='unlockedswitch$rand' name='unlocked' value='1'".
                     ($locked
                        ? "checked='checked'"
                        : "").">
               <span class='lever'></span>
            </label>
         </span>";
         echo $message." </div></td><td>";				 
			        						
			echo "<div class='fa-label'>
            <i class='fas fa-clock fa-fw'
               title='".strip_tags($time)."'></i>";
            echo $time."</div>";
				echo "</td>";						
	 echo "</tr>";

	$this->showFormButtons($options);

   echo Html::scriptBlock("

	$(document).ready(function() {
		var locked = '".$locked."';
		var now = new Date();
		var end = new Date('".date_result($this->fields['date'],0)."');
		var distance = end - now;
		if (locked == 0) {
			$('.submit').hide('fast');	
		}	
		
	function showRemaining() {
		
		var now = new Date();
        var distance = end - now;		
		
		if (distance > 0) {
			  
			  $('.submit').show('fast');
			  		  
		} else {
			  
			  $('.submit').hide('fast');
			  clearInterval(timer);
			  setTimeout('document.location.reload()',4000);
		}	

	}
	
	if (distance > 0) {
	timer = setInterval(showRemaining, 1000);
	}
	
	});
	");


		} else {
		
         echo "<div align='center'><br><br><i class='fas fa-info-circle fa-4x' style='color:blue'></i><br><br>";
         echo "<b>" . __("Este usuario no tiene registros de sesión.<br><br>", 'seguridad') . "</b></div>";
		
		}	 

      return true;
   }   

   function message_catalogo() {
	
	if (!isset($_SESSION['MENSAJE_SEGURIDAD'])) {  plugin_seguridad_display_central();  }

   }
   
   
   function session_error() {

	plugin_init_session_seguridad();

   }


}
