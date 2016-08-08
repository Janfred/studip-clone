<tbody  class="<?= ($modul_id == $modul->id ? 'not-collapsed' : 'collapsed') ?>">
    <tr class="table-header header-row" id="modul_<?= $modul->id ?>">
            <td class="toggle-indicator">
                <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/details/' . $modul->id . '/' . '#' . $modul->id) ?>">
                    <?= htmlReady($modul->getDisplayName(true, true)) ?>
                </a>
            </td>
            <td class="dont-hide">
                <?= htmlReady($modul->getDisplaySemesterValidity()) ?>
            </td>
            <td class="dont-hide">
                <? if ($modul->responsible_institute->institute) : ?>
                    <?=  htmlReady($modul->responsible_institute->institute->getDisplayName()); ?>
                <? endif; ?>
            </td>
    </tr>
    <? if ($details_id == $modul->getId()) : ?>
    <?= $this->render_partial('search/module/details'); ?>
    <? endif; ?>
</tbody>
