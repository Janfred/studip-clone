<tbody>
<? if ($th_title): ?>
    <tr>
        <th colspan="<?= count($structure) - ($structure['actions'] ? 1 : 0) ?>">
            <?= htmlReady($th_title) ?>
        </th>
    <? if ($structure['actions']): ?>
        <th class="actions">
        <?= ActionMenu::get()
            ->condition($mail_status)
            ->addLink(
                URLHelper::getLink('dispatch.php/messages/write?filter=inst_status', [
                    'who'             => $key,
                    'default_subject' => $GLOBALS['SessSemName'][0],
                    'course_id'       => $GLOBALS['SessSemName'][1],
                ]),
                sprintf(_('Nachricht an alle Mitglieder mit dem Status %s verschicken'), $th_title),
                Icon::create('mail', 'clickable'),
                ['data-dialog' => '']
            )
            ->condition($mail_gruppe)
            ->addLink(
                URLHelper::getScriptLink('dispatch.php/messages/write', [
                    'group_id'        => $role->id,
                    'default_subject' => $GLOBALS['SessSemName'][0],
                ]),
                sprintf(_('Nachricht an alle Mitglieder der Gruppe %s verschicken'), $th_title),
                Icon::create('mail', 'clickable'),
                ['data-dialog' => '']
            ) ?>
        </th>
    <? endif; ?>
    </tr>
<? endif; ?>
<? foreach ($members as $member):
        $default_entries = DataFieldEntry::getDataFieldEntries(array($member->user_id, $range_id));

        if ($role) {
            $role_entries = DataFieldEntry::getDataFieldEntries(array($member->user_id, $role->id));
        }
?>
    <tr>
        <td>
        <? if ($admin_view): ?>
            <a href="<?= URLHelper::getLink("dispatch.php/settings/statusgruppen?username={$member->username}&contentbox_open={$range_id}#{$range_id}") ?>">
                <?= htmlReady($member->getUserFullname('full_rev')) ?>
            </a>
        <? else: ?>
            <a href="<?= URLHelper::getLink("dispatch.php/profile?username={$member->username}") ?>">
                <?= htmlReady($member->getUserFullname('full_rev')) ?>
            </a>
        <? endif; ?>
        </td>
    <? if ($structure['status']): ?>
        <td><?= htmlReady($member->inst_perms) ?></td>
    <? endif; ?>
    <? if ($structure['statusgruppe']): ?>
        <td></td>
    <? endif; ?>
    <? if ($structure['raum']): ?>
        <td><?= htmlReady($member->raum) ?></td>
    <? endif; ?>
    <? if ($structure['sprechzeiten']): ?>
        <td><?= htmlReady($member->sprechzeiten) ?></td>
    <? endif; ?>
    <? if ($structure['telefon']): ?>
        <td><?= htmlReady($member->Telefon) ?></td>
    <? endif; ?>
    <? if ($structure['email']): ?>
        <td><?= htmlReady(get_visible_email($member->user_id)) ?></td>
    <? endif; ?>
    <? if ($structure['homepage']): ?>
        <td><?= htmlReady($member->user_info->Home) ?></td>
    <? endif; ?>
    <? foreach (array_filter($datafields_list, function ($e) use ($structure) { return isset($structure[$e->getId()]); }) as $entry): ?>
        <td>
        <? if ($role_entries[$entry->getId()] && $role_entries[$entry->getId()]->getValue() !== 'default_value'): ?>
            <?= $role_entries[$entry->getId()]->getDisplayValue() ?>
        <? elseif ($default_entries[$entry->getId()]): ?>
            <?= $default_entries[$entry->getId()]->getDisplayValue() ?>
        <? endif; ?>
        </td>
    <? endforeach; ?>
    <? if ($structure['actions']): ?>
        <td class="actions">
        <?= ActionMenu::get()
            ->addLink(
                URLHelper::getLink("dispatch.php/messages/write?rec_uname={$member->username}"),
                _('Nachricht an Benutzer verschicken'),
                Icon::create('mail', 'clickable'),
                ['data-dialog' => '']
            )
            ->conditionAll(
                $admin_view && !LockRules::Check($range_id, 'participants') // General permission check
                && ($member->inst_perms !== 'admin' // Don't delete admins
                    || ($GLOBALS['perm']->get_profile_perm($member->user_id) === 'admin' // unless you are a global admin yourself
                        && $member->user_id !== $GLOBALS['user']->id)) // but don't delete yourself
            )
            ->condition(isset($role))
            ->addLink(
                $controller->link_for('institute/members/remove_from_group/' . $role->id, ['username' => $member->username]),
                _('Person aus Gruppe austragen'),
                Icon::create('door-leave', 'clickable'),
                ['data-confirm' => _('Wollen Sie die Person wirklich aus der Gruppe austragen?')]
            )
            ->condition(!isset($role))
            ->addLink(
                URLHelper::getLink("institute/members/remove_from_institute?username={$member->username}"),
                _('Person aus Einrichtung austragen'),
                Icon::create('door-leave', 'clickable'),
                ['data-confirm' => _('Wollen Sie die Person wirklich aus der Einrichtung austragen?')]
            ) ?>
        </td>
    <? endif; ?>
    </tr>
<? if ($structure['statusgruppe']): ?>
    <? foreach ($groups->filter(function ($group) use ($member) { return $group->isMember($member->user_id); }) as $group):
        $group_member = $group->members->findOneBy('user_id', $member->user_id);
    ?>
        <tr>
            <td></td>
        <? if ($structure['status']): ?>
            <td></td>
        <? endif; ?>
            <td>
            <? if ($admin_view): ?>
                <a href="<?= URLHelper::getLink('dispatch.php/admin/statusgroups/editGroup/' . $group->id) ?>">
            <? endif; ?>
                <?= htmlReady($group->getFullGenderedName($member->user_id)) ?>
            <? if ($admin_view): ?>
                </a>
            <? endif; ?>
            </td>
        <? if ($structure['raum']): ?>
            <td></td>
        <? endif; ?>
        <? if ($structure['sprechzeiten']): ?>
            <td></td>
        <? endif; ?>
        <? if ($structure['telefon']): ?>
            <td></td>
        <? endif; ?>
        <? if ($structure['email']): ?>
            <td></td>
        <? endif; ?>
        <? if ($structure['homepage']): ?>
            <td></td>
        <? endif; ?>
        <? foreach ($group_member->datafields->filter(function ($e) use ($dview) { return in_array($e->getId(), $dview); }) as $entry): ?>
            <td>
            <? if ($entry->getValue() === 'default_value'): ?>
                <?= $default_entries[$e_id]->getDisplayValue() ?>
            <? else: ?>
                <?= $entry->getDisplayValue() ?>
            <? endif; ?>
            </td>
        <? endforeach; ?>
        <? if ($structure['actions']): ?>
            <td class="actions">
            <?= ActionMenu::get()
                ->conditionAll($admin_view && !LockRules::Check($range_id, 'participants'))
                ->addLink(
                    URLHelper::getLink("dispatch.php/settings/statusgruppen?username={$member->username}" .
                                       "&contentbox_open={$group->id}#{$group->id}"),
                    _('Gruppendaten bearbeiten'),
                    Icon::create('edit', 'clickable')
                )
                ->addLink(
                    URLHelper::getLink("?cmd=removeFromGroup&username={$member->username}&role_id={$group->id}"),
                    _('Person aus Gruppe austragen'),
                    Icon::create('door-leave', 'clickable')
                ) ?>
            </td>
        <? endif; ?>
        </tr>
    <? endforeach; ?>
<? endif; ?>
<? endforeach; ?>
</tbody>
