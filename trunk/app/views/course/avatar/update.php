<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<? if (isset($error)) : ?>
    <?= MessageBox::error($error) ?>
<? endif ?>

<h1><?= _('Veranstaltungsbild hochladen') ?></h1>

<div style="float: left; padding: 0 1em 1em 0;">
    <?= $avatar->getImageTag(Avatar::NORMAL) ?>
</div>

<form enctype="multipart/form-data"
      action="<?= $controller->url_for('course/avatar/put/' . $course_id) ?>"
      method="post" style="float: left">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
    <label for="upload-input"><?= _("Wählen Sie ein Bild für die Veranstaltung:") ?></label>
    <input id="upload-input" name="avatar" type="file">

    <p class="quiet">
        <?= Icon::create('info-circle', 'inactive')->asImg(16, ["style" => 'vertical-align: middle;']) ?>
        <? printf(_('Die Bilddatei darf max. %s groß sein, es sind nur Dateien mit den Endungen %s, %s oder %s erlaubt!'),
                relsize(Avatar::MAX_FILE_SIZE),
                '<b>.jpg</b>', '<b>.png</b>', '<b>.gif</b>') ?>
    </p>

    <p>
        <?= Button::createAccept(_('Absenden')) ?>
        <span class="quiet">
            <?= _("oder") ?>
            <? if ($this->studygroup_mode) : ?>
                <?= LinkButton::createCancel(_('Abbrechen'), URLHelper::getURL('dispatch.php/course/studygroup/edit/' . $course_id)) ?>
            <? else : ?>
                <?= LinkButton::createCancel(_('Abbrechen'), URLHelper::getURL('dispatch.php/course/basicdata/view/' . $course_id)) ?>
            <? endif ?>
        </span>
    </p>
</form>