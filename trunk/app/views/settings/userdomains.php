<? use Studip\Button; ?>

<? if ($allow_change): ?>
<form action="<?= $controller->url_for('settings/userdomains/store') ?>" method="post">
    <input type="hidden" name="studipticket" value="<?= get_ticket() ?>">
    <?= CSRFProtection::tokenTag() ?>
    <? endif; ?>
    <table class="default" id="assigned_userdomains">
        <caption><?= _('Ich bin folgenden Nutzerdomänen zugeordnet:') ?></caption>
        <colgroup>
            <col>
            <col width="100px">
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Nutzerdomäne') ?></th>
                <th>
                    <? if ($allow_change): ?>
                        <?= _('austragen') ?>
                    <? else: ?>
                        &nbsp;
                    <? endif; ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <? if (count($user_domains) === 0): ?>
                <tr>
                    <td colspan="2" style="text-align: center">
                        <?= _('Sie sind noch keiner Nutzerdomäne zugeordnet.') ?>
                    </td>
                </tr>
            <? endif; ?>
            <? foreach ($user_domains as $domain): ?>
                <tr>
                    <td><?= htmlReady($domain->getName()) ?></td>
                    <td style="text-align:center">
                        <? if ($allow_change): ?>
                            <input type="checkbox" name="userdomain_delete[]" value="<?= $domain->getID() ?>">
                        <? else: ?>
                            <?= Icon::create('accept', 'inactive')->asImg(['class' => 'text-top']) ?>
                        <? endif; ?>
                    </td>
                </tr>
            <? endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" id="select_userdomains">
                    <? if ($allow_change): ?>
                        <strong><?= _('Wählen Sie eine Nutzerdomäne aus der folgenden Liste aus:') ?></strong><br>
                        <br>

                        <a name="userdomains"></a>
                        <? if (!empty($domains)) : ?>
                            <select name="new_userdomain" id="new_userdomain">
                                <option selected value="none"><?= _('-- Bitte Nutzerdomäne auswählen --') ?></option>
                                <? foreach ($domains as $domain) : ?>
                                    <option value="<?= $domain->getID() ?>"><?= htmlReady(my_substr($domain->getName(), 0, 50)) ?></option>
                                <? endforeach ?>
                            </select>
                        <? endif ?>
                        <br>

                        <?= _('Wenn Sie Nutzerdomänen wieder entfernen möchten, markieren '
                              . 'Sie die entsprechenden Felder in der linken Tabelle.') ?><br>
                        <?= _('Mit einem Klick auf <b>Übernehmen</b> werden die gewählten Änderungen durchgeführt.') ?>
                        <br>
                        <br>

                        <?= Button::create(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
                    <? else: ?>
                        <?= _('Die Informationen zu Ihren Nutzerdomänen werden vom System verwaltet und können daher von Ihnen nicht geändert werden.') ?>
                    <? endif; ?>
                </td>
            </tr>
        </tfoot>
    </table>
    <? if ($allow_change): ?>
</form>
<? endif; ?>
