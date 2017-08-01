<?= $controller->renderMessages() ?>
<?= implode(PageLayout::getMessages()) ?>
<h1><?= htmlReady($modul->getDisplayName()) ?></h1>
<div>
    <?= _('Vergleichen mit folgendem Modul:') ?>
</div>
<div>
    <h4><?= _('Modulsuche:') ?></h4>
    <form data-dialog class="mvv-new-tab" action="<?= $controller->link_for('/diff') ?>" method="post">
        <?= CSRFProtection::tokenTag() ?>
        <?= $search_modul ?>
        <input type="hidden" name="new_id" value="<?= $modul->getId() ?>">
        <input name="modul_diff" class="text-top mvv-submit" type="image" title="<?= _('Modul suchen') ?>" src="<?= Icon::create('accept', 'clickable')->asImagePath(); ?>">
    </form>
</div>
<? if ($quelle) : ?>
<div>
    <h4><?= _('Dieses Modul ist eine Novellierung des Moduls:') ?></h4>
    <a class="mvv-new-tab" href="<?= $controller->link_for('/diff', $modul->id, $quelle->id) ?>"><?= htmlReady($quelle->getDisplayName()) ?></a>
</div>
<? endif; ?>
<? if ($variante) : ?>
<div>
    <h4><?= _('Dieses Modul ist eine Variante von:') ?></h4>
    <a class="mvv-new-tab" href="<?= $controller->link_for('/diff', $modul->id, $variante->id) ?>"><?= htmlReady($variante->getDisplayName()) ?></a>
<? endif; ?>
</div>
<? $variants = $modul->getVariants(); ?>
<? if (sizeof($variants)) : ?>
<div>
    <h4><?= _('Folgende Module sind Varianten dieses Moduls:') ?></h4>
    <ul>
    <? foreach ($variants as $variant) : ?>
        <li>
            <a href="<?= $controller->link_for('/diff', $modul->id, $variant->id) ?>">
            <?= htmlReady($variant->getDisplayName()) ?>
            </a>
        </li>
    <? endforeach; ?>
    </ul>
</div>
<? endif; ?>
