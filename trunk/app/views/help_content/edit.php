<? use Studip\Button, Studip\LinkButton; ?>

<form id="edit_help_content_form" class="default"
      action="<?= $controller->url_for('help_content/edit/' . $help_content_id) ?>"
      method="POST">
    <?= CSRFProtection::tokenTag(); ?>
    <fieldset>
        <? if ($help_content->route) : ?>
            <legend><?= sprintf(_('Seite %s'), $help_content->route) ?></legend>
            <input type="hidden" name="help_content_route" value="<?= $help_content->route ?>">
        <? else : ?>
            <legend><?= _('Neuer Hilfe-Text') ?></legend>
            <label for="help_content_route">
                <?= _('Seite:') ?>
                <input type="text" size="60" maxlength="255" name="help_content_route"
                       value=""
                       placeholder="<?= _('Bitte geben Sie eine Route für den Hilfe-Text an') ?>">
            </label>
        <? endif ?>
        <? if ($GLOBALS['perm']->have_perm('root')) : ?>
            <label for="help_content_language">
                <span class="required"><?= _('Sprache des Textes:') ?></span>
                <select name="help_content_language">
                    <? foreach ($GLOBALS['INSTALLED_LANGUAGES'] as $key => $language) : ?>
                        <option value="<?= substr($key, 0, 2) ?>"<?= ($help_content->language == substr($key, 0, 2)) ? ' selected' : '' ?>><?= $language['name'] ?></option>
                    <? endforeach ?>
                </select>
            </label>
        <? endif ?>
        <label for="help_content_content">
            <?= _('Hilfe-Text:') ?>
            <textarea cols="60" rows="5" name="help_content_content"
                      placeholder="<?= _('Bitte geben Sie den Text ein') ?>"><?= $help_content->content ? htmlReady($help_content->content) : '' ?></textarea>
        </label>

        <footer data-dialog-button="1">
            <? if ($via_ajax): ?>
                <?= Button::create(_('Speichern'), 'save_help_content', ['data-dialog' => '']) ?>
            <? else: ?>
                <?= Button::createAccept(_('Speichern'), 'save_help_content') ?>
                <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('help_content/admin_overview'), []) ?>
            <? endif; ?>
        </footer>
    </fieldset>
</form>
