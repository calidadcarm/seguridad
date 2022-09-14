<?php
/*
 -------------------------------------------------------------------------
 seguridad plugin for GLPI
 Copyright (C) 2001-2017 by the seguridad Development Team.

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


include_once (GLPI_ROOT."/plugins/seguridad/inc/profile.class.php");
use Glpi\Event;

// See also PluginSeguridadSeguridad::getSpecificValueToDisplay()
function plugin_seguridad_giveItem($type, $ID, $data, $num) {
   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      case "glpi_plugin_seguridad_seguridads.name" :
         $out = "<a href='".Toolbox::getItemTypeFormURL('PluginSeguridadSeguridad')."?id=".$data['id']."'>";
         $out .= $data[$num][0]['name'];
         if ($_SESSION["glpiis_ids_visible"] || empty($data[$num][0]['name'])) {
            $out .= " (".$data["id"].")";
         }
         $out .= "</a>";
         return $out;
   }
   return "";
}


function plugin_seguridad_displayConfigItem($type, $ID, $data, $num) {
   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   // seguridad of specific style options
   // No need of the function if you do not have specific cases
   switch ($table.'.'.$field) {
      case "glpi_plugin_seguridad_seguridads.name" :
         return " style=\"background-color:#DDDDDD;\" ";
   }
   return "";
}


/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_seguridad_install() {
   global $DB;

   $migration = new Migration(PLUGIN_SEGURIDAD_VERSION);
   
   if (!file_exists(GLPI_PLUGIN_DOC_DIR."/seguridad")) {
      mkdir(GLPI_PLUGIN_DOC_DIR."/seguridad");
   }
   	
   $migration = new Migration(PLUGIN_SEGURIDAD_VERSION);   
   PluginSeguridadSeguridad::install($migration);
   PluginSeguridadConfig::install($migration);  
   PluginSeguridadSeguridad_User::install($migration);     
   
   $query = "SELECT * 
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA in ('".$DB->dbdefault."') and table_name LIKE 'glpi_plugin_seguridad_%' and TABLE_TYPE = 'BASE TABLE'";

$result = $DB->query($query);
$rows=$DB->numrows($result);

			 $tabla= '<table><tr>
				<td class="center" colspan="2">- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -<br>
				<strong>Tablas instaladas</strong>: <strong><FONT color="#3a9b26">'.$rows.'</FONT></strong><br>
				- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -<br>
				</td>
			  </tr></table>';
   
			  $DB->query("DROP TRIGGER IF EXISTS after_events_insert;");
   
			  $query =  "
			CREATE TRIGGER after_events_insert
			AFTER INSERT ON glpi_events
			FOR EACH ROW

			BEGIN

			-- variable declarations
			DECLARE  v_name varchar(250);
			DECLARE  v_success boolean;
			DECLARE  v_ip varchar(250);
			DECLARE  v_users_id int;
			DECLARE  v_excluded int;

			  IF (NEW.level = 3 and NEW.service = 'login') 
				THEN

					IF (SUBSTRING_INDEX(NEW.message,' ',1)='Fallo') 
					THEN
								
						SET v_success = 0;
						SET v_name = SUBSTRING_INDEX(SUBSTRING_INDEX(NEW.message,' ',-5),' ',1);                         
									
					ELSE
						SET v_success = 1;
						SET v_name = SUBSTRING_INDEX(NEW.message,' ',1);
								
					END IF;   
						
						SET v_excluded = 0;
						SET v_users_id = (SELECT id FROM glpi_users where name = v_name);
						SET v_excluded = (SELECT count(*) FROM glpi_plugin_seguridad_seguridads_users where users_id = v_users_id);
					
					IF (v_excluded=0)  
					THEN

						SET v_ip = SUBSTRING_INDEX(NEW.message,' ',-1); 
														
						INSERT INTO `glpi_plugin_seguridad_seguridads` (`users_id`, `name`, `success`, `ip`, `date`, `date_creation`, `locked`, `unlocked`)
						values(v_users_id, v_name, v_success, v_ip, now(), now(), 0, 0); 
											
					END IF;
			  
			  END IF;
			  
			END;";
	
			$DB->query($query); 
	
			$tabla.=' <table><tr>
				<td class="center" colspan="2">- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -<br>
				<strong>Trigger enlazado con </strong>: <strong><FONT color="green">glpi_events</FONT></strong><br>
				- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -<br>
				</td>
			  </tr></table>';  			  
			  
Session::addMessageAfterRedirect(__($tabla, 'plugin_seguridad'),false, INFO);			  

   $config = new Config();
   $config->setConfigurationValues('plugin:seguridad', ['configuration' => false]);

   //ProfileRight::addProfileRights(['plugin_seguridad']);

  PluginSeguridadProfile::initProfile();
  PluginSeguridadProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);   
  
  $table = getTableForItemType("PluginSeguridadSeguridad");
  
		$query = "insert $table (users_id, name, ip, date, success, date_creation) 
 select users_id, name, ip, date, success, NOW() from (
select b.id as users_id, log.name, log.ip, log.date, success, NOW() from (
 select id as events_id, if (name='Fallo',SUBSTRING_INDEX(SUBSTRING_INDEX(message,' ',8),' ',-1),name) as name, 
                 if (name='Fallo',0,1) as success, ip, date from 
				  (SELECT id, date, message, SUBSTRING_INDEX(message,' ',1) AS name,
                  SUBSTRING_INDEX(SUBSTRING_INDEX(message,'IP',2),' ',-1) AS ip
                  FROM glpi_events WHERE level=3 and date>DATE_SUB(NOW(),INTERVAL 2 MONTH )) as eventos) as log
                  left join glpi_users b on log.name = b.name and b.id not in (select users_id from glpi_plugin_seguridad_seguridads_users)) as t where users_id is not null";
				  
				  $DB->query($query) or die ("Error adding values $table");
		 
     			Session::addMessageAfterRedirect(__('			
						<table>
			  <tr>
				<td align="left"><img style="vertical-align:middle;" alt="" src="'.$_SESSION["glpiroot"].'/plugins/seguridad/img/save.png">&nbsp;&nbsp;</td>
				<td class="center">&nbsp;
				<FONT color="#4f35a2"><strong>Volcado</strong> desde <strong></font><font color="green">glpi_events</font></strong><FONT color="#4f35a2"> a</font> <br>- - - - - - - - - - - - - - - - - - <br>
				<font color="green"><strong>'.$table.'</strong></font><br><br><FONT color="#4f35a2"> realizado con </font><strong><font color="green">exito<font></strong>		
				</td>
				
			  </tr>
			</table>','plugin_seguridad'),true, INFO);	  
  
   
   return true;
}


/**
 * Plugin uninstall process
 *
 * @return boolean
 */
 
