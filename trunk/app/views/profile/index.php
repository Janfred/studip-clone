<div class="profile-view">
    <div class="profile-view-aside">
        <?= Avatar::getAvatar($current_user->user_id)->getImageTag(Avatar::NORMAL, [
            'class' => 'profile-avatar',
        ]) ?>
        <br>
        <br>
        <?= _('Profilbesuche:') ?>
        <?= object_return_views($current_user->user_id) ?>
        <br>
        <? if ($score && $score_title) : ?>
            <br>
            <a href="<?= $controller->link_for("score") ?>" <?= tooltip(_("Zur Rangliste")) ?>>
                <?= _('Stud.IP-Punkte') ?>: <?= $score ?>
                <br>
                <?= _('Rang') ?>: <?= $score_title ?>
            </a>
        <? endif ?>

        <br>
        <ul class="profile-view-actions">
    <? if ($current_user->username != $user->username): ?>
        <? if (!$user->isFriendOf($current_user)): ?>
            <li>
                <a href="<?= $controller->link_for('profile/add_buddy?username=' . $current_user->username) ?>">
                    <?= Icon::create('person', 'clickable')->asImg([
                            'title' => _('Zu den Kontakten hinzufügen'),
                    ]) ?>
                    <?= _('zu den Kontakten hinzufügen') ?>
                </a>
            </li>
        <? endif; ?>

            <li>
                <a href="<?= $controller->link_for('messages/write', ['rec_uname' => $current_user->username]) ?>" data-dialog="size=50%">
                    <?= Icon::create('mail', 'clickable')->asImg([
                            'title' => _('Nachricht an Nutzer verschicken'),
                    ]) ?>
                    <?= _('Nachricht schreiben') ?>
                </a>
            </li>

        <? if (class_exists('Blubber')): ?>
            <li>
                <a href="<?= URLHelper::getLink('plugins.php/blubber/streams/global', ['mention' => $current_user->username]) ?>">
                    <?= Icon::create('blubber', 'clickable')->asImg([
                            'title' => _('Blubber diesen Nutzer an'),
                    ]) ?>
                    <?= _('Anblubbern') ?>
                </a>
            </li>
        <? endif; ?>
    <? endif; ?>

        <li>
            <a href="<?= $controller->link_for("contact/vcard", ['user[]' => $current_user->username]) ?>">
                <?= Icon::create('vcard', 'clickable')->asImg([
                        'title' => _('vCard herunterladen'),
                ]) ?>
                <?= _('vCard herunterladen') ?>
            </a>
        </li>

    <? if ($current_user->username !== $user->username && $perm->have_perm('root')): ?>
        <li>
            <a href="<?= $controller->link_for('dispatch.php/admin/user/edit/' . $current_user->user_id) ?>">
                <?= Icon::create('edit', 'clickable')->asImg([
                        'title' => _('Dieses Konto bearbeiten'),
                ]) ?>
                <?= _('Dieses Konto bearbeiten') ?>
            </a>
        </li>
    <? endif; ?>
        </ul>
    </div>
    
    <div class="profile-view-main">
        <h1><?= htmlReady($current_user->getFullName()) ?></h1>

    <? if ($motto) : ?>
        <h3><?= htmlReady($motto) ?></h3>
    <? endif ?>

    <? if (!get_visibility_by_id($current_user->user_id)): ?>
        <p style="color: red;">
        <? if ($current_user->user_id !== $user->user_id): ?>
            <?= _('(Dieser Nutzer ist unsichtbar.)') ?>
        <? else: ?>
            <?= _('(Sie sind unsichtbar. Deshalb können nur Sie diese Seite sehen.)') ?>
        <? endif; ?>
        </p>
    <? endif; ?>
    <? if ($current_user->auth_plugin === null): ?>
        <p style="color:red;">
            <?= _("(vorläufiger Benutzer)") ?>
        </p>
    <? endif; ?>
    <? if ($public_email): ?>
        <strong><?= _("E-Mail:") ?></strong>
        <a href="mailto:<?= htmlReady($public_email) ?>">
            <?= htmlReady($public_email) ?>
        </a>
        <br/>
    <? endif; ?>

    <? if ($private_nr) : ?>
        <strong><?= _("Telefon (privat):") ?></strong>
        <?= htmlReady($private_nr) ?>
        <br/>
    <? endif ?>

    <? if ($private_cell) : ?>
        <strong><?= _("Mobiltelefon:") ?></strong>
        <?= htmlReady($private_cell) ?>
        <br/>
    <? endif ?>

    <? if ($skype_name) : ?>
        <strong><?= _("Skype:") ?></strong>
        <?= htmlReady($skype_name) ?>
        <br/>
    <? endif ?>

    <? if ($privadr) : ?>
        <strong><?= _("Adresse (privat):") ?></strong>
        <?= htmlReady($privadr) ?>
        <br/>
    <? endif ?>

    <? if ($homepage) : ?>
        <strong><?= _("Homepage:") ?></strong>
        <?= formatLinks($homepage) ?>
        <br/>
    <? endif ?>

    <? if ($perm->have_perm('root') && $current_user['locked']) : ?>
        <br>
        <strong><font color="red"><?= _("BENUTZER IST GESPERRT!") ?></font></strong>
        <br>
    <? endif ?>

    <? if (count($study_institutes) > 0): ?>
        <br>
        <strong><?= _('Wo ich studiere:') ?></strong>
        <br>
        <? foreach ($study_institutes as $inst_result) : ?>
            <a href="<?= $controlle->link_for('institute/overview', ['auswahl' => $inst_result->institut_id]) ?>">
                <?= htmlReady($inst_result->institute->name) ?>
            </a>
            <br>
        <? endforeach ?>
        <br/>
    <? endif ?>

    <? if (count($institutes) > 0) : ?>
        <?= $this->render_partial("profile/working_place") ?>
    <? endif ?>

    <? if ($has_denoted_fields): ?>
        <br>* <?= _('Diese Felder sind nur für Sie und Admins sichtbar') ?><br>
    <? endif ?>
    <br>
    <? if (isset($kings)): ?>
        <?= $kings ?><br>
    <? endif; ?>
    <? if (!empty($shortDatafields)) : ?>
        <? foreach ($shortDatafields as $name => $entry) : ?>
            <strong><?= htmlReady($name) ?>:</strong>
            <?= $entry['content'] ?>
            <span class="minor"><?= $entry['visible'] ?></span>
            <br>
        <? endforeach ?>
    <? endif ?>
    </div>
