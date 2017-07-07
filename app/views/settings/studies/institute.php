<? use Studip\Button; ?>

<h3 style="text-align: center;"><?= _('Meine Einrichtungen:') ?></h3>

<? if ($allow_change['in']): ?>
<form action="<?= $controller->url_for('settings/studies/store_in') ?>" method="post">
    <input type="hidden" name="studipticket" value="<?= get_ticket() ?>">
    <?= CSRFProtection::tokenTag() ?>
<? endif; ?>

    <table class="default" id="select_institute">
        <colgroup>
            <col>
            <col width="100px">
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Einrichtung') ?></th>
                <th>
                <? if ($allow_change['in']): ?>
                    <?= _('austragen') ?>
                <? endif; ?>
                </th>
        </thead>
        <tbody>
        <? if (count($institutes) === 0 && $allow_change['in']): ?>
            <tr>
                <td colspan="2" style="background: inherit;">
                    <strong><?= _('Sie haben sich noch keinen Einrichtungen zugeordnet.') ?></strong><br>
                    <br>
                    <?= _('Wenn Sie auf Ihrem Profil Ihre Einrichtungen '
                          . 'auflisten wollen, können Sie diese Einrichtungen hier eintragen.') ?>
                </td>
            </tr>
        <? endif; ?>
        <? foreach ($institutes as  $inst_member): ?>
            <tr>
                <td>
                    <label for="inst_delete_<?= $inst_member->institute->id ?>"><?= htmlReady($inst_member->institute->name) ?></label>
                </td>
                <td style="text-align:center">
                <? if ($allow_change['in']): ?>
                    <input type="checkbox" name="inst_delete[]" id="inst_delete_<?= $inst_member->institute->id ?>"
                           value="<?= $inst_member->institute->id ?>">
                <? else: ?>
                    <?= Icon::create('accept', 'inactive')->asImg(['class' => 'text-top']) ?>
                <? endif; ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">
                <? if ($allow_change['in']): ?>
                    <label for="select_new_inst">
                        <?= _('Um sich einer Einrichtung zuzuordnen, wählen '
                              . 'Sie die entsprechende Einrichtung aus der folgenden Liste aus:') ?>
                    </label>
                    <br>
                    <br>

                    <a name="einrichtungen"></a>
                    <select name="new_inst" id="new_inst" class="nested-select">
                        <option value="" class="is-placeholder">
                            <?= _('-- Bitte Einrichtung auswählen --') ?>
                        </option>
                        <? foreach ($available_institutes as $i) : ?>
                            <? if (InstituteMember::countBySql('user_id = ? AND institut_id = ?', [$user->user_id, $i['Institut_id']]) == 0
                                   && (!($i['is_fak'] && $user->perms == 'admin') || $GLOBALS['perm']->have_perm('root'))
                            ): ?>
                                <option class="<?= $i['is_fak'] ? 'nested-item-header' : 'nested-item' ?>"
                                        value="<?= htmlReady($i['Institut_id']) ?>">
                                    <?= htmlReady(my_substr($i['Name'], 0, 70)) ?>
                                </option>
                            <? else: ?>
                                <option class="<?= $i['is_fak'] ? 'nested-item-header' : 'nested-item' ?>" disabled>
                                    <?= htmlReady(my_substr($i['Name'], 0, 70)) ?>
                                </option>
                            <? endif; ?>
                        <? endforeach; ?>
                    </select>
                    <br>
                    <br>

                    <?= _('Wenn Sie aus Einrichtungen wieder ausgetragen werden möchten, '
                          . 'markieren Sie die entsprechenden Felder in der linken Tabelle.') ?><br>
                    <?= _('Mit einem Klick auf <b>Übernehmen</b> werden die gewählten Änderungen durchgeführt.') ?>
                    <br>
                    <br>

                    <?= Button::create(_('Übernehmen'), 'store_in', ['title' => _('Änderungen übernehmen')]) ?>
                <? else: ?>
                    <?= _('Die Informationen zu Ihrer Einrichtung werden vom System verwaltet, '
                          . 'und können daher von Ihnen nicht geändert werden.') ?>
                <? endif; ?>
                </td>
            </tr>
        </tfoot>
    </table>

<? if ($allow_change['in']): ?>
</form>
<? endif; ?>
