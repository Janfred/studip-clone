<td colspan="3">
    <table class="default">
        <colgroup>
            <col>
            <col style="width: 1%;">
        </colgroup>
        <tbody>
            <? foreach ($stgteile as $stgteil) : ?>
            <tr>
                <td>
                    <? if ($ampel_icon) : ?>
                        <?= $ampel_icon->asImg(['title' => $ampelstatus, 'style' => 'vertical-align: text-top;']) ?>
                    <? endif; ?>
                    <?= htmlReady($stgteil->getDisplayName()) ?>
                </td>
                <td class="actions" style="white-space: nowrap;">
                <? if (MvvPerm::havePermWrite($stgteil)) : ?>
                    <a href="<?= $controller->url_for('/stgteil', $stgteil->id) ?>">
                        <?= Icon::create('edit', 'clickable', array('title' => _('Studiengangteil bearbeiten')))->asImg(); ?>
                    </a>
                <? endif; ?>
                <? if (MvvPerm::havePermCreate($stgteil)) : ?>
                    <a href="<?= $controller->url_for('/copy', $stgteil->id) ?>">
                        <?= Icon::create('files', 'clickable', array('title' => _('Studiengangteil kopieren')))->asImg(); ?>
                    </a>
                <? endif; ?>
                <? if (MvvPerm::havePermCreate($stgteil)) : ?>
                    <a href="<?= $controller->url_for('/delete', $stgteil->id) ?>">
                        <?= Icon::create('trash', 'clickable', array('title' => _('Studiengangteil löschen')))->asImg(); ?>
                    </a>
                <? endif; ?>
                </td>
            </tr>
            <? endforeach; ?>
        </tbody>
    </table>
</td>
