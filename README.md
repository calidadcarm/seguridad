# # GLPi Plugin seguridad
Plugin Security to GLPI.

## Introduction

The security plugin allows you to add a layer of security over the Login page by locking the page for 5 minutes if you try to login more than 3 times incorrectly.

## Installation

put this code on this file: front/login.php


} else {

	//[jmz18g] [inicio] sesion seguridad deshabilitado
	//[INICIO] [CRI] JMZ18G SI EL PLUGIN SEGURIDAD ESTA ACTIVO
       $plug = new Plugin();
       if ($plug->isActivated("seguridad")) {
        
        $sesion = new PluginSeguridadSeguridad();	

        $sesion->session_error($_GET);
        
       }		  		          		 		 
      //[FINAL] [CRI] JMZ18G SI EL PLUGIN SEGURIDAD ESTA ACTIVO 
      //[jmz18g] [final] Sesión seguridad deshabilitado

   
   // we have done at least a good login? No, we exit.
  
   Html::nullHeader("Login", $CFG_GLPI["root_doc"] . '/index.php');
