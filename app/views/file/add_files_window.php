<?php
$options = [];
if (Request::get('to_plugin')) {
    $options['to_plugin'] = Request::get('to_plugin');
}
if (Request::get('from_plugin')) {
    $options['from_plugin'] = Request::get('from_plugin');
}
if (Request::get('to_folder_id')) {
    $options['to_folder_id'] = Request::get('to_folder_id');
}
if ($folder_id) {
    $options['to_folder_id'] = $folder_id;
}

?>
<div class="files_source_selector" data-folder_id="<?= htmlReady($folder_id) ?>" <? if ($hidden) echo ' style="display: none;"'; ?>>
    <?= _('Quelle auswählen') ?>
    <div class="file_select_possibilities">
        <a href="#" onclick="jQuery('.file_selector input[type=file]').first().click(); return false;">
            <?= Icon::create('computer', Icon::ROLE_CLICKABLE)->asImg(50) ?>
            <?= _('Mein Computer') ?>
        </a>
        <a href="<?= $controller->link_for('file/add_url/' . $folder_id, $options) ?>" data-dialog>
            <?= Icon::create('globe', Icon::ROLE_CLICKABLE)->asImg(50) ?>
            <?= _('Webadresse') ?>
        </a>
        <a href="<?= $controller->link_for('file/choose_file/' . Folder::findTopFolder($GLOBALS['user']->id)->getId(), $options) ?>" data-dialog>
            <?= Icon::create('files', Icon::ROLE_CLICKABLE)->asImg(50) ?>
            <?= _('Meine Dateien') ?>
        </a>
        <a href="<?= $controller->link_for('file/choose_file_from_course/' . htmlReady($folder_id), $options) ?>" data-dialog>
            <?= Icon::create('seminar', Icon::ROLE_CLICKABLE)->asImg(50) ?>
            <?= _('Meine Veranstaltungen') ?>
        </a>
    <? foreach (PluginManager::getInstance()->getPlugins('FilesystemPlugin') as $plugin) : ?>
        <? if ($plugin->isSource()) : ?>
            <? $nav = $plugin->getFileSelectNavigation() ?>
            <? if ($nav): ?>
                <a href="<?= $controller->link_for('file/choose_file/', array_merge($options, ['from_plugin' => get_class($plugin)])) ?>" data-dialog>
                    <?= $nav->getImage()->asImg(50) ?>
                    <?= htmlReady($nav->getTitle()) ?>
                </a>
            <? endif; ?>
        <? endif; ?>
    <? endforeach; ?>
    </div>

    <form style="display: none;" class="file_selector">
        <script>
            STUDIP.Files.uploadConstraints = {
                filesize: <?= (int) $GLOBALS['UPLOAD_TYPES']['default']['file_sizes'][$GLOBALS['perm']->get_perm()] ?>,
                type: '<?= $GLOBALS['UPLOAD_TYPES']['default']['type'] ?>',
                file_types: <?= studip_json_encode($GLOBALS['UPLOAD_TYPES']['default']['file_types']) ?>
            };
        </script>
        <input type="file" name="files[]" multiple onchange="STUDIP.Files.upload(this.files);">
    </form>
</div>

<div style="display: none;">
    <?= _('Soll die hochgeladene ZIP-Datei entpackt werden?') ?>
</div>
