<td colspan="3">
    <table id="abschnitte_<?= $version->id ?>" class="default collapsable sortable">
        <colgroup>
            <col>
            <col style="width: 1%;">
        </colgroup>
    <? foreach ($abschnitte as $abschnitt) : ?>
        <tbody id="<?= $abschnitt->id ?>" class="<?= ($abschnitt_id == $abschnitt->id ? 'not-collapsed' : 'collapsed') ?><?= MvvPerm::haveFieldPermPosition($abschnitt, MvvPerm::PERM_WRITE) ? ' sort_items' : '' ?>">
        <tr class="header-row" id="abschnittt_<?= $abschnitt->id ?>">
            <td class="toggle-indicator">
                <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/details_abschnitt', $abschnitt->id) ?>"><?= htmlReady($abschnitt->name) ?></a>
            </td>
            <td class="dont-hide actions" style="white-space: nowrap;">            
            <? if (MvvPerm::havePermWrite($version)) : ?>
                <a data-dialog title="<?= _('Studiengangteil-Abschnitt bearbeiten') ?>" href="<?= $controller->url_for('/abschnitt', $abschnitt->id) ?>">
                    <?= Icon::create('edit', 'clickable', array())->asImg(); ?>
                </a>
            <? endif; ?>
            <? if (MvvPerm::haveFieldPermAbschnitte($version, MvvPerm::PERM_CREATE)) : ?>
                <? if (!$abschnitt->count_module) : ?>
                    <a href="<?= $controller->url_for('/delete_abschnitt', $abschnitt->id) ?>">
                        <?= Icon::create('trash', 'clickable', array('title' => _('Studiengangteil-Abschnitt löschen')))->asImg(); ?>
                    </a>
                <? else : ?>
                    <?= Icon::create('trash', 'inactive', array('title' => _('Löschen nicht möglich')))->asImg(); ?>
                <? endif; ?>
            <? endif; ?>
            </td>
        </tr>
        <? if ($abschnitt_id == $abschnitt->id) : ?>
        <tr class="loaded-details nohover">
            <?= $this->render_partial('studiengaenge/versionen/details_abschnitt', compact('abschnitt')) ?>
        </tr>
        <? endif; ?>
        </tbody>
    <? endforeach; TextHelper::reset_cycle(); ?>
        <? if (count($version->abschnitte) > 0
                && MvvPerm::haveFieldPermModul_zuordnungen('StgteilAbschnitt', MvvPerm::PERM_CREATE)) : ?>
        <tfoot>
        <tr>
            <td colspan="3">
                <form class="mvv-qsform" action="<?= $controller->url_for('/add_modul', $version->id) ?>" method="post">
                    <?= _('Modul hinzufügen') ?>
                    <?= CSRFProtection::tokenTag() ?>
                    <?= $search_modul_version->render(); ?>
                    <?= Icon::create('search', 'clickable', ['title' => _('Modul suchen'), 'name' => 'search_stgteil', 'data-qs_name' => $search_modul_version->getId(), 'data-qs_id' => $qs_search_modul_version_id, 'data-qs_submit' => 'no',  'class' => 'mvv-qs-button'])->asInput(); ?>
                    <label>
                        <?= _('zu Abschnitt') ?>
                        <select name="abschnitt_id">
                        <? foreach ($abschnitte as $abschnitt) : ?>
                            <option value="<?= $abschnitt->id; ?>"><?= htmlReady($abschnitt->getDisplayName()) ?></option>
                        <? endforeach; ?>
                        </select>
                    </label>
                    <input name="add_modul" class="text-top mvv-submit" type="image" title="<?= _('Studiengangteil-Abschnitt hinzufügen') ?>" src="<?= Icon::create('accept', 'clickable')->asImagePath(); ?>">
                </form>
            </td>
        </tr>
        </tfoot>
    <? endif; ?>
    </table>
</td>