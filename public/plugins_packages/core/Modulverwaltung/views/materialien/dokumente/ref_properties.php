<? use Studip\Button, Studip\LinkButton; ?>
<?= $this->controller->renderMessages() ?>
<? $perm = MvvPerm::get($relation); ?>
<form data-dialog="close" class="default mvv-form">
    <fieldset>
        <legend><?= _('Anmerkungen/Kommentare') ?></legend>
        <label>
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>" style="vertical-align: top;">
            <textarea <?= $perm->disable('kommentar') ?>cols="60" rows="5" id="dokument_kommentar_<?= $dokument->getId() ?>" name="dokumente_properties[<?= $dokument->getId() ?>][kommentar]" class="add_toolbar resizable ui-resizable mvv-ref-properties"><?= htmlReady($relation->kommentar) ?></textarea>
        </label>
        <label>
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>" style="vertical-align: top;">
            <textarea <?= $perm->disable('kommentar_en') ?>cols="60" rows="5" id="dokument_kommentar_en_<?= $dokument->getId() ?>" name="dokumente_properties[<?= $dokument->getId() ?>][kommentar_en]" class="add_toolbar resizable ui-resizable mvv-ref-properties"><?= htmlReady($relation->kommentar_en) ?></textarea>
        </label>
        <?= _('Die Änderungen werden erst gespeichert, wenn das Hauptformular gespeichert wurde!') ?>
    </fieldset>
    <footer data-dialog-button>
        <?= LinkButton::createAccept(_('übernehmen'), '#dokumente_' . $dokument->getId(), array('title' => _('Änderungen übernehmen'))) ?>
        <?= LinkButton::createCancel(_('abbrechen'), '', array('title' => _('Bearbeitung abbrechen'))) ?>
    </footer>
</form>