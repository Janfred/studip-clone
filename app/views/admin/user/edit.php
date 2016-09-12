<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<? if ($flash['delete']) : ?>
    <?= $this->render_partial('admin/user/_delete', ['data' => $flash['delete']]) ?>
<? endif ?>

<form method="post" action="<?= $controller->url_for('admin/user/edit/' . $user->id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default nohover collapsable">
        <caption>
            <?= _('Benutzerverwaltung für ') ?><?= htmlReady($user->getFullName()) ?>
            <?= ($prelim ? ' (' . _("vorläufiger Benutzer") . ')' : '') ?>
            <?= ($user->locked) ? '<br><span style="color: red">(' . _('gesperrt von') . ' ' . htmlReady(get_fullname($user->locked_by)) : '' ?>
            <?= ($user->lock_comment) ? ', Kommentar: ' . htmlReady($user->lock_comment) : '' ?>
            <?= ($user->locked) ? ')</span>' : '' ?>
        </caption>
        <colgroup>
            <col width="25%">
            <col>
            <col width="60px">
        </colgroup>
        <tbody>
            <tr class="header-row">
                <th colspan="3" class="toggle-indicator">
                    <a class="toggler"><?= _('Allgemeine Daten') ?></a>
                </th>
            </tr>
            <tr>
                <td>
                    <label for="username" class="required">
                        <?= _('Benutzername') ?>:
                    </label>
                </td>
                <td colspan="2">
                    <? if (StudipAuthAbstract::CheckField("auth_user_md5.username", $user->auth_plugin) || LockRules::check($user->user_id, 'username')) : ?>
                        <?= htmlReady($user->username) ?>
                    <? else : ?>
                        <input class="user_form" type="text" name="username" id="username"
                               value="<?= $user->username ?>" required>
                    <? endif ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="permission">
                        <?= _('globaler Status:') ?>
                    </label>
                </td>
                <td colspan="2">
                    <? if (StudipAuthAbstract::CheckField('auth_user_md5.perms', $user->auth_plugin)): ?>
                        <?= htmlReady($user->perms) ?>
                    <? else: ?>
                        <select name="perms[]" id="permission">
                            <? foreach (array_keys($GLOBALS['perm']->permissions) as $permission): ?>
                                <option <? if ($permission === $user->perms) echo 'selected'; ?>>
                                    <?= htmlReady($permission) ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                    <? endif; ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="visible"><?= _('Sichtbarkeit') ?>:</label>
                </td>
                <td colspan="2">
                    <? if (!$prelim): ?>
                        <?= vis_chooser($user->visible, false, 'visible') ?>
                    <? endif; ?>
                    <small>(<?= htmlReady($user->visible) ?>)</small>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="vorname" class="required"><?= _('Vorname') ?>:</label>
                </td>
                <td colspan="2">
                    <? if (StudipAuthAbstract::CheckField("auth_user_md5.Vorname", $user->auth_plugin) || LockRules::check($user->user_id, 'name')) : ?>
                        <?= htmlReady($user->vorname) ?>
                    <? else : ?>
                        <input class="user_form" type="text" name="Vorname" id="vorname"
                               value="<?= htmlReady($user->vorname) ?>" required>
                    <? endif ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="nachname" class="required"><?= _('Nachname') ?>:</label>
                </td>
                <td colspan="2">
                    <? if (StudipAuthAbstract::CheckField("auth_user_md5.Nachname", $user->auth_plugin) || LockRules::check($user->user_id, 'name')) : ?>
                        <?= htmlReady($user->nachname) ?>
                    <? else : ?>
                        <input class="user_form" type="text" name="Nachname" id="nachname"
                               value="<?= htmlReady($user->nachname) ?>" required>
                    <? endif ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="gender"><?= _('Geschlecht') ?>:</label>
                </td>
                <td colspan="2">
                    <? if (StudipAuthAbstract::CheckField("user_info.geschlecht", $user->auth_plugin) || LockRules::check($user->user_id, 'gender')): ?>
                        <?= (!$user->geschlecht ? _('unbekannt') : ($user->geschlecht == 1 ? _('männlich') : _('weiblich'))) ?>
                    <? else: ?>
                        <label>
                            <input type="radio" name="geschlecht" value="0"
                                    <? if (!$user->geschlecht) echo 'checked'; ?>>
                            <?= _('unbekannt') ?>
                        </label>
                        <label>
                            <input type="radio" name="geschlecht" value="1"
                                    <? if ($user->geschlecht == 1) echo 'checked'; ?>>
                            <?= _('männlich') ?>
                        </label>
                        <label>
                            <input type="radio" name="geschlecht" value="2"
                                    <? if ($user->geschlecht == 2) echo 'checked'; ?>>
                            <?= _('weiblich') ?>
                        </label>
                    <? endif; ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="title_front"><?= _('Titel') ?>:</label>
                </td>
                <td colspan="2">
                    <? if (StudipAuthAbstract::CheckField('user_info.title_front', $user->auth_plugin) || LockRules::check($user->user_id, 'title')): ?>
                        <?= htmlReady($user->title_front) ?>
                    <? else: ?>
                        <select name="title_front_chooser" id="title_front"
                                onchange="jQuery(this).next().val(this.value);">
                            <? foreach (Config::get()->TITLE_FRONT_TEMPLATE as $title): ?>
                                <option value="<?= htmlReady($title) ?>" <? if ($title == $user->title_front) echo 'selected'; ?>>
                                    <?= htmlReady($title) ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                        <input class="user_form" type="text" name="title_front"
                               value="<?= htmlReady($user->title_front) ?>">
                    <? endif; ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="title_rear"><?= _('Titel nachgestellt') ?>:</label>
                </td>
                <td colspan="2">
                    <? if (StudipAuthAbstract::CheckField('user_info.title_rear', $user->auth_plugin) || LockRules::check($user->user_id, 'title')): ?>
                        <?= htmlReady($user->title_rear) ?>
                    <? else : ?>
                    <select name="title_rear_chooser" id="title_rear" onchange="jQuery(this).next().val(this.value);">
                        <? foreach (Config::get()->TITLE_REAR_TEMPLATE as $rtitle): ?>
                            <option value="<?= htmlReady($rtitle) ?>" <? if ($rtitle == $user->title_rear) echo 'selected'; ?>>
                                <?= htmlReady($rtitle) ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                    <input class="user_form" type="text" name="title_rear"
                           value="<?= htmlReady($user->title_rear) ?>">
                </td>
                <? endif; ?>
            </tr>
        </tbody>
        <tbody>
            <tr class="header-row">
                <th colspan="3" class="toggle-indicator">
                    <a class="toggler"><b><?= _('Registrierungsdaten') ?></b></a>
                </th>
            </tr>

            <? if (!$user->locked) : ?>
                <tr>
                    <td>
                        <label for="locked"><?= _('Benutzer sperren') ?>:</label>
                    </td>
                    <td colspan="2">
                        <input type="checkbox" name="locked" id="locked" value="1"
                               data-activates=".user_form[name=locked_comment]">
                        <label>
                            <?= _('Kommentar') ?>:
                            <input class="user_form" name="locked_comment" type="text">
                        </label>
                    </td>
                </tr>
            <? endif ?>

            <? if ($GLOBALS['perm']->have_perm('root')
                   && Config::get()->ALLOW_ADMIN_USERACCESS && !StudipAuthAbstract::CheckField("auth_user_md5.password", $user->auth_plugin) && !$prelim
            ) : ?>
                <tr>
                    <td>
                        <label for="pass_1"><?= _('Neues Passwort') ?>:</label>
                    </td>
                    <td colspan="2">
                        <input class="user_form" name="pass_1" type="password" id="pass_1">
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="pass_2"><?= _('Passwortwiederholung') ?>:</label>
                    </td>
                    <td colspan="2">
                        <input class="user_form" name="pass_2" type="password" id="pass_2"
                               onkeyup="jQuery('#pw_success').toggle( jQuery('#pass_1').val()==$('#pass_2').val() )">
                        <?= Icon::create('accept', 'accept')->asImg(16, ["id" => 'pw_success', "style" => 'display: none']) ?>
                    </td>
                </tr>
            <? endif; ?>

            <tr>
                <td>
                    <label for="email" <? if (!$prelim) echo 'class="required"'; ?>>
                        <?= _('E-Mail') ?>:
                    </label>
                </td>
                <td colspan="2">
                    <? if (StudipAuthAbstract::CheckField("auth_user_md5.Email", $auth_plugin) || LockRules::check($user->user_id, 'email')) : ?>
                        <?= htmlReady($user->email) ?>
                    <? else : ?>
                        <input class="user_form" type="text" name="Email" id="email"
                               value="<?= htmlReady($user['Email']) ?>" <? if (!$prelim) echo 'required'; ?>>
                        <? if ($GLOBALS['MAIL_VALIDATE_BOX']) : ?>
                            <label>
                                <input type="checkbox" name="disable_mail_host_check" value="1">
                                <?= _('Mailboxüberprüfung deaktivieren') ?>
                            </label>
                        <? endif ?>
                    <? endif ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?= _('Zuletzt aktiv') ?>:
                </td>
                <td colspan="2">
                    <? if ($user->online->last_lifesign): ?>
                        <abbr title="<?= strftime('%x %X', $user->online->last_lifesign) ?>">
                            <?= reltime($user->online->last_lifesign, true, 2) ?>
                        </abbr>
                    <? else: ?>
                        <?= _('nie benutzt') ?>
                    <? endif; ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?= _('Registriert seit') ?>:
                </td>
                <td colspan="2">
                    <? if ($user->mkdate): ?>
                        <?= strftime('%x', $user->mkdate) ?>
                    <? else: ?>
                        <?= _('unbekannt') ?>
                    <? endif; ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="auth_plugin"><?= _('Authentifizierung') ?></label>
                </td>
                <td colspan="2">
                    <select name="auth_plugin" id="auth_plugin">
                        <? foreach ($available_auth_plugins as $key => $val): ?>
                            <option value="<?= strtolower($key) ?>" <? if (strcasecmp($key, $user->auth_plugin) == 0) echo 'selected'; ?>>
                                <?= $val ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </td>
            </tr>

            <? if ($user->validation_key) : ?>
                <tr>
                    <td>
                        <?= _('Validation-Key') ?>:
                    </td>
                    <td colspan="2">
                        <?= htmlReady($user->validation_key) ?>
                        <label>
                            <input type="checkbox" name="delete_val_key" value="1">
                            <?= _('löschen') ?>
                        </label>
                    </td>
                </tr>
            <? endif ?>

            <tr>
                <td>
                    <label for="expiration_date"><?= _('Ablaufdatum') ?>:</label>
                </td>
                <td colspan="2">
                    <input class="user_form" type="text"
                           name="expiration_date" id="expiration_date"
                           value="<? if (UserConfig::get($user->user_id)->EXPIRATION_DATE) echo strftime('%x', UserConfig::get($user->user_id)->EXPIRATION_DATE); ?>">
                    <label>
                        <input type="checkbox" onchange="jQuery('input[name=expiration_date]').val('');"
                               name="expiration_date_delete" value="1">
                        <?= _('löschen') ?>
                    </label>
                </td>
            </tr>
        </tbody>

        <? if (in_array($user->perms, words('autor tutor dozent'))) : ?>
            <tbody>
                <tr class="header-row">
                    <th colspan="3" class="toggle-indicator">
                        <a class="toggler"><b><?= _('Studiendaten') ?></b></a>
                    </th>
                </tr>
                <? if (!StudipAuthAbstract::CheckField('studiengang_id', $auth_plugin)) : ?>
                    <tr>
                        <td>
                            <label for="new_studiengang"><?= _('Neuer Studiengang') ?></label>
                        </td>
                        <td colspan="2">
                            <? $about->select_studiengang() ?>
                            <? $about->select_abschluss() ?>
                            <select name="fachsem">
                                <? for ($s = 1; $s < 51; $s++) : ?>
                                    <option><?= $s ?></option>
                                <? endfor ?>
                            </select>
                        </td>
                    </tr>
                <? endif ?>
                <? if (count($user->studycourses)) : ?>
                    <? foreach ($user->studycourses as $i => $usc) : ?>
                        <tr>
                            <td><?= sprintf('%u %s', $i + 1, _('Studiengang')) ?></td>
                            <td>
                                <?= sprintf('%s, %s, %s. %s',
                                        htmlReady($usc->studycourse->name),
                                        htmlReady($usc->degree->name),
                                        htmlReady($usc->semester),
                                        _('Fachsemester')) ?>
                                <? if (PluginEngine::getPlugin('MVVPlugin')) : ?>
                                    <? $versionen = StgteilVersion::findByFachAbschluss($usc->fach_id, $usc->abschluss_id); ?>
                                    <? $versionen = array_filter($versionen, function ($ver) {
                                        return $ver->hasPublicStatus('genehmigt');
                                    }); ?>
                                    <? if (count($versionen)) : ?><br>
                                        <select name="change_version[<?= $usc->fach_id ?>][<?= $usc->abschluss_id ?>]"
                                                aria-labelledby="version_label">
                                            <option value=""><?= _('-- Bitte Version auswählen --') ?></option>
                                            <? foreach ($versionen as $version) : ?>
                                                <option<?= $version->getId() == $usc->version_id ? ' selected' : '' ?>
                                                        value="<?= htmlReady($version->getId()) ?>">
                                                    <?= htmlReady($version->getDisplayName()) ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>,<br>
                                    <? else : ?>
                                        <?= tooltipIcon(_('Keine Version in der gewählten Fach-Abschluss-Kombination verfügbar.'), true) ?>
                                    <? endif; ?>
                                <? endif ?>
                            </td>
                            <td align="right">
                                <a href="<?= $controller->url_for('admin/user/delete_studycourse/' . $user->user_id . '/' . $usc->fach_id . '/' . $usc->abschluss_id) ?>">
                                    <?= Icon::create('trash', 'clickable')->asImg(['class' => 'text-top', 'title' => _('Diesen Studiengang löschen')]) ?>
                                </a>
                            </td>
                        </tr>
                    <? endforeach ?>
                <? endif ?>
                <tr>
                    <td>
                        <label for="new_student_inst"><?= _('Neue Einrichtung') ?></label>
                    </td>
                    <td colspan="2">
                        <select name="new_student_inst" id="new_student_inst" class="nested-select">
                            <option value="" class="is-placeholder">
                                <?= _('-- Bitte Einrichtung auswählen --') ?>
                            </option>
                            <? foreach ($available_institutes as $i) : ?>
                                <? if (!isset($institutes[$i['Institut_id']])) : ?>
                                    <option class="<?= $i['is_fak'] ? 'nested-item-header' : 'nested-item' ?>"
                                            value="<?= htmlReady($i['Institut_id']) ?>">
                                        <?= htmlReady(my_substr($i['Name'], 0, 70)) ?>
                                    </option>
                                <? endif; ?>
                            <? endforeach; ?>
                        </select>
                    </td>
                </tr>
                <? if (count($student_institutes) > 0) : ?>
                    <? foreach ($student_institutes as $i => $inst_membership) : ?>
                        <tr>
                            <td>
                                <?= $i + 1 ?>. <?= _('Einrichtung') ?>
                            </td>
                            <td>
                                <?= htmlReady($inst_membership->institute->name) ?>
                            </td>
                            <td align="right">
                                <? if ($GLOBALS['perm']->have_studip_perm('admin', $inst_membership->institut_id)) : ?>
                                    <a href="<?= $controller->url_for('admin/user/delete_institute/' . $user->user_id . '/' . $inst_membership->institut_id) ?>">
                                        <?= Icon::create('trash', 'clickable')->asImg(['class' => 'text-top', 'title' => _('Diese Einrichtung löschen')]) ?>
                                    </a>
                                <? endif; ?>
                            </td>
                        </tr>
                    <? endforeach; ?>
                <? endif; ?>
            </tbody>
        <? endif; ?>

        <? if ($user['perms'] !== 'root') : ?>
            <tbody>
                <tr class="header-row">
                    <th colspan="3" class="toggle-indicator">
                        <a class="toggler"><b><?= _('Einrichtungsdaten') ?></b></a>
                    </th>
                </tr>
                <tr>
                    <td>
                        <label for="new_inst"><?= _('Neue Einrichtung') ?></label>
                    </td>
                    <td colspan="2">
                        <select name="new_inst" id="new_inst" class="nested-select">
                            <option value="" class="is-placeholder">
                                <?= _('-- Bitte Einrichtung auswählen --') ?>
                            </option>
                            <? foreach ($available_institutes as $i) : ?>
                                <? if (InstituteMember::countBySql('user_id = ? AND institut_id = ?', [$user->user_id, $i['Institut_id']]) == 0
                                       && (!($i['is_fak'] && $user->perms == 'admin') || $GLOBALS['perm']->have_perm('root'))
                                ) : ?>
                                    <option class="<?= $i['is_fak'] ? 'nested-item-header' : 'nested-item' ?>"
                                            value="<?= htmlReady($i['Institut_id']) ?>">
                                        <?= htmlReady(my_substr($i['Name'], 0, 70)) ?>
                                    </option>
                                <? else: ?>
                                    <option class="<?= $i['is_fak'] ? 'nested-item-header' : 'nested-item' ?>" disabled>
                                        <?= htmlReady(my_substr($i['Name'], 0, 70)) ?>
                                    </option>
                                <? endif; ?>
                            <? endforeach; ?>
                        </select>
                    </td>
                </tr>
                <? if (count($institutes) > 0) : ?>
                    <? foreach ($institutes as $i => $inst_membership) : ?>
                        <tr>
                            <td>
                                <?= $i + 1 ?>. <?= _('Einrichtung') ?>
                            </td>
                            <td>
                                <?= htmlReady($inst_membership->institute->name) ?>
                            </td>
                            <td class="actions">
                                <? if ($GLOBALS['perm']->have_studip_perm("admin", $inst_membership->institut_id)) : ?>
                                    <a class="load-in-new-row"
                                       href="<?= $controller->url_for('admin/user/edit_institute/' . $user->user_id . '/' . $inst_membership->institut_id) ?>">
                                        <?= Icon::create('edit', 'clickable')->asImg(['class' => 'text-top', 'title' => _('Diese Einrichtung bearbeiten')]) ?>
                                    </a>
                                    <a href="<?= $controller->url_for('admin/user/delete_institute/' . $user->user_id . '/' . $inst_membership->institut_id) ?>">
                                        <?= Icon::create('trash', 'clickable')->asImg(['class' => 'text-top', 'title' => _('Diese Einrichtung löschen')]) ?>
                                    </a>
                                <? endif; ?>
                            </td>
                        </tr>
                    <? endforeach; ?>
                <? endif; ?>
            </tbody>
        <? endif; ?>

        <? if ($user->perms != 'root') : ?>
            <tbody>
                <tr class="header-row">
                    <th colspan="3" class="toggle-indicator">
                        <a class="toggler"><b><?= _('Nutzerdomänen') ?></b></a>
                    </th>
                </tr>
                <tr>
                    <td>
                        <label for="new_userdomain"><?= _('Neue Nutzerdomäne') ?></label>
                    </td>
                    <td colspan="2">
                        <? $about->select_userdomain() ?>
                    </td>
                </tr>
                <? if (count($userdomains) > 0): ?>
                    <? foreach ($userdomains as $i => $domain): ?>
                        <tr>
                            <td>
                                <?= $i + 1 ?>. <?= _('Nutzerdomäne') ?>
                            </td>
                            <td>
                                <?= htmlReady($domain->getName()) ?>
                            </td>
                            <td class="actions">
                                <a href="<?= $controller->url_for('admin/user/delete_userdomain/' . $user->user_id . '?domain_id=' . $domain->getID()) ?>">
                                    <?= Icon::create('trash', 'clickable')->asImg(['class' => 'text-top', 'title' => _('Aus dieser Nutzerdomäne austragen')]) ?>
                                </a>
                            </td>
                        </tr>
                    <? endforeach; ?>
                <? endif; ?>
            </tbody>
        <? endif; ?>

        <? if ($GLOBALS['perm']->have_perm('root') && count(LockRule::findAllByType('user')) > 0) : ?>
            <tbody>
                <tr class="header-row">
                    <th colspan="3" class="toggle-indicator">
                        <a class="toggler"><b><?= _('Sperrebene') ?></b></a>
                    </th>
                </tr>
                <tr>
                    <td>
                        <label for="lock_rule"><?= _('Sperrebene') ?></label>
                    </td>
                    <td colspan="2">
                        <select name="lock_rule" id="lock_rule">
                            <option value="none">
                                <?= _('-- Bitte Sperrebene auswählen --') ?>
                            </option>
                            <? foreach (LockRule::findAllByType('user') as $rule) : ?>
                                <option value="<?= $rule->getId() ?>" <? if ($user['lock_rule'] == $rule->getId()) echo 'selected'; ?>>
                                    <?= htmlReady($rule->name) ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                    </td>
                </tr>
            </tbody>
        <? endif ?>

        <? if (count($userfields) > 0) : ?>
            <tbody>
                <tr class="header-row">
                    <th colspan="3" class="toggle-indicator">
                        <a class="toggler"><b><?= _('Datenfelder') ?></b></a>
                    </th>
                </tr>
                <? foreach ($userfields as $entry) : ?>
                    <? if ($entry->isVisible()) : ?>
                        <tr>
                            <td>
                                <label for="datafields_<?= $entry->getID() ?>">
                                    <?= htmlReady($entry->getName()) ?>:
                                </label>
                                <? if (!$entry->isVisible($user->perms)): ?>
                                    <?= tooltipIcon(_('Systemfeld (für die Person selbst nicht sichtbar)'), true) ?>
                                <? endif; ?>
                            </td>
                            <td colspan="2">
                                <? if ($entry->isEditable() && !LockRules::Check($user->user_id, $entry->getId())) : ?>
                                    <?= $entry->getHTML("datafields") ?>
                                <? else : ?>
                                    <?= $entry->getDisplayValue() ?>
                                <? endif ?>
                            </td>
                        </tr>
                    <? endif ?>
                <? endforeach ?>
            </tbody>
        <? endif ?>
        <tbody>
            <tr>
                <td colspan="3">
                    <label>
                        <input name="u_edit_send_mail" value="1" checked type="checkbox">
                        <?= _('Emailbenachrichtigung bei Änderung der Daten verschicken?') ?>
                    </label>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:center">
                    <?= Button::createAccept(_('Speichern'), 'edit') ?>
                    <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/user/?reset'), ['name' => 'abort']) ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>

<script>
    jQuery('#expiration_date').datepicker();
</script>
