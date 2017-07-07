<form class="default" action="<?= $controller->url_for('course/statusgroups/batch_create') ?>" method="post">
    <fieldset>
        <legend>
            <?= _('Wie sollen Gruppen angelegt werden?') ?>
        </legend>
        <label>
            <input type="radio" name="mode" value="numbering" checked onclick="$('.numbering-data').show();$('.course-data').hide();">
            <?= _('Erzeuge beliebig viele Gruppen mit Namenspräfix') ?>
        </label>
        <label>
            <input type="radio" name="mode" value="coursedata" onclick="$('.numbering-data').hide();$('.course-data').show();">
            <?= _('Lege Gruppen zu bestehenden Veranstaltungsdaten an') ?>
        </label>
    </fieldset>
    <fieldset>
        <legend>
            <?= _('Lege folgende Gruppen an') ?>
        </legend>
        <section class="numbering-data">
            <label class="required">
                <?= _('Anzahl anzulegender Gruppen') ?>
            </label>
            <input type="number" name="number" value="5" min="1">
        </section>
        <section class="numbering-data">
            <label>
                <?= _('Beginne Nummerierung bei') ?>
            </label>
            <input type="number" name="startnumber" value="1" min="0">
        </section>
        <section class="numbering-data">
            <label class="required">
                <?= _('Namenspräfix') ?>
            </label>
            <input type="text" name="prefix" maxlength="200" value="<?= _('Gruppe') ?>">
        </section>
        <?php if ($has_topics) : ?>
            <section class="course-data">
                <label>
                    <input type="radio" name="createmode" value="topics">
                    <?= _('Lege eine Gruppe pro Thema an') ?>
                </label>
            </section>
        <?php endif ?>
        <?php if ($has_cycles || $has_singledates) : ?>
            <section class="course-data">
                <label>
                    <input type="radio" name="createmode" value="dates">
                    <?= _('Lege eine Gruppe pro regelmäßiger Zeit/Einzeltermin an') ?>
                </label>
            </section>
        <?php endif ?>
        <section class="course-data">
            <label>
                <input type="radio" name="createmode" value="lecturers">
                <?= _('Lege eine Gruppe pro Lehrendem an') ?>
            </label>
        </section>
    </fieldset>
    <fieldset>
        <legend>
            <?= _('Voreinstellungen für alle anzulegenden Gruppen') ?>
        </legend>
        <section>
            <label>
                <?= _('Gruppengröße') ?>
            </label>
            <input type="number" name="size" value="0" min="0">
        </section>
        <section>
            <label>
                <input type="checkbox" name="selfassign" value="1">
                <?= _('Selbsteintrag') ?>
            </label>
        </section>
        <section>
            <label>
                <input type="checkbox" name="exclusive" value="1">
                <?= _('Selbsteintrag in nur eine Gruppe') ?>
            </label>
        </section>
        <section>
            <label>
                <?= _('Selbsteintrag erlaubt ab') ?>
                <input type="text" data-datetime-picker id="selfassign_start"  size="20" name="selfassign_start" value="<?= date('d.m.Y H:i') ?>">
            </label>
        </section>
        <section>
            <label>
                <?= _('Selbsteintrag erlaubt bis') ?>
                <input type="text" data-datetime-picker='{">":"#selfassign_start"}' size="20" name="selfassign_end" value="">
            </label>
        </section>
        <section>
            <label>
                <input type="checkbox" name="makefolder" value="1">
                <?= _('Dateiordner anlegen') ?>
            </label>
        </section>
    </fieldset>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Anlegen'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
            $controller->url_for('course/statusgroups')) ?>
    </footer>
</form>
<script type="text/javascript" language="JavaScript">
    //<!--
    $('.course-data').hide();
    STUDIP.Statusgroups.initInputs();
    //-->
</script>
