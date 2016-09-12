<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>

<br>

<form action="<?= $controller->url_for('admin/user/delete') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <caption>
            <?= sprintf(_("Suchergebnis: es wurden %s Personen gefunden"), count($users)) ?>
        </caption>
        <thead>
            <tr class="sortable">
                <th colspan="2" <?= ($sortby == 'username') ? 'class="sort' . $order . '"' : '' ?>>
                    <a href="<?= URLHelper::getLink('?sortby=username&order=' . $order . '&toggle=' . ($sortby == 'username')) ?>"><?= _("Benutzername") ?></a>
                </th>
                <th>
                    &nbsp;
                </th>
                <th <?= ($sortby == 'perms') ? 'class="sort' . $order . '"' : '' ?>>
                    <a href="<?= URLHelper::getLink('?sortby=perms&order=' . $order . '&toggle=' . ($sortby == 'perms')) ?>"><?= _("Status") ?></a>
                </th>
                <th <?= ($sortby == 'Vorname') ? 'class="sort' . $order . '"' : '' ?>>
                    <a href="<?= URLHelper::getLink('?sortby=Vorname&order=' . $order . '&toggle=' . ($sortby == 'Vorname')) ?>"><?= _("Vorname") ?></a>
                </th>
                <th <?= ($sortby == 'Nachname') ? 'class="sort' . $order . '"' : '' ?>>
                    <a href="<?= URLHelper::getLink('?sortby=Nachname&order=' . $order . '&toggle=' . ($sortby == 'Nachname')) ?>"><?= _("Nachname") ?></a>
                </th>
                <th <?= ($sortby == 'Email') ? 'class="sort' . $order . '"' : '' ?>>
                    <a href="<?= URLHelper::getLink('?sortby=Email&order=' . $order . '&toggle=' . ($sortby == 'Email')) ?>"><?= _("E-Mail") ?></a>
                </th>
                <th <?= ($sortby == 'changed') ? 'class="sort' . $order . '"' : '' ?>>
                    <a href="<?= URLHelper::getLink('?sortby=changed&order=' . $order . '&toggle=' . ($sortby == 'changed')) ?>"><?= _("inaktiv") ?></a>
                </th>
                <th <?= ($sortby == 'mkdate') ? 'class="sort' . $order . '"' : '' ?>>
                    <a href="<?= URLHelper::getLink('?sortby=mkdate&order=' . $order . '&toggle=' . ($sortby == 'mkdate')) ?>"><?= _("registriert seit") ?></a>
                </th>
                <th colspan="2" <?= ($sortby == 'auth_plugin') ? 'class="sort' . $order . '"' : '' ?>>
                    <a href="<?= URLHelper::getLink('?sortby=auth_plugin&order=' . $order . '&toggle=' . ($sortby == 'auth_plugin')) ?>"><?= _("Authentifizierung") ?></a>
                </th>
            </tr>
        </thead>

        <tbody>

            <? foreach ($users as $user) : ?>
                <tr>
                    <td style="white-space:nowrap;">
                        <input class="check_all" type="checkbox" name="user_ids[]" value="<?= $user['user_id'] ?>">
                        <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $user['username']]) ?>"
                           title="<?= _('Profil des Benutzers anzeigen') ?>">
                            <?= Avatar::getAvatar($user->user_id, $user->username)->getImageTag(Avatar::SMALL, ['title' => htmlReady($user->getFullName())]) ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $user->username]) ?>"
                           title="<?= _('Profil des Benutzers anzeigen') ?>">
                            <?= $user->username ?>
                        </a>
                    </td>
                    <td>
                        <?
                        $userdomains = UserDomain::getUserDomainsForUser($user->user_id);
                        $tooltxt     = _('Sichtbarkeit:') . ' ' . $user->visible;
                        if (!empty($userdomains)) {
                            $domains = [];
                            array_walk($userdomains, function ($a) use (&$domains) {
                                if(!in_array($a->getName(), $domains)) {
                                    $domains[] = $a->getName();
                                }
                            });
                            $tooltxt .= "\n" . _('Domänen:') . ' ' . implode(', ', $domains);
                        }
                        if ($user->locked == '1') {
                            $tooltxt .= "\n" . _("Nutzer ist gesperrt!");
                        }
                        ?>
                        <?= tooltipHtmlIcon(htmlReady($tooltxt, true, true)) ?>
                    </td>
                    <td>
                        <?= $user['perms'] ?>
                    </td>
                    <td>
                        <?= htmlReady($user->Vorname) ?>
                    </td>
                    <td>
                        <?= htmlReady($user->nachname) ?>
                    </td>
                    <td>
                        <?= htmlReady($user->email) ?>
                    </td>
                    <td>
                        <? if ($user->online->last_lifesign != "") :
                            $inactive = time() - $user->online->last_lifesign;
                            if ($inactive < 3600 * 24) {
                                $inactive = gmdate('H:i:s', $inactive);
                            } else {
                                $inactive = floor($inactive / (3600 * 24)) . ' ' . _('Tage');
                            }
                        else :
                            $inactive = _("nie benutzt");
                        endif ?>
                        <?= $inactive ?>
                    </td>
                    <td>
                        <?= ($user->mkdate) ? date("d.m.Y", $user->mkdate) : _('unbekannt') ?>
                    </td>
                    <td><?= htmlReady($user['auth_plugin'] == 'preliminary' ? _('vorläufig') : $user->auth_plugin) ?></td>
                    <td class="actions" nowrap>
                        <a href="<?= $controller->url_for('admin/user/edit/' . $user->user_id) ?>"
                           title="<?= _('Detailansicht des Benutzers anzeigen') ?>">
                            <?= Icon::create('edit', 'clickable', ['title' => _('Diesen Benutzer bearbeiten')])->asImg() ?>
                        </a>
                        <a href="<?= $controller->url_for('admin/user/delete/' . $user->user_id) ?>">
                            <?= Icon::create('trash', 'clickable', ['title' => _('Diesen Benutzer löschen')])->asImg() ?>
                        </a>
                    </td>
                </tr>
            <? endforeach ?>

        </tbody>

        <tfoot>

            <tr>
                <td colspan="11" align="right">
                    <input class="middle" type="checkbox" name="check_all" title="<?= _('Alle Benutzer auswählen') ?>"
                           data-proxyfor=".check_all">
                    <?= Button::create(_('Löschen'), ['title' => _('Alle ausgewählten Benutzer löschen')]) ?>
                </td>
            </tr>

        </tfoot>

    </table>
</form>
