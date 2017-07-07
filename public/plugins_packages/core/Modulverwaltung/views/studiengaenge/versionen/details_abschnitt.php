<td colspan="2">
    <table id="module_<?= $abschnitt->id ?>" class="default collapsable sortable">
        <colgroup>
            <col>
            <col style="width: 1%">
        </colgroup>
    <? //foreach ($abschnitt->getModulAssignments() as $assignment) : ?>
    <? foreach ($assignments as $assignment) : ?>
        <tbody class="<?= count($assignment->modul->modulteile) ? '' : 'empty' ?> <?= ($modul_id == $assignment->modul->id ? 'not-collapsed' : 'collapsed') ?><?= MvvPerm::haveFieldPermPosition($assignment, MvvPerm::PERM_WRITE) ? ' sort_items' : '' ?>" id="<?= join('_',  $assignment->getId()) ?>">
            <tr id="modul_<?= $assignment->modul->id ?>" class="header-row">
                <td class="toggle-indicator">
                    <? if (count($assignment->modul->modulteile)) : ?>
                    <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/modulteile/' . join('/', $assignment->getId())) ?>"><?= htmlReady($assignment->getDisplayName(true)) ?></a>
                    <? else : ?>
                    <?= htmlReady($assignment->getDisplayName(true)) ?>
                    <? endif; ?>
                    <? if (trim($assignment->modulcode) || trim($assignment->bezeichnung)) : ?>
                    <span style="color: #636a71; font-size: smaller;">
                        <? printf(_('Orig.: %s - %s'),
                                htmlReady(trim($assignment->modul->code) ?: trim($assignment->modulcode)),
                                htmlReady(trim($assignment->modul->getDeskriptor()->bezeichnung) ?: trim($assignment->bezeichnung))) ?>
                    <? endif; ?>
                </td>
                <td class="dont-hide" style="white-space: nowrap;">
                <? if (MvvPerm::havePermWrite($assignment)) : ?>
                    <a data-dialog="" href="<?= $controller->url_for('/modul_zuordnung', $abschnitt->id, $assignment->modul->id) ?>">
                        <?= Icon::create('edit', 'clickable', array('title' => _('Modulzuordnung bearbeiten')))->asImg(); ?>
                    </a>
                <? endif; ?>
                <? if (MvvPerm::havePermCreate($assignment)) : ?>
                    <a href="<?= $controller->url_for('/delete_modul', $abschnitt->id, $assignment->modul->id) ?>">
                        <?= Icon::create('trash', 'clickable', array('title' => _('Zuordnung löschen')))->asImg(); ?>
                    </a>
                <? endif; ?>
                </td>
            </tr>
            <? if ($modul_id == $assignment->modul->id) : ?>
            <tr class="loaded-details nohover">
                <?= $this->render_partial('studiengaenge/versionen/modulteile', array('modul' => $assignment->modul, 'abschnitt_id' => $abschnitt->id)) ?>
            </tr>
            <? endif; ?>
        </tbody>
    <? endforeach; TextHelper::reset_cycle(); ?>
    <? if (MvvPerm::haveFieldPermModul_zuordnungen($abschnitt, MvvPerm::PERM_CREATE)) : ?>
        <tfoot>
            <tr>
                <td colspan="2">
                    <form action="<?= $controller->url_for('/add_modul', $version->id) ?>" method="post">
                        <?= _('Modul hinzufügen') ?>
                        <?= CSRFProtection::tokenTag() ?>
                        <?= $search_modul_abschnitt->render(); ?>
                        <?= Icon::create('search', 'clickable', ['title' => _('Modul suchen'), 'name' => 'search_stgteil', 'data-qs_name' => $search_modul_abschnitt->getId(), 'data-qs_id' => $qs_search_modul_abschnitt_id, 'data-qs_submit' => 'no',  'class' => 'mvv-qs-button'])->asInput(); ?>
                        <input type="hidden" name="abschnitt_id" value="<?= $abschnitt->id ?>">
                        <input name="add_modul" class="text-top mvv-submit" type="image" title="<?= _('Modul hinzufügen') ?>" src="<?= Icon::create('accept', 'clickable')->asImagePath(); ?>">
                    </form>
                </td>
            </tr>
        </tfoot>
    <? endif; ?>
    </table>
</td>
