# # GLPi Plugin seguridad
Plugin Security to GLPI.

## Introduction

The security plugin allows you to add a layer of security over the Login page by locking the page for 5 minutes if you try to login more than 3 times incorrectly.

## Installation

put this code on this file: front/login.php

// now we can continue with the process...
if ($auth->login($login, $password, (isset($_REQUEST["noAUTO"])?$_REQUEST["noAUTO"]:false), $remember, $login_auth)) {
   Auth::redirectIfAuthenticated();
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
