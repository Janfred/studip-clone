<? use Studip\Button, Studip\LinkButton; ?>
<h1>
    <?= $consumer->id
            ? sprintf(_('Registrierten Konsumenten "%s" bearbeiten'), htmlReady($consumer->title))
            : _('Neuen Konsumenten registrieren') ?></h1>


<form class="settings default"
      action="<?= $controller->url_for('admin/api/edit', $consumer->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Grundeinstellungen') ?></legend>

        <label for="active">
            <input type="checkbox" class="switch" id="active" name="active" value="1"
                    <?= $consumer->active ? 'checked' : '' ?>>
            <?= _('Aktiviert') ?>
        </label>


        <label for="title">
            <?= _('Titel') ?>
            <input required type="text" id="title" name="title"
                   placeholder="<?= _('Beispiel-Applikation') ?>"
                   value="<?= htmlReady($consumer->title) ?>">
        </label>
        
        <label for="contact">
            <?= _('Kontaktperson') ?>
            <input required type="text" id="contact" name="contact"
                   placeholder="John Doe"
                   value="<?= htmlReady($consumer->contact) ?>">
        </label>
        
        <label for="email">
            <?= _('Kontaktadresse') ?>
            <input required type="text" id="email" name="email"
                   placeholder="support@appsite.tld"
                   value="<?= htmlReady($consumer->email) ?>">
        </label>
        
        <label for="callback">
            <?= _('Callback URL') ?>
            <input required type="text" id="callback" name="callback"
                   placeholder="http://appsite.tld/auth"
                   value="<?= htmlReady($consumer->callback) ?>">
        </label>
        
        <? if ($consumer->id): ?>

            <label for="consumer_key">
                <?= _('Consumer Key') ?>
                <input readonly type="text" id="consumer_key"
                       value="<?= htmlReady($consumer->auth_key) ?>">
            </label>
            
            <label for="consumer_secret">
                <?= _('Consumer Secret') ?>
                <input readonly type="text" id="consumer_secret"
                       value="<?= htmlReady($consumer->auth_secret) ?>">
            </label>
        <? endif; ?>

        <? if ($consumer->id): ?>
            <div class="centered">
                <?= strftime(_('Erstellt am %d.%m.%Y %H:%M:%S'), $consumer->mkdate) ?><br>
                <? if ($consumer->mkdate != $consumer->chdate): ?>
                    <?= strftime(_('Zuletzt geändert am %d.%m.%Y %H:%M:%S'), $consumer->chdate) ?>
                <? endif; ?>
            </div>
        <? endif; ?>
    </fieldset>

    <fieldset>
        <legend><?= _('Applikation-Details') ?></legend>


        <label for="commercial">
            <?= _('Kommerziell') ?>
            <input type="checkbox" class="switch" id="commercial" name="commercial" value="1"
                    <?= $consumer->commercial ? 'checked' : '' ?>>
        </label>
        
        <label for="description">
            <?= _('Beschreibung') ?>
            <textarea id="description" name="description"><?= htmlReady($consumer->description) ?></textarea>
        </label>
        
        <label for="url">
            <?= _('URL') ?>
            <input type="text" id="url" name="url"
                   placeholder="http://appsite.tld"
                   value="<?= htmlReady($consumer->url) ?>">
        </label>

        <label for="type">
            <?= _('Typ') ?>
            <select name="type" id="type">
                <option value="">- <?= _('Keine Angabe') ?> -</option>
                <? foreach ($types as $type => $label): ?>
                    <option value="<?= $type ?>" <?= $consumer->type == $type ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>


        <label for="notes">
            <?= _('Notizen') ?>
            <textarea id="notes" name="notes"><?= htmlReady($consumer->notes) ?></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Button::createAccept(_('speichern'), 'store') ?>
        <?= LinkButton::createCancel(_('abbrechen'), $controller->url_for('admin/api')) ?>
    </footer>
</form>