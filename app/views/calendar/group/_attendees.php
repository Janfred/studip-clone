<tbody class="collapsed">
    <tr class="header-row">
        <th colspan="3" class="toggle-indicator">
            <a class="toggler"><?= _('Teilnehmer hinzufügen') ?>
                <?
                if ($event->attendees->count()) {
                    $count_attendees = $event->attendees->filter(
                        function ($att, $k) use ($calendar) {
                            if ($att->range_id != $calendar->getRangeId()) {
                                return $att;
                            }
                        })->count();
                } else {
                    $count_attendees = 0;
                }
                ?>
                <? if ($count_attendees) : ?>
                    <? if ($count_attendees < $event->attendees->count()) : ?>
                        <?= sprintf(ngettext('(%s weiterer Teilnehmer)', '(%s weitere Teilnehmer)', $count_attendees), $count_attendees) ?>
                    <? else : ?>
                        <?= sprintf(_('(%s Teilnehmer)'), $count_attendees) ?>
                    <? endif; ?>
                <? endif; ?>
            </a>
        </th>
    </tr>
    <tr>
        <td colspan="3">
            <div>
                <label for="user_id_1"><h4><?= _('Teilnehmer') ?></h4></label>
                <ul class="clean" id="adressees">
                    <li id="template_adressee" style="display: none;" class="adressee">
                        <input type="hidden" name="attendees[]" value="">
                        <span class="visual"></span>
                        <a class="remove_adressee"><?= Icon::create('trash', 'clickable')->asImg(16, ['class' => 'text-bottom']) ?></a>
                    </li>
                    <? if ($event->isNew()) : ?>
                    <li style="padding: 0px;" class="adressee">
                        <input type="hidden" name="attendees[]" value="<?= $event->owner->id ?>">
                        <span class="visual">
                            <a href="<?= URLHelper::getLink('dispatch.php/profile', array('username' => $event->owner->username), true) ?>"><?= htmlReady($event->owner->getFullname()) ?></a>
                        </span>
                        <a class="remove_adressee"><?= Icon::create('trash', 'clickable')->asImg(16, ['class' => 'text-bottom']) ?></a>
                    </li>
                    <? endif; ?>
                    <? $group_status = array(
                        CalendarEvent::PARTSTAT_TENTATIVE => _('Abwartend'),
                        CalendarEvent::PARTSTAT_ACCEPTED => _('Angenommen'),
                        CalendarEvent::PARTSTAT_DECLINED => _('Abgelehnt'),
                        CalendarEvent::PARTSTAT_DELEGATED => _('Angenommen (keine Teilnahme)'),
                        CalendarEvent::PARTSTAT_NEEDS_ACTION => '') ?>
                    <? foreach ($event->attendees as $attendee) : ?>
                        <? if ($attendee->owner) : ?>
                        <li style="padding: 0px;" class="adressee">
                            <input type="hidden" name="attendees[]" value="<?= htmlReady($attendee->owner->id) ?>">
                            <span class="visual">
                                <a href="<?= URLHelper::getLink('dispatch.php/profile', array('username' => $attendee->owner->username), true) ?>"><?= htmlReady($attendee->owner->getFullname()) ?></a>
                                <? if ($event->havePermission(Event::PERMISSION_OWN, $attendee->owner->id)) : ?>
                                    (<?= _('Organisator') ?>)
                                <? elseif ($group_status[$attendee->group_status]) : ?>
                                    (<?= $group_status[$attendee->group_status] ?>)
                                <? endif; ?>
                            </span>
                            <a class="remove_adressee"><?= Icon::create('trash', 'clickable', array('title' => _('Teilnehmer entfernen')))->asImg(16, ['class' => 'text-bottom']) ?></a>
                        </li>
                        <? endif; ?>
                    <? endforeach ?>
                </ul>
                <?= $quick_search->render() ?>
                <?= $mps->render(); ?>
                <script>
                    STUDIP.MultiPersonSearch.init();
                </script>
            </div>
        </td>
    </tr>
</tbody>
