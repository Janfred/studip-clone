<? if ($admin || $votes || $evaluations): ?>
<section class="contentbox">
    <header>
        <h1>
            <?= Icon::create('vote', 'info')->asImg(); ?>
            <?= _('Evaluationen') ?>
        </h1>
        <nav>
        <? if ($admin): ?>
            <a href="<?= URLHelper::getLink('admin_evaluation.php', array('rangeID' => 'overview')) ?>">
                <?= Icon::create('admin', 'clickable')->asImg(); ?>
            </a>
        <? endif; ?>
        </nav>
    </header>

    <? if (!$votes && !$evaluations): ?>
        <section>
            <?= _('Keine Evaluationen vorhanden. Um neue Umfragen zu erstellen, klicken Sie rechts auf die Zahnräder.') ?>
        </section>
    <? else: ?>
        <? foreach ($evaluations as $evaluation): ?>
            <?= $this->render_partial('evaluation/_evaluation.php', array('evaluation' => $evaluation)); ?>
        <? endforeach; ?>
    <? endif; ?>
        <footer>
            <? if (Request::get('show_expired')): ?>
                <a href="<?= URLHelper::getLink('', array('show_expired' => 0)) ?>"><?= _('Abgelaufene Evaluationen ausblenden') ?></a>
            <? else: ?>
                <a href="<?= URLHelper::getLink('', array('show_expired' => 1)) ?>"><?= _('Abgelaufene Evaluationen einblenden') ?></a>
            <? endif; ?>
        </footer>
</section>
<? endif; ?>