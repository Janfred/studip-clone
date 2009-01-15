<? StudIPTemplateEngine::makeContentHeadline(_('Default-Aktivierung')) ?>

<? if (isset($message['msg'])): ?>
    <? StudIPTemplateEngine::showSuccessMessage($message['msg']) ?>
<? elseif (isset($message['err'])): ?>
    <? StudIPTemplateEngine::showErrorMessage($message['err']) ?>
<? endif ?>

<p>
    <?= _('Wählen Sie die Einrichtungen, in deren Veranstaltungen das Plugin automatisch aktiviert sein soll:') ?>
</p>

<form action="" method="post">
    <input type="hidden" name="selected" value="1">
    <select name="selected_inst[]" multiple size="20">
        <? foreach ($institutes as $institute): ?>
            <option value="<?= $institute->getId() ?>" <?= in_array($institute->getId(), $selected_inst) ? 'selected' : '' ?>>
                <?= htmlReady($institute->getName()) ?>
            </option>

            <? foreach ($institute->getAllChildInstitutes() as $child): ?>
                <option value="<?= $child->getId() ?>" <?= in_array($child->getId(), $selected_inst) ? 'selected' : '' ?>>
                    &nbsp;&nbsp;&nbsp;
                    <?= htmlReady($child->getName()) ?>
                </option>
            <? endforeach ?>
        <? endforeach ?>
    </select>
    <p>
        <label>
            <input type="checkbox" name="nodefault" value="1">
            <?= _('keine Voreinstellung wählen') ?>
        </label>
    </p>
    <p>
        <?= makeButton('uebernehmen', 'input', _('Einstellungen speichern')) ?>
        &nbsp;
        <a href="<?= PluginEngine::getLink($admin_plugin) ?>">
            <?= makeButton('zurueck', 'img',  _('Zurück zur Plugin-Verwaltung')) ?>
        </a>
    </p>
</form>

<?
$infobox = array(
    array(
        'kategorie' => _('Hinweise:'),
        'eintrag'   => array(
            array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Wählen Sie die Institute, in deren Veranstaltungen das Plugin standardmäßig eingeschaltet werden soll.')
            ), array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Eine Mehrfachauswahl ist durch Drücken der Strg-Taste möglich.')
            )
        )
    )
);

StudIPTemplateEngine::createInfoBoxTableCell();
print_infobox($infobox, 'modules.jpg');
StudIPTemplateEngine::endInfoBoxTableCell();
?>
