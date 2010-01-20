<?= $this->render_partial('role_admin/status_message') ?>
<h3>
    <?=_("Rollenverwaltung für Benutzer")?>
</h3>
<form action="<?=$controller->url_for('role_admin/assign_role')?>" method="POST">
<table border="0" width="100%" cellpadding="2" cellspacing="0">
    <tr class="steel1">
        <td>
            Name der Person: <input type="text" name="usersearchtxt" size="25" value="<?= htmlReady($usersearchtxt) ?>" style="width: 300px;">
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
                <option value="<?= $user->getUserid()?>" <?=!empty($currentuser) && $currentuser->isSameUser($user) ? "selected" : ""?>><?= htmlReady($user->getGivenname()) . " " . htmlReady($user->getSurname()) . " (" . $user->getUsername() . ")"?></option>
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
        <input type="image" src="<?= Assets::image_path('move_left.gif') ?>" name="assignrolebtn" alt="<?= _("Markierte Rollen dem Benutzer zuweisen.") ?>">
        <br/><br/>
        <input type="image" src="<?= Assets::image_path('move_right.gif') ?>" name="deleteroleassignmentbtn" alt="<?= _("Markierte Rollen entfernen.") ?>">
        </td>
        <td valign="top">
            <select size="10" name="rolesel[]" multiple style="width: 300px;">
            <? foreach ($roles as $role): ?>
                <option value="<?= $role->getRoleid()?>"><?=$role->getRolename() ?> <? if($role->getSystemtype()):?>[Systemrolle]<? endif; ?></option>
            <? endforeach; ?>
            </select>
        </td>
    </tr>
</table>
<br/>
<table border="0" width="100%" cellpadding="2" cellspacing="0">
    <tr>
        <td class="topic"><b><?= _("Implizit zugewiesene Systemrollen")?></b></td>
    </tr>
    <? foreach ($implicitroles as $key=>$role):?>
    <tr class="<?=($key%2==0)?'steel1':'steelgraulight' ?>">
        <td><?=$role ?></td>
    </tr>
    <? endforeach; ?>
</table>
<? endif; ?>
</form>
<?
$infobox_content = array(
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
    ),
    array  ("kategorie"  => _("Hinweise:"),
        "eintrag" => array  (
            array ( "icon" => "ausruf_small.gif",
                            "text"  => _("Hier können Sie nach Benutzern suchen und Ihnen verschiedene Rollen zuweisen.")
            )
        )
    )
);
$infobox = array('picture' => 'modules.jpg', 'content' => $infobox_content);
?>
