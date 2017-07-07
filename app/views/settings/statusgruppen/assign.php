<? use Studip\Button, Studip\LinkButton; ?>

<form action="<?= $controller->url_for('settings/statusgruppen/assign') ?>" method="post" class="default">
    <input type="hidden" name="studipticket" value="<?= get_ticket() ?>">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Person einer Gruppe zuordnen') ?></legend>
        <label>
            <?= _('Einrichtung und Funktion auswählen') ?>:
            <select required name="role_id" class="role-selector">
                <option value="">-- <?= _('Bitte auswählen') ?> --</option>
                <? foreach ($admin_insts as $data): ?>
                    <optgroup label="<?= htmlReady(mb_substr($data['Name'], 0, 70)) ?>">
                        <? Statusgruppe::displayOptionsForRoles($data['groups']) ?>
                    </optgroup>
                    <? foreach ($data['sub'] as $sub_id => $sub): ?>
                        <optgroup label="<?= htmlReady(mb_substr($sub['Name'], 0, 70)) ?>" class="nested">
                            <? Statusgruppe::displayOptionsForRoles($sub['groups']) ?>
                        </optgroup>
                    <? endforeach; ?>
                <? endforeach; ?>
            </select>
        </label>

    </fieldset>

    <footer>
        <?= Button::create(_('Zuweisen'), 'assign') ?>
    </footer>
</form>
