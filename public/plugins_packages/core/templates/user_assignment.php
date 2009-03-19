<table border="0" width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td valign="top">
		<div class="topic"><b><?=_("Rollen-Verwaltung für Benutzer")?></b></div>
<form action="<?=$links['doRoleAssignment']?>" method="POST">
<table border="0" width="100%" cellpadding="2" cellspacing="0">
	<tr class="steel1">
		<td>
			Name der Person: <input type="text" name="usersearchtxt" size="25" value="<?=$usersearchtxt ?>" style="width: 300px;">
			<?= makeButton("suchen","input",_("Benutzer suchen"),"searchuserbtn") ?>
		</td>
	</tr>
</table>
<br/>
<? if (!empty($users)): ?>
<table border="0" width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<th align="left"><?= _("Benutzer auswählen")?>: </th>
	</tr>
	<tr class="steelgraulight">
		<td>
			<select size="1" name="usersel" style="min-width: 300px;">
			<? foreach ($users as $user): ?>
				<option value="<?= $user->getUserid()?>" <?=!empty($currentuser) && $currentuser->isSameUser($user) ? "selected" : ""?>><?= $user->getGivenname() . " " . $user->getSurname() . " (" . $user->getUsername() . ")"?></option>
			<? endforeach; ?>
			</select>
			<?= makeButton("auswaehlen","input",_("Benutzer auswählen"),"seluserbtn") ?>
			<?= makeButton("zuruecksetzen","input",_("Suche zurücksetzen"),"resetseluser") ?>
		</td>
	</tr>
</table>
<br/>
<? endif; ?>
<? if (!empty($currentuser)): $assigned = $currentuser->getAssignedRoles(); ?>
<table border="0" width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<th><?= _(sprintf("Rollen für %s",$currentuser->getGivenname() . " " . $currentuser->getSurname()))?></th>
		<th></th>
		<th><?=_("Verfügbare Rollen")?></th>
	</tr>
	<tr class="steel1">
		<td valign="top" align="right">
			<select multiple name="assignedroles[]" size="10" style="width: 300px;">
			<? foreach ($assigned as $assignedrole): ?>
				<option value="<?= $assignedrole->getRoleid()?>"><?= $assignedrole->getRolename()?> <? if($assignedrole->getSystemtype()):?>[Systemrolle]<? endif; ?></option>
			<? endforeach; ?>
			</select>
		</td>
		<td valign="middle" align="center">
		<input type="image" src="<?=$GLOBALS['ASSETS_URL']?>images/move_left.gif" name="assignrolebtn" alt="<?= _("Markierte Rollen dem Benutzer zuweisen.") ?>">
		<br/><br/>
		<input type="image" src="<?=$GLOBALS['ASSETS_URL']?>images/move_right.gif" name="deleteroleassignmentbtn" alt="<?= _("Markierte Rollen entfernen.") ?>">
		</td>
		<td valign="top">
			<select size="10" name="rolesel[]" multiple style="width: 300px;">
			<? foreach ($roles as $role): ?>
				<option value="<?= $role->getRoleid()?>"><?= $role->getRolename() ?> <? if($role->getSystemtype()):?>[Systemrolle]<? endif; ?></option>
			<? endforeach; ?>
			</select>
		</td>
	</tr>
</table>
<br/>
<table border="0" width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<th align="left"><?= _("Implizit zugewiesene Systemrollen")?></th>
	</tr>
	<? foreach ($implicidroles as $key=>$role):?>
	<tr class="<?=($key%2==0)?'steel1':'steelgraulight' ?>">
		<td><?=$role ?></td>
	</tr>
	<? endforeach; ?>
</table>
<? endif; ?>
</form>
		</td>
		<td width="270" align="right" valign="top">
		<?
		$infobox = array(
			array  ("kategorie"  => _("Hinweise:"),
				"eintrag" => array	(
					array (	"icon" => "ausruf_small.gif",
									"text"  => _("Hier können Sie nach Benutzern suchen und Ihnen verschiedene Rollen zuweisen.")
					)
				)
			),
			array  ("kategorie"  => _("Aktionen:"),
					"eintrag" => array	(
						array (	"icon" => "link_intern.gif",
										"text"  => '<a href="'.$links['createRole'].'">'._("Rollen verwalten").'</a>'
						),
						array (	"icon" => "link_intern.gif",
										"text"  => '<a href="'.$links['doRoleAssignment'].'">'._("Benutzerzuweisungen bearbeiten").'</a>'
						),
						array (	"icon" => "link_intern.gif",
										"text"  => '<a href="'.$links['doPluginRoleAssignment'].'">'._("Pluginzuweisungen bearbeiten").'</a>'
						),
						array (	"icon" => "link_intern.gif",
										"text"  => '<a href="'.$links['showRoleAssignments'].'">'._("Rollenzuweisungen anzeigen").'</a>'
						),
					)
			)
		);
		print_infobox ($infobox,"modules.jpg");
		?>
		</td>
	</tr>
</table>