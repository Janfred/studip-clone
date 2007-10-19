<?php
/**
 * @author Dennis Reil, <Dennis.Reil@offis.de>
 * @version $Revision$
 */
require_once('lib/language.inc.php');

class de_studip_core_RoleManagementVisualization extends AbstractStudIPPluginVisualization {
	
	/**
	 * 
	 *
	 * @param AbstractStudIPPlugin $pluginref
	 * @return de_studip_core_RoleManagementVisualization
	 */
	function de_studip_core_RoleManagementVisualization($pluginref){
		parent::AbstractStudIPPluginVisualization($pluginref);
	}
	
	function showDefaultView(){
		?>
		
		<table>
		<tr>
			<td><a href="<?= PluginEngine::getLink($this->pluginref,array(),"createRole")?>"><?= _("Rolle erstellen")?></a></td>
		</tr>
		<tr>
			<td><a href="<?= PluginEngine::getLink($this->pluginref,array(),"doRoleAssignment")?>"><?= _("Rollenzuweisungen bearbeiten")?></a></td>			
		</tr>
		</table>
		<?
	}
	
	function showRoleForm($roles){
		StudIPTemplateEngine::makeContentHeadline(_("Rollen-Verwaltung"));
		?>
		<form action="<?= PluginEngine::getLink($this->pluginref,array(),"createRole")?>" method="POST">
		<table>
		<tr>
			<td colspan="2">
				<?= _("Neue Rolle: ") ?>
				<input type="text" name="newrole" size="20" value="Neue Rolle"><?= makeButton("anlegen","input",_("Rolle anlegen"),"createrolebtn") ?><br>
			</td>
		</tr>
		</form>
		<form action="<?= PluginEngine::getLink($this->pluginref,array(),"removeRole")?>" method="POST">
		<tr>			
			<th><?= _("Vorhandene Rollen")?></th>
		</tr>
		<tr>			
			<td>
				<select size="10" name="rolesel[]" multiple>
					<?
					foreach ($roles as $role){
						?>
						<option value="<?= $role->getRoleid()?>"><?= $role->getRolename() ?></option>
						<?	
					}
					?>
				</select>
			</td>
		</tr>		
		<tr>
			<td>
				<?= _("Markierte Rollen:") ?><?= makeButton("loeschen","input",_("Markierte Einträge löschen"),"removerolebtn")?><!--&nbsp;<?= makeButton("bearbeiten","input",_("Markierte Einträge ändern"),"editrolebtn")?>-->
			</td>
		</tr>
		</table>
		</form>
		<?
	}
	
	function showRoleAdministrationForm($users=array(),$roles=array(),$lastsearch="",$currentuser=""){		
		?>
		<form action="<?= PluginEngine::getLink($this->pluginref,array(),"doRoleAssignment")?>" method="POST">
		<table>
		<tr>
			<th colspan="3"><?= _("Rollen-Verwaltung für Benutzer")?></th>
		</tr>
		<tr>
			<td colspan="3">
				<?= _("Suche nach Benutzern: ") ?>
				<input type="text" name="usersearchtxt" size="20" value="<?= $lastsearch ?>"><?= makeButton("suchen","input",_("Benutzer suchen"),"searchuserbtn") ?><br>
				<br>
				<?
				if (!empty($users)){
					?>
					<select size="1" name="usersel">					
					<?
					foreach ($users as $user){
						?>
						<option value="<?= $user->getUserid()?>" <?=  !empty($currentuser) && $currentuser->isSameUser($user) ? "selected" : ""?>><?= $user->getGivenname() . " " . $user->getSurname() . " (" . $user->getUsername() . ")"?></option>
						<?	
					}
					?>
					</select>
					<input type="image" src="<?=$GLOBALS['ASSETS_URL']?>images/move_down.gif" name="seluserbtn">
					<br><br>
					<?
				}		
				?>	
			</td>
		</tr>
		<?  
		if (!empty($currentuser)){
			$assigned = $currentuser->getAssignedRoles();
			?>
					<tr>
					<th><?= _(sprintf("Rollen für %s",$currentuser->getGivenname() . " " . $currentuser->getSurname()))?></th>
					<th>&nbsp;</th>
					<th><?= _("Verfügbare Rollen")?></th>
				</tr>
				
				<tr>
					<td valign="top">
						<select multiple name="assignedroles[]" size="7">				
						<? 					
							foreach ($assigned as $assignedrole){
								?>
								<option value="<?= $assignedrole->getRoleid()?>"><?= $assignedrole->getRolename()?>
								<?
							}
						?>
						</select>
					</td>
					<td valign="=middle">
					<input type="image" src="<?=$GLOBALS['ASSETS_URL']?>images/move_left.gif" name="assignrolebtn" alt="<?= _("Markierte Rollen dem Benutzer zuweisen.") ?>"><br><br>
					<input type="image" src="<?=$GLOBALS['ASSETS_URL']?>images/move_right.gif" name="deleteroleassignmentbtn" alt="<?= _("Markierte Rollen entfernen.") ?>">			
					</td>
					<td valign="top">
						<select size="10" name="rolesel[]" multiple>
							<?
							foreach ($roles as $role){
								?>
								<option value="<?= $role->getRoleid()?>"><?= $role->getRolename() ?></option>
								<?	
							}
							?>
						</select>
					</td>
				</tr>	
				<tr>
					<th colspan="3"><?= _("Implizit zugewiesene Systemrollen")?></th>
				</tr>			
				<tr>
					<td colspan="3">
					<?
					$withimplicitassigned = $currentuser->getAssignedRoles(true);					
					
					foreach ($withimplicitassigned as $assignedrole){
						$found = false;
						foreach ($assigned as $explassignedrole){
							if ($explassignedrole->getRoleid() == $assignedrole->getRoleid()){
								$found = true;
								break;
							}
						}
						if (!$found){
							echo ("<b>" . $assignedrole->getRolename() . "</b><br>");	
						}
					}					
					?>
					</td>
				</tr>
			<?
		}
		?>
		</table>
		</form>
		<?
	}
	
