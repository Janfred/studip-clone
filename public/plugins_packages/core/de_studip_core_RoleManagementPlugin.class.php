<?php
/**
 * Extends Stud.IP by role management, currently mainly used by the plugin engine.
 */
//ini_set("error_reporting",4096);
// ini_set("display_errors","on");
require_once("de_studip_core_RoleManagementVisualization.class.php");

class de_studip_core_RoleManagementPlugin extends AbstractStudIPAdministrationPlugin{
	
	var $rolemgmt;
	var $usermgmt;
	
	function de_studip_core_RoleManagementPlugin(){
		parent::AbstractStudIPAdministrationPlugin();
		$nav = new PluginNavigation();
		$nav->setDisplayname(_("Verwaltung von Rollen"));
		
		// $this->setTopnavigation($nav);
		$this->setNavigation($nav);
		
		$this->setPluginiconname("img/einst.gif");
		
		$this->rolemgmt = new de_studip_RolePersistence();		
		$roles = $this->rolemgmt->getAllRoles();	
		PluginEngine::saveToSession($this,"roles",$this->rolemgmt->getAllRoles());
		$pluginpersist = PluginEngine::getPluginPersistence();
		$pluginid = $pluginpersist->getPluginid("de_studip_core_UserManagementPlugin");
		$this->usermgmt = $pluginpersist->getPlugin($pluginid);					
	}
	
	function actionShow(){
		// show the standard view
		$rolevis = PluginEngine::getValueFromSession($this,"rolevis");
		if (empty($rolevis)){
			$rolevis = new de_studip_core_RoleManagementVisualization($this);
			PluginEngine::saveToSession($this,"rolevis",$rolevis);
		}
		$rolevis->showDefaultView();
	}
	
	function deletePluginRoleAssignment(){
		print_r($_REQUEST);
	}
	
	function checkRootRights(){
		$user = $this->getUser();
		$perms = $user->getPermission();
		if (!$perms->hasRootPermission()){
			die;
		}
	}
	
	function actionDoPluginRoleAssignment(){
		$pluginid = $_REQUEST["pluginid"];	
		$assignbtn = $_REQUEST["assignrolebtn_x"];
		$delassignbtn = $_REQUEST["deleteroleassignmentbtn_x"];
		$selroles = $_REQUEST["rolesel"];
		$delassignedrols = $_REQUEST["assignedroles"];
		
		$this->checkRootRights();
		
		if (!empty($assignbtn)){	
			// assign roles		
			$this->rolemgmt->assignPluginRoles($pluginid,$selroles);
			StudIPTemplateEngine::showSuccessMessage(_("Die Rechteeinstellungen wurden erfolgreich gespeichert."));
		}
		if (!empty($delassignbtn)){
			// delete role assignment
			$this->rolemgmt->deleteAssignedPluginRoles($pluginid,$delassignedrols);
			StudIPTemplateEngine::showSuccessMessage(_("Die Rechteeinstellungen wurden erfolgreich gespeichert."));
		}
			
		$pluginpersist = PluginEngine::getPluginPersistence();
		$pluginforassignment = $pluginpersist->getPlugin($pluginid);
		$rolevis = PluginEngine::getValueFromSession($this,"rolevis");
		if (empty($rolevis)){
			$rolevis = new de_studip_core_RoleManagementVisualization($this);
			PluginEngine::saveToSession($this,"rolevis",$rolevis);
		}
		$roles = PluginEngine::getValueFromSession($this,"roles");
		
		if (empty($roles)){
			$roles = $this->rolemgmt->getAllRoles();	
			PluginEngine::saveToSession($this,"roles",$this->rolemgmt->getAllRoles());
		}					
		$rolevis->showPluginRolesAssignmentForm($pluginforassignment,$roles,$this->rolemgmt->getAssignedPluginRoles($pluginid));	
	}

	function actionCreateRole(){
		$createrolebtn = $_POST["createrolebtn_x"];
		$this->checkRootRights();
		if (!empty($createrolebtn)){
			$role = new de_studip_Role();
			$role->setRolename($_POST["newrole"]);
			$this->rolemgmt->saveRole($role);			
			// create a new role
			PluginEngine::saveToSession($this,"roles",null);
		}
		$rolevis = PluginEngine::getValueFromSession($this,"rolevis");
		if (empty($rolevis)){
			$rolevis = new de_studip_core_RoleManagementVisualization($this);
			PluginEngine::saveToSession($this,"rolevis",$rolevis);
		}
		$roles = PluginEngine::getValueFromSession($this,"roles");
		
		if (empty($roles)){
			$roles = $this->rolemgmt->getAllRoles();	
			PluginEngine::saveToSession($this,"roles",$this->rolemgmt->getAllRoles());
		}	
		$rolevis->showRoleForm($roles);
	}
	
