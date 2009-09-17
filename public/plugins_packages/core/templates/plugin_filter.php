<form action="<? PluginEngine::getLink($admin_plugin) ?>" method="post">
    <select name="plugin_filter" onchange="this.form.submit();">
        <option value="">
            <?= _('alle anzeigen') ?>
        </option>
        <? foreach ($plugin_types as $type): ?>
            <option value="<?= $type ?>" <?= $type == $plugin_filter ? 'selected' : '' ?>>
                <?= strlen($type) > 20 ? substr($type, 0, 17) . '...' : $type ?>
            </option>
        <? endforeach ?>
    </select>

    <noscript>
        <input type="image" name="show" src="<?= Assets::image_path('GruenerHakenButton.png') ?>">
    </noscript>
</form>
