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
    <? if (count($about->user_fach_abschluss) === 0 && $allow_change['sg']): ?>
        <tr>
            <td colspan="<?= $modulemanagement_enabled ? '5' : '4' ?>" style="background: inherit;">
                <strong><?= _('Sie haben sich noch keinem Studiengang zugeordnet.') ?></strong><br>
                <br>
                <?= _('Tragen Sie bitte hier die Angaben aus Ihrem Studierendenausweis ein!') ?>
            </td>
        <tr>
    <? endif; ?>
    <? foreach ($about->user_fach_abschluss as $details): ?>
        <tr>
            <td><?= htmlReady($details['studycourse_name']) ?></td>
            <td><?= htmlReady($details['degree_name']) ?></td>
        <? if ($allow_change['sg']): ?>
            <? if ($modulemanagement_enabled) : ?>
            <td>
                <? $versionen = StgteilVersion::findByFachAbschluss($details['fach_id'], $details['abschluss_id']); ?>
                <? $versionen = array_filter($versionen, function ($ver) {
                    return $ver->hasPublicStatus('genehmigt');
                }); ?>
                <? if (count($versionen)) : ?>
                <select name="change_version[<?= $details['fach_id'] ?>][<?= $details['abschluss_id'] ?>]" aria-labelledby="version_label">
                    <option value=""><?= _('-- Bitte Version auswählen --') ?></option>
                <? foreach ($versionen as $version) : ?>
                    <option<?= $version->getId() == $details['version_id'] ? ' selected' : '' ?> value="<?= htmlReady($version->getId()) ?>">
                        <?= htmlReady($version->getDisplayName()) ?>
                    </option>
                <? endforeach; ?>
                </select>
                <? else : ?>
                <?= tooltipIcon(_('Keine Version in der gewählten Fach-Abschluss-Kombination verfügbar.'), true) ?>
                <? endif; ?>
            </td>
            <? endif; ?>
            <td>
                <select name="change_fachsem[<?= $details['fach_id'] ?>][<?= $details['abschluss_id'] ?>]" aria-labelledby="fachsemester_label">
                <? for ($i = 0; $i <= 50; $i += 1): ?>
                    <option <? if ($i == $details['semester']) echo 'selected'; ?>><?= $i ?></option>
                <? endfor; ?>
                </select>
            </td>
            <td style="text-align:center">
                <input type="checkbox" aria-labelledby="austragen_label" name="fach_abschluss_delete[<?= $details['fach_id'] ?>]" value="<?= $details['abschluss_id'] ?>">
            </td>
        <? else: ?>
            <td><?= htmlReady($details['semester']) ?></td>
            <td>
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
                    <select name="new_studiengang" id="new_studiengang" aria-label="<?= _('-- Bitte Fach auswählen --')?>">
                        <option selected value="none"><?= _('-- Bitte Fach auswählen --')?></option>
                        <? foreach ($faecher as $fach) :?>
                            <?= sprintf('<option value="%s">%s</option>', $fach->id, htmlReady(my_substr($fach->name, 0, 50)));?>
                        <? endforeach?>
                    </select>

                    <a name="abschluss"></a>
                    <select name="new_abschluss" id="new_abschluss" aria-label="<?= _('-- Bitte Abschluss auswählen --')?>">
                        <option selected value="none"><?= _('-- Bitte Abschluss auswählen --')?></option>
                        <? foreach ($abschluesse as $abschluss) :?>
                            <?= sprintf('<option value="%s">%s</option>' . "\n", $abschluss->id, htmlReady(my_substr($abschluss->name, 0, 50)));?>
                        <? endforeach?>
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
                         .'markieren Sie die entsprechenden Felder in der oberen Tabelle.') ?>
                    <?= _('Mit einem Klick auf <b>Übernehmen</b> werden die gewählten Änderungen durchgeführt.') ?><br>
                    <br>
                    <?= Button::create(_('Übernehmen'), 'store_sg', array('title' => _('Änderungen übernehmen'))) ?>
                </p>
            <? else: ?>
                <?= _('Die Informationen zu Ihrem Studiengang werden vom System verwaltet, '
                     .'und können daher von Ihnen nicht geändert werden.') ?>
            <? endif; ?>
            </td>
        </tr>
    </tfoot>
</table>
<? if ($allow_change['sg']): ?>
</form>
<? endif; ?>
