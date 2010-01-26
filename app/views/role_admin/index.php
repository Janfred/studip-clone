<?= $this->render_partial('role_admin/status_message') ?>

<h3>
    <?= _('Vorhandene Rollen') ?>
</h3>

<form action="<?= $controller->url_for('role_admin/remove_role') ?>" method="post">
    <input type="hidden" name="ticket" value="<?= get_ticket() ?>">
    <table class="default" style="width: 50%;">
        <tr>
            <th><?= _('Name') ?></th>
            <th style="text-align: right;"><?= _('Benutzer') ?></th>
            <th style="text-align: right;"><?= _('Plugins') ?></th>
            <th></th>
        </tr>
        <? foreach ($roles as $role): ?>
            <? $role_id = $role->getRoleid() ?>
            <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
                <td>
                    <a href="<?= $controller->url_for('role_admin/show_role', $role_id) ?>">
                        <?= htmlReady($role->getRolename()) ?>
                        <? if ($role->getSystemtype()): ?>[<?= _('Systemrolle') ?>]<? endif ?>
                    </a>
                </td>
                <td style="text-align: right;">
                    <?= $users[$role_id] ?>
                </td>
                <td style="text-align: right;">
                    <?= $plugins[$role_id] ?>
                </td>
                <td style="text-align: right;">
                    <? if (!$role->getSystemtype()): ?>
                        <input type="image" name="delete[<?= $role_id ?>]" src="<?= Assets::image_path('trash.gif') ?>" title="<?= _('Rolle löschen') ?>">
                    <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
    </table>
</form>

<h3>
    <?= _('Neue Rolle anlegen') ?>
</h3>

<form action="<?= $controller->url_for('role_admin/create_role') ?>" method="POST">
    <input type="hidden" name="ticket" value="<?= get_ticket() ?>">
    Name: <input type="text" name="name" size="25" value="">
    <?= makeButton('anlegen', 'input', _('Rolle anlegen'), 'createrolebtn') ?>
</form>

<?
$infobox_content = array(
    array(
        'kategorie' => _('Aktionen:'),
        'eintrag'   => array(
            array(
                'icon' => 'link_intern.gif',
                'text' => '<a href="'.$controller->url_for('role_admin').'">'._('Rollen verwalten').'</a>'
            ), array(
                'icon' => 'link_intern.gif',
                'text' => '<a href="'.$controller->url_for('role_admin/assign_role').'">'._('Benutzerzuweisungen bearbeiten').'</a>'
            ), array(
                'icon' => 'link_intern.gif',
                'text' => '<a href="'.$controller->url_for('role_admin/assign_plugin_role').'">'._('Pluginzuweisungen bearbeiten').'</a>'
            ), array(
                'icon' => 'link_intern.gif',
                'text' => '<a href="'.$controller->url_for('role_admin/show_role').'">'._('Rollenzuweisungen anzeigen').'</a>'
            )
        )
    ), array(
        'kategorie' => _('Hinweise:'),
        'eintrag'   => array(
            array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Zum Erstellen neuer Rollen geben Sie den Namen ein und klicken Sie auf "anlegen".')
            ), array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Zum Löschen von Rollen klicken Sie auf das Mülleimersymbol. Systemrollen können jedoch nicht gelöscht werden.')
            )
        )
    )
);

$infobox = array('picture' => 'modules.jpg', 'content' => $infobox_content);
?>
