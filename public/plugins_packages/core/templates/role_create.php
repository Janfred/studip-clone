<table border="0" width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td valign="top">
<div class="topic"><b><?=_("Rollen-Verwaltung")?></b></div>
<form action="<?=$links['createRole']?>" method="POST">
<table cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<th align="left"><?=_("Neue Rolle anlegen")?></th>
	</tr>
	<tr class="steel1">
		<td>Name: <input type="text" name="newrole" size="25" value="">
			<?=makeButton("anlegen", "input", _("Rolle anlegen"), "createrolebtn")?><br>
		</td>
	</tr>
</table>
</form>
<br/>
<form action="<?=$links['removeRole']?>" method="POST">
<table cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<th align="left"><?=_("Vorhandene Rollen")?></th>
	</tr>
	<tr class="steel1">
		<td>
			<select size="10" name="rolesel[]" multiple style="width: 300px">
				<? foreach($roles as $role): ?>
					<option value="<?=$role->getRoleid()?>" <? if($role->getSystemtype()):?>disabled="disabled"<? endif; ?>><?=$role->getRolename()?> <? if($role->getSystemtype()):?>[Systemrolle]<? endif; ?></option>
				<? endforeach; ?>
			</select>
		</td>
	</tr>
	<tr class="steel2">
		<td>
			<?=_("Markierte Rollen:")?><?=makeButton("loeschen", "input", _("Markierte Einträge löschen"), "removerolebtn")?>
		</td>
	</tr>
</table>
</form>
		</td>
		<td width="270" align="right" valign="top">
		<?
		$infobox = array(
						array  ("kategorie"  => _("Hinweise:"),
								"eintrag" => array	(
									array (	"icon" => "ausruf_small.gif",
													"text"  => _("Zum Erstellen neuer Rollen geben Sie den Namen ein und klicken Sie auf Anlegen.")
									),
									array (	"icon" => "ausruf_small.gif",
													"text"  =>_("Zum Löschen von Rollen wählen Sie diese aus und klicken Sie auf Löschen.<br>Systemrollen können jedoch nicht gelöscht werden und sind daher nicht auswählbar.")
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