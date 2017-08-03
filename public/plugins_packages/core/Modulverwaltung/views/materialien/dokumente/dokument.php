<? use Studip\Button, Studip\LinkButton; ?>
<? if (Request::isXhr()) : ?>
    <? foreach (PageLayout::getMessages() as $messagebox) : ?>
        <?= $messagebox ?>
    <? endforeach ?>
<? endif; ?>
<h1>
    <? if ($dokument->isNew()) : ?>
    <?= _('Neues Dokument') ?>
    <? else : ?>
    <?= sprintf(_('Dokument: %s'), htmlReady($dokument->getDisplayName())) ?>
    <? endif; ?>
</h1>
<? $perm = MvvPerm::get($dokument) ?>
<form class="default" action="<?= $controller->url_for('/dokument', $dokument->id) ?>" method="post"<?= Request::isXhr() ? ' data-dialog' : '' ?>>
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Name des Dokuments') ?></legend>
        <label>
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('Deutsch') ?>">
            <input <?= $perm->disable('name') ?> type="text" id="dokument_name" name="name" maxlength="254" value="<?= htmlReady($dokument->name) ?>" required>
        </label>
        <label>
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('Englisch') ?>">
            <input <?= $perm->disable('name_en') ?> type="text" id="dokument_name_en" name="name_en" maxlength="254" value="<?= htmlReady($dokument->name_en) ?>">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Linktext') ?></legend>
        <label>
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('Deutsch') ?>">
            <input <?= $perm->disable('linktext') ?> type="text" id="dokument_linktext" name="linktext" maxlength="254" value="<?= htmlReady($dokument->linktext) ?>" required>
        </label>
        <label>
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('Englisch') ?>">
            <input <?= $perm->disable('linktext_en') ?> type="text" id="dokument_linktext_en" name="linktext_en" maxlength="254" value="<?= htmlReady($dokument->linktext_en) ?>">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('URL des Dokuments') ?></legend>
        <input <?= $perm->disable('url') ?> type="text" id="dokument_url" name="url" maxlength="4000" value="<?= htmlReady($dokument->url) ?>" required>
    </fieldset>
    <fieldset>
        <legend><?= _('Beschreibung') ?></legend>
        <label for="dokument_beschreibung">
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('Deutsch') ?>" style="vertical-align: top;">
            <? if ($perm->haveFieldPermBeschreibung()) : ?>
            <textarea cols="60" rows="5" id="dokument_beschreibung" name="beschreibung" class="add_toolbar resizable ui-resizable"><?= htmlReady($dokument->beschreibung) ?></textarea>
            <? else : ?>
            <textarea readonly cols="60" rows="5" id="dokument_beschreibung" name="beschreibung" class="resizable ui-resizable"><?= htmlReady($dokument->beschreibung) ?></textarea>
            <? endif; ?>
        </label>
        <label for="dokument_beschreibung_en">
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('Englisch') ?>" style="vertical-align: top;">
            <? if ($perm->haveFieldPermBeschreibung_en()) : ?>
            <textarea cols="60" rows="5" id="dokument_beschreibung_en" name="beschreibung_en" class="add_toolbar resizable ui-resizable"><?= htmlReady($dokument->beschreibung_en) ?></textarea>
            <? else : ?>
            <textarea readonly cols="60" rows="5" id="dokument_beschreibung_en" name="beschreibung_en" class="resizable ui-resizable"><?= htmlReady($dokument->beschreibung_en) ?></textarea>
            <? endif; ?>
        </label>
    </fieldset>
    <footer data-dialog-button>
        <? if ($dokument->isNew()) : ?>
            <? if ($perm->havePermCreate()) : ?>
            <?= Button::createAccept(_('Anlegen'), 'store', array('title' => _('Dokument anlegen'))) ?>
            <? endif; ?>
        <? else : ?>
            <? if ($perm->havePermWrite()) : ?>
            <?= Button::createAccept(_('Übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
            <? endif; ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $cancel_url, array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>