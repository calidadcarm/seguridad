<?php
/*
   ----------------------------------------------------------
   Plugin Iframe 1.0
   GLPI 9.1.6 
  
   Autor: Javier David Marín Zafrilla.
   Fecha: Febrero 2019

   ----------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginSeguridadSeguridad_User extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1 = 'PluginSeguridadConfig';
   static public $items_id_1 = 'plugin_seguridad_configs_id';
    
   static public $itemtype_2 = 'User';
   static public $items_id_2 = 'users_id';
   
   static $rightname = "plugin_seguridad";


   static function install(Migration $migration) {
      global $DB;
      
      $table = getTableForItemType(__CLASS__);
      if (!$DB->TableExists($table)) {
         $query = "
			CREATE TABLE `$table` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `plugin_seguridad_configs_id` int(11) NOT NULL DEFAULT '0',
			  `users_id` int(11) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `unicity` (`plugin_seguridad_configs_id`,`users_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

         $DB->query($query) or die ("Error adding table $table");
		 
		// echo $query."<br>";
		 
		 $query = "INSERT `$table` (`plugin_seguridad_configs_id`, `users_id`)
				   SELECT 1 AS plugin_seguridad_configs_id, `id` FROM `glpi_users` where (name = 'REGISTR@-RM' or name = 'sismon')"; 
		
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
   
   static function cleanForUser(CommonDBTM $user) {
      $temp = new self();
      $temp->deleteByCriteria(
         array('users_id' => $user->getField('id'))
      );
   }
   
   static function cleanForItem(CommonDBTM $item) {
      $temp = new self();
	  if ($item->getType()== 'User'){
		 $temp->deleteByCriteria(
				array('users_id' => $item->getField('id')));
	  } else if ($item->getType()== 'PluginSeguridadConfig') {
		  $temp->deleteByCriteria(
				array('plugin_seguridad_configs_id' => $item->getField('id')));
	  }
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
		if ($item->getType()=='PluginSeguridadConfig') {
            return _n('Usuario Excluido','Usuarios Excluidos',2);
		}
	}


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {  
      if ($item->getType()=='PluginSeguridadConfig') {        
        self::showForConfig($item);
      } 
      return true;
   }
   
   static function countForSeguridad(PluginIframeIframe $seguridad) {
      return countElementsInTable('glpi_plugin_seguridad_seguridads_users',
                                  " AND `plugin_seguridad_configs_id` = '".$seguridad->getID()."'");
   }

   function addItem($values) {

      $this->add(array('plugin_seguridad_configs_id' =>$values["plugin_seguridad_configs_id"],
                        'users_id'=>$values["users_id"]));
    
   }
   
	/**
	* Muestra los USUARIOS Excluidos.
    **/
	
   static function showForConfig(PluginSeguridadConfig  $config) {
      global $DB, $CFG_GLPI;
	  
      $instID = $config->fields['id'];
	
      if (!$config->can($instID, READ)) {
         return false;
      }
      $canedit = $config->can($instID, UPDATE);

      $rand   = mt_rand();
         echo "<form name='seguridaduser_form$rand' id='seguridaduser_form$rand' method='post'
               action='".Toolbox::getItemTypeFormURL("PluginSeguridadSeguridad_User")."'>";
      if ($canedit) {
         echo "<div class='firstbloc'>";


         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='2'>Añadir Usuarios</th></tr>";

         echo "<tr class='tab_bg_1'><td class='center'>";

		  User::dropdown([
				'name' => 'users_id',
				'right' => 'all',
				'all'   => 0,
		  ]);
         		
         echo "</td><td class='center'>";
         echo "<input type='submit' name='adduser' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "<input type='hidden' name='plugin_seguridad_configs_id' value='$instID'>";
         echo "</td></tr>";
         echo "</table>";
         echo "</div>";
      }

      echo "<div class='spaced'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";

      if ($canedit) {
         echo "<th width='10'>&nbsp;</th>";
      }
      echo "<th colspan='3'>Usuarios Excluidos de Monitorización</th>";
      echo "</tr>";

      $query     = "SELECT `glpi_users`.*,
                           `glpi_plugin_seguridad_seguridads_users`.`id` AS IDD, ";


      $query .= "`glpi_entities`.`id` AS entity
                  FROM `glpi_plugin_seguridad_seguridads_users`, `glpi_users`, `glpi_entities` ";
      $query .= "WHERE plugin_seguridad_configs_id=".$instID." and `glpi_users`.`id` = `glpi_plugin_seguridad_seguridads_users`.`users_id`
				 GROUP BY `glpi_users`.id ORDER BY `glpi_users`.name";
				 
				// echo $query;
				 
      if ($result_linked = $DB->query($query)) {
               if ($DB->numrows($result_linked)) {
                  while ($data = $DB->fetchAssoc($result_linked)) {
                     $linkname = $data["name"];
                     if ($_SESSION["glpiis_ids_visible"]
                         || empty($data["name"])) {
                        $linkname = sprintf(__('%1$s (%2$s)'), $linkname, $data["id"]);
                     }

                     $link = '../../../front/user.form.php';
                     $name = "<a href=\"".$link."?id=".$data["id"]."\">".$linkname."</a>";

                     echo "<tr class='tab_bg_1'>";

                     if ($canedit) {
                        echo "<td width='10' style='padding-top: 0'>";
                        echo "<button type='submit'  value='".$data["IDD"]."' name='elimina' style='border:0; background-color: Transparent;' 
						onclick=\"return confirm('¿Seguro que deseas monitorizar a este usuario?');\">
						<img src='".$_SESSION["glpiroot"]."/plugins/seguridad/img/error.png' /></button>";
                        echo "</td>";
                     }
					  
					 echo "<td>";				
				          echo "<div class='fa-label'>
							    <i class='fas fa-user-secret fa-fw' style='color:".(isset($data['is_deleted']) && $data['is_deleted']?"red'":"green'")."' title='Login de ".__('Login')."'></i>".$name."</div></td>";				
				     echo "<td><div class='fa-label'>
								<i class='fas fa-user fa-fw' style='color:".(isset($data['is_deleted']) && $data['is_deleted']?"red'":"green'")."' title='Nombre de ".__('Login')."'></i>";
			   
						  echo $data['firstname'].' '.$data['realname']."</div></td>";
					
					 echo "<td>";				
						  echo "<div class='fa-label'>
							    <i class='fas ".(isset($data['is_deleted']) && $data['is_deleted']?"fa-trash-alt'":"fa-thumbs-up'")." fa-fw' style='color:".(isset($data['is_deleted']) && $data['is_deleted']?"red'":"green'")."' title='".(isset($data['is_deleted']) && $data['is_deleted']? __('Deleted'):__('Active'))."'></i></div></td>";				
					 echo "</tr>";					
								
                  }
               }
      }
      echo "</table>";
      if ($canedit) {
         $paramsma['ontop'] =false;
         
      }
	  Html::closeForm();
      echo "</div>";
	  echo "</form>";

   }
   

   /**
    * @since version 0.84
   **/
   function getForbiddenStandardMassiveAction() {

      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }
   

}
?>