// Uninstall process for plugin
function plugin_seguridad_uninstall() {   
   global $DB;
   
   $profileRight = new ProfileRight();
   foreach (PluginSeguridadProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(array('name' => $right['field']));
   }
    PluginSeguridadProfile::removeRightsFromSession();    
 // ProfileRight::deleteProfileRights(['plugin_seguridad']);   
   
   $config = new Config();
   $config->deleteConfigurationValues('plugin:seguridad', ['configuration' => false]);

  
   
   $query = "SELECT * 
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA in ('".$DB->dbdefault."') and table_name LIKE 'glpi_plugin_seguridad_%' and TABLE_TYPE = 'BASE TABLE'";

$result = $DB->query($query);
$rows=$DB->numrows($result);
	if ( $rows > 0) {
		
		
		$tabla='<table>
			  <tr>
				<td align="left"><img style="vertical-align:middle;" alt="" src="'.$_SESSION["glpiroot"].'/plugins/seguridad/img/erase.png">&nbsp;&nbsp;</td>
				<td class="center">&nbsp;
				<strong>Desinstalación</strong> realizada con <strong><font color="green">Éxito</font></strong> <br>- - - - - - - - - - - - - - - - - - <br>
				<strong>Plugin Seguridad</strong> versión <strong><font color="green">'. PLUGIN_SEGURIDAD_VERSION .'</font></strong>		
				</td>
			  </tr>
			  
			  <tr>
				<td class="center" colspan="2">- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -<br>
				<strong>Tablas eliminadas</strong>: <strong><FONT color="#620613">'.$rows.'</FONT></strong><br>
				- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -<br>
				</td>
			  </tr>
			  
			  ';
		
	while ($data=$DB->fetch_array($result)){
		$DB->query("DROP TABLE `".$data["TABLE_NAME"]."`");
		
		$tabla.='
			  <tr>
				<td colspan="2" align="left">&nbsp;&nbsp;<img style="vertical-align:middle;" alt="" src="'.$_SESSION["glpiroot"].'/plugins/seguridad/img/minus.png">&nbsp;
				&nbsp;<strong><FONT color="#620613">'.$data["TABLE_NAME"].'</FONT>.</strong>				
				</td>
			  </tr>';				
	}

		$tabla.='<tr>
				<td class="center" colspan="2">- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -</td>
			  </tr>';

		$DB->query("DROP TRIGGER IF EXISTS after_events_insert;");
		 
			$tabla.='  <tr>
				<td class="center" colspan="2">- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -<br>
				<strong>Trigger enlazado con <font color="green">glpi_events</font></strong>: <strong><FONT color="#620613">eliminado</FONT></strong><br>
				- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -<br>
				</td>
			  </tr></table>';

      			Session::addMessageAfterRedirect(__($tabla, 'plugin_seguridad'),false, INFO);

	}
		     
   //CronTask::unregister('PluginSeguridadTask');
   
   return true;
} 

