<? use Studip\Button, Studip\LinkButton;?>

<? if (!$xhr): ?>
<h1><?= PageLayout::getTitle() ?></h1>
<? endif; ?>

<form class="default" action="<?=$controller->url_for(sprintf('course/members/set_comment/%s', $user->user_id))?>" method="POST">
    <?= CSRFProtection::tokenTag() ?>
    <label><?= _('Bemerkung') ?></label>
    <textarea name="comment" aria-label="<?=_('Bemerkung')?>" rows="8" maxlength="255" id="comment"><?=htmlReady($comment)?></textarea>
    <div data-dialog-button>
        <?= Button::createAccept(_('Speichern'), 'save'); ?>
        <?= Button::createCancel(_('Abbrechen')); ?>
    </div>
</form>
