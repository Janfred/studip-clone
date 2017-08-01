<form method="post" class="default" action="<?=$controller->url_for('start/change_mail_address')?>" data-dialog="size=auto">
    <?= CSRFProtection::tokenTag()?>
    <label>
        <span class="required"><?= _('E-Mail:') ?></span>
        <input required type="email" name="email1" id="email1"
               value="<?= htmlReady($email) ?>"
            <? if ($restricted) echo 'disabled'; ?>>
    </label>
    <label>
        <span class="required"><?= _('E-Mail Wiederholung:') ?></span>
        <input required type="email" name="email2" id="email2"
               value=""
               data-must-equal="#email1"
            <? if ($restricted) echo 'disabled'; ?>>
    </label>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'))?>
    </footer>
</form>