function plugin_seguridad_postinit() {
   global $DB, $CFG_GLPI;
   //echo $_POST[$_SESSION['namfield']]."<br>";
if  ((isset($_SESSION['namfield'])) and (isset($_POST[$_SESSION['namfield']]))) {
$sesion = new PluginSeguridadSeguridad();	 

$usuario = ((isset($_SESSION['glpiname'])) ? $_SESSION['glpiname'] : $_POST[$_SESSION['namfield']]);

$date = explode(" ",$_SESSION['glpi_currenttime'])[0];

/*echo $usuario;
echo $_SESSION['namfield']."<br>";
echo $_POST[$_SESSION['namfield']];*/

      $result = $DB->request([
         'FROM'   => $sesion->getTable(),
         'WHERE'  => [
            "name" => $usuario,
			"date" => ['LIKE', $date.'%'],
         ],
         'ORDER'  => [            
            'id DESC'
         ],
		 'LIMIT'  => 1
      ])->next(); 


if (($result['unlocked']==1) and date_result($result['date']))  {
	
$toADD = "";

// Redirect management
if (isset($_POST['redirect']) && (strlen($_POST['redirect']) > 0)) {
   $toADD = "?redirect=" .$_POST['redirect'];

} else if (isset($_GET['redirect']) && (strlen($_GET['redirect']) > 0)) {
   $toADD = "?redirect=" .$_GET['redirect'];
}

if (isset($_SESSION["noAUTO"]) || isset($_GET['noAUTO'])) {
   if (empty($toADD)) {
      $toADD .= "?";
   } else {
      $toADD .= "&";
   }
   $toADD .= "noAUTO=1";
}	

$_SESSION['SEGURIDAD'] = [
"USER" => $usuario,
"DATE" => $result["date"],
];

Html::redirect($CFG_GLPI["root_doc"]."/index.php".$toADD);

} else {

//unset($_SESSION['SEGURIDAD']);
	
}	

}

}

// Check to add to status page
function plugin_seguridad_Status($param) {
   // Do checks (no check for seguridad)
   $ok = true;
   echo "seguridad plugin: seguridad";
   if ($ok) {
      echo "_OK";
   } else {
      echo "_PROBLEM";
      // Only set ok to false if trouble (global status)
      $param['ok'] = false;
   }
   echo "\n";
   return $param;
}




function plugin_seguridad_display_central() {
   echo "<tr><th colspan='2'>";
   
  echo '<div id="overbox3">
    <div id="infobox3"><p>
	<div id="button_close">
	  	<button onclick="aceptar_aviso();" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" title="Cerrar Aviso Legal"><span  id="button_cerrar" class="ui-button-icon-primary ui-icon ui-icon-close"></span><span class="ui-button-text">Cerrar Aviso Legal</span></button>
	</div>
	<div class="sesion_warning"><div class="sesion_msg"><i class="fa fa-exclamation-triangle fa-5x"></i><ul><li>';
		
   echo __('<font color="yellow">AVISO LEGAL:</font> Usted ha accedido a un equipo propiedad de la CARM. Debe tener autorización personal por parte del administrador antes de usarlo y está estrictamente limitado al uso establecido en dicha autorización. El acceso no autorizado a este sistema está prohibido y constituye un delito contemplado por el presente Código Penal. Si usted revela información confidencial sin estar autorizado deberá responder ante la Ley por sus acciones.</div>', "seguridad");   
		
  echo '</li></ul></div></div>	
	</p>
	</div>
	</div>
	</th>
	</tr>';
	
	echo Html::scriptBlock('$(document).ready(function(){
    $("#infobox3").slideToggle("slow");
					   })');
					   
	 if (isset($_SESSION['glpiID'])){
	 $_SESSION['MENSAJE_SEGURIDAD']="OK";
	 }
}