	function showPluginRolesAssignmentForm($plugin,$roles=array(),$assigned=array()){		
		?>
		<form action="<?= PluginEngine::getLink($this->pluginref,array(),"doPluginRoleAssignment")?>" method="POST">
		<input type="hidden" name="pluginid" value="<?= $plugin->getPluginid()?>">
		<table style="width: 100%;" cellspacing="0">		
		<thead>
			<th width="30%" align="center"><?=_("Gegenwärtig zugewiesene Rollen")?></th>
			<th width="1%" align="center">&nbsp;</th>
			<th align="center"><?=_("Verfügbare Rollen")?></th>
		</thead>
		<tr>
			<td valign="top">
				<select multiple name="assignedroles[]" size="7">				
				<? 					
					foreach ($assigned as $assignedrole){
						?>
						<option value="<?= $assignedrole->getRoleid()?>"><?= $assignedrole->getRolename()?>
						<?
					}
				?>
				</select>
			</td>
			<td valign="middle">
			<input type="image" src="<?=$GLOBALS['ASSETS_URL']?>images/move_left.gif" name="assignrolebtn" alt="<?= _("Markierte Rollen dem Plugin zuweisen.") ?>"><br><br>
			<input type="image" src="<?=$GLOBALS['ASSETS_URL']?>images/move_right.gif" name="deleteroleassignmentbtn" alt="<?= _("Markierte Rollen entfernen.") ?>">			
			</td>
			<td valign="top">
				<select multiple name="rolesel[]" size="10">				
				<? 
					foreach ($roles as $role){
						?>
						<option value="<?= $role->getRoleid()?>"><?= $role->getRolename()?>
						<?
					}
				?>
				</select>
			</td>
		</tr>		
		</table>
		</form>	
		<?		
		StudIPTemplateEngine::createInfoBoxTableCell();
		$infobox = array	(	
						array  ("kategorie"  => _("Hinweise:"),
								"eintrag" => array	(	
									array (	"icon" => "ausruf_small.gif",
													"text"  => _("Sie können in diesem Dialog den Zugriff auf das Plugin durch die Auswahl von Rollen beschränken.")
									),
									array (	"icon" => "ausruf_small.gif",
													"text"  =>_("Wählen Sie bspw. Evaluationsbeauftragte(r), so können alle Nutzer, die sich in der Rolle Evaluationsbeauftragte(r) befinden, dieses Plugin sehen und nutzen, unabhängig vom Stud.IP-Status")
									)
								)
						)
				);
		print_infobox ($infobox);
		StudIPTemplateEngine::endInfoBoxTableCell();
	}
}
?>