</div>

<br>

<?= $news ?>

<?= $dates ?>

<?= $evaluations ?>

<?= $questionnaires ?>

<? if (!empty($ausgabe_inhalt)) : ?>
    <? foreach ($ausgabe_inhalt as $key => $inhalt) : ?>
        <section class="contentbox">
            <header>
                <h1><?= htmlReady($key) ?></h1>
            </header>
            <section>
                <?= formatReady($inhalt) ?>
            </section>
        </section>
    <? endforeach ?>
<? endif ?>

<? if ($current_user['perms'] == 'dozent' && !empty($seminare)) : ?>
    <?= $this->render_partial("profile/seminare") ?>
<? endif ?>

<? if ($show_lit && $lit_list) : ?>
    <section class="contentbox">
        <header>
            <h1><?= _('Literaturlisten') ?></h1>
        </header>
        <section>
            <?= $lit_list ?>
        </section>
    </section>
<? endif ?>

<? if (!empty($longDatafields)) : ?>
    <? foreach ($longDatafields as $name => $entry) : ?>
        <section class="contentbox">
            <header>
                <h1><?= htmlReady($name . ' ' . $entry['visible']) ?></h1>
            </header>
            <section>
                <?= $entry['content'] ?>
            </section>
        </section>
    <? endforeach ?>
<? endif ?>

<?= $hompage_plugin ?>

<? if (!empty($categories)) : ?>
    <? foreach ($categories as $cat) : ?>
        <section class="contentbox">
            <header>
                <h1><?= htmlReady($cat['head'] . $cat['zusatz']) ?></h1>
            </header>
            <section>
                <?= formatReady($cat['content']) ?>
            </section>
        </section>
    <? endforeach ?>
<? endif; ?>