function clock($block_date) {
	    global $CFG_GLPI;

   echo '<div id="timer"><div class="divider"><h3>Su cuenta se bloqueó por superar el máximo de sesiones fallidas.</h3></div>
            <div class="container">               
                <div id="minute">00</div>
                <div class="divider">:</div>
                <div id="second">00</div>                
            </div>            
			<h3>Intente acceder despues de este tiempo.</h3>
        </div>';
  
   
   echo "<style>
   
   #timer{  display:none; color:white; width:100%; margin-top:-20px; margin-bottom: 20px; font-family: 'Bitstream Vera Sans', arial, Tahoma, 'Sans serif'; text-align:center;}

   #timer .container{
  display:table;
  background:#3a5693;
  border-color: gray;
  border-width: 5px;
  border-style: dotted;
  color:#fec95c;
  font-weight:bold;
  width:200px;
  text-align:center;
  text-shadow:1px 1px 4px #999;  
  margin:0px auto;
  }

#timer .container div{display:table-cell;font-size:60px;padding:10px;width:20px;}
#timer .container .divider{
	width:10px;
	color:#fec95c;
  animation-name: parpadeo;
  animation-duration: 1s;
  animation-timing-function: linear;
  animation-iteration-count: infinite;

  -webkit-animation-name:parpadeo;
  -webkit-animation-duration: 1s;
  -webkit-animation-timing-function: linear;
  -webkit-animation-iteration-count: infinite;
	
	}
	
@-moz-keyframes parpadeo{  
  0% { opacity: 1.0; }
  50% { opacity: 0.0; }
  100% { opacity: 1.0; }
}

@-webkit-keyframes parpadeo {  
  0% { opacity: 1.0; }
  50% { opacity: 0.0; }
   100% { opacity: 1.0; }
}

@keyframes parpadeo {  
  0% { opacity: 1.0; }
   50% { opacity: 0.0; }
  100% { opacity: 1.0; }
}	
	
   </style>";	

   echo Html::scriptBlock("

$(document).ready(function() {
   
   var end = new Date('".$block_date."');

    var _second = 1000;
    var _minute = _second * 60;
    var _hour = _minute * 60;
    var _day = _hour * 24;
    var timer;
    var now = new Date();
    var distance = end - now;
		
			$('#boxlogin').html('');
			$('#timer').slideToggle('slow');
		 
		 if (distance < 0) {
		  $('.container').html('<h3>DESBLOQUEANDO CUENTA....</h3>'); 
		 }  
	
	
    function showRemaining() {
        var now = new Date();
        var distance = end - now;
		
        if (distance < 0) {            
			  
			   $.ajax({
              url: '{$CFG_GLPI["root_doc"]}/plugins/seguridad/config.php',
			  method: 'post',             
                       success: function() {	
						clearInterval(timer);					   
						$('.container').html('<h3>DESBLOQUEANDO CUENTA....</h3>');
						location.reload();	
						clearInterval(timer);						
                       },
            });
			  
            	return;
        }
		
        var days = Math.floor(distance / _day);
        var hours = Math.floor((distance % _day) / _hour);
        var minutes = Math.floor((distance % _hour) / _minute);
        var seconds = Math.floor((distance % _minute) / _second);
        if (minutes<10) { minutes='0'+minutes; } 
		if (seconds<10) { seconds='0'+seconds; } 
    //  document.getElementById('hour').innerHTML = hours;
        document.getElementById('minute').innerHTML = minutes;
        document.getElementById('second').innerHTML = seconds;
    }

    timer = setInterval(showRemaining, 1000);


});

");	
	
	
}

