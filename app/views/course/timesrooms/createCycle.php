<form
    action="<?= $controller->url_for('course/timesrooms/' . ($cycle->isNew() ? 'saveCycle' : 'editCycle/' . $cycle->id), $editParams) ?>"
    class="default" method="post"
    <?= Request::isXhr() ? 'data-dialog="size=big"' : '' ?>>
    <?= CSRFProtection::tokenTag() ?>

    <? if ($has_bookings): ?>
        <?= MessageBox::error(_('Wenn Sie die regelm��ige Zeit �ndern, verlieren Sie die Raumbuchungen f�r alle in der Zukunft liegenden Termine!'),
            array(_('Sind Sie sicher, dass Sie die regelm��ige Zeit �ndern m�chten?'))) ?>
    <? endif; ?>

    <label>
        <?= _('Starttag') ?>
        <select name="day">
            <? foreach (array(1, 2, 3, 4, 5, 6, 0) as $d): ?>
                <option
                    value="<?= $d ?>" <?= (Request::int('day') === $d) || (!is_null($cycle->start_time) && $cycle->weekday == $d) || ($d == 1) ? 'selected' : ''?>>
                    <?= getWeekday($d, false) ?>
                </option>
            <? endforeach; ?>
        </select>
    </label>

    <label>
        <?= _('Startzeit') ?>
        <input class="has-time-picker size-s" type="text" name="start_time"
               value="<?= htmlReady(Request::get('start_time', $cycle->start_time)) ?>"
               required placeholder="HH:mm">
    </label>

    <label>
        <?= _('Endzeit') ?>
        <input class="has-time-picker size-s" type="text" name="end_time"
               value="<?= htmlReady(Request::get('end_time', $cycle->end_time)) ?>"
               required placeholder="HH:mm">
    </label>

    <label>
        <?= _('Beschreibung') ?>
        <input type="text" name="description"
               value="<?= Request::get('description', $cycle->description) ?>">
    </label>

    <label>
        <?= _('Turnus') ?>
        <select name="cycle">
            <option value="0" <?= (Request::int('cycle', $cycle->cycle) === 0) ? 'selected' : '' ?>>
                <?= _('W�chentlich') ?>
            </option>
            <option value="1" <?= (Request::int('cycle', $cycle->cycle) === 1) ? 'selected' : '' ?>>
                <?= _('Zweiw�chentlich') ?>
            </option>
            <option value="2" <?= (Request::int('cycle', $cycle->cycle) === 2) ? 'selected' : '' ?>>
                <?= _('Dreiw�chentlich') ?>
            </option>
        </select>
    </label>

    <label>
        <?= _('Startwoche') ?>
        <select name="startWeek">
            <? foreach ($start_weeks as $value => $data): ?>
                <option
                    value="<?= $value ?>" <?= (Request::get('startWeek', $cycle->week_offset) == $value) ? 'selected' : '' ?>>
                    <?= htmlReady($data['text']) ?>
                </option>
            <? endforeach; ?>
        </select>
    </label>

    <label>
        <?= _('Endwoche') ?>
        <select name="endWeek">
            <option value="0"><?= _('Ganzes Semester') ?></option>
            <? foreach ($start_weeks as $value => $data): ?>
                <option
                    value="<?= $value + 1 ?>" <?= (Request::get('endWeek', $cycle->end_offset) == $value + 1) ? 'selected' : '' ?>>
                    <?= htmlReady($data['text']) ?>
                </option>
            <? endforeach; ?>
        </select>
    </label>

    <label>
        <?= _('SWS Dozent') ?>
        <input type="text" name="teacher_sws" class="size-s"
               value="<?= htmlReady(Request::get('teacher_sws', $cycle->sws)) ?>">
    </label>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
        <? if (Request::get('fromDialog') == 'true'): ?>
            <?= Studip\LinkButton::create(_('Zur�ck zur �bersicht'), $controller->url_for('course/timesrooms/index'), array('data-dialog' => 'size=big')) ?>
        <? endif; ?>
    </footer>
</form>