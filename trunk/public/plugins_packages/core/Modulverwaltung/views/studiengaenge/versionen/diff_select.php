<?= $controller->renderMessages() ?>
<?= implode(PageLayout::getMessages()) ?>
<h3><?= htmlReady($version->getDisplayName()) ?></h3>
<div>
    <?= _('Vergleichen mit folgender Version:') ?>
</div>
<div>
    <h4><?= _('Versionsuche:') ?></h4>
    <form data-dialog="size=auto" class="mvv-new-tab" action="<?= $controller->url_for('/diff') ?>" method="post">
        <?= CSRFProtection::tokenTag() ?>
        <?= $search_version ?>
        <input type="hidden" name="new_id" value="<?= $version->id ?>">
        <input name="version_diff" class="text-top mvv-submit" type="image" title="<?= _('Version suchen') ?>" src="<?= Icon::create('accept', 'clickable')->asImagePath(); ?>">
    </form>
</div>
