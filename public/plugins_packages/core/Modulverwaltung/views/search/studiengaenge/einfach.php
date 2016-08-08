<?= $this->render_partial('search/breadcrumb') ?>
<h2><?= htmlReady($studiengangName) ?></h2>
<h3><?= _('Ausprägungen') ?></h3>
<ul class="mvv-result-list">
<? foreach ($data as $fach_id => $fach) : ?>
    <? $cycle_class = $i++ % 2 ? 'even' : 'odd'; ?>
    <li class="<?= $cycle_class ?>">
        <a href="<?= $verlauf_url.$fach_id?>"><?= htmlReady($fach) ?></a>
    </li>
<? endforeach; ?>
</ul>