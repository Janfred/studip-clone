<form class="default" action="<?= $controller->url_for('admin/loginstyle/add') ?>" method="post" enctype="multipart/form-data">
    <header>
        <h1><?= _('Bild(er) hinzufügen') ?></h1>
    </header>
    <section>
        <label>
            <?= _('Bild(er) hochladen') ?>
            <input type="file"
                   name="pictures[]"
                   style="display: none;"
                   multiple>
            <?= Icon::create('upload', 'clickable')->asImg(['class' => "text-bottom upload"]) ?>
        </label>
    </section>
    <section>
        <label>
            <input type="checkbox" name="desktop" value="1" checked>
            <?= _('aktiv in Desktopansicht') ?>
        </label>
    </section>
    <section>
        <label>
            <input type="checkbox" name="mobile" value="1" checked>
            <?= _('aktiv in Mobilansicht') ?>
        </label>
    </section>
    <footer data-dialog-button>
        <?= CSRFProtection::tokenTag() ?>
        <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('loginstyle/index')) ?>
    </footer>
</form>
