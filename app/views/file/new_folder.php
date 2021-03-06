<form method="post" class="default" data-dialog="reload-on-close" action="<?= $controller->url_for('file/new_folder/' . $parent_folder_id) ?>">
    <?= CSRFProtection::tokenTag() ?>    
    <?= $this->render_partial('file/new_edit_folder_form.php', [
        'name'            => $name,
        'description'     => $description,
        'folder_types'    => $folder_types,
        'new_folder_form' => true,
    ]) ?>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Erstellen'), 'create') ?>
    </div>
</form>
