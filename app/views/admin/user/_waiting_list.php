<article id="<?= 'seminar_wait' ?>" class="<?= ContentBoxHelper::classes('seminar_wait') ?>">
    <header>
        <h1>
            <a href="<?= ContentBoxHelper::href('seminar_wait') ?>">
                <?= _('Übersicht Wartelisten von Veranstaltungen') ?>
            </a>
        </h1>
    </header>
    <section>
        <table class="default">
            <colgroup>
                <col style="width: 200px">
                <col>
                <col style="width: 15%">
                <col style="width: 15%">
            </colgroup>
            <thead>
                <tr>
                    <th><?= _('Veranstaltungsnummer') ?></th>
                    <th><?= _('Veranstaltung') ?></th>
                    <th><?= _('Status') ?></th>
                    <th style="text-align: center"><?= _('Position') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($memberships as $membership): ?>
                    <tr>
                        <td>
                            <a href="<?= URLHelper::getLink('seminar_main.php', ['auswahl' => $course->id]) ?>">
                                <?= htmlReady($membership->course->veranstaltungsnummer) ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?= URLHelper::getLink('seminar_main.php', ['auswahl' => $course->id]) ?>">
                                <?= htmlReady($membership->course->getFullName('type-name')) ?>
                            </a>
                        </td>
                        <td><?= htmlReady($membership->status) ?></td>
                        <td style="text-align: center"><?= htmlReady($membership->position) ?></td>
                    </tr>
                <? endforeach; ?>
            </tbody>
        </table>
    </section>
</article>