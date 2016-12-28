<? use Studip\Button; ?>

<h3 style="text-align: center;"><?= _('Ich studiere folgende Fächer und Abschlüsse:') ?></h3>

<? if ($allow_change['sg']): ?>
<form action="<?= $controller->url_for('settings/studies/store_sg') ?>" method="post">
    <input type="hidden" name="studipticket" value="<?= get_ticket() ?>">
    <?= CSRFProtection::tokenTag() ?>
    <? endif; ?>
    <? $modulemanagement_enabled = PluginEngine::getPlugin('MVVPlugin'); ?>
    <table class="default" id="select_fach_abschluss">
        <colgroup>
            <col>
            <col>
            <?= $modulemanagement_enabled ? '<col>' : '' ?>
            <col width="100px">
            <col width="100px">
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Fach') ?></th>
                <th><?= _('Abschluss') ?></th>
                <? if ($modulemanagement_enabled) : ?>
                    <th id="version_label"><?= _('Versionen') ?></th>
                <? endif; ?>
                <th id="fachsemester_label"><?= _('Fachsemester') ?></th>
                <th style="text-align:center;" id="austragen_label">
                    <? if ($allow_change['sg']): ?>
                        <?= _('austragen') ?>
                    <? else: ?>
                        &nbsp;
                    <? endif; ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <? if (count($user->studycourses) === 0 && $allow_change['sg']): ?>
            <tr>
                <td colspan="<?= $modulemanagement_enabled ? '5' : '4' ?>" style="background: inherit;">
                    <strong><?= _('Sie haben sich noch keinem Studiengang zugeordnet.') ?></strong><br>
                    <br>
                    <?= _('Tragen Sie bitte hier die Angaben aus Ihrem Studierendenausweis ein!') ?>
                </td>
            <tr>
                <? endif; ?>


                <? foreach ($user->studycourses as $usc): ?>
            <tr>
                <td><?= htmlReady($usc->studycourse->name) ?></td>
                <td><?= htmlReady($usc->degree->name) ?></td>
                <? if ($modulemanagement_enabled) : ?>
                    <? if ($allow_change['sg']): ?>
                        <td>
                            <? $versionen = StgteilVersion::findByFachAbschluss($usc->fach_id, $ucs->abschluss_id); ?>
                            <? $versionen = array_filter($versionen, function ($ver) {
                                return $ver->hasPublicStatus('genehmigt');
                            }); ?>
                            <? if (count($versionen)) : ?>
                                <select name="change_version[<?= $usc->fach_id ?>][<?= $ucs->abschluss_id ?>]"
                                        aria-labelledby="version_label">
                                    <option value=""><?= _('-- Bitte Version auswählen --') ?></option>
                                    <? foreach ($versionen as $version) : ?>
                                        <option<?= $version->getId() == $ucs->version_id ? ' selected' : '' ?>
                                                value="<?= htmlReady($version->getId()) ?>">
                                            <?= htmlReady($version->getDisplayName()) ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            <? else : ?>
                                <?= tooltipIcon(_('Keine Version in der gewählten Fach-Abschluss-Kombination verfügbar.'), true) ?>
                            <? endif; ?>
                        </td>
                    <? else : ?>
                        <? $version = StgteilVersion::find($ucs->version_id); ?>
                        <td>
                            <? if ($version && $version->hasPublicStatus('genehmigt')) : ?>
                                <?= htmlReady($version->getDisplayName()); ?>
                            <? endif; ?>
                        </td>
                    <? endif; ?>
                <? endif; ?>
                <? if ($allow_change['sg']): ?>
                    <td>
                        <select name="change_fachsem[<?= $usc->fach_id?>][<?= $ucs->abschluss_id ?>]"
                                aria-labelledby="fachsemester_label">
                            <? for ($i = 1; $i <= 50; $i += 1): ?>
                                <option <? if ($i == $ucs->semester) echo 'selected'; ?>><?= $i ?></option>
                            <? endfor; ?>
                        </select>
                    </td>
                    <td style="text-align:center">
                        <input type="checkbox" aria-labelledby="austragen_label"
                               name="fach_abschluss_delete[<?= $usc->fach_id ?>]"
                               value="<?= $ucs->abschluss_id ?>">
                    </td>
                <? else: ?>
                    <td><?= htmlReady($ucs->semester) ?></td>
                    <td style="text-align: right;">
                        <?= Icon::create('accept', 'inactive')->asImg(['class' => 'text-top']) ?>
                    </td>
                <? endif; ?>
            </tr>
        <? endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="<?= $modulemanagement_enabled ? '5' : '4' ?>">
                    <? if ($allow_change['sg']): ?>
                        <p>
                            <?= _('Wählen Sie die Fächer, Abschlüsse und Fachsemester in der folgenden Liste aus:') ?>
                        </p>
                        <p>
                            <a name="studiengaenge"></a>
                            <select name="new_studiengang" id="new_studiengang"
                                    aria-label="<?= _('-- Bitte Fach auswählen --') ?>">
                                <option selected value="none"><?= _('-- Bitte Fach auswählen --') ?></option>
                                <? foreach ($faecher as $fach) : ?>
                                    <?= sprintf('<option value="%s">%s</option>', $fach->id, htmlReady(my_substr($fach->name, 0, 50))); ?>
                                <? endforeach ?>
                            </select>

                            <a name="abschluss"></a>
                            <select name="new_abschluss" id="new_abschluss"
                                    aria-label="<?= _('-- Bitte Abschluss auswählen --') ?>">
                                <option selected value="none"><?= _('-- Bitte Abschluss auswählen --') ?></option>
                                <? foreach ($abschluesse as $abschluss) : ?>
                                    <?= sprintf('<option value="%s">%s</option>' . "\n", $abschluss->id, htmlReady(my_substr($abschluss->name, 0, 50))); ?>
                                <? endforeach ?>
                            </select>

                            <a name="semester"></a>
                            <select name="fachsem" aria-label="<?= _("Bitte Fachsemester wählen") ?>">
                                <? for ($i = 1; $i <= 50; $i += 1): ?>
                                    <option><?= $i ?></option>
                                <? endfor; ?>
                            </select>
                        </p>

                        <p>
                            <?= _('Wenn Sie einen Studiengang wieder austragen möchten, '
                                  . 'markieren Sie die entsprechenden Felder in der oberen Tabelle.') ?>
                            <?= _('Mit einem Klick auf <b>Übernehmen</b> werden die gewählten Änderungen durchgeführt.') ?>
                            <br>
                            <br>
                            <?= Button::create(_('Übernehmen'), 'store_sg', ['title' => _('Änderungen übernehmen')]) ?>
                        </p>
                    <? else: ?>
                        <?= _('Die Informationen zu Ihrem Studiengang werden vom System verwaltet, '
                              . 'und können daher von Ihnen nicht geändert werden.') ?>
                    <? endif; ?>
                </td>
            </tr>
        </tfoot>
    </table>
    <? if ($allow_change['sg']): ?>
</form>
<? endif; ?>
