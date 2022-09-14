<?php
/*
   ----------------------------------------------------------
   Plugin Iframe 1.0
   GLPI 9.1.6 
  
   Autor: Javier David Marín Zafrilla.
   Fecha: Febrero 2019

   ----------------------------------------------------------
 */

include ("../../../inc/includes.php");
                       

if (!isset($_GET["id"])) $_GET["id"] = "";
if (!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";


$PluginSeguridadSeguridad_User = new PluginSeguridadSeguridad_User();

 if (isset($_POST["adduser"])) {   
   if ($_POST['users_id']>0) {
	   
		if (empty($PluginSeguridadSeguridad_User->find(["users_id" => $_POST['users_id']]))) {
			   $PluginSeguridadSeguridad_User ->addItem($_POST);
		} else {

			$dbu = new DbUtils();			
			
			$tabla = '<table>
					  <tr>
						<td align="left"><img style="vertical-align:middle;" alt="" src="'.$_SESSION["glpiroot"].'/pics/icones/system-attention-icon.png">&nbsp;&nbsp;</td>
						<td class="center">&nbsp;
						<FONT size="2px" color="#630a12"><strong>EL USUARIO YA EXISTE</strong></font><FONT color="yellow"><br>- - - - - - - - - - - - - - - - - - - - - - - - - - - - -<br></font>	
						<FONT color="#530910"><strong>'.$dbu->getUserName($_POST['users_id']).'</strong><br> Ya está <strong>Excluido</strong> de la <strong>Monitorización</strong>.</font>
						</td>				
					  </tr>
					</table>';	
			
			Session::addMessageAfterRedirect(__($tabla),false, ERROR);			
		}			
	
   }
   Html::back();  
} else if (isset($_POST["elimina"])){
	$PluginSeguridadSeguridad_User->delete(["id"=>$_POST["elimina"]]);
	Html::back();

} else {
	  
   Html::header(__('seguridad', 'seguridad'), $_SERVER['PHP_SELF'] ,"config", "pluginseguridadseguridad", "seguridad");
			   
   $PluginSeguridadSeguridad_User->display($_GET["id"]);
   Html::footer();
   
}
?>
