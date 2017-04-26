<div class="profile-view">
    <div class="profile-view-aside">

    </div>

    <div class="profile-view-main">
        <h1><?= htmlReady($current_user->getFullName()) ?></h1>

        <? if ($motto) : ?>
            <h3><?= htmlReady($motto) ?></h3>
        <? endif ?>
        <p>
            <strong>
                <?= _('Profilbesuche:') ?>
            </strong>
            <?= object_return_views($current_user->user_id) ?>
        </p>
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
            <p>
                <strong><?= _("E-Mail:") ?></strong>
                <a href="mailto:<?= htmlReady($public_email) ?>">
                    <?= htmlReady($public_email) ?>
                </a>
            </p>
        <? endif; ?>

        <? if ($private_nr) : ?>
            <p>
                <strong><?= _("Telefon (privat):") ?></strong>
                <?= htmlReady($private_nr) ?>
            </p>
        <? endif ?>

        <? if ($private_cell) : ?>
            <p>
                <strong><?= _("Mobiltelefon:") ?></strong>
                <?= htmlReady($private_cell) ?>
            </p>
        <? endif ?>

        <? if ($skype_name) : ?>
            <p>
                <strong><?= _("Skype:") ?></strong>
                <?= htmlReady($skype_name) ?>
            </p>
        <? endif ?>

        <? if ($privadr) : ?>
            <p>
                <strong><?= _("Adresse (privat):") ?></strong>
                <?= htmlReady($privadr) ?>
            </p>
        <? endif ?>

        <? if ($homepage) : ?>
            <p>
                <strong><?= _("Homepage:") ?></strong>
                <?= formatLinks($homepage) ?>
            </p>
        <? endif ?>

        <? if ($perm->have_perm('root') && $current_user['locked']) : ?>
            <p style="color:red; font-weight: bold"><?= _("BENUTZER IST GESPERRT!") ?></p>
        <? endif ?>

        <? if (count($study_institutes) > 0): ?>
            <p><strong><?= _('Wo ich studiere:') ?></strong></p>
            <ul>
            <? foreach ($study_institutes as $inst_result) : ?>
                <li>
                    <a href="<?= $controller->link_for('institute/overview', ['auswahl' => $inst_result->institut_id]) ?>">
                        <?= htmlReady($inst_result->institute->name) ?>
                    </a>
                </li>
            <? endforeach ?>
            </ul>
        <? endif ?>

        <? if (count($institutes) > 0) : ?>
            <?= $this->render_partial("profile/working_place") ?>
        <? endif ?>

        <? if ($has_denoted_fields): ?>
            <p>
                <small>
                    * <?= _('Diese Felder sind nur für Sie und Admins sichtbar') ?>
                </small>
            </p>
        <? endif ?>

        <? if (isset($kings)): ?>
            <p>
                <?= $kings ?>
            </p>
        <? endif; ?>
        <? if (!empty($shortDatafields)) : ?>
            <? foreach ($shortDatafields as $name => $entry) : ?>
                <p>
                    <strong><?= htmlReady($name) ?>:</strong>
                    <?= $entry['content'] ?>
                    <span class="minor"><?= $entry['visible'] ?></span>
                </p>
            <? endforeach ?>
        <? endif ?>
    </div>
</div>

<?= $news ?>

<?= $dates ?>

<?= $evaluations ?>

<?= $questionnaires ?>

<? if (!empty($ausgabe_inhalt)) : ?>
    <? foreach ($ausgabe_inhalt as $key => $inhalt) : ?>
        <article class="studip">
            <header>
                <h1><?= htmlReady($key) ?></h1>
            </header>
            <section>
                <?= formatReady($inhalt) ?>
            </section>
        </article>
    <? endforeach ?>
<? endif ?>

<? if ($current_user['perms'] == 'dozent' && !empty($seminare)) : ?>
    <?= $this->render_partial("profile/seminare") ?>
<? endif ?>

<? if ($show_lit && $lit_list) : ?>
    <article class="studip">
        <header>
            <h1><?= _('Literaturlisten') ?></h1>
        </header>
        <section>
            <?= $lit_list ?>
        </section>
    </article>
<? endif ?>

<? if (!empty($longDatafields)) : ?>
    <? foreach ($longDatafields as $name => $entry) : ?>
        <article class="studip">
            <header>
                <h1><?= htmlReady($name . ' ' . $entry['visible']) ?></h1>
            </header>
            <section>
                <?= $entry['content'] ?>
            </section>
        </article>
    <? endforeach ?>
<? endif ?>

<?= $hompage_plugin ?>

<? if (!empty($categories)) : ?>
    <? foreach ($categories as $cat) : ?>
        <article class="studip">
            <header>
                <h1><?= htmlReady($cat['head'] . $cat['zusatz']) ?></h1>
            </header>
            <section>
                <?= formatReady($cat['content']) ?>
            </section>
        </article>
    <? endforeach ?>
<? endif; ?>
