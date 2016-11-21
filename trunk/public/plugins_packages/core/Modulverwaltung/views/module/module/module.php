<? foreach ($module as $modul) : ?>
    <? $perm = MvvPerm::get($modul) ?>
    <tbody class="<?= ($modul->count_modulteile ? '' : 'empty ') ?><?= ($modul_id == $modul->getId() ? 'not-collapsed' : 'collapsed') ?>">
        <tr class="header-row" id="modul_<?= $modul->getId() ?>">
            <td class="toggle-indicator">
                <? $ampel_icon = $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'][$modul->stat]['icon'] ?>
                <? $ampelstatus = $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'][$modul->stat]['name'] ?>
                <? if ($modul->count_modulteile) : ?>
                    <? $details_url = $details_url ? $details_url : '/details'; ?>
                    <a class="mvv-load-in-new-row" href="<?= $controller->url_for($details_url, $modul->getId()) ?>">
                        <? if ($ampel_icon) : ?>
                            <?= $ampel_icon->asImg(['title' => $ampelstatus, 'style' => 'vertical-align: text-top;']) ?>
                        <? endif; ?>
                        <?= htmlReady($modul->getDisplayName()) ?> </a>
                <? else : ?>
                    <? if ($ampel_icon) : ?>
                        <?= $ampel_icon->asImg(['title' => $ampelstatus, 'style' => 'vertical-align: text-top;']) ?>
                    <? endif; ?>
                    <?= htmlReady($modul->getDisplayName()) ?>
                <? endif; ?>
            </td>
            <td style="white-space:nowrap;" class="dont-hide"><?= htmlReady($modul->code) ?></td>
            <td style="text-align:center;" class="dont-hide"><?= htmlReady($modul->fassung_nr) ?></td>
            <td style="text-align: center;" class="dont-hide"><?= $modul->count_modulteile ?></td>
            <td class="dont-hide actions">
                <? if ($perm->havePermRead()) : ?>
                    <? foreach ($modul->deskriptoren->pluck('sprache') as $language) : ?>
                        <? $lang = $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'][$language]; ?>
                        <a href="<?= $controller->url_for('/modul/' . $modul->id . '/', ['display_language' => $language]) ?>">
                            <img src="<?= Assets::image_path('languages/lang_' . mb_strtolower($language) . '.gif') ?>"
                                 alt="<?= $lang['name'] ?>" title="<?= $lang['name'] ?>">
                        </a>
                    <? endforeach; ?>
                <? endif; ?>
            </td>
            <td class="dont-hide actions" style="white-space: nowrap;">
                <? $actionMenu = ActionMenu::get() ?>
                <? if ($modul->stat == 'planung' && $perm->haveFieldPerm('stat')) : ?>
                    <? $actionMenu->addLink(
                            $controller->url_for('/approve/' . $modul->id),
                            _('Modul genehmigen'),
                            Icon::create('accept', 'clickable', ['title' => _('Modul genehmigen')]))
                    ?>
                <? endif; ?>
                <? if ($perm->havePermRead()) : ?>
                    <? $actionMenu->addLink(
                            $controller->url_for('/description/' . $modul->id),
                            _('Modulbeschreibung ansehen'),
                            Icon::create('log', 'clickable', ['title' => _('Modulbeschreibung ansehen')]),
                            ['data-dialog' => 'size=auto',
                             'title'       => htmlReady($modul->getDisplayName())])
                    ?>
                <? endif; ?>
                <? if ($perm->haveFieldPerm('modulteile', MvvPerm::PERM_CREATE)) : ?>
                    <? $actionMenu->addLink(
                            $controller->url_for('/modulteil', ['modul_id' => $modul->id]),
                            _('Modulteil anlegen'),
                            Icon::create('file+add', 'clickable', ['title' => _('Modulteil anlegen')]))
                    ?>
                <? endif; ?>
                <? if ($perm->havePermWrite()) : ?>
                    <? $actionMenu->addLink(
                            $controller->url_for('/modul/' . $modul->id),
                            _('Modul bearbeiten'),
                            Icon::create('edit', 'clickable', ['title' => _('Modulteil bearbeiten')]))
                    ?>
                <? endif; ?>
                <? if ($perm->havePermCreate()) : ?>
                    <? $actionMenu->addLink(
                            $controller->url_for('/copy/' . $modul->id),
                            _('Modul kopieren'),
                            Icon::create('files', 'clickable', ['title' => _('Modulteil kopieren')]))
                    ?>
                <? endif; ?>
                <? if ($perm->havePermCreate()) : ?>
                    <? $actionMenu->addLink(
                            $controller->url_for('/delete/' . $modul->id),
                            _('Modul löschen'),
                            Icon::create('trash', 'clickable', ['title' => _('Modulteil löschen')]))
                    ?>
                <? endif; ?>
                <?= $actionMenu->render() ?>
            </td>
        </tr>
        <? if ($modul->count_modulteile && $modul_id == $modul->id) : ?>
            <tr class="loaded-details nohover">
                <?= $this->render_partial('module/module/details', compact('modul')) ?>
            </tr>
        <? endif; ?>
    </tbody>
<? endforeach; ?>
