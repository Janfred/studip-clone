<? if (isset($flash['message'])): ?>
    <?= MessageBox::success($flash['message']) ?>
<? endif ?>

<h3>
    <?= _('Default-Aktivierung') ?>: <?= htmlspecialchars($plugin_name) ?>
</h3>

<p>
    <?= _('Wählen Sie die Einrichtungen, in deren Veranstaltungen das Plugin automatisch aktiviert sein soll:') ?>
</p>

<form action="<?= $controller->url_for('plugin_admin/save_default_activation', $plugin_id) ?>" method="post">
    <input type="hidden" name="ticket" value="<?= get_ticket() ?>">
    <select name="selected_inst[]" multiple size="20">
        <? foreach ($institutes as $id => $institute): ?>
            <option value="<?= $id ?>" <?= in_array($id, $selected_inst) ? 'selected' : '' ?>>
                <?= htmlReady($institute['name']) ?>
            </option>

            <? if (isset($institute['children'])): ?>
                <? foreach ($institute['children'] as $id => $child): ?>
                    <option style="padding-left: 1em;" value="<?= $id ?>" <?= in_array($id, $selected_inst) ? 'selected' : '' ?>>
                        <?= htmlReady($child['name']) ?>
                    </option>
                <? endforeach ?>
            <? endif ?>
        <? endforeach ?>
    </select>
    <p>
        <label>
            <input type="checkbox" name="nodefault" value="1">
            <?= _('keine Einrichtungen wählen') ?>
        </label>
    </p>
    <p>
        <?= makeButton('uebernehmen', 'input', _('Einstellungen speichern'), 'save') ?>
        &nbsp;
        <a href="<?= $controller->url_for('plugin_admin') ?>">
            <?= makeButton('zurueck', 'img',  _('Zurück zur Plugin-Verwaltung')) ?>
        </a>
    </p>
</form>

<?
$infobox_content = array(
    array(
        'kategorie' => _('Hinweise:'),
        'eintrag'   => array(
            array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Wählen Sie die Einrichtungen, in deren Veranstaltungen das Plugin standardmäßig eingeschaltet werden soll.')
            ), array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Eine Mehrfachauswahl ist durch Drücken der Strg-Taste möglich.')
            )
        )
    )
);

$infobox = array('picture' => 'modules.jpg', 'content' => $infobox_content);
?>
