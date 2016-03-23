<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

    if ($th_title) {
        ?>
        <tr><th colspan="<?= sizeof($structure) - ($structure['actions'] ? 1 : 0) ?>">
        <?= htmlReady($th_title) ?>
        </th>
        <? if ($structure['actions']) : ?>
            <th class="actions">
        <? if ($mail_status) {
            ?>
                <a data-dialog href="<?= URLHelper::getScriptLink("dispatch.php/messages/write", array('filter' => 'inst_status', 'who' => $key, 'default_subject' => $GLOBALS['SessSemName'][0], 'course_id' => $GLOBALS['SessSemName'][1])) ?>">
                    <?= Icon::create('mail', 'clickable', ['title' => sprintf(_('Nachricht an alle Mitglieder mit dem Status %s verschicken'),$th_title)])->asImg() ?>
                </a>
            <?
        } elseif ($mail_gruppe) {
            ?>
                <a data-dialog href="<?= URLHelper::getScriptLink("dispatch.php/messages/write", array('group_id' => $role_id, 'default_subject' => $GLOBALS['SessSemName'][0])) ?>">
                    <?= Icon::create('mail', 'clickable', ['title' => sprintf(_('Nachricht an alle Mitglieder der Gruppe %s verschicken'),$th_title)])->asImg() ?>
                </a>
            <?
        } ?>
            <?= Assets::img('blank.gif', array('width' => 16, 'height' => 16)) ?>
            </th>
        <? endif ?>
        </tr>
        <?
    }

    foreach ($members as $member) {

        $default_entries = DataFieldEntry::getDataFieldEntries(array($member['user_id'], $range_id));

        if ($member['statusgruppe_id']) {
            $role_entries = DataFieldEntry::getDataFieldEntries(array($member['user_id'], $member['statusgruppe_id']));
        }

        print "<tr>\n";

        print '<td>';
        if ($admin_view) {
            printf("<a href=\"%s\">%s</a>\n",
            URLHelper::getLink("dispatch.php/settings/statusgruppen?username={$member['username']}&open={$range_id}#{$range_id}"), htmlReady($member['fullname']));
        } else {
            echo '<a href="'.URLHelper::getLink('dispatch.php/profile?username='.$member['username']).'">'. htmlReady($member['fullname']) .'</a>';
        }
        echo '</td>';

        if ($structure["status"]) {
            printf("<td>%s</td>\n", htmlReady($member['inst_perms']));
        }

        if ($structure["statusgruppe"]) {
            print "<td></td>\n";
        }

        if ($structure['raum']) echo '<td>'. htmlReady($member['raum']) .'</td>';
        if ($structure['sprechzeiten']) echo '<td>'. htmlReady($member['sprechzeiten']) .'</td>';
        if ($structure['telefon']) echo '<td>'. htmlReady($member['Telefon']) .'</td>';
        if ($structure['email']) echo '<td>'. htmlReady($member['Email']) .'</td>';
        if ($structure['homepage']) echo '<td>'. htmlReady($member['Home']) .'</td>';

        foreach ($datafields_list as $entry) {
            if ($structure[$entry->getId()]) {
                $value = '';
                if ($role_entries[$entry->getId()]) {
                    if ($role_entries[$entry->getId()]->getValue() == 'default_value') {
                        $value = $default_entries[$entry->getId()]->getDisplayValue();
                    } else {
                        $value = $role_entries[$entry->getId()]->getDisplayValue();
                    }
                } else {
                    if ($default_entries[$entry->getId()]) {
                        $value = $default_entries[$entry->getId()]->getDisplayValue();
                    }
                }

                printf("<td>%s</td>\n", $value);
            }
        }

        if ($structure["actions"]) {
            print "<td class=\"actions\" nowrap>\n";
            printf("<a href=\"%s\" data-dialog>", URLHelper::getScriptLink("dispatch.php/messages/write?rec_uname=".$member['username']));
            print Icon::create('mail', 'clickable', ['title' => _('Nachricht an Benutzer verschicken')])->asImg(['valign' => 'baseline']);
            print "</a>\n";

            if ($admin_view && !LockRules::Check($range_id, 'participants')
                && ($member['inst_perms'] != 'admin'
                || ($GLOBALS['perm']->get_profile_perm($member['user_id']) == 'admin'
                && $member['user_id'] != $GLOBALS['user']->id))) {
                if ($member['statusgruppe_id']) {    // if we are in a view grouping by statusgroups
                    echo '<a href="'.URLHelper::getLink('?cmd=removeFromGroup&username='.$member['username'].'&role_id='. $member['statusgruppe_id']).'">';
                } else {
                    echo '<a href="'.URLHelper::getLink('?cmd=removeFromInstitute&username='.$member['username']).'">';
                }
                echo Icon::create('trash', 'clickable')->asImg();
                echo "</a>\n";
            }
            print '</td>';
        }

        echo "</tr>\n";

        // Statusgruppen kommen in neue Zeilen
        if ($structure["statusgruppe"]) {
            $statusgruppen = GetStatusgruppenForUser($member['user_id'], array_keys((array)$group_list));
            if (is_array($statusgruppen)) {
                foreach ($statusgruppen as $id) {
                    $entries = DataFieldEntry::getDataFieldEntries(array($member['user_id'], $id));

                    echo '<tr><td></td>';
                    if ($structure["status"]) {
                        echo '<td></td>';
                    }

                    echo '<td>';

                    if ($admin_view) {
                        echo '<a href="'.URLHelper::getLink('admin_statusgruppe.php?role_id='.$id.'&cmd=displayRole').'">'.htmlReady($group_list[$id]).'</a>';
                    } else {
                        echo htmlReady($group_list[$id]);
                    }

                    echo '</td>';

                    if ($structure['raum']) echo '<td></td>';
                    if ($structure['sprechzeiten']) echo '<td></td>';
                    if ($structure['telefon']) echo '<td></td>';
                    if ($structure['email']) echo '<td></td>';
                    if ($structure['homepage']) echo '<td></td>';

                    if (sizeof($entries) > 0) {
                        foreach ($entries as $e_id => $entry) {
                            if (in_array($e_id, $dview) === TRUE) {
                                echo '<td>';
                                if ($entry->getValue() == 'default_value') {
                                    echo $default_entries[$e_id]->getDisplayValue();
                                } else {
                                    echo $entry->getDisplayValue();
                                }
                                echo '</td>';
                            }
                        }
                    }
                    if ($structure['actions']) {
                        echo "<td class=\"actions\" nowrap>\n";
                        if ($admin_view && !LockRules::Check($range_id, 'participants')) {
                            echo '<a href="'.URLHelper::getLink('dispatch.php/settings/statusgruppen/switch/' . $id . '?username='.$member['username']).'">';
                            echo Icon::create('edit', 'clickable')->asImg();
                            echo "</a>\n";

                            echo '<a href="'.URLHelper::getLink('?cmd=removeFromGroup&username='.$member['username'].'&role_id='.$id).'">';
                            echo Icon::create('trash', 'clickable')->asImg();
                            echo '</a>';
                        }
                        echo '</td>';
                    }
                    echo '</tr>', "\n";
                }
            }
        }
    }
