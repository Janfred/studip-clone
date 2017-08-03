<? foreach ($tree[$node] as $current) : ?>
<li>
    <? if ($current['class'] != 'Modulteil' && $current['id'] != 'root') : ?>
        <input id="<?= htmlReady($current['id'] . $id_sfx->c) ?>" type="checkbox"<?= $current['class'] != 'StgteilabschnittModul' ? 'checked' : ''?>>
    <? endif; ?>
        <label for="<?= htmlReady($current['id'] . $id_sfx->c++) ?>"></label>
    <? if ($current['class'] == 'StgteilabschnittModul') : ?>
        <? $modul_id = end(explode('_', $current['id'])); ?>
        <a data-dialog title="<?= htmlReady($current['name']) ?>" href="<?= URLHelper::getLink('plugins.php/mvvplugin/shared/modul/overview/' . $modul_id) ?>">
            <?= htmlReady($current['name']) ?>
        </a>
        <a data-dialog title="<?= htmlReady($current['name']) ?>" href="<?= URLHelper::getLink('plugins.php/mvvplugin/shared/modul/description/' . $modul_id) ?>">
        <?= Icon::create('log', 'clickable', ['title' => _('Modulbeschreibung')]); ?>
        </a>
    <? else : ?>
        <?= htmlReady($current['name']) ?>
    <? endif; ?>
    <? if ($current['class'] != 'Modulteil') : ?>
        <ul>
            <?= $this->render_partial('shared/mvv_tree.php', array('tree' => $tree, 'node' => $current['id'])) ?>
        </ul>
    <? endif; ?>
</li>
<? endforeach; ?>
