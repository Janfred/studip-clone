<form class="default" action="<?= $controller->url_for('course/wizard/process', $stepnumber, $temp_id) ?>" method="post">
    <h1><?= _('Anlegen der Veranstaltung') ?></h1>
    <?php if ($dialog) : ?>
        <?= MessageBox::info(_('Sie haben alle benötigten Daten angegeben und '.
            'können nun die Veranstaltung anlegen.')) ?>
    <?php else : ?>
        <?= MessageBox::info(_('Sie haben alle benötigten Daten angegeben und '.
            'können nun die Veranstaltung anlegen. Der nächste Schritt führt Sie '.
            'gleich in den Verwaltungsbereich der neu angelegten Veranstaltung, wo '.
            'Sie weitere Daten hinzufügen können.')) ?>
    <?php endif ?>
    <? if ($source_course) : ?>
        <section>
            <label>
                <input type="checkbox" checked name="copy_basic_data" value="1">
                <?=sprintf(_("Alle Grunddaten der Ursprungsveranstaltung (%s) kopieren"),
                    '<a data-dialog href="' . URLHelper::getLink('dispatch.php/course/details', array('sem_id' => $source_course->id)) . '">' . htmlReady($source_course->getFullname()) . '</a>')?>
            </label>
        </section>
    <? endif ?>
    <section>
        <input type="hidden" name="step" value="<?= $stepnumber ?>"/>
        <?php if ($dialog) : ?>
            <input type="hidden" name="dialog" value="1"/>
        <?php endif ?>
    </section>
    <footer data-dialog-button>
        <?= Studip\Button::create(_('Zurück'), 'back',
            $dialog ? array('data-dialog' => 'size=50%') : array()) ?>
        <?= Studip\Button::createAccept(_('Veranstaltung anlegen'), 'create') ?>
    </footer>
</form>
