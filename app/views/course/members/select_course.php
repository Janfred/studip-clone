<form class="default" action="<?= $controller->url_for('course/members/select_course') ?>" method="post">
    <section>
        <label for="course_id">
            <?= _('Zielveranstaltung') ?>:
        </label>
        <?= $search ?>
    </section>
    <br>
    <section>
        <label>
            <?= _('Sollen die gewählten Personen in die Zielveranstaltung verschoben oder kopiert werden?') ?>
            <select name="move">
                <option value="1"><?= _('Verschieben') ?></option>
                <option value="0"><?= _('Kopieren') ?></option>
            </select>
        </label>
    </section>
    <?php foreach ($users as $u) : ?>
        <input type="hidden" name="users[]" value="<?= htmlReady($u) ?>"/>
    <?php endforeach ?>
    <?= CSRFProtection::tokenTag() ?>
    <br><br>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Abschicken'), 'submit') ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel', array('data-dialog' => 'close')) ?>
    </div>
</form>
