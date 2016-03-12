<form action="<?= $controller->url_for('course/timesrooms/saveDate/' . $date->termin_id) ?>"
      method="post" class="default collapsable" <?= Request::int('fromDialog') ? 'data-dialog="size=big"' : '' ?>>
    <?= CSRFProtection::tokenTag() ?>
    <fieldset style="margin-top: 1ex">
        <legend><?= _('Zeitangaben') ?></legend>
        <label id="course_type" class=col-6>
            <?= _('Art') ?>
            <select name="course_type" id="course_type" class="size-s">
                <? foreach ($GLOBALS['TERMIN_TYP'] as $id => $value) : ?>
                    <option value="<?= $id ?>"
                        <?= $date->date_typ == $id ? 'selected' : '' ?>>
                        <?= htmlReady($value['name']) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>
        <label class="col-2">
            <?= _('Datum') ?>
            <input class="has-date-picker size-s" type="text" name="date"
                   value="<?= $date->date ? strftime('%d.%m.%Y', $date->date) : '' ?>">
        </label>
        <label class="col-2">
            <?= _('Startzeit') ?>
            <input class="studip-timepicker size-s" type="text" name="start_time" placeholder="HH:mm"
                   value="<?= $date->date ? strftime('%H:%M', $date->date) : '' ?>">
        </label>
        <label class="col-2">
            <?= _('Endzeit') ?>
            <input class="studip-timepicker size-s" type="text" name="end_time" placeholder="HH:mm"
                   value="<?= $date->end_time ? strftime('%H:%M', $date->end_time) : '' ?>">
        </label>
    </fieldset>
    <fieldset class="collapsed">
        <legend><?= _('Raumangaben') ?></legend>
        <? if (Config::get()->RESOURCES_ENABLE && $resList->numberOfRooms()) : ?>
            <label>
                <input style="display: inline;" type="radio" name="room" value="room"
                       id="room" <?= $date->room_assignment->resource_id ? 'checked' : '' ?>>

                <select name="room_sd" style="display: inline-block; width: 50%; margin-left: 40px" class="single_room">
                    <option value=""><?= _('Wählen Sie einen Raum aus') ?></option>
                    <? foreach ($resList->resources as $room_id => $room) : ?>
                        <option value="<?= $room_id ?>"
                            <?= $date->room_assignment->resource_id == $room_id ? 'selected' : '' ?>>
                            <?= $room ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </label>
        <? endif; ?>
        <label class="horizontal">
            <input type="radio" name="room" value="freetext" <?= $date->raum ? 'checked' : '' ?>
                   style="display: inline">
            <input style="display: inline-block; width: 50%; margin-left: 40px" type="text"
                   name="freeRoomText_sd"
                   placeholder="<?= _('Freie Ortsangabe (keine Raumbuchung)') ?>"
                   value="<?= $date->raum ? htmlReady($date->raum) : '' ?>">
        </label>

        <label>
            <input type="radio" name="room" style="display:inline;" value="noroom"
                <?= (!empty($date->room_assignment->resource_id) || !empty($date->raum) ? '' : 'checked') ?>>
            <span style="display: inline-block; margin-left: 40px"><?= _('Kein Raum') ?></span>
        </label>

    </fieldset>

    <? if (!empty($dozenten)) : ?>
        <fieldset class="collapsed">
            <legend><?= _('Durchführende Lehrende') ?></legend>

            <ul class="termin_related teachers">
                <? foreach ($dozenten as $related_person => $dozent) : ?>
                    <? $related = true; ?>
                    <? if (in_array($related_person, $related_persons) !== false) : ?>
                        <? $related = false ?>
                    <? endif ?>
                    <li data-lecturerid="<?= $related_person ?>" <?= !$related ? '' : 'style="display: none"' ?>>
                        <? $dozenten[$related_person]['hidden'] = !$related ?>
                        <?= htmlReady(User::find($related_person)->getFullname()); ?>
                        <a href="javascript:" onClick="STUDIP.Raumzeit.removeLecturer('<?= $related_person ?>')">
                            <?= Icon::create('trash', 'clickable')->asImg(16) ?>
                        </a>
                    </li>
                <? endforeach ?>
            </ul>
            <input type="hidden" name="related_teachers" value="<?= implode(',', $related_persons) ?>" />

            <label for="add_teacher">
                <span style="display: block">
                    <?= _('Lehrende auswählen') ?>
                </span>
                <select id="add_teacher" name="teachers" style="display: inline-block; width: 40%">
                    <option value="none"><?= _('Lehrende auswählen') ?></option>
                    <? foreach ($dozenten as $dozent) : ?>
                        <option
                            value="<?= htmlReady($dozent['user_id']) ?>" <?= $dozent['hidden'] ? 'style="display: none"' : '' ?>>
                            <?= htmlReady($dozent['fullname']) ?>
                        </option>
                    <? endforeach; ?>
                </select>
                <a href="javascript:" onClick="STUDIP.Raumzeit.addLecturer()"
                   title="<?= _('Lehrenden hinzufügen') ?>">
                    <?= Icon::create('arr_2up', 'sort')->asImg(16) ?>
                </a>
            </label>
        </fieldset>
    <? endif ?>

    <? if (!empty($gruppen)) : ?>
        <fieldset class="collapsed">
            <legend><?= _('Beteiligte Gruppen') ?></legend>

            <ul class="termin_related groups">
                <? foreach ($gruppen as $index => $statusgruppe) : ?>
                    <? $related = true ?>
                    <? if (in_array($statusgruppe->getId(), $related_groups)) : ?>
                        <? $related = false; ?>
                    <? endif ?>
                    <li data-groupid="<?= htmlReady($statusgruppe->getId()) ?>" <?= !$related ? '' : 'style="display: none"' ?>>
                        <?= htmlReady($statusgruppe['name']) ?>
                        <a href="javascript:" onClick="STUDIP.Raumzeit.removeGroup('<?= $statusgruppe->getId() ?>')">
                            <?= Icon::create('trash', 'clickable')->asImg() ?>
                        </a>
                    </li>
                <? endforeach ?>
            </ul>

            <input type="hidden" name="related_statusgruppen" value="<?= implode(',', $related_groups) ?>">

            <label>
                <span style="display:block;">
                    <?= _('Gruppe hinzufügen') ?>
                </span>

                <select name="groups" style="display: inline-block; width: 40%">
                    <option value="none"><?= _('Gruppen auswählen') ?></option>
                    <? foreach ($gruppen as $gruppe) : ?>
                        <option value="<?= htmlReady($gruppe->getId()) ?>"
                                style="<?= in_array($gruppe->getId(), $related_groups) ? 'display: none;' : '' ?>">
                            <?= htmlReady($gruppe['name']) ?>
                        </option>
                    <? endforeach ?>
                </select>
                <a href="javascript:" onClick="STUDIP.Raumzeit.addGroup()" title="<?= _('Gruppe hinzufügen') ?>">
                    <?= Icon::create('arr_2up', 'sort')->asImg(16) ?>
                </a>

            </label>
        </fieldset>
    <? endif ?>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save_dates') ?>
        <? if (Request::int('fromDialog')) : ?>
            <?= Studip\LinkButton::create(_('Zurück zur Übersicht'),
                                          $controller->url_for('course/timesrooms',
                                                               array('fromDialog' => 1, 'contentbox_open' => $date->metadate_id)),
                                          array('data-dialog' => 'size=big')) ?>
        <? endif ?>
        <? if (Request::isXhr() && !$locked && Config::get()->RESOURCES_ENABLE && Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS): ?>
            <?= Studip\LinkButton::create(_('Raumanfrage erstellen'),
                                          $controller->url_for('course/room_requests/edit/' . $course->id,
                                                               array_merge($params, array('origin' => 'course_timesrooms'))),
                                          array('data-dialog' => 'size=big')) ?>
        <? endif ?>
    </footer>
</form>