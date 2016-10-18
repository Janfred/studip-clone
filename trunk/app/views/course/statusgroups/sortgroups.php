<div class="ordering" title="<?= _('Gruppenreihenfolge ändern') ?>">
    <div class="nestable" data-max-depth="1">
        <? if ($groups): ?>
            <ol class="dd-list">
                <? foreach ($groups as $group): ?>
                    <li class="dd-item" data-id="<?= $group->id ?>">
                        <div class="dd-handle"><?= formatReady($group->name) ?></div>
                    </li>
                <? endforeach; ?>
            </ol>
        <? endif; ?>
    </div>
</div>

<form class="default" id="order_form" action="<?= $controller->url_for('') ?>" method="POST">
    <input type="hidden" name="ordering" id="ordering">

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'order') ?>
    </footer>
</form>