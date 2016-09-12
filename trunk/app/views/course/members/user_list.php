<? use \Studip\Button; ?>
<a name="users"></a>

<form action="<?= $controller->url_for('course/members/edit_user/') ?>" method="post" data-dialog="">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default collapsable">
        <colgroup>
            <? if($is_tutor) :?>
            <col width="20">
            <? endif ?>
            <col width="20">
            <col>
            <? if($is_tutor) :?>
            <col width="15%">
            <col width="40%">
            <? endif ?>
            <col width="80">
        </colgroup>
        <caption>
            <?= $status_groups['user'] ?>
            <? if($is_tutor) :?>
            <span class="actions">
                <a href="<?= URLHelper::getLink('dispatch.php/messages/write',
                        ['filter' => 'send_sms_to_all',
                         'emailrequest' => 1,
                         'who' => 'user',
                         'course_id' => $course_id,
                         'default_subject' => $subject]
                ) ?>" data-dialog>
                       <?= Icon::create('inbox', 'clickable', ['title' => sprintf(_('Nachricht mit Mailweiterleitung an alle %s versenden'), $status_groups['user'])])->asImg() ?>
                </a>
            </span>
            <? endif ?>
        </caption>
        <thead>
            <tr class="sortable">
                <? if($is_tutor) :?>
                <th><input aria-label="<?= sprintf(_('Alle %s auswählen'), $status_groups['user']) ?>"
                               type="checkbox" name="all" value="1" data-proxyfor=":checkbox[name^=user]"></th>
                <? endif ?>
                <th></th>
                <th <?= ($sort_by == 'nachname' && $sort_status == 'user') ? sprintf('class="sort%s"', $order) : '' ?>>
                    <? ($sort_status != 'user') ? $order = 'desc' : $order = $order ?>
                    <a href="<?= URLHelper::getLink(sprintf('?sortby=nachname&sort_status=user&order=%s&toggle=%s',
                            $order, ($sort_by == 'nachname'))) ?>#users">
                        <?=_('Nachname, Vorname')?>
                    </a>
                </th>
                <? if($is_tutor) : ?>
                <th <?= ($sort_by == 'mkdate' && $sort_status == 'user') ? sprintf('class="sort%s"', $order) : '' ?>>
                    <a href="<?= URLHelper::getLink(sprintf('?sortby=mkdate&sort_status=user&order=%s&toggle=%s',
                       $order, ($sort_by == 'mkdate'))) ?>#user">
                        <?= _('Anmeldedatum') ?>
                    </a>
                </th>
                <th><?=_('Studiengang')?></th>
                <? endif ?>
                <th class="actions"><?= _('Aktion') ?></th>
            </tr>
        </thead>
        <tbody>
        <? $nr= 0; foreach($users as $leser) : ?>
        <? $fullname = $leser['fullname'];?>
            <tr>
                <? if($is_tutor) :?>
                <td>
                    <input aria-label="<?= sprintf(_('%s auswählen'), $status_groups['user']) ?>"
                           type="checkbox" name="user[<?= $leser['user_id'] ?>]" value="1" />
                </td>
                <? endif ?>
                <td style="text-align: right"><?= (++$nr < 10) ? sprintf('%02d', $nr) : $nr ?></td>
                <td>
                    <a href="<?= $controller->url_for(sprintf('profile?username=%s',$leser['username'])) ?>" <? if ($leser['mkdate'] >= $last_visitdate) echo 'class="new-member"'; ?>>
                        <?= Avatar::getAvatar($leser['user_id'],$leser['username'])->getImageTag(Avatar::SMALL,
                                ['style' => 'margin-right: 5px','title' => htmlReady($fullname)]); ?>
                        <?= htmlReady($fullname) ?>
                    </a>
                </td>
                <? if($is_tutor) : ?>
                    <td>
                        <? if(!empty($leser['mkdate'])) : ?>
                            <?= strftime('%x %X', $leser['mkdate'])?>
                        <? endif ?>
                    </td>
                    <td>
                        <?= $this->render_partial('course/members/_studycourse.php',
                                ['studycourses' => new SimpleCollection(UserStudyCourse::findByUser_id($autor['user_id']))]) ?>
                    </td>
                <? endif ?>
                <td style="text-align: right">
                    <? $actionMenu = ActionMenu::get() ?>
                    <? if($user_id != $leser['user_id']) : ?>
                        <? $actionMenu->addLink(URLHelper::getLink('dispatch.php/messages/write',
                                ['filter'           => 'send_sms_to_all',
                                 'emailrequest'    => 1,
                                 'rec_uname'       => $leser['username'],
                                 'default_subject' => $subject]),
                                _('Nachricht mit Mailweiterleitung senden'),
                                Icon::create('mail', 'clickable', ['title' => sprintf('Nachricht mit Weiterleitung an %s senden', $fullname)]),
                                ['data-dialog' => '1']) ?>
                    <? endif ?>

                    <? if ($is_tutor) : ?>
                        <? $actionMenu->addLink($controller->url_for('course/members/cancel_subscription/singleuser/user/' . $leser['user_id']),
                                _('Aus Veranstaltung austragen'),
                                Icon::create('door-leave', 'clickable', ['title' => sprintf(_('%s austragen'),htmlReady($fullname))])) ?>
                    <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
        <? if ($is_tutor) : ?>
        <tfoot>
            <tr>
                <td colspan="6">
                    <select name="action_user" id="user_action" aria-label="<?= _('Aktion ausführen') ?>">
                        <option value="">- <?= _('Aktion auswählen') ?></option>
                        <option value="upgrade"><?= sprintf(_('Zu %s hochstufen'),
                                htmlReady($status_groups['autor'])) ?></option>
                            <?php if ($to_waitlist_actions) : ?>
                            <option value="to_admission_first"><?= _('An den Anfang der Warteliste verschieben') ?></option>
                            <option value="to_admission_last"><?= _('Ans Ende der Warteliste verschieben') ?></option>
                            <?php endif ?>
                        <option value="remove"><?= _('Austragen') ?></option>
                            <?php if($is_dozent) : ?>
                            <option value="to_course"><?= _('In andere Veranstaltung verschieben/kopieren') ?></option>
                            <?php endif ?>
                        <option value="message"><?=_('Nachricht senden')?></option>
                        <!--<option value="copy_to_course"><?= _('In Seminar verschieben/kopieren') ?></option>-->
                    </select>
                    <?= Button::create(_('Ausführen'), 'submit_user') ?>
                </td>
            </tr>
        </tfoot>
        <? endif ?>
    </table>
</form>
