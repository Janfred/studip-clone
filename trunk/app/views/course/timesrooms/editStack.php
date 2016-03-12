<form method="post" action="<?= $controller->url_for('course/timesrooms/saveStack/' . $cycle_id) ?>" class="default"
      data-dialog="size=big">
    <input type="hidden" name="method" value="edit" />

    <label>
        <?= _('Durchführende Lehrende') ?>
        <select name="related_persons_action" id="related_persons_action">
            <option value="">-- <?= _('Aktion auswählen') ?> --</option>
            <option value="add"><?= _('hinzufügen') ?></option>
            <option value="delete">...<?= _('entfernen') ?></option>
        </select>
    </label>

    <select name="related_persons[]" id="related_persons" multiple>
        <? foreach ($teachers as $teacher) : ?>
            <option value="<?= htmlReady($teacher['user_id']) ?>"><?= htmlReady($teacher['fullname']) ?></option>
        <? endforeach ?>
    </select>

    <? if (!count($gruppen)) : ?>
        <label>
            <?= _('Betrifft die Gruppen') ?>
            <select name="related_groups_action" id="related_groups_action">
                <option value="">-- <?= _('Aktion auswählen') ?> --</option>
                <option value="add">...<?= _('hinzufügen') ?></option>
                <option value="delete">...<?= _('entfernen') ?></option>
            </select>
        </label>

        <select id="related_groups" name="related_groups[]" multiple>
            <? foreach ($gruppen as $gruppe) : ?>
                <option value="<?= htmlReady($gruppe->statusgruppe_id) ?>"><?= htmlReady($gruppe->name) ?></option>
            <? endforeach ?>
        </select>
    <? endif ?>


    <p><strong><?= _('Raumangaben') ?></strong></p>
    <? if (Config::get()->RESOURCES_ENABLE && $resList->numberOfRooms()) : ?>
        <? $resList->reset() ?>
        <section>
            <label style="width: 10%">
                <input type="radio" name="action" value="room" checked="checked" />
            </label>
            <label>
                <select class="size-l" name="room" onFocus="jQuery('input[type=radio][name=action][value=room]').prop('checked', 'checked')">
                    <option value="0">-- <?= _('Raum auswählen') ?> --</option>
                    <? while ($res = $resList->next()) : ?>
                        <option value="<?= $res['resource_id'] ?>">
                            <?= my_substr(htmlReady($res["name"]), 0, 30) ?> <?= $seats[$res['resource_id']] ? '(' . $seats[$res['resource_id']] . ' ' . _('Sitzplätze') . ')' : '' ?>
                        </option>
                    <? endwhile; ?>
                </select>
                <?= Icon::create('room-clear', 'inactive', ['title' => _("Nur buchbare Räume anzeigen")])->asImg(16, ["class" => 'bookable_rooms_action', "data-name" => 'bulk_action']) ?>
            </label>
        </section>

        <? $placerholder = _('Freie Ortsangabe (keine Raumbuchung):') ?>
    <? else : ?>
        <? $placerholder = _('Freie Ortsangabe:') ?>
    <? endif ?>
    <section class="hgroup" style="margin: 10px 0px">
        <label style="width:10%">
            <input type="radio" name="action" value="freetext">
        </label>
        <label>
            <input type="text" name="freeRoomText" class="size-l" maxlength="255" value="<?= $tpl['freeRoomText'] ?>"
                   placeholder="<?= $placerholder ?>"
                   onFocus="jQuery('input[type=radio][name=action][value=freetext]').prop('checked', 'checked')">
        </label>
    </section>
    <? if (Config::get()->RESOURCES_ENABLE) : ?>
        <label>
            <input type="radio" name="action" value="noroom" style="display:inline">
            <?= _('Kein Raum') ?>
        </label>
    <? endif ?>

    <label class="inline">
        <input type="radio" name="action" value="nochange" checked="checked">
        <?= _('Keine Änderungen an den Raumangaben vornehmen') ?>
    </label>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Änderungen speichern'), 'save') ?>
        <? if (Request::get('fromDialog') == 'true') : ?>
            <?= Studip\LinkButton::create(_('Zurück zur Übersicht'), $controller->url_for('course/timesrooms/index'), array('data-dialog' => 'size=big')) ?>
        <? endif ?>
    </footer>
</form>
