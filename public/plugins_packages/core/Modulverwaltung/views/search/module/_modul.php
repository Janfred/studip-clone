<tbody  class="<?= ($modul_id == $modul->id ? 'not-collapsed' : 'collapsed') ?>">
    <tr class="table-header header-row" id="modul_<?= $modul->id ?>">
        <td class="toggle-indicator">
            <a class="mvv-load-in-new-row" style="display: inline;" href="<?= $controller->url_for('/details/' . $modul->id . '/#' . $modul->id) ?>">
                <?= htmlReady($modul->getDisplayName(ModuleManagementModel::DISPLAY_CODE)) ?>
            </a>
            <a style="display: inline; background-image: none; padding-left: 5px;"
            	data-dialog title="<?= htmlReady($modul->getDisplayName(ModuleManagementModel::DISPLAY_CODE)) . ' (' . _('Vollständige Modulbeschreibung') . ')' ?>" href="<?= $controller->url_for('shared/modul/description/' . $modul->id) ?>">
                <?= Icon::create('log')->asImg(tooltip2(_('Vollständige Modulbeschreibung'))) ?> ?>
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
<? if ($details_id == $modul->id): ?>
    <?= $this->render_partial('search/module/details'); ?>
<? endif; ?>
</tbody>
