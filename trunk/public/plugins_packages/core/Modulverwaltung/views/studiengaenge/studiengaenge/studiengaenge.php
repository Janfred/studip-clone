<? foreach ($studiengaenge as $studiengang) : ?>
    <? $perm = new MvvPerm($studiengang) ?>
    <tbody class="<?= ($studiengang_id == $studiengang->id ? 'not-collapsed' : 'collapsed') ?>">
        <tr class="table-header header-row" id="studiengang_<?= $studiengang->id ?>">
            <td class="toggle-indicator">
                <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/details', $studiengang->id) ?>">
                <? $ampel_icon = $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'][$studiengang->stat]['icon'] ?>
                <? $ampelstatus = $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'][$studiengang->stat]['name'] ?>
                <? if ($ampel_icon) : ?>
                    <?= $ampel_icon->asImg(['title' => $ampelstatus, 'style' => 'vertical-align: text-top;']) ?>
                <? endif; ?>
                <?= htmlReady($studiengang->name) ?> <?= ($studiengang->name_kurz ? '(' . htmlReady($studiengang->name_kurz) . ')' : '') ?>
                <? if ($studiengang->count_dokumente) : ?>
                    <?= Icon::create('staple', 'info', ['title' => sprintf(ngettext('%s Dokument zugeordnet', '%s Dokumente zugeordnet', $studiengang->count_dokumente), $studiengang->count_dokumente), 'style' => 'vertical-align: text-top;']) ?>
                <? endif; ?>
                 </a>
            </td>
            <td class="dont-hide">
                <?= htmlReady($studiengang->institut_name) ?>
            </td>
            <td class="dont-hide">
                <?= htmlReady($studiengang->kategorie_name) ?>
            </td>
            <td class="actions dont-hide">
            <? if ($studiengang->stat == 'planung' && MvvPerm::haveFieldPermStat($studiengang)) : ?>
                <a data-dialog="title='<?= $studiengang->getDisplayName() ?>'" href="<?= $controller->url_for('/approve', $studiengang->id) ?>">
                    <?= Icon::create('accept', 'clickable', ['title' => _('Studiengang genehmigen')]) ?>
                </a>
            <? endif; ?>            
            <? if ($perm->havePerm(MvvPerm::PERM_WRITE)) : ?>
                <a href="<?= $controller->url_for('/studiengang', $studiengang->id) ?>">
                    <?= Icon::create('edit', ['title' => _('Studiengang bearbeiten')]) ?>
                </a>
            <? endif; ?>
            <? if ($perm->havePerm(MvvPerm::PERM_CREATE)) : ?>
                <? if (!$studiengang->count_faecher) : ?>
                <a href="<?= $controller->url_for('/delete',  $studiengang->id) ?>">
                    <?= Icon::create('trash', 'clickable', ['title' => _('Studiengang löschen')]) ?>
                </a>
                <? else : ?>
                    <?= Icon::create('trash', 'inactive', ['title' => _('Löschen nicht möglich')]) ?>
                <? endif; ?>
            <? endif; ?>
            </td>
        </tr>
        <? if ($studiengang_id == $studiengang->id) : ?>
            <? if ($studiengang->typ == 'mehrfach') : ?>
        <tr class="loaded-details nohover">
            <?= $this->render_partial('studiengaenge/studiengaenge/stgteil_bezeichnungen', compact('studiengang_id', 'studiengang', 'bez_stgteile', 'stgteile', 'stg_stgbez_id', 'search_stgteil', 'search')) ?>
        </tr>
            <? else : ?>
        <tr class="loaded-details nohover">
            <?= $this->render_partial('studiengaenge/studiengaenge/studiengangteile', compact('studiengang', 'stgteile', 'search_stgteil', 'search')) ?>
        </tr>
            <? endif; ?>
        <? endif; ?>
    </tbody>
    <? endforeach; ?>