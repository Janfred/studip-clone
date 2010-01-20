<?= $this->render_partial('role_admin/status_message') ?>
<h3><?= _('Rollenzuweisungen anzeigen') ?></h3>

<form action="<?=$controller->url_for('role_admin/show_role')?>" method="post">
<select name="role" style="width: 300px">
    <? foreach($roles as $getrole): ?>
        <option value="<?=$getrole->getRoleid()?>"<? if($getrole->getRoleid()==$roleid):?>selected="selected"<? endif; ?>><?=$getrole->getRolename()?> <? if($getrole->getSystemtype()):?>[Systemrolle]<? endif; ?></option>
    <? endforeach; ?>
</select>
<?= makeButton("auswaehlen","input",_("Rolle auswählen"),"selectrole") ?>
</form>
<br>

<? if(!empty($role)): ?>
<h3><?= sprintf(_('Liste der Benutzer mit der Rolle "%s"'), $role->getRolename()) ?></h3>
<? if (count($users) > 0): ?>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
    <tr>
        <th width="3%"></th>
        <th align="left">Name</th>
        <th align="left">Benutzername</th>
    </tr>
    <? foreach ($users as $index=>$user): ?>
    <tr class="<?=($index%2==0)?'steel1':'steelgraulight'?>">
        <td align="right"><?=$index+1?>.) </td>
        <td><?=htmlReady($user['vorname'])?> <?=htmlReady($user['nachname'])?></td>
        <td>
        <a href="<?=URLHelper::getLink('about.php', array('username' => $user['username'])) ?>"><?=$user['username']?></a>
        </td>
    </tr>
    <? endforeach; ?>
</table>
<? else:?>
    <?=Messagebox::error(_("Es wurden keine Benutzer gefunden.")) ?>
<? endif; ?>
<br>
<h3><?= sprintf(_('Liste der Plugins mit der Rolle "%s"'), $role->getRolename()) ?></h3>
<? if (count($plugins) > 0): ?>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
    <tr>
        <th width="3%"></th>
        <th align="left">Name</th>
        <th align="left">Typ</th>
    </tr>
    <? foreach ($plugins as $plugin): ?>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td align="right"><?= ++$index ?>.) </td>
        <td><?= htmlspecialchars($plugin['name']) ?></td>
        <td><?= join(', ', $plugin['type']) ?></td>
    </tr>
    <? endforeach; ?>
</table>
<? else:?>
    <?=Messagebox::error(_("Es wurden keine Plugins gefunden.")) ?>
<? endif; ?>
<? endif; ?>
<?
//Infobox
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
    array(
        'kategorie' => _('Hinweise').':',
        'eintrag' => array(
            array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Hier werden alle Benutzer und Plugins angezeigt, die der ausgewählten Rolle zugewiesen sind.')
            ),
            array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Klicken Sie auf den Benutzernamen, um sich die Homepage des Benutzers anzeigen zulassen.')
            )
        )
    )
);
$infobox = array('picture' => 'modules.jpg', 'content' => $infobox_content);
?>
