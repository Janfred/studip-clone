<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<?= $controller->renderMessages() ?>
<h1>
    <? if ($fach->isNew()) : ?>
    <?= _('Neues Fach') ?>
    <? else : ?>
    <?= sprintf(_('Fach: %s'), htmlReady($fach->getDisplayName())) ?>
    <? endif; ?>
</h1>
<? $perm = MvvPerm::get($fach) ?>
<form class="default" action="<?= $controller->url_for('/fach/', $fach->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Name des Faches') ?></legend>
        <label>
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>">
            <input <?= $perm->disable('name') ?>type="text" id="fach_name" name="name" maxlength="254" value="<?= htmlReady($fach->name) ?>" required>
        </label>
        <label>
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>">
            <input <?= $perm->disable('name_en') ?>type="text" id="fach_name_en" name="name_en" maxlength="254" value="<?= htmlReady($fach->name_en) ?>">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Kurzname') ?></legend>
        <label>
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>">
            <input <?= $perm->disable('name_kurz') ?>type="text" id="fach_name_kurz" name="name_kurz" maxlength="50" value="<?= htmlReady($fach->name_kurz) ?>">
        </label>
        <label>
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>">
            <input <?= $perm->disable('name_kurz_en') ?>type="text" id="fach_name_kurz_en" name="name_kurz_en" maxlength="50" value="<?= htmlReady($fach->name_kurz_en) ?>">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Beschreibung') ?></legend>
        <label for="fach_beschreibung">
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>" style="vertical-align: top;">
            <? if ($perm->haveFieldPermBeschreibung()) : ?>
                <textarea cols="60" rows="5" id="fach_beschreibung" name="beschreibung" class="add_toolbar resizable ui-resizable"><?= htmlReady($fach->beschreibung) ?></textarea>
            <? else : ?>
                <textarea readonly cols="60" rows="5" id="fach_beschreibung" name="beschreibung" class="resizable ui-resizable"><?= htmlReady($fach->beschreibung) ?></textarea>
            <? endif; ?>
        </label>
        <label for="fach_beschreibung_en">
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>" style="vertical-align: top;">
            <? if ($perm->haveFieldPermBeschreibung_en()) : ?>
                <textarea cols="60" rows="5" id="fach_beschreibung_en" name="beschreibung_en" class="add_toolbar resizable ui-resizable"><?= htmlReady($fach->beschreibung_en) ?></textarea>
            <? else : ?>
                <textarea readonly cols="60" rows="5" id="fach_beschreibung_en" name="beschreibung_en" class="resizable ui-resizable"><?= htmlReady($fach->beschreibung_en) ?></textarea>
            <? endif; ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Verantwortliche Einrichtung') ?></legend>
        <? if ($perm->haveFieldPermDepartments(MvvPerm::PERM_WRITE)) : ?>
        <?= $search_institutes->render(); ?>
        <? if (Request::submitted('search_institutes')) : ?>
            <?= Icon::create('refresh', 'clickable', ['name' => 'reset_institutes', 'data-qs_id' => $search_institutes_id])->asInput(); ?>
        <? else : ?>
            <?= Icon::create('search', 'clickable', ['name' => 'search_dokumente', 'data-qs_id' => $search_institutes_id, 'data-qs_name' => $search_institutes->getId(), 'class' => 'mvv-qs-button'])->asInput(); ?>
        <? endif; ?>
        <? endif;?>
        <ul id="institut_target" class="mvv-assigned-items mvv-institute sortable">
            <? if ($perm->haveFieldPermDepartments(MvvPerm::PERM_WRITE)) : ?>
                <li class="mvv-item-list-placeholder"<?= (count($fach->departments) ? ' style="display: none;"' : '') ?>><?= _('Bitte mindestens eine verantwortliche Einrichtung hinzufügen.') ?></li>
            <? elseif (!count($fach->getFachbereiche())) : ?>
                <li class="mvv-item-list-placeholder"><?= _('Es wurde noch keine verantwortliche Einrichtung angegeben.') ?></li>
            <? endif; ?>
            <? foreach ($fach->getFachbereiche() as $fachbereich) : ?>
            <li id="fachbereiche_<?= $fachbereich->getId() ?>" class="sort_items">
                <div class="mvv-item-list-text">
                    <? if ($fachbereich) : ?>
                    <?= htmlReady($fachbereich->getDisplayName()) ?>
                    <? else: ?>
                    <?= _('unbekannte Einrichtung') ?>
                    <? endif; ?>
                </div>
                <? if($perm->haveFieldPerm('institut_id', MvvPerm::PERM_WRITE)): ?>
                <div class="mvv-item-list-buttons">
                    <a href="#" class="mvv-item-remove"><?= Icon::create('trash', 'clickable', array('title' => _('Einrichtung entfernen')))->asImg(); ?></a>
                </div>
                <? endif;?>
                <input type="hidden" name="institut_items[]" value="<?= $fachbereich->getId() ?>">
            </li>
            <? endforeach; ?>
        </ul>
    </fieldset>
    <fieldset>
        <legend><?= _('Zusätzliche Angaben') ?></legend>
        <label><?= _('Schlagworte') ?>
        <? if ($perm->haveFieldPerm('schlagworte')) : ?>
            <textarea cols="60" rows="5" name="schlagworte" id="schlagworte" class="add_toolbar ui-resizable"><?= htmlReady($fach->schlagworte) ?></textarea>
        <? else : ?>
            <textarea readonly cols="60" rows="5" name="schlagworte" id="schlagworte" class="ui-resizable"><?= htmlReady($fach->schlagworte) ?></textarea>
        <? endif; ?>
            <?= _('Hier können zusätzlich Schlagworte angegeben werden, die in der Suche berücksichtigt werden.') ?>
        </label>
    </fieldset>
    <footer data-dialog-button>
    <? if ($fach->isNew()) : ?>
        <? if ($perm->havePermCreate()) : ?>
            <?= Button::createAccept(_('anlegen'), 'store', array('title' => _('Fach anlegen'))) ?>
        <? endif; ?>
    <? else : ?>
        <? if ($perm->havePermWrite()) : ?>
            <?= Button::createAccept(_('übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
        <? endif; ?>
    <? endif; ?>
        <?= LinkButton::createCancel(_('abbrechen'), $controller->url_for('/index'), array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>