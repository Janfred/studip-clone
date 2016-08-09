<tr>
    <?php if ($is_tutor && !$is_locked) : ?>
        <td>
            <input aria-label="<?= _('Mitglieder auswählen') ?>"
                   type="checkbox" name="group[<?= $group->id ?>][<?= $m->user_id ?>]"
                   class="groupmembers-<?= $group->id ?>" value="1">
        </td>
    <?php endif ?>
    <td>
        <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>
    </td>
    <td>
        <a href="<?= $controller->url_for(sprintf('profile?username=%s',$m->username)) ?>">
            <?= Avatar::getAvatar($m->user_id, $m->username)->getImageTag(Avatar::SMALL,
                array('style' => 'margin-right: 5px',
                    'title' => htmlReady($m->getUserFullname('full_rev')))); ?>
            <?= htmlReady($m->getUserFullname('full_rev')) ?>
            <?php if ($user_id == $m->user_id && $m->visible == 'no') : ?>
                (<?= _('unsichtbar') ?>)
            <?php endif ?>
        </a>
    </td>
    <?php if($is_tutor) : ?>
        <td>
            <?= strftime('%x %X', $m->mkdate) ?>
        </td>
        <td>
            <?= $this->render_partial('course/members/_studycourse.php',
                array('study_courses' => UserModel::getUserStudycourse($m->user_id))) ?>
        </td>
    <?php endif ?>
    <td class="memberactions">
        <?= ActionMenu::get()
                ->condition($is_tutor || $m->user_id !== $GLOBALS['user']->id)
                ->addLink(
                    $controller->url_for('messages/write', [
                        'rec_uname'       => $m->username,
                        'default_subject' => $course_title,
                    ]),
                    _('Nachricht schicken'),
                    Icon::create('mail', 'clickable', [
                        'title' => sprintf(_('Nachricht an %s schicken'), $m->getUserFullname()),
                    ]),
                    ['data-dialog' => 'size=auto']
                )
                ->condition($is_tutor)
                ->addLink(
                    $controller->url_for('course/statusgroups/move_member', $m->user_id, $group->id),
                    _('In eine andere Gruppe verschieben'),
                    Icon::create('person+move_right', 'clickable', [
                        'title' => sprintf(
                            _('%s in eine andere Gruppe verschieben'),
                            $m->getUserFullname()
                        ),
                    ]),
                    ['data-dialog' => 'size=auto']
                )
                ->condition($group->id !== 'nogroup' && ($is_tutor || $m->user_id === $GLOBALS['user']->id))
                ->addLink(
                    $controller->url_for('course/statusgroups/delete_member', $m->user_id, $group->id),
                    _('Aus der Gruppe entfernen'),
                    Icon::create('trash', 'clickable', [
                        'title' => sprintf(
                            _('%s aus Gruppe %s entfernen'),
                            $m->getUserFullname(),
                            $group->name
                        ),
                    ]),
                    ['data-confirm' => sprintf(
                        _('Soll %s wirklich aus der Gruppe %s entfernt werden?'),
                        $m->getUserFullname(),
                        $group->name
                    )]
                ) ?>
    </td>
</tr>
