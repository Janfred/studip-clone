<h2 class="hide-in-dialog"><?= _('Neues Widgets zur Startseite hinzufügen') ?></h2>
<form action="<?= $controller->url_for('start/add') ?>" method="post">
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    <input type="hidden" name="position" value="0">

    <ul class="addclip-widgets">
    <? foreach ($widgets as $widget): ?>
        <? $metadata = $widget->getMetadata(); ?>
        <li>
            <label>
                <input type="checkbox" name="widget_id[]" value="<?= $widget->getPluginId() ?>">
                <?= htmlReady($widget->getPluginName()) ?>
            </label>
        <? if ($metadata['description']): ?>
            <p><?= formatReady($metadata['description']) ?></p>
        <? endif; ?>
        </li>
    <? endforeach; ?>
    </ul>
    
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Hinzufügen')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('start')) ?>
    </div>
</form>
