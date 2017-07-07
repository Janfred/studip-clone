<? use Studip\Button, Studip\LinkButton; ?>
<h2 class="hide-in-dialog">
    <?= _('Bearbeiten der Einrichtungsdaten') ?>
</h2>

<form action="<?= $controller->url_for('admin/user/store_user_institute/' . $institute->user_id . '/' . $institute->institut_id) ?>"
      method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <label>
        <?= _('Status') ?>
        <select name="inst_perms">
            <? foreach ($perms as $perm) : ?>
                <option value="<?= htmlReady($perm) ?>" <? if ($institute->inst_perms === $perm) echo 'selected'; ?>>
                    <?= htmlReady($perm) ?>
                </option>
            <? endforeach ?>
        </select>
    </label>
    <label>
        <?= _('Raum') ?>
        <input type="text" name="raum" value="<?= htmlReady($institute->raum) ?>">
    </label>
    <label>
        <?= _('Sprechzeiten') ?>
        <input type="text" name="sprechzeiten" value="<?= htmlReady($institute->sprechzeiten) ?>">
    </label>
    <label>
        <?= _('Telefon') ?>
        <input type="tel" name="telefon" value="<?= htmlReady($institute->Telefon) ?>">
    </label>
    <label>
        <?= _('Fax') ?>
        <input type="tel" name="fax" value="<?= htmlReady($institute->Fax) ?>">
    </label>
    <label>
        <?= _('Standard-Adresse') ?>
        <input type="checkbox" name="externdefault" value="1" <? if ($institute->externdefault) echo 'checked'; ?>>
    </label>
    <label>
        <?= _('Auf der Profilseite und in Adressbüchern sichtbar') ?>
        <input type="checkbox" name="visible" value="1" <? if ($institute->visible) echo 'checked'; ?>>
    </label>

<? if (count($datafields) > 0) : ?>
    <? foreach ($datafields as $entry) : ?>
        <label>
            <?= htmlReady($entry->getName()) ?>:
        <? if ($entry->isEditable()): ?>
            <?= $entry->getHTML('datafields') ?>
        <? else : ?>
            <?= $entry->getDisplayValue() ?>
        <? endif ?>
        </label>
    <? endforeach ?>
<? endif ?>
    <footer data-dialog-button>
        <?= Button::createAccept(_('Übernehmen'), 'uebernehmen', ['title' => _('Änderungen übernehmen')]) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/user/edit/' . $user->user_id)) ?>
    </footer>
</form>
