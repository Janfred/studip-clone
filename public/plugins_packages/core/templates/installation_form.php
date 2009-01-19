<? if ($GLOBALS['PLUGINS_UPLOAD_ENABLE'] || count($installable) > 0): ?>
    <? StudIPTemplateEngine::makeContentHeadline(_('Installation neuer Plugins')) ?>

    <form action="<?= PluginEngine::getLink($admin_plugin, array(), 'installPlugin')?>" enctype="multipart/form-data" method="post">
        <? if ($GLOBALS['PLUGINS_UPLOAD_ENABLE']): ?>
            <input name="upload_file" type="file" size="50">
        <? else: ?>
            <table>
                <? foreach ($installable as $pluginfilename): ?>
                    <tr>
                        <td>
                            <label>
                                <input type="radio" name="pluginfilename" value="<?= htmlspecialchars($pluginfilename) ?>">
                                <?= htmlspecialchars($pluginfilename) ?>
                            </label>
                        </td>
                    </tr>
                <? endforeach ?>
            </table>
        <? endif ?>

        <?= makeButton('hinzufuegen', 'input' , _('neues Plugin installieren')) ?><br>
        <label>
            <input type="checkbox" name="update" value="force"><?= _('Aktualisieren, falls Plugin schon vorhanden.')?>
        </label>
    </form>
<? endif ?>
