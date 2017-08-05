<?php if (count($members)) : ?>
    <table class="default">
        <colgroup>
            <col width="20">
            <?php if($is_tutor) : ?>
                <?php $cols = 5 ?>
                <?php if (!$is_locked) : ?>
                    <col width="20">
                    <?php $cols = 6 ?>
                <?php endif ?>
                <col>
                <col width="15%">
                <col width="35%">
            <?php else : ?>
                <col>
                <?php $cols = 3 ?>
            <?php endif ?>

            <col width="80">
        </colgroup>
        <thead>
            <tr class="sortable">
                <?php if ($is_tutor && !$is_locked) : ?>
                    <th>
                        <input aria-label="<?= sprintf(_('Alle Mitglieder dieser Gruppe auswählen')) ?>"
                               type="checkbox" name="all" value="1"
                               data-proxyfor=":checkbox.groupmembers-<?= $group->id ?>"
                               data-activates="select#members-action-<?= $group->id ?>">
                    </th>
                <?php endif ?>
                <th></th>
                <th <?= ($sort_by == 'nachname' && $sort_group == $group->id) ?
                    sprintf('class="sort%s"', $order) : '' ?>>
                    <a href="<?= URLHelper::getLink('#' . $group->id,
                        array(
                            'sortby' => 'nachname',
                            'sort_group' => $group->id,
                            'order' => $group->id && $sort_by == 'nachname' ?
                                ($order == 'desc' ? 'asc' : 'desc') : 'desc',
                            'contentbox_open' => $group->id
                        )) ?>">
                        <?=_('Nachname, Vorname')?>
                    </a>
                </th>
                <?php if ($is_tutor) :?>
                    <th <?= ($sort_by == 'mkdate' && $sort_group == $group->id) ? sprintf('class="sort%s"', $order) : '' ?>>
                        <a href="<?= URLHelper::getLink('#' . $group->id,
                            array(
                                'sortby' => 'mkdate',
                                'sort_group' => $group->id,
                                'order' => $group->id && $sort_by == 'mkdate' ?
                                    ($order == 'desc' ? 'asc' : 'desc') : 'desc',
                                'contentbox_open' => $group->id
                            )) ?>">
                            <?= _('Anmeldedatum') ?>
                        </a>
                    </th>
                    <th>
                        <?= _('Studiengang') ?>
                    </th>
                <?php endif ?>
                <th><?= _('Aktion') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; $invisible = 0; foreach ($members as $m) : ?>
                <?php if ($is_tutor || $m->user_id == $GLOBALS['user']->id || $m->visible != 'no') : ?>
                    <?= $this->render_partial('course/statusgroups/_member',
                        array('m' => $m, 'i' => $i++, 'is_tutor' => $is_tutor, 'is_locked' => $is_locked)); ?>
                <?php else : $invisible++; endif ?>
            <?php endforeach ?>
        </tbody>
        <tfoot>
        <tr>
            <?php if ($is_tutor) : ?>
                <td colspan="<?= $cols ?>">
                    <?php if (!$is_locked) : ?>
                        <div class="memberselect">
                            <label>
                                <input aria-label="<?= sprintf(_('Alle Mitglieder dieser Gruppe auswählen')) ?>"
                                       type="checkbox" name="all" value="1"
                                       data-proxyfor=":checkbox.groupmembers-<?= $group->id ?>"
                                       data-activates="select#members-action-<?= $group->id ?>">
                                <?= _('Alle Mitglieder dieser Gruppe auswählen') ?>
                            </label>
                        </div>
                        <div class="memberactions">
                            <label>
                                <select name="members_action[<?= $group->id ?>]"
                                        id="members-action-<?= $group->id ?>" disabled>
                                    <option value="move"><?= _('In andere Gruppe verschieben') ?></option>
                                    <?php if ($group->id != 'nogroup') : ?>
                                        <option value="delete"><?= _('Aus dieser Gruppe entfernen') ?></option>
                                    <?php endif ?>
                                </select>
                            </label>
                            <input type="hidden" name="source" value="<?= $group->id ?>">
                            <?= Studip\Button::create(_('Ausführen'), 'batch_members['.$group->id.']',
                                array('data-dialog' => 'size=auto')) ?>
                        </div>
                    <?php endif ?>
                </td>
            <?php elseif (!$is_tutor) : ?>
                <td colspan="<?= $cols ?>">
                    <?= sprintf(_('+ %u unsichtbare Personen'), $invisible) ?>
                </td>
            <?php endif ?>
        </tr>
        </tfoot>
    </table>
<?php else : ?>
    <div class="statusgroup-no-members">
        <?= _('Diese Gruppe hat keine Mitglieder.') ?>
    </div>
<?php endif;