	function actionRemoveRole(){
		$removerolebtn = $_POST["removerolebtn_x"];
		$roles = PluginEngine::getValueFromSession($this,"roles");
		$this->checkRootRights();
		if (empty($roles)){
			$roles = $this->rolemgmt->getAllRoles();	
			PluginEngine::saveToSession($this,"roles",$this->rolemgmt->getAllRoles());
		}	
		
		if (!empty($removerolebtn)){
			$rolesel = $_POST["rolesel"];
			$delroles = array();
			foreach ($rolesel as $roleid){
				$this->rolemgmt->deleteRole($roles[$roleid]);
			}
			PluginEngine::saveToSession($this,"roles",null);
		}
		$rolevis = PluginEngine::getValueFromSession($this,"rolevis");
		if (empty($rolevis)){
			$rolevis = new de_studip_core_RoleManagementVisualization($this);
			PluginEngine::saveToSession($this,"rolevis",$rolevis);
		}
		$roles = PluginEngine::getValueFromSession($this,"roles");
		
		if (empty($roles)){
			$roles = $this->rolemgmt->getAllRoles();	
			PluginEngine::saveToSession($this,"roles",$this->rolemgmt->getAllRoles());
		}	
		$rolevis->showRoleForm($roles);
	}
	
	function actionDoRoleAssignment(){		
		$usersearchtxt = $_POST["usersearchtxt"];
		$userselid = $_POST["usersel"];
		$rolselids = $_POST["rolesel"];
		$searchuserbtn = $_POST["searchuserbtn_x"];
		$assignrolebtn = $_POST["assignrolebtn_x"];
		$deleteassignedrolebtn = $_POST["deleteroleassignmentbtn_x"];
		$delassignedroles = $_POST["assignedroles"];
		$seluserbtn = $_POST["seluserbtn_x"];
		
		// check, if admin is editing roles
		$this->checkRootRights();
		
		$founduser = array();
		if (!empty($seluserbtn)){
			$founduser = PluginEngine::getValueFromSession($this,"searcheduser");
			$selecteduser = $founduser[$userselid];
			PluginEngine::saveToSession($this,"selecteduser",$selecteduser);
		}
		else if (!empty($searchuserbtn) && !empty($usersearchtxt)){			
			// Suche wurde angefordert			
			$founduser = $this->usermgmt->searchUser($usersearchtxt);
			
			PluginEngine::saveToSession($this,"searcheduser",$founduser);
			PluginEngine::saveToSession($this,"selecteduser",null);
		}		
		
		$selecteduser = PluginEngine::getValueFromSession($this,"selecteduser");
		if (empty($founduser)){
			$founduser = PluginEngine::getValueFromSession($this,"searcheduser");
		}
		$rolevis = PluginEngine::getValueFromSession($this,"rolevis");
				
		if (empty($rolevis)){
			$rolevis = new de_studip_core_RoleManagementVisualization($this);
			PluginEngine::saveToSession($this,"rolevis",$rolevis);
		}
		$roles = PluginEngine::getValueFromSession($this,"roles");	
		if (empty($roles)){
			$roles = $this->rolemgmt->getAllRoles();	
			PluginEngine::saveToSession($this,"roles",$this->rolemgmt->getAllRoles());
		}			
		if (!empty($assignrolebtn)){			
			foreach ($rolselids as $selroleid) {
				// for all selected roles
				$role = $roles[$selroleid];
				$this->rolemgmt->assignRole($selecteduser,$role);
			}					
			StudIPTemplateEngine::showSuccessMessage(_("Zuweisungen erfolgreich durchgeführt."));
		}	

		if (!empty($deleteassignedrolebtn)){
			ini_set("display_errors","on");
			foreach ($delassignedroles as $roleid){
				$role = $roles[$roleid];
				$this->rolemgmt->deleteRoleAssignment($selecteduser,$role);
			}
			StudIPTemplateEngine::showSuccessMessage(_("Rollenzuweisung erfolgreich gelöscht."));
		}
			
		$rolevis->showRoleAdministrationForm($founduser,$roles,$usersearchtxt,$selecteduser);		
	}
}
?>