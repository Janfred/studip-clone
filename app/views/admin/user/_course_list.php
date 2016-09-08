<article id="<?= $class ?>" class="<?= ContentBoxHelper::classes($class) ?>">
    <header>
        <h1>
            <a href="<?= ContentBoxHelper::href($class) ?>">
                <?= $headline ?>
            </a>
        </h1>
    </header>
    <section>
        <table class="default">
            <colgroup>
                <col style="width: 200px">
                <col>
                <col style="width: 15%">
            </colgroup>
            <thead>
                <tr>
                    <th><?= _('Veranstaltungsnummer') ?></th>
                    <th><?= _('Veranstaltung') ?></th>
                    <th><?= _('Status') ?></th>
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
                            <a href="<?= URLHelper::getLink('seminar_main.php', ['auswahl' => $membership->course->id]) ?>">
                                <?= htmlReady($membership->course->getFullName('type-name')) ?>
                            </a>
                        </td>
                        <td>
                            <?= htmlReady($membership->status) ?>
                        </td>
                    </tr>
                <? endforeach; ?>
            </tbody>
        </table>
    </section>
</article>