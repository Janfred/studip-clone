<article id="<?= 'course_files' ?>" class="<?= ContentBoxHelper::classes('course_files') ?>">
    <header>
        <h1>
            <a href="<?= ContentBoxHelper::href('course_files') ?>">
                <?= _('Dateiübersicht Veranstaltungen') ?>
            </a>
        </h1>
    </header>
    <section>
        <table class="default">
            <colgroup>
                <col style="width: 200px">
                <col>
                <col style="width: 120px">
                <col style="width: 20px">
            </colgroup>
            <thead>
                <tr>
                    <th><?= _('Veranstaltungsnummer') ?></th>
                    <th><?= _('Veranstaltung') ?></th>
                    <th><?= _('Anzahl') ?></th>
                    <th class="actions"><?= _('Aktionen') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($course_files as $data): ?>
                    <tr>
                        <td><?= htmlReady($data['course']->veranstaltungsnummer) ?></td>
                        <td>
                            <?= htmlReady($data['course']->getFullName('type-name')) ?>
                        </td>
                        <td>
                            <? if ($data['files']) : ?>
                                <?= sprintf('%u %s', $data['files'], _('Dokumente')) ?>
                            <? else : ?>
                                -
                            <? endif ?>
                        </td>
                        <td class="actions">
                            <? if ($data['files']) : ?>
                                <?
                                $actionMenu = ActionMenu::get();
                                $actionMenu->addLink($controller->url_for('admin/user/list_files/' . $user['user_id'] . '/' . $data['course']->id),
                                        _('Dateien auflisten'),
                                        Icon::create('folder-full', 'clickable'),
                                        ['data-dialog' => 'size=50%']);
                                $actionMenu->addLink($controller->url_for('admin/user/download_user_files/' . $user['user_id'] . '/' . $data['course']->id),
                                        _('Dateien als ZIP herunterladen'),
                                        Icon::create('download', 'clickable'));

                                ?>

                                <?= $actionMenu->render() ?>
                            <? endif ?>
                        </td>

                    </tr>
                <? endforeach; ?>
            </tbody>
        </table>
    </section>
</article>