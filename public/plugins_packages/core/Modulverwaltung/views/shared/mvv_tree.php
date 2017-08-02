<? foreach ($tree[$node] as $current) : ?>
<li>
    <? if ($current['class'] != 'Modulteil' && $current['id'] != 'root') : ?>
        <input id='<?= htmlReady($current['id']) ?>' type='checkbox'>
    <? endif; ?>
        <label for='<?= htmlReady($current['id']) ?>'></label>
    <? if ($current['class'] == 'StgteilabschnittModul') : ?>
        <? $modul_id = end(explode('_', $current['id'])); ?>
        <a data-dialog="title='<?= $current['name'] ?>'" href="<?= URLHelper::getLink('plugins.php/mvvplugin/shared/modul/overview/' . $modul_id) ?>">
            <?= htmlReady($current['name']) ?>
        </a>
        <a data-dialog="title='<?= $current['name'] ?>'" href="<?= URLHelper::getLink('plugins.php/mvvplugin/shared/modul/description/' . $modul_id) ?>">
        <?= Icon::create('info-circle', 'clickable', ['title' => _('Vollständige Modulbeschreibung')]); ?>
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