function date_result($date, $calculate = 0) {

	  $config = new PluginSeguridadConfig();
	  
	    $config->getFromDB(1);
		
		//echo $config->fields['time_block'];
		         

/*	echo strftime("%a %d %b %H:%M:%S %Y", strtotime($date))."<br/>";
	echo strftime("%a %d %b %H:%M:%S %Y", strtotime($date))."<br/>";
echo strftime("%a %d %b %H:%M", strtotime($date))."<br/>";
echo strftime("%Y-%m-%d %H:%M:%S", strtotime($date))."<br/>";
echo strftime("%Y-%m-%d %H:%M", strtotime($date))."<br/>";
echo strftime("%Y-%m-%d", strtotime($date))."<br/>";
echo strftime("%a %d %b %Y", strtotime($date))."<br/>";	*/	
	
$fechaFinal = strftime("%Y-%m-%d %H:%M:%S");
$fechaInicial = $date;
//ECHO "<BR> fechaFinal ".$fechaFinal."<BR>";
//ECHO "<BR> fechaInicial ".$fechaInicial."<BR>";
$segundos = strtotime($fechaFinal) - strtotime($fechaInicial);
//echo "segundos ".$segundos." time_block ".$config->fields['time_block']."<br>";

$diff = strtotime($fechaFinal) + ($config->fields['time_block']-$segundos);

if ($segundos<$config->fields['time_block']) {

//print $segundos;

$block_date = (($calculate==1) ? strftime("%d/%m/%Y %H:%M:%S", $diff) : strftime("%m/%d/%Y %l:%M:%S %p", $diff));	

} else {
	
$block_date = (($calculate==1) ? strftime("%d/%m/%Y %H:%M:%S", $diff) : false);	
//	unset($_SESSION['SEGURIDAD']);
}	

return $block_date;

}
	



function plugin_seguridad_display_login($params) {
 global $DB;
 
if (isset($_SESSION['SEGURIDAD']['USER'])) {

$sesion = new PluginSeguridadSeguridad();	 

$usuario = $_SESSION['SEGURIDAD']['USER'];

/*echo $usuario;
echo $_SESSION['namfield']."<br>";
echo $_POST[$_SESSION['namfield']];*/

      $result = $DB->request([
         'FROM'   => $sesion->getTable(),
         'WHERE'  => [
            "name" => $usuario,			
         ],
         'ORDER'  => [            
            'id DESC'
         ],
		 'LIMIT'  => 1
      ])->next(); 


if ($result['unlocked']==1) {

if (isset($_SESSION['SEGURIDAD'])) {
if ($block_date = date_result($_SESSION['SEGURIDAD']['DATE'])){
	//echo date_result($_SESSION['SEGURIDAD']['DATE']);
clock($block_date);
} else {
unset($_SESSION['SEGURIDAD']);
} }

} }

//var_dump($_SESSION['SEGURIDAD']['DATE']);
//  echo date_result($_SESSION['SEGURIDAD']['DATE'])."11";
  echo '<div style="text-align:justify; font-size:0.9em;"><font color="#dd1d1d">AVISO LEGAL</font>: Usted va a acceder a un equipo propiedad de la CARM. Debe tener autorización personal por parte del administrador antes de usarlo y está estrictamente limitado al uso establecido en dicha autorización. El acceso no autorizado a este sistema está prohibido y constituye un delito contemplado por el presente Código Penal. Si usted revela información confidencial sin estar autorizado deberá responder ante la Ley por sus acciones.<div class="sep"></div>';
      
}

function plugin_seguridad_infocom_hook($params) {
   echo "<tr><th colspan='4'>";
   echo __("<div style='text-align:justify; font-size:0.8em'>", "seguridad");
   echo "</th></tr>";
}

/*function plugin_seguridad_item_add($item) {
	
	var_dump($item);
	exit();
	
}*/

function plugin_init_session_seguridad () {
 global $DB, $CFG_GLPI;
 
if (isset($_SESSION['glpiname'])) {

	$usuario = $_SESSION['glpiname'];
	//echo "1 ".$usuario;

} else {

	if (isset($_SESSION['namfield']) && isset($_POST[$_SESSION['namfield']])) {
		$usuario = $_POST[$_SESSION['namfield']];
  } 

}


if (isset($usuario)) {

	$date = explode(" ",$_SESSION['glpi_currenttime'])[0];

			$sesion = new PluginSeguridadSeguridad();	 
			
			$config = new PluginSeguridadConfig();
			
				$config->getFromDB(1);
			
			//echo $config->fields['time_block'];
			
				$iterator = $DB->request([         
					'FROM'   => $sesion->getTable(),
					'WHERE'  => [					
						"name" => $usuario,	
						"date" => ['LIKE', $date.'%'],									
					],
					'LIMIT'  => $config->fields['intentos'],
			'ORDER'    => 'id DESC',
				]);		

			$intentos=0;
			$sesions_id=0;
			if (count($iterator)==$config->fields['intentos']) {
	
					while ($data = $iterator->next()) {
					
					if ($sesions_id==0) { $sesions_id = $data['id']; }
					
					if ($data['success']==1) {
					$intentos++;
					}             
						
					if ((($data['unlocked']==1) and (date_result($data['date'])=== false)) or (($data['locked']==1) and ($data['unlocked']==0)))  {
					$intentos++;	
					}
						}		
			
					if ($intentos==0){
					
						$params = [
						"id" => $sesions_id,
						"locked" => 1,
						"unlocked" => 1,
						];
			
						$sesion->update($params);
						
						$_SESSION['SEGURIDAD'] = [
						"USER" => $usuario,
						"DATE" => $_SESSION['glpi_currenttime'],
						];						

						Html::redirect($CFG_GLPI["root_doc"]."/index.php?noAUTO=1");
						
						//var_dump ($_SESSION['SEGURIDAD']);	 	 
					
					}	
			}	 
	}		 	  
}





