<?= $this->render_partial('role_admin/status_message') ?>
<h3>
    <?=_("Rollenverwaltung für Plugins")?>
</h3>
<form action="<?=$controller->url_for('role_admin/assign_plugin_role')?>" method="POST">
<table border="0" width="100%" cellpadding="2" cellspacing="0">
    <tr class="steel1">
        <td>
        <select name="pluginid" style="min-width: 300px;">
        <? foreach ($plugins as $plugin): ?>
            <option value="<?=$plugin['id']?>" <? if($plugin['id']==$pluginid): ?>selected="selected"<? endif; ?>><?=$plugin['name']?></option>
        <? endforeach; ?>
        </select>
            <?= makeButton("auswaehlen","input",_("Plugin auswahlen"),"searchuserbtn") ?>
        </td>
    </tr>
</table>
<br/>
<? if($pluginid): ?>
<table width="100%" cellpadding="2" cellspacing="0" border="0">
<tr>
    <th><?=_("Gegenwärtig zugewiesene Rollen")?></th>
    <th></th>
    <th><?=_("Verfügbare Rollen")?></th>
</tr>
<tr class="steel1">
    <td valign="top" align="right">
        <select multiple name="assignedroles[]" size="10" style="width: 300px;">
        <? foreach ($assigned as $assignedrole): ?>
            <option value="<?=$assignedrole->getRoleid()?>"><?=$assignedrole->getRolename()?> <? if($assignedrole->getSystemtype()):?>[Systemrolle]<? endif; ?></option>
        <? endforeach; ?>
        </select>
    </td>
    <td valign="middle" align="center">
        <input type="image" src="<?= Assets::image_path('move_left.gif') ?>" name="assignrolebtn" alt="<?= _("Markierte Rollen dem Plugin zuweisen.") ?>">
        <br/><br/>
        <input type="image" src="<?= Assets::image_path('move_right.gif') ?>" name="deleteroleassignmentbtn" alt="<?= _("Markierte Rollen entfernen.") ?>">
    </td>
    <td valign="top">
        <select multiple name="rolesel[]" size="10" style="width: 300px;">
        <? foreach ($roles as $role): ?>
                <option value="<?=$role->getRoleid()?>"><?=$role->getRolename()?> <? if($role->getSystemtype()):?>[Systemrolle]<? endif; ?></option>
        <? endforeach; ?>
        </select>
    </td>
</tr>
</table>
<? endif; ?>
</form>
<?
$infobox_content = array(
        array  ("kategorie"  => _("Hinweise:"),
                "eintrag" => array  (
                    array ( "icon" => "ausruf_small.gif",
                                    "text"  => _("Sie können in diesem Dialog den Zugriff auf das Plugin durch die Auswahl von Rollen beschränken.")
                    ),
                    array ( "icon" => "ausruf_small.gif",
                                    "text"  =>_("Wählen Sie bspw. Evaluationsbeauftragte(r), so können alle Nutzer, die sich in der Rolle Evaluationsbeauftragte(r) befinden, dieses Plugin sehen und nutzen, unabhängig vom Stud.IP-Status")
                    )
                )
        ),
        array  ("kategorie"  => _("Aktionen:"),
                "eintrag" => array  (
                    array ( "icon" => "link_intern.gif",
                                    "text"  => '<a href="'.$controller->url_for('role_admin/create_role').'">'._("Rollen verwalten").'</a>'
                    ),
                    array ( "icon" => "link_intern.gif",
                                    "text"  => '<a href="'.$controller->url_for('role_admin/assign_role').'">'._("Benutzerzuweisungen bearbeiten").'</a>'
                    ),
                    array ( "icon" => "link_intern.gif",
                                    "text"  => '<a href="'.$controller->url_for('role_admin/assign_plugin_role').'">'._("Pluginzuweisungen bearbeiten").'</a>'
                    ),
                    array ( "icon" => "link_intern.gif",
                                    "text"  => '<a href="'.$controller->url_for('role_admin/show_role').'">'._("Rollenzuweisungen anzeigen").'</a>'
                    ),
                )
        )
);
$infobox = array('picture' => 'modules.jpg', 'content' => $infobox_content);
?>
