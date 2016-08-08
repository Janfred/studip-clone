<? foreach ($versionen as $version) : ?>
    <? $perm = MvvPerm::get($version); ?>
    <tbody class="<?= ($version->count_abschnitte ? '' : 'empty') ?> <?= ($version_id == $version->id ? 'not-collapsed' : 'collapsed') ?>">
        <tr class="header-row">
            <td class="toggle-indicator">
                <? if ($version->count_abschnitte) : ?>
                <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/abschnitte', $version->id) ?>">
                <? endif; ?>
                <? $ampel_icon = $GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'][$version->stat]['icon'] ?>
                <? $ampelstatus = $GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'][$version->stat]['name'] ?>
                <? if ($ampel_icon) : ?>
                    <?= $ampel_icon->asImg(['title' => $ampelstatus, 'style' => 'vertical-align: text-top;']) ?>
                <? endif; ?>
                <?= htmlReady($version->getDisplayName()) ?>
            <? if ($version->count_abschnitte) : ?>
                </a>
                <? endif; ?>
            </td>
            <td class="dont-hide" style="text-align: center;">
                <? if ($version->count_dokumente) : ?>
                <?= Icon::create('staple', 'info', array('title' => sprintf(ngettext('%s Dokument zugeordnet', '%s Dokumente zugeordnet', $version->count_dokumente), $version->count_dokumente)))->asImg(); ?>
                <? endif; ?>
            </td>
            <td class="dont-hide" style="white-space: nowrap; text-align: right;">
            <? if ($version->stat == 'planung' && MvvPerm::haveFieldPermStat($version)) : ?>
                <a data-dialog="title='<?= htmlReady($version->getDisplayName()) ?>'" href="<?= $controller->url_for('/approve', $version->id) ?>">
                    <?= Icon::create('accept', 'clickable', array('title' => _('Version genehmigen')))->asImg(); ?>
                </a>
            <? endif; ?>
            <? if ($perm->haveFieldPerm('abschnitte', MvvPerm::PERM_CREATE)) : ?>
                <a data-dialog href="<?= $controller->url_for('/abschnitt', ['version_id' => $version->id]) ?>">
                    <?= Icon::create('file+add', 'clickable', array('title' => _('Studiengangteil-Abschnitt anlegen')))->asImg(); ?>
                </a>
            <? endif; ?>
            <? if ($perm->havePermWrite()) : ?>
                <a href="<?= $controller->url_for('/version', $version->stgteil_id, $version->id) ?>">
                    <?= Icon::create('edit', 'clickable', array('title' => _('Version bearbeiten')))->asImg(); ?>
                </a>
            <? endif; ?>
            <? if (MvvPerm::havePermCreate('StgteilVersion')) : ?>
                <? $msg = sprintf(_('Wollen Sie wirklich die Version "%s" des Studiengangteils kopieren?'),
                        $version->getDisplayName()); ?>
                <form style="display: inline-block" action="<?= $controller->url_for('/copy', $version->id) ?>" method="post">
                    <?= CSRFProtection::tokenTag(); ?>
                    <?= Icon::create('files', 'clickable', ['title' => _('Version kopieren')])->asInput(['data-confirm' => htmlReady($msg)]); ?>
                </form>
            <? endif; ?>
            <? if ($perm->havePermCreate()) : ?>
                <? $msg = sprintf(_('Wollen Sie wirklich die Version "%s" des Studiengangteils löschen?'),
                    $version->getDisplayName()); ?>
                <form style="display: inline-block" action="<?= $controller->url_for('/delete_version', $version->id) ?>" method="post">
                    <?= CSRFProtection::tokenTag(); ?>
                    <?= Icon::create('trash', 'clickable', ['title' => _('Version löschen')])->asInput(['data-confirm' => htmlReady($msg)]); ?>
                </form>
            <? endif; ?>
            </td>
        </tr>
        <? if ($version_id == $version->id) : ?>
        <tr class="loaded-details nohover">
            <?= $this->render_partial('studiengaenge/versionen/abschnitte', compact('version', 'abschnitte')) ?>
        </tr>
        <? endif; ?>
    </tbody>
<? endforeach; ?>