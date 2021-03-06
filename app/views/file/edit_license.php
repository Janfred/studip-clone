<form action="<?= $controller->link_for('file/edit_license') ?>" method="post" class="default" data-dialog>
<? foreach ($file_refs as $file_ref) : ?>
    <input type="hidden" name="file_refs[]" value="<?= htmlReady($file_ref->id) ?>">
<? endforeach ?>

    <?= $this->render_partial('file/_terms_of_use_select.php', [
        'content_terms_of_use_entries' => $licenses,
        'selected_terms_of_use_id'     => $file_ref->content_terms_of_use_id
    ]) ?>

    <div data-dialog-button>
        <?= Studip\Button::create(_('Speichern')) ?>
        <?= Studip\LinkButton::create(
            _('Abbrechen'),
            $controller->url_for((in_array($folder->range_type, ['course', 'institute']) ? $folder->range_type . '/' : '') . 'files/index/' . $folder->id)
        ) ?>
    </div>
</form>
