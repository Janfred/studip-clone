<?php if (!$has_enabled) : ?>
    <?= MessageBox::info(_('Es gibt keine aktiven Schritte für den Anlegeassistenten!')) ?>
<?php endif ?>
<form method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <caption>
            <?= _('Vorhandene Schritte im Anlegeassistenten für Veranstaltungen') ?>
            <span class="actions">
            <a href="<?= $controller->url_for('admin/coursewizardsteps/edit') ?>" data-dialog="size=auto">
                <?= Icon::create('add', 'clickable')->asImg() ?></a>
        </span>
        </caption>
        <colgroup>
            <col style="width: 30%">
            <col style="width: 30%">
            <col style="width: 5%">
            <col style="width: 5%">
            <col style="width: 10%">
        </colgroup>
        <thead>
            <th><?= _('Name') ?></th>
            <th><?= _('PHP-Klasse') ?></th>
            <th><?= _('Nummer') ?></th>
            <th><?= _('aktiv?') ?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </thead>
        <tbody>
            <? if ($steps) : ?>
                <?php foreach ($steps as $step) : ?>
                    <tr>
                        <td><?= htmlReady($step->name) ?></td>
                        <td><?= htmlReady($step->classname) ?></td>
                        <td><?= $step->number ?></td>
                        <td><?= $step->enabled ? Icon::create('checkbox-checked', 'info')->asImg() :
                                    Icon::create('checkbox-unchecked', 'info')->asImg() ?></td>
                        <td class="actions">
                            <? $actionMenu = ActionMenu::get() ?>
                            <? $actionMenu->addLink($controller->url_for('admin/coursewizardsteps/edit/' . $step->id),
                                    _('Schritt bearbeiten'),
                                    Icon::create('edit', 'clickable'),
                                    ['data-dialog' => 'size=auto']) ?>

                            <? $actionMenu->addButton(
                                    'delete_step',
                                    _('Schritt lsöchen'),
                                    Icon::create('trash', 'clickable',
                                            ['title'        => _('Studiengangteil löschen'),
                                             'formaction'   => $controller->url_for('admin/coursewizardsteps/delete/' . $step->id),
                                             'data-confirm' => sprintf(_('Soll der Eintrag "%s" wirklich gelöscht werden?'), htmlReady($step->name)),
                                             'style'        => 'margin: 0px']))
                            ?>
                            <?= $actionMenu->render() ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            <?php else : ?>
                <tr>
                    <td colspan="5" style="text-align: center">
                        <?= _('Es sind keine Schritte für den Veranstaltungsanlegeassistenten registriert!') ?>
                    </td>
                </tr>
            <?php endif ?>
        </tbody>
    </table>
</form>
