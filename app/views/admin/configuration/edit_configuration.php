<h2 class="hide-in-dialog"><?= _('Bearbeiten von Konfigurationsparameter') ?></h2>
<form action="<?= $controller->url_for('admin/configuration/edit_configuration?id=' . $config['field']) ?>" method="post" data-dialog class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= htmlReady($config['field']) ?>
        </legend>
        
        <?= $this->render_partial('admin/configuration/type-edit.php', $config) ?>
        
        <label for="comment">
            <?= _('Kommentar') ?>
            <textarea cols="80" rows="2" name="comment" id="comment"><?= htmlReady($config['comment']) ?></textarea>
        </label>
        <label>
            <?= _('Standard') ?>

            <? if ($config['is_default'] === '1'): ?>
                <?= Icon::create('checkbox-checked', 'info', ['title' => _('Ja')])->asImg() ?>
            <? elseif ($config['is_default'] === '0'): ?>
                <?= Icon::create('checkbox-unchecked', 'info', ['title' => _('Nein')])->asImg() ?>
            <? elseif ($config['is_default'] === null): ?>
                <em>- <?= _('kein Eintrag vorhanden') ?> -</em>
            <? endif; ?>
        </label>
        <label>
            <?= _('Typ') ?>
            <input name="type" type="text" readonly value="<?= htmlReady($config['type']) ?>">
        </label>
        <label>
            <?= _('Bereich') ?>
            <input type="text" name="range" readonly value="<?= htmlReady($config['range']) ?>">
        </label>
        <label>
            <?= _('Kategorie') ?>
            <select name= "section" onchange="$(this).next('input').val( $(this).val() );">
                <? foreach (array_keys($allconfigs) as $section): ?>
                    <option <? if ($config['section'] === $section) echo 'selected'; ?>>
                        <?= htmlReady($section) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>
        <label>
            (<em><?= _('Bitte die neue Kategorie eingeben')?></em>)
            <input type="text" name="section_new" id="section">
        </label>
    </fieldset>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Übernehmen')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
                $controller->url_for('admin/configuration/configuration/' . $config['section'])) ?>
    </footer>
</form>