/*function plugin_init_session_seguridad () {
 global $DB, $CFG_GLPI;
 
$usuario = ((isset($_SESSION['glpiname'])) ? $_SESSION['glpiname'] : $_POST[$_SESSION['namfield']]);

$date = explode(" ",$_SESSION['glpi_currenttime'])[0];

$params = [
"service" => 'login',
"level" => 3,
'date'  => ['>=', $date],
'date'  => ['<=', $_SESSION['glpi_currenttime'],
            ['OR'          => [
					"message" => ['LIKE', $usuario.'%'],               
					"message" => ['LIKE', 'Fallo en el inicio de sesión de '.$usuario.'%']               			   
							  ]
            ]],
];

if (isset($_SESSION['glpiID'])) {

$users_id=$_SESSION['glpiID'];	
	
} else {  

	  $user = new User();
	  $item = $user->find(["name" => $usuario]);
	  
		if (!empty($item)) {

		$items = current($item);

		$users_id = $items['id'];

		} else { 
		
		$users_id = null;
		
		}
		
}	


  $log = new Event();	  
  $logs = $log->find($params);

	  $sesion = new PluginSeguridadSeguridad();	 

foreach ($logs as $logs_id => $sesion_log) {

$success = ((strpos($sesion_log["message"], "Fallo") !== false) ? 0 : 1);

$ip=explode("IP ",$sesion_log["message"])[1];
	
	$criteria = [
	"id" => $logs_id,
	];	
	
	         if (!countElementsInTable(PluginSeguridadSeguridad::getTable(), $criteria)) {
	
					$input = ["id" => $logs_id,
                             "users_id" => $users_id,
							 "name"   => $usuario,
                             "ip"   => $ip,
                             "date"  => $_SESSION['glpi_currenttime'],
                             "date_creation" => $_SESSION['glpi_currenttime'],
                             "success"  => $success,
                             "locked" => 0,
							 "unlocked" => 0];
							 
	 $sesions_id = $sesion->add($input);
	  //echo $logs_id."<br>";
	  
	  $config = new PluginSeguridadConfig();
	  
	    $config->getFromDB(1);
		
		//echo $config->fields['time_block'];
		
      $iterator = $DB->request([         
         'FROM'   => $sesion->getTable(),
         'WHERE'  => [					
					"name" => $usuario,	
					"date" => ['LIKE', $date.'%'],									
         ],
         'LIMIT'  => $config->fields['intentos'],
		 'ORDER'    => 'id DESC',
      ]);		

		$intentos=0;
		
		if (count($iterator)==$config->fields['intentos']) {
		
         while ($data = $iterator->next()) {
			  
			  if ($data['success']==1) {
			   $intentos++;
			   }             
     		  
			  if ((($data['unlocked']==1) and (date_result($data['date'])=== false)) or (($data['locked']==1) and ($data['unlocked']==0)))  {
			   $intentos++;	
			  }
          }		
		 
		 if ($intentos==0){
		
			$params = [
			"id" => $sesions_id,
			"locked" => 1,
			"unlocked" => 1,
			];
 
			$sesion->update($params);
			
			$_SESSION['SEGURIDAD'] = [
			"USER" => $usuario,
			"DATE" => $_SESSION['glpi_currenttime'],
			];						

			Html::redirect($CFG_GLPI["root_doc"]."/index.php?noAUTO=1");
			
			//var_dump ($_SESSION['SEGURIDAD']);	 	 
		
		}	} 
		 	  
			 }
}

}*/