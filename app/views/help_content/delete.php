<? use Studip\Button, Studip\LinkButton; ?>
<div id="delete_help_content" class="delete_help_content">

<form id="delete_help_content_form" class="studip_form"
      action="<?= $controller>url_for('help_content/delete/' . $help_content_id) ?>"
      method="POST">
    <?=CSRFProtection::tokenTag(); ?>
    <fieldset>
        <input type="hidden" name="help_content_route" value="<?=$help_content->route?>">
        <legend><?= sprintf(_('Seite %s'), $help_content->route) ?></legend>
        <?= _('Hilfe-Text:') ?>
        <?= $help_content->content ? htmlReady($help_content->content) : '' ?>
        <div data-dialog-button>
            <?= CSRFProtection::tokenTag() ?>
            <? if ($via_ajax): ?>
                <?= Button::create(_('Löschen'), 'delete_help_content', array('data-dialog' => '')) ?>
            <? else: ?>
                <?= Button::create(_('Löschen'), 'delete_help_content') ?>
            <? endif; ?>
        </div>
    </fieldset>
</form>

</div>