<aside id="folder_aside">
    <div class="folder-icon"><?= $folder->getIcon('info') ?></div>

    <table class="default nohover">
        <caption><?= htmlReady($folder->name) ?></caption>
        <tbody>
            <tr>
                <td><?= _('Erstellt') ?></td>
                <td><?= date('d.m.Y H:i', $folder->mkdate) ?></td>
            </tr>
            <tr>
                <td><?= _('Geändert') ?></td>
                <td><?= date('d.m.Y H:i', $folder->chdate) ?></td>
            </tr>
            <tr>
                <td><?= _('Besitzer/-in') ?></td>
                <td>
                <? if ($folder->owner): ?>
                    <?= htmlReady($folder->owner->getFullName()) ?>
                <? endif ?>
                </td>
            </tr>
        </tbody>
    </table>
</aside>
