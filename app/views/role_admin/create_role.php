<?= $this->render_partial('role_admin/status_message') ?>
<h3>
    <?=_("Rollenverwaltung")?>
</h3>
<form action="<?=$controller->url_for('role_admin/create_role')?>" method="POST">
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
<form action="<?=$controller->url_for('role_admin/remove_role')?>" method="post">
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
<?
$infobox_content = array(
                array  ("kategorie"  => _("Hinweise:"),
                        "eintrag" => array  (
                            array ( "icon" => "ausruf_small.gif",
                                            "text"  => _("Zum Erstellen neuer Rollen geben Sie den Namen ein und klicken Sie auf Anlegen.")
                            ),
                            array ( "icon" => "ausruf_small.gif",
                                            "text"  =>_("Zum Löschen von Rollen wählen Sie diese aus und klicken Sie auf Löschen.<br>Systemrollen können jedoch nicht gelöscht werden und sind daher nicht auswählbar.")
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
