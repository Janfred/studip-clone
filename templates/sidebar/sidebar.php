<div id="layout-sidebar">
    <section class="sidebar">
<? if ($image): ?>
        <div class="sidebar-image <? if ($avatar) echo 'sidebar-image-with-context'; ?>">
            <?= Assets::img($image, array('alt' => '')) ?>
        <? if ($avatar) : ?>
            <div class="sidebar-context">
                <a href="<?= htmlReady($avatar->getURL(Avatar::ORIGINAL)) ?>" data-lightbox="sidebar-avatar" data-title="<?= htmlReady(PageLayout::getTitle()) ?>">
                    <?= $avatar->getImageTag(Avatar::MEDIUM) ?>
                </a>
            </div>
        <? endif ?>
        </div>
<? endif; ?>

    <? foreach ($widgets as $index => $widget): ?>
        <?= $widget->render(array('base_class' => 'sidebar')) ?>
    <? endforeach; ?>
    </section>
</div>
