<form action="<?=$controller->url_for('admin/user/lock/' . $user->id, $params)?>" method="post" class="default">
    <?= CSRFProtection::tokenTag()?>
    <label>
        <span class="required"><?= _('Kommentar') ?>:</span>
        <input required class="user_form" name="lock_comment" type="text">
    </label>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Benuter sperren'))?>
    </footer>
</form>