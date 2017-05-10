<? use Studip\Button; ?>

<? if ($locked_info): ?>
    <?= MessageBox::info(formatLinks($locked_info)) ?>
<? endif; ?>

<form id="edit_private" action="<?= $controller->url_for('settings/details/store') ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studipticket" value="<?= get_ticket() ?>">
    <fieldset>
        <legend><?= _('Lebenslauf bearbeiten') ?></legend>
        <label>
            <?= _('Telefon Festnetz (privat)') ?>:<br>
            <input type="tel" name="telefon"
                   value="<?= htmlReady($user->privatnr) ?>"
                    <? if (!$controller->shallChange('user_info.privatnr')) echo 'disabled'; ?>>
        </label>
        <label>
            <?= _('Mobiltelefon (privat)') ?>:<br>
            <input type="tel" name="cell"
                   value="<?= htmlReady($user->privatcell) ?>"
                    <? if (!$controller->shallChange('user_info.privatcell')) echo 'disabled'; ?>>
        </label>
        <label for="private_address">
            <?= _('Adresse (privat):') ?>
            <input type="text" name="anschrift" id="private_address"
                   value="<?= htmlReady($user->privadr) ?>"
                    <? if (!$controller->shallChange('user_info.privadr')) echo 'disabled'; ?>>
        </label>
        <? if (Config::get()->ENABLE_SKYPE_INFO): ?>
            <label>
                <?= _('Skype Name:') ?>
                <input type="text" name="skype_name"
                       value="<?= htmlReady($config->SKYPE_NAME) ?>">
            </label>
        <? endif; ?>
        <label>
            <?= _('Motto:') ?>
            <input type="text" name="motto" id="motto"
                   value="<?= htmlReady($user->motto) ?>"
                    <? if (!$controller->shallChange('user_info.motto')) echo 'disabled'; ?>>
        </label>
        <label>
            <?= _('Homepage:') ?>
            <input type="url" name="home" id="homepage"
                   value="<?= htmlReady($user->Home) ?>"
                    <? if (!$controller->shallChange('user_info.Home')) echo 'disabled'; ?>>
        </label>
        <label>
            <?= _('Hobbys:') ?>
            <textarea name="hobby" id="hobbies" style="width:100%;height:100px;"
                      class="add_toolbar wysiwyg"
                      <? if (!$controller->shallChange('user_info.hobby')) echo 'disabled'; ?>
            ><?= htmlReady($user->hobby) ?></textarea>
        </label>
        <a name="lebenslauf"></a>
        <label>
            <?= _('Lebenslauf:') ?>
            <textarea id="lebenslauf" name="lebenslauf" style="width:100%;height:100px;"
                      class="add_toolbar wysiwyg"
                      <? if (!$controller->shallChange('user_info.lebenslauf')) echo 'disabled'; ?>
            ><?= htmlReady($user->lebenslauf) ?></textarea>
        </label>
        <? if ($is_dozent): ?>
            <a name="schwerpunkte"></a>
            <label>
                <?= _('Schwerpunkte:') ?>
                <textarea id="schwerp" name="schwerp" style="width:100%;height:100px;"
                      class="add_toolbar wysiwyg"
                      <? if (!$controller->shallChange('user_info.schwerp')) echo 'disabled'; ?>
                ><?= htmlReady($user->schwerp) ?></textarea>
            </label>
            <a name="publikationen"></a>
            <label>
                <?= _('Publikationen:') ?>
                <textarea id="publi" name="publi" style="width:100%;height:100px;"
                      class="add_toolbar wysiwyg"
                      <? if (!$controller->shallChange('user_info.publi')) echo 'disabled'; ?>
                ><?= htmlReady($user->publi) ?></textarea>
            </label>
        <? endif; ?>
    </fieldset>

    <? if (count($user_entries) > 0): ?>
        <fieldset>
            <legend> <?= _('Zusätzliche Datenfelder') ?></legend>
            <? foreach ($user_entries as $id => $entry): ?>

                <? if (isset($invalid_entries[$id])): ?>

                    <? $entry = $invalid_entries[$id]; // Exchange entry ?>
                <? else: ?>
                <? endif; ?>
                <label for="datafields_<?= $entry->getId() ?>">
                    <?= htmlReady($entry->getName()) ?>
                    <? if ($entry->isEditable() && !LockRules::check($user->user_id, $entry->getId())): ?>
                        <?= $entry->getHTML('datafields') ?>
                    <? else: ?>
                        <div>
                            <?= formatReady($entry->getDisplayValue(false)) ?>

                            <small> <?= _('(Das Feld ist für die Bearbeitung gesperrt und kann '
                                       . 'nur durch einen Administrator verändert werden.)') ?></small>
                        </div>
                    <? endif; ?>
                    <? if (!$entry->isVisible($user->perms)): ?>
                        <?= tooltipIcon(_('Systemfeld (für die Person selbst nicht sichtbar)'), true) ?>
                    <? endif; ?>
                </label>


            <? endforeach; ?>
        </fieldset>
    <? endif; ?>
    <footer>
        <?= Button::create(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
    </footer>
</form>
