<form class="default" action="<?= $controller->url_for('course/grouping/move_members', $source_id) ?>" method="post">
    <header>
        <h1><?= _('Wohin sollen die gewählten Personen verschoben werden?') ?></h1>
    </header>

    <section>
    <? foreach ($targets as $one) : ?>
        <label>
            <input type="radio" name="target" value="<?= $one->id ?>">
            <?= htmlReady($one->getFullname()) ?>
        </label>
    <? endforeach ?>
    </section>

    <footer data-dialog-button>
    <? foreach ($users as $user) : ?>
        <input type="hidden" name="users[]" value="<?= $user ?>">
    <? endforeach ?>
        <?= Studip\Button::createAccept(_('Personen verschieben'), 'move') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), 'cancel', ['data-dialog' => 'close']) ?>
    </footer>
</form>
