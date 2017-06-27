<form class="default" action="<?= $controller->url_for('course/wizard/step') ?>" method="post" data-dialog>
    <header>
        <h1>
            <?= _('Anlegen von mehreren Unterveranstaltungen') ?>
        </h1>
    </header>
    <section>
        <label class="required">
            <?= _('Anzahl anzulegender Veranstaltungen') ?>
        </label>
        <input type="number" name="batchcreate[number]" value="5" min="1" required>
    </section>
    <section>
        <span class="required">
            <?= _('Nummerierung/Kennzeichnung anhängen an') ?>
        </span>
        <label>
            <input type="radio" name="batchcreate[add_number_to]" value="name" required>
            <?= _('Name der Veranstaltung') ?>
        </label>
        <label>
            <input type="radio" name="batchcreate[add_number_to]" value="number" required>
            <?= _('Veranstaltungsnummer') ?>
        </label>
    </section>
    <section>
        <span class="required">
            <?= _('Nummerierung/Kennzeichnung durch') ?>
        </span>
        <label>
            <input type="radio" name="batchcreate[numbering]" value="number" required>
            <?= _('Zahlen (1, 2, 3, ...)') ?>
        </label>
        <label>
            <input type="radio" name="batchcreate[numbering]" value="letters" required>
            <?= _('Buchstaben (A, B, C, ...)') ?>
        </label>
    </section>

    <footer data-dialog-button>
        <input type="hidden" name="batchcreate[parent]" value="<?= $course->id ?>">
        <?= Studip\Button::createAccept(_('Weiter zum Anlegeassistenten'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for('course/grouping/children')
        ) ?>
    </footer>
</form>
