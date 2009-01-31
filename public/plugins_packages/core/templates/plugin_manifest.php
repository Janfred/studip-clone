<? StudIPTemplateEngine::makeContentHeadline(_('Plugin-Details')) ?>

<table>
    <tr>
        <td>Name:</td>
        <td><?= $plugin->getPluginname() ?></td>
    </tr>
    <tr>
        <td>Name (original):</td>
        <td><?= $plugininfos['pluginname'] ?></td>
    </tr>
    <tr>
        <td>Klasse:</td>
        <td><?= $plugin->getPluginclassname() ?></td>
    </tr>
    <tr>
        <td>Origin:</td>
        <td><?= $plugininfos['origin'] ?></td>
    </tr>
    <tr>
        <td>Version:</td>
        <td><?= $plugininfos['version'] ?></td>
    </tr>
    <tr>
        <td>
            <a href="<?= PluginEngine::getLink($admin_plugin) ?>">
                <?= makeButton('zurueck', 'img', _('zurück zur Plugin-Verwaltung')) ?>
            </a>
        </td>
    </tr>
</table>

<?
$infobox_content = array(
    array(
        'kategorie' => _('Hinweise:'),
        'eintrag'   => array(
            array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Hier finden Sie Informationen zum Plugin.')
            )
        )
    )
);

$infobox = array('picture' => 'modules.jpg', 'content' => $infobox_content);
?>
