<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<form action="<?= $controller->url_for('admin/plugin/search') ?>" method="post" style="float: right;">
    <?= CSRFProtection::tokenTag() ?>
    <?= _('Suche nach Plugins:') ?>
    <input name="search" type="text" size="20" value="<?= htmlReady($search) ?>">
    <?= Button::create(_('Suchen'), 'suchen', ['title' => _('Suche starten')])?>
    &nbsp;
    <?= LinkButton::create(_('Zurücksetzen'), $controller->url_for('admin/plugin/search'), array('title' => _('Suche zurücksetzen')))?>
</form>


<? if (empty($search_results)): ?>
    <?= MessageBox::info(_('Es wurden keine Plugins gefunden.')) ?>
<? else: ?>
    <table class="default">
        <caption>
        <? if ($search === null): ?>
            <?= _('Empfohlene Plugins') ?>
        <? else: ?>
            <?= _('Suchergebnisse') ?>
        <? endif ?>
        </caption>
        <thead>
            <tr>
                <th class="plugin_image"><?= _('Bild')?></th>
                <th><?= _('Name und Beschreibung')?></th>
                <th><?= _('Version') ?></th>
                <th><?= _('Bewertung') ?></th>
                <th class="plugin_install actions"><?= _('Installieren') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($search_results as $name => $plugin): ?>
            <tr>
                <td class="plugin_image">
                    <? if ($plugin['image']): ?>
                        <? if ($plugin['plugin_url']): ?>
                            <a href="<?= htmlReady($plugin['plugin_url']) ?>" target="_blank">
                                <img src="<?= htmlReady($plugin['image']) ?>" class="plugin_preview">
                            </a>
                        <? else: ?>
                            <img src="<?= htmlReady($plugin['image']) ?>" class="plugin_preview">
                        <? endif ?>
                    <? endif ?>
                </td>
                <td>
                <? if ($plugin['plugin_url']): ?>
                    <a href="<?= htmlReady($plugin['plugin_url']) ?>" target="_blank">
                        <strong><?= htmlReady($name) ?></strong>
                    </a>
                <? else: ?>
                    <strong><?= htmlReady($name) ?></strong>
                <? endif ?>
                    <p>
                        <?= htmlReady($plugin['description']) ?>
                    </p>
                </td>
                <td>
                    <?= htmlReady($plugin['version']) ?>
                </td>
                <td class="plugin_score">
                <? for ($i = 0; $i < $plugin['score']; ++$i): ?>
                    <?= Icon::create('star', 'inactive')->asImg() ?>
                <? endfor ?>
                </td>
                <td class="plugin_install actions">
                    <form action="<?= $controller->url_for('admin/plugin/install') ?>" method="post">
                        <?= CSRFProtection::tokenTag() ?>
                        <input type="hidden" name="plugin_url" value="<?= htmlReady($plugin['url']) ?>">
                        <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
                        <?= Icon::create('install', 'clickable', ['title' => _('Plugin installieren')])->asInput(["type" => "image", "class" => "middle", "name" => "install"]) ?>
                    </form>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
<? endif ?>
<? if ($unknown_plugins) : ?>
    <table class="default">
        <caption>
            <?= _('Im Pluginverzeichnis vorhandene Plugins registrieren') ?>
        </caption>
        <thead>
            <tr>
                <th><?= _('Name')?></th>
                <th><?= _('Pluginklasse')?></th>
                <th><?= _('Version') ?></th>
                <th><?= _('Ursprung') ?></th>
                <th class="plugin_install"><?= _('Registrieren') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($unknown_plugins as $n => $plugin): ?>
            <tr>
                <td><?= htmlReady($plugin['pluginname']) ?></td>
                <td><?= htmlReady($plugin['pluginclassname']) ?></td>
                <td><?= htmlReady($plugin['version']) ?></td>
                <td><?= htmlReady($plugin['origin']) ?></td>
                <td class="plugin_install">
                    <form action="<?= $controller->url_for('admin/plugin/register/' . $n) ?>" method="post">
                        <?= CSRFProtection::tokenTag() ?>
                        <?= Icon::create('install', 'clickable', ['title' => _('Plugin registrieren')])->asInput([
                            'type'  => 'image',
                            'class' => 'middle',
                            'name'  => 'install',
                        ]) ?>
                    </form>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
<? endif; ?>

<? if (Config::get()->PLUGINS_UPLOAD_ENABLE): ?>
    <h3>
        <?= _('Plugin als ZIP-Datei hochladen') ?>
    </h3>

    <form action="<?= $controller->url_for('admin/plugin/install') ?>" enctype="multipart/form-data" method="post">
        <?= CSRFProtection::tokenTag() ?>
        <?= _('Plugin-Datei:') ?>
        <input name="upload_file" type="file" size="40">
        <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">

        <?= Button::create(_('Hinzufügen'), 'hinzufuegen', ['title' => _('neues Plugin installieren')])?>
    </form>
<? endif ?>
